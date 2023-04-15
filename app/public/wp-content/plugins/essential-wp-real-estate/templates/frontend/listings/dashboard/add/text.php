<?php
$require_val = isset( $args['field_data']['required'] ) ? $args['field_data']['required'] : '';
?>
<div class="column form-group col-md-12" data-group_field_id="<?php echo esc_attr( $args['field_data']['id'] ); ?>">
	<label for="<?php echo 'add_' . esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['field_data']['name'] ); ?></label>
	<input <?php echo esc_html( $require_val ); ?> id="<?php echo 'add_' . esc_attr( $args['id'] ); ?>" class="form-control cl_add_field" type="text" name="<?php echo esc_attr( $args['id'] ); ?>">
</div>
