/**
 * For contactlist twig.
 */

FgContactListCustom = {
    callFilterFlag: function () {
        $("#filterFlag").on("click", function () {
            oldFilterCount = localStorage.getItem('oldfiltercount-' + clubId + "-" + contactId);
            newFilterCount = _.size(filterdata['contact_filter']);
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
        })

    },
    callFilter: function () {

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
            selectTitle: CLTrans.CM_SELECT_TYPE,
            criteria: '<div class="col-md-1"><span class="fg-criterion">' + CLTrans.CM_CRITERIA + ':</span></div>',
            savedCallback: function () {
                setTimeout(function () {
                    $("#callPopupFunction").click();
                }, 1);
            },
            onComplete: function (data) {
                if (localStorage.getItem(filterDisplayFlagStorage) == 0) {
                    $('.filter-alert').hide();
                }
                if (data != 0) {

                    if (data == 1) {
                        filterdata = 'contact';
                        localStorage.setItem('oldfiltercount-' + clubId + "-" + contactId, 0);
                        $("#tcount").hide();
                        $("#fg-slash").hide();
                    } else {
                        filterdata = data;
                        oldFilterCount = localStorage.getItem('oldfiltercount-' + clubId + "-" + contactId);
                        newFilterCount = _.size(filterdata['contact_filter']);
                        if (oldFilterCount == null && newFilterCount >= 1) {
                            $("#tcount").hide();
                            $("#fg-slash").hide();
                        } else if (newFilterCount == oldFilterCount) {
                            $(".fa-filter").hide();
                            $("#tcount").hide();
                            $("#fg-slash").hide();
                        }
                        else if (newFilterCount != oldFilterCount) {
                            $(".fa-filter").show();
                            $("#tcount").show();
                            $("#fg-slash").show();
                        } else {
                            $("#tcount").hide();
                            $("#fg-slash").hide();
                        }
                    }
                    if (!$.isEmptyObject(oTable)) {
                        oTable.api().draw();
                    } else {
                        FgTable.init();
                    }
                    if ($("#searchbox").val() != '') {
                        $("#tcount").show();
                        $("#fg-slash").show();
                    }
                    $('.alert').addClass('display-hide');
                    if (localStorage.getItem(functionshowStoragename) != null && localStorage.getItem(functionshowStoragename) != '') {
                        oTable.api().column(2).visible(true);
                    } else {
                        if (contactType == 'contact') {
                            oTable.api().column(2).visible(false);
                        } else {
                            oTable.api().column(2).visible(true);
                        }
                    }
                } else {
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
                    if (!$.isEmptyObject(oTable)) {
                        oTable.api().clear();
                        oTable.api().draw();
                    } else {
                        FgTable.init();
                    }
                    $('.alert').removeClass('display-hide');
                }
                if (FgSidebar.isFirstTime && contactType == 'contact') {
                    callSidebar();
                    FgContactListCustom.setSidebarCount();
                    if(isReadOnlyContact == 0) { // If logged in contact is readonly, no need to show missing assignment alert in side bar
                        FgContactListCustom.getMissingAssignment();
                    }
                    FgSidebar.isFirstTime = false;
                }
                FgTooltip.init();

            },
        });
    },
    sidebarHighlight: function (clubId, contactId) {

        FgSidebar.highlightSidebarWarning(clubId, contactId, urlIdentifier)
    },
    setSidebarCount: function () {
        $.getJSON(CLParams.contactSidebarCountUrl, function (data) {
            var countData = data;
            FgCountUpdate.update('show', 'contact', 'active', countData, 2);
        });
    },
    getMissingAssignment: function () {
        $('#MissingReqAssgmtError').addClass('display-hide');
        $.getJSON(CLParams.sidebarMissingAssignmentsUrl, function (data) {
            var missingAssignData = data;
            if (Object.keys(missingAssignData).length > 0) {
                FgCountUpdate.updateMissingAssignments(missingAssignData);
                reqRoleMissing = true;
            }
            FgSidebar.show('');
        });
    }


}
/*-------------- Function for  keep Data list Configuration --------*/
$('body').on('change', '#fgrowchange select', function () {
//store row count
    var tableVal = $(".dataTables_length .cl-bs-select").select2('val');
    tableVal = parseInt(tableVal);
    localStorage.setItem('tableRowCount-' + contactType + '-' + contactId + '-' + clubId, tableVal);
});
/*-------------- Page title bar configuration --------*/
FgPageTitlebar.init({
    actionMenu: actionmenuFlag,
    title: true,
    counter: true,
    searchFilter: true,
    search: true,
    colSetting: true

});
// Document ready starts
jQuery(document).ready(function () {
//disable inline edit on page refresh
    if ($("#inlineEditContact").is(':checked')) {
        $("#inlineEditContact").attr("checked", false);
        jQuery.uniform.update('#inlineEditContact');
        sessionStorage.setItem('inlineEditContactFlag', 0);
    }
//bind animation event to filter button
    FgSidebar.filterAnimationInit("#search");
    /* Function to hide export menu if no contacts available */
    $('.fgContactdrop').on('click', function () {

        var cntcount = $("#fcount").text();
        if (cntcount == '0') {
            $(".dropdown-menu").find(".fg-dev-exportmenu").addClass("hide");
        } else {
            $(".dropdown-menu").find(".fg-dev-exportmenu").removeClass("hide");
        }
    });
    $(".fa-filter").hide();
    $.getJSON(CLParams.filterContactDataUrl, function (data) {
        jsonData = data;
        var tblSettingValue = localStorage.getItem(tableSettingValueStorage);
        var tblSettingId = localStorage.getItem(tableSettingIdStorage);
        if (tblSettingValue === null || tblSettingValue === '' || tblSettingValue == 'undefined') {
            tblSettingValue = $("#fg-dev-defaultcolumnsetting").val();
            $("#tableColumns").select2('val', '');
        } else {
            $("#tableColumns").select2('val', tblSettingId);
        }
        tableSettingValues = $.parseJSON(tblSettingValue);
        tableSettingValues = FgContactList.deletecheck(tableSettingValues, jsonData, teamId, workgroupId);
        settingValue = tableSettingValues;
        localStorage.setItem(tableSettingValueStorage, JSON.stringify(tableSettingValues));
        tableColumnTitles = FgTableColumnHeading.getColumnNames(settingValue, teamId, workgroupId, general_table_title_array, false);
        //console.log(settingValue);
        //console.log(tableColumnTitles);
        //tableColumnTitles = getTableColumns(settingValue);
        localStorage.setItem(tableColumnTitleStorage, JSON.stringify(tableColumnTitles));
        //}

        FgContactListCustom.callFilter();
        FgContactListCustom.callFilterFlag();
    });
    var filterflag = localStorage.getItem(filterDisplayFlagStorage);
    if (filterflag != 1) {
        localStorage.setItem(filterDisplayFlagStorage, 0);
    } else if (filterflag == 1) {
        $("#filterFlag").attr("checked", true);
        jQuery.uniform.update('#filterFlag');
    }

    if (filterflag == 0) {
        $('.filter-alert').hide();
        //enable the filter checkbox
        $("#filterFlag").attr("checked", false);
        jQuery.uniform.update('#filterFlag');
    }

    if (contactType == 'contact') {
        $('#filternameText').editable({
            type: 'text',
            url: CLParams.updateFilterDataUrl,
            pk: clubId,
            emptytext: '',
            display: false,
            placement: 'bottom',
            inputclass: 'form-control input-sm',
            validate: function (value) {
                if ($.trim(value) == '') {
                    return CLTrans.VALIDATION_THIS_FIELD_REQUIRED;
                }
            },
            params: function (params) {
                var stringifyed_data = localStorage.getItem(filterStorage);
                stringifyed_data = stringifyed_data.replace(/,"disabled":true/g, '');
                //stringifyed_data = stringifyed_data.replace(',"disabled\":true', '');
                params.jString = stringifyed_data; //JSON.stringify(data);
                return params;
            },
            success: function (data) {
                $('#filternameText').html('');
                if (data.operation == 'INSERT') {
                    parentMenuId = 'filter_li';
                    var menuHtml = FgSidebar.generateHtml(FgSidebar.templateLevel2, {'subMenu': data});
                    var parentMenu = $('#' + parentMenuId + ' ul.sub-menu');
                    $('#' + parentMenuId).addClass('open');
                    $(menuHtml).appendTo(parentMenu);
                    FgSidebar.handleArrows(parentMenu, '');
                }
                FgUtility.showToastr(CLTrans.CM_CONTACT_FILTER_SAVE_SUCCESS);
            }
        });
    }

    $('#callPopupFunction').click(function (event) {
        event.stopPropagation();
        event.preventDefault();
        $('#filternameText').editable('toggle');
        $('#filternameText').editable('setValue', null);
    });
    //bind the click event to the tableColumn select box
    $('#tableColumns').on('click', function () {

        /*-------------- keep Data list Configuration --------*/
        var tableVal = $('#select2-chosen-3').text();
        tableVal = parseInt(tableVal);
        localStorage.setItem('tableRowCount-' + contactType + '-' + contactId + '-' + clubId, tableVal);
        tableSettingValues = $.parseJSON($("#tableColumns option:selected").attr("data-attributes"));
        localStorage.setItem(tableSettingIdStorage, $("#tableColumns option:selected").val());
        // tableSettingValue = removeDeletedFields(tableSettingValues);
        tableSettingValue = FgContactList.deletecheck(tableSettingValues, jsonData, teamId, workgroupId);
        settingValue = tableSettingValue;
        localStorage.setItem(tableSettingValueStorage, JSON.stringify(tableSettingValue));
        tableColumnTitles = FgTableColumnHeading.getColumnNames(settingValue, teamId, workgroupId, general_table_title_array, false);
        localStorage.setItem(tableColumnTitleStorage, JSON.stringify(tableColumnTitles));
        setTimeout(function () {
            if (contactType == 'formerfederationmember') {
                window.location = CLParams.formerFederationMemberIndexPath;
            } else if (contactType == 'contact') {
                window.location = CLParams.contactIndexPath;
            } else {
                window.location = CLParams.archiveIndexPath;
            }


        }, 100);
    });
    //bind the click event to filter close button
    $('.fg_filter_hide').on('click', function () {

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
});
// document ready ends

function checkExecutiveMembersMissing() {
    if (reqExecBoardFunError == '1') {
        $('#reqExecMembersError').removeClass('display-hide');
    } else {
        $('#reqExecMembersError').addClass('display-hide');
    }
}

function requiredFedRoleMissingAssignment() {
    if (reqRoleMissing && (isReadOnlyContact == 0)) { //if logged-in contact is readonly contact, missing assignmnet alert should not be shown
        $('#MissingReqAssgmtError').removeClass('display-hide');
    } else {
        $('#MissingReqAssgmtError').addClass('display-hide');
    }
}

/**#function to check whether filter criteria is broken#**/
function checkForBrokenFilterCriteria() {
    var cnt = jsonData.filter.entry.length;
    for (var i = 0; i < cnt; i++) {
        var filterJson = $.parseJSON(jsonData.filter.entry[i].filterData);
        var filterId = jsonData.filter.entry[i].id;
        var isBroken = false;
        isBroken = FgValidateFilter.init(jsonData, filterJson, 'contact');
        if (!isBroken) {
            $('a[filter_Id="' + filterId + '"]').empty().append('<i class="fa fa-warning fg-warning fg-broken-filter" data-toggle="tooltip"></i>');
        }
    }
}

// Function : To call Sidebar
function callSidebar() {
    var filterBookmark = {};
    var defaultTitle;
    var type = CLParams.type;
    var allActiveMenu = CLParams.allActiveMenu;
    filterBookmark = allActiveMenu.concat(jsonData['bookmark']['entry']);
    filterSavedFilter = jsonData['filter']['entry'];
    /* sidebar settings */
    FgSidebar.jsonData = true;
    FgSidebar.ActiveMenuDetVar = ActiveMenuDetVar;
    FgSidebar.activeMenuVar = fgLocalStorageNames.contact.active.sidebarActiveMenu;
    FgSidebar.activeSubMenuVar = fgLocalStorageNames.contact.active.sidebarActiveSubMenu;
    FgSidebar.activeOptionsVar = 'activeContactOptions' + clubId + '-' + contactId;
    FgSidebar.defaultMenu = 'bookmark_li';
    FgSidebar.defaultSubMenu = 'allActive';
    FgSidebar.bookemarkUpdateUrl = CLParams.bookemarkUpdateUrl;
    FgSidebar.filterCountUrl = CLParams.filterCountUrl;
    FgSidebar.filterDataUrl = CLParams.filterDataUrl;
    FgSidebar.list = 'contact';
    FgSidebar.options = [];
    FgSidebar.newElementLevel1 = CLParams.newElementLevel1;
    FgSidebar.newElementLevel2 = CLParams.newElementLevel2;
    FgSidebar.newElementLevel2Sub = CLParams.newElementLevel2Sub;
    FgSidebar.defaultTitle = defaultTitle;
    FgSidebar.newElementUrl = CLParams.newElementUrl;
    FgSidebar.module = CLParams.module;
    FgSidebar.showloading = true;
    sidebarClickObj = {
        currentModule: FgSidebar.module,
        clubId: clubId,
        contactId: CLParams.contactId,
        tableDetails: {'object': oTable, 'name': FgTable},
        oldFilterCountVar: 'oldfiltercount-' + clubId + "-" + CLParams.contactId,
        filterPath: saveFilterPath,
        filterNameVar: 'contact_filter',
        oldFilterTypeCountVar: 'oldfiltercount-' + clubId + "-" + CLParams.contactId
    };
    $.extend( handleCountOrSidebarClick, sidebarClickObj );
    FgSidebar.settings = {};
    /* sidebar bookmark settings */
    var bookmarkTitle = CLTrans.SIDEBAR_BOOKMARKS;
    var bookmarkId = 'bookmark_li';
    var iterationCount = 1;
    var bookmarksMenu = {templateType: 'general', menuType: 'bookmark', 'parent': {id: bookmarkId, class: 'tooltips bookmark-link', name: 'bookmark-link', 'data-placement': "right"}, title: bookmarkTitle, template: '#template_sidebar_menu', 'settings': {"0": {'title': CLTrans.SIDEBAR_SORTING, 'url': CLParams.bookmarkListPath}}, 'menu': {'items': filterBookmark}};
    FgSidebar.settings[bookmarkId] = bookmarksMenu;
    /* sidebar contact settings */
    var contactDataTitle = CLTrans.SIDEBAR_MEMBERSHIPS;
    var contactId = 'CONTACT_li';
    var contactData = (typeof jsonData['CN'] !== "undefined" && typeof jsonData['CN']['entry'] !== "undefined") ? jsonData['CN']['entry'] : {};
    //contactData[0]['hasSettings'] = 0;
    //contactData[1]['hasSettings'] = 0;
    var contentType = 'membership';
    if(type == 'federation'){
       contentType = 'fed_membership';
    }
    if(type == 'sub_federation'){
        contactData[0]['hasSettings'] = 0;
    }
    if(type == 'federation_club' || type == 'sub_federation_club'){
        if(clubMembershipAvailable == 1)
            contactData[1]['hasSettings'] = 0;
        else 
            contactData[0]['hasSettings'] = 0;
    }
    
    
    var createTitle = (type=='federation') ? CLTrans.SIDEBAR_CREATE_FEDMEMBERSHIP : CLTrans.SIDEBAR_CREATE_MEMBERSHIP;
    var manageTitle = (type=='federation') ? CLTrans.SIDEBAR_FEDMEMBERSHIP_SETTINGS : CLTrans.SIDEBAR_MEMBERSHIP_SETTINGS;
    var settings = {"0": {'type': 'newElement', 'title': createTitle, 'url': '#', 'contentType': contentType, 'hierarchy': '1'}, "1": {'title': manageTitle, 'url': CLParams.membershipListPath}};
    
    if(_.size(contactData) == 1){
        var contactMenu = {templateType: 'general', menuType: 'membership', 'parent': {id: contactId, class: contactId}, title: contactData[0].title, template: '#template_sidebar_menu', 'menu': {'items': contactData[0].input}};
        settings[0]['target'] = '#CONTACT_li';
        settings[0]['categoryId'] = (type=='federation') ? 'fed_membership' : 'membership';
        settings[0]['hierarchy'] = 2;
        if(contactData[0]['hasSettings'] != 0){
            contactMenu.settings = settings;
        }
    } else {
        var contactMenu = {templateType: 'menu2level', menuType: 'membership', 'parent': {id: contactId, class: contactId}, title: contactDataTitle, template: '#template_sidebar_menu2level', 'menu': {'items': contactData}};
        contactMenu.settingsLevel2 = settings;
    }
    
    FgSidebar.settings[contactId] = contactMenu;
    FgSidebar.options.push({'id': contactId, 'title': contactDataTitle});
    $.each(jsonData, function (key, data) {
        var keySplit = key.split('-');
        var actualKey = keySplit[0];
        switch (actualKey) {
            case "WORKGROUP":
                var workGroupTitle = data.title;
                var workGroupId = key + '_li_' + CLParams.clubWorkgroupId;
                var workgroupData = (typeof jsonData['WORKGROUP'] !== "undefined" && typeof jsonData['WORKGROUP']['entry'] !== "undefined") ? jsonData['WORKGROUP']['entry'][0]['input'] : {};
                workgroupData = _.without(workgroupData, _.findWhere(workgroupData, {id: 'any'}));
                var workGroupMenu = {templateType: 'general', menuType: 'WORKGROUP', 'parent': {id: workGroupId, class: workGroupId}, title: workGroupTitle, template: '#template_sidebar_menu', 'settings': {"0": {'type': 'newElement', 'title': CLTrans.SIDEBAR_CREATE_WORKGROUP, 'url': '#', 'contentType': 'workgroup', 'subContentType': 'workgroup', 'target': '#' + workGroupId, 'hierarchy': '2', 'functionAssign': 'individual', 'placeHolder': CLTrans.SIDEBAR_ADD_WORKGROUP, 'placeHolderFunction': CLTrans.SIDEBAR_ADD_FUNCTION}, "1": {'title': CLTrans.SIDEBAR_WORKGROUP_SETTINGS, 'url': CLParams.workgroupSettingsPath}}, 'menu': {'items': workgroupData}};
                FgSidebar.settings[workGroupId] = workGroupMenu;
                FgSidebar.options.push({'id': workGroupId, 'title': workGroupTitle});
                break;
            case "TEAM":
                var teamTitle = data.title;
                var teamId = key + '_li';
                var teamData = (typeof jsonData['TEAM'] !== "undefined" && typeof jsonData['TEAM']['entry'] !== "undefined") ? jsonData['TEAM']['entry'] : {};
                teamData = _.map(teamData, function(team){ team.input = _.without(team.input, _.findWhere(team.input, {id: 'any'})); return team; });
                var level1Settings = {"0": {'type': 'newElement', 'title': CLTrans.SIDEBAR_CREATE_TEAM_CATEGORRY, 'url': '#', 'contentType': 'teamcategory', 'target': '#' + teamId, 'hierarchy': '1', 'placeHolder': CLTrans.SIDEBAR_ADD_TEAM_CATEGORRY}, "1": {'title': CLTrans.SIDEBAR_CATEGORY_SORTING, 'url': CLParams.editRoleCategoryTeamPath}};
                var level2Settings = {"0": {'type': 'newElement', 'title': CLTrans.SIDEBAR_CREATE_TEAM, 'url': '#', 'contentType': 'team', 'hierarchy': '1', 'functionAssign': 'same', 'placeHolder': CLTrans.SIDEBAR_ADD_TEAM, 'placeHolderFunction': CLTrans.SIDEBAR_ADD_FUNCTION}, "1": {'title': CLTrans.SIDEBAR_TEAM_SETTINGS, 'url': CLParams.teamCategorySettingsPath}};
                var teamMenu = {templateType: 'menu2level', menuType: 'TEAM', 'parent': {id: teamId, class: teamId}, title: teamTitle, template: '#template_sidebar_menu2level', 'menu': {'items': teamData}};
                teamMenu.settingsLevel1 = level1Settings;
                teamMenu.settingsLevel2 = level2Settings;
                FgSidebar.settings[teamId] = teamMenu;
                FgSidebar.options.push({'id': teamId, 'title': teamTitle});
                break;
            case "ROLES":
                var roleTitle = data.title;
                var roleId = key + '_li';
                var teamData = (typeof jsonData[key] !== "undefined" && typeof jsonData[key]['entry'] !== "undefined") ? jsonData[key]['entry'] : {};
                teamData = _.map(teamData, function(team){ team.input = _.without(team.input, _.findWhere(team.input, {id: 'any'})); return team; });
                var level1Settings = {"0": {'type': 'newElement', 'title': CLTrans.SIDEBAR_CREATE_ROLE_CATEGORY, 'url': '#', 'contentType': 'rolecategory', 'subContentType': 'role', 'target': '#' + roleId, 'hierarchy': '1', 'placeHolder': CLTrans.SIDEBAR_ADD_ROLE_CATEGORRY}, "1": {'title': CLTrans.SIDEBAR_CATEGORY_SORTING, 'url': CLParams.editRoleCategoryClubPath}};
                var level2Settings = {"0": {'type': 'newElement', 'title': CLTrans.SIDEBAR_CREATE_ROLE, 'url': '#', 'contentType': 'role', 'target': '#' + data.id, 'hierarchy': '2', 'placeHolder': CLTrans.SIDEBAR_ADD_ROLE, 'placeHolderFunction': CLTrans.SIDEBAR_ADD_FUNCTION}, "1": {'title': CLTrans.SIDEBAR_ROLE_CATEGORY_SETTINGS, 'url': CLParams.roleCategorySettingsPath}};
                var roleMenu = {templateType: 'menu2level', menuType: 'ROLE', 'parent': {id: roleId, class: roleId, 'data-auto': actualKey + '_li'}, title: roleTitle, template: '#template_sidebar_menu2level', 'menu': {'items': teamData}};
                roleMenu.settingsLevel1 = level1Settings;
                roleMenu.settingsLevel2 = level2Settings;
                FgSidebar.settings[roleId] = roleMenu;
                FgSidebar.options.push({'id': roleId, 'title': roleTitle});
                break;
            case "FROLES":
                var froleTitle = data.title;
                var froleId = key + '_li';
                var teamData = (typeof jsonData[key] !== "undefined" && typeof jsonData[key]['entry'] !== "undefined") ? jsonData[key]['entry'] : {};
                teamData = _.map(teamData, function(team){ team.input = _.without(team.input, _.findWhere(team.input, {id: 'any'})); return team; });
                var froleMenu = {templateType: 'menu2level', menuType: 'FROLE', 'parent': {id: froleId, class: froleId, 'data-auto': actualKey + '_li'}, title: froleTitle, template: '#template_sidebar_menu2level', logo: data.logo, 'menu': {'items': teamData}};
                if (clubId == keySplit[1]) {
                    var level1Settings = {"0": {'type': 'newElement', 'title': CLTrans.SIDEBAR_CREATE_ROLE_CATEGORY, 'url': '#', 'contentType': 'fedrolecategory', 'subContentType': 'fedrole', 'target': '#' + froleId, 'hierarchy': '1', 'placeHolder': CLTrans.SIDEBAR_ADD_ROLE_CATEGORRY}, "1": {'title': CLTrans.SIDEBAR_CATEGORY_SORTING, 'url': CLParams.editRoleCategoryFedPath}};
                    var level2Settings = {"0": {'type': 'newElement', 'title': CLTrans.SIDEBAR_CREATE_ROLE, 'url': '#', 'contentType': 'role', 'subContentType': 'fedrole', 'target': '#' + data.id, 'hierarchy': '2', 'placeHolder': CLTrans.SIDEBAR_ADD_ROLE, 'placeHolderFunction': CLTrans.SIDEBAR_ADD_FUNCTION}, "1": {'title': CLTrans.SIDEBAR_ROLE_CATEGORY_SETTINGS, 'url': CLParams.roleCategorySettingsPath}};
                    froleMenu.settingsLevel1 = level1Settings;
                    froleMenu.settingsLevel2 = level2Settings;
                }
                FgSidebar.settings[froleId] = froleMenu;
                FgSidebar.options.push({'id': froleId, 'title': froleTitle});
                iterationCount++;
                break;
            case "FILTERROLES":
                var firoleTitle = data.title;
                var firoleId = key + '_li';
                var teamData = (typeof jsonData[key] !== "undefined" && typeof jsonData[key]['entry'] !== "undefined") ? jsonData[key]['entry'] : {};
                teamData = _.map(teamData, function(team){ team.input = _.without(team.input, _.findWhere(team.input, {id: 'any'})); return team; });
                var level1Settings = {"0": {'type': 'newElement', 'title': CLTrans.SIDEBAR_CREATE_FILTER_ROLE_CATEGORY, 'url': '#', 'contentType': 'rolecategory', 'subContentType': 'filterrole', 'target': '#' + firoleId, 'hierarchy': '1', 'placeHolder': CLTrans.SIDEBAR_ADD_FILTERROLE_CATEGORRY}, "1": {'title': CLTrans.SIDEBAR_CATEGORY_SORTING, 'url': CLParams.editRoleCategoryFilterPath}};
                var level2Settings = {"0": {'title': CLTrans.SIDEBAR_FILTER_ROLE_CATEGORY_SETTINGS, 'url': CLParams.filterRoleSettingsPath}};
                var firoleMenu = {templateType: 'menu2level', menuType: 'FILTERROLES', 'parent': {id: firoleId, class: firoleId, 'data-auto': actualKey + '_li'}, title: firoleTitle, template: '#template_sidebar_menu2level', 'menu': {'items': teamData}};
                firoleMenu.settingsLevel1 = level1Settings;
                firoleMenu.settingsLevel2 = level2Settings;
                FgSidebar.settings[firoleId] = firoleMenu;
                FgSidebar.options.push({'id': firoleId, 'title': firoleTitle});
                break;
            case "FI":
                var executeBoardTitle = CLParams.executeBoardTitle;
                var executeBoardId = key + '_li';
                var executeBoardData = (typeof jsonData[key] !== "undefined" && typeof jsonData[key]['entry'] !== "undefined") ? jsonData[key]['entry'][1]['input'] : {};
                StringifyexecuteBoardData = JSON.stringify(executeBoardData);
                executeBoardData = $.parseJSON(StringifyexecuteBoardData);
                if (_.size(executeBoardData) > 0) {
                    delete executeBoardData[0];
                    delete executeBoardData[1];
                }
                var executeBoardMenu = {templateType: 'general', menuType: 'executiveboard', 'parent': {id: executeBoardId, class: executeBoardId}, title: executeBoardTitle, template: '#template_sidebar_menu', 'settings': '', 'menu': {'items': executeBoardData}};
                //menu seetting is only for federation level executive board
                if (type == 'federation') {
                    executeBoardMenu.settings = {"0": {'type': 'newElement', 'title': CLTrans.SIDEBAR_EXECUTIVE_FUNCTION, 'url': '#', 'contentType': 'executiveFunction', 'target': '#' + executeBoardId, 'hierarchy': '1', 'placeHolder': CLTrans.SIDEBAR_ADD_FUNCTION}, "1": {'title': CLTrans.SIDEBAR_EXECUTIVEBOARD_SETTINGS, 'url': CLParams.execboardfunctionSettingsPath}}
                }
                FgSidebar.settings[executeBoardId] = executeBoardMenu;
                FgSidebar.options.push({'id': executeBoardId, 'title': executeBoardTitle});
                break;
        }


    });
    /* sidebar saved filter settings */
    var filterTitle = CLTrans.SIDEBAR_FILTER;
    filterMenuId = 'filter_li';
    var filterMenu = {templateType: 'general', menuType: 'filter', 'parent': {id: filterMenuId}, title: filterTitle, template: '#template_sidebar_menu', 'settings': {"0": {'title': CLTrans.SIDEBAR_SAVEDFILTER_SETTINGS, 'url': CLParams.savedFilterSettingsUrl}}, 'menu': {'items': filterSavedFilter}};
    FgSidebar.settings[filterMenuId] = filterMenu;
    //var sortedSettingObject = handleCountOrSidebarClick.sortObjectArray(FgSidebar.settings);
    //FgSidebar.settings=sortedSettingObject;
    FgSidebar.init();
    //For handling the pre-opening of the sponsor menu
    FgSidebar.handlePreOpening('open', FgSidebar.module);
    checkForBrokenFilterCriteria();
}