<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider       = WPERECCP()->front->listing_provider;
$property_video = $provider->get_meta_data( $provider->prefix . 'video', get_the_ID() );
$placeholder    = null;
if ( isset( $property_video['wperesds_video_img'] ) && ! empty( $property_video['wperesds_video_img'] ) ) {
	$placeholder = wp_get_attachment_image( $property_video['wperesds_video_img'][0], array( '1200', '600' ) );
}
if ( isset( $property_video['wperesds_video_url'] ) ) {
	$video_url = $property_video['wperesds_video_url'];
	if ( strpos( $video_url, 'watch?v=' ) !== false ) { // if string contains the word
		$video_id   = substr( $video_url, strpos( $video_url, 'watch?v=' ) + 8 );
		$video_data = 'https://www.youtube.com/embed/' . $video_id;
	} else {
		$video_data = $video_url;
	}
	if ( ! empty( $video_data ) ) {
		?>
		<div class="property_video">
			<div class="thumb">
				<?php if ( empty( $placeholder ) ) { ?>
					<iframe width="100%" height="600" src="<?php echo esc_attr( $video_data ); ?>"></iframe>
					<?php
				} else {
					echo wp_kses( $placeholder, 'cl_code_img' );
					?>
					<a href="<?php echo esc_attr( $property_video['wperesds_video_url'] ); ?>" data-toggle="modal" data-target="#popup-video" class="theme-cl">
						<div class="overlay_icon">
							<div class="bb-video-box">
								<div class="bb-video-box-inner">
									<div class="bb-video-box-innerup">
										<i class="fa fa-play"></i>
									</div>
								</div>
							</div>
						</div>
					</a>
					<!-- Modal -->
					<div class="modal fade" id="popup-video" tabindex="-1" role="dialog" aria-labelledby="popup-video" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<iframe class="embed-responsive-item" width="100%" height="480" src="<?php echo esc_attr( $video_data ); ?>" frameborder="0" allowfullscreen></iframe>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}
}
