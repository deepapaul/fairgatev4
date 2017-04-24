/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgWebsiteSettings {
    
    constructor() {
        this.handleTitleBar();
        this.renderTemplate(pathSettingsDetails);        
        FgGlobalSettings.handleLangSwitch(); 
        this.handleSave();          
        FgInternal.toolTipInit();   
    }

    public initUpload(){        
       FgFileUpload.init($('#image-uploader'), imageElementUploaderOptions);
       FgFileUpload.init($('#favicon-uploader'), faviconUploaderOptions);
       FgFileUpload.init($('#ogimg-uploader'), OGImageUploaderOptions); 
       FgFileUpload.init($('#file-uploader'), domainVerifyFileOption);       
    }
    
    //handleSave
    public handleSave(){
        var _this = this;        
//        $('body').off('click', '#save_changes');
        $('body').on('click', '#save_changes', function(e){   
            $('#g-analytics-error').removeClass('fg-replacewith-errormsg');
            $('#failcallbackClientSide').addClass('hide'); 
            
            //$('body').off('click', '#save_changes');                                               
            var trackerCode = $("#g-analytics").val();
            if(trackerCode != '') {
                if(!_this.validatetrackerID(trackerCode)) { 
                    $('#g-analytics-error').html(wrongTraker).addClass('has-error').addClass('fg-replacewith-errormsg');                     
                } 
            }
            
            if($('.fg-replacewith-errormsg').length > 0){
                $('.fg-replacewith-errormsg').parents('.form-group').addClass('has-error');
                $('#failcallbackClientSide').removeClass('hide');          
                return false;
            } 
        });
    }
    
    public triggerUploadButton() {
        $("#triggerLogoUpload").on("click", function(e) {
            e.stopImmediatePropagation();
            e.stopPropagation();
             if ( e.target === this ) {
                    $('#image-uploader').trigger('click');
             }            
        });
        
        $("#triggerFaviconUpload").on("click", function(e) {
            e.stopImmediatePropagation();
            e.stopPropagation();
             if ( e.target === this ) {
                    $('#favicon-uploader').trigger('click');
             }            
        });
        
        $("#triggerOGImageUpload").on("click", function(e) {
            e.stopImmediatePropagation();
            e.stopPropagation();
             if ( e.target === this ) {
                    $('#ogimg-uploader').trigger('click');
             }            
        }); 
        
        $("#triggerVerificFileUpload").on("click", function(e) {
            $('#file-uploader').trigger('click');
        });
    }
    
    public handleTextareaLength() {
        //handle textarea length
        _.each(clubLanguages,function(lang,key){            
            FgGlobalSettings.characterCount($('#siteDesc-'+lang),250, $('#siteDesc-'+lang).siblings('p'));
        });
    }
    
    // Function to handle titlebar
    public handleTitleBar()
    {
        $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            tab: false,
            row2: true,
            languageSwitch: true
        });        
    }
    
    //make dafult language in language button area
    public makeDefaultLanguage() {
        $('button[data-elem-function=switch_lang]').removeClass('adminbtn-ash active').addClass('fg-lang-switch-btn');
        $('button[data-elem-function=switch_lang][data-selected-lang=' + defaultClubLang + ']').removeClass('fg-lang-switch-btn').addClass('adminbtn-ash active');
    }
    
    public renderTemplate(pathSettingsDetails) 
    {    
        _this = this;     
        $('div[data-list-wrap]').rowList({
            template: '#templateSettings',
            jsondataUrl: pathSettingsDetails,
            postURL: pathSettingsSave, 
            fieldSort: '.sortables',
            submit: ['#save_changes', 'fg-web-settings-form'],
            reset: '#reset_changes',
            useDirtyFields: true, 
            dirtyFieldsConfig: { "enableDiscardChanges": true, 'saveChangeSelector': '#save_changes', 'discardChangeSelector': "#reset_changes", discardChangesCallback : FgWebsiteSettingsPublic.discardChangesClback  },
            validate: true,
            initCallback: function () {
                _this.initUpload();
                _this.triggerUploadButton();
                _this.deleteElement();
                //reinut sticky save bar
                FgStickySaveBarInternal.reInit(0);
                FgWebsiteSettingsObj.handleTextareaLength();
            },
            onSuccessCallback: function(){
                $('#image-uploader').fileupload('destroy');
                $('#favicon-uploader').fileupload('destroy');
                $('#ogimg-uploader').fileupload('destroy');
                $('#file-uploader').fileupload('destroy');      
                FgWebsiteSettingsObj.renderTemplate(pathSettingsDetails);
                FgWebsiteSettingsObj.makeDefaultLanguage();       
            };
        });
    } 
    
    public deleteElement() {
        $(document).off('click', '.fg-del-close');
        $(document).on('click', '.fg-del-close', function () {
            var rowId = $(".imagefield-req").val();
            $(this).parents().children(".imagefield-req").val("");
            if($(this).parents().children(".imagefield-file").length>0){
               $(this).parents().children(".imagefield-file").val(""); 
            }
            if($(this).hasClass('fg-dev-removelogo')) {
                $('#faviconFilePath').val(""); 
                $('#btn-favicongenerator').prop('disabled', true); 
            }            
            $(this).parent().parent().remove();            
            FgDirtyFields.updateFormState();
            return false;
        });
    }    
    
    public domainVerificationSuccess(uploadObj, data){
        var rowId = data.fileid;
        var tempUrl = '/uploads/temp/';
        if(rowId)
        {           
            $('#'+rowId).html('<span><span class="fileinput-filename">'+data.files[0].name+'</span>&nbsp;<a id="fg-del-close" class="close fg-del-close fg-marg-left-5 fg-marg-top-5" ></a></span>');                                  
            $('#'+rowId).parents().children('.imagefield-req').val(data.formData.title);
            $('#'+rowId).parents().children('.imagefield-file').val(data.files[0].name);
            ImagesUploader.setHiddenValue(rowId,data);
        }
        
        return false;
    }
    
    public removePreviousImg(uploadObj, data, settings){
        //remove previous image in case of drag images
        if($(settings.dropZoneElement).find('.fg-dev-dropzone-preview').length > 0) {
            $(settings.dropZoneElement).find('.fg-dev-dropzone-preview').remove();
        }
    }
    
    public setThumbnail(uploadObj, data, settings){             
        // add new image preview
        var rowId = data.fileid;
        var tempUrl = '/uploads/temp/';
        if(rowId)
        {
            var icon = "<img class='fg-thumb' src='"+tempUrl+data.formData.title+"'/>";
            $('#'+rowId).find('.fg-thumb-wrapper').html(icon);
            $('#'+rowId).parents().children('.imagefield-req').val(data.formData.title);
            $('#'+rowId).parents().children('.imagefield-file').val(data.files[0].name);
            
            //in case of default logo, enable generate favicon button
            if( settings.dropZoneElement == '#fg-default-logo-wrapper' ) {                
                $('#faviconFilePath').val(baseUrl+tempUrl+data.formData.title);  
                $('#btn-favicongenerator').removeAttr('disabled');   
            }    
            FgDirtyFields.updateFormState();                
        }
        return false;
    }
      
    public generateFavIcon(){
        let filePath = $('#faviconFilePath').val();
        let apiUrl = 'https://realfavicongenerator.net/api/favicon_generator';
        let apiKey = '87d5cd739b05c00416c4a19cd14a8bb5632ea563';
        
        if(filePath != ''){
            let uploadedFileDetail = filePath.split('/').pop() + '##__##' +$('#logo_originalname').val();
            let apiRequest = {};
            apiRequest.favicon_generation = {};
            apiRequest.favicon_generation.callback = {};
            apiRequest.favicon_generation.callback.type = 'url';
            apiRequest.favicon_generation.callback.url = favIconCallbackPath;
            apiRequest.favicon_generation.callback.short_url = 'false';
            apiRequest.favicon_generation.callback.path_only = 'false';
            apiRequest.favicon_generation.callback.custom_parameter = uploadedFileDetail;
            apiRequest.favicon_generation.master_picture = {};
            apiRequest.favicon_generation.master_picture.type = 'url';
            apiRequest.favicon_generation.master_picture.url = filePath;
            apiRequest.favicon_generation.files_location = {};
            apiRequest.favicon_generation.files_location.type = 'no_location';
            apiRequest.favicon_generation.api_key = apiKey;

            var apiForm = jQuery('<form>', {
                'action': apiUrl,
                'method': 'post'
            }).append(jQuery('<input>', {
                'name': 'json_params',
                'value': JSON.stringify(apiRequest),
                'type': 'hidden'
            }));
            apiForm.appendTo('body');
            apiForm.submit();
            apiForm.remove();
        }
    }

    private validatetrackerID(trackerCode){
        if(trackerCode != ''){
            if(/^UA-(\d{5,10})-(\d{1,3})$/.test(trackerCode)){
                return true;
            } else {
               return false; 
            }
        } else {
            return false;
        }
    }

}

var FgWebsiteSettingsPublic = {
    discardChangesClback: function() {
        FgWebsiteSettingsObj.initUpload();
        FgWebsiteSettingsObj.triggerUploadButton();
        FgStickySaveBarInternal.reInit(0);
        FgWebsiteSettingsObj.handleTextareaLength();
        FgWebsiteSettingsObj.makeDefaultLanguage();
    } 
}





