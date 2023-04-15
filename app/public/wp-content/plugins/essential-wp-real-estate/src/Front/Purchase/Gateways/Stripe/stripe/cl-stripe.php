<?php
if ( ! function_exists( 'CCL' ) ) {
	return;
}

	// Stripe is already active, do nothing.
if ( class_exists( 'CL_Stripe' ) ) {
	return;
}
if ( ! defined( 'CLS_PLUGIN_DIR' ) ) {
	define( 'CLS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CLS_PLUGIN_URL' ) ) {
	define( 'CLS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'CL_STRIPE_PLUGIN_FILE' ) ) {
	define( 'CL_STRIPE_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'CL_STRIPE_VERSION' ) ) {
	define( 'CL_STRIPE_VERSION', '2.8.9' );
}

if ( ! defined( 'CL_STRIPE_API_VERSION' ) ) {
	define( 'CL_STRIPE_API_VERSION', '2020-03-02' );
}

if ( ! defined( 'CL_STRIPE_PARTNER_ID' ) ) {
	define( 'CL_STRIPE_PARTNER_ID', 'pp_partner_DKh7NDe3Y5G8XG' );
}

require_once __DIR__ . '/includes/class-cl-stripe.php';
// Initial instantiation.
CL_Stripe::instance();

