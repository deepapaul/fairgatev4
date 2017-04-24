var fgGalleryUploader = { 
    settings : {},
    init: function(){
        fgGalleryUploader.handleSaveButton();
        fgGalleryUploader.dirtyInit('gallery_uploader_form', true)
        fgGalleryUploader.initUploader(galleryUploaderOptions);
        fgGalleryUploader.handleLangSwitch();
        Pagetitle.switchActive();
        $(".fg-action-gallery-scope").addClass('internal');
        fgGalleryUploader.switchScope();
        
        $( "#gallery_upload_discard" ).click(function() {
           fgGalleryUploader.handleDiscardButton();
        });
    },
    dirtyInit: function(formName, denoteDirty){
        FgDirtyFields.init(formName, {
            dirtyFieldSettings :{
                denoteDirtyForm  : denoteDirty
            }, 
                enableDiscardChanges : false,
        });
    },
    switchScope: function(){
        $( ".fg-action-gallery-scope:not(.fg-action-disabled)" ).click(function() {
            if($(this).hasClass('internal')){
                $('input[type=radio][value=INTERNAL]').attr('checked',true);
                $('input[type=radio]').uniform();
                $(".fg-action-gallery-scope").removeClass('internal');
                $(".fg-action-gallery-scope").addClass('public');
            }
            else if($(this).hasClass('public')){
                $('input[type=radio][value=PUBLIC]').attr('checked',true);
                $('input[type=radio]').uniform();
                $(".fg-action-gallery-scope").removeClass('public');
                $(".fg-action-gallery-scope").addClass('internal');
            }
        });
    },
    triggerUpload: function(){
        $('#file-uploader').trigger('click');
    },
    triggerVideoUpload: function(){
        $.post(galleryVideoUploadPopup, function(data) {
            FgModelbox.showPopup(data);
        });
    },
    triggerEditDesc: function(checkedIds, itemCount){
        $.post(galleryEditDescPopup, {'chekedIds' : checkedIds, 'itemCount': itemCount}, function(data) {
            FgModelbox.showPopup(data);
        });
    },
    initElements: function (uploadedObj,data){
        var rowId = data.fileid;
        if(rowId)
        {
            $("#gallery_upload_save").addClass('disabled');
//            $(".fg-action-gallery-scope").addClass('fg-action-disabled');
//            $("#gallery_upload_discard").addClass('disabled');
            
            $('input[type=radio][value=PUBLIC]').attr('checked',true);
            $('#'+rowId).find('input[type=radio]').uniform();
        
            $('#'+rowId).find('.fg-delete').click(function(){
                $(this).parents('.filecontent').remove();
                fgGalleryUploader.handleActionButtonContainer();
            });
            
            fgGalleryUploader.handleActionButtonContainer();
        }
    },
    setThumbnail: function(uploadObj, data){
        var rowId = data.fileid;
        if(rowId)
        {
            var icon = "<img src='"+tempUrl+data.formData.title+"'/>";
            $('#'+rowId).find('.fg-dev-scalable-img-wrapper').html(icon);
            $('#'+rowId+' .fg-gallery-upload-progress').remove();
        }
    },
    handleActionButtonContainer: function(){
        //If any rows exists show else hide
        if($('.filecontent').length > 0){
            $('#action-button-container').removeClass('hide');
            $(".fg-action-menu-wrapper").FgPageTitlebar({
                actionMenu: true,
                title: true,
                languageSwitch : true,
                galleryScope : true,
            });
            $('.upload-page-title-text').remove();
            $('.page-title-text').before('<span class="upload-page-title-text fg-marg-right-5">'+ uploadTitleText + '</span>');
            $('#imgCount').val($('.filecontent').length);
            $('.fg-gallery-upload-wrapper').removeClass('hide');
        } else {
            $('#action-button-container').addClass('hide');
            $('.upload-page-title-text').remove();
            var switchType = localStorage.getItem(FgLocalStorageNames.gallery.currentViewMode);
            FgGalleryView.handleActionMenu(1, switchType);
            $('#imgCount').val('');
            $('.fg-gallery-upload-wrapper').addClass('hide');
            $('#gallery-upload-error-container').html('');
            $("#gallery_upload_save").addClass('disabled');
            FgDirtyFields.removeAllDirtyInstances();
        }
    },
    initUploader: function(settings){
        FgFileUpload.init($('#file-uploader'), settings);
    },
    handleSaveButton: function(){
         $( "#gallery_upload_save" ).click(function() {
             FgDirtyFields.removeAllDirtyInstances();
             var albumId = localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum);
             var albumid = (albumId != null || albumId !='') ? albumId : '';
             $('#albumId').val(albumid);
            //Validate the step1 form
            clickedButton = $(this);
            
            //if disabled class set return
            if(clickedButton.hasClass('disabled') || clickedButton.attr('disabled') ==  'disabled')
                return;
            
           if($('#gallery_uploader_form-content li.has-error').length >0) {
                return false;
           }
            clickedButton.addClass('disabled');
            
                var paramObj = {};
                paramObj.form = $('#gallery_uploader_form');
                paramObj.url = galleryUploadPath;
                paramObj.successCallback = function(){
                                                        $('.filecontent').remove();
                                                        fgGalleryUploader.handleActionButtonContainer();
                                                        FgGalleryView.loadGallery();
                                                        FgInternal.pageLoaderOverlayStop();
                                                    };
                    FgXmlHttp.formPost(paramObj);
                    FgInternal.pageLoaderOverlayStart();   
            
          });
    },
    handleDiscardButton: function(){
        $('.filecontent').remove();
        fgGalleryUploader.handleActionButtonContainer();
    },
    //Lang Switch
    handleLangSwitch: function(){
        $(document).off('click', 'button[data-elem-function=switch_lang]');
            /* function to show data in different languages on switching language */
        $(document).on('click', 'button[data-elem-function=switch_lang]', function () {
            selectedLang = $(this).attr('data-selected-lang');
            FgUtility.showTranslation(selectedLang);
        });
    },
    setErrorMessage: function(uploadObj, data) {
      var template = $('#'+galleryUploaderOptions.validationErrorTemplateId).html();
      var result = _.template(template, {error : data.result.error ,name : data.result.filename});
      $('#'+data.fileid).find('.fg-replacewith-errormsg').html(result);
      $('#'+data.fileid).addClass('has-error');
      $('#'+data.fileid+" input:hidden").remove();
  },
        }
var Pagetitle = {
    switchActive : function (){
        $('body').on('click', '.btlang', function () {
            var attr = $(this).attr('data-selected-lang');
            $('.btlang').removeClass('active');
            $(this).addClass('active');
        });
    }
}

