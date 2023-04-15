<?php
use Essential\Restate\Traitval\Traitval;

use Essential\Restate\Admin\Settings\Archive;


class Setting_archive extends Archive {


	use Traitval;
	private $settings;
	public function __construct() {
		 add_action( 'cl_admin_settings_tab_top_pagelayout_archive', array( $this, 'cl_archive_setting_grid_view' ) );
	}

	function cl_archive_setting_grid_view() {    ?>
		<div class="col-lg-4 col-md-6 col-sm-12  cl_cpt type-cl_cpt status-publish has-post-thumbnail hentry pagelayout_archive_main_div" id="post-167">
			<div class="property-listing property-grid">

				<!-- Thumbnail -->
				<div class="thumbnail-section lazy-section">
					<div class="listing-img-wrapper">

						<?php
						$archive_setting = get_option( 'cl_archive_setting_grid_view', array() );

						if ( empty( $archive_setting ) || $archive_setting == 'null' ) {
							$archive_setting = $this->cl_archive_default_grid_setting();
						} else {
							$archive_setting = json_decode( $archive_setting, true );
						}
						if ( isset( $archive_setting['sectionzero'] ) && count( $archive_setting['sectionzero'] ) > 0 ) {
							$co = 0;
							foreach ( $archive_setting['sectionzero'] as $key => $section ) {
								$co++;
								$str = '';
								echo '<div class="' . esc_attr( $section['class'] ) . ' ' . esc_attr( $key ) . '" data-position="' . esc_attr( $key ) . '"  data-area="sectionzero">';
								foreach ( $section as $keysec => $sectionchild ) {
									$track = false;
									if ( $keysec == 'class' ) {
										continue;
									}

									if ( $sectionchild['active'] ) {
										echo '<div class="div_element_main _warpper_' . esc_attr( $key ) . ' ' . esc_attr( $key ) . '_item"  data-class= "' . esc_attr( $section['class'] ) . '">';
										echo $this->{$keysec . '_archive_setting_html'}( $key, $keysec, 1 . $this->badge_element_inactive_html() );
										echo '</div>';
										$track = true;
									} else {
										$str .= '<div class=" div_element_main _warpper_' . esc_attr( $keysec ) . '">' . Archive::{$keysec . '_archive_setting_html'}( $key, $keysec, 0 ) . $this->badge_element_inactive_html() . '</div>';
										echo '<input class="active_inactive_input ' . esc_attr( $key ) . '" type="hidden" name="archive_setting[sectionzero][' . esc_attr( $key ) . '][' . esc_attr( $keysec ) . '][active]" value="0">';
									}
								}
								echo '<span class="plus-icon fa fa-plus"></span>';
								echo WPERECCP()->admin->settings_instances->cl_listing_modal_container( $str );
								echo '</div>';
								if ( $co == 2 ) {
									echo '<div class="list-img-slide">
									<div class="click">
										<div><a href="#"><img src="' . WPERESDS_ASSETS . '/img/placeholder_light.png' . '" class="img-fluid mx-auto" alt="' . esc_attr__( 'Placeholder', 'essential-wp-real-estate' ) . '" /></a></div>
									</div>
								</div>';
								}
							}
						}
						?>
					</div>
				</div>

				<!-- Content -->
				<div class="content-section">
					<div class="listing-detail-wrapper shortablediv">
						<div class="listing-short-detail-wrap">
							<?php
							unset( $archive_setting['sectionzero'] );

							foreach ( $archive_setting as $setting_name => $setting_val ) {
								$co                = 1;
								$setting_val_class = $setting_val['class'] ?? '';
								unset( $setting_val['class'] );
								echo '<div class="_card_list_flex mb-2 ' . esc_attr( $setting_val_class ) . '">';
								foreach ( $setting_val as $key => $section ) {
									if ( $key == 'block' ) {
										continue;
									}
									$str   = '';
									$class = $section['class'] ?? '';
									echo '<div class="_card_flex_0' . esc_attr( $co ) . ' ' . esc_attr( $key ) . ' ' . esc_attr( $setting_val_class ) . '_item ">';

									if ( count( $section ) !== count( $section, COUNT_RECURSIVE ) ) {
										foreach ( $section as $keysec => $sectionchild ) {
											if ( $keysec != 'class' && $keysec != 'block' ) {
												if ( $sectionchild['active'] ) {
													echo '<div class="div_element_main _warpper_' . esc_attr( $keysec ) . ' ' . esc_attr( $key ) . '_item"  data-class= "' . esc_attr( $class ) . '">';
													echo Archive::{$keysec . '_archive_setting_html'}( 1 ) . $this->badge_element_inactive_html();
													echo '</div>';
												} else {

													$str .= '<div class="div_element_main _warpper_' . esc_attr( $keysec ) . '" data-class= "' . esc_attr( $class ) . '">' . Archive::{$keysec . '_archive_setting_html'}( 0 ) . $this->badge_element_inactive_html() . '</div>';
												}
											}
										}
									} else {
										if ( $key != 'class' && $key != 'block' ) {
											if ( $section['active'] ) {
												echo '<div class="div_element_main _warpper_' . esc_attr( $key ) . '" data-div="' . esc_attr( $key ) . '" data-class= "' . esc_attr( $class ) . '">';
												echo Archive::{$key . '_archive_setting_html'}( 1 ) . $this->badge_element_inactive_html();
												echo '</div>';
											} else {
												$str .= '<div class="div_element_main _warpper_' . esc_attr( $key ) . '" data-class= "' . esc_attr( $class ) . '">' . Archive::{$key . '_archive_setting_html'}( 0 ) . $this->badge_element_inactive_html() . '</div>';
											}
										}
									}
									if ( isset( $section['block'] ) ) {
										echo '<span class="plus-icon fa fa-plus"></span>';
										echo WPERECCP()->admin->settings_instances->cl_listing_modal_container( $str );
									}
									echo '</div>';
									$co++;
								}
								if ( isset( $setting_val['block'] ) ) {
									echo '<span class="plus-icon fa fa-plus"></span>';
									echo WPERECCP()->admin->settings_instances->cl_listing_modal_container( $str );
								}
								echo '</div>';
							}
							?>
						</div>
					</div>
				</div>


			<?php
	}

	function cl_archive_setting_view() {
		$archive_setting = get_option( 'cl_archive_setting', array() );

		if ( empty( $archive_setting ) || $archive_setting == 'null' ) {
			$archive_setting = $this->cl_archive_default_setting();
		} else {
			$archive_setting = json_decode( $archive_setting, true );
		}

		$archive_setting = WPERECCP()->admin->settings_instances->admin_settings_extend( $archive_setting, $this->cl_archive_default_setting() );
		ob_start();
		?>
				<div class="shop-sidebar-content">
				<?php
				foreach ( $archive_setting['elements']  as $key => $elements ) {
					?>
						<div class="faq-filter-item-area">
							<div class="faq-filter-item-title">
								<h2><?php echo esc_html( $key ); ?></h2>
								<input type="hidden" name="archive_setting[elements][<?php echo esc_attr( $key ); ?>]" />
								<input class="activeinactive" type="hidden" name="archive_setting[elements][<?php echo esc_attr( $key ); ?>][enabled]" value="<?php echo esc_attr( $elements['enabled'] ); ?>" />
								<a class="ac-trigger-active" href="javascript:void(0)">
									<i class="dashicons dashicons-yes-alt <?php echo ( $elements['enabled'] == 1 ) ? '' : 'hidethis'; ?>"></i>
									<i class="dashicons dashicons-dismiss <?php echo ( $elements['enabled'] == 0 ) ? '' : 'hidethis'; ?>"></i>
								</a>
								<a class="ac-trigger" href="javascript:void(0)">
									<i class="dashicons dashicons-arrow-down-alt2"></i>
									<i class="dashicons dashicons-arrow-up-alt2 hidethis"></i>
								</a>
								<a class="sortable-handle" href="javascript:void(0)">
									<i class="dashicons dashicons-move"></i>
								</a>
							</div>
							<div class="faq-filter-item-content-area">

								<?php
								foreach ( $elements  as $keyc => $element ) {
									if ( 'enabled' != $keyc ) {
										?>
										<h4><?php echo ucwords( str_replace( '_', ' ', $keyc ) ); ?></h4>
										<input type="checkbox" name="archive_setting[elements][<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $keyc ); ?>]" <?php checked( $element, 1 ); ?> value="<?php echo esc_attr( $element ); ?>" />
										<?php
									}
								}
								?>
							</div>
						</div>
					<?php } ?>
				</div>
		<?php
	}


	function cl_archive_default_setting() {
		 return array(
			 'general'  => array(
				 'style'          => 'style1',
				 'theme_template' => '',
				 'comments'       => 1,
				 'displ'          => '0',
			 ),
			 'elements' => array(
				 'labels'       => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'image'        => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'gallery'      => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'title'        => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'categories'   => array(
					 'enabled'    => 1,
					 'show_title' => 1,
				 ),
				 'price'        => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'tags'         => array(
					 'enabled'    => 1,
					 'show_title' => 1,
				 ),
				 'content'      => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'embed'        => array(
					 'enabled'    => 0,
					 'show_title' => 0,
				 ),
				 'attributes'   => array(
					 'enabled'    => 1,
					 'show_title' => 1,
				 ),
				 'features'     => array(
					 'enabled'    => 1,
					 'show_title' => 1,
				 ),
				 'contact'      => array(
					 'enabled'    => 1,
					 'show_title' => 1,
				 ),
				 'remark'       => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'locations'    => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'address'      => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'map'          => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'availability' => array(
					 'enabled'    => 1,
					 'show_title' => 1,
				 ),
				 'owner'        => array(
					 'enabled'    => 1,
					 'show_title' => 0,
				 ),
				 'share'        => array(
					 'enabled'    => 0,
					 'show_title' => 0,
				 ),
				 'abuse'        => array(
					 'enabled'    => 0,
					 'show_title' => 0,
				 ),
			 ),
		 );
	}
}
	new Setting_archive();
