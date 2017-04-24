/// <reference path="jquery.d.ts" />
/// <reference path="underscore.d.ts" />
/// <reference path="jqueryui.d.ts" />
class Fgcmspage {
    settings: any = '';
    sortSetting: any = '';
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
                'text': 'templateText'
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
        tolerance: "pointer",
        start: function(event, ui) { console.log('start', ui.item.attr('id')) },
        stop: function(event, ui) { console.log('stop', ui.item.attr('id')) }

    }
    constructor(public options: any) {

    }
    public initSettings() {
        this.settings = $.extend(true, {}, this.defaultSettings, this.options);
    }

    public renderContainerBox(containerId: any) {
        if (_.size(this.settings.container.data) > 0) {
            //render all columns of particular container
            let columnContent = this.containerColumns();
            console.log(this.settings.data.page.id)
            return FGTemplate.bind(this.settings.container.templateId, { details: this.settings.container.data, containerid: containerId, columnDetails: columnContent,pageId:this.settings.data.page.id });
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
    public renderElement(elementId: any) {
        return FGTemplate.bind(this.settings.elementbox.templateId[this.settings.elementbox.data.elementType], { details: this.settings.elementbox.data, elementid: elementId });
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
        _.each(containerDetails, function(containerValues: any, index) {
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
        _.each(this.settings.container.data.columns, function(columnValues: any, index) {
            //create column box id
            columnId = 'containercolumn-' + columnValues.columnId;
            //create columns
            _this.settings.column.data = columnValues;
            //console.log('column',settings.column.data)
            columnHtml += _this.renderColumnBox(columnId);

        });
        this.settings.initColumnCallback.call();
        return columnHtml;
    }

    public columnBox() {
        let boxHtml = '';
        let _this = this;
        let boxId = '';
        _.each(this.settings.column.data.box, function(boxValues: any, index) {
            //create box id
            boxId = 'columnbox-' + boxValues.boxId;
            //create box
            _this.settings.columnbox.data = boxValues;
            // console.log(settings.columnbox.data)
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
        _.each(this.settings.columnbox.data.Element, function(elementValues: any, index) {
            elementId = 'elementbox-' + elementValues.elementId;
            _this.settings.elementbox.data = elementValues;
            elementHtml += _this.renderElement(elementId);
        });

        this.settings.initElementBoxCallback.call();
        return elementHtml;

    }
    public sidebarInit() {
        let pageHtml = this.pageContainer();
        return pageHtml;
    }
    public contentInit() {
        this.initSettings();
        let pageHtml = this.pageContainer();
        return pageHtml;
    }

    public pageInit() {
        this.initSettings();
        this.settings.containerType = 'sidebar';
        let sidebarHtmlContent = this.sidebarInit();
        this.settings.containerType = 'content';
        let contentHtml = this.contentInit();
        let pageContent = this.renderPage({ 'side': this.settings.sidebarSide, 'size': this.settings.sidebarSize, 'type': this.settings.sidebarType, 'sidebarData': sidebarHtmlContent, 'contentData': contentHtml });
        $(this.settings.mainContainer).html(pageContent);
        this.settings.pageInitCallback.call();
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
        } else if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'narrow') {
            totalWidth = 1;
        } else if (this.settings.data.sidebar.size == 'wide') {
            totalWidth = 4;
        } else if (this.settings.data.sidebar.size == 'narrow') {
            totalWidth = 5;
        } 
        let calculatedWidth = 0;
        _.each(currentContainer.find('.rowColumn'), function(value, key) {
            calculatedWidth=calculatedWidth+parseInt($(value).attr('column-size'));
            if(parseInt($(value).attr('column-size')) > 1) {
               //decrease button 
            }
        })
        if(calculatedWidth < totalWidth) {
            //set increase button to all column
        }

    }
    public createContainer(pageId: any, containerId:any) {
        let totalColumnCount =this.getMaxColumnCount();
        
        console.log(totalColumnCount);
        //FGTemplate.bind('createContainer', { totalCount: totalColumnCount, currentColumnCount: 1,containerId:containerId });

    }
    
    public hideRemoveButton() {
        if (this.settings.containerType == 'content' && _.size(this.settings.data.page.container)<=1) {
          $(".contentRow .fa-times-circle").hide();
        } else if(this.settings.containerType == 'sidebar' && _.size(this.settings.data.sidebar.container)<=1) {
           $(".contentRow .fa-times-circle").hide(); 
        }
        console.log('fgf');
       
    }
    
    public getMaxColumnCount() {
        let totalColumnCount = 6;
        if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'wide') {
            totalColumnCount = 2;
        } else if (this.settings.containerType == 'sidebar' && this.settings.data.sidebar.size == 'narrow') {
            totalColumnCount = 1;
        } else if (this.settings.data.sidebar.size == 'wide') {
            totalColumnCount = 4;
        } else if (this.settings.data.sidebar.size == 'narrow') {
            totalColumnCount = 5;
        }
        
        return  totalColumnCount;
    }

    

}



