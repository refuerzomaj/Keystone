<?php
$provider = WPERECCP()->front->listing_provider;
$class    = '';
if ( isset( $args['class'] ) ) {
	$class = $args['class'];
	unset( $args['class'] );
}

$finalmetas = array();
// $key = array_key_first($args); // for php version > 7.3
$metas = array_shift( $args );
if ( isset( $metas['class'] ) ) {
	$class = $metas['class'];
	unset( $metas['class'] );
}
foreach ( $metas as $key => $val ) {
	if ( is_array( $val ) ) {
		if ( $val['active'] ) {
			$finalmetas['leftmeta'][] = 'listing_' . $key;
		}
	}
}
// $key = array_key_first($args); // for php version > 7.3
$metas = array_shift( $args );
if ( isset( $metas['class'] ) ) {
	$class = $metas['class'];
	unset( $metas['class'] );
}
foreach ( $metas as $key => $val ) {
	if ( is_array( $val ) ) {
		if ( $val['active'] ) {
			$finalmetas['rightmeta'][] = 'listing_' . $key;
		}
	}
}
?>
<div class="info_section mb-4">
	<?php
	printf( $provider->markups->leftmeta_wrapper_open() );
	if ( isset( $finalmetas['leftmeta'] ) ) {
		$provider->render_loop_sections( $finalmetas['leftmeta'] );
	}
	printf( $provider->markups->meta_wrapper_close() );
	printf( $provider->markups->rightmeta_wrapper_open() );
	if ( isset( $finalmetas['rightmeta'] ) ) {
		$provider->render_loop_sections( $finalmetas['rightmeta'] );
	}
	printf( $provider->markups->meta_wrapper_close() );
	?>
</div>
