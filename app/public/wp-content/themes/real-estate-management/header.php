<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Real Estate Management
 */

?>
<?php
$real_estate_management_sticky_header = get_theme_mod('real_estate_management_sticky_header');
    $data_sticky = "false";
    if ($real_estate_management_sticky_header) {
        $data_sticky = "true";
}
 ?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open();} else { do_action( 'wp_body_open' ); } ?>
<?php if(get_theme_mod('real_estate_management_preloader_hide','')){ ?>
    <div class="loading">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>
<?php } ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#skip-content"><?php esc_html_e('Skip to content', 'real-estate-management'); ?></a>
    <header id="masthead" class="site-header shadow-sm navbar-dark bg-primary">
        <div class="socialmedia" data-sticky="<?php echo esc_attr($data_sticky); ?>">
            <?php get_template_part('template-parts/topheader/top-header'); ?>
        </div>
    </header>
