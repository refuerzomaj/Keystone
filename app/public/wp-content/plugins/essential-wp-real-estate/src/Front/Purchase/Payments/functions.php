<?php
use  Essential\Restate\Front\Purchase\Payments\Clpayment;
use  Essential\Restate\Front\CL_Logging\CL_Logging;
use Essential\Restate\Front\Models\Listingsaction;
use Essential\Restate\Front\Purchase\Payments\Clpaymentsquery;
use Essential\Restate\Common\Customer\Customer;
use  Essential\Restate\Common\Formatting\Formatting;
use Essential\Restate\Front\Session\Session;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves an instance of Clpayment for a specified ID.
 *
 * @since 2.7
 *
 * @param mixed int|Clpayment|WP_Post $payment Payment ID, Clpayment object or WP_Post object.
 * @param bool                        $by_txn  Is the ID supplied as the first parameter
 * @return Clpayment|false false|object Clpayment if a valid payment ID, false otherwise.
 */
function cl_get_payment( $payment_or_txn_id = null, $by_txn = false ) {
	global $wpdb;

	if ( $payment_or_txn_id instanceof WP_Post || $payment_or_txn_id instanceof Clpayment ) {
		$payment_id = $payment_or_txn_id->ID;
	} elseif ( $by_txn ) {
		if ( empty( $payment_or_txn_id ) ) {
			return false;
		}

		$query      = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_cl_payment_transaction_id' AND meta_value = '%s'", $payment_or_txn_id );
		$payment_id = $wpdb->get_var( $query );

		if ( empty( $payment_id ) ) {
			return false;
		}
	} else {
		$payment_id = $payment_or_txn_id;
	}

	if ( empty( $payment_id ) ) {
		return false;
	}

	$cache_key = md5( 'cl_payment' . $payment_id );
	$payment   = wp_cache_get( $cache_key, 'payments' );

	if ( false === $payment ) {
		$payment = new Clpayment( $payment_id );
		if ( empty( $payment->ID ) || ( ! $by_txn && (int) $payment->ID !== (int) $payment_id ) ) {
			return false;
		} else {
			wp_cache_set( $cache_key, $payment, 'payments' );
		}
	}

	return $payment;
}

/**
 * Get Payments
 *
 * Retrieve payments from the database.
 *
 * Since 1.2, this function takes an array of arguments, instead of individual
 * parameters. All of the original parameters remain, but can be passed in any
 * order via the array.
 *
 * $offset = 0, $number = 20, $mode = 'live', $orderby = 'ID', $order = 'DESC',
 * $user = null, $status = 'any', $meta_key = null
 *
 * @since 1.0
 * @param array $args Arguments passed to get payments
 * @return Clpayment[] $payments Payments retrieved from the database
 */
function cl_get_payments( $args = array() ) {

	// Fallback to post objects to ensure backwards compatibility
	if ( ! isset( $args['output'] ) ) {
		$args['output'] = 'posts';
	}

	$args     = apply_filters( 'cl_get_payments_args', $args );
	$payments = new Clpaymentsquery( $args );
	return $payments->get_payments();
}

/**
 * Retrieve payment by a given field
 *
 * @since       2.0
 * @param       string $field The field to retrieve the payment with
 * @param       mixed  $value The value for $field
 * @return      mixed
 */
function cl_get_payment_by( $field = '', $value = '' ) {

	$payment = false;

	if ( ! empty( $field ) && ! empty( $value ) ) {

		switch ( strtolower( $field ) ) {

			case 'id':
				$payment = cl_get_payment( $value );

				if ( ! $payment->ID > 0 ) {
					$payment = false;
				}

				break;

			case 'key':
			case 'payment_number':
				global $wpdb;

				$meta_key   = ( 'key' == $field ) ? '_cl_payment_purchase_key' : '_cl_payment_number';
				$payment_id = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT post_ID FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value=%s",
						$meta_key,
						$value
					)
				);

				if ( $payment_id ) {

					$payment = cl_get_payment( $payment_id );

					if ( ! $payment->ID > 0 ) {
						$payment = false;
					}
				}

				break;
		}
	}

	return $payment;
}

/**
 * Insert Payment
 *
 * @since 1.0
 * @param array $payment_data Payment data to process
 * @return int|bool Payment ID if payment is inserted, false otherwise
 */
function cl_insert_payment( $payment_data = array() ) {

	if ( empty( $payment_data ) ) {
		return false;
	}

	$resume_payment   = false;
	$existing_payment = WPERECCP()->front->session->get( 'cl_resume_payment' );
	cl_debug_log( 'cl_resume_payment' );
	if ( ! empty( $existing_payment ) ) {
		$payment        = new Clpayment( $existing_payment );
		$resume_payment = $payment->is_recoverable();
	}

	if ( $resume_payment ) {
		$payment->date = date( 'Y-m-d G:i:s', current_time( 'timestamp' ) );

		$payment->add_note( __( 'Payment recovery processed', 'essential-wp-real-estate' ) );

		// Since things could have been added/removed since we first crated this...rebuild the cart details.
		foreach ( $payment->fees as $fee_index => $fee ) {
			$payment->remove_fee_by( 'index', $fee_index, true );
		}

		foreach ( $payment->listing as $cart_index => $listing ) {
			$item_args = array(
				'quantity'   => isset( $listing['quantity'] ) ? $listing['quantity'] : 1,
				'cart_index' => $cart_index,
			);
			$payment->remove_listing( $listing['id'], $item_args );
		}

		if ( strtolower( $payment->email ) !== strtolower( $payment_data['user_info']['email'] ) ) {

			// Remove the payment from the previous customer.
			$previous_customer = new Customer( $payment->customer_id );
			$previous_customer->remove_payment( $payment->ID, false );

			// Redefine the email frst and last names.
			$payment->email      = $payment_data['user_info']['email'];
			$payment->first_name = $payment_data['user_info']['first_name'];
			$payment->last_name  = $payment_data['user_info']['last_name'];
		}

		// Remove any remainders of possible fees from items.
		$payment->save();
		cl_debug_log( 'save' );
	} else {
		cl_debug_log( 'new' );
		$payment = new Clpayment();
	}

	if ( is_array( $payment_data['cart_details'] ) && ! empty( $payment_data['cart_details'] ) ) {

		foreach ( $payment_data['cart_details'] as $item ) {

			$args = array(
				'quantity'   => $item['quantity'],
				'price_id'   => isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null,
				'tax'        => $item['tax'],
				'item_price' => isset( $item['item_price'] ) ? $item['item_price'] : $item['price'],
				'fees'       => isset( $item['fees'] ) ? $item['fees'] : array(),
				'discount'   => isset( $item['discount'] ) ? $item['discount'] : 0,
			);

			$options = isset( $item['item_number']['options'] ) ? $item['item_number']['options'] : array();

			$payment->add_listing( $item['id'], $args, $options );
			cl_debug_log( 'cart_details' );
		}
	}
	cl_debug_log( 'increase_tax' );
	$payment->increase_tax( cl_get_cart_fee_tax() );

	$gateway = ! empty( $payment_data['gateway'] ) ? $payment_data['gateway'] : '';
	$gateway = empty( $gateway ) && isset( $_POST['cl-gateway'] ) ? cl_sanitization( $_POST['cl-gateway'] ) : $gateway;

	$country = ! empty( $payment_data['user_info']['address']['country'] ) ? $payment_data['user_info']['address']['country'] : false;
	$state   = ! empty( $payment_data['user_info']['address']['state'] ) ? $payment_data['user_info']['address']['state'] : false;
	$zip     = ! empty( $payment_data['user_info']['address']['zip'] ) ? $payment_data['user_info']['address']['zip'] : false;

	$payment->status         = ! empty( $payment_data['status'] ) ? $payment_data['status'] : 'pending';
	$payment->currency       = ! empty( $payment_data['currency'] ) ? $payment_data['currency'] : WPERECCP()->common->options->cl_get_currency();
	$payment->user_info      = $payment_data['user_info'];
	$payment->gateway        = $gateway;
	$payment->user_id        = $payment_data['user_info']['id'];
	$payment->first_name     = $payment_data['user_info']['first_name'];
	$payment->last_name      = $payment_data['user_info']['last_name'];
	$payment->email          = $payment_data['user_info']['email'];
	$payment->ip             = cl_get_ip();
	$payment->key            = $payment_data['purchase_key'];
	$payment->mode           = cl_is_test_mode() ? 'test' : 'live';
	$payment->parent_payment = ! empty( $payment_data['parent'] ) ? absint( $payment_data['parent'] ) : '';
	$payment->discounts      = ! empty( $payment_data['user_info']['discount'] ) ? $payment_data['user_info']['discount'] : array();
	$payment->tax_rate       = WPERECCP()->front->cart->cl_get_cart_tax_rate( $country, $state, $zip );

	cl_debug_log( 'payment' );

	if ( isset( $payment_data['post_date'] ) ) {
		$payment->date = $payment_data['post_date'];
	}

	// Clear the user's purchased cache
	delete_transient( 'cl_user_' . $payment_data['user_info']['id'] . '_purchases' );

	$payment->save();
	cl_debug_log( 'paymentsave' );

	if ( cl_admin_get_option( 'show_agree_to_terms', false ) && ! empty( $_POST['cl_agree_to_terms'] ) ) {
		$payment_data['agree_to_terms_time'] = current_time( 'timestamp' );
	}

	if ( cl_admin_get_option( 'show_agree_to_privacy_policy', false ) && ! empty( $_POST['cl_agree_to_privacy_policy'] ) ) {
		$payment_data['agree_to_privacy_time'] = current_time( 'timestamp' );
	}

	do_action( 'cl_insert_payment', $payment->ID, $payment_data );

	if ( ! empty( $payment->ID ) ) {
		return $payment->ID;
	}
	cl_debug_log( 'cl_insert_payment' );
	// Return false if no payment was inserted
	return false;
}

/**
 * Updates a payment status.
 *
 * @since  1.0
 * @param  int    $payment_id Payment ID
 * @param  string $new_status New Payment Status (default: publish)
 * @return bool               If the payment was successfully updated
 */
function cl_update_payment_status( $payment_id = 0, $new_status = 'publish' ) {

	$updated = false;
	$payment = new Clpayment( $payment_id );

	if ( $payment && $payment->ID > 0 ) {

		$payment->status = $new_status;
		$updated         = $payment->save();
	}

	return $updated;
}

/**
 * Deletes a Purchase
 *
 * @since 1.0
 * @global $cl_logs
 *
 * @uses CL_Logging::delete_logs()
 *
 * @param int  $payment_id Payment ID (default: 0)
 * @param bool $update_customer If we should update the customer stats (default:true)
 * @param bool $delete_listing_logs If we should remove all file listing logs associated with the payment (default:false)
 *
 * @return void
 */
function cl_delete_purchase( $payment_id = 0, $update_customer = true, $delete_listing_logs = false ) {
	global $cl_logs;

	$payment = new Clpayment( $payment_id );

	// Update sale counts and earnings for all purchased products
	cl_undo_purchase( false, $payment_id );

	$amount      = cl_get_payment_amount( $payment_id );
	$status      = $payment->post_status;
	$customer_id = cl_get_payment_customer_id( $payment_id );

	$customer = new Customer( $customer_id );

	if ( $status == 'revoked' || $status == 'publish' ) {
		// Only decrease earnings if they haven't already been decreased (or were never increased for this payment)
		cl_decrease_total_earnings( $amount );
		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'cl_earnings_this_monththis_month' ) );

		if ( $customer->id && $update_customer ) {

			// Decrement the stats for the customer
			$customer->decrease_purchase_count();
			$customer->decrease_value( $amount );
		}
	}

	do_action( 'cl_payment_delete', $payment_id );

	if ( $customer->id && $update_customer ) {

		// Remove the payment ID from the customer
		$customer->remove_payment( $payment_id );
	}

	// Remove the payment
	wp_delete_post( $payment_id, true );

	// Remove related sale log entries
	$cl_logs->delete_logs(
		null,
		'sale',
		array(
			array(
				'key'   => '_cl_log_payment_id',
				'value' => $payment_id,
			),
		)
	);

	if ( $delete_listing_logs ) {
		$cl_logs->delete_logs(
			null,
			'file_listing',
			array(
				array(
					'key'   => '_cl_log_payment_id',
					'value' => $payment_id,
				),
			)
		);
	}

	do_action( 'cl_payment_deleted', $payment_id );
}

/**
 * Undos a purchase, including the decrease of sale and earning stats. Used for
 * when refunding or deleting a purchase
 *
 * @since 1.0.8.1
 * @param int $listing_id listing (Post) ID. This should be passed as `false`.
 * @param int $payment_id Payment ID
 * @return void
 */
function cl_undo_purchase( $listing_id, $payment_id ) {

	/**
	 * In 2.5.7, a bug was found that $listing_id was an incorrect usage. Passing it in
	 * now does nothing, but we're holding it in place for legacy support of the argument order.
	 */

	if ( ! empty( $listing_id ) ) {
		$listing_id = false;
		_cl_deprected_argument( 'listing_id', 'cl_undo_purchase', '2.5.7' );
	}

	$payment = new Clpayment( $payment_id );

	$cart_details = $payment->cart_details;
	$user_info    = $payment->user_info;

	if ( is_array( $cart_details ) ) {

		foreach ( $cart_details as $item ) {

			// get the item's price
			$amount = isset( $item['price'] ) ? $item['price'] : false;
			$listingsaction          = new Listingsaction();
			// Decrease earnings/sales and fire action once per quantity number
			for ( $i = 0; $i < $item['quantity']; $i++ ) {

				// variable priced listings
				if ( false === $amount && cl_has_variable_prices( $item['id'] ) ) {
					$price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
					$amount   = ! isset( $item['price'] ) && 0 !== $item['price'] ? cl_get_price_option_amount( $item['id'], $price_id ) : $item['price'];
				}

				if ( ! $amount ) {
					// This function is only used on payments with near 1.0 cart data structure
					$amount = $listingsaction->cl_get_listing_final_price( $item['id'], $user_info, $amount );
				}
			}

			if ( ! empty( $item['fees'] ) ) {
				foreach ( $item['fees'] as $fee ) {
					// Only let negative fees affect the earnings
					if ( $fee['amount'] > 0 ) {
						continue;
					}

					$amount += $fee['amount'];
				}
			}
			
			$maybe_decrease_earnings = apply_filters( 'cl_decrease_earnings_on_undo', true, $payment, $item['id'] );
			if ( true === $maybe_decrease_earnings ) {
				// decrease earnings
				$listingsaction->cl_decrease_earnings( $item['id'], $amount );
			}

			$maybe_decrease_sales = apply_filters( 'cl_decrease_sales_on_undo', true, $payment, $item['id'] );
			if ( true === $maybe_decrease_sales ) {
				// decrease purchase count
				$listingsaction->cl_decrease_purchase_count( $item['id'], $item['quantity'] );
			}
		}
	}
}


/**
 * Count Payments
 *
 * Returns the total number of payments recorded.
 *
 * @since 1.0
 * @param array $args List of arguments to base the payments count on
 * @return array $count Number of payments sorted by payment status
 */
function cl_count_payments( $args = array() ) {

	global $wpdb;

	$defaults = array(
		'user'       => null,
		'customer'   => null,
		's'          => null,
		'start-date' => null,
		'end-date'   => null,
		'listing'    => null,
		'gateway'    => null,
	);

	$args = wp_parse_args( $args, $defaults );

	$select = 'SELECT p.post_status,count( * ) AS num_posts';
	$join   = '';
	$where  = "WHERE p.post_type = 'cl_payment'";

	// Count payments for a specific user
	if ( ! empty( $args['user'] ) ) {

		if ( is_email( $args['user'] ) ) {
			$field = 'email';
		} elseif ( is_numeric( $args['user'] ) ) {
			$field = 'id';
		} else {
			$field = '';
		}

		$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";

		if ( ! empty( $field ) ) {
			$where .= "
				AND m.meta_key = '_cl_payment_user_{$field}'
				AND m.meta_value = '{$args['user']}'";
		}
	} elseif ( ! empty( $args['customer'] ) ) {

		$join   = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
		$where .= "
			AND m.meta_key = '_cl_payment_customer_id'
			AND m.meta_value = '{$args['customer']}'";

		// Count payments for a search
	} elseif ( ! empty( $args['s'] ) ) {

		if ( is_email( $args['s'] ) || strlen( $args['s'] ) == 32 ) {

			if ( is_email( $args['s'] ) ) {
				$field = '_cl_payment_user_email';
			} else {
				$field = '_cl_payment_purchase_key';
			}

			$join   = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= $wpdb->prepare(
				'
				AND m.meta_key = %s
				AND m.meta_value = %s',
				$field,
				$args['s']
			);
		} elseif ( '#' == substr( $args['s'], 0, 1 ) ) {

			$search = str_replace( '#:', '', $args['s'] );
			$search = str_replace( '#', '', $search );

			$select = 'SELECT p2.post_status,count( * ) AS num_posts ';
			$join   = "LEFT JOIN $wpdb->postmeta m ON m.meta_key = '_cl_log_payment_id' AND m.post_id = p.ID ";
			$join  .= "INNER JOIN $wpdb->posts p2 ON m.meta_value = p2.ID ";
			$where  = "WHERE p.post_type = 'cl_log' ";
			$where .= $wpdb->prepare( 'AND p.post_parent = %d ', $search );
		} elseif ( is_numeric( $args['s'] ) ) {

			$join   = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= $wpdb->prepare(
				"
				AND m.meta_key = '_cl_payment_user_id'
				AND m.meta_value = %d",
				$args['s']
			);
		} elseif ( 0 === strpos( $args['s'], 'discount:' ) ) {

			$search = str_replace( 'discount:', '', $args['s'] );
			$search = 'discount.*' . $search;

			$join   = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= $wpdb->prepare(
				"
				AND m.meta_key = '_cl_payment_meta'
				AND m.meta_value REGEXP %s",
				$search
			);
		} else {
			$search = $wpdb->esc_like( $args['s'] );
			$search = '%' . $search . '%';

			$where .= $wpdb->prepare( 'AND ((p.post_title LIKE %s) OR (p.post_content LIKE %s))', $search, $search );
		}
	}

	if ( ! empty( $args['listing'] ) && is_numeric( $args['listing'] ) ) {

		$where .= $wpdb->prepare( ' AND p.post_parent = %d', $args['listing'] );
	}

	// Limit payments count by gateway
	if ( ! empty( $args['gateway'] ) ) {

		$join  .= "LEFT JOIN $wpdb->postmeta g ON (p.ID = g.post_id)";
		$where .= $wpdb->prepare(
			"
			AND g.meta_key = '_cl_payment_gateway'
			AND g.meta_value = %s",
			$args['gateway']
		);
	}

	// Limit payments count by date
	if ( ! empty( $args['start-date'] ) && false !== strpos( $args['start-date'], '/' ) ) {

		$date = DateTime::createFromFormat( 'm/d/Y', $args['start-date'] );
		if ( $date instanceof DateTime ) {
			$where .= $wpdb->prepare( " AND p.post_date >= '%s'", $date->format( 'Y-m-d' ) );
		}

		// Fixes an issue with the payments list table counts when no end date is specified (partly with stats class)
		if ( empty( $args['end-date'] ) ) {
			$args['end-date'] = $args['start-date'];
		}
	}

	if ( ! empty( $args['end-date'] ) && false !== strpos( $args['end-date'], '/' ) ) {

		$date = DateTime::createFromFormat( 'm/d/Y', $args['end-date'] );
		if ( $date instanceof DateTime ) {
			$where .= $wpdb->prepare( " AND p.post_date < '%s'", $date->modify( '+1 day' )->format( 'Y-m-d' ) );
		}
	}

	$where = apply_filters( 'cl_count_payments_where', $where );
	$join  = apply_filters( 'cl_count_payments_join', $join );

	$query = "$select
		FROM $wpdb->posts p
		$join
		$where
		GROUP BY p.post_status
	";

	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts' );
	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A );

	$stats    = array();
	$statuses = get_post_stati();
	if ( isset( $statuses['private'] ) && empty( $args['s'] ) ) {
		unset( $statuses['private'] );
	}

	foreach ( $statuses as $state ) {
		$stats[ $state ] = 0;
	}

	foreach ( (array) $count as $row ) {

		if ( 'private' == $row['post_status'] && empty( $args['s'] ) ) {
			continue;
		}

		$stats[ $row['post_status'] ] = $row['num_posts'];
	}

	$stats = (object) $stats;
	wp_cache_set( $cache_key, $stats, 'counts' );

	return $stats;
}


/**
 * Check For Existing Payment
 *
 * @since 1.0
 * @param int $payment_id Payment ID
 * @return bool true if payment exists, false otherwise
 */
function cl_check_for_existing_payment( $payment_id ) {
	$exists  = false;
	$payment = new Clpayment( $payment_id );

	if ( $payment_id === $payment->ID && 'publish' === $payment->status ) {
		$exists = true;
	}

	return $exists;
}

/**
 * Get Payment Status
 *
 * @since 1.0
 *
 * @param mixed  WP_Post|Clpayment|Payment ID $payment Payment post object, Clpayment object, or payment/post ID
 * @param bool                                $return_label Whether to return the payment status or not
 *
 * @return bool|mixed if payment status exists, false otherwise
 */
function cl_get_payment_status( $payment, $return_label = false ) {

	if ( is_numeric( $payment ) ) {

		$payment = cl_get_payment( $payment );

		if ( empty( $payment ) ) {
			return false;
		}
	} elseif ( $payment instanceof WP_Post ) {
		$payment = cl_get_payment( $payment->ID );
	}

	if ( ! is_object( $payment ) || ! isset( $payment->status ) ) {
		return false;
	}

	if ( true === $return_label ) {
		$status = cl_get_payment_status_label( $payment->status );
	} else {
		$keys      = cl_get_payment_status_keys();
		$found_key = array_search( strtolower( $payment->status ), $keys );
		$status    = array_key_exists( $found_key, $keys ) ? $keys[ $found_key ] : false;
	}

	return ! empty( $status ) ? $status : false;
}

/**
 * Given a payment status string, return the label for that string.
 *
 * @since 2.9.2
 * @param string $status
 *
 * @return bool|mixed
 */
function cl_get_payment_status_label( $status = '' ) {
	$statuses = cl_get_payment_statuses();

	if ( ! is_array( $statuses ) || empty( $statuses ) ) {
		return false;
	}

	if ( array_key_exists( $status, $statuses ) ) {
		return $statuses[ $status ];
	}

	return false;
}

/**
 * Retrieves all available statuses for payments.
 *
 * @since 1.0.8.1
 * @return array $payment_status All the available payment statuses
 */
function cl_get_payment_statuses() {
	$payment_statuses = array(
		'pending'    => __( 'Pending', 'essential-wp-real-estate' ),
		'publish'    => __( 'Complete', 'essential-wp-real-estate' ),
		'refunded'   => __( 'Refunded', 'essential-wp-real-estate' ),
		'failed'     => __( 'Failed', 'essential-wp-real-estate' ),
		'abandoned'  => __( 'Abandoned', 'essential-wp-real-estate' ),
		'revoked'    => __( 'Revoked', 'essential-wp-real-estate' ),
		'processing' => __( 'Processing', 'essential-wp-real-estate' ),
	);

	return apply_filters( 'cl_payment_statuses', $payment_statuses );
}

/**
 * Retrieves keys for all available statuses for payments
 *
 * @since 2.3
 * @return array $payment_status All the available payment statuses
 */
function cl_get_payment_status_keys() {
	 $statuses = array_keys( cl_get_payment_statuses() );
	asort( $statuses );

	return array_values( $statuses );
}

/**
 * Checks whether a payment has been marked as complete.
 *
 * @since 1.0.8
 * @param int $payment_id Payment ID to check against
 * @return bool true if complete, false otherwise
 */
function cl_is_payment_complete( $payment_id = 0 ) {
	$payment = new Clpayment( $payment_id );

	$ret = false;

	if ( $payment->ID > 0 ) {

		if ( (int) $payment_id === (int) $payment->ID && 'publish' == $payment->status ) {
			$ret = true;
		}
	}

	return apply_filters( 'cl_is_payment_complete', $ret, $payment_id, $payment->post_status );
}

/**
 * Get Total Sales
 *
 * @since 1.2.2
 * @return int $count Total sales
 */
function cl_get_total_sales() {
	 $payments = cl_count_payments();
	return $payments->revoked + $payments->publish;
}

/**
 * Get Total Earnings
 *
 * @since 1.2
 * @return float $total Total earnings
 */
function cl_get_total_earnings() {
	$total = get_option( 'cl_earnings_total', false );

	// If no total stored in DB, use old method of calculating total earnings
	if ( false === $total ) {

		global $wpdb;

		$total = get_transient( 'cl_earnings_total' );

		if ( false === $total ) {

			$total = (float) 0;

			$args = apply_filters(
				'cl_get_total_earnings_args',
				array(
					'offset' => 0,
					'number' => -1,
					'status' => array( 'publish', 'revoked' ),
					'fields' => 'ids',
				)
			);

			$payments = cl_get_payments( $args );
			if ( $payments ) {

				/*
				 * If performing a purchase, we need to skip the very last payment in the database, since it calls
				 * cl_increase_total_earnings() on completion, which results in duplicated earnings for the very
				 * first purchase
				 */

				if ( did_action( 'cl_update_payment_status' ) ) {
					array_pop( $payments );
				}

				if ( ! empty( $payments ) ) {
					$payments = implode( ',', $payments );
					$total   += $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_cl_payment_total' AND post_id IN({$payments})" );
				}
			}

			// Cache results for 1 day. This cache is cleared automatically when a payment is made
			set_transient( 'cl_earnings_total', $total, 86400 );

			// Store the total for the first time
			update_option( 'cl_earnings_total', $total );
		}
	}

	if ( $total < 0 ) {
		$total = 0; // Don't ever show negative earnings
	}
	$formatting = new Formatting();

	return apply_filters( 'cl_total_earnings', round( $total, $formatting->cl_currency_decimal_filter() ) );
}

/**
 * Increase the Total Earnings
 *
 * @since 1.8.4
 * @param $amount int The amount you would like to increase the total earnings by.
 * @return float $total Total earnings
 */
function cl_increase_total_earnings( $amount = 0 ) {
	$total  = floatval( cl_get_total_earnings() );
	$total += floatval( $amount );
	update_option( 'cl_earnings_total', $total );
	return $total;
}

/**
 * Decrease the Total Earnings
 *
 * @since 1.8.4
 * @param $amount int The amount you would like to decrease the total earnings by.
 * @return float $total Total earnings
 */
function cl_decrease_total_earnings( $amount = 0 ) {
	$total  = cl_get_total_earnings();
	$total -= $amount;
	if ( $total < 0 ) {
		$total = 0;
	}
	update_option( 'cl_earnings_total', $total );
	return $total;
}

/**
 * Get Payment Meta for a specific Payment
 *
 * @since 1.2
 * @param int    $payment_id Payment ID
 * @param string $meta_key The meta key to pull
 * @param bool   $single Pull single meta entry or as an object
 * @return mixed $meta Payment Meta
 */
function cl_get_payment_meta( $payment_id = 0, $meta_key = '_cl_payment_meta', $single = true ) {
	$payment = new Clpayment( $payment_id );
	return $payment->get_meta( $meta_key, $single );
}

/**
 * Update the meta for a payment
 *
 * @param  integer $payment_id Payment ID
 * @param  string  $meta_key   Meta key to update
 * @param  string  $meta_value Value to update to
 * @param  string  $prev_value Previous value
 * @return mixed               Meta ID if successful, false if unsuccessful
 */
function cl_update_payment_meta( $payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {
	$payment = new Clpayment( $payment_id );
	return $payment->update_meta( $meta_key, $meta_value, $prev_value );
}

/**
 * Get the user_info Key from Payment Meta
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return array $user_info User Info Meta Values
 */
function cl_get_payment_meta_user_info( $payment_id ) {
	$payment = new Clpayment( $payment_id );
	return $payment->user_info;
}

/**
 * Get the listings Key from Payment Meta
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return array $listings listings Meta Values
 */
function cl_get_payment_meta_listings( $payment_id ) {
	$payment = new Clpayment( $payment_id );
	return $payment->listing;
}

/**
 * Get the cart_details Key from Payment Meta
 *
 * @since 1.2
 * @param int  $payment_id Payment ID
 * @param bool $include_bundle_files Whether to retrieve product IDs associated with a bundled product and return them in the array
 * @return array $cart_details Cart Details Meta Values
 */
function cl_get_payment_meta_cart_details( $payment_id, $include_bundle_files = false ) {
	$payment      = new Clpayment( $payment_id );
	$cart_details = $payment->cart_details;

	$payment_currency = $payment->currency;

	if ( ! empty( $cart_details ) && is_array( $cart_details ) ) {
		$listingsaction = new Listingsaction();

		foreach ( $cart_details as $key => $cart_item ) {
			$cart_details[ $key ]['currency'] = $payment_currency;

			// Ensure subtotal is set, for pre-1.9 orders
			if ( ! isset( $cart_item['subtotal'] ) ) {
				$cart_details[ $key ]['subtotal'] = $cart_item['price'];
			}

			if ( $include_bundle_files ) {

				if ( 'bundle' != $listingsaction->cl_get_listing_type( $cart_item['id'] ) ) {
					continue;
				}

				$price_id = cl_get_cart_item_price_id( $cart_item );
				$products = cl_get_bundled_products( $cart_item['id'], $price_id );

				if ( empty( $products ) ) {
					continue;
				}

				foreach ( $products as $product_id ) {
					$cart_details[] = array(
						'id'          => $product_id,
						'name'        => get_the_title( $product_id ),
						'item_number' => array(
							'id'      => $product_id,
							'options' => array(),
						),
						'price'       => 0,
						'subtotal'    => 0,
						'quantity'    => 1,
						'tax'         => 0,
						'in_bundle'   => 1,
						'parent'      => array(
							'id'      => $cart_item['id'],
							'options' => isset( $cart_item['item_number']['options'] ) ? $cart_item['item_number']['options'] : array(),
						),
					);
				}
			}
		}
	}

	return apply_filters( 'cl_payment_meta_cart_details', $cart_details, $payment_id );
}

/**
 * Get the user email associated with a payment
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return string $email User Email
 */
function cl_get_payment_user_email( $payment_id ) {
	$payment = new Clpayment( $payment_id );
	return $payment->email;
}

/**
 * Is the payment provided associated with a user account
 *
 * @since  2.4.4
 * @param  int $payment_id The payment ID
 * @return bool            If the payment is associated with a user (false) or not (true)
 */
function cl_is_guest_payment( $payment_id ) {
	$payment_user_id  = cl_get_payment_user_id( $payment_id );
	$is_guest_payment = ! empty( $payment_user_id ) && $payment_user_id > 0 ? false : true;

	return (bool) apply_filters( 'cl_is_guest_payment', $is_guest_payment, $payment_id );
}

/**
 * Get the user ID associated with a payment
 *
 * @since 1.5.1
 * @param int $payment_id Payment ID
 * @return string $user_id User ID
 */
function cl_get_payment_user_id( $payment_id ) {
	$payment = new Clpayment( $payment_id );
	return $payment->user_id;
}

/**
 * Get the customer ID associated with a payment
 *
 * @since 2.1
 * @param int $payment_id Payment ID
 * @return string $customer_id Customer ID
 */
function cl_get_payment_customer_id( $payment_id ) {
	$payment = new Clpayment( $payment_id );
	return $payment->customer_id;
}

/**
 * Get the status of the unlimited listings flag
 *
 * @since 2.0
 * @param int $payment_id Payment ID
 * @return bool $unlimited
 */
function cl_payment_has_unlimited_listings( $payment_id ) {
	$payment = new Clpayment( $payment_id );
	return $payment->has_unlimited_listing;
}

/**
 * Get the IP address used to make a purchase
 *
 * @since 1.9
 * @param int $payment_id Payment ID
 * @return string $ip User IP
 */
function cl_get_payment_user_ip( $payment_id ) {
	$payment = new Clpayment( $payment_id );
	return $payment->ip;
}

/**
 * Get the date a payment was completed
 *
 * @since 2.0
 * @param int $payment_id Payment ID
 * @return string $date The date the payment was completed
 */
function cl_get_payment_completed_date( $payment_id = 0 ) {
	$payment = new Clpayment( $payment_id );
	return $payment->completed_date;
}

/**
 * Get the gateway associated with a payment
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return string $gateway Gateway
 */
function cl_get_payment_gateway( $payment_id ) {
	$payment = new Clpayment( $payment_id );
	return $payment->gateway;
}

/**
 * Get the currency code a payment was made in
 *
 * @since 2.2
 * @param int $payment_id Payment ID
 * @return string $currency The currency code
 */
function cl_get_payment_currency_code( $payment_id = 0 ) {
	$payment = new Clpayment( $payment_id );
	return $payment->currency;
}

/**
 * Get the currency name a payment was made in
 *
 * @since 2.2
 * @param int $payment_id Payment ID
 * @return string $currency The currency name
 */
function cl_get_payment_currency( $payment_id = 0 ) {
	$currency = cl_get_payment_currency_code( $payment_id );
	return apply_filters( 'cl_payment_currency', cl_get_currency_name( $currency ), $payment_id );
}

/**
 * Get the purchase key for a purchase
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return string $key Purchase key
 */
function cl_get_payment_key( $payment_id = 0 ) {
	$payment = new Clpayment( $payment_id );
	return $payment->key;
}

/**
 * Get the payment order number
 *
 * This will return the payment ID if sequential order numbers are not enabled or the order number does not exist
 *
 * @since 2.0
 * @param int $payment_id Payment ID
 * @return string $number Payment order number
 */
function cl_get_payment_number( $payment_id = 0 ) {
	$payment = new Clpayment( $payment_id );
	return $payment->number;
}

/**
 * Formats the payment number with the prefix and postfix
 *
 * @since  2.4
 * @param  int $number The payment number to format
 * @return string      The formatted payment number
 */
function cl_format_payment_number( $number ) {

	if ( ! cl_admin_get_option( 'enable_sequential' ) ) {
		return $number;
	}

	if ( ! is_numeric( $number ) ) {
		return $number;
	}

	$prefix  = cl_admin_get_option( 'sequential_prefix' );
	$number  = absint( $number );
	$postfix = cl_admin_get_option( 'sequential_postfix' );

	$formatted_number = $prefix . $number . $postfix;

	return apply_filters( 'cl_format_payment_number', $formatted_number, $prefix, $number, $postfix );
}

/**
 * Gets the next available order number
 *
 * This is used when inserting a new payment
 *
 * @since 2.0
 * @return string $number The next available payment number
 */
function cl_get_next_payment_number() {
	if ( ! cl_admin_get_option( 'enable_sequential' ) ) {
		return false;
	}

	$number           = get_option( 'cl_last_payment_number' );
	$start            = cl_admin_get_option( 'sequential_start', 1 );
	$increment_number = true;

	if ( false !== $number ) {

		if ( empty( $number ) ) {

			$number           = $start;
			$increment_number = false;
		}
	} else {

		// This case handles the first addition of the new option, as well as if it get's deleted for any reason
		$payments     = new Clpaymentsquery(
			array(
				'number'  => 1,
				'order'   => 'DESC',
				'orderby' => 'ID',
				'output'  => 'posts',
				'fields'  => 'ids',
			)
		);
		$last_payment = $payments->get_payments();

		if ( ! empty( $last_payment ) ) {

			$number = cl_get_payment_number( $last_payment[0] );
		}

		if ( ! empty( $number ) && $number !== (int) $last_payment[0] ) {

			$number = cl_remove_payment_prefix_postfix( $number );
		} else {

			$number           = $start;
			$increment_number = false;
		}
	}

	$increment_number = apply_filters( 'cl_increment_payment_number', $increment_number, $number );

	if ( $increment_number ) {
		$number++;
	}

	return apply_filters( 'cl_get_next_payment_number', $number );
}

/**
 * Given a given a number, remove the pre/postfix
 *
 * @since  2.4
 * @param  string $number  The formatted Current Number to increment
 * @return string          The new Payment number without prefix and postfix
 */
function cl_remove_payment_prefix_postfix( $number ) {

	$prefix  = (string) cl_admin_get_option( 'sequential_prefix' );
	$postfix = (string) cl_admin_get_option( 'sequential_postfix' );

	// Remove prefix
	$number = preg_replace( '/' . $prefix . '/', '', $number, 1 );

	// Remove the postfix
	$length      = strlen( $number );
	$postfix_pos = strrpos( $number, $postfix );
	if ( false !== $postfix_pos ) {
		$number = substr_replace( $number, '', $postfix_pos, $length );
	}

	// Ensure it's a whole number
	$number = intval( $number );

	return apply_filters( 'cl_remove_payment_prefix_postfix', $number, $prefix, $postfix );
}

/**
 * Get the fully formatted payment amount. The payment amount is retrieved using
 * cl_get_payment_amount() and is then sent through cl_currency_filter() and
 * cl_format_amount() to format the amount correctly.
 *
 * @since 1.4
 * @param int $payment_id Payment ID
 * @return string $amount Fully formatted payment amount
 */
function cl_payment_amount( $payment_id = 0 ) {
	$amount = cl_get_payment_amount( $payment_id );
	return WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $amount ), cl_get_payment_currency_code( $payment_id ) );
}

/**
 * Get the amount associated with a payment
 *
 * @since 1.2
 * @param int $payment_id Payment ID
 * @return float Payment amount
 */
function cl_get_payment_amount( $payment_id ) {
	$payment = new Clpayment( $payment_id );

	return apply_filters( 'cl_payment_amount', floatval( $payment->total ), $payment_id );
}

/**
 * Retrieves subtotal for payment (this is the amount before taxes) and then
 * returns a full formatted amount. This function essentially calls
 * cl_get_payment_subtotal()
 *
 * @since 1.3.3
 *
 * @param int $payment_id Payment ID
 *
 * @see cl_get_payment_subtotal()
 *
 * @return array Fully formatted payment subtotal
 */
function cl_payment_subtotal( $payment_id = 0 ) {
	$subtotal = cl_get_payment_subtotal( $payment_id );

	return WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $subtotal ), cl_get_payment_currency_code( $payment_id ) );
}

/**
 * Retrieves subtotal for payment (this is the amount before taxes) and then
 * returns a non formatted amount.
 *
 * @since 1.3.3
 * @param int $payment_id Payment ID
 * @return float $subtotal Subtotal for payment (non formatted)
 */
function cl_get_payment_subtotal( $payment_id = 0 ) {
	$payment = new Clpayment( $payment_id );

	return $payment->subtotal;
}

/**
 * Retrieves taxed amount for payment and then returns a full formatted amount
 * This function essentially calls cl_get_payment_tax()
 *
 * @since 1.3.3
 * @see cl_get_payment_tax()
 * @param int  $payment_id Payment ID
 * @param bool $payment_meta Payment Meta provided? (default: false)
 * @return string $subtotal Fully formatted payment subtotal
 */
function cl_payment_tax( $payment_id = 0, $payment_meta = false ) {
	$tax = cl_get_payment_tax( $payment_id, $payment_meta );

	return WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $tax ), cl_get_payment_currency_code( $payment_id ) );
}

/**
 * Retrieves taxed amount for payment and then returns a non formatted amount
 *
 * @since 1.3.3
 * @param int  $payment_id Payment ID
 * @param bool $payment_meta Get payment meta?
 * @return float $tax Tax for payment (non formatted)
 */
function cl_get_payment_tax( $payment_id = 0, $payment_meta = false ) {
	$payment = new Clpayment( $payment_id );

	return $payment->tax;
}

/**
 * Retrieve the tax for a cart item by the cart key
 *
 * @since  2.5
 * @param  integer $payment_id The Payment ID
 * @param  int     $cart_key   The cart key
 * @return float               The item tax amount
 */
function cl_get_payment_item_tax( $payment_id = 0, $cart_key = false ) {
	$payment  = new Clpayment( $payment_id );
	$item_tax = 0;

	$cart_details = $payment->cart_details;

	if ( false !== $cart_key && ! empty( $cart_details ) && array_key_exists( $cart_key, $cart_details ) ) {
		$item_tax = ! empty( $cart_details[ $cart_key ]['tax'] ) ? $cart_details[ $cart_key ]['tax'] : 0;
	}

	return $item_tax;
}

/**
 * Retrieves arbitrary fees for the payment
 *
 * @since 1.5
 * @param int    $payment_id Payment ID
 * @param string $type Fee type
 * @return mixed array if payment fees found, false otherwise
 */
function cl_get_payment_fees( $payment_id = 0, $type = 'all' ) {
	$payment = new Clpayment( $payment_id );
	return $payment->get_fees( $type );
}

/**
 * Retrieves the transaction ID for the given payment
 *
 * @since  2.1
 * @param int $payment_id Payment ID
 * @return string The Transaction ID
 */
function cl_get_payment_transaction_id( $payment_id = 0 ) {
	$payment = new Clpayment( $payment_id );
	return $payment->transaction_id;
}

/**
 * Sets a Transaction ID in post meta for the given Payment ID
 *
 * @since  2.1
 * @param int    $payment_id Payment ID
 * @param string $transaction_id The transaction ID from the gateway
 * @return mixed Meta ID if successful, false if unsuccessful
 */
function cl_set_payment_transaction_id( $payment_id = 0, $transaction_id = '' ) {

	if ( empty( $payment_id ) || empty( $transaction_id ) ) {
		return false;
	}

	$transaction_id = apply_filters( 'cl_set_payment_transaction_id', $transaction_id, $payment_id );

	return cl_update_payment_meta( $payment_id, '_cl_payment_transaction_id', $transaction_id );
}

/**
 * Retrieve the purchase ID based on the purchase key
 *
 * @since 1.3.2
 * @global object $wpdb Used to query the database using the WordPress
 *   Database API
 * @param string $key the purchase key to search for
 * @return int $purchase Purchase ID
 */
function cl_get_purchase_id_by_key( $key ) {
	global $wpdb;
	$global_key_string = 'cl_purchase_id_by_key' . $key;
	global $$global_key_string;

	if ( null !== $$global_key_string ) {
		return $$global_key_string;
	}

	$purchase = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_cl_payment_purchase_key' AND meta_value = %s LIMIT 1", $key ) );

	if ( $purchase != null ) {
		$$global_key_string = $purchase;
		return $$global_key_string;
	}

	return 0;
}

/**
 * Retrieve the purchase ID based on the transaction ID
 *
 * @since 2.4
 * @global object $wpdb Used to query the database using the WordPress
 *   Database API
 * @param string $key the transaction ID to search for
 * @return int $purchase Purchase ID
 */
function cl_get_purchase_id_by_transaction_id( $key ) {
	global $wpdb;

	$purchase = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_cl_payment_transaction_id' AND meta_value = %s LIMIT 1", $key ) );

	if ( $purchase != null ) {
		return $purchase;
	}

	return 0;
}

/**
 * Retrieve all notes attached to a purchase
 *
 * @since 1.4
 * @param int    $payment_id The payment ID to retrieve notes for
 * @param string $search Search for notes that contain a search term
 * @return array $notes Payment Notes
 */
function cl_get_payment_notes( $payment_id = 0, $search = '' ) {

	if ( empty( $payment_id ) && empty( $search ) ) {
		return false;
	}

	remove_action( 'pre_get_comments', 'cl_hide_payment_notes', 10 );
	remove_filter( 'comments_clauses', 'cl_hide_payment_notes_pre_41', 10 );

	$notes = get_comments(
		array(
			'post_id' => $payment_id,
			'order'   => 'ASC',
			'search'  => $search,
		)
	);

	add_action( 'pre_get_comments', 'cl_hide_payment_notes', 10 );
	add_filter( 'comments_clauses', 'cl_hide_payment_notes_pre_41', 10, 2 );

	return $notes;
}


/**
 * Add a note to a payment
 *
 * @since 1.4
 * @param int    $payment_id The payment ID to store a note for
 * @param string $note The note to store
 * @return int The new note ID
 */
function cl_insert_payment_note( $payment_id = 0, $note = '' ) {
	if ( empty( $payment_id ) ) {
		return false;
	}

	do_action( 'cl_pre_insert_payment_note', $payment_id, $note );

	$note_id = wp_insert_comment(
		wp_filter_comment(
			array(
				'comment_post_ID'      => $payment_id,
				'comment_content'      => $note,
				'user_id'              => is_admin() ? get_current_user_id() : 0,
				'comment_date'         => current_time( 'mysql' ),
				'comment_date_gmt'     => current_time( 'mysql', 1 ),
				'comment_approved'     => 1,
				'comment_parent'       => 0,
				'comment_author'       => '',
				'comment_author_IP'    => '',
				'comment_author_url'   => '',
				'comment_author_email' => '',
				'comment_type'         => 'cl_payment_note',

			)
		)
	);

	do_action( 'cl_insert_payment_note', $note_id, $payment_id, $note );

	return $note_id;
}

/**
 * Deletes a payment note
 *
 * @since 1.6
 * @param int $comment_id The comment ID to delete
 * @param int $payment_id The payment ID the note is connected to
 * @return bool True on success, false otherwise
 */
function cl_delete_payment_note( $comment_id = 0, $payment_id = 0 ) {
	if ( empty( $comment_id ) ) {
		return false;
	}

	do_action( 'cl_pre_delete_payment_note', $comment_id, $payment_id );
	$ret = wp_delete_comment( $comment_id, true );
	do_action( 'cl_post_delete_payment_note', $comment_id, $payment_id );

	return $ret;
}

/**
 * Gets the payment note HTML
 *
 * @since 1.9
 * @param object|int $note The comment object or ID
 * @param int        $payment_id The payment ID the note is connected to
 * @return string
 */
function cl_get_payment_note_html( $note, $payment_id = 0 ) {

	if ( is_numeric( $note ) ) {
		$note = get_comment( $note );
	}

	if ( ! empty( $note->user_id ) ) {
		$user = get_userdata( $note->user_id );
		$user = $user->display_name;
	} else {
		$user = __( 'CL BOT', 'essential-wp-real-estate' );
	}

	$date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );

	$delete_note_url = wp_nonce_url(
		add_query_arg(
			array(
				'cl-action'  => 'delete_payment_note',
				'note_id'    => $note->comment_ID,
				'payment_id' => $payment_id,
			)
		),
		'cl_delete_payment_note_' . $note->comment_ID
	);

	$note_html  = '<div class="cl-payment-note" id="cl-payment-note-' . esc_attr( $note->comment_ID ) . '">';
	$note_html .= '<p>';
	$note_html .= '<strong>' . esc_html( $user ) . '</strong>&nbsp;&ndash;&nbsp;' . date_i18n( $date_format, strtotime( $note->comment_date ) ) . '<br/>';
	$note_html .= make_clickable( wp_kses_post( $note->comment_content ) );
	$note_html .= '&nbsp;&ndash;&nbsp;<a href="' . esc_url( $delete_note_url ) . '" class="cl-delete-payment-note" data-note-id="' . absint( $note->comment_ID ) . '" data-payment-id="' . absint( $payment_id ) . '">' . __( 'Delete', 'essential-wp-real-estate' ) . '</a>';
	$note_html .= '</p>';
	$note_html .= '</div>';

	return $note_html;
}

/**
 * Exclude notes (comments) on cl_payment post type from showing in Recent
 * Comments widgets
 *
 * @since 1.4.1
 * @param obj $query WordPress Comment Query Object
 * @return void
 */
function cl_hide_payment_notes( $query ) {
	global $wp_version;

	if ( version_compare( floatval( $wp_version ), '4.1', '>=' ) ) {
		$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();
		if ( ! is_array( $types ) ) {
			$types = array( $types );
		}
		$types[]                           = 'cl_payment_note';
		$query->query_vars['type__not_in'] = $types;
	}
}
add_action( 'pre_get_comments', 'cl_hide_payment_notes', 10 );

/**
 * Exclude notes (comments) on cl_payment post type from showing in Recent
 * Comments widgets
 *
 * @since 2.2
 * @param array $clauses Comment clauses for comment query
 * @param obj   $wp_comment_query WordPress Comment Query Object
 * @return array $clauses Updated comment clauses
 */
function cl_hide_payment_notes_pre_41( $clauses, $wp_comment_query ) {
	global $wpdb, $wp_version;

	if ( version_compare( floatval( $wp_version ), '4.1', '<' ) ) {
		$clauses['where'] .= ' AND comment_type != "cl_payment_note"';
	}

	return $clauses;
}
add_filter( 'comments_clauses', 'cl_hide_payment_notes_pre_41', 10, 2 );


/**
 * Exclude notes (comments) on cl_payment post type from showing in comment feeds
 *
 * @since 1.5.1
 * @param array $where
 * @param obj   $wp_comment_query WordPress Comment Query Object
 * @return array $where
 */
function cl_hide_payment_notes_from_feeds( $where, $wp_comment_query ) {
	global $wpdb;

	$where .= $wpdb->prepare( ' AND comment_type != %s', 'cl_payment_note' );
	return $where;
}
add_filter( 'comment_feed_where', 'cl_hide_payment_notes_from_feeds', 10, 2 );


/**
 *
 * @since 1.5.2
 * @param array $stats (empty from core filter)
 * @param int   $post_id Post ID
 * @return array Array of comment counts
 */
function cl_remove_payment_notes_in_comment_counts( $stats, $post_id ) {
	global $wpdb, $pagenow;

	$array_excluded_pages = array( 'index.php', 'edit-comments.php' );
	if ( ! in_array( $pagenow, $array_excluded_pages ) ) {
		return $stats;
	}

	$post_id = (int) $post_id;

	if ( apply_filters( 'cl_count_payment_notes_in_comments', false ) ) {
		return $stats;
	}

	$stats = wp_cache_get( "comments-{$post_id}", 'counts' );

	if ( false !== $stats ) {
		return $stats;
	}

	$where = 'WHERE comment_type != "cl_payment_note"';

	if ( $post_id > 0 ) {
		$where .= $wpdb->prepare( ' AND comment_post_ID = %d', $post_id );
	}

	$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A );
	// fix for php 8.0+
	if ( false === $stats ) {
		$stats = array();
	}
	$total    = 0;
	$approved = array(
		'0'            => 'moderated',
		'1'            => 'approved',
		'spam'         => 'spam',
		'trash'        => 'trash',
		'post-trashed' => 'post-trashed',
	);
	foreach ( (array) $count as $row ) {
		// Don't count post-trashed toward totals
		if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] ) {
			$total += $row['num_comments'];
		}
		if ( isset( $approved[ $row['comment_approved'] ] ) ) {
			$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
		}
	}

	$stats['total_comments'] = $total;
	foreach ( $approved as $key ) {
		if ( empty( $stats[ $key ] ) ) {
			$stats[ $key ] = 0;
		}
	}

	$stats = (object) $stats;
	wp_cache_set( "comments-{$post_id}", $stats, 'counts' );

	return $stats;
}
add_filter( 'wp_count_comments', 'cl_remove_payment_notes_in_comment_counts', 10, 2 );


/**
 * Filter where older than one week
 *
 * @since 1.6
 * @param string $where Where clause
 * @return string $where Modified where clause
 */
function cl_filter_where_older_than_week( $where = '' ) {
	// Payments older than one week
	$start  = date( 'Y-m-d', strtotime( '-7 days' ) );
	$where .= " AND post_date <= '{$start}'";
	return $where;
}






// Initiate the logging system
$GLOBALS['cl_logs'] = new CL_Logging();

/**
 * Record a log entry
 *
 * This is just a simple wrapper function for the log class add() function
 *
 * @since 1.3.3
 *
 * @param string $title
 * @param string $message
 * @param int    $parent
 * @param null   $type
 *
 * @uses CL_Logging::add()
 *
 * @return mixed ID of the new log entry
 */
function cl_record_log( $title = '', $message = '', $parent = 0, $type = null ) {
	global $cl_logs;
	$log = $cl_logs->add( $title, $message, $parent, $type );
	return $log;
}


/**
 * Logs a message to the debug log file
 *
 * @since 2.8.7
 * @since 2.9.4 Added the 'force' option.
 *
 * @param string $message
 * @return void
 */
function cl_debug_log( $message = '', $force = false ) {
	global $cl_logs;

	if ( function_exists( 'mb_convert_encoding' ) ) {

		$message = mb_convert_encoding( $message, 'UTF-8' );
	}

	$cl_logs->log_to_file( $message );
}

function cl_get_php_arg_separator_output() {
	return ini_get( 'arg_separator.output' );
}
