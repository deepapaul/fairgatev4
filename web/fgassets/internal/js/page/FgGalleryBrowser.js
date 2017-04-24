/*
 ================================================================================================ 
 * Custom Plugin for extend gallery browser
 * Function - FgGalleryBrowser - to embed gallery browser into a page 
 ================================================================================================ 
 */

var FgGalleryBrowser = function () {

    galleryData = [];
    var settings;
    var defaultSettings = {
        selector: '.fg-gallery-browser',
        browserUrl: '/swisstennis/internal/gallery/browser',
        galleryDataUrl: '/swisstennis/internal/gallery/gallerydetails',
        coverImagePath: '/uploads/608/gallery/width_300/',
        hasInternalArea: true,
        addFromGalleryText: '',
        addedImagesTemplate: 'imageFromGallery',
        templatePlacementDiv: 'form.fg-nl-form ul',
        addTemplateCallBackFlag: false,
    };
    var selrole = '';
    var seltype = '';

    // extends the initial configuration on method init		
    var initSettings = function (options) {
        settings = $.extend(true, {}, defaultSettings, options);
        galleryData = getGalleryData();
        if(typeof(galleryData)=== 'undefined'){
            console.log(31);
            $('.fg-add-existing-image').hide();
        }
        // If internal area is purchased 
        manageLink();

    };

    //To load role category select box
    var init = function (el, fl) {
        galleryDataExists = getGalleryData();
       if(galleryDataExists.length==0){
            $('.fg-add-existing-image').hide();
        }
        if (el) {
            if (settings.browserUrl !== '') {
                $(el).parent(settings.selector).load(settings.browserUrl);
            }
        } else {
            $(settings.selector).load(settings.browserUrl);
        }



    };

    //Synchronous function to get gallery data.
    var getGalleryData = function () {
        var galData = [];
        $.ajax({
            url: settings.galleryDataUrl,
            async: false,
            dataType: 'json',
            success: function (json) {
                galData = json;
            }
        });
        return galData;
    };

    //Select gallery role type - to show albums from role
    var changeGalleryRole = function (obj) {


        var role = $(obj).attr('id');
        var type = $(obj).attr('data-type');
        var title = $(obj).attr('data-title');
        var row = 1;

        if (type == "CLUB") {

            var data = galleryData.CLUB[role];
        } else if (type == "ROLE") {
            var gData = galleryData.ROLE;
            var data = gData[role];
        } else if (type == "EXTERNAL") {
            var gData = galleryData.EXTERNAL;
            row = 2;
            var data = gData[0][0];
        }

        if (type !== undefined) {
            $('#fg-gallery-sel-role').val(type);
            $('#fg-gallery-sel-role-id').val(role);
            $('#fg-gallery-sel-role-name').val(title);
            selectedrole = role;
            selectedtype = type;
            $('.fg-breadcrum-level1 a').html(title);
            $('.fg-breadcrum-level1 a').attr('data-type',type);
            $('.fg-breadcrum-level1').attr('data-type',type);
            $('.fg-breadcrum-level1').attr('data-title',title);
            $('.fg-breadcrum-level1').show();
            var data = {data: data, type: type, row: row, coverImagePath: settings.coverImagePath};
             renderContent(data, obj);

            updateAlbumStyle($formGroup);
        }


        return;
    };

    //to render album content
    var renderContent = function (data, obj) {
        var content = FGTemplate.bind('gallaryBrowserTemplate', data);
        $formGroup = $(obj).parents(settings.selector);
        $formGroup.find('.fg-album-gallery-list-first-content').html(content);
        $formGroup.find('.fg-album-gallery-list-first-content').show();
        //To hide gallry browser action bar(add, cancel button)
        $('.fg-gal-parent1').hide();
    };

    // Get sub-albums and items from an album
    var selectAlbum = function (element) {

        manageAlbumList(element);

        if (!$(element).hasClass('fg-gallery-album-name')) {
            return;
        }

        var albumId = $(element).attr('data-albumid');
        var albumName = $(element).attr('data-album-title');
        var row = $(element).attr('data-row');
        

        var role = $('#fg-gallery-sel-role-id').val();
        var type = $('#fg-gallery-sel-role').val();
        var gData = (type === "CLUB") ? galleryData.CLUB : galleryData.ROLE;
        $formGroup = $('.fg-gallery-album-wrapper').parent();
        $formGroup.find('.fg-album-gallery-list-first-content').hide();

        //First row item click event
        if (row === '1') {

            // clearAlbumRows(row, $formGroup);
            $('#fg-gallery-sel-album').val(albumId);
            $('#fg-gallery-sel-album-name').val(albumName);
            $('#fg-gallery-sel-level').val(row);
            $('.fg-breadcrum-level2 a').html(albumName);
            $('.fg-breadcrum-level2 a').html(albumName);
            $('.fg-breadcrum-level2').attr('data-title',albumName);
            $('.fg-breadcrum-level2').show();
            var content = FGTemplate.bind('gallaryBrowserTemplate', {data: gData[role][albumId], row: '2', coverImagePath: settings.coverImagePath});
            $formGroup.find('.fg-alb-gallery-scroller-content').show();
            $formGroup.find('.fg-alb-gallery-scroller-content').html(content);
            $formGroup.find('.fg-sub-gallery-scroller-content').hide();
            updateAlbumStyle($formGroup);
        }
        //second or third row click event
        else {

            var subAlbumId = albumId;
            var subAlbumName = albumName;
            $('#fg-gallery-sel-sub-album').val(subAlbumId);
            $('#fg-gallery-sel-sub-album-name').val(subAlbumName);
            $('.fg-breadcrum-level3 a').html(subAlbumName);
            $('.fg-breadcrum-level3').attr('data-title',albumName);
            $('.fg-breadcrum-level3').show();
            var selectedAlbumId = $('#fg-gallery-sel-album').val();
            var subAlbumData = gData[role][selectedAlbumId]['subalbums'][subAlbumId];
            clearAlbumRows(row, $formGroup);
            if (row === '3')
                return;
            var content = FGTemplate.bind('gallaryBrowserTemplate', {data: subAlbumData, row: '3', coverImagePath: settings.coverImagePath});
            $formGroup.find('.fg-alb-gallery-scroller-content').hide();
            $formGroup.find('.fg-sub-gallery-scroller-content').show();
            $formGroup.find('.fg-sub-gallery-scroller-content').html(content);
            $formGroup.find('#fg-p-gl-image').removeClass('hide');
            updateAlbumStyle($formGroup);
            return false;



        }

    };

    // Re-arrange rows 
    var clearAlbumRows = function (row, el) {

        if (row === '1') {

            $formGroup.find('.fg-gallery-album-wrapper.second-row, .fg-gallery-album-wrapper.third-row').hide();
        } else if (row === '2') {
            $formGroup.find('.fg-gallery-album-wrapper.third-row').hide();
        }


    };

    //manage album/image listing 
    var manageAlbumList = function (el) {
        //Click on album/sub album

        if ($(el).hasClass('.fg-gallery-album-name')) {

            $(el).parent('.fg-gallery-items').find('.fg-gal-item.fg-gallery-album').removeClass('selected');
            $(el).addClass('selected');
        } else {

            if ($(el).hasClass('selected')) {
                $(el).removeClass('selected');
            } else {
                $(el).addClass('selected');
            }
        }



    };



    var getChild = function (data, type) {
        if (type != 'role') {
            var res = _.flatten(_.map(data, _.values));
            if (typeof(res[0].images) !== 'undefined'){
                  var getFirstImage = _.flatten(res, 'images');
                    if (typeof(getFirstImage[0].images) !== 'undefined'){
                          var allImages = getFirstImage[0].images; 
                    }else{
                         var allImages = _.flatten(_.pluck(res, 'images')); 
                    }
               
                
            }else{
                var sub = res[0].subalbums;
                var allImages = _.flatten(_.pluck(sub, 'images'));
            }
           
        } else {
            var allImages = _.flatten(_.pluck(data, 'images'));

        }
       
        var imageData = _.filter(allImages, function (num) {
            return typeof num != 'undefined';
        });
        var publicScopeObj = _.findWhere(imageData, {scope: "PUBLIC"});
        var coverObj = _.findWhere(imageData, {isCoverImage: "1"});
        var coverImage = '';
        if (typeof coverObj == 'undefined') {
            if (typeof publicScopeObj !== 'undefined') {
                coverImage = publicScopeObj['filePath'];
            } else if (typeof imageData !== 'undefined' && imageData.hasOwnProperty(0)) {


                coverImage = imageData[0]['filePath'];

            } else {
                var subImage = _.flatten(_.pluck(data, 'subalbums'));
                var subImageData = _.flatten(_.map(subImage, _.values));
                var imageSubDet = _.flatten(_.pluck(subImageData, 'images'));
                var imageDataSub = _.filter(imageSubDet, function (num) {
                    return typeof num != 'undefined';
                });
                var publicScopeObj1 = _.findWhere(imageDataSub, {scope: "PUBLIC"});
                var coverObj1 = _.findWhere(imageDataSub, {isCoverImage: "1"});

                if (typeof coverObj1 == 'undefined') {
                    if (typeof publicScopeObj1 !== 'undefined') {
                        coverImage = publicScopeObj1['filePath'];
                    } else if (typeof imageDataSub !== 'undefined' && imageDataSub.hasOwnProperty(0)) {
                        coverImage = imageDataSub[0]['filePath'];

                    }
                }

            }
        } else {
            coverImage = coverObj['filePath'];
        }

        return coverImage;

    }

    //Render Role listing - select box
    var renderRoleList = function () {
        var coverImagePath = galleryBrowserSettings.coverImagePath;
        $('.fg-breadcrum-level1').hide();
        $('.fg-breadcrum-level2').hide();
        $('.fg-breadcrum-level3').hide();
        if (galleryData.length == 0) {
            $('.select-gallery-role').parent().remove();
            return false;
        }
        var elem = '';
        if (typeof galleryData.EXTERNAL !== 'undefined') {
            var albumData = _.filter(galleryData.ROLE, function (num) {
                return typeof num == 'object';
            });
            var coverImage = FgGalleryBrowser.getCoverImage(galleryData.EXTERNAL[0][0]);

            var fullPathImg = coverImagePath + coverImage;
            $.each(galleryData.EXTERNAL, function (i, v) {

                elem += ' <div class="fg-gallery-album col-md-2 col-sm-3 col-xs-4" id="' + v.roleId + '" data-type="EXTERNAL" data-title="' + v.roleTitle + '" > <div class="fg-image fg-gallery-effect-1" style="background-image:url(\''+ fullPathImg +'\')"> <img src="' + fullPathImg + '"></div> <div class="fg-album-title">' + v.roleTitle + ' </div> </div>';
            });
        }
        if (typeof galleryData.CLUB !== 'undefined') {
            var albumData = _.filter(galleryData.CLUB, function (num) {
                return typeof num == 'object';
            });
            var coverImage = getChild(albumData, 'other');
            var fullPathImg = coverImagePath + coverImage;
            $.each(galleryData.CLUB, function (i, v) {

                elem += ' <div class="fg-gallery-album col-md-2 col-sm-3 col-xs-4" id="' + v.roleId + '" data-type="CLUB" data-title="' + v.roleTitle + '" > <div class="fg-image fg-gallery-effect-1" style="background-image:url(\''+ fullPathImg +'\')"><img src="' + fullPathImg + '"> </div> <div class="fg-album-title">' + v.roleTitle + ' </div> </div>';
            });
        }
        if (typeof galleryData.ROLE !== 'undefined') {
            var roles = {};
            roles['T'] = {};
            roles['W'] = {};
            $.each(galleryData.ROLE, function (i, v) {
                roles[v.roleType][v.sortOrder] = {'roleId': v.roleId, 'roleTitle': v.roleTitle};
            });

            $.each(roles['T'], function (i, v) {
                var teamGallery = _.findWhere(galleryData.ROLE, {roleTitle: v.roleTitle});
                var albumData = _.filter(teamGallery, function (num) {
                    return typeof num == 'object';
                });
                var coverImage = getChild(teamGallery, 'role');
                var fullPathImg = coverImagePath + coverImage;
                elem += ' <div class="fg-gallery-album col-md-2 col-sm-3 col-xs-4" id="' + v.roleId + '" data-type="ROLE" data-title="' + v.roleTitle + '" > <div class="fg-image fg-gallery-effect-1" style="background-image:url(\''+ fullPathImg +'\')"> <img src="' + fullPathImg + '"> </div> <div class="fg-album-title">' + v.roleTitle + ' </div> </div>';

            });
            
           
            $.each(roles['W'], function (i, v) {
                var teamGallery = _.findWhere(galleryData.ROLE, {roleId: v.roleId});
                var albumData = _.filter(teamGallery, function (num) {
                    return typeof num == 'object';
                });
               
                var coverImage = getChild(teamGallery, 'role');
                var fullPathImg = coverImagePath + coverImage;
                if(clubExecutive==parseInt(v.roleId)){
                      v.roleTitle = executiveTitle;
                  }
                elem += ' <div class="fg-gallery-album col-md-2 col-sm-3 col-xs-4" id="' + v.roleId + '" data-type="ROLE" data-title="' + v.roleTitle + '" > <div class="fg-image fg-gallery-effect-1" style="background-image:url(\''+ fullPathImg +'\')"> <img src="' + fullPathImg + '"> </div> <div class="fg-album-title">' + v.roleTitle + ' </div> </div>';

            });
        }
   
        $('.select-gallery-role').html(elem);


    };

    // Get cover image 
    var getCoverImage = function (d) {
        var coverObj = _.findWhere(d.images, {isCoverImage: "1"});
        var publicScopeObj = _.findWhere(d.images, {scope: "PUBLIC"});
        var coverImage = '';
        if (typeof coverObj == 'undefined') {
            if (typeof publicScopeObj !== 'undefined') {
                coverImage = publicScopeObj['filePath'];
            } else if (typeof d.images !== 'undefined') {
                coverImage = d.images[0]['filePath'];
            }
        } else {
            coverImage = coverObj['filePath'];
        }
        return coverImage;
    };

    // Get subalbum cover image 
    var getSubalbumCoverImage = function (d) {
        //get cover image of first sub album
        coverImage = getCoverImage(d.subalbums[Object.keys(d.subalbums)[0]]);
        return coverImage;
    };

    //show add from server link
    var showLink = function (el, flag) {

        $obj = $(settings.selector);
        if (el) {
            $obj = $(el).parents('.form-group').find(settings.selector);
        }
        $obj.html('<a class="pull-left fg-marg-top-15 add-from-server-link" href="#">' +
                '<i class="fa fa-plus-circle fa-2x pull-left"></i>' +
                '<span class="fg-add-text">' + addFromServerText + '</span></a>');


    };

    //manageLink
    var manageLink = function (el) {

        FgGalleryBrowser.init();


    };

    //hide gallery area
    var hideGallery = function (el) {
        var galleyElem = $(el).parents('.fg-gallery-browser');
        galleyElem.find('.fg-gallery-scroller-content').html('');
        galleyElem.find('a.add-from-server-link').removeClass('hide');
        if ($('li.fg-gallery-item').is(':visible')) {
             galleyElem.find('#fg-p-gl-image').removeClass('hide');
        } else {
            galleyElem.find('#fg-p-gl-image').addClass('hide');
        }
        var modalId = $(".fg-gallery-browser.modal.in").attr("id");
        $currentModal = $('#' + modalId)
        $currentModal.modal('hide');
        if (settings.hasInternalArea) {
            galleyElem.find('select.select-gallery-role').select2("val", null);
        }
        
        enableButtonClick(el);

    };

    //Set sortable to each container (el will be jQuery object)
    var setSortable = function (el) {
        el.sortable({
            handle: '.fg-media-sort',
            update: function (event, ui) {
                updateSortOrder(this);
            }
        });
    };

    //update sort order
    var updateSortOrder = function (el) {
        $.each($(el).find('li.fg-image-area'), function (i, obj) {

            $(obj).find('input.image-order').val(i + 1);
        });
        FgDirtyFields.updateFormState();
    };

    //Set sortable to each container (el will be jQuery object)
    var refreshSortable = function (el) {
        el.sortable("refresh");
    };


    //Append images to the content. when save button click el->button object
    var appendImages = function (el) {

        //$formGroup = $(el).parents('.form-group');
        $formGroup = $('.fg-gallery-album-wrapper').parent();

        $images = $formGroup.find('li.fg-gallery-item.selected:not(.fg-gallery-album)');
        var modalId = $(".fg-gallery-browser.modal.in").attr("id");
        $formGroupModal = $('#' + modalId).parent();

        var items = [];

        $.each($images, function (i, v) {
            var itemId = $(v).attr('data-itemid');
            if ($('#' + modalId).hasClass('fg-nl-fullwidth-image-gallery')) {
                $imgHidden = $formGroupModal.find('.hidden_fullwidth_image');
                $imgHidden.val($(v).attr('data-filepath'));
              
            }


            items.push({
                itemId: $(v).attr('data-itemid'),
                imgPath: $(v).find('img.fg-img').attr('src'),
                fileSize: $(v).attr('data-fileSize'),
                itemDescription: $(v).attr('data-itemDescription')
            });
        });

        //$formGroupModal = $('.fg-gal-browse-model.in').parent();

        var dataName = $formGroupModal.find('form.fg-nl-form').attr('data-name');

        var imageCount = $formGroupModal.find('form.fg-nl-form ul li').length;   //current image count

        var sortableElement = $formGroupModal.find('form.fg-nl-form ul.fg-image-area-container');
        refreshSortable(sortableElement);

        var html = FGTemplate.bind(settings.addedImagesTemplate, {data: items, dataName: dataName, imageCount: imageCount});
        if ($('#' + modalId).hasClass('fg-nl-fullwidth-image-gallery')) {
            imageFromGalleryFullWidth
             var html = FGTemplate.bind('imageFromGalleryFullWidth', {data: items, dataName: dataName, imageCount: imageCount});
            $formGroupModal.find(settings.templatePlacementDiv).html(html);
        } else {
            $formGroupModal.find(settings.templatePlacementDiv).append(html);
        }


        $(".fg-gallery-browser.modal.in").hide();
        FgGalleryBrowser.hideGallery(el);
        if (settings.addTemplateCallBackFlag) {
            console.log(items);
            console.log(settings.addTemplateCallBack);
            settings.addTemplateCallBack.call({}, items);
        }
        //manageLink(el);

    };

    // To update album style
    var updateAlbumStyle = function (el) {
        $formGroup =$('.fg-gallery-album-wrapper').parent();
        FgGalleryBrowser.updateBreadCrumb();
        $formGroup.find('ul.fg-gallery-breadcrumb  li:visible').removeClass('last'); 
        $formGroup.find('ul.fg-gallery-breadcrumb  li:visible:last').addClass('last'); 
        $formGroup.find('ul.fg-gallery-breadcrumb  li:visible ').wrapInner('<a href="#" />');
        $formGroup.find('ul.fg-gallery-breadcrumb  li:visible:last a').contents().unwrap();
        var title = $formGroup.find('ul.fg-gallery-breadcrumb  li:visible:last').attr("data-title");
        $formGroup.find('ul.fg-gallery-breadcrumb  li:visible:last').text(title);
        
        
        enableButtonClick(el);

    };
    var updateBreadCrumb = function () {
        
            var listItems = $("ul.fg-gallery-breadcrumb li");
            listItems.each(function(idx, li) {
            var breadCrumbId = $(li).attr("id");
            var title = $('#'+breadCrumbId).attr("data-title");
         
             if ($('#'+breadCrumbId).find("a span").length > 0) {
               
                $('#'+breadCrumbId +' span').text(title); 
             }
            else if ($('#'+breadCrumbId).find("a").length > 0) {
                
                 $('#'+breadCrumbId +' span').text(title); 
            }else if( $('#'+breadCrumbId).children().length > 0){
                $('#'+breadCrumbId).children().text(title);
            }else{
                $('#'+breadCrumbId).text(title);
            }
          });
        
        
    }
    
   var enableButtonClick = function () {
        $formGroupParent =$('.fg-gallery-album-wrapper:visible').parents('div.modal-body').siblings('div.modal-footer');
        $formGroup =$('.fg-gallery-album-wrapper:visible').parent();;
        $formGroupParent.find('.form-actions a:first').addClass('disabled');
        if($formGroup.find('li.fg-gallery-item')) {
           $formGroup.find('#fg-p-gl-image').removeClass('hide'); 
        }
        if($formGroup.find('li.fg-gallery-item').length==0){
          $formGroup.find('#fg-p-gl-image').addClass('hide');  
        }  
       
        if($formGroup.find('li.fg-gallery-item').hasClass('selected')){
          $formGroupParent.find('.form-actions a:first').removeClass('disabled');  
        }else{
           $formGroupParent.find('.form-actions a:first').addClass('disabled'); 
        }
    };

    return {
        // initialize gallery browser settings
        initialize: function (options) {
            initSettings(options);
        },
        // load gallery browser
        init: function (d, f) {

            init(d, f);
        },
        changeGalleryRole: function (object) {
            return changeGalleryRole(object);
        },
        selectAlbum: function (d) {
            selectAlbum(d);
        },
        renderRoleList: function () {
            renderRoleList();
        },
        getCoverImage: function (d) {
            return getCoverImage(d);
        },
        getSubalbumCoverImage: function (d) {
            return getSubalbumCoverImage(d);
        },
        appendImages: function (d) {
            appendImages(d);
        },
        showLink: function (d) {
            showLink(d);
        },
        setSortable: function (d) {
            setSortable(d);
        },
        manageLink: function (d) {
            manageLink(d);
        },
        renderContent: function (d, o) {
            renderContent(d, o);
        },
        hideGallery: function (el) {
            hideGallery(el);
        },
      updateAlbumStyle:function(el){
          updateAlbumStyle(el);
      },
      enableButtonClick:function(){
          enableButtonClick();
      },
      updateBreadCrumb:function(){
          updateBreadCrumb();
      },
    };




}();

//Document ready function
$(function () {

    $(document).on('click', 'div.fg-gallery-album', function () {
        FgGalleryBrowser.changeGalleryRole(this);
    });

    $(document).on('click', 'li.fg-gallery-item', function () {
        var modalId = $(".fg-gallery-browser.modal.in").attr("id");
        mdId = modalId;
        $('#'+mdId).find('.form-actions a').removeClass('disabled');
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            if ($('#' + modalId).hasClass('fg-nl-fullwidth-image-gallery')) {
                $('li.fg-gallery-item').removeClass('selected');
            }
            $(this).addClass('selected');
        }
        FgGalleryBrowser.enableButtonClick();
        
    });

    //Gallery item click function
    $(document).on('click', '.fg-sub-gallery-item', function () {

        FgGalleryBrowser.selectAlbum(this);


    });
    $('.fg-gallery-browser.modal.in').on('hidden', function () {
        
        $(this).data('modal', null);
        //form-actions a
    });
    ;

    //Save         
    $(document).on('click', '.gallery_browser_save', function () {

        FgGalleryBrowser.appendImages(this);

    });
    //Cancel         
    $(document).on('click', '.gallery_browser_cancel', function () {
        //FgGalleryBrowser.manageLink(this);

        FgGalleryBrowser.hideGallery(this);
    });
    //Remove item         
    $(document).on('click', '.deletediv.fromGallery', function () {

        $(this).parents('.fg-image-area').remove();
    });

    $(document).on('click', '.fg-gallery-breadcrumb-li', function () {
        var paramId = $(this).attr("id");
        var modalId = $(".fg-gallery-browser.modal.in").attr("id");
        $currentModal = $('#' + modalId)
        
        if (paramId == "fg-breadcrum-level0") {
            $currentModal.find('.fg-gal-parent1').show();
            $currentModal.find('.fg-sub-gallery-scroller-content').hide();
            $currentModal.find('.fg-alb-gallery-scroller-content').hide();
            $currentModal.find('.fg-album-gallery-list-first-content').hide();
            $currentModal.find('#fg-breadcrum-level1').hide();
            $currentModal.find('#fg-breadcrum-level2').hide();
            $currentModal.find('#fg-breadcrum-level3').hide();
           
        } else if (paramId == "fg-breadcrum-level1") {
            $currentModal.find('.fg-gal-parent1').hide();
            $currentModal.find('.fg-sub-gallery-scroller-content').hide();
            $currentModal.find('.fg-alb-gallery-scroller-content').hide();
            $currentModal.find('.fg-album-gallery-list-first-content').show();
            $currentModal.find('#fg-breadcrum-level1').show();
            $currentModal.find('#fg-breadcrum-level2').hide();
            $currentModal.find('#fg-breadcrum-level3').hide();
           
            
        } else if (paramId == "fg-breadcrum-level2") {
            $currentModal.find('.fg-gal-parent1').hide();
            $currentModal.find('.fg-sub-gallery-scroller-content').hide();
            $currentModal.find('.fg-alb-gallery-scroller-content').show();
            $currentModal.find('.fg-album-gallery-list-first-content').hide();
            $currentModal.find('#fg-breadcrum-level1').show();
            $currentModal.find('#fg-breadcrum-level2').show();
            $currentModal.find('#fg-breadcrum-level3').hide();
            
        } else if (paramId == "fg-breadcrum-level3") {
            $currentModal.find('.fg-gal-parent1').hide();
            $currentModal.find('.fg-sub-gallery-scroller-content').show();
            $currentModal.find('.fg-alb-gallery-scroller-content').hide();
            $currentModal.find('.fg-album-gallery-list-first-content').hide();
            $currentModal.find('#fg-breadcrum-level3').show();
            
        }
       
        FgGalleryBrowser.updateAlbumStyle();

    });

    $(document).on('click', 'a.add-from-server-link', function (event) {
        event.preventDefault();
        // FgGalleryBrowser.init(this);
        var gData = galleryData.EXTERNAL;
        var data = {data: gData[0][0], type: "EXTERNAL", row: 2, coverImagePath: settings.coverImagePath};
        FgGalleryBrowser.renderContent(data, this);
        $(this).addClass('hide');
    });


});

$(window).on('show.bs.modal', function (e) {
    setTimeout(function(){
        var popupId = $('.fg-gallery-browser.modal.in').attr("id");
         $("#"+popupId).find('li.fg-gallery-item').removeClass('selected');
         $("#"+popupId).find('ul.fg-gallery-breadcrumb li.fg-breadcrum-level0').click();
    }, 300);                
    
});
function  showPopupCallback() {
    $('.fg-gallery-browser.modal.in').on('shown.bs.modal', function () {
        
        $(this).find('li.fg-gallery-item').removeClass('selected');
        $(this).find('ul.fg-gallery-breadcrumb li.fg-breadcrum-level0').click();
        return false;
        
        
        //.form-actions a
    });
    $('.fg-gallery-browser.modal.in').on('show.bs.modal', function () {
          console.log($(this).attr("id"));
       FgGalleryBrowser.enableButtonClick();

    });
}




  