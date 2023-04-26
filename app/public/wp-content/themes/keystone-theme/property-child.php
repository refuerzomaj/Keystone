<?php
/**
 * Template Name: Property Child Template
 * Template Post Type: post, page
 * Description: A custom template for the child category archive page.
 *
 * @package YourTheme
 */

get_header();

// Start the loop
while ( have_posts() ) : the_post();

    // Display the post content here
    the_title();
    the_content();

endwhile;

// Display pagination
the_posts_pagination( array(
    'prev_text' => __( 'Previous', 'yourtheme' ),
    'next_text' => __( 'Next', 'yourtheme' ),
) );

get_footer();
?>
