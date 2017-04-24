/**
 * Custom functions for cms page list
 */

//Wrapper function for create page
var FgCmsPage = {
    showCreatePagePopup: function (navId) {
        if (navId) {
            $('#cmsNavigation').val(navId);
        } else {
            $('#cmsNavigation').val('');
        }
        $('#fg-cms-page-create').modal('show');
    },
    createPageCallback: function (d) {
        var pageEditLink = pageEditPath.replace("***dummy***", d.pageId);
        window.location.href = pageEditLink;
    },
    hidePopupCallback: function () {
        $('.cms-page-title-input').val('');
        $('#cmsSidebarType').val('');
        $('#cmsSidebarArea').val('');
        $('.fg-side-col-wrapper li.fg-side-col-layout div.fg-side-col-layout-inner').removeClass('active');
        $('.fg-side-col-wrapper li.fg-side-col-layout:first div.fg-side-col-layout-inner').addClass('active');
        FgCmsPage.selectDefaultLangSwitch();
    },
    handleCreateButton: function () {
        if ($('#cmsPageTitle_' + defaultLang).val() === '') {
            $('#createPageBtn').attr('disabled', true);
        } else {
            $('#createPageBtn').attr('disabled', false);
        }
    },
    showAssignExistingExternalPagePopup: function (module) {
        var navId = $('#fg-cms-existing-external-nav-id').val();
        $.post(assignExistingPage, {'module': module, 'navId': navId, 'pageFlag': 'assign'}, function (response) {
            FgModelbox.showPopup(response);
            $('select#fg-cms-pages-list').selectpicker('fg-event-select');
        });
    },
    assignPageOnDrag: function (navId, pageId, pageType) {
        var moduleType = (pageType == 'page') ?  'duplicate' : pageType;
        FgXmlHttp.post(assignExistingPageSave, {'module': moduleType, 'navId': navId, 'pageId': pageId, 'pageFlag': 'assign'}, '', function (response) {
            var li = $('a.nav-link[data-id="'+ navId +'"]').parent('li').attr('id');
            FgCmsPageList.updateSidebarElements(li);
            Layout.fixContentHeight()
        });
    },
    intiValidate: function () {
        $('.fg-cms-external-existing-form').addClass('has-error');
        $('.fg-error-add-required').append('<span class=required>' + required + '</span>');
    },
    appendHttp: function (fieldName) {
        var urlVal = $(fieldName).val();
        if ((urlVal != '') && (!urlVal.match(/^[a-zA-Z]+:\/\//))) {
            urlVal = 'http://' + urlVal;
            $(fieldName).val(urlVal);
        }
    },
    showEditExternalLinkPopup: function (navId) {
        $.post(assignExistingPage, {'module': 'editExternal', 'navId': navId, 'pageFlag': 'assign'}, function (response) {
            FgModelbox.showPopup(response);
        });
    },
    showUnAssignPopup: function (checkedIds) {
        var pageDetails = FgCmsPageList.getPageDetails(checkedIds);
        $.post(assignExistingPage, {'checkedIds': checkedIds, 'pageDetails': pageDetails, 'pageFlag': 'unAssign'}, function (response) {
            FgModelbox.showPopup(response);
        });
    },
    //Lang Switch
    handleLangSwitch: function () {

        $(document).off('click', 'button[data-elem-function=switch_lang]');
        /* function to show data in different languages on switching language */
        $(document).on('click', 'button[data-elem-function=switch_lang]', function () {

            selectedLang = $(this).attr('data-selected-lang');
            $('button.btlang').removeClass('active');
            $(this).addClass('active');
            FgUtility.showTranslation(selectedLang);
        });
    },
    selectDefaultLangSwitch: function () {
        $('button.btlang').removeClass('active');
        $('[data-selected-lang="' + defaultLang + '"]').addClass('active');
        FgUtility.showTranslation(defaultLang);
    },
    assignPageAndExLinkSaveCallback: function (response) {
        if (response.module == 'external') {
            FgCmsPageList.updateSidebarElements('li_PAGES_all_pages');
        } else {
            var li = $('a.nav-link[data-id="'+ response.navId +'"]').parent('li').attr('id');
            FgCmsPageList.updateSidebarElements(li);
        }
    },
    unassignPageCallback: function () {
        FgCmsPageList.updateSidebarElements();
        FgCmsPage.redrawDatatable();
    },
    hideAllWrappersDivs: function () {
        $('.fg-cms-create-page-wrapper').addClass('hide');
        $('.fg-cms-create-page-preview-wrapper').addClass('hide');
        $('.fg-cms-special-page-wrapper').addClass('hide');
        $('.fg-cms-page-list').addClass('hide');
    }, 
    reInitPageTitleBar: function(options) {
        FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar(options);
    },
    updatePageTitle: function(dataId) {
        var title = FgCmsPageList.createNavigation(dataId, ' >> ');
        $('.page-title > .page-title-text').text(jsonData['MM']['title'] + ' >> ' + title);
    },
    handleNavigationMenuClick: function (dataId, hasExlink, pageId, pageType) { 
        $('#hidPageId').val(pageId);
        $('#hidPageType').val(pageType);
        $('#hidNavId').val(dataId);
        var activeTab = $('#paneltab li.active >a').attr("data_id");
        FgCmsPage.hideAllWrappersDivs(); 
        pageID = pageId;
        currPageType = pageType;
        $('.fg-cms-create-page-preview-wrapper').html('');
        if (hasExlink == 1) {
            //FgCmsPage.updatePageTitle(dataId);
            FgCmsPage.reInitPageTitleBar({title: true});            
            FgCmsPage.showEditExternalLinkPopup(dataId);
        } else if (pageId != '') {
          if (pageType != 'page') {
              var activeTab = $('#paneltab li.active >a').attr("data_id");
              if(activeTab=='cmsTabContent'){
                  CmsSpecialPage.renderSpecialPage(pageId, pageType,'content');  
              }else{
                 CmsSpecialPage.renderSpecialPage(pageId, pageType);   
              }
               
          } else {
              
                $('#fg_tab_cmsTabContent span.fg-dev-tab-text').text(CmsTrans.content);
                FgCmsPage.reInitPageTitleBar({title: true}); 
              //  FgCmsPageList.callPagePreview(pageId,'');
                $('.fg-cms-create-page-preview-wrapper').removeClass('hide');
                $('#paneltab li').removeClass('active');
                $('#fg_tab_cmsTabPreview').addClass('active');
                $('#fg_tab_cmsTabContent').removeClass('active');
                CmsSpecialPage.redrawPagetitleBar();
                CmsSpecialPage.renderPreview(pageId, pageType);
                
            }
        } else {
            FgCmsPage.reInitPageTitleBar({title: true});
            $('.fg-cms-create-page-wrapper').removeClass('hide');
            $('#fg-cms-existing-external-nav-id').val(dataId);
        }
    },
    
    showPageList: function () {
        $('.fg-action-search').removeClass('hide');
        $('.fg-cms-page-list').removeClass('hide');
        $('.fg-cms-create-page-preview-wrapper').addClass('hide');
        $('.fg-cms-create-page-wrapper').addClass('hide');
    },
    multiCheckHandle: function () {
        if ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length <= 1) {
            $(".dataTable tr .fg-sort").draggable('enable');
        } else if ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length > 1) {
            $(".dataTable tr .fg-sort").draggable('disable');
        }
    },
    redrawDatatable: function () {
        var menuType = $('#'+ FgSidebar.getActiveMenu() + ' > .nav-link:not(.non-clickable)').attr('data-type');
        if (menuType == 'PAGES') {
            FgCmsPage.reInitPageList($('#'+ FgSidebar.getActiveMenu() + ' > .nav-link:not(.non-clickable)').attr('data-id'));
            FgCmsPage.reInitPageTitleBar({ actionMenu: ((adminFlag) ? true : false), title: true, search: true});
        }
    },
    reInitPageList: function (menuType) {
        var datatableSettings = FgDatatable.getSettings();
        datatableSettings.ajaxparameters.menuType = menuType;
        FgDatatable.setNewValues(datatableSettings);
        pageListDatatableObj.ajax.reload();
    },
    dirtyInitExternalEdit: function () {
        FgDirtyFields.init('fg-cms-edit-existing-form', {
            saveChangeSelector: '#editExternalPageBtn',
            enableUnsaveFormAlert: false,
            discardChangesCallback: function () {
            }});
    }
};

//Wrapper function for CMS page list datatable
var FgCmsPageList = {
    init: function () {
        if ($.isEmptyObject(pageListDatatableObj)) {
            FgCmsPageList.dataTableOpt();
            pageListDatatableObj = FgDatatable.listdataTableInit('datatable-cms-page-list', datatableOptions);
            FgDatatable.datatableSearch();
        } else {
            pageListDatatableObj.ajax.reload();
        }
        $('.fg-cms-page-list').removeClass('hide');  
    },
    dataTableOpt: function () {
        var i = 0;
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        if (hasSidebar) {
                columnDefs.push({type: "checkbox", width: '1%', orderable: false, sortable: false, className: 'fg-checkbox-th', targets: i++, data: function (row, type, val, meta) {
                        if (row['pageId'] !== null) {
                            var dragArrow = '<i class="fa fg-sort ui-draggable"></i>';
                            var titleInputHidden = '<input type="hidden" data-pagetype="'+row['pageType']+'" id="page_title_' + row['pageId'] + '" value="' + row['title'] + '"  data-element-count="' + row['ElementCount'] + '">';
                            var content = dragArrow + '<input class="dataClass fg-dev-avoidicon-behaviour" data-pagetype="'+row['pageType']+'" type="checkbox" id=' + row['pageId'] + ' name="check"  value="0"  >' + titleInputHidden;
                            row.sortData = '';
                            row.displayData = content;
                            return row;
                        }
                    }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
            }
        columnDefs.push({"name": "title", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row['title'] = _.escape(row['title']);
                var editIcons = '&nbsp;<a  onClick = "FgCmsPageList.editPage('+row['pageId']+',\''+row['pageType']+'\');" data-pageId="'+row['pageId']+'" data-pageType="'+row['pageType']+'" data-type="content" class="fg-tableimg-hide fg-edit-contact-ico"><i class="fa fa-pencil-square-o fg-pencil-square-o"></i></a>';
                row.sortData = row['title'];
                row.displayData = '<a onClick = "FgCmsPageList.showSpecialPage(this);" data-nav="'+row['navIds']+'"  data-pageId="'+row['pageId']+'" data-pageType="'+row['pageType']+'" data-type="preview">' + row['title'] + '</a>' + editIcons;
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
        
        columnDefs.push({"name": "navigation", type: 'null-last', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['navTitle'].toLowerCase();
                row.displayData = row['navTitle'];
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
        
        columnDefs.push({"name": "elements",  type: "null-last", "targets": i++, data: function (row, type, val, meta) {
                var element;
                if(row['pageType'] === 'page'){
                    element = FgCmsPageList.getElementIcons(row['elementTypes']);
                }else{
                    element = pageTypeTrans[row['pageType']];
                }
                row.sortData = (element === '') ? '' : parseInt(row['ElementCount']);
                row.displayData = element;
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
        
        columnDefs.push({"name": "sidecolumn", "type":"null-last","targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['sidebarType'];
                var pageLink = (row['sidebarType'] !='') ? editLink.replace("***dummy***", row['sidebarId']):'';
                row.displayData = (row['sidebarType'] !=null && row['sidebarType']!="") ? ((meta.settings.json.isCmsAdmin!= 0) ? "<a href='"+ pageLink +"'>"+CmsTrans[row['sidebarType']]+"</a>" : CmsTrans[row['sidebarType']]) :'-';
                return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
        if (hasSidebar) {
            columnDefs.push({"name": "pageadmins", "targets": i++, data: function (row, type, val, meta) {
                    row['pageAdmin'] = row['pageAdmin'] != null ? row['pageAdmin'] : '-';
                    if (row['pageAdmin'] != '-') {
                        var arr = row['pageAdmin'].split("*##*");
                        for (var i = 0; i < arr.length; i++) {
                            arr[i] = (typeof pageAdmins[arr[i]] != "undefined") ? pageAdmins[arr[i]] : arr[i];
                        }
                        row['pageAdmin'] = (arr.length > 0)? arr.join('*##*').replace(/['"]+/g, '') :'-';
                        row['displayData'] = FgInternal.createPopover(row['pageAdmin'], pageAdminTrans);
                        row['sortData'] = row['pageAdmin'].split("*##*").length;
                    } else {
                        row['displayData'] = '-';
                        row['sortData'] = 0;
                    }
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
        }
        columnDefs.push({"name": "last_edited", "type": "moment-" + currentDateFormat, "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['lastEdited'];
                row.displayData = row['lastEdited'];
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "edited_by", "targets": i++, data: function (row, type, val, meta) {
                var profileLink = profilePath.replace("***dummy***", row['activeContactId']);
                row.sortData = row['editedBy'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['editedBy'] + '</a></div>' : '<span class="fg-table-reply">' + row['editedBy'] + '</span>';
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});

        var initialsortingColumn = hasSidebar ? '1' : '0';
        var fixedcolumnCnt = hasSidebar ? '2' : '1';
        datatableOptions = {
            columnDefFlag: true,
            fixedcolumn:true,
            fixedcolumnCount:2,
            ajaxPath: pageListPath,
            ajaxparameterflag: true,             
            ajaxparameters: {
                menuType: 'all_pages'
            },
            columnDefValues: columnDefs,
            serverSideprocess: false,
            displaylengthflag: true,
            draggableFlag: true,
            popupFlag: true,
            initialSortingFlag: true,
            initialsortingColumn: initialsortingColumn,
            initialSortingorder: 'asc',
            rowlengthWrapperdivid: 'fg_dev_memberlist_row_length',
            tableFilterStorageName: 'tableFilterStorageName',
            manipulationFlag: true,
            manipulationFunction: 'manipulateCMSPageList',
            module: 'CMS'
           
        };
    },
    getElementIcons : function(elTypes) {
        if(typeof elTypes === 'undefined' || elTypes === null){
            return '';
        }
        var elTypeArray = elTypes.split('|&&&|');
        var iconString = '';
        for(i=0; i<elTypeArray.length; i++){
            var typeObject = _.findWhere(headerDetails, {id: parseInt(elTypeArray[i])});
            iconString += typeObject.logo;
        }

        return iconString;
    },
    editPage: function (pageId,type) {
        var typePg = $('#' + pageId).attr('data-pagetype');
        var navUrl = $('a.nav-link[data-pageid="'+ pageId +'"]').attr("data-id")
        var navUrl = (typeof navUrl == 'undefined')?'':navUrl;
        type = (typeof typePg == 'undefined')?type:typePg;
        if (type == 'gallery' || type == 'article' || type == 'calendar'){
            if(navUrl!=''&&hasSidebar){
                 $('#paneltab li').removeClass('active');
                 $('#fg_tab_cmsTabContent').addClass('active');
                var li = $('a.nav-link[data-pageid="' + pageId + '"]').parent('li').attr('id');
                    if (!_.isUndefined(li)) {
                      FgSidebar.handleSidebarClick(li);
                    }else{
                        CmsSpecialPage.renderSpecialPage(pageId, type, 'content');  
                    }
            }else{
                CmsSpecialPage.renderSpecialPage(pageId, type, 'content');  
            }
            
        } else{
            window.location = pageEditPath.replace('***dummy***', pageId);
        }
            
        
    },
    deletePagePopup: function (checkedIds) {
        var pageDetails = FgCmsPageList.getPageDetails(checkedIds);
        $.post(pageDeletePopupPath, {'pageArray': pageDetails}, function (data) {
            FgModelbox.showPopup(data);
        });
    },
    deletePage: function (pageDetails) {
        FgXmlHttp.post(pageDeletePath, {'pageDetails': pageDetails}, false, FgCmsPageList.deletePageCallBack);
        FgModelbox.hidePopup();
    },
    getPageDetails: function (checkedIds) {
        var pageIds = checkedIds.split(",");
        var pageArray = [];
        var title = '';
        var elementCount = '';
        var type = 'page';
        if (pageIds.length > 0) {
            _.each(pageIds, function (id) {
                title = $('#page_title_' + id).val();
                elementCount = $('#page_title_' + id).attr('data-element-count');
                type = $('#page_title_' + id).attr('data-pagetype');
                pageArray.push({'id': id, 'title': title, 'elementCount': elementCount,'type':type});
            });
        }
        return pageArray;
    },
    deletePageCallBack: function () {
        FgCmsPageList.updateSidebarElements();
        FgCmsPage.redrawDatatable();
    },
    handleActionMenu:function(flag){
        if(flag === 'show' ){
            $('.fg-action-menu-wrapper .fg-action-menu').removeClass('hide');
        }
        if(flag === 'hide'){
            $('.fg-action-menu-wrapper .fg-action-menu').addClass('hide');
        }
    },

    createNavigation: function (ids, seperator) {
        textSeperator = (typeof seperator !== 'undefined') ? seperator : ' > ';
        navIdArray = _.uniq(ids.split(','));
        var title = [];
        $.each(navIdArray, function (i, v) {
            navData = [];
            FgCmsPageList.getNavigation(v);
            navData.reverse();
            title.push(navData.join(textSeperator));
        });
        return title.join('<br>');
    },
    getNavigation: function (id) {
        var navig = _.filter(cmsData.navDetails, function(ob){ return ob.id == id; });
        var title = navig[0]['title'];
        var parentId = navig[0]['parentId'];
        if (parseInt(parentId) > 1) {
            navData.push(title);
            FgCmsPageList.getNavigation(parentId);
        } else {
            navData.push(title);
        }

    },
    callPagePreview: function (pageId,tab) {
        var iframeUrl = pagePreviewpath.replace('|IFRPAGEID|', pageId);
        var htmlIframe = "<div class='fg-cms-page-iframe-wrapper'><iframe class='lockframe' id='pagePreviewIframe' allowTransparency='true'  ></iframe></div>";
        $('.fg-cms-create-page-preview-wrapper').html(htmlIframe);
          $('.lockframe').contents().find("body").css('overflow','hidden');
        //$('#pagePreviewIframe').load(iframeUrl);
         $("#pagePreviewIframe").attr("src",iframeUrl);
         $('.lockframe').load(function() {
                     
         $('.lockframe').contents().find("body").css({
             'pointer-events':'none',
             
         })
         $('.lockframe').contents().find("body *").css({
             'pointer-events':'none',
             
         })
            
          });
          

    },
    callEditPreview: function (pageId,tab) {
       window.location = pathEditPreview.replace('***dummy***', pageID);

    },
    initSidebar: function () {
        var options = {
            clubId : clubId,
            jsonData: true, 
            module: 'CMS',
            defaultLevelSettings: {
                level1: {
                    settingsTemplateData : {
                        settingsMenuUrl : CmsTrans.menuSettingsPath, 
                        settingsMenuTitle : CmsTrans.menuSettings
                    }
                },
                level2: {
                    countSettings: {
                        showCount: 'NO'
                    },
                    showIcon: true,
                    showToggleMenu:true,
                },
                level3: {
                    countSettings: {
                        showCount: 'NO'
                    },
                    showIcon: true,
                    showToggleMenu:true,
                },
                level4: {
                    countSettings: {
                        showCount: 'NO'
                    },
                    showIcon: true
                },
                optionMenu: false
            },
            PAGES: {
                level1: {
                    settingsMenu: false
                },
                level2: {
                    showIcon: false,
                    isDroppable: false,
                    countSettings: {
                        showCount: 'YES'
                    },
                },
            },
            ADDITIONAL:{
                level1: {
                    settingsMenu: true,
                    settingsTemplateData : {
                        settingsMenuUrl : CmsTrans.addmenusettingPath, 
                        settingsMenuTitle : CmsTrans.addmenuSettings
                    }
                },
                
            },
            defaultMenuDetails: {menu: 'li_PAGES', subMenu : 'li_PAGES_all_pages'},
            sideClickCallback :function () {
                var dataType = $('#'+this+'> .nav-link').attr('data-type');
                var dataId = $('#'+this+'> .nav-link').attr('data-id'); 
                $('.fg-cms-special-page-wrapper').html(''); // to remove special page div
                switch (dataType) {
                    case 'PAGES':
                        FgCmsPage.hideAllWrappersDivs();
                        if ($.isEmptyObject(pageListDatatableObj)) {
                            FgCmsPageList.dataTableOpt();
                            datatableOptions.ajaxparameters.menuType = dataId;
                            pageListDatatableObj = FgDatatable.listdataTableInit('datatable-cms-page-list', datatableOptions);
                            FgDatatable.datatableSearch();
                        } else {
                            FgCmsPage.reInitPageList(dataId);
                        }
                        FgCmsPage.reInitPageTitleBar({ actionMenu: ((adminFlag) ? true : false), title: true, search: true});
                        $('.fg-cms-page-list').removeClass('hide');
                        break;
                    case 'MM':
                        var hasExlink = $('#'+this+'> .nav-link').attr('data-externallink');  
                        var pageId = $('#'+this+'> .nav-link').attr('data-pageid');
                        var pageType = $('#'+this+'> .nav-link').attr('data-pagetype');
                        var activeTab = $('#paneltab li.active').attr("id");
                        FgCmsPage.handleNavigationMenuClick(dataId, hasExlink, pageId, pageType);
                        break;
                    default:
                        break;
                }
            },
            sideDroppedCallback:function (ui) {
                var itemId = ui.draggable.siblings().find('input').attr('id');
                var pageType = ui.draggable.siblings().find('input').attr('data-pagetype');
                var menuId = $(this.target).attr('data-id'); 
                var hasExLink = $(this.target).attr('data-externallink');
                var pageId = $(this.target).attr('data-pageid');
                if (hasExLink == 1) {
                    FgCmsPage.showEditExternalLinkPopup(menuId);
                } else if (pageId != '') {
                    $('#fg-cms-existing-external-nav-id').val(menuId);
                    $('#fg-cms-existing-external-page-id').val(itemId);
                    var moduleType = (pageType == 'page') ?  'duplicate' : pageType;
                    FgCmsPage.showAssignExistingExternalPagePopup(moduleType);
                } else {
                    FgCmsPage.assignPageOnDrag(menuId, itemId, pageType);
                }
            }
            
        };
        FgSidebar.init(options, jsonData);
    },
    updateSidebarElements: function (li) {
        /* Get json data for updating sidebar count and icons */
        $.getJSON(cmsSidebarParams.updateSidebarPath, {}, function (menudata) {
            _.each(menudata, function (data, key) {
                switch (key) {
                    case 'all_pages':
                    case 'pages_without_navigation':
                        $('#sidemenu_bar a[data-id="' + key + '"]').children('span.badge-round').text(data);
                        break;
                    default:
                        var iconClass = 'fa fa-2x fg-sidebar-icon-right ';
                        switch (data.pageType) {
                            case 'calendar':
                                iconClass += 'fa-calendar';
                                break;
                            case 'article':
                                iconClass += 'fa-newspaper-o';
                                break;
                            case 'gallery':
                                iconClass += 'fa-picture-o';
                                break;
                            case 'page':
                                iconClass += 'fa-dot-circle-o';
                                break;
                            default:
                                iconClass += (data.hasExLink === '1') ? 'fa-globe' : 'fa-circle-o';
                                break;
                        }
                        var pageid = (data.pageId !== null) ? data.pageId : '';
                        $('#sidemenu_bar a[data-id="' + data.id + '"]').attr('data-pageid', pageid);
                        $('#sidemenu_bar a[data-id="' + data.id + '"]').attr('data-externallink', data.hasExLink);
                        $('#sidemenu_bar a[data-id="' + data.id + '"]').attr('data-pagetype', data.pageType);
                        $('#sidemenu_bar a[data-id="' + data.id + '"]').attr('data-pagetitle', data.pageTitle);
                        $('#sidemenu_bar a[data-id="' + data.id + '"]').children('i.fg-sidebar-icon-right').removeAttr('class').addClass(iconClass);
                        break;
                }
            });
            if (!_.isUndefined(li)) {
                FgSidebar.handleSidebarClick(li);
            }
        });
    },
    showSpecialPage: function(el){
        var type = $(el).attr('data-type');
        var pageId = $(el).attr('data-pageid');
        var pageType = $(el).attr('data-pageType');
        var navUrl = $(el).attr('data-nav');
        $('#hidPageType').val(pageType);
        $('#hidPageId').val(pageId);
        $('#hidNavId').val(navUrl); 
        renderPageType = currPageType = pageType;
        pageID = pageId;
        if(pageType=='page'){
          var contentText = tabheadingArray.cmsTabContent.text;
          $('#fg_tab_cmsTabContent .fg-dev-tab-text').text(contentText);  
        }
        if(type === 'content'){
            CmsSpecialPage.renderSpecialPage(pageId, pageType, 'content',navUrl);
        }else{
            $('#paneltab li').removeClass('active');
            $('#fg_tab_cmsTabPreview').addClass('active');
                
            if(navUrl!=''&&hasSidebar){
                var li = $('a.nav-link[data-pageid="' + pageId + '"]').parent('li').attr('id');
                if (!_.isUndefined(li)) {
                    FgSidebar.handleSidebarClick(li);
                }else{
                    if(pageType=='page' && navUrl==''){
                        FgCmsPageList.callEditPreview(pageID);
                    }else{
                        CmsSpecialPage.renderSpecialPage(pageId, pageType, '',navUrl);
                    }
                }
               // CmsSpecialPage.renderSpecialPage(pageId, pageType, '',navUrl);
            }else if(pageType=='page' && navUrl==''){
                  FgCmsPageList.callEditPreview(pageID);
            }else{
                CmsSpecialPage.renderSpecialPage(pageId, pageType, '',navUrl);
            }
        }
    },
    showActionPagePreview: function(){
       
       var pageId = $('#hidPageId').val();
       if($('a.nav-link[data-pageid="' + pageId + '"]').parent('li').length >0){
            if($('a.nav-link[data-pageid="' + pageId + '"]').parent('li').attr('id').length>0){
                var navUrl = $('a.nav-link[data-pageid="' + pageId + '"]').attr('data-id'); 
                var type = $('a.nav-link[data-pageid="' + pageId + '"]').attr('data-pagetype'); 
                $('#hidNavId').val(navUrl); 
                 $('#hidPageType').val(type);
            }
         }else{
             var type = $('#hidPageType').val();
             navUrl = $('#hidNavId').val();
             pageID = $('#hidPageId').val();
         }
        pageType = type;
      
         if (typeof navUrl !== 'undefined') {    
           navUrl = navUrl; 
        }else{
           navUrl = ''; 
        }
     
        if(navUrl!=''&&hasSidebar){
            
            var li = $('a.nav-link[data-pageid="' + pageId + '"]').parent('li').attr('id');
            if (!_.isUndefined(li)) {
                FgSidebar.handleSidebarClick(li);
            }
        }else if(pageType=='page' && navUrl==''){
              FgCmsPageList.callEditPreview(pageID);
        }else{
         
            CmsSpecialPage.renderSpecialPage(pageId, pageType, '',navUrl);
        }
        
       

    }

};

//document ready
$(document).ready(function () {
    scope = angular.element($("#BaseController")).scope();
    //Init actionmenu
    if (adminFlag) {
        window.actionMenuTextDraft = {'active': {'none': actionMenu.none, 'single': actionMenu.single, 'multiple': actionMenu.multiple}};
        scope.$apply(function () {
            scope.menuContent = window.actionMenuTextDraft;
        });
    }
    //handle create button ,It is disabled when page title is null
    FgCmsPage.handleLangSwitch();
    FgInternal.toolTipInit();

    //Click event for submit button
    $(document).off('click', '#createPageBtn');
    $(document).on('click', '#createPageBtn', function () {

        if ($.trim($('#cmsPageTitle_' + defaultLang).val()) == '') {
            $('#pagename-formgroup-error').removeClass('hide');
            $('#cms-pagename-formgroup').addClass('has-error');
            $("#pagename-formgroup-error").html(fieldRequiredMessage);
        } else {
            $('#cms-pagename-formgroup').removeClass('has-error');
            $('#pagename-formgroup-error').addClass('hide');
            var data = $('#cms_create_page_form').serializeArray();
            FgXmlHttp.post(createPagePath, data, false, FgCmsPage.createPageCallback);
            $(document).off('click', '#createPageBtn');
        }

    });

    //Click event for side column selection.
    $('.fg-side-col-wrapper li.fg-side-col-layout').click(function () {

        $('.fg-side-col-wrapper li.fg-side-col-layout div.fg-side-col-layout-inner').removeClass('active');
        $(this).find('.fg-side-col-layout-inner').addClass('active');
        $('#cmsSidebarType').val($(this).attr('data-sbType'));
        $('#cmsSidebarArea').val($(this).attr('data-sbArea'));

    });

    //Callbak function for modalbox hide
    $('#fg-cms-page-create').on('hidden.bs.modal', function (e) {
        FgCmsPage.hidePopupCallback();
        $('#cms-pagename-formgroup').removeClass('has-error');
        $('#pagename-formgroup-error').addClass('hide');
        //FgCmsPage.handleCreateButton();
    });

    //keyup event when changing page name
    //$('#cmsPageTitle_' + defaultLang).keyup(function () {
    // FgCmsPage.handleCreateButton();
    // });

    //Show createpage popup whwn clicking on link
    $(document).on('click', '#fg-cms-create-link', function () {
        var navId = $('#fg-cms-existing-external-nav-id').val();
        //cmsNavigation
        FgCmsPage.showCreatePagePopup(navId);
    });

    //Show assign existing page popup
    $(document).on('click', '#fg-cms-assign-existing-link', function () {
        FgCmsPage.showAssignExistingExternalPagePopup('existing');
    });

    //Show assign external page popup
    $(document).on('click', '#fg-cms-assign-external-link', function () {
        FgCmsPage.showAssignExistingExternalPagePopup('external');
    });

    //handle multi check
    $(document).on('click', 'input.dataClass', function () {
        FgCmsPage.multiCheckHandle();
    });

    //handle multi check all
    $(document).on('click', 'input.dataTable_checkall', function () {
        FgCmsPage.multiCheckHandle();
    });
});

(function ($) {
    $.fn.dataTable.moment = function (format, locale) {
        var types = $.fn.dataTable.ext.type;
        // Add type detection
        types.detect.unshift(function (d) {
            // Null and empty values are acceptable
            if (d === '' || d === null) {
                return 'moment-' + format;
            }

            return moment(d, format, locale, true).isValid() ?
                    'moment-' + format :
                    null;
        });
        // Add sorting method - use an integer for the sorting
        types.order[ 'moment-' + format + '-pre' ] = function (d) {
            return d === '' || d === null ?
                    -Infinity :
                    parseInt(moment(d, format, locale, true).format('x'), 10);
        };
    };

    $.fn.dataTable.moment(FgLocaleSettingsData.momentDateTimeFormat);

}(jQuery));