<?php
/**
 * Buy Now: AJAX
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Adds a listing to the Cart on the `cls_add_to_cart` AJAX action.
 *
 * @since 2.8.0
 */
function cls_buy_now_ajax_add_to_cart() {
	$data = cl_sanitization($_POST);

	if ( ! isset( $data['listing_id'] ) || ! isset( $data['nonce'] ) ) {
		return wp_send_json_error(
			array(
				'message' => __( 'Unable to add item to cart.', 'essential-wp-real-estate' ),
			)
		);
	}

	$listing_id = absint( $data['listing_id'] );
	$price_id   = absint( $data['price_id'] );
	$quantity   = absint( $data['quantity'] );

	$nonce       = cl_sanitization( $data['nonce'] );
	$valid_nonce = wp_verify_nonce( $nonce, 'cl-add-to-cart-' . esc_attr( $listing_id ) );

	if ( false === $valid_nonce ) {
		return wp_send_json_error(
			array(
				'message' => __( 'Unable to add item to cart.', 'essential-wp-real-estate' ),
			)
		);
	}

	// Empty cart.
	WPERECCP()->front->cart->cl_empty_cart();

	// Add individual item.
	cl_add_to_cart(
		$listing_id,
		array(
			'quantity' => $quantity,
			'price_id' => $price_id,
		)
	);

	return wp_send_json_success(
		array(
			'checkout' => cls_buy_now_checkout(),
		)
	);
}
add_action( 'wp_ajax_cls_add_to_cart', 'cls_buy_now_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_cls_add_to_cart', 'cls_buy_now_ajax_add_to_cart' );

/**
 * Empties the cart on the `cls_buy_now_empty_cart` AJAX action.
 *
 * @since 2.8.0
 */
function cls_buy_now_ajax_empty_cart() {
	WPERECCP()->front->cart->cl_empty_cart();

	return wp_send_json_success();
}
add_action( 'wp_ajax_cls_empty_cart', 'cls_buy_now_ajax_empty_cart' );
add_action( 'wp_ajax_nopriv_cls_empty_cart', 'cls_buy_now_ajax_empty_cart' );
