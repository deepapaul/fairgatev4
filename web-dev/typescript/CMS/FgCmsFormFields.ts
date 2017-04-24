/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgCmsFormFields {

    formId: any;
    clubLanguages: any;
    clubDefaultLanguage: any;
    hasAdminRights: any;
    editSignaturePath: any;
    constructor(meta) {
        this.setElementAddEvents();
        this.formId = meta.formId;
        this.event = meta.event;
        this.hasAdminRights = meta.hasAdminRights;       
        this.defaultTranslations = meta.defaultTranslations; 
        this.clubLanguages = meta.clubLanguages;
        this.clubDefaultLanguage = meta.clubDefaultLang;
        this.currentStage = (meta.currentStage > 0) ? meta.currentStage : 1;
        this.editSignaturePath = meta.editSignaturePath;

        this.connectButtonClick();
        this.setFormElementSortable();
        this.setFormElementRemove();
        this.setElementAddOptionValue();
        this.connectStageClick();
        this.connectDatepickerClick();
    } 

    public connectButtonClick() {
        let _this = this;
        $('#form_element_save').on('click', function() {
            if($(this).attr('disabled') == 'disabled')
                return;

            if (_this.getCurrentStage() == 1) {
                _this.saveWizardStage1(false);
            } else if (_this.getCurrentStage() == 2) {
                _this.saveWizardStage2(false);
            } else if (_this.getCurrentStage() == 3) { 
                _this.saveWizardStage3(false);
            }
        });

        $('#form_element_back').on('click', function() {
            if($(this).attr('disabled') == 'disabled')
                return;

            if (_this.getCurrentStage() == 3) {
                $('.nav-pills li:eq(1) a').tab('show');
                _this.getStage2Data();
            } else if (_this.getCurrentStage() == 2) {
                $('.nav-pills li:eq(0) a').tab('show');
                _this.getStage1Data();
            }
        });

        $('#form_element_save_and_next').on('click', function() {
            if($(this).attr('disabled') == 'disabled')
                return;

            if (_this.getCurrentStage() == 1) {
                _this.saveWizardStage1(true);
            } else if (_this.getCurrentStage() == 2) {
                _this.saveWizardStage2(true);
            }
        });

        $('#form_element_finish').on('click', function() {
            if($(this).attr('disabled') == 'disabled')
                return;

            _this.saveWizardStage3(true);
        });

        $('#form_element_discard').on('click', function() {
            if($(this).attr('disabled') == 'disabled')
                return;

            if (_this.getCurrentStage() == 1) {
                _this.getStage1Data();
            } else if (_this.getCurrentStage() == 2) {
                _this.getStage2Data();
            } else if (_this.getCurrentStage() == 3) { 
                _this.getStage3Data();
            }
        });
    }

    public connectStageClick() {
        let _this = this;
       $('ul.steps li').on('click', function() {
           let stage = $(this).attr('data-target');

           if($(this).hasClass('disabled'))
               return false;
           
           if(stage == 'form-stage1'){
               _this.getStage1Data();
           } else if(stage == 'form-stage2'){
               _this.getStage2Data();
           } else if(stage == 'form-stage3'){
               _this.getStage3Data();
           }
       });
    }

    public connectDatepickerClick() {
        $('#formFields').on('click', '.fa-calendar', function(){
            $(this).parent().parent().find('input').datepicker('show');
        })
    }

    public getCurrentStage() {
        return this.currentStage;
    }

    public setCurrentStage(stage) {
        this.currentStage = stage;
        return;
    }

    public setElementAddEvents() {
        let _this = this;
        $('#addFormField, #addSeperator').click(function() {
            var clickedLink = $(this).attr('id');
            var randomId = 'new' + parseInt(Math.random() * Math.pow(10, 5));
            var sortOrder = $('#formFields ul>li.list-group-item').length + 1;
            var intialData = [
                {
                    'fieldType': (clickedLink == 'addFormField') ? 'default' : 'heading',
                    'formElementSortOrder': sortOrder,
                    'formFieldId': randomId,
                    'formId': _this.formId,
                    'source': 'new'
                }
            ];
            _this.loadFormFields(intialData);
            _this.showTranslation();
            _this.reorderElementList($('#formFields>li'), 'fieldSort');
            $.fn.dirtyFields.updateFormState($("#form-field-elements-form-stage1"));
        });

    }

    public setElementAddOptionValue() {
        let _this = this;
        $('body').on('click', '.add_field_option_value', function() {
            var clickedLink = $(this).attr('data-field');
            var randomId = 'new' + parseInt(Math.random() * Math.pow(10, 5));
            var sortOrder = $('#formElement_value_' + clickedLink + '>li.list-group-item').length + 1;
            var JOSN = '{ "formFieldId": "'+clickedLink+'","options":{"'+randomId+'":{"isDeleted" : 0,"isActive":1}} }';
            var newRow = _this.getOptionValuesHtml(JSON.parse(JOSN), randomId);
            $('ul#formElement_value_' + clickedLink).append(newRow);

            _this.reorderElementList($('ul#formElement_value_' + clickedLink + '>li'), 'optionSort');
            $.fn.dirtyFields.updateFormState($("#form-field-elements-form-stage1"));
        });

    }

    public setElementInitialOptionValue(data) {
       var randomId= 'new'+_.random(0, 99999);
       var JOSN = '{ "formFieldId": "'+data.formFieldId+'","options":{"'+randomId+'":{"isDeleted" : 0,"isActive":1}} }';
       return this.getOptionValuesHtml(JSON.parse(JOSN), randomId);
    }

    public loadWizardStage1(formData) {

        this.setCurrentStage(1);

        //clear alreaderd rendered contnet
        $('#form-stage-progressbar .progress-bar').css('width','33%');
        $('#formFields-button').html('');
        $('#formFields').html('');

        //populate form name
        this.loadFormName(formData['name']);

        if(formData['elementArray'].length == 0){
            //create a first element
            formData['elementArray'] = [{'fieldType':'default','formElementSortOrder': 1,'formFieldId': 'new'+_.random(1000, 9999),'formId': this.formId,'source': 'new'}];
        } 

        //populate the form fields
        this.loadFormFields(formData['elementArray']);

        //Load the button html when the event is create and existing is false
        if($('#formwizard_event').val() == 'create' && $('#existing').val() == 0){
            var buttonData = {
                    'fieldType': 'button',
                    'formFieldId': 'new' + parseInt(Math.random() * Math.pow(10, 5)),
                    'formId': this.formId,
                    'source': 'new'
                };
            formHtml = this.getFieldRowHtml(buttonData);

            if($('#formFields-button input').length == 0){
                $('#formFields-button').html(formHtml);
            }
        }

        FormValidation.init('form-field-elements-form-stage1');
        this.setTranslationTabError('form-field-elements-form-stage1');
        this.renderFormButtons();
        this.setStepTitle();
        this.setlanguageSwitchActive();

    }

    public loadFormName(formName) {
        $('#formname').val(formName);
    }

    public loadFormFields(formElementArray) {

        //populate form fields

        //Loop through element Data
        let _this = this;
        var formHtml = '';
        _.each(formElementArray, function(data) {
            if ($.inArray(data.fieldType, ["captcha"]) == -1) {
                formHtml = _this.getFieldRowHtml(data);
                var elementIdArray = $(formHtml).attr('id').split('_');

                //append the html
                if (data.fieldType == 'button') {
                    $('#formFields-button').html(formHtml);
                } else {
                    $('#formFields').append(formHtml);
                    _this.setEvents(elementIdArray[1]);
                    _this.setFieldChangeEvent(elementIdArray[1]);
                }
            } else if (data.fieldType = "captcha") {
                //set the captcha option value
                $('#formFields-captcha').attr('checked', 'checked');
                $.uniform.update('#formFields-captcha');
            }
        })
    }

    public setFormElementRemove() {
        let _this = this;
        $('#formFields').on('click', '.fg-delete-row', function() {
            var source = $(this).parent('li').attr('data-source');
            if (source == 'new') {
                FgDirtyFields.removeFields($(this).parent('li'));
                $(this).parent('li').remove();
            } else {
                if ($(this).find('input.make-switch:checked').length == 0) {
                    $(this).parent('li').removeClass('inactiveblock');

                    // set required="true"
                    $(this).parent('li').find('input[data-required="true"]').attr('required', 'required');
                } else {
                    $(this).parent('li').addClass('inactiveblock');

                    // set required="false"
                    $(this).parent('li').find('input[data-required="true"]').removeAttr('required').parent().removeClass('has-error').find('.help-block').remove();
                }
            }

            _this.reorderElementList($(this).closest('ul').children('li.list-group-item'), $(this).hasClass('optionDelete')?'optionSort':'fieldSprt');
        })
    }
    public isUndefined(data, keys) {
        try {
            var dataArray = data;
            keys.forEach(function(value, key) {
                dataArray = dataArray[value];
            });
            return 0;
        }
        catch (err) {
            return true;
        }
    }

    public renderOptionHtml(data) {
        return this.getOptionHtml(data);
    }

    public saveWizardStage1(next) {
        if (this.validateWizardStage1()) {

            //get form data
            let formData = this.getFormData();

            FgInternal.pageLoaderOverlayStart();
            //save form data
            this.saveStage1(formData, next);
        }
    }

    public getOptionValuesHtml(data, key) {
        let templateId = 'form_field_templates_add_value';
        data.clubDefaultLanguage = this.clubDefaultLanguage;
        data.clubLanguages = this.clubLanguages;
        data.formId = this.formId;
        let options = data.options;
        let optionsArr = new Array();
        _.each(options, function(val) {
            optionsArr.push(val);
        });
        optionsArr = optionsArr.sort(function(a, b) {
            return a.sortOrder > b.sortOrder;
        });
        let count = 0;
        $.each(data.options, function(idx, obj) {
            data.options[idx] = optionsArr[count];
            count = count + 1;
        });
        let htmlToBeRendered = FGTemplate.bind(templateId, { "data": data, 'key': key });
        return htmlToBeRendered;
    }

    public getStage1Data(){
        let _this = this;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
                type: "POST",
                url: formDataPath,
                data: {'stage':'1', 'formId':_this.formId},
                success: function(response) {
                    if(response.error == null){
                        _this.loadWizardStage1(response.form.stage1.form);
                        FgInternal.pageLoaderOverlayStop();
                        _this.setWizardStage(response.meta.formStage);
                    }
                },
                dataType: 'json'
            });
    }

    public getStage2Data(){
        let _this = this;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
                type: "POST",
                url: formDataPath,
                data: {'stage':'2', 'formId':_this.formId},
                success: function(response) {
                    if(response.error == null){
                        _this.loadWizardStage2(response);
                        FgInternal.pageLoaderOverlayStop();
                        _this.setWizardStage(response.meta.formStage);
                    }
                },
                dataType: 'json'
            });
    }

    public loadWizardStage2(formData) {
        let _this = this;

        _this.setCurrentStage(2);
        $('#form-stage-progressbar .progress-bar').css('width','66%');

        let data = {
            'formId' : _this.formId,
            'form' : formData.form.stage2,
            'clubDefaultLang' : formData.meta.clubDefaultLang,
            'clubLanguages' : formData.meta.clubLanguages,
            'hasAdminRights' : _this.hasAdminRights,
            'defaultTranslations': _this.defaultTranslations,
            'editSignaturePath' : _this.editSignaturePath         
        }            
        let htmlToBeRendered = FGTemplate.bind('formElementStage2', data);
        $('#formelement-stage2').html(htmlToBeRendered);

        //connect elements

        //connect uniform
        $("#formelement_stage2_reply").uniform();
        $("#formelement_stage2_reply").on('click', function(){
            var checked = $(this).is(':checked');
            if(checked){
                $('#formelement_stage2_senderemail').attr('disabled','disabled');
                $('#formelement_stage2_senderemail').val($('#formelement_stage2_senderemail_default').html()).trigger('change');
            } else {
                $('#formelement_stage2_senderemail').removeAttr('disabled');
                $('#formelement_stage2_senderemail').val('');
            }
        });

        _this.connectCKEditorStage2(formData.meta.clubLanguages);
        _this.connectAutocomplete(formData.form.stage2.recipientlist);
        FormValidation.init('form-field-elements-form-stage2');
        _this.renderFormButtons();
        $('#formelement_stage2_senderemail').trigger('change');//To properly disable the buttons using dirty fields
        this.setTranslationTabError('form-field-elements-form-stage2');
        this.setStepTitle();
        this.setlanguageSwitchActive();
    }


    public getStage3Data(){
        let _this = this;
        FgInternal.pageLoaderOverlayStart();
        $.ajax({
                type: "POST",
                url: formDataPath,
                data: {'stage':'3', 'formId':_this.formId},
                success: function(response) {
                    if(response.error == null){
                        _this.loadWizardStage3(response);
                        FgInternal.pageLoaderOverlayStop();
                        _this.setWizardStage(response.meta.formStage);
                    }
                },
                dataType: 'json'
            });
    }

    public loadWizardStage3(formData) {
        let _this = this;
        $('#formelement-stage2').html('');
        _this.setCurrentStage(3);
        $('#form-stage-progressbar .progress-bar').css('width','100%');

        let data = {
            'formId' : _this.formId,
            'form' : formData.form.stage3,
            'clubDefaultLang' : formData.meta.clubDefaultLang,
            'clubLanguages' : formData.meta.clubLanguages
        }
        let htmlToBeRendered = FGTemplate.bind('formElementStage3', data);
        $('#formelement-stage3').html(htmlToBeRendered);
        FormValidation.init('form-field-elements-form-stage3');
        _this.renderFormButtons();
        $('#form-field-elements-form-stage3 textarea:first').trigger('change')//To properly disable the buttons using dirty fields
        if($('#form-field-elements-form-stage3 textarea[data-set-value]').length > 0){
            var defaultValue = $('#form-field-elements-form-stage3 textarea[data-set-value]').val();
            $('#form-field-elements-form-stage3 textarea[data-set-value]').val(defaultValue.replace(' ','')).trigger('change');
        }
        this.setTranslationTabError('form-field-elements-form-stage3');
        this.setStepTitle();
        this.setlanguageSwitchActive();
    }

    public saveWizardStage2(next){
        //save stage 2
        FgInternal.pageLoaderOverlayStart();
        if (this.validateWizardStage2()) {
            this.saveStage2(next);
        } else {
            FgInternal.pageLoaderOverlayStop();
        }
    }

    public saveWizardStage3(finish){
        //save stage 2
        FgInternal.pageLoaderOverlayStart();
        if (this.validateWizardStage3()) {
            this.saveStage3(finish);
        } else {
            FgInternal.pageLoaderOverlayStop();
        }
    }

    private getFieldRowHtml(data) {
        var templateId = (data.fieldType == 'button') ? 'form_field_templates_button' : 'newFormElement';
        data.clubDefaultLanguage = this.clubDefaultLanguage;
        data.clubLanguages = this.clubLanguages;
        var htmlToBeRendered = FGTemplate.bind(templateId, { "data": data });
        return htmlToBeRendered;
    }

    private getOptionHtml(data) {
        let templateId = 'form_field_templates_' + data.fieldType;
        data.clubDefaultLanguage = this.clubDefaultLanguage;
        data.clubLanguages = this.clubLanguages;
        let htmlToBeRendered = FGTemplate.bind(templateId, { "data": data });
        return htmlToBeRendered;
    }

    private setEvents(elementId) {
        this.setUIPlugins(elementId);
    }

    private setFieldChangeEvent(fieldId) {
        let _this = this;
        $("#fieldType_" + fieldId).change(function() {
            let formData = {};
            formData = FgInternalParseFormField.formFieldParse('formElement_' + fieldId);
            formData[_this.formId][fieldId]['formId'] = _this.formId;
            formData[_this.formId][fieldId]['formFieldId'] = fieldId;
            formData[_this.formId][fieldId]['clubLanguages'] = _this.clubLanguages;
            formData[_this.formId][fieldId]['clubDefaultLanguage'] = _this.clubDefaultLanguage;
            let postData = formData[_this.formId][fieldId];
            let templateId = 'form_field_templates_' + postData.fieldType;
            $("#typeOptions-" + fieldId).html(FGTemplate.bind(templateId, { 'data': postData }));

            if(postData.fieldType == 'default'){
                $('#typeOptions_'+fieldId+'_container').addClass('hide');
                $('#isActive_'+fieldId+'_container').addClass('hide');
            } else {
                $('#typeOptions_'+fieldId+'_container').removeClass('hide');
                $('#isActive_'+fieldId+'_container').removeClass('hide');
            }

            _this.setEvents(fieldId);
            _this.showTranslation()
        });
    }

    private setFormElementSortable() {
        let _this = this;
        $("#formFields").sortable({
            items: "> li.list-group-item",
            containment: "#form-field-elements-form-stage1",
            handle: ".fg-dev-field-sort-handle",
            stop: function(event, ui) {
                _this.reorderElementList($('#formFields>li'), 'fieldSort');
            }
        });
    }

    private setOptionSortable(fieldId) {
        let optionListContainer = $('#formElement_value_' + fieldId);
        let _this = this;
        optionListContainer.sortable({
            items: "> li.list-group-item",
            containment: $('#typeOptions-'+fieldId),
            handle: ".fg-dev-optionvalue-sort-handle",
            stop: function(event, ui) {
                _this.reorderElementList($('ul#formElement_value_' + fieldId + '>li'), 'optionSort');
            }
        });
    }

    private validateWizardStage1() {
        let valid = true;
        $('.link-error').removeClass('link-error');
        //remove all current error messages
        $('#alert_noFieldError').remove();

        //validate if atleast one element is added
        if ($('.fg-dev-field-delete').not(':checked').length == 0) {
            $('.fg-cms-form-element-create-container').prepend(FGTemplate.bind('noFieldError', {}));
            valid = false;
        }

        if ($('select.form_field_type').find(":selected[value='default']").length > 0) {
            $('.fg-cms-form-element-create-container').prepend(FGTemplate.bind('emptyFieldTypeError', {}));
            valid = false;
        }
        

        //validate option count 
        $('.fg-option-value-list').each(function() {
            if ($(this).find('li').length == 0) {
                $(this).parents('li').find('.fg-toggle-link').addClass('link-error');
            }
        });

        //validate form field using jquery validator 
        if (!$('#form-field-elements-form-stage1').valid()) {

            //set error to option toggle link
            $('.fg-option-value-list').each(function() {
                let error = $(this).find('.help-block').length;
                if (parseInt(error) >= 1) {
                    $(this).parents('li').find('.fg-toggle-link').addClass('link-error');
                }
            })

            valid = false;
        }

        $('.fg-collapsed-content-wrapper').find('.has-error').each(function(){
          $(this).parents('li.list-group-item').find('.fg-toggle-link').addClass('link-error');
        });

        this.showTranslation();
        return valid;
    }

    private saveStage1(formData, next) {
        //set ajax and validate form name + save
        let _this = this;
        formData.formId = _this.formId;
        $.ajax({
            type: "POST",
            url: savePath,
            data: formData,
            success: function(response) {
                if (response.result == 'success') {
                    FgInternal.pageLoaderOverlayStop();
                    FgInternal.showToastr(formSaveSuccess);
                    if(next){
                        //Load stage 2
                        _this.formId = response.meta.formId;
                        $('#formFields-button').html('');
                        $('#formFields').html('');
                        $('.nav-pills li:eq(1) a').tab('show');
                        _this.getStage2Data();
                    } else {
                        //Load stage 1
                        _this.formId = response.meta.formId;
                        _this.loadWizardStage1(response.form);   
                    }
                    _this.event = 'edit';
                    $('#formwizard_event').val('edit');
                } else if (response.result == 'formerror') {
                    $('#form-field-elements-form-stage1').validate().showErrors({ 'formname': response.message });
                    jQuery('html,body').animate({ scrollTop: ($('#formname').offset().top - 60) }, 0);
                    FgInternal.pageLoaderOverlayStop();
                } else if (response.result == 'error') {

                }
            },
            dataType: 'json'
        });
    }

    private getFormData() {
        //use form parse
        let postData = {};
        postData.formFieldData = FgInternalParseFormField.formFieldParse('form-field-elements-form-stage1');

        //append other data
        postData.formname = $('#formname').val();
        postData.existing = $('#existing').val();
        postData.pageId = $('#pageId').val();
        postData.boxId = $('#boxId').val();
        postData.sortOrder = $('#sortOrder').val();
        postData.event = $('#formwizard_event').val();
        postData.captchaEnabled = ($('#formFields-captcha').is(':checked')) ? 1 : 0;

        return postData;
    }

    private changeNumberValue(action, defaultVal, obj) {
        let currentVal: number = parseInt($(obj).parent().find('input').val());
        if (!isNaN(currentVal)) {
            let newVal: any;
            if (action == 'minus') {
                newVal = currentVal - 1;
            } else if (action == 'plus') {
                newVal = currentVal + 1;
            }
            $(obj).parent().find('input').val(newVal);
        }
        else {
            $(obj).val(defaultVal);
        }
    }

    private setUIPlugins(fieldId) {
        let _this = this;
        //set datepicker, bootstrap-select, uniform, timepicker, and other events
        let fieldType = $('#fieldType_' + fieldId).val();

        //set uniform
        $("input[type=checkbox]:not(.toggle, .make-switch), input[type=radio]:not(.toggle, .star, .make-switch)").uniform();

        //set bootstrap select to field type
        $('#fieldType_' + fieldId).selectpicker();

        switch (fieldType) {
            case 'singleline':
            case 'multiline':
            case 'email':
            case 'url':
            case 'fileupload':
            default:

                break;
            case 'number':
                var num = new FgNumber({'selector':'#'+fieldId+'_wrapper .selectButton','inputNum':'#'+fieldId+'_wrapper input.input-number'});
                num.init();
                break;
            case 'date':
                //set date picker
                let dateSettings = {
                    language: jstranslations.localeName,
                    format: FgLocaleSettingsData.jqueryDateFormat,
                    autoclose: true,
                    weekStart: 1,
                    clearBtn: true
                };

                $('#formElement_date_minValue_' + fieldId).datepicker(dateSettings).on('changeDate', function(ev) {
                    let selectedDate = $(this).val();
                    $('#formElement_date_maxValue_' + fieldId).datepicker('setStartDate', selectedDate);
                });
                $('#formElement_date_maxValue_' + fieldId).datepicker(dateSettings).on('changeDate', function(ev) {
                    let selectedDate = $(this).val();
                    $('#formElement_date_minValue_' + fieldId).datepicker('setEndDate', selectedDate);
                });

                break;
            case 'time':
                //set time picker
                break;
            case 'checkbox':
            case 'select':
            case 'radio':
                this.setOptionSortable(fieldId);
                break;
        }
    }

    private showTranslation() {
        let currentSelectedLanguage = $('.fg-lang-tab .active').attr('id');
        if(currentSelectedLanguage == '' || typeof currentSelectedLanguage == 'undefined')
            currentSelectedLanguage = this.clubDefaultLanguage;

        FgUtility.showTranslation(currentSelectedLanguage);
    }

    private reorderElementList(list, sortElement) {
        let z = 0;
        list.each(function(order, element) {
            if (!$(this).hasClass('inactiveblock')) {
                let sort = ++z;
                $(this).find('.'+sortElement).val(sort).attr('value',sort).trigger('change');
            }
        })
    }

    private connectCKEditorStage2(clubLanguages){
        let toolbarConfig = ckEditorConfig.cmsEditor;

        CKEDITOR.config.dialog_noConfirmCancel = true;
        CKEDITOR.config.extraPlugins='confighelper';
        CKEDITOR.config.allowedContent = {
            $1: {
                // Use the ability to specify elements as an object.
                elements: CKEDITOR.dtd,
                attributes: true,
                styles: true,
                classes: true
            }
        };
        CKEDITOR.config.disallowedContent = 'script; *[on*]';  

        CKEDITOR.on('dialogDefinition', function( ev ) {
            var diagName = ev.data.name;
            var diagDefn = ev.data.definition;
            if(diagName === 'table') {
                var infoTab = diagDefn.getContents('info');
                var width = infoTab.get('txtWidth');
                width.default = "100%";
                //Overidden the parent width.onChange event.
                width.onChange = function(){
                    var id = this.domId;
                    $('#' + id + ' input').attr('readonly','readonly');
                    return false;
                };
            }
        });

       let _this = this;
        _.each(clubLanguages,function(lang,key){
            let instanceName = _this.formId + '_content_' + lang;
            if( $('#' + instanceName).length > 0 ) {
                if(CKEDITOR.instances[instanceName]){
                    try {
                        //It will cause error when the CKEditor html is replaced manually by FGDirty field, but the distance is not removed
                        CKEDITOR.instances[instanceName].destroy();
                    } catch (error){}
                    delete CKEDITOR.instances[instanceName];
                }
                CKEDITOR.replace(instanceName, {
                    toolbar: toolbarConfig,
                    language :lang
                }).on('change',function(){
                    let content = CKEDITOR.instances[instanceName].document.getBody().getHtml().replace('<p><br></p>', '');
                    $('#' + instanceName).val(content).text(content).trigger('change');
                });
                CKEDITOR.instances[instanceName].addContentsCss('/fgcustom/css/fg-ckeditor-mail.css');

                if($('#' + instanceName).attr('data-required') == 'true'){
                    $('#' + instanceName).attr('required', 'required');
                }                               
            }
        });
    }

    private connectAutocomplete(selectedRecipients){
        let formId = this.formId;
        let selectedIds = [];

        if(selectedRecipients != null){
            _.each(selectedRecipients, function(name, id){
                selectedIds.push({'id':id,'title':name});
            })   
        }

        $('#' + formId + '_recipients').fbautocomplete({
            url: FgInternalVariables.topNavSearchUrl, // which url will provide json!
            maxItems: 0, // only one item can be selected                
            useCache: false,
            selected: selectedIds,
            onItemSelected: function($obj, itemId, selected) { 
                let alreadySelectedvalues = _.compact($('#' + formId + '_recipients_data').val().split(','));
                alreadySelectedvalues.push(itemId);
                $('#' + formId + '_recipients_data').val(_.uniq(alreadySelectedvalues).join()).trigger('change');
            },
            onItemRemoved : function($obj, itemId){
                let alreadySelectedvalues = _.compact($('#' + formId + '_recipients_data').val().split(','));
                alreadySelectedvalues = _.reject(alreadySelectedvalues, function(v){ return v == itemId; });
                $('#' + formId + '_recipients_data').val(_.uniq(alreadySelectedvalues).join()).trigger('change');
            }                
        });
    }

    private validateWizardStage2(){
        let valid = true;
        valid = $('#form-field-elements-form-stage2').valid();

        return valid;
    }

    private saveStage2(next){
        //
        let dataArray = {};
        let _this = this;
        dataArray.formData = FgInternalParseFormField.formFieldParse('formelement-stage2');
        dataArray.formId = this.formId;

        $.ajax({
            type: "POST",
            url: stage2SavePath,
            data: dataArray,
            success: function(response) {
                FgInternal.showToastr(formSaveSuccess);
                FgInternal.pageLoaderOverlayStop();
                $.fn.dirtyFields.markContainerFieldsClean($("#form-field-elements-form-stage2"));
                if(next){
                    $('.nav-pills li:eq(2) a').tab('show');
                    _this.getStage3Data();
                } else {
                    _this.getStage2Data();
                }
            },
            dataType: 'json'
        });
    }

    private validateWizardStage3(){
        let valid = true;
        valid = $('#form-field-elements-form-stage3').valid();

        return valid;
    }

    private saveStage3(finish){
        //
        let dataArray = {};
        let _this = this;
        dataArray.formData = FgInternalParseFormField.formFieldParse('formelement-stage3');
        dataArray.formId = this.formId;
        if(finish){
            dataArray.finish = finish;
        }

        $.ajax({
            type: "POST",
            url: stage3SavePath,
            data: dataArray,
            success: function(response) {
                $('#form_element_finish').addClass('hide');
                FgInternal.pageLoaderOverlayStop();
                FgInternal.showToastr(formSaveSuccess);
                $.fn.dirtyFields.markContainerFieldsClean($("#form-field-elements-form-stage3"));
                if(finish){
                   window.location.href = $('#form_element_finish').data('href');
                } else {
                    _this.getStage3Data();
                }
            },
            dataType: 'json'
        });
    }

    private renderFormButtons(){
        let _this = this;
        let stage = _this.getCurrentStage();
        $('#form_element_save_and_next,#form_element_save,#form_element_cancel,#form_element_discard,#form_element_back,#form_element_finish').addClass('hide');
        if(stage == 1){
            if(_this.event == 'create'){
                $('#form_element_save_and_next,#form_element_save,#form_element_cancel').removeClass('hide');
            } else {
                $('#form_element_save_and_next,#form_element_save,#form_element_discard').removeClass('hide');
            }
        } else if(stage == 2){
            $('#form_element_save_and_next,#form_element_save,#form_element_discard,#form_element_back').removeClass('hide');
        } else if(stage == 3){
            $('#form_element_save,#form_element_discard,#form_element_finish,#form_element_back').removeClass('hide');
        }
        _this.initDirtyFields();
    }

    private initDirtyFields(){
        let _this = this;
        let stage = _this.getCurrentStage();
        
        if(stage == 1){
            let formId = 'form-field-elements-form-stage1';
        } else if(stage == 2){
           let formId = 'form-field-elements-form-stage2';
        } else if(stage == 3){
           let formId = 'form-field-elements-form-stage3';
        }

        FgDirtyFields.init(formId, {enableDiscardChanges: false, setInitialHtml: false, formChangeCallback: function(isDirty){_this.setFormDirty(formId, isDirty)}});
    }

    private setFormDirty(formId, isDirty){
        if(this.getCurrentStage() == 1 && this.event == 'create')
            return;

        if(isDirty){
            $('#form_element_save,#form_element_discard,#form_element_save_and_next').removeAttr('disabled');
        } else {
            $('#form_element_save,#form_element_discard,#form_element_save_and_next').attr('disabled','disabled');
        }
    }

    private setStepTitle(){
        let stage = this.getCurrentStage();
        $('.portlet-title .step-title').html(stepTranslation.replace('%S%',stage));
    }

    private setTranslationTabError(formId){
        FgLanguageSwitch.checkMissingTranslation(this.clubDefaultLanguage, formId);
    }

    private setlanguageSwitchActive(){
        $(".fg-action-language-switch button.active").removeClass('active');
        $('#'+this.clubDefaultLanguage).addClass('active');
    }

    private setWizardStage(formStage){
        if(formStage == 'stage1'){
            $('.nav>li[data-target="form-stage1"]').addClass('done');
            $('.nav>li[data-target="form-stage2"]').removeClass('disabled');
            $('.nav>li[data-target="form-stage3"]').addClass('disabled');
        } else if(formStage == 'stage2'){
            $('.nav>li[data-target="form-stage1"]').addClass('done');
            $('.nav>li[data-target="form-stage2"]').addClass('done');
            $('.nav>li[data-target="form-stage3"]').removeClass('disabled');
        } else if(formStage == 'stage3'){
            $('.nav>li[data-target="form-stage1"]').addClass('done');
            $('.nav>li[data-target="form-stage2"]').addClass('done');
            $('.nav>li[data-target="form-stage3"]').addClass('done');
        }

        let currentStage = this.getCurrentStage(); 
        if(currentStage == 1){
             $('.nav>li[data-target="form-stage1"]').removeClass('done').addClass('active');
        } else if(currentStage == 2){
             $('.nav>li[data-target="form-stage2"]').removeClass('done').addClass('active');
        } else if(currentStage == 3){
             $('.nav>li[data-target="form-stage3"]').removeClass('done').addClass('active');
        }
    }    
}