<?php
/**
 * Payment Request: Functions
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Determines if Payment Requests are enabled.
 *
 * @since 2.8.0
 *
 * @param array|string $context Context the Payment Request Button is being output in.
 *                              Default empty, checks if any are enabled.
 * @return bool
 */
function cls_prb_is_enabled( $context = array() ) {
	// Stripe gateway is not active. Disabled.
	if ( false === cls_is_gateway_active( 'stripe' ) ) {
		return false;
	}

	// Gather allowed and enabled contexts.
	$allowed_contexts = array( 'single', 'archive', 'checkout' );
	$enabled_contexts = array_keys(
		(array) cl_admin_get_option( 'stripe_prb', array() )
	);

	if ( ! is_array( $context ) ) {
		$context = array( $context );
	}

	// Nothing particular is being checked for; check if any values are checked.
	if ( empty( $context ) ) {
		return count( $enabled_contexts ) > 0;
	}

	// Passed context is not allowed. Disabled.
	if ( 0 === count( array_intersect( $context, $allowed_contexts ) ) ) {
		return false;
	}

	// Passed context is not enabled in setting. Disabled.
	if ( 0 === count( array_intersect( $context, $enabled_contexts ) ) ) {
		return false;
	}

	// Taxes are enabled. Disabled.
	$taxes = WPERECCP()->front->tax->cl_use_taxes();

	if ( true === $taxes ) {
		return false;
	}

	// Recurring is enabled and a trial is in the cart. Disabled.
	//
	// Disabling for cart context here to avoid further adjusting the already
	// complex filtering of active gateways in checkout.php
	if (
		function_exists( 'cl_recurring' ) &&
		cl_recurring()->cart_has_free_trial()
	) {
		return false;
	}

	return true;
}

/**
 * Retrieves data for a Payment Request Button for a single listing.
 *
 * @since 2.8.0
 *
 * @param int       $listing_id listing ID.
 * @param false|int $price_id Price ID. Default will be used if not set. Default false.
 * @param int       $quantity Quantity. Default 1.
 * @return array Payment Request Button data.
 */
function cls_prb_get_listing_data( $listing_id, $price_id = false, $quantity = 1 ) {
	$data = array(
		'currency'      => strtolower( WPERECCP()->common->options->cl_get_currency() ),
		'country'       => strtoupper( WPERECCP()->front->country->cl_get_shop_country() ),
		'total'         => array(),
		'display-items' => array(),
	);

	$listing = cl_get_listing( $listing_id );

	// Return early if no listing can be found.
	if ( ! $listing ) {
		return array();
	}

	// Hacky way to ensure we don't display quantity for Recurring
	// listings. The quantity field is output incorrectly.
	//
	if ( defined( 'CL_RECURRING_VERSION' ) ) {
		$recurring = false;

		if ( false !== $price_id ) {
			$recurring = cl_recurring()->is_price_recurring( $listing_id, $price_id );
		} else {
			$recurring = cl_recurring()->is_recurring( $listing_id );
		}

		if ( true === $recurring ) {
			$quantity = 1;
		}
	}

	// Find price.
	$variable_pricing = $listing->has_variable_prices();
	$price            = 0;

	if ( $variable_pricing ) {
		if ( false === $price_id ) {
			$price_id = cl_get_default_variable_price( $listing_id );
		}

		$prices = $listing->prices;

		$price = isset( $prices[ $price_id ] )
			? $prices[ $price_id ]['amount']
			: false;

		$name = sprintf(
			'%1$s - %2$s',
			$listing->get_name(),
			WPERECCP()->front->listingsaction->cl_get_price_option_name( $listing->ID, $price_id )
		);
	} else {
		$price = $listing->price;
		$name  = $listing->get_name();
	}

	if ( false === cls_is_zero_decimal_currency() ) {
		$price = round( $price * 100 );
	}

	$price = ( $price * $quantity );

	// Add total.
	$data['total'] = array(
		'label'  => __( 'Total', 'essential-wp-real-estate' ),
		'amount' => $price,
	);

	// Add Display items.
	$has_quantity = cl_item_quantities_enabled() && ! cl_listing_quantities_disabled( $listing_id );

	$quantity = true === $has_quantity
		? $quantity
		: 1;

	$data['display-items'][] = array(
		'label'  => sprintf(
			'%s%s',
			strip_tags( $name ),
			( $quantity > 1 ? sprintf( __( ' × %d', 'essential-wp-real-estate' ), $quantity ) : '' )
		),
		'amount' => $price,
	);

	return $data;
}

/**
 * Retrieves data for a Payment Request Button for the cart.
 *
 * @since 2.8.0
 *
 * @return array Payment Request Button data.
 */
function cls_prb_get_cart_data() {
	$data = array(
		'currency'      => strtolower( WPERECCP()->common->options->cl_get_currency() ),
		'country'       => strtoupper( WPERECCP()->front->country->cl_get_shop_country() ),
		'total'         => array(),
		'display-items' => array(),
	);

	$total = WPERECCP()->front->cart->cl_get_cart_total();

	if ( false === cls_is_zero_decimal_currency() ) {
		$total = round( $total * 100 );
	}

	// Add total.
	$data['total'] = array(
		'label'  => __( 'Total', 'essential-wp-real-estate' ),
		'amount' => $total,
	);

	// Add Display items.
	$cart_items = cl_get_cart_contents();

	foreach ( $cart_items as $key => $item ) {
		$has_quantity = cl_item_quantities_enabled() && ! cl_listing_quantities_disabled( $item['id'] );

		$quantity = true === $has_quantity
			? cl_get_cart_item_quantity( $item['id'], $item['options'] )
			: 1;

		$price = cl_get_cart_item_price( $item['id'], $item['options'] );

		if ( false === cls_is_zero_decimal_currency() ) {
			$price = round( $price * 100 );
		}

		$price = ( $price * $quantity );

		$data['display-items'][] = array(
			'label'  => sprintf(
				'%s%s',
				strip_tags( cl_get_cart_item_name( $item ) ),
				( $quantity > 1 ? sprintf( __( ' × %d', 'essential-wp-real-estate' ), $quantity ) : '' )
			),
			'amount' => $price,
		);
	}

	return $data;
}
