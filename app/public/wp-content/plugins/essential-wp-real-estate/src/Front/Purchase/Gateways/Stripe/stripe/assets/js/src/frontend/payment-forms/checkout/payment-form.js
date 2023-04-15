/* global $, cl_stripe_vars, cl_global_vars */

/**
 * Internal dependencies
 */
import {
	createPaymentForm as createElementsPaymentForm,
	getPaymentMethod,
	capture as captureIntent,
	handle as handleIntent,
} from 'frontend/stripe-elements'; // eslint-disable-line @wordpress/dependency-group

import { apiRequest, generateNotice } from 'utils'; // eslint-disable-line @wordpress/dependency-group

/**
 * Binds Payment submission functionality.
 *
 * Resets before rebinding to avoid duplicate events
 * during gateway switching.
 */
export function paymentForm() {
	// Mount Elements.
	createElementsPaymentForm( window.clStripe.elements() );

	// Bind form submission.
	// Needs to be jQuery since that is what core submits against.
	$( '#cl_purchase_form' ).off( 'submit', onSubmit );
	$( '#cl_purchase_form' ).on( 'submit', onSubmit );

	// SUPER ghetto way to watch for core form validation because no events are in place.
	// Called after the purchase form is submitted (via `click` or `submit`)
	$( document ).off( 'ajaxSuccess', watchInitialValidation );
	$( document ).on( 'ajaxSuccess', watchInitialValidation );
}

/**
 * Processes Stripe gateway-specific functionality after core AJAX validation has run.
 */
async function onSubmitDelay() {
	try {
		// Form data to send to intent requests.
		let formData = $( '#cl_purchase_form' ).serialize();

		// Retrieve or create a PaymentMethod.
		const paymentMethod = await getPaymentMethod( document.getElementById( 'cl_purchase_form' ), window.clStripe.cardElement );

		// Run the modified `_cls_process_purchase_form` and create an Intent.
		const {
			intent: initialIntent,
			nonce: refreshedNonce
		} = await processForm( paymentMethod.id, paymentMethod.exists );

		// Update existing nonce value in DOM form data in case data is retrieved
		// again directly from the DOM.
		$( '#cl-process-checkout-nonce' ).val( refreshedNonce );

		// Handle any actions required by the Intent State Machine (3D Secure, etc).
		const handledIntent = await handleIntent(
			initialIntent,
			{
				form_data: formData += `&cl-process-checkout-nonce=${ refreshedNonce }`,
			}
		);

		// Create an payment record.
		const { intent, nonce } = await createPayment( handledIntent );

		// Capture any unpcaptured intents.
		const finalIntent = await captureIntent(
			intent,
			{},
			nonce
		);

		// Attempt to transition payment status and redirect.
		// @todo Maybe confirm payment status as well? Would need to generate a custom
		// response because the private Clpayment properties are not available.
		if (
			( 'succeeded' === finalIntent.status ) ||
			( 'canceled' === finalIntent.status && 'abandoned' === finalIntent.cancellation_reason )
		) {
			await completePayment( finalIntent, nonce );

			window.location.replace( cl_stripe_vars.successPageUri );
		} else {
			window.location.replace( cl_stripe_vars.failurePageUri );
		}
	} catch ( error ) {
		handleException( error );
		enableForm();
	}
}

/**
 * Processes the purchase form.
 *
 * Generates purchase data for the current session and
 * uses the PaymentMethod to generate an Intent based on data.
 *
 * @param {string} paymentMethodId PaymentMethod ID.
 * @param {Bool} paymentMethodExists If the PaymentMethod has already been attached to a customer.
 * @return {Promise} jQuery Promise.
 */
export function processForm( paymentMethodId, paymentMethodExists ) {
	return apiRequest( 'cls_process_purchase_form', {
		// Send available form data.
		form_data: $( '#cl_purchase_form' ).serialize(),
		payment_method_id: paymentMethodId,
		payment_method_exists: paymentMethodExists,
	} );
}

/**
 * Complete a Payment .
 *
 * @param {object} intent Intent.
 * @return {Promise} jQuery Promise.
 */
export function createPayment( intent ) {
	const paymentForm = $( '#cl_purchase_form' );
	let formData = paymentForm.serialize();

	// Attempt to find the Checkout nonce directly.
	if ( paymentForm.length === 0 ) {
		const nonce = $( '#cl-process-checkout-nonce' ).val();
		formData = `cl-process-checkout-nonce=${ nonce }`
	}

	return apiRequest( 'cls_create_payment', {
		form_data: formData,
		intent,
	} );
}

/**
 * Complete a Payment .
 *
 * @param {object} intent Intent.
 * @param {string} refreshedNonce A refreshed nonce that might be needed if the
 *                                user logged in.
 * @return {Promise} jQuery Promise.
 */
export function completePayment( intent, refreshedNonce ) {
	const paymentForm = $( '#cl_purchase_form' );
	let formData = paymentForm.serialize();

	// Attempt to find the Checkout nonce directly.
	if ( paymentForm.length === 0 ) {
		const nonce = $( '#cl-process-checkout-nonce' ).val();
		formData = `cl-process-checkout-nonce=${ nonce }`;
	}

	// Add the refreshed nonce if available.
	if ( refreshedNonce ) {
		formData += `&cl-process-checkout-nonce=${ refreshedNonce }`;
	}

	return apiRequest( 'cls_complete_payment', {
		form_data: formData,
		intent,
	} );
}


/**
 * Listen for initial core validation.
 *
 * @param {Object} event Event.
 * @param {Object} xhr AJAX request.
 * @param {Object} options Request options.
 */
function watchInitialValidation( event, xhr, options ) {
	if ( ! options || ! options.data || ! xhr ) {
		return;
	}

	if (
		options.data.includes( 'action=cl_process_checkout' ) &&
		options.data.includes( 'cl-gateway=stripe' ) &&
		( xhr.responseText && 'success' === xhr.responseText.trim() )
	) {
		return onSubmitDelay();
	}
};

/**
 * core listens to a a `click` event on the Checkout form submit button.
 *
 * This submit event handler captures true submissions and triggers a `click`
 * event so core can take over as normoal.
 *
 * @param {Object} event submit Event.
 */
function onSubmit( event ) {
	// Ensure we are dealing with the Stripe gateway.
	if ( ! (
		// Stripe is selected gateway and total is larger than 0.
		$( 'input[name="cl-gateway"]' ).val() === 'stripe'	&&
		$( '.cl_cart_total .cl_cart_amount' ).data( 'total' ) > 0
	) ) {
		return;
	}

	// While this function is tied to the submit event, block submission.
	event.preventDefault();

	// Simulate a mouse click on the Submit button.
	//
	// If the form is submitted via the "Enter" key we need to ensure the core
	// validation is run.
	//
	// When that is run and then the form is resubmitted
	// the click event won't do anything because the button will be disabled.
	$( '#cl_purchase_form #cl_purchase_submit [type=submit]' ).trigger( 'click' );
}

/**
 * Enables the Checkout form for further submissions.
 */
function enableForm() {
	// Update button text.
	document.querySelector( '#cl_purchase_form #cl_purchase_submit [type=submit]' ).value = cl_global_vars.complete_purchase;

	// Enable form.
	$( '.cl-loading-ajax' ).remove();
	$( '.cl_errors' ).remove();
	$( '.cl-error' ).hide();
	$( '#cl-purchase-button' ).attr( 'disabled', false );
}



function handleException( error ) {
	let { code, message } = error;
	const { elementsOptions: { i18n: { errorMessages } } } = window.cl_stripe_vars;

	if ( ! message ) {
		message = cl_stripe_vars.generic_error;
	}

	const localizedMessage = code && errorMessages[code] ? errorMessages[code] : message;

	const notice = generateNotice( localizedMessage );

	// Hide previous messages.
	// @todo These should all be in a container, but that's not how core works.
	$( '.cl-stripe-alert' ).remove();
	$( cl_global_vars.checkout_error_anchor ).before( notice );
	$( document.body ).trigger( 'cl_checkout_error', [ error ] );

	if ( window.console && error.responseText ) {
		window.console.error( error.responseText );
	}
}
