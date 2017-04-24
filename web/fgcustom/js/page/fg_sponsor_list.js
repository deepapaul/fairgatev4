
var exportflag = true;
FgSponsorList = {
    checkIfExist: function(checkValue) {
        var flag = false;
        $.each(checkValue, function(keys, values) {
            if (keys === 'id' && _.chain(jsonData["CF"]['entry']).where({"id": values}).flatten().pluck('id').value() !== '') {
                flag = true;
            }
        })
        return flag;
    },
    deletecheck: function(tableSettingValue, jsonData) {
        $.each(tableSettingValue, function(keys, values) {
            $.each(values, function(key, value) {
                if (key === 'type' && values[key] === 'CF') {
                    if (!FgSponsorList.checkIfExist(values)) {
                        delete tableSettingValue[keys];
                    }
                } else if (_.has(jsonData, 'SS') && values['type'] == 'SS' && key == 'type') {
                    if (_.size(_.where(jsonData['SS']['entry'], {"id": values['id']})) > 0) {
                        inputLength = _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length;
                        var deleteCount = 0;
                        if (inputLength > 0) {
                          var subIds = tableSettingValue[keys]['sub_ids'].split(",");
                          var actualSubIds = tableSettingValue[keys]['sub_ids'].split(",");
                            $.each(subIds, function(subKeys, subValues) {
                                if(subValues=='') {
                                    actualSubIds.splice(0, 1);
                                } else if(subValues !='all') {
                                        var objId = _.chain(jsonData["SS"]["entry"]).where({id: values['id']}).pluck("input").flatten().where({id: subValues}).value()
                                        if (objId.length == 0) {
                                            index = $.inArray(subValues,actualSubIds);
                                            actualSubIds.splice(index, 1);                                           
                                        }
                                  }
                            });
                            if (actualSubIds.length > 0) {
                                tableSettingValue[keys]['sub_ids'] = actualSubIds.join();                               
                                
                                if (tableSettingValue[keys]['sub_ids'].charAt(tableSettingValue[keys]['sub_ids'].length - 1) == ',') {
                                    tableSettingValue[keys]['sub_ids'] = tableSettingValue[keys]['sub_ids'].substring(0, tableSettingValue[keys]['sub_ids'].length - 1);
                                }
                                if (tableSettingValue[keys]['sub_ids'] == '' || tableSettingValue[keys]['sub_ids'] == ',') {
                                    tableSettingValue[keys]['sub_ids'] = 'all';
                                }
                            } else {
                                delete tableSettingValue[keys];
                            }
                        } else {
                            delete tableSettingValue[keys];
                        }

                    }
                    else {
                        delete tableSettingValue[keys];
                    }
                }
            });
        });
        return tableSettingValue;
    }

};
FgSponsorColumnHeading = {
    getColumnNames: function(tableSettingValue, general_table_title_array, exportflag) {
        tableColumnTitle = [];
        tableColumnTitle.push({"sTitle": "<div class='fg-th-wrap'><i class='chk_cnt' ></i>&nbsp;<input type='checkbox' name='check_all' id='check_all' class='dataTable_checkall'></div>&nbsp;", "mData": "edit", "bSortable": false});
        if (exportflag == false) {
            tableColumnTitle.push({"sTitle": "<span class='fg-contact-wrap'>" + datatabletranslations['contact_name'] + "</span>&nbsp;", "mData": "contactname", "bSortable": true});
        }
        if (contactType == 'archivedsponsor') {
            tableColumnTitle.push({"sTitle": datatabletranslations['archived_On'], "mData": "archived_on", "bSortable": true});
        }
        $.each(tableSettingValue, function(keys, values) {

            $.each(values, function(key, value) {

                if (key === 'type' && values[key] === 'CF') {

                    $.each(jsonData['CF']['entry'], function(jsonKey, jsonValue) {

                        if (jsonValue['id'] === values['id']) {
                            if (exportflag === false) {
                                var atrShortName = jsonValue['shortName'];
                                if ($.inArray(jsonValue['id'], corrAddrFieldIds) != -1) {
                                    if (atrShortName.indexOf("(Korr.)") >= 0) {
                                        atrShortName = atrShortName.replace('(Korr.)', '<span class="fg-left-exportblk"><i class="fa fa-home"></i></span>');
                                    } else {
                                        atrShortName = atrShortName + '<span class="fg-left-exportblk"><i class="fa fa-home"></i></span>';
                                    }
                                }
                                if ($.inArray(jsonValue['id'], invAddrFieldIds) != -1) {
                                    if (atrShortName.indexOf("(Rg.)") >= 0) {
                                        atrShortName = atrShortName.replace('(Rg.)', '<span class="fg-left-exportblk"><i class="fa fa-money"></i></span>');
                                    } else {
                                        atrShortName = atrShortName + '<span class="fg-left-exportblk"><i class="fa fa-money"></i></span>';
                                    }
                                }
                                tableColumnTitle.push({"sTitle": atrShortName, "mData": values['name']});
                            } else {
                                var atrShortNamexport = $.trim(jsonValue['shortName']);
                                if ($.inArray(jsonValue['id'], corrAddrFieldIds) !== -1) {
                                    if (atrShortNamexport.indexOf("(Korr.)") >= 0) {
                                        atrShortNamexport = atrShortNamexport;
                                    } else {
                                        atrShortNamexport = atrShortNamexport + "&nbsp;(" + datatabletranslations['Korr'] + ")";
                                    }
                                }
                                if ($.inArray(jsonValue['id'], invAddrFieldIds) !== -1) {
                                    if (atrShortNamexport.indexOf("(Rg.)") >= 0) {
                                        atrShortNamexport = atrShortNamexport;
                                    } else {
                                        atrShortNamexport = atrShortNamexport + "&nbsp;(" + datatabletranslations['Reg'] + ")";
                                    }
                                }
                                tableColumnTitle.push({"sTitle": atrShortNamexport, "mData": values['name']});
                            }
                        }

                    });
                } else if (key === 'type' && values[key] === 'SA') {
                    $.each(jsonData['SA']['entry'], function(jsonKey, jsonValue) {
                        if (jsonValue['id'] === values['id']) {
                            tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
                        }
                    });
                } else if (key === 'type' && values[key] === 'CO') { //set the CO condition for membership
                    $.each(jsonData['CO']['entry'], function (jsonKey, jsonValue) {
                        if (jsonValue['id'] === values['id']) {
                            tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
                        }
                    });
                }
                
                else if (key == 'type' && values[key] == 'G') {

                    if (general_table_title_array.hasOwnProperty(values['id'])) {
                        if (values['id'] == "join_leave_dates") {
                            tableColumnTitle.push({"sTitle": datatabletranslations['Joining_Date'], "mData": "joining_date"});
                            tableColumnTitle.push({"sTitle": datatabletranslations['Leaving_Date'], "mData": "leaving_date"});
                        } else {
                            tableColumnTitle.push({"sTitle": general_table_title_array[values['id']], "mData": values['name']});
                        }

                    }

                } else if (key == 'type' && (values[key] == 'SS')) {
                    var popovercontent = '';
                    var title = '';
                    var fcount = 0;
                    if (tableSettingValue[keys]['sub_ids'] == 'all') {
                        if (typeof jsonData['SS'] !== 'undefined' && _.size(jsonData['SS']['entry']) > 0) {
                            if (_.size(_.findWhere(jsonData['SS']['entry'], {"id": values['id']}))) {
                                title = _.where(jsonData['SS']['entry'], {"id": values['id']})[0].title;
                                fcount = _.size(_.where(jsonData['SS']['entry'], {"id": values['id']})[0]['input'])
                                inputObj = _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped;
                                $.each(inputObj, function(jsonKey, jsonValue) {
                                    popovercontent += jsonValue['title'] + "<br>";
                                });

                            }

                            var squarebracket = '';
                            if (fcount == 1) {
                                squarebracket = '';
                                popovercontent = popovercontent.replace(/<br>/g, "");
                                dummyTitle = title;
                                title = popovercontent;
                                popovercontent = dummyTitle;
                                title = (exportflag == false) ? "<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + title + "</span>" : title;

                            } else {
                                squarebracket = (exportflag == false) ? '&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + datatabletranslations['All'] + '</span>]' : '&nbsp;[' + fcount + '/' + fcount + ']';
                            }

                            var actualTitle = title;
                            popovervalues = squarebracket
                            if (exportflag == false) {
                                tableColumnTitle.push({"sTitle": actualTitle + popovervalues, "mData": values['name']});
                            } else {
                                tableColumnTitle.push({"sTitle": actualTitle + popovervalues, "mData": values['name']});
                            }
                        }
                    } else {
                        if (_.size(_.where(jsonData['SS']['entry'], {"id": values['id']})[0]['input']) > 0) {
                            inputObj = _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped;
                            functionArray = values['sub_ids'].split(",");
                            $.each(functionArray, function(iterationkey, iterationvalue) {
                                titleContent = _.chain(jsonData["SS"]['entry']).where({"id": values['id']}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value();
                                popovercontent += _.chain(jsonData["SS"]['entry']).where({"id": values['id']}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value() + "<br>";
                            })
                            if (functionArray.length > 1) {
                                var title = _.where(jsonData['SS']['entry'], {"id": values['id']})[0].title;
                            } else {
                                var title = titleContent;
                                popovercontent = _.where(jsonData['SS']['entry'], {"id": values['id']})[0].title;
                            }
                            var bracket = (functionArray.length > 1) ? "&nbsp;[" + functionArray.length + "/" + _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length + "]" : '';

                            fcount = title;
                            var popovervalues = '';
                            if (functionArray.length > 1) {
                                popovervalues = fcount + "&nbsp;[<span class='fg-custom-popovers fg-dotted-br'  data-content='" + popovercontent + "'>" + functionArray.length + "/" + _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length + "</span>]";
                            } else {
                                popovervalues = "&nbsp;<span class='fg-custom-popovers fg-dotted-br'  data-content='" + popovercontent + "'>" + fcount + '' + "</span>";
                            }


                            if (exportflag == false) {
                                tableColumnTitle.push({"sTitle": popovervalues, "mData": values['name']});
                            } else {
                                tableColumnTitle.push({"sTitle": fcount + bracket, "mData": values['name']});
                            }
                        }



                    }
                }
            });
        }); //end of main iteration

        return tableColumnTitle;
    }

};

function callSidebar() {

    /* sidebar settings */
    FgSidebar.jsonData = true;
    FgSidebar.ActiveMenuDetVar = ActiveMenuDetVar;
    FgSidebar.activeMenuVar = fgLocalStorageNames.sponsor.active.sidebarActiveMenu;
    FgSidebar.activeSubMenuVar = fgLocalStorageNames.sponsor.active.sidebarActiveSubMenu;
    FgSidebar.activeOptionsVar = 'activeSponsorOptions' + '-' + clubId + '-' + contactId;
    FgSidebar.defaultMenu = 'bookmark_li';
    FgSidebar.defaultSubMenu = 'allActive';
    FgSidebar.bookemarkUpdateUrl = bookmarkUpdateUrl;
    FgSidebar.list = 'club';
    FgSidebar.options = [];
    FgSidebar.newElementLevel1 = newElementLevel1;
    FgSidebar.newElementLevel2 = newElementLevel2;
    //FgSidebar.newElementLevel2Sub = newElementLevel2Sub;
    FgSidebar.defaultTitle = defaultTitle;
    FgSidebar.newElementUrl = newElementUrl;
    FgSidebar.module = module;
    sidebarClickObj = {
        currentModule: FgSidebar.module,
        clubId: clubId,
        contactId: contactId,
        tableDetails: {'object': sponsorTable, 'name': FgSponsorTable},
        oldFilterCountVar: 'oldfiltercount-' + clubId + "-" + contactId,
        filterPath: saveFilterPath,
        filterNameVar: 'sponsor_filter',
        oldFilterTypeCountVar: 'oldSponsorfiltercount-sponsor-' + clubId + "-" + contactId
    };
    $.extend( handleCountOrSidebarClick, sidebarClickObj );
    
    FgSidebar.settings = {};
    /* sidebar bookmark settings */
    var bookmarkId = 'bookmark_li';
    var filterBookmark = {};
    var allActiveMenu = [{'isAllActive': 1, 'title': defaultTitle}];
    
    var allActiveAssignment = [{'isOverview': 1, 'title': assignmentoverviewTitles.activeAssignments.title,'itemType':'overview','id':'active_assignments'}];
    var allFutureAssignment = [{'isOverview': 1, 'title': assignmentoverviewTitles.futureAssignments.title,'itemType':'overview','id':'future_assignments'}];
    var allRecentlyend = [{'isOverview': 1, 'title': assignmentoverviewTitles.recentlyEnded.title,'itemType':'overview','id':'recently_ended'}];
    var allFormerAssignment = [{'isOverview': 1, 'title': assignmentoverviewTitles.formerAssignments.title,'itemType':'overview','id':'former_assignments'}];
    
    filterBookmark = allFormerAssignment.concat(jsonData['bookmark']['entry']);
    filterBookmark = allRecentlyend.concat(filterBookmark);
    filterBookmark = allFutureAssignment.concat(filterBookmark);
    filterBookmark = allActiveAssignment.concat(filterBookmark);
    filterBookmark = allActiveMenu.concat(filterBookmark);
    var bookmarksMenu = {templateType: 'general', menuType: 'bookmark', 'parent': {id: bookmarkId, class: 'tooltips bookmark-link', name: 'bookmark-link', 'data-placement': "right"}, title: bookmarkTitle, template: '#template_sidebar_menu', 'settings': {"0": {'title': sortingTitle, 'url': bookMarkSortingPath}}, 'menu': {'items': filterBookmark}};
    FgSidebar.settings[bookmarkId] = bookmarksMenu;

    var contactid = 'contact_li';

    var contactData = (typeof jsonData['CN'] !== "undefined" && typeof jsonData['CN']['entry'] !== "undefined") ? jsonData['CN']['entry'] : {};
    contactData[0]['hasSettings'] = 0;
    contactData[1]['hasSettings'] = 0;
    var contactMenu = {templateType: 'menu2level', menuType: 'contactOptions', 'parent': {id: contactid, class: contactid}, title: contactDataTitle, template: '#template_sidebar_menu2level', 'menu': {'items': contactData}};
    FgSidebar.settings[contactid] = contactMenu;
    FgSidebar.options.push({'id': contactid, 'title': contactDataTitle});


    var serviceId = 'services_li';
    var serviceData = (typeof jsonData['SS'] !== "undefined" && typeof jsonData['SS']['entry'] !== "undefined") ? jsonData['SS']['entry'] : {};
    var serviceMenu = {templateType: 'menu2level', menuType: 'services', 'parent': {id: serviceId, class: serviceId}, title: servicesTitle, template: '#template_sidebar_menu2level', 'menu': {'items': serviceData}};
    var level1Settings = {"0": {'type': 'newElement', 'title': sidebar_create_category, 'url': '#', 'contentType': 'category', 'target': '#services_li', 'hierarchy': '1'}, "1": {'title': sidebar_category_settings, 'url': manageCategoryPath}};
    var level2Settings = {"0": {'type': 'newElement', 'title': sidebar_create_service, 'placeHolder':addService,'url':'#', 'contentType': 'service', 'hierarchy': '2' },"1": {'title': sidebar_service_settings, 'url': manageServicesPath}};
    serviceMenu.settingsLevel1 = level1Settings;
    serviceMenu.settingsLevel2 = level2Settings;
    FgSidebar.settings[serviceId] = serviceMenu;
    FgSidebar.options.push({'id': serviceId, 'title': servicesTitle});
    
//    var assignId = 'assignments_li';
//    var assignData = (typeof jsonData['AO'] !== "undefined" && typeof jsonData['AO']['entry'] !== "undefined") ? jsonData['AO']['entry'] : {};
//    var assgnmentMenu = {templateType: 'general', menuType: 'assignment','parent': {id: assignId, class: assignId , name: 'assignment-link', 'data-placement': "right"}, title: assgnmentTitle, template: '#template_sidebar_menu', 'menu': {'items': assignData}};
//    FgSidebar.settings[assignId] = assgnmentMenu;
//    FgSidebar.options.push({'id': assignId, 'title': assgnmentTitle});

    /* sidebar saved filter settings */
    var filterSavedFilter = jsonData['filter']['entry'];
    FgSidebar.filterCountUrl = sponsorPageVars.filterCountUrl;
    FgSidebar.filterDataUrl = sponsorPageVars.filterDataUrl;
    FgSidebar.bookemarkUpdateUrl = sponsorPageVars.bookemarkUpdateUrl;
    
    var filterMenu = {templateType: 'general', menuType: 'filter', 'parent': {id: 'filter_li'}, title: sponsorPageVars.filterTitle, template: '#template_sidebar_menu', 'settings': {"0": {'title': sponsorPageVars.filterSettingsTitle, 'url': sponsorPageVars.filterSettingsUrl}}, 'menu': {'items': filterSavedFilter}};
    FgSidebar.settings['filter_li'] = filterMenu;
  
    FgSidebar.init();
    FgUtility.stopPageLoading();

    //For handling the pre-opening of the sponsor menu
    FgSidebar.handlePreOpening('open',module);
    checkForBrokenFilterCriteria();
}
/* Function to check whether any saved filter criteria is broken */
function checkForBrokenFilterCriteria() {
    var cnt = jsonData.filter.entry.length;
    for (var i = 0; i < cnt; i++) {
        var filterJson = $.parseJSON(jsonData.filter.entry[i].filterData);
        var filterId = jsonData.filter.entry[i].id;
        var isBroken = FgValidateFilter.init(jsonData, filterJson, 'sponsor');
        if (!isBroken) {
            $('a[filter_Id="' + filterId + '"]').empty().append('<i class="fa fa-warning fg-warning fg-broken-filter"></i>');
        }
    }
}
function callFilterFlag() {
    $("#filterFlag").on("click", function() {
        oldFilterCount = localStorage.getItem('oldSponsorfiltercount-' + contactType+'-'+clubId + "-" + contactId);
        newFilterCount = _.size(filterdata['sponsor_filter']);
        if (newFilterCount != oldFilterCount) {
            $(".fa-filter").show();
            
        } else {
            $(".fa-filter").hide();
        }

        if ($(this).is(':checked')) {
            $('.filter-alert').show();
            localStorage.setItem(filterDisplayFlagStorage, 1);
        } else {
            $('.filter-alert').hide();
            localStorage.setItem(filterDisplayFlagStorage, 0);
        }
    });

}

function callFilter() {
    
    var oldContactfilter = localStorage.getItem(filterStorage);
        FgUtility.startPageLoading();
    filter = $("#target").searchFilter({
        jsonGlobalVar: jsonData,
        submit: '#search',
        save: '#saveFilter',
        filterName: filterName,
        storageName: filterStorage,
        addBtn: '#addCriteria',
        clearBtn: '.remove-filter',
        dateFormat: FgApp.dateFormat,
        customSelect: true,
        conditions: filterCondition,
        selectTitle: selectTitileTrans,
        criteria: '<div class="col-md-1"><span class="fg-criterion">' + criteriTitle + ':</span></div>',
        savedCallback: function() {
            setTimeout(function() {
                $("#callPopupFunction").click();
            }, 1);
        },
        onComplete: function(data) {
            if (localStorage.getItem(filterDisplayFlagStorage) == 0) {
                $('.filter-alert').hide();
            }
            if (data !== 0) {
                if (data === 1) {
                    filterdata = 'contact';
                    localStorage.setItem('oldSponsorfiltercount-' + contactType+'-'+clubId + "-" + contactId, 0);
                    $("#tcount").hide();
                    $("#fg-slash").hide();
                } else {
                    filterdata = data;
                    oldFilterCount = localStorage.getItem('oldSponsorfiltercount-' + contactType+'-'+clubId + "-" + contactId);
                    newFilterCount = _.size(filterdata['sponsor_filter']);

                    if (oldFilterCount === null && newFilterCount >= 1) {
                        $("#tcount").hide();
                        $("#fg-slash").hide();
                        $(".fa-filter").hide();
                    } else if (parseInt(newFilterCount) == parseInt(oldFilterCount)) {
                        $(".fa-filter").hide();
                        $("#tcount").hide();
                        $("#fg-slash").hide();
                    } else if (parseInt(newFilterCount) !== parseInt(oldFilterCount)) {
                        $(".fa-filter").show();
                         $('.filter-alert').hide();
                         if (localStorage.getItem(filterDisplayFlagStorage) == 1) {
                           $('.filter-alert').show();
                         } 
                        $("#tcount").show();
                        $("#fg-slash").show();
                      
                    } else {
                        $("#tcount").hide();
                        $("#fg-slash").hide();
                        $(".fa-filter").hide();
                    }
                }
                if(contactType !='archivedsponsor') {
                    filterCallback();
                }    
                if (!$.isEmptyObject(sponsorTable)) {
                    sponsorTable.api().draw();
                } else {
                    FgSponsorTable.init();
                }

                if ($("#searchbox").val() !== '') {
                    $("#tcount").show();
                    $("#fg-slash").show();
                }
                $('.alert').addClass('display-hide');

            } else {
                FgUtility.stopPageLoading();
                isFilterBroken = 1;
                $('.remove-filter').attr('disabled', true);
                filterdata = 0;
                $('.filter-alert').show();
                //enable the filter checkbox
                $("#filterFlag").attr("checked", true);
                //store the filterdisplay flag in html5
                localStorage.setItem(filterDisplayFlagStorage, 1);
                //update the property of the checkbox of jquery uniform plugin
                jQuery.uniform.update('#filterFlag');
                $('.alert').removeClass('display-hide');
                if (!$.isEmptyObject(sponsorTable)) {
                    sponsorTable.api().clear();
                    sponsorTable.api().draw();
                } else {
                    FgSponsorTable.init();
                }
            }
            if(contactType !='archivedsponsor') {
                    filterCallback();
                }  

        }
    });
}
var filterCallback = function() {

    if (FgSidebar.isFirstTime) {
        callSidebar();
        setSidebarCount();
        FgSidebar.isFirstTime = false;
    }
}

var setSidebarCount = function () {
    $.getJSON(sponsorSidebarCount, function(data) {
        var countData=data;
        FgCountUpdate.updateSidebar(countData);
        FgSidebar.show();
   });
   
}

jQuery(document).ready(function() {
    if ($("#inlineEditContact").is(':checked')) {
        $("#inlineEditContact").attr("checked", false);
        jQuery.uniform.update('#inlineEditContact');
        sessionStorage.setItem('inlineEditContactFlag', 0);
    }
//bind animation event to filter button
    FgSidebar.filterAnimationInit("#search");
    /* Function to hide export menu if no contacts available */
//    $('.fgContactdrop').on('click', function() {
//
//        var cntcount = $("#fcount").text();
//        if (cntcount === '0') {
//            $(".dropdown-menu").find(".fg-dev-exportmenu").addClass("hide");
//        } else {
//            $(".dropdown-menu").find(".fg-dev-exportmenu").removeClass("hide");
//        }
//    });
    /*Function ends here*/

    $(".fa-filter").hide();
    $.getJSON(jsonDataPath, function(data) {
        jsonData = data;
        var tblSettingValue = localStorage.getItem(tableSettingValueStorage);
        var tblSettingId = localStorage.getItem(tableSettingIdStorage);
        if (tblSettingValue === null || tblSettingValue === '' || tblSettingValue == 'undefined') {
            tblSettingValue = $("#fg-dev-defaultcolumnsetting").val();
        } else {
            $("#tableColumns").select2('val',tblSettingId); 
        }

        tableSettingValues = $.parseJSON(tblSettingValue);
        tableSettingValues = FgSponsorList.deletecheck(tableSettingValues, jsonData);

        settingValue = tableSettingValues;

        localStorage.setItem(tableSettingValueStorage, JSON.stringify(tableSettingValues));

        tableColumnTitles = FgSponsorColumnHeading.getColumnNames(settingValue, general_table_title_array, false);

        //tableColumnTitles = getTableColumns(settingValue);
        localStorage.setItem(tableColumnTitleStorage, JSON.stringify(tableColumnTitles));
        callFilter();
        callFilterFlag();

    });
    var filterflag = localStorage.getItem(filterDisplayFlagStorage);
     if (filterflag !== "1") {
        $("#filterFlag").attr("checked", false);
        localStorage.setItem(filterDisplayFlagStorage, 0);
        jQuery.uniform.update('#filterFlag');
    } else if (filterflag === "1") {
        $("#filterFlag").attr("checked", true);
        jQuery.uniform.update('#filterFlag');
        $('.filter-alert').show();
    }
    if (filterflag == "0") {
        $('.filter-alert').hide();
        //enable the filter checkbox
        $("#filterFlag").attr("checked", false);
        jQuery.uniform.update('#filterFlag');
    }


    $('#callPopupFunction').click(function(event) {
        event.stopPropagation();
        event.preventDefault();
        $('#filternameText').editable('toggle');
        $('#filternameText').editable('setValue', null);
    });
    //bind the click event to the tableColumn select box

    $('#tableColumns').on('click', function() {
        tableSettingValues = $.parseJSON($("#tableColumns option:selected").attr("data-attributes"));
        localStorage.setItem(tableSettingIdStorage, $("#tableColumns option:selected").val());
        tableSettingValue = FgSponsorList.deletecheck(tableSettingValues, jsonData);
        settingValue = tableSettingValue;
        localStorage.setItem(tableSettingValueStorage, JSON.stringify(tableSettingValue));
        tableColumnTitles = FgSponsorColumnHeading.getColumnNames(settingValue, general_table_title_array, false);
        localStorage.setItem(tableColumnTitleStorage, JSON.stringify(tableColumnTitles));
        setTimeout(function() {
            window.location = sponsorHomeLink;
        }, 100);


    });
    //bind the click event to filter close button
    $('.fg_filter_hide').on('click', function() {

        $('.filter-alert').hide();
        if ($('#filterFlag').length > 0 && $('#filterFlag').is(':checked')) {

            $('#filterFlag').attr('checked', false);
            //update the property of the checkbox of jquery uniform plugin
            localStorage.setItem(filterDisplayFlagStorage, 0);
            $.uniform.update('#filterFlag');
        } else {

            localStorage.setItem(filterDisplayFlagStorage, 1);
        }
    });
    
    $('#filternameText').editable({
        type: 'text',
        url: sponsorPageVars.filterSave,
        pk: clubId,
        emptytext: '',
        display: false,
        placement: 'bottom',
        inputclass: 'form-control input-sm',
        validate: function(value) {
            if ($.trim(value) == '') {
                return sponsorPageVars.filterValidation;
            }
        },
        params: function(params) {
            var stringifyed_data = localStorage.getItem(filterStorage);
            stringifyed_data = stringifyed_data.replace(/,"disabled":true/g, '');
            //stringifyed_data = stringifyed_data.replace(',"disabled\":true', '');
            params.jString = stringifyed_data; //JSON.stringify(data);
            params.contactType = contactType;
            return params;
        },
        success: function(data) {
            $('#filternameText').html('');
            if (data.operation == 'INSERT') {
                 parentMenuId = 'filter_li';
                 var menuHtml = FgSidebar.generateHtml(FgSidebar.templateLevel2, {'subMenu': data});
                 var parentMenu = $('#' + parentMenuId + ' ul.sub-menu');
                 $('#' + parentMenuId).addClass('open');
                 $(menuHtml).appendTo(parentMenu);
                 FgSidebar.handleArrows(parentMenu,'');
             }
             FgUtility.showToastr(sponsorPageVars.filterSaveMsg);
        }
    });

});

