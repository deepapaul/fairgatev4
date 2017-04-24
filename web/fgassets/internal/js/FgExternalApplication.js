

/*
 * XmlHttp wrapper class for external apllication
 */
FgXmlHttp = {
    //function added to focus to error element if exists or to common form error on failing validation
    scrollToErrorDiv: function (element) {
        var focusPos = 100;
        if (typeof element !== 'undefined') {
            if ($(element).length > 0) {
                focusPos = $(element).offset().top;
            }
        } else {
            if ($('.alert-danger').length > 0) {
                focusPos = $('.alert-danger').offset().top;
            }
        }
        //padding & margin is to be subtracted for better view
        focusPos = ((focusPos - 60) > 0) ? (focusPos - 60) : focusPos;
        $('html, body').animate({
            scrollTop: focusPos}, 'fast'
                );
    },
    //wrapper function $.post()
    post: function (url, data, replacediv, successCallback, failCallback, isReplaceContent) {
        if (!isReplaceContent)
            isReplaceContent = 1;
        Metronic.startPageLoading();
        $.post(url, data, function (result) {
            if (result.status) {
                if (result.redirect) {
                    if (result.sync) {
                        Metronic.stopPageLoading();
                        document.location = result.redirect;
                        if (result.flash)
                            FgExternalAppl.showToastr(result.flash);
                    } else {
                        FgXmlHttp.replaceContentFromUrl(result.redirect, result.flash, successCallback, result);
                    }
                } else {
                    if (result.noparentload) {
                        Metronic.stopPageLoading();
                        if (result.flash) {
                            FgExternalAppl.showToastr(result.flash);
                        }
                        if (successCallback && !result.errorArray) {
                            successCallback.call({}, result);
                        }
                        if (failCallback) {
                            failCallback.call({}, result);
                        }
                    } else {
                        FgXmlHttp.replaceContentFromUrl(document.location.href, result.flash, successCallback, result);
                    }
                }

            } else {
                if (isReplaceContent === 1) {
                    if (replacediv)
                        $(replacediv).html(result);
                    else {
                        $('#fg-wrapper').html(result);
                    }
                }
                if (successCallback && !result.errorArray) {
                    successCallback.call({}, result);
                }
                if (failCallback) {
                    failCallback.call({}, result);
                }
//                scroll to top common form error alert on failing validation
                FgXmlHttp.scrollToErrorDiv();
                Metronic.stopPageLoading();
            }
        });
        
    },
    //replaceContentFromUrl wrapper
    replaceContentFromUrl: function (url, flashmsg, callback, callbackdata) {
        $.ajax({
            url: url,
            data: {
                silent: 1
            }, 
            success: function (data) {
                Metronic.stopPageLoading();
                $('#fg-wrapper').html(data);
                if (flashmsg)
                    FgExternalAppl.showToastr(flashmsg);
                if (callback)
                    callback.call({}, callbackdata);
            }
        });
    }
    
};
FgExternalAppl = {
    /* For showing toaster notification */
    showToastr: function (msg, type, title) {
        var toastrType = 'success';
        if (type)
            toastrType = type;
        toastr.options = {
            positionClass: 'toast-top-center'
        };
        toastr[toastrType](msg, title);

    },
    handleDatepicker: function (extraSettings) {
        var defaultSettings = {
            language: jstranslations.localeName,
            format: FgLocaleSettingsData.jqueryDateFormat,
            autoclose: true,
            weekStart: 1,
            clearBtn: true
        };
        var dateSettings = $.extend(true, {}, defaultSettings, extraSettings);
        $('.fg-date').datepicker(dateSettings);
    }
};

//To handle exist email error message
$(document).on('keyup', '.fg-ext-email-input', function (e) {

    if ($('#email-exist-error').is(':visible'))
    {
        $('#email-exist-error').hide();
    }

});
//To handle membership select in external form application
$(document).on('change', 'form#externalApplication select', function (e) {

    if ($('#Fedmembership-error').is(':visible')) {
        $(".fg-ext-membership-block, .fg-ext-membership-select").removeClass('has-error');
        $('#Fedmembership-error').hide();
    }

});


//To handle employer radio button click in external form application
$(document).on('click', '#employer', function () {
    $("#employer-other-text").prop('required', false);    
    $("#employer-number-text").prop('required', true);    
    $(".fg-employer-other, .fg-ext-employer-other-block").parents('.form-group').removeClass('has-error').find('#employer-other-text-error').remove();
    $("#employer-other-text").val('');
});

$(document).on('click', '#other', function () {
    $("#employer-other-text").prop('required', true);    
    $("#employer-number-text").prop('required', false).val('');
    $(".fg-ext-employer-block").parents('.form-group').removeClass('has-error').find('#employer-number-text-error').remove();
    
});

//To handle employer other text box in external form application
$(document).on('keyup', '#employer-other-text', function () {
    $('#employer').prop('checked', false);   
    $(".uniform").uniform();
    $('#other').prop('checked', true); 
    $("#employer-other-text").prop('required', true);    
    $("#employer-number-text").prop('required', false).val('');
    $(".fg-ext-employer-block").parents('.form-group').removeClass('has-error').find('#employer-number-text-error').remove();    
});

//To handle employer other text box in external form application
$(document).on('keyup', '#employer-number-text', function () {
    $('#employer').prop('checked', true);   
    $(".uniform").uniform();
    $('#other').prop('checked', false); 
    $("#employer-other-text").prop('required', false).val('');
    $("#employer-number-text").prop('required', true);    
    $(".fg-employer-other, .fg-ext-employer-other-block").parents('.form-group').removeClass('has-error').find('#employer-other-text-error').remove();    
});