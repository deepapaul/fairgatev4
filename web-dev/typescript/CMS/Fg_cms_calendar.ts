/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgCmsCalendarElement {


    public renderContent() {
        $('#elementCalendarWrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsCalendarElementContent').addClass('active');
    }

    public renderLog() {
        $('#elementCalendarWrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        var CmsCalendarElementLog = new FgCmsCalendarElementLog();
        CmsCalendarElementLog.init();

    }
    public saveElementCallback(d) {
        var CmsCalendarElement = new FgCmsCalendarElement();
        FgDirtyFields.init('cms_calendar_element', { saveChangeSelector: "#save_changes, #save_bac", setInitialHtml: false, discardChangesCallback: CmsCalendarElement.discardAfterSave });
    }

    public discardAfterSave() {
     $('.selectpicker').selectpicker('refresh');
     FgUtility.handleSelectPicker();  
     var fedCheckVal = $("#fedShared").attr('data-id');
     var subFedCheckVal = $("#subFedShared").attr('data-id');
     
     if(fedCheckVal != ''){
           $("#fedShared").parent('span').addClass('checked');
       }else{
          $("#fedShared").parent('span').removeClass('checked');
      }
     if(subFedCheckVal != ''){
          $("#subFedShared").parent('span').addClass('checked');
       }else{
          $("#subFedShared").parent('span').removeClass('checked');
      }
     FgFormTools.handleUniform();
    
    }
    public discardChangesCallback() {
        $('.bootstrap-select').remove();
        $('select.selectpicker').selectpicker();
        $('select.selectpicker').selectpicker({ noneSelectedText: statusTranslations['select'] });
        FgUtility.handleSelectPicker();
        $('select.selectpicker').selectpicker('render');
        $('form input[name=fedShared], [name=subFedShared]').unwrap().unwrap();
        FgFormTools.handleUniform();
    }
    public validateForm() {
        var areas = $('.fg-event-areas').val();
        var categories = $('.fg-event-categories').val();
        var fedIdVal = ($("#fedShared").is(':checked')) ? fedId : '';
        var subFedIdVal = ($("#subFedShared").is(':checked')) ? subFedId : '';
        if ((fedIdVal == '' || fedIdVal == null) && (subFedIdVal == '' || subFedIdVal == null)) {
            if (areas == null || categories == null) {
                 $("#failcallbackClientSide").removeClass('hide');
                $("#failcallbackClientSide").show();
                return false;
            }
        }
        return true;
    }

}

class FgCmsCalendarElementLog {

    public init() {
        this.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    }

    public dataTableOpt() {
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var i = 0;
        columnDefs.push({
            "name": "date", "type": "moment-" + currentDateFormat, "targets": i++, data: function(row, type, val, meta) {
                row.sortData = row['date'];
                row.displayData = '&nbsp;&nbsp;' + row['date'];
                return row;
            }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' }
        });
        columnDefs.push({
            "name": "option", width: '20%', "targets": i++, data: function(row, type, val, meta) {

                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">' + statusTranslations.added + '</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">' + statusTranslations.changed + '</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">' + statusTranslations.removed + '</span>' : ''));
                var type = (row['type'] === 'element') ? statusTranslations.element : statusTranslations.page_assignment;
                row.sortData = row['type'];
                row.displayData = type + flag;
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' }
        });
        columnDefs.push({
            "name": "value_before", width: '20%', "targets": i++, data: function(row, type, val, meta) {
                row.sortData = row['valueBefore'];
                row.displayData = row['valueBefore'];
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' }
        });
        columnDefs.push({
            "name": "value_after", width: '20%', "targets": i++, data: function(row, type, val, meta) {
                row.sortData = row['valueAfter'];
                row.displayData = row['valueAfter'];
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' }
        });
        columnDefs.push({
            "name": "edited_by", "targets": i++, data: function(row, type, val, meta) {
                var profileLink = profilePath.replace("**placeholder**", row['activeContactId']);
                row.sortData = row['contact'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['contact'] + '</a></div>' : '<span class="fg-table-reply">' + row['contact'] + '</span>';
                return row;
            }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' }
        });

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
}
