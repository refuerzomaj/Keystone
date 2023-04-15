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
$listing_columns  = cl_admin_get_option( 'layout_columns_list', '6' );
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
$archive_setting = get_option( 'cl_archive_setting_list_view', array() );
if ( isset( $archive_setting ) && ! empty( $archive_setting ) ) {
	$fields = json_decode( $archive_setting, true );
} else {
	$fields = array(
		// -- Thumbnail Section hook data
		'thumbnail_section' => array(
			'top_left'     => array(),
			'top_right'    => array(),
			'bottom_left'  => array(),
			'bottom_right' => array(),
		),
		// -- Content Section hook data
		'content_section'   => array(
			'top_left'  => array( 'listing_types' ),
			'top_right' => array( 'listing_price' ),
			'main'      => array( 'listing_title', 'listing_excerpt' ),
			'bottom'    => array( 'listing_features' ),
		),
		// -- Footer Section hook data
		'footer_section'    => array(
			'top'    => array( 'listing_ratings' ),
			'bottom' => array( 'listing_favourite', 'listing_compare', 'listing_view' ),
		),
	);
}

?>
<!-- Single Property -->
<div <?php post_class( $listing_columns . ' col-sm-12 listing_data' ); ?> id="post-<?php the_ID(); ?>" data-listing="<?php echo esc_attr( $value ); ?>">
	<div class="property-listing property-list">
		<!-- Thumbnail -->
		<div class="thumbnail-section lazy-section">
			<div class="wperesds-thumb-sec top-left"><?php $provider->render_loop_sections( $fields['thumbnail_section']['top_left'] ); ?></div>
			<div class="wperesds-thumb-sec top-right"><?php $provider->render_loop_sections( $fields['thumbnail_section']['top_right'] ); ?></div>
			<div class="wperesds-thumb-sec bottom-left"><?php $provider->render_loop_sections( $fields['thumbnail_section']['bottom_left'] ); ?></div>
			<div class="wperesds-thumb-sec bottom-right"><?php $provider->render_loop_sections( $fields['thumbnail_section']['bottom_right'] ); ?></div>
			<?php cl_get_template( 'inc/featured-image.php' ); ?>
		</div>
		<!-- Content -->
		<div class="content-section">
			<div class="listing-detail-wrapper">
				<div class="listing-short-detail-wrap">
					<div class="_card_list_flex mb-2">
						<div class="_card_flex_left"><?php $provider->render_loop_sections( $fields['content_section']['top_left'] ); ?></div>
						<div class="_card_flex_right"><?php $provider->render_loop_sections( $fields['content_section']['top_right'] ); ?></div>
					</div>
					<?php $provider->render_loop_sections( $fields['content_section']['main'], '<div class="_card_list_flex">', '</div>' ); ?>
				</div>
			</div>
			<div class="price-features-wrapper">
				<?php $provider->render_loop_sections( $fields['content_section']['bottom'] ); ?>
			</div>
			<div class="listing-detail-footer">
				<div class="footer-flex">
					<?php $provider->render_loop_sections( $fields['footer_section']['top'] ); ?>
				</div>
				<div class="footer-flex">
					<?php $provider->render_loop_sections( $fields['footer_section']['bottom'] ); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Single Property -->
