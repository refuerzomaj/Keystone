<?php
namespace Essential\Restate\Common\PostTypes;

use Essential\Restate\Traitval\Traitval;

class PostTypes {

	use Traitval;

	public function initialize() {
		add_action( 'init', array( $this, 'initialize_filters' ) );
		add_action( 'init', array( $this, 'initialize_post_types' ) );
		add_action( 'init', array( $this, 'initialize_taxonomies' ) );
		add_action( 'init', array( $this, 'register_statuses' ), 2 );
	}

	/**
	 * Get value of post type / Taxonomies and store in the current object.
	 */
	public function initialize_filters() {
		$this->cl_cpt_config = apply_filters( $this->plugin_pref . 'post_types', array() );
		$this->cl_tax_config = apply_filters( $this->plugin_pref . 'taxonomies', array() );
	}

	/**
	 * Generate Post Type from the current object
	 */
	public function initialize_post_types() {
		foreach ( $this->cl_cpt_config as $config ) {
			$cpt_name     = $config['name'];
			$cpt_slug     = $config['slug'];
			$cpt_singular = $config['singular'];
			$cpt_plural   = $config['plural'];
			$menu_icon    = $config['dashicon'] ?? 'dashicons-wordpress';
			$cpt_supports = array( 'title', 'editor', 'author', 'thumbnail', 'comments','custom-fields' );
			$show_in_menu = $config['show_in_menu'] ?? true;
			$labels       = array(
				'name'                  => _x( $cpt_singular, 'Plural Name of Property Listing Plugin listing', 'essential-wp-real-estate' ),
				'singular_name'         => _x( $cpt_singular, 'Singular Name of Property Listing Plugin listing', 'essential-wp-real-estate' ),
				'menu_name'             => __( $cpt_plural, 'essential-wp-real-estate' ),
				'name_admin_bar'        => _x( $cpt_name, 'Add New on Toolbar', 'essential-wp-real-estate' ),
				'archives'              => __( $cpt_singular . ' Archives', 'essential-wp-real-estate' ),
				'attributes'            => __( $cpt_singular . ' Attributes', 'essential-wp-real-estate' ),
				'parent_item_colon'     => __( 'Parent ' . $cpt_singular . ':', 'essential-wp-real-estate' ),
				'all_items'             => __( 'All ' . $cpt_plural, 'essential-wp-real-estate' ),
				'add_new_item'          => __( 'Add New ' . $cpt_singular, 'essential-wp-real-estate' ),
				'add_new'               => __( 'Add New ' . $cpt_singular, 'essential-wp-real-estate' ),
				'new_item'              => __( 'New ' . $cpt_singular, 'essential-wp-real-estate' ),
				'edit_item'             => __( 'Edit ' . $cpt_singular, 'essential-wp-real-estate' ),
				'update_item'           => __( 'Update ' . $cpt_singular, 'essential-wp-real-estate' ),
				'view_item'             => __( 'View ' . $cpt_singular, 'essential-wp-real-estate' ),
				'view_items'            => __( 'View ' . $cpt_plural, 'essential-wp-real-estate' ),
				'search_items'          => __( 'Search ' . $cpt_singular, 'essential-wp-real-estate' ),
				'not_found'             => __( 'Not found', 'essential-wp-real-estate' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'essential-wp-real-estate' ),
				'featured_image'        => __( 'Featured Image', 'essential-wp-real-estate' ),
				'set_featured_image'    => __( 'Set featured image', 'essential-wp-real-estate' ),
				'remove_featured_image' => __( 'Remove featured image', 'essential-wp-real-estate' ),
				'use_featured_image'    => __( 'Use as featured image', 'essential-wp-real-estate' ),
				'insert_into_item'      => __( 'Insert into ' . $cpt_singular, 'essential-wp-real-estate' ),
				'uploaded_to_this_item' => __( 'Uploaded to this ' . $cpt_singular, 'essential-wp-real-estate' ),
				'items_list'            => __( $cpt_plural . ' list', 'essential-wp-real-estate' ),
				'items_list_navigation' => __( $cpt_plural . ' list navigation', 'essential-wp-real-estate' ),
				'filter_items_list'     => __( 'Filter' . $cpt_plural . ' list', 'essential-wp-real-estate' ),
			);
			$args         = array(
				'labels'             => $labels,
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => $show_in_menu,
				'menu_icon'          => $menu_icon,
				'menu_position'      => 5,
				'query_var'          => true,
				'rewrite'            => array(
					'slug'       => untrailingslashit( $cpt_slug ),
					'with_front' => false,
					'feeds'      => true,
				),
				'map_meta_cap'       => true,
				'has_archive'        => $cpt_slug,
				'hierarchical'       => false,
				'supports'           => $cpt_supports,
			);
			register_post_type( $cpt_name, $args );
		}
	}
	/**
	 * Generate Taxonoies from the current object
	 */
	public function initialize_taxonomies() {
		foreach ( $this->cl_tax_config as $config ) {
			$taxonomy_name     = $config['name'];
			$taxonomy_slug     = $config['slug'];
			$taxonomy_singular = $config['singular'];
			$taxonomy_plural   = $config['plural'];
			$reg_cpt           = $config['reg_cpt'];
			$hierarchical      = $config['hierarchical'] ?? 'false';
			$labels            = array(
				'name'              => _x( $taxonomy_plural, 'taxonomy general name', 'essential-wp-real-estate' ),
				'singular_name'     => _x( $taxonomy_singular, 'taxonomy singular name', 'essential-wp-real-estate' ),
				'search_items'      => __( 'Search ' . $taxonomy_singular, 'essential-wp-real-estate' ),
				'all_items'         => __( 'All ' . $taxonomy_singular, 'essential-wp-real-estate' ),
				'parent_item'       => __( 'Parent ' . $taxonomy_singular, 'essential-wp-real-estate' ),
				'parent_item_colon' => __( 'Parent ' . $taxonomy_singular . ' :', 'essential-wp-real-estate' ),
				'edit_item'         => __( 'Edit ' . $taxonomy_singular, 'essential-wp-real-estate' ),
				'update_item'       => __( 'Update ' . $taxonomy_singular, 'essential-wp-real-estate' ),
				'add_new_item'      => __( 'Add New ' . $taxonomy_singular, 'essential-wp-real-estate' ),
				'new_item_name'     => __( 'New ' . $taxonomy_singular . ' Name', 'essential-wp-real-estate' ),
				'menu_name'         => __( $taxonomy_plural, 'essential-wp-real-estate' ),
			);
			$args              = array(
				'hierarchical'      => $hierarchical,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => $taxonomy_slug,
					'with_front' => false,
				),
			);
			register_taxonomy( $taxonomy_name, $reg_cpt, $args );
		}
	}

	/**
	 * Registers Custom Post Statuses which are used by the Payments and Discount
	 * Codes
	 *
	 * @since 1.0.9.1
	 * @return void
	 */
	public function register_statuses() {
		// Payment Statuses
		register_post_status(
			'refunded',
			array(
				'label'                     => _x( 'Refunded', 'Refunded payment status', 'essential-wp-real-estate' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'essential-wp-real-estate' ),
			)
		);
		register_post_status(
			'failed',
			array(
				'label'                     => _x( 'Failed', 'Failed payment status', 'essential-wp-real-estate' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'essential-wp-real-estate' ),
			)
		);
		register_post_status(
			'revoked',
			array(
				'label'                     => _x( 'Revoked', 'Revoked payment status', 'essential-wp-real-estate' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Revoked <span class="count">(%s)</span>', 'Revoked <span class="count">(%s)</span>', 'essential-wp-real-estate' ),
			)
		);
		register_post_status(
			'abandoned',
			array(
				'label'                     => _x( 'Abandoned', 'Abandoned payment status', 'essential-wp-real-estate' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Abandoned <span class="count">(%s)</span>', 'Abandoned <span class="count">(%s)</span>', 'essential-wp-real-estate' ),
			)
		);
		register_post_status(
			'processing',
			array(
				'label'                     => _x( 'Processing', 'Processing payment status', 'essential-wp-real-estate' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'essential-wp-real-estate' ),
			)
		);

		// Discount Code Statuses
		register_post_status(
			'active',
			array(
				'label'                     => _x( 'Active', 'Active discount code status', 'essential-wp-real-estate' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'essential-wp-real-estate' ),
			)
		);
		register_post_status(
			'inactive',
			array(
				'label'                     => _x( 'Inactive', 'Inactive discount code status', 'essential-wp-real-estate' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'essential-wp-real-estate' ),
			)
		);
	}
}
