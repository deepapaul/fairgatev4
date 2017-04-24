/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgWebsiteFormValidation {

	formId: string;
	options: Object;

	constructor(formId, options) {
        this.formId = formId;
        this.options = options;
    }

    public validateForm(){
        FgFormValidation.init(this.formId,'','');
        var formValid = $('#'+this.formId).valid();
        var formIdQ = this.formId;
        $('#' + formIdQ).find('.alert').addClass('hide');
        if (formValid) {
            $('#'+this.formId+' .has-error').removeClass('has-error');
            var formData = this.getFormData();
            if(!_.isEmpty(formData)){
                var formSubmitData = { 'inquiry': formData, 'menu': menu, 'mainPageId': mainPageId };
                var formUrl = $('#' + this.formId).attr('data-url');
                var rand = Math.random();
                var _this = this;
                $.post(formUrl + "?rand=" + rand, formSubmitData, function (result) {
                    $('.fg-external-contact-form-wrapper .fg-form-element-submit').removeAttr('disabled')
                    if(result['status']=='success'){
                            elementId = $('#' + formIdQ).parent().attr('id');
                            $("html, body").animate({scrollTop:$('#' + _this.formId).position().top-60}, '500');
                            if(typeof _this.options != 'undefined' && typeof _this.options['formType'] != 'undefined' && _this.options['formType'] === 'contact-form'){
                                $('#' + formIdQ)[0].reset();
                                $('#'+elementId+' .bs-select').selectpicker('refresh');
                                $('.bs-select .glyphicon').removeClass('glyphicon');
                                $.uniform.update();
                            } else { 
                                var webObj= new Fgwebsitepage();
                                var data = formDataArray[elementId];
                                webObj.handleFormElement(elementId,data);
                            }
                            setTimeout(function(){
                                $('#' + elementId).find('.alert-success').removeClass('hide');
                                $('#' + elementId).find('.alert-danger').addClass('hide');
                            }, 100);
                    } else {
                            if(!_.isUndefined(result['URL']) && !_.isUndefined(result['URL']['matches'])){
                                virmsg = $('#' + formIdQ).find('.alert-danger span[data-error]').data('virmsg');
                                $('#' + formIdQ).find('.alert-danger span[data-error]').html(virmsg);
                            } else {
                                if(result['emailExists'] == '1'){
                                   errmsg = result['msg'];
                                } else {
                                 errmsg = $('#' + formIdQ).find('.alert-danger span[data-error]').data('error');
                                } 
                                $('#' + formIdQ).find('.alert-danger span[data-error]').html(errmsg);
                            }    
                            $('#' + formIdQ).find('.alert-danger').removeClass('hide');
                            $('#' + formIdQ).find('.alert-success').addClass('hide');
                    }
                });
            }
        } else {
            $('.fg-external-contact-form-wrapper .fg-form-element-submit').removeAttr('disabled')
        }

    }

    public getFormData(){
        $('#'+this.formId+' :input').addClass('fairgatedirty');
        var objectGraph = {};
        _this= this;
        $('#'+this.formId+' :input').each(function() {
            var attr = $(this).attr('data-key');
            if ($(this).hasClass("fairgatedirty") && typeof attr !== typeof undefined && attr !== false) {
                var inputVal = '';
                var inputType = $(this).attr('type');
                if (inputType == 'radio' || inputType == 'checkbox') {
                    if ($(this).is(':checked')) {
                        inputVal = $(this).val();
                    }
                } else {
                    inputVal = $(this).val();
                }
                if (inputVal !== '' && inputVal !=null) {
                    _this.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
            }
        });
        

        return objectGraph;
    }
    public converttojson(objectGraph, name, value){
        if (name.length == 1) {
            //if the array is now one element long, we're done
            objectGraph[name[0]] = value;
        } else {
            //else we've still got more than a single element of depth
            if (objectGraph[name[0]] == null) {
                //create the node if it doesn't yet exist
                objectGraph[name[0]] = {};
            }
            //recurse, chopping off the first array element
            this.converttojson(objectGraph[name[0]], name.slice(1), value);
        }
    }

}