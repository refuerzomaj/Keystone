<?php
use Essential\Restate\Front\Purchase\Gateways\Gateways;
use Essential\Restate\Front\Purchase\Tax\Tax;
use Essential\Restate\Front\Models\Listings;

$gateways = new Gateways();
$taxs     = new Tax();
$listings = new Listings();
?>
<div class="wrap cl-wrap">
	<h2><?php printf( __( 'Payment %s', 'essential-wp-real-estate' ), $number ); ?></h2>
	<?php do_action( 'cl_view_order_details_before', $payment_id ); ?>
	<form id="cl-edit-order-form" method="post">
		<?php do_action( 'cl_view_order_details_form_top', $payment_id ); ?>
		<div id="poststuff">
			<div id="cl-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php do_action( 'cl_view_order_details_sidebar_before', $payment_id ); ?>


							<div id="cl-order-update" class="postbox cl-order-data">

								<h3 class="hndle">
									<span><?php _e( 'Update Payment', 'essential-wp-real-estate' ); ?></span>
								</h3>
								<div class="inside">
									<div class="cl-admin-box">

										<?php do_action( 'cl_view_order_details_totals_before', $payment_id ); ?>

										<div class="cl-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Status:', 'essential-wp-real-estate' ); ?></span>&nbsp;
												<select name="cl-payment-status" class="medium-text">
													<?php foreach ( cl_get_payment_statuses() as $key => $status ) : ?>
														<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $payment->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
													<?php endforeach; ?>
												</select>

												<?php
												$status_help  = '<ul>';
												$status_help .= '<li>' . __( '<strong>Pending</strong>: payment is still processing or was abandoned by customer. Successful payments will be marked as Complete automatically once processing is finalized.', 'essential-wp-real-estate' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Complete</strong>: all processing is completed for this purchase.', 'essential-wp-real-estate' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Revoked</strong>: access to purchased items is disabled, perhaps due to policy violation or fraud.', 'essential-wp-real-estate' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Refunded</strong>: the purchase amount is returned to the customer and access to items is disabled.', 'essential-wp-real-estate' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Abandoned</strong>: the purchase attempt was not completed by the customer.', 'essential-wp-real-estate' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Failed</strong>: customer clicked Cancel before completing the purchase.', 'essential-wp-real-estate' ) . '</li>';
												$status_help .= '</ul>';
												?>
												<span alt="f223" class="cl-help-tip dashicons dashicons-editor-help" title="<?php echo esc_attr( $status_help ); ?>"></span>
											</p>

											<?php if ( $payment->is_recoverable() ) : ?>
												<p>
													<span class="label"><?php _e( 'Recovery URL', 'essential-wp-real-estate' ); ?>:</span>
													<?php $recover_help = __( 'Pending and abandoned payments can be resumed by the customer, using this custom URL. Payments can be resumed only when they do not have a transaction ID from the gateway.', 'essential-wp-real-estate' ); ?>
													<span alt="f223" class="cl-help-tip dashicons dashicons-editor-help" title="<?php echo esc_attr( $recover_help ); ?>"></span>

													<input type="text" class="large-text" readonly="readonly" value="<?php echo esc_attr( $payment->get_recovery_url() ); ?>" />
												</p>
											<?php endif; ?>
										</div>

										<div class="cl-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Date:', 'essential-wp-real-estate' ); ?></span>&nbsp;
												<input type="text" name="cl-payment-date" value="<?php echo esc_attr( date( 'm/d/Y', $payment_date ) ); ?>" class="medium-text cl_datepicker" />
											</p>
										</div>

										<div class="cl-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Time:', 'essential-wp-real-estate' ); ?></span>&nbsp;
												<input type="text" maxlength="2" name="cl-payment-time-hour" value="<?php echo esc_attr( date_i18n( 'H', $payment_date ) ); ?>" class="small-text cl-payment-time-hour" />&nbsp;:&nbsp;
												<input type="text" maxlength="2" name="cl-payment-time-min" value="<?php echo esc_attr( date( 'i', $payment_date ) ); ?>" class="small-text cl-payment-time-min" />
											</p>
										</div>

										<?php do_action( 'cl_view_order_details_update_inner', $payment_id ); ?>

										<div class="cl-order-discount cl-admin-box-inside">
											<p>
												<?php
												$found_discounts = array();
												if ( 'none' !== $payment->discounts ) {
													$discounts = $payment->discounts;
													if ( ! is_array( $discounts ) ) {
														$discounts = explode( ',', $discounts );
													}

													foreach ( $discounts as $discount ) {
														$discount_obj = WPERECCP()->admin->discount_action->cl_get_discount_by_code( $discount );

														if ( false === $discount_obj ) {
															$found_discounts[] = $discount;
														} else {
															$found_discounts[] = '<a href="' . esc_url( $discount_obj->edit_url() ) . '">' . esc_html( $discount_obj->code ) . '</a>';
														}
													}
												}
												?>
												<span class="label">
													<?php echo _n( 'Discount Code', 'Discount Codes', count( $found_discounts ), 'essential-wp-real-estate' ); ?>:
												</span>&nbsp;
												<span>
													<?php
													if ( ! empty( $found_discounts ) ) {
														echo implode( ', ', $found_discounts );
													} else {
														_e( 'None', 'essential-wp-real-estate' );
													}
													?>
												</span>
											</p>
										</div>

										<?php
										$fees = $payment->fees;
										if ( ! empty( $fees ) ) :
											?>
											<div class="cl-order-fees cl-admin-box-inside">
												<p class="strong"><?php _e( 'Fees', 'essential-wp-real-estate' ); ?>:</p>
												<ul class="cl-payment-fees">
													<?php foreach ( $fees as $fee ) : ?>
														<li data-fee-id="<?php echo esc_attr( $fee['id'] ); ?>"><span class="fee-label"><?php echo esc_html( $fee['label'] ) . ':</span> ' . '<span class="fee-amount" data-fee="' . esc_attr( $fee['amount'] ) . '">' . WPERECCP()->common->formatting->cl_currency_filter( $fee['amount'], $currency_code ); ?></span></li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>

										<?php
										$ret = cl_admin_get_option( 'enable_taxes', false );
										$ret = isset( $ret ) && ( $ret == 1 ) ? true : false;

										if ( $ret ) :
											?>
											<div class="cl-order-taxes cl-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Tax', 'essential-wp-real-estate' ); ?>:</span>&nbsp;
													<input name="cl-payment-tax" class="med-text" type="text" value="<?php echo esc_attr( WPERECCP()->common->formatting->cl_format_amount( $payment->tax ) ); ?>" />
													<?php if ( ! empty( $payment->tax_rate ) ) : ?>
														<span class="cl-tax-rate">
															&nbsp;<?php echo esc_html( $payment->tax_rate ) * 100; ?>%
														</span>
													<?php endif; ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="cl-order-payment cl-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Total Price', 'essential-wp-real-estate' ); ?>:</span>&nbsp;
												<?php echo WPERECCP()->common->currencies->cl_currency_symbol( $payment->currency ); ?>&nbsp;<input name="cl-payment-total" type="text" class="med-text" value="<?php echo esc_attr( WPERECCP()->common->formatting->cl_format_amount( $payment->total ) ); ?>" />
											</p>
										</div>

										<div class="cl-order-payment-recalc-totals cl-admin-box-inside" style="display:none">
											<p>
												<span class="label"><?php _e( 'Recalculate Totals', 'essential-wp-real-estate' ); ?>:</span>&nbsp;
												<a href="" id="cl-order-recalc-total" class="button button-secondary right"><?php _e( 'Recalculate', 'essential-wp-real-estate' ); ?></a>
											</p>
										</div>

										<?php do_action( 'cl_view_order_details_totals_after', $payment_id ); ?>

									</div><!-- /.cl-admin-box -->

								</div><!-- /.inside -->

								<div class="cl-order-update-box cl-admin-box">
									<?php do_action( 'cl_view_order_details_update_before', $payment_id ); ?>
									<div id="major-publishing-actions">
										<div id="delete-action">
											<a href="
											<?php
											echo wp_nonce_url(
												add_query_arg(
													array(
														'cl-action'   => 'delete_payment',
														'purchase_id' => $payment_id,
													),
													admin_url( 'edit.php?post_type=listing&page=cl-payment-history' )
												),
												'cl_payment_nonce'
											);
											?>
											" class="cl-delete-payment cl-delete"><?php _e( 'Delete Payment', 'essential-wp-real-estate' ); ?></a>
										</div>
										<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Payment', 'essential-wp-real-estate' ); ?>" />
										<div class="clear"></div>
									</div>
									<?php do_action( 'cl_view_order_details_update_after', $payment_id ); ?>
								</div><!-- /.cl-order-update-box -->

							</div><!-- /#cl-order-data -->

							<?php if ( cl_is_payment_complete( $payment_id ) ) : ?>
								<?php
								if ( count( $customer->emails ) > 1 ) {
									$email_id = 'cl-select-receipt-email';
								} else {
									$email_id = 'cl-resend-receipt';
								}
								$email_links = esc_url(
									add_query_arg(
										array(
											'cl-action'   => 'email_links',
											'purchase_id' => $payment_id,
										)
									)
								);
								?>
								<div id="cl-order-resend-receipt" class="postbox cl-order-data">
									<div class="inside">
										<div class="cl-order-resend-receipt-box cl-admin-box">
											<?php do_action( 'cl_view_order_details_resend_receipt_before', $payment_id ); ?>
											<a href="<?php echo esc_url( $email_links ); ?>" id="<?php echo esc_attr( $email_id ); ?>" class="button-secondary alignleft"><?php _e( 'Resend Receipt', 'essential-wp-real-estate' ); ?></a>
											<span alt="f223" class="cl-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Resend Receipt</strong>: This will send a new copy of the purchase receipt to the customer&#8217;s email address. If listing URLs are included in the receipt, new file listing URLs will also be included with the receipt.', 'essential-wp-real-estate' ); ?>"></span>
											<?php if ( count( $customer->emails ) > 1 ) : ?>
												<div class="clear"></div>
												<div class="cl-order-resend-receipt-addresses" style="display:none;">
													<select class="cl-order-resend-receipt-email">
														<option value=""><?php _e( ' -- select email --', 'essential-wp-real-estate' ); ?></option>
														<?php foreach ( $customer->emails as $email ) : ?>
															<option value="<?php echo urlencode( sanitize_email( $email ) ); ?>"><?php echo esc_html( $email ); ?></option>
														<?php endforeach; ?>
													</select>
												</div>
											<?php endif; ?>
											<div class="clear"></div>
											<?php do_action( 'cl_view_order_details_resend_receipt_after', $payment_id ); ?>
										</div><!-- /.cl-order-resend-receipt-box -->
									</div>
								</div>
							<?php endif; ?>

							<div id="cl-order-details" class="postbox cl-order-data">

								<h3 class="hndle">
									<span><?php _e( 'Payment Meta', 'essential-wp-real-estate' ); ?></span>
								</h3>
								<div class="inside">
									<div class="cl-admin-box">

										<?php do_action( 'cl_view_order_details_payment_meta_before', $payment_id ); ?>

										<?php
										if ( $gateway ) :
											?>
											<div class="cl-order-gateway cl-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Gateway:', 'essential-wp-real-estate' ); ?></span>&nbsp;
													<?php echo esc_html( $gateways->cl_get_gateway_admin_label( $gateway ) ); ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="cl-order-payment-key cl-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Key:', 'essential-wp-real-estate' ); ?></span>&nbsp;
												<span><?php echo esc_html( $payment->key ); ?></span>
											</p>
										</div>

										<div class="cl-order-ip cl-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'IP:', 'essential-wp-real-estate' ); ?></span>&nbsp;
												<span><?php echo cl_payment_get_ip_address_url( $payment_id ); ?></span>
											</p>
										</div>

										<?php if ( $transaction_id ) : ?>
											<div class="cl-order-tx-id cl-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Transaction ID:', 'essential-wp-real-estate' ); ?></span>&nbsp;
													<span><?php echo apply_filters( 'cl_payment_details_transaction_id-' . $gateway, $transaction_id, $payment_id ); ?></span>
												</p>
											</div>
										<?php endif; ?>

										<div class="cl-unlimited-listings cl-admin-box-inside">
											<p>
												<span class="label"><i data-code="f316" class="dashicons dashicons-listing"></i></span>&nbsp;
												<input type="checkbox" name="cl-unlimited-listings" id="cl_unlimited_listings" value="1" <?php checked( true, $unlimited, true ); ?> />
												<label class="description" for="cl_unlimited_listings"><?php _e( 'Unlimited file listings', 'essential-wp-real-estate' ); ?></label>
												<span alt="f223" class="cl-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Unlimited file listings</strong>: checking this box will override all other file listing limits for this purchase, granting the customer unliimited listings of all files included on the purchase.', 'essential-wp-real-estate' ); ?>"></span>
											</p>
										</div>

										<?php do_action( 'cl_view_order_details_payment_meta_after', $payment_id ); ?>

									</div><!-- /.column-container -->

								</div><!-- /.inside -->

							</div><!-- /#cl-order-data -->

							<div id="cl-order-logs" class="postbox cl-order-logs">

								<h3 class="hndle">
									<span><?php _e( 'Logs', 'essential-wp-real-estate' ); ?></span>
								</h3>
								<div class="inside">
									<div class="cl-admin-box">
										<div class="cl-admin-box-inside">
											<p>
												<a href="<?php echo admin_url( '/edit.php?post_type=listing&page=cl-reports&tab=logs&payment=' . esc_attr( $payment_id ) ); ?>"><?php _e( 'View file listing log for purchase', 'essential-wp-real-estate' ); ?></a>
											</p>
											<p>
												<?php $listing_log_url = admin_url( 'edit.php?post_type=listing&page=cl-reports&tab=logs&customer=' . esc_attr( $customer->id ) ); ?>
												<a href="<?php echo esc_url( $listing_log_url ); ?>"><?php _e( 'View customer listing log', 'essential-wp-real-estate' ); ?></a>
											</p>
											<p>
												<?php $purchase_url = admin_url( 'edit.php?post_type=listing&page=cl-payment-history&user=' . esc_attr( cl_get_payment_user_email( $payment_id ) ) ); ?>
												<a href="<?php echo esc_url( $purchase_url ); ?>"><?php _e( 'View all purchases of customer', 'essential-wp-real-estate' ); ?></a>
											</p>
										</div>
										<?php do_action( 'cl_view_order_details_logs_inner', $payment_id ); ?>

									</div><!-- /.column-container -->

								</div><!-- /.inside -->

							</div><!-- /#cl-order-logs -->

							<?php do_action( 'cl_view_order_details_sidebar_after', $payment_id ); ?>

						</div><!-- /#side-sortables -->
					</div><!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<?php do_action( 'cl_view_order_details_main_before', $payment_id ); ?>
							<?php $column_count = $taxs->cl_use_taxes() ? 'columns-5' : 'columns-4'; ?>
							<?php
							// If there are no cart items, add a paceholder product so that it can be duplicated through JS, which is how proeucts are added to orders.
							if ( empty( $cart_items ) ) {
								$cart_items = array(
									array(
										'name'        => __( 'No listing attached to this order', 'essential-wp-real-estate' ),
										'id'          => 0,
										'item_number' => array(
											'id'       => 0,
											'quantity' => 0,
											'options'  => array(
												'quantity' => 0,
												'price_id' => 0,
											),
										),
										'item_price'  => 0,
										'quantity'    => 0,
										'discount'    => 0,
										'subtotal'    => 0,
										'tax'         => 0,
										'fees'        => array(),
										'price'       => 0,
									),
								);

								$cart_items_existed = false;
							} else {
								$cart_items_existed = true;
							}

							if ( is_array( $cart_items ) ) :
								$is_qty_enabled = cl_item_quantities_enabled() ? ' item_quantity' : '';
								?>
								<div id="cl-purchased-files" class="postbox cl-edit-purchase-element <?php echo esc_attr( $column_count ); ?>">
									<h3 class="hndle cl-payment-details-label-mobile">
										<span><?php printf( __( 'Purchased %s', 'essential-wp-real-estate' ), cl_get_label_plural() ); ?></span>
									</h3>
									<div class="cl-purchased-files-header row header">
										<ul class="cl-purchased-files-list-wrapper">
											<li class="listing">
												<?php printf( _x( '%s Purchased', 'payment details purchased item title - full screen', 'essential-wp-real-estate' ), cl_get_label_singular() ); ?>
											</li>
											<li class="item_price<?php echo esc_attr( $is_qty_enabled ); ?>">
												<?php
												_ex( 'Price', 'payment details purchased item price - full screen', 'essential-wp-real-estate' );
												if ( cl_item_quantities_enabled() ) :
													_ex( ' & Quantity', 'payment details purchased item quantity - full screen', 'essential-wp-real-estate' );
												endif;
												?>
											</li>
											<?php if ( $taxs->cl_use_taxes() ) : ?>
												<li class="item_tax">
													<?php _ex( 'Tax', 'payment details purchased item tax - full screen', 'essential-wp-real-estate' ); ?>
												</li>
											<?php endif; ?>

											<li class="price">
												<?php printf( _x( '%s Total', 'payment details purchased item total - full screen', 'essential-wp-real-estate' ), cl_get_label_singular() ); ?>
											</li>
										</ul>
									</div>
									<?php
									$i = 0;
									foreach ( $cart_items as $key => $cart_item ) :
										?>
										<div class="row">
											<ul class="cl-purchased-files-list-wrapper">
												<?php
												// Item ID is checked if isset due to the near-1.0 cart data
												$item_id    = isset( $cart_item['id'] ) ? $cart_item['id'] : $cart_item;
												$price      = isset( $cart_item['price'] ) ? $cart_item['price'] : false;
												$item_price = isset( $cart_item['item_price'] ) ? $cart_item['item_price'] : $price;
												$subtotal   = isset( $cart_item['subtotal'] ) ? $cart_item['subtotal'] : $price;
												$item_tax   = isset( $cart_item['tax'] ) ? $cart_item['tax'] : 0;
												$price_id   = isset( $cart_item['item_number']['options']['price_id'] ) ? $cart_item['item_number']['options']['price_id'] : null;
												$quantity   = isset( $cart_item['quantity'] ) && $cart_item['quantity'] > 0 ? $cart_item['quantity'] : 1;
												$listing    = new Listings( $item_id );
												if ( false === $price ) {

													// This function is only used on payments with near 1.0 cart data structure
													$price = cl_get_listing_final_price( $item_id, $user_info, null );
												}
												?>

												<li class="listing">
													<span class="cl-purchased-listing-title">
														<?php if ( ! empty( $listing->ID ) ) : ?>
															<a href="<?php echo esc_url( admin_url( 'post.php?post=' . esc_attr( $item_id ) . '&action=edit' ) ); ?>">
																<?php
																echo esc_html( $listing->get_name() );
																if ( isset( $cart_items[ $key ]['item_number'] ) && isset( $cart_items[ $key ]['item_number']['options'] ) ) {
																	$price_options = $cart_items[ $key ]['item_number']['options'];
																	if ( $listing->cl_has_variable_prices( $item_id ) && isset( $price_id ) ) {
																		echo ' - ' . $listing->cl_get_price_option_name( $item_id, $price_id, $payment_id );
																	}
																}
																?>
															</a>
														<?php else : ?>
															<span class="deleted">
																<?php
																if ( ! $cart_items_existed ) {
																	echo esc_html( $cart_item['name'] );
																} elseif ( ! empty( $cart_item['name'] ) ) {
																	echo esc_html( $cart_item['name'] );
																	?>
																	&nbsp;-&nbsp;
																<em>(<?php _e( 'Deleted', 'essential-wp-real-estate' ); ?>)</em>
															<?php } else { ?>
																<em><?php printf( __( '%s deleted', 'essential-wp-real-estate' ), cl_get_label_singular() ); ?></em>
															<?php } ?>
															</span>
														<?php endif; ?>
													</span>
													<input type="hidden" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][id]" class="cl-payment-details-listing-id" value="<?php echo esc_attr( $item_id ); ?>" />
													<input type="hidden" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][price_id]" class="cl-payment-details-listing-price-id" value="<?php echo esc_attr( $price_id ); ?>" />

													<?php if ( ! cl_item_quantities_enabled() ) : ?>
														<input type="hidden" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][quantity]" class="cl-payment-details-listing-quantity" value="<?php echo esc_attr( $quantity ); ?>" />
													<?php endif; ?>

													<?php if ( ! $taxs->cl_use_taxes() ) : ?>
														<input type="hidden" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][item_tax]" class="cl-payment-details-listing-item-tax" value="<?php echo esc_attr( $item_tax ); ?>" />
													<?php endif; ?>

													<?php if ( ! empty( $cart_items[ $key ]['fees'] ) ) : ?>
														<?php $fees = array_keys( $cart_items[ $key ]['fees'] ); ?>
														<input type="hidden" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][fees]" class="cl-payment-details-listing-fees" value="<?php echo esc_attr( json_encode( $fees ) ); ?>" />
													<?php endif; ?>
												</li>
												<li class="item_price<?php echo esc_attr( $is_qty_enabled ); ?>">
													<span class="cl-payment-details-label-mobile">
														<?php
														_ex( 'Price', 'payment details purchased item price - mobile', 'essential-wp-real-estate' );
														if ( cl_item_quantities_enabled() ) :
															_ex( ' & Quantity', 'payment details purchased item quantity - mobile', 'essential-wp-real-estate' );
														endif;
														?>
													</span>
													<?php echo WPERECCP()->common->currencies->cl_currency_symbol( $currency_code ); ?>
													<input type="text" class="medium-text cl-price-field cl-payment-details-listing-item-price cl-payment-item-input" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][item_price]" value="<?php echo WPERECCP()->common->formatting->cl_format_amount( $item_price ); ?>" />
													<?php if ( cl_item_quantities_enabled() ) : ?>
														&nbsp;&times;&nbsp;
														<input type="number" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][quantity]" class="small-text cl-payment-details-listing-quantity cl-payment-item-input" min="1" step="1" value="<?php echo esc_attr( $quantity ); ?>" />
													<?php endif; ?>
												</li>
												<?php if ( $taxs->cl_use_taxes() ) : ?>
													<li class="item_tax">
														<span class="cl-payment-details-label-mobile">
															<?php _ex( 'Tax', 'payment details purchased item tax - mobile', 'essential-wp-real-estate' ); ?>
														</span>
														<?php echo WPERECCP()->common->currencies->cl_currency_symbol( $currency_code ); ?>
														<input type="text" class="small-text cl-price-field cl-payment-details-listing-item-tax cl-payment-item-input" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][item_tax]" value="<?php echo WPERECCP()->common->formatting->cl_format_amount( $item_tax ); ?>" />
													</li>
												<?php endif; ?>
												<li class="price">
													<span class="cl-payment-details-label-mobile">
														<?php printf( _x( '%s Total', 'payment details purchased item total - mobile', 'essential-wp-real-estate' ), cl_get_label_singular() ); ?>
													</span>
													<span class="cl-price-currency"><?php echo WPERECCP()->common->currencies->cl_currency_symbol( $currency_code ); ?></span><span class="price-text cl-payment-details-listing-amount"><?php echo WPERECCP()->common->formatting->cl_format_amount( $price ); ?></span>
													<input type="hidden" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][amount]" class="cl-payment-details-listing-amount" value="<?php echo esc_attr( $price ); ?>" />
												</li>
											</ul>
											<div class="cl-purchased-listing-actions actions">
												<input type="hidden" class="cl-payment-details-listing-has-log" name="cl-payment-details-listings[<?php echo esc_attr( $key ); ?>][has_log]" value="1" />
											</div>
										</div>
										<?php
										$i++;
									endforeach;
									?>
								</div>
								<?php
							else :
								$key = 0;
								?>
								<div class="row">
									<p><?php printf( __( 'No %s included with this purchase', 'essential-wp-real-estate' ), cl_get_label_plural() ); ?></p>
								</div>
							<?php endif; ?>


							<?php do_action( 'cl_view_order_details_files_after', $payment_id ); ?>

							<?php do_action( 'cl_view_order_details_billing_before', $payment_id ); ?>


							<div id="cl-customer-details" class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Customer Details', 'essential-wp-real-estate' ); ?></span>
								</h3>
								<div class="inside cl-clearfix">

									<div class="column-container customer-info">
										<div class="column">
											<?php
											if ( ! empty( $customer->id ) ) :
												?>
												<?php $customer_url = admin_url( 'edit.php?post_type=listing&page=cl-customers&view=overview&id=' . esc_attr( $customer->id ) ); ?>
												<a href="<?php echo esc_url( $customer_url ); ?>"><?php echo esc_html( $customer->name ); ?> - <?php echo esc_html( $customer->email ); ?></a>
											<?php endif; ?>
											<input type="hidden" name="cl-current-customer" value="<?php echo esc_attr( $customer->id ); ?>" />
										</div>
										<div class="column">
											<a href="#change" class="cl-payment-change-customer"><?php _e( 'Assign to another customer', 'essential-wp-real-estate' ); ?></a>
											&nbsp;|&nbsp;
											<a href="#new" class="cl-payment-new-customer"><?php _e( 'New Customer', 'essential-wp-real-estate' ); ?></a>
										</div>
									</div>

									<div class="column-container change-customer" style="display: none">
										<div class="column">
											<strong><?php _e( 'Select a customer', 'essential-wp-real-estate' ); ?>:</strong>
											<?php
											$args = array(
												'class'    => 'cl-payment-change-customer-input',
												'selected' => $customer->id,
												'name'     => 'customer-id',
												'placeholder' => __( 'Type to search all Customers', 'essential-wp-real-estate' ),
											);

											echo WPERECCP()->common->dbcustomer->customer_dropdown( $args );
											?>
										</div>
										<div class="column"></div>
										<div class="column">
											<strong><?php _e( 'Actions', 'essential-wp-real-estate' ); ?>:</strong>
											<br />
											<input type="hidden" id="cl-change-customer" name="cl-change-customer" value="0" />
											<a href="#cancel" class="cl-payment-change-customer-cancel cl-delete"><?php _e( 'Cancel', 'essential-wp-real-estate' ); ?></a>
										</div>
										<div class="column">
											<small><em>*<?php _e( 'Click "Save Payment" to change the customer', 'essential-wp-real-estate' ); ?></em></small>
										</div>
									</div>

									<div class="column-container new-customer" style="display: none">
										<div class="column">
											<strong><?php _e( 'Name', 'essential-wp-real-estate' ); ?>:</strong>&nbsp;
											<input type="text" name="cl-new-customer-name" value="" class="medium-text" />
										</div>
										<div class="column">
											<strong><?php _e( 'Email', 'essential-wp-real-estate' ); ?>:</strong>&nbsp;
											<input type="email" name="cl-new-customer-email" value="" class="medium-text" />
										</div>
										<div class="column">
											<strong><?php _e( 'Actions', 'essential-wp-real-estate' ); ?>:</strong>
											<br />
											<input type="hidden" id="cl-new-customer" name="cl-new-customer" value="0" />
											<a href="#cancel" class="cl-payment-new-customer-cancel cl-delete"><?php _e( 'Cancel', 'essential-wp-real-estate' ); ?></a>
										</div>
										<div class="column">
											<small><em>*<?php _e( 'Click "Save Payment" to create new customer', 'essential-wp-real-estate' ); ?></em></small>
										</div>
									</div>

									<?php
									// The cl_payment_personal_details_list hook is left here for backwards compatibility
									do_action( 'cl_payment_personal_details_list', $payment_meta, $user_info );
									do_action( 'cl_payment_view_details', $payment_id );
									?>

								</div><!-- /.inside -->
							</div><!-- /#cl-customer-details -->

							<div id="cl-billing-details" class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Billing Address', 'essential-wp-real-estate' ); ?></span>
								</h3>
								<div class="inside cl-clearfix">

									<div id="cl-order-address">

										<div class="order-data-address">
											<div class="data column-container">
												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 1:', 'essential-wp-real-estate' ); ?></strong><br />
														<input type="text" name="cl-payment-address[0][line1]" value="<?php echo esc_attr( $address['line1'] ); ?>" class="large-text" />
													</p>
													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 2:', 'essential-wp-real-estate' ); ?></strong><br />
														<input type="text" name="cl-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="large-text" />
													</p>

												</div>
												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'essential-wp-real-estate' ); ?></strong><br />
														<input type="text" name="cl-payment-address[0][city]" value="<?php echo esc_attr( $address['city'] ); ?>" class="large-text" />

													</p>
													<p>
														<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'essential-wp-real-estate' ); ?></strong><br />
														<input type="text" name="cl-payment-address[0][zip]" value="<?php echo esc_attr( $address['zip'] ); ?>" class="large-text" />

													</p>
												</div>
												<div class="column">
													<p id="cl-order-address-country-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'essential-wp-real-estate' ); ?></strong><br />
														<?php
														echo WPERECCP()->admin->settings_instances->cl_admin_select_callback(
															array(
																'options'          => WPERECCP()->front->country->cl_get_country_list(),
																'name'             => 'cl-payment-address[0][country]',
																'id'               => 'cl-payment-address-country',
																'selected'         => $address['country'],
																'show_option_all'  => false,
																'show_option_none' => false,
																'chosen'           => true,
																'placeholder'      => __( 'Select a country', 'essential-wp-real-estate' ),
																'data'             => array(
																	'search-type'        => 'no_ajax',
																	'search-placeholder' => __( 'Type to search all Countries', 'essential-wp-real-estate' ),
																	'nonce'              => wp_create_nonce( 'cl-country-field-nonce' ),
																),
															)
														);
														?>
													</p>
													<p id="cl-order-address-state-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'State / Province:', 'State / province of address', 'essential-wp-real-estate' ); ?></strong><br />
														<?php
														$states = WPERECCP()->front->country->cl_get_shop_states( $address['country'] );
														if ( ! empty( $states ) ) {
															echo WPERECCP()->admin->settings_instances->cl_admin_select_callback(
																array(
																	'options'          => $states,
																	'name'             => 'cl-payment-address[0][state]',
																	'id'               => 'cl-payment-address-state',
																	'selected'         => $address['state'],
																	'show_option_all'  => false,
																	'show_option_none' => false,
																	'chosen'           => true,
																	'placeholder'      => __( 'Select a state', 'essential-wp-real-estate' ),
																	'data'             => array(
																		'search-type'        => 'no_ajax',
																		'search-placeholder' => __( 'Type to search all States/Provinces', 'essential-wp-real-estate' ),
																	),
																)
															);
														} else {
															?>
															<input type="text" name="cl-payment-address[0][state]" value="<?php echo esc_attr( $address['state'] ); ?>" class="large-text" />
															<?php
														}
														?>
													</p>
												</div>
											</div>
										</div>
									</div><!-- /#cl-order-address -->

									<?php do_action( 'cl_payment_billing_details', $payment_id ); ?>

								</div><!-- /.inside -->
							</div><!-- /#cl-billing-details -->

							<?php do_action( 'cl_view_order_details_billing_after', $payment_id ); ?>

							<div id="cl-payment-notes" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Payment Notes', 'essential-wp-real-estate' ); ?></span></h3>
								<div class="inside">
									<div id="cl-payment-notes-inner">
										<?php
										$notes = cl_get_payment_notes( $payment_id );
										if ( ! empty( $notes ) ) :
											$no_notes_display = ' style="display:none;"';
											foreach ( $notes as $note ) :

												echo cl_get_payment_note_html( $note, $payment_id );

											endforeach;
										else :
											$no_notes_display = '';
										endif;
										echo '<p class="cl-no-payment-notes"' . $no_notes_display . '>' . __( 'No payment notes', 'essential-wp-real-estate' ) . '</p>';
										?>
									</div>
									<textarea name="cl-payment-note" id="cl-payment-note" class="large-text"></textarea>

									<p>
										<button id="cl-add-payment-note" class="button button-secondary right" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php _e( 'Add Note', 'essential-wp-real-estate' ); ?></button>
									</p>

									<div class="clear"></div>
								</div><!-- /.inside -->
							</div><!-- /#cl-payment-notes -->

							<?php do_action( 'cl_view_order_details_main_after', $payment_id ); ?>
						</div><!-- /#normal-sortables -->
					</div><!-- #postbox-container-2 -->
				</div><!-- /#post-body -->
			</div><!-- #cl-dashboard-widgets-wrap -->
		</div><!-- /#post-stuff -->
		<?php do_action( 'cl_view_order_details_form_bottom', $payment_id ); ?>
		<?php wp_nonce_field( 'cl_update_payment_details_nonce' ); ?>
		<input type="hidden" name="cl_payment_id" value="<?php echo esc_attr( $payment_id ); ?>" />
		<input type="hidden" name="cl_action" value="update_payment_details" />
	</form>
	<?php do_action( 'cl_view_order_details_after', $payment_id ); ?>
</div><!-- /.wrap -->

<div id="cl-listing-link"></div>
