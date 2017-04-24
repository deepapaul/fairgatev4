/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />

class FgConfigFont {
    data: string;
    constructor(public loaderMessage, public fontSaveUrl) {
        Metronic.startPageLoading({ message: loaderMessage });
    }

    public initFonts() {
        $('#formHideDom').html($('#themeFontConfigForm fieldset').html());
        $(".fg-font-select").each(function() {
            var fontName = $('#' + $(this).attr('id') + '_DEFAULT').val();
            $(this).val(fontName);
        });
        $('div .bootstrap-select').css("width", '100%');
        $("#paneltab li").removeClass("active");
        $('#fg_tab_font').addClass("active");
        $(".fg-action-menu-wrapper").FgPageTitlebar({
            title: true,
            editTitleInline: false,
            tab: true,
            tabType: 'server',
            languageSwitch: true,
            editTitle: true
        });
        $('#themeFontConfigForm .fg-font-select').selectpicker({});
        $('#themeFontConfigForm .fg-strength-select').selectpicker({});
        this.changePageTitle();
        this.savePageTitle();
    }

    public discardChanges(fontSaveUrl) {
        Metronic.startPageLoading();
        $('#themeFontConfigForm fieldset').html($('#formHideDom').html());
        $("#formHideDom .fg-font-select").each(function() {
            let divId = $(this).attr('id');
            var fontName = $(this).val();
            $("#themeFontConfigForm #" + divId).val(fontName);
        });
        $("#formHideDom .fg-strength-select").each(function() {
            let divId = $(this).attr('id');
            var strengthName = $(this).val();
            $("#themeFontConfigForm #" + divId).val(strengthName);
        });
        $("#formHideDom .italicCheck").each(function() {
            let divId = $(this).attr('id');
            var value = $(this).parent().attr('class');
            if (value == 'checked') {
                $("#themeFontConfigForm #" + divId).prop('checked', true);
                $("#themeFontConfigForm #" + divId).parent().attr('class', 'checked');
            } else {
                $("#themeFontConfigForm #" + divId).prop('checked', false);
                $("#themeFontConfigForm #" + divId).parent().attr('class', '');
            }
        });
        $("#formHideDom .ucaseCheck").each(function() {
            let divId = $(this).attr('id');
            var value = $(this).parent().attr('class');
            if (value == 'checked') {
                $("#themeFontConfigForm #" + divId).prop('checked', true);
                $("#themeFontConfigForm #" + divId).parent().attr('class', 'checked');
            } else {
                $("#themeFontConfigForm #" + divId).prop('checked', false);
                $("#themeFontConfigForm #" + divId).parent().attr('class', '');
            }
        });
        $('div .bootstrap-select').css("width", '100%');
        var uniformSuspectedElements = $("#themeFontConfigForm input:checkbox")
        if (uniformSuspectedElements.parent().parent().is("div")) {
            uniformSuspectedElements.unwrap().unwrap();
        }
        FgFormTools.handleUniform();
        $('#themeFontConfigForm .fg-font-select').selectpicker({});
        $('#themeFontConfigForm .fg-strength-select').selectpicker({});
        this.initPageFns(fontSaveUrl);
        Metronic.stopPageLoading();
    }

    public saveChanges(fontSaveUrl) {
        let postData: any;
        postData = $('#themeFontConfigForm').serializeArray();
        $("#themeFontConfigForm .fg-font-select").each(function() {
            let divId = $(this).attr('id');
            var fontName = $(this).val();
            $("#formHideDom #" + divId).val(fontName);
        });
        $("#themeFontConfigForm .fg-strength-select").each(function() {
            let divId = $(this).attr('id');
            var strengthName = $(this).val();
            $("#formHideDom #" + divId).val(strengthName);
        });
        $("#themeFontConfigForm .italicCheck").each(function() {
            let divId = $(this).attr('id');
            var value = $(this).parent().attr('class');
            if (value == 'checked') {
                $("#formHideDom #" + divId).prop('checked', true);
                $("#formHideDom #" + divId).parent().attr('class', 'checked');
            } else {
                $("#formHideDom #" + divId).prop('checked', false);
                $("#formHideDom #" + divId).parent().attr('class', '');
            }
        });
        $("#themeFontConfigForm .ucaseCheck").each(function() {
            let divId = $(this).attr('id');
            var value = $(this).parent().attr('class');
            if (value == 'checked') {
                $("#formHideDom #" + divId).prop('checked', true);
                $("#formHideDom #" + divId).parent().attr('class', 'checked');
            } else {
                $("#formHideDom #" + divId).prop('checked', false);
                $("#formHideDom #" + divId).parent().attr('class', '');
            }
        });
        let result: boolean = this.initPageDirty();
        if (result == true) {
            FgXmlHttp.post(fontSaveUrl, postData, false);
        }
        $('#defaultConfigFlag').val('0');
    }

    public initPageDirty() {
        FgDirtyFields.init('themeFontConfigForm', {
            dirtyFieldSettings: {
                denoteDirtyForm: true
            },
            enableDiscardChanges: false,
            initialHtml: false
        });
        return true;
    }

    public initPageFns(fontSaveUrl) {
        let thisObj = this;
        $("#themeFontConfigForm #save_changes").click(function(event) {
            event.stopPropagation();
            event.stopImmediatePropagation();
            thisObj.saveChanges(fontSaveUrl);
        });
        $("#themeFontConfigForm #reset_changes").click(function(event) {
            event.stopPropagation();
            event.stopImmediatePropagation();
            thisObj.discardChanges(fontSaveUrl);
        });
        this.initPageDirty();
    }

    public generateSelectBox(fonts, fontSaveUrl) {
        let optionString: string = '';
        let headLinkStr: string = '';
        var familyString: string = '';

        for (var i = 0; i < fonts.length; i++) {
            optionString += "<option style='font-family: " + fonts[i].family + ";' value='" + fonts[i].family + "'>" + fonts[i].family + "</option>";
            familyString += fonts[i].family.replace(/ /g, '+') + ':400|';
            
            if(i % 100 == 0){
                $('head').append("<link href='https://fonts.googleapis.com/css?family=" + familyString + "' rel='stylesheet' type='text/css'>");
                familyString = '';
            }
        }
        $('head').append("<link href='https://fonts.googleapis.com/css?family=" + familyString + "' rel='stylesheet' type='text/css'>");
        $('.fg-font-select').html(optionString);
        this.initFonts();
        this.initPageFns(fontSaveUrl);
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
            let configId = $('#CMS_FONT_CONFIG_ID').val()
            let pageTitle = $('#pageTitleChange').val();
            if ($.trim(pageTitle) === '') {
                $('.fg-cms-title-change-form').addClass('has-error');
                $('.fg-error-add-required').append('<span class="required">' + transFields.required + '</span>');
                return false;
            } else {
                FgXmlHttp.post(changePageTitlePath, { 'config': configId, 'title': pageTitle }, '', function(response) {
                    $('#config-title-change-modal').modal('hide');
                    $('.page-title  .page-title-text').html('');
                    $('.page-title  .page-title-text').html(pageTitle);
                });
            }
        });
    }
}