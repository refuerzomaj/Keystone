<?php
namespace Essential\Restate\Admin\MetaBoxes;

use Essential\Restate\Admin\MetaBoxes\Styles;
use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Admin\MetaBoxes\Components\Groups;

class MetaBoxes extends Styles {

	use Traitval;
	/**
	 * __construct
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function __construct() {
		global $pagenow;
		if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
			$this->enqueue_styles();
			$this->enqueue_scripts();
		}
		require_once __DIR__ . '/functions.php';

		// ----Render Meta box
		$this->config = apply_filters( 'cl_meta_boxes', Groups::getInstance()->generate_groups() );
		// ----Register Meta box
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_func' ), 100 );
		// ----Save meta value with save post hook
		add_action( 'save_post', array( $this, 'save_meta_boxes_func' ), 10 );
		// ----Add Image Meta Size
		add_action( 'init', array( $this, 'add_metabox_image_size' ) );
	}

	/**
	 * Render Meta Box Function
	 *
	 * @param  mixed $post
	 * @param  mixed $cl_meta_group
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function render_meta_func( $post, $cl_meta_group ) {

		printf( '<div class="cl-meta">' );
		foreach ( $this->config as $cl_render_meta ) {
			if ( $cl_meta_group['id'] == $cl_render_meta['id'] ) {
				foreach ( $cl_render_meta['fields'] as $cl_meta_field ) {
					if ( isset( $cl_meta_field['type'] ) ) {
						include dirname( __FILE__ ) . '/Forms/' . $cl_meta_field['type'] . '.php';
					}
				}
			}
		}
		printf( '</div>' );
	}

	/**
	 * Register Meta Box Function
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function add_meta_boxes_func() {
		foreach ( $this->config as $config ) {

			$id         = $config['id'];
			$title      = $config['title'];
			$post_types = $config['post_types'] ?? 'cl_cpt';
			$context    = $config['context'] ?? 'advanced';
			$priority   = $config['priority'] ?? 'default';
			add_meta_box(
				$id,
				$title,
				array( $this, 'render_meta_func' ),
				$post_types,
				$context,
				$priority
			);
		}
	}

	/**
	 * Save meta value with save post hook
	 *
	 * @param  mixed $post_id
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function save_meta_boxes_func( $post_id ) {
		foreach ( $this->config as $cl_save_meta ) {
			foreach ( $cl_save_meta['fields'] as $cl_meta_field ) {
				if ( isset( $_POST[ $cl_meta_field['id'] ] ) ) {

					if ( is_array( $_POST[ $cl_meta_field['id'] ] ) ) {
						// -- If is an array, prevent saving duplicate item on meta data
						$cl_meta_val = cl_sanitization( $_POST[ $cl_meta_field['id'] ] );
						update_post_meta( $post_id, $cl_meta_field['id'], $cl_meta_val );
					} else {
						update_post_meta( $post_id, $cl_meta_field['id'], cl_sanitization( $_POST[ $cl_meta_field['id'] ] ) );
					}
				} else {
					// update_post_meta( $post_id, $cl_meta_field['id'], '' );
				}
			}
		}
	}

	/**
	 * Define meta Image Size
	 *
	 * @return void
	 */
	public function add_metabox_image_size() {
		add_image_size( 'meta-thumb', 150, 150, true );
	}
}
