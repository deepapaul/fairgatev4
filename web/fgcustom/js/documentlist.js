jQuery(function() {
    var rand = Math.random();
    $(".fa-filter").hide();
    $('#filterFlag').attr('checked', false);
    $.uniform.update('#filterFlag');
    $.getJSON(jsonDataPath + '?rand=' + rand, function(data) {
        jsonData = data;
        var tblSettingValue = localStorage.getItem(tableSettingValueStorage);
        if (tblSettingValue === null || tblSettingValue === '' || tblSettingValue == 'undefined') {
            tblSettingValue = defaultColumnSetting;
        } else {
            tblSettingValue = $.parseJSON(tblSettingValue);
        }
        localStorage.setItem(tableSettingValueStorage, JSON.stringify(tblSettingValue));
        tableColumnTitles = FgDocumentTableColumnHeading.getColumnNames(tblSettingValue);
        localStorage.setItem(tableColumnTitleStorage, JSON.stringify(tableColumnTitles));
        FgDocumentTable.init();
        callFilter();
        callFilterFlag(filterDisplayFlagStorage);
    });

    var callFilter = function() {
        //for remove the bookmark entry from the json array
        var allJsonString = JSON.stringify(jsonData);
        var filterJson = $.parseJSON(allJsonString);
        delete filterJson.bookmark;

        FgUtility.startPageLoading();
        filter = $("#target").searchFilter({
            jsonGlobalVar: filterJson,
            submit: '#search',
            save: '#saveFilter',
            filterName: filterName,
            storageName: filterStorage,
            addBtn: '#addCriteria',
            clearBtn: '.remove-filter',
            dateFormat: FgApp.dateFormat,
            customSelect: true,
            selectTitle: 'Select type',
            conditions: filterCondition,
            selectTitle: selectText,
                    criteria: '<div class="col-md-1"><span class="fg-criterion">' + selectCriteria + ':</span></div>',
            savedCallback: function() {
                setTimeout(function() {
                    $("#callPopupFunction").click();
                }, 1);
            },
            onComplete: function(data) {

                if (localStorage.getItem(filterDisplayFlagStorage) == 0) {
                    $('.filter-alert').hide();
                }

                if (data != 0) {
                    $('.alert-danger').hide();
                    if (data == 1) {
                        filterdata = 'all';
                        $("#tcount").hide();
                        $("#fg-slash").hide();
                    } else {

                        filterdata = data;

                        oldFilterCount = localStorage.getItem('oldDocumentfiltercount-' + type + "-" + clubId + "-" + contactId);
                        oldFilterCount = (oldFilterCount != null) ? oldFilterCount : 0;
                        newFilterCount = _.size(filterdata['document_filter']);
                        if (newFilterCount != oldFilterCount) {
                            $(".fa-filter").show();
                            $("#tcount").show();
                            $("#fg-slash").show();
                        } else {
                            $("#tcount").hide();
                            $("#fg-slash").hide();
                        }
                    }
                    filterCallback();
                    if (!$.isEmptyObject(documentTable)) {
                        documentTable.api().draw();

                    } else {
                        FgDocumentTable.init();
                    }
                    if ($("#searchbox").val() != '') {
                        $("#tcount").show();
                        $("#fg-slash").show();
                    }
                    $('.alert').addClass('display-hide');
                    if ($('.filter-alert:visible').length == 0) {
                        $("#filterFlag").attr("checked", false);
                    } else {
                        $("#filterFlag").attr("checked", true);
                    }
                    jQuery.uniform.update('#filterFlag');

                } else {
                    isFilterBroken = 1;
                    filterdata = 0;
                    $('.alert-danger').show();
                    //enable the filter checkbox
                    $("#filterFlag").attr("checked", true);
                    //store the filterdisplay flag in html5
                    localStorage.setItem(filterDisplayFlagStorage, 1);
                    //update the property of the checkbox of jquery uniform plugin
                    jQuery.uniform.update('#filterFlag');
                    filterCallback();
                }

            },
        });
    }
    var filterCallback = function() {

        if (FgSidebar.isFirstTime) {
            callSidebar();
            FgSidebar.isFirstTime = false;
        }
    }
    var callSidebar = function() {
        /* sidebar settings */
        FgSidebar.jsonData = true;
        FgSidebar.ActiveMenuDetVar = ActiveMenuDetVar;
        FgSidebar.activeMenuVar = 'activeMenu-' + type + '-' + clubId + '-' + contactId;
        FgSidebar.activeSubMenuVar = 'activeSubMenu-' + type + '-' + clubId + '-' + contactId;
        FgSidebar.activeOptionsVar = 'activeOptions' + type + '-' + clubId + '-' + contactId;
        FgSidebar.defaultMenu = 'bookmark_li';
        FgSidebar.defaultSubMenu = 'allActive';
        FgSidebar.bookemarkUpdateUrl = bookmarkUpdateUrl;
        FgSidebar.filterCountUrl = filterCountUrl;
        FgSidebar.filterDataUrl = filterDataUrl;
        FgSidebar.list = 'club';
        FgSidebar.options = [];
        FgSidebar.newElementLevel1 = newElementLevel1;
        FgSidebar.newElementLevel2 = newElementLevel2;
        FgSidebar.newElementLevel2Sub = newElementLevel2Sub;
        FgSidebar.defaultTitle = defaultTitle;
        FgSidebar.newElementUrl = newElementUrl;
        FgSidebar.module = module;
        FgSidebar.settings = {};
        FgSidebar.extraParams = {docType: docType};
        sidebarClickObj = {
            currentModule: FgSidebar.module,
            tableDetails: {'object': documentTable, 'name': FgDocumentTable},
            oldFilterCountVar: 'oldDocumentfiltercount-' + type + "-" + clubId + "-" + contactId,
            filterPath: '',
            filterNameVar: 'document_filter',
            oldFilterTypeCountVar: ''
        };
        $.extend( handleCountOrSidebarClick, sidebarClickObj );

        /* sidebar bookmark settings */
        var bookmarkId = 'bookmark_li';
        var filterBookmark = {};
        var allActiveMenu = [{'isAllActive': 1, 'title': defaultTitle, count: allDocsCount}];
        filterBookmark = allActiveMenu.concat(jsonData['bookmark']['entry']);
        var bookmarksMenu = {templateType: 'general', menuType: 'bookmark', 'parent': {id: bookmarkId, class: 'tooltips bookmark-link', name: 'bookmark-link', 'data-placement': "right"}, title: bookmarkTitle, template: '#template_sidebar_menu', 'settings': {"0": {'title': sortingTitle, 'url': bookMarkSortingPath}}, 'menu': {'items': filterBookmark}};
        FgSidebar.settings[bookmarkId] = bookmarksMenu;

        /* sidebar category settings */
        var loopJsonData = JSON.parse(JSON.stringify(jsonData));
        delete(loopJsonData['FILE']);
        delete(loopJsonData['DATE']);
        delete(loopJsonData['USER']);
        delete(loopJsonData['bookmark']);

        $.each(loopJsonData, function(key, data) {
            var docCatTitle = data['title'];
            var docCatData = data['entry'];
            var docCatId = data['id'];
            var level1Settings = {"0": {'type': 'newElement', 'title': createCatTitle, 'url': '#', 'contentType': 'category', 'target': '#' + docCatId, 'hierarchy': '1', 'placeHolder': addCatTitle}, "1": {'title': manageCatTitle, 'url': manageCategoryPath}};
            var level2Settings = {"0": {'type': 'newElement', 'title': createSubcatTitle, 'url': '#', 'contentType': 'subcategory', 'hierarchy': '1', 'placeHolder': addSubcatTitle}, "1": {'title': manageSubcatTitle, 'url': manageSubcategoryPath}};
            var docCatMenu = {templateType: 'menu2level', menuType: key, 'parent': {id: docCatId, class: docCatId}, title: docCatTitle, template: '#template_sidebar_menu2level', 'menu': {'items': docCatData}};
            var docTypeArr = key.split('-');
            if (docTypeArr[0] == 'DOCS') {
                docCatMenu.settingsLevel1 = level1Settings;
                docCatMenu.settingsLevel2 = level2Settings;
            } else {
                docCatMenu.logo = data['logo'];
            }
            FgSidebar.settings[docCatId] = docCatMenu;
            FgSidebar.options.push({'id': docCatId, 'title': docCatTitle});
        });
        FgSidebar.init();
        FgUtility.stopPageLoading();
        //For handling the pre-opening of the sponsor menu
        FgSidebar.handlePreOpening('open',module);
    }

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



});
function callFilterFlag(filtername) {
    $("#filterFlag").on("click", function() {

        if ($(this).is(':checked')) {
            $('.filter-alert').show();
            $('#filterFlag').attr('checked', true);
            localStorage.setItem(filtername, 1);
        } else {
            $('.filter-alert').hide();
            $('#filterFlag').attr('checked', false);
            localStorage.setItem(filtername, 0);
        }
        $.uniform.update('#filterFlag');
    })
//    if (localStorage.getItem(filtername) == 1) {
//        $('#filterFlag').attr('checked', true);
//        //update the property of the checkbox of jquery uniform plugin
//        $.uniform.update('#filterFlag');
//    }
}

//for calling popup if no subcategory exists
var callCreateCategoryPopup = function (requestPath, type) {
    $('#popup_contents').html('');
    params = {"type": type};
    $.post(requestPath, params, function (data) {
        if (typeof data === 'string') {  // if typeof data === 'object', subcategories exist
            // no subcategories are existing
            actionMenuText.active.none.upload.isActive = false;
            FgSidebar.dynamicMenus.push({actionmenu: actionMenuText});
            var dataType = $('.fgContactdrop').attr('data-type');
            var actionMenuType = $('.fgContactdrop').attr('data-menu-type');
            FgSidebar.processDynamicMenuDisplay($('.fgContactdrop'), dataType, actionMenuType);
            $('#popup_contents').html(data);
            $('#popup').modal('show');
        }
    });

};

