/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
var Fgwebsitepage = (function () {
    function Fgwebsitepage() {
        this.settings = '';
        this.sortSetting = '';
        this.pageTranslation = {};
        this.defaultSettings = {
            sidebarContainer: '#sidebarBox',
            contentContainer: '#contentBox',
            footerContainer: '#footerBox',
            containerType: 'content',
            data: {},
            boxTemplateId: 'pageBox',
            sidebarSide: '',
            sidebarSize: '',
            sidebarType: 'normal',
            mainContainer: '#mainContainer',
            container: {
                data: {},
                templateId: 'containerBox' // ID of template to render container data
            },
            column: {
                data: {},
                templateId: 'columnBox' // ID of template to render column data
            },
            columnbox: {
                data: {},
                templateId: 'Box' // ID of template to render box data
            },
            elementbox: {
                data: {},
                templateId: {
                    'header': 'templateHeader',
                    'text': 'templateText',
                    'articles': 'templateArticle',
                    'calendar': 'templateCalendar',
                    'map': 'templateMap',
                    'login': 'templateLogin',
                    'image': 'templateImage',
                    'iframe': 'templateIframe',
                    'form': 'templateForm',
                    'supplementary-menu': 'templateSupplementary'
                } // ID of template to render box data
            },
            initContainerCallback: function () { },
            initColumnCallback: function () { },
            initColumnBoxCallback: function () { },
            initElementBoxCallback: function () { },
            pageInitCallback: function () { }
        };
        this.defaultSortOptions = {
            opacity: 0.8,
            forcePlaceholderSize: true,
            tolerance: "pointer"
        };
    }
    Fgwebsitepage.prototype.initSettings = function (options) {
        // for hiding link icon in images without links in image element with links
        $('body').on('mouseover', '.ug-gallery-wrapper .ug-thumb-wrapper', function () {
            var href = $(this).attr('href');
            if (href == 'javascript:void(0)') {
                $(this).find('.ug-thumb-overlay').removeClass('ug-thumb-overlay');
                $(this).find('.ug-icon-link').remove();
            }
        });
        this.settings = $.extend(true, {}, this.defaultSettings, options);
        var _this = this;
        $(window).resize(function () {
            _this.makeFooterSticky();
        });
        $(document).ready(function () {
            _this.makeFooterSticky();
        });
    };
    Fgwebsitepage.prototype.renderContainerBox = function (containerId) {
        if (_.size(this.settings.container.data) > 0) {
            //render all columns of particular container
            var columnContent = this.containerColumns();
            return FGTemplate.bind(this.settings.container.templateId, { details: this.settings.container.data, containerid: containerId, columnDetails: columnContent, pageId: this.settings.data.page.id, settingDetails: this.settings });
        }
    };
    Fgwebsitepage.prototype.renderColumnBox = function (columnId) {
        //render all column box
        var boxContent = this.columnBox();
        return FGTemplate.bind(this.settings.column.templateId, { details: this.settings.column.data, columnid: columnId, settingDetails: this.settings, boxDetails: boxContent });
    };
    Fgwebsitepage.prototype.renderBox = function (boxId) {
        //render all the box element
        var elementContent = this.elementBox();
        return FGTemplate.bind(this.settings.columnbox.templateId, { details: this.settings.columnbox.data, boxid: boxId, elementDetails: elementContent });
    };
    Fgwebsitepage.prototype.renderElement = function (elementId) {
        var _this = this;
        if (this.settings.elementbox.data.isAjax !== true) {
            return FGTemplate.bind(this.settings.elementbox.templateId[this.settings.elementbox.data.elementType], { details: this.settings.elementbox.data, elementid: elementId, settingDetails: this.settings });
        }
        else {
            if (this.settings.elementbox.data.elementType == 'supplementary-menu') {
                var supplymenteryDataUrl = this.settings.elementbox.data.dataURL;
                if (typeof supplymenteryDataUrl != 'undefined') {
                    supplymenteryDataUrl = supplymenteryDataUrl.replace("**dummy**", elementId);
                    if (typeof currentNavigationId == 'undefined') {
                        var clubLocalStorage = localStorage.getItem("ClubGlobalConfig_" + clubId);
                        if (clubLocalStorage !== null) {
                            var sidebarData = JSON.parse(clubLocalStorage);
                            var openedSidebarMenuData = _.isEmpty(sidebarData['sidebar']['CMS']["Active"]) ? '' : sidebarData['sidebar']['CMS']["Active"];
                            var openedSidebarMenu = openedSidebarMenuData.split("_");
                            var navigationId = openedSidebarMenu.slice(-1).pop();
                        }
                    }
                    else {
                        var navigationId = currentNavigationId;
                    }
                    var supplymenteryDataFinalUrl = supplymenteryDataUrl.replace('**navid**', navigationId);
                    this.settings.elementbox.data.ajaxURL = supplymenteryDataFinalUrl;
                }
            }
            if (this.settings.elementbox.data.elementType == 'articles') {
                this.settings.elementbox.data.ajaxURL = this.settings.elementbox.data.ajaxURL + '?menu=' + menu;
            }
            // ajax call of each element and render this one
            $.post(this.settings.elementbox.data.ajaxURL, function (data) {
                _this.insertElement(elementId, data);
            });
            return FGTemplate.bind(this.settings.elementbox.templateId[this.settings.elementbox.data.elementType], { details: this.settings.elementbox.data, elementid: elementId, settingDetails: this.settings });
        }
    };
    Fgwebsitepage.prototype.insertElement = function (elementId, data) {
        if (data.elementType == 'form') {
			if(data.formData == null || data.formData == ''){
				return true;
			}	
            dataHtml = FGTemplate.bind("templateFormField", { formDetails: data.formData, defLang: data.defLang });
            $("#" + elementId).append(dataHtml);
            buttonText = $("#" + elementId).find('form input[type=file]').attr('data-buttonText');
            $("#" + elementId + " input:checkbox,#" + elementId + " input:radio").uniform();
            $("#" + elementId + " :file").filestyle({ iconName: "fa fa-file", buttonName: "fg-upload-btn", buttonText: buttonText });
            var defaultSettings = {
                language: jstranslations.localeName,
                format: FgLocaleSettingsData.jqueryDateFormat,
                autoclose: true,
                weekStart: 1,
                clearBtn: true
            };
            var dateSettings = $.extend(true, {}, defaultSettings);
            $("#" + elementId + " .fg-datepicker1").each(function () {
                startDate = $(this).attr('data-startDate');
                if (startDate != '') {
                    dateSettings['startDate'] = startDate;
                }
                endDate = $(this).attr('data-endDate');
                if (endDate != '') {
                    dateSettings['endDate'] = endDate;
                }
                $(this).datepicker(dateSettings);
            });
            $("#" + elementId + " .bs-select").selectpicker({
                noneSelectedText: jstranslations.noneSelectedText,
                countSelectedText: jstranslations.countSelectedText
            });
            this.handleFormElementSubmit(elementId);
            this.handleFormFileUpload(elementId);
            this.handleTimePicker(elementId);
            this.handleToolTip(elementId);
            if ($("#" + elementId + " .g-recaptcha").length > 0) {
                $("#" + elementId).find('.fg-form-element-submit').attr('disabled', true);
                var captchaContainer = null;
                var formCaptcha = function () {
                    captchaContainer = grecaptcha.render('fg-captcha-' + elementId, {
                        'sitekey': sitekeys,
                        'callback': function (response) {
                            $("#" + elementId).find('.fg-form-element-submit').removeAttr('disabled');
                        }
                    });
                };
                formCaptcha();
            }
            return true;
        }
        $("#" + elementId).append(data.htmlContent);
        var elemId = elementId.match(/\d+/);
        var _this = this;
        if (data.elementType == 'image') {
            setTimeout(function () {
                _this.imageSlider(elemId);
            }, 1000);
        }
        if (data.elementType == 'text') {
            setTimeout(function () {
                _this.textImageSlider(elemId);
            }, 1000);
        }
        if (data.elementType == 'login') {
            this.handleLoginButtonsClick(elementId, data.htmlContent);
        }
        if (data.elementType == 'calendar') {
            this.handleCalendarClicks(elementId);
        }
        this.makeFooterSticky();
    };
    /**
     * //make footer sticky
     */
    Fgwebsitepage.prototype.makeFooterSticky = function () {
        setTimeout(function () {
            var hasAdmin = $('body').hasClass('fg-has-admin-nav'); // checking it page has admin nav
            var hasFixedheader = $('body').hasClass('fg-header-sticky'); // checking page has fixed header
            var headerHeight = $('.fg-web-page-header').outerHeight();
            var contentHeight = $('.fg-web-main-content').outerHeight();
            var footerHeight = $('.fg-web-page-footer').outerHeight();
            var winHeight = $(window).height();
            var winWidth = $(window).width();
            if (winWidth > 767) {
                var totHeight = headerHeight + contentHeight + footerHeight;
                if (winHeight > totHeight) {
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
                    $('body').css('padding-top', totHeaderHeight);
                }
            }
        }, 1000);
    };
    /**
     * Redirect calendar list clicks to detail page
     */
    Fgwebsitepage.prototype.handleCalendarClicks = function () {
        $('.fg-dev-calendar-detail').on('click', function () {
            var hrefUrl = $(this).attr('data-href').replace('NAV_IDENTIFIER', menu);
            window.location.href = hrefUrl;
        });
    };
    /**
    * Handle form element submit
    */
    Fgwebsitepage.prototype.handleFormElementSubmit = function (elementId) {
        $('#' + elementId + ' .fg-form-element-submit').on('click', function () {
            var formId = $(this).parents('form').attr('id');
            var validObj = new FgWebsiteFormValidation(formId);
            validObj.validateForm();
        });
    };
    Fgwebsitepage.prototype.handleTimePicker = function (elementId) {
        var timeFormatData = {};
        timeFormatData['hh:ii'] = { format: 'hh:mm', seperator: ':' };
        timeFormatData['hh.ii'] = { format: 'hh.mm', seperator: '.' };
        timeFormatData['hh ## ii'] = { format: 'hh h mm', seperator: ' h ' };
        timeFormatData['HH:ii P'] = { format: 'hh:mm AA', seperator: ':' };
        var currentTimeFormat = timeFormatData[FgLocaleSettingsData.jqueryDtimeFormat];
        $('#' + elementId + ' [data-timepick]').each(function () {
            parentDiv = $(this).attr('id');
            $('#' + parentDiv + ' .fg-timepicker').DateTimePicker({
                mode: 'time',
                isPopup: false,
                timeFormat: currentTimeFormat.format,
                setValueInTextboxOnEveryClick: true,
                buttonsToDisplay: [],
                timeSeparator: currentTimeFormat.seperator,
                incrementButtonContent: '<i class="fa fa-angle-up fa-2x"></i>',
                decrementButtonContent: '<i class="fa fa-angle-down fa-2x"></i>',
                parentElement: "#" + parentDiv,
                minuteInterval: 5
            });
        });
    };
    /**
     * Handle form element submit
     */
    Fgwebsitepage.prototype.handleFormFileUpload = function (elementId) {
        $("#" + elementId + " input[type=file]").each(function () {
            $(this).fileupload({
                dataType: 'json',
                add: function (e, data) {
                    $(this).parent().find('input[data-file]').val('');
                    itemId = $.now();
                    $("#" + elementId).find('.fg-form-element-submit').attr('disabled', true);
                    var fileName = data.files[0].name;
                    fileName = fileName.replace(/[&\/\\#,+()$~%'"`^=|:;*?<>{}]/g, '');
                    fileName = fileName.replace(/ /g, '-');
                    fileName = itemId + '--' + fileName;
                    data.formData = { title: fileName, nowtime: itemId };
                    var jqXHR = data.submit();
                },
                done: function (e, data) {
                    $(this).parent().find('input[data-file]').val(data.formData.nowtime + '#-#' + data.formData.title);
                    $("#" + elementId).find('.fg-form-element-submit').removeAttr('disabled');
                }
            });
        });
    };
    Fgwebsitepage.prototype.handleToolTip = function (elementId) {
        thisClass = this;
        $("#" + elementId + " label[data-content]").each(function () {
            if ($(this).attr('data-content') != '') {
                $(this).addClass('fg-custom-popovers');
            }
        });
        $('body').on('mouseover click', '.fg-custom-popovers', function (e) {
            var _this = $(this), thisContent = _this.data('content'), posLeft = _this.offset().left - 10, posTop = _this.offset().top + 50;
            thisClass.showTooltip({ element: e, content: thisContent, position: [posLeft, posTop] });
            $('.popover .popover-content').width($('.popover').width() - 27);
        });
        $('body').on('mouseout', '.fg-custom-popovers', function () {
            $('body').find('.custom-popup').hide();
            $('.popover .popover-content').width('');
        });
    };
    Fgwebsitepage.prototype.showTooltip = function (obj) {
        var targetElement = $('body').find('.custom-popup'), elementContent = targetElement.find('.popover-content');
        elementContent.html(obj.content);
        targetElement.css({ 'left': obj.position[0], 'top': obj.position[1] });
        targetElement.show();
    };
    Fgwebsitepage.prototype.renderPage = function (jsonData) {
        return FGTemplate.bind(this.settings.boxTemplateId, jsonData);
    };
    Fgwebsitepage.prototype.containerColumns = function () {
        var columnHtml = '';
        var columnId = '';
        var _this = this;
        this.settings.container.data.columns = _.sortBy(this.settings.container.data.columns, 'sortOrder');
        _.each(this.settings.container.data.columns, function (columnValues, index) {
            //create column box id
            columnId = 'containercolumn-' + columnValues.columnId;
            //create columns
            _this.settings.column.data = columnValues;
            columnHtml += _this.renderColumnBox(columnId);
        });
        this.settings.initColumnCallback.call();
        return columnHtml;
    };
    Fgwebsitepage.prototype.columnBox = function () {
        var boxHtml = '';
        var _this = this;
        var boxId = '';
        this.settings.column.data.box = _.sortBy(this.settings.column.data.box, 'sortOrder');
        _.each(this.settings.column.data.box, function (boxValues, index) {
            //create box id
            boxId = 'columnbox-' + boxValues.boxId;
            //create box
            _this.settings.columnbox.data = boxValues;
            boxHtml += _this.renderBox(boxId);
            //append box to specified columns
        });
        this.settings.initColumnBoxCallback.call();
        return boxHtml;
    };
    Fgwebsitepage.prototype.elementBox = function () {
        var elementHtml = '';
        var _this = this;
        var elementId = '';
        this.settings.columnbox.data.Element = _.sortBy(this.settings.columnbox.data.Element, 'sortOrder');
        _.each(this.settings.columnbox.data.Element, function (elementValues, index) {
            elementId = 'elementbox-' + elementValues.elementId;
            _this.settings.elementbox.data = elementValues;
            elementHtml += _this.renderElement(elementId);
        });
        this.settings.initElementBoxCallback.call();
        return elementHtml;
    };
    Fgwebsitepage.prototype.pageContainer = function (containerDetails) {
        var containerHtml = '';
        var _this = this;
        var containerId = '';
        this.settings.data.page.container = _.sortBy(this.settings.data.page.container, 'sortOrder');
        _.each(this.settings.data.page.container, function (containerValues, index) {
            //crea t e container box id
            containerId = 'pagecontainer-' + containerValues.containerId;
            //create container
            _this.settings.container.data = containerValues;
            containerHtml += _this.renderContainerBox(containerId);
        });
        _this.settings.initContainerCallback.call();
        return containerHtml;
    };
    Fgwebsitepage.prototype.sidebarInit = function () {
        var containerDetails = jsonData.sidebar.page.container;
        this.settings.data.page.container = jsonData.sidebar.page.container;
        this.settings.containerType = 'sidebar';
        this.settings.sidebarSide = (_.size(this.settings.data.sidebar) > 0) ? this.settings.data.sidebar.side : '';
        // To set the sidebar size
        this.settings.sidebarSize = (_.size(this.settings.data.sidebar) > 0) ? this.settings.data.sidebar.width_value : '';
        var pageHtml = this.pageContainer(containerDetails);
        return pageHtml;
    };
    Fgwebsitepage.prototype.contentInit = function () {
        var containerDetails = jsonData.page.page.container;
        this.settings.data.page.container = jsonData.page.page.container;
        this.settings.containerType = 'content';
        var pageHtml = this.pageContainer(containerDetails);
        return pageHtml;
    };
    Fgwebsitepage.prototype.footerInit = function () {
        var containerDetails = jsonData.footer.page.container;
        this.settings.data.page.container = jsonData.footer.page.container;
        this.settings.containerType = 'footer';
        var pageHtml = this.pageContainer(containerDetails);
        return pageHtml;
    };
    Fgwebsitepage.prototype.appendContent = function (appendObj, pageContent) {
        $(appendObj).html(pageContent);
        this.settings.pageInitCallback.call();
    };
    Fgwebsitepage.prototype.columnWidthCalculation = function (currentContainer) {
        var _this = this;
        var totalWidth = 6;
        var containerIdString = currentContainer.attr('id').split("-");
        var currentContainerId = containerIdString[1];
        if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'wide') {
            totalWidth = 2;
        }
        else if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'small') {
            totalWidth = 1;
        }
        else if (this.settings.data.sidebar.size == 'wide') {
            totalWidth = 4;
        }
        else if (this.settings.data.sidebar.size == 'small') {
            totalWidth = 5;
        }
        var calculatedWidth = 0;
        _.each(currentContainer.find('.rowColumn'), function (value, key) {
            calculatedWidth = calculatedWidth + parseInt($(value).attr('column-size'));
            if (parseInt($(value).attr('column-size')) > 1) {
                //decrease button
                $(value).find(".fg-left").show();
            }
        });
        if (calculatedWidth < totalWidth) {
            //set increase button to all column
            $(currentContainer).find(".fg-right").show();
        }
    };
    Fgwebsitepage.prototype.getMaxColumnCount = function () {
        var totalColumnCount = 6;
        if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'wide') {
            totalColumnCount = 2;
        }
        else if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'small') {
            totalColumnCount = 1;
        }
        else if (this.settings.data.sidebar.size == 'wide') {
            totalColumnCount = 4;
        }
        else if (this.settings.data.sidebar.size == 'small') {
            totalColumnCount = 5;
        }
        return totalColumnCount;
    };
    //ajax call for form submission
    Fgwebsitepage.prototype.requestCall = function (columnDetails) {
        FgXmlHttp.post(pageDetailSavePath, {
            'postArr': columnDetails,
            'pageDetails': JSON.stringify(jsonData)
        }, false, this.callBackFn);
    };
    Fgwebsitepage.prototype.boxSort = function () {
        //BOX SORT
        var fromColumn = '';
        $('.contentRow').disableSelection();
        var currentSortOrder = '';
        var _this = this;
        var boxsortoption = {
            connectWith: '.rowColumn',
            items: "> div.columnBox",
            handle: ".fg-dev-box-draggable",
            helper: "clone",
            cursorAt: { top: 40, left: 40 },
            start: function (event, ui) {
                fromColumn = $(ui.item).parent().attr('column-id');
                currentSortOrder = $(ui.item).index() + 1;
                ui.item.show().addClass('original-placeholder');
                $(ui.helper).append('<div class="fg-drag-holder-item"><i class="fa fa-square-o" aria-hidden="true"></i><div class="fg-text">' + _this.settings.translations.dragBoxTitle + '</div></div>');
            },
            stop: function (event, ui) {
                $(ui.helper).find(".fg-drag-holder-item").remove();
                $("#boxCreationForm").find("input").addClass('fairgatedirty');
                var sortElementDetails = {};
                sortElementDetails['toColumn'] = $(ui.item).parent().attr('column-id');
                sortElementDetails['fromColumn'] = fromColumn;
                if (typeof sortElementDetails['toColumn'] != 'undefined' && fromColumn != 'undefined') {
                    var pageId = jsonData.page.id;
                    var dataKey = $("#createBox").attr('data-key');
                    var oldKey = dataKey;
                    dataKey += '.sortBox';
                    $("#createBox").attr('data-key', dataKey);
                    sortElementDetails['boxId'] = $(ui.item).attr('box-id');
                    sortElementDetails['sortOrder'] = ($(ui.item).index()) + 1;
                    sortElementDetails['currentSortOrder'] = currentSortOrder;
                    var objectGraph = {};
                    objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                    objectGraph["page"][pageId]["sortBox"] = sortElementDetails;
                    var columnDetails = JSON.stringify(objectGraph);
                    $("#createBox").attr('data-key', oldKey);
                    FgXmlHttp.post(pageDetailSavePath, {
                        'postArr': columnDetails,
                        'pageDetails': JSON.stringify(jsonData)
                    }, false, _this.callBackFn);
                }
            }
        };
        this.sortableEvent('.rowColumn', boxsortoption);
    };
    Fgwebsitepage.prototype.mapGeneration = function () {
        //MAP GENERATING CODE
        $('.columnBox .fg-dev-map-element').each(function (i, value) {
            var elementId = $(value).attr('element-id');
            var mapDisplay = $("#mapDisplay-" + elementId).val().toUpperCase();
            ;
            var latitude = $("#latitude-" + elementId).val();
            var longitude = $("#longitude-" + elementId).val();
            var mapMarker = $("#mapMarker-" + elementId).val();
            var mapZoom = parseInt($("#mapZoom-" + elementId).val());
            var mapId = "googleMap-" + elementId;
            FgMapSettings.mapShow(latitude, longitude, mapDisplay, mapZoom, mapMarker, mapId, '');
        });
    };
    Fgwebsitepage.prototype.imageSlider = function (elementId) {
        var _this = this;
        var option3 = {
            gallery_theme: "slider",
            tile_enable_action: false,
            tile_enable_overlay: false,
            gallery_play_interval: 0,
            slider_enable_play_button: false,
            slider_enable_bullets: true,
            slider_enable_progress_indicator: false,
            lightbox_show_numbers: false,
            slider_enable_text_panel: true,
            slider_textpanel_enable_title: false,
            slider_control_zoom: false,
            gallery_min_width: 100,
            slider_textpanel_padding_top: 0,
            slider_textpanel_padding_bottom: 0,
            slider_transition: "fade",
            slider_transition_speed: 1000
        };
        if ($("#row-gallery-" + elementId).length > 0) {
            var option1 = {
                tiles_type: "justified",
                lightbox_show_numbers: false,
                tile_as_link: false,
                tile_enable_textpanel: true,
                gallery_min_width: 100
            };
            var viewType = $("#row-gallery-" + elementId).attr('data-image_view_type');
            var imageType = $("#row-gallery-" + elementId).children('img').attr('data-image-type');
            if (viewType == 'link' && imageType != 'VIDEO') {
                option1.tile_as_link = true;
                option1.tile_enable_textpanel = false;
            }
            _this.unitgalleryCall("#row-gallery-" + elementId, option1);
        }
        if ($("#column-gallery-" + elementId).length > 0) {
            var option2 = {
                lightbox_show_numbers: false,
                tile_as_link: false,
                tile_enable_textpanel: true,
                gallery_min_width: 100
            };
            var viewType = $("#column-gallery-" + elementId).attr('data-image_view_type');
            var imageType = $("#column-gallery-" + elementId).children('img').attr('data-image-type');
            if (viewType == 'link' && imageType != 'VIDEO') {
                option2.tile_as_link = true;
                option2.tile_enable_textpanel = false;
            }
            _this.unitgalleryCall("#column-gallery-" + elementId, option2);
        }
        if ($("#slider-gallery-" + elementId).length > 0) {
            var sliderTime = $("#slider-gallery-" + elementId).attr('data-slider-time');
            option3.gallery_play_interval = sliderTime * 1000;
            _this.unitgalleryCall("#slider-gallery-" + elementId, option3);
        }
    };
    Fgwebsitepage.prototype.textImageSlider = function (elementId) {
        var _this = this;
        var singleImageOption = {
            tiles_type: "justified",
            lightbox_show_numbers: false,
            tile_enable_textpanel: true,
            gallery_min_width: 100
        };
        var sliderOption = {
            gallery_theme: "slider",
            tile_enable_action: false,
            tile_enable_overlay: false,
            slider_enable_play_button: false,
            slider_enable_bullets: true,
            slider_enable_progress_indicator: false,
            lightbox_show_numbers: false,
            slider_enable_text_panel: true,
            slider_textpanel_enable_title: false,
            slider_control_zoom: false,
            gallery_min_width: 100
        };
        if ($("#row-gallery-" + elementId).length > 0) {
            _this.unitgalleryCall("#row-gallery-" + elementId, singleImageOption);
        }
        if ($("#gallery-textelement-" + elementId).length > 0) {
            _this.unitgalleryCall("#gallery-textelement-" + elementId, sliderOption);
        }
    };
    Fgwebsitepage.prototype.unitgalleryCall = function (identifier, slideroptions) {
        $(identifier).unitegallery(slideroptions);
    };
    //INITIALIZE PAGE
    Fgwebsitepage.prototype.pagedocInit = function () {
        this.initSettings(cmsOptions);
        if (_.size(jsonData.page) > 0) {
            if (_.size(jsonData.page.page) > 0) {
                var pagecontent = this.contentInit();
                this.appendContent(cmsOptions.mainContainer, pagecontent);
            }
        }
        if (_.size(jsonData.sidebar) > 0) {
            var sidebarContent = this.sidebarInit();
            this.appendContent(cmsOptions.sideContainer, sidebarContent);
        }
        if (_.size(jsonData.footer) > 0) {
            var footerContent = this.footerInit();
            this.appendContent(cmsOptions.footerContainer, footerContent);
        }
    };
    Fgwebsitepage.prototype.pageCallBackFunction = function () {
        //  ADD/EDIT CONTAINER POP UP
        // MAP GENERATING CODE
        this.mapGeneration();
        //IMAGE SLIDER
    };
    /**
     * Handle login and logout buttons click  with parameters as elementId & initialHtmlContent
     */
    Fgwebsitepage.prototype.handleLoginButtonsClick = function (elementId, initialHtmlContent) {
        //make remember checkbox uniform
        $('.uniform').uniform();
        var _this = this;
        //login button click
        $("#" + elementId).find('.fg-dev-login-btn').on("click", function (e) {
            var thisObj = $(this);
            e.preventDefault();
            $.ajax({
                method: 'post',
                url: loginPath,
                data: thisObj.parents('form').serialize(),
                success: function (data, status, object) {
                    if (data.success) {
                        location.reload();
                    }
                    if (data.error) {
                        thisObj.parents('form').find('.fg-dev-alert-div').removeClass('hide').find('.fg-dev-alert-span').html(data.error);
                    }
                },
                error: function (data, status, object) {
                }
            });
        });
        //logout button click
        $("#" + elementId).find('.fg-dev-logout-btn').on("click", function (e) {
            e.preventDefault();
            $.ajax({
                method: 'post',
                url: logoutPath,
                success: function (data, status, object) {
                    if (data.logout_success) {
                        location.reload();
                    }
                }
            });
        });
        //forgot password
        $("#" + elementId).find('.fg-dev-forgot-password').on("click", function () {
            _this.renderForgotPasswordTemplate(elementId, initialHtmlContent, 'forgotPassword');
        });
        //activate account
        $("#" + elementId).find('.fg-dev-activate-login').on("click", function () {
            _this.renderForgotPasswordTemplate(elementId, initialHtmlContent, 'activateLogin');
        });
    };
    /**
     * Function to render login password template / activate login template
     * @param string elementId
     * @param string initialHtmlContent
     * @param string templateName (forgotPassword/activateLogin)
     */
    Fgwebsitepage.prototype.renderForgotPasswordTemplate = function (elementId, initialHtmlContent, templateName) {
        var htmlFinal = FGTemplate.bind('templateLoginForgotPassword', { 'elementId': elementId, 'templateName': templateName });
        $("#" + elementId).html(htmlFinal);
        var captchaContainer = null;
        var loadCaptcha = function () {
            captchaContainer = grecaptcha.render('fg-captcha' + elementId, {
                'sitekey': sitekey,
                'callback': function (response) {
                    $("#" + elementId).find('.fg-dev-activate-submit').removeAttr('disabled');
                }
            });
        };
        loadCaptcha(); // THIS LINE WAS MISSING
        this.handleForgotPasswordSubmit(elementId, initialHtmlContent);
        this.handleBackToLoginButton(elementId, initialHtmlContent);
    };
    /*
     * Handle handle BackToLoginButton
     */
    Fgwebsitepage.prototype.handleBackToLoginButton = function (elementId, initialHtmlContent) {
        var _this = this;
        //back to login button
        $("#" + elementId).find('.fg-dev-back-button').on("click", function () {
            $("#" + elementId).html(initialHtmlContent);
            _this.handleLoginButtonsClick(elementId, initialHtmlContent);
        });
    };
    /*
     * Forgot password submit button
     */
    Fgwebsitepage.prototype.handleForgotPasswordSubmit = function (elementId, initialHtmlContent) {
        var _this = this;
        //forgot password send button 
        $("#" + elementId).find('.fg-dev-activate-submit').off('click');
        $("#" + elementId).find('.fg-dev-activate-submit').on("click", function (e) {
            var thisObj = $(this);
            thisObj.prop("disabled", true);
            e.preventDefault();
            $.ajax({
                method: 'post',
                url: sendEmailPath,
                data: $("#" + elementId).find('form').serialize(),
                success: function (data, status, object) {
                    thisObj.prop("disabled", false);
                    if (data.emailSendSuccess || data.passwordAlreadyRequested) {
                        var htmlFinal = FGTemplate.bind('templateLoginForgotPasswordSuccess', { 'messages': data.messages });
                        $("#" + elementId).html(htmlFinal);
                        _this.handleBackToLoginButton(elementId, initialHtmlContent);
                    }
                    if (data.error) {
                        _this.renderForgotPasswordTemplate(elementId, initialHtmlContent, 'forgotPassword');
                        $("#" + elementId).find('.fg-dev-alert-div').removeClass('hide').find('.fg-dev-alert-span').html(data.error);
                    }
                    if (data.errorActivateAccount) {
                        _this.renderForgotPasswordTemplate(elementId, initialHtmlContent, 'activateLogin');
                        $("#" + elementId).find('.fg-dev-alert-div').removeClass('hide').find('.fg-dev-alert-span').html(data.errorActivateAccount);
                    }
                }
            });
        });
    };
    // Function to get the video details for website
    Fgwebsitepage.prototype.getCmsVideoDetails = function (videoUrl, el) {
        var vDet = FgVideoThumbnail.getVideoId(videoUrl);
        var vType = (vDet.type == 'y') ? 'youtube' : ((vDet.type == 'v') ? 'vimeo' : '');
        $(el).attr('data-type', vType);
        $(el).attr('data-videoid', vDet.id);
    };
    return Fgwebsitepage;
}());
