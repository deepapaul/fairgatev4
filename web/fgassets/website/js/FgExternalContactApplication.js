var FgExternalContactApp = (function () {
    function FgExternalContactApp() {
        this.handleFormSubmit();
    }
    FgExternalContactApp.prototype.handleFormSubmit = function () {
        $(document).on('click', '.fg-external-contact-form-wrapper .fg-form-element-submit', function () {
            $('.fg-external-contact-form-wrapper .fg-form-element-submit').attr('disabled', 'disabled');
            var formId = $(this).parents('form').attr('id');
            menu = '';
            mainPageId = '';
            var validObj = new FgWebsiteFormValidation(formId, { 'formType': 'contact-form' });
            validObj.validateForm();
        });
    };
    FgExternalContactApp.prototype.renderApplicationForm = function (data) {
        var formOption = data.formOption;
        var dataHtml = FGTemplate.bind('templateContactApplicationFormField', { formDetails: data.formData, defLang: data.defLang, formMessage: formOption, elementId: data.elementId, contactFormOptions: data.contactFormOptions, formType: 'external' });
        $("#external-contact-form").html(dataHtml);
        this.handleFormFields(data);
    };
    FgExternalContactApp.prototype.handleFormFields = function (data) {
        var elementName = 'form_contact_' + data.contactFormOptions.formId;
        var elementId = $('form[name="' + elementName + '"]').attr('id');
        buttonText = $("#" + elementId).find('form input[type=file]').attr('data-buttonText');
        $("#" + elementId + " input:checkbox,#" + elementId + " input:radio").uniform();
        var defaultSettings = {
            language: data.defLang,
            format: FgLocaleSettingsData.jqueryDateFormat,
            autoclose: true,
            weekStart: 1,
            clearBtn: true
        };
        var dateSettings = $.extend(true, {}, defaultSettings);
        $("#" + elementId + " .fg-datepicker1").each(function () {
            startDate = $(this).attr('data-startDate');
            if (startDate != '') {
                dateSettings['startDate'] = startDate;
            }
            endDate = $(this).attr('data-endDate');
            if (endDate != '') {
                dateSettings['endDate'] = endDate;
            }
            $(this).datepicker(dateSettings);
        });
        var nonSelected = $("#" + elementId + " select.bs-select").data('none-selected');
        $("#" + elementId + " .bs-select").selectpicker({
            noneSelectedText: nonSelected,
            countSelectedText: jstranslations.countSelectedText,
            tickIcon: 'fa fa-check'
        }).on('change', function () {
            if ($(this).selectpicker('val') !== '') {
                $(this).closest('.form-group').removeClass('has-error');
                $(this).closest('.form-group').find('.help-block').remove();
            }
        });
        $('.bs-select .glyphicon').removeClass('glyphicon');
        this.handleFormFileUpload(elementId);
        this.handleTimePicker(elementId);
        FgGlobalSettings.handleInputmask();
        var num = new FgNumber({ 'selector': '#' + elementId + ' .selectButton', 'inputNum': '#' + elementId + ' input.input-number' });
        num.init();
        if ($('body').find('.custom-popup').length == 0) {
            $('body').append('<div class="custom-popup"><div class="popover bottom"><div class="arrow"></div><div class="popover-content"></div></div></div>');
        }
        this.handleToolTip(elementId);
        if ($("#" + elementId + " .g-recaptcha").length > 0) {
            $("#" + elementId).find('.fg-form-element-submit').attr('disabled', true);
            var captchaContainer = null;
            var formCaptcha = function () {
                captchaContainer = grecaptcha.render('fg-captcha-' + elementId, {
                    'sitekey': sitekeys,
                    'callback': function (response) {
                        $("#" + elementId).find('.fg-form-element-submit').removeAttr('disabled');
                    }
                });
            };
            setTimeout(function () { formCaptcha(); }, 1000);
        }
    };
    FgExternalContactApp.prototype.handleFormFileUpload = function (elementId) {
        $("#" + elementId + " input[type=file]").each(function () {
            var fieldType = $(this).attr('fieldtype');
            $(this).fileupload({
                dataType: 'json',
                autoUpload: true,
                add: function (e, data) {
                    $(this).parent().find('input[data-file]').val('');
                    $(this).parent().find('.help-block').remove();
                    var itemId = $.now();
                    if (fieldType == 'imageupload') {
                        var acceptFileTypes = /^image\/(gif|jpe?g|png|bmp)$/i;
                        if (data.originalFiles[0]['type'].length && !acceptFileTypes.test(data.originalFiles[0]['type'])) {
                            $(this).parent().find('input[data-file-name]').val(data.originalFiles[0]['name']);
                            $(this).parents('.form-group').addClass('has-error');
                            $(this).parent().find('span.help-block').remove();
                            $(this).parent().append('<span data-file-error class="help-block">' + formMessages.fileType + '</span>');
                            return false;
                        }
                    }
                    if (15728641 < data.files[0].size) {
                        $(this).parents('.form-group').addClass('has-error');
                        $(this).parent().find('span.help-block').remove();
                        $(this).parent().append('<span data-file-error class="help-block">' + $(this).data('exceedmsg') + '</span>');
                        return false;
                    }
                    else {
                        $(this).parents('[data-file-wrap]').find('input[data-file-name]').val(data.files[0].name);
                        $(this).parents('.form-group').removeClass('has-error');
                        $(this).parent().find('#file-error').remove();
                        $("#" + elementId).find('.fg-form-element-submit').attr('disabled', true);
                        var fileName = data.files[0].name;
                        fileName = fileName.replace(/[&\/\\#,+()$~%'"`^=|:;*?<>{}]/g, '');
                        fileName = fileName.replace(/ /g, '-');
                        fileName = itemId + '--' + fileName;
                        data.formData = { title: fileName, nowtime: itemId };
                        var jqXHR = data.submit();
                    }
                },
                done: function (e, data) {
                    var result = data.result;
                    if (result.status == 'success') {
                        $(this).parent().find('input[data-file]').val(data.formData.nowtime + '#-#' + data.formData.title);
                        $("#" + elementId).find('.fg-form-element-submit').removeAttr('disabled');
                    }
                    else {
                        $(this).parents('.form-group').addClass('has-error');
                        $(this).parents('[data-file-wrap]').find('input[data-file-name]').val('');
                        var errorMesg = (result.error == 'INVALID_VIRUS_FILE' || result.error == 'VIRUS_FILE_CONTACT') ? formMessages.virus : formMessages.fileType;
                        $(this).parent().append('<span id="file-error" class="help-block">' + errorMesg + '</span>');
                        $("#" + elementId).find('.fg-form-element-submit').removeAttr('disabled');
                    }
                }
            });
        });
        $("#" + elementId + " .alert .closeIt").click(function () {
            $(this).parent().addClass('hide');
        });
    };
    FgExternalContactApp.prototype.handleTimePicker = function (elementId) {
        var timeFormatData = {};
        timeFormatData['hh:ii'] = { format: 'hh:mm', seperator: ':' };
        timeFormatData['hh.ii'] = { format: 'hh.mm', seperator: '.' };
        timeFormatData['hh ## ii'] = { format: 'hh h mm', seperator: ' h ' };
        timeFormatData['HH:ii P'] = { format: 'hh:mm AA', seperator: ':' };
        var currentTimeFormat = timeFormatData[FgLocaleSettingsData.jqueryDtimeFormat];
        $('#' + elementId + ' [data-timepick]').each(function () {
            var parentDiv = $(this).attr('id');
            $('#' + parentDiv + ' .fg-timepicker').DateTimePicker({
                mode: 'time',
                isPopup: false,
                timeFormat: currentTimeFormat.format,
                setValueInTextboxOnEveryClick: true,
                buttonsToDisplay: [],
                timeSeparator: currentTimeFormat.seperator,
                incrementButtonContent: '<i class="fa fa-angle-up fa-2x"></i>',
                decrementButtonContent: '<i class="fa fa-angle-down fa-2x"></i>',
                parentElement: "#" + parentDiv,
                minuteInterval: 5
            });
        });
    };
    FgExternalContactApp.prototype.handleToolTip = function (elementId) {
        $("#" + elementId + " label span[data-content]").each(function () {
            if ($(this).attr('data-content').trim() != '') {
                $(this).addClass('fg-custom-popovers fg-dotted-br');
            }
        });
        var thisObj = this;
        $('body').on('mouseover click', '.fg-custom-popovers', function (e) {
            var _this = $(this), thisContent = _this.data('content'), posLeft = _this.offset().left - 10, posTop = _this.offset().top + 50;
            thisObj.showTooltip({ element: e, content: thisContent, position: [posLeft, posTop] });
            $('.popover .popover-content').width($('.popover').width() - 27);
        });
        $('body').on('mouseout', '.fg-custom-popovers', function () {
            $('body').find('.custom-popup').hide();
            $('.popover .popover-content').width('');
        });
    };
    FgExternalContactApp.prototype.showTooltip = function (obj) {
        var targetElement = $('body').find('.custom-popup'), elementContent = targetElement.find('.popover-content');
        elementContent.html(obj.content);
        targetElement.css({ 'left': obj.position[0], 'top': obj.position[1] });
        targetElement.show();
    };
    return FgExternalContactApp;
}());
//# sourceMappingURL=FgExternalContactApplication.js.map