fileUploader = {
            init: function(obj){
                fileUploader.initUpload(obj);
                fileUploader.events(obj);
            },

            events: function(obj){
                 $('body').on('click','div[data-deletable=checknew]',function(){
                    var parent=$(this).find('input[data-parentid]').attr('data-parentid');
                    FgDirtyFields.removeFields($('li#'+parent));
                    $(this).parents('li#'+parent).remove();                    
                    if ($('div[data-upload-doc-area] ul[data-files-ul]>li').length < 1) {
                        $('div[data-upload-doc-area]').addClass('hide');
                    }
                    if ($('div[data-upload-doc-area] .working').length != 0) {
                        documentupload.disableButtons();
                    }
                });
            },

            initUpload: function(obj){
                var limit = $('#'+obj.id).attr('limit');
                var filetypes = fileUploader.getFileTypeArray();
                $('#'+obj.id).fileupload({
                    // This element will accept file drag/drop uploading
                    url:$('#'+obj.id).attr('data-action'),
                    dropZone: $('body'),
                    maxChunkSize: 5000000,
                    autoUpload:true,
                    // This function is called when a file is added to the queue;
                    // either via the browse button, or via drag/drop:

                    add: function (e, data) {
                        $('div[data-upload-doc-area]').removeClass('hide').show();
                        var listTemplate = $('body').find('div[data-fieldarea='+obj.id+"] ul[data-files-ul]");
                        var fileTypes = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'bmp'];
                        if(limit && data.originalFiles.length>limit){
                            console.log("Maximum file number exceeded");
                        } else {
                            
                            if(limit){
                                listTemplate.html("");
                            }
                            documentupload.disableButtons();
                            var thisId=$.now();
                            var fileName= thisId+'-'+data.files[0].name;
                            fileName=fileName.replace(/[^A-Za-z0-9\-_.]/g, '-');
                            countimage=listTemplate.find('li').length+1;
                            var template = $('#'+$('#'+obj.id).attr('data-uploadtemplate')).html();
                            if($('#'+obj.id).attr('data-uploadtemplate')==='editDocUpload'){
                                $('#'+obj.id).parents('div[data-provides=fileinput]').removeClass('fileinput-new').addClass('fileinput-exists');
                                $('#'+obj.id).parents('div[data-provides=fileinput]').find('span.fileinput-filename').text(data.files[0].name);
                            }
                            dataKey=($('#'+obj.id).attr('data-name')==='') ? countimage: $('#'+obj.id).attr('data-name')+'.'+countimage;
                            var dataURI;
                            var support=true;
                            if('FileReader' in window) {
                                var oFReader = new FileReader();

                                var ofile = data.files[0];
                                var demoURL = oFReader.readAsDataURL(ofile);
                            } else{
                                support=false;
                            }
                            var sOrder=$('#'+obj.id).find('ul li').length+1;
                            var menuType=(typeof contactId === typeof undifined) ? null:localStorage.getItem('activeSubMenu-'+docType+'-'+clubId+'-'+contactId);
                            menuType = (menuType === null) ? '' :menuType.split('_');
                            var docCat=(typeof jsonData !== typeof undefined) ? jsonData['DOCS-'+clubId]['entry']:'';
                            var result = _.template(template, {data: data.files[0],docCategory:docCat,type:menuType,id : thisId,URL : dataURI,filenameReal:data.files[0].name, filename:fileName });
                            var tpl= $(result);
                            /* if no category created disable upload */
                            if($(tpl).find('select[data-subcategoryid] option').length==0 && $('#'+obj.id).attr('data-uploadtemplate') !=='editDocUpload'){
                                $('div[data-upload-doc-area]').hide();
                                return false;
                            }
                            tpl.find('[data-filesize]').text(fileUploader.formatFileSize(data.files[0].size));
                            var ext=data.files[0].name.split('.').pop().toLowerCase();
                            //show preview image or icon
                            if(fileTypes.indexOf(ext) > -1 && support){
                                oFReader.onload = function(eventData){
                                    dataURI = eventData.target.result;
                                    tpl.find('div[data-image-area]').html("<img src='"+dataURI+"' />");
                                };
                            } else if(filetypes.docTypes.indexOf(ext) > -1){
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file-word'></i>");
                            } else if(filetypes.pdfTypes.indexOf(ext) > -1){
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file-pdf'></i>");
                            } else if(filetypes.textTypes.indexOf(ext) > -1){
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file-text'></i>");
                            } else if(filetypes.excelTypes.indexOf(ext) > -1){
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file-excel'></i>");
                            } else if(filetypes.powerType.indexOf(ext) > -1){
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file-powerpoint'></i>");
                            } else if(filetypes.archiveType.indexOf(ext) > -1){
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file-zip'></i>");
                            } else if(filetypes.audioType.indexOf(ext) > -1){
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file-sound'></i>");
                            } else if(filetypes.videoType.indexOf(ext) > -1){
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file-video'></i>");
                            } else if(filetypes.webTypes.indexOf(ext) > -1){
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file-code'></i>");
                            } else {
                                tpl.find('div[data-image-area]').html("<i class='fa fg-file'></i>");
                            }
                            data.context = tpl.appendTo(listTemplate);
                            data.formData = {title: fileName};
                            var rowcallback=$('#'+obj.id).attr('data-row-callback');
                            if(typeof rowcallback !== typeof undefined && rowcallback != false) {
                                eval(rowcallback+'('+thisId+')');
                            }
                            var jqXHR = data.submit();
                                
                            
                        }
                        
                    },

                    progress: function(e, data){
                        var progress = parseInt(data.loaded / data.total * 100, 10); 
                        data.context.find('.progress-bar').css("width",progress+"%").change();
                        
                    },
                    done: function(e,data){
                        var result= data.result;
                        if(result.status=='success') {
                            setTimeout(function(){
                                if($('#'+obj.id).attr('data-updateglobal') !='undefined') {
                                    $('#'+$('#'+obj.id).attr('data-updateglobal')).val(result.filename);
                                }
                                data.context.find('input[data-fileName]').val(data.context.find('input[data-fileName]').attr('data-fileName'));
                                data.context.find('div[data-progress]').hide();
                                data.context.removeClass('working');
                                if($('div[data-upload-doc-area] .working').length==0){
                                    FgDirtyFields.updateFormState();
                                }
                            },100); 
                        } else { 
                            var template = $('#fileUploadError').html();
                            var result = _.template(template, {data: data.files[0],id:$(data.context[0]).attr('id'),error : result.error });
                            if($('#'+obj.id).attr('data-updateglobal') !='undefined') {
                                $('#'+$('#'+obj.id).attr('data-updateglobal')).val('');
                            }
                            $(data.context[0]).html(result);
                            $(data.context[0]).addClass('has-error');
                        }
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
            getFileTypeArray:function(){
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
                fileTypes.imgTypes = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'bmp'];
                
                return fileTypes;
            },
            getFileIcon:function(fileName){
                var ext= fileName.toString().split('.').pop().toLowerCase();
                var filetypes = this.getFileTypeArray();

                if(filetypes.docTypes.indexOf(ext) > -1){
                    return "<i class='fa fg-file-word fg-datatable-icon'></i>";
                }else if(filetypes.pdfTypes.indexOf(ext) > -1){
                    return "<i class='fa fg-file-pdf fg-datatable-icon'></i>";
                }else if(filetypes.excelTypes.indexOf(ext) > -1){
                    return "<i class='fa fg-file-excel fg-datatable-icon'></i>";
                }else if(filetypes.powerType.indexOf(ext) > -1){
                    return "<i class='fa fg-file-powerpoint fg-datatable-icon'></i>";
                }else if(filetypes.archiveType.indexOf(ext) > -1){
                    return "<i class='fa fg-file-zip fg-datatable-icon'></i>";
                }else if(filetypes.audioType.indexOf(ext) > -1){
                    return "<i class='fa fg-file-sound fg-datatable-icon'></i>";
                }else if(filetypes.videoType.indexOf(ext) > -1){
                    return "<i class='fa fg-file-video fg-datatable-icon'></i>";
                }else if(filetypes.webTypes.indexOf(ext) > -1){
                    return "<i class='fa fg-file-code fg-datatable-icon'></i>";
                }else if(filetypes.textTypes.indexOf(ext) > -1){
                    return "<i class='fa fg-file-text fg-datatable-icon'></i>";
                }else if(filetypes.imgTypes.indexOf(ext) > -1){
                    return "<i class='fa fg-file-photo fg-datatable-icon'></i>";
                }else{
                    return "<i class='fa fg-file fg-datatable-icon'></i>";
                }
                
                
            }
            
        }