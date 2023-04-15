<?php
/**
 * Bootstraps and outputs notices.
 *
 * @package CL_Stripe
 * @since   2.6.19
 */

/**
 * Registers scripts to manage dismissing notices.
 *
 * @since 2.6.19
 */
function cls_admin_notices_scripts() {
	wp_register_script(
		'cls-admin-notices',
		CLS_PLUGIN_URL . 'assets/js/build/notices.min.js',
		array(
			'wp-util',
			'jquery',
		),
		CL_STRIPE_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'cls_admin_notices_scripts' );

/**
 * Registers admin notices.
 *
 * @since 2.6.19
 *
 * @return true|WP_Error True if all notices are registered, otherwise WP_Error.
 */
function cls_admin_notices_register() {
	 $registry = cls_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return new WP_Error( 'cls-invalid-registry', esc_html__( 'Unable to locate registry', 'essential-wp-real-estate' ) );
	}

	try {
		// PHP
		$registry->add(
			'php-requirement',
			array(
				'message'     => function () {
					ob_start();
					require_once CLS_PLUGIN_DIR . '/includes/admin/notices/php-requirement.php';
					return ob_get_clean();
				},
				'type'        => 'error',
				'dismissible' => false,
			)
		);

		$registry->add(
			'cl-requirement',
			array(
				'message'     => function () {
					ob_start();
					require_once CLS_PLUGIN_DIR . '/includes/admin/notices/cl-requirement.php';
					return ob_get_clean();
				},
				'type'        => 'error',
				'dismissible' => false,
			)
		);

		// Recurring requirement.
		$registry->add(
			'cl-recurring-requirement',
			array(
				'message'     => function () {
					ob_start();
					require_once CLS_PLUGIN_DIR . '/includes/admin/notices/cl-recurring-requirement.php';
					return ob_get_clean();
				},
				'type'        => 'error',
				'dismissible' => false,
			)
		);

	} catch ( Exception $e ) {
		return new WP_Error(
			'cls-invalid-notices-registration',
			esc_html( $e->getMessage() )
		);
	};

	return true;
}
// add_action('admin_init', 'cls_admin_notices_register');

/**
 * Conditionally prints registered notices.
 *
 * @since 2.6.19
 */
function cls_admin_notices_print() {
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
		// PHP 5.6 requirement.
		if (
			false === cls_has_met_requirements( 'php' ) &&
			true === cls_is_pro()
		) {
			$notices->output( 'php-requirement' );
		}

		if ( false === cls_has_met_requirements( 'cl' ) ) {
			$notices->output( 'cl-requirement' );
		}

		// Recurring 2.10.0 requirement.
		if ( false === cls_has_met_requirements( 'recurring' ) ) {
			$notices->output( 'cl-recurring-requirement' );
		}

		// Stripe in Core notice.
		if ( false === cls_is_pro() && false === cls_is_gateway_active( 'stripe' ) ) {
			$notices->output( 'cl-stripe-core' );
		}
	} catch ( Exception $e ) {
	}
}
add_action( 'admin_notices', 'cls_admin_notices_print' );

/**
 * Handles AJAX dismissal of notices.
 *
 * WordPress automatically removes the notices, so the response here is arbitrary.
 * If the notice cannot be dismissed it will simply reappear when the page is refreshed.
 *
 * @since 2.6.19
 */
function cls_admin_notices_dismiss_ajax() {
	$notice_id = isset( $_REQUEST['id'] ) ? cl_sanitization( $_REQUEST['id'] ) : false;
	$nonce     = isset( $_REQUEST['nonce'] ) ? cl_sanitization( $_REQUEST['nonce'] ) : false;

	if ( ! ( $notice_id && $nonce ) ) {
		return wp_send_json_error();
	}

	if ( ! wp_verify_nonce( $nonce, "cls-dismiss-{$notice_id}-nonce" ) ) {
		return wp_send_json_error();
	}

	$registry = cls_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return wp_send_json_error();
	}

	$notices   = new CL_Stripe_Admin_Notices( $registry );
	$dismissed = $notices->dismiss( $notice_id );

	if ( true === $dismissed ) {
		return wp_send_json_success();
	} else {
		return wp_send_json_error();
	}
}
add_action( 'wp_ajax_cls_admin_notices_dismiss_ajax', 'cls_admin_notices_dismiss_ajax' );
