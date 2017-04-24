var pageTitleBarObj;
var clickCount = 0;
var FgCmsContactPortraits = (function () {
    function FgCmsContactPortraits(options) {
        this.sortSetting = '';
        this.defaultSettings = {
            stage1savepath: '',
            stage2savepath: '',
            portraitCount: 0
        };
        this.defaultSortOptions = {
            opacity: 0.8,
            forcePlaceholderSize: true,
            tolerance: "pointer"
        };
        this.tableId = options.tableId;
        this.event = options.event;
        this.log = options.log;
        this.currentStage = (options.currentStage > 0) ? options.currentStage : 1;
        this.connectButtonClick();
        this.connectStageClick();
        this.settings = $.extend(true, {}, this.defaultSettings, options);
        this.handleNumberButtons();
        this.containerEdit();
        this.createContainer();
        this.containerDelete();
        this.sortContainer();
        this.handleAddLabel();
        this.handleLabelLanguageSwitch();
        this.manageColumnWidth();
    }
    FgCmsContactPortraits.prototype.handleNumberButtons = function () {
        var plusminusOption = {
            'selector': ".selectButton"
        };
        var inputplusminus = new Fgplusminus(plusminusOption);
        inputplusminus.init();
    };
    FgCmsContactPortraits.prototype.connectButtonClick = function () {
        var _this = this;
        $('#contacts_table_element_save').on('click', function () {
            if ($(this).attr('disabled') == 'disabled')
                return;
            if (_this.getCurrentStage() == 1) {
                _this.saveWizardStage1(false);
            }
            else if (_this.getCurrentStage() == 2) {
                _this.saveWizardStage2(false);
            }
            else if (_this.getCurrentStage() == 3) {
                $(this).attr('disabled', 'disabled');
                _this.saveWizardStage3(false);
            }
            else if (_this.getCurrentStage() == 4) {
                _this.saveWizardStage4(false);
            }
        });
        $('#contacts_table_element_back').on('click', function () {
            if ($(this).attr('disabled') == 'disabled')
                return;
            if (_this.getCurrentStage() == 4) {
                $('.nav-pills li:eq(2) a').tab('show');
                _this.getStage3Data();
            }
            else if (_this.getCurrentStage() == 3) {
                $('.nav-pills li:eq(1) a').tab('show');
                _this.getStage2Data();
            }
            else if (_this.getCurrentStage() == 2) {
                $('.nav-pills li:eq(0) a').tab('show');
                _this.getStage1Data();
            }
        });
        $('#contacts_table_element_save_and_next').on('click', function () {
            if ($(this).attr('disabled') == 'disabled')
                return;
            if (_this.getCurrentStage() == 1) {
                _this.saveWizardStage1(true);
            }
            else if (_this.getCurrentStage() == 2) {
                _this.saveWizardStage2(true);
            }
            else if (_this.getCurrentStage() == 3) {
                _this.saveWizardStage3(true);
            }
        });
        $('#contacts_table_element_finish').on('click', function () {
            if ($(this).attr('disabled') == 'disabled')
                return;
            _this.saveWizardStage4(true);
        });
        $('#contacts_table_element_discard').on('click', function () {
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
            else if (_this.getCurrentStage() == 4) {
                _this.getStage4Data();
            }
        });
        $('body').on('click', '.fg-dev-addNewData', function () {
            var jsonData = { contactListColumnJson: contactListColumnJson, defaultLang: defaultLang, columnId: $(this).attr('column_id'), containerId: $(this).attr('container_id') };
            var htmlFinal = FGTemplate.bind('contactListNewColumnPopup', jsonData);
            FgModelbox.showPopup(htmlFinal);
            $('select.selectpicker').selectpicker('render');
            $('#saveContactListColumnPopup').attr("disabled", "disabled");
        });
        $('#fg-dev-addNewFilterColumn').on('click', function () {
            var jsonData = { contactListFilterJson: contactListFilterJson, defaultLang: defaultLang };
            var htmlFinal = FGTemplate.bind('contactListNewFilterPopup', jsonData);
            $('.fg-modal-contact-list-filter-content').html(htmlFinal);
            $('#contactListAddFilterPopup').modal('show');
            $('select.selectpicker').selectpicker('render');
            $('#saveContactListFilterPopup').attr("disabled", "disabled");
        });
        $('body').on('change', '.contact-list-table-column-type', function () {
            if ($(this).val() != 'default') {
                $('#saveContactListColumnPopup').attr("disabled", "disabled");
                if ($(this).val() == 'PROFILE_PIC' || contactListColumnJson[$(this).val()]['fieldValue'] == undefined) {
                    $('#saveContactListColumnPopup').removeAttr("disabled");
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                }
                else {
                    var secondDpData = { datas: contactListColumnJson[$(this).val()], defaultLang: defaultLang, clubLangDetails: clubLangDetails, selectedVal: $(this).val() };
                    var htmlFinal = FGTemplate.bind('contactListSecondDpStep3', secondDpData);
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                    $(this).parent().parent().append(htmlFinal);
                    $('select.selectpicker').selectpicker('render');
                }
            }
            else if ($(this).val() == '') {
                $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
            }
        });
        $('body').on('change', '.contact-list-portrait-column-type', function () {
            if ($(this).val() != 'default') {
                if (contactListColumnJson[$(this).val()]['fieldValue'] == undefined) {
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                }
                else {
                    var secondDpData = { datas: contactListColumnJson[$(this).val()], defaultLang: defaultLang, clubLangDetails: clubLangDetails, selectedVal: $(this).val() };
                    var htmlFinal = FGTemplate.bind('contactListSecondDp', secondDpData);
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                    $(this).parent().parent().append(htmlFinal);
                    $('select.selectpicker').selectpicker('render');
                }
            }
            else if ($(this).val() == '') {
                $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
            }
        });
        $('body').on('change', '.fg-dev-contact-list-table-secondDp', function () {
            if ($(this).val() != 'default') {
                $('#saveContactListColumnPopup').removeAttr("disabled");
            }
            else {
                $('#saveContactListColumnPopup').attr("disabled", "disabled");
            }
        });
        $('body').on('click', '.fg-new_row_rmv', function () {
            $(this).parent().remove();
        });
        $('body').on('click', '.fg-dev-place-holder-image-upload', function () {
            $("#image-uploader").trigger('click');
        });
        $('body').on('click', '.fg-dev-data-delete', function () {
            var checkBoxValue = $(this).find('.fg-data-delete').prop('checked');
            if (checkBoxValue) {
                $(this).parents().eq(0).addClass('fg-inactiveblock');
            }
            else {
                $(this).parents().eq(0).removeClass('fg-inactiveblock');
            }
        });
        $('body').on('click', '.fg-dev-container-delete', function () {
            var checkBoxValue = $(this).find('.fg-data-delete').prop('checked');
            if (checkBoxValue) {
                $(this).parents().eq(2).addClass('fg-inactiveblock');
            }
            else {
                $(this).parents().eq(2).removeClass('fg-inactiveblock');
            }
        });
        $('body').on('click', '.fg-placeholder-image', function () {
            var checkBoxValue = $(this).find('.fg-upload-delete').attr('checked', 'checked');
            var checkBoxValue = $(this).find('.fg-upload-delete').prop('checked');
            $(this).parent().hide();
            FgDirtyFields.updateFormState();
            if (checkBoxValue) {
            }
            else {
            }
        });
        $('body').on('click', '#triggerLogoUpload', function () {
            $("#image-uploader").trigger('click');
        });
        _this.connectDropdownEventsStage4();
        _this.connectFilterStage4AddButton();
        _this.connectColumnStage3AddButton();
    };
    FgCmsContactPortraits.prototype.getRowHtmlContactList = function (selectedFieldType, selectedField, selectedGroupValue, contactListColumnJson, selectedContainer, selectedColumns) {
        var rand = 'new_' + $.now();
        var linkContactFields = [];
        _.each(contactListColumnJson['CONTACT_FIELD']['fieldValue'], function (datas, catKey) {
            _.each(datas['attrDetails'], function (attrValues, attrKey) {
                if (attrValues['attrType'] == 'url') {
                    linkContactFields.push(attrValues);
                }
            });
        });
        var inputFieldArray;
        var checkboxFlag;
        var selectedFieldLable;
        var inputFieldNameDefault;
        var teamFunctions = [];
        var selectedFunction = [];
        var selectboxFlag = '';
        var addressType = '';
        var teamFunctionTitle = '';
        var fieldType = '';
        var required = 0;
        switch (selectedFieldType) {
            case 'CONTACT_NAME':
                inputFieldArray = contactListColumnJson[selectedFieldType]['fieldName'];
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldName'][defaultLang];
                linkContactFields = linkContactFields;
                fieldType = 'contactname';
                checkboxFlag = 0;
                break;
            case 'PROFILE_PIC':
                inputFieldArray = 'Profile picture';
                selectedFieldLable = this.settings.translations.profilepictrans;
                linkContactFields = linkContactFields;
                fieldType = 'PROFILE_PIC';
                checkboxFlag = 0;
                break;
            case 'TEAM_ASSIGNMENTS':
                inputFieldArray = contactListColumnJson[selectedFieldType]['fieldName'];
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldName'][defaultLang];
                teamFunctions = contactListColumnJson[selectedFieldType]['teamFunctions'];
                teamFunctionTitle = contactListColumnJson[selectedFieldType]['defaultOption'];
                fieldType = 'multiple';
                break;
            case 'CONTACT_FIELD':
                inputFieldArray = {};
                _.each(contactListColumnJson[selectedFieldType]['fieldValue'], function (catDetails, catKey) {
                    if (catDetails['catId'] == selectedGroupValue) {
                        _.each(catDetails['attrDetails'], function (attrArray, attrKey) {
                            if (attrArray['attrId'] == selectedField) {
                                fieldType = attrArray['attrType'];
                                if (attrArray['isSystemField'] == 1) {
                                    _.each(clubLanguages, function (clubLang, clubKey) {
                                        inputFieldArray[clubLang] = attrArray['attrNameLang'][clubLangDetails[clubLang]['systemLang']];
                                        addressType = attrArray['addressType'];
                                    });
                                    if ((attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != '') && (attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != undefined)) {
                                        inputFieldNameDefault = attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']];
                                    }
                                    else {
                                        inputFieldNameDefault = attrArray['fieldName'];
                                    }
                                }
                                else {
                                    inputFieldArray = attrArray['attrNameLang'];
                                    if ((attrArray['fieldNameLang'][defaultLang] != '') && (attrArray['fieldNameLang'][defaultLang] != undefined)) {
                                        inputFieldNameDefault = attrArray['fieldNameLang'][defaultLang];
                                    }
                                    else {
                                        inputFieldNameDefault = attrArray['fieldName'];
                                    }
                                    addressType = attrArray['addressType'];
                                }
                            }
                        });
                    }
                });
                var requiredData = _.filter(contactFieldDetails, function (contactField) { return (contactField['id'] == selectedField && contactField['isRequiredType'] != 'not_required'); });
                if (_.size(requiredData) > 0) {
                    required = 1;
                }
                var contactfieldLabelDetails = _.filter(contactFieldDetails, function (contactField) { return (contactField['id'] == selectedField); });
                if (_.size(contactfieldLabelDetails) > 0) {
                    selectedFieldLable = contactfieldLabelDetails[0]['shortName'];
                }
                else {
                    selectedFieldLable = inputFieldNameDefault;
                }
                break;
            case 'MEMBERSHIP_INFO':
            case 'ANALYSIS_FIELD':
            case 'FED_MEMBERSHIP_INFO':
            case 'FEDERATION_INFO':
                inputFieldArray = contactListColumnJson[selectedFieldType]['fieldValue'][selectedField]['attrNameLang'];
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldValue'][selectedField]['attrName'];
                fieldType = selectedFieldType;
                break;
            case 'TEAM_FUNCTIONS':
            case 'WORKGROUP_ASSIGNMENTS':
            case 'FILTER_ROLE_ASSIGNMENTS':
                inputFieldArray = contactListColumnJson[selectedFieldType]['fieldName'];
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldName'][defaultLang];
                fieldType = 'multiple';
                break;
            case 'WORKGROUP_FUNCTIONS':
            case 'ROLE_CATEGORY_ASSIGNMENTS':
            case 'FED_ROLE_CATEGORY_ASSIGNMENTS':
            case 'COMMON_ROLE_FUNCTIONS':
            case 'COMMON_FED_ROLE_FUNCTIONS':
            case 'INDIVIDUAL_ROLE_FUNCTIONS':
            case 'INDIVIDUAL_FED_ROLE_FUNCTIONS':
            case 'SUB_FED_ROLE_CATEGORY_ASSIGNMENTS':
            case 'INDIVIDUAL_SUB_FED_ROLE_FUNCTIONS':
            case 'COMMON_SUB_FED_ROLE_FUNCTIONS':
                fieldType = 'multiple';
                _.each(contactListColumnJson[selectedFieldType]['fieldValue'], function (catDetails, catKey) {
                    if (catDetails['attrId'] == selectedField) {
                        inputFieldArray = catDetails['attrNameLang'];
                        if ((catDetails['attrNameLang'][defaultLang] != '') && (catDetails['attrNameLang'][defaultLang] != undefined)) {
                            inputFieldNameDefault = catDetails['attrNameLang'][defaultLang];
                        }
                        else {
                            inputFieldNameDefault = catDetails['attrName'];
                        }
                    }
                });
                selectedFieldLable = inputFieldNameDefault;
                break;
        }
        var jsonVal = {
            type: 'new', dataKey: rand, inputFieldArray: inputFieldArray, selectedFieldLable: selectedFieldLable, teamFunctionTitle: teamFunctionTitle,
            checkboxFlag: checkboxFlag, linkContactFields: linkContactFields, teamFunctions: teamFunctions, defaultLang: defaultLang, clubLangDetails: clubLangDetails, systemLang: systemLang,
            clubLanguages: clubLanguages, selectedFieldType: selectedFieldType, selectedField: selectedField, addressType: addressType,
            selectedGroupValue: selectedGroupValue, selectedFunction: selectedFunction, sortOrder: '', selectboxFlag: selectboxFlag, inputFieldNameDefault: inputFieldNameDefault, fieldType: fieldType, containerId: selectedContainer, columnId: selectedColumns, required: required
        };
        return FGTemplate.bind('portraitContentData', jsonVal);
    };
    FgCmsContactPortraits.prototype.getFilterRowHtmlContactList = function (selectedFieldType, selectedField, selectedGroupValue, contactListFilterJson, selectedFieldText) {
        var rand = $.now();
        var inputFieldArray;
        var selectedFieldLable;
        var inputFieldNameDefault;
        var addressType = '';
        var teamFunctionTitle = '';
        var fieldType = '';
        switch (selectedFieldType) {
            case 'CONTACT_FIELD':
                inputFieldArray = {};
                _.each(contactListFilterJson[selectedFieldType]['fieldValue'], function (catDetails, catKey) {
                    if (catDetails['catId'] == selectedGroupValue) {
                        _.each(catDetails['attrDetails'], function (attrArray, attrKey) {
                            if (attrArray['attrId'] == selectedField) {
                                fieldType = attrArray['attrType'];
                                if (attrArray['isSystemField'] == 1) {
                                    lang = clubLangDetails[defaultLang]['systemLang'];
                                    _.each(clubLanguages, function (clubLang, clubKey) {
                                        inputFieldArray[clubLang] = attrArray['attrNameLang'][clubLangDetails[clubLang]['systemLang']];
                                    });
                                    if ((attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != '') && (attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != undefined)) {
                                        inputFieldNameDefault = attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']];
                                    }
                                    else {
                                        inputFieldNameDefault = attrArray['fieldName'];
                                    }
                                    addressType = attrArray['addressType'];
                                }
                                else {
                                    inputFieldArray = attrArray['attrNameLang'];
                                    if ((attrArray['fieldNameLang'][defaultLang] != '') && (attrArray['fieldNameLang'][defaultLang] != undefined)) {
                                        inputFieldNameDefault = attrArray['fieldNameLang'][defaultLang];
                                    }
                                    else {
                                        inputFieldNameDefault = attrArray['fieldName'];
                                    }
                                    addressType = attrArray['addressType'];
                                }
                            }
                        });
                    }
                });
                selectedFieldLable = contactListFilterJson[selectedFieldType]['fieldName'] + ': ' + inputFieldNameDefault;
                var selectedFieldValue = (selectedFieldText) ? selectedFieldText : inputFieldNameDefault;
                inputFieldNameDefault = selectTrans[systemLang].replace('**placeholder**', selectedFieldValue);
                _.each(clubLangDetails, function (clubLang, clubKey) {
                    if (inputFieldArray[clubKey] != '' && inputFieldArray[clubKey] != undefined) {
                        var selectText = (selectTrans[clubLang['systemLang']] != '' && selectTrans[clubLang['systemLang']] != undefined) ? selectTrans[clubLang['systemLang']] : selectTrans[systemLang];
                        var selectedFieldValue = (selectedFieldText) ? selectedFieldText : inputFieldArray[clubKey];
                        inputFieldArray[clubKey] = selectText.replace('**placeholder**', selectedFieldValue);
                    }
                });
                break;
            case 'MEMBERSHIPS':
            case 'FED_MEMBERSHIPS':
                inputFieldArray = contactListFilterJson[selectedFieldType]['labelLang'];
                selectedFieldLable = contactListFilterJson[selectedFieldType]['fieldName'];
                fieldType = 'multiple';
                break;
            case 'WORKGROUPS':
            case 'FILTER_ROLES':
                inputFieldArray = contactListFilterJson[selectedFieldType]['labelLang'];
                selectedFieldLable = contactListFilterJson[selectedFieldType]['fieldName'] + ': ' + $('div.fg-dev-contact-list-table-filter-secondDp button').attr('title');
                inputFieldNameDefault = inputFieldArray[defaultLang];
                fieldType = 'multiple';
                break;
            case 'TEAM_CATEGORY':
            case 'ROLE_CATEGORY':
            case 'FED_ROLE_CATEGORY':
            case 'SUBFED_ROLE_CATEGORY':
                fieldType = 'multiple';
                _.each(contactListFilterJson[selectedFieldType]['fieldValue'], function (catDetails, catKey) {
                    if (catDetails['attrId'] == selectedField) {
                        inputFieldNameDefault = (catDetails['attrNameLang'][defaultLang] != '' && typeof catDetails['attrNameLang'][defaultLang] != 'undefined') ? catDetails['attrNameLang'][defaultLang] : catDetails['attrName'];
                    }
                });
                inputFieldArray = contactListFilterJson[selectedFieldType]['labelLang'];
                selectedFieldLable = contactListFilterJson[selectedFieldType]['fieldName'] + ': ' + inputFieldNameDefault;
                inputFieldNameDefault = inputFieldArray[defaultLang];
                break;
        }
        var jsonVal = {
            type: 'new', dataKey: rand, inputFieldArray: inputFieldArray, selectedFieldLable: selectedFieldLable, defaultLang: defaultLang, clubLangDetails: clubLangDetails,
            clubLanguages: clubLanguages, selectedFieldType: selectedFieldType, selectedField: selectedField, addressType: addressType,
            selectedGroupValue: selectedGroupValue, sortOrder: '', inputFieldNameDefault: inputFieldNameDefault, fieldType: fieldType
        };
        return FGTemplate.bind('contactListFilterData', jsonVal);
    };
    FgCmsContactPortraits.prototype.connectStageClick = function () {
        var _this = this;
        $('ul.steps li').on('click', function () {
            var stage = $(this).attr('data-target');
            if ($(this).hasClass('disabled'))
                return false;
            if (stage == 'wizard-stage1') {
                _this.getStage1Data();
            }
            else if (stage == 'wizard-stage2') {
                _this.getStage2Data();
            }
            else if (stage == 'wizard-stage3') {
                _this.getStage3Data();
            }
            else if (stage == 'wizard-stage4') {
                _this.getStage4Data();
            }
        });
    };
    FgCmsContactPortraits.prototype.getCurrentStage = function () {
        return this.currentStage;
    };
    FgCmsContactPortraits.prototype.setCurrentStage = function (stage) {
        this.currentStage = stage;
        return;
    };
    FgCmsContactPortraits.prototype.loadWizardStage1 = function (stage1Data) {
        var _this = this;
        _this.setCurrentStage(1);
        $('#form-stage-progressbar .progress-bar').css('width', '25%');
        this.handlePageTitleBar({});
        var stage1Html = FGTemplate.bind('contacts_table_stage1_template', { "data": stage1Data, 'tableId': _this.tableId });
        $('#contacts-table-element-stage1').html(stage1Html);
        $('form input[type=radio]').uniform();
        $('#contactFilter').selectpicker('render');
        $('#sponsorFilter').selectpicker('render');
        _this.handleFilterSelection();
        if ($('input:radio[name=saved_filter_type]').is(':checked') === false) {
            _this.handleFilterSelectPickers('contactFilter', true);
            _this.handleFilterSelectPickers('sponsorFilter', true);
            $("input[name=saved_filter_type][value='contact']").attr('required', 'required');
        }
        else {
            $("input[name=saved_filter_type]").removeAttr('required');
            var filterType = $("input[name=saved_filter_type]:checked").val();
            $('input[name="saved_filter_type"][value="' + filterType + '"]').trigger("click");
        }
        _this.handleFilterRefresh('contactFilter');
        _this.handleFilterRefresh('sponsorFilter');
        _this.connectAutocomplete('included', stage1Data['includedContactDetails']);
        _this.connectAutocomplete('excluded', stage1Data['excludedContactDetails']);
        FormValidation.init('contacts_table_element_stage1');
        _this.renderFormButtons();
        _this.setStepTitle();
        _this.hideStage3Preview();
    };
    FgCmsContactPortraits.prototype.handlePageTitleBar = function (params) {
        var defaultOptions = { title: true, tab: (this.log == '1') ? true : false, tabType: 'server' };
        var options = $.extend({}, defaultOptions, params);
        pageTitleBarObj = $(".fg-action-menu-wrapper").FgPageTitlebar(options);
    };
    FgCmsContactPortraits.prototype.handleFilterSelectPickers = function (id, disabled) {
        $('#' + id).prop('disabled', disabled);
        if (disabled) {
            $('#' + id).removeAttr('required');
        }
        else {
            $('#' + id).attr('required', 'required');
        }
        $('#' + id).selectpicker('refresh');
    };
    FgCmsContactPortraits.prototype.handleFilterSelection = function () {
        var _this = this;
        $('.filter_type').click(function () {
            var filterType = $("input[name=saved_filter_type]:checked").val();
            var otherFilterType = (filterType == 'contact') ? 'sponsor' : 'contact';
            _this.handleFilterSelectPickers(filterType + 'Filter', false);
            _this.handleFilterSelectPickers(otherFilterType + 'Filter', true);
        });
    };
    FgCmsContactPortraits.prototype.handleFilterRefresh = function (id) {
        var _this = this;
        $('#' + id + 'Refresh').click(function () {
            var dataArray = { filter_type: $(this).attr('data-filterType') };
            $.ajax({
                type: "POST",
                data: dataArray,
                url: _this.settings['getFilterPath'],
                dataType: 'json',
                success: function (data) {
                    var placeholdertext = $('#' + id + ' option').eq(0);
                    $('#' + id + ' option').remove();
                    $('#' + id).append(placeholdertext);
                    $.each(data, function (index, element) {
                        $('#' + id).append($("<option></option>").attr("value", element.filterId).text(element.filterName));
                    });
                    $('#' + id).selectpicker('refresh');
                }
            });
        });
    };
    FgCmsContactPortraits.prototype.saveWizardStage1 = function (next) {
        if (this.validateWizardStage1()) {
            var _this = this;
            var formData = {};
            formData.contactData = FgInternalParseFormField.formFieldParse('contacts_table_element_stage1');
            formData.tableId = _this.tableId;
            formData.event = $('#contacts_table_element_event').val();
            formData.pageId = $('#contacts_table_element_pageId').val();
            formData.boxId = $('#contacts_table_element_boxId').val();
            formData.sortOrder = $('#contacts_table_element_sortOrder').val();
            formData.elementId = $('#contacts_table_elementId').val();
            formData.elementType = $('#contacts_table_elementType').val();
            FgInternal.pageLoaderOverlayStart();
            this.saveStage1(formData, next);
        }
    };
    FgCmsContactPortraits.prototype.getStage1Data = function () {
        var _this = this;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
            type: "POST",
            url: _this.settings.stage1DataPath,
            data: { 'stage': '1', 'tableId': _this.tableId },
            success: function (response) {
                if (response.error == null) {
                    _this.loadWizardStage1(response);
                    FgInternal.pageLoaderOverlayStop();
                    _this.setWizardStage(response.stage);
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactPortraits.prototype.validateWizardStage1 = function () {
        var valid = true;
        $('.alert-danger').addClass('hide');
        $('#contacts_table_element_stage1 .has-error').removeClass('has-error');
        if ($('input:radio[name=saved_filter_type]').is(':checked') === false || !$('#contacts_table_element_stage1').valid()) {
            valid = false;
        }
        var filterType = $('input:radio[name=saved_filter_type]:checked').val();
        var filterId = $('#' + filterType + 'Filter').val();
        if (filterId === '') {
            $('#' + filterType + 'Filter').closest('.form-group').addClass('has-error');
            valid = false;
        }
        if (!valid) {
            $('.alert-danger').removeClass('hide');
        }
        return valid;
    };
    FgCmsContactPortraits.prototype.saveStage1 = function (formData, next) {
        var _this = this;
        formData.tableId = _this.tableId;
        $.ajax({
            type: "POST",
            url: _this.settings.stage1savepath,
            data: formData,
            success: function (response) {
                if (response.result == 'success') {
                    FgInternal.pageLoaderOverlayStop();
                    FgInternal.showToastr(response.message);
                    _this.tableId = response.tableId;
                    _this.event = 'edit';
                    if (next) {
                        $('#contacts-table-element-stage1').html('');
                        $('.nav-pills li:eq(1) a').tab('show');
                        _this.getStage2Data();
                    }
                    else {
                        _this.loadWizardStage1(response.data);
                        _this.setWizardStage(response.data.stage);
                    }
                    $('#contacts_table_element_event').val('edit');
                    $('#contacts_table_element_tableId').val(response.tableId);
                }
                else if (response.result == 'error') {
                    FgInternal.pageLoaderOverlayStop();
                    FgInternal.showToastr(_this.settings.saveFailedMsg);
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactPortraits.prototype.getStage2Data = function () {
        var _this = this;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
            type: "POST",
            url: _this.settings.stage2DataPath,
            data: { 'stage': '2', 'tableId': _this.tableId },
            success: function (response) {
                FgInternal.pageLoaderOverlayStop();
                if (response.error == null) {
                    _this.loadWizardStage2(response.data);
                    FgInternal.pageLoaderOverlayStop();
                    _this.setWizardStage(response.stage);
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactPortraits.prototype.loadWizardStage2 = function (formData) {
        var _this = this;
        _this.setCurrentStage(2);
        $('.alert-danger').addClass('hide');
        $('#form-stage-progressbar .progress-bar').css('width', '50%');
        $('#saved-contactlist-fields').html('');
        var htmlFinal = FGTemplate.bind('contacts_portrait_stage2_template', { "data": formData, 'tableId': _this.tableId });
        $('#contacts-portrait-element-stage2').html(htmlFinal);
        _this.renderFormButtons();
        _this.setStep2InitialElements(formData);
        $('select.selectpicker').selectpicker('render');
        _this.handleHelpMessages();
        FgFormTools.handleUniform();
        _this.handlePageTitleBar({ tab: ((this.log == '1') ? true : false) });
        _this.hideStage3Preview();
    };
    FgCmsContactPortraits.prototype.handleHelpMessages = function () {
        var portraitPerRow = parseInt($('#portraitPerRow').val());
        $('#portraitsPerRowMsg').html(portraitsPerRowMsg[portraitPerRow]);
        $('#portraitPerRow').on('change', function () {
            var portraitPerRow = parseInt($('#portraitPerRow').val());
            var rowsPerPage = parseInt($('#rowsPerPage').val());
            $('#portraitsPerRowMsg').html(portraitsPerRowMsg[portraitPerRow]);
            var rowsPerPageCount = (rowsPerPage * portraitPerRow) ? (rowsPerPage * portraitPerRow) : 0;
            $('#rowsPerPageCount').html(rowsPerPageCount);
        });
        var rowsPerPage = parseInt($('#rowsPerPage').val());
        var rowsPerPageCount = (rowsPerPage * portraitPerRow) ? (rowsPerPage * portraitPerRow) : 0;
        $('#rowsPerPageCount').html(rowsPerPageCount);
        $('#rowsPerPage').on('change', function () {
            var rowsPerPage = parseInt($('#rowsPerPage').val());
            var portraitPerRow = parseInt($('#portraitPerRow').val());
            var rowsPerPageCount = (rowsPerPage * portraitPerRow) ? (rowsPerPage * portraitPerRow) : 0;
            $('#rowsPerPageCount').html(rowsPerPageCount);
        });
    };
    FgCmsContactPortraits.prototype.setStep2InitialElements = function (formData) {
        if (formData.rowPerpage == null || formData.rowPerpage == '') {
            $('#rowsPerPage').val(1);
        }
        if (formData.portraitPerRow == null || formData.portraitPerRow == '') {
            $('#portraitPerRow').val(1);
        }
        if (formData.initialSortOrder == null || formData.initialSortOrder == '') {
            $('#sortingOrder-0').prop('checked', 'checked');
        }
        if ($('#contact-list-portrait-column-type').val() == '') {
            $('#contact-list-portrait-column-type>option[value="CONTACT_FIELD"]').prop('selected', true);
            var secondDpData = { datas: contactListColumnJson['CONTACT_FIELD'], defaultLang: defaultLang, clubLangDetails: clubLangDetails, selectedVal: 'CONTACT_FIELD' };
            var htmlFinal = FGTemplate.bind('contactListSecondDp', secondDpData);
            $('#contact-list-portrait-column-type').parent().siblings('.fg-dev-contact-secondDp').remove();
            $('#contact-list-portrait-column-type').parent().parent().append(htmlFinal);
            $('#fg-dev-contact-list-portrait-secondDp>optgroup>option[value="23"]').prop('selected', true);
        }
        FgDirtyFields.updateFormState();
    };
    FgCmsContactPortraits.prototype.validateWizardStage2 = function () {
        var valid = true;
        $('.alert-danger').addClass('hide');
        $('.fg-dev-error-msg').addClass('hide');
        $('#contacts_portrait_element_stage2 .has-error').removeClass('has-error');
        $('#contacts_portrait_element_stage2 .no-error').removeClass('no-error');
        if (($('#contacts_portrait_element_stage2 #contact-list-portrait-column-type').val()) == '') {
            $('#contact-list-portrait-column-type').closest('.form-group').addClass('has-error');
            valid = false;
        }
        if ($('#contacts_portrait_element_stage2 #fg-dev-contact-list-portrait-secondDp').length) {
            if (($('#contacts_portrait_element_stage2 #fg-dev-contact-list-portrait-secondDp').val()) == '') {
                $('#fg-dev-contact-list-portrait-secondDp').closest('.form-group').addClass('has-error');
                $('.fg-dev-sort-type').addClass('no-error');
                valid = false;
            }
        }
        $('#contacts_portrait_element_stage2 input[required]').each(function (index) {
            if (($(this).val()) == '') {
                $(this).closest('.form-group').addClass('has-error');
                valid = false;
            }
        });
        var rowsPerPage = parseInt($('#rowsPerPage').val());
        if ((rowsPerPage > parseInt($('#rowsPerPage').attr('max'))) || (rowsPerPage < parseInt($('#rowsPerPage').attr('min')))) {
            $('#rowsPerPage').closest('.form-group').addClass('has-error');
            $('.fg-dev-error-msg').removeClass('hide');
            valid = false;
        }
        if (!valid) {
            $('.alert-danger').removeClass('hide');
        }
        return valid;
    };
    FgCmsContactPortraits.prototype.saveWizardStage2 = function (next) {
        FgInternal.pageLoaderOverlayStart();
        if (this.validateWizardStage2()) {
            this.saveStage2(next);
        }
        else {
            FgInternal.pageLoaderOverlayStop();
        }
    };
    FgCmsContactPortraits.prototype.saveStage2 = function (next) {
        var dataArray = {};
        var _this = this;
        FgDirtyFields.updateFormState();
        if ($('.fg-dev-contact-list-portrait-secondDp').hasClass('fairgatedirty')) {
            $('#contact-list-portrait-column-type').addClass('fairgatedirty');
        }
        dataArray.jsonData = FgInternalParseFormField.formFieldParse('contacts_portrait_element_stage2');
        dataArray.table = _this.tableId;
        dataArray.colSize = colSize;
        $.ajax({
            type: "POST",
            url: _this.settings.stage2savepath,
            data: dataArray,
            success: function (response) {
                FgInternal.showToastr(response.flash);
                FgInternal.pageLoaderOverlayStop();
                $.fn.dirtyFields.markContainerFieldsClean($("#contacts_portrait_element_stage2"));
                $('.nav>li[data-target="wizard-stage3"]').removeClass('disabled');
                $('.nav>li[data-target="wizard-stage4"]').removeClass('disabled');
                if (next) {
                    $('#contacts-portrait-element-stage2').html('');
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
    FgCmsContactPortraits.prototype.getStage3Data = function () {
        var _this = this;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
            type: "POST",
            url: _this.settings.stage3DataPath,
            data: { 'stage': '3', 'portraitId': _this.tableId },
            success: function (response) {
                if (response.error == null) {
                    _this.setCurrentStage(3);
                    _this.setWizardStage(response.data.portraitElement.stage);
                    _this.loadWizardStage3(response.data);
                    _this.loadStage3Preview(response.stage);
                    _this.displayManageIcon();
                    $('.nav>li[data-target="wizard-stage4"]').removeClass('disabled');
                    if (response.data.length == 0) {
                        $('#contacts_table_element_save_and_next').removeAttr('disabled');
                    }
                }
                setTimeout(function () { pageTitleBarObj.setMoreTab(); FgInternal.pageLoaderOverlayStop(); }, 1000);
            },
            dataType: 'json'
        });
    };
    FgCmsContactPortraits.prototype.loadWizardStage3 = function (portraitData) {
        var _this = this;
        _this.setCurrentStage(3);
        $('.alert-danger').addClass('hide');
        $('#form-stage-progressbar .progress-bar').css('width', '75%');
        _this.handlePageTitleBar({ tab: ((this.log == '1') ? true : false), languageSwitch: true });
        $('#contacts-portrait-element-stage3').html('');
        this.settings.portraitCount = portraitData['portraitElement']['portraitPerRow'];
        this.settings.elementId = portraitData['portraitElement']['elementId'];
        this.settings.maxColumnCount = 3;
        var htmlData = this.stage3EditTemplateBuild(portraitData);
        $('#contacts-portrait-element-stage3').html(htmlData);
        FgGlobalSettings.handleLangSwitch();
        FgUtility.showTranslation(selectedLang);
        this.handleNumberButtons();
        FgFileUpload.init($('#image-uploader'), placeholderImageOption);
        $('select.selectpicker').selectpicker('render');
        this.sortColumnData();
        this.sortContainer();
        $('.fg-dev-multiple').uniform();
        _this.renderFormButtons();
        _this.setStepTitle();
        _this.manageFirstColumnLinebreakdisplay();
        _this.manageFirstContainerDeleteIcon();
        _this.preventOneMoreMultipleAssignment();
        _this.displayToggleLineBreak();
        _this.hideStage3Preview();
        _this.manageLanguageSwitch();
    };
    FgCmsContactPortraits.prototype.loadStage3Preview = function (stage) {
        if (parseInt(stage) >= 3) {
            var _this = this;
            $.ajax({
                type: "POST",
                url: _this.settings.stage3PreviewPath,
                data: { 'elementId': _this.settings.elementId, 'columnSize': _this.settings.columnSize },
                success: function (response) {
                    if (_.has(response, 'contactsData')) {
                        _this.handleContactPortraitElement(response);
                    }
                },
                dataType: 'json'
            });
        }
    };
    FgCmsContactPortraits.prototype.hideStage3Preview = function () {
        $('#contacts-portrait-element-stage3-preview').addClass('hide');
        $('#contacts-portrait-element-stage3-preview').html('');
    };
    FgCmsContactPortraits.prototype.handleContactPortraitElement = function (data) {
        var options = {
            boxId: 'contacts-portrait-element-stage3-preview',
            portraitWrapperData: data.portraitData,
            portraitContactsData: data.contactsData,
            portraitTemplate: data.portraitTemplate,
        };
        FgPortraitElement.initPreviewSettings(options);
    };
    FgCmsContactPortraits.prototype.stage3EditTemplateBuild = function (portraitData) {
        var stage3Html = FGTemplate.bind('contacts_portrait_element_stage3_template', { "containerDatas": portraitData['portraitElement'], 'portraitId': this.tableId });
        return stage3Html;
    };
    FgCmsContactPortraits.prototype.stage4EditTemplateBuild = function (formData, contactListFilterJson) {
        var filterType = formData['filterType'].toUpperCase();
        var invalid = true;
        var htmlContent = '';
        var inputFieldNameDefault = '';
        var addressType = '';
        switch (filterType) {
            case 'CONTACT_FIELD':
                inputFieldArray = formData['titleLang'];
                var catId = 'c-' + formData['catId'];
                var attrId = 'a-' + formData['attrId'];
                if (typeof contactListFilterJson[filterType]['fieldValue'][catId] != 'undefined') {
                    if (typeof contactListFilterJson[filterType]['fieldValue'][catId]['attrDetails'][attrId] != 'undefined') {
                        var attrArray = contactListFilterJson[filterType]['fieldValue'][catId]['attrDetails'][attrId];
                        addressType = attrArray['addressType'];
                        if (attrArray['isSystemField'] == 1) {
                            lang = clubLangDetails[defaultLang]['systemLang'];
                            if ((attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != '') && (attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != undefined)) {
                                labelNameDefault = attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']];
                            }
                            else {
                                labelNameDefault = attrArray['fieldName'];
                            }
                        }
                        else {
                            if ((attrArray['fieldNameLang'][defaultLang] != '') && (attrArray['fieldNameLang'][defaultLang] != undefined)) {
                                labelNameDefault = attrArray['fieldNameLang'][defaultLang];
                            }
                            else {
                                labelNameDefault = attrArray['fieldName'];
                            }
                        }
                        invalid = false;
                        selectedFieldLable = contactListFilterJson[filterType]['fieldName'] + ': ' + labelNameDefault;
                        inputFieldNameDefault = formData['title'];
                    }
                }
                break;
            case 'MEMBERSHIPS':
            case 'FED_MEMBERSHIPS':
                if (typeof contactListFilterJson[filterType] != 'undefined') {
                    selectedFieldLable = contactListFilterJson[filterType]['fieldName'];
                    inputFieldArray = formData['titleLang'];
                    invalid = false;
                }
                break;
            case 'WORKGROUPS':
            case 'FILTER_ROLES':
                if (typeof contactListFilterJson[filterType] != 'undefined') {
                    var remainingLabel_1 = '';
                    inputFieldArray = formData['titleLang'];
                    inputFieldNameDefault = formData['title'];
                    selectedFieldLable = contactListFilterJson[filterType]['fieldName'] + ': ';
                    if (formData['filterSubtypeIds'] == 'ALL') {
                        remainingLabel_1 = contactListFilterJson[filterType]['fieldValue']['f-0']['attrName'];
                        invalid = false;
                    }
                    else {
                        selectedWorkGroups = formData['filterSubtypeIds'].split(",");
                        if (selectedWorkGroups.length > 0) {
                            _.each(selectedWorkGroups, function (data) {
                                index = 'f-' + data;
                                if (typeof contactListFilterJson[filterType]['fieldValue'][index] != 'undefined') {
                                    remainingLabel_1 = remainingLabel_1 + contactListFilterJson[filterType]['fieldValue'][index]['attrName'] + ', ';
                                    invalid = false;
                                }
                            });
                            remainingLabel_1 = remainingLabel_1.slice(0, -2);
                        }
                    }
                    selectedFieldLable = selectedFieldLable + remainingLabel_1;
                }
                break;
            case 'TEAM_CATEGORY':
            case 'ROLE_CATEGORY':
            case 'FED_ROLE_CATEGORY':
            case 'SUBFED_ROLE_CATEGORY':
                if (typeof contactListFilterJson[filterType] != 'undefined') {
                    inputFieldArray = formData['titleLang'];
                    inputFieldNameDefault = formData['title'];
                    var catId_1 = (filterType == 'TEAM_CATEGORY') ? 'tc-' + formData['filterSubtypeIds'] : 'r-' + formData['filterSubtypeIds'];
                    if (typeof contactListFilterJson[filterType]['fieldValue'][catId_1] != 'undefined') {
                        selectedFieldLable = contactListFilterJson[filterType]['fieldName'] + ': ';
                        catDetails = contactListFilterJson[filterType]['fieldValue'][catId_1];
                        selectedFieldLable += (catDetails['attrNameLang'][defaultLang] != '' && typeof catDetails['attrNameLang'][defaultLang] != 'undefined') ? catDetails['attrNameLang'][defaultLang] : catDetails['attrName'];
                        invalid = false;
                    }
                }
                break;
        }
        if (!invalid) {
            var jsonVal = {
                type: 'old', dataKey: formData['filterId'], inputFieldArray: inputFieldArray, selectedFieldLable: selectedFieldLable, defaultLang: defaultLang, clubLangDetails: clubLangDetails, clubLanguages: clubLanguages,
                selectedFieldType: filterType, selectedField: formData['filterId'], addressType: addressType, sortOrder: formData['sortOrder'], inputFieldNameDefault: inputFieldNameDefault
            };
            htmlContent = FGTemplate.bind('contactListFilterData', jsonVal);
        }
        return htmlContent;
    };
    FgCmsContactPortraits.prototype.validateWizardStage4 = function () {
        $('#contacts_table_element_stage4').find('input:text[required]').each(function () {
            $(this).val($(this).val().trim());
        });
        var valid = $('#contacts_table_element_stage4').valid();
        return valid;
    };
    FgCmsContactPortraits.prototype.saveWizardStage4 = function (next) {
        var _this = this;
        if (_this.validateWizardStage4()) {
            _this.saveStage4(next);
        }
    };
    FgCmsContactPortraits.prototype.saveStage4 = function (finish) {
        var dataArray = {};
        var _this = this;
        _this.reorderElementList($('#contacts_table_element_stage4>#saved-contactlist-filter>div.sortables'), 'sortVal');
        dataArray.jsonData = FgInternalParseFormField.formFieldParse('contacts_table_element_stage4');
        dataArray.table = _this.tableId;
        dataArray.stage = 4;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
            type: "POST",
            url: _this.settings.stage4savepath,
            data: dataArray,
            success: function (response) {
                FgInternal.showToastr(response.flash);
                FgInternal.pageLoaderOverlayStop();
                $.fn.dirtyFields.markContainerFieldsClean($("#contacts_table_element_stage4"));
                if (finish) {
                    var finishUrl = $('#contacts_table_element_finish').attr('data-href');
                    $('#contacts_table_element_finish').remove();
                    window.location.href = finishUrl;
                }
                else {
                    _this.getStage4Data();
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactPortraits.prototype.getStage4Data = function () {
        var _this = this;
        FgInternal.pageLoaderOverlayStart();
        $('.nav-pills li:eq(3) a').tab('show');
        $.ajax({
            type: "POST",
            url: _this.settings.stage4DataPath,
            data: { 'stage': '4', 'tableId': _this.tableId },
            success: function (response) {
                if (response.error == null) {
                    _this.setCurrentStage(4);
                    _this.setWizardStage(response.stage);
                    _this.changeColorOnDelete();
                    _this.loadWizardStage4(response.data);
                    $('.nav>li[data-target="wizard-stage4"]').removeClass('disabled');
                    if (response.data.length == 0) {
                        $('#contacts_table_element_save_and_next').removeAttr('disabled');
                    }
                }
                setTimeout(function () { pageTitleBarObj.setMoreTab(); FgInternal.pageLoaderOverlayStop(); }, 1000);
            },
            dataType: 'json'
        });
    };
    FgCmsContactPortraits.prototype.loadWizardStage4 = function (formData) {
        var _this = this;
        $('.alert-danger').addClass('hide');
        $('#form-stage-progressbar .progress-bar').css('width', '100%');
        $('#saved-contactlist-filter').html('');
        _.each(formData, function (filterData, id) {
            $('#saved-contactlist-filter').append(_this.stage4EditTemplateBuild(filterData, contactListFilterJson));
        });
        _this.triggerEnterKey();
        FgGlobalSettings.handleLangSwitch();
        FgUtility.showTranslation(selectedLang);
        FgTooltip.init();
        _this.setStepTitle();
        _this.setFilterElementsSortable();
        FormValidation.init('contacts_table_element_stage4');
        _this.renderFormButtons();
        _this.setTranslationTabError('contacts_table_element_stage4');
        _this.handlePageTitleBar({ tab: ((this.log == '1') ? true : false), languageSwitch: true });
        _this.hideStage3Preview();
    };
    FgCmsContactPortraits.prototype.connectAutocomplete = function (contactType, selectedContacts) {
        var selectedIds = [];
        if (selectedContacts != null) {
            _.each(selectedContacts, function (name, id) {
                selectedIds.push({ 'id': id, 'title': name });
            });
        }
        $('#contacts_table_' + contactType + '_contacts').fbautocomplete({
            url: FgInternalVariables.topNavSearchUrl,
            maxItems: 0,
            useCache: false,
            selected: selectedIds,
            onItemSelected: function ($obj, itemId, selected) {
                var alreadySelectedvalues = _.compact($('#contacts_table_' + contactType + '_contacts_data').val().split(','));
                alreadySelectedvalues.push(itemId);
                $('#contacts_table_' + contactType + '_contacts_data').val(_.uniq(alreadySelectedvalues).join()).trigger('change');
            },
            onItemRemoved: function ($obj, itemId) {
                var alreadySelectedvalues = _.compact($('#contacts_table_' + contactType + '_contacts_data').val().split(','));
                alreadySelectedvalues = _.reject(alreadySelectedvalues, function (v) { return v == itemId; });
                $('#contacts_table_' + contactType + '_contacts_data').val(_.uniq(alreadySelectedvalues).join()).trigger('change');
            }
        });
    };
    FgCmsContactPortraits.prototype.renderFormButtons = function () {
        var _this = this;
        var stage = _this.getCurrentStage();
        $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_cancel,#contacts_table_element_discard,#contacts_table_element_back,#contacts_table_element_finish').addClass('hide');
        if (stage == 1) {
            if (_this.event == 'create') {
                $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_cancel').removeClass('hide');
            }
            else {
                $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_discard').removeClass('hide');
            }
        }
        else if (stage == 2) {
            $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_back').removeClass('hide');
        }
        else if (stage == 3) {
            $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_back').removeClass('hide');
        }
        else if (stage == 4) {
            $('#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_finish,#contacts_table_element_back').removeClass('hide');
        }
        _this.initDirtyFields();
    };
    FgCmsContactPortraits.prototype.initDirtyFields = function () {
        var _this = this;
        var stage = _this.getCurrentStage();
        var formId = 'contacts_table_element_stage1';
        if (stage == 2) {
            formId = 'contacts_portrait_element_stage2';
        }
        else if (stage == 3) {
            formId = 'contacts_portrait_element_stage3_form';
        }
        else if (stage == 4) {
            formId = 'contacts_table_element_stage4';
        }
        this.setFormDirty(formId, 0);
        FgDirtyFields.init(formId, { enableDiscardChanges: false, setInitialHtml: false, formChangeCallback: function (isDirty) { _this.setFormDirty(formId, isDirty); } });
    };
    FgCmsContactPortraits.prototype.setFormDirty = function (formId, isDirty) {
        if (isDirty) {
            $('#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_save_and_next').removeAttr('disabled');
        }
        else {
            $('#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_save_and_next').attr('disabled', 'disabled');
        }
    };
    FgCmsContactPortraits.prototype.setStepTitle = function () {
        var stage = this.getCurrentStage();
        var stepTrans = this.settings['stepTrans'];
        $('.portlet-title .step-title').html(stepTrans.replace('%s%', stage));
    };
    FgCmsContactPortraits.prototype.setWizardStage = function (stage) {
        if (stage == 'stage1') {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').removeClass('disabled');
            $('.nav>li[data-target="wizard-stage3"]').addClass('disabled');
            $('.nav>li[data-target="wizard-stage4"]').addClass('disabled');
        }
        else if (stage == 'stage2') {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').removeClass('disabled');
            $('.nav>li[data-target="wizard-stage4"]').addClass('disabled');
        }
        else if (stage == 'stage3') {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').addClass('done');
            $('.nav>li[data-target="wizard-stage4"]').removeClass('disabled');
        }
        else if (stage == 'stage4') {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').addClass('done');
            $('.nav>li[data-target="wizard-stage4"]').addClass('done');
        }
        var currentStage = this.getCurrentStage();
        if (currentStage == 1) {
            $('.nav>li[data-target="wizard-stage1"]').removeClass('done').addClass('active');
        }
        else if (currentStage == 2) {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').removeClass('done').addClass('active');
        }
        else if (currentStage == 3) {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').removeClass('done').addClass('active');
        }
        else if (currentStage == 4) {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').addClass('done');
            $('.nav>li[data-target="wizard-stage4"]').removeClass('done').addClass('active');
        }
    };
    FgCmsContactPortraits.prototype.reorderElementList = function (list, sortElement) {
        var z = 0;
        list.each(function (order, element) {
            if (!$(this).hasClass('fg-inactiveblock')) {
                var sort = ++z;
                $(this).find('.' + sortElement).val(sort).attr('value', sort);
            }
        });
    };
    FgCmsContactPortraits.prototype.setFilterElementsSortable = function () {
        var _this = this;
        $("#saved-contactlist-filter").sortable({
            items: "> div.sortables",
            containment: "body",
            handle: ".fg-dev-field-sort-handle",
            stop: function (event, ui) {
                _this.reorderElementList($('#contacts_table_element_stage4 #saved-contactlist-filter>div.sortables'), 'sortVal');
                ui.item.find('.fg-dev-sortOrder').trigger('change');
                FgDirtyFields.updateFormState();
            }
        });
    };
    ;
    FgCmsContactPortraits.prototype.removeNewRows = function (container) {
        container.off('click', '.new_row_rmv');
        container.on('click', '.new_row_rmv', function (event) {
            FgDirtyFields.removeFields($(this).parent());
            $(this).parent().remove();
        });
    };
    ;
    FgCmsContactPortraits.prototype.changeColorOnDelete = function () {
        $('form').off('click', 'input[data-inactiveblock=changecolor]');
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function () {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('div#' + parentId);
            $(parentDiv).toggleClass('fg-inactiveblock');
        });
    };
    FgCmsContactPortraits.prototype.setTranslationTabError = function (formId) {
        FgLanguageSwitch.checkMissingTranslation(defaultLang, formId);
    };
    FgCmsContactPortraits.prototype.triggerEnterKey = function () {
        $("form input").off('keypress');
        $("form input").on('keypress', function (e) {
            if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                $('#contacts_table_element_save').click();
                return false;
            }
            else {
                return true;
            }
        });
    };
    FgCmsContactPortraits.prototype.connectDropdownEventsStage4 = function () {
        $('body').on('change', '.contact-list-table-filter-type', function () {
            if ($(this).val() != 'default') {
                $('#saveContactListFilterPopup').attr("disabled", "disabled");
                if (contactListFilterJson[$(this).val()]['fieldValue'] == undefined) {
                    $('#saveContactListFilterPopup').removeAttr("disabled");
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                }
                else {
                    var secondDpData = { datas: contactListFilterJson[$(this).val()], defaultLang: defaultLang, clubLangDetails: clubLangDetails, selectedVal: $(this).val() };
                    var htmlFinal = FGTemplate.bind('contactListFilterSecondDp', secondDpData);
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                    $(this).parent().parent().append(htmlFinal);
                    $('#fg-dev-contact-list-table-filter-secondDp').selectpicker();
                    FgUtility.handleSelectPicker();
                }
            }
            else if ($(this).val() == 'default') {
                $('#saveContactListFilterPopup').attr("disabled", "disabled");
                $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
            }
        });
        $('body').on('change', 'select.fg-dev-contact-list-table-filter-secondDp', function () {
            if ($(this).val() != 'default') {
                $('#saveContactListFilterPopup').removeAttr("disabled");
            }
            else {
                $('#saveContactListFilterPopup').attr("disabled", "disabled");
            }
        });
    };
    FgCmsContactPortraits.prototype.renderContainer = function (containerDatas) {
        var containerWidth = Math.floor(this.settings.columnSize / this.settings.portraitCount);
        return FGTemplate.bind('portrait_container_template', { "columnDatas": containerDatas, 'portraitId': this.tableId, 'containerWidth': containerWidth });
    };
    FgCmsContactPortraits.prototype.renderColumn = function (columnDatas) {
        return FGTemplate.bind('portrait_column_template', { "displayDatas": columnDatas, 'portraitId': this.tableId });
    };
    FgCmsContactPortraits.prototype.renderColumnData = function (displayDatas) {
        var linkContactFields = [];
        _.each(contactListColumnJson['CONTACT_FIELD']['fieldValue'], function (datas, catKey) {
            _.each(datas['attrDetails'], function (attrValues, attrKey) {
                if (attrValues['attrType'] == 'url') {
                    linkContactFields.push(attrValues);
                }
            });
        });
        return FGTemplate.bind('column_data_template', { "optionDatas": displayDatas, 'portraitId': this.tableId, 'linkContactFields': linkContactFields, 'contactListColumnJson': contactListColumnJson, 'defaultLang': defaultLang, 'contactFieldDetail': contactFieldDetails });
    };
    FgCmsContactPortraits.prototype.renderOptionTemplate = function (optionDatas) {
        return FGTemplate.bind('data_option_template', { "data": optionDatas, 'portraitId': this.tableId });
    };
    FgCmsContactPortraits.prototype.connectFilterStage4AddButton = function () {
        var _this = this;
        $(document).on('click', '#saveContactListFilterPopup', function (event) {
            $('#contactListAddFilterPopup').modal('hide');
            var selectedFieldType = ($('.contact-list-table-filter-type').val() != '') ? $('.contact-list-table-filter-type').val() : '';
            var selectedField = ($('.fg-dev-contact-list-table-filter-secondDp').val() != '') ? $('.fg-dev-contact-list-table-filter-secondDp').val() : '';
            var selectedGroupValue = ($('.fg-dev-contact-list-table-filter-secondDp :selected').parent().attr('value') != '') ? $('.fg-dev-contact-list-table-filter-secondDp :selected').parent().attr('value') : '';
            if ($(".fg-dev-contact-list-table-filter-secondDp").attr('title') !== undefined) {
                var selectedFieldText = '';
            }
            else {
                var selectedFieldText = $(".fg-dev-contact-list-table-filter-secondDp option[value=" + selectedField + "]").text();
            }
            var htmlFinal = _this.getFilterRowHtmlContactList(selectedFieldType, selectedField, selectedGroupValue, contactListFilterJson, selectedFieldText);
            $('#saved-contactlist-filter').append(htmlFinal);
            FgDirtyFields.addFields(htmlFinal);
            _this.setFilterElementsSortable();
            _this.removeNewRows($('#contacts_table_element_stage4'));
            _this.triggerEnterKey();
            FgGlobalSettings.handleLangSwitch();
            FgUtility.showTranslation(selectedLang);
            FgTooltip.init();
        });
    };
    FgCmsContactPortraits.prototype.containerEdit = function () {
        var _this = this;
        $("body").on('click', '.editContainerpopup', function (event) {
            event.stopImmediatePropagation();
            var currentPortraitId = $(this).attr('container-element-id');
            var containeType = $(this).attr('container-type');
            var portraitPerRow = _this.settings.portraitCount;
            var containerColumnSize = _this.settings.columnSize;
            var maxCount = Math.floor(containerColumnSize / portraitPerRow);
            var defaultColumnCount = $(this).attr('old_count');
            var headerTitle = (containeType == 'containerAdd') ? _this.settings.translations.createContainerHeader : _this.settings.translations.editContainerHeader;
            var popupContent = FGTemplate.bind('editcontainerpopup', {
                defaultColumnCount: defaultColumnCount,
                maxColumnCount: maxCount,
                portraitId: currentPortraitId,
                containerType: containeType,
                headerTitle: headerTitle,
                containerId: $(this).attr('container-id')
            });
            FgModelbox.showPopup(popupContent);
        });
    };
    FgCmsContactPortraits.prototype.createContainer = function () {
        var _this = this;
        var countDifference = 0;
        var multiClick = 1;
        $("body").on('click', '.fg-dev-datasave', function (ele) {
            ele.stopImmediatePropagation();
            var portraitPerRow = _this.settings.portraitCount;
            var containerColumnSize = Math.floor(parseInt(_this.settings.columnSize) / parseInt(_this.settings.portraitCount));
            var popupType = $("input[name='containerpopupType']").val();
            var columnCount = $("input[name='columnCount']").val();
            var oldCount = $("#oldColumnCount").val();
            var containerId = $("#clickedContainer").val();
            if (popupType == 'containerAdd') {
                var rand = $.now();
                var sortOrder = $(".fg-portrait-container").length + 1;
                var objectGraph = { 'containerId': 'newContainer_' + rand, 'sortOrder': sortOrder, 'actionType': 'add', 'columnSize': columnCount, 'columnCount': columnCount, 'columns': {} };
                var columnWidth = Math.floor(containerColumnSize / columnCount);
                for (var i = 1; i <= columnCount; i++) {
                    var columnDummyId = 'newColumn_' + parseInt(Math.random() * Math.pow(10, 5));
                    var actualWidth = parseInt(columnWidth) * 2;
                    objectGraph.columns[columnDummyId] = { 'columnId': columnDummyId, 'columnSize': actualWidth, 'gridSize': columnWidth, 'container': 'newContainer_' + rand, 'sortOrder': i };
                }
                var containerData = _this.renderContainer(objectGraph);
                $(".fg-cms-page-elements-container").append(containerData);
                FgDirtyFields.addFields(containerData);
            }
            else {
                var columnData = '';
                if (oldCount < columnCount) {
                    var addedCount = parseInt(columnCount) - parseInt(oldCount);
                    var columnWidth = Math.floor(containerColumnSize / columnCount);
                    var objectGraph = { 'container': containerId, 'columnData': {} };
                    for (var i = 1; i <= addedCount; i++) {
                        var columnDummyId = 'new_' + parseInt(Math.random() * Math.pow(10, 5));
                        var gridSize = columnWidth;
                        var actualWidth = columnWidth * 2;
                        objectGraph.columnData[columnDummyId] = { 'columnId': columnDummyId, 'columnSize': actualWidth, 'gridSize': gridSize, 'container': containerId, 'sortOrder': parseInt(oldCount) + i };
                    }
                    columnData = _this.renderColumnOnly(objectGraph);
                    $("#pagecontainer-" + containerId).find(".editContainerpopup").attr('old_count', columnCount);
                    $("#pagecontainer-" + containerId).append(columnData);
                    _this.resizeColumnWidth(containerId);
                }
                else {
                    var reducedCount = parseInt(oldCount) - parseInt(columnCount);
                    var columnIds_1 = [];
                    var currentColumnCount = $("#pagecontainer-" + containerId).find(".fg-portrait-column:visible").length;
                    var removeColumnFrom_1 = columnCount - 1;
                    var index_1 = 1;
                    $("#pagecontainer-" + containerId).find(".fg-portrait-column:visible").each(function (i) {
                        if (i >= (removeColumnFrom_1)) {
                            columnIds_1[index_1] = $(this).attr("column-id");
                            index_1++;
                        }
                    });
                    var firstId_1 = columnIds_1[1];
                    delete (columnIds_1[1]);
                    $.each(columnIds_1, function (key, columnId) {
                        if (typeof columnId !== 'undefined') {
                            _this.moveColumnData(columnId, firstId_1, containerId);
                        }
                    });
                    _this.resizeColumnWidth(containerId);
                    $("#pagecontainer-" + containerId).find(".editContainerpopup").attr('old_count', columnCount);
                    _this.sortColumnData();
                    _this.reorderDataList();
                    FgDirtyFields.updateFormState();
                }
            }
            _this.displayManageIcon();
            FgModelbox.hidePopup();
            _this.sortContainer();
            _this.sortColumnData();
        });
    };
    FgCmsContactPortraits.prototype.containerDelete = function () {
        $("body").on('click', '.fg-dev-temp-delete', function (ele) {
            ele.stopImmediatePropagation();
            $(this).parents().eq(1).remove();
        });
    };
    FgCmsContactPortraits.prototype.sortContainer = function () {
        var startIndex = 0;
        var _this = this;
        var containerOption = {
            tolerance: 'pointer',
            items: "> div.fg-portrait-container",
            stop: function (event, ui) {
                _this.reorderDataList();
                _this.showAllDeleteIcon();
                _this.manageFirstContainerDeleteIcon();
                FgDirtyFields.updateFormState();
            }
        };
        this.sortableEvent('.fg-portraite-main-container', containerOption);
    };
    FgCmsContactPortraits.prototype.sortableEvent = function (identifier, sortoptions) {
        this.sortSetting = $.extend(true, {}, this.defaultSortOptions, sortoptions);
        $(identifier).sortable(this.sortSetting);
    };
    FgCmsContactPortraits.prototype.connectColumnStage3AddButton = function () {
        var _this = this;
        $(document).on('click', '#saveContactListColumnPopup', function (event) {
            FgModelbox.hidePopup();
            var selectedFieldType = ($('.contact-list-table-column-type').val() != '') ? $('.contact-list-table-column-type').val() : '';
            var selectedField = ($('.fg-dev-contact-list-table-secondDp').val() != '') ? $('.fg-dev-contact-list-table-secondDp').val() : '';
            var selectedGroupValue = ($('.fg-dev-contact-list-table-secondDp :selected').parent().attr('value') != '') ? $('.fg-dev-contact-list-table-secondDp :selected').parent().attr('value') : '';
            var selectedColumns = $('#selectedcolumnId').val();
            var selectedContainer = $('#selectedcontainerId').val();
            var htmlFinal = _this.getRowHtmlContactList(selectedFieldType, selectedField, selectedGroupValue, contactListColumnJson, selectedContainer, selectedColumns);
            $("[portrait_column_id=" + selectedColumns + "]").append(htmlFinal);
            FgDirtyFields.addFields(htmlFinal);
            _this.handleNumberButtons();
            $('.fg-dev-multiple').uniform();
            $('select.selectpicker').selectpicker('render');
            FgFileUpload.init($('#image-uploader'), placeholderImageOption);
            _this.manageFirstColumnLinebreakdisplay();
            _this.sortColumnData();
        });
    };
    FgCmsContactPortraits.prototype.sortColumnData = function () {
        var _this = this;
        var fromContainer = '';
        var toContainer = '';
        var fromColumn = '';
        var toColumn = '';
        var columnDataOption = {
            tolerance: 'pointer',
            connectWith: '.portrait-data-sort',
            dropOnEmpty: true,
            items: "> li.portrait-data-field",
            start: function (event, ui) {
                fromColumn = $(ui.item).parent().attr('portrait_column_id');
                fromContainer = $(ui.item).parent().attr('container-id');
            },
            stop: function (event, ui) {
                toColumn = $(ui.item).parent().attr('portrait_column_id');
                toContainer = $(ui.item).parent().attr('container-id');
                $(ui.item).find("[data-key]").not(".fg-data-delete").each(function () {
                    var keyValue = $(this).attr('data-key');
                    var newKeyValue = keyValue.replace(fromContainer + ".column." + fromColumn, toContainer + ".column." + toColumn);
                    $(this).attr('data-key', newKeyValue);
                });
                if (fromContainer != toContainer) {
                    $(ui.item).find(".portraitDataColumn").attr('value', toColumn).trigger('change');
                }
                else if (fromColumn != toColumn) {
                    $(ui.item).find(".portraitDataColumn").attr('value', toColumn).trigger('change');
                }
                _this.reorderDataList();
                _this.manageFirstColumnLinebreakdisplay();
                FgDirtyFields.updateFormState();
            }
        };
        this.sortableEvent('.portrait-data-sort', columnDataOption);
    };
    FgCmsContactPortraits.prototype.handleAddLabel = function () {
        var _this = this;
        $('body').off('click', '.fg-add-label');
        $('body').on('click', '.fg-add-label', function (event) {
            var htmlFinal = FGTemplate.bind('portraitFieldDataLabel', { "clubLanguages": clubLanguages, 'clubDefaultLang': defaultLang, 'portraitId': this.tableId, 'labelId': $(this).attr('id'), 'jsonData': $(this).attr('json-data'), 'dataId': $(this).attr('data-id') });
            FgModelbox.showPopup(htmlFinal);
            FgDirtyFields.updateFormState();
        });
    };
    FgCmsContactPortraits.prototype.handleLabelLanguageSwitch = function () {
        var _this = this;
        $('body').off('click', '#editPageTitleBtn');
        $('body').on('click', '#editPageTitleBtn', function () {
            var data = {};
            $.each($('.cms-page-title-input'), function (i, v) {
                data[$(v).attr('data-lang')] = $.trim($("<span>" + $(v).val() + "</span>").text());
            });
            var labelId = $('#labelId').val();
            var dataId = $('#dataId').val();
            var defaultLabel = $('#fieldlabel_' + defaultLang).val();
            var labelText = $("<span>" + defaultLabel + "</span>").text();
            var htmlString = '';
            if ($.trim(labelText) != '') {
                htmlString = '<span class="fg-badge fg-badge-blue3">' + labelText + '</span>';
            }
            else {
                htmlString = '<i class="fg-plus-circle fa fa-2x"></i>' + _this.settings.translations.addLabelText;
            }
            $('#' + labelId).html(htmlString);
            $('#' + labelId).attr('json-data', JSON.stringify(data));
            $('#portraitDataLabel' + dataId).val(JSON.stringify(data));
            FgModelbox.hidePopup();
            FgDirtyFields.updateFormState();
            _this.manageLanguageSwitch();
        });
        $('body').on('click', '.modal-content .pageTitle-popup-lang-switch button[data-elem-function=switch_lang]', function (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
            var selectedLang = $(this).attr('data-selected-lang');
            $('.modal-content .pageTitle-popup-lang-switch button[data-elem-function=switch_lang]').removeClass('active');
            $(this).addClass('active');
            FgUtility.showTranslation(selectedLang);
        });
    };
    FgCmsContactPortraits.prototype.manageColumnWidth = function () {
        var _this = this;
        $('body').on('click', '.fg-manage-columnwidth', function () {
            var clicked_this = $(this);
            var containerWidth = Math.floor(_this.settings.columnSize / _this.settings.portraitCount);
            var currentWidth = $('#' + $(this).attr('data_column_id')).attr('column_width_value');
            var newWidth = currentWidth;
            var total = 0;
            $(this).parents().eq(2).find('.fg-portrait-column:visible').each(function (e) {
                total = total + parseInt($(this).attr('column_width_value'));
            });
            var columnObj = clicked_this.attr('data_column_id');
            if (clicked_this.attr('width-type') == 'increase') {
                var sumWidth = parseInt(total + 1);
                if (sumWidth <= containerWidth) {
                    newWidth = parseInt(currentWidth) + 1;
                    $('#' + columnObj).attr('column_width_value', newWidth);
                }
            }
            else {
                if (currentWidth - 1 >= 1) {
                    newWidth = (parseInt(currentWidth) - 1);
                    $('#' + columnObj).attr('column_width_value', newWidth);
                    clicked_this.parent().find('.fg-right').show();
                    clicked_this.parent().find('.fg-left').show();
                }
            }
            if (newWidth < containerWidth) {
                clicked_this.parent().find('.fg-right').show();
                clicked_this.parent().find('.fg-left').show();
            }
            if (newWidth >= containerWidth) {
                clicked_this.parent().find('.fg-right').hide();
                clicked_this.parent().find('.fg-left').show();
            }
            if (newWidth <= 1) {
                clicked_this.parent().find('.fg-left').hide();
                clicked_this.parent().find('.fg-right').show();
            }
            var currentGrid = parseInt(currentWidth);
            var newGrid = parseInt(newWidth);
            var viewableWidth = currentWidth * 2;
            var addableWidth = newGrid * 2;
            $('#' + columnObj).addClass('col-sm-' + addableWidth + ' fg-grid-col-' + newGrid + ' col-' + newGrid).removeClass('col-sm-' + viewableWidth + ' fg-grid-col-' + currentGrid);
            var colId = $('#' + columnObj).attr('column-id');
            $('#' + columnObj).find('input[name=portraitColumnSize_' + colId + ']').attr('value', newWidth).val(newWidth).trigger('change');
            FgDirtyFields.updateFormState();
            _this.displayManageIcon();
        });
    };
    FgCmsContactPortraits.prototype.saveWizardStage3 = function (next) {
        var _this = this;
        _this.saveStage3(next);
    };
    FgCmsContactPortraits.prototype.saveStage3 = function (next) {
        var dataArray = {};
        var _this = this;
        _this.reorderDataList();
        if ($(".fg-dev-uploded-profile-pic").length > 0) {
            $(".fg-dev-placeholder-image").val($(".fg-dev-uploded-profile-pic").val());
            $(".fg-dev-placeholder-image-temp").val($(".fg-dev-uploded-profile-pic-temp").val());
            FgDirtyFields.updateFormState();
        }
        dataArray.jsonData = FgInternalParseFormField.formFieldParse('contacts_portrait_element_stage3_form');
        dataArray.table = _this.tableId;
        dataArray.stage = 3;
        FgInternal.pageLoaderOverlayStart();
        if (clickCount == 0) {
            clickCount++;
            $.ajax({
                type: "POST",
                url: _this.settings.stage3savepath,
                data: dataArray,
                success: function (response) {
                    clickCount = 0;
                    FgInternal.showToastr(response.flash);
                    FgInternal.pageLoaderOverlayStop();
                    $.fn.dirtyFields.markContainerFieldsClean($("#contacts_portrait_element_stage3_form"));
                    if (next) {
                        _this.getStage4Data();
                    }
                    else {
                        _this.getStage3Data();
                    }
                },
                dataType: 'json'
            });
        }
        
        _this.preventOneMoreMultipleAssignment();
    };
    FgCmsContactPortraits.prototype.reorderDataList = function () {
        var i = 1;
        var j = 1;
        var k = 1;
        $('.fg-portrait-container').each(function () {
            var thisItem = $(this);
            thisItem.find('.fg-container-sort-order').attr('value', i);
            i++;
            j = 1;
            thisItem.find('.fg-portrait-column:visible').each(function () {
                var _this = $(this);
                _this.find('.fg-column-sort-order').attr('value', j).trigger('change');
                j++;
                k = 1;
                _this.find('.portrait-data-field').each(function () {
                    $(this).find('.fg-data-sort').attr('value', k).trigger('change');
                    k++;
                });
            });
        });
        FgDirtyFields.updateFormState();
    };
    FgCmsContactPortraits.prototype.moveColumnData = function (oldColumnId, newColumnId, containerId) {
        var _this = this;
        var htmlContent = $("#column-" + oldColumnId).html();
        var dummy = htmlContent;
        var $div = $('<div>').html(htmlContent);
        $div.find("[data-key]").each(function () {
            var keyValue = $(this).attr('data-key');
            var keyId = $(this).attr('id');
            var newKeyValue = keyValue.replace(".column." + oldColumnId, ".column." + newColumnId);
            $(this).attr('data-key', newKeyValue);
        });
        $div.find('.portrait-data-field').removeClass('ui-sortable-handle');
        $div.find('.fg-sortable-list').removeClass('ui-sortable');
        $div.find(".portraitColumns").attr('value', newColumnId);
        var dataContent = $div.find(".portrait-data-sort").html();
        if (isNaN(parseInt(oldColumnId))) {
            $("#column-" + oldColumnId).remove();
        }
        else {
            $("#column-" + oldColumnId).hide();
        }
        $("#column-" + oldColumnId).find(".fg-dev-column-delete").attr('value', 1);
        FgDirtyFields.updateFormState();
        $("#column-" + newColumnId).find(".portrait-data-sort").append(dataContent);
    };
    FgCmsContactPortraits.prototype.renderColumnOnly = function (columnDatas) {
        return FGTemplate.bind('portrait_column_template_only', { "columnDatas": columnDatas, 'portraitId': this.tableId, 'contactFieldDetail': contactFieldDetails });
    };
    FgCmsContactPortraits.prototype.resizeColumnWidth = function (containerId) {
        var _this = this;
        var containerWidth = Math.floor(_this.settings.columnSize / _this.settings.portraitCount);
        var columnCount = $("#pagecontainer-" + containerId).find(".fg-portrait-column:visible").length;
        var columnWidth = (Math.floor(containerWidth / columnCount));
        var columnGrid = columnWidth;
        if (columnWidth == 0) {
            columnWidth++;
        }
        $("#pagecontainer-" + containerId).find(".fg-portrait-column:visible").each(function () {
            var currentWidth = $(this).attr('column_width_value');
            var currentGrid = parseInt(currentWidth);
            var viewableWidth = currentWidth * 2;
            var addableWidth = columnWidth * 2;
            var columnId = $(this).find(".portraitColumns").attr('value');
            $(this).attr('column_width_value', columnWidth);
            var addClass = 'col-sm-' + addableWidth + ' fg-grid-col-' + columnGrid;
            var removeClass = 'col-sm-' + viewableWidth + ' fg-grid-col-' + currentGrid;
            $(this).removeClass(removeClass).addClass(addClass);
            $('#column-' + columnId).find('input[name=portraitColumnSize_' + columnId + ']').attr('value', columnWidth).val(columnWidth).trigger('change');
        });
        FgDirtyFields.updateFormState();
    };
    FgCmsContactPortraits.prototype.displayManageIcon = function () {
        var containerWidth = Math.floor(this.settings.columnSize / this.settings.portraitCount);
        $(".fg-portrait-container").each(function () {
            var clickedContainer = $(this);
            var columnTotalWidth = 0;
            clickedContainer.find('.fg-portrait-column:visible').each(function () {
                var clickedColumn = $(this);
                var columnWidth = $(this).attr('column_width_value');
                columnTotalWidth = columnTotalWidth + parseInt(columnWidth);
                (parseInt(clickedColumn.attr('column_width_value')) <= 1) ? clickedColumn.find('.fg-left').hide() : clickedColumn.find('.fg-left').show();
            });
            if (containerWidth > columnTotalWidth) {
                clickedContainer.find('.fg-right').show();
            }
            else {
                clickedContainer.find('.fg-right').hide();
            }
        });
    };
    FgCmsContactPortraits.prototype.manageLanguageSwitch = function () {
        var _this = this;
        $('body').off('click', '.fg-dev-portrait .btlang');
        $('body').on('click', '.fg-dev-portrait .btlang', function (e) {
            var buttonThis = $(this);
            $(".fg-add-label").each(function () {
                var jsonData = $(this).attr("json-data");
                var jsonArray = $.parseJSON(jsonData);
                var language = buttonThis.attr('data-selected-lang');
                var htmlString = '';
                console.log(jsonArray);
                console.log(language, 'val:', jsonArray[language]);
                if ((language in jsonArray) && (jsonArray[language] != '' && jsonArray[language] != null)) {
                    htmlString = '<span class="fg-badge fg-badge-blue3">' + jsonArray[language] + '</span>';
                }
                else {
                    htmlString = '<i class="fg-plus-circle fa fa-2x"></i>' + _this.settings.translations.addLabelText;
                }
                $(this).html(htmlString);
            });
        });
    };
    FgCmsContactPortraits.prototype.manageFirstColumnLinebreakdisplay = function () {
        var _this = this;
        $('.fg-portrait-column:visible').each(function () {
            var _that = $(this);
            _that.find('li.list-group-item').each(function (i) {
                if (i == 0) {
                    $(this).find(".fg-dev-line-break").hide();
                    $(this).find(".fa-break-icon").hide();
                }
                else {
                    $(this).find(".fg-dev-line-break").show();
                    var iconDisplay = $(this).find(".input-number").val();
                    if (iconDisplay == 2) {
                        $(this).find('.fa-long-arrow-down').show();
                    }
                    else if (iconDisplay == 0) {
                        $(this).find('.fa-level-up').show();
                    }
                }
                if ($(this).find(".fg-dev-display-type").length > 0 && $(this).find(".fg-dev-display-type").val() == 'image') {
                    $(this).find(".fg-dev-line-break").hide();
                    $(this).find(".fa-break-icon").hide();
                }
            });
        });
    };
    FgCmsContactPortraits.prototype.manageFirstContainerDeleteIcon = function () {
        var _this = this;
        $('div.columnboxsortable:first').find('.fg-dev-delete').hide();
    };
    FgCmsContactPortraits.prototype.showAllDeleteIcon = function () {
        $('div.columnboxsortable').find('.fg-dev-delete').show();
    };
    FgCmsContactPortraits.prototype.preventOneMoreMultipleAssignment = function () {
        $('body').on('click', '.fg-dev-multiple', function () {
            var checked = false;
            if ($(this).is(":checked")) {
                checked = true;
            }
            $(".fg-dev-multiple").prop('checked', false);
            if (checked) {
                $(this).prop('checked', true);
            }
            $('.fg-dev-multiple').uniform();
            FgDirtyFields.updateFormState();
        });
        $('body').on('click', '.fg-dev-multiple-label', function () {
            $(this).parent('.fg-checkbox').find('.fg-dev-multiple').trigger('click');
        });
    };
    FgCmsContactPortraits.prototype.displayToggleLineBreak = function () {
        $('body').on('change', '.fg-dev-display-type', function () {
            if ($(this).val() == 'image') {
                $(this).parents('.list-group-item').find('.fg-dev-line-break').hide();
            }
            else {
                $(this).parents('.list-group-item').find('.fg-dev-line-break').show();
            }
        });
    };
    return FgCmsContactPortraits;
}());
