<?php
$columns         = $cl_meta_field['columns'] ?? '12';
$meta_val        = get_post_meta( $post->ID, $cl_meta_field['id'], true );
$placeholder_url = WPERESDS_ASSETS . '/img/placeholder.png';
$desc            = $cl_meta_field['desc'] ?? '';
?>
<!-- Image Field -->
<div class="column col-<?php echo esc_html( $columns ); ?>">
	<label for="components-form-token-input-0" class="components-form-token-field__label"><?php echo esc_html( $cl_meta_field['name'] ); ?></label>
	<div class="mb_gal__container">
		<div data-field_id="<?php echo esc_attr( $cl_meta_field['id'] ); ?>" id="<?php echo esc_attr( $cl_meta_field['id'] ) . '_cont'; ?>" class="components-responsive-wrapper">
			<?php
			if ( ! empty( $meta_val ) ) {
				foreach ( $meta_val as $val ) {
					?>
					<div id="<?php echo esc_attr( $val ); ?>" class="single_img">
						<input type="hidden" name="<?php echo esc_attr( $cl_meta_field['id'] ); ?>[]" value="<?php echo esc_attr( $val ); ?>">
						<?php echo wp_get_attachment_image( $val, 'meta-thumb' ); ?>
						<a id="<?php echo esc_attr( $cl_meta_field['id'] ); ?>" data-img_id="<?php echo esc_attr( $val ); ?>" class="cl-remove" href="javascript:void(0)">X</a>
					</div>
					<?php
				}
			} else {
				?>
				<img id="<?php echo esc_attr( $cl_meta_field['id'] ); ?>" class="cl_mb_placeholder" src="<?php echo esc_attr( $placeholder_url ); ?>" alt="<?php echo esc_attr( $cl_meta_field['name'] ); ?>">
			<?php } ?>
		</div>
		<div class="button_control">
			<button data-name="<?php echo esc_attr( $cl_meta_field['id'] ); ?>" id="<?php echo esc_attr( $cl_meta_field['id'] ); ?>" type="button" class="mb_btn mb_img_upload_btn"><?php echo esc_html__( 'Upload Images', 'essential-wp-real-estate' ); ?></button>
			<button data-placeholder="<?php echo esc_attr( $placeholder_url ); ?>" id="<?php echo esc_attr( $cl_meta_field['id'] ); ?>" type="button" class="mb_btn cl_mb_clear_btn"><?php echo esc_html__( 'Clear', 'essential-wp-real-estate' ); ?></button>
		</div>
	</div>
	<?php if ( ! empty( $desc ) ) { ?>
		<div class="desc"><?php echo esc_html( $desc ); ?></div>
	<?php } ?>
</div>
