var ImagesUploader = {
 
    initUploader: function(settings){
        FgFileUpload.init($('#dropzoneImage'), settings);
    },
    ///********For Single Files ****************/
    setThumbnail: function(uploadObj, data){
     
        var rowId = data.fileid;
        var tempUrl = '/uploads/temp/';
        if(rowId)
        {
            var icon = "<img class='fg-thumb' src='"+tempUrl+data.formData.title+"'/>";
            $('#'+rowId).find('.fg-thumb-wrapper').html(icon);
            $('#'+rowId).parents().children('.imagefield-req').val(data.formData.title);
            $('#'+rowId).parents().children('.imagefield-file').val(data.files[0].name);
            if($('#'+rowId).parents().children('.imagefile-changed').length > 0){
               $('#'+rowId).parents().children('.imagefile-changed').val(1); 
            }
           
        }
      
        if($(imageElementUploaderOptions.removefileobj).length > 0){
            
            $(imageElementUploaderOptions.removefileobj).val("");
        }
         return false;
    },
    removeFileUpdate: function(uploadObj, data) {
        
        var rowId = data.fileid;
       $(imageElementUploaderOptions.removefileobj).val(rowId); 
         
    },
    setErrorMessage: function(uploadObj, data) {
        $(imageElementUploaderOptions.errorContainer).css('color','red');
        $(imageElementUploaderOptions.errorContainer).html(data.result.message);
         
    },
    serverErrorMessage: function(uploadObj, data) {
        var template = $('#'+imageElementUploaderOptions.validationErrorTemplateId).html();
        var result = _.template(template, {error : data.result.error,name:data.result.name });
        $('#'+data.fileid).find('.fg-replacewith-errormsg').html(result);
        $('#'+data.fileid).addClass('has-error');
        $('#'+data.fileid+" input:hidden").remove();
    },
    deleteElement: function () {

        $(document).on('click', '#fg-del-close', function () {
            var rowId = $(".imagefield-req").val();
            $(this).parents().children(".imagefield-req").val("");
            if($(this).parents().children(".imagefield-file").length>0){
               $(this).parents().children(".imagefield-file").val(""); 
            }
            ImagesUploader.cmsDeleteUpdate($(this).parents().children(".imagefile-delete"));
            $(this).parent().parent().remove();
            ImagesUploader.myDataFiledUpdated(rowId);
            ImagesUploader.updateDirtyField();
            
            
            return false;
        });
    },
    myDataFiledUpdated: function (rowId) {
        if (imageElementUploaderOptions.pageName === 'mydata') {
                if (imageElementUploaderOptions.contactType == 1) {
                    
                    $(imageElementUploaderOptions.removefileobj).val("68");
                } else
                    $(imageElementUploaderOptions.removefileobj).val("21");
            } else {
                $(imageElementUploaderOptions.removefileobj).val(rowId);
            }
        
    },
    cmsDeleteUpdate: function (obj) {
        if(imageElementUploaderOptions.mulltiEdit==1){
            obj.val(1);
        }
     },
    updateDirtyField: function () {
        if (imageElementUploaderOptions.removeElementdirty == 1) {
                FgDirtyFields.updateFormState();
            }
        if(imageElementUploaderOptions.enableButton==1){
             $('form').find('input[type="submit"]').removeAttr('disabled');
            $('form').find('input[type="reset"]').removeAttr('disabled');
        }
        
    },
    //create image for preview
    showExistImagePreview: function (rowId, datatoTemplate,imagename) {
        if($("#" + rowId).length == 0) {
            var templateDetails =$('#'+imageElementUploaderOptions.fileListTemplate).html(); 
            var result = _.template(templateDetails, datatoTemplate); 
            var templateDetails =$('#'+imageElementUploaderOptions.fileListTemplate).html(); 
            var result = _.template(templateDetails, datatoTemplate);    
            if($(imageElementUploaderOptions.previewClass).length > 0){
            $(imageElementUploaderOptions.previewClass).remove();
            }
            $(imageElementUploaderOptions.fileListTemplateContainer).append(result);
            var icon = "<img class='fg-thumb' src='"+imagename+"'/>";
            $('#'+rowId).find('.progress').hide();
            $('#'+rowId).find('.fg-thumb-wrapper').html(icon);
        }
        
    },
    
     ///********For Multiple Files ****************/
    setThumbnailMulti: function(uploadObj, data){
        
        var elemName = uploadSettings.elemid;
        var dynamicId  = elemName.replace ( /[^\d.]/g, '' );
        var rowId = data.fileid;
        var tempUrl = '/uploads/temp/';
        if(rowId)
        {
            var icon = "<img class='fg-thumb' src='"+tempUrl+data.formData.title+"'/>";
            $('#'+rowId).find('.fg-thumb-wrapper').html(icon);
            ImagesUploader.setHiddenValue(rowId,data);
            
        }
        return false;
     },createConfigDisabled:function (uploadedObj,data){
            var rowId = data.fileid;
            if(rowId)
            {
                 ImagesUploader.handleProgressbarCreation(); 
            }
        },
        handleProgressbarCreation:function(){
            if(imageElementUploaderOptions.pageName=="createlogo"){
                $("#tm_back_btn").attr('disabled',true);
                $("#tm_send_btn").attr('disabled',true);
            }
             if(imageElementUploaderOptions.pageName=="updatelogo"){
               $("#reset_changes").attr('disabled',true);
               $("#save_bac").attr('disabled',true);
            }
        },
     setHiddenValue:function(rowId,data){
            var hidElemId =$('#'+rowId).parents().children('.imagefield-req').attr("id");
            var hideFileId = $('#'+rowId).parents().children('.imagefile-req').attr("id");
            var hideFileChanged = $('#'+rowId).parents().children('.imagefile-changed').attr("id");
            var hideDeleted = $('#'+rowId).parents().children('.imagefile-delete').attr("id");
            $('#'+hidElemId).val(data.formData.title);
            $('#'+hidElemId).attr('value',data.formData.title);
            $('#'+hideFileId).val(data.files[0].name);
            $('#'+hideFileId).attr('value',data.files[0].name);
            $('#'+hideFileChanged).val(1);
            $('#'+hideDeleted).val(0);  
            ImagesUploader.updateDirtyField();
     },
     updateSingleViewForLogo:function(uploadObj, data){
         
        var elemName = uploadObj.currentTarget.id;
        var dynamicId  = elemName.replace ( /[^\d.]/g, '' );
        lenPrev =  $(imageElementUploaderOptions.fileTemplateContainer+dynamicId).find(imageElementUploaderOptions.previewClass).length;
        if(lenPrev > 0){
            $(imageElementUploaderOptions.fileTemplateContainer+dynamicId).find(imageElementUploaderOptions.previewClass).remove();
        }

    },
    showExistImagePreviewForLogo: function (rowId,id,datatoTemplate,filenamepath) {
        if($("#" + rowId).length == 0) {
            var templateDetails =$('#'+imageElementUploaderOptions.fileListTemplate).html(); 
            var result = _.template(templateDetails, datatoTemplate); 
            var templateDetails =$('#'+imageElementUploaderOptions.fileListTemplate).html(); 
            var result = _.template(templateDetails, datatoTemplate);    
            lenPrev =  $(imageElementUploaderOptions.fileTemplateContainer+id).find(imageElementUploaderOptions.previewClass).length;
            if(lenPrev > 0){
                $(imageElementUploaderOptions.fileTemplateContainer+id).find(imageElementUploaderOptions.previewClass).remove();
            }
            $(imageElementUploaderOptions.fileTemplateContainer+id).append(result);
            var icon = "<img class='fg-thumb' src='"+filenamepath+"'/>";
            $('#'+rowId).find('.progress').hide();
            $('#'+rowId).find('.fg-thumb-wrapper').html(icon);
        } 
        
    },
    
    //create image for preview
    createImagePreview: function (input, imgTagId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#'+imgTagId).attr('src', e.target.result).css({'height':'100px'});
            }

            reader.readAsDataURL(input.files[0]);
        }
    },
    
    //call back after adding new image
    addImgCallback: function(uploadedObj,data) {
        ImagesUploader.handleSortOrder(uploadedObj,data);
    },
    updateSingleView:function(){
        if($(imageElementUploaderOptions.previewClass).length > 0){
            $(imageElementUploaderOptions.previewClass).remove();
        }
        if(imageElementUploaderOptions.updateFormstate==1){
            FgDirtyFields.updateFormState();
       }
       if(imageElementUploaderOptions.enableButton==1){
            $('form').find('input[type="submit"]').removeAttr('disabled');
            $('form').find('input[type="reset"]').removeAttr('disabled');
       }

    },
    handleSortOrder: function(uploadedObj,data) {
        var rowId = data.fileid;
        var n = ($( ".fg-files-uploaded-lists-wrapper li.fg-files-uploaded-list" ).length) ? (parseInt($( ".fg-files-uploaded-lists-wrapper li" ).length)) : 1;
        if(rowId) {
            $('#'+rowId).find('input.fg-dev-sortable').val(n);
        }
    },
    dirtyInit: function(formName, denoteDirty){
        FgDirtyFields.init(formName, {
            dirtyFieldSettings :{
                denoteDirtyForm  : denoteDirty
            }, 
            setNewFieldsClean:true,
            initialHtml : false,
            saveChangeSelector : "#save_changes,#save_bac",
            enableDiscardChanges : true,
            discardChangesCallback : ImageElement.discardChangesCallback
        });
    },
    discardChangesCallback: function(){
        if(status == 'old') {
            FgGalleryBrowser.initialize(galleryBrowserSettings);
            ImageElement.handleSortOrder();
            //remove uniform to reinit for discard changes
            var uniformSuspectedElements = $("#rowDisplay,#columnDisplay,#sliderDisplay");
            if ( uniformSuspectedElements.parent().parent().is( "div" ) ) {
                uniformSuspectedElements.unwrap().unwrap();
            }
            ImageElement.commonInit('discard');
            if(displayType === 'slider') {
                $('.fg-slider-time').attr('disabled', false);
            }
        }
    },

    
}

