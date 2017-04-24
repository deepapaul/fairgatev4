$(function() {

    FgUtility.startPageLoading();
    /* Services listing */
    $('div[data-list-wrap]').rowList({
        template: '#sponsorServices',
        jsondataUrl: serviceSettingVars.pathServiceList,
        fieldSort: '.sortables',
        submit: ['#save_changes', 'servicesettings'],
        reset: '#reset_changes',
        useDirtyFields: true,
        dirtyFieldsConfig: { enableDiscardChanges : false, enableDragDrop: false, enableUpdateSortOrder: false },
        addData: ['#addrow', {
            isAllActive: false,
            isNew: true
        }],
        loadTemplate:[{
            btn:'#addrow',
            template:'#addSponsorServices'
        }],
        validate: true,
        postURL: serviceSettingVars.saveAction,
        success: function() {
            alert('Posting Data');
        },
        onErrorCallback:function(){
            FgUtility.showTranslation(serviceSettingVars.defaultLang);
        },
        load: function() {
            //console.log(_dynamicFunction);
        },
        initCallback: function() {
            FgPageTitlebar.checkMissingTranslation(serviceSettingVars.defaultLang);
        },
        triggerFunction: ['triggerAfterLoading'],
        rowCallback: function() {
            triggerAfterLoading();
        }
    });

    /* Showing translation entries on selecting different languages */
    $('form').off('click', 'button[data-elem-function=switch_lang]');
    $('form').on('click', 'button[data-elem-function=switch_lang]', function() {
        var selectedLang = $(this).attr('data-selected-lang');
        serviceSettingVars.selectedLang = selectedLang;
        FgUtility.showTranslation(selectedLang);
    });

    /* Switching between payment plans */
    $('form').off('click', 'input[data-type=paymentplan]');
    $('form').on('click', 'input[data-type=paymentplan]', function() {
        var serviceId = $(this).attr('data-id');
        var paymentPlan = $(this).val();
        $('div[data-type=paymentplan_' + serviceId + ']').addClass('hide');
        $('div[id=' + paymentPlan + 'Text_' + serviceId + ']').removeClass('hide');
        $('div#' + serviceId + ' input.fg-dev-paymentdata').removeAttr('required');
        $('div[id=' + paymentPlan + 'Text_' + serviceId + '] input.fg-dev-paymentdata').attr('required', '');
    });

    /* Log Filter */
    $('form').on('click', '.fgContactLogFilter', function(){
        var typeId = $(this).attr('data-typeId');
        $('div[data-log-area="log-area_'+typeId+'"]').toggleClass('show');
        var tableGroup = "log_display_"+typeId;
        $('table.table[data-table-group="'+tableGroup+'"]').toggleClass('fg-common-top');
        $('#fg-log-filter_'+typeId).toggleClass('fg-active-btn');
    });

    /* Switching between log tabs */
    $('form').off('shown.bs.tab', '.data-more-tab li a[data-toggle="tab"]');
    $('form').on('shown.bs.tab', '.data-more-tab li a[data-toggle="tab"]', function() {
        var curDataTableId = $(this).attr('data-datatableid');
        $('#' + curDataTableId).dataTable().api().draw();
    });
});

/* Function to be executed after loading page content */
var initPageFunctions = function() {
    FgUtility.stopPageLoading();
    /* Function to show service log data */
    _dynamicFunction.openLog = function(elem) {
        var serviceId = elem.id;
        var logDataPath = serviceSettingVars.pathLog;
        logDataPath = logDataPath.replace('serviceId', serviceId);

        if (!$(this).hasClass('LogDataExist')) {
            FgUtility.startPageLoading();
            $(this).addClass('LogDataExist');
            $.getJSON(logDataPath, null, function(data) {
                var logdisplay = FgUtility.groupByMulti(data.logdisplay, ['tabGroups']);
                var jsonlog = {type: 'service', typeId: serviceId, details: logdisplay, logTabs: data.logTabs, activeTab: '1'};
                var html = FGTemplate.bind('log-listing', jsonlog);
                $('#data-log-' + serviceId).children('.fg-pad-20').append(html);
                $('#data-log-' + serviceId).children('.fg-pad-20').slideDown(600);
                FgUtility.stopPageLoading();
                $('div.date input:enabled').parent().datepicker(FgApp.dateFormat);
                var logTabsLength = 2;
                for (var i = 1; i <= logTabsLength; i++) {
                    FgUtility.displaylogsettings(serviceId + '_' + i);
                    logDateFilterSubmit('date_filter_' + serviceId + '_' + i);
                }
                FgMoreMenu.initClientSideWithNoError('data-tabs_' + jsonlog['typeId'], 'data-tabs-content_' + jsonlog['typeId']);
            });
        } else {
            FgUtility.startPageLoading();
            $('#log-table_' + serviceId).show();
            FgUtility.stopPageLoading();
        }
    };
    _dynamicFunction.openDetails = function(elem) {
    };
    _dynamicFunction.openPaymentPlan = function(elem) {
    };

    /* Save Data */
    _dynamicFunctionSuccess = function() {
        $('select[data-type=selectcategory]').each(function(){
            if ($(this).val() != serviceSettingVars.catId) {
                var serviceId = $(this).attr('data-id');
                $('div#' + serviceId).find('input[data-sort-parent=sortservices]').remove();
            }
        });
        FgUtility.resetSortOrder($('div#sortservices'));
        FgDirtyFields.updateFormState();

        var objectGraph = FgParseFormField.fieldParse(),
            stringifyData = JSON.stringify(objectGraph);
        FgDirtyFields.removeAllDirtyInstances();
        FgXmlHttp.post(serviceSettingVars.saveAction, {saveData: stringifyData,catId:serviceSettingVars.catId}, false, function(){
            _dynamicFunction.getData();
        });
      }        
};
/* Function to be executed after loading services */
var triggerAfterLoading = function() {
    FgUtility.showTranslation(serviceSettingVars.selectedLang);
    FgFormTools.handleBootstrapSelect();
    FgFormTools.handleInputmask();
};
var isNumber = function(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}