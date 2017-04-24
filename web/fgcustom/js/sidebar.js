/* * GLOBAL JAVASCRIPT FILE FOR SIDEBAR LOADING * */
var totalCount = '';
window.sidebarTopnavLoaded = 0;
FgSidebar = {
    /* 
     * For loading details of sidebar menu on local storage HTML5  
     */
    dynamicMenus: [],
    jsonData: false,
    settings: [],
    templateTop: '#template_sidebar_top',
    templateSettings: '#template_sidebar_settings',
    templateOptions: '#template_sidebar_options',
    templateLevel1: '#template_sidebar_menu2level',
    templateLevel2: '#template_sidebar_menu2level_level2',
    parentUl: '#sidemenu_bar',
    bookemarkUpdateUrl: '',
    activeMenuVar: false,
    activeSubMenuVar: false,
    activeOptionsVar: false,
    list: false,
    filterCountUrl: '',
    filterDataUrl: '',
    isFirstTime: true,
    newElementLevel1: '',
    newElementLevel2: '',
    newElementLevel2Sub: '',
    newElementUrl: '',
    assignmentJsonArr: '',
    defaultTitle: '',
    module: 'contact',
    newMenuOpts: [],
    showloading: false,
    extraParams: {},
    init: function (new_element_template) {
        this.loadJsonSidebar();
        FgFormTools.handleUniform();
        this.handleSidebarMenu();  // override default metronix page sidebar slideToggle
        FgSidebar.bookmarkSectionDislay();
        FgSidebar.bookmarkClick();

        $('#sidemenu_bar').on('click', '.clsid', function () {
            if (!$("#" + $(this).attr('id')).is(':checked'))
            {
                var chkval = $(this).val();
                $("." + chkval).hide();
            } else {
                var chkval1 = $(this).val();
                $("." + chkval1).show();
            }
            var arr = new Array();
            localStorage.removeItem(FgSidebar.activeOptionsVar);

            $("#sidemenu_bar input[type=checkbox]").each(function () {
                if (!$(this).is(':checked')) {
                    arr.push($(this).attr('value'));
                    $("#" + chkval).hide();
                }
            });
            localStorage.setItem(FgSidebar.activeOptionsVar, arr);
            Layout.fixContentHeight(); //force content area  to resize height after sidebar render
            FgSidebar.positionCheck('.custom-drop', '.hidden-settings');
        });

        // custom menu popup init
        $('.custom-drop').live('click', function () {
            $(this).find('.hidden-settings').toggle();
            FgSidebar.positionCheck('.custom-drop', '.hidden-settings');
        });

        //Sidebar click hadling from various areas (by excluding filter and bookmark icon click handling)
        $('#sidemenu_bar').on('click', '.subclass > a.sidebabar-link', function () {

            var arr = new Array();
            localStorage.setItem('oldmenu-' + clubId + "-" + contactId, localStorage.getItem('submenu'));
            localStorage.removeItem(FgSidebar.activeMenuVar);
            var actid = $(this).parents('li.open').map(function () {
                return  $(this).attr('id');
            }).get();
            localStorage.setItem(FgSidebar.activeMenuVar, actid);
            if ($(this).attr('data-id')) {
                localStorage.removeItem(FgSidebar.activeSubMenuVar);
                $('.subclass').removeClass("active");
                $(this).parents().eq(0).addClass("active");
                localStorage.setItem(FgSidebar.activeSubMenuVar, $(this).parents().eq(0).attr('id'));
            } else if ($(this).attr('data-type') == 'allActive') {
                localStorage.removeItem(FgSidebar.activeSubMenuVar);
                $('.subclass').removeClass("active");
                $(this).parents().eq(0).addClass("active");
                localStorage.setItem(FgSidebar.activeSubMenuVar, $(this).parents().eq(0).attr('id'));
            }
        });

        FgSidebar.handleFilterCount();
        FgSidebar.handlePreOpening('init');
    },
    //Default settings of sidebar
    setDefault: function () {
        var getstr = localStorage.getItem(FgSidebar.activeMenuVar);
        var activeSubMenu = '#' + localStorage.getItem(FgSidebar.activeSubMenuVar);
        if (getstr == null || ($(activeSubMenu).length == 0 && activeSubMenu.indexOf("missing_req_assgmt") === -1)) {
            $('.filter-alert').hide();
            /* IF the local storage not cotains anything, then set it to 'Active contact'*/
            localStorage.setItem(FgSidebar.activeMenuVar, FgSidebar.defaultMenu);
            localStorage.setItem(FgSidebar.activeSubMenuVar, FgSidebar.defaultSubMenu);
            $('#allActive a').trigger('click');
        }
    },
    //Checkbox status of sidebar options
    checkBoxStatus: function () {
        var checkElement = localStorage.getItem(FgSidebar.activeOptionsVar);
        $('.clsid').prop('checked', true);
        jQuery.uniform.update('.clsid');

        if (checkElement == null) {
            return false;
        }
        var disabledElem = checkElement.split(",");
        $.each(disabledElem, function (i) {
            $('#' + disabledElem[i]).hide();
            $('input[value="' + disabledElem[i] + '"]').prop('checked', false);
            jQuery.uniform.update('input[value="' + disabledElem[i] + '"]');
        })
    },
    //Show sidebar
    show: function (ur) {
        var getstr = localStorage.getItem(FgSidebar.activeMenuVar);
        if (getstr != null) {
            var targetBlock = '.sub-menu',
                    activeMenu = '#' + localStorage.getItem(FgSidebar.activeSubMenuVar);
            closestMenu = $(activeMenu).closest(targetBlock);
            $(activeMenu).addClass("active");
            closestMenu.closest('li').addClass('open active');
            closestMenu.closest('li').closest(targetBlock).closest('li').addClass('open active');
            $('.page-sidebar-menu .open.active > a:first-child .arrow').addClass('open');

            $(activeMenu).closest('.sub-menu').css({'display': 'block'});
            var submenuId = localStorage.getItem(FgSidebar.activeSubMenuVar);
            if (submenuId == 'allActive') {
                $('.page-title-sub').text(FgSidebar.defaultTitle);
            } else {
                $('.page-title-sub').text($('#' + submenuId + ' .sidebabar-link .title').text());
            }
            FgPageTitlebar.setMoreTab();
            //Update count of selected sidebar menu in listing page
            var activeMenuCnt = ($(activeMenu).find(".badge").length > 0) ? $(activeMenu).find(".badge").text() : $("#fcount").text();
            $("#tcount").html(activeMenuCnt);
            $("#fcount").html(activeMenuCnt);
        }
        this.checkBoxStatus();
        window.sidebarTopnavLoaded = window.sidebarTopnavLoaded + 1;

    },
    //Missing assignment handling in sidebar
    highlightSidebarWarning: function (clubId, contactId, urlIdentifier) {
        /*req fed  role missing handle click*/
        var _selector = $('li ul > li a i.missingWarning:first').parents().eq(2);
        var _Catselector = _selector.find('ul li:first a').attr('data-id');
        var _Clubselector = _selector.find('ul li:first a').attr('data-club');
        handleCountOrSidebarClick.updateFilter('missingassignment', 'filterdisplayflag_contact' + clubId + '-' + contactId, '', clubId, contactId, '', '', urlIdentifier, 'count', '', '', '', '', 'contact', _selector, _Catselector, '', _Clubselector);

    },
    //Generate html for template   
    generateHtml: function (templateId, dataJson) {
        var template = $(templateId).html();
        var htmlFinal = _.template(template, dataJson);
        return htmlFinal;
    },
    //Load sidebar from json data
    loadJsonSidebar: function () {
        var sidebarCnt = $(".sidebar_menu").length;
        $.each(this.settings, function (key, sidebarItem) {
            switch (sidebarItem.templateType) {
                case 'general':
                    var menuItems = sidebarItem.menu;
                    var topHtml = FgSidebar.generateHtml(FgSidebar.templateTop, {'data': {title: sidebarItem.title, count: menuItems.items.length}});
                    menuItems.menuType = sidebarItem.menuType;
                    if (sidebarItem.parent) {
                        menuItems.parent = sidebarItem.parent;
                    }
                    menuItems.filterCountUrl = FgSidebar.filterCountUrl;
                    menuItems.filterDataUrl = FgSidebar.filterDataUrl;
                    menuItems.showLoading = FgSidebar.showloading;

                    var menuHtml = FgSidebar.generateHtml(sidebarItem.template, {'data': menuItems});
                    var html = topHtml + menuHtml;
                    if (sidebarItem.settings) {
                        var settingsHtml = FgSidebar.generateHtml(FgSidebar.templateSettings, {'settings': {items: sidebarItem.settings}});
                        html = html + settingsHtml;
                    }
                    $('<li/>', sidebarItem.parent).appendTo(FgSidebar.parentUl).wrapInner(html);
                    break;
                case 'menu2level':
                    var menuItems = sidebarItem.menu;
                    var menuData = {title: sidebarItem.title, count: menuItems.items.length}
                    if (typeof sidebarItem.logo !== "undefined") {
                        menuData.logo = sidebarItem.logo;
                    }
                    var topHtml = FgSidebar.generateHtml(FgSidebar.templateTop, {'data': menuData});
                    menuItems.menuType = sidebarItem.menuType;
                    if (sidebarItem.settingsLevel2) {
                        menuItems.settings = sidebarItem.settingsLevel2;
                    }
                    if (sidebarItem.parent) {
                        menuItems.parent = sidebarItem.parent;
                    }
                    menuItems.showLoading = FgSidebar.showloading;
                    var menuHtml = FgSidebar.generateHtml(sidebarItem.template, {'data': menuItems});
                    menuHtml = '<ul class="page-sidebar-menu sub-menu firstleval ">' + menuHtml + '</ul>';
                    var html = topHtml + menuHtml;
                    if (sidebarItem.settingsLevel1) {
                        var settingsHtml = FgSidebar.generateHtml(FgSidebar.templateSettings, {'settings': {items: sidebarItem.settingsLevel1}});
                        html = html + settingsHtml;
                        //$(sidebarItem.wrapper).append(settingsHtml);
                    }
                    $('<li/>', sidebarItem.parent).appendTo(FgSidebar.parentUl).wrapInner(html);
                    break;

            }
        });
        var optionsHtml = FgSidebar.generateHtml(FgSidebar.templateOptions, {'data': FgSidebar.options});
        $('<li/>', {'id': 'sidebar_options_li', 'class': 'sidebar_options_li'}).appendTo(FgSidebar.parentUl).wrapInner(optionsHtml);

        FgSidebar.setDefault();
        FgSidebar.show('');
        FgPopOver.customPophover(".fg-dev-sidebar-popover", true);
        if (window.sidebarTopnavLoaded == 2) {
            FgCountUpdate.updateSidebarAllactive('add', parseInt($('ul.dropdown-menu li.fg-dev-header-nav-active a span.badge').text(), 10));
        }
        FgSidebar.addNewElement(this.newElementLevel1, this.newElementLevel2, this.newElementLevel2Sub, this.newElementUrl);
    },
    //Position check
    positionCheck: function (element, target) {
        $('body').on('click', function (e) {
            $(target).hide();
        });
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
    },
    //Bookamrk click handling
    bookmarkClick: function () {
        jQuery(document).on('click', '.bookmarkclick', function (event) {
            var type = $(this).attr('data_type');
            var book_id = $(this).attr('data-id');
            var next_bookmark_id = $(this).attr('data-bookmark-id');
            var url = FgSidebar.bookemarkUpdateUrl;
            var urlParams = {type: type, selectedId: book_id};
            var requestData = $.extend(urlParams, FgSidebar.extraParams);
            $.get(url, requestData);
            if ($(this).hasClass('fa-bookmark') && !$(this).hasClass('fa-bookmark-o')) {
                $(this).removeClass('fa-bookmark');
                $(this).addClass('fa-bookmark-o');
                $('i[data_type="' + type + '"][data-id="' + book_id + '"]').removeClass('fa-bookmark').addClass('fa-bookmark-o');
                $('i[data_type="' + type + '"][data-id="' + book_id + '"]').filter(".bookmarked").parent().parent().remove();
            } else if ($(this).hasClass('fa-bookmark-o')) {
                $(this).addClass('fa-bookmark');
                $(this).removeClass('fa-bookmark-o');
                $('i[data_type="' + type + '"][data-id="' + book_id + '"]').addClass('fa-bookmark').removeClass('fa-bookmark-o');
                var addClass = $(this).parent().parent().hasClass('fg-dev-draggable') ? 'fg-dev-draggable' : $(this).parent().parent().hasClass('fg-dev-non-draggable') ? 'fg-dev-non-draggable' : '';//(type =='class')
                $('#bookmark_li .sub-menu').append('<li id="bookmark_li_' + next_bookmark_id + '" class="subclass ' + addClass + '" >' + $('i[data_type="' + type + '"][data-id="' + book_id + '"]').parent().parent().html() + '</li>');
                $('#bookmark_li ul i').addClass('bookmarked');
            }
            if ($('#bookmark_li .bookmarkdown li').length > 1) {
                $('#bookmark_li .btn-group .dropdown-menu li').removeClass('disabled');
                ($('#bookmark_li .btn-group .dropdown-menu li a').attr('url') != "#") ? $('#bookmark_li .btn-group .dropdown-menu li a').attr('href', $('#bookmark_li .btn-group .dropdown-menu li a').attr('url')) : '';
            } else {
                $('#bookmark_li .btn-group .dropdown-menu li').addClass('disabled');
                $('.disabled a').attr('href') ? $('.disabled a').attr('url', $('.disabled a').attr('href')) : $('.disabled a').attr('href', $('.disabled a').attr('url'));
                $('.disabled a').attr('href', '#');
            }
            FgSidebar.sidebarNewmenuApplyragevent('#bookmark_li_' + next_bookmark_id);
        });
    },
    //bookmarkSectionDislay : If there are no bookmark do not show settings
    bookmarkSectionDislay: function () {
        if ($('#bookmark_li .bookmarkdown li').length > 1) {
            $('#bookmark_li .btn-group .dropdown-menu li').removeClass('disabled');
            ($('#bookmark_li .btn-group .dropdown-menu li a').attr('url') !== "#") ? $('#bookmark_li .btn-group .dropdown-menu li a').attr('href', $('#bookmark_li .btn-group .dropdown-menu li a').attr('url')) : '';
        } else {
            $('#bookmark_li .btn-group .dropdown-menu li').addClass('disabled');
            $('.disabled a').attr('href') ? $('.disabled a').attr('url', $('.disabled a').attr('href')) : $('.disabled a').attr('href', $('.disabled a').attr('url'));
            $('.disabled a').attr('href', '#');
        }
    },
    //Append new element
    appendNewElement: function (thisVar, data, parentId) {
        switch (data.input[0]['itemType']) {
            default:
                _.find(jsonData[data.input[0]['itemType']]['entry'], function (item) {
                    return item.id == data.input[0]['categoryId']
                }).input.push(data.input[0]);
                _.find(jsonData[data.input[0]['itemType']]['entry'], function (item) {
                    return item.id == data.input[0]['categoryId']
                }).show_filter = 1
                jsonData[data.input[0]['itemType']]['show_filter'] = 1;
        }
        data.parentMenuId = parentId;
        filter.data().plugin_searchFilter.reCache(jsonData);
        html = FgSidebar.generateHtml(FgSidebar.templateLevel2, {'subMenu': data});
        $(html).appendTo(thisVar.closest("ul"));
        thisVar.closest("li").remove();
    },
    //addNewElement handling
    addNewElement: function (new_element_template_level1, new_element_template_level2, new_element_template_level2_withfunction, path) {

        $(document).on('click', '.create_new_element', function (event) {
            var thisDataTarget = $(this).data("target");
            $(thisDataTarget).addClass("open active");
            $(thisDataTarget).find('a span').first().addClass('open');
            if ($(thisDataTarget).children('ul').length <= 0) {
                $(thisDataTarget).append('<ul class="sub-menu"></ul>');
            }
            $(thisDataTarget).children(".sub-menu").show();
            var elementType = $(this).attr('element_type');
            var placeholder = $(this).attr('data-placeholder');
            var hierarchy = $(this).attr('hierarchy');
            var cat_id = $(this).attr('data-catid');
            if ($(this).attr('role_type') != undefined) {
                var roleType = $(this).attr('role_type');
            }

            var hiddenVal = 0;
            if (hierarchy == 1) {
                hiddenVal = $(thisDataTarget).find('.dev-firstlevel').length;
            } else if (hierarchy == 2) {
                hiddenVal = $(thisDataTarget).find('.dev-secondlevel').length;
            }

            if (hiddenVal == 0) {
                if (hierarchy == 1) {
                    $(thisDataTarget).children('ul').append(new_element_template_level1);
                } else if (hierarchy == 2) {
                    var functionType = $(this).attr('data-fntype');
                    var addrolefn = false;
                    if (functionType == 'individual' || (elementType == 'team' && jsonData['TEAM'].functionCount == 0)) {
                        addrolefn = true;
                        $(thisDataTarget).children(".sub-menu").append(new_element_template_level2_withfunction);
                    } else {
                        $(thisDataTarget).children(".sub-menu").append(new_element_template_level2);
                    }
                    $(thisDataTarget).find(">ul>li > .add-new-blk .new-blk-input").attr("function_type", functionType);
                    $(thisDataTarget).find(">ul>li > .add-new-blk .new-blk-input").attr("data-catid", cat_id);
                }
                $(thisDataTarget).find(">ul>li > .add-new-blk .new-blk-input").attr("hierarchy", hierarchy);
                Layout.fixContentHeight();

                if (addrolefn) {
                    var functionPlaceholder = $(this).attr('data-function-placeholder');
                    $(thisDataTarget).find(".add-new-blk .dev-new-function").attr("name", "function_new_input_title");
                    $(thisDataTarget).find(".add-new-blk .dev-new-function").attr("placeholder", functionPlaceholder);
                    $(thisDataTarget).find(".add-new-blk .dev-new-role").attr("name", "role_new_input_title");
                    $(thisDataTarget).find(".add-new-blk .dev-new-role").attr("placeholder", placeholder);
                } else {
                    $(thisDataTarget).find(">ul>li > .add-new-blk .new-blk-input").attr("name", elementType + "_new_input_title");
                    $(thisDataTarget).find(">ul>li > .add-new-blk .new-blk-input").attr("placeholder", placeholder);
                }
                $(thisDataTarget).find(">ul>li > .add-new-blk .new-blk-input").attr("element_type", elementType);
                if (roleType != undefined) {
                    $(thisDataTarget).find(">ul>li > .add-new-blk .new-blk-input").attr("role_type", roleType);
                }
                $(thisDataTarget).find(">ul>li > .add-new-blk").children(".new-blk-input").focus();
                $("html, body").animate({scrollTop: $(thisDataTarget).find(">ul>li > .add-new-blk").children(".new-blk-input").offset().top - 250}, 100);
                $('.' + elementType).val(1);
                $('.popovers').popover();
            } else {
                $(thisDataTarget).find(">ul>li > .add-new-blk").children(".new-blk-input").focus();
                $("html, body").animate({scrollTop: $(thisDataTarget).find(">ul>li > .add-new-blk").children(".new-blk-input").offset().top - 250}, 100);
            }
        });

        $(document).on('click', '.add-new-blk .fa-check', function () {
            $(this).prop('disabled', true);
            var thisVar = $(this);
            var roleType = '';
            var addrolefn = false;
            var value = '';
            var rolevalue = '';
            var fnvalue = '';
            if ($(this).parent().parent().find('input').length == 2) {
                addrolefn = true;
                rolevalue = $(this).parent().parent().find('input[name=role_new_input_title]').val();
                fnvalue = $(this).parent().parent().find('input[name=function_new_input_title]').val();
            } else {
                value = $(this).siblings('input').val();

            }
            var elementType = $(this).siblings('input').attr('element_type');
            var hierarchy = $(this).siblings('input').attr('hierarchy');
            var divClassName = 'fg-no-data-sidebar';
            if ($(this).siblings('input').attr('role_type') != undefined) {
                roleType = $(this).siblings('input').attr('role_type');
            }
            var categoryId = $(this).siblings('input').attr('data-catid');
            if (elementType == 'role' || elementType == 'team' || elementType == 'workgroup') {
                var fnType = $(this).siblings('input').attr('function_type');
                var functionCnt = $(this).siblings('input').attr('function_count');
                divClassName = 'fg-no-data-sidebar-sub';
            }
            var url = path + '?elementType=' + elementType + '&functionType=' + fnType + '&roleType=' + roleType;
            var data = {elementType: elementType, functionType: fnType, roleType: roleType};
            if (typeof (categoryId) != "undefined" && categoryId !== null) {
                data.category_id = categoryId;
            }
            if (addrolefn) {
                data.value = rolevalue;
                data.fnvalue = fnvalue;
            } else {
                data.value = value;
            }

            var requestData = $.extend(data, FgSidebar.extraParams);

            if ((value != '') || (rolevalue != '' && fnvalue != '')) {
                FgUtility.startPageLoading();
                $.getJSON(url, requestData, function (data) {
                    var parentDiv = thisVar.parent().parent().parent();
                    if (hierarchy == 1) {
                        var parentId = thisVar.closest("ul").parent('li').attr('id');
                        
                        //To handle the movement of membership to top, when there is only one membership type
                        if (elementType == 'membership' || elementType == 'fed_membership') {
                            itemType = (elementType == 'membership') ? 'CM' :'FM';
                            data.input[0]['categoryId'] = elementType;
                            data.input[0]['itemType'] = itemType;
                        }
                        
                        if (typeof data.addToJson !== 'undefined') {
                            FgSidebar.appendNewElement(thisVar, data, parentId);
                        }
                        data.parent = FgSidebar.settings[parentId].parent;
                        data.settings = FgSidebar.settings[parentId].settingsLevel2;
                        html = FgSidebar.generateHtml(FgSidebar.templateLevel1, {'data': data});
                        $(html).appendTo(thisVar.closest("ul"));
                        thisVar.closest("li").remove();
                        if(typeof data['input'] != 'undefined'){
                            var sidebarNewMenuid = '#' + data['input'][0]['menuItemId'];
                            FgSidebar.sidebarNewmenuApplyragevent(sidebarNewMenuid);
                        }
                        
                    } else {
                        divClassName = 'fg-no-data-sidebar-sub';
                        var parentId = thisVar.closest("ul").parent('li').attr('id');
                        if (elementType == 'team') {
                            jsonData['TEAM'].functionCount = parseInt(jsonData['TEAM'].functionCount) + 1
                        }
                        if (elementType == 'service') {
                            itemType = 'SS';
                        } else if (elementType == 'membership' || elementType == 'fed_membership') {
                            itemType=(elementType == 'membership') ? 'CM' :'FM';
                            data.input[0]['categoryId'] = elementType;
                        } else {
                            itemType = data.input[0]['itemType'];
                        }
                        if (_.chain(jsonData[itemType]['entry']).where({"id": data.input[0]['categoryId']}).pluck("id").value() == '') {
                            var parentTitle = $('#' + parentId).find('> div > span.title').text();
                            jsonData[itemType]['entry'].push({'id': data.input[0]['categoryId'], 'title': parentTitle, 'input': data.input, 'type': 'select', 'show_filter': 1});
                            jsonData[itemType]['show_filter'] = 1;
                        } else {
                            var parentTitle = $('#' + parentId).find('> a > span.title').text();
                            if (_.find(jsonData[itemType]['entry'], function (item) {
                                return item.id == data.input[0]['categoryId']
                            }).input)
                                _.find(jsonData[itemType]['entry'], function (item) {
                                    return item.id == data.input[0]['categoryId']
                                }).input.push(data.input[0]);
                            _.find(jsonData[itemType]['entry'], function (item) {
                                return item.id == data.input[0]['categoryId']
                            }).show_filter = 1
                            jsonData[itemType]['show_filter'] = 1;
                        }
                        data.parentMenuId = parentId;
                        filter.data().plugin_searchFilter.reCache(jsonData);
                        html = FgSidebar.generateHtml(FgSidebar.templateLevel2, {'subMenu': data});
                        $(html).appendTo(thisVar.closest("ul"));
                        thisVar.closest("li").remove();
                        //This block is used for enabling drag-drop functionality and related icon handling in sidebar menu
                        var sidebarNewMenuid = '#' + data['input'][0]['menuItemId'];
                        FgSidebar.sidebarNewmenuApplyragevent(sidebarNewMenuid);
                        //This block is used for enabling drag-drop functionality and related icon handling in sidebar menu
                    }
                    if (data.addRoleFn == 'rolefunction') {
                        parentDiv = parentDiv.parent();
                    }
                    FgSidebar.handleArrows(parentDiv, divClassName);
                    if (data.addRoleFn == 'rolefunction') {
                        thisVar.parent().parent().parent(".subclass").remove();
                    } else {
                        thisVar.parent().parent(".subclass").remove();
                    }
                    $('.' + elementType).val(0);
                    FgUtility.stopPageLoading();
                    if (hierarchy == 2 && elementType == 'subcategory'){
                        FgSidebar.enableDocUpload();
                    }                    
                });
            } else {
                $(this).prop('disabled', false);
            }
        });

        $(document).on('keypress', '.sidebar-create', function (event) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            if (keycode == '13') {// Enter key press
                $(this).parent().find('i.fa-check').trigger("click");
            } else if (keycode == '27') { // Esc key press
                $(this).parent().find('i.fa-times').trigger("click");
            }
        });
        $(document).on('click', '.add-new-blk .fa-times', function () {
            $(this).siblings('input').val('')
            var elementType = $(this).siblings('input').attr('element_type');
            $('.' + elementType).val(0);
            $(this).parents(".subclass").remove();
            activeMenu = '#' + ((FgSidebar.activeSubMenuVar) ? localStorage.getItem(FgSidebar.activeSubMenuVar) : localStorage.getItem("submenu"));
            $(activeMenu).addClass("active");
        });
    },
    //method for creating category and subcategory levels in sidebar
    addNewCategoryAndSubCategory: function (categorydata, parentId, parentMenuId) {
        //create category and subcategory level in sidebar
        thisVar = $('#' + parentId);
        categorydata.parent = FgSidebar.settings[parentId].parent;
        categorydata.settings = FgSidebar.settings[parentId].settingsLevel2;
        categorydata.parentMenuId = parentMenuId;
        html = FgSidebar.generateHtml(FgSidebar.templateLevel1, {'data': categorydata});
        thisVar.children("ul").append(html);
        thisVar.addClass("open");
        $('#' + parentMenuId).addClass("open");
        jsonData[parentId]['entry'] = categorydata.items;
        jsonData[parentId]['show_filter'] = 1;
        filter.data().plugin_searchFilter.reCache(jsonData);
    },
    //handleArrows
    handleArrows: function (parentDiv, divClassName) {
        if (divClassName == '') {
            divClassName = 'fg-no-data-sidebar';
        }
        if (parentDiv.siblings('.' + divClassName).children('span:first').hasClass('fg-without-arrow')) {
            parentDiv.siblings('.' + divClassName).children('span:first').removeClass('fg-without-arrow');
            parentDiv.siblings('.' + divClassName).children('span:first').addClass('arrow pull-left open');
            var innerhtml = parentDiv.siblings('.' + divClassName).html();
            parentDiv.siblings('.' + divClassName).after('<a href="#" class="fg-appended-html-sidebar">' + innerhtml + '</a>');
            parentDiv.siblings('.' + divClassName).remove();
        }
    },
    handlePreOpening: function (event, sidebarName, container) {
        //event: save - Save the opened menus
        //event: open - Open the pre-opened menus
        //event: init - Initialize click event handler

        if (typeof sidebarName === "undefined")
            sidebarName = FgSidebar.module;

        if (typeof sidebarName == 'string' && sidebarName != '') {
            var localstorageName = 'sidebar_active_' + sidebarName;
            var containerObj = $('#sidemenu_bar')
            if (container != '' && container != undefined)
                containerObj = containerObj.find(container);

            if (event == 'save')
            {
                //Get the opened navs
                var openedMenus = containerObj.find('li.open:visible').addBack('li.open:visible');
                var openedMenuArray = [];
                openedMenus.each(function (index) {
                    openedMenuArray[index] = $(this).attr('id');
                });
                localStorage.setItem(localstorageName, openedMenuArray.join());
            }
            else if (event == 'open')
            {
                if (localStorage.getItem(localstorageName) !== null) {
                    var openedMenuArray = localStorage.getItem(localstorageName).split(',');
                    for (i = 0; i < openedMenuArray.length; i++)
                    {
                        var openedId = openedMenuArray[i];
                        $('#' + openedId).addClass('open');
                        $('#' + openedId).parent('ul.sub-menu').show();
                        $('#' + openedId + '>a>span.arrow').addClass('open')
                    }
                }

            }
            else if (event == 'init')
            {
                $("#sidemenu_bar").on("click", "ul,li", function () {
                    setTimeout(function () {
                        FgSidebar.handlePreOpening('save', sidebarName);
                    }, 500); //Need to wait till the sub-menu open-close animation is complete
                });
            }

        }
    },
    //handleSidebarMenu
    handleSidebarMenu: function () {

        var viewport = Metronic.getViewPort();

        $('.page-sidebar').off('click', 'li > a'); // prevent parent event bind
        jQuery('.page-sidebar').on('click', 'li > a:not(".filterCount")', function (e) {
            if ($(this).next().hasClass('sub-menu') == false) {
                if ($('.btn-navbar').hasClass('collapsed') == false) {
                    $('.btn-navbar').click();
                }
                return;
            }

            if ($(this).next().hasClass('sub-menu always-open')) {
                return;
            }

            var parent = $(this).parent().parent();
            var the = $(this);
            var menu = $('.page-sidebar-menu');
            var menu2 = $('.side_main_menu');
            var sub = jQuery(this).next();

            var autoScroll = menu.data("auto-scroll") ? menu.data("auto-scroll") : true;
            var slideSpeed = menu.data("slide-speed") ? parseInt(menu.data("slide-speed")) : 200;

            var slideOffeset = -200;

            if (sub.is(":visible")) {

                sub.slideUp(slideSpeed, function () {
                    jQuery(this).parent().find('.arrow').first().removeClass("open");
                    if (autoScroll == true && $('body').hasClass('page-sidebar-closed') == false) {
                        if ($('body').hasClass('page-sidebar-fixed')) {
                            //menu.slimScroll({'scrollTo': (the.position()).top});
                            if (viewport.width >= 992) {
                                menu2.slimScroll();
                            }

                        } else {
                            Metronic.scrollTo(the, slideOffeset);
                        }
                    }
                    if ($(this).parent())
                        $(this).parent().removeClass("open");
                    Layout.fixContentHeight();
                });
            } else {

                sub.slideDown(slideSpeed, function () {
                    jQuery(this).parent().find('.arrow').first().addClass("open");
                    if (autoScroll == true && $('body').hasClass('page-sidebar-closed') == false) {
                        if ($('body').hasClass('page-sidebar-fixed')) {
                            //menu.slimScroll({'scrollTo': (the.position()).top});
                            if (viewport.width >= 992) {
                                menu2.slimScroll();
                            }
                        } else {
                            Metronic.scrollTo(the, slideOffeset);
                        }
                    }
                    if ($(this).parent())
                        $(this).parent().addClass("open");
                    Layout.fixContentHeight();
                });
            }
            e.preventDefault();
        });
    },
    //handleFilterCount: filter refresh icon handling
    handleFilterCount: function () {
        $('.filterCount').live('click', function () {
            if (localStorage.getItem(filterDisplayFlagStorage) == 1) {
                $('.filter-alert').show();
                //enable the filter checkbox
                $("#filterFlag").attr("checked", true);
                jQuery.uniform.update('#filterFlag');
            }
            var filter_id = $(this).attr('filter_id');
            var id = $(this).attr('id');
            var url = $(this).attr('url');
            var replc = '.filterId_' + filter_id;

            $.post(url, {'filterId': filter_id}, function (data) {
                if (data == '-1')
                    $(replc).parent().find('.sidebabar-link').append('<i class="fa fa-warning fg-warning"></i>');
                else
                    $(replc).parent().find('.sidebabar-link').append('<span class="badge badge-round badge-important">' + data + '</span>');
                $(replc).remove();
                //tooltip retained even after count loaded. so remove tooltip
                $('.tooltip').remove();
            });

            return false;
        });
    },
    //sidebarNewmenuApplyragevent
    sidebarNewmenuApplyragevent: function (sidebarNewMenuid) {
        $('body').on('mouseenter', sidebarNewMenuid, function () {
            if (!$(sidebarNewMenuid).data("init")) {
                if ($(sidebarNewMenuid).hasClass('fg-dev-draggable')) {
                    $(sidebarNewMenuid).data("init", true).droppable(FgSidebar.newMenuOpts['draggable']);
                } else {
                    $(sidebarNewMenuid).data("init", true).droppable(FgSidebar.newMenuOpts['nondraggable']);
                }
            }
        });
    },
    //filterAnimationInit
    filterAnimationInit: function (id) {
        FgUtility.startPageLoading();
        $(id).on('click', function () {
            FgUtility.startPageLoading();
        })
    },
    //Prepare Context menu Area                },
    //droppableEventIconHandling
    droppableEventIconHandling: function (from) {
        $("#sidemenu_bar li.fg-dev-draggable").liveDroppable({
            hoverClass: "fg-sidebar-hover",
            drop: function (event, ui) {
                FgSidebar.dragDropAssignmentProcessing(event, ui, $(this), from);
                $(".fg-dev-drag-active").removeClass('fg-dev-drag-active');
            },
            deactivate: function (e) {
                $(".fg-dev-drag-active").removeClass('fg-dev-drag-active');
            },
        });

        $("#sidemenu_bar li.fg-dev-non-draggable").liveDroppable({
            hoverClass: "fg-sidebar-not-allowed",
            drop: function (event, ui) {
                $(".fg-dev-drag-active").removeClass('fg-dev-drag-active');
            },
            deactivate: function (e) {
                $(".fg-dev-drag-active").removeClass('fg-dev-drag-active');
            },
        });
        $("body").liveDroppable({
            drop: function (event, ui) {
                $("#sidemenu_bar *").removeAttr("style");
            },
            deactivate: function (e) {
                $("#sidemenu_bar *").removeAttr("style");
            },
        });

    },
    //Common function to handle dragDropAssignment
    dragDropAssignmentProcessing: function (event, ui, thisObj, from) {
        //Initializing area
        var assignData = '';
        var contextMenuSettings = '';
        var draggedDataArr = {};
        var draggedDataArr1 = {};
        var function_id = '';
        var contactSelType = '';
        //Initializing area

        //Dragged item details used in case of dragging only 1 item

        //Dirty fix - Need to rework on this by fixing in contact list
//        if (from == 'contact' || from == 'sponsor') {
//            var itemId = ui.draggable.siblings('.checker').children().find('input').attr('id');
//        } else {
        var itemId = ui.draggable.siblings('input').attr('id');
//        }
        draggedDataArr['id'] = itemId;
        if (from == 'contact' || from == 'sponsor') {
            var itemName = ui.draggable.parents().eq(2).find('a.fg-dev-contactname').html();
            draggedDataArr['contactname'] = itemName;
            var fedMembershipId = ui.draggable.siblings('input').attr('data-fed-membership-id');
            draggedDataArr['fedMembershipId'] = fedMembershipId;
            var contactClub = ui.draggable.siblings('input').attr('data-contactclub');
            draggedDataArr['contactClub'] = contactClub;
            var clubMembershipId = ui.draggable.siblings('input').attr('data-club-membership_id');
            draggedDataArr['clubMembershipId'] = clubMembershipId;
            var fedMembershipApprove = ui.draggable.siblings('input').attr('data-fedmember-approve');
            draggedDataArr['fedMembershipApprove'] = fedMembershipApprove;
        } else if (from == 'document') {
            var itemName = ui.draggable.parents().eq(2).find('a.fg-dev-docname').html();
            draggedDataArr['documentSubcategoryId'] = ui.draggable.siblings('input').attr('data-subcategoryId');
            draggedDataArr['documentName'] = itemName;
        } else {
            var itemName = ui.draggable.parents().eq(2).find('a.fg-dev-clubname').html();
            draggedDataArr['clubname'] = itemName;
        }
        draggedDataArr1 = {'0': draggedDataArr};
        $("#selcontacthidden").val(JSON.stringify(draggedDataArr1));
        //Dragged item details

        //Dropped menu details
        var category_id = thisObj.find('.sidebabar-link').attr('data-categoryid');
        if (from == 'contact') {
            var sidebarType = thisObj.find('.sidebabar-link').attr('data-type');
            if (sidebarType == 'FI') {
                function_id = thisObj.find('.sidebabar-link').attr('data-id');
            } else {
                var role_id = thisObj.find('.sidebabar-link').attr('data-id');
            }
            var cat_club_id = thisObj.find('.sidebabar-link').attr('data-club');
            var cat_fn_type = thisObj.parent().siblings('.btngrpdiv').find('.create_new_element').attr('data-fntype');
        } else {
            var role_id = thisObj.find('.sidebabar-link').attr('data-id');
        }
        var title = (thisObj.find('.sidebabar-link > span.title').text()).trim();
        var categoryTitle = (thisObj.find('.sidebabar-link').parents().eq(1).find('a > span.title').html()).trim();
        //Dropped menu details

        //active menu details
        var activeMenuId = localStorage.getItem(FgSidebar.activeSubMenuVar);
        var activeMenuCategory_id = $('#' + activeMenuId).find('.sidebabar-link').attr('data-categoryid');
        var activeMenuRole_id = $('#' + activeMenuId).find('.sidebabar-link').attr('data-id');
        var activeMenuTitle = $('#' + activeMenuId).find('.sidebabar-link > span.title').text();
        if (from == 'contact') {
            var activeMenusidebarType = $('#' + activeMenuId).find('.sidebabar-link').attr('data-type');
        }
        //active menu details

        if (($(".DTFC_LeftBodyWrapper input.dataClass:checked").length) === 0) {
            contactSelType = 'single-select';
        } else {
            contactSelType = 'multi-select';
        }

        var moveText = '';
        if ($('#' + activeMenuId).hasClass('fg-dev-draggable') && activeMenusidebarType !== 'membership' && activeMenusidebarType !== 'fed_membership') {
            moveText = activeMenuTitle.trim();
        }
        var assignmentText = title;
        //Drag-Drop menu details
        contextMenuSettings = {'triggerDiv': 'fg-context-trigger', 'contextMenuDiv': 'fg-context-menu', 'moveText': moveText, 'assignmentText': assignmentText};
        if (from == 'contact') {
            assignData = {'dragMenuId': activeMenuRole_id, 'dragCategoryId': activeMenuCategory_id, 'dragCatType': activeMenusidebarType, 'dropFunctionId': function_id, 'dropMenuId': role_id, 'dropCategoryId': category_id, 'dropCatType': sidebarType, 'dropCategoryFnType': cat_fn_type};
        } else {
            assignData = {'dragMenuId': activeMenuRole_id, 'dragCategoryId': activeMenuCategory_id, 'dropMenuId': role_id, 'dropCategoryId': category_id, 'dropCategoryTitle': categoryTitle, 'moveText': moveText};
        }

        if (from == 'document') {
            showDocumentPopup({assignmentData: assignData, 'selActionType': contactSelType, 'subcategoryName': assignmentText});
        } else if (from == 'sponsor') {
            var selContIds = [];
            var selContNames = [];
            if ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length > 0) {
                $(".DTFC_LeftBodyWrapper input.dataClass:checked").each(function () {
                    var contactId = $(this).attr('id');
                    if ($.inArray(contactId, selContIds) == -1) {
                        selContIds.push(contactId);
                        selContNames.push({'id': contactId, 'name': $(this).parents('tr').find('.fg-dev-contactname').text()});
                    }
                });
            } else {
                var itemId = ui.draggable.siblings('input').attr('id');
                selContIds.push(itemId);
            }
            var form = $('<form action="' + sponsorAssignPath + '" method="post">' +
                    '<input type="hidden" name="dropCategoryId" value="' + category_id + '" />' +
                    '<input type="hidden" name="dropMenuId" value="' + role_id + '" />' +
                    '<input type="hidden" name="contactids" value="' + selContIds + '" />' +
                    '</form>');
            $('body').append(form);
            $(form).submit();
        } else {
            //Prepare Context menu Area
            FgSidebar.displayContextmenu(contextMenuSettings, assignData, from, contactSelType);
            if (moveText !== '' && from !== 'document') {
                $('#fg-context-trigger').triggerHandler('contextmenu');
                $('#fg-context-menu').css({'left': event.pageX, 'top': event.pageY});
            }
        }
    },
    //handle sidebar Contextmenu dynamically
    displayContextmenu: function (contextSettings, contextAssignData, moduleName, contactSelType) {
        var moveOption = true;

        //Setting assign text in context menu     
        FgSidebar.assignmentJsonArr = contextAssignData;
        if (moduleName == 'contact' || moduleName == 'club') {
            //Setting assign text in context menu
            var contextAssignText = FgSidebar.processContextMenu('context', 'assign', contextSettings);
            $('#fg-dev-assign-menu').text(contextAssignText);
            //Setting move text in context menu 
            if (contextSettings['moveText'] != '' && ((FgSidebar.assignmentJsonArr.dropCatType != 'membership') && (FgSidebar.assignmentJsonArr.dropCatType != 'fed_membership'))) {
                var contextMoveText = FgSidebar.processContextMenu('context', 'move', contextSettings);
                $('#fg-dev-move-menu').text(contextMoveText);
                $('#fg-dev-move-menu').parent().addClass('fg-dev-context-display');
            } else { //Remove the menu from context
                $('#fg-dev-move-menu').parent().removeClass('fg-dev-context-display');
                moveOption = false;
            }
        }

        if (moveOption) {
            if (moduleName == 'document') {
                showDocumentPopup({assignmentData: FgSidebar.assignmentJsonArr, 'selActionType': contactSelType, 'subcategoryName': contextSettings['assignmentText']});
            } else {
                $('#' + contextSettings['triggerDiv']).contextmenu({
                    target: '#' + contextSettings['contextMenuDiv'],
                    onItem: function (context, e) {
                        ;
                        var actionType = $(e.target).attr('data-type');
                        showPopup('assignment', {assignmentData: FgSidebar.assignmentJsonArr, 'actionType': actionType, 'selActionType': contactSelType});
                    }
                });
            }
        } else {
            if (($(".DTFC_LeftBodyWrapper input.dataClass:checked").length) == 0) {
                var contactSelType = 'single-select';
            } else {
                var contactSelType = 'multi-select';
            }
            if (moduleName == 'contact') {
                if (FgSidebar.assignmentJsonArr.dropCatType == 'membership' ||FgSidebar.assignmentJsonArr.dropCatType == 'fed_membership') {
                    showPopup('membershipassignment', {assignmentData: FgSidebar.assignmentJsonArr, 'actionType': 'assign', 'selActionType': contactSelType});
                } else {
                    showPopup('assignment', {assignmentData: FgSidebar.assignmentJsonArr, 'actionType': 'assign', 'selActionType': contactSelType});
                }
            } else {
                showPopup('assignment', {assignmentData: FgSidebar.assignmentJsonArr, 'actionType': 'assign', 'selActionType': contactSelType});
            }
        }
    },
    //processContextMenu
    //menutype - assign or move
    //plural - plural or singular
    //menuData - Array of text for processing the menu with translation
    processContextMenu: function (from, menuType, menuData) {
        var menuText = '';
        var checkedlength = $(".DTFC_LeftBodyWrapper input.dataClass:checked").length;
        var plural = (checkedlength == 0) ? 'none' : ((checkedlength > 1) ? 'plural' : 'singular');
        var menuDetails = '';
        if (from == 'context') {
            menuDetails = FgSidebar.dynamicMenus[1].context;
        }
        //PREPARE MENU                
        var settingsText = menuDetails[from + '_' + menuType + '_' + plural + '_text'];
        if (from == 'context') {
            if (menuType == 'assign') {
                var menuText = settingsText.replace("#groupA#", menuData['assignmentText']);
            } else if (menuType == 'move') {
                var moveText = settingsText.replace("#groupA#", menuData['moveText']);
                var menuText = moveText.replace("#groupB#", menuData['assignmentText']);
            }
        }
//        menuText = (menuType != 'remove') ? menuText + '&hellip;' : menuText;
        menuText = (menuType != 'remove') ? menuText + '...' : menuText;
        return menuText
    },
    //DYNAMIC MENU DISPLAY
    processDynamicMenuDisplay: function (obj, listType, actionMenuCount) {
        //Specially added for handling multiple datatable and dynamic menu in the same page       
        if (actionMenuCount === 'multiple') {
            var selItemCount = $('#fg_dev_' + listType).find("input.dataClass:checked").length;
            var contactCnt = $('#fg_dev_' + listType).find("input.dataClass").length;
        } else {
            if ($(".DTFC_LeftHeadWrapper:visible").length === 1) {
                var selItemCount = ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length);
            } else {
//                var section = obj.closest("div[data-type="+listType+"]").find("table.dataTable");             
//                var selItemCount = ($(section).find("input.dataClass:checked").length);
                var selItemCount = ($("input.dataClass:checked").length);
            }
            var contactCnt = $("input.dataClass").length;
        }

        var plural = (selItemCount === 0) ? 'none' : ((selItemCount > 1) ? 'multiple' : 'single');
        var activeMenuId = localStorage.getItem(FgSidebar.activeSubMenuVar);
        var activeMenuTitle = ($('#' + activeMenuId).find('.sidebabar-link > span.title').text()).trim();
        var movemenu = false;
        var removeMenu = false;
        var uploadmenu = false;
        var activeMenyType = $('#' + activeMenuId).find('.sidebabar-link').attr('data-type'); 
        if ($('#' + activeMenuId).hasClass('fg-dev-draggable') && (activeMenyType !== 'membership' && activeMenyType !== 'fed_membership')) {
                movemenu = true;
                removeMenu = true;
                uploadmenu = true;
        }

        var menuData = {'groupA_text': activeMenuTitle};
        var menuDetails = FgSidebar.dynamicMenus[0].actionmenu[listType][plural];
        var siblingUl = obj.next();
        if (!$.isEmptyObject(menuDetails) && typeof menuDetails !== "undefined") {
            if (Object.keys(menuDetails).length > 0) {
                //Declaration area starts
                var dynaminMenuHtml = '';
                var menuDivider = '<li class="divider"> </li>';
                var dataType = (plural === 'none') ? 'all' : 'selected';

                $(siblingUl).empty();
                //Declaration area ends     

                $.each(menuDetails, function (menuType, value) {
                    var menuTitle = '';
                    var hrefLink = 'href="#"';
                    var dataHTMLPath = '';
                    var linkUrl = '';
                    var dataUrl = '';
                    var menuVisibility = false;
                    var extraDataUrl = '';

                    //DEFINE hrefLink for MENU
                    if (value.hrefLink !== undefined) {
                        hrefLink = 'href="' + value.hrefLink + '"';
                    }
                    //DEFINE htmlCreatePath for MENU
                    if (value.htmlCreatePath) {
                        dataHTMLPath = 'data-html-path="' + value.htmlCreatePath + '"';
                    }

                    //DEFINE URL OF EACH MENU
                    if (plural === 'single' && value.appendSelectedId) {
                        var shortUrl = value.dataUrl.substring(0, value.dataUrl.lastIndexOf("/"));
                        if (selItemCount > 0) {
                            var selitemId = ($(".DTFC_LeftBodyWrapper") && $(".DTFC_LeftBodyWrapper").length) > 0 ? $(".DTFC_LeftBodyWrapper input.dataClass:checked").attr('id') : $(".table input.dataClass:checked").attr('id');
                            linkUrl = shortUrl + "/" + selitemId;
                        }
                    } else {
                        linkUrl = value.dataUrl;
                    }

                    //DEFINE dataUrl for MENU 
                    if (menuType === 'upload' && (uploadmenu || $('#' + activeMenuId).attr('id') === 'allActive')) {
                        menuVisibility = (value.isActive) ? true : false;
                    } else {
                        menuVisibility = (value.visibleAlways) ? true : false;
                    }
                    //DEFINE dataUrl for MENU
                    dataUrl = ((value.dataUrl && contactCnt > 0) || (menuVisibility)) ? 'class="fg-dev-menu-click" data-url="' + linkUrl + '"' : 'class="fg-dev-menu-click-inactive"';

                    //DEFINE extraDataUrl for MENU
                    extraDataUrl = (value.extraDataUrl !== undefined && value.extraDataUrl !== '') ? 'data-intermediate-redirect="' + value.extraDataUrl + '"' : '';

                    //DEFINE URL OF EACH MENU                
                    dynaminMenuHtml = '<li><a ' + hrefLink + ' ' + dataUrl + ' ' + extraDataUrl + ' ' + dataHTMLPath + ' data-action-type="' + menuType + '" data-type="' + dataType + '" data-list-type="' + listType + '" data-auto="' + menuType + '">#title#</a></li>';
                    if (menuType === 'move' || menuType === 'remove' || menuType === 'assign') {
                        if (movemenu || removeMenu) {
                            var menuText = value.title.replace("#groupA#", menuData['groupA_text']);
                            menuTitle = menuText;
                        } else if (menuType === 'assign') {
                            menuTitle = value.title;
                        }
                    } else {
                        menuTitle = value.title;
                    }

                    /* append menu */
                    if (menuTitle) {
                        dynaminMenuHtml = dynaminMenuHtml.replace('#title#', menuTitle);
                        $(siblingUl).append(dynaminMenuHtml);
                    }

                    /* add divider section */
                    if (value.divider) {
                        $(siblingUl).append(menuDivider);
                    }
                    $(siblingUl).removeClass('hide');
                });
            }
        } else {
            $(siblingUl).addClass('hide');
        }
    },
    
    enableDocUpload: function () {//enabling document upload action menu when creating document sub category from sidebar
        actionMenuText.active.none.upload.isActive = true;  
        FgSidebar.dynamicMenus.push({actionmenu: actionMenuText});
        var dataType = $('.fgContactdrop').attr('data-type');
        var actionMenuType = $('.fgContactdrop').attr('data-menu-type');
        FgSidebar.processDynamicMenuDisplay($('.fgContactdrop'), dataType, actionMenuType);

    }
};


//---- disable unwanted hover on dropping area -----------