<?php
/**
 * Payment Request Button: AJAX
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Starts the Checkout process for a Payment Request.
 *
 * This needs to be used instead of `_cls_process_purchase_form()` so
 * Checkout form data can be shimmed or faked to prevent getting hung
 * up on things like privacy policy, terms of service, etc.
 *
 * @since 2.8.0
 */
function cls_prb_ajax_process_checkout() {
	// Clear any errors that might be used as a reason to attempt a redirect in the following action.
	WPERECCP()->front->error->cl_clear_errors();

	$listing_id = isset( $_POST['listingId'] )
		? intval( cl_sanitization( $_POST['listingId'] ) )
		: 0;

	$email = isset( $_POST['email'] )
		? sanitize_email( $_POST['email'] )
		: '';

	$name = isset( $_POST['name'] )
		? cl_sanitization( $_POST['name'] )
		: '';

	$payment_method = isset( $_POST['paymentMethod'] )
		? cl_sanitization( $_POST['paymentMethod'] )
		: '';

	$context = isset( $_POST['context'] )
		? cl_sanitization( $_POST['context'] )
		: 'checkout';

	// Add a listing to the cart if we are not processing the full cart.
	if ( 'listing' === $context ) {
		$price_id = isset( $_POST['priceId'] )
			? intval( cl_sanitization( $_POST['priceId'] ) )
			: false;

		$quantity = isset( $_POST['quantity'] )
			? intval( cl_sanitization( $_POST['quantity'] ) )
			: 1;

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

		// Refilter guest checkout when the item is added to the cart dynamically.
		// This is a duplicate of CL_Recurring_Gateway::require_login().
		if ( defined( 'CL_RECURRING_VERSION' ) ) {
			$cart_items    = cl_get_cart_contents();
			$has_recurring = false;
			$auto_register = class_exists( 'CL_Auto_Register' );

			if ( ! empty( $cart_items ) ) {
				foreach ( $cart_items as $item ) {
					if ( ! isset( $item['options']['recurring'] ) ) {
						continue;
					}

					$has_recurring = true;
				}

				if ( $has_recurring && ! $auto_register ) {
					add_filter( 'cl_no_guest_checkout', '__return_true' );
					add_filter( 'cl_logged_in_only', '__return_true' );
				}
			}
		}
	}

	try {
		$data = array(
			// Mark "sub-gateway" for Stripe. This represents the Payment Method
			// currently being used. e.g `ideal`, `wepay`, `payment-request`, etc.
			//
			// This is used to filter field requirements via `cl_pre_process_purchase` hook.
			'cls-gateway'     => 'payment-request',
			'cls-prb-context' => $context,
		);

		// Checkout-specific data.
		if ( 'checkout' === $context ) {
			$form_data = isset( $_POST['form_data'] )
				? cl_sanitization( $_POST['form_data'] )
				: array();

			// Use the Payment Method's billing details.
			$card_name = ( ! empty( $payment_method['billing_details'] ) &&
				! empty( $payment_method['billing_details']['name'] )
			)
				? $payment_method['billing_details']['name']
				: $name;

			$billing_details = ! empty( $payment_method['billing_details'] )
				? array(
					'card_name'       => $card_name,
					'card_address'    => $payment_method['billing_details']['address']['line1'],
					'card_address_2'  => $payment_method['billing_details']['address']['line2'],
					'card_city'       => $payment_method['billing_details']['address']['city'],
					'card_zip'        => $payment_method['billing_details']['address']['postal_code'],
					'billing_country' => $payment_method['billing_details']['address']['country'],
					'card_state'      => $payment_method['billing_details']['address']['state'],
				)
				: array(
					'card_name'       => $card_name,
					'card_address'    => '',
					'card_address_2'  => '',
					'card_city'       => '',
					'card_zip'        => '',
					'billing_country' => '',
					'card_state'      => '',
				);

			// Add the Payment Request's name as the card name.
			$_POST['form_data'] = add_query_arg(
				$billing_details,
				$form_data
			);

			// Single-listing data.
		} else {
			// Fake checkout form data.
			$_POST['form_data'] = http_build_query(
				array_merge(
					$data,
					array(
						// Use Email from Payment Request.
						'cl_email'                   => $email,
						'cl-user-id'                 => get_current_user_id(),
						'cl_action'                  => 'purchase',
						'cl-gateway'                 => 'stripe',
						'cl_agree_to_terms'          => '1',
						'cl_agree_to_privacy_policy' => '1',
						'cl-process-checkout-nonce'  => wp_create_nonce( 'cl-process-checkout' ),
					)
				)
			);
		}

		$_POST['payment_method_id'] = isset( $payment_method['id'] )
			? cl_sanitization( $payment_method['id'] )
			: '';

		$_POST['payment_method_exists'] = false;

		// Adjust PaymentIntent creation for PRB flow.
		add_filter( 'cls_create_payment_intent_args', 'cls_prb_create_payment_intent_args', 20 );
		add_filter( 'cls_create_setup_intent_args', 'cls_prb_create_setup_intent_args', 20 );

		// This will send a JSON response.
		_cls_process_purchase_form();
	} catch ( \Exception $e ) {
		wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_cls_prb_ajax_process_checkout', 'cls_prb_ajax_process_checkout' );
add_action( 'wp_ajax_nopriv_cls_prb_ajax_process_checkout', 'cls_prb_ajax_process_checkout' );

/**
 * Filters the arguments used when creating a PaymentIntent while
 * using a Payment Request Button.
 *
 * @since 2.8.0
 *
 * @param array $args {
 *   PaymentIntent arguments.
 *
 *   @link https://stripe.com/docs/api/payment_intents/create
 * }
 * @return array
 */
function cls_prb_create_payment_intent_args( $args ) {
	$args['confirmation_method'] = 'automatic';
	$args['confirm']             = false;
	$args['capture_method']      = 'automatic';
	$args['metadata']['cls_prb'] = '1';

	return $args;
}

/**
 * Filters the arguments used when creating a SetupIntent while
 * using a Payment Request Button.
 *
 * @since 2.8.0
 *
 * @param array $args {
 *   SetupIntent arguments.
 *
 *   @link https://stripe.com/docs/api/setup_intents/create
 * }
 * @return array
 */
function cls_prb_create_setup_intent_args( $args ) {
	$args['confirm']             = false;
	$args['metadata']['cls_prb'] = '1';

	return $args;
}

/**
 * Gathers Payment Request options based on the current context.
 *
 * @since 2.8.0
 */
function cls_prb_ajax_get_options() {
	$listing_id = isset( $_POST['listingId'] )
		? intval( cl_sanitization( $_POST['listingId'] ) )
		: 0;

	// Single listing.
	if ( ! empty( $listing_id ) ) {
		$price_id = isset( $_POST['priceId'] ) && 'false' !== $_POST['priceId']
			? intval( cl_sanitization( $_POST['priceId'] ) )
			: false;

		$quantity = isset( $_POST['quantity'] )
			? intval( cl_sanitization( $_POST['quantity'] ) )
			: 1;

		$data = cls_prb_get_listing_data( $listing_id, $price_id, $quantity );

		// Handle cart eventually?
	} else {
		$data = cls_prb_get_cart_data();
	}

	// Country is not valid at this point.
	// https://stripe.com/docs/js/payment_request/update
	unset( $data['country'] );

	wp_send_json_success( $data );
}
add_action( 'wp_ajax_cls_prb_ajax_get_options', 'cls_prb_ajax_get_options' );
add_action( 'wp_ajax_nopriv_cls_prb_ajax_get_options', 'cls_prb_ajax_get_options' );
