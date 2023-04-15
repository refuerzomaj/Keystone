<?php
namespace  Essential\Restate\Front\Purchase\Gateways;

use Essential\Restate\Traitval\Traitval;

class Gateways {

	use Traitval;
	public function __construct() {
		 add_action( 'cl_straight_to_gateway', array( $this, 'cl_process_straight_to_gateway' ) );
	}

	function cl_send_to_gateway( $gateway, $payment_data ) {

		$payment_data['gateway_nonce'] = wp_create_nonce( 'cl-gateway' );

		// $gateway must match the ID used when registering the gateway
		do_action( 'cl_gateway_' . $gateway, $payment_data );
	}


	public function cl_get_chosen_gateway() {
		$gateways = $this->cl_get_enabled_payment_gateways();
		$chosen   = isset( $_REQUEST['payment-mode'] ) ? cl_sanitization( $_REQUEST['payment-mode'] ) : false;

		if ( false !== $chosen ) {
			$chosen = preg_replace( '/[^a-zA-Z0-9-_]+/', '', $chosen );
		}

		if ( ! empty( $chosen ) ) {
			$chosen_gateway = urldecode( $chosen );

			if ( ! $this->cl_is_gateway_active( $chosen_gateway ) ) {
				$chosen_gateway = $this->cl_get_default_gateway();
			}
		} else {
			$chosen_gateway = $this->cl_get_default_gateway();
		}
		if ( WPERECCP()->front->cart->cl_get_cart_subtotal() <= 0 ) {
			$chosen_gateway = 'manual';
		}
		return apply_filters( 'cl_chosen_gateway', $chosen_gateway );
	}

	public function cl_show_gateways() {
		$gateways = $this->cl_get_enabled_payment_gateways();

		$show_gateways = false;
		if ( count( $gateways ) > 1 ) {
			$show_gateways = true;
			if ( WPERECCP()->front->cart->get_total() <= 0 ) {

				$show_gateways = false;
			}
		}

		return apply_filters( 'cl_show_gateways', $show_gateways );
	}

	public function cl_get_payment_gateways() {
		 // Default, built-in gateways
		$gateways = array(
			'paypal' => array(
				'admin_label'    => __( 'PayPal Standard', 'essential-wp-real-estate' ),
				'checkout_label' => __( 'PayPal', 'essential-wp-real-estate' ),
				'supports'       => array( 'buy_now' ),
			),
			'stripe' => array(
				'admin_label'    => __( 'Stripe Payment', 'essential-wp-real-estate' ),
				'checkout_label' => __( 'Stripe Payment', 'essential-wp-real-estate' ),
			),
		);

		return apply_filters( 'cl_payment_gateways', $gateways );
	}


	public function cl_get_enabled_payment_gateways( $sort = false ) {
		$gateways = $this->cl_get_payment_gateways();
		$enabled  = array();
		foreach ( $gateways as $key => $value ) {
			$enabled[ $key ] = cl_admin_get_option( $key . '_active' );
		}
		$gateway_list = array();
		foreach ( $gateways as $key => $gateway ) {
			if ( isset( $enabled[ $key ] ) && $enabled[ $key ] == 1 ) {
				$gateway_list[ $key ] = $gateway;
			}
		}

		if ( true === $sort ) {
			// Reorder our gateways so the default is first
			$default_gateway_id = $this->cl_get_default_gateway();

			if ( $this->cl_is_gateway_active( $default_gateway_id ) ) {
				$default_gateway = array( $default_gateway_id => $gateway_list[ $default_gateway_id ] );
				unset( $gateway_list[ $default_gateway_id ] );

				$gateway_list = array_merge( $default_gateway, $gateway_list );
			}
		}

		return apply_filters( 'cl_enabled_payment_gateways', $gateway_list );
	}


	public function cl_is_gateway_active( $gateway ) {
		$gateways = $this->cl_get_enabled_payment_gateways();
		if ( empty( $gateways ) ) {
			return false;
		}
		$ret = array_key_exists( $gateway, $gateways );
		return apply_filters( 'cl_is_gateway_active', $ret, $gateway, $gateways );
	}

	public function cl_get_default_gateway() {
		$default = get_option( 'default_gateway', 'paypal' );

		if ( ! $this->cl_is_gateway_active( $default ) ) {
			$gateways = $this->cl_get_enabled_payment_gateways();
			$gateways = array_keys( $gateways );
			$default  = reset( $gateways );
		}

		return apply_filters( 'cl_default_gateway', $default );
	}


	function cl_get_gateway_admin_label( $gateway ) {
		$gateways = $this->cl_get_payment_gateways();
		$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['admin_label'] : $gateway;
		$payment  = isset( $_GET['id'] ) ? absint( cl_sanitization( $_GET['id'] ) ) : false;

		if ( $gateway == 'manual' && $payment ) {
			if ( cl_get_payment_amount( $payment ) == 0 ) {
				$label = __( 'Free Purchase', 'essential-wp-real-estate' );
			}
		}

		return apply_filters( 'cl_gateway_admin_label', $label, $gateway );
	}

	/**
	 * Returns the checkout label for the specified gateway
	 *
	 * @since 1.0.8.5
	 * @param string $gateway Name of the gateway to retrieve a label for
	 * @return string Checkout label for the gateway
	 */
	function cl_get_gateway_checkout_label( $gateway ) {
		$gateways = $this->cl_get_payment_gateways();
		$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['checkout_label'] : $gateway;

		if ( $gateway == 'manual' ) {
			$label = __( 'Free Purchase', 'essential-wp-real-estate' );
		}

		return apply_filters( 'cl_gateway_checkout_label', $label, $gateway );
	}
	/**
	 * Returns the options a gateway supports
	 *
	 * @since 1.8
	 * @param string $gateway ID of the gateway to retrieve a label for
	 * @return array Options the gateway supports
	 */
	public function cl_get_gateway_supports( $gateway ) {
		$gateways = $this->cl_get_enabled_payment_gateways();
		$supports = isset( $gateways[ $gateway ]['supports'] ) ? $gateways[ $gateway ]['supports'] : array();
		return apply_filters( 'cl_gateway_supports', $supports, $gateway );
	}
	/**
	 * Checks if a gateway supports buy now
	 *
	 * @since 1.8
	 * @param string $gateway ID of the gateway to retrieve a label for
	 * @return bool
	 */
	public function cl_gateway_supports_buy_now( $gateway ) {
		$supports = $this->cl_get_gateway_supports( $gateway );
		$ret      = in_array( 'buy_now', $supports );
		return apply_filters( 'cl_gateway_supports_buy_now', $ret, $gateway );
	}

	/**
	 * Checks if an enabled gateway supports buy now
	 *
	 * @since 1.8
	 * @return bool
	 */
	public function cl_shop_supports_buy_now() {
		$gateways = $this->cl_get_enabled_payment_gateways();
		$ret      = false;

		if ( ! WPERECCP()->front->tax->cl_use_taxes() && $gateways && 1 === count( $gateways ) ) {
			foreach ( $gateways as $gateway_id => $gateway ) {
				if ( $this->cl_gateway_supports_buy_now( $gateway_id ) ) {
					$ret = true;
					break;
				}
			}
		}

		return apply_filters( 'cl_shop_supports_buy_now', $ret );
	}

	public function cl_purchase_form_validate_fields() {
		// Check if there is $_POST
		if ( empty( $_POST ) ) {
			return false;
		}

		// Start an array to collect valid data
		$valid_data = array(
			'gateway'          => $this->cl_purchase_form_validate_gateway(), // Gateway fallback
			'discount'         => $this->cl_purchase_form_validate_discounts(),    // Set default discount
			'need_new_user'    => false,     // New user flag
			'need_user_login'  => false,     // Login user flag
			'logged_user_data' => array(),   // Logged user collected data
			'new_user_data'    => array(),   // New user collected data
			'login_user_data'  => array(),   // Login user collected data
			'guest_user_data'  => array(),   // Guest user collected data
			'cc_info'          => $this->cl_purchase_form_validate_cc(),    // Credit card info
		);

		// Validate agree to terms
		if ( cl_admin_get_option( 'show_agree_to_terms', false ) ) {
			$this->cl_purchase_form_validate_agree_to_terms();
		}

		if ( is_user_logged_in() ) {
			// Collect logged in user data
			$valid_data['logged_in_user'] = $this->cl_purchase_form_validate_logged_in_user();
		} elseif ( isset( $_POST['cl-purchase-var'] ) && $_POST['cl-purchase-var'] == 'needs-to-register' ) {
			// Set new user registration as required
			$valid_data['need_new_user'] = true;

			// Validate new user data
			$valid_data['new_user_data'] = $this->cl_purchase_form_validate_new_user();
			// Check if login validation is needed
		} elseif ( isset( $_POST['cl-purchase-var'] ) && $_POST['cl-purchase-var'] == 'needs-to-login' ) {
			// Set user login as required
			$valid_data['need_user_login'] = true;

			// Validate users login info
			$valid_data['login_user_data'] = $this->cl_purchase_form_validate_user_login();
		} else {
			// Not registering or logging in, so setup guest user data
			$valid_data['guest_user_data'] = $this->cl_purchase_form_validate_guest_user();
		}

		// Return collected data
		return $valid_data;
	}


	public function cl_process_purchase_login() {
		$is_ajax = isset( $_POST['cl_ajax'] );

		$user_data = cl_purchase_form_validate_user_login();

		if ( cl_get_errors() || $user_data['user_id'] < 1 ) {
			if ( $is_ajax ) {
				do_action( 'cl_ajax_checkout_errors' );
				wp_die();
			} else {
				wp_redirect( cl_sanitization( $_SERVER['HTTP_REFERER'] ) );
				exit;
			}
		}

		cl_log_user_in( $user_data['user_id'], $user_data['user_login'], $user_data['user_pass'] );

		if ( $is_ajax ) {
			echo 'success';
			wp_die();
		} else {
			wp_redirect( cl_get_checkout_uri( cl_sanitization( $_SERVER['QUERY_STRING'] ) ) );
		}
	}





	function cl_purchase_form_validate_gateway() {
		$gateway = $this->cl_get_default_gateway();

		// Check if a gateway value is present
		if ( ! empty( $_REQUEST['cl-gateway'] ) ) {

			$gateway = cl_sanitization( $_REQUEST['cl-gateway'] );

			if ( '0.00' == WPERECCP()->front->cart->cl_get_cart_total() ) {

				$gateway = 'manual';
			} elseif ( ! $this->cl_is_gateway_active( $gateway ) ) {

				WPERECCP()->front->error->cl_set_error( 'invalid_gateway', __( 'The selected payment gateway is not enabled', 'essential-wp-real-estate' ) );
			}
		}

		return $gateway;
	}


	function cl_purchase_form_validate_discounts() {
		// Retrieve the discount stored in cookies
		$discounts = WPERECCP()->front->discountaction->cl_get_cart_discounts();

		$user = '';
		if ( isset( $_POST['cl_user_login'] ) && ! empty( $_POST['cl_user_login'] ) ) {
			$user = cl_sanitization( $_POST['cl_user_login'] );
		} elseif ( isset( $_POST['cl_email'] ) && ! empty( $_POST['cl_email'] ) ) {
			$user = cl_sanitization( $_POST['cl_email'] );
		} elseif ( is_user_logged_in() ) {
			$user = wp_get_current_user()->user_email;
		}

		$error = false;

		// Check for valid discount(s) is present
		if ( ! empty( $_POST['cl-discount'] ) && __( 'Enter discount', 'essential-wp-real-estate' ) != $_POST['cl-discount'] ) {
			// Check for a posted discount
			$posted_discount = isset( $_POST['cl-discount'] ) ? trim( cl_sanitization( $_POST['cl-discount'] ) ) : false;

			// Add the posted discount to the discounts
			if ( $posted_discount && ( empty( $discounts ) || WPERECCP()->front->discountaction->cl_multiple_discounts_allowed() ) && WPERECCP()->front->discountaction->cl_is_discount_valid( $posted_discount, $user ) ) {
				WPERECCP()->front->discountaction->cl_set_cart_discount( $posted_discount );
			}
		}

		// If we have discounts, loop through them
		if ( ! empty( $discounts ) ) {
			foreach ( $discounts as $discount ) {
				// Check if valid
				if ( ! WPERECCP()->front->discountaction->cl_is_discount_valid( $discount, $user ) ) {
					// Discount is not valid
					$error = true;
				}
			}
		} else {
			// No discounts
			return 'none';
		}

		if ( $error ) {
			WPERECCP()->front->error->cl_set_error( 'invalid_discount', __( 'One or more of the discounts you entered is invalid', 'essential-wp-real-estate' ) );
		}
	}

	function cl_purchase_form_validate_cc() {
		$card_data = $this->cl_get_purchase_cc_info();

		// Validate the card zip
		if ( ! empty( $card_data['card_zip'] ) ) {
			if ( ! $this->cl_purchase_form_validate_cc_zip( $card_data['card_zip'], $card_data['card_country'] ) ) {
				WPERECCP()->front->error->cl_set_error( 'invalid_cc_zip', __( 'The zip / postal code you entered for your billing address is invalid', 'essential-wp-real-estate' ) );
			}
		}

		// This should validate card numbers at some point too
		return $card_data;
	}

	function cl_get_purchase_cc_info() {
		$cc_info                   = array();
		$cc_info['card_name']      = isset( $_POST['card_name'] ) ? cl_sanitization( $_POST['card_name'] ) : '';
		$cc_info['card_number']    = isset( $_POST['card_number'] ) ? cl_sanitization( $_POST['card_number'] ) : '';
		$cc_info['card_cvc']       = isset( $_POST['card_cvc'] ) ? cl_sanitization( $_POST['card_cvc'] ) : '';
		$cc_info['card_exp_month'] = isset( $_POST['card_exp_month'] ) ? cl_sanitization( $_POST['card_exp_month'] ) : '';
		$cc_info['card_exp_year']  = isset( $_POST['card_exp_year'] ) ? cl_sanitization( $_POST['card_exp_year'] ) : '';
		$cc_info['card_address']   = isset( $_POST['card_address'] ) ? cl_sanitization( $_POST['card_address'] ) : '';
		$cc_info['card_address_2'] = isset( $_POST['card_address_2'] ) ? cl_sanitization( $_POST['card_address_2'] ) : '';
		$cc_info['card_city']      = isset( $_POST['card_city'] ) ? cl_sanitization( $_POST['card_city'] ) : '';
		$cc_info['card_state']     = isset( $_POST['card_state'] ) ? cl_sanitization( $_POST['card_state'] ) : '';
		$cc_info['card_country']   = isset( $_POST['billing_country'] ) ? cl_sanitization( $_POST['billing_country'] ) : '';
		$cc_info['card_zip']       = isset( $_POST['card_zip'] ) ? cl_sanitization( $_POST['card_zip'] ) : '';

		// Return cc info
		return $cc_info;
	}

	/**
	 * Validate zip code based on country code
	 *
	 * @since  1.4.4
	 *
	 * @param int    $zip
	 * @param string $country_code
	 *
	 * @return bool|mixed|void
	 */
	function cl_purchase_form_validate_cc_zip( $zip = 0, $country_code = '' ) {
		$ret = false;

		if ( empty( $zip ) || empty( $country_code ) ) {
			return $ret;
		}

		$country_code = strtoupper( $country_code );

		$zip_regex = array(
			'AD' => 'AD\d{3}',
			'AM' => '(37)?\d{4}',
			'AR' => '^([A-Z]{1}\d{4}[A-Z]{3}|[A-Z]{1}\d{4}|\d{4})$',
			'AS' => '96799',
			'AT' => '\d{4}',
			'AU' => '^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$',
			'AX' => '22\d{3}',
			'AZ' => '\d{4}',
			'BA' => '\d{5}',
			'BB' => '(BB\d{5})?',
			'BD' => '\d{4}',
			'BE' => '^[1-9]{1}[0-9]{3}$',
			'BG' => '\d{4}',
			'BH' => '((1[0-2]|[2-9])\d{2})?',
			'BM' => '[A-Z]{2}[ ]?[A-Z0-9]{2}',
			'BN' => '[A-Z]{2}[ ]?\d{4}',
			'BR' => '\d{5}[\-]?\d{3}',
			'BY' => '\d{6}',
			'CA' => '^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$',
			'CC' => '6799',
			'CH' => '^[1-9][0-9][0-9][0-9]$',
			'CK' => '\d{4}',
			'CL' => '\d{7}',
			'CN' => '\d{6}',
			'CR' => '\d{4,5}|\d{3}-\d{4}',
			'CS' => '\d{5}',
			'CV' => '\d{4}',
			'CX' => '6798',
			'CY' => '\d{4}',
			'CZ' => '\d{3}[ ]?\d{2}',
			'DE' => "\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
			'DK' => '^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$',
			'DO' => '\d{5}',
			'DZ' => '\d{5}',
			'EC' => '([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?',
			'EE' => '\d{5}',
			'EG' => '\d{5}',
			'ES' => '^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$',
			'ET' => '\d{4}',
			'FI' => '\d{5}',
			'FK' => 'FIQQ 1ZZ',
			'FM' => '(9694[1-4])([ \-]\d{4})?',
			'FO' => '\d{3}',
			'FR' => '^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$',
			'GE' => '\d{4}',
			'GF' => '9[78]3\d{2}',
			'GL' => '39\d{2}',
			'GN' => '\d{3}',
			'GP' => '9[78][01]\d{2}',
			'GR' => '\d{3}[ ]?\d{2}',
			'GS' => 'SIQQ 1ZZ',
			'GT' => '\d{5}',
			'GU' => '969[123]\d([ \-]\d{4})?',
			'GW' => '\d{4}',
			'HM' => '\d{4}',
			'HN' => '(?:\d{5})?',
			'HR' => '\d{5}',
			'HT' => '\d{4}',
			'HU' => '\d{4}',
			'ID' => '\d{5}',
			'IE' => '((D|DUBLIN)?([1-9]|6[wW]|1[0-8]|2[024]))?',
			'IL' => '\d{5}',
			'IN' => '^[1-9][0-9][0-9][0-9][0-9][0-9]$', // india
			'IO' => 'BBND 1ZZ',
			'IQ' => '\d{5}',
			'IS' => '\d{3}',
			'IT' => '^(V-|I-)?[0-9]{5}$',
			'JO' => '\d{5}',
			'JP' => '\d{3}-\d{4}',
			'KE' => '\d{5}',
			'KG' => '\d{6}',
			'KH' => '\d{5}',
			'KR' => '\d{5}',
			'KW' => '\d{5}',
			'KZ' => '\d{6}',
			'LA' => '\d{5}',
			'LB' => '(\d{4}([ ]?\d{4})?)?',
			'LI' => '(948[5-9])|(949[0-7])',
			'LK' => '\d{5}',
			'LR' => '\d{4}',
			'LS' => '\d{3}',
			'LT' => '\d{5}',
			'LU' => '\d{4}',
			'LV' => '\d{4}',
			'MA' => '\d{5}',
			'MC' => '980\d{2}',
			'MD' => '\d{4}',
			'ME' => '8\d{4}',
			'MG' => '\d{3}',
			'MH' => '969[67]\d([ \-]\d{4})?',
			'MK' => '\d{4}',
			'MN' => '\d{6}',
			'MP' => '9695[012]([ \-]\d{4})?',
			'MQ' => '9[78]2\d{2}',
			'MT' => '[A-Z]{3}[ ]?\d{2,4}',
			'MU' => '(\d{3}[A-Z]{2}\d{3})?',
			'MV' => '\d{5}',
			'MX' => '\d{5}',
			'MY' => '\d{5}',
			'NC' => '988\d{2}',
			'NE' => '\d{4}',
			'NF' => '2899',
			'NG' => '(\d{6})?',
			'NI' => '((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?',
			'NL' => '^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$',
			'NO' => '\d{4}',
			'NP' => '\d{5}',
			'NZ' => '\d{4}',
			'OM' => '(PC )?\d{3}',
			'PF' => '987\d{2}',
			'PG' => '\d{3}',
			'PH' => '\d{4}',
			'PK' => '\d{5}',
			'PL' => '\d{2}-\d{3}',
			'PM' => '9[78]5\d{2}',
			'PN' => 'PCRN 1ZZ',
			'PR' => '00[679]\d{2}([ \-]\d{4})?',
			'PT' => '\d{4}([\-]\d{3})?',
			'PW' => '96940',
			'PY' => '\d{4}',
			'RE' => '9[78]4\d{2}',
			'RO' => '\d{6}',
			'RS' => '\d{5}',
			'RU' => '\d{6}',
			'SA' => '\d{5}',
			'SE' => '^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$',
			'SG' => '\d{6}',
			'SH' => '(ASCN|STHL) 1ZZ',
			'SI' => '\d{4}',
			'SJ' => '\d{4}',
			'SK' => '\d{3}[ ]?\d{2}',
			'SM' => '4789\d',
			'SN' => '\d{5}',
			'SO' => '\d{5}',
			'SZ' => '[HLMS]\d{3}',
			'TC' => 'TKCA 1ZZ',
			'TH' => '\d{5}',
			'TJ' => '\d{6}',
			'TM' => '\d{6}',
			'TN' => '\d{4}',
			'TR' => '\d{5}',
			'TW' => '\d{3}(\d{2})?',
			'UA' => '\d{5}',
			'UK' => '^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$',
			'US' => '^\d{5}([\-]?\d{4})?$',
			'UY' => '\d{5}',
			'UZ' => '\d{6}',
			'VA' => '00120',
			'VE' => '\d{4}',
			'VI' => '008(([0-4]\d)|(5[01]))([ \-]\d{4})?',
			'WF' => '986\d{2}',
			'YT' => '976\d{2}',
			'YU' => '\d{5}',
			'ZA' => '\d{4}',
			'ZM' => '\d{5}',
		);

		if ( ! isset( $zip_regex[ $country_code ] ) || preg_match( '/' . $zip_regex[ $country_code ] . '/i', $zip ) ) {
			$ret = true;
		}

		return apply_filters( 'cl_is_zip_valid', $ret, $zip, $country_code );
	}

	function cl_purchase_form_validate_guest_user() {
		// Start an array to collect valid user data
		$valid_user_data = array(
			// Set a default id for guests
			'user_id' => 0,
		);

		// Show error message if user must be logged in
		if ( cl_logged_in_only() ) {
			WPERECCP()->front->error->cl_set_error( 'logged_in_only', __( 'You must be logged into an account to purchase', 'essential-wp-real-estate' ) );
		}

		// Get the guest email
		$guest_email = isset( $_POST['cl_email'] ) ? sanitize_email( $_POST['cl_email'] ) : false;

		// Check email
		if ( $guest_email && strlen( $guest_email ) > 0 ) {
			// Validate email
			if ( ! is_email( $guest_email ) ) {
				// Invalid email
				WPERECCP()->front->error->cl_set_error( 'email_invalid', __( 'Invalid email', 'essential-wp-real-estate' ) );
			} else {
				// All is good to go
				$valid_user_data['user_email'] = $guest_email;
			}
		} else {
			// No email
			WPERECCP()->front->error->cl_set_error( 'email_empty', __( 'Enter an email', 'essential-wp-real-estate' ) );
		}

		// Loop through required fields and show error messages
		foreach ( $this->cl_purchase_form_required_fields() as $field_name => $value ) {
			if ( in_array( $value, $this->cl_purchase_form_required_fields() ) && empty( $_POST[ $field_name ] ) ) {
				WPERECCP()->front->error->cl_set_error( $value['error_id'], $value['error_message'] );
			}
		}

		return $valid_user_data;
	}


	/**
	 * Check the purchase to ensure a banned email is not allowed through
	 *
	 * @since       2.0
	 * @return      void
	 */
	function cl_check_purchase_email( $valid_data, $posted ) {

		$banned = cl_get_banned_emails();

		if ( empty( $banned ) ) {
			return;
		}

		$user_emails = array( $posted['cl_email'] );
		if ( is_user_logged_in() ) {

			// The user is logged in, check that their account email is not banned
			$user_data     = get_userdata( get_current_user_id() );
			$user_emails[] = $user_data->user_email;
		} elseif ( isset( $posted['cl-purchase-var'] ) && $posted['cl-purchase-var'] == 'needs-to-login' ) {

			// The user is logging in, check that their email is not banned
			if ( $user_data = get_user_by( 'login', $posted['cl_user_login'] ) ) {
				$user_emails[] = $user_data->user_email;
			}
		}

		foreach ( $user_emails as $email ) {
			if ( cl_is_email_banned( $email ) ) {
				// Set an error and give the customer a general error (don't alert them that they were banned)
				WPERECCP()->front->error->cl_set_error( 'email_banned', __( 'An internal error has occurred, please try again or contact support.', 'essential-wp-real-estate' ) );
				break;
			}
		}
	}



	/**
	 * Process a straight-to-gateway purchase
	 *
	 * @since 1.7
	 * @return void
	 */
	function cl_process_straight_to_gateway( $data ) {

		$listing_id = $data['listing_id'];
		$options    = isset( $data['cl_options'] ) ? $data['cl_options'] : array();
		$quantity   = isset( $data['cl_listing_quantity'] ) ? $data['cl_listing_quantity'] : 1;

		if ( empty( $listing_id ) || ! cl_get_listing( $listing_id ) ) {
			return;
		}

		$purchase_data    = $this->cl_build_straight_to_gateway_data( $listing_id, $options, $quantity );
		$enabled_gateways = $this->cl_get_enabled_payment_gateways();

		if ( ! array_key_exists( $purchase_data['gateway'], $enabled_gateways ) ) {
			foreach ( $purchase_data['listing'] as $listing ) {
				$options = isset( $listing['options'] ) ? $listing['options'] : array();

				$options['quantity'] = isset( $listing['quantity'] ) ? $listing['quantity'] : 1;
				$this->cl_add_to_cart( $listing['id'], $options );
			}

			WPERECCP()->front->error->cl_set_error( 'cl-straight-to-gateway-error', __( 'There was an error completing your purchase. Please try again.', 'essential-wp-real-estate' ) );
			wp_redirect( cl_get_checkout_uri() );
			exit;
		}

		$this->cl_set_purchase_session( $purchase_data );
		$this->cl_send_to_gateway( $purchase_data['gateway'], $purchase_data );
	}



	function cl_purchase_form_validate_logged_in_user() {
		global $user_ID;

		// Start empty array to collect valid user data
		$valid_user_data = array(
			// Assume there will be errors
			'user_id' => -1,
		);

		// Verify there is a user_ID
		if ( $user_ID > 0 ) {
			// Get the logged in user data
			$user_data = get_userdata( $user_ID );

			// Loop through required fields and show error messages
			foreach ( $this->cl_purchase_form_required_fields() as $field_name => $value ) {
				if ( in_array( $value, $this->cl_purchase_form_required_fields() ) && empty( $_POST[ $field_name ] ) ) {
					WPERECCP()->front->error->cl_set_error( $value['error_id'], $value['error_message'] );
				}
			}

			// Verify data
			if ( $user_data ) {
				// Collected logged in user data
				$valid_user_data = array(
					'user_id'    => $user_ID,
					'user_email' => isset( $_POST['cl_email'] ) ? sanitize_email( $_POST['cl_email'] ) : $user_data->user_email,
					'user_first' => isset( $_POST['cl_first'] ) && ! empty( $_POST['cl_first'] ) ? cl_sanitization( $_POST['cl_first'] ) : $user_data->first_name,
					'user_last'  => isset( $_POST['cl_last'] ) && ! empty( $_POST['cl_last'] ) ? cl_sanitization( $_POST['cl_last'] ) : $user_data->last_name,
				);

				if ( ! is_email( $valid_user_data['user_email'] ) ) {
					WPERECCP()->front->error->cl_set_error( 'email_invalid', __( 'Invalid email', 'essential-wp-real-estate' ) );
				}
			} else {
				// Set invalid user error
				WPERECCP()->front->error->cl_set_error( 'invalid_user', __( 'The user information is invalid', 'essential-wp-real-estate' ) );
			}
		}

		// Return user data
		return $valid_user_data;
	}


	function cl_purchase_form_required_fields() {
		$required_fields = array(
			'cl_email' => array(
				'error_id'      => 'invalid_email',
				'error_message' => __( 'Please enter a valid email address', 'essential-wp-real-estate' ),
			),
			'cl_first' => array(
				'error_id'      => 'invalid_first_name',
				'error_message' => __( 'Please enter your first name', 'essential-wp-real-estate' ),
			),
		);

		// Let payment gateways and other extensions determine if address fields should be required
		$require_address = apply_filters( 'cl_require_billing_address', WPERECCP()->front->tax->cl_use_taxes() && WPERECCP()->front->cart->cl_get_cart_total() );

		if ( $require_address ) {
			$required_fields['card_zip']        = array(
				'error_id'      => 'invalid_zip_code',
				'error_message' => __( 'Please enter your zip / postal code', 'essential-wp-real-estate' ),
			);
			$required_fields['card_city']       = array(
				'error_id'      => 'invalid_city',
				'error_message' => __( 'Please enter your billing city', 'essential-wp-real-estate' ),
			);
			$required_fields['billing_country'] = array(
				'error_id'      => 'invalid_country',
				'error_message' => __( 'Please select your billing country', 'essential-wp-real-estate' ),
			);
			$required_fields['card_state']      = array(
				'error_id'      => 'invalid_state',
				'error_message' => __( 'Please enter billing state / province', 'essential-wp-real-estate' ),
			);

			// Check if the Customer's Country has been passed in and if it has no states.
			if ( isset( $_POST['billing_country'] ) && isset( $required_fields['card_state'] ) ) {
				$customer_billing_country = cl_sanitization( $_POST['billing_country'] );
				$states                   = WPERECCP()->front->country->cl_get_shop_states( $customer_billing_country );

				// If this country has no states, remove the requirement of a card_state.
				if ( empty( $states ) ) {
					unset( $required_fields['card_state'] );
				}
			}
		}

		return apply_filters( 'cl_purchase_form_required_fields', $required_fields );
	}

	function cl_get_purchase_form_user( $valid_data = array() ) {
		if ( false === $valid_data || empty( $valid_data ) ) {
			// Return false
			return false;
		}
		// Initialize user
		$user    = false;
		$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		if ( $is_ajax ) {
			// Do not create or login the user during the ajax submission (check for errors only)
			return true;
		} elseif ( is_user_logged_in() ) {
			// Set the valid user as the logged in collected data
			$user = $valid_data['logged_in_user'];
		} elseif ( $valid_data['need_new_user'] === true || $valid_data['need_user_login'] === true ) {
			// New user registration
			if ( $valid_data['need_new_user'] === true ) {
				// Set user
				$user = $valid_data['new_user_data'];
				// Register and login new user
				$user['user_id'] = cl_register_and_login_new_user( $user );
				// User login
			} elseif ( $valid_data['need_user_login'] === true && ! $is_ajax ) {
				/*
				* The login form is now processed in the cl_process_purchase_login() function.
				* This is still here for backwards compatibility.
				* This also allows the old login process to still work if a user removes the
				* checkout login submit button.
				*
				* This also ensures that the customer is logged in correctly if they click "Purchase"
				* instead of submitting the login form, meaning the customer is logged in during the purchase process.
				*/

				// Set user
				$user = $valid_data['login_user_data'];

				// Login user
				if ( empty( $user ) || $user['user_id'] == -1 ) {
					cl_set_error( 'invalid_user', __( 'The user information is invalid', 'essential-wp-real-estate' ) );
					return false;
				} else {
					cl_log_user_in( $user['user_id'], $user['user_login'], $user['user_pass'] );
				}
			}
		}

		// Check guest checkout
		if ( false === $user && false === WPERECCP()->common->options->cl_no_guest_checkout() ) {
			// Set user
			$user = $valid_data['guest_user_data'];
		}

		// Verify we have an user
		if ( false === $user || empty( $user ) ) {
			// Return false
			return false;
		}

		// Get user first name
		if ( ! isset( $user['user_first'] ) || strlen( trim( $user['user_first'] ) ) < 1 ) {
			$user['user_first'] = isset( $_POST['cl_first'] ) ? strip_tags( trim( cl_sanitization( $_POST['cl_first'] ) ) ) : '';
		}

		// Get user last name
		if ( ! isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
			$user['user_last'] = isset( $_POST['cl_last'] ) ? strip_tags( trim( cl_sanitization( $_POST['cl_last'] ) ) ) : '';
		}

		// Get the user's billing address details
		$user['address']            = array();
		$user['address']['line1']   = ! empty( $_POST['card_address'] ) ? cl_sanitization( $_POST['card_address'] ) : false;
		$user['address']['line2']   = ! empty( $_POST['card_address_2'] ) ? cl_sanitization( $_POST['card_address_2'] ) : false;
		$user['address']['city']    = ! empty( $_POST['card_city'] ) ? cl_sanitization( $_POST['card_city'] ) : false;
		$user['address']['state']   = ! empty( $_POST['card_state'] ) ? cl_sanitization( $_POST['card_state'] ) : false;
		$user['address']['country'] = ! empty( $_POST['billing_country'] ) ? cl_sanitization( $_POST['billing_country'] ) : false;
		$user['address']['zip']     = ! empty( $_POST['card_zip'] ) ? cl_sanitization( $_POST['card_zip'] ) : false;

		if ( empty( $user['address']['country'] ) ) {
			$user['address'] = false; // Country will always be set if address fields are present
		}

		if ( ! empty( $user['user_id'] ) && $user['user_id'] > 0 && ! empty( $user['address'] ) ) {
			// Store the address in the user's meta so the cart can be pre-populated with it on return purchases
			update_user_meta( $user['user_id'], '_cl_user_address', $user['address'] );
		}

		// Return valid user
		return $user;
	}

	function cl_record_gateway_error( $title = '', $message = '', $parent = 0 ) {
		return cl_record_log( $title, $message, $parent, 'gateway_error' );
	}
}
