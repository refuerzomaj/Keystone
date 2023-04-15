<?php
namespace Essential\Restate\Common\Options;

use Essential\Restate\Traitval\Traitval;

/**
 * The admin class
 */
class Options {

	use Traitval;



	protected function __construct() {
	}

	function cl_straight_to_checkout() {
		$ret = cl_admin_get_option( 'redirect_on_add', false );
		$ret = isset( $ret ) && ( $ret == 1 ) ? true : false;
		return (bool) apply_filters( 'cl_straight_to_checkout', $ret );
	}

	function cl_get_currency() {
		$currency = cl_admin_get_option( 'currency', 'USD' );
		return apply_filters( 'currency', $currency );
	}

	function cl_get_single_page_layout() {
		$single_layout = cl_admin_get_option( 'cl_single_settings_layout', '' );
		return apply_filters( 'cl_single_settings_layout', $single_layout );
	}

	function cl_get_default_gateway() {
		$default = cl_admin_get_option( 'default_gateway', 'stripe' );

		return apply_filters( 'cl_default_gateway', $default );
	}

	function cl_item_quantities_enabled() {
		$ret = cl_admin_get_option( 'item_quantities', false );
		$ret = isset( $ret ) && ( $ret == 1 ) ? true : false;
		return (bool) apply_filters( 'cl_item_quantities_enabled', $ret );
	}


	function cl_is_ajax_enabled() {
		$retval = ! cl_is_ajax_disabled();
		return apply_filters( 'cl_is_ajax_enabled', $retval );
	}

	function cl_is_ajax_disabled() {
		return apply_filters( 'cl_is_ajax_disabled', false );
	}

	function cl_use_skus() {
		$ret = cl_admin_get_option( 'enable_skus', false );
		return (bool) apply_filters( 'cl_use_skus', $ret );
	}

	function cl_no_guest_checkout() {
		$ret = cl_admin_get_option( 'logged_in_only', false );
		return (bool) apply_filters( 'cl_no_guest_checkout', $ret );
	}
}
