<?php
/**
 * Real Estate Management functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Real Estate Management
 */

include get_theme_file_path( 'vendor/wptrt/autoload/src/Real_Estate_Management_Loader.php' );

$Real_Estate_Management_Loader = new \WPTRT\Autoload\Real_Estate_Management_Loader();

$Real_Estate_Management_Loader->real_estate_management_add( 'WPTRT\\Customize\\Section', get_theme_file_path( 'vendor/wptrt/customize-section-button/src' ) );

$Real_Estate_Management_Loader->real_estate_management_register();

if ( ! function_exists( 'real_estate_management_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function real_estate_management_setup() {

		load_theme_textdomain( 'real-estate-management', get_template_directory() . '/languages' );
		add_theme_support( 'woocommerce' );
		add_theme_support( "responsive-embeds" );
		add_theme_support( "align-wide" );
		add_theme_support( "wp-block-styles" );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

        add_image_size('real-estate-management-featured-header-image', 2000, 660, true);

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'primary' => esc_html__( 'Primary','real-estate-management' ),
	        'footer'=> esc_html__( 'Footer Menu','real-estate-management' ),
        ) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'real_estate_management_custom_background_args', array(
			'default-color' => 'f7ebe5',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 50,
			'width'       => 50,
			'flex-width'  => true,
		) );

		add_editor_style( array( '/editor-style.css' ) );
	}
endif;
add_action( 'after_setup_theme', 'real_estate_management_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function real_estate_management_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'real_estate_management_content_width', 1170 );
}
add_action( 'after_setup_theme', 'real_estate_management_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function real_estate_management_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'real-estate-management' ),
		'id'            => 'sidebar',
		'description'   => esc_html__( 'Add widgets here.', 'real-estate-management' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h5 class="widget-title">',
		'after_title'   => '</h5>',
	) );
}
add_action( 'widgets_init', 'real_estate_management_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function real_estate_management_scripts() {

	require_once get_theme_file_path( 'inc/wptt-webfont-loader.php' );

	wp_enqueue_style(
		'noto',
		wptt_get_webfont_url( 'https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap' ),
		array(),
		'1.0'
	);

	wp_enqueue_style( 'real-estate-management-block-editor-style', get_theme_file_uri('/assets/css/block-editor-style.css') );

	// load bootstrap css
    wp_enqueue_style( 'bootstrap-css', get_template_directory_uri() . '/assets/css/bootstrap.css');

    wp_enqueue_style( 'owl.carousel-css', get_template_directory_uri() . '/assets/css/owl.carousel.css');

	wp_enqueue_style( 'real-estate-management-style', get_stylesheet_uri() );

	// fontawesome
	wp_enqueue_style( 'fontawesome-style', get_template_directory_uri() .'/assets/css/fontawesome/css/all.css' );

    wp_enqueue_script('real-estate-management-theme-js', get_template_directory_uri() . '/assets/js/theme-script.js', array('jquery'), '', true );

    wp_enqueue_script('owl.carousel-js', get_template_directory_uri() . '/assets/js/owl.carousel.js', array('jquery'), '', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'real_estate_management_scripts' );

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Meta Feild
 */
require get_template_directory() . '/inc/popular-property-meta.php';

/*dropdown page sanitization*/
function real_estate_management_sanitize_dropdown_pages( $page_id, $setting ) {
	$page_id = absint( $page_id );
	return ( 'publish' == get_post_status( $page_id ) ? $page_id : $setting->default );
}

function real_estate_management_sanitize_select( $input, $setting ){
    $input = sanitize_key($input);
    $choices = $setting->manager->get_control( $setting->id )->choices;
    return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}

function real_estate_management_sanitize_checkbox( $input ) {
	// Boolean check
	return ( ( isset( $input ) && true == $input ) ? true : false );
}

function real_estate_management_sanitize_phone_number( $phone ) {
	return preg_replace( '/[^\d+]/', '', $phone );
}

if (!function_exists('real_estate_management_loop_columns')) {
		function real_estate_management_loop_columns() {
		return 3;
	}
}
add_filter('loop_shop_columns', 'real_estate_management_loop_columns');

/**
 * Get CSS
 */

function real_estate_management_getpage_css($hook) {
	if ( 'appearance_page_real-estate-management-info' != $hook ) {
		return;
	}
	wp_enqueue_style( 'real-estate-management-demo-style', get_template_directory_uri() . '/assets/css/demo.css' );
}
add_action( 'admin_enqueue_scripts', 'real_estate_management_getpage_css' );

add_action('after_switch_theme', 'real_estate_management_setup_options');

function real_estate_management_setup_options () {
	wp_redirect( admin_url() . 'themes.php?page=real-estate-management-info.php' );
}

if ( ! defined( 'REAL_ESTATE_MANAGEMENT_CONTACT_SUPPORT' ) ) {
define('REAL_ESTATE_MANAGEMENT_CONTACT_SUPPORT',__('https://wordpress.org/support/theme/real-estate-management','real-estate-management'));
}
if ( ! defined( 'REAL_ESTATE_MANAGEMENT_REVIEW' ) ) {
define('REAL_ESTATE_MANAGEMENT_REVIEW',__('https://wordpress.org/support/theme/real-estate-management/reviews/#new-post','real-estate-management'));
}
if ( ! defined( 'REAL_ESTATE_MANAGEMENT_LIVE_DEMO' ) ) {
define('REAL_ESTATE_MANAGEMENT_LIVE_DEMO',__('https://www.themagnifico.net/demo/real-estate-management/','real-estate-management'));
}
if ( ! defined( 'REAL_ESTATE_MANAGEMENT_GET_PREMIUM_PRO' ) ) {
define('REAL_ESTATE_MANAGEMENT_GET_PREMIUM_PRO',__('https://www.themagnifico.net/themes/property-management-wordpress-theme/','real-estate-management'));
}
if ( ! defined( 'REAL_ESTATE_MANAGEMENT_PRO_DOC' ) ) {
define('REAL_ESTATE_MANAGEMENT_PRO_DOC',__('https://www.themagnifico.net/eard/wathiqa/real-estate-management-pro-doc/','real-estate-management'));
}

add_action('admin_menu', 'real_estate_management_themepage');
function real_estate_management_themepage(){
	$real_estate_management_theme_info = add_theme_page( __('Theme Options','real-estate-management'), __('Theme Options','real-estate-management'), 'manage_options', 'real-estate-management-info.php', 'real_estate_management_info_page' );
}

function real_estate_management_info_page() {
	$real_estate_management_user = wp_get_current_user();
	$real_estate_management_theme = wp_get_theme();
	?>
	<div class="wrap about-wrap real-estate-management-add-css">
		<div>
			<h1>
				<?php esc_html_e('Welcome To ','real-estate-management'); ?><?php echo esc_html( $real_estate_management_theme ); ?>
			</h1>
			<div class="feature-section three-col">
				<div class="col">
					<div class="widgets-holder-wrap">
						<h3><?php esc_html_e("Contact Support", "real-estate-management"); ?></h3>
						<p><?php esc_html_e("Thank you for trying Real Estate Management , feel free to contact us for any support regarding our theme.", "real-estate-management"); ?></p>
						<p><a target="_blank" href="<?php echo esc_url( REAL_ESTATE_MANAGEMENT_CONTACT_SUPPORT ); ?>" class="button button-primary get">
							<?php esc_html_e("Contact Support", "real-estate-management"); ?>
						</a></p>
					</div>
				</div>
				<div class="col">
					<div class="widgets-holder-wrap">
						<h3><?php esc_html_e("Checkout Premium", "real-estate-management"); ?></h3>
						<p><?php esc_html_e("Our premium theme comes with extended features like demo content import , responsive layouts etc.", "real-estate-management"); ?></p>
						<p><a target="_blank" href="<?php echo esc_url( REAL_ESTATE_MANAGEMENT_GET_PREMIUM_PRO ); ?>" class="button button-primary get">
							<?php esc_html_e("Get Premium", "real-estate-management"); ?>
						</a></p>
					</div>
				</div>
				<div class="col">
					<div class="widgets-holder-wrap">
						<h3><?php esc_html_e("Review", "real-estate-management"); ?></h3>
						<p><?php esc_html_e("If You love Real Estate Management theme then we would appreciate your review about our theme.", "real-estate-management"); ?></p>
						<p><a target="_blank" href="<?php echo esc_url( REAL_ESTATE_MANAGEMENT_REVIEW ); ?>" class="button button-primary get">
							<?php esc_html_e("Review", "real-estate-management"); ?>
						</a></p>
					</div>
				</div>
			</div>
		</div>
		<hr>

		<h2><?php esc_html_e("Free Vs Premium","real-estate-management"); ?></h2>
		<div class="real-estate-management-button-container">
			<a target="_blank" href="<?php echo esc_url( REAL_ESTATE_MANAGEMENT_PRO_DOC ); ?>" class="button button-primary get">
				<?php esc_html_e("Checkout Documentation", "real-estate-management"); ?>
			</a>
			<a target="_blank" href="<?php echo esc_url( REAL_ESTATE_MANAGEMENT_LIVE_DEMO ); ?>" class="button button-primary get">
				<?php esc_html_e("View Theme Demo", "real-estate-management"); ?>
			</a>
		</div>

		<table class="wp-list-table widefat">
			<thead class="table-book">
				<tr>
					<th><strong><?php esc_html_e("Theme Feature", "real-estate-management"); ?></strong></th>
					<th><strong><?php esc_html_e("Basic Version", "real-estate-management"); ?></strong></th>
					<th><strong><?php esc_html_e("Premium Version", "real-estate-management"); ?></strong></th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td><?php esc_html_e("Header Background Color", "real-estate-management"); ?></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Custom Navigation Logo Or Text", "real-estate-management"); ?></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Hide Logo Text", "real-estate-management"); ?></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>

				<tr>
					<td><?php esc_html_e("Premium Support", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Fully SEO Optimized", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Recent Posts Widget", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>

				<tr>
					<td><?php esc_html_e("Easy Google Fonts", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Pagespeed Plugin", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Only Show Header Image On Front Page", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Show Header Everywhere", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Custom Text On Header Image", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Full Width (Hide Sidebar)", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Only Show Upper Widgets On Front Page", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Replace Copyright Text", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Customize Upper Widgets Colors", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Customize Navigation Color", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Customize Post/Page Color", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Customize Blog Feed Color", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Customize Footer Color", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Customize Sidebar Color", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Customize Background Color", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
				<tr>
					<td><?php esc_html_e("Importable Demo Content	", "real-estate-management"); ?></td>
					<td><span class="cross"><span class="dashicons dashicons-dismiss"></span></span></td>
					<td><span class="tick"><span class="dashicons dashicons-yes-alt"></span></span></td>
				</tr>
			</tbody>
		</table>
		<div class="real-estate-management-button-container">
			<a target="_blank" href="<?php echo esc_url( REAL_ESTATE_MANAGEMENT_GET_PREMIUM_PRO ); ?>" class="button button-primary get">
				<?php esc_html_e("Go Premium", "real-estate-management"); ?>
			</a>
		</div>
	</div>
	<?php
}
