<?php
$provider = WPERECCP()->front->listing_provider;
$gallery  = $provider->get_meta_data( 'wperesds_gallery' );
if ( has_post_thumbnail() || $gallery ) {
	if ( ! empty( $gallery ) ) { ?>
		<!-- Slide Section -->
		<div class="featured_slick_gallery gray mt-4 lazy-section">
			<?php
			printf( $provider->markups->thumbnail_wrapper_open() );
			$provider->show_thumb( 'full' );
			if ( $gallery ) {
				foreach ( $gallery as $value ) {
					echo wp_get_attachment_image( $value, 'full' );
				}
			}
			printf( $provider->markups->thumbnail_wrapper_close() );
			?>
		</div>
		<?php
	} else {
		$provider->show_thumb( 'full' );
	}
} ?>
