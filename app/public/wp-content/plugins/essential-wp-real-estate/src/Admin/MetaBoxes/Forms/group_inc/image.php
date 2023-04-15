<?php
$group_columns   = $cl_meta_group_field['columns'] ?? '12';
$placeholder     = $cl_meta_group_field['placeholder'] ?? '';
$placeholder_url = WPERESDS_ASSETS . '/img/placeholder.png';
$group_desc      = $cl_meta_group_field['desc'] ?? '';

// -- Load Default Value -- //
if ( $clone == true ) {
	$group_meta_val = $meta_val[ $key ][ $cl_meta_group_field['id'] ] ?? '';
} else {
	$group_meta_val = $meta_val[ $cl_meta_group_field['id'] ] ?? '';
}

// -- Clone Variable -- //
$meta_group_clone_val = meta_clone_group_variable_declaration( $clone, $key, $cl_meta_field['id'], $cl_meta_group_field['id'], $group_meta_val );

?>
<div class="column col-<?php echo esc_attr( $group_columns ); ?>" data-group_field_id="<?php echo esc_attr( $cl_meta_group_field['id'] ); ?>">

	<!-- Image -->
	<label for="components-form-token-input-0" class="components-form-token-field__label"><?php echo esc_html( $cl_meta_group_field['name'] ); ?></label>
	<div class="mb_gal__container" data-key="<?php echo esc_attr( $key ); ?>">
		<div data-field_id="<?php echo esc_attr( $cl_meta_group_field['id'] ); ?>" id="<?php echo esc_attr( $cl_meta_group_field['id'] ) . $key . '_cont'; ?>" class="components-responsive-wrapper">
			<?php
			if ( ! empty( $group_meta_val ) ) {
				foreach ( $group_meta_val as $val ) {
					?>
					<div id="<?php echo esc_attr( $val ); ?>" class="single_img">
						<input type="hidden" name="<?php echo esc_attr( $meta_group_clone_val['cl_name'] ) . '[]'; ?>" value="<?php echo esc_attr( $val ); ?>">
						<?php echo wp_get_attachment_image( $val, 'meta-thumb' ); ?>
						<a id="<?php echo esc_attr( $cl_meta_group_field['id'] ); ?>" data-img_id="<?php echo esc_attr( $val ); ?>" class="cl-remove" href="javascript:void(0)">X</a>
					</div>
					<?php
				}
			} else {
				?>
				<img id="<?php echo esc_attr( $cl_meta_group_field['id'] ) . $key; ?>" class="cl_mb_placeholder" src="<?php echo esc_attr( $placeholder_url ); ?>" alt="<?php echo esc_attr( $cl_meta_group_field['name'] ); ?>">
			<?php } ?>
		</div>
		<div class="button_control">
			<button data-name="<?php echo esc_attr( $meta_group_clone_val['cl_name'] ); ?>" id="<?php echo esc_attr( $cl_meta_group_field['id'] ) . $key; ?>" type="button" class="mb_btn mb_img_upload_btn"><?php echo esc_html__( 'Upload Images', 'essential-wp-real-estate' ); ?></button>
			<button data-placeholder="<?php echo esc_attr( $placeholder_url ); ?>" id="<?php echo esc_attr( $cl_meta_group_field['id'] ) . $key; ?>" type="button" class="mb_btn cl_mb_clear_btn"><?php echo esc_html__( 'Clear', 'essential-wp-real-estate' ); ?></button>
		</div>
	</div>
	<!-- Image -->

	<?php
	if ( ! empty( $group_desc ) ) {
		?>
		<div class="desc"><?php echo esc_html( $group_desc ); ?></div>
	<?php } ?>
</div>
