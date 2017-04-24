var thisObj;
var FgConfigCreate = (function () {
    function FgConfigCreate() {
        this.listconfig = {};
        this.flagDrag = 0;
        thisObj = this;
    }
    FgConfigCreate.prototype.createInit = function () {
        this.initPageTitle();
        $('.fg-tm-progress-bar').css("width", "33.3%");
        $('.fg-curr-page').html(' 1 ');
        this.dirtyInit();
        this.makeSelected();
        this.continueCreate();
        this.tabClick();
        this.backBtn();
        this.saveData();
    };
    FgConfigCreate.prototype.dirtyInit = function () {
        FgDirtyFields.init('fg-theme-creation-form', {
            dirtyFieldSettings: {
                denoteDirtyForm: true
            },
            enableDiscardChanges: false
        });
    };
    FgConfigCreate.prototype.makeSelected = function () {
        $('body').on('click', '.fg-theme-layout-thumb-wrapper-step1 li', function () {
            thisObj.tab2Inactive();
            thisObj.tab3Inactive();
            $('.fg-theme-layout-thumb-wrapper-step1 li').removeClass('selected');
            $(this).addClass('selected');
        });
        $('body').on('click', '.fg-theme-layout-thumb-wrapper-step2 li', function () {
            $('.fg-theme-layout-thumb-wrapper-step2 li').removeClass('selected');
            $(this).addClass('selected');
        });
    };
    FgConfigCreate.prototype.tab2Inactive = function () {
        $('.tab-steps li[data-tab="color"] a').attr('href', 'javascript:void(0)');
        $('.tab-steps li[data-tab="color"] a').attr('data-toggle', '');
        $('.tab-steps li[data-tab="color"]').removeClass('done');
    };
    FgConfigCreate.prototype.tab3Inactive = function () {
        $('.tab-steps li[data-tab="header"] a').attr('href', 'javascript:void(0)');
        $('.tab-steps li[data-tab="header"] a').attr('data-toggle', '');
        $('.tab-steps li[data-tab="header"]').removeClass('done');
    };
    FgConfigCreate.prototype.initPageTitle = function () {
        $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            tab: false,
            editTitle: false,
            preview: false
        });
    };
    FgConfigCreate.prototype.handleExistingimageUpload = function (obj, elem) {
        $('body').on('click', obj, function (event) {
            event.stopImmediatePropagation();
            event.stopPropagation();
            $(elem).trigger('click');
        });
    };
    FgConfigCreate.prototype.continueCreate = function () {
        $('body').on('click', '#save_nd_continue', function () {
            $('.tm-theme-error').addClass('hide');
            $('.fg-theme-selection-error').removeClass('has-error');
            var currTab = $('.tab-steps li.active').attr('data-tab');
            var fgConfig = new FgConfigCreate();
            if (currTab === 'theme') {
                if ($.trim($('#fg-conf-title').val()) === '') {
                    $('.fg-theme-selection-error').addClass('has-error');
                    $('.tm-theme-error').removeClass('hide');
                    return false;
                }
                else if ($('.fg-theme-layout-thumb-wrapper-step1 li.selected').length === 0) {
                    $('.tm-theme-error').removeClass('hide');
                    return false;
                }
                else {
                    $('.tab-steps li.active').addClass('done');
                    $('.tab-steps li[data-tab="color"] a').attr('href', '#tab2');
                    $('.tab-steps li[data-tab="color"] a').attr('data-toggle', 'tab');
                    $('.nav-pills > .active').next('li').find('a').trigger('click');
                    fgConfig.getColorsOfTheme();
                }
            }
            else if (currTab === 'color') {
                if ($('.fg-theme-layout-thumb-wrapper-step2 li.selected').length === 0) {
                    $('.tm-theme-error').removeClass('hide');
                    return false;
                }
                else {
                    $('.tab-steps li.active').addClass('done');
                    $('.tab-steps li[data-tab="header"] a').attr('href', '#tab3');
                    $('.tab-steps li[data-tab="header"] a').attr('data-toggle', 'tab');
                    $('.nav-pills > .active').next('li').find('a').trigger('click');
                    fgConfig.getHeadersOfTheme();
                }
            }
        });
    };
    FgConfigCreate.prototype.tabClick = function () {
        $('body').on('click', '.tab-steps li a[data-toggle="tab"]', function () {
            var currTab = $(this).attr('href');
            $('#save_nd_continue').attr('data-step', currTab);
            if (currTab === '#tab1') {
                $('.fg-tm-progress-bar').css("width", "33.3%");
                $('.fg-curr-page').html(' 1 ');
                $('#tm_back_btn').hide();
                $('#save_nd_continue').show();
                $('#tm_send_btn').hide();
                thisObj.tab2Inactive();
                thisObj.tab3Inactive();
                $('.tab-steps li[data-tab="theme"]').removeClass('done');
            }
            else if (currTab === '#tab2') {
                $('.fg-tm-progress-bar').css("width", "66.6%");
                $('.fg-curr-page').html(' 2 ');
                $('#tm_back_btn').show();
                $('#save_nd_continue').show();
                $('#tm_send_btn').hide();
                thisObj.tab3Inactive();
                $('.tab-steps li[data-tab="color"]').removeClass('done');
            }
            else {
                $('.fg-tm-progress-bar').css("width", "100%");
                $('.fg-curr-page').html(' 3 ');
                $('#tm_back_btn').show();
                $('#save_nd_continue').hide();
                $('#tm_send_btn').show();
            }
        });
    };
    FgConfigCreate.prototype.backBtn = function () {
        $('body').on('click', '#tm_back_btn', function () {
            var currStep = $('#save_nd_continue').attr('data-step');
            $('.nav-pills > .active').prev('li').find('a').trigger('click');
            if (currStep === 'color') {
                $('#tm_back_btn').hide();
            }
        });
    };
    FgConfigCreate.prototype.getColorsOfTheme = function () {
        $('.fg-theme-layout-thumb-wrapper-step2').html('');
        var selectedTheme = $('.fg-theme-layout-thumb-wrapper-step1 li.selected').attr('data-id');
        $('.fg-theme-layout-thumb-wrapper-step2').append(FGTemplate.bind('tm-config-step2', { 'selectedTheme': selectedTheme, 'data': themeList[selectedTheme]['color'] }));
    };
    FgConfigCreate.prototype.saveData = function () {
        $('body').on('click', '#tm_send_btn', function () {
             $('#tm_send_btn').addClass("disabled");
             $('#tm_back_btn').addClass("disabled");
            var title = $('#fg-conf-title').val();
            var themeId = $('.fg-theme-layout-thumb-wrapper-step1 li.selected').attr('data-id');
            var colorScemeId = $('.fg-theme-layout-thumb-wrapper-step2 li.selected').attr('data-id');
            var headerStyle = 0;
            var logoStyle = 'left';
            var theme2head = 'fullwidth';
            if(themeId==1){
                headerStyle = $("input:radio[name=fg-theme-conf-style]:checked").val(); 
            }else if(themeId==2){
                logoStyle = $("input:radio[name=fg-theme-logo-style]:checked").val();
                theme2head = $("input:radio[name=fg-theme-conf-style]:checked").val();
            }
            
            var headerLogos = {};
            $('.fg-header-logos').each(function (index) {
                headerLogos[$('#header-type' + index).val()] = {};
                headerLogos[$('#header-type' + index).val()]['fileName'] = $('#cms_header_file' + index).val();
                headerLogos[$('#header-type' + index).val()]['randomName'] = $('#cms_header' + index).val();
                headerLogos[$('#header-type' + index).val()]['headerId'] = $('#cms_header_id' + index).val();
                headerLogos[$('#header-type' + index).val()]['headerChanged'] = $('#cms_header_changed' + index).val();
                headerLogos[$('#header-type' + index).val()]['headerDeleted'] = $('#cms_header_removed' + index).val();
            });
            FgXmlHttp.post(fgCreateConfSave, { 'title': title, 'themeId': themeId, 'colorScemeId': colorScemeId,'theme2head':theme2head,'logoStyle':logoStyle, 'headerStyle': headerStyle, 'headerLogos': headerLogos }, false, function () {
                FgDirtyFields.removeAllDirtyInstances();
                window.location.href = fgConfigListPath;
            });
        });
    };
    FgConfigCreate.prototype.getHeadersOfTheme = function () {
        var fgConfig = new FgConfigCreate();
        $('#fg-cms-theme-header').html('');
        var selectedTheme = $('.fg-theme-layout-thumb-wrapper-step1 li.selected').attr('data-id');
        var labelsData = themeList[selectedTheme]['themeOptions']['headerImageLabels'];
        var selThemeLabels = headerLabels[selectedTheme];
        $('#fg-cms-theme-header').append(FGTemplate.bind(headerTemplate, { 'selectedTheme': selectedTheme, 'selThemeLabels': selThemeLabels, 'data': themeList[selectedTheme]['themeOptions']['headerImageLabels'], 'headercount': themeList[selectedTheme]['themeOptions']['noOfHeaderImages'] }));
        FgFormTools.handleUniform();
        var maxHeader = (themeList[selectedTheme]['themeOptions']['noOfHeaderImages'] - 1);
        var fileContainer = "#fg-files-uploaded-lists-wrapper" + maxHeader;
        if (this.flagDrag == 0) {
            for (var id in labelsData) {
                var fileid = "image-uploader" + id;
                imageElementUploaderOptions.dropZoneElement = "#fg-files-uploaded-lists-wrapper" + id;
                this.listconfig[id] = imageElementUploaderOptions;
                this.listconfig[id].fileListTemplateContainer = "#fg-files-uploaded-lists-wrapper" + id;
                var newOptions = this.listconfig[id];
                FgFileUpload.init($("#" + fileid), newOptions);
                var btnid = 'triggerFileUpload' + id;
                fgConfig.handleExistingimageUpload("#" + btnid, ('#' + fileid));
                this.flagDrag = 1;
            }
        }
    };
    return FgConfigCreate;
}());
//# sourceMappingURL=Fg-tm-configuration-create.js.map