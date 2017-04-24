FgCountUpdate = {
   /*update function used to update the count of listing page after any action
     action    : Action can be add, remove or move
     module    : Module to which update is happening. It can be an update from contact to document
     type      : Type of the module (Contact: Active, Archive, Document : Club, team, Workgroup, Contact etc)      
     updateArr : array contains {'categoryId' : catId, 'subCatId' : subId, 'dataType' : dataType, 'count' : responce.totalCount}        
     */
    update: function(action, module, type, updateArr, count) { //operation, modulename, type, countArray, updateArr, count         
        if (type !== '') {
            if (module === 'document' && count !== 0) {
                FgCountUpdate.updateTopNav(action, module, type, count);
            }
            if ($('#sidemenu_bar').length !== 0) {
                FgCountUpdate.updateSidebar(updateArr);
                //update the filter total count at count display area   
                if (module == 'contact') {
                    FgCountUpdate.updateFilterTotalCount();
                }
                if (module === 'document' && count !== 0) {
                    FgCountUpdate.updateSidebarAllactive(action, count);
                }
            }
        }
    },
    
    //Update TOP nav count on any action
    updateTopNav: function(action, module, type, count) {
        var newTopNavCount = 0;
        //GET TOPNAV COUNT ON ANY ADD/EDIT/DELETE ACTION         
        var topNavActiveCount = parseInt($('.navbar-nav #fg-dev-topnav-' + module + '-' + type + ' span.badge-round').text());

        //UPDATE COUNT ON ANY ADD/EDIT/DELETE ACTION                                    
        if (action === 'add') {
            newTopNavCount = topNavActiveCount + count;
        } else if (action === 'remove') {
            newTopNavCount = topNavActiveCount - count;
        }
        $('.navbar-nav #fg-dev-topnav-' + module + '-' + type + ' span.badge-round').text(newTopNavCount);
        //UPDATE TOPNAV COUNT ON ANY ADD/EDIT/DELETE ACTION   
    },
    
    //updateSidebar on any action
    updateSidebar: function(updateArr) {        
        $.each(updateArr, function(index, valueArr) {
            var categoryId = valueArr['categoryId'];
            var newCount = parseInt(valueArr['sidebarCount']);
            var subCatId = valueArr['subCatId'];
            var dataType = valueArr['dataType'] ? valueArr['dataType'] : '';
            if (dataType === '') {
                var dataType = valueArr['catClubId'] ? 'DOCS-' + valueArr['catClubId'] : '';
            }
            var action = valueArr['action'] ? valueArr['action'] : 'remove';
            var sidebarItems = '';
            
            if (dataType === 'TEAM' || categoryId === '') {
                sidebarItems = $('#sidemenu_bar').find("[data-id='" + subCatId + "'][data-type='" + dataType + "']");
            } else {
                sidebarItems = $('#sidemenu_bar').find("[data-id='" + subCatId + "'][data-categoryid='" + categoryId + "'][data-type='" + dataType + "']");
            }
            $.each(sidebarItems, function(key, valueData) {
                var sidebarNewCount = 0;
                var sidebarOldCount = 0;
                if ($(valueData).find('.fg-sidebar-loading')) {
                    $(valueData).find('.fg-sidebar-loading').addClass('badge badge-round badge-important').removeClass('fg-sidebar-loading fa-spin');
                }
                var sidebarOldCount = parseInt($(valueData).find('.badge-round').text());
                if (action === 'add') {
                    sidebarNewCount = sidebarOldCount + newCount;
                } else if (action === 'remove') {
                    sidebarNewCount = sidebarOldCount - newCount;
                } else if (action === 'show') {
                    sidebarNewCount = newCount;
                }
                $(valueData).find('.badge-round').text(sidebarNewCount);
            });
        });

    },
    
    //update all active count after action
    updateSidebarAllactive: function(action, count) {
        var allActiveCnt = 0;
        var newAllActiveCnt = 0;
        allActiveCnt = parseInt($('#allActive .sidebabar-link .badge-round').text());
        if (action === 'add') {
            newAllActiveCnt = allActiveCnt + count;
        } else if (action === 'remove') {
            newAllActiveCnt = allActiveCnt - count;
        }
        $('#allActive .sidebabar-link .badge-round').text(newAllActiveCnt);
    },
    
    //display the missing assignment block in sidebar
    updateMissingAssignments: function(missingAssignData) {
        $.each(missingAssignData, function(key, valueData) {
            var categoryId = valueData['roleCatId'];
            var missingCount = parseInt(valueData['missingCount']);
            var subCatId = valueData['roleId'];
            var dataType = valueData['clubId'] ? 'FROLES-' + valueData['clubId'] : '';
            var sidebarItems = $('#sidemenu_bar').find("[data-id='" + subCatId + "'][data-categoryid='" + categoryId + "'][data-type='" + dataType + "']");
            $.each(sidebarItems, function(index, dataArr) {
                if ($(this).parents('#bookmark_li').length == 0) {
                    $(this).parents().eq(2).children('a').find('.title').append('<i class="fa fa-warning fg-warning missingWarning"></i>');
                    if ($(this).parents().eq(4).children('a').find('i').length == 0) {
                        $(this).parents().eq(4).children('a').find('.title').append('<i class="fa fa-warning fg-warning missingWarning"></i>');
                    }
                    var html = '<li id="missing_req_assgmt_' + categoryId + '" class="subclass ">';
                    html = html + '<a class="sidebabar-link" href="javascript:void(0)" data-type="MISSING_MEMBERSHIP" data-club="' + valueData['clubId'] + '" data-id="' + categoryId + '" data-categoryId="' + categoryId + '">';
                    html = html + '<i class="fa fa-warning fg-warning"></i>';
                    html = html + '<span class="title">' + datatabletranslations.missingFedSubFedRoleAssignments + '</span>';
                    html = html + '<span class="badge badge-round badge-important">' + missingCount + '</span></a></li>';
                    $(this).parents().eq(1).prepend(html);
                }
            });
        });
        $('#MissingReqAssgmtError').removeClass('display-hide');
    },
    
    //update the total count at filter count display area
    updateFilterTotalCount: function() {
        activeMenu = '#' + localStorage.getItem(FgSidebar.activeSubMenuVar);
        var activeMenuCnt = ($(activeMenu).find(".badge").length > 0) ? $(activeMenu).find(".badge").text() : $("#fcount").text();
        $("#tcount").html(activeMenuCnt)
    },
    
    /**
     * Method to update datatable rows count
     * @param {string}  action remove/add
     * @param {integer} count  count to update
     */
    updateDatatableCount: function(action, count) {
        var currentCount = parseInt($("#fcount").html());
        if(action == "add") {
            updatedCount = (currentCount + count*1);
        } else if(action == "remove") {
            updatedCount = (currentCount - count*1);
        }
        $("#fcount").html(updatedCount);
    }
};