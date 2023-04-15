<?php
if ( isset( $args['field_data']['clone'] ) ) {
	$clone_data = true;
} else {
	$clone_data = false;
}
?>
<div class="form-group col-md-12">
	<label for="<?php echo 'add_' . esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['field_data']['name'] ); ?></label>
	<hr>
	<div class="row">
		<?php
		if ( $clone_data == true ) {
			$unique_id = uniqid();
			echo '<div class="col-md-12 column" data-group_id="' . esc_attr( $args['id'] ) . '"><div class="cl_mb_clone_single">';
			foreach ( $args['field_data']['fields'] as $value ) {
				$attr['key']        = $unique_id;
				$attr['id']         = $args['id'] . '[' . $attr['key'] . '][' . $value['id'] . ']';
				$attr['field_data'] = $value;
				cl_get_template( "dashboard/add/{$value['type']}.php", $attr );
			}
			echo '<button class="remove_clone_btn"><span class="dashicons dashicons-dismiss"></span> '.esc_html__('Remove','essential-wp-real-estate').'</button></div>';
			echo '<button id="' . esc_attr( $args['id'] ) . '" class="mb_btn primary clone_group_btn"><span class="dashicons dashicons-plus-alt"></span>' . esc_html__( 'Add New', 'essential-wp-real-estate' ) . '</button>';
			echo '</div>';
		} else {
			foreach ( $args['field_data']['fields'] as $value ) {
				$attr['id']         = $args['id'] . '[' . $value['id'] . ']';
				$attr['field_data'] = $value;
				cl_get_template( "dashboard/add/{$value['type']}.php", $attr );
			}
		}
		?>
	</div>
</div>
