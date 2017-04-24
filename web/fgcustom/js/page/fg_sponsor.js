var FgSponsor = function () {
    
    return {
        moretabInit: function (menuId, containerId) {
           // FgMoreMenu.initClientSideWithNoError(menuId, containerId);
            $('#data-tabs > li').off('shown.bs.tab');
            $('#data-tabs > li').on('shown.bs.tab', function(){
                var tabType = $(this).attr('data_type');
                var searchValue = $("#searchbox").val();
                switch(tabType){
                    case 'activeservice':
                        activeserviceTable.search(searchValue).draw();
                        FgClearCheckAll.init();
                        FgSponsor.hideTablerowmenu();
                        $(".fg-filter-check-icon").hide();
                        $(".fg-contact-searchbox").removeClass("fg-has-filter");
                        setTimeout(function(){
                         activeserviceTable.columns.adjust();
                        },50)
                        
                        break;
                    case 'futureservice':
                        futureserviceTable.search(searchValue).draw();
                        FgClearCheckAll.init();
                        $(".fg-filter-check-icon").hide();
                        $(".fg-contact-searchbox").removeClass("fg-has-filter");
                        FgSponsor.hideTablerowmenu();
                        setTimeout(function(){
                         futureserviceTable.columns.adjust();   
                        },50)
                        
                        break;
                    case 'formerservice':
                        formerserviceTable.search(searchValue).draw();                       
                        FgClearCheckAll.init();
                        $(".fg-filter-check-icon").show();
                        $(".fg-contact-searchbox").removeClass("fg-has-filter");
                        FgSponsor.showTablerowmenu();                        
                        setTimeout(function(){
                         formerserviceTable.columns.adjust();
                        },50)
                        break;                        
                }
                 $(".dataClass").uniform();
                 $('.dataTable_checkall').uniform(); 
                localStorage.setItem(fgLocalStorageNames.sponsor.active.serviceTab, tabType);
            });
        },
        sponsorserviceDatatableInit: function (menuType, serviceId, serviceType) {
                $(".fg-contact-searchbox").removeClass("fg-has-filter");
                $('.filter-alert').hide();
                //menubar show/hide   
                 $('#servicemenuBar').show();
                 $('#fg_dev_assignmentTable').hide();
                 $('#normalmenuBar').hide();
                 $('#searchbox').show();
                 $('#assignmentsearchbox').hide();
                 $("#fgfuturerowchange").hide();
                $(".fg-filter-check-icon").hide();
                //datatable show/hide
                $('#serviceTable').show();
                $('#sponsorTable').hide();
                $('#sponsor-column-settings').hide();
                $(".fg-filter-check-icon").hide();
                $('#sponsor-column-settings').parent().addClass('fg-sponsor-searchblock');
                $('.fg-search-div').addClass('fg-search-right');
                $(".dataClass").uniform();
               localStorage.setItem("clickedmenu_sponsor_" + "_" + clubId + "_" + contactId, menuType);
            FgServiceList.init(serviceId,serviceType);
            FgSponsor.dynamicMenuhandling(menuType);
            /*
             *  page titlebar re initilise
             */ 
            FgPageTitlebar.init({
                    actionMenu: true,
                    title: true,
                    search: true,
                    tab :true,
                    tabMenuId: 'data-tabs',
                    tabType: 'client',
                    colSetting: false,
                    moreCompleteCallback: function ($object) {
//                        if($('#data-tabs > li#activeservice-tab-li').hasClass('show')){
//                            $('.datahideshow ul #activeservice-tab-li').removeClass('show').addClass('hidden');
//                        }else{
//                            $('.datahideshow ul #activeservice-tab-li').removeClass('hidden').addClass('show');
//                        }                        
                       ($('#data-tabs > li#activeservice-tab-li').hasClass('show')) ?  $('.datahideshow ul #activeservice-tab-li').removeClass('show').addClass('hidden'):  $('.datahideshow ul #activeservice-tab-li').removeClass('hidden').addClass('show'); 
                       ($('#data-tabs > li#futureservice-tab-li').hasClass('show')) ?  $('.datahideshow ul #futureservice-tab-li').removeClass('show').addClass('hidden'):  $('.datahideshow ul #futureservice-tab-li').removeClass('hidden').addClass('show');
                       ($('#data-tabs > li#formerservice-tab-li').hasClass('show')) ?  $('.datahideshow ul #formerservice-tab-li').removeClass('show').addClass('hidden'):  $('.datahideshow ul #formerservice-tab-li').removeClass('hidden').addClass('show');
                    }
            });
            FgPageTitlebar.setMoreTab();
            
        },
        sponsorDatatableInit: function (menuType) {
            //if (menuType ==='sponsor') {
                //Hide the content div of sponsorservice table
                $(".fg-contact-searchbox").addClass("fg-has-filter");
                $('#servicemenuBar').hide();
                $('#fg_dev_assignmentTable').hide();
                $('#normalmenuBar').show();
                $('.searchbox').show();
                $(".fg-filter-check-icon").show();
                $(".fg-filter-check-icon").show();
                $('.assignmentsearchbox').hide();
                $('#fgoverviewrowchange').hide();
                $('#fgrowchange').show();
                $("#fgfuturerowchange").hide();
                
                //datatable show/hide
                $('#serviceTable').hide();
                $('#sponsorTable').show();
                $('#sponsor-column-settings').show();
                $('#tableColumns').show();
                $('#fg-dev-filter-check').show();
                $('#fg-dev-coumnsetting-title').show();
                $('#sponsor-column-settings').parent().removeClass('fg-sponsor-searchblock');
                $('.fg-search-div').removeClass('fg-search-right');              
                
                FgSponsorTable.initid('sponsordataTable');
                FgSponsor.dynamicMenuhandling(menuType);
                /*
                 *  page titlebar re initilise
                 */                
                FgPageTitlebar.init({
                         actionMenu: true,
                         title: true,
                         counter: true,
                         searchFilter: true,
                         filter: true,
                         search: true,
                         colSetting: true

                 });
        },
        serviceAssignmentTableInit:function (overviewtype) {
                //Hide the content div of sponsorservice table
                $(".fg-contact-searchbox").removeClass("fg-has-filter");
                safariCount++;
                 $('#sponsorTable').hide();
                 $('#fg_dev_assignmentTable').show();
                 $(".fg-filter-check-icon").show();
                $('#serviceTable').hide();                
                $('#servicemenuBar').hide();
                $('#normalmenuBar').show();
                $('.searchbox').hide();
                $('.assignmentsearchbox').show();
                $('#fgoverviewrowchange').show();
                $('#fgrowchange').hide();
                $("#fgfuturerowchange").hide();
                //datatable show/hide                           
                
                $('#sponsor-column-settings').show();
                $('#tableColumns').hide();
                $('#fg-dev-filter-check').hide();
                $('.fg-action-search-filter').removeClass('fg-active-IB').addClass('fg-dis-none'); 
                $('.fg-action-search').removeClass('fg-has-filter');
               
                $('#fg-dev-coumnsetting-title').hide();
                
                $('#sponsor-column-settings').parent().removeClass('fg-sponsor-searchblock');
                $('.fg-search-div').removeClass('fg-search-right');
                
                FgOverviewassignmentList.init(overviewtype);
                FgSponsor.dynamicMenuhandling('overview');
        },
        
        dynamicMenuhandling: function(menuType) {
            if (menuType === 'sponsor' || menuType === 'overview' ) {
                $('.fgContactdrop').attr('data-type', 'active');
            } else {
                var activeTab = localStorage.getItem(fgLocalStorageNames.sponsor.active.serviceTab);
                if (activeTab === null || activeTab === '' || activeTab == 'undefined') {
                    localStorage.setItem(fgLocalStorageNames.sponsor.active.serviceTab, 'activeservice');
                    $("#activeservice-tab-li").find('a').trigger('click');
                    $('.fgContactdrop').attr('data-type', 'activeservice');
                } else {
                    $('.fgSponsordrop').attr('data-type', activeTab);
                    $("#" + activeTab + "-tab-li").find('a').trigger('click');
                    $('.fgContactdrop').attr('data-type', activeTab);
                }
                //for get the dynamic menues data type
                $("ul.fg_sponsor_nav_tab li").off('click')
                $("ul.fg_sponsor_nav_tab li").on('click', function () {
                    $('.fgContactdrop').attr('data-type', $(this).attr('data_type'));

                })   
            }
        },
        hideTablerowmenu:function(){
           $('#sponsor-column-settings').parent().addClass('fg-sponsor-searchblock');
           $('.fg-search-div').addClass('fg-search-right');
           $('#sponsor-column-settings').hide();
        },
        showTablerowmenu:function() {
           $('#fg-dev-filter-check').hide();
           $('#fg-dev-coumnsetting-title').hide();
           $('#fg-dev-columnsetting-flag').hide();
           $('#fgrowchange').hide();
           $('#fgoverviewrowchange').hide();
           
           $('#sponsor-column-settings').parent().removeClass('fg-sponsor-searchblock');
           $('.fg-search-div').removeClass('fg-search-right'); 
           $('#sponsor-column-settings').show();
           $("#fgfuturerowchange").show();
        },
        actionmenuSet:function(overviewType) {
           
            switch(overviewType){
                        case "active_assignments":
                            $('.fgContactdrop').attr('data-type', 'activeassignments'); 
                            break;
                        case "future_assignments":
                             $('.fgContactdrop').attr('data-type', 'futureassignments');
                            break;
                        case "former_assignments":
                             $('.fgContactdrop').attr('data-type', 'formerassignments');
                            break;
                        case "recently_ended":
                             $('.fgContactdrop').attr('data-type', 'recentlydelete');
                            break;
                    }
        }
    }
}();