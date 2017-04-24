FgVideoThumbnail = function () {
    var videoThumb;

    var getVideoId = function (videoUrl) {
        var videoDetails;
        var videoId;
        if (videoUrl.indexOf('youtube.com') > -1)
        {
            if (videoUrl.indexOf('v=') > -1) {
                videoId = videoUrl.split('v=')[1].split('&')[0];
            }
            else if (videoUrl.indexOf('embed') > -1) {
                videoId = videoUrl.split('embed/')[1].split('?')[0];
            }
            ;
            return processYouTube(videoId);
        }
        else if (videoUrl.indexOf('youtu.be') > -1)
        {
            videoId = videoUrl.split('/')[3];
            return processYouTube(videoId);
        }
        else if (videoUrl.indexOf('vimeo.com') > -1)
        {
            if (videoUrl.match(/https?:\/\/(www\.)?vimeo.com\/(\d+)($|\/)/))
            {
                videoId = videoUrl.split('/')[3];
            }
            else if (videoUrl.match(/^vimeo.com\/channels\/[\d\w]+#[0-9]+/))
            {
                videoId = videoUrl.split('#')[1];
            }
            else if (videoUrl.match(/vimeo.com\/groups\/[\d\w]+\/videos\/[0-9]+/))
            {
                videoId = videoUrl.split('/')[4];
            }
            else if (videoUrl.match(/player.vimeo.com\/video\/[0-9]+/))
            {
                videoId = videoUrl.split('/')[2];
            }
            else if (videoUrl.match(/vimeo.com\/channels\/staffpicks\/[0-9]+/))
            {
                videoId = videoUrl.split('/')[5];
            }
            else {
                videoId = '';
                   }
            }
        else
        {

            videoId = '';
        }
        videoDetails = {type: 'v', id: videoId};
        return videoDetails;
    }

    var processYouTube = function (videoId) {
        
        if (!videoId) {
            throw new Error('Unsupported YouTube URL');
        }
        videoDetails = {type: 'y', id: videoId};
        return videoDetails; // default.jpg OR hqdefault.jpg
    }

    //var url;
    var getVideoThumb = function (url) {
        //  url = 'https://www.youtube.com/watch?v=Dp6lbdoprZ0';
        // url='https://www.youtube.com/watch?v=DkhZ3H5IelU';
        // url ='https://vimeo.com/channels/staffpicks/148982525';
     //   url = 'https://vimeo.com/channels/staffpicks/149124190';
        var id = getVideoId(url);

        if (id['type'] == 'y') {
            videoThumb =  getYouTubeThumb(id);
       
        } else if (id['type'] == 'v') {

            $.ajax({
                url: 'vimeo.com/api/v2/video/' + id['id'] + '.json',
                dataType: 'jsonp',
                success: function (data) {
                    showThumb({url: data[0].thumbnail_large});
                   // videoThumb = data[0].thumbnail_large;
                //  return videoThumb;
                   // console.log(videoThumb);
                }
            });
        }
        return videoThumb;
    }
    
    var showThumb = function (data){
      videoThumb = data;
      FgVideoThumbnail.thumb(videoThumb);
        return videoThumb;
    }
    
    var getYouTubeThumb = function (id) {
        if (!id) {
            throw new Error('Unsupported YouTube URL');
        }
        videoThumb = 'http://i2.ytimg.com/vi/' + id['id'] + '/hqdefault.jpg';
        return videoThumb;
    }
    
    var showThumbOnChangingUrl = function (settings) {
        urlVal = settings.urlVal;
        inputElement = settings.inputElement;
        if (!urlVal.match(/^[a-zA-Z]+:\/\//)) {
            urlVal = 'https://' + urlVal;
            inputElement.val(urlVal);
        }
        if(!(/^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(urlVal))){               
            inputElement.parent('div').append('<span class=required id=invalid-url>'+invalidUrl+'</span>');
            inputElement.parent('div').addClass('has-error');  
            return false;
        }
        var videoDetails = getVideoId(urlVal);   
        if (videoDetails['type'] == 'y') {
            videoThumb =  getYouTubeThumb(videoDetails);
            settings.videoThumb = videoThumb;
            settings.successCallBack.call({}, settings);                               
        } else if (videoDetails['type'] == 'v') {
            $.ajax({
                url: 'http://vimeo.com/api/v2/video/' + videoDetails['id'] + '.json',
                dataType: 'jsonp',
                success: function (data) {                            
                   //modified medium image to large
                   settings.videoThumb = data[0]['thumbnail_large'];
                   settings.successCallBack.call({}, settings);                   
                },
                error: function () {
                    console.log('ERROR');
                },
            });
        }
    }
    
    return {
        thumb: function (url) {
          videoThumb = getVideoThumb(url);
          return videoThumb;
        },
        getVideoId: function (url){
            return getVideoId(url);
        },
        getYouTubeThumb: function(id){
            return getYouTubeThumb(id);
        },
        
        //show thumb on changing video url
        showThumbOnChangingUrl:  function(urlVal){
            return showThumbOnChangingUrl(urlVal);
        },
    }
}();
   

