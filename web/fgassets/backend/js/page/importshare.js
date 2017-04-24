var manFiledError={};
var offset; var limit;
var update = 0;
$(window).resize(function(){
    dataAssignments.checkScreenSize();
});
jQuery(document).ready(function() {
    $('div[data-body]').show();
    dataAssignments.checkScreenSize();
    FormWizard.init();
    dataAssignments.init();
});

$(document).on('change','#form-tab1 input[name=contactType]',function(){
    var contactType=$(this).val();
    $('table[data-sample-type]').hide();
    $('table[data-sample-type='+contactType+']').show();
});

var dataAssignments={
   handleDelete: function(){
       $('#tab2').on('click', 'input[data-inactiveblock=changecolor]', function() {
         $(this).closest('tr').toggleClass('danger');
         $(this).closest('tr').find('span[data-field]').toggleClass('display-none');
     });
   },
   handleAddColumn: function(){          
       $('#tab2').on('click','div[data-addMore] a',function(){              
         var csvRows=$('#tab2').find('#assign-data-fields-selection').attr('data-rows');   
         csvData= JSON.parse(csvRows);
         offset=(limit)? limit : 50;
         limit=(( offset + 10) > csvData[0].length) ? csvData[0].length : offset+10;
         if(csvData[0].length <= ( offset + 10)){
             $(this).hide();
         }
         else if(csvData[0].length < (limit+10)){
           $(this).find('span.fg-add-text .fg-more-count').html((csvData[0].length-limit));  
         }
         var template = $('#assign-data-fields-selection').html();
         var result_data = _.template(template, {data: {'csvData': csvData, 'offset': offset, 'limit': limit}});
         $('#tab2 table tbody').append(result_data);
         $('#tab2 table tbody tr').slice(offset).find('.bs-select').selectpicker();            
       })  
   },
   /*handle icons of invoice and correspondense in select field*/      
   handleIconsInSelect:function() {                  
     $(document).on('click',".opt", function() {
         $(this).parents('.bootstrap-select').find('button.dropdown-toggle').removeClass('fg-btn-money').removeClass('fg-btn-home');
         if($(this).hasClass("fg-option-money")) {
             $(this).parents('.bootstrap-select').find('button.dropdown-toggle').addClass('fg-btn-money');
         }
         if($(this).hasClass("fg-option-home")) {
             $(this).parents('.bootstrap-select').find('button.dropdown-toggle').addClass('fg-btn-home');
         }
     });

     $('.bs-select').each(function(){            
         if($(this).find(":selected").hasClass( "fg-option-home" )) {                  
             $(this).parent().find('.bootstrap-select').find('button.dropdown-toggle').addClass('fg-btn-home');
         } 
         if($(this).find(":selected").hasClass( "fg-option-money" )) {                
             $(this).parent().find('.bootstrap-select').find('button.dropdown-toggle').addClass('fg-btn-money');
         }
     });    
   },
   handleImportFirstRow: function(){
         $('#tab2').on('change','input#not_import_first_row',function(){
             var columnClount=$('span[data-colCount]').html();
             var newCount= ($(this).is(':checked')) ? parseInt(columnClount)-1 :parseInt(columnClount)+1;
             $('span[data-colCount]').html(newCount);
             $('span[data-firstRow]').toggleClass('display-none');
         });
   },
   handleMandetory:function(){
     var manFileds={};
        $('table tr select:first option[data-req]').each(function(){
             var fieldReq=$(this).val();
             var manFiled=$(this).text();
             manFileds[fieldReq] = manFiled.replace('*', '');
        });
        return manFileds;
   },
   checkScreenSize:function(){
     if(window.screen.width < 768) {
         $('div.portlet-body').children('ul').hide();
         $('div.portlet-body').children('.row:last').hide();
         $('div.portlet-body div[data-sample]').children('.alert-info').hide();
         $('div.portlet-body div[data-sample]').children('.alert-danger').show();
    } else {
         $('div.portlet-body').children('ul').show();
         $('div.portlet-body').children('.row:last').show();
         $('div.portlet-body div[data-sample]').children('.alert-info').show();
         $('div.portlet-body div[data-sample]').children('.alert-danger').hide();
    }
    $('div[data-body]').show();
   },
   init:function(){
         this.handleDelete();
         this.handleAddColumn();            
         this.handleImportFirstRow();
   }        
}
  /*-------------- Page title bar configuration --------*/ 
FgPageTitlebar.init({
       title: true,
       tab: false,
       tabType  :'server'

   }); 