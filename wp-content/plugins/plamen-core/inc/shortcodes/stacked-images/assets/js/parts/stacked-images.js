(function ($) {
	"use strict";

	qodefCore.shortcodes.plamen_core_stacked_images = {};

	$(document).ready(function () {
		qodefStackedImages.init();
	});
	
	var qodefStackedImages = {
		init: function () {
			this.images = $('.qodef-stacked-images');
			
			if (this.images.length) {
				this.images.each(function () {
					var $thisImage = $(this);
					
					qodefStackedImages.animate($thisImage);
				});
			}
		},
		animate: function ($image) {
			
			var itemImage = $image.find('.qodef-m-images');
			$image.animate({opacity: 1}, 300);
			
			setTimeout(function () {
				$image.appear(function () {
					itemImage.addClass('qodef--appeared');
				});
			}, 200);
			
		}
	};

	qodefCore.shortcodes.plamen_core_stacked_images.qodefStackedImages = qodefStackedImages;

})(jQuery);