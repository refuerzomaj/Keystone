<?php
/**
 * Real Estate Management Theme Customizer
 *
 * @link: https://developer.wordpress.org/themes/customize-api/customizer-objects/
 *
 * @package Real Estate Management
 */

if ( ! defined( 'REAL_ESTATE_MANAGEMENT_URL' ) ) {
    define( 'REAL_ESTATE_MANAGEMENT_URL', esc_url( 'https://www.themagnifico.net/themes/property-management-wordpress-theme/', 'real-estate-management') );
}
if ( ! defined( 'REAL_ESTATE_MANAGEMENT_TEXT' ) ) {
    define( 'REAL_ESTATE_MANAGEMENT_TEXT', __( 'Real Estate Pro','real-estate-management' ));
}

use WPTRT\Customize\Section\Real_Estate_Management_Button;

add_action( 'customize_register', function( $manager ) {

    $manager->register_section_type( Real_Estate_Management_Button::class );

    $manager->add_section(
        new Real_Estate_Management_Button( $manager, 'real_estate_management_pro', [
            'title'       => esc_html( REAL_ESTATE_MANAGEMENT_TEXT, 'real-estate-management' ),
            'priority'    => 0,
            'button_text' => __( 'GET PREMIUM', 'real-estate-management' ),
            'button_url'  => esc_url( REAL_ESTATE_MANAGEMENT_URL)
        ] )
    );

} );

// Load the JS and CSS.
add_action( 'customize_controls_enqueue_scripts', function() {

    $version = wp_get_theme()->get( 'Version' );

    wp_enqueue_script(
        'real-estate-management-customize-section-button',
        get_theme_file_uri( 'vendor/wptrt/customize-section-button/public/js/customize-controls.js' ),
        [ 'customize-controls' ],
        $version,
        true
    );

    wp_enqueue_style(
        'real-estate-management-customize-section-button',
        get_theme_file_uri( 'vendor/wptrt/customize-section-button/public/css/customize-controls.css' ),
        [ 'customize-controls' ],
        $version
    );

} );

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function real_estate_management_customize_register($wp_customize){
    $wp_customize->get_setting('blogname')->transport = 'postMessage';
    $wp_customize->get_setting('blogdescription')->transport = 'postMessage';

    $wp_customize->add_setting('real_estate_management_logo_title_text', array(
        'default' => true,
        'sanitize_callback' => 'real_estate_management_sanitize_checkbox'
    ));
    $wp_customize->add_control( new WP_Customize_Control($wp_customize,'real_estate_management_logo_title_text',array(
        'label'          => __( 'Enable Disable Title', 'real-estate-management' ),
        'section'        => 'title_tagline',
        'settings'       => 'real_estate_management_logo_title_text',
        'type'           => 'checkbox',
    )));

    $wp_customize->add_setting('real_estate_management_theme_description', array(
        'default' => false,
        'sanitize_callback' => 'real_estate_management_sanitize_checkbox'
    ));
    $wp_customize->add_control( new WP_Customize_Control($wp_customize,'real_estate_management_theme_description',array(
        'label'          => __( 'Enable Disable Tagline', 'real-estate-management' ),
        'section'        => 'title_tagline',
        'settings'       => 'real_estate_management_theme_description',
        'type'           => 'checkbox',
    )));


    //Header
    $wp_customize->add_section('real_estate_management_header',array(
        'title' => esc_html__('Header Option','real-estate-management')
    ));

    $wp_customize->add_setting('real_estate_management_email_text',array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    $wp_customize->add_control('real_estate_management_email_text',array(
        'label' => esc_html__('Email Text','real-estate-management'),
        'section' => 'real_estate_management_header',
        'setting' => 'real_estate_management_email_text',
        'type'  => 'text'
    ));

    $wp_customize->add_setting('real_estate_management_email',array(
        'default' => '',
        'sanitize_callback' => 'sanitize_email'
    ));
    $wp_customize->add_control('real_estate_management_email',array(
        'label' => esc_html__('Email','real-estate-management'),
        'section' => 'real_estate_management_header',
        'setting' => 'real_estate_management_email',
        'type'  => 'text'
    ));

    $wp_customize->add_setting('real_estate_management_topbar_btn_text',array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    $wp_customize->add_control('real_estate_management_topbar_btn_text',array(
        'label' => esc_html__('Button Text','real-estate-management'),
        'section' => 'real_estate_management_header',
        'setting' => 'real_estate_management_topbar_btn_text',
        'type'  => 'text'
    ));

    $wp_customize->add_setting('real_estate_management_topbar_btn_url',array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw'
    ));
    $wp_customize->add_control('real_estate_management_topbar_btn_url',array(
        'label' => esc_html__('Button URL','real-estate-management'),
        'section' => 'real_estate_management_header',
        'setting' => 'real_estate_management_topbar_btn_url',
        'type'  => 'text'
    ));

    // General Settings
     $wp_customize->add_section('real_estate_management_general_settings',array(
        'title' => esc_html__('General Settings','real-estate-management'),
        'description' => esc_html__('General settings of our theme.','real-estate-management'),
        'priority'   => 1,
    ));

    $wp_customize->add_setting('real_estate_management_preloader_hide', array(
        'default' => 0,
        'sanitize_callback' => 'real_estate_management_sanitize_checkbox'
    ));
    $wp_customize->add_control( new WP_Customize_Control($wp_customize,'real_estate_management_preloader_hide',array(
        'label'          => __( 'Show Theme Preloader', 'real-estate-management' ),
        'section'        => 'real_estate_management_general_settings',
        'settings'       => 'real_estate_management_preloader_hide',
        'type'           => 'checkbox',
    )));

    $wp_customize->add_setting('real_estate_management_sticky_header', array(
        'default' => false,
        'sanitize_callback' => 'real_estate_management_sanitize_checkbox'
    ));
    $wp_customize->add_control( new WP_Customize_Control($wp_customize,'real_estate_management_sticky_header',array(
        'label'          => __( 'Show Sticky Header', 'real-estate-management' ),
        'section'        => 'real_estate_management_general_settings',
        'settings'       => 'real_estate_management_sticky_header',
        'type'           => 'checkbox',
    )));

    //Slider
    $wp_customize->add_section('real_estate_management_top_slider',array(
        'title' => esc_html__('Slider Option','real-estate-management')
    ));

    for ( $count = 1; $count <= 3; $count++ ) {
        $wp_customize->add_setting( 'real_estate_management_top_slider_page' . $count, array(
            'default'           => '',
            'sanitize_callback' => 'real_estate_management_sanitize_dropdown_pages'
        ) );
        $wp_customize->add_control( 'real_estate_management_top_slider_page' . $count, array(
            'label'    => __( 'Select Slide Page', 'real-estate-management' ),
            'section'  => 'real_estate_management_top_slider',
            'type'     => 'dropdown-pages'
        ) );
    }

    $wp_customize->add_setting('real_estate_management_phone_text',array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    $wp_customize->add_control('real_estate_management_phone_text',array(
        'label' => esc_html__('Phone Text','real-estate-management'),
        'section' => 'real_estate_management_top_slider',
        'setting' => 'real_estate_management_phone_text',
        'type'  => 'text'
    ));

    $wp_customize->add_setting('real_estate_management_phone',array(
        'default' => '',
        'sanitize_callback' => 'real_estate_management_sanitize_phone_number'
    ));
    $wp_customize->add_control('real_estate_management_phone',array(
        'label' => esc_html__('Phone Number','real-estate-management'),
        'section' => 'real_estate_management_top_slider',
        'setting' => 'real_estate_management_phone',
        'type'  => 'text'
    ));

    //Latest Property
    $wp_customize->add_section('real_estate_management_new_project',array(
        'title' => esc_html__('Latest Properties','real-estate-management'),
        'description' => esc_html__('Here you have to select properties which will display perticular latest properties in the home page.','real-estate-management')
    ));

    $wp_customize->add_setting('real_estate_management_projects_short_title',array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    $wp_customize->add_control('real_estate_management_projects_short_title',array(
        'label' => esc_html__('Short Title','real-estate-management'),
        'section' => 'real_estate_management_new_project',
        'setting' => 'real_estate_management_projects_short_title',
        'type'  => 'text'
    ));

    $wp_customize->add_setting('real_estate_management_projects_title',array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    $wp_customize->add_control('real_estate_management_projects_title',array(
        'label' => esc_html__('Title','real-estate-management'),
        'section' => 'real_estate_management_new_project',
        'setting' => 'real_estate_management_projects_title',
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

    $wp_customize->add_setting('real_estate_management_projects_category',array(
        'default'   => 'select',
        'sanitize_callback' => 'real_estate_management_sanitize_select',
    ));
    $wp_customize->add_control('real_estate_management_projects_category',array(
        'type'    => 'select',
        'choices' => $cat_post,
        'label' => __('Select category to display latest properties','real-estate-management'),
        'section' => 'real_estate_management_new_project',
    ));

    // Footer
    $wp_customize->add_section('real_estate_management_site_footer_section', array(
        'title' => esc_html__('Footer', 'real-estate-management'),
    ));

    $wp_customize->add_setting('real_estate_management_footer_text_setting', array(
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('real_estate_management_footer_text_setting', array(
        'label' => __('Replace the footer text', 'real-estate-management'),
        'section' => 'real_estate_management_site_footer_section',
        'priority' => 1,
        'type' => 'text',
    ));
}
add_action('customize_register', 'real_estate_management_customize_register');

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function real_estate_management_customize_partial_blogname(){
    bloginfo('name');
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function real_estate_management_customize_partial_blogdescription(){
    bloginfo('description');
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function real_estate_management_customize_preview_js(){
    wp_enqueue_script('real-estate-management-customizer', esc_url(get_template_directory_uri()) . '/assets/js/customizer.js', array('customize-preview'), '20151215', true);
}
add_action('customize_preview_init', 'real_estate_management_customize_preview_js');
