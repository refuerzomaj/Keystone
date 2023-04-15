<?php
namespace Essential\Restate\Front\Models;

use Essential\Restate\Traitval\Traitval;
use Essential\Restate\Front\Models\Datacore;
use Essential\Restate\Front\Provider\Query;

/**
 * Listings model class for listings
 *
 * since 1.0.0
 */
class Listings extends Datacore {

	use Traitval;

	public $ID = 0;
	private $prices;
	private $type;
	private $sales;
	private $earnings;
	public $notes;
	public $sku;
	private $img_url;
	private $meta_pref       = 'cl_mb_';
	private $meta_data       = array();
	private $button_behavior = 'false';
	public $price;
	// public $post_type             = 'cl_cpt',;
	public $post_author           = 0;
	public $post_date             = '0000-00-00 00:00:00';
	public $post_date_gmt         = '0000-00-00 00:00:00';
	public $post_content          = '';
	public $post_title            = '';
	public $post_excerpt          = '';
	public $post_status           = 'publish';
	public $comment_status        = 'open';
	public $ping_status           = 'open';
	public $post_password         = '';
	public $post_name             = '';
	public $to_ping               = '';
	public $pinged                = '';
	public $post_modified         = '0000-00-00 00:00:00';
	public $post_modified_gmt     = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent           = 0;
	public $guid                  = '';
	public $menu_order            = 0;
	public $post_mime_type        = '';
	public $comment_count         = 0;
	public $filter;

	/**
	 * __construct private function to create the whole listing object from id.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */




	/**
	 * Given the listing data, let's set the variables
	 *
	 * @since  2.3.6
	 * @param  WP_Post $listing The WP_Post object for listing.
	 * @return bool             If the setup was successful or not
	 */

	public function __construct( $_id = '' ) {

		$listing = \WP_Post::get_instance( $_id );
		parent::__construct();
		$this->setup_listing( $listing );
		if ( isset( $this->ID ) && $this->ID ) {
			$this->img_url = $this->get_thumb_url();
		}
	}

	private function setup_listing( $listing ) {

		if ( ! is_object( $listing ) ) {
			return false;
		}

		if ( ! is_a( $listing, 'WP_Post' ) ) {
			return false;
		}

		// if ( 'cl_cpt' !== $listing->post_type || 'pricing_plan' !== $listing->post_type ) {
		// return false;
		// }
		foreach ( $listing as $key => $value ) {

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;
			}
		}

		return true;
	}

	/**
	 * set_id set's the id of the current listing.
	 *
	 * @return void
	 *
	 * since 1.0.0
	 */
	public function set_id( $id = '' ) {
		$this->ID = get_the_ID();
		if ( $this->ID == '' ) {
			$this->ID = $id;
		}
		$this->__construct();
	}


	public function get_id( $id = '' ) {
		return get_the_ID();
	}

	/**
	 * show_thumb data and show image
	 *
	 * @param  mixed $args
	 * @return void
	 */
	protected function output_thumb( $args ) {
		extract( $args );
		$img_alt = apply_filters( $this->prefix . 'archive_thumbnail_alt', $this->title );
		if ( isset( $this->listing->ID ) && $this->listing->ID ) {

			$img_url = $this->get_thumb_url( $img_size );
			if ( isset( $img_url ) && $img_url ) {
				if ( $img_class != '' ) {
					return sprintf( '<img class="%1s" id="%2s" src="%3s" alt="%4s">', esc_attr( $img_class ), esc_attr( $this->prefix . 'thumb_id_' . $this->listing->ID ), esc_url( $img_url ), esc_attr( $img_alt ) );
				} else {
					return sprintf( '<img id="%1s" src="%2s" alt="%3s">', esc_attr( $this->prefix . 'thumb_id_' . $this->listing->ID ), esc_url( $img_url ), esc_attr( $img_alt ) );
				}
			}
			return false;
		}
	}

	public function get_name() {
		return get_the_title( $this->ID );
	}


	/**
	 * get_thumb_url gets the image url of full size if not given a size.
	 *
	 * @param  mixed $size
	 * @return void
	 */
	public function get_thumb_url( $size = '' ) {

		if ( $size == '' ) {
			if ( $this->img_url ) {
				return $this->img_url;
			} else {
				return get_the_post_thumbnail_url( $this->ID );
			}
		}
		return get_the_post_thumbnail_url( $this->ID, $size );
	}

	public function can_purchase( $id = '' ) {
		$can_purchase = true;
		if ( $id == '' ) {
			$this->ID = $id;
		}

		if ( ! current_user_can( 'edit_post', $this->ID ) && $this->post_status != 'publish' ) {
			$can_purchase = false;
		}

		return (bool) apply_filters( 'cl_can_purchase_listing', $can_purchase, $this );
	}


	public function get_details( $id = '' ) {

		$obj     = new \stdClass();
		$obj->ID = $this->ID;
		if ( $obj->ID == '' || $obj->ID == 0 ) {
			$obj->ID = $id;
		}
		$obj->url                  = $this->get_url( $obj->ID );
		$obj->title                = get_the_title( $obj->ID );
		$obj->content              = $this->content;
		$obj->excerpt              = $this->excerpt;
		$obj->button_behavior      = $this->button_behavior;
		$this->post_status         = $obj->post_status = 'publish';
		$obj->price                = $this->get_price( $obj->ID );
		$obj->is_single_price_mode = $this->is_single_price_mode();
		$obj->has_variable_prices  = $this->has_variable_prices();
		$obj->is_free              = $this->is_free();
		$obj->can_purchase         = $this->can_purchase();

		return $obj;
	}
	public function has_variable_prices() {
		 $ret = get_post_meta( $this->ID, '_variable_pricing', true );
		return (bool) apply_filters( 'cl_has_variable_prices', $ret, $this->ID );
	}

	public function cl_has_variable_prices( $ID ) {
		$ret = get_post_meta( $ID, '_variable_pricing', true );
		return (bool) apply_filters( 'cl_has_variable_prices', $ret, $ID );
	}

	function cl_get_price_name( $listing_id = 0, $options = array() ) {
		$return = false;
		if ( $this->cl_has_variable_prices( $listing_id ) && ! empty( $options ) ) {
			$prices = $this->cl_get_variable_prices( $listing_id );
			$name   = false;
			if ( $prices ) {
				if ( isset( $prices[ $options['price_id'] ] ) ) {
					$name = $prices[ $options['price_id'] ]['name'];
				}
			}
			$return = $name;
		}
		return apply_filters( 'cl_get_price_name', $return, $listing_id, $options );
	}

	function cl_get_variable_prices( $listing_id = 0 ) {

		if ( empty( $listing_id ) ) {
			return false;
		}

		return $this->get_prices();
	}


	public function is_single_price_mode() {
		$ret = $this->has_variable_prices() && get_post_meta( $this->ID, 'cl__price_options_mode', true );
		return (bool) apply_filters( 'cl_single_price_option_mode', $ret, $this->ID );
	}
	public function is_free( $price_id = false ) {

		$is_free          = false;
		$variable_pricing = $this->has_variable_prices( $this->ID );

		if ( $variable_pricing && ! is_null( $price_id ) && $price_id !== false ) {

			$price = cl_get_price_option_amount( $this->ID, $price_id );
		} elseif ( $variable_pricing && $price_id === false ) {

			$lowest_price  = (float) $this->cl_get_lowest_price_option( $this->ID );
			$highest_price = (float) $this->cl_get_highest_price_option( $this->ID );

			if ( $lowest_price === 0.00 && $highest_price === 0.00 ) {
				$price = 0;
			}
		} elseif ( ! $variable_pricing ) {

			$price = get_post_meta( $this->ID, 'cl_price', true );
		}

		if ( isset( $price ) && (float) $price == 0 ) {
			$is_free = true;
		}

		return (bool) apply_filters( 'cl_is_free_listing', $is_free, $this->ID, $price_id );
	}
	function cl_get_lowest_price_option( $listing_id = 0 ) {
		if ( empty( $listing_id ) ) {
			$listing_id = get_the_ID();
		}

		if ( ! $this->has_variable_prices( $this->ID ) ) {
			return $this->get_price( $listing_id );
		}

		$prices = $this->get_prices( $listing_id );

		$low = 0.00;

		if ( ! empty( $prices ) ) {

			foreach ( $prices as $key => $price ) {

				if ( empty( $price['amount'] ) ) {
					continue;
				}

				if ( ! isset( $min ) ) {
					$min = $price['amount'];
				} else {
					$min = min( $min, $price['amount'] );
				}

				if ( $price['amount'] == $min ) {
					$min_id = $key;
				}
			}

			$low = $prices[ $min_id ]['amount'];
		}

		return WPERECCP()->common->formatting->cl_sanitize_amount( $low );
	}
	function cl_get_highest_price_option( $listing_id = 0 ) {

		if ( empty( $listing_id ) ) {
			$listing_id = get_the_ID();
		}

		if ( ! $this->cl_has_variable_prices( $listing_id ) ) {
			return $this->get_price( $listing_id );
		}

		$prices = $this->get_prices( $listing_id );

		$high = 0.00;

		if ( ! empty( $prices ) ) {

			$max = 0;

			foreach ( $prices as $key => $price ) {

				if ( empty( $price['amount'] ) ) {
					continue;
				}

				$max = max( $max, $price['amount'] );

				if ( $price['amount'] == $max ) {
					$max_id = $key;
				}
			}

			$high = $prices[ $max_id ]['amount'];
		}

		return WPERECCP()->common->formatting->cl_sanitize_amount( $high );
	}

	function cl_get_price_option_amount( $listing_id = 0, $price_id = 0 ) {
		$prices = get_prices( $listing_id );
		$amount = 0.00;

		if ( $prices && is_array( $prices ) ) {
			if ( isset( $prices[ $price_id ] ) ) {
				$amount = $prices[ $price_id ]['amount'];
			}
		}

		return apply_filters( 'cl_get_price_option_amount', WPERECCP()->common->formatting->cl_sanitize_amount( $amount ), $listing_id, $price_id );
	}


	public function get_prices( $listing_id = 0 ) {

		if ( empty( $listing_id ) || $listing_id == 0 ) {
			$listing_id = get_the_ID();
		}

		$this->prices = array();

		if ( true === $this->has_variable_prices() ) {

			if ( empty( $this->prices ) ) {
				$this->prices = get_post_meta( $listing_id, 'wperesds_pricing', true );
			}
		}
		return apply_filters( 'get_prices', $this->prices, $listing_id );
	}

	public function get_price( $listing_id = 0 ) {
		if ( empty( $listing_id ) || $listing_id == 0 ) {
			$listing_id = get_the_ID();
		}
		if ( ! empty( $listing_id ) ) {

			$this->price = get_post_meta( $listing_id, 'wperesds_pricing', true );

			if ( $this->price ) {

				$this->price = WPERECCP()->common->formatting->cl_sanitize_amount( $this->price );
			} else {

				$this->price = 0;
			}
		}
		return apply_filters( 'cl_get_listing_price', $this->price, $listing_id );
	}



	public function get_notes() {
		if ( ! isset( $this->notes ) ) {

			$this->notes = get_post_meta( $this->ID, 'cl_product_notes', true );
		}

		return (string) apply_filters( 'cl_product_notes', $this->notes, $this->ID );
	}

	/**
	 * Retrieve the listing sku
	 *
	 * @since 2.2
	 * @return string SKU of the listing
	 */
	public function get_sku() {
		if ( ! isset( $this->sku ) ) {

			$this->sku = get_post_meta( $this->ID, 'cl_sku', true );

			if ( empty( $this->sku ) ) {
				$this->sku = '-';
			}
		}

		return apply_filters( 'cl_get_listing_sku', $this->sku, $this->ID );
	}

	/**
	 * Retrieve the purchase button behavior
	 *
	 * @since 2.2
	 * @return string
	 */
	public function get_button_behavior() {
		if ( ! isset( $this->button_behavior ) ) {

			$this->button_behavior = get_post_meta( $this->ID, '_cl_button_behavior', true );

			if ( empty( $this->button_behavior ) || ! cl_shop_supports_buy_now() ) {

				$this->button_behavior = 'add_to_cart';
			}
		}

		return apply_filters( 'cl_get_listing_button_behavior', $this->button_behavior, $this->ID );
	}

	/**
	 * Retrieve the sale count for the listing
	 *
	 * @since 2.2
	 * @return int Number of times this has been purchased
	 */
	public function get_sales() {
		if ( ! isset( $this->sales ) ) {

			if ( '' == get_post_meta( $this->ID, '_cl_listing_sales', true ) ) {
				add_post_meta( $this->ID, '_cl_listing_sales', 0 );
			}

			$this->sales = get_post_meta( $this->ID, '_cl_listing_sales', true );

			// Never let sales be less than zero
			$this->sales = max( $this->sales, 0 );
		}

		return $this->sales;
	}

	/**
	 * Increment the sale count by one
	 *
	 * @since 2.2
	 * @param int $quantity The quantity to increase the sales by
	 * @return int New number of total sales
	 */
	public function increase_sales( $quantity = 1 ) {

		$quantity    = absint( $quantity );
		$total_sales = $this->get_sales() + $quantity;

		if ( $this->update_meta( '_cl_listing_sales', $total_sales ) ) {

			$this->sales = $total_sales;

			do_action( 'cl_listing_increase_sales', $this->ID, $this->sales, $this );

			return $this->sales;
		}

		return false;
	}

	/**
	 * Decrement the sale count by one
	 *
	 * @since 2.2
	 * @param int $quantity The quantity to decrease by
	 * @return int New number of total sales
	 */
	public function decrease_sales( $quantity = 1 ) {

		// Only decrease if not already zero
		if ( $this->get_sales() > 0 ) {

			$quantity    = absint( $quantity );
			$total_sales = $this->get_sales() - $quantity;

			if ( $this->update_meta( '_cl_listing_sales', $total_sales ) ) {

				$this->sales = $total_sales;

				do_action( 'cl_listing_decrease_sales', $this->ID, $this->sales, $this );

				return $this->sales;
			}
		}

		return false;
	}

	/**
	 * Retrieve the total earnings for the listing
	 *
	 * @since 2.2
	 * @return float Total listing earnings
	 */
	public function get_earnings() {
		if ( ! isset( $this->earnings ) ) {

			if ( '' == get_post_meta( $this->ID, '_cl_listing_earnings', true ) ) {
				add_post_meta( $this->ID, '_cl_listing_earnings', 0 );
			}

			$this->earnings = get_post_meta( $this->ID, '_cl_listing_earnings', true );

			// Never let earnings be less than zero
			$this->earnings = max( $this->earnings, 0 );
		}

		return $this->earnings;
	}

	/**
	 * Increase the earnings by the given amount
	 *
	 * @since 2.2
	 * @param int|float $amount Amount to increase the earnings by
	 * @return float New number of total earnings
	 */
	public function increase_earnings( $amount = 0 ) {

		$current_earnings = $this->get_earnings();
		$new_amount       = apply_filters( 'cl_listing_increase_earnings_amount', $current_earnings + (float) $amount, $current_earnings, $amount, $this );

		if ( $this->update_meta( '_cl_listing_earnings', $new_amount ) ) {

			$this->earnings = $new_amount;

			do_action( 'cl_listing_increase_earnings', $this->ID, $this->earnings, $this );

			return $this->earnings;
		}

		return false;
	}

	/**
	 * Decrease the earnings by the given amount
	 *
	 * @since 2.2
	 * @param int|float $amount Number to decrease earning with
	 * @return float New number of total earnings
	 */
	public function decrease_earnings( $amount ) {

		// Only decrease if greater than zero
		if ( $this->get_earnings() > 0 ) {

			$current_earnings = $this->get_earnings();
			$new_amount       = apply_filters( 'cl_listing_decrease_earnings_amount', $current_earnings - (float) $amount, $current_earnings, $amount, $this );

			if ( $this->update_meta( '_cl_listing_earnings', $new_amount ) ) {

				$this->earnings = $new_amount;

				do_action( 'cl_listing_decrease_earnings', $this->ID, $this->earnings, $this );

				return $this->earnings;
			}
		}

		return false;
	}
	private function update_meta( $meta_key = '', $meta_value = '' ) {

		global $wpdb;

		if ( empty( $meta_key ) || ( ! is_numeric( $meta_value ) && empty( $meta_value ) ) ) {
			return false;
		}

		// Make sure if it needs to be serialized, we do
		$meta_value = maybe_serialize( $meta_value );

		if ( is_numeric( $meta_value ) ) {
			$value_type = is_float( $meta_value ) ? '%f' : '%d';
		} else {
			$value_type = "'%s'";
		}

		$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = $value_type WHERE post_id = $this->ID AND meta_key = '%s'", $meta_value, $meta_key );

		if ( $wpdb->query( $sql ) ) {

			clean_post_cache( $this->ID );
			return true;
		}

		return false;
	}

	public function get_files( $variable_price_id = null ) {
		return false;
	}
	public function is_bundled_listing() {
		return 'bundle' === $this->get_type();
	}

	public function get_type() {
		if ( ! isset( $this->type ) ) {

			$this->type = get_post_meta( $this->ID, '_cl_product_type', true );

			if ( empty( $this->type ) ) {
				$this->type = 'default';
			}
		}
	}

	public function get_file_listing_limit() {
		if ( ! isset( $this->file_listing_limit ) ) {

			$ret    = 0;
			$limit  = get_post_meta( $this->ID, '_cl_listing_limit', true );
			$global = cl_admin_get_option( 'file_listing_limit', 0 );

			if ( ! empty( $limit ) || ( is_numeric( $limit ) && (int) $limit == 0 ) ) {

				// listing specific limit
				$ret = absint( $limit );
			} else {

				// Global limit
				$ret = strlen( $limit ) == 0 || $global ? $global : 0;
			}

			$this->file_listing_limit = $ret;
		}

		return absint( apply_filters( 'cl_file_listing_limit', $this->file_listing_limit, $this->ID ) );
	}
}
