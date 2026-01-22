(function($) {
    "use strict";

    $(document).ready(function(){
        qodefSearchFullscreen.init();
    });

	var qodefSearchFullscreen = {
	    init: function(){
            var $searchOpener = $('a.qodef-search-opener'),
                $searchHolder = $('.qodef-fullscreen-search-holder'),
                $searchClose = $searchHolder.find('.qodef-m-close');

            if ($searchOpener.length && $searchHolder.length) {
                $searchOpener.on('click', function (e) {
                    e.preventDefault();
                    if(qodefCore.body.hasClass('qodef-fullscreen-search--opened')){
                        qodefSearchFullscreen.closeFullscreen($searchHolder);
                    }else{
                        qodefSearchFullscreen.openFullscreen($searchHolder);
                    }
                });
                $searchClose.on('click', function (e) {
                    e.preventDefault();
                    qodefSearchFullscreen.closeFullscreen($searchHolder);
                });

                //Close on escape
                $(document).keyup(function (e) {
                    if (e.keyCode === 27 && qodefCore.body.hasClass('qodef-fullscreen-search--opened')) { //KeyCode for ESC button is 27
                        qodefSearchFullscreen.closeFullscreen($searchHolder);
                    }
                });
            }
        },
        openFullscreen: function($searchHolder){
            qodefCore.body.removeClass('qodef-fullscreen-search--fadeout');
            qodefCore.body.addClass('qodef-fullscreen-search--opened qodef-fullscreen-search--fadein');

            setTimeout(function () {
                $searchHolder.find('.qodef-m-form-field').focus();
            }, 900);

            qodefCore.qodefScroll.disable();

            qodefSearchFullscreen.closeFullscreenCursor($searchHolder);
        },
        closeFullscreen: function($searchHolder){
            qodefCore.body.removeClass('qodef-fullscreen-search--opened qodef-fullscreen-search--fadein');
            qodefCore.body.addClass('qodef-fullscreen-search--fadeout');

            setTimeout(function () {
                $searchHolder.find('.qodef-m-form-field').val('');
                $searchHolder.find('.qodef-m-form-field').blur();
                qodefCore.body.removeClass('qodef-fullscreen-search--fadeout');
            }, 300);

            qodefCore.qodefScroll.enable();
        },
        closeFullscreenCursor: function($searchHolder) {
            var fullscreenOverlay = $('.qodef-fullscreen-search-overlay'),
                closeIcon = $('.qodef-m-close span').get(0).innerHTML;

            if ( fullscreenOverlay.length ) {
                var posX, posY;
                qodef.body.append('<div class="qodef-close-cursor">' + closeIcon + '</div>');
                var closeCursor = $('.qodef-close-cursor');
                qodef.window.on('mousemove', function(e) {
                    posX = e.clientX;
                    posY = e.clientY;

                    closeCursor.css({
                        'top': posY,
                        'left': posX
                    });
                });

                fullscreenOverlay.on('mouseenter', function() {
                    closeCursor.css('opacity', 1);
                }).on('mouseout', function() {
                    closeCursor.css('opacity', 0);
                });

                fullscreenOverlay.on('click', function (e) {
                    e.preventDefault();
                    closeCursor.remove();
                    qodefSearchFullscreen.closeFullscreen($searchHolder);
                });
            }
        }
    };

})(jQuery);
