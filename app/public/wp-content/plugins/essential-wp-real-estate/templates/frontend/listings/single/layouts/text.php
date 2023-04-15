<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider = WPERECCP()->front->listing_provider;

if ( isset( $args['data_id'] ) && ! empty( $args['data_id'] ) ) {
	$data_value = $provider->get_meta_data( $args['data_id'], get_the_ID() );
	$value      = $data_value[ $args['val_id'] ];
	echo '<div class="table-container"><div class="table-cell heading">' . esc_html( $args['name'] ) . '</div><div class="table-cell">' . esc_html( $value ) . '</div></div>';
} else {
	$value = $provider->get_meta_data( $args['id'], get_the_ID() );
	if ( ! empty( $value ) ) {
		echo '<div class="table-container"><div class="table-cell heading">' . esc_html( $args['name'] ) . '</div><div class="table-cell">' . esc_html( $value ) . '</div></div>';
	}
}
