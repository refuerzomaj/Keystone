/**
 * Internal dependencies
 */
// eslint-disable @wordpress/dependency-group
import { paymentMethods } from 'frontend/components/payment-methods';
// eslint-enable @wordpress/dependency-group

import { paymentForm } from './payment-form.js';

export function setup() {
	if ( ! document.getElementById( 'cls-update-payment-method' ) ) {
		return;
	}

	paymentForm();
	paymentMethods();
}
