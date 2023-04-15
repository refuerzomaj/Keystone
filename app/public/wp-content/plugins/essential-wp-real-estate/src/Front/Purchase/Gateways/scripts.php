<?php

/**
 * Scripts
 *
 * @package     CL
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.0
 * @global $post
 * @return void
 */
function cl_load_scripts() {
	global $post;

	$js_dir = WPERESDS_ASSETS . '/js/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Get position in cart of current listing
	if ( isset( $post->ID ) ) {
		$position = cl_get_item_position_in_cart( $post->ID );
	}

	$has_purchase_links = false;
	if ( ( ! empty( $post->post_content ) && ( has_shortcode( $post->post_content, 'purchase_link' ) || has_shortcode( $post->post_content, 'listings' ) ) ) || is_post_type_archive( 'listing' ) ) {
		$has_purchase_links = true;
	}

	$in_footer = cl_scripts_in_footer();

	if ( cl_is_checkout() ) {
		if ( cl_is_cc_verify_enabled() ) {
			wp_register_script( 'creditCardValidator', $js_dir . 'jquery.creditCardValidator' . $suffix . '.js', array( 'jquery' ), WPERESDS_PLUGIN_VERSION, $in_footer );

			// Registered so gateways can enqueue it when they support the space formatting. wp_enqueue_script( 'jQuery.payment' );
			wp_register_script( 'jQuery.payment', $js_dir . 'jquery.payment.min.js', array( 'jquery' ), WPERESDS_PLUGIN_VERSION, $in_footer );

			wp_enqueue_script( 'creditCardValidator' );
		}

		wp_register_script( 'cl-checkout-global', $js_dir . 'cl-checkout-global' . $suffix . '.js', array( 'jquery' ), WPERESDS_PLUGIN_VERSION, $in_footer );
		wp_enqueue_script( 'cl-checkout-global' );

		wp_localize_script(
			'cl-checkout-global',
			'cl_global_vars',
			apply_filters(
				'cl_global_checkout_script_vars',
				array(
					'ajaxurl'               => cl_get_ajax_url(),
					'checkout_nonce'        => wp_create_nonce( 'cl_checkout_nonce' ),
					'checkout_error_anchor' => '#cl_purchase_submit',
					'currency_sign'         => WPERECCP()->common->formatting->cl_currency_filter( '' ),
					'currency_pos'          => cl_admin_get_option( 'currency_position', 'before' ),
					'decimal_separator'     => cl_admin_get_option( 'decimal_separator', '.' ),
					'thousands_separator'   => cl_admin_get_option( 'thousands_separator', ',' ),
					'number_of_decimal'     => cl_admin_get_option( 'number_of_decimal', '0' ),
					'no_gateway'            => __( 'Please select a payment method', 'essential-wp-real-estate' ),
					'no_discount'           => __( 'Please enter a discount code', 'essential-wp-real-estate' ), // Blank discount code message
					'enter_discount'        => __( 'Enter discount', 'essential-wp-real-estate' ),
					'discount_applied'      => __( 'Discount Applied', 'essential-wp-real-estate' ), // Discount verified message
					'no_email'              => __( 'Please enter an email address before applying a discount code', 'essential-wp-real-estate' ),
					'no_username'           => __( 'Please enter a username before applying a discount code', 'essential-wp-real-estate' ),
					'purchase_loading'      => __( 'Please Wait...', 'essential-wp-real-estate' ),
					'complete_purchase'     => WPERECCP()->front->checkout->cl_get_checkout_button_purchase_label(),
					'taxes_enabled'         => WPERECCP()->front->tax->cl_use_taxes() ? '1' : '0',
					'cl_version'            => WPERESDS_VERSION,
				)
			)
		);
	}

	// Load AJAX scripts, if enabled
	if ( ! cl_is_ajax_disabled() ) {
		wp_register_script( 'cl-ajax', $js_dir . 'cl-ajax' . $suffix . '.js', array( 'jquery' ), WPERESDS_PLUGIN_VERSION, $in_footer );
		wp_enqueue_script( 'cl-ajax' );

		wp_localize_script(
			'cl-ajax',
			'cl_scripts',
			apply_filters(
				'cl_ajax_script_vars',
				array(
					'ajaxurl'                 => cl_get_ajax_url(),
					'position_in_cart'        => isset( $position ) ? $position : -1,
					'has_purchase_links'      => $has_purchase_links,
					'already_in_cart_message' => __( 'You have already added this item to your cart', 'essential-wp-real-estate' ), // Item already in the cart message
					'empty_cart_message'      => __( 'Your cart is empty', 'essential-wp-real-estate' ), // Item already in the cart message
					'loading'                 => __( 'Loading', 'essential-wp-real-estate' ), // General loading message
					'select_option'           => __( 'Please select an option', 'essential-wp-real-estate' ), // Variable pricing error with multi-purchase option enabled
					'is_checkout'             => cl_is_checkout() ? '1' : '0',
					'default_gateway'         => WPERECCP()->common->options->cl_get_default_gateway(),
					'redirect_to_checkout'    => ( WPERECCP()->common->options->cl_straight_to_checkout() || cl_is_checkout() ) ? '1' : '0',
					'checkout_page'           => cl_get_checkout_uri(),
					'permalinks'              => get_option( 'permalink_structure' ) ? '1' : '0',
					'quantities_enabled'      => WPERECCP()->common->options->cl_item_quantities_enabled(),
					'taxes_enabled'           => WPERECCP()->front->tax->cl_use_taxes() ? '1' : '0', // Adding here for widget, but leaving in checkout vars for backcompat
				)
			)
		);
	}

	wp_enqueue_script( 'recaptcha-api', 'https://www.google.com/recaptcha/api.js', array( 'jquery' ), WPERESDS_PLUGIN_VERSION, $in_footer );

	
}
add_action( 'wp_enqueue_scripts', 'cl_load_scripts' );

/**
 * Register Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 * @return void
 */
function cl_register_styles() {

	$disable_styles = cl_admin_get_option( 'disable_styles', true );
	if ( $disable_styles == '1' ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file          = 'cl' . $suffix . '.css';
	$templates_dir = cl_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'cl.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'cl.css';
	$cl_plugin_style_sheet      = trailingslashit( WPERESDS_ASSETS_CSS_DIR ) . $file;
	$url                        = '';
	// Look in the child theme directory first, followed by the parent theme, followed by the CL core templates directory
	// Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	// This allows users to copy just cl.css to their theme
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'cl.css';
		} else {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'cl.css';
		} else {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $cl_plugin_style_sheet ) || file_exists( $cl_plugin_style_sheet ) ) {
		$url = trailingslashit( WPERESDS_ASSETS_CSS ) . $file;
	}
	if ( $url != '' ) {
		wp_register_style( 'cl-styles', $url, array(), WPERESDS_PLUGIN_VERSION, 'all' );
		wp_enqueue_style( 'cl-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'cl_register_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function cl_load_admin_scripts( $hook ) {

	if ( ! apply_filters( 'cl_load_admin_scripts', cl_is_admin_page(), $hook ) ) {
		return;
	}

	global $post;

	$js_dir  = CLS_PLUGIN_URL . 'assets/js/';
	$css_dir = CLS_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// These have to be global
	wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), WPERESDS_PLUGIN_VERSION );
	wp_enqueue_style( 'jquery-chosen' );

	wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery' . $suffix . '.js', array( 'jquery' ), WPERESDS_PLUGIN_VERSION );
	wp_enqueue_script( 'jquery-chosen' );

	wp_enqueue_script( 'jquery-form' );

	$admin_deps = array();

	if ( ! cl_is_admin_page( $hook, 'edit' ) && ! cl_is_admin_page( $hook, 'new' ) ) {
		$admin_deps = array( 'jquery', 'jquery-form', 'inline-edit-post' );
	} else {
		$admin_deps = array( 'jquery', 'jquery-form' );
	}

	wp_register_script( 'cl-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, WPERESDS_PLUGIN_VERSION, false );

	wp_enqueue_script( 'cl-admin-scripts' );

	wp_localize_script(
		'cl-admin-scripts',
		'cl_vars',
		array(
			'post_id'                => isset( $post->ID ) ? $post->ID : null,
			'cl_version'             => WPERESDS_PLUGIN_VERSION,
			'add_new_listing'        => __( 'Add New listing', 'essential-wp-real-estate' ),
			'use_this_file'          => __( 'Use This File', 'essential-wp-real-estate' ),
			'quick_edit_warning'     => __( 'Sorry, not available for variable priced products.', 'essential-wp-real-estate' ),
			'delete_payment'         => __( 'Are you sure you wish to delete this payment?', 'essential-wp-real-estate' ),
			'delete_payment_note'    => __( 'Are you sure you wish to delete this note?', 'essential-wp-real-estate' ),
			'delete_tax_rate'        => __( 'Are you sure you wish to delete this tax rate?', 'essential-wp-real-estate' ),
			'revoke_api_key'         => __( 'Are you sure you wish to revoke this API key?', 'essential-wp-real-estate' ),
			'regenerate_api_key'     => __( 'Are you sure you wish to regenerate this API key?', 'essential-wp-real-estate' ),
			'resend_receipt'         => __( 'Are you sure you wish to resend the purchase receipt?', 'essential-wp-real-estate' ),
			'disconnect_customer'    => __( 'Are you sure you wish to disconnect the WordPress user from this customer record?', 'essential-wp-real-estate' ),
			'copy_listing_link_text' => __( 'Copy these links to your clipboard and give them to your customer', 'essential-wp-real-estate' ),
			'delete_payment_listing' => sprintf( __( 'Are you sure you wish to delete this %s?', 'essential-wp-real-estate' ), cl_get_label_singular() ),
			'one_price_min'          => __( 'You must have at least one price', 'essential-wp-real-estate' ),
			'one_field_min'          => __( 'You must have at least one field', 'essential-wp-real-estate' ),
			'one_listing_min'        => __( 'Payments must contain at least one item', 'essential-wp-real-estate' ),
			'one_option'             => sprintf( __( 'Choose a %s', 'essential-wp-real-estate' ), cl_get_label_singular() ),
			'one_or_more_option'     => sprintf( __( 'Choose one or more %s', 'essential-wp-real-estate' ), cl_get_label_plural() ),
			'numeric_item_price'     => __( 'Item price must be numeric', 'essential-wp-real-estate' ),
			'numeric_item_tax'       => __( 'Item tax must be numeric', 'essential-wp-real-estate' ),
			'numeric_quantity'       => __( 'Quantity must be numeric', 'essential-wp-real-estate' ),
			'currency'               => cl_get_currency(),
			'currency_sign'          => WPERECCP()->common->formatting->cl_currency_filter( '' ),
			'currency_pos'           => cl_admin_get_option( 'currency_position', 'before' ),
			'currency_decimals'      => WPERECCP()->common->formatting->cl_currency_decimal_filter(),
			'decimal_separator'      => cl_admin_get_option( 'decimal_separator', '.' ),
			'thousands_separator'    => cl_admin_get_option( 'thousands_separator', ',' ),
			'new_media_ui'           => apply_filters( 'cl_use_35_media_ui', 1 ),
			'remove_text'            => __( 'Remove', 'essential-wp-real-estate' ),
			'type_to_search'         => sprintf( __( 'Type to search %s', 'essential-wp-real-estate' ), cl_get_label_plural() ),
			'quantities_enabled'     => cl_item_quantities_enabled(),
			'batch_export_no_class'  => __( 'You must choose a method.', 'essential-wp-real-estate' ),
			'batch_export_no_reqs'   => __( 'Required fields not completed.', 'essential-wp-real-estate' ),
			'reset_stats_warn'       => __( 'Are you sure you want to reset your store? This process is <strong><em>not reversible</em></strong>. Please be sure you have a recent backup.', 'essential-wp-real-estate' ),
			'unsupported_browser'    => __( 'We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'essential-wp-real-estate' ),
			'show_advanced_settings' => __( 'Show advanced settings', 'essential-wp-real-estate' ),
			'hide_advanced_settings' => __( 'Hide advanced settings', 'essential-wp-real-estate' ),
			'no_listings_error'      => __( 'There are no listings attached to this payment', 'essential-wp-real-estate' ),
			'wait'                   => __( 'Please wait &hellip;', 'essential-wp-real-estate' ),
		)
	);

	wp_register_script( 'cl-admin-scripts-compatibility', $js_dir . 'admin-backwards-compatibility' . $suffix . '.js', array( 'jquery', 'cl-admin-scripts' ), WPERESDS_PLUGIN_VERSION );
	wp_localize_script(
		'cl-admin-scripts-compatibility',
		'cl_backcompat_vars',
		array(
			'purchase_limit_settings'     => __( 'Purchase Limit Settings', 'essential-wp-real-estate' ),
			'simple_shipping_settings'    => __( 'Simple Shipping Settings', 'essential-wp-real-estate' ),
			'software_licensing_settings' => __( 'Software Licensing Settings', 'essential-wp-real-estate' ),
			'recurring_payments_settings' => __( 'Recurring Payments Settings', 'essential-wp-real-estate' ),
		)
	);

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	// call for media manager
	wp_enqueue_media();

	wp_register_script( 'jquery-flot', $js_dir . 'jquery.flot' . $suffix . '.js' );
	wp_enqueue_script( 'jquery-flot' );

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-tooltip' );

	$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
	wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-' . $ui_style . $suffix . '.css' );
	wp_enqueue_style( 'jquery-ui-css' );

	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

	wp_register_style( 'cl-admin', $css_dir . 'cl-admin' . $suffix . '.css', array(), WPERESDS_PLUGIN_VERSION );
	wp_enqueue_style( 'cl-admin' );
}
// add_action('admin_enqueue_scripts', 'cl_load_admin_scripts', 100);

/**
 * Admin listings Icon
 *
 * Echoes the CSS for the listings post type icon.
 *
 * @since 1.0
 * @since 2.6.11 Removed globals and CSS for custom icon
 * @return void
 */
function cl_admin_listings_icon() {
	 $images_url     = CLS_PLUGIN_URL . 'assets/images/';
	$menu_icon       = '\f316';
	$icon_cpt_url    = $images_url . 'cl-cpt.png';
	$icon_cpt_2x_url = $images_url . 'cl-cpt-2x.png';
	?>
	<style type="text/css" media="screen">
		#dashboard_right_now .listing-count:before {
			content: '<?php echo esc_attr( $menu_icon ); ?>';
		}

		#icon-edit.icon32-posts-listing {
			background: url(<?php echo esc_url( $icon_cpt_url ); ?>) -7px -5px no-repeat;
		}

		@media only screen and (-webkit-min-device-pixel-ratio: 1.5),
		only screen and (min--moz-device-pixel-ratio: 1.5),
		only screen and (-o-min-device-pixel-ratio: 3/2),
		only screen and (min-device-pixel-ratio: 1.5),
		only screen and (min-resolution: 1.5dppx) {
			#icon-edit.icon32-posts-listing {
				background: url(<?php echo esc_url( $icon_cpt_2x_url ); ?>) no-repeat -7px -5px !important;
				background-size: 55px 45px !important;
			}
		}
	</style>
	<?php
}
add_action( 'admin_head', 'cl_admin_listings_icon' );


/**
 * Load head styles
 *
 * Ensures listing styling is still shown correctly if a theme is using the CSS template file
 *
 * @since 2.5
 * @global $post
 * @return void
 */
function cl_load_head_styles() {
	global $post;

	if ( cl_admin_get_option( 'disable_styles', false ) || ! is_object( $post ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file          = 'cl' . $suffix . '.css';
	$templates_dir = cl_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'cl.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'cl.css';

	$has_css_template = false;

	if (
		has_shortcode( $post->post_content, 'listings' ) &&
		file_exists( $child_theme_style_sheet ) ||
		file_exists( $child_theme_style_sheet_2 ) ||
		file_exists( $parent_theme_style_sheet ) ||
		file_exists( $parent_theme_style_sheet_2 )
	) {
		$has_css_template = apply_filters( 'cl_load_head_styles', true );
	}

	if ( ! $has_css_template ) {
		return;
	}

	?>
	<style>
		.cl_listing {
			float: left;
		}

		.cl_listing_columns_1 .cl_listing {
			width: 100%;
		}

		.cl_listing_columns_2 .cl_listing {
			width: 50%;
		}

		.cl_listing_columns_0 .cl_listing,
		.cl_listing_columns_3 .cl_listing {
			width: 33%;
		}

		.cl_listing_columns_4 .cl_listing {
			width: 25%;
		}

		.cl_listing_columns_5 .cl_listing {
			width: 20%;
		}

		.cl_listing_columns_6 .cl_listing {
			width: 16.6%;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'cl_load_head_styles' );

/**
 * Determine if the frontend scripts should be loaded in the footer or header (default: footer)
 *
 * @since 2.8.6
 * @return mixed
 */
function cl_scripts_in_footer() {
	return apply_filters( 'cl_load_scripts_in_footer', true );
}
