var FgConfigFont = (function () {
    function FgConfigFont(loaderMessage, fontSaveUrl) {
        this.loaderMessage = loaderMessage;
        this.fontSaveUrl = fontSaveUrl;
        Metronic.startPageLoading({ message: loaderMessage });
    }
    FgConfigFont.prototype.initFonts = function () {
        $('#formHideDom').html($('#themeFontConfigForm fieldset').html());
        $(".fg-font-select").each(function () {
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
    };
    FgConfigFont.prototype.discardChanges = function (fontSaveUrl) {
        Metronic.startPageLoading();
        $('#themeFontConfigForm fieldset').html($('#formHideDom').html());
        $("#formHideDom .fg-font-select").each(function () {
            var divId = $(this).attr('id');
            var fontName = $(this).val();
            $("#themeFontConfigForm #" + divId).val(fontName);
        });
        $("#formHideDom .fg-strength-select").each(function () {
            var divId = $(this).attr('id');
            var strengthName = $(this).val();
            $("#themeFontConfigForm #" + divId).val(strengthName);
        });
        $("#formHideDom .italicCheck").each(function () {
            var divId = $(this).attr('id');
            var value = $(this).parent().attr('class');
            if (value == 'checked') {
                $("#themeFontConfigForm #" + divId).prop('checked', true);
                $("#themeFontConfigForm #" + divId).parent().attr('class', 'checked');
            }
            else {
                $("#themeFontConfigForm #" + divId).prop('checked', false);
                $("#themeFontConfigForm #" + divId).parent().attr('class', '');
            }
        });
        $("#formHideDom .ucaseCheck").each(function () {
            var divId = $(this).attr('id');
            var value = $(this).parent().attr('class');
            if (value == 'checked') {
                $("#themeFontConfigForm #" + divId).prop('checked', true);
                $("#themeFontConfigForm #" + divId).parent().attr('class', 'checked');
            }
            else {
                $("#themeFontConfigForm #" + divId).prop('checked', false);
                $("#themeFontConfigForm #" + divId).parent().attr('class', '');
            }
        });
        $('div .bootstrap-select').css("width", '100%');
        var uniformSuspectedElements = $("#themeFontConfigForm input:checkbox");
        if (uniformSuspectedElements.parent().parent().is("div")) {
            uniformSuspectedElements.unwrap().unwrap();
        }
        FgFormTools.handleUniform();
        $('#themeFontConfigForm .fg-font-select').selectpicker({});
        $('#themeFontConfigForm .fg-strength-select').selectpicker({});
        this.initPageFns(fontSaveUrl);
        Metronic.stopPageLoading();
    };
    FgConfigFont.prototype.saveChanges = function (fontSaveUrl) {
        var postData;
        postData = $('#themeFontConfigForm').serializeArray();
        $("#themeFontConfigForm .fg-font-select").each(function () {
            var divId = $(this).attr('id');
            var fontName = $(this).val();
            $("#formHideDom #" + divId).val(fontName);
        });
        $("#themeFontConfigForm .fg-strength-select").each(function () {
            var divId = $(this).attr('id');
            var strengthName = $(this).val();
            $("#formHideDom #" + divId).val(strengthName);
        });
        $("#themeFontConfigForm .italicCheck").each(function () {
            var divId = $(this).attr('id');
            var value = $(this).parent().attr('class');
            if (value == 'checked') {
                $("#formHideDom #" + divId).prop('checked', true);
                $("#formHideDom #" + divId).parent().attr('class', 'checked');
            }
            else {
                $("#formHideDom #" + divId).prop('checked', false);
                $("#formHideDom #" + divId).parent().attr('class', '');
            }
        });
        $("#themeFontConfigForm .ucaseCheck").each(function () {
            var divId = $(this).attr('id');
            var value = $(this).parent().attr('class');
            if (value == 'checked') {
                $("#formHideDom #" + divId).prop('checked', true);
                $("#formHideDom #" + divId).parent().attr('class', 'checked');
            }
            else {
                $("#formHideDom #" + divId).prop('checked', false);
                $("#formHideDom #" + divId).parent().attr('class', '');
            }
        });
        var result = this.initPageDirty();
        if (result == true) {
            FgXmlHttp.post(fontSaveUrl, postData, false);
        }
        $('#defaultConfigFlag').val('0');
    };
    FgConfigFont.prototype.initPageDirty = function () {
        FgDirtyFields.init('themeFontConfigForm', {
            dirtyFieldSettings: {
                denoteDirtyForm: true
            },
            enableDiscardChanges: false,
            initialHtml: false
        });
        return true;
    };
    FgConfigFont.prototype.initPageFns = function (fontSaveUrl) {
        var thisObj = this;
        $("#themeFontConfigForm #save_changes").click(function (event) {
            event.stopPropagation();
            event.stopImmediatePropagation();
            thisObj.saveChanges(fontSaveUrl);
        });
        $("#themeFontConfigForm #reset_changes").click(function (event) {
            event.stopPropagation();
            event.stopImmediatePropagation();
            thisObj.discardChanges(fontSaveUrl);
        });
        this.initPageDirty();
    };
    FgConfigFont.prototype.generateSelectBox = function (fonts, fontSaveUrl) {
        var optionString = '';
        var headLinkStr = '';
        var familyString = '';
        for (var i = 0; i < fonts.length; i++) {
            optionString += "<option style='font-family: " + fonts[i].family + ";' value='" + fonts[i].family + "'>" + fonts[i].family + "</option>";
            familyString += fonts[i].family.replace(/ /g, '+') + ':400|';
            if (i % 100 == 0) {
                $('head').append("<link href='https://fonts.googleapis.com/css?family=" + familyString + "' rel='stylesheet' type='text/css'>");
                familyString = '';
            }
        }
        $('head').append("<link href='https://fonts.googleapis.com/css?family=" + familyString + "' rel='stylesheet' type='text/css'>");
        $('.fg-font-select').html(optionString);
        this.initFonts();
        this.initPageFns(fontSaveUrl);
    };
    FgConfigFont.prototype.changePageTitle = function () {
        $('body').on('click', '.fg-action-editTitle', function () {
            $('.fg-cms-title-change-form').removeClass('has-error');
            $('span.required').remove();
            var titleText = $('.page-title  .page-title-text').html();
            $('#pageTitleChange').val(titleText);
            $('#config-title-change-modal').modal('show');
        });
    };
    FgConfigFont.prototype.savePageTitle = function () {
        $(document).off('click', '#savePopup');
        $(document).on('click', '#savePopup', function () {
            var configId = $('#CMS_FONT_CONFIG_ID').val();
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
    return FgConfigFont;
}());
