/* global jQuery, cl_scripts, cl_stripe_vars */

/**
 * Internal dependencies
 */
import { forEach, domReady, apiRequest } from 'utils';
import { Modal, paymentMethods } from 'frontend/components';
import { paymentForm } from 'frontend/payment-forms/checkout'

/**
 * Adds a listing to the Cart.
 *
 * @param {number} listingId listing ID.
 * @param {number} priceId listing Price ID.
 * @param {number} quantity listing quantity.
 * @param {string} nonce Nonce token.
 * @param {HTMLElement} addToCartForm Add to cart form.
 *
 * @return {Promise}
 */
function addToCart( listingId, priceId, quantity, nonce, addToCartForm ) {
	const data = {
		listing_id: listingId,
		price_id: priceId,
		quantity: quantity,
		nonce,
		post_data: jQuery( addToCartForm ).serialize(),
	};

	return apiRequest( 'cls_add_to_cart', data );
}

/**
 * Empties the Cart.
 *
 * @return {Promise}
 */
function emptyCart() {
	return apiRequest( 'cls_empty_cart' );
}

/**
 * Displays the Buy Now modal.
 *
 * @param {Object} args
 * @param {number} args.listingId listing ID.
 * @param {number} args.priceId listing Price ID.
 * @param {number} args.quantity listing quantity.
 * @param {string} args.nonce Nonce token.
 * @param {HTMLElement} args.addToCartForm Add to cart form.
 */
function buyNowModal( args ) {
	const modalContent = document.querySelector( '#cls-buy-now-modal-content' );
	const modalLoading = '<span class="cl-loading-ajax cl-loading"></span>';

	// Show modal.
	Modal.open( 'cls-buy-now', {
		/**
		 * Adds the item to the Cart when opening.
		 */
		onShow() {
			modalContent.innerHTML = modalLoading;

			const {
				listingId,
				priceId,
				quantity,
				nonce,
				addToCartForm,
			} = args;

			addToCart(
				listingId,
				priceId,
				quantity,
				nonce,
				addToCartForm
			)
				.then( ( { checkout } ) => {
					// Show Checkout HTML.
					modalContent.innerHTML = checkout;

					// Reinitialize core JS.
					window.CL_Checkout.init();

					const totalEl = document.querySelector( '#cls-buy-now-modal-content .cl_cart_amount' );
					const total = parseFloat( totalEl.dataset.total );

					// Reinitialize Stripe JS if a payment is required.
					if ( total > 0 ) {
						paymentForm();
						paymentMethods();
					}
				} )
				.fail( ( { message } ) => {
					// Show error message.
					document.querySelector( '#cls-buy-now-modal-content' ).innerHTML = message;
				} );
		},
		/**
		 * Empties Cart on close.
		 */
		onClose() {
			emptyCart();
		}
	} );
}

// DOM ready.
export function setup() {

	// Find all "Buy Now" links on the page.
	forEach( document.querySelectorAll( '.cls-buy-now' ), ( el ) => {

		if ( el.classList.contains( 'cl-free-listing' ) ) {
			return;
		}

		/**
		 * Launches "Buy Now" modal when clicking "Buy Now" link.
		 *
		 * @param {Object} e Click event.
		 */
		el.addEventListener( 'click', ( e ) => {
			const { listingId, nonce } = e.currentTarget.dataset;

			// Stop other actions if a listing ID is found.
			if ( ! listingId ) {
				return;
			}

			e.preventDefault();
			e.stopImmediatePropagation();

			// Gather listing information.
			let priceId = 0;
			let quantity = 1;

			const addToCartForm = e.currentTarget.closest(
				'.cl_listing_purchase_form'
			);

			// Price ID.
			const priceIdEl = addToCartForm.querySelector(
				`.cl_price_option_${listingId}:checked`
			);

			if ( priceIdEl ) {
				priceId = priceIdEl.value;
			}

			// Quantity.
			const quantityEl = addToCartForm.querySelector(
				'input[name="cl_listing_quantity"]'
			);

			if ( quantityEl ) {
				quantity = quantityEl.value;
			}

			buyNowModal( {
				listingId,
				priceId,
				quantity,
				nonce,
				addToCartForm
			} );
		} );

	} );

	/**
	 * Replaces submit button text after validation errors.
	 *
	 * If there are no other items in the cart the core javascript will replace
	 * the button text with the value for a $0 cart (usually "Free listing")
	 * because the script variables were constructed when nothing was in the cart.
	 */
	jQuery( document.body ).on( 'cl_checkout_error', () => {
		const submitButtonEl = document.querySelector(
			'#cls-buy-now #cl-purchase-button'
		);

		if ( ! submitButtonEl ) {
			return;
		}

		const { i18n: { completePurchase } } = cl_stripe_vars;

		const amountEl = document.querySelector( '.cl_cart_amount' );
		const { total, totalCurrency } = amountEl.dataset;

		if ( '0' === total ) {
			return;
		}

		// For some reason a delay is needed to override the value set by
		setTimeout( () => {
			submitButtonEl.value = `${ totalCurrency } - ${ completePurchase }`;
		}, 10 );
	} );
}
