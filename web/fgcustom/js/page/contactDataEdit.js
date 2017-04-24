var isFrontend = (typeof isInternal != 'undefined') ? isInternal : false;
var companyLogoUploaderObj;

var contactEdit={
    preValidate: function () {
        $('<input>').attr({
            type: 'hidden',
            id: 'preValid',
            name: 'preValid',
            value: 'preValidData'
        }).appendTo('#form1');
        FgXmlHttp.iframepost(path, $('#form1'), false, false, function () {
            contactEdit.responseCallback();
        }, function () {
            contactEdit.responseCallback();
        });
    },
    responseCallback:function(){
        $('#fg-contact-data').removeClass('hide');
        $('#preValid').remove();
        $('#save_changes,#reset_changes').attr('disabled','disabled');
        FgMoreMenu.initClientSide('data-tabs', 'data-tabs-content', 'data');
        contactEdit.callbackfn();
    },
    handleExisting:function(){
        var activeTab = $("#active_tab").val();
        if (activeTab == '3') {
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
        }
    },
    resetCallback:function (){
        FgUtility.stopPageLoading();
        contactEdit.preValidate();
    },
    callbackfn:function(){
        FgPageTitlebar.setMoreTab();
        if(pageType == 'contact') {
            memberId = $('select[data-attrId=membership]').val();
           
        } else {
            memberId=$('input[id=membership]').val();
        }
        FgDirtyForm.init();
        contactEdit.handleRequiredToggle(memberId);
        contactEdit.handleSameAs();
        if (!isFrontend) {
            FgFormTools.handleUniform();
            FgFormTools.handleInputmask();
            FgFormTools.handleBootstrapSelect();
            FgFormTools.handleformSelect2();
        }
       if(($('.ids-fbautocomplete').val()!='')&&($('#mainContactId').val()=='')){
                     var mainId = parseInt($('.ids-fbautocomplete').val());
                     $('#mainContactId').val(mainId);
                     $('#mainContactId').addClass('fairgatedirty');
                     
        }
        //contactEdit.handleTypeahead();
        contactEdit.handletabs();
        contactEdit.handleExisting();
        Breadcrumb.load(index_url);
        //reinitializing dropzone after discard 
       
        
        contactEdit.initUpload();
        contactEdit.handleExistingimageUpload();
   
        $('input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
        //$(".datemask").inputmask(FgLocaleSettingsData.jqueryDateFormat, { placeholder: '', showMaskOnFocus : false, showMaskOnHover : false });
    },
    handleRequiredToggle:function(req){
        fedMem=$('#fedMembership').val();
        clubMem=$('#clubMembership').val();
        $('div[data-required=selected_members] label .required').hide();
        isFedMember=true;
        isClubMember=true;
        if(fedMem == '' || fedMem === null || fedMem=='default'){
            isFedMember=false;
            $('div[data-required=all_fed_members] label .required').hide();
        }
        if(clubMem == '' || clubMem === null || clubMem=='default'){
            isClubMember=false;
            $('div[data-required=all_club_members] label .required').hide();
        }
        if(isClubMember || isFedMember) {
            if(isClubMember) {
                $('div[data-required=all_club_members] label .required').show();
            }
            if(isFedMember) {
                $('div[data-required=all_fed_members] label .required').show();
            }
            $('div[data-required=selected_members]').each(function() {
                members = $(this).attr('data-members');
                reqType = $(this).attr('data-reqType');
                if(members.indexOf(':'+fedMem+':') != '-1' || members.indexOf(':'+clubMem+':') != '-1'){
                    $(this).find('label .required').show();
                }
                //handle fed field
                if (isFedMember && reqType == 'FRD') {
                    $(this).find('label .required').show();
                }
            });
        }
    },
    handleSameAs:function(){
        if ($('input#same_invoice_address').is(':checked')) {
            $('div[data-catId=2] :input[data-addresstype=both]').each(function() {
                addressId = $(this).attr('data-attrId');
                $('div[data-catId=137] :input[data-addressid=' + addressId + ']').val($(this).val());
                $('div[data-catId=137] :input[data-addressid=' + addressId + ']').attr('disabled', true);
            });
            $('div[data-catId=2] div[data-addresstype=both] input').each(function() {
                addressId = $(this).parents('div[data-addresstype=both]').attr('data-attrId');
                if ($(this).is(':checked')) {
                    $('div[data-catId=137] div[data-addressid=' + addressId + '] input[value=' + $(this).val() + ']').attr('checked', true);
                }
                $('div[data-catId=137] div[data-addressid=' + addressId + '] input[value=' + $(this).val() + ']').attr('disabled', true);
            });
            $('div[data-catId=137] input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
            jQuery('div[data-catId=137] div.date input:disabled').siblings('span').off("click");
            FgResetChanges.checkboxReset();
        }
        else {
            jQuery('div[data-catId=137] div.date input:disabled').parent().datepicker({autoclose: true, weekStart: 1});
            $('div[data-catId=137] input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').removeClass('fg-label-inactive');
            $('div[data-catId=137] :input').attr('disabled', false);
            FgResetChanges.checkboxReset();
        }
    },
    handleMainContactDisplay:function(){
        // with or with out main contact toggle function.
        $('div[data-attrid=mainContact] input').on('click', function() {
            mainType = $(this).val();
            read = $(this).attr('readonly');
            if (read) {
                return false;
            }
            if (mainType == 'noMain') {
                $('div[data-catId=1]').hide();
            }
            else {
                $('div[data-catId=1]').show();
                if (mainType == 'withMain') {
                    $('div[data-catId=1] .form-body .row').show();
                    $('div[data-catId=1] .form-body .row:first').hide();
                }
                if (mainType == 'existing') {
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
            params: {'isCompany': 0, offset:offSet,page:'contactEdit'} ,        
            selected: mcSelected,
            maxItems: 1,
            useCache: true,
            formName:'mainContactNameTitle',
            isLink:mainContactLink,
            linkUrl: autosuggestLinkUrl,
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
            contactEdit.handleSameAs();
        });
        $('div[data-catId=0] select').on('change', function() {
            contactEdit.handleRequiredToggle($(this).val());
        });
        $('form input[type=file]').change(function() {
            $('form').find('input[type="submit"]').removeAttr('disabled');
            $('form').find('input[type="reset"]').removeAttr('disabled');
        });
        if (!isFrontend) {
            FgFormTools.handleUniform();
        }
        $('input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
    },
    handleOtherClubContact:function(){
        mainContactType = $('div[data-catId=3] div[data-attrId=mainContact] input:checked').val();
        if (mainContactType == 'existing') {
            $('div[data-catId=3] div[data-attrId=mainContact] input').attr('disabled', 'disabled');
            $('div[data-catId=1] .form-body .row').hide();
            $('div[data-catId=1] .form-body .row:first').show();
            $('div[data-attrId=mainContactName] input').attr('readonly', 'readonly');
            $('#mainContactAuto').attr('readonly','readonly');
            $('.fg-contact-with-auto').addClass('fg-input-wrapper-disabled');
            $('div[data-attrId=mainContactFunction] input').attr('readonly', 'readonly');
        }
    },
    handleSave:function(){
        $('#save_changes').click(function() {
            var activeTab = $(".nav-tabs").find('li.active').attr('data-type');
            FgFileUploadInstance.remove($('#image-uploader'));
            $("#active_tab").val(activeTab);
            FgXmlHttp.iframepost(path, $('#form1'), false, false, contactEdit.callbackfn, contactEdit.callbackfn);
        });
    },
    handleReset:function(){
        $('#reset_changes').on('click', function() {
            FgFileUploadInstance.remove($('#image-uploader'));
            FgUtility.startPageLoading();
            FgXmlHttp.replaceContentFromUrl(document.location.href, false, contactEdit.resetCallback, false);
        });
    },
    handleTypeSwitching:function(){
        //Single persone or company population.
        $('div[data-catId=0] div[data-attrId=contactType] input').on('click', function() {
            read = $(this).attr('readonly');
            if (!read) {
                FgXmlHttp.iframepost(path + "?fieldType=1", $('#form1'), false, false, contactEdit.callbackfn, contactEdit.callbackfn);
            }
        });
    },
    handletabs:function() {
            var activeTab = $("#active_tab").val(); 
            if (activeTab != "") { 
                if (activeTab == '21' || activeTab == '68') {
                    $("#fg_field_category_21").show();
                } else if (activeTab == '2') {
                    $("#fg_field_category_2").show();
                    $("#fg_field_category_137").show();
                } else if (activeTab == '3') {
                    $("#fg_field_category_3").show();
                    contactEdit.handleExisting();
                } else {
                    $("#fg_field_category_"+activeTab).show();
                }
            } else {
               $('ul#data-tabs li:first a').click();
            }
    },
   
    handleFileDelete:function(){
        $('[data-dismiss="fileinput"]').on('click', function() {
            var fieldId = $(this).attr('data-fileid');
            var deletedFiles = $("#deletedFiles").val();
            deletedFiles = (deletedFiles == '') ? fieldId : (deletedFiles + ',' + fieldId);
            $("#deletedFiles").val(deletedFiles);
        });
    },
    handleBackLink:function(){
        $('.backbtn').on('click',function(){
            window.location=indexPath;
        });
    },
    handleTabClick:function(){
        $(document).on('click', '#data-tabs li a[data-toggle=tab]', function(event) {
            var page = $(this).attr('href');
            $(".tab-pane").hide();
            $(page).show();
            $("#active_tab").val($(this).closest('li').attr('data-type'));
            if (page == "#fg_field_category_3") {
                contactEdit.handleExisting();
            } else if (page == "#fg_field_category_2") {
                $("#fg_field_category_137").show();
            } else if (page == "#fg_field_category_68") {
                $("#fg_field_category_21").show();
            }
        });
    },
    handleImageTabs:function(){
        $(".tab-pane").each(function() {
            var attrId = $(this).attr('data-catid');
            if ((attrId == '2' || attrId == '137')) {
                //$("#fg_field_category_"+attrId).children().find('.panel-heading');
            } else {
                if (($("#fg_field_category_3").length)) {
                    if (attrId != '1') {
                        $("#fg_field_category_" + attrId).children().find('.panel-heading').remove();
                    }
                } else {
                    $("#fg_field_category_" + attrId).children().find('.panel-heading').remove();
                }
            }
        });
    },
   
    initUpload:function(){
           if(($('body').hasClass('fg-readonly-contact') && $('body').hasClass('fg-contact-module-blk')) || ($('body').hasClass('fg-readonly-sponsor') && $('body').hasClass('fg-sponsor-module-blk'))) {
            if(isCompany && compantLogo != '') {
               contactEdit.hanldePrevenUser();  
             }else if(isCompany && compantLogo == '') {
                 contactEdit.hanldePrevenUser();  
            } else if(isCompany=='0') {
                   contactEdit.hanldePrevenUser();  
             }else{
              FgFileUpload.init($('#image-uploader'), imageElementUploaderOptions);
             } 
        }else{
            FgFileUpload.init($('#image-uploader'), imageElementUploaderOptions);
        }
        
    },
    hanldePrevenUser:function(){
       $(".fg-files-uploaded-lists-wrapper").children().prop('disabled',true);
               $(".fg-files-uploaded-lists-wrapper *").off();
               $("#fg-files-uploaded-lists-wrapper div.fg-messages").html('');
                if($('#image-uploader').length >0){
                  $('#image-uploader').remove();  
               }
               $('#image-uploader').off();
                if($('#fg-del-close').length >0){
                  $('#fg-del-close').prop('disabled',true); 
               }
               contactEdit.showDropzonePreview();  
    },
    handleExistingimageUpload:function () {
         $("#triggerFileUpload").on("click", function(e) {
             console.log(314);
            e.stopImmediatePropagation();
            e.stopPropagation();
            $('#image-uploader').trigger('click');
            return false;
        });
    },
    readonlyFun:function(){
        // Checking whether the logged user has only readonly permission.
        // In that case need to disable all input tags
        // Below code is used to disable the dropzone of images
     
        if(($('body').hasClass('fg-readonly-contact') && $('body').hasClass('fg-contact-module-blk')) || ($('body').hasClass('fg-readonly-sponsor') && $('body').hasClass('fg-sponsor-module-blk'))) {
            

            $(':input').attr("disabled", true);
            $(':button').attr('disabled', false);
        }
    },
    showDropzonePreview:function (clubId) {
        var filename = "";
        var param = "";
        if($("input#fg_field_category_21_21").attr('type') === 'hidden') {
             var filename21 = $("#fg_field_category_21_21").attr('data-value');                  
            if (filename21 != "") {
               var param21 = path21;
               filename = $('#picture_21').val();
               var imagename = path21 + $('#picture_21').val();
               param = 21;
            }
         }else{
              var filename68 = $("#fg_field_category_21_68").attr('data-value');
                if (filename68 != "") {
                 var imagename = path68 +  $('#picture_68').val();
                 filename = $('#picture_68').val();
                 param = 68;
                }
        }
        
        if(filename!=""){
            var rowId = contactId+'_'+param;
            var datatoTemplate = {name: filename, id : rowId, };
             ImagesUploader.showExistImagePreview(rowId,datatoTemplate,imagename);
         }
    },
    pageInit:function(){
        contactEdit.handleSameAs();
        contactEdit.handleExisting();
        contactEdit.handleTypeahead();
        contactEdit.handleMainContactDisplay();
        contactEdit.handlePageInits();
        jQuery('div.date input:enabled:not(:has([readonly]))').parent().datepicker(FgApp.dateFormat);
        contactEdit.handleTypeSwitching();
        contactEdit.handleTabClick();
        contactEdit.handleImageTabs();
        contactEdit.handleBackLink();
        contactEdit.handleFileDelete();
        contactEdit.handleReset();
        contactEdit.readonlyFun();
        
        if (!isFrontend) {
            contactEdit.handleSave();
            FgFormTools.handleformSelect2();
            FgFormTools.handleInputmask();
        }
       
    }
};
$(window).bind("load", function() {
   contactEdit.initUpload();
   contactEdit.handleExistingimageUpload();
});

