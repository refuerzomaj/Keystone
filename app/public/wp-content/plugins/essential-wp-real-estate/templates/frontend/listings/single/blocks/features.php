<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider = WPERECCP()->front->listing_provider;
$features = $provider->get_meta_data( $provider->markups->prefix . 'features', get_the_ID() );
if ( ! empty( $features ) ) {
	?>
	<ul class="row p-0 m-0">
		<?php
		foreach ( $features as $feature ) {
			if ( ! empty( $feature[ $provider->markups->prefix . 'features_name' ] ) ) {

				$feature_name = $feature[ $provider->markups->prefix . 'features_name' ];
				$feature_icon = ! empty( $feature[ $provider->markups->prefix . 'features_icon' ] ) ? $feature[ $provider->markups->prefix . 'features_icon' ] : 'fa fa-bed';
				?>
				<li class="col-lg-4 col-md-6 mb-2 p-0"><i class="<?php echo esc_attr( $feature_icon ); ?> mr-1"></i><?php echo esc_html( $feature_name ); ?></li>
				<?php
			}
		}
		?>
	</ul>
	<?php
}
