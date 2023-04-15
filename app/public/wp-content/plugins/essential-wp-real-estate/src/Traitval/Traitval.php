<?php
namespace Essential\Restate\Traitval;

trait Traitval {

	private static $singleton = false;
	public $plugin_pref       = 'essential-wp-real-estate';
	public $prefix            = 'wperesds_';
	public $cl_cpt            = 'cl_cpt';


	/**
	 * Initialize the trait
	 */
	private function __construct() {
		$this->initialize();
	}

	protected function initialize() {
	}
	public static function getInstance() {
		if ( self::$singleton === false ) {
			self::$singleton = new self();
		}
		return self::$singleton;
	}
	public function get_cpt_lists( $main = false ) {
		if ( $main ) {
			return $this->cl_cpt;
		}
		return apply_filters( $this->prefix . 'append_cpt_lists', array( $this->cl_cpt ), $this->cl_cpt );
	}
}
