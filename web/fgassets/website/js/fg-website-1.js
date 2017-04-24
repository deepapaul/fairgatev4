/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
/**
 * FgWebsiteTheme class
 *
 */
var FgWebsiteObj;
var _thisFgWebsiteTheme;
var FgWebsiteTheme = (function () {
    function FgWebsiteTheme() {
        //object new FgWebsite() declared in layout.html
        FgWebsiteObj = fgwebsite;
        _thisFgWebsiteTheme = this;
    }
    /**
     * build header template
     */
    FgWebsiteTheme.prototype.headerNav = function (path, parentDivId) {
        //handle class for header image - display
        if (_.size(publicConfig.header_options.header_label)) {
            var label = publicConfig.header_options.header_label;
            if (typeof label.TM_MOBILE_SCREEN_LOGO != 'undefined' && typeof label.TM_SHRINKED_LOGO != 'undefined' && typeof label.TM_DEFAULT_LOGO != 'undefined') {
                //all 3 present
                var classO = 'hidden-xs hidden-sm';
                var classS = 'hidden-lg hidden-md hidden-xs';
                var classM = 'hidden-lg hidden-md hidden-sm';
            }
            else if (typeof label.TM_SHRINKED_LOGO != 'undefined' && typeof label.TM_DEFAULT_LOGO != 'undefined') {
                //original shrinked
                var classO = 'hidden-sm hidden-xs';
                var classS = 'visible-sm visible-xs ';
                var classM = '';
            }
            else if (typeof label.TM_MOBILE_SCREEN_LOGO != 'undefined' && typeof label.TM_DEFAULT_LOGO != 'undefined') {
                //original mobile
                var classO = ' hidden-xs visible-sm visible-md visible-lg';
                var classS = '';
                var classM = 'visible-xs';
            }
            else if (typeof label.TM_SHRINKED_LOGO != 'undefined' && typeof label.TM_MOBILE_SCREEN_LOGO != 'undefined') {
                //shrinked mobile
                var classO = '';
                var classS = 'visible-lg visible-md visible-sm hidden-xs';
                var classM = 'hidden-lg hidden-md hidden-sm visible-xs';
            }
        }
        $.ajax({
            type: "GET",
            url: path,
            async: false,
            success: function (jsonData) {
                jsonData.classO = classO;
                jsonData.classS = classS;
                jsonData.classM = classM;
                FgWebsiteObj.activeHeaderMenu = jsonData.menu;
                FgWebsiteObj.navigationMenuPath = jsonData.navPath;
                FgWebsiteObj.contactLang = jsonData.lang;
                FgWebsiteObj.clubLang = jsonData.clubLang;
                var htmlheader = FgWebsiteObj.buildHeaderNavigation(jsonData.data);
                var htmlFinal = FGTemplate.bind(FgWebsiteObj.templateHeaderNav, jsonData);
                $('#' + parentDivId).prepend(htmlFinal);
                $('#fg-web-header-menus').html(htmlheader);
                _thisFgWebsiteTheme.navigationActions();
                FgWebsiteObj.navigationActivation();
                //to make footer sticky
                //to handle header loading
                setTimeout(function () {
                    _thisFgWebsiteTheme.makeFooterSticky();
                    $(".fg-web-main-content").removeClass('fg-visible-hidden');
                }, 1000);
            }
        });
    };
    /**
     * default logo (after scrolling down , changed to shrinked)
     */
    FgWebsiteTheme.prototype.onScrollEvent = function () {
        $(window).scroll(function () {
            if (publicConfig.header_options.type == 'sticky') {
                var windowWidth = $(window).width();
                if (windowWidth > 768) {
                    if ($(this).scrollTop() > 200) {
                        if (_.size(publicConfig.header_options.header_label)) {
                            var label = publicConfig.header_options.header_label;
                            if (typeof label.TM_SHRINKED_LOGO != 'undefined' && typeof label.TM_DEFAULT_LOGO != 'undefined') {
                                $('.fg-original').addClass('hide');
                                $('.fg-shrinked').addClass('show');
                            }
                        }
                        $('.fg-web-top-nav-wrapper:not(.no-content)').slideUp(300);
                        $('.fg-web-page-header').addClass('fg-header-shrinked');
                    }
                    else if ($(this).scrollTop() < 75) {
                        $('.fg-original').removeClass('hide');
                        $('.fg-shrinked').removeClass('show');
                        $('.fg-web-page-header').removeClass('fg-header-shrinked');
                        $('.fg-web-top-nav-wrapper:not(.no-content)').slideDown(300);
                    }
                }
                else {
                    $('.fg-original').removeClass('hide');
                    $('.fg-shrinked').removeClass('show');
                    $('.fg-web-page-header').removeClass('fg-header-shrinked');
                }
            }
            if ($(this).scrollTop() > 200) {
                $('.fg-web-go-top').addClass('show');
                $('.fg-web-go-top').removeClass('hide');
            }
            else {
                //let FgwebsitepageObj = new Fgwebsitepage();
                //FgwebsitepageObj.makeFooterSticky();
                $('.fg-web-go-top').addClass('hide');
                $('.fg-web-go-top').removeClass('show');
            }
            setTimeout(function () {
                _thisFgWebsiteTheme.makeFooterSticky();
            }, 400);
        });
    };
    /**
     * make footer sticky
     */
    FgWebsiteTheme.prototype.makeFooterSticky = function () {
        var hasAdmin = $('body').hasClass('fg-has-admin-nav'); // checking it page has admin nav
        var hasFixedheader = $('body').hasClass('fg-header-sticky'); // checking page has fixed header
        var headerHeight = $('.fg-web-page-header').outerHeight();
        var contentHeight = $('.fg-web-main-content').outerHeight();
        var footerHeight = $('.fg-web-page-footer').outerHeight();
        var winHeight = $(window).height();
        var winWidth = $(window).width();
        if (winWidth > 767) {
            var totHeight = headerHeight + contentHeight + footerHeight;
            //if (winHeight > totHeight) {
            var remaingHeight = winHeight - headerHeight - footerHeight;
            if (hasAdmin) {
                remaingHeight = remaingHeight - 46;
            }
            remaingHeight = (remaingHeight < 300) ? 300 : remaingHeight;
            $('.fg-web-main-content').css('min-height', remaingHeight);
        }
        if (hasFixedheader) {
            var totHeaderHeight = headerHeight;
            if (hasAdmin) {
                totHeaderHeight = totHeaderHeight + 46;
            }
            $('body').css({ 'padding-top': totHeaderHeight });
        }
        $('body').removeClass('no-scroll');
    };
    /**
     * build additional navigation
     */
    FgWebsiteTheme.prototype.callAdditionalNav = function (templateId) {
        var htmlNav = FGTemplate.bind('template_top_lang_navigation', '');
        $('#' + templateId).prepend(htmlNav);
        $.ajax({
            type: "GET",
            url: additionalmenuPath,
            async: false,
            success: function (additionalmenu) {
                if ((additionalmenu.additionalNav.length == 0) && (typeof additionalmenu.langNav === "undefined")) {
                    $('.fg-web-top-nav-wrapper').addClass('no-content').hide();
                }
                if (additionalmenu) {
                    if (additionalmenu) {
                        var htmlFinal = FGTemplate.bind(_this.templateAdditionNav, { additionalNav: additionalmenu.additionalNav, activeMenu: additionalmenu.currentmenu });
                        $('#fg-div-additional').prepend(htmlFinal);
                        if (additionalmenu.defLang != $('html').attr('lang'))
                            $('html').attr('lang', additionalmenu.defLang);
                    }
                    if (additionalmenu.langNav) {
                        var htmlFinal = FGTemplate.bind('template_lang_navigation', { langNav: additionalmenu.langNav, deflang: additionalmenu.defLang });
                        $('.fg-web-top-nav-languages').append(htmlFinal);
                    }
                    if ((additionalmenu.additionalNav.length == 0) && (typeof additionalmenu.langNav === "undefined")) {
                        $('.fg-web-top-nav-icon').addClass('fg-hide');
                    }
                }
            }
        });
    };
    /**
     * Handle header nav icon click (on mobile view)
     */
    FgWebsiteTheme.prototype.headerNavIconClick = function () {
        $('.fg-web-nav-icon').click(function () {
            if ($('body').hasClass('fg-navbar-open')) {
                $('body').removeClass('fg-navbar-open');
                $('.fg-web-main-nav').css({
                    'min-height': 0
                });
            }
            else {
                var ulHeight = $('.fg-web-main-nav > .navbar-nav').outerHeight() + 35;
                ulHeight = (ulHeight > $(window).height()) ? '80vh' : ulHeight;
                $('.fg-web-main-nav').css({
                    'min-height': ulHeight
                });
                $('body').addClass('fg-navbar-open');
            }
        });
    };
    /**
     * header navigation actions
     */
    FgWebsiteTheme.prototype.navigationActions = function () {
        $(window).load(function () {
            FgWebsiteThemeObj.headerNavIconClick();
            $('.dropdown-submenu .fg-dropdown-toggle').on("click", function (e) {
                if ($(window).width() <= 768) {
                    $(this).parent('li').toggleClass('open');
                    $(this).toggleClass('open');
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
            $(".fg-web-main-nav .dropdown,.fg-web-main-nav .dropdown-submenu").on('mouseenter mouseleave tap', function (e) {
                if ($(this).children('ul').length) {
                    var elm = $(this).children('ul');
                    var off = elm.offset();
                    var l = off.left;
                    var w = elm.width();
                    var docW = $(window).width();
                    var isEntirelyVisible = (l + w <= docW);
                    if (!isEntirelyVisible) {
                        elm.removeClass('fg-web-open-right').addClass('fg-web-open-left');
                    }
                    else {
                    }
                }
            });
        });
    };
    return FgWebsiteTheme;
}());
;
