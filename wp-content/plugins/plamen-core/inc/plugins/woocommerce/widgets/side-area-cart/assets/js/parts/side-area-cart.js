(function ($) {
	"use strict";
	
	$(document).ready(function () {
		qodefSideAreaCart.init();
	});
	
	var qodefSideAreaCart = {
		init: function () {
			var $holder = $('.qodef-woo-side-area-cart');
			
			if ($holder.length) {
				$holder.each(function () {
					var $thisHolder = $(this);
					
					if (qodefCore.windowWidth > 680) {
						qodefSideAreaCart.trigger($thisHolder);
						
						qodefCore.body.on('added_to_cart', function () {
							qodefSideAreaCart.trigger($thisHolder);
						});
					}
				});
			}
		},
		trigger: function ($holder) {
			var $opener = $holder.find('.qodef-m-opener'),
				$close = $holder.find('.qodef-m-close'),
				$items = $holder.find('.qodef-m-items');
			
			// Open Side Area
			$opener.on('click', function (e) {
				e.preventDefault();
				
				if (!$holder.hasClass('qodef--opened')) {
					qodefSideAreaCart.openSideArea($holder);
					
					$(document).keyup(function (e) {
						if (e.keyCode === 27) {
							qodefSideAreaCart.closeSideArea($holder);
						}
					});
				} else {
					qodefSideAreaCart.closeSideArea($holder);
				}
			});
			
			$close.on('click', function (e) {
				e.preventDefault();
				
				qodefSideAreaCart.closeSideArea($holder);
			});
			
			if ($items.length && typeof qodefCore.qodefPerfectScrollbar === 'object') {
				qodefCore.qodefPerfectScrollbar.init($items);
			}
		},
		openSideArea: function ($holder) {
			qodefCore.qodefScroll.disable();
			
			$holder.addClass('qodef--opened');
			$('#qodef-page-wrapper').prepend('<div class="qodef-woo-side-area-cart-cover"/>');
			
			$('.qodef-woo-side-area-cart-cover').on('click', function (e) {
				e.preventDefault();
				
				qodefSideAreaCart.closeSideArea($holder);
			});
		},
		closeSideArea: function ($holder) {
			if ($holder.hasClass('qodef--opened')) {
				qodefCore.qodefScroll.enable();
				
				$holder.removeClass('qodef--opened');
				$('.qodef-woo-side-area-cart-cover').remove();
			}
		}
	};
	
})(jQuery);
