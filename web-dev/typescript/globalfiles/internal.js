scope = angular.element($("#BaseController")).scope();
FgInternal = {
    templateTopNav: 'template_top_navigation',
    extraBreadcrumbTitle: [],
    dateFormat: { todayHighlight: true, autoclose: true, language: jstranslations.localeName, format: FgLocaleSettingsData.jqueryDateFormat, weekStart: 1, clearBtn: true },
    dateTimeFormat: { todayHighlight: true, autoclose: true, language: jstranslations.localeName, format: FgLocaleSettingsData.jqueryDateTimeFormat, weekStart: 1, clearBtn: true },
    init: function () {
        $.fn.select2.defaults = $.extend($.fn.select2.defaults, {
            minimumResultsForSearch: 15
        });
        setMegaMenuActive();
        FgInternal.back();
        FgFormTools.handleSelect2();
        Layout.fixContentHeight();
    },
    groupByMulti: function (obj, values, context) {
        if (!values.length)
            return obj;
        var byFirst = _.groupBy(obj, values[0], context), rest = values.slice(1);
        for (var prop in byFirst) {
            byFirst[prop] = FgInternal.groupByMulti(byFirst[prop], rest, context);
        }
        return byFirst;
    },
    showToastr: function (msg, type, title) {
        var toastrType = 'success';
        var toastrTitle = '';
        if (type)
            toastrType = type;
        if (title)
            toastrTitle = title;
        toastr.options = {
            positionClass: 'toast-top-center'
        };
        toastr[toastrType](msg, title);
        FgStickySaveBarInternal.init(0);
    },
    toolTipInit: function () {
        $('body').on('mouseover click', '.fg-custom-popovers', function (e) {
            var _this = $(this), thisContent = _this.data('content'), posLeft = _this.offset().left - 10, posTop = _this.offset().top + 50;
            FgInternal.showTooltip({ element: e, content: thisContent, position: [posLeft, posTop] });
            $('.popover .popover-content').width($('.popover').width() - 27);
        });
        $('body').on('mouseout', '.fg-custom-popovers', function () {
            $('body').find('.custom-popup').hide();
            $('.popover .popover-content').width('');
        });
    },
    showTooltip: function (obj) {
        var targetElement = $('body').find('.custom-popup'), elementContent = targetElement.find('.popover-content');
        elementContent.html(obj.content);
        targetElement.css({ 'left': obj.position[0], 'top': obj.position[1] });
        targetElement.show();
    },
    resetSortOrder: function (parentElement) {
        var parentElementId = $(parentElement).attr('id');
        if (!$(parentElement).hasClass('excludejs')) {
            $('input[data-sort-parent=' + parentElementId + ']').parent().parent().addClass('blkareadiv');
        }
        var i = 0;
        $('input[data-sort-parent=' + parentElementId + ']').each(function () {
            var sortParentElemId = $(this).attr('id').replace('_sort_order', '');
            if (!($($(this).parent()).hasClass('inactiveblock') || $($(this).parent().parent()).hasClass('inactiveblock') || $('#' + sortParentElemId).hasClass('inactiveblock'))) {
                i++;
                var id = $(this).attr('id');
                $("#" + id).attr('value', i);
                if (!$(parentElement).hasClass('excludejs')) {
                    if (i == 1) {
                        $(this).parent().parent().removeClass('blkareadiv').addClass('blkareadiv-top');
                    }
                }
            }
        });
    },
    checkboxReset: function () {
        var timeoutObj = setTimeout(function () {
            $.uniform.update($('form input:checkbox, form input:radio'));
        }, 10);
    },
    converttojson: function (objectGraph, name, value) {
        if (name.length == 1) {
            objectGraph[name[0]] = value;
        }
        else {
            if (objectGraph[name[0]] == null) {
                objectGraph[name[0]] = {};
            }
            FgInternal.converttojson(objectGraph[name[0]], name.slice(1), value);
        }
    },
    topNavigation: function (path, parentDivId, params) {
        $.ajax({ type: "GET", url: path, async: false, data: JSON.parse(params),
            success: function (jsonData) {
                var htmlFinal = FGTemplate.bind(FgInternal.templateTopNav, jsonData);
                $('#' + parentDivId).html(htmlFinal);
                Metronic.initAjax();
                FgInternal.topNavigationSearch();
                FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
                FgInternal.triggerChangePasswordPopUp();
                if (!$('body').hasClass('page-sidebar-fixed')) {
                    FgInternal.topNavigationResponsiveClick();
                }
                if ($('body').find('.fg-sticky-block').length > 0) {
                    $('body').addClass('fg-sticky-save-area');
                }
                else {
                    $('body').removeClass('fg-sticky-save-area');
                }
            }
        });
    },
    topNavigationSearch: function () {
        if ($('#internalTopNavSearch').length > 0) {
            $('#internalTopNavSearch').fbautocomplete({
                url: FgInternalVariables.topNavSearchUrl,
                maxItems: 1,
                useCache: false,
                onItemSelected: function ($obj, itemId, selected) {
                    overViewPath = selected[0]['path'];
                    window.location.href = overViewPath;
                }
            });
        }
        if ($('#internalTopNavSearchSmallRes').length > 0) {
            $('#internalTopNavSearchSmallRes').fbautocomplete({
                url: FgInternalVariables.topNavSearchUrl,
                maxItems: 1,
                useCache: false,
                onItemSelected: function ($obj, itemId, selected) {
                    overViewPath = selected[0]['path'];
                    window.location.href = overViewPath;
                }
            });
        }
    },
    topNavigationResponsiveClick: function () {
        var viewport = Metronic.getViewPort();
        $('.page-sidebar').off('click', 'li > a');
        jQuery('.page-sidebar').on('click', 'li > a:not(".filterCount")', function (e) {
            if ($(this).next().hasClass('sub-menu') == false) {
                if ($('.btn-navbar').hasClass('collapsed') == false) {
                    $('.btn-navbar').click();
                }
                return;
            }
            if ($(this).next().hasClass('sub-menu always-open')) {
                return;
            }
            var parent = $(this).parent().parent();
            var the = $(this);
            var menu = $('.page-sidebar-menu');
            var menu2 = $('.side_main_menu');
            var sub = jQuery(this).next();
            var autoScroll = menu.data("auto-scroll") ? menu.data("auto-scroll") : true;
            var slideSpeed = menu.data("slide-speed") ? parseInt(menu.data("slide-speed")) : 200;
            var slideOffeset = -200;
            if (sub.is(":visible")) {
                sub.slideUp(slideSpeed, function () {
                    jQuery(this).parent().find('.arrow').first().removeClass("open");
                    if (autoScroll == true && $('body').hasClass('page-sidebar-closed') == false) {
                        if ($('body').hasClass('page-sidebar-fixed')) {
                            if (viewport.width >= 992) {
                                menu2.slimScroll();
                            }
                        }
                        else {
                            Metronic.scrollTo(the, slideOffeset);
                        }
                    }
                    if ($(this).parent())
                        $(this).parent().removeClass("open");
                    Layout.fixContentHeight();
                });
            }
            else {
                sub.slideDown(slideSpeed, function () {
                    jQuery(this).parent().find('.arrow').first().addClass("open");
                    if (autoScroll == true && $('body').hasClass('page-sidebar-closed') == false) {
                        if ($('body').hasClass('page-sidebar-fixed')) {
                            if (viewport.width >= 992) {
                                menu2.slimScroll();
                            }
                        }
                        else {
                            Metronic.scrollTo(the, slideOffeset);
                        }
                    }
                    if ($(this).parent())
                        $(this).parent().addClass("open");
                    Layout.fixContentHeight();
                });
            }
            e.preventDefault();
        });
    },
    triggerChangePasswordPopUp: function () {
        $('body').on('click', '#fg-top-nav-change-password', function (e) {
            $.get(FgInternalVariables.topNavChangePasswordUrl, '', function (data) {
                FgModelbox.showPopup(data);
                FormValidation.init("changePassword", "", "");
            });
        });
    },
    breadcrumb: function (obj) {
        if (typeof index_url != 'undefined') {
            $('ul.page-breadcrumb.breadcrumb .fg-dynamic-links').remove();
            var html = '<li class="fg-dynamic-links" data-auto="breadcrumb_level1"><a href="' + index_url + '"><i class="fa fa-home"></i></a></li>';
            var count = 2;
            $('.navbar-collapse .fg-dev-header-nav-active').each(function (index) {
                var linkItem = $(this).find('> a');
                if (linkItem.length === 0) {
                    var linkItem = $(this).find('> h3');
                }
                linkText = linkItem.attr('data-title');
                linkUrl = (linkItem.attr('href') === undefined) ? '#' : linkItem.attr('href');
                var appendId = (linkItem.attr('id') === undefined) ? '' : 'id = "' + linkItem.attr('id') + '"';
                var dynamicClass = (linkUrl === "#" || linkUrl === window.location.pathname) ? 'class="fg-dynamic-links fg-page-inactive"' : 'class="fg-dynamic-links fg-page-active"';
                html += '<li ' + dynamicClass + ' data-auto="breadcrumb_level' + count + '"><i class="fa fa-angle-right"></i><a href="' + linkUrl + '" ' + appendId + '>' + linkText + '</a></li>';
                count = count + 1;
            });
            $.each(obj, function (key, data) {
                if (data && typeof data.text != typeof undefined) {
                    var link = (data.link) ? data.link : "#";
                    html += '<li class="fg-dynamic-links fg-page-inactive" data-auto="breadcrumb_levellast"><i class="fa fa-angle-right"></i><a href="' + link + '">' + data.text + '</a></li>';
                }
            });
            $('ul.page-breadcrumb.breadcrumb').append(html);
        }
    },
    back: function (url) {
        $(document).on('click', '.bckid', function () {
            var data_url = $(this).attr('data-url');
            data_url = data_url.trim();
            document.location = ((data_url == '#') || (data_url == '')) ? '' : data_url;
        });
    },
    enterkeypress: function () {
        $("input").keypress(function (event) {
            if (event.which == 13) {
                event.preventDefault();
                $("form").submit();
            }
        });
    },
    getWindowHeight: function (reduceWidth) {
        var height = $(window).height() - reduceWidth;
        if (height <= 300) {
            height = 300;
        }
        return height;
    },
    startPageLoading: function (options) {
        if (options.wrapperClass) {
            $(options.wrapperClass).html('<div class="fg-lazy-loader"><div class="fg-page-loading"><img src="' + Metronic.getGlobalImgPath() + 'loading-spinner-grey.gif"/>&nbsp;&nbsp;<span>' + (options && options.message ? options.message : jstranslations.loadingVar) + '</span></div></div>');
        }
    },
    stopPageLoading: function () {
        $('.fg-page-loading').remove();
    },
    togglePopUpNames: function () {
        $(document).off('click', '.modal-title .fg-dev-names');
        $(document).on('click', '.modal-title .fg-dev-names', function (e) {
            $(this).parent().toggleClass('fg-arrowicon');
        });
    },
    pageLoaderOverlayStart: function (parentContainer, options) {
        Metronic.stopPageLoading();
        var container = ($(parentContainer).length == 0) ? 'body' : parentContainer;
        $(container).addClass('fg-overlay-no-scroll').append('<div class="fg-loader-overlay"><div class="fg-lazy-loader"><div class="fg-page-loading"><img src="' + Metronic.getGlobalImgPath() + 'loading-spinner-grey.gif"/>&nbsp;&nbsp;<span>' + (options && options.message ? options.message : jstranslations.loadingVar) + '</span></div></div>').css({ 'position': 'relative' });
        if (container == 'body') {
            $(container).children('.fg-loader-overlay').addClass('fg-PF');
            $(container).removeClass('fg-overlay-no-scroll');
        }
    },
    pageLoaderOverlayStop: function (parentContainer) {
        var container = ($(parentContainer).length == 0) ? 'body' : parentContainer;
        $(container).children('.fg-loader-overlay').remove();
        $(container).removeClass('fg-overlay-no-scroll').css({ 'position': '' });
    },
    clearAllStorage: function () {
        var FgCurrentSprint = Sprint.currentSprint;
        var currentSprint = localStorage.getItem('fgcurrentSprint');
        if (typeof currentSprint === 'undefined' || currentSprint === null || currentSprint == '') {
            localStorage.clear();
            console.log('call-1');
            localStorage.setItem('fgcurrentSprint', FgCurrentSprint);
        }
        else if (FgCurrentSprint != currentSprint) {
            console.log('call-2');
            localStorage.clear();
            localStorage.setItem('fgcurrentSprint', FgCurrentSprint);
        }
    },
    createPopover: function (str, type, noCount) {
        var arr = str.split('*##*');
        if (arr.length > 1) {
            var cont = '';
            $.each(arr, function () {
                cont += this + "<br>";
            });
            var count = (!noCount) ? arr.length : '';
            return '<i class="fg-dev-Popovers fg-dotted-br" data-content="' + cont + '" >' + count + ' ' + type + '</i>';
        }
        else {
            return str;
        }
    },
    convertByteToMb: function (bytes, mb) {
        var filesize = (bytes / 1048576).toFixed(2);
        if (filesize < 0.1) {
            filesize = '< ' + FgClubSettings.formatNumber(0.1) + " " + mb;
        }
        else {
            filesize = FgClubSettings.formatNumber(filesize) + " " + mb;
        }
        return filesize;
    },
    updateRemainingCharacterCount: function (obj, maxLength) {
        FgInternal.getRemainingCharacterCount(obj, maxLength);
        obj.keyup(function () {
            FgInternal.getRemainingCharacterCount(obj, maxLength);
        });
    },
    getRemainingCharacterCount: function (obj, maxLength) {
        if (obj.length) {
            var textLength = obj.val().length;
            var textRemaining = maxLength - textLength;
            obj.siblings('p').html(textRemaining + ' ' + jstranslations.chars);
        }
    }
};
FgModelbox = {
    showPopup: function (data) {
        $('#fg-dev-popup-model').html(data);
        $('#fg-popup').modal('show');
    },
    hidePopup: function () {
        $('#fg-popup').modal('hide');
    }
};
FgXmlHttp = {
    scrollToErrorDiv: function (element) {
        var focusPos = 100;
        if (typeof element !== 'undefined') {
            if ($(element).length > 0) {
                focusPos = $(element).offset().top;
            }
        }
        else {
            if ($('.alert-danger').length > 0) {
                focusPos = $('.alert-danger').offset().top;
            }
        }
        focusPos = ((focusPos - 60) > 0) ? (focusPos - 60) : focusPos;
        $('html, body').animate({
            scrollTop: focusPos }, 'fast');
    },
    post: function (url, data, replacediv, successCallback, failCallback, isReplaceContent) {
        if (!isReplaceContent)
            isReplaceContent = 1;
        var rand = Math.random();
        Metronic.startPageLoading();
        $.post(url + "?rand=" + rand, data, function (result) {
            if (result.status) {
                if (result.redirect) {
                    if (result.sync) {
                        Metronic.stopPageLoading();
                        document.location = result.redirect;
                        if (result.flash)
                            FgInternal.showToastr(result.flash);
                    }
                    else {
                        FgXmlHttp.replaceContentFromUrl(result.redirect, result.flash, successCallback, result);
                    }
                }
                else {
                    if (result.noparentload) {
                        Metronic.stopPageLoading();
                        if (result.flash) {
                            FgInternal.showToastr(result.flash);
                        }
                        if (successCallback && !result.errorArray) {
                            successCallback.call({}, result);
                        }
                        if (failCallback) {
                            failCallback.call({}, result);
                        }
                    }
                    else {
                        FgXmlHttp.replaceContentFromUrl(document.location.href, result.flash, successCallback, result);
                    }
                }
            }
            else {
                if (isReplaceContent === 1) {
                    if (replacediv)
                        $(replacediv).html(result);
                    else {
                        $('#fg-wrapper').html(result);
                    }
                }
                if (successCallback && !result.errorArray) {
                    successCallback.call({}, result);
                }
                if (failCallback) {
                    failCallback.call({}, result);
                }
                FgXmlHttp.scrollToErrorDiv();
                Metronic.stopPageLoading();
            }
        });
    },
    iframepost: function (url, form, extradata, replacediv, sucessCallback, failCallback) {
        Metronic.startPageLoading();
        if (extradata)
            extradata.layout = false;
        else
            extradata = {
                layout: false
            };
        var options = {
            success: function (responseText) {
                Metronic.stopPageLoading();
                flag = false;
                try {
                    if (typeof (responseText) == 'object') {
                        obj = responseText;
                        flag = true;
                    }
                    else {
                        obj = JSON.decode(responseText);
                        flag = true;
                    }
                }
                catch (e) {
                }
                if (flag) {
                    if (obj.redirect) {
                        if (obj.sync) {
                            Metronic.stopPageLoading();
                            document.location = obj.redirect;
                            if (obj.flash)
                                FgInternal.showToastr(obj.flash);
                        }
                        else {
                            FgXmlHttp.replaceContentFromUrl(obj.redirect, obj.flash, sucessCallback, false, obj);
                        }
                    }
                    else {
                        FgXmlHttp.replaceContentFromUrl(document.location.href, obj.flash, sucessCallback, false, obj);
                    }
                }
                else {
                    if (replacediv)
                        $(replacediv).html(responseText);
                    else {
                        $('#fg-wrapper').html(responseText);
                    }
                    if (failCallback)
                        failCallback.call({}, responseText);
                    if (form.attr('data-scrollToFirstError')) {
                        FgXmlHttp.scrollToErrorDiv('.has-error:eq(0):visible');
                    }
                    else {
                        FgXmlHttp.scrollToErrorDiv();
                    }
                }
            },
            url: url,
            data: extradata,
            type: 'post'
        };
        form.ajaxSubmit(options);
        return false;
    },
    formPost: function (paramObj) {
        if (paramObj.form && paramObj.url) {
            Metronic.startPageLoading();
            if (paramObj.extradata) {
                paramObj.extradata.layout = false;
            }
            else {
                paramObj.extradata = { 'layout': false };
            }
            paramObj.form.ajaxSubmit({
                url: paramObj.url,
                data: paramObj.extradata,
                type: 'post',
                success: function (responseText) {
                    Metronic.stopPageLoading();
                    flag = false;
                    try {
                        if (typeof (responseText) == 'object') {
                            obj = responseText;
                            flag = true;
                        }
                        else {
                            obj = JSON.decode(responseText);
                            flag = true;
                        }
                    }
                    catch (e) {
                    }
                    if (flag == true) {
                        if (obj.redirect) {
                            if (obj.sync) {
                                document.location = obj.redirect;
                                if (obj.flash)
                                    FgInternal.showToastr(obj.flash);
                            }
                            else {
                                FgXmlHttp.replaceContentFromUrl(obj.redirect, obj.flash, paramObj.sucessCallback, false, obj);
                            }
                        }
                        else if (obj.noreload) {
                            if (obj.flash)
                                FgInternal.showToastr(obj.flash);
                        }
                        else if (obj.status !== 'ERROR') {
                            FgXmlHttp.replaceContentFromUrl(document.location.href, obj.flash, paramObj.sucessCallback, false, obj);
                        }
                    }
                    else {
                        if (paramObj.replacediv) {
                            $(paramObj.replacediv).html(responseText);
                        }
                        else {
                            $('#fg-wrapper').html(responseText);
                        }
                        FgXmlHttp.scrollToErrorDiv();
                    }
                    if (paramObj.successCallback) {
                        if (paramObj.successParam) {
                            paramObj.successParam.responseText = responseText;
                        }
                        else {
                            paramObj.successParam = { 'responseText': responseText };
                        }
                        paramObj.successCallback.call(this, paramObj.successParam);
                    }
                    if (paramObj.failCallback) {
                        paramObj.failCallback.call({}, responseText);
                    }
                    Metronic.stopPageLoading();
                },
                error: function (data) {
                    if (paramObj.failCallback) {
                        paramObj.failCallback.call({}, responseText);
                    }
                }
            });
        }
    },
    replaceContentFromUrl: function (url, flashmsg, callback, callbackdata) {
        $.ajax({
            url: url,
            data: {
                silent: 1
            },
            success: function (data) {
                Metronic.stopPageLoading();
                $('#fg-wrapper').html(data);
                if (flashmsg)
                    FgInternal.showToastr(flashmsg);
                if (callback)
                    callback.call({}, callbackdata);
            }
        });
    },
    init: function () {
        $.ajaxSetup({ cache: false });
    }
};
FgInternalParseFormField = {
    fieldParse: function () {
        $('.sortables').parent().each(function () {
            FgInternal.resetSortOrder($(this));
        });
        if ($('body').hasClass('dirty_field_used')) {
            FgDirtyFields.updateFormState();
        }
        $('.fg-dev-newfield').addClass('fairgatedirty');
        var objectGraph = {};
        $("form :input").each(function () {
            var attr = $(this).attr('data-key');
            if ($(this).hasClass("fairgatedirty") && typeof attr !== typeof undefined && attr !== false) {
                var inputVal = '';
                var inputType = $(this).attr('type');
                if (inputType == 'checkbox') {
                    inputVal = $(this).attr('checked') ? 1 : 0;
                }
                else if (inputType == 'radio') {
                    if ($(this).is(':checked')) {
                        inputVal = $(this).val();
                    }
                }
                else {
                    inputVal = $(this).val();
                }
                if (inputVal !== '' || $(this).is("textarea") || $(this).is("select")) {
                    FgInternal.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
                else if (inputType == 'hidden' || $(this).hasClass("hide")) {
                    FgInternal.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
                else if ((inputVal === '') && ($(this).attr('data-notrequired') == 'true')) {
                    FgInternal.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
            }
        });
        return objectGraph;
    },
    formFieldParse: function (formId) {
        $('.sortables').parent().each(function () {
            FgInternal.resetSortOrder($(this));
        });
        $('.fg-dev-newfield').addClass('fairgatedirty');
        var objectGraph = {};
        $("#" + formId + " :input").each(function () {
            var attr = $(this).attr('data-key');
            if ($(this).hasClass("fairgatedirty") && typeof attr !== typeof undefined && attr !== false) {
                var inputVal = '';
                var inputType = $(this).attr('type');
                if (inputType == 'checkbox') {
                    inputVal = $(this).attr('checked') ? 1 : 0;
                }
                else if (inputType == 'radio') {
                    if ($(this).is(':checked')) {
                        inputVal = $(this).val();
                    }
                }
                else {
                    inputVal = $(this).val();
                }
                if (inputVal !== '' || $(this).is("textarea") || $(this).is("select")) {
                    FgInternal.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
                else if (inputType == 'hidden' || $(this).hasClass("hide")) {
                    FgInternal.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
                else if ((inputVal === '') && ($(this).attr('data-notrequired') == 'true')) {
                    FgInternal.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
            }
        });
        return objectGraph;
    }
};
FgFormTools = {
    handleUniform: function () {
        if (!jQuery().uniform) {
            return;
        }
        var test = $("input[type=checkbox]:not(.toggle, .make-switch), input[type=radio]:not(.toggle, .star, .make-switch)");
        if (test.size() > 0) {
            test.each(function () {
                if ($(this).parents(".checker").size() == 0) {
                    $(this).uniform();
                }
            });
        }
    },
    handleInputmask: function () {
        $(".numbermask").inputmask("decimal", {
            rightAlign: false,
            placeholder: "",
            digits: 2,
            radixPoint: FgLocaleSettingsData.decimalMark,
            autoGroup: true,
            allowPlus: false,
            allowMinus: false,
            clearMaskOnLostFocus: true,
            removeMaskOnSubmit: true,
            onUnMask: function (maskedValue, unmaskedValue) {
                var x = unmaskedValue.split(',');
                if (x.length != 2)
                    return "0.00";
                return x[0].replace(/\./g, '') + '.' + x[1];
            }
        });
        $(document).on('blur', ".fg-urlmask", function () {
            appendHttp(this);
        });
        $(document).on('keypress', ".fg-urlmask", function (e) {
            if (e.which == 13) {
                appendHttp(this);
            }
        });
        appendHttp = function (_this) {
            inputVal = $(_this).val();
            if (inputVal != "") {
                var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
                if (!regexp.test(inputVal)) {
                    var indx = inputVal.indexOf("://");
                    if (indx < 0) {
                        returnUrl = "http://" + inputVal.substring(indx);
                    }
                    else {
                        returnUrl = "http" + inputVal.substring(indx);
                    }
                    $(_this).val(returnUrl);
                }
            }
        };
    },
    handleDatepicker: function (extraSettings) {
        var defaultSettings = {
            language: jstranslations.localeName,
            format: FgLocaleSettingsData.jqueryDateFormat,
            autoclose: true,
            weekStart: 1,
            clearBtn: true
        };
        var dateSettings = $.extend(true, {}, defaultSettings, extraSettings);
        $('.datepicker').datepicker(dateSettings);
    },
    handleBootstrapSelect: function () {
        $('.bs-select').selectpicker({
            noneSelectedText: jstranslations.noneSelectedText,
            countSelectedText: jstranslations.countSelectedText,
        });
    },
    handleSelect2: function () {
        $("select.select2").select2();
        $('select.cl-bs-select, select.select2').not('.select-with-search').live("select2-focus", function () {
            if (!($(this).find('.select2-drop').hasClass('.select2-with-searchbox'))) {
                $(this).find('.select2-focusser').hide();
                $(this).find('.select2-drop').not('.select2-with-searchbox').find('.select2-search').remove();
            }
        });
    },
    handleformSelect2: function () {
        $('select.form-select').select2({
            minimumResultsForSearch: -1
        });
    },
    updateUniform: function (selector) {
        $.uniform.update(selector);
    },
    changeColorOnDelete: function () {
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function () {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('div#' + parentId);
            $(parentDiv).toggleClass('inactiveblock');
        });
    },
    select2ViaAjax: function (path, searchCount, dataArrayType) {
        $.ajax({
            type: "POST",
            url: path,
            dataType: 'json',
            success: function (data) {
                $("select.fg-select-with-search").each(function () {
                    var value = $(this).val();
                    if (value) {
                        $(this).find("option[value=" + value + "]").remove();
                    }
                });
                if (dataArrayType == 'associative') {
                    $.each(data, function (index, element) {
                        $('.fg-select-with-search').append($('<option/>', {
                            value: element.id,
                            text: element.title,
                        }));
                    });
                }
                else {
                    $.each(data, function (index, element) {
                        $('.fg-select-with-search').append($('<option/>', {
                            value: index,
                            text: element,
                        }));
                    });
                }
                $("select.fg-select-with-search").each(function () {
                    var originalVal = $(this).attr("data-originalVal");
                    $(this).select2({ minimumResultsForSearch: searchCount }).select2('val', originalVal);
                });
            }
        });
    },
    selectpickerViaAjax: function (path, dirtyFieldParam) {
        $.ajax({
            type: "POST",
            url: path,
            dataType: 'json',
            success: function (data) {
                $('#ajaxLoadSelectpicker').selectpicker('remove');
                $("#ajaxLoadSelectpicker").each(function () {
                    $(this).find("option").remove();
                });
                $('#ajaxLoadSelectpicker').selectpicker('refresh');
                $.each(data, function (index, element) {
                    $("#ajaxLoadSelectpicker").append($("<option></option>").attr("value", element.id).text(element.title));
                });
                $('#ajaxLoadSelectpicker').selectpicker('refresh');
                if (dirtyFieldParam) {
                    FgMultiEditApp.fgDirtyField();
                }
            }
        });
        return true;
    }
};
var setMegaMenuActive = function () {
    if (window.matchMedia('(max-width: 1400px)  and (min-width: 992px)').matches) {
        var activeItems = $('.mega-menu-content').parent().find('.active');
        if (activeItems.length) {
            $('.mega-menu-dropdown').addClass('active');
        }
    }
    else {
    }
};
FgPopOver = {
    init: function (selector, htmlflag, staybahaviour) {
        htmlflag = typeof htmlflag !== 'undefined' ? htmlflag : false;
        staybahaviour = typeof staybahaviour !== 'undefined' ? staybahaviour : true;
        if (staybahaviour) {
            $("body").on('mouseenter touchstart', selector, function () {
                $(this).popover({
                    html: htmlflag,
                    trigger: 'manual',
                    container: 'body',
                    placement: function () {
                        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                            return 'auto';
                        }
                        else {
                            var width = $(window).width() - 300;
                            var xPos = this.getPosition().left;
                            var placement = width < xPos ? 'left' : 'auto';
                            return placement;
                        }
                    },
                }).on("mouseenter click", function () {
                    var _this = this;
                    $(this).popover("show");
                    $(this).siblings(".popover").on("mouseleave", function () {
                        $(_this).popover('hide');
                    });
                }).on("mouseleave", function () {
                    var _this = this;
                    setTimeout(function () {
                        $(_this).popover("hide");
                    }, 50);
                    $('.popover .popover-content').width('');
                }).popover('show');
            });
        }
        else {
            $('body').popover({
                selector: selector,
                trigger: 'hover',
                html: htmlflag,
                delay: { show: 100, hide: 800 }
            });
        }
    },
    customPophover: function (userClass) {
        $(userClass).popover({
            trigger: 'hover',
            html: true,
            placement: 'auto'
        });
    }
};
var FgInternalDragAndDrop = {
    init: function (sortDiv) {
        handleDragAndDrop(sortDiv, false, false, false, false);
    },
    sortWithOrderUpdation: function (sortDiv, doChildSort) {
        handleDragAndDrop(sortDiv, doChildSort, false, false, true);
    },
    resetChanges: function (resetSections) {
        $('#reset_changes').click(function () {
            $('div.addednew').remove();
            $('.inactiveblock').removeClass('inactiveblock');
            jQuery.each(resetSections, function (key, resetSection) {
                var parentElement = resetSection.parentElement;
                var initialOrder = resetSection.initialOrder;
                var addClass = resetSection.addClass;
                var className = resetSection.className;
                initialOrder = JSON.parse(initialOrder);
                var i = 0;
                jQuery.each(initialOrder, function (key, val) {
                    i++;
                    var appendElement = $(parentElement).find('#' + val);
                    if (addClass) {
                        if (i == 1) {
                            $(appendElement).removeClass(className);
                        }
                        else {
                            $(appendElement).addClass(className);
                        }
                    }
                    $(parentElement).append(appendElement);
                });
            });
        });
    }
};
var handleDragAndDrop = function (sortDiv, doChildSort, sortCheck, inputId, updateSortOrder) {
    $(sortDiv).each(function () {
        $(this).sortable({
            droppable: true,
            connectWith: $(this).children('.sortables'),
            items: $(this).children('.sortables'),
            opacity: 0.8,
            forcePlaceholderSize: true,
            tolerance: "fit",
            handle: '.handle',
            placeholder: 'placeholder',
            start: function (event, ui) {
                ui.item.addClass("fg-drag-line-border");
                ui.item.startPos = ui.item.index();
            },
            stop: function (event, ui) {
                ui.item.removeClass("fg-drag-line-border");
                ui.item.stopPos = ui.item.index();
                if (doChildSort) {
                    doAfterSort(ui.item, this);
                }
                if (sortCheck) {
                    var changeval = '';
                    $(sortDiv).children().each(function (index, value) {
                        changeval = changeval + this.id + ',';
                    });
                    if (changeval != '') {
                        changeval = changeval.substring(0, changeval.length - 1);
                    }
                    $('#' + inputId).val(changeval);
                    $('form').trigger('checkform.areYouSure');
                }
                if (updateSortOrder) {
                    FgInternal.resetSortOrder(this);
                    FgDirtyFields.updateFormState();
                }
            }
        });
    });
};
FgInputTextValidation = {
    init: function () {
        if (!$('form :input.form-control[type="text"]').hasClass('fg-dev-autocomplete')) {
            $('form :input.form-control[type="text"]').attr('maxlength', '160');
        }
    }
};
function doSortOrderUpdation(parentElement) {
    FgInternal.resetSortOrder(parentElement);
}
FgUtility = {
    showTranslation: function (lang) {
        $("[data-lang]").addClass('hide');
        $('[data-lang=' + lang + ']').removeClass('hide');
        $('button[data-elem-function=switch_lang]').removeClass('adminbtn-ash').addClass('fg-lang-switch-btn');
        $('button[data-elem-function=switch_lang][data-selected-lang=' + lang + ']').removeClass('fg-lang-switch-btn').addClass('adminbtn-ash');
    },
    handleSelectPicker: function () {
        $('.single').on('click', function () {
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('deselectAll');
        });
        $('.multiple').on('click', function () {
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .single').prop("selected", false);
            var totalElements = $(this).parents('ul').find('li a.multiple').size();
            var totSelected = $(this).parents('ul').find('li.selected').size();
            var singleElemCount = $($(this).parent().parent().find('li.selected a.single')).length;
            var selectedMultiElmCnt = totSelected - singleElemCount;
            if (((totalElements - 1) == selectedMultiElmCnt) && !($(this).parents('li').hasClass('selected'))) {
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectall').prop("selected", true);
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
                var parentElem = $($(this).parents('.bootstrap-select').parent());
                FgUtility.showSelectAllTitle(parentElem, 'all');
            }
            else {
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectall').prop("selected", false);
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
            }
        });
        $('.selectall').on('click', function () {
            var totalElements = $(this).closest('ul').find('li a.multiple').size() + 1;
            var totSelected = $(this).closest('ul').find('li.selected').size();
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .single').prop("selected", false);
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .multiple').prop("selected", true);
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectAll').prop("selected", true);
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
            var parentElem = $($(this).parents('.bootstrap-select').parent());
            FgUtility.showSelectAllTitle(parentElem, 'all');
            if (totSelected == totalElements) {
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectAll').prop("selected", false);
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .multiple').prop("selected", false);
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
                FgUtility.showSelectAllTitle(parentElem, 'none');
            }
        });
    },
    showSelectAllTitle: function (parentElem, type) {
        var html = (type == 'all') ? jstranslations.allSelected : jstranslations.noneSelectedText;
        setTimeout(function () {
            parentElem.find('.filter-option').html(html);
        }, 20);
    },
    getOrderOfChildElements: function (parentDiv) {
        var sortOrderArray = {};
        var i = 0;
        $($(parentDiv).children()).each(function () {
            i++;
            var childId = $(this).attr('id');
            sortOrderArray[i] = childId;
        });
        sortOrderArray = JSON.stringify(sortOrderArray);
        return sortOrderArray;
    },
    handleDelete: function () {
        $('body form').on('click', 'input[data-inactiveblock=changecolor]', function () {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('div#' + parentId);
            $(parentDiv).toggleClass('inactiveblock');
            if ($(this).is(':checked')) {
                var inpurReq = $(parentDiv).find('input[required]');
                $(inpurReq).attr('data-required', true);
                $(inpurReq).removeAttr('required');
            }
            else {
                var inpurReq = $(parentDiv).find('input[data-required]');
                $(inpurReq).attr('required', true);
                $(inpurReq).removeAttr('data-required');
            }
        });
    },
    handleNestablelistHandler: function (container, lockTitle) {
        container = container || '.fg-nestable';
        lockTitle = lockTitle || 'This menu item has sub-items. Please delete them first.';
        $(container + ' li').each(function () {
            var data_id = $(this).attr('data-id');
            if ($(this).find('.fg-col-last-icon .deletediv ').find('.make-switch.fairgatedirty').length === 0)
                $(this).find('.fg-col-last-icon .deletediv ').html('<div class="closeico"><input type="checkbox" class="make-switch" data-inactiveblock="changecolor" data-parentid="' + data_id + '" data-key="' + data_id + '.is_deleted" name="' + data_id + '_is_deleted"  id="' + data_id + '_is_deleted"><label for="' + data_id + '_is_deleted"></label></div>');
        });
        $('.fg-nestable li ol').find('li:first').parent('ol').parent('li').children('.fg-nestable-row').removeClass('inactiveblock');
        $('.fg-nestable li ol').find('li:first').parent('ol').parent('li').children('.fg-nestable-row').find('.fg-col-last-icon .deletediv').html('<i class="fa fa-lock fa-2x ash" data-toggle="tooltip" title="' + lockTitle + '"></i> ');
        return true;
    },
    isGreaterDate: function (idate1, idate2) {
        if (idate1 != '')
            var idate1Timestamp = moment(idate1, FgLocaleSettingsData.momentDateTimeFormat).format('x');
        else
            var idate1Timestamp = 0;
        if (idate2 != '')
            var idate2Timestamp = moment(idate2, FgLocaleSettingsData.momentDateTimeFormat).format('x');
        else
            var idate2Timestamp = 0;
        if (idate1Timestamp == idate2Timestamp)
            return 0;
        else if (idate1Timestamp > idate2Timestamp)
            return 1;
        else if (idate1Timestamp < idate2Timestamp)
            return -1;
    },
    dateFilter: function (rowDate, startdate, enddate) {
        if (startdate != '')
            var startdateTimestamp = moment(startdate, FgLocaleSettingsData.momentDateFormat).format('x');
        else
            var startdateTimestamp = 0;
        if (enddate != '')
            var enddateTimestamp = moment(enddate, FgLocaleSettingsData.momentDateFormat).format('x');
        else
            var enddateTimestamp = 0;
        var currentRowTimestamp = moment(rowDate, FgLocaleSettingsData.momentDateFormat).format('x');
        var show = false;
        if (startdateTimestamp > 0 && enddateTimestamp > 0) {
            if (currentRowTimestamp >= startdateTimestamp && currentRowTimestamp <= enddateTimestamp)
                show = true;
        }
        else if (startdateTimestamp > 0) {
            if (currentRowTimestamp >= startdateTimestamp)
                show = true;
        }
        else if (enddateTimestamp > 0) {
            if (currentRowTimestamp <= enddateTimestamp)
                show = true;
        }
        if (show) {
            return true;
        }
        else {
            return false;
        }
    }
};
FgStickySaveBarInternal = {
    init: function (tabPage) {
        if ($("body").find(".internal-sticky-area").length > 0) {
            if (tabPage == 0) {
                var page_content_style = $(".page-content").attr('style');
                $(".page-content").css({ 'min-height': 0 });
                var contentHeight = $(".page-content").height() + 100;
                var windowHeight = $(window).height();
                $(window).load(function () {
                    var contentHeight = $(".page-content").height() + 100;
                    var windowHeight = $(window).height();
                    FgStickySaveBarInternal.handleStickyClass(contentHeight, windowHeight);
                    $(".page-content").attr('style', page_content_style);
                });
            }
            else {
                var contentHeight = (tabPage == 2) ? $('#fg_field_category_137').height() + $('#fg_field_category_' + tabPage).height() : $('#fg_field_category_' + tabPage).height();
                $(window).load(function () {
                    var contentHeight = contentHeight + 300;
                    var windowHeight = $(window).height();
                    FgStickySaveBarInternal.handleStickyClass(contentHeight, windowHeight);
                });
            }
        }
    },
    reInit: function (tabPage) {
        if ($("body").find(".internal-sticky-area").length > 0) {
            if (tabPage == 0) {
                var page_content_style = $(".page-content").attr('style');
                $(".page-content").css({ 'min-height': 0 });
                var contentHeight = $(".page-content").height() + 100;
                var windowHeight = $(window).height();
                FgStickySaveBarInternal.handleStickyClass(contentHeight, windowHeight);
                $(".page-content").attr('style', page_content_style);
            }
            else {
                var contentHeight = (tabPage == 2) ? $('#fg_field_category_137').height() + $('#fg_field_category_' + tabPage).height() : $('#fg_field_category_' + tabPage).height();
                var contentHeight = contentHeight + 300;
                var windowHeight = $(window).height();
                FgStickySaveBarInternal.handleStickyClass(contentHeight, windowHeight);
            }
        }
    },
    handleStickyClass: function (contentHeight, windowHeight) {
        if (contentHeight > windowHeight) {
            $('.internal-sticky-area').not('.exclude-sticky').addClass('fg-sticky-block');
            $('body').addClass('fg-sticky-save-area');
        }
        else {
            $('.internal-sticky-area').removeClass('fg-sticky-block');
            $('body').removeClass('fg-sticky-save-area');
        }
    }
};
FgInternal.clearAllStorage();
//# sourceMappingURL=internal.js.map