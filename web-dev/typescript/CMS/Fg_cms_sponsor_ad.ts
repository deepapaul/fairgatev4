/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgCmsSponsorAdElement {

    public validateForm() {
        $('.form-group').removeClass('has-error');
        $('.form-group').find('span.element-required').remove();
        var err = 0;
        var sponsorServices = $('.fg-sponsor-services').val();
        var sponsorAreas = $('.fg-sponsor-areas').val();
        var adDisplay = $("input[name=adView]:checked").val();
        var faderTime = $("#faderInterval").val();
        var horizontalWidth = $("#horizontalWidth").val();
        if (sponsorServices == '' || sponsorServices == null) {
            $('#serviceSelectpicker').parent('div').append('<span class="required element-required">'+ statusTranslations.invalid +'</span>');
            $('#serviceSelectpicker').closest('div.form-group').addClass('has-error');
            err = 1;
        }
        if(typeof sponsorAreas != 'undefined' ){
        if (sponsorAreas == '' || sponsorAreas == null) {
            $('#areaSelectpicker').parent('div').append('<span class="required element-required">'+ statusTranslations.invalid +'</span>');
            $('#areaSelectpicker').closest('div.form-group').addClass('has-error');
            err = 1;
        }
        }
        if (adDisplay == 'fader') {
            if (faderTime == '' || faderTime == null) {
                $('#faderInterval').parent('div').append('<span class="required element-required">'+ statusTranslations.invalid  +'</span>');
                $('#faderInterval').closest('div.form-group').addClass('has-error');
                err = 1;
            }
        } else if (adDisplay == 'horizontal') {
            if (horizontalWidth == '' || horizontalWidth == null) {
                $('#horizontalWidth').parent('div').append('<span class="required">'+ statusTranslations.invalid  +'</span>');
                $('#horizontalWidth').closest('div.form-group').addClass('has-error');
                err = 1;
            }
        }
        if (err == 1) {
            return false;
        } else {
            return true;
        }
    }
      public renderContent() {
        $('#elementSponsorAdWrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsSponsorElementContent').addClass('active');
    }

    public renderLog() {
        $('#elementSponsorAdWrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        var CmsSponsorElementLog = new FgCmsSponsorElementLog();
        CmsSponsorElementLog.init();

    }
    public saveElementCallback(d) {
        var CmsSponsorElement = new FgCmsSponsorAdElement();
        FgDirtyFields.init('cms_sponsorad_element', { saveChangeSelector: "#save_changes, #save_bac", setInitialHtml: false, discardChangesCallback: CmsSponsorElement.discardAfterSave });
    }

    public discardChangesCallback() {

        $('.bootstrap-select').remove();
        $('select.selectpicker').selectpicker();
        $('select.selectpicker').selectpicker({ noneSelectedText: statusTranslations['select'] });
        FgUtility.handleSelectPicker();
        $('select.selectpicker').selectpicker('render');
        $('form input[name=adView]').unwrap().unwrap();
        FgFormTools.handleUniform();
        $('input[type=radio][name=adView]').change(function() {
            var disp = this.value;
            enableField(disp);
        });
    }

    public discardAfterSave() {
        $('.selectpicker').selectpicker('refresh');
        FgUtility.handleSelectPicker();
        FgFormTools.handleUniform();
        //        $('input[type=radio][name=adView]').trigger('change');
        var disp = $("input[name=adView]:checked").val();
        enableField(disp);
    }

}
class FgCmsSponsorElementLog {

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