var FgWebsiteArticle = (function () {
    function FgWebsiteArticle() {
        this.paginationCount = 10;
        this.currentPagination = 0;
        this.currentTimeout = 0;
        this.initArticleClick();
        this.scrollFunction();
        this.connectSearchElements();
    }
    FgWebsiteArticle.prototype.renderArticleList = function () {
        $('#fg-dev-article-loader').remove();
        var _this = this;
        var data = {};
        data.page = _this.currentPagination;
        data.time = $('#fg-page-timeperiod-input').val();
        data.text = $('#fg-page-search').val();
        data.pageId = articleSpecialPageId;
        $.ajax({
            type: "POST",
            url: articleListPath,
            data: data,
            success: function (response) {
                if (response.articleData.length > 0) {
                    _this.currentPagination++;
                    _.each(response.articleData, function (article, index) {
                        if ($('#article-' + article.articleId).length == 0) {
                            if (article.text) {
                                article.text = _this.truncateStringCutWords(article.text, 160);
                            }
                            var toBeEncodedArray = response.toBeEncodedArray;
                            toBeEncodedArray.i = ((_this.currentPagination - 1) * _this.paginationCount) + index;
                            var articleDetPathEncoded = articleDetPath.replace(/__ENCODEDSTRING__/g, window.btoa(JSON.stringify(toBeEncodedArray)));
                            article.detailPath = articleDetPathEncoded.replace(/__ARTICLEID__/g, article.articleId);
                            article.translations = { areas: areaTranslation, cat: categoryTranslation };
                            article.areaCatTooltip = '';
                            if (article.club_id !== clubId) {
                                clubTitlesArray = jQuery.parseJSON(clubTitles);
                                if (clubTitlesArray[article.club_id].clubType == 'federation' || clubTitlesArray[article.club_id].clubType == 'sub_federation') {
                                    article.areaCatTooltip = clubTitlesArray[article.club_id].title;
                                }
                                article.AREAS = '';
                            }
                            var articleFinal = FGTemplate.bind('template-article-listing', { 'data': article });
                            $('#fg-article-container').append(articleFinal);
                        }
                    });
                }
                if ($('.fg-dev-article-list').length == 0) {
                    $('#fg-article-container').html('<p>' + articleEmptyMessage + '</p>');
                }
                if (response.articleData.length <= _this.paginationCount) {
                    $('#fg-article-container .fg-dev-article-list:last').after('<span id="fg-dev-article-loader"></span>');
                }
            },
            dataType: 'json'
        });
    };
    FgWebsiteArticle.prototype.initArticleClick = function () {
        $('#fg-article-container').on('click', '.fg-dev-article-list', function () {
            var url = $(this).data('url');
            window.location.href = url;
        });
    };
    FgWebsiteArticle.prototype.connectSearchElements = function () {
        var _this = this;
        $('#fg-dev-pagetitle-container').on('change', '#fg-page-timeperiod-input', function () {
            clearTimeout(_this.currentTimeout);
            _this.currentTimeout = setTimeout(function () {
                $('#fg-article-container').html('');
                _this.currentPagination = 0;
                _this.renderArticleList();
            }, 400);
        });
        $('#fg-dev-pagetitle-container').on('keyup', '#fg-page-search', function () {
            clearTimeout(_this.currentTimeout);
            _this.currentTimeout = setTimeout(function () {
                $('#fg-article-container').html('');
                _this.currentPagination = 0;
                _this.renderArticleList();
            }, 400);
        });
    };
    FgWebsiteArticle.prototype.truncateStringCutWords = function (unTruncatedText, maxLength) {
        var myText = jQuery(unTruncatedText).text();
        var textLength = myText.length;
        var trimmedString = myText.substr(0, maxLength);
        if (trimmedString.lastIndexOf(" ") > 0 && textLength > 160) {
            trimmedString = trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" ")));
        }
        if (myText.length > maxLength) {
            trimmedString = trimmedString + '...';
        }
        return trimmedString;
    };
    FgWebsiteArticle.prototype.scrollFunction = function () {
        var _this = this;
        $(window).scroll(function () {
            if (_this.currentPagination > 0 && ($('#fg-dev-article-loader').length > 0)) {
                var wintop = $(window).scrollTop(), docheight = $(document).height(), winheight = ($(window).height() + $('.fg-web-page-footer').height());
                var scrolltrigger = 0.95;
                if ((wintop / (docheight - winheight)) > scrolltrigger) {
                    _this.renderArticleList();
                }
            }
        });
    };
    return FgWebsiteArticle;
}());
