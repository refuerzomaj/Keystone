<?php
/*
 * Admin Settings: Stripe Connect
 *
 * @package CL_Stripe\Admin\Settings\Stripe_Connect
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines if the Stripe API keys can be managed manually.
 *
 * @since 2.8.0
 *
 * @return bool
 */
function cls_stripe_connect_can_manage_keys() {
	 $can_manage = false;

	/**
	 * Filters the ability to override the ability to manually manage
	 * Stripe API keys.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $can_manage If the current user can manage API keys.
	 */
	$can_manage = apply_filters( 'cls_stripe_connect_can_manage_keys', $can_manage );

	return $can_manage;
}

/**
 * Retrieves a URL to allow Stripe Connect via oAuth.
 *
 * @since 2.8.0
 *
 * @return string
 */
function cls_stripe_connect_url() {
	$return_url = add_query_arg(
		array(
			'post_type' => 'listing',
			'page'      => 'cl-settings',
			'tab'       => 'gateways',
			'section'   => 'cl-stripe',
		),
		admin_url( 'edit.php' )
	);

	/**
	 * Filters the URL users are returned to after using Stripe Connect oAuth.
	 *
	 * @since 2.8.0
	 *
	 * @param $return_url URL to return to.
	 */
	$return_url = apply_filters( 'cls_stripe_connect_return_url', $return_url );

	$stripe_connect_url = add_query_arg(
		array(
			'live_mode'         => (int) ! cl_is_test_mode(),
			'state'             => str_pad( wp_rand( wp_rand(), PHP_INT_MAX ), 100, wp_rand(), STR_PAD_BOTH ),
			'customer_site_url' => $return_url,
		),
		'#'
	);

	/**
	 * Filters the URL to start the Stripe Connect oAuth flow.
	 *
	 * @since 2.8.0
	 *
	 * @param $stripe_connect_url URL to oAuth proxy.
	 */
	$stripe_connect_url = apply_filters( 'cls_stripe_connect_url', $stripe_connect_url );

	return $stripe_connect_url;
}

/**
 * Listens for Stripe Connect completion requests and saves the Stripe API keys.
 *
 * @since 2.6.14
 */
function cls_process_gateway_connect_completion() {
	if ( ! isset( $_GET['cl_gateway_connect_completion'] ) || 'stripe_connect' !== $_GET['cl_gateway_connect_completion'] || ! isset( $_GET['state'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( headers_sent() ) {
		return;
	}

	$cl_credentials_url = add_query_arg(
		array(
			'live_mode'         => (int) ! cl_is_test_mode(),
			'state'             => cl_sanitization( $_GET['state'] ),
			'customer_site_url' => admin_url( 'edit.php?post_type=listing' ),
		),
		'#'
	);

	$response = wp_remote_get( esc_url_raw( $cl_credentials_url ) );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		$message = '<p>' . sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'There was an error getting your Stripe credentials. Please %1$stry again%2$s. If you continue to have this problem, please contact support.', 'essential-wp-real-estate' ),
			'<a href="' . esc_url( admin_url( 'edit.php?post_type=listing&page=cl-settings&tab=gateways&section=cl-stripe' ) ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		) . '</p>';
		wp_die( $message );
	}

	$data = json_decode( $response['body'], true );
	$data = $data['data'];

	if ( cl_is_test_mode() ) {
		cl_update_option( 'test_publishable_key', cl_sanitization( $data['publishable_key'] ) );
		cl_update_option( 'test_secret_key', cl_sanitization( $data['secret_key'] ) );
	} else {
		cl_update_option( 'live_publishable_key', cl_sanitization( $data['publishable_key'] ) );
		cl_update_option( 'live_secret_key', cl_sanitization( $data['secret_key'] ) );
	}

	cl_update_option( 'stripe_connect_account_id', cl_sanitization( $data['stripe_user_id'] ) );
	wp_redirect( esc_url_raw( admin_url( 'edit.php?post_type=listing&page=cl-settings&tab=gateways&section=cl-stripe' ) ) );
	exit;
}
add_action( 'admin_init', 'cls_process_gateway_connect_completion' );

/**
 * Returns a URL to disconnect the current Stripe Connect account ID and keys.
 *
 * @since 2.8.0
 *
 * @return string $stripe_connect_disconnect_url URL to disconnect an account ID and keys.
 */
function cls_stripe_connect_disconnect_url() {
	$stripe_connect_disconnect_url = add_query_arg(
		array(
			'post_type'             => 'listing',
			'page'                  => 'cl-settings',
			'tab'                   => 'gateways',
			'section'               => 'cl-stripe',
			'cls-stripe-disconnect' => true,
		),
		admin_url( 'edit.php' )
	);

	/**
	 * Filters the URL to "disconnect" the Stripe Account.
	 *
	 * @since 2.8.0
	 *
	 * @param $stripe_connect_disconnect_url URL to remove the associated Account ID.
	 */
	$stripe_connect_disconnect_url = apply_filters(
		'cls_stripe_connect_disconnect_url',
		$stripe_connect_disconnect_url
	);

	$stripe_connect_disconnect_url = wp_nonce_url( $stripe_connect_disconnect_url, 'cls-stripe-connect-disconnect' );

	return $stripe_connect_disconnect_url;
}

/**
 * Removes the associated Stripe Connect Account ID and keys.
 *
 * This does not revoke application permissions from the Stripe Dashboard,
 * it simply allows the "Connect with Stripe" flow to run again for a different account.
 *
 * @since 2.8.0
 */
function cls_stripe_connect_process_disconnect() {
	// Do not need to handle this request, bail.
	if ( ! ( isset( $_GET['page'] ) && 'cl-settings' === $_GET['page'] ) || ! isset( $_GET['cls-stripe-disconnect'] )
	) {
		return;
	}

	// Current user cannot handle this request, bail.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// No nonce, bail.
	if ( ! isset( $_GET['_wpnonce'] ) ) {
		return;
	}

	// Invalid nonce, bail.
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'cls-stripe-connect-disconnect' ) ) {
		return;
	}

	$options = array(
		'stripe_connect_account_id',
		'stripe_connect_account_country',
		'test_publishable_key',
		'test_secret_key',
		'live_publishable_key',
		'live_secret_key',
	);

	foreach ( $options as $option ) {
		cl_delete_option( $option );
	}

	$redirect = remove_query_arg(
		array(
			'_wpnonce',
			'cls-stripe-disconnect',
		)
	);

	return wp_redirect( esc_url_raw( $redirect ) );
}
add_action( 'admin_init', 'cls_stripe_connect_process_disconnect' );

/**
 * Updates the `stripe_connect_account_country` setting if using Stripe Connect
 * and no country information is available.
 *
 * @since 2.8.7
 */
function cls_stripe_connect_maybe_refresh_account_country() {
	// Current user cannot modify options, bail.
	if ( false === current_user_can( 'manage_options' ) ) {
		return;
	}

	// Stripe Connect has not been used, bail.
	$account_id = cl_admin_get_option( 'stripe_connect_account_id', '' );

	if ( empty( $account_id ) ) {
		return;
	}

	// Account country is already set, bail.
	$account_country = cl_admin_get_option( 'stripe_connect_account_country', '' );

	if ( ! empty( $account_country ) ) {
		return;
	}

	try {
		$account = cls_api_request( 'Account', 'retrieve', $account_id );

		if ( isset( $account->country ) ) {
			$account_country = cl_sanitization(
				strtolower( $account->country )
			);

			cl_update_option(
				'stripe_connect_account_country',
				$account_country
			);
		}
	} catch ( \Exception $e ) {
		// Do nothing.
	}
}
add_action( 'admin_init', 'cls_stripe_connect_maybe_refresh_account_country' );

/**
 * Renders custom HTML for the "Stripe Connect" setting field in the Stripe Payment Gateway
 * settings subtab.
 *
 * Provides a way to use Stripe Connect and manually manage API keys.
 *
 * @since 2.8.0
 */
function cls_stripe_connect_setting_field() {
	$stripe_connect_url    = cls_stripe_connect_url();
	$stripe_disconnect_url = cls_stripe_connect_disconnect_url();

	$stripe_connect_account_id = cl_admin_get_option( 'stripe_connect_account_id' );

	$api_key = cl_is_test_mode()
		? cl_admin_get_option( 'test_publishable_key' )
		: cl_admin_get_option( 'live_publishable_key' );

	ob_start();
	?>

	<?php if ( empty( $api_key ) ) : ?>

		<a href="<?php echo esc_url( $stripe_connect_url ); ?>" class="cl-stripe-connect">
			<span><?php esc_html_e( 'Connect with Stripe', 'essential-wp-real-estate' ); ?></span>
		</a>

		<p>
			<?php
			/** This filter is documented in includes/admin/settings/stripe-connect.php */
			$show_fee_message = apply_filters( 'cls_show_stripe_connect_fee_message', true );

			$fee_message = true === $show_fee_message
				? ( __(
					'Connect with Stripe for pay as you go pricing: 2% per-transaction fee + Stripe fees.',
					'essential-wp-real-estate'
				) . ' '
				)
				: '';

			echo esc_html( $fee_message );
			echo wp_kses(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__( 'Have questions about connecting with Stripe? See the %1$sdocumentation%2$s.', 'essential-wp-real-estate' ),
					'<a href="' . esc_url( cls_documentation_route( 'stripe-connect' ) ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				array(
					'a' => array(
						'href'   => true,
						'target' => true,
						'rel'    => true,
					),
				)
			);
			?>
		</p>

	<?php endif; ?>

	<?php if ( ! empty( $api_key ) ) : ?>

		<div id="cls-stripe-connect-account" class="cls-stripe-connect-acount-info notice inline" data-account-id="<?php echo esc_attr( $stripe_connect_account_id ); ?>" data-nonce="<?php echo wp_create_nonce( 'cls-stripe-connect-account-information' ); ?>">
			<p><span class="spinner is-active"></span>
				<em><?php esc_html_e( 'Retrieving account information...', 'essential-wp-real-estate' ); ?></em>
		</div>
		<div id="cls-stripe-disconnect-reconnect">
		</div>

	<?php endif; ?>

	<?php if ( true === cls_stripe_connect_can_manage_keys() ) : ?>

		<div class="cls-api-key-toggle">
			<p>
				<button type="button" class="button-link">
					<small>
						<?php esc_html_e( 'Manage API keys manually', 'essential-wp-real-estate' ); ?>
					</small>
				</button>
			</p>
		</div>

		<div class="cls-api-key-toggle cl-hidden">
			<p>
				<button type="button" class="button-link">
					<small>
						<?php esc_html_e( 'Hide API keys', 'essential-wp-real-estate' ); ?>
					</small>
				</button>
			</p>

			<div class="notice inline notice-warning" style="margin: 15px 0 -10px;">
				<?php echo wpautop( esc_html__( 'Although you can add your API keys manually, we recommend using Stripe Connect: an easier and more secure way of connecting your Stripe account to your website. Stripe Connect prevents issues that can arise when copying and pasting account details from Stripe into your Property Listing Plugin payment gateway settings. With Stripe Connect you\'ll be ready to go with just a few clicks.', 'essential-wp-real-estate' ) ); ?>
			</div>
		</div>

	<?php endif; ?>

	<?php
	return ob_get_clean();
}

/**
 * Responds to an AJAX request about the current Stripe connection status.
 *
 * @since 2.8.0
 */
function cls_stripe_connect_account_info_ajax_response() {
	// Generic error.
	$unknown_error = array(
		'message' => wpautop( esc_html__( 'Unable to retrieve account information.', 'essential-wp-real-estate' ) ),
	);

	// Current user can't manage settings.
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return wp_send_json_error( $unknown_error );
	}

	// Nonce validation, show error on fail.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cls-stripe-connect-account-information' ) ) {
		return wp_send_json_error( $unknown_error );
	}

	$account_id = isset( $_POST['accountId'] )
		? cl_sanitization( $_POST['accountId'] )
		: '';

	$mode = cl_is_test_mode()
		? _x( 'test', 'Stripe Connect mode', 'essential-wp-real-estate' )
		: _x( 'live', 'Stripe Connect mode', 'essential-wp-real-estate' );

	// Provides general reconnect and disconnect action URLs.
	$reconnect_disconnect_actions = wp_kses(
		sprintf(
			/* translators: %1$s Stripe payment mode. %2$s Opening anchor tag for reconnecting to Stripe, do not translate. %3$s Opening anchor tag for disconnecting Stripe, do not translate. %4$s Closing anchor tag, do not translate. */
			__( 'Your Stripe account is connected in %1$s mode. %2$sReconnect in %1$s mode%4$s, or %3$sdisconnect this account%4$s.', 'essential-wp-real-estate' ),
			'<strong>' . $mode . '</strong>',
			'<a href="' . esc_url( cls_stripe_connect_url() ) . '" rel="noopener noreferrer">',
			'<a href="' . esc_url( cls_stripe_connect_disconnect_url() ) . '">',
			'</a>'
		),
		array(
			'strong' => true,
			'a'      => array(
				'href' => true,
				'rel'  => true,
			),
		)
	);

	// If connecting in Test Mode Stripe gives you the opportunity to create a
	// temporary account. Alert the user of the limitations associated with
	// this type of account.
	$dev_account_error = array(
		'message' => wp_kses(
			wpautop(
				sprintf(
					__(
						/* translators: %1$s Opening bold tag, do not translate. %2$s Closing bold tag, do not translate. */
						'You are currently connected to a %1$stemporary%2$s Stripe test account, which can only be used for testing purposes. You cannot manage this account in Stripe.',
						'essential-wp-real-estate'
					),
					'<strong>',
					'</strong>'
				) . ' ' .
					( class_exists( 'CL_Recurring' )
						? __(
							'Webhooks cannot be configured for recurring purchases with this account.',
							'essential-wp-real-estate'
						)
						: ''
					) . ' ' .
					sprintf(
						__(
							/* translators: %1$s Opening link tag, do not translate. %2$s Closing link tag, do not translate. */
							'%1$sRegister a Stripe account%2$s for full access.',
							'essential-wp-real-estate'
						),
						'<a href="https://dashboard.stripe.com/register" target="_blank" rel="noopener noreferrer">',
						'</a>'
					) . ' ' .
					'<br /><br />' .
					sprintf(
						/* translators: %1$s Opening anchor tag for disconnecting Stripe, do not translate. %2$s Closing anchor tag, do not translate. */
						__( '%1$sDisconnect this account%2$s.', 'essential-wp-real-estate' ),
						'<a href="' . esc_url( cls_stripe_connect_disconnect_url() ) . '">',
						'</a>'
					)
			),
			array(
				'p'      => true,
				'strong' => true,
				'a'      => array(
					'href'   => true,
					'rel'    => true,
					'target' => true,
				),
			)
		),
		'status'  => 'warning',
	);

	// Attempt to show account information from Stripe Connect account.
	if ( ! empty( $account_id ) ) {
		try {
			$account = cls_api_request( 'Account', 'retrieve', $account_id );

			// Find the email.
			$email = isset( $account->email )
				? esc_html( $account->email )
				: '';

			// Find a Display Name.
			$display_name = isset( $account->display_name )
				? esc_html( $account->display_name )
				: '';

			if (
				empty( $display_name ) &&
				isset( $account->settings ) &&
				isset( $account->settings->dashboard ) &&
				isset( $account->settings->dashboard->display_name )
			) {
				$display_name = esc_html( $account->settings->dashboard->display_name );
			}

			// Unsaved/unactivated accounts do not have an email or display name.
			if ( empty( $email ) && empty( $display_name ) ) {
				return wp_send_json_success( $dev_account_error );
			}

			if ( ! empty( $display_name ) ) {
				$display_name = '<strong>' . $display_name . '</strong><br/ >';
			}

			if ( ! empty( $email ) ) {
				$email = $email . ' &mdash; ';
			}

			/**
			 * Filters if the Stripe Connect fee messaging should show.
			 *
			 * @since 2.8.1
			 *
			 * @param bool $show_fee_message Show fee message, or not.
			 */
			$show_fee_message = apply_filters( 'cls_show_stripe_connect_fee_message', true );

			$fee_message = true === $show_fee_message
				? wpautop(
					esc_html__(
						'Pay as you go pricing: 2% per-transaction fee + Stripe fees.',
						'essential-wp-real-estate'
					)
				)
				: '';

			// Return a message with name, email, and reconnect/disconnect actions.
			return wp_send_json_success(
				array(
					'message' => wpautop(
						// $display_name is already escaped
						$display_name . esc_html( $email ) . esc_html__( 'Administrator (Owner)', 'essential-wp-real-estate' ) . $fee_message
					),
					'actions' => $reconnect_disconnect_actions,
					'status'  => 'success',
				)
			);
		} catch ( \Stripe\Exception\AuthenticationException $e ) {
			// API keys were changed after using Stripe Connect.
			return wp_send_json_error(
				array(
					'message' => wpautop(
						esc_html__( 'The API keys provided do not match the Stripe Connect account associated with this installation. If you have manually modified these values after connecting your account, please reconnect below or update your API keys.', 'essential-wp-real-estate' ) .
							'<br /><br />' .
							$reconnect_disconnect_actions
					),
				)
			);
		} catch ( \CL_Stripe_Utils_Exceptions_Stripe_API_Unmet_Requirements $e ) {
			return wp_send_json_error(
				array(
					'message' => wpautop(
						$e->getMessage()
					),
				)
			);
		} catch ( \Exception $e ) {
			// General error.
			return wp_send_json_error( $unknown_error );
		}
		// Manual API key management.
	} else {
		$connect_button = sprintf(
			'<a href="%s" class="cl-stripe-connect"><span>%s</span></a>',
			esc_url( cls_stripe_connect_url() ),
			esc_html__( 'Connect with Stripe', 'essential-wp-real-estate' )
		);

		$connect = esc_html__( 'It is highly recommended to Connect with Stripe for easier setup and improved security.', 'essential-wp-real-estate' );

		// See if the keys are valid.
		try {
			// While we could show similar account information, leave it blank to help
			// push people towards Stripe Connect.
			$account = cls_api_request( 'Account', 'retrieve' );

			return wp_send_json_success(
				array(
					'message' => wpautop(
						sprintf(
							/* translators: %1$s Stripe payment mode.*/
							__( 'Your manually managed %1$s mode API keys are valid.', 'essential-wp-real-estate' ),
							'<strong>' . $mode . '</strong>'
						) .
							'<br /><br />' .
							$connect . '<br /><br />' . $connect_button
					),
					'status'  => 'success',
				)
			);
			// Show invalid keys.
		} catch ( \Exception $e ) {
			return wp_send_json_error(
				array(
					'message' => wpautop(
						sprintf(
							/* translators: %1$s Stripe payment mode.*/
							__( 'Your manually managed %1$s mode API keys are invalid.', 'essential-wp-real-estate' ),
							'<strong>' . $mode . '</strong>'
						) .
							'<br /><br />' .
							$connect . '<br /><br />' . $connect_button
					),
				)
			);
		}
	}
}
add_action( 'wp_ajax_cls_stripe_connect_account_info', 'cls_stripe_connect_account_info_ajax_response' );

/**
 * Registers admin notices for Stripe Connect.
 *
 * @since 2.8.0
 *
 * @return true|WP_Error True if all notices are registered, otherwise WP_Error.
 */
function cls_stripe_connect_admin_notices_register() {
	$registry = cls_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return new WP_Error( 'cls-invalid-registry', esc_html__( 'Unable to locate registry', 'essential-wp-real-estate' ) );
	}

	$connect_button = sprintf(
		'<a href="%s" class="cl-stripe-connect"><span>%s</span></a>',
		esc_url( cls_stripe_connect_url() ),
		esc_html__( 'Connect with Stripe', 'essential-wp-real-estate' )
	);

	try {
		// Stripe Connect.
		$registry->add(
			'stripe-connect',
			array(
				'message'     => sprintf(
					'<p>%s</p><p>%s</p>',
					esc_html__( 'Start accepting payments with Stripe by connecting your account. Stripe Connect helps ensure easier setup and improved security.', 'essential-wp-real-estate' ),
					$connect_button
				),
				'type'        => 'info',
				'dismissible' => true,
			)
		);

		// Stripe Connect reconnect.
		/** translators: %s Test mode status. */
		$test_mode_status = cl_is_test_mode()
			? _x( 'enabled', 'gateway test mode status', 'essential-wp-real-estate' )
			: _x( 'disabled', 'gateway test mode status', 'essential-wp-real-estate' );

		$registry->add(
			'stripe-connect-reconnect',
			array(
				'message'     => sprintf(
					'<p>%s</p><p>%s</p>',
					sprintf(
						/* translators: %s Test mode status. Enabled or disabled. */
						__( '"Test Mode" has been %s. Please verify your Stripe connection status.', 'essential-wp-real-estate' ),
						$test_mode_status
					),
					$connect_button
				),
				'type'        => 'warning',
				'dismissible' => true,
			)
		);
	} catch ( Exception $e ) {
		return new WP_Error( 'cls-invalid-notices-registration', esc_html__( $e->getMessage() ) );
	};

	return true;
}
add_action( 'admin_init', 'cls_stripe_connect_admin_notices_register' );

/**
 * Conditionally prints registered notices.
 *
 * @since 2.6.19
 */
function cls_stripe_connect_admin_notices_print() {
	 // Current user needs capability to dismiss notices.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$registry = cls_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return;
	}

	$notices = new CL_Stripe_Admin_Notices( $registry );

	wp_enqueue_script( 'cls-admin-notices' );

	try {
		$enabled_gateways = WPERECCP()->front->gateways->cl_get_enabled_payment_gateways();

		$api_key = true === cl_is_test_mode()
			? cl_admin_get_option( 'test_secret_key' )
			: cl_admin_get_option( 'live_secret_key' );

		$mode_toggle = isset( $_GET['cl-message'] ) && 'connect-to-stripe' === $_GET['cl-message'];

		if ( array_key_exists( 'stripe', $enabled_gateways ) && empty( $api_key ) ) {
			wp_enqueue_style(
				'cl-stripe-admin-styles',
				CLS_PLUGIN_URL . 'assets/css/build/admin.min.css',
				array(),
				CL_STRIPE_VERSION
			);

			// Stripe Connect.
			if ( false === $mode_toggle ) {
				$notices->output( 'stripe-connect' );
				// Stripe Connect reconnect.
			} else {
				$notices->output( 'stripe-connect-reconnect' );
			}
		}
	} catch ( Exception $e ) {
	}
}
add_action( 'admin_notices', 'cls_stripe_connect_admin_notices_print' );
