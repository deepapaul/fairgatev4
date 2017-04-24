FgContactProfile = {
    contactProfileData : {},
    setContactFields : function()
    {
        FgUtility.startPageLoading();
        var templateContactOptionCategory = $('#template_contact_option_category').html();
        var templateContactOptionField = $('#template_contact_option_field').html();
        _.each(FgContactProfile.contactProfileData, function(cd){
            var categoryHtml = _.template(templateContactOptionCategory, cd);
            $('#contact_profile_option_wrapper').append(categoryHtml);
            _.each(cd.fields, function(cf){
                $('#fields_cat_'+cd.catId+' .fg-formfield-innerblock').append(_.template(templateContactOptionField, cf));
                FgContactProfile.handleAvailableContactSelection(cf.categoryId, cf.attributeId, cf.availabilityContact);
                FgContactProfile.handleAvailableGroupAdminSelection(cf.categoryId, cf.attributeId, cf.availabilityGroupadmin);
            });
        });
        
        FgContactProfile.setDirtyFields();
        $('.fg-dev-confirmation').uniform();
        FgPopOver.customPophover(".fg-contact-Popovers", true);
        FgContactProfile.setContactFieldEvents();
        FgUtility.stopPageLoading();
    },
    
    setDirtyFields: function()
    {
        FgDirtyFields.init('profile_contactfield', { 
                                        setInitialHtml: false,
                                        initCompleteCallback : function () {
                                            
                                        },
                                        discardChangesCallback :function(){
                                            $('#contact_profile_option_wrapper').html('');
                                            FgContactProfile.setContactFields();
                                            $( "#save_changes" ).click(function() {
                                                FgContactProfile.saveContactProfileFields();
                                            });
                                        }
                                    });
    },
     
    setContactFieldEvents: function(){
        $('input:radio[name^="available_contact"]').click(function(){
            var availabilityContactChecked = $(this).val();
            var elementDataArray = $(this).attr('data-key').split('.');
            var categoryId = elementDataArray[0];
            var attributeId = elementDataArray[2];
            FgContactProfile.handleAvailableContactSelection(categoryId, attributeId, availabilityContactChecked);
        });
        $('input:radio[name^="available_group"]').click(function(){
            var availabilityGroupAdminChecked = $(this).val();
            var elementDataArray = $(this).attr('data-key').split('.');
            var categoryId = elementDataArray[0];
            var attributeId = elementDataArray[2];
            FgContactProfile.handleAvailableGroupAdminSelection(categoryId, attributeId, availabilityGroupAdminChecked);
        });
        $('input:radio[name^="visible_other"]').click(function(){
            
        });
        
    },
    
    handleAvailableContactSelection: function(categoryId, attributeId, availabilityContactChecked){
        var containerField = $('#fields_'+attributeId);
        if(availabilityContactChecked === 'changable'){
            //Show the wrapper
            containerField.find('.fg-dev-availibility-contact .fg-dev-confirmcheck-wrapper').show();
        } else {
           //Hide the wrapper
           containerField.find('.fg-dev-availibility-contact .fg-dev-confirmcheck-wrapper').hide();
           //Remove the checked property
           $('#is_confirm_contact_'+categoryId+'_'+attributeId).prop('checked',false).trigger('change');
           $.uniform.update('#is_confirm_contact_'+categoryId+'_'+attributeId);
        }
        
        FgContactProfile.handleOtherContactVisibilityState(categoryId, attributeId);
    },
    
    handleAvailableGroupAdminSelection: function(categoryId, attributeId, availabilityGroupAdminChecked){
        var containerField = $('#fields_'+attributeId);
        if(availabilityGroupAdminChecked === 'changable'){
            //Show the wrapper
            containerField.find('.fg-dev-availibility-groupadmin .fg-dev-confirmcheck-wrapper').show();
        } else {
           //Hide the wrapper
           containerField.find('.fg-dev-availibility-groupadmin .fg-dev-confirmcheck-wrapper').hide();
           //Remove the checked property
           $('#is_confirm_teamadmin_'+categoryId+'_'+attributeId).prop('checked',false).trigger('change');
           $.uniform.update('#is_confirm_teamadmin_'+categoryId+'_'+attributeId);
        }
        
        FgContactProfile.handleOtherContactVisibilityState(categoryId, attributeId);
    },
    
    handleOtherContactVisibilityState: function(categoryId, attributeId){
        var availabilityContactChecked = $('input[name="available_contact_'+categoryId+'_'+attributeId+'"]:checked').val();
        var availabilityGroupAdminChecked = $('input[name="available_group_'+categoryId+'_'+attributeId+'"]:checked').val();
        if(availabilityContactChecked === 'not_available' || availabilityGroupAdminChecked === 'not_available'){
            $('#fields_'+attributeId).find('.fg-dev-visibility-contact').hide();
            
            $('#is_set_privacy_itself_'+categoryId+'_'+attributeId).prop('checked',false).trigger('change');
            $('#visible_other_private_'+categoryId+'_'+attributeId).prop('checked',true).trigger('change');
            $.uniform.update('#is_set_privacy_itself_'+categoryId+'_'+attributeId);
        } else {
            $('#fields_'+attributeId).find('.fg-dev-visibility-contact').show();
        }
    },
   
    saveContactProfileFields: function()
    {
       var formData = JSON.stringify(FgParseFormField.fieldParse());
       var data = {
           'attributes': formData,
           'source': 'contactprofile'
       };
       var pathUrl = $('#profile_contactfield').attr('action');
       FgXmlHttp.post(pathUrl, data);
    }
}
