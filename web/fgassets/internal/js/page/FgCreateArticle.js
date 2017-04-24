var globalFormId; //form id can be create/edit form/ text-details/attachments-details/settings-details/media-details/
FgCreateArticle = {
    //handleDeleteArticleAttachments
    handleDeleteArticleAttachments: function() {
        $('body').on('click', '.delete-article-attachment', function(e){
            var attachId = $(this).attr('data-parentid');
            if($(this).is(':checked') == true) {
                $('#'+attachId+'_is_deleted-hid').val(attachId);
            } else {
                $('#'+attachId+'_is_deleted-hid').val('');
            }
        });
    },
    //make row color pink on delete
    handleDeleteIconColor: function() {
        $('body').on('click', '.make-switch', function(e){            
            if($(this).is(':checked') == true) {
                $(this).parents('li').addClass('inactiveblock');
            } else {
                $(this).parents('li').removeClass('inactiveblock');
            }
        });        
    },
    //handle delete newly added attachments and images
    handleDeleteNewRow: function(uploadedObj,data) {
        $('body').on('click', '.fg-delete-img', function(e){    //delete media        
            $(this).parents('.fileimgcontent').remove();
        });
        $('body').on('click', '.fg-delete', function(e){  //delete attachments           
            $(this).parents('.filecontent').remove();
            AttachmentsUploader.handleActionButtonContainer();
        });
    },
      //Function to reset sort order of elements
    resetSortOrder: function (parentElement) {
        $parentElementId = $(parentElement);
        var i = 0;
        $parentElementId.find('.fg-dev-sortable').each(function () {
            
            if (!($($(this).parent()).hasClass('inactiveblock') || $($(this).parent().parent()).hasClass('inactiveblock'))) {
                i++;
                $(this).val(i);
                $(this).trigger('change');
               
            }
        });
       
    },
    //handleSave
    handleSave: function(){
        $('body').off('click', '#save_changes');
        $('body').on('click', '#save_changes', function(e){   
            $('body').off('click', '#save_changes');
            if($('.fg-upload-area ul>li.has-error,.fg-article-upload-wrapper ul>li.has-error').length > 0){
                return false;
            }
            
            if($('#article-isdraft').val() == '' && globalFormId == 'fg-article-create-form') {
                $('#article-isdraft').val(0)
            }
            var errorVideoUrl = 0;
            FgCreateArticle.resetSortOrder('.fg-files-uploaded-lists-wrapper');
            
            $( ".invalid-video-url-flag" ).each(function( index ) {
                if($( this ).val() != '') {
                    $(this).parent('div').find('#invalid-url').remove();
                    $(this).parent('div').append('<span class=required id=invalid-url>'+invalidUrl+'</span>');
                    $(this).parent('div').addClass('has-error');
                    errorVideoUrl++;
                }
            });
            if(errorVideoUrl > 0) {
                return false;
            }

            //in case of only one area, area field is hided. make thatdirty
            $(".area-only-one #articleAreas option:first").prop("selected", "selected");

            FgDirtyFields.updateFormState();
            FgCreateArticle.checkMediaDescriptionChange();
            FgCreateArticle.checkTextChange();
            if ($('body').hasClass('dirty_field_used')) {
                $('body').removeClass('dirty_field_used');
            }
//             $('#save_changes,#save-draft').attr('disabled','disabled');
        });

        $('body').off('click', '#save-draft');
        $('body').on('click', '#save-draft', function(e){
            $('#article-isdraft').val(1);
           $('#save_changes').trigger('click');
        });
    },
        
    //check text change and add dirty to all text field on save (for handling text version)
    checkTextChange: function(){        
        $( ".fg-article-text-field" ).each(function( index ) {
            if($(this).hasClass('fairgatedirty')) {
                $('.fg-article-text-field').removeClass('fairgatedirty').addClass('fairgatedirty');
            }
        });        
    },

    //category save lick & popup
    categorysave: function(){
        $('body').on('click', '.fg-new-article-cat', function () {
                var rand = $.now();
                $.post(articleCategorySave, {'catId':rand,'defaultLang':defaultClubLang,'noParentLoad':true  }, function(data) {
                FgModelbox.showPopup(data);
            });
        });
    },

    //handle disable/enable share checkbox
    handleShareDisable: function() {
        $('body').on('change', '#articleAreas', function(e){
            var selectedAreas = $('#articleAreas').val();
            if($.inArray( "Club", selectedAreas ) < 0) {
                $('#show_to_lower_level').prop('disabled', true).prop('checked', false);
            } else {
                $('#show_to_lower_level').prop('disabled', false);
            }
            FgInternal.checkboxReset();
        });
    },

    handleGalleryBrowser: function() {
        FgGalleryBrowser.initialize(galleryBrowserSettings);
        FgGalleryBrowser.setSortable( $('form.fg-nl-form ul.fg-image-area-container') );
    },

    //check media decription and sort order change to handle version and language switching
    checkMediaDescriptionChange: function() {
        $( ".media-desc" ).each(function( index ) {
            if($( this ).hasClass('fairgatedirty') ) {
                $(this).parents('.fg-files-uploaded-list').find('.fg-media-desc-hid').addClass('fairgatedirty');
            }
        });
    },

     //handle video section: when pasting a youtube or vimeo url, add image to that
    handleVideoUrls: function() {
        $('body').off('click', '.fg-a-add-video');
        $('body').on('click', '.fg-a-add-video', function(e){
            FgCreateArticle.addVideoTemplate();
        });
        $('body').off('blur', ".fg-files-uploaded-list .video-url");
        $('body').on('blur', ".fg-files-uploaded-list .video-url", function () {
            parentId = $(this).parents('.fg-files-uploaded-list').attr('id');
            //remove error validation
            $(this).parent('div').find('#invalid-url').remove();
            $(this).parent('div').removeClass('has-error');
            $('#article-img-preview-'+parentId).attr('src', '');
            $(this).parents('.fg-files-uploaded-list').find('.video-thumb').val('');
            //set falg as 1, after success call back unset it
            $(this).parent('div').find('.invalid-video-url-flag').val($(this).parent('div').attr('id'));
            // -----------------------

            var urlVal = $(this).val();
            if(urlVal) {
                var settings = {'urlVal' : urlVal, 'inputElement': $(this), 'successCallBack': FgCreateArticle.changeVideoUrlCallBack, 'parentId' : parentId };
                FgVideoThumbnail.showThumbOnChangingUrl(settings )
            }
        });
    },

    //change video url success call back, add thumbnail image
    changeVideoUrlCallBack: function(settings) {
        $('#article-img-preview-'+settings.parentId).attr('src', settings.videoThumb);
        settings.inputElement.parents('.fg-files-uploaded-list').find('.video-thumb').val(settings.videoThumb).addClass('fairgatedirty');
        settings.inputElement.parent('div').find('.invalid-video-url-flag').val('');
    },

    //handle pagetitle bar
    handleTitleBar: function() {
        $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            tab: false,
            row2: true,
            languageSwitch: true
        });
    },

    CkEditorConfig: function(config, clubLanguages) {
        var textareaName = 'articleText';
        toolbarConfig = ckEditorConfig.articleCommon;

        _.each(clubLanguages,function(lang,key){
            if( $('#'+textareaName+'-'+lang).length ) {
                if(CKEDITOR.instances[textareaName+'-'+lang]){
                    try {
                        //It will cause error when the CKEditor html is replaced manually by FGDirty field, but the distance is not removed
                        CKEDITOR.instances[textareaName+'-'+lang].destroy();
                    } catch (error){}
                    delete CKEDITOR.instances[textareaName+'-'+lang];
                }
                CKEDITOR.replace(textareaName+'-'+lang, {
                    toolbar: toolbarConfig,
                    language :lang,
                    filebrowserBrowseUrl: filemanagerDocumentBrowse,
                    filebrowserImageBrowseUrl: filemanagerImageBrowse,
                }).on('change',function(){
                    $('#articleText-'+lang).html(CKEDITOR.instances[textareaName+'-'+lang].document.getBody().getHtml());
                    $('#articleText-'+lang).val(CKEDITOR.instances[textareaName+'-'+lang].document.getBody().getHtml());
                    //to enablediasable save button on changing
                    FgDirtyFields.enableSaveDiscardButtons();
                    if (CKEDITOR.instances[textareaName+'-'+lang].document.getBody().getHtml() != '' ) {
                        $('#articleText-'+lang).closest('.fg-form-group, .form-group').removeClass('has-error'); // set error class to the control group
                        $('#articleText-'+lang).closest('[dataerror-group]').removeClass('has-error');
                        $('#articleText-'+lang).siblings('span.help-block').text('');
                    }
                    //for validating empty htmls
                    editorContentWOHtml = CKEDITOR.instances[textareaName+'-'+lang].document.getBody().getHtml().replace(/(<(?!img)([^>]+)>)/ig,""); //editor content without html
                    $('#articleTextValidation-'+lang).val(editorContentWOHtml);

                });
                CKEDITOR.instances[textareaName+'-'+lang].addContentsCss('/fgcustom/css/fg-ckeditor-mail.css');                
                
                CKEDITOR.config.dialog_noConfirmCancel = true;
                CKEDITOR.config.extraPlugins='confighelper';
                CKEDITOR.config.allowedContent = {
                    $1: {
                        // Use the ability to specify elements as an object.
                        elements: CKEDITOR.dtd,
                        attributes: true,
                        styles: true,
                        classes: true
                    }
                };
                CKEDITOR.config.disallowedContent = 'script; *[on*]';  
                
                CKEDITOR.on('dialogDefinition', function( ev ) {
                    var diagName = ev.data.name;
                    var diagDefn = ev.data.definition;
                    if(diagName === 'table') {
                        var infoTab = diagDefn.getContents('info');
                        var width = infoTab.get('txtWidth');
                        width.default = "100%";
                        //Overidden the parent width.onChange event.
                        width.onChange = function(){
                            var id = this.domId;
                            $('#' + id + ' input').attr('readonly','readonly');
                            return false;
                        };
                    }
                });
            }
        });
        
        if(config == "simple") {
            $('.fg-simple-editor').hide();
            $('.fg-advanced-editor').show();
        } else {
            $('.fg-editor-switch .fg-advanced-editor').hide();
            $('.fg-editor-switch .fg-simple-editor').show();
        }
    },

    // Handle ck-editor
    handleCkEditor:function (){
        FgCreateArticle.CkEditorConfig('simple', clubLanguages);
        $('body').on('click', '.fg-advanced-editor', function(e){
            FgCreateArticle.CkEditorConfig('advanced', clubLanguages);
        });
        $('body').on('click', '.fg-simple-editor', function(e){
            FgCreateArticle.CkEditorConfig('simple', clubLanguages);
        });
    },

    // list row  handler
    renderTemplate: function (templateId, pathTemplateArticleJson, pathArticleSave, formId, successCallBack) { 
        globalFormId = formId;
        if(globalFormId == 'fg-article-create-form') { //always enable save in create/edit form
            saveChangeSelector = '#save_changes1,#save-draft1';
        } else {
            saveChangeSelector = '#save_changes,#save-draft';
        }
        $('div[data-list-wrap]').rowList({
            template: '#'+templateId,
            jsondataUrl: pathTemplateArticleJson,
            postValues: {'articleId': articleId },
            postURL: pathArticleSave,
            fieldSort: '.sortables',
            submit: ['#save_changes,#save-draft', formId],
            reset: '#reset_changes',
            useDirtyFields: true,
            dirtyFieldsConfig: {"enableDiscardChanges": true, 'saveChangeSelector': saveChangeSelector, 'discardChangeSelector' : "#reset_changes", 'discardChangesCallback': FgCreateArticle.discardChangesCallbackFn, 'fieldChangeCallback' : FgCreateArticle.dirtyfieldChangeCallBk, 'setInitialHtml' : false },
            validate: true,
            initCallback: function() { 
                FgCreateArticle.handleFormElements();
                FgDirtyFields.updateFormState();
                if (editorialMode == 'duplicate') {
                    FgCreateArticle.makeFormDirtyforDuplicate();
                    //To regenerate new thumburl
                    $('.video-url').trigger('blur');
                }
                if(formId == 'fg-article-attachments-form') {//from detail attachment tab - update count in tabs
                    $('#fg_tab_articleAttachments').find('.badge').html(parseInt($('.fg-article-upload-items li').length));
                }
                if(formId == 'fg-article-media-form') {//from detail media tab - update count in tabs
                    $('#fg_tab_articleMedia').find('.badge').html(parseInt($('.fg-files-uploaded-lists-wrapper li').length));
                } 
                if(globalFormId == 'fg-article-text-form' || globalFormId == 'fg-article-create-form') {
                    FgCreateArticle.handleCkEditor();
                }
                if(globalFormId == 'fg-article-settings-form' ) { //update badge in article title in details page
                    FgCreateArticle.switchTitleBadge($('#article-level').val()); 
                }
                
                if(globalFormId == 'fg-article-create-form') {
                    $('#reset_changes').removeAttr('disabled');
                }
                //FAIRDEV-144 
                $('.fg-action-language-switch .btlang.active').trigger('click');
            },
            onSuccessCallback: successCallBack
        });
    },
    /**
     * FAIR-2198 Edit article: Cancel button is not working
     * discard Changes Callback
     * 
     */
    discardChangesCallbackFn : function () {
        if(globalFormId == 'fg-article-create-form') {
            $('.bckid ').trigger('click')
        }
        customFunctions.buildTemplate();
        if (editorialMode == 'duplicate') {
            FgCreateArticle.makeFormDirtyforDuplicate();
        }
        FgCreateArticle.handleFormElements();
        if(globalFormId == 'fg-article-text-form' || globalFormId == 'fg-article-create-form') {
            FgCreateArticle.handleCkEditor();
        }
    },    

//  handle Form Elements
    handleFormElements : function() {                
        FgGlobalSettings.handleDateTimepicker();
        $('select.selectpicker').selectpicker({noneSelectedText: selectTrans});
        $('select.selectpicker').selectpicker('render');    
        FgCreateArticle.setDefaultFields(); //call handle unform after this function
        FgFormTools.handleUniform();        
        if(globalFormId == 'fg-article-attachments-form' || globalFormId == 'fg-article-create-form') {
            AttachmentsUploader.uploadInit();            
        }
        if(globalFormId == 'fg-article-media-form' || globalFormId == 'fg-article-create-form') {
            ImagesUploader.initUpload(articleImgUploaderOptions);
            FgCreateArticle.handleGalleryBrowser();
        }
        if(globalFormId == 'fg-article-text-form' || globalFormId == 'fg-article-create-form') {
            _.each(clubLanguages,function(lang,key){
                FgGlobalSettings.characterCount($('#articleTeaser-'+lang),160, $('#articleTeaser-'+lang).siblings('p'));
            });
        }
        FgStickySaveBarInternal.reInit(0);        
    },
    // in create mode set default fields
    setDefaultFields: function() {
        if(mode == "create") {
            $('#articlePublicationNow').prop("checked", true);
            $('#articleArchivingNever').prop("checked", true);
            $('#articleScopePublic').prop("checked", true);
            $('#articleComment0').prop("checked", true);
            $('#radios-0').prop("checked", true);            
            $('#articleAuthor').val(authorName);
            $('#article-isdraft').val(0);
        }

    },

    //call back function on dirtying any field
    dirtyfieldChangeCallBk: function(originalValue, isDirty) {        
        if($(this).hasClass('media-desc') && (isDirty)) {
            $(this).parent('.fg-media-desc').find('.fg-media-desc-hid').addClass('fairgatedirty');
        }
         FgCreateArticle.checkTextChange();
    },

    //handle enable disable artichiving and publishind date field inputs
    handleDateFields: function() {
        $('body').on('click', '#articlePublicationNow', function(e){
            $('#articlePublicationDate').prop("disabled", true).val("").prop("required", false);
            $('#articlePublicationDate').siblings('.fg-datetimepicker-icon').addClass('fg-datetimepicker-icon-disabled').removeClass('fg-datetimepicker-icon');
        });
        $('body').on('click', '#articlePublicationPlanned', function(e){
            $('#articlePublicationDate').prop("disabled", false).prop("required", true);
            $('#articlePublicationDate').siblings('.fg-datetimepicker-icon-disabled').addClass('fg-datetimepicker-icon').removeClass('fg-datetimepicker-icon-disabled');
        });
        $('body').on('click', '#articleArchivingNever', function(e){
            $('#articleExpiryDate').prop("disabled", true).val("").prop("required", false);
            $('#articleExpiryDate').siblings('.fg-datetimepicker-icon').addClass('fg-datetimepicker-icon-disabled').removeClass('fg-datetimepicker-icon');
        });
        $('body').on('click', '#articleArchivingPlanned', function(e){
            $('#articleExpiryDate').prop("disabled", false).prop("required", true);
            $('#articleExpiryDate').siblings('.fg-datetimepicker-icon-disabled').addClass('fg-datetimepicker-icon').removeClass('fg-datetimepicker-icon-disabled');
        });
    },

    //add video template
    addVideoTemplate: function() {
        var timestamp = $.now();
        var random1 = Math.random().toString(36).slice(2);
        var random2 = Math.random().toString(36).slice(2);
        var thisId = random1+'-'+timestamp+'-'+random2;
        var n = ($( ".fg-files-uploaded-lists-wrapper li.fg-files-uploaded-list" ).length) ? (parseInt($( ".fg-files-uploaded-lists-wrapper li" ).length) + parseInt(1)) : 1;

        var result_data = FGTemplate.bind('article-video-upload', {'id': thisId, 'sort' : n });
        $('.fg-files-uploaded-lists-wrapper').append(result_data);
        ImagesUploader.addImgCallback({}, {'fileid': thisId} )
        FgDirtyFields.enableSaveDiscardButtons();
    },
    
    makeFormDirtyforDuplicate: function () {
        $("#fg-article-create-form input,select,textarea").addClass('fg-dev-newfield');
        //No need, just put to enable the dirty field
        $("#save_changes,#save-draft,#reset_changes").removeAttr('disabled');
    },
   
    handleStatusSwitch: function () {
        FgCreateArticle.switchStatus(articleCurrentStatus);
        
        //article-status
        $('body').on('click', '.lock-article-status', function(e){ 
            var data = {};
            data['articleId'] = articleId;
            data['status'] = ($(this).hasClass('article-deactivate')) ? 1 : 0; 
            FgXmlHttp.post(articleStatusUpdatePath, data , false, FgCreateArticle.statusChangeSuccessCallbck);
        });
    },
    
    //switch status button activate/deactivate
    switchStatus: function (status) {
        $('.lock-article-status').addClass('hide');
        if(status == 0){
            //published
            $('.lock-article-status.article-deactivate').removeClass('hide');
        } else {
            $('.lock-article-status.article-activate').removeClass('hide');
        }          
    },
    
    //switch title badge draft/planned
    switchTitleBadge: function (articleLevel) {
        if(articleLevel == 'draft') {
            $('#article-badge').removeClass('hide').removeClass('fg-badge-green').addClass('fg-badge-dark-grey').text(statusTranslations.draft);
        } else if(articleLevel == 'planned') {
            $('#article-badge').removeClass('hide').removeClass('fg-badge-dark-grey').addClass('fg-badge-green').text(statusTranslations.planned);
        } else {
            $('#article-badge').addClass('hide');
        }  
        $(".fg-action-menu-wrapper").FgPageTitlebar(globalPageTitleBarOptions); //init pagetitlebar ( globalPageTitleBarOptions defined in FgEditorialDetails.js)    
    },
    
    //call back function after drfat/published status change (from editorial details page)
    statusChangeSuccessCallbck: function(result) {
        FgCreateArticle.switchStatus(result.articleStatus);
        FgCreateArticle.switchTitleBadge(result.articleLevel);            
    }
}

//Attachments handler
//var upload  = 'articleAttachments';
AttachmentsUploader = {
     //handle attachments upload
    uploadInit: function(){
        AttachmentsUploader.initUploader(articleUploaderOptions);
        $('.fg-cal-file-upload').on('click', function(){
            $('#file-uploader').trigger('click');
        });
        $('.fg-cal-browse-server').on('click', function(){
            upload  = 'articleAttachments';
            window.open(browseServerPath, "", "width=1000, height=1000");
        });
    },

    initUploader: function(settings){
        uploaderObj = FgFileUpload.init($('#file-uploader'), settings);
    },

    initElements: function (uploadedObj,data){        
        AttachmentsUploader.handleActionButtonContainer();
    },

    handleActionButtonContainer: function(){
        //If any rows exists show else hide
        if($('.filecontent').length > 0){
            $('.fileCount').val($('.filecontent').length);
            $('.fg-upload-area-div').removeClass('hide');
        } else {
            $('.fileCount').val('');
            $('.fg-upload-area-div').addClass('hide');
            $('#filemanager-upload-error-container').html('');
        }
    },

    //when selecting from filemanager filegator
    onFileSelect: function(serverFile) {
       
       
        var fileSize = FgFileUpload.formatFileSize(parseInt(serverFile.size));
        var appendContent = $('<li class="fg-article-upload-item fg-clear filecontent" id="'+serverFile.id+'">' +
        '<div class="col-sm-12 fg-pad-top-5 fg-calendar-item-name">' +
        '<div class="row fg-uploadcalendar-name"><div class="col-md-9"><label class="fg-marg-btm-0"><a  target="_blank" href="'+serverFile.url+'">'+serverFile.name+'</a></label></div><div class="col-md-3"> <span class="fg-file-size"> '+fileSize+' </span></div></div></div>' +
        '<input class="hide" name="file-'+serverFile.id+'" type="text" value="'+serverFile.id+'" data-key="article.attachment.filemanager.'+serverFile.id+'.fileid">'+
        '<a href="javascript:void(0)" class="fg-delete" parentid="'+serverFile.id+'"><i class="fa fa-times-circle fa-2x"></i></a>'+
        '</li>');
        $('.fg-upload-area-div').removeClass('hide');
        $('.fg-article-upload-items').append(appendContent);
       
        FgDirtyFields.updateFormState();
    },
    
    deleteAttachmentonDuplicate:function(fileId){
       $('#'+fileId).find('.fg-delete').parents('.filecontent').remove();   
    },
    
    setUploadErrorMessage:function(uploadObj, data){
        var template = $('#'+articleUploaderOptions.validationErrorTemplateId).html();
        var result = _.template(template, {error : data.result.error, name: data.result.filename});
        $('#'+data.fileid).find('.fg-replacewith-errormsg').html(result);
        $('#'+data.fileid).addClass('has-error');
        $('#'+data.fileid+" input:hidden").remove();
    }
}

var ImagesUploader = {
    initUpload: function(settings){
        $('.fg-media-img-uploader').on('click', function(){
            $('#image-uploader').trigger('click');
        });
        imguploaderObj = FgFileUpload.init($('#image-uploader'), settings);
    },

    //create image for preview
    createImagePreview: function (input, imgTagId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#'+imgTagId).attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    },

    //call back after adding new image
    addImgCallback: function(uploadedObj,data) {
        ImagesUploader.handleSortOrder(uploadedObj,data);
    },
    
    handleSortOrder: function(uploadedObj,data) {
        var rowId = data.fileid;
        var n = ($( ".fg-files-uploaded-lists-wrapper li.fg-files-uploaded-list" ).length) ? (parseInt($( ".fg-files-uploaded-lists-wrapper li" ).length)) : 1;
        if(rowId) {
            $('#'+rowId).find('input.fg-dev-sortable').val(n);
        }
    },

    addGalleryImgCallback: function(data) {
        $.each(data, function(i, item) {
           
                ImagesUploader.addImgCallback({}, {'fileid': item.itemId} );
        });
        
        FgDirtyFields.updateFormState();
    },
    
    setUploadErrorMessage:function(uploadObj, data){
        var template = $('#'+articleImgUploaderOptions.validationErrorTemplateId).html();
        var result = _.template(template, {error : data.result.error, name: data.result.filename });
        $('#'+data.fileid).find('.fg-replacewith-errormsg').html(result);
        $('#'+data.fileid).addClass('has-error');
        $('#'+data.fileid+" input:hidden").remove();
    }
}


