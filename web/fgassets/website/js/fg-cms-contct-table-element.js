var FgCmsContactTable = (function () {
    function FgCmsContactTable() {
        this.elementId = '';
        this.tableId = 'datatable-inquiry-list';
        this.tableHeaderTemplate = 'templateContactTableElementHeader';
        this.tableHeader = null;
        this.columnListUrl = '';
        this.listAjaxPath = '';
        this.tableHeaderData = {};
        this.tableRowData = {};
        this.searchTextBoxId = '';
        this.filterElementId = '';
        this.exportSearchBoxId = '';
        this.filterTemplateId = 'templateContactTableFilter';
        this.exportSearchTemplateId = 'templateContactTableExportSearch';
        this.wTable = {};
        this.filterData = {};
        this.columnData = {};
        this.tableInitialData = {};
        this.clubData = {};
        this.specialFilter = [];
        this.timer = 0;
    }
    FgCmsContactTable.prototype.drawTableHeader = function (callback) {
        var _this = this;
        var tableHeader = FGTemplate.bind(_this.tableHeaderTemplate, { tableColumns: this.columnData, 'searchTextBoxId': this.searchTextBoxId, settings: this.tableInitialData });
        $('#' + _this.tableId).html(tableHeader);
        if (this.tableInitialData['tableExport'] != '1') {
            $('#' + this.exportSearchBoxId).parent('.fg-contact-table-header').removeClass('fg-has-export');
        }
        if (!this.tableInitialData['tableSearch']) {
            $('#' + this.exportSearchBoxId).parent('.fg-contact-table-header').removeClass('fg-has-search');
        }
        callback();
    };
    FgCmsContactTable.prototype.getFilterElementId = function (filterElementId) {
        return this.filterElementId;
    };
    FgCmsContactTable.prototype.renderFilter = function () {
        var fData = this.formatFilterData(this.filterData);
        var dataHtml = FGTemplate.bind(this.filterTemplateId, { data: fData });
        $("#" + this.filterElementId).html(dataHtml);
        $("select").selectpicker();
    };
    FgCmsContactTable.prototype.renderExportAndSearch = function () {
        var dataHtml = FGTemplate.bind(this.exportSearchTemplateId, { data: this.tableInitialData, 'searchTextBoxId': this.searchTextBoxId, });
        $("#" + this.exportSearchBoxId).html(dataHtml);
    };
    FgCmsContactTable.prototype.formatFilterData = function (data) {
        var clubId = this.clubData['clubId'];
        var federationId = this.clubData['federationId'];
        var subFederationId = this.clubData['subFederationId'];
        var formattedData = [];
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
    FgCmsContactTable.prototype.drawContactTable = function () {
        this.handleFilter();
        var _this = this;
        this.drawTableHeader(function () {
            var datatableOptions = _this.getTableOptions();
            var dataTable = new FgWebsiteDatatable();
            _this.wTable = dataTable.initdatatable(_this.tableId, datatableOptions);
            _this.handleExport();
            dataTable.datatableSearch();
            if (_this.tableInitialData['overflowBehavior'] === 'toggle') {
                $('#' + _this.elementId).addClass('fg-responsive');
            }
        });
    };
    FgCmsContactTable.prototype.getTableOptions = function () {
        var self = this;
        return {
            columnDefFlag: true,
            stateSaveFlag: false,
            fixedcolumn: false,
            ajaxPath: this.listAjaxPath,
            columnDefValues: this.getColumndef(),
            serverSideprocess: false,
            displaylengthflag: true,
            displaylength: this.tableInitialData['rowPerpage'],
            initialSortingFlag: true,
            initialsortingColumn: (this.tableInitialData['overflowBehavior'] == 'toggle') ? '1' : '0',
            initialSortingorder: 'asc',
            ajaxparameterflag: true,
            scrollYflag: false,
            searchTextBox: this.searchTextBoxId,
            popupFlag: true,
            rowHighlight: this.tableInitialData['rowHighlighting'] ? true : false,
            hidePaginationOnSinglePageCount: true,
            tableDrawCallback: function () {
                if (self.tableInitialData['overflowBehavior'] === 'toggle') {
                    self.responsiveToggleClass();
                }
            },
            ajaxparameters: {
                tableField: this.tableInitialData['columnData'],
                filterdata: this.tableInitialData['filterData'],
                specialFilter: this.specialFilter,
                includedIds: this.tableInitialData['includedContacts'],
                excludedIds: this.tableInitialData['excludedContacts']
            },
            opt: {
                responsive: this.tableInitialData['overflowBehavior'] == 'toggle' ? {
                    details: {
                        type: 'column'
                    }
                } : false,
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: (typeof pageTitle != 'undefined') ? pageTitle + '_' + new Date().toLocaleString().slice(0, 9) + "_" + new Date(new Date()).toString().split(' ')[4] : 'export',
                        fieldSeparator: ',',
                        exportOptions: {
                            columns: this.tableInitialData['overflowBehavior'] == 'toggle' ? _.without(_.pluck(this.getColumndef(), 'targets'), 0) : ':visible',
                            orthogonal: 'export'
                        },
                        action: function (e, dt, button, config) {
                            config.fieldSeparator = ',';
                            if ($.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)) {
                                $.fn.dataTable.ext.buttons.csvHtml5.action(e, dt, button, config);
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: (typeof pageTitle != 'undefined') ? pageTitle + '_' + new Date().toLocaleString().slice(0, 9) + "_" + new Date(new Date()).toString().split(' ')[4] : 'export',
                        fieldSeparator: ';',
                        exportOptions: {
                            columns: this.tableInitialData['overflowBehavior'] == 'toggle' ? _.without(_.pluck(this.getColumndef(), 'targets'), 0) : ':visible',
                            orthogonal: 'export'
                        },
                        action: function (e, dt, button, config) {
                            config.fieldSeparator = ';';
                            if ($.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)) {
                                $.fn.dataTable.ext.buttons.csvHtml5.action(e, dt, button, config);
                            }
                        }
                    }
                ]
            }
        };
    };
    FgCmsContactTable.prototype.getColumndef = function () {
        var _this = this;
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var j = 0;
        var headerData = this.columnData;
        var ii = 0;
        if (_this.tableInitialData['overflowBehavior'] == 'toggle') {
            columnDefs.push({
                "name": "", "orderable": false, class: "all", "targets": ii++, data: function (row, type, val, meta) {
                    row.sortData = '';
                    row.displayData = '&nbsp;';
                    row.exportData = '';
                    return row;
                }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData', "export": "exportData" }
            });
        }
        $.each(headerData, function (i, col) {
            var k = (i == 0) ? 'all' : '';
            var sortMethod = (col.itemType == 'date' || col.itemType == 'number') ? 'null-numeric-last' : 'null-last';
            columnDefs.push({
                "name": "title" + ii, "targets": ii++, class: k, type: sortMethod, data: function (row, type, val, meta) {
                    row.displayData = _this.getDisplayData(row, col);
                    row.sortData = _this.getSortData(row, col);
                    row.exportData = _this.getExportData(row, col);
                    return row;
                }, render: { "_": 'sortData', "display": "displayData", "filter": "sortData", "export": "exportData" }
            });
        });
        return columnDefs;
    };
    FgCmsContactTable.prototype.getSortData = function (row, column) {
        var result = '';
        switch (column.itemType) {
            case 'date':
                result = this.toTimeStamp(row[column['name']], 'YYYY-MM-DD');
                break;
            case 'number':
                if (typeof row[column['name']] != 'undefined' && row[column['name']] != '')
                    result = parseFloat(row[column['name']]);
                else
                    result = '';
                break;
            default:
                result = !(_.isNull(row[column['name']])) ? row[column['name']].toLowerCase() : row[column['name']];
                break;
        }
        if (column.type == 'TA' || column.type == 'TF' || column.type == 'WA' || column.type == 'WF' || column.type == 'RCA' || column.type == 'FRCA' || column.type == 'SFRCA' || column.type == 'CRF' || column.type == 'FRA' || column.type == 'CFRF' || column.type == 'IRF' || column.type == 'IFRF' || column.type == 'ISFRF' || column.type == 'CSFRF') {
            result = row[column['name'] + '_sortorder'] == '' ? '' : this.getSortOrder(parseFloat(row[column['name'] + '_sortorder']));
        }
        return result;
    };
    FgCmsContactTable.prototype.getSortOrder = function (num) {
        var result = (Math.floor(num) + ((num % 1) / 1000));
        return result;
    };
    FgCmsContactTable.prototype.getDisplayData = function (row, column) {
        var result = '';
        if (column.type == 'CF' && column.is_set_privacy_itself == '0' && row[column['name'] + '_visibility'] == 'none') {
            return result;
        }
        switch (column.itemType) {
            case 'contactname':
                var iClass = '';
                if (row['Gender'] && row['Gender'].toLowerCase() == 'male') {
                    iClass = 'fa-male';
                }
                if (row['Gender'] && row['Gender'].toLowerCase() == 'female') {
                    iClass = 'fa-female';
                }
                if (row['Iscompany'] == '1') {
                    iClass = 'fa-building-o';
                }
                var icon = '<div class="fg-placeholder"><i class="fa ' + iClass + '"></i></div>';
                if (row[column['name'] + '_showProfilePicture'] == '1' && row['Gprofile_company_picExists'] && row['Iscompany'] != '1') {
                    var style = "style=\"background-image:url('" + row['Gprofile_company_pic'] + "')\"";
                    var contactImg = '<div class="fg-avatar fg-profile-img-blk35 fg-round-img"' + style + ' ></div>' + row[column['name']];
                }
                else {
                    var contactImg = column['showProfilePicture'] ? '<div class="fg-avatar">' + icon + '</div>' + row[column['name']] : row[column['name']];
                }
                if (column['linkUrl'] && row[column['name'] + '_linkUrl'] != '') {
                    result = '<a target="_blank" href="' + row[column['name'] + '_linkUrl'] + '">' + contactImg + '</a>';
                }
                else {
                    result = contactImg;
                }
                break;
            case 'email':
            case 'login email':
                var emailIcon = (iconSettingsType == 'website') ? 'fg-icon-envelope' : 'fa fa-envelope';
                result = row[column['name']] ? '<a href="mailto:' + row[column['name']] + '"><i class="' + emailIcon + '"></i></a>' : '';
                break;
            case 'url':
                var globeIcon = (iconSettingsType == 'website') ? 'fg-icon-globe' : 'fa fa-globe';
                result = row[column['name']] ? '<a href="' + row[column['name']] + '" target="_blank"><i class="' + globeIcon + '"></i></a>' : '';
                break;
            case 'date':
                result = moment(row[column['name']], 'YYYY-MM-DD').isValid() ? moment(row[column['name']], 'YYYY-MM-DD').format(FgLocaleSettingsData.momentDateFormat) : '';
                break;
            case 'number':
                result = ((column.id == 'club_member_years') || (column.id == 'fed_member_years')) ? FgClubSettings.formatNumber(row[column['name']], '', 1) : FgClubSettings.formatNumber(row[column['name']]);
                break;
            case 'fileupload':
                var downloadPath = fileDownloadPath.replace('**source**', 'contactfield_file');
                downloadPath = downloadPath.replace('**name**', row[column['name']]);
                downloadPath = downloadPath.replace('**clubId**', column['club_id']);
                result = row[column['name']] ? '<a href="' + downloadPath + '" target="_blank">' + this.getFileIcon(row[column['name']]) + '</a>' : '';
                break;
            case 'imageupload':
                if (row[column['name']]) {
                    var path = uploadPath.replace('**clubId**', column['club_id']);
                    var imgLink = "<img src='" + path + "contact/contactfield_image/" + row[column['name']] + "'>";
                    var link = path + "contact/contactfield_image/" + row[column['name']];
                    var pictureIcon = (iconSettingsType == 'website') ? 'fg-icon-picture-o' : 'fa fa-picture-o';
                    result = row[column['name']] ? '<a href="' + link + '" target="_blank"><i class="fg-custom-popovers ' + pictureIcon + '" data-trigger="hover" data-placement="bottom" data-content="' + imgLink + '" data-original-title=""></i></a>' : '';
                }
                break;
            case 'multiline':
                result = this.nl2br(row[column['name']], true);
                break;
            case 'select':
                if (column['id'] == this.clubData['corresLangField']) {
                    result = (row[column['name']] && typeof this.clubData['langList'][row[column['name']]] != 'undefined') ? this.clubData['langList'][row[column['name']]] : '';
                }
                else if (this.clubData['countryFields'].indexOf(parseInt(column['id'])) >= 0) {
                    result = (row[column['name']] && typeof this.clubData['countryList'][row[column['name']]] != 'undefined') ? this.clubData['countryList'][row[column['name']]] : '';
                }
                else {
                    result = row[column['name']] ? row[column['name']] : '';
                }
                break;
            case 'checkbox':
                result = row[column['name']] ? row[column['name']].replace(/;/g, ', ') : '';
                break;
            default:
                if (column.type == 'FI') {
                    var starIconClass = (iconSettingsType == 'website') ? 'fg-icon-star' : 'fa fa-star';
                    var starIcon = ' <i class="' + starIconClass + '"></i>';
                    result = row[column['name']] ? ((row[column['name']]).split(',').length > 1
                        ? row[column['name']].replace('#mainclub#', starIcon)
                        : row[column['name']].replace('#mainclub#', ''))
                        : '';
                }
                else {
                    result = row[column['name']] ? row[column['name']] : '';
                }
                break;
        }
        return result;
    };
    FgCmsContactTable.prototype.getExportData = function (row, column) {
        var result = '';
        if (column.type == 'CF' && column.is_set_privacy_itself == '0' && row[column['name'] + '_visibility'] == 'none') {
            return result;
        }
        switch (column.itemType) {
            case 'date':
                result = moment(row[column['name']], 'YYYY-MM-DD').isValid() ? moment(row[column['name']], 'YYYY-MM-DD').format(FgLocaleSettingsData.momentDateFormat) : '';
                break;
            case 'select':
                if (column['id'] == this.clubData['corresLangField']) {
                    result = (row[column['name']] && typeof this.clubData['langList'][row[column['name']]] != 'undefined') ? this.clubData['langList'][row[column['name']]] : '';
                }
                else if (this.clubData['countryFields'].indexOf(parseInt(column['id'])) >= 0) {
                    result = (row[column['name']] && typeof this.clubData['countryList'][row[column['name']]] != 'undefined') ? this.clubData['countryList'][row[column['name']]] : '';
                }
                else {
                    result = row[column['name']] ? row[column['name']] : '';
                }
                break;
            case 'number':
                result = ((column.id == 'club_member_years') || (column.id == 'fed_member_years')) ? FgClubSettings.formatNumber(row[column['name']], '', 1) : FgClubSettings.formatNumber(row[column['name']]);
                break;
            case 'multiline':
                result = this.nl2br(row[column['name']], true);
                break;
            case 'checkbox':
                result = row[column['name']] ? row[column['name']].replace(/;/g, ', ') : '';
                break;
            default:
                if (column.type == 'FI') {
                    result = row[column['name']].replace('#mainclub#', '');
                }
                else {
                    result = row[column['name']] ? row[column['name']] : '';
                }
                break;
        }
        return result;
    };
    FgCmsContactTable.prototype.toTimeStamp = function (date, currentFormat) {
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
    FgCmsContactTable.prototype.handleExport = function () {
        var _this = this;
        $('#' + _this.exportSearchBoxId).off('click', '.fg-contact-export a');
        $('#' + _this.exportSearchBoxId).on('click', '.fg-contact-export a', function () {
            if ($(this).children('input').val() == 'commaSep') {
                _this.wTable.button('0').trigger();
            }
            else {
                _this.wTable.button('1').trigger();
            }
        });
    };
    FgCmsContactTable.prototype.setSpecialFilter = function (specialFilter) {
        this.specialFilter = specialFilter;
    };
    FgCmsContactTable.prototype.getSpecialFilter = function () {
        return this.specialFilter;
    };
    FgCmsContactTable.prototype.handleFilter = function () {
        var _this = this;
        $(document).off('change', '#' + _this.elementId + ' .fg-contact-table-filter-selectbox');
        $(document).on('change', '#' + _this.elementId + ' .fg-contact-table-filter-selectbox', function () {
            var sFData = [];
            var filterElemId = _this.getFilterElementId();
            $('#' + filterElemId + ' select.fg-contact-table-filter-selectbox').each(function () {
                if ($(this).val() != '') {
                    sFData.push({
                        type: $(this).attr('data-type'),
                        id: $(this).attr('data-id'),
                        value: $(this).val()
                    });
                }
            });
            _this.setDelay(function () {
                $('#' + _this.elementId + ' .fg-contact-table-search-box').val('');
                _this.setSpecialFilter(sFData);
                _this.drawContactTable();
            }, 500);
        });
    };
    FgCmsContactTable.prototype.getFileTypeArray = function () {
        var fileTypes = {};
        fileTypes.docTypes = ['doc', 'docx', 'odt'];
        fileTypes.pdfTypes = ['pdf'];
        fileTypes.excelTypes = ['xls', 'xlsx'];
        fileTypes.powerType = ['ppt', 'pptx'];
        fileTypes.archiveType = ['zip', 'rar', 'tar', 'gz', '7z'];
        fileTypes.audioType = ['mp3', 'aac', 'amr', 'm4a', 'm4p', 'wma'];
        fileTypes.videoType = ['mp4', 'flv', 'mkv', 'avi', 'webm', 'vob', 'mov', 'wmv', 'm4v'];
        fileTypes.webTypes = ['html', 'htm'];
        fileTypes.textTypes = ['txt', 'rtf', 'log'];
        fileTypes.imgTypes = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'bmp'];
        return fileTypes;
    };
    FgCmsContactTable.prototype.getFileIcon = function (fileName) {
        var ext = fileName.toString().split('.').pop().toLowerCase();
        var filetypes = this.getFileTypeArray();
        if (filetypes.docTypes.indexOf(ext) > -1) {
            var fileWordIcon = (iconSettingsType == 'website') ? 'fg-icon-file-word-o' : 'fa fa-file-word-o';
            return '<i class="' + fileWordIcon + ' fg-datatable-icon"></i>';
        }
        else if (filetypes.pdfTypes.indexOf(ext) > -1) {
            var filePdfIcon = (iconSettingsType == 'website') ? 'fg-icon-file-pdf-o' : 'fa fa-file-pdf-o';
            return "<i class='" + filePdfIcon + " fg-datatable-icon'></i>";
        }
        else if (filetypes.excelTypes.indexOf(ext) > -1) {
            var fileExcelIcon = (iconSettingsType == 'website') ? 'fg-icon-file-excel-o' : 'fa fa-file-excel-o';
            return "<i class='" + fileExcelIcon + " fg-datatable-icon'></i>";
        }
        else if (filetypes.powerType.indexOf(ext) > -1) {
            var filePowerpointIcon = (iconSettingsType == 'website') ? 'fg-icon-file-powerpoint-o' : 'fa fa-file-powerpoint-o';
            return "<i class='" + filePowerpointIcon + " fg-datatable-icon'></i>";
        }
        else if (filetypes.archiveType.indexOf(ext) > -1) {
            var fileZipIcon = (iconSettingsType == 'website') ? 'fg-icon-file-zip-o' : 'fa-file-zip-o';
            return "<i class='" + fileZipIcon + " fg-datatable-icon'></i>";
        }
        else if (filetypes.audioType.indexOf(ext) > -1) {
            var fileSoundIcon = (iconSettingsType == 'website') ? 'fg-icon-file-sound-o' : 'fa fa-file-sound-o';
            return "<i class='" + fileSoundIcon + " fg-datatable-icon'></i>";
        }
        else if (filetypes.videoType.indexOf(ext) > -1) {
            var fileVideoIcon = (iconSettingsType == 'website') ? 'fg-icon-file-video-o' : 'fa fa-file-video-o';
            return "<i class='" + fileVideoIcon + " fg-datatable-icon'></i>";
        }
        else if (filetypes.webTypes.indexOf(ext) > -1) {
            var fileCodeIcon = (iconSettingsType == 'website') ? 'fg-icon-file-code' : 'fa fa-file-code';
            return '<i class="' + fileCodeIcon + ' fg-datatable-icon"></i>';
        }
        else if (filetypes.textTypes.indexOf(ext) > -1) {
            var fileTextIcon = (iconSettingsType == 'website') ? 'fg-icon-file-text' : 'fa fa-file-text';
            return '<i class="' + fileTextIcon + ' fg-datatable-icon"></i>';
        }
        else if (filetypes.imgTypes.indexOf(ext) > -1) {
            var filePhotoIcon = (iconSettingsType == 'website') ? 'fg-icon-file-photo' : 'fa fa-photo';
            return '<i class="' + filePhotoIcon + ' fg-datatable-icon"></i>';
        }
        else {
            var fileFileIcon = (iconSettingsType == 'website') ? 'fg-icon-file' : 'fa fa-file';
            return '<i class="' + fileFileIcon + ' fg-datatable-icon"></i>';
        }
    };
    FgCmsContactTable.prototype.nl2br = function (str, is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    };
    FgCmsContactTable.prototype.setDelay = function (callback, ms) {
        clearTimeout(this.timer);
        this.timer = setTimeout(callback, ms);
    };
    FgCmsContactTable.prototype.responsiveToggleClass = function () {
        var _this = this;
        if (_this.wTable.columns().nodes().length - _this.wTable.columns(':visible').nodes().length > 0 && $('#' + _this.tableId).hasClass('collapsed')) {
            $('#' + _this.elementId).addClass('fg-datatable-collapsed');
        }
        _this.wTable.on('responsive-resize', function (e, datatable, columns) {
            var count = columns.reduce(function (a, b) { return b === false ? a + 1 : a; }, 0);
            if (count > 0) {
                $('#' + _this.elementId).addClass('fg-datatable-collapsed');
            }
            else {
                $('#' + _this.elementId).removeClass('fg-datatable-collapsed');
            }
        });
    };
    return FgCmsContactTable;
}());
