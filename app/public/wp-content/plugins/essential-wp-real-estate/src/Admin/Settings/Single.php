<?php
namespace Essential\Restate\Admin\Settings;

use Essential\Restate\Common\Provider\Provider;

use Essential\Restate\Traitval\Traitval;


/**
 * The admin class
 */
class Single {

	use Traitval;

	private $extrametas;
	private $template_data;

	public function __construct() {     }

	protected function single_thumbnail_type_html() {
		return '<div class="mfp-gallery"><img src="' . WPERESDS_ASSETS . '/img/placeholder-single.png" class="img-fluid mx-auto" alt="' . esc_attr__( 'Placeholder', 'essential-wp-real-estate' ) . '"></div>';
	}

	protected function single_gallery_type_html() {
		 return '<div class="mfp-gallery"><img src="' . WPERESDS_ASSETS . '/img/placeholder-gallery.png" class="img-fluid mx-auto" alt="' . esc_attr__( 'Placeholder', 'essential-wp-real-estate' ) . '"></div>';
	}

	protected function action_element_html( $sc, $s, $n, $v ) {
		return '<input type="hidden" name="single_setting[' . esc_attr( $sc ) . '][' . esc_attr( $s ) . '][' . esc_attr( $n ) . ']" value="' . esc_attr( $v ) . '">';
	}

	protected function single_title_type_html() {
		return '<div class="single-element">' . esc_html__( 'Title', 'essential-wp-real-estate' ) . '</div>';
	}

	protected function single_features_type_html() {
		return '<div class="single-element">' . esc_html__( 'Types', 'essential-wp-real-estate' ) . '</div>';
	}

	protected function single_location_type_html() {
		return '<div class="single-element">' . esc_html__( 'Location', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_address_type_html() {
		return '<div class="single-element">' . esc_html__( 'Address', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_ratings_type_html() {
		return '<div class="single-element">' . esc_html__( 'Rating', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_publishdate_type_html() {
		return '<div class="single-element">' . esc_html__( 'Publish Date', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_viewcount_type_html() {
		return '<div class="single-element">' . esc_html__( 'View Count', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_status_type_html() {
		return '<div class="single-element">' . esc_html__( 'Status', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_price_type_html() {
		return '<div class="single-element">' . esc_html__( 'Price', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_meta_features_type_html() {
		return '<div class="single-element">' . esc_html__( 'Meta Features', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_share_type_html() {
		return '<div class="single-element">' . esc_html__( 'Social Share', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_cart_type_html() {
		return '<div class="single-element">' . esc_html__( 'Cart', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_favourite_type_html() {
		return '<div class="single-element">' . esc_html__( 'Favourite', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_compare_type_html() {
		return '<div class="single-element">' . esc_html__( 'Compare', 'essential-wp-real-estate' ) . '</div>';
	}
	protected function single_abuse_type_html() {
		return '<div class="single-element">' . esc_html__( 'Report Abuse', 'essential-wp-real-estate' ) . '</div>';
	}

	public function __call( $function, $args ) {
		$val = $args[0];
		if ( is_array( $args[0] ) ) {
			$val = $args[0]['active'];
		}

		if ( array_key_exists( $function, $this->extrametas ) ) {
			$name = $this->extrametas[ $function ];
			return '<div class="single-element">' . esc_html( $name ) . '</div><input type="hidden" name="single_setting[extrasection][' . esc_attr( $function ) . ']" value="0"><input type="hidden" name="single_setting[extrasection][' . esc_attr( $function ) . '][active]" value="' . esc_attr( $val ) . '">';
		} elseif ( array_key_exists( $function, $this->template_data ) ) {
			$name = $this->template_data[ $function ];
			return '<div class="single-element">' . esc_html( $name ) . '</div><input type="hidden" name="single_setting[templatesection][' . esc_attr( $function ) . ']" value="0"><input type="hidden" name="single_setting[templatesection][' . esc_attr( $function ) . '][active]" value="' . esc_attr( $val ) . '">';
		}
	}

	public function single_default_layout_setting() {
		$this->extrametas    = Provider::getInstance()->get_group_headings();
		$this->template_data = Provider::getInstance()->get_single_temp_data();

		$extrasection = array();
		foreach ( $this->extrametas as $key => $extra ) {
			if ( $key == 'description' ) {
				$extrasection['extrasection'][ $key ] = array(
					'active'   => 1,
					'dropable' => 'metasection',
				);
			} else {
				$extrasection['extrasection'][ $key ] = array(
					'active'   => 0,
					'dropable' => 'metasection',
				);
			}
		}

		$extrasection['extrasection']['class'] = 'extrasection';

		$template_section = array();
		foreach ( $this->template_data as $key => $template ) {
			$template_section['templatesection'][ $key ] = array(
				'active'   => 1,
				'dropable' => 'tempsection',
			);
		}
		$template_section['templatesection']['class'] = 'templatesection';

		return array(
			'sectionthumbnail' => array(
				'leftmeta' => array(
					'thumbnail' => array(
						'active'   => 1,
						'dropable' => 'thumbarea',
					),
					'class'     => 'leftmeta',
				),
				'class'    => 'sectionthumbnail',
			),
			'metasection'      => array(
				'leftmeta'  => array(
					'features'      => array(
						'active'   => 1,
						'dropable' => 'metasection',
					),
					'title'         => array(
						'active'   => 1,
						'dropable' => 'metasection',
					),
					'location'      => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'address'       => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'ratings'       => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'publishdate'   => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'viewcount'     => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'status'        => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'price'         => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'meta_features' => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'share'         => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'cart'          => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'abuse'         => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'class'         => 'leftmeta',
					'block'         => true,
				),
				'rightmeta' => array(
					'features'  => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'title'     => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'location'  => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'favourite' => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'compare'   => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'share'     => array(
						'active'   => 1,
						'dropable' => 'metasection',
					),
					'cart'      => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'abuse'     => array(
						'active'   => 0,
						'dropable' => 'metasection',
					),
					'class'     => 'rightmeta',
					'block'     => true,
				),
				'class'     => 'metasection',
			),
		) + $extrasection + $template_section;
	}
}
