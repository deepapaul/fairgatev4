var galleryImageLoadOnGoing = false;

$(window).on('beforeunload', function () {
    $(window).scrollTop(0);
});
  $(document).mousemove(function(e){
    window.mousePosition = {mouseY :e.pageY,mouseX : e.pageX};
  });
$(function () {
    $(this).scrollTop(0);
    //Set default view mode -justify
    if(localStorage.getItem(FgLocalStorageNames.gallery.currentViewMode) == null)
        localStorage.setItem(FgLocalStorageNames.gallery.currentViewMode, 'justify');
    //Set default album
    if(localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum) == null)
        localStorage.setItem(FgLocalStorageNames.gallery.selectedAlbum,'ALL');

    //To load gallery images
    FgGalleryView.loadGallery();
    
    //settings for sidebar
    FgSidebar.frompage = 'gallery'

    // Click event for album view switch button
    $('.fg-action-gallery-mode span').click(function () {
        $this = $(this);
        if ($this.attr('data-type') == 'grid')
            FgGalleryView.switchGalleryView('justify');
        else
            FgGalleryView.switchGalleryView('grid');
    });

    // Click event for selectAll button
    $('.fg-action-select-all span').click(function () {
        FgGalleryView.toggleSelectAllBtn();
        FgGalleryView.actionMenuUpdate();
    });


});

var FgGalleryView = {
    pagelimit:100,
    imageResult:{},
    /**
     * apply action menu
     * @param {int}    isAdmin    is-admin
     * @param {string} switchType grid/justify  grid for admin-mode
     */
    handleActionMenu: function(isAdmin, switchType) {        
        
        var galMode =(isAdmin=='1')?true:false;
        var actionmenuMode = (switchType == 'grid') ? true : false;
        $(".fg-action-menu-wrapper").FgPageTitlebar({
            actionMenu: actionmenuMode,
            title: true,
            galleryMode : galMode,
            selectAll : true
        });
    
        //init action menu functions
        FgActionmenuhandler.init(); 
        
        var sidebarSelectedAlbum = localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum);
        scope = angular.element($("#BaseController")).scope();
        scope.$apply(function () {
            if (sidebarSelectedAlbum == "ORPHAN") {
                scope.menuContent = actionMenuNoAlbumText;
            }else if (sidebarSelectedAlbum == "EXTERNAL") {
                scope.menuContent = actionMenuExternal;
            }
            else {
                scope.menuContent = actionMenuText;
            }
            scope.menuType = 0;
        });
        
    },
    
    /**
     * To load gallery items
     * 
     */
    loadGallery: function () {
        //To remove gallery video wrapper(Video not playing while calling loadGallery() 2nd time).
        $('.ug-gallery-wrapper.ug-lightbox').remove();
        
        fgGalleryUploader.handleDiscardButton();
        FgGalleryView.hideSelectallAndChangemodeBtn();
        //apply action menu
        FgInternal.pageLoaderOverlayStart('page-container');
        var albumId = localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum);
        $.ajax({
            type: "POST",
            url: galleryDatailsURL,
            data: {albumId: albumId},
            success: function(result){
                FgGalleryView.imageResult = result;
                
                //to handle drag-drop
                var loadedImageCount = $('#gallery-container').attr('data-loaded-image-count');
                loadedImageCount = (typeof loadedImageCount == 'undefined')?FgGalleryView.pagelimit:parseInt(loadedImageCount);
                $('#gallery-container').removeAttr('data-loaded-image-count');
                FgGalleryView.gallerySuccessCallback(loadedImageCount);                
            },
            dataType: 'json'
          });
    },
    gallerySuccessCallback: function (imagesToShow) {

        imagesToShow = (imagesToShow >= 0)?imagesToShow:FgGalleryView.pagelimit;
        
        var result = $.extend({}, FgGalleryView.imageResult);
        var randId = _.random(0,999);
        var loadedImageCount = $('#gallery-container').attr('data-loaded-image-count');
        loadedImageCount = (typeof loadedImageCount == 'undefined')?0:parseInt(loadedImageCount);
        toBeloadedImageCount = loadedImageCount + imagesToShow;
        var albumId = localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum);
        if (result.isAdmin == '0' || albumId == 'ALL') {
            localStorage.setItem(FgLocalStorageNames.gallery.currentViewMode, 'justify');
        } else if (albumId == 'ORPHAN' || albumId == 'EXTERNAL') {
            localStorage.setItem(FgLocalStorageNames.gallery.currentViewMode, 'grid');
        }

        var switchType = localStorage.getItem(FgLocalStorageNames.gallery.currentViewMode);
        var galleryElement = (switchType == 'grid') ? 'gallaryAdminTemplate' : 'gallaryNonAdminTemplate';
        result.data = result.data.slice(loadedImageCount,toBeloadedImageCount);
        $('#gallery-loader').remove();
       
        if (switchType == 'grid' && loadedImageCount > 0) {
            var content = FGTemplate.bind('gallaryAdminLoadmoreTemplate', {'data': result});
            $('.fg-temp-placeholder').remove();
            $('.fg-gallery-items').append(content);
        } else {
            var content = FGTemplate.bind(galleryElement, {'data': result,'randId':randId});
            if (switchType == 'grid') {
                $('#gallery-container').html(content);
            } else {
                $('#gallery-container').append(content);
            }
        }
        
        if (toBeloadedImageCount < FgGalleryView.imageResult.data.length) {
            var loadContent = FGTemplate.bind('gallaryLoadmoreTemplate');
            $('#gallery-container').append(loadContent);
        }

        $('#gallery-container').attr('data-loaded-image-count', toBeloadedImageCount);
        
        if (switchType == 'justify') {
            var galleryOptions = {'selector' : '#gallery-'+(randId)}
            fgUniteGalleryObj = FgGallery.init(galleryOptions);
            setTimeout(function(){ 
                var width = $('#fg-wrapper').width()
                fgUniteGalleryObj.resize(width)}, 5000);
            FgFileUpload.disableFileUpload();
        } else {
            $('#gallery-container .fg-gallery-items').append('<div class="fg-temp-placeholder" style="float: left;width: 50px;height: 100px;"></div>');
            FgGalleryView.selectimage();
            //in 'all images' menu sorting and move to album functionality is not required
            if (albumId != 'ORPHAN' && albumId != 'ALL' && albumId != 'EXTERNAL') { //only move and sort functionality needed
                FgGalleryView.dragAndDropItems();
            } else if (albumId == 'ORPHAN' || albumId == 'EXTERNAL') { //only move functionality needed
                FgGalleryView.dragToSidebar();
            }

            FgFileUpload.enableFileUpload()
        }

        FgGalleryView.handleActionMenu(result.isAdmin, switchType);
        FgGalleryView.handleEmptyDataActionMenu();
        FgInternal.pageLoaderOverlayStop('page-container');

        FgGalleryView.changeSwitchBtn(switchType, result.isAdmin);
        FgGalleryView.selectAllBtnHandler();
    },
    /**
     * To load gallery items
     * 
     */
    switchGalleryView: function (type) {
        $('#gallery-container').html('');
        $('#gallery-container').removeAttr('data-loaded-image-count');
        localStorage.setItem(FgLocalStorageNames.gallery.currentViewMode, type);
        FgGalleryView.loadGallery();
    },
    /**
     * To change admin mode switch button
     * 
     */
    changeSwitchBtn: function (type, isAdmin) {
        var content = (type == 'grid') ? leaveAdminModeText : adminModeText;
        var iclass = (type == 'grid') ? ' fa-times-circle-o' : 'fa-pencil-square-o';
        var str = '<i class="fa ' + iclass + ' fa-2x"></i> ' + content;
        $switchBtn = $('.fg-action-gallery-mode span');
        $switchBtn.attr('data-type', type);
        $switchBtn.html(str);
        var albumId = localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum);
        if (albumId  == 'ALL' || albumId  == 'ORPHAN' ||albumId  == 'EXTERNAL' || isAdmin==0){
            $('.fg-action-gallery-mode').removeClass('fg-active-IB');
            $('.fg-action-gallery-mode').addClass('fg-dis-none');
        }
        else{
            $('.fg-action-gallery-mode').removeClass('fg-dis-none');
            $('.fg-action-gallery-mode').addClass('fg-active-IB');
        }
        
    },
    /**
     * To hide select-all & change-mode button
     * 
     */
    hideSelectallAndChangemodeBtn:function(){
        //hide change mode btn
        $('.fg-action-gallery-mode').removeClass('fg-active-IB');
        $('.fg-action-gallery-mode').addClass('fg-dis-none');
        //hide select all btn
        $('.fg-action-select-all').removeClass('fg-active-IB');
        $('.fg-action-select-all').addClass('fg-dis-none');
    },
    
    //handle datatable in case of empty data
    handleEmptyDataActionMenu: function () {
        var datatableRowCnt = $("div.fg-gallery-img-wrapper").length;
        if (datatableRowCnt <= 0) {
            $('.fgContactdrop').next('.action-drop-menu').addClass('fg-dev-table-empty');
        } else {
            $('.fgContactdrop').next('.action-drop-menu').removeClass('fg-dev-table-empty');
        }
    },
    /* Action menu update according to selected items */
    actionMenuUpdate: function () {
        var count = $("div.fg-gallery-img-wrapper.selected").length;
        var imgScope = $("div.fg-gallery-img-wrapper.selected").attr('data-scope');
        if(imgScope=='INTERNAL'){
                actionMenuText.active.single.gallerySetCoverImage.isActive='false';
        }else{
                actionMenuText.active.single.gallerySetCoverImage.isActive='true';
                
        }
        if (count <= 0) {
            scope.$apply(function () {
                scope.menuType = 0;
            });
        } else if (count === 1) {
            scope.$apply(function () {
                scope.menuType = 1;
            });
        } else {
            scope.$apply(function () {
                scope.menuType = 2;
            });
        }
        var sidebarSelectedAlbum = localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum);
        if(sidebarSelectedAlbum == 'EXTERNAL'){
            scope.menuContent = actionMenuExternal;
        } else if(sidebarSelectedAlbum == 'ORPHAN') {
            scope.menuContent = actionMenuNoAlbumText;
        }else{
            scope.menuContent = actionMenuText;
        }
    },
    /**
     * Show modal popups
     * @param {string} checkedIds comma separated checked ids
     * @param {string} selected   selected/all
     * @param {string} modalType  can be CHANGE_SCOPE/REMOVE_IMAGE/MOVETO_ALBUM/DELETE_IMAGE
     * @param {json}   params     json data of current status like currentScope, albumName ...
     */
    showConfirmationPopup: function (checkedIds, selected, modalType, params) {   
        $.post(pathGalleryModal, {'checkedIds': checkedIds, 'selected': selected, 'modalType': modalType, 'params': params}, function (data) {                 
            FgModelbox.showPopup(data);
            //uniform radio buttons in change scope
            FgFormTools.handleUniform();
        });
    },
    /**
     * function for changing scope of album item
     * @param {string} checkedIds comma separated item ids
     * @param {string} scope      INTERNAL/PUBLIC
     */
    saveScope: function (checkedIds, scope) {
        var params = {'checkedIds': checkedIds, 'scope': scope};
        FgXmlHttp.post(pathGalleryChangeScope, params, '', FgGalleryView.saveScopeCallBack, false, false);
    },
    /*save scope type function */
    saveSortType: function (type, albumId) {
        var params = {'sortTtype': type, 'albumId': albumId};
        FgXmlHttp.post(pathGalleryChangeSort, params, false, FgGalleryView.saveSortTypeCallBack, false, false);

    },
    /*save sort type callback function */
    saveSortTypeCallBack: function () {
        FgModelbox.hidePopup();
        FgGalleryView.loadGallery();
    },
    /*save scope call back function */
    saveScopeCallBack: function (data) {
        var scope = data.scope;
        var checkedIds = data.checkedIds.split(",");
        $.each(checkedIds, function (index, checkedId) {
            $('div.fg-gallery-img-wrapper[data-itemid=' + checkedId + ']').attr('data-scope', scope);
            $('div.fg-gallery-img-wrapper[data-itemid=' + checkedId + ']').removeClass('fg-gallery-scope-lock');
            if(scope == "INTERNAL") {
               $('div.fg-gallery-img-wrapper[data-itemid=' + checkedId + '] .fa-asterisk' ).remove();
               $('div.fg-gallery-img-wrapper[data-itemid=' + checkedId + ']').addClass('fg-gallery-scope-lock');     
            }
        });
        $("div.fg-gallery-img-wrapper.selected").removeClass('selected');
        FgModelbox.hidePopup();
        //reset action menu
        var sidebarSelectedAlbum = localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum);        
        var isadmin = (sidebarSelectedAlbum == 'ORPHAN') ? 0 : 1;         
        FgGalleryView.handleActionMenu(isadmin, 'grid');
        FgGalleryView.handleEmptyDataActionMenu();
        FgGalleryView.toggleSelectAllBtn(true);
    },
    /*select thumbnail image in admin view */
    selectimage: function () {
         $('.fg-gallery-admin-wrapper .fg-gallery-img-wrapper').off('click');
        $('.fg-gallery-admin-wrapper .fg-gallery-img-wrapper').click(function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            } else {
                $(this).addClass('selected');
            }
            FgGalleryView.actionMenuUpdate();
        });
    },
    /*remove items from gallery*/
    removeItems: function (checkedIds) {
        var params = {'checkedIds': checkedIds};
        FgXmlHttp.post(pathGalleryRemove, params, '', FgGalleryView.removeItemsCallBack, false, false);
    },
    /*set cover image for album*/
    setCoverImage: function (checkedIds) {
        var params = {'checkedIds': checkedIds};
        FgXmlHttp.post(pathGalleryCoverImage, params, '',FgGalleryView.setCoverImageCallBack, false, false);
    },
  /*set ccover image call back function */
    setCoverImageCallBack: function(data) {
        var checkedIds = data.checkedIds;
        $( "div.fg-gallery-img-wrapper .fa-asterisk" ).remove();
        $( "div.fg-gallery-img-wrapper.selected" ).append('<i class="fa fa-asterisk fg-album-cover-icon"></i>');
        $( "div.fg-gallery-img-wrapper.selected" ).removeClass('selected');
        FgModelbox.hidePopup(); 
        FgGalleryView.handleActionMenu(1, 'grid');
    },
    /*remove items call back function */
    removeItemsCallBack: function (data) {
        var checkedIds = data.checkedIds.split(",");
        $.each(checkedIds, function (index, checkedId) {
                $('div.fg-gallery-img-wrapper[data-albumitemid=' + checkedId + ']').remove();
        });
        $("div.fg-gallery-img-wrapper.selected").removeClass('selected');
        FgModelbox.hidePopup();
        //reset action menu
        FgGalleryView.handleActionMenu(1, 'grid');
        FgGalleryView.handleEmptyDataActionMenu();
        FgGalleryView.toggleSelectAllBtn(true);
    },
    /* Handle drag&drop for gallery images (sorting and moving to album is handles here )*/
    dragAndDropItems: function () {  
        $('.fg-gallery-items').sortable({   
            start: function (event, ui) {                
                ui.item.addClass('selected');  
                $("body").addClass('fg-dev-drag-active');
            },           
            out: function (event, ui) {
                $(this).off("sortupdate");
                $('.fg-gallery-img-wrapper-drag').addClass('fg-helper-no-thumb');
                $('.fg-gallery-img-wrapper-drag div').removeClass('show').addClass('hide');
            },
            cursorAt: { top: 50, left: 40 },
            over: function (event, ui) {  
                $('.fg-helper-no-thumb').removeClass('fg-helper-no-thumb');
                $('.fg-gallery-img-wrapper-drag div').removeClass('hide').addClass('show');
                
                $( this ).on( "sortupdate", function( event, ui ) {
//                    in orphaned and all images only move to album is needed. sorting is not needed
                    $('#gallery-container .fg-gallery-items .fg-temp-placeholder').remove();
                    
                    ui.item.after($(".fg-gallery-img-wrapper.selected"));
                    //put a loader
                    var data = [];
                    $(".fg-gallery-img-wrapper.selected").each(function (index, value) {
                        sortOrder = $(".fg-gallery-img-wrapper").index($(value));
                        data[index] ={};
                        data[index].albumItemId = $(value).attr('data-albumitemid');
                        data[index].sortOrder = sortOrder+1;
                    });
                    _.sortBy(data, 'sortOrder');
                    
                    var albumId = localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum);
                    $('.fg-gallery-items .fg-gallery-img-wrapper-drag').replaceWith($(".fg-gallery-img-wrapper.selected"));
                    $(".fg-gallery-img-wrapper.selected").removeClass('selected');
                    $("body").removeClass('fg-dev-drag-active'); 
                    
                    FgXmlHttp.post(sortingURL, {'data': data, albumId:albumId}, '', function(){FgGalleryView.loadGallery();}, false, false);
                       
                } );
            },
            helper: function (event, ui) {
                if ($("div.fg-gallery-img-wrapper.selected").length > 0) {
                    count = $("div.fg-gallery-img-wrapper.selected").length;
                } else {
                    ui.addClass('selected');
                    count = 1;
                }
                
                var selectedImage = $('<div class="show fg-gallery-img-wrapper" style="border:none;margin:0;"></div>').append($("div.fg-gallery-img-wrapper.selected").first().find('img').prop('outerHTML')).prop('outerHTML');
                if(typeof selectedImage == 'undefined')
                    selectedImage = '';
//                var off = ui.offset();
//                var positionDifferenceY = window.mousePosition.mouseY - off.top;
//                var positionDifferenceX = window.mousePosition.mouseX - off.left - 50;
                    //return $("<div class='ui-widget-header fg-gallery-img-wrapper-drag' style='margin-top:"+positionDifferenceY+"px; margin-left:"+positionDifferenceX+"px;'><span class='fg-drag-count'>" + count + "</span>"+selectedImage+"</div>");
                    return $("<div class='ui-widget-header fg-gallery-img-wrapper-drag'><span class='fg-drag-count'>" + count + "</span>"+selectedImage+"</div>");
            }
            
        });
        FgSidebar.droppableEventIconHandlingForGallery('gallery');
        
    }, 
    
    dragToSidebar: function () {        
        $(".fg-gallery-img-wrapper").draggable({
            connectToSortable: ".fg-gallery-items",
            containment: "#page-container",
            start: function (event, ui) {  
                $("body").addClass('fg-dev-drag-active');                
            },  
            stop: function (event, ui) {  
                $("body").removeClass('fg-dev-drag-active');                
            },  
            cursorAt: { top: 25, left: 50 },
            helper: function (event) {
                if ($("div.fg-gallery-img-wrapper.selected").length > 0) {
                    count = $("div.fg-gallery-img-wrapper.selected").length;
                } else {
                    $(this).addClass('selected');
                    count = 1;
                }
                return $("<div class='ui-widget-header fg-gallery-img-wrapper-drag fg-helper-no-thumb'  ><span class='fg-drag-count'>" + count + "</span></div>");
            },
        });
        FgSidebar.droppableEventIconHandlingForGallery('gallery');
    },
    
    /**
     * function for changing scope of album item
     * @param {string} checkedIds comma separated item ids
     * @param {string} albumId    album to which items to move
     */
    moveToAlbum: function (checkedIds, albumId) {
        var params = {'checkedIds': checkedIds, 'albumId': albumId};
        FgXmlHttp.post(pathGalleryMoveAlbum, params, '', FgGalleryView.moveToAlbumCallBack, false, false);
    },
    
     /*remove items call back function */
    moveToAlbumCallBack: function (data) {
        FgModelbox.hidePopup();
        //To load gallery images
        FgGalleryView.loadGallery();
    },
    
     /*delete items from gallery*/
    deleteItems: function(checkedIds, params) {
        var selectedAlbum = localStorage.getItem(FgLocalStorageNames.gallery.selectedAlbum);
        var params = {'checkedIds': checkedIds, selectedAlbum : selectedAlbum };
        FgXmlHttp.post(pathGalleryDelete, params, '', FgGalleryView.deleteItemsCallBack, false, false);
    },
    
    /*delete items call back function */
    deleteItemsCallBack: function (data) {
        if(data.checkedIds) {
            var checkedIds = data.checkedIds.split(",");
            $.each(checkedIds, function (index, checkedId) {
                $('div.fg-gallery-img-wrapper[data-itemid=' + checkedId + ']').remove();
            });
        }
        $("div.fg-gallery-img-wrapper.selected").removeClass('selected');
        FgModelbox.hidePopup();
        //reset action menu
        FgGalleryView.handleActionMenu(0, 'grid'); //0 for to hide leave admin mode button
        FgGalleryView.handleEmptyDataActionMenu();
        FgGalleryView.toggleSelectAllBtn(true);
    },
    /*SelectAll button handler*/
    
    selectAllBtnHandler:function(){
        var switchType = localStorage.getItem(FgLocalStorageNames.gallery.currentViewMode);
        $('.fg-action-select-all').removeClass('fg-action-disabled');
        if(switchType == 'justify'){
            //Hide the select all
            $('.fg-action-select-all').removeClass('fg-active-IB');
            $('.fg-action-select-all').addClass('fg-dis-none');
        } else  {
            //Show the select all
            $('.fg-action-select-all').removeClass('fg-dis-none');
            $('.fg-action-select-all').addClass('fg-active-IB');
            if($('.fg-gallery-img-wrapper').length ==0){
                $('.fg-action-select-all').addClass('fg-action-disabled');
            }
            FgGalleryView.toggleSelectAllBtn(true);
        }
        
    },
    /*
     * Toggle select all button
     */
    toggleSelectAllBtn:function(flag){
        
        var elSelAll = $('.fg-action-select-all span'); 
        if(flag==true) elSelAll.addClass('deSel');
        
        if(elSelAll.hasClass('deSel')){
            $('#gallery-container .fg-gallery-img-wrapper').removeClass('selected');
            elSelAll.html('<i class="fa fa-th fa-2x"></i> '+selectAllTrans);
            elSelAll.removeClass('deSel');
        }else{
            $('#gallery-container .fg-gallery-img-wrapper').addClass('selected');
            elSelAll.html('<i class="fa fa-th fa-2x"></i> '+deselectAllTrans);
            elSelAll.addClass('deSel');
        }
    }
    
};