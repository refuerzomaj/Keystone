<?php
function cl_email_purchase_receipt( $payment_id, $admin_notice = true, $to_email = '', $payment = null, $customer = null ) {
	if ( is_null( $payment ) ) {
		$payment = cl_get_payment( $payment_id );
	}

	$payment_data = $payment->get_meta( '_cl_payment_meta', true );

	$from_name = cl_admin_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name = apply_filters( 'cl_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email = cl_admin_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email = apply_filters( 'cl_purchase_from_address', $from_email, $payment_id, $payment_data );

	if ( empty( $to_email ) ) {
		$to_email = $payment->email;
	}

	$subject = cl_admin_get_option( 'purchase_subject', __( 'Purchase Receipt', 'essential-wp-real-estate' ) );
	$subject = apply_filters( 'cl_purchase_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject = wp_specialchars_decode( WPERECCP()->common->emailtags->cl_do_email_tags( $subject, $payment_id ) );

	$heading = cl_admin_get_option( 'purchase_heading', __( 'Purchase Receipt', 'essential-wp-real-estate' ) );
	$heading = apply_filters( 'cl_purchase_heading', $heading, $payment_id, $payment_data );
	$heading = WPERECCP()->common->emailtags->cl_do_email_tags( $heading, $payment_id );

	$attachments = apply_filters( 'cl_receipt_attachments', array(), $payment_id, $payment_data );

	$message = WPERECCP()->common->emailtags->cl_do_email_tags( WPERECCP()->common->emailtemplate->cl_get_email_body_content( $payment_id, $payment_data ), $payment_id );

	$emails = WPERECCP()->common->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$headers = apply_filters( 'cl_receipt_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );
	$emails->send( $to_email, $subject, $message, $attachments );

	if ( $admin_notice && ! cl_admin_notices_disabled( $payment_id ) ) {
		do_action( 'cl_admin_sale_notice', $payment_id, $payment_data );
	}
}

/**
 * Email the listing link(s) and payment confirmation to the admin accounts for testing.
 *
 * @since 1.5
 * @return void
 */
function cl_email_test_purchase_receipt() {
	$from_name = cl_admin_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name = apply_filters( 'cl_purchase_from_name', $from_name, 0, array() );

	$from_email = cl_admin_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email = apply_filters( 'cl_test_purchase_from_address', $from_email, 0, array() );

	$subject = cl_admin_get_option( 'purchase_subject', __( 'Purchase Receipt', 'essential-wp-real-estate' ) );
	$subject = apply_filters( 'cl_purchase_subject', wp_strip_all_tags( $subject ), 0 );
	$subject = wp_specialchars_decode( WPERECCP()->common->emailtags->cl_do_email_tags( $subject, 0 ) );

	$heading = cl_admin_get_option( 'purchase_heading', __( 'Purchase Receipt', 'essential-wp-real-estate' ) );
	$heading = apply_filters( 'cl_purchase_heading', $heading, 0, array() );

	$attachments = apply_filters( 'cl_receipt_attachments', array(), 0, array() );

	$message = WPERECCP()->common->emailtags->cl_do_email_tags( WPERECCP()->common->emailtemplate->cl_get_email_body_content( 0, array() ), 0 );

	$emails = WPERECCP()->common->emails;
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$headers = apply_filters( 'cl_receipt_headers', $emails->get_headers(), 0, array() );
	$emails->__set( 'headers', $headers );

	$emails->send( cl_get_admin_notice_emails(), $subject, $message, $attachments );
}

/**
 * Sends the Admin Sale Notification Email
 *
 * @since 1.4.2
 * @param int   $payment_id Payment ID (default: 0)
 * @param array $payment_data Payment Meta and Data
 * @return void
 */
function cl_admin_email_notice( $payment_id = 0, $payment_data = array() ) {

	$payment_id = absint( $payment_id );

	if ( empty( $payment_id ) ) {
		return;
	}

	if ( ! cl_get_payment_by( 'id', $payment_id ) ) {
		return;
	}

	$from_name = cl_admin_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name = apply_filters( 'cl_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email = cl_admin_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email = apply_filters( 'cl_admin_sale_from_address', $from_email, $payment_id, $payment_data );

	$subject = cl_admin_get_option( 'sale_notification_subject', sprintf( __( 'New listing purchase - Order #%1$s', 'essential-wp-real-estate' ), $payment_id ) );
	$subject = apply_filters( 'cl_admin_sale_notification_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject = wp_specialchars_decode( WPERECCP()->common->emailtags->cl_do_email_tags( $subject, $payment_id ) );

	$heading = cl_admin_get_option( 'sale_notification_heading', __( 'New Sale!', 'essential-wp-real-estate' ) );
	$heading = apply_filters( 'cl_admin_sale_notification_heading', $heading, $payment_id, $payment_data );
	$heading = WPERECCP()->common->emailtags->cl_do_email_tags( $heading, $payment_id );

	$attachments = apply_filters( 'cl_admin_sale_notification_attachments', array(), $payment_id, $payment_data );

	$message = WPERECCP()->common->emailtemplate->cl_get_sale_notification_body_content( $payment_id, $payment_data );

	$emails = WPERECCP()->common->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$headers = apply_filters( 'cl_admin_sale_notification_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	$emails->send( cl_get_admin_notice_emails(), $subject, $message, $attachments );
}
add_action( 'cl_admin_sale_notice', 'cl_admin_email_notice', 10, 2 );

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the PROPERTY LISTING PLUGIN Settings)
 *
 * @since 1.0
 * @return mixed
 */
function cl_get_admin_notice_emails() {
	 $emails = cl_admin_get_option( 'admin_notice_emails', false );
	$emails  = strlen( trim( $emails ) ) > 0 ? $emails : get_bloginfo( 'admin_email' );
	$emails  = array_map( 'trim', explode( "\n", $emails ) );

	return apply_filters( 'cl_admin_notice_emails', $emails );
}

/**
 * Checks whether admin sale notices are disabled
 *
 * @since 1.5.2
 *
 * @param int $payment_id
 * @return mixed
 */
function cl_admin_notices_disabled( $payment_id = 0 ) {
	$ret = cl_admin_get_option( 'disable_admin_notices', false );
	return (bool) apply_filters( 'cl_admin_notices_disabled', $ret, $payment_id );
}

/**
 * Get sale notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since 1.7
 * @author Daniel J Griffiths
 * @return string $message
 */
function cl_get_default_sale_notification_email() {
	 $default_email_body = __( 'Hello', 'essential-wp-real-estate' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'essential-wp-real-estate' ), cl_get_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'essential-wp-real-estate' ), cl_get_label_plural() ) . "\n\n";
	$default_email_body .= '{listing_list}' . "\n\n";
	$default_email_body .= __( 'Purchased by: ', 'essential-wp-real-estate' ) . ' {name}' . "\n";
	$default_email_body .= __( 'Amount: ', 'essential-wp-real-estate' ) . ' {price}' . "\n";
	$default_email_body .= __( 'Payment Method: ', 'essential-wp-real-estate' ) . ' {payment_method}' . "\n\n";
	$default_email_body .= __( 'Thank you', 'essential-wp-real-estate' );

	$message = cl_admin_get_option( 'sale_notification', false );
	$message = ! empty( $message ) ? $message : $default_email_body;

	return $message;
}

/**
 * Get various correctly formatted names used in emails
 *
 * @since 1.9
 * @param $user_info
 * @param $payment   Clpayment for getting the names
 *
 * @return array $email_names
 */
function cl_get_email_names( $user_info, $payment = false ) {
	$email_names             = array();
	$email_names['fullname'] = '';

	if ( $payment instanceof Clpayment ) {
		if ( $payment->user_id > 0 ) {
			$user_data               = get_userdata( $payment->user_id );
			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $user_data->user_login;
		} elseif ( ! empty( $payment->first_name ) ) {
			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $payment->first_name;
		} else {
			$email_names['name']     = $payment->email;
			$email_names['username'] = $payment->email;
		}
	} else {

		if ( is_serialized( $user_info ) ) {
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				return array(
					'name'     => '',
					'fullname' => '',
					'username' => '',
				);
			} else {
				$user_info = maybe_unserialize( $user_info );
			}
		}

		if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
			$user_data               = get_userdata( $user_info['id'] );
			$email_names['name']     = $user_info['first_name'];
			$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username'] = $user_data->user_login;
		} elseif ( isset( $user_info['first_name'] ) ) {
			$email_names['name']     = $user_info['first_name'];
			$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username'] = $user_info['first_name'];
		} else {
			$email_names['name']     = $user_info['email'];
			$email_names['username'] = $user_info['email'];
		}
	}

	return $email_names;
}

/**
 * Handle installation and connection for SendWP via ajax
 *
 * @since 2.9.15
 */
function cl_sendwp_remote_install_handler() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'essential-wp-real-estate' ),
			)
		);
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if ( ! array_key_exists( 'sendwp/sendwp.php', $plugins ) ) {

		/*
		* Use the WordPress Plugins API to get the plugin listing link.
		*/
		$api = plugins_api(
			'plugin_information',
			array(
				'slug' => 'sendwp',
			)
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error(
				array(
					'error' => $api->get_error_message(),
					'debug' => $api,
				)
			);
		}

		/*
		* Use the AJAX Upgrader skin to quietly install the plugin.
		*/
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install  = $upgrader->install( $api->listing_link );
		if ( is_wp_error( $install ) ) {
			wp_send_json_error(
				array(
					'error' => $install->get_error_message(),
					'debug' => $api,
				)
			);
		}

		$activated = activate_plugin( $upgrader->plugin_info() );
	} else {

		$activated = activate_plugin( 'sendwp/sendwp.php' );
	}

	/*
	* Final check to see if SendWP is available.
	*/
	if ( ! function_exists( 'sendwp_get_server_url' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'Something went wrong. SendWP was not installed correctly.', 'essential-wp-real-estate' ),
			)
		);
	}

	wp_send_json_success(
		array(
			'partner_id'      => 81,
			'register_url'    => sendwp_get_server_url() . '_/signup',
			'client_name'     => sendwp_get_client_name(),
			'client_secret'   => sendwp_get_client_secret(),
			'client_redirect' => admin_url( '/edit.php?post_type=listing&page=cl-settings&tab=emails&cl-message=sendwp-connected' ),
		)
	);
}
add_action( 'wp_ajax_cl_sendwp_remote_install', 'cl_sendwp_remote_install_handler' );

/**
 * Handle deactivation of SendWP via ajax
 *
 * @since 2.9.15
 */
function cl_sendwp_disconnect() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'essential-wp-real-estate' ),
			)
		);
	}

	sendwp_disconnect_client();

	deactivate_plugins( 'sendwp/sendwp.php' );

	wp_send_json_success();
}
add_action( 'wp_ajax_cl_sendwp_disconnect', 'cl_sendwp_disconnect' );

/**
 * Handle installation and connection for Recapture via ajax
 *
 * @since 2.10.2
 */
function cl_recapture_remote_install_handler() {
	if ( ! current_user_can( 'manage_shop_settings' ) || ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'essential-wp-real-estate' ),
			)
		);
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if ( ! array_key_exists( 'recapture-for-cl/recapture.php', $plugins ) ) {

		/*
		* Use the WordPress Plugins API to get the plugin listing link.
		*/
		$api = plugins_api(
			'plugin_information',
			array(
				'slug' => 'recapture-for-cl',
			)
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error(
				array(
					'error' => $api->get_error_message(),
					'debug' => $api,
				)
			);
		}

		/*
		* Use the AJAX Upgrader skin to quietly install the plugin.
		*/
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install  = $upgrader->install( $api->listing_link );
		if ( is_wp_error( $install ) ) {
			wp_send_json_error(
				array(
					'error' => $install->get_error_message(),
					'debug' => $api,
				)
			);
		}

		$activated = activate_plugin( $upgrader->plugin_info() );
	} else {

		$activated = activate_plugin( 'recapture-for-cl/recapture.php' );
	}

	/*
	* Final check to see if Recapture is available.
	*/
	if ( is_wp_error( $activated ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'Something went wrong. Recapture for PROPERTY LISTING PLUGIN was not installed correctly.', 'essential-wp-real-estate' ),
			)
		);
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_cl_recapture_remote_install', 'cl_recapture_remote_install_handler' );

/**
 * Maybe adds a notice to abandoned payments if Recapture isn't installed.
 *
 * @since 2.10.2
 *
 * @param int $payment_id The ID of the abandoned payment, for which a Recapture notice is being thrown.
 */
function maybe_add_recapture_notice_to_abandoned_payment( $payment_id ) {

	if (
		! class_exists( 'Recapture' )
		&& 'abandoned' === cl_get_payment_status( $payment_id )
		&& ! get_user_meta( get_current_user_id(), '_cl_try_recapture_dismissed', true )
	) {
		?>
		<div class="notice notice-warning recapture-notice">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* Translators: %1$s - <strong> tag, %2$s - </strong> tag, %3$s - <a> tag, %4$s - </a> tag */
						__( '%1$sRecover abandoned purchases like this one.%2$s %3$sTry Recapture for free%4$s.', 'essential-wp-real-estate' ),
						'<strong>',
						'</strong>',
						'<a href="#" rel="noopener" target="_blank">',
						'</a>'
					)
				);
				?>
			</p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* Translators: %1$s - Opening anchor tag, %2$s - The url to dismiss the ajax notice, %3$s - Complete the opening of the anchor tag, %4$s - Open span tag, %4$s - Close span tag */
					__( '%1$s %2$s %3$s %4$s Dismiss this notice. %5$s', 'essential-wp-real-estate' ),
					'<a href="',
					esc_url(
						add_query_arg(
							array(
								'cl_action' => 'dismiss_notices',
								'cl_notice' => 'try_recapture',
							)
						)
					),
					'" type="button" class="notice-dismiss">',
					'<span class="screen-reader-text">',
					'</span>
					</a>'
				)
			);
			?>
		</div>
		<?php
	}
}
add_action( 'cl_view_order_details_before', 'maybe_add_recapture_notice_to_abandoned_payment' );
