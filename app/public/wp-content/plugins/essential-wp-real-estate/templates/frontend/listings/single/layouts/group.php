<?php
if (!defined('ABSPATH')) exit;
$provider   = WPERECCP()->front->listing_provider;
$value      = $provider->get_meta_data($args['id'], get_the_ID());
$field_info = [];

$default_group_arr = [
    $provider->prefix . 'amenities',
    $provider->prefix . 'maps_fields',
    $provider->prefix . 'floor',
    $provider->prefix . 'features',
    $provider->prefix . 'video',
];

if (in_array($args['id'], $default_group_arr)) {
    switch ($args['id']) {
        case $provider->prefix . 'amenities':
            cl_get_template("single/blocks/amenities.php", $args);
            break;
        case $provider->prefix . 'maps_fields':
            cl_get_template("single/blocks/maps.php", $args);
            break;
        case $provider->prefix . 'floor':
            cl_get_template("single/blocks/floor.php", $args);
            break;
        case $provider->prefix . 'features':
            cl_get_template("single/blocks/features.php", $args);
            break;
        case $provider->prefix . 'video':
            cl_get_template("single/blocks/video.php", $args);
            break;
        default:
            echo "Template not found";
            break;
    }
} else {
    foreach ($args['fields'] as $args_key => $args_value) {
        $field_info[$args_value['id']]['type'] = $args_value['type'];
        $field_info[$args_value['id']]['name'] = $args_value['name'];
    }

    echo '<div class="table-cell">';
    foreach ($value as $first_stage_key => $first_stage_value) {
        if (!is_array($first_stage_value)) {
            $data[$first_stage_key]['name']    = $args['name'];
            $data[$first_stage_key]['data_id'] = $args['id'];
            $data[$first_stage_key]['val_id']  = $first_stage_key;
            $data[$first_stage_key]['name']    = $field_info[$first_stage_key]['name'];
            $data[$first_stage_key]['type']    = $field_info[$first_stage_key]['type'];
            cl_get_template("single/layouts/{$field_info[$first_stage_key]['type']}.php", $data[$first_stage_key]);
        } else {
            foreach ($first_stage_value as $second_stage_key => $second_stage_value) {
                $data[$second_stage_key]['name']    = $args['name'];
                $data[$second_stage_key]['data_id'] = $args['id'];
                $data[$second_stage_key]['val_id']  = $first_stage_key;
                $data[$second_stage_key]['name']    = $field_info[$first_stage_key]['name'];
                $data[$second_stage_key]['type']    = $field_info[$first_stage_key]['type'];
                cl_get_template("single/layouts/{$field_info[$first_stage_key]['type']}.php", $data[$second_stage_key]);
            }
        }
    }
    echo '</div>';
}
