<?php
namespace Essential\Restate\Common\Provider;

use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Admin\MetaBoxes\Components\Groups;

/**
 * The admin class
 */
class Provider {

	use Traitval;

	public function get_single_temp_data() {
		return array(
			'rating_overview'   => esc_html__( 'Rating', 'essential-wp-real-estate' ),
			'comment_form'      => esc_html__( 'Write a Review', 'essential-wp-real-estate' ),
			'comments_template' => esc_html__( 'Reviews', 'essential-wp-real-estate' ),
		);
	}

	public function get_group_data() {
		$field_data = array();
		$group_dat  = $this->get_group_headings();
		foreach ( $group_dat as $group_name => $group ) {
			$get_groups_data           = Groups::getInstance()->get_groups_data( $group_name );
			$field_data[ $group_name ] = $get_groups_data;
		}
		return $field_data;
	}

	public function get_group_headings() {
		$add_opt          = get_option( 'cl_add_builder_setting', array() );
		$default_template = array(
			'preset'      => esc_html__( 'Details', 'essential-wp-real-estate' ),
			'description' => esc_html__( 'Description', 'essential-wp-real-estate' ),
		);

		if ( isset( $add_opt ) && ! empty( $add_opt ) ) {
			$add_opt   = json_decode( $add_opt );
			$meta_data = isset( $add_opt ) && ! empty( $add_opt ) ? $add_opt : '';
			if ( isset( $meta_data->enabled ) && ! empty( $meta_data->enabled ) ) {
				foreach ( $meta_data->enabled as $meta_data_key => $value ) {
					$heading_title                  = ucwords( str_replace( '-', ' ', $meta_data_key ) );
					$heading_data[ $meta_data_key ] = esc_html( $heading_title );
				}
				$heading_data['description'] = 'Description';
				return $heading_data;
			} else {
				return $default_template;
			}
		} else {
			// get headings from db
			return $default_template;
		}
	}

	public function get_default_templates() {
		return array(
			'amenities',
			'comment_form',
			'comments_template',
			'contact',
			'description',
			'features',
			'floor',
			'gallery',
			'maps',
			'price',
			'rating_overview',
			'video',
		);
	}
}
