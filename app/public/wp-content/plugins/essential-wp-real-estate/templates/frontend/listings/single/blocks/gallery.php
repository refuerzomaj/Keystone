<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider = WPERECCP()->front->listing_provider;
$gallery  = $provider->get_meta_data( 'wperesds_gallery', get_the_ID() );
$masonry  = false;

if ( ! empty( $gallery ) ) {
	echo '<div class="grid-wrapper">';
	foreach ( $gallery as $item ) {
		if ( $masonry == true ) {
			$rand_class = array( 'small', 'big' );
			$key        = array_rand( $rand_class );
			echo '<div class="gallery-item ' . esc_attr( $rand_class[ $key ] ) . '">' . wp_get_attachment_image( $item, array( '350', '350' ) ) . '</div>';
		} else {
			echo '<div class="gallery-item">' . wp_get_attachment_image( $item, array( '350', '350' ) ) . '</div>';
		}
	}
	echo '</div>';
}
