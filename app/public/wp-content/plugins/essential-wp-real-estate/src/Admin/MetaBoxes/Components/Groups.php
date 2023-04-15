<?php
namespace Essential\Restate\Admin\MetaBoxes\Components;

use Essential\Restate\Common\Provider\Provider;
use Essential\Restate\Traitval\Traitval;

class Groups extends Fields {

	use Traitval;

	private $headings = array();
	private $groups   = array();
	private $meta_data;

	public function __construct() {
		$add_opt = get_option( 'cl_add_builder_setting', array() );
		if ( isset( $add_opt ) && ! empty( $add_opt ) ) {
			$add_opt  = json_decode( $add_opt );
			$opt_data = isset( $add_opt ) && ! empty( $add_opt ) ? $add_opt : '';
			if ( isset( $opt_data ) && ! empty( $opt_data ) ) {
				$this->meta_data = $opt_data;
			}
		}
	}

	public function set_group_headings() {
		if ( ! isset( $this->headings ) || empty( $this->headings ) ) {
			$this->headings = Provider::getInstance()->get_group_headings();
		}
		return $this->headings;
	}

	public function set_groups() {
		if ( isset( $this->meta_data->enabled ) ) {
			foreach ( $this->meta_data->enabled as $meta_data_key => $value ) {
				$this->groups[] = array(
					'id'         => $meta_data_key,
					'post_types' => array( 'cl_cpt' ),
					'context'    => 'normal',
					'priority'   => 'high',
				);
			}
		} else {
			$this->groups = array(
				array(
					'id'         => 'preset',
					'post_types' => array( 'cl_cpt' ),
					'context'    => 'normal',
					'priority'   => 'high',
				),
			);
		}
	}

	public function generate_groups() {
		$this->set_group_headings();
		$this->set_groups();

		foreach ( $this->groups as $group ) {
			$group           = array( 'title' => $this->headings[ $group['id'] ] ) + $group;
			$group['fields'] = ! empty( $this->get_fields( $group['id'] ) ) ? $this->get_fields( $group['id'] ) : array();
			array_shift( $this->groups );
			$index                  = count( $this->groups );
			$this->groups[ $index ] = $group;
		}
		return $this->groups;
	}

	public function get_groups_data( $name ) {
		return $this->get_fields( $name );
	}
}
