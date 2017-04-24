/// <reference path="../directives/jquery.d.ts" /> 
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />

/**
  * Handle text element edit/ create
  */
var saveRedirectBck:boolean = false;
var globalFormId; //form id can be create/edit form/ text-details/attachments-details/settings-details/media-details/
class FgTextElement { 
    
    constructor() {
         
    } 
    /**
     * render content tab
     */ 
    public renderContent() {
        this.handleTitleBar(true);
        if(mode == 'edit')
            $('.fg-news-editorial-article-wrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        $('#addTextElementEdit').removeClass('fg-dis-none');
        $('.text-toggle[data-tab="article-section-text"]').trigger('click');
    }
    
    /**
     * render log tab 
     */
    public renderLog() {
        this.handleTitleBar(false);
        $('.fg-news-editorial-article-wrapper').addClass('fg-dis-none');
        $('#addTextElementEdit').addClass('fg-dis-none');
        $('#div-textelement-history').addClass('hide');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        var CmsTextElementLog = new FgCmsTextElementLog();
        CmsTextElementLog.init();
    }
    /**
     * reset button click function  - redirect to edit page
     */
    public redirectBack(){
        $('#reset_changes').on('click',function(){
            window.location = editpageUrl;
        });
    }
    
    public setErrorMessage(uploadObj, data) {
       var template = $('#'+textImgUploaderOptions.validationErrorTemplateId).html();
       var result = _.template(template, {error : data.result.error,name:data.result.name });
       $('#'+data.fileid).find('.fg-replacewith-errormsg').html(result);
       $('#'+data.fileid).addClass('has-error');
       $('#'+data.fileid+" input:hidden").remove();
    };
    
    //make row color pink on delete
    public handleDeleteIconColor() {
        $('body').on('click', '.make-switch', function(e){            
            if($(this).is(':checked') == true) {
                $(this).parents('li').addClass('inactiveblock');
            } else {
                $(this).parents('li').removeClass('inactiveblock');
            }
        });        
    }
    //handle delete newly added attachments and images
    public  handleDeleteNewRow() {
        $('body').on('click', '.fg-delete-img', function(e){    //delete media        
            $(this).parents('.fileimgcontent').remove();
        });
    }
    /**
     * toggle the tabs action
     */
    public toggleTextElementTabs() {
        let _this = this;
        $(document).on('click', '.text-toggle', function (e) {
            $('.article-text').addClass('hide');
            $('#show-history-preview').parent().addClass('hide');
            var divClass = $(this).attr('data-tab');
            if (divClass == 'article-section-history') {
                $('#addTextElementEdit').addClass('hide');
                $('#div-textelement-history').removeClass('hide')
                
                _this.renderTextElementHistory();
                _this.handleTitleBar(false);
               
            } else { //current tab
                 _this.handleTitleBar(true);
                  $('#addTextElementEdit').removeClass('hide');
                if (updateHistoryFlag == 1) {//if history updated reload tab
                    updateHistoryFlag = 0; //reset flag
                    _this.renderTemplate('templateCreateTextElement', pathTemplateArticleJson, pathArticleSave, 'fg-article-create-form');
                }
            }
            $('.' + divClass).removeClass('hide');
        });
    }
    /**
     * update revision history
     */
    public updateRevisionText(){
        let _this = this;
        $('#update-history-revision i').off('click');
        $('#update-history-revision i').on('click',function(){
            var element = $(this).parent().data('element-id');
            var version = $(this).parent().data('content');
            var path = revisionUpdatePath.replace('%23textelement%23', element).replace('%23version%23',version);
            $.ajax({
                type: 'GET',
                url: path,
                success: function(response) {
                    _this.renderTextElementHistory();
                    updateHistoryFlag = 1;
                },
                async: false
            });
        });     
    }
    /**
     * render history 
     */
    public renderTextElementHistory() {
        let _this = this;
        $.getJSON(historyUrl, null, function (data) {
            var htmlContent = FGTemplate.bind(textElementTemplateHistoryId, data);
            $('#div-textelement-history').html(htmlContent);
            $('#preview-history ').off('click');
            $('#preview-history ').on('click',function(){
                $('#preview-text-element-version').removeClass('hide');
                $('#show-history-preview').html($(this).data('content'));
            });
            _this.updateRevisionText();
        });
    }
    //handleSave
    public handleSave(){
        let _this = this;
        $('body').off('click', '#save_changes');
        $('body').on('click', '#save_changes', function(e){
            errorVideoUrl = 0;
            $( ".video-url" ).each(function( index ) {
                if($(this).parents('.fg-files-uploaded-list').children('.fg-thumb-wrapper').children().attr('src') == '' ){
                    $(this).parent('div').find('#invalid-url').remove();
                    $(this).parent('div').append('<span class=required id=invalid-url>'+invalidUrl+'</span>');
                    $(this).parent('div').addClass('has-error');
                    errorVideoUrl++;
                }
            });
            if(errorVideoUrl > 0) {
                return false;
            }
            langError = 0;
            if(mode != 'create'){
                _.each(clubLanguages,function(lang,key){
                   _this.displayError(lang);
                });
            }else
                _this.displayError(defaultClubLang);
            if(langError > 0){
                   return false; 
            }
            if($('li.has-error').length > 0){
                $('#failcallbackClientSide').css('display', 'block');
                return false;
            }
            FgDirtyFields.updateFormState();
            _this.checkMediaDescriptionChange();
            if ($('body').hasClass('dirty_field_used')) {
                $('body').removeClass('dirty_field_used');
            }
            //resetting sort value for images and videos combined
            $('.sortables:not(.inactiveblock)').each(function(i,val){
                $(this).find('.fg-dev-sortable').val(i+1);
            });
        });

        $('body').off('click', '#save-draft');
        $('body').on('click', '#save-draft', function(e){
           saveRedirectBck = true;
           $('#save-draft').val(1);
           $('#save_changes').trigger('click');
        });

    }
    /**
     * display errors in form
     */
    public displayError(lang){
        $('#textValidation-'+lang+'-error').remove();
        if($('#articleTextValidation-'+lang).val() == ''){
            if(mode == 'edit' && defaultClubLang != lang ){
                $('#'+lang).addClass('error');
                return ;
            }
            langError++;
            $('#failcallbackClientSide').css('display','block');
            $('#'+lang).addClass('error');
            $('#calDescDiv_'+lang).addClass('has-error');
            $('#articleTextValidation-'+lang).parent().append('<span id="textValidation-'+lang+'-error" class="help-block">'+statusTranslations.required+'</span>');
            return ;
        }
    }
    /**
     * language switching
     */
    public handleLangSwitch() {
        _.each(clubLanguages, function (lang, key) {
            if ($('#articleTextValidation-' + lang).val() == '') {
                $('#' + lang).addClass('error');
            }
        });
    }
    /**
     * handle gallery browser
     */
    public  handleGalleryBrowser() {
        FgGalleryBrowser.initialize(galleryBrowserSettings);
        FgGalleryBrowser.setSortable( $('.fg-files-uploaded-lists-wrapper') );
    }

    //check media decription and sort order change to handle version and language switching
    public  checkMediaDescriptionChange() {
        $( ".media-desc" ).each(function( index ) {
            if($( this ).hasClass('fairgatedirty') ) {
                $(this).parents('.fg-files-uploaded-list').find('.fg-media-desc-hid').addClass('fairgatedirty');
            }
        });
    }

     //handle video section: when pasting a youtube or vimeo url, add image to that
    public handleVideoUrls() {
        let _this = this;
        $('body').off('click', '.fg-a-add-video');
        $('body').on('click', '.fg-a-add-video', function(e){
            _this.addVideoTemplate();
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
                var settings = {'urlVal' : urlVal, 'inputElement': $(this), 'successCallBack': _this.changeVideoUrlCallBack, 'parentId' : parentId };
                FgVideoThumbnail.showThumbOnChangingUrl(settings )
            }
        });
    }

    //change video url success call back, add thumbnail image
    public changeVideoUrlCallBack(settings) {
        $('#article-img-preview-'+settings.parentId).attr('src', settings.videoThumb);
        settings.inputElement.parents('.fg-files-uploaded-list').find('.video-thumb').val(settings.videoThumb).addClass('fairgatedirty');
        settings.inputElement.parent('div').find('.invalid-video-url-flag').val('');
    }

    //handle pagetitle bar
    public handleTitleBar(langSwitch) {
        scope = angular.element($("#BaseController")).scope();
        if (langSwitch) {
            /* action menu bar ---- */
            $(".fg-action-menu-wrapper").FgPageTitlebar({
                title: true,
                tab: true,
                tabType: 'client',
                row2: true,
                languageSwitch: true,
            });
        } else {
            /* action menu bar ---- */
            $(".fg-action-menu-wrapper").FgPageTitlebar({
                title: true,
                tab: true,
                row2: true,
                tabType: 'client',
                
            });
        }
    }
    
     /*
     * ck editor configurations 
     * @param array clubLanguages
     * 
     */
    public CkEditorConfig(clubLanguages) {
        var textareaName = 'articleText';
        toolbarConfig = ckEditorConfig.addTextElement;
        _.each(clubLanguages, function (lang, key) {
            if ($('#' + textareaName + '-' + lang).length) {
                if (CKEDITOR.instances[textareaName + '-' + lang]) {
                    try {
                        CKEDITOR.instances[textareaName + '-' + lang].destroy();
                    }
                    catch (error) { }
                    delete CKEDITOR.instances[textareaName + '-' + lang];
                }
                
                CKEDITOR.config.bodyClass = 'fg-web-theme-0'+publicConfig.theme+' fg-body-ckeditor' ;
                CKEDITOR.config.contentsCss = ['/fgassets/website/css/fg-web-theme-0' + publicConfig.theme +'.css','/fgassets/website/css/fg-web-style.css','/'+colorCssPath+'/'+publicConfig.theme+'/'+publicConfig.cssColorScheme,'/'+themecssPath+'/'+publicConfig.theme+'/'+publicConfig.cssFile];
                CKEDITOR.config.dialog_noConfirmCancel = true;
                CKEDITOR.config.extraPlugins = 'confighelper';
                CKEDITOR.config.allowedContent = {
                    $1: {
                        elements: CKEDITOR.dtd,
                        attributes: true,
                        styles: true,
                        classes: true
                    }
                };
                CKEDITOR.config.disallowedContent = 'script; *[on*]';
                CKEDITOR.config.forcePasteAsPlainText = true;
                CKEDITOR.on('dialogDefinition', function (ev) {
                    var diagName = ev.data.name;
                    var diagDefn = ev.data.definition;
                    if (diagName === 'table') {
                        var infoTab = diagDefn.getContents('info');
                        var width = infoTab.get('txtWidth');
                        width['default'] = "100%";
                        width.onChange = function () {
                            var id = this.domId;
                            $('#' + id + ' input').attr('readonly', 'readonly');
                            return false;
                        };
                    }
                });
                CKEDITOR.replace(textareaName + '-' + lang, {
                    toolbar: toolbarConfig,
                    language: lang,
                    filebrowserBrowseUrl: filemanagerDocumentBrowse,
                    filebrowserImageBrowseUrl: filemanagerImageBrowse,
                }).on('change', function () {
                    $('#articleText-' + lang).html(CKEDITOR.instances[textareaName + '-' + lang].document.getBody().getHtml());
                    $('#articleText-' + lang).val(CKEDITOR.instances[textareaName + '-' + lang].document.getBody().getHtml());
                    FgDirtyFields.enableSaveDiscardButtons();
                    if (CKEDITOR.instances[textareaName + '-' + lang].document.getBody().getHtml() != '') {
                        $('#' + lang).removeClass('error');
                        $('#articleText-' + lang).closest('.fg-form-group, .form-group').removeClass('has-error');
                        $('#articleText-' + lang).closest('[dataerror-group]').removeClass('has-error');
                        $('#articleText-' + lang).siblings('span.help-block').text('');
                    }
                    editorContentWOHtml = CKEDITOR.instances[textareaName + '-' + lang].document.getBody().getHtml().replace(/(<(?!img)([^>]+)>)/ig, "");
                    $('#articleTextValidation-' + lang).val(editorContentWOHtml);
                });
               CKEDITOR.instances[textareaName + '-' + lang].addContentsCss('/fgcustom/css/fg-ckeditor-mail.css');
            }
        });
    };

    // list row  handler
    public renderTemplate(templateId, pathTemplateTextElementJson, pathTextElementSave, formId) { 
        globalFormId = formId;
        saveRedirectBck = false;
        $('div[data-list-wrap]').rowList({
            template: '#'+templateId,
            jsondataUrl: pathTemplateTextElementJson,
            postValues: {'elementId': elementId },
            postURL: pathTextElementSave,
            fieldSort: '.sortables',
            submit: ['#save_changes,#save-draft', formId],
            reset: '#reset_changes',
            useDirtyFields: true,
            dirtyFieldsConfig: {"enableDiscardChanges": true, 'saveChangeSelector': '#save_changes,#save-draft', 'discardChangeSelector' : "#reset_changes", 'discardChangesCallback': this.discardChangesCallbackFn, 'fieldChangeCallback' : this.dirtyfieldChangeCallBk, 'setInitialHtml' : false },
            validate: true,
            initCallback: function() { 
                var fgTextElement = new FgTextElement();
                fgTextElement.handleFormElements();
                var imagesUploader = new ImagesUploader();
                imagesUploader.initUpload(textImgUploaderOptions);
                fgTextElement.handleGalleryBrowser();
                fgTextElement.CkEditorConfig( clubLanguages);
                if(elementId !== 'new'){
                    fgTextElement.handleLangSwitch();
                }
                //FAIRDEV-144 
                $('.fg-action-language-switch .btlang.active').trigger('click');
                 radioVal = $("input[name='radios']:checked").val();
                
                if(radioVal == 'topSlider' ||  radioVal == 'bottomSlider'){
                   $('#frm-txt-slider').removeClass("hide");
                }
                $('input:radio[name="radios"]').change(function(){
                    
                    if($(this).val() == 'topSlider' ||  $(this).val() == 'bottomSlider'){
                       $('#frm-txt-slider').removeClass("hide");
                    }else{
                      $('#frm-txt-slider').addClass("hide");  
                    }
                });
                
            },
            stopSortableCallback:function(){
                FgDirtyFields.updateFormState();
            },
            onSuccessCallback: this.successCallBack
        });
    }
    
    public successCallBack(){
        if(saveRedirectBck){
            window.location = editpageUrl;
        }
    }
    /**
     * discard changes call back
     */
    public discardChangesCallbackFn() {
        customFunctions.buildTemplate();
        var imagesUploader = new ImagesUploader();
        imagesUploader.initUpload(textImgUploaderOptions);
        var fgTextElement = new FgTextElement();
        fgTextElement.handleFormElements();
        fgTextElement.CkEditorConfig( clubLanguages);
        radioVal = $("input[name='radios']:checked").val();
        if (radioVal == 'topSlider' || radioVal == 'bottomSlider') {
            $('#frm-txt-slider').removeClass("hide");
        }
          $('input:radio[name="radios"]').change(function () {

                  if ($(this).val() == 'topSlider' || $(this).val() == 'bottomSlider') {
                      $('#frm-txt-slider').removeClass("hide");
                  }
                  else {
                      $('#frm-txt-slider').addClass("hide");
                  }
              });
    }

    //  handle Form Elements
    public handleFormElements() {
        if(mode == "create") {
            $('#radios-0').prop("checked", true);
        }                
        FgFormTools.handleUniform();        
        FgStickySaveBarInternal.reInit(0);        
    }
    

    //call back function on dirtying any field
    public dirtyfieldChangeCallBk(originalValue, isDirty) {        
        if($(this).hasClass('media-desc') && (isDirty)) {
            $(this).parent('.fg-media-desc').find('.fg-media-desc-hid').addClass('fairgatedirty');
        }
    }

    //add video template
    public addVideoTemplate() {
        var timestamp = $.now();
        var random1 = Math.random().toString(36).slice(2);
        var random2 = Math.random().toString(36).slice(2);
        var thisId = random1+'-'+timestamp+'-'+random2;
        var n = ($( ".fg-files-uploaded-lists-wrapper li.fg-files-uploaded-list" ).length) ? (parseInt($( ".fg-files-uploaded-lists-wrapper li" ).length) + parseInt(1)) : 1;
        var result_data = FGTemplate.bind('article-video-upload', {'id': thisId, 'sort' : n });
        $('.fg-files-uploaded-lists-wrapper').append(result_data);
        FgDirtyFields.enableSaveDiscardButtons();
    }
	
}

class ImagesUploader {
    constructor() {
        
    } 
    
    public initUpload(settings){
        $('.fg-media-img-uploader').on('click', function(){
            $('#image-uploader').trigger('click');
        });
       FgFileUpload.init($('#image-uploader'), settings);
    }

    //create image for preview
    public createImagePreview(input, imgTagId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#'+imgTagId).attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
    //file upload callback
    public addImgCallback(){
        FgDirtyFields.updateFormState();
    }

}

class FgCmsTextElementLog  {
    constructor() {
        
    } 
    
    public init() {
        this.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    }
    
    public reload() {
        listTable.ajax.reload();
    }
    
    public dataTableOpt() { 
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var i = 0;
        columnDefs.push({"name": "date", "type": "moment-" + currentDateFormat, "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['date'];
                row.displayData = '&nbsp;&nbsp;' + row['date'];
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "option", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">'+statusTranslations[row['status']]+'</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">'+statusTranslations[row['status']]+'</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">'+statusTranslations[row['status']]+'</span>' : '-'));
                var type = (row['type'] === 'element') ? statusTranslations.element : statusTranslations.page_assignment;
                row.sortData = row['type'];
                row.displayData = type + flag;

                return row; 
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_before", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueBefore'];
                row.displayData = row['valueBefore'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_after", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueAfter'];
                row.displayData = row['valueAfter'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "edited_by", "targets": i++, data: function (row, type, val, meta) {
                if(row['activeContactId'] != null && row['isStealth'] == false)
                    var replacement = profilePath.replace("|contactId|", row['activeContactId']);
                else if(row['changedBy'] != 1 && row['activeContactId'] == null )
                    var replacement = row['contact']+' ('+row['clubChangedBy']+')';
                else
                    var replacement = row['contact'];
                row.sortData = row['contact'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + replacement + '">' + row['contact'] + '</a></div>' :  replacement;
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});

        datatableOptions = {
            columnDefFlag: true,
            ajaxPath: logUrl,
            ajaxparameterflag: true,
            ajaxparameters: {
                elementId: elementId
            },
            columnDefValues: columnDefs,
            serverSideprocess: false,
            displaylengthflag: false,
            initialSortingFlag: true,
            initialsortingColumn: '0',
            initialSortingorder: 'asc'
        };
    }
};


