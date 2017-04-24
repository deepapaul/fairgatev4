var pageid = 1;
window.checkFlag = 0;  //Flag checking to avoid repeatation on lazy loading
var firstLoadCheck = false;
FgArticleList = {
    init: function () {
        FgArticleList.drawList('lazyLoad');
        FgArticleList.redirectToDetails();
        FgArticleList.scrollFunction();
        //init tooltip
        FgPopOver.init('.fg-dev-Popovers',true);
        //search
        FgArticleList.searchArticleList();
    },
    drawList: function(source) {
        if(firstLoadCheck==false){
            firstLoadCheck = true;
            if (source !== 'lazyLoad') {
                pageid = 1;
                window.checkFlag = 0;
                currentPageResultCount = articleCount;
            }
            if(currentPageResultCount < articleCount) {
                firstLoadCheck = false;
                return;
            }
            //get listing
            Metronic.startPageLoading();
            pageid = (pageid <= 0) ? 1 : pageid;
            articleCount = (articleCount <= 0) ? 10 : articleCount;
            articlefilter = localStorage.getItem(filterStoragename);
            articlesearch = localStorage.getItem(searchLocalStorage);
            $.ajax({
                type: 'POST',
                url: getListingPage,
                data: { pageId : pageid, countIs : articleCount, filterDetails : articlefilter, searchDetails : articlesearch },
                success: function( data ) {
                    firstLoadCheck = false;
                    if (data['aaData'].length > 0) {
                        $('.article-nodata-wrapper').addClass('hide');
                        FgArticleList.loopTemplate(data, source);
                    } else {
                        if (source !== 'lazyLoad') {
                            $('.fg-news-overview-wrapper').html("");
                            $('.article-nodata-wrapper').removeClass('hide');
                        }
                    }
                    pageid++;
                    Metronic.stopPageLoading();
                    currentPageResultCount = data['aaData'].length;
                }
            });
        }
    },
    loopTemplate: function(data, source) {
        if (source !== 'lazyLoad') {
            $('.fg-news-overview-wrapper').html("");
        }
        $.each(data['aaData'], function(key, value) {
            //text truncating
            var trimmedString = '';
            if (value.text) {
                trimmedString = FgArticleList.truncateStringCutWords(value.text, 160);
            }
            value.text = trimmedString;
            var articleDetPath = articleDetailPath;
            value.detailPath = articleDetPath.replace("replaceArticleId", value.articleId);
            value.translations = {areas : areaTrans, cat : catTrans};
            value.areaCatTooltip = '';
            if (value.club_id !== clubId) {
                if (clubTitles[value.club_id].clubType == 'federation' || clubTitles[value.club_id].clubType == 'sub_federation') {
                    value.areaCatTooltip = clubTitles[value.club_id].title;
                }
            }
            var htmlFinal = FGTemplate.bind('template-article-listing', {'data':value});
            $('.fg-news-overview-wrapper').append(htmlFinal);
        });
    },
    truncateStringCutWords: function(unTruncatedText, maxLength) {
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
    },
    redirectToDetails: function() {
        $(document).on('click tap touchstart', '.detail-page-redirect-wrapper', function(){
            var articleId = this.id;
            var articleDetPath = articleDetailPath;
            var detailPath = articleDetPath.replace("replaceArticleId", articleId);
            window.location.href = detailPath;
        });
    },
    scrollFunction: function() {
        $(window).scroll(function () {
            if (pageid > 1) {
                var wintop = $(window).scrollTop(), docheight = $(document).height(), winheight = $(window).height();
                var scrolltrigger = 0.95;
                if ((wintop / (docheight - winheight)) > scrolltrigger) {
                    if(pageid !== window.checkFlag) {
                        window.checkFlag = pageid;
                        FgArticleList.drawList('lazyLoad');
                    }            
                }
            }
        });
    },
    searchArticleList: function() {
        $(document).on('keypress','#fg_dev_member_search',function(event){
            if (event.which == 13) {//enter key pressed
                localStorage.setItem(searchLocalStorage,$(this).val())
                FgArticleList.searchEvent();
            }
        });
    },
    //To search events
    searchEvent:function(){
        var searchVal = $('#fg_dev_member_search').val();
        $pageTitle =$('.article-page-title-text');
        if(searchVal===''){
           $pageTitle.html(pageTitle);
        }else{
           $pageTitle.html(searchTitle.replace('%searchval%',searchVal));
        }
        FgArticleList.drawList('serachList');
    },
}


