/*
 ================================================================================================
 * Wrapper function for rendering Article editorial detail page
 * Function - FgEditorialDetails
 ================================================================================================
 */
var globalPageTitleBarOptions;
var FgEditorialDetails = function () {
    var settings;
    var defaultSettings = {
        articleId: '',
        articleContentDiv: '#editorial-content',
        articleCommentDiv: '#editorial-content',
        articleHistoryDiv: '#div-article-history',
        articleTemplateId: 'templateArticleDetails',
        articleTemplatePreviewId: 'templateArticleDetailsPreview',
        articleTemplateMediaId: 'templateArticleMedia',
        articleTemplateDetailMediaId: 'templateArticleDetailMedia',
        articleTemplateAttachmentsId: 'templateArticleAttachments',
        articleTemplateSettingsId: 'templateArticleSettings',
        articleTemplateTextId: 'templateArticleText',
        articleTemplateDetailTextId: 'templateArticleDetailText',
        articleTemplateLinkId: 'templateArticleLink',
        articleTemplateSliderId: 'templateArticleSlider',
        articleTemplateHistoryId: 'templateArticleDetailHistory',
        commentsTemplateId: 'templateArticleComments',
        commentAreaType: 'editorialDetails',
        articleLogTemplateId: 'templateArticleLog',
        articleDataUrl: '',
        articleCommentsUrl: '',
        articleLogUrl: '',
        page: 'preview'

    };

    var initSettings = function (options) {
        settings = $.extend(true, {}, defaultSettings, options);
    };
    /*render details of an article */
    var renderArticle = function () {
        FgEditorialDetails.handlePageTitleBar(false);
        $.ajax({
            type: 'POST',
            url: settings.articleDataUrl,
            data: {articleId: settings.articleId},
            success: function (data) {
                handleCategoryTranslation(data);
                if (_.size(data) > 0) {
                    var mediaContent = FGTemplate.bind(settings.articleTemplateMediaId, {ArticleData: data});
                    var textContent = FGTemplate.bind(settings.articleTemplateTextId, {ArticleData: data});
                    var linkContent = FGTemplate.bind(settings.articleTemplateLinkId, {ArticleData: data});
                    var sliderContent = FGTemplate.bind(settings.articleTemplateSliderId, {ArticleData: data});

                    var content = {media: mediaContent, text: textContent, link: linkContent, slider: sliderContent};
                    var templateId = (settings.page === 'details') ? settings.articleTemplateId : settings.articleTemplatePreviewId;
                    content = FGTemplate.bind(templateId, {ArticleData: data, content: content});
                    $(settings.articleContentDiv).html(content);
                    if (data.article.media.position == "right_column" || data.article.media.position == "left_column") {
                        if( _.size(data.article.media) > 1 ){
                            initImageList();
                        }
                    } else {
                        if( _.size(data.article.media) > 1 ){
                            initSlider();
                        }
                        
                    }

                }
            }
        });
    };

    var renderArticleHistory = function () {
        var dataUrlTemp = 'revision/' + settings.articleId;
        $.getJSON(dataUrlTemp, null, function (data) {
            var htmlContent = FGTemplate.bind(settings.articleTemplateHistoryId, data);
            $(settings.articleHistoryDiv).html(htmlContent);


        });
    };
    // This function is used to handle all the functionalities related to article comment management 
    var handleArticleComments = function () {
        handleCommentEdit();
        handleCommentDelete();
        
        $('body').on('keyup','#comment-text-area_new', function(){
            if($(this).val() != ''){
                $('#save_changes').removeAttr('disabled')
            } else {
                $('#save_changes').attr('disabled', 'disabled');
            }
        })
    };

    var handleCommentEdit = function () {
        $('body').on('click', '.comment-edit', function () {
            var dataId = $(this).attr('data-id');
            
            $('#comments-form_' + dataId).removeClass('hide');
            $('#timeline_footer_' + dataId).removeClass('hide');
            
            $('#comment-edit_' + dataId).addClass('hide');
            $('#comment-text-content_' + dataId).addClass('hide');
            $('#comment-delete-' + dataId).addClass('hide');
            
            FgDirtyFields.init('comments-form_'+dataId,{
                saveChangeSelector: "#save_change-"+dataId
            });
            
        });

    };

    // Function for saving editorial comments
        $('body').on('click', '.comment_save', function (e) {
            e.preventDefault();
            validationFlag = 0;
            var commentId = $(this).attr("data-id");
            var articleId = $(this).attr("data-articleId");
            var comment = $('#comment-text-area_' + commentId).val();
            var postContent = bbcodeParser.bbcodeToHtml(comment).replace(/(<(?!img)([^>]+)>)/ig, "");
            if (postContent == '') {
                validationFlag = 1;
                $('#comment-error-block_' + commentId).removeClass('hide');
                $('#comments-li-data_' + commentId).find('.timeline-body').addClass('has-error');
            }
            if (validationFlag == 0) {
                FgXmlHttp.isDisabled = false;
                bbcodeTohtml = bbcodeParser.bbcodeToHtml(comment);
                dataArray = {'comment': bbcodeTohtml, 'commentId': commentId, 'articleId': articleId};
                FgXmlHttp.post(commentSavePath, dataArray, false, saveCallBack);
                $('.fg-dev-btnsave').off('click');
                
            }

        });
        
        $('body').on('click', '.comment_cancel', function (e) {
            e.preventDefault();
            
            var commentId = $(this).attr("data-id");
            
            $('#comments-form_' + commentId).addClass('hide');
            $('#timeline_footer_' + commentId).addClass('hide');
            
            $('#comment-edit_' + commentId).removeClass('hide');
            $('#comment-text-content_' + commentId).removeClass('hide');
            $('#comment-delete-' + commentId).removeClass('hide');
            
            var oldValue = $('#comment-text-area_' + commentId).data('value');
            $('#comment-text-area_' + commentId).val(oldValue);
        });

    // Function for deleting editorial comments
    var handleCommentDelete = function () {
        $('body').on('click', '.fg-comment-delete', function (e) {
            e.preventDefault();
            commentId = $(this).attr('data-id');
            $.post(deleteConfirmationPath, {'commentId': commentId}, function (data) {
                FgModelbox.showPopup(data);
            });
        });
    }

    // Callback function after saving the article comments
    var saveCallBack = function (data) {
        if (data) {
            var commentStatus = data.commentId;
            var dataUrlTemp = settings.articleCommentsUrl;
            $.getJSON(dataUrlTemp, {articleId: settings.articleId}, function (data) {
                var content = FGTemplate.bind(settings.commentsTemplateId, {commentDetails: data, 'commentAreaType': settings.commentAreaType});
                $(settings.articleCommentDiv).html(content);
                FgArticleCommentsCountUpdate.updateCount(commentStatus);
            });
        }
    };

    // Function for listing the comments added
    var renderArticleComments = function () {
        FgEditorialDetails.handlePageTitleBar(false);
        $.ajax({
            type: 'POST',
            url: settings.articleCommentsUrl,
            data: {articleId: settings.articleId},
            success: function (data) {
               if(data.globalCommentAccess == 1) {
                  content = FGTemplate.bind(settings.commentsTemplateId, {commentDetails: data, 'commentAreaType': settings.commentAreaType});
                  $(settings.articleCommentDiv).html(content);
                  handleArticleComments();
               }
            }
        });
    };

// Function for creating datatable for article log listing
    var renderDatatable = function () {

        columnDefs = [
            {"name": "name", width: '20%', "targets": 0, data: function (row, type, val, meta) {
                    row.sortData = row['date'];
                    row.displayData = row['date'];
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}},
            {"name": "option", width: '20%', "targets": 1, data: function (row, type, val, meta) {

                    var option = (typeof statusTranslations[row['field']] !== 'undefined') ? statusTranslations[row['field']] : row['field'];
                    var field = '';
                    if (option != '') {
                        field = option + '<span class="label label-sm fg-color-' + row['status'] + '">' + statusTranslations[row['status']] + '</span>';
                    } else {
                        field = (type === 'display') ? '-' : option;
                    }

                    row.sortData = field;
                    row.displayData = field;
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}},
            {"name": "value_before", width: '20%', "targets": 2, width: '15%', data: function (row, type, val, meta) {
                    row.sortData = row['valueBefore'];
                    row.displayData = row['valueBefore'];
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}},
            {"name": "value_after", width: '20%', "targets": 3, width: '15%', data: function (row, type, val, meta) {
                    row.sortData = row['valueAfter'];
                    row.displayData = row['valueAfter'];
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}},
            {"name": "changedBy", width: '20%', "targets": 4, data: function (row, type, val, meta) {
                    row.sortData = row['contact'];

                    row.displayData = row['contact'];
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}}

        ];


        var datatableOptions = {
            columnDefFlag: true,
            ajaxPath: logAjaxUrl,
            columnDefValues: columnDefs,
            ajaxparameterflag: true,
            fixedcolumn: false,
            scrollYflag: true,
            displaylengthflag: true,
            displaylength: 10,
            initialSortingFlag: true,
            initialsortingColumn: 0,
            initialSortingorder: 'desc',
            serverSideprocess: false,
        };
        FgDatatable.listdataTableInit('dataTable-article-log', datatableOptions)
        $.fn.dataTable.ext.afnFiltering.push(function (oSettings, aData, iDataIndex) {
            if (oSettings.nTable.id != 'dataTable-article-log') {
                return true;
            }
            var date = aData[0]; //date value of current record
            var startdate = $("#filter_start_date_" + articleId).val() != '' ? $("#filter_start_date_" + articleId).val() : '';
            var enddate = $("#filter_end_date_" + articleId).val() != '' ? $("#filter_end_date_" + articleId).val() : '';

            if ((startdate != '') || (enddate != '')) {
                var error = false;
                if ((startdate != '') || (enddate != '')) {
                    var div = 'log_date_error_' + articleId;
                    error = FgArticleLogFilter.validateDate($("#filter_start_date_" + articleId).val(), $("#filter_end_date_" + articleId).val(), div);
                }
                if (error) {
                    return false;
                } else {
                    $('#log_date_error_' + articleId).css('display', 'none');
                    return FgUtility.dateFilter(date, startdate, enddate);
                }
            }

            return true;
        });
        FgFormTools.handleDatepicker({todayHighlight: true, format: FgLocaleSettingsData.jqueryDateFormat, clearBtn: true});
        $('.datepicker').change(function () {
            listTable.draw();
        });
    };

    // Function for listing the log data of an article
    var renderArticleLog = function () {
        FgEditorialDetails.handlePageTitleBar(false); //with lan switch
        content = FGTemplate.bind(settings.articleLogTemplateId);
        $(settings.articleContentDiv).html(content);

        setTimeout(function () {
            renderDatatable();
        }, 1000);
    };

    var initSlider = function () {
        $("#fg-article-gallery").unitegallery({
            slider_textpanel_enable_title: true,
            slider_textpanel_enable_description: false,
            slider_control_zoom: false,
            slider_enable_text_panel: true,
            slider_textpanel_enable_bg: true
        });
    };
    var initImageList = function () {
        $("#fg-article-gallery").unitegallery({
                tiles_min_columns: 1,
                tiles_max_columns: 1,
            tile_enable_shadow: false,
//            tile_enable_border: true,
            tiles_space_between_cols: 15,
            tiles_justified_space_between: 15,
            tiles_col_width: 235,
            tile_border_color: "#ffffff",
            tile_enable_outline: true,
            tile_enable_textpanel:true,
            gallery_theme: "tiles",
            grid_num_rows: 30,
            theme_appearance_order: 'keep'
        });
    };

    var renderArticleTexts = function () {
        FgEditorialDetails.handlePageTitleBar(true); //with lan switch
        FgCreateArticle.renderTemplate(settings.articleTemplateDetailTextId, settings.articleDetailTextUrl, pathArticleSave, 'fg-article-text-form', FgEditorialDetails.renderArticleTexts);        
        setTimeout(function(){FgPageTitlebar.setMoreTab()},1000);
    }

    var renderArticleMedia = function (data) {
        FgEditorialDetails.handlePageTitleBar(true);
        FgCreateArticle.renderTemplate(settings.articleTemplateDetailMediaId, settings.articleDetailMediaUrl, pathArticleSave, 'fg-article-media-form', FgEditorialDetails.renderArticleMedia);
        FgCreateArticle.handleVideoUrls();
        FgCreateArticle.handleSave();
        setTimeout(function(){FgPageTitlebar.setMoreTab()},1000);
    }

    var renderArticleAttachments = function () {
        FgEditorialDetails.handlePageTitleBar(false);
        FgCreateArticle.renderTemplate(settings.articleTemplateAttachmentsId, settings.articleAttachmentsUrl, pathArticleSave, 'fg-article-attachments-form', FgEditorialDetails.renderArticleAttachments);
    }

    var renderArticleSettings = function () {
        FgEditorialDetails.handlePageTitleBar(false);
        FgCreateArticle.renderTemplate(settings.articleTemplateSettingsId, settings.articleSettingsUrl, pathArticleSave, 'fg-article-settings-form', FgEditorialDetails.renderArticleSettings);
    }

    var handlePageTitleBar = function (langSwitch) {
        if (langSwitch) {
            globalPageTitleBarOptions = {
                title: true,
                tab: true,
                tabType: 'client',
                row2: true,
                languageSwitch: true,
                articleSwitch: true
            }
            /* action menu bar ---- */
            FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar(globalPageTitleBarOptions);
        } else {
            globalPageTitleBarOptions = {
                title: true,
                tab: true,
                tabType: 'client',
                articleSwitch: true
            }
             $(".fg-action-menu-wrapper").FgPageTitlebar(globalPageTitleBarOptions);
        }
    }

    var toggleArticleText = function () {
        $(document).on('click', '.text-toggle', function (e) {
            $('.article-text').addClass('hide');
            $('#show-history-preview').parent().addClass('hide');
            var divClass = $(this).attr('data-tab');
            if (divClass == 'article-section-history') {
                renderArticleHistory();
                FgEditorialDetails.handlePageTitleBar(false);
            } else { //current tab
                FgEditorialDetails.handlePageTitleBar(true);
                if (updateHistoryFlag == 1) {//if history updated reload tab
                    updateHistoryFlag = 0; //reset flag
                    renderArticleTexts();
                }
            }
            $('.' + divClass).removeClass('hide');
        });
    }
    
    var handleCategoryTranslation  = function(data){
        var catTitleI18n = data.article.settings.categoryTitles;
        var catTitleDef = data.article.settings.categoryTitlesDef;
        var catTitleI18nArray = (catTitleI18n) ? catTitleI18n.split(',') : new Array();
        var catTitleDefArray = (catTitleDef) ? catTitleDef.split(',') : '';
        if(catTitleDefArray) {
            $.each(catTitleDefArray ,function(i, v){
                if(!catTitleI18nArray[i]){
                    catTitleI18nArray[i] = catTitleDefArray[i];
                }
            });
            var finalcatString = catTitleI18nArray.join(',');
            data.article.settings.categoryTitles = finalcatString;
        }
    };

    return {
        renderArticle: function () {
            renderArticle();
        },
        renderArticleComments: function () {
            renderArticleComments();
        },
        renderArticleTexts: function () {
            renderArticleTexts();
        },
        renderArticleMedia: function () {
            renderArticleMedia();
        },
        renderArticleAttachments: function () {
            renderArticleAttachments();
        },
        renderArticleSettings: function () {
            renderArticleSettings();
        },
        renderArticleHistory: function () {
            renderArticleHistory();
        },
        renderArticleLog: function () {
            renderArticleLog();
        },
        init: function (opt) {
            initSettings(opt);
        },
        initSlider: function () {
            initSlider();
        },
        initImageList: function () {
            initImageList();
        },
        toggleArticleText: function () {
            toggleArticleText();
        },
        handlePageTitleBar: function (langSwitch) {
            handlePageTitleBar(langSwitch);
        }
    };
}();

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

$(document).on('click', '#preview-history', function (e) {
    var preview_text = $(this).attr('data-content');
    var preview_teaser = $(this).attr('data-teaser');
    var preview_title = $(this).attr('data-title');
    $('#preview-article-version').removeClass('hide');
    $('#preview-article-version').find('h2').html(preview_title);

    var str_teaser = (preview_teaser.length > 0) ? "<p class='fg-FS-15'>" + preview_teaser + "</p>" : "";
    var preview_content = str_teaser + "<div class='clearfix'/>" + preview_text;
    $('#show-history-preview').html(preview_content);
});

$(document).on('click', '#update-history-revision', function (e) {
    var id = $(this).attr('data-content');
    var articleId = $(this).attr('data-article-id');
    var dataUrlTemp = 'updateRevision/' + articleId + '/' + id;
    updateHistoryFlag = 1; //set flag
    FgXmlHttp.post(dataUrlTemp, '', '', FgEditorialDetails.renderArticleHistory);

});

// Function for updating the comments count while creating or deleting a commment
FgArticleCommentsCountUpdate = {
    updateCount: function (type) {
        var currentCount = parseInt($('#fg_tab_comments span.fg-badge-blue').html());
        var newCount = (type == 'new') ? currentCount + 1 : ((type == 'delete') ? currentCount - 1 : currentCount);
        $('#fg_tab_comments span.fg-badge-blue').html(newCount);
    }
};

// Function for handling functionalities regarding log filter
FgArticleLogFilter = {
    getWindowHeight: function (reduceWidth) {
        var height = $(window).height() - reduceWidth;
        if (height <= 300) {
            height = 300;
        }

        return height;
    },
    isFutureDate: function (idate) {
        //The parameter,idate passed should be timestamp with seconds
        var today = Date.now();
        return (today < parseInt(idate)) ? true : false;
    },
    validateDate: function (startdate, enddate, divid) {
        //to check whether start date is greater than end date
        if (startdate != '')
            var startdateTimestamp = moment(startdate, FgLocaleSettingsData.momentDateFormat).format('x');
        else
            var startdateTimestamp = 0;

        if (enddate != '')
            var enddateTimestamp = moment(enddate, FgLocaleSettingsData.momentDateFormat).format('x');
        else
            var enddateTimestamp = 0;
        //ends

        //to check whether the dates are less than future date
        var isStartDateFuture = FgArticleLogFilter.isFutureDate(startdateTimestamp);
        var isEndDateFuture = FgArticleLogFilter.isFutureDate(enddateTimestamp);
        //ends
        var error_flag = false;
        if ((enddateTimestamp) && (startdateTimestamp > enddateTimestamp)) {
            error_flag = true;
            $('#' + divid).css('display', 'block');
            $('#' + divid).html(errorMsgTranslations['Log_date_filter_err_msg1'] + '.');
        }
        if (isStartDateFuture && isEndDateFuture) {
            error_flag = true;
            $('#' + divid).css('display', 'block');
            $('#' + divid).html(errorMsgTranslations['Log_date_filter_err_msg2'] + '.');
        } else if (isStartDateFuture || isEndDateFuture) {
            if (isStartDateFuture) {
                error_flag = true;
                $('#' + divid).css('display', 'block');
                $('#' + divid).html(errorMsgTranslations['Log_date_filter_err_msg3'] + '.');
            }
            if (isEndDateFuture) {
                error_flag = true;
                $('#' + divid).css('display', 'block');
                $('#' + divid).html(errorMsgTranslations['Log_date_filter_err_msg4'] + '.');
            }
        }

        return error_flag;

    }
};

$(document).on('hide.bs.tab', '#paneltab', function (e) {
    var articleFormIsDirty = ($('#save_changes').length > 0 && typeof $('#save_changes').attr('disabled') == 'undefined')?true:false;
    var unsavedMessage = jstranslations.dirtyformConfirm;
    if(articleFormIsDirty && !confirm(unsavedMessage)){
       e.preventDefault();
    }
}) 
