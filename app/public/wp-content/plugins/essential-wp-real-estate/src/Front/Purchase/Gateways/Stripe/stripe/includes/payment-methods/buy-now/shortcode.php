<?php
/**
 * Buy Now: Shortcode
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Sets the stripe-checkout parameter if the direct parameter is present in the [purchase_link] short code.
 *
 * @since 2.0
 *
 * @param array $out
 * @param array $pairs
 * @param array $atts
 * @return array
 */
function cl_stripe_purchase_link_shortcode_atts( $out, $pairs, $atts ) {

	if ( ! cls_buy_now_is_enabled() ) {
		return $out;
	}

	$direct = false;

	// [purchase_link direct=true]
	if ( isset( $atts['direct'] ) && true === cls_truthy_to_bool( $atts['direct'] ) ) {
		$direct = true;

		// [purchase_link stripe-checkout]
	} elseif ( isset( $atts['stripe-checkout'] ) || false !== array_search( 'stripe-checkout', $atts, true ) ) {
		$direct = true;
	}

	$out['direct'] = $direct;

	if ( true === $direct ) {
		$out['stripe-checkout'] = $direct;
	} else {
		unset( $out['stripe-checkout'] );
	}

	return $out;
}
add_filter( 'shortcode_atts_purchase_link', 'cl_stripe_purchase_link_shortcode_atts', 10, 3 );

/**
 * Sets the stripe-checkout parameter if the direct parameter is present in cl_get_purchase_link()
 *
 * @since 2.0
 * @since 2.8.0 Adds `.cls-buy-now` to the class list.
 *
 * @param array $arg Purchase link shortcode attributes.
 * @return array
 */
function cl_stripe_purchase_link_atts( $args ) {
	global $cls_has_buy_now;

	if ( ! cls_buy_now_is_enabled() ) {
		return $args;
	}

	// Don't use modal if "Free listings" is active and available for this listing.
	if ( function_exists( 'cl_free_listings_use_modal' ) ) {
		if ( cl_free_listings_use_modal( $args['listing_id'] ) && ! cl_has_variable_prices( $args['listing_id'] ) ) {
			return $args;
		}
	}

	$direct = cls_truthy_to_bool( $args['direct'] );

	$args['direct'] = $direct;

	if ( true === $direct ) {
		$args['stripe-checkout'] = true;
		$args['class']          .= ' cls-buy-now';

		if ( false === cl_item_in_cart( $args['listing_id'] ) ) {
			$cls_has_buy_now = $direct;
		}
	} else {
		unset( $args['stripe-checkout'] );
	}

	return $args;
}
add_filter( 'cl_purchase_link_args', 'cl_stripe_purchase_link_atts', 10 );
