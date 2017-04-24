/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
var Fgcmspage = (function () {
    function Fgcmspage() {
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
            ClubId: '',
            languages: '',
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
        this.callSidePopup();
    }
    Fgcmspage.prototype.callSidePopup = function () {
        var _this = this;
        $("#fg-cms-remove-sidebar").on("click", function (event) { return _this.excludeSideColumn(event); });
        this.closeSidebar();
        $("#fg-cms-show-sidebar").on("click", function (event) { return _this.showPopup(); });
        this.closeSidePopup();
        $("#saveSidebarBtn").on("click", function (event) { return _this.saveSidebar(); });
        this.closesaveSidebar();
        $(".fg-side-col-wrapper li.fg-side-col-layout").on("click", function (e) { return _this.selectSideColumn(e); });
    };
    Fgcmspage.prototype.excludeSideColumn = function (event) {
        var pageId = $("#fg-cms-remove-sidebar").attr('data-pageid');
        FgXmlHttp.post(sidebarPagePath, { 'cmsPageId': pageId, 'cmsPageSidebarAction': 'exclude' }, function (response) { });
    };
    Fgcmspage.prototype.closeSidebar = function () {
        $(document).off('click', '#saveSidebarBtn');
    };
    Fgcmspage.prototype.closeSidePopup = function () {
        $(document).off('click', '#fg-cms-show-sidebar');
    };
    Fgcmspage.prototype.closesaveSidebar = function () {
        $(document).off('click', '#saveSidebarBtn');
    };
    Fgcmspage.prototype.selectSideColumn = function (e) {
        $('.fg-side-col-wrapper li.fg-side-col-layout div.fg-side-col-layout-inner').removeClass('active');
        $(e.currentTarget).find('.fg-side-col-layout-inner').addClass('active');
        $('#cmsSidebarType').val($(e.currentTarget).attr('data-sbType'));
        $('#cmsSidebarArea').val($(e.currentTarget).attr('data-sbArea'));
    };
    Fgcmspage.prototype.showPopup = function () {
        $('#fg-cms-page-sidebar').modal('show');
    };
    Fgcmspage.prototype.saveSidebar = function () {
        $('#cmsPageSidebarAction').val('include');
        var data = $('#cms_sidebar_page_form').serializeArray();
        FgXmlHttp.post(sidebarPagePath, data, false, '');
    };
    Fgcmspage.prototype.initSettings = function (options) {
        this.settings = $.extend(true, {}, this.defaultSettings, options);
    };
    Fgcmspage.prototype.renderContainerBox = function (containerId) {
        if (_.size(this.settings.container.data) > 0) {
            //render all columns of particular container
            var columnContent = this.containerColumns();
            return FGTemplate.bind(this.settings.container.templateId, { details: this.settings.container.data, containerid: containerId, columnDetails: columnContent, pageId: this.settings.data.page.id });
        }
    };
    Fgcmspage.prototype.renderColumnBox = function (columnId) {
        //render all column box
        var boxContent = this.columnBox();
        return FGTemplate.bind(this.settings.column.templateId, { details: this.settings.column.data, columnid: columnId, settingDetails: this.settings, boxDetails: boxContent });
    };
    Fgcmspage.prototype.renderBox = function (boxId) {
        //render all the box element
        var elementContent = this.elementBox();
        return FGTemplate.bind(this.settings.columnbox.templateId, { details: this.settings.columnbox.data, boxid: boxId, elementDetails: elementContent });
    };
    Fgcmspage.prototype.renderElement = function (elementId, params) {
        var _this = this;
        if (this.settings.elementbox.data.isAjax !== true) {
            return FGTemplate.bind(this.settings.elementbox.templateId[this.settings.elementbox.data.elementType], { details: this.settings.elementbox.data, elementid: elementId, settingDetails: this.settings });
        }
        else {
            if (this.settings.elementbox.data.elementType == 'supplementary-menu') {
                this.settings.elementbox.data.ajaxURL = supplymenteryDataUrl;
            }
            // ajax call of each element and render this one                    
            $.post(this.settings.elementbox.data.ajaxURL, function (data) {
                _this.insertElement(elementId, data, params);
            });
            return FGTemplate.bind(this.settings.elementbox.templateId[this.settings.elementbox.data.elementType], { details: this.settings.elementbox.data, elementid: elementId, settingDetails: this.settings });
        }
    };
    Fgcmspage.prototype.insertElement = function (elementId, dataHtml, params) {
        var _this = this;
        if (params.type == 'form') {
            dataHtml = FGTemplate.bind("templateFormField", { formDetails: dataHtml.formData, defLang: dataHtml.defLang });
            $("#" + elementId).append(dataHtml);
            $("#" + elementId + " input:checkbox,#" + elementId + " input:radio").uniform();
            $("#" + elementId + " :file").filestyle({ iconName: "fa fa-file" });
            return true;
        }
        $("#" + elementId).append(dataHtml);
        if (params.type == 'image') {
            _this.imageElementOptions(params.id);
        }
        if (params.type == 'text') {
            _this.textElementOptions(params.id);
        }
    };
    Fgcmspage.prototype.renderPage = function (jsonData) {
        return FGTemplate.bind(this.settings.boxTemplateId, jsonData);
    };
    Fgcmspage.prototype.pageContainer = function () {
        var containerDetails = this.settings.data.page.container;
        if (this.settings.containerType == 'sidebar' && _.size(this.settings.data.sidebar) > 0) {
            containerDetails = this.settings.data.sidebar.container;
            // To set the side of sidebar that display
            this.settings.sidebarSide = (_.size(this.settings.data.sidebar) > 0) ? this.settings.data.sidebar.side : '';
            // To set the sidebar size
            this.settings.sidebarSize = (_.size(this.settings.data.sidebar) > 0) ? this.settings.data.sidebar.width_value : '';
        }
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
    Fgcmspage.prototype.containerColumns = function () {
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
    Fgcmspage.prototype.columnBox = function () {
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
    Fgcmspage.prototype.elementBox = function () {
        var elementHtml = '';
        var _this = this;
        var elementId = '';
        this.settings.columnbox.data.Element = _.sortBy(this.settings.columnbox.data.Element, 'sortOrder');
        _.each(this.settings.columnbox.data.Element, function (elementValues, index) {
            elementId = 'elementbox-' + elementValues.elementId;
            _this.settings.elementbox.data = elementValues;
            var params = { 'type': elementValues.elementType, 'id': elementValues.elementId };
            elementHtml += _this.renderElement(elementId, params);
        });
        this.settings.initElementBoxCallback.call();
        return elementHtml;
    };
    Fgcmspage.prototype.sidebarInit = function () {
        var pageHtml = this.pageContainer();
        return pageHtml;
    };
    Fgcmspage.prototype.contentInit = function () {
        var pageHtml = this.pageContainer();
        return pageHtml;
    };
    Fgcmspage.prototype.appendContent = function (pageContent) {
        $(this.settings.mainContainer).html(pageContent);
        this.settings.pageInitCallback.call();
    };
    Fgcmspage.prototype.sortableEvent = function (identifier, sortoptions) {
        this.sortSetting = $.extend(true, {}, this.defaultSortOptions, sortoptions);
        $(identifier).sortable(this.sortSetting);
    };
    Fgcmspage.prototype.columnWidthCalculation = function (currentContainer) {
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
    Fgcmspage.prototype.createContainer = function (pageId, containerId) {
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
        //FGTemplate.bind('createContainer', { totalCount: totalColumnCount, currentColumnCount: 1,containerId:containerId });
    };
    Fgcmspage.prototype.hideRemoveButton = function () {
        if (this.settings.containerType == 'content' && _.size(this.settings.data.page.container) <= 1) {
            $(".contentRow .fg-dev-delete").hide();
        }
        else if (this.settings.containerType == 'sidebar' && _.size(this.settings.data.page.container) <= 1) {
            $(".contentRow .fg-dev-delete").hide();
        }
    };
    Fgcmspage.prototype.getMaxColumnCount = function () {
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
    //clip board creation
    Fgcmspage.prototype.callClipBoard = function (data) {
        var htmlFinal = FGTemplate.bind('globalClipboard', { clipBoardData: data });
        $('#fg-clipBoard-section').html('');
        $('#fg-clipBoard-section').append(htmlFinal);
        //CLIPPBOARD DRAGGABLE                   
        $(".fg-clipboard-item").draggable({
            connectToSortable: ".columnBox",
            helper: "clone",
            start: function (event, ui) {
                $(".fg-drop-holder").addClass("no-hover");
                $('body').addClass("fg-dev-drag-active");
                ui.helper.addClass('fg-dragging-element');
            },
            stop: function (event, ui) {
                $(".fg-drop-holder").removeClass("no-hover");
                $('body').removeClass("fg-dev-drag-active");
                ui.helper.addClass('hide').removeClass('fg-dragging-element');
            }
        });
        this.clipboardSlimScroll();
    };
    //slim scroll init in clip board
    Fgcmspage.prototype.clipboardSlimScroll = function () {
        $('.fg-clipboard-element-wrapper').slimScroll({
            height: '170px',
            width: '100%'
        });
    };
    // Callback function after save
    Fgcmspage.prototype.callBackFn = function (result) {
        cmspage.saveJsonData();
        switch (result.type) {
            case 'newContainer':
            case 'newColumn':
            case 'deleteColumn':
            case 'columnWidthChange':
            case 'newBox':
            case 'deleteContainer':
            case 'sortContainer':
            case 'deleteBox':
            case 'sortBox':
            case 'sortElement':
            case 'deleteElement':
                jsonData = result.data;
                cmspage.reloadData();
                break;
            case 'remove':
            case 'moveFromClipboard':
                jsonData = result.data;
                cmspage.reloadData();
                cmspage.callClipBoard(result.clipboardData);
                break;
        }
    };
    //ajax call for form submission
    Fgcmspage.prototype.requestCall = function (columnDetails) {
        FgXmlHttp.post(pageDetailSavePath, {
            'postArr': columnDetails,
            'pageDetails': JSON.stringify(jsonData)
        }, false, this.callBackFn);
    };
    // Reload existing page 
    Fgcmspage.prototype.reloadData = function () {
        options.data = jsonData;
        cmspage.initSettings(options);
        var pagecontent = cmspage.contentInit();
        cmspage.appendContent(pagecontent);
    };
    // EDIT BOX OVERLAY GENERATION  
    Fgcmspage.prototype.boxOverlay = function () {
        $("body").on('click', ".fg-dev-box-edit", function (event) {
            event.stopImmediatePropagation();
            $('.fg-widget-hover-column-wrapper').show();
            $('.fg-widget-dropmenu-options').hide();
            $('.fg-widget-block').removeClass('open-widget-options');
            var elementId = $(this).attr('element-id');
            var columnId = $('#elementbox-' + elementId).parents().eq(1).attr("id");
            $('#elementbox-' + elementId).parent().addClass("open-block-options");
        });
        //EMPTY BOX OVERLAY GENERATION
        $(document).on({
            mouseenter: function (event) {
                event.stopPropagation();
                $(".fg-drop-holder").removeClass("open-block-options");
                $('.fg-widget-dropmenu-options').hide();
                $('.fg-widget-block').removeClass('open-widget-options');
                $(this).addClass("open-block-options");
            },
            mouseleave: function () {
                $(this).removeClass("open-block-options");
            }
        }, '.fg-empty-drop-holder');
    };
    Fgcmspage.prototype.deleteBox = function () {
        //DELETE BOX
        var _this = this;
        $("body").on('click', ".fg-dev-box-remove", function (event) {
            event.stopImmediatePropagation();
            $("#boxCreationForm").find("input").addClass('fairgatedirty');
            var dataKey = $("#createBox").attr('data-key');
            var oldDataKey = dataKey;
            var boxId = $(this).attr('box-id');
            var columnId = $("#columnbox-" + boxId).parent().attr("column-id");
            var containerId = $("#columnbox-" + boxId).parents().eq(1).attr('container-id');
            var pageId = $(this).attr('page-id');
            dataKey += '.container.' + containerId + '.column.' + columnId + '.box.delete.id';
            if ($("#columnbox-" + boxId).find(".elementBox").length >= 1) {
                var message = _this.settings.translations.deleteBoxMsg;
                var popupContent = FGTemplate.bind('deletePopup', {
                    deleteId: boxId,
                    pageId: pageId,
                    dataKey: dataKey,
                    displaymessage: message,
                    headerTitle: _this.settings.translations.deleteBoxHeader
                });
                FgModelbox.showPopup(popupContent);
            }
            else {
                $("#createBox").attr('data-key', dataKey);
                $("#createBox").val(boxId);
                var objectGraph = {};
                objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                //parse the all form field value as json array and assign that value to the array
                var columnDetails = JSON.stringify(objectGraph);
                $("#createBox").attr('data-key', oldDataKey);
                FgXmlHttp.post(pageDetailSavePath, {
                    'postArr': columnDetails
                }, false, _this.callBackFn);
            }
        });
    };
    Fgcmspage.prototype.elementOverlay = function () {
        //ELEMENT EDIT OVERLAY  
        $(window).click(function () {
            $('.fg-widget-dropmenu-options').hide(); //Hide the menus if visible
            $('.fg-widget-block').removeClass('open-widget-options');
            $('.fg-drop-holder').removeClass('open-block-options');
        });
    };
    //ACTION MENU POSITION SETTING 
    Fgcmspage.prototype.setMenuPosition = function () {
        $('.fg-cms-page-elements-container').on('click', '.fg-widget-hover-elements-wrapper .fg-widget-block-options', function (event) {
            event.stopImmediatePropagation();
            var position = $(this).offset();
            var winWidth = $(window).width();
            var dropDownWidth = $('.fg-widget-dropmenu-options').outerWidth();
            $('.fg-widget-block').removeClass('open-widget-options');
            $(this).parents('.fg-widget-block').addClass('open-widget-options');
            if (winWidth > dropDownWidth + position.left + 20) {
                $('.fg-widget-dropmenu-options').show().css({
                    'top': position.top + 37,
                    'left': position.left
                });
            }
            else {
                $('.fg-widget-dropmenu-options').show().css({
                    'top': position.top + 37,
                    'left': position.left - dropDownWidth + 50
                });
            }
        });
    };
    // CREATE ACTION MENU FOR ELEMENT OVERLAY 
    Fgcmspage.prototype.actionMenuDisplay = function () {
        var _this = this;
        $('.fg-widget-hover-elements-wrapper .fg-widget-block-options').hover(function () {
            $("#elementActionmenu").empty();
            var elementId = $(this).attr('element-Id');
            var boxId = $("#elementbox-" + elementId).parent().attr("box-id");
            var liString = '<li class="fg-element-edit-actions" element-id=' + elementId + ' box-id=' + boxId + ' action-type="remove"><a href="javascript:void(0);" >' + _this.settings.translations.clipboardMovement + '</a></li><li class="fg-element-edit-actions" action-type="delete" element-id=' + elementId + ' box-id=' + boxId + '><a href="javascript:void(0);" >' + _this.settings.translations.deleteElement + '</a></li><li class="fg-dev-box-edit" element-id=' + elementId + '><a href="javascript:void(0);" >' + _this.settings.translations.editBox + '&hellip;</a></li>';
            $("#elementActionmenu").html(liString);
        });
    };
    Fgcmspage.prototype.boxSort = function () {
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
    Fgcmspage.prototype.elementSort = function () {
        // ELEMENT SORT
        $('.rowColumn').disableSelection();
        var fromBox = '';
        var currentSortOrder = '';
        var _this = this;
        var elementsortoption = {
            connectWith: '.columnBox',
            items: "> div.elementBox",
            handle: ".fg-dev-elementbox-draggable",
            helper: "clone",
            cursorAt: { top: 10, left: 10 },
            start: function (event, ui) {
                fromBox = $(ui.item).parent().attr('box-id');
                currentSortOrder = $(ui.item).index();
                var helperHtml = $(ui.item).find(".fg-elementtype-class").html();
                if (typeof helperHtml != 'undefined') {
                    $(ui.helper).append('<div class="fg-drag-holder-item">' + helperHtml + '</div>');
                }
            },
            stop: function (event, ui) {
                $(ui.helper).find(".fg-drag-holder-item").remove();
                var sortElementDetails = {};
                sortElementDetails['elementId'] = $(ui.item).attr('element-id');
                sortElementDetails['toBox'] = $(ui.item).parent().attr('box-id');
                sortElementDetails['currentSortOrder'] = currentSortOrder;
                var actionFrom = $(ui.item).attr('action-from') ? $(ui.item).attr('action-from') : 'content';
                var pageId = jsonData.page.id;
                var dataKey = $("#createBox").attr('data-key');
                $("#columnbox-" + sortElementDetails['toBox']).find('.fg-dev-drop-box-comment').remove();
                $("#boxCreationForm").find("input").addClass('fairgatedirty');
                var oldKey = dataKey;
                var objectGraph = {};
                var columnDetails = '';
                if (actionFrom == 'clipboard' && typeof sortElementDetails['toBox'] != 'undefined' && typeof sortElementDetails['elementId'] != 'undefined') {
                    dataKey += '.moveFromClipboard.elementId';
                    $("#createBox").attr('data-key', dataKey);
                    sortElementDetails['sortOrder'] = $(ui.item).index();
                    objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                    objectGraph["page"][pageId]["moveFromClipboard"]['elementId'] = sortElementDetails['elementId'];
                    objectGraph["page"][pageId]["moveFromClipboard"]['boxId'] = sortElementDetails['toBox'];
                    objectGraph["page"][pageId]["moveFromClipboard"]['sortOrder'] = sortElementDetails['sortOrder'];
                    columnDetails = JSON.stringify(objectGraph);
                    $("#createBox").attr('data-key', oldKey);
                    FgXmlHttp.post(pageDetailSavePath, {
                        'postArr': columnDetails,
                        'pageDetails': JSON.stringify(jsonData)
                    }, false, _this.callBackFn);
                }
                else if (typeof sortElementDetails['elementId'] != 'undefined' && typeof sortElementDetails['toBox'] != 'undefined') {
                    $("#columnbox-" + sortElementDetails['toBox']).find('.fg-dev-drop-box-comment').remove();
                    $("#columnbox-" + sortElementDetails['toBox']).removeClass('fg-empty-drop-holder');
                    $("#boxCreationForm").find("input").addClass('fairgatedirty');
                    dataKey += '.sortElement';
                    $("#createBox").attr('data-key', dataKey);
                    //$(".fg-dev-drop-box-comment")
                    sortElementDetails['fromBox'] = fromBox;
                    sortElementDetails['sortOrder'] = $(ui.item).index();
                    objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                    objectGraph["page"][pageId]["sortElement"] = sortElementDetails;
                    columnDetails = JSON.stringify(objectGraph);
                    $("#createBox").attr('data-key', oldKey);
                    FgXmlHttp.post(pageDetailSavePath, {
                        'postArr': columnDetails,
                        'pageDetails': JSON.stringify(jsonData)
                    }, false, _this.callBackFn);
                }
            }
        };
        this.sortableEvent('.columnBox', elementsortoption);
    };
    //CONTAINER SORT
    Fgcmspage.prototype.containerSort = function () {
        var startIndex = 0;
        var _this = this;
        var containerOption = {
            handle: ".fg-container-sortable",
            tolerance: 'pointer',
            start: function (event, ui) {
                ui.placeholder.height(ui.helper.outerHeight());
                startIndex = $(ui.item).index();
                $(ui.item).parents('.fg-cms-page-elements-container').addClass('fg-drag-start');
            },
            stop: function (event, ui) {
                $(ui.item).parents('.fg-cms-page-elements-container').removeClass('fg-drag-start');
                var stopIndex = $(ui.item).index();
                var selector = $(ui.item).parent().find(".contentRow");
                var containerArray = {};
                var i = 1;
                $(selector).each(function (index, value) {
                    var sortArray = {};
                    sortArray['sortOrder'] = i;
                    containerArray[$(value).attr('container-id')] = sortArray;
                    i++;
                });
                $("#boxCreationForm").find("input").addClass('fairgatedirty');
                var pageId = jsonData.page.id;
                var dataKey = $("#createBox").attr('data-key');
                var oldKey = dataKey;
                dataKey += '.container';
                $("#createBox").attr('data-key', dataKey);
                var objectGraph = {};
                objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                objectGraph["page"][pageId]["container"] = containerArray;
                var columnDetails = JSON.stringify(objectGraph);
                $("#createBox").attr('data-key', oldKey);
                if (startIndex != stopIndex) {
                    FgXmlHttp.post(pageDetailSavePath, {
                        'postArr': columnDetails,
                        'pageDetails': JSON.stringify(jsonData)
                    }, false, _this.callBackFn);
                }
            }
        };
        this.sortableEvent('.contentBox', containerOption);
    };
    // DELETE CONTAINER
    Fgcmspage.prototype.containerDelete = function () {
        var _this = this;
        $('body').on('click', '.fg-dev-delete', function (ele) {
            ele.stopImmediatePropagation();
            var containerId = $(this).attr('container-id');
            var dataKey = $("#createBox").attr('data-key');
            var pageId = $(this).attr('page-id');
            dataKey += '.container.delete.id';
            var elementCount = $("#pagecontainer-" + containerId).find(".elementBox").length;
            var message = (elementCount >= 1) ? _this.settings.translations.containerDeleteMsgWithElement : _this.settings.translations.containerDeleteMsgWithOutElement;
            var popupContent = FGTemplate.bind('deletePopup', {
                deleteId: containerId,
                pageId: pageId,
                dataKey: dataKey,
                displaymessage: message,
                headerTitle: _this.settings.translations.deleteContainerHeader
            });
            FgModelbox.showPopup(popupContent);
        });
    };
    //DELETE CONTAINER/BOX/ELEMENT ACTION FROM POP UP
    Fgcmspage.prototype.pagedataDelete = function () {
        var _this = this;
        $("body").on("click", "#fg-dev-container-delete", function (ele) {
            ele.stopImmediatePropagation();
            $("#deletePagePopup").find("input").addClass('fairgatedirty');
            var objectGraph = {};
            objectGraph = FgInternalParseFormField.formFieldParse('deletePagePopup');
            //parse the all form field value as json array and assign that value to the array
            var columnDetails = JSON.stringify(objectGraph);
            FgModelbox.hidePopup();
            FgXmlHttp.post(pageDetailSavePath, {
                'postArr': columnDetails
            }, false, _this.callBackFn);
        });
    };
    Fgcmspage.prototype.headerSort = function () {
        //ELEMENT HEADER SORTABLE
        var _this = this;
        //        $(".fg-dev-element-header .fg-dev-draggable-element").on('mouseover', function() {
        //            var boxsortoption = {
        //                connectWith: '.columnBox',
        //                items: "> div.elementBox"
        //            };
        //            _this.sortableEvent('.columnBox', boxsortoption);
        //        });
    };
    //ELEMENT HEADER DRAGGABLE
    Fgcmspage.prototype.elementDraggable = function () {
        var _this = this;
        $(".fg-dev-element-header .fg-dev-draggable-element").draggable({
            connectToSortable: ".columnBox",
            helper: "clone",
            cursorAt: { top: 10, left: 10 },
            start: function (event, ui) {
                $(".fg-drop-holder").addClass("no-hover");
                ui.helper.addClass('fg-dragging-element');
            },
            stop: function (event, ui) {
                ui.helper.addClass('hide').removeClass('fg-dragging-element');
                var boxId = $(ui.helper).parent().attr('box-id');
                if ($("#columnbox-" + boxId).find(".fg-dev-drop-box-comment").length >= 1) {
                    $("#droppedboxSortorder").val('1');
                }
                else {
                    $("#droppedboxSortorder").val(($(ui.helper).index()));
                }
                $("#columnbox-" + boxId).find(".fg-dev-drop-box-comment").remove();
                $(".fg-drop-holder").removeClass("no-hover");
                var elementType = $(ui.helper).attr('element-type');
                if (typeof boxId != 'undefined') {
                    $("#droppedboxId").val(boxId);
                    $("#elementType").val(elementType);
                    elementType = $("#elementType").val();
                    if (elementType === 'login') {
                        FgXmlHttp.post(addElementPagePath, {
                            'elementType': elementType,
                            'pageId': $("#droppedpageId").val(),
                            'boxId': $("#droppedboxId").val(),
                            'elementId': $("#elementId").val(),
                            'sortOrder': $("#droppedboxSortorder").val()
                        }, '', function (response) {
                            _this.settings.data = response.data;
                            jsonData = response.data;
                            _this.saveJsonData();
                            _this.reloadData();
                            _this.callClipBoard(response.clipboardData);
                            FgFormTools.handleUniform();
                        });
                    }
                    else if (elementType === 'form') {
                        FgXmlHttp.post(addElementPagePath, {
                            'elementType': elementType,
                            'pageId': $("#droppedpageId").val(),
                            'boxId': $("#droppedboxId").val(),
                            'elementId': $("#elementId").val(),
                            'sortOrder': $("#droppedboxSortorder").val()
                        }, '', function (response) {
                            if (response['formElements'].length > 0) {
                                var result_data = FGTemplate.bind('form-element-popup-data', { 'data': response['formElements'] });
                                $('.fg-form-element-data').append(result_data);
                                $('#fg-form-element-popup').modal('show');
                                $('.selectpicker').select2();
                            }
                            else {
                                $("#elementcreationForm").attr("action", formElementCreatePath);
                                $("#elementcreationForm").submit();
                            }
                        }, false, 'false');
                    }
                    else if (elementType === 'supplementary-menu') {
                        FgXmlHttp.post(addElementPagePath, {
                            'elementType': elementType,
                            'pageId': $("#droppedpageId").val(),
                            'boxId': $("#droppedboxId").val(),
                            'elementId': $("#elementId").val(),
                            'sortOrder': $("#droppedboxSortorder").val()
                        }, '', function (data) {
                            FgModelbox.showPopup(data);
                        }, false, 'false');
                    }
                    else {
                        $("#elementcreationForm").attr("action", addElementPagePath);
                        $("#elementcreationForm").submit();
                    }
                }
            }
        });
        //CLIPPBOARD DRAGGABLE
        $(".fg-clipboard-item").draggable({
            connectToSortable: ".columnBox",
            helper: "clone",
            start: function (event, ui) {
                $(".fg-drop-holder").addClass("no-hover");
                $('body').addClass("fg-dev-drag-active");
                ui.helper.addClass('fg-dragging-element');
            },
            stop: function (event, ui) {
                $(".fg-drop-holder").removeClass("no-hover");
                $('body').removeClass("fg-dev-drag-active");
                ui.helper.addClass('hide').removeClass('fg-dragging-element');
            }
        });
    };
    Fgcmspage.prototype.elementEdit = function () {
        $("body").on('click', '.fg-dev-edit-element', function (event) {
            event.stopImmediatePropagation();
            $("#droppedboxId").val('0');
            $("#elementType").val($(this).attr('element-type'));
            $("#droppedboxSortorder").val('0');
            $("#elementId").val($(this).attr('element-Id'));
            $("#droppedpageId").val($(this).attr('page-id'));
            $("#elementcreationForm").attr("action", addElementPagePath);
            //submit form
            $("#elementcreationForm").submit();
        });
    };
    Fgcmspage.prototype.supplementaryMenuCallback = function (response) {
        var _this = this;
        _this.settings = {};
        _this.settings.data = response.data;
        jsonData = response.data;
        _this.saveJsonData();
        _this.reloadData();
        _this.callClipBoard(response.clipboardData);
    };
    Fgcmspage.prototype.removeElement = function () {
        // DELETE/REMOVE ELEMENT BOX ACTION
        var _this = this;
        $("body").on("click", '.fg-element-edit-actions', function (event) {
            event.stopImmediatePropagation();
            $('.fg-widget-dropmenu-options').hide();
            $('.fg-widget-block').removeClass('open-widget-options');
            $("#boxCreationForm").find("input").addClass('fairgatedirty');
            //parse the all form field value as json array and assign that value to the array
            var elementId = $(this).attr('element-id');
            var actionType = $(this).attr('action-type');
            var boxId = $(this).attr('box-id');
            var pageId = jsonData.page.id;
            var dataKey = $("#createBox").attr('data-key');
            var oldKey = dataKey;
            var objectGraph = {};
            if (actionType == "delete") {
                dataKey += '.deleteElement.elementId';
                var message = _this.settings.translations.deleteElementMsg;
                var popupContent = FGTemplate.bind('deletePopup', {
                    deleteId: elementId,
                    pageId: pageId,
                    dataKey: dataKey,
                    displaymessage: message,
                    headerTitle: _this.settings.translations.deleteElementHeader
                });
                FgModelbox.showPopup(popupContent);
            }
            else {
                dataKey += '.removeElement.elementId';
                $("#createBox").attr('data-key', dataKey);
                //parse the all form field value as json array and assign that value to the array
                objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                objectGraph["page"][pageId]["removeElement"]["elementId"] = elementId;
                objectGraph["page"][pageId]["removeElement"]["boxId"] = boxId;
                var columnDetails = JSON.stringify(objectGraph);
                $("#createBox").attr('data-key', oldKey);
                //hide modal box overlay
                FgModelbox.hidePopup();
                FgXmlHttp.post(pageDetailSavePath, {
                    'postArr': columnDetails,
                    'pageDetails': JSON.stringify(jsonData)
                }, false, _this.callBackFn);
            }
        });
    };
    //COLUMN WIDTH ADJUST
    Fgcmspage.prototype.adjustColumnWidth = function () {
        var _this = this;
        $("body").on('click', '.fg-dev-width-adjust', function (event) {
            event.stopImmediatePropagation();
            $("#boxCreationForm").find("input").addClass('fairgatedirty');
            var adjustType = $(this).attr('width-change');
            var columnId = $(this).attr('columnId');
            //parse the all form field value as json array and assign that value to the array
            var containerId = $(this).parents().eq(2).attr('container-id');
            var pageId = jsonData.page.id;
            var dataKey = $("#createBox").attr('data-key');
            var oldKey = dataKey;
            dataKey += '.container.' + containerId + '.column.' + columnId + '.newWidth';
            $("#createBox").attr('data-key', dataKey);
            var objectGraph = {};
            objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
            var currentWidth = $(this).parents().eq(1).attr('column-size');
            if (adjustType == 'inc') {
                objectGraph["page"][pageId]["container"][containerId]["column"][columnId]["newWidth"] = parseInt(currentWidth) + 1;
                objectGraph["page"][pageId]["container"][containerId]["column"][columnId]["type"] = adjustType;
            }
            else {
                objectGraph["page"][pageId]["container"][containerId]["column"][columnId]["newWidth"] = parseInt(currentWidth) - 1;
                objectGraph["page"][pageId]["container"][containerId]["column"][columnId]["type"] = adjustType;
            }
            var columnDetails = JSON.stringify(objectGraph);
            $("#createBox").attr('data-key', oldKey);
            FgXmlHttp.post(pageDetailSavePath, {
                'postArr': columnDetails,
                'pageDetails': JSON.stringify(jsonData)
            }, false, _this.callBackFn);
        });
    };
    //COLUMN WIDTH CALCULATION
    Fgcmspage.prototype.widthCalculation = function () {
        var _this = this;
        $("div.contentRow").hover(function (event) {
            event.stopImmediatePropagation();
            _this.columnWidthCalculation($(this));
        }, function (eve) {
            eve.stopImmediatePropagation();
            $(".fg-left").hide();
            $(".fg-right").hide();
        });
    };
    //  ADD/EDIT CONTAINER POP UP
    Fgcmspage.prototype.containerEdit = function () {
        var _this = this;
        $("body").on('click', '.editContainerpopup', function (event) {
            event.stopImmediatePropagation();
            var idString = $(this).attr('container-id');
            var containeType = $(this).attr('container-type');
            var splitArray = idString.split("-");
            var currentContainerId = splitArray[1];
            var pageId = $(this).attr('container-page-id');
            var maxCount = _this.getMaxColumnCount();
            var defaultColumnCount = ($("#" + idString).find(".columWidth").length > 0) ? $("#" + idString).find(".columWidth").length : 1;
            var headerTitle = (containeType == 'containerAdd') ? _this.settings.translations.createContainerHeader : _this.settings.translations.editContainerHeader;
            var popupContent = FGTemplate.bind('editcontainerpopup', {
                containerId: currentContainerId,
                defaultColumnCount: defaultColumnCount,
                maxColumnCount: maxCount,
                pageId: pageId,
                containerType: containeType,
                headerTitle: headerTitle
            });
            FgModelbox.showPopup(popupContent);
        });
    };
    Fgcmspage.prototype.pageSave = function () {
        // SAVE FUNCTIONALITY
        var _this = this;
        var countDifference = 0;
        var multiClick = 1;
        $("body").on('click', '.fg-dev-datasave', function (ele) {
            if (multiClick == 1) {
                multiClick = 0;
                ele.stopImmediatePropagation();
                var pageId = $("input[name='currentPage']").val();
                var currentContainer = $("input[name='currentContainer']").val();
                var popupType = $("input[name='containerpopupType']").val();
                var columnCount = $("input[name='columnCount']").val();
                var oldCount = $("input[name='columnCount']").attr("oldCount");
                countDifference = Math.abs(oldCount - columnCount);
                var container = $("#pagecontainer-" + currentContainer);
                var calculatedWidth_1 = 0;
                _.each(container.find('.rowColumn'), function (value, key) {
                    calculatedWidth_1 = calculatedWidth_1 + parseInt($(value).attr('column-size'));
                });
                var totalWidth = _this.getMaxColumnCount();
                $("#editContainer").find("input").addClass('fairgatedirty');
                var objectGraph = {};
                //parse the all form field value as json array and assign that value to the array
                objectGraph = FgInternalParseFormField.formFieldParse('editContainer');
                if (popupType == 'containerAdd') {
                    //check if the new count is greater than totalwidth
                    if (columnCount > totalWidth || columnCount == 0) {
                        columnCount = totalWidth;
                    }
                    objectGraph["page"][pageId]["container"]["new"]["columnCount"] = columnCount;
                    objectGraph["page"][pageId]["container"]["new"]["totalWidth"] = totalWidth;
                    objectGraph["page"][pageId]["container"]["new"]["sortOrder"] = ($(".columnboxsortable").length) + 1;
                    var columnDetails_1 = JSON.stringify(objectGraph);
                    _this.requestCall(columnDetails_1);
                }
                else {
                    if ((oldCount - columnCount) >= 1) {
                        //check if the new count is greater than totalwidth
                        if (columnCount > totalWidth || columnCount == 0) {
                            columnCount = totalWidth;
                        }
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["delete"]["newCount"] = columnCount;
                        delete objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"];
                        var columnDetails = JSON.stringify(objectGraph);
                        _this.requestCall(columnDetails);
                    }
                    else if ((oldCount - columnCount) < 0) {
                        //check if the new count is greater than totalwidth
                        if (columnCount > totalWidth) {
                            countDifference = totalWidth - oldCount;
                        }
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"]["addCount"] = countDifference;
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"]["currentColumnCount"] = oldCount;
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"]["currentTotalWidth"] = calculatedWidth_1;
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"]["totalWidth"] = totalWidth;
                        delete objectGraph["page"][pageId]["container"][currentContainer]["column"]["delete"];
                        var columnDetails = JSON.stringify(objectGraph);
                        _this.requestCall(columnDetails);
                    }
                }
                //hide modal box overlay
                $(this).removeClass('noClick');
                FgModelbox.hidePopup();
                multiClick = 1;
            }
        });
    };
    Fgcmspage.prototype.addBox = function () {
        //ADD BOX 
        var _this = this;
        $("body").on('click', '.fg-dev-add-box', function (event) {
            event.stopImmediatePropagation();
            var columnId = $(this).attr('columnId');
            var containerId = $(this).parents().eq(1).attr('container-id');
            var pageId = $(this).parents().eq(1).attr('page-id');
            var sortOrder = ($('#containercolumn-' + columnId).find(".columnBox").length) + 1;
            var dataKey = $("#createBox").attr('data-key');
            var oldKey = dataKey;
            dataKey += ".container." + containerId + '.column.' + columnId + '.box.new.sortOrder';
            $("#createBox").attr('data-key', dataKey);
            $("#boxCreationForm").find("input").addClass('fairgatedirty');
            var objectGraph = {};
            //parse the all form field value as json array and assign that value to the array
            objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
            objectGraph["page"][pageId]["container"][containerId]["column"][columnId]['box']["new"]["sortOrder"] = sortOrder;
            var columnDetails = JSON.stringify(objectGraph);
            $("#createBox").attr('data-key', oldKey);
            FgXmlHttp.post(pageDetailSavePath, {
                'postArr': columnDetails
            }, false, _this.callBackFn);
        });
    };
    Fgcmspage.prototype.mapGeneration = function () {
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
    Fgcmspage.prototype.imageElementOptions = function (elementId) {
        var _this = this;
        var option1 = {
            tiles_type: "justified",
            tile_enable_action: false,
            tile_enable_overlay: false
        };
        var option2 = {
            tile_enable_action: false,
            tile_enable_overlay: false
        };
        var option3 = {
            gallery_theme: "slider",
            tile_enable_action: false,
            tile_enable_overlay: false,
            gallery_play_interval: 0,
            slider_enable_play_button: false,
            slider_enable_bullets: true,
            slider_enable_progress_indicator: false,
            slider_transition: "fade",
            slider_transition_speed: 1000
        };
        if ($("#row-gallery-" + elementId).length > 0) {
            _this.unitgalleryCall("#row-gallery-" + elementId, option1);
        }
        if ($("#column-gallery-" + elementId).length > 0) {
            _this.unitgalleryCall("#column-gallery-" + elementId, option2);
        }
        if ($("#slider-gallery-" + elementId).length > 0) {
            var sliderTime = $("#slider-gallery-" + elementId).attr('data-slider-time');
            option3.gallery_play_interval = sliderTime * 1000;
            _this.unitgalleryCall("#slider-gallery-" + elementId, option3);
        }
    };
    Fgcmspage.prototype.textElementOptions = function (elementId) {
        var sliderOption = {
            gallery_theme: "slider",
            tile_enable_action: false,
            tile_enable_overlay: false,
            slider_enable_play_button: false,
            slider_enable_bullets: true,
            slider_enable_progress_indicator: false
        };
        var _this = this;
        _this.unitgalleryCall("#gallery-textelement-" + elementId, sliderOption);
    };
    Fgcmspage.prototype.unitgalleryCall = function (identifier, slideroptions) {
        $(identifier).unitegallery(slideroptions);
    };
    Fgcmspage.prototype.setFixedcanvasHeader = function () {
        var scrollPos = $(window).scrollTop();
        if (scrollPos > 150) {
            $('.fg-cms-page-canvas-wrapper').addClass('fixed-header');
        }
        else if (scrollPos < 100) {
            $('.fg-cms-page-canvas-wrapper').removeClass('fixed-header');
        }
    };
    Fgcmspage.prototype.formElementPopupAction = function () {
        $(document).off('click', '#formElementDontUse');
        $(document).on('click', '#formElementDontUse', function () {
            $("#elementcreationForm").attr("action", formElementCreatePath);
            $('#formElementId').val('');
            $("#elementcreationForm").submit();
        });
        $(document).off('click', '#formElementUseTemplate');
        $(document).on('click', '#formElementUseTemplate', function () {
            $('span.required').remove();
            $('.fgFormTemplateError').removeClass('has-error');
            $('#formElementId').val('');
            var formTempId = $('.formTemplateSelect option:selected').val();
            if (formTempId === '') {
                $('.fgFormTemplateError').addClass('has-error');
                $('.fg-form-element-data').append('<span class="required">' + required + '</span>');
                return false;
            }
            else {
                $('#formElementId').val(formTempId);
            }
            $("#elementcreationForm").attr("action", formElementCreatePath);
            $("#elementcreationForm").submit();
        });
    };
    //INITIALIZE PAGE
    Fgcmspage.prototype.pagedocInit = function () {
        $(window).load(function () {
            cmspage.setFixedcanvasHeader();
        });
        $(window).scroll(function () {
            cmspage.setFixedcanvasHeader();
        });
        //Language switch click event
        $(document).off('click', 'button[data-elem-function=switch_lang]');
        $(document).on('click', '.fg-action-menu-wrapper button[data-elem-function=switch_lang]', function () {
            var selectedLang = $(this).attr('data-selected-lang');
            $('.fg-action-menu-wrapper .btlang').removeClass('active');
            $(this).addClass('active');
            FgUtility.showTranslation(selectedLang);
        });
        cmspage.callClipBoard(clipBoarDetails);
        //
        var plusminusOption = {
            'selector': ".selectButton"
        };
        // PLUS/MINUS FUNCTIONALITY 
        var inputplusminus = new Fgplusminus(plusminusOption);
        //DEFAULT SECTION OF CONTENET TAB
        if (pagetype == 'sidebar') {
            var active = '{{isActveTab}}';
            $('#paneltab > li').removeClass('active');
            $('#paneltab >  li').eq(active).addClass('active');
        }
        else {
            $('#paneltab > li').removeClass('active');
            $('#fg_tab_content').addClass('active');
        }
        $(document).ready(function () {
            //SLIM SCROLL IN CLIPBOARD
            cmspage.clipboardSlimScroll();
            //REINITIALIZATION OF MORE TAB
            setTimeout(function () {
                FgPageTitlebar.setMoreTab();
            }, 1000);
            //TITLE BAR INIT
            FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({
                title: true,
                tab: true,
                languageSwitch: true,
                tabType: 'server',
                editTitle: editTitleFlag,
                pagetitleSwitch: (pageTitleStatus == 1 || pageTitleStatus == 0) ? true : false
            });
            $('.fg-action-editTitle').on('click', function () {
                $.get(editPagetitlePopupPath, {
                    'pageId': pageDetails.id
                }, function (data) {
                    FgModelbox.showPopup(data);
                });
            });
            //fair 2403 new option to hide page title
            $('.fg-action-pagetitle-switch').on('click', function () {
                var data = {};
                data['pageId'] = pageDetails.id;
                data['status'] = $(this).find('.hide').attr('data-status');
                FgXmlHttp.post(pageTitleUpdatePath, data, false, false);
                $('.lock-pagetitle-status').addClass('hide');
                if (data['status'] == 0) {
                    $('.lock-pagetitle-status.pagetitle-hide').removeClass('hide');
                }
                else {
                    $('.lock-pagetitle-status.pagetitle-show').removeClass('hide');
                }
            });
            // PAGE CREATION CLASS INITIALIZATION                  
            cmspage.initSettings(options);
            var pagecontent = cmspage.contentInit();
            cmspage.appendContent(pagecontent);
            //INCREMENT DECREMENT FUNCTIONALITY
            inputplusminus.init();
            // SAVE FUNCTIONALITY
            cmspage.pageSave();
            //ADD BOX FUNCTIONALITY
            cmspage.addBox();
            //CLIP BOARD TOGGLE 
            $('body').on('click', '.fg-clipboard-nav', function (e) {
                e.stopImmediatePropagation();
                $('body').toggleClass('fg-clipboard-tray-open');
            });
            if (pagetype == 'sidebar') {
                $('#paneltab > li').removeClass('active');
                $('#paneltab >  li').eq(pagetabActive).addClass('active');
            }
            else {
                $('#paneltab > li').removeClass('active');
                $('#fg_tab_content').addClass('active');
            }
            //form element 
            cmspage.formElementPopupAction();
        });
    };
    // Reload existing page 
    Fgcmspage.prototype.saveJsonData = function () {
        FgXmlHttp.post(pageJsonSavePath, {
            'pageId': this.settings.data.page.id
        }, false);
        FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
    };
    Fgcmspage.prototype.pageCallBackFunction = function () {
        // EDIT BOX OVERLAY GENERATION
        this.boxOverlay();
        //DELETE BOX
        this.deleteBox();
        //ELEMENT EDIT OVERLAY  
        this.elementOverlay();
        //ACTION MENU POSITION SETTING 
        this.setMenuPosition();
        // CREATE ACTION MENU FOR ELEMENT OVERLAY 
        this.actionMenuDisplay();
        //BOX SORT
        this.boxSort();
        // ELEMENT SORT
        this.elementSort();
        //HIDE CONTAINER DELETE IMAGE
        this.hideRemoveButton();
        //CONTAINER SORT
        this.containerSort();
        // DELETE CONTAINER    
        this.containerDelete();
        //DELETE CONTAINER/BOX/ELEMENT ACTION FROM POP UP  
        this.pagedataDelete();
        //ELEMENT HEADER SORTABLE
        this.headerSort();
        //ELEMENT HEADER DRAGGABLE AND CLIPPBOARD DRAGGABLE
        this.elementDraggable();
        //ELEMENT EDIT
        this.elementEdit();
        // DELETE/REMOVE ELEMENT BOX ACTION
        this.removeElement();
        //COLUMN WIDTH ADJUST
        this.adjustColumnWidth();
        //COLUMN WIDTH CALCULATION
        this.widthCalculation();
        //  ADD/EDIT CONTAINER POP UP
        this.containerEdit();
        // MAP GENERATING CODE
        this.mapGeneration();
        //UNIFORM HANDLE
        FgFormTools.handleUniform();
    };
    return Fgcmspage;
}());
