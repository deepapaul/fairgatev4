FgConfirmStop = {
    
    //To get selected rows objects
    getSelectedObjects: function(bookedIds) {
        bookedIdArray = bookedIds.split(",")
        var dataObj = overviewTable.rows({
                                order: 'applied', // 'current', 'applied', 'index',  'original'
                                search: 'applied', // 'none',    'applied', 'removed'
                                page: 'all'      // 'all',     'current'
                            }).data();
        var selectedObjs = _.filter(dataObj, function(ret){ 
            if(bookedIdArray.indexOf(ret.SA_bookingId) > -1 ) { return ret.SA_bookingId; }
        });
        
        return selectedObjs; 
    },
    //method to stop service
    stopServiceFun: function() {
        var  selectedId = ''; 
        selectedId =  JSON.stringify(bookedIds);
       
        $('#popup').modal('hide');
        if (typeof CurrentContactId == 'undefined') {
            CurrentContactId = 0;
        }
        var passingData = {'selectedId':selectedId,'actionType': actionType, 'pageType': pageType, "CurrentContactId" : CurrentContactId};
        url = (actionType == "skipAssignment") ? pathSponsorServiceSkip : pathSponsorServiceStop ;
        $.post(url + "?rand=" + Math.random(), passingData, function(result) {            
            FgUtility.stopPageLoading();
            if (result.flash) {
                FgUtility.showToastr(result.flash);
            }            
            FgConfirmStop.callBackFn(result.activeServicesCount,actionType, pageType, bookedIds, servicesCount, skippedServicesCount);
        });
    },
    
    callBackFn: function (activeServicesCount,actionType, pageType, bookedIds, servicesCount, skippedServicesCount) {
        if (pageType == "sponsorlist") {            
             //in page service listing from sponsor detail
            var sponsorlistDet = localStorage.getItem(fgLocalStorageNames.sponsor.active.listDetails);
            var sponsorListJson = JSON.parse(sponsorlistDet);
            
            FgSponsor.sponsorserviceDatatableInit('service', sponsorListJson.id, sponsorListJson.serviceType);    
          if(actionType =='stopservice'){
                var updateArr = {"0":{'categoryId':'',"subCatId": sponsorListJson.id ,'dataType':sponsorListJson.type,'sidebarCount': servicesCount,"action":"remove"},
                "1":{'categoryId':'',"subCatId": 'former_assignments' ,'dataType':'overview','sidebarCount': servicesCount,"action":"add"},
                "2":{'categoryId':'',"subCatId": 'active_assignments' ,'dataType':'overview','sidebarCount': servicesCount,"action":"remove"},
                "3":{'categoryId':'',"subCatId": 'recently_ended' ,'dataType':'overview','sidebarCount': servicesCount,"action":"add"}};
            }else if (actionType =='deleteservice'){
                var activeId = $('#data-tabs').find('.active').attr('id');
                var tab = (activeId == "activeservice-tab-li") ? 'active_assignments':(activeId == "formerservice-tab-li")?'former_assignments':'future_assignments';
                if(tab == "active_assignments") {
                    var updateArr = {"0":{'categoryId':'',"subCatId": sponsorListJson.id ,'dataType':sponsorListJson.type,'sidebarCount': servicesCount,"action":"remove"},
                    "1":{'categoryId':'',"subCatId": tab ,'dataType':'overview','sidebarCount': servicesCount,"action":"remove"}};
                } else if(tab == "former_assignments"){                                                          
                    var recentlyEnded = servicesCount - skippedServicesCount;                    
                    var updateArr = {
                        "0":{'categoryId':'',"subCatId": tab ,'dataType':'overview','sidebarCount': servicesCount,"action":"remove"},
                    "1":{'categoryId':'',"subCatId": 'recently_ended' ,'dataType':'overview','sidebarCount': recentlyEnded ,"action":"remove"}};
                }else {
                    var updateArr = {"0":{'categoryId':'',"subCatId": tab ,'dataType':'overview','sidebarCount': servicesCount,"action":"remove"}};
                }
            }else{
                var updateArr = {"0":{'categoryId':'',"subCatId": sponsorListJson.id ,'dataType':sponsorListJson.type,'sidebarCount': servicesCount,"action":"remove"},"0":{'categoryId':'',"subCatId": sponsorListJson.id ,'dataType':sponsorListJson.type,'sidebarCount': servicesCount,"action":"remove"}};
            }
           // $.getJSON(sponsorSidebarCount, function(data) {
             //  var updateArr = data;
               FgCountUpdate.updateSidebar(updateArr); 
          // });
           
            FgClearCheckAll.init();            
        } else if(pageType == "servicelist") {
            //in page sponsor overview services
            $.getJSON(datapathUrl,function(data){
                //redraw datatable
                $('table[data-contact-service]').each(function(){
                    var tableId = $(this).attr('data-contact-service');
                    serviceTableObj = (tableId == "activesponsor") ? serviceTable.activesponsor : ((tableId == "past") ? serviceTable.past : serviceTable.future )                    
                    serviceTableObj.clear();
                    serviceTableObj.rows.add(data[tableId]);
                    if(data[tableId].length==0){
                        $('div[data-type='+tableId+']').hide();
                        $('div[data-empty='+tableId+']').show();
                    } else {
                        $('div[data-type='+tableId+']').show();
                        $('div[data-empty='+tableId+']').hide();
                    } 
                    serviceTableObj.draw();     
                });
                // update count in panel tabs
                $("li[name=fg-dev-services-tab]").find(".badge").html(activeServicesCount);
                // update checkboxes
                FgClearCheckAllServices.init();
            }); 
        } else {            
            //in page recently ended
            var datapath = $('table.overviewTable').attr('data-ajax-path');   
            var selectedIds = FgConfirmStop.getSelectedObjects(bookedIds);
            $.getJSON( datapath, { assignmentType: pageType })
                .done(function( result ) {
                    overviewTable.clear();
                    overviewTable.rows.add(result['aaData']);
                    overviewTable.draw();  
                    if(actionType =='stopassignmentOverview'){
                    //update sidebar count
                        if(pageType == "active_assignments") {
                             var updateArr = [];
                              updateArr.push({'categoryId':'',"subCatId": pageType ,'dataType':'overview','sidebarCount': servicesCount,"action":"remove"},
                                              {'categoryId':'',"subCatId": 'former_assignments' ,'dataType':'overview','sidebarCount': servicesCount,"action":"add"},
                                              {'categoryId':'',"subCatId": 'recently_ended' ,'dataType':'overview','sidebarCount': servicesCount,"action":"add"});
                                    for(var i=0,j=2;i<selectedIds.length;i++,j++){
                                   updateArr.push({'categoryId':selectedIds[i].SA_service_category,"subCatId": selectedIds[i].SA_serviceId ,'dataType':'service','sidebarCount': 1,"action":"remove"});
                                 }
                        }   
                    }else{//delete
                        //update sidebar count
                        if(pageType == "active_assignments") {
                            var updateArr = [];
                              updateArr.push({'categoryId':'',"subCatId": pageType ,'dataType':'overview','sidebarCount': servicesCount,"action":"remove"});
                                    for(var i=0,j=1;i<selectedIds.length;i++,j++){
                                   updateArr.push({'categoryId':selectedIds[i].SA_service_category,"subCatId": selectedIds[i].SA_serviceId ,'dataType':'service','sidebarCount': 1,"action":"remove"});
                                 }
                        
                        }else if(pageType == "recently_ended") {
                           var updateArr = {"0":{'categoryId':'',"subCatId": pageType ,'dataType':'overview','sidebarCount': servicesCount,"action":"remove"} };
//                                         "1":{'categoryId':'',"subCatId": 'former_assignments' ,'dataType':'overview','sidebarCount': servicesCount,"action":"remove"}
                        }else{
                           var updateArr = {"0":{'categoryId':'',"subCatId": pageType ,'dataType':'overview','sidebarCount': servicesCount,"action":"remove"}};
                        } 
                    }
                    //$.getJSON(sponsorSidebarCount, function(data) {
                     //   var updateArr = data;
                        FgCountUpdate.updateSidebar(updateArr); 
                    //});
                    FgCountUpdate.updateDatatableCount("remove", servicesCount);
                    //clear check all 
                    FgClearCheckAll.init();                      
                });          
        } 
    },
    
}