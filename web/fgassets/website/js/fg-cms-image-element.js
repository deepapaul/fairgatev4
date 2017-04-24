var buttonClick = true;
var ImageElement = {
    init: function() {
        ImageElement.commonInit('add');
    },
    commonInit: function(mode){
        $( ".fg-action-menu-wrapper" ).FgPageTitlebar({
            title:true,
            tabType:'client',
            tab:true,
            languageSwitch: true
        });
        //disable slider time text on page load
        $('.fg-slider-time').attr('disabled', true);
        var option = {
            pageType: 'cmsAddElement',
            contactId: contactId,
            currentClubId: clubId,
            localStorageName: type + '_' + clubId + '_' + contactId,
            tabheadingArray: tabheadingArray
        };
        FgFormTools.handleUniform();
        $('#image_link_target').select2();
        Fgtabselectionprocess.initialize(option);
        FgGlobalSettings.handleLangSwitch();
        FgLanguageSwitch.checkMissingTranslation(defaultClubLang);
        FgGalleryBrowser.initialize(galleryBrowserSettings);
        ImageElement.initUploader(imageElementUploaderOptions);
        if (mode === 'edit' || mode === 'discard') {
            ImageElement.dirtyInit('image_uploader_form', true);
        }
        //hide link type on page load
        ImageElement.handleView();
        ImageElement.handleDisplayType();
        ImageElement.handleFileUpload();
        ImageElement.addLink();
        if (mode != 'discard') {
            $('.selectpicker').select2();
        }
        ImageElement.saveAddLinkPopup();
        ImageElement.deleteLink();
        ImageElement.deleteElement();
        ImageElement.handleVideoUrls();
        ImageElementSaveCancel.save();
        ImageElementSaveCancel.discard();
        ImageElement.renderContent();
        ImageElement.handleTargetDisplay();
    },
    dirtyInit: function(formName, denoteDirty){
        FgDirtyFields.init(formName, {
            dirtyFieldSettings :{
                denoteDirtyForm  : denoteDirty
            }, 
            setNewFieldsClean:true,
            initialHtml : false,
            saveChangeSelector : "#save_changes,#save_bac",
            enableDiscardChanges : true,
            discardChangesCallback : ImageElement.discardChangesCallback
        });
    },
    discardChangesCallback: function(){
        if(status == 'old') {
            FgGalleryBrowser.initialize(galleryBrowserSettings);
            ImageElement.handleSortOrder();
            //remove uniform to reinit for discard changes
            var uniformSuspectedElements = $("#rowDisplay,#columnDisplay,#sliderDisplay");
            if ( uniformSuspectedElements.parent().parent().is( "div" ) ) {
                uniformSuspectedElements.unwrap().unwrap();
            }
            ImageElement.commonInit('discard');
            if(displayType === 'slider') {
                $('.fg-slider-time').attr('disabled', false);
            }
        }
    },
    handleView: function(){
        $("input:radio[name=imageAction]").click(function() {
            var value = $(this).attr('id');
            ImageElement.viewContent(value);
            ImageElement.handleTargetDisplay();
        });
    },
    handleTargetDisplay: function(){
        var selectedDisplay = $("input:radio[name=displayType]:checked").val();
        var selectedAction = $("input:radio[name=imageAction]:checked").val();
        if(selectedDisplay != 'slider' && selectedAction == 'linkView'){
            $('#image_link_target_container').removeClass('hide');
        } else {
            $('#image_link_target_container').addClass('hide');
            $('#image_link_target').select2();
        }
    },
    setErrorMessage: function(uploadObj, data) {
        var template = $('#'+imageElementUploaderOptions.validationErrorTemplateId).html();
        var result = _.template(template, {error : data.result.error,name:data.result.filename });
        $('#'+data.fileid).find('.fg-replacewith-errormsg').html(result);
        $('#'+data.fileid).addClass('has-error');
        $('#'+data.fileid+" input:hidden").not('.fg-delete').remove();
    },
    renderContent: function () {
        $('.fg-cms-image-video-edit-wrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsImageVideoElementContent').addClass('active');
        $('.fg-lang-tab').removeClass('invisible');
    },
    renderLog: function () {
        $('.fg-cms-image-video-edit-wrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        ImageElement.initLog();

    },
    initLog: function () {
        ImageElement.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    },
    reload: function () {
        listTable.ajax.reload();
    },
    dataTableOpt: function () {
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var i = 0;
        columnDefs.push({"name": "date", "type": "moment-" + currentDateFormat, "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['date'];
                row.displayData = '&nbsp;&nbsp;' + row['date'];
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "option", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">'+statusTranslations[row['status']]+'</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">'+statusTranslations[row['status']]+'</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">'+statusTranslations[row['status']]+'</span>' : '-'));
                var type = (row['type'] === 'element') ? statusTranslations.element : statusTranslations.page_assignment;
                row.sortData = row['type'];
                row.displayData = type+ flag;
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_before", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueBefore'];
                row.displayData = row['valueBefore'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_after", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueAfter'];
                row.displayData = row['valueAfter'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "edited_by", "targets": i++, data: function (row, type, val, meta) {
                var profileLink = profilePath.replace("**placeholder**", row['activeContactId']);
                row.sortData = row['contact'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['contact'] + '</a></div>' : '<span class="fg-table-reply">' + row['contact'] + '</span>';
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});

        datatableOptions = {
            columnDefFlag: true,
            ajaxPath: elementLogDetailsPath,
            ajaxparameterflag: true,
            ajaxparameters: {
                elementId: elementId
            },
            columnDefValues: columnDefs,
            serverSideprocess: false,
            displaylengthflag: false,
            initialSortingFlag: true,
            initialsortingColumn: '0',
            initialSortingorder: 'desc',
            fixedcolumnCount: 0
        };
    },
    viewContent: function(value) {
        if(value === 'detailView' || value === 'noneAction' ){
            $('.cms-imgvideo-desc').removeClass('hide');
            $('.cms-imgvideo-link').addClass('hide');
            $('.cms-video-content').removeClass('hide');
            $('.fg-a-add-video').removeClass('hide');
        } else {
            $('.cms-imgvideo-desc').addClass('hide');
            $('.cms-imgvideo-link').removeClass('hide');
            $('.cms-video-content').addClass('hide');
            $('.fg-a-add-video').addClass('hide');
        }
    },
    handleDisplayType: function() {
        $("input:radio[name=displayType]").click(function() {
            var value = $(this).attr('value');
            if(value === 'slider'){
                $('.fg-slider-time').attr('disabled', false);
                $("#detailView").prop('checked',true);
                $("#linkView").prop('checked',false);
                ImageElement.viewContent('detailView');
                $('#imageclickaction').addClass('hide');
                ImageElement.handleTargetDisplay();
            } else {
                $('.fg-slider-time').attr('disabled', true);
                checkedValue = $("input:radio[name=imageAction]:checked").val();
                ImageElement.viewContent(checkedValue);
                $('#imageclickaction').removeClass('hide');
                ImageElement.handleTargetDisplay();
            }
        });
    },
    handleFileUpload: function() {
        $(".triggerFileUpload").click(function() {
            $('#cms-file-uploader').trigger('click');
        });
    },
    setThumbnail: function(uploadObj, data){
        var rowId = data.fileid;
        if(rowId)
        {
            var icon = "<img class='fg-thumb' src='"+tempUrl+data.formData.title+"'/>";
            $('#'+rowId).find('.fg-thumb-wrapper').html(icon);
            var value = $("input:radio[name=imageAction]:checked").val();
            ImageElement.viewContent(value);
        }
    },
    galleryImageCallback: function() {
      
        var value = $("input:radio[name=imageAction]:checked").val();
        ImageElement.viewContent(value);
        ImageElement.handleSortOrder();
        $('form').find('[type="submit"]').removeAttr('disabled');
        if(status === 'old')
            FgDirtyFields.updateFormState();
    },
    initUploader: function(settings){
        FgFileUpload.init($('#cms-file-uploader'), settings);
    },
    initElements: function(uploadedObj,data) {
        var rowId = data.fileid;
        if(rowId)
        {
            $('.cms-imgvideo-link').addClass('hide');
            ImageElement.handleSortOrder();
        }
    },
    deleteElement: function() {
        $(document).on('click', '.fg-del-close', function () {
            var dataId = $(this).closest("li").attr("id");
            $('#'+dataId).remove();
            FgInternal.resetSortOrder($('#fg-files-uploaded-lists-wrapper'));
        });
    },
    linkToggle: function() {
        var checkedVal = $("input:radio[name=addLinkRadio]:checked").val();
        if (checkedVal === 'externalLink') {
            $('.externalLinkText').attr('disabled', false);
            $('.internalLinkSelect').attr("disabled", true);
        } else {
            $('.externalLinkText').attr('disabled', true);
            $('.internalLinkSelect').attr("disabled", false);
        }
    },
    addLink: function() {
        $(document).on('click', '.fg-add-link', function () {
            $('div.has-error').removeClass('has-error');
            $('span.required').remove();
            $('#externalLink').attr('checked', true);
            jQuery.uniform.update('#externalLink');
            $('#internalLink').attr('checked', false);
            jQuery.uniform.update('#internalLink');
            $('.externalLinkText').val('');
            $('.selectpicker').select2("val", "");
            ImageElement.linkToggle();
            $('#add-link-modal').attr('data-id', $(this).closest("li").attr("id"));
            $("input:radio[name=addLinkRadio]").click(function() {
                ImageElement.linkToggle();
            });
            $('#add-link-modal').modal('show');
        });
    },
    saveAddLinkPopup: function() {
        $('.externalLinkText').on('blur', function(){
            ImageElement.appendHttp(this);
        });
        $(document).on('click', '.saveBtn', function (e) {
            $('div.has-error').removeClass('has-error');
            $('span.required').remove();
            var value = $("input:radio[name=addLinkRadio]:checked").val();
            var dataId = $('#add-link-modal').attr('data-id');
            if(value === 'externalLink'){
                var linkVal = $('.externalLinkText').val();
                if (linkVal == '') {
                    ImageElement.intiValidate('.fg-external-link-form', '.fg-external-required');
                    return false;
                } else {
                    if (!(/^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(linkVal))) {
                        $('.fg-external-link-form').addClass('has-error');
                        $('.fg-external-required').append('<span class=required>'+invalidUrl+'</span>');
                        return false;
                    }
                }
                $('#add-link-modal').modal('hide');
                ImageElement.showExternalLink(dataId, linkVal);
            } else {
                var linkVal = $('.internalLinkSelect :selected').text();
                if ($('.internalLinkSelect').select2("val") == '') {
                    ImageElement.intiValidate('.fg-internal-link-form', '.fg-internal-required');
                    return false;
                }
                $('#add-link-modal').modal('hide');
                $('.cms-link-value-'+dataId).val($('select.internalLinkSelect').val());
                ImageElement.showInternalBadge(dataId, linkVal);
            }
        });
    },
    appendHttp: function (fieldName) {
        var urlVal = $(fieldName).val();
        if ((urlVal != '') && (!urlVal.match(/^[a-zA-Z]+:\/\//))) {
            urlVal = 'http://' + urlVal;
            $(fieldName).val(urlVal);
        }
    },
    showAddLink: function(dataId) {
        $('#'+dataId+' .cms-add-link').removeClass('hide');
        $('#'+dataId+' .cms-external-link').addClass('hide');
        $('#'+dataId+' .cms-internal-link-badge').addClass('hide');
        if(status === 'old'){
            $('.cms-internal-link-badge-'+dataId).addClass('hide');
        }
        $('.cms-link-value-'+dataId).val('');
        $('.cms-link-type-'+dataId).val('');
        if(status === 'old')
            $('#save_changes,#save_bac,#reset_changes').removeAttr('disabled');
    },
    showExternalLink: function(dataId, linkVal) {
        $('#'+dataId+' .cms-add-link').addClass('hide');
        $('#'+dataId+' .cms-external-link').removeClass('hide');
        $('#'+dataId+' .cms-internal-link-badge').addClass('hide');
        $('#'+dataId+' .cms-external-link a').html(linkVal);

        $('.cms-link-value-'+dataId).val(linkVal);
        $('.cms-link-type-'+dataId).val('external');
        $('#'+dataId+' .cms-external-link a').attr('href', linkVal);
        if(status === 'old')
            $('#save_changes,#save_bac,#reset_changes').removeAttr('disabled');
    },
    showInternalBadge: function(dataId, linkVal) {
        $('#'+dataId+' .cms-add-link').addClass('hide');
        $('#'+dataId+' .cms-external-link').addClass('hide');
        $('#'+dataId+' .cms-internal-link-badge').removeClass('hide');
        
        if(status === 'old'){
            $('.cms-internal-link-badge-'+dataId).addClass('hide');
        }
        var selectedLink = $('select.internalLinkSelect').attr('value');
        var hasAccess = $('select.internalLinkSelect').find('option[value="'+selectedLink+'"]').attr('data-hasAccess');
        var pageId = $('select.internalLinkSelect').find('option[value="'+selectedLink+'"]').attr('data-pageId');
        var pathInternalLink = linkEditPath.replace("dummy", pageId);
        if(parseInt(hasAccess)){
            $('#'+dataId+' .cms-internal-link-badge a,#'+dataId+' .cms-internal-link-badge span').replaceWith('<a href='+pathInternalLink+' class="fg-link-page cms-internal-link-badge">'+ linkVal +'</span>');
        } else {
            $('#'+dataId+' .cms-internal-link-badge a,#'+dataId+' .cms-internal-link-badge span').replaceWith('<span class="fg-link-page cms-internal-link-badge">'+ linkVal +'</span>');
        }
        
        $('.cms-link-type-'+dataId).val('internal');
        if(status === 'old')
            $('#save_changes,#save_bac,#reset_changes').removeAttr('disabled');
    },
    deleteLink: function() {
        $(document).on('click', '.cms-linkdelete', function () {
            var dataId = $(this).closest("li").attr("id");
            ImageElement.showAddLink(dataId);
        });
    },
    intiValidate: function (hasErrorClass, requiredClass) {
        $(hasErrorClass).addClass('has-error');
        if(requiredClass != '')
            $(requiredClass).append('<span class=required>' + required + '</span>');
    },
    handleVideoUrls: function() {
        $('body').off('click', '.fg-a-add-video');
        $('body').on('click', '.fg-a-add-video', function(e){
            ImageElement.addVideoTemplate();
        });
        $('body').off('blur', ".fg-files-uploaded-list .video-url");
        $('body').on('blur', ".fg-files-uploaded-list .video-url", function () {
            $('div.has-error').removeClass('has-error');
            $('span.required').remove();
            parentId = $(this).parents('.fg-files-uploaded-list').attr('id');
            $('#article-img-preview-'+parentId).attr('src', '');
            $(this).parents('.fg-files-uploaded-list').find('.video-thumb').val('');
            //set falg as 1, after success call back unset it
            $(this).parent('div').find('.invalid-video-url-flag').val($(this).parent('div').attr('id'));
            // -----------------------

            var urlVal = $(this).val();
            if(urlVal) {
                var settings = {'urlVal' : urlVal, 'inputElement': $(this), 'successCallBack': ImageElement.changeVideoUrlCallBack, 'parentId' : parentId };
                FgVideoThumbnail.showThumbOnChangingUrl(settings )
            }
        });
    },
    //change video url success call back, add thumbnail image
    changeVideoUrlCallBack: function(settings) {
        $('#article-img-preview-'+settings.parentId).attr('src', settings.videoThumb);
        settings.inputElement.parents('.fg-files-uploaded-list').find('.video-thumb').val(settings.videoThumb).addClass('fairgatedirty');
        settings.inputElement.parent('div').find('.invalid-video-url-flag').val('');
    },
    addVideoTemplate: function() {
        var timestamp = $.now();
        var random1 = Math.random().toString(36).slice(2);
        var random2 = Math.random().toString(36).slice(2);
        var thisId = random1+'-'+timestamp+'-'+random2;

        var result_data = FGTemplate.bind('article-video-upload', {'id': thisId});
        $('.fg-files-uploaded-lists-wrapper').append(result_data);
        ImageElement.handleSortOrder();
        if(status === 'old')
            FgDirtyFields.updateFormState();
    },
    handleSortOrder: function() {
        FgInternalDragAndDrop.sortWithOrderUpdation('#fg-files-uploaded-lists-wrapper', false);
        FgInternal.resetSortOrder($('#fg-files-uploaded-lists-wrapper'));
    },
}
var EditImageElement = {
    init: function(){
        EditImageElement.bindEdit();
        ImageElement.commonInit('edit');
        FgDirtyFields.addFields($('.fg-files-upload-wrapper'));
        if(displayType === 'slider') {
            $('.fg-slider-time').attr('disabled', false);
        }
        EditImageElement.deleteInEdit();
        var totalLength = $('#fg-files-uploaded-lists-wrapper li').length;
        if(totalLength > 1)
            EditImageElement.displayEditSortOrder(totalLength);
        ImageElement.handleSortOrder();
        EditImageElement.onKeyUpDesc();
    },
    onKeyUpDesc: function() {
        $(document).on("keyup","textarea",function( event ) { 
            FgDirtyFields.updateFormState();
        });
    },
    displayEditSortOrder: function(totalLength) {
        var divObj = $('<ul ></ul>');
        for (var i=1;i<=totalLength;i++) {
            divObj.append($('#'+i+'_sort_order').closest('.fg-files-uploaded-lists-wrapper li'));
        }
        $('#fg-files-uploaded-lists-wrapper').html(
                divObj.html()
                );
    },
    deleteInEdit: function() {
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function() {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('li#' + parentId);
            $(parentDiv).toggleClass('inactiveblock');
            ImageElement.handleSortOrder();
            FgDirtyFields.updateFormState();
        });
    },
    bindEdit: function(){
        var result_data_img = FGTemplate.bind('cms-imgvideo-upload-edit', {});
        $('#fg-files-uploaded-lists-wrapper').append(result_data_img);
        $('#fg-files-uploaded-lists-wrapper').show();
        var result_data_vdo = FGTemplate.bind('article-video-upload-edit', {});
        $('#fg-files-uploaded-lists-wrapper').append(result_data_vdo);
        $('#fg-files-uploaded-lists-wrapper').show();
        var value = $("input:radio[name=imageAction]:checked").val();
        ImageElement.viewContent(value);
    }
}
var ImageElementSaveCancel = {
    save: function(){
        $(document).off('click', '#save_changes');
        $(document).on('click', '#save_changes,#save_bac', function () {
            if (buttonClick) {
                $('div.has-error').removeClass('has-error');
                $('span.required').remove();
                $('#image-error-noimg-container').addClass('hide');
                var objectCalendarData = FgInternalParseFormField.fieldParse();
                stringifyData = JSON.stringify(objectCalendarData);
                var sliderTime = $('.fg-slider-time').val();
                var displayType = $("input:radio[name=displayType]:checked").attr('value');
                if (displayType =='slider' && sliderTime == '') {
                    ImageElement.intiValidate('.fg-slider-time-error', '');
                    return false;
                }
                var err = true;
                $('.fg-files-upload-wrapper .fileimgcontent').each( function(i, value){
                    var videoId = $(this).attr('id');
                    if ($('#videoThumb-'+videoId).val() === '' || $('#videoThumbImg-'+videoId).val() === '') {
                        $('.fg-video-error-'+videoId).addClass('has-error');
                        $('.fg-video-error-'+videoId).append('<span class=required>' + invalidUrl + '</span>');
                        err = false;
                    }
                });
                if($('li.has-error').length >0){
                    return false;
                }
                var currentSelectedButton = $(this).attr('id');
                var saveType = (currentSelectedButton == 'save_changes') ? 'save' : 'saveBack';
                var data = {'saveData': stringifyData, 'saveType': saveType, 'boxId':boxId, 'elementId':elementId, 'sortOrder':sortOrder, 'pageId':pageId}
                if($(".fileimgcontent,.filecontent:not(.inactiveblock)").length > 0 && err) {
                    if(saveType === 'saveBack' && status === 'old')
                        FgDirtyFields.removeAllDirtyInstances();
                    FgXmlHttp.post(saveImageElement, data, false, ImageElementSaveCancel.saveElementCallback(saveType));
                } else {
                    $('#image-error-noimg-container').removeClass('hide');
                }
                buttonClick = true;
            }
        });
    },
    discard: function(){
        $(document).off('click', '#cancel_btn');
        $(document).on('click', '#cancel_btn', function () {
            window.location = cancelPath;
        });
    },
    saveElementCallback: function(saveType){
        if(saveType === 'save' && status === 'old') {
            buttonClick = false;
            $('.fg-files-upload-wrapper .inactiveblock').remove();
            ImageElement.dirtyInit('image_uploader_form', true);
        }
    }
}
