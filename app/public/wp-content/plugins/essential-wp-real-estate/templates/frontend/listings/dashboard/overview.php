<?php
if ( ! is_user_logged_in() ) {
	echo '<p>' . esc_html__( 'Please', 'essential-wp-real-estate' ) . ' <a href="' . esc_url( get_page_link( cl_admin_get_option( 'login_redirect_page' ) ) ) . '">' . esc_html__( 'Login', 'essential-wp-real-estate' ) . '</a></p>';
} else {
	global $current_user;
	$udata = get_userdata( $current_user->ID );
	?>
	<div id="cl-user-overview" class="cl-user-overview">
		<div class="cl-user-overview-activity-boxes">
			<div class="cl-user-overview-activity-box">
				<i class="fa fa-ad"></i>
				<div>
					<p><?php esc_html_e( 'Active Listing', 'essential-wp-real-estate' ); ?></p>
					<h3> <?php echo cl_total_active_listing_by_user(); ?>
					</h3>
				</div>
			</div>
			<div class="cl-user-overview-activity-box">
				<i class="fa fa-eye"></i>
				<div>
					<p><?php esc_html_e( 'Total Views', 'essential-wp-real-estate' ); ?></p>
					<h3><?php echo cl_listing_total_view(); ?></h3>
				</div>
			</div>
			<div class="cl-user-overview-activity-box">
				<i class="fa fa-star"></i>
				<div>
					<p><?php esc_html_e( 'Total Reviews', 'essential-wp-real-estate' ); ?></p>
					<h3><?php echo cl_listing_total_review(); ?></h3>
				</div>
			</div>
			<div class="cl-user-overview-activity-box">
				<i class="fa fa-heart"></i>
				<div>
					<p><?php esc_html_e( 'Bookmarked', 'essential-wp-real-estate' ); ?></p>
					<h3><?php echo cl_listing_total_saved(); ?></h3>
				</div>
			</div>
		</div>
		<div class="cl-user-overview-chart">
			<canvas id="myChart" width="400" height="200"></canvas>
		</div>
	</div>
	<?php
}
