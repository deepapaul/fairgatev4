var FgCmsThemeBackgroundList = (function () {
    function FgCmsThemeBackgroundList() {
        this.tabselected = 1;
    }
    FgCmsThemeBackgroundList.prototype.renderTabContent = function (templateId, data, appendDom) {
        var pageContent = FGTemplate.bind(templateId, {
            backgroundDetails: data
        });
        $(appendDom).html(pageContent);
    };
    FgCmsThemeBackgroundList.prototype.initUpload = function (settings) {
        $('#tab1 .fg-media-img-uploader').off('click');
        $('#tab1 .fg-media-img-uploader').on('click', function () {
            $('#tab1 .image-uploader').trigger('click');
        });
        imguploaderObj = FgFileUpload.init($('#tab1 .image-uploader'), settings);
    };
    FgCmsThemeBackgroundList.prototype.createImagePreview = function (input, imgTagId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#' + imgTagId).attr('src', e.target.result).css({ 'height': '100px' });
            };
            reader.readAsDataURL(input.files[0]);
        }
    };
    FgCmsThemeBackgroundList.prototype.addImgCallback = function (uploadedObj, data) {
        fgthemeBackground.handleSortOrder(uploadedObj, data);
        FgDirtyFields.updateFormState();
        $('select.select2').select2();
    };
    FgCmsThemeBackgroundList.prototype.handleSortOrder = function (uploadedObj, data) {
        var rowId = data.fileid;
        var n = ($(".fg-files-uploaded-lists-wrapper li.fg-files-uploaded-list").length) ? (parseInt($(".fg-files-uploaded-lists-wrapper li").length)) : 1;
        if (rowId) {
            $('#' + rowId).find('input.fg-dev-sortable').val(n);
        }
    };
    FgCmsThemeBackgroundList.prototype.addGalleryImgCallback = function (data) {
        fgthemeBackground.addImgCallback({}, { 'fileid': data[0].itemId });
        FgDirtyFields.updateFormState();
    };
    FgCmsThemeBackgroundList.prototype.handleGalleryBrowser = function (gellerySettings, mainIdentifier) {
        FgGalleryBrowser.initialize(gellerySettings);
        FgGalleryBrowser.setSortable($('.fg-files-uploaded-lists-wrapper'));
        setTimeout(function () {
            $(mainIdentifier + ".fg-a-add-video").remove();
        }, 100);
    };
    FgCmsThemeBackgroundList.prototype.handleDeleteNewRow = function () {
        $('body').off('click', '.fg-delete-img');
        $('body').on('click', '.fg-delete-img', function (e) {
            $(this).parents().eq(1).remove();
        });
    };
    FgCmsThemeBackgroundList.prototype.saveBackgroundImageDetails = function () {
        var _this = this;
        $("body").on('click', "#save_changes", function () {
            $("#articleimg-upload-error-container").html('');
            if ($('#radios-0').is(':checked')) {
                $('#default_bg_slider_time').val('');
                $('#random_bg_slider_time').val('');
            }
            if ($("#radios-1").is(':checked') && $('#default_bg_slider_time').val() == '') {
                $("#articleimg-upload-error-container").html(timevalidationMessage);
                return;
            }
            else if ($("#sliderwithRandom").is(':checked') && $('#random_bg_slider_time').val() == '') {
                $("#articleimg-upload-error-container").html(timevalidationMessage);
                return;
            }
            else if (($('#default_bg_slider_time').val() != '') && $.isNumeric($('#default_bg_slider_time').val()) == false) {
                $("#articleimg-upload-error-container").html(validationMessage);
                return;
            }
            else if (($('#random_bg_slider_time').val() != '') && $.isNumeric($('#random_bg_slider_time').val()) == false) {
                $("#articleimg-upload-error-container").html(validationMessage);
                return;
            }
            if($("ul.fg-files-uploaded-lists-wrapper li.has-error").length >0 ){
                return false;
            }
            $("#articleimg-upload-error-container").html('');
            var objectGraph = {};
            $("ul.fg-files-uploaded-lists-wrapper li:not(.inactiveblock)").each(function (e, value) {
                var oldVal = $(this).find(".fg-dev-sortable").val();
                if (oldVal != (e + 1)) {
                    $(this).find(".fg-dev-sortable").val(e + 1);
                    $(this).find(".fg-dev-sortable").addClass('fg-dev-newfield');
                }
            });
            objectGraph = FgInternalParseFormField.formFieldParse('fg_cms_background_add');
            _this.initDirty();
            $('.fg-files-uploaded-lists-wrapper').find('.inactiveblock').remove();
            var imageDetails = JSON.stringify(objectGraph);
            FgXmlHttp.post(backgroundImageSave, {
                'imageDetails': imageDetails, 'configId': configId
            }, '', function (response) {
                backgroundDetails = response.backgroundData;
                fgthemeBackground.renderTabContent('templateOriginalSize', backgroundDetails, '#tab2');
                fgthemeBackground.renderTabContent('templateFullscreen', backgroundDetails, '#tab1');
                if (fgthemeBackground.tabselected == '2') {
                    fgthemeBackground.initOriginalImageUpload(backgroundOriginalImgUploaderOptions, '#tab2');
                    fgthemeBackground.handleGalleryBrowser(originalGalleryBrowserSettings, "#tab2");
                }
                else {
                    fgthemeBackground.initUpload(backgroundFullImgUploaderOptions, '#tab1');
                    fgthemeBackground.handleGalleryBrowser(galleryBrowserSettings, '#tab1');
                }
                fgthemeBackground.initDirty();
                $('select.select2').select2();
                FgFormTools.handleUniform();
            });
        });
    };
    FgCmsThemeBackgroundList.prototype.handleDeleteIconColor = function () {
        $('body').off('click', '.make-switch');
        $('body').on('click', '.make-switch', function (e) {
            if ($(this).is(':checked') == true) {
                $(this).parents('li').addClass('inactiveblock');
            }
            else {
                $(this).parents('li').removeClass('inactiveblock');
            }
        });
    };
    FgCmsThemeBackgroundList.prototype.initOriginalImageUpload = function (settings) {
        $('#fg-media-img-uploader').off('click');
        $('#fg-media-img-uploader').on('click', function () {
            $('#image-original-uploader').trigger('click');
        });
        FgFileUpload.init($('#image-original-uploader'), settings);
    };
    FgCmsThemeBackgroundList.prototype.initDirty = function () {
        FgDirtyFields.init('fg_cms_background_add', { saveChangeSelector: "#save_changes, #reset_changes", enableDiscardChanges: true, enableUpdateSortOrder: true, discardChangesCallback: fgthemeBackground.discardChangesCallback });
    };
    FgCmsThemeBackgroundList.prototype.bgtabInit = function () {
        var _this = this;
        $(function () {
            _this.initDirty();
            _this.titlebarObj = $(".fg-action-menu-wrapper").FgPageTitlebar({
                title: true,
                editTitleInline: false,
                tab: true,
                tabType: 'server',
                languageSwitch: false,
                editTitle: true
            });
            $("#paneltab").find(".active").removeClass('active');
            $("#fg_tab_background").addClass('active');
            $('body').on('click', 'ul.fg-dev-bg-tabs li', function () {
                if ($(this).attr('data-type') == 1) {
                    _this.tabselected = 1;
                    _this.handleGalleryBrowser(galleryBrowserSettings, '#tab1');
                    _this.initUpload(backgroundFullImgUploaderOptions, '#tab1');
                }
                else {
                    _this.tabselected = 2;
                    $('select.select2').select2();
                    _this.handleGalleryBrowser(originalGalleryBrowserSettings, "#tab2");
                    _this.initOriginalImageUpload(backgroundOriginalImgUploaderOptions, '#tab2');
                }
            });
            $('body').on('click', '.fg-bg-radio', function () {
                $(".fg-bg-radio").val('');
                $(this).parents('.radio-block').find('.fg-radio-select').trigger('click');
                $.uniform.update();
            });
            _this.initDirty();
            _this.changePageTitle();
            _this.savePageTitle();
        });
    };
    FgCmsThemeBackgroundList.prototype.discardChangesCallback = function () {
        fgthemeBackground.renderTabContent('templateOriginalSize', backgroundDetails, '#tab2');
        fgthemeBackground.renderTabContent('templateFullscreen', backgroundDetails, '#tab1');
        if (fgthemeBackground.tabselected == '2') {
            fgthemeBackground.initOriginalImageUpload(backgroundOriginalImgUploaderOptions, '#tab2');
            fgthemeBackground.handleGalleryBrowser(originalGalleryBrowserSettings, "#tab2");
            $("#data_li_2").addClass("active");
            $("#tab2").addClass("active");
            $("#data_li_1").removeClass("active");
            $("#tab1").removeClass("active");
        }
        else {
            fgthemeBackground.initUpload(backgroundFullImgUploaderOptions, '#tab1');
            fgthemeBackground.handleGalleryBrowser(galleryBrowserSettings, '#tab1');
        }
        fgthemeBackground.initDirty();
        $('select.select2').select2();
        FgFormTools.handleUniform();
        fgthemeBackground.titlebarObj.setMoreTab();
    };
    FgCmsThemeBackgroundList.prototype.changePageTitle = function () {
        $('body').on('click', '.fg-action-editTitle', function () {
            $('.fg-cms-title-change-form').removeClass('has-error');
            $('span.required').remove();
            var titleText = $('.page-title  .page-title-text').html();
            $('#pageTitleChange').val(titleText);
            $('#config-title-change-modal').modal('show');
        });
    };
    FgCmsThemeBackgroundList.prototype.savePageTitle = function () {
        $(document).off('click', '#savePopup');
        $(document).on('click', '#savePopup', function () {
            var pageTitle = $('#pageTitleChange').val();
            if ($.trim(pageTitle) === '') {
                $('.fg-cms-title-change-form').addClass('has-error');
                $('.fg-error-add-required').append('<span class="required">' + transFields.required + '</span>');
                return false;
            }
            else {
                 pageTitle = $('<div/>').text(pageTitle).html();
                FgXmlHttp.post(changePageTitlePath, { 'config': configId, 'title': pageTitle }, '', function (response) {
                    $('#config-title-change-modal').modal('hide');
                    $('.page-title  .page-title-text').html('');
                    $('.page-title  .page-title-text').html(pageTitle);
                });
            }
        });
    };
    return FgCmsThemeBackgroundList;
}());
//# sourceMappingURL=Fg_cms_theme_background_list.js.map