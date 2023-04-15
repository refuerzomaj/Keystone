<?php
global $current_user;
$args               = array(
	'author'         => $current_user->ID,
	'post_type'      => 'cl_cpt',
	'post_status'    => array( 'publish', 'pending', 'draft' ),
	'orderby'        => 'post_date',
	'order'          => 'DESC',
	'posts_per_page' => -1, // no limit,
);
$current_user_posts = get_posts( $args );

if ( ! empty( $current_user_posts ) ) {
	foreach ( $current_user_posts as $single_post ) {
		$comments  = get_comments( array( 'post_id' => $single_post->ID ) );
		$term_list = wp_get_post_terms( $single_post->ID, 'listing_status', array( 'fields' => 'names' ) );
		?>
		<!-- Single Property -->
		<div class="col-md-12 col-sm-12 col-md-12">
			<div class="singles-dashboard-list">
				<?php if ( $single_post->post_status != 'publish' ) { ?>
					<span class="post-status"><?php esc_html_e( 'Pending', 'essential-wp-real-estate' ); ?></span>
					<?php
				}
				if ( has_post_thumbnail( $single_post->ID ) ) {
					?>
					<div class="sd-list-left">
						<?php echo get_the_post_thumbnail( $single_post->ID, 'thumbnail' ); ?>
					</div>
				<?php } else { ?>
					<div class="sd-list-left">
						<img src="<?php echo WPERESDS_ASSETS . '/img/placeholder_light.png'; ?>" alt="<?php esc_attr_e( 'Placeholder', 'essential-wp-real-estate' ); ?>">
					</div>
				<?php } ?>
				<div class="sd-list-right">
					<h4 class="listing_dashboard_title"><a href="<?php echo esc_url( get_permalink( $single_post->ID ) ); ?>" class="theme-cl"><?php echo esc_html( $single_post->post_title ); ?></a></h4>
					<?php if ( $term_list ) { ?>
						<div class="user_dashboard_listed">
							<?php echo esc_html( 'Listed in' ); ?>
							<?php foreach ( $term_list as $term ) { ?>
								<a href="<?php echo esc_url( get_term_link( $term, 'listing_status' ) ); ?>" class="theme-cl"><?php echo wp_kses( $term, 'cl_code_context' ); ?></a>
							<?php } ?>
						</div>
					<?php } ?>
					<div class="action">
						<a href="<?php echo get_permalink( $single_post->ID ); ?>" data-toggle="tooltip" data-placement="top" title="View Property"><i class="far fa-eye"></i> <?php echo esc_html__( 'View', 'essential-wp-real-estate' ); ?></a>
						<a href="<?php echo add_query_arg( 'cl_edit_listing_var', $single_post->ID, get_page_link( cl_admin_get_option( 'edit_listing_page' ) ) ); ?>" data-toggle="tooltip" data-placement="top" title="Edit Property"><i class="fas fa-edit"></i> <?php echo esc_html__( 'Edit', 'essential-wp-real-estate' ); ?></a>
						<?php
						if ( current_user_can( 'administrator' ) ) {
							?>
							<a href="<?php echo get_delete_post_link( $single_post->ID ); ?>" data-toggle="tooltip" data-placement="top" title="Delete Property" class="delete"><i class="far fa-trash-alt"></i> <?php echo esc_html__( 'Delete', 'essential-wp-real-estate' ); ?></a>
							<?php
						} else {
							?>
							<a data-warning="<?php echo esc_attr__( 'Are you sure you want to delete this item?', 'essential-wp-real-estate' ); ?>" id="delete-listing" data-listing-id="<?php echo esc_attr( $single_post->ID ); ?>" class="delete-listing gray" href="javascript:void(0);"><i class="far fa-trash-alt"></i></a>
						<?php } ?>
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
