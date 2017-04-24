var activeserviceTable, futureserviceTable, formerserviceTable, ServiceListOpt, serviceData;
FgServiceList = {
    setinitialOpt: function () {
        ServiceListOpt = {
            deferRender: true,
            order: [[1, "desc"]],
            scrollCollapse: true,
            paging: false,
            autoWidth: true,
            sScrollX: $('table.serviceTable').attr('xWidth') + "%",
            sScrollXInner: $('table.serviceTable').attr('xWidth') + "%",
            scrollY: FgCommon.getWindowHeight(359) + "px",
            stateSave: true,
            stateDuration: 60 * 60 * 24,
            lengthChange: true,
            serverSide: false,
            processing: false,
            retrieve: true,
            scrollX: true,
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
                FgPopOver.customPophover(".popovers");
                var api = this.api();
                // Output the data for the visible rows to the browser's console
                // localStorage.setItem('formerServicedata_' + clubId + '_'+ contactId , JSON.stringify(api.rows({order:  'applied', search: 'applied', page:   'all'}).data()));    


                $(".dataClass").uniform();
                $(".dataTable_checkall").uniform();
            },
            initComplete: function () {
                FgPopOver.init(".fgPopovers", true, false);
                FgPopOver.init(".fg-dev-Popovers", true);
                FgSponsor.moretabInit('data-tabs', 'data-tabs-content');
                
            }

        };
        ServiceListOpt.serverSide = false;
        ServiceListOpt.processing = false;
        return ServiceListOpt;
    },
    init: function (serviceId, serviceType) {
        $.getJSON($('table.serviceTable').attr('data-ajax-path'), {'serviceType': serviceType, 'serviceId': serviceId}, function (result) {
            /* Set options and draw table for active bookings */
            var opt = FgServiceList.setinitialOpt();
            opt.data = result['aaData']['active'];
            opt.columnDefs = FgServiceList.getcolumndefs('active');
            opt.dom = "<'col-md-12't><'col-md-4 col-sm-4 col-xs-12'i>";

            opt.footerCallback = function (row, data, start, end, display) {
                var api = this.api(), data;
                /*  Total payment for Current FIscal year */
                var paymentCurrentTotal = FgDataTableUtil.getColumnTotal(data, "SA_paymentCurr");
                /*  Total payment for Next FIscal year */
                var paymentNextTotal = FgDataTableUtil.getColumnTotal(data, "SA_paymentNext");
                $(api.column(7).footer()).html(FgClubSettings.getAmountWithCurrency(paymentCurrentTotal));
                $(api.column(8).footer()).html(FgClubSettings.getAmountWithCurrency(paymentNextTotal));

            };
            opt.stateSaveCallback = function (settings, data) {
                var stringified = localStorage.getItem('sponsorlist' + clubId + '-' + contactId)
                var tabType = localStorage.getItem('serviceselectedTab_' + clubId + '_' + contactId)
                var menuType = JSON.parse(stringified || null);

                localStorage.setItem('serviceDatalisting_' + menuType.id + '_' + tabType + '_' + clubId + '_' + contactId, JSON.stringify(data));
            };
            if (!$.isEmptyObject(activeserviceTable)) {
                activeserviceTable.clear();
                activeserviceTable.rows.add(opt.data);
                activeserviceTable.draw();
            } else {
                activeserviceTable = $('[data-tab="active"]').DataTable(opt);
            }
            /* Hide Deposited with column for club type service*/
            if (serviceType == 'club') {
                activeserviceTable.column(4).visible(false);
            } else {
                activeserviceTable.column(4).visible(true);
            }
            var tableInfo = activeserviceTable.page.info();
            $('.fg-active-service-count').html(tableInfo.recordsTotal);
            /* Set options and draw table for future bookings */
            var opt = FgServiceList.setinitialOpt();
            opt.data = result['aaData']['future'];
            opt.columnDefs = FgServiceList.getcolumndefs('future');
            opt.dom = "<'col-md-12't><'col-md-4 col-sm-4 col-xs-12'i>";
            opt.footerCallback = function (row, data, start, end, display) {
                var api = this.api(), data;
                /*  Total payment for Current FIscal year */
                var paymentCurrentTotal = FgDataTableUtil.getColumnTotal(data, "SA_paymentCurr");
                /*  Total payment for Next FIscal year */
                var paymentNextTotal = FgDataTableUtil.getColumnTotal(data, "SA_paymentNext");
                $(api.column(6).footer()).html(FgClubSettings.getAmountWithCurrency(paymentCurrentTotal));
                $(api.column(7).footer()).html(FgClubSettings.getAmountWithCurrency(paymentNextTotal));

            };
            opt.stateSaveCallback = function (settings, data) {
                var stringified = localStorage.getItem('sponsorlist' + clubId + '-' + contactId)
                var tabType = localStorage.getItem('serviceselectedTab_' + clubId + '_' + contactId)
                var menuType = JSON.parse(stringified || null);

                localStorage.setItem('serviceDatalisting_' + menuType.id + '_' + tabType + '_' + clubId + '_' + contactId, JSON.stringify(data));
            };
            if (!$.isEmptyObject(futureserviceTable)) {
                futureserviceTable.clear();
                futureserviceTable.rows.add(opt.data);
                futureserviceTable.draw();
            } else {
                futureserviceTable = $('[data-tab="future"]').DataTable(opt);
            }

            var tableInfo = futureserviceTable.page.info();
            $('.fg-future-service-count').html(tableInfo.recordsTotal);
            /* Set options and draw table for former bookings */
            var opt = FgServiceList.setinitialOpt();
            opt.data = result['aaData']['former'];
            opt.columnDefs = FgServiceList.getcolumndefs('former');
            opt.dom = "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4 col-sm-4 col-xs-12'i><'col-md-8 col-sm-8 col-xs-12'p>";
            opt.paging = true;
            opt.pagingType = "full_numbers";
            opt.footerCallback = function (row, data, start, end, display) {
                var api = this.api(), data;
                /*  Total payment for Current FIscal year */
                var paymentCurrentTotal = FgDataTableUtil.getColumnTotal(data, "SA_paymentCurr");
                $(api.column(5).footer()).html(FgClubSettings.getAmountWithCurrency(paymentCurrentTotal));

            };
            opt.stateSaveCallback = function (settings, data) {
                var stringified = localStorage.getItem('sponsorlist' + clubId + '-' + contactId)
                var tabType = localStorage.getItem('serviceselectedTab_' + clubId + '_' + contactId)
                var menuType = JSON.parse(stringified || null);
                localStorage.setItem('serviceDatalisting_' + menuType.id + '_' + tabType + '_' + clubId + '_' + contactId, JSON.stringify(data));
            };
            if (!$.isEmptyObject(formerserviceTable)) {
                formerserviceTable.clear();
                formerserviceTable.rows.add(opt.data);
                formerserviceTable.draw();
            } else {
                formerserviceTable = $('[data-tab="former"]').DataTable(opt);
                $("#fgfuturerowchange").empty();
                //for change the position of row length
                $("#formertable_length").detach().prependTo("#fgfuturerowchange");
                //add our own classes to the selectbox
                $("#formertable_length").find('select').addClass('form-control cl-bs-select');
                $("#formertable_length").find('select').select2();
            }

            var tableInfo = formerserviceTable.page.info();
            $('.fg-former-service-count').html(tableInfo.recordsTotal);
        });
        //For change the search box field
        $(".searchbox").off("keyup");
        $(".searchbox").on("keyup", function () {         
            var sponsorlistDet = localStorage.getItem(fgLocalStorageNames.sponsor.active.listDetails);
            var sponsorListJson = JSON.parse(sponsorlistDet);
            if (sponsorListJson.type == 'sponsor') {
                if (!$.isEmptyObject(sponsorTable)) {
                    sponsorTable.api().search(this.value).draw();
                } else {
                    FgSponsorTable.init();
                }
            } else if (sponsorListJson.type == 'overview') {
                //overviewTable.search(this.value).draw();
            } else {
                //set the search value to the current localstorage of the particular table
                var stringified = localStorage.getItem('sponsorlist' + clubId + '-' + contactId)
                var menuType = JSON.parse(stringified || null);
                var tabName = localStorage.getItem('serviceselectedTab_' + clubId + '_' + contactId)
                var serviceDataValue = localStorage.getItem('serviceDatalisting_' + menuType.id + '_' + tabName + '_' + clubId + '_' + contactId);
                var serviceData = JSON.parse(serviceDataValue || null);
                if (serviceData) {
                    serviceData.search.search = this.value;
                    localStorage.setItem('serviceDatalisting_' + menuType.id + '_' + tabName + '_' + clubId + '_' + contactId, JSON.stringify(serviceData));
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
    },
    /* Columns for active bookings tab */
    getcolumndefs: function (tabType) {
        var columnDefs = [];
        columnDefs['active'] = [{"name": "edit", "title": '<input type="checkbox" name="check_all" id="check_all" class="dataTable_checkall fg-dev-avoidicon-behaviour" data-type="activeservice"><i class="chk_cnt" ></i>', "width": "1%", "orderable": false, "targets": 0, data: function (row, type, val, meta) {
                    return '<input class="dataClass fg-dev-avoidicon-behaviour" type="checkbox" id=' + row['SA_bookingId'] + ' name="check" data-iscompany ="' + row['Iscompany'] + '" value="0"  >';

                }},
            {"name": "startdate", "targets": 1, data: function (row, type, val, meta) {
                    row.displayData = "<a href='" + row['assignment_edit_url'] + "'>" + row['SA_paymentstartdate'] + "</a>";
                    row.sortData = FgDataTableUtil.getDateTime(row['SA_paymentstartdate']);
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "enddate", type: "null-last", "targets": 2, data: function (row, type, val, meta) {
                    row.displayData = (row['SA_paymentenddate'] != 'null' ? row['SA_paymentenddate'] : '-');
                    row.sortData = row['SA_paymentenddate'] != 'null' ? FgDataTableUtil.getDateTime(row['SA_paymentenddate']) : null;
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "contact", "targets": 3, data: function (row, type, val, meta) {
                    var icons = '';
                    var classes = 'inactive';
                    var fedImage = (row['fedmembershipType'] > 0) ? "&nbsp;<img class ='fg-global-fed-icon' src='"+fedIcon[fedclubId]+"'/>" : '';
                    if (row['Ismember'] == '') {
                        classes = "inactive";
                    } else {
                        if (row['clubmembershipType'] >0 && (currentClubType == 'sub_federation_club' || currentClubType == 'standard_club' || currentClubType == 'federation_club')) {
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

                    return "<div class='fg-contact-wrap'> " + icons + "<a class='fg-dev-contactname' href='" + row['click_url'] + "'>" + row['contactname'] + "</a>"+ fedImage+"</div>";
                }},
            {"name": "depositedwith", "targets": 4, type: "hyphen-last", data: function (row, type, val, meta) {
                    var depositeDetails = row['SA_depositedwith'];
                    var actualData = '-';
                    if (depositeDetails != '' && depositeDetails !== undefined && depositeDetails != null && depositeDetails != '-') {
                        depositeDetails = "[" + depositeDetails + "]";
                        depositeDetails = $.parseJSON(depositeDetails);
                        depositeDetails =  _.sortBy(depositeDetails, function(o) { return moment(o.date, "DD-MM-YYYY").format('YYYYMMDD'); });
                        var displayText = '';
                        var depositedCount = _.size(depositeDetails);
                        if (depositedCount == 1) {
                            actualData = depositeDetails[0]['name'];
                        } else {
                            var depositeType = '';
                            $.each(depositeDetails, function (key, values) {
                                if (key <= 3) {
                                    depositeType = values['type'];
                                    displayText += values['name'] + "<br/>";
                                }
                            });
                            title = (depositeType == 'contact') ? depositedCount + " " + datatabletranslations['contacts'] : depositedCount + " " + datatabletranslations['TeamTitle'];
                            actualData = (depositedCount > 4) ? '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + displayText + '&hellip;" >' + title + ' </i>' : '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + displayText + '" >' + title + ' </i>';

                        }
                    }
                    return actualData;
                }},
            {"name": "payment_plan", "targets": 5, data: function (row, type, val, meta) {
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
            {"name": "next_payment_date", "targets": 6, type: "null-numeric-last", data: function (row, type, val, meta) {
                    var nextpaymentdateArr = (row['SA_paymentDate'] != "") ? row['SA_paymentDate'].split('|') : '-';
                    if (nextpaymentdateArr.length > 1) {
                        row.displayData = nextpaymentdateArr[0] + " (" + FgClubSettings.getAmountWithCurrency(nextpaymentdateArr[1]) + ")";
                    } else {
                        row.displayData = nextpaymentdateArr;
                    }
                    row.sortData = (nextpaymentdateArr.length > 1) ? FgDataTableUtil.getDateTime(nextpaymentdateArr[0]) : null;
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "current_payment", "targets": 7, type: "null-numeric-last", "className": 'text-right', data: function (row, type, val, meta) {
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
            {"name": "future_payment", "targets": 8, type: "null-numeric-last", "className": 'text-right', data: function (row, type, val, meta) {
                    var paymentDetails = row['Nextpayments'];
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
            {"name": "total_payment", type: "null-numeric-last", "targets": 9,  "className": 'text-right', data: function (row, type, val, meta) {
                    var actualData = (row['SA_totalPayment'] != null ? FgClubSettings.getAmountWithCurrency(row['SA_totalPayment']) : '-');
                    var resValue = FgServiceList.getPaymentValues(row, 'SA_totalPayment', 'Totalpayments');
                    row.sortData = (resValue.indexOf('&infin;') >= 0) ? '-1' : resValue != '-' && row['SA_totalPayment'] ? row['SA_totalPayment'] : '';
                    row.displayData = (resValue === '') ? '-' : resValue;
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "", "width": "10%", "targets": 10, "orderable": false, data: function (row, type, val, meta) {
                    return "";
            }}
        ];
        /* Columns for future bookings tab */
        columnDefs['future'] = [{"name": "edit", title: '<input type="checkbox" name="check_all" id="check_all" class="dataTable_checkall fg-dev-avoidicon-behaviour" data-type="futureservice"><i class="chk_cnt" ></i>', "width": "1%", "orderable": false, "targets": 0, data: function (row, type, val, meta) {
                    return '<input class="dataClass fg-dev-avoidicon-behaviour" type="checkbox" id=' + row['SA_bookingId'] + ' name="check" data-iscompany =' + row['Iscompany'] + 'value="0"  >';
                }},
            {"name": "startdate", "targets": 1, data: function (row, type, val, meta) {
                    row.displayData = "<a href='" + row['assignment_edit_url'] + "'>" + row['SA_paymentstartdate'] + "</a>";
                    row.sortData = FgDataTableUtil.getDateTime(row['SA_paymentstartdate']);
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "enddate", type: "null-last", "targets": 2, data: function (row, type, val, meta) {
                    row.displayData = (row['SA_paymentenddate'] != 'null' ? row['SA_paymentenddate'] : '-');
                    row.sortData = row['SA_paymentenddate'] != 'null' ? FgDataTableUtil.getDateTime(row['SA_paymentenddate']) : null;
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "contact", "targets": 3, data: function (row, type, val, meta) {
                    var icons = '';
                    var classes = 'inactive';
                     var fedImage = (row['fedmembershipType'] > 0) ? "&nbsp;<img class ='fg-global-fed-icon' src='"+fedIcon[fedclubId]+"'/>" : '';
                    if (row['Ismember'] == '') {
                        classes = "inactive";
                    } else {
                        if (row['clubmembershipType'] >0 && (currentClubType == 'sub_federation_club' || currentClubType == 'standard_club' || currentClubType == 'federation_club')) {
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

                    return "<div class='fg-contact-wrap'> " + icons + "<a class='fg-dev-contactname' href='" + row['click_url'] + "'>" + row['contactname'] + "</a>"+fedImage+"</div>";
                }},
            {"name": "payment_plan", "targets": 4, data: function (row, type, val, meta) {
                    var paymentplanArr = row['SA_paymentplan'] != 'none' ? row['paymentplanDetails'].split('|') : '';
                    if (row['SA_paymentplan'] == 'custom') {
                        var title = (paymentplanArr[1] > 1) ? datatabletranslations['Payments'] : datatabletranslations['Payment'];
                        return datatabletranslations[paymentplanArr[0]] + " (" + paymentplanArr[1] + " " + title + ")";
                    } else if (row['SA_paymentplan'] == 'regular') {
                        var every_months_text = datatabletranslations['every_months'];
                        return (datatabletranslations[paymentplanArr[0]] + " (" + every_months_text.replace('%month%', paymentplanArr[1]) + ")");
                    } else {
                        return datatabletranslations[row['SA_paymentplan']];
                    }
                }},
            {"name": "next_payment", type: "null-last", "targets": 5, data: function (row, type, val, meta) {
                    var nextpaymentdateArr = (row['SA_paymentDate'] != null) ? row['SA_paymentDate'].split('|') : '-';
                    if (nextpaymentdateArr.length > 1) {
                        row.displayData = nextpaymentdateArr[0] + " (" + FgClubSettings.getAmountWithCurrency(nextpaymentdateArr[1]) + ")";
                    } else {
                        row.displayData = nextpaymentdateArr;
                    }
                    row.sortData = (nextpaymentdateArr.length > 1) ? FgDataTableUtil.getDateTime(nextpaymentdateArr[0]) : null;
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "current_payment", type: "null-numeric-last", "targets": 6, "className": 'text-right', data: function (row, type, val, meta) {
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
                }, render: {"_": 'SA_paymentCurr', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "future_payment", type: "null-numeric-last", "targets": 7, "className": 'text-right', data: function (row, type, val, meta) {
                    var paymentDetails = row['Nextpayments'];
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
            {"name": "total_payment", type: "null-numeric-last",  "targets": 8, "className": 'text-right', data: function (row, type, val, meta) {
                    var actualData = (row['SA_totalPayment'] != null ? FgClubSettings.getAmountWithCurrency(row['SA_totalPayment']) : '-');
                    var resValue = FgServiceList.getPaymentValues(row, 'SA_totalPayment', 'Totalpayments');
                    row.sortData = (resValue.indexOf('&infin;') >= 0) ? '-1' : resValue != '-' && row['SA_totalPayment'] ? row['SA_totalPayment'] : '';
                    row.displayData = (resValue === '') ? '-' : resValue;
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "", "width": "10%", "targets": 9, "orderable": false, data: function (row, type, val, meta) {
                    return "";
            }}
        ];
        /* Columns for former bookings tab */
        columnDefs['former'] = [{"name": "edit", title: '<input type="checkbox" name="check_all" id="sponsor_check_all" class="dataTable_checkall fg-dev-avoidicon-behaviour" data-type="formerservice"><i class="chk_cnt" ></i>', "width": "1%", "orderable": false, "targets": 0, data: function (row, type, val, meta) {
                    return '<input class="dataClass fg-dev-avoidicon-behaviour" type="checkbox" id=' + row['SA_bookingId'] + ' name="check" data-iscompany =' + row['Iscompany'] + 'value="0"  >';
                }},
            {"name": "startdate", "targets": 1, data: function (row, type, val, meta) {
                    row.displayData = "<a href='" + row['assignment_edit_url'] + "'>" + row['SA_paymentstartdate'] + "</a>";
                    row.sortData = FgDataTableUtil.getDateTime(row['SA_paymentstartdate']);
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "enddate", type: "null-last", "targets": 2, data: function (row, type, val, meta) {
                    row.displayData = (row['SA_paymentenddate'] != 'null' ? row['SA_paymentenddate'] : '-');
                    row.sortData = row['SA_paymentenddate'] != 'null' ? FgDataTableUtil.getDateTime(row['SA_paymentenddate']) : null;
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "contact", "targets": 3, data: function (row, type, val, meta) {
                    var icons = '';
                    var classes = 'inactive';
                     var fedImage = (row['fedmembershipType'] > 0) ? "&nbsp;<img class ='fg-global-fed-icon' src='"+fedIcon[fedclubId]+"'/>" : '';
                    if (row['Ismember'] == '') {
                        classes = "inactive";
                    } else {
                        if (row['clubmembershipType'] >0 && (currentClubType == 'sub_federation_club' || currentClubType == 'standard_club' || currentClubType == 'federation_club')) {
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

                    return "<div class='fg-contact-wrap'> " + icons + "<a class='fg-dev-contactname' href='" + row['click_url'] + "'>" + row['contactname'] + "</a>"+fedImage+"</div>";
                }},
            {"name": "payment_plan", "targets": 4, data: function (row, type, val, meta) {
                    var paymentplanArr = row['SA_paymentplan'] != 'none' ? row['paymentplanDetails'].split('|') : '';
                    if (row['SA_paymentplan'] == 'custom') {
                        var title = (paymentplanArr[1] > 1) ? datatabletranslations['Payments'] : datatabletranslations['Payment'];
                        return datatabletranslations[paymentplanArr[0]] + " (" + paymentplanArr[1] + " " + title + ")";
                    } else if (row['SA_paymentplan'] == 'regular') {
                        var every_months_text = datatabletranslations['every_months'];
                        return (datatabletranslations[paymentplanArr[0]] + " (" + every_months_text.replace('%month%', paymentplanArr[1]) + ")");
                    } else {
                        return datatabletranslations[row['SA_paymentplan']];
                    }
                }},
            {"name": "current_payment", type: "null-numeric-last", "targets": 5, "className": 'text-right', data: function (row, type, val, meta) {
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
                }, render: {"_": 'SA_paymentCurr', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "total_payment", type: "null-numeric-last",  "targets": 6, "className": 'text-right', data: function (row, type, val, meta) {
                    var actualData = (row['SA_totalPayment'] != null ? FgClubSettings.getAmountWithCurrency(row['SA_totalPayment']) : '-');
                    var resValue = FgServiceList.getPaymentValues(row, 'SA_totalPayment', 'Totalpayments');
                    row.sortData = (resValue.indexOf('&infin;') >= 0) ? '-1' : resValue != '-' && row['SA_totalPayment'] ? row['SA_totalPayment'] : '';
                    row.displayData = (resValue === '') ? '-' : resValue;
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
            },
            {"name": "", "width": "10%", "targets": 7, "orderable": false, data: function (row, type, val, meta) {
                    return "";
            }}
        ];

        return columnDefs[tabType];

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
            var textValue = infin ? '&infin;' : FgClubSettings.getAmountWithCurrency(row[fieldValue]);
            //itrate over payment array to build popover
            $.each(currPaym, function (key, values) {
                if (key <= 3) {
                    textPayments += values['date'] + ": " + FgClubSettings.getAmountWithCurrency(values['amount']) + "<br/>";
                }
            });
            actualData = (paymntCount > 4) ? '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + textPayments + '&hellip;" >' + textValue + '</i>' : '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + textPayments + '" >' + textValue + ' </i>';

        }
        return  row['SA_paymentplan'] === 'none' ? '-' : actualData;
    },
    //handle the active menu tab while refreshing a page
     handleMoreTabactiveMenu : function (type) {
        $("#data-tabs li").removeClass('active');
        $("#data-tabs li").removeClass('show');
        $("#data-tabs li").removeClass('hidden');
        if(type=="past"){
           type='formerservice';  
        } else if(type=='future'){
           type='futureservice'; 
        } else {
           type='activeservice';  
        }
        
        $("#data-tabs").find("[data-type='"+type+"']").addClass('active');
//        FgMoreMenu.initClientSide('data-tabs');
    }

}
$(function () {
    //for initially set the tab and its dynamic menu
    var activeTab = localStorage.getItem("serviceselectedTab_" + clubId + "_" + contactId);
    if (activeTab === null || activeTab === '' || activeTab == 'undefined') {
        localStorage.setItem("serviceselectedTab_" + clubId + "_" + contactId, 'activeservice');
        $("#activeservice-tab-li").find('a').trigger('click');
        $('.fgContactdrop').attr('data-type', 'activeservice');
    } else {
        $('.fgSponsordrop').attr('data-type', activeTab);
        $("#" + activeTab + "-tab-li").find('a').trigger('click');
        $('.fgContactdrop').attr('data-type', activeTab);
    }
    //for get the dynamic menues data type
    $("ul.fg_sponsor_nav_tab li").on('click', function () {
        $('.fgContactdrop').attr('data-type', $(this).attr('data-type'));

    })

    setTimeout(function () {
        FgCheckBoxClick.init('dataTable');
    }, 200);

});