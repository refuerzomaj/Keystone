<?php
use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Admin\MetaBoxes\Components\Groups;

class Setting_Comp_Field_List {


	use Traitval;

	public function __construct() {
		add_action( 'cl_admin_settings_tab_top_pagelayout_comp_field_list', array( $this, 'cl_comp_field_list_builder_setting' ) );
	}

	public function cl_comp_field_list_builder_setting() {
		$comp_field_data_arr = array();
		$add_settings        = get_option( 'cl_add_builder_setting', array() );
		$comp_field_data     = get_option( 'cl_comp_field_list_builder_setting', array() );
		if ( isset( $add_settings ) && ! empty( $add_settings ) ) {
			$add_settings = json_decode( $add_settings, true );
		}
		if ( isset( $comp_field_data ) && ! empty( $comp_field_data ) ) {
			$comp_field_data_arr = json_decode( $comp_field_data, true );
		}

		if ( isset( $add_settings['enabled'] ) && $add_settings['enabled'] ) {
			echo '<h1>' . esc_html_e( 'Select fields to Compare Page', 'essential-wp-real-estate' ) . '</h1>';

			foreach ( $add_settings['enabled'] as $add_settings_key => $add_settings_value ) {
				echo '<h2>' . esc_html( $add_settings_key ) . '</h2>';
				if ( is_array( $add_settings_value ) ) {
					echo '<div class="comp-data">';
					foreach ( $add_settings_value as $add_settings_value_key => $add_settings_value_child ) {
						$name     = str_replace( 'wperesds_', '', $add_settings_value_child );
						$label    = ucwords( $name );
						$field_id = $add_settings_key . '_' . $add_settings_value_key;
						echo '<div class="comp-data-item">';
						if ( in_array( $add_settings_value_child, $comp_field_data_arr ) ) {
							echo '<p>' . esc_html( $label ) . '</p><label class="switch" for="' . esc_attr( $field_id ) . '"><input checked type="checkbox" id="' . esc_attr( $field_id ) . '" name="comp_field_list[]" value="' . esc_attr( $add_settings_value_child ) . '"><span class="slider round"></span></label>';
						} else {
							echo '<p>' . esc_html( $label ) . '</p><label class="switch" for="' . esc_attr( $field_id ) . '"><input type="checkbox" id="' . esc_attr( $field_id ) . '" name="comp_field_list[]" value="' . esc_attr( $add_settings_value_child ) . '"><span class="slider round"></span></label>';
						}
						echo '</div>';
					}
					echo '</div>';
				}
			}
		}
	}
}
new Setting_Comp_Field_List();
