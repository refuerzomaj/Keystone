<?php
/**
 * Payment Request Button: Checkout
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Registers "Express" (via Apple Pay/Google Pay) gateway.
 *
 * @since 2.8.0
 *
 * @param array $gateways Registered payment gateways.
 * @return array
 */
function cls_prb_shim_gateways( $gateways ) {
	// Do nothing in admin.
	if ( is_admin() ) {
		return $gateways;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	remove_filter( 'cl_payment_gateways', 'cls_prb_shim_gateways' );
	remove_filter( 'cl_enabled_payment_gateways', 'cls_prb_shim_gateways' );

	$enabled = true;

	// Do nothing if Payment Requests are not enabled.
	if ( false === cls_prb_is_enabled( 'checkout' ) ) {
		$enabled = false;
	}

	// Track default gateway so we can resort the list.
	$default_gateway_id = WPERECCP()->common->options->cl_get_default_gateway();

	if ( 'stripe-prb' === $default_gateway_id ) {
		$default_gateway_id = 'stripe';
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	add_filter( 'cl_payment_gateways', 'cls_prb_shim_gateways' );
	add_filter( 'cl_enabled_payment_gateways', 'cls_prb_shim_gateways' );

	if ( false === $enabled ) {
		return $gateways;
	}

	// Ensure default gateway is considered registered at this point.
	if ( isset( $gateways[ $default_gateway_id ] ) ) {
		$default_gateway = array(
			$default_gateway_id => $gateways[ $default_gateway_id ],
		);

		// Fall back to first gateway in the list.
	} else {
		if ( function_exists( 'array_key_first' ) ) {
			$first_gateway_id = array_key_first( $gateways );
		} else {
			$gateway_keys     = array_keys( $gateways );
			$first_gateway_id = reset( $gateway_keys );
		}
		$default_gateway = array(
			$first_gateway_id => $gateways[ $first_gateway_id ],
		);
	}

	unset( $gateways[ $default_gateway_id ] );

	return array_merge(
		array(
			'stripe-prb' => array(
				'admin_label'    => __( 'Express Checkout (Apple Pay/Google Pay)', 'essential-wp-real-estate' ),
				'checkout_label' => __( 'Express Checkout', 'essential-wp-real-estate' ),
				'supports'       => array(),
			),
		),
		$default_gateway,
		$gateways
	);
}
add_filter( 'cl_payment_gateways', 'cls_prb_shim_gateways' );
add_filter( 'cl_enabled_payment_gateways', 'cls_prb_shim_gateways' );

/**
 * Enables the shimmed `stripe-prb` gateway.
 *
 * @since 2.8.0
 *
 * @param array $gateways Enabled payment gateways.
 * @return array
 */
function cls_prb_enable_shim_gateway( $gateways ) {
	// Do nothing in admin.
	if ( is_admin() ) {
		return $gateways;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	remove_filter( 'cl_admin_get_option_gateways', 'cls_prb_enable_shim_gateway' );

	$enabled = true;

	// Do nothing if Payment Requests are not enabled.
	if ( false === cls_prb_is_enabled( 'checkout' ) ) {
		$enabled = false;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	add_filter( 'cl_admin_get_option_gateways', 'cls_prb_enable_shim_gateway' );

	if ( false === $enabled ) {
		return $gateways;
	}

	$gateways['stripe-prb'] = 1;

	return $gateways;
}
add_filter( 'cl_admin_get_option_gateways', 'cls_prb_enable_shim_gateway' );

/**
 * Ensures the base `stripe` gateway is used as an ID _only_ when generating
 * the hidden `input[name="cl-gateway"]` field.
 *
 * @since 2.8.0
 */
function cls_prb_shim_active_gateways() {
	add_filter( 'cl_chosen_gateway', 'cls_prb_set_base_gateway' );
	add_filter( 'cls_is_gateway_active', 'cls_prb_is_gateway_active', 10, 2 );
}
add_action( 'cl_purchase_form_before_submit', 'cls_prb_shim_active_gateways' );

/**
 * Removes conversion of `stripe-prb` to `stripe` after the `input[name="cl-gateway"]`
 * hidden input is generated.
 *
 * @since 2.8.0
 */
function cls_prb_unshim_active_gateways() {
	 remove_filter( 'cl_chosen_gateway', 'cls_prb_set_base_gateway' );
	remove_filter( 'cls_is_gateway_active', 'cls_prb_is_gateway_active', 10, 2 );
}
add_action( 'cl_purchase_form_after_submit', 'cls_prb_unshim_active_gateways' );

/**
 * Ensures the "Express Checkout" gateway is considered active if the setting
 * is enabled.
 *
 * @since 2.8.0
 *
 * @param bool   $active Determines if the gateway is considered active.
 * @param string $gateway The gateway ID to check.
 * @return bool
 */
function cls_prb_is_gateway_active( $active, $gateway ) {
	remove_filter( 'cls_is_gateway_active', 'cls_prb_is_gateway_active', 10, 2 );

	if (
		'stripe-prb' === $gateway &&
		true === cls_prb_is_enabled( 'checkout' )
	) {
		$active = true;
	}

	add_filter( 'cls_is_gateway_active', 'cls_prb_is_gateway_active', 10, 2 );

	return $active;
}

/**
 * Transforms the found active `stripe-prb` Express Checkout gateway back
 * to the base `stripe` gateway ID.
 *
 * @param string $gateway Chosen payment gateway.
 * @return string
 */
function cls_prb_set_base_gateway( $gateway ) {
	if ( 'stripe-prb' === $gateway ) {
		$gateway = 'stripe';
	}

	return $gateway;
}

/**
 * Filters the default gateway.
 *
 * Sets the Payment Request Button (Express Checkout) as default
 * when enabled for the context.
 *
 * @since 2.8.0
 *
 * @param string $default Default gateway.
 * @return string
 */
function cls_prb_default_gateway( $default ) {
	// Do nothing in admin.
	if ( is_admin() ) {
		return $default;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	remove_filter( 'cl_default_gateway', 'cls_prb_default_gateway' );

	$enabled = true;

	// Do nothing if Payment Requests are not enabled.
	if ( false === cls_prb_is_enabled( 'checkout' ) ) {
		$enabled = false;
	}

	// Avoid endless loops when checking if the Stripe gateway is active.
	add_filter( 'cl_default_gateway', 'cls_prb_default_gateway' );

	if ( false === $enabled ) {
		return $default;
	}

	return 'stripe' === $default
		? 'stripe-prb'
		: $default;
}
add_filter( 'cl_default_gateway', 'cls_prb_default_gateway' );

/**
 * Adds Payment Request-specific overrides when processing a single listing.
 *
 * Disables all required fields.
 *
 * @since 2.8.0
 */
function cls_prb_process_overrides() {
	if ( ! isset( $_POST ) ) {
		return;
	}

	if ( ! isset( $_POST['cls-gateway'] ) ) {
		return;
	}

	if ( 'payment-request' !== $_POST['cls-gateway'] ) {
		return;
	}

	if ( 'listing' !== $_POST['cls-prb-context'] ) {
		return;
	}

	// Ensure Billing Address and Name Fields are not required.
	add_filter( 'cl_require_billing_address', '__return_false' );

	// Require email address.
	add_filter( 'cl_purchase_form_required_fields', 'cls_prb_purchase_form_required_fields', 9999 );

	// Remove 3rd party validations.
	remove_all_actions( 'cl_checkout_error_checks' );
	remove_all_actions( 'cl_checkout_user_error_checks' );
}
add_action( 'cl_pre_process_purchase', 'cls_prb_process_overrides' );

/**
 * Filters the purchase form's required field to only
 * require an email address.
 *
 * @since 2.8.0
 *
 * @return array
 */
function cls_prb_purchase_form_required_fields() {
	return array(
		'cl_email' => array(
			'error_id'      => 'invalid_email',
			'error_message' => __( 'Please enter a valid email address', 'essential-wp-real-estate' ),
		),
	);
}

/**
 * Adds a note and metadata to Payments made with a Payment Request Button.
 *
 * @since 2.8.0
 *
 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent Created Stripe Intent.
 */
function cls_prb_payment_created( $payment, $intent ) {
	if ( false === isset( $intent['metadata']['cls_prb'] ) ) {
		return;
	}

	$payment->update_meta( '_cls_stripe_prb', 1 );
	$payment->add_note( 'Purchase completed with Express Checkout (Apple Pay/Google Pay)' );
}
add_action( 'cls_payment_created', 'cls_prb_payment_created', 10, 2 );

/**
 * Creates an empty Credit Card form to ensure core actions are still called.
 *
 * @since 2.8.0
 */
function cls_prb_cc_form() {
	do_action( 'cl_before_cc_fields' );
	do_action( 'cl_after_cc_fields' );
}

/**
 * Loads the Payment Request gateway.
 *
 * This fires before core's callbacks to avoid firing additional
 * actions (and therefore creating extra output) when using the Payment Request.
 *
 * @since 2.8.0
 */
function cls_prb_load_gateway() {
	if ( ! isset( $_POST['nonce'] ) ) {
		cl_debug_log(
			__( 'Missing nonce when loading the gateway fields. Please read the following for more information.', 'essential-wp-real-estate' ),
			true
		);
	}

	if ( isset( $_POST['cl_payment_mode'] ) && isset( $_POST['nonce'] ) ) {
		$payment_mode = cl_sanitization( $_POST['cl_payment_mode'] );
		$nonce        = cl_sanitization( $_POST['nonce'] );

		$nonce_verified = wp_verify_nonce( $nonce, 'cl-gateway-selected-' . $payment_mode );

		if ( false !== $nonce_verified ) {
			// Load the "Express" gateway.
			if ( 'stripe-prb' === $payment_mode ) {
				// Remove credit card fields.
				remove_action( 'cl_stripe_cc_form', 'cls_credit_card_form' );
				remove_action( 'cl_cc_form', 'cl_get_cc_form' );

				// Hide "Billing Details" which are populated by the Payment Method.
				add_filter( 'cl_require_billing_address', '__return_true' );
				remove_filter( 'cl_purchase_form_required_fields', 'cl_stripe_require_zip_and_country' );

				remove_action( 'cl_after_cc_fields', 'cl_stripe_zip_and_country', 9 );
				remove_action( 'cl_after_cc_fields', 'cl_default_cc_address_fields', 10 );

				// Remove "Update billing address" checkbox. All Payment Requests create
				// a new source.
				remove_action( 'cl_cc_billing_top', 'cl_stripe_update_billing_address_field', 10 );

				// Output a Payment Request-specific credit card form (empty).
				add_action( 'cl_stripe_cc_form', 'cls_prb_cc_form' );

				// Swap purchase button with Payment Request button.
				add_filter( 'cl_checkout_button_purchase', 'cls_prb_checkout_button_purchase', 10000 );

				/**
				 * Allows further adjustments to made before the "Express Checkout"
				 * gateway is loaded.
				 *
				 * @since 2.8.0
				 */
				do_action( 'cls_prb_before_purchase_form' );
			}

			do_action( 'cl_purchase_form' );

			// Ensure core callbacks are fired.
			add_action( 'wp_ajax_cl_load_gateway', 'cl_load_ajax_gateway' );
			add_action( 'wp_ajax_nopriv_cl_load_gateway', 'cl_load_ajax_gateway' );
		}

		exit();
	}
}
add_action( 'wp_ajax_cl_load_gateway', 'cls_prb_load_gateway', 5 );
add_action( 'wp_ajax_nopriv_cl_load_gateway', 'cls_prb_load_gateway', 5 );









function cl_get_cc_form() {
	 ob_start(); ?>

	<?php do_action( 'cl_before_cc_fields' ); ?>

	<fieldset id="cl_cc_fields" class="cl-do-validate">
		<legend><?php _e( 'Credit Card Info', 'essential-wp-real-estate' ); ?></legend>
		<?php if ( is_ssl() ) : ?>
			<div id="cl_secure_site_wrapper">
				<span class="padlock">
					<svg class="cl-icon cl-icon-lock" xmlns="http://www.w3.org/2000/svg" width="18" height="28" viewBox="0 0 18 28" aria-hidden="true">
						<path d="M5 12h8V9c0-2.203-1.797-4-4-4S5 6.797 5 9v3zm13 1.5v9c0 .828-.672 1.5-1.5 1.5h-15C.672 24 0 23.328 0 22.5v-9c0-.828.672-1.5 1.5-1.5H2V9c0-3.844 3.156-7 7-7s7 3.156 7 7v3h.5c.828 0 1.5.672 1.5 1.5z" />
					</svg>
				</span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'essential-wp-real-estate' ); ?></span>
			</div>
		<?php endif; ?>
		<p id="cl-card-number-wrap">
			<label for="card_number" class="cl-label">
				<?php _e( 'Card Number', 'essential-wp-real-estate' ); ?>
				<span class="cl-required-indicator">*</span>
				<span class="card-type"></span>
			</label>
			<span class="cl-description"><?php _e( 'The (typically) 16 digits on the front of your credit card.', 'essential-wp-real-estate' ); ?></span>
			<input type="tel" pattern="^[0-9!@#$%^&* ]*$" autocomplete="off" name="card_number" id="card_number" class="card-number cl-input required" placeholder="<?php _e( 'Card number', 'essential-wp-real-estate' ); ?>" />
		</p>
		<p id="cl-card-cvc-wrap">
			<label for="card_cvc" class="cl-label">
				<?php _e( 'CVC', 'essential-wp-real-estate' ); ?>
				<span class="cl-required-indicator">*</span>
			</label>
			<span class="cl-description"><?php _e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'essential-wp-real-estate' ); ?></span>
			<input type="tel" pattern="[0-9]{3,4}" size="4" maxlength="4" autocomplete="off" name="card_cvc" id="card_cvc" class="card-cvc cl-input required" placeholder="<?php _e( 'Security code', 'essential-wp-real-estate' ); ?>" />
		</p>
		<p id="cl-card-name-wrap">
			<label for="card_name" class="cl-label">
				<?php _e( 'Name on the Card', 'essential-wp-real-estate' ); ?>
				<span class="cl-required-indicator">*</span>
			</label>
			<span class="cl-description"><?php _e( 'The name printed on the front of your credit card.', 'essential-wp-real-estate' ); ?></span>
			<input type="text" autocomplete="off" name="card_name" id="card_name" class="card-name cl-input required" placeholder="<?php _e( 'Card name', 'essential-wp-real-estate' ); ?>" />
		</p>
		<?php do_action( 'cl_before_cc_expiration' ); ?>
		<p class="card-expiration">
			<label for="card_exp_month" class="cl-label">
				<?php _e( 'Expiration (MM/YY)', 'essential-wp-real-estate' ); ?>
				<span class="cl-required-indicator">*</span>
			</label>
			<span class="cl-description"><?php _e( 'The date your credit card expires, typically on the front of the card.', 'essential-wp-real-estate' ); ?></span>
			<select id="card_exp_month" name="card_exp_month" class="card-expiry-month cl-select cl-select-small required">
				<?php
				for ( $i = 1; $i <= 12; $i++ ) {
					echo '<option value="' . esc_attr( $i ) . '">' . sprintf( '%02d', $i ) . '</option>';
				}
				?>
			</select>
			<span class="exp-divider"> / </span>
			<select id="card_exp_year" name="card_exp_year" class="card-expiry-year cl-select cl-select-small required">
				<?php
				for ( $i = date( 'Y' ); $i <= date( 'Y' ) + 30; $i++ ) {
					echo '<option value="' . esc_attr( $i ) . '">' . substr( $i, 2 ) . '</option>';
				}
				?>
			</select>
		</p>
		<?php do_action( 'cl_after_cc_expiration' ); ?>

	</fieldset>
	<?php
	do_action( 'cl_after_cc_fields' );

	echo ob_get_clean();
}
add_action( 'cl_cc_form', 'cl_get_cc_form' );
