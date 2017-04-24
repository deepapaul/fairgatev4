var fgMessageWizardStep1 = { 
    init: function(){
        fgMessageWizardStep1.connectRecipientAutocomplete(getSelectedRecipients());
        FgFormTools.handleBootstrapSelect();
        fgMessageWizardStep1.connectAddAllRecipients();
        fgMessageWizardStep1.handleSaveStep1();
        
    },
    initFgDirtyFields: function(){
        $.uniform.restore($("input[name='conversationtype']"));
        FgDirtyFields.init('submit_form', { 
                                        saveChangeSelector: '#message_wizard_save_',
                                        discardChangeSelector: '#back_',
                                        exclusionClass: 'dirtyExclude',
                                        initCompleteCallback : function () {
                                            $("input[name='conversationtype']").uniform();
                                        },
                                        discardChangesCallback :function(){
                                            $('.bootstrap-select.form-control.bs-select').remove()
                                            FgFormTools.handleBootstrapSelect();
                                            fgMessageWizardStep1.init();   
                                        }
                                    });
    },
    //initialization the autocomplete for recipients
    connectRecipientAutocomplete:function(selectedRecipients){
        
       var autocompleteOptions = {};
       autocompleteOptions.minLength = 1;
       autocompleteOptions.formName = 'message_recipients';
       autocompleteOptions.sendTitles = false;
       autocompleteOptions.removeButtonTitle = removeButtonTransalator+' %s';
       
       //Both function to handle the FGDirty fields
       autocompleteOptions.onItemSelected = function($obj, itemId, selection) { 
                                                    $('#recipients-suggest-updator').attr('value', $( "input[name='message_recipients[]']" ).map(function() {return this.value;}).get().join()).change();
                                                };
       autocompleteOptions.onItemRemoved = function($obj, itemId) { 
                                                    $('#recipients-suggest-updator').attr('value', $( "input[name='message_recipients[]']" ).map(function() {return this.value;}).get().join()).change();
                                                };
       //Code to set the selected elements
       if(typeof selectedRecipients == 'object' && selectedRecipients.length > 0){
        //Safe to remove all selected options created now  
          $( ".fbautocomplete-main-div span" ).remove();
         autocompleteOptions.selected = selectedRecipients;
       }
       
       if (message_recipient_type === "CONTACT"){
           autocompleteOptions.url = message_recipient_contact_url;
       }
       else {
            autocompleteOptions.staticRetrieve = function(term){
                    var recipients = getRecipients();
                    var recipientList = [];
                    jQuery.each(recipients, function( i,str ) {
                        if(str['title'].toLowerCase().match("^"+term.toLowerCase())){
                            recipientList.push({id:str.id,title:str.title});
                        }
                    });
                    return recipientList;
                };
       }
                  
       $('#recipients').fbautocomplete(autocompleteOptions);
       $('#recipients-suggest-updator').attr('value', $( "input[name='message_recipients[]']" ).map(function() {return this.value;}).get().join()).change();
    },
    
    connectAddAllRecipients: function (){
        $( "#add-all-contact" ).click(function() {
            //Remove the current instance and add again
            $( ".fbautocomplete-main-div span" ).remove();
            fgMessageWizardStep1.connectRecipientAutocomplete(getRecipients());
          });
    },
    handleSaveStep1: function (){       
        $( "#message_wizard_save" ).click(function() {
            //Validate the step1 form
            clickedButton = $(this);
            
            //if disabled class set return
            if(clickedButton.hasClass('disabled') || clickedButton.attr('disabled') ==  'disabled')
                return;
            
            clickedButton.addClass('disabled');
            
            //Validation
            var error = false;
            var recipientList = $( "input[name='message_recipients[]']" );
            if(recipientList.length == 0) {
                setFormErrors($('.fg-dev-recipients'),  $('.fg-dev-recipients').find('.fbautocomplete-main-div'), '', false);//remove already rendered error
                setFormErrors($('.fg-dev-recipients'), $('.fg-dev-recipients').find('.fbautocomplete-main-div'),requiredValidationTransalator, true)
                error = true;
            } else {
                setFormErrors($('.fg-dev-recipients'),  $('.fg-dev-recipients').find('.fbautocomplete-main-div'), '', false);
            }
            var conversationType = $( "input[name='conversationtype']:checked" ).val();
            if(conversationType == '' || conversationType == 'undefined' || conversationType == undefined) {
                setFormErrors($('.fg-dev-conversation'), $('.fg-dev-conversation').find('.radio-list'), '', false);
                setFormErrors($('.fg-dev-conversation'), $('.fg-dev-conversation').find('.radio-list'),requiredValidationTransalator, true)
                error = true;
            } else {
                setFormErrors($('.fg-dev-conversation'), $('.fg-dev-conversation').find('.radio-list'), '', false);
            }
            
            if(!error) {
                $('.alert-danger').addClass('display-none');
                var paramObj = {};
                paramObj.form = $('#submit_form');
                paramObj.url = messageStep1Saveurl;
                FgXmlHttp.formPost(paramObj);   
                FgDirtyFields.removeAllDirtyInstances();
            } else {
               clickedButton.removeClass('disabled');
               $('.alert-danger').removeClass('display-none'); 
            }
          });
    }
}    

var fgMessageWizardStep2 = {
    fileLimit : 1048576 * 15, //15 MB
   init: function(){
        fgMessageWizardStep2.initMessageEditor();
        fgMessageWizardStep2.handleAttachementUploader($('#attachments'), 'message_wizard_save');
        fgMessageWizardStep2.initMessageEditorToggler();
        fgMessageWizardStep2.handleSaveStep2();
        fgMessageWizardStep2.handleRemoveFile();
        fgMessageWizardStep2.handleBackButton();
   },
   initFgDirtyFields: function(){
        FgDirtyFields.init('submit_form', { 
                                        saveChangeSelector: '#message_wizard_save_',
                                        discardChangeSelector: '#back_',
                                        exclusionClass: 'dirtyExclude',
                                        initCompleteCallback : function () {
                                            
                                        },
                                        discardChangesCallback :function(){
                                            fgMessageWizardStep2.init();   
                                        }
                                    });
    },
   initMessageEditor: function(settings){
        var configArray = {};
        if(settings == 'advanced')
            configArray.toolbar = ckEditorConfig.mailAdvanced;
        else
            configArray.toolbar = ckEditorConfig.mailSimple;
        configArray.language = jstranslations.localeName;
        configArray.disallowedContent = 'script; *[on*]';        
      
        if(CKEDITOR.instances['message']) {
            try {
                //It will cause error when the CKEditor html is replaced manually by FGDirty field, but the distance is not removed
                CKEDITOR.instances['message'].destroy();
            } catch (error){}
            delete CKEDITOR.instances['message'];
        }
        CKEDITOR.config.dialog_noConfirmCancel = true;    
       
       
        var editorObj = CKEDITOR.replace( 'message',configArray); 
        
        editorObj.on( 'change', function() {
            $('#message').attr('value', editorObj.getData()).change();
        } );
   },
   handleBackButton:function(){
        $('body').on('click','#message_wizard_discard',function(){
            window.location = messageBackTo;
        });
    },
   initMessageEditorToggler: function(){
        $( ".fg-advanced-editor" ).click(function() {
            $( ".fg-advanced-editor" ).hide();
            $( ".fg-simple-editor" ).show();
            fgMessageWizardStep2.initMessageEditor('advanced');
        });
        $( ".fg-simple-editor" ).click(function() {     
            $( ".fg-advanced-editor" ).show();
            $( ".fg-simple-editor" ).hide();
            fgMessageWizardStep2.initMessageEditor('simple');
        });
   },
   handleAttachementUploader: function(elementObj, saveButtonId){
        var url = elementObj.attr('upload-url');
        
        var settings = {
            fileListTemplate: 'imageUploadContent',
            fileListTemplatePlacement: 'append',
            fileListTemplateContainer: '#file-uploaded-attachements',
            progressBarContainer: '.progress',
            progressBarElement: '.progress-bar-success',
            onFileUploadError:'fgMessageWizardStep2.setErrorMessage',
            uploadUrl: url,
            validationErrorTemplateId:'fileUploadError',
            validations: {
                fileType: 'image,doc,pdf,excel,power,archive,audio,video,web,text',
                forbiddenFiletypes: '{{ forbiddenFiletypes }}', 
            },
            saveButtonDisableOnUploading: true,
            saveButtonId: saveButtonId
        };
        
        uploaderObj = FgFileUpload.init(elementObj, settings);
    },
    
    setErrorMessage: function(uploadObj, data) {
        var template = $('#fileUploadError').html();
        var result = _.template(template, {error : data.result.error });
        $('#'+data.fileid).find('.fg-replacewith-errormsg').html(result);
        $('#'+data.fileid).addClass('has-error');
        $('#'+data.fileid+" input:hidden").remove();
    },
       
    handleSaveStep2: function(){
     $( "#message_wizard_save" ).click(function() { 
         
         clickedButton = $(this);
            
        //if disabled class set return
        if(clickedButton.hasClass('disabled') || clickedButton.attr('disabled') ==  'disabled')
                return;

        clickedButton.addClass('disabled');
            
        var subject = $('#subject').val();
        var message = CKEDITOR.instances.message.getData();
        var error = false;
        if(subject == '' || subject == 'undefined' || subject == undefined) {
            setFormErrors($('.fg-dev-subject [dataerror-group]'), $('.fg-dev-subject #subject'), '', false);
            setFormErrors($('.fg-dev-subject [dataerror-group]'), $('.fg-dev-subject #subject'),requiredValidationTransalator, true);
            error = true;
        } else {
            setFormErrors($('.fg-dev-subject [dataerror-group]'), $('.fg-dev-subject #subject'), '', false);
        }
        if(message == '' || message == 'undefined' || message == undefined) {
            setFormErrors($('.fg-dev-message [dataerror-group]'), $('.fg-dev-message #cke_message'), '', false);
            setFormErrors($('.fg-dev-message [dataerror-group]'), $('.fg-dev-message #cke_message'),requiredValidationTransalator, true);
            error = true;
        } else {
            setFormErrors($('.fg-dev-message [dataerror-group]'), $('.fg-dev-message #cke_message'), '', false);
        }
        //check any file upload error
        if( $('.fg-replacewith-errormsg').find('.help-block').length > 0 ) {
            error = true;
        }
        
        if(!error) {
            //need to get the CK editor value and put it to the message element for proper using of FgXmlHttp.formPost()
            $('#message').val(CKEDITOR.instances.message.getData());
            
            $('.alert-danger').addClass('display-none');
            var paramObj = {};
            paramObj.form = $('#submit_form');
            paramObj.url = message_step2_save;
            FgXmlHttp.formPost(paramObj);   
            FgDirtyFields.removeAllDirtyInstances();
        } else {
           clickedButton.removeClass('disabled');
           $('.alert-danger').removeClass('display-none'); 
        }
         
     });
    },
    formatFileSize: function(bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }

        if (bytes >= 1073741824) {
            return FgClubSettings.formatNumber((bytes / 1073741824).toFixed(2)) + ' GB';
        }

        if (bytes >= 1048576) {
            return FgClubSettings.formatNumber((bytes / 1048576).toFixed(2)) + ' MB';
        }

        return FgClubSettings.formatNumber((bytes / 1024).toFixed(2)) + ' KB';
    },
    handleRemoveFile: function (){
        $( "#file-uploaded-attachements" ).on( "click", ".removeUpload", function() {
             $( this ).parent().slideUp().remove();             
             //Need to handle the FGDirty Field
             $('#attachment-updator').attr('value', $( "input[name='uploaded_attachments[]']" ).map(function() {return this.value;}).get().join()).change();
          });
    }
}    


function setFormErrors(container, errorLocation, errorMessage, hasError)
{ 
    if(hasError)
    { 
        var errorHtml = '<span class="help-block fg-dev-errorblock">'+errorMessage+'</span>'; 
        errorLocation.after(errorHtml);
        container.addClass('has-error');
    }
    else
    {
        container.removeClass('has-error');
        errorLocation.parent().find('.fg-dev-errorblock').remove();
    }
}