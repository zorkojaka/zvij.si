(function ($) {
	"use strict";

	qodefCore.shortcodes.plamen_core_google_map = {};

	$(document).ready(function () {
		qodefGoogleMap.init();
	});
	
	var qodefGoogleMap = {
		init: function () {
			this.holder = $('.qodef-google-map');
			
			if (this.holder.length) {
				this.holder.each(function () {
					if (qodefCore.qodefGoogleMap !== undefined) {
						qodefCore.qodefGoogleMap.initMap($(this).find('.qodef-m-map'));
					}
				});
			}
		}
	};
	qodefCore.shortcodes.plamen_core_google_map.qodefGoogleMap  = qodefGoogleMap;
})(jQuery);