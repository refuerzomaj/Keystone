<div id="cl_checkout_wrap">
	<?php if ( WPERECCP()->front->cart->cl_get_cart_contents() || WPERECCP()->front->cart->cl_cart_has_fees() ) {
		echo WPERECCP()->front->cart->cl_shopping_cart();
		?>
		<div id="cl_checkout_form_wrap" class="cl_clearfix">
			<?php do_action( 'cl_before_purchase_form' ); ?>
			<form id="cl_purchase_form" class="cl_form" action="<?php echo esc_attr( $form_action ); ?>" method="POST">
				<?php
				/**
				 * Hooks in at the top of the checkout form
				 *
				 * @since 1.0
				 */
				do_action( 'cl_checkout_form_top' );

				if ( cl_is_ajax_disabled() && ! empty( $_REQUEST['payment-mode'] ) ) {
					do_action( 'cl_purchase_form' );
				} elseif ( WPERECCP()->front->gateways->cl_show_gateways() ) {
					?>
					<fieldset id="cl_payment_mode_select">
						<legend><?php _e( 'Select Payment Method', 'essential-wp-real-estate' ); ?></legend>
						<?php do_action( 'cl_payment_mode_before_gateways_wrap' ); ?>
						<div id="cl-payment-mode-wrap">
							<?php

							do_action( 'cl_payment_mode_before_gateways' );

							foreach ( $gateways as $gateway_id => $gateway ) :

								$label         = apply_filters( 'cl_gateway_checkout_label_' . $gateway_id, $gateway['checkout_label'] );
								$checked       = checked( $gateway_id, $chosen_gateway, false );
								$checked_class = $checked ? ' cl-gateway-option-selected' : '';
								$nonce         = ' data-' . esc_attr( $gateway_id ) . '-nonce="' . wp_create_nonce( 'cl-gateway-selected-' . esc_attr( $gateway_id ) ) . '"';

								echo '<label for="cl-gateway-' . esc_attr( $gateway_id ) . '" class="cl-gateway-option' . esc_attr( $checked_class ) . '" id="cl-gateway-option-' . esc_attr( $gateway_id ) . '">';
								echo '<input type="radio" name="payment-mode" class="cl-gateway" id="cl-gateway-' . esc_attr( $gateway_id ) . '" value="' . esc_attr( $gateway_id ) . '" ' . esc_attr( $checked ) . $nonce . '>' . esc_html( $label );
								echo '</label>';

							endforeach;

							do_action( 'cl_payment_mode_after_gateways' );

							?>
						</div>
						<?php do_action( 'cl_payment_mode_after_gateways_wrap' ); ?>
					</fieldset>
					<fieldset id="cl_payment_mode_submit" class="cl-no-js">
						<p id="cl-next-submit-wrap">
							<input type="hidden" name="cl_action" value="gateway_select" />
							<input type="hidden" name="page_id" value="<?php echo absint( $purchase_page ); ?>" />
							<input type="submit" name="gateway_submit" id="cl_next_button" class="cl-submit" value="<?php _e( 'Next', 'essential-wp-real-estate' ); ?>" />
						</p>
					</fieldset>
					<?php
				} else {

					do_action( 'cl_purchase_form' );
				}

				do_action( 'cl_checkout_form_bottom' )
				?>
				<div id="cl_purchase_form_wrap"></div>
			</form>
			<?php do_action( 'cl_after_purchase_form' ); ?>
		</div>
		<!--end #cl_checkout_form_wrap-->
	<?php } else {
		do_action( 'cl_cart_empty' );
	} ?>
