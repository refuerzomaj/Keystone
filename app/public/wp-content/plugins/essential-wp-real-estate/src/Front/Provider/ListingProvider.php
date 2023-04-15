<?php
namespace Essential\Restate\Front\Provider;

use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Front\Provider\Markups;
use Essential\Restate\Front\Models\Listings;

class ListingProvider extends Listings {


	use Traitval;

	public $markups;
	public $listing;

	protected function __construct() {  }

	public function set_listing_object() {
		$this->set_id();
		$this->markups = Markups::getInstance();
		$this->listing = $this->get_details();
	}



	/**
	 * get_listing_url gets the url of the listing post.
	 *
	 * @param  mixed $url
	 * @return void
	 */
	protected function get_listing_url() {
		// -- Variable declaration
		$url   = $this->get_url( get_the_ID() );
		$title = $this->get_title() ?? get_the_title();
		$class = 'prt-link-detail';
		$id    = 'listing-' . get_the_ID();

		$target = '_self';

		// -- attribute filter addition
		$link_attr = apply_filters(
			$this->prefix . 'permalink_attr',
			array(
				'url'    => $url,
				'title'  => $title,
				'id'     => $id,
				'class'  => $class,
				'target' => $target,
			)
		);

		// -- permalink html markup
		$permalink_html = '<a href="' . esc_url( $link_attr['url'] ) . '" id="' . esc_attr( $link_attr['id'] ) . '" class="wperesds-link ' . esc_attr( $link_attr['class'] ) . '" target="' . esc_attr( $link_attr['target'] ) . '" >' . esc_html( $link_attr['title'] ) . '</a>';

		return apply_filters( $this->prefix . 'permalink_html', $permalink_html );
	}

	/**
	 * get_listing_title get the title of the current post.
	 *
	 * @param  mixed $post_id
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function get_listing_title() {
		return apply_filters( $this->prefix . 'archive_title_html', '<h4 class="listing-name">' . $this->get_listing_url() . '</h4>' );
	}

	/**
	 * get_listing_content get the content of the current post.
	 *
	 * @param  mixed $post_id
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function get_listing_content() {
		 return apply_filters( $this->prefix . 'archive_content_html', '<p>' . $this->get_content( get_the_ID() ) . '</p>' );
	}

	/**
	 * get_listing_excerpt get the excerpt of the current post.
	 *
	 * @param  mixed $post_id
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function get_listing_excerpt() {
		if ( $this->get_excerpt( get_the_ID() ) ) {
			return apply_filters( $this->prefix . 'archive_excerpt_html', '<p>' . $this->get_excerpt( get_the_ID() ) . '</p>' );
		} else {
			return;
		}
	}

	/**
	 * get_listing_author get the excerpt of the current post.
	 *
	 * @param  mixed $post_id
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function get_listing_author() {
		return cl_get_avat( 30 );
	}
	/**
	 * get_listing_views get the excerpt of the current post.
	 *
	 * @param  mixed $post_id
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function get_listing_views() {
		$key     = 'wperesds_post_views_count';
		$post_id = get_the_ID();
		$count   = (int) get_post_meta( $post_id, $key, true );
		$count++;
		update_post_meta( $post_id, $key, $count );
		$count = get_post_meta( get_the_ID(), 'wperesds_post_views_count', true );
		return $count;
	}

	public function get_listing_terms( $value = 'listings_property' ) {
		return get_the_terms( get_the_ID(), $value );
	}

	/**
	 * get_listing_pricing get the excerpt of the current post.
	 *
	 * @param  mixed $post_id
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function get_listing_pricing() {
		$pricing_range = get_post_meta( get_the_ID(), 'wperesds_pricing_range', true );
		$price_suffix = get_post_meta( get_the_ID(), 'wperesds_price_suffix', true );

		if(!empty($pricing_range)){
			if(!empty($price_suffix) && is_single()){
				return '<div class="listing-price-range">'.apply_filters( $this->prefix . 'archive_pricing_html', '<h6 class="listing-card-info-price">' . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->listing_provider->get_meta_data( 'wperesds_pricing' ) ) ) . '<sub>/'.$price_suffix.'</sub></h6>' ) . ' - '. apply_filters( $this->prefix . 'archive_pricing_html', '<h6 class="listing-card-info-price">' . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->listing_provider->get_meta_data( 'wperesds_pricing_range' ) ) ) . '<sub>/'.$price_suffix.'</sub></h6>' ).'</div>';
			}else{
				return '<div class="listing-price-range">'.apply_filters( $this->prefix . 'archive_pricing_html', '<h6 class="listing-card-info-price">' . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->listing_provider->get_meta_data( 'wperesds_pricing' ) ) ) . '</h6>' ) .' - '. apply_filters( $this->prefix . 'archive_pricing_html', '<h6 class="listing-card-info-price">' . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->listing_provider->get_meta_data( 'wperesds_pricing_range' ) ) ) . '</h6>' ).'</div>';
			}
		}else{
			if(!empty($price_suffix) && is_single()){
				return apply_filters( $this->prefix . 'archive_pricing_html', '<h6 class="listing-card-info-price">' . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->listing_provider->get_meta_data( 'wperesds_pricing' ) ) ) . '<sub>/'.$price_suffix.'</sub></h6>' );
			}else{
				return apply_filters( $this->prefix . 'archive_pricing_html', '<h6 class="listing-card-info-price">' . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->listing_provider->get_meta_data( 'wperesds_pricing' ) ) ) . '</h6>' );
			}
		}
		
	}

	public function get_listing_types() {
		$term_lists = WPERECCP()->front->listing_provider->get_listing_terms( 'listings_property' );
		if ( ! empty( $term_lists ) ) {
			foreach ( $term_lists as $key => $value ) {
				echo '<span class="_list_blickes types">' . esc_html( $value->name ) . '</span>';
			}
		}
	}
	public function get_listing_status() {
		$term_lists = WPERECCP()->front->listing_provider->get_listing_terms( 'listing_status' );
		if ( ! empty( $term_lists ) ) {
			foreach ( $term_lists as $key => $value ) {
				echo '<span class="_list_blickes types">' . esc_html( $value->name ) . '</span>';
			}
		}
	}
	public function get_listing_location() {
		$term_list = wp_get_post_terms( get_the_id(), 'listing_location', array( 'fields' => 'ids' ) );
		if ( $term_list ) {
			?>
			<div class="foot-location">
				<i class="fas fa-map-marker-alt" aria-hidden="true"></i>
			<?php foreach ( $term_list as $term ) { 
				$term_data    = get_term_by('id', $term, 'listing_location');
				$term_url   = get_term_link($term, 'listing_location');
				?>
				<a href="<?php echo esc_url( $term_url ); ?>" class="theme-cl"><?php echo esc_html( $term_data->name ); ?></a>
				<?php } ?>
			</div>
			<?php
		}
	}
	public function get_listing_address() {
		 $address_data = WPERECCP()->front->listing_provider->get_meta_data( 'wperesds_address' );
		if ( $address_data ) {
			echo '<div class="foot-location"><i class="fas fa-map-marker-alt"></i>' . WPERECCP()->front->listing_provider->get_meta_data( 'wperesds_address' ) . '</div>';
		}
	}
	public function get_listing_ratings() {
		$average = $this->get_average_rate( get_the_ID() );
		if ( ! empty( $average ) ) {
			if ( $average <= 3 ) {
				$rate_output = 'bad';
			} elseif ( $average <= 4 ) {
				$rate_output = 'poor';
			} else {
				$rate_output = 'good';
			}
			echo '<div class="foot-rates"><span class="elio_rate ' . esc_attr( $rate_output ) . '">' . esc_html( $average ) . '</span><div class="_rate_stio">';
			$averageRounded = ceil( $average );
			if ( $averageRounded ) {
				$active_comment_rate = $averageRounded;
				for ( $x = 1; $x <= $active_comment_rate; $x++ ) {
					echo '<i class="fa fa-star filled"></i>';
				}
				$inactive_comment_rate = 5 - $active_comment_rate;
				if ( $inactive_comment_rate > 0 ) {
					for ( $x = 1; $x <= $inactive_comment_rate; $x++ ) {
						echo '<i class="fa fa-star"></i>';
					}
				}
			}
			echo '</div><span class="reviews_text">(';
			 comments_number();
			echo ')</span></div>';
		}
	}
	public function get_listing_favourite() {
		global $current_user;
		if ( ! is_user_logged_in() ) {
			$output = '<span class="selio_style"><a href="' . esc_url( get_page_link( cl_admin_get_option( 'login_redirect_page' ) ) ) . '" data-balloon-nofocus data-balloon-pos="up" aria-label="Save property" class="prt_saveed_12lk"><i class="fas fa-heart"></i></a></span>';
		} else {
			$user_meta = get_user_meta( $current_user->ID, '_favorite_posts' );
			if ( in_array( get_the_ID(), $user_meta ) ) {
				$output = '<span class="selio_style"><a href="javascript:void(0)" data-balloon-nofocus data-balloon-pos="up" aria-label="Save property" data-userid="' . esc_attr( $current_user->ID ) . '" data-postid="' . esc_attr( get_the_ID() ) . '" class="cl_favorite_item add-to-favorite prt_saveed_12lk" id="like_listing' . get_the_ID() . '"><i class="fas fa-heart"></i></a></span>';
			} else {
				$output = '<span class="selio_style"><a href="javascript:void(0)" data-balloon-nofocus data-balloon-pos="up" aria-label="Save property" data-userid="' . esc_attr( $current_user->ID ) . '" data-postid="' . esc_attr( get_the_ID() ) . '" class="add-to-favorite prt_saveed_12lk" id="like_listing' . esc_attr( get_the_ID() ) . '"><i class="fas fa-heart"></i></a></span>';
			}
		}

		echo apply_filters( 'cl_listing_favourite', $output );
	}
	public function get_listing_compare() {
		$output = '<span class="selio_style"><a href="javascript:void(0)" data-postid="' . esc_attr( get_the_ID() ) . '" data-balloon-nofocus data-balloon-pos="up" aria-label="Compare property" class="add-to-compare prt_saveed_12lk"><i class="fas fa-random"></i></a></span>';
		echo apply_filters( 'cl_listing_compare', $output );
	}

	public function get_listing_meta_features() {
		$beds = get_post_meta( get_the_id(), 'wperesds_beds', true );
		$bath = get_post_meta( get_the_id(), 'wperesds_bath', true );
		$area = get_post_meta( get_the_id(), 'wperesds_area', true );
		if ( $beds == 1 ) {
			$beds_txt = __( 'Bed', 'essential-wp-real-estate' );
		} else {
			$beds_txt = __( 'Beds', 'essential-wp-real-estate' );
		}
		if ( $bath == 1 ) {
			$bath_txt = __( 'Bath', 'essential-wp-real-estate' );
		} else {
			$bath_txt = __( 'Baths', 'essential-wp-real-estate' );
		}
		?>
		<div class="price-features-wrapper">
			<div class="list-fx-features">
			<div class="listing-card-info-icon">
			<div class="inc-fleat-icon">
			<img src="<?php echo WPERESDS_ASSETS . '/img/bed.svg'; ?>" width="13" alt="<?php esc_attr( 'bed', 'essential-wp-real-estate' ); ?>">
			</div><?php echo esc_html( $beds . ' ' . $beds_txt ); ?></div>
			<div class="listing-card-info-icon">
			<div class="inc-fleat-icon">
			<img src="<?php echo WPERESDS_ASSETS . '/img/bathtub.svg'; ?>" width="13" alt="<?php esc_attr( 'bath', 'essential-wp-real-estate' ); ?>">
			</div><?php echo esc_html( $bath . ' ' . $bath_txt ); ?></div>
			<div class="listing-card-info-icon">
			<div class="inc-fleat-icon">
			<img src="<?php echo WPERESDS_ASSETS . '/img/move.svg'; ?>" width="13" alt="<?php esc_attr( 'sqft', 'essential-wp-real-estate' ); ?>">
			</div><?php echo esc_html( $area ) . ' ' . esc_html__( 'sqft', 'essential-wp-real-estate' ); ?></div>
			</div>
		</div>
		<?php
	}
	/**
	 * show_thumb shows the thumbnail for listing
	 *
	 * @param  mixed $size
	 * @param  mixed $class
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function show_thumb( $size = 'medium', $class = '' ) {

		$arg              = array();
		$arg['img_size']  = apply_filters( $this->prefix . 'archive_thumbnail_size', $size );
		$arg['img_class'] = apply_filters( $this->prefix . 'archive_thumbnail_class', $class );
		$output           = $this->output_thumb( $arg );
		if ( ! $output ) {
			// put condition from settings if wants to show default thumb
			$this->show_default_thumb();
		} else {
			echo '' . $output;
		}
	}

	public function show_default_thumb() {
		$src = $this->get_default_thumb_src();
		// echo default thumb source
	}

	public function get_default_thumb_src() {
		// get the default thumb src
	}

	/**
	 * get_meta_data gets the meta data of the post.
	 *
	 * If the post_id is not set, it will return the current post's meta data.
	 *
	 * @param  mixed $key
	 * @param  mixed $post_id
	 * @return void
	 */
	public function get_meta_data( $key = '', $post_id = null, $single = true ) {
		/**
		 * Check if there are any post id. if not it will get the current post id.
		 */
		if ( empty( $post_id ) ) {
			$post_id = $this->get_id();
		}
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}
		/**
		 * default get_post_meta function to get the post meta.
		 */
		$meta_data = get_post_meta( $post_id, $key, $single );
		return $meta_data;
	}


	/**
	 * get_gen_ted_link get all the layout list and generate the link to change the layout
	 *
	 * @return void
	 */
	public function get_gen_ted_link( $params = array() ) {
		// -- Defining generated link
		$generated_link = array();
		$state          = null;
		$get_link       = ! empty( $_SERVER['REQUEST_URI'] ) ? esc_url( cl_sanitization( $_SERVER['REQUEST_URI'] ) ) : '';
		$queryString    = cl_sanitization( $_SERVER['QUERY_STRING'] );
		parse_str( $queryString, $arguments );

		foreach ( $params as $key => $param ) {

			if ( isset( $arguments[ $param['type'] ] ) && $key == $arguments[ $param['type'] ] ) {
				$state = 'active';
			} else {
				$state = null;

			}
			$default        = '';
			$default_layout = cl_admin_get_option( 'default_layout', 'grid' );

			if ( ! isset( $arguments[ $param['type'] ] ) && $key == $default_layout ) {
				$default = 'active';
			}

			$generated_link[ $key ] = array(
				'link'    => add_query_arg( array( $param['type'] => $key ) ),
				'name'    => $param['name'],
				'icon'    => $param['icon'],
				'active'  => $state,
				'default' => $default,
			);
		}
		return $generated_link;
	}

	/**
	 * render_loop_section
	 *
	 * @param  mixed $sections
	 * @param  mixed $before
	 * @param  mixed $after
	 * @return void
	 */

	public function render_loop_sections( $sections, $before = '', $after = '' ) {
		if ( ! empty( $sections ) ) {
			foreach ( $sections as $section ) {
				printf( $before );
				do_action( $this->prefix . $section );
				// $this->render_card_field($section);
				printf( $after );
			}
		}
	}

	public function single_listing_section() {
		// -- ['type'=>'default'] -> value like default WordPress fields as description title tags
		// -- ['type'=>'other'] -> meta values

		$this->section_data = array(
			array(
				'type'   => 'general',
				'id'     => 1,
				'label'  => 'About Listings',
				'blocks' => array(
					'description' => array(
						'name' => 'description',
						'type' => 'default',
					),
				),
			),
			array(
				'type'   => 'general',
				'id'     => 2,
				'label'  => 'Advance Features',
				'blocks' => array(
					'features' => array(
						'name' => 'features',
						'key'  => 'wperesds_features',
						'type' => 'other',
					),
				),
			),
			array(
				'type'   => 'general',
				'id'     => 3,
				'label'  => 'Ameneties',
				'blocks' => array(
					'ameneties' => array(
						'name' => 'ameneties',
						'key'  => 'wperesds_ameneties',
						'type' => 'other',
					),
				),
			),
			array(
				'type'   => 'general',
				'id'     => 3,
				'label'  => 'Listing Video',
				'blocks' => array(
					'ameneties' => array(
						'name' => 'video',
						'key'  => 'wperesds_video',
						'type' => 'other',
					),
				),
			),
			array(
				'type'   => 'general',
				'id'     => 3,
				'label'  => 'Floor Plan',
				'blocks' => array(
					'ameneties' => array(
						'name' => 'floor',
						'key'  => 'wperesds_floor',
						'type' => 'other',
					),
				),
			),
		);

		return $this->section_data;
	}

	/**
	 * cl_sorter_options_data function returns the array of the sorter options.
	 *
	 * @return void
	 */
	public function cl_sorter_options_data() {
		$this->sorter_options = array(
			'date__desc' => array(
				'name' => __('Latest', 'essential-wp-real-estate'),
				'type' => 'sort',
				'icon' => 'fas fa-caret-right',
			),
			'date__asc' => array(
				'name' => __('Oldest', 'essential-wp-real-estate'),
				'type' => 'sort',
				'icon' => 'fas fa-caret-right',
			),
			'title__asc' => array(
				'name' => __('Title Ascending', 'essential-wp-real-estate'),
				'type' => 'sort',
				'icon' => 'fas fa-caret-right',
			),
			'title__desc' => array(
				'name' => __('Title Descending', 'essential-wp-real-estate'),
				'type' => 'sort',
				'icon' => 'fas fa-caret-right',
			),
			'meta_value_num__desc__avarage_ratings'  => array(
				'name' => __('Most Rated', 'essential-wp-real-estate'),
				'type' => 'sort',
				'icon' => 'fas fa-caret-right',
			),
			'meta_value_num__desc__post_views_count' => array(
				'name' => __('Most Viewed', 'essential-wp-real-estate'),
				'type' => 'sort',
				'icon' => 'fas fa-caret-right',
			),
		);

		return $this->sorter_options;
	}

	/**
	 * cl_search_fields_data function returns the array of the search fields data array. accepts
	 * required - label - placeholder - widget_name - type - field_key - options
	 *
	 * @return void
	 */
	public function cl_search_fields_data() {
		$this->search_fields = array();

		$search_settings = get_option( 'cl_search_builder_setting', array() );
		if ( empty( $search_settings ) || $search_settings == 'null' ) {
			$search_settings['enabled'] = array(
				'search',
				'price',
				'price_min',
				'price_max',
				'location',
				'property_status',
				'listings_property',
			);
		} else {
			$search_settings = json_decode( $search_settings, true );
		}

		$search_field['search']            = array(
			'icon'        => 'fas fa-map-marker-alt',
			'label'       => '',
			'placeholder' => __('Keywords', 'essential-wp-real-estate'),
			'field_key'   => 'search',
			'type'        => 'search',
			'data_key'    => 'search_key',
			'options'     => array(),
		);
		$search_field['price']             = array(
			'icon'        => 'fas fa-dollar-sign',
			'label'       => __('Price', 'essential-wp-real-estate'),
			'placeholder' => __('Price', 'essential-wp-real-estate'),
			'field_key'   => 'exact_price',
			'type'        => 'meta_type',
			'data_key'    => 'wperesds_pricing',
			'options'     => array(
				'compare' => '==',
			),
		);
		$search_field['price_min']         = array(
			'icon'        => 'fas fa-dollar-sign',
			'label'       => __('Min Price', 'essential-wp-real-estate'),
			'placeholder' => __('Min Price', 'essential-wp-real-estate'),
			'field_key'   => 'min_price',
			'type'        => 'meta_type',
			'data_key'    => 'wperesds_pricing',
			'options'     => array(
				'compare' => '>=',
			),
		);
		$search_field['price_max']         = array(
			'icon'        => 'fas fa-dollar-sign',
			'label'       => __('Max Price', 'essential-wp-real-estate'),
			'placeholder' => __('Max Price', 'essential-wp-real-estate'),
			'field_key'   => 'max_price',
			'type'        => 'meta_type',
			'data_key'    => 'wperesds_pricing',
			'options'     => array(
				'compare' => '<=',
				'type'    => 'numeric',
			),
		);
		$search_field['location']          = array(
			'icon'        => 'fas fa-map-marked-alt',
			'label'       => __('Location', 'essential-wp-real-estate'),
			'placeholder' => __('Address', 'essential-wp-real-estate'),
			'field_key'   => 'location',
			'type'        => 'meta_type',
			'data_key'    => 'wperesds_address',
			'options'     => array(
				'class'   => 'get-location-js',
				'compare' => 'LIKE',
			),
		);
		$search_field['property_status']   = array(
			'icon'        => 'fas fa-house-user',
			'label'       => __('Status', 'essential-wp-real-estate'),
			'placeholder' => __('Status', 'essential-wp-real-estate'),
			'field_key'   => 'property_status',
			'type'        => 'taxo_type',
			'data_key'    => 'listing_status',
			'options'     => array(
				'hide_empty' => false,
				'type'       => 'single',
			),
		);
		$search_field['listings_property'] = array(
			'label'       => __('Types', 'essential-wp-real-estate'),
			'placeholder' => __('Types', 'essential-wp-real-estate'),
			'field_key'   => 'listings_property',
			'type'        => 'taxo_type',
			'data_key'    => 'listings_property',
			'options'     => array(
				'hide_empty' => false,
				'type'       => 'multiple',
			),
		);

		if ( ! empty( $search_settings['enabled'] ) ) {
			foreach ( $search_settings['enabled'] as $search_setting ) {
				$this->search_fields[] = $search_field[ $search_setting ];
			}
		}

		return $this->search_fields;
	}

	public function cl_compare_fields_data() {
		$comp_field_data = get_option( 'cl_comp_field_list_builder_setting', array() );
		if ( $comp_field_data ) {
			$comp_field_data_arr = json_decode( $comp_field_data, true );
			$comp_data_array     = array();
			if ( is_array( $comp_field_data_arr ) ) {
				foreach ( $comp_field_data_arr as $c_f_d_a_value ) {
					$name                      = str_replace( 'wperesds_', '', $c_f_d_a_value );
					$label                     = ucwords( str_replace( '_', ' ', $name ) );
					$comp_data_array[ $label ] = (string) $c_f_d_a_value;
				}
			}
		}
		
		if ( isset( $comp_data_array ) && $comp_data_array ) {
			$this->compare_fields = $comp_data_array;
		} else {
			$this->compare_fields = array(
				'Price'  => 'wperesds_pricing',
				'Price Range'  => 'pricing_range',
				'Price Suffix'  => 'price_suffix',
				'Beds'   => 'wperesds_beds',
				'Bath'   => 'wperesds_bath',
				'Area'   => 'wperesds_area',
				'Garage' => 'wperesds_garage',
				'Year'   => 'wperesds_year',
			);
		}

		return $this->compare_fields;
	}

	public function get_average_rate( $post_id ) {
		$comments = get_comments(
			array(
				'post_id' => $post_id,
				'status'  => 'approve',
			)
		);
		if ( ! empty( $comments ) ) {
			$average = array();
			foreach ( $comments as $comment ) {
				$property        = get_comment_meta( $comment->comment_ID, 'property', true );
				$location        = get_comment_meta( $comment->comment_ID, 'location', true );
				$value_for_money = get_comment_meta( $comment->comment_ID, 'value_for_money ', true );
				$agent_support   = get_comment_meta( $comment->comment_ID, 'agent_support ', true );
				if ( $property ) {
					$total_rate[] = (int) $property;
				}
				if ( $location ) {
					$total_rate[] = (int) $location;
				}
				if ( $value_for_money ) {
					$total_rate[] = (int) $value_for_money;
				}
				if ( $agent_support ) {
					$total_rate[] = (int) $agent_support;
				}
				$average[] = array_sum( $total_rate ) / count( $total_rate );
			}
			$total_average = array_sum( $average ) / count( $comments );
			return round( $total_average, 1 );
		} else {
			return false;
		}
	}

	public function get_average_ratting_name( $post_id ) {

		$property_array        = array();
		$value_for_money_array = array();
		$agent_support_array   = array();
		$location_array        = array();
		$comments              = get_comments(
			array(
				'post_id' => $post_id,
				'status'  => 'approve',
			)
		);
		foreach ( $comments as $key => $comment ) {
			$property                = get_comment_meta( $comment->comment_ID, 'property', true );
			$value_for_money         = get_comment_meta( $comment->comment_ID, 'value_for_money', true );
			$agent_support           = get_comment_meta( $comment->comment_ID, 'agent_support', true );
			$location                = get_comment_meta( $comment->comment_ID, 'location', true );
			$property_array[]        = (int) $property;
			$value_for_money_array[] = (int) $value_for_money;
			$agent_support_array[]   = (int) $agent_support;
			$location_array[]        = (int) $location;
		}
		$total_average['property']        = array_sum( $property_array ) / count( $comments );
		$total_average['value_for_money'] = array_sum( $value_for_money_array ) / count( $comments );
		$total_average['agent_support']   = array_sum( $agent_support_array ) / count( $comments );
		$total_average['location']        = array_sum( $location_array ) / count( $comments );

		return $total_average;
	}

	/**
	 * get_post_view
	 *
	 * @return void
	 */
	public function get_post_view( $post_id ) {
		$count = get_post_meta( $post_id, 'post_views_count', true );
		return "$count views";
	}

	/**
	 * set_post_view
	 *
	 * @return void
	 */
	public function set_post_view( $post_id ) {
		$key   = 'post_views_count';
		$count = (int) get_post_meta( $post_id, $key, true );
		$count++;
		update_post_meta( $post_id, $key, $count );
	}
}
