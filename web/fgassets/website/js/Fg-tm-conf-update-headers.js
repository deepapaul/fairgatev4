var FgConfigUpdateHeader = (function () {
    function FgConfigUpdateHeader() {
        this.flagDrag = 0;
        this.listconfig = {};
    }
    FgConfigUpdateHeader.prototype.createInit = function () {
        this.pageTitleInit();
        this.getHeadersOfTheme();
        this.activeHeaderTab();
        this.saveData();
        this.savePageTitle();
        this.changePageTitle();
    };
    FgConfigUpdateHeader.prototype.pageTitleInit = function () {
        $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            editTitleInline: false,
            tab: true,
            tabType: 'server',
            languageSwitch: true,
            editTitle: true
        });
    };
    FgConfigUpdateHeader.prototype.activeHeaderTab = function () {
        $("#paneltab").find(".active").removeClass('active');
        $("#fg_tab_header").addClass('active');
    };
    FgConfigUpdateHeader.prototype.initDirty = function () {
        FgDirtyFields.init('frmHeaders', { saveChangeSelector: "#save_bac, #cancelsettings", enableDiscardChanges: true, setInitialHtml: false, discardChangesCallback: configCreate.discardChangesCallback });
    };
    FgConfigUpdateHeader.prototype.discardChangesCallback = function () {
        configCreate.resetImages();
    };
    FgConfigUpdateHeader.prototype.successCallback = function () {
        configCreate.initDirty();
    };
    FgConfigUpdateHeader.prototype.resetImages = function () {
        if (headerScrolling == '1') {
            $('#radios-0').attr('checked', false);
            $('#radios-1').prop('checked', true);
            $('#radios-1').parent().addClass('checked');
            $('#radios-0').parent().removeClass('checked');
        }
        else {
            $('#radios-0').prop('checked', true);
            $('#radios-1').prop('checked', false);
            $('#radios-0').parent().addClass('checked');
            $('#radios-1').parent().removeClass('checked');
        }
        configCreate.displayDropzone(1);
        this.flagDrag = 0;
    };
    FgConfigUpdateHeader.prototype.handleExistingimageUpload = function (obj, elem) {
        $('body').on('click', obj, function (event) {
            event.stopImmediatePropagation();
            event.stopPropagation();
            $(elem).trigger('click');
        });
    };
    FgConfigUpdateHeader.prototype.changePageTitle = function () {
        $('body').on('click', '.fg-action-editTitle', function () {
            $('.fg-cms-title-change-form').removeClass('has-error');
            $('span.required').remove();
            var titleText = $('.page-title  .page-title-text').html();
            $('#pageTitleChange').val(titleText);
            $('#config-title-change-modal').modal('show');
        });
    };
    FgConfigUpdateHeader.prototype.savePageTitle = function () {
        $(document).off('click', '#savePopup');
        $(document).on('click', '#savePopup', function () {
            var pageTitle = $('#pageTitleChange').val();
            if (pageTitle.trim() === '') {
                $('.fg-cms-title-change-form').addClass('has-error');
                $('.fg-error-add-required').append('<span class="required">' + transFields.required + '</span>');
                return false;
            }
            else {
                 pageTitle = $('<div/>').text(pageTitle).html();
                FgXmlHttp.post(changePageTitlePath, { 'config': configId, 'title': pageTitle }, false, configCreate.successCallback, function (response) {
                    $('#config-title-change-modal').modal('hide');
                    $('.page-title  .page-title-text').html('');
                    $('.page-title  .page-title-text').html(pageTitle);
                });
            }
        });
    };
    FgConfigUpdateHeader.prototype.previewHeaderImage = function (id, savedConfig) {
        if (id != '') {
            var path = '/uploads/' + club_id + '/admin/website_header/';
            var filenamepath = path + savedConfig[id].fileName;
        }
        var rowId = 'header-' + savedConfig[id].id;
        var datatoTemplate = { name: rowId, id: rowId, };
        ImagesUploader.showExistImagePreviewForLogo(rowId, id, datatoTemplate, filenamepath);
    };
    FgConfigUpdateHeader.prototype.saveData = function () {
        $('#save_bac').off();
        $('#save_bac').attr("disabled", true);
        $('#reset_changes').attr("disabled", true);
        $("#reset_changes").click(function() {
            $('#fg-cms-theme-header').html('');
            configCreate.getHeadersOfTheme();
        });
        $('body').on('click', '#save_bac', function (event) {
            event.stopImmediatePropagation();
            event.stopPropagation();
            $('.fg-del-close').attr("disabled", true);
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
            FgXmlHttp.post(fgHeaderSave, { 'configId': selectedTheme, 'headerStyle': headerStyle,'theme2head':theme2head,'logoStyle':logoStyle,  'headerLogos': headerLogos }, false, '', function (response) {
                if (response.status === 'SUCCESS') {
                    configDetails = response.viewParams.configDetails;
                    headerPosition = response.viewParams.configDetails.headerPosition||'full_width';
                    logoPosition = response.viewParams.configDetails.headerLogoPosition||'left';
                   
                    configCreate.displayImageAfterSave(response.viewParams);
                }
            });
            configCreate.initDirty();
            $('.fg-del-close').attr("disabled", false);
        });
    };
    FgConfigUpdateHeader.prototype.displayImageAfterSave = function (viewParams) {
        var labelsData = themeList['headerImageLabels'];
        savedConfig = viewParams['savedConfig'];
        headerScrolling = (viewParams['configDetails'].headerScrolling == 1 ? '1' : '0');
        for (var id in labelsData) {
            $("#cms_header_id" + id).val('');
            $("#cms_header_file" + id).val('');
            $("#cms_header_changed" + id).val(0);
            $("#cms_header" + id).val('');
            $("#cms_header_removed" + id).val('');
            if (savedConfig.length > 0) {
                if (savedConfig[id].typeid == id && savedConfig[id].hasOwnProperty('fileName')) {
                    $("#cms_header_id" + id).val(savedConfig[id].id);
                    $("#cms_header_file" + id).val(savedConfig[id].fileName);
                    $("#cms_header" + id).val(savedConfig[id].fileName);
                }
            }
        }
    };
    FgConfigUpdateHeader.prototype.getHeadersOfTheme = function () {
        var fgConfig = new FgConfigUpdateHeader();
        var selThemeLabels = headerLabels;
        $('#fg-cms-theme-header').append(FGTemplate.bind(headerTemplate, { 'selectedTheme': selectedTheme, 'data': themeList['headerImageLabels'], 'selThemeLabels': selThemeLabels, 'headercount': themeList['noOfHeaderImages'] }));
        fgConfig.displayDropzone();
        FgFormTools.handleUniform();
    };
    FgConfigUpdateHeader.prototype.displayDropzone = function (callback) {
        if (callback === void 0) { callback = 0; }
        var fgConfig = new FgConfigUpdateHeader();
        var maxHeader = (themeList['noOfHeaderImages'] - 1);
        var fileContainer = "#fg-files-uploaded-lists-wrapper" + maxHeader;
        var labelsData = themeList['headerImageLabels'];
        if (this.flagDrag == 0) {
            for (var id in labelsData) {
                var fileid = "image-uploader" + id;
                imageElementUploaderOptions.dropZoneElement = "#fg-files-uploaded-lists-wrapper" + id;
                this.listconfig[id] = imageElementUploaderOptions;
                this.listconfig[id].fileListTemplateContainer = "#fg-files-uploaded-lists-wrapper" + id;
                var newOptions = this.listconfig[id];
                FgFileUpload.init($("#" + fileid), newOptions);
                var btnid = 'triggerFileUpload' + id;
                var savedId = '';
                if (savedConfig.length > 0) {
                    if (savedConfig[id].typeid == id && savedConfig[id].hasOwnProperty('fileName')) {
                        $("#cms_header_id" + id).val(savedConfig[id].id);
                        $("#cms_header_file" + id).val(savedConfig[id].fileName);
                        $("#cms_header" + id).val(savedConfig[id].fileName);
                        $("#header-type" + id).val(savedConfig[id].headerLabel);
                        fgConfig.previewHeaderImage(id, savedConfig);
                    }
                    else if (callback == 1) {
                        var removeDivid = $(imageElementUploaderOptions.dropZoneElement).find('.fg-dropzone-preview').parent().attr("id");
                        $('#' + removeDivid).remove();
                    }
                }
                else {
                    if (callback == 1) {
                        var removeDivid = $(imageElementUploaderOptions.dropZoneElement).find('.fg-dropzone-preview').parent().attr("id");
                        if ($('#' + removeDivid).length > 0)
                            $('#' + removeDivid).remove();
                    }
                }
                fgConfig.handleExistingimageUpload("#" + btnid, ('#' + fileid));
                this.flagDrag = 1;
            }
        }
    };
    return FgConfigUpdateHeader;
}());
//# sourceMappingURL=Fg-tm-conf-update-headers.js.map