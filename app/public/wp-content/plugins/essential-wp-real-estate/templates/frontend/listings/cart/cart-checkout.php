<?php if ( WPERECCP()->front->tax->cl_use_taxes() ) : ?>
	<li class="cart_item cl-cart-meta cl_subtotal"><?php echo __( 'Subtotal:', 'essential-wp-real-estate' ) . " <span class='subtotal'>" . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->cart->get_subtotal() ) ); ?></span></li>
	<li class="cart_item cl-cart-meta cl_cart_tax"><?php _e( 'Estimated Tax:', 'essential-wp-real-estate' ); ?> <span class="cart-tax"><?php echo WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->cart->get_tax() ) ); ?></span></li>
<?php endif; ?>
<li class="cart_item cl-cart-meta cl_total"><?php _e( 'Total:', 'essential-wp-real-estate' ); ?> <span class="cl_cart_amount"><?php echo WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->cart->get_total() ) ); ?></span></li>
<?php
if ( ! cl_is_checkout() ) {
	?>
	<li class="cart_item cl_checkout"><a href="<?php echo cl_get_checkout_uri(); ?>"><?php _e( 'Checkout', 'essential-wp-real-estate' ); ?></a></li>
	<?php
}
