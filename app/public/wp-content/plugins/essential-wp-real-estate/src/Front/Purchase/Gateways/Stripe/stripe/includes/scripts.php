<?php
/**
 * Load our javascript
 *
 * The Stripe JS is by default, loaded on every page as suggested by Stripe. This can be overridden by using the Restrict Stripe Assets
 * setting within the admin, and the Stripe Javascript resources will only be loaded when necessary.
 *
 * @link https://stripe.com/docs/web/setup
 *
 * The custom Javascript is loaded if the page is checkout. If checkout, the function is called directly with
 * `true` set for the `$force_load_scripts` argument.
 *
 * @access      public
 * @since       1.0
 *
 * @param bool $force_load_scripts Allows registering our Javascript files on pages other than is_checkout().
 *                                 This argument allows the `cl_stripe_js` function to be called directly, outside of
 *                                 the context of checkout, such as the card management or update subscription payment method
 *                                 UIs. Sending in 'true' will ensure that the Javascript resources are enqueued when you need them.
 *
 * @return      void
 */
function cl_stripe_js( $force_load_scripts = false ) {

	if ( false === cls_is_gateway_active() ) {

		return;
	}

	if ( method_exists( WPERECCP()->front->checkout, 'cl_is_checkout' ) ) {

		$publishable_key = null;

		if ( cl_is_test_mode() ) {

			$publishable_key = cl_admin_get_option( 'test_publishable_key', '' );
		} else {

			$publishable_key = cl_admin_get_option( 'live_publishable_key', '' );
		}

		wp_register_script(
			'sandhills-stripe-js-v3',
			'https://js.stripe.com/v3/',
			array(),
			'v3'
		);

		wp_register_script(
			'cl-stripe-js',
			CLS_PLUGIN_URL . 'assets/js/build/app.min.js',
			array(
				'sandhills-stripe-js-v3',
				'jquery',
				'cl-ajax',
			),
			CL_STRIPE_VERSION,
			true
		);

		$is_checkout     = WPERECCP()->front->checkout->cl_is_checkout();
		$restrict_assets = cl_admin_get_option( 'stripe_restrict_assets', false );

		if ( $is_checkout || $force_load_scripts || false === $restrict_assets ) {
			wp_enqueue_script( 'sandhills-stripe-js-v3' );
		}

		if ( $is_checkout || $force_load_scripts ) {
			wp_enqueue_script( 'cl-stripe-js' );
			wp_enqueue_script( 'jQuery.payment' );

			$stripe_vars = apply_filters(
				'cl_stripe_js_vars',
				array(
					'publishable_key'                => trim( $publishable_key ),
					'is_ajaxed'                      => cl_is_ajax_enabled() ? 'true' : 'false',
					'currency'                       => WPERECCP()->common->options->cl_get_currency(),
					'country'                        => cl_admin_get_option( 'base_country', 'US' ),
					'locale'                         => cls_get_stripe_checkout_locale(),
					'is_zero_decimal'                => cls_is_zero_decimal_currency() ? 'true' : 'false',
					'checkout'                       => cl_admin_get_option( 'stripe_checkout' ) ? 'true' : 'false',
					'store_name'                     => get_bloginfo( 'name' ),
					'alipay'                         => cl_admin_get_option( 'stripe_alipay' ) ? 'true' : 'false',
					'submit_text'                    => cl_admin_get_option( 'stripe_checkout_button_text', __( 'Next', 'essential-wp-real-estate' ) ),
					'image'                          => cl_admin_get_option( 'stripe_checkout_image' ),
					'zipcode'                        => cl_admin_get_option( 'stripe_checkout_zip_code', false ) ? 'true' : 'false',
					'billing_address'                => cl_admin_get_option( 'stripe_checkout_billing', false ) ? 'true' : 'false',
					'remember_me'                    => cl_admin_get_option( 'stripe_checkout_remember', false ) ? 'true' : 'false',
					'no_key_error'                   => __( 'Stripe publishable key missing. Please enter your publishable key in Settings.', 'essential-wp-real-estate' ),
					'checkout_required_fields_error' => __( 'Please fill out all required fields to continue your purchase.', 'essential-wp-real-estate' ),
					'checkout_agree_to_terms'        => __( 'Please agree to the terms to complete your purchase.', 'essential-wp-real-estate' ),
					'checkout_agree_to_privacy'      => __( 'Please agree to the privacy policy to complete your purchase.', 'essential-wp-real-estate' ),
					'generic_error'                  => __( 'Unable to complete your request. Please try again.', 'essential-wp-real-estate' ),
					'successPageUri'                 => WPERECCP()->front->checkout->cl_get_success_page_uri(),
					'failurePageUri'                 => WPERECCP()->front->checkout->cl_get_failed_transaction_uri(),
					'elementsOptions'                => cls_get_stripe_elements_options(),
					'elementsSplitFields'            => '1' === cl_admin_get_option( 'stripe_split_payment_fields', false ) ? 'true' : 'false',
					'isTestMode'                     => cl_is_test_mode() ? 'true' : 'false',
					'checkoutHasPaymentRequest'      => cls_prb_is_enabled( 'checkout' ) ? 'true' : 'false',
				)
			);

			wp_localize_script( 'cl-stripe-js', 'cl_stripe_vars', $stripe_vars );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'cl_stripe_js', 100 );

function cl_stripe_css( $force_load_scripts = false ) {
	if ( false === cls_is_gateway_active() ) {
		return;
	}

	if ( cl_is_checkout() || $force_load_scripts ) {
		$deps = array( 'cl-styles' );

		if ( ! wp_script_is( 'cl-styles', 'enqueued' ) ) {
			$deps = array();
		}

		wp_register_style( 'cl-stripe', CLS_PLUGIN_URL . 'assets/css/build/app.min.css', $deps, CL_STRIPE_VERSION );
		wp_enqueue_style( 'cl-stripe' );
	}
}
add_action( 'wp_enqueue_scripts', 'cl_stripe_css', 100 );

/**
 * Load our admin javascript
 *
 * @access      public
 * @since       1.8
 * @return      void
 */
function cl_stripe_admin_js( $payment_id = 0 ) {

	if ( function_exists( 'cl_get_order' ) ) {
		return;
	}

	if ( 'stripe' !== cl_get_payment_gateway( $payment_id ) ) {
		return;
	}
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('select[name=cl-payment-status]').change(function() {

				if ('refunded' == $(this).val()) {

					// Localize refund label
					var cl_stripe_refund_charge_label = "<?php echo esc_js( __( 'Refund Charge in Stripe', 'essential-wp-real-estate' ) ); ?>";

					$(this).parent().parent().append('<input type="checkbox" id="cl_refund_in_stripe" name="cl_refund_in_stripe" value="1" style="margin-top: 0;" />');
					$(this).parent().parent().append('<label for="cl_refund_in_stripe">' + cl_stripe_refund_charge_label + '</label>');

				} else {

					$('#cl_refund_in_stripe').remove();
					$('label[for="cl_refund_in_stripe"]').remove();

				}

			});
		});
	</script>
	<?php

}
add_action( 'cl_view_order_details_before', 'cl_stripe_admin_js', 100 );

/**
 * Loads the javascript for the Stripe Connect functionality in the settings page.
 *
 * @param string $hook The current admin page.
 */
function cl_stripe_connect_admin_script( $hook ) {

	if ( 'listing_page_cl-settings' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'cl-stripe-admin-styles', CLS_PLUGIN_URL . 'assets/css/build/admin.min.css', array(), CL_STRIPE_VERSION );

	wp_enqueue_script( 'cl-stripe-admin-scripts', CLS_PLUGIN_URL . 'assets/js/build/admin.min.js', array( 'jquery' ), CL_STRIPE_VERSION );

	$test_key = cl_admin_get_option( 'test_publishable_key' );
	$live_key = cl_admin_get_option( 'live_publishable_key' );

	wp_localize_script(
		'cl-stripe-admin-scripts',
		'cl_stripe_admin',
		array(
			'stripe_enabled'  => array_key_exists( 'stripe', WPERECCP()->front->gateways->cl_get_enabled_payment_gateways() ),
			'test_mode'       => (int) cl_is_test_mode(),
			'test_key_exists' => ! empty( $test_key ) ? 'true' : 'false',
			'live_key_exists' => ! empty( $live_key ) ? 'true' : 'false',
			'ajaxurl'         => esc_url( admin_url( 'admin-ajax.php' ) ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'cl_stripe_connect_admin_script' );
