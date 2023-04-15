<?php
/**
 * This template is used to display the purchase summary with [cl_receipt]
 */

$payment = get_post( $cl_receipt_args['id'] );
if ( empty( $payment ) ) : ?>
	<div class="cl_errors cl-alert cl-alert-error">
		<?php _e( 'The specified receipt ID appears to be invalid', 'essential-wp-real-estate' ); ?>
	</div>
	<?php
	return;
endif;

$meta   = cl_get_payment_meta( $payment->ID );
$cart   = cl_get_payment_meta_cart_details( $payment->ID, true );
$user   = cl_get_payment_meta_user_info( $payment->ID );
$email  = cl_get_payment_user_email( $payment->ID );
$status = cl_get_payment_status( $payment, true );
?>
<table id="cl_purchase_receipt" class="cl-table">
	<thead>
		<?php do_action( 'cl_payment_receipt_before', $payment, $cl_receipt_args ); ?>

		<?php if ( filter_var( $cl_receipt_args['payment_id'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<th><strong><?php _e( 'Payment', 'essential-wp-real-estate' ); ?>:</strong></th>
				<th><?php echo cl_get_payment_number( $payment->ID ); ?></th>
			</tr>
		<?php endif; ?>
	</thead>

	<tbody>

		<tr>
			<td class="cl_receipt_payment_status"><strong><?php _e( 'Payment Status', 'essential-wp-real-estate' ); ?>:</strong></td>
			<td class="cl_receipt_payment_status <?php echo strtolower( $status ); ?>"><?php echo esc_html( $status ); ?></td>
		</tr>

		<?php if ( filter_var( $cl_receipt_args['payment_key'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php _e( 'Payment Key', 'essential-wp-real-estate' ); ?>:</strong></td>
				<td><?php echo cl_get_payment_meta( $payment->ID, '_cl_payment_purchase_key', true ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $cl_receipt_args['payment_method'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php _e( 'Payment Method', 'essential-wp-real-estate' ); ?>:</strong></td>
				<td><?php echo WPERECCP()->front->gateways->cl_get_gateway_checkout_label( cl_get_payment_gateway( $payment->ID ) ); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( filter_var( $cl_receipt_args['date'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php _e( 'Date', 'essential-wp-real-estate' ); ?>:</strong></td>
				<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $meta['date'] ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( ( $fees = cl_get_payment_fees( $payment->ID, 'fee' ) ) ) : ?>
			<tr>
				<td><strong><?php _e( 'Fees', 'essential-wp-real-estate' ); ?>:</strong></td>
				<td>
					<ul class="cl_receipt_fees">
						<?php foreach ( $fees as $fee ) : ?>
							<li>
								<span class="cl_fee_label"><?php echo esc_html( $fee['label'] ); ?></span>
								<span class="cl_fee_sep">&nbsp;&ndash;&nbsp;</span>
								<span class="cl_fee_amount"><?php echo WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $fee['amount'] ) ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $cl_receipt_args['discount'], FILTER_VALIDATE_BOOLEAN ) && isset( $user['discount'] ) && $user['discount'] != 'none' ) : ?>
			<tr>
				<td><strong><?php _e( 'Discount(s)', 'essential-wp-real-estate' ); ?>:</strong></td>
				<td><?php echo esc_html( $user['discount'] ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( WPERECCP()->front->tax->cl_use_taxes() ) : ?>
			<tr>
				<td><strong><?php _e( 'Tax', 'essential-wp-real-estate' ); ?>:</strong></td>
				<td><?php echo cl_payment_tax( $payment->ID ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $cl_receipt_args['price'], FILTER_VALIDATE_BOOLEAN ) ) : ?>

			<tr>
				<td><strong><?php _e( 'Subtotal', 'essential-wp-real-estate' ); ?>:</strong></td>
				<td>
					<?php echo cl_payment_subtotal( $payment->ID ); ?>
				</td>
			</tr>

			<tr>
				<td><strong><?php _e( 'Total Price', 'essential-wp-real-estate' ); ?>:</strong></td>
				<td><?php echo cl_payment_amount( $payment->ID ); ?></td>
			</tr>

		<?php endif; ?>

		<?php do_action( 'cl_payment_receipt_after', $payment, $cl_receipt_args ); ?>
	</tbody>
</table>

<?php do_action( 'cl_payment_receipt_after_table', $payment, $cl_receipt_args ); ?>

<?php if ( filter_var( $cl_receipt_args['products'], FILTER_VALIDATE_BOOLEAN ) ) : ?>

	<h3><?php echo apply_filters( 'cl_payment_receipt_products_title', __( 'Products', 'essential-wp-real-estate' ) ); ?></h3>

	<table id="cl_purchase_receipt_products" class="cl-table">
		<thead>
			<th><?php _e( 'Name', 'essential-wp-real-estate' ); ?></th>
			<?php if ( WPERECCP()->common->options->cl_use_skus() ) { ?>
				<th><?php _e( 'SKU', 'essential-wp-real-estate' ); ?></th>
			<?php } ?>
			<?php if ( cl_item_quantities_enabled() ) : ?>
				<th><?php _e( 'Quantity', 'essential-wp-real-estate' ); ?></th>
			<?php endif; ?>
			<th><?php _e( 'Price', 'essential-wp-real-estate' ); ?></th>
		</thead>

		<tbody>
			<?php if ( $cart ) : ?>
				<?php foreach ( $cart as $key => $item ) : ?>

					<?php if ( ! apply_filters( 'cl_user_can_view_receipt_item', true, $item ) ) : ?>
						<?php
						continue; // Skip this item if can't view it
						?>
					<?php endif; ?>

					<?php if ( empty( $item['in_bundle'] ) ) : ?>
						<tr>
							<td>
								<?php
								$price_id = cl_get_cart_item_price_id( $item );
								?>

								<div class="cl_purchase_receipt_product_name">
									<?php echo esc_html( $item['name'] ); ?>
									<?php if ( WPERECCP()->front->listing_provider->cl_has_variable_prices( $item['id'] ) && ! is_null( $price_id ) ) : ?>
										<span class="cl_purchase_receipt_price_name">&nbsp;&ndash;&nbsp;<?php echo esc_html( cl_get_price_option_name( $item['id'], $price_id, $payment->ID ) ); ?></span>
									<?php endif; ?>
								</div>

								<?php
								$notes = WPERECCP()->front->listingsaction->cl_get_product_notes( $item['id'] );
								if ( $cl_receipt_args['notes'] && ! empty( $notes ) ) :
									?>
									<div class="cl_purchase_receipt_product_notes"><?php echo wp_kses_post( wpautop( $notes ) ); ?></div>
								<?php endif; ?>

															</td>
							<?php if ( WPERECCP()->common->options->cl_use_skus() ) : ?>
								<td><?php echo cl_get_listing_sku( $item['id'] ); ?></td>
							<?php endif; ?>
							<?php if ( cl_item_quantities_enabled() ) { ?>
								<td><?php echo esc_html( $item['quantity'] ); ?></td>
							<?php } ?>
							<td>
								<?php
								if ( empty( $item['in_bundle'] ) ) : // Only show price when product is not part of a bundle
									?>
									<?php echo WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $item['price'] ) ); ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if ( ( $fees = cl_get_payment_fees( $payment->ID, 'item' ) ) ) : ?>
				<?php foreach ( $fees as $fee ) : ?>
					<tr>
						<td class="cl_fee_label"><?php echo esc_html( $fee['label'] ); ?></td>
						<?php if ( cl_item_quantities_enabled() ) : ?>
							<td></td>
						<?php endif; ?>
						<td class="cl_fee_amount"><?php echo WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $fee['amount'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>

	</table>
<?php endif; ?>
