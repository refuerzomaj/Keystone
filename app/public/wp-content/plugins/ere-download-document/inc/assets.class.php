<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if (!class_exists('Ered_Assets')) {
	class Ered_Assets
	{
		private static $_instance;

		public static function getInstance()
		{
			if (self::$_instance == null) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function init()
		{
			add_action('init', array($this, 'register_assets'));
			add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_assets' ) );
		}

		public function register_assets()
		{
			// Vendors assets
			wp_register_style('magnific-popup', ERED()->asset_url('assets/vendors/magnific-popup/magnific-popup.min.css'), array(), ERED()->plugin_ver());
			wp_register_script('magnific-popup' , ERED()->asset_url('assets/vendors/magnific-popup/jquery.magnific-popup.min.js'), array('jquery'), '1.1.0', true);

			wp_register_style('ladda', ERED()->asset_url('assets/vendors/ladda/ladda-themeless.min.css'), array(), '1.0.5');
			wp_register_script('ladda-spin', ERED()->asset_url('assets/vendors/ladda/spin.min.js'), array('jquery'), '1.0.5', true);
			wp_register_script('ladda', ERED()->asset_url('assets/vendors/ladda/ladda.min.js'), array(
				'jquery',
				'ladda-spin'
			), '1.0.5', true);
			wp_register_script('ladda-jquery', ERED()->asset_url('assets/vendors/ladda/ladda.jquery.min.js'), array(
				'jquery',
				'ladda'
			), '1.0.5', true);

			// Plugin assets
			wp_register_style(ERED()->assets_handle('ered'), ERED()->asset_url('assets/css/ered.css'), array(), ERED()->plugin_ver());
			wp_register_script(ERED()->assets_handle('ered'), ERED()->asset_url('assets/js/ered.js'), array('jquery'), ERED()->plugin_ver(), true);

			// Plugin admin assets
			wp_register_style(ERED()->assets_handle('ered-admin'), ERED()->asset_url('assets/css/ered-admin.css'), array(), ERED()->plugin_ver());
			wp_register_script(ERED()->assets_handle('ered-admin'), ERED()->asset_url('assets/js/ered-admin.js'), array('jquery','jquery-ui-core'), ERED()->plugin_ver(), true);
		}

		public function enqueue_assets()
		{
			wp_enqueue_style('magnific-popup');
			wp_enqueue_script('magnific-popup');

			wp_enqueue_style('ladda');
			wp_enqueue_script('ladda-jquery');

			wp_enqueue_style(ERED()->assets_handle('ered'));
			wp_enqueue_script(ERED()->assets_handle('ered'));
		}

		public function enqueue_backend_assets() {
			wp_enqueue_style('magnific-popup');
			wp_enqueue_script('magnific-popup');

			wp_enqueue_style('ladda');
			wp_enqueue_script('ladda-jquery');

			wp_enqueue_style(ERED()->assets_handle('ered-admin'));
			wp_enqueue_script(ERED()->assets_handle('ered-admin'));
		}
	}
}