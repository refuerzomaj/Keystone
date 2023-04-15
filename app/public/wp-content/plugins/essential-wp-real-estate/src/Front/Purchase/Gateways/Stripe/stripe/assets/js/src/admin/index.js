/* global $, cl_stripe_admin */

/**
 * Internal dependencies.
 */
import './../../../css/src/admin.scss';
import './settings/index.js';

let testModeCheckbox;
let testModeToggleNotice;

$( document ).ready( function() {
	testModeCheckbox = document.getElementById( 'cl_settings[test_mode]' );
	if ( testModeCheckbox ) {
		testModeToggleNotice = document.getElementById( 'cl_settings[stripe_connect_test_mode_toggle_notice]' );
		CL_Stripe_Connect_Scripts.init();
	}

	// Toggle API keys.
	$( '.cls-api-key-toggle button' ).on( 'click', function( event ) {
		event.preventDefault();

		$( '.cls-api-key-toggle, .cls-api-key-row' )
			.toggleClass( 'cl-hidden' );
	} );
} );

const CL_Stripe_Connect_Scripts = {

	init() {
		this.listeners();
	},

	listeners() {
		const self = this;

		testModeCheckbox.addEventListener( 'change', function() {
			// Don't run these events if Stripe is not enabled.
			if ( ! cl_stripe_admin.stripe_enabled ) {
				return;
			}

			if ( this.checked ) {
				if ( 'false' === cl_stripe_admin.test_key_exists ) {
					self.showNotice( testModeToggleNotice, 'warning' );
					self.addHiddenMarker();
				} else {
					self.hideNotice( testModeToggleNotice );
					const hiddenMarker = document.getElementById( 'cl-test-mode-toggled' );
					if ( hiddenMarker ) {
						hiddenMarker.parentNode.removeChild( hiddenMarker );
					}
				}
			}

			if ( ! this.checked ) {
				if ( 'false' === cl_stripe_admin.live_key_exists ) {
					self.showNotice( testModeToggleNotice, 'warning' );
					self.addHiddenMarker();
				} else {
					self.hideNotice( testModeToggleNotice );
					const hiddenMarker = document.getElementById( 'cl-test-mode-toggled' );
					if ( hiddenMarker ) {
						hiddenMarker.parentNode.removeChild( hiddenMarker );
					}
				}
			}
		} );
	},

	addHiddenMarker() {
		const submit = document.getElementById( 'submit' );

		if ( ! submit ) {
			return;
		}

		submit.parentNode.insertAdjacentHTML( 'beforeend', '<input type="hidden" class="cl-hidden" id="cl-test-mode-toggled" name="cl-test-mode-toggled" />' );
	},

	showNotice( element = false, type = 'error' ) {
		if ( ! element ) {
			return;
		}

		if ( typeof element !== 'object' ) {
			return;
		}

		element.className = 'notice notice-' + type;
	},

	hideNotice( element = false ) {
		if ( ! element ) {
			return;
		}

		if ( typeof element !== 'object' ) {
			return;
		}

		element.className = 'cl-hidden';
	},
};
