(function ($) {
	"use strict";
	
	$(document).ready(function () {
		qodefAgeVerificationModal.init();
	});
	
	var qodefAgeVerificationModal = {
		init: function () {
			this.holder = $('#qodef-age-verification-modal');
			
			if (this.holder.length) {
				var $preventHolder = this.holder.find('.qodef-m-content-prevent');
				
				if ($preventHolder.length) {
					var $preventYesButton = $preventHolder.find('.qodef-prevent--yes');
					
					$preventYesButton.on('click', function () {
						var cname = 'disabledAgeVerification';
						var cvalue = 'Yes';
						var exdays = 7;
						var d = new Date();
						
						d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
						var expires = "expires=" + d.toUTCString();
						document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
						
						qodefAgeVerificationModal.handleClassAndScroll('remove');
					});
				}
			}
		},
		
		handleClassAndScroll: function (option) {
			if (option === 'remove') {
				qodef.body.removeClass('qodef-age-verification--opened');
				qodefCore.qodefScroll.enable();
			}
			if (option === 'add') {
				qodef.body.addClass('qodef-age-verification--opened');
				qodefCore.qodefScroll.disable();
			}
		},
	};
	
})(jQuery);