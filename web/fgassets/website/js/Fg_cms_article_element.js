var FgCmsArticleElement = (function () {
    function FgCmsArticleElement() {
    }
    FgCmsArticleElement.prototype.renderContent = function () {
        $('#elementArticleWrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsArticleElementContent').addClass('active');
    };
    FgCmsArticleElement.prototype.renderLog = function () {
        var CmsArticleElementLog = new FgCmsArticleElementLog();
        CmsArticleElementLog.init();
        $('#elementArticleWrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        $('.fg-lang-tab').addClass('invisible');
    };
    FgCmsArticleElement.prototype.saveElementCallback = function (d) {
        var CmsArticleElement = new FgCmsArticleElement({});
        FgDirtyFields.init('addArticleElement', { saveChangeSelector: "#save_changes, #save_bac", setInitialHtml: false, discardChangesCallback: CmsArticleElement.discardAfterSave });
    };
    FgCmsArticleElement.prototype.discardChangesCallback = function () {
        $('.bootstrap-select').remove();
        $('.selectpicker').selectpicker();
        $('select.selectpicker').selectpicker({ noneSelectedText: statusTranslations['select'] });
        $('select.selectpicker').selectpicker('render');
        FgUtility.handleSelectPicker();
        $('form input[name=fedShared], [name=subFedShared]').unwrap().unwrap();
        FgFormTools.handleUniform();
    };
    FgCmsArticleElement.prototype.discardAfterSave = function () {
        $('.selectpicker').selectpicker('refresh');
        FgUtility.handleSelectPicker();
        var fedCheckVal = $("#fedShared").attr('data-id');
        var subFedCheckVal = $("#subFedShared").attr('data-id');
        if (fedCheckVal != '') {
            $("#fedShared").parent('span').addClass('checked');
        }
        else {
            $("#fedShared").parent('span').removeClass('checked');
        }
        if (subFedCheckVal != '') {
            $("#subFedShared").parent('span').addClass('checked');
        }
        else {
            $("#subFedShared").parent('span').removeClass('checked');
        }
        FgFormTools.handleUniform();
    };
    FgCmsArticleElement.prototype.isValidForm = function (articleAreas, articleCategories, fedIdVal, subFedIdVal) {
        $("#failcallbackClientSide").addClass('hide');
        if ((fedIdVal == '' || fedIdVal == null) && (subFedIdVal == '' || subFedIdVal == null)) {
            if (articleAreas == null || articleCategories == null) {
                $("#failcallbackClientSide").removeClass('hide');
                $("#failcallbackClientSide").show();
                return false;
            }
        }
        return true;
    };
    return FgCmsArticleElement;
}());
var FgCmsArticleElementLog = (function () {
    function FgCmsArticleElementLog() {
    }
    FgCmsArticleElementLog.prototype.init = function () {
        this.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    };
    FgCmsArticleElementLog.prototype.dataTableOpt = function () {
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var i = 0;
        columnDefs.push({ "name": "date", "type": "moment-" + currentDateFormat, "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['date'];
                row.displayData = '&nbsp;&nbsp;' + row['date'];
                return row;
            }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' } });
        columnDefs.push({ "name": "option", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">' + statusTranslations[row['status']] + '</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">' + statusTranslations[row['status']] + '</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">' + statusTranslations[row['status']] + '</span>' : '-'));
                var type = (row['type'] === 'element') ? statusTranslations.element : statusTranslations.page_assignment;
                row.sortData = row['type'];
                row.displayData = type + flag;
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' } });
        columnDefs.push({ "name": "value_before", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueBefore'];
                row.displayData = row['valueBefore'];
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' } });
        columnDefs.push({ "name": "value_after", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueAfter'];
                row.displayData = row['valueAfter'];
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' } });
        columnDefs.push({ "name": "edited_by", "targets": i++, data: function (row, type, val, meta) {
                var profileLink = profilePath.replace("dummy", row['activeContactId']);
                row.sortData = row['contact'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['contact'] + '</a></div>' : '<span class="fg-table-reply">' + row['contact'] + '</span>';
                return row;
            }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' } });
        datatableOptions = {
            columnDefFlag: true,
            ajaxPath: elementLogDetailsPath,
            ajaxparameterflag: true,
            ajaxparameters: {
                elementId: elementId
            },
            columnDefValues: columnDefs,
            serverSideprocess: false,
            displaylengthflag: false,
            initialSortingFlag: true,
            initialsortingColumn: '0',
            initialSortingorder: 'desc',
            fixedcolumnCount: 0
        };
    };
    return FgCmsArticleElementLog;
}());
