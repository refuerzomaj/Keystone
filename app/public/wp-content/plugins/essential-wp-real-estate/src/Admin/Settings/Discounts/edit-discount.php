<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_GET['discount'] ) || ! is_numeric( $_GET['discount'] ) ) {
	wp_die( __( 'Something went wrong.', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 400 ) );
}

$discount_id       = absint( cl_sanitization( $_GET['discount'] ) );
$discount          = WPERECCP()->admin->discount_action->cl_get_discount( $discount_id );
$product_reqs      = WPERECCP()->admin->discount_action->cl_get_discount_product_reqs( $discount_id );
$excluded_products = WPERECCP()->admin->discount_action->cl_get_discount_excluded_products( $discount_id );
$condition         = WPERECCP()->admin->discount_action->cl_get_discount_product_condition( $discount_id );
$single_use        = WPERECCP()->admin->discount_action->cl_discount_is_single_use( $discount_id );
$flat_display      = WPERECCP()->admin->discount_action->cl_get_discount_type( $discount_id ) == 'flat' ? '' : ' style="display:none;"';
$percent_display   = WPERECCP()->admin->discount_action->cl_get_discount_type( $discount_id ) == 'percent' ? '' : ' style="display:none;"';
$condition_display = empty( $product_reqs ) ? ' style="display:none;"' : '';

function get_date_val( $param ) {
	$date_output = '';
	if ( ! empty( $param ) ) {
		$date        = explode( ' ', $param );
		$formateed   = explode( '/', $date[0] );
		$final_date  = $formateed[2] . '-' . $formateed[0] . '-' . $formateed[1];
		$date_output = $final_date;
	}
	return $date_output;
}


?>
<h2><?php _e( 'Edit Discount', 'essential-wp-real-estate' ); ?></h2>

<?php if ( isset( $_GET['cl_discount_updated'] ) ) : ?>
	<div id="message" class="updated">
		<p><strong><?php _e( 'Discount code updated.', 'essential-wp-real-estate' ); ?></strong></p>

		<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cl_cpt&page=cl_discounts' ) ); ?>"><?php _e( '&larr; Back to Discounts', 'essential-wp-real-estate' ); ?></a></p>
	</div>
<?php endif; ?>

<form id="cl-edit-discount" action="" method="post">
	<?php do_action( 'cl_edit_discount_form_top', $discount_id, $discount ); ?>
	<table class="form-table">
		<tbody>
			<?php do_action( 'cl_edit_discount_form_before_name', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-name"><?php _e( 'Name', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input name="name" required="required" id="cl-name" type="text" value="<?php echo esc_attr( stripslashes( $discount->post_title ) ); ?>" />
					<p class="description"><?php _e( 'The name of this discount', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_edit_discount_form_before_code', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-code"><?php _e( 'Code', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" id="cl-code" name="code" value="<?php echo esc_attr( WPERECCP()->admin->discount_action->cl_get_discount_code( $discount_id ) ); ?>" pattern="[a-zA-Z0-9-_]+" />
					<p class="description"><?php _e( 'Enter a code for this discount, such as 10PERCENT. Only alphanumeric characters are allowed.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_edit_discount_form_before_type', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-type"><?php _e( 'Type', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<select name="type" id="cl-type">
						<option value="percent" <?php selected( WPERECCP()->admin->discount_action->cl_get_discount_type( $discount_id ), 'percent' ); ?>><?php _e( 'Percentage', 'essential-wp-real-estate' ); ?></option>
						<option value="flat" <?php selected( WPERECCP()->admin->discount_action->cl_get_discount_type( $discount_id ), 'flat' ); ?>><?php _e( 'Flat amount', 'essential-wp-real-estate' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The kind of discount to apply for this discount.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_edit_discount_form_before_amount', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-amount"><?php _e( 'Amount', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="text" class="cl-price-field" required="required" id="cl-amount" name="amount" value="<?php echo esc_attr( WPERECCP()->admin->discount_action->cl_get_discount_amount( $discount_id ) ); ?>" />
					<p class="description cl-amount-description flat" <?php echo wp_kses_post($flat_display); ?>><?php printf( __( 'Enter the discount amount in %s', 'essential-wp-real-estate' ), WPERECCP()->common->options->cl_get_currency() ); ?></p>
					<p class="description cl-amount-description percent" <?php echo wp_kses_post($percent_display); ?>><?php _e( 'Enter the discount percentage. 10 = 10%', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_edit_discount_form_before_start', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-start"><?php _e( 'Start date', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input name="start" id="cl-start" type="date" value="<?php echo esc_attr( get_date_val( WPERECCP()->admin->discount_action->cl_get_discount_start_date( $discount_id ) ) ); ?>" />
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_edit_discount_form_before_expiration', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-expiration"><?php _e( 'Expiration date', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input name="expiration" id="cl-expiration" type="date" value="<?php echo esc_attr( get_date_val( WPERECCP()->admin->discount_action->cl_get_discount_expiration( $discount_id ) ) ); ?>" />
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_edit_discount_form_before_max_uses', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-max-uses"><?php _e( 'Max Uses', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="text" id="cl-max-uses" name="max" value="<?php echo esc_attr( WPERECCP()->admin->discount_action->cl_get_discount_max_uses( $discount_id ) ); ?>" />
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_edit_discount_form_before_min_cart_amount', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-min-cart-amount"><?php _e( 'Minimum Amount', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="text" id="cl-min-cart-amount" name="min_price" value="<?php echo esc_attr( WPERECCP()->admin->discount_action->cl_get_discount_min_price( $discount_id ) ); ?>" />
					<p class="description"><?php _e( 'The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_edit_discount_form_before_status', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-status"><?php _e( 'Status', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<select name="status" id="cl-status">
						<option value="active" <?php selected( $discount->post_status, 'active' ); ?>><?php _e( 'Active', 'essential-wp-real-estate' ); ?></option>
						<option value="inactive" <?php selected( $discount->post_status, 'inactive' ); ?>><?php _e( 'Inactive', 'essential-wp-real-estate' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The status of this discount code.', 'essential-wp-real-estate' ); ?></p>
				</td>
			</tr>
			<?php do_action( 'cl_edit_discount_form_before_use_once', $discount_id, $discount ); ?>
			<tr>
				<th scope="row" valign="top">
					<label for="cl-use-once"><?php _e( 'Use Once Per Customer', 'essential-wp-real-estate' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="cl-use-once" name="use_once" value="1" <?php checked( true, $single_use ); ?> />
					<span class="description"><?php _e( 'Limit this discount to a single-use per customer?', 'essential-wp-real-estate' ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'cl_edit_discount_form_bottom', $discount_id, $discount ); ?>
	<p class="submit">
		<input type="hidden" name="cl-action" value="cl_edit_discount" />
		<input type="hidden" name="discount-id" value="<?php echo  esc_attr($discount_id); ?>" />
		<input type="hidden" name="cl-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=cl_cpt&page=cl_discounts&cl-action=edit_discount&discount=' . esc_attr( $discount_id ) ) ); ?>" />
		<input type="hidden" name="cl-discount-nonce" value="<?php echo wp_create_nonce( 'cl_discount_nonce' ); ?>" />
		<input type="submit" value="<?php _e( 'Update Discount Code', 'essential-wp-real-estate' ); ?>" class="button-primary" />
	</p>
</form>
