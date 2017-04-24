var sponsorTable = '';

var FgSponsorTable = function () {

    return {
        //main function to initiate the module
        init: function () {
            if (!jQuery().dataTable) {
                return;
            }
            // dataTables
            if ($('.sponsordataTable:visible').length > 0) {
                $('.sponsordataTable:visible').each(function () {
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
            return sponsorTable;

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
            "data": function (parameter) {
                var sponsorparameters = instData.attr('document-parameter');
                //for setting the document listing parameters
                if (typeof sponsorparameters !== 'undefined' && sponsorparameters !== false) {
                    var tablecolumnName = localStorage.getItem(tableSettingValueStorage);
                    parameter.filterdata = filterdata;
                    parameter.tableField = tablecolumnName;
                }
            },
            "type": "POST",
            "dataSrc": function (json) {
                manipulatesponsorColumnFields(json);
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
    //To set the initial sorting
    if (instData.hasClass("dataTable-initialSort")) {
        var str = instData.attr('data-sort');
        var res = str.split("#");
        opt.order = [[res[0], res[1]]];

    }
    //set table column
    if (instData.hasClass("dataTable-ajaxHeader")) {
        opt.aoColumns = $.parseJSON(localStorage.getItem(tableColumnTitleStorage));

    }
    var columnDeflag = instData.attr('data-column-def');

    if (typeof columnDeflag !== 'undefined' && columnDeflag !== false) {
        opt.columnDefs = columnDefs;
    }

    var xwidth = instData.attr('xWidth');
    opt.sScrollX = xwidth + "%";
    opt.sScrollXInner = xwidth + "%";
    opt.scrollCollapse = true;
    if (instData.hasClass("dataTable-y-height")) {
        var yheight = FgCommon.getWindowHeight(100);
    } else {
        var yheight = FgCommon.getWindowHeight(418);
    }
    opt.scrollY = yheight + "px";

    opt.stateLoadCallback = function (settings) {
        if (contactType !== undefined && contactType != 'archivedsponsor') {
            var stringifieddata = localStorage.getItem('sponsorlist' + clubId + '-' + contactId);
            var menuType = JSON.parse(stringifieddata || null);
            var stringified = localStorage.getItem('DataTables_' + window.location.pathname + window.location.search + "_" + menuType.type)
        } else {
            var stringified = localStorage.getItem('DataTables_' + window.location.pathname + window.location.search)
        }
        var oData = JSON.parse(stringified || null);

        if (oData && settings.fnRecordsTotal() < oData.start) {
            oData.start = 0;
        }
        if (oData) {
            $(".searchbox").val(oData.search.search);
        }

        return oData;
    }
    opt.stateSaveCallback = function (settings, data) {
        if (contactType !== undefined && contactType != 'archivedsponsor') {
            var stringified = localStorage.getItem('sponsorlist' + clubId + '-' + contactId)
            var menuType = JSON.parse(stringified || null);
            localStorage.setItem('DataTables_' + window.location.pathname + window.location.search + "_" + menuType.type, JSON.stringify(data));
        } else {
            localStorage.setItem('DataTables_' + window.location.pathname + window.location.search, JSON.stringify(data));
        }

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
        $('.dataTable_checkall').uniform();
    };
    opt.fnDrawCallback = function (oSettings) {
        
        //checkbox convert to uniform model

        $(".dataTable_checkall").parent().removeClass('checked');
        $(".dataTable_checkall").uniform();

        FgCommon.checkboxpluginInit();
        if (instData.hasClass("doc-assignments")) {
            $(".dataClass").uniform();
        }
// For setting the count in the top of the datatable

        if (instData.hasClass("data-filter-count")) {
            // to set the values in the datatable count display area
            FgCommon.setDataTableCountDisplay(this.fnSettings());
            //data-type="archived"
            if (contactType !== undefined && contactType == 'archivedsponsor') {
                //replace the total count from the menu
                $("#tcount").html($('li.fg-header-nav-active .fg-header-nav-active').find('span.badge').eq(0).text());
            }

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
                    var count;
                    if ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length > 0) {
                        count = $(".DTFC_LeftBodyWrapper input.dataClass:checked").length;
                    } else {
                        count = 1;
                    }
                    var contactId = $(this).parent().find("input[class=dataClass]").attr('id');
                    contactlist = contactId;
                    return $("<div class='ui-widget-header fg-dev-grabbing-icon'><span style='display:none;' id='contactList'>" + contactlist + "</span><span class='fg-drag-count'>" + count + "</span></div>");
                },
                containment: "body"
            });

            FgSidebar.droppableEventIconHandling('sponsor');
        }

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

    opt.fnServerParams = function (aoData) {

    };
    var tableid = instData.attr('data-table-name');
    $('#'+tableid).on( 'length.dt', function ( e, settings, len ) {
        FgUtility.startPageLoading();
    } );

    opt.fnInitComplete = function () {
        var leftColumnCount = 2;

        if (typeof contactType !==undefined &&  contactType=='archivedsponsor') {
            leftColumnCount = 3;
        }
        if ($(this).hasClass("dataTable-fixed")) {
            FgCommon.generateFixedColumn(sponsorTable, leftColumnCount);
        } else {
            $(".dataClass").uniform();

        }
        setTimeout(function () {
            FgCheckBoxClick.init('sponsordataTable');
        }, 200);
        $('.dataTable_checkall').uniform();
        //for show the pop over functionality in the dataTable

        var attr = $(this).attr('dataTable-popover');

        // For some browsers, `attr` is undefined; for others,
        // `attr` is false.  Check for both.
        if (typeof attr !== undefined && attr !== false) {
            FgPopOver.init(".fgPopovers", true, false);
            FgPopOver.init(".fg-dev-Popovers", true);
        }

        // For change the position of the no.of records per page selection drop down box
         $('#fgrowchange').show();
         $("#fg-dev-columnsetting-flag").show();
       
        var replaceid = instData.attr('data_row_change');
        if (replaceid != "") {
            //for change the position
            var tableid = instData.attr('data-table-name');
            $("#" + tableid + "_length").detach().prependTo("#" + replaceid);
            //add our own classes to the selectbox
            $("#" + tableid + "_length").find('select').addClass('form-control cl-bs-select');
            $("#" + tableid + "_length").find('select').select2();
        }
        if (!$.isEmptyObject(sponsorTable)) {
            var api = sponsorTable.api();
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
    //initialize the datatable
    if (!$.isEmptyObject(sponsorTable)) {
        sponsorTable.api().draw();
    } else {
         /** DATABASE INITIALIZING AREA **/
        //block the error/warning pop up block
        $.fn.dataTable.ext.errMode = 'none';
        //initialize datatable with error event handling code
        sponsorTable = instData.on('error.dt', function (e, settings, data) {
            window.location.reload();
        }).dataTable(opt);
        
      //  sponsorTable = instData.dataTable(opt);
    }


    //For change the search box field
    $(".searchbox").off("keyup");
    $(".searchbox").on("keyup", function () {       
        var sponsorlistDet = localStorage.getItem(fgLocalStorageNames.sponsor.active.listDetails);        
        var sponsorListJson = JSON.parse(sponsorlistDet);
       
        if (sponsorListJson ===null || sponsorListJson.type == 'sponsor') {
            var searchVal = this.value;
            setDelay(function () {
                sponsorTable.api().search(searchVal).draw();
            }, 500);
        } else if(sponsorListJson.type == 'overview') {
           //overviewTable.search(this.value).draw();
        } else {
            //set the search value to the current localstorage of the particular table
            var stringified = localStorage.getItem('sponsorlist' + clubId + '-' + contactId)
            var menuType = JSON.parse(stringified || null);
            var tabName = localStorage.getItem('serviceselectedTab_'+clubId+'_'+contactId)  
            var serviceDataValue = localStorage.getItem('serviceDatalisting_' + menuType.id +  '_' + tabName + '_' + clubId + '_'+ contactId );
            var serviceData = JSON.parse(serviceDataValue || null); 
            if(serviceData){
               serviceData.search.search=this.value;
               localStorage.setItem('serviceDatalisting_' + menuType.id +  '_' + tabName + '_' + clubId + '_'+ contactId, JSON.stringify(serviceData));
            }
            var stringified = localStorage.getItem('DataTables_' + window.location.pathname + window.location.search + "_" + menuType.type)
            var oData = JSON.parse(stringified || null);
            if (oData) {
                oData.search.search = this.value;
                localStorage.setItem('DataTables_' + window.location.pathname + window.location.search + "_" + menuType.type, JSON.stringify(oData));
            }
            var tabType = $('#data-tabs > li.active').attr('data_type');
            switch (tabType) {
                case 'activeservice':
                    $('.dataTable_checkall').uniform();
                    if (!$.isEmptyObject(activeserviceTable)) {
                        activeserviceTable.search(this.value).draw();
                    }
                    break;
                case 'futureservice':
                    if (!$.isEmptyObject(futureserviceTable)) {
                        futureserviceTable.search(this.value).draw();
                    }
                    break;
                case 'formerservice':
                    if (!$.isEmptyObject(formerserviceTable)) {
                        formerserviceTable.search(this.value).draw();
                    }
                    break;
            }
        }
        if ($(".searchbox").val() != '') {
            $("#tcount").show();
            $("#fg-slash").show();
            //$(".fa-filter").show();
        } else {
            $("#tcount").hide();
            $("#fg-slash").hide();
            // $(".fa-filter").hide();
        }
    });

    var hideColumn = instData.attr('dataTable-column-hide');

    if (typeof hideColumn !== 'undefined' && hideColumn !== false) {
        sponsorTable.api().column(hideColumn).visible(false);
    }
    instData.css("width", '100%');
    $('.dataTables_filter input').attr("placeholder", datatabletranslations['data_Search']);
   
    $('body').on('click', '#inlineEditContact', function () {
        FgUtility.startPageLoading();
        sponsorTable.fnDraw();
    });

    if (instData.hasClass("dataTable-grouping")) {
        var rowOpt = {};

        if (instData.attr("data-grouping") == 'expandable') {
            rowOpt.bExpandableGrouping = true;
        }
        sponsorTable.rowGrouping(rowOpt);
    }

    $("#run").change(function () {
        sponsorTable.fnDraw();
    });
    return sponsorTable;
}

(function ($) {

    $(".fg_dev_filter_show").off('click');
    $(".fg_dev_filter_show").on('click', function () {
        $('.filter-alert').toggle('slow', function () {

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
        setTimeout(function () {
            $.uniform.update('#filterFlag');
        }, 500)
    })



}(jQuery));

function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}


function redrawdataTable() {
    sponsorTable.api().draw();
}
function redrawdataTableFromServer() {
    sponsorTable.api().ajax.reload(function (data) {
        $(".dataClass").uniform();
        $(".count-document-tab").html(data.iTotalRecords);
        $("#check_all").prop('checked', false);
        $("#check_all").parent().removeClass("checked");
        $(".dataTable_checkall").uniform();
    });
}
function manipulatesponsorColumnFields(json)
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
                    json.aaData[i][title] = json.aaData[i][title] ? ' <i class="fg-custom-popovers" data-trigger="hover" data-placement="bottom" data-content="<img src=\'' + json.aaData[i][title] + '\'/>" data-original-title="">' + json.aaData[i][title] + '</i> ' : '-';
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

                case "multiline":
                case "singleline":
                    if (inlineEditFlag == 0) {
                        if (json.aaData[i][title].length > 50) {
                            var lineBreak = json.aaData[i][title].match(/.{1,50}/g);
                            if (_.size(lineBreak) > 1) {
                                json.aaData[i][title] = '<i class="fg-dotted-br fg-custom-popovers" data-trigger="hover" data-placement="bottom" data-content="' + nl2br(json.aaData[i][title]) + '" data-original-title="">' + lineBreak[0] + ' &hellip;</i>';
                            }
                        }
                    } else {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;

                case "contactname"  :
                    var icons = '';
                    var editIcons = (contactType == 'formerfederationmember') ? '' : '&nbsp;<a href="' + json.aaData[i]['edit_url'] + '" class="fg-tableimg-hide fg-edit-sponsor-ico"><i class="fa fa-pencil-square-o fg-pencil-square-o"></i></a>';
                    var classes = "inactive";
                    var sponsorIcon = '';
                    var formerfederationClass = (contactType == 'formerfederationmember') ? ' fg-dev-stay-icon' : '';
                    var fedImage = (json.aaData[i]['fedmembershipType'] > 0) ? "&nbsp;<img src='"+fedIcon[fedclubId]+"'/>" : '';
                    var approveIcon = (json.aaData[i]['fedmembershipApprove'] > 0) ? "&nbsp;<i class='fg-dev-Popovers fa fa-clock-o' data-trigger='hover' data-placement='bottom' data-content='" + fedmemConfirmTootipMsg + "' > </i>" : '';

                    switch (contactType) {
                        case "sponsor" :case "archivedsponsor" : 
                            var sponsorIcon = (json.aaData[i]['SponsorIcon'] == true) ? "" : '';
                         if (json.aaData[i]['Ismember'] === '') {
                                classes = "inactive";
                            } else if (json.aaData[i]['clubmembershipType'] > 0 && (clubType == 'sub_federation_club' || clubType == 'standard_club' || clubType == 'federation_club')) {
                                classes = "active";
                            }
                            if (json.aaData[i]['Iscompany'] === '1') {
                                icons += '<i class="fa fa-building-o fg-building ' + classes + formerfederationClass + '"></i>&nbsp;';
                            } else if (json.aaData[i]['Gender'].toLowerCase() === 'female') {
                                icons += '<i class="fa fa-female fg-female ' + classes + formerfederationClass + '"></i>&nbsp;';
                            } else if (json.aaData[i]['Gender'].toLowerCase() === 'male') {
                                icons += '<i class="fa fa-male fg-male ' + classes + formerfederationClass + '"></i>&nbsp;';
                            }  

                            if (i == 0) {
                                json.aaData[i][title] = "<div class='fg-contact-wrap'> " + icons + "<input type='hidden' id='filterCount' value='0'><a class='fg-dev-contactname' href='" + json.aaData[i]['click_url'] + " '>" + json.aaData[i][title] + "</a>" + sponsorIcon + editIcons + fedImage + approveIcon + "</div>";
                            } else {
                                json.aaData[i][title] = "<div class='fg-contact-wrap'> " + icons + "<a class='fg-dev-contactname' href='" + json.aaData[i]['click_url'] + "'>" + json.aaData[i][title] + "</a>" + sponsorIcon + editIcons + fedImage + approveIcon + "</div>";
                            }
                            break;

                    }
                    break;
                case "edit"  :
                    var dragArrow = (contactType == 'contact') ? '<i class="fa fg-sort"></i>' : '<i class="fa fg-sort"></i>';
                    json.aaData[i][title] = '<div class="fg-td-wrap">' + dragArrow + ' <input class="dataClass" type="checkbox" id=' + json.aaData[i]['id'] + ' name="check" data-iscompany =' + json.aaData[i]['Iscompany'] + ' data-contactclub =' + json.aaData[i]['contactclubid'] + ' data-membership-type ="' + json.aaData[i][title] + '" value="0" ></div>';

                    break;
                case "select":
                    if (inlineEditFlag == 1) {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
                case "joining_date":
                case "leaving_date":
                    if (inlineEditFlag == 1) {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
                case "SS":
                    var serviceDetails = json.aaData[i][json.aaDataType[j]['title']];
                    if (serviceDetails != '' && serviceDetails !== undefined) {
                        serviceDetails = "[" + serviceDetails + "]";
                        serviceDetails = $.parseJSON(serviceDetails);
                        var columnText = '';

                        $.each(serviceDetails, function (key, values) {
                            var popoverContent = '';
                            $.each(values['booking'], function (firstkey, firstValues) {
                                firstValues['amount'] = (firstValues['amount'] == 'null') ? 0 : firstValues['amount'];
                                switch (firstValues['plan']) {
                                    case "regular":
                                        if (firstValues['end'] == '' || firstValues['end'] == 'null') {
                                            if (firstValues['lastpaymentDate'] == '' || firstValues['lastpaymentDate'] == 'null') {
                                                popoverContent += firstValues['start'] + " (" + FgClubSettings.getAmountWithCurrency('&infin;',true) + ")<br />";
                                            } else {
                                                popoverContent += firstValues['start'] + " (" + FgClubSettings.getAmountWithCurrency(firstValues['amount']) + ")<br />";
                                            }
                                        } else {
                                            popoverContent += firstValues['start'] + " - " + firstValues['end'] + " (" + FgClubSettings.getAmountWithCurrency(firstValues['amount']) + ")<br />";
                                        }

                                        break;
                                    case "custom":
                                        if (firstValues['end'] == '' || firstValues['end'] == null) {
                                            popoverContent += firstValues['start'] + " (" + FgClubSettings.getAmountWithCurrency(firstValues['amount']) + ")<br />";
                                        } else {
                                            popoverContent += firstValues['start'] + " - " + firstValues['end'] + " (" + FgClubSettings.getAmountWithCurrency(firstValues['amount']) + ")<br />";
                                        }
                                        break;
                                    case "none":
                                        if (firstValues['end'] == '' || firstValues['end'] == null) {
                                            popoverContent += firstValues['start'] + "<br />";
                                        } else {
                                            popoverContent += firstValues['start'] + " - " + firstValues['end'] + "<br />";
                                        }
                                        break;
                                }


                            })
                            columnText += '<i class="fg-dotted-br fg-custom-popovers" data-trigger="hover" data-placement="bottom" data-content="' + popoverContent + '" >' + values['serviceName'] + ' </i>, ';

                        })
                        columnText = columnText.substring(0, columnText.length - 2);
                        json.aaData[i][json.aaDataType[j]['title']] = columnText;
                    }
                    break;
                case "SAactive_assignments":
                case "SAfuture_assignments":
                case "SApast_assignments":
                    var activeServiceDetails = json.aaData[i][json.aaDataType[j]['fieldname']];
                    if (activeServiceDetails != '' && activeServiceDetails !== undefined) {
                        var activeServiceDetail = activeServiceDetails.split(";");
                        var iCount = 0;
                        var textContact = '';
                        var firstId;
                        var servicename;
                        var activeCount = _.size(activeServiceDetail);
                        $.each(activeServiceDetail, function (index, value) {
                            var splitValues = value.split("|");
                            if (iCount <= 3) {
                                textContact += splitValues[0] + " (" + splitValues[1] + " - ";
                                if (splitValues[2] != undefined && splitValues[2] != '') {
                                    textContact += splitValues[2] + ")<br/>";
                                } else {
                                    textContact += "&hellip;)<br/>";
                                }
                                if (activeCount > 4 && iCount == 3) {
                                    textContact += "&hellip;";
                                }
                                iCount++;
                            }

                        })
                        json.aaData[i][json.aaDataType[j]['type']] = "<i class='fg-dotted-br fg-custom-popovers' data-trigger='hover' data-placement='bottom' data-content=\"" + textContact + "\" >" + json.aaData[i][json.aaDataType[j]['type']] + " </i>";
                    }
                    break;
                case "SApayments_curr":
                case "SApayments_nex":
                    var paymentDetails = json.aaData[i][json.aaDataType[j]['fieldname']];
                    if (paymentDetails != '' && paymentDetails !== undefined) {
                        paymentDetails = "[" + paymentDetails + "]";
                        paymentDetails = $.parseJSON(paymentDetails);
                        paymentDetails =  _.sortBy(paymentDetails, function(o) { return moment(o.date, "DD-MM-YYYY").format('YYYYMMDD'); });
                        var textContact = '';
                        var paymentCount = _.size(paymentDetails);
                        $.each(paymentDetails, function (key, values) {
                            if (key <= 3) {
                                textContact += values['date'] + ': ' + FgClubSettings.getAmountWithCurrency(values['amount']) + " (" + values['service'] + ")<br/>";
                            }

                        });
                        json.aaData[i][json.aaDataType[j]['type']] = (paymentCount > 4) ? '<i class="fg-dotted-br fg-custom-popovers" data-trigger="hover" data-placement="bottom" data-content="' + textContact + '&hellip;" >' + FgClubSettings.getAmountWithCurrency(json.aaData[i][json.aaDataType[j]['type']]) + ' </i>' : '<i class="fg-dotted-br fg-custom-popovers" data-trigger="hover" data-placement="bottom" data-content="' + textContact + '" >' + FgClubSettings.getAmountWithCurrency(json.aaData[i][json.aaDataType[j]['type']]) + ' </i>';

                    } else {
                       json.aaData[i][json.aaDataType[j]['type']] = (json.aaData[i][json.aaDataType[j]['type']]!=0) ? FgClubSettings.getAmountWithCurrency(json.aaData[i][json.aaDataType[j]['type']]):json.aaData[i][json.aaDataType[j]['type']]; 
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
                default:
                    if (inlineEditFlag == 1) {
                        json.aaData[i][title] = getInlineEditParameters(json.aaData[i], json.aaDataType[j]);
                    }
                    break;
            }

        }
    }
}


