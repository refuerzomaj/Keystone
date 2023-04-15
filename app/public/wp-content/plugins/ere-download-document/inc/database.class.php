<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Ered_Database' ) ) {
	class Ered_Database {
		private static $_instance;
		public $limit_user = 5;

		public static function getInstance() {
			if ( self::$_instance == null ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function init() {
			add_action( 'init', array( $this, 'create_database' ) );
		}

		public function tableDownload() {
			global $wpdb;

			return $wpdb->prefix . 'ered_email_download';
		}

		public function create_database() {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			global $wpdb;
			$charset_collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) {
				$charset_collate = $wpdb->get_charset_collate();
			}

			$sql = "CREATE TABLE {$this->tableDownload()} (
                        id mediumint(9) NOT NULL AUTO_INCREMENT,
                        full_name varchar(200) NOT NULL,
                        email varchar(200) NOT NULL,
                        document_link text NOT NULL,
                        created_date datetime NOT NULL,
                        PRIMARY KEY  (id)
                        ) {$charset_collate};
                        ";
			dbDelta( $sql );
		}

		public function getEmailList( $page = 1 ) {
			global $wpdb;
			$page_size   = 30;
			$offset      = ( $page - 1 ) * $page_size;
			$total_items = intval( $wpdb->get_var( "select COUNT(*) from {$this->tableDownload()}" ) );
			$total_page  = ceil( $total_items / $page_size );

			$items = $wpdb->get_results( $wpdb->prepare( "select id, full_name, email, document_link, created_date from {$this->tableDownload()} ORDER BY created_date limit %d offset %d", $page_size, $offset ) );

			return array(
				'items' => $items,
				'total' => $total_page
			);
		}

		public function insertEmail( $full_name, $email, $link ) {
			global $wpdb;

			return $wpdb->insert( $this->tableDownload(), array(
				'full_name' => $full_name,
				'email'         => $email,
				'document_link' => $link,
				'created_date'  => date( "Y-m-d H:i:s" )
			) );
		}

		public function updateEmail( $id, $name, $email ) {
			global $wpdb;

			return $wpdb->update(
				$this->tableDownload(),
				array(
					'full_name' => $name,
					'email' => $email
				),
				array( 'id' => $id )
			);
		}

		public function deleteEmail($id) {
			global $wpdb;

			return $wpdb->delete(
				$this->tableDownload(),
				array( 'id' => $id )
			);
		}
	}
}