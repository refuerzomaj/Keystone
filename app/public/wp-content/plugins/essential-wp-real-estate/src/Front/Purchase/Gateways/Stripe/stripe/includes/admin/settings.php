<?php
/**
 * Register our settings section
 *
 * @return array
 */
function cls_settings_section( $sections ) {
	$sections['cl-stripe'] = __( 'Stripe', 'essential-wp-real-estate' );

	return $sections;
}
add_filter( 'cl_settings_sections_gateways', 'cls_settings_section' );

/**
 * Register the gateway settings
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function cls_add_settings( $settings ) {
	// Output a placeholder setting to help promote Stripe
	// for non-Pro installs that do not meet PHP requirements.
	if (
		false === cls_has_met_requirements( 'php' ) &&
		false === cls_is_pro()
	) {
		return array_merge(
			$settings,
			array(
				'cl-stripe' => array(
					'cls-requirements-not-met' => array(
						'id'    => 'cls-requirements-not-met',
						'name'  => __( 'Unmet Requirements', 'essential-wp-real-estate' ),
						'type'  => 'stripe_requirements_not_met',
						'class' => 'cls-requirements-not-met',
					),
				),
			)
		);
	}

	$stripe_settings = array(
		'stripe_connect_button'       => array(
			'id'    => 'stripe_connect_button',
			'name'  => __( 'Connection Status', 'essential-wp-real-estate' ),
			'desc'  => cls_stripe_connect_setting_field(),
			'type'  => 'descriptive_text',
			'class' => 'cl-stripe-connect-row',
		),
		'test_publishable_key'        => array(
			'id'    => 'test_publishable_key',
			'name'  => __( 'Test Publishable Key', 'essential-wp-real-estate' ),
			'desc'  => __( 'Enter your test publishable key, found in your Stripe Account Settings', 'essential-wp-real-estate' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'cl-hidden cls-api-key-row',
		),
		'test_secret_key'             => array(
			'id'    => 'test_secret_key',
			'name'  => __( 'Test Secret Key', 'essential-wp-real-estate' ),
			'desc'  => __( 'Enter your test secret key, found in your Stripe Account Settings', 'essential-wp-real-estate' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'cl-hidden cls-api-key-row',
		),
		'live_publishable_key'        => array(
			'id'    => 'live_publishable_key',
			'name'  => __( 'Live Publishable Key', 'essential-wp-real-estate' ),
			'desc'  => __( 'Enter your live publishable key, found in your Stripe Account Settings', 'essential-wp-real-estate' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'cl-hidden cls-api-key-row',
		),
		'live_secret_key'             => array(
			'id'    => 'live_secret_key',
			'name'  => __( 'Live Secret Key', 'essential-wp-real-estate' ),
			'desc'  => __( 'Enter your live secret key, found in your Stripe Account Settings', 'essential-wp-real-estate' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'cl-hidden cls-api-key-row',
		),
		'stripe_webhook_description'  => array(
			'id'   => 'stripe_webhook_description',
			'type' => 'descriptive_text',
			'name' => __( 'Webhooks', 'essential-wp-real-estate' ),
			'desc' =>
			'<p>' . sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'In order for Stripe to function completely, you must configure your Stripe webhooks. Visit your %1$saccount dashboard%2$s to configure them. Please add a webhook endpoint for the URL below.', 'essential-wp-real-estate' ),
				'<a href="https://dashboard.stripe.com/account/webhooks" target="_blank" rel="noopener noreferrer">',
				'</a>'
			) . '</p>' .
				'<p><strong>' . sprintf(
					/* translators: %s Webhook URL. Do not translate. */
					__( 'Webhook URL: %s', 'essential-wp-real-estate' ),
					home_url( 'index.php?cl-listener=stripe' )
				) . '</strong></p>' .
				'<p>' . sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__( 'See our %1$sdocumentation%2$s for more information.', 'essential-wp-real-estate' ),
					'<a href="' . esc_url( cls_documentation_route( 'stripe-webhooks' ) ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				) . '</p>',
		),
		'stripe_billing_fields'       => array(
			'id'      => 'stripe_billing_fields',
			'name'    => __( 'Billing Address Display', 'essential-wp-real-estate' ),
			'desc'    => __( 'Select how you would like to display the billing address fields on the checkout form. <p><strong>Notes</strong>:</p><p>If taxes are enabled, this option cannot be changed from "Full address".</p><p>If set to "No address fields", you <strong>must</strong> disable "zip code verification" in your Stripe account.</p>', 'essential-wp-real-estate' ),
			'type'    => 'select',
			'options' => array(
				'full'        => __( 'Full address', 'essential-wp-real-estate' ),
				'zip_country' => __( 'Zip / Postal Code and Country only', 'essential-wp-real-estate' ),
				'none'        => __( 'No address fields', 'essential-wp-real-estate' ),
			),
			'std'     => 'full',
		),
		'stripe_statement_descriptor' => array(
			'id'   => 'stripe_statement_descriptor',
			'name' => __( 'Statement Descriptor', 'essential-wp-real-estate' ),
			'desc' => __( 'Choose how charges will appear on customer\'s credit card statements. <em>Max 22 characters</em>', 'essential-wp-real-estate' ),
			'type' => 'text',
		),
		'stripe_use_existing_cards'   => array(
			'id'   => 'stripe_use_existing_cards',
			'name' => __( 'Show Previously Used Cards', 'essential-wp-real-estate' ),
			'desc' => __( 'Provides logged in customers with a list of previous used payment methods for faster checkout.', 'essential-wp-real-estate' ),
			'type' => 'checkbox',
		),
		'stripe_allow_prepaid'        => array(
			'id'   => 'stripe_allow_prepaid',
			'name' => __( 'Prepaid Cards', 'essential-wp-real-estate' ),
			'desc' => __( 'Allow prepaid cards as valid payment method.', 'essential-wp-real-estate' ),
			'type' => 'checkbox',
		),
		'stripe_split_payment_fields' => array(
			'id'   => 'stripe_split_payment_fields',
			'name' => __( 'Split Credit Card Form', 'essential-wp-real-estate' ),
			'desc' => __( 'Use separate card number, expiration, and CVC fields in payment forms.', 'essential-wp-real-estate' ),
			'type' => 'checkbox',
		),
		'stripe_restrict_assets'      => array(
			'id'            => 'stripe_restrict_assets',
			'name'          => ( __( 'Restrict Stripe Assets', 'essential-wp-real-estate' ) ),
			'desc'          => ( __( 'Only load Stripe.com hosted assets on pages that specifically utilize Stripe functionality.', 'essential-wp-real-estate' ) ),
			'type'          => 'checkbox',
			'tooltip_title' => __( 'Loading Javascript from Stripe', 'essential-wp-real-estate' ),
			'tooltip_desc'  => __( 'Stripe advises that their Javascript library be loaded on every page to take advantage of their advanced fraud detection rules. If you are not concerned with this, enable this setting to only load the Javascript when necessary. Read more about Stripe\'s recommended setup here: https://stripe.com/docs/web/setup.', 'essential-wp-real-estate' ),
		),
	);

	if ( cl_admin_get_option( 'stripe_checkout' ) ) {
		$stripe_settings['stripe_checkout'] = array(
			'id'   => 'stripe_checkout',
			'name' => '<strong>' . __( 'Stripe Checkout', 'essential-wp-real-estate' ) . '</strong>',
			'type' => 'stripe_checkout_notice',
			'desc' => wp_kses(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					esc_html__( 'To ensure your website is compliant with the new %1$sStrong Customer Authentication%2$s (SCA) regulations, the legacy Stripe Checkout modal is no longer supported. Payments are still securely accepted through through Stripe on the standard Property Listing Plugin checkout page. "Buy Now" buttons will also automatically redirect to the standard checkout page.', 'essential-wp-real-estate' ),
					'<a href="https://stripe.com/en-ca/guides/strong-customer-authentication" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				array(
					'a' => array(
						'href'   => true,
						'rel'    => true,
						'target' => true,
					),
				)
			),
		);
	}

	if ( version_compare( VERSION, 2.5, '>=' ) ) {
		$stripe_settings = array( 'cl-stripe' => $stripe_settings );

		// Set up the new setting field for the Test Mode toggle notice
		$notice = array(
			'stripe_connect_test_mode_toggle_notice' => array(
				'id'          => 'stripe_connect_test_mode_toggle_notice',
				'desc'        => '<p>' . __( 'You have disabled the "Test Mode" option. Once you have saved your changes, please verify your Stripe connection, especially if you have not previously connected in with "Test Mode" disabled.', 'essential-wp-real-estate' ) . '</p>',
				'type'        => 'stripe_connect_notice',
				'field_class' => 'cl-hidden',
			),
		);

		// Insert the new setting after the Test Mode checkbox
		$position = array_search( 'test_mode', array_keys( $settings['main'] ), true );
		$settings = array_merge(
			array_slice( $settings['main'], $position, 1, true ),
			$notice,
			$settings
		);
	}

	return array_merge( $settings, $stripe_settings );
}
add_filter( 'cl_settings_gateways', 'cls_add_settings' );

/**
 * Force full billing address display when taxes are enabled
 *
 * @access      public
 * @since       2.5
 * @return      string
 */
function cl_stripe_sanitize_stripe_billing_fields_save( $value, $key ) {

	if ( 'stripe_billing_fields' == $key && WPERECCP()->front->tax->cl_use_taxes() ) {

		$value = 'full';
	}

	return $value;
}
add_filter( 'cl_settings_sanitize_select', 'cl_stripe_sanitize_stripe_billing_fields_save', 10, 2 );

/**
 * Filter the output of the statement descriptor option to add a max length to the text string
 *
 * @since 2.6
 * @param $html string The full html for the setting output
 * @param $args array  The original arguments passed in to output the html
 *
 * @return string
 */
function cl_stripe_max_length_statement_descriptor( $html, $args ) {
	if ( 'stripe_statement_descriptor' !== $args['id'] ) {
		return $html;
	}

	$html = str_replace( '<input type="text"', '<input type="text" maxlength="22"', $html );

	return $html;
}
add_filter( 'cl_after_setting_output', 'cl_stripe_max_length_statement_descriptor', 10, 2 );

/**
 * Callback for the stripe_connect_notice field type.
 *
 * @since 2.6.14
 *
 * @param array $args The setting field arguments
 */
function cl_stripe_connect_notice_callback( $args ) {

	$value = isset( $args['desc'] ) ? $args['desc'] : '';

	$class = cl_sanitize_html_class( $args['field_class'] );

	$html = '<div class="' . esc_attr( $class ) . '" id="cl_settings[' . WPERECCP()->common->formatting->cl_sanitize_key( $args['id'] ) . ']">' . esc_html( $value ) . '</div>';

	printf( $html );
}

/**
 * Callback for the stripe_checkout_notice field type.
 *
 * @since 2.7.0
 *
 * @param array $args The setting field arguments
 */
function cl_stripe_checkout_notice_callback( $args ) {
	$value = isset( $args['desc'] ) ? $args['desc'] : '';

	$html = '<div class="notice notice-warning inline' . cl_sanitize_html_class( $args['field_class'] ) . '" id="cl_settings[' . WPERECCP()->common->formatting->cl_sanitize_key( $args['id'] ) . ']">' . wpautop( $value ) . '</div>';

	printf( $html );
}

/**
 * Outputs information when Stripe has been activated but application requirements are not met.
 *
 * @since 2.8.1
 */
function cl_stripe_requirements_not_met_callback() {
	$required_version = 5.6;
	$current_version  = phpversion();

	echo '<div class="notice inline notice-warning">';
	echo '<p>';
	echo wp_kses(
		sprintf(
			/* translators: %1$s Future PHP version requirement. %2$s Current PHP version. %3$s Opening strong tag, do not translate. %4$s Closing strong tag, do not translate. */
			__(
				'Processing credit cards with Stripe requires PHP version %1$s or higher. It looks like you\'re using version %2$s, which means you will need to %3$supgrade your version of PHP before acceping credit card payments%4$s.',
				'essential-wp-real-estate'
			),
			'<code>' . $required_version . '</code>',
			'<code>' . $current_version . '</code>',
			'<strong>',
			'</strong>'
		),
		array(
			'code'   => true,
			'strong' => true,
		)
	);
	echo '</p>';
	echo '<p>';

	echo '<strong>';
	esc_html_e( 'Need help upgrading? Ask your web host!', 'essential-wp-real-estate' );
	echo '</strong><br />';

	echo wp_kses(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__(
				'Many web hosts can give you instructions on how/where to upgrade your version of PHP through their control panel, or may even be able to do it for you. If you need to change hosts, please see %1$sour hosting recommendations%2$s.',
				'essential-wp-real-estate'
			),
			'<a href="#" target="_blank" rel="noopener noreferrer">',
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
	echo '</p>';
	echo '</div>';
}

/**
 * Adds a notice to the "Payment Gateways" selector if Stripe has been activated but does
 * not meet application requirements.
 *
 * @since 2.8.1
 *
 * @param string $html Setting HTML.
 * @param array  $args Setting arguments.
 * @return string
 */
function cls_payment_gateways_notice( $html, $args ) {
	if ( 'gateways' !== $args['id'] ) {
		return $html;
	}

	if (
		true === cls_is_pro() ||
		true === cls_has_met_requirements( 'php' )
	) {
		return $html;
	}

	$required_version = 5.6;
	$current_version  = phpversion();

	$html .= '<div id="cls-payment-gateways-stripe-unmet-requirements" class="notice inline notice-info"><p>' .
		wp_kses(
			sprintf(
				/* translators: %1$s PHP version requirement. %2$s Current PHP version. %3$s Opening strong tag, do not translate. %4$s Closing strong tag, do not translate. */
				__(
					'Processing credit cards with Stripe requires PHP version %1$s or higher. It looks like you\'re using version %2$s, which means you will need to %3$supgrade your version of PHP before acceping credit card payments%4$s.',
					'essential-wp-real-estate'
				),
				'<code>' . $required_version . '</code>',
				'<code>' . $current_version . '</code>',
				'<strong>',
				'</strong>'
			),
			array(
				'code'   => true,
				'strong' => true,
			)
		) .
		'</p><p><strong>' .
		esc_html__( 'Need help upgrading? Ask your web host!', 'essential-wp-real-estate' ) .
		'</strong><br />' .
		wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'Many web hosts can give you instructions on how/where to upgrade your version of PHP through their control panel, or may even be able to do it for you. If you need to change hosts, please see %1$sour hosting recommendations%2$s.',
					'essential-wp-real-estate'
				),
				'<a href="#" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
			array(
				'a' => array(
					'href'   => true,
					'target' => true,
					'rel'    => true,
				),
			)
		) . '</p></div>';

	return $html;
}
add_filter( 'cl_after_setting_output', 'cls_payment_gateways_notice', 10, 2 );
