/* global $, cl_scripts */

/**
 * Internal dependencies
 */
// eslint-disable @wordpress/dependency-group
import { paymentMethods } from 'frontend/components';
// eslint-enable @wordpress/dependency-group

import { paymentForm } from './payment-form.js';

export * from './payment-form.js';

export function setup() {
	if ( '1' !== cl_scripts.is_checkout ) {
		return;
	}

	// Initial load for single gateway.
	const singleGateway = document.querySelector( 'input[name="cl-gateway"]' );

	if ( singleGateway && 'stripe' === singleGateway.value ) {
		paymentForm();
		paymentMethods();
	}

	// Gateway switch.
	$( document.body ).on( 'cl_gateway_loaded', ( e, gateway ) => {
		if ( 'stripe' !== gateway ) {
			return;
		}

		paymentForm();
		paymentMethods();
	} );
}
