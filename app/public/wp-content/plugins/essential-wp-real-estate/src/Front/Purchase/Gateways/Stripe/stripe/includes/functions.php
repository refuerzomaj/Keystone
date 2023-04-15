<?php
use Essential\Restate\Common\Customer\Customer;

/**
 * Functions
 *
 * @package CL_Stripe
 * @since unknown
 */

use Essential\Restate\Front\Purchase\Payments\Clpayment;

/**
 * Returns the one true instance of CL_Stripe
 *
 * @since 2.6
 *
 *                          listings is not active.
 */
function cl_stripe() {
	if ( ! function_exists( 'CCL' ) ) {
		return;
	}

	return CL_Stripe::instance();
}

/**
 * Determines if the current execution of Stripe is being loaded from the
 * "Pro" version of the gateway.
 *
 * (Currently) when Stripe is packaged with core the bootstrap file is
 * renamed to `cls_stripe_bootstrap_core()` to prevent collision.
 *
 * @since 2.8.1
 *
 * @return bool True if the "Pro" version of the gateway is loaded.
 */
function cls_is_pro() {
	 return function_exists( 'cl_stripe_bootstrap' );
}

/**
 * Determines if the Stripe gateway is active.
 *
 * This checks both application requirements being met as
 * well as the gateway being enabled via Payment Gateways settings.
 *
 * @since 2.8.1
 *
 * @return bool
 */
function cls_is_gateway_active() {
	if ( false === cls_has_met_requirements() ) {
		return false;
	}

	if ( false === WPERECCP()->front->gateways->cl_is_gateway_active( 'stripe' ) ) {
		return false;
	}

	return true;
}

/**
 * Determines of the application requirements have been met.
 *
 * @since 2.8.1
 *
 * @param false|string $requirement Specific requirement to check for.
 *                                  False ensures all requirements are met.
 *                                  Default false.
 * @return bool True if the requirement(s) is met.
 */
function cls_has_met_requirements( $requirement = false ) {
	$requirements = array(
		'php' => ( version_compare( PHP_VERSION, '5.6.0', '>' )
		),
	);

	if ( false === $requirement ) {
		return false === in_array( false, $requirements, true );
	} else {
		return isset( $requirements[ $requirement ] )
			? $requirements[ $requirement ]
			: true;
	}
}

/**
 * Allows preconfigured Stripe API requests to be made.
 *
 * @since 2.7.0
 *
 * @throws \CL_Stripe_Utils_Exceptions_Stripe_Object_Not_Found When attempting to call an object or method that is not available.
 * @throws \Stripe\Exception
 *
 * @param string $object Name of the Stripe object to request.
 * @param string $method Name of the API operation to perform during the request.
 * @param mixed  ...$args Additional arguments to pass to the request.
 * @return \Stripe\StripeObject
 */
function cls_api_request( $object, $method, $args = null ) {
	$api = new CL_Stripe_API();

	return call_user_func_array( array( $api, 'request' ), func_get_args() );
}

/**
 * Converts a truthy value (e.g. '1', 'yes') to a bool.
 *
 * @since 2.8.0
 *
 * @param mixed $truthy_value Truthy value.
 * @return bool
 */
function cls_truthy_to_bool( $truthy_value ) {
	$truthy = array(
		'yes',
		1,
		'1',
		'true',
	);

	return is_bool( $truthy_value )
		? $truthy_value
		: in_array( strtolower( $truthy_value ), $truthy, true );
}

/**
 * Retrieve the exsting cards setting.
 *
 * @return bool
 */
function cl_stripe_existing_cards_enabled() {
	$use_existing_cards = cl_admin_get_option( 'stripe_use_existing_cards', false );
	return ! empty( $use_existing_cards );
}

/**
 * Given a user ID, retrieve existing cards within stripe.
 *
 * @since 2.6
 * @param int $user_id
 *
 * @return array
 */
function cl_stripe_get_existing_cards( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return array();
	}

	$enabled = cl_stripe_existing_cards_enabled();

	if ( ! $enabled ) {
		return array();
	}

	static $existing_cards;

	if ( ! is_null( $existing_cards ) && array_key_exists( $user_id, $existing_cards ) ) {
		return $existing_cards[ $user_id ];
	}

	// Check if the user has existing cards
	$customer_cards     = array();
	$stripe_customer_id = cls_get_stripe_customer_id( $user_id );

	if ( ! empty( $stripe_customer_id ) ) {
		try {
			$stripe_customer = cls_api_request( 'Customer', 'retrieve', $stripe_customer_id );

			if ( isset( $stripe_customer->deleted ) && $stripe_customer->deleted ) {
				return $customer_cards;
			}

			$payment_methods = cls_api_request(
				'PaymentMethod',
				'all',
				array(
					'type'     => 'card',
					'customer' => $stripe_customer->id,
					'limit'    => 100,
				)
			);

			$cards = cls_api_request(
				'Customer',
				'allSources',
				$stripe_customer->id,
				array(
					'limit' => 100,
				)
			);

			$sources = array_merge( $payment_methods->data, $cards->data );

			foreach ( $sources as $source ) {
				if ( ! in_array( $source->object, array( 'payment_method', 'card' ), true ) ) {
					continue;
				}

				$source_data     = new stdClass();
				$source_data->id = $source->id;

				switch ( $source->object ) {
					case 'payment_method':
						$source_data->brand           = ucwords( $source->card->brand );
						$source_data->last4           = $source->card->last4;
						$source_data->exp_month       = $source->card->exp_month;
						$source_data->exp_year        = $source->card->exp_year;
						$source_data->fingerprint     = $source->card->fingerprint;
						$source_data->address_line1   = $source->billing_details->address->line1;
						$source_data->address_line2   = $source->billing_details->address->line2;
						$source_data->address_city    = $source->billing_details->address->city;
						$source_data->address_zip     = $source->billing_details->address->postal_code;
						$source_data->address_state   = $source->billing_details->address->state;
						$source_data->address_country = $source->billing_details->address->country;

						$customer_cards[ $source->id ]['default'] = $source->id === $stripe_customer->invoice_settings->default_payment_method;
						break;
					case 'card':
						$source_data->brand           = $source->brand;
						$source_data->last4           = $source->last4;
						$source_data->exp_month       = $source->exp_month;
						$source_data->exp_year        = $source->exp_year;
						$source_data->fingerprint     = $source->fingerprint;
						$source_data->address_line1   = $source->address_line1;
						$source_data->address_line2   = $source->address_line2;
						$source_data->address_city    = $source->address_city;
						$source_data->address_zip     = $source->address_zip;
						$source_data->address_state   = $source->address_state;
						$source_data->address_country = $source->address_country;
						break;
				}

				$customer_cards[ $source->id ]['source'] = $source_data;
			}
		} catch ( Exception $e ) {
			return $customer_cards;
		}
	}

	// Show only the latest version of card for duplicates.
	$fingerprints = array();
	foreach ( $customer_cards as $key => $customer_card ) {
		$fingerprint = $customer_card['source']->fingerprint;
		if ( ! in_array( $fingerprint, $fingerprints ) ) {
			$fingerprints[] = $fingerprint;
		} else {
			unset( $customer_cards[ $key ] );
		}
	}

	// Put default card first.
	usort(
		$customer_cards,
		function ( $a, $b ) {
			return $a['default'] ? 1 : -1;
		}
	);

	$existing_cards[ $user_id ] = $customer_cards;

	return $existing_cards[ $user_id ];
}

/**
 * Look up the stripe customer id in user meta, and look to recurring if not found yet
 *
 * @since  2.4.4
 * @since  2.6               Added lazy load for moving to customer meta and ability to look up by customer ID.
 * @param  int  $id_or_email The user ID, customer ID or email to look up.
 * @param  bool $by_user_id  If the lookup is by user ID or not.
 *
 * @return string       Stripe customer ID
 */
function cls_get_stripe_customer_id( $id_or_email, $by_user_id = true ) {
	$stripe_customer_id = '';
	$meta_key           = cl_stripe_get_customer_key();

	if ( is_email( $id_or_email ) ) {
		$by_user_id = false;
	}

	$customer = new Customer( $id_or_email, $by_user_id );
	if ( $customer->id > 0 ) {
		$stripe_customer_id = $customer->get_meta( $meta_key );
	}

	if ( empty( $stripe_customer_id ) ) {
		$user_id = 0;
		if ( ! empty( $customer->user_id ) ) {
			$user_id = $customer->user_id;
		} elseif ( $by_user_id && is_numeric( $id_or_email ) ) {
			$user_id = $id_or_email;
		} elseif ( is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}

		if ( ! isset( $user ) ) {
			$user = get_user_by( 'id', $user_id );
		}

		if ( $user ) {

			$customer = new Customer( $user->user_email );

			if ( ! empty( $user_id ) ) {
				$stripe_customer_id = get_user_meta( $user_id, $meta_key, true );

				// Lazy load migrating data over to the customer meta from Stripe issue #113
				$customer->update_meta( $meta_key, $stripe_customer_id );
			}
		}
	}

	if ( empty( $stripe_customer_id ) && class_exists( 'CL_Recurring_Subscriber' ) ) {
		$subscriber = new CL_Recurring_Subscriber( $id_or_email, $by_user_id );
		if ( $subscriber->id > 0 ) {
			$verified = false;
			if ( ( $by_user_id && $id_or_email == $subscriber->user_id ) ) {
				// If the user ID given, matches that of the subscriber
				$verified = true;
			} else {
				// If the email used is the same as the primary email
				if ( $subscriber->email == $id_or_email ) {
					$verified = true;
				}
				if ( property_exists( $subscriber, 'emails' ) && in_array( $id_or_email, $subscriber->emails ) ) {
					$verified = true;
				}
			}
			if ( $verified ) {
				$stripe_customer_id = $subscriber->get_recurring_customer_id( 'stripe' );
			}
		}
		if ( ! empty( $stripe_customer_id ) ) {
			$customer->update_meta( $meta_key, $stripe_customer_id );
		}
	}

	return $stripe_customer_id;
}

/**
 * Get the meta key for storing Stripe customer IDs in
 *
 * @access      public
 * @since       1.6.7
 * @return      string
 */
function cl_stripe_get_customer_key() {
	$key = '_cl_stripe_customer_id';
	if ( cl_is_test_mode() ) {
		$key .= '_test';
	}
	return $key;
}

/**
 * Determines if the provided currency is a zero-decimal currency
 *
 * @access       public
 * @since        1.8.4
 * @param string $currency Three-letter ISO currency code or an empty string. If empty, the shop's currency is used.
 * @since  2.8.8 $currency parameter added
 * @return       bool
 */
function cls_is_zero_decimal_currency( $currency = '' ) {
	if ( empty( $currency ) ) {
		$currency = WPERECCP()->common->options->cl_get_currency();
	}

	$currency = strtolower( $currency );

	$currencies = array(
		'bif',
		'clp',
		'djf',
		'gnf',
		'jpy',
		'kmf',
		'krw',
		'mga',
		'pyg',
		'rwf',
		'ugx',
		'vnd',
		'vuv',
		'xaf',
		'xof',
		'xpf',
	);

	return in_array( $currency, $currencies, true );
}

/**
 * Retrieves a sanitized statement descriptor.
 *
 * @since 2.6.19
 *
 * @return string $statement_descriptor Sanitized statement descriptor.
 */
function cls_get_statement_descriptor() {
	$statement_descriptor = cl_admin_get_option( 'stripe_statement_descriptor', '' );
	$statement_descriptor = cls_sanitize_statement_descriptor( $statement_descriptor );

	return $statement_descriptor;
}

/**
 * Retrieves a list of unsupported characters for Stripe statement descriptors.
 *
 * @since 2.6.19
 *
 * @return array $unsupported_characters List of unsupported characters.
 */
function cls_get_statement_descriptor_unsupported_characters() {
	$unsupported_characters = array(
		'<',
		'>',
		'"',
		'\'',
		'\\',
		'*',
	);

	/**
	 * Filters the list of unsupported characters for Stripe statement descriptors.
	 *
	 * @since 2.6.19
	 *
	 * @param array $unsupported_characters List of unsupported characters.
	 */
	$unsupported_characters = apply_filters( 'cls_get_statement_descriptor_unsupported_characters', $unsupported_characters );

	return $unsupported_characters;
}

/**
 * Sanitizes a string to be used for a statement descriptor.
 *
 * @since 2.6.19
 *
 * @link https://stripe.com/docs/connect/statement-descriptors#requirements
 *
 * @param string $statement_descriptor Statement descriptor to sanitize.
 * @return string $statement_descriptor Sanitized statement descriptor.
 */
function cls_sanitize_statement_descriptor( $statement_descriptor ) {
	$unsupported_characters = cls_get_statement_descriptor_unsupported_characters();

	$statement_descriptor = trim( str_replace( $unsupported_characters, '', $statement_descriptor ) );
	$statement_descriptor = substr( $statement_descriptor, 0, 22 );

	return $statement_descriptor;
}

/**
 * Retrieves a given registry instance by name.
 *
 * @since 2.6.19
 *
 * @param string $name Registry name.
 * @return null|CL_Stripe_Registry Null if the registry doesn't exist, otherwise the object instance.
 */
function cls_get_registry( $name ) {
	switch ( $name ) {
		case 'admin-notices':
			$registry = CL_Stripe_Admin_Notices_Registry::instance();
			break;
		default:
			$registry = null;
			break;
	}

	return $registry;
}

/**
 * Attempts to verify a nonce from various payment forms when the origin
 * of the action isn't explicitly known.
 *
 * This could be coming from the Checkout, Payment Authorization,
 * or Update Payment Method (Recurring) form.
 *
 * @since 2.8.0
 *
 * @return bool
 */
function cls_verify_payment_form_nonce() {
	// Checkout.
	$nonce = isset( $_POST['cl-process-checkout-nonce'] )
		? cl_sanitization( $_POST['cl-process-checkout-nonce'] )
		: '';

	if ( ! empty( $nonce ) ) {
		return wp_verify_nonce( $nonce, 'cl-process-checkout' );
	}

	// Update Payment Method.
	$nonce = isset( $_POST['cl_recurring_update_nonce'] )
		? cl_sanitization( $_POST['cl_recurring_update_nonce'] )
		: '';

	if ( ! empty( $nonce ) ) {
		return wp_verify_nonce( $nonce, 'update-payment' );
	}

	return false;
}

/**
 * Routes user to correct support documentation, depending on whether they are using Standard or Pro version of Stripe
 *
 * @since 2.8.1
 * @param string $type The type of Stripe documentation.
 * @return string
 */
function cls_documentation_route( $type ) {
	$base_url = 'https://docs.smartdatasoft.com/standard';

	/**
	 *
	 * @since 2.8.1
	 */
	$base_url = apply_filters( 'cls_documentation_route_base', $base_url );

	return trailingslashit( $base_url ) . $type;
}

/**
 * Determines if the current Stripe account's country supports application fees.
 *
 * @since 2.8.7
 *
 * @return bool True if the Stripe account country (or core "Base Country" setting)
 *              can use the `application_fee_amount` parameter in API requests.
 *              True if no country information can be found which will still be
 *              validated by Stripe when a request is made.
 */
function cls_stripe_connect_account_country_supports_application_fees() {
	$cl_country = cl_admin_get_option( 'base_country', '' );

	$account_country = cl_admin_get_option(
		'stripe_connect_account_country',
		$cl_country
	);

	// If we have no country to compare against try to add an application fee.
	// If the Stripe account is actually one of the blocked countries an API
	// error will be reflected in the Checkout.
	if ( empty( $account_country ) ) {
		return true;
	}

	$account_country = strtolower( $account_country );

	$blocked_countries = array(
		'br',
	);

	return ! in_array( $account_country, $blocked_countries, true );
}

/**
 * Refunds a charge made via Stripe.
 *
 * @since 2.8.7
 *
 * @param Order|null $refund_object              Optional. The refund object associated with this
 *                                               charge refund. If provided, then the refund amount
 *                                               is used as the charge refund amount (for partial refunds), and
 *
 * @throws \CL_Stripe_Utils_Exceptions_Stripe_Object_Not_Found When attempting to call an object or method that is not available.
 * @throws \Exception
 */
function cl_refund_stripe_purchase( $order_id_or_object, $refund_object = null ) {
	$order_id = $order_id_or_object instanceof Order ? $order_id_or_object->id : $order_id_or_object;

	cl_debug_log(
		sprintf(
			'Processing Stripe refund for order #%d',
			$order_id
		)
	);

	if ( ! is_numeric( $order_id ) ) {
		throw new \Exception( __( 'Invalid order ID.', 'essential-wp-real-estate' ), 400 );
	}

	$charge_id = cl_get_payment_transaction_id( $order_id );

	if ( empty( $charge_id ) || $charge_id == $order_id ) {
		$notes = cl_get_payment_notes( $order_id );

		foreach ( $notes as $note ) {
			if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
				$charge_id = $match[1];
				break;
			}
		}
	}

	// Bail if no charge ID was found.
	if ( empty( $charge_id ) ) {
		cl_debug_log( sprintf( 'Exiting refund of order #%d. No Stripe charge found.', $order_id ) );

		return;
	}

	$args = array(
		'charge' => $charge_id,
	);

	if ( $refund_object instanceof Order && $order_id_or_object instanceof Order && abs( $refund_object->total ) !== abs( $order_id_or_object->total ) ) {
		$args['amount'] = abs( $refund_object->total );
		if ( ! cls_is_zero_decimal_currency() ) {
			$args['amount'] = round( $args['amount'] * 100, 0 );
		}

		cl_debug_log(
			sprintf(
				'Processing partial Stripe refund for order #%d. Refund amount: %s; Amount sent to Stripe: %s',
				$order_id_or_object->id,
				WPERECCP()->common->formatting->cl_currency_filter( $refund_object->total, $refund_object->currency ),
				$args['amount']
			)
		);
	} else {
		cl_debug_log( sprintf( 'Processing full Stripe refund for order #%d.', $order_id ) );
	}

	/**
	 * Filters the refund arguments sent to Stripe.
	 *
	 * @link https://stripe.com/docs/api/refunds/create
	 *
	 * @param array $args
	 */
	$args = apply_filters( 'cls_create_refund_args', $args );

	/**
	 * Filters the secondary refund arguments.
	 *
	 * @param array $sec_args
	 */
	$sec_args = apply_filters( 'cls_create_refund_secondary_args', array() );

	$refund = cls_api_request( 'Refund', 'create', $args, $sec_args );

	$amount_refunded = (float) $refund->amount;
	if ( ! cls_is_zero_decimal_currency() ) {
		$amount_refunded = round( $amount_refunded / 100, cl_currency_decimal_filter( 2, strtoupper( $refund->currency ) ) );
	}

	$order_note = sprintf(
		/* translators: %1$s the amount refunded; %2$s Stripe Refund ID */
		__( '%1$s refunded in Stripe. Refund ID %2$s', 'essential-wp-real-estate' ),
		WPERECCP()->common->formatting->cl_currency_filter( $amount_refunded, strtoupper( $refund->currency ) ),
		$refund->id
	);

	cl_insert_payment_note( $order_id, $order_note );

	if ( $refund_object instanceof Order && function_exists( 'cl_add_order_transaction' ) ) {
		cl_add_order_transaction(
			array(
				'object_id'      => $refund_object->id,
				'object_type'    => 'order',
				'transaction_id' => cl_sanitization( $refund->id ),
				'gateway'        => 'stripe',
				'status'         => 'complete',
				'total'          => cl_negate_amount( $amount_refunded ),
			)
		);

		cl_add_note(
			array(
				'object_id'   => $refund_object->id,
				'object_type' => 'order',
				'user_id'     => is_admin() ? get_current_user_id() : 0,
				'content'     => $order_note,
			)
		);
	}

	/**
	 * Triggers after a refund has been processed.
	 *
	 * @param int $order_id ID of the order that was refunded.
	 */
	do_action( 'cls_payment_refunded', $order_id );
}

/**
 * Checks if Stripe preapproval is enabled. Pro must be active.
 *
 * @since 2.8.9
 * @return bool
 */
function cls_is_preapprove_enabled() {
	return cls_is_pro() && cl_admin_get_option( 'stripe_preapprove_only' );
}
