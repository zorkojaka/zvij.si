(function ($) {
    "use strict";

    qodefCore.shortcodes.plamen_core_testimonials_list = {};

    $(document).ready(function () {
        qodefTestimonials.init();
    });

    var qodefTestimonials = {
        init: function () {
            var holder = $('.qodef-testimonials-list');

            if (holder.length) {
                holder.each(function () {
                    var thisHolder = $(this),
                        swiper = thisHolder.find('.qodef-m-testimonials-swiper'),
                        swiperSlide = thisHolder.find('.qodef-e'),
                        maxHeight = 100,
                        sliderOptions = typeof swiper.data('options') !== 'undefined' ? swiper.data('options') : {},
                        spaceBetween = sliderOptions.spaceBetween !== undefined && sliderOptions.spaceBetween !== '' ? sliderOptions.spaceBetween : 0,
                        loop = sliderOptions.loop !== undefined && sliderOptions.loop !== '' ? sliderOptions.loop : true,
                        autoplay = sliderOptions.autoplay !== undefined && sliderOptions.autoplay !== '' ? sliderOptions.autoplay : true,
                        speed = sliderOptions.speed !== undefined && sliderOptions.speed !== '' ? parseInt(sliderOptions.speed, 10) : 3000,
                        speedAnimation = sliderOptions.speedAnimation !== undefined && sliderOptions.speedAnimation !== '' ? parseInt(sliderOptions.speedAnimation, 10) : 1500,
                        pagination = thisHolder.find('.swiper-pagination');
                    swiperSlide.each(function() {
                        if ($(this).height() > maxHeight) {
                            maxHeight = $(this).height();
                        }

                    });

                    swiper.css('max-height', maxHeight);

                    if (autoplay !== false && speed !== 3000) {
                        autoplay = {
                            delay: speed
                        };
                    }

                    var slider = new Swiper(swiper, {
                        slidesPerView: 1,
                        direction: 'vertical',
                        spaceBetween: spaceBetween,
                        loop: loop,
                        speed: speedAnimation,
                        speedAnimation: speed,
                        pagination: {
                            el: pagination,
                            type: 'bullets',
                            clickable: 'true'
                        },
                        autoplay: autoplay,
                        on: {
                            slideChange: function () {

                                setTimeout(function () {
                                    changeActiveImage();
                                }, 100);
                            }
                        }
                    });

                    var initActiveImage = function () {
                        var activeItem = thisHolder.find('.swiper-slide-active'),
                            itemIndex = activeItem.data('slide-index'),
                            activeImage = $('.qodef-testimonial-image[data-slide-index=' + itemIndex + ']');

                        activeImage.addClass('active');
                    };

                    var changeActiveImage = function () {

                        var activeItem = thisHolder.find('.swiper-slide-active, .swiper-slide-duplicate-active'),
                            itemIndex = activeItem.data('slide-index'),
                            activeImage = $('.qodef-testimonial-image.active'),
                            nextActiveImage = $('.qodef-testimonial-image[data-slide-index=' + itemIndex + ']');

                        activeImage.removeClass('active');
                        nextActiveImage.addClass('active');
                    };

                    initActiveImage();
                })
            }
        },
	}

    qodefCore.shortcodes.plamen_core_testimonials_list.qodefTestimonials = qodefTestimonials;

})(jQuery);