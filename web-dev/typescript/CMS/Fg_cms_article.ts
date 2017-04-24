/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgCmsArticleElement {
    
    
   
    public renderContent() {
        $('#elementArticleWrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsArticleElementContent').addClass('active');
    }

    public renderLog(){
        var CmsArticleElementLog = new FgCmsArticleElementLog();
    	CmsArticleElementLog.init();
        $('#elementArticleWrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        $('.fg-lang-tab').addClass('invisible');
    }
    
    public saveElementCallback(d){
          var CmsArticleElement = new FgCmsArticleElement({});
    	  FgDirtyFields.init('addArticleElement', {saveChangeSelector: "#save_changes, #save_bac", setInitialHtml: false, discardChangesCallback:CmsArticleElement.discardAfterSave});
    }
    
    public discardChangesCallback(){
        var displaySelect = new FgCmsArticleElement();
        displaySelect.handleArticleDisplay(articleEditData);
        $('.bootstrap-select').remove();
        $('.selectpicker').selectpicker();
        $('select.selectpicker').selectpicker({noneSelectedText: statusTranslations['select']});
        $('select.selectpicker').selectpicker('render');
        FgUtility.handleSelectPicker();
        $('form input[name=fedShared], [name=subFedShared]').unwrap().unwrap();
        $('form input[name=showAreas], [name=showCategory], [name=showDate],[name=thumbnail]').unwrap().unwrap();
        $('form input[name=slider_nav]').unwrap();
        FgFormTools.handleUniform();
        
    }
    
    public discardAfterSave()
    {
     $('.selectpicker').selectpicker('refresh');
     FgUtility.handleSelectPicker();  
     var fedCheckVal = $("#fedShared").attr('data-id');
     var subFedCheckVal = $("#subFedShared").attr('data-id');
     
     if(fedCheckVal != ''){
           $("#fedShared").parent('span').addClass('checked');
       }else{
          $("#fedShared").parent('span').removeClass('checked');
      }
     if(subFedCheckVal != ''){
          $("#subFedShared").parent('span').addClass('checked');
       }else{
          $("#subFedShared").parent('span').removeClass('checked');
      }
      var displaySelect = new FgCmsArticleElement();
      displaySelect.handleArticleDisplay(articleEditData);
      FgFormTools.handleUniform();
    
    }
    
    public isValidForm(articleAreas, articleCategories, fedIdVal, subFedIdVal){
        $("#failcallbackClientSide").addClass('hide');
        if ((fedIdVal == '' || fedIdVal == null) && (subFedIdVal == '' || subFedIdVal == null)) {
            if (articleAreas == null || articleCategories == null) {
                 $("#failcallbackClientSide").removeClass('hide');
                $("#failcallbackClientSide").show();
                return false;
            }
        }
          return true;
        }
    public handleArticleDisplay(articleEditData) {
        let _this = this;
        if (typeof (articleEditData.articlePerRow) == 'undefined' || articleEditData.articlePerRow == null || articleEditData.articlePerRow == '') {
            $('#articlePerRow').val(1);
        }
        if (typeof (articleEditData.articleRowsCount) == 'undefined' || articleEditData.articleRowsCount == null || articleEditData.articleRowsCount == '') {
            $('#maxRows').val(4);
        }
        if (typeof (articleEditData.articleRowsCount) == 'undefined' || articleEditData.articleCount == null || articleEditData.articleCount == '') {
            $('#maxArticles').val(5);
        }
        if (typeof (articleEditData.articleSliderNavigation) == 'undefined' || articleEditData.articleSliderNavigation == null || articleEditData.articleSliderNavigation == '') {
            $('input:radio[name=slider_nav][value=none]').prop('checked', 'checked');
            $('#nav_none').attr('checked', 'checked');
        }
        _this.handleAreaCheckbox();
        _this.handleCategoryCheckbox();
        
        $('#articleAreas').on('change', function() {
            _this.handleAreaCheckbox();
        });
        $('#articleCategories').on('change', function() {
            _this.handleCategoryCheckbox();
        });
        
        $('#fedShared, #subFedShared').on('change', function () {
            _this.handleAreaCheckbox();
            _this.handleCategoryCheckbox();
        });
        
        _this.handleArticleDisplayChange();
        _this.handleThumbnailImage();

        $("input:radio[name=articleDisplay]").click(function() {
            _this.handleArticleDisplayChange();
        });
        $("input:radio[name=slider_nav]").click(function() {
            _this.handleThumbnailImage();
        });
        
        _this.handleNumberButtons();
        _this.handleHelpMessages();

    }

    private handleNumberButtons() {
        var plusminusOption = {
            'selector': ".selectButton"
        };
        var inputplusminus = new Fgplusminus(plusminusOption);
        inputplusminus.init();
    }



    public handleArticleDisplayChange() {
        var selectedDisplay = $("input:radio[name=articleDisplay]:checked").val();
        if (selectedDisplay == 'slider') {
            $('#view_slider').removeClass('hide');
            $('#view_listing').addClass('hide');
        } else {
            $('#view_listing').removeClass('hide');
            $('#view_slider').addClass('hide');
        }
    }
    public handleThumbnailImage() {
        var selectedSlider = $("input:radio[name=slider_nav]:checked").val();
        if (selectedSlider == 'none') {
            $('#thumbnail').attr('disabled', 'disabled');
            $('#thumbnail').parent().removeClass('checked');
            $('#thumbnail').prop('checked',false);
        } else {
            $('#thumbnail').removeAttr('disabled');
        }
        $.uniform.update("#thumbnail");
    }
    public handleAreaCheckbox() {
        var areaCount = 0;
        var selected = $('[name=articleAreas]').val();
        if(selected == 'ALL_AREAS'){
            areaCount = areaCount + 10;
        }else if( selected != null){
            areaCount = areaCount +  selected.length;
        }
        areaCount = (($("#fedShared").is(':checked')) ? 10 : 0) + areaCount;
        areaCount = (($("#subFedShared").is(':checked')) ? 10  : 0) + areaCount ;
        var showArea = (areaCount >  1) ? 1 : 0;
        this.handleAreaCategoryCheckbox(showArea,'area');
    }
    
    public handleCategoryCheckbox() {
        var catCount = 0;
        var selected = $('[name=articleCategories]').val();
        if(selected == 'ALL_CATS'){
            catCount = catCount + 10;
        }else if( selected != null){
           catCount = catCount + selected.length;
        }
        catCount = (($("#fedShared").is(':checked')) ? 10 : 0) + catCount;
        catCount = (($("#subFedShared").is(':checked')) ? 10  : 0) + catCount;
        var showCategory = (catCount >  1) ? 1 : 0;
        this.handleAreaCategoryCheckbox(showCategory,'category');
    }
    private handleAreaCategoryCheckbox(showFlag, boxType) {
        if (boxType == 'area') {
            if (showFlag) {
                $('#showAreas').removeAttr('disabled');
            } else {
                $('#showAreas').attr('disabled', 'disabled');
                $('#showAreas').parent().removeClass('checked');
                $('#showAreas').prop('checked',false);

            }
            $.uniform.update("#showAreas");

        } else {
            if (showFlag) {
                $('#showCategory').removeAttr('disabled');
            } else {
                $('#showCategory').attr('disabled', 'disabled');
                $('#showCategory').parent().removeClass('checked');
                $('#showCategory').prop('checked',false);
            }
            $.uniform.update("#showCategory");
        }
    }
    public handleHelpMessages() {
        $('#articlePerRow').on('change', function () {
            var articlePerRow = parseInt($('#articlePerRow').val());
            var Maxrows = parseInt($('#maxRows').val());
            var articleListCount = (Maxrows * articlePerRow) ? (Maxrows * articlePerRow) : 0;
            $('#rowsCount').html(articleListCount);
            $('#articlesPerRowMsg').html(articlesPerRowMsg[articlePerRow]);
        });
            var articlePerRow = parseInt($('#articlePerRow').val());
            var Maxrows = parseInt($('#maxRows').val());
            var articleListCount = (Maxrows * articlePerRow) ? (Maxrows * articlePerRow) : 0;
            $('#articlesPerRowMsg').html(articlesPerRowMsg[articlePerRow]);
            $('#rowsCount').html(articleListCount);
        $('#maxRows').on('change', function () {
            var articlePerRow = parseInt($('#articlePerRow').val());
            var Maxrows = parseInt($('#maxRows').val());
            var articleListCount = (Maxrows * articlePerRow) ? (Maxrows * articlePerRow) : 0;
            $('#rowsCount').html(articleListCount);
        });
    }
  }
	


class FgCmsArticleElementLog {
     
    public init(){
        this.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    }
    
    public dataTableOpt(){
            
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var i = 0;
        columnDefs.push({"name": "date", "type": "moment-" + currentDateFormat, "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['date'];
                row.displayData = '&nbsp;&nbsp;' + row['date'];
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "option", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">'+statusTranslations[row['status']]+'</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">'+statusTranslations[row['status']]+'</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">'+statusTranslations[row['status']]+'</span>' : '-'));
                var type = (row['type'] === 'element') ? statusTranslations.element : statusTranslations.page_assignment;
                row.sortData =  row['type'];
                row.displayData = type + flag;

                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_before", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueBefore'];
                row.displayData = row['valueBefore'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "value_after", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueAfter'];
                row.displayData = row['valueAfter'];
                return row;
            }, render: {"_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData'}});
        columnDefs.push({"name": "edited_by", "targets": i++, data: function (row, type, val, meta) {
                
                var profileLink = profilePath.replace("dummy", row['activeContactId']);
                row.sortData = row['contact'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['contact'] + '</a></div>' : '<span class="fg-table-reply">' + row['contact'] + '</span>';
                return row;
                
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});

        datatableOptions = {
            columnDefFlag: true,
            ajaxPath: elementLogDetailsPath,
            ajaxparameterflag: true,
            ajaxparameters: {
                elementId: elementId
            },
            columnDefValues: columnDefs,
            serverSideprocess: false,
            displaylengthflag: false,
            initialSortingFlag: true,
            initialsortingColumn: '0',
            initialSortingorder: 'desc',
            fixedcolumnCount: 0
        };
        
    }
    
    
}


