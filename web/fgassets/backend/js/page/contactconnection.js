$(document).ready(function() { 
    FgPageTitlebar.init({
        title       : true,
        tab       : true,
        search     :false,
        actionMenu  : false,
        tabType  :'server'

    }); 
});
contactConnection = {
    handleDelete : function(){
        //handle delete
        $(document).on('click', 'div[data-isNew=1] .fg-close-btn', function() {
              var parent= $(this).closest('div[data-isNew=1]');
              contactObj=$(parent).find('input[data-area]');
              if($(contactObj).val() !== ''){
                  dataArea=$(contactObj).attr('data-area');
                  newExclude[dataArea].pop($(contactObj).val());
              }
              $(parent).toggle('slide',function(){ 
                  $(parent).parent().children('div[data-overwrite=1]').show();
                  $(this).remove();
                  $('#contact_connections').trigger('checkform.areYouSure');
              })
         });
    },
    handleBoxDisplay : function(){
        //handle box display
        $(document).on('change', 'div[data-isNew=1] select', function() {
             $(this).valid();
             if($(this).valid()){
             $(this).parent().parent().removeClass('has-error');
             }
             else{
                 $(this).parent().parent().addClass('has-error');
             }
             var contact =$(this).closest('div[data-isNew=1]').find('input[type=hidden]').val();
             if(contact != ''){
              contactConnection.boxDisplay(this,contact);
             }
        });
    },
    boxDisplay : function(selected,contact){
        $( ".tooltips" ).tooltip( "destroy" );
        var parentBox = $(selected).closest('div[data-isNew=1]');
        var relation = $(parentBox).find('select').val();
        var relType = parentBox.find('input[type=hidden][data-area]').attr('data-area');
        var relationType = '';
        switch(relType) {
             case 'household':
                 relationType = 'household';
                 break;
             case 'osp':
                 relationType = 'otherpersonal';
                 break;
         }
        var dataKey = relationType + '.' + contact + '.' + relation + '.is_new';
        var dataName = relationType + '_' + contact + '_' + relation + '_is_new';
        parentBox.find('input[type=hidden][data-type=add_conn]').remove();
        parentBox.append('<input type="hidden" data-type="add_conn" name="' + dataName + '" data-key="' + dataKey + '" value="1" class="fairgatedirty" />');
        // var search=$(parentBox).find('input[data-contact]').val();
        if(contact.length !== 0 ){
            $.getJSON(implicationPath, {'linked_contact_id': contact, 'relation_id': relation, 'relation_type': relationType }, function(result) {
                var template = $('#showImplications').html();
                var result_data = _.template(template, { content: result});
                $(parentBox).find('.implications').html(result_data);
                jQuery('.popovers').popover({
                    html: true,
                    trigger: 'hover',
                    container: $(this).attr('id'),
                    placement: 'auto',
                    template:'<div class="fg-connection-popover-new popover"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'

               });
            });
        }
    },
    handleResetChanges : function(){
        //reset changes starts
        $(document).on('click', '#reset_changes', function() {
            $('div[data-isNew=1]').remove();
            $('.fg-inactive-bg input').attr('disabled',false);
            $('.fg-inactive-bg ul label[data-type]').removeClass('fg-switch-inactive');
            $('.fg-inactive-bg').removeClass('fg-inactive-bg');
            handleToggleSwitch.update(selectedMC);
            $.uniform.update();
            $('#contact_connections').trigger('checkform.areYouSure');
        });
    },
    handleAddConnection : function(){
        //handle add connection
        $('a[data-toggle="connection"]').click(function(){
            type=$(this).attr('data-type'); 
            var template = $('#addNewConnection').html();
            var id=$.now();
            var result_data = _.template(template, {data: {'type':$(this).attr('data-type'),'id':id} });
            var parentDiv = $(this).closest('.col-md-3');
            $(result_data).insertBefore(parentDiv);
            if($(this).attr('data-overwrite')){
                $(parentDiv).hide();
            }
            $('#'+id+'_relation').selectpicker();
            handleTypeahead('#contact'+id,this);
            $('#contact_connections').trigger('checkform.areYouSure'); 
        });
    },
    initPageFunctions :function() {
        FgApp.init();
        FormValidation.init('contact_connections', 'saveChanges');
    },
    handleColorOnDelete : function(){
        $(document).on('click', 'input[data-inactiveblock=changecolor]', function() {
            var parent = $(this).attr('data-parentid');
            var parentDiv = $(this).parents('div[data-cloumn]');
            if($(this).is(':checked')){
                $(parentDiv).find('.'+parent).addClass('fg-inactive-bg');
                $(this).closest('div[data-cloumn]').find('ul label[data-type] input').attr('disabled',true);
                $(this).closest('div[data-cloumn]').find('ul label[data-type]').addClass('fg-switch-inactive');
            }
            else{
                $(parentDiv).find('.'+parent).removeClass('fg-inactive-bg');
                $(this).closest('div[data-cloumn]').find('ul label[data-type] input').attr('disabled',false);
                $(this).closest('div[data-cloumn]').find('ul label[data-type]').removeClass('fg-switch-inactive');
            }
            $.uniform.update();
        });
    },
    handleTypeSwitch:function(){
        //handle 'main contact' and 'seperate invoice' switching
        $('label[data-type]').on('click',function(){
           if($(this).find('input').is(':disabled')){
            return false;
           }
           handleToggleSwitch.update(this);
           $('#contact_connections').trigger('checkform.areYouSure');
        });
    },
    connectionUtility:function(){
        $('form').bind("keyup keypress", function(e) {
            var code = e.keyCode || e.which; 
            if (code  == 13) {               
              e.preventDefault();
              return false;
            }
        });
        //TEMPARARY FIX FOR REDIRECTING TABS
        // Checking whether the logged user has only readonly permission.
        // In that case need to disable all input tags
        if(($('body').hasClass('fg-readonly-contact') && $('body').hasClass('fg-contact-module-blk')) || ($('body').hasClass('fg-readonly-sponsor') && $('body').hasClass('fg-sponsor-module-blk'))) {
            $('input').attr("disabled", true);
        }
    },
    failCallbackFunctions : function () {
        
    },
    successCallbackFunctions : function () {
        FgPageTitlebar.setMoreTab();
    }
};
 var selectedMC='';
 var newExclude= [];
 FgPopOver.customPophover('.fg-dev-tabicon-right-Popovers');
 newExclude['household'] = []; newExclude['company']= []; newExclude['osp']= []; newExclude['ocy']= []; newExclude['ocp']= [];
 FgMoreMenu.initServerSide('paneltab');
 $(function(){
    selectedMC = $('label[data-type=MC] input:checked').closest('label[data-type=MC]');
    handleToggleSwitch.init(selectedMC);
    $('a[data-toggle="connection"]').each(function(){
        if($(this).attr('data-exclude')!==''){
            newExclude[$(this).attr('data-type')].push($(this).attr('data-exclude'));
        }
    });
    contactConnection.handleAddConnection();
    contactConnection.initPageFunctions();
    contactConnection.handleDelete();
    contactConnection.handleBoxDisplay();
    contactConnection.handleResetChanges();
    contactConnection.handleColorOnDelete();
    contactConnection.handleTypeSwitch();
    contactConnection.connectionUtility();
 });
 
handleToggleSwitch = {
    init: function(item){
        $(item).parents('ul').find('label[data-type]').addClass('fg-switch-inactive');
        $(item).parents('ul').find('label[data-type] input').attr('disabled',true);
        $(item).parents('ul').find('label[data-type=SI] input').attr('checked',false);
        $(item).parents('div[data-cloumn]').find('div[data-close] label').hide();
        $(item).parents('div[data-cloumn]').find('div[data-close] i').show();
        $('div[data-cloumn=MC]').attr('data-cloumn','');
        $(item).parents('div[data-cloumn]').attr('data-cloumn','MC');
        $.uniform.update();
    },
    update: function(item){
        if($(item).attr('data-type')=='MC' && $(item).find('input:checked')){
            $('div[data-cloumn=MC]').find('label[data-type]').removeClass('fg-switch-inactive');
            $('div[data-cloumn=MC]').find('label[data-type] input').attr('disabled',false);
            $('div[data-cloumn=MC]').find('label[data-type=MC] input').attr('checked',false);
            $(item).find('input').attr('checked',true);
            $(item).parents('ul').find('label[data-type] input').attr('disabled',true);
            $(item).parents('ul').find('label[data-type]').addClass('fg-switch-inactive');
            $(item).parents('ul').find('label[data-type=SI] input').attr('checked',false);
            $('div[data-close] label').show();
            $('div[data-close] i').hide();
            $(item).parents('div[data-cloumn]').find('div[data-close] label').hide();
            $(item).parents('div[data-cloumn]').find('div[data-close] i').show();
            $('div[data-cloumn=MC]').attr('data-cloumn','');
            $(item).parents('div[data-cloumn]').attr('data-cloumn','MC');
       }
       $.uniform.update();
    }
}
//handle auto complete
handleTypeahead = function(item,obj) {
    var isCompany=$(obj).attr('data-isCompany');
    var newType=$(obj).attr('data-type');
    function changeTypeahead(obj, datum) {
        idInput=$(item).attr('id');
        newExclude[newType].push(datum.id);
        $('#'+idInput+'Hidden').val(datum.id);
        $(item).typeahead('val', datum.contactname);
    }
    var contacts = new Bloodhound({
        datumTokenizer: function(d) {
            return d.tokens;
        },
        limit: 100,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: { url: contactSearchUrl.replace('QUERY', '%QUERY')+'?'+$.now(),
            ajax: {data: {"exclude": newExclude[newType].join(), 'isCompany': isCompany, 'type':newType }, method: 'post'},
            filter: function (contacts) { dataset=[];
              $.map(contacts, function (contact) {
                  ids=newExclude[newType].join();
                  count=ids.search(contact.id);
                  if(count == -1){
                    dataset.push(contact);
                  }   
              }); 
              return dataset;
          }
        },
    });

    contacts.initialize();
    $(item).typeahead(null, {
        displayKey: 'contactname',
        highlight: true,
        hint: true,
        source: contacts.ttAdapter()
    }).bind('typeahead:selected', function(obj, datum) { 
        if(isCompany == '0'){ 
            contactConnection.boxDisplay(item,datum.id);
        }
        changeTypeahead(obj, datum); 
    }).bind('typeahead:autocompleted', function(obj, datum) {
        changeTypeahead(obj, datum); 
    });
}
function saveChanges() {
    $('div[data-isNew] input[type=hidden]').each(function(){
        if($(this).val()==''){
            $('div.alert-danger').show();
            Metronic.scrollTo($('div.alert-danger'), -200);
            throw new Error('Contact not selected');
        }
    });
    $('.newCompanyConn').each(function() {
        var relationName = $(this).find('input[type=text][data-type=relation_name]').val();
        var hiddenElem = $(this).find('input[type=hidden][data-area]');
        var linkedContactId = hiddenElem.val();
        var data_area = hiddenElem.attr('data-area');
        var dataArea = '';
        if (data_area == 'company') {
            dataArea = 'company';
        } else if (data_area == 'ocp') {
            dataArea = 'othercompanypersonal';
        } else {
            dataArea = 'othercompany';
        }
        var dataKey = dataArea + '.' + linkedContactId + '.' + relationName + '.is_new';
        var dataName = dataArea + '_' + linkedContactId + '_' + relationName + '_is_new';
        $(this).append('<input type="hidden" data-type="add_conn" name="' + dataName + '" data-key="' + dataKey + '" value="1" class="fairgatedirty" />');
    });
    var objectGraph = {};
    //parse the all form field value as json array and assign that value to the array
    objectGraph=  FgParseFormField.fieldParse();
    var connArr = JSON.stringify(objectGraph);
    var currHouseholdCntIds = $('#current_household_contacts').val();
    FgXmlHttp.post(updateConnection, {'connArr': connArr, 'contactId': contactId, 'isCompany': isCompany, 'currHouseholdCntIds': currHouseholdCntIds} , false, contactConnection.successCallbackFunctions, contactConnection.failCallbackFunctions, '0');
}
    