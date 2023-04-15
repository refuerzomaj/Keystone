<?php
global $current_user;
$args          = array(
	'post_type'      => 'cl_cpt',
	'post_status'    => array( 'publish', 'pending', 'draft' ),
	'orderby'        => 'post_date',
	'order'          => 'DESC',
	'posts_per_page' => -1, // no limit,
);
$user_posts    = get_posts( $args );
$fav_post_meta = get_user_meta( $current_user->ID, '_favorite_posts' );


if ( ! empty( $fav_post_meta ) ) {
	foreach ( $fav_post_meta as $single_post_id ) {
		$comments  = get_comments( array( 'post_id' => $single_post_id ) );
		$term_list = wp_get_post_terms( $single_post_id, 'listing_status', array( 'fields' => 'names' ) );
		?>
		<!-- Single Property -->
		<div id="favourite_item_<?php echo esc_attr( $single_post_id ); ?>" class="col-md-12 col-sm-12 col-md-12">
			<div class="singles-dashboard-list">
				<?php if ( has_post_thumbnail( $single_post_id ) ) { ?>
					<div class="sd-list-left">
						<?php echo get_the_post_thumbnail( $single_post_id, 'thumbnail' ); ?>
					</div>
				<?php } else { ?>
					<div class="sd-list-left">
						<img src="<?php echo WPERESDS_ASSETS . '/img/placeholder_light.png'; ?>" alt="<?php esc_attr_e( 'Placeholder', 'essential-wp-real-estate' ); ?>">
					</div>
				<?php } ?>

				<div class="sd-list-right">
					<h4 class="listing_dashboard_title"><a href="<?php echo esc_url( get_permalink( $single_post_id ) ); ?>" class="theme-cl"><?php echo get_the_title( $single_post_id ); ?></a></h4>
					<div class="action">
						<a href="<?php echo esc_url( get_permalink( $single_post_id ) ); ?>" data-toggle="tooltip" data-placement="top" title="View Property"><i class="far fa-eye"></i><?php echo esc_html__( ' View', 'essential-wp-real-estate' ); ?></a>
						<a href="javascript:void(0)" data-userid="<?php echo esc_attr( $current_user->ID ); ?>" data-postid="<?php echo esc_attr( $single_post_id ); ?>" class="remove-from-favorite prt_saveed_12lk" id="like_listing <?php echo esc_attr( $single_post_id ); ?>"><i class="far fa-trash-alt"></i><?php echo esc_html__( ' Remove', 'essential-wp-real-estate' ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
} else {
	echo '<p class="messages-headline">';
	echo esc_html__( 'No Listing Found', 'essential-wp-real-estate' );
	echo '</p>';
}
