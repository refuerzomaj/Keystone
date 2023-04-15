<?php
namespace  Essential\Restate\Front\Purchase\Gateways\Woocommerce;

use Essential\Restate\Traitval\Traitval;

class Woocommerce {

	use Traitval;

	public function __construct() {

	}

	public static function get_setting() {

		if ( class_exists( 'WooCommerce' ) && class_exists( 'WooCommerce_Payments_for_Listings' ) ) {
			return array(
				'woocommerce_active'        => array(
					'id'   => 'woocommerce_active',
					'name' => __( 'WooCommerce Active', 'essential-wp-real-estate' ),
					'type' => 'checkbox',
				),
		
			);
		
		}else{
			return array('');
		}

	}


}
