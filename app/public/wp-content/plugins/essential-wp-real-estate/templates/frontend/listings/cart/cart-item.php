<li class="cl-cart-item">
    <div class="cart-item-details">
        <span class="cl-cart-item-title">{item_title}</span></span><?php echo WPERECCP()->common->options->cl_item_quantities_enabled() ? '<span class="cl-cart-item-quantity">{item_quantity}&nbsp;@&nbsp;</span>' : ''; ?><span class="cl-cart-item-price">{item_amount}</span>
    </div>
    <a href="{remove_url}" data-nonce="<?php echo wp_create_nonce('cl-remove-cart-item'); ?>" data-cart-item="{cart_item_id}" data-listing-id="{item_id}" data-action="cl_remove_from_cart" class="cl-remove-from-cart"><i class="fas fa-trash-alt"></i></a>
</li>