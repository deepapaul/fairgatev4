var overviewTable;
FgOverviewassignmentList = {
   overviewType :'',
    setinitialOpt: function () {
        OverviewListOpt = {
            deferRender: true,
            dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4 col-sm-4 col-xs-12'i><'col-md-8 col-sm-8 col-xs-12'p>",
            scrollCollapse: true,
            paging: true,
            autoWidth: true,
            sScrollX: $('table.overviewTable').attr('xWidth') + "%",
            sScrollXInner: $('table.overviewTable').attr('xWidth') + "%",
            scrollY: FgCommon.getWindowHeight(360) + "px",
            stateSave: true,
            stateDuration: 60 * 60 * 24,
            lengthChange: true,
            serverSide: false,
            processing: false,
            retrieve: true,
            scrollX: true,
            pagingType: "full_numbers",
            ordering: true,
            responsive: true,
            destroy:true,
            language: {
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
            drawCallback: function (settings) {
                $(".dataTable_checkall").prop('checked', false);
                 $(".dataClass").prop('checked', false);
                 $(".chk_cnt").empty();
                 $(".fgContactdrop .fa").removeClass('fa-users').addClass('fa-bars');
                FgPopOver.customPophover(".popovers");
                $(".dataTable_checkall").prop
                $(".dataClass").uniform();
                $(".dataTable_checkall").uniform();
                $('.fg-dev-serviceclick').off('click');
                //bind click event to service name 
                $('.fg-dev-serviceclick').on('click', function () {
                    var serviceId = $(this).attr('data-service-id');
                    var serviceType = $(this).attr('data-service-type');
                    var servicetabType=$(this).attr('data-tab-type');
                    localStorage.removeItem(FgSidebar.activeMenuVar);
                    localStorage.removeItem(FgSidebar.activeSubMenuVar);
                    $('.subclass').removeClass("active");
                    localStorage.setItem(fgLocalStorageNames.sponsor.active.sidebarActiveMenu, 'services_li');
                    localStorage.setItem(fgLocalStorageNames.sponsor.active.sidebarActiveSubMenu, 'services_li_' + $(this).attr('data-service-category') + '_' + $(this).attr('data-service-id'));
                    FgSidebar.show();
                    var serviceDetails = {type: 'service', id: serviceId, serviceType: serviceType};
                    localStorage.setItem(fgLocalStorageNames.sponsor.active.listDetails, JSON.stringify(serviceDetails));
                    FgSponsor.sponsorserviceDatatableInit('service', serviceId, serviceType);
                    FgSponsor.moretabInit('data-tabs', 'data-tabs-content');
                    if(servicetabType =='former_assignments' || servicetabType=='recently_ended') {
                       $('#data-tabs a:last').tab('show');
                       localStorage.setItem(fgLocalStorageNames.sponsor.active.serviceTab, 'formerservice');
                       $(".fgContactdrop").attr('data-type','formerservice'); 
                    } else if(servicetabType=='future_assignments'){
                        $('#data-tabs li:nth-child(2) a').tab('show');
                        localStorage.setItem(fgLocalStorageNames.sponsor.active.serviceTab, 'futureservice');
                        $(".fgContactdrop").attr('data-type','futureservice'); 
                    } else {
                        $('#data-tabs a:first').tab('show');
                        localStorage.setItem(fgLocalStorageNames.sponsor.active.serviceTab, 'activeservice');
                        $(".fgContactdrop").attr('data-type','activeservice'); 
                    }
                    FgSponsor.showTablerowmenu(); 
                })
            },
            initComplete: function () {
                $('#fg_dev_assignmentTable').show();
                $('#fgrowchange').hide();
                $('#overviewTable').show();
                $("#fg-dev-columnsetting-flag").hide();
                //check all click event
                var instData = $('#overviewTable');
                var replaceid = instData.attr('data_row_change');
                if (replaceid != "") {
                    //for change the position
                    var tableid = instData.attr('data-table-name');
                    $("#fgoverviewrowchange").empty();
                    $("#" + tableid + "_length").detach().prependTo("#" + replaceid);
                    //add our own classes to the selectbox
                    $("#" + tableid + "_length").find('select').addClass('form-control cl-bs-select');
                    $("#" + tableid + "_length").find('select').select2();
                }
                FgPopOver.init(".fgPopovers", true, false);
                FgPopOver.init(".fg-dev-Popovers", true);                
               
            },
        stateLoadCallback: function(settings) {
            var stringified = localStorage.getItem('DataTables_' + window.location.pathname + FgOverviewassignmentList.overviewType)
            var oData = JSON.parse(stringified || null);
            if (oData) {
                $(".assignmentsearchbox").val(oData.search.search);
            }
            return oData;
        },
        stateSaveCallback : function(settings, data) {
            localStorage.setItem('DataTables_' + window.location.pathname + FgOverviewassignmentList.overviewType, JSON.stringify(data));
        }
        };
        OverviewListOpt.serverSide = false;
        OverviewListOpt.processing = false;
        return OverviewListOpt;
    },
    init: function (overviewType) {
        $('#assignmentTable').show();
        FgOverviewassignmentList.overviewType=overviewType;       
       
        $.getJSON($('table.overviewTable').attr('data-ajax-path'), {'assignmentType': overviewType}, function (result) {
            
            /* Set options and draw table for active bookings */
            var opt = FgOverviewassignmentList.setinitialOpt(overviewType);
               if (overviewType == 'former_assignments' || overviewType == 'recently_ended') {
                opt.order= [[2, 'asc']];
            } else if (overviewType == 'active_assignments') {
                opt.order = [[2, 'desc'], [3, 'asc'], [4, 'asc']];
            } else {
                opt.order = [[1, 'asc'], [3, 'asc'], [4, 'asc']];
            } 
            //To set the initial sorting
            opt.data = result['aaData'];
            opt.columnDefs = FgOverviewassignmentList.getcolumndefs(overviewType);
            opt.footerCallback = function (row, data, start, end, display) {
                var api = this.api(), data;
                /*  Total payment for Current FIscal year */
                var paymentCurrentTotal = FgDataTableUtil.getColumnTotal(data, "SA_paymentCurr");
                /*  Total payment for Next FIscal year */
                var paymentNextTotal = FgDataTableUtil.getColumnTotal(data, "SA_paymentNext");
                $(api.column(7).footer()).html(FgClubSettings.getAmountWithCurrency(paymentCurrentTotal));
                $(api.column(8).footer()).html(FgClubSettings.getAmountWithCurrency(paymentNextTotal));

            };

            
            
            /** DATABASE INITIALIZING AREA **/
            //block the error/warning pop up block
            $.fn.dataTable.ext.errMode = 'none';
            //initialize datatable with error event handling code
            overviewTable = $('table.overviewTable').on('error.dt', function (e, settings, data) {
                window.location.reload();
            }).DataTable(opt);
            
            
            
            //For change the search box field
            $(".assignmentsearchbox").off("keyup");
            $(".assignmentsearchbox").on("keyup", function () {
                var searchVal = this.value;
                setDelay(function () {

                    overviewTable.search(searchVal).draw();
                    if ($(".assignmentsearchbox").val() != '') {
                        var tableInfo = overviewTable.page.info();
                        $("#fcount").html(tableInfo.recordsDisplay)
                        $("#tcount").show();
                        $("#fg-slash").show();
                    } else {
                        $("#tcount").hide();
                        $("#fg-slash").hide();
                    }
                }, 500);
            });

            if (overviewType == 'former_assignments' || overviewType == 'recently_ended') {
                overviewTable.column(6).visible(false);
                overviewTable.column(8).visible(false);
            } else {
                overviewTable.column(6).visible(true);
                overviewTable.column(8).visible(true);
            }
            var tableInfo = overviewTable.page.info();
            $('#fcount').html(tableInfo.recordsTotal);
            setTimeout(function(){overviewTable.columns.adjust();},200)
            
        });
    },
    /* Columns for active bookings tab */
    getcolumndefs: function (overviewType) {
        var columnDefs = '';

        switch (overviewType) {
            case "active_assignments" :
            case "future_assignments" :
            case "former_assignments" :
            case "recently_ended" :
                columnDefs = [{"name": "edit", "title": '<input type="checkbox" name="check_all" id="check_all" class="dataTable_checkall fg-dev-avoidicon-behaviour" data-type="assignmentTable"><i class="chk_cnt"></i>', "width": "1%", "orderable": false, "targets": 0, data: function (row, type, val, meta) {
                            return '<input class="dataClass fg-dev-avoidicon-behaviour" type="checkbox" id=' + row['SA_bookingId'] + ' name="check" data-iscompany ="' + row['Iscompany'] + '" value="0" >';

                        }},
                    {"name": "startdate", "targets": 1, "orderable": true, data: function (row, type, val, meta) {
                            row.displayData = "<a href='"+row['assignment_edit_url']+"'>"+row['SA_paymentstartdate']+"</a>";
                            row.sortData = FgDataTableUtil.getDateTime(row['SA_paymentstartdate']);
                            return row;
                        }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
                    },
                    {"name": "enddate", type: "null-last", "targets": 2, "orderable": true, data: function (row, type, val, meta) {
                            row.displayData = (row['SA_paymentenddate'] != 'null' ? row['SA_paymentenddate'] : '-');
                            row.sortData = row['SA_paymentenddate'] != 'null' ? FgDataTableUtil.getDateTime(row['SA_paymentenddate']) : null;
                            return row;
                        }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
                    },
                    {"name": "contact", "targets": 3, "orderable": true, data: function (row, type, val, meta) {
                            var icons = '';
                            var classes = 'inactive';
                             if (row['Ismember'] == '') {
                                classes = "inactive";
                            } else {
                                if (row['membershipType'] == 'fed') {
                                    classes = "fed-member";
                                } else if (row['membershipType'] == 'club') {
                                    classes = "active";
                                }
                            }
                            if (row['Iscompany'] == 1) {
                                icons += '<i class="fa fa-building-o fg-building ' + classes + '"></i>&nbsp;';
                            } else if (row['Gender'].toLowerCase() == 'female') {
                                icons += '<i class="fa fa-female fg-female ' + classes + '"></i>&nbsp;';
                            } else if (row['Gender'].toLowerCase() == 'male') {
                                icons += '<i class="fa fa-male fg-male ' + classes + '"></i>&nbsp;';
                            }

                            return "<div class='fg-contact-wrap'> " + icons + "<a class='fg-dev-contactname' href='" + row['click_url'] + "'>" + row['contactname'] + "</a></div>";
                        }},
                    {"name": "service", type: "null-last", "orderable": true, "targets": 4, data: function (row, type, val, meta) {
                            row.displayData = (row['SA_serviceName'] != 'null' ? '<a href="#" class="fg-dev-serviceclick" data-tab-type="'+overviewType+'" data-service-id="' + row['SA_serviceId'] + '" data-service-type="' + row['SA_service_type'] + '" data-service-category=' + row['SA_service_category'] + ' data-service_type="' + row['SA_service_type'] + '">' + row['SA_serviceName'] + '</a>' : '-');
                            row.sortData = row['SA_serviceName'] != 'null' ? row['SA_serviceName'] : null;
                            return row;
                        }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
                    },
                    {"name": "payment_plan", "targets": 5, "orderable": true, data: function (row, type, val, meta) {
                            var paymentplanArr = row['SA_paymentplan'] != 'none' && row['paymentplanDetails'] != null ? row['paymentplanDetails'].split('|') : [];
                            if (row['SA_paymentplan'] == 'custom') {
                                var title = (paymentplanArr[1] > 1) ? datatabletranslations['Payments'] : datatabletranslations['Payment'];
                                return datatabletranslations[paymentplanArr[0]] + " (" + paymentplanArr[1] + " " + title + ")";
                            } else if (row['SA_paymentplan'] == 'regular') {
                                var every_months_text = (paymentplanArr[1] > 1) ? datatabletranslations['every_months'] : datatabletranslations['every_month'];
                                return (datatabletranslations[paymentplanArr[0]] + " (" + every_months_text.replace('%month%', paymentplanArr[1]) + ")");
                            } else {
                                return datatabletranslations[row['SA_paymentplan']];
                            }
                        }},
                    {"name": "next_payment_date", "targets": 6, "orderable": true, type: "null-numeric-last", data: function (row, type, val, meta) {
                            var nextpaymentdateArr = (row['SA_paymentDate'] !== undefined && row['SA_paymentDate'] != "") ? row['SA_paymentDate'].split('|') : '-';
                            if (nextpaymentdateArr.length > 1) {
                                row.displayData = nextpaymentdateArr[0] + " (" + FgClubSettings.getAmountWithCurrency(nextpaymentdateArr[1]) + ")";
                            } else {
                                row.displayData = nextpaymentdateArr;
                            }
                            row.sortData = (nextpaymentdateArr.length > 1) ? FgDataTableUtil.getDateTime(nextpaymentdateArr[0]) : null;
                            return row;
                        }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
                    },
                    {"name": "current_payment", "targets": 7, "orderable": true, type: "null-numeric-last", "className": 'text-right', data: function (row, type, val, meta) {
                            var paymentDetails = row['Currentpayments'];
                            var actualData = '-';
                            if (paymentDetails != '' && paymentDetails !== undefined && paymentDetails != null) {
                                paymentDetails = "[" + paymentDetails + "]";
                                paymentDetails = $.parseJSON(paymentDetails);
                                paymentDetails =  _.sortBy(paymentDetails, function(o) { return moment(o.date, "DD-MM-YYYY").format('YYYYMMDD'); });
                                var displayText = '';
                                var paymentCount = _.size(paymentDetails);
                                $.each(paymentDetails, function (key, values) {
                                    displayText += values['date'] + ': ' + FgClubSettings.getAmountWithCurrency(values['amount']) + "<br/>";
                                });
                                actualData = '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + displayText + '" >' + FgClubSettings.getAmountWithCurrency(row['SA_paymentCurr']) + ' </i>';

                            }
                            row.displayData = actualData;
                            return row;
                        }
                        , render: {"_": 'SA_paymentCurr', "display": 'displayData', 'filter': 'displayData'}
                    },
                    {"name": "future_payment", "targets": 8, "orderable": true, type: "null-numeric-last", "className": 'text-right', data: function (row, type, val, meta) {
                            var paymentDetails = (row['Nextpayments'] !== undefined && row['Nextpayments'] != '') ? row['Nextpayments'] : '';
                            var actualData = '-';
                            if (paymentDetails != '' && paymentDetails !== undefined && paymentDetails != null) {
                                paymentDetails = "[" + paymentDetails + "]";
                                paymentDetails = $.parseJSON(paymentDetails);
                                paymentDetails =  _.sortBy(paymentDetails, function(o) { return moment(o.date, "DD-MM-YYYY").format('YYYYMMDD'); });
                                var displayText = '';
                                var paymentCount = _.size(paymentDetails);
                                $.each(paymentDetails, function (key, values) {
                                    displayText += values['date'] + ': ' + FgClubSettings.getAmountWithCurrency(values['amount']) + "<br/>";
                                });
                                actualData = '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + displayText + '" >' + FgClubSettings.getAmountWithCurrency(row['SA_paymentNext']) + ' </i>';

                            }
                            row.displayData = actualData;
                            return row;
                        }, render: {"_": 'SA_paymentNext', "display": 'displayData', 'filter': 'displayData'}
                    },
                    {"name": "total_payment", "orderable": true, type: "null-numeric-last", "targets": 9, "className": 'text-right', data: function (row, type, val, meta) {
                            var actualData = (row['SA_totalPayment'] != null ? FgClubSettings.getAmountWithCurrency(row['SA_totalPayment']) : '-');
                            var resValue = FgServiceList.getPaymentValues(row, 'SA_totalPayment', 'Totalpayments');
                            row.sortData = (resValue.indexOf('&infin;') >= 0) ? '-1' : resValue != '-' && row['SA_totalPayment'] ? row['SA_totalPayment'] : '';
                            row.displayData = (resValue === '') ? '-' : resValue;
                            return row;
                        }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
                    }, {"name": "", "width": "10%", "targets": 10, "orderable": false, data: function (row, type, val, meta) {
                    return null;
            }}
                ];

                break;

        }

        return columnDefs;

    },
    /**
     * function to get payment plan value 
     * 
     * @param {type} row        value array of a row
     * @param {type} fieldValue field name of value
     * @param {type} fieldJson  filed name name of json array
     * @returns {String}
     */
    getPaymentValues: function (row, fieldValue, fieldJson) {
        var currPaym = row[fieldJson];
        var actualData = '';
        var infin = false;
        //set value as infinity when plan is regular and end date is null
        if (fieldValue == 'SA_totalPayment' && row['SA_paymentplan'] == 'regular' && (row['SA_last_payment_date'] == 'null' || row['SA_last_payment_date'] == '' || row['SA_last_payment_date'] == null) && (row['SA_paymentenddate'] == 'null' || row['SA_paymentenddate'] == '' || row['SA_paymentenddate'] == null)) {
            infin = true;
        }
        if (currPaym != '' && currPaym !== undefined && currPaym != null && row['SA_paymentplan'] !== 'none') {
            currPaym = "[" + currPaym + "]";
            currPaym = $.parseJSON(currPaym);
            currPaym =  _.sortBy(currPaym, function(o) { return moment(o.date, "DD-MM-YYYY").format('YYYYMMDD'); });
            var textPayments = '';
            var paymntCount = _.size(currPaym);
            var textValue = infin ? FgClubSettings.getAmountWithCurrency('&infin;', true) : FgClubSettings.getAmountWithCurrency(row[fieldValue]);
            //itrate over payment array to build popover
            $.each(currPaym, function (key, values) {
                if (key <= 3) {
                    textPayments += values['date'] + ": " + FgClubSettings.getAmountWithCurrency(values['amount']) + "<br/>";
                }
            });
            actualData = (paymntCount > 4) ? '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + textPayments + '&hellip;" >' + textValue + '</i>' : '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + textPayments + '" >' + textValue + ' </i>';

        }
        return  row['SA_paymentplan'] === 'none' ? '-' : actualData;
    }

}
