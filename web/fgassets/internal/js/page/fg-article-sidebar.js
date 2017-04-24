FgArticleSidebar = {
    initSidebar: function () {
        var options = {
            clubId: clubId,
            jsonData: true,
            module: 'ARTICLE',
            defaultLevelSettings: {
                level1: {
                    countSettings: {
                        showCount: 'AJAX',
                        ajaxCountUrl: articleSidebarCountPath,
                    },
                },
                level2: {
                    templateId: "sidebarLevel2CommonTemplate",
                    
                },
                level3: {
                    templateId: "sidebarLevel3CommonTemplate",
                }
            },
            CAT: {
                level1: {
                    settingsMenu: true,
                    settingsTemplateId: "sidebarSettingsArticleCatTemplate",
                    settingsTemplateData: {sideContentType: 'CAT', createTitle: ArticleTrans.CREATE_CATEGORY, settingsMenuTitle: ArticleTrans.CATEGORY_SETTINGS, settingsMenuUrl: ArticleParams.catSettingPath, categoryId: 'CAT'}
                },
               
            },
            GEN:{
                level2: {isDroppable:false, }
            },
            ARCHIVE:{
                  level2: {isDroppable:false, }  
            },
            WC:{
                level2: {
                     navItemCustomClass:'hide'  ,
                     isDroppable:false,
                     isTooltip:true,
                }
            },
            TEAM: {
                level2: {
                    isClickable: false,
                     isDroppable:false,
                    countSettings: {
                        showCount: 'NO',
                    },
                },
                level3: {
                    showCount: 'YES',
                },
            },
            WG: {
                level2: {
                    isClickable: false,
                     isDroppable:false,
                    countSettings: {
                        showCount: 'NO',
                    },
                },
                level3: {
                    showCount: 'YES',
                },
            },
            WA:{
             level2: { navItemCustomClass:'hide',
                        isDroppable:false,
                        isTooltip:true,
                    },
             
            },
            TIME: {
                level1: {
                    settingsMenu: true,
                    settingsTemplateId: "sidebarSettingsArticleTimeTemplate",
                    settingsTemplateData: {settingsMenuTitle: ArticleTrans.CREATE_TIMEPERIOD, }
                },
                 level2: {isDroppable:false, }  
            },
            defaultMenuDetails: {menu: 'li_GEN', subMenu: 'li_GEN_AEA'},
            sideClickCallback: function () {
                var dataType = $('#' + this + '> .nav-link').attr('data-type');
                var dataId = $('#' + this + '> .nav-link').attr('data-id');
                if (!$.isEmptyObject(listTable)) {
                    listTable.destroy();
                    $('#datatable-internal-article').empty();
                }
                if (dataType == 'ARCHIVE') {
                    FgEditorialList.dataTableOpt('ARCHIVE');
                    FgArticleFilter.setFilter(dataType, dataId);
                    FgDatatable.listdataTableInit('datatable-internal-article', datatableOptionsArchive);

                } else {
                    FgEditorialList.dataTableOpt('EDITORIAL');
                    datatableOptionsArticle.ajaxparameters.menuType = dataType;
                    datatableOptionsArticle.ajaxparameters.subCategoryId = dataId;
                    FgArticleSidebar.updateCategoryFilter(this, dataId,dataType);
                    FgArticleFilter.setFilter(dataType, dataId);
                    FgDatatable.listdataTableInit('datatable-internal-article', datatableOptionsArticle);
                }
                FgArticleSidebar.setPageTitleCount();


            },
            initCompleteCallback: function () {
                 
                $(function () {
                    scope = angular.element($("#BaseController")).scope();
                   FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({
                        actionMenu: true,
                        title: true,
                        counter: true,
                        searchFilter: true,
                        search: true
                    });
                    
                    FgArticleSidebar.setPageTitleCount();
                    FgActionmenuhandler.init();
                    FgArticleFilter.init();
                    FgEditorialList.init();
                    
                    if (FgSidebar.getActiveMenu() == 'li_ARCHIVE_ARCHIVE_ART')
                        FgDatatable.listdataTableInit('datatable-internal-article', datatableOptionsArchive);
                    else
                        FgDatatable.listdataTableInit('datatable-internal-article', datatableOptionsArticle);
                    FgDatatable.datatableSearch();
                    FgFormTools.handleBootstrapSelect();
                    FgUtility.handleSelectPicker();
                    FgFormTools.handleDatepicker();
                    $('body').append('<div class="custom-popup" style="margin-left:-20px"><div class="popover bottom"><div class="arrow"></div><div class="popover-content"></div></div></div>');
                    FgInternal.toolTipInit();
                 

                });

            },
            sideDroppedCallback: function (ui) {

           
                var selectedSubcat = this.target.getAttribute('data-id');
                var selectedCat = this.target.getAttribute('data-type');
                 var selArticle1 = [];
                 var selArticle = [];
                $('input.dataClass:checked').each(function () {
                    selArticle1.push(this.id);
                    
                });
                selArticle = _.uniq(selArticle1);
               
                 //Single assignment without check
                if(selArticle.length==0){
                       var id = (ui.draggable).siblings().find('input').attr('id')
                       selArticle.push(id);
                }
                var articleIds = selArticle.join(',');
                FgArticleSidebar.showArticleAssignPopup(articleIds, 'selected', {selectedId: selectedSubcat, type: selectedCat});


            },
            saveNewElementSidebarCallback: function () {
                var value = this.val();
                var elementType = this.attr("element_type");
                var url = newElementUrl + '?elementType=' + elementType + '&value=' + value;
                var requestData = value;
                $.getJSON(url, requestData, function (data) {
                    FgArticleSidebar.generateSidebar();
                });

            },
            countCallback:function(){
               //Hide Without Assignment and Without Category if count 0
                 FgArticleSidebar.handleWithoutDataItems(); 
                 FgArticleSidebar.setPageTitleCount();
            },
           
        };
         if (parseInt(hasRights) == 0) { //check userrights
             options.CAT.level1.settingsMenu = false;
             options.TIME.level1.settingsMenu = false;
        }
        FgSidebar.init(options, jsonData);
    },
   
    generateSidebar: function () {
        $.getJSON(pathSidebardata, function (data) {
            var jsonSidebarData = data;
            FgSidebar.rebuildSidebar(jsonSidebarData);
        });

    },
   handleWithoutDataItems: function () {
        if (parseInt(hasRights) == 1) {
            var withoutCategoryCount = parseInt($('#li_CAT_WA .badge').text());
            if (withoutCategoryCount > 0) {
                $('#li_CAT_WA').removeClass('hide');
             }else{
                 //If without category hidden active general page
                if(FgSidebar.getActiveMenu()=='li_CAT_WA'){ 
                    FgSidebar.handleSidebarClick('li_GEN_AEA'); 
                }
             }
            var withoutAssignmentCount = parseInt($('#li_AREAS_WA .badge').text());
            if (withoutAssignmentCount > 0) {
                $('#li_AREAS_WA').removeClass('hide');
            } else{
                if(FgSidebar.getActiveMenu()=='li_AREAS_WA'){
                    FgSidebar.handleSidebarClick('li_GEN_AEA'); 
                }
            }
        }
    },
    
    setPageTitleCount: function () {
        var dataCount = $('#sidemenu_bar .active .badge').text();
        $('.page-title #tcount').text(dataCount);
        $('.page-title #tcount').text(dataCount);
        $('.page-title > .page-title-text').text($('#sidemenu_bar .active .title').text());
    },
    updateCategoryFilter: function (obj, dataId,dataType) {
        if (dataType == 'CAT') {
            var catTitle = $('#' + obj + '> .nav-link > .title').text();
            if ($("select#FILTER_CAT  option[value='" + dataId + "']").length == 0) {
                $("select#FILTER_CAT").
                        append("<option  class='multiple' value='" + dataId + "'>" + catTitle + "</option>");
            }
        }
    },
    showArticleAssignPopup: function (checkedIds, selected, params) {
        var articleDetails = FgEditorialList.getArticleTitles(checkedIds);
        $.post(articleAssignPath, {'checkedIds': checkedIds, 'selected': selected, 'params': params, 'articleArray': articleDetails}, function (data) {
            FgModelbox.showPopup(data);
        });
    }
}


$(document).on('click', '.fg-dev-timeperiod-link', function (event) {
    event.preventDefault();
    $.post(timeperiodPopupPath, {}, function (data) {
        FgModelbox.showPopup(data);
    });
});

FgArticleTimePeriod = {
    timePeriodSave: function () {
        var day = $("#time-day").val();
        var month = $("#time-month").val();
        if ((!isNaN(day)) && (!isNaN(month))) {
            var year = (new Date).getFullYear();
            var dateFormat = "DD/MM/YYYY";
            var date = day + '/' + month + '/' + year;
            var isValid = moment(date, dateFormat).isValid();
            if (isValid) {
                var timePeriodData = {'dayVal': day, 'monthVal': month};
                FgXmlHttp.post(timePeriodSavePath, timePeriodData, false, function (d) {
                    FgArticleTimePeriod.callback(d);
                });
                FgModelbox.hidePopup();
            } else {
                $(".fg-modal-preview").addClass('hide');
                $('.time-period-error').removeClass('hide');
            }
        } else {
            if (day != '' && month != '') {
                $(".fg-modal-preview").addClass('hide');
            }
        }
    },
    callback: function (data) {
         FgArticleSidebar.generateSidebar();
    },
    redrawTimeperiodSection: function (data) {
        var tpData = [];
        $.each(data, function (i, v) {
            if (v.count > 0) {
                tpData.push({id: v.start + '__' + v.end, title: v.label, count: v.count, isArticle: 1, itemType: 'TIME'});
            }
        });
        delete jsonData.TIME.entry;
        jsonData.TIME.entry = tpData;
        FgArticleSidebar.generateSidebar();
    }


}