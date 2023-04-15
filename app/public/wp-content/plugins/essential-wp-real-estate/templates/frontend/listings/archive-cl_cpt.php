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

get_header();

global $pref;

$LISTING_Query = WPERECCP()->front->query->get_listing_query();

if ( is_active_sidebar( 'listing-sidebar' ) ) {
	$column_class = 'col-lg-8';
} else {
	$column_class = 'col-lg-12';
}

$map_data = wp_json_encode(
	array(
		'enable_geolocation' => cl_admin_get_option( 'enable_geolocation' ) != 1 ? false : true,
		'default_latitude'   => cl_admin_get_option( 'default_latitude' ),
		'default_longitude'  => cl_admin_get_option( 'default_longitude' ),
		'default_zoom'       => cl_admin_get_option( 'default_zoom' ),
		'geo_markup'         => esc_html__( 'You are within {{radius_value}} meters from this point', 'essential-wp-real-estate' ),
	)
);
$value    = utf8_encode( json_encode( $map_data ) );
?>
<section class="pt-2 cl_listing_archive">
	<div class="mb-4" id="map" data-map_data="<?php echo esc_attr( $map_data ); ?>" style="height: 450px;"></div>
	<div class="container">
		<!-- Sorter Section -->
		<?php
		cl_get_template( 'inc/sorter.php' );
		do_action( $pref . 'before_listing_content' );
		?>
		<div class="row">
			<!-- Sidebar Section -->
			<?php do_action( $pref . 'get_sidebar_template' ); ?>
			<!-- Looping Section -->
			<div class="<?php echo esc_attr( $column_class ); ?>">
				<?php
				do_action( $pref . 'before_listing_loop' );

				if ( $LISTING_Query->have_posts() ) {
					while ( $LISTING_Query->have_posts() ) {
						$LISTING_Query->the_post();
						do_action( $pref . 'listing_loop' );
					}
				} else {
					do_action( $pref . 'no_listings_found' );
				}
				do_action( $pref . 'after_listing_loop' );
				?>
				<div class="col-lg-12">
					<div class="pagination-wrapper">
						<?php cl_get_template( 'inc/pagination.php' ); ?>
					</div>
				</div>
			</div>
			<?php wp_reset_postdata(); ?>
		</div>
	</div>
</section>
<?php
do_action( $pref . 'after_listing_content_archive' );
do_action( $pref . 'after_listing_content' );
get_footer();
