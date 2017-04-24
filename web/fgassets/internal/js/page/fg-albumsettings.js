

/*function to initialize on page load */
$(document).ready(function () {
    initPageFunctions();
    pageTitleBarInit();
    showAlbumData();
    handleGalleryImageClick();
    connectNestable();
    updateTempData();
    initDirtyField();
    FgUtility.showTranslation(selectedLang);
});


/*function to handle click of add new album */
$('form').on('click', '#add-album', function () {
    var rand = $.now();
    var attributes = {title: '', titleLang: '', sortOrder: '1', bookmarkId: '', imageCount: 0, subAlbumCount: 0};
    var jsonData = {albumId: rand, clubLanguages: clubLanguages, isNew: true, attributes: attributes};
    renderNewRow('template-albumsettings-add', 'gallery_album_settings', jsonData);
});


/* function to show data in different languages on switching language */
$(document).off('click', 'button[data-elem-function=switch_lang]');
$(document).on('click', 'button[data-elem-function=switch_lang]', function () {
    $('.btlang').removeClass('active');
    $(this).addClass('active');
    selectedLang = $(this).attr('data-selected-lang');
    FgUtility.showTranslation(selectedLang);
});


/* function to remove newly added row on clicking delete button */
$('form').on('click', 'div.addednew input[data-inactiveblock=changecolor]', function () {
    $(this).parents('li').remove();
    updateTempData();
});


/* function  called to render a new album template */
function renderNewRow(templateScriptId, parentDivId, jsonData)
{
    var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
    $('#' + parentDivId).append(htmlFinal);
    
    setTimeout(function () {
       $('#' + parentDivId).find('.addednew').slideDown(250, 'easeInQuart');
    }, 100); 
    
    connectNestable();
    FgUtility.showTranslation(selectedLang);
    updateTempData();
}

/* function  called to initialize nestable options */
function connectNestable()
{
    $('.fg-nestable').nestable({
        expandBtnHTML: '',
        collapseBtnHTML: '',
        maxDepth: 2,
        dragClass: 'dd-dragel fg-gallery-album-settings-drag',
    }).on('change', function () {
        updateTempData();
    });
}

/* function  called to initialize album settings page */
function initPageFunctions()
{
    FormValidation.init('album-settings', 'saveChanges', 'errorHandler');
    FgInputTextValidation.init();
}
/* save function */
function saveChanges()
{
    FgXmlHttp.post(saveAlbumPath, {'catArr': getCurrentFormData()}, false, callback);
}

/* function  to get currrent form data for saving*/
function getCurrentFormData()
{
    var objectGraph = {};
    //parse the all form field value as json arrays
    objectGraph = getAlbumSettingFormParse();

    var currentSettingsData = $('.fg-nestable').nestable('serialize');

    _.each(currentSettingsData, function (mainAlbum, index) {
        objectGraph[mainAlbum.id]['parentId'] = 0;
        objectGraph[mainAlbum.id]['sortOrder'] = index + 1;
        if (_.size(mainAlbum.children) > 0) {
            _.each(mainAlbum.children, function (subAlbum, index1) {
                objectGraph[subAlbum.id]['parentId'] = mainAlbum.id;
                objectGraph[subAlbum.id]['sortOrder'] = index1 + 1;

                if ($('#new_' + subAlbum.id).hasClass('addednew'))
                    objectGraph[subAlbum.id]['new'] = true;
            });
        }
        if ($('#new_' + mainAlbum.id).hasClass('addednew'))
            objectGraph[mainAlbum.id]['new'] = true;
    });

    return JSON.stringify(objectGraph);
}


/* function to handle image count click */
function handleGalleryImageClick()
{
    $(document).on('click', '.fg-dev-image-count-click', function (e) {
        e.preventDefault();
        var albumId = $(this).attr('data-album-id');
        var parentId  = $(this).attr('data-parent-id');
        if(parentId > 0){
           (galleryType== 'club') ? localStorage.setItem(FgLocalStorageNames.gallery.activeMenuVar, 'CG_li_'+parentId) :localStorage.setItem(FgLocalStorageNames.gallery.activeMenuVar, galleryType+'_li_'+parentId);
           (galleryType== 'club') ?  localStorage.setItem(FgLocalStorageNames.gallery.activeSubMenuVar,'CG_li_'+parentId+'_'+albumId) : localStorage.setItem(FgLocalStorageNames.gallery.activeSubMenuVar, galleryType+'_li_'+parentId+'_'+albumId);
        }else {
          (galleryType== 'club') ? localStorage.setItem(FgLocalStorageNames.gallery.activeMenuVar, 'CG_li_'+albumId) :localStorage.setItem(FgLocalStorageNames.gallery.activeMenuVar, galleryType+'_li_'+albumId);
          localStorage.setItem(FgLocalStorageNames.gallery.activeSubMenuVar,'');
        }
        localStorage.setItem(FgLocalStorageNames.gallery.selectedAlbum, albumId);
        window.location = sideBarPath;
    });
}

function getAlbumSettingFormParse() {
    var objectGraph = {};
    $("#album-settings :input[type='text'],[type='checkbox']").each(function () {
        if ($(this).attr('data-key') != undefined) {
            var inputType = $(this).attr('type');
            if (inputType == 'checkbox') {
                inputVal = $(this).attr('checked') ? 1 : 0;
            } else if (inputType == 'radio') {
                if ($(this).is(':checked')) {
                    inputVal = $(this).val();
                }
            } else {
                inputVal = $(this).val();
            }

            if (inputVal !== '' || $(this).is("textarea") || $(this).is("select")) {
                FgInternal.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
            } else if (inputType == 'hidden' || $(this).hasClass("hide")) {
                FgInternal.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
            } else if ((inputVal === '') && ($(this).attr('data-notrequired') == 'true')) {
                FgInternal.converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
            }
        }
    });
    return objectGraph;
}

/* function to show the saved album data */
function showAlbumData()
{
    $.each(resultData, function (i, data) {
        var jsonData = {clubLanguages: clubLanguages, isNew: false, albumId: data.albumId, attributes: data};
        var htmlFinal = FGTemplate.bind('template-albumsettings-add', jsonData);

        if (_.size(data.children) > 0) {
            var subLi = '<ol class="dd-list">';
            $.each(data.children, function (i, childData) {
                var jsonData = {clubLanguages: clubLanguages, isNew: false, albumId: childData.albumId, attributes: childData};
                subLi += FGTemplate.bind('template-albumsettings-add', jsonData);
            });
            subLi += '</ol>';

            var htmlFinal = $(htmlFinal).append(subLi);

        }
        $('#gallery_album_settings').append(htmlFinal);
    });
     FgLanguageSwitch.checkMissingTranslation(defaultLang);
}


function initDirtyField() {
    FgDirtyFields.init('album-settings', {
        discardChangesCallback: function () {
            handleGalleryImageClick();
            connectNestable();
            updateTempData();
            initDirtyField();
            FgUtility.showTranslation(selectedLang);
        }
    });
}


/* function to init the page title bar*/
function pageTitleBarInit()
{
    $(".fg-action-menu-wrapper").FgPageTitlebar({
        title: true,
        row2: true,
        languageSwitch: true
    });
}

/* function to trigger dirty field when nestable is changed*/
function updateTempData() {
    $('#albumSettingData').val(getCurrentFormData()).trigger('change');
    return;
}

/* error handler function */
function errorHandler() {
    FgUtility.showTranslation(defaultLang);
    makeactiveLang(defaultLang);
}

/* save callback function */
function callback()
{
    FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
}

/* function to make language tab as active */
function makeactiveLang(defaultLang) {
    
 var selectLang = $('.active').attr('id');
 if (selectLang != defaultLang) {
     $('.btlang').removeClass('active');
     $("#" + defaultLang).addClass('active');
 }
}