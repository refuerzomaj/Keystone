<?php
namespace Essential\Restate\Front\Models;

use Essential\Restate\Traitval\Traitval;

abstract class Datacore {

	use Traitval;

	public $ID;
	public $url;
	public $title;
	public $content;
	public $excerpt;


	/**
	 * __construct protected function to create the whole listing object from id.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	protected function __construct() {
		if ( isset( $this->ID ) && $this->ID ) {
			$this->url     = get_the_permalink( $this->ID );
			$this->title   = get_the_title( $this->ID );
			$this->content = get_the_content( $this->ID );
			$this->excerpt = get_the_excerpt( $this->ID );
		}
	}

	/**
	 * get_id getter to get the listing id.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	protected function get_id() {
		return $this->ID;
	}

	/**
	 * get_url returns the permalink of current post.
	 *
	 * @return void
	 */
	protected function get_url( $id = '' ) {
		if ( $id != '' ) {
			$this->url = get_the_permalink( $id );
		}
		return $this->url;
	}

	/**
	 * get_title getter to get the listing title.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	protected function get_title( $id = '' ) {
		if ( $id != '' ) {
			$this->title = get_the_title( $id );
		}
		return $this->title;
	}

	/**
	 * get_content getter to get the listing content.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	protected function get_content( $id = '' ) {
		if ( $id != '' ) {
			$this->content = get_the_content( $id );
		}
		return $this->content;
	}

	/**
	 * get_content getter to get the listing content.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	protected function get_excerpt( $id = '' ) {
		if ( $id != '' ) {
			$excerpt       = get_the_excerpt( $id );
			$excerpt       = substr( $excerpt, 0, 110 );
			$this->excerpt = substr( $excerpt, 0, strrpos( $excerpt, ' ' ) );
		}
		return $this->excerpt;
	}
}
