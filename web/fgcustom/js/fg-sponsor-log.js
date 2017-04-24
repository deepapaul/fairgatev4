

 FgSponsorLogOpt = {
    setinitialOpt :function(){
       fgDataTableInit();
       var opt ={
        deferRender: true,
        order: [[0, "desc"]],
        dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4'i><'col-md-8'p>",
        scrollCollapse: true,
        paging: true,
        autoWidth: true,
        sScrollX: $('table.dataTable-log').attr('xWidth') + "%",
        sScrollXInner: $('table.dataTable-log').attr('xWidth') + "%",
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
        fnDrawCallback: function() {
            FgPopOver.customPophover(".popovers");                  
        }
    };
    return opt;
    }
 }

$(document).ready(function() {
     FgMoreMenu.initClientSideWithNoError('data-tabs', 'data-tabs-content');
     FgMoreMenu.initServerSide('paneltab');        
     
     $('div.date input:enabled').parent().datepicker(FgApp.dateFormat);
     $.getJSON($('table.dataTable-log').attr('data-ajax-path'),function(result){
        $('table.dataTable-log').each(function(){
            var id = $(this).attr('data-id');
            var tableId = $(this).attr('id');
            if($(this).attr('data-tab')== 'data'){
                var opt = FgSponsorLogOpt.setinitialOpt();
                opt.data = result['aaData']['data'];
                opt.columnDefs = columnDefs['data'];
                logTable1 = $(this).DataTable(opt);
                logDateFilterSubmit("date_filter_"+result['aaData']['contact']+"_"+id);
                $("#"+tableId+"_length").detach().prependTo("#fg_contact_log_row_change");
                //add our own classes to the selectbox
                $("#"+tableId+"_length").find('select').addClass('form-control cl-bs-select');
                $("#"+tableId+"_length").find('select').select2();
            } else {
                $("#"+tableId+"_length").detach();
            }
            if($(this).attr('data-tab')== 'services'){
                var opt =FgSponsorLogOpt.setinitialOpt();
                opt.data = result['aaData']['services'];
                opt.columnDefs = columnDefs['services'];
                logTable2 = $(this).DataTable(opt);
                logDateFilterSubmit("date_filter_"+result['aaData']['contact']+"_"+id);
            }
        });
    });
    $('.commonLogClass').on('click',function(){
        var id = $(this).attr('href');
        var tab = id.split("_");
        setTimeout(function(){
            
            if($('#log_display_'+tab['1']+'_'+tab['2']).attr('data-tab') == 'data'){
                logTable1.columns.adjust().draw();
                 logDateFilterSubmit("date_filter_"+tab['1']+"_"+tab['2']);
            }     
            
            if($('#log_display_'+tab['1']+'_'+tab['2']).attr('data-tab') == 'services'){
                logTable2.columns.adjust().draw();
                logDateFilterSubmit("date_filter_"+tab['1']+"_"+tab['2']);
            }  
            
            
        },1); 
    });
    $('#fg_contact_log_date_filter_flag').on('click', function(){
        if ($(this).is(':checked')) {
           $('#fg_contact_log_date_filter_flag').attr('checked', true);
           //update the property of the checkbox of jquery uniform plugin
           $.uniform.update('#fg_contact_log_date_filter_flag');
       } else {
           $('#fg_contact_log_date_filter_flag').attr('checked', false);
           //update the property of the checkbox of jquery uniform plugin
           $.uniform.update('#fg_contact_log_date_filter_flag');
       }
       $('.log-area').toggleClass('show');
       $('table.table').toggleClass('fg-common-top');
    });
    

    $(document).on('click', '#data-tabs li', function(event) {
        if($(this).attr('id') == "data_li_4") {
            $(".fg-log-filter").hide();
        } else {
            $(".fg-log-filter").show();
        }
    });
    $('#log_display_'+contactId+'_1').on( 'length.dt', function ( e, settings, len ) {
      
        logTable2.page.len(len).draw();
    });

});


