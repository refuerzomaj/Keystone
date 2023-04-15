<?php
/**
 * Payment Request: Apple Pay
 *
 * @link https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Registers admin notices.
 *
 * @since 2.8.0
 *
 * @return true|WP_Error True if all notices are registered, otherwise WP_Error.
 */
function cls_prb_apple_pay_admin_notices_register() {
	$registry = cls_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return new WP_Error( 'cls-invalid-registry', esc_html__( 'Unable to locate registry', 'essential-wp-real-estate' ) );
	}

	try {
		// General error message.
		$message = ( '<strong>' . esc_html__( 'Apple Pay domain verification error.', 'essential-wp-real-estate' ) . '</strong><br />' .
			cl_admin_get_option( 'stripe_prb_apple_pay_domain_error', '' )
		);

		$registry->add(
			'apple-pay-' . cl_sanitization( $_SERVER['HTTP_HOST'] ),
			array(
				'message'     => wp_kses(
					wpautop( $message ),
					array(
						'code'   => true,
						'br'     => true,
						'strong' => true,
						'p'      => true,
						'a'      => array(
							'href'   => true,
							'rel'    => true,
							'target' => true,
						),
					)
				),
				'type'        => 'error',
				'dismissible' => true,
			)
		);
	} catch ( Exception $e ) {
		return new WP_Error( 'cls-invalid-notices-registration', esc_html( $e->getMessage() ) );
	};

	return true;
}
add_action( 'admin_init', 'cls_prb_apple_pay_admin_notices_register', 30 );

/**
 * Conditionally prints registered notices.
 *
 * @since 2.8.0
 */
function cls_prb_apple_pay_admin_notices_print() {
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
		$error     = cl_admin_get_option( 'stripe_prb_apple_pay_domain_error', '' );
		$test_mode = cl_is_test_mode();

		if ( ! empty( $error ) && false === $test_mode ) {
			$notices->output( 'apple-pay-' . cl_sanitization( $_SERVER['HTTP_HOST'] ) );
		}
	} catch ( Exception $e ) {
	}
}
add_action( 'admin_notices', 'cls_prb_apple_pay_admin_notices_print' );

/**
 * Returns information associated with the name/location of the domain verification file.
 *
 * @since 2.8.0
 *
 * @return array Domain verification file information.
 */
function cls_prb_apple_pay_get_fileinfo() {
	$path = untrailingslashit( cl_sanitization( $_SERVER['DOCUMENT_ROOT'] ) );
	$dir  = '.well-known';
	$file = 'apple-developer-merchantid-domain-association';

	return array(
		'path'     => $path,
		'dir'      => $dir,
		'file'     => $file,
		'fullpath' => $path . '/' . $dir . '/' . $file,
	);
}

/**
 * Determines if the current website is setup to use Apple Pay.
 *
 * @since 2.8.0
 *
 * @return bool True if the domain has been verified and the association file exists.
 */
function cls_prb_apple_pay_is_valid() {
	 return ( cls_prb_apple_pay_has_domain_verification_file() &&
		cls_prb_apple_pay_has_domain_verification()
	);
}

/**
 * Determines if the domain verification file already exists.
 *
 * @since 2.8.0
 *
 * @return bool True if the domain verification file exists.
 */
function cls_prb_apple_pay_has_domain_verification_file() {
	 $fileinfo = cls_prb_apple_pay_get_fileinfo();

	if ( ! @file_exists( $fileinfo['fullpath'] ) ) {
		return false;
	}

	return true;
}

/**
 * Determines if the currently verified domain matches the current site.
 *
 * @since 2.8.0
 *
 * @return bool True if the saved verified domain matches the current site.
 */
function cls_prb_apple_pay_has_domain_verification() {
	return cl_admin_get_option( 'stripe_prb_apple_pay_domain' ) === $_SERVER['HTTP_HOST'];
}

/**
 * Attempts to create a directory in the server root and copy the domain verification file.
 *
 * @since 2.8.0
 *
 * @throws \Exception If the directory or file cannot be created.
 */
function cls_prb_apple_pay_create_directory_and_move_file() {
	$file = cls_prb_apple_pay_has_domain_verification_file();

	if ( true === $file ) {
		return;
	}

	$fileinfo = cls_prb_apple_pay_get_fileinfo();

	// Create directory if it does not exist.
	if ( ! file_exists( trailingslashit( $fileinfo['path'] ) . $fileinfo['dir'] ) ) {
		if (!@mkdir(trailingslashit($fileinfo['path']) . $fileinfo['dir'], 0755)) { // @codingStandardsIgnoreLine
			throw new \Exception( __( 'Unable to create domain association folder in domain root.', 'essential-wp-real-estate' ) );
		}
	}

	// Move file if needed.
	if ( ! cls_prb_apple_pay_has_domain_verification_file() ) {
		if (!@copy(trailingslashit(CLS_PLUGIN_DIR) . $fileinfo['file'], $fileinfo['fullpath'])) { // @codingStandardsIgnoreLine
			throw new \Exception( __( 'Unable to copy domain association file to domain .well-known directory.', 'essential-wp-real-estate' ) );
		}
	}
}

/**
 * Checks Apple Pay domain verification if there is an existing error.
 * If the domain was added to the Stripe Dashboard clear the error.
 *
 * @since 2.8.0
 */
function cls_prb_apple_pay_check_domain() {
	 $error = cl_admin_get_option( 'stripe_prb_apple_pay_domain_error', '' );

	if ( empty( $error ) ) {
		return;
	}

	try {
		$domains = cls_api_request( 'ApplePayDomain', 'all' );

		foreach ( $domains->autoPagingIterator() as $domain ) {
			if ( $domain->domain_name === $_SERVER['HTTP_HOST'] ) {
				cl_delete_option( 'stripe_prb_apple_pay_domain_error' );
				cl_update_option( 'stripe_prb_apple_pay_domain', cl_sanitization( $_SERVER['HTTP_HOST'] ) );
				break;
			}
		}
	} catch ( \Exception $e ) {
	}
}
add_action( 'admin_init', 'cls_prb_apple_pay_check_domain', 10 );

/**
 * Verifies the current domain.
 *
 * @since 2.8.0
 */
function cls_prb_apple_pay_verify_domain() {
	// Payment Request Button is not enabled, do nothing.
	if ( false === cls_prb_is_enabled() ) {
		return;
	}

	// Avoid getting caught in AJAX requests.
	if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) {
		return;
	}

	// Must be verified in Live Mode.
	if ( true === cl_is_test_mode() ) {
		return;
	}

	// Current site is a development environment, Apple Pay won't be able to be used, do nothing.
	if ( false !== cl_is_dev_environment() ) {
		return;
	}

	// Current domain matches and the file exists, do nothing.
	if ( true === cls_prb_apple_pay_is_valid() ) {
		return;
	}

	try {
		// Create directory and move file if needed.
		cls_prb_apple_pay_create_directory_and_move_file();

		$stripe_connect_account_id = cl_admin_get_option( 'stripe_connect_account_id', '' );

		// Automatically verify when using "real" API keys.
		if ( empty( $stripe_connect_account_id ) ) {
			$verification = cls_api_request(
				'ApplePayDomain',
				'create',
				array(
					'domain_name' => cl_sanitization( $_SERVER['HTTP_HOST'] ),
				)
			);

			cl_update_option( 'stripe_prb_apple_pay_domain', cl_sanitization( $_SERVER['HTTP_HOST'] ) );

			// Set an error that the domain needs to be manually added.
			// Using Stripe Connect API keys does not allow this to be done automatically.
		} else {
			throw new \Exception(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					( __( 'Please %1$smanually add your domain%2$s %3$s to use Apple Pay.', 'essential-wp-real-estate' ) . '<br />' ),
					'<a href="https://dashboard.stripe.com/settings/payments/apple_pay" target="_blank" rel="noopener noreferrer">',
					'</a>',
					'<code>' . cl_sanitization( $_SERVER['HTTP_HOST'] ) . '</code>'
				)
			);
		}
	} catch ( \Exception $e ) {
		// Set error if something went wrong.
		cl_update_option( 'stripe_prb_apple_pay_domain_error', $e->getMessage() );
	}
}
add_action( 'admin_init', 'cls_prb_apple_pay_verify_domain', 20 );
