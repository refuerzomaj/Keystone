<?php
/**
 * Plugin Name: ERE Download Document
 * Plugin URI: https://wordpress.org/plugins/ere-download-document
 * Description: ERE Download Document use for collect name and email of customer before download attachment.
 * Version: 1.0.1
 * Author: G5Theme
 * Author URI: http://g5plus.net
 * Text Domain: ered
 * Domain Path: /languages/
 * License: GPLv2 or later
 *
 **/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('EreDownloadDocument')):
	class EreDownloadDocument
	{
		private static $_instance;

		public static function getInstance()
		{
			if (self::$_instance == null) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public $meta_prefix = 'ered_';

		public function __construct()
		{
			$this->init();
		}

		/**
		 * Init plugin
		 */
		public function init()
		{
			spl_autoload_register(array($this, 'auto_load'));
			add_action('plugins_loaded', array($this, 'load_text_domain'));
			$this->includes();
			$this->db()->init();
			$this->assets()->init();
			$this->ajax()->init();
			$this->admin()->init();
		}

		/**
		 * Autoloader library class for plugin
		 *
		 * @param $class
		 */
		public function auto_load($class)
		{
			$file_name = preg_replace('/^Ered_/', '', $class);
			if ($file_name !== $class) {
				$path      = '';
				$file_name = strtolower($file_name);
				$file_name = str_replace('_', '-', $file_name);
				$this->load_file($this->plugin_dir("inc/{$path}{$file_name}.class.php"));
			}
		}

		public function load_text_domain() {
			load_plugin_textdomain('ered', false, $this->plugin_dir('languages'));
		}

		/**
		 * Include library for plugin
		 */
		public function includes()
		{
			$this->load_file($this->plugin_dir('inc/functions.php'));
		}

		/**
		 * Get plugin directory
		 *
		 * @param string $path
		 *
		 * @return string
		 */
		public function plugin_dir($path = '')
		{
			return plugin_dir_path(__FILE__) . $path;
		}

		/**
		 * Get plugin url
		 *
		 * @param string $path
		 *
		 * @return string
		 */
		public function plugin_url($path = '')
		{
			return trailingslashit(plugins_url(basename(__DIR__))) . $path;
		}

		public function plugin_ver()
		{
			return '1.0';
		}

		/**
		 * Get plugin assets handler
		 *
		 * @param string $handle
		 *
		 * @return string
		 */
		public function assets_handle($handle = '')
		{
			return "ered-{$handle}";
		}

		/**
		 * Get plugin assets url (CSS file or JS file)
		 *
		 * @param $file
		 *
		 * @return string
		 */
		public function asset_url($file)
		{
			return $this->plugin_url(untrailingslashit($file));
		}

		/**
		 * Include library for plugin
		 *
		 * @param $path
		 *
		 * @return bool
		 */
		public function load_file($path)
		{
			if ($path && is_readable($path)) {
				include_once $path;

				return true;
			}

			return false;
		}

		/**
		 * Locate template path from template name
		 *
		 * @param $template_name
		 * @param $args
		 *
		 * @return mixed|string|void
		 */
		public function locate_template($template_name, $args = array())
		{
			$located = '';

			// Theme or child theme template
			$template = trailingslashit(get_stylesheet_directory()) . 'support-ticket-system/' . $template_name;
			if (file_exists($template)) {
				$located = $template;
			}

			// Plugin template
			if (!$located) {
				$located = $this->plugin_dir() . 'templates/' . $template_name;
			}

			$located = apply_filters('ered_locate_template', $located, $template_name, $args);

			// Return what we found.
			return $located;
		}

		/**
		 * Render template
		 *
		 * @param $template_name
		 * @param array $args
		 *
		 * @return mixed|string|void
		 */
		public function get_template($template_name, $args = array())
		{
			if (!empty($args) && is_array($args)) {
				extract($args);
			}

			$located = $this->locate_template($template_name, $args);
			if (!file_exists($located)) {
				_doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $located), '1.0');

				return '';
			}

			do_action('ered_before_template_part', $template_name, $located, $args);
			include($located);
			do_action('ered_after_template_part', $template_name, $located, $args);

			return $located;
		}

		/**
		 * Render plugin template
		 *
		 * @param $template_name
		 * @param array $args
		 *
		 * @return string
		 */
		public function get_plugin_template($template_name, $args = array())
		{
			if ($args && is_array($args)) {
				extract($args);
			}

			$located = $this->plugin_dir($template_name);
			if (!file_exists($located)) {
				_doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $template_name), '1.0');

				return '';
			}

			do_action('ered_before_plugin_template', $template_name, $located, $args);
			include($located);
			do_action('ered_after_plugin_template', $template_name, $located, $args);

			return $located;
		}

		/**
		 * @return Ered_Assets
		 */
		public function assets()
		{
			return Ered_Assets::getInstance();
		}

		/**
		 * @return Ered_Ajax
		 */
		public function ajax()
		{
			return Ered_Ajax::getInstance();
		}

		/**
		 * @return Ered_Database
		 */
		public function db()
		{
			return Ered_Database::getInstance();
		}

		/**
		 * @return Ered_Admin
		 */
		public function admin() {
			return Ered_Admin::getInstance();
		}
	}

	function ERED()
	{
		return EreDownloadDocument::getInstance();
	}

	ERED();
endif;