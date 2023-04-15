<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider = WPERECCP()->front->listing_provider;

if($args['id'] == 'wperesds_pricing' || $args['id'] == 'wperesds_pricing_range'){
	$value    = $provider->get_meta_data( $args['id'], get_the_ID() );
	$value    = WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $value ) );
}else{
	$value    = $provider->get_meta_data( $args['id'], get_the_ID() );
}

if ( ! empty( $value ) ) {
	echo '<div class="table-container"><div class="table-cell heading">' . esc_html( $args['name'] ) . '</div><div class="table-cell">' . esc_html( $value ) . '</div></div>';
}
