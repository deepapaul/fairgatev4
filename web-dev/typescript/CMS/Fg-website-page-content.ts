/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />

class Fgwebsitepage {
    loginElement = [];
    onLoadData = {}
    settings: Object = '';
    sortSetting: Object = '';
    pageTranslation: Object = {};
    ogTags = [];
    defaultSettings: Object = {
        sidebarContainer: '#sidebarBox',
        contentContainer: '#contentBox',
        footerContainer: '#footerBox',
        containerType: 'content',
        data: {},
        boxTemplateId: 'pageBox', // selector to which the overview content to be rendered
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
                'textBase': 'templateTextBaseTemplate',
                'articles': 'templateArticle',
                'calendar': 'templateCalendar',
                'map': 'templateMap',
                'login': 'templateLogin',
                'image': 'templateImage',
                'imageBase': 'templateImageBaseTemplate',
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
        pageInitCallback: function() { },
        containerBoxCompletedCallback: function($object) { },
        initFooterContentCallback: function() { },
        initMainContentCallback: function() { },
        initSidebarContentCallback: function() { },
        renderAllAreaContentCallback: function() { }

    };
    defaultSortOptions: Object = {
        opacity: 0.8,
        forcePlaceholderSize: true,
        tolerance: "pointer"

    }
    constructor() {

    }

    /**
     * Page document initialisation
     * 
     */
    public pagedocInit() {
        this.initSettings(cmsOptions);

        if (_.size(jsonData.page) > 0) {
            if (_.size(jsonData.page.page) > 0) {

                let pagecontent = this.contentInit();
                this.appendContent(cmsOptions.mainContainer, pagecontent);
                
            }
        }
        if (_.size(jsonData.sidebar) > 0) {

            let sidebarContent = this.sidebarInit();
            this.appendContent(cmsOptions.sideContainer, sidebarContent);
        }

        if (_.size(jsonData.footer) > 0) {

            let footerContent = this.footerInit();
            this.appendContent(cmsOptions.footerContainer, footerContent);
        }
        
        //callback after all contents of a page are present in dom
        this.settings.renderAllAreaContentCallback.call();
    }

    
    /**
     * initialize settings
     * 
     * @param options
     */
    public initSettings(options: Object) {
        // for hiding link icon in images without links in image element with links
        $('body').on('mouseover', '.ug-gallery-wrapper .ug-thumb-wrapper', function() {
            var href = $(this).attr('href');
            if (href == 'javascript:void(0)') {
                $(this).find('.ug-thumb-overlay').removeClass('ug-thumb-overlay');
                $(this).find('.ug-icon-link').remove();
                $(this).css({ 'cursor': 'default', 'pointer-events': 'none' });
            }
        })
        this.settings = $.extend(true, {}, this.defaultSettings, options);
        $(window).resize(function() {
            FgWebsiteThemeObj.makeFooterSticky();
        });

    }
    
    /**
     * Initialize the content area
     * 
     * call back initMainContentCallback
     * 
     */
    public contentInit() {

        this.settings.data.page.container = jsonData.page.page.container;
        this.settings.containerType = 'content';
        let pageHtml = this.pageContainer();
        if(typeof jsonData['ajax']['page'] != "undefined"){
            this.ajaxElementCalls(jsonData['ajax']['page'],jsonData.pageId);
        }
        this.settings.initMainContentCallback.call();
        
        return pageHtml;
    }
   
    
    
    /**
     * Initialize the sidebar area
     * 
     * callback initSidebarContentCallback
     * 
     */
    public sidebarInit() {

        this.settings.data.page.container = jsonData.sidebar.page.container;
        this.settings.containerType = 'sidebar';
        this.settings.sidebarSide = (_.size(this.settings.data.sidebar) > 0) ? this.settings.data.sidebar.side : '';

        // To set the sidebar size
        this.settings.sidebarSize = (_.size(this.settings.data.sidebar) > 0) ? this.settings.data.sidebar.width_value : '';

        let pageHtml = this.pageContainer();
         if(typeof jsonData['ajax']['sidebar'] != "undefined"){
            this.ajaxElementCalls(jsonData['ajax']['sidebar'],jsonData.sidebarId);
         }
        this.settings.initSidebarContentCallback.call();
        
        return pageHtml;
    }
    
    /**
     * Initialize the footer area
     * 
     * call back initFooterContentCallback 
     * 
     */
    public footerInit() {
        this.settings.data.page.container = jsonData.footer.page.container;
        this.settings.containerType = 'footer';
        let pageHtml = this.pageContainer();
         if(typeof jsonData['ajax']['footer'] != "undefined"){
            this.ajaxElementCalls(jsonData['ajax']['footer'],jsonData.footerId);
         }
        this.settings.initFooterContentCallback.call();
        
        return pageHtml;
    }
    
    /**
     * Render the container
     * 
     * callback initContainerCallback
     */
    public pageContainer() {
        let containerHtml = '';
        let _this = this;
        let container = '';
        this.settings.data.page.container = _.sortBy(this.settings.data.page.container, 'sortOrder');
        _.each(this.settings.data.page.container, function(containerValues: any, index) {
            //crea t e container box id
            container = 'pagecontainer-' + containerValues.containerId;
            //create container
            _this.settings.container.data = containerValues;
            containerHtml += _this.renderContainerBox(container);
            _this.settings.containerBoxCompletedCallback.call(containerValues);
        });

        _this.settings.initContainerCallback.call();
        
        return containerHtml;
    }
    

    /**
     * Render container box
     * 
     * @param   container 
     */
    public renderContainerBox(container: string) {
        if (_.size(this.settings.container.data) > 0) {
            //render all columns of particular container
            let columnContent = this.containerColumns();

            return FGTemplate.bind(this.settings.container.templateId, { details: this.settings.container.data, containerid: container, columnDetails: columnContent, pageId: this.settings.data.page.id, settingDetails: this.settings, });
        }
    }
    
    /**
     * Render container column
     * 
     * callback initColumnCallback
     */
    public containerColumns() {
        let columnHtml = '';
        let idColumn = '';
        let _this = this;
        this.settings.container.data.columns = _.sortBy(this.settings.container.data.columns, 'sortOrder');
        _.each(this.settings.container.data.columns, function(columnValues: any, index) {
            //create column box id
            idColumn = 'containercolumn-' + columnValues.columnId;
            //create columns
            _this.settings.column.data = columnValues;
            columnHtml += _this.renderColumnBox(idColumn);

        });
        this.settings.initColumnCallback.call();
        
        return columnHtml;
    }
     
    /**
     * Render column box
     * 
     * @idcolumn html id of column
     */
    public renderColumnBox(idColumn: string) {
        //render all column box
        let boxContent = this.columnBox();

        return FGTemplate.bind(this.settings.column.templateId, { details: this.settings.column.data, columnid: idColumn, settingDetails: this.settings, boxDetails: boxContent });
        
        $('#columnbox-'+idColumn).trigger( "loaded.fg.columnBox");
    }
    
    /**
     * Render column box
     */
    public columnBox() {
        let boxHtml = '';
        let _this = this;
        let idBox = '';
        this.settings.column.data.box = _.sortBy(this.settings.column.data.box, 'sortOrder');
        _.each(this.settings.column.data.box, function(boxValues: any, index) {
            //create box id
            idBox = 'columnbox-' + boxValues.boxId;
            //create box
            _this.settings.columnbox.data = boxValues;
            boxHtml += _this.renderBox(idBox);
            //append box to specified columns
        });

        this.settings.initColumnBoxCallback.call();
        
        return boxHtml;

    }
    
    /**
     * Render box
     * @param idBox html id of box
     */
    public renderBox(idBox: string) {
        //render all the box element
        let elementContent = this.elementBox();

        return FGTemplate.bind(this.settings.columnbox.templateId, { details: this.settings.columnbox.data, boxid: idBox, elementDetails: elementContent });
        
        $('#'+idBox).trigger( "loaded.fg.box");
    }
    
    /**
     * Render element box
     */
    public elementBox() {
        let elementHtml = '';
        let _this = this;
        let idElement = '';
        this.settings.columnbox.data.Element = _.sortBy(this.settings.columnbox.data.Element, 'sortOrder');
        _.each(this.settings.columnbox.data.Element, function(elementValues: any, index) {
            idElement = 'elementbox-' + elementValues.elementId;
            _this.settings.elementbox.data = $.extend(elementValues,this.onLoadData[elementValues.elementId]);
            elementHtml += _this.renderElement(idElement);
            $('body').trigger("loaded.fg.element." +elementValues.elementType);
        });

        this.settings.initElementBoxCallback.call();
        
        return elementHtml;

    }
    
    /**
     * Render elements
     * 
     * @param idElement html id of elements
     */
    public renderElement(idElement: string) {
        
        let pageElements = JSON.parse(jsonData.pageElements);
        let sidebarElements = JSON.parse(jsonData.sidebarElements);
        
        if (jsonData['ajaxUrl'].hasOwnProperty(this.settings.elementbox.data.elementType)) {
            
            let elementId = this.settings.elementbox.data.elementId;
            let dataE = this.settings.elementbox.data;
            if (pageElements.hasOwnProperty(elementId)){
                if (typeof jsonData['ajax']['page'] == "undefined"){
                    jsonData['ajax']['page'] = {};
                }
                this.buildAjaxArray('page',this.settings.elementbox.data.elementType,dataE);
            } else if(sidebarElements.hasOwnProperty(elementId)){
                if (typeof jsonData['ajax']['sidebar'] == "undefined"){
                    jsonData['ajax']['sidebar'] = {};
                }
                this.buildAjaxArray('sidebar',this.settings.elementbox.data.elementType,dataE);
                //jsonData.sidebarId
            }else{
                if (typeof jsonData['ajax']['footer'] == "undefined"){
                    jsonData['ajax']['footer'] = {};
                }
                this.buildAjaxArray('footer',this.settings.elementbox.data.elementType,dataE);
                //jsonData.footerId
            }
        }
        //render the outer element div for both onload and ajax elelemnts
        return FGTemplate.bind(this.settings.elementbox.templateId[this.settings.elementbox.data.elementType], { details: this.settings.elementbox.data, elementid: idElement, settingDetails: this.settings,  clubDefaultLang: clubDefaultLang });
        
    }
    /**
     * build the ajax element array type wise
     */
    public buildAjaxArray(pageType,elementType,dataE){
        //Build an array of the ajax call needed elements
            if (!jsonData['ajax'][pageType].hasOwnProperty(elementType)){
                jsonData['ajax'][pageType][elementType] = [];
                jsonData['ajax'][pageType][elementType][0] = dataE;
                
            } else {
                jsonData['ajax'][pageType][elementType].push(dataE);
            }  
    }
    
    /**
     * Render ajax call elements
     * @param pageId page id of page/sidebar/footer
     */
    public ajaxElementCalls(json ,page:number) {
        let _this = this;
        
        /**
         * Image element
         */
        if(typeof json.image != "undefined" && _.size(json.image) > 0) {
            let baseImageHtml = _.template($('#'+this.defaultSettings.elementbox.templateId['imageBase']).html());
            let elemIds = _.pluck(json.image,"elementId");
             
            $.post( jsonData['ajaxUrl']['image'], {elementIds: elemIds,pageId: page},function(imageData) {
                _.each(imageData, function(datas, elem) {
                    let igArray = datas.finalArray;
                    let htmlContent  = baseImageHtml({ imageData : igArray.imageData, columnWidth: igArray.columnWidth,imageWidth : igArray.imageWidth, club_id: igArray.club_id, elementId : elem, navPath: jsonData.navPath });
                    $('#elementbox-' +elem).html(htmlContent);
                    _this.imageElementUniteGallerySettings(elem);
                });
            });
            
        }
        
        /**
         * Text element
         */
        if(typeof json.text != "undefined" && _.size(json.text) > 0){
            let baseTextHtml = _.template($('#'+this.defaultSettings.elementbox.templateId['textBase']).html());
            let elemIds = _.pluck(json.text,"elementId");
             
            $.post( jsonData['ajaxUrl']['text'], {elementIds: elemIds,pageId: page},function(textData) {
                _.each(textData, function(datas, elem) {
                    let htmlContent  = baseTextHtml({ textelement : datas });
                    $('#elementbox-' +datas.element).html(htmlContent);
                    _this.textElementUniteGallerySettings(datas.element);
                });
            });
        }
        
        /**
         * Articles element
         */
        if(typeof json.articles != "undefined" && _.size(json.articles) > 0){
            _.each(json.articles, function(data) {
                let articleUrl = jsonData['ajaxUrl']['articles'].replace('%23dummyElement%23',data.elementId).replace('%23dummy%23',page)+ '?menu=' + menu;
                $.post(articleUrl , {},function(articleData) {
                    $('#elementbox-' +data.elementId).html(articleData.htmlContent);
                    $( '#elementbox-' +data.elementId ).find(" a:first" ).addClass( "active" );
                    $( '#elementbox-' +data.elementId ).find(" ul li:first" ).addClass( "active" );
                    _this.articleElementCarouselSettings(data.elementId);
                });
                
            });
        }
        
        /**
         * Calendar element
         */
        if(typeof json.calendar != "undefined" && _.size(json.calendar) > 0){
             _.each(json['calendar'], function(data) {
                let calendarUrl = jsonData['ajaxUrl']['calendar'].replace('%23dummyElement%23',data.elementId).replace('%23dummy%23',page);
                $.post(calendarUrl, {},function(datas) {
                     $('#elementbox-' + datas.elementId).html(datas.htmlContent);
                    _this.handleCalendarClicks();
                });
             });
            
        }
        
        /**
         * Contact Table element
         */
        if(typeof json['contacts-table'] != "undefined" && _.size(json['contacts-table']) > 0){
            _.each(json['contacts-table'], function(data) {
                let cTUrl = jsonData['ajaxUrl']['contacts-table'].replace('%23dummyElement%23',data.elementId);
                $.post(cTUrl, {}, function(dataa) {
                    _this.handleCotactTableElement('elementbox-' +data.elementId, dataa);
                });
            });
        }
        
        /**
         * Form Element
         */
        if (typeof json.form != "undefined" && _.size(json.form) > 0) {
            _.each(json.form, function(data) {
                let formUrl = jsonData['ajaxUrl']['form'].replace('%23dummyElement%23',data.elementId);
                $.post(formUrl, {},function(data) {
                    formDataArray['elementbox-' +data.elementId] = data;
                    _this.handleFormElement('elementbox-' +data.elementId, data);
                });
             });
        }
        
        /**
         * Contact application form
         */
        if(typeof json['contact-application-form'] != "undefined" && _.size(json['contact-application-form']) > 0){
            _.each(json['contact-application-form'], function(data) {
                let cformUrl = jsonData['ajaxUrl']['contact-application-form'].replace('%23dummyElement%23',data.elementId);
                $.post(cformUrl, {},function(dataa) {
                    formDataArray['elementbox-' +dataa.elementId] = dataa;
                    _this.handleFormElement('elementbox-' +dataa.elementId, dataa);
                });
             });
        }
        
        /**
         * Newsletter archive
         */
        if(typeof json['newsletter-archive'] != "undefined" && _.size(json['newsletter-archive']) > 0){
             _.each(json['newsletter-archive'], function(data) {
                let elementId = data.elementId;
                let NAUrl = jsonData['ajaxUrl']['newsletter-archive'].replace('%23dummyElement%23',elementId).replace('%23dummy%23',page);
                $.post(NAUrl, {},function(dataa) {
                   _this.handleNewsletterArchiveElement('elementbox-' +elementId, dataa, dataa.widthValue);
                });
             });
        }
        /**
         * Newsletter Subscription
         */
        if(typeof json['newsletter-subscription'] != "undefined" && _.size(json['newsletter-subscription']) > 0){
             _.each(json['newsletter-subscription'], function(data) {
                let elementId = data.elementId;
                let NSUrl = jsonData['ajaxUrl']['newsletter-subscription'].replace('%23dummyElement%23',elementId);
                $.post(NSUrl, {},function(dataa) {
                    $('#elementbox-' +elementId).html(dataa.htmlContent);
                    _this.handleSubscriptionForm('elementbox-' +elementId);
                });
             });
        }
        /**
         * Portrait Element
         */
        if(typeof json['portrait-element'] != "undefined" && _.size(json['portrait-element']) > 0){
            _.each(json['portrait-element'], function(data) {
                let elementId = data.elementId;
                let portraitUrl = jsonData['ajaxUrl']['portrait-element'].replace('%23dummyElement%23',elementId);
                $.post(portraitUrl, {},function(dataa) {
                    _this.handleContactPortraitElement('elementbox-' +elementId,dataa);
                });
             });
        }
        /**
         * Sponsor ads
         */
        if(typeof json['sponsor-ads'] != "undefined" && _.size(json['sponsor-ads']) > 0){
            _.each(json['sponsor-ads'], function(data) {
                let SelementId = data.elementId;
                let sponsorUrl = jsonData['ajaxUrl']['sponsor-ads'].replace('%23dummyElement%23',SelementId).replace('%23dummy%23',page)
                $.post(sponsorUrl, {},function(dataa) {
                    $('#elementbox-' +SelementId).html(dataa.htmlContent);
                    _this.sponsorAdsCallback('elementbox-' +SelementId,SelementId);
                });
             });
        }
    }
    
    /**
     * sponsor ads callback
     */
    public  sponsorAdsCallback(elementId,elemId){
        var _this = this;
        FgTooltip.init();
        var elementWidth = $("#" + elementId).width();
        var logoWidth_1 = (elementWidth > 150) ? 'original' : ((elementWidth > 65) ? 'width_150' : 'width_65');
        var sponsorWidth_1 = (elementWidth > 1100) ? 'original' : ((elementWidth > 500) ? '1100' : ((elementWidth > 250) ? '500' : ((elementWidth > 150) ? '250' : '150')));
        $("#" + elementId + ' .faderImg').each(function(i, e) {
            var srcArray = $(e).attr('data-src').split('/');
            var folderIndex = srcArray.length - 2;
            srcArray[folderIndex] = $(e).hasClass('faderImgLogo') ? logoWidth_1 : sponsorWidth_1;
            $(e).attr('src', srcArray.join('/')).removeClass('hide');
        });
        if ($('.fg-sponsor-ads-widget').hasClass('fg-fader')) {
            setTimeout(function() {
                _this.sponsorElementOptions(elemId);
            }, 3000);
        }
        $("[data-toggle='tooltip']").on('shown.bs.tooltip', function(e) {
            setTimeout(function() {
                $('.tooltip').tooltip('hide');
            }, 5000);
        });
    }

    /**
     * Redirect calendar list clicks to detail page
     */
    public handleCalendarClicks() {
        $('.fg-dev-calendar-detail').on('click', function() {
            let hrefUrl = $(this).attr('data-href').replace('NAV_IDENTIFIER', menu);
            window.location.href = hrefUrl
        });
    }

    /**
    * Handle form element submit
    */
    public handleFormElementSubmit(elementId: string) {
        $("body").off("click", "#" + elementId + ' .fg-form-element-submit');
        $("body").on("click","#" + elementId + ' .fg-form-element-submit', function() {
            var formId = $(this).parents('form').attr('id');
            var validObj = new FgWebsiteFormValidation(formId);
            validObj.validateForm();
        });
    }
    
    /**
     * Handle the contact table element
     * @param   elementId
     * @param   data
     */
    public handleCotactTableElement(elementId : string, data : Object) {
        contactTable = new FgCmsContactTable();
        contactTable.elementId = elementId;
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

    }
    
    /**
     * Handle the portrait element
     * @param   elementId   html id of portrait element
     * @param   data        portrait setting object 
     */
    public handleContactPortraitElement(elementId: String, data: Object) {
        var elmtId = $("#" + elementId).attr('element-id');
        var self = this;
        if (_.has(portraitElementSettings, elmtId)) {
            let displayedPortraitPages =  (portraitElementSettings[elmtId].data.portraitElement.columnWidth==2) ? 1 :4;
            var portraitData = portraitElementSettings[elmtId].data.portraitElement;
            var options = {
                boxId : 'columnbox-'+portraitData.boxId,
                elemId:elementId,
                initCompletedCallback: function ($object){
                },
                renderContactsCompletedCallback: function (){
                    self.handleToolTip(elementId);
                },
                filter : portraitData.filter,
                filterData: portraitData.filterData,
                searchBox: portraitData.tableSearch,
                portraitWrapperData : portraitData,
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
                clubDetails:data.clubDetails,
                dataUrl:data.dataUrl,
                portraitContactsData:data
            };
            FgPortraitElement.initSettings(options);
        }
    }
    /**
     * handle Newsletter Archive Element
     */
    public handleNewsletterArchiveElement(elementId, data, widthValue) {
        var fgCmsNewsletterArchive = new FgCmsNewsletterArchive();
        fgCmsNewsletterArchive.tableId = 'website-datatable-list-' + elementId;
        fgCmsNewsletterArchive.listAjaxPath = newsletterArchiveListUrl;
        fgCmsNewsletterArchive.columnData = data.columnData;
        fgCmsNewsletterArchive.widthValue = widthValue;
        fgCmsNewsletterArchive.drawNewsletterArchiveTable();
    }
    /**
     * handle date picker
     * @param elementId element id
     */
    public handleTimePicker(elementId: number) {
        var timeFormatData = {};
        timeFormatData['hh:ii'] = { format: 'hh:mm', seperator: ':' };
        timeFormatData['hh.ii'] = { format: 'hh.mm', seperator: '.' };
        timeFormatData['hh ## ii'] = { format: 'hh h mm', seperator: ' h ' };
        timeFormatData['HH:ii P'] = { format: 'hh:mm AA', seperator: ':' };

        var currentTimeFormat = timeFormatData[FgLocaleSettingsData.jqueryDtimeFormat];
        var setMeridian = false;
        $('.open-timepicker').click(function(event) {
            event.preventDefault();
            $(this).parent().find('.timeclick').trigger('click');
        });
        if (currentTimeFormat == timeFormatData['HH:ii P']) {
            setMeridian = true;
        }
        $('#' + elementId + ' [data-timepic]').each(function() {
            var parentDiv = $(this).attr('id');
            $('#' + parentDiv).timepicker({
                showMeridian: setMeridian,
                defaultTime: false,
                minuteStep: 5,
            });

        });

    }

    /**
     * Handle form element submit
     */
    public handleFormFileUpload(elementId: number) {
        $("#" + elementId + " input[type=file]").each(function() {
            let fieldType = $(this).attr('fieldtype');
            $(this).fileupload({
                dataType: 'json',
                autoUpload: true,
                add: function(e, data) {
                    $(this).parent().find('input[data-file]').val('');
                    let itemId = $.now();
                    if (fieldType == 'imageupload') {
                        var acceptFileTypes = /^image\/(gif|jpe?g|png|bmp)$/i;
                        if (data.originalFiles[0]['type'].length && !acceptFileTypes.test(data.originalFiles[0]['type'])) {
                            $(this).parent().find('input[data-file-name]').val(data.originalFiles[0]['name']);
                            $(this).parents('.form-group').addClass('has-error');
                            $(this).parent().find('span.help-block').remove();
                            $(this).parent().append('<span data-file-error class="help-block">' + formMessages.fileType + '</span>');
                            return false;
                        }
                    }
                    if (15728641 < data.files[0].size) {
                        $(this).parents('.form-group').addClass('has-error');
                        $(this).parent().find('span.help-block').remove();
                        $(this).parent().append('<span data-file-error class="help-block">' + $(this).data('exceedmsg') + '</span>');
                        grecaptcha.reset($("#" + elementId + " .g-recaptcha").attr('captchaclientid'));
                        $("#" + elementId + " .fg-form-element-submit").attr('disabled', 'disabled');
                        return false;
                    } else {
                        $(this).parents('[data-file-wrap]').find('input[data-file-name]').val(data.files[0].name);
                        $(this).parents('.form-group').removeClass('has-error');
                        $(this).parent().find('#file-error').remove();
                        var fileName = data.files[0].name;
                        fileName = fileName.replace(/[&\/\\#,+()$~%'"`^=|:;*?<>{}]/g, '');
                        fileName = fileName.replace(/ /g, '-');
                        fileName = itemId + '--' + fileName;
                        data.formData = { title: fileName, nowtime: itemId };
                        var jqXHR = data.submit();
                    }
                },
                done: function(e, data) {
                    var result = data.result;
                    if (result.status == 'success') {
                        $(this).parent().find('input[data-file]').val(data.formData.nowtime + '#-#' + data.formData.title);
                    } else {
                        $(this).parents('.form-group').addClass('has-error');
                        $(this).parents('[data-file-wrap]').find('input[data-file-name]').val('');
                        var errorMesg = (result.error == 'INVALID_VIRUS_FILE' || result.error == 'VIRUS_FILE_CONTACT') ? formMessages.virus : formMessages.fileType;
                        $(this).parent().append('<span id="file-error" class="help-block">' + errorMesg + '</span>');
                        grecaptcha.reset($("#" + elementId + " .g-recaptcha").attr('captchaclientid'));
                        $("#" + elementId + " .fg-form-element-submit").attr('disabled', 'disabled');
                    }
                }
            });
        });
        $("#" + elementId + " .alert .closeIt").click(function() {
            $(this).parent().addClass('hide');
        });
    }
    /**
     * handle the form eleemnt
     */
    public handleFormElement(elementId: string, data: any[]) {
        if (data.formData == null || data.formData == '') {
            return true;
        }
        var templateId = (data.elementType !== 'contact-application-form') ? 'templateFormField' : 'templateContactApplicationFormField';
        var formOption = _.isUndefined(data.formOption.successmessagemain) ? { "successmessagemain": "", "successmessage": [] } : data.formOption;
        let dataHtml = FGTemplate.bind(templateId, { formDetails: data.formData, defLang: data.defLang, formMessage: formOption, elementId: data.elementId, contactFormOptions: data.contactFormOptions });
        $("#" + elementId).html(dataHtml);
        buttonText = $("#" + elementId).find('form input[type=file]').attr('data-buttonText');
        $("#" + elementId + " input:checkbox,#" + elementId + " input:radio").uniform();
        var defaultSettings: Object = {
            language: data.defLang,
            format: FgLocaleSettingsData.jqueryDateFormat,
            autoclose: true,
            weekStart: 1,
            clearBtn: true
        };
        var dateSettings = $.extend(true, {}, defaultSettings);
        $("#" + elementId + " .fg-datepicker1").each(function() {
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
        var nonSelected = $("#" + elementId + " select.bs-select").data('none-selected');
        $("#" + elementId + " .bs-select").selectpicker({
            noneSelectedText: nonSelected,
            countSelectedText: jstranslations.countSelectedText,
            tickIcon: 'fa fa-check'
        }).on('change', function() {
            if ($(this).selectpicker('val') !== '') {
                $(this).closest('.form-group').removeClass('has-error');
                $(this).closest('.form-group').find('.help-block').remove();
            }
        });
        this.handleFormElementSubmit(elementId);
        this.handleFormFileUpload(elementId);
        this.handleTimePicker(elementId);
        FgGlobalSettings.handleInputmask();
        var num = new FgNumber({ 'selector': '#' + elementId + ' .selectButton', 'inputNum': '#' + elementId + ' input.input-number' });
        num.init();
        if ($('body').find('.custom-popup').length == 0) {
            $('body').append('<div class="custom-popup"><div class="popover bottom"><div class="arrow"></div><div class="popover-content"></div></div></div>');
        }
        this.handleToolTip(elementId);

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
    /**
     * tooltip click actions
     */
    public handleToolTip(elementId: number) {
        let thisClass = this;
        $("#" + elementId + " label span[data-content]").each(function() {
            if ($(this).attr('data-content').trim() != '') {
                $(this).addClass('fg-custom-popovers fg-dotted-br');
            }

        });
        $('body').on('mouseover click', '.fg-custom-popovers', function(e) {
            var _this = $(this),
                thisContent = _this.data('content'),
                posLeft = _this.offset().left - 10,
                posTop = _this.offset().top + 50;
            thisClass.showTooltip({ element: e, content: thisContent, position: [posLeft, posTop] });
            $('.popover .popover-content').width($('.popover').width() - 27);
        });
        $('body').on('mouseout', '.fg-custom-popovers', function() {
            $('body').find('.custom-popup').hide();
            $('.popover .popover-content').width('');
        });
    }
    /**
     * show tool tips
     */
    public showTooltip(obj: Object) {
        var targetElement = $('body').find('.custom-popup'), elementContent = targetElement.find('.popover-content');
        elementContent.html(obj.content);
        targetElement.css({ 'left': obj.position[0], 'top': obj.position[1] });
        targetElement.show();
    }
    /**
     * render page
     */
    public renderPage(jsonData: Object) {

        return FGTemplate.bind(this.settings.boxTemplateId, jsonData);

    }
    /**
     * append content
     */
    public appendContent(appendObj: String, pageContent: String) {
        $(appendObj).html(pageContent);
        this.settings.pageInitCallback.call();
    }

    /**
     * calculate column width
     */
    public columnWidthCalculation(currentContainer: Object) {
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

    /**
     * column count 
     */
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

    //ajax call for form submission
    public requestCall(columnDetails: any[]) {
        FgXmlHttp.post(pageDetailSavePath, {
            'postArr': columnDetails,
            'pageDetails': JSON.stringify(jsonData)
        }, false, this.callBackFn);
    }
    /**
     * map generation
     */
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
    
    /**
     * unite gallery settings for image element
     */
    public imageElementUniteGallerySettings(elementId: Number) {
        let _this = this;
        let option3: Object = {
            gallery_theme: "slider",
            tile_enable_action: false,
            tile_enable_overlay: false,
            gallery_play_interval: 0,
            slider_enable_play_button: false,
            slider_enable_bullets: false,
            slider_enable_progress_indicator: false,
            lightbox_show_numbers: false,
            slider_enable_text_panel: true,
            slider_textpanel_enable_title: false,
            slider_control_zoom: false,
            gallery_min_width: 60,
            slider_textpanel_padding_top: 0,
            slider_textpanel_padding_bottom: 0,
            slider_transition: "fade",
            slider_transition_speed: 1000

        };
        if ($("#row-gallery-" + elementId).length > 0) {
            let option1 = {
                tiles_type: "justified",
                lightbox_show_numbers: false,
                tile_as_link: false,
                tile_enable_textpanel: true,
                gallery_min_width: 60
            };
            let viewType = $("#row-gallery-" + elementId).attr('data-image_view_type');
            let imageType = $("#row-gallery-" + elementId).children('img').attr('data-image-type');
            if (viewType == 'link' && imageType != 'VIDEO') {
                option1.tile_as_link = true;
                option1.tile_enable_textpanel = false;
                option1.tile_link_newpage = ($("#row-gallery-" + elementId).attr('target') == '_blank') ? true : false;
            }
            if (viewType == 'none' && imageType != 'VIDEO') {
                option1.tile_enable_textpanel = true;
                option1.tile_enable_overlay = true;
                option1.tile_enable_action = false;
                option1.tile_as_link = false;
                option1.tile_overlay_opacity = 0;
                option1.thumb_image_overlay_effect = false;
            }
            _this.unitgalleryCall("#row-gallery-" + elementId, option1);
        }
        if ($("#column-gallery-" + elementId).length > 0) {
            let option2 = {
                gallery_theme: "tiles",
                tiles_min_columns: 1,
                tiles_max_columns: 1,
                lightbox_show_numbers: false,
                tile_as_link: false,
                tile_enable_textpanel: true,
                gallery_min_width: 60

            };
            let viewType = $("#column-gallery-" + elementId).attr('data-image_view_type');
            let imageType = $("#column-gallery-" + elementId).children('img').attr('data-image-type');
            if (viewType == 'link' && imageType != 'VIDEO') {

                option2.tile_as_link = true;
                option2.tile_enable_textpanel = false;
                option2.tile_link_newpage = ($("#column-gallery-" + elementId).attr('target') == '_blank') ? true : false;
            }
            _this.unitgalleryCall("#column-gallery-" + elementId, option2);
       
        }

        if ($("#slider-gallery-" + elementId).length > 0) {
            let sliderTime = $("#slider-gallery-" + elementId).attr('data-slider-time');
            option3.gallery_play_interval = sliderTime * 1000;
            _this.unitgalleryCall("#slider-gallery-" + elementId, option3);
        }
    }
    /**
     * unite gallery text element settings and options
     */
    public textElementUniteGallerySettings(elementId: Number) {
        let _this = this;

        var singleImageOption = {
            tiles_min_columns: 1,
            tiles_max_columns: 1,
            lightbox_show_numbers: false,
            tile_enable_textpanel: true,
            gallery_min_width: 60
        };

        let sliderOption = {
            gallery_theme: "slider",
            tile_enable_action: false,
            tile_enable_overlay: false,
            slider_enable_play_button: false,
            slider_enable_bullets: false,
            slider_enable_progress_indicator: false,
            lightbox_show_numbers: false,
            slider_enable_text_panel: true,
            slider_textpanel_enable_title: false,
            slider_control_zoom: false,
            gallery_min_width: 60,
            slider_transition: "fade",
            slider_transition_speed: 1000,
            gallery_play_interval: 5000,
        }

        if ($("#row-gallery-" + elementId).length > 0) {
            _this.unitgalleryCall("#row-gallery-" + elementId, singleImageOption);
        }

        if ($("#gallery-textelement-" + elementId).length > 0) {
            sliderTime = $("#hidetextslider" + elementId).val();
            if(sliderTime!=0){
                 sliderOption.gallery_play_interval =  sliderTime * 1000;
                
            }
            _this.unitgalleryCall("#gallery-textelement-" + elementId, sliderOption);
        }



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
    /**
     * sponsor element options
     */
    public sponsorElementOptions(elementId: Number) {
        let sliderTime = $("#slider_" + elementId).attr('interval');
        $("#slider_" + elementId).FgFader({
            duration: sliderTime * 1000, //slider interval
        });
    }
    /**
     * unite gallery call
     */
    public unitgalleryCall(identifier: String, slideroptions: Object) {
        $(identifier).unitegallery(slideroptions);

    }

    public pageCallBackFunction() {
        //  ADD/EDIT CONTAINER POP UP
        // MAP GENERATING CODE
        this.mapGeneration();
        //IMAGE SLIDER
    }

    /**
     * Handle login and logout buttons click  with parameters as elementId & initialHtmlContent
     * @param   elementId          string html id of login element
     * @param   initialHtmlContent string initial Html Content
     */
    public handleLoginButtonsClick(elementId: string, initialHtmlContent: string) {
        //make remember checkbox uniform
        $('.uniform').uniform();

        let _this = this;
        //login button click
        $("body").on("click","#" + elementId +" .fg-dev-login-btn", function (e) {
            let thisObj = $(this);
            e.preventDefault();
            $.ajax({
                method: 'post',
                url: loginPath,
                data: thisObj.parents('form').serialize(),
                success: function(data, status, object) {
                    if (data.success) {
                        location.reload();
                    }
                    if (data.error) {
                        thisObj.parents('form').find('.fg-dev-alert-div').removeClass('hide').find('.fg-dev-alert-span').html(data.error);
                    }
                },
                error: function(data, status, object) {
                }
            });
        });
        //logout button click
        $("body").on("click","#" + elementId +" .fg-dev-logout-btn", function (e) {
            e.preventDefault(); 
            $.ajax({
                method: 'post',
                url: logoutPath,
                success: function(data, status, object) {
                    if (data.logout_success) {
                        location.reload();
                    }
                },
            });
        });

        //forgot password
        $("body").on("click","#" + elementId +" .fg-dev-forgot-password", function (e) {
            _this.renderForgotPasswordTemplate(elementId, initialHtmlContent, 'forgotPassword');
        });

        //activate account
        $("body").on("click","#" + elementId +" .fg-dev-activate-login", function (e) {
            _this.renderForgotPasswordTemplate(elementId, initialHtmlContent, 'activateLogin');
        });
    }

    /**
     * Function to render login password template / activate login template
     * @param string elementId
     * @param string initialHtmlContent
     * @param string templateName (forgotPassword/activateLogin)
     */
    public renderForgotPasswordTemplate(elementId: string, initialHtmlContent: string, templateName: string) {
        var htmlFinal = FGTemplate.bind('templateLoginForgotPassword', { 'elementId': elementId, 'templateName': templateName });
        $("#" + elementId).html(htmlFinal);
        var captchaContainer = null;
        var loadCaptcha = function() {
            captchaContainer = grecaptcha.render('fg-captcha' + elementId, {
                'sitekey': sitekey,
                'callback': function(response) {
                    $("#" + elementId).find('.fg-dev-activate-submit').removeAttr('disabled');
                }
            });
        };
        loadCaptcha(); // THIS LINE WAS MISSING
        this.handleForgotPasswordSubmit(elementId, initialHtmlContent);
        this.handleBackToLoginButton(elementId, initialHtmlContent);
    }

    /*
     * Handle handle BackToLoginButton
     */
    public handleBackToLoginButton(elementId: string, initialHtmlContent: string) {
        let _this = this;
        //back to login button
        $("#" + elementId).find('.fg-dev-back-button').on("click", function() {
            $("#" + elementId).html(initialHtmlContent);
            FgWebsiteThemeObj.makeFooterSticky()
            _this.handleLoginButtonsClick(elementId, initialHtmlContent);
        });
    }

    /*
     * Forgot password submit button
     */
    public handleForgotPasswordSubmit(elementId: string, initialHtmlContent: string) {
        let _this = this;
        //forgot password send button
        $("#" + elementId).find('.fg-dev-activate-submit').off('click');
        $("#" + elementId).find('.fg-dev-activate-submit').on("click", function(e) {
            let thisObj = $(this);
            thisObj.prop("disabled", true);
            e.preventDefault();
            $.ajax({
                method: 'post',
                url: sendEmailPath,
                data: $("#" + elementId).find('form').serialize(),
                success: function(data, status, object) {
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
                },
            });
        });
    }

    // Function to get the video details for website
    public getCmsVideoDetails(videoUrl: String, el: String) {
        var vDet = FgVideoThumbnail.getVideoId(videoUrl);
        var vType = (vDet.type == 'y') ? 'youtube' : ((vDet.type == 'v') ? 'vimeo' : '');
        $(el).attr('data-type', vType);
        $(el).attr('data-videoid', vDet.id);
    }
    /**
     * handle subscription form
     */
    public handleSubscriptionForm(elementId){
        $('#' + elementId + ' .bs-select').selectpicker();
        var subscriberCaptcha = function() {
            grecaptcha.render('fg-captcha' + elementId, {
                'sitekey': sitekeys,
                'callback': function(response) {
                    $("#subscription-form-" + elementId).find('.subscribeFormSubmit').removeAttr('disabled');
                }
            });
        };
        setTimeout(function() { subscriberCaptcha(); }, 1000);
        this.handleSubscriptionSubmit(elementId);
    }
    /**
     * submit subscription
     */
    public handleSubscriptionSubmit(elementId){
        let form = $("#subscription-form-" + elementId);
        form.on('click', '.subscribeFormSubmit', function() {
            let id = elementId.replace('elementbox-', '');
            let error = false;

            if (typeof $(this).attr('disabled') != 'undefined')
                return;

            form.find('.help-block').addClass('hide');
            $('div[dataerror-group]').removeClass('has-error');

            //validate email
            if ($('#email-' + id).val() == '') {
                error = true;
                $('#email-' + id + '-required-error').removeClass('hide');
                $('#email-' + id).parent().parent().addClass('has-error')
            } else {
                let email = $('#email-' + id).val();
                var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                if (!emailReg.test(email)) {
                    error = true;
                    $('#email-' + id + '-email-error').removeClass('hide');
                    $('#email-' + id).parent().parent().addClass('has-error')
                }
            }

            if ($('#language-' + id).val() == '') {
                error = true;
                $('#language-' + id + '-required-error').removeClass('hide');
                $('#language-' + id).parent().parent().addClass('has-error')
            }

            //sent request
            if (!error) {
                $(this).attr('disabled', 'disabled');
                let formData = {};
                formData['data'] = form.serializeArray();
                let postUrl = form.attr('action')

                $.ajax({
                    type: "POST",
                    url: postUrl,
                    data: formData,
                    success: function(data) {
                        $(this).removeAttr('disabled');
                        if (data.status == true) {
                            form.find('.alert-info').removeClass('hide').html(data.message);
                        } else {
                            form.find('.alert-danger').removeClass('hide').html(data.message);
                        }
                        form.trigger('reset');
                        form.find('.bs-select').selectpicker('render');
                        grecaptcha.reset();
                        setTimeout(function() { form.find('.alert').addClass('hide') }, 10000);
                        $('#email-' + id).focus();
                        form.find('.form-group.required').removeClass('has-error');
                    },
                    dataType: 'json'
                });
            }
        })
    }
    /**
     * gather all the og tags of a page
     */
    public gatherOGTagDetails(ogTagDetails){
        if (ogTagDetails.length > 0) {
            this.ogTags = this.ogTags.concat(ogTagDetails);
        }
    }
    /**
     * og tag
     */
    public checkAndSaveOGTags(){
        // will handle the pages where the tag update not needed eg: article detail page
        if (typeof ogTagUpdateUrl == 'string') {
            let currentOGTags = [];
            $('meta[property="og:image"]').each(function(index, metatag) {
                currentOGTags.push($(metatag).attr('imagename'));
            })
            currentOGTags = _.sortBy(_.uniq(currentOGTags));
            newOGTags = _.sortBy(_.uniq(this.ogTags));

            if (currentOGTags.length != newOGTags.length || (_.difference(currentOGTags, newOGTags).length > 0)) {
                $.ajax({
                    type: "POST",
                    url: ogTagUpdateUrl,
                    data: { 'ogTags': JSON.stringify($.extend({}, newOGTags)), 'pageId': mainPageId },
                    success: function() { },
                    dataType: 'json'
                });
            }
        }
    }
}