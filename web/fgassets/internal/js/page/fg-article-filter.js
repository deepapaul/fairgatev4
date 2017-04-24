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
     
   },
    redrawArticleTable:function(){
        
        Metronic.startPageLoading();
        // Destroy datatable
        if (!$.isEmptyObject(listTable)) {
            listTable.destroy();
        }
      
        if(FgSidebar.getActiveMenu() == 'li_ARCHIVE_ARCHIVE_ART') 
            FgDatatable.listdataTableInit('datatable-internal-article', datatableOptionsArchive);
        else
            FgDatatable.listdataTableInit('datatable-internal-article', datatableOptionsArticle);
            
    },
    
   
    filterSubmitHandling : function(){
        $('#search-filter-article').on("click", function() {
            filterData = FgInternalParseFormField.formFieldParse('articleFilterWrap');
            localStorage.setItem( tableFilterStorageName, JSON.stringify(filterData));
            FgArticleFilter.redrawArticleTable();
        });
        $('.remove-filter').on("click", function() {
            //sidebarActive = localStorage.getItem('activeSubMenuVar-'+clubId+'-'+contactId);
            sidebarActive= FgSidebar.getActiveMenu();
            dataType = $('#'+sidebarActive+' a').attr('data-type');
            dataId = $('#'+sidebarActive+' a').attr('data-id');
            localStorage.removeItem(tableFilterStorageName);
            FgArticleFilter.setFilter(dataType,dataId);
        });
    },
    loadFilter:function(){
        var filterData = localStorage.getItem( tableFilterStorageName );
        filterObj = JSON.parse(filterData);
       // sidebarActive = localStorage.getItem('activeSubMenuVar-'+clubId+'-'+contactId);
        sidebarActive= FgSidebar.getActiveMenu();
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
                    if(sidebarActive != 'li_GEN_MA' ){
                        FgArticleFilter.initFilterAuto('selected',filterObj);
                    }
                    break;
            }
        });
        if(sidebarActiveValue[1] == 'AREAS' || sidebarActiveValue[1] == 'CAT'){
            
            if(sidebarActive=='li_CAT_WA' || sidebarActive=='li_AREAS_WA'){
                datatableOptionsArticle.ajaxparameters.menuType = sidebarActiveValue[1];
                datatableOptionsArticle.ajaxparameters.subCategoryId = 'WA';
                $('div[data-filter-select='+sidebarActiveValue[1]+']').addClass('hide');
            } else {
                $('select#FILTER_'+sidebarActiveValue[0]).prop('disabled',true);
            }
        } else if(sidebarActiveValue[0]=='TIME'){
            $('.FILTER-DATE').prop('disabled',true);
            $('#articleFilterWrap .fg-date .input-group-addon').addClass('fg-disabled-icon');
        } else if(sidebarActive=='li_ARCHIVE_ARCHIVE_ART'){
                if ($("select#FILTER_STATUS option[value='archived']").length==0) {
                     $('select#FILTER_STATUS').append(archivedOpt);
                    $('select#FILTER_STATUS').val('archived');
                    $('#FILTER_STATUS').prop('disabled',true);
                }
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
            if ($("select#FILTER_STATUS option[value='archived']").length==0) { 
                
                $('select#FILTER_STATUS').append(archivedOpt);
                $('select#FILTER_STATUS').val('archived');
                $('#FILTER_STATUS').prop('disabled',true);
            }
        }
       // sidebarActive = localStorage.getItem('activeSubMenuVar-'+clubId+'-'+contactId);
       sidebarActive = FgSidebar.getActiveMenu();
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
