var thisObj;
var FgPageTitlebar;
var FgConfigUpdateColor = (function () {
    function FgConfigUpdateColor() {
        thisObj = this;
    }
    FgConfigUpdateColor.prototype.createInit = function () {
        this.pageTitleInit();
        this.bindColorListTemplate();
        this.activateColor();
        this.duplicateColor();
        this.createColor();
        this.saveCreateModal();
        this.editColor();
        this.deleteModal();
        this.saveDeleteModal();
        this.changePageTitle();
        this.savePageTitle();
    };
    FgConfigUpdateColor.prototype.pageTitleInit = function () {
        FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            editTitleInline: false,
            tab: true,
            tabType: 'server',
            languageSwitch: false,
            editTitle: true
        });
    };
    FgConfigUpdateColor.prototype.bindColorListTemplate = function () {
        $.ajax({
            type: 'GET',
            url: getColorsListPath,
            data: { 'configId': configId },
            success: function (response) {
                thisObj.reInitList(response);
            },
        });
    };
    FgConfigUpdateColor.prototype.reInitList = function (response) {
        $('.fg-color-scheme-list-wrapper').html('');
        $('.fg-color-scheme-list-wrapper').append(FGTemplate.bind('tm-color-scheme-list', { 'data': response }));
        FgTooltip.init();
    };
    FgConfigUpdateColor.prototype.activateColor = function () {
        $('body').on('click', '.fg-activate-link', function () {
            var colorId = $(this).attr('data-id');
            var fgActivateColorSchemePath = fgActivateColorScheme.replace("dummy", 'activate');
            FgXmlHttp.post(fgActivateColorSchemePath, { 'color': colorId, 'config': configId }, '', function (response) {
                if (response.status === 'SUCCESS') {
                    thisObj.reInitList(response.data);
                }
            });
        });
    };
    FgConfigUpdateColor.prototype.duplicateColor = function () {
        $('body').on('click', '.fg-duplicate i', function () {
            var colorId = $(this).attr('data-id');
            var colorSchemes = {};
            $('.fg-colorscheme-display-' + colorId).each(function () {
                colorSchemes[$(this).attr('data-title')] = $(this).attr('data-value');
            });
            var colorSchemeData = JSON.stringify(colorSchemes);
            var themeId = $(this).closest('li').attr('data-theme');
            var fgActivateColorSchemePath = fgActivateColorScheme.replace("dummy", 'duplicate');
            FgXmlHttp.post(fgActivateColorSchemePath, { 'color': colorId, 'config': configId, 'colorSchemeData': colorSchemeData, 'themeId': themeId }, '', function (response) {
                if (response.status === 'SUCCESS') {
                    thisObj.reInitList(response.data);
                }
            });
        });
    };
    FgConfigUpdateColor.prototype.createColor = function () {
        $('body').on('click', '.fg-add-item', function () {
            $('.fg-modal-create-edit-content').html('');
            var colorSchemes = {};
            $('.fg-color-schemes-wrapper[data-active="1"] ul.fg-color-schemes li').each(function () {
                colorSchemes[$(this).attr('data-title')] = {};
                colorSchemes[$(this).attr('data-title')]['value'] = $(this).attr('data-value');
                colorSchemes[$(this).attr('data-title')]['title'] = colorSchemeTrans[$(this).attr('data-title')];
            });
            $('.fg-modal-create-edit-content').append(FGTemplate.bind('tm-color-scheme-create-edit', { 'flag': 'create', 'title': transFields.createPopup, 'colorSchemes': colorSchemes }));
            $('#createEditPopup').modal('show');
            var fgColorPicker = new FgConfigUpdateColor();
            fgColorPicker.initColorPicker();
            FgTooltip.init();
        });
    };
    FgConfigUpdateColor.prototype.editColor = function () {
        $('body').on('click', '.fg-edit-scheme', function () {
            var colorId = $(this).attr('data-id');
            $('.fg-modal-create-edit-content').html('');
            var colorSchemes = {};
            $('ul.fg-color-schemes[data-id=' + colorId + '] li').each(function () {
                colorSchemes[$(this).attr('data-title')] = {};
                colorSchemes[$(this).attr('data-title')]['value'] = $(this).attr('data-value');
                colorSchemes[$(this).attr('data-title')]['title'] = colorSchemeTrans[$(this).attr('data-title')];
            });
            $('.fg-modal-create-edit-content').append(FGTemplate.bind('tm-color-scheme-create-edit', { 'colorId': colorId, 'flag': 'edit', 'title': transFields.editPopup, 'colorSchemes': colorSchemes }));
            $('#createEditPopup').modal('show');
            var fgColorPicker = new FgConfigUpdateColor();
            fgColorPicker.initColorPicker();
            FgTooltip.init();
        });
    };
    FgConfigUpdateColor.prototype.initColorPicker = function () {
        var rgba = '';
        $('.mini-color').minicolors({
            position: $(this).attr('data-position') || 'bottom left',
            control: $(this).attr('data-control') || 'wheel',
            theme: 'bootstrap',
            format: 'rgb',
            rgb: true,
            opacity: true
        });
    };
    FgConfigUpdateColor.prototype.saveCreateModal = function () {
        $('body').on('click', '#save-create-modal', function () {
            var colorSchemes = {};
            $('.mini-color').each(function () {
                colorSchemes[$(this).attr('data-keyval')] = $(this).val();
            });
            var colorSchemeData = JSON.stringify(colorSchemes);
            var themeId = $('.fg-border-line').attr('data-theme');
            var flag = $(this).attr('data-flag');
            var colorId = '';
            if (flag == 'edit') {
                colorId = $(this).attr('data-id');
            }
            FgXmlHttp.post(fgCreateColorSchemePath, { 'colorId': colorId, 'flag': flag, 'config': configId, 'colorSchemeData': colorSchemeData, 'themeId': themeId }, '', function (response) {
                if (response.status === 'SUCCESS') {
                    $('#createEditPopup').modal('hide');
                    thisObj.reInitList(response.data);
                }
            });
        });
    };
    FgConfigUpdateColor.prototype.deleteModal = function () {
        $('body').on('click', '.closeico', function () {
            $('.fg-theme-color-id').val('');
            var colorId = $(this).attr('data-id');
            $('.fg-theme-color-id').val(colorId);
            $('#cms-theme-delete-modal').modal('show');
        });
    };
    FgConfigUpdateColor.prototype.saveDeleteModal = function () {
        $(document).off('click', '#removePopup');
        $(document).on('click', '#removePopup', function () {
            var colorId = $('.fg-theme-color-id').val();
            var fgActivateColorSchemePath = fgActivateColorScheme.replace("dummy", 'delete');
            FgXmlHttp.post(fgActivateColorSchemePath, { 'color': colorId, 'config': configId, 'colorSchemeData': '', 'themeId': '' }, '', function (response) {
                if (response.status === 'SUCCESS') {
                    $('#cms-theme-delete-modal').modal('hide');
                    thisObj.reInitList(response.data);
                }
            });
        });
    };
    FgConfigUpdateColor.prototype.changePageTitle = function () {
        $('body').on('click', '.fg-action-editTitle', function () {
            $('.fg-cms-title-change-form').removeClass('has-error');
            $('span.required').remove();
            var titleText = $('.page-title  .page-title-text').html();
            $('#pageTitleChange').val(titleText);
            $('#config-title-change-modal').modal('show');
        });
    };
    FgConfigUpdateColor.prototype.savePageTitle = function () {
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
                    FgPageTitlebar.setMoreTab();
                });
            }
        });
    };
    return FgConfigUpdateColor;
}());
