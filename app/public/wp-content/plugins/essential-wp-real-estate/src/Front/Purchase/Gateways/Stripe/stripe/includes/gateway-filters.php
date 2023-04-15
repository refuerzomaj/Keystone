<?php
/**
 * Removes Stripe from active gateways if application requirements are not met.
 *
 * @since 2.8.1
 *
 * @param array $enabled_gateways Enabled gateways that allow purchasing.
 * @return array
 */
function cls_validate_gateway_requirements( $enabled_gateways ) {
	if ( false === cls_has_met_requirements() ) {
		unset( $enabled_gateways['stripe'] );
	}

	return $enabled_gateways;
}
// add_filter('cl_enabled_payment_gateways', 'cls_validate_gateway_requirements', 20);

/**
 * Injects the Stripe token and customer email into the pre-gateway data
 *
 * @since 2.0
 *
 * @param array $purchase_data
 * @return array
 */
function cl_stripe_straight_to_gateway_data( $purchase_data ) {

	$gateways = WPERECCP()->front->gateways->cl_get_enabled_payment_gateways();

	if ( isset( $gateways['stripe'] ) ) {
		$_REQUEST['cl-gateway']   = 'stripe';
		$purchase_data['gateway'] = 'stripe';
	}

	return $purchase_data;
}
add_filter( 'cl_straight_to_gateway_purchase_data', 'cl_stripe_straight_to_gateway_data' );

/**
 * Process the POST Data for the Credit Card Form, if a token wasn't supplied
 *
 * @since  2.2
 * @return array The credit card data from the $_POST
 */
function cls_process_post_data( $purchase_data ) {

	if ( ! isset( $purchase_data['gateway'] ) || 'stripe' !== $purchase_data['gateway'] ) {
		return;
	}

	if ( isset( $_POST['cl_stripe_existing_card'] ) && 'new' !== $_POST['cl_stripe_existing_card'] ) {

		return;
	}

	// Require a name for new cards.
	if ( ! isset( $_POST['card_name'] ) || strlen( trim( $_POST['card_name'] ) ) === 0 ) {

		WPERECCP()->front->error->cl_set_error( 'no_card_name', __( 'Please enter a name for the credit card.', 'essential-wp-real-estate' ) );
	}
}

add_action( 'cl_checkout_error_checks', 'cls_process_post_data' );

/**
 * Retrieves the locale used for Checkout modal window
 *
 * @since  2.5
 * @return string The locale to use
 */
function cls_get_stripe_checkout_locale() {
	 return apply_filters( 'cl_stripe_checkout_locale', 'auto' );
}

/**
 * Sets the $_COOKIE global when a logged in cookie is available.
 *
 * We need the global to be immediately available so calls to wp_create_nonce()
 * within the same session will use the newly available data.
 *
 * @since 2.8.0
 *
 * @link https://wordpress.stackexchange.com/a/184055
 *
 * @param string $logged_in_cookie The logged-in cookie value.
 */
function cls_set_logged_in_cookie_global( $logged_in_cookie ) {
	$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
}
