
var oTable = '';
var updateFlag = true;
var plannedDataTableName = '';
var plannedSortField = '';
var sendingDataTableName = '';
var draftSortField = '';
var sendingSortField = '';
var s1 = '';
var sentSortField = '';

var FgCommunicationTable = function () {

    return {
        initMailingsid: function (tableId, filterFlag) {


            if (!jQuery().dataTable) {
                return;
            }

            var instData = $('#' + tableId);


            var ajaxPath = '';
            if (instData.hasClass("dataTable-ajax")) {

                ajaxPath = instData.attr('data-ajax-path');
                data = '';
                fgCommunicationDataTable(instData, data);
            }

            return oTable;

        }

    };
}();

function fgCommunicationDataTableInit(instData) {
    var displayPage;
    var rowCount;
    if (instData.hasClass("fg-dev-colums-send")) {
        displayPage = "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4'i><'col-md-8'p>";
        rowCount = 10;
    } else {
        displayPage = "<'row_select_datatow col-md-12'l><'col-md-12't>";
        rowCount = 50;
    }
    var opt = {
        language: {
            sSearch: "<span>" + datatabletranslations['data_Search'] + ":</span> ",
            sInfo: datatabletranslations['data_showing'] + " <span>_START_</span> " + datatabletranslations['data_to'] + " <span>_END_</span> " + datatabletranslations['data_of'] + " <span>_TOTAL_</span> " + datatabletranslations['data_entries'],
            sInfoEmpty: datatabletranslations['data_showing'] + " <span>0</span> " + datatabletranslations['data_to'] + " <span>0</span> " + datatabletranslations['data_of'] + " <span>0</span> " + datatabletranslations['data_entries'],
            sZeroRecords: datatabletranslations['no_matching_records'],
            lengthMenu: '<select>' +
                    '<option value="10">10 ' + datatabletranslations['row'] + '</option>' +
                    '<option value="20">20 ' + datatabletranslations['row'] + '</option>' +
                    '<option value="50">50 ' + datatabletranslations['row'] + '</option>' +
                    '<option value="100">100 ' + datatabletranslations['row'] + '</option>' +
                    '<option value="200">200 ' + datatabletranslations['row'] + '</option>' +
                    '</select> ',
            oPaginate: {
                "sFirst": '<i class="fa fa-angle-double-left"></i>',
                "sLast": '<i class="fa fa-angle-double-right"></i>',
                "sNext": '<i class="fa fa-angle-right"></i>',
                "sPrevious": '<i class="fa fa-angle-left"></i>'
            }
        },
        paging: false,
        scrollCollapse: true,
        dom: displayPage,
        iDisplayLength: rowCount,
        autoWidth: true,
        stateSave: true,
        stateDuration: 60 * 60 * 24,
        pagingType: "full_numbers"

    };
    return opt;
}
function fgCommunicationDataTable(instData, data) {
    var opt = fgCommunicationDataTableInit(instData);
    if (instData.hasClass("dataTable-ajax")) {

        opt.ajax = {
            "url": instData.attr('data-ajax-path'),
            "data": function (parameter) {


            },
            "type": "POST",
            "dataSrc": function (json) {
                if (_.size(json.aaData) > 0) {
                    instData.parents('.fg-dev-dataTable-hide-wrapper').show();
                }
                //console.log(instData.fnSettings().fnRecordsTotal());
                var dataTabletype = instData.attr('data_table_type');
                if (typeof dataTabletype !== 'undefined' && dataTabletype !== false) {
                    if (dataTabletype == 'recipient') {
                        manipulateRecipienttable(json);
                    }
                }

                return json.aaData;
            }
        };
        opt.serverSide = false;
        opt.processing = false;


    }
    var columnDeflag = instData.attr('data-column-def');
    if (instData.hasClass("dataTable-ajaxHeader")) {

        //opt.aoColumns = emailColumnTitle;

    }

    if (typeof columnDeflag !== 'undefined' && columnDeflag !== false) {

        if (instData.hasClass("fg-dev-colums-draft")) {
            opt.columnDefs = columnDrafts;
        } else if (instData.hasClass("fg-dev-colums-planned")) {
            opt.columnDefs = columnPlanned;
        } else if (instData.hasClass("fg-dev-colums-sending")) {
            opt.columnDefs = columnSending;
        } else if (instData.hasClass("fg-dev-colums-send")) {
            opt.columnDefs = columnSend;
        }

    }
    
    //For handle the width of the datatable
    if (instData.hasClass("dataTable-scroll-x")) {
        var xwidth = instData.attr('xWidth')
        opt.sScrollX = xwidth + "%";
        opt.sScrollXInner = xwidth + "%";
    }
    // For handle the height of the dataTable
    if (instData.hasClass("dataTable-scroll-y")) {

        var yheight = FgCommon.getWindowHeight(275);
        opt.scrollY = yheight + "px";
    }
    opt.deferRender = true;

    // For handle the height of the dataTable
    if (instData.hasClass("fg-dev-no-paging")) {

        opt.paging = false;
    } else {
        opt.paging = true;
    }

    opt.lengthChange = true;
    instData.on( 'length.dt', function ( e, settings, len ) {
        FgUtility.startPageLoading();
    } );

    opt.fnRowCallback = function (nRow, aData, iDataIndex) {

    };
    opt.stateLoadCallback = function (oSettings) {
        var stringified = localStorage.getItem('DataTables_communication' + window.location.pathname + window.location.search)
        var oClubData = JSON.parse(stringified || null);
        if (oClubData && oSettings.fnRecordsTotal() < oClubData.start) {
            oClubData.start = 0;
            //oSettings.fnStateSave(oSettings, oClubData);
        }
        if (oClubData) {
            $("#searchbox").val(oClubData.search.search);
        }
        return oClubData;
    };
    opt.stateSaveCallback = function (settings, data) {
        localStorage.setItem('DataTables_communication' + window.location.pathname + window.location.search, JSON.stringify(data));
        if (instData.hasClass("fg-dev-colums-planned") || instData.hasClass("fg-dev-colums-sending") || instData.hasClass("fg-dev-colums-send")) {
            handleNextPreviousOption();
        }
    };
    var handleNextPreviousOption = function(){
        var nextPreviousOptions = {};
        var key = saveNextPreviousKey;
        var column = 'id';
        var path = saveNextPreviousPath;
        var currentDataSet = [];
        if (instData.hasClass("fg-dev-colums-planned")) {
            currentDataSet = $('.fg-dev-colums-planned').dataTable().api().rows().data();
            key+= '_SCHEDULED';
        } else if (instData.hasClass("fg-dev-colums-sending")) {
            currentDataSet = $('.fg-dev-colums-sending').dataTable().api().rows().data();
            key+= '_SENDING';
        } else if (instData.hasClass("fg-dev-colums-send")) {
            currentDataSet = $('.fg-dev-colums-send').dataTable().api().rows().data();
            key+= '_SENT';
        }
        var currentData = _.pluck(currentDataSet, column).join();
        if(nextPreviousOptions.currentData != currentData){
            nextPreviousOptions.currentData = currentData;
            $.ajax({
                 type: "POST",
                 url: path,
                 data: {'key':key,'id':currentData}
               });
        }
    }

    opt.fnHeaderCallback = function (thead, aData, iStart, iEnd, aiDisplay) {
        //$(thead).find('th').eq(0).css('width','50px');
    };
    opt.fnDrawCallback = function (oSettings) {

        if (from == 'mailings') {
            if (plannedDataTableName != "") {
                plannedSortField = plannedDataTableName.fnSettings().aaSorting;
            }
            if (sendingDataTableName != "") {
                sendingSortField = sendingDataTableName.fnSettings().aaSorting;
            }
            if (draftDataTableName != "") {
                draftSortField = draftDataTableName.fnSettings().aaSorting;
            }

            if (s1 != "") {
                sentSortField = s1.fnSettings().aaSorting;
                searchValSent = s1.api().search(this.value);
                if (searchValSent != "") {
                    sentSortField = sentSortField + ',' + searchValSent;
                }
            }
            if ($.cookie) {
                $.cookie('communication_' + clubId + '_' + contactId + '_plannedsortorder', plannedSortField);
                $.cookie('communication_' + clubId + '_' + contactId + '_sendingsortorder', sendingSortField);
                $.cookie('communication_' + clubId + '_' + contactId + '_sentsortorder', sentSortField);
                $.cookie('communication_' + clubId + '_' + contactId + '_draftsortorder', draftSortField);
            }
        }

        if (instData.hasClass("fg-dev-colums-send")) {
            if (s1 != "") {
                var info = s1.api().page.info();
                var selectedRowLength = info.length;
                var totalRecordsDisplay = info.recordsDisplay;
                if (selectedRowLength >= totalRecordsDisplay) {
                    $('#fg-dev-dataTable-send_wrapper .col-md-4').hide();
                    $('#fg-dev-dataTable-send_wrapper .col-md-8').hide();
                } else {
                    $('#fg-dev-dataTable-send_wrapper .col-md-4').show();
                    $('#fg-dev-dataTable-send_wrapper .col-md-8').show();
                }
            }
        }

        // For setting the count in the top of the datatable
        if (instData.hasClass("data-count")) {
            handleCountDisplay(oTable);
        }

        //stop the pageloading process
        setTimeout(function(){FgUtility.stopPageLoading();}, 1000);

        FgPopOver.init(".fg-dev-Popovers", true);

    };
    opt.fnServerParams = function (aoData) {

    };
    opt.fnInitComplete = function (settings, json) {
        updateFlag = false;

    }
    if (instData.hasClass("dataTable-initialSort")) {

        var str = instData.attr('data-sort');
        var res = str.split("#");
        opt.order = [[res[0], res[1]]];

    }

    /** DATABASE INITIALIZING AREA **/
    //block the error/warning pop up block
    $.fn.dataTable.ext.errMode = 'none';
    //initialize datatable with error event handling code
    oTable = instData.on('error.dt', function (e, settings, data) {
        window.location.reload();
    }).dataTable(opt);


    // For change the position of the no.of records per page selection drop down box

    var replaceid = instData.attr('data_row_change');
    if (replaceid != "") {
        //for change the position
        var tableid = instData.attr('data-table-name');
        $("." + tableid + "_length").detach().prependTo("#" + replaceid);
        //add our own classes to the selectbox
        $("." + tableid + "_length").find('select').addClass('form-control cl-bs-select');
        $("." + tableid + "_length").find('select').select2();

    }



    instData.css("width", '100%');

    $('.dataTables_filter input').attr("placeholder", "Search here...");

    $("#run").change(function () {
        oTable.fnDraw();
    });
    return oTable;
}






//handle the count dispaly area 
function handleCountDisplay() {
}
function  manipulateRecipienttable(json) {
    //var languageArray = FgUtility.groupByMulti(json.adData, ['CL_lang']);
    $("#langcount").html('');
    if (_.size(json.adData) > 1) {
        var langCounts = "(";
        $.each(json.adData, function (key, valueArray) {
            if (valueArray['515'] != '') {
                langCounts += " " + valueArray['515'] + ": " + valueArray['count'] + ",";
            }

        });
        langCounts = langCounts.substring(0, langCounts.length - 1);
        langCounts += ")";
        $("#langcount").html(langCounts);
    }
}
$(function () {
    draftDataTableName = FgCommunicationTable.initMailingsid('fg-dev-dataTable-drafts');
    plannedDataTableName = FgCommunicationTable.initMailingsid('fg-dev-dataTable-planned');
    sendingDataTableName = FgCommunicationTable.initMailingsid('fg-dev-dataTable-sending');
    s1 = FgCommunicationTable.initMailingsid('fg-dev-dataTable-send');

    if (from == 'mailings') {
        plannedSortField = plannedDataTableName.fnSettings().aaSorting;
        sendingSortField = sendingDataTableName.fnSettings().aaSorting;
        draftSortField = draftDataTableName.fnSettings().aaSorting;
        sentSortField = s1.fnSettings().aaSorting;
        searchValSent = s1.api().search(this.value);
        if (searchValSent != "" && s1 != "") {
            sentSortField = sentSortField + ',' + searchValSent;
        }

        if ($.cookie) {
            $.cookie('communication_' + clubId + '_' + contactId + '_plannedsortorder', plannedSortField);
            $.cookie('communication_' + clubId + '_' + contactId + '_sendingsortorder', sendingSortField);
            $.cookie('communication_' + clubId + '_' + contactId + '_sentsortorder', sentSortField);
            $.cookie('communication_' + clubId + '_' + contactId + '_draftsortorder', draftSortField);
        }
    }

    //For change the search box field
    $("#searchbox").on("keyup", function () {
        var val;
        var searchVal = this.value;
        setDelay(function(){
        if (searchVal == '"') {
            val = '';
        } else {
            val = searchVal;
        }
        s1.api().search(val).draw();
         }, 500);
    });

})



