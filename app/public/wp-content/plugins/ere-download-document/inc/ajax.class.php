<?php
/**
 * Class Ajax
 *
 */
if (!defined('ABSPATH')) {
	exit('Direct script access denied.');
}
if (!class_exists('Ered_Ajax')) {
	class Ered_Ajax
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
			// Download Document Ajax
			add_action('wp_ajax_nopriv_ered_download_document', array($this,'download_document'));
			add_action('wp_ajax_ered_download_document', array($this,'download_document'));

			// Update Email (allow user logged in access only)
			add_action('wp_ajax_ered_change_email', array($this,'change_email'));
			add_action('wp_ajax_ered_delete_email', array($this,'delete_email'));
		}

		public function download_document() {
			if (!isset($_POST['ered_download_document_nonce'])
			    || !wp_verify_nonce($_POST['ered_download_document_nonce'], 'ered_download_document_action')
			) {
				wp_send_json_error(esc_html__('Access Deny! Refresh website and try again!','ered'));
			}

			$url = esc_url_raw($_POST['url']);
			$full_name = sanitize_text_field($_POST['full_name']);
			$email_address = sanitize_email($_POST['email_address']);


			if (is_email($email_address) === false) {
				wp_send_json_error(esc_html__('The email address is incorrect!','ered'));
			}

			if (ERED()->db()->insertEmail($full_name, $email_address, $url)) {
				$ered_settings = get_option('ered_settings', array());
				$email_notification = isset($ered_settings['email_address']) ? $ered_settings['email_address'] : '';

				if ($email_notification !== '') {
					$subject = sprintf(esc_html__('[%s] has just downloaded file','ered'), $email_address);
					$message = $subject . sprintf(' [%s]', $url, $url);

					wp_mail($email_notification, $subject, $message);
				}

			}
			else {
				wp_send_json_error(esc_html__('Error! Refresh website and try again!','ered'));
			}

			wp_send_json_success($url);
		}

		public function change_email() {
			if (!isset($_POST['ered_change_email_nonce'])
			    || !wp_verify_nonce($_POST['ered_change_email_nonce'], 'ered_change_email_action')
			) {
				wp_send_json_error(esc_html__('Access Deny! Refresh website and try again!','ered'));
			}

			$id = sanitize_text_field($_POST['id']);
			$name = sanitize_text_field($_POST['name']);
			$email = sanitize_email($_POST['email']);

			if (is_email($email) === false) {
				wp_send_json_error(esc_html__('The email address is incorrect!','ered'));
			}

			ERED()->db()->updateEmail($id, $name, $email);
			wp_send_json_success(array(
				'name' => $name,
				'email' => $email
			));
		}

		public function delete_email() {
			if (!isset($_POST['ered_delete_email_nonce'])
			    || !wp_verify_nonce($_POST['ered_delete_email_nonce'], 'ered_delete_email_action')
			) {
				wp_send_json_error(esc_html__('Access Deny! Refresh website and try again!','ered'));
			}
			$id = sanitize_text_field($_POST['id']);
			$res = ERED()->db()->deleteEmail($id);
			wp_send_json_success($res . '');
		}
	}
}