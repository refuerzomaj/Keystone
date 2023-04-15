<?php
/**
 * Template Name: Home Template
 */

get_header(); ?>

<main id="skip-content">
  <section id="top-slider">
    <?php $real_estate_management_slide_pages = array();
      for ( $real_estate_management_count = 1; $real_estate_management_count <= 3; $real_estate_management_count++ ) {
        $real_estate_management_mod = intval( get_theme_mod( 'real_estate_management_top_slider_page' . $real_estate_management_count ));
        if ( 'page-none-selected' != $real_estate_management_mod ) {
          $real_estate_management_slide_pages[] = $real_estate_management_mod;
        }
      }
      if( !empty($real_estate_management_slide_pages) ) :
        $real_estate_management_args = array(
          'post_type' => 'page',
          'post__in' => $real_estate_management_slide_pages,
          'orderby' => 'post__in'
        );
        $real_estate_management_query = new WP_Query( $real_estate_management_args );
        if ( $real_estate_management_query->have_posts() ) :
          $real_estate_management_i = 1;
    ?>
    <div class="owl-carousel" role="listbox">
      <?php  while ( $real_estate_management_query->have_posts() ) : $real_estate_management_query->the_post(); ?>
        <div class="slider-box">
          <img src="<?php esc_url(the_post_thumbnail_url('full')); ?>"/>
          <div class="slider-inner-box">
            <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
            <p class="mb-3"><?php echo wp_trim_words( get_the_content(), 30 ); ?></p>
            <div class="row">
              <div class="col-lg-6 col-md-6 align-self-center">
                <div class="slider-box-btn text-center text-md-right">
                  <a href="<?php the_permalink(); ?>"><?php esc_html_e('Contact Us','real-estate-builder'); ?></a>
                </div>
              </div>
              <div class="col-lg-6 col-md-6 phone-text align-self-center">
                <?php if(get_theme_mod('real_estate_management_phone_text') != '' || get_theme_mod('real_estate_management_phone') != ''){ ?>
                  <div class="row">
                    <div class="col-lg-2 col-md-2 col-sm-2 align-self-center">
                      <i class="fas fa-phone"></i>
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 align-self-center pl-0">
                      <h6 class="mb-0"><?php echo esc_html(get_theme_mod('real_estate_management_phone_text','')); ?></h6>
                      <p class="mb-0"><?php echo esc_html(get_theme_mod('real_estate_management_phone','')); ?></p>
                    </div>
                  </div>
                <?php }?>
              </div>
            </div>
          </div>
        </div>
      <?php $real_estate_management_i++; endwhile;
      wp_reset_postdata();?>
    </div>
    <?php else : ?>
      <div class="no-postfound"></div>
    <?php endif;
    endif;?>
  </section>

  <section class="latest-project py-5">
    <div class="container">
      <?php if(get_theme_mod('real_estate_management_projects_short_title') != ''){ ?>
        <h6 class="text-center"><?php echo esc_html(get_theme_mod('real_estate_management_projects_short_title','')); ?></h6>
      <?php }?>
      <?php if(get_theme_mod('real_estate_management_projects_title') != ''){ ?>
        <h3 class="mb-4 text-center"><?php echo esc_html(get_theme_mod('real_estate_management_projects_title','')); ?></h3>
      <?php }?>
      <div class="row">
        <?php
          $real_estate_management_projects_cat = get_theme_mod('real_estate_management_projects_category','');          
          if($real_estate_management_projects_cat){
            $real_estate_management_page_query5 = new WP_Query(array( 'category_name' => esc_html($real_estate_management_projects_cat,'real-estate-builder')));
            $real_estate_management_i=1;
            while( $real_estate_management_page_query5->have_posts() ) : $real_estate_management_page_query5->the_post(); ?>
              <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="box mb-5">
                  <?php if ( has_post_thumbnail() ) { ?>
                    <div class="box-image">
                      <?php the_post_thumbnail(); ?>
                    </div>
                  <?php }?>
                  <div class="box-content">
                    <div class="flat-meta my-2">
                      <?php if( get_post_meta($post->ID, 'real_estate_management_flat_bedroom', true) ) {?>
                        <span class="mr-2"><i class="fas fa-bed mr-2"></i><?php esc_html_e('Beds: ','real-estate-builder'); ?><?php echo esc_html(get_post_meta($post->ID,'real_estate_management_flat_bedroom',true)); ?></span>
                      <?php }?>
                      <?php if( get_post_meta($post->ID, 'real_estate_management_flat_bathroom', true) ) {?>
                        <span class="mr-2"><i class="fas fa-bath mr-2"></i><?php esc_html_e('Bath: ','real-estate-builder'); ?><?php echo esc_html(get_post_meta($post->ID,'real_estate_management_flat_bathroom',true)); ?></span>
                      <?php }?>
                      <?php if( get_post_meta($post->ID, 'real_estate_management_flat_sqrfit', true) ) {?>
                        <span><i class="fas fa-vector-square mr-2"></i><?php echo esc_html(get_post_meta($post->ID,'real_estate_management_flat_sqrfit',true)); ?></span>
                      <?php }?>
                    </div>
                    <h4 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                    <?php if( get_post_meta($post->ID, 'real_estate_management_flat_location', true) ) {?>
                      <p class="flat-locatn"><i class="fas fa-map-marker-alt mr-2"></i><?php echo esc_html(get_post_meta($post->ID,'real_estate_management_flat_location',true)); ?></p>
                    <?php }?>
                    <?php if( get_post_meta($post->ID, 'real_estate_management_flat_rent', true) ) {?>
                      <span class="flat-rent"><?php echo esc_html(get_post_meta($post->ID,'real_estate_management_flat_rent',true)); ?></span>
                    <?php }?>
                  </div>
                </div>
              </div>
            <?php $real_estate_management_i++; endwhile;
          wp_reset_postdata();
        } ?>
      </div>
    </div>
  </section>

  <section class="latest-services py-5">
    <div class="container">
      <?php if(get_theme_mod('real_estate_builder_projects_short_title') != ''){ ?>
        <h6 class="text-center"><?php echo esc_html(get_theme_mod('real_estate_builder_projects_short_title','')); ?></h6>
      <?php }?>
      <?php if(get_theme_mod('real_estate_builder_projects_title') != ''){ ?>
        <h3 class="mb-4 text-center"><?php echo esc_html(get_theme_mod('real_estate_builder_projects_title','')); ?></h3>
      <?php }?>
      <div class="row">
        <?php
          $real_estate_builder_services_cat = get_theme_mod('real_estate_builder_services_category','');
          $real_estate_builder_services_per_page = get_theme_mod('real_estate_builder_services_per_page',3);          
          if($real_estate_builder_services_cat){
            $real_estate_builder_page_query5 = new WP_Query(array( 'category_name' => esc_html($real_estate_builder_services_cat,'real-estate-builder'),'post_per_page' => esc_attr( $real_estate_builder_services_per_page )));
            $real_estate_builder_i=1;
            while( $real_estate_builder_page_query5->have_posts() ) : $real_estate_builder_page_query5->the_post(); ?>
              <div class="col-lg-4 col-md-4 col-sm-6">
                <div class="box mb-4 text-center">
                  <i class="mb-4 <?php echo esc_attr(get_theme_mod('real_estate_builder_services_icon'.$real_estate_builder_i,'fas fa-home')); ?>"></i>
                  <h4 class="mb-3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                  <hr>
                  <p class="mb-4 pb-2"><?php echo esc_html( wp_trim_words( get_the_content(), 15 )); ?><p>
                  <a href="<?php the_permalink(); ?>"><?php esc_html_e('Read More','real-estate-builder'); ?></a>
                </div>
              </div>
            <?php $real_estate_builder_i++; endwhile;
          wp_reset_postdata();
        } ?>
      </div>
    </div>
  </section>

  <section id="page-content">
    <div class="container">
      <div class="py-5">
        <?php
          if ( have_posts() ) :
            while ( have_posts() ) : the_post();
              the_content();
            endwhile;
          endif;
        ?>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>