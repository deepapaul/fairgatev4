/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgWebsiteArticle {

    private paginationCount: number = 10;
    private currentPagination: number = 0;
    private currentTimeout: number = 0;

    constructor() {
        this.initArticleClick();
        this.scrollFunction();
        this.connectSearchElements();
    }

    // Function to render the articles for special page
    public renderArticleList()
    {
        $('#fg-dev-article-loader').remove();
        let _this = this;
        let data = {};
        data.page = _this.currentPagination;
        data.time = $('#fg-page-timeperiod-input').val();
        data.text = $('#fg-page-search').val();
        data.pageId = articleSpecialPageId;

        $.ajax({
          type: "POST",
          url: articleListPath,
          data: data,
          success: function(response){
            if(response.articleData.length > 0){
                _this.currentPagination++;

                let isAllCategory = response.filterData.isAllCategory;
                let isAllArea = response.filterData.isAllArea;
                let categoryCount = (response.filterData.categoryIds != null)?response.filterData.categoryIds.split(',').length:0;
                let areaCount = (response.filterData.areas != null)?response.filterData.areas.split(',').length:0;
                let sharedClub = (typeof response.filterData.sharedClub != 'undefined')?true:false;
                let areaClub = response.filterData.areaClub;
                if(areaClub == true) areaCount++;

                _.each(response.articleData, function(article, index){
                    article.isAllCategory = isAllCategory;
                    article.isAllArea = isAllArea;
                    article.categoryCount = categoryCount;
                    article.areaCount = areaCount;
                    article.areaClub = areaClub;
                    article.sharedClub = sharedClub;

                    if($('#article-'+article.articleId).length == 0){
                        if (article.text) {
                            article.text = _this.truncateStringCutWords(article.text, 160);
                        }

                        let toBeEncodedArray = response.toBeEncodedArray;
                        toBeEncodedArray.i = ((_this.currentPagination-1)*_this.paginationCount) + index;
                        let articleDetPathEncoded = articleDetPath.replace(/__ENCODEDSTRING__/g, window.btoa(JSON.stringify(toBeEncodedArray)));
                        article.detailPath = articleDetPathEncoded.replace(/__ARTICLEID__/g, article.articleId);
                        article.translations = {areas : areaTranslation, cat : categoryTranslation};

                        article.areaTooltip = '';
                        article.catTooltip = '';
                        article.isCurrentClub = 1;
                        if (article.club_id !== clubId) {
                            clubTitlesArray = jQuery.parseJSON(clubTitles);
                            if (clubTitlesArray[article.club_id].clubType == 'federation' || clubTitlesArray[article.club_id].clubType == 'sub_federation') {
                                article.areaTooltip = clubTitlesArray[article.club_id].title;
                                article.catTooltip = clubTitlesArray[article.club_id].title;
                            }
                            article.AREAS = '';
                            article.CATEGORIES = '';
                            article.isCurrentClub = 0;
                        }
                        let articleFinal = FGTemplate.bind('template-article-listing', {'data':article});
                        $('#fg-article-container').append(articleFinal);
                    }
                });
            } 

            //If article data is empty put no data 
            if($('.fg-dev-article-list').length == 0){
                $('#fg-article-container').html('<div class="col-md-12"><p>'+articleEmptyMessage+'</p></div>');
            }

            if(response.articleData.length == _this.paginationCount){
                $('#fg-article-container article:last').after('<span id="fg-dev-article-loader"></span>');
            }
            
          },
          dataType: 'json'
        });
        
    }

    public initArticleClick(){
        $('#fg-article-container').on('click','.fg-dev-article-list', function(){
            let url = $(this).data('url');
            window.location.href = url;
        });
    }

    public connectSearchElements(){
        let _this = this;
        $('#fg-dev-pagetitle-container').on('change', '#fg-page-timeperiod-input', function(){

            //clear current content
            clearTimeout(_this.currentTimeout);

            //settimeout
            _this.currentTimeout = setTimeout(function(){ 
                $('#fg-article-container').html('');
                _this.currentPagination = 0;
                _this.renderArticleList();
              }, 400);
        });

        $('#fg-dev-pagetitle-container').on('keyup', '#fg-page-search', function(){
            
            //clear current content
            clearTimeout(_this.currentTimeout);

            //settimeout
            _this.currentTimeout = setTimeout(function(){ 
                $('#fg-article-container').html('');
                _this.currentPagination = 0;
                _this.renderArticleList();
              }, 500);
        });
    }

    private truncateStringCutWords(unTruncatedText, maxLength) {
        var myText = jQuery(unTruncatedText).text();
        var textLength = myText.length;
        //trim the string to the maximum length
        var trimmedString = myText.substr(0, maxLength);
        //re-trim if we are in the middle of a word
        if(trimmedString.lastIndexOf(" ") > 0 && textLength > 160 ) {
            trimmedString = trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" ")));
        }
        
        if (myText.length > maxLength) {
            trimmedString = trimmedString+'...';
        }
        
        return trimmedString;
    }

    private scrollFunction() {
        let _this = this;
        $(window).scroll(function () {
            if (_this.currentPagination > 0 && ($('#fg-dev-article-loader').length >0) ) {
                var wintop = $(window).scrollTop(), docheight = $(document).height(), winheight = ($(window).height() + $('.fg-web-page-footer').height());
                var scrolltrigger = 0.95;
                if ((wintop / (docheight - winheight)) > scrolltrigger) {
                    _this.renderArticleList();
                }  
            }
        });
    }
}


