<?php
$columns     = $cl_meta_field['columns'] ?? '12';
$sort_clone  = $cl_meta_field['sort_clone'] ?? false;
$post_meta   = array_filter( get_post_meta( $post->ID, $cl_meta_field['id'] ) );
$placeholder = $cl_meta_field['placeholder'] ?? '';
$desc        = $cl_meta_field['desc'] ?? '';
$name        = $cl_meta_field['name'] ?? '';

// -- Adding std Value -- //
if ( empty( $post_meta ) ) {
	$meta_val = false;
} else {
	$meta_val = get_post_meta( $post->ID, $cl_meta_field['id'], true );
}

// -- Clone Variable -- //
$clone = $cl_meta_field['clone'] ?? false;
if ( isset( $clone ) && $clone === true ) {
	$before_clone = '<div class="cl_mb_clone_single cl-group-meta">';
	$after_clone  = '</div>';
	$cl_name      = $cl_meta_field['id'];
	$add_btn      = '<button id="' . esc_attr( $cl_meta_field['id'] ) . '" class="mb_btn primary clone_group_btn"><span class="dashicons dashicons-plus-alt"></span>Add New</button>';
	$remove_btn   = '<button class="remove_clone_btn"><span class="dashicons dashicons-dismiss"></span></button>';
} else {
	$before_clone = '<div class="cl-group-meta">';
	$after_clone  = '</div>';
	$cl_name      = $cl_meta_field['id'];
	$add_btn      = null;
	$remove_btn   = null;
}

// -- Sortable Status
if ( $clone == true && $sort_clone == true ) {
	$sortable = 'cl-meta-sortable';
} else {
	$sortable = '';
}

?>
<!-- Group Field -->
<div class="column col-<?php echo esc_attr( $columns . ' ' . $sortable ); ?>" data-group_id="<?php echo esc_attr( $cl_meta_field['id'] ); ?>">
	<label for="cl-meta-group-val"><?php echo esc_html( $name ); ?></label>
	<?php
	if ( is_array( $meta_val ) && $clone === true ) {
		$group_val = array_values( $meta_val );
		foreach ( $meta_val as $key => $val ) {
			printf( $before_clone );
			foreach ( $cl_meta_field['fields'] as $cl_meta_group_field ) {
				include dirname( __FILE__ ) . '/group_inc/' . $cl_meta_group_field['type'] . '.php';
			}
			printf( $remove_btn );
			printf( $after_clone );
		}
	} else {
		$key = $key ?? substr( str_shuffle( '0123456789abcdefghijklmnopqrstuvwxyz' ), 1, 10 );
		printf( $before_clone );
		foreach ( $cl_meta_field['fields'] as $cl_meta_group_field ) {
			include dirname( __FILE__ ) . '/group_inc/' . $cl_meta_group_field['type'] . '.php';
		}
		printf( $after_clone );
	}
	printf( $add_btn );
	if ( ! empty( $desc ) ) {
		?>
		<div class="desc"><?php echo esc_html( $desc ); ?></div>
	<?php } ?>
</div>
