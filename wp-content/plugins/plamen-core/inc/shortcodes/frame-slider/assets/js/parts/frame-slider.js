(function ($) {
	'use strict';

	qodefCore.shortcodes.plamen_core_frame_slider = {};

	$(document).ready(function () {
		qodefFrameSlider.init();
	});
	
	var qodefFrameSlider = {
		init: function () {
			this.holder = $('.qodef-frame-slider-holder');
			
			if (this.holder.length) {
				this.holder.each(function () {
					var $thisHolder = $(this);
					
					qodefFrameSlider.createSlider($thisHolder);
				});
			}
		},
		
		createSlider: function ($holder) {
			var $swiperHolder = $holder.find('.qodef-m-swiper'),
				$sliderHolder = $holder.find('.qodef-m-items'),
				$pagination = $holder.find('.swiper-pagination');
			
			var $swiper = new Swiper($swiperHolder, {
				slidesPerView: 'auto',
				centeredSlides: true,
				spaceBetween: 0,
				autoplay: true,
				loop: true,
				speed: 800,
				pagination: {
					el: $pagination,
					type: 'bullets',
					clickable: true
				},
				on: {
					init: function () {
						setTimeout(function () {
                            $sliderHolder.addClass('qodef-swiper--initialized');
                        }, 1500);
					}
				}
			});
		}
	};

	qodefCore.shortcodes.plamen_core_frame_slider.qodefFrameSlider  = qodefFrameSlider;

})(jQuery);