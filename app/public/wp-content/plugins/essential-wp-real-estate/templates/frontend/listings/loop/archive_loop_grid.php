<?php
/**
 * The template for displaying listing content
 *
 * This template can be overridden by copying it to yourtheme/essential-wp-real-estate/archive-cl_cpt.php.
 *
 * HOWEVER, on occasion essential-wp-real-estate will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.essential-wp-real-estate.com/document/template-structure/
 * @package essential-wp-real-estate/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Getting global $pref val
global $pref;
$provider         = WPERECCP()->front->listing_provider;
$listing_columns  = cl_admin_get_option( 'layout_columns_grid', '4' );
$maps_fields_data = $provider->get_meta_data( $provider->prefix . 'maps_fields' );
// START MAP DATA
$img_url      = ! empty( get_the_post_thumbnail_url() ) ? get_the_post_thumbnail_url() : WPERESDS_ASSETS . '/img/placeholder_light.png';
$listing_data = wp_json_encode(
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
$value        = utf8_encode( json_encode( $listing_data ) );
// END MAP DATA

$archive_setting = get_option( 'cl_archive_setting_grid_view', array() );

if ( empty( $archive_setting ) || $archive_setting == 'null' ) {
	$fields = array(
		// -- Thumbnail Section hook data
		'thumbnail_section' => array(
			'topleft'     => array( 'listing_types' ),
			'topright'    => array(),
			'bottomleft'  => array(),
			'bottomright' => array( 'listing_ratings' ),
		),
		// -- Content Section hook data
		'sectionone'        => array(
			'sectiononeleft'  => array( 'listing_types' ),
			'sectiononeright' => array( 'listing_price' ),
		),
		'sectiontwo'        => array( 'listing_title', 'listing_excerpt', 'listing_features' ),
		// -- Footer Section hook data
		'sectionfive'       => array(
			'sectionfiveleft'  => array( 'listing_location' ),
			'sectionfiveright' => array( 'listing_favourite', 'listing_compare', 'listing_view' ),
		),
	);
} else {
	$fields          = array();
	$archive_setting = json_decode( $archive_setting, true );
	foreach ( $archive_setting['sectionzero'] as $key => $asettings ) {
		if ( is_array( $asettings ) ) {
			$fields['thumbnail_section'][ $key ] = array();
			foreach ( $asettings as $keyname => $asetting ) {
				if ( is_array( $asetting ) ) {
					if ( $asetting['active'] == 1 ) {
						$fields['thumbnail_section'][ $key ][] = 'listing_types';
					}
				}
			}
		}
	}
	array_shift( $archive_setting );
	foreach ( $archive_setting as $setting_name => $setting_val ) {
		unset( $setting_val['class'] );
		$fields[ $setting_name ] = array();
		foreach ( $setting_val as $key => $section ) {
			if ( $key == 'block' ) {
				continue;
			}
			if ( count( $section ) !== count( $section, COUNT_RECURSIVE ) ) {
				foreach ( $section as $keysec => $sectionchild ) {
					if ( $keysec != 'class' && $keysec != 'block' ) {
						if ( is_array( $sectionchild ) ) {
							if ( $sectionchild['active'] ) {
								$fields[ $setting_name ][ $key ][] = 'listing_' . $keysec;
							}
						}
					}
				}
			} else {
				if ( $key != 'class' && $key != 'block' ) {
					if ( is_array( $section ) ) {
						if ( $section['active'] ) {
							$fields[ $setting_name ][] = 'listing_' . $key;
						}
					}
				}
			}
		}
	}
}

?>
<!-- Single Property -->
<div <?php post_class( $listing_columns . ' col-sm-12 listing_data' ); ?> id="post-<?php the_ID(); ?>" data-listing="<?php echo esc_attr( $value ); ?>">
	<div class="property-listing property-grid">
		<!-- Thumbnail -->
		<div class="thumbnail-section lazy-section">
			<div class="wperesds-thumb-sec top-left"><?php $provider->render_loop_sections( $fields['thumbnail_section']['topleft'] ); ?></div>
			<div class="wperesds-thumb-sec top-right"><?php $provider->render_loop_sections( $fields['thumbnail_section']['topright'] ); ?></div>
			<div class="wperesds-thumb-sec bottom-left"><?php $provider->render_loop_sections( $fields['thumbnail_section']['bottomleft'] ); ?></div>
			<div class="wperesds-thumb-sec bottom-right"><?php $provider->render_loop_sections( $fields['thumbnail_section']['bottomright'] ); ?></div>
			<?php cl_get_template( 'inc/featured-image.php' ); ?>
		</div>
		<!-- Content -->
		<div class="content-section">
			<div class="listing-detail-wrapper">
				<div class="listing-short-detail-wrap">
					<?php
					array_shift( $fields );
					foreach ( $fields as $keys => $values ) {
						$counter = 1;
						echo '<div class="_card_list_flex mb-2">';

						if ( count( $values ) !== count( $values, COUNT_RECURSIVE ) ) {
							foreach ( $values as $key => $value ) {
								if ( is_array( $value ) ) {
									echo '<div class="_card_flex_' . ( ( $counter % 2 ) ? 'left' : 'right' ) . '">';
									$provider->render_loop_sections( $value );
									echo '</div>';
									$counter++;
								}
							}
						} else {
							$provider->render_loop_sections( $values, '<div class="_card_list_flex">', '</div>' );
						}
						echo '</div>';
					}
					?>

				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Single Property -->
