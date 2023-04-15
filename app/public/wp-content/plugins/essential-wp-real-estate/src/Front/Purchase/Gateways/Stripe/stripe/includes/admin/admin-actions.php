<?php
use Essential\Restate\Front\Purchase\Payments\Clpayment;

function cls_process_preapproved_charge() {
	if ( empty( $_GET['nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['nonce'], 'cls-process-preapproval' ) ) {
		return;
	}

	$payment_id = absint( cl_sanitization( $_GET['payment_id'] ) );
	$charge     = cls_charge_preapproved( $payment_id );

	if ( $charge ) {
		wp_redirect( esc_url_raw( add_query_arg( array( 'cl-message' => 'preapproval-charged' ), admin_url( 'edit.php?post_type=listing&page=cl-payment-history' ) ) ) );
		exit;
	} else {
		wp_redirect( esc_url_raw( add_query_arg( array( 'cl-message' => 'preapproval-failed' ), admin_url( 'edit.php?post_type=listing&page=cl-payment-history' ) ) ) );
		exit;
	}
}
add_action( 'cl_charge_stripe_preapproval', 'cls_process_preapproved_charge' );


/**
 * Cancel a preapproved payment
 *
 * @since 1.6
 * @return void
 */
function cls_process_preapproved_cancel() {
	 global $cl_options;

	if ( empty( $_GET['nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['nonce'], 'cls-process-preapproval' ) ) {
		return;
	}

	$payment_id = absint( cl_sanitization( $_GET['payment_id'] ) );

	if ( empty( $payment_id ) ) {
		return;
	}

	$payment     = cl_get_payment( $payment_id );
	$customer_id = $payment->get_meta( '_cls_stripe_customer_id', true );
	$status      = $payment->status;

	if ( empty( $customer_id ) ) {
		return;
	}

	if ( 'preapproval' !== $status ) {
		return;
	}

	cl_insert_payment_note( $payment_id, __( 'Preapproval cancelled', 'essential-wp-real-estate' ) );
	cl_update_payment_status( $payment_id, 'cancelled' );
	$payment->delete_meta( '_cls_stripe_customer_id' );

	wp_redirect( esc_url_raw( add_query_arg( array( 'cl-message' => 'preapproval-cancelled' ), admin_url( 'edit.php?post_type=listing&page=cl-payment-history' ) ) ) );
	exit;
}
add_action( 'cl_cancel_stripe_preapproval', 'cls_process_preapproved_cancel' );

/**
 * Admin Messages
 *
 * @since 1.6
 * @return void
 */
function cls_admin_messages() {
	if ( isset( $_GET['cl-message'] ) && 'preapproval-charged' == $_GET['cl-message'] ) {
		add_settings_error( 'cls-notices', 'cls-preapproval-charged', __( 'The preapproved payment was successfully charged.', 'essential-wp-real-estate' ), 'updated' );
	}
	if ( isset( $_GET['cl-message'] ) && 'preapproval-failed' == $_GET['cl-message'] ) {
		add_settings_error( 'cls-notices', 'cls-preapproval-charged', __( 'The preapproved payment failed to be charged. View order details for further details.', 'essential-wp-real-estate' ), 'error' );
	}
	if ( isset( $_GET['cl-message'] ) && 'preapproval-cancelled' == $_GET['cl-message'] ) {
		add_settings_error( 'cls-notices', 'cls-preapproval-cancelled', __( 'The preapproved payment was successfully cancelled.', 'essential-wp-real-estate' ), 'updated' );
	}

	if ( isset( $_GET['cl_gateway_connect_error'], $_GET['cl-message'] ) ) {
		/* translators: %1$s Stripe Connect error message. %2$s Retry URL. */
		echo '<div class="notice notice-error"><p>' . sprintf( __( 'There was an error connecting your Stripe account. Message: %1$s. Please <a href="%2$s">try again</a>.', 'essential-wp-real-estate' ), urldecode( cl_sanitization( $_GET['cl-message'] ) ), esc_url( admin_url( 'edit.php?post_type=listing&page=cl-settings&tab=gateways&section=cl-stripe' ) ) ) . '</p></div>';
		add_filter(
			'wp_parse_str',
			function ( $ar ) {
				if ( isset( $ar['cl_gateway_connect_error'] ) ) {
					unset( $ar['cl_gateway_connect_error'] );
				}

				if ( isset( $ar['cl-message'] ) ) {
					unset( $ar['cl-message'] );
				}
				return $ar;
			}
		);
	}

	settings_errors( 'cls-notices' );
}
add_action( 'admin_notices', 'cls_admin_messages' );

/**
 * Add payment meta item to payments that used an existing card
 *
 * @since 2.6
 * @param $payment_id
 * @return void
 */
function cls_show_existing_card_meta( $payment_id ) {
	$payment       = new Clpayment( $payment_id );
	$existing_card = $payment->get_meta( '_cls_used_existing_card' );
	if ( ! empty( $existing_card ) ) {
		?>
		<div class="cl-order-stripe-existing-card cl-admin-box-inside">
			<p>
				<span class="label"><?php _e( 'Used Existing Card:', 'essential-wp-real-estate' ); ?></span>&nbsp;
				<span><?php _e( 'Yes', 'essential-wp-real-estate' ); ?></span>
			</p>
		</div>
		<?php
	}
}
add_action( 'cl_view_order_details_payment_meta_after', 'cls_show_existing_card_meta', 10, 1 );

/**
 * Handles redirects to the Stripe settings page under certain conditions.
 *
 * @since 2.6.14
 */
function cls_stripe_connect_test_mode_toggle_redirect() {
	// Check for our marker
	if ( ! isset( $_POST['cl-test-mode-toggled'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( false === cls_is_gateway_active() ) {
		return;
	}

	/**
	 * Filter the redirect that happens when options are saved and
	 * add query args to redirect to the Stripe settings page
	 * and to show a notice about connecting with Stripe.
	 */
	add_filter(
		'wp_redirect',
		function ( $location ) {
			if ( false !== strpos( $location, 'page=cl-settings' ) && false !== strpos( $location, 'settings-updated=true' ) ) {
				$location = add_query_arg(
					array(
						'cl-message' => 'connect-to-stripe',
					),
					$location
				);
			}
			return $location;
		}
	);
}
add_action( 'admin_init', 'cls_stripe_connect_test_mode_toggle_redirect' );

/**
 * Adds a "Refund Charge in Stripe" checkbox to the refund UI.
 *
 * @since 2.8.7
 */
function cls_show_refund_checkbox( $order ) {
	if ( 'stripe' !== $order->gateway ) {
		return;
	}
	?>
	<div class="cl-form-group cl-stripe-refund-transaction">
		<div class="cl-form-group__control">
			<input type="checkbox" id="cl-stripe-refund" name="cl-stripe-refund" class="cl-form-group__input" value="1">
			<label for="cl-stripe-refund" class="cl-form-group__label">
				<?php esc_html_e( 'Refund Charge in Stripe', 'essential-wp-real-estate' ); ?>
			</label>
		</div>
	</div>
	<?php
}
add_action( 'cl_after_submit_refund_table', 'cls_show_refund_checkbox' );
