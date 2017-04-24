var FgRegistration = (function () {
    function FgRegistration(validateObj) {
        this.validateObj = {};
        this.validateObj = validateObj;
        this.initTabEvents();
        this.eventNextStepClick();
        this.showHidePassword();
        this.initOrganizationKeyupEvent();
        this.initSameAsAddressEvent();
        this.countryChangeEvent();
    }
    FgRegistration.prototype.switchStepWizard = function (step) {
        if (step === void 0) { step = ''; }
        $('[href="#tab' + step + '"]').tab('show');
    };
    FgRegistration.prototype.initTabEvents = function () {
        $('div[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            var step = $(e.target).attr("data-step");
            var progressWidth = (parseInt(step) * 20) + '%';
            $('div[data-toggle="tab"]').removeClass('active');
            $('[href="' + target + '"]').addClass('active');
            $('#regis-progressbar .progress-bar').css('width', progressWidth);
        });
    };
    FgRegistration.prototype.eventNextStepClick = function () {
        var _self = this;
        $("#regis-form").submit(function (e) {
            e.preventDefault();
            var postData = new FormData($(this)[0]);
            var step = parseInt($('[data-toggle="tab"].active').attr('data-step'));
            var formData = $('#tab' + step + ' input,select', this).serializeArray();
            _self.validate(formData);
            var formURL = 'http://192.168.33.10:8090/register';
            $.ajax({
                url: formURL,
                type: "POST",
                data: postData,
                processData: false,
                success: function (data, textStatus, jqXHR) {
                },
                error: function (jqXHR, textStatus, errorThrown) {
                }
            });
        });
    };
    FgRegistration.prototype.initOrganizationKeyupEvent = function () {
        console.log('1');
        $('#fg_reg_club_name').keyup(function () {
            console.log('2');
            console.log($(this).val());
            $('#fg_reg_corr_org_name').val($(this).val());
        });
    };
    FgRegistration.prototype.countryChangeEvent = function () {
        $('#fg_reg_corr_country, #fg_reg_bill_country').change(function () {
            var regionFieldId = $(this).attr('id') == 'fg_reg_corr_country' ? 'fg_reg_corr_region' : 'fg_reg_bill_region';
            var wrapperDiv = $('#' + regionFieldId).closest('.fg-region-wrapper');
            if ($(this).val() == '7') {
                var inputField = FGTemplate.bind('regionInputFieldTemplate', { fieldId: regionFieldId });
                wrapperDiv.html(inputField);
            }
            else {
                var selectOption = FGTemplate.bind('regionSelectFieldTemplate', { fieldId: regionFieldId, data: countries[$(this).val()] });
                wrapperDiv.html(selectOption);
                $('#' + regionFieldId).selectpicker('refresh');
            }
        });
    };
    FgRegistration.prototype.initSameAsAddressEvent = function () {
        $('#fg_reg_same_as').click(function () {
            var billInputFields = ['fg_reg_bill_org_name', 'fg_reg_bill_co', 'fg_reg_bill_street', 'fg_reg_bill_postalcode', 'fg_reg_bill_city'];
            var corrInputFields = ['fg_reg_corr_org_name', 'fg_reg_corr_co', 'fg_reg_corr_street', 'fg_reg_corr_postalcode', 'fg_reg_corr_city'];
            var billSelectFields = ['fg_reg_bill_country', 'fg_reg_bill_region', 'fg_reg_bill_lang'];
            var corrSelectFields = ['fg_reg_corr_country', 'fg_reg_corr_region', 'fg_reg_corr_lang'];
            var allBillFields = billInputFields.concat(billSelectFields);
            if ($(this).prop('checked')) {
                $.each(billInputFields, function (i, bField) {
                    $('#' + bField).val($('#' + corrInputFields[i]).val());
                });
                $.each(billSelectFields, function (i, bField) {
                    $('#' + bField).val($('#' + corrSelectFields[i]).val());
                    $('#' + bField).selectpicker('refresh');
                });
                $.each(allBillFields, function (i, field) {
                    $('#' + field).attr('disabled', true);
                });
            }
            else {
                $.each(allBillFields, function (i, field) {
                    $('#' + field).attr('disabled', false);
                });
            }
        });
    };
    FgRegistration.prototype.validate = function (data) {
        $.each(data, function (i, d) {
            var input = $("[name=" + d.name + "]");
            var formGroup = input.closest('.form-group');
            formGroup.removeClass('has-error');
            formGroup.find('.help-block').addClass('hide');
            var required = input.attr('required');
            if (d.value == "" && (typeof required !== 'undefined')) {
                formGroup.addClass('has-error');
                formGroup.find('.help-block').removeClass('hide');
            }
        });
        var step = parseInt($('[data-toggle="tab"].active').attr('data-step'));
        if (step == 5) {
            if (!this.validateEmail($('#fg_reg_email').val())) {
                return false;
            }
            var res = this.validateObj.validate($('#fg_reg_password').val());
            if (!res.status) {
                return false;
            }
            if ($('#fg_reg_password').val() !== $('#fg_reg_password').val()) {
                return false;
            }
            console.log(res);
        }
    };
    FgRegistration.prototype.showHidePassword = function () {
        $('.fg-show-password').click(function () {
            if ($(this).siblings("input").attr('type') == 'password') {
                $(this).siblings("input").attr('type', 'text');
            }
            else {
                $(this).siblings("input").attr('type', 'password');
            }
        });
    };
    FgRegistration.prototype.validateEmail = function (email) {
        if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
            return true;
        }
        return false;
    };
    return FgRegistration;
}());
