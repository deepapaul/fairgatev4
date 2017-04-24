/** 
 * ---------------------------------------------------------------------------
 * @Global JS for sidebar loading and handling all other
 * @Ver 1.0
 * @Object call - FgSidebar.init();
 * @Functions as follows
 *      1.  initSettings                     //Initializing sidebar
 *      2.  renderSidebarLevels             //render sidebar levels content 
 *      3.  renderSidebar         
 *      4.  renderTemplate
 *      5.  fetchDataFromLocalStorage
 *      6.  handleOpenedSidebarMenus
 *      7.  handleSidebarClick
 *      8.  handleLocalStorage
 *      
 *      99  RETURN Functions
 *          1.  init
 *          2.  initWithAjaxData
 *          
 *          
 * @Jquery events for handling sidebar items
 *      1.  Click function
 * ---------------------------------------------------------------------------
 */

var FgSidebar = function () {
    var settings;
    var $object;

    /*==================================================================================================
     *@a
     ===================================================================================================*/


    var defaultSettings = {
        module: '',
        jsonData: false,
        parentUl: '#sidemenu_bar',
        sideClickCallback: function (event) { }, //sideClickCallback :function () {console.log(this)},
        sideDroppedCallback: function (event, ui) { }, //sideDroppedCallback:function (ui) {console.log(this,ui)}
        saveNewElementSidebarCallback: function (atrObject) {},
        countCallback: function (data) {}, //ajaxcountupdate callback
        defaultMenuDetails: {menu: '', subMenu: ''},
        data: {},
        defaultLevelSettings: {
            level1: {
                templateId: "sidebarLevel1Template",
                isDroppable: false,
                isClickable: false,
                isHoverable: true,
                isOpenAlways: false,
                //'YES', 'NO', 'AJAX'
                countSettings: {
                    showCount: 'YES',
                    ajaxCountUrl: '#'
                },
                showIcon: false,
                settingsMenu: true,
                settingsTemplateId: "sidebarSettingsTemplate",
                settingsTemplateData: {},
                optionsTemplateId: "sidebarOptionsTemplate"
            },
            level2: {
                templateId: "sidebarLevel2Template",
                navItemCustomClass: '',
                isDroppable: true,
                isClickable: true,
                isHoverable: true,
                isOpenAlways: false,
                isTooltip:false,
                //'YES', 'NO', 'AJAX'
                countSettings: {
                    showCount: 'YES',
                    ajaxCountUrl: '#',
                    countData: {},
                },
                showIcon: false,
                settingsMenu: false,
                showToggleMenu: false,
                settingsTemplateId: '',
            },
            level3: {
                templateId: "sidebarLevel3Template",
                isDroppable: true,
                isClickable: true,
                isHoverable: true,
                isOpenAlways: false,
                //'YES', 'NO', 'AJAX'
                countSettings: {
                    showCount: 'YES',
                    ajaxCountUrl: '#'
                },
                showIcon: false,
                showToggleMenu: false,
            },
            level4: {
                templateId: "sidebarLevel4Template",
                isDroppable: true,
                isClickable: true,
                isHoverable: true,
                isOpenAlways: false,
                //'YES', 'NO', 'AJAX'
                countSettings: {
                    showCount: 'YES',
                    ajaxCountUrl: '#'
                },
                showIcon: false,
            },
            optionMenu : true
        },
        Bookmark: {
            level1: {
                templateId: "firstLevelBookmarkTemplate"
            },
            level2: {
                templateId: "secondLevelBookmarkTemplate"
            }

        },
        initCompleteCallback: function ($object) {
            handleActiveMenuContentLoad();
        },
//        sidebarClickCallback: function ($object) {},
//        addMenuCallback: function ($object) {},
//        itemDroppableCallback : function ($object) {},
    };

    /*==================================================================================================
     *@Extends the initial configuration on method init	
     ===================================================================================================*/

    var initSettings = function (options, data) {
        settings = $.extend(true, {}, defaultSettings, options, data);
    }

    /*==================================================================================================
     *@Method to render different levels of a sidebar
     ===================================================================================================*/

    var renderSidebarLevels = function (levelSettings, levelData, levelDetails) {
        var htmldata = '';
        var parentIds = levelDetails.parentIds;
        _.each(levelData, function (fieldValue, fieldKey) {
            var overrideSettings = (levelDetails.level == 1) ? settings[fieldKey] : settings[fieldValue.menuType];
            var subLevelData = (levelDetails.level == 1) ? fieldValue.entry : fieldValue.input;
            var parentId = (levelDetails.level == 1) ? fieldKey : fieldValue.id;
            parentIds.push(parentId);
            var subLevelSettings = $.extend(true, {}, levelSettings, overrideSettings);
            var subLevel = {level: levelDetails.level + 1, parentIds: parentIds};
            var levelObject = {ids: parentIds, content: renderSidebarLevels(subLevelSettings, subLevelData, subLevel)};
            setSidebarMenuProperties(levelObject, subLevelSettings['level' + levelDetails.level], fieldValue);
            levelObject.settings = getSidebarSettingsMenuContent(subLevelSettings['level' + levelDetails.level]);
            parentIds.pop();
            htmldata += renderTemplate(subLevelSettings['level' + levelDetails.level].templateId, {levelObject: levelObject, levelData: fieldValue});
        });

        return htmldata;
    }

    /*==================================================================================================
     *@Method to render different sidebar options
     ===================================================================================================*/

    var renderSidebarOptions = function (levelSettings, levelData) {
        var htmldata = '';
        if(settings.defaultLevelSettings.optionMenu){
            htmldata = renderTemplate(levelSettings['level1'].optionsTemplateId, {levelData: levelData, inactiveOptions: settings.inactiveSidebarOptions});
        }
        
        return htmldata;
    }

    /*==================================================================================================
     *@Method to set the various javascript events for sidebar menu
     ===================================================================================================*/

    var setSidebarMenuProperties = function (levelObject, levelSettings, levelData) {
        levelObject.hoverable = isHoverable(levelSettings);
        levelObject.droppable = isDroppable(levelSettings);
        levelObject.clickable = isClickable(levelSettings, levelData);
        levelObject.open = isOpened(levelSettings, levelData);
        levelObject.active = isActive(levelSettings, levelData);
        levelObject.showIcon = levelSettings.showIcon;
        levelObject.openedSidebarMenus = settings.openedSidebarMenus;
        levelObject.activeMenu = settings.activeMenu;
        levelObject.showtoggle = isToggled(levelSettings);
        levelObject.showCount = (levelSettings.countSettings.showCount != 'NO') ? true : false;
        if (levelObject.showCount) {
            levelObject.count = (levelSettings.countSettings.showCount == 'YES') ? (!_.isEmpty(levelData.count) ? levelData.count : 0) : '';
        }
        levelObject.navItemCustomClass = (!_.isUndefined(levelSettings.navItemCustomClass)) ? levelSettings.navItemCustomClass : '';
        levelObject.isTooltip = (!_.isUndefined(levelSettings.isTooltip)) ? levelSettings.isTooltip : '';
    }

    /*==================================================================================================
     *@Method to get sidebar settings menu content 
     ===================================================================================================*/

    var getSidebarSettingsMenuContent = function (levelSettings) {
        var settingsTemplate = '';
        if (levelSettings.settingsMenu) {
            settingsTemplate = ((!_.isEmpty(levelSettings.settingsTemplateData)) ? renderTemplate(levelSettings.settingsTemplateId, levelSettings.settingsTemplateData) : '');
        }

        return settingsTemplate;
    }

    /*==================================================================================================
     *@Method to get sidbar menu count 
     ===================================================================================================*/

    var renderSidebarCount = function () {
        var callCount = $.getJSON(settings.defaultLevelSettings.level1.countSettings.ajaxCountUrl, function (data) {
            var countData = data;
            if (countData) {
                FgCountUpdate.update('show', false, 'active', countData, 1);
            }
            if ($('#sidemenu_bar').find('.fg-sidebar-loading')) {
                $('#sidemenu_bar').find('.fg-sidebar-loading').addClass('badge badge-round badge-important no-value fg-badge-blue').removeClass('fg-sidebar-loading fa-spin');
                $('#sidemenu_bar').find('.no-value').text('0');
            }
        });
        //callback after ajaxupdate
        callCount.done(function (data) {
            settings.countCallback.call(data)
        });
    }

    /*==================================================================================================
     *@Method to check whether the sidebar menu is clickable 
     ===================================================================================================*/

    var isClickable = function (levelSettings, levelData) {
        return levelSettings.isClickable ? true : false;//checkWhetherMenuIsClickable(levelData);
    }

    /*==================================================================================================
     *@Method to check whether the sidebar menu is opened or not
     ===================================================================================================*/

    var isOpened = function (levelSettings) {
        return ((levelSettings.isOpenAlways || levelSettings.isOpened) ? true : false);
    }

    /*==================================================================================================
     *@Method to check whether items can be dropped to the sidebar menu 
     ===================================================================================================*/

    var isDroppable = function (levelSettings) {
        return levelSettings.isDroppable;
    }

    /*==================================================================================================
     *@Method to check whether the sidebar menu will be hoverable if it does not have any sub-menus
     ===================================================================================================*/

    var isHoverable = function (levelSettings) {
        return levelSettings.isHoverable ? true : false;
    }

    /*==================================================================================================
     *@Method to check whether the sidebar menu is active or not
     ===================================================================================================*/

    var isActive = function (levelSettings) {
        return (levelSettings.isActive ? levelSettings.isActive : false);
    }


    /*==================================================================================================
     *@Method to check whether items can be toggled
     ===================================================================================================*/

    var isToggled = function (levelSettings) {
        return levelSettings.showToggleMenu;
    }

    /*==================================================================================================
     *@Method to render sidebar
     ===================================================================================================*/

    var renderSidebar = function () {
        $(settings.parentUl).html("");
        handleOpenedSidebarMenus();
        var sidebarMenuContent = renderSidebarLevels(settings.defaultLevelSettings, settings.data, {level: 1, parentIds: []});
        var sidebarOptions = renderSidebarOptions(settings.defaultLevelSettings, settings.data);
        var sidebarContent = sidebarMenuContent + sidebarOptions;
        $(settings.parentUl).html(sidebarContent);
    }

    /*==================================================================================================
     *@Method to bind data to templates
     ===================================================================================================*/

    var renderTemplate = function (templateId, templateData) {
        return FGTemplate.bind(templateId, templateData);
    }

    /*==================================================================================================
     *@Method to get active menu and opened menu details from local storage if exist and else set default
     ===================================================================================================*/

    var handleOpenedSidebarMenus = function () {
        var clubLocalStorage = localStorage.getItem("ClubGlobalConfig_" + settings.clubId);
        if (clubLocalStorage !== null) {
            var sidebarData = JSON.parse(clubLocalStorage);
            var module = settings.module.toUpperCase();
            var openedSidebarMenuData = _.isEmpty(sidebarData['sidebar'][module]) ? {"Active": "", "Opened": [], "Options": []} : sidebarData['sidebar'][module];
            settings.openedSidebarMenus = openedSidebarMenuData.Opened;
            settings.inactiveSidebarOptions = _.isEmpty(openedSidebarMenuData.Options) ? [] : openedSidebarMenuData.Options;
            if (!_.isEmpty(openedSidebarMenuData.Active)) {
                settings.activeMenu = openedSidebarMenuData.Active;
            } else {
                settings.activeMenu = settings.defaultMenuDetails.subMenu;
                settings.openedSidebarMenus.push(settings.defaultMenuDetails.menu);
                settings.openedSidebarMenus.push(settings.defaultMenuDetails.subMenu);
            }
        } else {
            settings.activeMenu = settings.defaultMenuDetails.subMenu;
            settings.openedSidebarMenus = [settings.defaultMenuDetails.menu, settings.defaultMenuDetails.subMenu];
            settings.inactiveSidebarOptions = [];
        }
    };

    /*==================================================================================================
     *@Method to dynamically set the active sidebar menu
     ===================================================================================================*/

    var handleActiveMenu = function (activeMenu) {
        if (!_.isUndefined(activeMenu)) {
            settings.activeMenu = activeMenu;
        }
    }

    /*==================================================================================================
     *@Handle sidebar click function
     *@it will trigger when user click on sidebar link
     ===================================================================================================*/

    var handleSidebarClick = function (parentId) {
        Metronic.startPageLoading();
        updatePageTitle($('#' + parentId + ' > .nav-link'));
        var sidebarData = {};
        var currentData = {};
        var openPrevious = '';
        if ($('.fg-page-sidebar .nav-item .active').hasClass('open')) {
            openPrevious = $('.fg-page-sidebar .nav-item .active').attr("id");
        }
        $('.fg-page-sidebar .nav-item').removeClass('active');
        $('#' + parentId).parents('.sub-menu').show();
        $('#' + parentId).addClass('active');
        if (openPrevious) {
            $('#' + openPrevious).children('.sub-menu').show();
        }

        $('#' + parentId).parents('li').children('a').children('.arrow').addClass('open');
        $('#' + parentId + '.open').children('.arrow').addClass('open');
        $('#' + parentId + '.open>ul').show();

        /*---------------------------------------------------------
         * Get all opened items' class
         ---------------------------------------------------------*/

        var totOpenedItems = $('.fg-page-sidebar').find('.nav-item.open');
        var openedItemsArray = [];

        $(totOpenedItems).each(function (e) {
            openedItemsArray.push($(this).attr('id'))
        })

        /*---------------------------------------------------------
         * Checking the localstorage already exist or not
         ---------------------------------------------------------*/
        if (localStorage.getItem("ClubGlobalConfig_" + settings.clubId) !== null) {
            currentData = JSON.parse(localStorage.getItem("ClubGlobalConfig_" + settings.clubId));
        }
        sidebarData[settings.module] = {"Active": parentId, "Opened": openedItemsArray, "Options": []};
        var updatedSidebarData = $.extend(true, currentData['sidebar'], sidebarData);
        localStorage.setItem("ClubGlobalConfig_" + settings.clubId, JSON.stringify({'sidebar': updatedSidebarData}));


        /*---------------------------------------------------------
         * Callback for sidebar click
         ---------------------------------------------------------*/
        FgSidebar.setActiveMenu(parentId);
        settings.sideClickCallback.call(parentId);
        Metronic.stopPageLoading();
    };

    /*==================================================================================================
     *@Handle sidebar draopable function
     *@it will trigger when user start trigger
     ===================================================================================================*/
    var handleSidebardropable = function () {
        $('.fg-page-sidebar .nav-item .nav-link:not(.non-dropable)').droppable({
            activate: function (event, ui) {
                ui.draggable.css("cursor", "copy");
                $('body').addClass("fg-sidebar-drag-active");
            },
            over: function (event, ui) {
                $(this).parent().addClass("fg-sidebar-hover");
                ui.draggable.css("cursor", "copy");
            },
            out: function (event, ui) {
                $(this).parent().removeClass("fg-sidebar-hover");
            },
            drop: function (event, ui) {
                $(this).parent().removeClass("fg-sidebar-hover");
                settings.sideDroppedCallback.call(event, ui);
            },
            deactivate: function (event, ui) {
                $('body').removeClass("fg-dev-drag-active fg-sidebar-drag-active");
            }
        });
    };

    /*==================================================================================================
     *@Method to load the content of the active sidebar menu
     ===================================================================================================*/

    var handleActiveMenuContentLoad = function () {
        $('.fg-page-sidebar #' + settings.activeMenu + ' > .nav-link:not(.non-clickable)').trigger('click');
    }

    /*==================================================================================================
     *@Handle sidebar open items on load
     *@it will trigger when load a page
     ===================================================================================================*/

    var handleSidebarOpenedItems = function () {
        $.each($('.fg-page-sidebar .nav-item.open'), function () {
            $(this).children('.sub-menu').show();
        });
    }

    /*==================================================================================================
     *@Handle sidebar open items on load
     *@it will trigger when load a page
     ===================================================================================================*/

    var handleSidebarOptions = function () {       
        if(settings.defaultLevelSettings.optionMenu){
            _.each(settings.inactiveSidebarOptions, function (id, index) {
                $('#' + id + '_checker').parent('span').removeClass('checker');
                $("#" + id).hide();
            });
        }
        
    }
    /*==================================================================================================
     *@Handle sidebar open items on load
     *@it will trigger when load a page
     ===================================================================================================*/

    var updatePageTitle = function (clickedItem) {
        //show title bar when it is hided
        $('.fg-action-menu-wrapper').removeClass('hide');
        switch (settings.module) {
            case 'CMS':
                var menuType = clickedItem.attr('data-type');
                var pageTitle = clickedItem.find('.title').text();
                if (menuType == 'MM') {
                    if (!_.isEmpty(clickedItem.attr('data-pagetitle'))) {
                        pageTitle = clickedItem.attr('data-pagetitle');
                    } else {
                        pageTitle = '';
                        //hide title bar when no title
                        $('.fg-action-menu-wrapper').addClass('hide');
                    }
                }
                $('.page-title > .page-title-text').text(pageTitle);
                break;
            default:
                $('.page-title > .page-title-text').text(clickedItem.find('.title').text());
                break;
        }
    }

    /*==================================================================================================
     *@Method to rebuild the sidebar with new data and active menu if passed or else with default settings
     ===================================================================================================*/
    var rebuildSidebar = function (data, activeMenu) {
        if (!_.isEmpty(data)) {
            settings.data = data;
        }
        $(settings.parentUl).html("");
        handleOpenedSidebarMenus();
        handleSidebarOptions();
        handleActiveMenu(activeMenu);
        var sidebarContent = renderSidebarLevels(settings.defaultLevelSettings, settings.data, {level: 1, parentIds: []});
        $(settings.parentUl).html(sidebarContent);
        if (settings.defaultLevelSettings.level1.countSettings.showCount == 'AJAX') {
            renderSidebarCount();
        }
    }

    /*==================================================================================================
     *@Method to set the default menu if the active menu is not present in DOM
     ===================================================================================================*/

    var setDefaultMenu = function () {
        if ($('#' + settings.activeMenu).length == 0) {
            settings.activeMenu = settings.defaultMenuDetails.subMenu;
            handleActiveMenuContentLoad();
        }
    }

    /*==================================================================================================
     *@Method to save the sidebar options in local storage 
     ===================================================================================================*/

    var saveSidebarOptions = function (options) {
        var sidebarData = {};
        if (localStorage.getItem("ClubGlobalConfig_" + settings.clubId) !== null) {
            var localStorageData = JSON.parse(localStorage.getItem("ClubGlobalConfig_" + settings.clubId));
            if (localStorageData.hasOwnProperty('sidebar')) {
                sidebarData = localStorageData;
            }
        }
        if (sidebarData.hasOwnProperty(settings.module)) {
            $.extend(sidebarData["sidebar"][settings.module], {"Options": options});
        } else {
            sidebarData["sidebar"][settings.module]["Options"] = options;
        }
        localStorage.setItem("ClubGlobalConfig_" + settings.clubId, JSON.stringify({'sidebar': sidebarData["sidebar"]}));
    }

    return {
        /*==================================================================================================
         *@initialize sidebar with already build json
         ===================================================================================================*/

        init: function (options, data) {
            Metronic.startPageLoading;
            initSettings(options, {data: data});
            renderSidebar();
            renderSidebarCount();
            handleSidebarOptions();
            handleSidebardropable(); //initialise dropable item
            handleSidebarOpenedItems();
            setDefaultMenu();//if active menu not in dom 
            settings.initCompleteCallback.call($object);
            FgTooltip.init();
            Metronic.stopPageLoading;
        },
        /*==================================================================================================
         *@initialize sidebar with url to get json via ajax
         ===================================================================================================*/

        initWithAjaxData: function (options, url) {
            //fetch data from url
            var jsonData = {};
            initSettings(options, {data: jsonData});
            renderSidebar();
        },
        /*==================================================================================================
         *@Sidebar handling click function call
         *@Sidebar show opened items
         ===================================================================================================*/

        handleSidebarClick: function (parentId) {
            handleSidebarClick(parentId);
        },
        saveToLocalStorage: function (parentId, active, openclass) {

            var sidebarData = {};
            var currentData = {};
            var totOpenedItems = $('.fg-page-sidebar').find('.nav-item.open');
            var openedItemsArray = [];

            $(totOpenedItems).each(function (e) {
                openedItemsArray.push($(this).attr('id'));
            })
            if (localStorage.getItem("ClubGlobalConfig_" + settings.clubId) !== null) {

                currentData = JSON.parse(localStorage.getItem("ClubGlobalConfig_" + settings.clubId));
                var sidebarDataJson = currentData['sidebar'];
                if (sidebarDataJson.hasOwnProperty(settings.module)) {

                    currentData['sidebar'][settings.module]['Opened'] = openedItemsArray;
                }
            }
            var activeli = FgSidebar.getActiveMenu();
            sidebarData[settings.module] = {"Active": activeli, "Opened": openedItemsArray, "Options": []};
            var updatedSidebarData = $.extend(true, currentData['sidebar'], sidebarData);
            localStorage.setItem("ClubGlobalConfig_" + settings.clubId, JSON.stringify({'sidebar': updatedSidebarData}));


        },
        /*==================================================================================================
         *@Call this function to get the current active sidebar menu id
         ===================================================================================================*/

        getActiveMenu: function () {
            return settings.activeMenu;
        },
        /*==================================================================================================
         *@Method to open already opened sidebar menu items saved in local storage
         ===================================================================================================*/

        handleSidebarOpenedItems: function () {
            handleSidebarOpenedItems();
        },
        /*==================================================================================================
         *@invoke method to reload the sidebar with updated data
         ===================================================================================================*/

        reloadSidebar: function (data) {
            settings.data = data;
            renderSidebar();
        },
        /*==================================================================================================
         *@method to change the sidebar active menu
         ===================================================================================================*/

        setActiveMenu: function (defaultMenu) {
            var activeMenu = (!_.isUndefined(defaultMenu)) ? defaultMenu : settings.defaultMenuDetails.subMenu;
            settings.activeMenu = activeMenu;
        },
        /*==================================================================================================
         *@initialize sidebar with url to get json via ajax
         ===================================================================================================*/

        rebuildSidebar: function (data, activeMenu) {
            Metronic.startPageLoading;
            rebuildSidebar(data, activeMenu);
            settings.initCompleteCallback.call($object);
            handleSidebarOpenedItems();
            handleSidebardropable(); //initialise dropable item
            setDefaultMenu();
            FgTooltip.init();
            Metronic.stopPageLoading;
        },
        /*==================================================================================================
         *@method to save the newly added elemnts
         ===================================================================================================*/
        saveNewElementSidebar: function (atrObject) {
            settings.saveNewElementSidebarCallback.call(atrObject);
        },
        /*==================================================================================================
         *@method to save the sidebar opions
         ===================================================================================================*/

        saveSidebarOptions: function (options) {
            saveSidebarOptions(options);
        },
        /*==================================================================================================
         *@method to check sidebar opions position
         ===================================================================================================*/
        positionCheck: function (element, target) {
            $(element).removeClass('dropup dropdown');
            if ($(target).is(":hidden"))
                return false;

            var elementHeight = $(target).outerHeight(),
                    elementPos = $(element + ' ' + target).offset().top,
                    renderPos = elementHeight + elementPos;
            if (elementPos <= (elementHeight + elementHeight * 0.8)) {
                $(element).addClass('dropdown');
            } else {
                $(element).addClass('dropup');
            }
        }
    };
}();


/*==================================================================================================
 *@Click function for sidebar handling
 ===================================================================================================*/

$('.fg-page-sidebar').on('click', '.nav-link:not(.non-clickable)', function (event) {
    var parentId = $(this).parent().attr('id');
    FgSidebar.handleSidebarClick(parentId);
});


$('.fg-page-sidebar').on('click', '.nav-link.non-clickable', function (event) {
    var parentId = $(this).parent().attr('id');
    var activechild = $('#' + parentId).find('ul li.active').length;
    var classOpen = $('#' + parentId).hasClass('open');
    FgSidebar.saveToLocalStorage(parentId, activechild, classOpen);



});

/*==================================================================================================
 *@Click function for sidebar options handling
 ===================================================================================================*/

$('.fg-page-sidebar').on('click', '.fg-dev-sidebar-options', function () {
    var menuGroupDivId = $(this).val();
    if ($(this).is(':checked')) {
        $("#" + menuGroupDivId).show();
    } else {
        $("#" + menuGroupDivId).hide();
    }
    FgSidebar.positionCheck('.fg-sidebar-options', '.dropdown-checkboxes');
});

$(".fg-page-sidebar").on("hidden.bs.dropdown", '.fg-sidebar-options', function () {
    var sidebarOptions = [];
    $.each($('.fg-page-sidebar .fg-dev-sidebar-options:not(:checked)'), function () {
        sidebarOptions.push($(this).attr('value'));
    });
    FgSidebar.saveSidebarOptions(sidebarOptions);
    FgSidebar.positionCheck('.fg-sidebar-options', '.dropdown-checkboxes');
});

$(".fg-page-sidebar").on("show.bs.dropdown", '.fg-sidebar-options', function () {
    FgSidebar.positionCheck('.fg-sidebar-options', '.dropdown-checkboxes');
});

$('.fg-page-sidebar').on('click', '.create_new_element', function () {
    var dataHtml = FGTemplate.bind("sidebarLevel2NewElementTemplate", newElementData);
    if ($(this).closest('div.fg-sidebar-menu-button').siblings('ul.sub-menu').find(".new-blk-input").length == 0)
    {
        $(this).closest('div.fg-sidebar-menu-button').siblings('ul.sub-menu').append(dataHtml);
    }
    $(this).closest('div.fg-sidebar-menu-button').siblings('ul.sub-menu').slideDown();
    $(this).closest('div.fg-sidebar-menu-button').siblings('ul.sub-menu').find(".new-blk-input").focus();
    $(this).closest('div.fg-sidebar-menu-button').parent('li.nav-item').find('.arrow').addClass('open');
});

$(document).on('keypress', '.sidebar-create', function (event) {
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') { // Enter key press
        $(this).parent().find('i.fg-new-save').trigger("click");
    } else if (keycode == '27') { // Esc key press
        $(this).parent().find('i.fg-new-close').trigger("click");
    }
});

$(document).on('click', '.add-new-blk .fg-new-close', function () {
    $(this).parents(".fg-new-element-li").remove();
});

$(document).on('click', '.add-new-blk .fg-new-save', function () {
    if ($(this).siblings(".new-blk-input").val().trim().length > 0) {
        FgSidebar.saveNewElementSidebar($(this).siblings(".new-blk-input"));
        $(this).parents(".fg-new-element-li").remove();
    }
});

$('.fg-page-sidebar').on('click', '.nav-link:not(.non-clickable) > .arrow', function (event) {
    event.stopImmediatePropagation();
    FgSidebar.saveToLocalStorage('', '', '');


});
