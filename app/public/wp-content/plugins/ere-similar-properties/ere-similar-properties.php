<?php
/**
 * Plugin Name: ERE Similar Properties - Essential Real Estate Add-On
 * Plugin URI: https://wordpress.org/plugins/ere-similar-properties
 * Description: ERE Similar Properties displays a list of properties that are similar or related to the current property listing.
 * Version: 1.2
 * Author: G5Theme
 * Author URI: http://themeforest.net/user/g5theme
 * Text Domain: ere-similar-properties
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
function ere_similar_properties_load_textdomain()
{
    $mofile = plugin_dir_path(__FILE__) . 'languages/' . 'ere-similar-properties-' . get_locale() .'.mo';

    if (file_exists($mofile)) {
        load_textdomain('ere-similar-properties', $mofile );
    }
}

add_action('plugins_loaded', 'ere_similar_properties_load_textdomain');
/**
 * @return array
 */
function ere_similar_properties_register_option()
{
    return array(
        array(
            'id' => 'ere_similar_properties_option',
            'title' => esc_html__('Similar Properties', 'ere-similar-properties'),
            'type' => 'group',
            'fields' => array(
                array(
                    'id' => 'similar_properties_heading_title',
                    'type' => 'text',
                    'title' => esc_html__('Heading Title', 'ere-similar-properties'),
                    'default' => esc_html__('Similar Properties', 'ere-similar-properties'),
                ),
                array(
                    'id' => 'similar_properties_type',
                    'type' => 'checkbox_list',
                    'title' => esc_html__('Similar Type', 'ere-similar-properties'),
                    'subtitle' => esc_html__('Select type for similar properties', 'ere-similar-properties'),
                    'options' => array(
                        'property-status' => esc_html__('Status', 'ere-similar-properties'),
                        'property-type' => esc_html__('Type', 'ere-similar-properties'),
                        'property-city' => esc_html__('City / Town', 'ere-similar-properties'),
                        'property-neighborhood' => esc_html__('Neighborhood', 'ere-similar-properties'),
                        'property-label' => esc_html__('Label', 'ere-similar-properties'),
                    ),
                    'value_inline' => false,
                    'default' => array(
                        'property-status',
                        'property-type'
                    ),
                ),
                array(
                    'id' => 'similar_properties_layout_style',
                    'type' => 'button_set',
                    'title' => esc_html__('Layout Style', 'ere-similar-properties'),
                    'default' => 'property-list',
                    'options' => array(
                        'property-grid' => esc_html__('Grid', 'ere-similar-properties'),
                        'property-list' => esc_html__('List', 'ere-similar-properties')
                    )
                ),
                array(
                    'id' => 'similar_properties_items_amount',
                    'type' => 'text',
                    'title' => esc_html__('Items Amount', 'ere-similar-properties'),
                    'default' => 4,
                    'pattern' => '[0-9]*',
                ),
            )
        )
    );
}

add_filter('ere_register_option_property_page_bottom', 'ere_similar_properties_register_option', 10);


function ere_similar_properties()
{
    $similar_types = ere_get_option('similar_properties_type', array('property-status','property-type'));
    $similar_items_amount = ere_get_option('similar_properties_items_amount', 4);
    $tax_query = Array();
    $term_ids = Array();
    foreach ($similar_types as $similar_type) {
        $terms = get_the_terms(get_the_ID(), $similar_type);
        if ( !empty( $terms ) ){
            $term_ids = wp_list_pluck($terms, 'term_id');
        }
        $tax_query[] = array(
            'taxonomy' => $similar_type,
            'field' => 'id',
            'terms' => $term_ids,
            'operator' => 'IN'
        );
    }
    $tax_count = count( $tax_query );
    if ($tax_count > 1){
        $tax_query['relation'] = 'AND';
    }
    $args = array(
        'posts_per_page' => $similar_items_amount,
        'post_type' => 'property',
        'post_status' => 'publish',
        'orderby' => 'rand',
        'tax_query' => $tax_query,
        'post__not_in' => array(get_the_ID())
    );
    $data = new WP_Query(apply_filters('ere_similar_properties_query_args',$args) );
    wp_print_styles( ERE_PLUGIN_PREFIX . 'property');
    $custom_property_layout_style =ere_get_option( 'similar_properties_layout_style', 'property-list' );
    $custom_property_image_size = ere_get_option( 'archive_property_image_size', '330x180' );
    $wrapper_classes = array(
        'ere-property clearfix',
        $custom_property_layout_style
    );
    $property_item_class = array();
    $property_item_class[] = 'ere-item-wrap mg-bottom-30';
    if ($custom_property_layout_style == 'property-list') {
        $wrapper_classes[] = 'list-1-column';
        $wrapper_classes[]= 'col-gap-0';
    }
    else{
        $wrapper_classes[] = 'columns-2 columns-md-2 columns-sm-2 columns-xs-1 columns-mb-1';
        $wrapper_classes[]= 'col-gap-30';
    }
    ?>
    <div class="ere-similar-properties">
        <?php
        $similar_properties_heading_title =ere_get_option( 'similar_properties_heading_title', esc_html__('Similar Properties', 'ere-similar-properties') );
        if(!empty($similar_properties_heading_title)):?>
        <div class="ere-heading-style2 mg-bottom-35 text-left">
            <h2><?php echo esc_html($similar_properties_heading_title); ?></h2>
        </div>
        <?php endif;?>
        <div class="<?php echo join(' ', $wrapper_classes) ?>">
            <?php if ($data->have_posts()) :
                while ($data->have_posts()): $data->the_post(); ?>
                    <?php  ere_get_template('loop/property.php', array('property_item_class' => $property_item_class, 'custom_property_image_size' => $custom_property_image_size)); ?>
                <?php endwhile;
            endif;
            wp_reset_postdata(); ?>
        </div>
    </div>
    <?php
}
add_action('ere_single_property_after_summary', 'ere_similar_properties',96);