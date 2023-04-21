<?php
/*This file load  css and javascripts files*/

/*ESE Scripts*/
function my_enqueue_scripts() {
    // Enqueue Essential Real Estate script
    wp_enqueue_script( 'essential-real-estate', plugins_url( '/essential-real-estate/js/es.js' ), array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'my_enqueue_scripts' );

/*Homepage CSS*/
function add_style_css(){
    wp_enqueue_style( 'keystone-stylesheet' , get_stylesheet_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'add_style_css' );

/*Blog Page CSS*/
function add_blogpage_style_css(){
    wp_enqueue_style( 'keystone-stylesheet-2' , get_stylesheet_directory_uri() . '/css/blogpage-style.css' );
}
add_action( 'wp_enqueue_scripts', 'add_blogpage_style_css' );

/*BMV Properties CSS*/
function add_bmvproperties_style_css(){
    wp_enqueue_style( 'keystone-stylesheet-3' , get_stylesheet_directory_uri() . '/css/bmv-properties-style.css' );
}
add_action( 'wp_enqueue_scripts', 'add_bmvproperties_style_css' );

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