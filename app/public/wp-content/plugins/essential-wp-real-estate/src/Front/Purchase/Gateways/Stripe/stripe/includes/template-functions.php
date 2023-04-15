<?php
/**
 * Add an errors div
 *
 * @since       1.0
 * @return      void
 */
function cls_add_stripe_errors() {
	echo '<div id="cl-stripe-payment-errors"></div>';
}
add_action( 'cl_after_cc_fields', 'cls_add_stripe_errors', 999 );

/**
 * Stripe uses it's own credit card form because the card details are tokenized.
 *
 * We don't want the name attributes to be present on the fields in order to prevent them from getting posted to the server
 *
 * @since       1.7.5
 * @return      void
 */
function cls_credit_card_form( $echo = true ) {
	global $cl_options;

	if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
		cl_set_error( 'cl_stripe_error_limit', __( 'We are unable to process your payment at this time, please try again later or contact support.', 'essential-wp-real-estate' ) );
		return;
	}

	ob_start(); ?>

	<?php if ( ! wp_script_is( 'cl-stripe-js' ) ) : ?>
		<?php cl_stripe_js( true ); ?>
	<?php endif; ?>

	<?php do_action( 'cl_before_cc_fields' ); ?>

	<fieldset id="cl_cc_fields" class="cl-do-validate">
		<legend><?php _e( 'Credit Card Info', 'essential-wp-real-estate' ); ?></legend>
		<?php if ( is_ssl() ) : ?>
			<div id="cl_secure_site_wrapper">
				<span class="padlock">
					<?php
					if ( function_exists( 'cl_get_payment_icon' ) ) {
						echo cl_get_payment_icon(
							array(
								'icon'    => 'lock',
								'width'   => 18,
								'height'  => 28,
								'classes' => array(
									'cl-icon',
									'cl-icon-lock',
								),
							)
						);
					} else {
						?>
						<svg class="cl-icon cl-icon-lock" xmlns="http://www.w3.org/2000/svg" width="18" height="28" viewBox="0 0 18 28" aria-hidden="true">
							<path d="M5 12h8V9c0-2.203-1.797-4-4-4S5 6.797 5 9v3zm13 1.5v9c0 .828-.672 1.5-1.5 1.5h-15C.672 24 0 23.328 0 22.5v-9c0-.828.672-1.5 1.5-1.5H2V9c0-3.844 3.156-7 7-7s7 3.156 7 7v3h.5c.828 0 1.5.672 1.5 1.5z" />
						</svg>
						<?php
					}
					?>
				</span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'essential-wp-real-estate' ); ?></span>
			</div>
		<?php endif; ?>

		<?php
		$existing_cards = cl_stripe_get_existing_cards( get_current_user_id() );
		?>
		<?php
		if ( ! empty( $existing_cards ) ) {
			cl_stripe_existing_card_field_radio( get_current_user_id() );
		}
		?>

		<div class="cl-stripe-new-card" 
		<?php
		if ( ! empty( $existing_cards ) ) {
											echo 'style="display: none;"';
		}
		?>
										>
			<?php do_action( 'cl_stripe_new_card_form' ); ?>
			<?php do_action( 'cl_after_cc_expiration' ); ?>
		</div>

	</fieldset>
	<?php

	do_action( 'cl_after_cc_fields' );

	$form = ob_get_clean();

	if ( false !== $echo ) {
		printf( $form );
	}

	return $form;
}
add_action( 'cl_stripe_cc_form', 'cls_credit_card_form' );

/**
 * Display the markup for the Stripe new card form
 *
 * @since 2.6
 * @return void
 */
function cl_stripe_new_card_form() {
	if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
		cl_set_error( 'cl_stripe_error_limit', __( 'Adding new payment methods is currently unavailable.', 'essential-wp-real-estate' ) );
		cl_print_errors();
		return;
	}

	$split = cl_admin_get_option( 'stripe_split_payment_fields', false );
	?>

	<p id="cl-card-name-wrap">
		<label for="card_name" class="cl-label">
			<?php esc_html_e( 'Name on the Card', 'essential-wp-real-estate' ); ?>
			<span class="cl-required-indicator">*</span>
		</label>
		<span class="cl-description"><?php esc_html_e( 'The name printed on the front of your credit card.', 'essential-wp-real-estate' ); ?></span>
		<input type="text" name="card_name" id="card_name" class="card-name cl-input required" placeholder="<?php esc_attr_e( 'Card name', 'essential-wp-real-estate' ); ?>" autocomplete="cc-name" />
	</p>

	<div id="cl-card-wrap">
		<label for="cl-card-element" class="cl-label">
			<?php
			if ( '1' === $split ) :
				esc_html_e( 'Credit Card Number', 'essential-wp-real-estate' );
			else :
				esc_html_e( 'Credit Card', 'essential-wp-real-estate' );
			endif;
			?>
			<span class="cl-required-indicator">*</span>
		</label>

		<div id="cl-stripe-card-element-wrapper">
			<?php if ( '1' === $split ) : ?>
				<span class="card-type"></span>
			<?php endif; ?>

			<div id="cl-stripe-card-element" class="cl-stripe-card-element"></div>
		</div>

		<p class="cls-field-spacer-shim"></p><!-- Extra spacing -->
	</div>

	<?php if ( '1' === $split ) : ?>

		<div id="cl-card-details-wrap">
			<p class="cls-field-spacer-shim"></p><!-- Extra spacing -->

			<div id="cl-card-exp-wrap">
				<label for="cl-card-exp-element" class="cl-label">
					<?php esc_html_e( 'Expiration', 'essential-wp-real-estate' ); ?>
					<span class="cl-required-indicator">*</span>
				</label>

				<div id="cl-stripe-card-exp-element" class="cl-stripe-card-exp-element"></div>
			</div>

			<div id="cl-card-cvv-wrap">
				<label for="cl-card-exp-element" class="cl-label">
					<?php esc_html_e( 'CVC', 'essential-wp-real-estate' ); ?>
					<span class="cl-required-indicator">*</span>
				</label>

				<div id="cl-stripe-card-cvc-element" class="cl-stripe-card-cvc-element"></div>
			</div>
		</div>

	<?php endif; ?>

	<div id="cl-stripe-card-errors" role="alert"></div>

	<?php
	/**
	 * Allow output of extra content before the credit card expiration field.
	 *
	 * This content no longer appears before the credit card expiration field
	 * with the introduction of Stripe Elements.
	 *
	 * @deprecated 2.7
	 * @since unknown
	 */
	do_action( 'cl_before_cc_expiration' );
}
add_action( 'cl_stripe_new_card_form', 'cl_stripe_new_card_form' );

/**
 * Show the checkbox for updating the billing information on an existing Stripe card
 *
 * @since 2.6
 * @return void
 */
function cl_stripe_update_billing_address_field() {
	 $payment_mode = strtolower( WPERECCP()->front->gateways->cl_get_chosen_gateway() );
	if ( cl_is_checkout() && 'stripe' !== $payment_mode ) {
		return;
	}

	$existing_cards = cl_stripe_get_existing_cards( get_current_user_id() );
	if ( empty( $existing_cards ) ) {
		return;
	}

	if ( ! did_action( 'cl_stripe_cc_form' ) ) {
		return;
	}

	$default_card = false;

	foreach ( $existing_cards as $existing_card ) {
		if ( $existing_card['default'] ) {
			$default_card = $existing_card['source'];
			break;
		}
	}
	?>
	<p class="cl-stripe-update-billing-address-current">
		<?php
		if ( $default_card ) :
			$address_fields = array(
				'line1'   => isset( $default_card->address_line1 ) ? $default_card->address_line1 : null,
				'line2'   => isset( $default_card->address_line2 ) ? $default_card->address_line2 : null,
				'city'    => isset( $default_card->address_city ) ? $default_card->address_city : null,
				'state'   => isset( $default_card->address_state ) ? $default_card->address_state : null,
				'zip'     => isset( $default_card->address_zip ) ? $default_card->address_zip : null,
				'country' => isset( $default_card->address_country ) ? $default_card->address_country : null,
			);

			$address_fields = array_filter( $address_fields );

			echo esc_html( implode( ', ', $address_fields ) );
		endif;
		?>
	</p>

	<p class="cl-stripe-update-billing-address-wrapper">
		<input type="checkbox" name="cl_stripe_update_billing_address" id="cl-stripe-update-billing-address" value="1" />
		<label for="cl-stripe-update-billing-address">
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s Card type. %2$s Card last 4. */
					__( 'Update card billing address for %1$s •••• %2$s', 'essential-wp-real-estate' ),
					'<span class="cl-stripe-update-billing-address-brand">' . ( $default_card ? $default_card->brand : '' ) . '</span>',
					'<span class="cl-stripe-update-billing-address-last4">' . ( $default_card ? $default_card->last4 : '' ) . '</span>'
				),
				array(
					'strong' => true,
					'span'   => array(
						'class' => true,
					),
				)
			);
			?>
		</label>
	</p>
	<?php
}
add_action( 'cl_cc_billing_top', 'cl_stripe_update_billing_address_field', 10 );

/**
 * Display a radio list of existing cards on file for a user ID
 *
 * @since 2.6
 * @param int $user_id
 *
 * @return void
 */
function cl_stripe_existing_card_field_radio( $user_id = 0 ) {
	if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
		cl_set_error( 'cl_stripe_error_limit', __( 'We are unable to process your payment at this time, please try again later or contacts support.', 'essential-wp-real-estate' ) );
		return;
	}

	// Can't use just cl_is_checkout() because this could happen in an AJAX request.
	$is_checkout = cl_is_checkout() || ( isset( $_REQUEST['action'] ) && 'cl_load_gateway' === $_REQUEST['action'] );

	cl_stripe_css( true );
	$existing_cards = cl_stripe_get_existing_cards( $user_id );
	if ( ! empty( $existing_cards ) ) :
		?>
		<div class="cl-stripe-card-selector cl-card-selector-radio">
			<?php foreach ( $existing_cards as $card ) : ?>
				<?php $source = $card['source']; ?>
				<div class="cl-stripe-card-radio-item existing-card-wrapper 
				<?php
				if ( $card['default'] ) {
																				echo ' selected';
				}
				?>
																			">
					<input type="hidden" id="<?php echo esc_attr( $source->id ); ?>-billing-details" data-address_city="<?php echo esc_attr( $source->address_city ); ?>" data-address_country="<?php echo esc_attr( $source->address_country ); ?>" data-address_line1="<?php echo esc_attr( $source->address_line1 ); ?>" data-address_line2="<?php echo esc_attr( $source->address_line2 ); ?>" data-address_state="<?php echo esc_attr( $source->address_state ); ?>" data-address_zip="<?php echo esc_attr( $source->address_zip ); ?>" data-brand="<?php echo esc_attr( $source->brand ); ?>" data-last4="<?php echo esc_attr( $source->last4 ); ?>" />
					<label for="<?php echo esc_attr( $source->id ); ?>">
						<input <?php checked( true, $card['default'], true ); ?> type="radio" id="<?php echo esc_attr( $source->id ); ?>" name="cl_stripe_existing_card" value="<?php echo esc_attr( $source->id ); ?>" class="cl-stripe-existing-card">
						<span class="card-label">
							<span class="card-data">
								<span class="card-name-number">
									<?php
									echo wp_kses(
										sprintf(
											/* translators: %1$s Card type. %2$s Card last 4. */
											__( '%1$s •••• %2$s', 'essential-wp-real-estate' ),
											'<span class="card-brand">' . $source->brand . '</span>',
											'<span class="card-last-4">' . $source->last4 . '</span>'
										),
										array(
											'span' => array(
												'class' => true,
											),
										)
									);
									?>
								</span>
								<small class="card-expires-on">
									<span class="default-card-sep"><?php echo '&nbsp;&nbsp;&nbsp;'; ?></span>
									<span class="card-expiration">
										<?php echo esc_html( $source->exp_month ) . '/' . esc_html( $source->exp_year ); ?>
									</span>
								</small>
							</span>
							<?php
							$current  = strtotime( date( 'm/Y' ) );
							$exp_date = strtotime( $source->exp_month . '/' . $source->exp_year );
							if ( $exp_date < $current ) :
								?>
								<span class="card-expired">
									<?php _e( 'Expired', 'essential-wp-real-estate' ); ?>
								</span>
								<?php
							endif;
							?>
						</span>
					</label>
				</div>
			<?php endforeach; ?>
			<div class="cl-stripe-card-radio-item new-card-wrapper">
				<label for="cl-stripe-add-new">
					<input type="radio" id="cl-stripe-add-new" class="cl-stripe-existing-card" name="cl_stripe_existing_card" value="new" />
					<span class="add-new-card"><?php _e( 'Add New Card', 'essential-wp-real-estate' ); ?></span>
				</label>
			</div>
		</div>
		<?php
	endif;
}

/**
 * Output the management interface for a user's Stripe card
 *
 * @since 2.6
 * @return void
 */
function cl_stripe_manage_cards() {
	if ( false === cls_is_gateway_active() ) {
		return;
	}

	$enabled = cl_stripe_existing_cards_enabled();
	if ( ! $enabled ) {
		return;
	}

	$stripe_customer_id = cls_get_stripe_customer_id( get_current_user_id() );
	if ( empty( $stripe_customer_id ) ) {
		return;
	}

	if ( cl_stripe()->rate_limiting->has_hit_card_error_limit() ) {
		cl_set_error( 'cl_stripe_error_limit', __( 'Payment method management is currently unavailable.', 'essential-wp-real-estate' ) );
		cl_print_errors();
		return;
	}

	$existing_cards = cl_stripe_get_existing_cards( get_current_user_id() );

	cl_stripe_css( true );
	cl_stripe_js( true );
	$display = cl_admin_get_option( 'stripe_billing_fields', 'full' );
	?>
	<div id="cl-stripe-manage-cards">
		<fieldset>
			<legend><?php _e( 'Manage Payment Methods', 'essential-wp-real-estate' ); ?></legend>
			<input type="hidden" id="stripe-update-card-user_id" name="stripe-update-user-id" value="<?php echo get_current_user_id(); ?>" />
			<?php if ( ! empty( $existing_cards ) ) : ?>
				<?php foreach ( $existing_cards as $card ) : ?>
					<?php $source = $card['source']; ?>
					<div id="<?php echo esc_attr( $source->id ); ?>_card_item" class="cl-stripe-card-item">
						<span class="card-details">
							<?php
							echo wp_kses(
								sprintf(
									__( '%1$s •••• %2$s', 'essential-wp-real-estate' ),
									'<span class="card-brand">' . $source->brand . '</span>',
									'<span class="card-last-4">' . $source->last4 . '</span>'
								),
								array(
									'span' => array(
										'class' => true,
									),
								)
							);
							?>

							<?php if ( $card['default'] ) { ?>
								<span class="default-card-sep"><?php echo '&mdash; '; ?></span>
								<span class="card-is-default"><?php _e( 'Default', 'essential-wp-real-estate' ); ?></span>
							<?php } ?>
						</span>

						<span class="card-meta">
							<span class="card-expiration"><span class="card-expiration-label"><?php _e( 'Expires', 'essential-wp-real-estate' ); ?>: </span><span class="card-expiration-date"><?php echo esc_html( $source->exp_month ); ?>/<?php echo esc_html( $source->exp_year ); ?></span></span>
							<span class="card-address">
								<?php
								$address_fields = array(
									'line1'   => isset( $source->address_line1 ) ? $source->address_line1 : '',
									'zip'     => isset( $source->address_zip ) ? $source->address_zip : '',
									'country' => isset( $source->address_country ) ? $source->address_country : '',
								);

								echo esc_html( implode( ' ', $address_fields ) );
								?>
							</span>
						</span>

						<span id="<?php echo esc_attr( $source->id ); ?>-card-actions" class="card-actions">
							<span class="card-update">
								<a href="#" class="cl-stripe-update-card" data-source="<?php echo esc_attr( $source->id ); ?>"><?php _e( 'Update', 'essential-wp-real-estate' ); ?></a>
							</span>

							<?php if ( ! $card['default'] ) : ?>
								|
								<span class="card-set-as-default">
									<a href="#" class="cl-stripe-default-card" data-source="<?php echo esc_attr( $source->id ); ?>"><?php _e( 'Set as Default', 'essential-wp-real-estate' ); ?></a>
								</span>
								<?php
							endif;

							$can_delete = apply_filters( 'cl_stripe_can_delete_card', true, $card, $existing_cards );
							if ( $can_delete ) :
								?>
								|
								<span class="card-delete">
									<a href="#" class="cl-stripe-delete-card delete" data-source="<?php echo esc_attr( $source->id ); ?>"><?php _e( 'Delete', 'essential-wp-real-estate' ); ?></a>
								</span>
							<?php endif; ?>

							<span style="display: none;" class="cl-loading-ajax cl-loading"></span>
						</span>

						<form id="<?php echo esc_attr( $source->id ); ?>-update-form" class="card-update-form" data-source="<?php echo esc_attr( $source->id ); ?>">
							<label><?php _e( 'Billing Details1', 'essential-wp-real-estate' ); ?></label>

							<div class="card-address-fields">
								<p class="cls-card-address-field cls-card-address-field--address1">
									<?php
									echo WPERECCP()->admin->settings_instances->text(
										array(
											'id'    => sprintf( 'cls_address_line1_%1$s', $source->id ),
											'value' => cl_sanitization( isset( $source->address_line1 ) ? $source->address_line1 : '' ),
											'label' => esc_html__( 'Address Line 1', 'essential-wp-real-estate' ),
											'name'  => 'address_line1',
											'class' => 'card-update-field address_line1 text cl-input',
											'data'  => array(
												'key' => 'address_line1',
											),
										)
									);
									?>
								</p>
								<p class="cls-card-address-field cls-card-address-field--address2">
									<?php
									echo WPERECCP()->admin->settings_instances->text(
										array(
											'id'    => sprintf( 'cls_address_line2_%1$s', $source->id ),
											'value' => cl_sanitization( isset( $source->address_line2 ) ? $source->address_line2 : '' ),
											'label' => esc_html__( 'Address Line 2', 'essential-wp-real-estate' ),
											'name'  => 'address_line2',
											'class' => 'card-update-field address_line2 text cl-input',
											'data'  => array(
												'key' => 'address_line2',
											),
										)
									);
									?>
								</p>
								<p class="cls-card-address-field cls-card-address-field--city">
									<?php
									echo WPERECCP()->admin->settings_instances->text(
										array(
											'id'    => sprintf( 'cls_address_city_%1$s', $source->id ),
											'value' => cl_sanitization( isset( $source->address_city ) ? $source->address_city : '' ),
											'label' => esc_html__( 'City', 'essential-wp-real-estate' ),
											'name'  => 'address_city',
											'class' => 'card-update-field address_city text cl-input',
											'data'  => array(
												'key' => 'address_city',
											),
										)
									);
									?>
								</p>

								<p class="cls-card-address-field cls-card-address-field--zip">
									<?php
									echo WPERECCP()->admin->settings_instances->text(
										array(
											'id'    => sprintf( 'cls_address_zip_%1$s', $source->id ),
											'value' => cl_sanitization( isset( $source->address_zip ) ? $source->address_zip : '' ),
											'label' => esc_html__( 'ZIP Code', 'essential-wp-real-estate' ),
											'name'  => 'address_zip',
											'class' => 'card-update-field address_zip text cl-input',
											'data'  => array(
												'key' => 'address_zip',
											),
										)
									);
									?>
								</p>
								<p class="cls-card-address-field cls-card-address-field--country">
									<label for="<?php echo esc_attr( sprintf( 'cls_address_country_%1$s', $source->id ) ); ?>">
										<?php esc_html_e( 'Country', 'essential-wp-real-estate' ); ?>
									</label>

									<?php
									$countries = array_filter( cl_get_country_list() );
									$country   = isset( $source->address_country ) ? $source->address_country : cl_get_shop_country();
									echo WPERECCP()->admin->settings_instances->select(
										array(
											'id'       => sprintf( 'cls_address_country_%1$s', $source->id ),
											'name'     => 'address_country',
											'label'    => esc_html__( 'Country', 'essential-wp-real-estate' ),
											'options'  => $countries,
											'selected' => $country,
											'class'    => 'card-update-field address_country',
											'data'     => array( 'key' => 'address_country' ),
											'show_option_all' => false,
											'show_option_none' => false,
										)
									);
									?>
								</p>

								<p class="cls-card-address-field cls-card-address-field--state">
									<label for="<?php echo esc_attr( sprintf( 'cls_address_state_%1$s', $source->id ) ); ?>">
										<?php esc_html_e( 'State', 'essential-wp-real-estate' ); ?>
									</label>

									<?php
									$selected_state = isset( $source->address_state ) ? $source->address_state : cl_get_shop_state();
									$states         = cl_get_shop_states( $country );
									echo WPERECCP()->admin->settings_instances->select(
										array(
											'id'       => sprintf( 'cls_address_state_%1$s', $source->id ),
											'name'     => 'address_state',
											'options'  => $states,
											'selected' => $selected_state,
											'class'    => 'card-update-field address_state card_state',
											'data'     => array( 'key' => 'address_state' ),
											'show_option_all' => false,
											'show_option_none' => false,
										)
									);
									?>
								</p>
							</div>

							<p class="card-expiration-fields">
								<label for="<?php echo esc_attr( sprintf( 'cls_card_exp_month_%1$s', $source->id ) ); ?>" class="cl-label">
									<?php _e( 'Expiration (MM/YY)', 'essential-wp-real-estate' ); ?>
								</label>

								<?php
								$months = array_combine( $r = range( 1, 12 ), $r );
								echo WPERECCP()->admin->settings_instances->select(
									array(
										'id'               => sprintf( 'cls_card_exp_month_%1$s', $source->id ),
										'name'             => 'exp_month',
										'options'          => $months,
										'selected'         => $source->exp_month,
										'class'            => 'card-expiry-month cl-select cl-select-small card-update-field exp_month',
										'data'             => array( 'key' => 'exp_month' ),
										'show_option_all'  => false,
										'show_option_none' => false,
									)
								);
								?>

								<span class="exp-divider"> / </span>

								<?php
								$years = array_combine( $r = range( date( 'Y' ), date( 'Y' ) + 30 ), $r );
								echo WPERECCP()->admin->settings_instances->select(
									array(
										'id'               => sprintf( 'cls_card_exp_year_%1$s', $source->id ),
										'name'             => 'exp_year',
										'options'          => $years,
										'selected'         => $source->exp_year,
										'class'            => 'card-expiry-year cl-select cl-select-small card-update-field exp_year',
										'data'             => array( 'key' => 'exp_year' ),
										'show_option_all'  => false,
										'show_option_none' => false,
									)
								);
								?>
							</p>

							<p>
								<input type="submit" class="cl-stripe-submit-update" data-loading="<?php echo esc_attr__( 'Please Wait…', 'essential-wp-real-estate' ); ?>" data-submit="<?php echo esc_attr__( 'Update Card', 'essential-wp-real-estate' ); ?>" value="<?php echo esc_attr__( 'Update Card', 'essential-wp-real-estate' ); ?>" />

								<a href="#" class="cl-stripe-cancel-update" data-source="<?php echo esc_attr( $source->id ); ?>"><?php _e( 'Cancel', 'essential-wp-real-estate' ); ?></a>

								<input type="hidden" name="card_id" data-key="id" value="<?php echo esc_attr( $source->id ); ?>" />
								<?php wp_nonce_field( $source->id . '_update', 'card_update_nonce_' . $source->id, true ); ?>
							</p>
						</form>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<form id="cl-stripe-add-new-card">
				<div class="cl-stripe-add-new-card cl-stripe-new-card" style="display: none;">
					<label><?php _e( 'Add New Card', 'essential-wp-real-estate' ); ?></label>
					<fieldset id="cl_cc_card_info" class="cc-card-info">
						<legend><?php _e( 'Credit Card Details', 'essential-wp-real-estate' ); ?></legend>
						<?php do_action( 'cl_stripe_new_card_form' ); ?>
					</fieldset>
					<?php
					switch ( $display ) {
						case 'full':
							cl_default_cc_address_fields();
							break;

						case 'zip_country':
							cl_stripe_zip_and_country();
							add_filter( 'cl_purchase_form_required_fields', 'cl_stripe_require_zip_and_country' );

							break;
					}
					?>
				</div>
				<div class="cl-stripe-add-card-errors"></div>
				<div class="cl-stripe-add-card-actions">

					<input type="submit" class="cl-button cl-stripe-add-new" data-loading="<?php echo esc_attr__( 'Please Wait…', 'essential-wp-real-estate' ); ?>" data-submit="<?php echo esc_attr__( 'Add new card', 'essential-wp-real-estate' ); ?>" value="<?php echo esc_attr__( 'Add new card', 'essential-wp-real-estate' ); ?>" />
					<a href="#" id="cl-stripe-add-new-cancel" style="display: none;"><?php _e( 'Cancel', 'essential-wp-real-estate' ); ?></a>
					<?php wp_nonce_field( 'cl-stripe-add-card', 'cl-stripe-add-card-nonce', false, true ); ?>
				</div>
			</form>
		</fieldset>
	</div>
	<?php
}
add_action( 'cl_profile_editor_after', 'cl_stripe_manage_cards' );

/**
 * Determines if the default Profile Editor's "Billing Address"
 * fields should be hidden.
 *
 * If using Stripe + Saved Cards (and Stripe is the only active gateway)
 * the information set in "Billing Address" is never utilized:
 *
 * - When using an existing Card that Card's billing address is used.
 * - When adding a new Card the address form is blanked.
 *
 * @since 2.8.0
 */
function cl_stripe_maybe_hide_profile_editor_billing_address() {
	if ( false === cls_is_gateway_active() ) {
		return;
	}

	// Only hide if Stripe is the only active gateway.
	$active_gateways = WPERECCP()->front->gateways->cl_get_enabled_payment_gateways();

	if ( ! ( 1 === count( $active_gateways ) && isset( $active_gateways['stripe'] ) ) ) {
		return;
	}

	// Only hide if using Saved Cards.
	$use_saved_cards = cl_stripe_existing_cards_enabled();

	if ( false === $use_saved_cards ) {
		return;
	}

	// Allow a default addres to be entered for the first Card
	// if the Profile Editor is found before Checkout.
	$existing_cards = cl_stripe_get_existing_cards( get_current_user_id() );

	if ( empty( $existing_cards ) ) {
		return;
	}

	echo '<style>#cl_profile_address_fieldset { display: none; }</style>';
}
add_action( 'cl_profile_editor_after', 'cl_stripe_maybe_hide_profile_editor_billing_address' );

/**
 * Zip / Postal Code field for when full billing address is disabled
 *
 * @since       2.5
 * @return      void
 */
function cl_stripe_zip_and_country() {
	$logged_in = is_user_logged_in();
	$customer  = WPERECCP()->front->session->get( 'customer' );
	$customer  = wp_parse_args(
		$customer,
		array(
			'address' => array(
				'line1'   => '',
				'line2'   => '',
				'city'    => '',
				'zip'     => '',
				'state'   => '',
				'country' => '',
			),
		)
	);

	$customer['address'] = array_map( 'cl_sanitization', $customer['address'] );

	if ( $logged_in ) {
		$existing_cards = cl_stripe_get_existing_cards( get_current_user_id() );
		if ( empty( $existing_cards ) ) {

			$user_address = cl_get_customer_address( get_current_user_id() );

			foreach ( $customer['address'] as $key => $field ) {

				if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
					$customer['address'][ $key ] = $user_address[ $key ];
				} else {
					$customer['address'][ $key ] = '';
				}
			}
		} else {
			foreach ( $existing_cards as $card ) {
				if ( false === $card['default'] ) {
					continue;
				}

				$source              = $card['source'];
				$customer['address'] = array(
					'line1'   => $source->address_line1,
					'line2'   => $source->address_line2,
					'city'    => $source->address_city,
					'zip'     => $source->address_zip,
					'state'   => $source->address_state,
					'country' => $source->address_country,
				);
			}
		}
	}
	?>
	<fieldset id="cl_cc_address" class="cc-address">
		<legend><?php _e( 'Billing Details', 'essential-wp-real-estate' ); ?></legend>
		<p id="cl-card-country-wrap">
			<label for="billing_country" class="cl-label">
				<?php _e( 'Billing Country', 'essential-wp-real-estate' ); ?>
				<?php if ( cl_field_is_required( 'billing_country' ) ) { ?>
					<span class="cl-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="cl-description"><?php _e( 'The country for your billing address.', 'essential-wp-real-estate' ); ?></span>
			<select name="billing_country" id="billing_country" class="billing_country cl-select
			<?php
			if ( WPERECCP()->front->checkout->cl_field_is_required( 'billing_country' ) ) {
				echo ' required';
			}
			?>
				" 
				<?php
				if ( WPERECCP()->front->checkout->cl_field_is_required( 'billing_country' ) ) {
							echo ' required ';
				}
				?>
				autocomplete="billing country">
				<?php

				$selected_country = cl_get_shop_country();

				if ( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
					$selected_country = $customer['address']['country'];
				}

				$countries = cl_get_country_list();
				foreach ( $countries as $country_code => $country ) {
					echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . esc_html( $country ) . '</option>';
				}
				?>
			</select>
		</p>
		<p id="cl-card-zip-wrap">
			<label for="card_zip" class="cl-label">
				<?php _e( 'Billing Zip / Postal Code', 'essential-wp-real-estate' ); ?>
				<?php if ( cl_field_is_required( 'card_zip' ) ) { ?>
					<span class="cl-required-indicator">*</span>
				<?php } ?>
			</label>
			<span class="cl-description"><?php _e( 'The zip or postal code for your billing address.', 'essential-wp-real-estate' ); ?></span>
			<input type="text" size="4" name="card_zip" id="card_zip" class="card-zip cl-input
			<?php
			if ( WPERECCP()->front->checkout->cl_field_is_required( 'card_zip' ) ) {
				echo ' required';
			}
			?>
			" placeholder="<?php _e( 'Zip / Postal Code', 'essential-wp-real-estate' ); ?>" value="<?php echo esc_attr( $customer['address']['zip'] ); ?>" 
			<?php
			if ( WPERECCP()->front->checkout->cl_field_is_required( 'card_zip' ) ) {
				 echo ' required ';
			}
			?>
			 autocomplete="billing postal-code" />
		</p>
	</fieldset>
	<?php
}

/**
 * Determine how the billing address fields should be displayed
 *
 * @access      public
 * @since       2.5
 * @return      void
 */
function cl_stripe_setup_billing_address_fields() {
	if ( ! method_exists( WPERECCP()->front->tax, 'cl_use_taxes' ) ) {
		return;
	}

	if ( WPERECCP()->front->tax->cl_use_taxes() || 'stripe' !== WPERECCP()->front->gateways->cl_get_chosen_gateway() || ! WPERECCP()->front->cart->cl_get_cart_total() > 0 ) {
		return;
	}

	$display = cl_admin_get_option( 'stripe_billing_fields', 'full' );

	switch ( $display ) {

		case 'full':
			// Make address fields required
			add_filter( 'cl_require_billing_address', '__return_true' );

			break;

		case 'zip_country':
			remove_action( 'cl_after_cc_fields', 'cl_default_cc_address_fields', 10 );
			add_action( 'cl_after_cc_fields', 'cl_stripe_zip_and_country', 9 );

			// Make Zip required
			add_filter( 'cl_purchase_form_required_fields', 'cl_stripe_require_zip_and_country' );

			break;

		case 'none':
			remove_action( 'cl_after_cc_fields', 'cl_default_cc_address_fields', 10 );

			break;
	}
}
add_action( 'init', 'cl_stripe_setup_billing_address_fields', 9 );

/**
 * Force zip code and country to be required when billing address display is zip only
 *
 * @access      public
 * @since       2.5
 * @return      array $fields The required fields
 */
function cl_stripe_require_zip_and_country( $fields ) {

	$fields['card_zip'] = array(
		'error_id'      => 'invalid_zip_code',
		'error_message' => __( 'Please enter your zip / postal code', 'essential-wp-real-estate' ),
	);

	$fields['billing_country'] = array(
		'error_id'      => 'invalid_country',
		'error_message' => __( 'Please select your billing country', 'essential-wp-real-estate' ),
	);

	return $fields;
}
