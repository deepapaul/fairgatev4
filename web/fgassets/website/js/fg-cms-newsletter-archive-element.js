var FgCmsNewsletterArchive = (function () {
    function FgCmsNewsletterArchive() {
        this.elementId = '';
        this.tableId = '';
        this.tableHeaderTemplate = 'templateNewsletterArchiveElementHeader';
        this.tableHeader = null;
        this.columnListUrl = '';
        this.listAjaxPath = '';
        this.columnData = {};
        this.widthValue = '';
    }
    FgCmsNewsletterArchive.prototype.drawTableHeader = function (callback) {
        var _this = this;
        var tableHeader = FGTemplate.bind(_this.tableHeaderTemplate, { tableColumns: this.columnData });
        $('#' + _this.tableId).html(tableHeader);
        callback();
    };
    FgCmsNewsletterArchive.prototype.drawNewsletterArchiveTable = function () {
        var _this = this;
        this.drawTableHeader(function () {
            var datatableOptions = _this.getTableOptions();
            var dataTable = new FgWebsiteDatatable();
            _this.wTable = dataTable.initdatatable(_this.tableId, datatableOptions);
        });
    };
    FgCmsNewsletterArchive.prototype.getTableOptions = function () {
        optArray = {};
        if (this.widthValue == 1) {
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
            ajaxparameters: {},
            opt: optArray
        };
    };
    FgCmsNewsletterArchive.prototype.getColumndef = function () {
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
            "name": "title1", "targets": 1, class: '', type: 'null-numeric-last', data: function (row, type, val, meta) {
                row.displayData = moment(row['date']['date'], 'YYYY-MM-DD').isValid() ? moment(row['date']['date'], 'YYYY-MM-DD').format(FgLocaleSettingsData.momentDateFormat) : '';
                row.sortData = _this.toTimeStamp(row['date']['date'], 'YYYY-MM-DD');
                return row;
            }, render: { "_": 'sortData', "display": "displayData", "filter": "sortData" }
        });
        columnDefs.push({
            "name": "title2", "targets": 2, class: '', type: 'null-last', data: function (row, type, val, meta) {
                row.displayData = row['title'];
                previewpath = newsletterPreview.replace("dummynewsletter", row['id']);
                row.displayData = '<a target="_blank" href="' + previewpath + '">' + row['title'] + '</a>';
                return row;
            }, render: { "_": 'sortData', "display": "displayData", "filter": "sortData" }
        });
        return columnDefs;
    };
    FgCmsNewsletterArchive.prototype.toTimeStamp = function (date, currentFormat) {
        var timestamp = null;
        if (date != '' && date != null && date != '0000-00-00') {
            var momentObj = moment(date, currentFormat);
            if (momentObj.isValid()) {
                timestamp = momentObj.format('x');
            }
        }
        return timestamp;
    };
    ;
    FgCmsNewsletterArchive.prototype.nl2br = function (str, is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    };
    FgCmsNewsletterArchive.prototype.setDelay = function (callback, ms) {
        clearTimeout(this.timer);
        this.timer = setTimeout(callback, ms);
    };
    return FgCmsNewsletterArchive;
}());
