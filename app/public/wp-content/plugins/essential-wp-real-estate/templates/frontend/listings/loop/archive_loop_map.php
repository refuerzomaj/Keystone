<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Getting global $pref val
global $pref;
$provider         = WPERECCP()->front->listing_provider;
$img_url          = ! empty( get_the_post_thumbnail_url() ) ? get_the_post_thumbnail_url() : WPERESDS_ASSETS . '/img/placeholder_light.png';
$maps_fields_data = $provider->get_meta_data( $provider->prefix . 'maps_fields' );
$listing_data     = wp_json_encode(
	array(
		'id'        => $provider->listing->ID,
		'url'       => $provider->listing->url,
		'title'     => $provider->listing->title,
		'content'   => $provider->listing->content,
		'excerpt'   => $provider->listing->excerpt,
		'img_url'   => $img_url,
		'address'   => $provider->get_meta_data( $provider->prefix . 'address' ),
		'latitude'  => isset( $maps_fields_data[ $provider->prefix . 'map_address_lat' ] ) ? $maps_fields_data[ $provider->prefix . 'map_address_lat' ] : '',
		'longitude' => isset( $maps_fields_data[ $provider->prefix . 'map_address_lon' ] ) ? $maps_fields_data[ $provider->prefix . 'map_address_lon' ] : '',
		'price'     => $provider->get_meta_data( $provider->prefix . 'pricing', get_the_ID() ),
	)
);

$value = utf8_encode( json_encode( $listing_data ) );
echo '<div id="' . esc_attr( $provider->listing->ID ) . '" class="listing_data" data-listing="' . esc_attr( $value ) . '"></div>';
