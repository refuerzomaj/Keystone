<?php
use Essential\Restate\Front\Purchase\Payments\Clpayment;
use Essential\Restate\Front\Models\Listingsaction;
use Essential\Restate\Common\Customer\Customer;
use Essential\Restate\Front\Session\Session;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function cl_update_payment_details( $data ) {

	check_admin_referer( 'cl_update_payment_details_nonce' );

	// Retrieve the payment ID
	$payment_id = absint( $data['cl_payment_id'] );
	$payment    = new Clpayment( $payment_id );

	// Retrieve existing payment meta
	$meta      = $payment->get_meta();
	$user_info = $payment->user_info;

	$status    = $data['cl-payment-status'];
	$unlimited = isset( $data['cl-unlimited-listings'] ) ? '1' : '';
	$date      = cl_sanitization( $data['cl-payment-date'] );
	$hour      = cl_sanitization( $data['cl-payment-time-hour'] );

	// Restrict to our high and low
	if ( $hour > 23 ) {
		$hour = 23;
	} elseif ( $hour < 0 ) {
		$hour = 00;
	}

	$minute = cl_sanitization( $data['cl-payment-time-min'] );

	// Restrict to our high and low
	if ( $minute > 59 ) {
		$minute = 59;
	} elseif ( $minute < 0 ) {
		$minute = 00;
	}

	$address = array_map( 'trim', $data['cl-payment-address'][0] );

	$curr_total = WPERECCP()->common->formatting->cl_sanitize_amount( $payment->total );
	$new_total  = WPERECCP()->common->formatting->cl_sanitize_amount( cl_sanitization( $_POST['cl-payment-total'] ) );
	$tax        = isset( $_POST['cl-payment-tax'] ) ? WPERECCP()->common->formatting->cl_sanitize_amount( cl_sanitization( $_POST['cl-payment-tax'] ) ) : 0;
	$date       = date( 'Y-m-d', strtotime( $date ) ) . ' ' . $hour . ':' . $minute . ':00';

	$curr_customer_id = cl_sanitization( $data['cl-current-customer'] );
	$new_customer_id  = cl_sanitization( $data['customer-id'] );

	// Setup purchased listings and price options
	$updated_listings = isset( $_POST['cl-payment-details-listings'] ) ? cl_sanitization( $_POST['cl-payment-details-listings'] ) : false;

	if ( $updated_listings ) {

		foreach ( $updated_listings as $cart_position => $listing ) {

			// If this item doesn't have a log yet, add one for each quantity count
			$has_log = absint( $listing['has_log'] );
			$has_log = empty( $has_log ) ? false : true;

			if ( $has_log ) {

				$quantity   = isset( $listing['quantity'] ) ? absint( $listing['quantity'] ) : 1;
				$item_price = isset( $listing['item_price'] ) ? $listing['item_price'] : 0;
				$item_tax   = isset( $listing['item_tax'] ) ? $listing['item_tax'] : 0;

				// Format any items that are currency.
				$item_price = WPERECCP()->common->formatting->cl_format_amount( $item_price );
				$item_tax   = WPERECCP()->common->formatting->cl_format_amount( $item_tax );

				$args = array(
					'item_price' => $item_price,
					'quantity'   => $quantity,
					'tax'        => $item_tax,
				);

				$payment->modify_cart_item( $cart_position, $args );
			} else {

				// This
				if ( empty( $listing['item_price'] ) ) {
					$listing['item_price'] = 0.00;
				}

				if ( empty( $listing['item_tax'] ) ) {
					$listing['item_tax'] = 0.00;
				}

				$item_price = $listing['item_price'];
				$listing_id = absint( $listing['id'] );
				$quantity   = absint( $listing['quantity'] ) > 0 ? absint( $listing['quantity'] ) : 1;
				$price_id   = false;
				$tax        = $listing['item_tax'];

				if ( WPERECCP()->front->listing_provider->cl_has_variable_prices( $listing_id ) && isset( $listing['price_id'] ) ) {
					$price_id = absint( $listing['price_id'] );
				}

				// Set some defaults
				$args = array(
					'quantity'   => $quantity,
					'item_price' => $item_price,
					'price_id'   => $price_id,
					'tax'        => $tax,
				);

				$payment->add_listing( $listing_id, $args );
			}
		}
	}

	do_action( 'cl_update_edited_purchase', $payment_id );

	$payment->date = $date;

	$customer_changed = false;

	if ( isset( $data['cl-new-customer'] ) && $data['cl-new-customer'] == '1' ) {

		$email = isset( $data['cl-new-customer-email'] ) ? cl_sanitization( $data['cl-new-customer-email'] ) : '';
		$names = isset( $data['cl-new-customer-name'] ) ? cl_sanitization( $data['cl-new-customer-name'] ) : '';

		if ( empty( $email ) || empty( $names ) ) {
			wp_die( __( 'New Customers require a name and email address', 'essential-wp-real-estate' ) );
		}

		$customer = new Customer( $email );
		if ( empty( $customer->id ) ) {
			$customer_data = array(
				'name'  => $names,
				'email' => $email,
			);
			$user_id       = email_exists( $email );
			if ( false !== $user_id ) {
				$customer_data['user_id'] = $user_id;
			}

			if ( ! $customer->create( $customer_data ) ) {
				// Failed to crete the new customer, assume the previous customer
				$customer_changed = false;
				$customer         = new Customer( $curr_customer_id );
				WPERECCP()->front->error->cl_set_error( 'cl-payment-new-customer-fail', __( 'Error creating new customer', 'essential-wp-real-estate' ) );
			}
		} else {
			wp_die( sprintf( __( 'A customer with the email address %s already exists. Please go back and use the "Assign to another customer" link to assign this payment to them.', 'essential-wp-real-estate' ), $email ) );
		}

		$new_customer_id = $customer->id;

		$previous_customer = new Customer( $curr_customer_id );

		$customer_changed = true;
	} elseif ( $curr_customer_id !== $new_customer_id ) {

		$customer = new Customer( $new_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

		$previous_customer = new Customer( $curr_customer_id );

		$customer_changed = true;
	} else {

		$customer = new Customer( $curr_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;
	}

	// Setup first and last name from input values
	$names      = explode( ' ', $names );
	$first_name = ! empty( $names[0] ) ? $names[0] : '';
	$last_name  = '';
	if ( ! empty( $names[1] ) ) {
		unset( $names[0] );
		$last_name = implode( ' ', $names );
	}

	if ( $customer_changed ) {

		// Remove the stats and payment from the previous customer and attach it to the new customer
		$previous_customer->remove_payment( $payment_id, false );
		$customer->attach_payment( $payment_id, false );

		// If purchase was completed and not ever refunded, adjust stats of customers
		if ( 'revoked' == $status || 'publish' == $status ) {

			$previous_customer->decrease_purchase_count();
			$previous_customer->decrease_value( $new_total );

			$customer->increase_purchase_count();
			$customer->increase_value( $new_total );
		}

		$payment->customer_id = $customer->id;
	}

	// Set new meta values
	$payment->user_id    = $customer->user_id;
	$payment->email      = $customer->email;
	$payment->first_name = $first_name;
	$payment->last_name  = $last_name;
	$payment->address    = $address;

	$payment->total = $new_total;
	$payment->tax   = $tax;

	$payment->has_unlimited_listings = $unlimited;

	// Check for payment notes
	if ( ! empty( $data['cl-payment-note'] ) ) {

		$note = wp_kses( $data['cl-payment-note'], array() );
		cl_insert_payment_note( $payment->ID, $note );
	}

	// Set new status
	$payment->status = $status;

	// Adjust total store earnings if the payment total has been changed
	if ( $new_total !== $curr_total && ( 'publish' == $status || 'revoked' == $status ) ) {

		if ( $new_total > $curr_total ) {
			// Increase if our new total is higher
			$difference = $new_total - $curr_total;
			cl_increase_total_earnings( $difference );
		} elseif ( $curr_total > $new_total ) {
			// Decrease if our new total is lower
			$difference = $curr_total - $new_total;
			cl_decrease_total_earnings( $difference );
		}
	}

	$updated = $payment->save();

	if ( 0 === $updated ) {
		wp_die( __( 'Error Updating Payment', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 400 ) );
	}

	do_action( 'cl_updated_edited_purchase', $payment_id );

	wp_safe_redirect( admin_url( 'edit.php?post_type=cl_cpt&page=cl-payment-history&view=view-order-details&cl-message=payment-updated&id=' . esc_attr( $payment_id ) ) );
	exit;
}
add_action( 'cl_update_payment_details', 'cl_update_payment_details' );


/**
 * Complete a purchase
 *
 * Performs all necessary actions to complete a purchase.
 * Triggered by the cl_update_payment_status() function.
 *
 * @since 1.0.8.3
 * @param int    $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @return void
 */
function cl_complete_purchase( $payment_id, $new_status, $old_status ) {
	if ( $old_status == 'publish' || $old_status == 'complete' ) {
		return; // Make sure that payments are only completed once
	}

	// Make sure the payment completion is only processed when new status is complete
	if ( $new_status != 'publish' && $new_status != 'complete' ) {
		return;
	}

	$payment = new Clpayment( $payment_id );

	$creation_date  = get_post_field( 'post_date', $payment_id, 'raw' );
	$completed_date = $payment->completed_date;
	$user_info      = $payment->user_info;
	$customer_id    = $payment->customer_id;
	$amount         = $payment->total;
	$cart_details   = $payment->cart_details;

	do_action( 'cl_pre_complete_purchase', $payment_id );

	if ( is_array( $cart_details ) ) {

		// Increase purchase count and earnings
		$listingsaction = new Listingsaction();

		foreach ( $cart_details as $cart_index => $listing ) {

			// "bundle" or "default"
			$listing_type = $listingsaction->cl_get_listing_type( $listing['id'] );
			$price_id     = isset( $listing['item_number']['options']['price_id'] ) ? (int) $listing['item_number']['options']['price_id'] : false;
			// Increase earnings and fire actions once per quantity number

			for ( $i = 0; $i < $listing['quantity']; $i++ ) {

				// Ensure these actions only run once, ever
				if ( empty( $completed_date ) ) {

					$listingsaction->cl_record_sale_in_log( $listing['id'], $payment_id, $price_id, $creation_date );
					do_action( 'cl_complete_listing_purchase', $listing['id'], $payment_id, $listing_type, $listing, $cart_index );
				}
			}

			// Increase the earnings for this listing ID
			$listingsaction->cl_increase_earnings( $listing['id'], $listing['price'] );
			$listingsaction->cl_increase_purchase_count( $listing['id'], $listing['quantity'] );
		}

		// Clear the total earnings cache
		delete_transient( 'cl_earnings_total' );
		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'cl_earnings_this_monththis_month' ) );
		delete_transient( md5( 'cl_earnings_todaytoday' ) );
	}

	// Increase the customer's purchase stats
	$customer = new Customer( $customer_id );
	$customer->increase_purchase_count();
	$customer->increase_value( $amount );

	cl_increase_total_earnings( $amount );

	// Check for discount codes and increment their use counts
	if ( ! empty( $user_info['discount'] ) && $user_info['discount'] !== 'none' ) {

		$discounts = array_map( 'trim', explode( ',', $user_info['discount'] ) );

		if ( ! empty( $discounts ) ) {

			foreach ( $discounts as $code ) {

				cl_increase_discount_usage( $code );
			}
		}
	}

	// Ensure this action only runs once ever
	if ( empty( $completed_date ) ) {

		// Save the completed date
		$payment->completed_date = current_time( 'mysql' );
		$payment->save();

		do_action( 'cl_complete_purchase', $payment_id, $payment, $customer );

		// If cron doesn't work on a site, allow the filter to use __return_false and run the events immediately.
		$use_cron = apply_filters( 'cl_use_after_payment_actions', true, $payment_id );
		if ( false === $use_cron ) {

			do_action( 'cl_after_payment_actions', $payment_id, $payment, $customer );
		}
	}

	// Empty the shopping cart

	empty_cart();
}
add_action( 'cl_update_payment_status', 'cl_complete_purchase', 100, 3 );

function empty_cart() {
	 $session = new Session();
	// Remove cart contents.
	$session->set( 'cl_cart', null );

	// Remove all cart fees.
	$session->set( 'cl_cart_fees', null );

	// Remove any resuming payments.
	$session->set( 'cl_resume_payment', null );
	$session->set( 'cart_discounts', null );
}

/**
 * Schedules the one time event via WP_Cron to fire after purchase actions.
 *
 * Is run on the cl_complete_purchase action.
 *
 * @since 2.8
 * @param $payment_id
 */
function cl_schedule_after_payment_action( $payment_id ) {
	$use_cron = apply_filters( 'cl_use_after_payment_actions', true, $payment_id );
	if ( $use_cron ) {
		$after_payment_delay = apply_filters( 'cl_after_payment_actions_delay', 30, $payment_id );

		// Use time() instead of current_time( 'timestamp' ) to avoid scheduling the event in the past when server time
		// and WordPress timezone are different.
		wp_schedule_single_event( time() + $after_payment_delay, 'cl_after_payment_scheduled_actions', array( $payment_id, false ) );
	}
}
add_action( 'cl_complete_purchase', 'cl_schedule_after_payment_action', 10, 1 );

/**
 * Executes the one time event used for after purchase actions.
 *
 * @since 2.8
 * @param $payment_id
 * @param $force
 */
function cl_process_after_payment_actions( $payment_id = 0, $force = false ) {
	if ( empty( $payment_id ) ) {
		return;
	}

	$payment   = new Clpayment( $payment_id );
	$has_fired = $payment->get_meta( '_cl_complete_actions_run' );
	if ( ! empty( $has_fired ) && false === $force ) {
		return;
	}

	$payment->add_note( __( 'After payment actions processed.', 'essential-wp-real-estate' ) );
	$payment->update_meta( '_cl_complete_actions_run', time() ); // This is in GMT

	do_action( 'cl_after_payment_actions', $payment_id, $payment, new Customer( $payment->customer_id ) );
}
add_action( 'cl_after_payment_scheduled_actions', 'cl_process_after_payment_actions', 10, 1 );

/**
 * Record payment status change
 *
 * @since 1.4.3
 * @param int    $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @return void
 */
function cl_record_status_change( $payment_id, $new_status, $old_status ) {

	// Get the list of statuses so that status in the payment note can be translated
	$stati      = cl_get_payment_statuses();
	$old_status = isset( $stati[ $old_status ] ) ? $stati[ $old_status ] : $old_status;
	$new_status = isset( $stati[ $new_status ] ) ? $stati[ $new_status ] : $new_status;

	$status_change = sprintf( __( 'Status changed from %1$s to %2$s', 'essential-wp-real-estate' ), $old_status, $new_status );

	cl_insert_payment_note( $payment_id, $status_change );
}
add_action( 'cl_update_payment_status', 'cl_record_status_change', 100, 3 );

/**
 * Flushes the current user's purchase history transient when a payment status
 * is updated
 *
 * @since 1.2.2
 *
 * @param int    $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 */
function cl_clear_user_history_cache( $payment_id, $new_status, $old_status ) {
	$payment = new Clpayment( $payment_id );

	if ( ! empty( $payment->user_id ) ) {
		delete_transient( 'cl_user_' . $payment->user_id . '_purchases' );
	}
}
add_action( 'cl_update_payment_status', 'cl_clear_user_history_cache', 10, 3 );

/**
 * Updates all old payments, prior to 1.2, with new
 * meta for the total purchase amount
 *
 * This is so that payments can be queried by their totals
 *
 * @since 1.2
 * @param array $data Arguments passed
 * @return void
 */
function cl_update_old_payments_with_totals( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'cl_upgrade_payments_nonce' ) ) {
		return;
	}

	if ( get_option( 'cl_payment_totals_upgraded' ) ) {
		return;
	}

	$payments = cl_get_payments(
		array(
			'offset' => 0,
			'number' => -1,
			'mode'   => 'all',
		)
	);

	if ( $payments ) {
		foreach ( $payments as $payment ) {

			$payment = new Clpayment( $payment->ID );
			$meta    = $payment->get_meta();

			$payment->total = $meta['amount'];
			$payment->save();
		}
	}

	add_option( 'cl_payment_totals_upgraded', 1 );
}
add_action( 'cl_upgrade_payments', 'cl_update_old_payments_with_totals' );

/**
 * Updates week-old+ 'pending' orders to 'abandoned'
 *
 *  This function is only intended to be used by WordPress cron.
 *
 * @since 1.6
 * @return void
 */
function cl_mark_abandoned_orders() {
	// Bail if not in WordPress cron
	if ( ! cl_doing_cron() ) {
		return;
	}

	$args = array(
		'status' => 'pending',
		'number' => -1,
		'output' => 'cl_payments',
	);

	add_filter( 'posts_where', 'cl_filter_where_older_than_week' );

	$payments = cl_get_payments( $args );

	remove_filter( 'posts_where', 'cl_filter_where_older_than_week' );

	if ( $payments ) {
		foreach ( $payments as $payment ) {
			if ( 'pending' === $payment->post_status ) {
				$payment->status = 'abandoned';
				$payment->save();
			}
		}
	}
}
add_action( 'cl_weekly_scheduled_events', 'cl_mark_abandoned_orders' );

/**
 * Listens to the updated_postmeta hook for our backwards compatible payment_meta updates, and runs through them
 *
 * @since  2.3
 * @param  int              $meta_id    The Meta ID that was updated
 * @param  int              $object_id  The Object ID that was updated (post ID)
 * @param  string           $meta_key   The Meta key that was updated
 * @param  string|int|float $meta_value The Value being updated
 * @return bool|int             If successful the number of rows updated, if it fails, false
 */
function cl_update_payment_backwards_compat( $meta_id, $object_id, $meta_key, $meta_value ) {

	$meta_keys = array( '_cl_payment_meta', '_cl_payment_tax' );

	if ( ! in_array( $meta_key, $meta_keys ) ) {
		return;
	}

	global $wpdb;
	switch ( $meta_key ) {

		case '_cl_payment_meta':
			$meta_value = maybe_unserialize( $meta_value );

			if ( ! isset( $meta_value['tax'] ) ) {
				return;
			}

			$tax_value = $meta_value['tax'];

			$data         = array( 'meta_value' => $tax_value );
			$where        = array(
				'post_id'  => $object_id,
				'meta_key' => '_cl_payment_tax',
			);
			$data_format  = array( '%f' );
			$where_format = array( '%d', '%s' );
			break;

		case '_cl_payment_tax':
			$tax_value    = ! empty( $meta_value ) ? $meta_value : 0;
			$current_meta = cl_get_payment_meta( $object_id, '_cl_payment_meta', true );

			$current_meta['tax'] = $tax_value;
			$new_meta            = maybe_serialize( $current_meta );

			$data         = array( 'meta_value' => $new_meta );
			$where        = array(
				'post_id'  => $object_id,
				'meta_key' => '_cl_payment_meta',
			);
			$data_format  = array( '%s' );
			$where_format = array( '%d', '%s' );

			break;
	}

	$updated = $wpdb->update( $wpdb->postmeta, $data, $where, $data_format, $where_format );

	if ( ! empty( $updated ) ) {
		// Since we did a direct DB query, clear the postmeta cache.
		wp_cache_delete( $object_id, 'post_meta' );
	}

	return $updated;
}
add_action( 'updated_postmeta', 'cl_update_payment_backwards_compat', 10, 4 );

/**
 * Deletes cl_stats_ transients that have expired to prevent database clogs
 *
 * @since 2.6.7
 * @return void
 */
function cl_cleanup_stats_transients() {
	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( defined( 'WP_INSTALLING' ) ) {
		return;
	}

	$now        = current_time( 'timestamp' );
	$transients = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '%\_transient_timeout\_cl\_stats\_%' AND option_value+0 < $now LIMIT 0, 200;" );
	$to_delete  = array();

	if ( ! empty( $transients ) ) {

		foreach ( $transients as $transient ) {

			$to_delete[] = $transient->option_name;
			$to_delete[] = str_replace( '_timeout', '', $transient->option_name );
		}
	}

	if ( ! empty( $to_delete ) ) {

		$option_names = implode( "','", $to_delete );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')" );
	}
}
add_action( 'cl_daily_scheduled_events', 'cl_cleanup_stats_transients' );

/**
 * Process an attempt to complete a recoverable payment.
 *
 * @since  2.7
 * @return void
 */
function cl_recover_payment() {
	if ( empty( $_GET['payment_id'] ) ) {
		return;
	}

	$payment = new Clpayment( $_GET['payment_id'] );
	if ( $payment->ID !== (int) $_GET['payment_id'] ) {
		return;
	}

	if ( ! $payment->is_recoverable() ) {
		return;
	}

	if (
		// Logged in, but wrong user ID
		( is_user_logged_in() && $payment->user_id != get_current_user_id() )

		// ...OR...
		||

		// Logged out, but payment is for a user
		( ! is_user_logged_in() && ! empty( $payment->user_id ) )
	) {
		$redirect = get_permalink( cl_admin_get_option( 'purchase_history_page' ) );
		WPERECCP()->front->error->cl_set_error( 'cl-payment-recovery-user-mismatch', __( 'Error resuming payment.', 'essential-wp-real-estate' ) );
		wp_redirect( $redirect );
	}

	$payment->add_note( __( 'Payment recovery triggered URL', 'essential-wp-real-estate' ) );

	// Empty out the cart.
	WPERECCP()->front->cart->empty_cart();

	// Recover any listings.
	foreach ( $payment->cart_details as $listing ) {
		cl_add_to_cart( $listing['id'], $listing['item_number']['options'] );

		// Recover any item specific fees.
		if ( ! empty( $listing['fees'] ) ) {
			foreach ( $listing['fees'] as $key => $fee ) {
				$fee['id'] = ! empty( $fee['id'] ) ? $fee['id'] : $key;
				WPERECCP()->front->fees->add_fee( $fee );
			}
		}
	}

	// Recover any global fees.
	foreach ( $payment->fees as $key => $fee ) {
		$fee['id'] = ! empty( $fee['id'] ) ? $fee['id'] : $key;
		WPERECCP()->front->fees->add_fee( $fee );
	}

	// Recover any discounts.
	if ( 'none' !== $payment->discounts && ! empty( $payment->discounts ) ) {
		$discounts = ! is_array( $payment->discounts ) ? explode( ',', $payment->discounts ) : $payment->discounts;

		foreach ( $discounts as $discount ) {
			WPERECCP()->front->discountaction->cl_set_cart_discount( $discount );
		}
	}

	WPERECCP()->front->session->set( 'cl_resume_payment', $payment->ID );

	$redirect_args = array( 'payment-mode' => $payment->gateway );
	$redirect      = add_query_arg( $redirect_args, cl_get_checkout_uri() );
	wp_redirect( $redirect );
	exit;
}
add_action( 'cl_recover_payment', 'cl_recover_payment' );

/**
 * If the payment trying to be recovered has a User ID associated with it, be sure it's the same user.
 *
 * @since  2.7
 * @return void
 */
function cl_recovery_user_mismatch() {
	if ( ! cl_is_checkout() ) {
		return;
	}

	$resuming_payment = WPERECCP()->front->session->get( 'cl_resume_payment' );
	if ( $resuming_payment ) {
		$payment = new Clpayment( $resuming_payment );
		if ( is_user_logged_in() && $payment->user_id != get_current_user_id() ) {
			WPERECCP()->front->cart->cl_empty_cart();
			WPERECCP()->front->error->cl_set_error( 'cl-payment-recovery-user-mismatch', __( 'Error resuming payment.', 'essential-wp-real-estate' ) );
			wp_redirect( get_permalink( cl_admin_get_option( 'purchase_page' ) ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'cl_recovery_user_mismatch' );

/**
 * If the payment trying to be recovered has a User ID associated with it, we need them to log in.
 *
 * @since  2.7
 * @return void
 */
function cl_recovery_force_login_fields() {
	 $resuming_payment = WPERECCP()->front->session->get( 'cl_resume_payment' );
	if ( $resuming_payment ) {
		$payment        = new Clpayment( $resuming_payment );
		$requires_login = WPERECCP()->common->options->cl_no_guest_checkout();
		if ( ( $requires_login && ! is_user_logged_in() ) && ( $payment->user_id > 0 && ( ! is_user_logged_in() ) ) ) {
			?>
			<div class="cl-alert cl-alert-info">
				<p><?php _e( 'To complete this payment, please login to your account.', 'essential-wp-real-estate' ); ?></p>
				<p>
					<a href="<?php echo esc_url( cl_get_lostpassword_url() ); ?>" title="<?php esc_attr_e( 'Lost Password', 'essential-wp-real-estate' ); ?>">
						<?php _e( 'Lost Password?', 'essential-wp-real-estate' ); ?>
					</a>
				</p>
			</div>
			<?php
			$show_register_form = cl_admin_get_option( 'show_register_form', 'none' );

			if ( 'both' === $show_register_form || 'login' === $show_register_form ) {
				return;
			}
			do_action( 'cl_purchase_form_login_fields' );
		}
	}
}
add_action( 'cl_purchase_form_before_register_login', 'cl_recovery_force_login_fields' );

/**
 * When processing the payment, check if the resuming payment has a user id and that it matches the logged in user.
 *
 * @since 2.7
 * @param $verified_data
 * @param $post_data
 */
function cl_recovery_verify_logged_in( $verified_data, $post_data ) {
	$resuming_payment = WPERECCP()->front->session->get( 'cl_resume_payment' );
	if ( $resuming_payment ) {
		$payment    = new Clpayment( $resuming_payment );
		$same_user  = ! empty( $payment->user_id ) && ( is_user_logged_in() && $payment->user_id == get_current_user_id() );
		$same_email = strtolower( $payment->email ) === strtolower( $post_data['cl_email'] );

		if ( ( is_user_logged_in() && ! $same_user ) || ( ! is_user_logged_in() && (int) $payment->user_id > 0 && ! $same_email ) ) {
			WPERECCP()->front->error->cl_set_error( 'recovery_requires_login', __( 'To complete this payment, please login to your account.', 'essential-wp-real-estate' ) );
		}
	}
}
add_action( 'cl_checkout_error_checks', 'cl_recovery_verify_logged_in', 10, 2 );
