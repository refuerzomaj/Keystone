<?php
namespace Essential\Restate\Admin\Adminnotice;

class Adminnotice {


	/**
	 * Get things started
	 *
	 * @since 2.3
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'cl_dismiss_notices', array( $this, 'dismiss_notices' ) );
	}

	/**
	 * Show relevant notices
	 *
	 * @since 2.3
	 */
	public function show_notices() {
		$notices = array(
			'updated' => array(),
			'error'   => array(),
		);

		// Global (non-action-based) messages
		if ( ( cl_admin_get_option( 'purchase_page', '' ) == '' || 'trash' == get_post_status( cl_admin_get_option( 'purchase_page', '' ) ) ) && current_user_can( 'edit_pages' ) && ! get_user_meta( get_current_user_id(), '_cl_set_checkout_dismissed' ) ) {
			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'essential-wp-real-estate' ), admin_url( 'edit.php?post_type=listing&page=cl-settings' ) ); ?></p>
				<p><a href="
				<?php
				echo esc_url(
					add_query_arg(
						array(
							'cl_action' => 'dismiss_notices',
							'cl_notice' => 'set_checkout',
						)
					)
				);
				?>
							"><?php _e( 'Dismiss Notice', 'essential-wp-real-estate' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}

		if ( isset( $_GET['page'] ) && 'cl-payment-history' == cl_sanitization($_GET['page']) && current_user_can( 'view_shop_reports' ) && cl_is_test_mode() ) {
			$notices['updated']['cl-payment-history-test-mode'] = sprintf( __( 'Note: Test Mode is enabled. While in test mode no live transactions are processed. <a href="%s">Settings</a>.', 'essential-wp-real-estate' ), admin_url( 'edit.php?post_type=cl_cpt&page=listing_settings_func&tab=payments' ) );
		}

		$show_nginx_notice = apply_filters( 'cl_show_nginx_redirect_notice', true );
		$server_software   = isset( $_SERVER['SERVER_SOFTWARE'] ) ? wp_unslash( cl_sanitization( $_SERVER['SERVER_SOFTWARE'] ) ) : false;

		if ( $show_nginx_notice && stristr( $server_software, 'nginx' ) && ! get_user_meta( get_current_user_id(), '_cl_nginx_redirect_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {

			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'The listing files in %s are not currently protected due to your site running on NGINX.', 'essential-wp-real-estate' ), '<strong>' . cl_get_upload_dir() . '</strong>' ); ?></p>
				 
				<p><?php _e( 'If you have already added the redirect rule, you may safely dismiss this notice', 'essential-wp-real-estate' ); ?></p>
				<p><a href="
				<?php
				echo esc_url(
					add_query_arg(
						array(
							'cl_action' => 'dismiss_notices',
							'cl_notice' => 'nginx_redirect',
						)
					)
				);
				?>
							"><?php _e( 'Dismiss Notice', 'essential-wp-real-estate' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}

		/**
		 * Notice for users running PHP < 5.6.
		 *
		 * @since 2.10
		 */
		if ( version_compare( PHP_VERSION, '5.6', '<' ) && ! get_user_meta( get_current_user_id(), '_cl_upgrade_php_56_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
			echo '<div class="notice notice-warning is-dismissible cl-notice">';
			printf(
				'<h2>%s</h2>',
				esc_html__( 'Upgrade PHP to Prepare for Property Listing Plugin', 'essential-wp-real-estate' )
			);
			echo wp_kses_post(
				sprintf(
					/*
					 translators:
					%1$s Opening paragraph tag, do not translate.
					%2$s Current PHP version
					%3$s Opening strong tag, do not translate.
					%4$s Closing strong tag, do not translate.
					%5$s Opening anchor tag, do not translate.
					%6$s Closing anchor tag, do not translate.
					%7$s Closing paragraph tag, do not translate.
					*/
					__( '%1$sYour site is running an outdated version of PHP (%2$s), which requires an update. Property Listing Plugin  will require %3$sPHP 5.6 or greater%4$s in order to keep your store online and selling. While 5.6 is the minimum version we will be supporting, we encourage you to update to the most recent version of PHP that your hosting provider offers. %5$sLearn more about updating PHP.%6$s%7$s', 'essential-wp-real-estate' ),
					'<p>',
					PHP_VERSION,
					'<strong>',
					'</strong>',
					'<a href="https://wordpress.org/support/update-php/" target="_blank" rel="noopener noreferrer">',
					'</a>',
					'</p>'
				)
			);
			echo wp_kses_post(
				sprintf(
					/*
					 translators:
					%1$s Opening paragraph tag, do not translate.
					%2$s Opening anchor tag, do not translate.
					%3$s Closing anchor tag, do not translate.
					%4$s Closing paragraph tag, do not translate.
					*/
					__( '%1$sMany web hosts can give you instructions on how/where to upgrade your version of PHP through their control panel, or may even be able to do it for you. If you need to change hosts, please see %2$sour hosting recommendations%3$s.', 'essential-wp-real-estate' ),
					'<p>',
					'<a href="#" target="_blank" rel="noopener noreferrer">',
					'</a>',
					'</p>'
				)
			);
			echo wp_kses_post(
				sprintf(
					/* Translators: %1$s - Opening anchor tag, %2$s - The url to dismiss the ajax notice, %3$s - Complete the opening of the anchor tag, %4$s - Open span tag, %4$s - Close span tag */
					__( '%1$s%2$s%3$s %4$s Dismiss this notice. %5$s', 'essential-wp-real-estate' ),
					'<a href="',
					esc_url(
						add_query_arg(
							array(
								'cl_action' => 'dismiss_notices',
								'cl_notice' => 'upgrade_php_56',
							)
						)
					),
					'" type="button" class="notice-dismiss">',
					'<span class="screen-reader-text">',
					'</span>
					</a>'
				)
			);
			echo '</div>';
		}

		if ( isset( $_GET['cl-message'] ) ) {
			// Shop discounts errors
			if ( current_user_can( 'manage_shop_discounts' ) ) {
				$msg_case = cl_sanitization($_GET['cl-message']);
				switch ( $msg_case ) {
					case 'discount_added':
						$notices['updated']['cl-discount-added'] = __( 'Discount code added.', 'essential-wp-real-estate' );
						break;
					case 'discount_add_failed':
						$notices['error']['cl-discount-add-fail'] = __( 'There was a problem adding your discount code, please try again.', 'essential-wp-real-estate' );
						break;
					case 'discount_exists':
						$notices['error']['cl-discount-exists'] = __( 'A discount with that code already exists, please use a different code.', 'essential-wp-real-estate' );
						break;
					case 'discount_updated':
						$notices['updated']['cl-discount-updated'] = __( 'Discount code updated.', 'essential-wp-real-estate' );
						break;
					case 'discount_update_failed':
						$notices['error']['cl-discount-updated-fail'] = __( 'There was a problem updating your discount code, please try again.', 'essential-wp-real-estate' );
						break;
					case 'discount_validation_failed':
						$notices['error']['cl-discount-validation-fail'] = __( 'The discount code could not be added because one or more of the required fields was empty, please try again.', 'essential-wp-real-estate' );
						break;
					case 'discount_invalid_code':
						$notices['error']['cl-discount-invalid-code'] = __( 'The discount code entered is invalid; only alphanumeric characters are allowed, please try again.', 'essential-wp-real-estate' );
						break;
					case 'discount_invalid_amount':
						$notices['error']['cl-discount-invalid-amount'] = __( 'The discount amount must be a valid percentage or numeric flat amount. Please try again.', 'essential-wp-real-estate' );
						break;
				}
			}

			// Shop reports errors
			if ( current_user_can( 'view_shop_reports' ) ) {
				$msg_case = cl_sanitization($_GET['cl-message']);
				switch ( $msg_case ) {
					case 'payment_deleted':
						$notices['updated']['cl-payment-deleted'] = __( 'The payment has been deleted.', 'essential-wp-real-estate' );
						break;
					case 'email_sent':
						$notices['updated']['cl-payment-sent'] = __( 'The purchase receipt has been resent.', 'essential-wp-real-estate' );
						break;
					case 'refreshed-reports':
						$notices['updated']['cl-refreshed-reports'] = __( 'The reports have been refreshed.', 'essential-wp-real-estate' );
						break;
					case 'payment-note-deleted':
						$notices['updated']['cl-payment-note-deleted'] = __( 'The payment note has been deleted.', 'essential-wp-real-estate' );
						break;
				}
			}

			// Shop settings errors
			if ( current_user_can( 'manage_shop_settings' ) ) {
				$msg_case = cl_sanitization($_GET['cl-message']);
				switch ( $msg_case ) {
					case 'settings-imported':
						$notices['updated']['cl-settings-imported'] = __( 'The settings have been imported.', 'essential-wp-real-estate' );
						break;
					case 'api-key-generated':
						$notices['updated']['cl-api-key-generated'] = __( 'API keys successfully generated.', 'essential-wp-real-estate' );
						break;
					case 'api-key-exists':
						$notices['error']['cl-api-key-exists'] = __( 'The specified user already has API keys.', 'essential-wp-real-estate' );
						break;
					case 'api-key-regenerated':
						$notices['updated']['cl-api-key-regenerated'] = __( 'API keys successfully regenerated.', 'essential-wp-real-estate' );
						break;
					case 'api-key-revoked':
						$notices['updated']['cl-api-key-revoked'] = __( 'API keys successfully revoked.', 'essential-wp-real-estate' );
						break;
					case 'sendwp-connected':
						$notices['updated']['cl-sendwp-connected'] = __( 'SendWP has been successfully connected!', 'essential-wp-real-estate' );
						break;
				}
			}

			// Shop payments errors
			if ( current_user_can( 'edit_shop_payments' ) ) {
				$msg_case = cl_sanitization($_GET['cl-message']);
				switch ( $msg_case ) {
					case 'note-added':
						$notices['updated']['cl-note-added'] = __( 'The payment note has been added successfully.', 'essential-wp-real-estate' );
						break;
					case 'payment-updated':
						$notices['updated']['cl-payment-updated'] = __( 'The payment has been successfully updated.', 'essential-wp-real-estate' );
						break;
				}
			}

			// Customer Notices
			if ( current_user_can( 'edit_shop_payments' ) ) {
				$msg_case = cl_sanitization($_GET['cl-message']);
				switch ( $msg_case ) {
					case 'customer-deleted':
						$notices['updated']['cl-customer-deleted'] = __( 'Customer successfully deleted', 'essential-wp-real-estate' );
						break;
					case 'user-verified':
						$notices['updated']['cl-user-verified'] = __( 'User successfully verified', 'essential-wp-real-estate' );
						break;
					case 'email-added':
						$notices['updated']['cl-customer-email-added'] = __( 'Customer email added', 'essential-wp-real-estate' );
						break;
					case 'email-removed':
						$notices['updated']['cl-customer-email-removed'] = __( 'Customer email removed', 'essential-wp-real-estate' );
						break;
					case 'email-remove-failed':
						$notices['error']['cl-customer-email-remove-failed'] = __( 'Failed to remove customer email', 'essential-wp-real-estate' );
						break;
					case 'primary-email-updated':
						$notices['updated']['cl-customer-primary-email-updated'] = __( 'Primary email updated for customer', 'essential-wp-real-estate' );
						break;
					case 'primary-email-failed':
						$notices['error']['cl-customer-primary-email-failed'] = __( 'Failed to set primary email', 'essential-wp-real-estate' );
						break;
				}
			}
		}

		if ( count( $notices['updated'] ) > 0 ) {
			foreach ( $notices['updated'] as $notice => $message ) {
				add_settings_error( 'cl-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $notices['error'] ) > 0 ) {
			foreach ( $notices['error'] as $notice => $message ) {
				add_settings_error( 'cl-notices', $notice, $message, 'error' );
			}
		}

		settings_errors( 'cl-notices' );
	}

	/**
	 * Dismiss admin notices when Dismiss links are clicked
	 *
	 * @since 2.3
	 * @return void
	 */
	function dismiss_notices() {
		if ( isset( $_GET['cl_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_cl_' . cl_sanitization( $_GET['cl_notice'] ) . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'cl_action', 'cl_notice' ) ) );
			exit;
		}
	}
}
// new Adminnotice;
