<div class="col-md-12 form-group cl_featured_img">
	<?php
	$avatar_url = wp_get_attachment_url( $args['field_data']['value'][0] );
	if ( $avatar_url ) {
		echo '<img class="files_featured" width="230px"  height="230px" src="' . esc_url( $avatar_url ) . '" alt="img" />';
	} else {
		echo '<img class="files_featured" src="' . WPERESDS_ASSETS . '/img/placeholder_light.png' . '" alt="img" />';
	}
	?>
	<label for="file-input" id="add-ft-img" class="select_single_label">
		<i class="fa fa-upload"></i><?php echo esc_html__( ' Select Image', 'essential-wp-real-estate' ); ?>
	</label>
	<input type="hidden" class="single_img_id" name="<?php echo esc_attr( $args['id'] . '[]' ); ?>">
</div>
