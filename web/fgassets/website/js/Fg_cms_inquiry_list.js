var _this;
var FGTemplate = FGTemplate;
var FgLocaleSettingsData = FgLocaleSettingsData;
var FgXmlHttp = FgXmlHttp;
var FgDatatable = FgDatatable;
var datatableOptions;
var inquiryListTable;
var FgCmsInquiryList = (function () {
    function FgCmsInquiryList() {
        this.tableId = 'datatable-inquiry-list';
        this.target = 'listing';
        this.isAllFormInquiries = false;
        this.inquiryListAjaxPath = '';
        this.inquiryListAllAjaxPath = '';
        this.formElementId = '';
        this.formTitle = '';
        this.tableData = {};
        this.tableColumns = {};
        this.pageTitlebarOpt = {};
        this.contactProfilePath = '';
        this.inquiryDeletePath = '';
        this.editFormPath = '';
        this.defaultPageTitle = '';
        this.inquityDetailsPopupPath = '';
        this.hasEditFormBtn = false;
        this.csvExportSelector = '.fg-inquiry-export-csv';
        this.exportAttachmantpath = '';
        this.getSidebarDataPath = '';
        this.formFileUploadDir = '';
        this.hasAttatchments = false;
        this.attachmentDownloadPath = '';
        this.fieldSeparator = ',';
        _this = this;
        this.handleClickEvent();
    }
    FgCmsInquiryList.prototype.init = function () {
        this.initActionMenu();
        if (!this.isAllFormInquiries) {
            FgInternal.pageLoaderOverlayStart('page-container');
            $.post(this.inquiryListAjaxPath.replace("placeholder", this.formElementId), {}, function (data) {
                var pageTitleOpt = { hasEditFormBtn: (data.isActiveForm == '1') ? true : false };
                _this.initPageTitleBar(pageTitleOpt);
                _this.setTableColumns(data.formFields);
                _this.setFormTitle(data.formTitle);
                _this.setHasAttatchments(data.hasAttatchments);
                if (data.hasAttatchments == '0') {
                    _this.removeExportAttachmentsFromActionMenu();
                }
                _this.dataTableInit();
                FgInternal.pageLoaderOverlayStop('page-container');
            }, false);
        }
        else {
            this.initPageTitleBar({});
            this.dataTableInit();
        }
    };
    FgCmsInquiryList.prototype.setTableColumns = function (columns) {
        this.tableColumns = columns;
    };
    FgCmsInquiryList.prototype.setFormTitle = function (title) {
        this.formTitle = title;
    };
    FgCmsInquiryList.prototype.setHasAttatchments = function (data) {
        this.hasAttatchments = (data == '0') ? false : true;
    };
    FgCmsInquiryList.prototype.getTableData = function () {
        return this.tableData;
    };
    FgCmsInquiryList.prototype.getFormElementId = function () {
        return this.formElementId;
    };
    FgCmsInquiryList.prototype.getTableColumnsForExport = function () {
        var result = [];
        var j = 1;
        result.push(j);
        $.each(this.tableColumns, function () {
            j++;
            result.push(j);
        });
        return result;
    };
    FgCmsInquiryList.prototype.setFieldSeparator = function (fieldSeparator) {
        this.fieldSeparator = fieldSeparator;
    };
    FgCmsInquiryList.prototype.getFieldSeparator = function () {
        return this.fieldSeparator;
    };
    FgCmsInquiryList.prototype.renderFormInquiryList = function (formId) {
        if (formId) {
            this.formElementId = formId;
            this.isAllFormInquiries = false;
        }
        else {
            this.isAllFormInquiries = true;
        }
        if (typeof listTable !== 'undefined') {
            listTable.destroy();
        }
        $('#' + this.tableId).empty();
        this.init();
    };
    FgCmsInquiryList.prototype.dataTableInit = function () {
        var tableHeader = FGTemplate.bind('inquiryListHeaderTemplate', { isAllFormInquiries: this.isAllFormInquiries, tableColumns: this.tableColumns });
        $('#' + this.tableId).html(tableHeader);
        this.dataTableOpt();
        inquiryListTable = FgDatatable.listdataTableInit(this.tableId, datatableOptions);
    };
    FgCmsInquiryList.prototype.dataTableOpt = function () {
        var i = 0;
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        if (this.isAllFormInquiries) {
            columnDefs.push({
                "name": "createdAt", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                    row.sortData = row.sortData = parseInt(_this.toTimeStamp(row['createdAt'], currentDateFormat));
                    row.displayData = row['createdAt'];
                    return row;
                }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' }
            });
            columnDefs.push({
                "name": "formTitle", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                    row.sortData = row['formTitle'];
                    row.displayData = '<a href="#' + row['elementId'] + '" class="fg-dev-form-inquiry-link" data-elementId="' + row['elementId'] + '">' + _.escape(row['formTitle']) + '</a>';
                    return row;
                }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' }
            });
            columnDefs.push({
                "name": "request", "orderable": false, width: '20%', "targets": i++, data: function (row, type, val, meta) {
                    return '<a href="#" class="fg-dev-inquiry-popup-link" data-fiId="' + row['fiId'] + '" data-createdAt="' + row['createdAt'] + '"><i class="fa fa-commenting-o"><i></a>';
                }
            });
            columnDefs.push({
                "name": "user", "targets": i++, data: function (row, type, val, meta) {
                    var profileLink = (this.profilePath).replace("placeholder", row['activeContactId']);
                    row.sortData = row['contactName'];
                    row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['contactName'] + '</a></div>' : '<span class="fg-table-reply">' + row['contactName'] + '</span>';
                    return row;
                }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' }
            });
        }
        else {
            columnDefs.push({
                type: "checkbox", width: '1%', orderable: false, sortable: false, className: 'fg-checkbox-th', targets: i++, data: function (row, type, val, meta) {
                    var content = '<input class="dataClass fg-dev-avoidicon-behaviour fg-dev-inquiry-list-checkbox" type="checkbox" id=' + row['fiId'] + ' name="check" value="0">';
                    row.sortData = '';
                    row.displayData = content;
                    return row;
                }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' }
            });
            columnDefs.push({
                "name": "createdAt", "type": "moment-" + currentDateFormat, width: '20%', "targets": i++, data: function (row, type, val, meta) {
                    row.sortData = parseInt(_this.toTimeStamp(row['createdAt'], currentDateFormat));
                    row.displayData = row['createdAt'];
                    row.exportData = row['createdAt'];
                    return row;
                }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData', 'export': 'exportData' }
            });
            $.each(this.tableColumns, function (j, v) {
                columnDefs.push({
                    "name": "title" + v['fieldId'], type: 'null-last', "targets": i++, data: function (row, type, val, meta) {
                        var rowData = typeof row['formData'][v['fieldId']] == 'undefined' ? '' : row['formData'][v['fieldId']]['fieldValue'];
                        var displayRowData = rowData;
                        var sortRowData = rowData;
                        var exportData = rowData;
                        if (v.fieldType == 'fileupload' && rowData != '') {
                            var filePath = (_this.attachmentDownloadPath).replace("|placeholder|", rowData);
                            displayRowData = '<a class="fg-dev-file-field-link" href="' + filePath + '">' + rowData + '</a>';
                            exportData = displayRowData;
                        }
                        else if (v.fieldType == 'url' && rowData != '') {
                            displayRowData = '<a class="fg-dev-url-field-link" target="_blank" href="' + rowData + '">' + rowData + '</a>';
                            exportData = displayRowData;
                        }
                        else if (v.fieldType == 'date' && rowData != '') {
                            sortRowData = parseInt(_this.toTimeStamp(rowData, FgLocaleSettingsData.momentDateFormat));
                            displayRowData = rowData;
                        }
                        else if (v.fieldType == 'time' && rowData != '') {
                            sortRowData = parseInt(_this.toTimeStamp(rowData, FgLocaleSettingsData.momentTimeFormat));
                            displayRowData = rowData;
                        }
                        else if (v.fieldType == 'number' && rowData != '') {
                            sortRowData = parseFloat(rowData);
                            displayRowData = FgClubSettings.formatNumber(rowData);
                            exportData = FgClubSettings.formatDecimalMark(rowData);
                        }
                        else if (v.fieldType == 'multiline' && rowData != '') {
                            sortRowData = rowData;
                            var contentString = rowData;
                            if (contentString.length > 50) {
                                var lineBreak = rowData.match(/.{1,50}/g);
                                displayRowData = '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" >' + lineBreak[0] + ' &hellip;<div class="popover-content hide">' + contentString + '</div></i>';
                            }
                            else {
                                displayRowData = contentString;
                            }
                        }
                        sortRowData = (typeof sortRowData == 'string') ? sortRowData.toLowerCase() : sortRowData;
                        row.sortData = sortRowData;
                        row.displayData = displayRowData;
                        row.exportData = exportData;
                        return row;
                    }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData', 'export': 'exportData' }
                });
            });
        }
        var initialsortingColumn = this.isAllFormInquiries ? '0' : '1';
        var fixedcolumnCnt = this.isAllFormInquiries ? 3 : 2;
        var listAjaxPAth = (this.isAllFormInquiries) ? this.inquiryListAllAjaxPath : this.inquiryListAjaxPath.replace("placeholder", this.formElementId);
        datatableOptions = {
            columnDefFlag: true,
            fixedcolumn: true,
            fixedcolumnCount: fixedcolumnCnt,
            ajaxPath: listAjaxPAth,
            columnDefValues: columnDefs,
            serverSideprocess: false,
            displaylengthflag: true,
            popupFlag: true,
            initialSortingFlag: true,
            initialsortingColumn: initialsortingColumn,
            initialSortingorder: 'desc',
            module: 'CMS_FORM_INQUIRY',
            manipulationFlag: true,
            manipulationFunction: 'manipulationFn',
            rowlengthshow: true,
            rowlengthWrapperdivid: (this.target == 'listing') ? 'fg_dev_memberlist_row_length' : 'fg_dev_formedit_row_length',
            opt: {
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: this.formTitle,
                        fieldSeparator: this.fieldSeparator,
                        exportOptions: {
                            rows: '.fg-dev-isChecked',
                            columns: this.getTableColumnsForExport(),
                            orthogonal: 'export'
                        },
                        action: function (e, dt, button, config) {
                            config.fieldSeparator = _this.getFieldSeparator();
                            if ($.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)) {
                                $.fn.dataTable.ext.buttons.csvHtml5.action(e, dt, button, config);
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: this.formTitle,
                        fieldSeparator: this.fieldSeparator,
                        exportOptions: {
                            columns: this.getTableColumnsForExport(),
                            orthogonal: 'export'
                        },
                        action: function (e, dt, button, config) {
                            config.fieldSeparator = _this.getFieldSeparator();
                            if ($.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)) {
                                $.fn.dataTable.ext.buttons.csvHtml5.action(e, dt, button, config);
                            }
                        }
                    }
                ]
            }
        };
    };
    FgCmsInquiryList.prototype.toTimeStamp = function (date, currentFormat) {
        var timestamp = 0;
        if (typeof date != 'undefined' || date != '') {
            var momentObj = moment(date, currentFormat);
            timestamp = momentObj.format('x');
        }
        return timestamp;
    };
    FgCmsInquiryList.prototype.initActionMenu = function () {
        scope = angular.element($("#BaseController")).scope();
        window.actionMenuTextDraft = { 'active': { 'none': actionMenu.none, 'single': actionMenu.single, 'multiple': actionMenu.multiple } };
        scope.$apply(function () {
            scope.menuContent = window.actionMenuTextDraft;
        });
    };
    FgCmsInquiryList.prototype.removeExportAttachmentsFromActionMenu = function () {
        var changedActionMenu = $.extend(true, {}, actionMenu);
        delete changedActionMenu.none.exportInquiryAttachments;
        delete changedActionMenu.single.exportInquiryAttachments;
        delete changedActionMenu.multiple.exportInquiryAttachments;
        window.changedActionMenuTextDraft = { 'active': { 'none': changedActionMenu.none, 'single': changedActionMenu.single, 'multiple': changedActionMenu.multiple } };
        scope.$apply(function () {
            scope.menuContent = window.changedActionMenuTextDraft;
        });
    };
    FgCmsInquiryList.prototype.handleActionMenu = function () {
        if (!this.hasAttatchments) {
            return;
        }
        var attachmentCount = $('#datatable-inquiry-list tbody tr.fg-dev-isChecked .fg-dev-file-field-link').length;
        if (attachmentCount >= 1) {
            this.initActionMenu();
        }
        else {
            var changedActionMenu = $.extend(true, {}, actionMenu);
            changedActionMenu.single.exportInquiryAttachments.isActive = "false";
            changedActionMenu.multiple.exportInquiryAttachments.isActive = "false";
            window.changedActionMenuTextDraft = { 'active': { 'none': changedActionMenu.none, 'single': changedActionMenu.single, 'multiple': changedActionMenu.multiple } };
            scope.$apply(function () {
                scope.menuContent = window.changedActionMenuTextDraft;
            });
        }
    };
    FgCmsInquiryList.prototype.initPageTitleBar = function (option) {
        this.pageTitlebarOpt = {
            title: true,
            tab: (this.target == 'formEdit') ? true : false,
            actionMenu: this.isAllFormInquiries ? false : true,
            search: (this.target == 'listing') ? true : false,
            searchBox: false,
            editForm: (this.target == 'listing') ? option.hasEditFormBtn : false,
        };
        $(".fg-action-menu-wrapper").FgPageTitlebar(this.pageTitlebarOpt);
    };
    FgCmsInquiryList.prototype.redrawPageTitle = function (title) {
        $('.page-title > .page-title-text').text(title);
    };
    FgCmsInquiryList.prototype.handleClickEvent = function () {
        $(document).on('click', 'a.fg-dev-inquiry-popup-link', function (event) {
            event.preventDefault();
            var fiId = $(this).attr('data-fiId');
            var createdAt = $(this).attr('data-createdAt');
            var inquityDetailsUrl = (_this.inquityDetailsPopupPath).replace('placeholder', fiId);
            $.post(inquityDetailsUrl, {}, function (data) {
                var popupHTML = FGTemplate.bind('inquiryDetailPopupTemplate', { data: data, formTitle: createdAt, downloadPath: _this.attachmentDownloadPath });
                FgModelbox.showPopup(popupHTML);
            });
        });
        $(document).on('click', 'a.fg-dev-form-inquiry-link', function (event) {
            event.preventDefault();
            var elementId = $(this).attr('data-elementId');
            var li = $('a.nav-link[data-id="' + elementId + '"]').parent('li').attr('id');
            FgSidebar.handleSidebarClick(li);
        });
        $(document).on('click', '#savePopup', function () {
            var inquiryIds = $('#hiddenInquiryIds').val();
            FgXmlHttp.post(_this.inquiryDeletePath, { 'elementId': _this.getFormElementId(), 'inquiryIds': inquiryIds }, false, _this.deleteInquiryCallBack);
            FgModelbox.hidePopup();
        });
        $(document).on('click', '.fg-action-editForm', function (event) {
            event.preventDefault();
            var editFormUrl = (_this.editFormPath).replace('placeholder', _this.getFormElementId());
            document.location.href = editFormUrl;
        });
        $(document).on('click', 'input.fg-dev-inquiry-list-checkbox', function (el) {
            if ($(this).is(':checked')) {
                $('#datatable-inquiry-list tr').eq(trIndex).addClass('fg-dev-isChecked');
            }
            else {
                $('#datatable-inquiry-list tr').eq(trIndex).removeClass('fg-dev-isChecked');
            }
            _this.handleActionMenu();
        });
        $(document).on('click', '#check_all', function (el) {
            if ($(this).is(':checked')) {
                $('#datatable-inquiry-list tbody tr').addClass('fg-dev-isChecked');
            }
            else {
                $('#datatable-inquiry-list tbody tr').removeClass('fg-dev-isChecked');
            }
            _this.handleActionMenu();
        });
    };
    FgCmsInquiryList.prototype.exportAttachments = function (inquiryIds) {
        var url = (this.exportAttachmantpath).replace('placeholder', this.formElementId);
        window.location.href = url + '?inquiryIds=' + inquiryIds;
    };
    FgCmsInquiryList.prototype.showDeleteInquiryPopup = function (checkedIds) {
        var idArray = [];
        $.each(checkedIds.split(','), function (index, value) {
            if (value !== "") {
                idArray.push(parseInt(value));
            }
        });
        var popupHTML = FGTemplate.bind('inquiryDeletePopupTemplate', { data: idArray });
        FgModelbox.showPopup(popupHTML);
    };
    FgCmsInquiryList.prototype.showExportPopup = function () {
        var popupHTML = FGTemplate.bind('inquiryExportPopupTemplate');
        FgModelbox.showPopup(popupHTML);
        $('input[type=radio]').uniform();
        $(document).off('click', '#exportInquryBtn');
        $(document).on('click', '#exportInquryBtn', function (event) {
            event.preventDefault();
            var fieldSeparator = $("input[name='exportSeparator']:checked").val();
            _this.setFieldSeparator(fieldSeparator);
            var checkedCount = $('#datatable-inquiry-list_wrapper tr.fg-dev-isChecked').length;
            if (checkedCount > 0) {
                listTable.button('0').trigger();
            }
            else {
                listTable.button('1').trigger();
            }
            FgModelbox.hidePopup();
        });
    };
    FgCmsInquiryList.prototype.deleteInquiryCallBack = function () {
        _this.updateSidebarElements();
        listTable.ajax.reload();
    };
    FgCmsInquiryList.prototype.initSidebar = function () {
        var options = {
            clubId: clubId,
            jsonData: true,
            module: 'FORMINQUIRY',
            defaultMenuDetails: { menu: 'li_Overview', subMenu: 'li_Overview_all_forms' },
            sideClickCallback: function () {
                var dataId = $('#' + this + '> .nav-link').attr('data-id');
                dataId = (dataId == 'all_forms') ? '' : dataId;
                _this.renderFormInquiryList(dataId);
            },
        };
        FgSidebar.init(options, jsonData);
    };
    FgCmsInquiryList.prototype.updateSidebarElements = function () {
        $.getJSON(_this.getSidebarDataPath, {}, function (sidebarData) {
            FgSidebar.rebuildSidebar(sidebarData);
        });
    };
    return FgCmsInquiryList;
}());
var FgCmsCalendarElementLog = (function () {
    function FgCmsCalendarElementLog() {
        datatableOptions = this.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
    }
    FgCmsCalendarElementLog.prototype.dataTableOpt = function () {
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var i = 0;
        columnDefs.push({
            "name": "date", "type": "moment-" + currentDateFormat, "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['date'];
                row.displayData = '&nbsp;&nbsp;' + row['date'];
                return row;
            }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' }
        });
        columnDefs.push({
            "name": "option", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">' + statusTranslations.added + '</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">' + statusTranslations.changed + '</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">' + statusTranslations.removed + '</span>' : ''));
                var type = (row['type'] === 'element') ? statusTranslations.element : statusTranslations.page_assignment;
                row.sortData = row['type'];
                row.displayData = type + flag;
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' }
        });
        columnDefs.push({
            "name": "value_before", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueBefore'];
                row.displayData = row['valueBefore'];
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' }
        });
        columnDefs.push({
            "name": "value_after", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueAfter'];
                row.displayData = row['valueAfter'];
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' }
        });
        columnDefs.push({
            "name": "edited_by", "targets": i++, data: function (row, type, val, meta) {
                var profileLink = profilePath.replace("dummy", row['activeContactId']);
                row.sortData = row['contact'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['contact'] + '</a></div>' : '<span class="fg-table-reply">' + row['contact'] + '</span>';
                return row;
            }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' }
        });
        return {
            columnDefFlag: true,
            columnDefValues: columnDefs,
            ajaxPath: formElementLogPath,
            ajaxparameterflag: true,
            serverSideprocess: false,
            displaylengthflag: false,
            initialSortingFlag: true,
            initialsortingColumn: '0',
            initialSortingorder: 'desc',
            fixedcolumnCount: 0
        };
    };
    return FgCmsCalendarElementLog;
}());
//# sourceMappingURL=Fg_cms_inquiry_list.js.map
