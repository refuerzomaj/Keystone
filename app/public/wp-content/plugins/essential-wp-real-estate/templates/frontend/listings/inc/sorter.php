<?php
global $pref;
$LISTING_Query  = WPERECCP()->front->query->get_listing_query();
$query_showing  = $LISTING_Query->post_count;
$query_total    = $LISTING_Query->found_posts;
$layout_options = WPERECCP()->front->listing_provider->get_gen_ted_link();
?>
<div class="row">
	<div class="col-lg-12">
		<div class="sorter-arch list-flex">
			<div class="item-flex">
				<?php do_action( $pref . 'listing_layout' ); ?>
			</div>
			<div class="item-flex arch-post-count">
				<?php echo wp_sprintf( '<span>' . __( 'Showing', 'essential-wp-real-estate' ) . ' %s ' . __( 'of' ) . ' %s ' . __( 'Results', 'essential-wp-real-estate' ) . '</span>', $query_showing, $query_total ); ?>
			</div>
			<div class="item-flex">
				<div class="sort-dropdown">
					<?php do_action( $pref . 'listing_sorter' ); ?>
				</div>
			</div>
		</div>
	</div>
</div>
