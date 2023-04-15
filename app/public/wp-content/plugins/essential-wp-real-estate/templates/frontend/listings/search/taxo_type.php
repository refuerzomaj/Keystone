<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$get_value  = isset( $_GET[ $args['field_key'] ] ) ? cl_sanitization( $_GET[ $args['field_key'] ] ) : '';
$hide_empty = isset( $_GET[ $args['options']['hide_empty'] ] ) ? cl_sanitization( $_GET[ $args['options']['hide_empty'] ] ) : false;
$class      = isset( $args['options']['class'] ) ? $args['options']['class'] : '';
$term_lists = get_terms(
	array(
		'taxonomy'   => $args['data_key'],
		'hide_empty' => $hide_empty,
	)
);
if ( isset( $args['icon'] ) && ! empty( $args['icon'] ) ) {
	$ic_on = 'input-with-icon';
} else {
	$ic_on = '';
}
?>
<!-- Location Field -->
<div class="form-group">
	<div class="field-container">
		<div class="form-group">
			<div class="<?php echo esc_attr( $ic_on ); ?> simple-input">
				<?php $args['options']['type']; ?>
				<?php if ( isset( $args['options']['type'] ) && ! empty( $args['options']['type'] ) && $args['options']['type'] == 'multiple' ) { ?>
					<!-- Checkbox / Multiple Type Field -->
					<ul class="row p-0 m-0">
						<?php
						foreach ( $term_lists as $term_list ) {
							if ( is_array( $get_value ) && in_array( $term_list->slug, $get_value ) ) {
								$active = 'checked';
							} elseif ( ! is_array( $get_value ) && $term_list->slug == $get_value ) {
								$active = 'checked';
							} else {
								$active = '';
							}
							echo '<li class="col-lg-6 col-md-6 p-0">';
							echo '<input ' . esc_attr( $active ) . ' id="' . esc_attr( $term_list->slug ) . '" class="checkbox-custom" name="' . esc_attr( $args['field_key'] ) . '[]" type="checkbox" value="' . esc_attr( $term_list->slug ) . '">';
							echo '<label for="' . esc_attr( $term_list->slug ) . '" class="checkbox-custom-label">' . esc_html( $term_list->name ) . '</label>';
							echo '</li>';
						}
						?>
					</ul>
				<?php } else { ?>
					<!-- Select Type -->
					<select name="<?php echo esc_attr( $args['field_key'] ); ?>" class="form-control select2">
						<option value=""><?php echo esc_html__( 'Select ', 'essential-wp-real-estate' ) . esc_html( $args['label'] ); ?></option>
						<?php
						foreach ( $term_lists as $term_list ) {
							if ( $get_value == $term_list->slug ) {
								$active = 'selected';
							} else {
								$active = '';
							}
							echo '<option ' . esc_attr( $active ) . ' value="' . esc_attr( $term_list->slug ) . '">' . esc_html( $term_list->name ) . '</option>';
						}
						?>
					</select>
					<?php
				}
				// -- Show icon if set and not empty
				if ( isset( $args['icon'] ) && ! empty( $args['icon'] ) ) {
					echo '<i class="' . esc_attr( $args['icon'] ) . '"></i>';
				}
				?>
			</div>
		</div>
	</div>
</div>
