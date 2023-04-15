<?php
/**
 * Discount Actions
 *
 * @package     CL
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up and stores a new discount code
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses cl_store_discount()
 * @return void
 */
function cl_add_discount( $data ) {
	if ( ! isset( $data['cl-discount-nonce'] ) || ! wp_verify_nonce( $data['cl-discount-nonce'], 'cl_discount_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	// Setup the discount code details
	$posted = array();

	if ( empty( $data['name'] ) || empty( $data['code'] ) || empty( $data['type'] ) || empty( $data['amount'] ) ) {
		wp_redirect( add_query_arg( 'cl-message', 'discount_validation_failed' ) );
		die();
	}

	// Verify only accepted characters
	$sanitized = preg_replace( '/[^a-zA-Z0-9-_]+/', '', $data['code'] );
	if ( strtoupper( $data['code'] ) !== strtoupper( $sanitized ) ) {
		wp_redirect( add_query_arg( 'cl-message', 'discount_invalid_code' ) );
		die();
	}

	if ( ! is_numeric( $data['amount'] ) ) {
		wp_redirect( add_query_arg( 'cl-message', 'discount_invalid_amount' ) );
		die();
	}

	foreach ( $data as $key => $value ) {

		if ( $key === 'products' || $key === 'excluded-products' ) {

			foreach ( $value as $product_key => $product_value ) {
				$value[ $product_key ] = preg_replace( '/[^0-9_]/', '', $product_value );
			}

			$posted[ $key ] = $value;
		} elseif ( $key != 'cl-discount-nonce' && $key != 'cl-action' && $key != 'cl-redirect' ) {

			if ( is_string( $value ) || is_int( $value ) ) {

				$posted[ $key ] = strip_tags( addslashes( $value ) );
			} elseif ( is_array( $value ) ) {

				$posted[ $key ] = array_map( 'absint', $value );
			}
		}
	}

	// Ensure this discount doesn't already exist
	if ( ! WPERECCP()->admin->discount_action->cl_get_discount_by_code( $posted['code'] ) ) {

		// Set the discount code's default status to active
		$posted['status'] = 'active';

		if ( WPERECCP()->admin->discount_action->cl_store_discount( $posted ) ) {

			wp_redirect( add_query_arg( 'cl_discount_added', '1', $data['cl-redirect'] ) );
			die();
		} else {

			wp_redirect( add_query_arg( 'cl-message', 'discount_add_failed', $data['cl-redirect'] ) );
			die();
		}
	} else {

		wp_redirect( add_query_arg( 'cl-message', 'discount_exists', $data['cl-redirect'] ) );
		die();
	}
}
add_action( 'cl_add_discount', 'cl_add_discount' );

/**
 * Saves an edited discount
 *
 * @since 1.0
 * @param array $data Discount code data
 * @return void
 */
function cl_edit_discount( $data ) {

	if ( ! isset( $data['cl-discount-nonce'] ) || ! wp_verify_nonce( $data['cl-discount-nonce'], 'cl_discount_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	if ( empty( $data['amount'] ) || ! is_numeric( $data['amount'] ) ) {
		wp_redirect( add_query_arg( 'cl-message', 'discount_invalid_amount' ) );
		wp_die();
	}

	// Setup the discount code details
	$discount = array();

	foreach ( $data as $key => $value ) {

		if ( $key === 'products' || $key === 'excluded-products' ) {

			foreach ( $value as $product_key => $product_value ) {
				$value[ $product_key ] = preg_replace( '/[^0-9_]/', '', $product_value );
			}

			$discount[ $key ] = $value;
		} elseif ( $key != 'cl-discount-nonce' && $key != 'cl-action' && $key != 'discount-id' && $key != 'cl-redirect' ) {

			if ( is_string( $value ) || is_int( $value ) ) {

				$discount[ $key ] = strip_tags( addslashes( $value ) );
			} elseif ( is_array( $value ) ) {

				$discount[ $key ] = array_map( 'absint', $value );
			}
		}
	}

	if ( WPERECCP()->admin->discount_action->cl_store_discount( $discount, $data['discount-id'] ) ) {

		wp_redirect( add_query_arg( 'cl_discount_updated', '1', $data['cl-redirect'] ) );
		wp_die();
	} else {

		wp_redirect( add_query_arg( 'cl-message', 'discount_update_failed', $data['cl-redirect'] ) );
		wp_die();
	}
}
add_action( 'cl_edit_discount', 'cl_edit_discount' );

/**
 * Listens for when a discount delete button is clicked and deletes the
 * discount code
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses cl_remove_discount()
 * @return void
 */
function cl_delete_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'cl_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to delete discount codes', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	$discount_id = $data['discount'];
	WPERECCP()->front->discountaction->cl_remove_discount( $discount_id );
}
add_action( 'cl_delete_discount', 'cl_delete_discount' );

/**
 * Activates Discount Code
 *
 * Sets a discount code's status to active
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses cl_update_discount_status()
 * @return void
 */
function cl_activate_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'cl_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['discount'] );
	WPERECCP()->admin->discount_action->cl_update_discount_status( $id, 'active' );
}
add_action( 'cl_activate_discount', 'cl_activate_discount' );

/**
 * Deactivate Discount
 *
 * Sets a discount code's status to deactivate
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses cl_update_discount_status()
 * @return void
 */
function cl_deactivate_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'cl_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'essential-wp-real-estate' ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['discount'] );
	WPERECCP()->admin->discount_action->cl_update_discount_status( $id, 'inactive' );
}
add_action( 'cl_deactivate_discount', 'cl_deactivate_discount' );
