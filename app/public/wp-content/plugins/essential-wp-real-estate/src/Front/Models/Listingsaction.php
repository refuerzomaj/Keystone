<?php
namespace Essential\Restate\Front\Models;

use Essential\Restate\Front\Models\Listings;
use Essential\Restate\Traitval\Traitval;

/**
 * Listings model class for listings
 *
 * since 1.0.0
 */
class Listingsaction {

	use Traitval;

	public function __construct() {
		add_filter( 'cl_listing_price', array( $this, 'cl_format_amount' ), 10 );
		add_filter( 'cl_listing_price', array( $this, 'cl_currency_filter' ), 20 );
		add_action( 'delete_post', array( $this, 'cl_remove_listing_logs_on_delete' ) );
	}


	function cl_get_listing_by( $field = '', $value = '' ) {

		if ( empty( $field ) || empty( $value ) ) {
			return false;
		}

		switch ( strtolower( $field ) ) {

			case 'id':
				$listing = get_post( $value );

				// if ( get_post_type( $listing ) != 'cl_cpt' ) {
				// return false;
				// }

				break;

			case 'slug':
			case 'name':
				$listing = get_posts(
					array(
						'post_type'      => array( 'cl_cpt', 'pricing_plan' ),
						'name'           => $value,
						'posts_per_page' => 1,
						'post_status'    => 'any',
					)
				);

				if ( $listing ) {
					$listing = $listing[0];
				}

				break;

			case 'sku':
				$listing = get_posts(
					array(
						'post_type'      => array( 'cl_cpt', 'pricing_plan' ),
						'meta_key'       => 'cl_sku',
						'meta_value'     => $value,
						'posts_per_page' => 1,
						'post_status'    => 'any',
					)
				);

				if ( $listing ) {
					$listing = $listing[0];
				}

				break;

			default:
				return false;
		}

		if ( $listing ) {
			return $listing;
		}

		return false;
	}

	/**
	 * Retrieves a listing post object by ID or slug.
	 *
	 * @since 1.0
	 * @since 2.9 - Return an Listings object.
	 *
	 * @param int $listing_id listing ID.
	 *
	 * @return Listings $listing Entire listing data.
	 */
	function cl_get_listing( $listing_id = 0 ) {
		$listing = null;

		if ( is_numeric( $listing_id ) ) {

			$found_listing = new Listings( $listing_id );

			if ( ! empty( $found_listing->ID ) ) {
				$listing = $found_listing;
			}
		} else { // Support getting a listing by name.
			$args = array(
				'post_type'     => array( 'cl_cpt', 'pricing_plan' ),
				'name'          => $listing_id,
				'post_per_page' => 1,
				'fields'        => 'ids',
			);

			$listings = new WP_Query( $args );
			if ( is_array( $listings->posts ) && ! empty( $listings->posts ) ) {

				$listing_id = $listings->posts[0];

				$listing = new Listings( $listing_id );
			}
		}

		return $listing;
	}

	/**
	 * Checks whether or not a listing is free
	 *
	 * @since 2.1
	 * @author Daniel J Griffiths
	 * @param int $listing_id ID number of the listing to check
	 * @param int $price_id (Optional) ID number of a variably priced item to check
	 * @return bool $is_free True if the product is free, false if the product is not free or the check fails
	 */
	function cl_is_free_listing( $listing_id = 0, $price_id = false ) {

		if ( empty( $listing_id ) ) {
			return false;
		}

		$listing = new Listings( $listing_id );
		return $listing->is_free( $price_id );
	}

	/**
	 * Returns the price of a listing, but only for non-variable priced listings.
	 *
	 * @since 1.0
	 * @param int $listing_id ID number of the listing to retrieve a price for
	 * @return mixed|string|int Price of the listing
	 */
	function cl_get_listing_price( $listing_id = 0 ) {

		if ( empty( $listing_id ) ) {
			return false;
		}

		$listing = new Listings( $listing_id );
		return $listing->get_price();
	}

	/**
	 * Displays a formatted price for a listing
	 *
	 * @since 1.0
	 * @param int  $listing_id ID of the listing price to show
	 * @param bool $echo Whether to echo or return the results
	 * @param int  $price_id Optional price id for variable pricing
	 * @return void
	 */
	function cl_price( $listing_id = 0, $echo = true, $price_id = false ) {

		if ( empty( $listing_id ) ) {
			$listing_id = get_the_ID();
		}

		if ( $this->cl_has_variable_prices( $listing_id ) ) {

			$prices = $this->cl_get_variable_prices( $listing_id );

			if ( false !== $price_id && isset( $prices[ $price_id ] ) ) {

				$price = $this->cl_get_price_option_amount( $listing_id, $price_id );
			} elseif ( $default = cl_get_default_variable_price( $listing_id ) ) {

				$price = $this->cl_get_price_option_amount( $listing_id, $default );
			} else {

				$price = $this->cl_get_lowest_price_option( $listing_id );
			}

			$price = WPERECCP()->common->formatting->cl_sanitize_amount( $price );
		} else {

			$price = $this->cl_get_listing_price( $listing_id );
		}

		$price           = apply_filters( 'cl_listing_price', WPERECCP()->common->formatting->cl_sanitize_amount( $price ), $listing_id, $price_id );
		$formatted_price = '<span class="cl_price" id="cl_price_' . esc_attr( $listing_id ) . '">' . esc_html( $price ) . '</span>';
		$formatted_price = apply_filters( 'cl_listing_price_after_html', $formatted_price, $listing_id, $price, $price_id );

		if ( $echo ) {
			echo wp_kses( $formatted_price, 'cl_code_context' );
		} else {
			return $formatted_price;
		}
	}


	/**
	 * Retrieves the final price of a listingable product after purchase
	 * this price includes any necessary discounts that were applied
	 *
	 * @since 1.0
	 * @param int    $listing_id ID of the listing
	 * @param array  $user_purchase_info - an array of all information for the payment
	 * @param string $amount_override a custom amount that over rides the 'cl_price' meta, used for variable prices
	 * @return string - the price of the listing
	 */
	function cl_get_listing_final_price( $listing_id, $user_purchase_info, $amount_override = null ) {
		if ( is_null( $amount_override ) ) {
			$original_price = get_post_meta( $listing_id, 'cl_price', true );
		} else {
			$original_price = $amount_override;
		}
		if ( isset( $user_purchase_info['discount'] ) && $user_purchase_info['discount'] != 'none' ) {
			// if the discount was a %, we modify the amount. Flat rate discounts are ignored
			if ( WPERECCP()->front->discountaction->cl_get_discount_type( WPERECCP()->front->discountaction->cl_get_discount_id_by_code( $user_purchase_info['discount'] ) ) != 'flat' ) {
				$price = WPERECCP()->front->discountaction->cl_get_discounted_amount( $user_purchase_info['discount'], $original_price );
			} else {
				$price = $original_price;
			}
		} else {
			$price = $original_price;
		}
		return apply_filters( 'cl_final_price', $price, $listing_id, $user_purchase_info );
	}

	/**
	 * Retrieves the variable prices for a listing
	 *
	 * @since 1.2
	 * @param int $listing_id ID of the listing
	 * @return array Variable prices
	 */
	function cl_get_variable_prices( $listing_id = 0 ) {

		if ( empty( $listing_id ) ) {
			return false;
		}

		$listing = new Listings( $listing_id );
		return $listing->get_prices();
	}

	/**
	 * Checks to see if a listing has variable prices enabled.
	 *
	 * @since 1.0.7
	 * @param int $listing_id ID number of the listing to check
	 * @return bool true if has variable prices, false otherwise
	 */
	function cl_has_variable_prices( $listing_id = 0 ) {

		if ( empty( $listing_id ) ) {
			return false;
		}

		$listing = new Listings( $listing_id );
		return $listing->has_variable_prices();
	}

	/**
	 * Returns the default price ID for variable pricing, or the first
	 * price if none is set
	 *
	 * @since  2.2
	 * @param  int $listing_id ID number of the listing to check
	 * @return int              The Price ID to select by default
	 */
	function cl_get_default_variable_price( $listing_id = 0 ) {

		if ( ! cl_has_variable_prices( $listing_id ) ) {
			return false;
		}

		$prices           = $this->cl_get_variable_prices( $listing_id );
		$default_price_id = get_post_meta( $listing_id, '_cl_default_price_id', true );

		if ( $default_price_id === '' || ! isset( $prices[ $default_price_id ] ) ) {
			$default_price_id = current( array_keys( $prices ) );
		}

		return apply_filters( 'cl_variable_default_price_id', absint( $default_price_id ), $listing_id );
	}

	/**
	 * Retrieves the name of a variable price option
	 *
	 * @since 1.0.9
	 * @param int $listing_id ID of the listing
	 * @param int $price_id ID of the price option
	 * @param int $payment_id optional payment ID for use in filters
	 * @return string $price_name Name of the price option
	 */
	function cl_get_price_option_name( $listing_id = 0, $price_id = 0, $payment_id = 0 ) {
		$prices     = $this->cl_get_variable_prices( $listing_id );
		$price_name = '';

		if ( $prices && is_array( $prices ) ) {
			if ( isset( $prices[ $price_id ] ) ) {
				$price_name = $prices[ $price_id ]['name'];
			}
		}

		return apply_filters( 'cl_get_price_option_name', $price_name, $listing_id, $payment_id, $price_id );
	}

	/**
	 * Retrieves the amount of a variable price option
	 *
	 * @since 1.8.2
	 * @param int $listing_id ID of the listing
	 * @param int $price_id ID of the price option
	 * @param int $payment_id ID of the payment
	 * @return float $amount Amount of the price option
	 */
	function cl_get_price_option_amount( $listing_id = 0, $price_id = 0 ) {
		$prices = $this->cl_get_variable_prices( $listing_id );
		$amount = 0.00;

		if ( $prices && is_array( $prices ) ) {
			if ( isset( $prices[ $price_id ] ) ) {
				$amount = $prices[ $price_id ]['amount'];
			}
		}

		return apply_filters( 'cl_get_price_option_amount', WPERECCP()->common->formatting->cl_sanitize_amount( $amount ), $listing_id, $price_id );
	}

	/**
	 * Retrieves cheapest price option of a variable priced listing
	 *
	 * @since 1.4.4
	 * @param int $listing_id ID of the listing
	 * @return float Amount of the lowest price
	 */
	function cl_get_lowest_price_option( $listing_id = 0 ) {
		if ( empty( $listing_id ) ) {
			$listing_id = get_the_ID();
		}

		if ( ! $this->cl_has_variable_prices( $listing_id ) ) {
			return $this->cl_get_listing_price( $listing_id );
		}

		$prices = $this->cl_get_variable_prices( $listing_id );

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

	/**
	 * Retrieves the ID for the cheapest price option of a variable priced listing
	 *
	 * @since 2.2
	 * @param int $listing_id ID of the listing
	 * @return int ID of the lowest price
	 */
	function cl_get_lowest_price_id( $listing_id = 0 ) {
		if ( empty( $listing_id ) ) {
			$listing_id = get_the_ID();
		}

		if ( ! $this->cl_has_variable_prices( $listing_id ) ) {
			return $this->cl_get_listing_price( $listing_id );
		}

		$prices = $this->cl_get_variable_prices( $listing_id );

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
		}

		return (int) $min_id;
	}

	/**
	 * Retrieves most expensive price option of a variable priced listing
	 *
	 * @since 1.4.4
	 * @param int $listing_id ID of the listing
	 * @return float Amount of the highest price
	 */
	function cl_get_highest_price_option( $listing_id = 0 ) {

		if ( empty( $listing_id ) ) {
			$listing_id = get_the_ID();
		}

		if ( ! $this->cl_has_variable_prices( $listing_id ) ) {
			return $this->cl_get_listing_price( $listing_id );
		}

		$prices = $this->cl_get_variable_prices( $listing_id );

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

	/**
	 * Retrieves a price from from low to high of a variable priced listing
	 *
	 * @since 1.4.4
	 * @param int $listing_id ID of the listing
	 * @return string $range A fully formatted price range
	 */
	function cl_price_range( $listing_id = 0 ) {
		$low    = $this->cl_get_lowest_price_option( $listing_id );
		$high   = $this->cl_get_highest_price_option( $listing_id );
		$range  = '<span class="cl_price cl_price_range_low" id="cl_price_low_' . esc_attr( $listing_id ) . '">' . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $low ) ) . '</span>';
		$range .= '<span class="cl_price_range_sep">&nbsp;&ndash;&nbsp;</span>';
		$range .= '<span class="cl_price cl_price_range_high" id="cl_price_high_' . esc_attr( $listing_id ) . '">' . WPERECCP()->common->formatting->cl_currency_filter( WPERECCP()->common->formatting->cl_format_amount( $high ) ) . '</span>';

		return apply_filters( 'cl_price_range', $range, $listing_id, $low, $high );
	}

	/**
	 * Checks to see if multiple price options can be purchased at once
	 *
	 * @since 1.4.2
	 * @param int $listing_id listing ID
	 * @return bool
	 */
	function cl_single_price_option_mode( $listing_id = 0 ) {

		if ( empty( $listing_id ) ) {
			$listing = get_post();

			$listing_id = isset( $listing->ID ) ? $listing->ID : 0;
		}

		if ( empty( $listing_id ) ) {
			return false;
		}

		$listing = new Listings( $listing_id );
		return $listing->is_single_price_mode();
	}

	/**
	 * Get product types
	 *
	 * @since 1.8
	 * @return array $types listing types
	 */
	function cl_get_listing_types() {

		$types = array(
			'0'      => __( 'Default', 'essential-wp-real-estate' ),
			'bundle' => __( 'Bundle', 'essential-wp-real-estate' ),
		);

		return apply_filters( 'cl_listing_types', $types );
	}

	/**
	 * Gets the listing type, either default or "bundled"
	 *
	 * @since 1.6
	 * @param int $listing_id listing ID
	 * @return string $type listing type
	 */
	function cl_get_listing_type( $listing_id = 0 ) {
		$listing = new Listings( $listing_id );
		return $listing->post_type;
	}

	/**
	 * Determines if a product is a bundle
	 *
	 * @since 1.6
	 * @param int $listing_id listing ID
	 * @return bool
	 */
	function cl_is_bundled_product( $listing_id = 0 ) {
		$listing = new Listings( $listing_id );
		return $listing->is_bundled_listing();
	}


	/**
	 * Retrieves the product IDs of bundled products
	 *
	 * @since 1.6
	 * @param int $listing_id listing ID
	 * @return array $products Products in the bundle
	 *
	 * @since 2.7
	 * @param int $price_id Variable price ID
	 */
	function cl_get_bundled_products( $listing_id = 0, $price_id = null ) {
		$listing = new Listings( $listing_id );
		if ( null !== $price_id ) {
			return $listing->get_variable_priced_bundled_listings( $price_id );
		} else {
			return $listing->bundled_listings;
		}
	}

	/**
	 * Returns the total earnings for a listing.
	 *
	 * @since 1.0
	 * @param int $listing_id listing ID
	 * @return int $earnings Earnings for a certain listing
	 */
	function cl_get_listing_earnings_stats( $listing_id = 0 ) {
		$listing = new Listings( $listing_id );
		return $listing->earnings;
	}

	/**
	 * Return the sales number for a listing.
	 *
	 * @since 1.0
	 * @param int $listing_id listing ID
	 * @return int $sales Amount of sales for a certain listing
	 */
	function cl_get_listing_sales_stats( $listing_id = 0 ) {
		$listing = new Listings( $listing_id );
		return $listing->sales;
	}

	/**
	 * Record Sale In Log
	 *
	 * Stores log information for a listing sale.
	 *
	 * @since 1.0
	 * @global $cl_logs
	 * @param int         $listing_id listing ID
	 * @param int         $payment_id Payment ID
	 * @param bool|int    $price_id Price ID, if any
	 * @param string|null $sale_date The date of the sale
	 * @return void
	 */
	function cl_record_sale_in_log( $listing_id, $payment_id, $price_id = false, $sale_date = null ) {
		global $cl_logs;

		$log_data = array(
			'post_parent'   => $listing_id,
			'log_type'      => 'sale',
			'post_date'     => ! empty( $sale_date ) ? $sale_date : null,
			'post_date_gmt' => ! empty( $sale_date ) ? get_gmt_from_date( $sale_date ) : null,
		);

		$log_meta = array(
			'payment_id' => $payment_id,
			'price_id'   => (int) $price_id,
		);

		$cl_logs->insert_log( $log_data, $log_meta );
	}

	/**
	 * Record listing In Log
	 *
	 * Stores a log entry for a file listing.
	 *
	 * @since 1.0
	 * @global $cl_logs
	 * @param int    $listing_id listing ID
	 * @param int    $file_id ID of the file listinged
	 * @param array  $user_info User information (Deprecated)
	 * @param string $ip IP Address
	 * @param int    $payment_id Payment ID
	 * @param int    $price_id Price ID, if any
	 * @return void
	 */
	function cl_record_listing_in_log( $listing_id, $file_id, $user_info, $ip, $payment_id, $price_id = false ) {
		global $cl_logs;

		$log_data = array(
			'post_parent' => $listing_id,
			'log_type'    => 'file_listing',
		);

		$payment = new Clpayment( $payment_id );

		$log_meta = array(
			'customer_id' => $payment->customer_id,
			'user_id'     => $payment->user_id,
			'file_id'     => (int) $file_id,
			'ip'          => $ip,
			'payment_id'  => $payment_id,
			'price_id'    => (int) $price_id,
		);

		$cl_logs->insert_log( $log_data, $log_meta );
	}

	/**
	 * Delete log entries when deleting listing product
	 *
	 * Removes all related log entries when a listing is completely deleted.
	 * (Does not run when a listing is trashed)
	 *
	 * @since 1.3.4
	 * @param int $listing_id listing ID
	 * @return void
	 */
	function cl_remove_listing_logs_on_delete( $listing_id = 0 ) {
		// if ( 'cl_cpt' !== get_post_type( $listing_id ) ) {
		// return;
		// }

		global $cl_logs;

		// Remove all log entries related to this listing
		$cl_logs->delete_logs( $listing_id );
	}


	/**
	 *
	 * Increases the sale count of a listing.
	 *
	 * @since 1.0
	 * @param int $listing_id listing ID
	 * @param int $quantity Quantity to increase purchase count by
	 * @return bool|int
	 */
	function cl_increase_purchase_count( $listing_id = 0, $quantity = 1 ) {
		$quantity = (int) $quantity;
		$listing  = new Listings( $listing_id );
		return $listing->increase_sales( $quantity );
	}

	/**
	 * Decreases the sale count of a listing. Primarily for when a purchase is
	 * refunded.
	 *
	 * @since 1.0.8.1
	 * @param int $listing_id listing ID
	 * @return bool|int
	 */
	function cl_decrease_purchase_count( $listing_id = 0, $quantity = 1 ) {
		$listing = new Listings( $listing_id );
		return $listing->decrease_sales( $quantity );
	}

	/**
	 * Increases the total earnings of a listing.
	 *
	 * @since 1.0
	 * @param int $listing_id listing ID
	 * @param int $amount Earnings
	 * @return bool|int
	 */
	function cl_increase_earnings( $listing_id, $amount ) {
		$listing = new Listings( $listing_id );
		return $listing->increase_earnings( $amount );
	}

	/**
	 * Decreases the total earnings of a listing. Primarily for when a purchase is refunded.
	 *
	 * @since 1.0.8.1
	 * @param int $listing_id listing ID
	 * @param int $amount Earnings
	 * @return bool|int
	 */
	function cl_decrease_earnings( $listing_id, $amount ) {
		$listing = new Listings( $listing_id );
		return $listing->decrease_earnings( $amount );
	}

	/**
	 * Retrieves the average monthly earnings for a specific listing
	 *
	 * @since 1.3
	 * @param int $listing_id listing ID
	 * @return float $earnings Average monthly earnings
	 */
	function cl_get_average_monthly_listing_earnings( $listing_id = 0 ) {
		$earnings     = cl_get_listing_earnings_stats( $listing_id );
		$release_date = get_post_field( 'post_date', $listing_id );

		$diff = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

		$months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

		if ( $months > 0 ) {
			$earnings = ( $earnings / $months );
		}

		return $earnings < 0 ? 0 : $earnings;
	}

	/**
	 * Retrieves the average monthly sales for a specific listing
	 *
	 * @since 1.3
	 * @param int $listing_id listing ID
	 * @return float $sales Average monthly sales
	 */
	function cl_get_average_monthly_listing_sales( $listing_id = 0 ) {
		$sales        = cl_get_listing_sales_stats( $listing_id );
		$release_date = get_post_field( 'post_date', $listing_id );

		$diff = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

		$months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

		if ( $months > 0 ) {
			$sales = ( $sales / $months );
		}

		return $sales;
	}

	/**
	 * Gets all listing files for a product
	 *
	 * Can retrieve files specific to price ID
	 *
	 * @since 1.0
	 * @param int $listing_id listing ID
	 * @param int $variable_price_id Variable pricing option ID
	 * @return array $files listing files
	 */
	function cl_get_listing_files( $listing_id = 0, $variable_price_id = null ) {
		$listing = new Listings( $listing_id );
		return $listing->get_files( $variable_price_id );
	}

	/**
	 * Retrieves a file name for a product's listing file
	 *
	 * Defaults to the file's actual name if no 'name' key is present
	 *
	 * @since 1.6
	 * @param array $file File array
	 * @return string The file name
	 */
	function cl_get_file_name( $file = array() ) {
		if ( empty( $file ) || ! is_array( $file ) ) {
			return false;
		}

		$name = ! empty( $file['name'] ) ? esc_html( $file['name'] ) : basename( $file['file'] );

		return $name;
	}

	/**
	 * Gets the number of times a file has been listinged for a specific purchase
	 *
	 * @since 1.6
	 * @param int $listing_id listing ID
	 * @param int $file_key File key
	 * @param int $payment_id The ID number of the associated payment
	 * @return int Number of times the file has been listinged for the purchase
	 */
	function cl_get_file_listinged_count( $listing_id = 0, $file_key = 0, $payment_id = 0 ) {
		global $cl_logs;

		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'   => '_cl_log_file_id',
				'value' => (int) $file_key,
			),
			array(
				'key'   => '_cl_log_payment_id',
				'value' => (int) $payment_id,
			),
		);

		return $cl_logs->get_log_count( $listing_id, 'file_listing', $meta_query );
	}


	/**
	 * Gets the file listing file limit for a particular listing
	 *
	 * This limit refers to the maximum number of times files connected to a product
	 * can be listinged.
	 *
	 * @since 1.3.1
	 * @param int $listing_id listing ID
	 * @return int $limit File listing limit
	 */
	function cl_get_file_listing_limit( $listing_id = 0 ) {
		$listing = new Listings( $listing_id );
		return $listing->get_file_listing_limit();
	}

	/**
	 * Gets the file listing file limit override for a particular listing
	 *
	 * The override allows the main file listing limit to be bypassed
	 *
	 * @since 1.3.2
	 * @param int $listing_id listing ID
	 * @param int $payment_id Payment ID
	 * @return int $limit_override The new limit
	 */
	function cl_get_file_listing_limit_override( $listing_id = 0, $payment_id = 0 ) {
		$limit_override = get_post_meta( $listing_id, '_cl_listing_limit_override_' . $payment_id, true );
		if ( $limit_override ) {
			return absint( $limit_override );
		}
		return 0;
	}

	/**
	 * Sets the file listing file limit override for a particular listing
	 *
	 * The override allows the main file listing limit to be bypassed
	 * If no override is set yet, the override is set to the main limit + 1
	 * If the override is already set, then it is simply incremented by 1
	 *
	 * @since 1.3.2
	 * @param int $listing_id listing ID
	 * @param int $payment_id Payment ID
	 * @return void
	 */
	function cl_set_file_listing_limit_override( $listing_id = 0, $payment_id = 0 ) {
		$override = $this->cl_get_file_listing_limit_override( $listing_id, $payment_id );
		$limit    = $this->cl_get_file_listing_limit( $listing_id );

		if ( ! empty( $override ) ) {
			$override = $override += 1;
		} else {
			$override = $limit += 1;
		}

		update_post_meta( $listing_id, '_cl_listing_limit_override_' . $payment_id, $override );
	}

	/**
	 * Checks if a file is at its listing limit
	 *
	 * This limit refers to the maximum number of times files connected to a product
	 * can be listinged.
	 *
	 * @since 1.3.1
	 * @uses CL_Logging::get_log_count()
	 * @param int       $listing_id listing ID
	 * @param int       $payment_id Payment ID
	 * @param int       $file_id File ID
	 * @param int|false $price_id Price ID
	 * @return bool True if at limit, false otherwise
	 */
	function cl_is_file_at_listing_limit( $listing_id = 0, $payment_id = 0, $file_id = 0, $price_id = false ) {

		// Assume that the file listing limit has not been hit.
		$ret           = false;
		$listing_limit = $this->cl_get_file_listing_limit( $listing_id );

		if ( ! empty( $listing_limit ) ) {

			// The store does not have unlimited listings, does this payment?
			$unlimited_purchase = cl_payment_has_unlimited_listings( $payment_id );

			if ( empty( $unlimited_purchase ) ) {

				// Get the file listing count.
				$logs = new CL_Logging();

				$meta_query = array(
					'relation' => 'AND',
					array(
						'key'   => '_cl_log_file_id',
						'value' => (int) $file_id,
					),
					array(
						'key'   => '_cl_log_payment_id',
						'value' => (int) $payment_id,
					),
					array(
						'key'   => '_cl_log_price_id',
						'value' => (int) $price_id,
					),
				);

				$listing_count = $logs->get_log_count( $listing_id, 'file_listing', $meta_query );

				if ( $listing_count >= $listing_limit ) {
					$ret = true;

					// Check to make sure the limit isn't overwritten.
					// A limit is overwritten when purchase receipt is resent.
					$limit_override = $this->cl_get_file_listing_limit_override( $listing_id, $payment_id );

					if ( ! empty( $limit_override ) && $listing_count < $limit_override ) {
						$ret = false;
					}
				}
			}
		}

		/**
		 * Filters whether or not a file is at its listing limit.
		 *
		 * @param bool      $ret
		 * @param int       $listing_id
		 * @param int       $payment_id
		 * @param int       $file_id
		 * @param int|false $price_id
		 *
		 * @since 2.10 Added `$price_id` parameter.
		 */
		return (bool) apply_filters( 'cl_is_file_at_listing_limit', $ret, $listing_id, $payment_id, $file_id, $price_id );
	}

	/**
	 * Gets the Price ID that can listing a file
	 *
	 * @since 1.0.9
	 * @param int    $listing_id listing ID
	 * @param string $file_key File Key
	 * @return string - the price ID if restricted, "all" otherwise
	 */
	function cl_get_file_price_condition( $listing_id, $file_key ) {
		$listing = new Listings( $listing_id );
		return $listing->get_file_price_condition( $file_key );
	}

	/**
	 * Get listing File Url
	 * Constructs a secure file listing url for a specific file.
	 *
	 * @since 1.0
	 *
	 * @param string   $key Payment key. Use cl_get_payment_key() to get key.
	 * @param string   $email Customer email address. Use cl_get_payment_user_email() to get user email.
	 * @param int      $filekey Index of array of files returned by cl_get_listing_files() that this listing link is for.
	 * @param int      $listing_id Optional. ID of listing this listing link is for. Default is 0.
	 * @param bool|int $price_id Optional. Price ID when using variable prices. Default is false.
	 *
	 * @return string A secure listing URL
	 */
	function cl_get_listing_file_url( $key, $email, $filekey, $listing_id = 0, $price_id = false ) {

		$hours = absint( cl_admin_get_option( 'listing_link_expiration', 24 ) );

		if ( ! ( $date = strtotime( '+' . $hours . 'hours', current_time( 'timestamp' ) ) ) ) {
			$date = 2147472000; // Highest possible date, January 19, 2038
		}

		// Leaving in this array and the filter for backwards compatibility now
		$old_args = array(
			'listing_key' => rawurlencode( $key ),
			'email'       => rawurlencode( $email ),
			'file'        => rawurlencode( $filekey ),
			'price_id'    => (int) $price_id,
			'listing_id'  => $listing_id,
			'expire'      => rawurlencode( $date ),
		);

		$params  = apply_filters( 'cl_listing_file_url_args', $old_args );
		$payment = cl_get_payment_by( 'key', $params['listing_key'] );

		if ( ! $payment ) {
			return false;
		}

		$args = array();

		if ( ! empty( $payment->ID ) ) {

			// Simply the URL by concatenating required data using a colon as a delimiter.
			$args = array(
				'clfile' => rawurlencode( sprintf( '%d:%d:%d:%d', $payment->ID, $params['listing_id'], $params['file'], $price_id ) ),
			);

			if ( isset( $params['expire'] ) ) {
				$args['ttl'] = $params['expire'];
			}

			// Ensure all custom args registered with extensions through cl_listing_file_url_args get added to the URL, but without adding all the old args
			$args = array_merge( $args, array_diff_key( $params, $old_args ) );

			$args = apply_filters( 'cl_get_listing_file_url_args', $args, $payment->ID, $params );

			$args['file']  = $params['file'];
			$args['token'] = cl_get_listing_token( add_query_arg( $args, untrailingslashit( site_url() ) ) );
		}

		$listing_url = add_query_arg( $args, site_url( 'index.php' ) );

		return $listing_url;
	}

	/**
	 * Get product notes
	 *
	 * @since 1.2.1
	 * @param int $listing_id listing ID
	 * @return string $notes Product notes
	 */
	function cl_get_product_notes( $listing_id = 0 ) {
		$listing = new Listings( $listing_id );
		return $listing->notes;
	}

	/**
	 * Retrieves a listing SKU by ID.
	 *
	 * @since 1.6
	 *
	 * @author Daniel J Griffiths
	 * @param int $listing_id
	 *
	 * @return mixed|void listing SKU
	 */
	function cl_get_listing_sku( $listing_id = 0 ) {
		$listing = new Listings( $listing_id );
		return $listing->sku;
	}

	/**
	 * get the listing button behavior, either add to cart or direct
	 *
	 * @since 1.7
	 *
	 * @param int $listing_id
	 * @return mixed|void Add to Cart or Direct
	 */
	function cl_get_listing_button_behavior( $listing_id = 0 ) {
		$listing = new Listings( $listing_id );
		return $listing->button_behavior;
	}

	/**
	 * Is quantity input disabled on this product?
	 *
	 * @since 2.7
	 * @return bool
	 */
	function cl_listing_quantities_disabled( $listing_id = 0 ) {

		$listing = new Listings( $listing_id );
		return $listing->quantities_disabled();
	}

	/**
	 * Get the file listing method
	 *
	 * @since 1.6
	 * @return string The method to use for file listings
	 */
	function cl_get_file_listing_method() {
		$method = cl_admin_get_option( 'listing_method', 'direct' );
		return apply_filters( 'cl_file_listing_method', $method );
	}

	/**
	 * Returns a random listing
	 *
	 * @since 1.7
	 * @author Chris Christoff
	 * @param bool $post_ids True for array of post ids, false if array of posts
	 * @return array Returns an array of post ids or post objects
	 */
	function cl_get_random_listing( $post_ids = true ) {
		return cl_get_random_listings( 1, $post_ids );
	}

	/**
	 * Returns random listings
	 *
	 * @since 1.7
	 * @author Chris Christoff
	 * @param int  $num The number of posts to return
	 * @param bool $post_ids True for array of post objects, else array of ids
	 * @return mixed $query Returns an array of id's or an array of post objects
	 */
	function cl_get_random_listings( $num = 3, $post_ids = true ) {
		if ( $post_ids ) {
			$args = array(
				'post_type'   => array( 'cl_cpt', 'pricing_plan' ),
				'orderby'     => 'rand',
				'numberposts' => $num,
				'fields'      => 'ids',
			);
		} else {
			$args = array(
				'post_type'   => array( 'cl_cpt', 'pricing_plan' ),
				'orderby'     => 'rand',
				'numberposts' => $num,
			);
		}
		$args = apply_filters( 'cl_get_random_listings', $args );
		return get_posts( $args );
	}

	/**
	 * Generates a token for a given URL.
	 *
	 * An 'o' query parameter on a URL can include optional variables to test
	 * against when verifying a token without passing those variables around in
	 * the URL. For example, listings can be limited to the IP that the URL was
	 * generated for by adding 'o=ip' to the query string.
	 *
	 * Or suppose when WordPress requested a URL for automatic updates, the user
	 * agent could be tested to ensure the URL is only valid for requests from
	 * that user agent.
	 *
	 * @since 2.3
	 *
	 * @param string $url The URL to generate a token for.
	 * @return string The token for the URL.
	 */
	function cl_get_listing_token( $url = '' ) {

		$args   = array();
		$hash   = apply_filters( 'cl_get_url_token_algorithm', 'sha256' );
		$secret = apply_filters( 'cl_get_url_token_secret', hash( $hash, wp_salt() ) );

		/*
		* Add additional args to the URL for generating the token.
		* Allows for restricting access to IP and/or user agent.
		*/
		$parts   = parse_url( $url );
		$options = array();

		if ( isset( $parts['query'] ) ) {

			wp_parse_str( $parts['query'], $query_args );

			// o = option checks (ip, user agent).
			if ( ! empty( $query_args['o'] ) ) {

				// Multiple options can be checked by separating them with a colon in the query parameter.
				$options = explode( ':', rawurldecode( $query_args['o'] ) );

				if ( in_array( 'ip', $options ) ) {

					$args['ip'] = cl_get_ip();
				}

				if ( in_array( 'ua', $options ) ) {

					$ua                 = isset( $_SERVER['HTTP_USER_AGENT'] ) ? cl_sanitization( $_SERVER['HTTP_USER_AGENT'] ) : '';
					$args['user_agent'] = rawurlencode( $ua );
				}
			}
		}

		/*
		* Filter to modify arguments and allow custom options to be tested.
		* Be sure to rawurlencode any custom options for consistent results.
		*/
		$args = apply_filters( 'cl_get_url_token_args', $args, $url, $options );

		$args['secret'] = $secret;
		$args['token']  = false; // Removes a token if present.

		$url   = add_query_arg( $args, $url );
		$parts = parse_url( $url );

		// In the event there isn't a path, set an empty one so we can MD5 the token
		if ( ! isset( $parts['path'] ) ) {

			$parts['path'] = '';
		}

		$token = hash_hmac( 'sha256', $parts['path'] . '?' . $parts['query'], wp_salt( 'cl_file_listing_link' ) );
		return $token;
	}

	/**
	 * Generate a token for a URL and match it against the existing token to make
	 * sure the URL hasn't been tampered with.
	 *
	 * @since 2.3
	 *
	 * @param string $url URL to test.
	 * @return bool
	 */
	function cl_validate_url_token( $url = '' ) {

		$ret   = false;
		$parts = parse_url( $url );

		if ( isset( $parts['query'] ) ) {

			wp_parse_str( $parts['query'], $query_args );

			// If the TTL is in the past, die out before we go any further.
			if ( isset( $query_args['ttl'] ) && current_time( 'timestamp' ) > $query_args['ttl'] ) {

				wp_die( apply_filters( 'cl_listing_link_expired_text', __( 'Sorry but your listing link has expired.', 'essential-wp-real-estate' ) ), __( 'Error', 'essential-wp-real-estate' ), array( 'response' => 403 ) );
			}

			// These are the only URL parameters that are allowed to affect the token validation.
			$allowed = apply_filters(
				'cl_url_token_allowed_params',
				array(
					'clfile',
					'ttl',
					'file',
					'token',
				)
			);

			// Collect the allowed tags in proper order, remove all tags, and re-add only the allowed ones.
			$allowed_args = array();

			foreach ( $allowed as $key ) {
				if ( true === array_key_exists( $key, $query_args ) ) {
					$allowed_args[ $key ] = $query_args[ $key ];
				}
			}

			// strtok allows a quick clearing of existing query string parameters, so we can re-add the allowed ones.
			$url = add_query_arg( $allowed_args, strtok( $url, '?' ) );

			if ( isset( $query_args['token'] ) && hash_equals( $query_args['token'], cl_get_listing_token( $url ) ) ) {

				$ret = true;
			}
		}

		return apply_filters( 'cl_validate_url_token', $ret, $url, $query_args );
	}

	/**
	 * Allows parsing of the values saved by the product drop down.
	 *
	 * @since  2.6.9
	 * @param  array $values Parse the values from the product dropdown into a readable array
	 * @return array         A parsed set of values for listing_id and price_id
	 */
	function cl_parse_product_dropdown_values( $values = array() ) {

		$parsed_values = array();

		if ( is_array( $values ) ) {

			foreach ( $values as $value ) {
				$value = cl_parse_product_dropdown_value( $value );

				$parsed_values[] = array(
					'listing_id' => $value['listing_id'],
					'price_id'   => $value['price_id'],
				);
			}
		} else {

			$value           = cl_parse_product_dropdown_value( $values );
			$parsed_values[] = array(
				'listing_id' => $value['listing_id'],
				'price_id'   => $value['price_id'],
			);
		}

		return $parsed_values;
	}

	/**
	 * Given a value from the product dropdown array, parse it's parts
	 *
	 * @since  2.6.9
	 * @param  string $values A value saved in a product dropdown array
	 * @return array          A parsed set of values for listing_id and price_id
	 */
	function cl_parse_product_dropdown_value( $value ) {
		$parts      = explode( '_', $value );
		$listing_id = $parts[0];
		$price_id   = isset( $parts[1] ) ? $parts[1] : false;

		return array(
			'listing_id' => $listing_id,
			'price_id'   => $price_id,
		);
	}

	/**
	 * Get bundle pricing variations
	 *
	 * @since  2.7
	 * @param  int $listing_id
	 * @return array|void
	 */
	function cl_get_bundle_pricing_variations( $listing_id = 0 ) {
		if ( $listing_id == 0 ) {
			return;
		}

		$listing = new Listings( $listing_id );
		return $listing->get_bundle_pricing_variations();
	}
}
