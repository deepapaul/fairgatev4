var FgCmsArticleDetails = (function () {
    function FgCmsArticleDetails() {
        this.settings = '';
        this.defaultSettings = {
            articleId: '',
            articleContentDiv: '#article-content',
            articleCommentDiv: '#article-comment',
            articleTemplateId: 'templateArticleDetails',
            articleTemplateMediaId: 'templateArticleMedia',
            articleTemplateAttachmentsId: 'templateArticleAttachments',
            articleTemplateSettingsId: 'templateArticleSettings',
            articleTemplateTextId: 'templateArticleText',
            articleTemplateLinkAndAttachmentId: 'templateArticleLinkAndAttachments',
            articleTemplateSliderId: 'templateArticleSlider',
            commentsTemplateId: 'templateArticleComments',
            articleDataUrl: '',
            articleCommentsUrl: '',
            encodedString: ''
        };
    }
    FgCmsArticleDetails.prototype.init = function (options) {
        this.initSettings(options);
    };
    FgCmsArticleDetails.prototype.initSettings = function (options) {
        this.settings = $.extend(true, {}, this.defaultSettings, options);
    };
    FgCmsArticleDetails.prototype.renderArticleData = function () {
        this.renderArticle();
    };
    FgCmsArticleDetails.prototype.handleComments = function () {
        this.saveComments();
    };
    FgCmsArticleDetails.prototype.renderArticle = function () {
        var settingsObj = this.settings;
        var thisObj = this;
        var data = articleData;
        if (_.size(data) > 0) {
            var mediaContent = FGTemplate.bind(settingsObj.articleTemplateMediaId, { ArticleData: data });
            var textContent = FGTemplate.bind(settingsObj.articleTemplateTextId, { ArticleData: data });
            var linkContent = FGTemplate.bind(settingsObj.articleTemplateLinkAndAttachmentId, { ArticleData: data });
            var sliderContent = FGTemplate.bind(settingsObj.articleTemplateSliderId, { ArticleData: data });
            var content = { media: mediaContent, text: textContent, link: linkContent, slider: sliderContent };
            var templateId = settingsObj.articleTemplateId;
            content = FGTemplate.bind(templateId, { ArticleData: data, content: content });
            $(settingsObj.articleContentDiv).html(content);
            if (data.article.media.position == "right_column" || data.article.media.position == "left_column") {
                if (_.size(data.article.media) > 1) {
                    thisObj.initImageList();
                }
            }
            else {
                if (_.size(data.article.media) > 1) {
                    thisObj.initSlider();
                }
            }
        }
    };
    FgCmsArticleDetails.prototype.renderArticleComments = function () {
        var settingsObj = this.settings;
        $.ajax({
            type: 'POST',
            url: settingsObj.articleCommentsUrl,
            data: { articleId: settingsObj.articleId },
            success: function (data) {
                if (data.globalCommentAccess == 1) {
                    content = FGTemplate.bind(settingsObj.commentsTemplateId, { commentDetails: data });
                    $(settingsObj.articleCommentDiv).html(content);
                    if ($('#fg-captcha').length > 0) {
                        grecaptcha.render('fg-captcha', {
                            'sitekey': sitekey,
                            'callback': function (response) {
                                $('#save_changes').removeAttr('disabled');
                            }
                        });
                    }
                }
            }
        });
    };
    FgCmsArticleDetails.prototype.initSlider = function () {
        $("#fg-article-gallery").unitegallery({
            gallery_theme: "slider",
            slider_textpanel_enable_title: false,
            slider_textpanel_enable_description: true,
            slider_enable_bullets: false,
            slider_control_zoom: false,
            slider_enable_text_panel: true,
            slider_textpanel_enable_bg: true,
            slider_textpanel_padding_top: 0,
            slider_textpanel_padding_bottom: 0,
        });
    };
    FgCmsArticleDetails.prototype.initImageList = function () {
        $("#fg-article-gallery").unitegallery({
            tile_enable_shadow: false,
            tile_enable_border: false,
            slider_enable_bullets: false,
            tiles_space_between_cols: 15,
            tiles_justified_space_between: 15,
            tiles_col_width: 235,
            tile_enable_outline: true,
            tile_enable_textpanel: true,
            gallery_theme: "tiles",
            tiles_min_columns: 1,
            tiles_max_columns: 1,
            grid_num_rows: 30,
            gallery_width: "100%",
            gallery_min_width: 75,
            tile_textpanel_padding_top: 0,
            tile_textpanel_padding_bottom: 0
        });
    };
    FgCmsArticleDetails.prototype.saveComments = function () {
        var _this = this;
        $('body').on('keyup', '#comment-text-area_new', function () {
            if ($(this).val() != '') {
                $('#save_changes').removeAttr('disabled');
            }
            else {
                $('#save_changes').attr('disabled', 'disabled');
            }
        });
        $('body').on('click', '.comment_save', function (e) {
            e.preventDefault();
            var validationFlag = 0;
            var FgXmlHttp = FgXmlHttp;
            var commentId = $(this).attr("data-id");
            var articleId = $(this).attr("data-articleId");
            var guestContactName = (isGuestContact) ? $("#guest-user").val().trim() : '';
            var comment = $('#comment-text-area_' + commentId).val();
            var postContent = bbcodeParser.bbcodeToHtml(comment).replace(/(<(?!img)([^>]+)>)/ig, "");
            var dataArray = { 'comment': postContent, 'commentId': commentId, 'articleId': articleId, 'guestContactName': guestContactName };
            if (isGuestContact && guestContactName == '') {
                validationFlag = 1;
                $('#guestuser-error-block').removeClass('hide');
                $('#fg-dev-guestuser-block').addClass('has-error');
            }
            if (postContent.trim() == '') {
                validationFlag = 1;
            }
            if (validationFlag == 0) {
                $.ajax({
                    type: 'POST',
                    url: commentSavePath,
                    data: dataArray,
                    success: function (data) {
                        _this.saveCallBack(isGuestContact, guestContactName, postContent.replace(/\r?\n/g, '<br />'), data);
                    }
                });
            }
        });
        $('body').on('keyup', '#guest-user', function () {
            $('#guestuser-error-block').addClass('hide');
            $('#fg-dev-guestuser-block').removeClass('has-error');
        });
    };
    FgCmsArticleDetails.prototype.saveCallBack = function (isGuestContact, guestContactName, postContent, data) {
        var commentHtml = this.createCommentHtml(isGuestContact, guestContactName, postContent, data);
        $(".fg-web-article-comments-list").prepend(commentHtml);
        $("#comment-text-area_new").val('');
        $("#guest-user").val('');
        $('#save_changes').attr('disabled', 'disabled');
        var commentCountString = $("#comments-count").text();
        var commentCountVal = parseInt(commentCountString) + 1;
        $("#comments-count").html(commentCountVal);
        if (commentCount == 0) {
            if ($("#fg-link-comments-count").is(':visible')) {
                var linkcmntCount = $("#fg-link-comments-count strong").text();
                var linkcmntCountVal = parseInt(linkcmntCount) + 1;
                $("#fg-article-comment-text strong").html(transArray.commentMultiple);
                $("#fg-link-comments-count strong").html(linkcmntCountVal);
            }
            else {
                $('<p class="fg-comments-count"><span id="fg-link-comments-count"> <strong>1</strong> </span> <span id="fg-article-comment-text"><strong>' + transArray.commentSingle + '</strong></span></p>').insertBefore($(".fg-cat-tags"));
            }
        }
        else {
            $("#fg-link-comments-count strong").html(commentCountVal);
        }
    };
    FgCmsArticleDetails.prototype.createCommentHtml = function (isGuestContact, guestContactName, postContent, data) {
        var commentHtml = '';
        var commentedContact = '';
        var date = data.date;
        commentHtml += '<li class="fg-web-article-comment-block">';
        if (isGuestContact) {
            commentHtml += '<div class="fg-avatar"><div></div></div>';
        }
        else if (contactImage) {
            commentHtml += '<div class="fg-avatar"> <div class="fg-profile-img-blk45 fg-round-img" style="background-image:url(\'' + contactImage + '\')"></div> </div>';
        }
        else {
            commentHtml += '<div class="fg-avatar"><div class="fg-profile-img-blk45 fg-round-img"></div></div>';
        }
        if (isGuestContact) {
            commentedContact = guestContactName;
        }
        else {
            commentedContact = contactName;
        }
        commentHtml += '<div class="fg-content-wrapper">';
        commentHtml += '<div class="fg-comment-details">';
        commentHtml += '<p><strong class="fg-author">' + commentedContact + '</strong> on ' + date + '</p>';
        commentHtml += '</div>';
        commentHtml += '<div class="fg-comment" id="comment-text-content" >';
        commentHtml += postContent;
        commentHtml += '</div>';
        commentHtml += '</div>';
        commentHtml += '</li>';
        return commentHtml;
    };
    FgCmsArticleDetails.prototype.downloadAttachment = function () {
        $(document).on('click', '.fg-article-attachment', function (e) {
            e.preventDefault();
            var filemanagerId = $(this).attr('data-filemanagerId');
            var clubId = $(this).attr('data-clubId');
            $('#ArticleAttachmentForm').remove();
            $form = $("<form id='ArticleAttachmentForm' method='post' action=" + downloadPath + "></form>");
            $form.append('<input type="hidden" id="filemanagerId" name="filemanagerId">');
            $form.append('<input type="hidden" id="clubId" name="clubId">');
            $('body').append($form);
            $('#filemanagerId').val(filemanagerId);
            $('#clubId').val(clubId);
            $form.submit();
        });
    };
    FgCmsArticleDetails.prototype.getArticleTitle = function (articleData) {
        var title = '';
        var defaultClbLang = defaultClubLang;
        if (typeof (articleData.article.text[defaultClbLang]) != 'undefined' && articleData.article.text[defaultClbLang]['title'] != '') {
            title = articleData.article.text[defaultClbLang]['title'];
        }
        else {
            title = articleData.article.text.default.title;
        }
        return title;
    };
    return FgCmsArticleDetails;
}());
