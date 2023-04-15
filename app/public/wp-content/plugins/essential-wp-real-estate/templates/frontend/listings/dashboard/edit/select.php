<?php
if ( array_key_exists( 'value', $args['field_data'] ) && isset( $args['field_data']['value'] ) && ! empty( $args['field_data']['value'] ) ) {
	if ( is_array( $args['field_data']['value'] ) ) {
		foreach ( $args['field_data']['value'] as $args_f_v_k => $args_f_v_v ) {
			if ( ! empty( $args_f_v_k ) ) {
				$value = $args_f_v_v;
			} else {
				$value = '';
			}
		}
	} else {
		$value = $args['field_data']['value'];
	}
} else {
	$value = '';
}
?>
<div class="column form-group col-md-12" data-group_field_id="<?php echo esc_attr( $args['field_data']['id'] ); ?>">
	<label for="<?php echo 'add_' . esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['field_data']['name'] ); ?></label>
	<select class="form-control cl_add_field" id="<?php echo 'add_' . esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>">
		<?php
		foreach ( $args['field_data']['options'] as $option_key => $option ) {
			if ( $option == $value ) {
				echo '<option selected value="' . esc_attr( $option_key ) . '">' . esc_html( $option ) . '</option>';
			} else {
				echo '<option value="' . esc_attr( $option_key ) . '">' . esc_html( $option ) . '</option>';
			}
		}
		?>
	</select>
</div>
