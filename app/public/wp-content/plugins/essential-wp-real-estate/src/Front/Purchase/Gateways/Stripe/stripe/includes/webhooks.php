<?php
/**
 * Webhooks.
 *
 * @package CL_Stripe
 * @since   2.7.0
 */

/**
 * Listen for Stripe Webhooks.
 *
 * @since 1.5
 */
function cls_stripe_event_listener() {
	if ( ! isset( $_GET['cl-listener'] ) || 'stripe' !== $_GET['cl-listener'] ) {
		return;
	}

	try {
		// Retrieve the request's body and parse it as JSON.
		$body  = @file_get_contents( 'php://input' );
		$event = json_decode( $body );

		if ( isset( $event->id ) ) {
			$event = cls_api_request( 'Event', 'retrieve', $event->id );
		} else {
			throw new \Exception( esc_html__( 'Unable to find Event', 'essential-wp-real-estate' ) );
		}

		// Handle events.
		//
		switch ( $event->type ) {
			case 'charge.succeeded':
				$charge     = $event->data->object;
				$payment_id = cl_get_purchase_id_by_transaction_id( $charge->id );
				$payment    = new Clpayment( $payment_id );

				if ( $payment && $payment->ID > 0 ) {
					$payment->address = array(
						'line1'   => $charge->billing_details->address->line1,
						'line2'   => $charge->billing_details->address->line2,
						'state'   => $charge->billing_details->address->state,
						'city'    => $charge->billing_details->address->city,
						'zip'     => $charge->billing_details->address->postal_code,
						'country' => $charge->billing_details->address->country,
					);

					$payment->save();
				}

				break;

			case 'charge.refunded':
				$charge     = $event->data->object;
				$payment_id = cl_get_purchase_id_by_transaction_id( $charge->id );
				$payment    = new Clpayment( $payment_id );

				// This is an uncaptured PaymentIntent, not a true refund.
				if ( ! $charge->captured ) {
					return;
				}

				if ( $payment && $payment->ID > 0 ) {

					// If this was completely refunded, set the status to refunded.
					if ( $charge->refunded ) {
						$payment->status = 'refunded';
						$payment->save();
						// Translators: The charge ID from Stripe that is being refunded.
						$payment->add_note( sprintf( __( 'Charge %s has been fully refunded in Stripe.', 'essential-wp-real-estate' ), $charge->id ) );

						// If this was partially refunded, don't change the status.
					} else {
						// Translators: The charge ID from Stripe that is being partially refunded.
						$payment->add_note( sprintf( __( 'Charge %s partially refunded in Stripe.', 'essential-wp-real-estate' ), $charge->id ) );
					}
				}

				break;

			// Review started.
			case 'review.opened':
				$is_live = ! cl_is_test_mode();
				$review  = $event->data->object;

				// Make sure the modes match.
				if ( $is_live !== $review->livemode ) {
					return;
				}

				$charge = $review->charge;

				// Get the charge from the PaymentIntent.
				if ( ! $charge ) {
					$payment_intent = $review->payment_intent;

					if ( ! $payment_intent ) {
						return;
					}

					$payment_intent = cls_api_request( 'PaymentIntent', 'retrieve', $payment_intent );
					$charge         = $payment_intent->charges->data[0]->id;
				}

				$payment_id = cl_get_purchase_id_by_transaction_id( $charge );
				$payment    = new Clpayment( $payment_id );

				if ( $payment && $payment->ID > 0 ) {
					$payment->add_note(
						sprintf(
						/* translators: %s Stripe Radar review opening reason. */
							__( 'Stripe Radar review opened with a reason of %s.', 'essential-wp-real-estate' ),
							$review->reason
						)
					);
					$payment->save();

					do_action( 'cl_stripe_review_opened', $review, $payment_id );
				}

				break;

			// Review closed.
			case 'review.closed':
				$is_live = ! cl_is_test_mode();
				$review  = $event->data->object;

				// Make sure the modes match
				if ( $is_live !== $review->livemode ) {
					return;
				}

				$charge = $review->charge;

				// Get the charge from the PaymentIntent.
				if ( ! $charge ) {
					$payment_intent = $review->payment_intent;

					if ( ! $payment_intent ) {
						return;
					}

					$payment_intent = cls_api_request( 'PaymentIntent', 'retrieve', $payment_intent );
					$charge         = $payment_intent->charges->data[0]->id;
				}

				$payment_id = cl_get_purchase_id_by_transaction_id( $charge );
				$payment    = new Clpayment( $payment_id );

				if ( $payment && $payment->ID > 0 ) {
					$payment->add_note(
						sprintf(
						/* translators: %s Stripe Radar review closing reason. */
							__( 'Stripe Radar review closed with a reason of %s.', 'essential-wp-real-estate' ),
							$review->reason
						)
					);
					$payment->save();

					do_action( 'cl_stripe_review_closed', $review, $payment_id );
				}

				break;
		}

		do_action( 'cls_stripe_event_' . $event->type, $event );

		// Nothing failed, mark complete.
		status_header( 200 );
		die( esc_html( 'Stripe: ' . $event->type ) );

		// Fail, allow a retry.
	} catch ( \Exception $e ) {
		status_header( 500 );
		die( '-2' );
	}
}
add_action( 'init', 'cls_stripe_event_listener' );
