$(function() {

    FgUtility.startPageLoading();
    /* Services listing */
    $('div[data-list-wrap]').rowList({
        template: '#sponsorAds',
        jsondataUrl: sponsorAdVars.pathSponsorAds,
        fieldSort: '.sortables',
        submit: ['#save_changes', 'sponsoradslist'],
        reset: '#reset_changes',
        useDirtyFields: true,
        dirtyFieldsConfig: { enableDiscardChanges : false, enableDragDrop: false, enableUpdateSortOrder: false },
        validate: true,
        postURL: sponsorAdVars.saveAction,
        success: function() {
            alert('Posting Data');
        },
        load: function() {
            //console.log(_dynamicFunction);
        },
        triggerFunction: ['triggerAfterLoading'],
        rowCallback: function() {
            triggerAfterLoading();
        },
        stopSortableCallback: function (event, ui) {
            checkFormButtons();
        }
    });

    /* Function on clicking upload menu */
    $('body').on('click','#add-ads',function(){
        $('input[data-uploadtemplate]').click();
    });
    fileUploader.initUpload({id:'upload-1'});

    /* Check if there is any upload error */
    $('form#sponsoradslist').off('input propertychange paste keypress keyup keydown', 'input');
    $('form#sponsoradslist').on('input propertychange paste keypress keyup keydown', 'input', function(){
        setTimeout(function() { checkFormButtons(); }, 50);
    });
    $('form#sponsoradslist').off('change', 'select');
    $('form#sponsoradslist').on('change', 'select', function(){
        checkFormButtons();
    });
    $('form#sponsoradslist').on('click', 'div.bootstrap-select, .closeico label', function(){
        setTimeout(function() { checkFormButtons(); }, 50);
    });
    /*************************************/
});
/* Functions that should be executed after loading page content */
var initPageFunctions = function() {
    FgUtility.stopPageLoading();

    /* Save Data */
    _dynamicFunctionSuccess = function() {
        resetAdsSortOrder();
        FgDirtyFields.updateFormState();

        var objectGraph = FgParseFormField.fieldParse(),
            stringifyData = JSON.stringify(objectGraph);

        FgXmlHttp.post(sponsorAdVars.saveAction, {saveData: stringifyData, sponsorId: sponsorAdVars.sponsorId}, false, function(){
            _dynamicFunction.getData();
            setTimeout(function(){
                var adsCount = $('div#sortads').children().length;
                if (adsCount > 0){
                    adsCount = adsCount - 1;
                }
                $('li[name=fg-dev-ads-tab] span.badge').text(adsCount); 
            }, 1000);
        });
    }
};
/* Function for resetting the sort order of ads */
var resetAdsSortOrder = function() {
    var elementCount = $('div#sortads').children().not('.inactiveblock').length;
    $('input[data-element=sortorder]').each(function() {
        if (!($($(this).parent()).hasClass('inactiveblock') || $($(this).parent().parent()).hasClass('inactiveblock'))) {
            $(this).val(elementCount);
            elementCount--;
        }
    });
};
/* Function to be executed after loading ads */
var triggerAfterLoading = function() {
    FgFormTools.handleBootstrapSelect();
};
/* File uploading function */
fileUploader = {
    initUpload: function(obj){
        var limit = $('#'+obj.id).attr('limit');
        $('#'+obj.id).fileupload({
            // This element will accept file drag/drop uploading
            url: $('#'+obj.id).attr('data-action'),
            dropZone: $('body'),
            maxChunkSize: 5000000,
            autoUpload: true,
            // This function is called when a file is added to the queue;
            // either via the browse button, or via drag/drop:

            add: function (e, data) {
                if(limit && data.originalFiles.length>limit){
                    console.log("Maximum file number exceeded");
                } else {
                    var thisId = $.now();
                    var template = $('#'+$('#'+obj.id).attr('data-uploadtemplate')).html();
                    if($('#'+obj.id).attr('data-uploadtemplate')==='editDocUpload'){
                        $('#'+obj.id).parents('div[data-provides=fileinput]').removeClass('fileinput-new').addClass('fileinput-exists');
                        $('#'+obj.id).parents('div[data-provides=fileinput]').find('span.fileinput-filename').text(data.files[0].name);
                    }
                    var result = _.template(template, {data: data.files[0], itemId: thisId, filenameReal: data.files[0].name, filename: thisId+'-'+data.files[0].name });
                    var tpl = $(result);
                    var firstRow = $('div[data-list-wrap] :first');
                    if (firstRow.length > 0) {
                        data.context = tpl.insertBefore(firstRow);
                    } else {
                        data.context = $('div[data-list-wrap]').append(tpl);
                    }
                    data.formData = {title: thisId+'-'+data.files[0].name};
                    tpl.find('input#new_'+thisId+'_image').val(data.files[0].name);
                    tpl.find('input#new_'+thisId+'_image_size').val(data.files[0].size);
                    tpl.find('div[data-filesize]').text(fileUploader.formatFileSize(data.files[0].size));
                    var oFReader = new FileReader();
                    var ofile = data.files[0];
                    var demoURL = oFReader.readAsDataURL(ofile);
                    oFReader.onload = function(eventData){
                        var dataURI = eventData.target.result;
                        tpl.find('div[data-image-area]').html("<img src='"+dataURI+"' />");
                    };
                    var rowcallback = $('#'+obj.id).attr('data-row-callback');
                    if(typeof rowcallback !== typeof undefined && rowcallback != false) {
                        eval(rowcallback+'('+thisId+')');
                    }
                    data.submit();
                }
            },

            progress: function(e, data){
                var progress = parseInt(data.loaded / data.total * 100, 10);
                data.context.find('.progress-bar').css("width",progress+"%").change();
            },
            done: function(e, data){
                var result= data.result;
                if(result.status=='success') {
                    setTimeout(function(){
                        data.context.find('div[data-progress]').hide();
                        data.context.removeClass('working');
                        var dataContext = data.context;
                        FgDirtyFields.addFields(dataContext.html());
                        checkFormButtons();
                    },100);
                } else {
                    var template = $('#fileUploadError').html();
                    var result = _.template(template, {data: data.files[0],id:$(data.context[0]).attr('id'),error : result.error });
                    $(data.context[0]).html(result);
                    $(data.context[0]).append('<input type="text" data-type="uploaderror" class="hide" name="error_'+$(data.context[0]).attr('id')+'" id="error_'+$(data.context[0]).attr('id')+'" value="" />');
                    $(data.context[0]).addClass('has-error');
                    checkFormButtons();
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
    afterUpload: function(obj){
        FgFormTools.handleBootstrapSelect();
    }
};
var checkFormButtons = function() {
    var errorCnt = $('input[data-type=uploaderror]').length;
    if (errorCnt > 0) {
        FgDirtyForm.disableButtons();
        $('.alert-danger').show();
    }
};