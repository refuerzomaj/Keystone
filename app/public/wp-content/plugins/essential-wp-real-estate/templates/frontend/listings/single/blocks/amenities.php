<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$provider  = WPERECCP()->front->listing_provider;
$ameneties = $provider->get_meta_data( 'wperesds_amenities', get_the_ID() );

if ( ! empty( $ameneties ) ) { ?>
	<ul class="avl-features">
		<?php
		foreach ( $ameneties as $amenity ) {
			$amenity_chk = $amenity[ $provider->markups->prefix . 'amenity_name' ];
			if ( ! empty( $amenity_chk ) ) {
				if ( $amenity[ $provider->markups->prefix . 'amenity_status' ] == 'Yes' ) {
					$status = 'active';
				} else {
					$status = null;
				}
				?>
				<li class="<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $amenity[ $provider->markups->prefix . 'amenity_name' ] ); ?></li>
				<?php
			}
		}
		?>
	</ul>
	<?php
}
