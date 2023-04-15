<?php
/**
 * Given a Payment ID, extract the transaction ID from Stripe
 *
 * @param  string $payment_id       Payment ID
 * @return string                   Transaction ID
 */
function cls_get_payment_transaction_id( $payment_id ) {

	$txn_id = '';
	$notes  = cl_get_payment_notes( $payment_id );

	foreach ( $notes as $note ) {
		if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
			$txn_id = $match[1];
			continue;
		}
	}

	return apply_filters( 'cls_set_payment_transaction_id', $txn_id, $payment_id );
}
add_filter( 'cl_get_payment_transaction_id-stripe', 'cls_get_payment_transaction_id', 10, 1 );

/**
 * Given a transaction ID, generate a link to the Stripe transaction ID details
 *
 * @since  1.9.1
 * @param  string $transaction_id The Transaction ID
 * @param  int    $payment_id     The payment ID for this transaction
 * @return string                 A link to the Stripe transaction details
 */
function cl_stripe_link_transaction_id( $transaction_id, $payment_id ) {

	$test   = cl_get_payment_meta( $payment_id, '_cl_payment_mode' ) === 'test' ? 'test/' : '';
	$status = cl_get_payment_status( $payment_id );

	if ( 'preapproval' === $status ) {
		$url = '<a href="https://dashboard.stripe.com/' . esc_attr( $test ) . 'setup_intents/' . esc_attr( $transaction_id ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';
	} else {
		$url = '<a href="https://dashboard.stripe.com/' . esc_attr( $test ) . 'payments/' . esc_attr( $transaction_id ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';
	}
	return apply_filters( 'cl_stripe_link_payment_details_transaction_id', $url );
}
add_filter( 'cl_payment_details_transaction_id-stripe', 'cl_stripe_link_transaction_id', 10, 2 );

/**
 * Show the Process / Cancel buttons for preapproved payments
 *
 * @since 1.6
 * @return string
 */
function cls_payments_column_data( $value, $payment_id, $column_name ) {
	if ( 'status' !== $column_name ) {
		return $value;
	}

	$status = cl_get_payment_status( $payment_id );
	if ( ! in_array( $status, array( 'preapproval', 'preapproval_pending' ), true ) ) {
		return $value;
	}

	if ( function_exists( 'cl_get_order_meta' ) ) {
		$customer_id = cl_get_order_meta( $payment_id, '_cls_stripe_customer_id', true );
	} else {
		$customer_id = cl_get_payment_meta( $payment_id, '_cls_stripe_customer_id', true );
	}

	if ( empty( $customer_id ) ) {
		return $value;
	}

	$nonce = wp_create_nonce( 'cls-process-preapproval' );

	$preapproval_args = array(
		'payment_id' => $payment_id,
		'nonce'      => $nonce,
		'cl-action'  => 'charge_stripe_preapproval',
	);

	$cancel_args = array(
		'preapproval_key' => $customer_id,
		'payment_id'      => $payment_id,
		'nonce'           => $nonce,
		'cl-action'       => 'cancel_stripe_preapproval',
	);

	$actions = array();

	$value .= '<p class="row-actions">';

	$actions[] = '<a href="' . esc_url( add_query_arg( $preapproval_args, admin_url( 'edit.php?post_type=listing&page=cl-payment-history' ) ) ) . '">' . __( 'Process', 'essential-wp-real-estate' ) . '</a>';

	if ( 'cancelled' !== $status ) {
		$actions[] = '<span class="delete"><a href="' . esc_url( add_query_arg( $cancel_args, admin_url( 'edit.php?post_type=listing&page=cl-payment-history' ) ) ) . '">' . __( 'Cancel', 'essential-wp-real-estate' ) . '</a></span>';
	}

	$value .= implode( ' | ', $actions );

	$value .= '</p>';

	return $value;
}
add_filter( 'cl_payments_table_column', 'cls_payments_column_data', 20, 3 );
