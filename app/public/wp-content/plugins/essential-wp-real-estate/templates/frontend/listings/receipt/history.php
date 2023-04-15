<?php
use Essential\Restate\Front\Purchase\Payments\Clpayment;

if ( ! empty( $_GET['cl-verify-success'] ) ) : ?>
	<p class="cl-account-verified cl_success">
		<?php _e( 'Your account has been successfully verified!', 'essential-wp-real-estate' ); ?>
	</p>
	<?php
endif;
/**
 * This template is used to display the purchase history of the current user.
 */
if ( is_user_logged_in() ) :
	$payments = WPERECCP()->common->user->cl_get_users_purchases( get_current_user_id(), 20, true, 'any' );
	if ( $payments ) :
		do_action( 'cl_before_purchase_history', $payments );
		?>
		<table id="cl_user_history" class="cl-table">
			<thead>
				<tr class="cl_purchase_row">
					<?php do_action( 'cl_purchase_history_header_before' ); ?>
					<th class="cl_purchase_id"><?php _e( 'ID', 'essential-wp-real-estate' ); ?></th>
					<th class="cl_purchase_date"><?php _e( 'Date', 'essential-wp-real-estate' ); ?></th>
					<th class="cl_purchase_amount"><?php _e( 'Amount', 'essential-wp-real-estate' ); ?></th>
					<th class="cl_purchase_details"><?php _e( 'Details', 'essential-wp-real-estate' ); ?></th>
					<?php do_action( 'cl_purchase_history_header_after' ); ?>
				</tr>
			</thead>
			<?php foreach ( $payments as $payment ) : ?>
				<?php $payment = new Clpayment( $payment->ID ); ?>
				<tr class="cl_purchase_row">
					<?php do_action( 'cl_purchase_history_row_start', $payment->ID, $payment->payment_meta ); ?>
					<td class="cl_purchase_id">#<?php echo esc_html( $payment->number ); ?></td>
					<td class="cl_purchase_date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ); ?></td>
					<td class="cl_purchase_amount">
						<span class="cl_purchase_amount"><?php echo WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $payment->total ) ); ?></span>
					</td>
					<td class="cl_purchase_details">
						<?php if ( $payment->status != 'publish' ) : ?>
							<span class="cl_purchase_status <?php echo esc_attr( $payment->status ); ?>"><?php echo esc_html( $payment->status_nicename ); ?></span>
							<?php if ( $payment->is_recoverable() ) : ?>
								&mdash; <a href="<?php echo esc_url( $payment->get_recovery_url() ); ?>"><?php _e( 'Complete Purchase', 'essential-wp-real-estate' ); ?></a>
							<?php endif; ?>
						<?php else : ?>
							<a href="<?php echo esc_url( add_query_arg( 'payment_key', $payment->key, WPERECCP()->front->checkout->cl_get_success_page_uri() ) ); ?>"><?php _e( 'View Details and listings', 'essential-wp-real-estate' ); ?></a>
						<?php endif; ?>
					</td>
					<?php do_action( 'cl_purchase_history_row_end', $payment->ID, $payment->payment_meta ); ?>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
		echo cl_pagination(
			array(
				'type'  => 'purchase_history',
				'total' => ceil( WPERECCP()->common->user->cl_count_purchases_of_customer() / 20 ), // 20 items per page
			)
		);
		?>
		<?php do_action( 'cl_after_purchase_history', $payments ); ?>
		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<p class="cl-no-purchases"><?php _e( 'You have not made any purchases', 'essential-wp-real-estate' ); ?></p>
		<?php
endif;
endif;
