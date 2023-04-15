window.CL_Checkout = (function($) {
	'use strict';

	var $body,
		$form,
		$cl_cart_amount,
		before_discount,
		$checkout_form_wrap;

	function init() {
		$body = $(document.body);
		$form = $("#cl_purchase_form");
		$cl_cart_amount = $('.cl_cart_amount');
		before_discount = $cl_cart_amount.text();
		$checkout_form_wrap = $('#cl_checkout_form_wrap');

		$body.on('cl_gateway_loaded', function( e ) {
			cl_format_card_number( $form );
		});

		$body.on('keyup change', '.cl-do-validate .card-number', function() {
			cl_validate_card( $(this) );
		});

		$body.on('blur change', '.card-name', function() {
			var name_field = $(this);

			name_field.validateCreditCard(function(result) {
				if(result.card_type != null) {
					name_field.removeClass('valid').addClass('error');
					$('#cl-purchase-button').attr('disabled', 'disabled');
				} else {
					name_field.removeClass('error').addClass('valid');
					$('#cl-purchase-button').removeAttr('disabled');
				}
			});
		});

		// Make sure a gateway is selected
		$body.on('submit', '#cl_payment_mode', function() {
			var gateway = $('#cl-gateway option:selected').val();
			if( gateway == 0 ) {
				alert( cl_global_vars.no_gateway );
				return false;
			}
		});

		// Add a class to the currently selected gateway on click
		$body.on('click', '#cl_payment_mode_select input', function() {
			$('#cl_payment_mode_select label.cl-gateway-option-selected').removeClass( 'cl-gateway-option-selected' );
			$('#cl_payment_mode_select input:checked').parent().addClass( 'cl-gateway-option-selected' );
		});

		// Validate and apply a discount
		$checkout_form_wrap.on('click', '.cl-apply-discount', apply_discount);

		// Prevent the checkout form from submitting when hitting Enter in the discount field
		$checkout_form_wrap.on('keypress', '#cl-discount', function (event) {
			if (event.keyCode == '13') {
				return false;
			}
		});

		// Apply the discount when hitting Enter in the discount field instead
		$checkout_form_wrap.on('keyup', '#cl-discount', function (event) {
			if (event.keyCode == '13') {
				$checkout_form_wrap.find('.cl-apply-discount').trigger('click');
			}
		});

		// Remove a discount
		$body.on('click', '.cl_discount_remove', remove_discount);

		// When discount link is clicked, hide the link, then show the discount input and set focus.
		$body.on('click', '.cl_discount_link', function(e) {
			e.preventDefault();
			$('.cl_discount_link').parent().hide();
			$('#cl-discount-code-wrap').show().find('#cl-discount').focus();
		});

		// Hide / show discount fields for browsers without javascript enabled
		$body.find('#cl-discount-code-wrap').hide();
		$body.find('#cl_show_discount').show();

		// Update the checkout when item quantities are updated
		$body.on('change', '.cl-item-quantity', update_item_quantities);

		$body.on('click', '.cl-amazon-logout #Logout', function(e) {
			e.preventDefault();
			amazon.Login.logout();
			window.location = cl_amazon.checkoutUri;
		});

	}

	function cl_validate_card(field) {
		var card_field = field;
		card_field.validateCreditCard(function(result) {
			var $card_type = $('.card-type');

			if(result.card_type == null) {
				$card_type.removeClass().addClass('off card-type');
				card_field.removeClass('valid');
				card_field.addClass('error');
			} else {
				$card_type.removeClass('off');
				$card_type.addClass( result.card_type.name );
				if (result.length_valid && result.luhn_valid) {
					card_field.addClass('valid');
					card_field.removeClass('error');
				} else {
					card_field.removeClass('valid');
					card_field.addClass('error');
				}
			}
		});
	}

	function cl_format_card_number( form ) {
		var card_number = form.find('.card-number'),
			card_cvc = form.find('.card-cvc'),
			card_expiry = form.find('.card-expiry');

		if ( card_number.length && 'function' === typeof card_number.payment ) {
			card_number.payment('formatCardNumber');
			card_cvc.payment('formatCardCVC');
			card_expiry.payment('formatCardExpiry');
		}
	}

	function apply_discount(event) {

		event.preventDefault();

		var discount_code = $( '#cl-discount' ).val(),
			cl_discount_loader = $( '#cl-discount-loader' ),
			required_inputs = $( '#cl_cc_address .cl-input, #cl_cc_address .cl-select' ).filter( '[required]' );

		if (discount_code == '' || discount_code == cl_global_vars.enter_discount ) {
			return false;
		}

		var postData = {
			action: 'cl_apply_discount',
			code: discount_code,
			form: $( '#cl_purchase_form' ).serialize()
		};

		$('#cl-discount-error-wrap').html('').hide();
		cl_discount_loader.show();

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: cl_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (discount_response) {
				if( discount_response ) {
					if (discount_response.msg == 'valid') {
						$('.cl_cart_discount').html(discount_response.html);
						$('.cl_cart_discount_row').show();

						$( '.cl_cart_amount' ).each( function() {
							// Format discounted amount for display.
							$( this ).text( discount_response.total );
							// Set data attribute to new (unformatted) discounted amount.'
							$( this ).data( 'total', discount_response.total_plain );
						} );

						$('#cl-discount', $checkout_form_wrap ).val('');

						recalculate_taxes();

						if( '0.00' == discount_response.total_plain ) {

							$('#cl_cc_fields,#cl_cc_address,#cl_payment_mode_select').slideUp();
							required_inputs.prop( 'required', false );
							$('input[name="cl-gateway"]').val( 'manual' );

						} else {

							required_inputs.prop( 'required', true );
							$('#cl_cc_fields,#cl_cc_address').slideDown();

						}

						$body.trigger('cl_discount_applied', [ discount_response ]);

					} else {
						$('#cl-discount-error-wrap').html( '<span class="cl_error">' + discount_response.msg + '</span>' );
						$('#cl-discount-error-wrap').show();
						$body.trigger('cl_discount_invalid', [ discount_response ]);
					}
				} else {
					if ( window.console && window.console.log ) {
						console.log( discount_response );
					}
					$body.trigger('cl_discount_failed', [ discount_response ]);
				}
				cl_discount_loader.hide();
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
	};

	function remove_discount(event) {

		var $this = $(this), postData = {
			action: 'cl_remove_discount',
			code: $this.data('code')
		};

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: cl_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (discount_response) {

				var zero = '0' + cl_global_vars.decimal_separator + cl_global_vars.number_of_decimal;

				$('.cl_cart_amount').each(function() {
					if( cl_global_vars.currency_sign + zero == $(this).text() || zero + cl_global_vars.currency_sign == $(this).text() ) {
						// We're removing a 100% discount code so we need to force the payment gateway to reload
						window.location.reload();
					}

					// Format discounted amount for display.
					$( this ).text( discount_response.total );
					// Set data attribute to new (unformatted) discounted amount.'
					$( this ).data( 'total', discount_response.total_plain );
				});

				$('.cl_cart_discount').html(discount_response.html);

				if( ! discount_response.discounts ) {

					$('.cl_cart_discount_row').hide();

				}

				recalculate_taxes();

				$('#cl_cc_fields,#cl_cc_address').slideDown();

				$body.trigger('cl_discount_removed', [ discount_response ]);

			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
	}

	function update_item_quantities(event) {

		var $this = $(this),
			quantity = $this.val(),
			key = $this.data('key'),
			listing_id = $this.closest('.cl_cart_item').data('listing-id'),
			options = $this.parent().find('input[name="cl-cart-listing-' + key + '-options"]').val();

		var cl_cc_address = $('#cl_cc_address');
		var billing_country = cl_cc_address.find('#billing_country').val(),
			card_state      = cl_cc_address.find('#card_state').val();

		var postData = {
			action: 'cl_update_quantity',
			quantity: quantity,
			listing_id: listing_id,
			options: options,
			billing_country: billing_country,
			card_state: card_state,
		};

		//cl_discount_loader.show();

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: cl_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (response) {

				$('.cl_cart_subtotal_amount').each(function() {
					$(this).text(response.subtotal);
				});

				$('.cl_cart_tax_amount').each(function() {
					$(this).text(response.taxes);
				});

				$('.cl_cart_amount').each(function() {
					var total = response.total;
					var subtotal = response.subtotal;

					$(this).text(total);

					var float_total = parseFloat(total.replace(/[^0-9\.-]+/g,""));
					var float_subtotal = parseFloat(subtotal.replace(/[^0-9\.-]+/g,""));

					$(this).data('total', float_total);
					$(this).data('subtotal', float_subtotal);

					$body.trigger('cl_quantity_updated', [ response ]);
				});
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
	}

	// Expose some functions or variables to window.CL_Checkout object
	return {
		'init': init,
		'recalculate_taxes': recalculate_taxes
	}

})(window.jQuery);

// init on document.ready
window.jQuery(document).ready(CL_Checkout.init);

var ajax_tax_count = 0;
function recalculate_taxes(state) {

	if( '1' != cl_global_vars.taxes_enabled )
		return; // Taxes not enabled

	var $cl_cc_address = jQuery('#cl_cc_address');

	if( ! state ) {
		state = $cl_cc_address.find('#card_state').val();
	}

	var postData = {
		action: 'cl_recalculate_taxes',
		billing_country: $cl_cc_address.find('#billing_country').val(),
		state: state,
		card_zip: $cl_cc_address.find('input[name=card_zip]').val(),
		nonce: jQuery('#cl-checkout-address-fields-nonce').val(),
	};

	jQuery('#cl_purchase_submit [type=submit]').after('<span class="cl-loading-ajax cl-recalculate-taxes-loading cl-loading"></span>');

	var current_ajax_count = ++ajax_tax_count;
	jQuery.ajax({
		type: "POST",
		data: postData,
		dataType: "json",
		url: cl_global_vars.ajaxurl,
		xhrFields: {
			withCredentials: true
		},
		success: function (tax_response) {
			// Only update tax info if this response is the most recent ajax call.
			// Avoids bug with form autocomplete firing multiple ajax calls at the same time and not
			// being able to predict the call response order.
			if (current_ajax_count === ajax_tax_count) {
				if ( tax_response.html ) {
					jQuery( '#cl_checkout_cart_form' ).replaceWith( tax_response.html );
				}
				jQuery('.cl_cart_amount').html(tax_response.total);
				var tax_data = new Object();
				tax_data.postdata = postData;
				tax_data.response = tax_response;
				jQuery('body').trigger('cl_taxes_recalculated', [ tax_data ]);
			}
			jQuery('.cl-recalculate-taxes-loading').remove();
		}
	}).fail(function (data) {
		if ( window.console && window.console.log ) {
			console.log( data );
			if (current_ajax_count === ajax_tax_count) {
				jQuery('body').trigger('cl_taxes_recalculated', [ tax_data ]);
			}
		}
	});
}
