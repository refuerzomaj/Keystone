(function ($) {
	"use strict";

	var ERED_ADMIN = {
		_ajaxRequesting: false,
		$trEdit: null,

		init: function () {
			this.openPopup();
			this.saveEmail();
			this.deleteEmail();
		},

		openPopup: function () {
			var mfOptions = {
				type: 'inline',
				closeOnBgClick: false,
				closeBtnInside: true,
				mainClass: 'mfp-zoom-in',
				midClick: true,
				callbacks: {
					open: function () {
						var $this = $(this.currItem.el),
							$tr = $this.closest('tr'),
							$tdEmail = $tr.find('.td-email'),
							$tdName = $tr.find('.td-name');

						ERED_ADMIN.$trEdit = $tr;

						$('#ered-change-info-form').find('[name="id"]').val($this.data('id'));
						$('#ered-change-info-form').find('[name="name"]').val($tdName.text());
						$('#ered-change-info-form').find('[name="email"]').val($tdEmail.text());
					}
				}
			};
			$('.ered-change-email').magnificPopup(mfOptions);
		},
		saveEmail: function () {
			$('#ered-change-info-form').on('submit', function (event) {
				event.preventDefault();

				if (ERED_ADMIN._ajaxRequesting) {
					return;
				}

				ERED_ADMIN._ajaxRequesting = true;

				var self = this,
					formData = new FormData(this);

				self._ladda = Ladda.create($(this).find('[type="submit"]')[0]);
				self._ladda.start();

				$.ajax({
					type: 'POST',
					url: this.action,
					data: formData,
					processData: false,
					contentType: false,
					success: function (res) {
						if (res.success) {
							ERED_ADMIN.$trEdit.find('.td-name').text(res.data['name']);
							ERED_ADMIN.$trEdit.find('.td-email').text(res.data['email']);

							var trEdit = ERED_ADMIN.$trEdit;
							trEdit.addClass('highlight-color');
							setTimeout(function () {
								trEdit.removeClass('highlight-color');
							}, 500);

							$(self).closest('#ered_edit_info_popup').find(' > .mfp-close').trigger('click');

						}
						else {
							alert(res.data);
						}
					},
					error: function () {},
					complete: function () {
						ERED_ADMIN._ajaxRequesting = false;
						if (self._ladda) {
							self._ladda.stop();
						}
					}
				});

				return false;
			});
		},
		deleteEmail: function() {
			$('.ered-delete-email').on('click', function (event) {
				event.preventDefault();
				if (ERED_ADMIN._ajaxRequesting) {
					return;
				}

				ERED_ADMIN._ajaxRequesting = true;
				if (confirm('Are your sure to delete?')) {
					var $this = $(this);
					var _ladda = Ladda.create(this);
					_ladda.start();

					$.ajax({
						type: 'POST',
						url: $this.data('url'),
						data: {
							id: $this.data('id'),
							ered_delete_email_nonce: $this.data('nonce')
						},
						success: function (res) {
							if (res.success) {
								$this.closest('tr').fadeOut(function () {
									$(this).remove();
								});
							}
							else {
								alert(res.data);
							}
						},
						complete: function () {
							ERED_ADMIN._ajaxRequesting = false;
							if (_ladda) {
								_ladda.stop();
							}
						}
					});
				}
			});
		}
	};
	$(document).ready(function () {
		ERED_ADMIN.init();
	});
})(jQuery);