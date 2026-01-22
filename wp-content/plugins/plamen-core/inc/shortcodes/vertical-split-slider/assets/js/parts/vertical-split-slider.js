(function ($) {
    "use strict";

	qodefCore.shortcodes.plamen_vertical_split_slider = {};

    $(document).ready(function () {
        qodefVerticalSplitSlider.init();
    });

    var qodefVerticalSplitSlider = {
        init: function () {
            var $holder = $('.qodef-vertical-split-slider'),
                breakpoint = qodefVerticalSplitSlider.getBreakpoint($holder),
                initialHeaderStyle = '';

            if (qodefCore.body.hasClass('qodef-header--light')) {
                initialHeaderStyle = 'light';
            } else if (qodefCore.body.hasClass('qodef-header--dark')) {
                initialHeaderStyle = 'dark';
            }

            if ($holder.length) {
                $holder.multiscroll({
                    navigation: true,
                    navigationPosition: 'right',
                    afterRender: function () {
                        qodefCore.body.addClass('qodef-vertical-split-slider--initialized');
                        qodefVerticalSplitSlider.bodyClassHandler($('.ms-left .ms-section:first-child').data('header-skin'), initialHeaderStyle);
                    },
                    onLeave: function (index, nextIndex) {
                        qodefVerticalSplitSlider.bodyClassHandler($($('.ms-left .ms-section')[nextIndex - 1]).data('header-skin'), initialHeaderStyle);
                    }
                });

                $holder.height(qodefCore.windowHeight);
                qodefVerticalSplitSlider.buildAndDestroy(breakpoint);

                $(window).resize(function () {
                    qodefVerticalSplitSlider.buildAndDestroy(breakpoint);
                });
            }
        },
        getBreakpoint: function ($holder) {
            if ($holder.hasClass('qodef-disable-below--768')) {
                return 768;
            } else {
                return 1024;
            }
        },
        buildAndDestroy: function (breakpoint) {
            if (qodefCore.windowWidth <= breakpoint) {
                $.fn.multiscroll.destroy();
                $('html, body').css('overflow', 'initial');
                qodefCore.body.removeClass('qodef-vertical-split-slider--initialized');
            } else {
                $.fn.multiscroll.build();
                qodefCore.body.addClass('qodef-vertical-split-slider--initialized');
            }
        },
        bodyClassHandler: function (slideHeaderStyle, initialHeaderStyle) {
            if (slideHeaderStyle !== undefined && slideHeaderStyle !== '') {
                qodefCore.body.removeClass('qodef-header--light qodef-header--dark').addClass('qodef-header--' + slideHeaderStyle);
            } else if (initialHeaderStyle !== '') {
                qodefCore.body.removeClass('qodef-header--light qodef-header--dark').addClass('qodef-header--' + slideHeaderStyle);
            } else {
                qodefCore.body.removeClass('qodef-header--light qodef-header--dark');
            }
        }
    };

	qodefCore.shortcodes.plamen_vertical_split_slider.qodefVerticalSplitSlider = qodefVerticalSplitSlider;

})(jQuery);