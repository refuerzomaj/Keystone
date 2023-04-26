<?php
/**
 * The template for displaying single properties
 *
 * @package Essential Real Estate
 * @subpackage Template Parts
 * @since 1.0.0
 */

get_header(); 
?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main">

            <?php 
                while ( have_posts() ) : the_post(); 
                get_template_part( 'template-parts/property/content', 'single' );
                $custom_fields = get_post_custom();
                $thumbnail_url1 = get_field( 'property_image_1' );
                $thumbnail_url2 = get_field( 'property_image_2' );
                $thumbnail_url3 = get_field( 'property_image_3' );
                $thumbnail_url4 = get_field( 'property_image_4' );
                $thumbnail_url5 = get_field( 'property_image_5' );
                $thumbnail_url6 = get_field( 'property_image_6' );
                $thumbnail_url7 = get_field( 'property_image_7' );
                $thumbnail_url8 = get_field( 'property_image_8' );
                $thumbnail_url9 = get_field( 'property_image_9' );
                $thumbnail_url10 = get_field( 'property_image_10' );
                $property_location = get_post_meta( get_the_ID(), 'property_address', true);
                ?>
                <div class="single-property">
                    <div class="breadcrumbs-section">
                        <div class="breadcrumbs-container">
                            <p>
                            <a href="http://keystone.local/homepage" style="padding:0;margin:0; text-decoration:none; color:white;">Home</a> > <a href="http://keystone.local/bmv" style="padding:0;margin:0; text-decoration:none; color:white;">BMV Properties</a> > <?php the_title();?>
                            </p>
                        </div>
                    </div>
                    <div class="property-section">
                        <div class="property-box">
                            <div class="title-box">
                                <h1><?php the_title();?></h1>
                            </div>
                            <div class="images-box">
                                <div class="main-image">
                                    <img src="<?php echo $thumbnail_url1['url']; ?>" alt="<?php echo $thumbnail_url3['alt']; ?>" />
                                </div>
                                <div class="other-image">
                                    <div class="thumbnails">
                                        <div class="thumb1">
                                            <img src="<?php echo $thumbnail_url2['url']; ?>" alt="<?php echo $thumbnail_url2['alt']; ?>" />
                                        </div>
                                        <div class="thumb2">
                                            <img src="<?php echo $thumbnail_url3['url']; ?>" alt="<?php echo $thumbnail_url3['alt']; ?>" />
                                        </div>
                                        <div class="thumb3">
                                            <img src="<?php echo $thumbnail_url4['url']; ?>" alt="<?php echo $thumbnail_url4['alt']; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="location-box">
                                    <div class="map">
                                </div>
                            </div>
                            <div class="address-box">
                                <p><?php echo $property_location;?></p>
                            </div>
                            <div class="info-box">
                                <div class="left"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?    // If comments are open or we have at least one comment, load up the comment template.
                    /*if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;*/
                endwhile;wp_reset_postdata(); // End of the loop. 
                ?>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php get_footer(); ?>
