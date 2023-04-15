<?php
use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Admin\MetaBoxes\Components\Groups;

class Setting_Custom_Field {


	use Traitval;

	public function __construct() {
		add_action( 'cl_admin_settings_tab_top_pagelayout_custom_field', array( $this, 'cl_custom_field_builder_setting' ) );
	}

	public function cl_custom_field_builder_setting() {
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
				<div class="add_custom_field-section">
					<h2><?php esc_html_e('Add Custom Fields','essential-wp-real-estate');?></h2>
					<div class="add_custom_field-form">
						<input type="text" name="field_label[]" placeholder="Field Label" value=""/>
						<input type="text" name="field_default_value[]" placeholder="Default Value" value=""/>
						<select name="field_type[]">
							<option value="text"><?php esc_html_e('Text Field','essential-wp-real-estate');?></option>
							<option value="number"><?php esc_html_e('Number Field','essential-wp-real-estate');?></option>
							<option value="url"><?php esc_html_e('URL Field','essential-wp-real-estate');?></option>
							<option value="textarea"><?php esc_html_e('Textarea Field','essential-wp-real-estate');?></option>
						</select>
						<a href="javascript:void(0)" id="add_custom_field"><?php esc_html_e('Add Field','essential-wp-real-estate');?></a>
					</div>
					<div class="added-field-list">
						<?php 
							$fields = get_option( 'cl_custom_meta_settings' );
							if(!empty($fields)){
						?>
							<?php 
							foreach($fields as $key => $field){

									$field_name = str_replace(' ', '', $field['field_label']);

									$field_label = $field['field_label'];
									$field_default_value = $field['field_default_value'];
									$field_type = $field['field_type'];
								?>
								<?php if($field_type == 'textarea'){ ?>
									<div class="custom-field-item">
										<label><?php echo esc_html($field_label);?></label>
										<textarea name="<?php echo esc_attr($field_name);?>" disabled><?php echo esc_attr( $field_default_value ) ?></textarea>
										<a href="javascript:void(0)" class="delete-field" data-field_id="<?php echo esc_attr($key);?>" data-field_name="<?php echo esc_attr($field_name);?>"><?php esc_html_e('Delete','essential-wp-real-estate');?></a>
									</div>
								<?php }else{ ?>
									<div class="custom-field-item">
										<label><?php echo esc_html($field_label)?></label>
										<input type="<?php echo esc_attr($field_type);?>" name="<?php echo esc_attr($field_name);?>" value="<?php echo esc_attr( $field_default_value ) ?>" disabled>
										<a href="javascript:void(0)" class="delete-field" data-field_id="<?php echo esc_attr($key);?>" data-field_name="<?php echo esc_attr($field_name);?>"><?php esc_html_e('Delete','essential-wp-real-estate');?></a>
									</div>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</div>
				</div>

			</div>
		</div>
		<?php
	}
}
new Setting_Custom_Field();

