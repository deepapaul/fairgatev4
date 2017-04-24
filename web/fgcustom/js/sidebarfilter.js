var safariCount = 0;
jQuery('body').off('click', '.page-sidebar ul ul ul .sidebabar-link, ul ul .sidebabar-link');
jQuery('body').on('click', '.page-sidebar ul ul ul .sidebabar-link, ul ul .sidebabar-link', function (e) {
    $('.remove-filter').attr('disabled', false);
    e.stopImmediatePropagation();
    var dataType = $(this).attr('data-type');
    var dataCategory = $(this).attr('data-categoryid');
    var dataId = $(this).attr('data-id');
    var pageTitle = $(this).find('.title').text();
    var pageCount = $(this).find('span.badge').text();
    totalCount = $(this).find('span.badge').text();
    var dataFnType = ($(this).attr('data-fntype')) ? $(this).attr('data-fntype') : "none";
    var dataFilter = ($(this).attr('data-filter')) ? $(this).attr('data-filter') : "";
    var dataUrl = $(this).attr('data-url');
    var clubData = $(this).attr('data-club') ? $(this).attr('data-club') : '';
    handleCountOrSidebarClick.clubData = clubData;
    handleCountOrSidebarClick.dataFnType = dataFnType;
    handleCountOrSidebarClick.dataFilter = dataFilter;
    handleCountOrSidebarClick.dataUrl = dataUrl;
    //start the page loading process
    FgUtility.startPageLoading();
    if (typeof handleCountOrSidebarClick.tableDetails.object !== 'undefined') {
        if (!$.isEmptyObject(handleCountOrSidebarClick.tableDetails.object)) {
            handleCountOrSidebarClick.tableDetails.object.api().search('');
        }
    }
    if (typeof type === 'undefined') {
        type = 'CLUB';
    }
    handleCountOrSidebarClick.updateFilter(dataType, filterDisplayFlagStorage, type, clubId, contactId, dataId, dataCategory, '', 'sidebar', '', pageTitle, pageCount, '');
});

handleCountOrSidebarClick = {
    currentModule: 'document',
    dataFnType: '',
    dataFilter: '',
    dataUrl: '',
    clubId: '',
    contactId: '',
    clubData: '',
    totalCount: 0,
    filterNameVar: '',
    updateFilter: function (dataType, filterDisplayFlagStorage, type, clubId, contactId, dataId, dataCategory, clubidentifier, fromType, teamId, pageTitle, pageCount, pageTarget, source, sourceType, catid, subcatid, catclubid) {
        var filterExportData = '';
        var filterExportCount = 0;
        var activeMenuDet = {};
        //set the total count
        $("#tcount").html(pageCount);
        var currentModule = handleCountOrSidebarClick.currentModule;
        if ((source === undefined) || (source === '')) {
            source = handleCountOrSidebarClick.currentModule;
        }
        var filterName = (handleCountOrSidebarClick.filterNameVar === '') ? source + '_filter' : handleCountOrSidebarClick.filterNameVar;
        var filterStorage = (source === 'document') ? fgLocalStorageNames[source][type.toLowerCase()]['filterStorage'] : fgLocalStorageNames[source]['active']['filterStorage'];
        var parentli = '';
        var subparentli = '';
        var submenuli = '';
        var isBookmark = 0;
        var filtertype = '';
        var exportData = {};
        exportData[filterName] = {};
        exportData[filterName][0] = {};
        exportData[filterName][0]['disabled'] = true;
        exportData[filterName][0]['connector'] = null;
        $('#searchbox').val('');
        var typeClick = handleCountOrSidebarClick.handleSidebarClickOfType(currentModule, dataType, pageCount);
        var categoryClubId = typeClick.categoryClubId;
        var dataTypeArr = typeClick.dataTypeArr;
        dataType = typeClick.dataType;
        if (fromType === 'count') {
            currentModule = source;
            if (dataType.indexOf("bookmark") >= 0) {
                dataType = dataType.replace('bookmark_', '');
                isBookmark = 1;
            }
        }
        localStorage.setItem(filterDisplayFlagStorage, 0);
        switch (dataType) {
            case 'allActive':
                filterdata = ((currentModule === 'contact') || (currentModule === 'sponsor')) ? currentModule : 'all';
                var activeClick = handleCountOrSidebarClick.handleAllActiveClick(currentModule, teamId, exportData, filterExportData, filterName, activeMenuDet, fromType, filterDisplayFlagStorage, filterExportCount);
                exportData = activeClick.exportData;
                filterExportData = activeClick.filterExportData;
                activeMenuDet = activeClick.activeMenuDet;
                break;
            case 'filter':
            case 'FILTER':
                if (fromType === 'count') {
                    if ((source === 'contact') && (sourceType === 'filterrole')) {
                        parentli = 'FILTERROLES-' + clubId + '_li';
                        submenuli = parentli + '_' + subcatid;
                    } else {
                        parentli = 'filter_li';
                        submenuli = parentli + '_' + subcatid;
                    }
                } else {
                    var filterClick = handleCountOrSidebarClick.handleFilterClick(currentModule, activeMenuDet, dataId, filterDisplayFlagStorage, filterExportData, filterExportCount, filterStorage);
                    activeMenuDet = filterClick.activeMenuDet;
                    filterExportData = filterClick.filterExportData;
                }
                break;
            case 'membership':
            case 'MEMBERSHIP':
            case 'CEBF':
                if (dataType === 'MEMBERSHIP') {
                    filtertype = 'CM';
                    if (isBookmark) {
                        subparentli = parentli + 'membership_li';
                    } else {
                        parentli = 'CONTACT_li';
                        subparentli = parentli + '_' + catid;
                    }
                    submenuli = subparentli + '_' + subcatid;
                    
                    //TO handle single level membership menu
                    if(typeof membershipLevel != 'undefined'){
                        if(membershipLevel == 1 && !isBookmark){
                           parentli = 'CONTACT_li';
                           subparentli = '';
                           submenuli = parentli + '_' + subcatid;
                        }
                    }
                
                } else {
                    filtertype = 'FI';
                    parentli = 'FI_li';
                    submenuli = parentli + '_' + subcatid;
                }
                exportData[filterName][0]['type'] = (dataType === 'membership') ? 'CM' : 'FI';
                exportData[filterName][0]['entry'] = 'membership';
                exportData[filterName][0]['condition'] = "is";
                exportData[filterName][0]['input1'] = dataId;
                filterExportCount = _.size(exportData['contact_filter']);
                localStorage.setItem('oldfiltercount-' + clubId + "-" + contactId, filterExportCount);
                filterExportData = JSON.stringify(exportData);
                $("#filterFlag").attr("checked", false);
                jQuery.uniform.update('#filterFlag');
                break;
            case 'fed_membership':
                filtertype = 'FM';
                if (isBookmark) {
                    subparentli = parentli + 'fed_membership_li';
                } else {
                    parentli = 'CONTACT_li';
                    subparentli = parentli + '_' + catid;
                }
                submenuli = subparentli + '_' + subcatid;

                //TO handle single level membership menu
                if(typeof membershipLevel != 'undefined'){
                    if(membershipLevel == 1 && !isBookmark){
                       parentli = 'CONTACT_li';
                       subparentli = '';
                       submenuli = parentli + '_' + subcatid;
                    }
                }
                
                exportData[filterName][0]['type'] =  'FM' ;
                exportData[filterName][0]['entry'] = 'fed_membership';
                exportData[filterName][0]['condition'] = "is";
                exportData[filterName][0]['input1'] = dataId;
                filterExportCount = _.size(exportData['contact_filter']);
                localStorage.setItem('oldfiltercount-' + clubId + "-" + contactId, filterExportCount);
                filterExportData = JSON.stringify(exportData);
                $("#filterFlag").attr("checked", false);
                jQuery.uniform.update('#filterFlag');
                break;
            case 'MISSING_MEMBERSHIP':
                exportData[filterName][1] = {};
                exportData[filterName][0]['type'] = 'CO';
                exportData[filterName][0]['entry'] = 'fedmembership';
                exportData[filterName][0]['condition'] = "is";
                exportData[filterName][0]['input1'] = "yes";
                exportData[filterName][0]['connector'] = null;

                exportData[filterName][1]['disabled'] = true;
                exportData[filterName][1]['connector'] = 'and';
                exportData[filterName][1]['type'] = 'FROLES-' + handleCountOrSidebarClick.clubData;
                exportData[filterName][1]['entry'] = dataId;
                exportData[filterName][1]['condition'] = "is not";
                exportData[filterName][1]['input1'] = "any";
                exportData[filterName][1]['input2'] = "any";
                filterExportCount = _.size(exportData['contact_filter']);
                localStorage.setItem('oldfiltercount-' + clubId + "-" + contactId, filterExportCount);
                filterExportData = JSON.stringify(exportData);
                $("#filterFlag").attr("checked", false);
                jQuery.uniform.update('#filterFlag');
                break;
                //Single persons// Companies // Members //
            case 'prospect':
            case 'future_sponsor':
            case 'active_sponsor':
            case 'former_sponsor':
                exportData[filterName][0]['type'] = 'CO';
                exportData[filterName][0]['entry'] = 'sponsor';
                exportData[filterName][0]['condition'] = "is";
                exportData[filterName][0]['input1'] = dataId;
                filterExportCount = _.size(exportData['contact_filter']);
                $('.searchbox').val('');
                setTimeout(function () {
                    $('.searchbox').trigger('keyup');
                }, 100);
                localStorage.setItem('oldfiltercount-' + clubId + "-" + contactId, filterExportCount);
                filterExportData = JSON.stringify(exportData);
                $("#filterFlag").attr("checked", false);
                jQuery.uniform.update('#filterFlag');
                break;
            case 'single_person':
            case 'company':
                exportData[filterName][0]['type'] = 'CO';
                exportData[filterName][0]['entry'] = 'contact_type';
                exportData[filterName][0]['condition'] = "is";
                exportData[filterName][0]['input1'] = dataId;
                filterExportCount = _.size(exportData['contact_filter']);
                $('.searchbox').val('');
                setTimeout(function () {
                    $('.searchbox').trigger('keyup');
                }, 100);
                localStorage.setItem('oldfiltercount-' + clubId + "-" + contactId, filterExportCount);
                filterExportData = JSON.stringify(exportData);
                $("#filterFlag").attr("checked", false);
                jQuery.uniform.update('#filterFlag');
                break;
            case 'service':
                /* Show active tab*/
                localStorage.setItem(fgLocalStorageNames.sponsor.active.serviceTab, 'activeservice');
                $("#filterFlag").attr("checked", false);
                $('.searchbox').val('');
                setTimeout(function () {
                    $('.searchbox').trigger('keyup');
                }, 100);

                localStorage.removeItem(filterStorage);
                /* Hide filter */
                $('.filter-alert').hide();
                /* Set list details in local storageb*/
                var serviceDetails = {type: 'service', id: dataId, serviceType: handleCountOrSidebarClick.dataFnType};
                localStorage.setItem(fgLocalStorageNames.sponsor.active.listDetails, JSON.stringify(serviceDetails));
                FgSponsor.sponsorserviceDatatableInit('service', dataId, handleCountOrSidebarClick.dataFnType);
                FgSponsor.moretabInit('data-tabs', 'data-tabs-content');
                break;
            case 'overview':
                /* Show active tab*/
                $('.searchbox').val('');
                localStorage.setItem(fgLocalStorageNames.sponsor.active.serviceTab, dataType);
                $("#filterFlag").attr("checked", false);
                localStorage.removeItem(filterStorage);
                /* Hide filter */
                $('.filter-alert').hide();
                /* Set list details in local storageb*/
                var serviceDetails = {type: 'overview', id: dataId, overviewType: dataType};
                localStorage.setItem(fgLocalStorageNames.sponsor.active.listDetails, JSON.stringify(serviceDetails));
                //fix only for safari browser
                if (safariCount == 0 && $.browser.safari) {
                } else if (!$.isEmptyObject(overviewTable)) {
                    overviewTable.clear();
                    overviewTable.destroy();
                }
                FgSponsor.serviceAssignmentTableInit(dataId);

                //set the action menu for all the categories
                switch (dataId) {
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
                break;
            case 'class':
                filtertype = 'class';
                parentli = 'class_li';
                subparentli = 'class_li_' + catid;
                submenuli = 'class_li_' + catid + '_' + subcatid;
                activeMenuDet.type = 'class';
                activeMenuDet.id = dataId;
                activeMenuDet.categoryId = dataCategory;
                exportData[filterName][0]['type'] = 'class';
                exportData[filterName][0]['entry'] = dataCategory;
                exportData[filterName][0]['condition'] = "is";
                exportData[filterName][0]['input1'] = dataId;
                filterExportCount = _.size(exportData['club_filter']);
                localStorage.setItem('oldClubfiltercount-' + clubId + "-" + contactId, filterExportCount);
                filterExportData = JSON.stringify(exportData);
                $("#filterFlag").attr("checked", false);
                jQuery.uniform.update('#filterFlag');
                break;
            case 'subfed':
                filtertype = 'CO';
                parentli = 'subfed_li';
                submenuli = parentli + '_' + subcatid;
                activeMenuDet.type = 'subfed';
                activeMenuDet.id = dataId;
                exportData[filterName][0]['type'] = 'CO';
                exportData[filterName][0]['entry'] = 'subfed';
                exportData[filterName][0]['condition'] = "is";
                exportData[filterName][0]['input1'] = dataId;
                filterExportCount = _.size(exportData['club_filter']);
                localStorage.setItem('oldClubfiltercount-' + clubId + "-" + contactId, filterExportCount);
                filterExportData = JSON.stringify(exportData);
                $("#filterFlag").attr("checked", false);
                jQuery.uniform.update('#filterFlag');
                break;
            default:
                activeMenuDet = handleCountOrSidebarClick.getActiveMenuDetails(activeMenuDet, dataType, dataId, currentModule, dataCategory, categoryClubId);
                if (fromType === 'count') {
                    var countDetails = handleCountOrSidebarClick.getCountDetailsOfDefaultDataType(dataType, filtertype, parentli, submenuli, subparentli, type, clubId, catid, subcatid, catclubid, exportData, source);
                    filtertype = countDetails.filtertype;
                    parentli = countDetails.parentli;
                    subparentli = countDetails.subparentli;
                    submenuli = countDetails.submenuli;
                    exportData = countDetails.exportData;
                } else {
                    if ((currentModule === 'document') || (currentModule === 'contact')) {
                        var clickDetails = handleCountOrSidebarClick.getClickDetailsOfDefaultDataType(dataType, dataId, dataCategory, exportData, filterName, filterDisplayFlagStorage);
                        filterExportData = clickDetails.filterExportData;
                        dataType = clickDetails.dataType;
                    }
                }
                break;
        }
        var activeMenuDetVar = (source === 'document') ? fgLocalStorageNames[source][type.toLowerCase()]['ActiveMenuDetVar'] : fgLocalStorageNames[source]['active']['ActiveMenuDetVar'];
        localStorage.setItem(activeMenuDetVar, JSON.stringify(activeMenuDet));
        if (fromType === 'sidebar') {
            handleCountOrSidebarClick.sidebarClick(currentModule, dataType, pageTitle, pageCount, filterExportData, filterStorage);
        } else {
            handleCountOrSidebarClick.setLocalStorageForCountClick(dataType, filtertype, catid, subcatid, source, parentli, subparentli, submenuli, clubId, contactId, clubidentifier, sourceType, isBookmark, categoryClubId, dataTypeArr, dataCategory, type, dataId, exportData, filterExportData, pageTarget, filterDisplayFlagStorage);
        }
    },
    sortObjectArray: function (objArray) {
        var keys = _.keys(objArray);
        var sortedKeys = _.sortBy(keys, function (key) {
            return key;
        });

        var sortedObj = {};
        _.each(sortedKeys, function (key) {
            sortedObj[key] = objArray[key];
        });

        return sortedObj;
    },
    /* Setting local storages for clicking datatype - filters */
    setLocalStorageForFilter: function(currentModule, filterDisplayFlagStorage, dataId) {
        var isStacticFIlter = -1;
        if ((currentModule === 'contact') || (currentModule === 'sponsor')) {
            var staticFilters = ['1', '2', '3', '4'];
            isStacticFIlter = staticFilters.indexOf(dataId);
        }
        if (isStacticFIlter !== -1) {
            localStorage.setItem(filterDisplayFlagStorage, 0);
            $("#filterFlag").attr("checked", false);
            jQuery.uniform.update('#filterFlag');
        } else {
            //show the filter area
            $('.filter-alert').show();
            //enable the filter checkbox
            $("#filterFlag").attr("checked", true);
            //store the filterdisplay flag in html5
            localStorage.setItem(filterDisplayFlagStorage, 1);
            //update the property of the checkbox of jquery uniform plugin    
            jQuery.uniform.update('#filterFlag');
        }
    },
    /* Setting local storages for click from contact counts */
    setLocalStorageForCountClick: function(dataType, filtertype, catid, subcatid, source, parentli, subparentli, submenuli, clubId, contactId, clubidentifier, sourceType, isBookmark, categoryClubId, dataTypeArr, dataCategory, type, dataId, exportData, filterExportData, pageTarget, filterDisplayFlagStorage) {
        var typeArray = [ "team", "TEAM", "workgroup", "WORKGROUP", "role", "ROLES", "filterrole", "FILTERROLES", "frole", "FROLES" ];
        if ($.inArray(dataType, typeArray) !== -1) {
            parentli = filtertype + '_li';
            subparentli = parentli + '_' + catid;
            submenuli = subparentli + '_' + subcatid;
            if (source === 'contact') {
                if ((dataType === 'filterrole') || (dataType === 'FILTERROLES')) {
                    localStorage.setItem(fgLocalStorageNames['contact']['active']['functionshowVar'], '');
                } else {
                    localStorage.setItem(fgLocalStorageNames['contact']['active']['functionshowVar'], subcatid + '#' + catid + '#' + filtertype);
                }
            }
        }
        if (isBookmark) {
            parentli = 'bookmark_li';
            subparentli = '';
            submenuli = parentli + '_' + submenuli;
        }
        var contactFilterName = (handleCountOrSidebarClick.filterNameVar === '') ? source + '_filter' : handleCountOrSidebarClick.filterNameVar;
        var returnUrl;
        var filterRoleStorage;
        var oldFilterCountVar;
        var domainUrlIdentifier;
        if (systemEnvironment == 'domain') {
            domainUrlIdentifier = "";
        } else {
            domainUrlIdentifier = "/" + clubidentifier;
        }
        switch (source) {
            case 'contact':
                oldFilterCountVar = 'oldfiltercount-' + clubId + "-" + contactId;
                returnUrl = domainUrlIdentifier + "/backend/contact/list";
                filterRoleStorage = 'filterrole' + clubId + '-' + contactId;
                if (sourceType === 'filterrole') {
                    localStorage.setItem(filterRoleStorage, subcatid);
                } else {
                    localStorage.removeItem(filterRoleStorage);
                }
                break;
            case 'club':
                oldFilterCountVar = 'oldClubfiltercount-' + clubId + "-" + contactId;
                returnUrl = domainUrlIdentifier + "/backend/club";
                break;
            case 'sponsor':
                oldFilterCountVar = 'oldfiltercount-' + clubId + "-" + contactId;
                returnUrl = domainUrlIdentifier + "/backend/sponsor";
                break;
            case 'document':
                oldFilterCountVar = 'oldDocumentfiltercount-' + type + "-" + clubId + "-" + contactId;
                var sidebarDocType = (categoryClubId === clubId) ? 'DOCS-' : 'FDOCS-';
                var sidebarId = (dataType === 'allActive') ? 'allActive' : (dataTypeArr[0] === 'bookmark_li') ? "bookmark_li_" + sidebarDocType + categoryClubId + "_li_" + dataCategory + "_" + dataId : sidebarDocType + categoryClubId + "_" + dataCategory + "_" + dataId;
                returnUrl = domainUrlIdentifier + "/backend/document/" + type.toLowerCase();
                localStorage.setItem(fgLocalStorageNames[source][type.toLowerCase()]['sidebarActiveSubMenu'], sidebarId);
                if (dataTypeArr[0] === 'bookmark_li') {
                    localStorage.setItem(fgLocalStorageNames[source][type.toLowerCase()]['sidebarActiveMenu'], 'bookmark_li');
                } else {
                    localStorage.setItem(fgLocalStorageNames[source][type.toLowerCase()]['sidebarActiveMenu'], sidebarId);
                }
                break;
        }
        if ($.inArray(source, ['contact', 'club', 'sponsor']) !== -1) {
            localStorage.setItem(fgLocalStorageNames[source]['active']['sidebarActiveMenu'], parentli + ',' + subparentli);
            localStorage.setItem(fgLocalStorageNames[source]['active']['sidebarActiveSubMenu'], submenuli);
        }
        if (dataType === 'FILTER' || dataType === 'filter') {
            handleCountOrSidebarClick.setLocalStorageForFilter(source, filterDisplayFlagStorage, subcatid);
            filterExportData = catid.replace(/{connector/g, '{"disabled":true,"connector');
        } else {
            if (dataType !== "ACTIVECONTACT" && dataType !== "ACTIVECLUB" && dataType !== "missingassignment") {
                exportData = {};
                exportData[contactFilterName] = {};
                exportData[contactFilterName][0] = {};
                exportData[contactFilterName][0]['disabled'] = true;
                exportData[contactFilterName][0]['connector'] = null;
                exportData[contactFilterName][0]['type'] = filtertype;
                exportData[contactFilterName][0]['entry'] = catid;
                exportData[contactFilterName][0]['condition'] = "is";
                exportData[contactFilterName][0]['input1'] = subcatid;
                if ((dataType !== 'MEMBERSHIP') && (dataType !== 'CEBF')) {
                    exportData[contactFilterName][0]['input2'] = 'any';
                }
                filterExportData = JSON.stringify(exportData);
            } else if (dataType === "missingassignment") {
                filterExportData = JSON.stringify(exportData);
            }
        }
        var filterStorageName = (source === 'document') ? fgLocalStorageNames[source][type.toLowerCase()]['filterStorage'] : fgLocalStorageNames[source]['active']['filterStorage'];
        if (dataType === 'allActive') {
            localStorage.removeItem(filterStorageName);
        } else {
            localStorage.setItem(filterStorageName, filterExportData);
            var filterDataStorage = $.parseJSON(localStorage.getItem(filterStorageName));
            if (filterDataStorage !== null) {
                localStorage.setItem(oldFilterCountVar, _.size(filterDataStorage[contactFilterName]));
            }
        }
        // If it is clicked on count, it should be redirected to listing page.
        if (pageTarget === "_blank") {
            window.open(returnUrl, pageTarget);
        } else {
            window.location = returnUrl;
        }
    },
    /* Handling click from contact counts */
    getCountDetailsOfDefaultDataType: function(dataType, filtertype, parentli, submenuli, subparentli, type, clubId, catid, subcatid, catclubid, exportData, source) {
        switch (dataType) {
            case 'FI':
                filtertype = 'FI';
                parentli = 'FI_li';
                submenuli = parentli + '_' + subcatid;
                break;
            case 'team':
            case 'TEAM':
                filtertype = 'TEAM';
                break;
            case 'workgroup':
            case 'WORKGROUP':
                filtertype = 'WORKGROUP';
                break;
            case 'role':
            case 'ROLES':
                filtertype = 'ROLES-' + clubId;
                break;
            case 'filterrole':
            case 'FILTERROLES':
                filtertype = 'FILTERROLES-' + clubId;
                break;
            case 'frole':
            case 'FROLES':
                filtertype = 'FROLES-' + catclubid;
                break;
            case 'ACTIVECONTACT':
            case 'ACTIVECLUB':
                parentli = 'bookmark_li';
                submenuli = 'allActive';
                break;
            case 'missingassignment':
                var contactFilterName = 'contact_filter';
                exportData = {};
                exportData[contactFilterName] = {};
                exportData[contactFilterName][0] = {};
                exportData[contactFilterName][1] = {};
                exportData[contactFilterName][0]['disabled'] = true;
                exportData[contactFilterName][0]['connector'] = null;
                exportData[contactFilterName][0]['type'] = 'CO';
                exportData[contactFilterName][0]['entry'] = 'fedmembership';
                exportData[contactFilterName][0]['condition'] = "is";
                exportData[contactFilterName][0]['input1'] = "yes";
                exportData[contactFilterName][1]['disabled'] = true;
                exportData[contactFilterName][1]['connector'] = 'and';
                exportData[contactFilterName][1]['type'] = 'FROLES-' + catclubid;
                exportData[contactFilterName][1]['entry'] = catid;
                exportData[contactFilterName][1]['condition'] = "is not";
                exportData[contactFilterName][1]['input1'] = "any";
                exportData[contactFilterName][1]['input2'] = "any";

                filtertype = 'FROLES-' + catclubid;
                parentli = filtertype + '_li';
                subparentli = parentli + '_' + catid;
                submenuli = 'missing_req_assgmt_' +  catid;
                break;
            default:
                var restype = type.split("_");
                type = restype[1];
                if (restype[1] === 'class') {
                    filtertype = 'class';
                } else if (restype[1] === 'subfed') {
                    filtertype = 'CO';
                } else if (restype[1] === 'filter' || restype[1] === 'FILTER') {
                    type = 'filter';
                } else {
                    if (restype[1] !== 'FILTER') {
                        if (source === 'document') {
                            filtertype = 'DOCS-' + clubId;
                        } else {
                            filtertype = restype[1] + clubId;
                        }
                    }
                }
                submenuli = parentli + '_' + type + '_li' + (parseInt(catid) > 0 ? '_' + catid : '') + '_' + subcatid;
                break;
        }
        var returnObj = {'filtertype': filtertype, 'parentli': parentli, 'subparentli': subparentli, 'submenuli': submenuli, 'exportData': exportData};
        
        return returnObj;
    },
    /* Handle clicking on default data types */
    getClickDetailsOfDefaultDataType: function(dataType, dataId, dataCategory, exportData, filterName, filterDisplayFlagStorage) {
        if ((dataType === 'ROLES') || (dataType === 'FROLES') || (dataType === 'FILTERROLES')) {
            dataType = dataType + '-' + handleCountOrSidebarClick.clubData;
        }
        exportData[filterName][0]['type'] = dataType;
        exportData[filterName][0]['entry'] = dataCategory;
        exportData[filterName][0]['condition'] = "is";
        exportData[filterName][0]['input1'] = dataId;
        if (handleCountOrSidebarClick.dataFnType !== 'none') {
            functionType = dataId + '#' + dataCategory + '#' + dataType;
            localStorage.setItem(functionshowStoragename, functionType);
        }
        if (dataType !== 'FI') {
            exportData[filterName][0]['input2'] = 'any';
        }
        var filterNameVar = handleCountOrSidebarClick.filterNameVar;
        var filterExportCount = _.size(exportData[filterNameVar]);
        localStorage.setItem(handleCountOrSidebarClick.oldFilterCountVar, filterExportCount);
        var filterExportData = JSON.stringify(exportData);
        $("#filterFlag").attr("checked", false);
        jQuery.uniform.update('#filterFlag');
        var returnObj = {'filterExportData': filterExportData, 'dataType': dataType};
        
        return returnObj;
    },
    /* Handling sidebar click */
    sidebarClick: function(currentModule, dataType, pageTitle, pageCount, filterExportData, filterStorage) {
        if (((currentModule === 'document') || (currentModule === 'club')) && (dataType === 'allActive')) {
            $('.page-title-sub').text(FgSidebar.defaultTitle);
        } else {
            $('.page-title-sub').text(pageTitle);
        }
        handleCountOrSidebarClick.totalCount = pageCount;
        filterExportData = filterExportData.trim();
        $('.alert').addClass('display-hide');
        var setLocalStorage = false;
        switch (currentModule) {
            case 'document':
                setLocalStorage = true;
                break;
            case 'contact':
                if (dataType !== 'FILTER') {
                    setLocalStorage = true;
                }
                break;
            case 'sponsor':
                if (dataType !== 'service' && dataType !== 'overview') {
                    setLocalStorage = true;
                }
                break;
            case 'club':
                if (dataType !== 'filter') {
                    setLocalStorage = true;
                }
                break;
        }
        if (setLocalStorage) {
            if ((dataType === 'allActive') || (dataType === 'ACTIVE_CONTACTS')) {
                setTimeout(function () {
                    localStorage.removeItem(filterStorage);
                    filter.data().plugin_searchFilter.redraw();
                    $("#tcount, #fg-slash, .fg_dev_filter_show").hide();
                }, 10);
            } else {
                setTimeout(function () {
                    localStorage.setItem(filterStorage, filterExportData);
                    filter.data().plugin_searchFilter.redraw();
                    $("#tcount, #fg-slash, .fg_dev_filter_show").hide();
                }, 10);
            }
            if (currentModule === 'sponsor') {
                localStorage.setItem(fgLocalStorageNames.sponsor.active.listDetails, JSON.stringify({type: 'sponsor'}));
                FgSponsor.sponsorDatatableInit('sponsor');
            }
        }
        if ((currentModule === 'contact') || (currentModule === 'sponsor')) {
            FgPageTitlebar.setMoreTab();
            FgUtility.stopPageLoading();
        }
    },
    /* Handling sidebar click of different modules */
    handleSidebarClickOfType: function(currentModule, dataType, pageCount) {
        var dataTypeArr = [];
        switch (currentModule) {
            case 'document':
                var dataTypeArr = dataType.split('-');
                var categoryClubId = dataTypeArr[1];
                if (dataTypeArr[0] === 'bookmark_li') {
                    categoryClubId = dataTypeArr[2];
                    dataType = dataType.replace("bookmark_li-", "");
                }
                break;
            case 'contact':
                isFilterBroken = 0;
                functionType = '';
                localStorage.removeItem(filterRoleStorage);
                localStorage.setItem(functionshowStoragename, functionType);
                if (handleCountOrSidebarClick.dataFnType === 'none') {
                    columnName = $.parseJSON(localStorage.getItem(tableColumnTitleStorage));
                    if (_.chain($.parseJSON(localStorage.getItem(tableColumnTitleStorage))).where({'mData': 'Function'})._wrapped.length == 0) {
                        columnName.splice(2, 1);
                        localStorage.setItem(tableColumnTitleStorage, JSON.stringify(columnName));
                    }
                }
                break;
            case 'sponsor':
                isFilterBroken = 0;
                $('.dataTable_checkall').attr('checked', false);
                $.uniform.update('.dataTable_checkall');
                setTimeout(function () {
                    $('.dataTable_checkall').uniform();
                }, 100);

                $(".fgContactdrop .fa").removeClass('fa-users').addClass('fa-bars');
                //remove action bar while clicking on sidebar 
                $(".fg-action-menu").removeClass('open');
                $(".chk_cnt").html('');
                $("#tcount").html(pageCount);
                $('.btn-group').removeClass('open');
                break;
            default:
                break;
        }
        var returnObj = {'categoryClubId': categoryClubId, 'dataType': dataType, 'dataTypeArr': dataTypeArr};

        return returnObj;
    },
    /* Handling click on 'All Active' */
    handleAllActiveClick: function(currentModule, teamId, exportData, filterExportData, filterName, activeMenuDet, fromType, filterDisplayFlagStorage, filterExportCount) {
        if (currentModule === 'document') {
            if (teamId !== '') {
                exportData[filterName][0]['type'] = 'FILE';
                exportData[filterName][0]['entry'] = 'DEPOSITED_WITH';
                exportData[filterName][0]['condition'] = "is";
                exportData[filterName][0]['input1'] = teamId;
            } else {
                exportData[filterName] = {};
            }
            filterExportData = JSON.stringify(exportData);
            $('dl[data-cat-area]').removeClass('hide');
            $('dl[data-cat-area] select').val($('dl[data-cat-area] select option:first').attr('value'));
        }
        activeMenuDet.type = 'allActive';
        $("#filterFlag").attr("checked", false);
        jQuery.uniform.update('#filterFlag');
        if (fromType === 'sidebar') {
            $('.alert').addClass('display-hide');
            $('.filter-alert').hide();
            localStorage.setItem(handleCountOrSidebarClick.oldFilterCountVar, filterExportCount);
            if (currentModule === 'sponsor') {
                $('#sponsorTable').show();
                setTimeout(function () {
                    $('.searchbox').trigger('keyup');
                }, 100);
            }
            if (!$.isEmptyObject(handleCountOrSidebarClick.tableDetails.object)) {
                handleCountOrSidebarClick.tableDetails.object.api().draw();
            } else {
                handleCountOrSidebarClick.tableDetails.name.init();
            }
        }
        var returnObj = {'exportData': exportData, 'filterExportData': filterExportData, 'activeMenuDet': activeMenuDet};
        
        return returnObj;
    },
    /* Handling click on dataType - filter */
    handleFilterClick: function(currentModule, activeMenuDet, dataId, filterDisplayFlagStorage, filterExportData, filterExportCount, filterStorage) {
        var filterUrl = (currentModule === 'club') ? handleCountOrSidebarClick.dataUrl : handleCountOrSidebarClick.filterPath;
        activeMenuDet.type = 'filter';
        activeMenuDet.id = dataId;
        var param = false;
        if ((currentModule === 'contact') || (currentModule === 'sponsor')) {
            param = 'filterId=' + dataId + '&type=filter';
            if (handleCountOrSidebarClick.dataFilter === 'filterrole') {
                param = 'filterId=' + dataId + '&type=filterrole';
            }
        }
        $.post(filterUrl, param, function (data) {
            handleCountOrSidebarClick.setLocalStorageForFilter(currentModule, filterDisplayFlagStorage, dataId);
            if (data.content) {
                filterExportData = data.content;
                filterExportData = filterExportData.replace(/{connector/g, '{"disabled":true,"connector');
                dummy = $.parseJSON(filterExportData);
                filterExportCount = _.size(dummy[handleCountOrSidebarClick.filterNameVar]);
                $('.alert').addClass('display-hide');
                setTimeout(function () {
                    localStorage.setItem(filterStorage, filterExportData);
                    localStorage.setItem(handleCountOrSidebarClick.oldFilterTypeCountVar, filterExportCount);
                    if (currentModule === 'contact') {
                        if (handleCountOrSidebarClick.dataFilter === 'filterrole') {
                            localStorage.setItem(filterRoleStorage, dataId);
                        } else {
                            localStorage.removeItem(filterRoleStorage);
                        }
                    }
                    filter.data().plugin_searchFilter.redraw();
                    if (isFilterBroken && ((currentModule === 'contact') || (currentModule === 'sponsor'))) {
                        FgXmlHttp.post(saveFilterErrorPath, {'id': dataId, broken: 1}, 'replcediv', false);
                    }
                }, 10);
            } else {
                return false;
            }
        });
        var returnObj = {'activeMenuDet': activeMenuDet, 'filterExportData': filterExportData};
        
        return returnObj;
    },
    /* Function to get active menu details */
    getActiveMenuDetails: function(activeMenuDet, dataType, dataId, currentModule, dataCategory, categoryClubId) {
        activeMenuDet.type = dataType;
        activeMenuDet.id = dataId;
        if (currentModule === 'document') {
            $('dl[data-cat-area]').addClass('hide');
            $('dl[data-cat-area] select').val(dataId);
            activeMenuDet.categoryId = dataCategory;
            activeMenuDet.categoryClubId = categoryClubId;
        }

        return activeMenuDet;
    }
};