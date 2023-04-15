<?php
$group_columns = $cl_meta_group_field['columns'] ?? '12';
$placeholder   = $cl_meta_group_field['placeholder'] ?? '';
$group_desc    = $cl_meta_group_field['desc'] ?? '';

// -- Adding std Value -- //
if ( isset( $cl_meta_group_field['std'] ) && $meta_val == false ) {
	$group_meta_val = $cl_meta_group_field['std'];
} else {
	if ( $clone == true ) {
		$group_meta_val = $meta_val[ $key ][ $cl_meta_group_field['id'] ] ?? '';
	} else {
		$group_meta_val = $meta_val[ $cl_meta_group_field['id'] ] ?? '';
	}
}
// echo "<pre>", print_r($meta_val);

$lat_field_id  = $cl_meta_group_field['id'] . '_lat';
$lon_field_id  = $cl_meta_group_field['id'] . '_lon';
$lat_field_val = $meta_val[ $lat_field_id ] ?? '';
$lon_field_val = $meta_val[ $lon_field_id ] ?? '';
?>
<div class="column col-<?php echo esc_attr( $group_columns ); ?>" data-group_field_id="<?php echo esc_attr( $cl_meta_group_field['id'] ); ?>">
	<label for="cl-meta-text-input-lat" class="cl-meta-text-input-label"><?php echo isset( $cl_meta_group_field['name_lat'] ) ? esc_attr( $cl_meta_group_field['name_lat'] ) : ''; ?></label>
	<input id="cl-meta-text-input-lat" placeholder="<?php echo esc_attr( $placeholder ); ?>" class="cl-meta-text-input" type="text" name="<?php echo esc_attr( $cl_meta_field['id'] ) . '[' . esc_attr( $lat_field_id ) . ']'; ?>" value="<?php echo esc_attr( $lat_field_val ); ?>">

	<?php
	if ( ! empty( $group_desc ) ) {
		?>
		<div class="desc"><?php echo esc_html( $group_desc ); ?></div>
	<?php } ?>
</div>

<div class="column col-<?php echo esc_attr( $group_columns ); ?>" data-group_field_id="<?php echo esc_attr( $cl_meta_group_field['id'] ); ?>">

	<label for="cl-meta-text-input-lon" class="cl-meta-text-input-label"><?php echo isset( $cl_meta_group_field['name_lon'] ) ? esc_attr( $cl_meta_group_field['name_lon'] ) : ''; ?></label>
	<input id="cl-meta-text-input-lon" placeholder="<?php echo esc_attr( $placeholder ); ?>" class="cl-meta-text-input" type="text" name="<?php echo esc_attr( $cl_meta_field['id'] ) . '[' . esc_attr( $lon_field_id ) . ']'; ?>" value="<?php echo esc_attr( $lon_field_val ); ?>">
	<?php
	if ( ! empty( $group_desc ) ) {
		?>
		<div class="desc"><?php echo esc_html( $group_desc ); ?></div>
	<?php } ?>
</div>
<div class="column col-12">
	<div id="map-ed" style="height:500px;"></div>
</div>
