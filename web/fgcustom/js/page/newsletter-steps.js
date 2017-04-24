var data = {}
var toaddFile = 0;
var totalAddedFileSize = 0;
var totalAddedFileSizeFormatted = '';
var mega15ByteCheck = 15728640;
var trigger = false;
var signatureOverwriteText = '';
var introOverwriteText = '';
var closingOverwriteText = '';
var signatureDeleteText = '';
var introDeleteText = '';
var closingDeleteText = '';
var simpleToolsArr=ckEditorConfig.mailSimpleNewsletter;
var advancedToolsArr=ckEditorConfig.mailAdvancedNewsletter;
var fileSize = new Array();

$(function() {
    var loadinit = function(){
        _dynamicFunction.editor = {
            init: function(obj){
                var that = _dynamicFunction.editor;               
                that.updateCKEditor(obj);
                that.event(obj);
                if(newsletterType=='newsletter'){
                  that.limitTextarea(obj);  
                }
                
            },
            updateCKEditor: function(obj){
                var textareaName = 'editor-'+obj.id;
                $('.fg-simple-editor').hide();
                if(CKEDITOR.instances[textareaName]){
                    CKEDITOR.instances[textareaName].destroy();
                }                      
                if($('#'+textareaName).length>0) {
                    CKEDITOR.replace(textareaName, {
                        toolbar: simpleToolsArr,
                        language :datatabletranslations.localeName,
                        filebrowserBrowseUrl: filemanagerDocumentBrowse,
                        filebrowserImageBrowseUrl: filemanagerImageBrowse,
                    }); 
                    CKEDITOR.config.dialog_noConfirmCancel = true;
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
                    CKEDITOR.instances[textareaName].addContentsCss('/fgcustom/css/fg-ckeditor-mail.css');
                    ckeditorConfig.ckEditorImageResize();  
                }
            },            
            event: function(obj){
                var textareaName = 'editor-'+obj.id;
                $('body').on('click', '.fg-editor-'+obj.id, function(e){
                    if($('#'+textareaName).hasClass('basic')){
                        if(CKEDITOR.instances[textareaName]){
                            CKEDITOR.instances[textareaName].destroy();
                        }       
                        CKEDITOR.replace(textareaName, {
                            toolbar: advancedToolsArr,
                            language :datatabletranslations.localeName,
                            filebrowserBrowseUrl: filemanagerDocumentBrowse,
                            filebrowserImageBrowseUrl: filemanagerImageBrowse,
                        });
                        CKEDITOR.config.dialog_noConfirmCancel = true;
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
                        ckeditorConfig.ckEditorImageResize();    
                        $('.fg-editor-'+obj.id+' .fg-advanced-editor').hide();
                        $('.fg-editor-'+obj.id+' .fg-simple-editor').show();
                        $('#'+textareaName).removeClass('basic');       
                    }
                    else{
                        if(CKEDITOR.instances[textareaName]){
                            CKEDITOR.instances[textareaName].destroy();
                        }       
                        CKEDITOR.replace(textareaName, {
                            toolbar: simpleToolsArr,
                            language :datatabletranslations.localeName,
                            filebrowserBrowseUrl: filemanagerDocumentBrowse,
                            filebrowserImageBrowseUrl: filemanagerImageBrowse,
                        });
                        CKEDITOR.config.dialog_noConfirmCancel = true;
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
                        ckeditorConfig.ckEditorImageResize();    
                        $('.fg-simple-editor').hide();
                        $('.fg-advanced-editor').show();
                        $('#'+textareaName).addClass('basic');                      
                    }
                    CKEDITOR.instances[textareaName].addContentsCss('/fgcustom/css/fg-ckeditor-mail.css');
                    e.preventDefault();
                });
            },
            getTemplate: function(obj){
                var newUrl = (obj.redirectUrl!=undefined) ? obj.redirectUrl : obj.url;
                $.getJSON(newUrl, function(json, textStatus) {
                        _dynamicFunction.editor.createTemplatelist({data:json,editor:'editor-'+obj.id,obj:obj});
                        _dynamicFunction.editor.saveTemplateList({editor:'editor-'+obj.id,data:obj});
                });

            },
            createTemplatelist: function(obj){   
                var objData = obj.data;
                var templateList;
                $.each(objData,function(index,item){
                    var selectedItem = (item.active==1) ? 'selected="selected"' : '';
                     templateList+='<option value="'+item.id+'"  id="'+index+'" '+selectedItem+'>'+item.title+'</option>'
                });
                
                var element = $('#dynamic-select-'+obj.obj.id);
                    element.html('');
                    element.html(templateList);
                CKEDITOR.instances[obj.editor].setData(obj.data[0].value);  
                var content;
                element.off('change');
                element.on('change',function(){
                    var _this = $(this);
                    var templateNameId =  $(this).parents('.form-group').find('.fg-save-template').data('id');
                    $(this).parents('.form-group').find('.fg-name-template-close').click();
                    var title = objData[_dynamicFunction.editor.getId(_this)].title;
                    if(_this.val() == '0'){
                        $(this).parents('.form-group').find('.fg-delete-template').addClass('hidden');
                        $(this).parents('.form-group').find('.fg-template-name-'+templateNameId).val("");
                        $('.fg-template-delete-name').text('');
                    }else{
                        $(this).parents('.form-group').find('.fg-delete-template').removeClass('hidden');
                        $('.fg-template-delete-name').text(title);
                        $(this).parents('.form-group').find('.fg-template-name-'+templateNameId).val(title);
                    }                    
                    content = objData[_dynamicFunction.editor.getId(element)].value;
                    CKEDITOR.instances[obj.editor].setData(content);
                });
                FgFormTools.handleSelect2();
            },

            saveTemplateList: function(obj){
                if(!$("#"+obj.data.id).hasClass('prevent')){
                    _dynamicFunction.editor.deleteTemplate({editor:'editor-'+obj.data.id,data:obj.data});
                    $('body').on('click', '.template-action-btn-'+obj.data.id, function(e){
                        $(this).parents('.form-group').find('.fg-save-template').addClass('show-input');
                        e.preventDefault();
                    });
                    $('body').on('click', '.fg-name-template-close', function(e){
                        $(this).parents('.form-group').find('.fg-save-template').removeClass('show-input');
                        e.preventDefault();
                    });
                    $('body').on('click', '.fg-name-template-save-'+obj.data.id, function(e){
                        FgUtility.startPageLoading();
                        var _this = $(this);
                        var templateTitle = _dynamicFunction.editor.getValue('.fg-template-name-'+obj.data.id),
                            templateId = $('#dynamic-select-'+obj.data.id).val();
                        if(_dynamicFunction.editor.haveDuplicate(templateTitle,obj) && (_this.data('check')!=undefined)){
                            FgUtility.stopPageLoading();
                            var overwriteText = $('#basic3-'+obj.data.id+' .fg-dev-overwrite-text').val();
                            var tempTitle = '"'+templateTitle+'"';
                            overwriteText = overwriteText.replace("%templateName%", tempTitle);
                            $('#basic3-'+obj.data.id+' .fg-dev-confirm-text').text(overwriteText);
                            $('#basic3-'+obj.data.id).modal('show');
                        }else{
                            obj.data.url= _this.data('url');
                            obj.data.redirectUrl = _this.data('redirect-url');
                            _dynamicFunction.editor.sendjson({
                                data:obj.data, 
                                json: {
                                    title: $('.fg-template-name-'+obj.data.id).val(),
                                    type: $('.fg-template-name-'+obj.data.id).data('type'),
                                    id: templateId,
                                    value: CKEDITOR.instances['editor-'+obj.data.id].getData()
                        }
                            });
                        }

                        e.preventDefault();
                    });
                    $('body').on('keypress', '.fg-template-name-'+obj.data.id, function(event) {
                        var keycode = (event.keyCode ? event.keyCode : event.which);
                        if (keycode == '13') {// Enter key press
                           $('.fg-name-template .fg-name-template-save-'+obj.data.id).trigger("click");
                        }
                    });
                    $('body').on('keyup', '.fg-template-name-'+obj.data.id, function(event) {
                       var keycode = (event.keyCode ? event.keyCode : event.which);
                       if(keycode == '27') {
                           $(".fg-name-template[data-id='"+obj.data.id+"'] i.fa-times").trigger("click");
                       }
                    });
                    $("#"+obj.data.id).addClass('prevent');
                }
            },
            sendjson: function(obj){
                obj.data.thisObj.removeClass('revealed opened in');
                $.ajax({
                    url: obj.data.url,
                    type: 'POST',
                    dataType: 'json',
                    data: obj.json,
                })
                .done(function(result) {
                    FgUtility.stopPageLoading();
                    FgUtility.showToastr(result.flash); 
                     $('#basic3-'+obj.data.id).modal('hide');
                     $('#basic2-'+obj.data.id).modal('hide');
                    
                    _dynamicFunction.editor.getTemplate(obj.data);
                    setTimeout(function(){
                        $("#dynamic-select-"+obj.data.id+" option:selected").removeAttr("selected");
                        $("#dynamic-select-"+obj.data.id+" option[value='"+result.id +"']").attr('selected', 'selected'); 
                        $('#dynamic-select-'+obj.data.id).trigger("change");
                        FgFormTools.handleSelect2();
                  }, 2000);
                })
                .fail(function() {
                    $('#basic3-'+obj.data.id).modal('hide');
                    $('#basic2-'+obj.data.id).modal('hide');
                    _dynamicFunction.editor.getTemplate(obj.data)
                })
                
            },
            haveDuplicate: function(data,obj){
                var haveItem = false;
                $('#dynamic-select-'+obj.data.id+' option[value!="0"]').each(function(index,item){
                    if(data===$(item).text()){
                        haveItem=true;
                    }
                });                
                return haveItem;
            },
            deleteTemplate: function(obj){
                $('body').on('click', '.fg-delete-template-'+obj.data.id, function(e){                       
                    var overwriteText = $('#basic3-'+obj.data.id+' .fg-dev-overwrite-text').val();
                    var tempTitle = '"'+obj.data.thisObj.find(":selected").text()+'"';
                    var deleteText = $('#basic2-'+obj.data.id+' .fg-dev-delete-text').val();
                    deleteText = deleteText.replace("%templateName%", tempTitle);
                    $('#basic2-'+obj.data.id+' .fg-dev-confirm-text').text(deleteText);
                    $('#basic2-'+obj.data.id).modal('show');
                    e.preventDefault();
                });
                 $('body').on('click', '.fg-remove-template-'+obj.data.id, function(e){
                    FgUtility.startPageLoading();
                    var deleteTemplateJson = {
                        type: $(this).data('type'),
                        id: $('#dynamic-select-'+obj.data.id).val()
                    }
                    obj.data.url = $(this).data('url');
                    obj.data.redirectUrl = $(this).data('redirect-url');
                    _dynamicFunction.editor.sendjson({data:obj.data,json:deleteTemplateJson});
                    e.preventDefault();
                });
            },
            getValue: function(element){
                return $(element).val();
            },
            getId: function(element){
                return element.children(":selected").attr("id");;
            },
            setCkeditorData:function(){
                $('textarea.ckeditor').each(function(){
                    if(CKEDITOR.instances[$(this).attr('name')]) {
                        $(this).html(CKEDITOR.instances[$(this).attr('id')].getData());
                    }
                });
            },
            limitTextarea: function(obj){
                if($('.teaser-'+obj.id).length>0){
                    var maxCount = 160;
                    FgGlobalSettings.characterCount($('.teaser-'+obj.id),160, $('.teaser-'+obj.id).siblings('.fg-text-limit'));
                }
            }
        }
        
        _dynamicFunction.imageUploader = {
            tabArticle: '',
            init: function(obj){
                var that = _dynamicFunction.imageUploader;
                tabArticle = obj.val; // = 'newsletter_attachments' if clicking from aricle attachments in newsletter
                that.initUpload(obj);
                that.initUploadAttachments(obj);                
                that.events(obj);
            },

            events: function(obj){
                 $('body').on('click','.removeUpload',function(){
                    if (newsletterType == 'simpleemail' || (tabArticle == 'newsletter_attachments')) {
                    existingSize=0;
                    thisId=$(this).parents('li').attr('id');
                    if(fileSize['a'+thisId]) {
                        toaddFile=toaddFile-fileSize['a'+thisId];
                        delete fileSize['a'+thisId];
                    }
                    if (($('#upload-'+obj.id).attr('data-totalsize')) > 0) {
                            existingSize = parseInt($('#upload-'+obj.id).attr('data-totalsize'));
                    }
                    totalAddedFileSize = toaddFile + existingSize;
                    totalAddedFileSizeFormatted =  _dynamicFunction.imageUploader.formatFileSize(totalAddedFileSize);
                    //$('.fg-attachments-simple').html(totalAddedFileSizeFormatted+' '+transTo+" 15 MB");
                    }
                    $(this).parent('li.newImage').remove();
                });
            },

            initUpload: function(obj){
               
                var limit = $('#upload-'+obj.id).attr('limit');
                $('#upload-'+obj.id).fileupload({

                    // This element will accept file drag/drop uploading
                    dropZone: $('#upload-'+obj.id).parent(),
                    singleFileUploads: true,
                    limitConcurrentUploads: 1,
                    // autoUpload:false,
                    // This function is called when a file is added to the queue;
                    // either via the browse button, or via drag/drop:

                    add: function (e, data) { 
                        $('.button-save, .button-next').addClass('disabled');
                        var listTemplate = $('body').find('#upload-'+obj.id+" ul");
                        var existingSize = 0;
                        var addedSize = 0;
                        if (($('#upload-'+obj.id).attr('data-totalsize')) > 0) {
                            existingSize = parseInt($('#upload-'+obj.id).attr('data-totalsize'));
                        }
                        addedSize = parseInt(data.files[0].size);
                        toaddFile = parseInt(toaddFile) + addedSize;
                        totalAddedFileSize = parseInt(toaddFile) + parseInt(existingSize);
                        totalAddedFileSizeFormatted =  _dynamicFunction.imageUploader.formatFileSize(totalAddedFileSize);
                        var itemId=$.now();
                        if (newsletterType == 'simpleemail') {                            
                            fileSize['a'+itemId]=addedSize;
                        }
                        
                        if(limit && data.originalFiles.length>limit){
                           // console.log("Maximum file number exceeded");
                        }else{
                            
                            if(limit){
                                listTemplate.html("");
                            }
                            countimage=listTemplate.find('li').length+1;
                            dataKey=$('#upload-'+obj.id).attr('data-name')+'.'+countimage;
                            if (newsletterType == 'simpleemail') {
                                var fileName=data.files[0].name;
                                fileName=fileName.replace(/[&\/\\#,+()$~%'"`^=|:;*?<>{}]/g, '');
                                fileName=fileName.replace(/ /g, '-');
                                var sizeF = _dynamicFunction.imageUploader.formatFileSize(data.files[0].size);
                                var progressBarHtml = '<div class="progress progress-striped fg-progress-wrapper"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>';
                                var tpl = $('<li id="'+itemId+'" class="working newImage"><input type="hidden" value="'+data.files[0].name+'" data-key="'+dataKey+'.filename" class="ignore hid-article-image" /><input type="hidden" value="'+fileName+'" data-key="'+dataKey+'.tmpFileName" class="ignore hid-article-image" /><div class="row"><div class="col-sm-6">'+fileName+'</div><div class="col-sm-3">'+progressBarHtml+'</div><div class="col-sm-3 fg-replacewith-errormsg"><span class="fg-bytes">'+sizeF+'<span></div></div><span class="removeUpload"><div class="closeico"><input type="checkbox" class="make-switch ignore"><label for="isDeleted"></label></div></span></li>');

                                data.context = tpl.appendTo(listTemplate);
                                data.nowtime = itemId;
                                data.formData = {title: fileName};
                                var jqXHR = data.submit();
//                                } else{
//                                    console.log("cccc");
//                                    toaddFile = toaddFile-addedSize;
//                                }
                            } else {
                                
                                    var dataURI;
                                    var oFReader = new FileReader();
                                    oFReader.onload = function(eventData){
                                        dataURI = eventData.target.result;
                                    };
                                    var ofile = data.files[0];
                                    var demoURL = oFReader.readAsDataURL(ofile);
                                    var template = $('#'+$('#upload-'+obj.id).attr('data-uploadtemplate')).html();
                                    var sOrder=$('#upload-'+obj.id).find('ul li').length+1;
                                    var nowtime = $.now();                                    
                                    var result = _.template(template, {data: data.files[0],id : nowtime,URL : dataURI, sortOrder:sOrder });
                                    var tpl= $(result);
                                    tpl.find('div span.fg-bytes').text(_dynamicFunction.imageUploader.formatFileSize(data.files[0].size));
                                    setTimeout(function(){
                                        tpl.find('img').attr('src',dataURI);
                                    },700)
                                    data.context = tpl.appendTo(listTemplate);
                                    data.nowtime = nowtime;
                                    var jqXHR = data.submit();
                                
                            }

                            
                            
                        }
                        
                    },

                    progress: function(e, data){
                        var progress = parseInt(data.loaded / data.total * 100, 10); 
                        data.context.find('.progress-bar').css("width",progress+"%").change();
                        if(progress == 100){
//                             data.context.find('.fg-status-bar').hide();
//                            // $(data.files).each(function(i,k){
//                            //     console.log(k.name);
//                            // });
//                            data.context.removeClass('working');
                        }
                    },
                    done: function(e,data){
                        var result= data.result;
                        if(result.status=='success') {
                            $('#'+data.nowtime).find('.hid-article-image').val(result.filename);  
                            setTimeout(function(){
                                if($('#upload-'+obj.id).attr('data-updateglobal') !='undefined') {
                                    $('#'+$('#upload-'+obj.id).attr('data-updateglobal')).val(result.filename);
                                }
                                data.context.find('.fg-status-bar').hide();
                                data.context.find('.fg-progress-wrapper').hide();
                                data.context.removeClass('working');
                            },100); 
                        } else { 
                            var template = $('#fileUploadError').html();
                            var result = _.template(template, {error : result.error,name:result.filename });
                            if($('#upload-'+obj.id).attr('data-updateglobal') !='undefined') {
                                    $('#'+$('#upload-'+obj.id).attr('data-updateglobal')).val('');
                            }
                            data.context.find('.fg-status-bar').hide();
                            data.context.find('.fg-progress-wrapper').hide();
                            $(data.context[0]).find('.fg-replacewith-errormsg').html(result);
                            $(data.context[0]).addClass('has-error');
                            $(data.context[0]).find('input:hidden:not(.make-switch)').remove();
                        }
                        $('.button-save, .button-next').removeClass('disabled');
                    }
                });
               
            },
            
            //init aupload atttachments in newsletter article
            initUploadAttachments: function(obj){
                var limit = $('#uploadattachments-'+obj.id).attr('limit');
                $('#uploadattachments-'+obj.id).fileupload({

                    // This element will accept file drag/drop uploading
                    dropZone: '.fg-nl-attachment',
                    singleFileUploads: true,
                    limitConcurrentUploads: 1,
                    itemId: $.now(),
                    // autoUpload:false,
                    // This function is called when a file is added to the queue;
                    // either via the browse button, or via drag/drop:

                    add: function (e, data) { 
                        $('.button-save, .button-next').addClass('disabled');
                        var listTemplate = $('body').find('#uploadattachments-'+obj.id+" ul");
                        var existingSize = 0;
                        var addedSize = 0;
                        if (($('#uploadattachments-'+obj.id).attr('data-totalsize')) > 0) {
                            existingSize = parseInt($('#uploadattachments-'+obj.id).attr('data-totalsize'));
                        }
                        addedSize = parseInt(data.files[0].size);
                        toaddFile = parseInt(toaddFile) + addedSize;
                        totalAddedFileSize = parseInt(toaddFile) + parseInt(existingSize);
                        totalAddedFileSizeFormatted =  _dynamicFunction.imageUploader.formatFileSize(totalAddedFileSize);                        
                        itemId=$.now();
                        fileSize['a'+itemId]=addedSize;
                        
                        if(limit && data.originalFiles.length>limit){
                           // console.log("Maximum file number exceeded");
                        }else{
                            
                            if(limit){
                                listTemplate.html("");
                            }                                                     
                            
                            var fileName=data.files[0].name;
                            fileName=fileName.replace(/[&\/\\#,+()$~%'"`^=|:;*?<>{}]/g, '');
                            fileName=fileName.replace(/ /g, '-');
                            countimage=listTemplate.find('li').length+1;
                            dataKey=$('#uploadattachments-'+obj.id).attr('data-name')+'.'+countimage;   
                            var sizeF = _dynamicFunction.imageUploader.formatFileSize(data.files[0].size);
                            var progressBarHtml = '<div class="progress progress-striped fg-progress-wrapper"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>';
                            var tpl = $('<li id="'+itemId+'" class="working newImage"><input type="hidden" value="'+fileName+'" data-key="'+dataKey+'.filename" class="ignore hid-article-attachment" /><div  class="row"><div class="col-sm-6">'+fileName+'</div><div class="col-sm-3">'+progressBarHtml+'</div><div class="col-sm-3 fg-replacewith-errormsg pull-right"><span class="fg-bytes">'+sizeF+'<span></div></div><span class="removeUpload"><div class="closeico"><input type="checkbox" class="make-switch ignore"><label for="isDeleted"></label></div></span></li>');
                            data.context = tpl.appendTo(listTemplate);
                            data.nowtime = itemId;
                            data.formData = {title: fileName};
                            var jqXHR = data.submit();                            
                           
                        }
                        
                    },

                    progress: function(e, data){   
                        var progress = parseInt(data.loaded / data.total * 100, 10); 
                        data.context.find('.progress-bar').css("width",progress+"%").change();
                        if(progress == 100){
//                             data.context.find('.fg-status-bar').hide();
//                            // $(data.files).each(function(i,k){
//                            //     console.log(k.name);
//                            // });
//                            data.context.removeClass('working');
                        }
                    },
                    done: function(e,data){
                        var result= data.result;
                        if(result.status=='success') {
                            $('#'+data.nowtime).find('.hid-article-attachment').val(result.filename);                              
                            setTimeout(function(){
                                if($('#uploadattachments-'+obj.id).attr('data-updateglobal') !='undefined') {
                                    $('#'+$('#upload-'+obj.id).attr('data-updateglobal')).val(result.filename);
                                }                                
                                data.context.find('.fg-status-bar').hide();
                                data.context.find('.fg-progress-wrapper').hide();
                                data.context.removeClass('working');
                            },100); 
                        } else { 
                            
                            var template = $('#fileUploadErrorAttachment').html();
                            var result = _.template(template, {error : result.error });
                            if($('#upload-'+obj.id).attr('data-updateglobal') !='undefined') {
                                    $('#'+$('#upload-'+obj.id).attr('data-updateglobal')).val('');
                            }
                            data.context.find('.fg-status-bar').hide();
                            data.context.find('.fg-progress-wrapper').hide();
                            $(data.context[0]).find('.fg-replacewith-errormsg').html(result);
                            $(data.context[0]).addClass('has-error');
                            $(data.context[0]).find('input:hidden:not(.make-switch)').remove();
                        }
                        $('.button-save, .button-next').removeClass('disabled');
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
            }
        }
               
        
    }
//$(window).on('load',function(){
    if (newsletterType == 'simpleemail') {
        trigger = ['editor.init','imageUploader.init'];
    }
    $('div[data-list-wrap]').rowList({
        template: '#newsletterContentlistWrap',
        jsondataUrl: pathGetContent,
        //jsondataUrl: 'http://localhost:8090/json/dummy.json',
        fieldSort: '.sortables',
        submit: ['#save_changes', 'receiverslist'],
        deleteBtn:'.fg-row-close',
        reset: '#reset_changes',
        triggerFn: trigger,
        rowCallback : function(data){  
            $('select.selectpicker').selectpicker({noneSelectedText: datatabletranslations.noneSelectedText});            
            $('select.selectpicker').selectpicker('render');
            FgFormTools.handleUniform();
            FgFormTools.handleSelect2();
            customFun.updateSortOrder();
            var rowid = data.find(".new-row:last").attr("id");
            $('#' + rowid).find('[data-type="imagefullwidth"]').trigger('click');
            // To show add images from server
            FgGalleryBrowser.manageLink($('#addContent .fg-border-line:last .fg-nl-form'));
            
            addAutoComplete();
           
        },
        // searchfilterData: filterData,
        addData: ['.addField', {
            isAllActive: false,
            isNew: true
        }, 'editor.init'],
        loadTemplate:[{
            btn:'.addField',
            template:'#newsletterContent',
            target:'#addContent'
        }, 
        {
            btn:'.cat-1', 
            template:'#imagefullwidth',
            target:'#addContent'

        },
        {
            btn:'.cat-2', 
            template:'#sponsor-area',
            target:'#addContent'

        },
        {
            btn:'.cat-3', 
            template:'#exisiting-article',
            target:'#addContent'

        }],
        validate: true,
        // postURL: saveAction,
        success: function() {
            //alert('Posting Data');
           
            
        },
        startSortableCallback: function(event, ui){
            var _this = {};
            _this.id_textarea = "editor-"+ ui.item.find('[data-fn = "editor.init"]').attr("data-id"); 
            return _this;
            },
        stopSortableCallback: function(event, ui){
            var _this = {};
            _this.id_textarea = "editor-"+ ui.item.find('[data-fn = "editor.init"]').attr("data-id"); 
            return _this;
            },
            useCKEditor: true,
        load: function() { 
            loadinit();
            $('select.select2').select2();            
            setTimeout(function(){      
                $('select.selectpicker').selectpicker({noneSelectedText: datatabletranslations.noneSelectedText});
                $('select.selectpicker').selectpicker('render');                 
                FgFormTools.handleUniform();
                FgFormTools.handleSelect2();
                customFun.updateSortOrder();
                 
            },4000);            
            
        },
        initCallback: function() { //call back function after template loaded  
            customFun.toggleSign();
            FgGalleryBrowser.initialize(galleryBrowserSettings);
            FgFormTools.handleUniform();
            FgGalleryBrowser.setSortable( $('form.fg-nl-form ul.fg-image-area-container') );
            existAutoComplete();
           
        },
       
    });
    //FAIR 889 Step 3: Fold-out sections on first view
    $(window).load(function(){
        if(typeof wizardStep != "undefined" ) {
            if(wizardStep == 2){
                setTimeout(function(){
                    customFunctions.customTrigger('editor.init');
                    //bug fix for default article closeicon click
                    $('.closeico label[for="article_article_isDelete"]').click(function(){
                        $(this).parent().parent().parent().parent().remove();
                     });
                },800);
            }
        }     
        
       
       
 
        $(document).on('click', '.fg-sponsor-settings', function() {            
            $('select.selectpicker').selectpicker({noneSelectedText: datatabletranslations.noneSelectedText});
            $('select.selectpicker').selectpicker('render'); 
            FgFormTools.handleUniform();
            FgFormTools.handleSelect2();
            //to show sponsor preview on opening sponsor contents itself
            var sponsorDivId = $(this).attr('data-target');
            $(sponsorDivId).find('select.select-for-preview:first').first().trigger('change');
        });    
        if(typeof wizardStep != "undefined" ) {
            if(wizardStep == 3){
                FgFileManagerUploader.serverBrowseActions();
                localStorage.setItem('nlBrowseServer','nlUpload');
            }
        }
    });
    var customFun = {
        toggleSign : function(){
            setTimeout(function(){
                //toggle plus minus icons
                $('[data-toggle=collapse]').off('click');
                $('[data-toggle=collapse]').on('click',function(){
                    $(this).find('i').toggleClass('fa-minus-square-o');
                    $(this).find('i').toggleClass('fa-plus-square-o'); 
                    if($(this).find('i').hasClass('fa-minus-square-o')){
                        $(this).siblings().find('i').removeClass('fa-minus-square-o');
                        $(this).siblings().find('i').addClass('fa-plus-square-o');
                    }
                    
                });
            },1000);
        },
        updateSortOrder : function() {
            $('input.sort-val').each(function(i, obj){
                i++;
                $(obj).val(parseInt(i));
            });
        }
    }
    $('.addField,.addCategory').on('click',function(){    
        customFun.toggleSign();
    });
        
     
    //Function to get ad preview on changing select boxes
    $(document).on('change','.select-for-preview', function() {
       var rowId = $(this).attr("row-id");
       var selectedServices =  $(this).parents('.form-body').find('#selected-services'+rowId).val();
       var Services = "";
       if(selectedServices != null) {
           Services = selectedServices.join(",");
       }
       
       var selectedAdareas =  $(this).parents('.form-body').find('#selected-adareas'+rowId).val();
       var selectedwidth =  $(this).parents('.form-body').find('#selected-width'+rowId).val();       
       if(Services !== "" && selectedAdareas!== "" && selectedwidth!== "") { 
           FgUtility.startPageLoading();
           $.post( sponsorPreviewPath, { services: Services, adareas: selectedAdareas, "width" : selectedwidth })
            .done(function( data ) {
                $("#div-sponsor-ad-preview"+rowId).html( data );
                FgUtility.stopPageLoading();
            });
       }        
    });

    $('body').on('click','.fg-cal-browse-server',function(){
       
        window.toSend = $(this);
        if (newsletterType == 'simpleemail') {
             window.upload = 'smUpload';
             window.open(filemanagerDocumentBrowse, "", "width=1000, height=1000");
        }else{
            // in newsletter both attachments and image uploads need to handle
            if($(this).hasClass('nl-attachments')) {  //newsletter attachments
                window.upload = 'nlAttachments';
                window.open(filemanagerDocumentBrowse, "", "width=1000, height=1000");
            } else {   //newsletter images
                window.upload = 'nlUpload';
                window.open(filemanagerImageBrowse, "", "width=1000, height=1000");
            }                       
        }
    });
})     

    function nlFileSelect(){  
        var serverFile = JSON.parse(localStorage.getItem('nlBrowseServer'));
        var fileSize1 = _dynamicFunction.imageUploader.formatFileSize(parseInt(serverFile.size));
        var dataKey = $(window.toSend).parent().data('name');
        var htmlToUse =  $(window.toSend).parent().data('uploadtemplate');
        dataKey = dataKey.replace('upload-','');
        var sortOrder =  $(window.toSend).parent().find(' ul li').length+1 ;
        //append to the interface

        if(htmlToUse == 'imageUploadFull')  {
            
            var dataupdateglobal = $(window.toSend).parent().data('updateglobal');
            var appendContent = $(' <li id="'+serverFile.id+'" class="fg-image-area newImage">'+
            '<div class="fg-image-thumb-wrap"><img src="'+serverFile.url+'"/></div>'+
            '<div><span class="fg-bytes">'+fileSize1+'</span></div>'+
            '<input type="hidden" value="'+serverFile.name+'" data-key="'+dataKey+'.filename" class="ignore" />'+
            '<input type="hidden" value="'+serverFile.id+'" data-key="'+dataKey+'.filemanagerId" class="ignore" />'+
            '<input type="hidden" value="'+ sortOrder +'" data-key="'+dataKey+'.imgorder" class="ignore" />'+
            '</li>');
            $('#'+dataupdateglobal).val(serverFile.name);            
            $(window.toSend).parent().find('ul').html(appendContent);
        }else{            
           
            var appendContent = $(' <li id="'+serverFile.id+'" class=" fg-image-area newImage">'+
                '<div class="fg-image-thumb-wrap"><img src="'+serverFile.url+'"/></div><p>'+newsletterDespTrans+'<span class="fg-bytes">'+fileSize1+'</span></p>'+
                '<input type="hidden" value="'+serverFile.id+'" data-key="'+dataKey+'.'+sortOrder+'.filemanagerId" class="ignore" />'+
                '<input type="hidden" value="'+serverFile.name+'" data-key="'+dataKey+'.'+sortOrder+'.filename" class="ignore" />'+
                '<input type="hidden" value="'+ sortOrder +'" data-key="'+dataKey+'.'+sortOrder+'.imgorder" class="ignore" />'+
                ' <textarea data-key="'+dataKey+'.'+sortOrder+'.description" class="ignore" name="imageDescription" ></textarea>'+
                '<div class="col-md-2 pull-right deletediv removeUpload">'+
                 ' <div class="closeico">'+
                      '<input type="checkbox" class="make-switch ignore">'+
                      '<label for="isDeleted"></label>'+
                  '</div></div> </li>');          
            $(window.toSend).parent().find(' ul').append(appendContent);
        }
    }

    function smFileSelect(){
        var existingSize = 0;
        if (($(window.toSend).parent().attr('data-totalsize')) > 0) {
            existingSize = parseInt($(window.toSend).parent().attr('data-totalsize'));
        }
        var serverFile = JSON.parse(localStorage.getItem('smBrowseServer'));
        var fileSize1 = _dynamicFunction.imageUploader.formatFileSize(parseInt(serverFile.size));
        var dataKey = $(window.toSend).parent().data('name');
        dataKey = dataKey.replace('upload-','');
        toaddFile = parseInt(toaddFile) + parseInt(serverFile.size);
        var sortOrder =  $(window.toSend).parent().find(' ul li').length+1 ;
        var totalsize = _dynamicFunction.imageUploader.formatFileSize(parseInt(existingSize + toaddFile));
        var now = $.now();
        fileSize["a"+now] = parseInt(serverFile.size);
        //append to the interface
        var appendContent = $('<li class=" newImage " id ="'+now+'">'+
                '<input type="hidden" value="'+serverFile.name+'" data-key="'+dataKey+'.'+sortOrder+'.filename" class="ignore" />'+
                '<input type="hidden" value="'+serverFile.id+'" data-key="'+dataKey+'.'+sortOrder+'.filemanagerId" class="ignore" />'+
                '<div class="row"><div class="col-sm-9"><a target="_blank" href= "'+serverFile.url+'">'+serverFile.name+'</a></div>'+
                '<div class="col-sm-3"><span class="fg-bytes">'+fileSize1+'</span></div></div><div class=" removeUpload">'+
                '<div class="closeico">'+
                '<input type="checkbox" class="make-switch ignore">'+
                '<label for="isDeleted"></label></div></div></li>');
        $(window.toSend).parent().find(' ul ').append(appendContent);
        
    } 
  
    function existAutoComplete(){
        
       $('.cms-article-autofill').each(function() {
            var selectedId = $(this).attr("id").match(/\d+/);
            var selectedVal1 = $('#cms_article_' + selectedId + '_selected').val();
            selectedVal = $.parseJSON(selectedVal1);
            autoComplete($(this), 1, selectedVal);
        });
        $('.article_attach_on').each(function() {
            var selectedId = $(this).attr("id").match(/\d+/);
           if(!$(this).is(':checked')) {
                $('#span_attachment-'+selectedId).text(0);
            }
              
         });
        $('.article_img_position').each(function() {
             var selectedId = $(this).attr("id").match(/\d+/);
             var val = '';
                if($(this).is(':checked')) {
                     val = $(this).val();
                }
             if(val==='none'){
               $('#span_image-'+selectedId).text(0);
             }
         });
          $('.article_attach_on').on("change", function() {
            var selectedId = $(this).attr("id").match(/\d+/);
            var attachval = $('#hide_span_attachment-'+selectedId).text();
            if(!$(this).is(':checked')) {
                $('#span_attachment-'+selectedId).text(0);
                
            }else{
                $('#span_attachment-'+selectedId).text(attachval);
            }
              
         });
        $('.article_img_position').on("change", function() {
             var selectedId = $(this).attr("id").match(/\d+/);
             var val = '';
             var orgVal = $('#hide_span_image-'+selectedId).text();
                if($(this).is(':checked')) {
                     val = $(this).val();
                }
             if(val==='none'){
               $('#span_image-'+selectedId).text(0);
             }else{
                 if(val!=orgVal){
                    $('#span_image-'+selectedId).text(orgVal); 
                 }
             }
         });
         
          
    }
    function addAutoComplete(){
         $('.cms-article-autofill').on("focus", function() {
               autoComplete($(this), 0, '');
           });
            
            $('.article_attach_on').on("change", function() {
            var selectedId = $(this).attr("id").match(/\d+/);
            var attachval = $('#hide_span_attachment-'+selectedId).text();
            if(!$(this).is(':checked')) {
                $('#span_attachment-'+selectedId).text(0);
                
            }else{
                $('#span_attachment-'+selectedId).text(attachval);
            }
              
         });
        $('.article_img_position').on("change", function() {
             var selectedId = $(this).attr("id").match(/\d+/);
             var val = '';
             var orgVal = $('#hide_span_image-'+selectedId).text();
                if($(this).is(':checked')) {
                     val = $(this).val();
                }
             if(val==='none'){
               $('#span_image-'+selectedId).text(0);
             }else{
                 if(val!=orgVal){
                    $('#span_image-'+selectedId).text(orgVal); 
                 }
             }
         });
         
    }
    
    function autoComplete($obj,exist, selectedVal) {
        
         $obj.fbautocomplete({
            url: articlePath, // which url will provide json!
            maxItems: 1,
            selected: selectedVal,
            useCache: false,
             onItemSelected: function ($obj, itemId, selected) {                
                $obj.parents('div').children('span').contents().get(0).nodeValue = selected[0].articleTitle;
                var item_id = $obj.attr("id").match(/\d+/); // 123456
                var articleId = selected[0].articleId;
                var lang = selected[0].lang;
                var str = articleDetails.replace("**dummy**", articleId).replace('**LANG**', lang);
                $.ajax({url: str,
                    success: function (data) {
                       
                        $('#cms_article_hidden_' + item_id).val(articleId);
                        $('#cms_article_lang_hidden_' + item_id).val(lang);
                        $('#cms_article_' + item_id + '_teasertext').text(data.text.teaser);
                        $('#cms_article_' + item_id + '_teasertext').attr('readonly', true);
                        $('#cms_article_takeover_' + item_id).prop('checked', true);
                        $('#cms_article_active_' + item_id).prop('checked', true);                        
                        $('#cms_article_text_' + item_id ).html(data.text.text);                            
                        $('#editor-' + item_id).text(data.text.text);
                        $('#span_image-' + item_id).text(data.image);
                         var radioName1 = 'cms_article_L_'+item_id+'_imgPostion';
                         var radioName2 = 'cms_article_R_'+item_id+'_imgPostion';
                         var radioName3 = 'cms_article_N_'+item_id+'_imgPostion';
                        $("#"+radioName1).attr('checked', true);
                        $("#"+radioName1).parent('span').addClass("checked");
                        $("#"+radioName2).attr('checked', false);
                        $("#"+radioName2).parent('span').removeClass("checked");
                        $("#"+radioName3).attr('checked', false);
                        $("#"+radioName3).parent('span').removeClass("checked");
                        $('#hide_span_image-' + item_id).text(data.image);
                        $('#span_attachment-' + item_id).text(data.attachment);
                        $('#hide_span_attachment-' + item_id).text(data.attachment);
                    }
                });
            },
            onItemRemoved: function ($obj, itemId) {
                var item_id = $obj.attr("id").match(/\d+/); // 123456
                $('#cms_article_hidden_' + item_id).val('');
                $('#cms_article_lang_hidden_' + item_id).val('');
                $('#cms_article_' + item_id + '_teasertext').text('');
                $('#cms_article_text_' + item_id ).html('');  
                //$('#editor-' + item_id).text('');
//                if(CKEDITOR.instances['editor-' + item_id]) {
//                     CKEDITOR.instances['editor-' + item_id].setData('');
//                 }
                $('#span_image-' + item_id).text(0);
                $('#span_attachment-' + item_id).text(0);
                $('#hide_span_image-' + item_id).text(0);
                $('#hide_span_attachment-' + item_id).text(0); 

            },
            onAlreadySelected: function ($obj) {

            }
        }); 
     
       
    }
    
    //give max-with = 435 to images in cke ditor content, to make suit to the preview
    var ckeditorConfig = {
        ckEditorImageResize: function() {
            CKEDITOR.on('dialogDefinition', function (ev) {
                var dialogName = ev.data.name;
                var dialogDefinition = ev.data.definition;
                var dialog = dialogDefinition.dialog;
                var editor = ev.editor;
                if (dialogName == 'image') {
                    dialogDefinition.onOk = function (e) {
                        var imageSrcUrl = e.sender.originalElement.$.src;
                        var width = e.sender.originalElement.$.width;
                        var height = e.sender.originalElement.$.height;
                        var imgHtml = CKEDITOR.dom.element.createFromHtml('<img src="'+imageSrcUrl+ '" style="width: '+width+'; height: '+height+'; max-width: 435px;" alt="" />');
                        editor.insertElement(imgHtml);
                    };
                }
            }); 
        }
    }
    
     