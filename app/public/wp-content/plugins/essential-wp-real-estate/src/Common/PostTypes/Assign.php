<?php
namespace Essential\Restate\Common\PostTypes;

use Essential\Restate\Traitval\Traitval;

class Assign {

	use Traitval;
	public function initialize() {
		add_filter( $this->plugin_pref . 'post_types', array( $this, 'initialize_cpt_default' ) );
		add_filter( $this->plugin_pref . 'taxonomies', array( $this, 'initialize_taxo_default' ) );
	}
	public function initialize_cpt_default( $post_types ) {
		$post_types[] = array(
			'name'         => 'cl_cpt',
			'slug'         => cl_admin_get_option( 'listing_slug' ) ? cl_admin_get_option( 'listing_slug' ) : 'listings',
			'singular'     => _x('Property Listing','taxonomy singular name','essential-wp-real-estate'),
			'plural'       => _x('Property Listings','taxonomy plural name','essential-wp-real-estate'),
			'dashicon'     => WPERESDS_ASSETS . ( '/img/ccl-icon.svg' ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revision', 'author','custom-fields' ),
			'show_in_menu' => true,
		);
		$post_types[] = array(
			'name'         => 'cl_payment',
			'slug'         => 'crazy-payments',
			'singular'     => _x('Payment','taxonomy singular name','essential-wp-real-estate'),
			'plural'       => _x('Payments','taxonomy plural name','essential-wp-real-estate'),
			'dashicon'     => WPERESDS_ASSETS . ( '/img/ccl-icon.svg' ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revision', 'author' ),
			'show_in_menu' => false,
		);
		$post_types[] = array(
			'name'         => 'cl_discount',
			'slug'         => 'crazy-discounts',
			'singular'     => _x('Discount','taxonomy singular name','essential-wp-real-estate'),
			'plural'       => _x('Discounts','taxonomy plural name','essential-wp-real-estate'),
			'dashicon'     => WPERESDS_ASSETS . ( '/img/ccl-icon.svg' ),
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revision', 'author' ),
			'show_in_menu' => false,
		);
		return $post_types;
	}
	public function initialize_taxo_default( $taxonomies ) {
		$taxonomies[] = array(
			'name'         => 'listings_property',
			'slug'         => 'listings_property',
			'singular'     => _x('Type','taxonomy singular name','essential-wp-real-estate'),
			'plural'       => _x('Types','taxonomy plural name','essential-wp-real-estate'),
			'hierarchical' => true,
			'reg_cpt'      => array( 'cl_cpt' ),
		);
		$taxonomies[] = array(
			'name'         => 'listing_location',
			'slug'         => 'listing_location',
			'singular'     => _x('Location','taxonomy singular name','essential-wp-real-estate'),
			'plural'       => _x('Locations','taxonomy plural name','essential-wp-real-estate'),
			'hierarchical' => true,
			'reg_cpt'      => array( 'cl_cpt' ),
		);
		$taxonomies[] = array(
			'name'         => 'listing_status',
			'slug'         => 'listing_status',
			'singular'     => _x('Status','taxonomy singular name','essential-wp-real-estate'),
			'plural'       => _x('Status','taxonomy plural name','essential-wp-real-estate'),
			'hierarchical' => false,
			'reg_cpt'      => array( 'cl_cpt' ),
		);
		$taxonomies[] = array(
			'name'              => 'listing_features',
			'slug'              => 'listing_features',
			'singular'          => _x('Feature','taxonomy singular name','essential-wp-real-estate'),
			'plural'            => _x('Features','taxonomy plural name','essential-wp-real-estate'),
			'hierarchical'      => false,
			'show_admin_column' => false,
			'reg_cpt'           => array( 'cl_cpt' ),
		);
		return $taxonomies;
	}
}
