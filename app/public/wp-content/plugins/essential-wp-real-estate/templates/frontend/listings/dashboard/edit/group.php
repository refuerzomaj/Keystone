<?php
if ( isset( $args['field_data']['clone'] ) ) {
	$clone_data = true;
} else {
	$clone_data = false;
}
foreach ( $args['field_data']['fields'] as $field_data_key => $field_data_value ) {
	$field_arg[ $field_data_value['id'] ] = $field_data_value;
}
?>
<div class="form-group col-md-12">
	<label for="<?php echo 'add_' . esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['field_data']['name'] ); ?></label>
	<hr>
	<div class="row">
		<?php
		if ( isset( $args['field_data']['value'] ) && ! empty( $args['field_data']['value'] ) ) { // --- WITH VALUE --- //
			if ( $clone_data == false ) {
				foreach ( $field_arg as $n_c_f_k => $n_c_f_v ) {
					$atts['id']         = $args['id'] . '[' . $n_c_f_k . ']';
					$atts['field_data'] = $field_arg[ $n_c_f_k ];
					if ( array_key_exists( $n_c_f_k, $args['field_data']['value'] ) ) {
						$atts['field_data']['value'] = $args['field_data']['value'][ $n_c_f_k ];
					}
					if ( $atts['field_data']['type'] == 'maps' ) {
						$atts['field_data']['value'] = $args['field_data']['value'];
					}
					cl_get_template( "dashboard/edit/{$field_arg[$n_c_f_k]['type']}.php", $atts );
				}
			} else {



				echo '<div class="col-md-12 column" data-group_id="' . esc_attr( $args['id'] ) . '">';
				foreach ( $args['field_data']['value'] as $c_f_v ) {
					$unique_id = uniqid();
					$c_f_k     = $args['id'] . '[' . $unique_id . ']';
					echo '<div class="cl_mb_clone_single">';
					foreach ( $c_f_v as $field_data_key => $field_data_value ) {
						$attr['key']                 = $unique_id;
						$attr['id']                  = $c_f_k . '[' . $field_data_key . ']';
						$attr['field_data']          = $field_arg[ $field_data_key ];
						$attr['field_data']['value'] = $field_data_value;
						cl_get_template( "dashboard/edit/{$field_arg[$field_data_key]['type']}.php", $attr );
					}
					echo '<button class="remove_clone_btn"><span class="dashicons dashicons-dismiss"></span> ' . esc_html__( 'Remove', 'essential-wp-real-estate' ) . '</button></div>';
				}

				echo '<button id="' . esc_attr( $args['id'] ) . '" class="mb_btn primary clone_group_btn"><span class="dashicons dashicons-plus-alt"></span>' . esc_html__( 'Add New', 'essential-wp-real-estate' ) . '</button>';
				echo '</div>';
			}
		} else { // --- WITHOUT VALUE RUN DEFAULT FORMS --- //
			foreach ( $args['field_data']['fields'] as $value ) {
				$attr['id']         = $args['id'] . '[' . $value['id'] . ']';
				$attr['field_data'] = $value;
				cl_get_template( "dashboard/add/{$value['type']}.php", $attr );
			}
		}
		?>
	</div>
</div>
