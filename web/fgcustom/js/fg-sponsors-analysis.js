var opt,smTable,saTable;
var smTableFixed;
var sponsorAnalysisData = {};
var csvValue = {};
var columnDefs=[];
FgSponsorAnalysis = {
    handleYrTab : function(){
        $('#past-tab .dropdown-menu li').on('click',function(){
            live = $(this).attr('id');
            $('#past-tab > .dropdown-menu ').find('.hide').addClass('show').removeClass('active').removeClass('hide');
            $(this).removeClass('show').addClass('hide');
            $('#data-tabss').find('.past').removeClass('active').removeClass('show').addClass('hide').removeClass('past');
            $('#data-tabss').find('.active').removeClass('active');
            $('#data-tabss').find('#'+live).removeClass('hide').addClass('show active past');
            FgSponsorAnalysis.handlePastTabShow();
            return false;

        });
        $('#data-tabss > li:lt(3)').on('click',function(){
            $('#past-tab > .dropdown-menu ').find('.hide').addClass('show').removeClass('active').removeClass('hide');
            $('#data-tabss > li:gt(2)').not('#past-tab').removeClass('active').removeClass('show').removeClass('past').addClass('hide');
            FgSponsorAnalysis.handlePastTabShow();
        });



    },
    handlePastTabShow:function(){
         if($('#past-tab ul').find('.show').length){
            $('#past-tab').removeClass('hide').addClass('show');
        }else{
            $('#past-tab').removeClass('show').addClass('hide');
        }

    }
}
FgSMOpt = {
    setinitialOpt: function() {
        //fgDataTableInit();
        opt = {
            deferRender     : true,
            order           : [[1, "asc"]],
            dom             : "<'row_select_datatow col-md-12'l><'col-md-12't>",
            scrollCollapse  : true,
            paging          : false,
            scrollY         : FgCommon.getWindowHeight(275) + "px",
            stateSave       : true,
            stateDuration   : 60 * 60 * 24,
            lengthChange    : true,
            serverSide      : false,
            processing      : false,
            retrieve        : true,
            language        : {
                sInfo        : datatabletranslations['data_showing'] + " <span>_START_</span> " + datatabletranslations['data_to'] + " <span>_END_</span> " + datatabletranslations['data_of'] + " <span>_TOTAL_</span> " + datatabletranslations['data_entries'],
                sZeroRecords : datatabletranslations['no_matching_records'],
                sInfoEmpty   : datatabletranslations['data_showing'] + " <span>0</span> " + datatabletranslations['data_to'] + " <span>0</span> " + datatabletranslations['data_of'] + " <span>0</span> " + datatabletranslations['data_entries'],
                sEmptyTable  : datatabletranslations['no_record'],
                sInfoFiltered: "(" + datatabletranslations['filtered_from'] + " <span>_MAX_</span> " + datatabletranslations['total_entries'] + ")",
            },
            drawCallback: function() {
                FgPopOver.customPophover(".popovers");
            }
        };
        return opt;
    },
    /* Get total amount for a column*/
    getColumnTotal: function(data, column) {
        var paymentCurrentTotalArray = _.pluck(data, column);
        var paymentCurrentTotal = 0;
        _.each(paymentCurrentTotalArray, function(value) {
            value = parseFloat(value);
            paymentCurrentTotal += (!isNaN(value) ? value : 0);
        });
        return paymentCurrentTotal;
    }
}
    columnDefs['service']= [
                { "name": "category","width" : "20%",  "targets": 0 , data:function(row, type, val, meta){
            row['order'] = row['cOrder'];
                        row['firstCol'] = "<input type='hidden' class='dataClass'>"+row['category'];
                    return  row['category']=='' || row['category'] == null ? '-':row; 
                },  render:{"_": 'order', "display": 'firstCol','filter': 'category' }},
                { "name": "service", "width" : "20%", "targets": 1 , data:function(row, type, val, meta){
                       var sPath1 = sPath.replace(/%23catId%23/g,row['catId']); 
                    return  row['service']=='' || row['service'] == null ? '-':'<a href="'+sPath1+'" >'+row['service']+'</a>'; 
        }},
                { "name": "sponsors", "width" : "20%",  "targets": 2, data:function(row, type, val, meta){
                    if(row['sponsors']>0){
                        var splitSD= row['sponsorDetails'].split("<br/>");
                        if(_.size(splitSD)>5){
                          row['display']= '<span data-original-title="" data-content="'+
                                   splitSD[0]+" <br/>"+splitSD[1]+" <br/>"+splitSD[2]+" <br/>"+splitSD[3]+" <br/>"+splitSD[4]+"<br/>..."+ '"data-container="body"  data-trigger="hover" class="popovers fg-dotted-br">'+row['sponsors']+'</i> '
                        }else{
                        row['display']= '<span data-original-title="" data-content="'+
                                   row['sponsorDetails']+ '"data-container="body"  data-trigger="hover" class="popovers fg-dotted-br">'+row['sponsors']+'</i> '
                }
                    }else{     
                        row['display']= row['sponsors']=='' || row['sponsors'] == null ? '-':row['sponsors']; 
                    } return row;
                    },  render:{"_": 'sponsors', "display": 'display','filter': 'payments' }},
                { "name": "payments", "width" : "20%",  "targets": 3, data:function(row, type, val, meta){
                    if(row['payments'] > 0){
                        var servicesPath1 = servicesPath.replace('%23catId%23',row['catId']);
                        var payDtls = '['+row['paymentDetails']+']';
                        var tooltipVal='';
                        var splitPY= JSON.parse(payDtls);
                        _.each(splitPY, function(value,key) {
                            if(key<=4){
                                tooltipVal += value.date+': '+FgClubSettings.getAmountWithCurrency(value.amount)+' '+value.name+' <br/>';
                                if(key == 4)
                                    tooltipVal +='...';
                        return true;
                    }
                });
                        row['display']= '<span data-original-title="" data-content="'+
                                   tooltipVal+ '"data-container="body"  data-trigger="hover" class="popovers fg-dotted-br">'+row['payments']+'</i> '
                    }else{
                        row['display']=  row['payments']=='' || row['payments'] == null ? '-':row['payments']; 
            }
            return row;
                },  render:{"_": 'payments', "display": 'display','filter': 'payments' }},
                { "name": "amt", "className":"text-right","width" : "20%",  "targets": 4, data:function(row, type, val, meta){
                       if(row['amt']!='' || row['amt'] != null ){ 
                             row['displayAmt']= FgClubSettings.getAmountWithCurrency(row['amt']);//currency+ " "+row['amt'];
                         }else{
                             row['displayAmt']='-';
            }
                return  row['amt']=='' || row['amt'] == null ? '-':row; 
            },  render:{"_": 'amt', "display": 'displayAmt','filter': 'amt' }}
];
        columnDefs['sponsor']= [
            { "name":"hidden",type: "input", orderable: false,  targets: 0, data:function(row, type, val, meta){
            return "<input type='hidden' >";
            } },
            { "name": "contact",  "targets": 1 , data:function(row, type, val, meta){
                    overviewLink = overviewPath1.replace('dummyContactId',row['contactId']); 
                 if(row['company']=='0'){
                     row['display']='<i class="fa fa-user"></i>  <a target="_blank" href="'+ overviewLink +'">'+row['contact']+'</a>';
                 } else{
                     row['display']='<i class="fa fa-building-o"></i>   <a target="_blank" href="'+ overviewLink +'">'+row['contact']+'</a>';
            }
                return  row ;
                },  render:{"_": 'contact', "display": 'display','filter': 'contact'  }} 
];

            serviceGroup.contact='';
            serviceGroup.rowTotal=0;
            var i=2;
            _.each(catHead,function(value){ 
                columnDefs['sponsor'].push({ "name": value.servicesId, "className":"text-right", "targets": i , data:function(row, type, val, meta){
                    row['display']=  (row[value.servicesId] === '' || row[value.servicesId] === null) ? '-':FgClubSettings.getAmountWithCurrency(row[value.servicesId]); 
            return row;
             },  render:{"_": value.servicesId, "display": 'display','filter': value.servicesId }} ) ;
    i++;
                serviceGroup[value.servicesId]='';
});
            columnDefs['sponsor'].push({ "name": "total",  "targets": i, "className":"text-right fg-border-left", data:function(row, type, val, meta){
        row['rowT'] = FgClubSettings.getAmountWithCurrency(row['rowTotal']);
                    return  row ;
                },  render:{"_": 'rowTotal', "display": 'rowT','filter': 'rowTotal'

            }} );
$(document).ready(function() {
    FgSponsorAnalysis.handleYrTab();
    init();
    //service tab
    function init() {
        var finalPath = servicePath.replace('%23startDate%23', startDate).replace('%23endDate%23', endDate);
        $.getJSON(finalPath, function(result) {
            var opt = FgSMOpt.setinitialOpt();
            opt.data = result['aaData']['service'];
            opt.columnDefs = columnDefs['service'];
            opt.footerCallback = function(row, data, start, end, display) {
                var api = this.api(), data;
                /*  Total payment for FIscal year */
                var paymentCurrentTotal = FgSMOpt.getColumnTotal(data, "amt");
                $(api.column(4).footer()).html(FgClubSettings.getAmountWithCurrency(paymentCurrentTotal));
                $(api.column(4).footer()).addClass('fg-datatable-footer-grey');
            };
            opt.drawCallback = function() {
                FgPopOver.customPophover(".popovers");
            }

            if (!$.isEmptyObject(smTable)) {
                 $('.dataTables_scrollHeadInner table thead tr th:last-child ').css('opacity',1);
                smTable.destroy();
                smTable.clear();
                smTable = $("[data-tab = 'service']").DataTable(opt);
            } else {
                smTable = $("[data-tab = 'service']").DataTable(opt);
            }
        });
    }

    //more tabs
    $('#data-tabss li a').not("#fg-contact-more-tab").on('click', function () {
        startDate = $(this).attr('data-startdate');
        endDate = $(this).attr('data-enddate');
        var activeTabId = $('.commonSMClass').parent('.active').attr('id');
        if (activeTabId == 'data_li_0') {
            $('.commonSMClass[type_id=0]').trigger('click');
        } else {
            $('.commonSMClass[type_id=1]').trigger('click');
        }
//        $('.commonSMClass[type_id=0]').trigger('click');
    });

    //sponsor tab
    $('.commonSMClass[type_id=1]').click(function() {
        var finalPath = sponsorPath.replace('%23startDate%23', startDate).replace('%23endDate%23', endDate);
        $.getJSON(finalPath, function(result) {
            var sponsorGroup = _.groupBy(result.sponsor, "contactId");
            //get sum of service column totals
            var serviceSum = {};
            _.each(serviceGroup, function(val, key) {
                serviceSum[key] = 0;
            });
            serviceSum['colTotal'] = 0;

            _.each(sponsorGroup, function(val, key) {
                var defaultSum = 0;
                _.each(val, function(val1, key1) {
                    defaultSum = defaultSum + parseFloat(val1['amt']);
                });
                sponsorGroup[key]['rowTotal'] = defaultSum;
            });

            sponsorAnalysisData = _.map(sponsorGroup, function(val) {
                var defaultData = _.clone(serviceGroup);
                defaultData.contactId = val[0].contactId;
                defaultData.contact = val[0].contactName;
                defaultData.rowTotal = val.rowTotal;
                defaultData.company = val[0].company;
                //calculate colTotal
                _.each(val, function(val2, key) {
                    defaultData[val2.serviceId] = parseFloat(val2.amt);
                    serviceSum[val2.serviceId] = serviceSum[val2.serviceId] + parseFloat(val2.amt);
                });
                serviceSum['colTotal'] = serviceSum['colTotal'] + parseFloat(defaultData.rowTotal);
                /*end*/
                return defaultData;
            });

            var opt = FgSMOpt.setinitialOpt();
            opt.scrollX = true;
            opt.sScrollX = $('table.dataTable-sm').attr('xWidth') + "%";
            opt.dom = "<'row_select_datatow col-md-12'l><'col-md-12't>";
            csvValue = _.sortBy(sponsorAnalysisData, function (i) { return i.contact.toLowerCase(); });
            opt.data = sponsorAnalysisData;
            opt.columnDefs = columnDefs['sponsor'];
            opt.fixedHeader =true;
            opt.autoWidth = true;
            if ($(window).width() >= 768){
              opt.fixedColumns ={
                    leftColumns: 2,
                    rightColumns: 1
                }
            }
            opt.footerCallback = function(row, data, start, end, display) {
                _.each(serviceSum, function(val, key) {
                    if (key == 'colTotal') {
                        $("#colTotal").html(FgClubSettings.getAmountWithCurrency(val));
                    } else {
                        $("#servid_" + key).html(FgClubSettings.getAmountWithCurrency(val));
                    }
                });
            };
             opt.drawCallback = function() {
                FgPopOver.customPophover(".popovers");
                if (!$.isEmptyObject(saTable) && ($(window).width() >= 768)) {
                    if (typeof saTable.columns.adjust().fixedColumns == 'function') {
                        saTable.columns.adjust().fixedColumns().relayout();
                    } else {
                        saTable.columns.adjust();
                    }
                }

            }
            opt.fnRowCallback = function(nRow, aData, iDataIndex) {
                //give - value to the null 
                var prevCat='';
                var additionalClass = '';
                var i=3;
                _.each(catHead,function(value){ 
                    if (prevCat == value.categoryId){
                        additionalClass = '';
                    }else{
                        additionalClass = 'fg-border-left';
                    }
                    $(nRow).each(function(index, value) {
                        $(this).find('td.text-right:nth-child('+i+')').addClass(additionalClass);
                        i++;
                    });
                    prevCat = value.categoryId;
                });
            };
            if (!$.isEmptyObject(saTable)) {
                 $('.dataTables_scrollHeadInner table thead tr th:last-child ').css('opacity',1);
                saTable.destroy();
                saTable.clear();
            }
            saTable = $("[data-tab = 'sponsor']").DataTable(opt);
            console.log(saTable);

           if (($(window).width() >= 768) && (!$.isEmptyObject(saTable)) ){
                saTable.columns.adjust().fixedColumns().relayout();              
               // setTimeout(function(){
                    $('.dataTables_scroll .dataTables_scrollHead .table  thead  tr  th:last-child').css('opacity',0);
               // },1000);
               
            }
 $(document).off('mouseenter','.dataTables_wrapper tr');  
 $(document).off('mouseleave','.dataTables_wrapper tr'); 
            //for create the mouse over effect on the both fixed column and normal table
            $(document).on({
    mouseenter: function() {
                    trIndex = $(this).index() + 3;
        $(".DTFC_ScrollWrapper .dataTables_scrollBody .table").each(function(index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                            $(this).find("td").addClass("fghover");
                            $(this).find("td").addClass('fg-dev-td-hover');
                        });
                    });
                    trIndex = $(this).index() + 2;
        $(".DTFC_ScrollWrapper .DTFC_LeftBodyWrapper .table").each(function(index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                            $(this).find("td").addClass("fghover");
                            $(this).find("td").addClass('fg-dev-td-hover');
                        });
                    });
        $(".DTFC_ScrollWrapper .DTFC_RightBodyWrapper .table").each(function(index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                            $(this).find("td").addClass("fghover");
                            $(this).find("td").addClass('fg-dev-td-hover');
                        });
                    });
                },
    mouseleave: function() {
                    trIndex = $(this).index() + 3;
        $(".DTFC_ScrollWrapper .dataTables_scrollBody .table").each(function(index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                            $(this).find("td").removeClass("fghover");
                $(this).css('background-color','')
                        });
                    });
                    trIndex = $(this).index() + 2;
        $(".DTFC_ScrollWrapper .DTFC_LeftBodyWrapper .table").each(function(index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                            $(this).find("td").removeClass("fghover");
                $(this).css('background-color','')
                        });
                    });
        $(".DTFC_ScrollWrapper .DTFC_RightBodyWrapper .table").each(function(index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                            $(this).find("td").removeClass("fghover");
                $(this).css('background-color','')
                        });
                    });
                    $("table.dataTable").find('.fg-dev-td-hover').removeClass('fg-dev-td-hover');
                }

            }, ".dataTables_wrapper tr");


        });


    });
    //click service tab
    $('.commonSMClass[type_id=0]').click(function() {
        init();
    });

});
