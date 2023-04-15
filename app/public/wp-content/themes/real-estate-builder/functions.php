<?php
/**
 * Real Estate Builder functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Real Estate Builder
 */

if ( ! defined( 'REAL_ESTATE_MANAGEMENT_URL' ) ) {
    define( 'REAL_ESTATE_MANAGEMENT_URL', esc_url( 'https://www.themagnifico.net/themes/estate-wordpress-theme/', 'real-estate-builder') );
}
if ( ! defined( 'REAL_ESTATE_MANAGEMENT_TEXT' ) ) {
    define( 'REAL_ESTATE_MANAGEMENT_TEXT', __( 'Real Estate Builder Pro','real-estate-builder' ));
}

function real_estate_builder_enqueue_styles() {
    wp_enqueue_style( 'bootstrap-css', get_template_directory_uri() . '/assets/css/bootstrap.css');
    $real_estate_builder_parentcss = 'real-estate-management-style';
    $real_estate_builder_theme = wp_get_theme(); wp_enqueue_style( $real_estate_builder_parentcss, get_template_directory_uri() . '/style.css', array(), $real_estate_builder_theme->parent()->get('Version'));
    wp_enqueue_style( 'real-estate-builder-style', get_stylesheet_uri(), array( $real_estate_builder_parentcss ), $real_estate_builder_theme->get('Version'));

    wp_enqueue_script( 'comment-reply', '/wp-includes/js/comment-reply.min.js', array(), false, true );  
}

add_action( 'wp_enqueue_scripts', 'real_estate_builder_enqueue_styles' );

function real_estate_builder_customize_register($wp_customize){

    // Our Services
    $wp_customize->add_section('real_estate_builder_our_services_section',array(
        'title' => esc_html__('Our Services','real-estate-builder'),
        'description' => esc_html__('Here you have to select category which will display perticular services in the home page.','real-estate-builder'),
    ));

    $wp_customize->add_setting('real_estate_builder_projects_short_title',array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    $wp_customize->add_control('real_estate_builder_projects_short_title',array(
        'label' => esc_html__('Section Title','real-estate-builder'),
        'section' => 'real_estate_builder_our_services_section',
        'setting' => 'real_estate_builder_projects_short_title',
        'type'  => 'text'
    ));

    $wp_customize->add_setting('real_estate_builder_projects_title',array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    $wp_customize->add_control('real_estate_builder_projects_title',array(
        'label' => esc_html__('Section Text','real-estate-builder'),
        'section' => 'real_estate_builder_our_services_section',
        'setting' => 'real_estate_builder_projects_title',
        'type'  => 'text'
    ));

    $categories = get_categories();
    $cat_post = array();
    $cat_post[]= 'select';
    $i = 0;
    foreach($categories as $category){
        if($i==0){
            $default = $category->slug;
            $i++;
        }
        $cat_post[$category->slug] = $category->name;
    }

    $wp_customize->add_setting('real_estate_builder_services_category',array(
        'default'   => 'select',
        'sanitize_callback' => 'real_estate_management_sanitize_select',
    ));
    $wp_customize->add_control('real_estate_builder_services_category',array(
        'type'    => 'select',
        'choices' => $cat_post,
        'label' => __('Select Category to display services','real-estate-builder'),
        'section' => 'real_estate_builder_our_services_section',
    ));

    $wp_customize->add_setting('real_estate_builder_services_per_page',array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    $wp_customize->add_control('real_estate_builder_services_per_page',array(
        'label' => esc_html__('No Of Icons','real-estate-builder'),
        'section' => 'real_estate_builder_our_services_section',
        'setting' => 'real_estate_builder_services_per_page',
        'type'  => 'text'
    ));

    $icon = get_theme_mod('real_estate_builder_services_per_page','');
    for ($i=1; $i <= $icon; $i++) {
        $wp_customize->add_setting('real_estate_builder_services_icon'.$i,array(
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        $wp_customize->add_control('real_estate_builder_services_icon'.$i,array(
            'label' => esc_html__('Icon ','real-estate-builder').$i,
            'section' => 'real_estate_builder_our_services_section',
            'setting' => 'real_estate_builder_services_icon'.$i,
            'type'  => 'text'
        ));
    }
}
add_action('customize_register', 'real_estate_builder_customize_register');

if ( ! function_exists( 'real_estate_builder_setup' ) ) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function real_estate_builder_setup() {

        add_theme_support( 'responsive-embeds' );

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

        add_image_size('real-estate-builder-featured-header-image', 2000, 660, true);

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
            'default-color' => '',
            'default-image' => '',
        ) ) );

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

        add_theme_support( 'align-wide' );

        add_theme_support( 'wp-block-styles' );
    }
endif;
add_action( 'after_setup_theme', 'real_estate_builder_setup' );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function real_estate_builder_widgets_init() {
        register_sidebar( array(
        'name'          => esc_html__( 'Sidebar', 'real-estate-builder' ),
        'id'            => 'sidebar',
        'description'   => esc_html__( 'Add widgets here.', 'real-estate-builder' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h5 class="widget-title">',
        'after_title'   => '</h5>',
    ) );
}
add_action( 'widgets_init', 'real_estate_builder_widgets_init' );

function real_estate_builder_remove_my_action() {
    remove_action( 'admin_menu','real_estate_management_themepage' );
    remove_action( 'after_switch_theme','real_estate_management_setup_options' );
}
add_action( 'init', 'real_estate_builder_remove_my_action');

function real_estate_builder_remove_customize_register() {
    global $wp_customize;
    $wp_customize->remove_section( 'real_estate_builder_top_slider' );
}

add_action( 'customize_register', 'real_estate_builder_remove_customize_register', 11 );