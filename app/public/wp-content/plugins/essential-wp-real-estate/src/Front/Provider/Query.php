<?php
namespace Essential\Restate\Front\Provider;

use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Front\Models\Listings;

class Query extends Listings {


	use Traitval;

	public $post_type;
	public $post_status;
	public $posts_per_page;
	public $paged;
	public $order;
	public $orderby;

	private function __construct() {
		$this->post_type      = $this->cl_cpt;
		$this->post_status    = 'publish';
		$this->posts_per_page = cl_admin_get_option( 'listings_per_pages', 4 );
		$this->order          = 'date';
		$this->orderby        = 'DESC';
		if ( ! is_admin() ) {
			add_filter( 'parse_query', array( $this, 'parse_query_func' ) );
		}
	}

	/**
	 * get_listing_query generate the main query for the listing
	 *
	 * @return void
	 */
	public function get_listing_query() {
		global $paged;

		$taxo_search_val = array();
		$meta_search_val = array();

		$cl_search_fields_data = WPERECCP()->front->listing_provider->cl_search_fields_data();

		// -- Initialize arguments
		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => $this->post_status,
			'posts_per_page' => $this->posts_per_page,
			'paged'          => $paged,
			'orderby'        => $this->orderby,
			'order'          => $this->order,
		);

		if ( get_query_var( 'taxonomy' ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => get_query_var( 'taxonomy' ),
					'field'    => 'slug',
					'terms'    => get_query_var( 'term' ),
				),
			);
		}

		// -- Sorting data
		if ( isset( $_GET['sort'] ) && ! empty( $_GET['sort'] ) ) {
			$value = explode( '__', cl_sanitization( $_GET['sort'] ) );
			if ( isset( $value[0] ) ) {
				$args['orderby'] = $value[0];
			}
			if ( isset( $value[1] ) ) {
				$args['order'] = $value[1];
			}
			if ( isset( $value[2] ) ) {
				$args['meta_key'] = $value[2];
			}
		}

		// -- Search Query

		if ( is_archive( $this->cl_cpt ) ) {
			if ( isset( $_REQUEST['search'] ) && $_REQUEST['search'] == 'advanced' ) {
				if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
					$args['s'] = cl_sanitization( $_GET['s'] );
				}

				// -- Metabox value set

				foreach ( $cl_search_fields_data as $field_data ) {

					// -- Get Compare data if isset
					if ( isset( $field_data['options']['compare'] ) && ! empty( $field_data['options']['compare'] ) ) {
						$compare_set = $field_data['options']['compare'];
					} else {
						$compare_set = 'LIKE';
					}

					// -- Get Type data if isset
					if ( isset( $field_data['options']['type'] ) && ! empty( $field_data['options']['type'] ) ) {
						$type_set = $field_data['options']['type'];
					} else {
						$type_set = null;
					}

					if ( $field_data['type'] == 'meta_type' ) {
						if ( isset( $_GET[ $field_data['field_key'] ] ) && ! empty( $_GET[ $field_data['field_key'] ] ) ) {
							// -- Meta Query
							$meta_search_val[] = array(
								'key'     => $field_data['data_key'],
								'value'   => cl_sanitization( $_GET[ $field_data['field_key'] ] ),
								'compare' => $compare_set,
								'type'    => $type_set,
							);
						}
					}
				}

				// -- Taxonomy value set

				foreach ( $cl_search_fields_data as $field_data ) {
					if ( $field_data['type'] == 'taxo_type' ) {
						if ( isset( $_GET[ $field_data['field_key'] ] ) && ! empty( $_GET[ $field_data['field_key'] ] ) ) {
							$taxo_search_val[] = array(
								'taxonomy' => $field_data['data_key'],
								'field'    => 'slug',
								'terms'    => cl_sanitization( $_GET[ $field_data['field_key'] ] ),
							);
						}
					}
				}

				// -- execute taxonomy search query

				if ( ! empty( $taxo_search_val ) ) {
					$args['tax_query'] = array(
						'relation' => 'AND',
						$taxo_search_val,
					);
				}

				// -- execute meta search query

				if ( ! empty( $meta_search_val ) ) {
					$args['meta_query'] = array(
						'relation' => 'AND',
						$meta_search_val,
					);
				}
			}
		}

		// -- Query init
		$LISTING_Query = new \WP_Query( $args );
		return $LISTING_Query;
	}

	/**
	 * get_listing_query generate the main query for the listing
	 *
	 * @return void
	 */
	public function get_listing_query_by_user() {
		global $paged, $current_user;

		// -- Initialize arguments
		$args = array(
			'author'         => $current_user->ID,
			'post_type'      => $this->post_type,
			'post_status'    => array( 'publish', 'pending', 'draft', 'expired' ),
			'posts_per_page' => $this->posts_per_page,
			'paged'          => $paged,
			'orderby'        => $this->orderby,
			'order'          => $this->order,
		);

		// -- Query init
		$LISTING_Query = new \WP_Query( $args );
		return $LISTING_Query;
	}

	public function get_listing_query_by_user_published() {
		 global $paged, $post, $author, $author_name;

		$curauth = ( isset( $_GET['author_name'] ) ) ? get_user_by( 'slug', $author_name ) : get_userdata( intval( $author ) );
		if ( $curauth != '' ) {
			$author_id = $curauth->ID;
		} else {
			$author_id = $post->post_author;
		}

		// -- Initialize arguments
		$args = array(
			'author'         => $author_id,
			'post_type'      => $this->post_type,
			'post_status'    => array( 'publish' ),
			'posts_per_page' => $this->posts_per_page,
			'paged'          => $paged,
			'orderby'        => $this->orderby,
			'order'          => $this->order,
		);

		// -- Query init
		$LISTING_Query = new \WP_Query( $args );
		return $LISTING_Query;
	}


	public function get_listing_query_by_user_fav() {
		global $paged, $current_user;

		// -- Initialize arguments
		$args = array(
			'author__in'     => $current_user->ID,
			'post_type'      => $this->post_type,
			'post_status'    => array( 'publish' ),
			'posts_per_page' => $this->posts_per_page,
			'paged'          => $paged,
			'orderby'        => array(
				'meta_value_num' => 'ASC',
			),
			'meta_query'     => array(
				'key'     => '_favorite_posts',
				'compare' => '!==',
				'value'   => '',
			),
			'order'          => $this->order,
		);

		// -- Query init
		$LISTING_Query = new \WP_Query( $args );
		return $LISTING_Query;
	}

	// -- Search Query

	public function parse_query_func() {
		global $wp_query;
	}
}
