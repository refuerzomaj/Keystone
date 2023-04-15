<?php
use Essential\Restate\Common\Customer\Customer;

/**
 * Payment actions.
 *
 * @package CL_Stripe
 * @since   2.7.0
 */

/**
 * Starts the process of completing a purchase with Stripe.
 *
 * Generates an intent that can require user authorization before proceeding.
 *
 * @link https://stripe.com/docs/payments/intents
 * @since 2.7.0
 *
 * @param array $purchase_data {
 *   Purchase form data.
 *
 * }
 */
function cls_process_purchase_form( $purchase_data ) {
	// Catch a straight to gateway request.
	// Remove the error set by the "gateway mismatch" and allow the redirect.

	if ( isset( $_REQUEST['cl_action'] ) && 'straight_to_gateway' === $_REQUEST['cl_action'] ) {
		foreach ( $purchase_data['listing'] as $listing ) {
			$options             = isset( $listing['options'] ) ? $listing['options'] : array();
			$options['quantity'] = isset( $listing['quantity'] ) ? $listing['quantity'] : 1;

			cl_add_to_cart( $listing['id'], $options );
		}

		WPERECCP()->front->error->cl_unset_error( 'cl-straight-to-gateway-error' );
		WPERECCP()->front->checkout->cl_send_back_to_checkout();

		return;
	}

	try {
		if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'We are unable to process your payment at this time, please try again later or contact support.',
					'essential-wp-real-estate'
				)
			);
		}

		/**
		 * Allows processing before an Intent is created.
		 *
		 * @since 2.7.0
		 *
		 * @param array $purchase_data Purchase data.
		 */
		do_action( 'cls_pre_process_purchase_form', $purchase_data );

		$payment_method_id     = isset( $_POST['payment_method_id'] ) ? cl_sanitization( $_POST['payment_method_id'] ) : false;
		$payment_method_exists = isset( $_POST['payment_method_exists'] ) ? 'true' == cl_sanitization( $_POST['payment_method_exists'] ) : false;

		if ( ! $payment_method_id ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'Unable to locate payment method 3. Please try again with a new payment method.',
					'essential-wp-real-estate'
				)
			);
		}

		// Ensure Payment Method is still valid.
		$payment_method = cls_api_request( 'PaymentMethod', 'retrieve', $payment_method_id );
		$card           = isset( $payment_method->card ) ? $payment_method->card : null;

		// ...block prepaid cards if option is not enabled.
		if (
			$card &&
			'prepaid' === $card->funding &&
			false === (bool) cl_admin_get_option( 'stripe_allow_prepaid' )
		) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'Prepaid cards are not a valid payment method. Please try again with a new payment method.',
					'essential-wp-real-estate'
				)
			);
		}

		if ( cls_is_zero_decimal_currency() ) {
			$amount = $purchase_data['price'];
		} else {
			$amount = round( $purchase_data['price'] * 100, 0 );
		}

		// Retrieves or creates a Stripe Customer.
		$customer = cls_checkout_setup_customer( $purchase_data );

		if ( ! $customer ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'Unable to create customer. Please try again1.',
					'essential-wp-real-estate'
				)
			);
		}

		/**
		 * Allows processing before an Intent is created, but
		 * after a \Stripe\Customer is available.
		 *
		 * @since 2.7.0
		 *
		 * @param array            $purchase_data Purchase data.
		 * @param \Stripe\Customer $customer Stripe Customer object.
		 */
		do_action( 'cls_process_purchase_form_before_intent', $purchase_data, $customer );

		// Flag if this is the first card being attached to the Customer.
		$existing_payment_methods = cl_stripe_get_existing_cards( $purchase_data['user_info']['id'] );
		$is_first_payment_method  = empty( $existing_payment_methods );

		$address_info = $purchase_data['user_info']['address'];

		// Update PaymentMethod details if necessary.
		if ( $payment_method_exists && ! empty( $_POST['cl_stripe_update_billing_address'] ) ) {
			$billing_address = array();

			foreach ( $address_info as $key => $value ) {
				// Adjusts address data keys to work with PaymentMethods.
				switch ( $key ) {
					case 'zip':
						$key = 'postal_code';
						break;
				}

				$billing_address[ $key ] = ! empty( $value ) ? cl_sanitization( $value ) : '';
			}

			cls_api_request(
				'PaymentMethod',
				'update',
				$payment_method_id,
				array(
					'billing_details' => array(
						'address' => $billing_address,
					),
				)
			);
		}

		// Create a list of {$listing_id}_{$price_id}
		$payment_items = array();

		foreach ( $purchase_data['cart_details'] as $item ) {
			$price_id = isset( $item['item_number']['options']['price_id'] )
				? $item['item_number']['options']['price_id']
				: null;

			$payment_items[] = $item['id'] . ( ! empty( $price_id ) ? ( '_' . $price_id ) : '' );
		}

		// Shared Intent arguments.
		$intent_args = array(
			'confirm'        => true,
			'payment_method' => $payment_method_id,
			'customer'       => $customer->id,
			'metadata'       => array(
				'email'               => esc_html( $purchase_data['user_info']['email'] ),
				'cl_payment_subtotal' => esc_html( $purchase_data['subtotal'] ),
				'cl_payment_discount' => esc_html( $purchase_data['discount'] ),
				'cl_payment_tax'      => esc_html( $purchase_data['tax'] ),
				'cl_payment_tax_rate' => esc_html( $purchase_data['tax_rate'] ),
				'cl_payment_fees'     => esc_html( cl_get_cart_fee_total() ),
				'cl_payment_total'    => esc_html( $purchase_data['price'] ),
				'cl_payment_items'    => esc_html( implode( ', ', $payment_items ) ),
			),
		);

		// Attempt to map existing charge arguments to PaymentIntents.
		if ( has_filter( 'cls_create_charge_args' ) ) {
			/**
			 * @deprecated 2.7.0 In favor of `cls_create_payment_intent_args`.
			 *
			 * @param array $intent_args
			 */
			$old_charge_args = apply_filters_deprecated(
				'cls_create_charge_args',
				array(
					$intent_args,
				),
				'2.7.0',
				'cls_create_payment_intent_args'
			);

			// Grab a few compatible arguments from the old charges filter.
			$compatible_keys = array(
				'amount',
				'currency',
				'customer',
				'description',
				'metadata',
				'application_fee',
			);

			foreach ( $compatible_keys as $compatible_key ) {
				if ( ! isset( $old_charge_args[ $compatible_key ] ) ) {
					continue;
				}

				$value = $old_charge_args[ $compatible_key ];

				switch ( $compatible_key ) {
					case 'application_fee':
						$intent_args['application_fee_amount'] = $value;
						break;

					default:
						// If a legacy value is an array merge it with the existing values to avoid overriding completely.
						$intent_args[ $compatible_key ] = is_array( $value ) && is_array( $intent_args[ $compatible_key ] )
							? wp_parse_args( $value, $intent_args[ $compatible_key ] )
							: $value;
				}

				cl_debug_log( __( 'Charges are no longer directly created in Stripe. ', 'essential-wp-real-estate' ), true );
			}
		}

		// Create a SetupIntent for a non-payment carts.
		if ( cls_is_preapprove_enabled() || 0 === $amount ) {
			$intent_args = array_merge(
				array(
					'usage'       => 'off_session',
					'description' => cls_get_payment_description( $purchase_data['cart_details'] ),
				),
				$intent_args
			);

			/**
			 * Filters the arguments used to create a SetupIntent.
			 *
			 * @since 2.7.0
			 *
			 * @param array $intent_args SetupIntent arguments.
			 * @param array $purchase_data {
			 *   Purchase form data.
			 *
			 * }
			 */
			$intent_args = apply_filters( 'cls_create_setup_intent_args', $intent_args, $purchase_data );

			$intent = cls_api_request( 'SetupIntent', 'create', $intent_args );

			// Manually attach PaymentMethod to the Customer.
			if ( ! $payment_method_exists && cl_stripe_existing_cards_enabled() ) {
				$payment_method = cls_api_request( 'PaymentMethod', 'retrieve', $payment_method_id );
				$payment_method->attach(
					array(
						'customer' => $customer->id,
					)
				);
			}

			// Create a PaymentIntent for an immediate charge.
		} else {
			$purchase_summary     = cls_get_payment_description( $purchase_data['cart_details'] );
			$statement_descriptor = cls_get_statement_descriptor();

			if ( empty( $statement_descriptor ) ) {
				$statement_descriptor = substr( $purchase_summary, 0, 22 );
			}

			$statement_descriptor = apply_filters( 'cls_statement_descriptor', $statement_descriptor, $purchase_data );
			$statement_descriptor = cls_sanitize_statement_descriptor( $statement_descriptor );

			if ( empty( $statement_descriptor ) ) {
				$statement_descriptor = null;
			} elseif ( is_numeric( $statement_descriptor ) ) {
				$statement_descriptor = cl_get_label_singular() . ' ' . $statement_descriptor;
			}

			$intent_args = array_merge(
				array(
					'amount'               => $amount,
					'currency'             => cl_get_currency(),
					'setup_future_usage'   => 'off_session',
					'confirmation_method'  => 'manual',
					'save_payment_method'  => true,
					'description'          => $purchase_summary,
					'statement_descriptor' => $statement_descriptor,
				),
				$intent_args
			);

			$stripe_connect_account_id = cl_admin_get_option( 'stripe_connect_account_id' );

			if (
				! empty( $stripe_connect_account_id ) &&
				true === cls_stripe_connect_account_country_supports_application_fees()
			) {
				$intent_args['application_fee_amount'] = round( $amount * 0.02 );
			}

			/**
			 * Filters the arguments used to create a SetupIntent.
			 *
			 * @since 2.7.0
			 *
			 * @param array $intent_args SetupIntent arguments.
			 * @param array $purchase_data {
			 *   Purchase form data.
			 *
			 * }
			 */
			$intent_args = apply_filters( 'cls_create_payment_intent_args', $intent_args, $purchase_data );

			$intent = cls_api_request( 'PaymentIntent', 'create', $intent_args );
		}

		// Set the default payment method when attaching the first one.
		if ( $is_first_payment_method ) {
			cls_api_request(
				'Customer',
				'update',
				$customer->id,
				array(
					'invoice_settings' => array(
						'default_payment_method' => $payment_method_id,
					),
				)
			);
		}

		/**
		 * Allows further processing after an Intent is created.
		 *
		 * @since 2.7.0
		 *
		 * @param array                                     $purchase_data Purchase data.
		 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
		 */
		do_action( 'cls_process_purchase_form', $purchase_data, $intent );

		return wp_send_json_success(
			array(
				'intent' => $intent,
				// Send back a new nonce because the user might have logged in.
				'nonce'  => wp_create_nonce( 'cl-process-checkout' ),
			)
		);

		// Catch card-specific errors to handle rate limiting.
	} catch ( \Stripe\Exception\CardException $e ) {
		// Increase the card error count.
		cl_stripe()->rate_limiting->increment_card_error_count();

		$error = $e->getJsonBody()['error'];

		// Record error in log.
		WPERECCP()->front->gateways->cl_record_gateway_error(
			esc_html__( 'Stripe Error', 'essential-wp-real-estate' ),
			sprintf(
				esc_html__( 'There was an error while processing a Stripe payment. Payment data: %s', 'essential-wp-real-estate' ),
				wp_json_encode( $error )
			),
			0
		);

		return wp_send_json_error(
			array(
				'message' => esc_html(
					cls_get_localized_error_message( $error['code'], $error['message'] )
				),
			)
		);

		// Catch Stripe-specific errors.
	} catch ( \Stripe\Exception\ApiErrorException $e ) {
		$error = $e->getJsonBody()['error'];

		// Record error in log.
		WPERECCP()->front->gateways->cl_record_gateway_error(
			esc_html__( 'Stripe Error', 'essential-wp-real-estate' ),
			sprintf(
				esc_html__( 'There was an error while processing a Stripe payment. Payment data: %s', 'essential-wp-real-estate' ),
				wp_json_encode( $error )
			),
			0
		);

		return wp_send_json_error(
			array(
				'message' => esc_html(
					cls_get_localized_error_message( $error['code'], $error['message'] )
				),
			)
		);

		// Catch gateway processing errors.
	} catch ( \CL_Stripe_Gateway_Exception $e ) {
		if ( true === $e->hasLogMessage() ) {
			WPERECCP()->front->gateways->cl_record_gateway_error(
				esc_html__( 'Stripe Error', 'essential-wp-real-estate' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);

		// Catch any remaining error.
	} catch ( \Exception $e ) {

		// Safety precaution in case the payment form is submitted directly.
		// Redirects back to the Checkout.
		if ( isset( $_POST['cl_email'] ) && ! isset( $_POST['payment_method_id'] ) ) {
			cl_set_error( $e->getCode(), $e->getMessage() );
			WPERECCP()->front->checkout->cl_send_back_to_checkout( '?payment-mode=' . $purchase_data['gateway'] );
		}

		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'cl_gateway_stripe', 'cls_process_purchase_form' );

/**
 * Retrieves an Intent.
 *
 * @since 2.7.0
 */
function cls_get_intent() {
	 // Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_cls_map_form_data_to_request( cl_sanitization($_POST) );

	$intent_id   = isset( $_REQUEST['intent_id'] ) ? cl_sanitization( $_REQUEST['intent_id'] ) : null;
	$intent_type = isset( $_REQUEST['intent_type'] ) ? cl_sanitization( $_REQUEST['intent_type'] ) : 'payment_intent';

	try {
		if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Rate limit reached during Intent retrieval.'
			);
		}

		if ( false === cls_verify_payment_form_nonce() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Nonce verification failed during Intent retrieval.'
			);
		}

		if ( 'setup_intent' === $intent_type ) {
			$intent = cls_api_request( 'SetupIntent', 'retrieve', $intent_id );
		} else {
			$intent = cls_api_request( 'PaymentIntent', 'retrieve', $intent_id );
		}

		return wp_send_json_success(
			array(
				'intent' => $intent,
			)
		);
		// Catch gateway processing errors.
	} catch ( \CL_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit if an exception occurs mid-process.
		cl_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			WPERECCP()->front->gateways->cl_record_gateway_error(
				esc_html__( 'Stripe Error', 'essential-wp-real-estate' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);

		// Catch any remaining error.
	} catch ( \Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_cls_get_intent', 'cls_get_intent' );
add_action( 'wp_ajax_nopriv_cls_get_intent', 'cls_get_intent' );

/**
 * Confirms a PaymentIntent.
 *
 * @since 2.7.0
 */
function cls_confirm_intent() {
	 // Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_cls_map_form_data_to_request( cl_sanitization($_POST) );

	$intent_id   = isset( $_REQUEST['intent_id'] ) ? cl_sanitization( $_REQUEST['intent_id'] ) : null;
	$intent_type = isset( $_REQUEST['intent_type'] ) ? cl_sanitization( $_REQUEST['intent_type'] ) : 'payment_intent';

	try {
		if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Rate limit reached during Intent confirmation.'
			);
		}

		if ( false === cls_verify_payment_form_nonce() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Nonce verification failed during Intent confirmation.'
			);
		}

		// SetupIntent was used if the cart total is $0.
		if ( 'setup_intent' === $intent_type ) {
			$intent = cls_api_request( 'SetupIntent', 'retrieve', $intent_id );
		} else {
			$intent = cls_api_request( 'PaymentIntent', 'retrieve', $intent_id );
			$intent->confirm();
		}

		/**
		 * Allows further processing after an Intent is confirmed.
		 * Runs for all calls to confirm(), regardless of action needed.
		 *
		 * @since 2.7.0
		 *
		 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Stripe intent.
		 */
		do_action( 'cls_confirm_payment_intent', $intent );

		return wp_send_json_success(
			array(
				'intent' => $intent,
			)
		);

		// Catch gateway processing errors.
	} catch ( \CL_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit if an exception occurs mid-process.
		cl_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			WPERECCP()->front->gateways->cl_record_gateway_error(
				esc_html__( 'Stripe Error', 'essential-wp-real-estate' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);

		// Catch any remaining error.
	} catch ( Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_cls_confirm_intent', 'cls_confirm_intent' );
add_action( 'wp_ajax_nopriv_cls_confirm_intent', 'cls_confirm_intent' );

/**
 * Capture a PaymentIntent.
 *
 * @since 2.7.0
 */
function cls_capture_intent() {
	 // Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_cls_map_form_data_to_request( cl_sanitization($_POST) );

	$intent_id = isset( $_REQUEST['intent_id'] ) ? cl_sanitization( $_REQUEST['intent_id'] ) : null;

	try {
		if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Rate limit reached during Intent capture.'
			);
		}

		// This must happen in the Checkout flow, so validate the Checkout nonce.
		$nonce = isset( $_POST['cl-process-checkout-nonce'] ) ? cl_sanitization( $_POST['cl-process-checkout-nonce'] ) : '';

		$nonce_verified = wp_verify_nonce( $nonce, 'cl-process-checkout' );

		if ( false === $nonce_verified ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Nonce verification failed during Intent capture.'
			);
		}

		$intent = cls_api_request( 'PaymentIntent', 'retrieve', $intent_id );

		/**
		 * Allows processing before a PaymentIntent is captured.
		 *
		 * @since 2.7.0
		 *
		 * @param \Stripe\PaymentIntent $payment_intent Stripe PaymentIntent.
		 */
		do_action( 'cls_capture_payment_intent', $intent );

		// Capture capturable amount if nothing else has captured the intent.
		if ( 'requires_capture' === $intent->status ) {
			$intent->capture(
				array(
					'amount_to_capture' => $intent->amount_capturable,
				)
			);
		}

		return wp_send_json_success(
			array(
				'intent' => $intent,
			)
		);

		// Catch gateway processing errors.
	} catch ( \CL_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit if an exception occurs mid-process.
		cl_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			WPERECCP()->front->gateways->cl_record_gateway_error(
				esc_html__( 'Stripe Error', 'essential-wp-real-estate' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);

		// Catch any remaining error.
	} catch ( Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_cls_capture_intent', 'cls_capture_intent' );
add_action( 'wp_ajax_nopriv_cls_capture_intent', 'cls_capture_intent' );

/**
 * Update a PaymentIntent.
 *
 * @since 2.7.0
 */
function cls_update_intent() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_cls_map_form_data_to_request( cl_sanitization($_POST) );

	$intent_id = isset( $_REQUEST['intent_id'] ) ? cl_sanitization( $_REQUEST['intent_id'] ) : null;

	try {
		if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Rate limit reached during Intent update.'
			);
		}

		if ( false === cls_verify_payment_form_nonce() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Nonce verification failed during Intent update.'
			);
		}

		$intent = cls_api_request( 'PaymentIntent', 'retrieve', $intent_id );

		/**
		 * Allows processing before a PaymentIntent is updated.
		 *
		 * @since 2.7.0
		 *
		 * @param string $intent_id Stripe PaymentIntent ID.
		 */
		do_action( 'cls_update_payment_intent', $intent_id );

		$intent_args           = array();
		$intent_args_whitelist = array(
			'payment_method',
		);

		foreach ( $intent_args_whitelist as $intent_arg ) {
			if ( isset( $_POST[ $intent_arg ] ) ) {
				$intent_args[ $intent_arg ] = cl_sanitization( $_POST[ $intent_arg ] );
			}
		}

		$intent = cls_api_request( 'PaymentIntent', 'update', $intent_id, $intent_args );

		return wp_send_json_success(
			array(
				'intent' => $intent,
			)
		);

		// Catch gateway processing errors.
	} catch ( \CL_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit if an exception occurs mid-process.
		cl_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			WPERECCP()->front->gateways->cl_record_gateway_error(
				esc_html__( 'Stripe Error', 'essential-wp-real-estate' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);

		// Catch any remaining error.
	} catch ( Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_cls_update_intent', 'cls_update_intent' );
add_action( 'wp_ajax_nopriv_cls_update_intent', 'cls_update_intent' );

/**
 * Create an \Clpayment.
 *
 * @since 2.7.0
 */
function cls_create_payment() {
	 // Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_cls_map_form_data_to_request( $_POST );

	// Simulate being in an `cl_process_purchase_form()` request.
	_cls_fake_process_purchase_step();

	try {
		if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Rate limit reached during payment creation.'
			);
		}

		// This must happen in the Checkout flow, so validate the Checkout nonce.
		$nonce = isset( $_POST['cl-process-checkout-nonce'] ) ? cl_sanitization( $_POST['cl-process-checkout-nonce'] ) : '';

		$nonce_verified = wp_verify_nonce( $nonce, 'cl-process-checkout' );

		if ( false === $nonce_verified ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Nonce verification failed during payment creation.'
			);
		}

		$intent = isset( $_REQUEST['intent'] ) ? cl_sanitization( $_REQUEST['intent'] ) : array();

		if ( ! isset( $intent['id'] ) ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Unable to retrieve Intent data during payment creation.'
			);
		}

		$purchase_data = cl_get_purchase_session();

		if ( false === $purchase_data ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Unable to retrieve purchase data during payment creation.'
			);
		}

		// Ensure Intent has transitioned to the correct status.
		if ( 'setup_intent' === $intent['object'] ) {
			$intent = cls_api_request( 'SetupIntent', 'retrieve', $intent['id'] );
		} else {
			$intent = cls_api_request( 'PaymentIntent', 'retrieve', $intent['id'] );
		}

		if ( ! in_array( $intent->status, array( 'succeeded', 'requires_capture' ), true ) ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Invalid Intent status ' . $intent->status . ' during payment creation.'
			);
		}

		$payment_data = array(
			'price'        => $purchase_data['price'],
			'date'         => $purchase_data['date'],
			'user_email'   => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency'     => cl_get_currency(),
			'listing'      => $purchase_data['listing'],
			'cart_details' => $purchase_data['cart_details'],
			'user_info'    => $purchase_data['user_info'],
			'status'       => 'pending',
			'gateway'      => 'stripe',
		);

		// Ensure $_COOKIE is available without a new HTTP request.
		if ( class_exists( 'CL_Auto_Register' ) ) {
			add_action( 'set_logged_in_cookie', 'cls_set_logged_in_cookie_global' );
		}

		// Record the pending payment.
		$payment_id = cl_insert_payment( $payment_data );

		if ( false === $payment_id ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Unable to insert payment record.'
			);
		}

		// Retrieve created payment.
		$payment = cl_get_payment( $payment_id );

		// Retrieve the relevant Intent.
		if ( 'setup_intent' === $intent->object ) {
			$intent = cls_api_request(
				'SetupIntent',
				'update',
				$intent->id,
				array(
					'metadata' => array(
						'cl_payment_id' => $payment_id,
					),
				)
			);

			$payment->add_note( 'Stripe SetupIntent ID: ' . $intent->id );
			$payment->update_meta( '_cls_stripe_setup_intent_id', $intent->id );
		} else {
			$intent = cls_api_request(
				'PaymentIntent',
				'update',
				$intent->id,
				array(
					'metadata' => array(
						'cl_payment_id' => $payment_id,
					),
				)
			);

			$payment->add_note( 'Stripe PaymentIntent ID: ' . $intent->id );
			$payment->update_meta( '_cls_stripe_payment_intent_id', $intent->id );
		}

		// Use Intent ID for temporary transaction ID.
		// It will be updated when a charge is available.
		$payment->transaction_id = $intent->id;

		// Retrieves or creates a Stripe Customer.
		$payment->update_meta( '_cls_stripe_customer_id', $intent->customer );
		$payment->add_note( 'Stripe Customer ID: ' . $intent->customer );

		// Attach the \Stripe\Customer ID to the \Customer meta if one exists.
		$cl_customer = new Customer( $purchase_data['user_email'] );

		if ( $cl_customer->id > 0 ) {
			$cl_customer->update_meta( cl_stripe_get_customer_key(), $intent->customer );
		}

		$saved = $payment->save();

		if ( class_exists( 'CL_Auto_Register' ) ) {
			remove_action( 'set_logged_in_cookie', 'cls_set_logged_in_cookie_global' );
		}

		if ( true === $saved ) {
			/**
			 * Allows further processing after a payment is created.
			 *
			 * @since 2.7.0
			 *
			 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
			 */
			do_action( 'cls_payment_created', $payment, $intent );

			return wp_send_json_success(
				array(
					'intent'  => $intent,
					'payment' => $payment,
					// Send back a new nonce because the user might have logged in via Auto Register.
					'nonce'   => wp_create_nonce( 'cl-process-checkout' ),
				)
			);
		} else {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'Unable to create payment.',
					'essential-wp-real-estate'
				),
				'Unable to save payment record.'
			);
		}

		// Catch gateway processing errors.
	} catch ( \CL_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit count when something goes wrong mid-process.
		cl_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			WPERECCP()->front->gateways->cl_record_gateway_error(
				esc_html__( 'Stripe Error', 'essential-wp-real-estate' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);

		// Catch any remaining error.
	} catch ( \Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_cls_create_payment', 'cls_create_payment' );
add_action( 'wp_ajax_nopriv_cls_create_payment', 'cls_create_payment' );

/**
 * Completes an \Clpayment (via AJAX)
 *
 * @since 2.7.0
 */
function cls_complete_payment() {
	// Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	_cls_map_form_data_to_request( cl_sanitization($_POST) );

	$intent = isset( $_REQUEST['intent'] ) ? cl_sanitization( $_REQUEST['intent'] ) : array();

	try {
		if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Rate limit reached during payment completion.'
			);
		}

		// This must happen in the Checkout flow, so validate the Checkout nonce.
		$nonce = isset( $_POST['cl-process-checkout-nonce'] )
			? cl_sanitization( $_POST['cl-process-checkout-nonce'] )
			: '';

		$nonce_verified = wp_verify_nonce( $nonce, 'cl-process-checkout' );

		if ( false === $nonce_verified ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Nonce verification failed during payment completion.'
			);
		}

		if ( ! isset( $intent['id'] ) ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Unable to retrieve Intent during payment completion.'
			);
		}

		// Retrieve the intent from Stripe again to verify linked payment.
		if ( 'setup_intent' === $intent['object'] ) {
			$intent = cls_api_request( 'SetupIntent', 'retrieve', $intent['id'] );
		} else {
			$intent = cls_api_request( 'PaymentIntent', 'retrieve', $intent['id'] );
		}

		$payment = cl_get_payment( $intent->metadata->cl_payment_id );

		if ( ! $payment ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Unable to retrieve pending payment record.'
			);
		}

		if ( 'setup_intent' !== $intent['object'] ) {
			$charge_id = cl_sanitization( current( $intent['charges']['data'] )['id'] );

			$payment->add_note( 'Stripe Charge ID: ' . $charge_id );
			$payment->transaction_id = cl_sanitization( $charge_id );
		}

		// Mark payment as Preapproved.
		if ( cls_is_preapprove_enabled() ) {
			$payment->status = 'preapproval';

			// Complete payment and transition the Transaction ID to the actual Charge ID.
		} else {
			$payment->status = 'publish';
		}

		if ( $payment->save() ) {
			/**
			 * Allows further processing after a payment is completed.
			 *
			 * Sends back just the Intent ID to avoid needing always retrieve
			 * the intent in this step, which has been transformed via JSON,
			 * and is no longer a \Stripe\PaymentIntent
			 *
			 * @since 2.7.0
			 *
			 * @param string       $intent_id Stripe Intent ID.
			 */
			do_action( 'cls_payment_complete', $payment, $intent['id'] );

			// Empty cart.
			WPERECCP()->front->cart->cl_empty_cart();

			return wp_send_json_success(
				array(
					'payment' => $payment,
					'intent'  => $intent,
				)
			);
		} else {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Unable to update payment record to completion.'
			);
		}

		// Catch gateway processing errors.
	} catch ( \CL_Stripe_Gateway_Exception $e ) {
		// Increase the rate limit count when something goes wrong mid-process.
		cl_stripe()->rate_limiting->increment_card_error_count();

		if ( true === $e->hasLogMessage() ) {
			WPERECCP()->front->gateways->cl_record_gateway_error(
				esc_html__( 'Stripe Error', 'essential-wp-real-estate' ),
				$e->getLogMessage(),
				0
			);
		}

		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);

		// Catch any remaining error.
	} catch ( \Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_cls_complete_payment', 'cls_complete_payment' );
add_action( 'wp_ajax_nopriv_cls_complete_payment', 'cls_complete_payment' );

/**
 * Completes a Payment authorization.
 *
 * @since 2.7.0
 */
function cls_complete_payment_authorization() {
	 $intent_id = isset( $_REQUEST['intent_id'] ) ? cl_sanitization( $_REQUEST['intent_id'] ) : null;

	try {
		if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Rate limit reached during payment authorization.'
			);
		}

		$nonce = isset( $_POST['cls-complete-payment-authorization'] ) ? cl_sanitization( $_POST['cls-complete-payment-authorization'] ) : '';

		$nonce_verified = wp_verify_nonce( $nonce, 'cls-complete-payment-authorization' );

		if ( false === $nonce_verified ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Nonce verification failed during payment authorization.'
			);
		}

		$intent        = cls_api_request( 'PaymentIntent', 'retrieve', $intent_id );
		$cl_payment_id = $intent->metadata->cl_payment_id ? $intent->metadata->cl_payment_id : false;

		if ( ! $cl_payment_id ) {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Unable to retrieve payment record ID from Stripe metadata.'
			);
		}

		$payment   = cl_get_payment( $cl_payment_id );
		$charge_id = current( $intent->charges->data )->id;

		$payment->add_note( 'Stripe Charge ID: ' . $charge_id );
		$payment->transaction_id = $charge_id;
		$payment->status         = 'publish';

		if ( $payment->save() ) {

			/**
			 * Allows further processing after a payment authorization is completed.
			 *
			 * @since 2.7.0
			 *
			 * @param \Stripe\PaymentIntent $intent Created Stripe Intent.
			 */
			do_action( 'cls_payment_authorization_complete', $intent, $payment );

			return wp_send_json_success(
				array(
					'intent'  => $intent,
					'payment' => $payment,
				)
			);
		} else {
			throw new \CL_Stripe_Gateway_Exception(
				esc_html__(
					'An error occurred, but your payment may have gone through. Please contact the site administrator.',
					'essential-wp-real-estate'
				),
				'Unable to save payment record during authorization.'
			);
		}
	} catch ( \Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => esc_html( $e->getMessage() ),
			)
		);
	}
}
add_action( 'wp_ajax_cls_complete_payment_authorization', 'cls_complete_payment_authorization' );
add_action( 'wp_ajax_nopriv_cls_complete_payment_authorization', 'cls_complete_payment_authorization' );

/**
 * Sets up a \Stripe\Customer object based on the current purchase data.
 *
 * @param array $purchase_data {
 *
 * }
 * @return \Stripe\Customer|false $customer Stripe Customer if one is created or false on error.
 */
function cls_checkout_setup_customer( $purchase_data ) {
	$customer           = false;
	$stripe_customer_id = '';
	if ( is_user_logged_in() ) {
		$stripe_customer_id = cls_get_stripe_customer_id( get_current_user_id() );
	}
	if ( empty( $stripe_customer_id ) ) {
		// No customer ID found, let's look one up based on the email.
		$stripe_customer_id = cls_get_stripe_customer_id( $purchase_data['user_email'], false );
	}
	$customer_args = array(
		'email'       => $purchase_data['user_email'],
		'description' => $purchase_data['user_email'],
	);
	/**
	 * Filters the arguments used to create a Customer in Stripe.
	 *
	 * @since unknown
	 *
	 * @param array $customer_args {
	 *   Arguments to create a Stripe Customer.
	 *
	 *   @link https://stripe.com/docs/api/customers/create
	 * }
	 * @param array $purchase_data {
	 *   Cart purchase data if in the checkout context. Empty otherwise.
	 * }
	 */
	$customer_args = apply_filters( 'cls_create_customer_args', $customer_args, $purchase_data );
	$customer      = cls_get_stripe_customer( $stripe_customer_id, $customer_args );

	return $customer;
}

/**
 * Generates a description based on the cart details.
 *
 * @param array $cart_details {
 *
 * }
 * @return string
 */
function cls_get_payment_description( $cart_details ) {
	$purchase_summary = '';

	if ( is_array( $cart_details ) && ! empty( $cart_details ) ) {
		foreach ( $cart_details as $item ) {
			$purchase_summary .= $item['name'];
			$price_id          = isset( $item['item_number']['options']['price_id'] )
				? absint( $item['item_number']['options']['price_id'] )
				: false;

			if ( false !== $price_id ) {
				$purchase_summary .= ' - ' . WPERECCP()->front->listingsaction->cl_get_price_option_name( $item['id'], $item['item_number']['options']['price_id'] );
			}

			$purchase_summary .= ', ';
		}

		$purchase_summary = rtrim( $purchase_summary, ', ' );
	}

	// Stripe has a maximum of 999 characters in the charge description
	$purchase_summary = substr( $purchase_summary, 0, 1000 );

	return html_entity_decode( $purchase_summary, ENT_COMPAT, 'UTF-8' );
}

/**
 * Charge a preapproved payment
 *
 * @since 1.6
 * @return bool
 */
function cls_charge_preapproved( $payment_id = 0 ) {
	$retval = false;

	if ( empty( $payment_id ) ) {
		return $retval;
	}

	$payment     = cl_get_payment( $payment_id );
	$customer_id = $payment->get_meta( '_cls_stripe_customer_id' );

	if ( empty( $customer_id ) ) {
		return $retval;
	}

	if ( ! in_array( $payment->status, array( 'preapproval', 'preapproval_pending' ), true ) ) {
		return $retval;
	}

	$setup_intent_id = $payment->get_meta( '_cls_stripe_setup_intent_id' );

	try {
		if ( cls_is_zero_decimal_currency() ) {
			$amount = cl_get_payment_amount( $payment->ID );
		} else {
			$amount = cl_get_payment_amount( $payment->ID ) * 100;
		}

		$cart_details         = cl_get_payment_meta_cart_details( $payment->ID );
		$purchase_summary     = cls_get_payment_description( $cart_details );
		$statement_descriptor = cls_get_statement_descriptor();

		if ( empty( $statement_descriptor ) ) {
			$statement_descriptor = substr( $purchase_summary, 0, 22 );
		}

		$statement_descriptor = apply_filters( 'cls_preapproved_statement_descriptor', $statement_descriptor, $payment->ID );
		$statement_descriptor = cls_sanitize_statement_descriptor( $statement_descriptor );

		if ( empty( $statement_descriptor ) ) {
			$statement_descriptor = null;
		}

		// Create a PaymentIntent using SetupIntent data.
		if ( ! empty( $setup_intent_id ) ) {
			$setup_intent = cls_api_request( 'SetupIntent', 'retrieve', $setup_intent_id );
			$intent_args  = array(
				'amount'               => $amount,
				'currency'             => cl_get_currency(),
				'payment_method'       => $setup_intent->payment_method,
				'customer'             => $setup_intent->customer,
				'off_session'          => true,
				'confirm'              => true,
				'description'          => $purchase_summary,
				'metadata'             => $setup_intent->metadata->toArray(),
				'statement_descriptor' => $statement_descriptor,
			);
			// Process a legacy preapproval. Uses the Customer's default source.
		} else {
			$customer    = \Stripe\Customer::retrieve( $customer_id );
			$intent_args = array(
				'amount'               => $amount,
				'currency'             => cl_get_currency(),
				'payment_method'       => $customer->default_source,
				'customer'             => $customer->id,
				'off_session'          => true,
				'confirm'              => true,
				'description'          => $purchase_summary,
				'metadata'             => array(
					'email'         => cl_get_payment_user_email( $payment->ID ),
					'cl_payment_id' => $payment->ID,
				),
				'statement_descriptor' => $statement_descriptor,
			);
		}

		/** This filter is documented in includes/payment-actions.php */
		$intent_args = apply_filters( 'cls_create_payment_intent_args', $intent_args, array() );

		$payment_intent = cls_api_request( 'PaymentIntent', 'create', $intent_args );

		if ( 'succeeded' === $payment_intent->status ) {
			$charge_id = current( $payment_intent->charges->data )->id;

			$payment->status = 'publish';
			$payment->add_note( 'Stripe Charge ID: ' . $charge_id );
			$payment->add_note( 'Stripe PaymentIntent ID: ' . $payment_intent->id );
			$payment->add_meta( '_cls_stripe_payment_intent_id', $payment_intent->id );
			$payment->transaction_id = $charge_id;

			$retval = $payment->save();
		}
	} catch ( \Stripe\Exception\ApiErrorException $e ) {
		$error = $e->getJsonBody()['error'];

		$payment->status = 'preapproval_pending';
		$payment->add_note(
			esc_html(
				cls_get_localized_error_message( $error['code'], $error['message'] )
			)
		);
		$payment->add_note( 'Stripe PaymentIntent ID: ' . $error['payment_intent']['id'] );
		$payment->add_meta( '_cls_stripe_payment_intent_id', $error['payment_intent']['id'] );
		$payment->save();

		/**
		 * Allows further processing when a Preapproved payment needs further action.
		 *
		 * @since 2.7.0
		 *
		 * @param int $payment_id ID of the payment.
		 */
		do_action( 'cls_preapproved_payment_needs_action', $payment_id );
	} catch ( \Exception $e ) {
		$payment->add_note( esc_html( $e->getMessage() ) );
	}

	return $retval;
}

/**
 * @see cl_stripe_maybe_refund_charge()
 *
 * @access      public
 * @since       1.8
 * @return      void
 */
function cl_stripe_process_refund( $payment_id, $new_status, $old_status ) {
	if ( empty( $_POST['cl_refund_in_stripe'] ) ) {
		return;
	}

	$should_process_refund = 'publish' != $old_status && 'revoked' != $old_status ? false : true;
	$should_process_refund = apply_filters( 'cls_should_process_refund', $should_process_refund, $payment_id, $new_status, $old_status );

	if ( false === $should_process_refund ) {
		return;
	}

	if ( 'refunded' != $new_status ) {
		return;
	}

	try {
		cl_refund_stripe_purchase( $payment_id );
	} catch ( \Exception $e ) {
		wp_die( $e->getMessage(), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 400 ) );
	}
}
add_action( 'cl_update_payment_status', 'cl_stripe_process_refund', 200, 3 );

/**
 * If selected, refunds a charge in Stripe when creating a new refund record.
 *
 * @see cl_stripe_process_refund()
 *
 * @since 2.8.7
 *
 * @param int  $order_id     ID of the order we're processing a refund for.
 * @param int  $refund_id    ID of the newly created refund record.
 * @param bool $all_refunded Whether or not this was a full refund.
 */
function cl_stripe_maybe_refund_charge( $order_id, $refund_id, $all_refunded ) {
	if ( ! current_user_can( 'edit_shop_payments', $order_id ) ) {
		return;
	}

	if ( empty( $_POST['data'] ) ) {
		return;
	}

	$order = cl_get_order( $order_id );
	if ( empty( $order->gateway ) || 'stripe' !== $order->gateway ) {
		return;
	}

	cl_debug_log( sprintf( 'Stripe - Maybe processing refund for order #%d.', $order_id ) );

	// Get our data out of the serialized string.
	parse_str( cl_sanitization($_POST['data']), $form_data );

	if ( empty( $form_data['cl-stripe-refund'] ) ) {
		cl_debug_log( 'Stripe - Exiting refund process, as checkbox was not selected.' );

		cl_add_note(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'user_id'     => is_admin() ? get_current_user_id() : 0,
				'content'     => __( 'Charge not refunded in Stripe, as checkbox was not selected.', 'essential-wp-real-estate' ),
			)
		);

		return;
	}

	cl_debug_log( 'Stripe - Refund checkbox was selected, proceeding to refund charge.' );

	$refund = cl_get_order( $refund_id );
	if ( empty( $refund->total ) ) {
		cl_debug_log(
			sprintf(
				'Stripe - Exiting refund for order #%d - refund total is empty.',
				$order_id
			)
		);

		return;
	}

	try {
		cl_refund_stripe_purchase( $order, $refund );
	} catch ( \Exception $e ) {
		cl_debug_log( sprintf( 'Exception thrown while refunding order #%d. Message: %s', $order_id, $e->getMessage() ) );
	}
}
add_action( 'cl_refund_order', 'cl_stripe_maybe_refund_charge', 10, 3 );
