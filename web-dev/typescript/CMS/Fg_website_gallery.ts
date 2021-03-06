/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
var FgXmlHttp = FgXmlHttp;
var FGTemplate = FGTemplate;

var galleryData;
var galleryDetailedData;
class FgWebsiteGallery {
    public wrapperDiv: string = '#gallery-specialpage-wrapper';
    public level1Template: string = 'level1Template'; // gallery listing
    public level2Template: string = 'level2Template'; //album listing
    public level3Template: string = 'level3Template'; //image listing
    public levelMoreTemplate: string = 'galleryLoadRemainingTemplate'; //load more image listing
    public wrapperMoreDiv: string = '.fg-gallery-photos-wrapper';//load more wrapper div
    public loadMoreDiv: string = 'gallaryLoadmoreTemplate';//load more button template
    public hashValue: string = '';
    public galleyDetails: any;
    public galleyVariables: any;
    public albumVariables: any;
    public galleryId: any = '';
    public albumId: any = '';
    public subAlbumId: any = '';
    public settings: any = {};
    public defaultSettings: any = {
        galleryDetailUrl: '',
        galleryUploadPath: '',
    };
    public pageLimit = 100;
    public galleryImages = [];
    public curentPage = 0;
    
    constructor(options) {

        this.settings = $.extend(true, {}, this.defaultSettings, options);
        this.getGalleryDetails();
        this.handleAlbumClick();
        this.breadcrumbClick();

    }

    public init() {
    }

 public breadcrumbClick(){
        let _this = this;
        $(document).on('click', '.fg-breadcrumbs', function(event) {            
            var dataBC = event.target.id;
            var id = parseInt(dataBC.match(/\d+/)[0], 10);;

            var homepage = window.location.href.split('#')[0];
            _this.splitHash();
             var galData = _this.processGallery();
            if(id==1){
                _this.loadPage('');
            } 
            if(id==2){
                _this.loadPage(_this.galleryId);
            }else if(id==3){
                var albumLoad = _this.galleryId + "_" + _this.albumId;
                _this.loadPage(albumLoad);
            }
            
    
        });​
    }
    public generateBreadCrumb() {
        
        var homepage = window.location.href.split('#')[0];
        this.splitHash();
        let breadCrumbArray: any = [];
        // var htmlBreadcrumb = "";
        var j = 0;
        var title = pageTitle1;
       
        if (this.galleryId) {
               var galleryname = _.findWhere(this.galleyDetails, { "role_id": this.galleryId });
            if (this.albumId) {
                var gallerypage = homepage + "#" + this.galleryId;
            } else {
                var gallerypage = 'javascript:void(0);';
                title = galleryname.title;
            }
  
        }
        if (this.albumId) {
           
            var albumname = _.findWhere(this.galleyDetails, { "parent_id": this.albumId });
            if ((this.subAlbumId)) {
                var albumpage = homepage + "#" + this.galleryId + "_" + this.albumId;
                
            } else {
                var albumpage = 'javascript:void(0);';
                title =albumname.name;
                
            }

            
        }
      
         if ((this.subAlbumId))  {
            var subalbumname = _.findWhere(this.galleyDetails, { "album_id": this.subAlbumId });
           
            // var subalbumpage =  homepage + "#"+this.galleryId+"_"+this.albumId+"_"+ this.subAlbumId;
            var subalbumpage = 'javascript:void(0);';
              var subalbname = subalbumname.name;
            
        }
        var galData = this.processGallery();
        var linkdata = 'javascript:void(0);';
        if (this.galleryId != '' && this.albumId != '' && this.subAlbumId != '') {
            title = subalbname;
            pageTitleBarOptions['breadcrumb'] = true;
            
            breadCrumbArray.push({ link: homepage, label: pageTitle1 });
            breadCrumbArray.push({ link: linkdata, label: galleryname.title });
            breadCrumbArray.push({ link: linkdata, label: (typeof albumname !=='undefined')? albumname.parentname:'' });
            pageTitleBarOptions['breadcrumbData'] = breadCrumbArray;
        }else if (this.galleryId != '' && this.albumId != '') {
       
            title = albumname.parentname;
            pageTitleBarOptions['breadcrumb'] = true;
            breadCrumbArray.push({ link: linkdata, label: pageTitle1 });
            breadCrumbArray.push({ link: linkdata, label: galleryname.title });
            pageTitleBarOptions['breadcrumbData'] = breadCrumbArray;
        }else if (this.galleryId != '') {
            if(galData.length == '1'){
                 title = pageTitle1;
                pageTitleBarOptions['breadcrumb'] = false;
            }else{
             pageTitleBarOptions['breadcrumb'] = true;
             breadCrumbArray.push({ link: linkdata, label: pageTitle1 });
              title =galleryname.title;
            }
            
             pageTitleBarOptions['breadcrumbData'] = breadCrumbArray;
         }else{
            pageTitleBarOptions['breadcrumb'] = false;
         }
        
        
        
        pageTitleBarOptions['title'] = title;
       
        new FgWebsitePageTitleBar('fg-dev-pagetitle-container', pageTitleBarOptions);

    }



    
    public getGalleryDetails() {
        this.splitHash();
        var _this = this;
        $.post(this.settings.galleryDetailUrl, {pageId: this.settings['pageId']}, function(data) {
            galleryData = data;
            _this.galleyDetails = data;
            _this.renderTemplate();

        });


    }
    public processAlbum(role_id) {
        let albumDataArray: any = [];
        let result: any = [];
        let j = 0;
        var albumData = _.where(galleryData, { "role_id": role_id });
       // var albumGrp = _(albumData).filter(function(x) { return x['parent_id'] != "0"; });
       
        _.each(_.groupBy(albumData, 'parent_id'), function(value, key, list) {
             var firstAlbum = value[0];
            var itemSel = _.where(value, { "is_cover_image": "1","parent_id" : firstAlbum['parent_id'] });

            if (_.isEmpty(itemSel)) {
                albumDataArray[j] = value[0];
            } else {
                 if(itemSel[0]["album_id"]==firstAlbum["album_id"]){
                     albumDataArray[j] = itemSel[0];
                    
                   
                }else{
                   albumDataArray[j] = value[0];
                }
                //albumDataArray[j] = itemSel[0];
            }
            albumDataArray[j]['index'] = j;
            j++;
        });
        
        //delete albumDataArray["0"];
        this.albumVariables = albumDataArray;
        albumDataArray = _.sortBy(albumDataArray, 'albumOrder');
        result['albums'] = albumDataArray;

        return result;

    }
    public processGallery() {
        let galleryArray: any = [];
        let galleryFinalArray: any = [];
        let i = 0;
        let groupByData = _.sortBy(_.groupBy(galleryData, 'role_id'),"parentSort");
        _.each(groupByData, function(value, key, list) {
            var firstAlbum = value[0];
            var itemSel = _.where(value, { "is_cover_image": "1", "parent_id" : firstAlbum['parent_id'] });
            if (_.isEmpty(itemSel)) {
                galleryArray[i] = value[0];
                galleryArray[i]['index'] = i;
                i++;

            } else {
                if(itemSel[0]["album_id"]==firstAlbum["album_id"]){
                    galleryArray[i] = itemSel[0];
                    galleryArray[i]['index'] = i;
                   
                }else{
                    galleryArray[i] = value[0];
                    galleryArray[i]['index'] = i;
                }
                 i++;
            }
        }
        );

        this.galleyVariables = galleryArray;        
          var galleryArrayGrp = _.groupBy(galleryArray, 'role_type');  
          var sortedResult = [];
          if(typeof galleryArrayGrp.C != 'undefined')
            sortedResult.push(galleryArrayGrp.C);
          if(typeof galleryArrayGrp.T != 'undefined')
            sortedResult.push(galleryArrayGrp.T);
          if(typeof galleryArrayGrp.W != 'undefined')
            sortedResult.push(galleryArrayGrp.W);

        _.each(_.flatten(sortedResult), function(value, key, list) {
            value.index = key;
            galleryFinalArray[key] = value;
        });
        return  galleryFinalArray;
        
    }
   
    public processSubAlbum(role_id, album_id) {
        let albumArray: any = [];
        let j = 0;
        let result = [];

        var subAlbumData = _.where(galleryData, { "role_id": role_id, "parent_id": album_id });
        
        var subAlbumDataFiltered = _(subAlbumData).filter(function(x) { return x['subAlbumID'] == "0"; });
       
        var subAlbumGroup = _.groupBy(subAlbumDataFiltered, 'album_id');
      
        _.each(subAlbumGroup, function(value, key, list) {
            var itemSel = _.where(value, { "is_cover_image": "1" });
            if (_.isEmpty(itemSel)) {
                albumArray[j] = value[0];

            } else {

                albumArray[j] = itemSel[0];
            }
            albumArray[j]['index'] = j;
            j++;
        });
        var albumSorted = _.sortBy( albumArray, 'parentSort');
        
        this.albumVariables = albumSorted;
        
        result['albums'] = albumSorted;
        result['images'] = _.where(galleryData, { "role_id": role_id, "parent_id": album_id, "album_id": album_id });

        return result;

    }

    public processSubAlbumImages(role_id, album_id, sub_album_id) {
        var result = [];
        result['images'] = _.where(galleryData, { "role_id": role_id, "parent_id": album_id, "album_id": sub_album_id });

        return result;
    }

    public renderUniteGallery() {

        let imagesArray = $.extend({}, this.galleryImages);
        imagesArray.images = imagesArray.images.slice((this.curentPage * this.pageLimit), ((this.curentPage + 1) * this.pageLimit));
         if (this.curentPage < 1) {
            var html = FGTemplate.bind(this.level3Template, { data: imagesArray, uploadPath: this.settings.galleryUploadPath, curentPage: this.curentPage });
            $(this.wrapperDiv).html(html);
        } else {
            var html = FGTemplate.bind(this.levelMoreTemplate, { data: imagesArray, uploadPath: this.settings.galleryUploadPath, curentPage: this.curentPage });
            $(this.wrapperMoreDiv).append(html);
        }
 
        $('#gallery-loader').remove();
        if (this.pageLimit <= imagesArray.images.length) {
            var loadContent = FGTemplate.bind(this.loadMoreDiv);
            $('.fg-gallery-photos-wrapper').append(loadContent);
        }
        if (typeof imagesArray['images'] != 'undefined') {
            let galleryId = '#gallery-' + this.curentPage;
            this.initUniteGallery(galleryId);
            this.curentPage++;
        }
    }
    
    public renderTemplate() {

        this.generateBreadCrumb();

        if (this.galleryId != '' && this.albumId != '' && this.subAlbumId != '') {//show sub album images
            this.curentPage = 0;
            var subAlbumImages = this.processSubAlbumImages(this.galleryId, this.albumId, this.subAlbumId);
            this.galleryImages = subAlbumImages;
            this.renderUniteGallery();
        } else if (this.galleryId != '' && this.albumId != '') { // show subalbums and album images
            this.curentPage = 0;
            var subAlbumDetails = this.processSubAlbum(this.galleryId, this.albumId);
            this.galleryImages = subAlbumDetails;
            this.renderUniteGallery();
        } else if (this.galleryId != '') { // show albums        
            var galData = this.processGallery();    
            if(galData.length == '1'){ // To show/hide title bar buttons if gallery count is 1
                $('.fg-web-titlebar-right-col').addClass('hide');
            } else {
                $('.fg-web-titlebar-right-col').removeClass('hide');
            }
            var albumDetails = this.processAlbum(this.galleryId);
            var html = FGTemplate.bind(this.level2Template, { data: albumDetails, uploadPath: this.settings.galleryUploadPath });
            $(this.wrapperDiv).html(html);

        } else {//show galleris          
            var galData = this.processGallery();
            if(galData.length < 1){
                $('#gallery-specialpage-wrapper').html(noAlbumTrans);
                this.handleNextPrevBtn();
                return;
            }
            if(galData.length == '1'){ // To show albums if gallery count is 1
                this.loadPage(galData[0]['role_id']);
                return true;
            }
            var html = FGTemplate.bind(this.level1Template, { data: galData, uploadPath: this.settings.galleryUploadPath });
            $(this.wrapperDiv).html(html);
        }

        this.handleNextPrevBtn();
    }

    public handleNextPrevBtn() {
        var hashvalue = window.location.hash.substr(1);
        var hashArrauy = hashvalue.split('_');
        var galleryId = (typeof hashArrauy[0] !== 'undefined') ? hashArrauy[0] : '';
        var albumId = (typeof hashArrauy[1] !== 'undefined') ? hashArrauy[1] : '';
        var subAlbumId = (typeof hashArrauy[2] !== 'undefined') ? hashArrauy[2] : '';
        var nextHash = "";
        var prevHash = "";
        var nextPreviousLabel = ['',''];
        var nextPreviousSubLabel = ['',''];
        var backButtonSubLabel = '';

        if (galleryId != '' && albumId != '' && subAlbumId != '') {

            var subAlbumDetailsArray = this.processSubAlbum(galleryId, albumId);
            var subAlbumDetailsFiltered = _(subAlbumDetailsArray['albums']).filter(function(x) { return (x['album_id'] == subAlbumId) });
            var currentIndex = subAlbumDetailsFiltered[0]['index'];
            var nextSubAlbumDetails = _(subAlbumDetailsArray['albums']).filter(function(x) { return x['index'] == currentIndex + 1; });
            var prevSubAlbumDetails = _(subAlbumDetailsArray['albums']).filter(function(x) { return x['index'] == currentIndex - 1; });
            if (nextSubAlbumDetails.length > 0) {
                nextHash = galleryId + '_' + albumId + '_' + nextSubAlbumDetails[0]['album_id'];
                nextPreviousLabel[1] = nextSubAlbumDetails[0]['name'];
                nextPreviousSubLabel[1] = galleryNavigationTranslation.WEBSITE_GALLERY_NEXT_SUBALBUM;
            }
            if (prevSubAlbumDetails.length > 0) {
                prevHash = galleryId + '_' + albumId + '_' + prevSubAlbumDetails[0]['album_id'];
                nextPreviousLabel[0] = prevSubAlbumDetails[0]['name'];
                nextPreviousSubLabel[0] = galleryNavigationTranslation.WEBSITE_GALLERY_PREV_SUBALBUM;
            }
            backButtonSubLabel = galleryNavigationTranslation.WEBSITE_GALLERY_BACK_ALBUM;
            var pageTitleBarHideBtns = $.extend(true, {}, pageTitleBarOptions, { nextPrevious: true, nextPreviousLabel: nextPreviousLabel, nextPreviousSubLabel:nextPreviousSubLabel, backButton: true, backButtonLabel: subAlbumDetailsFiltered[0]['parentname'], backButtonSubLabel: backButtonSubLabel });

        } else if (galleryId != '' && albumId != '') {

            var albumDetailsArray = this.processAlbum(galleryId);
            var albumDetailsFiltered = _(albumDetailsArray['albums']).filter(function(x) { return (x['parent_id'] == albumId) });
            var currentIndex = albumDetailsFiltered[0]['index'];
            var nextAlbumDetails = _(albumDetailsArray['albums']).filter(function(x) { return x['index'] == currentIndex + 1; });
            var prevAlbumDetails = _(albumDetailsArray['albums']).filter(function(x) { return x['index'] == currentIndex - 1; });
            if (nextAlbumDetails.length > 0) {
                nextHash = galleryId + '_' + nextAlbumDetails[0]['parent_id']
                nextPreviousLabel[1] = nextAlbumDetails[0]['parentname']
                nextPreviousSubLabel[1] = galleryNavigationTranslation.WEBSITE_GALLERY_NEXT_ALBUM;
            }
            if (prevAlbumDetails.length > 0) {
                prevHash = galleryId + '_' + prevAlbumDetails[0]['parent_id']
                nextPreviousLabel[0] = prevAlbumDetails[0]['parentname']
                nextPreviousSubLabel[0] = galleryNavigationTranslation.WEBSITE_GALLERY_PREV_ALBUM;
            }
            backButtonSubLabel = galleryNavigationTranslation.WEBSITE_GALLERY_BACK_GALLERY;
            var pageTitleBarHideBtns = $.extend(true, {}, pageTitleBarOptions, { nextPrevious: true, nextPreviousLabel: nextPreviousLabel, nextPreviousSubLabel:nextPreviousSubLabel, backButton: true, backButtonLabel: albumDetailsFiltered[0]['parentname'], backButtonSubLabel: backButtonSubLabel });

        } else if (galleryId != '') {

            var galleryDetailsArray = this.processGallery();
            var galleryDetailsFiltered = _(galleryDetailsArray).filter(function(x) { return x['role_id'] == galleryId; });
            var currentIndex = galleryDetailsFiltered[0]['index'];
            var nextGalleryDetails = _(galleryDetailsArray).filter(function(x) { return x['index'] == currentIndex + 1; });
            var prevGalleryDetails = _(galleryDetailsArray).filter(function(x) { return x['index'] == currentIndex - 1; });
            
           if (nextGalleryDetails.length > 0) {
                nextHash = nextGalleryDetails[0]['role_id'];
                nextPreviousLabel[1] = nextGalleryDetails[0].title;
                nextPreviousSubLabel[1] = galleryNavigationTranslation.WEBSITE_GALLERY_NEXT_GALLERY;
            }
            if (prevGalleryDetails.length > 0) {
                prevHash = prevGalleryDetails[0]['role_id'];
                nextPreviousLabel[0] = prevGalleryDetails[0].title;
                nextPreviousSubLabel[0] = galleryNavigationTranslation.WEBSITE_GALLERY_PREV_GALLERY;
            }
            backButtonSubLabel = galleryNavigationTranslation.WEBSITE_GALLERY_BACK_OVERVIEW;
            var pageTitleBarHideBtns = $.extend(true, {}, pageTitleBarOptions, { nextPrevious: true, nextPreviousLabel: nextPreviousLabel, nextPreviousSubLabel:nextPreviousSubLabel, backButton: true, backButtonLabel: pageTitle1, backButtonSubLabel: backButtonSubLabel });
        } else {
            var pageTitleBarHideBtns = $.extend(true, {}, pageTitleBarOptions, {nextPrevious:false, backButton: false});   
        }
        new FgWebsitePageTitleBar('fg-dev-pagetitle-container', pageTitleBarHideBtns);
        $('.fg-page-prev-link').removeAttr('hash-target');
        $('.fg-page-next-link').removeAttr('hash-target');

        if (prevHash != '') {
            $('.fg-page-prev-link').removeClass('fg-disabled-link');
            $('.fg-page-prev-link').attr('hash-target', prevHash);
        } else {
            $('.fg-page-prev-link').addClass('fg-disabled-link');
        }

        if (nextHash != '') {
            $('.fg-page-next-link').removeClass('fg-disabled-link');
            $('.fg-page-next-link').attr('hash-target', nextHash);
        } else {
            $('.fg-page-next-link').addClass('fg-disabled-link');
        }

    }

    public initUniteGallery(galleryId) {
        $(galleryId).unitegallery({
            tiles_type: "justified"
        });
    }
 
    public splitHash() {
        var hash = window.location.hash.substr(1);
        this.hashValue = hash;
        var hashArrauy = hash.split('_');
        this.galleryId = (typeof hashArrauy[0] !== 'undefined') ? hashArrauy[0] : '';
        this.albumId = (typeof hashArrauy[1] !== 'undefined') ? hashArrauy[1] : '';
        this.subAlbumId = (typeof hashArrauy[2] !== 'undefined') ? hashArrauy[2] : '';        
        return hashArrauy;
    }
    public loadPage(hashvalue) {
        
        window.history.pushState({}, "", '#' + hashvalue);
        this.splitHash();
        this.renderTemplate();
    }
    
    public handleAlbumClick() {
        var _this = this;
        $(document).on('click', '.fg-image, .fg-album-title', function() { //click event for album coverimage and album label 
            var hashvalue = $(this).attr('data-hash');
            _this.loadPage(hashvalue);
        });

        $(document).on('click', '.fg-page-close-link', function() {
            var hashvalue = window.location.hash.substr(1);
            var hashArrauy = hashvalue.split('_');
            hashArrauy.pop();
            var hashOut = hashArrauy.join('_');
            _this.loadPage(hashOut);
        });
        $(document).on('click', '.fg-page-next-link, .fg-page-prev-link', function() {
            var hashvalue = $(this).attr('hash-target');
            if (typeof hashvalue != 'undefined' && hashvalue !== '') {
                _this.loadPage(hashvalue);
            }


        });
    }
}