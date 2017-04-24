FgActionmenuhandler = {
     init: function() {
        $("body").off('click', ".fg-action-menu-list a");
        $("body").on('click', ".fg-action-menu-list a", function(event) {

            event.preventDefault();
            var checkedIds = '';
            var seperator = '';
            var actionType = $(this).attr('data-menu-key');
            var callbackFunction = $(this).data('callback');
            var ajaxUrl = $(this).attr('data-url');
           
            if ($(".dataTables_wrapper div").hasClass('DTFC_LeftBodyWrapper')) {
                $('.dataTables_wrapper .DTFC_LeftBodyWrapper tbody input.dataClass:checked').each(function() {
                    checkedIds += seperator + $(this).attr('id');
                    seperator = ',';
                });
            } else {
                $('input.dataClass:visible:checked').each(function() {
                    checkedIds += seperator + $(this).attr('id');
                    seperator = ',';
                });
            }
            switch (actionType) {
                case 'reminder':
                    //code goes here
                    if(callbackFunction){
                        window[callbackFunction]();
                    }
                break;
                case 'Export':

                    var roleDetails = $.parseJSON(localStorage.getItem(tablocalstorageName));
                    var roleId = roleDetails.id;
                    if(checkedIds==''){
                        var checkedCount =0;
                    }else{
                        var checkedCount =checkedIds.split(",").length;
                    }

                    //To show export settings popup
                    $.get( $(this).data('url') ,{'roleId':roleId, 'roleType':memberType, 'checkedCount':checkedCount }, function(data) {
                        FgModelbox.showPopup( data );
                    });
                    localStorage.setItem('checkedIds', checkedIds);
                break;
                case 'addnonexistingMembers':
                    FgActionmenuhandler.addTeammembers(ajaxUrl);
                    return false;
                break;
                case 'deleteDraftMessage':
                case 'deleteMessage':
                    var selected = 'selected';
                    if(!checkedIds) {
                        var listType = (actionType == "deleteDraftMessage") ? "draft" : "inbox";
                        checkedIds = fgMessageInbox.getAllMessageIdsInList(listType);
                        selected = 'all';
                    }
                    var isDraft = (actionType == "deleteDraftMessage") ? "1" : "0";
                    fgMessageInbox.showConfirmationPopup(checkedIds, selected, isDraft);
                    return false;
                break;
                case 'messageMarkAsRead':
                    if(!checkedIds) {
                        checkedIds = fgMessageInbox.getAllMessageIdsInList("inbox");
                    }
                    fgMessageInbox.markAsRead(checkedIds);
                    break;
                case 'messageMarkAsUnread':
                    if(!checkedIds) {
                        checkedIds = fgMessageInbox.getAllMessageIdsInList("inbox");
                    }
                    fgMessageInbox.markAsUnread(checkedIds);
                    break;
                case 'messageEnableNotification':
                case 'messageDisableNotification':
                    if(!checkedIds) {
                        checkedIds = fgMessageInbox.getAllMessageIdsInList("inbox");
                    }
                    var status = (actionType == "messageEnableNotification") ? "1" : "0";
                    fgMessageInbox.setNotification(checkedIds, status, true);
                    break;
                case 'teamloginstatus':
                case 'teamUserRight':
                    var localStorageName = $(this).attr('data-local-storage');
                    var roleDetails = $.parseJSON(localStorage.getItem(localStorageName));
                    var roleId = roleDetails['id'];
                    window.location = ajaxUrl+'/'+roleId;
                    break;
                case 'memberCreate':
                case 'memberEdit':
                    var localStorageName = $(this).attr('data-local-storage');
                    var roleDetails = JSON.parse(localStorage.getItem(localStorageName))
                    window.location = ajaxUrl.replace('roleId', roleDetails.id).replace('contactId', checkedIds);
                    break;
                case 'sendPersonalMessage':
                    var selectedIds = $.param({ mr: checkedIds.split(','),source : 'overview'});
                    window.location = ajaxUrl + '?' + selectedIds;
                    break;
                case 'removeMember':
                    var selected = 'selected';
                    var localStorageName = $(this).attr('data-local-storage');
                    var roleDetails = $.parseJSON(localStorage.getItem(localStorageName));
                    var type = roleDetails.type
                    var roleId = roleDetails.id
                    FgMemberList.removeMemberConfirmationPopup(checkedIds, selected, type, roleId );
                    return false;
                    break;
                case 'uploadDocument':
                    fgDocumentUploader.triggerUpload();
                    break;
                case 'documentdelete':
                    var selected = 'selected';
                    var localStorageName = $(this).attr('data-local-storage');
                    var roleDetails = $.parseJSON(localStorage.getItem(localStorageName));
                    var type = roleDetails.type;
                    var roleId = roleDetails.id;
                    FgTeamDocuments.deleteDocumentConfirmationPopup(checkedIds, selected, type, roleId );
                    break;
                case 'documentlog':
                case 'editdocument':
                    window.location = ajaxUrl.replace('documentId', checkedIds);
                    break;
                case 'documentmove':
                    assignData = {'dropMenuId': '','dropCategoryId': ''};
                    FgSidebar.showAssignPopup({
                        assignmentData: assignData,
                        subcategoryName: ''
                    });
                    break;
                case 'documentDownload':
                    window.open(ajaxUrl.replace('versionId', checkedIds), '_blank');
                    break;
                 case 'calendarDelete':
                    var deletedEvents = {};
                    var values = [];
                    var i = 0;
                    $('#calendarList .fg-first-col input:checked').each(function(d){
                        var data = parseInt($(this).val());
                        if(!_.contains(values,data)){
                            deletedEvents[parseInt(i)]= {'index':data};
                            i++;
                        }
                        values.push(data);
                    });
                    FgCalendarDelete.clickDelete(deletedEvents);
                    break;
                case 'calendarEdit':
                    var editEvents = {};
                    var values = [];
                    var i = 0;
                    $('#calendarList .fg-first-col input:checked').each(function(d){
                        var data = parseInt($(this).val());
                        var repeat = parseInt($(this).data('isrepeat'));
                        var eventId = parseInt($(this).data('eventdetid'));
                        if(!_.contains(values,data)){
                            editEvents[parseInt(i)]= {'index':data,'repeat':repeat,'eventId':eventId};
                            i++;
                        }
                        values.push(data);
                    });
                    if(_.size(editEvents)==1){
                        var detId = $('#calendarList .fg-first-col input:checked').attr('data-eventdetid');
                        var stDate = $('#calendarList .fg-first-col input:checked').attr('data-sdate');
                        var stTime = $('#calendarList .fg-first-col input:checked').attr('data-sTime');
                        var endDate = $('#calendarList .fg-first-col input:checked').attr('data-edate');
                        var endTime = $('#calendarList .fg-first-col input:checked').attr('data-eTime');
                        var editSingle = editSinglePath;
                        var editSingle = editSingle.replace('dummyId', detId);
                        var form = $('<form action="' + editSingle + '" method="post">' +
                            '<input type="hidden" name="startDate" id="startDate" value="'+stDate+'" />' +
                            '<input type="hidden" name="startTime" id="startTime" value="'+stTime+'" />' +
                            '<input type="hidden" name="endDate" id="endDate" value="'+endDate+'" />' +
                            '<input type="hidden" name="endTime" id="endTime" value="'+endTime+'" />' +
                            '</form>');
                        $('body').append(form);
                        $(form).submit();
                        break;
                    }
                    else if(_.size(editEvents)>1){
                        editEvents = JSON.stringify(editEvents)
                        editEvents = editEvents.replace(/"/g,"&quot;");
                        var calandarDatas = JSON.stringify(calandarData).replace(/"/g,"&quot;");
                        var form = $('<form action="' + editMultiEditPath + '" method="post">' +
                            '<input type="hidden" name="jsonRowId" id="jsonRowId" value="'+editEvents+'" />' +
                            '<input type="hidden" name="calandarData" id="calandarData" value="'+calandarDatas+'" />' +
                            '</form>');
                        $('body').append(form);
                        $(form).submit();
                    }
                    break;
                case 'duplicate':
                    var detId = $('#calendarList .fg-first-col input:checked').attr('data-eventdetid');
                    var stDate = $('#calendarList .fg-first-col input:checked').attr('data-sdate');
                    var stTime = $('#calendarList .fg-first-col input:checked').attr('data-sTime');
                    var endDate = $('#calendarList .fg-first-col input:checked').attr('data-edate');
                    var endTime = $('#calendarList .fg-first-col input:checked').attr('data-eTime');
                    var editDuplicate = editDuplicatePath;
                    var editDuplicate = editDuplicate.replace('dummyId', detId);
                    var form = $('<form action="' + editDuplicate + '" method="post">' +
                        '<input type="hidden" name="startDate" id="startDate" value="'+stDate+'" />' +
                        '<input type="hidden" name="startTime" id="startTime" value="'+stTime+'" />' +
                        '<input type="hidden" name="endDate" id="endDate" value="'+endDate+'" />' +
                        '<input type="hidden" name="endTime" id="endTime" value="'+endTime+'" />' +
                        '</form>');
                    $('body').append(form);
                    $(form).submit();
                    break;
                //gallery action menu functions
                case 'galleryUploadImage':  //Upload image to gallery
                    fgGalleryUploader.triggerUpload();
                    break;
                case 'gallerySorting':  //sort all items
                    var albumName = $('.page-title-text').text().replace( /[\s\n\r]+/g, ' ' ); //gallery title with white spaces trimmed
                    var params = {'currentSort' : 'NEWEST_TOP', 'albumName':albumName};
                    FgGalleryView.showConfirmationPopup('', '', 'CHANGE_SORT', params);
                break;
                case 'galleryAddVideo':     //Upload video to gallery
                    fgGalleryUploader.triggerVideoUpload();
                    break;
                case 'galleryEditDesc':     //Edit description
                    var checkedIds = '';
                    var splitter = '';
                    var itemCount = 0;
                    $( "div.fg-gallery-img-wrapper.selected" ).each(function( index ) {
                        if($(this).attr('data-itemid')) {
                            itemCount++;
                            checkedIds += splitter + $(this).attr('data-itemid');
                            splitter = ',';
                        }
                    });
                    fgGalleryUploader.triggerEditDesc(checkedIds, itemCount);
                    break;
                case 'galleryChangeScope':  //change scope of gallery
                case 'galleryRemove':       //remove image from gallery
                    var selected = 'selected';
                    var checkedIds = '';
                    var splitter = '';
                    var modalType = (actionType == 'galleryChangeScope') ? 'CHANGE_SCOPE' : 'REMOVE_IMAGE';
                    if(actionType == 'galleryChangeScope') {
                        var itemCount = 0;
                        var currentScope = '';
                        $( "div.fg-gallery-img-wrapper.selected" ).each(function( index ) {
                            if($(this).attr('data-itemid')) {
                                itemCount++;
                                checkedIds += splitter + $(this).attr('data-itemid');
                                currentScope = $(this).attr('data-scope');
                                splitter = ',';
                            }
                        });
                        var scope = (itemCount > 1) ? '' : currentScope ;
                        var params = {'currentScope' : scope};
                    } else {
                        var albumName = $('.page-title-text').text().replace( /[\s\n\r]+/g, ' ' ); //gallery title with white spaces trimmed
                        var params = { 'albumName': albumName };
                        $( "div.fg-gallery-img-wrapper.selected" ).each(function( index ) {
                            if($(this).attr('data-albumitemid')) {
                                checkedIds += splitter + $(this).attr('data-albumitemid');
                                splitter = ',';
                            }
                        });
                    }
                    FgGalleryView.showConfirmationPopup(checkedIds, selected, modalType, params);
                    return false;
                    break;

                case 'galleryMoveToAlbum':
                case 'galleryAssignToAlbum':
                    var checkedIds = '';
                    var splitter = '';
                    $( "div.fg-gallery-img-wrapper.selected" ).each(function( index ) {
                        if($(this).attr('data-itemid')) {
                            checkedIds += splitter + $(this).attr('data-itemid');
                            splitter = ',';
                        }
                    });
                    var modalType = (actionType == 'galleryMoveToAlbum') ? 'MOVETO_ALBUM' : 'ASSIGNTO_ALBUM';
                    var albumName = $('.page-title-text').text().replace( /[\s\n\r]+/g, ' ' ); //gallery title with white spaces trimmed
                    var params = { 'albumName': albumName };
                    FgGalleryView.showConfirmationPopup(checkedIds, 'selected', modalType, params);
                    return false;
                    break;

                case 'gallerySetCoverImage':     //Set Album cover image
                    var selected = 'selected';
                    var checkedIds = '';
                    var splitter = '';
                    var albumName = $('.page-title-text').text().replace( /[\s\n\r]+/g, ' ' ); //gallery title with white spaces trimmed
                        var params = { 'albumName': albumName };
                    $( "div.fg-gallery-img-wrapper.selected" ).each(function( index ) {
                        if($(this).attr('data-albumitemid')) {
                            checkedIds += splitter + $(this).attr('data-albumitemid');
                            splitter = ',';
                        }
                    });
                    FgGalleryView.showConfirmationPopup(checkedIds, selected,'SET_COVER_IMAGE', params);
                    return false;
                    break;
                case 'galleryItemDelete':
                    var selected = 'selected';
                    var checkedIds = '';
                    var splitter = '';
                    var modalType = 'DELETE_IMAGE' ;
                    var params = {};
                    $( "div.fg-gallery-img-wrapper.selected" ).each(function( index ) {
                        if($(this).attr('data-itemid')) {
                            checkedIds += splitter + $(this).attr('data-itemid');
                            splitter = ',';
                        }
                    });
                    FgGalleryView.showConfirmationPopup(checkedIds, selected, modalType, params);
                    return false;
                    break;
                case 'filemanagerUploadImage':
                    FgFileManagerUploader.triggerUpload();
                    break;
                case 'filedownload':
                    var selected = 'selected';
                    var source = '';
                    var modalType = $("#paneltab li.active").children().attr('data_id');
                    if((modalType === 'admin')||(modalType === 'users')|| (modalType === 'contact')){
                        var checkedIds = '';
                        var splitter = '';
                        var checkedSpans = $( "span.checked .fg-check-admintab" );
                        $(checkedSpans).each(function( index ) {
                            checkedIds += splitter + $(this).attr('filename');
                            source += splitter + $(this).attr('source');
                            splitter = ',';
                        });
                    }
                    var params = {};
                    FgListModuleFiles.showPopup(checkedIds, selected, modalType, params, source);
                    break;
                case 'filemanagerLog':
                   var url = ajaxUrl.replace('|id|', checkedIds);
                   var type = 'view';
                    $('#logform').remove();
                    var form = $('<form id = "logform" action="' + url + '" method="post">' +
                        '<input type="hidden" name="type" id="logtype" value="'+type+'" />' +
                        '</form>');
                    $('body').append(form);
                    $(form).submit();
                   // window.location = ajaxUrl.replace('|id|', checkedIds);
                    break;
                case 'filemanagerContentLog':
                   var url = ajaxUrl.replace('|id|', checkedIds);
                   var type = 'content';
                    $('#logform').remove();
                    var form = $('<form id = "logform" action="' + url + '" method="post">' +
                        '<input type="hidden" name="type" id="logtype" value="'+type+'" />' +
                        '</form>');
                    $('body').append(form);
                    $(form).submit();
                    break;
                case 'filemanagerDownloadZip':
                    var searchValue =  $( "#fg_dev_member_search" ).val();
                    FilemanagerDatatable.showDownloadZipPopup(checkedIds,searchValue);
                    return false;
                    break;
                case 'filemanagerDownload':
                    var virtualName = $('input.dataClass:visible:checked').attr('virtualname');
                    var downloadLink = fileInsertPath.replace("XXX", virtualName,'g');
                    var filedownloadLink = baseUrl+downloadLink;
                    window.open(filedownloadLink);
//                    window.location.href = filedownloadLink;
                    return false;
                    break;
                case 'filemanagerMarkDelete':
                     var searchValue =  $( "#fg_dev_member_search" ).val();
                    FilemanagerDatatable.markForDeletion(checkedIds,searchValue);
                    break;
                case 'filemanagerRestore':
                     var searchValue =  $( "#fg_dev_member_search" ).val();
                    FilemanagerDatatable.restoreFile(checkedIds,searchValue);
                    break;
               case 'filemanagerRename':
                    var fileName = $('input.dataClass:visible:checked').attr('filename');
                    FilemanagerDatatable.renamePopup(checkedIds, fileName);
                    return false;
                break;
                case 'filemanagerDelete':
                    FilemanagerDatatable.deleteFilePopup(checkedIds);
                    break;
               case 'filemanagerReplace':
                    FgFileManagerUploader.triggerReplace();
                break;
                case 'archiveArticle':
                 var articleDetails =  FgEditorialList.getArticleTitles(checkedIds);
                 FgArticleManage.archiveArticlePopup(articleDetails);
                break;
                case 'deleteArticle':
                 var articleDetails =  FgEditorialList.getArticleTitles(checkedIds);
                 FgArticleManage.deleteArticlePopup(articleDetails);
                break;
                case 'reactivateArticle':
                 var articleDetails =  FgEditorialList.getArticleTitles(checkedIds);
                 FgArticleManage.reactivateArticlePopup(articleDetails);
                break;
                case 'assignArticle' :
                   var selected = '';
                   var params = {};
                   FgArticleManage.showArticleAssignPopup(checkedIds, selected, params);
                break;
                case 'editArticle':
                    window.location = ajaxUrl.replace('ARTICLEIDREPLACE', checkedIds);
                    break;
                case 'duplicateArticle':
                    window.location = articleDuplicatePath.replace('dummyId', checkedIds);
                    break;
                case 'createPage':
                    FgCmsPage.showCreatePagePopup();
                    break;
                case 'editPage':
                    FgCmsPageList.editPage(checkedIds);
                    break;
                case 'previewPage':
                    $('#hidPageId').val(parseInt(checkedIds));
                    var pagetype = $('input#'+checkedIds).attr('data-pagetype');
                    var nav =  $('input#'+checkedIds).attr('data-nav');
                    $('#hidPageType').val(pagetype);
                     $('#hidNavId').val(nav);
                    
                    FgCmsPageList.showActionPagePreview();
                    break;
                case 'deletePage':
                    FgCmsPageList.deletePagePopup(checkedIds);
                    break;
                case 'unAssignPage':
                    FgCmsPage.showUnAssignPopup(checkedIds);
                    break;
                case 'deleteInquiry':
                    CmsInquiryList.showDeleteInquiryPopup(checkedIds);
                    break;
//                    export csv in form inquiries
                case 'exportCsv': 
                    CmsInquiryList.showExportPopup();
                    break;
                case 'exportInquiryAttachments':
                    CmsInquiryList.exportAttachments(checkedIds);
                    break;
               case 'fileedit':
                   var docType = $('input.dataClass:visible:checked').attr('data-doctype');
                   var docId = $('input.dataClass:visible:checked').attr('id');
                   switch (docType)
                   {
                    case 'CONTACT':
                        var editUrl = contactDocEditUrl.replace("|docId|", docId);
                        var type = 'files';
                        FgActionmenuhandler.documentcreateForm(editUrl,type);
                        break;
                    case 'TEAM':
                        var editUrl = teamDocEditUrl.replace("|docId|", docId);
                        var type = 'files';
                        FgActionmenuhandler.documentcreateForm(editUrl,type);
                        break;
                    case 'CLUB':
                        var editUrl = clubDocEditUrl.replace("|docId|", docId);
                        var type = 'files';
                        FgActionmenuhandler.documentcreateForm(editUrl,type);
                        break;
                    case 'WORKGROUP':
                        var editUrl = workgroupDocEditUrl.replace("|docId|", docId);
                        var type = 'files';
                        FgActionmenuhandler.documentcreateForm(editUrl,type);
                        break;
                      }
                    break;
                }
        });
     },
     addTeammembers: function(ajaxUrl){
       $.get(ajaxUrl, '', function(data) {
                FgModelbox.showPopup(data);
                FgActionmenuhandler.contactAutocomplete();
            });
     },
     contactAutocomplete:function(){
       var isItemSelected = 0;
       var autocompleteOptions = {};
       autocompleteOptions.minLength = 1;
       autocompleteOptions.formName = 'non_memberlist';
       autocompleteOptions.sendTitles = false;
       autocompleteOptions.removeButtonTitle = "ghgh"+' %s';
       autocompleteOptions.onItemSelected = function($obj, itemId, selected) {
           isItemSelected++;
           $('#nonmemberContacts').parent('div').parent('div').removeClass('fg-pop-up-search').addClass('fg-pop-up-no-search');
           $('#nonmemberContacts').attr('placeholder',  String.fromCharCode("0xf002"));
       };
       autocompleteOptions.onItemRemoved = function($obj, itemId) {
           isItemSelected--;
           if (isItemSelected <= 0) {
               $('#nonmemberContacts').parent('div').parent('div').removeClass('fg-pop-up-no-search').addClass('fg-pop-up-search');
               $('#nonmemberContacts').attr('placeholder', searchPlaceholder);
           }
       };
       autocompleteOptions.url = $('#nonmemberContacts').attr('auto_url');
       $('#nonmemberContacts').fbautocomplete(autocompleteOptions);

    },
      documentcreateForm: function(editUrl,type){
        localStorage.setItem("file_doc_edit_"+clubId+'_'+contactId, 0)
        $('#docEdit').remove();
        var form = $('<form id = "docEdit" action="' + editUrl + '" method="post">' +
            '<input type="hidden" name="doctype" id="doctype" value="'+type+'" />' +
            '</form>');
        $('body').append(form);
        $(form).submit();

     }

}
