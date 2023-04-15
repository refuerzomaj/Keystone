<?php
/**
 * @package Essential WP Real Estate
 * @version 1.0.9
 */
/*
Plugin Name: Essential WP Real Estate
Plugin URI: https://wordpress.org/plugins/essential-wp-real-estate/
Description: One of the best and advanced property listing plugin. Which is a comprehensive solution to create professional looking property listing site of any kind.
Version: 1.0.9
Requires at least: 5.2
Requires PHP: 7.2
Author: SmartDataSoft
Author URI: http://www.smartdatasoft.com/
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: essential-wp-real-estate
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPERESDS_TITLE', 'Essential WP Real Estate' );
define( 'WPERESDS_PATH', dirname( __FILE__ ) );
define( 'WPERESDS_FILE', __FILE__ );
define( 'WPERESDS_PLUGIN_VERSION', '1.0.9' );

add_action( 'plugins_loaded', 'wperesds_load_plugin_textdomain' );
if ( ! version_compare( PHP_VERSION, '7.0', '>=' ) ) {
	add_action( 'admin_notices', 'wperesds_fail_php_version' );
} elseif ( ! version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) ) {
	add_action( 'admin_notices', 'wperesds_fail_wp_version' );
} else {
	require_once __DIR__ . '/src/essential-wp-real-estate.php';
}

/**
 * wperesds_fail_php_version admin notice for minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 1.0.0
 *
 * @return void
 */

function wperesds_fail_php_version() {
	 /* translators: %s: PHP version */
	$message      = sprintf( esc_html__( '%1$s requires PHP version %2$s+, plugin is currently NOT RUNNING.', 'essential-wp-real-estate' ), WPERESDS_TITLE, '5.6' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

/**
 * wperesds_fail_wp_version admin notice for minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @since 1.0.0
 *
 * @return void
 */
function wperesds_fail_wp_version() {
	/* translators: %s: WordPress version */
	$message      = sprintf( esc_html__( '%1$s requires WordPress version %2$s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', 'essential-wp-real-estate' ), WPERESDS_TITLE, '5.2' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

/**
 * wperesds_load_plugin_textdomain loads essential-wp-real-estate textdomain.
 *
 * Load gettext translate for essential-wp-real-estate text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function wperesds_load_plugin_textdomain() {
	load_plugin_textdomain( 'essential-wp-real-estate' );
}



function wperesds_set_listing_views_count( $postID ) {
	$count_key = 'listing_views_count';
	$count     = get_post_meta( $postID, $count_key, true );
	if ( $count == '' ) {
		delete_post_meta( $postID, $count_key );
		add_post_meta( $postID, $count_key, '1' );
	} else {
		$count++;
		update_post_meta( $postID, $count_key, $count );
	}
}

/*
 * track post views
 */
function wperesds_track_listing_views( $post_id ) {
	if ( is_single() ) {
		if ( empty( $post_id ) ) {
			global $post;
			$post_id = $post->ID;
			wperesds_set_listing_views_count( $post_id );
		}
	}
}
add_action( 'wp_head', 'wperesds_track_listing_views' );



function wperesds_kmb( $count, $precision = 2 ) {
	if ( $count < 1000 ) {
		$n_format = $count;
	} elseif ( $count < 1000000 ) {
		// Anything less than a million
		$n_format = number_format( $count / 1000 ) . 'K';
	} elseif ( $count < 1000000000 ) {
		// Anything less than a billion
		$n_format = number_format( $count / 1000000, $precision ) . 'M';
	} else {
		// At least a billion
		$n_format = number_format( $count / 1000000000, $precision ) . 'B';
	}
	return $n_format;
}
