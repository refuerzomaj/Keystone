<?php
namespace  Essential\Restate\Front\Purchase\Gateways\PaypalStandard;

use Essential\Restate\Front\Purchase\Payments\Clpayment;
use Essential\Restate\Common\Customer\Customer;


use Essential\Restate\Traitval\Traitval;

class PaypalStandard {

	use Traitval;
	public function __construct() {
		add_action( 'cl_paypal_cc_form', '__return_false' );
		add_action( 'cl_gateway_paypal', array( $this, 'cl_process_paypal_purchase' ) );
		add_action( 'init', array( $this, 'cl_listen_for_paypal_ipn' ) );
		add_action( 'cl_verify_paypal_ipn', array( $this, 'cl_process_paypal_ipn' ) );
		add_action( 'cl_paypal_web_accept', array( $this, 'cl_process_paypal_web_accept_and_cart' ), 10, 2 );
		add_filter( 'cl_payment_confirm_paypal', array( $this, 'cl_paypal_success_page_content' ) );
		add_action( 'template_redirect', array( $this, 'cl_paypal_process_pdt_on_return' ) );
		add_filter( 'cl_get_payment_transaction_id-paypal', array( $this, 'cl_paypal_get_payment_transaction_id' ), 10, 1 );
		add_filter( 'cl_payment_details_transaction_id-paypal', array( $this, 'cl_paypal_link_transaction_id' ), 10, 2 );
		add_action( 'cl_view_order_details_before', array( $this, 'cl_paypal_refund_admin_js' ), 100 );
		add_action( 'cl_pre_refund_payment', array( $this, 'cl_maybe_refund_paypal_purchase' ), 999 );

	}

	function cl_process_paypal_purchase( $purchase_data ) {
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
			'gateway'      => 'paypal',
			'status'       => ! empty( $purchase_data['buy_now'] ) ? 'private' : 'pending',
		);

		// Record the pending payment
		$payment = cl_insert_payment( $payment_data );

		// Check payment
		if ( ! $payment ) {

			cl_debug_log( 'not payment: ' );
			// Record the error
			WPERECCP()->front->gateways->cl_record_gateway_error( __( 'Payment Error', 'essential-wp-real-estate' ), sprintf( __( 'Payment creation failed before sending buyer to PayPal. Payment data: %s', 'essential-wp-real-estate' ), json_encode( $payment_data ) ), $payment );
			// Problems? send back
			WPERECCP()->front->checkout->cl_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['cl-gateway'] );
		} else {
			cl_debug_log( ' payment true: ' );
			// Only send to PayPal if the pending payment is created successfully

			$listener_url = add_query_arg( 'cl-listener', 'IPN', home_url( 'index.php' ) );

			// Set the session data to recover this payment in the event of abandonment or error.
			WPERECCP()->front->session->set( 'cl_resume_payment', $payment );

			// Get the success url
			$return_url = add_query_arg(
				array(
					'payment-confirmation' => 'paypal',
					'payment-id'           => $payment,
				),
				get_permalink( cl_admin_get_option( 'success_page', false ) )
			);

			// Get the PayPal redirect uri
			$paypal_redirect = trailingslashit( $this->cl_get_paypal_redirect() ) . '?';

			// Setup PayPal arguments
			$paypal_args = array(
				'cmd'           => '_xclick',
				'business'      => cl_admin_get_option( 'paypal_email', false ),
				'business'      => cl_admin_get_option( 'paypal_email', false ),
				'email'         => $purchase_data['user_email'],
				'first_name'    => $purchase_data['user_info']['first_name'],
				'last_name'     => $purchase_data['user_info']['last_name'],
				'invoice'       => $purchase_data['purchase_key'],
				'no_shipping'   => '1',
				'shipping'      => '0',
				'no_note'       => '1',
				'currency_code' => WPERECCP()->common->options->cl_get_currency(),
				'charset'       => get_bloginfo( 'charset' ),
				'custom'        => $payment,
				'rm'            => '2',
				'return'        => $return_url,
				'cancel_return' => WPERECCP()->front->checkout->cl_get_failed_transaction_uri( '?payment-id=' . $payment ),
				'notify_url'    => $listener_url,
				'image_url'     => $this->cl_get_paypal_image_url(),
				'cbt'           => get_bloginfo( 'name' ),
				'bn'            => 'CL_LISTING',
			);

			if ( ! empty( $purchase_data['user_info']['address'] ) ) {
				$paypal_args['address1'] = $purchase_data['user_info']['address']['line1'];
				$paypal_args['address2'] = $purchase_data['user_info']['address']['line2'];
				$paypal_args['city']     = $purchase_data['user_info']['address']['city'];
				$paypal_args['country']  = $purchase_data['user_info']['address']['country'];
			}

			$paypal_extra_args = array(
				'cmd'    => '_cart',
				'upload' => '1',
			);

			$paypal_args = array_merge( $paypal_extra_args, $paypal_args );

			// Add cart items
			$i          = 1;
			$paypal_sum = 0;
			$item_name  = '';
			if ( is_array( $purchase_data['cart_details'] ) && ! empty( $purchase_data['cart_details'] ) ) {
				foreach ( $purchase_data['cart_details'] as $item ) {

					$item_amount = round( ( $item['subtotal'] / $item['quantity'] ) - ( $item['discount'] / $item['quantity'] ), 2 );

					if ( $item_amount <= 0 ) {
						$item_amount = 0;
					}
					$nameofitem = stripslashes_deep( html_entity_decode( WPERECCP()->front->cart->cl_get_cart_item_name( $item ), ENT_COMPAT, 'UTF-8' ) );

					$paypal_args[ 'item_name_' . $i ] = $nameofitem;
					$paypal_args[ 'quantity_' . $i ]  = $item['quantity'];
					$paypal_args[ 'amount_' . $i ]    = $item_amount;

					if ( WPERECCP()->common->options->cl_use_skus() ) {
						$paypal_args[ 'item_number_' . $i ] = WPERECCP()->front->listingsaction->cl_get_listing_sku( $item['id'] );
					}

					$paypal_sum += ( $item_amount * $item['quantity'] );
					$item_name  .= $nameofitem . '|';
					$i++;
				}
			}

			// Calculate discount
			$discounted_amount = 0.00;
			if ( ! empty( $purchase_data['fees'] ) ) {
				$i = empty( $i ) ? 1 : $i;
				foreach ( $purchase_data['fees'] as $fee ) {
					if ( empty( $fee['listing_id'] ) && floatval( $fee['amount'] ) > '0' ) {
						echo 'dfsdf';
						// this is a positive fee
						$paypal_args[ 'item_name_' . $i ] = stripslashes_deep( html_entity_decode( wp_strip_all_tags( $fee['label'] ), ENT_COMPAT, 'UTF-8' ) );
						$paypal_args[ 'quantity_' . $i ]  = '1';
						$paypal_args[ 'amount_' . $i ]    = WPERECCP()->common->formatting->cl_sanitize_amount( $fee['amount'] );
						$i++;
					} elseif ( empty( $fee['listing_id'] ) ) {
						// This is a negative fee (discount) not assigned to a specific listing
						$discounted_amount += abs( $fee['amount'] );
					}
				}
			}

			$price_before_discount = $purchase_data['price'];
			if ( $discounted_amount > '0' ) {
				$paypal_args['discount_amount_cart'] = WPERECCP()->common->formatting->cl_sanitize_amount( $discounted_amount );

				/*
				* Add the discounted amount back onto the price to get the "price before discount". We do this
				* to avoid double applying any discounts below.
				*/
				$price_before_discount += $paypal_args['discount_amount_cart'];
			}

			// Check if there are any additional discounts we need to add that we haven't already accounted for.
			if ( $paypal_sum > $price_before_discount ) {
				$difference = round( $paypal_sum - $price_before_discount, 2 );
				if ( ! isset( $paypal_args['discount_amount_cart'] ) ) {
					$paypal_args['discount_amount_cart'] = 0;
				}
				$paypal_args['discount_amount_cart'] += $difference;
			}

			// Add taxes to the cart
			if ( WPERECCP()->front->tax->cl_use_taxes() ) {

				$paypal_args['tax_cart'] = WPERECCP()->common->formatting->cl_sanitize_amount( $purchase_data['tax'] );
			}
			$paypal_args['amount']    = $paypal_sum;
			$paypal_args['item_name'] = $item_name;

			$paypal_args = apply_filters( 'cl_paypal_redirect_args', $paypal_args, $purchase_data );

			// cl_debug_log('PayPal arguments: ' . print_r($paypal_args, true));

			// Build query
			$paypal_redirect .= http_build_query( $paypal_args );

			// Fix for some sites that encode the entities
			$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

			// Redirect to PayPal
			wp_redirect( $paypal_redirect );
			exit;
		}
	}

	function cl_listen_for_paypal_ipn() {
		if ( isset( $_GET['cl-listener'] ) && 'ipn' === strtolower( $_GET['cl-listener'] ) ) {

			$token = cl_admin_get_option( 'paypal_identity_token' );
			if ( $token ) {
				sleep( 5 );
			}
			do_action( 'cl_verify_paypal_ipn' );
		}
	}

	function cl_process_paypal_ipn() {
		cl_debug_log( 'cl_process_paypal_ipn Start' );
		cl_debug_log( 'cl_process_paypal_ipn POST' . print_r( cl_sanitization($_POST), true ) );
		// Check the request method is POST
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
			return;
		}

		cl_debug_log( 'cl_process_paypal_ipn() running during PayPal IPN processing' );

		// Set initial post data to empty string
		$post_data = '';

		// Fallback just in case post_max_size is lower than needed
		if ( ini_get( 'allow_url_fopen' ) ) {
			$post_data = file_get_contents( 'php://input' );
			cl_debug_log( 'allow_url_fopen data array: ' . print_r( $post_data, true ) );
		} else {

			cl_debug_log( 'ELSE' );
			// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
			ini_set( 'post_max_size', '12M' );
		}
		// Start the encoded data collection with notification command
		$encoded_data = 'cmd=_notify-validate';

		// Get current arg separator
		$arg_separator = cl_get_php_arg_separator_output();

		// Verify there is a post_data
		if ( $post_data || strlen( $post_data ) > 0 ) {
			// Append the data
			$encoded_data .= $arg_separator . $post_data;
		} else {
			// Check if POST is empty
			if ( empty( $_POST ) ) {
				cl_debug_log( 'empty post' );
				// Nothing to do
				return;
			} else {
				// Loop through each POST
				$arr = cl_sanitization($_POST);
				foreach ( $arr as $key => $value ) {
					// Encode the value and append the data
					$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
				}
			}
		}

		// Convert collected post data to an array
		parse_str( $encoded_data, $encoded_data_array );

		foreach ( $encoded_data_array as $key => $value ) {

			if ( false !== strpos( $key, 'amp;' ) ) {
				$new_key = str_replace( '&amp;', '&', $key );
				$new_key = str_replace( 'amp;', '&', $new_key );

				unset( $encoded_data_array[ $key ] );
				$encoded_data_array[ $new_key ] = $value;
			}
		}

		/**
		 * PayPal Web IPN Verification
		 *
		 * Allows filtering the IPN Verification data that PayPal passes back in via IPN with PayPal Standard
		 *
		 * @since 2.8.13
		 *
		 * @param array $data      The PayPal Web Accept Data
		 */
		$encoded_data_array = apply_filters( 'cl_process_paypal_ipn_data', $encoded_data_array );

		cl_debug_log( 'encoded_data_array data array: ' . print_r( $encoded_data_array, true ) );

		// if (!cl_admin_get_option('disable_paypal_verification')) {

		// Validate the IPN
		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 60,
			'redirection' => 5,
			'httpversion' => '1.1',
			'compress'    => false,
			'decompress'  => false,
			'user-agent'  => 'CL IPN Verification/1.0',
			'body'        => wp_unslash( $encoded_data_array ),
		);

		// cl_debug_log('Attempting to verify PayPal IPN. Data sent for verification: ' . print_r($remote_post_vars, true));

		// Get response
		$api_response = wp_remote_post( $this->cl_get_paypal_redirect( true, true ), $remote_post_vars );

		if ( is_wp_error( $api_response ) ) {
			WPERECCP()->front->gateways->cl_record_gateway_error( __( 'IPN Error', 'essential-wp-real-estate' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'essential-wp-real-estate' ), json_encode( $api_response ) ) );
			cl_debug_log( 'Invalid IPN verification response. IPN data: ' );

			return; // Something went wrong
		}

		// if (wp_remote_retrieve_body($api_response) !== 'VERIFIED' && cl_admin_get_option('disable_paypal_verification', false)) {
		if ( wp_remote_retrieve_body( $api_response ) !== 'VERIFIED' ) {
			WPERECCP()->front->gateways->cl_record_gateway_error( __( 'IPN Error', 'essential-wp-real-estate' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'essential-wp-real-estate' ), json_encode( $api_response ) ) );
			cl_debug_log( 'Invalid IPN verification response not varified. IPN data: ' );

			return; // Response not okay
		}

		cl_debug_log( 'IPN verified successfully' );
		// }

		// Check if $post_data_array has been populated
		if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ) {
			return;
		}

		$defaults = array(
			'txn_type'       => '',
			'payment_status' => '',
		);

		$encoded_data_array = wp_parse_args( $encoded_data_array, $defaults );

		$payment_id = 0;

		if ( ! empty( $encoded_data_array['parent_txn_id'] ) ) {
			$payment_id = cl_get_purchase_id_by_transaction_id( $encoded_data_array['parent_txn_id'] );
		} elseif ( ! empty( $encoded_data_array['txn_id'] ) ) {
			$payment_id = cl_get_purchase_id_by_transaction_id( $encoded_data_array['txn_id'] );
		}

		if ( empty( $payment_id ) ) {
			$payment_id = ! empty( $encoded_data_array['custom'] ) ? absint( $encoded_data_array['custom'] ) : 0;
		}

		if ( has_action( 'cl_paypal_' . $encoded_data_array['txn_type'] ) ) {
			// Allow PayPal IPN types to be processed separately
			do_action( 'cl_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $payment_id );
			cl_debug_log( 'IF action' . print_r( $encoded_data_array['txn_type'], true ) );
		} else {
			// Fallback to web accept just in case the txn_type isn't present
			cl_debug_log( 'else action' . print_r( $encoded_data_array, true ) );
			do_action( 'cl_paypal_web_accept', $encoded_data_array, $payment_id );
		}
		exit;
	}


	function cl_process_paypal_web_accept_and_cart( $data, $payment_id ) {

		/**
		 * PayPal Web Accept Data
		 *
		 * Allows filtering the Web Accept data that PayPal passes back in via IPN with PayPal Standard
		 *
		 * @since 2.8.13
		 *
		 * @param array $data      The PayPal Web Accept Data
		 * @param int  $payment_id The Payment ID associated with this IPN request
		 */
		$data = apply_filters( 'cl_paypal_web_accept_and_cart_data', $data, $payment_id );

		if ( $data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' && $data['payment_status'] != 'Refunded' ) {
			return;
		}

		if ( empty( $payment_id ) ) {
			return;
		}

		$payment = new Clpayment( $payment_id );

		// Collect payment details
		$purchase_key   = isset( $data['invoice'] ) ? $data['invoice'] : $data['item_number'];
		$paypal_amount  = $data['mc_gross'];
		$payment_status = strtolower( $data['payment_status'] );
		$currency_code  = strtolower( $data['mc_currency'] );
		$business_email = isset( $data['business'] ) && is_email( $data['business'] ) ? trim( $data['business'] ) : trim( $data['receiver_email'] );

		if ( $payment->gateway != 'paypal' ) {
			return; // this isn't a PayPal standard IPN
		}

		// Verify payment recipient
		if ( strcasecmp( $business_email, trim( cl_admin_get_option( 'paypal_email', false ) ) ) != 0 ) {
			WPERECCP()->front->gateways->cl_record_gateway_error( __( 'IPN Error', 'essential-wp-real-estate' ), sprintf( __( 'Invalid business email in IPN response. IPN data: %s', 'essential-wp-real-estate' ), json_encode( $data ) ), $payment_id );
			cl_debug_log( 'Invalid business email in IPN response. IPN data: ' . print_r( $data, true ) );
			cl_update_payment_status( $payment_id, 'failed' );
			cl_insert_payment_note( $payment_id, __( 'Payment failed due to invalid PayPal business email.', 'essential-wp-real-estate' ) );
			return;
		}

		// Verify payment currency
		if ( $currency_code != strtolower( $payment->currency ) ) {

			WPERECCP()->front->gateways->cl_record_gateway_error( __( 'IPN Error', 'essential-wp-real-estate' ), sprintf( __( 'Invalid currency in IPN response. IPN data: %s', 'essential-wp-real-estate' ), json_encode( $data ) ), $payment_id );
			cl_debug_log( 'Invalid currency in IPN response. IPN data: ' . print_r( $data, true ) );
			cl_update_payment_status( $payment_id, 'failed' );
			cl_insert_payment_note( $payment_id, __( 'Payment failed due to invalid currency in PayPal IPN.', 'essential-wp-real-estate' ) );
			return;
		}

		if ( empty( $payment->email ) ) {

			// This runs when a Buy Now purchase was made. It bypasses checkout so no personal info is collected until PayPal

			// Setup and store the customers's details
			$address            = array();
			$address['line1']   = ! empty( $data['address_street'] ) ? cl_sanitization( $data['address_street'] ) : false;
			$address['city']    = ! empty( $data['address_city'] ) ? cl_sanitization( $data['address_city'] ) : false;
			$address['state']   = ! empty( $data['address_state'] ) ? cl_sanitization( $data['address_state'] ) : false;
			$address['country'] = ! empty( $data['address_country_code'] ) ? cl_sanitization( $data['address_country_code'] ) : false;
			$address['zip']     = ! empty( $data['address_zip'] ) ? cl_sanitization( $data['address_zip'] ) : false;

			$payment->email      = cl_sanitization( $data['payer_email'] );
			$payment->first_name = cl_sanitization( $data['first_name'] );
			$payment->last_name  = cl_sanitization( $data['last_name'] );
			$payment->address    = $address;

			if ( empty( $payment->customer_id ) ) {

				$customer = new Customer( $payment->email );
				if ( ! $customer || $customer->id < 1 ) {

					$customer->create(
						array(
							'email'   => $payment->email,
							'name'    => $payment->first_name . ' ' . $payment->last_name,
							'user_id' => $payment->user_id,
						)
					);
				}

				$payment->customer_id = $customer->id;
			}

			$payment->save();
		}

		if ( empty( $customer ) ) {

			$customer = new Customer( $payment->customer_id );
		}

		// Record the payer email on the Customer record if it is different than the email entered on checkout
		if ( ! empty( $data['payer_email'] ) && ! in_array( strtolower( $data['payer_email'] ), array_map( 'strtolower', $customer->emails ) ) ) {

			$customer->add_email( strtolower( $data['payer_email'] ) );
		}

		if ( $payment_status == 'refunded' || $payment_status == 'reversed' ) {

			// Process a refund
			$this->cl_process_paypal_refund( $data, $payment_id );
		} else {

			if ( get_post_status( $payment_id ) == 'publish' ) {
				return; // Only complete payments once
			}

			// Retrieve the total purchase amount (before PayPal)
			$payment_amount = cl_get_payment_amount( $payment_id );

			if ( number_format( (float) $paypal_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
				// The prices don't match
				WPERECCP()->front->gateways->cl_record_gateway_error( __( 'IPN Error', 'essential-wp-real-estate' ), sprintf( __( 'Invalid payment amount in IPN response. IPN data: %s', 'essential-wp-real-estate' ), json_encode( $data ) ), $payment_id );
				// cl_debug_log('Invalid payment amount in IPN response. IPN data: ' . printf($data, true));
				cl_update_payment_status( $payment_id, 'failed' );
				cl_insert_payment_note( $payment_id, __( 'Payment failed due to invalid amount in PayPal IPN.', 'essential-wp-real-estate' ) );
				return;
			}
			if ( $purchase_key != cl_get_payment_key( $payment_id ) ) {
				// Purchase keys don't match
				// cl_debug_log('Invalid purchase key in IPN response. IPN data: ' . printf($data, true));
				WPERECCP()->front->gateways->cl_record_gateway_error( __( 'IPN Error', 'essential-wp-real-estate' ), sprintf( __( 'Invalid purchase key in IPN response. IPN data: %s', 'essential-wp-real-estate' ), json_encode( $data ) ), $payment_id );
				cl_update_payment_status( $payment_id, 'failed' );
				cl_insert_payment_note( $payment_id, __( 'Payment failed due to invalid purchase key in PayPal IPN.', 'essential-wp-real-estate' ) );
				return;
			}

			if ( 'completed' == $payment_status || cl_is_test_mode() ) {

				cl_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'essential-wp-real-estate' ), $data['txn_id'] ) );
				cl_set_payment_transaction_id( $payment_id, $data['txn_id'] );
				cl_update_payment_status( $payment_id, 'publish' );
			} elseif ( 'pending' == $payment_status && isset( $data['pending_reason'] ) ) {

				// Look for possible pending reasons, such as an echeck

				$note = '';

				switch ( strtolower( $data['pending_reason'] ) ) {

					case 'echeck':
						$note            = __( 'Payment made via eCheck and will clear automatically in 5-8 days', 'essential-wp-real-estate' );
						$payment->status = 'processing';
						$payment->save();
						break;

					case 'address':
						$note = __( 'Payment requires a confirmed customer address and must be accepted manually through PayPal', 'essential-wp-real-estate' );

						break;

					case 'intl':
						$note = __( 'Payment must be accepted manually through PayPal due to international account regulations', 'essential-wp-real-estate' );

						break;

					case 'multi-currency':
						$note = __( 'Payment received in non-shop currency and must be accepted manually through PayPal', 'essential-wp-real-estate' );

						break;

					case 'paymentreview':
					case 'regulatory_review':
						$note = __( 'Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations', 'essential-wp-real-estate' );

						break;

					case 'unilateral':
						$note = __( 'Payment was sent to non-confirmed or non-registered email address.', 'essential-wp-real-estate' );

						break;

					case 'upgrade':
						$note = __( 'PayPal account must be upgraded before this payment can be accepted', 'essential-wp-real-estate' );

						break;

					case 'verify':
						$note = __( 'PayPal account is not verified. Verify account in order to accept this payment', 'essential-wp-real-estate' );

						break;

					case 'other':
						$note = __( 'Payment is pending for unknown reasons. Contact PayPal support for assistance', 'essential-wp-real-estate' );

						break;
				}

				if ( ! empty( $note ) ) {

					cl_debug_log( 'Payment not marked as completed because: ' . $note );
					cl_insert_payment_note( $payment_id, $note );
				}
			}
		}
	}

	function cl_process_paypal_refund( $data, $payment_id = 0 ) {

		/**
		 * PayPal Process Refund Data
		 *
		 * Allows filtering the Refund data that PayPal passes back in via IPN with PayPal Standard
		 *
		 * @since 2.8.13
		 *
		 * @param array $data      The PayPal Refund data
		 * @param int  $payment_id The Payment ID associated with this IPN request
		 */
		$data = apply_filters( 'cl_process_paypal_refund_data', $data, $payment_id );

		// Collect payment details
		if ( empty( $payment_id ) ) {
			return;
		}

		if ( get_post_status( $payment_id ) == 'refunded' ) {
			return; // Only refund payments once
		}

		$payment_amount = cl_get_payment_amount( $payment_id );
		$refund_amount  = $data['mc_gross'] * -1;

		if ( number_format( (float) $refund_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {

			cl_insert_payment_note( $payment_id, sprintf( __( 'Partial PayPal refund processed: %s', 'essential-wp-real-estate' ), $data['parent_txn_id'] ) );
			return; // This is a partial refund

		}

		cl_insert_payment_note( $payment_id, sprintf( __( 'PayPal Payment #%1$s Refunded for reason: %2$s', 'essential-wp-real-estate' ), $data['parent_txn_id'], $data['reason_code'] ) );
		cl_insert_payment_note( $payment_id, sprintf( __( 'PayPal Refund Transaction ID: %s', 'essential-wp-real-estate' ), $data['txn_id'] ) );
		cl_update_payment_status( $payment_id, 'refunded' );
	}


	function cl_get_paypal_redirect( $ssl_check = false, $ipn = false ) {

		$protocol = 'http://';
		if ( is_ssl() || ! $ssl_check ) {
			$protocol = 'https://';
		}

		// Check the current payment mode
		if ( cl_is_test_mode() ) {

			// Test mode

			if ( $ipn ) {

				$paypal_uri = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
			} else {

				$paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';
			}
		} else {

			// Live mode

			if ( $ipn ) {

				$paypal_uri = 'https://ipnpb.paypal.com/cgi-bin/webscr';
			} else {

				$paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';
			}
		}

		return apply_filters( 'cl_paypal_uri', $paypal_uri, $ssl_check, $ipn );
	}

	function cl_get_paypal_image_url() {
		$image_url = trim( cl_admin_get_option( 'paypal_image_url', '' ) );
		return apply_filters( 'cl_paypal_image_url', $image_url );
	}

	function cl_paypal_success_page_content( $content ) {
		if ( ! isset( $_GET['payment-id'] ) && ! cl_get_purchase_session() ) {
			return $content;
		}

		WPERECCP()->front->cart->cl_empty_cart();

		$payment_id = isset( $_GET['payment-id'] ) ? absint( cl_sanitization( $_GET['payment-id'] ) ) : false;

		if ( ! $payment_id ) {
			$session    = cl_get_purchase_session();
			$payment_id = cl_get_purchase_id_by_key( $session['purchase_key'] );
		}

		$payment = new Clpayment( $payment_id );

		if ( $payment->ID > 0 && 'pending' == $payment->status ) {

			// Payment is still pending so show processing indicator to fix the Race Condition, issue #
			ob_start();

			cl_get_template_part( 'payment', 'processing' );

			$content = ob_get_clean();
		}

		$product_html = '';

		foreach ( $payment->cart_details as $product ) {
			$product_html .= '
			<tr>
				<td>' . esc_html( $product['name'] ) . '</td>
				<td>' . esc_html( $product['item_price'] ) . '</td>
				<td>' . esc_html( $product['quantity'] ) . '</td>
				<td>' . esc_html( $product['discount'] ) . '</td>
				<td>' . esc_html( $product['subtotal'] ) . '</td>
				<td>' . esc_html( $product['tax'] ) . '</td>
			</tr>
			';
		}

		// Heading
		$content = '
		<div class="container">
			<div class="row">
				<div class="col-lg-6">
					<p class="feedback success">Thank you for your purchase</p>
					<table class="table table-bordered">
						<tr>
							<td>Payment</td>
							<td>' . esc_html( $payment_id ) . '</td>
						</tr>
						<tr>
							<td>Key</td>
							<td>' . esc_html( $payment->key ) . '</td>
						</tr>
						<tr>
							<td>Payment Status</td>
							<td class="payment_status"><span class="' . esc_attr( $payment->post_status ) . '">' . esc_html( $payment->post_status ) . '</span></td>
						</tr>
						<tr>
							<td>Payment Method</td>
							<td>' . esc_html( $payment->gateway ) . '</td>
						</tr>
						<tr>
							<td>Date</td>
							<td>' . esc_html( $payment->date ) . '</td>
						</tr>
						<tr>
							<td>Sub Total</td>
							<td>' . esc_html( $payment->subtotal ) . '</td>
						</tr>
					</table>
				</div>
				<div class="col-lg-6">
					<p class="feedback">Products</p>
					<table class="table table-bordered">
					<tr>
						<td>Name</td>
						<td>Item Price</td>
						<td>Quantity</td>
						<td>Discount</td>
						<td>Subtotal</td>
						<td>Tax</td>
					</tr>' . $product_html . '</table>
				</div>
			</div>
		</div>
		';
		return $content;
	}


	function cl_paypal_process_pdt_on_return() {
		if ( isset( $_GET['payment-id'] ) ) {
			cl_debug_log( 'cl_paypal_process_pdt_on_return' . print_r( cl_sanitization($_GET), true ) );
		}

		if ( ! isset( $_GET['payment-id'] ) || ! isset( $_GET['tx'] ) ) {
			cl_debug_log( 'return not get' );
			return;
		}

		$token = cl_admin_get_option( 'paypal_identity_token' );

		cl_debug_log( 'cl_paypal_process_pdt_on_return Token' . $token );

		if ( ! cl_is_success_page() || ! $token || ! WPERECCP()->front->gateways->cl_is_gateway_active( 'paypal' ) ) {
			cl_debug_log( 'return' );
			return;
		}

		$payment_id = isset( $_GET['payment-id'] ) ? absint( cl_sanitization( $_GET['payment-id'] ) ) : false;

		if ( empty( $payment_id ) ) {
			cl_debug_log( 'return payment_id' );
			return;
		}

		$purchase_session = cl_get_purchase_session();
		$payment          = new Clpayment( $payment_id );

		// If there is no purchase session, don't try and fire PDT.
		if ( empty( $purchase_session ) ) {
			cl_debug_log( 'return purchase_session' );
			return;
		}

		// Do not fire a PDT verification if the purchase session does not match the payment-id PDT is asking to verify.
		if ( ! empty( $purchase_session['purchase_key'] ) && $payment->key !== $purchase_session['purchase_key'] ) {
			cl_debug_log( 'return purchase_key' );
			return;
		}

		if ( $token && ! empty( $_GET['tx'] ) && $payment->ID > 0 ) {
			cl_debug_log( 'find TX' );

			// An identity token has been provided in settings so let's immediately verify the purchase

			$remote_post_vars = array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'compress'    => false,
				'decompress'  => false,
				'user-agent'  => 'CL PDT Verification/1.0 ;',
				'sslverify'   => false,
				'body'        => array(
					'tx'  => cl_sanitization( $_GET['tx'] ),
					'at'  => $token,
					'cmd' => '_notify-synch',
				),
			);

			// Sanitize the data for debug logging.
			$debug_args               = $remote_post_vars;
			$debug_args['body']['at'] = str_pad( substr( $debug_args['body']['at'], -6 ), strlen( $debug_args['body']['at'] ), '*', STR_PAD_LEFT );
			// cl_debug_log('Attempting to verify PayPal payment with PDT. Args: ' . print_r($debug_args, true));

			// cl_debug_log('Sending PDT Verification request to ' . cl_get_paypal_redirect());

			$request = wp_remote_post( $this->cl_get_paypal_redirect(), $remote_post_vars );

			if ( ! is_wp_error( $request ) ) {

				$body = wp_remote_retrieve_body( $request );

				// parse the data
				$lines = explode( "\n", trim( $body ) );
				$data  = array();
				if ( strcmp( $lines[0], 'SUCCESS' ) == 0 ) {

					for ( $i = 1; $i < count( $lines ); $i++ ) {
						$parsed_line                          = explode( '=', $lines[ $i ], 2 );
						$data[ urldecode( $parsed_line[0] ) ] = urldecode( $parsed_line[1] );
					}

					if ( isset( $data['mc_gross'] ) ) {

						$total = $data['mc_gross'];
					} elseif ( isset( $data['payment_gross'] ) ) {

						$total = $data['payment_gross'];
					} elseif ( isset( $_REQUEST['amt'] ) ) {

						$total = cl_sanitization( $_REQUEST['amt'] );
					} else {

						$total = null;
					}

					if ( is_null( $total ) ) {

						// cl_debug_log('Attempt to verify PayPal payment with PDT failed due to payment total missing');
						$payment->add_note( __( 'Payment could not be verified while validating PayPal PDT. Missing payment total fields.', 'essential-wp-real-estate' ) );
						$payment->status = 'pending';
					} elseif ( (float) $total < (float) $payment->total ) {

						/**
						 * Here we account for payments that are less than the expected results only. There are times that
						 * PayPal will sometimes round and have $0.01 more than the amount. The goal here is to protect store owners
						 * from getting paid less than expected.
						 */
						// cl_debug_log('Attempt to verify PayPal payment with PDT failed due to payment total discrepancy');
						$payment->add_note( sprintf( __( 'Payment failed while validating PayPal PDT. Amount expected: %1$f. Amount Received: %2$f', 'essential-wp-real-estate' ), $payment->total, $data['payment_gross'] ) );
						$payment->status = 'failed';
					} else {

						// Verify the status
						switch ( strtolower( $data['payment_status'] ) ) {

							case 'completed':
								$payment->status = 'publish';
								break;

							case 'failed':
								$payment->status = 'failed';
								break;

							default:
								$payment->status = 'pending';
								break;
						}
					}

					$payment->transaction_id = cl_sanitization( $_GET['tx'] );
					$payment->save();
				} elseif ( strcmp( $lines[0], 'FAIL' ) == 0 ) {

					// cl_debug_log('Attempt to verify PayPal payment with PDT failed due to PDT failure response: ' . print_r($body, true));
					$payment->add_note( __( 'Payment failed while validating PayPal PDT.', 'essential-wp-real-estate' ) );
					$payment->status = 'failed';
					$payment->save();
				} else {

					// cl_debug_log('Attempt to verify PayPal payment with PDT met with an unexpected result: ' . print_r($body, true));
					$payment->add_note( __( 'PayPal PDT encountered an unexpected result, payment set to pending', 'essential-wp-real-estate' ) );
					$payment->status = 'pending';
					$payment->save();
				}
			} else {

				cl_debug_log( 'Attempt to verify PayPal payment with PDT failed. Request return: ' . print_r( $request, true ) );
			}
		}
	}

	function cl_paypal_get_payment_transaction_id( $payment_id ) {
		$transaction_id = '';
		$notes          = cl_get_payment_notes( $payment_id );
		foreach ( $notes as $note ) {
			if ( preg_match( '/^PayPal Transaction ID: ([^\s]+)/', $note->comment_content, $match ) ) {
				$transaction_id = $match[1];
				continue;
			}
		}
		return apply_filters( 'cl_paypal_set_payment_transaction_id', $transaction_id, $payment_id );
	}
	function cl_paypal_link_transaction_id( $transaction_id, $payment_id ) {

		$payment         = new Clpayment( $payment_id );
		$sandbox         = 'test' === $payment->mode ? 'sandbox.' : '';
		$paypal_base_url = 'https://www.' . $sandbox . 'paypal.com/activity/payment/';
		$transaction_url = '<a href="' . esc_url( $paypal_base_url . $transaction_id ) . '" target="_blank">' . $transaction_id . '</a>';

		return apply_filters( 'cl_paypal_link_payment_details_transaction_id', $transaction_url );
	}
	function cl_paypal_refund_admin_js( $payment_id = 0 ) {

		// If not the proper gateway, return early.
		if ( 'paypal' !== cl_get_payment_gateway( $payment_id ) ) {
			return;
		}
		// If our credentials are not set, return early.
		if ( cl_is_test_mode() ) {
			$key = 'sandbox';
		} else {
			$key = 'live';
		}

		$username  = cl_admin_get_option( 'paypal_' . $key . '_api_username' );
		$password  = cl_admin_get_option( 'paypal_' . $key . '_api_password' );
		$signature = cl_admin_get_option( 'paypal_' . $key . '_api_signature' );

		if ( empty( $username ) || empty( $password ) || empty( $signature ) ) {
			return;
		}

		// Localize the refund checkbox label.
		$label = __( 'Refund Payment in PayPal', 'essential-wp-real-estate' );
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('select[name=cl-payment-status]').change(function() {
					if ('refunded' == $(this).val()) {
						$(this).parent().parent().append('<input type="checkbox" id="cl-paypal-refund" name="cl-paypal-refund" value="1" style="margin-top:0">');
						$(this).parent().parent().append('<label for="cl-paypal-refund"><?php echo esc_html( $label ); ?></label>');
					} else {
						$('#cl-paypal-refund').remove();
						$('label[for="cl-paypal-refund"]').remove();
					}
				});
			});
		</script>
		<?php
	}

	function cl_maybe_refund_paypal_purchase( Clpayment $payment ) {

		if ( ! current_user_can( 'edit_shop_payments', $payment->ID ) ) {
			return;
		}

		if ( empty( $_POST['cl-paypal-refund'] ) ) {
			return;
		}

		$processed = $payment->get_meta( '_cl_paypal_refunded', true );

		// If the status is not set to "refunded", return early.
		if ( 'publish' !== $payment->old_status && 'revoked' !== $payment->old_status ) {
			return;
		}

		// If not PayPal/PayPal Express, return early.
		if ( 'paypal' !== $payment->gateway ) {
			return;
		}

		// If the payment has already been refunded in the past, return early.
		if ( $processed ) {
			return;
		}

		// Process the refund in PayPal.
		$this->cl_refund_paypal_purchase( $payment );
	}

	function cl_refund_paypal_purchase( $payment ) {

		if ( ! $payment instanceof Clpayment && is_numeric( $payment ) ) {
			$payment = new Clpayment( $payment );
		}

		// Set PayPal API key credentials.
		$credentials = array(
			'api_endpoint'  => 'test' == $payment->mode ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp',
			'api_username'  => cl_admin_get_option( 'paypal_' . $payment->mode . '_api_username' ),
			'api_password'  => cl_admin_get_option( 'paypal_' . $payment->mode . '_api_password' ),
			'api_signature' => cl_admin_get_option( 'paypal_' . $payment->mode . '_api_signature' ),
		);

		$credentials = apply_filters( 'cl_paypal_refund_api_credentials', $credentials, $payment );

		$body = array(
			'USER'          => $credentials['api_username'],
			'PWD'           => $credentials['api_password'],
			'SIGNATURE'     => $credentials['api_signature'],
			'VERSION'       => '124',
			'METHOD'        => 'RefundTransaction',
			'TRANSACTIONID' => $payment->transaction_id,
			'REFUNDTYPE'    => 'Full',
		);

		$body = apply_filters( 'cl_paypal_refund_body_args', $body, $payment );

		// Prepare the headers of the refund request.
		$headers = array(
			'Content-Type'  => 'application/x-www-form-urlencoded',
			'Cache-Control' => 'no-cache',
		);

		$headers = apply_filters( 'cl_paypal_refund_header_args', $headers, $payment );

		// Prepare args of the refund request.
		$args = array(
			'body'        => $body,
			'headers'     => $headers,
			'httpversion' => '1.1',
		);

		$args = apply_filters( 'cl_paypal_refund_request_args', $args, $payment );

		$error_msg = '';
		$request   = wp_remote_post( $credentials['api_endpoint'], $args );

		if ( is_wp_error( $request ) ) {

			$success   = false;
			$error_msg = $request->get_error_message();
		} else {

			$body    = wp_remote_retrieve_body( $request );
			$code    = wp_remote_retrieve_response_code( $request );
			$message = wp_remote_retrieve_response_message( $request );
			if ( is_string( $body ) ) {
				wp_parse_str( $body, $body );
			}

			if ( empty( $code ) || 200 !== (int) $code ) {
				$success = false;
			}

			if ( empty( $message ) || 'OK' !== $message ) {
				$success = false;
			}

			if ( isset( $body['ACK'] ) && 'success' === strtolower( $body['ACK'] ) ) {
				$success = true;
			} else {
				$success = false;
				if ( isset( $body['L_LONGMESSAGE0'] ) ) {
					$error_msg = $body['L_LONGMESSAGE0'];
				} else {
					$error_msg = __( 'PayPal refund failed for unknown reason.', 'essential-wp-real-estate' );
				}
			}
		}

		if ( $success ) {

			// Prevents the PayPal Express one-time gateway from trying to process the refundl
			$payment->update_meta( '_cl_paypal_refunded', true );
			$payment->add_note( sprintf( __( 'PayPal refund transaction ID: %s', 'essential-wp-real-estate' ), $body['REFUNDTRANSACTIONID'] ) );
		} else {

			$payment->add_note( sprintf( __( 'PayPal refund failed: %s', 'essential-wp-real-estate' ), $error_msg ) );
		}

		// Run hook letting people know the payment has been refunded successfully.
		do_action( 'cl_paypal_refund_purchase', $payment );
	}

	public static function get_setting() {
		return array(

			'paypal_active'                => array(
				'id'   => 'paypal_active',
				'name' => __( 'Paypal Active', 'essential-wp-real-estate' ),
				'type' => 'checkbox',
			),

			'paypal_title'                 => array(
				'id'   => 'paypal_title',
				'name' => __( 'Title', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_des'                   => array(
				'id'   => 'paypal_des',
				'name' => __( 'Description', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_email'                 => array(
				'id'   => 'paypal_email',
				'name' => __( 'Paypal Email', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_sandbox'               => array(
				'id'   => 'paypal_sandbox_active',
				'name' => __( 'Paypal Sandbox', 'essential-wp-real-estate' ),
				'type' => 'checkbox',
			),
			'paypal_email_notify_ipn'      => array(
				'id'   => 'paypal_email_notify_ipn',
				'name' => __( 'IPN email notifications', 'essential-wp-real-estate' ),
				'type' => 'checkbox',
			),
			'paypal_email_reciver'         => array(
				'id'   => 'paypal_email_reciver',
				'name' => __( 'Receiver email', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_identity_token'        => array(
				'id'   => 'paypal_identity_token',
				'name' => __( 'PayPal identity token', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_invoice_prefix'        => array(
				'id'   => 'paypal_invoice_prefix',
				'name' => __( 'Invoice Prefix', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_image_url'             => array(
				'id'   => 'paypal_image_url',
				'name' => __( 'Image Url', 'essential-wp-real-estate' ),
				'type' => 'url',
			),

			'paypal_api_header'            => array(
				'id'   => 'paypal_api_header',
				'name' => __( 'API credentials', 'essential-wp-real-estate' ),
				'type' => 'header',
			),

			'paypal_live_api_username'     => array(
				'id'   => 'paypal_live_api_username',
				'name' => __( 'Live API username', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_live_api_password'     => array(
				'id'   => 'paypal_live_api_password',
				'name' => __( 'Live API password', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_live_api_signature'    => array(
				'id'   => 'paypal_live_api_signature',
				'name' => __( 'Live API signature', 'essential-wp-real-estate' ),
				'type' => 'text',
			),

			'paypal_sandbox_api_username'  => array(
				'id'   => 'paypal_sandbox_api_username',
				'name' => __( 'Sandbox API username', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_sandbox_api_password'  => array(
				'id'   => 'paypal_sandbox_api_password',
				'name' => __( 'Sandbox API password', 'essential-wp-real-estate' ),
				'type' => 'text',
			),
			'paypal_sandbox_api_signature' => array(
				'id'   => 'paypal_sandbox_api_signature',
				'name' => __( 'Sandbox API signature', 'essential-wp-real-estate' ),
				'type' => 'text',
			),

		);
	}








	function cl_paypal_process_pdt_on_return1() {

		$_GET = array(
			'payment-confirmation'   => 'paypal',
			'payment-id'             => '228',
			'PayerID'                => '67FZ6NYXXVAQU',
			'st'                     => 'Pending',
			'tx'                     => '31N96293BT125984X',
			'cc'                     => 'USD',
			'amt'                    => '1.00',
			'cm'                     => '228',
			'payer_email'            => 'tanvir_hasan_j-buyer@yahoo.com',
			'payer_id'               => '67FZ6NYXXVAQU',
			'payer_status'           => 'VERIFIED',
			'first_name'             => 'test',
			'last_name'              => 'buyer',
			'txn_id'                 => '31N96293BT125984X',
			'mc_currency'            => 'USD',
			'mc_gross'               => '1.00',
			'protection_eligibility' => 'INELIGIBLE',
			'payment_gross'          => '1.00',
			'payment_status'         => 'Pending',
			'pending_reason'         => 'unilateral',
			'payment_type'           => 'instant',
			'handling_amount'        => '0.00',
			'shipping'               => '0.00',
			'quantity'               => '1',
			'txn_type'               => 'web_accept',
			'payment_date'           => '2021-12-09T06:23:04Z',
			'notify_version'         => 'UNVERSIONED',
			'custom'                 => '228',
			'invoice'                => 'c2f24444ac8067fede6c14a3df0600fc',
			'verify_sign'            => 'AEN0Ii33.6.ifarrbhA9vryccVfFA6SeRdvnyOC2nVCPX7dT-R08MR1a',
		);

		$token = cl_admin_get_option( 'paypal_identity_token' );

		$payment_id = isset( $_GET['payment-id'] ) ? absint( cl_sanitization( $_GET['payment-id'] ) ) : false;

		$purchase_session = cl_get_purchase_session();
		$payment          = new Clpayment( $payment_id );

		// An identity token has been provided in settings so let's immediately verify the purchase

		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 60,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'user-agent'  => 'CL PDT Verification/1.0 ;',
			'body'        => array(
				'tx'  => cl_sanitization( '31N96293BT125984X' ),
				'at'  => 'cB_-Lj4Rc667y-5dCPUV5rXxfjjbf12MyaU5xc8WjY_TTjKiKVgHH1Bphz0',
				'cmd' => '_notify-synch',
			),
		);

		// Sanitize the data for debug logging.
		$debug_args = $remote_post_vars;

		$request = wp_remote_post( $this->cl_get_paypal_redirect(), $remote_post_vars );

		if ( ! is_wp_error( $request ) ) {

			$body = wp_remote_retrieve_body( $request );

			// parse the data
			$lines = explode( "\n", trim( $body ) );
			$data  = array();
			if ( strcmp( $lines[0], 'SUCCESS' ) == 0 ) {

				for ( $i = 1; $i < count( $lines ); $i++ ) {
					$parsed_line                          = explode( '=', $lines[ $i ], 2 );
					$data[ urldecode( $parsed_line[0] ) ] = urldecode( $parsed_line[1] );
				}

				if ( isset( $data['mc_gross'] ) ) {

					$total = $data['mc_gross'];
				} elseif ( isset( $data['payment_gross'] ) ) {

					$total = $data['payment_gross'];
				} elseif ( isset( $_REQUEST['amt'] ) ) {

					$total = cl_sanitization( $_REQUEST['amt'] );
				} else {

					$total = null;
				}

				if ( is_null( $total ) ) {

					// cl_debug_log('Attempt to verify PayPal payment with PDT failed due to payment total missing');
					$payment->add_note( __( 'Payment could not be verified while validating PayPal PDT. Missing payment total fields.', 'essential-wp-real-estate' ) );
					$payment->status = 'pending';
				} elseif ( (float) $total < (float) $payment->total ) {

					/**
					 * Here we account for payments that are less than the expected results only. There are times that
					 * PayPal will sometimes round and have $0.01 more than the amount. The goal here is to protect store owners
					 * from getting paid less than expected.
					 */
					// cl_debug_log('Attempt to verify PayPal payment with PDT failed due to payment total discrepancy');
					$payment->add_note( sprintf( __( 'Payment failed while validating PayPal PDT. Amount expected: %1$f. Amount Received: %2$f', 'essential-wp-real-estate' ), $payment->total, $data['payment_gross'] ) );
					$payment->status = 'failed';
				} else {

					// Verify the status
					switch ( strtolower( $data['payment_status'] ) ) {

						case 'completed':
							$payment->status = 'publish';
							break;

						case 'failed':
							$payment->status = 'failed';
							break;

						default:
							$payment->status = 'pending';
							break;
					}
				}

				$payment->transaction_id = cl_sanitization( $_GET['tx'] );
				$payment->save();
			} elseif ( strcmp( $lines[0], 'FAIL' ) == 0 ) {

				echo 'fail';

				// cl_debug_log('Attempt to verify PayPal payment with PDT failed due to PDT failure response: ' . print_r($body, true));
				$payment->add_note( __( 'Payment failed while validating PayPal PDT.', 'essential-wp-real-estate' ) );
				$payment->status = 'failed';
				$payment->save();
			} else {
				echo 'pending';
				// cl_debug_log('Attempt to verify PayPal payment with PDT met with an unexpected result: ' . print_r($body, true));
				$payment->add_note( __( 'PayPal PDT encountered an unexpected result, payment set to pending', 'essential-wp-real-estate' ) );
				$payment->status = 'pending';
				$payment->save();
			}
		} else {
			echo 'error';

		}
	}
}
