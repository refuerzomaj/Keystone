

<?php 
$woocommerce_active = cl_admin_get_option( 'woocommerce_active' ) != 1 ? false : true;
if($woocommerce_active == '1' && class_exists( 'WooCommerce' )){
$price = get_post_meta($listing->ID,'wperesds_pricing',true);
$checkout = esc_url( wc_get_cart_url() );
?>
<a href="javascript:void(0)" class="button wc-listing-cart" data-product_id="<?php echo $listing->ID;?>" data-price="<?php echo esc_attr( $price ); ?>" data-quantity="1" data-product_sku="" tabindex="0" data-checkout="<?php echo esc_attr( $checkout ); ?>"><?php echo $args['text'];?></a>
<?php }else{ ?>
	<form id="<?php echo esc_attr( $form_id ); ?>" class="cl_listing_purchase_form cl_purchase_<?php echo absint( esc_attr( $listing->ID ) ); ?>" method="post">
	<div class="cl_purchase_submit_wrapper">
		<?php
		$class = implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) );

		if ( ! cl_is_ajax_disabled() ) {
			$timestamp = time();
			echo '<a href="#" class="cl-add-to-cart ' . esc_attr( $class ) . '" data-nonce="' . wp_create_nonce( 'cl-add-to-cart-' . esc_attr( $listing->ID ) ) . '" data-timestamp="' . esc_attr( $timestamp ) . '" data-token="' . esc_attr( \Essential\Restate\Front\Purchase\Tokenizer\Tokenizer::tokenize( $timestamp ) ) . '" data-action="cl_add_to_cart" data-listing-id="' . esc_attr( $listing->ID ) . '" ' . $data_variable . ' ' . $type . ' ' . $data_price . ' ' . $button_display . '><span class="cl-add-to-cart-label">' . $args['text'] . '</span> <span class="cl-loading" aria-label="' . esc_attr__( 'Loading', 'essential-wp-real-estate' ) . '"></span></a>';
		}

		echo '<input type="submit" class="cl-add-to-cart cl-no-js ' . esc_attr( $class ) . '" name="cl_purchase_listing" value="' . esc_attr( $args['text'] ) . '" data-action="cl_add_to_cart" data-listing-id="' . esc_attr( $listing->ID ) . '" ' . $data_variable . ' ' . $type . ' ' . $button_display . '/>';
		echo '<a href="' . esc_url( cl_get_checkout_uri() ) . '" class="cl_go_to_checkout ' . esc_attr( $class ) . '" ' . $checkout_display . '>' . esc_html( $args['checkout'] ) . '</a>';
		echo '<span class="cl-cart-meta"></span>';
		?>

		<?php if ( ! cl_is_ajax_disabled() ) : ?>
			<span class="cl-cart-ajax-alert" aria-live="assertive">
				<span class="cl-cart-added-alert" style="display: none;">
					<svg class="cl-icon cl-icon-check" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" aria-hidden="true">
						<path d="M26.11 8.844c0 .39-.157.78-.44 1.062L12.234 23.344c-.28.28-.672.438-1.062.438s-.78-.156-1.06-.438l-7.782-7.78c-.28-.282-.438-.673-.438-1.063s.156-.78.438-1.06l2.125-2.126c.28-.28.672-.438 1.062-.438s.78.156 1.062.438l4.594 4.61L21.42 5.656c.282-.28.673-.438 1.063-.438s.78.155 1.062.437l2.125 2.125c.28.28.438.672.438 1.062z" />
					</svg>
					<?php _e( 'Added to cart', 'essential-wp-real-estate' ); ?>
				</span>
			</span>
		<?php endif; ?>
		<?php if ( ! $listing->is_free && ! cl_listing_is_tax_exclusive( $listing->ID ) ) : ?>
			<?php
			if ( cl_display_tax_rate() && WPERECCP()->front->tax->cl_prices_include_tax() ) {
				echo '<span class="cl_purchase_tax_rate">' . sprintf( __( 'Includes %1$s&#37; tax', 'essential-wp-real-estate' ), cl_get_tax_rate() * 100 ) . '</span>';
			} elseif ( cl_display_tax_rate() && ! WPERECCP()->front->tax->cl_prices_include_tax() ) {
				echo '<span class="cl_purchase_tax_rate">' . sprintf( __( 'Excluding %1$s&#37; tax', 'essential-wp-real-estate' ), cl_get_tax_rate() * 100 ) . '</span>';
			}
			?>
		<?php endif; ?>
	</div>
	<!--end .cl_purchase_submit_wrapper-->

	<input type="hidden" name="listing_id" value="<?php echo esc_attr( $listing->ID ); ?>">
	<?php if ( $variable_pricing && isset( $price_id ) && isset( $prices[ $price_id ] ) ) : ?>
		<input type="hidden" name="cl_options[price_id][]" id="cl_price_option_<?php echo esc_attr( $listing->ID ); ?>_<?php echo esc_attr( $price_id ); ?>" class="cl_price_option_<?php echo esc_attr( $listing->ID ); ?>" value="<?php echo esc_attr( $price_id ); ?>">
	<?php endif; ?>
	<?php if ( ! empty( $args['direct'] ) && ! $listing->is_free( $args['price_id'] ) ) { ?>
		<input type="hidden" name="cl_action" class="cl_action_input" value="straight_to_gateway">
	<?php } else { ?>
		<input type="hidden" name="cl_action" class="cl_action_input" value="add_to_cart">
	<?php } ?>

	<?php if ( apply_filters( 'cl_listing_redirect_to_checkout', WPERECCP()->common->options->cl_straight_to_checkout(), $listing->ID, $args ) ) : ?>
		<input type="hidden" name="cl_redirect_to_checkout" class="cl_redirect_to_checkout" value="1">
	<?php endif; ?>

	<?php do_action( 'cl_purchase_link_end', $listing->ID, $args ); ?>
	</form>
<?php } ?>