/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgCmsTwitterElement {


    public renderContent() {
        $('#elementTwitterWrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsTwitterElementContent').addClass('active');
    }

    public renderLog() {
        $('#elementTwitterWrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        var CmsTwitterElementLog = new FgCmsTwitterElementLog();
        CmsTwitterElementLog.init();

    }
    public triggerEnterKey(){
        $("#cms_twitter_element input").off('keypress');
        $("#cms_twitter_element input").on('keypress', function (e) {
            if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                e.preventDefault();
                return false;
            }
            else {
                return true;
            }
        });
    }
    public saveElementCallback(data) {
        $.each(data.accountTitle.accountName, function(key, value) {
            $('#accountName-' + key).val(value);
        });
        $.each($('input.accountName'), function (i, obj) {
            $(this).attr('placeholder', data['accountTitle']['mainAccountName']);
        });
        var CmsTwitterElement = new FgCmsTwitterElement();
        FgDirtyFields.init('cms_twitter_element', { saveChangeSelector: "#save_changes, #save_bac", setInitialHtml: false, discardChangesCallback: CmsTwitterElement.discardAfterSave });
    }

    public discardAfterSave() {

     FgFormTools.handleUniform();
    
    }
    public discardChangesCallback() {
        FgFormTools.handleUniform();
    }
    public validateForm(acccountName, contentHeightLimit) {
        $('.form-group').removeClass('has-error');
        $('.form-group').find('span.required').remove();
        var err = 0;
        if (acccountName[defaultlanguage] === '') {
            $('.accountName').parent('div').append('<span class="required">'+ statusTranslations.requiredFieldMessage +'</span>');
            $('.accountName').closest('div.form-group').addClass('has-error');
            $('.btlang ').removeClass('active');
            $('#' + defaultlanguage).addClass('active');
            FgUtility.showTranslation(defaultlanguage);
            err = 1;
        } else {
            $.each(acccountName, function(key, value) {
                if (/^[a-zA-Z0-9-@_ ]*$/.test(value) == false) {
                    $("#failcallbackClientSide").removeClass('hide');
                    $("#failcallbackClientSide").show();
                    $('.accountName').parent('div').append('<span class="required">'+statusTranslations.warningMessage+' </span>');
                    $('.accountName').closest('div.form-group').addClass('has-error');
                    $('.btlang ').removeClass('active');
                    $('#' + key).addClass('active');
                    FgUtility.showTranslation(key);
                    err = 1;
                    return false;
                }
            });

        }
        if(!(/^\+?\d+$/.test(contentHeightLimit)) && (contentHeightLimit)) { //if not an integer
            $('#contentHeightLimit').parent('div').append('<span class="required">' + statusTranslations.warningMessageDigits + ' </span>');
            $('#contentHeightLimit').closest('div.form-group').addClass('has-error');
            err = 1;            
        }
        if (err == 1) {
            return false;
        }
        return true;
    }

}

class FgCmsTwitterElementLog {

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
