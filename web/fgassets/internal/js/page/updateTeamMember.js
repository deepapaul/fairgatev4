$(document).ready(function() {
    updateMember.handleSave();
    $(document).off('click', '#cancel');
    $(document).on('click', '#cancel', function () {
        location.href = memberVars.memberlistPath;
    });
});
var updateMember = {
    //save handling
    handleSave: function() {
        $(document).off('click', '#save_changes');
        $(document).on('click', '#save_changes', function () {
            memberVars.createOneMore = $('#oneMore').is(':checked');
            var objectGraph = FgInternalParseFormField.fieldParse();
            stringifyData = JSON.stringify(objectGraph);
            var toConfirmFields = updateMember.getFieldsToConfirm();
            var roleType = (memberVars.roleType == 'team') ? 'team' : 'workgroup';
            var currRole = JSON.parse(localStorage.getItem(roleType + "_" + memberVars.currClubId + "_" + memberVars.currContactId));
            //disable double click on save button
            $(document).off('click', '#save_changes');
            FgXmlHttp.iframepost(memberVars.saveDataPath, $('#updateMember'), {toConfirmData: stringifyData, toConfirmFields: toConfirmFields, currRoleId: currRole.id}, false, updateMember.sucessCallback, updateMember.failCallback);

            return false;
        });
    },
    
    //success call back
    failCallback: function () {
        updateMember.handleSave();
        FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
        updateMember.initPageFunctions();
        $('form').find('button#save_changes').removeAttr('disabled');
    },
    
    //fail call back
    sucessCallback: function ()
    {
        FgDirtyFields.init('updateMember');
        FgInternal.showToastr(memberVars.flashMsg);
        location.href = memberVars.createOneMore ? memberVars.oneMorePath : memberVars.memberlistPath;
    },
    
    initSelectpicker: function(){
        $('.selectpicker').each(function(){
            $(this).selectpicker({
                noneSelectedText: jstranslations.noneSelectedText
            });
        });
    },
    initClockIcon: function(){
        $('.fg-clock-blk').css('right', '0px'); //to remove
        $('.preview').popover({
            'trigger':'hover',
            'html':true,
            'placement':'bottom'
        });
    },
    initPageFunctions: function(){
        updateMember.initSelectpicker();
        jQuery('div.date input:enabled:not([readonly]').parent().datepicker(FgInternal.dateFormat);
        updateMember.initClockIcon();
        updateMember.handleSameAs();
        updateMember.handleReadonly();
        FgFormTools.handleInputmask();
        FgFormTools.handleUniform();
        FgFormTools.handleBootstrapSelect();
        FgFormTools.handleSelect2();
        updateMember.handleExisting();
        updateMember.handleMainContactDisplay();
        updateMember.handleTypeahead();
        updateMember.handleTypeSwitching();
        updateMember.handleFileDelete();
        updateMember.handleFileUpload();
        updateMember.initDirtyFields();
        updateMember.hideCategoryHavingNoElements();
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

    // Function to handle 'same as' functionality.
    handleSameAs: function(){
        $('div[data-catId=2] input#same_invoice_address').on('click', function() {
            if ($('input#same_invoice_address').is(':checked')) {
                $('div[data-catId=137] :input').attr('checked', false);
                var fileAttrIds = [];
                $('div[data-catId=2] :input[data-addresstype=both]').each(function() {
                    var addressId = $(this).attr('data-attrId');
                    if ($('div[data-catId=137] :input[data-addressid=' + addressId + ']').attr('type') == 'file') {
                        var invAttrId = $('div[data-catId=137] :input[data-addressid=' + addressId + ']').attr('data-attrid');
                        fileAttrIds.push(addressId + '-' + invAttrId);
                        updateMember.copyFileData(addressId, invAttrId);
                    } else {
                        $('div[data-catId=137] :input[data-addressid=' + addressId + ']').val($(this).val());
                    }
                });
                $('#duplicateFileAttrs').val(fileAttrIds.toString());
                $('div[data-catId=2] div[data-addresstype=both] input').each(function() {
                    var addressId = $(this).parents('div[data-addresstype=both]').attr('data-attrId');
                    if ($(this).is(':checked')) {
                        if ($(this).val() != '') {
                            $('div[data-catId=137] div[data-addressid=' + addressId + '] input[value=' + $(this).val() + ']').attr('checked', true);
                        } else {
                            $('div[data-catId=137] div[data-addressid=' + addressId + '] input[value=""]').attr('checked', true);
                        }
                    }
                });
                $('div[data-catId=137] input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
                jQuery('div[data-catId=137] div.date input:disabled').siblings('span').off("click");
                FgInternal.checkboxReset();
            } else {
                jQuery('div[data-catId=137] div.date input:disabled').parent().datepicker({autoclose: true, weekStart: 1});
                $('div[data-catId=137] input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').removeClass('fg-label-inactive');
                FgInternal.checkboxReset();
            }
            FgFormTools.handleBootstrapSelect();
            FgFormTools.handleSelect2();
        });
    },
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
        var mainContactId = $('#mainContactId').val();
        var mainContactName = $('#fg_field_category_1_mainContactName').val();
        var mcSelected = ((mainContactId != '') && (mainContactName != '')) ? [{id: mainContactId, title: mainContactName}] : '';
        $('#mainContactAuto').fbautocomplete({
            url: contactUrl, // which url will provide json!
            removeButtonTitle: removestring,
            params: {'isCompany': 0,'contactId':editContactId} ,
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
    handleOtherClubContact:function(){
        mainContactType = $('div[data-catId=3] div[data-attrId=mainContact] input:checked').val();
        $('div[data-attrId=membership] select').attr('disabled','disabled');
        if(mainContactType == 'withMain' || mainContactType == 'noMain'){
            $('div[data-catId=3] div[data-attrId=mainContact] input[value=existing]').attr('disabled','disabled');
        }
        else if(mainContactType == 'existing'){
            $('div[data-catId=3] div[data-attrId=mainContact] input').attr('disabled','disabled');
            $('div[data-catId=1] .form-body .row').hide();
            $('div[data-catId=1] .form-body .row:first').show();
            $('div[data-attrId=mainContactName] input').attr('readonly','readonly');
            $('#mainContactAuto').attr('readonly','readonly');
            $('.fg-contact-with-auto').addClass('fg-input-wrapper-disabled');
            $('div[data-attrId=mainContactFunction] input').attr('readonly','readonly');
        }
    },
    handleTypeSwitching:function(){
        //Single person or company population.
        $('div[data-catId=0] div[data-attrId=contactType] input').on('click', function() {
            read = $(this).attr('readonly');
            if(!read){
                $('#failcallbackServerSide span').text("");
                $('#failcallbackServerSide').hide();
                FgXmlHttp.iframepost(path+"?fieldType=1", $('#updateMember'), false, false, updateMember.switchCallbackfn, updateMember.switchCallbackfn);
            }
        });
    },
    switchCallbackfn:function(){
        $('input[type=radio]:disabled,input[type=checkbox]:disabled').parents('.radio-inline').addClass('fg-label-inactive');
        FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
        updateMember.initPageFunctions();
    },
    handleFileDelete:function(){
        $('[data-dismiss="fileinput"]').on('click',function(){
            var fieldId = $(this).attr('data-fileid');
            updateMember.addDeletedFileAttribute(fieldId);
            setTimeout(function(){
                $('form').find('button#save_changes').removeAttr('disabled');
            }, 10);
            var fileAttr = $(this).attr('data-addressAttrId') + '-' + fieldId;
            updateMember.checkDuplicateFile(fileAttr);
        });
    },
    handleFileUpload: function(){
        $('form input[type=file]').change(function(){
            var fileAttr = $(this).attr('data-addressid') + '-' + $(this).attr('data-attrid');
            updateMember.checkDuplicateFile(fileAttr);

            var elemIdArray = $(this).attr('id').split('_');
            var attrId = elemIdArray[elemIdArray.length - 1];
            $('input#file_'+attrId).val($(this).val()).addClass('fairgatedirty');
            if ($(this).val() != '') {
                // Remove attibute from deleted file set.
                updateMember.removeDeletedFileAttribute(attrId);
            }
        });
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
            updateMember.addDeletedFileAttribute(invAttrId);
        } else {
            $(fileElem.parent().find('span.fileinput-new')).hide();
            $(fileElem.parent().find('span.fileinput-exists')).show();
            $(fileElem.parent().parent().find('span.fileinput-filename')).text(fileName);
            $(fileElem.parent().parent().find('a[data-dismiss=fileinput]')).css('display', 'inline-block');
            // Remove attibute from deleted file set.
            updateMember.removeDeletedFileAttribute(invAttrId);
        }
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
    initDirtyFields: function(){
        var dirtyElements = $('[data-key].fairgatedirty');
        FgDirtyFields.init('updateMember');
        $(dirtyElements).each(function(){
            $(this).addClass('fairgatedirty');
        });
    },
    hideCategoryHavingNoElements: function(){
        $('div[data-catid].row').each(function(){
            var inputLength = $(this).find('input').length;
            var selectLength = $(this).find('select').length;
            if ((inputLength == 0) && (selectLength == 0)) {
                $(this).hide();
            }
        });
    },
    handleReadonly: function(){
        $('select[readonly]').each(function(){
            var hiddenElem = '<input type="hidden" value="'+$(this).val()+'" id="'+$(this).attr('id')+'" name="'+$(this).attr('name')+'" />';
            $('#updateMember').append(hiddenElem);
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
    }
};
