(function ($) {
    "use strict";

    $(document).ready(function () {
        qodefPlamenSpinner.init();
    });

    $(window).on('elementor/frontend/init', function () {
        var isEditMode = Boolean(elementorFrontend.isEditMode());
        if (isEditMode) {
            qodefPlamenSpinner.removeSpinner();
        }
    });

    var qodefPlamenSpinner = {
        init: function () {
            var $holder = $('#qodef-page-spinner.qodef-layout--plamen');

            qodefPlamenSpinner.windowLoaded = false;

            if ($holder.length) {
                if (qodef.body.is('.elementor_library-template-default')) {
                    qodefPlamenSpinner.removeSpinner();
                } else {
                    qodefPlamenSpinner.animateSpinner($holder);
                }
            }
        },
        removeSpinner: function () {
            this.holder = $('#qodef-page-spinner.qodef-layout--plamen');
            if (this.holder.length) {
                this.holder.remove();
            }
        },
        animateSpinner: function ($holder) {
            var revHolder = $('#qodef-landing-rev-holder');
            qodefCore.qodefScroll.disable();

            var animateElements = function () {
                var $spinnerFrame = $holder.find('.qodef-m-plamen-frame');

                $spinnerFrame.addClass('qodef-animate-elements');
            };

            var fadeOutElements = function () {
                var $spinnerFrame = $holder.find('.qodef-m-plamen-frame');

                $spinnerFrame.css('opacity', '0');
            };

            // Start Spinner Animation
            setTimeout(function () {
                animateElements();
            }, 750);

            // End Spinner Animation
            setTimeout(function () {
                if (qodefPlamenSpinner.windowLoaded) {
                    fadeOutElements();

                    setTimeout(function () {
                        qodefCore.qodefScroll.enable();

                        if (revHolder.length) {
                            revHolder.find('rs-module').revstart();
                        }

                        qodefPlamenSpinner.fadeOutLoader($holder);
                    }, 300);

                    setTimeout(function () {
                        $holder.remove();
                    }, 1500);
                } else {
                    $(window).on('load', function () {
                        fadeOutElements();

                        setTimeout(function () {
                            qodefCore.qodefScroll.enable();

                            if (revHolder.length) {
                                revHolder.find('rs-module').revstart();
                            }

                            qodefPlamenSpinner.fadeOutLoader($holder);
                        }, 300);

                        setTimeout(function () {
                            $holder.remove();
                        }, 1500);
                    });
                }
            }, 7500);

            // Allow end on click after initial load
            setTimeout(function () {
                $(window).on('click', function () {
                    if (qodefPlamenSpinner.windowLoaded) {
                        fadeOutElements();

                        setTimeout(function () {
                            qodefCore.qodefScroll.enable();

                            if (revHolder.length) {
                                revHolder.find('rs-module').revstart();
                            }

                            qodefPlamenSpinner.fadeOutLoader($holder);
                        }, 300);

                        setTimeout(function () {
                            $holder.remove();
                        }, 500);
                    }
                });
            }, 3000);

            $(window).on('load', function () {
                qodefPlamenSpinner.windowLoaded = true;
            });
        },
        fadeOutLoader: function ($holder, speed, delay, easing) {
            speed = speed ? speed : 500;
            delay = delay ? delay : 0;
            easing = easing ? easing : 'swing';

            if ($holder.length) {
                $holder.delay(delay).fadeOut(speed, easing);
                $(window).on('bind', 'pageshow', function (event) {
                    if (event.originalEvent.persisted) {
                        $holder.fadeOut(speed, easing);
                    }
                });
            }
        }
    };

})(jQuery);