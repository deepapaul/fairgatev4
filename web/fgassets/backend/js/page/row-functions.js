var rowFunctions = {
    // Function to display the filter in log listing.
    showLogFilter: function () {
        $('form').on('click', '.fgContactLogFilter', function () {
            var typeId = $(this).attr('data-typeId');
            $('div[data-log-area="log-area_' + typeId + '"]').toggleClass('show');
            var tableGroup = "log_display_" + typeId;
            $('table.table[data-table-group="' + tableGroup + '"]').toggleClass('fg-common-top');
            $('#fg-log-filter_' + typeId).toggleClass('fg-active-btn');
        });
        $('form').on('shown.bs.tab', '.data-more-tab li a[data-toggle="tab"]', function () {
            var curDataTableId = $(this).attr('data-datatableid');
            $('#' + curDataTableId).dataTable().api().draw();
        });
    },
    // Function to display the log.
    logdisplay: function (elemId, classtype) {
        var logParams = rowFunctions.getLogDataParams(elemId, classtype);
        var param = logParams.param;
        var jsonlog = logParams.jsonlog;
        var typeId = logParams.typeId;
        FgUtility.startPageLoading();
        
        if ($("#" + elemId).children().attr('data-loaded') == 'true') {
            $('#log_' + jsonlog['typeId']).children('.fg-pad-20').css('display','block');
            if ((classtype == 'team') || (classtype == 'log_role') || (classtype == 'log_fun')) {
                $(this).find('#displaydetails_' + elemId).removeClass('hide');
                $("#log_" + typeId).removeClass('hide');
            }
            FgUtility.stopPageLoading();
        } else {
            $("#" + elemId).children().attr('data-loaded', 'true');
            $.getJSON(rowFunctionVariables.logDataPath + param, null, function (data) {
                var logdisplay = FgUtility.groupByMulti(data.logdisplay, ['tabGroups']);
                var hierarchyClubIdArr = data.hierarchyClubIdArr;

                jsonlog['details'] = logdisplay;
                jsonlog['hierarchyClubIdArr'] = hierarchyClubIdArr;
                jsonlog['logTabs'] = data.logTabs;
                jsonlog['activeTab'] = '1';
                var html = FGTemplate.bind('log-listing', jsonlog);
                $('#log_' + jsonlog['typeId']).children('.fg-pad-20').append(html);
                $('#log_' + jsonlog['typeId']).children('.fg-pad-20').css('display','block');
                $('#log_' + jsonlog['typeId']).slideDown(600);

                FgUtility.stopPageLoading();
                $('div.date input:enabled').parent().datepicker(FgApp.dateFormat);
                var logTabsLength = 2;
                for (var i = 1; i <= logTabsLength; i++) {
                    FgUtility.displaylogsettings(typeId + '_' + i);
                    logDateFilterSubmit('date_filter_' + typeId + '_' + i);
                }
                FgMoreMenu.initClientSideWithNoError('data-tabs_' + jsonlog['typeId'], 'data-tabs-content_' + jsonlog['typeId']);
            });
        }
    },
    // Function to get parameters for displaying log.
    getLogDataParams: function(elemId, classtype) {
        var params = elemId.split('_');
        var param = '';
        var jsonlog = {};
        var typeId = '';
        switch(classtype) {
            case 'membership':
                param = '?type=membership&membershipId=' + params[0];
                jsonlog = {type: 'membership', typeId: params[0]};
                typeId = params[0];
                break;
            case 'team':
                param = '?type=' + params[1] + '&teamCatId=' + params[0] + '&roleId=' + params[2];
                jsonlog = {type: params[1], typeId: params[2]};
                typeId = params[2];
                break;
            case 'log_role':
                param = '?type=' +  params[3]+ '&CatId=' + params[0]+ '&roleId='+ params[2];
                jsonlog = {type: params[1], typeId: params[2]};
                typeId = params[2];
                break;
            case 'log_fun':
                param = '?type=function&roleId='+ params[0];
                jsonlog = {type: 'function', typeId: params[0]};
                typeId = params[0];
                break;
        }
        var logParams = {'param': param, 'jsonlog': jsonlog, 'typeId': typeId};

        return logParams;
    }
};