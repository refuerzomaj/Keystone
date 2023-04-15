<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$search_key  = isset( $_GET['s'] ) ? cl_sanitization( $_GET['s'] ) : '';
$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
?>
<!-- Search Field -->
<div class="form-group">
	<div class="input-with-icon">
		<input name="s" type="text" class="form-control" placeholder="<?php echo esc_html( $placeholder ); ?>" value="<?php echo esc_html( $search_key ); ?>">
		<i class="fas fa-search"></i>
	</div>
</div>
