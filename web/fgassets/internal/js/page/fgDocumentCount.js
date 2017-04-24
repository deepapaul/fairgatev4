var FgDocumentCount={ 
    settings : {
        dataUrl : '',
        myAdminTeams : {},
        myAdminWorkgroups : {},
        myMemberTeams : {},
        myMemberWorkgroups : {},
        myMemberFunctions : {},
        isClubAdmin : 0
    },
    documentData : {},
    initRoleDocumentsCount: function() {
        FgDocumentCount.getDocumentCount();
    }, 
    getDocumentCount : function() {
        $.ajax({type: "GET", url: FgDocumentCount.settings.dataUrl,    
            success: function(data){
                FgDocumentCount.documentData = _.groupBy(data.data, 'roleId') ;
                FgDocumentCount.settings.isClubAdmin = data.isClubAdmin;
                FgDocumentCount.settings.myAdminTeams = data.myAdminTeams;
                FgDocumentCount.settings.myAdminWorkgroups = data.myAdminWorkgroups;
                FgDocumentCount.settings.myMemberTeams = data.myMemberTeams;
                FgDocumentCount.settings.myMemberWorkgroups = data.myMemberWorkgroups;
                FgDocumentCount.settings.myMemberFunctions = data.myMemberFunctions;
                FgDocumentCount.showRoleDocumentTabCount();
            }
        });
    },
    updatePersonalDocumentsCount : function(action) {
        switch(action) {
            case 'download':
                FgDocumentCount.updatePersonalTopNavCount();
                FgDocumentCount.updateSidebarNewDocumentsCount();
                break;
            default:
                break;
        }
    },
    updateRoleDocumentsCount : function(action, docId) {
        var documentData = _.findWhere(jsonData['aaData'], {'documentId' : docId});
        var roleType = (documentData.documentType).toLowerCase();
        switch(action) {
            case 'download':
                FgDocumentCount.updateRoleTopNavCount(roleType);
                FgDocumentCount.updatePersonalTopNavCount();
                FgDocumentCount.updateSidebarNewDocumentsCount();
                FgDocumentCount.updateRoleTabCount(action, documentData);
                break;
            default:
                break;
        }
    },
    updatePersonalTopNavCount : function() {
        var unreadCount = $('#fg-dev-unread-personal-documents').attr('data-count');
        unreadCount = unreadCount - 1;
        $('.fg-right-area-block #fg-dev-unread-personal-documents').attr('data-count', unreadCount);
        $('.fg-mega-series #fg-dev-unread-personal-documents').attr('data-count', unreadCount);
        if (unreadCount == 0) {
            $('.fg-right-area-block #fg-dev-unread-personal-documents').addClass('hide');
            $('.fg-mega-series #fg-dev-unread-personal-documents').addClass('hide');
        }
    },
    updateRoleTopNavCount : function(roleType) {
        var unreadCount = $('#fg-dev-unread-' + roleType + '-documents').attr('data-count');
        unreadCount = unreadCount - 1;
        $('.fg-right-area-block #fg-dev-unread-' + roleType + '-documents').attr('data-count', unreadCount);
        $('.fg-mega-series #fg-dev-unread-' + roleType + '-documents').attr('data-count', unreadCount);
        if (unreadCount == 0) {
            $('.fg-right-area-block #fg-dev-unread-' + roleType + '-documents').addClass('hide');
            $('.fg-mega-series #fg-dev-unread-' + roleType + '-documents').addClass('hide');
        }
    },
    updateSidebarNewDocumentsCount : function() {
        var unreadCount = parseInt($('.page-sidebar-menu a[data-id="NEW"] span.badge-round').text());
        unreadCount = unreadCount - 1;
        unreadCount = (unreadCount <= 0) ? 0 : unreadCount;
        var activeSubMenu = localStorage.getItem(FgSidebar.activeSubMenuVar);
        if (activeSubMenu == 'MYDOCS_li_NEW') {
            $('#tcount').html(unreadCount);   
        }
        if (unreadCount == 0) {
            $('.fg-page-title-block-2').addClass('hide');
        }

        $('.page-sidebar-menu a[data-id="NEW"] span.badge-round').text(unreadCount);
    },
    showRoleDocumentTabCount : function() {
//        var tabCountId = 'fg-role-document-count-';
        _.each(FgDocumentCount.documentData, function(document, roleId) {
//            var docCount = 0;
            var newCount = 0;
            _.each(document, function(val, key) {
//                docCount += parseInt(val.docCount);
                newCount += parseInt(val.unreadCount);
            });
//            console.log('role : '+ roleId + ', docCount : '+ docCount + ', newCount : '+ newCount);
//            $('#' + tabCountId + roleId).html(docCount);
//            $('#' + tabCountId + roleId).removeClass('hide');
//            //handle more menu also
//            $('.fg-dropdown-more #' + tabCountId + roleId).html(docCount);
//            $('.fg-dropdown-more #' + tabCountId + roleId).removeClass('hide');
            
            FgDocumentCount.updateNewBadge(roleId, newCount);
        });
    },
    updateRoleTabCount : function(action, document) {
        var depositedIds = document.depositedRoleIds;
        var visibleFor = document.visibleForRights;
        var visibleFunctionIds = document.visibleFunctionIds;
        var functionIds = (visibleFunctionIds !== null) ? visibleFunctionIds.split(',') : [];
        var subcategoryId = document.subCategoryId;
        var reloadCount = false;
        if (depositedIds !== 'ALL') {
            var roleIds = depositedIds.split('*##*');
            if (roleIds.length > 0) {
                _.each(roleIds, function(roleId, index) {
                    if (
                            ((visibleFor == 'team' || visibleFor == 'team_admin' || visibleFor == 'team_functions') && _.contains(FgDocumentCount.settings.myAdminTeams, parseInt(roleId))) ||
                            ((visibleFor == 'team') && _.contains(FgDocumentCount.settings.myMemberTeams, parseInt(roleId))) ||
                            ((visibleFor == 'team_functions') && _.find(FgDocumentCount.settings.myMemberFunctions[roleId], function(funId){ return _.contains(functionIds, funId);})) ||
                            ((visibleFor == 'workgroup' || visibleFor == 'workgroup_admin') && _.contains(FgDocumentCount.settings.myAdminWorkgroups, parseInt(roleId))) ||
                            ((visibleFor == 'workgroup') && _.contains(FgDocumentCount.settings.myMemberWorkgroups, parseInt(roleId)))
                        ) {
                        _.each(FgDocumentCount.documentData[roleId], function(document, key) {
                            if (FgDocumentCount.documentData[roleId][key]['subCategoryId'] == subcategoryId) {
                                FgDocumentCount.documentData[roleId][key]['unreadCount'] -= 1;
//                                console.log('role : ' + roleId + ', subcategoryid : ' + subcategoryId);
                                reloadCount = true;
                            }
                        });
                    }
                });
            }
        } else if (depositedIds == 'ALL') {
            _.each(FgDocumentCount.documentData, function(document, roleId) {
                if (
                        ((visibleFor == 'team' || visibleFor == 'team_admin' || visibleFor == 'team_functions') && _.contains(FgDocumentCount.settings.myAdminTeams, parseInt(roleId))) ||
                        ((visibleFor == 'team') && _.contains(FgDocumentCount.settings.myMemberTeams, parseInt(roleId))) ||
                        ((visibleFor == 'team_functions') && _.find(FgDocumentCount.settings.myMemberFunctions[roleId], function(funId){ return _.contains(functionIds, funId);})) ||
                        ((visibleFor == 'workgroup' || visibleFor == 'workgroup_admin') && _.contains(FgDocumentCount.settings.myAdminWorkgroups, parseInt(roleId))) ||
                        ((visibleFor == 'workgroup') && _.contains(FgDocumentCount.settings.myMemberWorkgroups, parseInt(roleId)))
                    ) {
                    _.each(document, function(val, key) {
                        if (FgDocumentCount.documentData[roleId][key]['subCategoryId'] == subcategoryId) {
                            FgDocumentCount.documentData[roleId][key]['unreadCount'] -= 1;
//                            console.log('role : ' + roleId + ', subcategoryid : ' + subcategoryId);
                            reloadCount = true;
                        }
                    });
                }
            });
        }  
        if (reloadCount) {
            FgDocumentCount.showRoleDocumentTabCount();
        }
    },
    updateNewBadge : function(roleId, unreadCount) {
        var tabNewId = 'fg-role-document-new-';
        if (unreadCount > 0) {
            $('#' + tabNewId + roleId).removeClass('hide');           
            //handle more menu also
            $('.fg-dropdown-more #' + tabNewId + roleId).removeClass('hide');
        } else {
            $('#' + tabNewId + roleId).addClass('hide');
            //handle more menu also
            $('.fg-dropdown-more #' + tabNewId + roleId).addClass('hide');
        }
    }
}