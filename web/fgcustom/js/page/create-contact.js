var createContact={
    handleExisting:function(){
        mcType = $('div[data-catId=3] div[data-attrId=mainContact] input:checked').val();
        if (mcType == 'noMain') {
            $('div[data-catId=1]').hide();
        } else {
            $('div[data-catId=1]').show();
            if (mcType == 'withMain') {
                $('div[data-catId=1] .form-body .row').show();
                $('div[data-catId=1] .form-body .row:first').hide();
            }
            if (mcType == 'existing') {
                $('div[data-catId=1] .form-body .row').hide();
                $('div[data-catId=1] .form-body .row:first').show();
            }
        }
    },
    resetCallback:function (){
        FgUtility.stopPageLoading();        
        createContact.callbackfn();
    },
    saveToSessionStorage:function(){
        var randomNumber=Math.random();
        var assignmentHtml=$('#fullAssignmentSection').html();
        $('#remove-bootstrap-select').html(assignmentHtml);
        $("#remove-bootstrap-select div.bootstrap-select").remove();
        $("#remove-bootstrap-select").find('.has-error').removeClass('has-error');
        assignmentHtml=$('#remove-bootstrap-select').html();
        sessionStorage.setItem("assignmentHtml"+randomNumber, assignmentHtml);
        $('#randomAssignNum').val(randomNumber);
    },
    generateDataKeyElement:function(thisVal){
        var selectedOption = thisVal.find('select:last option:selected');
        var dataKey = selectedOption.attr('data-key');
        var dataName = selectedOption.attr('name');
        var dataValue = selectedOption.val();
        var appendVal = (selectedOption.attr('element-type') == 'Team') ? 'team' : '';
        var keyElement = thisVal.find('input[type=hidden][data-type=key_element]');
        $(keyElement).attr({'name': appendVal + dataName + '_is_new', 'data-key': appendVal + dataKey + '.is_new', 'value': dataValue, 'class': 'fg-dev-finalKeyVal'});
    },
    switchCallbackfn:function(){
        createContact.handleExisting();
        if(module == 'contact') {
            memberId=$('select[data-attrId=membership]').val();
        } else {
            memberId=$('input[data-attrId=membership]').val();
        }  
        createContact.handleRequiredToggle('1');
        createContact.handleSameAs();
        FgFormTools.handleUniform();
        FgFormTools.handleInputmask();
        createContact.handleTypeahead();
        FgFormTools.handleBootstrapSelect();
        FgFormTools.handleSelect2();
        Breadcrumb.load(index_url);
        FgPopOver.init(".fg-dev-Popovers", true);
        $('input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
        var randomAssignNum=$('#randomAssignNum').val();
        var assignmentHtmlFrmSession=sessionStorage.getItem("assignmentHtml"+randomAssignNum);
        $('#fullAssignmentSection').html('');
        $('#fullAssignmentSection').html(assignmentHtmlFrmSession);
        if(isEditMode) {
            $('#save_changes').attr("data-toggle","confirmation"); 
        }
    },
    callbackfn:function(responce){
        if (typeof (responce) == 'object') {
            if(responce.mergeable){
                fedMem={};
                isCompanyContact = ($('div[data-attrid="contactType"] input:checked').val()=='Company') ? true:false;
                var duplicates = (responce['mergeEmail'].length>0) ? responce.mergeEmail:responce.duplicates;
                var typeMer= (responce['mergeEmail'].length>0) ? 'email':'fields';
                var countMergeable = (responce['mergeEmail'].length>0) ? 1:duplicates.length;
                yours={'firstname':$('#fg_field_category_1_2').val()};
                if(isCompanyContact){
                    yours['company']=$('#fg_field_category_3_9').val();
                }
                yours['email']=$('#fg_field_category_6_3').val();
                yours['lastname']=$('#fg_field_category_1_23').val();
                yours['gender']=$('#fg_field_category_1_72').val();
                yours['dob']=$('#fg_field_category_1_4').val();
                yours['location']=$('#fg_field_category_2_77').val();
                $('select#fg_field_category_system_fedMembership option').each(function(){
                    if($(this).attr('value') !='' && $(this).attr('value') !='default' ){
                        fedMem[$(this).attr('value')]=$(this).attr('data-content');
                    }
                });
                var htmlFinal = _.template($('#merge-popup-template').html(),{'duplicates': duplicates,'fedMem':fedMem,'isCompanyContact':isCompanyContact,'typeMer':typeMer,'countMergeable':countMergeable,'yours':yours});
                $('#merge-popup').find('div.modal-content').html(htmlFinal);
                FgFormTools.handleUniform();
                $('#merge-popup').modal('show');
                createContact.mergePopupHandling(typeMer);
                $('#merge-popup').on('hidden.bs.modal', function () {
                    createContact.cancelMerging(typeMer);
                })
                
            }
        } else {
            createContact.handleExisting();
            if(module == 'contact') {
                memberId=$('select[data-attrId=membership]').val();
            } else {
                memberId=$('input[data-attrId=membership]').val();
            }        
            //createContact.handleRequiredToggle('1');
            createContact.handleSameAs();
            FgFormTools.handleUniform();
            FgFormTools.handleInputmask();
            FgFormTools.handleBootstrapSelect();
            FgFormTools.handleSelect2();
            Breadcrumb.load(index_url);
            FgPopOver.init(".fg-dev-Popovers", true);
            $('input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
        }
    },
    mergePopupHandling:function(typeMer){
        $('#cancel_merging').on('click', function() {
            createContact.cancelMerging(typeMer);
        });
         $('#save_merging').on('click', function() {
            var mergerValue=$('.merge-value-radio:checked').val();
            extraData={'merging':'save','mergeTo':mergerValue,'typeMer':typeMer};
            FgXmlHttp.iframepost(path,$('#form1'),extraData,false,createContact.callbackfn, createContact.callbackfn);
        });
    },
    cancelMerging:function(typeMer){
        if(typeMer=='email'){
            $('#merge-popup').modal('hide');
        } else {
            var mergerValue=$('.merge-value-radio:checked').val();
            extraData={'merging':'cancel','mergeTo':mergerValue,'typeMer':typeMer};
            FgXmlHttp.iframepost(path,$('#form1'),extraData,false,createContact.callbackfn, createContact.callbackfn);
        }
    },
    handleRequiredToggle:function(callBack){
        fedMem=$('div[data-catId=0] select[data-attrid=fedMembership]').val();
        clubMem=$('div[data-catId=0] select[data-attrid=membership]').val();
        $('div[data-required=selected_members] label .required').hide();
        isFedMember=true;
        isClubMember=true;
        if(fedMem == '' || fedMem === null || fedMem=='default'){
            isFedMember=false;
            $('#new-fedmember-assignment-panel').empty();
            $('div[data-required=all_fed_members] label .required').hide();
        }
        if(clubMem == '' || clubMem === null || clubMem=='default'){
            isClubMember=false;
            $('#new-fedmember-assignment-panel').empty();
            $('div[data-required=all_club_members] label .required').hide();
        }
        if(isClubMember || isFedMember) {
            if(isClubMember) {
                $('div[data-required=all_club_members] label .required').show();
            }
            if(isFedMember) {
                $('div[data-required=all_fed_members] label .required').show();
            }
            
            $('div[data-required=selected_members]').each(function(){
                members=$(this).attr('data-members');
                reqType=$(this).attr('data-reqType');
                if(members.indexOf(':'+fedMem+':') != '-1' || members.indexOf(':'+clubMem+':') != '-1'){
                    $(this).find('label .required').show();
                } 
                //handle fed field
                if(isFedMember && reqType=='FRD' ){
                    $(this).find('label .required').show();
                }
            });
            if(isEditMode==false && module == 'contact') {
                if(isFedMember){
                    if(callBack !='1'){
                        $('#new-fedmember-assignment-panel').empty();
                        listFederationCategories();
                    }
                } else {
                    $('#new-fedmember-assignment-panel').empty();
                }
            }
        }
    },
    handleSameAs:function(){
        if($('input#same_invoice_address').is(':checked')){
            $('div[data-catId=2] :input[data-addresstype=both]').each(function(){
                addressId=$(this).attr('data-attrId');
                $('div[data-catId=137] :input[data-addressid='+addressId+']').val($(this).val());
                $('div[data-catId=137] :input[data-addressid='+addressId+']').attr('disabled',true);
            });
            $('div[data-catId=2] div[data-addresstype=both] input').each(function(){
                addressId=$(this).parents('div[data-addresstype=both]').attr('data-attrId');
                if($(this).is(':checked')){
                    $('div[data-catId=137] div[data-addressid='+addressId+'] input[value='+$(this).val()+']').attr('checked',true);
                }
                $('div[data-catId=137] div[data-addressid='+addressId+'] input[value='+$(this).val()+']').attr('disabled',true);
            });
             $('div[data-catId=137] input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
            jQuery('div[data-catId=137] div.date input:disabled').siblings('span').off("click");
            FgResetChanges.checkboxReset();
            $('div[data-catId=137] select.bs-select').selectpicker('refresh');
        }
        else{
            jQuery('div[data-catId=137] div.date input:disabled').parent().datepicker(FgApp.dateFormat);
            $('div[data-catId=137] input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').removeClass('fg-label-inactive');
            $('div[data-catId=137] :input').attr('disabled',false);
            $('div[data-catId=137] select.bs-select').selectpicker('refresh');
            FgResetChanges.checkboxReset();
        }
    },
    handleTypeSwitching:function(){
        //Single persone or company population.
        $('div[data-catId=0] div[data-attrId=contactType] input').on('click', function() {
            read = $(this).attr('readonly');
            if(!read){
                $('#failcallbackServerSide span').text("");
                $('#failcallbackServerSide').hide();
                createContact.saveToSessionStorage();
                FgXmlHttp.iframepost(path+"?fieldType=1",$('#form1'),false,false,createContact.switchCallbackfn, createContact.callbackfn);
            }
        });
    },
    handleMainContactDisplay:function(){
        // with or with out main contact toggle function.
        $('div[data-attrid=mainContact] input').on('click', function() {
            mainType = $(this).val();
            read = $(this).attr('readonly');
            if(read){
                return false;
            }
            if(mainType=='noMain'){
               $('div[data-catId=1]').hide();
            }
            else{
                $('div[data-catId=1]').show();
                if(mainType=='withMain'){
                    $('div[data-catId=1] .form-body .row').show();
                    $('div[data-catId=1] .form-body .row:first').hide();
                }
                if(mainType=='existing'){
                    $('div[data-catId=1] .form-body .row').hide();
                    $('div[data-catId=1] .form-body .row:first').show();
                }
            }
        });
    },
    handleTypeahead:function(){
        $('#mainContactAuto').fbautocomplete({
            url: contactUrl, // which url will provide json!
            removeButtonTitle: removestring,
            params: {'isCompany': 0,'contactId':editContactId, page:'contactEdit'} ,        
            selected: mcSelected,
            maxItems: 1,
            useCache: true,
            formName:'mainContactNameTitle',
            isLink:mainContactLink,
            linkUrl:autosuggestLinkUrl,
            onItemSelected: function($obj, itemId, selected) {
                $('#mainContactId').val(itemId);
                $('#fg_field_category_1_mainContactName').val(selected[0].title);
                FgDirtyForm.checkForm($('body').find('form').attr('id'));
            },
            onItemRemoved: function($obj, itemId) {
                $('#mainContactId').val('');
                $('#fg_field_category_1_mainContactName').val('');
                FgDirtyForm.checkForm($('body').find('form').attr('id'));
            },
            onAlreadySelected: function($obj) {

            }
        });
    },
    handlePageInits:function(){
        // invoice /corespondence address same as toggle function
        $('div[data-catId=2] input#same_invoice_address').on('click', function() {
            createContact.handleSameAs();
        });
        $('div[data-catId=0] select').on('change', function() {
           createContact.handleRequiredToggle($(this).val());
        });
        $('form input[type=file]').change(function(){
            $('form').find('input[type="submit"]').removeAttr('disabled');
            $('form').find('input[type="reset"]').removeAttr('disabled');
        });
        FgFormTools.handleUniform();
        FgPopOver.init(".fg-dev-Popovers", true);
        $('input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
    },
    handleOtherClubContact:function(){
        mainContactType = $('div[data-catId=3] div[data-attrId=mainContact] input:checked').val();
        if(mainContactType == 'existing'){
            $('div[data-catId=3] div[data-attrId=mainContact] input').attr('disabled','disabled');
            $('div[data-catId=1] .form-body .row').hide();
            $('div[data-catId=1] .form-body .row:first').show();
            $('div[data-attrId=mainContactName] input').attr('readonly','readonly'); 
            $('#mainContactAuto').attr('readonly','readonly'); 
            $('.fg-contact-with-auto').addClass('fg-input-wrapper-disabled');
            $('div[data-attrId=mainContactFunction] input').attr('readonly','readonly');
        }
    },
    handleSave:function(){
        $('#save_changes').click(function() {
            $('#save_changes').off('click');
            var errorFlag=validateAssignments();
            createContact.saveToSessionStorage();
            $('#assignErrFlag').val(errorFlag);
            $('.roleTypeDpdn').each(function() {
                createContact.generateDataKeyElement($(this));
            });
            $('.fg-dev-fed-wrap').each(function() {
                createContact.generateDataKeyElement($(this));
            });
            var objectGraph = {};
            //parse the all form field value as json array and assign that value to the array
            objectGraph=  customFieldParse();
            var catRoleFunctionArray = JSON.stringify(objectGraph);
            $('#assignedCatRolFunArray').val(catRoleFunctionArray);
            if(isEditMode && isSwitchable===0 && contacttype != $('div[data-attrid="contactType"] input:checked').val()){
                var cType= $('div[data-attrid="contactType"] input:checked').val()=='Company' ? companyC :singleP;
                $('#save_changes').parent().removeClass("fg-confirm-btn").addClass("fg-confirm-btn");
                FgConfirmation.confirm(confirmNote.replace('%TARGHETTYPE%',cType),cancelLabel,saveLabel,$('#save_changes'), createContact.saveField );
            } else {
               createContact.saveField();
            }
        });
    },
    saveField:function(){
        FgXmlHttp.iframepost(path,$('#form1'),false,false,createContact.callbackfn, createContact.callbackfn);
    },
    handleReset:function(){
        $('#reset_changes').on('click',function(){
            if (isEditMode){
                    FgUtility.startPageLoading();
                    FgXmlHttp.replaceContentFromUrl(document.location.href, false, createContact.resetCallback, false);
            } else {
                window.location=indexPath;
            }
        });
    },
    handleBackLink:function(){
        $('.backbtn').on('click',function(){
            window.location=indexPath;
        });
    },
    handleFileDelete:function(){
        $('[data-dismiss="fileinput"]').on('click',function(){
            var fieldId = $(this).attr('data-fileid');
            var deletedFiles = $("#deletedFiles").val();
            deletedFiles = (deletedFiles=='') ? fieldId : (deletedFiles+','+fieldId);
            $("#deletedFiles").val(deletedFiles);
        });
    },
    pageInit:function(){
        createContact.handleSameAs();
        createContact.handleExisting();
        createContact.handleMainContactDisplay();
        createContact.handlePageInits();
        FgFormTools.handleInputmask();
        jQuery('div.date input:enabled:not(:has([readonly]))').parent().datepicker(FgApp.dateFormat);
        createContact.handleTypeSwitching();
        createContact.handleBackLink();
        createContact.handleFileDelete();
        createContact.handleReset();
        createContact.handleSave();
        createContact.handleTypeahead();
        //createContact.handleRequiredToggle('1');
    }
};
