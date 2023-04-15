<?php
$columns     = $cl_meta_field['columns'] ?? '12';
$sort_clone  = $cl_meta_field['sort_clone'] ?? false;
$post_meta   = array_filter( get_post_meta( $post->ID, $cl_meta_field['id'] ) );
$placeholder = $cl_meta_field['placeholder'] ?? '';
$desc        = $cl_meta_field['desc'] ?? '';
$rows        = $cl_meta_field['rows'] ?? '4';

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

<!-- Textarea Field -->
<div class="column col-<?php echo esc_attr( $columns . ' ' . $sortable ); ?>">

	<?php
	if ( is_array( $meta_clone_val['meta_val'] ) ) {
		foreach ( $meta_clone_val['meta_val'] as $val ) {
			printf( $meta_clone_val['before_clone'] );
			?>
			<label for="cl-meta-text-input" class="cl-meta-text-input-label"><?php echo esc_html( $cl_meta_field['name'] ); ?></label>
			<textarea placeholder="<?php echo esc_attr( $placeholder ); ?>" class="cl-meta-textarea-input" name="<?php echo esc_attr( $meta_clone_val['cl_name'] ); ?>" rows="<?php echo esc_attr( $rows ); ?>"><?php echo esc_attr( $val ); ?></textarea>
			<?php
			printf( $meta_clone_val['remove_btn'] );
			printf( $meta_clone_val['after_clone'] );
		}
	} else {
		?>
		<label for="cl-meta-textarea-input" class="cl-meta-textarea-input-label"><?php echo esc_html( $cl_meta_field['name'] ); ?></label>
		<textarea placeholder="<?php echo esc_attr( $placeholder ); ?>" class="cl-meta-textarea-input" name="<?php echo esc_attr( $meta_clone_val['cl_name'] ); ?>" rows="<?php echo esc_attr( $rows ); ?>"><?php echo esc_attr( $meta_clone_val['meta_val'] ); ?></textarea>
		<?php
	}
	printf( $meta_clone_val['add_btn'] );
	if ( ! empty( $desc ) ) {
		?>
		<div class="desc"><?php echo esc_html( $desc ); ?></div>
	<?php } ?>
</div>
