<?php
namespace Essential\Restate\Front\Loader;

use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Front\Provider\Markups;
use Essential\Restate\Front\Loader\Shortcode;

/**
 * TemplateHooks
 *
 * since 1.0.0
 */
class TemplateHooks extends Loader {


	use Traitval;
	protected $hooks_names;

	/**
	 * Initialize the class
	 *
	 * since 1.0.0
	 */
	protected function initialize() {
		parent::initialize();
		$this->init_content_hooks_names();
		$this->init_structure_hooks();

		// Assign_globals function call
		add_action( 'init', array( $this, 'assign_globals' ) );
		add_action( 'init', array( Shortcode::getInstance(), 'init_shortcode' ) );
		add_action( 'init', array( $this, 'cl_get_actions' ) );
		add_action( 'init', array( $this, 'cl_post_actions' ) );
	}

	function cl_get_actions() {
		if ( isset( $_GET['cl_action'] ) ) {
			do_action( 'cl_' . cl_sanitization( $_GET['cl_action'] ), cl_sanitization( $_GET ) );
		}
	}

	function cl_post_actions() {
		if ( isset( $_POST['cl_action'] ) ) {
			do_action( 'cl_' . cl_sanitization( $_POST['cl_action'] ), cl_sanitization( $_POST ) );
		}
	}

	/**
	 * init_structure_hooks initializes all the hooks for frontend.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	private function init_structure_hooks() {

		// Hook to include listing page templates
		add_filter( 'template_include', array( $this, 'include_template' ) );
		add_action( 'init', array( $this, 'section_footer' ) );
		/**
		 * Add Comment Rate field.
		 */
		add_action( 'comment_post', array( $this, 'comment_ratings' ), 10, 3 );
		$this->current_post_type = ( ( get_post_type() ) ) ? get_post_type() : $this->cl_cpt;

		// General Listing Structure Hooks
		add_action( $this->prefix . 'before_listing_content', array( $this, 'page_header' ), 10 );
		add_action( $this->prefix . 'after_listing_content', array( $this, 'page_footer' ), 10 );
		add_action( $this->prefix . 'get_sidebar_template', array( $this, 'sidebar_template' ), 10 );
		add_action( $this->prefix . 'listing_cart', array( $this, 'append_cart_button' ), 5 );

		// Listing Archive and Single Structure Hooks
		add_action( $this->prefix . 'before_listing_loop', array( $this, 'before_listing_loop' ), 10 );
		add_action( $this->prefix . 'listing_loop', array( $this, 'listing_loop' ), 10 );
		add_action( $this->prefix . 'after_listing_loop', array( $this, 'after_listing_loop' ), 10 );
		add_action( $this->prefix . 'no_listings_found', array( $this, 'nothing_found' ), 10 );
		$this->init_archive_content_hooks();
		$this->init_single_content_hooks();
	}

	/**
	 * init_content_hooks_names function for archive listing content hooks Names
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function init_content_hooks_names() {

		$this->hooks_names = apply_filters(
			'cl_hooks',
			array(
				'listing_title',
				'listing_content',
				'listing_excerpt',
				'listing_views',
				'listing_price',
				'listing_types',
				'listing_author',
				'listing_status',
				'listing_features',
				'listing_location',
				'listing_address',
				'listing_ratings',
				'listing_favourite',
				'listing_compare',
				'listing_view',
				'listing_publishdate',
				'listing_viewcount',
				'listing_layout',
				'listing_sorter',
				'listing_abuse',
				'listing_share',
				'listing_meta_features',
			)
		);

		// print_r( $this->hooks_names );
	}

	/**
	 * init_archive_content_hooks function for archive listing content hooks
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function init_archive_content_hooks() {
		foreach ( $this->hooks_names as $value ) {
			add_action( $this->prefix . $value, array( Markups::getInstance(), $value ), 10 );
		}
	}

	/**
	 * init_single_content_hooks function for single listing content hooks
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function init_single_content_hooks() {

		add_action( $this->prefix . 'sectionthumbnail', array( $this, 'listing_thumbnail' ), 10, 1 );
		add_action( $this->prefix . 'metasection', array( $this, 'listing_meta_data' ), 10, 1 );
		add_action( $this->prefix . 'extrasection', array( $this, 'listing_extra_data' ), 10, 1 );
		add_action( $this->prefix . 'templatesection', array( $this, 'listing_extra_data' ), 10, 1 );
	}

	public function append_cart_button( $args ) {
		WPERECCP()->front->cart->append_cart_button( $args );
	}
}
