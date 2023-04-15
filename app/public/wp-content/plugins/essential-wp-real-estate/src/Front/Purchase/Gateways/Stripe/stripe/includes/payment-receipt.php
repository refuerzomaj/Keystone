<?php
/**
 * Payment receipt.
 *
 * @package CL_Stripe
 * @since   2.7.0
 */

/**
 * Output a Payment authorization form in the Payment Receipt.
 *
 * @param WP_Post $payment Payment.
 */
function cls_payment_receipt_authorize_payment_form( $payment ) {
	if ( is_a( $payment, 'WP_Post' ) ) {
		$payment = cl_get_payment( $payment->ID );
	}

	$customer_id       = $payment->get_meta( '_cls_stripe_customer_id' );
	$payment_intent_id = $payment->get_meta( '_cls_stripe_payment_intent_id' );

	if ( empty( $customer_id ) || empty( $payment_intent_id ) ) {
		return false;
	}

	if ( 'preapproval_pending' !== $payment->status ) {
		return false;
	}

	$payment_intent = cls_api_request( 'PaymentIntent', 'retrieve', $payment_intent_id );

	// Enqueue core scripts.
	add_filter( 'cl_is_checkout', '__return_true' );

	cl_load_scripts();

	remove_filter( 'cl_is_checkout', '__return_true' );

	cl_stripe_js( true );
	cl_stripe_css( true );
	?>

	<form id="cls-update-payment-method" data-payment-intent="<?php echo esc_attr( $payment_intent->id ); ?>" 
																		 <?php
																			if ( isset( $payment_intent->last_payment_error ) && isset( $payment_intent->last_payment_error->payment_method ) ) :
																				?>
		 data-payment-method="<?php echo esc_attr( $payment_intent->last_payment_error->payment_method->id ); ?>" <?php endif; ?>>
		<h3>Authorize Payment</h3>
		<p><?php esc_html_e( 'To finalize your preapproved purchase, please confirm your payment method.', 'essential-wp-real-estate' ); ?></p>

		<div id="cl_checkout_form_wrap">
			<?php
			do_action( 'cl_stripe_cc_form' );
			?>
			<p>
				<input id="cls-update-payment-method-submit" type="submit" data-loading="<?php echo esc_attr( 'Please Wait…', 'cls' ); ?>" data-submit="<?php echo esc_attr( 'Authorize Payment', 'cls' ); ?>" value="<?php echo esc_attr( 'Authorize Payment', 'cls' ); ?>" class="button cl-button" />
			</p>
			<div id="cls-update-payment-method-errors"></div>
			<?php
			wp_nonce_field(
				'cls-complete-payment-authorization',
				'cls-complete-payment-authorization'
			);
			?>
		</div>
	</form>
	<?php
}
add_action( 'cl_payment_receipt_after_table', 'cls_payment_receipt_authorize_payment_form' );
