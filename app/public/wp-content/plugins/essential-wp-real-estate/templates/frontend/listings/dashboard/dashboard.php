<?php
$dash_overview_class = '';
$my_ls_class         = '';
$fav_ls_class        = '';
$pr_class            = '';
if ( isset( $_COOKIE['dash_menu_activate'] ) ) {
	switch ( $_COOKIE['dash_menu_activate'] ) {
		case 'dash_overview':
			$dash_overview_class = 'active';
			break;
		case 'dash_my_listings':
			$my_ls_class = 'active';
			break;
		case 'dash_fav_listings':
			$fav_ls_class = 'active';
			break;
		default:
			$pr_class = 'active';
			break;
	}
} else {
	$dash_overview_class = 'active';
}
?>

<div class="container dashboard">
	<div class="row">
		<div class="col-md-4 menu-section">
			<a href="javascript:void(0)" class="<?php echo esc_attr( $dash_overview_class ); ?>" id="dash_overview"><i class="fa fa-layer-group"></i><?php echo esc_html__( 'Dashboard', 'essential-wp-real-estate' ); ?></a>
			<a href="javascript:void(0)" class="<?php echo esc_attr( $pr_class ); ?>" id="dash_profile"><i class="fa fa-user"></i><?php echo esc_html__( 'My Profile', 'essential-wp-real-estate' ); ?></a>
			<a href="<?php echo esc_url( get_page_link( cl_admin_get_option( 'add_listing_page' ) ) ); ?>"><i class="fa fa-plus-circle"></i><?php echo esc_html__( 'Add Listing', 'essential-wp-real-estate' ); ?></a>
			<a href="javascript:void(0)" class="<?php echo esc_attr( $my_ls_class ); ?>" id="dash_my_listings"><i class="fa fa-list"></i><?php echo esc_html__( 'My Listings', 'essential-wp-real-estate' ); ?></a>
			<a href="javascript:void(0)" class="<?php echo esc_attr( $fav_ls_class ); ?>" id="dash_fav_listings"><i class="fa fa-heart"></i><?php echo esc_html__( 'Bookmarked Listings', 'essential-wp-real-estate' ); ?></a>
			<a href="<?php echo esc_url( wp_logout_url( site_url() ) ); ?>" id="dash_sign_out"><i class="fa fa-sign-out-alt"></i><?php echo esc_html__( 'Sign Out', 'essential-wp-real-estate' ); ?></a>
		</div>
		<div class="col-md-8">
			<div id="dash_overview_section" class="block-section <?php echo esc_attr( $dash_overview_class ); ?>">
				<?php cl_get_template( 'dashboard/overview.php' ); ?>
			</div>
			<div id="dash_my_listings_section" class="block-section <?php echo esc_attr( $my_ls_class ); ?>">
				<?php cl_get_template( 'dashboard/listings.php' ); ?>
			</div>
			<div id="dash_fav_listings_section" class="block-section <?php echo esc_attr( $fav_ls_class ); ?>">
				<?php cl_get_template( 'dashboard/favourites.php' ); ?>
			</div>
			<div id="dash_profile_section" class="block-section <?php echo esc_attr( $pr_class ); ?>">
				<?php cl_get_template( 'dashboard/profile.php' ); ?>
			</div>
		</div>
	</div>
</div>
