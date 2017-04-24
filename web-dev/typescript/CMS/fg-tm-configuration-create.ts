/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
let thisObj;
class FgConfigCreate {
   
    listconfig: any = { }
    flagDrag:any = 0;
    constructor() {
        thisObj = this;
    }
    public createInit()
    {
        this.initPageTitle();
        $('.fg-tm-progress-bar').css("width","33.3%");
        $('.fg-curr-page').html(' 1 ');
        this.dirtyInit();
        this.makeSelected();
        this.continueCreate();
        this.tabClick();
        this.backBtn();
        this.saveData();
    }
    public dirtyInit() {
        FgDirtyFields.init('fg-theme-creation-form', {
            dirtyFieldSettings :{
                denoteDirtyForm  : true
            }, 
            enableDiscardChanges : false
        });
    }
    public makeSelected()
    {
        $('body').on('click', '.fg-theme-layout-thumb-wrapper-step1 li', function(){
            thisObj.tab2Inactive();
            thisObj.tab3Inactive();
            $('.fg-theme-layout-thumb-wrapper-step1 li').removeClass('selected');
            $(this).addClass('selected');
        });
        $('body').on('click', '.fg-theme-layout-thumb-wrapper-step2 li', function(){
            $('.fg-theme-layout-thumb-wrapper-step2 li').removeClass('selected');
            $(this).addClass('selected');
        });
    }
    public tab2Inactive()
    {
        $('.tab-steps li[data-tab="color"] a').attr('href', 'javascript:void(0)');
        $('.tab-steps li[data-tab="color"] a').attr('data-toggle', '');
        $('.tab-steps li[data-tab="color"]').removeClass('done');
    }
    public tab3Inactive()
    {
        $('.tab-steps li[data-tab="header"] a').attr('href', 'javascript:void(0)');
        $('.tab-steps li[data-tab="header"] a').attr('data-toggle', '');
        $('.tab-steps li[data-tab="header"]').removeClass('done');
    }
    public initPageTitle()
    {
        $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            tab: false,
            editTitle: false,
            preview: false
        });
    }
    public handleExistingimageUpload(obj,elem){
       $('body').on('click', obj, function(event) {
            event.stopImmediatePropagation();
            event.stopPropagation();
            $(elem).trigger('click');
        });
    }
    public continueCreate()
    {
        $('body').on('click', '#save_nd_continue', function() {
            $('.tm-theme-error').addClass('hide');
            $('.fg-theme-selection-error').removeClass('has-error');
            let currTab = $('.tab-steps li.active').attr('data-tab');
            let fgConfig = new FgConfigCreate();
             if (currTab === 'theme') {
                if ($.trim($('#fg-conf-title').val()) === '') {
                    $('.fg-theme-selection-error').addClass('has-error');
                    $('.tm-theme-error').removeClass('hide');
                    return false;
                } else if ($('.fg-theme-layout-thumb-wrapper-step1 li.selected').length === 0) {
                    $('.tm-theme-error').removeClass('hide');
                    return false;
                } else {
                    $('.tab-steps li.active').addClass('done');
                    $('.tab-steps li[data-tab="color"] a').attr('href', '#tab2');
                    $('.tab-steps li[data-tab="color"] a').attr('data-toggle', 'tab');
                    $('.nav-pills > .active').next('li').find('a').trigger('click');
                    fgConfig.getColorsOfTheme();
                }
            } else if (currTab === 'color') {
                if ($('.fg-theme-layout-thumb-wrapper-step2 li.selected').length === 0) {
                    $('.tm-theme-error').removeClass('hide');
                    return false;
                } else {
                    $('.tab-steps li.active').addClass('done');
                    $('.tab-steps li[data-tab="header"] a').attr('href', '#tab3');
                    $('.tab-steps li[data-tab="header"] a').attr('data-toggle', 'tab');
                    $('.nav-pills > .active').next('li').find('a').trigger('click');
                    fgConfig.getHeadersOfTheme();
                }
            }
        });
    }
    public tabClick()
    {
        $('body').on('click', '.tab-steps li a[data-toggle="tab"]', function() {
            let currTab = $(this).attr('href');
            $('#save_nd_continue').attr('data-step',currTab);
            if (currTab === '#tab1') {
                $('.fg-tm-progress-bar').css("width","33.3%");
                $('.fg-curr-page').html(' 1 ');
                $('#tm_back_btn').hide();
                $('#save_nd_continue').show();
                $('#tm_send_btn').hide();
                thisObj.tab2Inactive();
                thisObj.tab3Inactive();
                $('.tab-steps li[data-tab="theme"]').removeClass('done');
            } else if (currTab === '#tab2') {
                $('.fg-tm-progress-bar').css("width","66.6%");
                $('.fg-curr-page').html(' 2 ');
                $('#tm_back_btn').show();
                $('#save_nd_continue').show();
                $('#tm_send_btn').hide();
                thisObj.tab3Inactive();
                $('.tab-steps li[data-tab="color"]').removeClass('done');
            } else {
                $('.fg-tm-progress-bar').css("width","100%");
                $('.fg-curr-page').html(' 3 ');
                $('#tm_back_btn').show();
                $('#save_nd_continue').hide();
                $('#tm_send_btn').show();
            }
        });
    }
    public backBtn()
    {
        $('body').on('click', '#tm_back_btn', function() {
            let currStep = $('#save_nd_continue').attr('data-step');
            $('.nav-pills > .active').prev('li').find('a').trigger('click');
            if (currStep === 'color') {
                $('#tm_back_btn').hide();
            }
        });
    }
    public getColorsOfTheme()
    {
        $('.fg-theme-layout-thumb-wrapper-step2').html('');
        let selectedTheme = $('.fg-theme-layout-thumb-wrapper-step1 li.selected').attr('data-id');
        $('.fg-theme-layout-thumb-wrapper-step2').append(FGTemplate.bind('tm-config-step2', {'selectedTheme':selectedTheme,'data':themeList[selectedTheme]['color']}));
    }
    public saveData()
    {
        $('body').on('click', '#tm_send_btn', function() {
            let title = $('#fg-conf-title').val();
            let themeId = $('.fg-theme-layout-thumb-wrapper-step1 li.selected').attr('data-id');
            let colorScemeId = $('.fg-theme-layout-thumb-wrapper-step2 li.selected').attr('data-id');
            let headerStyle = $("input:radio[name=fg-theme-conf-style]:checked").val();
            let headerLogos = {};
            $('.fg-header-logos').each(function(index) {
                headerLogos[$('#header-type'+index).val()] = {};
                headerLogos[$('#header-type'+index).val()]['fileName'] = $('#cms_header_file'+index).val();
                headerLogos[$('#header-type'+index).val()]['randomName'] = $('#cms_header'+index).val();
                headerLogos[$('#header-type'+index).val()]['headerId']      = $('#cms_header_id'+index).val();
                headerLogos[$('#header-type'+index).val()]['headerChanged'] = $('#cms_header_changed'+index).val();
                headerLogos[$('#header-type'+index).val()]['headerDeleted'] = $('#cms_header_removed'+index).val();
            });
            FgXmlHttp.post(fgCreateConfSave, {'title' : title, 'themeId': themeId, 'colorScemeId': colorScemeId, 'headerStyle': headerStyle, 'headerLogos': headerLogos}, false, function() {
                FgDirtyFields.removeAllDirtyInstances();
                window.location.href = fgConfigListPath;
            });
        });
    }
    public getHeadersOfTheme()
    {
        let fgConfig = new FgConfigCreate();
        $('#fg-cms-theme-header').html('');
        let selectedTheme = $('.fg-theme-layout-thumb-wrapper-step1 li.selected').attr('data-id');
        let labelsData = themeList[selectedTheme]['themeOptions']['headerImageLabels'];
        let selThemeLabels = headerLabels[selectedTheme];
        $('#fg-cms-theme-header').append(FGTemplate.bind('fg-dropzone-underscore', {'selectedTheme':selectedTheme,'selThemeLabels':selThemeLabels,'data':themeList[selectedTheme]['themeOptions']['headerImageLabels'],'headercount':themeList[selectedTheme]['themeOptions']['noOfHeaderImages']}));
        var maxHeader = (themeList[selectedTheme]['themeOptions']['noOfHeaderImages']-1);
        var fileContainer = "#fg-files-uploaded-lists-wrapper"+maxHeader;
            if(this.flagDrag== 0){
                for(var id in labelsData){
                    var fileid = "image-uploader"+id ;
                    imageElementUploaderOptions.dropZoneElement = "#fg-files-uploaded-lists-wrapper"+id;
                    this.listconfig[id] = imageElementUploaderOptions;
                    this.listconfig[id].fileListTemplateContainer = "#fg-files-uploaded-lists-wrapper"+id;
                    let newOptions = this.listconfig[id];
                    FgFileUpload.init($("#"+fileid), newOptions);
                    var btnid = 'triggerFileUpload'+id;
                    fgConfig.handleExistingimageUpload("#"+btnid,('#'+fileid));
                    this.flagDrag = 1;
             }
        }
    }
}