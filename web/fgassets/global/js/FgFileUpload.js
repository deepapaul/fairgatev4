/*
    In-Built Callbacks
    -------------------
    fileuploadadd
    fileuploadsubmit
    fileuploadsend
    fileuploaddone
    fileuploadfail
    fileuploadalways
    fileuploadprogress
    fileuploadprogressall
    fileuploadstart
    fileuploadstop
    fileuploadchange
    fileuploadpaste
    fileuploaddrop
    fileuploaddragover
    fileuploadchunksend
    fileuploadchunkdone
    fileuploadchunkfail
    fileuploadchunkalways
    fileuploadprocessstart
    fileuploadprocess
    fileuploadprocessdone
    fileuploadprocessfail
    fileuploadprocessalways
    fileuploadprocessstop
    fileuploaddestroy
    fileuploaddestroyed
    fileuploadadded
    fileuploadsent
    fileuploadcompleted
    fileuploadfailed
    fileuploadfinished
    fileuploadstarted
    fileuploadstopped

    Usage
    callbacks:{
                fileuploadadd: 'function1',
                fileuploadsubmit: 'function2',
                fileuploaddone : 'function'
            }

    Custom Backs ; supports comma seperated list of functions
    ------------------
    onFileUploadSuccess
    onFileUploadError
    onRemoveFileEvent

    Validations
    -----------
    validation:{
        fileType:'image',
        'customObj.customFunction': whatever
    }

*/
FgFileUpload = {
    _files: [],
    uploader: '',
    
    init: function(elementObj, options){

        if(elementObj.attr('upload-attr') != 'undefined')
            elementObj.attr('upload-attr','true');
        else
            return;

        var $this = this;
        //merge the options
         _settings = {};
        jQuery.extend(_settings,this.getOptions(options));


        //First wrap the file element with the specified template
        var wrapperContainer = options.wrapperContainer;
        elementObj.wrap($(wrapperContainer).html());

        //get the id of the file uploder element ans save it in settngd
         _settings.elemid = elementObj.attr('id');

        //Get the upload url from the element and assign it
        if(elementObj.attr('upload-url') != undefined)
            _settings.url = elementObj.attr('upload-url');
        else
            _settings.url = options.uploadUrl;

        //Handle the drop zone placeholder
        if(options.dropZoneElement != '') {          
           _settings.dropZone = $(options.dropZoneElement);
           $this.setHoverEffect(_settings);
        }

        //Connect the element that needed to be clicked to open the file browser, if specified
        if(_settings.fileSelectClickerElement != '')
        {
            $(_settings.fileSelectClickerElement).on('click', function(){
                elementObj.click();
           });
        }


        var uploadActions = {
                    uploadActionSettings : {},
                    add: function (e, data) {  
                        uploadSettings = uploadActions.uploadActionSettings;
                        
                        // make save button disabled until uploads finished
                        if(uploadSettings.saveButtonDisableOnUploading) {
                            $('#'+uploadSettings.saveButtonId).addClass('disabled');
                        }
                        var timestamp = $.now();
                        var random1 = Math.random().toString(36).slice(2);
                        var random2 = Math.random().toString(36).slice(2);
                        var thisId = random1+'-'+timestamp+'-'+random2;
                        data.fileid = thisId;
                        var uniqueFileName = thisId + '.' + _.last((data.files[0].name).split('.')) ;

                        var template = $('#'+uploadSettings.fileListTemplate).html(); 
                        var datatoTemplate = {
                                                name: data.files[0].name, 
                                                id : thisId, 
                                                value: uniqueFileName, 
                                                size_raw: data.files[0].size, 
                                                size:$this.formatFileSize(data.files[0].size),
                                                filedetails: data
                                            };
                        jQuery.extend(datatoTemplate,uploadSettings.extraDataToTemplate);  
                        
                        if(uploadSettings.fileuploadadd){
                            $this.call_callback(uploadSettings.fileuploadadd, $this, data, uploadSettings);
                        }

                        var result = _.template(template, datatoTemplate);
                        if(uploadSettings.fileListTemplatePlacement == 'append') {
                            $(uploadSettings.fileListTemplateContainer).append(result);
                        } else if(uploadSettings.fileListTemplatePlacement == 'edit') {
                            $(uploadSettings.fileListTemplateContainer).html(result);
                        } else {
                            $(uploadSettings.fileListTemplateContainer).prepend(result);     
                        }

                        $this.fileRemoveHandler(data, $this);

                        //client side validation performed 
                        if($this.validate(data, $this, uploadSettings)){
                            //form submitted for server side validation
                            data.formData = {title: uniqueFileName, fileName: data.files[0].name};
                            data.submit();
                            $this._files.push(data.files[0]);
                        }
                        if(uploadSettings.onFileListAdd){
                         $this.call_callback(uploadSettings.onFileListAdd, $this, data, uploadSettings, $(result));
                        }
                    },
                    progress: function(e, data){
                        uploadSettings = uploadActions.uploadActionSettings;
                        var uploadId = data.fileid;
                        $('#'+uploadId).find(uploadSettings.progressBarContainer).show();
                        $('#'+uploadId).find(uploadSettings.progressBarContainer)
                                        .find(uploadSettings.progressBarElement)
                                        .width(parseInt(data.loaded / data.total * 100, 10)+'%');

                     },
                     stop:function(e, data){
                         uploadSettings = uploadActions.uploadActionSettings;
                         // if all files are uploaded.
                        if(uploadSettings.progressAllCallBack){
                            uploadSettings.progressAllCallBack.call();
                        }
                        // make save button disabled until uploads finished
                        if(uploadSettings.saveButtonDisableOnUploading) {
                            $('#'+uploadSettings.saveButtonId).removeClass('disabled');
                        }
                     },
                    done: function(e,data){
                        uploadSettings = uploadActions.uploadActionSettings;
                        //if error handle error
                        var uploadId = data.fileid;
                        uploadError = false;
                        if(data.result.status == 'success')
                        {
                            $('#'+uploadId).find(uploadSettings.progressBarContainer).remove();
                            //need to call the call back function if set
                             if(uploadSettings.onFileUploadSuccess){
                                var successCallbackFunction = uploadSettings.onFileUploadSuccess.split(',');
                                _.each(successCallbackFunction, function(functionName){
                                    $this.call_callback(functionName, $this, data, uploadSettings);
                                });
                             }
                        } 
                        else
                        {
                            $('#'+uploadId).find(uploadSettings.progressBarContainer).remove();
                            //need to call the call back function if set
                             if(uploadSettings.onFileUploadError)
                                $this.call_callback(uploadSettings.onFileUploadError, $this, data, uploadSettings);
                            
                        }
                        
                    },
                    fail: function(e,data){

                        //if error handle error
                         var uploadId = data.fileid;
                        $('#'+uploadId).remove();
                    }
        };
        uploadActions.uploadActionSettings = _settings;
        jQuery.extend(_settings, uploadActions);
        uploader = elementObj.fileupload(_settings);

        $this.bindInBuiltEvents($this, uploader, _settings);
        return uploader;
    },

    disableFileUpload: function()
    {
        $('#'+_settings.elemid).fileupload('disable');
    },
   
    enableFileUpload: function()
    {
        $('#'+_settings.elemid).fileupload('enable');
    },
    
    getOptions: function(options)
    {
        defaultOptions = {
            logger: false,
            maxChunkSize: 10000000 // 10 MB
        };
        return $.extend({}, defaultOptions, options);
    },
    /**
     * Method to validate uploaded data
     * @param {array} data uploadSettings
     * @param {object} $this this object
     * @param {object} uploadSettings settings
     * @returns {Boolean}
     */
    validate: function(data, $this, uploadSettings){
        var errorArray = [];
        var files = $this._files       
        if(_.size(uploadSettings.validations) > 0) {
            _.each(uploadSettings.validations, function(validationValue, validationType) { 

                if(typeof window['FgFileUploadValidator'][validationType] === 'function') {                    
                    validationResult = window['FgFileUploadValidator'][validationType](validationValue, data, uploadSettings, files);
                } else {
                    //It is a custom function
                    validationResult = $this.call_callback(validationType, $this, data, uploadSettings, files);
                    FgFileUpload.l('validationResult on custom '+validationResult);
                }   

                if(!validationResult)
                    errorArray.push(Object.keys(uploadSettings.validations).indexOf(validationType));//To get the index of validatioType in validation {}

                if(!validationResult)
                    FgFileUpload.l(validationType+' '+validationResult);
            });
        }

        if(_.size(errorArray) > 0)
        {
            errorArray = errorArray.slice(0, 1);
            $('#'+data.fileid).find(uploadSettings.progressBarContainer).remove();
            //need to call the call back function if set
            var template = $('#'+uploadSettings.validationErrorTemplateId).html();
            var error = _.template(template, {'error': errorArray,'name':data.files[0].name});
            $('#'+data.fileid).addClass('has-error');
            $('#'+data.fileid).find('.fg-replacewith-errormsg').html(error);
            $('#'+data.fileid+" input[type='hidden']").remove();
            return false;
        }
        else
        {
            return true;
        }
    },

    fileRemoveHandler: function(data, $this){
        if(_settings.removeElement ) {
            $(_settings.fileListTemplateContainer).on( "click", _settings.removeElement, function() {
                $( this ).parents('li').slideUp().remove();

                //need to call the call back function if set
                if(_settings.onRemoveFileEvent)
                   $this.call_callback(_settings.onRemoveFileEvent, $this, data, _settings);
             });
        }
    },

    bindInBuiltEvents: function($this, uploader, settings){
        if(_.size(settings.callbacks) > 0) {
            var callbacks = settings.callbacks;
            _.each(callbacks, function(functionName, event) { 
                uploader.bind(event, function(e, data){
                    $this.call_callback(functionName, e, data, event);
                });
            });
        }
    },

    setHoverEffect: function(settings){
        $(document).bind('dragover', function (e) {
                var dropZone = settings.dropZone,
                timeout = window.dropZoneTimeout;
                if (!timeout) {
                    dropZone.addClass(settings.dropHoverClass);
                } else {
                    clearTimeout(timeout);
                }
                var found = false,
                    node = e.target;
                do {
                    if (node === dropZone[0]) {
                        found = true;
                        break;
                    }
                    node = node.parentNode;
                } while (node != null);
                if (found) {
                    dropZone.addClass(settings.dropHoverClass);
                } else {
                    dropZone.removeClass(settings.dropHoverClass);
                }
                window.dropZoneTimeout = setTimeout(function () {
                    window.dropZoneTimeout = null;
                    dropZone.removeClass(settings.dropHoverClass);
                }, 100);
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

    //The function to calla function froma string with parameters
    call_callback: function (functionName, obj, data, settings)
    {
        FgFileUpload.l('FunctionName '+functionName+' called');
        context = window;
        var args = [].slice.call(arguments).splice(1);
        var namespaces = functionName.split(".");
        var func = namespaces.pop();

        for(var i = 0; i < namespaces.length; i++) {
            context = context[namespaces[i]];
        }

        if(context[func] != 'undefined' && context[func] != undefined )
            return context[func].apply(this, args);
        else
            FgFileUpload.l(functionName+' not exists');
    },
    
    l: function(message){
        if(_settings.logger === true)
            console.log(message);
    }
}

FgFileUploadValidator = {
    fileType: function(validationValue, data, settings, files){
        var validFileTypes = validationValue.split(',');
        var currentFileType = _.last((data.files[0].name).split('.'));
        var valid = false;
        _.each(validFileTypes, function(fileType, key){
            if(!valid) {
                if(_.indexOf(FgFileUploadValidator.getFileTypeArray(fileType), currentFileType.toLowerCase()) > -1) {
                    valid = true;
                }
            } 
        });
        return valid;
    },
    //checking wheteher the uploaded file is in forbidden file list, return false it it is
    forbiddenFiletypes: function(validationValue, data, settings, files){  
        var validFileTypes = validationValue.split(',');
        var currentFileType = _.last((data.files[0].name).split('.'));

        var valid = true;
        _.each(validFileTypes, function(fileType, key){
             if(valid) {
                if( fileType == currentFileType ) {
                    valid = false;
                }
            } 
        });
        return valid;
    },
    
    fileSizeLimit: function(validationValue, data, settings, files){
        var currentFileSize = data.files[0].size;
        var valid = true;
        if(parseInt(currentFileSize) > parseInt(validationValue))
            valid = false;

        return valid;
    },
    totalFileSizeLimit: function(validationValue, data, settings, files){
        var alreadyUploaded = 0;
        var valid = true;
        _.each(files, function(file){
            alreadyUploaded = alreadyUploaded + parseInt(file.size);
        });

        if(parseInt(alreadyUploaded) > parseInt(validationValue))
            valid = false;

        return valid;
    },
    fileCountLimit: function(validationValue, data, settings, files){
        var valid = true;
        var totalUploaded = files.length;
        if(parseInt(totalUploaded) >= parseInt(validationValue))
            valid = false;

        return valid;
    },
    getFileTypeArray:function(type){
        var fileTypes={};
        fileTypes.image = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'bmp'];
        fileTypes.doc = ['doc', 'docx','odt'];
        fileTypes.pdf = ['pdf'];
        fileTypes.excel = ['xls','xlsx','csv'];
        fileTypes.power = ['ppt','pptx'];
        fileTypes.archive = ['zip','rar','tar','gz','7z'];
        fileTypes.audio = ['mp3','aac','amr','m4a','m4p','wma'];
        fileTypes.video = ['mp4','flv','mkv','avi','webm','vob','mov','wmv','m4v'];
        fileTypes.web = ['html','htm'];
        fileTypes.text = ['txt','rtf','log'];
        return fileTypes[type]
    }
};

function afterUpload(){
    alert('got It');
}

function afterUpload2(){
    alert('got It 2');
}

FgFileUploadInstance = {
    remove: function(elementObj){
        elementObj.fileupload('destroy');
        FgFileUpload._files = [];
    }
};
