<?php
/**
 * Manage deprecations.
 *
 * @package CL_Stripe
 * @since   2.7.0
 */

/**
 * Process stripe checkout submission
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function cls_process_stripe_payment( $purchase_data ) {
	_cl_deprecated_function( 'cls_process_stripe_payment', '2.7.0', 'cls_process_purchase_form', debug_backtrace() );

	return cls_process_purchase_form( $purchase_data );
}

/**
 * Database Upgrade actions
 *
 * @access      public
 * @since       2.5.8
 * @return      void
 */
function cls_plugin_database_upgrades() {
	_cl_deprecated_function(
		__FUNCTION__,
		'2.8.1',
		null,
		debug_backtrace()
	);

	cl_stripe()->database_upgrades();
}

/**
 * Internationalization
 *
 * @since       1.6.6
 * @return      void
 */
function cls_textdomain() {
	_cl_deprecated_function(
		__FUNCTION__,
		'2.8.1',
		null,
		debug_backtrace()
	);

	cl_stripe()->load_textdomain();
}

/**
 * Register our payment gateway
 *
 * @since       1.0
 * @return      array
 */
function cls_register_gateway( $gateways ) {
	_cl_deprecated_function(
		__FUNCTION__,
		'2.8.1',
		null,
		debug_backtrace()
	);

	return cl_stripe()->register_gateway( $gateways );
}
