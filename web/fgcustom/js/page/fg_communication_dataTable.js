
var oTable = '';
var updateFlag = true;

var FgCommunicationTable = function () {

    return {
        //main function to initiate the module
        init: function () {

            if (!jQuery().dataTable) {
                return;
            }

            // dataTables
            var tableObj = '';
            if ($('.fg-dev-communication-dataTable').length > 0) {
                $('.fg-dev-communication-dataTable').each(function () {
                    var instData = $(this);

                    var ajaxPath = '';
                    if (instData.hasClass("dataTable-ajax")) {
                        data = '';
                        ajaxPath = instData.attr('data-ajax-path');
                        tableObj = fgCommunicationDataTable(instData, data);
                    }

                });

            }
            return tableObj;
        },
        initid: function (tableId) {


            if (!jQuery().dataTable) {
                return;
            }

            var instData = $('#' + tableId);


            var ajaxPath = '';
            if (instData.hasClass("dataTable-ajax")) {

                ajaxPath = instData.attr('data-ajax-path');

            }
            data = '';
            fgCommunicationDataTable(instData, data);

            return oTable;

        }

    };
}();

function fgCommunicationDataTableInit() {
    var opt = {
        language: {
            sSearch: "<span>" + datatabletranslations['data_Search'] + ":</span> ",
            sInfo: datatabletranslations['data_showing'] + " <span>_START_</span> " + datatabletranslations['data_to'] + " <span>_END_</span> " + datatabletranslations['data_of'] + " <span>_TOTAL_</span> " + datatabletranslations['data_entries'],
            sInfoEmpty: datatabletranslations['data_showing'] + " <span>0</span> " + datatabletranslations['data_to'] + " <span>0</span> " + datatabletranslations['data_of'] + " <span>0</span> " + datatabletranslations['data_entries'],
            sZeroRecords: datatabletranslations['no_matching_records'],
            sEmptyTable: datatabletranslations['no_record'],
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
        dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4'i><'col-md-8'p>",
        autoWidth: true,
        stateSave: true,
        stateDuration: 60 * 60 * 24,
        pagingType: "full_numbers"

    };
    return opt;
}
function fgCommunicationDataTable(instData, data) {
    var opt = fgCommunicationDataTableInit();
    if (instData.hasClass("dataTable-ajax")) {

        opt.ajax = {
            "url": instData.attr('data-ajax-path'),
            "data": function (parameter) {
                var dataTabletype = instData.attr('data_table_type');

                if (typeof dataTabletype !== 'undefined' && dataTabletype !== false) {
                    if (dataTabletype == 'nl_mandatory_recipient') {
                        parameter.filterdata = filterId;
                        parameter.updateflag = updateFlag;
                        parameter.manualSelectedIds = manualaddedIds;
                        parameter.newsletterId = newsletterId;

                    } else {
                        parameter.filterdata = filterId;
                    }
                } else {
                    parameter.filterdata = filterId;
                    parameter.updateflag = updateFlag;
                }
            },
            "type": "POST",
            "dataSrc": function (json) {
                var dataTabletype = instData.attr('data_table_type');
                if (typeof dataTabletype !== 'undefined' && dataTabletype !== false) {
                    if (dataTabletype == 'recipient' || dataTabletype == 'nl_mandatory_recipient') {
                        manipulateRecipienttable(json);
                    }
                }

                return json.aaData;
            }
        };

        var serversideprocess = instData.attr('serverside_process');
        if (typeof serversideprocess !== 'undefined' && serversideprocess !== false) {
            opt.serverSide = false;
            opt.processing = false;
        } else {
            opt.serverSide = true;
            opt.processing = true;
        }



    }
    var columnDeflag = instData.attr('data-column-def');

    if (instData.hasClass("dataTable-ajaxHeader")) {

        //opt.aoColumns = emailColumnTitle;

    }

    if (typeof columnDeflag !== 'undefined' && columnDeflag !== false) {
        var columndefname = instData.attr('data-column-def-name');
        if (typeof columndefname !== 'undefined' && columndefname !== false) {

            opt.columnDefs = columnDefs1;
        } else {
            opt.columnDefs = columnDefs;
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
    opt.paging = true;
    opt.lengthChange = true;

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
    };

    opt.fnHeaderCallback = function (thead, aData, iStart, iEnd, aiDisplay) {
        //$(thead).find('th').eq(0).css('width','50px');
    };
    opt.fnDrawCallback = function (oSettings) {

// For setting the count in the top of the datatable
        if (instData.hasClass("data-count")) {
            handleCountDisplay(oTable);
        }

        //stop the pageloading process
        setTimeout(function () {
            FgUtility.stopPageLoading();}, 200);



    };
    opt.fnServerParams = function (aoData) {

    };
    opt.fnInitComplete = function (settings, json) {
        updateFlag = false;
    }
    if (instData.hasClass("dataTable-initialSort")) {

        var str = instData.attr('data-sort');
        var res = str.split("#");
        opt.order = [res[0], res[1]];

    }

    /** DATABASE INITIALIZING AREA **/
    //block the error/warning pop up block
    $.fn.dataTable.ext.errMode = 'none';
    //initialize datatable with error event handling code
    oTable = instData.on('error.dt', function (e, settings, data) {
        window.location.reload();
    }).dataTable(opt);
    instData.on( 'length.dt', function ( e, settings, len ) {
        FgUtility.startPageLoading();
    });

    // For change the position of the no.of records per page selection drop down box

    var replaceid = instData.attr('data_row_change');
    if (typeof replaceid !== 'undefined' && replaceid !== false) {

        //for change the position
        var tableid = instData.attr('data-table-name');
        $("." + tableid + "_length").detach().prependTo("#" + replaceid);
        //add our own classes to the selectbox
        $("." + tableid + "_length").find('select').addClass('form-control cl-bs-select');
        $("." + tableid + "_length").find('select').select2();

    }

    //For change the search box field
    $("#searchbox").off('keyup');
    $("#searchbox").on("keyup", function () {
        var searchVal = this.value;
        setDelay(function () {
            oTable.api().search(searchVal).draw();
        }, 500)
    });


    instData.css("width", '100%');

    $('.dataTables_filter input').attr("placeholder", "Search here...");

    $("#run").change(function () {
        oTable.fnDraw();
    });
    return oTable;
}



$(function () {

    $(window).bind('resize', function () {
        if (!$.isEmptyObject(oTable)) {
            oTable.fnAdjustColumnSizing();
        }

    });

})


//handle the count dispaly area 
function handleCountDisplay() {
}
function  manipulateRecipienttable(json) {
    $("#langcount").html('');
    if (typeof json.adData !== 'undefined') {
        if (_.size(json.adData) > 1) {
            var langCounts = "(";
            $.each(json.adData, function (key, valueArray) {
                if (valueArray['515'] != '') {
                    langCounts += " " + valueArray['515'].toUpperCase() + ": " + valueArray['count'] + ",";
                }

            });
            langCounts = langCounts.substring(0, langCounts.length - 1);
            langCounts += ")";
            $("#langcount").html(langCounts);
        }
    }
}


