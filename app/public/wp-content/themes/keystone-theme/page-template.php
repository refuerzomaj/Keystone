<?php
/**
 * Template Name: Custom Template
 */
get_header();//call the header
/*loops until have posts*/
while(have_posts()){
    the_post(); 
    get_template_part( 'template-parts/content', get_post_type() );
}
get_footer();//call the footer
?>