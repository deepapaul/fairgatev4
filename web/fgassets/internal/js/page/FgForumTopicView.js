 /**
  * Handle forum topic view
  */
$(function () {
    $(".fg-action-menu-wrapper").FgPageTitlebar({
        row1 : false,
        row2 : true
    });
    
    //if unfollow is not null show toaster 'unfollowed topic'
    if( unfollow != '') {
        FgInternal.showToastr(UnfollowText);
    }
    
    FgFormTools.handleUniform();
    FgFormTools.handleSelect2();
    FgForumTopicView.initPageFunctions();
    ForumCkEditor.init();
    FgForumTopicView.ckEditorReplace(textareaName, true, false);
    FgForumTopicView.disableReplybuttons();     
    FgForumTopicView.addClickEventForSaveReply();
    FgForumTopicView.clickPreview();
    FgForumTopicView.handleEditPost();
    FgForumTopicView.handleQuote();
    FgForumTopicView.handleSettingMenu();

    FgForumTopicView.handleFollow();
    FgForumTopicView.handleDelete();    

    $('.fg-badge-important').hide();
    $('.fg-badge-closed').hide();
    $('.fg-forum-no-reply').hide();
    $('.fg-active-IB .fg-post-delete').hide();
    $('.fg-follow').hide();
    $('.fg-unfollow').hide();
    $('.internal-sticky-area').addClass('exclude-sticky');
    if(isClosed == 1) {
        $('.fg-topic-clo').attr('checked', true);
        jQuery.uniform.update('.fg-topic-clo');
        $('.fg-topic-imp').attr('disabled', true);
        jQuery.uniform.update('.fg-topic-imp');
        $('.fg-settings-menu').attr('disabled', true);
        $('.fg-badge-closed').show();
        $('.fg-badge-important').hide();
        $('.fg-active-IB .fg-post-delete').show();
    }
    else if(isImportant == 1) {
        $('.fg-topic-imp').attr('checked', true);
        jQuery.uniform.update('.fg-topic-imp');
        $('.fg-badge-important').show();
        $('.fg-badge-closed').hide();
    }
    if(isFollower == 1) {
        $('.fg-unfollow').show();
        $('.fg-follow').hide();
    }
    else {
        $('.fg-follow').show();
        $('.fg-unfollow').hide();
    }
    if(isDeactivated == 1)
    {
        $('.follow-unfollow-block').hide();
    }
    if(isRepliesAllowed == 1) {
        $('.fg-settings-menu').select2('val', 'allowed');
    }
    else {
        $('.fg-settings-menu').select2('val', 'not_allowed');
        $('.fg-post-addnew-reply').hide();
        $('.fg-post-quote').hide();
        $('.fg-forum-no-reply').show();
    }
});
var Previewflag = 0;
var ckeditorInstanceName = textareaName;
var FgForumTopicView = {
    
    //for listing
    initPageFunctions: function() {       
        var data = [];
        data['page'] = page;
        data['topicDetails'] = topicDetails;
        data['totalCnt'] = totalCnt;
        data['contactId'] = contactId;
        data['isAdmin'] = isAdmin;
        data['dpp'] = dpp;
        data['imgPath'] = imgPath;

        FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
        var htmlFinal = FGTemplate.bind('topicViewTemplate', data);
        $('#topicViewContent').html(htmlFinal);
        if(isRepliesAllowed == "1" && isClosed != "1") {
            FgForumTopicView.showReplyArea();
        }
        
        FgForumTopicView.handleScrollToPost();
   
        $('body').on('click', '.fg-dev-posts-pagination li a', function() {
            var curPage = $(this).attr('data-page');
            if (curPage != page) {
                FgForumTopicView.loadPage(curPage);  
                page = curPage;
            }
            return false;
        });
        
        $('body').on('click', '.fg-post-addnew-reply', function() {
            CKEDITOR.instances[textareaName].insertText('');
            var pos = $("#save_changes").offset().top;
            $('html, body').animate({
                scrollTop: pos
            }, 1000);
        });
    },
    
    handleSettingMenu: function() {
        $('body').on('change', '.fg-settings-menu', function() {
            var repliesData = $('.fg-settings-menu').select2('data').id;
            var changeRepliesUrl = repliesPath;
            var changeRepliesUrl = changeRepliesUrl.replace('|repliesData|', repliesData);
            $.post(changeRepliesUrl, {'topicId': topicId, 'repliesData': repliesData}, function(data) {
                if(repliesData == 'allowed') {
                    $('.fg-forum-no-reply').hide();
                    isRepliesAllowed = 1;
                    $('.fg-post-quote').show();
                    $('.fg-post-addnew-reply').show();
                    FgForumTopicView.showReplyArea();
                }
                else {
                    $('.fg-forum-no-reply').show();
                    isRepliesAllowed = 0;
                    $('.fg-post-quote').hide();
                    $('.fg-post-addnew-reply').hide();
                    FgForumTopicView.hideReplyArea();
                }
                FgInternal.showToastr(data);
            });
        });
        
        $('body').on('click', '.fg-topic-imp', function(){
            if($(this).is(':checked')){
                var forumSettingUrl = settingsMenuChkPath;
                forumSettingUrl = forumSettingUrl.replace('|topicId|', topicId);
                forumSettingUrl = forumSettingUrl.replace('|checkedVal|', 1);
                forumSettingUrl = forumSettingUrl.replace('|chkType|', 'isImportant');
                $.post(forumSettingUrl, {'topicId' : topicId, 'checkedVal': 1, 'chkType': 'isImportant'}, function(data) {
                    $('.fg-topic-clo').attr('disabled', false);
                    jQuery.uniform.update('.fg-topic-clo');
                    $('.fg-badge-important').show();
                    FgInternal.showToastr(data);
                });
            }
            else {
                var forumSettingUrl = settingsMenuChkPath;
                forumSettingUrl = forumSettingUrl.replace('|topicId|', topicId);
                forumSettingUrl = forumSettingUrl.replace('|checkedVal|', 0);
                forumSettingUrl = forumSettingUrl.replace('|chkType|', 'isImportant');
                $.post(forumSettingUrl, {'topicId' : topicId, 'checkedVal': 0, 'chkType': 'isImportant'}, function(data) {
                    $('.fg-topic-clo').attr('disabled', false);
                    jQuery.uniform.update('.fg-topic-clo');
                    $('.fg-badge-important').hide();
                    FgInternal.showToastr(data);
                });
            }
        });
        
        $('body').on('click', '.fg-topic-clo', function(){
            if($(this).is(':checked')){ 
                var forumSettingUrl = settingsMenuChkPath;
                forumSettingUrl = forumSettingUrl.replace('|topicId|', topicId);
                forumSettingUrl = forumSettingUrl.replace('|checkedVal|', 1);
                forumSettingUrl = forumSettingUrl.replace('|chkType|', 'isClosed');
                $.post(forumSettingUrl, {'topicId' : topicId, 'checkedVal': 1, 'chkType': 'isClosed'}, function(data) {
                    $('.fg-topic-imp').attr('disabled', true);
                    $('.fg-topic-imp').attr('checked', false);
                    jQuery.uniform.update('.fg-topic-imp');
                    $('.fg-settings-menu').select2('val', 'not_allowed');
                    $('.fg-forum-no-reply').show();
                    isRepliesAllowed = 0;
                    $('.fg-post-quote').hide();
                    $('.fg-settings-menu').attr('disabled', true);
                    $('.fg-badge-closed').show();
                    $('.fg-badge-important').hide();
                    $('.fg-active-IB .fg-post-delete').show();
                    $('.fg-post-addnew-reply').hide();
                    FgInternal.showToastr(data);
                    FgForumTopicView.hideReplyArea();
                });
            }
            else {
                var forumSettingUrl = settingsMenuChkPath;
                forumSettingUrl = forumSettingUrl.replace('|topicId|', topicId);
                forumSettingUrl = forumSettingUrl.replace('|checkedVal|', 0);
                forumSettingUrl = forumSettingUrl.replace('|chkType|', 'isClosed');
                $.post(forumSettingUrl, {'topicId' : topicId, 'checkedVal': 0, 'chkType': 'isClosed'}, function(data) {
                    $('.fg-topic-imp').removeAttr('disabled');
                    jQuery.uniform.update('.fg-topic-imp');
                    $('.fg-settings-menu').select2('val', 'allowed');
                    $('.fg-settings-menu').attr('disabled', false);
                    $('.fg-forum-no-reply').hide();
                    isRepliesAllowed = 1;
                    $('.fg-post-quote').show();
                    $('.fg-post-addnew-reply').show();
                    $('.fg-badge-closed').hide();
                    $('.fg-active-IB .fg-post-delete').hide();
                    FgInternal.showToastr(data);
                    FgForumTopicView.showReplyArea();
                });
            }
        });
    },
    
    handleEditPost: function() {        
        //for handling edit reply
        $('body').on('click', '.fg-post-edit', function() {
            dataId = $(this).attr("id");
            $('#forum-content-'+dataId).find('.fg-forum-post-content').addClass("hide");
            $('#forum-content-'+dataId).find('.fg-forum-post-content-edit').removeClass("hide");
            $('#forum-content-'+dataId).find('.fg-post-edit-save').removeClass("hide"); 
            $('#forum-content-'+dataId).find('.fg-post-edit-cancel').removeClass("hide"); 
            $('#forum-content-'+dataId).find('.fg-post-edit').addClass("hide");            
            FgForumTopicView.hideReplyEditorError(dataId);
            $('#forum-post-'+dataId).val($('#forum-content-'+dataId).find('.fg-forum-post-content').html());             
            FgForumTopicView.ckEditorReplace('forum-post-'+dataId, false, true);   
           
            return false;
        }); 
        
        //for handling edit reply save
        $('body').on('click', '.fg-post-edit-save', function() {
            dataId = $(this).attr("data-id");
            var postContent = bbcodeParser.bbcodeToHtml($('#forum-post-'+dataId).text()).replace(/(<(?!img)([^>]+)>)/ig,"");     
            if (postContent == '') { // Setting validation flag if there is any errors
                $('#forum-content-'+dataId).find('.fg-forum-post-content-edit').addClass("has-error");
                $('#forum-content-'+dataId).find('.fg-dev-errorblock').removeClass("hide");
            } else {                                
                var content = bbcodeParser.bbcodeToHtml($('#forum-post-'+dataId).text());
                $('#forum-content-'+dataId).find('.fg-forum-post-content').html(content);
                uniqueId = $('#forum-content-'+dataId).find('a.fa-post-count').html().replace("#", "");                
                FgXmlHttp.post(pathEditTopicReply, {'content': content,'topicId': topicId, 'dataId' : dataId, 'uniqueId' : uniqueId} , false, FgForumTopicView.postEditCallBack);
            }
            
            return false;
        });
        
        //for handling edit reply cancel
        $('body').on('click', '.fg-post-edit-cancel', function() {
            dataId = $(this).attr("data-id");
            FgForumTopicView.hideReplyEditor(dataId);
            
            return false;
        });

    },
    
    handleDelete: function() {
        $('body').on('click', '.fg-post-delete', function(){
            var topicContentId = $(this).attr('data-id');
            var type = $(this).attr('data-type');
            var uniqueId = $(this).attr('data-unique-id');
            var deletePopupConfirmationPath = delTopicPath;
            deletePopupConfirmationPath = deletePopupConfirmationPath.replace('|topicContentId|', topicContentId);
            deletePopupConfirmationPath = deletePopupConfirmationPath.replace('|type|', type);
            $.post(deletePopupConfirmationPath, {'topicContentId': topicContentId, 'type': type,'uniqueid':uniqueId}, function(data) {
                FgModelbox.showPopup(data);
            });
        });
    },
    
    handleFollow: function() {
        $('body').on('click', '.fg-unfollow', function(){
            var forumFollowUrl = followUnFollowPath;
            forumFollowUrl = forumFollowUrl.replace('|topicId|', topicId);
            forumFollowUrl = forumFollowUrl.replace('|followVal|', 0);
            $.post(forumFollowUrl, {'topicId' : topicId, 'followVal': 0}, function(data) {
                $('.fg-follow').show();
                $('.fg-unfollow').hide();
                FgInternal.showToastr(data);
            });
        });
        $('body').on('click', '.fg-follow', function(){
            var forumFollowUrl = followUnFollowPath;
            forumFollowUrl = forumFollowUrl.replace('|topicId|', topicId);
            forumFollowUrl = forumFollowUrl.replace('|followVal|', 1);
            $.post(forumFollowUrl, {'topicId' : topicId, 'followVal': 1}, function(data) {
                $('.fg-follow').hide();
                $('.fg-unfollow').show();
                FgInternal.showToastr(data);
            });
        });
    },        
    
    handleQuote: function() {
        $('body').on('click', '.fg-post-quote', function() {
            dataId = $(this).attr("data-id");
            var postedByText = $('#forum-content-'+dataId).find('.fg-dev-forum-posted-by').html();
            var postedOnText = $('#forum-content-'+dataId).find('.fg-dev-forum-posted-on').html();
            var contentText = $('#forum-content-'+dataId).find('.fg-forum-post-content').html();
            var quoteText = '<blockquote><p>' + postedByText + ' ' + wroteOnText + ' ' + postedOnText + ':</p>' + contentText + '</blockquote><br />';
            if (CKEDITOR.instances[ckeditorInstanceName]) {
                CKEDITOR.instances[ckeditorInstanceName].insertHtml(quoteText);
            } else {
                CKEDITOR.instances[textareaName].insertHtml(quoteText);
            }
            return false;
        });
    },
    
    //call back after edit post
    postEditCallBack: function(data) {
        dataId = (data.returnArray.dataId);
        FgForumTopicView.hideReplyEditor(dataId);
        FgForumTopicView.hideReplyEditorError(dataId);
        var editText = editedByText+" "+data.returnArray.updatedBy+" "+editedOnText+" "+data.returnArray.updatedDate;
        $('#forum-content-'+dataId).find('.fg-forum-post-edit').html(editText);
        $('#forum-content-'+dataId).find('.fg-forum-post-edit').removeClass("hide");
    },
    
    hideReplyEditor: function(dataId) {
        $('#forum-content-'+dataId).find('.fg-forum-post-content').removeClass("hide");
        $('#forum-content-'+dataId).find('.fg-forum-post-content-edit').addClass("hide");
        $('#forum-content-'+dataId).find('.fg-post-edit-save').addClass("hide");  
        $('#forum-content-'+dataId).find('.fg-post-edit-cancel').addClass("hide");  
        $('#forum-content-'+dataId).find('.fg-post-edit').removeClass("hide");
    },
    
    hideReplyEditorError: function(dataId) {
        if(CKEDITOR.instances['forum-post-'+dataId]){
            CKEDITOR.instances['forum-post-'+dataId].destroy();  
        }
        $('#forum-content-'+dataId).find('.fg-forum-post-content-edit').removeClass("has-error");
        $('#forum-content-'+dataId).find('.fg-dev-errorblock').addClass("hide");

    },
    
    /**
     * replce with ckeditor
     * @param {string} textareaName - fieldname
     * @param {boolean} replyArea
     * @param {boolean} autofocus
     */
    ckEditorReplace:function (textareaName, replyArea, autofocus){
        CKEDITOR.replace(textareaName, {
            toolbar: advancedToolsArr,
            language :locale
        }).on('change',function(){
            if(replyArea) {
                FgForumTopicView.addToTextArea();
            } else {
                $('#'+textareaName).html(CKEDITOR.instances[textareaName].document.getBody().getHtml());
            }           
        });
        CKEDITOR.instances[textareaName].on('focus', function(){
            ckeditorInstanceName = textareaName;
        });
        
        //To focus cursor in correct position // only for edit
        if(autofocus) {
            CKEDITOR.instances[textareaName].on('instanceReady', function(ev) {
                FgForumTopicView.ckeditorFocus(ev.editor);
            });
        }
        CKEDITOR.instances[textareaName].addContentsCss('/fgassets/global/css/fg-ckeditor.css');                
    },
    
    addToTextArea: function() {        
        if(CKEDITOR.instances[textareaName].getData() != ''){                 
            $('#reset_changes').attr('disabled',false);
            $('#save_changes').attr('disabled',false);
            $('#forum-post-text').html(CKEDITOR.instances[textareaName].document.getBody().getHtml());            
        } else{         
            FgForumTopicView.disableReplybuttons();            
            $('#forum-post-text').html('');            
        }
    },
    
//    resetChanges: function(){
//        $('#reset_changes').click(function() {
//            FgForumTopicView.handleResetChanges();
//        });
//    },

    addClickEventForSaveReply: function() {
        $("body").on('click', "#save_changes", function () {
            $("body").off('click', "#save_changes");
            FgForumTopicView.saveChanges();
        });
    },
    
    //save reply function
    saveChanges: function(){
        var validation = 0;
        //var postContent = bbcodeParser.bbcodeToHtml(CKEDITOR.instances[textareaName].document.getBody().getHtml()).replace(/(<(?!img)([^>]+)>)/ig,"");
        var postContent = bbcodeParser.bbcodeToHtml($('#forum-post-text').text()).replace(/(<(?!img)([^>]+)>)/ig,""); 
        if (postContent == '') { // Setting validation flag if there is any errors
            validation = 1;
            $('.fg-forum-topic-content-wrapper').addClass("has-error");
            $('.fg-dev-errorblock').removeClass("hide");
            FgForumTopicView.addClickEventForSaveReply();
        } else {
            $('.fg-forum-topic-content-wrapper').removeClass("has-error");
            $('.fg-dev-errorblock').addClass("hide");
        }

        if (validation == 0) { // Checking validation. If any, should display error message
            //convert bbcode if any to html
            var bbcodeTohtml = bbcodeParser.bbcodeToHtml($('#forum-post-text').text());
            $('#forum-post-text').val(bbcodeTohtml);                
            jsonArray = {"forum-post-text":bbcodeTohtml};                
            var forumArr = JSON.stringify(jsonArray);                 
            //increment total count of post
            totalCnt = totalCnt + 1;
            FgXmlHttp.post(pathSaveTopicReply, {'postArr': forumArr,'topicId': topicId, 'role' : roleId, 'grpType' : module} , false, FgForumTopicView.saveReplyCallBack);
        } 
    },
    
    //call back function after save reply
    saveReplyCallBack: function(data) {
        if(data) {            
            FgForumTopicView.reloadLastPage();              
            if(CKEDITOR.instances[textareaName]){
                CKEDITOR.instances[textareaName].destroy();  
            }
            FgForumTopicView.hidePreview();
            FgForumTopicView.addClickEventForSaveReply();
            FgForumTopicView.handleResetChanges(); 
        }
    },
    
   
    deleteTopicContent: function(topicContentId, deletePath, type){
        FgModelbox.hidePopup();
        $.post(deletePath, {'topicContentId': topicContentId, 'type': type}, function(data) {
            if(data.type == "content") {
                //$('.forum-topic-content-'+topicContentId).hide();
                totalCnt = totalCnt - 1;
                FgForumTopicView.reloadLastPage();
                FgInternal.showToastr(data.message);                
            }
            else if(data.type == "forum") {
                FgInternal.showToastr(data.message);
                window.location.replace(delRedirectPath);
            }
        });
    },
    
    showReplyArea: function() {
        $('#topicReply').removeClass("hide"); 
    },
    
    hideReplyArea: function() {
        $('#topicReply').addClass("hide"); 
    },
    
    //handle pagination
    loadPage: function(curPage) {
        var dataUrlTemp = dataUrl.replace('|page|', curPage);
        $.getJSON(dataUrlTemp, null, function(data) {
            var htmlContent = FGTemplate.bind('topicViewTemplate', data);
            $('#topicViewContent').html(htmlContent);
            if(isRepliesAllowed == "1") {
                $('.fg-post-quote').show();
            }
            else {
                $('.fg-post-quote').hide();
            }
        });
    },
    
    reloadLastPage: function() {
        var totalPages = ((totalCnt%dpp) != 0) ? (parseInt(totalCnt/dpp) + 1) : (totalCnt/dpp);
        FgForumTopicView.loadPage(totalPages);
    },
    
    handleResetChanges: function() {
        CKEDITOR.instances[textareaName].setData('');
        FgForumTopicView.disableReplybuttons();        
    },
    
    disableReplybuttons: function() {
        $('#reset_changes').attr('disabled',true);
        $('#save_changes').attr('disabled',true);        
    },
    
//  scroll to correct post
    handleScrollToPost : function() {
        var uniqueId = window.location.hash;
        if (uniqueId) {   
            uniqueId = uniqueId.substring(1);
            if ($('div[data-unique-id="'+ uniqueId +'"]').length > 0) {
                var pos = $('div[data-unique-id="'+ uniqueId +'"]').offset().top - 70;
                $('html, body').animate({
                    scrollTop: pos
                }, 1000);
            }
        }
    },
    
    //show preview function
    clickPreview :function(){
        $('#reset_changes').click(function(){
            if(Previewflag == 0){                
                FgForumTopicView.showPreview();
            } else {
                FgForumTopicView.hidePreview();
                //FgForumTopicView.ckeditorFocus(CKEDITOR.instances[textareaName]); 
            }
        });
    },
    
    showPreview: function() {
        Previewflag = 1;                
        $('.fg-forum-reply-editor').addClass('hide');
        var bbcodeTohtml = bbcodeParser.bbcodeToHtml(CKEDITOR.instances[textareaName].document.getBody().getHtml());
        $('#preview-text').removeClass('hide');
        $('#preview-text').html(bbcodeTohtml);                
        $('#reset_changes').text(editText);   
        if(CKEDITOR.instances[textareaName]){
            CKEDITOR.instances[textareaName].destroy();  
        }
    },
    
    hidePreview: function() {
        Previewflag = 0;
        $('.fg-forum-reply-editor').removeClass('hide');          
        FgForumTopicView.ckEditorReplace(textareaName, true, true);        
        $('#preview-text').addClass('hide');
        $('#reset_changes').text(previewText);                                
    },
    
    //focus at the end of text in the editor
    ckeditorFocus: function(editorInstance) {
        editorInstance.focus();
        var s = editorInstance.getSelection(); // getting selection
        var selected_ranges = s.getRanges(); // getting ranges
        var node = selected_ranges[0].startContainer; // selecting the starting node
        var parents = node.getParents(true);

        node = parents[parents.length - 2].getFirst();

        while (true) {
            var x = node.getNext();
            if (x == null) {
                break;
            }
            node = x;
        }

        s.selectElement(node);
        selected_ranges = s.getRanges();
        selected_ranges[0].collapse(false);  //  false collapses the range to the end of the selected node, true before the node.
        s.selectRanges(selected_ranges); 
        editorInstance.insertHtml('');
    }
}
