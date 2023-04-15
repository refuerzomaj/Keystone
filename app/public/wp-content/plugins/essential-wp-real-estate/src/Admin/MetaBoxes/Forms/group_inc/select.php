<?php
$group_columns = $cl_meta_group_field['columns'] ?? '12';
$group_desc    = $cl_meta_group_field['desc'] ?? '';
$group_options = $cl_meta_group_field['options'] ?? '';

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

// -- Clone Variable -- //
$meta_group_clone_val = meta_clone_group_variable_declaration( $clone, $key, $cl_meta_field['id'], $cl_meta_group_field['id'], $group_meta_val );
?>
<div class="column col-<?php echo esc_attr( $group_columns ); ?>" data-group_field_id="<?php echo esc_attr( $cl_meta_group_field['id'] ); ?>">
	<!-- Select Field -->
	<label for="cl-meta-select-val"><?php echo esc_html( $cl_meta_group_field['name'] ); ?></label>
	<select name="<?php echo esc_attr( $meta_group_clone_val['cl_name'] ); ?>">
		<?php
		foreach ( $group_options as $option ) {
			if ( $option == $meta_group_clone_val['group_meta_val'] ) {
				echo '<option selected>' . esc_html( $option ) . '</option>';
			} else {
				echo '<option>' . esc_html( $option ) . '</option>';
			}
		}
		?>
	</select>
	<?php
	if ( ! empty( $group_desc ) ) {
		?>
		<div class="desc"><?php echo esc_html( $group_desc ); ?></div>
	<?php } ?>
</div>
