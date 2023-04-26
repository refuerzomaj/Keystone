<?php
/*This file load  css and javascripts files*/

/*ESE Scripts*/
function my_enqueue_scripts() {
    // Enqueue Essential Real Estate script
    wp_enqueue_script( 'essential-real-estate', plugins_url( '/essential-real-estate/js/es.js' ), array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'my_enqueue_scripts' );


//This Function is for CSS
function add_style_css(){
    wp_enqueue_style( 'keystone-stylesheet' , get_stylesheet_directory_uri() . '/style.css' );/*Homepage CSS*/
    wp_enqueue_style( 'keystone-stylesheet-2' , get_stylesheet_directory_uri() . '/css/blogpage-style.css' );/*Blog Page CSS*/
    wp_enqueue_style( 'keystone-stylesheet-3' , get_stylesheet_directory_uri() . '/css/bmv-properties-style.css' );/*BMV Properties CSS*/
    wp_enqueue_style( 'keystone-stylesheet-4' , get_stylesheet_directory_uri() . '/css/single-property-style.css' );/*Single Property CSS */
}
add_action( 'wp_enqueue_scripts', 'add_style_css' );

/*Preloader function*/
function add_keystone_preloader(){
    if(is_page()){
        wp_enqueue_style('preloader-style', get_stylesheet_directory_uri() . '/CSS/preloader.css');
        wp_enqueue_script('preloader-script', get_stylesheet_directory_uri() . '/JS/preloader.js', array('jquery'), '', true);
    }
}
add_action('wp_enqueue_scripts','add_keystone_preloader');

/*Filter Form JQuery Script*/
function add_filter_script(){
    wp_enqueue_script('filter-form-script', get_stylesheet_directory_uri() . '/JS/filter-form.js', array('jquery'), '', true);
}
add_action( 'wp_enqueue_scripts', 'add_filter_script' );

/* Display all meta query data */
function display_meta_query_values( $query ) {
    if( $query->is_main_query() && is_archive() ) {
        $meta_query = $query->get('meta_query');
        if( !empty($meta_query) ) {
            foreach( $meta_query as $meta ) {
                $key = $meta['key'];
                $value = $meta['value'];
                $compare = $meta['compare'];
                echo "Key: $key, Value: $value, Compare: $compare <br>";
            }
        }
    }
}
add_action('pre_get_posts', 'display_meta_query_values');
