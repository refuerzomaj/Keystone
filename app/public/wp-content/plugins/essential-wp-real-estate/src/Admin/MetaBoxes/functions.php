<?php
/**
 * meta_clone_variable_declaration
 *
 * @param  mixed $param_clone
 * @param  mixed $param_id
 * @param  mixed $param_val
 * @return void
 *
 * since 1.0.0
 */
function meta_clone_variable_declaration( $param_clone, $param_id, $param_val ) {

	if ( isset( $param_clone ) && $param_clone === true ) {
		$before_clone = '<div class="cl_mb_clone_single">';
		$after_clone  = '</div>';
		$cl_name      = $param_id . '[]';
		$add_btn      = '<button id="' . esc_attr( $param_id ) . '" class="mb_btn primary clone_btn"><span class="dashicons dashicons-plus-alt"></span>' . esc_html__( 'Add New', 'essential-wp-real-estate' ) . '</button>';
		$remove_btn   = '<button class="remove_clone_btn"><span class="dashicons dashicons-dismiss"></span></button>';
		$meta_val     = (array) $param_val;
	} else {
		$before_clone = null;
		$after_clone  = null;
		$cl_name      = $param_id;
		$add_btn      = null;
		$remove_btn   = null;
		$meta_val     = (string) $param_val;
	}
	$result = array(
		'before_clone' => $before_clone,
		'after_clone'  => $after_clone,
		'cl_name'      => $cl_name,
		'add_btn'      => $add_btn,
		'remove_btn'   => $remove_btn,
		'meta_val'     => $meta_val,
	);
	return $result;
}


/**
 * meta_clone_group_variable_declaration
 *
 * @param  mixed $param_clone
 * @param  mixed $key
 * @param  mixed $param_id
 * @param  mixed $param_group_id
 * @param  mixed $group_meta_val
 * @return void
 *
 * since 1.0.0
 */
function meta_clone_group_variable_declaration( $param_clone, $key, $param_id, $param_group_id, $group_meta_val ) {
	// -- Clone Variable -- //
	if ( isset( $param_clone ) && $param_clone === true ) {
		$cl_name        = $param_id . '[' . $key . '][' . $param_group_id . ']';
		$group_meta_val = $group_meta_val;
	} else {
		$cl_name        = $param_id . '[' . $param_group_id . ']';
		$group_meta_val = $group_meta_val;
	}

	$result = array(
		'cl_name'        => $cl_name,
		'group_meta_val' => $group_meta_val,
	);
	return $result;
}
