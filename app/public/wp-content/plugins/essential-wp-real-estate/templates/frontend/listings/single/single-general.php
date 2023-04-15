<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Essential\Restate\Common\Provider\Provider;

$provider              = WPERECCP()->front->listing_provider;
$headings              = Provider::getInstance()->get_group_headings();
$headings             += Provider::getInstance()->get_single_temp_data();
$get_group_data        = Provider::getInstance()->get_group_data();
$get_default_templates = Provider::getInstance()->get_default_templates();
$class                 = '';
if ( isset( $args['class'] ) ) {
	$class = $args['class'];
	unset( $args['class'] );
}
foreach ( $args as $group => $data ) {
	if ( $data['active'] ) { ?>
		<div id="wperesds-general-<?php echo esc_attr( $group ); ?>" class="listing-single-general property_block_wrap">
			<div class="single-general-header property_block_wrap_header">
				<h4 class="property_block_title">
					<?php
					if ( key_exists( $group, $headings ) ) {
						$heading = str_replace( '_', ' ', $headings[ $group ] );
						echo esc_html( $heading );
					}
					?>
				</h4>
			</div>
			<div class="block-body">
				<?php
				if ( in_array( $group, $get_default_templates ) ) {
					cl_get_template( "single/blocks/{$group}.php" );
				} else {
					if ( isset( $get_group_data[ $group ] ) && ! empty( $get_group_data[ $group ] ) ) {
						foreach ( $get_group_data[ $group ] as $key => $value ) {
							cl_get_template( "single/layouts/{$value['type']}.php", $value );
							$val = $provider->get_meta_data( $value['id'], get_the_ID() );
						}
					}
				}
				?>
			</div>
		</div>
		<?php
	}
}
