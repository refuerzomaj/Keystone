<?php
/**
 * Buy Now: Functions
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Checks to see if "Buy Now" is enabled.
 *
 * @since 2.8
 * @return boolean
 */
function cls_buy_now_is_enabled() {
	if ( false === cls_is_gateway_active() ) {
		return false;
	}

	// Check if the shop supports Buy Now.
	$shop_supports = WPERECCP()->front->gateways->cl_shop_supports_buy_now();

	if ( false === $shop_supports ) {
		return false;
	}

	// Check if guest checkout is disabled and the user is not logged in.
	if ( cl_logged_in_only() && ! is_user_logged_in() ) {
		return false;
	}

	return true;
}

/**
 * Allows "Buy Now" support if `stripe` and `stripe-prb` (Express Checkout) are
 * the only two active gateways and taxes are not enabled.
 *
 * @since 2.8
 * @param boolean $supports Whether the shop supports Buy Now .
 * @return boolean
 */
function cls_shop_supports_buy_now( $supports ) {

	if ( WPERECCP()->front->tax->cl_use_taxes() ) {
		return false;
	}

	$gateways        = WPERECCP()->front->gateways->cl_get_enabled_payment_gateways();
	$stripe_gateways = array( 'stripe', 'stripe-prb' );
	if ( empty( array_diff( array_keys( $gateways ), $stripe_gateways ) ) ) {
		return true;
	}

	return $supports;
}
add_filter( 'cl_shop_supports_buy_now', 'cls_shop_supports_buy_now' );
