$(document).ready(function () {
    scope = angular.element($("#BaseController")).scope();
    FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({
        title: true,
        tab: true,
        tabType: 'client'
    });
    $('#articleAreas').selectpicker();
    $('#articleCategories').selectpicker();
    var option = {
        pageType: 'cmsAddElement',
        contactId: contactId,
        currentClubId: clubId,
        localStorageName: type + '_' + clubId + '_' + contactId,
        tabheadingArray: tabheadingArray
    };
    Fgtabselectionprocess.initialize(option);
    FgDirtyFields.init('addArticleElement', {saveChangeSelector: "#save_changes, #save_bac", discardChangesCallback:FgCmsArticleElement.discardChangesCallback});
    FgCmsArticleElementLog.init();
    FgUtility.handleSelectPicker();        
 });

var FgCmsArticleElement = {
    renderContent: function () {
        $('#elementArticleWrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
    },
    renderLog: function () {
        $('#elementArticleWrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        FgCmsArticleElementLog.init();

    },
    saveElementCallback: function (d) {
        FgDirtyFields.init('addArticleElement', {saveChangeSelector: "#save_changes, #save_bac", discardChangesCallback:FgCmsArticleElement.discardChangesCallback});
    },
    discardChangesCallback :function(){
        $('.bootstrap-select').remove();
        $('#articleAreas').selectpicker();
        $('#articleCategories').selectpicker();
        FgUtility.handleSelectPicker();    
    }
};


var FgCmsArticleElementLog = {
    init: function () {
        FgCmsArticleElementLog.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    },
    dataTableOpt: function () {
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var i = 0;
        columnDefs.push({"name": "date", "type": "moment-" + currentDateFormat, "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['date'];
                row.displayData = '&nbsp;&nbsp;' + row['date'];
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "option", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">'+statusTranslations[row['status']]+'</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">'+statusTranslations[row['status']]+'</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">'+statusTranslations[row['status']]+'</span>' : '-'));
                row.sortData = row['type'];
                row.displayData = row['type'] + flag;

                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_before", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueBefore'];
                row.displayData = row['valueBefore'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_after", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueAfter'];
                row.displayData = row['valueAfter'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "edited_by", "targets": i++, data: function (row, type, val, meta) {
                
                var profileLink = profilePath.replace("dummy", row['activeContactId']);
                row.sortData = row['contact'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['contact'] + '</a></div>' : '<span class="fg-table-reply">' + row['contact'] + '</span>';
                return row;
                
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});

        datatableOptions = {
            columnDefFlag: true,
            ajaxPath: elementLogDetailsPath,
            ajaxparameterflag: true,
            ajaxparameters: {
                elementId: elementId
            },
            columnDefValues: columnDefs,
            serverSideprocess: false,
            displaylengthflag: false,
            initialSortingFlag: true,
            initialsortingColumn: '0',
            initialSortingorder: 'asc'
        };
    }
};

    $('body').off('click', '#save_changes');
    $('body').on('click', '#save_changes, #save_bac', function (e) {
        var currentSelectedButton = $(this).attr('id');
        var isAllArea;
        var isAllCat; 
        var articleAreas = $('[name=articleAreas]').val(); 
        var articleCategories = $('[name=articleCategories]').val();
        var isValid =  validateForm(articleAreas, articleCategories);
        if(isValid){
          isAllArea = (articleAreas == 'ALL_AREAS') ?  1 : '';
          isAllCat = (articleCategories == 'ALL_CATS') ? 1 : '';
          var saveType = (currentSelectedButton == 'save_changes') ? 'save' : 'saveBack';
          var data = {'areas': articleAreas, 'categories': articleCategories, 'boxId':boxId, 'elementId':elementId, 'sortOrder':sortOrder, 'pageId':pageId, 'isAllArea':isAllArea, 'isAllCat':isAllCat, 'saveType':saveType };
          FgDirtyFields.removeAllDirtyInstances();
          FgXmlHttp.post(saveArticleElement, data, false, FgCmsArticleElement.saveElementCallback); 
        }
        
    });
    
 function validateForm(articleAreas, articleCategories) {

    if (articleAreas == null) {
        $('form#addArticleElement select#articleAreas').parent().addClass('has-error');
        $('<span class="help-block fg-cms-article-areas-error-block fg-marg-top-5">required</span>').insertAfter($('form#addArticleElement select#articleAreas + .btn-group.bs-select'));
        $('.fg-cms-article-areas').addClass('has-error');
         return false;
    }

    if (articleCategories == null) {
        $('form#addArticleElement select#articleCategories').parent().addClass('has-error');
        $('<span class="help-block fg-marg-top-5 fg-cms-article-categories-error-block">required</span>').insertAfter($('form#addArticleElement select#articleCategories + .btn-group.bs-select'));
        $('.fg-cms-article-categories').addClass('has-error');
         return false;
    }
    return true;
}

   $('body').on('change','#articleCategories', function(){
          $('form#addArticleElement select#articleCategories').parent().removeClass('has-error');
          $('.fg-cms-article-categories').removeClass('has-error'); 
          $('.fg-cms-article-categories-error-block').hide();
   });
   
   $('body').on('change','#articleAreas', function(){
          $('form#addArticleElement select#articleAreas').parent().removeClass('has-error');
          $('.fg-cms-article-areas').removeClass('has-error'); 
          $('.fg-cms-article-areas-error-block').hide();
   });