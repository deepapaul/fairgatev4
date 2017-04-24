/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgConfigUpdateHeader {
    flagDrag:any = 0;
    listconfig: any = { }
    public createInit()
    {
        this.pageTitleInit();
        this.getHeadersOfTheme();
        this.activeHeaderTab();
        this.saveData();
        this.savePageTitle();
        this.changePageTitle();
        
      }
    public pageTitleInit() {
        $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            editTitleInline: false,
            tab: true,
            tabType: 'server',
            languageSwitch: true,
            editTitle: true
        });
        
    }
    public activeHeaderTab(){
        $("#paneltab").find(".active").removeClass('active');
        $("#fg_tab_header").addClass('active');
    }
    public initDirty() {
        FgDirtyFields.init('frmHeaders', {saveChangeSelector: "#save_bac, #cancelsettings",enableDiscardChanges : true,setInitialHtml:false,discardChangesCallback:configCreate.discardChangesCallback}); 
    }
    public discardChangesCallback(){
         configCreate.resetImages();
    }
    public successCallback(){
         configCreate.initDirty();
         
    }
    public resetImages( ){
        
        
        if(headerScrolling == '1'){
            $('#radios-0').attr('checked', false);
            $('#radios-1').prop('checked', true);
            $('#radios-1').parent().addClass('checked');
            $('#radios-0').parent().removeClass('checked');
        }else{
            $('#radios-0').prop('checked', true);
            $('#radios-1').prop('checked', false);
            $('#radios-0').parent().addClass('checked');
            $('#radios-1').parent().removeClass('checked');
        }
        
        configCreate.displayDropzone(1);
        this.flagDrag = 0;
        
    }
    public handleExistingimageUpload(obj,elem){
       $('body').on('click', obj, function(event) {
            event.stopImmediatePropagation();
            event.stopPropagation();
            $(elem).trigger('click');
        });
    }
    public changePageTitle() {
        $('body').on('click', '.fg-action-editTitle', function() {
            $('.fg-cms-title-change-form').removeClass('has-error');
            $('span.required').remove();
            let titleText = $('.page-title  .page-title-text').html();
            $('#pageTitleChange').val(titleText);
            $('#config-title-change-modal').modal('show');
        });
    }
    public savePageTitle() {
        $(document).off('click', '#savePopup');
        $(document).on('click', '#savePopup', function() {
            let pageTitle = $('#pageTitleChange').val();
            if (pageTitle.trim() === '') {
                $('.fg-cms-title-change-form').addClass('has-error');
                $('.fg-error-add-required').append('<span class="required">'+transFields.required+'</span>');
                return false;
            } else {
                FgXmlHttp.post(changePageTitlePath, {'config': configId, 'title': pageTitle}, false, configCreate.successCallback, function(response) {
                    $('#config-title-change-modal').modal('hide');
                    $('.page-title  .page-title-text').html('');
                    $('.page-title  .page-title-text').html(pageTitle);
                });
            }
        });
    }
   
    public previewHeaderImage(id,savedConfig){
        if (id != '') {

             var path = '/uploads/' + club_id + '/admin/website_header/';
             var filenamepath = path +savedConfig[id]. fileName;
            
        }
        var rowId = 'header-'+savedConfig[id].id;
        var datatoTemplate = { name: rowId, id : rowId, };
        ImagesUploader.showExistImagePreviewForLogo(rowId,id,datatoTemplate,filenamepath);
    }
    public saveData()
    {
        
        $('#save_bac').off();
        $('#save_bac').attr("disabled",true);
        $('#reset_changes').attr("disabled",true);
         
        $('body').on('click', '#save_bac', function(event) {
            event.stopImmediatePropagation();
            event.stopPropagation();
            $('.fg-del-close').attr("disabled",true);
            let headerStyle = $("input:radio[name=fg-theme-conf-style]:checked").val();
            let headerLogos = {};
                $('.fg-header-logos').each(function(index) {
                    headerLogos[$('#header-type'+index).val()] = {};
                    headerLogos[$('#header-type'+index).val()]['fileName']      = $('#cms_header_file'+index).val();
                    headerLogos[$('#header-type'+index).val()]['randomName']    = $('#cms_header'+index).val();
                    headerLogos[$('#header-type'+index).val()]['headerId']      = $('#cms_header_id'+index).val();
                    headerLogos[$('#header-type'+index).val()]['headerChanged'] = $('#cms_header_changed'+index).val();
                    headerLogos[$('#header-type'+index).val()]['headerDeleted'] = $('#cms_header_removed'+index).val();
                });
                FgXmlHttp.post(fgHeaderSave, { 'configId': selectedTheme,  'headerStyle': headerStyle, 'headerLogos': headerLogos}, false, '' function(response) {
                if (response.status === 'SUCCESS') {
                    
                    configCreate.displayImageAfterSave(response.viewParams);
                    
                }
            });
            configCreate.initDirty();
            $('.fg-del-close').attr("disabled",false);
        });
        
    }
     public displayImageAfterSave(viewParams){
        
        
        let labelsData = themeList['headerImageLabels'];
        savedConfig = viewParams['savedConfig'];
        headerScrolling = (viewParams['configDetails'].headerScrolling==1?'1':'0');
        
        for(var id in labelsData){
                $("#cms_header_id"+id).val('');
                $("#cms_header_file"+id).val('');
                $("#cms_header_changed"+id).val(0);
                $("#cms_header"+id).val('');
                $("#cms_header_removed"+id).val('');
            if(savedConfig.length > 0 ){
               
                if(savedConfig[id].typeid==id&&savedConfig[id].hasOwnProperty('fileName')){
                     $("#cms_header_id"+id).val(savedConfig[id].id);
                     $("#cms_header_file"+id).val(savedConfig[id].fileName);
                     $("#cms_header"+id).val(savedConfig[id].fileName);
                }
           } 
        } 
        
    }
    public getHeadersOfTheme()
    {
        let fgConfig = new FgConfigUpdateHeader();
        let selThemeLabels = headerLabels;
        $('#fg-cms-theme-header').append(FGTemplate.bind('fg-dropzone-underscore', {'selectedTheme':selectedTheme,'data':themeList['headerImageLabels'],'selThemeLabels':selThemeLabels,'headercount':themeList['noOfHeaderImages']}));
        fgConfig.displayDropzone();
    }
    
    public displayDropzone(callback=0){
         let fgConfig = new FgConfigUpdateHeader();
        var maxHeader = (themeList['noOfHeaderImages']-1);
         var fileContainer = "#fg-files-uploaded-lists-wrapper"+maxHeader;
         let labelsData = themeList['headerImageLabels'];
         
            if(this.flagDrag== 0){
                 for(var id in labelsData){
                    var fileid = "image-uploader"+id ;
                    imageElementUploaderOptions.dropZoneElement = "#fg-files-uploaded-lists-wrapper"+id;
                    this.listconfig[id] = imageElementUploaderOptions;
                    this.listconfig[id].fileListTemplateContainer = "#fg-files-uploaded-lists-wrapper"+id;
                    let newOptions = this.listconfig[id];
                    FgFileUpload.init($("#"+fileid), newOptions);
                    var btnid = 'triggerFileUpload'+id;
                    var savedId = '';
                    
                    if(savedConfig.length > 0 ){
                        
                       if(savedConfig[id].typeid==id&&savedConfig[id].hasOwnProperty('fileName')){
                            $("#cms_header_id"+id).val(savedConfig[id].id);
                            $("#cms_header_file"+id).val(savedConfig[id].fileName);
                            $("#cms_header"+id).val(savedConfig[id].fileName);
                            $("#header-type"+id).val(savedConfig[id].headerLabel);
                            fgConfig.previewHeaderImage(id,savedConfig);
                        }else if(callback==1){
                         var removeDivid= $(imageElementUploaderOptions.dropZoneElement).find('.fg-dropzone-preview').parent().attr("id"); 
                           $('#'+removeDivid).remove();
                        }
                         
                        
                     }else{
                        if(callback==1){
                            var removeDivid= $(imageElementUploaderOptions.dropZoneElement).find('.fg-dropzone-preview').parent().attr("id"); 
                            if( $('#'+removeDivid).length>0)
                                $('#'+removeDivid).remove();
                           } 
                     }
                    fgConfig.handleExistingimageUpload("#"+btnid,('#'+fileid));
                    this.flagDrag = 1;
             }
        }
    }
}

