<?php
namespace Essential\Restate\Front\Loader;

use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Front\Provider\Markups;
use Essential\Restate\Front\Purchase\Gateways\Gateways;

/**
 * Loader class loads everything related templates
 *
 * since 1.0.0
 */
class Shortcode {

	use Traitval;


	/**
	 * init_shortcode
	 *
	 * This function initialize shortcode hooks
	 *
	 * @return void
	 */
	public function init_shortcode() {
		add_shortcode( 'failure_page', array( $this, 'failure_page_func' ) );
		add_shortcode( 'cancel_payment', array( $this, 'cancel_payment_func' ) );
		add_shortcode( 'purchase_history', array( $this, 'purchase_history_func' ) );
		add_shortcode( 'cl_register_user', array( $this, 'cl_register_user_func' ) );
		add_shortcode( 'cl_update_user', array( $this, 'cl_update_user_func' ) );
		add_shortcode( 'cl_admin_login', array( $this, 'cl_admin_login_func' ) );
		// -- Compare listings
		add_shortcode( 'cl_compare_listing', array( $this, 'cl_compare_listing_func' ) );
		// -- Dashboard listings
		add_shortcode( 'cl_dashboard', array( $this, 'cl_dashboard_func' ) );
		add_shortcode( 'cl_add_listing', array( $this, 'cl_add_listing_func' ) );
		add_shortcode( 'cl_edit_listing', array( $this, 'cl_edit_listing_func' ) );
		add_shortcode( 'cl_listing_checkout', array( $this, 'cl_checkout_form_shortcode' ) );
		add_shortcode( 'purchase_link', array( $this, 'cl_listing_shortcode' ) );
		add_shortcode( 'cl_listing_cart', array( $this, 'cl_cart_shortcode' ) );

		add_shortcode( 'cl_receipt', array( $this, 'cl_receipt_shortcode' ) );
	}

	function cl_cart_shortcode( $atts, $content = null ) {
		return WPERECCP()->front->cart->cl_shopping_cart();
	}

	function cl_checkout_form_shortcode( $atts, $content = null ) {
		return WPERECCP()->front->checkout->cl_checkout_form_shortcode();
	}

	function cl_receipt_shortcode( $atts, $content = null ) {
		global $cl_receipt_args;

		$cl_receipt_args = shortcode_atts(
			array(
				'error'          => __( 'Sorry, trouble retrieving payment receipt.', 'essential-wp-real-estate' ),
				'price'          => true,
				'discount'       => true,
				'products'       => true,
				'date'           => true,
				'notes'          => true,
				'payment_key'    => false,
				'payment_method' => true,
				'payment_id'     => true,
			),
			$atts,
			'cl_receipt'
		);

		$session = cl_get_purchase_session();

		if ( isset( $_GET['payment_key'] ) ) {
			$payment_key = urldecode( cl_sanitization( $_GET['payment_key'] ) );
		} elseif ( $session ) {
			$payment_key = $session['purchase_key'];
		} elseif ( $cl_receipt_args['payment_key'] ) {
			$payment_key = $cl_receipt_args['payment_key'];
		}

		// No key found
		if ( ! isset( $payment_key ) ) {
			return '<p class="cl-alert cl-alert-error">' . esc_html( $cl_receipt_args['error'] ) . '</p>';
		}

		$payment_id    = cl_get_purchase_id_by_key( $payment_key );
		$user_can_view = cl_can_view_receipt( $payment_key );

		$cl_receipt_args['id'] = $payment_id;

		// Key was provided, but user is logged out. Offer them the ability to login and view the receipt
		if ( ! $user_can_view && ! empty( $payment_key ) && ! is_user_logged_in() && ! cl_is_guest_payment( $payment_id ) ) {
			global $cl_login_redirect;
			$cl_login_redirect = cl_get_current_page_url();

			ob_start();

			echo '<p class="cl-alert cl-alert-warn">' . __( 'You must be logged in to view this payment receipt.', 'essential-wp-real-estate' ) . '</p>';

			cl_get_template( 'login.php' );
			$login_form = ob_get_clean();

			return $login_form;
		}

		$user_can_view = apply_filters( 'cl_user_can_view_receipt', $user_can_view, $cl_receipt_args );

		// If this was a guest checkout and the purchase session is empty, output a relevant error message
		if ( empty( $session ) && ! is_user_logged_in() && ! $user_can_view ) {
			return '<p class="cl-alert cl-alert-error">' . apply_filters( 'cl_receipt_guest_error_message', __( 'Receipt could not be retrieved, your purchase session has expired.', 'essential-wp-real-estate' ) ) . '</p>';
		}

		/*
		* Check if the user has permission to view the receipt
		*
		* If user is logged in, user ID is compared to user ID of ID stored in payment meta
		*
		* Or if user is logged out and purchase was made as a guest, the purchase session is checked for
		*
		* Or if user is logged in and the user can view sensitive shop data
		*
		*/

		if ( ! $user_can_view ) {
			return '<p class="cl-alert cl-alert-error">' . esc_html( $cl_receipt_args['error'] ) . '</p>';
		}

		ob_start();

		cl_get_template( 'receipt/receipt.php', array( 'cl_receipt_args' => $cl_receipt_args ) );

		$display = ob_get_clean();

		return $display;
	}

	function cl_listing_shortcode( $atts, $content = null ) {
		global $post;

		$post_id = is_object( $post ) ? $post->ID : 0;

		$atts = shortcode_atts(
			array(
				'id'       => $post_id,
				'price_id' => isset( $atts['price_id'] ) ? $atts['price_id'] : false,
				'sku'      => '',
				'price'    => '1',
				'direct'   => '0',
				'text'     => '',
				'style'    => cl_admin_get_option( 'button_style', 'button' ),
				'color'    => cl_admin_get_option( 'checkout_color', 'blue' ),
				'class'    => 'cl-submit',
				'form_id'  => '',
			),
			$atts,
			'purchase_link'
		);

		// Override text only if not provided / empty
		if ( ! $atts['text'] ) {
			if ( $atts['direct'] == '1' || $atts['direct'] == 'true' ) {
				$atts['text'] = cl_admin_get_option( 'buy_now_text', __( 'Buy Now', 'essential-wp-real-estate' ) );
			} else {
				$atts['text'] = cl_admin_get_option( 'add_to_cart_text', __( 'Purchase', 'essential-wp-real-estate' ) );
			}
		}

		// Override color if color == inherit
		if ( isset( $atts['color'] ) ) {
			$atts['color'] = ( $atts['color'] == 'inherit' ) ? '' : $atts['color'];
		}

		if ( ! empty( $atts['sku'] ) ) {

			$listing = cl_get_listing_by( 'sku', $atts['sku'] );

			if ( $listing ) {
				$atts['listing_id'] = $listing->ID;
			}
		} elseif ( isset( $atts['id'] ) ) {

			// cl_get_purchase_link() expects the ID to be listing_id since v1.3
			$atts['listing_id'] = $atts['id'];

			$listing = cl_get_listing( $atts['listing_id'] );
		}

		if ( $listing ) {
			return cl_get_purchase_link( $atts );
		}
	}

	/**
	 * listing_checkout_func
	 *
	 * @return void
	 */
	public function listing_checkout_func( $args = array() ) {
		return 'This is listing_checkout_func';
	}

	/**
	 * failure_page_func
	 *
	 * @return void
	 */
	public function failure_page_func( $args = array() ) {
		return '<p>' . esc_html__( 'Your transaction failed, please try again or contact site support.', 'essential-wp-real-estate' ) . '</p>';
	}

	/**
	 * cancel_payment_func
	 *
	 * @return void
	 */
	public function cancel_payment_func( $args = array() ) {
		return '<p>' . esc_html__( 'Your transaction has been successfully cancelled.', 'essential-wp-real-estate' ) . '</p>';
	}

	/**
	 * purchase_history_func
	 *
	 * @return void
	 */
	public function purchase_history_func( $args = array() ) {
		ob_start();

		if ( ! WPERECCP()->common->user->cl_user_pending_verification() ) {
			cl_get_template( 'receipt/history.php' );
		} else {
			cl_get_template( 'receipt/account-pending.php' );
		}

		return ob_get_clean();
	}

	/**
	 * cl_register_user_func
	 *
	 * @return void
	 */
	public function cl_register_user_func( $args = array() ) {
		$output = Markups::getInstance()->cl_register_user_html( $args );
		return;
	}

	/**
	 * cl_update_user_func
	 *
	 * @return void
	 */
	public function cl_update_user_func( $args = array() ) {
		$output = Markups::getInstance()->cl_update_user_html( $args );
		return;
	}

	/**
	 * cl_admin_login_func
	 *
	 * @return void
	 */
	public function cl_admin_login_func( $args = array() ) {
		$output = Markups::getInstance()->cl_admin_login_html( $args );
		return;
	}

	/**
	 * cl_compare_listing_func
	 *
	 * @return void
	 */
	public function cl_compare_listing_func( $args = array() ) {
		$output = Markups::getInstance()->cl_compare_listing_html();
		return;
	}

	/**
	 * cl_dashboard_func
	 *
	 * @return void
	 */
	public function cl_dashboard_func( $args = array() ) {
		$output = Markups::getInstance()->cl_dashboard_listing_html();
		return;
	}

	/**
	 * cl_add_listing_func
	 *
	 * @return void
	 */
	public function cl_add_listing_func( $args = array() ) {
		$output = Markups::getInstance()->cl_add_listing_html();
		return;
	}

	/**
	 * cl_edit_listing_func
	 *
	 * @return void
	 */
	public function cl_edit_listing_func( $args = array() ) {
		$output = Markups::getInstance()->cl_edit_listing_html();
		return;
	}
}
