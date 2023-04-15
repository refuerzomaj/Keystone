<div class="column form-group col-md-12" data-group_field_id="<?php echo esc_attr( $args['field_data']['id'] ); ?>">
	<label for="<?php echo 'add_' . esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['field_data']['name'] ); ?></label>
	<select class="form-control cl_add_field" id="<?php echo 'add_' . esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>">
		<?php
		foreach ( $args['field_data']['options'] as $option_key => $option ) {
			echo '<option value="' . esc_attr( $option_key ) . '">' . esc_html( $option ) . '</option>';
		}
		?>
	</select>
</div>
