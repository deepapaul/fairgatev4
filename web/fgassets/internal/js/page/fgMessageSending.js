var fgMessageSending = {
    /**
     * Function to initialise data table config array
     * 
     * @returns {opt}
     */
    setinitialOpt :function(){
//       FgCommon.setinitialOpt()
       opt ={
        language: {
            sSearch: "<span>" + jstranslations['data_Search'] + ":</span> ",
            sInfo: jstranslations['data_showing'] + " <span>_START_</span> " + jstranslations['data_to'] + " <span>_END_</span> " + jstranslations['data_of'] + " <span>_TOTAL_</span> " + jstranslations['data_entries'],
            sZeroRecords: jstranslations['no_matching_records'],
            sInfoEmpty: jstranslations['data_showing'] + " <span>0</span> " + jstranslations['data_to'] + " <span>0</span> " + jstranslations['data_of'] + " <span>0</span> " + jstranslations['data_entries'],
            sEmptyTable: jstranslations['no_record'],
            sInfoFiltered: "(" + jstranslations['filtered_from'] + " <span>_MAX_</span> " + jstranslations['total_entries'] + ")",
            oPaginate: {
                "sFirst": '<i class="fa fa-angle-double-left"></i>',
                "sLast": '<i class="fa fa-angle-double-right"></i>',
                "sNext": '<i class="fa fa-angle-right"></i>',
                "sPrevious": '<i class="fa fa-angle-left"></i>'
            }

        },  
        deferRender: true,
        fixedcolumn: true,
        fixedcolumnCount: 1,
        responsive: true,
        order: [[0, "asc"]],
        dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4'i><'col-md-8'p>",
        scrollCollapse: true,
        paging: true,
        sScrollX:  "100%",
        sScrollXInner: "100%",
        lengthChange:false,
        autoWidth: true,
        stateSave: true,
        stateDuration: 60 * 60 * 24,
        serverSide: false,
        pageLength:50,
        processing: false,
        pagingType: "full_numbers",
        retrieve : true,
        scrollX : true,
        fnDrawCallback: function() {
            $(".attrbChecker").uniform();
            $('input[type=checkbox].data_checkall').uniform();
        },
        
    };
    return opt;
    },
    handleCheckAll:function(){
        $('body').on('click','.data_checkall',function(){
            var attrId=$(this).attr('data-attr-id');
            var checkedVal=$(this).is(':checked') ? 1:0;
            $.each(contactsData,function(key,rowValue){
                rowValue[attrId+'_checked']=checkedVal;
                rowValue['contactname']=rowValue['contactname'];
            });
            notificationContacts.clear();
            notificationContacts.rows.add(contactsData);
            notificationContacts.draw();
        });
    },
    handleCheckThis:function(){
        $('body').on('click','.attrbChecker',function(){
            var contactId=$(this).attr('data-contact');
            var attrId=$(this).attr('data-attr');
            var checkedVal=$(this).is(':checked') ? 1:0;
            if(checkedVal==0){
                $('#check_all_attr_'+attrId).prop('checked', 0);
                $('#check_all_attr_'+attrId).uniform();
            } else {
                var keyv= attrId+'_checked';
                thisAllRows=_.where(contactsData, JSON.parse('{ "'+attrId+'_checked'+'": 1 }'));
                if(thisAllRows.length+1==contactsData.length){
                    $('#check_all_attr_'+attrId).prop('checked', 1);
                    $('#check_all_attr_'+attrId).uniform();
                }
            }
            var thisRow=_.findWhere(contactsData, {id: contactId});
            thisRow[attrId+'_checked']=checkedVal;
        });
    }, 
    handleSending:function(){
        $('body').on('click','#message_wizard_save',function(){
            FgXmlHttp.post(pathSending, {'contactsData': JSON.stringify(contactsData)}, false);
        });
    },
    handleBackButton:function(){
        $('body').on('click','#message_wizard_discard',function(){
            window.location = backStep2Path;
        });
    }
}
$(document).ready(function() {
    fgMessageSending.handleCheckAll();
    fgMessageSending.handleCheckThis();
    fgMessageSending.handleSending();
    fgMessageSending.handleBackButton();
     //get services list
     $.getJSON(dataPath,function(data){
            opt =fgMessageSending.setinitialOpt();
            contactsData=data;
            opt.data =  contactsData;
            opt.columnDefs = setColumnDef();
            notificationContacts = $('table[data-contacts]').DataTable(opt);
            notificationContacts.columns.adjust().draw();
    });
 
});