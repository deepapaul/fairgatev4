
var oTable = '';
var fc = '';
var FgTable = function() {

    return {
        //main function to initiate the module
        init: function() {

            if (!jQuery().dataTable) {
                return;
            }

            // dataTables
            if ($('.dataTable').length > 0) {
                $('.dataTable').each(function() {
                    var instData = $(this);

                    var ajaxPath = '';
                    if (instData.hasClass("dataTable-ajax")) {
                        data = '';
                        ajaxPath = instData.attr('data-ajax-path');
                        fgDataTable(instData, data);
                    }
                    else {
                        data = '';
                        fgDataTable(instData, data);
                    }
                });

            }
        },
        initid: function(tableId) {
            filter_submit(tableId);
            if (!jQuery().dataTable) {
                return;
            }

            var instData = $('#' + tableId);


            var ajaxPath = '';
            if (instData.hasClass("dataTable-ajax")) {

                ajaxPath = instData.attr('data-ajax-path');
            }
            if (instData.hasClass("dataTable-ajaxHeader")) {

            } else {
                data = '';
                fgDataTable(instData, data);
            }

        }


    };
}();

function fgDataTableInit() {
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
        columnDefs: [{"width": "10%", "targets": 0}],
        scrollCollapse: true,
        dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4'i><'col-md-8'p>",
        autoWidth: true,
        stateSave: true,
        stateDuration: 60 * 60 * 24,
        pagingType: "full_numbers"
    };
    return opt;
}
function fgDataTable(instData, data) {
    var opt = fgDataTableInit();
    if (instData.hasClass("dataTable-ajax")) {

        opt.ajax = {
            "url": instData.attr('data-ajax-path'),
            "data": function(parameter) {
                var storageName = instData.attr('data-storage');
                var tablecolumnName = '';
                if (typeof storageName !== 'undefined' && storageName !== false) {
                    tablecolumnName = localStorage.getItem(storageName + "_" + clubId + "_" + contactId);
                }
                parameter.filterdata = filterdata;
                parameter.tableField = tablecolumnName;
            },
            "type": "POST",
            "dataSrc": function(json) {
                manipulateColumnFields(json);

                return json.aaData;
            }
        };
        opt.serverSide = true;
        opt.processing = true;


    }
    if (instData.hasClass("dataTable-ajaxHeader")) {
        var columnStorageName = instData.attr('data-columnstorage');

        if (typeof columnStorageName !== typeof undefined && columnStorageName !== false) {
            tablecolumnName = localStorage.getItem(columnStorageName + "_" + clubId + "_" + contactId);
            opt.aoColumns = $.parseJSON(tablecolumnName);
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

    opt.fnRowCallback = function(nRow, aData, iDataIndex) {
        //give - value to the null 
        $('td', nRow).each(function(index, value) {
            if ($(this).html() == '' || $(this).html() == null) {
                $(this).html("-")
            }
        });

    };
    if (instData.hasClass("dataTable-widthResize")) {
        tablecolumnName = localStorage.getItem(columnStorageName + "_" + clubId + "_" + contactId);
        var totalColumn = (_.size($.parseJSON(tablecolumnName))) - 1;
        opt.columnDefs = [{"width": "100%", "targets": totalColumn}];
    }
    opt.stateLoadCallback = function(oSettings) {
        var stringified = localStorage.getItem('DataTables_club' + window.location.pathname + window.location.search)
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
    opt.stateSaveCallback = function(settings, data) {
        localStorage.setItem('DataTables_club' + window.location.pathname + window.location.search, JSON.stringify(data));
    };
    

    opt.fnHeaderCallback = function(thead, aData, iStart, iEnd, aiDisplay) {
        //$(thead).find('th').eq(0).css('width','50px');
    };
    opt.fnDrawCallback = function(oSettings) {
        //checkbox convert to uniform model
         FgCommon.checkboxpluginInit();

// For setting the count in the top of the datatable
        if (instData.hasClass("data-count")) {
            handleCountDisplay(oTable);
        }

        if (!$.isEmptyObject(fc)) {
            fc.fnRedrawLayout();
        }
        //stop the pageloading process
        setTimeout(function(){FgUtility.stopPageLoading();}, 1000);

        $(".chk_cnt").html('');

        //show default menu
        //fncSetDefaultmenu();

        /*Drag/Drop Starts*/
        if (instData.hasClass("dataTable-dragable")) {
            var insideMain = false;
            $(".dataTables_scrollBody").droppable({
                over: function(  ) {
                    insideMain = true;
                },
                out: function() {
                    insideMain = false;
                }
            });

            $(".dataTable tr .fg-sort").draggable({
                cursorAt: {top: 5, left: -20},
                helper: function(event) {
                    var contactlist = '';
                    if ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length > 0) {
                        var count = $(".DTFC_LeftBodyWrapper input.dataClass:checked").length;
                    } else {
                        var count = 1;
                    }
                    var contactId = $(this).parent().find("input[class=dataClass]").attr('id');
                    var isCompany = $(this).parent().find("input[class=dataClass]").attr('data-iscompany');
                    var contactName = $(this).parent().parent().parent().find('.fg-dev-contactname').html();
                    var contactClubId = $(this).parent().find("input[class=dataClass]").attr('data-contactclub');
                    contactlist = contactId + '%#-#%' + isCompany + '%#-#%' + encodeURI(contactName) + '%#-#%' + contactClubId;
                    return $("<div class='ui-widget-header'><span style='display:none;' id='contactList'>" + contactlist + "</span><span class='fg-drag-count'>" + count + "</span></div>");
                },
                containment: "body"
            });
            FgSidebar.droppableEventIconHandling('club');
        }
        ;
        /*Drag/Drop ends*/

    };
    opt.fnServerParams = function(aoData) {

    };
    opt.fnInitComplete = function() {

        if ($(this).hasClass("dataTable-fixed")) {
            FgCommon.generateFixedColumn(oTable, 2);
        }
        //checkbox-check all initialize
        setTimeout(function () {
            FgCheckBoxClick.init('dataTable');
        }, 200);
        //init a popover
        fncDatatablepopover(instData);
        // For change the position of the no.of records per page selection drop down box
        fncChangeRowposition(instData);
    };
    
    var tableid = instData.attr('data-table-name');
    $('#'+tableid).on( 'length.dt', function ( e, settings, len ) {
        FgUtility.startPageLoading();
    } );
    
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


    //For change the search box field
    $("#searchbox").on("keyup", function() {
        var searchVal = this.value;
        setDelay(function () {
        oTable.api().search(searchVal).draw();
        if ($("#searchbox").val() != '') {
            $("#tcount").show();
            $("#fg-slash").show();
            //$(".fa-filter").show();
        } else {
            $("#tcount").hide();
            $("#fg-slash").hide();
            //$(".fa-filter").hide();
        }
        }, 500);
    });


    instData.css("width", '100%');

    $('.dataTables_filter input').attr("placeholder", "Search here...");

    $("#run").change(function() {
        oTable.fnDraw();
    });
    return oTable;
}


$(function() {
    $(window).bind('resize', function() {
        if (!$.isEmptyObject(oTable)) {
            oTable.fnAdjustColumnSizing();
        }

    });

})
//for create the mouse over effect on the both fixed column and normal table
$(document).on({
    mouseenter: function() {
        trIndex = $(this).index() + 1;
        $("table.dataTable").each(function(index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                $(this).find("td").addClass("fghover");
                $(this).find("td").css("background", "#f5f5f5");
            });
        });
    },
    mouseleave: function() {
        trIndex = $(this).index() + 1;
        $("table.dataTable").each(function(index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                $(this).find("td").removeClass("fghover");
                $(this).find("td").css("background", "#fff");
            });
        });
    }

}, ".dataTables_wrapper tr");

function fncDatatablepopover(instData) {
    var attr = $(this).attr('dataTable-popover');

// For some browsers, `attr` is undefined; for others,
// `attr` is false.  Check for both.
    if (typeof attr !== 'undefined' && attr !== false) {

        FgPopOver.init(".fgPopovers", true);

    }
}

function fncChangeRowposition(instData) {

    var replaceid = instData.attr('data_row_change');
    if (replaceid != "") {
        //for change the position
        var tableid = instData.attr('data-table-name');
        $("." + tableid + "_length").detach().prependTo("#" + replaceid);
        //add our own classes to the selectbox
        $("." + tableid + "_length").find('select').addClass('form-control cl-bs-select');
        $("." + tableid + "_length").find('select').select2();
    }
}
//handle the count dispaly area 
function handleCountDisplay() {
    if ($.isNumeric(totalCount) && totalCount >= 0 && $("#filterCount").length > 0) {
        $("#tcount").html(totalCount);
        $("#fcount").html(oTable.fnSettings().fnRecordsTotal());
    } else if (!$.isNumeric(totalCount) && totalCount == '' && $("#filterCount").length > 0) {
        // $("#tcount").html(oTable.fnSettings().fnRecordsTotal());
        $("#fcount").html(oTable.fnSettings().fnRecordsTotal());
    } else if ($.isNumeric(totalCount) && totalCount == 0 && $("#filterCount").length == 0) {
        $("#tcount").html(0)
        $("#fcount").html(0)
    } else if ($.isNumeric(totalCount) && totalCount >= 0 && $("#filterCount").length == 0) {
        $("#tcount").html(totalCount)
        $("#fcount").html(0)
    } else if (typeof totalCount == 'string' && totalCount == '' && $("#filterCount").length > 0) {
    } else if ($.isNumeric(totalCount) && totalCount == 0) {
        $("#tcount").html(0)
        $("#fcount").html(0)
    } else if (!$.isNumeric(totalCount) && totalCount == '' && $("#filterCount").length == 0) {
        $("#fcount").html(0)
    } else {
        // $("#tcount").html(0)
        // $("#fcount").html(0)
        $("#tcount").html(oTable.fnSettings().fnRecordsTotal());
        $("#fcount").html(oTable.fnSettings().fnRecordsTotal());
    }

}
//Function for handling client side filtering of Log diplay 
//Start date, End date and kind select box
function filter_submit(tableId) {
    $('#' + tableId).parent().find('div.date').each(function() {
        $(this).datepicker({autoclose: true}).change(function() {
            var type_Id = $(this).find('input').attr("data-event");
            clientsideFiltering(type_Id);
        });
    });
    $(".selectpicker").on("change", function() {
        var type_Id = $(this).attr("data-event");
        clientsideFiltering(type_Id);
    });
}
//Function for filtering through the Datatable
function clientsideFiltering(type_Id) {
    $.fn.dataTable.ext.afnFiltering.push(
            function(settings, data, dataIndex) {
                if (settings.nTable.id != 'log_display_' + type_Id) {
                    return true;
                }
                var searchData = $("#log_filter_type_" + type_Id).val();
                var hasSrchData = false;
                var date = data[0]; //date value of current record
                var kind = data[1]; //kind value of current record
                var startdate = $("#filter_start_date_" + type_Id).val() != '' ? $("#filter_start_date_" + type_Id).val() : '';
                var enddate = $("#filter_end_date_" + type_Id).val() != '' ? $("#filter_end_date_" + type_Id).val() : '';
                if ((startdate != '') || (enddate != '')) {
                    var error = false;
                    if ((startdate != '') || (enddate != '')) {
                        var div = 'log_date_error_' + type_Id;
                        error = FgUtility.validateDate($("#filter_start_date_" + type_Id).val(), $("#filter_end_date_" + type_Id).val(), div);
                    }
                    if (error) {
                        return false;
                    } else {
                        $('#log_date_error_' + type_Id).css('display', 'none');
                        var startdateArr = startdate.split('.');
                        var enddateArr = enddate.split('.');
                        var startDate = (startdate == '') ? '' : new Date(startdateArr[2], startdateArr[1], startdateArr[0], 00, 00);
                        var endDate = (enddate == '') ? '' : new Date(enddateArr[2], enddateArr[1], enddateArr[0], 23, 59);
                        var dateArr = date.split(' ');
                        var rowDateArr = dateArr[0].split('.');
                        var rowTimeArr = dateArr[1].split(':');
                        var rowDate = (date == '') ? '' : new Date(rowDateArr[2], rowDateArr[1], rowDateArr[0], rowTimeArr[0], rowTimeArr[1]);
                        if (
                                ((startDate == '') && (endDate == '')) ||
                                ((startDate == '') && (rowDate <= endDate)) ||
                                ((startDate <= rowDate) && (endDate == '')) ||
                                ((startDate <= rowDate) && (rowDate <= endDate))
                                )
                        {
                            hasSrchData = filterKindField(kind, searchData);
                            return (hasSrchData ? true : false);
                        } else {
                            return false;
                        }
                    }
                } else {
                    hasSrchData = filterKindField(kind, searchData);
                    return (hasSrchData ? true : false);
                }
            }
    );
    $('#log_display_' + type_Id).dataTable().api().draw();
}
//kind field filtering
function filterKindField(kind, searchData) {
    if ((searchData == null) || !$.inArray('all', searchData)) {
        return true;
    } else {
        var hasSrchVal = false;
        $.each(searchData, function(keyVal, searchVal) {
            if (searchVal == kind) {
                hasSrchVal = true;
            }
        });
        if (hasSrchVal) {
            return true;
        } else {
            return false;
        }
    }
}

function filterGlobaldata(actualdata) {
    var getData1212 = actualdata;

    if (_.has(getData1212, 'class')) {

        $.each(getData1212['class']['entry'], function(iteratekey, iteratevalues) {
//                  console.log(iteratevalues);
            if (_.has(iteratevalues, 'input')) {
                var count = _.size(iteratevalues['input']);
                if (count == 0) {
//                        console.log(count);
                    delete getData1212['class']['entry'][iteratekey];
                }
            }

        });

    }
    if (_.has(getData1212, 'AF')) {
        delete getData1212['AF'];

    }
    if (_.has(getData1212, 'filter')) {
        delete getData1212['filter'];
    }
    if (_.has(getData1212, 'bookmark')) {
        delete getData1212['bookmark'];
    }

    return getData1212;

}

(function($) {
    $.fn.liveDroppable = function(opts) {
        //Used for enabling drag-drop functionality and related icon handling in sidebar menu
        if (opts['hoverClass'] === 'fg-sidebar-hover') {
            FgSidebar.newMenuOpts['draggable'] =  opts;
        }
        if (opts['hoverClass'] === 'fg-sidebar-not-allowed') {
            FgSidebar.newMenuOpts['nondraggable'] = opts;
        }
        if (!$(this).data("init")) {
            $(this).data("init", true).droppable(opts);
        }
    };

}(jQuery));

function showPopup(type, params) {
    var requestPath = '';
    if (type == 'connection') {
        if (typeof connectionPath !== 'undefined') {
            requestPath = connectionPath;
        }
    } else if (type == 'assignment') {
        if (typeof assignmentPath !== 'undefined') {
            requestPath = assignmentPath;
        }
    }
    if (requestPath != '') {
        $('#popup_contents').html('');
        var proceedToRequestedpath = true;
        if (type == 'connection') {
            var connParams = params['contactData'].split(',');
            var contactidArr1 = connParams[0].split('%#-#%');
            var contactidArr2 = connParams[1].split('%#-#%');
            var draggedContact = contactidArr1[0];
            var droppedContact = contactidArr2[0];
            if (draggedContact == droppedContact) {
                proceedToRequestedpath = false;
                var notAllowedText = (typeof jstranslations.ACTION_NOT_ALLOWED != 'undefined' && jstranslations.ACTION_NOT_ALLOWED != '')?jstranslations.ACTION_NOT_ALLOWED:'Not allowed';
                FgUtility.showToastr(notAllowedText, 'warning');
            }
        }
        if (proceedToRequestedpath) {
            $.get(requestPath, params, function(data) {
                if (data.status == 'FAILURE') {
                    FgUtility.showToastr(data.flash, 'warning');
                } else {
                    $('#popup_contents').html(data);
                    $('#popup').modal('show');
                }
            });
        }
    }
}

function manipulateColumnFields(json)
{
    for (var i = 0, ien = json.aaData.length; i < ien; i++) {

        for (var j = 0, jen = json.aaDataType.length; j < jen; j++) {
            var title = json.aaDataType[j]['title'];

            switch (json.aaDataType[j]['type']) {
                case "CF_email":
                    json.aaData[i][title] = json.aaData[i][title] ? '<a  href="mailto:' + json.aaData[i][title] + '" target="_self">' + json.aaData[i][title] + '</a>' : '-';
                    break;
                case "CF_website":
                    json.aaData[i][title] = json.aaData[i][title] ? '<a href="' + json.aaData[i][title] + '" target="_blank">' + json.aaData[i][title] + '</a>' : '-';
                    break;
                case "AFNotes":
                    var url = json.aaData[i]["AFNotes_url"];
                    json.aaData[i][title] = (json.aaData[i][title] > 0) ? '<a href="' + url + '" target="_blank">' + json.aaData[i][title] + '</a>' : 0;
                    break;
                case "AFDocuments":
                    var url = json.aaData[i]["AFDocuments_url"];
                    json.aaData[i][title] = (json.aaData[i][title] > 0) ? '<a href="' + url + '" target="_blank">' + json.aaData[i][title] + '</a>' : 0;
                    break;    
                    
                case "clubname"  :
                    json.aaData[i][title] = "<a href='" + json.aaData[i]['clubname_url'] + "' class='fg-dev-clubname'>" + json.aaData[i][title] + "</a>";
                    break;
                case "edit"  :
                    if (i == 0) {
                        json.aaData[i][title] = "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox' id=" + json.aaData[i]['id'] + " name='check'   value='0' ></div><input type='hidden' id='filterCount' value='0'><input type='hidden' id='totalCount' value='0'>";
                    } else {
                        json.aaData[i][title] = "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox' id=" + json.aaData[i]['id'] + " name='check'  value='0' ></div>";
                    }

                    break;
            }

        }
    }

}