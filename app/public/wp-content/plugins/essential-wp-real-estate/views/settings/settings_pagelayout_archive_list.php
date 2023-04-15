<?php
use Essential\Restate\Traitval\Traitval;

class Setting_Archive_List {


	use Traitval;

	public function __construct() {
		add_action( 'cl_admin_settings_tab_top_pagelayout_archive_list', array( $this, 'cl_archive_builder_setting' ) );
	}

	private function field_gen( $param, $parent_pos, $child_pos ) {
		$input_html = '<input class="widget-item" type="hidden" name="archive_setting[' . esc_attr( $parent_pos ) . '][' . esc_attr( $child_pos ) . '][]" value="">';
		if ( array_key_exists( $parent_pos, $param ) ) {
			if ( array_key_exists( $child_pos, $param[ $parent_pos ] ) ) {
				foreach ( $param[ $parent_pos ][ $child_pos ] as $archive_field ) {
					if ( ! empty( $archive_field ) ) {
						$name        = ucwords( $archive_field );
						$label       = str_replace( '_', ' ', $name );
						$input_html .= '<div class="widget-item ' . esc_attr( $name ) . '"><span class="dashicons dashicons-admin-links"></span>' . esc_html( $label ) . '<input class="widget-item" type="hidden" name="archive_setting[' . esc_attr( $parent_pos ) . '][' . esc_attr( $child_pos ) . '][]" value="' . esc_attr( $archive_field ) . '"><span class="widget-item-remove dashicons dashicons-no"></span></div>';
					}
				}
			}
		}
		printf( $input_html );
	}

	public function cl_archive_builder_setting() {
		$archive_fields = array(
			'elements' => array(
				'listing_types',
				'listing_status',
				'listing_author',
				'listing_ratings',
				'listing_price',
				'listing_title',
				'listing_content',
				'listing_excerpt',
				'listing_features',
				'listing_location',
				'listing_favourite',
				'listing_compare',
				'listing_view',
				'listing_share',
				'listing_meta_features',
				'listing_address',
			),
		);

		$archive_settings = get_option( 'cl_archive_setting_list_view', array() );

		if ( empty( $archive_settings ) || $archive_settings == 'null' ) {
			$archive_settings          = $archive_fields;
			$archive_settings_elements = $archive_fields['elements'];
		} else {
			$archive_settings          = json_decode( $archive_settings, true );
			$archive_settings_elements = isset( $archive_settings['elements'] ) ? $archive_settings['elements'] : array();
			$archive_settings_elements = $archive_fields['elements'];
		}

		?>
		<div class="archive_builder_container">
			<div class="div_element_main">
				<h3><?php echo esc_html__( 'Customize the Archive page', 'essential-wp-real-estate' ); ?></h3>
				<p><a target="_blank" href="https://doc.smartdatasoft.net"><?php echo esc_html__( 'Need help?', 'essential-wp-real-estate' ); ?></a></p>
			</div>
			<div class="archive_builder_row">
				<div class="elements archive_builder-section" data-item_type="[elements]">
					<?php
					foreach ( $archive_settings_elements as $archive_field ) {
						$name  = ucwords( $archive_field );
						$label = str_replace( '_', ' ', $name );
						echo '<div class="widget-item ' . esc_attr( $name ) . '"><span class="dashicons dashicons-editor-unlink"></span>' . esc_html( $label ) . '<input class="widget-item" type="hidden" name="archive_setting[elements][]" value="' . esc_attr( $archive_field ) . '"><span class="widget-item-remove dashicons dashicons-no"></span></div>';
					}
					?>
				</div>
				<div class="property-listing property-list archive_builder-section enabled">
					<div class="thumbnail-section lazy-section">
						<div data-item_type="thumbnail_section" data-item_val="top_left" class="wperesds-thumb-sec top-left archive_builder-section"><?php $this->field_gen( $archive_settings, 'thumbnail_section', 'top_left' ); ?></div>
						<div data-item_type="thumbnail_section" data-item_val="top_right" class="wperesds-thumb-sec top-right archive_builder-section"><?php $this->field_gen( $archive_settings, 'thumbnail_section', 'top_right' ); ?></div>
						<div data-item_type="thumbnail_section" data-item_val="bottom_left" class="wperesds-thumb-sec bottom-left archive_builder-section"><?php $this->field_gen( $archive_settings, 'thumbnail_section', 'bottom_left' ); ?></div>
						<div data-item_type="thumbnail_section" data-item_val="bottom_right" class="wperesds-thumb-sec bottom-right archive_builder-section"><?php $this->field_gen( $archive_settings, 'thumbnail_section', 'bottom_right' ); ?></div>
						<?php cl_get_template( 'inc/featured-image.php' ); ?>
					</div>
					<div class="content-section">
						<div class="isting-detail-wrapper">
							<div class="listing-short-detail-wrap">
								<div class="_card_list_flex mb-2 archive_builder-section">
									<div data-item_type="content_section" data-item_val="top_left" class="_card_flex_left top_left archive_builder-section"><?php $this->field_gen( $archive_settings, 'content_section', 'top_left' ); ?></div>
									<div data-item_type="content_section" data-item_val="top_right" class="_card_flex_right top_right archive_builder-section"><?php $this->field_gen( $archive_settings, 'content_section', 'top_right' ); ?></div>
								</div>
								<div data-item_type="content_section" data-item_val="main" class="main archive_builder-section"><?php $this->field_gen( $archive_settings, 'content_section', 'main' ); ?></div>
							</div>
							<div class="price-features-wrapper">
								<div data-item_type="content_section" data-item_val="bottom" class="bottom archive_builder-section"><?php $this->field_gen( $archive_settings, 'content_section', 'bottom' ); ?></div>
							</div>
						</div>
						<div class="listing-detail-footer">
							<div data-item_type="footer_section" data-item_val="top" class="footer-flex archive_builder-section"><?php $this->field_gen( $archive_settings, 'footer_section', 'top' ); ?></div>
							<div data-item_type="footer_section" data-item_val="bottom" class="footer-flex archive_builder-section"><?php $this->field_gen( $archive_settings, 'footer_section', 'bottom' ); ?></div>
						</div>
					</div>
				</div>

			</div>
		</div>

		<?php
	}
}
new Setting_Archive_List();
