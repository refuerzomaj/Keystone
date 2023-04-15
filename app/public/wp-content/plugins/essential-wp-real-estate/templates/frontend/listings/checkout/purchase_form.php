<?php
$payment_mode = WPERECCP()->front->gateways->cl_get_chosen_gateway();

/**
 * Hooks in at the top of the purchase form
 *
 * @since 1.4
 */
do_action('cl_purchase_form_top');

if (WPERECCP()->front->checkout->cl_can_checkout()) {

    do_action('cl_purchase_form_before_register_login');

    $show_register_form = cl_admin_get_option('show_register_form', 'none');
    if (($show_register_form === 'registration' || ($show_register_form === 'both' && !isset($_GET['login']))) && !is_user_logged_in()) : ?>
        <div id="cl_checkout_login_register">
            <?php do_action('cl_purchase_form_register_fields'); ?>
        </div>
    <?php elseif (($show_register_form === 'login' || ($show_register_form === 'both' && isset($_GET['login']))) && !is_user_logged_in()) : ?>
        <div id="cl_checkout_login_register">
            <?php do_action('cl_purchase_form_login_fields'); ?>
        </div>
    <?php endif; ?>

<?php if ((!isset($_GET['login']) && is_user_logged_in()) || !isset($show_register_form) || 'none' === $show_register_form || 'login' === $show_register_form) {
        do_action('cl_purchase_form_after_user_info');
    }

    /**
     * Hooks in before Credit Card Form
     *
     * @since 1.4
     */
    do_action('cl_purchase_form_before_cc_form');

    if (WPERECCP()->front->cart->get_total() > 0) {

        // Load the credit card form and allow gateways to load their own if they wish
        if (has_action('cl_' . $payment_mode . '_cc_form')) {
            do_action('cl_' . $payment_mode . '_cc_form');
        } else {
            do_action('cl_cc_form');
        }
    }

    /**
     * Hooks in after Credit Card Form
     *
     * @since 1.4
     */
    do_action('cl_purchase_form_after_cc_form');
} else {
    // Can't checkout
    do_action('cl_purchase_form_no_access');
}

/**
 * Hooks in at the bottom of the purchase form
 *
 * @since 1.4
 */
do_action('cl_purchase_form_bottom');
