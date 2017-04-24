var fgDocumentUploader = { 
    settings : {},
    init: function(){
        fgDocumentUploader.initFgDirtyFields();
    },
    triggerUpload: function(){
        $('#file-uploader').trigger('click');
    },
    initElements: function (uploadedObj,data){
        var rowId = data.fileid;
        if(rowId)
        {
             //Select the current team/workgroup
            var selectedEntity = $.parseJSON(localStorage.getItem(tabStorageName)).id;
            if(selectedEntity != '' && selectedEntity != null){
                $('#'+rowId).find('select[name^="deposit"]').val(selectedEntity);
            }
            
            //Select the current category
            var selectedCategory = parseInt(FgSidebar.activeMenuData.id);
            if(!isNaN(selectedCategory) && selectedCategory > 0){
                $('#'+rowId).find('select[name^="doccategory"]').val(selectedCategory);
                $('#'+rowId).find('select[name^="doccategory"]').parents('dl').addClass('hide');
            }
            
            $('#'+rowId).find('.bs-select').selectpicker({
                    noneSelectedText: jstranslations.noneSelectedText,
                    countSelectedText: jstranslations.countSelectedText,
                });          
            $('#'+rowId).find('input[type=radio]:not(.make-switch)').uniform();  
            
            $('#'+rowId).find('input.fg-visibility-radio').click(function(){
                if($(this).val() == 'team_functions'){
                    $('#'+rowId).find('.docvisibility_2_for_container').show();
                }
                else {
                    $('#'+rowId).find('.docvisibility_2_for_container').hide();
                    $('#'+rowId).find('.docvisibility_2_for').val('');
                }
            });
            
            $('#'+rowId).find('div.deletediv ').click(function(){
                $(this).parents('.filecontent').remove();
                fgDocumentUploader.handleActionButtonContainer();
            });
            
            fgDocumentUploader.handleActionButtonContainer();
        }
    },
    initUploader: function(settings){
        FgFileUpload.init($('#file-uploader'), settings);
    },
    setFileIcon: function(uploadObj, data){
        var rowId = data.fileid;
        if(rowId)
        {
           var ext = data.files[0].name.split('.').pop().toLowerCase();
           var filetypes = fgDocumentUploader.getFileTypeArray();
           
           if(filetypes.imageTypes.indexOf(ext) > -1){
                 var icon = "<img src='"+tempUrl+data.formData.title+"'/>";
            } else {
                var icon = fgDocumentUploader.getFileTypeIcon(ext);
            }
            $('#'+rowId).find('.fg-upload-div').html(icon);
        }
    },
    initFgDirtyFields: function(){
        FgDirtyFields.init('document_uploader_form', { 
                                        saveChangeSelector: '#document_upload_save',
                                        discardChangeSelector: '#document_upload_discard',
                                        exclusionClass: 'dirtyExclude',
                                        initCompleteCallback : function () {
                                            
                                        },
                                        discardChangesCallback :function(){
                                             fgDocumentUploader.initUploader(documentUploaderOptions);
                                             fgDocumentUploader.handleSaveButton();
                                        }
                                    });
    },
    removeFgDirtyInstance: function(){
        try {
            FgDirtyFields.removeAllDirtyInstances();
        } catch(err) {
            console.log('Error');
        }
    },
    handleSaveButton: function(){
         $( "#document_upload_save" ).click(function() {
            //Validate the step1 form
            clickedButton = $(this);
            
            //if disabled class set return
            if(clickedButton.hasClass('disabled') || clickedButton.attr('disabled') ==  'disabled')
                return;
            
            clickedButton.addClass('disabled');
            
            var error = fgDocumentUploader.validateUpload();
            if(!error) {
                var paramObj = {};
                paramObj.form = $('#document_uploader_form');
                paramObj.url = documentUploadSaveurl;
                paramObj.successCallback = function(){
                                                        clickedButton.removeClass('disabled');
                                                        fgDocumentUploader.removeFgDirtyInstance();
                                                        fgDocumentUploader.redrawAfterSave();
                                                        FgDocumentCount.initRoleDocumentsCount();
                                                    };
                FgXmlHttp.formPost(paramObj);
            } else {
               clickedButton.removeClass('disabled');
                
            }
          });
    },
    
    validateUpload: function() {
        var error = false;
        $('.filecontent').each(function(){
            var content = $(this);
            var filename = content.find('input[name="docname[]"]');
            if(filename.val() == ''){
                setDocumentFormErrors(content.find('.fg-dev-docname'),filename,formerrormessage, true); 
                error = true;
            } else {
                setDocumentFormErrors(content.find('.fg-dev-docname'),filename,'', false); 
            }
            
            var deposited = content.find('select[name^="deposit"]');
            var depositederror = content.find('.fg-dev-deposited>div.bs-select');
            if(deposited.val() == null){
                setDocumentFormErrors(content.find('.fg-dev-deposited'),depositederror,formerrormessage, true);
                error = true;
            } else {
                setDocumentFormErrors(content.find('.fg-dev-deposited'),depositederror,'', false); 
            }
            
            var visibility = content.find('input[name^="docvisibility"]:checked');
            if(visibility.val() == 'team_functions'){
                var visibilityfunction = content.find('select[name^="docvisibility_2_for"]');
                var visibilityfunctionerror = content.find('.fg-dev-docvisibilityfor>div.bs-select');
                 if(visibilityfunction.val() == null){
                    setDocumentFormErrors(content.find('.fg-dev-docvisibilityfor'),visibilityfunctionerror,formerrormessage, true); 
                    error = true;
                } else {
                    setDocumentFormErrors(content.find('.fg-dev-docvisibilityfor'),visibilityfunctionerror,'', false); 
                }
            } 
            
            
        });
        
        return error;
    },
    getFileTypeIcon: function(ext){
       var icon = '';
       var filetypes = fgDocumentUploader.getFileTypeArray();
       if(filetypes.docTypes.indexOf(ext) > -1){
            icon = "<i class='fa fg-file-word'></i>";
        } else if(filetypes.pdfTypes.indexOf(ext) > -1){
            icon = "<i class='fa fg-file-pdf'></i>";
        } else if(filetypes.textTypes.indexOf(ext) > -1){
            icon = "<i class='fa fg-file-text'></i>";
        } else if(filetypes.excelTypes.indexOf(ext) > -1){
            icon = "<i class='fa fg-file-excel'></i>";
        } else if(filetypes.powerType.indexOf(ext) > -1){
            icon = "<i class='fa fg-file-powerpoint'></i>";
        } else if(filetypes.archiveType.indexOf(ext) > -1){
            icon = "<i class='fa fg-file-zip'></i>";
        } else if(filetypes.audioType.indexOf(ext) > -1){
            icon = "<i class='fa fg-file-sound'></i>";
        } else if(filetypes.videoType.indexOf(ext) > -1){
            icon = "<i class='fa fg-file-video'></i>";
        } else if(filetypes.webTypes.indexOf(ext) > -1){
            icon = "<i class='fa fg-file-code'></i>";
        } else {
            icon = "<i class='fa fg-file'></i>";
        }
        
        return icon;
    },
    getFileTypeArray: function(){
        var fileTypes={};
        fileTypes.docTypes = ['doc', 'docx','odt'];
        fileTypes.pdfTypes = ['pdf'];
        fileTypes.excelTypes = ['xls','xlsx'];
        fileTypes.powerType = ['ppt','pptx'];
        fileTypes.archiveType = ['zip','rar','tar','gz','7z'];
        fileTypes.audioType = ['mp3','aac','amr','m4a','m4p','wma'];
        fileTypes.videoType = ['mp4','flv','mkv','avi','webm','vob','mov','wmv','m4v'];
        fileTypes.webTypes = ['html','htm'];
        fileTypes.textTypes = ['txt','rtf','log'];
        fileTypes.imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'bmp'];
        
        return fileTypes;
    },
    removeFileContents: function(){
        $('.filecontent').remove();
        $('#document-upload-error-container').html('');
        fgDocumentUploader.handleActionButtonContainer();
    },
    handleActionButtonContainer: function(){
        //If any rows exists show else hide
        if($('.filecontent').length > 0){
            $('#action-button-container').removeClass('hide');
        } else {
            $('#action-button-container').addClass('hide');
            fgDocumentUploader.removeFgDirtyInstance();
        }
    },
    redrawAfterSave: function(){
        fgDocumentUploader.removeFileContents();
        
        //redraw the datatable
        //listTable.ajax.reload();
        Fgtabselectionprocess.listDocument();
        
        //Recount sidebar 
        FgTeamDocuments.setSidebarCount(FgTeamDocuments.sidebarMenuDatas);
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
}    


function setDocumentFormErrors(container, errorLocation, errorMessage, hasError)
{ 
    container.removeClass('has-error');
    errorLocation.parent().find('.fg-dev-errorblock').remove();
    if(hasError)
    { 
        var errorHtml = '<span class="help-block fg-dev-errorblock">'+errorMessage+'</span>'; 
        errorLocation.after(errorHtml);
        container.addClass('has-error');
    }
   
}