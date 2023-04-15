<?php
use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Admin\Settings\Single;

class Setting_single extends Single {

	use Traitval;
	public function __construct() {
		add_action( 'cl_admin_settings_tab_top_pagelayout_single', array( $this, 'cl_single_settings_layout_view' ) );
	}

	function cl_single_settings_layout_view() {
		$single_setting = get_option( 'cl_single_settings_layout', array() );

		if ( empty( $single_setting ) || $single_setting == 'null' ) {
			$single_setting = $this->single_default_layout_setting();
		} else {
			$single_setting = json_decode( $single_setting, true );
		}

		$single_setting = WPERECCP()->admin->settings_instances->admin_settings_extend( $single_setting, $this->single_default_layout_setting() );
		?>
		<section class="gallery_parts pt-2 pb-2 d-lg-none d-xl-block">
			<div class="container shortablediv">
				<div class="row align-items-center single-sortable-div">
					<?php

					foreach ( $single_setting as $settings_key => $settings_val ) {
						$co = 0;
						if ( $settings_key == 'extrasection' ) {

							$co                = 0;
							$setting_val_class = $single_setting['extrasection']['class'] ?? '';
							unset( $single_setting['extrasection']['class'] );
							echo '<div class="_card_list_flex mb-2 ' . esc_attr( $setting_val_class ) . '">';
							$str = '';
							echo ' <div class="property_block_wrap extrasection_item_wrapper">';

							foreach ( $single_setting['extrasection'] as $key => $section ) {
								$co++;
								$class = $section['class'] ?? '';
								if ( is_array( $section ) && ! empty( $section ) ) {
									if ( $section['active'] ) {
										echo '<div class="div_element_main _warpper_' . esc_attr( $key ) . '"  data-class= "' . esc_attr( $class ) . '">';
										echo $this->{$key}( $section['active'] ) . '<span class="action_box"><li class="element_inactive"><i class="fa fa-times" aria-hidden="true"></i></li><li class="element_active"><i class="fa fa-plus" aria-hidden="true"></i></li></span>';
										echo '</div>';
									} else {
										$str .= '<div class="div_element_main _warpper_' . esc_attr( $key ) . '"  data-class= "' . esc_attr( $class ) . '">';
										$str .= $this->{$key}( $section['active'] ) . '<span class="action_box"><li class="element_inactive"><i class="fa fa-times" aria-hidden="true"></i></li><li class="element_active"><i class="fa fa-plus" aria-hidden="true"></i></li></span>';
										$str .= '</div>';
									}
								}
							}
							echo '<span class="plus-icon fa fa-plus"></span>';
							echo WPERECCP()->admin->settings_instances->cl_listing_modal_container( $str );
							echo '</div>';
							echo '</div>';
						} elseif ( $settings_key == 'templatesection' ) {
							$co                = 0;
							$setting_val_class = $single_setting['templatesection']['class'] ?? '';
							unset( $single_setting['templatesection']['class'] );
							echo '<div class="_card_list_flex mb-2 ' . esc_attr( $setting_val_class ) . '">';
							$str = '';
							echo ' <div class="property_block_wrap templatesection_item_wrapper">';

							foreach ( $single_setting['templatesection'] as $key => $section ) {
								$co++;
								$class = $section['class'] ?? '';
								if ( is_array( $section ) && ! empty( $section ) ) {
									if ( $section['active'] ) {
										echo '<div class="div_element_main _warpper_' . esc_attr( $key ) . '"  data-class= "' . esc_attr( $class ) . '">';
										echo $this->{$key}( $section['active'] ) . '<span class="action_box"><li class="element_inactive"><i class="fa fa-times" aria-hidden="true"></i></li><li class="element_active"><i class="fa fa-plus" aria-hidden="true"></i></li></span>';
										echo '</div>';
									} else {
										$str .= '<div class="div_element_main _warpper_' . esc_attr( $key ) . '"  data-class= "' . esc_attr( $class ) . '">';
										$str .= $this->{$key}( $section['active'] ) . '<span class="action_box"><li class="element_inactive"><i class="fa fa-times" aria-hidden="true"></i></li><li class="element_active"><i class="fa fa-plus" aria-hidden="true"></i></li></span>';
										$str .= '</div>';
									}
								}
							}
							echo '<span class="plus-icon fa fa-plus"></span>';
							echo WPERECCP()->admin->settings_instances->cl_listing_modal_container( $str );
							echo '</div>';
							echo '</div>';
						} else {
							$setting_val_class = $settings_val['class'] ?? '';
							unset( $settings_val['class'] );
							echo '<div class="_card_list_flex mb-2 ' . esc_attr( $setting_val_class ) . '">';
							foreach ( $settings_val as $key => $section ) {
								$co++;
								$str = '';
								if ( is_array( $section ) && ! empty( $section ) ) {
									echo '<div class="_card_list_flex_0' . esc_attr( $co ) . ' ' . esc_attr( $settings_key ) . '_item ' . esc_attr( $settings_key ) . '_' . esc_attr( $section['class'] ) . '">';
									echo '<div class="' . esc_attr( $section['class'] ) . '" data-position="' . esc_attr( $key ) . '"  data-area="' . esc_attr( $settings_key ) . '">';
									foreach ( $section as $keysec => $sectionchild ) {
										$class = $sectionchild['class'] ?? '';
										if ( is_array( $sectionchild ) && ! empty( $sectionchild ) ) {
											if ( $sectionchild['active'] ) {
												$func = 'single_' . esc_attr( $keysec ) . '_type_html';
												echo '<div class="div_element_main _warpper_' . esc_attr( $keysec ) . ' ' . esc_attr( $key ) . '_item"  data-class= "' . esc_attr( $class ) . '">';
												echo $this->{$func}() . $this->action_element_html( $settings_key, $key, $keysec, '0' ) . $this->action_element_html( $settings_key, $key, $keysec . '][active', $sectionchild['active'] ) . '<span class="action_box"><li class="element_inactive"><i class="fa fa-times" aria-hidden="true"></i></li><li class="element_active"><i class="fa fa-plus" aria-hidden="true"></i></li></span>';
												echo '</div>';
											} else {
												$func = 'single_' . esc_attr( $keysec ) . '_type_html';
												$str .= '<div class="div_element_main _warpper_' . esc_attr( $keysec ) . '"  data-class= "' . esc_attr( $class ) . '">';
												$str .= $this->{$func}() . $this->action_element_html( $settings_key, $key, $keysec, $sectionchild['active'] ) . '<span class="action_box"><li class="element_inactive"><i class="fa fa-times" aria-hidden="true"></i></li><li class="element_active"><i class="fa fa-plus" aria-hidden="true"></i></li></span>';
												$str .= '</div>';
											}
										}
									}
									echo '<span class="plus-icon fa fa-plus"></span>';
									echo WPERECCP()->admin->settings_instances->cl_listing_modal_container( $str );
									echo '</div>';
									echo '</div>';
								}
							}
							echo '</div>';
						}
					}
					?>
				</div>
			</div>
		</section>
		<?php
	}
}
new Setting_single();
