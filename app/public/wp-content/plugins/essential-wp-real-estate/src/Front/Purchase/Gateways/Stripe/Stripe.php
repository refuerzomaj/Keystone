<?php
namespace  Essential\Restate\Front\Purchase\Gateways\Stripe;

use Essential\Restate\Front\Purchase\Gateways\Stripe\Classes\CL_Stripe_Gateway_Exception;
use Essential\Restate\Front\Purchase\Gateways\Stripe\Classes\CL_Stripe_Functions;
use Essential\Restate\Front\Purchase\Gateways\Stripe\Classes\CL_Stripe_Rate_Limiting;
use Essential\Restate\Front\Purchase\Payments\Clpayment;
use Essential\Restate\Common\Customer\Customer;

use Essential\Restate\Traitval\Traitval;

class Stripe {

	use Traitval;
	public function __construct() {

		$stripe = __DIR__ . '/stripe/cl-stripe.php';

		if ( file_exists( $stripe ) ) {
			require_once $stripe;
		}

		add_action( 'cl_gateway_stripe', array( $this, 'cl_process_stripe_purchase' ) );
	}

	public static function get_setting() {

		return array(
			'stripe_active'        => array(
				'id'   => 'stripe_active',
				'name' => __( 'Stripe Active', 'essential-wp-real-estate' ),
				'type' => 'checkbox',
			),
			'test_publishable_key' => array(
				'id'    => 'test_publishable_key',
				'name'  => __( 'Test Publishable Key', 'essential-wp-real-estate' ),
				'desc'  => __( 'Enter your test publishable key, found in your Stripe Account Settings', 'essential-wp-real-estate' ),
				'type'  => 'text',
				'size'  => 'regular',
				'class' => 'cl-hidden cls-api-key-row',
			),
			'test_secret_key'      => array(
				'id'    => 'test_secret_key',
				'name'  => __( 'Test Secret Key', 'essential-wp-real-estate' ),
				'desc'  => __( 'Enter your test secret key, found in your Stripe Account Settings', 'essential-wp-real-estate' ),
				'type'  => 'text',
				'size'  => 'regular',
				'class' => 'cl-hidden cls-api-key-row',
			),
			'live_publishable_key' => array(
				'id'    => 'live_publishable_key',
				'name'  => __( 'Live Publishable Key', 'essential-wp-real-estate' ),
				'desc'  => __( 'Enter your live publishable key, found in your Stripe Account Settings', 'essential-wp-real-estate' ),
				'type'  => 'text',
				'size'  => 'regular',
				'class' => 'cl-hidden cls-api-key-row',
			),
			'live_secret_key'      => array(
				'id'    => 'live_secret_key',
				'name'  => __( 'Live Secret Key', 'essential-wp-real-estate' ),
				'desc'  => __( 'Enter your live secret key, found in your Stripe Account Settings', 'essential-wp-real-estate' ),
				'type'  => 'text',
				'size'  => 'regular',
				'class' => 'cl-hidden cls-api-key-row',
			),
		);
	}

	function cl_process_stripe_purchase( $purchase_data ) {

		if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'cl-gateway' ) ) {
			wp_die( __( 'Nonce verification has failed', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
		}

		// Collect payment data
		$payment_data = array(
			'price'        => $purchase_data['price'],
			'date'         => $purchase_data['date'],
			'user_email'   => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency'     => WPERECCP()->common->options->cl_get_currency(),
			'listing'      => $purchase_data['listing'],
			'user_info'    => $purchase_data['user_info'],
			'cart_details' => $purchase_data['cart_details'],
			'gateway'      => 'stripe',
			'status'       => ! empty( $purchase_data['buy_now'] ) ? 'private' : 'pending',
		);

		// Record the pending payment
		$payment = cl_insert_payment( $payment_data );

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
			if ( CL_Stripe_Rate_Limiting::getInstance()->has_hit_card_error_limit() ) {
				throw new CL_Stripe_Gateway_Exception(
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
				throw new CL_Stripe_Gateway_Exception(
					esc_html__(
						'Unable to locate payment method 2. Please try again with a new payment method.',
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
				throw new CL_Stripe_Gateway_Exception(
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
				throw new CL_Stripe_Gateway_Exception(
					esc_html__(
						'Unable to create customer. Please try again2',
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

					cl_debug_log( __( 'Charges are no longer directly created in Stripe.', 'essential-wp-real-estate' ), true );
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
		} catch ( CL_Stripe_Gateway_Exception $e ) {
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
}
