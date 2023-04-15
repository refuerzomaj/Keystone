<?php
namespace  Essential\Restate\Front\Purchase\Cart;

use Essential\Restate\Traitval\Traitval;

class Cartactions {

	use Traitval;
	public function __construct() {
		add_action( 'init', array( $this, 'cl_add_rewrite_endpoints' ) );
		add_action( 'template_redirect', array( $this, 'cl_process_cart_endpoints' ), 100 );
		add_action( 'cl_add_to_cart', array( $this, 'cl_process_add_to_cart' ) );
		add_action( 'cl_remove', array( $this, 'cl_process_remove_from_cart' ) );
		add_action( 'cl_purchase_collection', array( $this, 'cl_process_collection_purchase' ) );
		add_action( 'cl_update_cart', array( $this, 'cl_process_cart_update' ) );
		add_action( 'cl_remove_fee', array( $this, 'cl_process_remove_fee_from_cart' ) );
		add_action( 'cl_save_cart', array( $this, 'cl_process_cart_save' ) );
		add_action( 'cl_restore_cart', array( $this, 'cl_process_cart_restore' ) );
		add_action( 'cl_cart_empty', array( $this, 'cl_empty_checkout_cart' ) );
	}

	public function cl_add_rewrite_endpoints( $rewrite_rules ) {
		add_rewrite_endpoint( 'cl-add', EP_ALL );
		add_rewrite_endpoint( 'cl-remove-item', EP_ALL );
	}


	/**
	 * Process Cart Endpoints
	 *
	 * Listens for add/remove requests sent from the cart
	 *
	 * @since 1.3.4
	 * @global $wp_query Used to access the current query that is being requested
	 * @return void
	 */
	public function cl_process_cart_endpoints() {
		global $wp_query;

		// Adds an item to the cart with a /cl-add/# URL
		if ( isset( $wp_query->query_vars['cl-add'] ) ) {
			$listing_id = absint( $wp_query->query_vars['cl-add'] );
			$cart       = self::cl_add_to_cart( $listing_id, array() );

			wp_redirect( cl_get_checkout_uri() );
			wp_die();
		}

		// Removes an item from the cart with a /cl-remove/# URL
		if ( isset( $wp_query->query_vars['cl-remove-item'] ) ) {
			$cart_key = absint( $wp_query->query_vars['cl-remove-item'] );
			$cart     = cl_remove_from_cart( $cart_key );

			wp_redirect( cl_get_checkout_uri() );
			wp_die();
		}
	}


	/**
	 * Process the Add to Cart request
	 *
	 * @since 1.0
	 *
	 * @param $data
	 */
	public function cl_process_add_to_cart( $data ) {
		$listing_id = ! empty( $data['listing_id'] ) ? absint( $data['listing_id'] ) : false;
		$options    = isset( $data['cl_options'] ) ? $data['cl_options'] : array();

		if ( ! empty( $data['cl_listing_quantity'] ) ) {
			$options['quantity'] = absint( $data['cl_listing_quantity'] );
		}

		if ( isset( $options['price_id'] ) && is_array( $options['price_id'] ) ) {
			foreach ( $options['price_id'] as  $key => $price_id ) {
				$options['quantity'][ $key ] = isset( $data[ 'cl_listing_quantity_' . $price_id ] ) ? absint( $data[ 'cl_listing_quantity_' . $price_id ] ) : 1;
			}
		}

		if ( ! empty( $listing_id ) ) {
			self::cl_add_to_cart( $listing_id, $options );
		}

		if ( WPERECCP()->common->options->cl_straight_to_checkout() && ! cl_is_checkout() ) {
			$query_args     = remove_query_arg( array( 'cl_action', 'listing_id', 'cl_options' ) );
			$query_part     = strpos( $query_args, '?' );
			$url_parameters = '';

			if ( false !== $query_part ) {
				$url_parameters = substr( $query_args, $query_part );
			}

			wp_redirect( cl_get_checkout_uri() . $url_parameters, 303 );
			wp_die();
		} else {
			wp_redirect( remove_query_arg( array( 'cl_action', 'listing_id', 'cl_options' ) ) );
			wp_die();
		}
	}


	/**
	 * Process the Remove from Cart request
	 *
	 * @since 1.0
	 *
	 * @param $data
	 */
	public function cl_process_remove_from_cart( $data ) {
		$cart_key = absint( $_GET['cart_item'] );
		if ( ! isset( $_GET['cl_remove_from_cart_nonce'] ) ) {
			WPERECCP()->front->error->cl_set_error( 'remove_from_cart', sprintf( __( 'Security key not found', 'essential-wp-real-estate' ) ) );
			WPERECCP()->front->error->cl_print_errors();
		}
		$nonce          = ! empty( $_GET['cl_remove_from_cart_nonce'] ) ? cl_sanitization( $_GET['cl_remove_from_cart_nonce'] ) : '';
		$nonce_verified = wp_verify_nonce( $nonce, 'cl-remove-cart-item-' . $cart_key );
		if ( false !== $nonce_verified ) {
			WPERECCP()->front->cart->remove( $cart_key );
		} else {
			WPERECCP()->front->error->cl_set_error( 'remove_from_cart_error', sprintf( __( 'Security key not verified', 'essential-wp-real-estate' ) ) );
			WPERECCP()->front->error->cl_print_errors();
		}
		wp_redirect( remove_query_arg( array( 'cl_action', 'cart_item', 'nocache', 'cl_remove_from_cart_nonce' ) ) );
		die();
	}


	/**
	 * Process the Remove fee from Cart request
	 *
	 * @since 2.0
	 *
	 * @param $data
	 */
	public function cl_process_remove_fee_from_cart( $data ) {
		$fee = cl_sanitization( $data['fee'] );
		WPERECCP()->front->fees->remove_fee( $fee );
		wp_redirect( remove_query_arg( array( 'cl_action', 'fee', 'nocache' ) ) );
		wp_die();
	}


	/**
	 * Process the Collection Purchase request
	 *
	 * @since 1.0
	 *
	 * @param $data
	 */
	public function cl_process_collection_purchase( $data ) {
		$taxonomy   = urldecode( $data['taxonomy'] );
		$terms      = urldecode( $data['terms'] );
		$cart_items = cl_add_collection_to_cart( $taxonomy, $terms );
		wp_redirect( add_query_arg( 'added', '1', remove_query_arg( array( 'cl_action', 'taxonomy', 'terms' ) ) ) );
		wp_die();
	}



	/**
	 * Process cart updates, primarily for quantities
	 *
	 * @since 1.7
	 */
	public function cl_process_cart_update( $data ) {
		if ( ! empty( $data['cl-cart-listing'] ) && is_array( $data['cl-cart-listing'] ) ) {
			foreach ( $data['cl-cart-listing'] as $key => $cart_listing_id ) {
				$options  = json_decode( stripslashes( $data[ 'cl-cart-listing-' . $key . '-options' ] ), true );
				$quantity = absint( $data[ 'cl-cart-listing-' . $key . '-quantity' ] );
				cl_set_cart_item_quantity( $cart_listing_id, $quantity, $options );
			}
		}
	}


	/**
	 * Process cart save
	 *
	 * @since 1.8
	 * @return void
	 */
	public function cl_process_cart_save( $data ) {
		$cart = cl_save_cart();
		if ( ! $cart ) {
			wp_redirect( cl_get_checkout_uri() );
			exit;
		}
	}


	/**
	 * Process cart save
	 *
	 * @since 1.8
	 * @return void
	 */
	public function cl_process_cart_restore( $data ) {
		$cart = cl_restore_cart();
		if ( ! is_wp_error( $cart ) ) {
			wp_redirect( cl_get_checkout_uri() );
			exit;
		}
	}

	/**
	 * Add cart Button
	 *
	 * @since 1.0
	 * @return void
	 */

	public function append_cart_button( $args ) {
		global $post, $cl_displayed_form_ids;

		$purchase_page = cl_admin_get_option( 'purchase_page', false );

		if ( ! $purchase_page || $purchase_page == 0 ) {
			WPERECCP()->front->error->cl_set_error( 'set_checkout', sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'essential-wp-real-estate' ), admin_url( 'edit.php?post_type=cl_cpt&page=listing_settings_func' ) ) );
			WPERECCP()->front->error->cl_print_errors();
			return false;
		}

		$post_id = is_object( $post ) ? $post->ID : 0;
		$listing = WPERECCP()->front->listing_provider->listing;
		if ( isset( $args['post_id'] ) ) {
			$post_id = $args['post_id'];
			$listing = WPERECCP()->front->listing_provider->get_details( $post_id );
		}

		$listing = apply_filters( 'cl_append_cart_button_item', $listing );

		$defaults = apply_filters(
			'cl_append_cart_button',
			array(
				'listing_id' => $post_id,
				'price'      => (bool) true,
				'price_id'   => isset( $args['price_id'] ) ? $args['price_id'] : false,
				'direct'     => ( isset( $listing->button_behavior ) && $listing->button_behavior == 'direct' ) ? true : false,
				'text'       => ( isset( $listing->button_behavior ) && $listing->button_behavior == 'direct' ) ? cl_admin_get_option( 'buy_now_text', __( 'Buy Now', 'essential-wp-real-estate' ) ) : cl_admin_get_option( 'add_to_cart_text', __( 'Purchase', 'essential-wp-real-estate' ) ),
				'checkout'   => cl_admin_get_option( 'checkout_button_text', _x( 'Checkout', 'text shown on the Add to Cart Button when the product is already in the cart', 'essential-wp-real-estate' ) ),
				'style'      => cl_admin_get_option( 'button_style', 'button' ),
				'color'      => cl_admin_get_option( 'checkout_color', 'blue' ),
				'class'      => 'cl-submit',
			)
		);

		$args = wp_parse_args( $args, $defaults );
		// Override the straight_to_gateway if the shop doesn't support it
		if ( ! WPERECCP()->front->gateways->cl_shop_supports_buy_now() ) {
			$args['direct'] = false;
		}
		if ( empty( $listing->ID ) ) {
			return false;
		}
		if ( 'publish' !== $listing->post_status && ! current_user_can( 'edit_product', $listing->ID ) ) {
			return false; // Product not published or user doesn't have permission to view drafts
		}
		// Override color if color == inherit
		$args['color'] = ( $args['color'] == 'inherit' ) ? '' : $args['color'];

		$options          = array();
		$variable_pricing = $listing->has_variable_prices;
		$data_variable    = $variable_pricing ? ' data-variable-price="yes"' : 'data-variable-price="no"';
		$type             = $listing->is_single_price_mode ? 'data-price-mode=multi' : 'data-price-mode=single';

		$show_price       = $args['price'] && $args['price'] !== 'no';
		$data_price_value = 0;
		$price            = false;

		if ( $variable_pricing && false !== $args['price_id'] ) {

			$price_id            = $args['price_id'];
			$prices              = $listing->prices;
			$options['price_id'] = $args['price_id'];
			$found_price         = isset( $prices[ $price_id ] ) ? $prices[ $price_id ]['amount'] : false;

			$data_price_value = $found_price;

			if ( $show_price ) {
				$price = $found_price;
			}
		} elseif ( ! $variable_pricing ) {

			$data_price_value = $listing->price;

			if ( $show_price ) {
				$price = $listing->price;
			}
		}

		$data_price = 'data-price="' . esc_attr( $data_price_value ) . '"';

		$button_text = ! empty( $args['text'] ) ? '&nbsp;&ndash;&nbsp;' . $args['text'] : '';

		if ( false !== $price ) {

			if ( 0 == $price ) {
				$args['text'] = '<span>' . __( 'Free', 'essential-wp-real-estate' ) . '</span>' . $button_text;
			} else {
				$args['text'] = '<span>' . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $price ) ) . '</span>' . $button_text;
				$args['text'] = apply_filters( 'cl_purchase_listing_cart_button_text', $args['text'] );
			}
		}

		if ( WPERECCP()->front->cart->is_item_in_cart( $listing->ID, $options ) && ( ! $variable_pricing || ! $listing->is_single_price_mode ) ) {

			$button_display   = 'style="display:none;"';
			$checkout_display = '';
		} else {
			$button_display   = '';
			$checkout_display = 'style="display:none;"';
		}

		// Collect any form IDs we've displayed already so we can avoid duplicate IDs
		if ( isset( $cl_displayed_form_ids[ $listing->ID ] ) ) {
			$cl_displayed_form_ids[ $listing->ID ]++;
		} else {
			$cl_displayed_form_ids[ $listing->ID ] = 1;
		}

		$form_id = ! empty( $args['form_id'] ) ? $args['form_id'] : 'cl_purchase_' . $listing->ID;

		// If we've already generated a form ID for this listing ID, append -#
		if ( $cl_displayed_form_ids[ $listing->ID ] > 1 ) {
			$form_id .= '-' . $cl_displayed_form_ids[ $listing->ID ];
		}

		$args                     = apply_filters( 'cl_purchase_link_args', $args );
		$args['listing']          = $listing;
		$args['button_display']   = $button_display;
		$args['checkout_display'] = $checkout_display;
		$args['data_variable']    = $data_variable;
		$args['data_price']       = $data_price;
		$args['type']             = $type;
		$args['variable_pricing'] = $variable_pricing;
		$args['form_id']          = $form_id;

		$purchase_form = cl_get_template( 'cart/cart-button.php', $args );

		return apply_filters( 'cl_purchase_listing_form', $purchase_form, $args );
	}

	public static function cl_add_to_cart( $listing_id, $options = array() ) {

		return WPERECCP()->front->cart->add( $listing_id, $options );
	}

	public function cl_get_cart_item_template_func( $cart_key, $item, $ajax = false ) {
		global $post;

		$id = is_array( $item ) ? $item['id'] : $item;

		$listing    = WPERECCP()->front->listing_provider->get_details( $id );
		$remove_url = WPERECCP()->front->cart->remove_item_url( $cart_key );
		$title      = $listing->title;
		$options    = ! empty( $item['options'] ) ? $item['options'] : array();
		$quantity   = WPERECCP()->front->cart->get_item_quantity( $id, $options );
		$price      = WPERECCP()->front->cart->get_item_price( $id, $options );

		if ( ! empty( $options ) ) {
			$title .= ( WPERECCP()->front->listing_provider->cl_has_variable_prices( $item['id'] ) ) ? ' <span class="cl-cart-item-separator">-</span> ' . WPERECCP()->front->listing_provider->cl_get_price_name( $id, $item['options'] ) : WPERECCP()->front->listing_provider->cl_get_price_name( $id, $item['options'] );
		}
		ob_start();
		cl_get_template( 'cart/cart-item.php' );
		$item     = ob_get_clean();
		$item     = str_replace( '{item_title}', $title, $item );
		$item     = str_replace( '{item_amount}', WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $price ) ), $item );
		$item     = str_replace( '{cart_item_id}', absint( $cart_key ), $item );
		$item     = str_replace( '{item_id}', absint( $id ), $item );
		$item     = str_replace( '{item_quantity}', absint( $quantity ), $item );
		$item     = str_replace( '{remove_url}', $remove_url, $item );
		$subtotal = '';
		if ( $ajax ) {
			$subtotal = WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( WPERECCP()->front->cart->get_subtotal() ) );
		}
		$item = str_replace( '{subtotal}', $subtotal, $item );
		return apply_filters( 'cl_cart_item', $item, $id );
	}

	function cl_shopping_cart( $echo = false ) {
		ob_start();

		do_action( 'cl_before_cart' );

		cl_get_template_with_dir( 'cart.php', 'cart' );

		do_action( 'cl_after_cart' );

		if ( $echo ) {
			echo ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}

	function cl_empty_cart_message() {
		return apply_filters( 'cl_empty_cart_message', '<span class="cl_empty_cart">' . __( 'Your cart is empty.', 'essential-wp-real-estate' ) . '</span>' );
	}

	function cl_empty_checkout_cart() {
		 echo WPERECCP()->front->cart->cl_empty_cart_message();
	}
}
