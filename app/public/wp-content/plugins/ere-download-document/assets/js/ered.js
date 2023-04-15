(function ($) {
	"use strict";

	var ERED = {
		_ajaxRequesting: false,
		init: function () {
			this.replaceDocumentUrl();
			this.openPopup();
			this.submitForm();
		},
		replaceDocumentUrl: function () {
			$('.property-attachments .media-info > a, .g5ere__property-attachments .media-info > a').each(function () {
				var $this = $(this);
				$this.data('ered-link', $this.attr('href'));
				$this.attr('href', '#ered_download_popup');
			});
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
						$('.ered-download-popup-wrapper input[name="url"]').val($(this.currItem.el).data('ered-link'));
					}
				}
			};
			$('.property-attachments .media-info > a, .g5ere__property-attachments .media-info > a').magnificPopup(mfOptions);
		},
		submitForm: function () {
			// Submit Form
			$('.ered-download-popup-wrapper form').on('submit', function (event) {
				event.preventDefault();
				if (ERED._ajaxRequesting) {
					return;
				}

				ERED._ajaxRequesting = true;

				var self = this,
					formData = new FormData(this),
					$button = $(this).find('button[type="submit"]');

				self._ladda = Ladda.create($button[0]);
				self._ladda.start();

				$.ajax({
					type: 'POST',
					enctype: 'multipart/form-data',
					url: self.action,
					data: formData,
					processData: false,
					contentType: false,
					success: function (res) {
						if (res.success) {
							ERED.downloadFile(res.data);
							$(self).closest('#ered_download_popup').find(' > .mfp-close').trigger('click');
						}
						else {
							alert(res.data);
						}
					},
					error: function () {},
					complete: function () {
						ERED._ajaxRequesting = false;
						if (self._ladda) {
							self._ladda.stop();
						}
					}
				});

				return false;
			});
		},
		downloadFile: function (urlToSend) {
			var req = new XMLHttpRequest();
			req.open("GET", urlToSend, true);
			req.responseType = "blob";
			var fileName = urlToSend.split('/').pop();
			req.onload = function (event) {
				var blob = req.response;
				var link=document.createElement('a');
				link.href=window.URL.createObjectURL(blob);
				link.download=fileName;
				link.click();
			};

			req.send();
		}
	};

	$(document).ready(function () {
		ERED.init();
	});
})(jQuery);