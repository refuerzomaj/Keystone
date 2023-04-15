<?php
namespace  Essential\Restate\Front\Purchase\Cart;

use Essential\Restate\Traitval\Traitval;

class Fees {

	use Traitval;

	public function __construct() {
		 add_filter( 'cl_payment_meta', array( $this, 'record_fees' ), 10, 2 );
	}

	/**
	 * Adds a new Fee
	 *
	 * @since 1.5
	 *
	 * @param array $args Fee arguments
	 *
	 * @return array The fees.
	 */
	public function add_fee( $args = array() ) {

		// Backwards compatibility with pre 2.0
		if ( func_num_args() > 1 ) {

			$args   = func_get_args();
			$amount = $args[0];
			$label  = isset( $args[1] ) ? $args[1] : '';
			$id     = isset( $args[2] ) ? $args[2] : '';
			$type   = 'fee';

			$args = array(
				'amount'     => $amount,
				'label'      => $label,
				'id'         => $id,
				'type'       => $type,
				'no_tax'     => false,
				'listing_id' => 0,
				'price_id'   => null,
			);
		} else {

			$defaults = array(
				'amount'     => 0,
				'label'      => '',
				'id'         => '',
				'no_tax'     => false,
				'type'       => 'fee',
				'listing_id' => 0,
				'price_id'   => null,
			);

			$args = wp_parse_args( $args, $defaults );

			if ( $args['type'] != 'fee' && $args['type'] != 'item' ) {
				$args['type'] = 'fee';
			}
		}

		// If the fee is for an "item" but we passed in a listing id
		if ( 'item' === $args['type'] && ! empty( $args['listing_id'] ) ) {
			unset( $args['listing_id'] );
			unset( $args['price_id'] );
		}

		if ( ! empty( $args['listing_id'] ) ) {
			$options = isset( $args['price_id'] ) ? array( 'price_id' => $args['price_id'] ) : array();
			if ( ! cl_item_in_cart( $args['listing_id'], $options ) ) {
				return false;
			}
		}

		$fees = $this->get_fees( 'all' );

		// Determine the key
		$key = empty( $args['id'] ) ? sanitize_key( $args['label'] ) : sanitize_key( $args['id'] );

		// Remove the unneeded id key
		unset( $args['id'] );

		// Sanitize the amount
		$args['amount'] = WPERECCP()->common->formatting->cl_sanitize_amount( $args['amount'] );

		// Force the amount to have the proper number of decimal places.
		$args['amount'] = number_format( (float) $args['amount'], cl_currency_decimal_filter(), '.', '' );

		// Force no_tax to true if the amount is negative
		if ( $args['amount'] < 0 ) {
			$args['no_tax'] = true;
		}

		// Set the fee
		$fees[ $key ] = apply_filters( 'cl_fees_add_fee', $args, $this );

		// Allow 3rd parties to process the fees before storing them in the session
		$fees = apply_filters( 'cl_fees_set_fees', $fees, $this );

		// Update fees
		WPERECCP()->front->session->set( 'cl_cart_fees', $fees );

		do_action( 'cl_post_add_fee', $fees, $key, $args );

		return $fees;
	}

	/**
	 * Remove an Existing Fee
	 *
	 * @since 1.5
	 * @param string $id Fee ID
	 * @return array Remaining fees
	 */
	public function remove_fee( $id = '' ) {

		$fees = $this->get_fees( 'all' );

		if ( isset( $fees[ $id ] ) ) {
			unset( $fees[ $id ] );
			WPERECCP()->front->session->set( 'cl_cart_fees', $fees );

			do_action( 'cl_post_remove_fee', $fees, $id );
		}

		return $fees;
	}

	/**
	 * Check if any fees are present
	 *
	 * @since 1.5
	 * @param string $type Fee type, "fee" or "item"
	 * @return bool True if there are fees, false otherwise
	 */
	public function has_fees( $type = 'fee' ) {

		if ( 'all' == $type || 'fee' == $type ) {
			if ( ! WPERECCP()->front->cart->cl_get_cart_contents() ) {
				$type = 'item';
			}
		}

		$fees = $this->get_fees( $type );
		return ! empty( $fees ) && is_array( $fees );
	}

	/**
	 * Retrieve all active fees
	 *
	 * @since 1.5
	 * @param string $type Fee type, "fee" or "item"
	 * @param int    $listing_id The listing ID whose fees to retrieve
	 * @param int    $price_id The variable price ID whose fees to retrieve
	 * @return array|bool List of fees when available, false when there are no fees
	 */
	public function get_fees( $type = 'fee', $listing_id = 0, $price_id = null ) {
		$fees = WPERECCP()->front->session->get( 'cl_cart_fees' );

		if ( WPERECCP()->front->cart->is_empty() ) {
			// We can only get item type fees when the cart is empty
			$type = 'item';
		}

		if ( ! empty( $fees ) && ! empty( $type ) && 'all' !== $type ) {
			foreach ( $fees as $key => $fee ) {
				if ( ! empty( $fee['type'] ) && $type != $fee['type'] ) {
					unset( $fees[ $key ] );
				}
			}
		}

		if ( ! empty( $fees ) && ! empty( $listing_id ) ) {
			// Remove fees that don't belong to the specified listing
			$applied_fees = array();
			foreach ( $fees as $key => $fee ) {

				if ( empty( $fee['listing_id'] ) || (int) $listing_id !== (int) $fee['listing_id'] ) {
					unset( $fees[ $key ] );
				}

				$fee_hash = md5( $fee['amount'] . $fee['label'] . $fee['type'] );

				if ( in_array( $fee_hash, $applied_fees ) ) {
					unset( $fees[ $key ] );
				}

				$applied_fees[] = $fee_hash;
			}
		}

		// Now that we've removed any fees that are for other listings, lets also remove any fees that don't match this price id
		if ( ! empty( $fees ) && ! empty( $listing_id ) && ! is_null( $price_id ) ) {
			// Remove fees that don't belong to the specified listing AND Price ID
			foreach ( $fees as $key => $fee ) {
				if ( is_null( $fee['price_id'] ) ) {
					continue;
				}

				if ( (int) $price_id !== (int) $fee['price_id'] ) {
					unset( $fees[ $key ] );
				}
			}
		}

		if ( ! empty( $fees ) ) {
			// Remove fees that belong to a specific listing but are not in the cart
			foreach ( $fees as $key => $fee ) {
				if ( empty( $fee['listing_id'] ) ) {
					continue;
				}

				if ( ! cl_item_in_cart( $fee['listing_id'] ) ) {
					unset( $fees[ $key ] );
				}
			}
		}

		// Allow 3rd parties to process the fees before returning them
		return apply_filters( 'cl_fees_get_fees', ! empty( $fees ) ? $fees : array(), $this );
	}

	/**
	 * Retrieve a specific fee
	 *
	 * @since 1.5
	 *
	 * @param string $id ID of the fee to get
	 * @return array|bool The fee array when available, false otherwise
	 */
	public function get_fee( $id = '' ) {
		$fees = $this->get_fees( 'all' );

		if ( ! isset( $fees[ $id ] ) ) {
			return false;
		}

		return $fees[ $id ];
	}

	/**
	 * Calculate the total fee amount for a specific fee type
	 *
	 * Can be negative
	 *
	 * @since 2.0
	 * @param string $type Fee type, "fee" or "item"
	 * @return float Total fee amount
	 */
	public function type_total( $type = 'fee' ) {
		$fees  = $this->get_fees( $type );
		$total = (float) 0.00;

		if ( $this->has_fees( $type ) ) {
			foreach ( $fees as $fee ) {
				$total += WPERECCP()->common->formatting->cl_sanitize_amount( $fee['amount'] );
			}
		}

		return WPERECCP()->common->formatting->cl_sanitize_amount( $total );
	}

	/**
	 * Calculate the total fee amount
	 *
	 * Can be negative
	 *
	 * @since 1.5
	 * @param int $listing_id The listing ID whose fees to retrieve
	 * @return float Total fee amount
	 */
	public function total( $listing_id = 0 ) {
		$fees  = $this->get_fees( 'all', $listing_id );
		$total = (float) 0.00;

		if ( $this->has_fees( 'all' ) ) {
			foreach ( $fees as $fee ) {
				$total += WPERECCP()->common->formatting->cl_sanitize_amount( $fee['amount'] );
			}
		}

		return WPERECCP()->common->formatting->cl_sanitize_amount( $total );
	}

	/**
	 * Stores the fees in the payment meta
	 *
	 * @since 1.5
	 * @param array $payment_meta The meta data to store with the payment
	 * @param array $payment_data The info sent from process-purchase.php
	 * @return array Return the payment meta with the fees added
	 */
	public function record_fees( $payment_meta, $payment_data ) {
		if ( $this->has_fees( 'all' ) ) {

			$payment_meta['fees'] = $this->get_fees( 'all' );

			// Only clear fees from session when status is not pending
			if ( ! empty( $payment_data['status'] ) && 'pending' !== strtolower( $payment_data['status'] ) ) {

				WPERECCP()->front->session->set( 'cl_cart_fees', null );
			}
		}

		return $payment_meta;
	}
}
