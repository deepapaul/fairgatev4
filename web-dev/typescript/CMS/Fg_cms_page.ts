/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class Fgcmspage {
    settings: any = '';
    sortSetting: any = '';
    pageTranslation: any = {};
    defaultSettings: any = {
        sidebarContainer: '#sidebarBox',
        contentContainer: '#contentBox',
        footerContainer: '#footerBox',
        containerType: 'content',
        data: {},
        boxTemplateId: 'pageBox', // selector to which the overview content to be rendered
        ClubId: '', // Club id of contact to be listed
        languages: '', // list of all languages
        sidebarSide: '',
        sidebarSize: '',
        sidebarType: 'normal',
        mainContainer: '#mainContainer',
        container: {
            data: {}, //container data
            templateId: 'containerBox' // ID of template to render container data

        },
        column: {
            data: {}, // column data
            templateId: 'columnBox' // ID of template to render column data


        },
        columnbox: {
            data: {}, // box data
            templateId: 'Box' // ID of template to render box data

        },
        elementbox: {
            data: {}, // box data
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
                'supplementary-menu': 'templateSupplementary',
                'sponsor-ads': 'templateSponsorAd',
                'contact-application-form': 'templateForm',
                'contacts-table': 'templateContactTableElement',
                'portrait-element': 'templatePortraitElement',
                'newsletter-subscription': 'templateSubscriptionForm',
                'newsletter-archive': 'templateNewsletterArchive',
                'twitter': 'templateTwitter'
            }// ID of template to render box data

        },
        initContainerCallback: function() { },
        initColumnCallback: function() { },
        initColumnBoxCallback: function() { },
        initElementBoxCallback: function() { },
        pageInitCallback: function() { }

    };
    defaultSortOptions: any = {
        opacity: 0.8,
        forcePlaceholderSize: true,
        tolerance: "pointer"

    }
    constructor() {
        this.callSidePopup();
    }
    public callSidePopup() {
        $("#fg-cms-remove-sidebar").on("click", (event) => this.excludeSideColumn(event));
        this.closeSidebar();
        $("#fg-cms-show-sidebar").on("click", (event) => this.showPopup());
        this.closeSidePopup();
        $("#saveSidebarBtn").on("click", (event) => this.saveSidebar());
        this.closesaveSidebar();
        $(".fg-side-col-wrapper li.fg-side-col-layout").on("click", (e) => this.selectSideColumn(e));
    }
    public excludeSideColumn(event): void {

        var pageId = $("#fg-cms-remove-sidebar").attr('data-pageid');
        FgXmlHttp.post(sidebarPagePath, { 'cmsPageId': pageId, 'cmsPageSidebarAction': 'exclude' }, function(response) { });
    }
    public closeSidebar() {
        $(document).off('click', '#saveSidebarBtn');
    }
    public closeSidePopup() {
        $(document).off('click', '#fg-cms-show-sidebar');
    }
    public closesaveSidebar() {
        $(document).off('click', '#saveSidebarBtn');
    }
    public selectSideColumn(e: JQueryEventObject) {
        $('.fg-side-col-wrapper li.fg-side-col-layout div.fg-side-col-layout-inner').removeClass('active');
        $(e.currentTarget).find('.fg-side-col-layout-inner').addClass('active');
        $('#cmsSidebarType').val($(e.currentTarget).attr('data-sbType'));
        $('#cmsSidebarArea').val($(e.currentTarget).attr('data-sbArea'));

    }
    public showPopup(): void {
        $('#fg-cms-page-sidebar').modal('show');

    }
    public saveSidebar() {
        $('#cmsPageSidebarAction').val('include');
        var data = $('#cms_sidebar_page_form').serializeArray();
        FgXmlHttp.post(sidebarPagePath, data, false, '');

    }
    public initSettings(options: any) {
        this.settings = $.extend(true, {}, this.defaultSettings, options);
    }

    public renderContainerBox(containerId: any) {
        if (_.size(this.settings.container.data) > 0) {
            //render all columns of particular container
            let columnContent = this.containerColumns();
            return FGTemplate.bind(this.settings.container.templateId, { details: this.settings.container.data, containerid: containerId, columnDetails: columnContent, pageId: this.settings.data.page.id });
        }
    }


    public renderColumnBox(columnId: any) {
        //render all column box
        let boxContent = this.columnBox();

        return FGTemplate.bind(this.settings.column.templateId, { details: this.settings.column.data, columnid: columnId, settingDetails: this.settings, boxDetails: boxContent });
    }
    public renderBox(boxId: any) {
        //render all the box element
        let elementContent = this.elementBox();

        return FGTemplate.bind(this.settings.columnbox.templateId, { details: this.settings.columnbox.data, boxid: boxId, elementDetails: elementContent });
    }
    public renderElement(elementId: any, params: any) {
        let _this = this;
        let widthValue = this.settings.elementbox.data.widthValue;
        if (this.settings.elementbox.data.isAjax !== true) {
            return FGTemplate.bind(this.settings.elementbox.templateId[this.settings.elementbox.data.elementType], { details: this.settings.elementbox.data, elementid: elementId, settingDetails: this.settings });
        } else {
            if (this.settings.elementbox.data.elementType == 'supplementary-menu') {
                this.settings.elementbox.data.ajaxURL = supplymenteryDataUrl;
            }
            var postdata = { 'fromedit': 1 };
            // ajax call of each element and render this one             
            $.post(this.settings.elementbox.data.ajaxURL, postdata, function(data) {
                _this.insertElement(elementId, data, widthValue, params);
            });


            return FGTemplate.bind(this.settings.elementbox.templateId[this.settings.elementbox.data.elementType], { details: this.settings.elementbox.data, elementid: elementId, settingDetails: this.settings });

        }


    }
    public insertElement(elementId: any, dataHtml: any, widthValue: number, params: any) {
        let _this = this;
        if (params.type == 'form' || params.type == 'contact-application-form') {
            if (dataHtml.formData == null || dataHtml.formData == '') {
                return true;
            }
            if (dataHtml.elementType == 'contact-application-form') {
                dataHtml = FGTemplate.bind('templateContactApplicationFormField', { formDetails: dataHtml.formData, defLang: dataHtml.defLang, formMessage: dataHtml.formOption, elementId: dataHtml.elementId, contactFormOptions: dataHtml.contactFormOptions });
            } else {
                dataHtml = FGTemplate.bind('templateFormField', { formDetails: dataHtml.formData, defLang: dataHtml.defLang, formStage: dataHtml.formStage, elementId: dataHtml.elementId });
            }
            $("#" + elementId).append(dataHtml);
            $("#" + elementId + " input:checkbox,#" + elementId + " input:radio").uniform();
            $("#" + elementId + " .bs-select").selectpicker({
                noneSelectedText: jstranslations.noneSelectedText,
                countSelectedText: jstranslations.countSelectedText,
            });
            /*********init captcha*********/
            this.initCaptcha(elementId);
            this.handleToolTip(elementId);
            return true;
        }
        if (params.type == 'contacts-table') {
            if (dataHtml.length == 0) {
                $('#' + elementId + ' .fg-contact-table-empty-box').removeClass('hide');
            } else {
                this.handleCotactTableElement(elementId, dataHtml);
            }
            return;
        }
        if (params.type == 'portrait-element') {
            if (dataHtml.length == 0) {
                $('#' + elementId + ' .fg-contact-portrait-empty-box').removeClass('hide');
            } else {
                this.handleContactPortraitElement(elementId, dataHtml);
            }
            return;
        }
        if (params.type == 'newsletter-archive') {
            this.handleNewsletterArchiveElement(elementId, dataHtml, widthValue);
            return;
        }
        $("#" + elementId).append(dataHtml);
        if (params.type == 'image') {
            _this.imageElementOptions(params.id);
        }
        if (params.type == 'sponsor-ads') {
            let elementWidth = $("#" + elementId).width();
            let logoWidth = (elementWidth > 150) ? 'original' : ((elementWidth > 65) ? 'width_150' : 'width_65');
            let sponsorWidth = (elementWidth > 1100) ? 'original' : ((elementWidth > 500) ? '1100' : ((elementWidth > 250) ? '500' : ((elementWidth > 150) ? '250' : '150')));
            $("#" + elementId + ' .faderImg').each(function(i, e) {
                let srcArray = $(e).attr('data-src').split('/');
                let folderIndex = srcArray.length - 2;
                srcArray[folderIndex] = $(e).hasClass('faderImgLogo') ? logoWidth : sponsorWidth;
                $(e).attr('src', srcArray.join('/')).removeClass('hide');
            })
            if ($('.fg-sponsor-ads-widget').hasClass('fg-fader')) {
                setTimeout(function() {
                    _this.sponsorElementOptions(params.id);
                }, 3000);

            }

        }
        if (params.type == 'text') {
            _this.textElementOptions(params.id);
        }
        if (params.type == 'articles') {
          if($('#elementbox-' + params.id).children().hasClass('sliderView')){
            $( '#elementbox-' +params.id ).find(" a:first" ).addClass( "active" );
            $( '#elementbox-' +params.id).find(" ul li:first" ).addClass( "active" );
            _this.articleElementCarouselSettings(params.id);
          }
        }
        if (params.type == 'newsletter-subscription') {
            $("#" + elementId + " .bs-select").selectpicker();
            this.initCaptcha(elementId);
        }
    }
    public renderPage(jsonData: any) {

        return FGTemplate.bind(this.settings.boxTemplateId, jsonData);

    }
    public pageContainer() {
        let containerDetails = this.settings.data.page.container;
        if (this.settings.containerType == 'sidebar' && _.size(this.settings.data.sidebar) > 0) {
            containerDetails = this.settings.data.sidebar.container;
            // To set the side of sidebar that display
            this.settings.sidebarSide = (_.size(this.settings.data.sidebar) > 0) ? this.settings.data.sidebar.side : '';
            // To set the sidebar size
            this.settings.sidebarSize = (_.size(this.settings.data.sidebar) > 0) ? this.settings.data.sidebar.width_value : '';
        }
        let containerHtml = '';
        let _this = this;
        let containerId = '';
        this.settings.data.page.container = _.sortBy(this.settings.data.page.container, 'sortOrder');
        _.each(this.settings.data.page.container, function(containerValues: any, index) {
            //crea t e container box id
            containerId = 'pagecontainer-' + containerValues.containerId;
            //create container
            _this.settings.container.data = containerValues;
            containerHtml += _this.renderContainerBox(containerId);
        });

        _this.settings.initContainerCallback.call();
        return containerHtml;
    }


    public containerColumns() {
        let columnHtml = '';
        let columnId = '';
        let _this = this;
        this.settings.container.data.columns = _.sortBy(this.settings.container.data.columns, 'sortOrder');
        _.each(this.settings.container.data.columns, function(columnValues: any, index) {
            //create column box id
            columnId = 'containercolumn-' + columnValues.columnId;
            //create columns
            _this.settings.column.data = columnValues;
            columnHtml += _this.renderColumnBox(columnId);

        });
        this.settings.initColumnCallback.call();
        return columnHtml;
    }

    public columnBox() {
        let boxHtml = '';
        let _this = this;
        let boxId = '';
        this.settings.column.data.box = _.sortBy(this.settings.column.data.box, 'sortOrder');
        _.each(this.settings.column.data.box, function(boxValues: any, index) {
            //create box id
            boxId = 'columnbox-' + boxValues.boxId;
            //create box
            _this.settings.columnbox.data = boxValues;
            boxHtml += _this.renderBox(boxId);
            //append box to specified columns
        });
        this.settings.initColumnBoxCallback.call();
        return boxHtml;

    }

    public elementBox() {
        let elementHtml = '';
        let _this = this;
        let elementId = '';
        this.settings.columnbox.data.Element = _.sortBy(this.settings.columnbox.data.Element, 'sortOrder');
        _.each(this.settings.columnbox.data.Element, function(elementValues: any, index) {
            elementId = 'elementbox-' + elementValues.elementId;
            _this.settings.elementbox.data = elementValues;
            let params = { 'type': elementValues.elementType, 'id': elementValues.elementId }
            elementHtml += _this.renderElement(elementId, params);
        });

        this.settings.initElementBoxCallback.call();
        return elementHtml;

    }
    public sidebarInit() {
        let pageHtml = this.pageContainer();
        return pageHtml;
    }
    public contentInit() {

        let pageHtml = this.pageContainer();
        return pageHtml;
    }


    public appendContent(pageContent: any) {
        $(this.settings.mainContainer).html(pageContent);
        this.settings.pageInitCallback.call();
    }
    public sortableEvent(identifier: string, sortoptions: any) {

        this.sortSetting = $.extend(true, {}, this.defaultSortOptions, sortoptions);
        $(identifier).sortable(this.sortSetting);

    }

    public columnWidthCalculation(currentContainer: any) {
        let _this = this;
        let totalWidth = 6;
        let containerIdString = currentContainer.attr('id').split("-");
        let currentContainerId = containerIdString[1];

        if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'wide') {
            totalWidth = 2;
        } else if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'small') {
            totalWidth = 1;
        } else if (this.settings.data.sidebar.size == 'wide') {
            totalWidth = 4;
        } else if (this.settings.data.sidebar.size == 'small') {
            totalWidth = 5;
        }
        let calculatedWidth = 0;
        _.each(currentContainer.find('.rowColumn'), function(value, key) {
            calculatedWidth = calculatedWidth + parseInt($(value).attr('column-size'));
            if (parseInt($(value).attr('column-size')) > 1) {
                //decrease button 
                $(value).find(".fg-left").show();

            }
        })
        if (calculatedWidth < totalWidth) {
            //set increase button to all column
            $(currentContainer).find(".fg-right").show();
        }

    }
    public createContainer(pageId: any, containerId: any) {
        let totalColumnCount = 6;
        if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'wide') {
            totalColumnCount = 2;
        } else if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'small') {
            totalColumnCount = 1;
        } else if (this.settings.data.sidebar.size == 'wide') {
            totalColumnCount = 4;
        } else if (this.settings.data.sidebar.size == 'small') {
            totalColumnCount = 5;
        }

        //FGTemplate.bind('createContainer', { totalCount: totalColumnCount, currentColumnCount: 1,containerId:containerId });

    }
    public hideRemoveButton() {
        if (this.settings.containerType == 'content' && _.size(this.settings.data.page.container) <= 1) {
            $(".contentRow .fg-dev-delete").hide();
        } else if (this.settings.containerType == 'sidebar' && _.size(this.settings.data.page.container) <= 1) {
            $(".contentRow .fg-dev-delete").hide();
        }

    }

    public getMaxColumnCount() {
        let totalColumnCount = 6;
        if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'wide') {
            totalColumnCount = 2;
        } else if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'small') {
            totalColumnCount = 1;
        } else if (this.settings.data.sidebar.size == 'wide') {
            totalColumnCount = 4;
        } else if (this.settings.data.sidebar.size == 'small') {
            totalColumnCount = 5;
        }

        return totalColumnCount;
    }
    //clip board creation
    public callClipBoard(data: any) {
        let htmlFinal = FGTemplate.bind('globalClipboard', { clipBoardData: data });
        $('#fg-clipBoard-section').html('');
        $('#fg-clipBoard-section').append(htmlFinal);
        //CLIPPBOARD DRAGGABLE                   
        $(".fg-clipboard-item").draggable({
            connectToSortable: ".columnBox",
            helper: "clone",
            start: function(event, ui) {
                $(".fg-drop-holder").addClass("no-hover");
                $('body').addClass("fg-dev-drag-active");
                ui.helper.addClass('fg-dragging-element');
            },
            stop: function(event, ui) {
                $(".fg-drop-holder").removeClass("no-hover");
                $('body').removeClass("fg-dev-drag-active");
                ui.helper.addClass('hide').removeClass('fg-dragging-element');
            }
        });
        this.clipboardSlimScroll();

    }
    //slim scroll init in clip board
    public clipboardSlimScroll() {
        $('.fg-clipboard-element-wrapper').slimScroll({
            height: '170px',
            width: '100%'
        });

    }
    // Callback function after save
    public callBackFn(result: any) {

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
             case 'loadJson' :
             let columnDetails = result.columnDetails;
             let pageJsonData = result.jsonData;
             let portTemplate = result.portraitElemTemplate.elementId.template;
             FgXmlHttp.post(pageDetailSavePath, {
                        'postArr': columnDetails,
                        'pageDetails':pageJsonData 
                    }, false, this.callBackFn);
             
             break;
        }
    }
    //portrait element callback
     public portraitCallBack(result: any) {
        window.location.reload();
        
    }
    
    



    //ajax call for form submission
    public requestCall(columnDetails: any) {
        FgXmlHttp.post(pageDetailSavePath, {
            'postArr': columnDetails,
            'pageDetails': JSON.stringify(jsonData)
        }, false, this.callBackFn);
    }
    // Reload existing page 
    public reloadData() {
        options.data = jsonData;
        cmspage.initSettings(options);
        let pagecontent = cmspage.contentInit();
        cmspage.appendContent(pagecontent);
        if (twitterElementCount > 0) {
            twttr.widgets.load();
        }
    }
    // EDIT BOX OVERLAY GENERATION  
    public boxOverlay() {
        $("body").on('click', ".fg-dev-box-edit", function(event) {
            event.stopImmediatePropagation();
            $('.fg-widget-hover-column-wrapper').show();
            $('.fg-widget-dropmenu-options').hide();
            $('.fg-widget-block').removeClass('open-widget-options');
            let elementId = $(this).attr('element-id');
            let columnId = $('#elementbox-' + elementId).parents().eq(1).attr("id")
            $('#elementbox-' + elementId).parent().addClass("open-block-options");

        });
        //EMPTY BOX OVERLAY GENERATION
        $(document).on({
            mouseenter: function(event) {
                event.stopPropagation();
                $(".fg-drop-holder").removeClass("open-block-options");
                $('.fg-widget-dropmenu-options').hide();
                $('.fg-widget-block').removeClass('open-widget-options');
                $(this).addClass("open-block-options");
            },

            mouseleave: function() {
                $(this).removeClass("open-block-options");
            }
        }, '.fg-empty-drop-holder');
    }


    public deleteBox() {
        //DELETE BOX
        let _this = this;
        $("body").on('click', ".fg-dev-box-remove", function(event) {
            event.stopImmediatePropagation();
            $("#boxCreationForm").find("input").addClass('fairgatedirty');
            let dataKey = $("#createBox").attr('data-key');
            let oldDataKey = dataKey;
            let boxId = $(this).attr('box-id');
            let columnId = $("#columnbox-" + boxId).parent().attr("column-id");
            let containerId = $("#columnbox-" + boxId).parents().eq(1).attr('container-id');
            let pageId = $(this).parents('.fg-cms-page-elements-block-row').attr('page-id');
            dataKey += '.container.' + containerId + '.column.' + columnId + '.box.delete.id';
            if ($("#columnbox-" + boxId).find(".elementBox").length >= 1) {
                //get elements inside the box
                let elementId = [];
                $("#columnbox-" + boxId).find(".elementBox").each(function() {
                    elementId.push($(this).attr('element-id'));
                });

                let message = _this.settings.translations.deleteBoxMsg;
                let popupContent = FGTemplate.bind('deletePopup', {
                    deleteId: boxId,
                    pageId: pageId,
                    dataKey: dataKey,
                    elementIds: elementId.join(','),
                    displaymessage: message,
                    headerTitle: _this.settings.translations.deleteBoxHeader,
                    actionType: 'deleteBox'
                });
                FgModelbox.showPopup(popupContent);
            } else {
                $("#createBox").attr('data-key', dataKey);
                $("#createBox").val(boxId);
                let objectGraph = {};
                objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                //parse the all form field value as json array and assign that value to the array
                let columnDetails = JSON.stringify(objectGraph);
                $("#createBox").attr('data-key', oldDataKey);

                FgXmlHttp.post(pageDetailSavePath, {
                    'postArr': columnDetails
                }, false, _this.callBackFn);
            }


        });
    }

    public elementOverlay() {
        //ELEMENT EDIT OVERLAY  

        $(window).click(function() {
            $('.fg-widget-dropmenu-options').hide(); //Hide the menus if visible
            $('.fg-widget-block').removeClass('open-widget-options');
            $('.fg-drop-holder').removeClass('open-block-options');

        });

    }
    //ACTION MENU POSITION SETTING 
    public setMenuPosition() {
        $('.fg-cms-page-elements-container').on('click', '.fg-widget-hover-elements-wrapper .fg-widget-block-options', function(event) {
            event.stopImmediatePropagation();
            let position = $(this).offset();
            let winWidth = $(window).width();
            let dropDownWidth = $('.fg-widget-dropmenu-options').outerWidth();
            $('.fg-widget-block').removeClass('open-widget-options');
            $(this).parents('.fg-widget-block').addClass('open-widget-options');
            if (winWidth > dropDownWidth + position.left + 20) {
                $('.fg-widget-dropmenu-options').show().css({
                    'top': position.top + 37,
                    'left': position.left
                });
            } else {
                $('.fg-widget-dropmenu-options').show().css({
                    'top': position.top + 37,
                    'left': position.left - dropDownWidth + 50
                });
            }

        });
    }
    // CREATE ACTION MENU FOR ELEMENT OVERLAY 
    public actionMenuDisplay() {
        let _this = this;
        $('.fg-widget-hover-elements-wrapper .fg-widget-block-options').hover(function() {
            $("#elementActionmenu").empty();
            let elementId = $(this).attr('element-Id');
            let boxId = $("#elementbox-" + elementId).parent().attr("box-id");
            let liString = '<li class="fg-element-edit-actions" element-id=' + elementId + ' box-id=' + boxId + ' action-type="remove"><a href="javascript:void(0);" >' + _this.settings.translations.clipboardMovement + '</a></li><li class="fg-element-edit-actions" action-type="delete" element-id=' + elementId + ' box-id=' + boxId + '><a href="javascript:void(0);" >' + _this.settings.translations.deleteElement + '</a></li><li class="fg-dev-box-edit" element-id=' + elementId + '><a href="javascript:void(0);" >' + _this.settings.translations.editBox + '&hellip;</a></li>';
            $("#elementActionmenu").html(liString);
        })
    }

    public boxSort() {
        //BOX SORT
        let fromColumn = '';
        $('.contentRow').disableSelection();
        let currentSortOrder: any = '';
        let _this = this;
        let boxsortoption = {
            connectWith: '.rowColumn',
            items: "> div.columnBox",
            handle: ".fg-dev-box-draggable",
            helper: "clone",
            cursorAt: { top: 40, left: 40 },
            start: function(event, ui) {
                fromColumn = $(ui.item).parent().attr('column-id');
                currentSortOrder = $(ui.item).index() + 1;
                ui.item.show().addClass('original-placeholder');
                $(ui.helper).append('<div class="fg-drag-holder-item"><i class="fa fa-square-o" aria-hidden="true"></i><div class="fg-text">' + _this.settings.translations.dragBoxTitle + '</div></div>');
            },
            stop: function(event, ui) {
                $(ui.helper).find(".fg-drag-holder-item").remove();
                $("#boxCreationForm").find("input").addClass('fairgatedirty');

                let sortElementDetails = {};
                sortElementDetails['toColumn'] = $(ui.item).parent().attr('column-id');
                sortElementDetails['fromColumn'] = fromColumn;
                if (typeof sortElementDetails['toColumn'] != 'undefined' && fromColumn != 'undefined') {
                    let pageId = jsonData.page.id;
                    let dataKey = $("#createBox").attr('data-key');
                    let oldKey = dataKey;
                    dataKey += '.sortBox';
                    $("#createBox").attr('data-key', dataKey);
                    sortElementDetails['boxId'] = $(ui.item).attr('box-id');
                    sortElementDetails['sortOrder'] = ($(ui.item).index()) + 1;
                    sortElementDetails['currentSortOrder'] = currentSortOrder;
                    let objectGraph = {};
                    objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                    objectGraph["page"][pageId]["sortBox"] = sortElementDetails;
                    let columnDetails = JSON.stringify(objectGraph);
                    $("#createBox").attr('data-key', oldKey);
                    FgXmlHttp.post(pageDetailSavePath, {
                        'postArr': columnDetails,
                        'pageDetails': JSON.stringify(jsonData)
                    }, false, _this.callBackFn);
                }
            }
        };
        this.sortableEvent('.rowColumn', boxsortoption);

    }
    public elementSort() {
        // ELEMENT SORT
        $('.rowColumn').disableSelection();
        let fromBox = '';
        let currentSortOrder: any = '';
        let _this = this;
        let elementsortoption = {
            connectWith: '.columnBox',
            items: "> div.elementBox",
            handle: ".fg-dev-elementbox-draggable",
            helper: "clone",
            cursorAt: { top: 10, left: 10 },
            start: function(event, ui) {
                fromBox = $(ui.item).parent().attr('box-id');
                currentSortOrder = $(ui.item).index();
                let helperHtml = $(ui.item).find(".fg-elementtype-class").html();
                if (typeof helperHtml != 'undefined') {
                    $(ui.helper).append('<div class="fg-drag-holder-item">' + helperHtml + '</div>');
                }
            },
            stop: function(event, ui) {
                $(ui.helper).find(".fg-drag-holder-item").remove();
                let sortElementDetails = {};
                sortElementDetails['elementId'] = $(ui.item).attr('element-id');
                sortElementDetails['toBox'] = $(ui.item).parent().attr('box-id');
                sortElementDetails['currentSortOrder'] = currentSortOrder;
                let actionFrom = $(ui.item).attr('action-from') ? $(ui.item).attr('action-from') : 'content';
                let pageId = jsonData.page.id;
                let dataKey = $("#createBox").attr('data-key');
                $("#columnbox-" + sortElementDetails['toBox']).find('.fg-dev-drop-box-comment').remove();
                $("#boxCreationForm").find("input").addClass('fairgatedirty');
                let oldKey = dataKey;
                let objectGraph = {};
                let columnDetails = '';
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
                    let elementType = $(ui.item).attr('element-type');
                    //for handle portrait element only
                    
                    let callback = (elementType == 'portrait-element') ?  _this.portraitCallBack : _this.callBackFn;
                    
                    FgXmlHttp.post(pageDetailSavePath, {
                            'postArr': columnDetails,
                            'pageDetails': JSON.stringify(jsonData)
                        }, false, callback); 

                } else if (typeof sortElementDetails['elementId'] != 'undefined' && typeof sortElementDetails['toBox'] != 'undefined') {
                    $("#columnbox-" + sortElementDetails['toBox']).find('.fg-dev-drop-box-comment').remove();
                    $("#columnbox-" + sortElementDetails['toBox']).removeClass('fg-empty-drop-holder');
                    $("#boxCreationForm").find("input").addClass('fairgatedirty');
                    let elementType = $(ui.item).attr('element-type');
                    dataKey += '.sortElement';
                    $("#createBox").attr('data-key', dataKey);
                    //$(".fg-dev-drop-box-comment")
                    sortElementDetails['fromBox'] = fromBox
                    sortElementDetails['sortOrder'] = $(ui.item).index();
                    objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                    objectGraph["page"][pageId]["sortElement"] = sortElementDetails;
                    columnDetails = JSON.stringify(objectGraph);
                    $("#createBox").attr('data-key', oldKey);
                    let callback = (typeof elementType != 'undefined') ?  _this.portraitCallBack : _this.callBackFn;
                    FgXmlHttp.post(pageDetailSavePath, {
                        'postArr': columnDetails,
                        'pageDetails': JSON.stringify(jsonData)
                    }, false, callback);
                }
            }
        };
        this.sortableEvent('.columnBox', elementsortoption);
    }
    //CONTAINER SORT
    public containerSort() {
        let startIndex = 0;
        let _this = this;
        let containerOption = {
            handle: ".fg-container-sortable",
            tolerance: 'pointer',
            start: function(event, ui) {
                ui.placeholder.height(ui.helper.outerHeight());
                startIndex = $(ui.item).index();
                $(ui.item).parents('.fg-cms-page-elements-container').addClass('fg-drag-start');
            },
            stop: function(event, ui) {
                $(ui.item).parents('.fg-cms-page-elements-container').removeClass('fg-drag-start');
                let stopIndex = $(ui.item).index();
                let selector = $(ui.item).parent().find(".contentRow");
                let containerArray = {};
                let i = 1;
                $(selector).each(function(index, value) {
                    let sortArray = {};
                    sortArray['sortOrder'] = i;
                    containerArray[$(value).attr('container-id')] = sortArray;
                    i++;
                })
                $("#boxCreationForm").find("input").addClass('fairgatedirty');
                let pageId = jsonData.page.id;
                let dataKey = $("#createBox").attr('data-key');
                let oldKey = dataKey;
                dataKey += '.container';
                $("#createBox").attr('data-key', dataKey);
                let objectGraph = {};
                objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                objectGraph["page"][pageId]["container"] = containerArray;
                let columnDetails = JSON.stringify(objectGraph);
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
    }
    // DELETE CONTAINER
    public containerDelete() {
        let _this = this;
        $('body').on('click', '.fg-dev-delete', function(ele) {
            ele.stopImmediatePropagation();
            let containerId = $(this).attr('container-id');
            let dataKey = $("#createBox").attr('data-key');
            let pageId = $(this).attr('page-id');
            dataKey += '.container.delete.id';
            let elementCount = $("#pagecontainer-" + containerId).find(".elementBox").length;
            let message = (elementCount >= 1) ? _this.settings.translations.containerDeleteMsgWithElement : _this.settings.translations.containerDeleteMsgWithOutElement;
            let popupContent = FGTemplate.bind('deletePopup', {
                deleteId: containerId,
                pageId: pageId,
                dataKey: dataKey,
                elementIds: '',
                displaymessage: message,
                headerTitle: _this.settings.translations.deleteContainerHeader,
                actionType: 'containerDelete'
            });
            FgModelbox.showPopup(popupContent);

        });
    }
    //DELETE CONTAINER/BOX/ELEMENT ACTION FROM POP UP
    public pagedataDelete() {
        let _this = this;
        $("body").on("click", "#fg-dev-container-delete", function(ele) {
            ele.stopImmediatePropagation();
            $("#deleteInput").addClass('fairgatedirty');
            let objectGraph = {};
            objectGraph = FgInternalParseFormField.formFieldParse('deletePagePopup');
            //parse the all form field value as json array and assign that value to the array
            let columnDetails = JSON.stringify(objectGraph);
            FgModelbox.hidePopup();
            FgXmlHttp.post(pageDetailSavePath, {
                'postArr': columnDetails
            }, false, _this.callBackFn);

        });
        //button click on 'move to clipboard''
        $("body").on("click", "#fg-dev-mov2clip", function(ele) {
            ele.stopImmediatePropagation();
            $("#deletePagePopup").find("input.fg-dev-elements").addClass('fairgatedirty');
            let objectGraph = {};
            objectGraph = FgInternalParseFormField.formFieldParse('deletePagePopup');
            //Details of elemnts to move to clipboard
            let columnDetails = JSON.stringify(objectGraph);

            var boxDetailsJson = {};
            FgInternal.converttojson(boxDetailsJson, $("#deleteInput").attr('data-key').split('.'), $("#deleteInput").val());
            //Details of box to delete
            let boxDetails = JSON.stringify(boxDetailsJson);
            FgModelbox.hidePopup();
            FgXmlHttp.post(pageBoxDeletePath, {
                'postArr': columnDetails,
                'boxDetails': boxDetails
            }, false, _this.callBackFn);
        });
    }
    public headerSort() {
        //ELEMENT HEADER SORTABLE
        let _this = this;
        //        $(".fg-dev-element-header .fg-dev-draggable-element").on('mouseover', function() {
        //            var boxsortoption = {
        //                connectWith: '.columnBox',
        //                items: "> div.elementBox"
        //            };
        //            _this.sortableEvent('.columnBox', boxsortoption);
        //        });
    }
    //ELEMENT HEADER DRAGGABLE
    public elementDraggable() {
        let _this = this;
        $(".fg-dev-element-header .fg-dev-draggable-element").draggable({
            connectToSortable: ".columnBox",
            helper: "clone",
            cursorAt: { top: 10, left: 10 },
            start: function(event, ui) {
                $(".fg-drop-holder").addClass("no-hover");
                ui.helper.addClass('fg-dragging-element');
            },
            stop: function(event, ui) {
                ui.helper.addClass('hide').removeClass('fg-dragging-element');
                let boxId = $(ui.helper).parent().attr('box-id');
                //coloum size of droppable box
                let colSize = $(ui.helper).parent().parent().attr('column-size');
                if ($("#columnbox-" + boxId).find(".fg-dev-drop-box-comment").length >= 1) {
                    $("#droppedboxSortorder").val('1');
                } else {
                    $("#droppedboxSortorder").val(($(ui.helper).index()));
                }
                $("#columnbox-" + boxId).find(".fg-dev-drop-box-comment").remove();
                $(".fg-drop-holder").removeClass("no-hover")
                let elementType = $(ui.helper).attr('element-type');

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
                        }, '', function(response) {
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
                        }, '', function(response) {
                            if (response['formElements'].length > 0) {
                                var result_data = FGTemplate.bind('form-element-popup-data', { 'data': response['formElements'] });
                                $('#fg-form-element-popup-content').html(result_data);
                                $('select.selectpicker').selectpicker();
                                FgFormTools.handleUniform();
                                cmspage.formElementPopupAction();

                                $('#fg-form-element-popup').modal('show');
                            } else {
                                $("#elementcreationForm").attr("action", formElementCreatePath);
                                $("#elementcreationForm").submit();
                            }
                        }, false, 'false');
                    } else if (elementType === 'supplementary-menu') {
                        FgXmlHttp.post(addElementPagePath, {
                            'elementType': elementType,
                            'pageId': $("#droppedpageId").val(),
                            'boxId': $("#droppedboxId").val(),
                            'elementId': $("#elementId").val(),
                            'sortOrder': $("#droppedboxSortorder").val()
                        }, '', function(data) {
                            FgModelbox.showPopup(data);
                        }, false, 'false');

                    }
                    else if (elementType === 'contact-application-form') {
                        FgXmlHttp.post(addElementPagePath, {
                            'elementType': elementType,
                            'pageId': $("#droppedpageId").val(),
                            'boxId': $("#droppedboxId").val(),
                            'elementId': $("#elementId").val(),
                            'sortOrder': $("#droppedboxSortorder").val(),
                            'formId': $('#contactFormElementId').val()
                        }, '', function(response) {
                            if (response.formCount == 1) {
                                if (response.status == 'SUCCESS') {
                                    _this.settings.data = response.data;
                                    jsonData = response.data;
                                    _this.saveJsonData();
                                    _this.reloadData();
                                    _this.callClipBoard(response.clipboardData);
                                    FgFormTools.handleUniform();
                                }
                                else {
                                    FgModelbox.hidePopup();
                                }
                            }
                            else {
                                FgModelbox.showPopup(response);
                                $('select.selectpicker').selectpicker();
                            }
                        }, false, 'false');
                    }
                    else if (elementType === 'contacts-table') {
                        $("#elementcreationForm").attr("action", contactTableElementPath);
                        $("#elementcreationForm").submit();
                    }
                    else if (elementType === 'portrait-element') {
                        $("#colSize").val(colSize);
                        $("#elementcreationForm").attr("action", portraitElementPath);
                        $("#elementcreationForm").submit();
                    }
                    else if (elementType === 'newsletter-subscription') {

                        FgXmlHttp.post(addElementPagePath, {
                            'elementType': elementType,
                            'pageId': $("#droppedpageId").val(),
                            'boxId': $("#droppedboxId").val(),
                            'elementId': $("#elementId").val(),
                            'sortOrder': $("#droppedboxSortorder").val()
                        }, '', function(response) {
                            //                            console.log(response);return false;
                            _this.settings.data = response.data;
                            jsonData = response.data;
                            _this.saveJsonData();
                            _this.reloadData();
                            _this.callClipBoard(response.clipboardData);
                            FgFormTools.handleUniform();
                        });
                    }
                    else if (elementType === 'newsletter-archive') {
                        FgXmlHttp.post(addElementPagePath, {
                            'elementType': elementType,
                            'pageId': $("#droppedpageId").val(),
                            'boxId': $("#droppedboxId").val(),
                            'elementId': $("#elementId").val(),
                            'sortOrder': $("#droppedboxSortorder").val()
                        }, '', function(response) {
                            _this.settings.data = response.data;
                            jsonData = response.data;
                            _this.saveJsonData();
                            _this.reloadData();
                            _this.callClipBoard(response.clipboardData);
                            FgFormTools.handleUniform();
                        });
                    }
                    else {
                        $("#colSize").val(colSize);
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
            start: function(event, ui) {
                $(".fg-drop-holder").addClass("no-hover");
                $('body').addClass("fg-dev-drag-active");
                ui.helper.addClass('fg-dragging-element');
            },
            stop: function(event, ui) {
                $(".fg-drop-holder").removeClass("no-hover");
                $('body').removeClass("fg-dev-drag-active");
                ui.helper.addClass('hide').removeClass('fg-dragging-element');
            }
        });

    }

    public elementEdit() {
        $("body").on('click', '.fg-dev-edit-element', function(event) {
            event.stopImmediatePropagation();
            var elementType = $(this).attr('element-type');
            var postUrl = (elementType == 'contacts-table') ? contactTableElementPath : (elementType == 'portrait-element' ? portraitElementPath : addElementPagePath);
            $("#droppedboxId").val('0');
            $("#elementType").val($(this).attr('element-type'));
            $("#droppedboxSortorder").val('0');
            $("#elementId").val($(this).attr('element-Id'));
            $("#droppedpageId").val($(this).attr('page-id'));
            let colSize = $(this).parents('.fg-dev-page-elements-block').attr('column-size');
            $("#colSize").val(colSize);
            $("#elementcreationForm").attr("action", postUrl);
            //submit form
            $("#elementcreationForm").submit();
        })
    }
    public supplementaryMenuCallback(response) {
        let _this = this;
        _this.settings = {};
        _this.settings.data = response.data;
        jsonData = response.data;
        _this.saveJsonData();
        _this.reloadData();
        _this.callClipBoard(response.clipboardData);
    }
    public removeElement() {
        // DELETE/REMOVE ELEMENT BOX ACTION
        let _this = this;
        $("body").on("click", '.fg-element-edit-actions', function(event) {
            event.stopImmediatePropagation();
            $('.fg-widget-dropmenu-options').hide();
            $('.fg-widget-block').removeClass('open-widget-options');
            $("#boxCreationForm").find("input").addClass('fairgatedirty');
            //parse the all form field value as json array and assign that value to the array
            let elementId = $(this).attr('element-id');
            let actionType = $(this).attr('action-type');
            let boxId = $(this).attr('box-id');
            let pageId = jsonData.page.id;
            let dataKey = $("#createBox").attr('data-key');
            let oldKey = dataKey;
            let objectGraph = {};
            if (actionType == "delete") {
                dataKey += '.deleteElement.elementId';
                let message = _this.settings.translations.deleteElementMsg;
                let popupContent = FGTemplate.bind('deletePopup', {
                    deleteId: elementId,
                    pageId: pageId,
                    dataKey: dataKey,
                    elementIds: '',
                    displaymessage: message,
                    headerTitle: _this.settings.translations.deleteElementHeader,
                    actionType: 'removeElement'
                });
                FgModelbox.showPopup(popupContent);

            } else {
                dataKey += '.removeElement.elementId';
                $("#createBox").attr('data-key', dataKey);
                //parse the all form field value as json array and assign that value to the array
                objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
                objectGraph["page"][pageId]["removeElement"]["elementId"] = elementId;
                objectGraph["page"][pageId]["removeElement"]["boxId"] = boxId;
                let columnDetails = JSON.stringify(objectGraph);
                $("#createBox").attr('data-key', oldKey);
                //hide modal box overlay
                FgModelbox.hidePopup();
                FgXmlHttp.post(pageDetailSavePath, {
                    'postArr': columnDetails,
                    'pageDetails': JSON.stringify(jsonData)
                }, false, _this.callBackFn);
            }

        });
    }
    //COLUMN WIDTH ADJUST
    public adjustColumnWidth() {
        let _this = this;
        $("body").on('click', '.fg-dev-width-adjust', function(event) {
            event.stopImmediatePropagation();
            $("#boxCreationForm").find("input").addClass('fairgatedirty');
            let adjustType = $(this).attr('width-change');
            let columnId = $(this).attr('columnId');
            //parse the all form field value as json array and assign that value to the array
            let containerId = $(this).parents().eq(2).attr('container-id');
            let pageId = jsonData.page.id;
            let dataKey = $("#createBox").attr('data-key');
            let oldKey = dataKey;
            dataKey += '.container.' + containerId + '.column.' + columnId + '.newWidth';
            $("#createBox").attr('data-key', dataKey);
            let objectGraph = {};
            objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
            let currentWidth = $(this).parents().eq(1).attr('column-size');
            if (adjustType == 'inc') {
                objectGraph["page"][pageId]["container"][containerId]["column"][columnId]["newWidth"] = parseInt(currentWidth) + 1;
                objectGraph["page"][pageId]["container"][containerId]["column"][columnId]["type"] = adjustType;
            } else {
                objectGraph["page"][pageId]["container"][containerId]["column"][columnId]["newWidth"] = parseInt(currentWidth) - 1;
                objectGraph["page"][pageId]["container"][containerId]["column"][columnId]["type"] = adjustType;
            }
            let columnDetails = JSON.stringify(objectGraph);
            $("#createBox").attr('data-key', oldKey);
            FgXmlHttp.post(pageDetailSavePath, {
                'postArr': columnDetails,
                'pageDetails': JSON.stringify(jsonData)
            }, false, _this.callBackFn);
        });

    }
    //COLUMN WIDTH CALCULATION
    public widthCalculation() {

        let _this = this;
        $("div.contentRow").hover(function(event) {
            event.stopImmediatePropagation();
            _this.columnWidthCalculation($(this))

        }, function(eve) {
            eve.stopImmediatePropagation();
            $(".fg-left").hide();
            $(".fg-right").hide();

        });
    }
    //  ADD/EDIT CONTAINER POP UP
    public containerEdit() {
        let _this = this;
        $("body").on('click', '.editContainerpopup', function(event) {
            event.stopImmediatePropagation();
            let idString = $(this).attr('container-id');

            let containeType = $(this).attr('container-type')
            let splitArray = idString.split("-");
            let currentContainerId = splitArray[1];
            let pageId = $(this).attr('container-page-id');
            let maxCount = _this.getMaxColumnCount();
            let defaultColumnCount = ($("#" + idString).find(".columWidth").length > 0) ? $("#" + idString).find(".columWidth").length : 1;
            let headerTitle = (containeType == 'containerAdd') ? _this.settings.translations.createContainerHeader : _this.settings.translations.editContainerHeader;
            let popupContent = FGTemplate.bind('editcontainerpopup', {
                containerId: currentContainerId,
                defaultColumnCount: defaultColumnCount,
                maxColumnCount: maxCount,
                pageId: pageId,
                containerType: containeType,
                headerTitle: headerTitle
            });
            FgModelbox.showPopup(popupContent);
        })
    }


    public pageSave() {
        // SAVE FUNCTIONALITY
        let _this = this;
        let countDifference: any = 0;
        let multiClick = 1;
        $("body").on('click', '.fg-dev-datasave', function(ele) {
            if (multiClick == 1) {
                multiClick = 0;
                ele.stopImmediatePropagation();
                let pageId = $("input[name='currentPage']").val();
                let currentContainer = $("input[name='currentContainer']").val();
                let popupType = $("input[name='containerpopupType']").val();
                let columnCount = $("input[name='columnCount']").val();
                let oldCount = $("input[name='columnCount']").attr("oldCount");
                countDifference = Math.abs(oldCount - columnCount);
                let container = $("#pagecontainer-" + currentContainer);
                let calculatedWidth = 0;
                _.each(container.find('.rowColumn'), function(value, key) {
                    calculatedWidth = calculatedWidth + parseInt($(value).attr('column-size'));
                })
                let totalWidth = _this.getMaxColumnCount();
                $("#editContainer").find("input").addClass('fairgatedirty');

                let objectGraph = {};
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
                    let columnDetails = JSON.stringify(objectGraph);
                    _this.requestCall(columnDetails)

                } else {
                    if ((oldCount - columnCount) >= 1) { //decrease  
                        //check if the new count is greater than totalwidth
                        if (columnCount > totalWidth || columnCount == 0) {
                            columnCount = totalWidth;
                        }
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["delete"]["newCount"] = columnCount;
                        delete objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"];
                        var columnDetails = JSON.stringify(objectGraph);
                        _this.requestCall(columnDetails)
                    } else if ((oldCount - columnCount) < 0) {
                        //check if the new count is greater than totalwidth
                        if (columnCount > totalWidth) {
                            countDifference = totalWidth - oldCount;
                        }
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"]["addCount"] = countDifference;
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"]["currentColumnCount"] = oldCount;
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"]["currentTotalWidth"] = calculatedWidth;
                        objectGraph["page"][pageId]["container"][currentContainer]["column"]["new"]["totalWidth"] = totalWidth;
                        delete objectGraph["page"][pageId]["container"][currentContainer]["column"]["delete"];

                        var columnDetails = JSON.stringify(objectGraph);
                        _this.requestCall(columnDetails)
                    }
                }
                //hide modal box overlay
                $(this).removeClass('noClick');
                FgModelbox.hidePopup();
                multiClick = 1;
            }
        })
    }

    public addBox() {
        //ADD BOX 
        let _this = this;
        $("body").on('click', '.fg-dev-add-box', function(event) {
            event.stopImmediatePropagation();
            let columnId = $(this).attr('columnId');
            let containerId = $(this).parents().eq(1).attr('container-id');
            let pageId = $(this).parents().eq(1).attr('page-id');
            let sortOrder = ($('#containercolumn-' + columnId).find(".columnBox").length) + 1;
            let dataKey = $("#createBox").attr('data-key');
            let oldKey = dataKey;
            dataKey += ".container." + containerId + '.column.' + columnId + '.box.new.sortOrder';
            $("#createBox").attr('data-key', dataKey);
            $("#boxCreationForm").find("input").addClass('fairgatedirty');
            let objectGraph = {};
            //parse the all form field value as json array and assign that value to the array
            objectGraph = FgInternalParseFormField.formFieldParse('boxCreationForm');
            objectGraph["page"][pageId]["container"][containerId]["column"][columnId]['box']["new"]["sortOrder"] = sortOrder;
            var columnDetails = JSON.stringify(objectGraph);
            $("#createBox").attr('data-key', oldKey);
            FgXmlHttp.post(pageDetailSavePath, {
                'postArr': columnDetails
            }, false, _this.callBackFn);
        })
    }

    public mapGeneration() {
        //MAP GENERATING CODE
        $('.columnBox .fg-dev-map-element').each(function(i, value) {
            let elementId = $(value).attr('element-id');
            let mapDisplay = $("#mapDisplay-" + elementId).val().toUpperCase();;
            let latitude = $("#latitude-" + elementId).val();
            let longitude = $("#longitude-" + elementId).val();
            let mapMarker = $("#mapMarker-" + elementId).val();
            let mapZoom = parseInt($("#mapZoom-" + elementId).val());
            let mapId = "googleMap-" + elementId;
            FgMapSettings.mapShow(latitude, longitude, mapDisplay, mapZoom, mapMarker, mapId, '');

        });
    }

    public imageElementOptions(elementId: any) {
        let _this = this;
        let option1 = {
            tiles_type: "justified",
            tile_enable_action: false,
            tile_enable_overlay: false,
            tile_textpanel_padding_top: 0,
            tile_textpanel_padding_bottom: 0
        };
        let option2 = {
            tile_enable_action: false,
            tile_enable_overlay: false,
            tile_textpanel_padding_top: 0,
            tile_textpanel_padding_bottom: 0
        };
        let option3 = {
            gallery_theme: "slider",
            tile_enable_action: false,
            tile_enable_overlay: false,
            gallery_play_interval: 0,
            slider_enable_play_button: false,
            slider_enable_bullets: false,
            slider_enable_progress_indicator: false,
            slider_transition: "fade",
            slider_transition_speed: 1000,
            slider_textpanel_padding_top: 0,
            slider_textpanel_padding_bottom: 0,
        };
        if ($("#row-gallery-" + elementId).length > 0) {
            _this.unitgalleryCall("#row-gallery-" + elementId, option1);
        }
        if ($("#column-gallery-" + elementId).length > 0) {
            _this.unitgalleryCall("#column-gallery-" + elementId, option2);
        }
        if ($("#slider-gallery-" + elementId).length > 0) {
            let sliderTime = $("#slider-gallery-" + elementId).attr('data-slider-time');
            option3.gallery_play_interval = sliderTime * 1000;
            _this.unitgalleryCall("#slider-gallery-" + elementId, option3);
        }
    }

    public textElementOptions(elementId: any) {
        let sliderOption = {
            gallery_theme: "slider",
            tile_enable_action: false,
            tile_enable_overlay: false,
            slider_enable_play_button: false,
            slider_enable_bullets: false,
            slider_enable_progress_indicator: false,
            slider_textpanel_padding_top: 0,
            slider_textpanel_padding_bottom: 0,
            slider_transition: "fade",
            slider_transition_speed: 1000,
            gallery_play_interval: 5000
        }
        let _this = this;
        _this.unitgalleryCall("#gallery-textelement-" + elementId, sliderOption);
    }
    public sponsorElementOptions(elementId: any) {
        let sliderTime = $("#slider_" + elementId).attr('interval');
        $("#slider_" + elementId).FgFader({
            duration: sliderTime * 1000, //slider interval
        });
    }
     /**
     * article element carousel settings and options
     */
    public articleElementCarouselSettings(elemId) {
        console.log($("#carousel-" + elemId).length);
        $("#carousel-" + elemId).carousel({
              interval:   4000
            });
         var clickEvent = false;
            $("#carousel-" + elemId).on('click', '.nav a', function() {
              clickEvent = true;
              $('.nav li').removeClass('active');
              $(this).parent().addClass('active');    
            }).on('slid.bs.carousel', function(e) {
                $("#carousel-" + elemId+' .nav li.active').removeClass('active');
                $("#carousel-" + elemId+' .nav li:eq('+$(e.relatedTarget).index()+')').addClass('active');
            });    
            
    }
    public unitgalleryCall(identifier: string, slideroptions: any) {
        $(identifier).unitegallery(slideroptions);
    }
    public setFixedcanvasHeader() {
        var scrollPos = $(window).scrollTop();
        if (scrollPos > 150) {
            $('.fg-cms-page-canvas-wrapper').addClass('fixed-header');
        } else if (scrollPos < 100) {
            $('.fg-cms-page-canvas-wrapper').removeClass('fixed-header');
        }
    }
    public formElementPopupAction() {
        $(document).off('click', '#formElementDontUse');
        $(document).on('click', '#formElementDontUse', function() {
            $('.fg-form-element-data').children().remove();
            $('span.required').remove();
            $('.fgFormTemplateError').removeClass('has-error');
            $('#fg-form-element-popup').modal('hide');
        });
        $(document).off('click', '#formElementUseTemplate');
        $(document).on('click', '#formElementUseTemplate', function() {
            $('#formElementId').val('');
            let formTempId = $('.formTemplateSelect option:selected').val();
            var formType = $("input[name=selectForm]:checked").val();
            if (formType == 'newForm') {
                $("#elementcreationForm").attr("action", formElementCreatePath);
                $("#elementcreationForm").submit();
            } else {

                if (formTempId === '') {
                    $('.fgFormTemplateError').addClass('has-error');
                    $('.fg-form-element-data').append('<span class="required">' + required + '</span>');
                    return false;
                } else {
                    $('#formElementId').val(formTempId);
                }
                $("#elementcreationForm").attr("action", formElementCreatePath);
                $("#elementcreationForm").submit();
            }
        });
        // create form modal popup select dropdown enable/disable
        $('input:radio[name=selectForm]').on('change', function() {
            if ($("input[name='selectForm']:checked").val() == 'newForm') {
                $('.selectpicker.formTemplateSelect').prop('disabled', true);
            }
            if ($("input[name='selectForm']:checked").val() == 'existingForm') {
                $('.selectpicker.formTemplateSelect').prop('disabled', false);
            }
            $('.selectpicker.formTemplateSelect').selectpicker('refresh');
        });
    }

    public initCaptcha(elementId) {
        if ($("#" + elementId + " .g-recaptcha").length > 0) {
            $("#" + elementId).find('.fg-form-element-submit').attr('disabled', true);
            var captchaContainer = null;
            var formCaptcha = function() {
                var captchaId = $("#" + elementId + " .g-recaptcha").attr('id');
                captchaContainer = grecaptcha.render(captchaId, {
                    'sitekey': sitekeys,
                    'callback': function(response) {
                        $("#" + elementId).find('.fg-form-element-submit').removeAttr('disabled');
                    }
                });
                $("#" + captchaId).attr('captchaClientId', captchaContainer);
            };
            setTimeout(function() { formCaptcha(); }, 1000);
        }
    }
    public handleToolTip(elementId) {
        var thisClass = this;
        $("#" + elementId + " label span[data-content]").each(function() {
            if ($(this).attr('data-content').trim() != '') {
                $(this).addClass('fg-custom-popovers fg-dotted-br');
            }
        });
    };

    //INITIALIZE PAGE
    public pagedocInit() {

        $(window).load(function() {
            cmspage.setFixedcanvasHeader();
        });
        $(window).scroll(function() {
            cmspage.setFixedcanvasHeader();
        });
        //Language switch click event
        $(document).off('click', 'button[data-elem-function=switch_lang]');
        $(document).on('click', '.fg-action-menu-wrapper button[data-elem-function=switch_lang]', function() {
            let selectedLang = $(this).attr('data-selected-lang');
            $('.fg-action-menu-wrapper .btlang').removeClass('active');
            $(this).addClass('active');
            FgUtility.showTranslation(selectedLang);
        });
        // create form modal popup select dropdown enable/disable
        cmspage.callClipBoard(clipBoarDetails);
        //
        var plusminusOption = {
            'selector': ".selectButton"
        }

        // PLUS/MINUS FUNCTIONALITY 
        var inputplusminus = new Fgplusminus(plusminusOption);
        //DEFAULT SECTION OF CONTENET TAB
        if (pagetype == 'sidebar') {
            var active = '{{isActveTab}}';
            $('#paneltab > li').removeClass('active');
            $('#paneltab >  li').eq(active).addClass('active');
        } else {
            $('#paneltab > li').removeClass('active');
            $('#fg_tab_content').addClass('active')
        }


        $(document).ready(function() {
            //SLIM SCROLL IN CLIPBOARD
            cmspage.clipboardSlimScroll();
            //REINITIALIZATION OF MORE TAB
            setTimeout(function() {
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


            $('.fg-action-editTitle').on('click', function() {
                $.get(editPagetitlePopupPath, {
                    'pageId': pageDetails.id
                }, function(data) {
                    FgModelbox.showPopup(data);
                });
            });
            //fair 2403 new option to hide page title
            $('.fg-action-pagetitle-switch').on('click', function() {
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
            cmspage.pageSave()
            //ADD BOX FUNCTIONALITY
            cmspage.addBox();

            //CLIP BOARD TOGGLE 
            $('body').on('click', '.fg-clipboard-nav', function(e) {
                e.stopImmediatePropagation();
                $('body').toggleClass('fg-clipboard-tray-open');
            });

            if (pagetype == 'sidebar') {
                $('#paneltab > li').removeClass('active');
                $('#paneltab >  li').eq(pagetabActive).addClass('active');
            } else {
                $('#paneltab > li').removeClass('active');
                $('#fg_tab_content').addClass('active')
            }
            //form element 
        })


    }

    // Reload existing page 
    public saveJsonData() {
        FgXmlHttp.post(pageJsonSavePath, {
            'pageId': this.settings.data.page.id,
        }, false);

        FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);

    }

    public handleCotactTableElement(elementId, data) {
        var contactTable = new FgCmsContactTable();
        contactTable.tableId = 'website-datatable-list-' + elementId;
        contactTable.filterElementId = 'fg-contact-table-filter-' + elementId;
        contactTable.exportSearchBoxId = 'fg-contact-table-export-search-' + elementId;
        contactTable.searchTextBoxId = 'fg_dev_member_search_' + (elementId.split('-')[1]);
        contactTable.listAjaxPath = contactTableListUrl.replace('dummyType', data.tableInitialData.filterType);
        contactTable.columnData = data.columnData;
        contactTable.tableInitialData = data.tableInitialData;
        contactTable.filterData = data.filterData;
        contactTable.clubData = data.clubDetails;
        contactTable.renderExportAndSearch();
        contactTable.renderFilter();
        contactTable.drawContactTable();
    };
    public handleContactPortraitElement(elementId, data) {
        var elmtId = $("#" + elementId).attr('element-id');
        if (data.stage == 'stage4'){
            if (_.has(portraitElementSettings, elmtId)) {
                let portraitData = portraitElementSettings[elmtId].data.portraitElement;
                
                let displayedPortraitPages =  (portraitElementSettings[elmtId].data.portraitElement.columnWidth==2) ? 1 :4;
                var options = {
                    boxId: 'columnbox-' + portraitData.boxId,
                    elemId: elementId,
                    initCompletedCallback: function($object) {
                    },
                    filter : portraitData.filter,
                    filterData: portraitData.filterData,
                    searchBox: portraitData.tableSearch,
                    portraitWrapperData: portraitData,
                    pagination:true,
                    paginationOptions : {
                    selector: '#fg-pagination-' + elementId,
                    options: {
                        items: parseInt(data.totalRecords),					//Total number of items that will be used to calculate the pages.
                        itemsOnPage: parseInt(portraitData.rowPerpage) * parseInt(portraitData.portraitPerRow),
                        displayedPages :displayedPortraitPages,
                        onPageClick: function(pageNumber, event) {
                            // Callback triggered when a page is clicked
                            // Page number is given as an optional parameter
                            FgPortraitElement.getContacts(false, elementId, pageNumber);
                        }
                    } },      
                    clubDetails: data.clubDetails,
                    dataUrl: data.dataUrl,
                    portraitContactsData: data
                };
                FgPortraitElement.initSettings(options);        
            }
        }else{
            $('#' + elementId + ' .fg-contact-portrait-empty-box').removeClass('hide');
        }
    }

    public handleNewsletterArchiveElement(elementId, data, widthValue) {
        var fgCmsNewsletterArchive = new FgCmsNewsletterArchive();
        fgCmsNewsletterArchive.tableId = 'website-datatable-list-' + elementId;
        fgCmsNewsletterArchive.listAjaxPath = newsletterArchiveListUrl;
        fgCmsNewsletterArchive.columnData = data.columnData;
        fgCmsNewsletterArchive.widthValue = widthValue;
        fgCmsNewsletterArchive.drawNewsletterArchiveTable();
    };
    public pageCallBackFunction() {
        // EDIT BOX OVERLAY GENERATION
        this.boxOverlay();
        //DELETE BOX
        this.deleteBox();
        //ELEMENT EDIT OVERLAY  
        this.elementOverlay();
        //ACTION MENU POSITION SETTING 
        this.setMenuPosition();
        // CREATE ACTION MENU FOR ELEMENT OVERLAY 
        this.actionMenuDisplay()
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

    }
}



