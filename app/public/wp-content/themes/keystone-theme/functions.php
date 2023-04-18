<?php
/*This file load  css and javascripts files*/

/*ESE Scripts*/
function my_enqueue_scripts() {
    // Enqueue Essential Real Estate script
    wp_enqueue_script( 'essential-real-estate', plugins_url( '/essential-real-estate/js/es.js' ), array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'my_enqueue_scripts' );

/*Style CSS*/
function add_style_css(){
    wp_enqueue_style( 'keystone-stylesheet' , get_stylesheet_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'add_style_css' );
