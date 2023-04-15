<?php
/**
 * This template is used to display the listings cart widget.
 */
$cart_items    = WPERECCP()->front->cart->get_contents();
$cart_quantity = WPERECCP()->front->cart->get_quantity();
$display       = $cart_quantity > 0 ? '' : ' style="display:none;"';
?>
<p class="cl-cart-number-of-items" <?php echo esc_attr( $display ); ?>><?php _e( 'Number of items in cart', 'essential-wp-real-estate' ); ?>: <span class="cl-cart-quantity"><?php echo esc_html( $cart_quantity ); ?></span></p>
<ul class="cl-cart">
	<?php if ( $cart_items ) : ?>
		<?php foreach ( $cart_items as $key => $item ) : ?>
			<?php echo WPERECCP()->front->cart->cl_get_cart_item_template( $key, $item, false ); ?>
		<?php endforeach; ?>
		<?php cl_get_template_with_dir( 'discount-code.php', 'discount' ); ?>
		<?php cl_get_template_with_dir( 'cart-checkout.php', 'cart' ); ?>

	<?php else : ?>
		<?php cl_get_template_with_dir( 'cart-empty.php', 'cart' ); ?>
	<?php endif; ?>
</ul>
