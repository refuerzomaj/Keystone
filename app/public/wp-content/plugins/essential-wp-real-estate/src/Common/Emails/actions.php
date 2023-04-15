<?php
use Essential\Restate\Common\Customer\Customer;

function cl_trigger_purchase_receipt( $payment_id = 0, $payment = null, $customer = null ) {
	// Make sure we don't send a purchase receipt while editing a payment
	if ( isset( $_POST['cl-action'] ) && 'edit_payment' == $_POST['cl-action'] ) {
		return;
	}

	// Send email with secure listing link
	cl_email_purchase_receipt( $payment_id, true, '', $payment, $customer );
}
add_action( 'cl_complete_purchase', 'cl_trigger_purchase_receipt', 999, 3 );

/**
 * Resend the Email Purchase Receipt. (This can be done from the Payment History page)
 *
 * @since 1.0
 * @param array $data Payment Data
 * @return void
 */
function cl_resend_purchase_receipt( $data ) {
	$purchase_id = absint( $data['purchase_id'] );

	if ( empty( $purchase_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_shop_payments' ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	$email = ! empty( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : '';

	if ( empty( $email ) ) {
		$customer = new Customer( cl_get_payment_customer_id( $purchase_id ) );
		$email    = $customer->email;
	}
	if ( empty( $email ) ) {
		wp_die( __( 'Email Empty', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	cl_email_purchase_receipt( $purchase_id, false, $email );

	// Grab all listing of the purchase and update their file listing limits, if needed
	// This allows admins to resend purchase receipts to grant additional file listing
	$listing = cl_get_payment_meta_cart_details( $purchase_id, true );

	if ( is_array( $listing ) ) {
		foreach ( $listing as $listing ) {
			$limit = WPERECCP()->front->listingsaction->cl_get_file_listing_limit( $listing['id'] );
			if ( ! empty( $limit ) ) {
				cl_set_file_listing_limit_override( $listing['id'], $purchase_id );
			}
		}
	}
	wp_redirect(
		add_query_arg(
			array(
				'cl-message'  => 'email_sent',
				'cl-action'   => false,
				'purchase_id' => false,
			)
		)
	);
	exit;
}

add_action( 'cl_email_links', 'cl_resend_purchase_receipt' );

/**
 * Trigger the sending of a Test Email
 *
 * @since 1.5
 * @param array $data Parameters sent from Settings page
 * @return void
 */
function cl_send_test_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'cl-test-email' ) ) {
		return;
	}

	// Send a test email
	cl_email_test_purchase_receipt();

	// Remove the test email query arg
	wp_redirect( remove_query_arg( 'cl_action' ) );
	exit;
}
add_action( 'cl_send_test_email', 'cl_send_test_email' );
