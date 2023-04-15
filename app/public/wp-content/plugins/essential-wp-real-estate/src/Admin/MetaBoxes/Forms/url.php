<?php
$columns     = $cl_meta_field['columns'] ?? '12';
$sort_clone  = $cl_meta_field['sort_clone'] ?? false;
$post_meta   = array_filter( get_post_meta( $post->ID, $cl_meta_field['id'] ) );
$placeholder = $cl_meta_field['placeholder'] ?? '';
$desc        = $cl_meta_field['desc'] ?? '';
// -- Adding std Value -- //
if ( isset( $cl_meta_field['std'] ) && empty( $post_meta ) ) {
	$meta_val = $cl_meta_field['std'];
} else {
	$meta_val = get_post_meta( $post->ID, $cl_meta_field['id'], true );
}

// -- Sortable Status
if ( $sort_clone == true ) {
	$sortable = 'cl-meta-sortable';
} else {
	$sortable = '';
}

// -- Clone Variable -- //
$clone          = $cl_meta_field['clone'] ?? false;
$meta_clone_val = meta_clone_variable_declaration( $clone, $cl_meta_field['id'], $meta_val );

?>
<!-- Url Field -->
<div class="column col-<?php echo esc_attr( $columns . ' ' . $sortable ); ?>">
	<label for="cl-meta-url-input" class="cl-meta-url-input-label"><?php echo esc_html( $cl_meta_field['name'] ); ?></label>
	<?php
	if ( is_array( $meta_clone_val['meta_val'] ) ) {
		foreach ( $meta_clone_val['meta_val'] as $val ) {
			printf( $meta_clone_val['before_clone'] );
			?>
			<input placeholder="<?php echo esc_attr( $placeholder ); ?>" class="cl-meta-url-input" type="url" name="<?php echo esc_attr( $meta_clone_val['cl_name'] ); ?>" value="<?php echo esc_attr( $val ); ?>">
			<?php
			printf( $meta_clone_val['remove_btn'] );
			printf( $meta_clone_val['after_clone'] );
		}
	} else {
		?>
		<input placeholder="<?php echo esc_attr( $placeholder ); ?>" class="cl-meta-url-input" type="url" name="<?php echo esc_attr( $meta_clone_val['cl_name'] ); ?>" value="<?php echo esc_attr( $meta_clone_val['meta_val'] ); ?>">
		<?php
	}
	printf( $meta_clone_val['add_btn'] );
	if ( ! empty( $desc ) ) {
		?>
		<div class="desc"><?php echo esc_html( $desc ); ?></div>
	<?php } ?>
</div>
