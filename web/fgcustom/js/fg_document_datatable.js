
var documentTable = '';
var FgDocumentTable = function() {

    return {
        //main function to initiate the module
        init: function() {

            if (!jQuery().dataTable) {
                return;
            }
            // dataTables
            if ($('.documentdataTable').length > 0) {
                $('.documentdataTable').each(function() {
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
        initid: function(tableId, filterFlag) {

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
            return documentTable;

        }
    };
}();

function fgDataTableInit() {
    var opt = FgCommon.setinitialOpt();
    return opt;
}
function fgDataTable(instData, data) {

    var opt = fgDataTableInit();

    if (instData.hasClass("dataTable-ajax")) {
        opt.ajax = {
            "url": instData.attr('data-ajax-path'),
            "data": function(parameter) {
                var documentparameters = instData.attr('document-parameter');
                //for setting the document listing parameters
                if (typeof documentparameters !== 'undefined' && documentparameters !== false) {
                    var tablecolumnName = localStorage.getItem(tableSettingValueStorage);
                    parameter.filterdata = filterdata;
                    parameter.tableField = tablecolumnName;
                }

            },
            "type": "POST",
            "dataSrc": function(json) {
                if (docType == 'CLUB') {
                    manipulateClubFields(json);
                } else if (docType == 'WORKGROUP') {
                    manipulateWorkgroupFields(json);
                } else if (docType == 'CONTACT') {
                    manipulateContactFields(json);
                } else if (docType == 'TEAM') {
                    manipulateTeamFields(json);
                }
                return json.aaData;
            }
        };
        var serverSideaprocess = instData.attr('data_serversideProcess');
        //for setting the serverside process
        if (typeof serverSideaprocess !== 'undefined' && serverSideaprocess !== false) {
            opt.serverSide = true;
        } else {
            opt.serverSide = false;
        }
        opt.processing = true;
    }
    //set table column
    if (instData.hasClass("dataTable-ajaxHeader")) {
        opt.aoColumns = $.parseJSON(localStorage.getItem(tableColumnTitleStorage));

    }
    var columnDeflag = instData.attr('data-column-def');

    if (typeof columnDeflag !== 'undefined' && columnDeflag !== false) {
        opt.columnDefs = columnDefs;
    }

    var xwidth = instData.attr('xWidth')
    opt.sScrollX = xwidth + "%";
    opt.sScrollXInner = xwidth + "%";
    opt.scrollCollapse = true;
    if (instData.hasClass("dataTable-y-height")) {
        var yheight = FgCommon.getWindowHeight(100);
    } else {
        var yheight = FgCommon.getWindowHeight(418);
    }
    opt.scrollY = yheight + "px";

    opt.stateLoadCallback = function(settings) {
        var stringified = localStorage.getItem('DataTables_' + window.location.pathname + window.location.search)
        var oData = JSON.parse(stringified || null);
        if (oData && settings.fnRecordsTotal() < oData.start) {
            oData.start = 0;
        }
        if (oData) {
            $("#searchbox").val(oData.search.search);
        }

        return oData;
    }
    opt.stateSaveCallback = function(settings, data) {
        localStorage.setItem('DataTables_' + window.location.pathname + window.location.search, JSON.stringify(data));
    }
    opt.deferRender = true;
    opt.paging = true;
    opt.lengthChange = true;

    opt.fnRowCallback = function(nRow, aData, iDataIndex) {

        //give - value to the null
        $('td', nRow).each(function(index, value) {
            if ($(this).html() == '' || $(this).html() == null) {
                $(this).html("-");
            }
        });
    };

    if (instData.hasClass("dataTable-widthResize")) {
        var totalColumn = (_.size($.parseJSON(localStorage.getItem(tableColumnTitleStorage)))) - 1;
        opt.columnDefs = [{"width": "100%", "targets": totalColumn}];
    }

    opt.fnHeaderCallback = function(nHead, aData, iStart, iEnd, aiDisplay) {

    };
    opt.fnDrawCallback = function(oSettings) {

        if (instData.hasClass("doc-assignments")) {
            $(".dataClass").uniform();
        }
        //checkbox convert to uniform model
         FgCommon.checkboxpluginInit();
// For setting the count in the top of the datatable

        if (instData.hasClass("data-filter-count")) {
            if ($('.fa-filter:visible').length > 0)
            {
                $("#fcount").html(this.fnSettings().fnRecordsTotal());
            } else if ($.isNumeric(totalCount) && totalCount >= 0 && $("#filterCount").length > 0) {
                $("#tcount").html(totalCount);
                $("#fcount").html(this.fnSettings().fnRecordsTotal());
            } else if (!$.isNumeric(totalCount) && totalCount == '' && $("#filterCount").length > 0) {
                $("#fcount").html(this.fnSettings().fnRecordsTotal())
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
                $("#tcount").html(0)
                $("#fcount").html(0)
            }
        }
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
                    var count;
                    if ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length > 0) {
                        count = $(".DTFC_LeftBodyWrapper input.dataClass:checked").length;
                    } else {
                        count = 1;
                    }
                    return $("<div class='ui-widget-header'><span class='fg-drag-count'>" + count + "</span></div>");
                },
                containment: "body"
            });
            
            FgSidebar.droppableEventIconHandling('document');                      
        }
        ;
        /*Drag/Drop ends*/


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


    };

    opt.fnServerParams = function(aoData) {

    };


    opt.fnInitComplete = function() {
        setTimeout(function () {
            FgCheckBoxClick.init('documentdataTable');
        }, 200);
        if ($(this).hasClass("dataTable-fixed")) {
            FgCommon.generateFixedColumn(documentTable, 2);
        } else {
            $(".dataClass").uniform();
        }

        if (instData.hasClass("contain-add-doc-autocomplete")) {
            //listing page containing autocomplete field for add document ( club & contact documents)
            // to render the input field above pagination
            renderAddExistingDiv();
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
        if (replaceid != "") {
            //for change the position
            var tableid = instData.attr('data-table-name');
            $("#" + tableid + "_length").detach().prependTo("#" + replaceid);
            //add our own classes to the selectbox
            $("#" + tableid + "_length").find('select').addClass('form-control cl-bs-select');
            $("#" + tableid + "_length").find('select').select2();
        }
        if (!$.isEmptyObject(documentTable)) {
            var api = documentTable.api();
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
    //initialize datatable with error event handling code
    documentTable = instData.on('error.dt', function (e, settings, data) {
        window.location.reload();
    }).dataTable(opt);


    //For change the search box field
    $("#searchbox").on("keyup", function() {
        var searchVal = this.value;
        setDelay(function(){
            documentTable.api().search(searchVal).draw();
            if ($("#searchbox").val() != '') {
                $("#tcount").show();
                $("#fg-slash").show();
                //$(".fa-filter").show();
            } else {
                $("#tcount").hide();
                $("#fg-slash").hide();
                // $(".fa-filter").hide();
            }
            },500);
    });

    var hideColumn = instData.attr('dataTable-column-hide');

    if (typeof hideColumn !== 'undefined' && hideColumn !== false) {
        documentTable.api().column(hideColumn).visible(false);
    }
    instData.css("width", '100%');
    $('.dataTables_filter input').attr("placeholder", datatabletranslations['data_Search']);
    $("#check_all").click(function(e) {
        $('input', documentTable.fnGetNodes()).prop('checked', this.checked);
    });
    $('body').on('click', '#inlineEditContact', function() {
        FgUtility.startPageLoading();
        documentTable.fnDraw();
    });
    
    var tableid = instData.attr('data-table-name');
    $('#'+tableid).on( 'length.dt', function ( e, settings, len ) {
        FgUtility.startPageLoading();
    } );
    
    if (instData.hasClass("dataTable-grouping")) {
        var rowOpt = {};

        if (instData.attr("data-grouping") == 'expandable') {
            rowOpt.bExpandableGrouping = true;
        }
        documentTable.rowGrouping(rowOpt);
    }

    $("#run").change(function() {
        documentTable.fnDraw();
    });
    return documentTable;
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
    $(".fg_dev_filter_show").off('click');
    $(".fg_dev_filter_show").on('click', function() {
        $('.filter-alert').toggle('slow', function() {

            if ($(this).is(":hidden")) {
                localStorage.setItem(filterDisplayFlagStorage, 0);
                $("#filterFlag").attr('checked', false);
                $.uniform.update('#filterFlag');
            } else {
                localStorage.setItem(filterDisplayFlagStorage, 1);
                $("#filterFlag").attr('checked', true);
                $.uniform.update('#filterFlag');
            }
        })
        //$.uniform.update('#filterFlag');
        setTimeout(function() {
            $.uniform.update('#filterFlag');
        }, 500)
    })



}(jQuery));

function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function manipulateClubFields(json)
{

    var indexCount = json.start;
    for (var i = 0, ien = json.aaData.length; i < ien; i++) {
        for (var j = 0, jen = json.aaDataType.length; j < jen; j++) {

            var title = json.aaDataType[j]['title'];

            switch (json.aaDataType[j]['type']) {

                case "edit"  :

                    if (i == 0) {
                        json.aaData[i][title] = (json.aaData[i]['club_id'] == clubId) ? "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox' data-subcategoryId=" + json.aaData[i]['subcatId'] + " id=" + json.aaData[i]['documentId'] + " name='check'   value='0'  data_index=" + indexCount + "></div><input type='hidden' id='filterCount' value='0'><input type='hidden' id='totalCount' value='0'>" : "<div class='fg-td-wrap'> </div><input type='hidden' id='filterCount' value='0'><input type='hidden' id='totalCount' value='0'>";

                    } else {

                        json.aaData[i][title] = (json.aaData[i]['club_id'] == clubId) ? "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox' data-subcategoryId=" + json.aaData[i]['subcatId'] + " id=" + json.aaData[i]['documentId'] + " name='check'  value='0'  data_index=" + indexCount + "></div>" : "<div class='fg-td-wrap'> </div><input type='hidden' id='filterCount' value='0'><input type='hidden' id='totalCount' value='0'>";
                    }

                    break;
                case "CL_FO_DESCRIPTION"  :

                    if (json.aaData[i][title] != null && json.aaData[i][title].length > 50) {
                        var lineBreak = json.aaData[i][title].match(/.{1,50}/g);
                        if (_.size(lineBreak) > 1) {
                            json.aaData[i][title] = '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + nl2br(json.aaData[i][title]) + '" data-original-title="">' + lineBreak[0] + ' &hellip;</i>';
                        }
                    }
                    break;
                case "CL_FO_SIZE"  :
                    if (json.aaData[i][title] != null) {
                        var filesize = convertByteToMb(json.aaData[i][title]);

                        json.aaData[i][title] = convertByteToMb(json.aaData[i][title]);

                    }

                    break;
                case "CL_FO_VISIBLE_TO"  :
                    if (json.aaData[i][title] == 1) {
                        json.aaData[i][title] = '<div class="fg-static-on">' + datatabletranslations['On'] + '</div>';
                    } else {
                        json.aaData[i][title] = '<div class="fg-static-off">' + datatabletranslations['Off'] + '</div>';
                    }

                    break;
                case "CL_FO_ISPUBLIC"  :
                 
                    if (json.aaData[i][title] == 1) {
                        json.aaData[i][title] = '<div class="fg-static-on">' + datatabletranslations['On'] + '</div>';
                    } else {
                        json.aaData[i][title] = '<div class="fg-static-off">' + datatabletranslations['Off'] + '</div>';
                    }

                    break;
                case "docname"  :
                    if (json.aaData[i][title] != null) {
                        json.aaData[i][title] = (json.aaData[i]['club_id'] == clubId) ? "<i class='fa " + json.aaData[i]['docname_icon'] + " fg-datatable-icon'></i><a class='fg-dev-docname' href='" + json.aaData[i]['docname_url'] + "' target='_blank'>" + json.aaData[i][title] + "</a>&nbsp;<a href='" + json.aaData[i]['edit_url'] + "' class='fg-tableimg-hide'><i class='fa fa-pencil-square-o fg-pencil-square-o'></i></a>" : "<i class='fa " + json.aaData[i]['docname_icon'] + " fg-datatable-icon'></i><a href='" + json.aaData[i]['docname_url'] + "' target='_blank'>" + json.aaData[i][title] + "</a><img src='" + json.aaData[i]['fedicon'] + "' class='fg-global-fed-icon'>";
                    }

                    break;
                case "CL_FO_DEPOSITED_WITH"  :

                    if (json.aaData[i][title] != null) {
                        var textContact = '';
                        var value = json.aaData[i][title];
                        var totalList = _.size(value.split("#"))
                        var splitValues = value.split("#", 10);
                        var isAllClub = false;
                        if (splitValues[0] === 'ALLC') {
                            isAllClub = true;
                            splitValues.shift();
                            allClub = splitValues[0].split('|@|');
                            splitValues.shift();
                        }
                        $.each(splitValues, function(index, value) {
                            if (_.size(splitValues) == 1) {
                                textContact += value;
                            } else {
                                textContact += value + "<br/>";
                            }
                        })

                        if (totalList > 10) {
                            textContact += "&nbsp;" + (totalList - 10) + "&nbsp;" + datatabletranslations['More'];
                        }
                        if (isAllClub) {
                            json.aaData[i][title] = (_.size(splitValues) > 0) ? allClub[0] + ',&nbsp; <i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + allClub[1] + ' </i>' : allClub[0];
                        } else {
                            json.aaData[i][title] = (_.size(splitValues) > 1) ? '<i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + totalList + '&nbsp;' + clubTerminology + ' </i>' : textContact;
                        }
                    }


                    break;
                case "CL_FO_DEPOSITED_WITH_FOR_ASSIGNED"  :

                    if (json.aaData[i][title] != null) {
                        var textContact = '';
                        var value = json.aaData[i][title];
                        var totalList = _.size(value.split("#"))
                        if (typeof totalList === 'undefined') {
                            totalList = 0;
                        }
                        var splitValues = value.split("#", 10);
                        var isAllClub = false;
                        if (splitValues[0] === 'ALLC') {
                            isAllClub = true;
                            splitValues.shift();
                            allClub = splitValues[0].split('|@|');
                            splitValues.shift();
                        }
                        $.each(splitValues, function(index, value) {
                            if (_.size(splitValues) == 1) {
                                textContact += value;
                            } else {
                                textContact += value + "<br/>";
                            }
                        })
                        if (totalList > 10) {
                            textContact += "&nbsp;" + (totalList - 10) + "&nbsp;" + datatabletranslations['More'];
                        }
                        if (isAllClub) {
                            json.aaData[i][title] = (_.size(splitValues) > 0) ? allClub[0] + ',&nbsp;<i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + allClub[1] + ' </i>' : allClub[0];
                        } else {
                            json.aaData[i][title] = (_.size(splitValues) > 1) ? '<i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + totalList + '&nbsp;' + (totalList > 1 ? datatabletranslations['other_clubs'] : datatabletranslations['other_club']) + ' </i>' : textContact;
                        }
                    }
                    break;
            }

        }
        indexCount++;
    }

}
function manipulateWorkgroupFields(json)
{
    var indexCount = json.start;
    for (var i = 0, ien = json.aaData.length; i < ien; i++) {
        for (var j = 0, jen = json.aaDataType.length; j < jen; j++) {
            var title = json.aaDataType[j]['title'];

            switch (json.aaDataType[j]['type']) {

                case "edit"  :
                    if (i == 0) {
                        json.aaData[i][title] = "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox' data-subcategoryId=" + json.aaData[i]['subcatId'] + " id=" + json.aaData[i]['documentId'] + " name='check'   value='0' data_index=" + indexCount + "></div><input type='hidden' id='filterCount' value='0'><input type='hidden' id='totalCount' value='0'>";
                    } else {
                        json.aaData[i][title] = "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox' data-subcategoryId=" + json.aaData[i]['subcatId'] + " id=" + json.aaData[i]['documentId'] + " name='check'  value='0'  data_index=" + indexCount + "></div>";
                    }

                    break;

                case 'WG_FO_VISIBLE_TO':
                    if (json.aaData[i][title] == 'workgroup') {
                        json.aaData[i][title] = workgroups;
                    } else if (json.aaData[i][title] == 'workgroup_admin') {
                        json.aaData[i][title] = workgroupAdmin;

                    } else if (json.aaData[i][title] == 'main_document_admin') {
                        json.aaData[i][title] = documenAdmin;
                    }
                    break;
                case "WG_FO_SIZE"  :
                    if (json.aaData[i][title] != null) {
                        var filesize = convertByteToMb(json.aaData[i][title]);

                        json.aaData[i][title] = convertByteToMb(json.aaData[i][title]);

                    }

                    break;
                case "WG_FO_DEPOSITED_WITH"  :

                    if (json.aaData[i][title] != null) {
                        var textContact = '';
                        var value = json.aaData[i][title];

                        var totalList = _.size(value.split("#"))
                        var splitValues = value.split("#", 10);
                        $.each(splitValues, function(index, value) {
                            if (value == 'Executive Board') {
                                value = executiveBoardTerminology;
                            }
                            if (_.size(splitValues) == 1) {
                                textContact += value;
                            } else {
                                textContact += value + "<br/>";
                            }


                        })

                        if (totalList > 10) {
                            textContact += "&nbsp;" + (totalList - 10) + "&nbsp;" + datatabletranslations['More'];
                        }
                        json.aaData[i][title] = (_.size(splitValues) > 1) ? '<i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + totalList + '&nbsp;' + datatabletranslations['workgroups'] + ' </i>' : textContact;
                    }

                    break;
                case "docname"  :
                    if (json.aaData[i][title] != null) {
                        json.aaData[i][title] = "<i class='fa " + json.aaData[i]['docname_icon'] + " fg-datatable-icon '></i><a class='fg-dev-docname' href='" + json.aaData[i]['docname_url'] + "' target='_blank'>" + json.aaData[i][title] + "</a>&nbsp;<a href='" + json.aaData[i]['edit_url'] + "' class='fg-tableimg-hide'><i class='fa fa-pencil-square-o fg-pencil-square-o'></i></a>";
                    }

                    break;
                case "WG_FO_DESCRIPTION"  :

                    if (json.aaData[i][title] != null && json.aaData[i][title].length > 50) {
                        var lineBreak = json.aaData[i][title].match(/.{1,50}/g);
                        if (_.size(lineBreak) > 1) {
                            json.aaData[i][title] = '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + nl2br(json.aaData[i][title]) + '" data-original-title="">' + lineBreak[0] + ' &hellip;</i>';
                        }
                    }
                    break;
                 case "WG_FO_ISPUBLIC"  :
                    if (json.aaData[i][title] == 1) {
                        json.aaData[i][title] = '<div class="fg-static-on">' + datatabletranslations['On'] + '</div>';
                    } else {
                        json.aaData[i][title] = '<div class="fg-static-off">' + datatabletranslations['Off'] + '</div>';
                        ;
                    }

                    break;       

            }

        }
        indexCount++;
    }

}

function manipulateContactFields(json)
{
    var indexCount = json.start;
    for (var i = 0, ien = json.aaData.length; i < ien; i++) {
        for (var j = 0, jen = json.aaDataType.length; j < jen; j++) {
            var title = json.aaDataType[j]['title'];

            switch (json.aaDataType[j]['type']) {

                case "edit"  :
                    if (i == 0) {
                        json.aaData[i][title] = "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox' data-subcategoryId=" + json.aaData[i]['subcatId'] + " id=" + json.aaData[i]['documentId'] + " name='check'   value='0'  data_index=" + indexCount + "></div><input type='hidden' id='filterCount' value='0'><input type='hidden' id='totalCount' value='0'>";
                    } else {
                        json.aaData[i][title] = "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox' data-subcategoryId=" + json.aaData[i]['subcatId'] + " id=" + json.aaData[i]['documentId'] + " name='check'  value='0'  data_index=" + indexCount + "></div>";
                    }

                    break;
                case "CO_FO_SIZE"  :
                    if (json.aaData[i][title] != null) {
                        var filesize = convertByteToMb(json.aaData[i][title]);

                        json.aaData[i][title] = filesize;

                    }

                    break;
                case "CO_FO_DEPOSITED_WITH"  :

                    if (json.aaData[i][title] != null && json.aaData[i][title] != '' && json.aaData[i][title] != '-' && json.aaData[i][title] != 'NONE') {
                        var textContact = '';
                        var value = json.aaData[i][title];
                        var totalList = _.size(value.split("#"))
                        var splitValues = value.split("#", 10);
                        //  var json.aaData[i]['click_url'];
                        var stringUrl = json.aaData[i]["CO_FO_DEPOSITED_WITH_URL"];
                        $.each(splitValues, function(index, value) {
                            var contactName = value.split("|");
                            var url = stringUrl.replace("%23contactId", contactName[1]);
                            if (_.size(splitValues) == 1) {
                                textContact += (accessFlag == 1) ? "<a href='" + url + "'>" + contactName[0] + "</a>" : contactName[0];

                            } else {
                                textContact += (accessFlag == 1) ? "<a href='" + url + "'>" + contactName[0] + "</a><br/>" : contactName[0] + "<br/>";
                            }
                        })

                        if (totalList > 10) {
                            textContact += "&nbsp;" + (totalList - 10) + "&nbsp;" + datatabletranslations['More'];
                        }
                        json.aaData[i][title] = (_.size(splitValues) > 1) ? '<i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + totalList + '&nbsp;' + datatabletranslations['contacts'] + ' </i>' : textContact;
                    }

                    break;
                case "CO_FO_VISIBLE_TO"  :
                    if (json.aaData[i][title] == 1) {
                        json.aaData[i][title] = '<div class="fg-static-on">' + datatabletranslations['On'] + '</div>';
                    } else {
                        json.aaData[i][title] = '<div class="fg-static-off">' + datatabletranslations['Off'] + '</div>';
                        ;
                    }

                    break;
                case "CO_FO_ISPUBLIC"  :
                    if (json.aaData[i][title] == 1) {
                        json.aaData[i][title] = '<div class="fg-static-on">' + datatabletranslations['On'] + '</div>';
                    } else {
                        json.aaData[i][title] = '<div class="fg-static-off">' + datatabletranslations['Off'] + '</div>';
                        ;
                    }

                    break;    
                case "docname"  :
                    if (json.aaData[i][title] != null) {
                        json.aaData[i][title] = "<i class='fa " + json.aaData[i]['docname_icon'] + " fg-datatable-icon'></i><a class='fg-dev-docname' href='" + json.aaData[i]['docname_url'] + "' target='_blank'>" + json.aaData[i][title] + "</a>&nbsp;<a href='" + json.aaData[i]['edit_url'] + "' class='fg-tableimg-hide'><i class='fa fa-pencil-square-o fg-pencil-square-o'></i></a>";
                    }

                    break;
                case "CO_FO_DESCRIPTION"  :

                    if (json.aaData[i][title] != null && json.aaData[i][title].length > 50) {
                        var lineBreak = json.aaData[i][title].match(/.{1,50}/g);
                        if (_.size(lineBreak) > 1) {
                            json.aaData[i][title] = '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + nl2br(json.aaData[i][title]) + '" data-original-title="">' + lineBreak[0] + ' &hellip;</i>';
                        }
                    }
                    break;
                case "CO_FO_DEPOSITED_WITH_FOR_ASSIGNED"  :
                    if (json.aaData[i][title] != null && json.aaData[i][title] != '' && json.aaData[i][title] != '-' && json.aaData[i][title] != 'NONE') {
                        var textContact = '';
                        var value = json.aaData[i][title];
                        var totalList = _.size(value.split("#"));
                        if (typeof totalList === 'undefined') {
                            totalList = 0;
                        }
                        var splitValues = value.split("#", 10);
                        //  var json.aaData[i]['click_url'];
                        var stringUrl = json.aaData[i]["CO_FO_DEPOSITED_WITH_FOR_ASSIGNED_URL"];
                        $.each(splitValues, function(index, value) {
                            var contactName = value.split("|");
                            var url = stringUrl.replace("%23contactId", contactName[1]);
                            if (_.size(splitValues) == 1) {
                                textContact += (accessFlag == 1) ? "<a href='" + url + "'>" + contactName[0] + "</a>" : contactName[0];

                            } else {
                                textContact += (accessFlag == 1) ? "<a href='" + url + "'>" + contactName[0] + "</a><br/>" : contactName[0] + "<br/>";
                            }
                        })

                        if (totalList > 10) {
                            textContact += "&nbsp;" + (totalList - 10) + "&nbsp;" + datatabletranslations['More'];
                        }
                        json.aaData[i][title] = (_.size(splitValues) > 1) ? '<i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + totalList + '&nbsp;' + (totalList > 1 ? datatabletranslations['other_contacts'] : datatabletranslations['other_contact']) + ' </i>' : textContact;
                    }
                    break;

            }

        }
        indexCount++;
    }

}
function manipulateTeamFields(json)
{

    var indexCount = json.start;
    for (var i = 0, ien = json.aaData.length; i < ien; i++) {

        for (var j = 0, jen = json.aaDataType.length; j < jen; j++) {
            var title = json.aaDataType[j]['title'];

            switch (json.aaDataType[j]['type']) {

                case "edit"  :
                    if (i == 0) {
                        json.aaData[i][title] = "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox'  data-subcategoryId=" + json.aaData[i]['subcatId'] + " id=" + json.aaData[i]['documentId'] + " name='check'   value='0' data_index=" + indexCount + "></div><input type='hidden' id='filterCount' value='0'><input type='hidden' id='totalCount' value='0'>";
                    } else {
                        json.aaData[i][title] = "<div class='fg-td-wrap'><i class='fa fg-sort ui-draggable'></i> <input class='dataClass fg-dev-avoidicon-behaviour' type='checkbox' data-subcategoryId=" + json.aaData[i]['subcatId'] + " id=" + json.aaData[i]['documentId'] + " name='check'  value='0'  data_index=" + indexCount + "></div>";
                    }

                    break;
                case "T_FO_DEPOSITED_WITH"  :

                    if (json.aaData[i][title] != null) {
                        var textContact = '';
                        var value = json.aaData[i][title];
                        var totalList = _.size(value.split("#"))
                        var splitValues = value.split("#", 10);

                        $.each(splitValues, function(index, value) {
                            if (_.size(splitValues) == 1) {
                                textContact += value;
                            } else {
                                textContact += value + "<br/>";
                            }
                        })

                        if (totalList > 10) {
                            textContact += "&nbsp;" + (totalList - 10) + "&nbsp;" + datatabletranslations['More'];
                        }
                        json.aaData[i][title] = (_.size(splitValues) > 1) ? '<i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + totalList + '&nbsp;' + teamTerminology + ' </i>' : textContact;
                    }

                    break;
                case "function"  :

                    if (json.aaData[i][title] != null) {
                        var textContact = '';
                        var value = json.aaData[i][title];
                        var totalList = _.size(value.split("#"))
                        var splitValues = value.split("#", 10);
                        $.each(splitValues, function(index, value) {
                            if (_.size(splitValues) == 1) {
                                textContact += value;
                            } else {
                                textContact += value + "<br/>";
                            }
                        })

                        if (totalList > 10) {
                            textContact += "&nbsp;" + (totalList - 10) + "&nbsp;" + datatabletranslations['More'];
                        }

                        json.aaData[i][title] = (_.size(splitValues) > 1) ? '<i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + totalList + '&nbsp;' + datatabletranslations['Function'] + ' </i>' : textContact;
                    }

                    break;
                case "T_FO_SIZE"  :

                    if (json.aaData[i][title] != null) {
                        var filesize = convertByteToMb(json.aaData[i][title]);
                        json.aaData[i][title] = filesize;

                    }

                    break;
                case "visibleflag"  :
                    if (json.aaData[i][title] == 1) {
                        json.aaData[i][title] = '<div class="fg-static-on">' + datatabletranslations['On'] + '</div>';
                    } else {
                        json.aaData[i][title] = '<div class="fg-static-off">' + datatabletranslations['Off'] + '</div>';
                    }

                    break;
                case "docname"  :
                    if (json.aaData[i][title] != null) {
                        json.aaData[i][title] = "<i class='fa " + json.aaData[i]['docname_icon'] + " fg-datatable-icon'></i><a class='fg-dev-docname' href='" + json.aaData[i]['docname_url'] + "' target='_blank'>" + json.aaData[i][title] + "</a>&nbsp;<a href='" + json.aaData[i]['edit_url'] + "' class='fg-tableimg-hide'><i class='fa fa-pencil-square-o fg-pencil-square-o'></i></a>";
                    }

                    break;
                case "T_FO_VISIBLE_TO"  :
                    if (json.aaData[i][title] == 'team') {
                        json.aaData[i][title] = wholeTeam;
                    } else if (json.aaData[i][title] == 'team_functions') {
                        json.aaData[i][title] = generateFunctionpoover(teamFunction, json.aaData[i]['FO_FUNCTIONS']);

                    } else if (json.aaData[i][title] == 'team_admin') {
                        json.aaData[i][title] = teamAdmins;
                    } else if (json.aaData[i][title] == 'club_contact_admin') {
                        json.aaData[i][title] = clubDocumentAdmin;
                    }

                    break;
                  case "T_FO_ISPUBLIC"  :
                    if (json.aaData[i][title] == 1) {
                        json.aaData[i][title] = '<div class="fg-static-on">' + datatabletranslations['On'] + '</div>';
                    } else {
                        json.aaData[i][title] = '<div class="fg-static-off">' + datatabletranslations['Off'] + '</div>';
                        ;
                    }

                    break;   

                case "T_FO_DESCRIPTION"  :

                    if (json.aaData[i][title] != null && json.aaData[i][title].length > 50) {
                        var lineBreak = json.aaData[i][title].match(/.{1,50}/g);
                        if (_.size(lineBreak) > 1) {
                            json.aaData[i][title] = '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + nl2br(json.aaData[i][title]) + '" data-original-title="">' + lineBreak[0] + ' &hellip;</i>';
                        }
                    }
                    break;



            }

        }
        indexCount++;
    }

}
function convertByteToMb(bytes) {
    if(bytes.indexOf('&lt;') > -1) { // case whan size less than 1 MB
        bytes = bytes.replace("&lt;", "");
        filesize = '< '+ FgClubSettings.formatNumber(parseFloat(bytes)) + " MB";
    } else {
        filesize = FgClubSettings.formatNumber(parseFloat(bytes)) + " MB" ;
    }
    return filesize;
}
function redrawdataTable() {
    documentTable.api().draw();
}
function redrawdataTableFromServer() {
    documentTable.api().ajax.reload(function(data) {
        $(".dataClass").uniform();
        $(".count-document-tab").html(data.iTotalRecords);
        $("#check_all").prop('checked', false);
        $("#check_all").parent().removeClass("checked");
    });
}
function generateFunctionpoover(title, functionNames) {

    if (functionNames != null && functionNames != '-') {
        var textContact = '';
        var value = functionNames;
        var totalList = _.size(value.split("#"))
        var splitValues = value.split("#", 10);
        $.each(splitValues, function(index, value) {

            if (_.size(splitValues) == 1) {
                textContact += value;
            } else {
                textContact += value + "<br/>";
            }
        })

        if (_.size(splitValues) > 10) {
            textContact += "&nbsp;" + (totalList - 10) + "&nbsp;" + datatabletranslations['More'];
        }

        title = (_.size(splitValues) > 1) ? '<i class="fg-dev-Popovers fg-dotted-br"  data-content="' + textContact + '" >' + title + ' </i>' : textContact;
    }

    return title;
}
/**
 * To handle move pop up area
 * @param {type} assignmentArray
 */
function showDocumentPopup(assignmentArray) {
    var selContNames = [];
    var selContIds = [];
    var subCategoryId = [];
    selectedCat= assignmentArray['assignmentData']['dropCategoryId'];
    selectedSubcat = assignmentArray['assignmentData']['dropMenuId'];
    
    //find document id
    if (assignmentArray['selActionType'] == 'single-select') {
        var allContactsData = $.parseJSON($('#selcontacthidden').val());

        $.each(allContactsData, function(cdKey, contactData) {
            var contactId = contactData.id;
            subCategoryId.push({'id': contactData.documentSubcategoryId})
            selContIds.push(contactId);
            selContNames.push({'id': contactId, 'name': contactData.documentName});

        });
    } else {
        $(".DTFC_LeftBodyWrapper input.dataClass:checked").each(function() {
            var contactId = $(this).attr('id');
            subCategoryId.push({'id': $(this).attr('data-subcategoryId')})
            selContIds.push(contactId);
            selContNames.push({'id': contactId, 'name': $(this).parents('tr').find('.fg-dev-docname').text()});
        });
    }
    //grouping the subcategory id
    var groupedSubcategory = _.groupBy(subCategoryId, 'id');

    //dropdown -category and subcategory
    $.getJSON(dropdownPath,function(data){
       arrayRslt = FgUtility.groupByMulti(data.resultArray, ['id']);
       $.each(arrayRslt, function(key,assignment) {
        var catId = key;
        if ((catId != null) && (catId != 'null')) {
            var catTitle = assignment[0].title;
            sortOrder = assignment[0].sortOrder;
            catArray[sortOrder] = {'id': catId, 'title': catTitle};
            } 
           
            $.each(assignment,function(key,classs){
                var subId = classs.subId;
                if ((subId != null) && (subId != 'null')) {
                        if (subcatArray[catId] == undefined) {
                            subcatArray[catId] = {};
                        }
                        var classTitle = classs.titleSub;
                        classOrder = classs.subSortOrder;
                        subcatArray[catId][classOrder] = {'id': subId, 'title': classTitle};
                }
            }); 
        });
      
     displayDropdown();
     $('#popup_contents input').uniform(); 
     //disable if no subcategory selected
      setTimeout(saveOption,1000);
    });
    
    $(document).off('change', '#category_dropdown');
    $(document).on('change', '#category_dropdown', function() {
        selectedCat = $(this).val();
        selectedSubcat = '';
        displayDropdown();
        setTimeout(saveOption,1);
    });
    
     $(document).on('change', '#subcategory_dropdown', function() {
        selectedSubcat = $(this).val();
        displayDropdown();
        setTimeout(saveOption,1);
    });
    
    function displayDropdown(){
        renderTemplateContent('display_dropdown', {'options':catArray , 'selectedId': selectedCat}, 'category_dropdown');
        var classOptions = subcatArray[selectedCat] ? subcatArray[selectedCat]: {};
        renderTemplateContent('display_dropdown', {'options':classOptions , 'selectedId': selectedSubcat}, 'subcategory_dropdown');
        if (Object.keys(classOptions).length > 0) {
            $('div[data-id=show_class_section]').removeClass('hide');
        } 
        $('#popup_contents select').select2();
    }
    
    function renderTemplateContent(templateScriptId, jsonData, parentDivId) {
        var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
        $('#' + parentDivId).html(htmlFinal);
    }
     
    $("#popup_contents").html($("#dummyPopupcontent").html());
    var singleArchiveTxt = $('#dummyPopupcontent .fg-dev-singleSelectionText').html();
    var multipleArchiveTxt = $('#dummyPopupcontent .fg-dev-multipleSelectionText').html();
    $('.fg-dev-multipleSelectionText').hide();
    $('.fg-dev-singleSelectionText').hide();
    //set pop up header text and its content
    if (selContIds.length == 1) {
        popupHeadText = singleArchiveTxt.replace('%docname%', selContNames[0].name);
        popupHeadText = popupHeadText.replace('%category%', assignmentArray['subcategoryName']);
        $('h4.modal-title').html('<div class="fg-popup-text" id="popup_head_text"></div>');
        $('#popup_contents #popup_head_text').text(popupHeadText + '...');
    } else {
        popupHeadText = multipleArchiveTxt.replace('%category%', assignmentArray['subcategoryName']);
        popupHeadText = popupHeadText.replace('%count%', selContIds.length);
        var contNamesHtml = '';
        var i = 0;
        var doctype =type.toLowerCase();
        $.each(selContNames, function(ckey, selContName) {
            i++;
            if (i == 11) {
                contNamesHtml += '<li>&hellip;</li>';
                return false;
            } else {
                contNamesHtml += '<li><a href="edit/'+doctype+'/'+selContName.id+'/0" target="_blank" data-cont-id="' + selContName.id + '">' + selContName.name + '</a></li>';
            }
        });
        $('#popup_contents h4.modal-title').html('<span class="fg-dev-contact-names"><a href="#" class="fg-plus-icon"></a><a href="#" class="fg-minus-icon"></a></span><div class="fg-popup-text" id="popup_head_text"></div>\n\
                    <div class="fg-arrow-sh"><ul>' + contNamesHtml + '</ul></div>');
        $('#popup_contents #popup_head_text').text(popupHeadText + '...');

    }

    $('#popup').modal('show');
    //bind click event to the +/- icon
    $(document).off('click', '.modal-title .fg-dev-contact-names');
    $(document).on('click', '.modal-title .fg-dev-contact-names', function(e) {
        $(this).parent().toggleClass('fg-arrowicon');
    });
    //bind click event to the button
    
    $(document).off('click', '.fg-dev-move');
    var updateArr = [];
    var dropMenuCount = 0;
    $(document).on('click', ".fg-dev-move", function() {
                   $('#popup').modal('hide');
        _.each(catArray, function(option) {
        if (option['id'] == selectedCat) { 
            assignmentArray['assignmentData']['dropCategoryTitle']= option['title'] ;
        }
     }); 
     _.each(subcatArray[selectedCat], function(option) {
        if (option['id'] == selectedSubcat) { 
            assignmentArray['subcategoryName']= option['title'] ;
        }
     }); 
     assignmentArray['assignmentData']['dropCategoryId'] = selectedCat;
     assignmentArray['assignmentData']['dropMenuId'] = selectedSubcat;
    
     var dropValues = assignmentArray['assignmentData']['dropCategoryTitle'] + ' - ' + assignmentArray['subcategoryName'];
     
        $.ajax({url: movePath,
            data: {'documentId': JSON.stringify(selContIds), 'dropedCategory': assignmentArray['assignmentData']['dropCategoryId'], 'dropedSubCategory': assignmentArray['assignmentData']['dropMenuId'], 'dropValue': dropValues, 'docType':docType},
            type: "post",
            success: function(data) {
                     FgUtility.showToastr(data.flash, 'success');
                //to find the category count and action 
                $.each(groupedSubcategory, function(ckey, subcategory) {
                    var groupCount = _.size(subcategory);
                    var menuCount = (ckey == assignmentArray['assignmentData']['dropMenuId']) ? (selContIds.length - groupCount) : groupCount;
                    var actionType = 'remove';
                    if (ckey == assignmentArray['assignmentData']['dropMenuId']) {
                        actionType = 'add';
                        dropMenuCount =(selContIds.length == groupCount)? selContIds.length: menuCount;
                    } 
                    updateArr.push({'categoryId': '', 'subCatId': ckey, 'catClubId': clubId, 'sidebarCount': menuCount, 'action': actionType});
                    
                });
                if (dropMenuCount === 0) {
                    updateArr.push({'categoryId': assignmentArray['assignmentData']['dropCategoryId'], 'subCatId': assignmentArray['assignmentData']['dropMenuId'], 'catClubId': clubId, 'sidebarCount': selContIds.length, 'action': 'add'});
                }
                FgCountUpdate.update('add', 'document', type.toLowerCase(), updateArr, 0);
                //datatable redraw
                 documentTable.api().draw();
            }
        })
    });
    
    function saveOption() {
        if (selectedSubcat =='') {
            $('.fg-dev-move').attr('disabled', 'true');
        } else {
            $('.fg-dev-move').removeAttr('disabled');
        }
    }
}


