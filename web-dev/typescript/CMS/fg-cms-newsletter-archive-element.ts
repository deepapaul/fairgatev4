/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgCmsNewsletterArchive {

    elementId: String = '';
    tableId: String = '';
    tableHeaderTemplate: String = 'templateNewsletterArchiveElementHeader';
    tableHeader: Object = null;
    columnListUrl: String = '';
    listAjaxPath: String = '';
    columnData: Object = {};
    widthValue = '';
    constructor() {

    }
    public drawTableHeader(callback) {
        let _this = this;
        var tableHeader = FGTemplate.bind(_this.tableHeaderTemplate, { tableColumns: this.columnData });
        $('#' + _this.tableId).html(tableHeader);

        callback();
    }

    public drawNewsletterArchiveTable() {
        var _this = this;
        this.drawTableHeader(function() {
            let datatableOptions = _this.getTableOptions();
            let dataTable = new FgWebsiteDatatable();
            _this.wTable = dataTable.initdatatable(_this.tableId, datatableOptions);
        });
    }

    public getTableOptions() {
        optArray = {};
        if (this.widthValue == 1) 
        {
            optArray = {
                            responsive: {
                                details: {
                                    display: $.fn.dataTable.Responsive.display.childRowImmediate,
                                    type: ''
                                }
                            },
                            responsiveToggle: false
                        };
        }
        return {
            columnDefFlag: true,
            ajaxPath: this.listAjaxPath,
            columnDefValues: this.getColumndef(),
            fixedcolumn: false,
            serverSideprocess: false,
            displaylengthflag: true,
            displaylength: 10,
            hidePagination: true,
            initialSortingFlag: false,
            initialSortingorder: 'asc',
            ajaxparameterflag: true,
            scrollYflag: false,
            stateSaveFlag: false,
            ajaxparameters: {
            },
            opt: optArray
        };
    }

    public getColumndef() {
        var _this = this;
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var headerData = this.columnData;
        columnDefs.push({
            "name": "title3", "targets": 0, "visible": false, class: '', type: '', data: function (row, type, val, meta) {
                return row;
            }
        });
        columnDefs.push({
            "name": "title1", "targets": 1, class: '', type: 'null-numeric-last', data: function(row, type, val, meta) {
                row.displayData = moment(row['date']['date'], 'YYYY-MM-DD').isValid() ? moment(row['date']['date'], 'YYYY-MM-DD').format(FgLocaleSettingsData.momentDateFormat) : '';
                row.sortData = _this.toTimeStamp(row['date']['date'], 'YYYY-MM-DD');
                return row;
            }, render: { "_": 'sortData', "display": "displayData", "filter": "sortData" }
        });
        columnDefs.push({
            "name": "title2", "targets": 2, class: '', type: 'null-last', data: function(row, type, val, meta) {
                row.displayData = row['title'];
                previewpath = newsletterPreview.replace("dummynewsletter", row['id']);
                row.displayData = '<a target="_blank" href="'+previewpath+'">'+row['title']+'</a>';
                return row;
            }, render: { "_": 'sortData', "display": "displayData", "filter": "sortData" }
        });

        return columnDefs;
    }

    public toTimeStamp(date, currentFormat) {
        var timestamp = null;
        if (date != '' && date != null && date != '0000-00-00') {
            var momentObj = moment(date, currentFormat);
            if (momentObj.isValid()) {
                timestamp = momentObj.format('x');
            }
        }
        return timestamp;
    };

    public nl2br(str, is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    }

    public setDelay(callback, ms) {

        clearTimeout(this.timer);
        this.timer = setTimeout(callback, ms);
    }

}

