<?php
namespace  Essential\Restate\Front\Purchase\Payments;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CL_Stats Class
 *
 * This class is for retrieving stats for earnings and sales
 *
 * Stats can be retrieved for date ranges and pre-defined periods
 *
 * @since 1.8
 */
class Clpaymentstats {

	/**
	 * Retrieve sale stats
	 *
	 * @since 1.8
	 *
	 * @param int          $listing_id The listing product to retrieve stats for. If false, gets stats for all products
	 * @param string|bool  $start_date  The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param string|bool  $end_date    The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param string|array $status      The sale status(es) to count. Only valid when retrieving global stats
	 *
	 * @return float|int Total amount of sales based on the passed arguments.
	 */
	/**
	 * The start date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * @since 1.8
	 */
	public $start_date;


	/**
	 * The end date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * The end date is optional
	 *
	 * @since 1.8
	 */
	public $end_date;

	/**
	 * Flag to determine if current query is based on timestamps
	 *
	 * @since 1.9
	 */
	public $timestamp;

	/**
	 *
	 * @since 1.8
	 * @return void
	 */
	public function __construct() {
		/* nothing here. Call get_sales() and get_earnings() directly */
	}


	/**
	 * Get the predefined date periods permitted
	 *
	 * @since 1.8
	 * @return array
	 */
	public function get_predefined_dates() {
		$predefined = array(
			'today'        => __( 'Today', 'essential-wp-real-estate' ),
			'yesterday'    => __( 'Yesterday', 'essential-wp-real-estate' ),
			'this_week'    => __( 'This Week', 'essential-wp-real-estate' ),
			'last_week'    => __( 'Last Week', 'essential-wp-real-estate' ),
			'this_month'   => __( 'This Month', 'essential-wp-real-estate' ),
			'last_month'   => __( 'Last Month', 'essential-wp-real-estate' ),
			'this_quarter' => __( 'This Quarter', 'essential-wp-real-estate' ),
			'last_quarter' => __( 'Last Quarter', 'essential-wp-real-estate' ),
			'this_year'    => __( 'This Year', 'essential-wp-real-estate' ),
			'last_year'    => __( 'Last Year', 'essential-wp-real-estate' ),
		);
		return apply_filters( 'cl_stats_predefined_dates', $predefined );
	}

	/**
	 * Setup the dates passed to our constructor.
	 *
	 * This calls the convert_date() member function to ensure the dates are formatted correctly
	 *
	 * @since 1.8
	 * @return void
	 */
	public function setup_dates( $_start_date = 'this_month', $_end_date = false ) {

		if ( empty( $_start_date ) ) {
			$_start_date = 'this_month';
		}

		if ( empty( $_end_date ) ) {
			$_end_date = $_start_date;
		}

		$this->start_date = $this->convert_date( $_start_date );
		$this->end_date   = $this->convert_date( $_end_date, true );
	}

	/**
	 * Converts a date to a timestamp
	 *
	 * @since 1.8
	 * @return array|WP_Error If the date is invalid, a WP_Error object will be returned
	 */
	public function convert_date( $date, $end_date = false ) {

		$this->timestamp = false;
		$second          = $end_date ? 59 : 0;
		$minute          = $end_date ? 59 : 0;
		$hour            = $end_date ? 23 : 0;
		$day             = 1;
		$month           = date( 'n', current_time( 'timestamp' ) );
		$year            = date( 'Y', current_time( 'timestamp' ) );

		if ( ( is_string( $date ) || is_int( $date ) ) && array_key_exists( $date, $this->get_predefined_dates() ) ) {

			// This is a predefined date rate, such as last_week
			switch ( $date ) {

				case 'this_month':
					if ( $end_date ) {

						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'last_month':
					if ( $month == 1 ) {

						$month = 12;
						$year--;
					} else {

						$month--;
					}

					if ( $end_date ) {
						$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );
					}

					break;

				case 'today':
					$day = date( 'd', current_time( 'timestamp' ) );

					if ( $end_date ) {
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'yesterday':
					$day = date( 'd', current_time( 'timestamp' ) ) - 1;

					// Check if Today is the first day of the month (meaning subtracting one will get us 0)
					if ( $day < 1 ) {

						// If current month is 1
						if ( 1 == $month ) {

							$year -= 1; // Today is January 1, so skip back to last day of December
							$month = 12;
							$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						} else {

							// Go back one month and get the last day of the month
							$month -= 1;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						}
					}

					break;

				case 'this_week':
					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) * 60 * 60 * 24;
					$today              = date( 'd', current_time( 'timestamp' ) ) * 60 * 60 * 24;

					if ( $today < $days_to_week_start ) {

						if ( $month > 1 ) {
							$month -= 1;
						} else {
							$month = 12;
						}
					}

					if ( ! $end_date ) {

						// Getting the start day

						$day  = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' );
					} else {

						// Getting the end day

						$day  = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' ) + 6;
					}

					break;

				case 'last_week':
					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) * 60 * 60 * 24;
					$today              = date( 'd', current_time( 'timestamp' ) ) * 60 * 60 * 24;

					if ( $today < $days_to_week_start ) {

						if ( $month > 1 ) {
							$month -= 1;
						} else {
							$month = 12;
						}
					}

					if ( ! $end_date ) {

						// Getting the start day

						$day  = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' );
					} else {

						// Getting the end day

						$day  = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' ) + 6;
					}

					break;

				case 'this_quarter':
					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if ( ! $end_date ) {
							$month = 1;
						} else {
							$month  = 3;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}
					} elseif ( $month_now <= 6 ) {

						if ( ! $end_date ) {
							$month = 4;
						} else {
							$month  = 6;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}
					} elseif ( $month_now <= 9 ) {

						if ( ! $end_date ) {
							$month = 7;
						} else {
							$month  = 9;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}
					} else {

						if ( ! $end_date ) {
							$month = 10;
						} else {
							$month  = 12;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}
					}

					break;

				case 'last_quarter':
					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if ( ! $end_date ) {
							$month = 10;
						} else {
							$year  -= 1;
							$month  = 12;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}
					} elseif ( $month_now <= 6 ) {

						if ( ! $end_date ) {
							$month = 1;
						} else {
							$month  = 3;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}
					} elseif ( $month_now <= 9 ) {

						if ( ! $end_date ) {
							$month = 4;
						} else {
							$month  = 6;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}
					} else {

						if ( ! $end_date ) {
							$month = 7;
						} else {
							$month  = 9;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}
					}

					break;

				case 'this_year':
					if ( ! $end_date ) {
						$month = 1;
					} else {
						$month  = 12;
						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'last_year':
					$year -= 1;
					if ( ! $end_date ) {
						$month = 1;
						$day   = 1;
					} else {
						$month  = 12;
						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;
			}
		} elseif ( is_numeric( $date ) ) {

			// return $date unchanged since it is a timestamp
			$this->timestamp = true;
		} elseif ( false !== strtotime( $date ) ) {

			$date  = strtotime( $date, current_time( 'timestamp' ) );
			$year  = date( 'Y', $date );
			$month = date( 'm', $date );
			$day   = date( 'd', $date );
		} else {

			return new WP_Error( 'invalid_date', __( 'Improper date provided.', 'essential-wp-real-estate' ) );
		}

		if ( false === $this->timestamp ) {
			// Create an exact timestamp
			$date = mktime( $hour, $minute, $second, $month, $day, $year );
		}

		return apply_filters( 'cl_stats_date', $date, $end_date, $this );
	}

	/**
	 * Modifies the WHERE flag for payment counts
	 *
	 * @since 1.8
	 * @return string
	 */
	public function count_where( $where = '' ) {
		// Only get payments in our date range

		$start_where = '';
		$end_where   = '';

		if ( $this->start_date ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND p.post_date >= '{$start_date}'";
		}

		if ( $this->end_date ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date = date( $format, $this->end_date );

			$end_where = " AND p.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	}

	/**
	 * Modifies the WHERE flag for payment queries
	 *
	 * @since 1.8
	 * @return string
	 */
	public function payments_where( $where = '' ) {

		global $wpdb;

		$start_where = '';
		$end_where   = '';

		if ( ! is_wp_error( $this->start_date ) ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND $wpdb->posts.post_date >= '{$start_date}'";
		}

		if ( ! is_wp_error( $this->end_date ) ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date = date( $format, $this->end_date );

			$end_where = " AND $wpdb->posts.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	}
	public function get_sales( $listing_id = 0, $start_date = false, $end_date = false, $status = 'publish' ) {
		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		if ( empty( $listing_id ) ) {
			// Global sale stats
			add_filter( 'cl_count_payments_where', array( $this, 'count_where' ) );

			if ( is_array( $status ) ) {
				$count = 0;
				foreach ( $status as $payment_status ) {
					$count += cl_count_payments()->$payment_status;
				}
			} else {
				$count = cl_count_payments()->$status;
			}

			remove_filter( 'cl_count_payments_where', array( $this, 'count_where' ) );
		} else {
			$this->timestamp = false;

			// Product specific stats
			global $cl_logs;

			add_filter( 'posts_where', array( $this, 'payments_where' ) );
			$count = $cl_logs->get_log_count( $listing_id, 'sale' );
			remove_filter( 'posts_where', array( $this, 'payments_where' ) );
		}

		return $count;
	}

	/**
	 * Retrieve earning stats
	 *
	 * @since 1.8
	 *
	 * @param int         $listing_id   The listing product to retrieve stats for. If false, gets stats for all products
	 * @param string|bool $start_date    The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param string|bool $end_date      The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param bool        $include_taxes If taxes should be included in the earnings graphs
	 *
	 * @return float|int Total amount of sales based on the passed arguments.
	 */
	public function get_earnings( $listing_id = 0, $start_date = false, $end_date = false, $include_taxes = true ) {
		global $wpdb;

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		add_filter( 'posts_where', array( $this, 'payments_where' ) );

		if ( empty( $listing_id ) ) {
			// Global earning stats
			$args = array(
				'post_type'              => 'cl_payment',
				'nopaging'               => true,
				'post_status'            => array( 'publish', 'revoked' ),
				'fields'                 => 'ids',
				'update_post_term_cache' => false,
				'suppress_filters'       => false,
				'start_date'             => $this->start_date, // These dates are not valid query args, but they are used for cache keys
				'end_date'               => $this->end_date,
				'cl_transient_type'      => 'cl_earnings', // This is not a valid query arg, but is used for cache keying
				'include_taxes'          => $include_taxes,
			);

			$args   = apply_filters( 'cl_stats_earnings_args', $args );
			$cached = get_transient( 'cl_stats_earnings' );
			$key    = md5( json_encode( $args ) );

			if ( ! isset( $cached[ $key ] ) ) {
				$sales    = get_posts( $args );
				$earnings = 0;

				if ( $sales ) {
					$sales = implode( ',', array_map( 'intval', $sales ) );

					$total_earnings = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_cl_payment_total' AND post_id IN ({$sales})" );
					$total_tax      = 0;

					if ( ! $include_taxes ) {
						$total_tax = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_cl_payment_tax' AND post_id IN ({$sales})" );
					}

					$total_earnings = apply_filters( 'cl_payment_stats_earnings_total', $total_earnings, $sales, $args );

					$earnings += ( $total_earnings - $total_tax );
				}

				// Cache the results for one hour
				$cached[ $key ] = $earnings;
				set_transient( 'cl_stats_earnings', $cached, HOUR_IN_SECONDS );
			}
		} else {
			// listing specific earning stats
			global $cl_logs, $wpdb;

			$args = array(
				'post_parent'       => $listing_id,
				'nopaging'          => true,
				'log_type'          => 'sale',
				'fields'            => 'ids',
				'suppress_filters'  => false,
				'start_date'        => $this->start_date, // These dates are not valid query args, but they are used for cache keys
				'end_date'          => $this->end_date,
				'cl_transient_type' => 'cl_earnings', // This is not a valid query arg, but is used for cache keying
				'include_taxes'     => $include_taxes,
			);

			$args   = apply_filters( 'cl_stats_earnings_args', $args );
			$cached = get_transient( 'cl_stats_earnings' );
			$key    = md5( json_encode( $args ) );

			if ( ! isset( $cached[ $key ] ) ) {
				$this->timestamp = false;
				$log_ids         = $cl_logs->get_connected_logs( $args, 'sale' );

				$earnings = 0;

				if ( $log_ids ) {
					$log_ids     = implode( ',', array_map( 'intval', $log_ids ) );
					$payment_ids = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_cl_log_payment_id' AND post_id IN ($log_ids);" );

					foreach ( $payment_ids as $payment_id ) {

						$payment = new Clpayment( $payment_id );
						$items   = cl_get_payment_meta_cart_details( $payment_id );

						foreach ( $items as $cart_key => $item ) {

							if ( $item['id'] != $listing_id ) {
								continue;
							}

							/*
							 * Prior to version 2.9.9, there was a bug with item-specific earnings.
							 *
							 * To get around it, we will use alternate calculation logic if the payment was created before version 2.9.9 was released.
							 *
							 * This is not a perfect fix but is as close as we can get to accurate reporting.
							 *
							 */
							if ( $payment->date > '2018-12-03' ) {

								$earnings += $item['price'];
							} else {

								$earnings += $item['item_price'];

								if ( ! empty( $item['fees'] ) ) {
									foreach ( $item['fees'] as $key => $fee ) {
										$earnings += $fee['amount'];
									}
								}
							}

							if ( ! $include_taxes ) {
								$earnings -= cl_get_payment_item_tax( $payment_id, $cart_key );
							}

							$earnings = apply_filters( 'cl_payment_stats_item_earnings', $earnings, $payment_id, $cart_key, $item );
						}
					}
				}

				// Cache the results for one hour
				$cached[ $key ] = $earnings;
				set_transient( 'cl_stats_earnings', $cached, HOUR_IN_SECONDS );
			}
		}

		remove_filter( 'posts_where', array( $this, 'payments_where' ) );

		$result = $cached[ $key ];

		return round( $result, WPERECCP()->common->formatting->cl_currency_decimal_filter() );
	}

	/**
	 * Get the best selling products
	 *
	 * @since 1.8
	 *
	 * @param int $number The number of results to retrieve with the default set to 10.
	 *
	 * @return array List of listing IDs that are best selling
	 */
	public function get_best_selling( $number = 10 ) {
		global $wpdb;

		$listings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id as listing_id, max(meta_value) as sales
			 FROM $wpdb->postmeta
			 WHERE meta_key='_cl_listing_sales' AND meta_value > 0
			 GROUP BY meta_value+0
			 DESC LIMIT %d;",
				$number
			)
		);

		return $listings;
	}

	/**
	 * Retrieve sales stats based on range provided (used for Reporting)
	 *
	 * @since  2.6.11
	 *
	 * @param int          $listing_id The listing product to retrieve stats for. If false, gets stats for all products
	 * @param string|bool  $start_date The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param string|bool  $end_date The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param string|array $status The sale status(es) to count. Only valid when retrieving global stats
	 *
	 * @return array Total amount of sales based on the passed arguments.
	 */
	public function get_sales_by_range( $range = 'today', $day_by_day = false, $start_date = false, $end_date = false, $status = 'publish' ) {
		global $wpdb;

		$this->setup_dates( $start_date, $end_date );

		$this->end_date = strtotime( 'midnight', $this->end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$cached = get_transient( 'cl_stats_sales' );
		$key    = md5( $range . '_' . date( 'Y-m-d', $this->start_date ) . '_' . date( 'Y-m-d', strtotime( '+1 DAY', $this->end_date ) ) );
		$sales  = isset( $cached[ $key ] ) ? $cached[ $key ] : false;

		if ( false === $sales || ! $this->is_cacheable( $range ) ) {
			if ( ! $day_by_day ) {
				$select   = "DATE_FORMAT(posts.post_date, '%%m') AS m, YEAR(posts.post_date) AS y, COUNT(DISTINCT posts.ID) as count";
				$grouping = 'YEAR(posts.post_date), MONTH(posts.post_date)';
			} else {
				if ( $range == 'today' || $range == 'yesterday' ) {
					$select   = "DATE_FORMAT(posts.post_date, '%%d') AS d, DATE_FORMAT(posts.post_date, '%%m') AS m, YEAR(posts.post_date) AS y, HOUR(posts.post_date) AS h, COUNT(DISTINCT posts.ID) as count";
					$grouping = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), HOUR(posts.post_date)';
				} else {
					$select   = "DATE_FORMAT(posts.post_date, '%%d') AS d, DATE_FORMAT(posts.post_date, '%%m') AS m, YEAR(posts.post_date) AS y, COUNT(DISTINCT posts.ID) as count";
					$grouping = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
				}
			}

			if ( $range == 'today' || $range == 'yesterday' ) {
				$grouping = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), HOUR(posts.post_date)';
			}

			$statuses = apply_filters( 'cl_payment_stats_post_statuses', array( 'publish', 'revoked' ) );
			$statuses = "'" . implode( "', '", $statuses ) . "'";

			$sales = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT $select
				 FROM {$wpdb->posts} AS posts
				 WHERE posts.post_type IN ('cl_payment')
				 AND posts.post_status IN (%s)
				 AND posts.post_date >= %s
				 AND posts.post_date < %s
				 AND posts.post_status IN ($statuses)
				 GROUP BY $grouping
				 ORDER by posts.post_date ASC",
					$status,
					date( 'Y-m-d', $this->start_date ),
					date( 'Y-m-d', strtotime( '+1 day', $this->end_date ) )
				),
				ARRAY_A
			);

			if ( $this->is_cacheable( $range ) ) {
				$cached[ $key ] = $sales;
				set_transient( 'cl_stats_sales', $cached, HOUR_IN_SECONDS );
			}
		}

		return $sales;
	}

	/**
	 * Retrieve sales stats based on range provided (used for Reporting)
	 *
	 * @since  2.7
	 *
	 * @param string|bool $start_date The starting date for which we'd like to filter our earnings stats. If false, we'll use the default start date of `this_month`
	 * @param string|bool $end_date The end date for which we'd like to filter our earnings stats. If false, we'll use the default end date of `this_month`
	 * @param bool        $include_taxes If taxes should be included in the earnings graphs
	 *
	 * @return array Total amount of earnings based on the passed arguments.
	 */
	public function get_earnings_by_range( $range = 'today', $day_by_day = false, $start_date = false, $end_date = false, $include_taxes = true ) {
		global $wpdb;

		$this->setup_dates( $start_date, $end_date );

		$this->end_date = strtotime( 'midnight', $this->end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$earnings = array();

		$cached = get_transient( 'cl_stats_earnings' );
		$key    = md5( $range . '_' . date( 'Y-m-d', $this->start_date ) . '_' . date( 'Y-m-d', strtotime( '+1 DAY', $this->end_date ) ) );
		$sales  = isset( $cached[ $key ] ) ? $cached[ $key ] : false;

		if ( false === $sales || ! $this->is_cacheable( $range ) ) {
			if ( ! $day_by_day ) {
				$select   = "DATE_FORMAT(posts.post_date, '%%m') AS m, YEAR(posts.post_date) AS y, COUNT(DISTINCT posts.ID) as count";
				$grouping = 'YEAR(posts.post_date), MONTH(posts.post_date)';
			} else {
				if ( $range == 'today' || $range == 'yesterday' ) {
					$select   = "DATE_FORMAT(posts.post_date, '%%d') AS d, DATE_FORMAT(posts.post_date, '%%m') AS m, YEAR(posts.post_date) AS y, HOUR(posts.post_date) AS h";
					$grouping = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), HOUR(posts.post_date)';
				} else {
					$select   = "DATE_FORMAT(posts.post_date, '%%d') AS d, DATE_FORMAT(posts.post_date, '%%m') AS m, YEAR(posts.post_date) AS y";
					$grouping = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
				}
			}

			if ( $range == 'today' || $range == 'yesterday' ) {
				$grouping = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), HOUR(posts.post_date)';
			}

			$statuses = apply_filters( 'cl_payment_stats_post_statuses', array( 'publish', 'revoked' ) );
			$statuses = "'" . implode( "', '", $statuses ) . "'";

			$earnings = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT SUM(meta_value) AS total, $select
				 FROM {$wpdb->posts} AS posts
				 INNER JOIN {$wpdb->postmeta} ON posts.ID = {$wpdb->postmeta}.post_ID
				 WHERE posts.post_type IN ('cl_payment')
				 AND {$wpdb->postmeta}.meta_key = '_cl_payment_total'
				 AND posts.post_date >= %s
				 AND posts.post_date < %s
				 AND posts.post_status IN ($statuses)
				 GROUP BY $grouping
				 ORDER by posts.post_date ASC",
					date( 'Y-m-d', $this->start_date ),
					date( 'Y-m-d', strtotime( '+1 day', $this->end_date ) )
				),
				ARRAY_A
			);

			if ( ! $include_taxes ) {
				$taxes = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT SUM(meta_value) AS tax, $select
					 FROM {$wpdb->posts} AS posts
					 INNER JOIN {$wpdb->postmeta} ON posts.ID = {$wpdb->postmeta}.post_ID
					 WHERE posts.post_type IN ('cl_payment')
					 AND {$wpdb->postmeta}.meta_key = '_cl_payment_tax'
					 AND posts.post_date >= %s
					 AND posts.post_date < %s
					 AND posts.post_status IN ($statuses)
					 GROUP BY $grouping
					 ORDER by posts.post_date ASC",
						date( 'Y-m-d', $this->start_date ),
						date( 'Y-m-d', strtotime( '+1 day', $this->end_date ) )
					),
					ARRAY_A
				);

				foreach ( $earnings as $key => $value ) {
					$earnings[ $key ]['total'] -= $taxes[ $key ]['tax'];
				}
			}

			return $earnings;
		}
	}

	/**
	 * Is the date range cachable
	 *
	 * @since  2.6.11
	 *
	 * @param  string $range Date range of the report
	 * @return boolean Whether the date range is allowed to be cached or not
	 */
	public function is_cacheable( $date_range = '' ) {
		if ( empty( $date_range ) ) {
			return false;
		}

		$cacheable_ranges = array(
			'today',
			'yesterday',
			'this_week',
			'last_week',
			'this_month',
			'last_month',
			'this_quarter',
			'last_quarter',
		);

		return in_array( $date_range, $cacheable_ranges );
	}
}
