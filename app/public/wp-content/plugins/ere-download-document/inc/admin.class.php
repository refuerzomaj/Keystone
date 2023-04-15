<?php
/**
 * Class Ajax
 *
 */
if (!defined('ABSPATH')) {
	exit('Direct script access denied.');
}
if (!class_exists('Ered_Admin')) {
	class Ered_Admin
	{
		private static $_instance;
		public static function getInstance()
		{
			if (self::$_instance == NULL) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function init() {
			add_action('admin_menu', array($this, 'add_admin_menu'));
			add_action('wp_loaded', array($this, 'save_settings'));
		}

		public function add_admin_menu() {
			add_menu_page(
				esc_html__('ERE Download Document', 'ered'),
				esc_html__('ERE Download Document', 'ered'),
				'manage_options',
				'ered-settings',
				array($this, 'download_document_page'),
				'dashicons-download',
				30
				);
			add_submenu_page(
				'ered-settings',
				esc_html__('Settings','ered'),
				esc_html__('Settings','ered'),
				'manage_options',
				"ered-settings",
				array( $this, 'setting_page' )
			);
			add_submenu_page(
				'ered-settings',
				esc_html__('Download Management','ered'),
				esc_html__('Download Management','ered'),
				'manage_options',
				"ered-download-management",
				array( $this, 'download_management_page' )
			);
		}

		public function download_document_page() {
		}

		public function setting_page() {
			ERED()->get_plugin_template('admin/templates/settings.php');
		}

		public function download_management_page() {
			ERED()->get_plugin_template('admin/templates/download-management.php');
		}

		public function save_settings() {
			if (!isset($_POST['ered_settings_page_nonce'])
			    || !wp_verify_nonce($_POST['ered_settings_page_nonce'], 'ered_settings_page_action')
			) {
				return;
			}
			if (!user_can(get_current_user_id(), 'manage_options')) {
				return;
			}

			$ered_settings = array(
				'popup_title' => sanitize_text_field($_POST['popup_title']),
				'popup_subtitle' => sanitize_text_field($_POST['popup_subtitle']),
				'email_address' => sanitize_email($_POST['email_address'])
			);
			update_option('ered_settings', $ered_settings);

			add_settings_error(
				'page_for_ered_setting',
				'page_for_ered_setting',
				esc_html__('Settings page updated successfully.', 'ered'),
				'updated'
			);
		}
	}
}