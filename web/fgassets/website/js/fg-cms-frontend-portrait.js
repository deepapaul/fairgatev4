var FgPortraitElem = (function () {
    function FgPortraitElem() {
        this.timer = 0;
        this.settings = "";
        this.currentPage = 1;
        this.defaultSettings = {
            boxId: '',
            elemId: '',
            filter: false,
            filterData: {},
            searchBox: false,
            portraitWrapperData: {},
            portraitContactsData: {},
            pagination: false,
            paginationOptions: {
                selector: '#fg-pag',
                options: {
                    items: 1,
                    itemsOnPage: 1,
                    pages: 0,
                    displayedPages: 3,
                    edges: 1,
                    currentPage: 0,
                    hrefTextPrefix: '#',
                    hrefTextSuffix: '',
                    prevText: '<i class="fa fa-angle-left"></i>',
                    nextText: '<i class="fa fa-angle-right"></i>',
                    labelMap: [],
                    ellipsePageSet: false,
                    cssStyle: "",
                    listStyle: 'pagination',
                    onPageClick: function (pageNumber, event) {
                    },
                    onInit: function () {
                    }
                }
            },
            initCompletedCallback: function ($object) { },
            filterCompletedCallback: function ($object) { },
            renderContactsCompletedCallback: function ($object) { },
            paginationCompletedCallback: function ($object) { },
        };
    }
    FgPortraitElem.prototype.initSettings = function (options) {
        this.settings = $.extend(true, {}, this.defaultSettings, options);
        this.renderContainer();
        this.settings.initCompletedCallback.call();
    };
    FgPortraitElem.prototype.initPreviewSettings = function (options) {
        this.settings = $.extend(true, {}, this.defaultSettings, options);
        this.renderPreview();
    };
    FgPortraitElem.prototype.renderPreview = function () {
        var portraitView = _.template(this.settings.portraitTemplate);
        var htmlFinal = portraitView({ 'contactsData': this.settings.portraitContactsData });
        $('#' + this.settings.boxId).html(htmlFinal);
        $('#' + this.settings.boxId).removeClass('hide');
    };
    FgPortraitElem.prototype.renderContainer = function (data) {
        var htmlFinal = FGTemplate.bind('templateContactPortraitElementFrontendContainer', { 'portraitId': this.settings.elemId });
        $('#' + this.settings.boxId).children('#' + this.settings.elemId).append(htmlFinal);
        if (this.settings.filter) {
            this.renderFilters();
        }
        if (this.settings.searchBox) {
            this.renderSearchbox();
        }
        if (this.settings.pagination) {
            this.renderPagination();
        }
        this.renderContacts();
    };
    FgPortraitElem.prototype.renderFilters = function (data) {
        var filterData = this.formatFilterData(this.settings.filterData);
        var $filterWrapper = $('#' + this.settings.boxId).children('#' + this.settings.elemId).find('.fg-filter-wrapper');
        var htmlFinal = FGTemplate.bind('templateContactPortraitElementFilter', { 'filterData': filterData });
        $filterWrapper.html(htmlFinal);
        $filterWrapper.find('select').selectpicker();
        this.handleFilter();
        this.settings.filterCompletedCallback.call();
    };
    FgPortraitElem.prototype.renderSearchbox = function (data) {
        var $searchWrapper = $('#' + this.settings.boxId).children('#' + this.settings.elemId).find('.fg-search-wrapper');
        var htmlFinal = FGTemplate.bind('templateContactPortraitElementSearch');
        $searchWrapper.html(htmlFinal);
        this.handleSearch();
    };
    FgPortraitElem.prototype.renderContacts = function (data) {
        var $contacts = this.settings.portraitContactsData;
        var $elemId = $contacts.elementId;
        var $templateId = "templateContactPortraitElement-" + $elemId;
        var $contentWrapper = $('#elementbox-' + $elemId).children('.fg-portrait-body');
        var htmlFinal = FGTemplate.bind($templateId, { 'contactsData': $contacts.portraitData });
        $contentWrapper.html(htmlFinal);
        this.settings.renderContactsCompletedCallback.call();
    };
    FgPortraitElem.prototype.renderPagination = function (data) {
        var $paginationOptons = $.extend(true, {}, this.settings.paginationOptions, data);
        var $elemId = this.settings.elemId;
        var $selector = $paginationOptons.selector;
        var itemsCount = this.settings.paginationOptions.options.items;
        var itemsOnPageCount = this.settings.paginationOptions.options.itemsOnPage;
        if ((itemsCount !== 0) && (itemsCount > itemsOnPageCount)) {
            var $paginationWrapper = $('#' + $elemId).children('.fg-portrait-footer').find('.fg-pagination').not('.has-pagination');
            var htmlFinal = FGTemplate.bind('templateContactPortraitElementPagination', { 'paginationData': $paginationOptons });
            $paginationWrapper.html(htmlFinal).addClass('has-pagination');
            $paginationWrapper.find($selector).pagination($paginationOptons.options);
        }
    };
    FgPortraitElem.prototype.formatFilterData = function (data) {
        var formattedData = [];
        var clubId = this.settings.clubDetails.clubId;
        var federationId = this.settings.clubDetails.federationId;
        var subFederationId = this.settings.clubDetails.subFederationId;
        $.each(data, function (i, values) {
            $.each(values, function (key, v) {
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
    };
    FgPortraitElem.prototype.handleFilter = function () {
        var _this = this;
        $(document).off('change', '#' + _this.settings.elemId + ' .fg-contact-portrait-filter-selectbox');
        $(document).on('change', '#' + _this.settings.elemId + ' .fg-contact-portrait-filter-selectbox', function () {
            _this.getContacts(true, $(this).closest('.fg-portrait-widget').attr('data-id'), 0);
        });
    };
    FgPortraitElem.prototype.handleSearch = function () {
        var _this = this;
        $('#' + _this.settings.elemId + ' .fg-contact-portrait-search-box').off("keyup");
        $('#' + _this.settings.elemId + ' .fg-contact-portrait-search-box').on("keyup", function () {
            var elementId = $(this).closest('.fg-portrait-widget').attr('data-id');
            _this.setDelay(function () { _this.getContacts(true, elementId, 0); }, 500);
        });
    };
    FgPortraitElem.prototype.getContacts = function (pageReInit, element, page) {
        var _this = this;
        var pageNumber = (typeof page !== 'undefined') ? page : 0;
        var elementId = (typeof element !== 'undefined') ? $('#' + element).attr('element-id') : $('#' + _this.settings.elemId).attr('element-id');
        var filterCriteria = _this.getFilterCriteria(element);
        var searchCriteria = _this.getSearchCriteria(element);
        $.post(_this.settings.dataUrl, { 'filterCriteria': filterCriteria, 'search': { 'value': searchCriteria }, 'elementId': elementId, 'pagenumber': pageNumber }, function (data) {
            _this.settings.portraitContactsData = data;
            _this.renderContacts();
            if (pageReInit) {
                var paginationObject = $('#' + element).children('.fg-portrait-footer').find('.fg-pagination').find('#fg-pagination-' + element);
                paginationObject.parent('.fg-pagination').removeClass('has-pagination');
                paginationObject.pagination('destroy');
                var itemsCount = parseInt(data.totalRecords);
                var itemsOnPageCount = (portraitElementSettings[elementId].data.portraitElement.portraitPerRow * portraitElementSettings[elementId].data.portraitElement.rowPerpage);
                if ((itemsCount !== 0) && (itemsCount > itemsOnPageCount)) {
                    paginationObject.pagination('updateItems', itemsCount);
                    paginationObject.pagination('updateItemsOnPage', itemsOnPageCount);
                    paginationObject.pagination('redraw');
                }
            }
        });
    };
    FgPortraitElem.prototype.getFilterCriteria = function (elementId) {
        var sFData = [];
        $('#' + elementId + ' select.fg-contact-portrait-filter-selectbox').each(function () {
            if ($(this).val() != '') {
                sFData.push({
                    type: $(this).attr('data-type'),
                    id: $(this).attr('data-id'),
                    value: $(this).val()
                });
            }
        });
        return sFData;
    };
    FgPortraitElem.prototype.getSearchCriteria = function (elementId) {
        return $.trim($('#' + elementId + ' .fg-contact-portrait-search-box').val());
    };
    FgPortraitElem.prototype.setDelay = function (callback, ms) {
        var _this = this;
        clearTimeout(_this.timer);
        _this.timer = setTimeout(callback, ms);
    };
    return FgPortraitElem;
}());
var FgPortraitElement = new FgPortraitElem();
