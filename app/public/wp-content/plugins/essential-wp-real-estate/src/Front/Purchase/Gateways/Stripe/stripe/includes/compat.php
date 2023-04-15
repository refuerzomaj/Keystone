<?php
use Essential\Restate\Common\Customer\Customer;

/**
 * Rewritten core functions to provide compatibility with a full AJAX checkout.
 *
 * @package CL_Stripe
 * @since   2.7.0
 */

/**
 * Maps serialized form data to global $_POST and $_REQUEST variables.
 *
 * This ensures any custom code that hooks in to actions inside an
 * AJAX processing step can utilize form field data.
 *
 * @since 2.7.3
 *
 * @param array $post_data $_POST data containing serialized form data.
 */
function _cls_map_form_data_to_request( $post_data ) {
	if ( ! isset( $post_data['form_data'] ) ) {
		return;
	}

	parse_str( $post_data['form_data'], $form_data );

	$post_arr = cl_sanitization($_POST);
	$_POST    = array_merge( $post_arr, $form_data );
	$post_arr = cl_sanitization($_POST);
	$req_arr = cl_sanitization($_REQUEST);
	$_REQUEST = array_merge( $req_arr, $post_arr );
}

/**
 * When dealing with payments certain aspects only work if the payment
 * is being created inside the `cl_process_purchase_form()` function.
 *
 * Since this gateway uses multiple steps via AJAX requests this context gets lost.
 * Calling this function "fakes" that we are still in this process when creating
 * a new payment.
 *
 * Mainly this prevents `cl_insert_payment()` from creating multiple customers for
 * the same user by ensuring the checkout email address is added to the existing customer.
 *
 * @since 2.7.0
 */
function _cls_fake_process_purchase_step() {
	// Save current errors.
	$errors = WPERECCP()->front->error->cl_get_errors();

	// Clear any errors that might be used as a reason to attempt a redirect in the following action.
	WPERECCP()->front->error->cl_clear_errors();

	// Don't run any attached actions twice.
	remove_all_actions( 'cl_pre_process_purchase' );

	// Pretend we are about to process a purchase.
	do_action( 'cl_pre_process_purchase' );

	// Clear any errors that may have been set in the previous action.
	WPERECCP()->front->error->cl_clear_errors();

	// Restore original errors.
	if ( ! empty( $errors ) ) {
		foreach ( $errors as $error_id => $error_message ) {
			cl_set_error( $error_id, $error_message );
		}
	}
}

/**
 * A rewritten version of `cls_get_purchase_form_user()` that can be run during AJAX.
 *
 * @since 2.7.0
 *
 * @return array
 */
function _cls_get_purchase_form_user( $valid_data = array() ) {
	// Initialize user
	$user = false;

	if ( is_user_logged_in() ) {

		// Set the valid user as the logged in collected data.
		$user = $valid_data['logged_in_user'];
	} elseif ( true === $valid_data['need_new_user'] || true === $valid_data['need_user_login'] ) {

		// Ensure $_COOKIE is available without a new HTTP request.
		add_action( 'set_logged_in_cookie', 'cls_set_logged_in_cookie_global' );

		// New user registration.
		if ( true === $valid_data['need_new_user'] ) {

			// Set user.
			$user = $valid_data['new_user_data'];

			// Register and login new user.
			$user['user_id'] = cl_register_and_login_new_user( $user );
		} elseif ( true === $valid_data['need_user_login'] ) { // User login.

			/*
			 * The login form is now processed in the cl_process_purchase_login() function.
			 * This is still here for backwards compatibility.
			 * This also allows the old login process to still work if a user removes the
			 * checkout login submit button.
			 *
			 * This also ensures that the customer is logged in correctly if they click "Purchase"
			 * instead of submitting the login form, meaning the customer is logged in during the purchase process.
			 */

			// Set user.
			$user = $valid_data['login_user_data'];

			// Login user.
			if ( empty( $user ) || -1 === $user['user_id'] ) {
				cl_set_error( 'invalid_user', __( 'The user information is invalid', 'essential-wp-real-estate' ) );
				return false;
			} else {
				cl_log_user_in( $user['user_id'], $user['user_login'], $user['user_pass'] );
			}
		}

		remove_action( 'set_logged_in_cookie', 'cls_set_logged_in_cookie_global' );
	}

	// Check guest checkout.
	if ( false === $user && false === WPERECCP()->common->options->cl_no_guest_checkout() ) {
		// Set user.
		$user = $valid_data['guest_user_data'];
	}

	// Verify we have an user.
	if ( false === $user || empty( $user ) ) {
		return false;
	}

	// Get user first name.
	if ( ! isset( $user['user_first'] ) || strlen( trim( $user['user_first'] ) ) < 1 ) {
		$user['user_first'] = isset( $_POST['cl_first'] ) ? strip_tags( trim( cl_sanitization( $_POST['cl_first'] ) ) ) : '';
	}

	// Get user last name.
	if ( ! isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
		$user['user_last'] = isset( $_POST['cl_last'] ) ? strip_tags( trim( cl_sanitization( $_POST['cl_last'] ) ) ) : '';
	}

	// Get the user's billing address details.
	$user['address']            = array();
	$user['address']['line1']   = ! empty( $_POST['card_address'] ) ? cl_sanitization( $_POST['card_address'] ) : '';
	$user['address']['line2']   = ! empty( $_POST['card_address_2'] ) ? cl_sanitization( $_POST['card_address_2'] ) : '';
	$user['address']['city']    = ! empty( $_POST['card_city'] ) ? cl_sanitization( $_POST['card_city'] ) : '';
	$user['address']['state']   = ! empty( $_POST['card_state'] ) ? cl_sanitization( $_POST['card_state'] ) : '';
	$user['address']['country'] = ! empty( $_POST['billing_country'] ) ? cl_sanitization( $_POST['billing_country'] ) : '';
	$user['address']['zip']     = ! empty( $_POST['card_zip'] ) ? cl_sanitization( $_POST['card_zip'] ) : '';

	if ( empty( $user['address']['country'] ) ) {
		$user['address'] = false; // Country will always be set if address fields are present.
	}

	if ( ! empty( $user['user_id'] ) && $user['user_id'] > 0 && ! empty( $user['address'] ) ) {
		if ( function_exists( 'cl_maybe_add_customer_address' ) ) {
			$customer = WPERECCP()->common->dbcustomer->get_customer_by( 'user_id', $user['user_id'] );
			if ( $customer ) {
				cl_maybe_add_customer_address( $customer->id, $user['address'] );
			}
		} else {
			// Store the address in the user's meta so the cart can be pre-populated with it on return purchases.
			update_user_meta( $user['user_id'], '_cl_user_address', $user['address'] );
		}
	}

	// Return valid user.
	return $user;
}

/**
 * A rewritten version of `cl_process_purchase_form()` that allows for full AJAX processing.
 *
 * `cl_process_purchase_form()` is run up until:
 *
 * if ( $is_ajax ) {
 *   echo 'success';
 *   wp_die();
 * }
 *
 * Then this function is called which reruns the start of `cl_process_purchase_form()` and
 * continues the rest of the processing.
 *
 * @since 2.7.0
 */
function _cls_process_purchase_form() {
	 // Catch exceptions at a high level.
	try {
		// `cl_process_purchase_form()` and subsequent code executions are written
		// expecting form processing to happen via a POST request from a client form.
		//
		// This version is called from an AJAX POST request, so the form data is sent
		// in a serialized string to ensure all fields are available.
		//
		// Map and merge formData to $_POST so it's accessible in other functions.
		$post_arr = cl_sanitization($_POST);
		$_POST    = array_merge( $post_arr, $form_data );
		$post_arr = cl_sanitization($_POST);
		$req_arr = cl_sanitization($_REQUEST);
		$_REQUEST = array_merge( $req_arr, $post_arr );

		/**
		 * @since unknown
		 * @todo document
		 */
		do_action( 'cl_pre_process_purchase' );

		// Make sure the cart isn't empty.
		if ( ! WPERECCP()->front->cart->cl_get_cart_contents() && ! WPERECCP()->front->cart->cl_cart_has_fees() ) {
			echo '1';
			throw new \Exception( esc_html__( 'Your cart is empty.', 'essential-wp-real-estate' ) );
		}

		if ( ! isset( $_POST['cl-process-checkout-nonce'] ) ) {
			echo '2';
			cl_debug_log( __( 'Missing nonce when processing checkout.', 'essential-wp-real-estate' ), true );
		}

		$nonce          = isset( $_POST['cl-process-checkout-nonce'] ) ? cl_sanitization( $_POST['cl-process-checkout-nonce'] ) : '';
		$nonce_verified = wp_verify_nonce( $nonce, 'cl-process-checkout' );

		if ( false === $nonce_verified ) {
			throw new \Exception( esc_html__( 'Error processing purchase. Please reload the page and try again.', 'essential-wp-real-estate' ) );
		}

		// Validate the form $_POST data.
		$valid_data = WPERECCP()->front->gateways->cl_purchase_form_validate_fields();

		// Allow themes and plugins to hook to errors.
		//
		// In the future these should throw exceptions, existing `cl_set_error()` usage will be caught below.
		do_action( 'cl_checkout_error_checks', $valid_data, cl_sanitization($_POST) );

		// Validate the user.
		$user = _cls_get_purchase_form_user( $valid_data );

		// Let extensions validate fields after user is logged in if user has used login/registration form
		do_action( 'cl_checkout_user_error_checks', $user, $valid_data, cl_sanitization($_POST) );

		if ( false === $valid_data || WPERECCP()->front->error->cl_get_errors() || ! $user ) {
			$errors = WPERECCP()->front->error->cl_get_errors();

			if ( is_array( $errors ) ) {
				throw new \Exception( current( $errors ) );
			}
		}

		// Setup user information.
		$user_info = array(
			'id'         => $user['user_id'],
			'email'      => $user['user_email'],
			'first_name' => $user['user_first'],
			'last_name'  => $user['user_last'],
			'discount'   => $valid_data['discount'],
			'address'    => ! empty( $user['address'] ) ? $user['address'] : array(),
		);

		// Update a customer record if they have added/updated information.
		$customer = new Customer( $user_info['email'] );

		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];

		if ( empty( $customer->name ) || $name != $customer->name ) {
			$update_data = array(
				'name' => $name,
			);

			// Update the customer's name and update the user record too.
			$customer->update( $update_data );

			wp_update_user(
				array(
					'ID'         => get_current_user_id(),
					'first_name' => $user_info['first_name'],
					'last_name'  => $user_info['last_name'],
				)
			);
		}

		// Update the customer's address if different to what's in the database
		$address = wp_parse_args(
			$user_info['address'],
			array(
				'line1'   => '',
				'line2'   => '',
				'city'    => '',
				'state'   => '',
				'country' => '',
				'zip'     => '',
			)
		);

		$address = array(
			'address'     => $address['line1'],
			'address2'    => $address['line2'],
			'city'        => $address['city'],
			'region'      => $address['state'],
			'country'     => $address['country'],
			'postal_code' => $address['zip'],
		);

		if ( ! empty( $user['user_id'] ) && $user['user_id'] > 0 && ! empty( $address ) ) {
			if ( function_exists( 'cl_maybe_add_customer_address' ) ) {
				$customer = WPERECCP()->common->dbcustomer->get_customer_by( 'user_id', $user['user_id'] );
				if ( $customer ) {
					cl_maybe_add_customer_address( $customer->id, $user['address'] );
				}
			} else {
				// Store the address in the user's meta so the cart can be pre-populated with it on return purchases.
				update_user_meta( $user['user_id'], '_cl_user_address', $user['address'] );
			}
		}

		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

		$card_country = isset( $valid_data['cc_info']['card_country'] ) ? $valid_data['cc_info']['card_country'] : false;
		$card_state   = isset( $valid_data['cc_info']['card_state'] ) ? $valid_data['cc_info']['card_state'] : false;
		$card_zip     = isset( $valid_data['cc_info']['card_zip'] ) ? $valid_data['cc_info']['card_zip'] : false;

		// Set up the unique purchase key. If we are resuming a payment, we'll overwrite this with the existing key.
		$purchase_key     = strtolower( md5( $user['user_email'] . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'cl', true ) ) );
		$existing_payment = WPERECCP()->front->session->get( 'cl_resume_payment' );

		if ( ! empty( $existing_payment ) ) {
			$payment = new Clpayment( $existing_payment );

			if ( $payment->is_recoverable() && ! empty( $payment->key ) ) {
				$purchase_key = $payment->key;
			}
		}

		// Setup purchase information.
		$purchase_data = array(
			'listing'      => WPERECCP()->front->cart->cl_get_cart_contents(),
			'fees'         => WPERECCP()->front->cart->cl_cart_has_fees(),        // Any arbitrary fees that have been added to the cart
			'subtotal'     => WPERECCP()->front->cart->cl_get_cart_subtotal(),    // Amount before taxes and discounts
			'discount'     => WPERECCP()->front->discountaction->cl_get_cart_discounted_amount(), // Discounted amount
			'tax'          => WPERECCP()->front->cart->cl_get_cart_tax(),               // Taxed amount
			'tax_rate'     => WPERECCP()->front->cart->cl_get_cart_tax_rate( $card_country, $card_state, $card_zip ), // Tax rate
			'price'        => WPERECCP()->front->cart->cl_get_cart_total(),    // Amount after taxes
			'purchase_key' => $purchase_key,
			'user_email'   => $user['user_email'],
			'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'user_info'    => stripslashes_deep( $user_info ),
			'post_data'    => cl_sanitization($_POST),
			'cart_details' => WPERECCP()->front->cart->cl_get_cart_content_details(),
			'gateway'      => $valid_data['gateway'],
			'card_info'    => $valid_data['cc_info'],
		);

		// Add the user data for hooks
		$valid_data['user'] = $user;

		// Allow themes and plugins to hook before the gateway
		do_action( 'cl_checkout_before_gateway', cl_sanitization($_POST), $user_info, $valid_data );

		// Store payment method data.
		$purchase_data['gateway_nonce'] = wp_create_nonce( 'cl-gateway' );

		// Allow the purchase data to be modified before it is sent to the gateway
		$purchase_data = apply_filters(
			'cl_purchase_data_before_gateway',
			$purchase_data,
			$valid_data
		);

		// Setup the data we're storing in the purchase session
		$session_data = $purchase_data;

		// Used for showing listing links to non logged-in users after purchase, and for other plugins needing purchase data.
		WPERECCP()->front->cart->cl_set_purchase_session( $session_data );

		do_action( 'cl_gateway_' . $purchase_data['gateway'], $purchase_data );
	} catch ( \Exception $e ) {
		return wp_send_json_error(
			array(
				'message' => $e->getMessage(),
			)
		);
	}
}
add_action( 'wp_ajax_cls_process_purchase_form', '_cls_process_purchase_form' );
add_action( 'wp_ajax_nopriv_cls_process_purchase_form', '_cls_process_purchase_form' );

if ( ! function_exists( 'cl_is_dev_environment' ) ) {
	/**
	 * Check the network site URL for signs of being a development environment.
	 *
	 * @since 3.0
	 *
	 * @return bool $retval True if dev, false if not.
	 */
	function cl_is_dev_environment() {
		// Assume not a development environment
		$retval = false;

		// Get this one time and use it below
		$network_url = network_site_url( '/' );

		// Possible strings
		$strings = array(

			// Popular port suffixes
			':8888',      // This is common with MAMP on OS X

			// Popular development TLDs
			'.dev',       // VVV
			'.local',     // Local
			'.test',      // IETF
			'.example',   // IETF
			'.invalid',   // IETF
			'.localhost', // IETF

			// Popular development subdomains
			'dev.',

			// Popular development domains
			'localhost',
			'example.com',
		);

		// Loop through all strings
		foreach ( $strings as $string ) {
			if ( stristr( $network_url, $string ) ) {
				$retval = $string;
				break;
			}
		}

		// Filter & return
		return apply_filters( 'cl_is_dev_environment', $retval );
	}
}
