$(document).ready(function () {
    scope = angular.element($("#BaseController")).scope();
    FgPageTitlebar = $(".fg-action-menu-wrapper").FgPageTitlebar({
        title: true,
        tab: true,
        tabType: 'client'
    });
    var option = {
        pageType: 'cmsAddElement',
        contactId: contactId,
        currentClubId: clubId,
        localStorageName: type + '_' + clubId + '_' + contactId,
        tabheadingArray: tabheadingArray
    };
    Fgtabselectionprocess.initialize(option);
    FgDirtyFields.init('mapElement', {saveChangeSelector: "#save_changes, #save_bac", discardChangesCallback:FgCmsMapElement.discardChangesCallback});
    FgCmsMapElementLog.init();
    FgMapSettings.mapAutoComplete(); 
    $(".uniform").uniform();
    FgFormTools.handleInputmask();
 });

var FgCmsMapElement = {
    renderContent: function () {
        $('#elementMapWrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
    },
    renderLog: function () {
        $('#elementMapWrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        FgCmsMapElementLog.init();

    },
    saveElementCallback: function (d) {
        FgDirtyFields.init('mapElement', {saveChangeSelector: "#save_changes, #save_bac", discardChangesCallback:FgCmsMapElement.discardChangesCallback});
        $(".uniform").uniform();
    },
    discardChangesCallback :function(){
        FgMapSettings.mapAutoComplete();    
    }
};


var FgCmsMapElementLog = {
    init: function () {
        FgCmsMapElementLog.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    },
    dataTableOpt: function () {
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
                row.sortData = row['type'];
                row.displayData = row['type'] + flag;

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
            initialSortingorder: 'asc'
        };
    }
};

    $('body').off('click', '#save_changes');
    $('body').on('click', '#save_changes, #save_bac', function (e) {
       
        var currentSelectedButton = $(this).attr('id');
        var longitude = $("#mapLng").val();
        var latitude =  $("#mapLat").val(); 
        var location =  $("#locAutoComp").val();
        var mapHeight = $("#mapHeight").val();
        var mapDisplay = $("input[name=mapDisp]:checked").val();
        var mapZoomValue = $("#mapZoom").val();
        var mapMarker = 0;
        if($("#mapMarker").is(':checked')){
           mapMarker = 1;
        }
       
        var validation =  validateForm(location, mapHeight);
       
        if(validation == 0){
          
          var saveType = (currentSelectedButton == 'save_changes') ? 'save' : 'saveBack';
          var data = {'longitude': longitude, 'latitude': latitude,'location':location, 'boxId':boxId, 'elementId':elementId, 'sortOrder':sortOrder, 'pageId':pageId, 'mapHeight':mapHeight, 'mapDisplay':mapDisplay, 'saveType':saveType, 'mapMarker':mapMarker, 'mapZoomValue':mapZoomValue };
          FgDirtyFields.removeAllDirtyInstances();
          FgXmlHttp.post(saveMapElement, data, false, FgCmsMapElement.saveElementCallback); 
        }
        
    });
    
 function validateForm(location, mapHeight) 
 {
  var validationFlag = 0;
    if (location == '') {
        $('form#mapElement input#locAutoComp').parent().addClass('has-error');
        $('<span class="help-block fg-cms-map-location-error-block fg-marg-top-5">'+ jstranslations.VALIDATION_THIS_FIELD_REQUIRED +'</span>').insertAfter($('form#mapElement input#locAutoComp'));
        $('.fg-cms-map-location').addClass('has-error');
        validationFlag = 1;
         
    }
    if (mapHeight == '') {
        $('form#mapElement input#mapHeight').parent().addClass('has-error');
        $('<span class="help-block fg-marg-top-5 fg-cms-map-height-error-block">' + jstranslations.VALIDATION_THIS_FIELD_REQUIRED +'</span>').insertAfter($('form#mapElement input#mapHeight'));
        $('.fg-cms-map-height').addClass('has-error');
        validationFlag = 1; 
    }
     return validationFlag;
}

