<?php
/**
 * Payment Request Button: Template
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Outputs a Payment Request Button via `cl_get_purchase_link()`
 * (which is implemented via [purchase_link])
 *
 * @since 2.8.0
 *
 * @param int   $listing_id Current listing ID.
 * @param array $args Arguments for displaying the purchase link.
 */
function cls_prb_purchase_link( $listing_id, $args ) {
	// Don't output if context is not enabled.
	$context = is_singular() && 0 === did_action( 'cl_listings_list_before' )
		? 'single'
		: 'archive';

	if ( false === cls_prb_is_enabled( $context ) ) {
		return;
	}

	// Don't output if the item is free. Stripe won't process < $0.50
	if ( true === cl_is_free_listing( $listing_id ) ) {
		return;
	}

	// Don't output if the item is already in the cart.
	if ( true === cl_item_in_cart( $listing_id ) ) {
		return;
	}

	// Don't output if `cl_get_purchase_link` is filtered to disable.
	if (
		isset( $args['payment-request'] ) &&
		true !== cls_truthy_to_bool( $args['payment-request'] )
	) {
		return;
	}

	// Don't output if Recurring is enabled, and a free trial is present.
	if ( function_exists( 'cl_recurring' ) ) {
		// Don't output if non-variable price has a free trial.
		if ( cl_recurring()->has_free_trial( $listing_id ) ) {
			return;
		}

		// ...or if a variable price options has a free trial.
		$prices = cl_get_variable_prices( $listing_id );

		if ( ! empty( $prices ) ) {
			foreach ( $prices as $price ) {
				if ( cl_recurring()->has_free_trial( $listing_id, $price['index'] ) ) {
					return;
				}
			}
		}
	}

	// Don't output if it has been filtered off for any reason.
	$enabled = true;

	/**
	 * Filters the output of Payment Request Button in purchase links.
	 *
	 * @since 2.8.0
	 *
	 * @param bool  $enabled If the Payment Request Button is enabled.
	 * @param int   $listing_id Current listing ID.
	 * @param array $args Purchase link arguments.
	 */
	$enabled = apply_filters( 'cls_prb_purchase_link_enabled', $enabled, $listing_id, $args );

	if ( true !== $enabled ) {
		return;
	}

	static $instance_id = 0;

	echo cls_get_prb_markup(
		cls_prb_get_listing_data( $listing_id ),
		array(
			'id'      => sprintf(
				'cls-prb-listing-%d-%d',
				$listing_id,
				$instance_id
			),
			'classes' => array(
				'cls-prb--listing',
			),
		)
	); // WPCS: XSS okay.

	// Shim the Checkout processing nonce.
	wp_nonce_field( 'cl-process-checkout', 'cl-process-checkout-nonce', false );

	$instance_id++;
}
add_action( 'cl_purchase_link_top', 'cls_prb_purchase_link', 20, 2 );

/**
 * Outputs a Payment Request Button on the Checkout.
 *
 * @since 2.8.0
 */
function cls_prb_checkout_button_purchase( $button ) {
	// Do nothing if Payment Requests are not enabled.
	if ( false === cls_prb_is_enabled( 'checkout' ) ) {
		return $button;
	}

	$errors = '<div id="cls-prb-error-wrap"></div>';

	$button = cls_get_prb_markup(
		cls_prb_get_cart_data(),
		array(
			'id'      => 'cls-prb-checkout',
			'classes' => array(
				'cls-prb--checkout',
			),
		)
	);

	return $errors . $button;
}

/**
 * Retrieves HTML used to mount a Payment Request Button.
 *
 * @since 2.8.0
 * @see cls_prb_get_listing_data()
 * @link https://stripe.com/docs/js/appendix/payment_item_object
 *
 * @param PaymentItem[] $data {
 *   PaymentItems.
 *
 *   @type int    $amount The amount in the currency's subunit.
 *   @type string $label A name the browser shows the customer in the payment interface.
 * }
 * @param array         $args {
 *           Mount arguments.
 *
 *   @type string $id HTML ID attribute.
 *   @type array  $classes HTML classes.
 * }
 * @return string
 */
function cls_get_prb_markup( $data, $args = array() ) {
	$defaults = array(
		'id'      => '',
		'classes' => array(),
	);

	$args = wp_parse_args( $args, $defaults );

	// ID/Class
	$id    = $args['id'];
	$class = implode(
		' ',
		array_merge(
			$args['classes'],
			array(
				'cls-prb',
			)
		)
	);

	// Data
	$_data = array();

	foreach ( $data as $key => $value ) {
		$_data[] = sprintf(
			'data-%s="%s"',
			esc_attr( $key ),
			esc_attr( is_array( $value ) ? wp_json_encode( $value ) : $value )
		);
	}

	$_data = implode( ' ', $_data );

	cl_stripe_js( true );
	cl_stripe_css( true );

	return sprintf(
		'<div id="%1$s" class="%2$s" %3$s>
			<div class="cls-prb__button"></div>
			<div class="cls-prb__or">%4$s</div>
		</div>',
		esc_attr( $id ),
		esc_attr( $class ),
		$_data,
		esc_html_x( 'or', 'payment request button divider', 'essential-wp-real-estate' )
	);
}
