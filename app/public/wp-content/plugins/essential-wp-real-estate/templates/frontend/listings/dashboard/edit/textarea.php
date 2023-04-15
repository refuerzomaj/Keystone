<?php
if ( array_key_exists( 'value', $args['field_data'] ) ) {
	$value = $args['field_data']['value'];
} else {
	$value = '';
}
?>
<div class="column form-group col-md-12" data-group_field_id="<?php echo esc_attr( $args['field_data']['id'] ); ?>">
	<label for="<?php echo 'add_' . esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['field_data']['name'] ); ?></label>
	<textarea id="<?php echo 'add_' . esc_attr( $args['id'] ); ?>" class="form-control cl_add_field" name="<?php echo esc_attr( $args['id'] ); ?>" id="" cols="30" rows="10" <?php echo $max_characters;?>><?php echo esc_attr( $value ); ?></textarea>
</div>
