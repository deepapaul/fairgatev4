var FgCmsContactsTable = (function () {
    function FgCmsContactsTable(options) {
        this.defaultSettings = {
            stage1savepath: '',
            stage2savepath: '',
        };
        this.tableId = options.tableId;
        this.event = options.event;
        this.currentStage = (options.currentStage > 0) ? options.currentStage : 1;
        this.connectButtonClick();
        this.connectStageClick();
        this.settings = $.extend(true, {}, this.defaultSettings, options);
    }
    FgCmsContactsTable.prototype.connectButtonClick = function () {
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
        $('#fg-dev-addNewTableColumn').on('click', function () {
            var jsonData = { contactListColumnJson: contactListColumnJson, defaultLang: defaultLang };
            var htmlFinal = FGTemplate.bind('contactListNewColumnPopup', jsonData);
            $('.fg-modal-contact-list-column-content').html(htmlFinal);
            $('#contactListAddColumnPopup').modal('show');
            $('select.selectpicker').selectpicker('render');
            $('#saveContactListColumnPopup').attr("disabled", "disabled");
            _this.setelementsSortable();
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
                if (contactListColumnJson[$(this).val()]['fieldValue'] == undefined) {
                    $('#saveContactListColumnPopup').removeAttr("disabled");
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
            else if ($(this).val() == 'default') {
                $('#saveContactListColumnPopup').attr("disabled", "disabled");
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
        _this.connectDropdownEventsStage2();
        _this.connectColumnStage2AddButton();
        _this.connectDropdownEventsStage3();
        _this.connectFilterStage3AddButton();
    };
    FgCmsContactsTable.prototype.getRowHtmlContactList = function (selectedFieldType, selectedField, selectedGroupValue, contactListColumnJson) {
        var rand = $.now();
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
        switch (selectedFieldType) {
            case 'CONTACT_NAME':
                inputFieldArray = contactListColumnJson[selectedFieldType]['fieldName'];
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldName'][defaultLang];
                linkContactFields = linkContactFields;
                checkboxFlag = 0;
                break;
            case 'TEAM_ASSIGNMENTS':
                inputFieldArray = contactListColumnJson[selectedFieldType]['fieldName'];
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldName'][defaultLang];
                teamFunctions = contactListColumnJson[selectedFieldType]['teamFunctions'];
                teamFunctionTitle = contactListColumnJson[selectedFieldType]['defaultOption'];
                break;
            case 'CONTACT_FIELD':
                inputFieldArray = {};
                _.each(contactListColumnJson[selectedFieldType]['fieldValue'], function (catDetails, catKey) {
                    if (catDetails['catId'] == selectedGroupValue) {
                        _.each(catDetails['attrDetails'], function (attrArray, attrKey) {
                            if (attrArray['attrId'] == selectedField) {
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
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldName'] + ': ' + inputFieldNameDefault;
                break;
            case 'MEMBERSHIP_INFO':
            case 'ANALYSIS_FIELD':
            case 'FED_MEMBERSHIP_INFO':
            case 'FEDERATION_INFO':
                inputFieldArray = contactListColumnJson[selectedFieldType]['fieldValue'][selectedField]['attrNameLang'];
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldName'] + ': ' + contactListColumnJson[selectedFieldType]['fieldValue'][selectedField]['attrName'];
                break;
            case 'TEAM_FUNCTIONS':
            case 'WORKGROUP_ASSIGNMENTS':
            case 'FILTER_ROLE_ASSIGNMENTS':
                inputFieldArray = contactListColumnJson[selectedFieldType]['fieldName'];
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldName'][defaultLang];
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
                selectedFieldLable = contactListColumnJson[selectedFieldType]['fieldName'] + ': ' + inputFieldNameDefault;
                break;
        }
        var jsonVal = {
            type: 'new', dataKey: rand, inputFieldArray: inputFieldArray, selectedFieldLable: selectedFieldLable, teamFunctionTitle: teamFunctionTitle,
            checkboxFlag: checkboxFlag, linkContactFields: linkContactFields, teamFunctions: teamFunctions, defaultLang: defaultLang, clubLangDetails: clubLangDetails,
            clubLanguages: clubLanguages, selectedFieldType: selectedFieldType, selectedField: selectedField, addressType: addressType,
            selectedGroupValue: selectedGroupValue, selectedFunction: selectedFunction, sortOrder: '', selectboxFlag: selectboxFlag, inputFieldNameDefault: inputFieldNameDefault
        };
        return FGTemplate.bind('contactListData', jsonVal);
    };
    FgCmsContactsTable.prototype.getFilterRowHtmlContactList = function (selectedFieldType, selectedField, selectedGroupValue, contactListFilterJson) {
        var rand = $.now();
        var inputFieldArray;
        var selectedFieldLable;
        var inputFieldNameDefault;
        var addressType = '';
        var teamFunctionTitle = '';
        switch (selectedFieldType) {
            case 'CONTACT_FIELD':
                inputFieldArray = {};
                _.each(contactListFilterJson[selectedFieldType]['fieldValue'], function (catDetails, catKey) {
                    if (catDetails['catId'] == selectedGroupValue) {
                        _.each(catDetails['attrDetails'], function (attrArray, attrKey) {
                            if (attrArray['attrId'] == selectedField) {
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
                inputFieldNameDefault = selectTrans[systemLang].replace('**placeholder**', inputFieldNameDefault);
                _.each(clubLangDetails, function (clubLang, clubKey) {
                    if (inputFieldArray[clubKey] != '' && inputFieldArray[clubKey] != undefined) {
                        var selectText = (selectTrans[clubLang['systemLang']] != '' && selectTrans[clubLang['systemLang']] != undefined) ? selectTrans[clubLang['systemLang']] : selectTrans[systemLang];
                        inputFieldArray[clubKey] = selectText.replace('**placeholder**', inputFieldArray[clubKey]);
                    }
                });
                break;
            case 'MEMBERSHIPS':
            case 'FED_MEMBERSHIPS':
                inputFieldArray = contactListFilterJson[selectedFieldType]['labelLang'];
                selectedFieldLable = contactListFilterJson[selectedFieldType]['fieldName'];
                break;
            case 'WORKGROUPS':
            case 'FILTER_ROLES':
                inputFieldArray = contactListFilterJson[selectedFieldType]['labelLang'];
                selectedFieldLable = contactListFilterJson[selectedFieldType]['fieldName'] + ': ' + $('div.fg-dev-contact-list-table-filter-secondDp button').attr('title');
                inputFieldNameDefault = inputFieldArray[defaultLang];
                break;
            case 'TEAM_CATEGORY':
            case 'ROLE_CATEGORY':
            case 'FED_ROLE_CATEGORY':
            case 'SUBFED_ROLE_CATEGORY':
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
            selectedGroupValue: selectedGroupValue, sortOrder: '', inputFieldNameDefault: inputFieldNameDefault
        };
        return FGTemplate.bind('contactListFilterData', jsonVal);
    };
    FgCmsContactsTable.prototype.connectStageClick = function () {
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
    FgCmsContactsTable.prototype.getCurrentStage = function () {
        return this.currentStage;
    };
    FgCmsContactsTable.prototype.setCurrentStage = function (stage) {
        this.currentStage = stage;
        return;
    };
    FgCmsContactsTable.prototype.loadWizardStage1 = function (stage1Data) {
        var _this = this;
        _this.setCurrentStage(1);
        $('#form-stage-progressbar .progress-bar').css('width', '25%');
        _this.handlePageTitleBar({});
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
    };
    FgCmsContactsTable.prototype.handlePageTitleBar = function (params) {
        var defaultOptions = {
            title: true,
            tab: (this.event == 'edit') ? true : false,
            tabType: 'server',
            row2: true,
            languageSwitch: false
        };
        var options = $.extend({}, defaultOptions, params);
        var FgPageTitlebarObj = $(".fg-action-menu-wrapper").FgPageTitlebar(options);
        setTimeout(function () { FgPageTitlebarObj.setMoreTab(); }, 200);
    };
    FgCmsContactsTable.prototype.handleFilterSelectPickers = function (id, disabled) {
        $('#' + id).prop('disabled', disabled);
        if (disabled) {
            $('#' + id).removeAttr('required');
        }
        else {
            $('#' + id).attr('required', 'required');
        }
        $('#' + id).selectpicker('refresh');
    };
    FgCmsContactsTable.prototype.handleFilterSelection = function () {
        var _this = this;
        $('.filter_type').click(function () {
            var filterType = $("input[name=saved_filter_type]:checked").val();
            var otherFilterType = (filterType == 'contact') ? 'sponsor' : 'contact';
            _this.handleFilterSelectPickers(filterType + 'Filter', false);
            _this.handleFilterSelectPickers(otherFilterType + 'Filter', true);
        });
    };
    FgCmsContactsTable.prototype.handleFilterRefresh = function (id) {
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
    FgCmsContactsTable.prototype.saveWizardStage1 = function (next) {
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
    FgCmsContactsTable.prototype.getStage1Data = function () {
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
    FgCmsContactsTable.prototype.validateWizardStage1 = function () {
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
    FgCmsContactsTable.prototype.saveStage1 = function (formData, next) {
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
    FgCmsContactsTable.prototype.getStage2Data = function () {
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
                    _this.changeColorOnDelete();
                    FgInternal.pageLoaderOverlayStop();
                    _this.setWizardStage(response.stage);
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactsTable.prototype.loadWizardStage2 = function (formData) {
        var _this = this;
        _this.setCurrentStage(2);
        $('.alert-danger').addClass('hide');
        $('#form-stage-progressbar .progress-bar').css('width', '50%');
        $('#saved-contactlist-fields').html('');
        _.each(formData, function (datas, catKey) {
            var htmlFinal = _this.stage2EditTemplateBuild(_this.tableId, contactListColumnJson, datas);
            $('#saved-contactlist-fields').append(htmlFinal);
        });
        _this.triggerEnterKey();
        FgGlobalSettings.handleLangSwitch();
        FgUtility.showTranslation(selectedLang);
        $('select.selectpicker').selectpicker('render');
        $('.fg-dev-contact-list-show-profile-pic').uniform();
        FgTooltip.init();
        _this.setStepTitle();
        _this.setelementsSortable();
        FormValidation.init('contacts_table_element_stage2');
        _this.renderFormButtons();
        _this.setTranslationTabError('contacts_table_element_stage2');
        _this.handlePageTitleBar({ languageSwitch: true });
    };
    FgCmsContactsTable.prototype.stage2EditTemplateBuild = function (table, contactListColumnJson, list) {
        var rand = list['id'];
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
        var selectedFieldLabel;
        var inputFieldNameDefault;
        var teamFunctions = [];
        var selectedFunction = [];
        var selectedField = '';
        var selectboxFlag = '';
        var addressType = '';
        var teamFunctionTitle = '';
        var selectedFieldType = list['type'].toUpperCase();
        switch (selectedFieldType) {
            case 'CONTACT_NAME':
                inputFieldArray = list['titleLang'];
                selectedFieldLabel = contactListColumnJson[selectedFieldType]['fieldName'][defaultLang];
                linkContactFields = linkContactFields;
                selectboxFlag = list['attr'];
                checkboxFlag = list['showProfilePicture'];
                break;
            case 'TEAM_ASSIGNMENTS':
                inputFieldArray = list['titleLang'];
                selectedFieldLabel = contactListColumnJson[selectedFieldType]['fieldName'][defaultLang];
                teamFunctions = contactListColumnJson[selectedFieldType]['teamFunctions'];
                selectedFunction = (list['functionIds'] != null) ? list['functionIds'].split(',') : [];
                teamFunctionTitle = contactListColumnJson[selectedFieldType]['defaultOption'];
                break;
             case 'FEDERATION_INFO':
                inputFieldArray = list['titleLang'];
                selectedField = list['col'];
                selectedFunction = (list['functionIds'] != null) ? list['functionIds'].split(',') : [];
                selectedFieldLabel = contactListColumnJson[selectedFieldType]['fieldName'] + ': ' + contactListColumnJson[selectedFieldType]['fieldValue'][selectedField]['attrName'];
                break;
            case 'CONTACT_FIELD':
                _.each(contactListColumnJson[selectedFieldType]['fieldValue'], function (catDetails, catKey) {
                    var selectedGroupValue = list['attrset'];
                    if (catDetails['catId'] == selectedGroupValue) {
                        _.each(catDetails['attrDetails'], function (attrArray, attrKey) {
                            var selectedField = list['attr'];
                            if (attrArray['attrId'] == selectedField) {
                                if (attrArray['isSystemField'] == 1) {
                                    _.each(clubLanguages, function (clubLang, clubKey) {
                                        addressType = attrArray['addressType'];
                                    });
                                    inputFieldArray = list['titleLang'];
                                    if ((attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != '') && (attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != undefined)) {
                                        inputFieldNameDefault = attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']];
                                    }
                                    else {
                                        inputFieldNameDefault = attrArray['fieldName'];
                                    }
                                }
                                else {
                                    inputFieldArray = list['titleLang'];
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
                selectedFieldLabel = contactListColumnJson[selectedFieldType]['fieldName'] + ': ' + inputFieldNameDefault;
                break;
            
            case 'MEMBERSHIP_INFO':
            case 'ANALYSIS_FIELD':
            case 'FED_MEMBERSHIP_INFO':
                var inputFieldArray = list['titleLang'];
                selectedField = list['col'];
                selectedFieldLabel = contactListColumnJson[selectedFieldType]['fieldName'] + ': ' + contactListColumnJson[selectedFieldType]['fieldValue'][selectedField]['attrName'];
                break;
            case 'TEAM_FUNCTIONS':
            case 'WORKGROUP_ASSIGNMENTS':
            case 'FILTER_ROLE_ASSIGNMENTS':
                inputFieldArray = list['titleLang'];
                selectedFieldLabel = contactListColumnJson[selectedFieldType]['fieldName'][defaultLang];
                break;
            case 'WORKGROUP_FUNCTIONS':
            case 'INDIVIDUAL_ROLE_FUNCTIONS':
            case 'INDIVIDUAL_FED_ROLE_FUNCTIONS':
            case 'INDIVIDUAL_SUB_FED_ROLE_FUNCTIONS':
                _.each(contactListColumnJson[selectedFieldType]['fieldValue'], function (catDetails, catKey) {
                    selectedField = list['role'];
                    inputFieldArray = list['titleLang'];
                    if (catDetails['attrId'] == selectedField) {
                        if ((catDetails['attrNameLang'][defaultLang] != '') && (catDetails['attrNameLang'][defaultLang] != undefined)) {
                            inputFieldNameDefault = catDetails['attrNameLang'][defaultLang];
                        }
                        else {
                            inputFieldNameDefault = catDetails['attrName'];
                        }
                    }
                });
                selectedFieldLabel = contactListColumnJson[selectedFieldType]['fieldName'] + ': ' + inputFieldNameDefault;
                break;
            case 'ROLE_CATEGORY_ASSIGNMENTS':
            case 'COMMON_ROLE_FUNCTIONS':
            case 'COMMON_FED_ROLE_FUNCTIONS':
            case 'COMMON_SUB_FED_ROLE_FUNCTIONS':
            case 'FED_ROLE_CATEGORY_ASSIGNMENTS':
            case 'SUB_FED_ROLE_CATEGORY_ASSIGNMENTS':
                _.each(contactListColumnJson[selectedFieldType]['fieldValue'], function (catDetails, catKey) {
                    selectedField = list['cat'];
                    inputFieldArray = list['titleLang'];
                    if (catDetails['attrId'] == selectedField) {
                        if ((catDetails['attrNameLang'][defaultLang] != '') && (catDetails['attrNameLang'][defaultLang] != undefined)) {
                            inputFieldNameDefault = catDetails['attrNameLang'][defaultLang];
                        }
                        else {
                            inputFieldNameDefault = catDetails['attrName'];
                        }
                    }
                });
                selectedFieldLabel = contactListColumnJson[selectedFieldType]['fieldName'] + ': ' + inputFieldNameDefault;
                break;
        }
        var jsonVal = {
            type: 'old', dataKey: rand, inputFieldArray: inputFieldArray, selectedFieldLable: selectedFieldLabel, teamFunctionTitle: teamFunctionTitle,
            checkboxFlag: checkboxFlag, linkContactFields: linkContactFields, teamFunctions: teamFunctions, defaultLang: defaultLang,
            clubLanguages: clubLanguages, selectedFieldType: selectedFieldType, selectedField: selectedField, addressType: addressType,
            selectedFunction: selectedFunction, sortOrder: list['sortOrder'], selectboxFlag: selectboxFlag, inputFieldNameDefault: inputFieldNameDefault
        };
        return FGTemplate.bind('contactListData', jsonVal);
    };
    FgCmsContactsTable.prototype.validateWizardStage2 = function () {
        $('.alert-danger span').text(error);
        var valid = true;
        $('#contacts_table_element_stage2').find('input:text[required]').each(function () {
            $(this).val($(this).val().trim());
        });
        valid = $('#contacts_table_element_stage2').valid();
        if ($('#contacts_table_element_stage2>#saved-contactlist-fields>div.sortables').not('.fg-inactiveblock').length < 1) {
            valid = false;
            if (!valid) {
                $('.alert-danger span').text(minError);
                $('.alert-danger').removeClass('hide');
            }
        }
        return valid;
    };
    FgCmsContactsTable.prototype.saveWizardStage2 = function (next) {
        FgInternal.pageLoaderOverlayStart();
        if (this.validateWizardStage2()) {
            this.saveStage2(next);
        }
        else {
            FgInternal.pageLoaderOverlayStop();
        }
    };
    FgCmsContactsTable.prototype.saveStage2 = function (next) {
        var dataArray = {};
        var _this = this;
        _this.reorderElementList($('#contacts_table_element_stage2>#saved-contactlist-fields>div.sortables'), 'sortVal');
        dataArray.jsonData = FgInternalParseFormField.formFieldParse('contacts_table_element_stage2');
        dataArray.table = _this.tableId;
        $.ajax({
            type: "POST",
            url: _this.settings.stage2savepath,
            data: dataArray,
            success: function (response) {
                FgInternal.showToastr(response.flash);
                FgInternal.pageLoaderOverlayStop();
                $.fn.dirtyFields.markContainerFieldsClean($("#contacts_table_element_stage2"));
                $('.nav>li[data-target="wizard-stage3"]').removeClass('disabled');
                $('.nav>li[data-target="wizard-stage4"]').removeClass('disabled');
                if (next) {
                    _this.getStage3Data();
                }
                else {
                    _this.getStage2Data();
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactsTable.prototype.getStage3Data = function () {
        var _this = this;
        FgInternal.pageLoaderOverlayStart();
        $('.nav-pills li:eq(2) a').tab('show');
        $.ajax({
            type: "POST",
            url: _this.settings.stage3DataPath,
            data: { 'stage': '3', 'tableId': _this.tableId },
            success: function (response) {
                FgInternal.pageLoaderOverlayStop();
                if (response.error == null) {
                    _this.setCurrentStage(3);
                    _this.setWizardStage(response.stage);
                    _this.changeColorOnDelete();
                    _this.loadWizardStage3(response.data);
                    FgInternal.pageLoaderOverlayStop();
                    $('.nav>li[data-target="wizard-stage4"]').removeClass('disabled');
                    if (response.data.length == 0) {
                        $('#contacts_table_element_save_and_next').removeAttr('disabled');
                    }
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactsTable.prototype.loadWizardStage3 = function (formData) {
        var _this = this;
        $('.alert-danger').addClass('hide');
        $('#form-stage-progressbar .progress-bar').css('width', '75%');
        $('#saved-contactlist-filter').html('');
        _.each(formData, function (filterData, id) {
            $('#saved-contactlist-filter').append(_this.stage3EditTemplateBuild(filterData, contactListFilterJson));
        });
        _this.triggerEnterKey();
        FgGlobalSettings.handleLangSwitch();
        FgUtility.showTranslation(selectedLang);
        FgTooltip.init();
        _this.setStepTitle();
        _this.setFilterElementsSortable();
        FormValidation.init('contacts_table_element_stage3');
        _this.renderFormButtons();
        _this.setTranslationTabError('contacts_table_element_stage3');
        _this.handlePageTitleBar({ languageSwitch: true });
    };
    FgCmsContactsTable.prototype.stage3EditTemplateBuild = function (formData, contactListFilterJson) {
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
                                    var attrNameLang = (typeof contactListFilterJson[filterType]['fieldValue'][index]['attrNameLang'][defaultLang] == 'undefined') ? contactListFilterJson[filterType]['fieldValue'][index]['attrName'] : contactListFilterJson[filterType]['fieldValue'][index]['attrNameLang'][defaultLang];
                                    remainingLabel_1 = remainingLabel_1 + attrNameLang + ', ';
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
    FgCmsContactsTable.prototype.validateWizardStage3 = function () {
        $('#contacts_table_element_stage3').find('input:text[required]').each(function () {
            $(this).val($(this).val().trim());
        });
        valid = $('#contacts_table_element_stage3').valid();
        return valid;
    };
    FgCmsContactsTable.prototype.saveWizardStage3 = function (next) {
        var _this = this;
        if (_this.validateWizardStage3()) {
            _this.saveStage3(next);
        }
    };
    FgCmsContactsTable.prototype.saveStage3 = function (next) {
        var dataArray = {};
        var _this = this;
        _this.reorderElementList($('#contacts_table_element_stage3>#saved-contactlist-filter>div.sortables'), 'sortVal');
        dataArray.jsonData = FgInternalParseFormField.formFieldParse('contacts_table_element_stage3');
        dataArray.table = _this.tableId;
        dataArray.stage = 3;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
            type: "POST",
            url: _this.settings.stage3savepath,
            data: dataArray,
            success: function (response) {
                FgInternal.showToastr(response.flash);
                FgInternal.pageLoaderOverlayStop();
                $.fn.dirtyFields.markContainerFieldsClean($("#contacts_table_element_stage3"));
                if (next) {
                    _this.getStage4Data();
                }
                else {
                    _this.getStage3Data();
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactsTable.prototype.getStage4Data = function () {
        var _this = this;
        FgInternal.pageLoaderOverlayStart();
        $('.nav-pills li:eq(3) a').tab('show');
        $.ajax({
            type: "POST",
            url: _this.settings.stage4DataPath,
            data: { 'stage': '4', 'tableId': _this.tableId },
            success: function (response) {
                FgInternal.pageLoaderOverlayStop();
                if (response.error == null) {
                    _this.loadWizardStage4(response.data);
                    FgInternal.pageLoaderOverlayStop();
                    _this.setWizardStage('stage4');
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactsTable.prototype.loadWizardStage4 = function (stage4Data) {
        var _this = this;
        _this.setCurrentStage(4);
        $('#form-stage-progressbar .progress-bar').css('width', '100%');
        var stage4Html = FGTemplate.bind('contacts_table_stage4_template', { "data": stage4Data, 'tableId': _this.tableId });
        $('#contacts-table-element-stage4').html(stage4Html);
        $('select.selectpicker').selectpicker('render');
        $('form input[type=checkbox]').uniform();
        _this.renderFormButtons();
        _this.setStepTitle();
        _this.handlePageTitleBar({ languageSwitch: false });
    };
    FgCmsContactsTable.prototype.saveWizardStage4 = function (finish) {
        var _this = this;
        var formData = {};
        var tableRaw = $('#tableRows').val();
        var tableOverflow = $('#tableOverflow').val();
        var highlightRow = ($("#highlightRow").is(':checked')) ? '1' : '';
        var tableSearch = ($("#tableSearch").is(':checked')) ? '1' : '';
        var tableExport = $('#tableExport').val();
        formData = { 'tableRaw': tableRaw, 'tableOverflow': tableOverflow, 'highlightRow': highlightRow, 'tableSearch': tableSearch, 'tableExport': tableExport };
        FgInternal.pageLoaderOverlayStart();
        this.saveStage4(formData, finish);
    };
    FgCmsContactsTable.prototype.saveStage4 = function (formData, finish) {
        var _this = this;
        formData.tableId = _this.tableId;
        $.ajax({
            type: "POST",
            url: _this.settings.stage4savepath,
            data: formData,
            success: function (response) {
                if (response.status == 'SUCCESS') {
                    FgInternal.pageLoaderOverlayStop();
                    FgInternal.showToastr(response.flash);
                    _this.tableId = response.tableId;
                    _this.event = 'edit';
                    $.fn.dirtyFields.markContainerFieldsClean($("#contacts_table_element_stage4"));
                    if (finish) {
                        var finishUrl = $('#contacts_table_element_finish').attr('data-href');
                        $('#contacts_table_element_finish').remove();
                        window.location.href = finishUrl;
                    }
                    else {
                        _this.getStage4Data();
                    }
                }
            },
            dataType: 'json'
        });
    };
    FgCmsContactsTable.prototype.connectAutocomplete = function (contactType, selectedContacts) {
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
    FgCmsContactsTable.prototype.renderFormButtons = function () {
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
    FgCmsContactsTable.prototype.initDirtyFields = function () {
        var _this = this;
        var stage = _this.getCurrentStage();
        var formId = 'contacts_table_element_stage1';
        if (stage == 2) {
            formId = 'contacts_table_element_stage2';
        }
        else if (stage == 3) {
            formId = 'contacts_table_element_stage3';
        }
        else if (stage == 4) {
            formId = 'contacts_table_element_stage4';
        }
        this.setFormDirty(formId, 0);
        FgDirtyFields.init(formId, { enableDiscardChanges: false, setInitialHtml: false, formChangeCallback: function (isDirty) { _this.setFormDirty(formId, isDirty); } });
    };
    FgCmsContactsTable.prototype.setFormDirty = function (formId, isDirty) {
        if (isDirty) {
            $('#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_save_and_next').removeAttr('disabled');
        }
        else {
            $('#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_save_and_next').attr('disabled', 'disabled');
        }
    };
    FgCmsContactsTable.prototype.setStepTitle = function () {
        var stage = this.getCurrentStage();
        var stepTrans = this.settings['stepTrans'];
        $('.portlet-title .step-title').html(stepTrans.replace('%s%', stage));
    };
    FgCmsContactsTable.prototype.setWizardStage = function (stage) {
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
    FgCmsContactsTable.prototype.reorderElementList = function (list, sortElement) {
        var z = 0;
        list.each(function (order, element) {
            if (!$(this).hasClass('fg-inactiveblock')) {
                var sort = ++z;
                $(this).find('.' + sortElement).val(sort).attr('value', sort);
            }
        });
    };
    ;
    FgCmsContactsTable.prototype.setelementsSortable = function () {
        var _this = this;
        $("#saved-contactlist-fields").sortable({
            items: "> div.sortables",
            containment: "body",
            handle: ".fg-dev-field-sort-handle",
            stop: function (event, ui) {
                _this.reorderElementList($('#contacts_table_element_stage2>#saved-contactlist-fields>div.sortables'), 'sortVal');
                FgDirtyFields.updateFormState();
            }
        });
    };
    ;
    FgCmsContactsTable.prototype.setFilterElementsSortable = function () {
        var _this = this;
        $("#saved-contactlist-filter").sortable({
            items: "> div.sortables",
            containment: "body",
            handle: ".fg-dev-field-sort-handle",
            stop: function (event, ui) {
                _this.reorderElementList($('#contacts-table-element-stage3 #saved-contactlist-filter>div.sortables'), 'sortVal');
                ui.item.find('.fg-dev-sortOrder').trigger('change');
                FgDirtyFields.updateFormState();
            }
        });
    };
    ;
    FgCmsContactsTable.prototype.removeNewRows = function (container) {
        container.off('click', '.new_row_rmv');
        container.on('click', '.new_row_rmv', function (event) {
            FgDirtyFields.removeFields($(this).parent());
            $(this).parent().remove();
        });
    };
    ;
    FgCmsContactsTable.prototype.changeColorOnDelete = function () {
        $('form').off('click', 'input[data-inactiveblock=changecolor]');
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function () {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('div#' + parentId);
            $(parentDiv).toggleClass('fg-inactiveblock');
        });
    };
    FgCmsContactsTable.prototype.setTranslationTabError = function (formId) {
        FgLanguageSwitch.checkMissingTranslation(defaultLang, formId);
    };
    FgCmsContactsTable.prototype.triggerEnterKey = function () {
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
    FgCmsContactsTable.prototype.connectDropdownEventsStage2 = function () {
        $('body').on('change', '.contact-list-table-column-type', function () {
            if ($(this).val() != 'default') {
                $('#saveContactListColumnPopup').attr("disabled", "disabled");
                if (contactListColumnJson[$(this).val()]['fieldValue'] == undefined) {
                    $('#saveContactListColumnPopup').removeAttr("disabled");
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
            else if ($(this).val() == 'default') {
                $('#saveContactListColumnPopup').attr("disabled", "disabled");
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
    };
    FgCmsContactsTable.prototype.connectDropdownEventsStage3 = function () {
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
    FgCmsContactsTable.prototype.connectColumnStage2AddButton = function () {
        var _this = this;
        $(document).on('click', '#saveContactListColumnPopup', function (event) {
            $('#contactListAddColumnPopup').modal('hide');
            var selectedFieldType = ($('.contact-list-table-column-type').val() != '') ? $('.contact-list-table-column-type').val() : '';
            var selectedField = ($('.fg-dev-contact-list-table-secondDp').val() != '') ? $('.fg-dev-contact-list-table-secondDp').val() : '';
            var selectedGroupValue = ($('.fg-dev-contact-list-table-secondDp :selected').parent().attr('value') != '') ? $('.fg-dev-contact-list-table-secondDp :selected').parent().attr('value') : '';
            var htmlFinal = _this.getRowHtmlContactList(selectedFieldType, selectedField, selectedGroupValue, contactListColumnJson);
            $('#saved-contactlist-fields').append(htmlFinal);
            FgDirtyFields.addFields(htmlFinal);
            _this.removeNewRows($('#contacts_table_element_stage2'));
            _this.triggerEnterKey();
            FgGlobalSettings.handleLangSwitch();
            FgUtility.showTranslation(selectedLang);
            $('select.selectpicker').selectpicker('render');
            $('.fg-dev-contact-list-show-profile-pic').uniform();
            FgTooltip.init();
        });
    };
    FgCmsContactsTable.prototype.connectFilterStage3AddButton = function () {
        var _this = this;
        $(document).on('click', '#saveContactListFilterPopup', function (event) {
            $('#contactListAddFilterPopup').modal('hide');
            var selectedFieldType = ($('.contact-list-table-filter-type').val() != '') ? $('.contact-list-table-filter-type').val() : '';
            var selectedField = ($('.fg-dev-contact-list-table-filter-secondDp').val() != '') ? $('.fg-dev-contact-list-table-filter-secondDp').val() : '';
            var selectedGroupValue = ($('.fg-dev-contact-list-table-filter-secondDp :selected').parent().attr('value') != '') ? $('.fg-dev-contact-list-table-filter-secondDp :selected').parent().attr('value') : '';
            var htmlFinal = _this.getFilterRowHtmlContactList(selectedFieldType, selectedField, selectedGroupValue, contactListFilterJson);
            $('#saved-contactlist-filter').append(htmlFinal);
            FgDirtyFields.addFields(htmlFinal);
            _this.setFilterElementsSortable();
            _this.removeNewRows($('#contacts_table_element_stage3'));
            _this.triggerEnterKey();
            FgGlobalSettings.handleLangSwitch();
            FgUtility.showTranslation(selectedLang);
            FgTooltip.init();
        });
    };
    return FgCmsContactsTable;
}());