<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider = WPERECCP()->front->listing_provider;

if ( isset( $args['data_id'] ) && ! empty( $args['data_id'] ) ) {
	$data_value = $provider->get_meta_data( $args['data_id'], get_the_ID() );
	$value      = $data_value[ $args['val_id'] ];
	if ( is_array( $value ) ) {
		foreach ( $value as $value_key => $val ) {
			echo '<div class="table-container"><div class="table-cell heading">' . esc_html( $args['name'] ) . '</div><div class="table-cell">' . wp_get_attachment_image( $val, 'full' ) . '</div></div>';
		}
	}
} else {
	$value = $provider->get_meta_data( $args['id'], get_the_ID() );
	if ( ! empty( $value ) ) {
		echo '<div class="table-container"><div class="table-cell heading">' . esc_html( $args['name'] ) . '</div><div class="table-cell"><a href="' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a></div></div>';
	}
}
