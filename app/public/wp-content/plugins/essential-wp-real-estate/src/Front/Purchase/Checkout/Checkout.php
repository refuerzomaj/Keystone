<?php
namespace  Essential\Restate\Front\Purchase\Checkout;

use Essential\Restate\Traitval\Traitval;

class Checkout {

	use Traitval;
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'cl_enforced_ssl_asset_handler' ) );
		add_action( 'template_redirect', array( $this, 'cl_enforced_ssl_redirect_handler' ) );
		add_action( 'template_redirect', array( $this, 'cl_listen_for_failed_payments' ) );
		add_action( 'cl_purchase_form', array( $this, 'cl_show_purchase_form' ) );
		add_action( 'cl_purchase_form_after_cc_form', array( $this, 'cl_checkout_submit' ), 9999 );
		add_action( 'cl_purchase_form_before_submit', array( $this, 'cl_checkout_final_total' ), 9999 );
		add_action( 'cl_purchase_form_after_submit', array( $this, 'cl_prb_unshim_active_gateways' ) );

		add_action( 'cl_purchase_form_after_user_info', array( $this, 'cl_user_info_fields' ) );
		add_action( 'cl_register_fields_before', array( $this, 'cl_user_info_fields' ) );

		add_action( 'cl_after_cc_fields', array( $this, 'cl_default_cc_address_fields' ) );
		add_action( 'cl_purchase_form_after_cc_form', array( $this, 'cl_checkout_tax_fields' ), 999 );
	}

	function cl_user_info_fields() {
		$customer = WPERECCP()->front->session->get( 'customer' );
		$customer = wp_parse_args(
			$customer,
			array(
				'first_name' => '',
				'last_name'  => '',
				'email'      => '',
			)
		);

		if ( is_user_logged_in() ) {
			$user_data = get_userdata( get_current_user_id() );

			foreach ( $customer as $key => $field ) {

				if ( 'email' == $key && empty( $field ) ) {
					$customer[ $key ] = $user_data->user_email;
				} elseif ( 'first_name' == $key && empty( $field ) ) {
					if ( isset( $user_data->first_name ) && $user_data->first_name != '' ) {
						$customer[ $key ] = $user_data->first_name;
					} else {
						$customer[ $key ] = $user_data->user_login;
					}
				} elseif ( empty( $field ) ) {
					$customer[ $key ] = $user_data->$key;
				}
			}
		}
		$customer = array_map( 'cl_sanitization', $customer );

		?>
		<fieldset id="cl_checkout_user_info">
			<legend><?php echo apply_filters( 'cl_checkout_personal_info_text', esc_html__( 'Personal Info', 'essential-wp-real-estate' ) ); ?></legend>
			<?php do_action( 'cl_purchase_form_before_email' ); ?>
			<p id="cl-email-wrap">
				<label class="cl-label" for="cl-email">
					<?php esc_html_e( 'Email Address', 'essential-wp-real-estate' ); ?>
					<?php if ( $this->cl_field_is_required( 'cl_email' ) ) { ?>
						<span class="cl-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="cl-description" id="cl-email-description"><?php esc_html_e( 'We will send the purchase receipt to this address.', 'essential-wp-real-estate' ); ?></span>
				<input class="cl-input required" type="email" name="cl_email" placeholder="<?php esc_html_e( 'Email address', 'essential-wp-real-estate' ); ?>" id="cl-email" value="<?php echo esc_attr( $customer['email'] ); ?>" aria-describedby="cl-email-description" 
																											 <?php
																												if ( $this->cl_field_is_required( 'cl_email' ) ) {
																																																																		echo ' required ';
																												}
																												?>
																																																																	 />
			</p>
			<?php do_action( 'cl_purchase_form_after_email' ); ?>
			<p id="cl-first-name-wrap">
				<label class="cl-label" for="cl-first">
					<?php esc_html_e( 'First Name', 'essential-wp-real-estate' ); ?>
					<?php if ( $this->cl_field_is_required( 'cl_first' ) ) { ?>
						<span class="cl-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="cl-description" id="cl-first-description"><?php esc_html_e( 'We will use this to personalize your account experience.', 'essential-wp-real-estate' ); ?></span>
				<input class="cl-input required" type="text" name="cl_first" placeholder="<?php esc_html_e( 'First Name', 'essential-wp-real-estate' ); ?>" id="cl-first" value="<?php echo esc_attr( $customer['first_name'] ); ?>" 
																											<?php
																											if ( $this->cl_field_is_required( 'cl_first' ) ) {
																																																								echo ' required ';
																											}
																											?>
																																																							>
			</p>
			<p id="cl-last-name-wrap">
				<label class="cl-label" for="cl-last">
					<?php esc_html_e( 'Last Name', 'essential-wp-real-estate' ); ?>
					<?php if ( $this->cl_field_is_required( 'cl_last' ) ) { ?>
						<span class="cl-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="cl-description" id="cl-last-description"><?php esc_html_e( 'We will use this as well to personalize your account experience.', 'essential-wp-real-estate' ); ?></span>
				<input class="cl-input
				<?php
				if ( $this->cl_field_is_required( 'cl_last' ) ) {
											echo ' required';
				}
				?>
										" type="text" name="cl_last" id="cl-last" placeholder="<?php esc_html_e( 'Last Name', 'essential-wp-real-estate' ); ?>" value="<?php echo esc_attr( $customer['last_name'] ); ?>" 
										<?php
										if ( $this->cl_field_is_required( 'cl_last' ) ) {
																																																					  echo ' required ';
										}
										?>
																																																					 aria-describedby="cl-last-description" />
			</p>
			<?php do_action( 'cl_purchase_form_user_info' ); ?>
			<?php do_action( 'cl_purchase_form_user_info_fields' ); ?>
		</fieldset>
		<?php
	}



	function cl_checkout_final_total() {
		?>
		<p id="cl_final_total_wrap">
			<strong><?php _e( 'Purchase Total:', 'essential-wp-real-estate' ); ?></strong>
			<span class="cl_cart_amount" data-subtotal="<?php echo WPERECCP()->front->cart->get_subtotal(); ?>" data-total="<?php echo WPERECCP()->front->cart->get_total(); ?>"><?php WPERECCP()->front->cart->cl_cart_total(); ?></span>
		</p>
		<?php
	}

	function cl_prb_unshim_active_gateways() {
		remove_filter( 'cl_chosen_gateway', 'cls_prb_set_base_gateway' );
		remove_filter( 'cl_is_gateway_active', 'cls_prb_is_gateway_active', 10, 2 );
	}



	function cl_checkout_submit() {
		?>
		<fieldset id="cl_purchase_submit">
			<?php do_action( 'cl_purchase_form_before_submit' ); ?>

			<?php $this->cl_checkout_hidden_fields(); ?>

			<?php echo $this->cl_checkout_button_purchase(); ?>

			<?php do_action( 'cl_purchase_form_after_submit' ); ?>

			<?php if ( cl_is_ajax_disabled() ) { ?>
				<p class="cl-cancel"><a href="<?php echo cl_get_checkout_uri(); ?>"><?php _e( 'Go back', 'essential-wp-real-estate' ); ?></a></p>
			<?php } ?>
		</fieldset>
		<?php
	}

	function cl_checkout_hidden_fields() {
		?>
		<?php if ( is_user_logged_in() ) { ?>
			<input type="hidden" name="cl-user-id" value="<?php echo get_current_user_id(); ?>" />
		<?php } ?>
		<input type="hidden" name="cl_action" value="purchase" />
		<input type="hidden" name="cl-gateway" value="<?php echo WPERECCP()->front->gateways->cl_get_chosen_gateway(); ?>" />
		<?php wp_nonce_field( 'cl-process-checkout', 'cl-process-checkout-nonce', false, true ); ?>
		<?php
	}

	function cl_checkout_button_purchase() {
		ob_start();

		$enabled_gateways = WPERECCP()->front->gateways->cl_get_enabled_payment_gateways();
		$cart_total       = WPERECCP()->front->cart->get_total();

		if ( ! empty( $enabled_gateways ) || empty( $cart_total ) ) {
			$color = cl_admin_get_option( 'checkout_color', 'blue' );
			$color = ( $color == 'inherit' ) ? '' : $color;
			$style = cl_admin_get_option( 'button_style', 'button' );
			$label = $this->cl_get_checkout_button_purchase_label();

			?>
			<input type="submit" class="cl-submit <?php echo esc_attr( $color ); ?> <?php echo esc_attr( $style ); ?>" id="cl-purchase-button" name="cl-purchase" value="<?php echo esc_attr( $label ); ?>" />
			<?php
		}

		return apply_filters( 'cl_checkout_button_purchase', ob_get_clean() );
	}

	function cl_get_checkout_button_purchase_label() {
		if ( WPERECCP()->front->cart->get_total() ) {
			$label             = cl_admin_get_option( 'checkout_label', '' );
			$complete_purchase = ! empty( $label ) ? $label : __( 'Purchase', 'essential-wp-real-estate' );
		} else {
			$label             = cl_admin_get_option( 'free_checkout_label', '' );
			$complete_purchase = ! empty( $label ) ? $label : __( 'Free listing', 'essential-wp-real-estate' );
		}

		return apply_filters( 'cl_get_checkout_button_purchase_label', $complete_purchase, $label );
	}




	public function cl_show_purchase_form() {
		ob_start();
		echo cl_get_template( 'checkout/purchase_form.php' );
		return ob_get_clean();
	}



	public function cl_checkout_form_shortcode() {
		ob_start();
		$gateways       = WPERECCP()->front->gateways->cl_get_enabled_payment_gateways( true );
		$page_URL       = cl_get_current_page_url();
		$chosen_gateway = WPERECCP()->front->gateways->cl_get_chosen_gateway();
		$purchase_page  = cl_admin_get_option( 'purchase_page', '0' );
		$form_action    = esc_url( cl_get_checkout_uri( 'payment-mode=' . $chosen_gateway ) );
		cl_get_template(
			'checkout/checkout.php',
			array(
				'gateways'       => $gateways,
				'chosen_gateway' => $chosen_gateway,
				'purchase_page'  => $purchase_page,
				'form_action'    => $form_action,
			)
		);
		return ob_get_clean();
	}


	function cl_is_checkout() {
		 global $wp_query;

		$is_object_set    = isset( $wp_query->queried_object );
		$is_object_id_set = isset( $wp_query->queried_object_id );
		$is_checkout      = is_page( cl_admin_get_option( 'purchase_page' ) );

		if ( ! $is_object_set ) {
			unset( $wp_query->queried_object );
		} elseif ( is_singular() ) {
			$content = $wp_query->queried_object->post_content;
		}

		if ( ! $is_object_id_set ) {
			unset( $wp_query->queried_object_id );
		}

		// If we know this isn't the primary checkout page, check other methods.
		if ( ! $is_checkout && isset( $content ) && has_shortcode( $content, 'listing_checkout' ) ) {
			$is_checkout = true;
		}

		return apply_filters( 'cl_is_checkout', $is_checkout );
	}


	/**
	 * Determines if a user can checkout or not
	 *
	 * @since 1.3.3
	 * @return bool Can user checkout?
	 */
	function cl_can_checkout() {
		$can_checkout = true; // Always true for now

		return (bool) apply_filters( 'cl_can_checkout', $can_checkout );
	}

	/**
	 * Retrieve the Success page URI
	 *
	 * @since       1.6
	 * @return      string
	 */
	function cl_get_success_page_uri( $query_string = null ) {
		$page_id = cl_admin_get_option( 'success_page', 0 );
		$page_id = absint( $page_id );

		$success_page = get_permalink( $page_id );

		if ( $query_string ) {
			$success_page .= $query_string;
		}

		return apply_filters( 'cl_get_success_page_uri', $success_page );
	}




	/**
	 * Send To Success Page
	 *
	 * Sends the user to the succes page.
	 *
	 * @param string $query_string
	 * @since       1.0
	 * @return      void
	 */
	function cl_send_to_success_page( $query_string = null ) {
		$redirect = $this->cl_get_success_page_uri();

		if ( $query_string ) {
			$redirect .= $query_string;
		}
		$gateway = isset( $_REQUEST['cl-gateway'] ) ? cl_sanitization( $_REQUEST['cl-gateway'] ) : '';
		wp_redirect( apply_filters( 'cl_success_page_redirect', $redirect, $gateway, $query_string ) );
		wp_die();
	}

	/**
	 * Get the URL of the Checkout page
	 *
	 * @since 1.0.8
	 * @param array $args Extra query args to add to the URI
	 * @return mixed Full URL to the checkout page, if present | null if it doesn't exist
	 */
	function cl_get_checkout_uri( $args = array() ) {
		$uri = false;

		if ( cl_is_checkout() ) {
			global $post;
			$uri = $post instanceof WP_Post ? get_permalink( $post->ID ) : null;
		}

		// If we are not on a checkout page, determine the URI from the default.
		if ( empty( $uri ) ) {
			$uri = cl_admin_get_option( 'purchase_page', false );
			$uri = isset( $uri ) ? get_permalink( $uri ) : null;
		}

		if ( ! empty( $args ) ) {
			// Check for backward compatibility
			if ( is_string( $args ) ) {
				$args = str_replace( '?', '', $args );
			}

			$args = wp_parse_args( $args );

			$uri = add_query_arg( $args, $uri );
		}

		$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

		$ajax_url = admin_url( 'admin-ajax.php', $scheme );

		if ( ( ! preg_match( '/^https/', $uri ) && preg_match( '/^https/', $ajax_url ) && cl_is_ajax_enabled() ) || cl_is_ssl_enforced() ) {
			$uri = preg_replace( '/^http:/', 'https:', $uri );
		}

		if ( cl_admin_get_option( 'no_cache_checkout', false ) ) {
			$uri = cl_add_cache_busting( $uri );
		}

		return apply_filters( 'cl_get_checkout_uri', $uri );
	}

	/**
	 * Send back to checkout.
	 *
	 * Used to redirect a user back to the purchase
	 * page if there are errors present.
	 *
	 * @param array $args
	 * @since  1.0
	 * @return Void
	 */
	function cl_send_back_to_checkout( $args = array() ) {
		$redirect = cl_get_checkout_uri();

		if ( ! empty( $args ) ) {
			// Check for backward compatibility
			if ( is_string( $args ) ) {
				$args = str_replace( '?', '', $args );
			}

			$args = wp_parse_args( $args );

			$redirect = add_query_arg( $args, $redirect );
		}

		wp_redirect( apply_filters( 'cl_send_back_to_checkout', $redirect, $args ) );
		wp_die();
	}

	/**
	 * Get the URL of the Transaction Failed page
	 *
	 * @since 1.3.4
	 * @param bool $extras Extras to append to the URL
	 * @return mixed|void Full URL to the Transaction Failed page, if present, home page if it doesn't exist
	 */
	function cl_get_failed_transaction_uri( $extras = false ) {
		$uri = cl_admin_get_option( 'failure_page', '' );
		$uri = ! empty( $uri ) ? trailingslashit( get_permalink( $uri ) ) : home_url();

		if ( $extras ) {
			$uri .= $extras;
		}

		return apply_filters( 'cl_get_failed_transaction_uri', $uri );
	}

	/**
	 * Determines if we're currently on the Failed Transaction page.
	 *
	 * @since 2.1
	 * @return bool True if on the Failed Transaction page, false otherwise.
	 */
	function cl_is_failed_transaction_page() {
		$ret = cl_admin_get_option( 'failure_page', false );
		$ret = isset( $ret ) ? is_page( $ret ) : false;

		return apply_filters( 'cl_is_failure_page', $ret );
	}

	/**
	 * Mark payments as Failed when returning to the Failed Transaction page
	 *
	 * @since       1.9.9
	 * @return      void
	 */
	function cl_listen_for_failed_payments() {
		$failed_page = cl_admin_get_option( 'failure_page', 0 );

		if ( ! empty( $failed_page ) && is_page( $failed_page ) && ! empty( $_GET['payment-id'] ) ) {

			$payment_id = absint( cl_sanitization( $_GET['payment-id'] ) );
			$payment    = get_post( $payment_id );
			$status     = cl_get_payment_status( $payment );

			if ( $status && 'pending' === strtolower( $status ) ) {

				cl_update_payment_status( $payment_id, 'failed' );
			}
		}
	}


	/**
	 * Check if a field is required
	 *
	 * @param string $field
	 * @since       1.7
	 * @return      bool
	 */
	function cl_field_is_required( $field = '' ) {
		$required_fields = WPERECCP()->front->gateways->cl_purchase_form_required_fields();
		return array_key_exists( $field, $required_fields );
	}

	/**
	 * Retrieve an array of banned_emails
	 *
	 * @since       2.0
	 * @return      array
	 */
	function cl_get_banned_emails() {
		$emails = array_map( 'trim', cl_admin_get_option( 'banned_emails', array() ) );

		return apply_filters( 'cl_get_banned_emails', $emails );
	}

	/**
	 * Determines if an email is banned
	 *
	 * @since       2.0
	 * @param string $email Email to check if is banned.
	 * @return bool
	 */
	function cl_is_email_banned( $email = '' ) {

		$email = trim( $email );
		if ( empty( $email ) ) {
			return false;
		}

		$email         = strtolower( $email );
		$banned_emails = cl_get_banned_emails();

		if ( ! is_array( $banned_emails ) || empty( $banned_emails ) ) {
			return false;
		}

		$return = false;
		foreach ( $banned_emails as $banned_email ) {

			$banned_email = strtolower( $banned_email );

			if ( is_email( $banned_email ) ) {

				// Complete email address
				$return = ( $banned_email == $email ? true : false );
			} elseif ( strpos( $banned_email, '.' ) === 0 ) {

				// TLD block
				$return = ( substr( $email, ( strlen( $banned_email ) * -1 ) ) == $banned_email ) ? true : false;
			} else {

				// Domain block
				$return = ( stristr( $email, $banned_email ) ? true : false );
			}

			if ( true === $return ) {
				break;
			}
		}

		return apply_filters( 'cl_is_email_banned', $return, $email );
	}

	/**
	 * Determines if secure checkout pages are enforced
	 *
	 * @since       2.0
	 * @return      bool True if enforce SSL is enabled, false otherwise
	 */
	function cl_is_ssl_enforced() {
		 $ssl_enforced = cl_admin_get_option( 'enforce_ssl', false );
		return (bool) apply_filters( 'cl_is_ssl_enforced', $ssl_enforced );
	}

	/**
	 * Handle redirections for SSL enforced checkouts
	 *
	 * @since 2.0
	 * @return void
	 */
	function cl_enforced_ssl_redirect_handler() {
		if ( ! cl_is_ssl_enforced() || ! cl_is_checkout() || is_admin() || is_ssl() ) {
			return;
		}

		if ( cl_is_checkout() && false !== strpos( cl_get_current_page_url(), 'https://' ) ) {
			return;
		}

		$uri = 'https://' . cl_sanitization( $_SERVER['HTTP_HOST'] ) . cl_sanitization( $_SERVER['REQUEST_URI'] );

		wp_safe_redirect( $uri );
		exit;
	}


	/**
	 * Handle rewriting asset URLs for SSL enforced checkouts
	 *
	 * @since 2.0
	 * @return void
	 */
	function cl_enforced_ssl_asset_handler() {
		if ( ! cl_is_ssl_enforced() || ! cl_is_checkout() || is_admin() ) {
			return;
		}

		$filters = array(
			'post_thumbnail_html',
			'wp_get_attachment_url',
			'wp_get_attachment_image_attributes',
			'wp_get_attachment_url',
			'option_stylesheet_url',
			'option_template_url',
			'script_loader_src',
			'style_loader_src',
			'template_directory_uri',
			'stylesheet_directory_uri',
			'site_url',
		);

		$filters = apply_filters( 'cl_enforced_ssl_asset_filters', $filters );

		foreach ( $filters as $filter ) {
			add_filter( $filter, array( $this, 'cl_enforced_ssl_asset_filter' ), 1 );
		}
	}


	/**
	 * Filter filters and convert http to https
	 *
	 * @since 2.0
	 * @param mixed $content
	 * @return mixed
	 */
	function cl_enforced_ssl_asset_filter( $content ) {

		if ( is_array( $content ) ) {

			$content = array_map( 'cl_enforced_ssl_asset_filter', $content );
		} else {

			// Detect if URL ends in a common domain suffix. We want to only affect assets
			$extension = untrailingslashit( cl_get_file_extension( $content ) );
			$suffixes  = array(
				'br',
				'ca',
				'cn',
				'com',
				'de',
				'dev',
				'edu',
				'fr',
				'in',
				'info',
				'jp',
				'local',
				'mobi',
				'name',
				'net',
				'nz',
				'org',
				'ru',
			);

			if ( ! in_array( $extension, $suffixes ) ) {

				$content = str_replace( 'http:', 'https:', $content );
			}
		}

		return $content;
	}

	/**
	 * Given a number and algorithem, determine if we have a valid credit card format
	 *
	 * @since  2.4
	 * @param  integer $number The Credit Card Number to validate
	 * @return bool            If the card number provided matches a specific format of a valid card
	 */
	function cl_validate_card_number_format( $number = 0 ) {

		$number = trim( $number );
		if ( empty( $number ) ) {
			return false;
		}

		if ( ! is_numeric( $number ) ) {
			return false;
		}

		$is_valid_format = false;

		// First check if it passes with the passed method, Luhn by default
		$is_valid_format = cl_validate_card_number_format_luhn( $number );

		// Run additional checks before we start the regexing and looping by type
		$is_valid_format = apply_filters( 'cl_valiate_card_format_pre_type', $is_valid_format, $number );

		if ( true === $is_valid_format ) {
			// We've passed our method check, onto card specific checks
			$card_type       = cl_detect_cc_type( $number );
			$is_valid_format = ! empty( $card_type ) ? true : false;
		}

		return apply_filters( 'cl_cc_is_valid_format', $is_valid_format, $number );
	}

	/**
	 * Validate credit card number based on the luhn algorithm
	 *
	 * @since  2.4
	 * @param string $number
	 * @return bool
	 */
	function cl_validate_card_number_format_luhn( $number ) {

		// Strip any non-digits (useful for credit card numbers with spaces and hyphens)
		$number = preg_replace( '/\D/', '', $number );

		// Set the string length and parity
		$length = strlen( $number );
		$parity = $length % 2;

		// Loop through each digit and do the math
		$total = 0;
		for ( $i = 0; $i < $length; $i++ ) {
			$digit = $number[ $i ];

			// Multiply alternate digits by two
			if ( $i % 2 == $parity ) {
				$digit *= 2;

				// If the sum is two digits, add them together (in effect)
				if ( $digit > 9 ) {
					$digit -= 9;
				}
			}

			// Total up the digits
			$total += $digit;
		}

		// If the total mod 10 equals 0, the number is valid
		return ( $total % 10 == 0 ) ? true : false;
	}

	/**
	 * Detect credit card type based on the number and return an
	 * array of data to validate the credit card number
	 *
	 * @since  2.4
	 * @param string $number
	 * @return string|bool
	 */
	function cl_detect_cc_type( $number ) {

		$return = false;

		$card_types = array(
			array(
				'name'         => 'amex',
				'pattern'      => '/^3[4|7]/',
				'valid_length' => array( 15 ),
			),
			array(
				'name'         => 'diners_club_carte_blanche',
				'pattern'      => '/^30[0-5]/',
				'valid_length' => array( 14 ),
			),
			array(
				'name'         => 'diners_club_international',
				'pattern'      => '/^36/',
				'valid_length' => array( 14 ),
			),
			array(
				'name'         => 'jcb',
				'pattern'      => '/^35(2[89]|[3-8][0-9])/',
				'valid_length' => array( 16 ),
			),
			array(
				'name'         => 'laser',
				'pattern'      => '/^(6304|670[69]|6771)/',
				'valid_length' => array( 16, 17, 18, 19 ),
			),
			array(
				'name'         => 'visa_electron',
				'pattern'      => '/^(4026|417500|4508|4844|491(3|7))/',
				'valid_length' => array( 16 ),
			),
			array(
				'name'         => 'visa',
				'pattern'      => '/^4/',
				'valid_length' => array( 16 ),
			),
			array(
				'name'         => 'mastercard',
				'pattern'      => '/^5[1-5]/',
				'valid_length' => array( 16 ),
			),
			array(
				'name'         => 'maestro',
				'pattern'      => '/^(5018|5020|5038|6304|6759|676[1-3])/',
				'valid_length' => array( 12, 13, 14, 15, 16, 17, 18, 19 ),
			),
			array(
				'name'         => 'discover',
				'pattern'      => '/^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/',
				'valid_length' => array( 16 ),
			),
		);

		$card_types = apply_filters( 'cl_cc_card_types', $card_types );

		if ( ! is_array( $card_types ) ) {
			return false;
		}

		foreach ( $card_types as $card_type ) {

			if ( preg_match( $card_type['pattern'], $number ) ) {

				$number_length = strlen( $number );
				if ( in_array( $number_length, $card_type['valid_length'] ) ) {
					$return = $card_type['name'];
					break;
				}
			}
		}

		return apply_filters( 'cl_cc_found_card_type', $return, $number, $card_types );
	}

	/**
	 * Validate credit card expiration date
	 *
	 * @since  2.4
	 * @param string $exp_month
	 * @param string $exp_year
	 * @return bool
	 */
	function cl_purchase_form_validate_cc_exp_date( $exp_month, $exp_year ) {

		$month_name = date( 'M', mktime( 0, 0, 0, $exp_month, 10 ) );
		$expiration = strtotime( date( 't', strtotime( $month_name . ' ' . $exp_year ) ) . ' ' . $month_name . ' ' . $exp_year . ' 11:59:59PM' );

		return $expiration >= time();
	}


	function cl_default_cc_address_fields() {
		$logged_in = is_user_logged_in();
		$customer  = WPERECCP()->front->session->get( 'customer' );
		$customer  = wp_parse_args(
			$customer,
			array(
				'address' => array(
					'line1'   => '',
					'line2'   => '',
					'city'    => '',
					'zip'     => '',
					'state'   => '',
					'country' => '',
				),
			)
		);

		$customer['address'] = array_map( 'cl_sanitization', $customer['address'] );

		if ( $logged_in ) {

			$user_address = get_user_meta( get_current_user_id(), '_cl_user_address', true );

			foreach ( $customer['address'] as $key => $field ) {

				if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
					$customer['address'][ $key ] = $user_address[ $key ];
				} else {
					$customer['address'][ $key ] = '';
				}
			}
		}

		/**
		 * Billing Address Details.
		 *
		 * Allows filtering the customer address details that will be pre-populated on the checkout form.
		 *
		 * @since 2.8
		 *
		 * @param array $address The customer address.
		 * @param array $customer The customer data from the session
		 */
		$customer['address'] = apply_filters( 'cl_checkout_billing_details_address', $customer['address'], $customer );

		ob_start();
		?>
		<fieldset id="cl_cc_address" class="cc-address">
			<legend><?php _e( 'Billing Details', 'essential-wp-real-estate' ); ?></legend>
			<?php do_action( 'cl_cc_billing_top' ); ?>
			<p id="cl-card-address-wrap">
				<label for="card_address" class="cl-label">
					<?php _e( 'Billing Address', 'essential-wp-real-estate' ); ?>
					<?php if ( $this->cl_field_is_required( 'card_address' ) ) { ?>
						<span class="cl-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="cl-description"><?php _e( 'The primary billing address for your credit card.', 'essential-wp-real-estate' ); ?></span>
				<input type="text" id="card_address" name="card_address" class="card-address cl-input
				<?php
				if ( $this->cl_field_is_required( 'card_address' ) ) {
					echo ' required';
				}
				?>
				" placeholder="<?php _e( 'Address line 1', 'essential-wp-real-estate' ); ?>" value="<?php echo esc_attr( $customer['address']['line1'] ); ?>" 
						<?php
						if ( $this->cl_field_is_required( 'card_address' ) ) {
							 echo ' required ';
						}
						?>
				/>
			</p>
			<p id="cl-card-address-2-wrap">
				<label for="card_address_2" class="cl-label">
					<?php _e( 'Billing Address Line 2 (optional)', 'essential-wp-real-estate' ); ?>
					<?php if ( $this->cl_field_is_required( 'card_address_2' ) ) { ?>
						<span class="cl-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="cl-description"><?php _e( 'The suite, apt no, PO box, etc, associated with your billing address.', 'essential-wp-real-estate' ); ?></span>
				<input type="text" id="card_address_2" name="card_address_2" class="card-address-2 cl-input
				<?php
				if ( $this->cl_field_is_required( 'card_address_2' ) ) {
					echo ' required';
				}
				?>
				" placeholder="<?php _e( 'Address line 2', 'essential-wp-real-estate' ); ?>" value="<?php echo esc_attr( $customer['address']['line2'] ); ?>" 
				<?php
				if ( $this->cl_field_is_required( 'card_address_2' ) ) {
					echo ' required ';
				}
				?>
				/>
			</p>
			<p id="cl-card-city-wrap">
				<label for="card_city" class="cl-label">
					<?php _e( 'Billing City', 'essential-wp-real-estate' ); ?>
					<?php if ( $this->cl_field_is_required( 'card_city' ) ) { ?>
						<span class="cl-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="cl-description"><?php _e( 'The city for your billing address.', 'essential-wp-real-estate' ); ?></span>
				<input type="text" id="card_city" name="card_city" class="card-city cl-input
				<?php
				if ( $this->cl_field_is_required( 'card_city' ) ) {
					echo ' required';
				}
				?>
				" placeholder="<?php _e( 'City', 'essential-wp-real-estate' ); ?>" value="<?php echo esc_attr( $customer['address']['city'] ); ?>" 
				<?php
				if ( $this->cl_field_is_required( 'card_city' ) ) {
					echo ' required ';
				}
				?>
				/>
			</p>
			<p id="cl-card-zip-wrap">
				<label for="card_zip" class="cl-label">
					<?php _e( 'Billing Zip / Postal Code', 'essential-wp-real-estate' ); ?>
					<?php if ( $this->cl_field_is_required( 'card_zip' ) ) { ?>
						<span class="cl-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="cl-description"><?php _e( 'The zip or postal code for your billing address.', 'essential-wp-real-estate' ); ?></span>
				<input type="text" size="4" id="card_zip" name="card_zip" class="card-zip cl-input
				<?php
				if ( $this->cl_field_is_required( 'card_zip' ) ) {
					echo ' required';
				}
				?>
				" placeholder="<?php _e( 'Zip / Postal Code', 'essential-wp-real-estate' ); ?>" value="<?php echo esc_attr( $customer['address']['zip'] ); ?>" 
					<?php
					if ( $this->cl_field_is_required( 'card_zip' ) ) {
						echo ' required ';
					}
					?>
				/>
			</p>
			<p id="cl-card-country-wrap">
				<label for="billing_country" class="cl-label">
					<?php _e( 'Billing Country', 'essential-wp-real-estate' ); ?>
					<?php if ( $this->cl_field_is_required( 'billing_country' ) ) { ?>
						<span class="cl-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="cl-description"><?php _e( 'The country for your billing address.', 'essential-wp-real-estate' ); ?></span>
				<select name="billing_country" id="billing_country" data-nonce="<?php echo wp_create_nonce( 'cl-country-field-nonce' ); ?>" class="billing_country cl-select 
																						   <?php
																							if ( $this->cl_field_is_required( 'billing_country' ) ) :
																								echo ' required';
endif;
																							?>
				" 
																																											<?php
																																											if ( $this->cl_field_is_required( 'billing_country' ) ) {
																																												echo ' required ';
																																											}
																																											?>
			>
					<?php
					$selected_country = WPERECCP()->front->country->cl_get_shop_country();

					if ( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
						$selected_country = $customer['address']['country'];
					}

					$countries = WPERECCP()->front->country->cl_get_country_list();
					foreach ( $countries as $country_code => $country ) {
						echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . esc_html( $country ) . '</option>';
					}
					?>
				</select>
			</p>
			<p id="cl-card-state-wrap">
				<label for="card_state" class="cl-label">
					<?php _e( 'Billing State / Province', 'essential-wp-real-estate' ); ?>
					<?php if ( $this->cl_field_is_required( 'card_state' ) ) { ?>
						<span class="cl-required-indicator">*</span>
					<?php } ?>
				</label>
				<span class="cl-description"><?php _e( 'The state or province for your billing address.', 'essential-wp-real-estate' ); ?></span>
				<?php
				$selected_state = WPERECCP()->front->country->cl_get_shop_state();
				$states         = WPERECCP()->front->country->cl_get_shop_states( $selected_country );

				if ( ! empty( $customer['address']['state'] ) ) {
					$selected_state = $customer['address']['state'];
				}

				if ( ! empty( $states ) ) :
					?>
					<select name="card_state" id="card_state" class="card_state cl-select
					<?php
					if ( $this->cl_field_is_required( 'card_state' ) ) {
																								echo ' required';
					}
					?>
																							">
						<?php
						foreach ( $states as $state_code => $state ) {
							echo '<option value="' . esc_attr( $state_code ) . '"' . selected( $state_code, $selected_state, false ) . '>' . esc_html( $state ) . '</option>';
						}
						?>
					</select>
				<?php else : ?>
					<?php $customer_state = ! empty( $customer['address']['state'] ) ? $customer['address']['state'] : ''; ?>
					<input type="text" size="6" name="card_state" id="card_state" class="card_state cl-input" value="<?php echo esc_attr( $customer_state ); ?>" placeholder="<?php _e( 'State / Province', 'essential-wp-real-estate' ); ?>" />
				<?php endif; ?>
			</p>
			<?php do_action( 'cl_cc_billing_bottom' ); ?>
			<?php wp_nonce_field( 'cl-checkout-address-fields', 'cl-checkout-address-fields-nonce', false, true ); ?>
		</fieldset>
		<?php
		echo ob_get_clean();
	}


	/**
	 * Renders the billing address fields for cart taxation
	 *
	 * @since 1.6
	 * @return void
	 */
	function cl_checkout_tax_fields() {
		if ( WPERECCP()->front->tax->cl_cart_needs_tax_address_fields() && WPERECCP()->front->cart->cl_get_cart_total() ) {
			cl_default_cc_address_fields();
		}
	}
}
