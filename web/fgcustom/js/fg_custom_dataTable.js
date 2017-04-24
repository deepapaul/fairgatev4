
var oTable = '';
var sponsorTable = '';
var fc = '';
var tabIndex = 1;
var FgTable = function () {

    return {
        //main function to initiate the module
        init: function () {

            if (!jQuery().dataTable) {
                return;
            }


            // dataTables
            if ($('.dataTable').length > 0) {
                $('.dataTable').each(function () {
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
        initid: function (tableId, filterFlag) {

            if (typeof filterFlag === "undefined") {
                filterFlag = false;
            }
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
            return oTable;

        }
    };
}();




function fgDataTableInit() {
    var opt = {
        language: {
            sSearch: "<span>" + datatabletranslations['data_Search'] + ":</span> ",
            sInfo: datatabletranslations['data_showing'] + " <span>_START_</span> " + datatabletranslations['data_to'] + " <span>_END_</span> " + datatabletranslations['data_of'] + " <span>_TOTAL_</span> " + datatabletranslations['data_entries'],
            sZeroRecords: datatabletranslations['no_matching_records'],
            sInfoEmpty: datatabletranslations['data_showing'] + " <span>0</span> " + datatabletranslations['data_to'] + " <span>0</span> " + datatabletranslations['data_of'] + " <span>0</span> " + datatabletranslations['data_entries'],
            sEmptyTable: datatabletranslations['no_record'],
            sInfoFiltered: "(" + datatabletranslations['filtered_from'] + " <span>_MAX_</span> " + datatabletranslations['total_entries'] + ")",
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
        dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4 col-sm-4 col-xs-12'i><'col-md-8 col-sm-8 col-xs-12'p>",
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
            "data": function (parameter) {
                var tablecolumnName = localStorage.getItem(tableSettingValueStorage);
                var tablecolumnName1 = localStorage.getItem(filterRoleStorage);
                parameter.filterdata = filterdata;
                parameter.tableField = tablecolumnName;
                parameter.filterrole = tablecolumnName1;
                var functionValues = localStorage.getItem(functionshowStoragename);
                if (typeof functionValues !== 'undefined' && functionValues !== false) {
                    if (functionValues != '' && functionValues !== null) {
                        parameter.functionType = functionValues;
                    }

                }
                localStorage.setItem(functionshowStoragename, functionValues);

            },
            "type": "POST",
            "dataSrc": function (json) {
                manipulateColumnFields(json);
                return json.aaData;

            }
        };
        opt.serverSide = true;
        opt.processing = true;
    }
    // To set the default row length of a table
    if (typeof contactType != 'undefined') {
        var rowLength = localStorage.getItem('tableRowCount-' + contactType + '-' + contactId + '-' + clubId);
        if (rowLength) {
            opt.iDisplayLength = rowLength;
        }
    }
    if (instData.hasClass("dataTable-ajaxHeader")) {

        opt.aoColumns = $.parseJSON(localStorage.getItem(tableColumnTitleStorage));

    }
    //avoid sorting on column with icons
    if (instData.hasClass("noColumnSort")) {
        opt.aoColumnDefs = [{bSortable: false, aTargets: ["no-sort"]}];
    }
    if (instData.hasClass("no_initial_sort")) {
        opt.aaSorting = [];
    }
    //For handle the width of the datatable
    if (instData.hasClass("dataTable-scroll-x")) {
        var xwidth = instData.attr('xWidth')
        opt.sScrollX = xwidth + "%";
        opt.sScrollXInner = xwidth + "%";
        opt.scrollCollapse = true;
    }
    // For handle the height of the dataTable
    if (instData.hasClass("dataTable-scroll-y")) {

        var yheight = FgCommon.getWindowHeight(318);
        opt.scrollY = yheight + "px";

    }
    if (instData.hasClass("dataTable-rows")) {
        //opt.sDom = opt.sDom+'<div i>';
    }
    if (instData.hasClass("dataTable-search")) {
        // opt.Dom = "f" + opt.Dom;
    }
    opt.stateLoadCallback = function (settings) {
        var stringified = localStorage.getItem('DataTables_' + window.location.pathname + window.location.search)
        var oData = JSON.parse(stringified || null);
        if (oData && settings.fnRecordsTotal() < oData.start) {
            oData.start = 0;
            // settings.fnStateSave(settings, oData);
            // state.save();

        }
        if (oData) {
            $("#searchbox").val(oData.search.search);

        }


        return oData;
    }
    opt.stateSaveCallback = function (settings, data) {
        localStorage.setItem('DataTables_' + window.location.pathname + window.location.search, JSON.stringify(data));
    }

    opt.deferRender = true;
    opt.paging = true;
    opt.lengthChange = true;

    opt.fnRowCallback = function (nRow, aData, iDataIndex) {
        //give - value to the null
        $('td', nRow).each(function (index, value) {
            if ($(this).html() == '' || $(this).html() == null) {
                $(this).html("-");
            }
        });
    };

    if (instData.hasClass("dataTable-widthResize")) {
        var totalColumn = (_.size($.parseJSON(localStorage.getItem(tableColumnTitleStorage)))) - 1;
        opt.columnDefs = [{"width": "100%", "targets": totalColumn}];
    }

    opt.fnHeaderCallback = function (nHead, aData, iStart, iEnd, aiDisplay) {

    };
    opt.fnDrawCallback = function(oSettings) {
        FgTooltip.init();
        // Checking whether club executive members are missing.
        if (window['checkExecutiveMembersMissing']) {
            checkExecutiveMembersMissing();
        }
        if (window['requiredFedRoleMissingAssignment']) {
            requiredFedRoleMissingAssignment();
        }

        //checkbox convert to uniform model
        FgCommon.checkboxpluginInit();

// For setting the count in the top of the datatable


        if (instData.hasClass("data-count")) {
            countDisplay(oSettings);
        }

        /*Drag/Drop Starts*/
        if (instData.hasClass("dataTable-dragable")) {
            var insideMain = false;
            $(".dataTables_scrollBody").droppable({
                over: function (  ) {
                    insideMain = true;
                },
                out: function () {
                    insideMain = false;
                }
            });

            $(".dataTable tr .fg-sort").draggable({
                cursorAt: {top: 5, left: -20},
                helper: function (event) {

                    var contactlist = '';
                    if ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length > 0) {
                        var count = $(".DTFC_LeftBodyWrapper input.dataClass:checked").length;
                    } else {
                        var count = 1;
                    }
                    $("#sidemenu_bar li.fg-dev-non-draggable a").css("cursor", "not-allowed");
                    $("#sidemenu_bar li.fg-dev-draggable a").css("cursor", "grabbing");
                    var contactId = $(this).parent().find("input[class=dataClass]").attr('id');
                    var isCompany = $(this).parent().find("input[class=dataClass]").attr('data-iscompany');
                    var contactName = $(this).parent().parent().parent().find('.fg-dev-contactname').html();
                    var contactClubId = $(this).parent().find("input[class=dataClass]").attr('data-contactclub');
                    contactlist = contactId + '%#-#%' + isCompany + '%#-#%' + encodeURI(contactName) + '%#-#%' + contactClubId;
                    return $("<div class='ui-widget-header fg-dev-grabbing-icon '><span style='display:none;' id='contactList'>" + contactlist + "</span><span class='fg-drag-count'>" + count + "</span></div>");
                },
                containment: "body"
            });
            if (instData.hasClass("dataTable-droppable")) {
                $(".dataTable tr").droppable({
                    drop: function (event, ui) {
                        if ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length <= 1) {
                            var n = ui.helper.find("#contactList").text().search("##");
                            if (n < 0) {
                                var contactId = $(this).find("input[class=dataClass]").attr('id');
                                var isCompany = $(this).find("input[class=dataClass]").attr('data-iscompany');
                                var contactName = $(this).find('a.fg-dev-contactname').html();
                                var contactClubId = $(this).find("input[class=dataClass]").attr('data-contactclub');
                                var contactlist = ui.helper.find("#contactList").text();
                                contactlist = contactlist.concat('*#*##*#*', contactId + '%#-#%' + isCompany + '%#-#%' + encodeURI(contactName)) + '%#-#%' + contactClubId;
                                showPopup('connection', {contactData: contactlist});
                            }
                        }
                    },
                    accept: function () {
                        return insideMain;
                    }
                });

            }
            FgSidebar.droppableEventIconHandling('contact');
        }
        ;
        /*Drag/Drop ends*/

        if (localStorage.getItem("submenu") != '')
        {
            var activeMenu = '#' + localStorage.getItem("submenu");
            //$("#tcount").html($(activeMenu).find(".badge").text());
            var activeMenuCnt = ($(activeMenu).find(".badge").length > 0) ? $(activeMenu).find(".badge").text() : $("#fcount").text();
            // $("#tcount").html(activeMenuCnt);
        }

        if (!$.isEmptyObject(fc)) {
            fc.fnRedrawLayout();
        }
        //stop the pageloading process
        setTimeout(function () {
            FgUtility.stopPageLoading();}, 200);

        $(".chk_cnt").html('');
        //show default menu
        $('#fgdropmenu').html($("#fgdefaultMenu").html());
        $(".fgContactdrop .fa").removeClass('fa-users').addClass('fa-bars');
        //Dynamic menu display : It should load only after contactlist
        if ($("#fg-dev-dynamic-menu").length > 0) {
            //FgSidebar.processDynamicMenuDisplay();
        }
        // Checking whether fedmemeber /subfed req role members are missing.

        //Dynamic menu display : It should load only after contactlist

        if ($("#inlineEditContact").length > 0 && $('#inlineEditContact').is(':checked')) {
            var postUrl = $('#inlineEditContact').attr('inlineedit-post-url');
            $('.inline-editable').editable({
                emptytext: '-',
                autotext: 'never',
            });
            var inlineData = {"CF" : jsonData['CF']['entry']};
            if (jsonData.hasOwnProperty('FM')) {
                inlineData["FM"] = jsonData['FM']['entry'];
            }
            if (jsonData.hasOwnProperty('CM')) {
                inlineData["CM"] = jsonData['CM']['entry'];
            }
            inlineEdit.init({
                element: '.inline-editable',
                postUrl: postUrl,
                data: inlineData,
                callback: function (data) {
                    var _this = data,
                            _thistD = $(_this).parent('td'),
                            index = _thistD.index(),
                            parentWidth = $('.dataTables_scrollHeadInner th').eq(index).innerWidth();
                    _thistD.css({'width': parentWidth});
                    _this.css({'width': parentWidth - 10});
                }
            })
            //tab key edit
            $('body').off('keyup', 'span.inline-editable');
            $('body').on('keyup', 'span.inline-editable', function (e) {
                if (e.which == 13) {
                    $(this).trigger('click');
                }
            });

        }
    };

    opt.fnServerParams = function (aoData) {

    };


    opt.fnInitComplete = function () {
        var leftColumnCount = 2;

        if (typeof contactType != 'undefined' && (contactType == 'archive' || contactType == 'formerfederationmember')) {
            leftColumnCount = 3;
        }
        setTimeout(function () {
            FgCheckBoxClick.init('dataTable');
        }, 200);
        if ($(this).hasClass("dataTable-fixed")) {
            FgCommon.generateFixedColumn(oTable, leftColumnCount);
        }
        //for show the pop over functionality in the dataTable

        var attr = $(this).attr('dataTable-popover');

// For some browsers, `attr` is undefined; for others,
// `attr` is false.  Check for both.
        if (typeof attr !== undefined && attr !== false) {

            FgPopOver.init(".fgPopovers", true, false);
            FgPopOver.init(".fg-dev-Popovers", true);
        }

        // For change the position of the no.of records per page selection drop down box

        var replaceid = instData.attr('data_row_change');
        if (typeof replaceid !== "undefined" && replaceid !== "") {
            //for change the position
            var tableid = instData.attr('data-table-name');
            $("." + tableid + "_length").detach().prependTo("#" + replaceid);
            //add our own classes to the selectbox
            $("." + tableid + "_length").find('select').addClass('form-control cl-bs-select');
            $("." + tableid + "_length").find('select').select2();
        }
        if (!$.isEmptyObject(oTable)) {
            var api = oTable.api();
            var state = api.state.loaded();
            if (!$.isEmptyObject(state)) {
                var currentPage = state.start / state.length
                api.page(currentPage);
            }
        }
        ;
    }
    if (instData.hasClass("dataTable-initialSort")) {

        var str = instData.attr('data-sort');
        var res = str.split("#");
        opt.order = [[res[0], res[1]]];

    }
    /** DATABASE INITIALIZING AREA **/
    //block the error/warning pop up block
    $.fn.dataTable.ext.errMode = 'none';
    // initialize datatable with error event handling code
    oTable = instData.on('error.dt', function (e, settings, data) {
        window.location.reload();
    }).dataTable(opt);

  //   oTable = instData.dataTable(opt);

    //For change the search box field
    $("#searchbox").on("keyup", function () {
        var searchVal = this.value;
        setDelay(function(){
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

    var hideColumn = instData.attr('dataTable-column-hide');

    if (typeof hideColumn !== 'undefined' && hideColumn !== false) {
        oTable.api().column(hideColumn).visible(false);
    }

    var tableid = instData.attr('data-table-name');
    instData.on( 'length.dt', function ( e, settings, len ) {
        FgUtility.startPageLoading();
    } );

    instData.css("width", '100%');
    $('.dataTables_filter input').attr("placeholder", datatabletranslations['data_Search']);

    $('body').on('click', '#inlineEditContact', function () {
        FgUtility.startPageLoading();
        oTable.fnDraw();
       
    });
    if (instData.hasClass("dataTable-columnfilter")) {
        oTable.columnFilter({
            "sPlaceHolder": "thead:after",
            aoColumns: [
                {type: "text"},
                {type: "date-range"},
                {type: "date-range"}
            ]
        });
    }
    if (instData.hasClass("dataTable-grouping")) {
        var rowOpt = {};

        if (instData.attr("data-grouping") == 'expandable') {
            rowOpt.bExpandableGrouping = true;
        }
        oTable.rowGrouping(rowOpt);
    }

    $("#run").change(function () {
        oTable.fnDraw();
    });
    return oTable;
}

//Function for handling client side filtering of Log diplay
//Start date, End date and kind select box
function filter_submit(tableId) {
    $('#' + tableId).parent().find('div.date').each(function () {
        $(this).datepicker({autoclose: true}).change(function () {
            var type_Id = $(this).find('input').attr("data-event");
            clientsideFiltering(type_Id, ', ');
        });
    });

}

//Function for handling client side filtering of Log diplay
//Start date, End date
function logDateFilterSubmit(divId) {
    $('#' + divId).children().find('div.date').each(function () {
        $(this).datepicker({autoclose: true}).change(function () {
            var type_Id = $(this).find('input').attr("data-event");
            var tab = $(this).find('input').attr("data-tab");
            if (tab == "membership" || tab == "fed_membership") {
                clientsideFilteringMembership(type_Id, " ");
            } else {
                clientsideFiltering(type_Id, " ");
            }
        });
    });
}

//Function for filtering through the Datatable
function clientsideFiltering(type_Id, dateSeperator) {
    $.fn.dataTable.ext.afnFiltering.push(
            function (settings, data, dataIndex) {
                if (settings.nTable.id != 'log_display_' + type_Id) {
                    return true;
                }
                var searchData = $("#log_filter_type_" + type_Id).val();

                var hasSrchData = false;
                var date = data[0]; //date value of current record
                var kind = data[1]; //kind value of current record
                var startdate = $("#filter_start_date_" + type_Id).val() != '' ? $("#filter_start_date_" + type_Id).val() : '';
                var enddate = $("#filter_end_date_" + type_Id).val() != '' ? $("#filter_end_date_" + type_Id).val() : '';
                if (typeof dateSeperator === "undefined") {
                    dateSeperator = ' ';
                }
                if ((startdate != '') || (enddate != '')) {
                    var error = false;
                    var div = 'log_date_error_' + type_Id;
                    error = FgUtility.validateDate($("#filter_start_date_" + type_Id).val(), $("#filter_end_date_" + type_Id).val(), div);

                    if (error) {
                        return false;
                    } else {
                        $('#log_date_error_' + type_Id).css('display', 'none');
                        if (startdate != '')
                            var startdateTimestamp = moment(startdate, FgLocaleSettingsData.momentDateFormat).format('x')
                        else
                            var startdateTimestamp = 0;

                        if (enddate != '')
                            var enddateTimestamp = moment(enddate, FgLocaleSettingsData.momentDateFormat).format('x')
                        else
                            var enddateTimestamp = 0;

                        var currentRowTimestamp = moment(date, FgLocaleSettingsData.momentDateFormat).format('x');
                        var show = false;
                        if (startdateTimestamp > 0 && enddateTimestamp > 0) {
                            if (currentRowTimestamp >= startdateTimestamp && currentRowTimestamp <= enddateTimestamp)
                                show = true;
                        }
                        else if (startdateTimestamp > 0) {
                            if (currentRowTimestamp >= startdateTimestamp)
                                show = true;
                        }
                        else if (enddateTimestamp > 0) {
                            if (currentRowTimestamp <= enddateTimestamp)
                                show = true;
                        }
                        if (show)
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
        $.each(searchData, function (keyVal, searchVal) {
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

//Function for filtering through the Datatable
function clientsideFilteringMembership(type_Id) {
    $.fn.dataTable.ext.afnFiltering.push(
            function (settings, data, dataIndex) {
                if (settings.nTable.id != 'log_display_' + type_Id) {
                    return true;
                }
                var dateFrom = data[0]; //from value of current record
                var dateTo = data[1]; //to value of current record
                dateFrom = $(dateFrom).text();
                dateTo = $(dateTo).text();
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
                        if (startdate != '')
                            var startdateTimestamp = moment(startdate, FgLocaleSettingsData.momentDateFormat).format('x')
                        else
                            var startdateTimestamp = 0;

                        if (enddate != '')
                            var enddateTimestamp = moment(enddate, FgLocaleSettingsData.momentDateFormat).format('x')
                        else
                            var enddateTimestamp = 0;

                        var currentRowTimestamp = moment(date, FgLocaleSettingsData.momentDateFormat).format('x');
                        var show = false;
                        if (startdateTimestamp > 0 && enddateTimestamp > 0) {
                            if (currentRowTimestamp >= startdateTimestamp && currentRowTimestamp <= enddateTimestamp)
                                show = true;
                        }
                        else if (startdateTimestamp > 0) {
                            if (currentRowTimestamp >= startdateTimestamp)
                                show = true;
                        }
                        else if (enddateTimestamp > 0) {
                            if (currentRowTimestamp <= enddateTimestamp)
                                show = true;
                        }
                        if (show)
                        {
                            return true;
                        } else {
                            return false;
                        }
                    }
                } else {
                    return true;
                }
            }
    );
    $('#log_display_' + type_Id).dataTable().api().draw();
}

//for create the mouse over effect on the both fixed column and normal table
$(document).on({
    mouseenter: function () {
        trIndex = $(this).index() + 1;
        $(".DTFC_ScrollWrapper .dataTable").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                $(this).find("td").addClass("fghover");
                $(this).find("td").addClass('fg-dev-td-hover');
            });
        });
        $(".hover-edit").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                $(this).find("td").addClass("fghover");
                $(this).find("td").addClass('fg-dev-td-hover');
            });
        });
    },
    mouseleave: function () {
        trIndex = $(this).index() + 1;
        $(".DTFC_ScrollWrapper .dataTable").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                $(this).find("td").removeClass("fghover");
                // $(this).find("td").css("background", "#fff");
            });
        });
        $(".hover-edit").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                $(this).find("td").removeClass("fghover");
                // $(this).find("td").css("background", "#fff");
            });
        });
        $("table.dataTable").find('.fg-dev-td-hover').removeClass('fg-dev-td-hover');
    },
    drop: function () {
        trIndex = $(this).index() + 1;
        var selCount = $(".DTFC_LeftBodyWrapper input.dataClass:checked").length;
        $("table.dataTable").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                if (selCount <= 1) {
                    $(this).find("td").addClass("fg-droppable-color");
                }
            });
        });
        $("table.dataTable").removeClass('fg-dev-drag-active');
        $("table.dataTable").find('.fg-dev-td-hover').removeClass('fg-dev-td-hover');

    },
    drag: function (e) {
        //$("table.dataTable").not('.fg-dev-drag-active').addClass('fg-dev-drag-active');
        $("body").addClass('fg-dev-drag-active');
    },
}, ".dataTables_wrapper tr");


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
    } else if (type == 'membershipassignment') {
        if (typeof membershipAssignmentPath !== 'undefined') {
            requestPath = membershipAssignmentPath;
        }
    } else if (type == 'archive') {
        if (typeof archivePath !== 'undefined') {
            requestPath = archivePath;
        }
    } else if (type == 'delete') {
        if (typeof permanentDeletePath !== 'undefined') {
            requestPath = permanentDeletePath;
        }
    } else if (type == 'reactivate') {
        requestPath = params.urlpath;
    } else if (type == 'formerfedmember-delete') {
        requestPath = params.urlpath;
    } else if (type == 'templateduplicate' || type == 'templatedelete') {
        if (typeof duplicateDeleteTemplatePath !== 'undefined') {
            requestPath = duplicateDeleteTemplatePath;
        }
    } else if (type == 'subscriberdelete') {
        if (typeof deleteSubscriberPath !== 'undefined') {
            requestPath = deleteSubscriberPath;
        }
    } else if (type == 'subscriberexport') {
        if (typeof exportSubscribers !== 'undefined') {
            requestPath = exportSubscribers;
        }
    } else if ((type == 'documentdelete') || (type == 'documentVersionDelete') || (type == 'documentOldVersionDelete')) {
        if (typeof deletedocumentPath !== 'undefined') {
            requestPath = deletedocumentPath;
        }
    } else if (type == 'editdocumentdelete') {
        if (typeof editdocumentdeletePath !== 'undefined') {
            requestPath = editdocumentdeletePath;
        }
    } else if (type == 'stopservice') {
        if (typeof stopservicePath !== 'undefined') {
            requestPath = stopservicePath;
        }
    } else if (type == 'serviceexportcsv') {
        if (typeof serviceexportcsvPath !== 'undefined') {
            requestPath = serviceexportcsvPath;
        }
    } else if (type == 'sa_export_csv') {
        if (typeof analysisexportcsvPath !== 'undefined') {
            requestPath = analysisexportcsvPath;
        }
    } else if ((type == 'confirmchanges') || (type == 'discardchanges')) {
        requestPath = params.urlpath;
    } else if ((type == 'confirmConfirmations') || (type == 'discardConfirmations')) {
        requestPath = params.urlpath;
    } else if (type == 'newcontactdetail') {
        requestPath = params.urlpath;
    } else if (type == 'contact_overview') {
        requestPath = params.path;
    } else if (type=='contactProfilePreviewPopup') {
        requestPath = params.path;
    } else if(type == 'addexistingfedmember'){
        requestPath = params.path;
    } else if(type == 'quit_membership'||type=='assign_membership'||type== 'assign_fedmembership'||type=='quit_fed_membership'){
        requestPath = params.path;
    }

    if (requestPath != '') {
        $('#popup_contents').html('');
        var proceedToRequestedpath = true;
        if (type == 'connection') {
            var connParams = params['contactData'].split('*#*##*#*');
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

            $.get(requestPath, params, function (data) {
                if (type == 'reactivate') {
                    if (contactType == "archivedsponsor") {
                        sponsorTable.api().draw();
                    } else {
                        oTable.api().draw();
                    }

                    if (data.status == 'FAILURE') {
                        FgUtility.showToastr(data.flash, 'warning');
                    } else {
                        FgUtility.showToastr(data.flash, 'success');
                        FgCountUpdate.updateTopNav('add', 'contact', 'active', data.totalCount);
                        if (contactType == "archivedsponsor") {
                            FgCountUpdate.updateTopNav('remove', 'sponsor', 'archived', data.totalCount);
                            FgCountUpdate.updateTopNav('add', 'sponsor', 'active', data.totalCount);
                        } else {
                            FgCountUpdate.updateTopNav('remove', 'contact', 'archive', data.totalCount);
                        }
                    }

                } else if (type == 'formerfedmember-delete') {
                    oTable.api().draw();
                    if (data.status == 'FAILURE') {
                        FgUtility.showToastr(data.flash, 'warning');
                    } else {
                        FgUtility.showToastr(data.flash, 'success');
                        $('#fg-dev-formerfedmem-count').html(data.activeFedmemberCount);

                    }

                } else if (data.status == 'FAILURE') {
                    FgUtility.showToastr(data.flash, 'warning');
                } else {
                    $('#popup_contents').html(data);
                    $('#popup').modal('show');
                }
            });
        }
    }
}

(function ($) {
    $.fn.liveDroppable = function (opts) {
        //Used for enabling drag-drop functionality and related icon handling in sidebar menu
        if (opts['hoverClass'] === 'fg-sidebar-hover') {
            FgSidebar.newMenuOpts['draggable'] = opts;
        }
        if (opts['hoverClass'] === 'fg-sidebar-not-allowed') {
            FgSidebar.newMenuOpts['nondraggable'] = opts;
        }
        if (!$(this).data("init")) {
            $(this).data("init", true).droppable(opts);
        }
    };

    $(".fg_dev_filter_show").on('click', function () {
        $('.filter-alert').toggle('fast', function () {
            if ($(this).is(":hidden")) {
                localStorage.setItem(filterDisplayFlagStorage, 0);
                $("#filterFlag").attr('checked', false);
            } else {
                localStorage.setItem(filterDisplayFlagStorage, 1);
                $("#filterFlag").attr('checked', true);
            }
        })
        setTimeout(function () {
            $.uniform.update('#filterFlag');
        }, 500)
    })

    $(".fg_dev_filter_hide").on('click', function () {
        localStorage.setItem(filterDisplayFlagStorage, 1);
        $(".fg_dev_filter_hide").addClass('fg_dev_filter_show');
        $(".fg_dev_filter_show").removeClass('fg_dev_filter_hide');
        $('.filter-alert').show();
        $("#filterFlag").attr('checked', true);
        jQuery.uniform.update('#filterFlag');
    })

}(jQuery));

function manipulateColumnFields(json)
{
    var inlineEditFlag = 0;
    if ($("#inlineEditContact").length > 0 && $('#inlineEditContact').is(':checked')) {
        inlineEditFlag = 1;
    }
    for (var i = 0, ien = json.aaData.length; i < ien; i++) {

        for (var j = 0, jen = json.aaDataType.length; j < jen; j++) {
            var title = json.aaDataType[j]['title'];
            switch (json.aaDataType[j]['type']) {
                case "email":
                    if (inlineEditFlag == 0) {
                        json.aaData[i][title] = json.aaData[i][title] ? '<a  href="mailto:' + json.aaData[i][title] + '" target="_self">' + json.aaData[i][title] + '</a>' : '-';
                    } else {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
                case "imageupload":
                    var uploadPath = json.aaDataType[j]['uploadPath']+'/';
                    json.aaData[i][title] = json.aaData[i][title] ? ' <i class="fg-custom-popovers" data-trigger="hover" data-placement="bottom" data-content="<img src=\'' + uploadPath +json.aaData[i][title] + '\'/>" data-original-title="">' + json.aaData[i][title] + '</i> ' : '-';
                    break;
                case "fileupload":
                    var uploadPath = json.aaDataType[j]['uploadPath']+'/';
                    json.aaData[i][title] = json.aaData[i][title] ? '<a  href="' + uploadPath + json.aaData[i][title] + '" target="_blank">' + json.aaData[i][title] + '</a>' : '-';
                    break;
                case "url":
                    if (inlineEditFlag == 0) {
                        json.aaData[i][title] = json.aaData[i][title] ? '<a href="' + json.aaData[i][title] + '" target="_blank">' + json.aaData[i][title] + '</a>' : '-';
                    } else {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
                case "Gnotes":
                    var url = json.aaData[i]["Gnotes_url"];

                    json.aaData[i][title] = (json.aaData[i][title] > 0) ? '<a href="' + url + '" target="_blank">' + json.aaData[i][title] + '</a>' : 0;
                    break;
                case "Gdocuments":
                    var url = json.aaData[i]["Gdocuments_url"];

                    json.aaData[i][title] = (json.aaData[i][title] > 0) ? '<a href="' + url + '" target="_blank">' + json.aaData[i][title] + '</a>' : 0;
                    break;

                case "multiline":
                case "singleline":
                    if (inlineEditFlag == 0) {
                        if (!(_.isNull(json.aaData[i][title])) && (json.aaData[i][title].length > 50)) {
                            var lineBreak = json.aaData[i][title].match(/.{1,50}/g);
                            if (_.size(lineBreak) > 1) {
                                json.aaData[i][title] = '<i class="fg-custom-popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + nl2br(json.aaData[i][title]) + '" data-original-title="">' + lineBreak[0] + ' &hellip;</i>';
                            }
                        }
                    } else {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
                case "CNhousehold_contact":
                    var householdArray = json.aaData[i]["CNhousehold_contact_jsonarray"];
                    if ($.isArray(householdArray)) {
                        var iCount = 0;
                        var textContact = '';
                        var firstId;
                        var firstcontact;
                        var connectionCount = _.size(householdArray);
                        var stringUrl = json.aaData[i]["CNhousehold_contact_url"];
                        var firstListUrl;
                        $.each(householdArray, function (index, value) {
                            var splitValues = value.split("|");
                            url = stringUrl.replace("%23contactId", splitValues[1]);
                            if (iCount == 0) {
                                firstcontact = splitValues[0];
                                firstId = splitValues[1];
                                firstListUrl = url;
                                iCount++;
                            } else {
                                textContact += "<a href='" + url + "'>" + splitValues[0] + "</a><br/>";
                            }

                        })

                        if (connectionCount > 1) {
                            connectionCount = connectionCount - 1;
                            json.aaData[i][title] = '<a href="' + firstListUrl + '">' + firstcontact + '</a>&nbsp; <i class="fg-custom-popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + textContact + '" >' + connectionCount + ' ' + datatabletranslations['More'] + ' </i>';
                        } else if (connectionCount == 1) {
                            json.aaData[i][title] = '<a href="' + firstListUrl + '">' + firstcontact + '</a>';
                        }
                    }
                    break;

                case "contactname"  :
                    var icons = '';
                    var editIcons = (contactType == 'formerfederationmember') ? '' : '&nbsp;<a href="' + json.aaData[i]['edit_url'] + '" class="fg-tableimg-hide fg-edit-contact-ico"><i class="fa fa-pencil-square-o fg-pencil-square-o"></i></a>';
                    var classes = "inactive";
                    var sponsorIcon = '';
                    var formerfederationClass = (contactType == 'formerfederationmember') ? ' fg-dev-stay-icon' : '';
                    var fedImage = (json.aaData[i]['fedmembershipType'] > 0 && contactType == 'contact') ? "&nbsp;<img class ='fg-global-fed-icon' src='"+fedIcon[fedclubId]+"'/>" : '';
                    var approveIcon = (json.aaData[i]['fedmembershipApprove'] > 0) ? "&nbsp;<i class='fg-dev-Popovers fa fa-clock-o' data-trigger='hover' data-placement='bottom' data-content='" + fedmemConfirmTootipMsg + "' > </i>" : '';
                    switch (contactType) {

                        case "contact":
                        case "archive":
                        case "formerfederationmember":
                           
                            var sponsorIcon = (json.aaData[i]['SponsorIcon'] === true) ? "&nbsp;<i class='fa fa-star fg-star inactive'></i>" : '';
                            if (json.aaData[i]['Ismember'] === '') {
                                classes = "inactive";
                            } else if (json.aaData[i]['clubmembershipType'] > 0 && (CLParams.type == 'sub_federation_club' || CLParams.type == 'standard_club' || CLParams.type == 'federation_club')) {
                                classes = "active";
                            }
                            if (json.aaData[i]['Iscompany'] === '1') {
                                icons += '<i class="fa fa-building-o fg-building ' + classes + formerfederationClass + '"></i>&nbsp;';
                            } else if (json.aaData[i]['Gender'].toLowerCase() === 'female') {
                                icons += '<i class="fa fa-female fg-female ' + classes + formerfederationClass + '"></i>&nbsp;';
                            } else if (json.aaData[i]['Gender'].toLowerCase() === 'male') {
                                icons += '<i class="fa fa-male fg-male ' + classes + formerfederationClass + '"></i>&nbsp;';
                            }
                            if (i === 0) {
                                json.aaData[i][title] = "<div class='fg-contact-wrap'> " + icons + "<input type='hidden' id='filterCount' value='0'><a class=' fg-dev-contactname' href='" + json.aaData[i]['click_url'] + " '>" + json.aaData[i][title] + "</a>" + sponsorIcon + editIcons + fedImage + approveIcon + "</div>";
                            } else {
                                json.aaData[i][title] = "<div class='fg-contact-wrap'> " + icons + "<a class='fg-dev-contactname' href='" + json.aaData[i]['click_url'] + "'>" + json.aaData[i][title] + "</a>" + sponsorIcon + editIcons + fedImage + approveIcon + "</div>";
                            }


                            break;

                    }
                    break;
                case "edit"  :
                    var dragArrow = (contactType == 'contact') ? '<i class="fa fg-sort" data-toggle="tooltip"></i>' : '<i class="fa fg-sort"></i>';
                    var subfedId = (json.aaData[i]['subfed_contact_id']=='' || json.aaData[i]['subfed_contact_id']==null) ? "":json.aaData[i]['subfed_contact_id'];
                    json.aaData[i][title] = '<div class="fg-td-wrap">' + dragArrow + ' <input class="dataClass" type="checkbox" id=' + json.aaData[i]['id'] + ' name="check" data-iscompany =' + json.aaData[i]['Iscompany'] + ' data-contactclub ="' + json.aaData[i]['createdclubid'] + '" data-fedmember-approve="' + json.aaData[i]['fedmembershipApprove'] + '"   data-club-membership_id ="' + json.aaData[i]['clubMembershipId'] + '" data-fed-membership-id ="' + json.aaData[i]['fedMembershipId'] + '" value="0"  data-fed-contactId='+json.aaData[i]['fed_contact_id']+' data-subfed-contactId ="'+subfedId+'"></div>';


                    break;
                case "select":
                    if (inlineEditFlag == 1) {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
                case "CMfirst_joining_date":
                case "CMjoining_date":
                case "CMleaving_date":
                case "FMfirst_joining_date":
                case "FMjoining_date":
                case "FMleaving_date":
                    if (inlineEditFlag == 1) {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
                case "Function":
                    if (inlineEditFlag == 1) {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
                case "Gprofile_company_pic":
                    if (json.aaData[i]['Gprofile_company_pic'] != "") {
                        if(json.aaData[i]['Iscompany'] != 1) {
                            var imgDiv = '<div class="popover-content hide" ><div class="fg-profile-img-blk90 fg-round-img" style="background-image:url(\''+json.aaData[i]['Gprofile_company_pic']+'\')" ></div></div>';
                        } else {
                            var imgDiv = '<div class="popover-content hide" ><div class="fg-profile-img-blk-C90" ><img src="' + json.aaData[i]['Gprofile_company_pic'] + '"></div></div>';
                        }
                        json.aaData[i][title] = '<i class="fg-custom-popovers fa fa-image" data-trigger="hover" data-placement="bottom" data-popover-content=".popover-content" >'+imgDiv+'</i>';
                    }
                    break;
                case "fed_membership_category":
                    if (json.aaData[i]['Gfed_membership_category'] != "") {
                        var approveIcon = (json.aaData[i]['fedmembershipApprove'] > 0) ? "&nbsp;<i class='fg-dev-Popovers fa fa-clock-o' data-trigger='hover' data-placement='bottom' data-content='" + fedmemConfirmTootipMsg + "'></i>" : '';
                        json.aaData[i]['Gfed_membership_category'] = json.aaData[i]['Gfed_membership_category'] + approveIcon;
                    }
                    break;
                case "FIclub":
                    if (json.aaData[i]['FIclub'] != "" && typeof json.aaData[i]['FIclub'] != "undefined") {
                        var myarr = json.aaData[i]['FIclub'].split(",");
                        for(var loc = 0; loc < myarr.length; loc++){
                            if(myarr.length == 1){
                                myarr[loc] = myarr[loc].replace('#mainclub#','');
                            }else{
                                myarr[loc] = myarr[loc].replace('#mainclub#',' <i class="fa  fa-star text-yellow"></i>');
                            }
                        }
                        json.aaData[i]['FIclub'] = myarr.join();
                    }
                break;
                default:
                    if (inlineEditFlag == 1) {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
            }

        }
    }
}
function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function getInlineEditParameters(contactParams, attrParams)
{
    var ret = '';
    var dataClass = '';
    var attributes = '';
    var title = attrParams.title;
    if ($.inArray(attrParams.type, ["CMfirst_joining_date", "CMjoining_date", "CMleaving_date", "FMfirst_joining_date", "FMjoining_date", "FMleaving_date"]) !== -1) {
        if ((typeof (contactParams[title]) !== 'undefined') && (contactParams[title] != '-')) {
            var isClubEditable = (($.inArray(attrParams.clubType, ['sub_federation_club', 'federation_club', 'standard_club']) !== -1) && ($.inArray(attrParams.type, ["CMfirst_joining_date", "CMjoining_date", "CMleaving_date"]) !== -1)) ? 1 : 0;
            var isFedEditable = (($.inArray(attrParams.clubType, ['sub_federation', 'federation']) !== -1) && ($.inArray(attrParams.type, ["FMfirst_joining_date", "FMjoining_date", "FMleaving_date"]) !== -1)) ? 1 : 0;
            if (isClubEditable || isFedEditable)  {
                ret = '<span class="inline-editable" data-edit-row="' + contactParams.id + '" data-edit-col="' + attrParams.type + '" data-edit-val="' + contactParams[title] + '" data-tabindex=' + tabIndex + ' tabindex=' + tabIndex + '>' + contactParams[title] + '</span>';
            } else {
                ret = contactParams[title];
            }
        } else {
            ret = '-';
        }
    } else if (attrParams.type == "Function") {
        if ((typeof (contactParams[title]) !== 'undefined') && (contactParams[title] != '-')) {
            ret = '<span class="inline-editable" data-edit-type="select23" data-edit-row="' + contactParams.id + '" data-edit-col="' + attrParams.type + '" data-edit-val="' + contactParams[title] + '" data-tabindex=' + tabIndex + ' tabindex=' + tabIndex + '>' + contactParams[title] + '</span>';
        }
    } else {
        switch (contactParams.Iscompany) {
            case "0":
                if (attrParams.is_personal == 1) {
                    dataClass = 'inline-editable';
                }
                break;
            case "1":
                if (contactParams.hasMainContact == 1 && contactParams.compDefContact == '') {
                    if (attrParams.is_company == 1) {
                        dataClass = 'inline-editable';
                    }
                } else {
                    if (attrParams.is_company == 1 && attrParams.category_id != 1) {
                        dataClass = 'inline-editable';
                    }
                }
                break;
            default:
                break;
        }
        if (contactParams.sameInvoiceAddress == 1) {
            if ((attrParams.category_id == 137) && (attrParams.addres_type == "invoice") && (attrParams.address_id != null)) {
                dataClass = '';
            }
        }
        if (attrParams.is_system_field == 0 && attrParams.is_editable == 0) {
            dataClass = '';
        }
        var dataVal = ((typeof (contactParams[title]) !== 'undefined') && (contactParams[title] != '') && (contactParams[title] != '-')) ? contactParams[title] : '';
        if (dataClass == 'inline-editable') {
            attributes = 'class="inline-editable" ';
            if (contactParams.id) {
                attributes += 'data-edit-row="' + contactParams.id + '" ';
            }
            if (attrParams.attrId) {
                attributes += 'data-edit-col="' + attrParams.attrId + '" ';
            }
            if (typeof (attrParams.originalTitle) !== 'undefined') {
                ret = ((typeof (contactParams[attrParams.originalTitle]) !== 'undefined') && (contactParams[attrParams.originalTitle] != '') && (contactParams[attrParams.originalTitle] != '-')) ? contactParams[attrParams.originalTitle] : '';
                attributes += 'data-edit-val="' + ret + '" ';
            } else {
                dataVal = ((attrParams.type == 'select') || (attrParams.type == 'radio')) ? ((dataVal == ' ' || dataVal == '-') ? '' : dataVal) : dataVal;
                if ((attrParams.type == 'checkbox')) {
                    var dataValArr = dataVal.split(';');
                    dataVal = [];
                    for (var i = 0; i < dataValArr.length; i++) {
                        dataVal.push(" " + dataValArr[i]);
                    }
                }
                attributes += 'data-edit-val="' + dataVal + '" ';
            }
            if ((attributes != '') && (typeof attributes !== "undefined")) {
                ret = '<span ' + attributes + ' data-tabindex=' + tabIndex + ' tabindex=' + tabIndex + '>' + dataVal + '</span>';
            }
        } else {
            if (((attrParams.type == 'singleline') || (attrParams.type == 'multiline')) && (dataVal.length > 50)) {
                var lineBreak = dataVal.match(/.{1,50}/g);
                if (_.size(lineBreak) > 1) {
                    dataVal = '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom"  data-tabindex=' + tabIndex + ' data-content="' + nl2br(dataVal) + '" data-original-title="" tabindex=' + tabIndex + '>' + lineBreak[0] + ' &hellip;</i>';
                }
            }
            ret = dataVal;
        }
    }

    tabIndex++;
    return ret;
}
function countDisplay($this) {

    if ($("#filterCount").length > 0) {
        $("#fcount").html($this.fnRecordsTotal());
    }
    //initialy set total count value 
    if (FgSidebar.isFirstTime && (contactType == 'archive' || contactType =='formerfederationmember')) {
        FgSidebar.isFirstTime=false;
        $("#tcount").html($('li.fg-header-nav-active .fg-header-nav-active').find('span.badge').eq(0).text());
    }

}

