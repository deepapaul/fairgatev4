//Js file for handling workgroup and team documents
var jsonData = {};

var FgTeamDocuments = {
    sidebarMenuDatas: [],
    /* For rendering sidebar*/
    renderSidebar: function (isFirstTime) {
        roleIdObj = localStorage.getItem(tabStorageName);
        var roleIdJson = JSON.parse(roleIdObj);
        $.getJSON(documentCategoryPath, {"roleId": roleIdJson["id"]}, function (data) {
            FgTeamDocuments.sidebarMenuDatas = data;
            FgSidebar.jsonData = true;
            FgSidebar.ActiveMenuDetVar = (docType == "WORKGROUP") ? FgLocalStorageNames.workgroupDocuments.activeContactMenuDet : FgLocalStorageNames.teamDocuments.activeContactMenuDet;
            FgSidebar.activeMenuVar = (docType == "WORKGROUP") ? FgLocalStorageNames.workgroupDocuments.sidebarActiveMenu : FgLocalStorageNames.teamDocuments.sidebarActiveMenu;
            FgSidebar.activeSubMenuVar = (docType == "WORKGROUP") ? FgLocalStorageNames.workgroupDocuments.sidebarActiveSubMenu : FgLocalStorageNames.teamDocuments.sidebarActiveSubMenu;
            FgSidebar.activeOptionsVar = (docType == "WORKGROUP") ? FgLocalStorageNames.workgroupDocuments.sidebarActiveOptions : FgLocalStorageNames.teamDocuments.sidebarActiveOptions;
            FgSidebar.defaultMenu = 'MYDOCS_li';
            FgSidebar.defaultSubMenu = 'MYDOCS_li_' + data["MYDOCS"]['entry'][0]['id']; //If NEW exist, then 'NEW' else 'ALLDOCUMENTS'
            FgSidebar.options = [];
            FgSidebar.defaultTitle = data["MYDOCS"]['entry'][0]['title'];
            FgSidebar.showloading = true;
            FgSidebar.isAlwaysOpen = true;
            FgSidebar.settings = {};

            $.each(data, function (categoryName, categoryArray) {
                if (categoryName == "MYDOCS") {
                    /* My Documents */
                    var myDocMenu = {templateType: 'general', menuType: categoryArray['id'], 'parent': {id: categoryArray['id'] + '_li', class: 'tooltips bookmark-link', name: 'bookmark-link', 'data-placement': "right"}, title: categoryArray['title'], template: '#template_sidebar_menu', 'menu': {'items': categoryArray['entry']}};
                    FgSidebar.settings[categoryArray['id'] + '_li'] = myDocMenu;
                } else {
                    var contactMenu = {templateType: 'menu2level', menuType: categoryArray['id'], 'parent': {id: categoryArray['id'] + '_li', class: categoryArray['id']}, title: categoryArray['title'], template: '#template_sidebar_menu2level', 'logo': categoryArray['logo'], 'menu': {'items': categoryArray['entry']}};
                    FgSidebar.settings[categoryArray['id'] + '_li'] = contactMenu;
                }
            });

            if (isFirstTime) {
                FgSidebar.init();
            } else {
                FgSidebar.loadJsonSidebar();
            }
            FgTeamDocuments.showHideMarkAll();
            // data table for documents list
            Fgtabselectionprocess.listDocument();

            FgTeamDocuments.setSidebarCount(FgTeamDocuments.sidebarMenuDatas);
        });
    },
    //for updateing sidebar count
    setSidebarCount: function (params) {
        roleIdObj = localStorage.getItem(tabStorageName);
        var roleIdJson = JSON.parse(roleIdObj);
        $.post(documentSidebarCountPath, {"category": params, "type": docType, "currentRoleId": roleIdJson["id"]}, function (data) {
            FgCountUpdate.updateSidebar(data);
            FgSidebar.show();
        });
    },
    /*function to confirmation pop up on delete*/
    deleteDocumentConfirmationPopup: function (checkedIds, selected, type, roleId) {
        var titletext = $("#paneltab .active").find('span.fg-dev-tab-text').html();
        $.post(pathDeleteDocumentConfirmationPopup, {'docIds': checkedIds, 'selected': selected, 'titleText': titletext, 'type': type, 'roleId': roleId}, function (data) {
            FgModelbox.showPopup(data);
        });
    },
    /*function for delete documents*/
    deleteDocs: function (memberIds, type, roleId) {
        var params = {'docIds': memberIds, 'type': type, 'roleId': roleId};
        FgModelbox.hidePopup();
        FgXmlHttp.post(pathDeleteMember, params, false, FgTeamDocuments.redrawList, false);

    },
    /*Call back function*/
    redrawList: function (data) {
        Fgtabselectionprocess.listDocument();
        FgTeamDocuments.setSidebarCount(FgTeamDocuments.sidebarMenuDatas);
        FgDocumentCount.initRoleDocumentsCount();
    },
    /* Show mark all as seen link on new document selection only*/
    showHideMarkAll: function () {
        /* If there are no new documents mark all read link not needed */
        var activeSubMenu = localStorage.getItem(FgSidebar.activeSubMenuVar);
        /* show mark all as seen link only for new documents */
        if(( (activeSubMenu == null || activeSubMenu=='') && FgSidebar.defaultSubMenu == 'MYDOCS_li_NEW') || activeSubMenu == 'MYDOCS_li_NEW' ){
            $('.fg-page-title-block-2').removeClass('fg-dis-none').addClass('fg-active-IB');
        } else {
            $('.fg-page-title-block-2').removeClass('fg-active-IB').addClass('fg-dis-none');
        }
    }
}