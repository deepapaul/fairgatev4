var data;
var opt;
var tabledf = '';
var tableId = '';
var totalvalues=[];
totalvalues['fg-dev-dataTable-active']=[];
totalvalues['fg-dev-dataTable-future']=[];
totalvalues['fg-dev-dataTable-past']=[];
 FgContactServiceOpt = {
    /**
     * Function to initialise data table config array
     * 
     * @returns {opt}
     */
    setinitialOpt :function(){
       fgDataTableInit();
       opt ={
        language: {
            sSearch: "<span>" + datatabletranslations['data_Search'] + ":</span> ",
            sInfo: datatabletranslations['data_showing'] + " <span>_START_</span> " + datatabletranslations['data_to'] + " <span>_END_</span> " + datatabletranslations['data_of'] + " <span>_TOTAL_</span> " + datatabletranslations['data_entries'],
            sZeroRecords: datatabletranslations['no_matching_records'],
            sInfoEmpty: datatabletranslations['data_showing'] + " <span>0</span> " + datatabletranslations['data_to'] + " <span>0</span> " + datatabletranslations['data_of'] + " <span>0</span> " + datatabletranslations['data_entries'],
            sEmptyTable: datatabletranslations['no_record'],
            sInfoFiltered: "(" + datatabletranslations['filtered_from'] + " <span>_MAX_</span> " + datatabletranslations['total_entries'] + ")",
            oPaginate: {
                "sFirst": '<i class="fa fa-angle-double-left"></i>',
                "sLast": '<i class="fa fa-angle-double-right"></i>',
                "sNext": '<i class="fa fa-angle-right"></i>',
                "sPrevious": '<i class="fa fa-angle-left"></i>'
            }

        },  
        deferRender: true,
        order: [[1, "asc"]],
        dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4'i><'col-md-8'p>",
        scrollCollapse: true,
        paging: false,
        autoWidth: true,
        sScrollX: $('table[data-contact-service]').attr('xWidth') + "%",
        sScrollXInner: $('table[data-contact-service]').attr('xWidth') + "%",
        scrollY: FgCommon.getWindowHeight(275) + "px",
        stateSave: true,
        deferRender: true,
        stateDuration: 60 * 60 * 24,
        lengthChange: true,
        serverSide: false,
        processing: false,
        pagingType: "full_numbers",
        retrieve : true,
        scrollX : true,
        fnDrawCallback: function() {
            FgPopOver.customPophover(".fg-dev-Popovers");    
            $(".dataClass").uniform();
            $('input[type=checkbox].dataTable_checkall').uniform();
        },
        footerCallback: function( tfoot, data, start, end, display ) {
            FgContactServiceOpt.setTotalValueArray(data,tfoot)
        },
        fnInitComplete : function() {
            
        }
    };
    return opt;
    },
    /**
     * function to get payment plan value 
     * 
     * @param {type} row        value array of a row
     * @param {type} fieldValue field name of value
     * @param {type} fieldJson  filed name name of json array
     * @returns {String}
     */
    getPaymentValues :function(row,fieldValue,fieldJson,sTableId){
        var currPaym = row[fieldJson];
        var actualData ='';
        var infin=false;
        //set value as infinity when plan is regular and end date is null
        if(fieldValue=='SA_totalPayment' && row['SA_paymentplan']=='regular' && ((row['SA_enddate'] == 'null' || row['SA_enddate'] == '' || row['SA_enddate'] == null) && (row['SA_last_payment_date'] == 'null' || row['SA_last_payment_date'] == '' || row['SA_last_payment_date'] == null))){
            infin=true;
        }
        if (currPaym != '' && currPaym !== undefined && currPaym != null && row['SA_paymentplan'] !=='none') {
            currPaym = "[" + currPaym + "]";
            currPaym = $.parseJSON(currPaym);
            currPaym =  _.sortBy(currPaym, function(o) { return moment(o.date, "DD-MM-YYYY").format('YYYYMMDD'); });
            var textPayments = '';
            var paymntCount = _.size(currPaym);
            var textValue=infin ? ' &infin; ':FgClubSettings.getAmountWithCurrency(row[fieldValue]);
            //itrate over payment array to build popover
            $.each(currPaym, function (key, values) {                                
                if (key <= 3) {
                    textPayments += values['date']+": "+FgClubSettings.getAmountWithCurrency(values['amount'])+"<br/>";
                }
            });
            actualData = (paymntCount > 4) ? '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + textPayments + '&hellip;" >'+textValue+'</i>' : '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + textPayments + '" >'+ textValue + ' </i>';

        }
        return  row['SA_paymentplan']==='none' ? '-':actualData;
    },
   
    /**
     * Function to handle total selected row count
     * @param {type} e this
     */
    updateTotalCount:function(e){
        var totalCount=$(e).parents('[data-service-list]').find('input.dataClass:checked').length;
        if(totalCount>0){
            $(e).parents('[data-service-list]').find('i.chk_cnt').html(totalCount);
        } else {
            $(e).parents('[data-service-list]').find('i.chk_cnt').html('');
        }
    },
    /**
     * Function to define column definition of datatables
     */
    setColumnDef:function(tableType){
        if(tableType=='activesponsor'||tableType=='future'){
        columnDefs[tableType] =  [
        {"name": "edit", "width": "5%", orderable: false, "targets": 0, data: function (row, type, val, meta) {
            return '<div class="fg-td-wrap"> <i class="fg-sort"></i>&nbsp;<input class="dataClass fg-dev-avoidicon-behaviour" type="checkbox" id=' + row['SA_bookingId'] + ' name="check'+row['SA_bookingId']+'" value="1"  ></div>';

        }},
        { "name": "startDate", "targets": 1 ,"type":"null-numeric-last", data:function(row, type, val, meta){ 
            row.sortData = row['SA_startdate']==='null'? '':FgDataTableUtil.getDateTime(row['SA_startdate']);
            if(row['SA_startdate']!=='null') {
                row.displayData = (typeof actionMenuSingleSelectedText.editService != typeof undefined) ? '<a href="#" data-edit-service-link="'+actionMenuSingleSelectedText.editService.dataUrl.replace('BOOKINGID',row['SA_bookingId']+'">'+row['SA_startdate']+'</a>') :row['SA_startdate'];
            } else {
                row.displayData = '-';
            }
            return row;}, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'} },
        { "name": "endDate", "targets": 2,"type":"null-numeric-last", data:function(row, type, val, meta){ 
            row.sortData = row['SA_enddate']==='null'? '':FgDataTableUtil.getDateTime(row['SA_enddate']);
            row.displayData = row['SA_enddate']==='null'? '-':row['SA_enddate'];
            return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}},
        { "name": "service", "targets": 3 , data:function(row, type, val, meta){ 
            row.sortData = row['SA_serviceTitle'];
            row.displayData = '<a type="service" service_type="'+tableType+'" service_id="'+row['SA_serviceId']+'" catid="'+row['SA_serviceCatId']+'" href="#" onclick=FgContactServiceOpt.handleServiceClick(this,"'+tableType+'")>'+row['SA_serviceTitle']+'</a>';
            return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}},
        { "name": "paymentPlan", "targets": 4, data:function(row, type, val, meta){
                switch(row['SA_paymentplan']){
                    case 'custom':
                        var plan= row['paymentplanDetails'].split('|');
                        returnValue = plan[1]=='1' ? customText+' (1 '+paymText+')':customText+' ('+plan[1]+' '+paymentText+')';
                        break;
                    case 'regular':
                        var plan= row['paymentplanDetails'].split('|');
                        returnValue = regularText+' ('+regularMonths.replace('%month%',plan[1])+')';
                        break;
                    default:
                       returnValue= noneText;
                        break;
                }
                return  returnValue; }
        },
        { "name": "nextPayment", "targets": 5, "type":"null-numeric-last", data:function(row, type, val, meta){ 
                row['sortData'] = returnValue='';
                if(row['SA_nextPaymentDate']){
                    var nextPay= row['SA_nextPaymentDate'].split('|');
                    returnValue= nextPay[0]+' ('+FgClubSettings.getAmountWithCurrency(nextPay[1])+')';
                    row.sortData = FgDataTableUtil.getDateTime(nextPay[0]);
                }
                row.displayData =  row['SA_paymentplan'] !=='none' && returnValue !==''  ? returnValue: '-'; 
                row.sortData=row['SA_paymentplan'] ==='none' && returnValue ===''  ? '':row.sortData;
                return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
        },
        { "name": "paymentCurrent", "targets": 6,"type":"null-numeric-last", data:function(row, type, val, meta){  
            var resValue = FgContactServiceOpt.getPaymentValues(row,'SA_paymentCurr','Currentpayments',meta.settings.sTableId);
            row.displayData = resValue==='' ? '-':resValue;
            row.sortData = resValue==='-' ? '':row['SA_paymentCurr'];
            return row;
            },render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
        },
        { "name": "paymentNext", "targets": 7, "type":"null-numeric-last", data:function(row, type, val, meta){  
            var resValue =  FgContactServiceOpt.getPaymentValues(row,'SA_paymentNex','Nextpayments',meta.settings.sTableId);
            row.displayData = resValue==='' ? '-':resValue;
            row.sortData = resValue=='-'||resValue=='' ? '':row['SA_paymentNex'];
            return row;
            },render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}
        },
        { "name": "totalPayment","targets": 8, "type":"null-numeric-last", data:function(row, type, val, meta){  //console.log(meta.settings.sTableId);
            var resValue =  FgContactServiceOpt.getPaymentValues(row,'SA_totalPayment','Totalpayments',meta.settings.sTableId);
            row.displayData = resValue==='' ? '-':resValue;
            row.sortData =  (resValue.indexOf(' &infin; ') >= 0) ? '-1':resValue !='-' && row['SA_totalPayment'] ? row['SA_totalPayment']:'';
            return row;
            },render: {"_": 'sortData', "display": 'displayData'}
        }
         ];
     } else {
         //set column definition for past
    columnDefs['past'] = [
        {"name": "edit", "width": "5%", orderable: false, "targets": 0, data: function (row, type, val, meta) {
            return '<div class="fg-td-wrap"><i class="fg-sort"></i>&nbsp;<input class="dataClass fg-dev-avoidicon-behaviour" type="checkbox" id=' + row['SA_bookingId'] + ' name="check'+row['SA_bookingId']+'" value="1"  ></div>';

        }},
        { "name": "startDate", "targets": 1 , data:function(row, type, val, meta){
            row.sortData = row['SA_startdate']==='null'? '':FgDataTableUtil.getDateTime(row['SA_startdate']);
            if(row['SA_startdate']!=='null') {
                row.displayData = (typeof actionMenuSingleSelectedText.editService.dataUrl != typeof undefined) ? '<a href="#" data-edit-service-link="'+actionMenuSingleSelectedText.editService.dataUrl.replace('BOOKINGID',row['SA_bookingId']+'">'+row['SA_startdate']+'</a>') :row['SA_startdate'];
            } else {
                row.displayData = '-';
            }
            return row;
        }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'} },
        { "name": "endDate", "targets": 2, data:function(row, type, val, meta){ 
            row.sortData = row['SA_enddate']==='null'? '':FgDataTableUtil.getDateTime(row['SA_enddate']);
            row.displayData = row['SA_enddate']==='null'? '-':row['SA_enddate'];
            return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}},
        { "name": "service", "targets": 3 , data:function(row, type, val, meta){ 
            row.sortData = row['SA_serviceTitle'];
            tabtype='past';
            row.displayData = '<a type="service" service_type="past" service_id="'+row['SA_serviceId']+'" catid="'+row['SA_serviceCatId']+'" href="#" onclick=FgContactServiceOpt.handleServiceClick(this,"'+tabtype+'")>'+row['SA_serviceTitle']+'</a>';
            return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}},
        { "name": "paymentPlan", "targets": 4, data:function(row, type, val, meta){
                switch(row['SA_paymentplan']){
                    case 'custom':
                        var plan= row['paymentplanDetails'].split('|');
                        returnValue = plan[1]=='1' ? customText+' (1 '+paymText+')':customText+' ('+plan[1]+' '+paymentText+')';
                        break;
                    case 'regular':
                        var plan= row['paymentplanDetails'].split('|');
                        returnValue = regularText+' ('+regularMonths.replace('%month%',plan[1])+')';
                        break;
                    default:
                       returnValue= noneText;
                        break;
                }
                return  returnValue; }
        },
        { "name": "totalPayment","targets": 5,"type":"null-numeric-last", data:function(row, type, val, meta){ 
                var resValue =  FgContactServiceOpt.getPaymentValues(row,'SA_totalPayment','Totalpayments',meta.settings.sTableId);
                row.displayData = resValue==='' ? '-':resValue;
                row.sortData = resValue !='-' && row['SA_totalPayment'] ? row['SA_totalPayment']:'';
                return row;
                },render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'} 
        }
         ];
     }
    },
    /**
     * Init action menu 
     */
    initActionMenu:function(){
        var actionMenuText = {'activesponsor' : {'none': actionMenuNoneSelectedText, 'single': actionMenuSingleSelectedText, 'multiple': actionMenuMultipleSelectedText},
            'future' : {'none': pastMenuNoneSelectedText, 'single': pastMenuSingleSelectedText, 'multiple': pastMenuMultipleSelectedText},
            'past' : {'none': pastMenuNoneSelectedText, 'single': pastMenuSingleSelectedText, 'multiple': pastMenuMultipleSelectedText}
            };
        FgSidebar.dynamicMenus.push({actionmenu: actionMenuText});
    },
    /**
     * function to handle click on service start date to edit assignment page with back functionality
     */
    handleServiceLinkNavigation:function(){
        $('body').on('click', 'a[data-edit-service-link]', function() {
            redirectPath=$(this).attr('data-edit-service-link');
            if (redirectPath != '' && typeof CurrentContactId !== typeof undefined) {
                var form = $('<form action="' + redirectPath + '" method="post">' +
                '<input type="hidden" name="backTo" value="'+CurrentContactId+'|'+CurrentOffset+'" />' +
                '</form>');
                $('body').append(form);
                $('form').submit();
            } else {
                window.location  = redirectPath;
            }
        });
    },
    /**
     * Function to set datatable footer with total value
     * 
     * @param {type} data  Data array
     * @param {type} tfoot Footer object
     */
    setTotalValueArray:function(data,tfoot){
        var tableType=$(tfoot).find('th[data-total]').attr('data-total');
        var totalSum = nextSum = currSum = 0;
        $.each(data, function (key,values) { 
            if(values['SA_paymentNex'] !=='none'){
                if(tableType !=='past') {
                   nextSum = isNaN(parseFloat(values['SA_paymentNex'])) ? nextSum:nextSum+parseFloat(values['SA_paymentNex']);
                   currSum = isNaN(parseFloat(values['SA_paymentCurr'])) ? currSum: currSum+parseFloat(values['SA_paymentCurr']);
                } 
                totalSum = isNaN(parseFloat(values['SA_totalPayment'])) ? totalSum :totalSum+parseFloat(values['SA_totalPayment']);
            }
        });
        if(totalSum>0 && tableType ==='past') {
            $(tfoot).find('th[data-total]').html(FgClubSettings.getAmountWithCurrency(totalSum.toFixed(2)));
        }
        if(tableType !=='past'){ 
            if(nextSum>0){ 
                $(tfoot).find('th[data-next-total]').html(FgClubSettings.getAmountWithCurrency(nextSum.toFixed(2)));
            }
            if(currSum>0) { 
                $(tfoot).find('th[data-current-total]').html(FgClubSettings.getAmountWithCurrency(currSum.toFixed(2)));
            }
        }

    },
    handleServiceClick:function(me,type){
        localStorage.setItem('clickedServiceTab-'+thisClubId+'-'+loggedContactId,type);
        FgSponsor.handlesidebarclick(me,serviceListUrl,thisClubId,loggedContactId)
    }
 }

$(document).ready(function() {
     FgContactServiceOpt.setColumnDef('activesponsor');
     FgContactServiceOpt.setColumnDef('past');
     FgContactServiceOpt.setColumnDef('future');
     FgContactServiceOpt.initActionMenu();
     FgContactServiceOpt.handleServiceLinkNavigation();
     //get services list
     $.getJSON(datapath,function(data){
        $('table[data-contact-service]').each(function(){
            var tableId = $(this).attr('data-contact-service');
            var opt =FgContactServiceOpt.setinitialOpt();
            opt.data = data[tableId];
            opt.columnDefs = columnDefs[tableId];
            if(data[tableId].length==0){
                $('div[data-type='+tableId+']').hide();
                $('div[data-empty='+tableId+']').show();
            } else {
                $('div[data-type='+tableId+']').show();
                $('div[data-empty='+tableId+']').hide();
            }
            serviceTable[tableId] = $(this).DataTable(opt);
        });
    });
    setTimeout(function () {
                FgCheckBoxClick.init('fg-dev-sponsor-overview-dataTable');
            }, 200);

});
 $(window).resize(function(){
    if(typeof serviceTable.activesponsor !== typeof undefined) {
        serviceTable.activesponsor.draw();
    }
    if(typeof serviceTable.future !== typeof undefined) {
        serviceTable.future.draw();
    }
    if(typeof serviceTable.past !== typeof undefined) {
        serviceTable.past.draw();
    }
        
 });
