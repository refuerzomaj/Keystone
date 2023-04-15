<?php
namespace Essential\Restate\Admin\Settings;

use Essential\Restate\Traitval\Traitval;


/**
 * The admin class
 */
class Pages {

	use Traitval;

	public function __construct() {     }

	public static function cl_get_pages( $force = false ) {

		$pages = get_pages();
		$pages_return = array();
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_return[ $page->ID ] = $page->post_title;
			}
		}
		return $pages_return;
	}


	public static function get_setting() {
		return array(

			'page_settings'         => array(
				'id'            => 'page_settings',
				'name'          => '<h3>' . __( 'Pages', 'essential-wp-real-estate' ) . '</h3>',
				'desc'          => '',
				'type'          => 'header',
				'tooltip_title' => __( 'Page Settings', 'essential-wp-real-estate' ),
				'tooltip_desc'  => __( 'Property Listing Plugin uses the pages below for handling the display of checkout, purchase confirmation, purchase history, and purchase failures. If pages are deleted or removed in some way, they can be recreated manually from the Pages menu. When re-creating the pages, enter the shortcode shown in the page content area.', 'essential-wp-real-estate' ),
			),
			'purchase_page'         => array(
				'id'          => 'purchase_page',
				'name'        => __( 'Primary Checkout Page', 'essential-wp-real-estate' ),
				'desc'        => __( 'This is the checkout page where buyers will complete their purchases. The [listing_checkout] shortcode must be on this page.', 'essential-wp-real-estate' ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'success_page'          => array(
				'id'          => 'success_page',
				'name'        => __( 'Success Page', 'essential-wp-real-estate' ),
				'desc'        => __( 'This is the page buyers are sent to after completing their purchases. The [cl_receipt] shortcode should be on this page.', 'essential-wp-real-estate' ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'failure_page'          => array(
				'id'          => 'failure_page',
				'name'        => __( 'Failed Transaction Page', 'essential-wp-real-estate' ),
				'desc'        => __( 'This is the page buyers are sent to if their transaction fails.', 'essential-wp-real-estate' ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'cancel_payment_page'   => array(
				'id'          => 'cancel_payment_page',
				'name'        => __( 'Cancel Payment Page', 'essential-wp-real-estate' ),
				'desc'        => __( 'This is the page buyers are sent to if their transaction is cancelled.', 'essential-wp-real-estate' ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'purchase_history_page' => array(
				'id'          => 'purchase_history_page',
				'name'        => __( 'Purchase History Page', 'essential-wp-real-estate' ),
				'desc'        => __( 'This page shows a complete purchase history for the current user, including listing links. The [purchase_history] shortcode should be on this page.', 'essential-wp-real-estate' ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'register_user_page'    => array(
				'id'          => 'register_user_page',
				'name'        => __( 'Register User Page', 'essential-wp-real-estate' ),
				'desc'        => sprintf(
					__( 'If a customer sign up using the [cl_register_user] shortcode, this is the page they will be redirected to. Note, this can be overridden using the redirect attribute in the shortcode like this: [cl_admin_login redirect="%s"].', 'essential-wp-real-estate' ),
					trailingslashit( home_url() )
				),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'update_user_page'      => array(
				'id'          => 'update_user_page',
				'name'        => __( 'User Profile', 'essential-wp-real-estate' ),
				'desc'        => sprintf(
					__( 'If a customer sign up using the [cl_update_user] shortcode, this is the page they will be redirected to. Note, this can be overridden using the redirect attribute in the shortcode like this: [cl_admin_login redirect="%s"].', 'essential-wp-real-estate' ),
					trailingslashit( home_url() )
				),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'login_redirect_page'   => array(
				'id'          => 'login_redirect_page',
				'name'        => __( 'Login Page', 'essential-wp-real-estate' ),
				'desc'        => sprintf(
					__( 'If a customer logs in using the [cl_admin_login] shortcode, this is the page they will be redirected to. Note, this can be overridden using the redirect attribute in the shortcode like this: [cl_admin_login redirect="%s"].', 'essential-wp-real-estate' ),
					trailingslashit( home_url() )
				),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'compare_page'          => array(
				'id'          => 'compare_page',
				'name'        => __( 'Compare Page', 'essential-wp-real-estate' ),
				'desc'        => sprintf( __( 'The is main compare page. The [cl_compare_listing] shortcode should be on this page.', 'essential-wp-real-estate' ), trailingslashit( home_url() ) ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'dashboard_page'        => array(
				'id'          => 'dashboard_page',
				'name'        => __( 'Listing Dashboard Page', 'essential-wp-real-estate' ),
				'desc'        => sprintf( __( 'The is main listing dashboard page. The [cl_dashboard] shortcode should be on this page.', 'essential-wp-real-estate' ), trailingslashit( home_url() ) ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'cl_add_listing'        => array(
				'id'          => 'cl_add_listing',
				'name'        => __( 'Add Listing Page', 'essential-wp-real-estate' ),
				'desc'        => sprintf( __( 'The is main add listing page. The [cl_add_listing] shortcode should be on this page.', 'essential-wp-real-estate' ), trailingslashit( home_url() ) ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'cl_edit_listing'       => array(
				'id'          => 'cl_edit_listing',
				'name'        => __( 'Edit Listing Page', 'essential-wp-real-estate' ),
				'desc'        => sprintf( __( 'The is main edit listing page. The [cl_add_listing] shortcode should be on this page.', 'essential-wp-real-estate' ), trailingslashit( home_url() ) ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'checkout_page'         => array(
				'id'          => 'checkout_page',
				'name'        => __( 'Checkout Page', 'essential-wp-real-estate' ),
				'desc'        => sprintf( __( 'The is main Checkout Page. The [cl_compare_listing] shortcode should be on this page.', 'essential-wp-real-estate' ), trailingslashit( home_url() ) ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
			'cart_page'             => array(
				'id'          => 'cart_page',
				'name'        => __( 'Cart Page', 'essential-wp-real-estate' ),
				'desc'        => sprintf( __( 'The is main Cart Page. The [cl_compare_listing] shortcode should be on this page.', 'essential-wp-real-estate' ), trailingslashit( home_url() ) ),
				'type'        => 'select',
				'options'     => self::cl_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'essential-wp-real-estate' ),
			),
		);
	}
}
