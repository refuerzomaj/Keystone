<?php
/**
 * Reporting: Stripe
 *
 * @package CL_Stripe
 * @since   2.6
 */

/**
 * Class CL_Stripe_Reports
 *
 * Do nothing in 2.8.0
 * The reports have not collected data since 2.7.0 and provide no tangible value.
 *
 * @since 2.6
 * @deprecated 2.8.0
 */
class CL_Stripe_Reports {

	public function __construct() {
		_doing_it_wrong(
			__CLASS__,
			__( 'Stripe-specific reports have been removed.', 'essential-wp-real-estate' ),
			'2.8.0'
		);
	}
}
