<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$get_value   = isset( $_GET[ $args['field_key'] ] ) ? cl_sanitization( $_GET[ $args['field_key'] ] ) : '';
$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
$class       = isset( $args['options']['class'] ) ? $args['options']['class'] : '';
if ( isset( $args['icon'] ) && ! empty( $args['icon'] ) ) {
	$ic_on = 'input-with-icon';
} else {
	$ic_on = '';
}
?>
<!-- Location Field -->
<div class="form-group">
	<div class="<?php echo esc_attr( $ic_on ); ?> field-container">
		<input name="<?php echo esc_attr( $args['field_key'] ); ?>" type="text" class="form-control <?php echo esc_attr( $class ); ?>" autocomplete="off" value="<?php echo esc_html( $get_value ); ?>" placeholder="<?php echo esc_html( $placeholder ); ?>">
		<?php
		// -- Show icon if set and not empty
		if ( isset( $args['icon'] ) && ! empty( $args['icon'] ) ) {
			echo '<i class="' . esc_attr( $args['icon'] ) . '"></i>';
		}
		if ( $class == 'get-location-js' ) {
			echo '<div class="location_result"></div>';
		}
		?>
	</div>
</div>
