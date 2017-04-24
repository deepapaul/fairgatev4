FgCountUpdate = {
   /*update function used to update the count of listing page after any action
     action    : Action can be add, show, remove or move
     isTopNav  : Is topnavigation update needed or not
     type      : Type of the module     
     updateArr : array contains {'categoryId' : catId, 'subCatId' : subId, 'dataType' : dataType, 'count' : responce.totalCount} 
	 count	   : count to be updated in sidebar
     */
    update: function(action, isTopNav, type, updateArr, count) { //operation, isTopNav, type, countArray, updateArr, count         
        if (type !== '') {
            if (isTopNav) {
                FgCountUpdate.updateTopNav(action, isTopNav, type, count);
            }
            if ($('#sidemenu_bar').length !== 0) {
                FgCountUpdate.updateSidebar(updateArr);
                
                if (isTopNav && count !== 0) {
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
    updateSidebar: function(updateArr) {   console.log(updateArr);     
        $.each(updateArr, function(index, valueArr) {
            var categoryId = valueArr['categoryId'];
            var newCount = parseInt(valueArr['sidebarCount']);
            var subCatId = valueArr['subCatId'];
            var dataType = valueArr['dataType'] ? valueArr['dataType'] : '';
            
            var action = valueArr['action'] ? valueArr['action'] : 'remove';
            var sidebarItems = '';
            
            if (categoryId === '') {
                sidebarItems = $('#sidemenu_bar').find("[data-id='" + subCatId + "'][data-type='" + dataType + "']");
            } else {
                sidebarItems = $('#sidemenu_bar').find("[data-id='" + subCatId + "'][data-categoryid='" + categoryId + "'][data-type='" + dataType + "']");
            }
            $.each(sidebarItems, function(key, valueData) {
                var sidebarNewCount = 0;
                var sidebarOldCount = 0;
                if ($(valueData).find('.fg-sidebar-loading')) {
                    $(valueData).find('.fg-sidebar-loading').addClass('badge badge-round badge-important fg-badge-blue').removeClass('fg-sidebar-loading fa-spin');
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
};