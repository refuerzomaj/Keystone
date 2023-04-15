<?php
/**
 * The template for displaying listing single sidebar
 *
 * @see     https://docs.essential-wp-real-estate.com/document/template-structure/
 * @package essential-wp-real-estate/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $args ) && $args == 'single' ) {
	if ( ! is_active_sidebar( 'listing-single' ) ) {
		return;
	}
} else {
	if ( ! is_active_sidebar( 'listing-sidebar' ) ) {
		return;
	}
}
?>
<div class="listing-sidebar col-lg-4">
	<div class="page-sidebar">
		<?php
		if ( isset( $args ) && $args == 'single' ) {
			$listing_purchase = cl_admin_get_option( 'listing_purchase' );
			if($listing_purchase == '1'){
				do_action( $pref . 'listing_cart' );
			}
			dynamic_sidebar( 'listing-single' );
		} else {
			dynamic_sidebar( 'listing-sidebar' );
		}
		?>
	</div>
</div>
