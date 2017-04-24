/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
let pageTitleBarObj;
let clickCount: number = 0;
class FgCmsContactPortraits {
    tableId: any;
    event: String;
    log: String;
    currentStage: number;
    sortSetting: Object = '';
    defaultSettings: Object = {
        stage1savepath: '',
        stage2savepath: '',
        portraitCount: 0
    };
    settings: Object;
    defaultSortOptions: Object = {
        opacity: 0.8,
        forcePlaceholderSize: true,
        tolerance: "pointer"

    }
    constructor(options) {
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

    public handleNumberButtons() {
        var plusminusOption = {
            'selector': ".selectButton"
        };
        var inputplusminus = new Fgplusminus(plusminusOption);
        inputplusminus.init();
    }

    public connectButtonClick() {
        let _this = this;
        $('#contacts_table_element_save').on('click', function() {
            if ($(this).attr('disabled') == 'disabled')
                return;

            if (_this.getCurrentStage() == 1) {
                _this.saveWizardStage1(false);
            } else if (_this.getCurrentStage() == 2) {
                _this.saveWizardStage2(false);
            } else if (_this.getCurrentStage() == 3) {
                $(this).attr('disabled', 'disabled');
                _this.saveWizardStage3(false);
            } else if (_this.getCurrentStage() == 4) {
                _this.saveWizardStage4(false);
            }
        });

        $('#contacts_table_element_back').on('click', function() {
            if ($(this).attr('disabled') == 'disabled')
                return;

            if (_this.getCurrentStage() == 4) {
                $('.nav-pills li:eq(2) a').tab('show');
                _this.getStage3Data();
            } else if (_this.getCurrentStage() == 3) {
                $('.nav-pills li:eq(1) a').tab('show');
                _this.getStage2Data();
            } else if (_this.getCurrentStage() == 2) {
                $('.nav-pills li:eq(0) a').tab('show');
                _this.getStage1Data();
            }
        });

        $('#contacts_table_element_save_and_next').on('click', function() {

            if ($(this).attr('disabled') == 'disabled')
                return;

            if (_this.getCurrentStage() == 1) {
                _this.saveWizardStage1(true);
            } else if (_this.getCurrentStage() == 2) {
                _this.saveWizardStage2(true);
            } else if (_this.getCurrentStage() == 3) {
                _this.saveWizardStage3(true);
            }
        });

        $('#contacts_table_element_finish').on('click', function() {
            if ($(this).attr('disabled') == 'disabled')
                return;

            _this.saveWizardStage4(true);
        });

        $('#contacts_table_element_discard').on('click', function() {
            if ($(this).attr('disabled') == 'disabled')
                return;

            if (_this.getCurrentStage() == 1) {
                _this.getStage1Data();
            } else if (_this.getCurrentStage() == 2) {
                _this.getStage2Data();
            } else if (_this.getCurrentStage() == 3) {
                _this.getStage3Data();
            } else if (_this.getCurrentStage() == 4) {
                _this.getStage4Data();
            }
        });

        $('body').on('click', '.fg-dev-addNewData', function() {

            var jsonData = { contactListColumnJson: contactListColumnJson, defaultLang: defaultLang, columnId: $(this).attr('column_id'), containerId: $(this).attr('container_id') };
            var htmlFinal = FGTemplate.bind('contactListNewColumnPopup', jsonData);
            FgModelbox.showPopup(htmlFinal);
            $('select.selectpicker').selectpicker('render');
            $('#saveContactListColumnPopup').attr("disabled", "disabled");


        });


        $('#fg-dev-addNewFilterColumn').on('click', function() {
            var jsonData = { contactListFilterJson: contactListFilterJson, defaultLang: defaultLang };
            var htmlFinal = FGTemplate.bind('contactListNewFilterPopup', jsonData);
            $('.fg-modal-contact-list-filter-content').html(htmlFinal);
            $('#contactListAddFilterPopup').modal('show');
            $('select.selectpicker').selectpicker('render');
            $('#saveContactListFilterPopup').attr("disabled", "disabled");
        });


        $('body').on('change', '.contact-list-table-column-type', function() {
            if ($(this).val() != 'default') {
                $('#saveContactListColumnPopup').attr("disabled", "disabled");
                if ($(this).val() == 'PROFILE_PIC' || contactListColumnJson[$(this).val()]['fieldValue'] == undefined) {
                    $('#saveContactListColumnPopup').removeAttr("disabled");
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                } else {
                    var secondDpData = { datas: contactListColumnJson[$(this).val()], defaultLang: defaultLang, clubLangDetails: clubLangDetails, selectedVal: $(this).val() };
                    var htmlFinal = FGTemplate.bind('contactListSecondDpStep3', secondDpData);
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                    $(this).parent().parent().append(htmlFinal);
                    $('select.selectpicker').selectpicker('render');
                }
            } else if ($(this).val() == '') {
                $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
            }
        });
        $('body').on('change', '.contact-list-portrait-column-type', function() {
            if ($(this).val() != 'default') {
                if (contactListColumnJson[$(this).val()]['fieldValue'] == undefined) {
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                } else {
                    var secondDpData = { datas: contactListColumnJson[$(this).val()], defaultLang: defaultLang, clubLangDetails: clubLangDetails, selectedVal: $(this).val() };
                    var htmlFinal = FGTemplate.bind('contactListSecondDp', secondDpData);
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                    $(this).parent().parent().append(htmlFinal);
                    $('select.selectpicker').selectpicker('render');
                }
            } else if ($(this).val() == '') {
                $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
            }
        });
        $('body').on('change', '.fg-dev-contact-list-table-secondDp', function() {
            if ($(this).val() != 'default') {
                $('#saveContactListColumnPopup').removeAttr("disabled");
            } else {
                $('#saveContactListColumnPopup').attr("disabled", "disabled");
            }
        });
        $('body').on('click', '.fg-new_row_rmv', function() {
            $(this).parent().remove();
        });

        $('body').on('click', '.fg-dev-place-holder-image-upload', function() {
            $("#image-uploader").trigger('click');
        })


        $('body').on('click', '.fg-dev-data-delete', function() {
            let checkBoxValue = $(this).find('.fg-data-delete').prop('checked');
            if (checkBoxValue) {
                $(this).parents().eq(0).addClass('fg-inactiveblock');
            } else {
                $(this).parents().eq(0).removeClass('fg-inactiveblock');
            }

        })
        //container delete handling
        $('body').on('click', '.fg-dev-container-delete', function() {
            let checkBoxValue = $(this).find('.fg-data-delete').prop('checked');
            if (checkBoxValue) {
                $(this).parents().eq(2).addClass('fg-inactiveblock');
            } else {
                $(this).parents().eq(2).removeClass('fg-inactiveblock');
            }

        })

        //container delete handling
        $('body').on('click', '.fg-placeholder-image', function() {
            let checkBoxValue = $(this).find('.fg-upload-delete').attr('checked', 'checked');
            let checkBoxValue = $(this).find('.fg-upload-delete').prop('checked');
            $(this).parent().hide();
            FgDirtyFields.updateFormState();
            if (checkBoxValue) {
                //$(this).parents().eq(2).addClass('fg-inactiveblock');
            } else {
                //$(this).parents().eq(2).removeClass('fg-inactiveblock');
            }

        })

        $('body').on('click', '#triggerLogoUpload', function() {
            $("#image-uploader").trigger('click')

        })
        _this.connectDropdownEventsStage4();
        _this.connectFilterStage4AddButton();
        _this.connectColumnStage3AddButton();

    }

    public getRowHtmlContactList(selectedFieldType, selectedField, selectedGroupValue, contactListColumnJson, selectedContainer, selectedColumns) {
        var rand = 'new_' + $.now();
        var linkContactFields = [];

        _.each(contactListColumnJson['CONTACT_FIELD']['fieldValue'], function(datas, catKey) {
            _.each(datas['attrDetails'], function(attrValues, attrKey) {
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
        let fieldType = '';
        let required = 0;
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
                _.each(contactListColumnJson[selectedFieldType]['fieldValue'], function(catDetails, catKey) {
                    if (catDetails['catId'] == selectedGroupValue) {
                        _.each(catDetails['attrDetails'], function(attrArray, attrKey) {
                            if (attrArray['attrId'] == selectedField) {
                                fieldType = attrArray['attrType'];
                                if (attrArray['isSystemField'] == 1) {
                                    _.each(clubLanguages, function(clubLang, clubKey) {
                                        inputFieldArray[clubLang] = attrArray['attrNameLang'][clubLangDetails[clubLang]['systemLang']];
                                        addressType = attrArray['addressType'];
                                    });
                                    if ((attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != '') && (attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != undefined)) {
                                        inputFieldNameDefault = attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']];
                                    } else {
                                        inputFieldNameDefault = attrArray['fieldName'];
                                    }
                                } else {
                                    inputFieldArray = attrArray['attrNameLang'];
                                    if ((attrArray['fieldNameLang'][defaultLang] != '') && (attrArray['fieldNameLang'][defaultLang] != undefined)) {
                                        inputFieldNameDefault = attrArray['fieldNameLang'][defaultLang];
                                    } else {
                                        inputFieldNameDefault = attrArray['fieldName'];
                                    }
                                    addressType = attrArray['addressType'];
                                }
                            }
                        });
                    }
                });
                var requiredData = _.filter(contactFieldDetails, function(contactField) { return (contactField['id'] == selectedField && contactField['isRequiredType'] != 'not_required'); });

                if (_.size(requiredData) > 0) {
                    required = 1;
                }

                var contactfieldLabelDetails = _.filter(contactFieldDetails, function(contactField) { return (contactField['id'] == selectedField; });

                if (_.size(contactfieldLabelDetails) > 0) {
                    selectedFieldLable = contactfieldLabelDetails[0]['shortName'];
                } else {
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
                _.each(contactListColumnJson[selectedFieldType]['fieldValue'], function(catDetails, catKey) {
                    if (catDetails['attrId'] == selectedField) {
                        inputFieldArray = catDetails['attrNameLang'];

                        if ((catDetails['attrNameLang'][defaultLang] != '') && (catDetails['attrNameLang'][defaultLang] != undefined)) {
                            inputFieldNameDefault = catDetails['attrNameLang'][defaultLang];
                        } else {
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
    }


    public getFilterRowHtmlContactList(selectedFieldType, selectedField, selectedGroupValue, contactListFilterJson, selectedFieldText) {
        var rand = $.now();
        var inputFieldArray;
        var selectedFieldLable;
        var inputFieldNameDefault;
        var addressType = '';
        var teamFunctionTitle = '';
        let fieldType = '';
        switch (selectedFieldType) {
            case 'CONTACT_FIELD':
                inputFieldArray = {};
                _.each(contactListFilterJson[selectedFieldType]['fieldValue'], function(catDetails, catKey) {
                    if (catDetails['catId'] == selectedGroupValue) {
                        _.each(catDetails['attrDetails'], function(attrArray, attrKey) {
                            if (attrArray['attrId'] == selectedField) {
                                fieldType = attrArray['attrType'];
                                if (attrArray['isSystemField'] == 1) {
                                    lang = clubLangDetails[defaultLang]['systemLang'];
                                    _.each(clubLanguages, function(clubLang, clubKey) {
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
                _.each(clubLangDetails, function(clubLang, clubKey) {
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
                _.each(contactListFilterJson[selectedFieldType]['fieldValue'], function(catDetails, catKey) {
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
    }

    //different stages click
    public connectStageClick() {
        let _this = this;
        $('ul.steps li').on('click', function() {
            let stage = $(this).attr('data-target');
            if ($(this).hasClass('disabled'))
                return false;

            if (stage == 'wizard-stage1') {
                _this.getStage1Data();
            } else if (stage == 'wizard-stage2') {
                _this.getStage2Data();
            } else if (stage == 'wizard-stage3') {
                _this.getStage3Data();
            } else if (stage == 'wizard-stage4') {
                _this.getStage4Data();
            }
        });
    }

    public getCurrentStage() {
        return this.currentStage;
    }

    public setCurrentStage(stage) {
        this.currentStage = stage;
        return;
    }

    public loadWizardStage1(stage1Data) {
        var _this = this;
        _this.setCurrentStage(1);

        $('#form-stage-progressbar .progress-bar').css('width', '25%');
        this.handlePageTitleBar({});

        //render content
        let stage1Html = FGTemplate.bind('contacts_table_stage1_template', { "data": stage1Data, 'tableId': _this.tableId });
        $('#contacts-table-element-stage1').html(stage1Html);

        $('form input[type=radio]').uniform();

        $('#contactFilter').selectpicker('render');
        $('#sponsorFilter').selectpicker('render');

        _this.handleFilterSelection();
        if ($('input:radio[name=saved_filter_type]').is(':checked') === false) {
            _this.handleFilterSelectPickers('contactFilter', true);
            _this.handleFilterSelectPickers('sponsorFilter', true);
            $("input[name=saved_filter_type][value='contact']").attr('required', 'required');
        } else {
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
    }

    private handlePageTitleBar(params) {
        let defaultOptions = { title: true, tab: (this.log == '1') ? true : false, tabType: 'server' };
        var options = $.extend({}, defaultOptions, params);
        pageTitleBarObj = $(".fg-action-menu-wrapper").FgPageTitlebar(options);
    }

    private handleFilterSelectPickers(id, disabled) {
        $('#' + id).prop('disabled', disabled);
        if (disabled) {
            $('#' + id).removeAttr('required');
        } else {
            $('#' + id).attr('required', 'required');
        }
        $('#' + id).selectpicker('refresh');
    }

    private handleFilterSelection() {
        var _this = this;
        $('.filter_type').click(function() {
            var filterType = $("input[name=saved_filter_type]:checked").val();
            var otherFilterType = (filterType == 'contact') ? 'sponsor' : 'contact';
            _this.handleFilterSelectPickers(filterType + 'Filter', false);
            _this.handleFilterSelectPickers(otherFilterType + 'Filter', true);
        });
    }

    private handleFilterRefresh(id) {
        var _this = this;
        $('#' + id + 'Refresh').click(function() {
            let dataArray = { filter_type: $(this).attr('data-filterType') };
            $.ajax({
                type: "POST",
                data: dataArray,
                url: _this.settings['getFilterPath'],
                dataType: 'json',
                success: function(data) {
                    var placeholdertext = $('#' + id + ' option').eq(0);
                    $('#' + id + ' option').remove();
                    $('#' + id).append(placeholdertext);
                    $.each(data, function(index, element) {
                        $('#' + id).append($("<option></option>").attr("value", element.filterId).text(element.filterName));
                    });
                    $('#' + id).selectpicker('refresh');
                }
            });
        });
    }

    public saveWizardStage1(next) {
        if (this.validateWizardStage1()) {
            let _this = this;
            let formData = {};
            formData.contactData = FgInternalParseFormField.formFieldParse('contacts_table_element_stage1');
            formData.tableId = _this.tableId;
            formData.event = $('#contacts_table_element_event').val();
            formData.pageId = $('#contacts_table_element_pageId').val();
            formData.boxId = $('#contacts_table_element_boxId').val();
            formData.sortOrder = $('#contacts_table_element_sortOrder').val();
            formData.elementId = $('#contacts_table_elementId').val();
            formData.elementType = $('#contacts_table_elementType').val();
            FgInternal.pageLoaderOverlayStart();
            //save form data
            this.saveStage1(formData, next);
        }
    }

    public getStage1Data() {
        let _this = this;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
            type: "POST",
            url: _this.settings.stage1DataPath,
            data: { 'stage': '1', 'tableId': _this.tableId },
            success: function(response) {
                if (response.error == null) {
                    _this.loadWizardStage1(response);
                    FgInternal.pageLoaderOverlayStop();
                    _this.setWizardStage(response.stage);
                }
            },
            dataType: 'json'
        });
    }
    /**
     * validate wizard 2
     */
    private validateWizardStage1() {
        let valid = true;
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
    }
    //set ajax and validate  + save
    private saveStage1(formData, next) {
        let _this = this;
        formData.tableId = _this.tableId;
        $.ajax({
            type: "POST",
            url: _this.settings.stage1savepath,
            data: formData,
            success: function(response) {
                if (response.result == 'success') {
                    FgInternal.pageLoaderOverlayStop();
                    FgInternal.showToastr(response.message);
                    _this.tableId = response.tableId;
                    _this.event = 'edit';
                    if (next) {
                        //Load stage 2
                        $('#contacts-table-element-stage1').html('');
                        $('.nav-pills li:eq(1) a').tab('show');
                        _this.getStage2Data();
                    } else {
                        //Load stage 1
                        _this.loadWizardStage1(response.data);
                        _this.setWizardStage(response.data.stage);
                    }
                    $('#contacts_table_element_event').val('edit');
                    $('#contacts_table_element_tableId').val(response.tableId);
                } else if (response.result == 'error') {
                    FgInternal.pageLoaderOverlayStop();
                    FgInternal.showToastr(_this.settings.saveFailedMsg);
                }
            },
            dataType: 'json'
        });
    }

    /**
     * get the existing columns
     */
    public getStage2Data() {
        let _this = this;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
            type: "POST",
            url: _this.settings.stage2DataPath,
            data: { 'stage': '2', 'tableId': _this.tableId },
            success: function(response) {
                FgInternal.pageLoaderOverlayStop();
                if (response.error == null) {
                    _this.loadWizardStage2(response.data);
                    //_this.changeColorOnDelete();
                    FgInternal.pageLoaderOverlayStop();
                    _this.setWizardStage(response.stage);
                }
            },
            dataType: 'json'
        });
    }
    /**
     * build the existing  columns template stage 2
     */
    public loadWizardStage2(formData) {
        let _this = this;
        _this.setCurrentStage(2);
        $('.alert-danger').addClass('hide');
        $('#form-stage-progressbar .progress-bar').css('width', '50%');
        $('#saved-contactlist-fields').html('');
        let htmlFinal = FGTemplate.bind('contacts_portrait_stage2_template', { "data": formData, 'tableId': _this.tableId });
        $('#contacts-portrait-element-stage2').html(htmlFinal);
        _this.renderFormButtons();
        _this.setStep2InitialElements(formData);
        $('select.selectpicker').selectpicker('render');
        _this.handleHelpMessages();
        FgFormTools.handleUniform();
        _this.handlePageTitleBar({ tab: ((this.log == '1') ? true : false) });
        _this.hideStage3Preview();
    }

    /**
     * handle Help Messages in step2
     */
    private handleHelpMessages() {
        var portraitPerRow = parseInt($('#portraitPerRow').val());
        $('#portraitsPerRowMsg').html(portraitsPerRowMsg[portraitPerRow]);
        $('#portraitPerRow').on('change', function() {
            var portraitPerRow = parseInt($('#portraitPerRow').val());
            var rowsPerPage = parseInt($('#rowsPerPage').val());
            $('#portraitsPerRowMsg').html(portraitsPerRowMsg[portraitPerRow]);
            var rowsPerPageCount = (rowsPerPage * portraitPerRow) ? (rowsPerPage * portraitPerRow) : 0;
            $('#rowsPerPageCount').html(rowsPerPageCount);
        });
        var rowsPerPage = parseInt($('#rowsPerPage').val());
        var rowsPerPageCount = (rowsPerPage * portraitPerRow) ? (rowsPerPage * portraitPerRow) : 0;
        $('#rowsPerPageCount').html(rowsPerPageCount);
        $('#rowsPerPage').on('change', function() {
            var rowsPerPage = parseInt($('#rowsPerPage').val());
            var portraitPerRow = parseInt($('#portraitPerRow').val());
            var rowsPerPageCount = (rowsPerPage * portraitPerRow) ? (rowsPerPage * portraitPerRow) : 0;
            $('#rowsPerPageCount').html(rowsPerPageCount);
        });
    }

    /**
     * set initial elements
     */
    private setStep2InitialElements(formData) {
        if (formData.rowPerpage == null || formData.rowPerpage == '') {
            $('#rowsPerPage').val(1);
        }
        if (formData.portraitPerRow == null || formData.portraitPerRow == '') {
            $('#portraitPerRow').val(1);
        }
        if (formData.initialSortOrder == null || formData.initialSortOrder == '') {
            $('#sortingOrder-0').prop('checked', 'checked');
        }

        //if nothing selected, make last_name selected
        if ($('#contact-list-portrait-column-type').val() == '') {
            $('#contact-list-portrait-column-type>option[value="CONTACT_FIELD"]').prop('selected', true);

            //make second dropdown selected
            var secondDpData = { datas: contactListColumnJson['CONTACT_FIELD'], defaultLang: defaultLang, clubLangDetails: clubLangDetails, selectedVal: 'CONTACT_FIELD' };
            var htmlFinal = FGTemplate.bind('contactListSecondDp', secondDpData);
            $('#contact-list-portrait-column-type').parent().siblings('.fg-dev-contact-secondDp').remove();
            $('#contact-list-portrait-column-type').parent().parent().append(htmlFinal);
            //23 is last_name field
            $('#fg-dev-contact-list-portrait-secondDp>optgroup>option[value="23"]').prop('selected', true);
        }

        FgDirtyFields.updateFormState();

    }


    /**
     * validate the stage 2 wizard form
     */
    private validateWizardStage2() {
        let valid = true;
        //$( "#contacts_portrait_element_stage2" ).validate();         
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
                $('.fg-dev-sort-type').addClass('no-error')
                valid = false;
            }
        }

        $('#contacts_portrait_element_stage2 input[required]').each(function(index) {
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
    }
    /**
     * intiate the save wizard stage 2
     */
    public saveWizardStage2(next) {
        //save stage 2
        FgInternal.pageLoaderOverlayStart();
        if (this.validateWizardStage2()) {
            this.saveStage2(next);
        } else {
            FgInternal.pageLoaderOverlayStop();
        }
    }

    /**
     * save the wizard step 2
     */
    private saveStage2(next) {
        let dataArray = {};
        let _this = this;
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
            success: function(response) {
                FgInternal.showToastr(response.flash);
                FgInternal.pageLoaderOverlayStop();
                $.fn.dirtyFields.markContainerFieldsClean($("#contacts_portrait_element_stage2"));
                $('.nav>li[data-target="wizard-stage3"]').removeClass('disabled');
                $('.nav>li[data-target="wizard-stage4"]').removeClass('disabled');
                if (next) {
                    $('#contacts-portrait-element-stage2').html('');
                    $('.nav-pills li:eq(2) a').tab('show');
                    _this.getStage3Data();
                } else {
                    _this.getStage2Data();
                }
            },
            dataType: 'json'
        });
    }


    public getStage3Data() {
        let _this = this;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
            type: "POST",
            url: _this.settings.stage3DataPath,
            data: { 'stage': '3', 'portraitId': _this.tableId },
            success: function(response) {
                //FgInternal.pageLoaderOverlayStop();
                if (response.error == null) {
                    _this.setCurrentStage(3);
                    _this.setWizardStage(response.data.portraitElement.stage);
                    _this.loadWizardStage3(response.data);
                    _this.loadStage3Preview(response.stage);
                    _this.displayManageIcon();

                    //FgInternal.pageLoaderOverlayStop();
                    $('.nav>li[data-target="wizard-stage4"]').removeClass('disabled');
                    if (response.data.length == 0) {
                        $('#contacts_table_element_save_and_next').removeAttr('disabled');
                    }
                }
                setTimeout(function() { pageTitleBarObj.setMoreTab(); FgInternal.pageLoaderOverlayStop(); }, 1000);
            },
            dataType: 'json'
        });
    }
    public loadWizardStage3(portraitData) {
        let _this = this;
        _this.setCurrentStage(3);
        $('.alert-danger').addClass('hide');

        $('#form-stage-progressbar .progress-bar').css('width', '75%');
        _this.handlePageTitleBar({ tab: ((this.log == '1') ? true : false), languageSwitch: true });
        $('#contacts-portrait-element-stage3').html('');
        this.settings.portraitCount = portraitData['portraitElement']['portraitPerRow'];
        this.settings.elementId = portraitData['portraitElement']['elementId'];
        this.settings.maxColumnCount = 3;
        let htmlData = this.stage3EditTemplateBuild(portraitData);
        $('#contacts-portrait-element-stage3').html(htmlData);
        FgGlobalSettings.handleLangSwitch();
        FgUtility.showTranslation(selectedLang);
        this.handleNumberButtons();
        FgFileUpload.init($('#image-uploader'), placeholderImageOption);
        $('select.selectpicker').selectpicker('render');
        this.sortColumnData();
        this.sortContainer();

        $('.fg-dev-multiple').uniform();
        //        FormValidation.init('contacts-portrait-element-stage3');
        _this.renderFormButtons();
        _this.setStepTitle();
        _this.manageFirstColumnLinebreakdisplay();
        _this.manageFirstContainerDeleteIcon();
        _this.preventOneMoreMultipleAssignment();
        _this.displayToggleLineBreak();
        _this.hideStage3Preview();
        _this.manageLanguageSwitch();
    }

    private loadStage3Preview(stage) {
        if (parseInt(stage) >= 3) {
            var _this = this;
            $.ajax({
                type: "POST",
                url: _this.settings.stage3PreviewPath,
                data: { 'elementId': _this.settings.elementId, 'columnSize': _this.settings.columnSize },
                success: function(response) {
                    if (_.has(response, 'contactsData')) {
                        _this.handleContactPortraitElement(response);
                    }
                },
                dataType: 'json'
            });
        }
    }

    private hideStage3Preview() {
        $('#contacts-portrait-element-stage3-preview').addClass('hide');
        $('#contacts-portrait-element-stage3-preview').html('');
    }

    private handleContactPortraitElement(data: Object) {
        let options = {
            boxId: 'contacts-portrait-element-stage3-preview',
            portraitWrapperData: data.portraitData,
            portraitContactsData: data.contactsData,
            portraitTemplate: data.portraitTemplate,
        };
        FgPortraitElement.initPreviewSettings(options);
    }

    private stage3EditTemplateBuild(portraitData: Object) {
        let stage3Html = FGTemplate.bind('contacts_portrait_element_stage3_template', { "containerDatas": portraitData['portraitElement'], 'portraitId': this.tableId });
        return stage3Html;
    }
    private stage4EditTemplateBuild(formData, contactListFilterJson) {
        let filterType = formData['filterType'].toUpperCase();
        let invalid = true;
        let htmlContent = '';
        let inputFieldNameDefault = '';
        let addressType = '',
        switch (filterType) {
            case 'CONTACT_FIELD':
                inputFieldArray = formData['titleLang'];
                let catId = 'c-' + formData['catId'];
                let attrId = 'a-' + formData['attrId'];
                if (typeof contactListFilterJson[filterType]['fieldValue'][catId] != 'undefined') {
                    if (typeof contactListFilterJson[filterType]['fieldValue'][catId]['attrDetails'][attrId] != 'undefined') {
                        let attrArray = contactListFilterJson[filterType]['fieldValue'][catId]['attrDetails'][attrId];
                        addressType = attrArray['addressType'];
                        if (attrArray['isSystemField'] == 1) {
                            lang = clubLangDetails[defaultLang]['systemLang'];
                            if ((attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != '') && (attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']] != undefined)) {
                                labelNameDefault = attrArray['fieldNameLang'][clubLangDetails[defaultLang]['systemLang']];
                            } else {
                                labelNameDefault = attrArray['fieldName'];
                            }
                        } else {
                            if ((attrArray['fieldNameLang'][defaultLang] != '') && (attrArray['fieldNameLang'][defaultLang] != undefined)) {
                                labelNameDefault = attrArray['fieldNameLang'][defaultLang];
                            } else {
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
                    let remainingLabel = '';
                    inputFieldArray = formData['titleLang'];
                    inputFieldNameDefault = formData['title'];
                    selectedFieldLable = contactListFilterJson[filterType]['fieldName'] + ': ';
                    if (formData['filterSubtypeIds'] == 'ALL') {
                        remainingLabel = contactListFilterJson[filterType]['fieldValue']['f-0']['attrName'];
                        invalid = false;
                    } else {
                        selectedWorkGroups = formData['filterSubtypeIds'].split(",");
                        if (selectedWorkGroups.length > 0) {
                            _.each(selectedWorkGroups, function(data) {
                                index = 'f-' + data;
                                if (typeof contactListFilterJson[filterType]['fieldValue'][index] != 'undefined') {
                                    remainingLabel = remainingLabel + contactListFilterJson[filterType]['fieldValue'][index]['attrName'] + ', ';
                                    invalid = false;
                                }
                            })
                            remainingLabel = remainingLabel.slice(0, -2);
                        }
                    }
                    selectedFieldLable = selectedFieldLable + remainingLabel;
                }
                break;
            case 'TEAM_CATEGORY':
            case 'ROLE_CATEGORY':
            case 'FED_ROLE_CATEGORY':
            case 'SUBFED_ROLE_CATEGORY':
                if (typeof contactListFilterJson[filterType] != 'undefined') {
                    inputFieldArray = formData['titleLang'];
                    inputFieldNameDefault = formData['title'];
                    let catId = (filterType == 'TEAM_CATEGORY') ? 'tc-' + formData['filterSubtypeIds'] : 'r-' + formData['filterSubtypeIds'];
                    if (typeof contactListFilterJson[filterType]['fieldValue'][catId] != 'undefined') {
                        selectedFieldLable = contactListFilterJson[filterType]['fieldName'] + ': '
                        catDetails = contactListFilterJson[filterType]['fieldValue'][catId];
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
    }



    private validateWizardStage4() {

        $('#contacts_table_element_stage4').find('input:text[required]').each(function() {
            $(this).val($(this).val().trim());
        });

        let valid = $('#contacts_table_element_stage4').valid();
        return valid;
    }


    public saveWizardStage4(next) {
        let _this = this;
        if (_this.validateWizardStage4()) {
            _this.saveStage4(next);
        }
    }
    private saveStage4(finish) {
        let dataArray = {};
        let _this = this;
        _this.reorderElementList($('#contacts_table_element_stage4>#saved-contactlist-filter>div.sortables'), 'sortVal');
        dataArray.jsonData = FgInternalParseFormField.formFieldParse('contacts_table_element_stage4');
        dataArray.table = _this.tableId;
        dataArray.stage = 4;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
            type: "POST",
            url: _this.settings.stage4savepath,
            data: dataArray,
            success: function(response) {
                FgInternal.showToastr(response.flash);
                FgInternal.pageLoaderOverlayStop();
                $.fn.dirtyFields.markContainerFieldsClean($("#contacts_table_element_stage4"));
                if (finish) {
                    var finishUrl = $('#contacts_table_element_finish').attr('data-href');
                    $('#contacts_table_element_finish').remove();
                    window.location.href = finishUrl;
                } else {
                    _this.getStage4Data();
                }
            },
            dataType: 'json'
        });
    }





    public getStage4Data() {
        let _this = this;
        FgInternal.pageLoaderOverlayStart();
        $('.nav-pills li:eq(3) a').tab('show');
        $.ajax({
            type: "POST",
            url: _this.settings.stage4DataPath,
            data: { 'stage': '4', 'tableId': _this.tableId },
            success: function(response) {
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
                setTimeout(function() { pageTitleBarObj.setMoreTab(); FgInternal.pageLoaderOverlayStop(); }, 1000);
            },
            dataType: 'json'
        });
    }
    public loadWizardStage4(formData) {
        let _this = this;
        $('.alert-danger').addClass('hide');

        $('#form-stage-progressbar .progress-bar').css('width', '100%');

        $('#saved-contactlist-filter').html('');
        _.each(formData, function(filterData, id) {
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
    }




    private connectAutocomplete(contactType, selectedContacts) {
        let selectedIds = [];

        if (selectedContacts != null) {
            _.each(selectedContacts, function(name, id) {
                selectedIds.push({ 'id': id, 'title': name });
            })
        }

        $('#contacts_table_' + contactType + '_contacts').fbautocomplete({
            url: FgInternalVariables.topNavSearchUrl, // which url will provide json!
            maxItems: 0, // only one item can be selected
            useCache: false,
            selected: selectedIds,
            onItemSelected: function($obj, itemId, selected) {
                let alreadySelectedvalues = _.compact($('#contacts_table_' + contactType + '_contacts_data').val().split(','));
                alreadySelectedvalues.push(itemId);
                $('#contacts_table_' + contactType + '_contacts_data').val(_.uniq(alreadySelectedvalues).join()).trigger('change');
            },
            onItemRemoved: function($obj, itemId) {
                let alreadySelectedvalues = _.compact($('#contacts_table_' + contactType + '_contacts_data').val().split(','));
                alreadySelectedvalues = _.reject(alreadySelectedvalues, function(v) { return v == itemId; });
                $('#contacts_table_' + contactType + '_contacts_data').val(_.uniq(alreadySelectedvalues).join()).trigger('change');
            }
        });
    }

    private renderFormButtons() {
        let _this = this;
        let stage = _this.getCurrentStage();
        $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_cancel,#contacts_table_element_discard,#contacts_table_element_back,#contacts_table_element_finish').addClass('hide');
        if (stage == 1) {
            if (_this.event == 'create') {
                $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_cancel').removeClass('hide');
            } else {
                $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_discard').removeClass('hide');
            }
        } else if (stage == 2) {
            $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_back').removeClass('hide');
        } else if (stage == 3) {
            $('#contacts_table_element_save_and_next,#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_back').removeClass('hide');
        } else if (stage == 4) {
            $('#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_finish,#contacts_table_element_back').removeClass('hide');
        }
        _this.initDirtyFields();
    }

    private initDirtyFields() {
        let _this = this;
        let stage = _this.getCurrentStage();
        let formId = 'contacts_table_element_stage1';
        if (stage == 2) {
            formId = 'contacts_portrait_element_stage2';
        } else if (stage == 3) {
            formId = 'contacts_portrait_element_stage3_form';
        } else if (stage == 4) {
            formId = 'contacts_table_element_stage4';
        }

        this.setFormDirty(formId, 0);
        FgDirtyFields.init(formId, { enableDiscardChanges: false, setInitialHtml: false, formChangeCallback: function(isDirty) { _this.setFormDirty(formId, isDirty) } });
    }

    private setFormDirty(formId, isDirty) {
        if (isDirty) {
            $('#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_save_and_next').removeAttr('disabled');
        } else {
            $('#contacts_table_element_save,#contacts_table_element_discard,#contacts_table_element_save_and_next').attr('disabled', 'disabled');
        }
    }

    private setStepTitle() {
        let stage = this.getCurrentStage();
        let stepTrans = this.settings['stepTrans'];
        $('.portlet-title .step-title').html(stepTrans.replace('%s%', stage));
    }

    private setWizardStage(stage) {
        if (stage == 'stage1') {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').removeClass('disabled');
            $('.nav>li[data-target="wizard-stage3"]').addClass('disabled');
            $('.nav>li[data-target="wizard-stage4"]').addClass('disabled');
        } else if (stage == 'stage2') {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').removeClass('disabled');
            $('.nav>li[data-target="wizard-stage4"]').addClass('disabled');
        } else if (stage == 'stage3') {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').addClass('done');
            $('.nav>li[data-target="wizard-stage4"]').removeClass('disabled');
        } else if (stage == 'stage4') {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').addClass('done');
            $('.nav>li[data-target="wizard-stage4"]').addClass('done');
        }

        let currentStage = this.getCurrentStage();
        if (currentStage == 1) {
            $('.nav>li[data-target="wizard-stage1"]').removeClass('done').addClass('active');
        } else if (currentStage == 2) {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').removeClass('done').addClass('active');
        } else if (currentStage == 3) {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').removeClass('done').addClass('active');
        } else if (currentStage == 4) {
            $('.nav>li[data-target="wizard-stage1"]').addClass('done');
            $('.nav>li[data-target="wizard-stage2"]').addClass('done');
            $('.nav>li[data-target="wizard-stage3"]').addClass('done');
            $('.nav>li[data-target="wizard-stage4"]').removeClass('done').addClass('active');
        }
    }


    private reorderElementList(list, sortElement) {
        var z = 0;
        list.each(function(order, element) {
            if (!$(this).hasClass('fg-inactiveblock')) {
                var sort = ++z;
                $(this).find('.' + sortElement).val(sort).attr('value', sort);
            }
        });
    }




    private setFilterElementsSortable() {
        var _this = this;
        $("#saved-contactlist-filter").sortable({
            items: "> div.sortables",
            containment: "body",
            handle: ".fg-dev-field-sort-handle",
            stop: function(event, ui) {
                _this.reorderElementList($('#contacts_table_element_stage4 #saved-contactlist-filter>div.sortables'), 'sortVal');
                ui.item.find('.fg-dev-sortOrder').trigger('change');
                FgDirtyFields.updateFormState();
            }
        });
    };


    //remove row -internal settings - backend
    private removeNewRows(container) {
        container.off('click', '.new_row_rmv');
        container.on('click', '.new_row_rmv', function(event) {
            FgDirtyFields.removeFields($(this).parent());
            $(this).parent().remove();
        });
    };

    private changeColorOnDelete() {
        $('form').off('click', 'input[data-inactiveblock=changecolor]');
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function() {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('div#' + parentId);
            $(parentDiv).toggleClass('fg-inactiveblock');
        });
    }

    private setTranslationTabError(formId) {
        FgLanguageSwitch.checkMissingTranslation(defaultLang, formId);
    }

    private triggerEnterKey() {
        $("form input").off('keypress');
        $("form input").on('keypress', function(e) {
            if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                $('#contacts_table_element_save').click();
                return false;
            } else {
                return true;
            }
        });
    }



    private connectDropdownEventsStage4() {
        $('body').on('change', '.contact-list-table-filter-type', function() {
            if ($(this).val() != 'default') {
                $('#saveContactListFilterPopup').attr("disabled", "disabled");
                if (contactListFilterJson[$(this).val()]['fieldValue'] == undefined) {
                    $('#saveContactListFilterPopup').removeAttr("disabled");
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                } else {
                    var secondDpData = { datas: contactListFilterJson[$(this).val()], defaultLang: defaultLang, clubLangDetails: clubLangDetails, selectedVal: $(this).val() };
                    var htmlFinal = FGTemplate.bind('contactListFilterSecondDp', secondDpData);
                    $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
                    $(this).parent().parent().append(htmlFinal);
                    $('#fg-dev-contact-list-table-filter-secondDp').selectpicker();
                    FgUtility.handleSelectPicker();
                }
            } else if ($(this).val() == 'default') {
                $('#saveContactListFilterPopup').attr("disabled", "disabled");
                $(this).parent().siblings('.fg-dev-contact-secondDp').remove();
            }
        });

        $('body').on('change', 'select.fg-dev-contact-list-table-filter-secondDp', function() {
            if ($(this).val() != 'default') {
                $('#saveContactListFilterPopup').removeAttr("disabled");
            } else {
                $('#saveContactListFilterPopup').attr("disabled", "disabled");
            }
        });
    }

    public renderContainer(containerDatas: Object) {

        let containerWidth = Math.floor(this.settings.columnSize / this.settings.portraitCount);
        return FGTemplate.bind('portrait_container_template', { "columnDatas": containerDatas, 'portraitId': this.tableId, 'containerWidth': containerWidth });
    }
    public renderColumn(columnDatas: Object) {
        return FGTemplate.bind('portrait_column_template', { "displayDatas": columnDatas, 'portraitId': this.tableId });
    }
    public renderColumnData(displayDatas: Object) {
        let linkContactFields = [];

        //find link url field
        _.each(contactListColumnJson['CONTACT_FIELD']['fieldValue'], function(datas, catKey) {
            _.each(datas['attrDetails'], function(attrValues, attrKey) {
                if (attrValues['attrType'] == 'url') {
                    linkContactFields.push(attrValues);
                }
            });
        });

        return FGTemplate.bind('column_data_template', { "optionDatas": displayDatas, 'portraitId': this.tableId, 'linkContactFields': linkContactFields, 'contactListColumnJson': contactListColumnJson, 'defaultLang': defaultLang, 'contactFieldDetail': contactFieldDetails });
    }
    public renderOptionTemplate(optionDatas: Object) {
        return FGTemplate.bind('data_option_template', { "data": optionDatas, 'portraitId': this.tableId });
    }


    //  ADD/EDIT CONTAINER POP UP

    private connectFilterStage4AddButton() {
        let _this = this;
        $(document).on('click', '#saveContactListFilterPopup', function(event) {
            $('#contactListAddFilterPopup').modal('hide');
            var selectedFieldType = ($('.contact-list-table-filter-type').val() != '') ? $('.contact-list-table-filter-type').val() : '';
            var selectedField = ($('.fg-dev-contact-list-table-filter-secondDp').val() != '') ? $('.fg-dev-contact-list-table-filter-secondDp').val() : '';
            var selectedGroupValue = ($('.fg-dev-contact-list-table-filter-secondDp :selected').parent().attr('value') != '') ? $('.fg-dev-contact-list-table-filter-secondDp :selected').parent().attr('value') : '';
            if ($(".fg-dev-contact-list-table-filter-secondDp").attr('title') !== undefined) {
                var selectedFieldText = '';
            } else {
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
    }


    //  ADD/EDIT CONTAINER POP UP

    public containerEdit() {
        let _this = this;
        $("body").on('click', '.editContainerpopup', function(event) {
            event.stopImmediatePropagation();
            let currentPortraitId = $(this).attr('container-element-id');

            let containeType = $(this).attr('container-type');
            let portraitPerRow = _this.settings.portraitCount;
            let containerColumnSize = _this.settings.columnSize;

            let maxCount = Math.floor(containerColumnSize / portraitPerRow);
            //_this.getMaxColumnCount()
            //let defaultColumnCount = ($("#" + currentPortraitId).find(".columWidth").length > 0) ? $("#" + currentPortraitId).find(".columWidth").length : 1;
            let defaultColumnCount = $(this).attr('old_count');
            let headerTitle = (containeType == 'containerAdd') ? _this.settings.translations.createContainerHeader : _this.settings.translations.editContainerHeader;
            let popupContent = FGTemplate.bind('editcontainerpopup', {
                defaultColumnCount: defaultColumnCount,
                maxColumnCount: maxCount,
                portraitId: currentPortraitId,
                containerType: containeType,
                headerTitle: headerTitle,
                containerId: $(this).attr('container-id')
            });
            FgModelbox.showPopup(popupContent);

        })
    }


    public createContainer() {
        // SAVE FUNCTIONALITY
        let _this = this;
        let countDifference: number = 0;
        let multiClick: number = 1;
        $("body").on('click', '.fg-dev-datasave', function(ele) {
            ele.stopImmediatePropagation();
            let portraitPerRow = _this.settings.portraitCount;
            let containerColumnSize = Math.floor(parseInt(_this.settings.columnSize) / parseInt(_this.settings.portraitCount));
            let popupType = $("input[name='containerpopupType']").val();
            let columnCount = $("input[name='columnCount']").val();
            let oldCount = $("#oldColumnCount").val();
            let containerId = $("#clickedContainer").val();
            if (popupType == 'containerAdd') {
                let rand = $.now();
                let sortOrder = $(".fg-portrait-container").length + 1
                let objectGraph = { 'containerId': 'newContainer_' + rand, 'sortOrder': sortOrder, 'actionType': 'add', 'columnSize': columnCount, 'columnCount': columnCount, 'columns': {} };
                let columnWidth = Math.floor(containerColumnSize / columnCount);
                for (let i = 1; i <= columnCount; i++) {
                    let columnDummyId = 'newColumn_' + parseInt(Math.random() * Math.pow(10, 5));
                    let actualWidth = parseInt(columnWidth) * 2;
                    objectGraph.columns[columnDummyId] = { 'columnId': columnDummyId, 'columnSize': actualWidth, 'gridSize': columnWidth, 'container': 'newContainer_' + rand, 'sortOrder': i };

                }

                //bind container template and column template
                let containerData = _this.renderContainer(objectGraph);
                $(".fg-cms-page-elements-container").append(containerData);
                FgDirtyFields.addFields(containerData);


            } else {
                let columnData = '';
                if (oldCount < columnCount) {
                    let addedCount = parseInt(columnCount) - parseInt(oldCount);
                    let columnWidth = Math.floor(containerColumnSize / columnCount);
                    let objectGraph = { 'container': containerId, 'columnData': {} };
                    for (let i = 1; i <= addedCount; i++) {
                        let columnDummyId = 'new_' + parseInt(Math.random() * Math.pow(10, 5));
                        let gridSize = columnWidth;
                        let actualWidth = columnWidth * 2;
                        objectGraph.columnData[columnDummyId] = { 'columnId': columnDummyId, 'columnSize': actualWidth, 'gridSize': gridSize, 'container': containerId, 'sortOrder': parseInt(oldCount) + i };

                    }
                    columnData = _this.renderColumnOnly(objectGraph);
                    $("#pagecontainer-" + containerId).find(".editContainerpopup").attr('old_count', columnCount);
                    $("#pagecontainer-" + containerId).append(columnData);
                    //reorder the column size
                    _this.resizeColumnWidth(containerId);


                } else {
                    let reducedCount = parseInt(oldCount) - parseInt(columnCount);
                    let columnIds = [];
                    let currentColumnCount = $("#pagecontainer-" + containerId).find(".fg-portrait-column:visible").length;
                    let removeColumnFrom = columnCount - 1;
                    //iterate columns
                    let index: number = 1;
                    $("#pagecontainer-" + containerId).find(".fg-portrait-column:visible").each(function(i) {
                        if (i >= (removeColumnFrom){
                            columnIds[index] = $(this).attr("column-id");
                            index++;
                        }
                    });
                    //iterate all the column data for move from one column to another
                    let firstId = columnIds[1];
                    delete (columnIds[1])
                    $.each(columnIds, function(key, columnId) {
                        if (typeof columnId !== 'undefined') {
                            _this.moveColumnData(columnId, firstId, containerId);
                        }

                        //move data  
                    })
                    _this.resizeColumnWidth(containerId);
                    $("#pagecontainer-" + containerId).find(".editContainerpopup").attr('old_count', columnCount);
                    _this.sortColumnData();
                    _this.reorderDataList();
                    FgDirtyFields.updateFormState()

                }

            }
            _this.displayManageIcon();
            FgModelbox.hidePopup();
            _this.sortContainer();
            _this.sortColumnData();


        })
    }

    public containerDelete() {
        $("body").on('click', '.fg-dev-temp-delete', function(ele) {
            ele.stopImmediatePropagation();
            $(this).parents().eq(1).remove();

        })
    }

    public sortContainer() {
        let startIndex = 0;
        let _this = this;
        let containerOption = {
            tolerance: 'pointer',
            items: "> div.fg-portrait-container",
            stop: function(event, ui) {
                _this.reorderDataList();
                _this.showAllDeleteIcon();
                _this.manageFirstContainerDeleteIcon()
                FgDirtyFields.updateFormState()
            }
        };
        this.sortableEvent('.fg-portraite-main-container', containerOption);
    }

    public sortableEvent(identifier: string, sortoptions: any) {
        this.sortSetting = $.extend(true, {}, this.defaultSortOptions, sortoptions);
        $(identifier).sortable(this.sortSetting);
    }

    private connectColumnStage3AddButton() {
        let _this = this;
        $(document).on('click', '#saveContactListColumnPopup', function(event) {
            FgModelbox.hidePopup();
            var selectedFieldType = ($('.contact-list-table-column-type').val() != '') ? $('.contact-list-table-column-type').val() : '';
            var selectedField = ($('.fg-dev-contact-list-table-secondDp').val() != '') ? $('.fg-dev-contact-list-table-secondDp').val() : '';
            var selectedGroupValue = ($('.fg-dev-contact-list-table-secondDp :selected').parent().attr('value') != '') ? $('.fg-dev-contact-list-table-secondDp :selected').parent().attr('value') : '';
            var selectedColumns = $('#selectedcolumnId').val();
            let selectedContainer = $('#selectedcontainerId').val();
            var htmlFinal = _this.getRowHtmlContactList(selectedFieldType, selectedField, selectedGroupValue, contactListColumnJson, selectedContainer, selectedColumns);
            $("[portrait_column_id=" + selectedColumns + "]").append(htmlFinal);
            FgDirtyFields.addFields(htmlFinal);
            _this.handleNumberButtons();
            //$('form input[type=checkbox]').uniform();
            $('.fg-dev-multiple').uniform();
            $('select.selectpicker').selectpicker('render');
            FgFileUpload.init($('#image-uploader'), placeholderImageOption);

            // FgGlobalSettings.handleLangSwitch();
            //FgUtility.showTranslation(selectedLang);
            _this.manageFirstColumnLinebreakdisplay();

            _this.sortColumnData();
        });
    }

    private sortColumnData() {
        let _this = this;
        let fromContainer: String = '';
        let toContainer: String = '';
        let fromColumn: String = '';
        let toColumn: String = '';
        let columnDataOption = {
            tolerance: 'pointer',
            connectWith: '.portrait-data-sort',
            dropOnEmpty: true,
            items: "> li.portrait-data-field",
            start: function(event, ui) {
                fromColumn = $(ui.item).parent().attr('portrait_column_id');
                fromContainer = $(ui.item).parent().attr('container-id');

            },
            stop: function(event, ui) {
                toColumn = $(ui.item).parent().attr('portrait_column_id');
                toContainer = $(ui.item).parent().attr('container-id');
                $(ui.item).find("[data-key]").not(".fg-data-delete").each(function() {
                    let keyValue = $(this).attr('data-key');
                    let newKeyValue = keyValue.replace(fromContainer + ".column." + fromColumn, toContainer + ".column." + toColumn);
                    $(this).attr('data-key', newKeyValue);


                });
                if (fromContainer != toContainer) {
                    $(ui.item).find(".portraitDataColumn").attr('value', toColumn).trigger('change');
                } else if (fromColumn != toColumn) {
                    $(ui.item).find(".portraitDataColumn").attr('value', toColumn).trigger('change');
                }

                _this.reorderDataList();
                _this.manageFirstColumnLinebreakdisplay();
                FgDirtyFields.updateFormState();
            }
        };
        this.sortableEvent('.portrait-data-sort', columnDataOption);
    }

    private handleAddLabel() {
        let _this = this;
        $('body').off('click', '.fg-add-label');
        $('body').on('click', '.fg-add-label', function(event) {
            let htmlFinal = FGTemplate.bind('portraitFieldDataLabel', { "clubLanguages": clubLanguages, 'clubDefaultLang': defaultLang, 'portraitId': this.tableId, 'labelId': $(this).attr('id'), 'jsonData': $(this).attr('json-data'), 'dataId': $(this).attr('data-id') });
            FgModelbox.showPopup(htmlFinal);
            FgDirtyFields.updateFormState();
        });
    }

    private handleLabelLanguageSwitch() {
        let _this = this;
        $('body').off('click', '#editPageTitleBtn');
        $('body').on('click', '#editPageTitleBtn', function() {
            var data = {};
            $.each($('.cms-page-title-input'), function(i, v) {
                data[$(v).attr('data-lang')] = $.trim($("<span>" + $(v).val() + "</span>").text());
            });
            let labelId = $('#labelId').val();
            let dataId = $('#dataId').val();
            var defaultLabel = $('#fieldlabel_' + defaultLang).val();
            var labelText = $("<span>" + defaultLabel + "</span>").text();
            let htmlString = '';
            if ($.trim(labelText) != '') {
                htmlString = '<span class="fg-badge fg-badge-blue3">' + labelText + '</span>';
            } else {
                htmlString = '<i class="fg-plus-circle fa fa-2x"></i>' + _this.settings.translations.addLabelText;
            }
            $('#' + labelId).html(htmlString);
            $('#' + labelId).attr('json-data', JSON.stringify(data));
            $('#portraitDataLabel' + dataId).val(JSON.stringify(data));
            FgModelbox.hidePopup();
            FgDirtyFields.updateFormState();
            _this.manageLanguageSwitch();
        });
        /* function to show data in different languages on switching language */
        $('body').on('click', '.modal-content .pageTitle-popup-lang-switch button[data-elem-function=switch_lang]', function(e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
            let selectedLang = $(this).attr('data-selected-lang');
            $('.modal-content .pageTitle-popup-lang-switch button[data-elem-function=switch_lang]').removeClass('active');
            $(this).addClass('active');
            FgUtility.showTranslation(selectedLang);
        });
    }

    private manageColumnWidth() {
        let _this = this;

        $('body').on('click', '.fg-manage-columnwidth', function() {
            let clicked_this = $(this);
            let containerWidth = Math.floor(_this.settings.columnSize / _this.settings.portraitCount);
            let currentWidth = $('#' + $(this).attr('data_column_id')).attr('column_width_value');

            let newWidth: number = currentWidth;
            let total: number = 0;
            $(this).parents().eq(2).find('.fg-portrait-column:visible').each(function(e) {
                total = total + parseInt($(this).attr('column_width_value'));
            });
            let columnObj = clicked_this.attr('data_column_id');
            if (clicked_this.attr('width-type') == 'increase') {

                let sumWidth = parseInt(total + 1);
                if (sumWidth <= containerWidth) {
                    newWidth = parseInt(currentWidth) + 1;
                    $('#' + columnObj).attr('column_width_value', newWidth);
                }

            } else {
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
            let currentGrid = parseInt(currentWidth);
            let newGrid = parseInt(newWidth);
            let viewableWidth = currentWidth * 2;
            let addableWidth = newGrid * 2;
            $('#' + columnObj).addClass('col-sm-' + addableWidth + ' fg-grid-col-' + newGrid + ' col-' + newGrid).removeClass('col-sm-' + viewableWidth + ' fg-grid-col-' + currentGrid);
            var colId = $('#' + columnObj).attr('column-id');
            $('#' + columnObj).find('input[name=portraitColumnSize_' + colId + ']').attr('value', newWidth).val(newWidth).trigger('change');
            FgDirtyFields.updateFormState();
            _this.displayManageIcon();
        })
    }

    public saveWizardStage3(next) {
        let _this = this;
        _this.saveStage3(next);

    }

    private saveStage3(next) {
        let dataArray = {};
        let _this = this;
        _this.reorderDataList();


        //manage uploaded file details
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
                success: function(response) {
                    clickCount = 0;
                    FgInternal.showToastr(response.flash);
                    FgInternal.pageLoaderOverlayStop();
                    $.fn.dirtyFields.markContainerFieldsClean($("#contacts_portrait_element_stage3_form"));
                    if (next) {
                        _this.getStage4Data();
                    } else {
                        _this.getStage3Data();
                    }
                },
                dataType: 'json'
            });
        }
        _this.preventOneMoreMultipleAssignment();
    }

    private reorderDataList() {
        let i = 1;
        let j = 1;
        let k = 1;

        $('.fg-portrait-container').each(function() {
            let thisItem = $(this);
            thisItem.find('.fg-container-sort-order').attr('value', i);
            i++;
            j = 1;
            thisItem.find('.fg-portrait-column:visible').each(function() {
                let _this = $(this);
                _this.find('.fg-column-sort-order').attr('value', j).trigger('change');
                j++;
                k = 1;
                _this.find('.portrait-data-field').each(function() {
                    $(this).find('.fg-data-sort').attr('value', k).trigger('change');
                    k++;

                });

            });

        });
        FgDirtyFields.updateFormState();
    }

    //reduce the column count

    private moveColumnData(oldColumnId: String, newColumnId: String, containerId: String) {
        let _this = this;
        let htmlContent = $("#column-" + oldColumnId).html();
        let dummy = htmlContent;
        var $div = $('<div>').html(htmlContent);
        $div.find("[data-key]").each(function() {
            let keyValue = $(this).attr('data-key');
            let keyId = $(this).attr('id');
            let newKeyValue = keyValue.replace(".column." + oldColumnId, ".column." + newColumnId);
            $(this).attr('data-key', newKeyValue);

        });

        $div.find('.portrait-data-field').removeClass('ui-sortable-handle');
        $div.find('.fg-sortable-list').removeClass('ui-sortable');
        $div.find(".portraitColumns").attr('value', newColumnId);


        let dataContent = $div.find(".portrait-data-sort").html();

        if (isNaN(parseInt(oldColumnId))) {
            $("#column-" + oldColumnId).remove();
        } else {
            $("#column-" + oldColumnId).hide();

        }
        $("#column-" + oldColumnId).find(".fg-dev-column-delete").attr('value', 1);
        FgDirtyFields.updateFormState();
        $("#column-" + newColumnId).find(".portrait-data-sort").append(dataContent);
    }


    private renderColumnOnly(columnDatas: Object) {
        return FGTemplate.bind('portrait_column_template_only', { "columnDatas": columnDatas, 'portraitId': this.tableId, 'contactFieldDetail': contactFieldDetails });
    }

    private resizeColumnWidth(containerId: String) {
        let _this = this;
        let containerWidth = Math.floor(_this.settings.columnSize / _this.settings.portraitCount);
        let columnCount = $("#pagecontainer-" + containerId).find(".fg-portrait-column:visible").length;
        let columnWidth = (Math.floor(containerWidth / columnCount));
        let columnGrid = columnWidth;
        if (columnWidth == 0) {
            columnWidth++;
        }
        $("#pagecontainer-" + containerId).find(".fg-portrait-column:visible").each(function() {
            let currentWidth = $(this).attr('column_width_value');
            let currentGrid = parseInt(currentWidth);
            let viewableWidth = currentWidth * 2;
            let addableWidth = columnWidth * 2;
            let columnId = $(this).find(".portraitColumns").attr('value');
            $(this).attr('column_width_value', columnWidth);
            let addClass = 'col-sm-' + addableWidth + ' fg-grid-col-' + columnGrid;
            let removeClass = 'col-sm-' + viewableWidth + ' fg-grid-col-' + currentGrid;
            $(this).removeClass(removeClass).addClass(addClass);
            $('#column-' + columnId).find('input[name=portraitColumnSize_' + columnId + ']').attr('value', columnWidth).val(columnWidth).trigger('change');

        })
        FgDirtyFields.updateFormState();
    }

    //show/hide increase decrease icon

    private displayManageIcon() {
        let containerWidth = Math.floor(this.settings.columnSize / this.settings.portraitCount);
        $(".fg-portrait-container").each(function() {
            let clickedContainer = $(this);
            let columnTotalWidth = 0;
            clickedContainer.find('.fg-portrait-column:visible').each(function() {
                let clickedColumn = $(this);
                let columnWidth = $(this).attr('column_width_value');
                columnTotalWidth = columnTotalWidth + parseInt(columnWidth);
                (parseInt(clickedColumn.attr('column_width_value')) <= 1) ? clickedColumn.find('.fg-left').hide() : clickedColumn.find('.fg-left').show();

            })

            if (containerWidth > columnTotalWidth) {
                clickedContainer.find('.fg-right').show();
            } else {
                clickedContainer.find('.fg-right').hide();
            }

        })

    }

    private manageLanguageSwitch() {
        let _this = this;
        $('body').off('click', '.fg-dev-portrait .btlang');
        $('body').on('click', '.fg-dev-portrait .btlang', function(e) {
            let buttonThis = $(this);            
            $(".fg-add-label").each(function() {
                let jsonData = $(this).attr("json-data");
                let jsonArray = $.parseJSON(jsonData);
                let language = buttonThis.attr('data-selected-lang');
                let htmlString = '';
                console.log(jsonArray);
                console.log(language,'val:',jsonArray[language]);
                if ((language in jsonArray) && (jsonArray[language] != '' && jsonArray[language] != null) {
                    htmlString = '<span class="fg-badge fg-badge-blue3">' + jsonArray[language] + '</span>';
                } else {
                    htmlString = '<i class="fg-plus-circle fa fa-2x"></i>' + _this.settings.translations.addLabelText;
                }
                $(this).html(htmlString);

            })

        })
    }
    private manageFirstColumnLinebreakdisplay() {
        let _this = this;
        $('.fg-portrait-column:visible').each(function() {
            let _that = $(this);
            _that.find('li.list-group-item').each(function(i) {
                //hide line break of  first field in each column
                if (i == 0) {                    //first column line break hide
                    $(this).find(".fg-dev-line-break").hide();
                    $(this).find(".fa-break-icon").hide();
                } else {
                    $(this).find(".fg-dev-line-break").show();
                    let iconDisplay = $(this).find(".input-number").val();
                    if (iconDisplay == 2) {
                        $(this).find('.fa-long-arrow-down').show();
                    } else if (iconDisplay == 0) {
                        $(this).find('.fa-level-up').show();
                    }


                }
                //hide line break before ,if display type is image 
                if ($(this).find(".fg-dev-display-type").length > 0 && $(this).find(".fg-dev-display-type").val() == 'image') {
                    $(this).find(".fg-dev-line-break").hide();
                    $(this).find(".fa-break-icon").hide();
                }

            });
        });

    }
    private manageFirstContainerDeleteIcon() {
        let _this = this;
        $('div.columnboxsortable:first').find('.fg-dev-delete').hide();

    }
    private showAllDeleteIcon() {
        $('div.columnboxsortable').find('.fg-dev-delete').show();
    }

    private preventOneMoreMultipleAssignment() {
        $('body').on('click', '.fg-dev-multiple', function() {
            let checked = false;
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
        //handle label click
        $('body').on('click', '.fg-dev-multiple-label', function() {
            $(this).parent('.fg-checkbox').find('.fg-dev-multiple').trigger('click');
        });


    }

    private displayToggleLineBreak() {
        $('body').on('change', '.fg-dev-display-type', function() {

            if ($(this).val() == 'image') {
                $(this).parents('.list-group-item').find('.fg-dev-line-break').hide();
            } else {
                $(this).parents('.list-group-item').find('.fg-dev-line-break').show();
            }


        })
    }

    //fg-dev-multiple

}