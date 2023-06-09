var G5Utils = G5Utils || {};
(function($) {
    "use strict";

    G5Utils.popup = {
        show: function (data) {
            var $body = $('body');

            $body.css('overflow', 'hidden');

            var $popupWrap = $('<div class="g5u-popup-wrap"></div>');

            $body.append($popupWrap);

            $popupWrap.on('g5u-popup-close', function () {
                var $this = $(this);
                if ($this.data('g5u-popup-type') === 'target') {
                    $this.children().appendTo($this.data('g5u-popup-target'));
                }
                $this.remove();

                $body.css('overflow', '');
            });

            G5Utils.loading.show($popupWrap);


            var $buttonClose = $('<button type="button" class="g5u-popup-close">×</button>');
            $buttonClose.on('click', function () {
                $(this).closest('.g5u-popup-wrap').trigger('g5u-popup-close');
            });
            $popupWrap.data('g5u-popup-type', data.type);

            if (data.type === 'target') {
                $(data.target).children().appendTo($popupWrap);
                $popupWrap.data('g5u-popup-target', data.target);

                $popupWrap.find('.g5u-popup-close').remove();
                $popupWrap.find('.g5u-popup-header').append($buttonClose);

                G5Utils.loading.close();

                if (typeof (data.callback) === 'function') {
                    data.callback($popupWrap);
                }
            } else if (data.type === 'inline') {
                $popupWrap.append(data.content);
                $popupWrap.find('.g5u-popup-close').remove();
                $popupWrap.find('.g5u-popup-header').append($buttonClose);

                G5Utils.loading.close();
                if (typeof (data.callback) === 'function') {
                    data.callback($popupWrap);
                }

            } else {
                $.ajax({
                    type: typeof (data.method) === 'undefined' ? 'GET' : data.method,
                    url: data.src,
                    success: function (res) {
                        $popupWrap.append(res);
                        $popupWrap.find('.g5u-popup-close').remove();
                        $popupWrap.find('.g5u-popup-header').append($buttonClose);

                        $popupWrap.find('.g5u-navigation a').on('click', function (event) {
                            event.preventDefault();
                            var $this = $(this);
                            if ($this.closest('.g5u-navigation-disabled').length === 0) {

                            }
                        });

                        if (typeof (data.callback) === 'function') {
                            data.callback($popupWrap);
                        }
                    },
                    error: function () {
                        $body.css('overflow', '');
                        $popupWrap.remove();
                    },
                    complete: function () {
                        G5Utils.loading.close();
                    }
                });
            }
        },
        close: function () {
            $('.g5u-popup-wrap').last().trigger('g5u-popup-close');
        }
    };

    G5Utils.loading = {
        $_loading: null,
        show: function ($wrap) {
            if (G5Utils.loading.$_loading === null) {
                G5Utils.loading.$_loading = $('<div class="g5u-loading"><span></span></div>');
            }
            G5Utils.loading.$_loading.appendTo($wrap);
        },
        close: function () {
            if (G5Utils.loading.$_loading !== null) {
                G5Utils.loading.$_loading.remove();
            }
        }
    };

    G5Utils.loadingButton = {
        show: function ($btn) {
            var loadingType = $btn.data('g5u-lb') === undefined ? 'g5u-lb-left': 'g5u-lb-' . $btn.data('g5u-lb');

            $btn.append('<div class="g5u-lb"></div>');
            $btn.addClass(loadingType).addClass('g5u-lb-running');
        },
        hide: function ($btn) {
            var loadingType = $btn.data('g5u-lb') === undefined ? 'g5u-lb-left': 'g5u-lb-' . $btn.data('g5u-lb');
            $btn.removeClass(loadingType).removeClass('g5u-lb-running');
            $btn.find('.g5u-lb').remove();
        }
    };


})(jQuery);