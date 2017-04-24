var FgMoreMenu = function () {
    var handleMoreMenu = function (menuSelector) {
        var $winWidth = $(window).width();
        $(menuSelector + ' li').removeClass("hide");
        $(menuSelector + ' > li.active').removeClass(" fg-truncate").css("max-width", "");
        $(menuSelector + ' > li.active').css("max-width", "");
        $(menuSelector + ' > li.datahideshow').addClass("hide");
        if ($winWidth >= 992) {
            var w = $(menuSelector + ' > li.active').outerWidth(true);
            var mw = $('.fg-web-main-nav').width() - 150;
            if (mw < w) {
                w = mw;
                $(menuSelector + ' > li.active').removeClass("hide").addClass("fg-truncate").css("max-width", w);
                $(menuSelector + ' .datahideshow ul > li.active').addClass("hide");
                var spanWidth = $(menuSelector + ' > li.active.fg-truncate span.badge').outerWidth(true);
                $(menuSelector + ' li.active span.fg-dev-tab-text').css({ 'max-width': w - spanWidth - 45 });
                $(menuSelector + ' .datahideshow ul').addClass("fg-more-menu-only");
            }
            else {
                $(menuSelector + ' > li.active').removeClass("hide fg-truncate").css("max-width", "");
                $(menuSelector + ' .datahideshow ul > li.active').addClass("hide");
                $(menuSelector + ' .datahideshow ul').removeClass("fg-more-menu-only");
            }
            var i = -1;
            var menuhtml = '';
            $(menuSelector + ' > li').each(function (index) {
                var hidePosition = index + 1;
                if (!($(this).hasClass('active') || $(this).hasClass('datahideshow'))) {
                    w += $(this).outerWidth(true);
                    if (mw < w) {
                        $(this).addClass("hide");
                        $(menuSelector + ' .datahideshow').removeClass("hide");
                        $(menuSelector + ' .datahideshow li:nth-child(' + hidePosition + ')').removeClass("hide");
                    }
                    else {
                        $(this).removeClass("hide");
                        $(menuSelector + ' .datahideshow .fg-dev-more-menu > li:nth-child(' + hidePosition + ')').addClass("hide");
                    }
                }
            });
            if (mw + $(menuSelector + ' > li.datahideshow').outerWidth(true) > w) {
                $(menuSelector + ' > li').removeClass("hide");
                $(menuSelector + ' > li.datahideshow').addClass("hide");
            }
            $(menuSelector).css('visibility', 'visible');
        }
    };
    return {
        initServerSide: function (menuId) {
            var menuSelector = '#' + menuId;
            $(function () {
                handleMoreMenu(menuSelector);
                $(window).resize(function () {
                    handleMoreMenu(menuSelector);
                });
                $(window).load(function () {
                    handleMoreMenu(menuSelector);
                });
            });
        }
    };
}();
