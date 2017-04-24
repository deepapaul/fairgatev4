var FgContactFormFields = (function () {
    function FgContactFormFields(meta, options) {
        this.defaultsettings = {
            stage1savepath: '',
            stage2savepath: '',
            stage3savepath: '',
            formEvent: '',
            meta: {
                correspondanceLangId: '',
                mandatoryFieldId: ''
            }
        };
        this.sortOrder = 1;
        this.setElementAddEvents();
        this.formId = meta.formId;
        this.event = meta.event;
        this.clubLanguages = meta.clubLanguages;
        this.clubDefaultLanguage = meta.clubDefaultLang;
        this.currentStage = (meta.currentStage > 0) ? meta.currentStage : 1;
        this.connectButtonClick();
        this.setFormElementSortable();
        this.setFormElementRemove();
        this.setElementAddOptionValue();
        this.connectStageClick();
        this.connectDatepickerClick();
        this.settings = $.extend(true, {}, this.defaultSettings, options);
        this.settings.contactfields = _.groupBy(this.settings.contactfields, 'selectgroup');
    }
    FgContactFormFields.prototype.connectButtonClick = function () {
        var _this = this;
        $('#form_element_save').off('click');
        $('#form_element_save').on('click', function () {
            if ($(this).attr('disabled') == 'disabled')
                return;
            if (_this.getCurrentStage() == 1) {
                _this.saveWizardStage1(false);
            }
            else if (_this.getCurrentStage() == 2) {
                _this.saveWizardStage2(false);
            }
            else if (_this.getCurrentStage() == 3) {
                _this.saveWizardStage3(false);
            }
        });
        $('#form_element_back').on('click', function () {
            if ($(this).attr('disabled') == 'disabled')
                return;
            if (_this.getCurrentStage() == 3) {
                $('.nav-pills li:eq(1) a').tab('show');
                _this.getStage2Data();
            }
            else if (_this.getCurrentStage() == 2) {
                $('.nav-pills li:eq(0) a').tab('show');
                _this.getStage1Data();
            }
        });
        $('#form_element_save_and_next').on('click', function () {
            if ($(this).attr('disabled') == 'disabled')
                return;
            if (_this.getCurrentStage() == 1) {
                _this.saveWizardStage1(true);
            }
            else if (_this.getCurrentStage() == 2) {
                _this.saveWizardStage2(true);
            }
        });
        $('#form_element_finish').on('click', function () {
            if ($(this).attr('disabled') == 'disabled')
                return;
            _this.saveWizardStage3(true);
        });
        $('#form_element_discard').on('click', function () {
            if ($(this).attr('disabled') == 'disabled')
                return;
            if (_this.getCurrentStage() == 1) {
                _this.getStage1Data();
            }
            else if (_this.getCurrentStage() == 2) {
                _this.getStage2Data();
            }
            else if (_this.getCurrentStage() == 3) {
                _this.getStage3Data();
            }
        });
    };
    FgContactFormFields.prototype.connectStageClick = function () {
        var _this = this;
        $('ul.steps li').on('click', function () {
            var stage = $(this).attr('data-target');
            if ($(this).hasClass('disabled'))
                return false;
            if (stage == 'form-stage1') {
                _this.getStage1Data();
            }
            else if (stage == 'form-stage2') {
                _this.getStage2Data();
            }
            else if (stage == 'form-stage3') {
                _this.getStage3Data();
            }
        });
    };
    FgContactFormFields.prototype.connectDatepickerClick = function () {
        $('#formFields').on('click', '.fa-calendar', function () {
            $(this).parent().parent().find('input').datepicker('show');
        });
    };
    FgContactFormFields.prototype.getCurrentStage = function () {
        return this.currentStage;
    };
    FgContactFormFields.prototype.setCurrentStage = function (stage) {
        this.currentStage = stage;
        return;
    };
    FgContactFormFields.prototype.setElementAddEvents = function () {
        var _this = this;
        $('#addFormField, #addSeperator,#addContactField, #addMembership').click(function () {
            var clickedLinkId = $(this).attr('id');
            var fieldType;
            var randomId = 'new' + parseInt(Math.random() * Math.pow(10, 5));
            var sortOrder = $('#formFields ul>li.list-group-item').length + 1;
            switch (clickedLinkId) {
                case "addContactField":
                    fieldType = 'contactfield';
                    randomId = 'newSys' + parseInt(Math.random() * Math.pow(10, 5));
                    break ;
                case "addMembership":
                    fieldType = 'membership';
                    $("#addMembership").addClass('fg-disabled-link');
                    randomId = 'newClubMembershipSelection';
                    break;
                case "addFormField":
                    fieldType = 'default';
                    break;
                default:
                    fieldType = 'heading';
                    break;
            }
            var intialData = [
                {
                    'fieldType': fieldType,
                    'formElementSortOrder': sortOrder,
                    'formFieldId': randomId,
                    'formId': _this.formId,
                    'source': 'new',
                    'clubMembershipSelection': ''
                }
            ];
            _this.loadFormFields(intialData);
            $('.fg-dev-attribute').each(function () {
                $("select.fg_dev_contact_field").find('option[value=' + $(this).val() + ']').hide();
                $("select.fg_dev_contact_field").selectpicker('refresh');
            });
            if (clickedLinkId == 'addMembership') {
                FgColumnSettings.handleSelectPicker();
            }
            _this.showTranslation();
            _this.reorderElementList($('#formFields>li'), 'fieldSort');
            $.fn.dirtyFields.updateFormState($("#form-field-elements-form-stage1"));
        });
    };
    FgContactFormFields.prototype.setElementAddOptionValue = function () {
        var _this = this;
        $('body').on('click', '.add_field_option_value', function () {
            var clickedLink = $(this).attr('data-field');
            var randomId = 'new' + parseInt(Math.random() * Math.pow(10, 5));
            var sortOrder = $('#formElement_value_' + clickedLink + '>li.list-group-item').length + 1;
            var JOSN = '{ "formFieldId": "' + clickedLink + '","options":{"' + randomId + '":{"isDeleted" : 0,"isActive":1}} }';
            var newRow = _this.getOptionValuesHtml(JSON.parse(JOSN), randomId);
            $('ul#formElement_value_' + clickedLink).append(newRow);
            _this.reorderElementList($('ul#formElement_value_' + clickedLink + '>li'), 'optionSort');
            $.fn.dirtyFields.updateFormState($("#form-field-elements-form-stage1"));
        });
    };
    FgContactFormFields.prototype.setElementInitialOptionValue = function (data) {
        var randomId = 'new' + _.random(0, 99999);
        var JOSN = '{ "formFieldId": "' + data.formFieldId + '","options":{"' + randomId + '":{"isDeleted" : 0,"isActive":1}} }';
        return this.getOptionValuesHtml(JSON.parse(JOSN), randomId);
    };
    FgContactFormFields.prototype.loadWizardStage1 = function (formData) {
        this.setCurrentStage(1);
        $('#form-stage-progressbar .progress-bar').css('width', '33%');
        $('#formFields-button').html('');
        $('#formFields').html('');
        this.loadFormName(formData['name']);
        this.loadMandatoryfields();
        if ($('#formwizard_event').val() == 'create') {
            var buttonData = {
                'fieldType': 'button',
                'formFieldId': 'new' + parseInt(Math.random() * Math.pow(10, 5)),
                'formId': this.formId,
                'source': 'new'
            };
            var formHtml = this.getFieldRowHtml(buttonData);
            if ($('#formFields-button input').length == 0) {
                $('#formFields-button').html(formHtml);
            }
        }
        FormValidation.init('form-field-elements-form-stage1');
        this.setTranslationTabError('form-field-elements-form-stage1');
        this.renderFormButtons();
        this.setStepTitle();
        this.setlanguageSwitchActive();
    };
    FgContactFormFields.prototype.loadFormName = function (formName) {
        $('#formname').val(formName);
    };
    FgContactFormFields.prototype.loadFormFields = function (formElementArray) {
        var _this = this;
        var formHtml = '';
        _.each(formElementArray, function (data) {
            if ($.inArray(data.fieldType, ["captcha"]) == -1) {
                formHtml = _this.getFieldRowHtml(data);
                var elementIdArray = $(formHtml).attr('id').split('_');
                switch (data.fieldType) {
                    case 'button':
                        $('#formFields-button').html(formHtml);
                        break;
                    case 'contactfield':
                        $('#formFields').append(formHtml);
                        $('select.fg_dev_contact_field').each(function () {
                            var selectedValue = $(this).val();
                            if (selectedValue !== 'default') {
                                $('select.fg_dev_contact_field').not(this).find('option[value="' + selectedValue + '"]').hide();
                            }
                        });
                        _this.setEvents(elementIdArray[1]);
                        _this.setFieldChangeEvent(elementIdArray[1]);
                        break;
                    case "membership":
                        $('#formFields').append(formHtml);
                        _this.setEvents(elementIdArray[1]);
                        _this.setFieldChangeEvent(elementIdArray[1]);
                        break;
                    default:
                        $('#formFields').append(formHtml);
                        _this.setEvents(elementIdArray[1]);
                        _this.setFieldChangeEvent(elementIdArray[1]);
                        break;
                }
            }
        });
    };
    FgContactFormFields.prototype.setFormElementRemove = function () {
        var _this = this;
        $('#formFields').on('click', '.fg-delete-row', function () {
            var source = $(this).parent('li').attr('data-source');
            if ($(this).attr("element_type") && $(this).attr("element_type") == 'mandatory') {
                return;
            }
            if ($(this).attr("element_type") && $(this).attr("element_type") == 'CF') {
                var deletedSelectBoxVal = $(this).parent().find('select.fg_dev_contact_field').val();
                $('select.fg_dev_contact_field option[value="' + deletedSelectBoxVal + '"]').show();
            }
            if (source == 'new') {
                FgDirtyFields.removeFields($(this).parent('li'));
                $(this).parent('li').remove();
            }
            else {
                if ($(this).find('input.make-switch:checked').length == 0) {
                    $(this).parent('li').removeClass('inactiveblock');
                    $(this).parent('li').find('input[data-required="true"]').attr('required', 'required');
                }
                else {
                    $(this).parent('li').addClass('inactiveblock');
                    $(this).parent('li').find('input[data-required="true"]').removeAttr('required').parent().removeClass('has-error').find('.help-block').remove();
                }
            }
            if ($(this).attr("element_type") && $(this).attr("element_type") == 'Membership') {
                if ($(".fg-member-hiden-selection").length == 0) {
                    $("#addMembership").removeClass('fg-disabled-link');
                }
            }
            _this.reorderElementList($(this).closest('ul').children('li.list-group-item'), $(this).hasClass('optionDelete') ? 'optionSort' : 'fieldSprt');
        });
    };
    FgContactFormFields.prototype.isUndefined = function (data, keys) {
        try {
            var dataArray = data;
            keys.forEach(function (value, key) {
                dataArray = dataArray[value];
            });
            return 0;
        }
        catch (err) {
            return true;
        }
    };
    FgContactFormFields.prototype.renderOptionHtml = function (data) {
        return this.getOptionHtml(data);
    };
    FgContactFormFields.prototype.saveWizardStage1 = function (next) {
        if (this.validateWizardStage1()) {
            var formData = this.getFormData();
            this.saveStage1(formData, next);
        }
    };
    FgContactFormFields.prototype.getOptionValuesHtml = function (data, key) {
        var templateId = 'form_field_templates_add_value';
        data.clubDefaultLanguage = this.clubDefaultLanguage;
        data.clubLanguages = this.clubLanguages;
        data.formId = this.formId;
        var options = data.options;
        var optionsArr = new Array();
        _.each(options, function (val) {
            optionsArr.push(val);
        });
        optionsArr = optionsArr.sort(function (a, b) {
            return a.sortOrder > b.sortOrder;
        });
        var count = 0;
        $.each(data.options, function (idx, obj) {
            data.options[idx] = optionsArr[count];
            count = count + 1;
        });
        var htmlToBeRendered = FGTemplate.bind(templateId, { "data": data, 'key': key });
        return htmlToBeRendered;
    };
    FgContactFormFields.prototype.getStage1Data = function () {
        var _this = this;
        FgUtility.startPageLoading();
        $.ajax({
            type: "POST",
            url: formDataPath,
            data: { 'stage': '1', 'formId': _this.formId },
            success: function (response) {
                FgUtility.stopPageLoading();
                if (response.error == null) {
                    _this.settings.mandatoryFieldId = response.meta.mandatoryFieldsId;
                    _this.loadWizardStage1Edit(response.form.stage1.form);
                    $('.has-needed').uniform();
                    _this.setWizardStage(response.meta.formStage);
                    if ($("select.fg_dev_membership_field").length > 0) {
                        $("#addMembership").addClass('fg-disabled-link');
                    }
                    else {
                        $("#addMembership").removeClass('fg-disabled-link');
                    }
                    if ($(".fg-dev-hide-membership").is(':checked')) {
                        $("#fg-selcted-membership").html($(".fg-dev-hide-membership-selction").find('option:selected').text());
                        $(".fg-hide-membeship-selection").hide();
                        $(".fg-member-hiden-selection").show();
                        $(".fg-member-default").hide();
                        $(".membership-type-label").addClass('col-sm-offset-2');
                    }
                    FgTooltip.init();
                }
            },
            dataType: 'json'
        });
    };
    FgContactFormFields.prototype.getStage2Data = function () {
        var _this = this;
        FgUtility.startPageLoading();
        $.ajax({
            type: "POST",
            url: formDataPath,
            data: { 'stage': '2', 'formId': _this.formId },
            success: function (response) {
                if (response.error == null) {
                    _this.loadWizardStage2(response);
                    FgUtility.stopPageLoading();
                    _this.setWizardStage(response.meta.formStage);
                }
            },
            dataType: 'json'
        });
    };
    FgContactFormFields.prototype.loadWizardStage2 = function (formData) {
        var _this = this;
        _this.setCurrentStage(2);
        $('#form-stage-progressbar .progress-bar').css('width', '66%');
        var data = {
            'formId': _this.formId,
            'form': formData.form.stage2,
            'clubDefaultLang': formData.meta.clubDefaultLang,
            'clubLanguages': formData.meta.clubLanguages,
            'hasAdminRights': formData.meta.hasAdminRights,
            'editSignaturePath': formData.meta.editSignaturePath,
            'formStage': formData.meta.formStage,
            'defaultTranslations': formData.meta.defaultTranslations
        };
        var htmlToBeRendered = FGTemplate.bind('formElementStage2', data);
        $('#formelement-stage2').html(htmlToBeRendered);
        _this.handleReply('formelement_stage2');
        _this.handleReply('formelement_stage2_acceptance');
        _this.handleReply('formelement_stage2_dismissal');
        _this.handleDeactivateMail(formData.meta.clubLanguages, formData.meta.clubDefaultLang, 'formelement_stage2_acceptance');
        _this.handleDeactivateMail(formData.meta.clubLanguages, formData.meta.clubDefaultLang, 'formelement_stage2_dismissal');
        _this.connectCKEditorStage2(formData.meta.clubLanguages, 'formelement_stage2_content');
        _this.connectCKEditorStage2(formData.meta.clubLanguages, 'formelement_stage2_acceptance_content');
        _this.connectCKEditorStage2(formData.meta.clubLanguages, 'formelement_stage2_dismissal_content');
        if (formData.meta.formStage == 'stage1') {
            _this.handleCKEditorStage2Default(formData.meta.clubLanguages, formData.meta.defaultTranslations);
        }
        _this.connectAutocomplete(formData.form.stage2.recipientlist);
        FormValidation.init('form-field-elements-form-stage2');
        _this.renderFormButtons();
        $('#formelement_stage2_senderemail').trigger('change');
        this.setTranslationTabError('form-field-elements-form-stage2');
        this.setStepTitle();
        this.setlanguageSwitchActive();
    };
    FgContactFormFields.prototype.handleCKEditorStage2Default = function (clubLanguages, defaultTranslations) {
        _.each(clubLanguages, function (lang, key) {
            CKEDITOR.instances['formelement_stage2_content_' + lang].setData(defaultTranslations[lang]['confirmationMailContent']);
            CKEDITOR.instances['formelement_stage2_acceptance_content_' + lang].setData(defaultTranslations[lang]['acceptanceMailContent']);
            CKEDITOR.instances['formelement_stage2_dismissal_content_' + lang].setData(defaultTranslations[lang]['dismissalMailContent']);
        });
    };
    FgContactFormFields.prototype.handleReply = function (elementPrefix) {
        $("#" + elementPrefix + "_reply").uniform();
        $("#" + elementPrefix + "_reply").on('click', function () {
            var checked = $(this).is(':checked');
            if (checked) {
                $('#' + elementPrefix + '_senderemail').attr('disabled', 'disabled');
                $('#' + elementPrefix + '_senderemail').val($('#' + elementPrefix + '_senderemail_default').html()).trigger('change');
            }
            else {
                $('#' + elementPrefix + '_senderemail').removeAttr('disabled');
                $('#' + elementPrefix + '_senderemail').val('');
            }
        });
    };
    FgContactFormFields.prototype.handleDeactivateMail = function (clubLanguages, clubDefaultLang, elementPrefix) {
        $("#" + elementPrefix + "_is_active_mail").uniform();
        $("#" + elementPrefix + "_is_active_mail").on('click', function () {
            var checked = $(this).is(':checked');
            if (checked) {
                $('#' + elementPrefix + '_senderemail').attr('disabled', 'disabled');
                //$('#' + elementPrefix + '_senderemail').val('');
                $('#' + elementPrefix + '_senderemail').removeAttr('required');
                $('#' + elementPrefix + '_reply').attr('disabled', 'disabled');
                //$('#' + elementPrefix + '_reply').prop('checked', false);
                $('#' + elementPrefix + '_subject_' + clubDefaultLang).removeAttr('required');
                $('#' + elementPrefix + '_content_' + clubDefaultLang).removeAttr('required');
                $.uniform.update('#' + elementPrefix + '_reply');
                _.each(clubLanguages, function (lang, key) {
                    $('#' + elementPrefix + '_subject_' + lang).attr('disabled', 'disabled');
                    //$('#' + elementPrefix + '_subject_' + lang).val('');
                    CKEDITOR.instances[elementPrefix + '_content_' + lang].setReadOnly(true);
                    //CKEDITOR.instances[elementPrefix + '_content_' + lang].setData('');
                });
            }
            else {
                $('#' + elementPrefix + '_senderemail').removeAttr('disabled');
                $('#' + elementPrefix + '_senderemail').attr('required', true);
                $('#' + elementPrefix + '_reply').removeAttr('disabled');
                $('#' + elementPrefix + '_subject_' + clubDefaultLang).attr('required', true);
                $('#' + elementPrefix + '_content_' + clubDefaultLang).attr('required', true);
                $.uniform.update('#' + elementPrefix + '_reply');
                _.each(clubLanguages, function (lang, key) {
                    $('#' + elementPrefix + '_subject_' + lang).removeAttr('disabled');
                    $('#' + elementPrefix + '_content_' + lang).removeAttr('disabled');
                    CKEDITOR.instances[elementPrefix + '_content_' + lang].setReadOnly(false);
                });
            }
        });
    };
    FgContactFormFields.prototype.getStage3Data = function () {
        var _this = this;
        FgUtility.startPageLoading();
        $.ajax({
            type: "POST",
            url: formDataPath,
            data: { 'stage': '3', 'formId': _this.formId },
            success: function (response) {
                if (response.error == null) {
                    _this.loadWizardStage3(response);
                    FgUtility.stopPageLoading();
                    _this.setWizardStage(response.meta.formStage);
                }
            },
            dataType: 'json'
        });
    };
    FgContactFormFields.prototype.loadWizardStage3 = function (formData) {
        var _this = this;
        $('#formelement-stage2').html('');
        _this.setCurrentStage(3);
        $('#form-stage-progressbar .progress-bar').css('width', '100%');
        var data = {
            'formId': _this.formId,
            'form': formData.form.stage3,
            'clubDefaultLang': formData.meta.clubDefaultLang,
            'clubLanguages': formData.meta.clubLanguages
        };
        var htmlToBeRendered = FGTemplate.bind('formElementStage3', data);
        $('#formelement-stage3').html(htmlToBeRendered);
        FormValidation.init('form-field-elements-form-stage3');
        _this.renderFormButtons();
        $('#form-field-elements-form-stage3 textarea:first').trigger('change');
        if ($('#form-field-elements-form-stage3 textarea[data-set-value]').length > 0) {
            var defaultValue = $('#form-field-elements-form-stage3 textarea[data-set-value]').val();
            $('#form-field-elements-form-stage3 textarea[data-set-value]').val(defaultValue.replace(' ', '')).trigger('change');
        }
        this.setTranslationTabError('form-field-elements-form-stage3');
        this.setStepTitle();
        this.setlanguageSwitchActive();
    };
    FgContactFormFields.prototype.saveWizardStage2 = function (next) {
        FgUtility.startPageLoading();
        if (this.validateWizardStage2()) {
            this.saveStage2(next);
        }
        else {
            FgUtility.stopPageLoading();
        }
    };
    FgContactFormFields.prototype.saveWizardStage3 = function (finish) {
        FgUtility.startPageLoading();
        if (this.validateWizardStage3()) {
            this.saveStage3(finish);
        }
        else {
            FgUtility.stopPageLoading();
        }
    };
    FgContactFormFields.prototype.getFieldRowHtml = function (data, formElementId) {
        var templateId;
        switch (data.fieldType) {
            case 'button':
                templateId = 'form_field_templates_button';
                break;
            case 'contactfield':
                templateId = 'newContactFormElement';
                if (typeof formElementId !== 'undefined' && formElementId != '') {
                    templateId = 'editContactFormElement';
                }
                data.fieldArray = this.settings.contactfields;
                break;
            case 'membership':
                templateId = 'newMembershipElement';
                data.membershipArray = this.settings.clubmembership;
                break;
            default:
                templateId = 'newFormElement';
                break;
        }
        data.clubDefaultLanguage = this.clubDefaultLanguage;
        data.clubLanguages = this.clubLanguages;
        var htmlToBeRendered = FGTemplate.bind(templateId, { "data": data });
        return htmlToBeRendered;
    };
    FgContactFormFields.prototype.getOptionHtml = function (data) {
        var templateId = 'form_field_templates_' + data.fieldType;
        data.clubDefaultLanguage = this.clubDefaultLanguage;
        data.clubLanguages = this.clubLanguages;
        var htmlToBeRendered = FGTemplate.bind(templateId, { "data": data });
        return htmlToBeRendered;
    };
    FgContactFormFields.prototype.setEvents = function (elementId) {
        this.setUIPlugins(elementId);
    };
    FgContactFormFields.prototype.setFieldChangeEvent = function (fieldId) {
        var _this = this;
        $("body").off('change', 'select.fg-dev-form-field');
        $("body").on('change', 'select.fg-dev-form-field', function () {
            {
                var formData = {};
                fieldId = $(this).attr('fieldId');
                formData = _this.formFieldParse('formElement_' + fieldId);
                formData[_this.formId][fieldId]['formId'] = _this.formId;
                formData[_this.formId][fieldId]['formFieldId'] = fieldId;
                formData[_this.formId][fieldId]['clubLanguages'] = _this.clubLanguages;
                formData[_this.formId][fieldId]['clubDefaultLanguage'] = _this.clubDefaultLanguage;
                var postData = formData[_this.formId][fieldId];
                var templateId = 'form_field_templates_' + postData.fieldType;
                $("#typeOptions-" + fieldId).html(FGTemplate.bind(templateId, { 'data': postData }));
                if (postData.fieldType == 'default') {
                    $('#typeOptions_' + fieldId + '_container').addClass('hide');
                    $('#isActive_' + fieldId + '_container').addClass('hide');
                }
                else {
                    $('#typeOptions_' + fieldId + '_container').removeClass('hide');
                    $('#isActive_' + fieldId + '_container').removeClass('hide');
                }
                _this.setEvents(fieldId);
                _this.showTranslation();
            }
        });
        $("body").off('change', 'select.fg_dev_contact_field');
        $("body").on('change', 'select.fg_dev_contact_field', function () {
            var formData = {};
            fieldId = $(this).attr('fieldId');
            formData = _this.formFieldParse('formElement_' + fieldId);
            formData[_this.formId][fieldId]['formId'] = _this.formId;
            formData[_this.formId][fieldId]['formFieldId'] = fieldId;
            formData[_this.formId][fieldId]['clubLanguages'] = _this.clubLanguages;
            formData[_this.formId][fieldId]['clubDefaultLanguage'] = _this.clubDefaultLanguage;
            var postData = formData[_this.formId][fieldId];
            postData.elementId = $(this).val();
            var hideItem = $(this).val();
            postData.elementType = $('option:selected', this).attr('opt_type');
            if (hideItem != 'default') {
                $("select.fg_dev_contact_field").not(this).each(function () {
                    $(this).find('option[value="' + hideItem + '"]').hide();
                    $(this).selectpicker('refresh');
                });
            }
            var elementType = _this.getContactFieldTemplate(postData.elementType);
            postData.elementType = elementType;
            var templateId = 'form_field_templates_' + postData.elementType;
            if (postData.attributeId != 'default') {
                $("#typeOptions-" + fieldId).html(FGTemplate.bind(templateId, { 'data': postData }));
            }
            if (postData.attributeId == 'default') {
                $('#typeOptions_' + fieldId + '_container').addClass('hide');
                $('#isActive_' + fieldId + '_container').addClass('hide');
            }
            else {
                $('#typeOptions_' + fieldId + '_container').removeClass('hide');
                $('#isActive_' + fieldId + '_container').removeClass('hide');
            }
            _this.setContactFieldEvents(fieldId, postData.elementType);
            _this.showTranslation();
        });
        $("body").off('change', 'select.fg_dev_membership_field');
        $("body").on('change', 'select.fg_dev_membership_field', function () {
            var formData = {};
            fieldId = $(this).attr('fieldId');
            formData = _this.formFieldParse('formElement_' + fieldId);
            formData[_this.formId][fieldId]['formId'] = _this.formId;
            formData[_this.formId][fieldId]['formFieldId'] = fieldId;
            formData[_this.formId][fieldId]['clubLanguages'] = _this.clubLanguages;
            formData[_this.formId][fieldId]['clubDefaultLanguage'] = _this.clubDefaultLanguage;
            var postData = formData[_this.formId][fieldId];
            postData.elementId = $(this).val();
            postData.membershipArray = _this.settings.clubmembership;
            var hideItem = $(this).val();
            var templateId = 'form_field_templates_membership';
            $("#typeOptions-" + fieldId).html(FGTemplate.bind(templateId, { 'data': postData }));
            $("#membershiphide_" + fieldId).selectpicker();
            if (postData.elementType == 'membership') {
                $('#typeOptions_' + fieldId + '_container').addClass('hide');
                $('#isActive_' + fieldId + '_container').addClass('hide');
            }
            else {
                $('#typeOptions_' + fieldId + '_container').removeClass('hide');
                $('#isActive_' + fieldId + '_container').removeClass('hide');
            }
            _this.setEvents(fieldId);
            _this.showTranslation();
        });
        $('body').off('change', 'select.fg-dev-hide-membership-selction');
        $('body').on('change', 'select.fg-dev-hide-membership-selction', function () {
            if ($(".fg-dev-hide-membership").is(':checked') && $(this).find('option:selected').val() != 'none') {
                if ($(this).find('option:selected').val() != 'none') {
                    $("#fg-selcted-membership").html($(this).find('option:selected').text());
                }
                $(".fg-hide-membeship-selection").hide();
                $(".fg-member-hiden-selection").show();
                $(".fg-member-default").hide();
                $(".membership-type-label").addClass('col-sm-offset-2');
            }
            else {
                $(".fg-hide-membeship-selection").show();
                $(".fg-member-default").show();
                $(".fg-member-hiden-selection").hide();
                $(".membership-type-label").removeClass('col-sm-offset-2');
            }
        });
        $('body').off('click', '.fg-dev-hide-membership');
        $('body').on('click', '.fg-dev-hide-membership', function () {
            if ($(this).is(':checked')) {
                if ($("select.fg-dev-hide-membership-selction").find('option:selected').val() != 'none') {
                    $("#fg-selcted-membership").html($("select.fg-dev-hide-membership-selction").find('option:selected').text());
                }
                $(".fg-hide-membeship-selection").hide();
                $(".fg-member-hiden-selection").show();
                $(".fg-member-default").hide();
                $(".membership-type-label").addClass('col-sm-offset-2');
                $(this).parents().eq(3).find(".fg-selection-hide").removeAttr('disabled');
            }
            else {
                $(".fg-hide-membeship-selection").show();
                $(".fg-member-default").show();
                $(".fg-member-hiden-selection").hide();
                $(".membership-type-label").removeClass('col-sm-offset-2');
                $(this).parents().eq(3).find(".fg-selection-hide").attr('disabled', 'disabled');
            }
            $("select.fg-dev-hide-membership-selction").selectpicker('refresh');
        });
        $('body').off('click', '.fg-dev-hide-field');
        $('body').on('click', '.fg-dev-hide-field', function () {
            if ($(this).is(":checked")) {
                $(this).parents().eq(3).find(".fg-selection-hide").removeAttr('disabled');
            }
            else {
                $(this).parents().eq(3).find(".fg-selection-hide").attr('disabled', 'disabled');
            }
            $("select.fg-dev-hide-mandatory-selction").selectpicker('refresh');
        });
    };
    FgContactFormFields.prototype.setFormElementSortable = function () {
        var _this = this;
        $("#formFields").sortable({
            items: "> li.list-group-item",
            containment: "#form-field-elements-form-stage1",
            handle: ".fg-dev-field-sort-handle",
            stop: function (event, ui) {
                _this.reorderElementList($('#formFields>li'), 'fieldSort');
            }
        });
    };
    FgContactFormFields.prototype.setOptionSortable = function (fieldId) {
        var optionListContainer = $('#formElement_value_' + fieldId);
        var _this = this;
        optionListContainer.sortable({
            items: "> li.list-group-item",
            containment: $('#typeOptions-' + fieldId),
            handle: ".fg-dev-optionvalue-sort-handle",
            stop: function (event, ui) {
                _this.reorderElementList($('ul#formElement_value_' + fieldId + '>li'), 'optionSort');
            }
        });
    };
    FgContactFormFields.prototype.validateWizardStage1 = function () {
        var valid = true;
        $('.link-error').removeClass('link-error');
        $('#alert_noFieldError').remove();
        $("#alert_noMandatoryError").remove();
        $("#alert_noMembershipError").remove();
        $("#emptyMembershipTypeeError").remove();
        if ($.trim($('#formname').val()) == '') {
            $('#formname').parent().addClass('has-error');
            valid = false;
        }
        if ($('select.form_field_type').find(":selected[value='default']").length > 0) {
            $('.fg-cms-form-element-create-container').prepend(FGTemplate.bind('emptyFieldTypeError', {}));
            valid = false;
        }
        if ($('select.fg_dev_contact_field').find(":selected[value='default']").length > 0) {
            $('.fg-cms-form-element-create-container').prepend(FGTemplate.bind('emptyFieldTypeError', {}));
            valid = false;
        }
        if ($('.fg-member-default').length > 0 && ($('.fg-dev-hide-membership').is(':checked') == 0) && ($('select.fg_dev_membership_field').find(":selected[value='none']").length > 0 || $('select.fg_dev_membership_field').val() == null)) {
            $('.fg-cms-form-element-create-container').prepend(FGTemplate.bind('emptyMembershipTypeeError', {}));
            valid = false;
        }
        else if ($('.fg-dev-hide-membership').is(':checked') == 1 && ($('select.fg-dev-hide-membership-selction').val() == 'none')) {
            $('.fg-cms-form-element-create-container').prepend(FGTemplate.bind('emptyMembershipTypeeError', {}));
            valid = false;
        }
        $('.fg-dev-hide-field').each(function () {
            if ($(this).is(':checked') == 1 && $(this).parents().eq(3).find(".fg-dev-hide-mandatory-selction").val() == "default_select") {
                $('.fg-cms-form-element-create-container').prepend(FGTemplate.bind('emptyMandatoryError', {}));
                valid = false;
                return valid;
            }
        });
        $('.fg-option-value-list').each(function () {
            if ($(this).find('li').length == 0) {
                $(this).parents('li').find('.fg-toggle-link').addClass('link-error');
            }
        });
        $('input[type=text]').each(function () {
            var currentVal = $(this).val();
            $(this).val($.trim(currentVal));
        });
        if (!$('#form-field-elements-form-stage1').valid()) {
            $('.fg-option-value-list').each(function () {
                var error = $(this).find('.help-block').length;
                if (parseInt(error) >= 1) {
                    $(this).parents('li').find('.fg-toggle-link').addClass('link-error');
                }
            });
            valid = false;
        }
        $('.fg-collapsed-content-wrapper').find('.has-error').each(function () {
            $(this).parents('li.list-group-item').find('.fg-toggle-link').addClass('link-error');
        });
        this.showTranslation();
        return valid;
    };
    FgContactFormFields.prototype.saveStage1 = function (formData, next) {
        var _this = this;
        formData.formId = _this.formId;
        FgUtility.startPageLoading();
        $.ajax({
            type: "POST",
            url: this.settings.stage1savepath,
            data: formData,
            success: function (response) {
                FgUtility.stopPageLoading();
                if (response.status == 'SUCCESS') {
                    $.fn.dirtyFields.markContainerFieldsClean($("#form-field-elements-form-stage1"));
                    $('.alert_noFieldError').remove();
                    $("#alert_noMandatoryError").remove();
                    $("#emptyMembershipTypeeError").remove();
                    $("#alert_noMembershipError").remove();
                    FgUtility.showToastr(formSaveSuccess);
                    if (next) {
                        _this.formId = response.meta.formId;
                        $('#formFields-button').html('');
                        $('#formFields').html('');
                        $('.nav-pills li:eq(1) a').tab('show');
                        _this.getStage2Data();
                    }
                    else {
                        $("#formname-error").addClass('hide');
                        $("#formname-error").hide();
                        _this.formId = response.meta.formId;
                        _this.getStage1Data();
                    }
                    _this.event = 'edit';
                    $('#formwizard_event').val('edit');
                }
                else {
                    $("#form-name-group").addClass('has-error');
                    $("#formname-error").html(response.error);
                    $("#formname-error").removeClass('hide');
                    $("#formname-error").show();
                }
            },
            dataType: 'json'
        });
    };
    FgContactFormFields.prototype.getFormData = function () {
        var postData = {};
        postData.formFieldData = this.formFieldParse('form-field-elements-form-stage1');
        postData.formname = $('#formname').val();
        postData.existing = $('#existing').val();
        postData.pageId = $('#pageId').val();
        postData.boxId = $('#boxId').val();
        postData.sortOrder = $('#sortOrder').val();
        postData.event = $('#formwizard_event').val();
        postData.stage = $('#formStage').val();
        postData.captchaEnabled = ($('#formFields-captcha').is(':checked')) ? 1 : 0;
        return postData;
    };
    FgContactFormFields.prototype.changeNumberValue = function (action, defaultVal, obj) {
        var currentVal = parseInt($(obj).parent().find('input').val());
        if (!isNaN(currentVal)) {
            var newVal = void 0;
            if (action == 'minus') {
                newVal = currentVal - 1;
            }
            else if (action == 'plus') {
                newVal = currentVal + 1;
            }
            $(obj).parent().find('input').val(newVal);
        }
        else {
            $(obj).val(defaultVal);
        }
    };
    FgContactFormFields.prototype.setUIPlugins = function (fieldId) {
        var _this = this;
        var fieldType = $('#fieldType_' + fieldId).val();
        $("input[type=checkbox]:not(.toggle, .make-switch,.formFields-captcha), input[type=radio]:not(.toggle, .star, .make-switch)").uniform();
        $('#fieldType_' + fieldId).selectpicker();
        $('#cf_' + fieldId).selectpicker();
        $('#club_membership_' + fieldId).selectpicker({ noneSelectedText: _this.settings.noneSelectedText });
        switch (fieldType) {
            case 'singleline':
            case 'multiline':
            case 'email':
            case 'url':
            case 'fileupload':
            default:
                break;
            case 'number':
                var num = new FgNumber({ 'selector': '#' + fieldId + '_wrapper .selectButton', 'inputNum': '#' + fieldId + '_wrapper input.input-number' });
                num.init();
                break;
            case 'date':
                var dateSettings = {
                    language: jstranslations.localeName,
                    format: FgLocaleSettingsData.jqueryDateFormat,
                    autoclose: true,
                    weekStart: 1,
                    clearBtn: true
                };
                $('#formElement_date_minValue_' + fieldId).datepicker(dateSettings).on('changeDate', function (ev) {
                    var selectedDate = $(this).val();
                    $('#formElement_date_maxValue_' + fieldId).datepicker('setStartDate', selectedDate);
                });
                $('#formElement_date_maxValue_' + fieldId).datepicker(dateSettings).on('changeDate', function (ev) {
                    var selectedDate = $(this).val();
                    $('#formElement_date_minValue_' + fieldId).datepicker('setEndDate', selectedDate);
                });
                break;
            case 'time':
                break;
            case 'checkbox':
            case 'select':
            case 'radio':
                this.setOptionSortable(fieldId);
                break;
        }
    };
    FgContactFormFields.prototype.showTranslation = function () {
        var currentSelectedLanguage = $('.fg-lang-tab .active').attr('id');
        if (currentSelectedLanguage == '' || typeof currentSelectedLanguage == 'undefined')
            currentSelectedLanguage = this.clubDefaultLanguage;
        FgUtility.showTranslation(currentSelectedLanguage);
    };
    FgContactFormFields.prototype.reorderElementList = function (list, sortElement) {
        var z = 0;
        list.each(function (order, element) {
            if (!$(this).hasClass('inactiveblock')) {
                var sort = ++z;
                $(this).find('.' + sortElement).val(sort).attr('value', sort).trigger('change');
            }
        });
    };
    FgContactFormFields.prototype.connectCKEditorStage2 = function (clubLanguages, elementPrefix) {
        var toolbarConfig = ckEditorConfig.cmsEditor;
        CKEDITOR.config.dialog_noConfirmCancel = true;
        CKEDITOR.config.extraPlugins = 'confighelper';
        CKEDITOR.config.allowedContent = {
            $1: {
                elements: CKEDITOR.dtd,
                attributes: true,
                styles: true,
                classes: true
            }
        };
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.on('dialogDefinition', function (ev) {
            var diagName = ev.data.name;
            var diagDefn = ev.data.definition;
            if (diagName === 'table') {
                var infoTab = diagDefn.getContents('info');
                var width = infoTab.get('txtWidth');
                width.default = "100%";
                width.onChange = function () {
                    var id = this.domId;
                    $('#' + id + ' input').attr('readonly', 'readonly');
                    return false;
                };
            }
        });
        var _this = this;
        _.each(clubLanguages, function (lang, key) {
            var instanceName = elementPrefix + '_' + lang;
            if ($('#' + instanceName).length > 0) {
                if (CKEDITOR.instances[instanceName]) {
                    try {
                        CKEDITOR.instances[instanceName].destroy();
                    }
                    catch (error) { }
                    delete CKEDITOR.instances[instanceName];
                }
                CKEDITOR.replace(instanceName, {
                    toolbar: toolbarConfig,
                    language: lang
                }).on('change', function () {
                    var content = CKEDITOR.instances[instanceName].document.getBody().getHtml().replace('<p><br></p>', '');
                    $('#' + instanceName).val(content).text(content).trigger('change');
                });
                CKEDITOR.instances[instanceName].addContentsCss('/fgcustom/css/fg-ckeditor-mail.css');
                if ($('#' + instanceName).attr('data-required') == 'true') {
                    $('#' + instanceName).attr('required', 'required');
                }
            }
        });
    };
    FgContactFormFields.prototype.connectAutocomplete = function (selectedRecipients) {
        var formId = this.formId;
        var selectedIds = [];
        if (selectedRecipients != null) {
            _.each(selectedRecipients, function (name, id) {
                selectedIds.push({ 'id': id, 'title': name });
            });
        }
        $('#' + formId + '_recipients').fbautocomplete({
            url: notificationRecipientsPath,
            maxItems: 0,
            useCache: false,
            selected: selectedIds,
            onItemSelected: function ($obj, itemId, selected) {
                var alreadySelectedvalues = _.compact($('#' + formId + '_recipients_data').val().split(','));
                alreadySelectedvalues.push(itemId);
                $('#' + formId + '_recipients_data').val(_.uniq(alreadySelectedvalues).join()).trigger('change');
            },
            onItemRemoved: function ($obj, itemId) {
                var alreadySelectedvalues = _.compact($('#' + formId + '_recipients_data').val().split(','));
                alreadySelectedvalues = _.reject(alreadySelectedvalues, function (v) { return v == itemId; });
                $('#' + formId + '_recipients_data').val(_.uniq(alreadySelectedvalues).join()).trigger('change');
            }
        });
    };
    FgContactFormFields.prototype.validateWizardStage2 = function () {
        var valid = true;
        $('#form-field-elements-form-stage2').find('.has-error').removeClass('has-error');
        valid = $('#form-field-elements-form-stage2').valid();
        return valid;
    };
    FgContactFormFields.prototype.saveStage2 = function (next) {
        var dataArray = {};
        var _this = this;
        dataArray.formFieldData = this.formFieldParse('formelement-stage2');
        dataArray.formId = this.formId;
        dataArray.stage = 'stage2';
        $.ajax({
            type: "POST",
            url: this.settings.stage2savepath,
            data: dataArray,
            success: function (response) {
                FgUtility.showToastr(formSaveSuccess);
                FgUtility.stopPageLoading();
                $.fn.dirtyFields.markContainerFieldsClean($("#form-field-elements-form-stage2"));
                if (next) {
                    $('.nav-pills li:eq(2) a').tab('show');
                    _this.getStage3Data();
                }
                else {
                    _this.getStage2Data();
                }
            },
            dataType: 'json'
        });
    };
    FgContactFormFields.prototype.validateWizardStage3 = function () {
        var valid = true;
        $('.fg-dev-success-msg').each(function () {
            var currentVal = $(this).val();
            $(this).val($.trim(currentVal));
        });
        valid = $('#form-field-elements-form-stage3').valid();
        return valid;
    };
    FgContactFormFields.prototype.saveStage3 = function (finish) {
        var dataArray = {};
        var _this = this;
        dataArray.formFieldData = this.formFieldParse('formelement-stage3');
        dataArray.formId = this.formId;
        dataArray.stage = 'stage3';
        if (finish) {
            dataArray.finish = finish;
        }
        $.ajax({
            type: "POST",
            url: this.settings.stage3savepath,
            data: dataArray,
            success: function (response) {
                $('#form_element_finish').addClass('hide');
                FgUtility.stopPageLoading();
                FgUtility.showToastr(formSaveSuccess);
                $.fn.dirtyFields.markContainerFieldsClean($("#form-field-elements-form-stage3"));
                if (finish) {
                    window.location.href = $('#form_element_finish').data('href');
                }
                else {
                    _this.getStage3Data();
                }
            },
            dataType: 'json'
        });
    };
    FgContactFormFields.prototype.renderFormButtons = function () {
        var _this = this;
        var stage = _this.getCurrentStage();
        $('#form_element_save_and_next,#form_element_save,#form_element_cancel,#form_element_discard,#form_element_back,#form_element_finish').addClass('hide');
        if (stage == 1) {
            if (_this.event == 'create') {
                $('#form_element_save_and_next,#form_element_save,#form_element_cancel').removeClass('hide');
            }
            else {
                $('#form_element_save_and_next,#form_element_save,#form_element_discard').removeClass('hide');
            }
        }
        else if (stage == 2) {
            $('#form_element_save_and_next,#form_element_save,#form_element_discard,#form_element_back').removeClass('hide');
        }
        else if (stage == 3) {
            $('#form_element_save,#form_element_discard,#form_element_finish,#form_element_back').removeClass('hide');
        }
        _this.initDirtyFields();
    };
    FgContactFormFields.prototype.initDirtyFields = function () {
        var _this = this;
        var stage = _this.getCurrentStage();
        if (stage == 1) {
            var formId = 'form-field-elements-form-stage1';
        }
        else if (stage == 2) {
            var formId = 'form-field-elements-form-stage2';
        }
        else if (stage == 3) {
            var formId = 'form-field-elements-form-stage3';
        }
        FgDirtyFields.init(formId, { enableDiscardChanges: false, setInitialHtml: false, formChangeCallback: function (isDirty) { _this.setFormDirty(formId, isDirty); } });
    };
    FgContactFormFields.prototype.setFormDirty = function (formId, isDirty) {
        if (this.getCurrentStage() == 1 && this.event == 'create')
            return;
        if (isDirty) {
            $('#form_element_save,#form_element_discard,#form_element_save_and_next').removeAttr('disabled');
        }
        else {
            $('#form_element_save,#form_element_discard,#form_element_save_and_next').attr('disabled', 'disabled');
        }
    };
    FgContactFormFields.prototype.setStepTitle = function () {
        var stage = this.getCurrentStage();
        $('.portlet-title .step-title').html(stepTranslation.replace('%S%', stage));
    };
    FgContactFormFields.prototype.setTranslationTabError = function (formId) {
        FgLanguageSwitch.checkMissingTranslation(this.clubDefaultLanguage, formId);
    };
    FgContactFormFields.prototype.setlanguageSwitchActive = function () {
        $(".fg-action-language-set button").removeClass('active');
        $('#' + this.clubDefaultLanguage).addClass('active');
    };
    FgContactFormFields.prototype.setWizardStage = function (formStage) {
        if (formStage == 'stage1') {
            $('.nav>li[data-target="form-stage1"]').addClass('done');
            $('.nav>li[data-target="form-stage2"]').removeClass('disabled');
            $('.nav>li[data-target="form-stage3"]').addClass('disabled');
        }
        else if (formStage == 'stage2') {
            $('.nav>li[data-target="form-stage1"]').addClass('done');
            $('.nav>li[data-target="form-stage2"]').addClass('done');
            $('.nav>li[data-target="form-stage3"]').removeClass('disabled');
        }
        else if (formStage == 'stage3') {
            $('.nav>li[data-target="form-stage1"]').addClass('done');
            $('.nav>li[data-target="form-stage2"]').addClass('done');
            $('.nav>li[data-target="form-stage3"]').addClass('done');
        }
        var currentStage = this.getCurrentStage();
        if (currentStage == 1) {
            $('.nav>li[data-target="form-stage1"]').removeClass('done').addClass('active');
        }
        else if (currentStage == 2) {
            $('.nav>li[data-target="form-stage2"]').removeClass('done').addClass('active');
        }
        else if (currentStage == 3) {
            $('.nav>li[data-target="form-stage3"]').removeClass('done').addClass('active');
        }
    };
    FgContactFormFields.prototype.formFieldParse = function (formId) {
        var _this = this;
        $('.sortables').parent().each(function () {
            FgUtility.resetSortOrder($(this));
        });
        $('.fg-dev-newfield').addClass('fairgatedirty');
        var objectGraph = {};
        $("#" + formId + " :input").each(function () {
            var attr = $(this).attr('data-key');
            if ($(this).hasClass("fairgatedirty") && typeof attr !== typeof undefined && attr !== false) {
                var inputVal = '';
                var inputType = $(this).attr('type');
                if (inputType == 'checkbox') {
                    inputVal = $(this).attr('checked') ? 1 : 0;
                }
                else if (inputType == 'radio') {
                    if ($(this).is(':checked')) {
                        inputVal = $(this).val();
                    }
                }
                else {
                    inputVal = $(this).val();
                }
                if (inputVal !== '' || $(this).is("textarea") || $(this).is("select")) {
                    _this.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
                else if (inputType == 'hidden' || $(this).hasClass("hide")) {
                    _this.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
                else if ((inputVal === '') && ($(this).attr('data-notrequired') == 'true')) {
                    _this.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
            }
        });
        return objectGraph;
    };
    FgContactFormFields.prototype.converttojson = function (objectGraph, name, value) {
        if (name.length == 1) {
            objectGraph[name[0]] = value;
        }
        else {
            if (objectGraph[name[0]] == null) {
                objectGraph[name[0]] = {};
            }
            this.converttojson(objectGraph[name[0]], name.slice(1), value);
        }
    };
    FgContactFormFields.prototype.loadMandatoryfields = function () {
        var _this = this;
        var formHtml;
        var postData;
        var formElementData = {};
        var fieldId = 0;
        _.each(this.settings.manadatoryFields, function (data, key) {
            fieldId = "mandatoryField_" + _.random(2000, 9999);
            formElementData['mandatoryElementArray'] = [{ 'fieldType': 'mandatory', 'formElementSortOrder': _this.sortOrder++, 'formFieldId': fieldId, 'formId': _this.formId, 'source': 'new', 'typeField': data.inputType, 'fieldTitle': data.fieldnameLang }];
            var templateId = 'newMandatoryElement';
            formHtml = FGTemplate.bind(templateId, { "data": formElementData['mandatoryElementArray'][0] });
            $('#formFields').append(formHtml);
            var formData = _this.formFieldParse('formElement_' + fieldId);
            formData[_this.formId][fieldId]['formId'] = _this.formId;
            formData[_this.formId][fieldId]['formFieldId'] = fieldId;
            formData[_this.formId][fieldId]['clubLanguages'] = _this.clubLanguages;
            formData[_this.formId][fieldId]['clubDefaultLanguage'] = _this.clubDefaultLanguage;
            postData = formData[_this.formId][fieldId];
            postData.elementId = data.id;
            postData.fieldTitle = data.fieldnameLang;
            switch (data.inputType) {
                case 'singleline':
                    var fieldTemplate = "form_field_templates_mandatory_singleline";
                    $("#typeOptions-" + fieldId).html(FGTemplate.bind(fieldTemplate, { 'data': postData }));
                    break;
                case 'select':
                    var fieldTemplate = "form_field_templates_mandatory_select";
                    if (data.id == _this.settings.systemFieldCorressLangId) {
                        postData.options = _this.clubLanguages;
                    }
                    else {
                        var databaseValue = data.predefinedValue;
                        postData.options = databaseValue.split(";");
                    }
                    $("#typeOptions-" + fieldId).html(FGTemplate.bind(fieldTemplate, { 'data': postData }));
                    $(".fg-dev-hide-mandatory-selction").selectpicker();
                    break;
            }
            _this.setFieldChangeEvent(fieldId);
        });
        $(".has-needed").uniform();
    };
    FgContactFormFields.prototype.validateFormName = function (formname) {
        $.ajax({
            type: "POST",
            url: formNameValidationPath,
            data: { 'formName': formname },
            success: function (response) {
                if (response.valid == false) {
                    valid = false;
                }
                else {
                    valid = true;
                }
            },
            dataType: 'json'
        });
    };
    FgContactFormFields.prototype.loadWizardStage1Edit = function (formData) {
        this.setCurrentStage(1);
        this.event = 'edit;';
        $('#form-stage-progressbar .progress-bar').css('width', '33%');
        $('#formFields-button').html('');
        $('#formFields').html('');
        this.loadFormName(formData['name']);
        this.loadContactFormfields(formData);
        FormValidation.init('form-field-elements-form-stage1');
        this.setTranslationTabError('form-field-elements-form-stage1');
        this.renderFormButtons();
        this.setStepTitle();
    };
    FgContactFormFields.prototype.loadContactFormfields = function (formData) {
        var _this = this;
        _.each(formData['elementArray'], function (data, key) {
            if ($.inArray(parseInt(data.attributeId), _this.settings.mandatoryFieldId) != -1) {
                _this.loadMandatoryEditData(data, key);
            }
            else if (data.formFieldType == 'contact' || data.formFieldType == 'form') {
                _this.loadFieldEditData(data, key);
            }
            else if (data.formFieldType == 'club-membership') {
                _this.loadFieldEditData(data, key);
            }
            else {
                _this.loadFieldEditData(data, key);
            }
            _this.setFieldChangeEvent(data.formFieldId);
        });
        if (_.size(_.findWhere(formData['elementArray'], { fieldType: "captcha" })) > 0) {
            $.uniform.restore("#formFields-captcha");
            $('#formFields-captcha').attr('checked', 'checked');
            $('#formFields-captcha').uniform();
        }
    };
    FgContactFormFields.prototype.loadMandatoryEditData = function (data, formElementId) {
        var _this = this;
        var fieldIdArray = formElementId.split("_");
        var formElementData = {};
        var fieldId = fieldIdArray[1];
        formElementData['mandatoryElementArray'] = [{ 'fieldType': 'mandatory', 'formElementSortOrder': data.sortOrder, 'formFieldId': fieldId, 'formId': data.formId, 'source': 'new', 'typeField': data.mandatoryInputType, 'fieldTitle': data.fieldnameLang }];
        _this.formId = data.formId;
        var templateId = 'newMandatoryElement';
        var formHtml = FGTemplate.bind(templateId, { "data": formElementData['mandatoryElementArray'][0] });
        $('#formFields').append(formHtml);
        var postData = data;
        postData.clubLanguages = _this.clubLanguages;
        postData.clubDefaultLanguage = _this.clubDefaultLanguage;
        postData.elementId = data.attributeId;
        postData.fieldTitle = data.fieldnameLang;
        switch (data.mandatoryInputType) {
            case 'singleline':
                var fieldTemplate = "form_field_templates_mandatory_singleline";
                $("#typeOptions-" + fieldId).html(FGTemplate.bind(fieldTemplate, { 'data': postData }));
                break;
            case 'select':
                var fieldTemplate = "form_field_templates_mandatory_select";
                if (data.attributeId == _this.settings.systemFieldCorressLangId) {
                    postData.options = _this.clubLanguages;
                }
                else {
                    var databaseValue = data.mandatoryPredefinedValue;
                    postData.options = databaseValue.split(";");
                }
                $("#typeOptions-" + fieldId).html(FGTemplate.bind(fieldTemplate, { 'data': postData }));
                $(".fg-dev-hide-mandatory-selction").selectpicker();
                break;
        }
    };
    FgContactFormFields.prototype.loadFieldEditData = function (data, formElementId) {
        var _this = this;
        var formHtml = '';
        $('#formFields-captcha').removeAttr('checked');
        if ($.inArray(data.fieldType, ["captcha"]) == -1) {
            var dataCopy = data;
            var splitFormElementArray = formElementId.split('_');
            dataCopy.formFieldId = splitFormElementArray[1];
            dataCopy.fieldType = (data.formFieldType == 'contact') ? 'contactfield' : data.fieldType;
            if (data.formFieldType == 'club-membership') {
                dataCopy.fieldType = 'membership';
            }
            formHtml = _this.getFieldRowHtml(dataCopy, formElementId);
            var elementIdArray = $(formHtml).attr('id').split("_");
            if (data.fieldType == 'button') {
                $('#formFields-button').html(formHtml);
            }
            else {
                $('#formFields').append(formHtml);
                var elementType = (data.formFieldType == 'contact') ? _this.getContactFieldTemplate(data.mandatoryInputType) : data.fieldType;
                dataCopy.elementType = elementType;
                if (data.formFieldType == 'club-membership') {
                    elementType = 'membership';
                }
                var templateId = 'form_field_templates_' + elementType;
                if (elementType != 'heading') {
                    $("#typeOptions-" + elementIdArray[1]).html(FGTemplate.bind(templateId, { 'data': dataCopy }));
                }
                $("select.fg-dev-hide-membership-selction").selectpicker();
                $("select.fg_dev_membership_field").selectpicker({ noneSelectedText: _this.settings.noneSelectedText });
                if (data.formFieldType == 'club-membership') {
                    FgColumnSettings.handleSelectPicker();
                }
                _this.setEvents(elementIdArray[1]);
                if (data.formFieldType == 'contact') {
                    _this.setContactFieldEvents(elementIdArray[1], elementType);
                }
            }
        }
    };
    FgContactFormFields.prototype.getContactFieldTemplate = function (elementType) {
        var fieldType;
        switch (elementType) {
            case "imageupload":
                fieldType = 'fileupload';
                break;
            case "login email":
                fieldType = 'email';
                break;
            case "select":
                fieldType = 'cf_select';
                break;
            case "checkbox":
                fieldType = 'cf_checkbox';
                break;
            case "radio":
                fieldType = 'cf_radio';
                break;
            default:
                fieldType = elementType;
                break;
        }
        return fieldType;
    };
    FgContactFormFields.prototype.setContactFieldEvents = function (fieldId, elementType) {
        var _this = this;
        var fieldType = $('#fieldType_' + fieldId).val();
        console.log('entercont');
        $("input[type=checkbox]:not(.toggle, .make-switch,.formFields-captcha), input[type=radio]:not(.toggle, .star, .make-switch)").uniform();
        $('#fieldType_' + fieldId).selectpicker();
        $('#cf_' + fieldId).selectpicker();
        $('#club_membership_' + fieldId).selectpicker();
        switch (elementType) {
            case 'singleline':
            case 'multiline':
            case 'email':
            case 'url':
            case 'fileupload':
            default:
                break;
            case 'number':
                var num = new FgNumber({ 'selector': '#' + fieldId + '_wrapper .selectButton', 'inputNum': '#' + fieldId + '_wrapper input.input-number' });
                num.init();
                break;
            case 'date':
                var dateSettings = {
                    language: jstranslations.localeName,
                    format: FgLocaleSettingsData.jqueryDateFormat,
                    autoclose: true,
                    weekStart: 1,
                    clearBtn: true
                };
                $('#formElement_date_minValue_' + fieldId).datepicker(dateSettings).on('changeDate', function (ev) {
                    var selectedDate = $(this).val();
                    $('#formElement_date_maxValue_' + fieldId).datepicker('setStartDate', selectedDate);
                });
                $('#formElement_date_maxValue_' + fieldId).datepicker(dateSettings).on('changeDate', function (ev) {
                    var selectedDate = $(this).val();
                    $('#formElement_date_minValue_' + fieldId).datepicker('setEndDate', selectedDate);
                });
                break;
            case 'time':
                break;
            case 'checkbox':
            case 'select':
            case 'radio':
                this.setOptionSortable(fieldId);
                break;
        }
    };
    return FgContactFormFields;
}());
