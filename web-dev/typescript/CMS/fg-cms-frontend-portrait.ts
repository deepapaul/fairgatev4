/*
 ================================================================================================ 
 * Wrapper for portrait element
 * Function - FgPortraitHeader - to configure portrait header section
 * Function - FgPortraitBody - to configure portrait body section
 * Function - FgPagination - to configure pagination properites
 ================================================================================================ 
 */
/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgPortraitElem {

    /*
     ================================================================================================ 
     *  Default settings 
     ================================================================================================ 
     */
    timer = 0;
    settings: Object = "";
    currentPage: number = 1;
    defaultSettings: Object = {
        boxId: '', // boxId / COntainer Id/ where the element going to append
        elemId: '', //Dynamic element id 
        filter: false, // enable or disable filter
        filterData: {},// filter iterating data
        searchBox: false, // enable or disable searchbox in portrait elements
        portraitWrapperData: {},
        portraitContactsData: {},
        pagination: false,
        paginationOptions: {
            selector: '#fg-pag', // default selector for pagination
            options: {
                items: 1,					//Total number of items that will be used to calculate the pages.
                itemsOnPage: 1, 				//Number of items displayed on each page.
                pages: 0, 				//If specified, items and itemsOnPage will not be used to calculate the number of pages.
                displayedPages: 3, 			//How many page numbers should be visible while navigating. Minimum allowed: 3 (previous, current & next)
                edges: 1, 					//How many page numbers are visible at the beginning/ending of the pagination.
                currentPage: 0,				//Which page will be selected immediately after init.,
                hrefTextPrefix: '#', 	//A string used to build the href attribute, added before the page number.
                hrefTextSuffix: '',			//Another string used to build the href attribute, added after the page number.
                prevText: '<i class="fa fa-angle-left"></i>',			//Text to be display on the previous button.
                nextText: '<i class="fa fa-angle-right"></i>',			//Text to be display on the next button.
                labelMap: [],    			//A collection of labels that will be used to render the pagination items, replacing the numbers. DEFAULT EMPTY ARRAY
                ellipsePageSet: false,		//When this option is true, clicking on the ellipse will replace the ellipse with a number type input in which you can manually set the resulting page.
                cssStyle: "",		//The class of the CSS theme.
                listStyle: 'pagination',
                onPageClick: function(pageNumber, event) {
                    // Callback triggered when a page is clicked
                    // Page number is given as an optional parameter

                },
                onInit: function() {
                    // Callback triggered immediately after initialization
                }
            }
        },
        initCompletedCallback: function($object) { }, //  function callback after init
        filterCompletedCallback: function($object) { }, //  function callback after init
        renderContactsCompletedCallback: function($object) { }, //  function callback after contacts rendered
        paginationCompletedCallback: function($object) { }, //  function callback after init

    }
    
    /*
     ================================================================================================ 
     *  @Initilise portrait element  
     ================================================================================================ 
     */
    public initSettings(options: Object) {
        this.settings = $.extend(true, {}, this.defaultSettings, options); //console.log(this.settings);
        this.renderContainer();


        this.settings.initCompletedCallback.call();
    }

    /*
     ================================================================================================ 
     *  @Initilise portrait element for create/edit portrait wizard stage 3
     ================================================================================================ 
     */
    public initPreviewSettings(options: Object) {
        this.settings = $.extend(true, {}, this.defaultSettings, options);
        this.renderPreview();
    }
    
    /*
     ================================================================================================ 
     *  @Render preview for create/edit portrait wizard stage 3
     ================================================================================================ 
     */
    public renderPreview() {
        var portraitView = _.template(this.settings.portraitTemplate);
        var htmlFinal = portraitView({'contactsData' : this.settings.portraitContactsData});
        $('#' + this.settings.boxId).html(htmlFinal); //append portrait wrapper to block
        $('#' + this.settings.boxId).removeClass('hide');
    }
    
    /*
     ================================================================================================ 
     *  @Render container html 
     *  @param 'data' optional parameter // currently not in use
     ================================================================================================ 
     */
    public renderContainer(data?: Object) {
        //  let _this: Object = data;
        let htmlFinal = FGTemplate.bind('templateContactPortraitElementFrontendContainer', { 'portraitId': this.settings.elemId });
        $('#' + this.settings.boxId).children('#' + this.settings.elemId).append(htmlFinal); //append portrait wrapper to block

        if (this.settings.filter) {
            this.renderFilters(); // render filter to block 
        }
        if (this.settings.searchBox) {
            this.renderSearchbox();// render searchbox to block
        }
        if (this.settings.pagination) {
            this.renderPagination();// render pagination to footer
        }
        this.renderContacts();
    }

    /*
     ================================================================================================ 
     *  @Render filtes
     *  @param 'data' optional parameter [Filter data object ] // currently not in use
     ================================================================================================ 
     */
    public renderFilters(data?: Object) {
        let filterData = this.formatFilterData(this.settings.filterData); 
        let $filterWrapper: any = $('#' + this.settings.boxId).children('#' + this.settings.elemId).find('.fg-filter-wrapper');
        let htmlFinal = FGTemplate.bind('templateContactPortraitElementFilter', { 'filterData': filterData }); 
        $filterWrapper.html(htmlFinal); //append portrait wrapper to block
        $filterWrapper.find('select').selectpicker();
        this.handleFilter();
        this.settings.filterCompletedCallback.call();

    }

    /*
    ================================================================================================ 
    *  @Render searchBox
    *  @param 'data' optional parameter 
    ================================================================================================ 
    */
    public renderSearchbox(data?: Object) {

        let $searchWrapper = $('#' + this.settings.boxId).children('#' + this.settings.elemId).find('.fg-search-wrapper');
        let htmlFinal = FGTemplate.bind('templateContactPortraitElementSearch');
        $searchWrapper.html(htmlFinal); //append portrait wrapper to block
        this.handleSearch();
    }

    /*
    ================================================================================================ 
    *  @Render Contacts
    *  @param 'data' optional parameter 
    ================================================================================================ 
    */
    public renderContacts(data?: Object) {
        let $contacts: any = this.settings.portraitContactsData;
        let $elemId: number = $contacts.elementId;
        let $templateId: string = "templateContactPortraitElement-" + $elemId;
        let $contentWrapper: any = $('#elementbox-' + $elemId).children('.fg-portrait-body'); // contentwraper- where element want to show
        let htmlFinal = FGTemplate.bind($templateId,{ 'contactsData':  $contacts.portraitData});
        $contentWrapper.html(htmlFinal); //append portrait wrapper to block
        this.settings.renderContactsCompletedCallback.call();
    }

    /*
    ================================================================================================ 
    *  @Render Pagination
    *  @param 'data' optional parameter 
    ================================================================================================ 
    */
    public renderPagination(data?: Object) {
        let $paginationOptons: Object = $.extend(true, {}, this.settings.paginationOptions, data);
        let $elemId: string = this.settings.elemId;
        let $selector = $paginationOptons.selector;
        let itemsCount = this.settings.paginationOptions.options.items;
        let itemsOnPageCount = this.settings.paginationOptions.options.itemsOnPage;
        if ((itemsCount !== 0) && (itemsCount > itemsOnPageCount)) {
            let $paginationWrapper: any = $('#' + $elemId).children('.fg-portrait-footer').find('.fg-pagination').not('.has-pagination');
            let htmlFinal = FGTemplate.bind('templateContactPortraitElementPagination', { 'paginationData': $paginationOptons });
            $paginationWrapper.html(htmlFinal).addClass('has-pagination'); //append portrait wrapper to block

            //Call thirdparty pagination plugin with options
            $paginationWrapper.find($selector).pagination($paginationOptons.options);
        }
        
    }

    /*
    ================================================================================================ 
    *  @Format the data apt for building filters
    *  @param 'data' filter data 
    ================================================================================================ 
    */
    public formatFilterData(data) {
        let formattedData = [];
        let clubId = this.settings.clubDetails.clubId;
        let federationId = this.settings.clubDetails.federationId;
        let subFederationId = this.settings.clubDetails.subFederationId;
        $.each(data, function(i, values) {
            $.each(values, function(key, v) {
                switch (v.type) {
                    case 'CF':
                    case 'TEAM':
                    case 'ROLES-' + clubId:
                    case 'FROLES-' + federationId:
                    case 'FROLES-' + subFederationId:
                    case 'WORKGROUP':
                        if (typeof v != 'undefined') {
                            formattedData.push(v);
                        }
                        break;
                    case 'FILTERROLES-' + clubId:
                        if (typeof v != 'undefined') {
                            formattedData.push(v);
                        }
                        break;
                    case 'CM':
                    case 'FM':
                        if (typeof v != 'undefined') {
                            v[0]['type'] = v.type;
                            v[0]['title'] = v.title;
                            formattedData.push(v[0]);
                        }
                        break;
                    default:
                        if (typeof v != 'undefined') {
                            formattedData.push(v);
                        }
                        break;
                }
            });
        });

        return formattedData;
    }

    /*
    ================================================================================================ 
    *  @Handle the filtering of the contacts result set
    ================================================================================================ 
    */
    public handleFilter() {
        let _this = this;
        $(document).off('change', '#' + _this.settings.elemId + ' .fg-contact-portrait-filter-selectbox');
        $(document).on('change', '#' + _this.settings.elemId + ' .fg-contact-portrait-filter-selectbox', function() {
            _this.getContacts(true, $(this).closest('.fg-portrait-widget').attr('data-id'), 0);
        });
    }
    
    /*
    ================================================================================================ 
    *  @Handle the search functionality in contacts result set
    ================================================================================================ 
    */
    public handleSearch() {
        let _this = this;
        $('#' + _this.settings.elemId + ' .fg-contact-portrait-search-box').off("keyup");
        $('#' + _this.settings.elemId + ' .fg-contact-portrait-search-box').on("keyup", function () {
            var elementId = $(this).closest('.fg-portrait-widget').attr('data-id');       
            _this.setDelay(function() { _this.getContacts(true, elementId, 0); }, 500);
        });
    }
    
    /*
    ================================================================================================ 
    *  @Get the contacts with current filter, search and pagination criteria
    ================================================================================================ 
    */
    public getContacts(pageReInit?: boolean, element?: string, page?: number) {
        let _this = this;
        var pageNumber = (typeof page !== 'undefined') ? page : 0;
        var elementId = (typeof element !== 'undefined') ? $('#' + element).attr('element-id') : $('#' + _this.settings.elemId).attr('element-id');
        let filterCriteria = _this.getFilterCriteria(element);
        let searchCriteria = _this.getSearchCriteria(element);       
        $.post(_this.settings.dataUrl, { 'filterCriteria': filterCriteria, 'search': {'value' : searchCriteria}, 'elementId': elementId, 'pagenumber': pageNumber}, function(data) {
            _this.settings.portraitContactsData = data;
            _this.renderContacts();
            if (pageReInit) {
                let paginationObject = $('#' + element).children('.fg-portrait-footer').find('.fg-pagination').find('#fg-pagination-'+element);
                paginationObject.parent('.fg-pagination').removeClass('has-pagination');
                paginationObject.pagination('destroy');
                let itemsCount = parseInt(data.totalRecords);
                let itemsOnPageCount = (portraitElementSettings[elementId].data.portraitElement.portraitPerRow * portraitElementSettings[elementId].data.portraitElement.rowPerpage);
                if ((itemsCount !== 0) && (itemsCount > itemsOnPageCount)) {
                    paginationObject.pagination('updateItems', itemsCount);
                    paginationObject.pagination('updateItemsOnPage', itemsOnPageCount);
                    paginationObject.pagination('redraw');
                }
            }
        });
    }
    
    /*
    ================================================================================================ 
    *  @Get the filter criteria of an element
    ================================================================================================ 
    */
    public getFilterCriteria(elementId) {
        let sFData = [];
        $('#' + elementId + ' select.fg-contact-portrait-filter-selectbox').each(function() {
            if ($(this).val() != '') {
                sFData.push({
                    type: $(this).attr('data-type'),
                    id: $(this).attr('data-id'),
                    value: $(this).val()
                });
            }
        });
        
        return sFData;
    }
    
    /*
    ================================================================================================ 
    *  @Get the search criteria of an element
    ================================================================================================ 
    */
    public getSearchCriteria(elementId) {
        return $.trim($('#' + elementId + ' .fg-contact-portrait-search-box').val());
    }
    
    /*
    ================================================================================================ 
    *  @Sets a delay before an action like search
    ================================================================================================ 
    */
    public setDelay(callback, ms) {
        let _this = this;
        clearTimeout(_this.timer);
        _this.timer = setTimeout(callback, ms);
    }
}

let FgPortraitElement = new FgPortraitElem();
