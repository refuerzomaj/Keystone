<?php
/**
 * The template for displaying single listing content
 *
 * This template can be overridden by copying it to yourtheme/essential-wp-real-estate/single-cl_cpt.php.
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
do_action( $pref . 'before_listing_content' );
if ( is_active_sidebar( 'listing-single' ) ) {
	$column_class = 'col-lg-8';
} else {
	$column_class = 'col-lg-12';
}
?>
<section class="cl_listing_single">
	<div class="container">
		<div class="row">
			<div class="<?php echo esc_attr( $column_class ); ?> cl_listing_single_content">
				<?php
				do_action( $pref . 'before_listing_loop' );
				do_action( $pref . 'listing_loop' );
				do_action( $pref . 'after_listing_loop' );
				?>
			</div>
			<?php do_action( $pref . 'get_sidebar_template', 'single' ); ?>
		</div>
	</div>
</section>
<?php
do_action( $pref . 'after_listing_content', array( 'listing_id' => get_the_ID() ) );
get_footer();
