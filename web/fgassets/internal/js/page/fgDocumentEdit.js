var fgDocumentEdit={ 
    settings : {
        selectedLang : '',
        uploadUrl : '',
        dataSet : '',
        defaultLang : '',
        columnDefs : '',
        getVersionsUrl : '',
        updateDocumentUrl : '',
    },
    actionMenuTextDraft : {},
    initDocumentEdit: function() {
        fgDocumentUploader.settings = { 
                            wrapperContainer: '#uploader-container-template',
                            dropZoneElement: '#fg-wrapper',
                            fileListTemplate: 'editDocUpload',
                            fileListTemplateContainer: '#upload_container',
                            progressBarContainer: '.fg-upload-progress',
                            progressBarElement: '.progress-bar',
                            removeElement: '.removeUploadedFile',
                            errorContainer: '#document-upload-error-container',                                
                            errorListTemplate: 'editdocument-uploader-errorlist-template',
                            uploadUrl : fgDocumentEdit.settings.uploadUrl,
                            extraDataToTemplate : '',
                            onRemoveFileEvent: 'fgDocumentEdit.clearFileNameDirty',
                            callbacks : {
                                        fileuploadadd: 'fgDocumentEdit.updateFileNameDirty'
                                      },
                            validations : {
                                            fileType : 'image,doc,pdf,excel,power,archive,audio,video,web,text'
                                        }            
        }
        
        $.uniform.restore('form input[type=radio]:not(.make-switch)');
        FgDirtyFields.init('documentsettings', {
            dirtyFieldSettings :{}, 
            setInitialHtml : false,
            enableDragDrop : false, 
            enableUpdateSortOrder : false, 
            initCompleteCallback : function () {
                $('#documentsettings').find('.bs-select').selectpicker({
                        noneSelectedText: jstranslations.noneSelectedText,
                        countSelectedText: jstranslations.countSelectedText,
                });          
                $('form input[type=radio]:not(.make-switch)').uniform();
                FgUtility.handleSelectPicker();      
                FormValidation.init('documentsettings','');
                fgDocumentUploader.initUploader(fgDocumentUploader.settings);
                fgDocumentEdit.renderActionMenu();
                fgDocumentEdit.renderVersionDataTable();
            },
            discardChangesCallback : function () {
                var content = FGTemplate.bind('editDocumentTemplate', { 'dataSet' : fgDocumentEdit.settings.dataSet });
                $('#editTemplate').html(content);
                FgLanguageSwitch.checkMissingTranslation(fgDocumentEdit.settings.selectedLang);
                fgDocumentEdit.clearFileNameDirty();
                fgDocumentEdit.renderVersionDataTable();
//                fgDocumentEdit.initDocumentEdit();
            }
        });
        
    }, 
    renderActionMenu : function() {
        $( ".fg-action-menu-wrapper" ).FgPageTitlebar({
                title       : true,
                tab         : false,
                search      : false,
                actionMenu  : true
        });
        scope = angular.element($("#BaseController")).scope();
        scope.$apply(function () {
            scope.menuContent = fgDocumentEdit.actionMenuTextDraft;
        });
    },
    renderVersionDataTable : function() {
        if (!$.isEmptyObject(listTable)) {
            listTable.destroy();
        }
        var datatableOptions = {
            columnDefFlag: true,
            ajaxPath: fgDocumentEdit.settings.getVersionsUrl,
            columnDefValues: fgDocumentEdit.settings.columnDefs,
            fixedcolumn: false,
            initialSortingFlag: true,
            initialsortingColumn: 1,
            initialSortingorder: 'desc',
            serverSideprocess: false,
            opt: {
                dom: "<'col-md-12't>",
                autoWidth: true,
                responsive: true,
                processing: false,
                serverSide: false
            }
        };
        FgDatatable.listdataTableInit('document-version-list', datatableOptions);
    },
    clearFileNameDirty : function() {
        $('#uploadedFilename').attr('value', "0").trigger('change');
        fgDocumentEdit.clearCurrentDocuments();
    },
    updateFileNameDirty : function() {
        $('#uploadedFilename').attr('value', "1").trigger('change');
        fgDocumentEdit.clearCurrentDocuments();
    },
    clearCurrentDocuments : function() {
       $('#upload_container').html('');
       $('.fileinput').removeClass('fileinput-exists').addClass('fileinput-new');
    }
}

$(document).ready(function() {

        var content = FGTemplate.bind('editDocumentTemplate', { 'dataSet' : fgDocumentEdit.settings.dataSet });
        $('#editTemplate').html(content);
        FgLanguageSwitch.checkMissingTranslation(fgDocumentEdit.settings.selectedLang);
        fgDocumentEdit.initDocumentEdit();
        FgUtility.showTranslation(fgDocumentEdit.settings.selectedLang); 
        
        /* function to show data in different languages on switching language */
        $('form').on('click', 'button[data-elem-function=switch_lang]', function() {
            fgDocumentEdit.settings.selectedLang = $(this).attr('data-selected-lang');
            FgUtility.showTranslation(fgDocumentEdit.settings.selectedLang);
        }); 
        
        $('body').on('click', '#save_changes', function() {
            if ($('#documentsettings').valid()) {
                var paramObj = {};
                paramObj.form = $('#documentsettings');
                paramObj.url = fgDocumentEdit.settings.updateDocumentUrl;
                paramObj.successCallback = function(){
                 FgUtility.showTranslation(fgDocumentEdit.settings.selectedLang); 
                };
                FgDirtyFields.removeAllDirtyInstances();                             
                FgXmlHttp.formPost(paramObj);
            } else{
                FgUtility.showTranslation(fgDocumentEdit.settings.defaultLang); 
            }
            
            return false;
        });

        $('body').on('change','input[data-deposited]',function() {
            var visibleFor = $('.teamFunctionSection').find('input[data-deposited]:checked').val();
            if (visibleFor == 'team_functions') {
                $('select.teamFunctionSelect').removeAttr('disabled');
                $('select.teamFunctionSelect').selectpicker('refresh');
            } else {
                $('select.teamFunctionSelect').attr('disabled', 'disabled');
                $('.teamFunctionSelect').find('button').addClass('disabled');
            }
        });
    });