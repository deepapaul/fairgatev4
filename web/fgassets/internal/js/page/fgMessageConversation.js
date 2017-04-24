var pageid = 1;
var count = 10;
var totalPages = Math.ceil(totalMessages / count);
window.checkFlag = 0;  //Flag checking to avoid repeatation on lazy loading 
$(function () {
    fgMessageWizardStep2.initMessageEditor();
    fgMessageWizardStep2.handleAttachementUploader($('#attachments'), 'btn-add-reply');
    fgMessageWizardStep2.initMessageEditorToggler();
    fgMessageWizardStep2.handleRemoveFile();

    
    $(".fg-reply-link-a").click(function () {
        var now = new Date(Date.now());        
        var currentTime = now.getHours() + ":" + (( now.getMinutes() < 10 ) ? "0" : "") + now.getMinutes();
        $('#current-time').html(currentTime);
        $('.fg-reply-link').addClass("hide");
        $('.fg-reply-area').removeClass("hide");
        $('.fg-reply-area').addClass("active")
        fgMessageConversation.removeErrorClass();
        fgMessageConversation.addClickEventForSend();
    });
    
    $(".fg-reply-cancel").click(function () {        
        fgMessageConversation.hideReplyArea()
        fgMessageConversation.removeErrorClass();
    });        

    $(window).scroll(function () {
        var wintop = $(window).scrollTop(), docheight = $(document).height(), winheight = $(window).height();
        var scrolltrigger = 0.95;
        if ((wintop / (docheight - winheight)) > scrolltrigger) {             
            if(pageid !== window.checkFlag) {
                window.checkFlag = pageid;
                fgMessageConversation.lastAddedLiveFunc();
            }            
        }
    });
    $(window).on('load', function () {
        fgMessageConversation.lastAddedLiveFunc();
        fgMessageConversation.readMessage(messageId, contactId);
    });
});

var fgMessageConversation = {
    /*Adding click event for send button */
    addClickEventForSend: function() {
        $("body").on('click', ".add-reply", function () {
            fgMessageConversation.removeErrorClass();
            $("body").off('click', ".add-reply");            
            fgMessageConversation.validateMessage();
        });
    },
    
    /* Method to validate editor field */
    validateMessage: function() {
        var message = CKEDITOR.instances.message.getData();
        if(message == '' || typeof message == "undefined" || ( $('.fg-replacewith-errormsg').find('.help-block').length > 0 ) ) {
            if(message == '' || typeof message == "undefined") {
                fgMessageConversation.showErrorClass();
            }
            fgMessageConversation.addClickEventForSend();
        } else {
            fgMessageConversation.replyToConversation();
        }
    },
    
    /* Method to add reply */
    replyToConversation: function () {
        //need to get the CK editor value and put it to the message element for proper using of FgXmlHttp.formPost()
        $('#message').val(CKEDITOR.instances.message.getData());
        var paramObj = {};
        paramObj.form = $('#submit_reply');
        paramObj.url = addReplyPath;
        paramObj.extradata = addReplyParams; 
        paramObj.successCallback = fgMessageConversation.callBackFn;
        paramObj.async = false;
        FgXmlHttp.formPost(paramObj);
    },
    
    /* Method to append messages on scrolling (lazy loading) */
    lastAddedLiveFunc: function () {
        if(pageid > totalPages) {
            return;
        }
       
        FgInternal.startPageLoading({'wrapperClass': '.fg-lazy-loader-wrapper'});
        pageid = (pageid <= 0) ? 1 : pageid;
        count = (count <= 0) ? 10 : count;
        var url = conversationListingUrl.replace("PAGE", pageid);
        url = url.replace("LIMIT", count);
        $.post(url, {'currentDateTime': currentDateTime}, function (data) {
            if (data != "") {
                //console.log(data);
                $.each(data, function (i, data) {                   
                    fgMessageConversation.renderResults('msgContent', 'timeline', {'data': data});
                    if(data.attachments){                        
                        var singleAttach = data.attachments;
                        fgMessageConversation.appendAttachment(singleAttach, data.id);                        
                    }
                });
            }
            $('div#lastPostsLoader').empty();
            pageid++;
            FgInternal.stopPageLoading();
        });
    },
    
    /* Method to remove error class */
    removeErrorClass: function() {
        $('.fg-reply-area').removeClass("has-error");
        $('.fg-dev-errorblock').addClass("hide");
    },
    
    /* Method to show error class */
    showErrorClass: function() {
        $('.fg-reply-area').addClass("has-error");
        $('.fg-dev-errorblock').removeClass("hide");
    },
    
    /* Method to load the ajax results into underscore template */
    renderResults: function (templateScriptId, parentDivClass, data) {
        var template = $('#' + templateScriptId).html();            
        var result_data = _.template(template, data);
        $('.' + parentDivClass).append(result_data);
    }, 
    
    /* Method to hide reply area */
    hideReplyArea: function() {
        $('.fg-reply-link').removeClass("hide");
        $('.fg-reply-area').addClass("hide");
        $('.fg-reply-area').removeClass("active");
    },
        
    /* Method to append reply after added */
    appendReply: function(templateScriptId, data) {
        var template = $('#' + templateScriptId).html();            
        var result_data = _.template(template, data);
        $('ul li.fg-reply-area').after(result_data);
    },
    
    /* Call back function */
    callBackFn: function(data) {        
        CKEDITOR.instances.message.setData("");
        $('#file-uploaded-attachements').html("");  
        fgMessageConversation.hideReplyArea();
        if(data.responseText.noreload) {
            fgMessageConversation.appendReply('replyContent', data.responseText);           
            if(data.responseText.attachments){                        
                var singleAttach = data.responseText.attachments;                
                fgMessageConversation.appendAttachment(singleAttach, data.responseText.dataId);
            }
        }        
    },
    
    /*Append attachment to message*/
    appendAttachment: function(singleAttach, dataId) {
        singleAttachs = singleAttach.split(",");
        $.each(singleAttachs, function (i, singleAttach) {                    
            attachmentNames = singleAttach;
            downloadPath = attachmentDownloadPath.replace("FILENAME", attachmentNames);
            var attachContent = '<a href="'+downloadPath+'" class="fg-attachment"><i class="fa fa-paperclip"></i>'+attachmentNames+'</a>';
            $(".fg-attachment-wrapper-"+dataId).append(attachContent);
        });
    },
    
    /*Method to set read flag for message*/
    readMessage: function(messageId, contactId) {
        params = {'messageId': messageId, 'contactId': contactId };
        $.post(pathSetRead, params, function (response) {                      
        });
    }
    
};