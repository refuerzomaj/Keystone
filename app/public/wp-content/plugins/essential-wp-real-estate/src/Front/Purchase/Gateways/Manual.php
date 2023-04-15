<?php
namespace  Essential\Restate\Front\Purchase\Gateways;

use Essential\Restate\Traitval\Traitval;

class Manual {

	use Traitval;
	public function __construct() {
		add_action( 'cl_manual_cc_form', '__return_false' );
		add_action( 'cl_gateway_manual', array( $this, 'cl_manual_payment' ) );
	}

	function cl_manual_payment( $purchase_data ) {
		global $post;
		if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'cl-gateway' ) ) {
			wp_die( __( 'Nonce verification has failed', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
		}

		$payment_data = array(
			'price'        => $purchase_data['price'],
			'date'         => $purchase_data['date'],
			'user_email'   => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency'     => WPERECCP()->common->options->cl_get_currency(),
			'listing'      => $purchase_data['listing'],
			'user_info'    => $purchase_data['user_info'],
			'cart_details' => $purchase_data['cart_details'],
			'status'       => 'pending',
		);

		// Record the pending payment
		$payment = cl_insert_payment( $payment_data );

		if ( $payment ) {
			cl_update_payment_status( $payment, 'publish' );
			// Empty the shopping cart
			WPERECCP()->front->cart->cl_empty_cart();
			wp_redirect( WPERECCP()->front->checkout->cl_get_success_page_uri() );
			exit();
			WPERECCP()->front->checkout->cl_send_to_success_page();
			exit();
		} else {
			WPERECCP()->front->gateways->cl_record_gateway_error( __( 'Payment Error', 'essential-wp-real-estate' ), sprintf( __( 'Payment creation failed while processing a manual (free or test) purchase. Payment data: %s', 'essential-wp-real-estate' ), json_encode( $payment_data ) ), $payment );
			// If errors are present, send the user back to the purchase page so they can be corrected
			WPERECCP()->front->checkout->cl_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['cl-gateway'] );
		}
	}
}
