<?php
/**
 * Payment emails.
 *
 * @package CL_Stripe
 * @since   2.7.0
 */

/**
 * Notify a customer that a Payment needs further action.
 *
 * @since 2.7.0
 *
 * @param int $payment_id Payment ID.
 */
function cls_preapproved_payment_needs_action_notification( $payment_id ) {
	$payment      = cl_get_payment( $payment_id );
	$payment_data = $payment->get_meta( '_cl_payment_meta', true );

	$from_name  = cl_admin_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name  = apply_filters( 'cl_purchase_from_name', $from_name, $payment_id, $payment_data );
	$from_email = cl_admin_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email = apply_filters( 'cl_purchase_from_address', $from_email, $payment_id, $payment_data );

	if ( empty( $to_email ) ) {
		$to_email = $payment->email;
	}

	$subject = esc_html__( 'Your Preapproved Payment Requires Action', 'essential-wp-real-estate' );
	$heading = WPERECCP()->common->emailtags->cl_do_email_tags( esc_html__( 'Payment Requires Action', 'essential-wp-real-estate' ), $payment_id );

	$message  = esc_html__( 'Dear {name},', 'essential-wp-real-estate' ) . "\n\n";
	$message .= esc_html__( 'Your preapproved payment requires further action before your purchase can be completed. Please click the link below to take finalize your purchase', 'essential-wp-real-estate' ) . "\n\n";
	$message .= esc_url( add_query_arg( 'payment_key', $payment->key, WPERECCP()->front->checkout->cl_get_success_page_uri() ) );
	$message  = WPERECCP()->common->emailtags->cl_do_email_tags( $message, $payment_id );

	$message = apply_filters( 'cl_email_template_wpautop', true ) ? wpautop( $message ) : $message;

	$emails = WPERECCP()->common->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$headers = $emails->get_headers();
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message );
}
add_action( 'cls_preapproved_payment_needs_action', 'cls_preapproved_payment_needs_action_notification' );
