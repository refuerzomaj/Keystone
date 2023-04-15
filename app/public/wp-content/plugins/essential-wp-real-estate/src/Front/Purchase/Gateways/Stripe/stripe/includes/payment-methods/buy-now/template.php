<?php
/**
 * Buy Now: Template
 *
 * @package CL_Stripe
 * @since   2.8.0
 */

/**
 * Adds "Buy Now" modal markup to the bottom of the page.
 *
 * @since 2.8.0
 */
function cls_buy_now_modal() {
	// Check if Stripe Buy Now is enabled.
	global $cls_has_buy_now;

	if ( true !== $cls_has_buy_now ) {
		return;
	}

	if ( ! cls_buy_now_is_enabled() ) {
		return;
	}

	// Enqueue core scripts.
	add_filter( 'cl_is_checkout', '__return_true' );

	if ( function_exists( 'cl_enqueue_scripts' ) ) {
		cl_enqueue_scripts();
		cl_localize_scripts();
	} else {
		cl_load_scripts();
	}

	cl_agree_to_terms_js();

	remove_filter( 'cl_is_checkout', '__return_true' );

	// Enqueue scripts.
	cl_stripe_js( true );
	cl_stripe_css( true );

	echo cls_modal(
		array(
			'id'      => 'cls-buy-now',
			'title'   => __( 'Buy Now', 'essential-wp-real-estate' ),
			'class'   => array(
				'cls-buy-now-modal',
			),
			'content' => '<span class="cl-loading-ajax cl-loading"></span>',
		)
	); // WPCS: XSS okay.
}
add_action( 'wp_print_footer_scripts', 'cls_buy_now_modal', 0 );

/**
 * Outputs a custom "Buy Now"-specific Checkout form.
 *
 * @since 2.8.0
 */
function cls_buy_now_checkout() {
	$total = (int) WPERECCP()->front->cart->cl_get_cart_total();

	$form_mode      = $total > 0
		? 'payment-mode=stripe'
		: 'payment-mode=manual';
	$form_action    = cl_get_checkout_uri( $form_mode );
	$existing_cards = cl_stripe_get_existing_cards( get_current_user_id() );

	$customer = WPERECCP()->front->session->get( 'customer' );
	$customer = wp_parse_args(
		$customer,
		array(
			'email' => '',
		)
	);

	if ( is_user_logged_in() ) {
		$user_data = get_userdata( get_current_user_id() );

		foreach ( $customer as $key => $field ) {
			if ( 'email' == $key && empty( $field ) ) {
				$customer[ $key ] = $user_data->user_email;
			} elseif ( empty( $field ) ) {
				$customer[ $key ] = $user_data->$key;
			}
		}
	}

	$customer = array_map( 'cl_sanitization', $customer );

	remove_action( 'cl_after_cc_fields', 'cl_default_cc_address_fields', 10 );
	remove_action( 'cl_purchase_form_before_submit', 'cl_checkout_final_total', 999 );

	// Filter purchase button label.
	add_filter( 'cl_get_checkout_button_purchase_label', 'cls_buy_now_checkout_purchase_label' );

	ob_start();
	?>

	<div id="cl_checkout_form_wrap">
		<form id="cl_purchase_form" class="cl_form cls-buy-now-form" action="<?php echo esc_url( $form_action ); ?>" method="POST">
			<p>
				<label class="cl-label" for="cl-email">
					<?php esc_html_e( 'Email Address', 'essential-wp-real-estate' ); ?>
					<?php if ( cl_field_is_required( 'cl_email' ) ) : ?>
						<span class="cl-required-indicator">*</span>
					<?php endif ?>
				</label>

				<input id="cl-email" class="cl-input required" type="email" name="cl_email" value="<?php echo esc_attr( $customer['email'] ); ?>" 
																											  <?php
																												if ( cl_field_is_required( 'cl_email' ) ) :
																													?>
					 required <?php endif; ?> />
			</p>

			<?php if ( $total > 0 ) : ?>

				<?php if ( ! empty( $existing_cards ) ) : ?>
					<?php cl_stripe_existing_card_field_radio( get_current_user_id() ); ?>
				<?php endif; ?>

				<div class="cl-stripe-new-card" 
				<?php
				if ( ! empty( $existing_cards ) ) :
					?>
					 style="display: none;" <?php endif; ?>>
					<?php do_action( 'cl_stripe_new_card_form' ); ?>
					<?php do_action( 'cl_after_cc_expiration' ); ?>
				</div>

			<?php endif; ?>

			<?php
			cl_terms_agreement();
			cl_privacy_agreement();
			cl_checkout_hidden_fields();
			?>

			<div id="cl_purchase_submit">
				<?php
				echo cl_checkout_button_purchase(); // WPCS: XSS okay.
				?>
			</div>

			<div class="cl_cart_total" style="display: none;">
				<div class="cl_cart_amount" data-total="<?php echo WPERECCP()->front->cart->cl_get_cart_total(); ?>" data-total-currency="<?php echo WPERECCP()->common->formatting->cl_currency_filter( cl_format_amount( WPERECCP()->front->cart->cl_get_cart_total() ) ); ?>">
				</div>
			</div>

			<input type="hidden" name="cls-gateway" value="buy-now" />
		</form>
	</div>

	<?php
	return ob_get_clean();
}

/**
 * Filters the label of the of the "Purchase" button in the "Buy Now" modal.
 *
 * @since 2.8.0
 *
 * @param string $label Purchase label.
 * @return string
 */
function cls_buy_now_checkout_purchase_label( $label ) {
	$total = WPERECCP()->front->cart->cl_get_cart_total();

	if ( 0 === (int) $total ) {
		return $label;
	}

	return sprintf(
		'%s - %s',
		WPERECCP()->common->formatting->cl_currency_filter(
			cl_format_amount( $total )
		),
		$label
	);
}

/**
 * Adds additional script variables needed for the Buy Now flow.
 *
 * @since 2.8.0
 *
 * @param array $vars Script variables.
 * @return array
 */
function cls_buy_now_vars( $vars ) {
	if ( ! isset( $vars['i18n'] ) ) {
		$vars['i18n'] = array();
	}

	// Non-zero amount.
	$label             = cl_admin_get_option( 'checkout_label', '' );
	$complete_purchase = ! empty( $label )
		? $label
		: esc_html__( 'Purchase', 'essential-wp-real-estate' );

	$complete_purchase = apply_filters(
		'cl_get_checkout_button_purchase_label',
		$complete_purchase,
		$label
	);

	$vars['i18n']['completePurchase'] = $complete_purchase;

	return $vars;
}
add_filter( 'cl_stripe_js_vars', 'cls_buy_now_vars' );
