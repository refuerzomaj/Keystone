<?php
use Essential\Restate\Front\Purchase\Discount\DiscountAction;


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2><?php _e( 'Add New Discount', 'essential-wp-real-estate' ); ?></h2>

<?php if ( isset( $_GET['cl_discount_added'] ) ) : ?>
	<div id="message" class="updated">
		<p><strong><?php _e( 'Discount code created.', 'essential-wp-real-estate' ); ?></strong></p>

		<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cl_cpt&page=cl_discounts' ) ); ?>"><?php _e( '&larr; Back to Discounts', 'essential-wp-real-estate' ); ?></a></p>
	</div>
<?php endif; ?>

<form id="cl-add-discount" action="" method="POST">
	<?php do_action( 'cl_add_discount_form_top' ); ?>
	<table class="form-table">
		<tbody>
			<?php do_action( 'cl_add_discount_form_before_name' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-name"><?php _e( 'Name', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input name="name" required="required" id="cl-name" type="text" value="" />
					<p class="description"><?php _e( 'The name of this discount.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_add_discount_form_before_code' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-code"><?php _e( 'Code', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" id="cl-code" name="code" value="" pattern="[a-zA-Z0-9-_]+" />
					<p class="description"><?php _e( 'Enter a code for this discount, such as 10PERCENT. Only alphanumeric characters are allowed.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_add_discount_form_before_type' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-type"><?php _e( 'Type', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<select name="type" id="cl-type">
						<option value="percent"><?php _e( 'Percentage', 'essential-wp-real-estate' ); ?></option>
						<option value="flat"><?php _e( 'Flat amount', 'essential-wp-real-estate' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The kind of discount to apply for this discount.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_add_discount_form_before_amount' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-amount"><?php _e( 'Amount', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" class="cl-price-field" id="cl-amount" name="amount" value="" />
					<p class="description cl-amount-description flat-discount" style="display:none;"><?php printf( __( 'Enter the discount amount in %s', 'essential-wp-real-estate' ), WPERECCP()->common->options->cl_get_currency() ); ?></p>
					<p class="description cl-amount-description percent-discount"><?php _e( 'Enter the discount percentage. 10 = 10%', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_add_discount_form_before_products' ); ?>

			<?php do_action( 'cl_add_discount_form_before_excluded_products' ); ?>
			<?php do_action( 'cl_add_discount_form_before_start' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-start"><?php _e( 'Start date', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input name="start" id="cl-start" type="date" value="" class="" />
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_add_discount_form_before_expiration' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-expiration"><?php _e( 'Expiration date', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="cl-expiration" type="date" class="" />
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_add_discount_form_before_min_cart_amount' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-min-cart-amount"><?php _e( 'Minimum Amount', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="text" id="cl-min-cart-amount" name="min_price" value="" />
					<p class="description"><?php _e( 'The minimum dollar amount that must be in the cart before this discount can be used. Leave blank for no minimum.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_add_discount_form_before_max_uses' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-max-uses"><?php _e( 'Max Uses', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="text" id="cl-max-uses" name="max" value="" />
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_add_discount_form_before_use_once' ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-use-once"><?php _e( 'Use Once Per Customer', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="cl-use-once" name="use_once" value="1" />
					<span class="description"><?php _e( 'Limit this discount to a single-use per customer?', 'essential-wp-real-estate' ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'cl_add_discount_form_bottom' ); ?>
	<p class="submit">
		<input type="hidden" name="cl-action" value="add_discount" />
		<input type="hidden" name="cl-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=cl_cpt&page=cl_discounts' ) ); ?>" />
		<input type="hidden" name="cl-discount-nonce" value="<?php echo wp_create_nonce( 'cl_discount_nonce' ); ?>" />
		<input type="submit" value="<?php _e( 'Add Discount Code', 'essential-wp-real-estate' ); ?>" class="button-primary" />
	</p>
</form>
