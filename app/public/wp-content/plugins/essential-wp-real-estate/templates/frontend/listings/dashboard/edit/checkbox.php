<?php
if ( array_key_exists( 'value', $args['field_data'] ) && isset( $args['field_data']['value'] ) && ! empty( $args['field_data']['value'] ) ) {
	$value = $args['field_data']['value'];
} else {
	$value = '';
}
?>
<div class="column form-group col-md-12" data-group_field_id="<?php echo esc_attr( $args['field_data']['id'] ); ?>">
	<label><?php echo esc_html( $args['field_data']['name'] ); ?></label>
	<div class="checkbox_cont row">
		<?php
		foreach ( $args['field_data']['options'] as $option_key => $option ) {
			if ( ! empty( $option_key ) ) {
				if ( in_array( $option, $value ) ) {
					$check_val = 'checked';
				} else {
					$check_val = null;
				}
				echo '<div class="checkbox_item col-md-3">';
				echo '<input type="checkbox" id="' . esc_attr( $option_key ) . '" name="' . esc_attr( $args['id'] ) . '[]" value="' . esc_attr( $option_key ) . '" ' . esc_attr( $check_val ) . '>';
				echo '<label for="' . esc_attr( $option_key ) . '">' . esc_html( $option ) . '</label>';
				echo '</div>';
			}
		}
		?>
	</div>
</div>
