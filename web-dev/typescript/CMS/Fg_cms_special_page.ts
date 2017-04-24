/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
var saveButtonType;
var thisObj;
class FgCmsSpecialPage {

    constructor() {
        thisObj = this;
        this.init();
    }
    public init(){
        let thisOpt = this;
        $(document).on("click",".fg-dev-special-page-create:not(.fg-tile-disabled)", function () {
            $('.fg-cms-special-page-wrapper').addClass('hide');
            thisOpt.showSpecialPagePopup(this);
        });
    }
    public showSpecialPagePopup(el){

        var specialPageType =  $(el).attr('data-val');
        $.post(specialPageCreatePopUpLink, {'pageType': specialPageType}, function (response) {
            FgModelbox.showPopup(response);
        });
    }

    //Function to valiidate gallery special page cretae form
    public validateGalleryForm(pageType) {
        var err = 0;
        $('.fg-cms-special-page-modal .fg-label-error').addClass('hide');
        $('.fg-cms-special-page-modal .form-group').removeClass('has-error');
        if ($.trim($('#cmsCreatePageTitle').val()) === '') {
            $('#cmsCreatePageTitle-formgroup-error').removeClass('hide');
            $('#cmsCreatePageTitle-formgroup-error').closest('.form-group').addClass('has-error');
            err = 1;
        }
        if (pageType != 'gallery'){
           var areas = $('#specialPageArea').val();
           var categories = $('#specialPageCategory').val();
           var fedIdVal = ($("#fedShared").is(':checked')) ? fedId : '';
           var subFedIdVal = ($("#subFedShared").is(':checked')) ? subFedId  : '';
           if ((fedIdVal == '' || fedIdVal == null) && (subFedIdVal == '' || subFedIdVal == null)) {
            if (areas == null || categories == null) {
              $("#failcallbackClientSide").show();
               err = 1;
            }
        }
        
        }
        if ($('#cmsCreateGalleryRoles').selectpicker('val') == null) {
            $('#cmsCreateGalleryRoles-formgroup-error').removeClass('hide');
            $('#cmsCreateGalleryRoles-formgroup-error').closest('.form-group').addClass('has-error');
            err = 1;
        }

        if (err == 0) {
            return true;
        }
        return false;
    }

    //To show preview
    public renderPreview(navurl=''){
        renderPageType=  $('#hidPageType').val();
        pageID = $('#hidPageId').val();
        navurl = $('#hidNavId').val(); 
        $('.fg-cms-create-page-wrapper').addClass('hide');
        $('.fg-cms-special-page-wrapper').addClass('hide');
        $('.fg-cms-page-list').addClass('hide');
        $('.fg-cms-create-page-preview-wrapper').removeClass('hide');
        //Select preview tab
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsTabPreview').addClass('active');
        if(navurl!=''){
            if (hasSidebar) {
                FgCmsPageList.callPagePreview(pageID, '');
               
            }else{
              if(renderPageType == 'page' || currPageType=== 'page')  
              FgCmsPageList.callEditPreview(pageID); 
               else
             FgCmsPageList.callPagePreview(pageID,'')
            } 
        }else{
            if(renderPageType == 'page' || currPageType=== 'page')
               FgCmsPageList.callEditPreview(pageID);
            else
             FgCmsPageList.callPagePreview(pageID,'');
              
        }        
        
        //update title in pageTitle bar after updating title and click on preview     
        var pageTitle = $('#sidemenu_bar a[data-pageid="' + pageID + '"]').attr('data-pagetitle');
        if (!_.isEmpty(pageTitle)) {
            $('.page-title > .page-title-text').text(pageTitle);
        }
        
        this.redrawPagetitleBar('');
    }

    //To show Content
    public renderContent(){
         pageID = $('#hidPageId').val();
        renderPageType=  $('#hidPageType').val();
      if(renderPageType == 'page'){ 
            window.location = pageEditPath.replace('***dummy***', pageID);
        }else{
            $('.fg-cms-create-page-wrapper').addClass('hide'); 
            $('.fg-cms-special-page-wrapper').addClass('hide');
            $('.fg-cms-create-page-preview-wrapper').addClass('hide');
            $('.fg-cms-page-list').addClass('hide');
            $('.fg-cms-special-page-wrapper').removeClass('hide');

            //Select Content tab
            $('#paneltab li').removeClass('active');
            $('#fg_tab_cmsTabContent').addClass('active');
            this.redrawPagetitleBar('content'); 
        }       
    }
    //Save callback function from popup
    public gallerySaveCallback(res){
        Layout.fixContentHeight();
        thisObj.renderSpecialPage(res.pageId, 'gallery', 'content');
        $('#fg-popup').modal('hide');
        FgCmsPageList.updateSidebarElements();
    }
    
    public renderSpecialPage(pageId, pageType, tab,navurl=''){
         
    
        let _this = this;
        var renderCallback = (tab === 'content') ? function(){ _this.renderContent(); _this.setLangSwitchDefault(); Layout.fixContentHeight();} : function(){ _this.renderPreview(); Layout.fixContentHeight();} ;
        if(pageType ==='gallery'){
            _this.showGalleySpecialPage(pageId, renderCallback);
        }else if(pageType === 'page'){
             _this.renderPreview(navurl);
        }else{
            _this.showArticleAndCalendarSpecialPage(pageId, pageType, renderCallback);
    }

    public showGalleySpecialPage(pageId, callback){
        let _this = this;
        //Get gallery page details
        $.get(galleryPageDetails, {'pageId':pageId},function( data ) {
                _this.renderGalleryFormContent(data);
                _this.setGalleryDetails(data);
                FgUtility.handleSelectPicker();
                _this.updatePageTitle(data);
                callback();
        });
        $('#hiddenPageId').val(pageId);
        _this.handleGalleryUpdate();
        $('#hidPageId').val(pageId);
        $('#hidPageType').val('gallery');
    }

    public renderGalleryFormContent(data){
        $('#fg_tab_cmsTabContent span.fg-dev-tab-text').text(CmsTrans.galleries);
        var galleryHtml =FGTemplate.bind('gallerySpecialPageTemplate', { data: data});
        $('.fg-cms-special-page-wrapper').html(galleryHtml);
        $('.selectpicker').selectpicker();
        $('select.selectpicker').selectpicker({noneSelectedText: CmsTrans.setDefault });
        $('select.selectpicker').selectpicker('render');
    }

    public redrawPagetitleBar(type) {
        var opt;
        if (type == 'content') {
            FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({ tab: true, editTitleInline: true, languageSwitch: true, tabType: 'client',isCalSetmoreTab:false });
        }
        else {
            FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({ tab: true, title: true, tabType: 'client',isCalSetmoreTab:false });
        }
        //setTimeout(function () { FgPageTitlebar.setMoreTab(); }, 1250);

    }

    public resetPagetitleBar(){
        if(hasSidebar){
                  FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({
                                actionMenu: true,
                                title: true,
                                search: true
                            });
        } else {
            FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({title:true, search: true});
        }
            
    }

    public setGalleryDetails(d){
        var defaultTitle = d.pageTitles.default;

        $.each(clubLanguages,function(i,v){
            $('#pageTitle-'+v).attr('placeholder', defaultTitle);
                $('#pageTitle-'+v).val(d.pageTitles[v]);
        });
        $('#pageTitle-'+defaultLang).val(defaultTitle);
        if(d.page['isAllGalleries']){
            $('.selectpicker').selectpicker('val', 'ALL_GALLERIES');
        }else{
            $('.selectpicker').selectpicker('val', d.roles);
        }
        $('.selectpicker').selectpicker('refresh');
        FgLanguageSwitch.checkMissingTranslation(defaultLang);
        FgDirtyFields.init(specialPageFormSelector, {saveChangeSelector: "#save_changes, #save_bac", setInitialHtml:false, discardChangesCallback : this.discardChangesCallback});
    }

    public discardChangesCallback() {
        $('.selectpicker').selectpicker('refresh');
        FgUtility.handleSelectPicker();
        $('#fedShared, #subFedShared').uniform('destroy');
        FgFormTools.handleUniform();
    }

    public galleryUpdateCallback(d){
        Layout.fixContentHeight();
        if (saveButtonType == 'save_bac') {
            thisObj.showAllPages();
        } else {
            FgLanguageSwitch.checkMissingTranslation(defaultLang);
            thisObj.handlePageTitlePlaceholder(d.page);
            
            FgDirtyFields.init(specialPageFormSelector, { saveChangeSelector: "#save_changes, #save_bac", setInitialHtml: false, discardChangesCallback: function(){  $('.selectpicker').selectpicker('refresh');
            FgUtility.handleSelectPicker();   } });
        }
        FgCmsPageList.updateSidebarElements();
    }

    public setLangSwitchDefault(){
        $.each(clubLanguages,function(i,v){
            $('#pageTitle-'+v).addClass('hide');
            $('.fg-action-menu-wrapper .fg-lang-tab .btlang[lang="'+v+'"]').removeClass('active');
            if(v == defaultLang){
               $('#pageTitle-'+v).removeClass('hide');
                $('.fg-action-menu-wrapper .fg-lang-tab .btlang[lang="'+v+'"]').addClass('active');
            }
        });
    }

    public handlePageTitlePlaceholder(data){
        $('input.pageTitles').attr('placeholder', data.title);
    }

    public validateGalleryEditForm(){
        var err = 0;
        $('#pageTitle-' + defaultLang).closest('div').removeClass('has-error');
        if($('#pageTitle-'+defaultLang).val() == ''){
            err  = 1;
            $('#pageTitle-' + defaultLang).closest('div').addClass('has-error');
        }
        $('#cmsGalleris-formgroup-error').addClass('hide');
        $('#cmsGalleris-formgroup-error').closest('.form-group').removeClass('has-error');
        if ($('#cmsGalleris').selectpicker('val') == null) {
            $('#cmsGalleris-formgroup-error').removeClass('hide');
            $('#cmsGalleris-formgroup-error').closest('.form-group').addClass('has-error');
            err = 1;
        }
        if (err == 0) {
            return true;
        }
        return false;
    }

    public showAllPages(){
        FgCmsPage.hideAllWrappersDivs();
        if(hasSidebar){
           FgSidebar.handleSidebarClick('li_PAGES_all_pages');
        } else { // Only for page admin
            FgCmsPage.reInitPageList('all_pages');
        }
        $('.fg-cms-page-list').removeClass('hide'); 
        this.resetPagetitleBar();
    }

    public handleGalleryUpdate() {

        var _this = this;
        $(document).off('click', '#save_changes, #save_bac');
        $(document).on('click', '#save_changes, #save_bac', function() {
            var isValid = _this.validateGalleryEditForm();
            var data = {};
            if (isValid) {
                var title = {};
                $.each($('input.pageTitles'), function(i, obj) {
                    title[$(obj).attr('data-lang')] = $(obj).val();
                });
                data['titleArray'] = title;
                data['pageId'] = $('#hiddenPageId').val();
                data['galleryRoleArray'] = $('#cmsGalleris').selectpicker('val');
                saveButtonType = $(this).attr('id');
                FgXmlHttp.post(saveGalleryPageEdit, data, false, _this.galleryUpdateCallback);
            }

        });
        $(document).off('click', '.fg-backbtn-btm');
        $(document).off('click', '.bckid');
        $(document).on('click', '.fg-backbtn-btm', function() {
            _this.showAllPages();
        });

    }

    //Save callback function from popup
    public specialPageSaveCallback(res){
        Layout.fixContentHeight();
        thisObj.renderSpecialPage(res.pageId, res.pageType,'');
        $('#fg-popup').modal('hide');
        FgCmsPageList.updateSidebarElements();
    }

    public renderSpecialPageFormContent(data){
        let pageTitle = (data.pageType == 'article') ? CmsTrans.article : CmsTrans.calendar ;
        $('#fg_tab_cmsTabContent span.fg-dev-tab-text').text(pageTitle);
        var pageHtml = FGTemplate.bind('articleAndSpecialPageTemplate', { data: data});
        $('.fg-cms-special-page-wrapper').html(pageHtml);
        $('.selectpicker').selectpicker();
        $('select.selectpicker').selectpicker({noneSelectedText: CmsTrans.setDefault });
        $('select.selectpicker').selectpicker('render');
    }


     public renderArticleAndCalendarSpecialPage(pageId, pageType, callback){

        this.showArticleAndCalendarSpecialPage(pageId, pageType, callback);
        //Show preview wrapper and hide others
        $('.fg-cms-create-page-preview-wrapper').removeClass('hide');
        $('.fg-cms-create-page-wrapper').addClass('hide');
        $('.fg-cms-special-page-wrapper').addClass('hide');
        $('.fg-cms-page-list').addClass('hide');
        this.renderPreview();
    }

    public showArticleAndCalendarSpecialPage(pageId, pageType, callback){
        let _this = this;
        if(typeof articleAndCalendarDetails =='undefined'){
            return;
        }
        /** Get article or calendar special page details */
        $.get(articleAndCalendarDetails, {'pageId':pageId, 'pageType':pageType},function( data ) {
                _this.renderSpecialPageFormContent(data);
                _this.setArticleAndGallerySpecialPage(data);
                FgUtility.handleSelectPicker();
                FgFormTools.handleUniform();
                _this.updatePageTitle(data);
                callback();
        });
         $('#currentPageId').val(pageId);
         $('#currentPageType').val(pageType);
        _this.handleArticleAndCalendarUpdate();
        $('#hidPageId').val(pageId);
        $('#hidPageType').val(pageType);
        
    }

    public updatePageTitle(data) { 
        if (!_.isEmpty(data.pageTitles.default)) {
            //show title bar when it is hided
            $('.fg-action-menu-wrapper').removeClass('hide');        
            $('.page-title > .page-title-text').text(data.pageTitles.default);   
            CmsSpecialPage.redrawPagetitleBar();
        }
    }

    public setArticleAndGallerySpecialPage(data){

        var defaultTitle = data.pageTitles.default;
        
        $.each(clubLanguages, function(i, v) {
            $('#pageTitle-' + v).attr('placeholder', defaultTitle);
                $('#pageTitle-' + v).val(data.pageTitles[v]);
        });
        $('#pageTitle-' + defaultLang).val(defaultTitle);
        
        var selectedAreas = [];
        if (data.existingDatas['isAllArea']) {
            selectedAreas = 'ALL_AREAS';
        } else if (data.existingDatas['areas']) {
            selectedAreas = data.existingDatas['areaIds'];

        }
        if (data.existingDatas['areaClub']) {
            selectedAreas.push(data.clubId);
        }
        $('#specialPageAreas').selectpicker('val', selectedAreas);

        if (data.existingDatas['isAllCategory']) {
            $('#specialPageCategories').selectpicker('val', 'ALL_CATS');
        } else if (data.existingDatas['categories']) {
            $('#specialPageCategories').selectpicker('val', data.existingDatas['catIds']);
        }
        $('.selectpicker').selectpicker('refresh');


        FgLanguageSwitch.checkMissingTranslation(defaultLang);
        FgDirtyFields.init(specialPageFormSelector, { saveChangeSelector: "#save_changes, #save_bac",  setInitialHtml: false, discardChangesCallback: this.discardChangesCallback });

    }


    public handleArticleAndCalendarUpdate(){

        var _this = this;
        $(document).off('click', '#save_changes, #save_bac');
        $(document).on('click', '#save_changes, #save_bac', function() {
            var isValid = _this.validateArticleAndCalendarEdit();
            var data = {};
            if (isValid) {
                var title = {};
                $.each($('input.pageTitles'), function(i, obj) {
                    title[$(obj).attr('data-lang')] = $(obj).val();
                });
                data['title'] = title;
                data['pageId'] = $('#currentPageId').val();
                data['type'] = $('#currentPageType').val();
                data['areas'] = $('[name=specialPageAreas]').val();
                data['categories'] = $('[name=specialPageCategories]').val();
                data['fedIdVal'] = ($("#fedShared").is(':checked')) ? fedId : '';
                data['subFedIdVal'] = ($("#subFedShared").is(':checked')) ? subFedId  : '';
                $("#fedShared").attr('data-id',   data['fedIdVal']);
                $("#subFedShared").attr('data-id',  data['subFedIdVal']);
                data['isAllArea'] = ( data['areas'] == 'ALL_AREAS') ?  1 : '';
                data['isAllCat'] = (data['categories'] == 'ALL_CATS') ? 1 : '';
                saveButtonType = $(this).attr('id');
                FgXmlHttp.post(saveArticleAndGalleryPageEditSavePath, data, false, _this.articleAndCalendarUpdateCallback);
            }

        });
        $(document).off('click', '.fg-backbtn-btm');
        $(document).off('click', '.bckid');
        $(document).on('click', '.fg-backbtn-btm', function() {
            _this.showAllPages();
        });
    }

    public articleAndCalendarUpdateCallback(d) {
        Layout.fixContentHeight();
        if (saveButtonType == 'save_bac') {
            thisObj.showAllPages();
        } else {
            FgLanguageSwitch.checkMissingTranslation(defaultLang);
            thisObj.setLangSwitchDefault();
            $("#failcallbackClientSideError").remove();
            FgDirtyFields.init(specialPageFormSelector, {
                saveChangeSelector: "#save_changes, #save_bac", setInitialHtml: false, discardChangesCallback: function() {
                    $('.selectpicker').selectpicker('refresh'); 
                    FgUtility.handleSelectPicker();
                    var fedCheckVal = $("#fedShared").attr('data-id');
                    var subFedCheckVal = $("#subFedShared").attr('data-id');
                    (fedCheckVal != '') ? $("#fedShared").parent('span').addClass('checked') :  $("#fedShared").parent('span').removeClass('checked'); 
                    (subFedCheckVal != '') ? $("#subFedShared").parent('span').addClass('checked') :  $("#subFedShared").parent('span').removeClass('checked');  
                    $('#fedShared, #subFedShared').uniform('destroy');
                    FgFormTools.handleUniform();
                }
            });
        }
        FgCmsPageList.updateSidebarElements();
        thisObj.handlePageTitlePlaceholder(d.page);
    }

    public discardAfterUpdate(){

        $('.selectpicker').selectpicker('refresh');
        FgUtility.handleSelectPicker();
    }


    public validateArticleAndCalendarEdit() {
        var err = 0;
        $('#pageTitle-' + defaultLang).closest('div').removeClass('has-error');
        if ($.trim($('#pageTitle-' + defaultLang).val()) === '') {
            err = 1;
            $('#pageTitle-' + defaultLang).closest('div').addClass('has-error');
        }
        var areas = $('#specialPageAreas').val();
        var categories = $('#specialPageCategories').val();
        var fedIdVal = ($("#fedShared").is(':checked')) ? fedId : '';
        var subFedIdVal = ($("#subFedShared").is(':checked')) ? subFedId : '';
        if ((fedIdVal == '' || fedIdVal == null) && (subFedIdVal == '' || subFedIdVal == null)) {
            if (areas == null || categories == null) {
                if ($('#failcallbackClientSideError').is(":visible") == true) {
                $('#failcallbackClientSideError').remove();
            }
            $('<div class="alert alert-danger" id="failcallbackClientSideError">' +
                '<button class="close" data-dismiss="alert"></button>' +
                '<span data-error>'+CmsTrans.formError+'</span>' +
                '</div>').insertAfter($('.fg-cms-elements-head-edit-wrapper')).insertBefore($('.articleOrCalendarPageForm'));
                  err = 1;
            }
        }
        if (err == 0) {
            return true;
        }
        return false;
    }

}