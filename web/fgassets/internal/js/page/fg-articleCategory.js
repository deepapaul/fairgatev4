
/*function to initialize on page load */
$(document).ready(function () {
    initPageFunctions();
    $(".fg-action-menu-wrapper").FgPageTitlebar({
        title: true,
        row2: true,
        languageSwitch: true
    });
});
$('form').on('click', '#addcategory', function () {
    var rand = $.now();
    var attributes = {title: '', articleCount: 0, isActive: 1, titleLang: '', sortOrder: ''};
    var jsonData = {catId: rand, clubLanguages: clubLanguages, isNew: true, catType: 'article', attributes: attributes};
    renderNewRow('template-articlecategory-add', 'article_category_settings', jsonData);
});
$('body').on('click', '.fg-article-count', function () {
    var url = articleListPath;
    var dataId = $(this).attr('data-catid');
    sessionStorage.setItem('activeArticleMenu-' + clubId + '-' + contactId, 'li_CAT');
    sessionStorage.setItem('activeSubMenuVar-' + clubId + '-' + contactId, 'li_CAT_' + dataId);
    sessionStorage.setItem( 'ARTICLE_INTERNAL_FILTER_editorial'+clubId+'-'+contactId,'{"filter":{"CATEGORIES":["'+dataId+'"]}}' );
    window.location = url;
});
/* save function */
function saveChanges() {
    var objectGraph = {};
    //parse the all form field value as json array and assign that value to the array
    objectGraph = FgInternalParseFormField.fieldParse();
    var catArr = JSON.stringify(objectGraph);
    FgXmlHttp.post(saveArticleCategoryPath, {'catArr': catArr}, false, callback);
}
function callback() {
    initPageFunctions();
    FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
}
/* function to remove newly added row on clicking delete button */
$('form').on('click', 'div.addednew input[data-inactiveblock=changecolor]', function () {
    FgDirtyFields.removeFields('#' + $(this).attr('data-parentid'));
    $('#' + $(this).attr('data-parentid')).remove();
    FgInternal.resetSortOrder($('#article_category_settings'));
});

/* error handler function */
function errorHandler() {
    $('.btlang').removeClass('active');
    $("#"+defaultLang).addClass('active');
    FgUtility.showTranslation(defaultLang);
    FgGlobalSettings.handleLangSwitch();
}
function renderNewRow(templateScriptId, parentDivId, jsonData) {

    var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
    $('#' + parentDivId).append(htmlFinal);
    if (jsonData.isNew) {
        FgDirtyFields.updateFormState();
    }
    $('#' + parentDivId).find('.addednew').slideDown('250', 'easeInQuart');
    FgInternalDragAndDrop.sortWithOrderUpdation('#article_category_settings', false);
    FgInternal.resetSortOrder($('#article_category_settings'));
    FgUtility.showTranslation(selectedLang);

}
function initPageFunctions() {
    createHtml(result_data);
    FgGlobalSettings.handleLangSwitch();
    FgInternalDragAndDrop.sortWithOrderUpdation('#article_category_settings', false);
    // For resetting the sorting changes done in the page on 'discard_changes'
    var initialOrderArray = FgUtility.getOrderOfChildElements('#article_category_settings');
    var resetSections = {
        '0': {
            'parentElement': '#article_category_settings',
            'initialOrder': initialOrderArray,
            'addClass': true,
            'className': 'blkareadiv'
        }
    };
    FgInternalDragAndDrop.resetChanges(resetSections);
    FormValidation.init('editArticleCategory', 'saveChanges', 'errorHandler');
    FgInputTextValidation.init();
    initDirtyField();
    FgLanguageSwitch.checkMissingTranslation(defaultLang);
}
function createHtml(result_data) {
    $('#article_category_settings').html('');
    var catCount = Object.keys(result_data).length;
    if (catCount > 0) {
        var resultData = FgInternal.groupByMulti(result_data, ['sortOrder']);
        _.each(resultData, function (cat_data, catSortOrder) {
            _.each(cat_data, function (catData, catVal) {
                var jsonData = {catId: catData.id, clubLanguages: clubLanguages, isNew: false, catType: 'calendar', attributes: catData};
                renderNewRow('template-articlecategory-add', 'article_category_settings', jsonData);
            });
        });
    }
}
function initDirtyField() {
    FgDirtyFields.init('editArticleCategory', {
        discardChangesCallback: function () {
            initDirtyField();
           // To init sorting after discard
            FgInternalDragAndDrop.sortWithOrderUpdation('#article_category_settings', false);
        }
    });
}
