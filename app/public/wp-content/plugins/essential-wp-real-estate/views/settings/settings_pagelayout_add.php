<?php
use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Admin\MetaBoxes\Components\Groups;
use Essential\Restate\Admin\MetaBoxes\Components\Fields;

class Setting_Add {

	use Traitval;

	public function __construct() {
		add_action( 'cl_admin_settings_tab_top_pagelayout_add', array( $this, 'cl_add_builder_setting' ) );
	}

	public function cl_add_builder_setting() {

		$this->get_fields = apply_filters( 'cl_meta_boxes', Groups::getInstance()->generate_groups() );
		
		$field_arr        = array();
		if ( isset( $this->get_fields ) && ! empty( $this->get_fields ) ) {
			foreach ( $this->get_fields as $fields ) {
				if ( isset( $fields['fields'] ) && ! empty( $fields['fields'] ) ) {
					foreach ( $fields['fields'] as $field ) {
						$field_arr[ $fields['id'] ][] = $field['id'];
					}
				}
			}
		}

		$add_fields = array(
			'enabled'  => $field_arr,
			'disabled' => array(),
		);

		$add_settings = get_option( 'cl_add_builder_setting', array() );

		if ( empty( $add_settings ) || $add_settings == 'null' ) {
			$add_settings          = $add_fields;
			$add_settings_enabled  = $add_fields['enabled'];
			$add_settings_disabled = $add_fields['disabled'];
		} else {
			$add_settings          = json_decode( $add_settings, true );
			$add_settings_enabled  = isset( $add_settings['enabled'] ) ? $add_settings['enabled'] : array();
			$add_settings_disabled = isset( $add_settings['disabled'] ) ? $add_settings['disabled'] : array();
		}
		?>
		<div class="add_builder_container">
			<div class="div_element_main">
				<h3><?php echo esc_html__( 'Customize the add form for this listing type', 'essential-wp-real-estate' ); ?></h3>
				<p><a target="_blank" href="https://doc.smartdatasoft.net"><?php echo esc_html__( 'Need help?', 'essential-wp-real-estate' ); ?></a></p>
			</div>
			<div class="add_builder_row">
				<div class="enabled add_builder-main_section">
					<h2><?php echo esc_html__( 'Enabled Fields', 'essential-wp-real-estate' ); ?></h2>
					<?php
					foreach ( $add_settings_enabled as $add_settings_key => $add_settings ) {
						echo '<div class="' . esc_attr( $add_settings_key ) . ' add_builder-section enabled" data-item_type="enabled" data-item_val="' . esc_attr( $add_settings_key ) . '"><span>' . ucwords( str_replace( '_', ' ', $add_settings_key ) ) . '</span>';
						foreach ( $add_settings as $add_setting ) {
							$name  = ucwords( $add_setting );
							$label = str_replace( '_', ' ', $name );
							echo '<div class="widget-item ' . esc_attr( $name ) . '">' . esc_html( $label ) . '<input class="widget-item" type="hidden" name="add_setting[enabled][' . esc_attr( $add_settings_key ) . '][]" value="' . esc_attr( $add_setting ) . '"></div>';
						}
						echo '</div>';
					}
					?>
					<div class="add_builder-add_section">
						<input id="add_builder_sec_val" class="widget-item" type="text" name="set_add_section" value="">
						<button class="button" id="add_builder_sec_btn"><?php echo esc_html( 'Create Section' ); ?></button>
					</div>
				</div>
				<div class="disabled add_builder-section" data-item_type="disabled">
					<h2><?php echo esc_html__( 'Preset Fields', 'essential-wp-real-estate' ); ?></h2>
					<?php
					$all_enabled_fields = $add_settings_enabled['preset'];

					$this->get_all_fields = Groups::getInstance()->get_groups_data('description');
					$fields = $this->get_all_fields;

					foreach ( $fields as $key => $add_field ) {
						 if(!in_array($key,$all_enabled_fields)){
							$name  = ucwords( $key );
							$label = str_replace( '_', ' ', $name );
							echo '<div class="widget-item ' . esc_attr( $name ) . '">' . esc_html( $label ) . '<input class="widget-item" type="hidden" name="add_setting[disabled][]" value="' . esc_attr( $key ) . '"></div>';
						 }
					}

					// foreach ( $add_settings_disabled as $add_field ) {
					// 	$name  = ucwords( $add_field );
					// 	$label = str_replace( '_', ' ', $name );
					// 	echo '<div class="widget-item ' . esc_attr( $name ) . '">' . esc_html( $label ) . '<input class="widget-item" type="hidden" name="add_setting[disabled][]" value="' . esc_attr( $add_field ) . '"></div>';
					// }
					?>
				</div>
			</div>
		</div>
		<?php
	}
}
new Setting_Add();
