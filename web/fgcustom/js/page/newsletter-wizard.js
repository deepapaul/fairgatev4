var FormWizard = function () {


    return {
        //main function to initiate the module
        init: function () {
           var handleTitle = function() {
                var total =  $('#form_wizard_1 ul.steps').find('li').length;
                var current = $('#form_wizard_1 ul.steps li.active').index() + 1;
                
                if (current === 1) {
                    $('#form_wizard_1').find('.button-previous').hide();
                } else {
                    $('#form_wizard_1').find('.button-previous').show();
                }

                if (current >= total) {
                    $('#form_wizard_1').find('.button-next').hide();
                     $('#form_wizard_1').find('.button-save').hide();
                    $('#form_wizard_1').find('.button-submit').show();
                } else {
                    $('#form_wizard_1').find('.button-next').show();
                    $('#form_wizard_1').find('.button-submit').hide();
                }
                Metronic.scrollTo($('.page-title'));
            }();

            $('#form_wizard_1 .button-submit').click(function () {
                 handleSave(false);
            });
            $('#form_wizard_1 .button-save').click(function () {
                handleSave(false);
            });
            $('#form_wizard_1 .button-next').click(function () {
                handleSave(true);
            });
            handleSave = function(showNext){
                var index = $('#form_wizard_1 ul.steps li.active').index() + 1;
                var form = $('body #form-tab'+index);
                var hasError=0;
                $('div[data-target].fg-error-warning').removeClass('fg-error-warning');
                $('div.has-error').removeClass('has-error');                
                if (form.attr('data-validation')==='true') {
                    FormValidation.init('form-tab'+index);
                    if(form.valid()===false ) {
                        hasError=1;
                    } 
                    $('.alert-danger').hide();
                }
                if(index===3) {
                    _dynamicFunction.editor.setCkeditorData();
                }
                if(form.attr('data-post-type')==='json'){
                    //parse the all form field value as json array and assign that value to the array
                    var objectGraph = {};
                    $("form :input").each(function() {
                        var inputVal = '';
                        var inputType = $(this).attr('type');
                        if (inputType == 'checkbox') {
                            inputVal = $(this).attr('checked') ? 1 : 0;
                        } else if (inputType == 'radio') {
                            if ($(this).is(':checked')) {
                                inputVal = $(this).val();
                            }
                        } else {
                            inputVal = $(this).val();
                        }
                        if($(this).is("textarea") && $(this).hasClass('ckeditor')){
                            inputVal =$(this).html();
                            if($(this).attr('data-required')==='1') {
                                if($(this).text()===''){
                                    $('span[for='+$(this).attr('name')+']').show();
                                    $(this).parents('.form-group').addClass('has-error');
                                    hasError=1;
                                } else {
                                    $('span[for='+$(this).attr('name')+']').hide();
                                    $(this).parents('.has-error').removeClass('has-error');
                                }
                            }
                        }
                        var attr = $(this).attr('data-key');

                        if (typeof attr !== typeof undefined && attr !== false) {
                            if (inputVal !== '' || $(this).is("textarea") || $(this).is("select")) {
                                converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                            } else if (inputType == 'hidden') {
                                converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                            }
                        }

                    });
                    if($('li.has-error').length > 0){
                        hasError = 1;
                    }
                    if(hasError===1) {
                        $('.has-error').each(function(){
                            var errorId=$(this).parents('.collapse ').attr('id');
                            $('div[data-target=#'+errorId+']').addClass('fg-error-warning');
                        });

                        $('form .alert-danger').show();
                        return false;
                    }
                    $('form .alert-danger').hide();
                    var catArr = JSON.stringify(objectGraph);
                    FgXmlHttp.post(form.attr('data-url'), {'catArr': catArr,'newsletterId': newsletterId,'showNext': showNext} , false, handleCallback);
                } else {
                    if(hasError===1) {
                         return false;
                    }else{
                        if(index == 1){          
                             
                              var confirmation =  handleConfirmation();
                        } else {
                          var confirmation = true;
                        }
                    }
                    if(confirmation == true){
                        var replaceDiv = 'body #tab' +(index+1);
                        var paramobj = {'url': form.attr('data-url'), 'extradata': {'newsletterId': newsletterId,'showNext': showNext}, 'form': form, 'replacediv': replaceDiv, 'successCallback': handleCallback, 'successParam': {'index': index, 'showNext': showNext}};
                        var noError = true;
                        if (index == 2) {
                            if ($("#newsletterType").val() == 'MANDATORY') {
                                if (($('#selected-email-fields').val() == '' || $('#selected-email-fields').val() == null) && ($(".fbautocomplete-main-div").find('.ids-fbautocomplete').length > 0)) {
                                    $("#mandatoryEmailError").show();
                                    noError = false;
                                }  
                            }
                        }
                        if (noError) {
                            FgXmlHttp.formPost(paramobj);
                        }
                    }
                }
            }
            handlethirdStepConfirmation = function(){
              
                  $('#save_nd_continue').confirmation('destroy');
                  var element = $('#save_nd_continue');
                  element.attr('data-toggle',"confirmation");
                  FgConfirmation.confirm("Change of Template",change, continuee, element, confirmTrue);  
                  $('#save_nd_continue').confirmation('show');
                  return confirm;
                   
            },
            handleConfirmation = function(){
                $('#save_nd_continue').confirmation('destroy');
                var sender = $('input[name="email"]').val(); 
                var senderDomain = sender.split('@');
                var domains =["gmail.com", "hotmail.com","hotmail.ch", "hotmail.de", "hotmail.fr", "hotmail.at", "hotmail.it", "live.com", "me.com", "bluewin.ch", "bluemail.ch", "web.de", "swissonline.ch", "hispeed.ch"];
                var domainsAnyTld =["gmx","yahoo" ];                
                var result =  _.contains(domains,senderDomain[1]);
                var senderDomainName = senderDomain[1].split('.');
                var resultAnyTld =  _.contains(domainsAnyTld,senderDomainName[0]);               
                var newTemplateId = $('select[name="templateId"]').val();
                if(result || resultAnyTld){
                    var element = $('#save_nd_continue');
                    element.attr('data-toggle',"confirmation");
                    var confirmNote1 = confirmNote.replace('%senderemail%',sender);
                    if ((oldTemplateId != 0) && (oldTemplateId != newTemplateId)) {
                        confirmNote1 = "<br/>" + confirmNote1;
                        var  confirmNote2 = templateConfirmNote + "<br/>";
                        confirmNote1 =  confirmNote2 + confirmNote1 ;
                    }                   
                    FgConfirmation.confirm(confirmNote1,change,continuee,element, confirmTrue, false, 'click');  
                    $('#save_nd_continue').confirmation('show');
                } else if ((oldTemplateId != 0) && (oldTemplateId != newTemplateId)){
                      $('#save_nd_continue').confirmation('destroy');
                      var element = $('#save_nd_continue');
                      element.attr('data-toggle',"confirmation");
                      FgConfirmation.confirm(templateConfirmNote,change,continueButtonText,element, confirmTrue, false, 'click');  
                      $('#save_nd_continue').confirmation('show');
                }
                else{
                   confirm = true;   
                }
                    return confirm;
            },
            handleProgressBar= function () {
                    var total =  $('#form_wizard_1 ul.steps').find('li').length;
                    var current = $('#form_wizard_1 ul.steps li.active').index() + 1;
                    var $percent = (current / total) * 100;
                    $('#form_wizard_1').find('.progress-bar').css({
                        width: $percent + '%'
                    });
            }();
            handleCallback=function(responce){
                if(responce.index==1) {
                    newsletterId=responce.responseText.newsletterId;
                }
//                if (responce.showNext) {
//                    $('#form_wizard_1').bootstrapWizard('next');
//                }
            }
        }
        

    };

}();

function confirmTrue() {
    confirm = true;
}
