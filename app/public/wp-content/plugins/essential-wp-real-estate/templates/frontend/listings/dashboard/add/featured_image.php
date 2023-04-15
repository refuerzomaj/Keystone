<div class="col-md-12 form-group cl_featured_img">
	<?php
	echo '<img class="files_featured" src="' . WPERESDS_ASSETS . '/img/placeholder_light.png' . '" alt="' . esc_attr( 'Listing Image', 'essential-wp-real-estate' ) . '" />';
	?>
	<label for="file-input" id="add-ft-img" class="select_single_label">
		<i class="fa fa-upload"></i><?php echo esc_html__( ' Select Image', 'essential-wp-real-estate' ); ?>
	</label>
	<input type="hidden" class="single_img_id" name="<?php echo esc_attr( $args['id'] . '[]' ); ?>">
</div>
