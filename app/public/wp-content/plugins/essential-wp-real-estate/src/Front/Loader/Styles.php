<?php
namespace Essential\Restate\Front\Loader;

use Essential\Restate\Traitval\Traitval;

/**
 * Loader class loads everything related templates
 *
 * since 1.0.0
 */
class Styles extends Condition {

	use Traitval;

	/**
	 * enque_styles calls wp_enqueue_scripts hooks to load styles
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		/**
		   * Load archive page style
		   */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
	}

	/**
	 * enque_scripts calls wp_enqueue_scripts hooks to load scripts
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		 /**
		 * Load archive page scripts
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	/**
	 * enqueue_frontend_styles loads styles & scripts
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles() {
		wp_enqueue_style( $this->plugin_pref . '-frontend-fontawesome', 'https://use.fontawesome.com/releases/v6.1.1/css/all.css', array(), time(), false );
		wp_enqueue_style( $this->plugin_pref . '-select2', WPERESDS_ASSETS . ( '/lib/select2/select2.min.css' ), array(), time(), false );
		wp_enqueue_style( $this->prefix . 'leaflet', WPERESDS_ASSETS . ( '/css/plugins/leaflet.css' ) );
		wp_enqueue_style( $this->prefix . 'bootstrap', WPERESDS_ASSETS . ( '/css/plugins/bootstrap.min.css' ), array(), time(), false );
		wp_enqueue_style( $this->prefix . 'balloon', WPERESDS_ASSETS . ( '/css/plugins/balloon.min.css' ), array(), time(), false );
		wp_enqueue_style( $this->prefix . 'slick', WPERESDS_ASSETS . ( '/css/plugins/slick.css' ), array(), time(), false );
		wp_enqueue_style( $this->prefix . 'slick-theme', WPERESDS_ASSETS . ( '/css/plugins/slick-theme.css' ), array(), time(), false );
		wp_enqueue_style( $this->prefix . 'responsive', WPERESDS_ASSETS . ( '/css/responsive.css' ), array(), time(), false );
		wp_enqueue_style( $this->prefix . 'var', WPERESDS_ASSETS . ( '/css/var.css' ), array(), time(), false );
		wp_enqueue_style( $this->prefix . 'frontend-layout', WPERESDS_ASSETS . ( '/css/pages/frontend_layout.css' ), array(), time(), false );
		wp_enqueue_style( $this->plugin_pref . '-frontend-style', WPERESDS_ASSETS . ( '/css/styles.css' ), array(), time(), false );
		wp_enqueue_style( $this->plugin_pref . '-leaflet-markercluster', WPERESDS_ASSETS . ( '/lib/leaflet-markercluster/MarkerCluster.css' ) );
		wp_enqueue_style( $this->plugin_pref . '-leaflet-markercluster-default', WPERESDS_ASSETS . ( '/lib/leaflet-markercluster/MarkerCluster.Default.css' ) );
	}

	/**
	 * enqueue_frontend_scripts loads styles & scripts
	 *
	 * @return void
	 */
	public function enqueue_frontend_scripts() {
		global $post;

		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( $this->plugin_pref . '-slick', WPERESDS_ASSETS . ( '/js/slick.js' ), array( 'jquery' ), '', true );
		wp_enqueue_script( $this->plugin_pref . '-select2', WPERESDS_ASSETS . ( '/lib/select2/select2.min.js' ), array('jquery'), time(), false );
		wp_enqueue_script( $this->plugin_pref . '-frontend-script', WPERESDS_ASSETS . ( '/js/scripts.js' ), array('jquery'), time(), true );
		wp_enqueue_script( $this->plugin_pref . '-frontend-main', WPERESDS_ASSETS . ( '/js/main.js' ), array('jquery'), time(), true );
		wp_enqueue_script( $this->plugin_pref . '-leaflet', WPERESDS_ASSETS . ( '/js/leaflet.js' ), array('jquery'), time(), false );
		wp_enqueue_script( $this->plugin_pref . '-leaflet-markercluster', WPERESDS_ASSETS . ( '/lib/leaflet-markercluster/leaflet.markercluster.js' ), array('jquery'), time(), false );
		wp_enqueue_script( $this->plugin_pref . '-frontend-map', WPERESDS_ASSETS . ( '/js/map.js' ), array('jquery'), time(), true );

		$package_price = '';
		if(is_user_logged_in()){
			$current_user  = wp_get_current_user();
			$package_args  = array(
				'post_type'      => 'cl_payment',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => '_cl_payment_user_email',
						'value' => $current_user->data->user_email,
					),
				),
			);
			$package_query = new \WP_Query( $package_args );
			if ( $package_query->posts ) {
				foreach ( $package_query->posts as $key => $post ) {
					$package_name = get_post_meta( $post->ID, '_cl_payment_meta', true );
					$package_id   = $package_name['cart_details'][0]['id'];
					$package_price   = $package_name['cart_details'][0]['price'];
				}
				wp_reset_postdata();
			}
			
		}


		$ajax_var = array(
			'ajax_url'              => esc_url( admin_url( 'admin-ajax.php' ) ),
			'site_url'              => esc_url( site_url() ),
			'abuse_dialog'          => esc_html( 'Report Abuse' ),
			'checkout_nonce'        => wp_create_nonce( 'cl_checkout_nonce' ),
			'checkout_error_anchor' => '#cl_purchase_submit',
			'currency_sign'         => WPERECCP()->common->formatting->cl_currency_filter( '' ),
			'currency_pos'          => cl_admin_get_option( 'currency_position', 'before' ),
			'decimal_separator'     => cl_admin_get_option( 'decimal_separator', '.' ),
			'thousands_separator'   => cl_admin_get_option( 'thousands_separator', ',' ),
			'number_of_decimal'     => cl_admin_get_option( 'number_of_decimal', '0' ),
			'no_gateway'            => __( 'Please select a payment method', 'essential-wp-real-estate' ),
			'no_discount'           => __( 'Please enter a discount code', 'essential-wp-real-estate' ), // Blank discount code message
			'enter_discount'        => __( 'Enter discount', 'essential-wp-real-estate' ),
			'discount_applied'      => __( 'Discount Applied', 'essential-wp-real-estate' ), // Discount verified message
			'no_email'              => __( 'Please enter an email address before applying a discount code', 'essential-wp-real-estate' ),
			'no_username'           => __( 'Please enter a username before applying a discount code', 'essential-wp-real-estate' ),
			'purchase_loading'      => __( 'Please Wait...', 'essential-wp-real-estate' ),
			'add_compare_text'      => __( 'Item added to compare.', 'essential-wp-real-estate' ),
			'complete_purchase'     => WPERECCP()->front->checkout->cl_get_checkout_button_purchase_label(),
			'taxes_enabled'         => WPERECCP()->front->tax->cl_use_taxes() ? '1' : '0',
			'package_price'            => $package_price,
			'add_listing_gallery_limit'  => __( "Can't upload over 5 photos for free plan", 'essential-wp-real-estate' ),
			'cl_version'            => WPERESDS_VERSION,
		);


		
		wp_localize_script( $this->plugin_pref . '-frontend-main', 'ajax_obj', $ajax_var );
	}
}
