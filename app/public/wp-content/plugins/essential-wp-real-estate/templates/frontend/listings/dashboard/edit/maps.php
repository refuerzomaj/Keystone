<?php
if (array_key_exists("value", $args['field_data'])) {
    $value = $args['field_data']['value'];
} else {
    $value = "";
}
?>

<div class="column col-md-12" data-group_field_id="<?php echo esc_attr($args['field_data']['id']); ?>">
    <input id="cl-meta-text-input-lat" type="hidden" value="<?php echo esc_attr($value['wperesds_map_address_lat']); ?>" name="<?php echo esc_attr(str_replace($args['field_data']['id'], $args['field_data']['id'] . '_lat', $args['id'])); ?>">
</div>
<div class="column col-md-12" data-group_field_id="<?php echo esc_attr($args['field_data']['id']); ?>">
    <input id="cl-meta-text-input-lon" type="hidden" value="<?php echo esc_attr($value['wperesds_map_address_lon']); ?>" name="<?php echo esc_attr(str_replace($args['field_data']['id'], $args['field_data']['id'] . '_lon', $args['id'])); ?>">
</div>
<div class="column col-12">
    <div id="map-ed" style="height:500px;"></div>
</div>