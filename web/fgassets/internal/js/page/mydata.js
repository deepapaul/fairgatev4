$(function(){
    $(document).off('click', '#save_changes');
    $(document).on('click', '#save_changes', function () {
        myData.setDirtyChoiceValues();
        var objectGraph = FgInternalParseFormField.fieldParse();
            stringifyData = JSON.stringify(objectGraph);
        //console.log(objectGraph);return false;
        var toConfirmFields = myData.getFieldsToConfirm();
        Metronic.startPageLoading();
        FgXmlHttp.iframepost(saveDataPath, $('#fg-myDataForm'), {saveData: stringifyData, toConfirmFields: toConfirmFields}, false, sucessCallback, failCallback);

        return false;
    });
    function sucessCallback() {
        FgDirtyFields.init('fg-myDataForm');
        location.href = window.location.href;
    }
    function failCallback() {
        setTimeout(function(){ //update uniform
            FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
            myData.initPageFunctions('fail');
            $('.alert-danger').show();
            
        },200);
    }
    // Avoid clicking on readonly elements.
    $(document).off('focus', '[readonly]');
    $(document).on("focus", '[readonly]', function(){
        $(this).blur();
    });
});
var myData = {
    handletabs: function(){
        var activeTab = $("#active_tab").val();
        if (activeTab != "") {
            $('ul#data-tabs li[data-type='+activeTab+'] a').click();
            if (activeTab == '21' || activeTab == '68') {
                $("#fg_field_category_21").show();
            } else if (activeTab == '2') {
                $("#fg_field_category_2").show();
                $("#fg_field_category_137").show();
            } else if (activeTab == '3') {
                $("#fg_field_category_3").show();
                myData.handleExisting();
            } else {
                $("#fg_field_category_"+activeTab).show();
            }
        } else {
           $('ul#data-tabs li:first a').click();
        }
    },
    handleTabClick: function(){
        $(document).on('click', '#data-tabs li a[data-toggle=tab]', function(event) {
            var page = $(this).attr('href');
            $(".tab-pane").hide();
            $(page).show();
            $("#active_tab").val($(this).closest('li').attr('data-type'));
            if (page == "#fg_field_category_3") {
                myData.handleExisting();
            } else if (page == "#fg_field_category_2") {
                $("#fg_field_category_137").show();
            } else if (page == "#fg_field_category_68") {
                $("#fg_field_category_21").show();
            }
        });
    },
    handleSameAs:function(){
        if ($('input#same_invoice_address').is(':checked')) {
            $('div[data-catId=137] :input').attr('checked', false);
            var fileAttrIds = [];
            $('div[data-catId=2] :input[data-addresstype=both]').each(function() {
                addressId = $(this).attr('data-attrId');
                if ($('div[data-catId=137] :input[data-addressid=' + addressId + ']').attr('type') == 'file') {
                    var invAttrId = $('div[data-catId=137] :input[data-addressid=' + addressId + ']').attr('data-attrid');
                    fileAttrIds.push(addressId + '-' + invAttrId);
                    myData.copyFileData(addressId, invAttrId);
                } else {
                    $('div[data-catId=137] :input[data-addressid=' + addressId + ']').val($(this).val());
                    $('div[data-catId=137] :input[data-addressid=' + addressId + ']').attr('disabled', true);
                    
                }
            });
            $('#duplicateFileAttrs').val(fileAttrIds.toString());
            $('div[data-catId=2] div[data-addresstype=both] input').each(function() {
                addressId = $(this).parents('div[data-addresstype=both]').attr('data-attrId');
                if ($(this).is(':checked')) {
                    if ($(this).val() == '') {
                        $('div[data-catId=137] div[data-addressid=' + addressId + '] input[value=""]').attr('checked', true);
                    } else {
                        $('div[data-catId=137] div[data-addressid=' + addressId + '] input[value="' + $(this).val() + '"]').attr('checked', true);
                    }
                }
                $('div[data-catId=137] div[data-addressid=' + addressId + '] input[value="' + $(this).val() + '"]').attr('disabled', true);
            });
            $('div[data-catId=137] input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
            jQuery('div[data-catId=137] div.date input:disabled').siblings('span').off("click");
        }
        else {
            jQuery('div[data-catId=137] div.date input:disabled').parent().datepicker({autoclose: true, weekStart: 1});
            $('div[data-catId=137] input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').removeClass('fg-label-inactive');
            $('div[data-catId=137] :input').attr('disabled', false);
        }
            FgInternal.checkboxReset();
    },
    handleRequiredToggle:function(req){
        $('div[data-required=selected_members] label .required').hide();
        if (req == '' || req === null) {
            $('div[data-required=all_members] label .required').hide();
        } else {
            $('div[data-required=all_members] label .required').show();
            isFedMember = fedMembers.indexOf(':' + req + ':');
            $('div[data-required=selected_members]').each(function() {
                members = $(this).attr('data-members');
                reqType = $(this).attr('data-reqType');
                if (members.indexOf(':' + req + ':') != '-1') {
                    $(this).find('label .required').show();
                }
                //handle fed field
                if (isFedMember != '-1' && reqType == 'FRD') {
                    $(this).find('label .required').show();
                }
            });
        }
    },
    handlePageInits:function(){
        // invoice /corespondence address same as toggle function
        $('div[data-catId=2] input#same_invoice_address').on('click', function() {
            myData.handleSameAs();
            FgDirtyFields.updateFormState();
        });
        $('div[data-catId=0] select').on('change', function() {
            myData.handleRequiredToggle($(this).val());
        });
        $('form input[type=file]').change(function() {
            var fileAttr = $(this).attr('data-addressid') + '-' + $(this).attr('data-attrid');
            myData.checkDuplicateFile(fileAttr);
            
            var elemIdArray = $(this).attr('id').split('_');
            var attrId = elemIdArray[elemIdArray.length - 1];
            $('input#file_'+attrId).val($(this).val());
            if ($(this).val() != '') {
                // Remove attibute from deleted file set.
                myData.removeDeletedFileAttribute(attrId);
            }
        });
        $('input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
        $('input:file[readonly]').css('cursor','not-allowed').click(function(){
            return false;
          })
    },
    initClockIcon: function(){
        $('.preview').popover({
            'trigger':'hover',
            'html':true,
            'placement':'bottom'
        });
    },
    initSelectpicker: function(){
        $('.selectpicker').each(function(){
            $(this).selectpicker({
                noneSelectedText: jstranslations.noneSelectedText
            });
        });
    },
    
    handleActivetabs: function() {
        $("#paneltab li").removeClass("active");
        $("#paneltab li[data-target=2]").addClass("active");
    },
    
    initPageFunctions: function(from){
        FgMoreMenu.initServerSide('paneltab');
        myData.handleTabClick();
        myData.handletabs();
        myData.handleActivetabs();
        myData.handleSameAs();
        myData.handlePageInits();
        myData.initClockIcon();
        myData.handleReadonly();
        $("#fg_field_category_21").children().removeClass();
        myData.initSelectpicker();
        $("div.date :input:not([readonly])").parent().datepicker(FgInternal.dateFormat);
      // $("div.date input:not([readonly]").parent().datepicker(FgInternal.dateFormat);       
        $('#fg-contact-data').removeClass('hide');
        FgMoreMenu.initClientSide('data-tabs', 'data-tabs-content', 'data');
        FgFormTools.handleUniform();
        FgFormTools.handleInputmask();
        FgFormTools.handleBootstrapSelect();
        FgFormTools.handleSelect2();
        myData.initUpload(imageElementUploaderOptions);
        myData.handleExisting();
        myData.handleTypeahead();
        myData.handleMainContactDisplay();
        myData.handleImageTabs();
        myData.handleFileDelete();
        //FAIR-1772 Company user: restriction in data section
        myData.handleOtherClubContact();
        myData.resetDatakeyAttributes();
        myData.initDirtyFields(from);
        myData.handleDiscardChanges();
        myData.handleExistingimageUpload();

        $( ".fg-action-menu-wrapper" ).FgPageTitlebar({
            title       : true,
            tab       : tabCondition,
            search     :false,
            actionMenu  : false,
            tabType  :'server'

        }); 
    },

    showErrorTriangle: function(){
        $('span.help-block').each(function(){
            var parentElemId = $(this).parents('.tab-pane').attr('data-catid');
            $($('ul.data-more-tab li[data-type='+parentElemId+']').find('i.fa-exclamation-triangle')).removeClass('hide');
        })
    },
    handleReadonly: function(){
        $('select[readonly]').each(function(){
            var hiddenElem = '<input type="hidden" value="'+$(this).val()+'" id="'+$(this).attr('id')+'" name="'+$(this).attr('name')+'" />';
            $('#fg-myDataForm').append(hiddenElem);
            $(this).attr('disabled', 'true');
        });
        $('div[readonly]').each(function(){
            var atrId = $(this).attr('id');
            var catAtrId = atrId.replace('fg_field_category_','');
            var catAtrIdArr = catAtrId.split('_');
            var atrName = 'fg_field_category[' + catAtrIdArr[0] + '][' + catAtrIdArr[1] + ']';
            if ($(this).find('input').attr('type') == 'checkbox') {
                atrName = atrName + '[]';
            }
            var hiddenElem = '<input type="hidden" value="'+$(this).attr('data-attrval')+'" id="'+atrId+'" name="'+atrName+'" />';
            $('#fg-myDataForm').append(hiddenElem);
            $(this).find('input').attr('disabled', 'true');
        });
    },
    initUpload:function(imageElementUploaderOptions){
         FgFileUpload.init($('#image-uploader'), imageElementUploaderOptions);
         //for  edit preview
        
         var filename = "";
         var param = "";
         
         if ($("input#fg_field_category_21_21").attr('type') === 'hidden') {
              var  filename21 = $("#fg_field_category_21_21").attr('data-value');
                if (filename21 != "") {
                     var imagename = path21 + $('#picture_21').val();
                      filename = $('#picture_21').val();
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
            var datatoTemplate = { name: filename, id : rowId, };
            ImagesUploader.showExistImagePreview(rowId,datatoTemplate,imagename);
        }
 
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
    handleTypeahead:function(){
        $('#mainContactAuto').fbautocomplete({
            url: contactUrl, // which url will provide json!
            removeButtonTitle: removestring,
            params: {'isCompany': 0} ,
            selected: mcSelected,
            maxItems: 1,
            useCache: true,
            formName:'mainContactNameTitle',
            onItemSelected: function($obj, itemId, selected) {
                $('#mainContactId').val(itemId);
                $('#fg_field_category_1_mainContactName').val(selected[0].title);
                //FgDirtyForm.checkForm($('body').find('form').attr('id'));
            },
            onItemRemoved: function($obj, itemId) {
                $('#mainContactId').val('');
                $('#fg_field_category_1_mainContactName').val('');
                //FgDirtyForm.checkForm($('body').find('form').attr('id'));
            },
            onAlreadySelected: function($obj) {

            }
        });
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
    handleFileDelete:function(){
        $('[data-dismiss="fileinput"]').on('click', function() {
            var fieldId = $(this).attr('data-fileid');
            myData.addDeletedFileAttribute(fieldId);
            setTimeout(function(){
                FgDirtyFields.enableSaveDiscardButtons();
            }, 10);
            var fileAttr = $(this).attr('data-addressAttrId') + '-' + fieldId;
            myData.checkDuplicateFile(fileAttr);
        });
    },

    // Function to get field details that needed confirmation.
    getFieldsToConfirm: function() {
        var confirmFields = [];
        $('[data-key]').each(function() {
            var fieldData = $(this).attr('data-key').replace(/\./g, '_');
            confirmFields.push(fieldData);
        });
        var confirmFieldsStr = confirmFields.toString();

        return confirmFieldsStr;
    },

    

    // Function to reset 'data-key' attributes of elements if null.
    resetDatakeyAttributes: function() {
        $('[data-key]').each(function() {
            if ($(this).attr('data-key') == '') {
                $(this).removeAttr('data-key');
            }
        })
    },

    // Function to handle the main contact switching of company contact if not contact created club.
    handleOtherClubContact:function(){
        var mainContactType = $('div[data-catId=3] div[data-attrId=mainContact] input:checked').val();
        $('div[data-catId=3] div[data-attrId=mainContact] input').attr('disabled', 'disabled');
        $('#membership').attr('disabled', 'disabled');
        if (mainContactType == 'existing') {
            $('div[data-catId=1] .form-body .row').hide();
            $('div[data-catId=1] .form-body .row:first').show();
            $('div[data-attrId=mainContactName] input').attr('readonly', 'readonly');
            $('#mainContactAuto').attr('readonly','readonly');
            $('.fg-contact-with-auto').addClass('fg-input-wrapper-disabled');
            $('div[data-attrId=mainContactFunction] input').attr('readonly', 'readonly');
            $('#has_main_contact_address').attr('disabled','disabled');

        }
    },
    initDirtyFields: function(from){
        var dirtyElements = $('[data-key].fairgatedirty');
        FgDirtyFields.init('fg-myDataForm');
        $(dirtyElements).each(function(){
            $(this).addClass('fairgatedirty');
        });
        if (from == 'fail') {
            FgDirtyFields.enableSaveDiscardButtons();
        }
    },
    handleExistingimageUpload:function () {
           
     
       $("#triggerFileUpload").on("click", function(e) {
            e.stopImmediatePropagation();
            e.stopPropagation();
            $('#image-uploader').trigger('click');
            
        });
    },
    
    handleDiscardChanges: function(){
        $('#fg-myDataForm').off('click', '#reset_changes');
        $('#fg-myDataForm').on('click', '#reset_changes', function () {
            $('#fg-myDataForm').html(initialFormHtml);
            
            //remove the bs-select wrapper form the html
            $('.btn-group.bootstrap-select.form-control.bs-select').remove();
            $('.bs-select').show();
            
            $('.select2-container.form-control.select2').remove();
            
            //remove the uniform wrapper from the html
            var uniformSuspectedElements = $("#fg-myDataForm input:radio, #fg-myDataForm input:checkbox");
            if ( uniformSuspectedElements.parent().parent().is( "div" ) ) {
                uniformSuspectedElements.unwrap().unwrap();
            }

            myData.initPageFunctions();
            $('.alert-danger').hide();
            return false;
        });
    },
    copyFileData: function(attrId, invAttrId){
        var mainElem = $('input[type=file][data-attrid='+attrId+']');
        var fileName = $(mainElem.parent().parent().find('span.fileinput-filename')).text();
        var fileElem = $('input[type=file][data-addressid='+attrId+']');
        if (fileName == '') {
            $(fileElem.parent().find('span.fileinput-new')).show();
            $(fileElem.parent().find('span.fileinput-exists')).hide();
            $(fileElem.parent().parent().find('span.fileinput-filename')).text(fileName);
            $(fileElem.parent().parent().find('a[data-dismiss=fileinput]')).css('display', 'none');
            // Add attibute to deleted file set.
            myData.addDeletedFileAttribute(invAttrId);
        } else {
            $(fileElem.parent().find('span.fileinput-new')).hide();
            $(fileElem.parent().find('span.fileinput-exists')).show();
            $(fileElem.parent().parent().find('span.fileinput-filename')).text(fileName);
            $(fileElem.parent().parent().find('a[data-dismiss=fileinput]')).css('display', 'inline-block');
            // Remove attibute from deleted file set.
            myData.removeDeletedFileAttribute(invAttrId);
        }
        $('input#file_'+invAttrId).val($('input#file_'+attrId).val());
    },
    addDeletedFileAttribute: function(attrId){
        var deletedFiles = $("#deletedFiles").val();
        deletedFiles = (deletedFiles=='') ? attrId : (deletedFiles + ',' + attrId);
        $("#deletedFiles").val(deletedFiles);
    },
    removeDeletedFileAttribute: function(attrId){
        var deletedFiles = $("#deletedFiles").val();
        var deletedFilesArray = deletedFiles.split(',');
        deletedFilesArray = jQuery.grep(deletedFilesArray, function(value) {
            return value != attrId;
        });
        deletedFiles = deletedFilesArray.toString();
        $("#deletedFiles").val(deletedFiles);
    },
    checkDuplicateFile: function(fileAttr){
        var duplicateFileAttrs = $('#duplicateFileAttrs').val().split(',');
        if (jQuery.inArray(fileAttr, duplicateFileAttrs) != '-1') {
            duplicateFileAttrs = jQuery.grep(duplicateFileAttrs, function(value) {
                return value != fileAttr;
            });
        }
        $('#duplicateFileAttrs').val(duplicateFileAttrs.toString());
    },
    setDirtyChoiceValues: function(){
        $('div[data-key].radio-list').each(function(){
            var dataKey = $(this).attr('data-key');
            var elemIds = [];
            $($(this).find('input:checked')).each(function(){
                elemIds.push($(this).val());
            });
            var elemIdStr = elemIds.toString().replace(/,/g, ";");
            $('input[data-key="'+dataKey+'"]').val(elemIdStr);
        });
        FgDirtyFields.updateFormState();
    }
};
