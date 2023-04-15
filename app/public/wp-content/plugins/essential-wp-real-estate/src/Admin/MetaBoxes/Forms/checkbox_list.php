<?php
$columns   = $cl_meta_field['columns'] ?? '12';
$post_meta = array_filter( get_post_meta( $post->ID, $cl_meta_field['id'] ) );
$options   = $cl_meta_field['options'] ?? '';
$desc      = $cl_meta_field['desc'] ?? '';

// -- Adding std Value -- //
if ( isset( $cl_meta_field['std'] ) && empty( $post_meta ) ) {
	$meta_val = $cl_meta_field['std'];
} else {
	$meta_val = get_post_meta( $post->ID, $cl_meta_field['id'], true );
}
?>
<!-- Checkbox List Field -->
<div class="column col-<?php echo esc_html( $columns ); ?>">
	<label for="cl-meta-checkbox-val"><?php echo esc_html( $cl_meta_field['name'] ); ?></label>
	<?php
	foreach ( $options as $key => $option ) {
		echo '<div class="cl_meta_check_list">';
		if ( is_array( $meta_val ) && in_array( $key, $meta_val ) ) {
			$checked = 'checked';
		} else {
			$checked = null;
		}
		echo '<input ' . esc_attr( $checked ) . ' type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $cl_meta_field['id'] ) . '[]" value="' . esc_attr( $key ) . '"><label for="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</label></div>';
	}
	if ( ! empty( $desc ) ) {
		?>
		<div class="desc"><?php echo esc_html( $desc ); ?></div>
	<?php } ?>
</div>
