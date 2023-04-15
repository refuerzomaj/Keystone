<?php
add_action( 'wp_footer', 'wperesds_compare_func' );
add_action( 'wp_footer', 'wperesds_alert_func' );
function wperesds_compare_func() {
	$comp_url  = get_page_link( cl_admin_get_option( 'compare_page' ) );
	$comp_data = array();
	if ( isset( $_COOKIE['compare_listing_data'] ) ) {
		$comp_data = explode( ',', cl_sanitization( $_COOKIE['compare_listing_data'] ) );
		$comp_data = array_filter( $comp_data );
	};
	?>
	<div id="wperesds-compare-wrapper" class="wperesds-compare-wrapper">
		<div class="wperesds-compare-collapse-button">
			<a class="wperesds-collapse-btn" href="javascript:void(0)"><i class="fas fa-random"></i></a>
		</div>
		<div class="wperesds-compare-container">
			<h4 class="compare_title"><?php echo esc_html__( 'Compare Listings', 'essential-wp-real-estate' ); ?></h4>
			<div class="wperesds-compare-items">
				<?php foreach ( $comp_data as $post ) { ?>
					<!-- Compare selected Item -->
					<div id="wperesds-compare-item<?php echo esc_attr( $post ); ?>" class="compare-listing-single">
						<div class="compare-item-img">
							<?php
							if ( has_post_thumbnail( $post ) ) {
								$alt = get_post_meta( $post, '_wp_attachment_image_alt', true );
								?>
								<img src="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'thumbnail' ) ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
							<?php } else { ?>
								<img src="<?php echo WPERESDS_ASSETS . '/img/placeholder_light.png'; ?>" alt="<?php esc_attr_e( 'Placeholder', 'essential-wp-real-estate' ); ?>">
							<?php } ?>
						</div>
						<div class="compare-item-content">
							<span class="item-title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
							<a class="wperesds-compare-remove-btn" data-remove_compare_item="<?php echo esc_attr( $post ); ?>" href="javascript:void(0)" target="_blank"><i class="fas fa-trash-alt"></i></a>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="wperesds-compare-button">
				<a class="wperesds-compare-btn" href="<?php echo esc_url( $comp_url ); ?>" target="_blank"><?php esc_html_e( 'Compare', 'essential-wp-real-estate' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}

function wperesds_alert_func() {
	?>
	<div class="wperesds-alart"></div>
	<?php
}
