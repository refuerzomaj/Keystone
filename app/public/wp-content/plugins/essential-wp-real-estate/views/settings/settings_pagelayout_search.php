<?php
use Essential\Restate\Traitval\Traitval;

class Setting_search {

	use Traitval;

	public function __construct() {
		add_action( 'cl_admin_settings_tab_top_pagelayout_search', array( $this, 'cl_search_builder_setting' ) );
	}

	public function cl_search_builder_setting() {
		$search_fields = array(
			'enabled'  => array(
				'search',
				'location',
			),
			'disabled' => array(
				'price',
				'price_min',
				'price_max',
				'property_status',
				'listings_property',
			),
		);

		$search_settings = get_option( 'cl_search_builder_setting', array() );
		if ( empty( $search_settings ) || $search_settings == 'null' ) {
			$search_settings          = $search_fields;
			$search_settings_enabled  = $search_fields['enabled'];
			$search_settings_disabled = $search_fields['disabled'];
		} else {
			$search_settings          = json_decode( $search_settings, true );
			$search_settings_enabled  = isset( $search_settings['enabled'] ) ? $search_settings['enabled'] : array();
			$search_settings_disabled = isset( $search_settings['disabled'] ) ? $search_settings['disabled'] : array();
		}
		?>
		<div class="search_builder_container">
			<div class="div_element_main">
				<h3><?php echo esc_html__( 'Customize the search form for this listing type', 'essential-wp-real-estate' ); ?></h3>
				<p><a target="_blank" href="https://doc.smartdatasoft.net"><?php echo esc_html__( 'Need help?', 'essential-wp-real-estate' ); ?></a></p>
			</div>
			<div class="search_builder_row">
				<div class="enabled search_builder-section" data-item_type="enabled">
					<h2><?php echo esc_html__( 'Enabled Fields', 'essential-wp-real-estate' ); ?></h2>
					<?php
					foreach ( $search_settings_enabled as $search_setting ) {
						$name  = ucwords( $search_setting );
						$label = str_replace( '_', ' ', $name );
						echo '<div class="widget-item ' . esc_attr( $name ) . '">' . esc_html( $label ) . '<input class="widget-item" type="hidden" name="search_setting[enabled][]" value="' . esc_attr( $search_setting ) . '"></div>';
					}
					?>
				</div>
				<div class="disabled search_builder-section" data-item_type="disabled">
					<h2><?php echo esc_html__( 'Disabled Fields', 'essential-wp-real-estate' ); ?></h2>
					<?php
					foreach ( $search_settings_disabled as $search_field ) {
						$name  = ucwords( $search_field );
						$label = str_replace( '_', ' ', $name );
						echo '<div class="widget-item ' . esc_attr( $name ) . '">' . esc_html( $label ) . '<input class="widget-item" type="hidden" name="search_setting[disabled][]" value="' . esc_attr( $search_field ) . '"></div>';
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}
}
new Setting_search();
