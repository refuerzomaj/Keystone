<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider = WPERECCP()->front->listing_provider;
$floor    = $provider->get_meta_data( 'wperesds_floor', get_the_ID() );
?>
<div class="accordion" id="floor-option">
	<?php
	if ( ! empty( $floor ) && is_array( $floor ) ) {
		foreach ( $floor as $key => $floor ) {
			if ( ! empty( $floor[ $provider->markups->prefix . 'floor_name' ] ) ) {
				?>
				<div class="card">
					<div class="card-header" id="floor<?php echo esc_attr( $key ); ?>">
						<h6 class="pt-3 pb-2 title" data-bs-toggle="collapse" data-bs-target="#flooritem<?php echo esc_attr( $key ); ?>" aria-controls="flooritem<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $floor['wperesds_floor_name'] ); ?></h6>
						<div class="floor_listeo">
							<ul>
								<?php if ( ! empty( $floor['wperesds_floor_beds'] ) ) { ?>
								<li><?php echo esc_html__( 'Beds: ', 'essential-wp-real-estate' ) . esc_html( $floor['wperesds_floor_beds'] ); ?></li>
								<?php } ?>
								<?php if ( ! empty( $floor['wperesds_floor_baths'] ) ) { ?>
								<li><?php echo esc_html__( 'Baths: ', 'essential-wp-real-estate' ) . esc_html( $floor['wperesds_floor_baths'] ); ?></li>
								<?php } ?>
								<?php if ( ! empty( $floor['wperesds_floor_area'] ) ) { ?>
								<li><span><?php echo esc_html( $floor['wperesds_floor_area'] ) . ' ' . esc_html__( 'sqft', 'essential-wp-real-estate' ); ?></span></li>
								<?php } ?>
							</ul>
						</div>
					</div>
					<div id="flooritem<?php echo esc_attr( $key ); ?>" class="collapse" aria-labelledby="floor<?php echo esc_attr( $key ); ?>" data-parent="#floor-option">
						<div class="card-body">
							<?php
							if ( isset( $floor['wperesds_floor_blueprint'] ) && is_array( $floor['wperesds_floor_blueprint'] ) ) {
								foreach ( $floor['wperesds_floor_blueprint'] as $key => $value ) {
									echo wp_get_attachment_image( $floor['wperesds_floor_blueprint'][ $key ], 'full', false, 'class=img-fluid' );
								}
							}
							?>
						</div>
					</div>
				</div>
				<?php
			} else {
				echo esc_html__( 'No Floor Plan Included', 'essential-wp-real-estate' );
			}
		}
	}
	?>
</div>
