$(document).ready(function () {
    scope = angular.element($("#BaseController")).scope();
    $(".fg-action-menu-wrapper").FgPageTitlebar({
        title: true,
        row2: true,
        tab: true,
        tabType: 'client'
    });
    $('select.selectpicker').selectpicker({noneSelectedText: statusTranslations['select']});
    initPageFunctions();
});
function initPageFunctions() {
    $('select#areaSelectpicker').selectpicker('fg-event-select');
    $('select#catSelectpicker').selectpicker('fg-event-select');
        var option = {
        pageType: 'cmsAddElement',
        contactId: contactId,
        currentClubId: clubId,
        localStorageName: type + '_' + clubId + '_' + contactId,
        tabheadingArray: tabheadingArray
    };
    Fgtabselectionprocess.initialize(option);
    FgDirtyFields.init('cms_calendar_element', {saveChangeSelector: "#save_changes, #save_bac", discardChangesCallback:FgCmsCalendarElement.discardChangesCallback});
    FgCmsCalendarElementLog.init();
    FgUtility.handleSelectPicker();
    FgCmsCalendarElement.renderContent();

}

var FgCmsCalendarElement = {
    renderContent: function () {
        $('#elementCalendarWrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsCalendarElementContent').addClass('active');
    },
    renderLog: function () {
        $('#elementCalendarWrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        FgCmsCalendarElementLog.init();

    },
    saveElementCallback: function (d) {
        FgDirtyFields.init('cms_calendar_element', {saveChangeSelector: "#save_changes, #save_bac", discardChangesCallback:FgCmsCalendarElement.discardChangesCallback});
    },
    discardChangesCallback :function(){
        $('.bootstrap-select').remove();
        $('select#areaSelectpicker').selectpicker('fg-event-select');
        $('select#catSelectpicker').selectpicker('fg-event-select');
        FgUtility.handleSelectPicker();    
        $('select.selectpicker').selectpicker({noneSelectedText: statusTranslations['select']});
        $('select.selectpicker').selectpicker('render');  
    }
};
var FgCmsCalendarElementLog = {
    init: function () {
        FgCmsCalendarElementLog.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    },
    reload: function () {
        listTable.ajax.reload();
    },
    dataTableOpt: function () {
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var i = 0;
        columnDefs.push({"name": "date", "type": "moment-" + currentDateFormat, "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['date'];
                row.displayData = '&nbsp;&nbsp;' + row['date'];
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "option", width: '20%', "targets": i++, data: function (row, type, val, meta) {

                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">'+statusTranslations.added+'</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">'+statusTranslations.changed+'</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">'+statusTranslations.removed+'</span>' : ''));
                var type = (row['type'] === 'element') ? statusTranslations.element : statusTranslations.page_assignment;
                row.sortData = row['type'];
                row.displayData = type+ flag;
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_before", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueBefore'];
                row.displayData = row['valueBefore'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_after", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueAfter'];
                row.displayData = row['valueAfter'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "edited_by", "targets": i++, data: function (row, type, val, meta) {
                var profileLink = profilePath.replace("**placeholder**", row['activeContactId']);
                row.sortData = row['contact'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['contact'] + '</a></div>' : '<span class="fg-table-reply">' + row['contact'] + '</span>';
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});

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
    }
};

function validateForm() {
    var areas = $('.fg-event-areas').val();
    var categories = $('.fg-event-categories').val();
    if (areas == null) {
        $('form#cms_calendar_element select.fg-event-areas').parent().addClass('has-error');
        $('<span class="help-block fg-marg-top-5">required</span>').insertAfter($('form#cms_calendar_element select.fg-event-areas + .btn-group.bootstrap-select'));
        return false;
    }

    if (categories == null) {
        $('form#cms_calendar_element select.fg-event-categories').parent().addClass('has-error');
        $('<span class="help-block fg-marg-top-5">required</span>').insertAfter($('form#cms_calendar_element select.fg-event-categories + .btn-group.bootstrap-select'));
        return false;
    }
    return true;
}
$('body').on('click', '#preview', function (e) {
     window.location.href = contentEditPagePath;
});

$('body').off('click', '#save_changes,#save_bac');
$('body').on('click', '#save_changes,#save_bac', function (e) {
    $('form#cms_calendar_element .help-block').remove();
    $('form#cms_calendar_element .has-error').removeClass('has-error');
    var currentSelectedButton = $(this).attr('id');
    var isValid = validateForm();
    if(isValid){
    var areas = $('.fg-event-areas').val();
    var categories = $('.fg-event-categories').val();
    var isAllArea = '';
     var isAllCategories = '';
    if(areas == 'ALL_AREAS'){
        var isAllArea = 1;
    }
    if(categories == 'ALL_CATS'){
        var isAllCategories = 1;
    }
    var saveType =  (currentSelectedButton == 'save_changes') ? 'save' : 'saveBack';
    var param = {'categories': categories, 'areas': areas, 'pageId': pageId, 'boxId': boxId, 'elementId': elementId,'sortOrder':sortOrder,'isAllArea':isAllArea,'isAllCategories':isAllCategories, 'saveType': saveType}
    FgDirtyFields.removeAllDirtyInstances();
    FgXmlHttp.post(saveCalendarElementPath, {'param': param}, false, FgCmsCalendarElement.saveElementCallback);
}
});


