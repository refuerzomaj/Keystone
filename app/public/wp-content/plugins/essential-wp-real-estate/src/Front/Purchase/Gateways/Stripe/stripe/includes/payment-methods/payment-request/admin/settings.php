<?php
/**
 * Payment Request Button: Settings
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Adds settings to the Stripe subtab.
 *
 * @since 2.8.0
 *
 * @param array $settings Gateway settings.
 * @return array Filtered gateway settings.
 */
function cls_prb_add_settings( $settings ) {
	// Prevent adding the extra settings if the requirements are not met.
	// The `cl_settings_gateways` filter runs regardless of the short circuit
	// inside of `cls_add_settings()`
	if (
		false === cls_has_met_requirements( 'php' ) &&
		false === cls_is_pro()
	) {
		return $settings;
	}

	if ( true === WPERECCP()->front->tax->cl_use_taxes() ) {
		$prb_settings = array(
			array(
				'id'   => 'stripe_prb_taxes',
				'name' => __( 'Apple Pay/Google Pay', 'essential-wp-real-estate' ),
				'type' => 'cls_stripe_prb_taxes',
			),
		);
	} else {
		$prb_settings = array(
			array(
				'id'      => 'stripe_prb',
				'name'    => __( 'Apple Pay/Google Pay', 'essential-wp-real-estate' ),
				'desc'    => wp_kses(
					( sprintf(
						/* translators: %1$s Opening anchor tag, do not translate. %2$s Opening anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
						__( '"Express Checkout" via Apple Pay, Google Pay, or Microsoft Pay digital wallets. By using Apple Pay, you agree to %1$sStripe%3$s and %2$sApple\'s%3$s terms of service.', 'essential-wp-real-estate' ),
						'<a href="https://stripe.com/apple-pay/legal" target="_blank" rel="noopener noreferrer">',
						'<a href="https://developer.apple.com/apple-pay/acceptable-use-guidelines-for-websites/" target="_blank" rel="noopener noreferrer">',
						'</a>'
					) . ( cl_is_test_mode()
						? '<br /><strong>' . __( 'Apple Pay is not available in Test Mode.', 'essential-wp-real-estate' ) . '</strong> ' . sprintf(
							/* translators: %1$s Opening anchor tag, do not translate. %2$s Opening anchor tag, do not translate. */
							__( 'See our %1$sdocumentation%2$s for more information.', 'essential-wp-real-estate' ),
							'<a href="' . esc_url( cls_documentation_route( 'stripe-express-checkout' ) ) . '" target="_blank" rel="noopener noreferrer">',
							'</a>'
						)
						: ''
					)
					),
					array(
						'br'     => true,
						'strong' => true,
						'a'      => array(
							'href'   => true,
							'target' => true,
							'rel'    => true,
						),
					)
				),
				'type'    => 'multicheck',
				'options' => array(
					/** translators: %s listing noun */
					'single'   => sprintf(
						__( 'Single %s', 'essential-wp-real-estate' ),
						cl_get_label_singular()
					),
					/** translators: %s listing noun */
					'archive'  => sprintf(
						__( '%s Archive (includes <code>[listings]</code> shortcode)', 'essential-wp-real-estate' ),
						cl_get_label_singular()
					),
					'checkout' => __( 'Checkout', 'essential-wp-real-estate' ),
				),
			),
		);
	}

	$position = array_search(
		'stripe_statement_descriptor',
		array_values( wp_list_pluck( $settings['cl-stripe'], 'id' ) ),
		true
	);

	$settings['cl-stripe'] = array_merge(
		array_slice( $settings['cl-stripe'], 0, $position + 1 ),
		$prb_settings,
		array_slice( $settings['cl-stripe'], $position + 1 )
	);

	return $settings;
}
add_filter( 'cl_settings_gateways', 'cls_prb_add_settings', 20 );

/**
 * Removes multicheck options and outputs a message about "Express Checkout" incompatibility with taxes.
 *
 * @since 2.8.7
 */
function cl_cls_stripe_prb_taxes_callback() {
	echo esc_html__(
		'This feature is not available when taxes are enabled.',
		'essential-wp-real-estate'
	) . ' ';

	echo wp_kses(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__(
				'See the %1$sExpress Checkout documentation%2$s for more information.',
				'essential-wp-real-estate'
			),
			'<a href="' . esc_url( cls_documentation_route( 'stripe-express-checkout' ) ) . '#cls-prb-faqs" target="_blank" rel="noopener noreferrer">',
			'</a>'
		),
		array(
			'a' => array(
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
		)
	);
}

/**
 * Force "Payment Request Buttons" to be disabled if taxes are enabled.
 *
 * @since 2.8.0
 *
 * @param mixed  $value Setting value.
 * @param string $key Setting key.
 * @return string Setting value.
 */
function cls_prb_sanitize_setting( $value, $key ) {
	if ( 'stripe_prb' === $key && WPERECCP()->front->tax->cl_use_taxes() ) {
		$value = array();
	}

	return $value;
}
add_filter( 'cl_settings_sanitize_multicheck', 'cls_prb_sanitize_setting', 10, 2 );
