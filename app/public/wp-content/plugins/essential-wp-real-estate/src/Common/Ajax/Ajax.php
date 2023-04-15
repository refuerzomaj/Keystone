<?php
namespace Essential\Restate\Common\Ajax;

use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Common\Customer\Customer;
use Essential\Restate\Front\Purchase\Payments\Clpayment;

use Essential\Restate\Front\Purchase\Cart\Cartactions;
use Essential\Restate\Front\Front;

/**
 * The admin class
 */
class Ajax {

	use Traitval;

	public function initialize() {

		add_action( 'wp_ajax_cl_save_pagelayout_archive', array( $this, 'cl_save_pagelayout_archive_setting' ) );
		add_action( 'wp_ajax_nopriv_cl_save_pagelayout_archive', array( $this, 'cl_save_pagelayout_archive_setting' ) );

		add_action( 'wp_ajax_cl_save_pagelayout_archive_list', array( $this, 'cl_save_pagelayout_archive_list_setting' ) );
		add_action( 'wp_ajax_nopriv_cl_save_pagelayout_archive_list', array( $this, 'cl_save_pagelayout_archive_list_setting' ) );

		add_action( 'wp_ajax_cl_single_settings_layout_save', array( $this, 'cl_save_pagelayout_single_setting' ) );
		add_action( 'wp_ajax_nopriv_cl_single_settings_layout_save', array( $this, 'cl_save_pagelayout_single_setting' ) );

		add_action( 'wp_ajax_cl_save_settings_layout_add', array( $this, 'cl_save_pagelayout_add_setting' ) );
		add_action( 'wp_ajax_nopriv_cl_save_settings_layout_add', array( $this, 'cl_save_pagelayout_add_setting' ) );

		add_action( 'wp_ajax_ccl_add_custom_field', array( $this, 'cl_custom_field_ajax' ));
		add_action( 'wp_ajax_nopriv_ccl_add_custom_field', array( $this, 'cl_custom_field_ajax' ));

		add_action( 'wp_ajax_cl_save_settings_layout_custom_field', array( $this, 'cl_save_pagelayout_custom_field_setting' ) );
		add_action( 'wp_ajax_nopriv_cl_save_settings_layout_custom_field', array( $this, 'cl_save_pagelayout_custom_field_setting' ) );

		add_action( 'wp_ajax_ccl_delete_custom_field', array( $this, 'ccl_delete_custom_field_ajax' ));
		add_action( 'wp_ajax_nopriv_ccl_delete_custom_field', array( $this, 'ccl_delete_custom_field_ajax' ));

		add_action( 'wp_ajax_cl_save_settings_layout_comp_field_list', array( $this, 'cl_save_pagelayout_comp_field_list' ) );
		add_action( 'wp_ajax_nopriv_cl_save_settings_layout_comp_field_list', array( $this, 'cl_save_pagelayout_comp_field_list' ) );

		add_action( 'wp_ajax_cl_save_settings_layout_search', array( $this, 'cl_save_pagelayout_search_setting' ) );
		add_action( 'wp_ajax_nopriv_cl_save_settings_layout_search', array( $this, 'cl_save_pagelayout_search_setting' ) );

		add_action( 'wp_ajax_listing_abuse_dialog_action', array( $this, 'listing_abuse_dialog_action_func' ) );
		add_action( 'wp_ajax_nopriv_listing_abuse_dialog_action', array( $this, 'listing_abuse_dialog_action_func' ) );

		add_action( 'wp_ajax_cl_enquiry', array( $this, 'cl_enquiry_func' ) );
		add_action( 'wp_ajax_nopriv_cl_enquiry', array( $this, 'cl_enquiry_func' ) );

		// -- Single Listing add_to_favorite call
		add_action( 'wp_ajax_cl_add_to_favorite', array( $this, 'cl_add_to_favorite_func' ) );
		add_action( 'wp_ajax_nopriv_cl_add_to_favorite', array( $this, 'cl_add_to_favorite_func' ) );

		add_action( 'wp_ajax_cl_delete_listing_func', array( $this, 'cl_delete_listing_func' ) );
		add_action( 'wp_ajax_nopriv_cl_delete_listing_func', array( $this, 'cl_delete_listing_func' ) );

		add_action( 'wp_ajax_cl_compare_func', array( $this, 'cl_compare_func' ) );
		add_action( 'wp_ajax_nopriv_cl_compare_func', array( $this, 'cl_compare_func' ) );

		add_action( 'wp_ajax_listing_clear_cache', array( $this, 'cl_listing_clear_cache' ) );

		add_action( 'wp_ajax_cl_add_to_cart', array( $this, 'cl_ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_cl_add_to_cart', array( $this, 'cl_ajax_add_to_cart' ) );

		add_action( 'wp_ajax_cl_remove_from_cart', array( $this, 'cl_ajax_remove_from_cart' ) );
		add_action( 'wp_ajax_nopriv_cl_remove_from_cart', array( $this, 'cl_ajax_remove_from_cart' ) );

		add_action( 'cl_purchase_form', array( $this, 'cl_show_purchase_form' ) );

		add_action( 'wp_ajax_cl_load_gateway', array( $this, 'cl_load_ajax_gateway' ) );
		add_action( 'wp_ajax_nopriv_cl_load_gateway', array( $this, 'cl_load_ajax_gateway' ) );

		add_action( 'cl_purchase', array( $this, 'cl_process_purchase_form' ) );
		add_action( 'wp_ajax_cl_process_checkout', array( $this, 'cl_process_purchase_form' ) );
		add_action( 'wp_ajax_nopriv_cl_process_checkout', array( $this, 'cl_process_purchase_form' ) );

		add_action( 'wp_ajax_cl_get_shop_states', array( $this, 'cl_ajax_get_states_field' ) );
		add_action( 'wp_ajax_nopriv_cl_get_shop_states', array( $this, 'cl_ajax_get_states_field' ) );

		add_action( 'wp_ajax_cl_apply_discount', array( $this, 'cl_ajax_apply_discount' ) );
		add_action( 'wp_ajax_nopriv_cl_apply_discount', array( $this, 'cl_ajax_apply_discount' ) );

		add_action( 'wp_ajax_cl_remove_discount', array( $this, 'cl_ajax_remove_discount' ) );
		add_action( 'wp_ajax_nopriv_cl_remove_discount', array( $this, 'cl_ajax_remove_discount' ) );

		add_action('wp_ajax_cl_usr_login', array( $this, 'cl_usr_login') );
		add_action('wp_ajax_nopriv_cl_usr_login', array( $this,'cl_usr_login') );

		add_action('wp_ajax_cl_user_registration',  array( $this,'cl_user_registration'));
		add_action('wp_ajax_nopriv_cl_user_registration',  array( $this,'cl_user_registration'));
	}

	function cl_enquiry_func() {
		if ( ! wp_verify_nonce( $_REQUEST['wperesds_enquiry'], 'wperesds-enquiry-form' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Nonce verification failed!', 'essential-wp-real-estate' ),
				)
			);
		}

		$name          = isset( $_POST['name'] ) ? cl_sanitization( $_POST['name'] ) : '';
		$http_referer  = isset( $_POST['_wp_http_referer'] ) ? cl_sanitization( $_POST['_wp_http_referer'] ) : '';
		$created_for   = isset( $_POST['created_for'] ) ? cl_sanitization( $_POST['created_for'] ) : 1;
		$email         = isset( $_POST['email'] ) ? cl_sanitization( $_POST['email'] ) : '';
		$phone         = isset( $_POST['phone'] ) ? cl_sanitization( $_POST['phone'] ) : '';
		$message       = isset( $_POST['message'] ) ? cl_sanitization( $_POST['message'] ) : '';
		$listing_email = isset( $_POST['listing_email'] ) ? cl_sanitization( $_POST['listing_email'] ) : '';

		$args = array(
			'name'        => $name,
			'email'       => $email,
			'phone'       => $phone,
			'message'     => $message,
			'created_for' => $created_for,
		);

		$subject = esc_html__('Listing enquiry message', 'essential-wp-real-estate' );

		$sent_message  = '';
		$sent_message .= 'Name - ' . $name . "\r\n" . 'Email - ' . $email . "\r\n" . 'Phone - ' . $phone . "\r\n" . 'Referer - ' . $http_referer . "\r\n" . 'Messege -' . $message;
		$headers       = 'From: ' . $email . "\r\n" . 'Reply-To: ' . $email . "\r\n";

		// Here put your Validation and send mail
		$sent = wp_mail( $listing_email, $subject, strip_tags( $sent_message ), $headers );
		// $sent = wp_mail($listing_email, strip_tags($sent_message), $headers);

		if ( $sent ) {
			$insert_id = cl_listing_insert_enquiry_message( $args );
			if ( $insert_id ) {
				wp_send_json_success(
					array(
						'message' => esc_html__( 'Enquiry has been sent successfully!', 'essential-wp-real-estate' ),
					)
				);
			}
		}else {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Failed to send message', 'essential-wp-real-estate' ),
				)
			);
		}

	}



	
	function cl_usr_login(){
	
		if (!wp_verify_nonce($_REQUEST['wperesds_login_form'], 'wperesds_log_form')) {
			wp_send_json_error(
				array(
					'message' => esc_html__('Nonce verification failed!', 'essential-wp-real-estate'),
				)
			);
		}
	
		if (isset($_POST)) {
			$creds                  = array();
			$creds['user_login']    = stripslashes(trim(sanitize_text_field($_POST['log'])));
			$creds['user_password'] = stripslashes(trim(sanitize_text_field($_POST['pwd'])));

			$user              = wp_signon($creds, true);

			if (!is_wp_error($user)) {
	
				$userID = $user->ID;
				wp_set_current_user($userID, $_POST['log']);
				wp_set_auth_cookie($userID, true, false);
	
				wp_send_json_success(
					array(
						'message' => esc_html__('Successfully redirecting ...', 'essential-wp-real-estate'),
					)
				);
			} else {
				wp_send_json_error(
					array(
						'message' => '<strong>' . esc_html__('ERROR', 'essential-wp-real-estate') . '</strong>: ' . esc_html__('Username or password is incorrect.', 'essential-wp-real-estate'),
					)
				);
			}
		}
	}


	function cl_user_registration()
	{
		if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'cl_resgis_form')) {
			wp_send_json_error(
				array(
					'message' => esc_html__('Nonce verification failed!', 'essential-wp-real-estate'),
				)
			);
		}
	
		global $wpdb;
		if ($_POST) {
	
			$fisrt_name   = sanitize_text_field($_POST['first_name']);
			$last_name    = sanitize_text_field($_POST['last_name']);
			$username     = sanitize_text_field($_POST['username']);
			$email        = sanitize_text_field($_POST['email']);
			$password     = sanitize_text_field($_POST['password']);
			$confPassword = sanitize_text_field($_POST['conf_password']);
			$google_captcha_secr_api = sanitize_text_field($_POST['google_captcha_secr_api']);
			$google_captcha_sitekey = sanitize_text_field($_POST['google_captcha_sitekey']);
	
			$error = array();
			if (strpos($username, ' ') !== false) {
				$error['error_msg'] = esc_html__('Username has Space', 'essential-wp-real-estate');
			}
	
			if (empty($username)) {
				$error['error_msg'] = esc_html__('Needed Username must', 'essential-wp-real-estate');
			}
	
			if (username_exists($username)) {
				$error['error_msg'] = esc_html__('Username already exists', 'essential-wp-real-estate');
			}
	
			if (!is_email($email)) {
				$error['error_msg'] = esc_html__('Email has no valid value', 'essential-wp-real-estate');
			}
	
			if (email_exists($email)) {
				$error['error_msg'] = esc_html__('Email already exists', 'essential-wp-real-estate');
			}
	
			if (strcmp($password, $confPassword) !== 0) {
				$error['error_msg'] = esc_html__("Password didn't match", 'essential-wp-real-estate');
			}
			if(!empty($google_captcha_secr_api) && !empty($google_captcha_sitekey)) {
				if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
					$error['error_msg']   = esc_html__("Plese check on the reCAPTCHA box.", 'essential-wp-real-estate' );
				}
			}
	
			if (count($error) == 0) {

				if(!empty($google_captcha_secr_api) && !empty($google_captcha_sitekey)) {
					if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
						// Google secret API
						$secretAPIkey = $google_captcha_secr_api;
						// reCAPTCHA response verification
						$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretAPIkey.'&response='.$_POST['g-recaptcha-response']);
						// Decode JSON data
						$response = json_decode($verifyResponse);
							if($response->success){

								$userdata = array(
									'user_login'   => $username,
									'user_pass'    => $password,
									'first_name'   => $first_name,
									'last_name'    => $last_name,
									'user_email'   => $email,
									'display_name' => $first_name . $last_name,
									'role'         => 'listing_user',
								);

								$user_id = wp_insert_user( $userdata );

							}  

					} else{ 
						$error['class'] = 'danger';
						$error['error_msg']   = esc_html__("Plese check on the reCAPTCHA box.", 'essential-wp-real-estate' );
					} 
				}else{
					$userdata = array(
						'user_login'   => $username,
						'user_pass'    => $password, // When creating an user, `user_pass` is expected.
						'first_name'   => $fisrt_name,
						'last_name'    => $last_name,
						'user_email'   => $email,
						'display_name' => $fisrt_name . $last_name,
						'role'         => 'listing_user',
					);
				}
	
				$user_id = wp_insert_user($userdata);
				// On success.
				if (!is_wp_error($user_id)) {
					$creds                  = array();
					$creds['user_login']    = stripslashes(trim(sanitize_text_field($userdata['user_login'])));
					$creds['user_password'] = stripslashes(trim(sanitize_text_field($userdata['user_pass'])));
					$creds['remember']		= true;
					$user					= wp_signon($creds, true);
					wp_send_json_success(
						array(
							'message' => esc_html__('Successfully registered.', 'essential-wp-real-estate'),
						)
					);
				}
			} else {
				wp_send_json_error(
					array(
						'message' => esc_html__('Sometings wrong goes here', 'essential-wp-real-estate'),
						'error'   => wp_json_encode($error),
					)
				);
			}
		}

	}


	function cl_ajax_remove_discount() {
		if ( isset( $_POST['code'] ) ) {

			WPERECCP()->front->discountaction->cl_unset_cart_discount( urldecode( cl_sanitization( $_POST['code'] ) ) );

			$total = WPERECCP()->front->cart->cl_get_cart_total();

			$return = array(
				'total_plain' => $total,
				'total'       => html_entity_decode( WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
				'code'        => cl_sanitization( $_POST['code'] ),
				'discounts'   => WPERECCP()->front->discountaction->cl_get_cart_discounts(),
				'html'        => WPERECCP()->front->discountaction->cl_get_cart_discounts_html(),
			);

			wp_send_json( $return );
		}
		die();
	}


	function cl_ajax_apply_discount() {
		if ( isset( $_POST['code'] ) ) {

			$discount_code = cl_sanitization( $_POST['code'] );

			$return = array(
				'msg'  => '',
				'code' => $discount_code,
			);

			$user = '';

			if ( is_user_logged_in() ) {
				$user = get_current_user_id();
			} else {
				parse_str( cl_sanitization( $_POST['form'] ), $form );
				if ( ! empty( $form['cl_email'] ) ) {
					$user = urldecode( $form['cl_email'] );
				}
			}

			if ( WPERECCP()->front->discountaction->cl_is_discount_valid( $discount_code, $user ) ) {
				$discount  = WPERECCP()->front->discountaction->cl_get_discount_by_code( $discount_code );
				$amount    = WPERECCP()->front->discountaction->cl_format_discount_rate( WPERECCP()->front->discountaction->cl_get_discount_type( $discount->ID ), WPERECCP()->front->discountaction->cl_get_discount_amount( $discount->ID ) );
				$discounts = WPERECCP()->front->discountaction->cl_set_cart_discount( $discount_code );
				$total     = WPERECCP()->front->cart->cl_get_cart_total( $discounts );

				$return = array(
					'msg'         => 'valid',
					'amount'      => $amount,
					'total_plain' => $total,
					'total'       => html_entity_decode( WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
					'code'        => $discount_code,
					'html'        => WPERECCP()->front->discountaction->cl_get_cart_discounts_html( $discounts ),
				);
			} else {
				$errors        = WPERECCP()->front->error->cl_get_errors();
				$return['msg'] = $errors['cl-discount-error'];
				WPERECCP()->front->error->cl_unset_error( 'cl-discount-error' );
			}

			// Allow for custom discount code handling
			$return = apply_filters( 'cl_ajax_discount_response', $return );

			echo json_encode( $return );
		}
		die();
	}
	

	function cl_ajax_get_states_field() {
		if ( empty( $_POST['country'] ) ) {
			$_POST['country'] = WPERECCP()->front->country->cl_get_shop_country();
		}

		$nonce          = isset( $_POST['nonce'] ) ? cl_sanitization( $_POST['nonce'] ) : '';
		$nonce_verified = wp_verify_nonce( $nonce, 'cl-country-field-nonce' );

		if ( false !== $nonce_verified ) {
			$states = WPERECCP()->front->country->cl_get_shop_states( cl_sanitization( $_POST['country'] ) );

			if ( ! empty( $states ) ) {

				$args = array(
					'name'             => cl_sanitization( $_POST['field_name'] ),
					'id'               => cl_sanitization( $_POST['field_name'] ),
					'class'            => cl_sanitization( $_POST['field_name'] ) . '  cl-select',
					'options'          => $states,
					'show_option_all'  => false,
					'show_option_none' => false,
				);

				$response = WPERECCP()->admin->settings_instances->select( $args );
			} else {

				$response = 'nostates';
			}

			echo '' . $response;
		}

		die();
	}


	public function cl_show_purchase_form() {
		return cl_get_template( 'checkout/purchase_form.php' );
	}


	function cl_load_ajax_gateway() {
		if ( ! isset( $_POST['nonce'] ) ) {
			echo array( 'error' => 'true' );
		}

		if ( isset( $_POST['cl_payment_mode'] ) && isset( $_POST['nonce'] ) ) {
			$payment_mode = cl_sanitization( $_POST['cl_payment_mode'] );
			$nonce        = cl_sanitization( $_POST['nonce'] );

			$nonce_verified = wp_verify_nonce( $nonce, 'cl-gateway-selected-' . $payment_mode );

			if ( false !== $nonce_verified ) {

				do_action( 'cl_purchase_form' );
			}

			exit();
		}
	}

	public function cl_listing_clear_cache() {
		echo cl_sanitization( $_POST['action'] );
		die();
	}

	function cl_ajax_remove_from_cart() {
		if ( ! isset( $_POST['nonce'] ) ) {
			$return = array(
				'error' => 'Nonce not found',
			);
			echo json_encode( $return );
		}

		if ( isset( $_POST['cart_item'] ) && isset( $_POST['nonce'] ) ) {

			$cart_item      = absint( cl_sanitization( $_POST['cart_item'] ) );
			$nonce          = cl_sanitization( $_POST['nonce'] );
			$nonce_verified = wp_verify_nonce( $nonce, 'cl-remove-cart-item' );
			if ( false === $nonce_verified ) {
				$return = array( 'removed' => 0 );
			} else {
				WPERECCP()->front->cart->remove( $cart_item );

				$return = array(
					'removed'       => 1,
					'subtotal'      => html_entity_decode( WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->cart->get_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
					'total'         => html_entity_decode( WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->cart->get_total() ) ), ENT_COMPAT, 'UTF-8' ),
					'cart_quantity' => html_entity_decode( WPERECCP()->front->cart->get_quantity() ),
				);
				if ( WPERECCP()->front->tax->cl_use_taxes() ) {
					$cart_tax      = (float) WPERECCP()->front->cart->get_tax();
					$return['tax'] = html_entity_decode( WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $cart_tax ) ), ENT_COMPAT, 'UTF-8' );
				}
			}

			$return = apply_filters( 'cl_ajax_remove_from_cart_response', $return );

			echo json_encode( $return );
		}
		die();
	}

	public function cl_save_pagelayout_search_setting() {
		check_ajax_referer( 'cl_all_settings-options', '_wpnonce' );
		$search_setting = cl_sanitization( $_POST['search_setting'] );

		update_option( 'cl_search_builder_setting', json_encode( $search_setting ) );
		die();
	}

	public function cl_save_pagelayout_add_setting() {
		check_ajax_referer( 'cl_all_settings-options', '_wpnonce' );
		$add_setting = cl_sanitization( $_POST['add_setting'] );

		update_option( 'cl_add_builder_setting', json_encode( $add_setting ) );
		die();
	}

	public function cl_custom_field_ajax() {

		$existing_data = get_option('cl_custom_meta_settings');
	
		$field_name = str_replace(' ', '', $_POST['field_label']);
		$field_label = $_POST['field_label'];
		$field_default_value = $_POST['field_default_value'];	
		$field_type = $_POST['field_type'];
	
		$set_option = array(
			'field_name' => $field_name,
			'field_label' => $field_label,
			'field_default_value' => $field_default_value,
			'field_type' => $field_type,
		);
		
		if($existing_data){
			array_push($existing_data,$set_option);
		}else{
			$existing_data = array();
			$existing_data[] = $set_option;
		}
	
		update_option( 'cl_custom_meta_settings', $existing_data );
	?>
		<?php
		if(!empty($set_option)){
	?>
	<?php if($field_type == 'textarea'){ ?>
		<div class="custom-field-item">
			<label><?php echo $field_label?></label>
			<textarea name="<?php echo $field_name;?>" disabled><?php echo esc_attr( $field_default_value ) ?></textarea>
			<a href="javascript:void(0)" class="delete-field" data-field_name="<?php echo esc_attr($field_name);?>"><?php esc_html_e('Delete','essential-wp-real-estate');?></a>
		</div>
	<?php }else{ ?>
		<div class="custom-field-item">
			<label><?php echo $field_label?></label>
			<input type="<?php echo $field_type;?>" name="<?php echo $field_name;?>" value="<?php echo esc_attr( $field_default_value ) ?>" disabled>
			<a class="delete-field" data-field_name="<?php echo esc_attr($field_name);?>"><?php esc_html_e('Delete','essential-wp-real-estate');?></a>
		</div>
	<?php } ?>
	<?php }
		die();
	}
	
	public function cl_save_pagelayout_custom_field_setting() {
		check_ajax_referer( 'cl_all_settings-options', '_wpnonce' );

		$existing_add_listing_meta = get_option('cl_add_builder_setting');
	
		$existing_custom_field_data = get_option('cl_custom_meta_settings');
		$field_name = array();
		foreach($existing_custom_field_data as $data){
			$field_name[] =	$this->prefix.$data['field_name'];
		}

		$add_listing_meta = json_decode($existing_add_listing_meta, true);
		$add_listing_meta_disabled_data = $add_listing_meta['disabled'];

		if(!empty($add_listing_meta_disabled_data)){
			$add_listing_meta_disabled['disabled'] = array_unique(array_merge($add_listing_meta_disabled_data,$field_name));
		}else{
			$add_listing_meta_disabled['disabled'] = $field_name;
		}
		
		$add_listing_meta_enabled['enabled'] = $add_listing_meta['enabled'];

		$add_listing_meta_all_data = array_merge($add_listing_meta_enabled,$add_listing_meta_disabled);

		update_option( 'cl_add_builder_setting', json_encode( $add_listing_meta_all_data ) );

		die();
	}

	public function ccl_delete_custom_field_ajax(){

		$existing_data = get_option('cl_custom_meta_settings');
	
		$field_id = $_POST['field_id'];
		$field_name = $_POST['field_name'];
	
		if($existing_data){
			unset($existing_data[$field_id]);
		}
	
		update_option( 'cl_custom_meta_settings', $existing_data );
	
		$existing_add_listing_meta = get_option('cl_add_builder_setting');
	
		$add_listing_meta = json_decode($existing_add_listing_meta,true);
		$add_listing_meta_disabled = $add_listing_meta['disabled'];
	
		$add_listing_meta_enabled = $add_listing_meta['enabled'];
	
		$field_name = $this->prefix.$field_name;
	
		$result = array();
		$result_filter_data = array();		
	
		foreach($add_listing_meta_enabled as $keydfd => $inner) {
			$result[key($inner)] = current($inner);   
			  
			if (($key = array_search($field_name, $inner)) !== false) {
				unset($inner[$key]);
			}
			$result_filter_data[$keydfd] = $inner;
		}
	
		$add_listing_meta_enabled_data['enabled'] = $result_filter_data;

		if(!empty($add_listing_meta_disabled)){
			if (($key = array_search($field_name, $add_listing_meta_disabled)) !== false) {
				unset($add_listing_meta_disabled[$key]);
			}
			$add_listing_meta_disabled_data['disabled'] = $add_listing_meta_disabled;
		}else{
			$add_listing_meta_disabled_data['disabled'] = $add_listing_meta_disabled;
		}
	
		$add_listing_meta_all_data = array_merge($add_listing_meta_enabled_data,$add_listing_meta_disabled_data);

		update_option( 'cl_add_builder_setting', json_encode( $add_listing_meta_all_data ) );
	
		die();
	}

	public function cl_save_pagelayout_comp_field_list() {
		check_ajax_referer( 'cl_all_settings-options', '_wpnonce' );
		$comp_field_list = cl_sanitization( $_POST['comp_field_list'] );

		update_option( 'cl_comp_field_list_builder_setting', json_encode( $comp_field_list ) );
		die();
	}

	public function cl_save_pagelayout_single_setting() {
		check_ajax_referer( 'cl_all_settings-options', '_wpnonce' );
		$single_setting = cl_sanitization( $_POST['single_setting'] );
		$single_setting = WPERECCP()->admin->settings_instances->admin_settings_extend( $single_setting, WPERECCP()->admin->single_instance->single_default_layout_setting() );
		update_option( 'cl_single_settings_layout', json_encode( $single_setting ) );
		die();
	}

	public function cl_save_pagelayout_archive_setting() {
		check_ajax_referer( 'cl_all_settings-options', '_wpnonce' );
		$archive_setting = cl_sanitization( $_POST['archive_setting'] );

		if ( ! isset( $archive_setting['sectionzero']['topleft'] ) ) {
			$archive_setting['sectionzero']['topleft']['badge']['active'] = 0;
		}
		$archive_setting = WPERECCP()->admin->settings_instances->admin_settings_extend( $archive_setting, WPERECCP()->admin->archive_instance->cl_archive_default_grid_setting() );

		update_option( 'cl_archive_setting_grid_view', json_encode( $archive_setting ) );
		die();
	}
	public function cl_save_pagelayout_archive_list_setting() {
		check_ajax_referer( 'cl_all_settings-options', '_wpnonce' );
		$archive_setting = cl_sanitization( $_POST['archive_setting'] );
		update_option( 'cl_archive_setting_list_view', json_encode( $archive_setting ) );
		die();
	}

	public function listing_abuse_dialog_action_func() {
		$return       = array();
		$id           = cl_sanitization( $_POST['listing_abuse_dialog_id'] );
		$data         = sanitize_textarea_field( $_POST['listing_abuse_dialog_text'] );
		$presentabuse = get_post_meta( $id, 'listing_abuse_report_by_visitor', true );
		$email        = get_option( 'admin_email' );
		$subject      = 'Report Abuse By Visitor And Post Id ' . $id;
		if ( is_object( get_post( $id ) ) && $data ) {
			$headers = 'From: ' . $email . "\r\n" . 'Reply-To: ' . $email . "\r\n";
			$sent    = wp_mail( $email, $subject, strip_tags( $data ), $headers );
			if ( $sent ) {
				update_post_meta( $id, 'listing_abuse_report_by_visitor', intval( (int) $presentabuse + 1 ) );
				$return['message'] = esc_html__( 'Your message sent successfully.', 'essential-wp-real-estate' );
				$return['success'] = true;
				$return['class']   = 'success';
			} else {
				$return['message'] = esc_html__( 'Something Wrong.', 'essential-wp-real-estate' );
				$return['success'] = false;
				$return['class']   = 'error';
			}
		} else {
			$return['message'] = esc_html__( 'Please fill out the form.', 'essential-wp-real-estate' );
			$return['class']   = 'error';
		}

		wp_send_json( $return );
		die();
	}


	// -- Single Listing add_to_favorite func
	public function cl_add_to_favorite_func() {
		global $current_user;
		// If the ID is not set, return
		if ( ! isset( $_POST['post_id'] ) || ! isset( $_POST['user_id'] ) ) {
			echo 'error';
			die();
		}
		// Store user and product's ID
		$rpost_id  = cl_sanitization( $_POST['post_id'] );
		$ruser_id  = cl_sanitization( $_POST['user_id'] );
		$user_meta = get_user_meta( $current_user->ID, '_favorite_posts' );

		// Check if the post is favorited or not
		if ( in_array( $rpost_id, $user_meta ) ) {
			delete_user_meta( $current_user->ID, '_favorite_posts', $rpost_id );
			echo 'deleted';
			die();
		} else {
			add_user_meta( $ruser_id, '_favorite_posts', $rpost_id );
			echo 'added';
			die();
		}
		die();
	}

	public function cl_delete_listing_func() {
		$listing_id = cl_sanitization( $_POST['listing_id'] );
		$response   = wp_delete_post( $listing_id, true );
		if ( $response ) {
			echo 'deleted';
		} else {
			echo false;
		}
		die();
	}

	// -- Single Listing add_to_favorite func
	public function cl_compare_func() {
		$comp_data = array();
		if ( isset( $_COOKIE['compare_listing_data'] ) ) {
			$comp_data = explode( ',', cl_sanitization( $_COOKIE['compare_listing_data'] ) );
			$comp_data = array_filter( $comp_data );
		};
		$html = '';
		foreach ( $comp_data as $post ) {
			$html .= '';
			$html .= "<div id=\"wperesds-compare-item{$post}\" class=\"compare-listing-single\"><div class=\"compare-item-img\">";
			if ( has_post_thumbnail( $post ) ) {
				$alt   = get_post_meta( $post, '_wp_attachment_image_alt', true );
				$html .= '<img src="' . esc_url( get_the_post_thumbnail_url( $post, 'thumbnail' ) ) . '" alt="' . esc_attr( $alt ) . '">';
			} else {
				$html .= '<img src="' . WPERESDS_ASSETS . '/img/placeholder_light.png' . '" alt="' . esc_attr__( 'Placeholder', 'essential-wp-real-estate' ) . '">';
			}
			$html .= '</div><div class="compare-item-content"><span class="item-title">';
			$html .= esc_html( get_the_title( $post ) );
			$html .= '</span>';
			$html .= '<a class="wperesds-compare-remove-btn" data-remove_compare_item="' . esc_attr( $post ) . '" href="javascript:void(0)"><i class="fas fa-trash-alt"></i></a>';
			$html .= '</div></div>';
		}
		echo '' . $html;
		die();
	}



	function cl_ajax_add_to_cart() {
		if ( ! isset( $_POST['listing_id'] ) ) {
			die();
		}

		$listing_id        = absint( cl_sanitization( $_POST['listing_id'] ) );
		$request_validated = false;

		if ( isset( $_POST['timestamp'] ) && isset( $_POST['token'] ) && \Essential\Restate\Front\Purchase\Tokenizer\Tokenizer::is_token_valid( $_POST['token'], $_POST['timestamp'] ) ) {
			$request_validated = true;
		} elseif ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'cl-add-to-cart-' . $listing_id ) ) {
			$request_validated = true;
		}

		if ( ! $request_validated ) {
			echo 'Missing nonce when adding an item to the cart.';
			die();
		}

		$to_add = array();

		if ( isset( $_POST['price_ids'] ) && is_array( $_POST['price_ids'] ) ) {
			$price_ids = cl_sanitization( $_POST['price_ids'] );
			foreach ( $price_ids as $price ) {
				$to_add[] = array( 'price_id' => $price );
			}
		}

		$items = '';

		if ( isset( $_POST['post_data'] ) ) {
			parse_str( cl_sanitization( $_POST['post_data'] ), $post_data );
		} else {
			$post_data = array();
		}

		foreach ( $to_add as $options ) {

			if ( $_POST['listing_id'] == $options['price_id'] ) {
				$options = array();
			}

			if ( isset( $options['price_id'] ) && isset( $post_data[ 'cl_listing_quantity_' . $options['price_id'] ] ) ) {

				$options['quantity'] = absint( $post_data[ 'cl_listing_quantity_' . $options['price_id'] ] );
			} else {

				$options['quantity'] = isset( $post_data['cl_listing_quantity'] ) ? absint( $post_data['cl_listing_quantity'] ) : 1;
			}
			$l_id = cl_sanitization( $_POST['listing_id'] );
			$key  = WPERECCP()->front->cart->add( $l_id, $options );

			$item = array(
				'id'      => $l_id,
				'options' => $options,
			);

			$item = apply_filters( 'cl_ajax_pre_cart_item_template', $item );
		}

		$return = array(
			'subtotal'      => html_entity_decode( WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->cart->get_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'         => html_entity_decode( WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->cart->get_total() ) ), ENT_COMPAT, 'UTF-8' ),
			'cart_item'     => $items,
			'cart_quantity' => html_entity_decode( WPERECCP()->front->cart->get_quantity() ),
		);

		if ( WPERECCP()->front->tax->cl_use_taxes() ) {
			$cart_tax      = (float) WPERECCP()->front->cart->get_tax();
			$return['tax'] = html_entity_decode( WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $cart_tax ) ), ENT_COMPAT, 'UTF-8' );
		}

		$return = apply_filters( 'cl_ajax_add_to_cart_response', $return );

		echo json_encode( $return );
		die();
	}


	function cl_process_purchase_form() {
		do_action( 'cl_pre_process_purchase' );

		// Make sure the cart isn't empty
		if ( ! WPERECCP()->front->cart->get_contents() && ! WPERECCP()->front->fees->has_fees() ) {

			$valid_data = false;
			WPERECCP()->front->error->cl_set_error( 'empty_cart', __( 'Your cart is empty...', 'essential-wp-real-estate' ) );
		} else {
			// Validate the form $_POST data
			$valid_data = WPERECCP()->front->gateways->cl_purchase_form_validate_fields();

			// Allow themes and plugins to hook to errors
			do_action( 'cl_checkout_error_checks', $valid_data, cl_sanitization( $_POST ) );
		}

		$is_ajax = isset( $_POST['cl_ajax'] );

		// Process the login form
		if ( isset( $_POST['cl_login_submit'] ) ) {
			WPERECCP()->front->gateways->cl_process_purchase_login();
		}

		// Validate the user
		$user = WPERECCP()->front->gateways->cl_get_purchase_form_user( $valid_data );

		// Let extensions validate fields after user is logged in if user has used login/registration form
		do_action( 'cl_checkout_user_error_checks', $user, $valid_data, cl_sanitization( $_POST ) );

		if ( false === $valid_data || WPERECCP()->front->error->cl_get_errors() || ! $user ) {
			if ( $is_ajax ) {
				do_action( 'cl_ajax_checkout_errors' );
				die();
			} else {
				return false;
			}
		}

		if ( $is_ajax ) {

			echo 'success';
			die();
		}
		// Setup user information
		$user_info = array(
			'id'         => $user['user_id'],
			'email'      => $user['user_email'],
			'first_name' => $user['user_first'],
			'last_name'  => $user['user_last'],
			'discount'   => $valid_data['discount'],
			'address'    => ! empty( $user['address'] ) ? $user['address'] : array(),
		);

		// Update a customer record if they have added/updated information
		$customer = new Customer( $user_info['email'] );

		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
		if ( empty( $customer->name ) || $name != $customer->name ) {
			$update_data = array(
				'name' => $name,
			);

			// Update the customer's name and update the user record too
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
		$address = get_user_meta( $customer->user_id, '_cl_user_address', true );
		if ( ! is_array( $address ) ) {
			$address = array();
		}

		if ( 0 == strlen( implode( $address ) ) || count( array_diff( $address, $user_info['address'] ) ) > 0 ) {
			update_user_meta( $user['user_id'], '_cl_user_address', $user_info['address'] );
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

		// Setup purchase information
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
			'post_data'    => cl_sanitization( $_POST ),
			'cart_details' => WPERECCP()->front->cart->cl_get_cart_content_details(),
			'gateway'      => $valid_data['gateway'],
			'card_info'    => $valid_data['cc_info'],
		);

		// Add the user data for hooks
		$valid_data['user'] = $user;

		// Allow themes and plugins to hook before the gateway
		do_action( 'cl_checkout_before_gateway', cl_sanitization( $_POST ), $user_info, $valid_data );

		// If the total amount in the cart is 0, send to the manual gateway. This emulates a free listing purchase
		if ( ! $purchase_data['price'] ) {
			// Revert to manual
			$purchase_data['gateway'] = 'manual';
			$_POST['cl-gateway']      = 'manual';
		}

		// Allow the purchase data to be modified before it is sent to the gateway
		$purchase_data = apply_filters(
			'cl_purchase_data_before_gateway',
			$purchase_data,
			$valid_data
		);

		// Setup the data we're storing in the purchase session
		$session_data = $purchase_data;

		// Make sure credit card numbers are never stored in sessions
		unset( $session_data['card_info']['card_number'] );

		// Used for showing listing links to non logged-in users after purchase, and for other plugins needing purchase data.
		WPERECCP()->front->cart->cl_set_purchase_session( $session_data );

		// Send info to the gateway for payment processing
		WPERECCP()->front->gateways->cl_send_to_gateway( $purchase_data['gateway'], $purchase_data );

		die();
	}
}
