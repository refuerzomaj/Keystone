/* global Stripe, cl_stripe_vars */

/**
 * Internal dependencies
 */
import './../../../css/src/frontend.scss';
import { domReady, apiRequest, generateNotice } from 'utils';

import {
	setupCheckout,
	setupProfile,
	setupPaymentHistory,
	setupBuyNow,
	setuplistingPRB,
	setupCheckoutPRB,
} from 'frontend/payment-forms';

import {
	paymentMethods,
} from 'frontend/components/payment-methods';

import {
	mountCardElement,
	createPaymentForm as createElementsPaymentForm,
	getBillingDetails,
	getPaymentMethod,
	confirm as confirmIntent,
	handle as handleIntent,
	retrieve as retrieveIntent,
} from 'frontend/stripe-elements';
// eslint-enable @wordpress/dependency-group

( () => {
	try {
		window.clStripe = new Stripe( cl_stripe_vars.publishable_key );

		// Alias some functionality for external plugins.
		window.clStripe._plugin = {
			domReady,
			apiRequest,
			generateNotice,
			mountCardElement,
			createElementsPaymentForm,
			getBillingDetails,
			getPaymentMethod,
			confirmIntent,
			handleIntent,
			retrieveIntent,
			paymentMethods,
		};

		// Setup frontend components when DOM is ready.
		domReady(
			setupCheckout,
			setupProfile,
			setupPaymentHistory,
			setupBuyNow,
			setuplistingPRB,
			setupCheckoutPRB,
		);
	} catch ( error ) {
		alert( error.message );
	}
} )();
