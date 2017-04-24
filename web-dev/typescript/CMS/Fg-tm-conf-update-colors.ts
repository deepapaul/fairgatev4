/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
var thisObj;
var FgPageTitlebar;
class FgConfigUpdateColor {
    constructor() {
        thisObj = this;
    }
    public createInit()
    {
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
    }
    public pageTitleInit() {
        FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            editTitleInline: false,
            tab: true,
            tabType: 'server',
            languageSwitch: false,
            editTitle: true
        });
    }
    public bindColorListTemplate() {
        $.ajax({
            type: 'GET',
            url: getColorsListPath,
            data: {'configId':configId},
            success: function(response) {
                thisObj.reInitList(response)
            },
        });
    }
    public reInitList(response) {
        $('.fg-color-scheme-list-wrapper').html('');
        $('.fg-color-scheme-list-wrapper').append(FGTemplate.bind('tm-color-scheme-list', {'data':response}));
         FgTooltip.init();
    }
    public activateColor() {
        $('body').on('click', '.fg-activate-link', function() {
            let colorId = $(this).attr('data-id');
            let fgActivateColorSchemePath = fgActivateColorScheme.replace("dummy", 'activate');
            FgXmlHttp.post(fgActivateColorSchemePath, {'color' : colorId, 'config': configId}, '', function(response) {
                if (response.status === 'SUCCESS') {
                    thisObj.reInitList(response.data)
                }
            });
        });
    }
    public duplicateColor() {
        $('body').on('click', '.fg-duplicate i', function() {
            let colorId = $(this).attr('data-id');
            let colorSchemes = {};
            $('.fg-colorscheme-display-'+colorId).each(function() {
                colorSchemes[$(this).attr('data-title')] = $(this).attr('data-value');
            });
            let colorSchemeData = JSON.stringify(colorSchemes);
            let themeId = $(this).closest('li').attr('data-theme');
            let fgActivateColorSchemePath = fgActivateColorScheme.replace("dummy", 'duplicate');
            FgXmlHttp.post(fgActivateColorSchemePath, {'color' : colorId, 'config': configId, 'colorSchemeData': colorSchemeData, 'themeId': themeId}, '', function(response) {
                if (response.status === 'SUCCESS') {
                    thisObj.reInitList(response.data)
                }
            });
        });
    }
    public createColor() {
        $('body').on('click', '.fg-add-item', function() {
            $('.fg-modal-create-edit-content').html('');
            let colorSchemes = {};
            $('.fg-color-schemes-wrapper[data-active="1"] ul.fg-color-schemes li').each(function() {
                colorSchemes[$(this).attr('data-title')] = {};
                colorSchemes[$(this).attr('data-title')]['value'] = $(this).attr('data-value');
                colorSchemes[$(this).attr('data-title')]['title'] = colorSchemeTrans[$(this).attr('data-title')];
            });
            $('.fg-modal-create-edit-content').append(FGTemplate.bind('tm-color-scheme-create-edit', {'flag':'create','title': transFields.createPopup, 'colorSchemes': colorSchemes}));
            $('#createEditPopup').modal('show');
            let fgColorPicker = new FgConfigUpdateColor();
            fgColorPicker.initColorPicker();
            FgTooltip.init();
        })
    }
    public editColor() {
        $('body').on('click', '.fg-edit-scheme', function() {
            let colorId = $(this).attr('data-id');
            $('.fg-modal-create-edit-content').html('');
            let colorSchemes = {};
            $('ul.fg-color-schemes[data-id='+colorId+'] li').each(function() {
                colorSchemes[$(this).attr('data-title')] = {};
                colorSchemes[$(this).attr('data-title')]['value'] = $(this).attr('data-value');
                colorSchemes[$(this).attr('data-title')]['title'] = colorSchemeTrans[$(this).attr('data-title')];
            });
            $('.fg-modal-create-edit-content').append(FGTemplate.bind('tm-color-scheme-create-edit', {'colorId':colorId,'flag':'edit','title': transFields.editPopup, 'colorSchemes': colorSchemes}));
            $('#createEditPopup').modal('show');
            let fgColorPicker = new FgConfigUpdateColor();
            fgColorPicker.initColorPicker();
            FgTooltip.init();
        })
    }
    public initColorPicker() {
        var rgba = '';
        $('.mini-color').minicolors({
          position: $(this).attr('data-position') || 'bottom left',                    
          control: $(this).attr('data-control') || 'wheel',
          theme: 'bootstrap',
          format:'rgb',
          rgb:true,
          opacity:true
        });
    }
    public saveCreateModal() {
        $('body').on('click', '#save-create-modal', function() {
            let colorSchemes = {};
            $('.mini-color').each(function() {
                colorSchemes[$(this).attr('data-keyval')] = $(this).val();
            });
            let colorSchemeData = JSON.stringify(colorSchemes);
            let themeId = $('.fg-border-line').attr('data-theme');
            let flag = $(this).attr('data-flag');
            let colorId = '';
            if (flag == 'edit') {
                colorId = $(this).attr('data-id');
            }
            FgXmlHttp.post(fgCreateColorSchemePath, {'colorId':colorId,'flag': flag,'config': configId, 'colorSchemeData': colorSchemeData, 'themeId': themeId}, '', function(response) {
                if (response.status === 'SUCCESS') {
                    $('#createEditPopup').modal('hide');
                    thisObj.reInitList(response.data)
                }
            });
        });
    }
    public deleteModal() {
        $('body').on('click', '.closeico', function() {
            $('.fg-theme-color-id').val('');
            let colorId = $(this).attr('data-id');
            $('.fg-theme-color-id').val(colorId);
            $('#cms-theme-delete-modal').modal('show');
        })
    }
    public saveDeleteModal() {
        $(document).off('click', '#removePopup');
        $(document).on('click', '#removePopup', function() {
            let colorId = $('.fg-theme-color-id').val();
            let fgActivateColorSchemePath = fgActivateColorScheme.replace("dummy", 'delete');
            FgXmlHttp.post(fgActivateColorSchemePath, {'color' : colorId, 'config': configId, 'colorSchemeData': '', 'themeId': ''}, '', function(response) {
                if (response.status === 'SUCCESS') {
                    $('#cms-theme-delete-modal').modal('hide');
                    thisObj.reInitList(response.data)
                }
            });
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
            if ($.trim(pageTitle) === '') {
                $('.fg-cms-title-change-form').addClass('has-error');
                $('.fg-error-add-required').append('<span class="required">'+transFields.required+'</span>');
                return false;
            } else {
                FgXmlHttp.post(changePageTitlePath, {'config': configId, 'title': pageTitle}, '', function(response) {
                    $('#config-title-change-modal').modal('hide');
                    $('.page-title  .page-title-text').html('');
                    $('.page-title  .page-title-text').html(pageTitle);
                    FgPageTitlebar.setMoreTab();
                });
            }
        });
    }
}

