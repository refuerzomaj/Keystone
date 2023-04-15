<?php
namespace Essential\Restate\Front\Loader;

use Essential\Restate\Traitval\Traitval;

/**
 * The admin class
 */
class Condition {

	use Traitval;

	protected $current_post_type;

	/**
	 * is_listing detects if any listing page single or archive.
	 *
	 * @return void
	 */
	public function is_listing() {
		if ( $this->is_single() ) {
			return 'single';
		} elseif ( $this->is_archive() || $this->is_listing_taxonomy() ) {
			return 'archive';
		}
	}
	/**
	 * is_single checks if the current page is the single page of a post type.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function is_single() {
		return ( is_singular( $this->get_cpt_lists() ) );
	}

	/**
	 * is_archive checks if the current page is the archive page of the current post type
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function is_listing_taxonomy() {
		 return ( is_tax( get_object_taxonomies( $this->get_cpt_lists( true ) ) ) );
	}

	/**
	 * is_archive checks if the current page is the archive page of the current post type
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function is_archive() {
		return ( is_post_type_archive( $this->get_cpt_lists() ) );
	}
}
