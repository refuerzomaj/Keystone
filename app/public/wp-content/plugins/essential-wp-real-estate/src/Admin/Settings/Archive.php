<?php
namespace Essential\Restate\Admin\Settings;

use Essential\Restate\Traitval\Traitval;


/**
 * The admin class
 */
class Archive {

	use Traitval;

	public function __construct() {     }
	protected function badge_element_inactive_html() {
		return '<span class="action_box"><li class="element_inactive"><i class="fa fa-times" aria-hidden="true"></i></li><li class="element_active"><i class="fa fa-plus" aria-hidden="true"></i></li></span>';
	}

	protected function badge_archive_setting_html( $a, $b, $n = 0 ) {

		$str = '<input class="active_inactive_input ' . esc_attr( $a ) . '" type="hidden" name="archive_setting[sectionzero][' . esc_attr( $a ) . '][' . esc_attr( $b ) . '][active]" value="' . esc_attr( $n ) . '">';

		return ' <span class="_list_blickes _exlio" data-type="badge"><input type="hidden" name="archive_setting[sectionzero][' . esc_attr( $a ) . '][' . esc_attr( $b ) . ']">' . $str . 'Badge</span>';
	}

	protected function types_archive_setting_html( $a ) {

		return '<span class="single-element _list_blickes _netork" data-type="types"><input type="hidden" name="archive_setting[sectionone][sectiononeleft][types]"><input class="active_inactive_input " type="hidden" name="archive_setting[sectionone][sectiononeleft][types][active]" value="' . esc_attr( $a ) . '">'.esc_html__('Types','essential-wp-real-estate').'</span>';
	}

	protected function price_archive_setting_html( $a ) {
		return '<div class="single-element"><input type="hidden" name="archive_setting[sectionone][sectiononeright][price]"><input class="active_inactive_input " type="hidden" name="archive_setting[sectionone][sectiononeright][price]active]" value="' . esc_attr( $a ) . '">'.esc_html__('Price','essential-wp-real-estate').'</div>';
	}

	protected function title_archive_setting_html( $a ) {
		return '<div class="single-element"><input type="hidden" name="archive_setting[sectiontwo][title]"><input class="active_inactive_input " type="hidden" name="archive_setting[sectiontwo][title][active]" value="' . esc_attr( $a ) . '">'.esc_html__('Title','essential-wp-real-estate').'</div>';
	}

	protected function excerpt_archive_setting_html( $a ) {
		return '<div class="single-element"><input type="hidden" name="archive_setting[sectiontwo][excerpt]"><input class="active_inactive_input " type="hidden" name="archive_setting[sectiontwo][excerpt][active]" value="' . esc_attr( $a ) . '">'.esc_html__('Excerpt','essential-wp-real-estate').'</div>';
	}

	protected function features_archive_setting_html( $a ) {
		return '<div class="single-element list-fx-features"><input type="hidden" name="archive_setting[sectiontwo][features]"><input class="active_inactive_input " type="hidden" name="archive_setting[sectiontwo][features][active]" value="' . esc_attr( $a ) . '">'.esc_html__('Features','essential-wp-real-estate').'</div>';
	}
	protected function meta_features_archive_setting_html( $a ) {
		return '<div class="single-element list-fx-features"><input type="hidden" name="archive_setting[sectiontwo][meta_features]"><input class="active_inactive_input " type="hidden" name="archive_setting[sectiontwo][meta_features][active]" value="' . esc_attr( $a ) . '">'.esc_html__('Meta Features','essential-wp-real-estate').'</div>';
	}
	protected function location_archive_setting_html( $a ) {
		return '<div class="footer-flex"><input type="hidden" name="archive_setting[sectionfive][sectionfiveleft][location]"><input class="active_inactive_input " type="hidden" name="archive_setting[sectionfive][sectionfiveleft][location][active]" value="' . esc_attr( $a ) . '"><div class="single-element foot-location">'.esc_html__('Location','essential-wp-real-estate').'</div></div>';
	}

	protected function favourite_archive_setting_html( $a ) {
		return '<span class="selio_style"><a href="#" data-balloon-nofocus data-balloon-pos="up" aria-label="Save property">
					<div class="prt_saveed_12lk">
					<input type="hidden" name="archive_setting[sectionfive][sectionfiveright][favourite]">
					<input class="active_inactive_input " type="hidden" name="archive_setting[sectionfive][sectionfiveright][favourite][active]" value="' . esc_attr( $a ) . '">
						<i class="fas fa-heart" aria-hidden="true"></i>
					</div>
				</a></span>';
	}

	protected function compare_archive_setting_html( $a ) {
		return '<span class="selio_style"><a href="#" data-balloon-nofocus data-balloon-pos="up" aria-label="Compare property">
					<div class="prt_saveed_12lk">
					<input type="hidden" name="archive_setting[sectionfive][sectionfiveright][compare]">
					<input class="active_inactive_input " type="hidden" name="archive_setting[sectionfive][sectionfiveright][compare][active]" value="' . esc_attr( $a ) . '">
						<i class="fas fa-random" aria-hidden="true"></i>
					</div>
				</a></span>';
	}
	protected function view_archive_setting_html( $a ) {
		return '<span class="selio_style"><a href="#" data-balloon-nofocus data-balloon-pos="up" aria-label="View Property">
					<div class="prt_saveed_12lk">
					<input type="hidden" name="archive_setting[sectionfive][sectionfiveright][view]">
					<input class="active_inactive_input " type="hidden" name="archive_setting[sectionfive][sectionfiveright][view][active]" value="' . esc_attr( $a ) . '">
						<i class="fas fa-arrow-right" aria-hidden="true"></i>
					</div>
				</a></span>';
	}

	protected function warpper_archive_setting_html_start( $a ) {
		return '<div class="' . esc_attr( $a ) . '">';
	}

	protected function warpper_archive_setting_html_stop() {
		return '</div>';
	}
	public function cl_archive_default_grid_setting() {
		 return array(
			 'sectionzero' => array(
				 'topleft'     => array(
					 'badge' => array(
						 'active'   => 1,
						 'dropable' => 'cornerarea',
					 ),
					 'class' => 'cornerarea top-left',
				 ),
				 'topright'    => array(
					 'badge' => array(
						 'active'   => 0,
						 'dropable' => 'cornerarea',
					 ),
					 'class' => 'cornerarea top-right',
				 ),
				 'bottomleft'  => array(
					 'badge' => array(
						 'active'   => 0,
						 'dropable' => 'cornerarea',
					 ),
					 'class' => 'cornerarea bottom-left',
				 ),
				 'bottomright' => array(
					 'badge' => array(
						 'active'   => 0,
						 'dropable' => 'cornerarea',
					 ),
					 'class' => 'cornerarea bottom-right',
				 ),
			 ),
			 'sectionone'  => array(
				 'sectiononeleft'  => array(
					 'types' => array(
						 'active'   => 1,
						 'dropable' => 'sectionone',
					 ),
					 'class' => 'sectionone',
					 'block' => true,

				 ),
				 'sectiononeright' => array(
					 'price' => array(
						 'active'   => 1,
						 'dropable' => 'sectionone',
					 ),
					 'class' => 'sectionone',
					 'block' => true,

				 ),
				 'class'           => 'sectionone',
			 ),
			 'sectiontwo'  => array(
				 'title'    => array(
					 'active' => 1,
				 ),
				 'excerpt'  => array(
					 'active' => 1,
				 ),
				 'features' => array(
					 'active' => 1,
				 ),
				 'meta_features'  => array(
					'active' => 0,
				 ),
				 'class'    => 'sectiontwo',
				 'block'    => true,
			 ),
			 'sectionfive' => array(
				 'sectionfiveleft'  => array(
					 'location' => array(
						 'active'   => 1,
						 'dropable' => 'sectionfive',
					 ),
					 'class'    => 'sectionfive',
					 'block'    => true,
				 ),
				 'sectionfiveright' => array(
					 'favourite' => array(
						 'active'   => 1,
						 'dropable' => 'sectionfive',
					 ),
					 'compare'   => array(
						 'active'   => 1,
						 'dropable' => 'sectionfive',
					 ),
					 'view'      => array(
						 'active'   => 0,
						 'dropable' => 'sectionfive',
					 ),
					 'class'     => 'sectionfive',
					 'block'     => true,
				 ),
				 'class'            => 'sectionfive',
			 ),

		 );
	}
}
