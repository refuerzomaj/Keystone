<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider = WPERECCP()->front->listing_provider;
$value    = $provider->get_meta_data( $args['id'], get_the_ID() );
$masonry  = false;

if ( ! empty( $value ) ) {
	echo '<div class="grid-wrapper front_gallery_block">';
	foreach ( $value as $item ) {
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
