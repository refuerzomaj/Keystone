<?php
/**
 * Plugin Name: ERE Recently Viewed - Essential Real Estate Add-On
 * Plugin URI: https://wordpress.org/plugins/ere-recently-viewed
 * Description: ERE Recently Viewed plugin shows properties viewed by a visitor as a responsive sidebar widget or in post/page using shortcode
 * Version: 1.3
 * Author: G5Theme
 * Author URI: http://themeforest.net/user/g5theme
 * Text Domain: ere-recently-viewed
 * Domain Path: /languages/
 * License: GPLv2 or later
 */
/*
Copyright 2018 by G5Theme

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

class ERE_Recently_Viewed extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'ere_widget_recently_viewed',
            'ERE Recently Viewed Properties',
            array('description' => esc_html__('Display recent viewed properties by a visitor as a responsive sidebar widget or in page/post using shortcode', 'ere-recently-viewed')) // Args
        );
    }

    function form($instance)
    {
        $widget_id = str_replace('ere_widget_recently_viewed-', '', $this->id);
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $totalproperties = isset($instance['totalproperties']) ? absint($instance['totalproperties']) : 5;
        $width = isset($instance['width']) ? esc_attr($instance['width']) : '';
        $height = isset($instance['height']) ? esc_attr($instance['height']) : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Title', 'ere-recently-viewed'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
        </p>
        <p>
            <label
                for="<?php echo $this->get_field_id('totalproperties'); ?>"><?php esc_html_e('Number of properties:', 'ere-recently-viewed'); ?></label>
            <input id="<?php echo $this->get_field_id('totalproperties'); ?>"
                   name="<?php echo $this->get_field_name('totalproperties'); ?>" type="text" size="5"
                   value="<?php echo $totalproperties; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php esc_html_e('Thumbnail Width: ','ere-recently-viewed'); ?></label>
            <input size="5" id="<?php echo $this->get_field_id('width'); ?>"
                   name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>"/>
            px<?php esc_html_e(' (Default: 370)','ere-recently-viewed'); ?>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>"><?php esc_html_e('Thumbnail Height: ','ere-recently-viewed'); ?></label>
            <input size="5" id="<?php echo $this->get_field_id('height'); ?>"
                   name="<?php echo $this->get_field_name('height'); ?>" type="text"
                   value="<?php echo $height; ?>"/> px<?php esc_html_e(' (Default: 180)','ere-recently-viewed'); ?>
        </p>
        <?php if ($widget_id != "__i__") { ?>
        <p>
            <label class="ere-recently-viewed-shortocde-title"><?php esc_html_e('Shortcode: ','ere-recently-viewed'); ?></label>
            <label class="ere-recently-viewed-shortocde-name">[ere_recently_viewed widget_id="<?php echo $widget_id; ?>"]</label>
        </p>
    <?php }
    }

    function update($new_instance, $old_instance)
    {
        $old_instance['title'] = $new_instance['title'];
        $old_instance['totalproperties'] = isset($new_instance['totalproperties']) ? (int)$new_instance['totalproperties'] : '';
        $old_instance['width'] = isset($new_instance['width']) ? $new_instance['width'] : '';
        $old_instance['height'] = isset($new_instance['height']) ? $new_instance['height'] : '';
        return $old_instance;
    }

    function widget($args, $instance)
    {
        if (class_exists('Essential_Real_Estate')) {
            $widget_id = $args['widget_id'];
            $widget_id = str_replace('ere_widget_recently_viewed-', '', $widget_id);
            $widgetOptions = get_option($this->option_name);
            $instance = $widgetOptions[$widget_id];
            $title = (!empty($instance['title'])) ? $instance['title'] : __('Recent Visited Posts');
            $title = apply_filters('widget_title', $title, $instance, $this->id_base);
            $number = (!empty($instance['totalproperties'])) ? absint($instance['totalproperties']) : 5;
            $width = empty($instance['width']) ? '370' : apply_filters('ere_widget_recently_viewed_image_width', $instance['width']);
            $height = empty($instance['height']) ? '180' : apply_filters('ere_widget_recently_viewed_image_height', $instance['height']);
            extract($args, EXTR_SKIP);
            wp_enqueue_style(ERE_PLUGIN_PREFIX . 'property');
            if (isset($_COOKIE['ere_recently_viewed']) && $_COOKIE['ere_recently_viewed'] != '') {
                $viewed_list = unserialize($_COOKIE['ere_recently_viewed']);
                $viewed_list = array_diff($viewed_list, array(get_the_ID()));
                if (count($viewed_list) > 0){

                    echo $before_widget;
                    echo $before_title . $title . $after_title;
                    echo '<div class="ere-property ere-recently-viewed-properties">';
                    $current_property_id = get_the_ID();
                    $no_image_src = ERE_PLUGIN_URL . 'public/assets/images/no-image.jpg';
                    $default_image = ere_get_option('default_property_image', '');
                    $count = 0;
                    global $post;
                    foreach ($viewed_list as $property_id) {
                        if ($count < $number){
                            global $wpdb;
                            $post_exists = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = '" . $property_id . "'", 'ARRAY_A');
                            $post = get_post($property_id);

                            setup_postdata($post);
                            $attach_id = get_post_thumbnail_id($property_id);
                            $image_src = ere_image_resize_id($attach_id, $width, $height, true);
                            if ($default_image != '') {
                                if (is_array($default_image) && $default_image['url'] != '') {
                                    $resize = ere_image_resize_url($default_image['url'], $width, $height, true);
                                    if ($resize != null && is_array($resize)) {
                                        $no_image_src = $resize['url'];
                                    }
                                }
                            }
                            $property_link = get_the_permalink();
                            $property_id = get_the_ID();
                            $property_label = get_the_terms($property_id, 'property-label');
                            $price = get_post_meta($property_id, ERE_METABOX_PREFIX . 'property_price', true);
                            $price_short = get_post_meta($property_id, ERE_METABOX_PREFIX . 'property_price_short', true);
                            $price_unit = get_post_meta($property_id, ERE_METABOX_PREFIX . 'property_price_unit', true);
                            $price_prefix = get_post_meta($property_id, ERE_METABOX_PREFIX . 'property_price_prefix', true);
                            $price_postfix = get_post_meta($property_id, ERE_METABOX_PREFIX . 'property_price_postfix', true);
                            $property_address = get_post_meta($property_id, ERE_METABOX_PREFIX . 'property_address', true);
                            if ($post_exists && $post && $post->ID != $current_property_id && $post->post_type == 'property') {
                                $count++;
                                ?>
                                <div class="property-item">
                                    <div class="property-inner">
                                        <div class="property-image">
                                            <img width="<?php echo esc_attr($width) ?>" height="<?php echo esc_attr($height) ?>"
                                                 src="<?php echo esc_url($image_src) ?>"
                                                 onerror="this.src = '<?php echo esc_url($no_image_src) ?>';"
                                                 alt="<?php the_title(); ?>"
                                                 title="<?php the_title(); ?>">

                                            <div class="property-action block-center">
                                                <div class="block-center-inner">
                                                    <?php
                                                    /**
                                                     * ere_property_action hook.
                                                     *
                                                     * @hooked property_social_share - 5
                                                     * @hooked property_favorite - 10
                                                     * @hooked property_compare - 15
                                                     */
                                                    do_action('ere_property_action'); ?>
                                                </div>
                                                <a class="property-link" href="<?php echo esc_url($property_link); ?>"
                                                   title="<?php the_title(); ?>"></a>
                                            </div>
                                            <?php if ($property_label): ?>
                                                <div class="property-label">
                                                    <?php foreach ($property_label as $label_item): ?>
                                                        <p class="label-item">
                                                            <span><?php echo esc_attr($label_item->name) ?></span>
                                                        </p>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="property-item-content">
                                            <h4 class="property-title fs-18"><a href="<?php echo esc_url($property_link); ?>"
                                                                                title="<?php the_title(); ?>"><?php the_title() ?></a>
                                            </h4>
                                            <?php if (!empty($price)): ?>
                                                <div class="property-price">
                                    <span>
                                        <?php if (!empty($price_prefix)) {
                                            echo '<span class="property-price-prefix fs-12 accent-color">' . $price_prefix . ' </span>';
                                        } ?>
                                        <?php echo ere_get_format_money($price_short, $price_unit) ?>
                                        <?php if (!empty($price_postfix)) {
                                            echo '<span class="property-price-postfix fs-12 accent-color"> / ' . $price_postfix . '</span>';
                                        } ?>
                                    </span>
                                                </div>
                                            <?php elseif (ere_get_option('empty_price_text', '') != ''): ?>
                                                <div class="property-price">
                                                    <span><?php echo ere_get_option('empty_price_text', '') ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($property_address)):
                                                $property_location = get_post_meta(get_the_ID(), ERE_METABOX_PREFIX . 'property_location', true);
                                                if ($property_location) {
                                                    $google_map_address_url = "http://maps.google.com/?q=" . $property_location['address'];
                                                } else {
                                                    $google_map_address_url = "http://maps.google.com/?q=" . $property_address;
                                                }
                                                ?>
                                                <div class="property-location"
                                                     title="<?php echo esc_attr($property_address) ?>">
                                                    <i class="fa fa-map-marker accent-color"></i>
                                                    <a target="_blank"
                                                       href="<?php echo esc_url($google_map_address_url); ?>"><span><?php echo esc_html($property_address) ?></span></a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                    echo '</div>' . $after_widget;
                }
            }
        }
    }
}

function ere_recently_viewed_register_widget()
{
    return register_widget("ERE_Recently_Viewed");
}

add_action('widgets_init', 'ere_recently_viewed_register_widget');

function ere_recently_viewed_stylesheet()
{
    wp_enqueue_style('ere_recently_viewed_stylesheet', plugins_url('css/style.min.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'ere_recently_viewed_stylesheet');

function ere_recently_viewed_setcookie()
{
    if (is_singular('property')) {
        if (isset($_COOKIE['ere_recently_viewed']) && $_COOKIE['ere_recently_viewed'] != '') {
            $property_ids = unserialize($_COOKIE['ere_recently_viewed']);
            if (!is_array($property_ids)) {
                $property_ids = array(get_the_ID());
            } else {
                $property_ids = array_diff($property_ids, array(get_the_ID()));
                array_unshift($property_ids, get_the_ID());
            }
        } else {
            $property_ids = array(get_the_ID());
        }
        setcookie('ere_recently_viewed', serialize($property_ids), time() + (DAY_IN_SECONDS * 31), '/');
    }
}

add_action('template_redirect', 'ere_recently_viewed_setcookie');

function ere_recently_viewed_add_shortcode($atts)
{
    $args = array(
        'widget_id' => $atts['widget_id']
    );
    ob_start();
    the_widget('ERE_Recently_Viewed', '', $args);
    return ob_get_clean();
}

add_shortcode('ere_recently_viewed', 'ere_recently_viewed_add_shortcode');

function ere_recently_viewed_load_textdomain()
{
    $mofile = plugin_dir_path(__FILE__) . 'languages/' . 'ere-recently-viewed-' . get_locale() .'.mo';

    if (file_exists($mofile)) {
        load_textdomain('ere-recently-viewed', $mofile );
    }
}

add_action('plugins_loaded', 'ere_recently_viewed_load_textdomain');