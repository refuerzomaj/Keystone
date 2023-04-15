/**
 * Internal dependencies
 */
import { domReady } from 'utils';

/**
 * Hides "Save Changes" button if showing the special settings placeholder.
 */
domReady( () => {
	const containerEl = document.querySelector( '.cls-requirements-not-met' );

	if ( ! containerEl ) {
		return;
	}

	// Hide "Save Changes" button.
	document.querySelector( '.cl-settings-wrap .submit' ).style.display = 'none';
} );

/**
 * Moves "Payment Gateways" notice under Stripe.
 * Disables/unchecks the checkbox.
 */
domReady( () => {
	const noticeEl = document.getElementById( 'cls-payment-gateways-stripe-unmet-requirements' );

	if ( ! noticeEl ) {
		return;
	}

	const stripeLabel = document.querySelector( 'label[for="cl_settings[gateways][stripe]"]' );
	stripeLabel.parentNode.insertBefore( noticeEl, stripeLabel.nextSibling );

	const stripeCheck = document.getElementById( 'cl_settings[gateways][stripe]' );
	stripeCheck.disabled = true;
	stripeCheck.checked = false;

	noticeEl.insertBefore( stripeCheck, noticeEl.querySelector( 'p' ) );
	noticeEl.insertBefore( stripeLabel, noticeEl.querySelector( 'p' ) );
} );
