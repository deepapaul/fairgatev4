var FormValidation = function () {
    var prevTimeStamp = '';
    // advance validation
    var handleValidation3 = function(formname, savefnname, errorfnname) {
        // for more info visit the official plugin documentation:
        // http://docs.jquery.com/Plugins/Validation

            var form3 = $('#'+formname);
            var error3 = $('.alert-danger', form3);

            form3.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: ".date,.ignore", // validate all fields including form hidden input
                rules: {
                    name: {
                        minlength: 2,
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    options1: {
                        required: true
                    },
                    options2: {
                        required: true
                    },
                    select2tags: {
                        required: true
                    },
                    datepicker: {
                        required: true
                    },
                    occupation: {
                        minlength: 5,
                    },
                    membership: {
                        required: true
                    },
                    service: {
                        required: true,
                        minlength: 2
                    },
                    markdown: {
                        required: true
                    },
                    editor1: {
                        required: true
                    },
                    editor2: {
                        required: true
                    }
                },

                messages: { // custom messages for radio buttons and checkboxes
                    membership: {
                        required: "Please select a Membership type"
                    },
                    service: {
                        required: "Please select  at least 2 types of Service",
                        minlength: jQuery.validator.format("Please select  at least {0} types of Service")
                    }
                    },

                errorPlacement: function (error, element) { // render error placement for each input type
                    if (element.attr("data-error-container")) {
                        error.appendTo(element.attr("data-error-container"));
                    } else if (element.parent(".input-group").size() > 0) {
                        error.insertAfter(element.parent(".input-group"));
                    } else if (element.parents('.radio-list').size() > 0) {
                        error.appendTo(element.parents('.radio-list').attr("data-error-container"));
                    } else if (element.parents('.radio-inline').size() > 0) {
                        error.appendTo(element.parents('.radio-inline').attr("data-error-container"));
                    } else if (element.parents('.checkbox-list').size() > 0) {
                        error.appendTo(element.parents('.checkbox-list').attr("data-error-container"));
                    } else if (element.parents('.checkbox-inline').size() > 0) {
                        error.appendTo(element.parents('.checkbox-inline').attr("data-error-container"));
                    } else {
                        var isAutocompleteField = false;
                        if (element.attr('data-function') == 'autocomplete') {
                            isAutocompleteField = true;
                        }
                        if (isAutocompleteField) {
                            error.insertAfter(element.parent()); // For auto-complete fields, place error outside of auto-complete div.
                        } else {
                            if(element.parents('.sft-field').size() > 0) {
                                error = "";
                            } else {
                                error.insertAfter(element);
                            }
                        }
                    }
                    //Customized for removing the extra span class coming in each input box (Design issue)
                    if ($(formname).hasClass( "errorClass" )) {
                        $("."+errorClass).each(function(){
                            var test = $(this).html();
                            if (test == '') {
                                $(this).remove();
                            }
                        });
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit
                    error3.show();
//                    Metronic.scrollTo(error3, -200);
//                    scroll to top common form error alert on failing validation
                    FgXmlHttp.scrollToErrorDiv();
                    if (errorfnname) {
                        window[errorfnname]();
                    }
                },

                highlight: function (element) { // hightlight error inputs
            
                   $(element)
                        .closest('.fg-form-group, .form-group').addClass('has-error'); // set error class to the control group

                   $(element)
                        .closest('[dataerror-group]').addClass('has-error');
                 },

                unhighlight: function (element) { // revert the change done by hightlight
                    if ($(element).is(':visible')) {
                        $(element).closest('.fg-form-group, .form-group').removeClass('has-error'); // set error class to the control group
                        $(element).closest('[dataerror-group]').removeClass('has-error');
                 }
                },

//                success: function (label) {
//                    label
//                        .closest('.fg-form-group, .form-group').removeClass('has-error'); // set success class to the control group
//                },

                submitHandler: function (form) {
                    var currTimeStamp = $.now()
                    if ((prevTimeStamp == '') || ((currTimeStamp - prevTimeStamp) > 3000)) {
                        prevTimeStamp = currTimeStamp;
                        if (savefnname != '') {
                            error3.hide();
                            window[savefnname]();
                        }
                    }
                }

            });

    }
    setMessages= function(){
        jQuery.extend(jQuery.validator.messages, {
            required: datatabletranslations.VALIDATION_THIS_FIELD_REQUIRED,
            remote: "Please fix this field.",
            email: datatabletranslations.EMAIL_VALIDATION,
            url: datatabletranslations.invalidurl,
            date: "Please enter a valid date.",
            dateISO: "Please enter a valid date (ISO).",
            number: "Please enter a valid number.",
            digits: "Please enter only digits.",
            creditcard: "Please enter a valid credit card number.",
            equalTo: "Please enter the same value again.",
            accept: "Please enter a value with a valid extension.",
            maxlength: jQuery.validator.format("Please enter no more than {0} characters."),
            minlength: jQuery.validator.format("Please enter at least {0} characters."),
            rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
            range: jQuery.validator.format("Please enter a value between {0} and {1}."),
            max: jQuery.validator.format(datatabletranslations.validateMax),
            min: jQuery.validator.format(datatabletranslations.validateMin)
        });
    }

    return {
        //main function to initiate the module
        init: function (formname, savefnname, errorfnname) {
            handleValidation3(formname, savefnname, errorfnname);
            setMessages();
        }
    };

}();