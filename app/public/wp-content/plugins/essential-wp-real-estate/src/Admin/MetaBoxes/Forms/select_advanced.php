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

// -- Multiple Value -- //
$mlt_val = $cl_meta_field['multiple'] ?? false;
if ( $mlt_val == true ) {
	$select_type = 'multiple';
} else {
	$select_type = null;
}
?>
<!-- Select Advance Field -->
<div class="column col-<?php echo esc_html( $columns ); ?>">
	<label for="<?php echo esc_html( $cl_meta_field['id'] ); ?>"><?php echo esc_html( $cl_meta_field['name'] ); ?></label>
	<select class="select-adv" name="<?php echo esc_html( $cl_meta_field['id'] ); ?>[]" id="<?php echo esc_html( $cl_meta_field['id'] ); ?>" <?php echo esc_html( $select_type ); ?>>
		<?php
		foreach ( $options as $key => $option ) {
			if ( is_array( $meta_val ) && in_array( $key, $meta_val ) ) {
				$selected = 'selected';
			} else {
				$selected = null;
			}
			echo '<option ' . esc_attr( $selected ) . ' value="' . esc_attr( $key ) . '">' . esc_attr( $option ) . '</option>';
		}
		?>
	</select>
	<?php if ( ! empty( $desc ) ) { ?>
		<div class="desc"><?php echo esc_html( $desc ); ?></div>
	<?php } ?>
</div>
