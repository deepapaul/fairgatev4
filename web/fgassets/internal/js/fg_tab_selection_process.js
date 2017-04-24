var isFirstLoad = true;
var Fgtabselectionprocess = function () {
    var settings;
    var defaultSettings = {
        groupId: '', // id of selected team/workgroup
        grouptype: 'team', // type of selected item( team/workgroup )
        tabheadingArray: '', // array contains all the tab heading details
        tabuserrightsArray: '', // array contains the userrights of each tab item
        contactId: '', //contact id
        localStorageName: '', //localstorage name of the items according to the type
        moreTabId: '#paneltab',
        pageType: 'memberlist',
        teamoverviewOptions: '',
        defaultGroupId     :'',
    };
    var gettabId = function (tabId) {
        var tabDetails = $.parseJSON(localStorage.getItem(settings.localStorageName));
        if (typeof tabId !== 'undefined' && typeof tabId !== false && tabId !== null) {
            if (_.size(_.find(settings.tabheadingArray, function (value, key) {
                return key == tabId
            })) <= 0) {
                //find the first  
                getFirstTabDetails()
            } else {
                settings.groupId = tabId;
                localStorage.setItem(settings.localStorageName, JSON.stringify({'id': settings.groupId, 'type': settings.grouptype}));
            }

        } else if (typeof tabDetails !== 'undefined' && typeof tabDetails !== false && tabDetails != null) {
            settings.groupId = tabDetails['id'];
            if (_.size(_.find(settings.tabheadingArray, function (value, key) {
                return key == settings.groupId
            })) <= 0) {
                //find the first  
                getFirstTabDetails()
            }
            localStorage.setItem(settings.localStorageName, JSON.stringify({'id': settings.groupId, 'type': settings.grouptype}));

        } else {
            getFirstTabDetails()
        }

    };

    var getFirstTabDetails = function () {
        indexArray = Object.keys(settings.tabheadingArray);
        settings.groupId = indexArray[0];
        localStorage.setItem(settings.localStorageName, JSON.stringify({'id': settings.groupId, 'type': settings.grouptype}));

    };
    
    var getforumListdata = function () {
         ajaxForumUrl = $('#fg_tab_' + settings.groupId).find('a').attr('data_url');

        if (!$.isEmptyObject(listTable)) {
            listTable.ajax.url(ajaxForumUrl).load(function () {
                listTable.columns.adjust().fixedColumns().relayout();
               setTimeout(function(){$('.sorting_asc').removeClass('sorting_asc').addClass('sorting_disabled');},3000)
            });
        } else {
            var datatableOptions = {
                fixedcolumn: false,
                columnDefFlag: true,
                columnDefValues: columnDefs,
                ajaxPath: ajaxForumUrl,
                ajaxparameterflag: true,
                rowlengthshow: false,
                tableId: "forumlisttable",
                manipulationFlag: true,
                manipulationFunction: 'manipulateforumdata',
                popupFlag: false,
                widthResize: false,
                ajaxHeader: false,
                displaylength: 20,
                serverSideprocess:true,               
                initialsortingColumn: localStorage.getItem(sortingValuestrorageName),
                initialSortingorder: 'asc',
                initialSortingFlag: true,
                fixedColumns: {
                    leftColumns: 1,
                },
                ajaxparameters: {
                    sortName: localStorage.getItem(sortingValuestrorageName)
                }
            };

            FgDatatable.listdataTableInit('forumlisttable', datatableOptions);
            
            //show the last active tab 
            handleMoreTabactiveMenu();
            
        }


    };
    var getmemberListdata = function () {
        var ajaxUrl = $('#fg_tab_' + settings.groupId).find('a').attr('data_url');

        if (!$.isEmptyObject(listTable)) {
            listTable.ajax.url(ajaxUrl).load(function () {
                listTable.columns.adjust().fixedColumns().relayout();
            });
        } else {
            var datatableOptions = {
                ajaxHeader: true,
                tableColumnTitleStorageName: tableColumnTitleStorage,
                tableSettingValueStorageName: tableSettingValueStorage,
                ajaxparameterflag: true,
                ajaxPath: ajaxUrl,
                rowlengthWrapperdivid: 'fg_dev_memberlist_row_length',
                tableId: "memberlisttable",
                manipulationFlag: true,
                manipulationFunction: 'manipulatememberColumnFields',
                popupFlag: true,
                widthResize: true,
                initialSortingFlag: true,
                initialsortingColumn: 1,
                initialSortingorder: 'asc',
                fixedColumns: {
                    leftColumns: 2,
                },
                ajaxparameters: {
                    tableField: localStorage.getItem(tableSettingValueStorage)
                }
            };
            
            FgDatatable.listdataTableInit('memberlisttable', datatableOptions);
            //show the last active tab 
            handleMoreTabactiveMenu();

        }


    };


    //check whwether the contact has access to this role
    var checkAccessToRole = function () {
        var hasAccess = 1;
        if (!(settings.groupId in settings.tabheadingArray)) {
            hasAccess = 0;
            for (firstKey in settings.tabheadingArray)
                break;
            settings.groupId = firstKey;
            hasAccess = checkAccessToRole();

//            $('.page-content').html('This user does not have access to this section.');
        }

        return hasAccess;
    };
//redirect to corresponding user rights page
    var getuserrightsData = function () {

    };
//get log data
    var getLogdata = function () {
        
    };
    //handle the active menu tab while refreshing a page
    var handleMoreTabactiveMenu = function () {
        $("#paneltab li").removeClass('active');
        $("#paneltab li").removeClass('show');
        $("#paneltab li").removeClass('hidden');
        $("#paneltab #fg_tab_" + settings.groupId).addClass('active');
        FgMoreMenu.initClientSide('paneltab');
    };

    var renderPage = function () {
        switch (settings.pageType) {
            case "memberlist" :
                getmemberListdata();
                break;
            case 'roleoverview' :
                renderRoleOverview();
                break;
            case 'documentlist' :
                FgTeamDocuments.renderSidebar(isFirstLoad);
                break;
            case 'forumlist' :
                getforumListdata();
                break;
            case 'articleDetails' :
                renderArticleDetails();
                break; 
            case 'cmsAddElement' :
                renderCmsAddElement();
                break; 
        }

    };
    // To get document list data.
    var getdocumentListdata = function () {
        var ajaxUrl = $('#fg_tab_' + settings.groupId).find('a').attr('data_url');
        if (typeof fgDocumentUploader !== 'undefined') {
            fgDocumentUploader.removeFileContents();
        }

        if (!$.isEmptyObject(listTable)) {
            listTable.destroy();
        }

        // Set datatable options       
        datatableOptions = {
            ajaxHeader: true,
            fixedcolumn: true,
            tableColumnTitleStorageName: tableColumnTitleStorage,
            tableSettingValueStorageName: tableSettingValueStorage,
            ajaxPath: ajaxUrl,
            ajaxparameterflag: true,
            isCheckbox: false,
            ajaxparameters: {
                columns: tableSettingValue,
                menuType: FgSidebar.activeMenuData.menuType, //'new' or 'all' or 'subcategory'
                categoryId: FgSidebar.activeMenuData.categoryId,
                subCategoryId: FgSidebar.activeMenuData.id
            },
            popupFlag: true,
            displaylength: 10,
            serverSideprocess: false,
            manipulationFlag: true,
            manipulationFunction: 'manipulateDocumentColumnFields',
            initialSortingFlag: true,
            initialsortingColumn: 1,
            initialSortingorder: 'asc',
            draggableFlag: true,
            rowlengthWrapperdivid: 'fg_dev_memberlist_row_length',
            widthResize: true
        };
        FgDatatable.listdataTableInit('datatable-club-document', datatableOptions);

        FgDatatable.datatableSearch();
        isFirstLoad = false;
        //show the last active tab 
        setTimeout(function () {
            handleMoreTabactiveMenu();
        }, 200);
        setTimeout(function () {
            $("div.DTFC_LeftBodyLiner").scrollLeft(300);
        }, 1000);


    };

    var renderRoleOverview = function () {
        settings.teamoverviewOptions.nextbirthdays.params = {'roleType': settings.grouptype, 'roleId': settings.groupId};
        settings.teamoverviewOptions.members.params = {'roleType': settings.grouptype, 'roleId': settings.groupId};
        settings.teamoverviewOptions.documents.params = {'roleType': settings.grouptype, 'roleId': settings.groupId};
        settings.teamoverviewOptions.forums.params = {'roleType': settings.grouptype, 'roleId': settings.groupId};
        settings.teamoverviewOptions.calendar.params = {'roleType': settings.grouptype, 'roleId': settings.groupId,'currentClubId':settings.currentClubId};
        settings.teamoverviewOptions.articles.params = {'roleType': settings.grouptype, 'roleId': settings.groupId,'currentClubId':settings.currentClubId};
        FgOverview.initRoleOverview(settings.teamoverviewOptions);
        handleMoreTabactiveMenu();
    };
    
    var renderArticleDetails = function(){
        var opt = { articleDataUrl : settings.articleDataUrl,
                    articleId : settings.articleId,
                    articleCommentsUrl: settings.articleCommentsUrl,
                    articleTextUrl: settings.articleTextUrl,
                    articleMediaUrl: settings.articleMediaUrl,
                    articleDetailTextUrl: settings.articleDetailTextUrl,
                    articleDetailMediaUrl: settings.articleDetailMediaUrl,
                    articleAttachmentsUrl: settings.articleAttachmentsUrl,
                    articleSettingsUrl: settings.articleSettingsUrl,
                };
        FgEditorialDetails.init(opt);
        var selectedTab = $('#paneltab li.active.show a').attr('data_id');
        renderTabDetails(selectedTab);   
    };
    
    var renderCmsAddElement = function(){
        var selectedTab = $('#paneltab li.active.show a').attr('data_id');
        renderTabDetails(selectedTab);
    };
    
     var renderTabDetails = function (selectedTab) {
        switch (selectedTab) {
            case "preview" :
                FgEditorialDetails.renderArticle();
                break;
            case 'comments' :
                FgEditorialDetails.renderArticleComments();
                break;
            case 'documentlist' :
                FgTeamDocuments.renderSidebar(isFirstLoad);
                break;
            case 'log' :
                FgEditorialDetails.renderArticleLog();
                break;
            case 'articleDetails' :
                renderArticleDetails();
                break; 
            case 'articleText' :
                FgEditorialDetails.renderArticleTexts();
                break;
            case 'articleMedia' :
                FgEditorialDetails.renderArticleMedia();
                break;
            case 'articleAttachments' :
                FgEditorialDetails.renderArticleAttachments();
                break;
            case 'articleSettings' :
                FgEditorialDetails.renderArticleSettings();
                break;
            case 'elementContent' :
                var CmsElement = new FgCmsElement();
                CmsElement.renderContent();
                break;
            case 'elementLog' :
                var CmsElement = new FgCmsElement();
                CmsElement.renderLog();
                break;
            case 'cmsArticleElementContent' :
                var CmsArticleElement = new FgCmsArticleElement();
                CmsArticleElement.renderContent();
                break;
            case 'cmsArticleElementLog' :
                var CmsArticleElement = new FgCmsArticleElement();
                CmsArticleElement.renderLog();
                break;
            case 'cmsCalendarElementContent' :
                var CmsCalendarElement = new FgCmsCalendarElement();
                CmsCalendarElement.renderContent();
                break;
            case 'cmsCalendarElementLog' :
                
                var CmsCalendarElement = new FgCmsCalendarElement();
                CmsCalendarElement.renderLog();
                break;
            case 'cmsTwitterElementContent' :
                var CmsTwitterElement = new FgCmsTwitterElement();
                CmsTwitterElement.renderContent();
                break;
            case 'cmsTwitterElementLog' :
                var CmsTwitterElement = new FgCmsTwitterElement();
                CmsTwitterElement.renderLog();
                break;    
            case 'cmsSponsorElementContent' :
                var CmsSponsorElement = new FgCmsSponsorAdElement();
                CmsSponsorElement.renderContent();
                break;
            case 'cmsSponsorElementLog' :
                var CmsSponsorElement = new FgCmsSponsorAdElement();
                CmsSponsorElement.renderLog();
                break;
            case 'cmsMapElementContent' :
                var CmsMapElement = new FgCmsMapElement();
                CmsMapElement.renderContent();
                break;
            case 'cmsMapElementLog' :
                var CmsMapElement = new FgCmsMapElement();
                CmsMapElement.renderLog();
                break;
            case 'cmsTextElementContent':
                var fgTextElement = new FgTextElement();
                fgTextElement.renderContent();
                 break;
            case 'cmsTextElementLog':
                var fgTextElement = new FgTextElement();
                fgTextElement.renderLog();
                 break;
            case 'cmsImageVideoElementContent':
                ImageElement.renderContent();
                 break;
            case 'cmsImageVideoElementLog':
                ImageElement.renderLog();
                 break;     
            case 'cmsIframeElementContent':
                var CmsIframeElement = new FgCmsIframe();
                CmsIframeElement.renderContent();
                 break;
            case 'cmsIframeElementLog':
                var CmsIframeElement = new FgCmsIframe();
                CmsIframeElement.renderLog();
                 break;     
            case 'cmsTabContent':
                CmsSpecialPage.renderContent();
                 break;     
            case 'cmsTabPreview':
                CmsSpecialPage.renderPreview();
                 break;     
        }

    };
    

//function to merge the new option value with default option value
    var initSettings = function (options) {
        settings = $.extend(true, {}, defaultSettings, options);
    };
 

    return {
        listDocument: function () {
            getdocumentListdata();
        },
        listForum : function() {
          getforumListdata()  
        },
        renderArticle : function(){
            renderArticleDetails();
        },
        initialize: function (options) {
             initSettings(options);
            (settings.defaultGroupId !='') ? gettabId(settings.defaultGroupId) : gettabId();
            if (checkAccessToRole()) {
                renderPage();
                //activate the current selected tab
                $(settings.moreTabId + " > li").removeClass('active');
                $('#fg_tab_' + settings.groupId).addClass('active');
            }

            $(document).on('shown.bs.tab', settings.moreTabId + ' a[data-toggle="tab"]', function (e) {
                var clickedId = $(this).attr('data_id');
                gettabId(clickedId);
                if (checkAccessToRole()) {
                    //Clear sidebar active menu local storage on tab click
                    if (defaultSettings.grouptype == "team" || defaultSettings.grouptype == "workgroup") {
                        localStorage.removeItem(FgSidebar.activeMenuVar);
                        localStorage.removeItem(FgSidebar.activeSubMenuVar);
                    }
                    renderPage();
                }
            })
        }

    };
}();

							