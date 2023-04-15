<div class="col-md-12" data-group_field_id="<?php echo esc_attr($args['field_data']['id']); ?>">
    <input id="cl-meta-text-input-lat" type="hidden" value="0" name="<?php echo esc_attr(str_replace($args['field_data']['id'], $args['field_data']['id'] . '_lat', $args['id'])); ?>">
</div>
<div class="col-md-12" data-group_field_id="<?php echo esc_attr($args['field_data']['id']); ?>">
    <input id="cl-meta-text-input-lon" type="hidden" value="0" name="<?php echo esc_attr(str_replace($args['field_data']['id'], $args['field_data']['id'] . '_lon', $args['id'])); ?>">
</div>
<div class="col-12">
    <div id="map-ed" style="height:500px;"></div>
</div>