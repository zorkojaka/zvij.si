(function ($) {
    "use strict";
	
	qodefCore.shortcodes.plamen_core_item_showcase = {};
	
	$(document).ready(function () {
		qodefItemShowcaseList.init();
	});
	
	var qodefItemShowcaseList = {
		init: function () {
			this.holder = $('.qodef-item-showcase');
			
			if (this.holder.length) {
				this.holder.each(function () {
					var $thisHolder = $(this);
					
					$thisHolder.appear(function(){
						$thisHolder.addClass('qodef--init');
					}, {accX: 0, accY: -100});
				});
			}
		}
	};
	qodefCore.shortcodes.plamen_core_item_showcase.qodefItemShowcaseList = qodefItemShowcaseList;

})(jQuery);