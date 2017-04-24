//function to call sidebar
function callSidebar(){
    var filterBookmark = {};
    var defaultTitle;
            FgSidebar.jsonData = true;
            FgSidebar.activeMenuVar = ArticleParams.activeMenuVar;
            FgSidebar.activeSubMenuVar = ArticleParams.activeSubMenuVar;
            FgSidebar.defaultMenu = 'GEN';
            FgSidebar.defaultSubMenu = 'GEN_li_AEA';
            FgSidebar.options = [];
            FgSidebar.newElementLevel1 = newElementLevel1;
            FgSidebar.newElementLevel2 = newElementLevel2;
            FgSidebar.newElementLevel2Sub = newElementLevel2Sub;
            FgSidebar.defaultTitle = defaultTitle;
            FgSidebar.newElementUrl = newElementUrl;
            FgSidebar.showloading = true;
            FgSidebar.isDataTable = true;
            FgSidebar.module = 'article';
            FgSidebar.settings = {};
    $.each(jsonData, function (key, data) {
        var keySplit = key.split('-');
        var actualKey = keySplit[0];
        switch (actualKey) {
            case "GEN":
                var genTitle = data.title;
                var genId = key + '_li';
                var genData = (typeof jsonData['GEN'] !== "undefined" && typeof jsonData['GEN']['entry'] !== "undefined") ? jsonData['GEN']['entry'] : {};
                var genMenu = {templateType: 'general', itemType: 'ggg', menuType: 'GEN', 'parent': {itemType: 'ggg', id: genId, class: '', name: '', 'data-placement': "right"}, title: genTitle, template: '#template_sidebar_menu', 'menu': {'items': genData}};
                FgSidebar.settings[genId] = genMenu;
                FgSidebar.options.push({'id': genId, 'title': genTitle});
            break;
            case "AREAS":
                var areaTitle = data.title;
                var areaId = key + '_li';
                var areaData = (typeof jsonData['AREAS'] !== "undefined" && typeof jsonData['AREAS']['entry'] !== "undefined") ? jsonData['AREAS']['entry'] : {};
                areaData = _.map(areaData, function(team){ team.input = _.without(team.input, _.findWhere(team.input, {id: 'any'})); return team; });
                var areaMenu = {templateType: 'menu2level', menuType: 'AREAS', 'parent': {id: areaId, class: areaId}, title: areaTitle, template: '#template_sidebar_menu2level', 'menu': {'items': areaData}};
                FgSidebar.settings[areaId] = areaMenu;
                FgSidebar.options.push({'id': areaId, 'title': areaTitle, 'df':'sdfsd'});
            break;
            case "TIME":
                var tpTitle = data.title;
                var tpId = key + '_li';
                var tpData = (typeof jsonData['TIME'] !== "undefined" && typeof jsonData['TIME']['entry'] !== "undefined") ? jsonData['TIME']['entry'] : {};
                var tpSettings = (hasRights === '1') ? {"0": {'title': ArticleTrans.CREATE_TIMEPERIOD , 'url': '', 'class' : 'fg-dev-timeperiod-link', } } : '';
                var tpMenu = {templateType: 'general', menuType: 'TIME','parent': {id: tpId, class: 'tooltips bookmark-link', name: 'bookmark-link', 'data-placement': "right"}, title: tpTitle, template: '#template_sidebar_menu', 'settings': tpSettings, 'menu': {'items': tpData}};
                FgSidebar.settings[tpId] = tpMenu;
                FgSidebar.options.push({'id': tpId, 'title': tpTitle});
            break;
            case "CAT":
                var catTitle = data.title;
                var catId = key + '_li';
                var catData = (typeof jsonData['CAT'] !== "undefined" && typeof jsonData['CAT']['entry'] !== "undefined") ? jsonData['CAT']['entry'] : {};
                var catSettings = (hasRights === '1') ? {"0": {'type': 'newElement', 'title': ArticleTrans.CREATE_CATEGORY , 'placeHolder': ArticleTrans.CREATE_CATEGORY ,  'url': '#', 'contentType':'CAT', 'subContentType':key, 'target': '#'+catId, 'hierarchy': '1'}, "1": {'title': ArticleTrans.CATEGORY_SETTINGS , 'url': ArticleParams.catSettingPath }} : '';
                var catMenu = {templateType: 'general', modType:'article', menuType: key, 'parent': {id: catId, isClickable: true, 'data-placement': "right"},  title: catTitle, template: '#template_sidebar_menu',  'settings': catSettings,'menu': {'items': catData}};
                FgSidebar.settings[catId] = catMenu;
                FgSidebar.options.push({'id': catId, 'title': catTitle});
            break;
            case "ARCHIVE":
                var arTitle = data.title;
                var arId = key + '_li';
                var arData = (typeof jsonData['ARCHIVE'] !== "undefined" && typeof jsonData['ARCHIVE']['entry'] !== "undefined") ? jsonData['ARCHIVE']['entry'] : {};
                var arMenu = {templateType: 'general', menuType: 'ARCHIVE', 'parent': {id: arId, class: 'tooltips bookmark-link', name: 'bookmark-link', 'data-placement': "right"}, title: arTitle, template: '#template_sidebar_menu', 'menu': {'items': arData}};
                FgSidebar.settings[arId] = arMenu;
                FgSidebar.options.push({'id': arId, 'title': arTitle});
            break;

        }

    });
    if(FgSidebar.isFirstTime){
        FgSidebar.init();
        FgSidebar.isFirstTime = false;
    }else{
        FgSidebar.loadJsonSidebar();
    }
    FgArticleFilter.handleWithoutDataItems();
}

 $(document).on('click', '.fg-dev-timeperiod-link', function(event){
    event.preventDefault();
    $.post(timeperiodPopupPath, {}, function(data) {
        FgModelbox.showPopup(data);
    });
 });

FgArticleTimePeriod = {
    timePeriodSave: function(){
          var day = $("#time-day").val();
          var month = $("#time-month").val();
          if ((!isNaN(day)) && (!isNaN(month))) {
            var year = (new Date).getFullYear();
            var dateFormat = "DD/MM/YYYY";
            var date = day + '/' + month + '/' + year;
            var isValid = moment(date, dateFormat).isValid();
            if (isValid) {
               var timePeriodData = {'dayVal':day,'monthVal':month };
               FgXmlHttp.post(timePeriodSavePath, timePeriodData, false, function(d){ FgArticleTimePeriod.callback(d); });
               FgModelbox.hidePopup();
            } else {
                $(".fg-modal-preview").addClass('hide');
                $('.time-period-error').removeClass('hide');
            }
        }else{
            if (day != '' && month != ''){
                $(".fg-modal-preview").addClass('hide');
            }
        }
    },
    callback : function(data){
        
        FgArticleTimePeriod.redrawTimeperiodSection(data.result);
        
    },
    
    redrawTimeperiodSection : function(data){
        var tpData = [];
        $.each(data, function(i,v){           
            if(v.count > 0){
                tpData.push({ id:v.start+'__'+v.end, title : v.label, count : v.count, isArticle : 1, itemType:'TIME'});
            }
        });
        delete jsonData.TIME.entry;
        jsonData.TIME.entry = tpData;
        callSidebar();
        FgArticleFilter.redrawSidebarArticleCount('show');
    }
 }
FgArticleFilter = {
    init : function(){
        FgArticleFilter.callFilterFlag();
        FgArticleFilter.filterSubmitHandling();
        sidebarActive = localStorage.getItem('activeSubMenuVar-'+clubId+'-'+contactId);
        var filterSelected = (sidebarActive=='GEN_li_MA') ? '1':'0';
        if(filterSelected=='1'){
            $('#publishedBy').prop('disabled',true);
        }
        FgArticleFilter.initFilterAuto(filterSelected);
    },
    callFilterFlag: function() {
        $("#filterFlag").on("click", function() {
            if ($(this).is(':checked')) {
                $('.custom-alerts').removeClass('hide');
                $('#filterFlag').attr('checked', true);
                localStorage.setItem(tableFilterVisible, 1);
            } else {
                $('.custom-alerts').addClass('hide');
                $('#filterFlag').attr('checked', false);
                localStorage.setItem(tableFilterVisible, 0);
            }
        });
        $(".fg_filter_hide").on("click", function() {
            $('.custom-alerts').addClass('hide');
            $('#filterFlag').attr('checked', false);
            FgFormTools.updateUniform('#filterFlag');
            localStorage.setItem(tableFilterVisible, 0);
        });
        FgArticleFilter.redrawSidebarArticleCount('show'); 
   },
    redrawArticleTable:function(){
        Metronic.startPageLoading();
        // Destroy datatable
        if (!$.isEmptyObject(listTable)) {
            listTable.destroy();
        }
        FgDatatable.listdataTableInit('datatable-internal-article', datatableOptions);
    },
    redrawSidebarArticleCount:function(action){
        if ($('#sidemenu_bar').find('.badge.badge-round.badge-important.fg-badge-blue') || $('#sidemenu_bar').find('.no-value.badge.badge-round.badge-important')) {
            $('#sidemenu_bar').find('.no-value.badge.badge-round.badge-important').addClass('fg-sidebar-loading fa-spin').removeClass('no-value badge badge-round badge-important');
            $('#sidemenu_bar').find('.badge.badge-round.badge-importantfg-badge-blue').addClass('fg-sidebar-loading fa-spin').removeClass('badge badge-round badge-important fg-badge-blue');
            $('#sidemenu_bar').find('.fg-sidebar-loading.fa-spin').text('');
        } 
        $.getJSON(articleSidebarCountPath, function (data) {
            var countData = data;
            if (countData) {
                FgCountUpdate.update(action, false, 'active', countData, 1);
            }
            if ($('#sidemenu_bar').find('.fg-sidebar-loading')) {
                $('#sidemenu_bar').find('.fg-sidebar-loading').addClass('badge badge-round badge-important no-value fg-badge-blue').removeClass('fg-sidebar-loading fa-spin');
                $('#sidemenu_bar').find('.no-value').text('0');
            }
            FgArticleFilter.handleWithoutDataItems();
            FgArticleFilter.setPageTitleCount();
        });
    },
    handleWithoutDataItems: function(){
        var withoutCategoryCount = parseInt($('#CAT_li_WA .badge').text());
        if(withoutCategoryCount > 0){
            $('#CAT_li_WA').show();
        } else {
            $('#CAT_li_WA').hide();
            if ($('#CAT_li_WA').siblings().length == 0) {
                $('#CAT_li_WA').parents('#CAT_li').children('a').first().addClass('fg-no-data-sidebar').find('.arrow').removeClass('arrow pull-left').addClass('fg-without-arrow');
            }
        }
        
        var withoutAssignmentCount = parseInt($('#AREAS_li_WA .badge').text());
        if(withoutAssignmentCount > 0){
            $('#AREAS_li_WA').show();
        } else {
            $('#AREAS_li_WA').hide();
        }
    },
    setPageTitleCount: function(){
        var dataCount = $('#sidemenu_bar .active.fg-without-arrow  .badge').text();
        $('.page-title #tcount').text(dataCount);
    },
    filterSubmitHandling : function(){
        $('#search-filter-article').on("click", function() {
            filterData = FgInternalParseFormField.formFieldParse('articleFilterWrap');
            localStorage.setItem( tableFilterStorageName, JSON.stringify(filterData));
            FgArticleFilter.redrawArticleTable();
        });
        $('.remove-filter').on("click", function() {
            sidebarActive = localStorage.getItem('activeSubMenuVar-'+clubId+'-'+contactId);
            dataType = $('#'+sidebarActive+' a').attr('data-type');
            dataId = $('#'+sidebarActive+' a').attr('data-id');
            localStorage.removeItem(tableFilterStorageName);
            FgArticleFilter.setFilter(dataType,dataId);
        });
    },
    loadFilter:function(){
        var filterData = localStorage.getItem( tableFilterStorageName );
        filterObj = JSON.parse(filterData);
        sidebarActive = localStorage.getItem('activeSubMenuVar-'+clubId+'-'+contactId);
        sidebarActiveValue = sidebarActive.split('_');
        $.each(filterObj.filter, function(key,values){
            switch(key){
                case 'AREAS':
                    $('select#FILTER_'+key).val(values);
                    break;
                case 'CATEGORIES':
                    $('select#FILTER_CAT').val(values);
                    break;
                case 'START_DATE':
                    $('input#START-DATE').attr('value',values);
                    break;
                case 'END_DATE':
                    $('input#END-DATE').attr('value',values);
                    break;
                case 'STATUS':
                    $('select#FILTER_STATUS').val(values);
                    break;
                case 'publishedBy':
                    if(sidebarActive != 'GEN_li_MA' ){
                        FgArticleFilter.initFilterAuto('selected',filterObj);
                    }
                    break;
            }
        });
        if(sidebarActiveValue[0] == 'AREAS' || sidebarActiveValue[0] == 'CAT'){
            if(sidebarActive=='CAT_li_WA' || sidebarActive=='AREAS_li_WA'){
                datatableOptions.ajaxparameters.menuType = sidebarActiveValue[0];
                datatableOptions.ajaxparameters.subCategoryId = 'WA';
                $('div[data-filter-select='+sidebarActiveValue[0]+']').addClass('hide');
            } else {
                $('select#FILTER_'+sidebarActiveValue[0]).prop('disabled',true);
            }
        } else if(sidebarActiveValue[0]=='TIME'){
            $('.FILTER-DATE').prop('disabled',true);
            $('#articleFilterWrap .fg-date .input-group-addon').addClass('fg-disabled-icon');
        } else if(sidebarActive=='ARCHIVE_li_ARCHIVE_ART'){
            $('select#FILTER_STATUS').append(archivedOpt);
            $('select#FILTER_STATUS').val('archived');
            $('#FILTER_STATUS').prop('disabled',true);
        }
        FgFormTools.handleDatepicker();
        $('#articleFilterWrap .bs-select').selectpicker('refresh');
        if(localStorage.getItem(tableFilterVisible)==1){
            $('.custom-alerts').removeClass('hide');
            $('#filterFlag').attr('checked', true);
        }
    },
    setFilter:function(type,data){
        $('#articleFilterWrap :input').val('');
        $('select#FILTER_STATUS option[value=archived]').remove();
        $('#articleFilterWrap select').val('ALL');
        $('#articleFilterWrap :input').prop('disabled',false);
        $('div[data-filter-select]').removeClass('hide');
        $('#articleFilterWrap .fg-date .input-group-addon').removeClass('fg-disabled-icon');
        localStorage.removeItem(tableFilterStorageName);
        if(type==='AREAS'||type==='CAT') {
            if(data=='WA'){
                $('div[data-filter-select='+type+']').addClass('hide');
            } else {
                $('select#FILTER_'+type).val(data);
                $('select#FILTER_'+type).prop('disabled',true);
            }
        } else if(type=='TIME'){
            var dates =data.split('__');
            $('input.FILTER_'+type).prop('disabled',true);
            var dateFrom = FgLocaleSettings.formatDate(dates[0],'date','YYYY-MM-DD H:mm');
            $('input#START-DATE').attr('value',dateFrom);
            var dateTo = FgLocaleSettings.formatDate(dates[1],'date','YYYY-MM-DD H:mm');
            $('input#END-DATE').attr('value',dateTo);
            $('.FILTER-DATE').prop('disabled',true);
            FgFormTools.handleDatepicker();
            $('#articleFilterWrap .fg-date .input-group-addon').addClass('fg-disabled-icon');
        }
        if(type=='ARCHIVE'){
            $('select#FILTER_STATUS').append(archivedOpt);
            $('select#FILTER_STATUS').val('archived');
            $('#FILTER_STATUS').prop('disabled',true);
        }
        sidebarActive = localStorage.getItem('activeSubMenuVar-'+clubId+'-'+contactId);
        sidebarActiveValue = sidebarActive.split('_');
        $('#publishedBy').prop('disabled',false);
        $('.remove-fbautocomplete').click();
        if(data=='MA'){
            $('#publishedBy').prop('disabled',true);
            $('#publishedBySelection').val(contactId);
            FgArticleFilter.initFilterAuto('1');
        } else {
            FgArticleFilter.initFilterAuto();
        }
        $('#articleFilterWrap .bs-select').selectpicker('refresh');
        FgUtility.handleSelectPicker();
        filterData = FgInternalParseFormField.formFieldParse('articleFilterWrap');
        localStorage.setItem( tableFilterStorageName, JSON.stringify(filterData));
    },
    initFilterAuto:function(withSelection,filterObj){
        var selectedContact = (withSelection == '1') ? selectedCon:[];
        if(withSelection=='selected' && filterObj.filter.publishedBy!==contactId && filterObj.filter.publishedBy!=''){
            published = filterObj.filter.publishedByTitle;
            var selectedContact = [{"id":filterObj.filter.publishedBy,"title":published}];
        }
        $('#publishedBy').fbautocomplete({
            url: contactUrl, // which url will provide json!
            removeButtonTitle: removestring,
            formName: 'publishedBy',
            selected: selectedContact,
            maxItems: 1,
            useCache: true,
            onItemSelected: function($obj, itemId, selected) {
                $('#publishedBySelection').val(itemId);
                $('#publishedByTitle').val(selected[0]['title']);
            },
            onItemRemoved: function($obj, itemId) {
                $('#publishedBySelection').val('');
                $('#publishedByTitle').val();
            },
            onAlreadySelected: function($obj) {

            }
        });
    }
}

FgArticleManage = {
    archiveArticlePopup: function (articleArray) {
        $.post(archiveArticlePopup, {'articleArray': articleArray}, function (data) {
            FgModelbox.showPopup(data);
        });
    },
    archiveArticle: function (articleDetails) {
        FgXmlHttp.post(archiveArticle, {'articleDetails': articleDetails}, false, FgArticleManage.callBack);
        FgModelbox.hidePopup();
    },
    callBack: function (data) {
        FgArticleFilter.redrawArticleTable();
        FgArticleTimePeriod.redrawTimeperiodSection(data.result);
    },
    deleteArticlePopup: function (articleArray) {
        $.post(deleteArticlePopup, {'articleArray': articleArray}, function (data) {
            FgModelbox.showPopup(data);
        });
    },
    reactivateArticlePopup: function (articleArray) {
        $.post(reactivateArticlePopup, {'articleArray': articleArray}, function (data) {
            FgModelbox.showPopup(data);
        });
    },
    deleteArticle: function (articleDetails) {
        FgXmlHttp.post(deleteArticle, {'articleDetails': articleDetails}, false, FgArticleManage.callBack);
        FgModelbox.hidePopup();
    },
    reactivateArticle: function (articleDetails) {
        FgXmlHttp.post(reactivateArticle, {'articleDetails': articleDetails}, false, FgArticleManage.callBack);
        FgModelbox.hidePopup();
    },
    showArticleAssignPopup : function (checkedIds, selected, params) {        
        var articleDetails =  FgEditorialList.getArticleTitles(checkedIds);
        $.post(articleAssignPath, {'checkedIds': checkedIds, 'selected': selected, 'params': params, 'articleArray' : articleDetails}, function (data) {                 
            FgModelbox.showPopup(data);
        });
    }
};
