<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Essential\Restate\Admin\Admin;
use Essential\Restate\Front\Front;
use Essential\Restate\Common\Common;
use Essential\Restate\Common\Ajax\Ajax;
use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Common\Customer\Dbcustomer;
use Essential\Restate\Common\Customer\Customermeta;
use Essential\Restate\Common\Roles\Roles;

final class WPEssentialRealEstate {

	use Traitval;

	/**
	 * Plugin Version
	 *
	 * @since 1.2.0
	 * @var string The plugin version.
	 */

	private static $instance;
	public $admin;
	public $front;
	public $common;
	public $ajax;

	private function __construct() {
		$this->define_constants();
		register_activation_hook( WPERESDS_FILE, array( $this, 'activate' ) );

		add_action( 'activated_plugin', array( $this, 'activation_handler1' ) );
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wperesds_enqueue_scripts' ) );
		add_action( 'pre_current_active_plugins', array( $this, 'pre_output1' ) );
	}

	/**
	 * Define the required plugin constants
	 *
	 * @return void
	 */
	public function define_constants() {
		// general constants
		define( 'WPERESDS_VERSION', WPERESDS_PLUGIN_VERSION );
		define( 'WPERESDS_URL', plugins_url( '', WPERESDS_FILE ) );
		define( 'WPERESDS_ASSETS', WPERESDS_URL . '/assets' );
		define( 'WPERESDS_ASSETS_CSS', WPERESDS_ASSETS . '/css' );
		define( 'WPERESDS_TEMPLATES_DIR', WPERESDS_PATH . '/templates' );

		define( 'WPERESDS_DIR', plugin_dir_path( WPERESDS_FILE ) );
		define( 'WPERESDS_ASSETS_DIR', WPERESDS_DIR . '/assets' );
		define( 'WPERESDS_ASSETS_CSS_DIR', WPERESDS_ASSETS_DIR . '/css' );

		// src constanst
		define( 'WPERESDS_SRC_FILE', __FILE__ );
		define( 'WPERESDS_SRC_PATH', __DIR__ );

		// constants for theme
		$theme = wp_get_theme();
		define( 'THEME_VERSION_CORE', $theme->Version );
		define( 'temp_file', ABSPATH . '/_temp_out.txt' );

		if ( ! defined( 'CLS_PLUGIN_DIR' ) ) {
			define( 'CLS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		if ( ! defined( 'CLS_PLUGIN_URL' ) ) {
			define( 'CLS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}
	}





	function activation_handler1() {
		$cont = ob_get_contents();
		if ( ! empty( $cont ) ) {
			file_put_contents( temp_file, $cont );
		}
	}


	function pre_output1( $action ) {
		// debug_print_backtrace();
		if ( is_admin() && file_exists( temp_file ) ) {
			$cont = file_get_contents( temp_file );
			if ( ! empty( $cont ) ) {
				echo '<div class="error"> ' . esc_html__( 'Error Message:', 'essential-wp-real-estate' ) . $cont . '</div>';
				@unlink( temp_file );
			}
		}
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {
		self::$instance         = self::getInstance();
		self::$instance->common = Common::getInstance();
		self::$instance->front  = Front::getInstance();

		if ( is_admin() ) {
			self::$instance->admin = Admin::getInstance();
		}
	}

	/**
	 * Do stuff upon plugin activation
	 *
	 * @return void
	 */
	public function cl_run_install() {

		$a = new Dbcustomer();
		$b = new Customermeta();
		$c = new Roles();
		$c->add_roles();
		$c->add_caps();
		@$a->create_table();
		@$b->create_table();

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$schema          = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}enquiry_message` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `name` varchar(100) NOT NULL DEFAULT '',
          `email` varchar(30) DEFAULT NULL,
          `phone` varchar(30) DEFAULT NULL,
          `message` varchar(255) DEFAULT NULL,
          `created_for` bigint(20) unsigned NOT NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) $charset_collate";

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		dbDelta( $schema );

	}
	function activate( $network_wide = false ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) {
			foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->cl_run_install();
				restore_current_blog();
			}
		} else {

			$this->cl_run_install();
		}

	}

	public function wperesds_enqueue_scripts() {
		wp_enqueue_style( $this->plugin_pref . '-admin-style', WPERESDS_ASSETS . '/css/admin.css', '', time() );
		wp_enqueue_style( $this->plugin_pref . '-admin-fontawesome', 'https://use.fontawesome.com/releases/v6.1.1/css/all.css', '', time() );

		wp_enqueue_script( $this->plugin_pref . '-admin-ajax', WPERESDS_ASSETS . ( '/js/admin-ajax.js' ), array(), time(), false );

		$ajax_var = array(
			'ajax_url'              => esc_url( admin_url( 'admin-ajax.php' ) ),
			'site_url'              => esc_url( site_url() )
		);
		wp_localize_script( $this->plugin_pref . '-admin-ajax', 'ajax_obj', $ajax_var );
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

}

/**
 * Initializes the main plugin
 *
 * @return \WPEssentialRealEstate
 */
function WPERECCP() {
	return WPEssentialRealEstate::getInstance();
}

// kick-off the plugin
WPERECCP();