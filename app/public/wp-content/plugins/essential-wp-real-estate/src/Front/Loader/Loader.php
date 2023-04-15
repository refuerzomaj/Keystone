<?php
namespace Essential\Restate\Front\Loader;

use Essential\Restate\Front\Loader\Styles;
use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Front\Models\Listings;

/**
 * Loader class loads everything related templates
 *
 * since 1.0.0
 */
class Loader extends Styles {

	use Traitval;

	private $template;

	/**
	 * Initialize the class
	 *
	 * since 1.0.0
	 */
	protected function initialize() {
		$this->enqueue_styles();
		$this->enqueue_scripts();
	}

	/**
	 * assign_globals
	 *
	 * This Function use to assign global variables.
	 *
	 * @return void
	 * since 1.0.0
	 */
	public function assign_globals() {
		global $pref;
		$pref = $this->prefix;
	}

	/**
	 * get_layout_style function helps to get the grid/list/map layout
	 *
	 * @param  mixed $page
	 * @return void
	 */
	public function get_layout_style( $page ) {
		if ( ! empty( $_GET['layout'] ) ) {
			return '_' . cl_sanitization( $_GET['layout'] );
		} else {
			$default_layout = cl_admin_get_option( 'default_layout', 'grid' );
			if ( $page == 'archive' ) {
				return '_' . $default_layout;
			} else {
				return '';
			}
		}
	}

	/**
	 * include_template get templates for cpt pages.
	 *
	 * @param  mixed $template
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function include_template( $template ) {

		$file = '';
		global $pref;
		$args['pref']   = $pref;
		$this->template = $template;
		if ( $this->is_single() ) {
			$file = 'single-' . $this->current_post_type . '.php';
		} elseif ( $this->is_listing_taxonomy() ) {
			$object = get_queried_object();
			$file   = 'archive-' . $this->current_post_type . '.php';
		} elseif ( $this->is_archive() ) {
			$file = 'archive-' . $this->current_post_type . '.php';
		}
		if ( ! $file ) {
			return $template;
		}
		$template = cl_get_template( $file, $args );
		return $template;
	}

	/**
	 * section_footer gets the page header template to load.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function section_footer() {
		cl_get_template_with_dir( 'listing_footer.php', '/common' );
	}

	/**
	 * page_header gets the page header template to load.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function page_header() {
		 cl_get_template_with_dir( 'page_header.php', '/common' );
	}

	/**
	 * page_footer gets the page footer template to load.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function page_footer() {
		 cl_get_template_with_dir( 'page_footer.php', '/common' );
	}

	/**
	 * before_listing_loop gets any template that will go before the loop
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function before_listing_loop() {
		 cl_get_template_with_dir( $this->is_listing() . '_top.php', '/loop' );
	}

	/**
	 * listing_loop gets the template that will traverse the loop to show listings.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function listing_loop( $args ) {
		WPERECCP()->front->listing_provider->set_listing_object();
		$template = $this->is_listing();

		if ( $args == 'list' ) {
			$template = 'archive_loop_list';
		} elseif ( $args == 'grid' ) {
			$template = 'archive_loop_grid';
		} else {
			$template .= '_loop' . $this->get_layout_style( $template );
		}
		cl_get_template_with_dir( "{$template}.php", '/loop' );
	}


	/**
	 * single_section_loop gets the template that will traverse the loop to show single listing sections.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function single_section_loop() {
		 // -- Get section data from listing provider
		$section_data = WPERECCP()->front->listing_provider->single_listing_section();
		foreach ( $section_data as $section ) {
			if ( $this->section_content_func( $section ) == true ) {
				cl_get_template( "single/single-{$section['type']}.php", $section );
			}
		}
	}

	/**
	 * single_thumbnail_sec include the thumbnail template on single page
	 *
	 * @return void
	 */
	public function single_thumbnail_sec() {
		cl_get_template( 'single/single-slider.php' );
	}

	/**
	 * single_section_info gets the template that will traverse the loop to show single listing sections.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function single_section_info() {
		 cl_get_template( 'single/single-listing-info.php' );
	}

	public function section_content_func( $section ) {
		$content_data = false;

		foreach ( $section['blocks'] as $block ) {
			if ( $block['type'] == 'other' ) {
				$value = WPERECCP()->front->listing_provider->get_meta_data( $block['key'], get_the_ID() );

				if ( is_array( $value ) ) {
					$val = array_values( array_filter( $value ) );
					if ( ! empty( array_filter( $val ) ) ) {
						if ( is_array( $val[0] ) ) {
							if ( ! empty( array_filter( $val[0] ) ) ) {
								$content_data = true;
							} else {
								$content_data = false;
							}
						} else {
							$content_data = true;
						}
					} else {
						$content_data = false;
					}
				} else {
					if ( ! empty( $value ) ) {
						$content_data = true;
					} else {
						$content_data = false;
					}
				}
			} elseif ( $block['type'] == 'default' ) {
				$content_data = true;
			}
		}

		return $content_data;
	}

	/**
	 * after_listing_loop gets any template that will go before the loop
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function after_listing_loop() {
		cl_get_template_with_dir( $this->is_listing() . '_bottom.php', '/loop' );
	}

	/**
	 * comments_template gets the comment template for single
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function comments_template() {
		cl_get_template( 'single/comments/comments_template.php' );
	}

	/**
	 * comment_form show the comment form which is the rating form in the main time.
	 *
	 * @return void
	 */
	public function comment_form() {
		cl_get_template( 'single/comments/comment_form.php' );
	}

	/**
	 * sidebar_template gets the listing sidebar template.
	 *
	 * @return void
	 */
	public function sidebar_template( $args = '' ) {
		cl_get_template( 'common/sidebar.php', $args );
	}

	public function nothing_found() {
		cl_get_template_with_dir( 'nothing_found.php', '/common' );
	}

	public function comment_ratings( $comment_id, $comment_approved, $commentdata ) {
		$property        = cl_sanitization( $_POST['property'] );
		$location        = cl_sanitization( $_POST['location'] );
		$value_for_money = cl_sanitization( $_POST['value_for_money'] );
		$agent_support   = cl_sanitization( $_POST['agent_support'] );

		add_comment_meta( $comment_id, 'property', $property );
		add_comment_meta( $comment_id, 'location', $location );
		add_comment_meta( $comment_id, 'value_for_money', $value_for_money );
		add_comment_meta( $comment_id, 'agent_support', $agent_support );

		$comment_post_id = $commentdata['comment_post_ID'];
		$total_rate      = (int) $property + (int) $location + (int) $value_for_money + (int) $agent_support;
		$average         = ( $total_rate * 100 ) / 4;
		$average         = round( $average, 1 );
		$average         = WPERECCP()->front->listing_provider->get_average_rate( $comment_post_id );
		update_post_meta( $comment_post_id, 'avarage_ratings', $average );
	}
	/**
	 * listing_thumbnail call back function to be added to thumbnail hook.
	 *
	 * @return void
	 */
	public function listing_thumbnail( $args ) {
		$page = $this->is_listing();
		cl_get_template( "{$page}/single-slider.php", $args );
	}

	public function listing_meta_data( $args ) {
		$page = $this->is_listing();
		cl_get_template( "{$page}/single-listing-info.php", $args );
	}

	public function listing_extra_data( $args ) {
		cl_get_template( 'single/single-general.php', array( 'args' => $args ) );
	}
}
