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
    isDataTable: true,
    extraParams: {},
    activeMenuData : {},
    //No fold in/fold out functionality
    isAlwaysOpen: false,
    frompage: '', //if from gallery set as gallery
    init: function (new_element_template) {
        this.loadJsonSidebar();
        //FgFormTools.handleUniform();
        this.handleSidebarMenu(); // override default metronix page sidebar slideToggle
        FgSidebar.bookmarkSectionDislay();
        FgSidebar.bookmarkClick();

        // for options handling
        $('#sidemenu_bar').on('click', '.clsid', function () {
            if (!$("#" + $(this).attr('id')).is(':checked')) {
                var chkval = $(this).val();
                $("#" + chkval).hide();
            } else {
                var chkval1 = $(this).val();
                $("#" + chkval1).show();
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

        // need to check
        //Sidebar click hadling from various areas (by excluding filter and bookmark icon click handling)
        $('#sidemenu_bar').on('click', '.subclass > a.sidebabar-link', function () {

            var arr = new Array();
            localStorage.removeItem(FgSidebar.activeMenuVar);
            var actid = $(this).parents('li.open').map(function () {
                return $(this).attr('id');
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

        //FgSidebar.handleFilterCount();
    },
    //Default settings of sidebar
    setDefault: function () {
        var activeMenu = localStorage.getItem(FgSidebar.activeMenuVar);
        var activeSubMenu = localStorage.getItem(FgSidebar.activeSubMenuVar);  
        if(FgSidebar.module == 'gallery'){  
            if (($('#'+activeMenu).length == 0) || ( 
                  ($('#'+activeMenu).length != 0) &&
                  ($('#'+activeSubMenu).length == 0) && 
                  (activeSubMenu != '' ) 
            )) {
                $('.filter-alert').hide();
                /* IF the local storage not cotains anything, then set it to 'Active contact'*/
                localStorage.setItem(FgSidebar.activeMenuVar, FgSidebar.defaultMenu);
                localStorage.setItem(FgSidebar.activeSubMenuVar, FgSidebar.defaultSubMenu);
                localStorage.setItem(FgLocalStorageNames.gallery.selectedAlbum,'ALL');
            }
        } else if (FgSidebar.module == 'cms') { //Temporary for CMS
            $('#sidemenu_bar').find('.active').removeClass('active');
            localStorage.setItem(FgSidebar.activeMenuVar, FgSidebar.defaultMenu);
            localStorage.setItem(FgSidebar.activeSubMenuVar, FgSidebar.defaultSubMenu);
        } else{
            if (activeMenu == null || ($('#'+activeSubMenu).length == 0)) {
                $('.filter-alert').hide();
                /* IF the local storage not cotains anything, then set it to 'Active contact'*/
                localStorage.setItem(FgSidebar.activeMenuVar, FgSidebar.defaultMenu);
                localStorage.setItem(FgSidebar.activeSubMenuVar, FgSidebar.defaultSubMenu);
            }
        }
    },
    //Checkbox status of sidebar options
    checkBoxStatus: function () {
        var checkElement = localStorage.getItem(FgSidebar.activeOptionsVar);
        $('.clsid').prop('checked', true);
        jQuery('.clsid').uniform();

        if (checkElement == null) {
            return false;
        }
        var disabledElem = checkElement.split(",");
        $.each(disabledElem, function (i) {
            $('#' + disabledElem[i]).hide();
            $('input[value="' + disabledElem[i] + '"]').prop('checked', false);
            jQuery('input[value="' + disabledElem[i] + '"]').uniform();
        })
    },
    //Show sidebar
    show: function (ur) {
        var getstr = localStorage.getItem(FgSidebar.activeMenuVar);
        if (getstr != null) {
            if(FgSidebar.module == "gallery"){
                activeSubMenu = localStorage.getItem(FgSidebar.activeSubMenuVar);
                if(activeSubMenu == ''){
                   
                   $('#'+getstr).parents().eq(1).toggleClass('open');
                   $('#'+getstr).toggleClass('open');
                   $('#'+getstr).parents().eq(1).children('a').find('span.arrow').toggleClass('open');
                   //$('#'+getstr).parents().eq(1).children('ul').toggleClass('hhh show');
                   $('#'+getstr).parents().eq(1).children('ul').css({
                        'display': 'block'
                    });
                   $('#'+getstr).children('.sub-menu').css({
                        'display': 'block'
                    });
                    $('.page-title > .page-title-text').text($('#' + getstr + ' .sidebabar-link:eq(0) .title').text());
                    var activeSidebarLink = getstr + '> a.sidebabar-link';
                    $('#'+activeSidebarLink+' .arrow').toggleClass('open');
                    FgSidebar.activeMenuData = {menuType: $('#'+activeSidebarLink).attr('data-type'), categoryId: $('#'+activeSidebarLink).attr('data-categoryid'), id: $('#'+activeSidebarLink).attr('data-id')};
    
                }else{
                    FgSidebar.handleSubMenuSelect();
                }
            }else{
                FgSidebar.handleSubMenuSelect();
            }
        }
        this.checkBoxStatus();
        window.sidebarTopnavLoaded = window.sidebarTopnavLoaded + 1;

    },
    handleSubMenuSelect: function(){
        var targetBlock = '.sub-menu',
        activeMenu = '#' + localStorage.getItem(FgSidebar.activeSubMenuVar);
        closestMenu = $(activeMenu).closest(targetBlock);
        $(activeMenu).addClass("active");
        closestMenu.closest('li').addClass('open ');
        closestMenu.closest('li').children('a').find('span.arrow').toggleClass('open');
        closestMenu.closest('li').closest(targetBlock).closest('li').addClass('open active');
        $('.page-sidebar-menu .open.active > a:first-child .arrow').addClass('open');

        $(activeMenu).closest('.sub-menu').css({
            'display': 'block'
        });
        var submenuId = localStorage.getItem(FgSidebar.activeSubMenuVar);
        if (submenuId == 'allActive') {
            $('.page-title > .page-title-text').text(FgSidebar.defaultTitle);
        } else {
            $('.page-title > .page-title-text').text($('#' + submenuId + ' .sidebabar-link .title').text());
        }
        //Update count of selected sidebar menu in listing page
        var activeMenuCnt = ($(activeMenu).find(".badge").length > 0) ? $(activeMenu).find(".badge").text() : $("#fcount").text();
        $("#tcount").html(activeMenuCnt);
        var activeSidebarLink = activeMenu + '> a.sidebabar-link';

        FgSidebar.activeMenuData = {menuType: $(activeSidebarLink).attr('data-type'), categoryId: $(activeSidebarLink).attr('data-categoryid'), id: $(activeSidebarLink).attr('data-id')};
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
        $(FgSidebar.parentUl).html("");
        $.each(this.settings, function (key, sidebarItem) {
            switch (sidebarItem.templateType) {
                case 'general':
                    var menuItems = sidebarItem.menu;
                    var topHtml = FgSidebar.generateHtml(FgSidebar.templateTop, {
                        'data': {
                            title: sidebarItem.title,
                            count: menuItems.items.length
                        }
                    });
                    menuItems.menuType = sidebarItem.menuType;
                    if (sidebarItem.parent) {
                        menuItems.parent = sidebarItem.parent;
                    }
                    menuItems.filterCountUrl = FgSidebar.filterCountUrl;
                    menuItems.filterDataUrl = FgSidebar.filterDataUrl;
                    menuItems.showLoading = FgSidebar.showloading;

                    var menuHtml = FgSidebar.generateHtml(sidebarItem.template, {
                        'data': menuItems
                    });
                    var html = topHtml + menuHtml;
                    if (sidebarItem.settings) {
                        var settingsHtml = FgSidebar.generateHtml(FgSidebar.templateSettings, {
                            'settings': {
                                items: sidebarItem.settings
                            }
                        });
                        html = html + settingsHtml;
                    }
                    $('<li/>', sidebarItem.parent).appendTo(FgSidebar.parentUl).wrapInner(html);
                    break;
                case 'menu2level':
                    var menuItems = sidebarItem.menu;
                    var menuData = {
                        title: sidebarItem.title,
                        count: menuItems.items.length
                    }
                    if (typeof sidebarItem.logo !== "undefined") {
                        menuData.logo = sidebarItem.logo;
                    }
                    var topHtml = FgSidebar.generateHtml(FgSidebar.templateTop, {
                        'data': menuData
                    });
                    menuItems.menuType = sidebarItem.menuType;
                    if (sidebarItem.settingsLevel2) {
                        menuItems.settings = sidebarItem.settingsLevel2;
                    }
                    if (sidebarItem.parent) {
                        menuItems.parent = sidebarItem.parent;
                    }
                    menuItems.showLoading = FgSidebar.showloading;
                    var menuHtml = FgSidebar.generateHtml(sidebarItem.template, {
                        'data': menuItems
                    });
                    menuHtml = '<ul class="page-sidebar-menu sub-menu firstleval ">' + menuHtml + '</ul>';
                    var html = topHtml + menuHtml;
                    if (sidebarItem.settingsLevel1) {
                        var settingsHtml = FgSidebar.generateHtml(FgSidebar.templateSettings, {
                            'settings': {
                                items: sidebarItem.settingsLevel1
                            }
                        });
                        html = html + settingsHtml;
                        //$(sidebarItem.wrapper).append(settingsHtml);
                    }
                    $('<li/>', sidebarItem.parent).appendTo(FgSidebar.parentUl).wrapInner(html);
                    break;

            }
        });
        if(FgSidebar.options.length > 0){
            var optionsHtml = FgSidebar.generateHtml(FgSidebar.templateOptions, {
                'data': FgSidebar.options
            });        
            $('<li/>', {
                'id': 'sidebar_options_li',
                'class': 'sidebar_options_li'
            }).appendTo(FgSidebar.parentUl).wrapInner(optionsHtml);
        }
        //No fold in/fold out functionality
        if(this.isAlwaysOpen){
            $('.page-sidebar-wrapper').addClass('fg-always-open');
        }
        FgSidebar.setDefault();
        FgSidebar.show('');
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
            var urlParams = {
                type: type,
                selectedId: book_id
            };
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
                var addClass = $(this).parent().parent().hasClass('fg-dev-draggable') ? 'fg-dev-draggable' : $(this).parent().parent().hasClass('fg-dev-non-draggable') ? 'fg-dev-non-draggable' : ''; //(type =='class')
                var listContent = $('i[data_type="' + type + '"][data-id="' + book_id + '"]').parent().parent();
                listContent.find('.btngrpdiv').remove();
                $('#bookmark_li .sub-menu').append('<li id="bookmark_li_' + next_bookmark_id + '" class="subclass ' + addClass + '" >' + listContent.html() + '</li>');
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
    //handleArrows
    handleArrows: function (parentDiv, divClassName) {
        if (divClassName == '') {
            divClassName = 'fg-no-data-sidebar';
        }
        if (parentDiv.siblings('.' + divClassName).children('span:first').hasClass('fg-without-arrow')) {
            parentDiv.siblings('.' + divClassName).children('span:first').removeClass('fg-without-arrow');
            parentDiv.siblings('.' + divClassName).children('span:first').addClass('arrow pull-left open');
            parentDiv.siblings('.' + divClassName).children('i').remove();
            var dataId = parentDiv.siblings('.' + divClassName).data('id');
            var dataType = parentDiv.siblings('.' + divClassName).data('type');
            var innerhtml = parentDiv.siblings('.' + divClassName).html();
            parentDiv.siblings('.' + divClassName).after('<a href="#" class="fg-appended-html-sidebar sidebabar-link" data-id ="'+dataId+'" data-type="'+dataType+'" >' + innerhtml + '</a>');
            parentDiv.siblings('.' + divClassName).remove();
            
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
    addNewElement: function(new_element_template_level1, new_element_template_level2, new_element_template_level2_withfunction, path) {

        $(document).on('click', '.create_new_element', function(event) {
            var thisDataTarget = $(this).data("target");
            $(thisDataTarget).addClass("open show");
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
            //console.log(thisDataTarget);
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
                    //var functionCount = $(this).attr('data-fncount');
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

//                if (addrolefn) {
//                    var functionPlaceholder = $(this).attr('data-function-placeholder');
//                    $(thisDataTarget).find(".add-new-blk .dev-new-function").attr("name", "function_new_input_title");
//                    $(thisDataTarget).find(".add-new-blk .dev-new-function").attr("placeholder", functionPlaceholder);
//                    $(thisDataTarget).find(".add-new-blk .dev-new-role").attr("name", "role_new_input_title");
//                    $(thisDataTarget).find(".add-new-blk .dev-new-role").attr("placeholder", placeholder);
//                } else {
                    $(thisDataTarget).find(">ul>li > .add-new-blk .new-blk-input").attr("name", elementType + "_new_input_title");
                    $(thisDataTarget).find(">ul>li > .add-new-blk .new-blk-input").attr("placeholder", placeholder);
               // }
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

        $(document).on('click', '.add-new-blk .fa-check', function() {
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
                //var categoryId = $(this).siblings('input').attr('data-catid');
                var fnType = $(this).siblings('input').attr('function_type');
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
                Metronic.startPageLoading();
                $.getJSON(url, requestData, function(data) {
                    var parentDiv = thisVar.parent().parent().parent();
                    //if (FgSidebar.list) {
                    if (hierarchy == 1) {
                        var parentId = thisVar.closest("ul").parent('li').attr('id');
                        
                        if (typeof data.addToJson !== 'undefined') {
                            FgSidebar.appendNewElement(thisVar, data, parentId);
                        }
                        data.parent = FgSidebar.settings[parentId].parent;
                        data.settings = FgSidebar.settings[parentId].settingsLevel2;
                        if (FgSidebar.module == 'article') {
                            data.parentMenuId = data.parent.id;
                            html = FgSidebar.generateHtml(FgSidebar.templateLevel2, {'subMenu': data});  
                            if (thisVar.closest("ul").find('#CAT_li_WA')) {
                                $(html).insertBefore($('#CAT_li_WA'));
                            } else {
                                $(html).appendTo(thisVar.closest("ul"));
                            }
                            //This block is used for enabling drag-drop functionality and related icon handling in sidebar menu                        
                            var sidebarNewMenuid = '#'+data['input'][0]['id'];  
                            FgSidebar.sidebarNewmenuApplyragevent(sidebarNewMenuid);
                            //This block is used for enabling drag-drop functionality and related icon handling in sidebar menu
                        } else {
                            html = FgSidebar.generateHtml(FgSidebar.templateLevel1, {'data': data}); 
                            $(html).appendTo(thisVar.closest("ul"));
                        }
                        thisVar.closest("li").remove();
                    } else {
                        divClassName = 'fg-no-data-sidebar-sub';
                        var parentId = thisVar.closest("ul").parent('li').attr('id');
                        itemType = data.input[0]['dataType'];
                        
                        if (_.chain(jsonData[itemType]['entry']).where({"id": data.input[0]['categoryId']}).pluck("id").value() == '') {
                            var parentTitle = $('#' + parentId).find('> div > span.title').text();
                            jsonData[itemType]['entry'].push({'id': data.input[0]['categoryId'], 'title': parentTitle, 'input': data.input, 'type': 'select', 'show_filter': 1});
                            jsonData[itemType]['show_filter'] = 1;
                        } else {
                            var parentTitle = $('#' + parentId).find('> a > span.title').text();
                            if (_.find(jsonData[itemType]['entry'], function(item) {
                                return item.id == data.input[0]['categoryId']
                            }).input)
                                _.find(jsonData[itemType]['entry'], function(item) {
                                    return item.id == data.input[0]['categoryId']
                                }).input.push(data.input[0]);
                            _.find(jsonData[itemType]['entry'], function(item) {
                                return item.id == data.input[0]['categoryId']
                            }).show_filter = 1
                            jsonData[itemType]['show_filter'] = 1;
                        }
                        data.parentMenuId = parentId;
                        //filter.data().plugin_searchFilter.reCache(jsonData);
                        html = FgSidebar.generateHtml(FgSidebar.templateLevel2, {'subMenu': data});          
                        $(html).appendTo(thisVar.closest("ul"));
                        thisVar.closest("li").remove();   
                        //This block is used for enabling drag-drop functionality and related icon handling in sidebar menu                        
                        var sidebarNewMenuid = '#'+data['input'][0]['menuItemId'];  
                        FgSidebar.sidebarNewmenuApplyragevent(sidebarNewMenuid);
                        //This block is used for enabling drag-drop functionality and related icon handling in sidebar menu
                    }
                    
                    FgSidebar.handleArrows(parentDiv, divClassName);
                    thisVar.parent().parent(".subclass").remove();
                    
                    $('.' + elementType).val(0);
                    if(FgSidebar.frompage == 'gallery' && hierarchy == 1) {  //In gallery drag is active for parent menu also
                         //This block is used for enabling drag-drop functionality and related icon handling in sidebar menu
                        var sidebarNewMenuid = '#'+data['items'][0]['roleId']+'_li_'+data['items'][0]['id'];                          
                        FgSidebar.sidebarNewmenuApplyragevent(sidebarNewMenuid);
                        //This block is used for enabling drag-drop functionality and related icon handling in sidebar menu
                    } 
                    Metronic.stopPageLoading();
                });
            } else {
                $(this).prop('disabled', false);
            }
        });

        $(document).on('keypress', '.sidebar-create', function(event) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            if (keycode == '13') {// Enter key press
                $(this).parent().find('i.fa-check').trigger("click");
            } else if (keycode == '27') { // Esc key press
                $(this).parent().find('i.fa-times').trigger("click");
            }
        });
        $(document).on('click', '.add-new-blk .fa-times', function() {
            $(this).siblings('input').val('')
            var elementType = $(this).siblings('input').attr('element_type');
            $('.' + elementType).val(0);
            $(this).parents(".subclass").remove();
            activeMenu = '#' + ((FgSidebar.activeSubMenuVar) ? localStorage.getItem(FgSidebar.activeSubMenuVar) : localStorage.getItem("submenu"));
            $(activeMenu).addClass("show");
        });
    },
    //handleSidebarMenu
    handleSidebarMenu: function () {
       
        var viewport = Metronic.getViewPort();
        if(viewport.width >= 992){
            
            var tempHeight = viewport.height - 70;
            $('.side_main_menu').slimScroll({'height': tempHeight});
        }

        //$('.page-sidebar').off('click', 'li > a'); // prevent parent event bind
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

        // need to check
        jQuery('body').on('click', '.page-sidebar ul ul ul .sidebabar-link, ul ul .sidebabar-link', function (e) {
            e.preventDefault();
            $(".fa-filter").hide();
            var dataType = $(this).attr('data-type');
            var dataCategory = $(this).attr('data-categoryId');
            var dataId = $(this).attr('data-id');            
            $('.page-title > .page-title-text').text($(this).find('.title').text());
            Metronic.startPageLoading();
            FgSidebar.activeMenuData = {menuType: dataType, categoryId: dataCategory, id: dataId};
            if (FgSidebar.module == 'article') {
                if (preActive == 'ARCHIVE_li_ARCHIVE_ART') {
                    location.reload();
                }
//                sidebarActive = localStorage.getItem('activeSubMenuVar-'+clubId+'-'+contactId);
//                preActive = sidebarActive;
            }
            
            switch (dataType) {
                case 'ALLDOCUMENTS':
                    datatableOptions.ajaxparameters.menuType = dataType;
                    listTable.column(1).visible(true);
                    $('.fg-page-title-block-2').removeClass('fg-active-IB').addClass('fg-dis-none');
                    FgPageTitlebar.setMoreTab();
                    break;
                case 'NEW':    
                    datatableOptions.ajaxparameters.menuType = dataType;
                    if(FgSidebar.module == 'personalDocuments'){
                        listTable.column(1).visible(false);                        
                    }
                    $('.fg-page-title-block-2').removeClass('fg-dis-none').addClass('fg-active-IB');
                    FgPageTitlebar.setMoreTab();
                    break; 
                case 'CG':  
                case 'RG':  
                case 'ALL':  
                case 'ORPHAN':
                case 'EXTERNAL':    
                    var clickedElement = $(this).parent();
                    if(clickedElement.hasClass('subclass')){
                        //Sub album clicked
                        localStorage.setItem(FgLocalStorageNames.gallery.activeMenuVar, clickedElement.parents('li').attr('id'));
                        localStorage.setItem(FgLocalStorageNames.gallery.activeSubMenuVar,clickedElement.attr('id'));
                    } else {
                        $('#sidemenu_bar').find('.active').removeClass('active');
                        //Main album clicked
                        localStorage.setItem(FgLocalStorageNames.gallery.activeMenuVar, clickedElement.attr('id'));
                        localStorage.setItem(FgLocalStorageNames.gallery.activeSubMenuVar,'');
                    }
                    
            
                    localStorage.setItem(FgLocalStorageNames.gallery.selectedAlbum,dataId);
                    $('#gallery-container').html('').removeAttr('data-loaded-image-count');
                    FgGalleryView.loadGallery();  
                    Metronic.stopPageLoading();
                    break; 
                case 'ARCHIVE':
                    location.reload();
                break;
                case 'CMS_GEN':
                    datatableOptions.ajaxparameters.menuType = dataId;
                    FgCmsPage.showPageList();
                    FgCmsPageList.handleActionMenu('show');
                    FgDatatable.listdataTableInit('datatable-cms-page-list', datatableOptions);
                    break;
                case 'MM':
                    FgCmsPageList.handleActionMenu('hide');
                    var hasExlink = $(this).attr('data-externallink');  
                    var pageId = $(this).attr('data-pageid');
                    FgCmsPage.handleNavigationMenuClick(dataId, hasExlink, pageId);
                    Metronic.stopPageLoading();
                    if($(e.currentTarget).parent().hasClass('fg-sidebar-thirdlevel') || $(e.currentTarget).parent().hasClass('fg-sidebar-fourthlevel')){
                        $(e.currentTarget).next().toggleClass('open1');
                        if($(e.currentTarget).next().hasClass('open') || $(e.currentTarget).next().hasClass('open1')){
                            $(e.currentTarget).find('.arrow.pull-left').addClass('open1');
                        }else{
                            $(e.currentTarget).find('.arrow.pull-left').removeClass('open1');
                        }
                         
                        
                    }else{
                        $('.page-sidebar-menu ul ul').find('.fg-sidebar-fourthlevel.active').removeClass('active');
                    }
                    
                    break;
                default:
                    if (dataType == 'AREAS' && (dataId == 'CLUB' || dataId == 'WA')) {
                        $('#sidemenu_bar').find('.sub-menu .active').removeClass('active');
                        $(this).parent().addClass('article-level1-active');
                    }
                    datatableOptions.ajaxparameters.menuType = dataType;
                    datatableOptions.ajaxparameters.categoryId = dataCategory;
                    datatableOptions.ajaxparameters.subCategoryId = dataId;
                    if(FgSidebar.module == 'personalDocuments'){
                        listTable.column(1).visible(false);                        
                    }
                    $('.fg-page-title-block-2').removeClass('fg-active-IB').addClass('fg-dis-none');
                    FgPageTitlebar.setMoreTab();
                    break;
            }
            
            if (FgSidebar.isDataTable) {
                // Destroy datatable
                if(FgSidebar.module=='article'){
                    if (dataType != 'ARCHIVE' && preActive != 'ARCHIVE_li_ARCHIVE_ART' && !$.isEmptyObject(listTable)) {                        
                        listTable.destroy();                        
                    }
                }
                //Update count of selected sidebar menu in listing page
                var activeMenuCnt = ($(this).find(".badge").length > 0) ? $(this).find(".badge").text() : $("#fcount").text();
                $("#tcount").html(activeMenuCnt);
                //Reinitialize datatable 
                if(FgSidebar.module=='article'){
                    localStorage.removeItem(tableFilterStorageName);
                    FgArticleFilter.setFilter(dataType,dataId);
                    if (dataType != 'ARCHIVE' && preActive != 'ARCHIVE_li_ARCHIVE_ART') {
                        FgDatatable.listdataTableInit('datatable-internal-article', datatableOptions);
                    }
                } else {
                    FgDatatable.listdataTableInit('datatable-club-document', datatableOptions);
                }
                FgDatatable.datatableSearch();

                //remove the error and uploaded file contents
                if (typeof fgDocumentUploader !== 'undefined') {
                    fgDocumentUploader.removeFileContents();
                }
            }
        });
    },
    //droppableEventIconHandling
    droppableEventIconHandling: function (from) {   
        $("#sidemenu_bar li.fg-dev-draggable a").liveDroppable({
            hoverClass: "fg-sidebar-hover",
            drop: function (event, ui) {  
                FgSidebar.dragDropAssignmentProcessing(event, ui, $(this));
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
                if(FgSidebar.module !== 'article'){
                    $("#sidemenu_bar *").removeAttr("style");
                }
                
            },
            deactivate: function (e) {
                if(FgSidebar.module !== 'article'){
                    $("#sidemenu_bar *").removeAttr("style");
                }
            },
        });
    },
    
    //droppableEventIconHandling for gallery
    droppableEventIconHandlingForGallery: function (from) {     
        $("#sidemenu_bar li.fg-dev-draggable a").liveDroppable({
            hoverClass: "fg-sidebar-hover",
            drop: function (event, ui) {      
                FgSidebar.dragDropGalleryAssignment(event, ui, $(this).parent());
                $(".fg-dev-drag-active").removeClass('fg-dev-drag-active');
                $(this).siblings('a').removeClass('fg-sidebar-hover');
            },
            deactivate: function (e) {
                $(".fg-dev-drag-active").removeClass('fg-dev-drag-active');
                $(this).siblings('a').removeClass('fg-sidebar-hover');
            },
            over: function (event, ui) {                                
                $(this).siblings('a').addClass('fg-sidebar-hover');
            },
            out: function (event, ui) {   
                $(this).siblings('a').removeClass('fg-sidebar-hover');
            },
        });
        
        $("#sidemenu_bar li.fg-dev-draggable.subclass").liveDroppable({
            hoverClass: "fg-sidebar-hover",
            drop: function (event, ui) {                
                FgSidebar.dragDropGalleryAssignment(event, ui, $(this));
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
                    $(".fg-dev-drag-active").removeClass('fg-dev-drag-active');
            },
            deactivate: function (e) {                
                $(".fg-dev-drag-active").removeClass('fg-dev-drag-active');
            },
        });
    },
    
    //handle drag drop from gallery
    dragDropGalleryAssignment: function (event, ui, thisObj) {   
        //Dropped menu details
        var dataArr = thisObj.attr('id').split('_');
        var category_id = dataArr[0];
        var menu_id = dataArr.pop()
//        var category_id = thisObj.find('.sidebabar-link').attr('data-type');
//        var menu_id = thisObj.find('.sidebabar-link').attr('data-id');
        
        var checkedIds = '';
        var splitter = '';   
        $( "div.fg-gallery-img-wrapper.selected" ).each(function( index ) {
            if($(this).attr('data-itemid')) {
                checkedIds += splitter + $(this).attr('data-itemid');
                splitter = ','; 
            }
        });
        if(checkedIds == '') {
            //Dragged item details used in case of dragging only 1 item        
            checkedIds = ui.draggable.siblings().find('.fg-gallery-img-wrapper').attr('data-itemid');
        }
        
        var modalType = 'MOVETO_ALBUM';
        var albumName = $('.page-title-text').text().replace( /[\s\n\r]+/g, ' ' ); //gallery title with white spaces trimmed
        var params = { 'albumName': albumName, 'galleryId': category_id, 'albumId' : menu_id};          
        FgGalleryView.showConfirmationPopup(checkedIds, 'selected', modalType, params);
        return false;
    },
    
    //Common function to handle dragDropAssignment
    dragDropAssignmentProcessing: function (event, ui, thisObj) {
        //Initializing area
        var assignData = '';
        //var contextMenuSettings = '';
        var draggedDataArr = {};
        var draggedDataArr1 = {};
        var function_id = '';
        var selType = '';
        //Initializing area

        //Dragged item details used in case of dragging only 1 item        
        var itemId = ui.draggable.siblings().find('input').attr('id');
        draggedDataArr['id'] = itemId;
        
        draggedDataArr['subCategoryId'] = ui.draggable.siblings().find('input').attr('data-subCategoryid');
        draggedDataArr['itemName'] = ui.draggable.parents('tr').find('.fg-dev-docname').text();

        draggedDataArr1 = {
            '0': draggedDataArr
        };       
        $("#selectedIds").val(JSON.stringify(draggedDataArr1));
        //Dragged item details

        //Dropped menu details
        
        var category_id = thisObj.attr('data-categoryid');
        var menu_id = thisObj.attr('data-id');
        var assignmentText = (thisObj.find('span.title').html()).trim();console.log(assignmentText);
        var categoryTitle = (thisObj.parents().eq(2).find('a > span.title').html()).trim();        console.log(categoryTitle);        

        assignData = {
            'dropMenuId': menu_id,
            'dropCategoryId': category_id,
            'dropCategoryTitle': categoryTitle,
        };
        if (FgSidebar.module == 'cms') {
            var hasExLink = thisObj.attr('data-externallink');
            var pageId = thisObj.attr('data-pageid');
            if (hasExLink == 1) {
                FgCmsPage.showEditExternalLinkPopup(menu_id);
            } else if (pageId != '') {
                $('#fg-cms-existing-external-nav-id').val(menu_id);
                $('#fg-cms-existing-external-page-id').val(itemId);
                FgCmsPage.showAssignExistingExternalPagePopup('duplicate');
            } else {
                FgCmsPage.assignPageOnDrag(menu_id, itemId);
            }
        } else {
            FgSidebar.showAssignPopup({
                assignmentData: assignData,
                subcategoryName: assignmentText
            });
        }
    },
    /**
     * To handle move pop up area
     *  @param array assignmentArray
     */
    showAssignPopup: function(assignmentArray) {        
        var selDocNames = [];
        var selDocIds = [];
        var subCategoryId = [];
        selectedCat= assignmentArray['assignmentData']['dropCategoryId'];
        selectedSubcat = assignmentArray['assignmentData']['dropMenuId'];
        var type = (typeof docType !== 'undefined') ? docType.toLowerCase() : "";

        //get dragged items
        if ($("input.dataClass:visible:checked").length > 0) {  
            $("input.dataClass:visible:checked").each(function() {  
                var documentId = $(this).attr('id');
                if ( $.inArray(documentId, selDocIds) == -1) {                    
                    selDocIds.push(documentId);
                    subCategoryId.push({'id': $(this).attr('data-subcategoryid')});
                if(FgSidebar.module === 'article'){
                    selDocNames.push({'id': documentId, 'name': $(this).parents('tr').find('.fg-dev-article-title').text()});
                }else{
                    selDocNames.push({'id': documentId, 'name': $(this).parents('tr').find('.fg-dev-docname').text()});                                      
                }    
                   
                };                
            });
        } else {            
            var draggedItem  = $.parseJSON($("#selectedIds").val());
            selDocIds.push(draggedItem[0].id);
            subCategoryId.push({'id': draggedItem[0].subCategoryId});
            selDocNames.push({'id': draggedItem[0].id, 'name': draggedItem[0].itemName});  
        }
        //This condition only for Article listing page, Code has to be optimized.
        if(FgSidebar.module === 'article'){
            var articleIds = selDocIds.join(',');
            FgArticleManage.showArticleAssignPopup(articleIds, 'selected', { selectedId : selectedSubcat, type: selectedCat });
            //return;
        } else {

                    //grouping the subcategory id
                    var groupedSubcategory = _.groupBy(subCategoryId, 'id');

                    //dropdown -category and subcategory
                    $.getJSON(docCategoryDropdownPath,function(data){           
                       arrayRslt = FgInternal.groupByMulti(data.resultArray, ['id']);
                       catArray = new Array();
                       subcatArray = new Array();
                       $.each(arrayRslt, function(key,assignment) {
                        var catId = key;
                        if ((catId != null) && (catId != 'null')) {
                            var catTitle = assignment[0].title;
                            sortOrder = assignment[0].sortOrder;
                            catArray[sortOrder] = {'id': catId, 'title': catTitle};
                            } 

                            $.each(assignment,function(key,classs){
                                var subId = classs.subId;
                                if ((subId != null) && (subId != 'null')) {
                                        if (subcatArray[catId] == undefined) {
                                            subcatArray[catId] = {};
                                        }
                                        var classTitle = classs.titleSub;
                                        classOrder = classs.subSortOrder;
                                        subcatArray[catId][classOrder] = {'id': subId, 'title': classTitle};
                                }
                            }); 
                        });

                     displayDropdown(); 
                     $('#popup_contents input').uniform(); 
                     //disable if no subcategory selected
                      setTimeout(saveOption,1000);
                    });
        
        }   
        
        $(document).off('change', '#category_dropdown');
        $(document).on('change', '#category_dropdown', function() {
            selectedCat = $(this).val();
            selectedSubcat = '';
            displayDropdown();
            setTimeout(saveOption,1);
        });

         $(document).on('change', '#subcategory_dropdown', function() {
            selectedSubcat = $(this).val();
            displayDropdown();
            setTimeout(saveOption,1);
        });

        function displayDropdown(){
            renderTemplateContent('internal_display_dropdown', {'options':catArray , 'selectedId': selectedCat}, 'category_dropdown');
            var classOptions = subcatArray[selectedCat] ? subcatArray[selectedCat]: {};
            renderTemplateContent('internal_display_dropdown', {'options':classOptions , 'selectedId': selectedSubcat}, 'subcategory_dropdown');
            if (Object.keys(classOptions).length > 0) {
                $('div[data-id=show_class_section]').removeClass('hide');
            } 
            $('#popup_contents select').select2();
        }

        function renderTemplateContent(templateScriptId, jsonData, parentDivId) {  
            var template = $('#' + templateScriptId).html();     
            var htmlFinal = _.template(template, jsonData);            
            $('#' + parentDivId).html(htmlFinal);
        }

        $("#popup_contents").html($("#dummyPopupcontent").html());
        var singleArchiveTxt = $('#dummyPopupcontent .fg-dev-singleSelectionText').html();
        var multipleArchiveTxt = $('#dummyPopupcontent .fg-dev-multipleSelectionText').html();
        $('.fg-dev-multipleSelectionText').hide();
        $('.fg-dev-singleSelectionText').hide();
        //set pop up header text and its content
        if (selDocIds.length == 1) {
            popupHeadText = singleArchiveTxt.replace('%docname%', selDocNames[0].name);
            popupHeadText = popupHeadText.replace('%category%', assignmentArray['subcategoryName']);
            $('h4.modal-title').html('<div class="fg-popup-text" id="popup_head_text"></div>');
            $('#popup_contents #popup_head_text').text(popupHeadText + '...');
        } else {
            popupHeadText = multipleArchiveTxt.replace('%category%', assignmentArray['subcategoryName']);
            popupHeadText = popupHeadText.replace('%count%', selDocIds.length);
            var docNamesHtml = '';
            var i = 0;                       
            $.each(selDocNames, function(ckey, selDocName) {
                i++;
                if (i == 11) {
                    docNamesHtml += '<li>&hellip;</li>';
                    return false;
                } else {
                    var documentEditPath = (typeof docEditPath !=='undefined') ? docEditPath.replace("|documentId|", selDocName.id) : '';
                    docNamesHtml += '<li><a href="'+documentEditPath+'" target="_blank" data-cont-id="' + selDocName.id + '">' + selDocName.name + '</a></li>';
                }
            });
            $('#popup_contents h4.modal-title').html('<span class="fg-dev-contact-names"><a href="#" class="fg-plus-icon"></a><a href="#" class="fg-minus-icon"></a></span><div class="fg-popup-text" id="popup_head_text"></div>\n\
                        <div class="fg-arrow-sh"><ul>' + docNamesHtml + '</ul></div>');
            $('#popup_contents #popup_head_text').text(popupHeadText + '...');

        }

        $('#popup').modal('show');
        //bind click event to the +/- icon
        $(document).off('click', '.modal-title .fg-dev-contact-names');
        $(document).on('click', '.modal-title .fg-dev-contact-names', function(e) {
            $(this).parent().toggleClass('fg-arrowicon');
        });
        //bind click event to the button

        $(document).off('click', '.fg-dev-move');
        var updateArr = [];
        var dropMenuCount = 0;
        $(document).on('click', ".fg-dev-move", function() {
                       $('#popup').modal('hide');
            _.each(catArray, function(option) {
            if (option['id'] == selectedCat) { 
                assignmentArray['assignmentData']['dropCategoryTitle']= option['title'] ;
            }
         }); 
         _.each(subcatArray[selectedCat], function(option) {
            if (option['id'] == selectedSubcat) { 
                assignmentArray['subcategoryName']= option['title'] ;
            }
         }); 
         assignmentArray['assignmentData']['dropCategoryId'] = selectedCat;
         assignmentArray['assignmentData']['dropMenuId'] = selectedSubcat;

         var dropValues = assignmentArray['assignmentData']['dropCategoryTitle'] + ' - ' + assignmentArray['subcategoryName'];

            $.ajax({url: movePath,
                data: {'documentId': JSON.stringify(selDocIds), 'dropedCategory': assignmentArray['assignmentData']['dropCategoryId'], 'dropedSubCategory': assignmentArray['assignmentData']['dropMenuId'], 'dropValue': dropValues, 'docType':type},
                type: "post",
                success: function(data) {
                         FgInternal.showToastr(data.flash, 'success');
                    /* json array for sidebar count update */
                    updateArr.push({'categoryId' : assignmentArray['assignmentData']['dropCategoryId'], 'subCatId' : assignmentArray['assignmentData']['dropMenuId'], 'dataType' : docType, "sidebarCount" : selDocIds.length, "action" : "add"})
                    for(cnt = 0; cnt < subCategoryId.length; cnt++) {
                        updateArr.push({'categoryId' : "", 'subCatId' : subCategoryId[cnt].id, 'dataType' : docType, "sidebarCount" : 1, "action" : "remove"})                      
                    }
                    
                    FgCountUpdate.update('add', 'document', type, updateArr, selDocIds.length);
                    //datatable redraw
                    FgTeamDocuments.redrawList();
                }
            })
        });

        function saveOption() {
            if (selectedSubcat =='') {
                $('.fg-dev-move').attr('disabled', 'true');
            } else {
                $('.fg-dev-move').removeAttr('disabled');
            }
        }
        
    }
    
};

$(function () {    
    //sidebar
     $.fn.liveDroppable = function(opts) {
         //Used for enabling drag-drop functionality and related icon handling in sidebar menu
         if (opts['hoverClass'] === 'fg-sidebar-hover') {
             FgSidebar.newMenuOpts['draggable'] =  opts;
         }
         if (opts['hoverClass'] === 'fg-sidebar-not-allowed') {
             FgSidebar.newMenuOpts['nondraggable'] = opts;
         }
         if (!$(this).data("init")) {
             $(this).data("init", true).droppable(opts);
         } 
     };
});


//---- disable unwanted hover on dropping area -----------


$(window).resize(function(){
    var viewport = Metronic.getViewPort();
    if(viewport.width >= 992){
        var tempHeight = viewport.height - 70;
        $(".side_main_menu").slimScroll({destroy: true});
        $('.side_main_menu').slimScroll({'height': tempHeight});
    }else{
       
        $(".page-sidebar-menu").attr('style','');
    }
    });
