/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
/**
 * FgWebsite class
 *
 */
var _this;
var websiteNavMenu = [];
var FgWebsite = (function () {
    function FgWebsite() {
        /*
         * default template ids for topnavigation, header and background
         */
        this.templateTopNav = 'template_top_navigation';
        this.templateHeaderNav = 'template_header_navigation';
        this.templateAdditionNav = 'template_addition_navigation';
        this.templateAdditionNavMobile = 'template_addition_navigation_mobile';
        this.templateBackground = 'template_background';
        this.templateAdminTopNavLG = 'template_admin_top_navigation_lg';
        this.templateAdminTopNavXS = 'template_admin_top_navigation_xs';
        this.sidebarData = {};
        this.navigationMenuPath = '';
        this.activeHeaderMenu = '';
        this.subNavigationData = [];
        this.contactLang = '';
        this.clubLang = '';
        this.subLevelData = [];
        _this = this;
    }
    /**
     * initialize - header ,top navigation
     */
    FgWebsite.prototype.init = function () {
        if (isHeader == true) {
            FgWebsiteThemeObj.headerNav(headerNav, 'webpagelayout-header');
            FgWebsiteThemeObj.callAdditionalNav('webpagelayout-header');
        }
        else {
            $(".fg-web-main-content").removeClass('fg-visible-hidden');
            $('body').removeClass('no-scroll');
        }
        this.bindBackground('fg-web-bg-wrapper-id');
        this.setSiteLanguage();
        FgWebsiteThemeObj.onScrollEvent();
        this.onAjaxStopEvents();
        this.topAdditionalNavClick();
        this.initArticleCommonClick();
        this.fgAjaxError();
    };
    FgWebsite.prototype.buildHeaderNavigation = function (data) {
        var headerData = this.renderHeaderNavigationLevels(data, 1, []);
        return headerData;
    };
    FgWebsite.prototype.renderHeaderNavigationLevels = function (levelData, level, parentIds) {
        var htmldata = '';
        var headerMenuTemplate = (level == 1) ? "template_header_navigation_level1" : (level == 2) ? "template_header_navigation_level2" : "template_header_navigation_level3";
        var parentIdsTemp = (level == 1) ? [] : parentIds;
        _.each(levelData, function (fieldValue, fieldKey) {
            if ((parseInt(fieldValue.parent_id) !== 1) && (_.indexOf(parentIdsTemp, fieldValue.parent_id) == -1)) {
                parentIdsTemp.push(fieldValue.parent_id);
            }
            var subLevelData = fieldValue.subMenus;
            var levelObject = { parentIds: parentIdsTemp, content: _this.renderHeaderNavigationLevels(subLevelData, parseInt(fieldValue.level) + 1, parentIdsTemp), navPath: _this.navigationMenuPath, activeHeaderMenu: _this.activeHeaderMenu, subNavigationData: _this.subNavigationData, lang: _this.contactLang, clubLang: _this.clubLang };
            htmldata += _this.renderTemplate(headerMenuTemplate, { levelObject: levelObject, item: fieldValue });
        });
        return htmldata;
    };
    FgWebsite.prototype.renderTemplate = function (templateId, templateData) {
        var template = FGTemplate.bind(templateId, templateData);
        if (templateData.item.page_id !== null || templateData.item.external_link !== null) {
            _.each(templateData.levelObject.parentIds, function (value, key) {
                if (!_.has(_this.subNavigationData, value)) {
                    _this.subNavigationData[value] = templateData.item;
                }
            });
        }
        return template;
    };
    /**
     * Method to make activate parent menus on default conditions and on hover cases
     */
    FgWebsite.prototype.navigationActivation = function () {
        // to make parent menus default active after loading on 3rd level menus
        $('.fg-dev-nav-active').parent('ul').parent('li.dropdown-submenu').addClass('active').parent('ul').parent('li.dropdown').addClass('active');
        // to make parent menus default active after loading on 2nd level menus
        $('.fg-dev-nav-active').parent('ul').parent('li.dropdown').addClass('active');
        // to make parent menus active on hover
        $('li.dropdown-submenu').hover(function () {
            // to make parent menus default active on hover on 3rd level menus
            $(this).parent('ul').parent('li.dropdown-submenu').addClass('hover').parent('ul').parent('li.dropdown').addClass('hover');
            // to make parent menus default active after loading on 2nd level menus
            $(this).parent('ul').parent('li.dropdown').addClass('hover');
        }, function () {
            // to make parent menus default active on hover on 3rd level menus
            $(this).parent('ul').parent('li.dropdown-submenu').removeClass('hover').parent('ul').parent('li.dropdown').removeClass('hover');
            // to make parent menus default active after loading on 2nd level menus
            $(this).parent('ul').parent('li.dropdown').removeClass('hover');
        });
    };
    /**
     * bind the background with remaining layout
     */
    FgWebsite.prototype.bindBackground = function (parentDivId) {
        var fgWebsite = new FgWebsite();
        var htmlFinal = FGTemplate.bind(fgWebsite.templateBackground);
        $('#' + parentDivId).html(htmlFinal);
        fgWebsite.backgroundSliderConfig();
    };
    /**
     * unite gallery configuration settings for layout background
     */
    FgWebsite.prototype.backgroundSliderConfig = function () {
        $('.fg-web-bg-wrapper .fg-dev-slider').each(function (i, value) {
            var displayTime = $(value).data('slidertime');
            var sliderOption = {
                gallery_theme: "slider",
                slider_transition: "fade",
                tile_enable_action: false,
                tile_enable_overlay: false,
                gallery_play_interval: displayTime * 1000,
                slider_enable_play_button: false,
                slider_enable_arrows: false,
                slider_enable_bullets: false,
                slider_enable_progress_indicator: false,
                gallery_width: $(window).width() + 10,
                gallery_height: $(window).height() + 10,
            };
            var randomId = $(value).data('random-id');
            $("#bg-slider-" + randomId).unitegallery(sliderOption);
        });
    };
    /**
     * events to be done after ajax
     */
    FgWebsite.prototype.onAjaxStopEvents = function () {
        //scroll to top
        $(window).scrollTop(0);
        $('html').animate({ scrollTop: 0 }, 1);
        $('body').animate({ scrollTop: 0 }, 1);
        //on window resize reinit unitegallery slider
        $(window).resize(function () {
            var fgWebsite = new FgWebsite();
            fgWebsite.backgroundSliderConfig();
        });
    };
    FgWebsite.prototype.topNavigation = function (path, parentDivId, params) {
        var _this = this;
        $.ajax({ type: "GET", url: path, async: false, data: JSON.parse(params),
            success: function (jsonData) {
                _this.sidebarData = jsonData;
                _this.renderTopNavHtml(parentDivId, params);
            }
        });
    };
    FgWebsite.prototype.renderTopNavHtml = function (parentDivId, params) {
        var windowWidth = $(window).width();
        var _this = this;
        var sidebarData = _this.sidebarData;
        if (typeof sidebarData != 'undefined' && sidebarData != null) {
            if (windowWidth < 992) {
                sidebarData.topNavArr.combinedMenu = _.union(sidebarData.topNavArr.leftmenu, sidebarData.topNavArr.rightmenu);
                var htmlFinal = FGTemplate.bind(_this.templateAdminTopNavXS, sidebarData);
                var container = '.fg-page-header-small';
            }
            else {
                var htmlFinal = FGTemplate.bind(_this.templateAdminTopNavLG, sidebarData);
                var container = '.fg-page-header-large';
            }
        }
        if ($(container).length == 0) {
            $('#' + parentDivId).html(htmlFinal);
            _this.topNavigationSearch(params);
            var FgwebsitepageObj = new Fgwebsitepage();
            FgwebsitepageObj.handleLoginButtonsClick('fg-dev-admin-top-navigation');
        }
        return;
    };
    FgWebsite.prototype.topNavigationSearch = function (params) {
        var params = JSON.parse(params);
        if ($('#internalTopNavSearch').length > 0) {
            $('#webpage-admin-header').on('click', '.fbautocomplete-main-div', function () {
                $(this).parent().addClass('open');
            });
            $('#internalTopNavSearch').on('focusout', function () {
                $(this).parent().parent().removeClass('open');
            });
            $('#internalTopNavSearch').fbautocomplete({
                url: params.contactSearchUrl,
                maxItems: 1,
                useCache: false,
                onItemSelected: function ($obj, itemId, selected) {
                    var overViewPath = selected[0]['path'];
                    window.location.href = overViewPath;
                }
            });
        }
        //search for small resolutions
        if ($('#internalTopNavSearchSmallRes').length > 0) {
            $('#internalTopNavSearchSmallRes').fbautocomplete({
                url: params.contactSearchUrl,
                maxItems: 1,
                useCache: false,
                onItemSelected: function ($obj, itemId, selected) {
                    var overViewPath = selected[0]['path'];
                    window.location.href = overViewPath;
                }
            });
        }
    };
    FgWebsite.prototype.renderPromoBox = function (pathPost, pathCookie) {
        $('.fg-web-promobox-wrapper').hide();
        $.ajax({
            type: "GET",
            url: pathPost,
            async: false,
            success: function (jsonData) {
                if (jsonData.displayPromo == 1) {
                    $('.fg-web-promobox-wrapper').html(jsonData.displayHtml);
                    $('.fg-web-promo-toggle-btn').click(function (e) {
                        e.stopPropagation();
                        var action = $(this).attr('data-action');
                        if (action == 'close') {
                            $('.fg-web-promobox-wrapper').removeClass('open');
                        }
                        else {
                            $('.fg-web-promobox-wrapper').addClass('open');
                        }
                        if ($(this).attr('id') == 'dontDisplayPromo') {
                            $.ajax({
                                type: "GET",
                                url: pathCookie,
                                async: true,
                                success: function (response) {
                                    if (response == 'success') {
                                        console.log('success');
                                    }
                                    else {
                                        console.log('failed');
                                    }
                                }
                            });
                        }
                    });
                    $('.fg-web-promobox-wrapper').show();
                    if (jsonData.displayPromoFull == 1) {
                        $('.fg-web-promobox-wrapper').addClass('open');
                    }
                    else {
                        $('.fg-web-promobox-wrapper').removeClass('open');
                    }
                }
            }
        });
    };
    FgWebsite.prototype.setSiteLanguage = function () {
        $('#fg-website-lang-ul li a').click(function (e) {
            var lang = $(this).attr('data-id');
            var key = 'fg_website_lang_' + clubId;
            var value = lang;
            var date = new Date();
            // Default at 365 days.
            var days = 365;
            // Get unix milliseconds at current time plus number of days
            date.setTime(+date + (days * 86400000)); //24 * 60 * 60 * 1000
            document.cookie = key + "=" + value + "; expires=" + date.toGMTString() + "; path=/";
            location.reload();
        });
    };
    FgWebsite.prototype.topAdditionalNavClick = function () {
        $('body').on('click touchstart', '.fg-web-top-nav-icon', function () {
            $('.fg-web-top-nav-wrapper').toggleClass('show');
            setTimeout(function () {
                $('.fg-web-top-nav-links').toggleClass('active');
            }, 500);
        });
    };
    FgWebsite.prototype.initArticleCommonClick = function () {
        $('body').on('click', '.fg-article-link', function () {
            var url = $(this).data('url');
            window.location.href = url;
        });
    };
    FgWebsite.prototype.fgAjaxError = function () {
        $(document).ajaxError(function (event, jqXHR) {
            if (jqXHR.status === 403) {
                window.location.reload();
            }
        });
    };
    return FgWebsite;
}());
;
