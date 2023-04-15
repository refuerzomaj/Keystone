<?php
/**
 * Displays main header
 *
 * @package Real Estate Management
 */
?>

<div class="main-header text-center text-md-left">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-3 align-self-center">
                <div class="navbar-brand text-center text-md-left">
                    <?php if ( has_custom_logo() ) : ?>
                        <div class="site-logo"><?php the_custom_logo(); ?></div>
                    <?php endif; ?>
                    <?php $real_estate_management_blog_info = get_bloginfo( 'name' ); ?>
                        <?php if ( ! empty( $real_estate_management_blog_info ) ) : ?>
                            <?php if ( is_front_page() && is_home() ) : ?>
                              <?php if( get_theme_mod('real_estate_management_logo_title_text',true) != ''){ ?>
                                <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
                              <?php } ?>
                            <?php else : ?>
                              <?php if( get_theme_mod('real_estate_management_logo_title_text',true) != ''){ ?>
                                <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
                              <?php } ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php
                            $real_estate_management_description = get_bloginfo( 'description', 'display' );
                            if ( $real_estate_management_description || is_customize_preview() ) :
                        ?>
                        <?php if( get_theme_mod('real_estate_management_theme_description',false) != ''){ ?>
                        <p class="site-description"><?php echo esc_html($real_estate_management_description); ?></p>
                      <?php } ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-2 col-md-5 col-sm-5 align-self-center email-box">
                <?php if(get_theme_mod('real_estate_management_email_text') != '' || get_theme_mod('real_estate_management_email') != ''){ ?>
                    <div class="row">
                        <div class="col-lg-2 col-md-2 col-sm-2 align-self-center">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="col-lg-10 col-md-10 col-sm-10 align-self-center">
                            <h6 class="mb-0"><?php echo esc_html(get_theme_mod('real_estate_management_email_text','')); ?></h6>
                            <a href="mailto:<?php echo esc_html(get_theme_mod('real_estate_management_email','')); ?>"><p class="mb-0"><?php echo esc_html(get_theme_mod('real_estate_management_email','')); ?></p></a>
                        </div>
                    </div>
                <?php }?>
            </div>
            <div class="col-lg-5 col-md-1 col-sm-1 col-4 align-self-center">
                <?php get_template_part('template-parts/navigation/nav'); ?>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-3 col-8 align-self-center text-center text-md-right">
                <?php if(get_theme_mod('real_estate_management_topbar_btn_url') != '' || get_theme_mod('real_estate_management_topbar_btn_text') != ''){ ?>
                    <div class="top-btn">
                        <a href="<?php echo esc_url(get_theme_mod('real_estate_management_topbar_btn_url','')); ?>"><?php echo esc_html(get_theme_mod('real_estate_management_topbar_btn_text','')); ?></a>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
</div>
