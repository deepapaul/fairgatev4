var FgCmsMapElement = (function () {
    function FgCmsMapElement() {
        this.elementLogDetailsPath = '';
        this.elementId = '';
    }
    FgCmsMapElement.prototype.renderContent = function () {
        $('#elementMapWrapper').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsMapElementContent').addClass('active');
    };
    FgCmsMapElement.prototype.renderLog = function () {
        var CmsMapElementLog = new FgCmsMapElementLog();
        CmsMapElementLog.init();
        $('#elementMapWrapper').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
    };
    FgCmsMapElement.prototype.saveElementCallback = function (d) {
        var CmsMapElement = new FgCmsMapElement();
        FgDirtyFields.init('mapElement', { saveChangeSelector: "#save_changes, #save_bac", setInitialHtml: false, discardChangesCallback: CmsMapElement.discardAfterSave });
        FgMapSettings.mapAutoComplete();
    };
    FgCmsMapElement.prototype.discardChangesCallback = function () {
        FgMapSettings.mapAutoComplete();
        var CmsMapElement = new FgCmsMapElement();
        CmsMapElement.handleSpinner();
        $('form input[name=mapDisp], [name=mapMarker]').unwrap().unwrap();
        FgFormTools.handleUniform();
    };
    FgCmsMapElement.prototype.discardAfterSave = function () {
        var existingMapStyle = $("#mapStyleHidden").val();
        var existingMarkerValue = $("#mapMarkerHidden").val();
        $("#" + existingMapStyle).attr('checked', 'checked');
        if (existingMarkerValue == 1) {
            $("#mapMarker").parent('span').addClass('checked');
        }
        else {
            $("#mapMarker").parent('span').removeClass('checked');
        }
        FgFormTools.handleUniform();
    };
    FgCmsMapElement.prototype.isValidForm = function (data, location, mapHeight) {
        var _this = this;
        var validateLocation = 0;
        var latlng = new google.maps.LatLng(data.latitude, data.longitude);
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ 'location': latlng }, function (results, status) {
            if (status === 'OK') {
                validateLocation = 1;
            }else{
                $('#mapLat').val('');
                $('#mapLng').val('');
            }
            _this.saveMap(data, validateLocation);
        });
    };
    FgCmsMapElement.prototype.saveMap = function (data, validLocation) {
        var validation = 1;
        if (data.location == '' || validLocation == 0) {
            var validLocationMessage = (data.location == '') ? jstranslations.VALIDATION_THIS_FIELD_REQUIRED : invalidMapLocationMessage;
            $('.fg-cms-map-location-error-block').remove();
            $('form#mapElement input#locAutoComp').parent().addClass('has-error');
            $('<span class="help-block fg-cms-map-location-error-block fg-marg-top-5">' + validLocationMessage + '</span>').insertAfter($('form#mapElement input#locAutoComp'));
            $('.fg-cms-map-location').addClass('has-error');
            validation = 0;
        }
        if (data.mapHeight == '') {
            $('.fg-cms-map-height-error-block').remove();
            $('form#mapElement input#mapHeight').parent().addClass('has-error');
            $('<span class="help-block fg-marg-top-5 fg-cms-map-height-error-block">' + jstranslations.VALIDATION_THIS_FIELD_REQUIRED + '</span>').insertAfter($('form#mapElement input#mapHeight'));
            $('.fg-cms-map-height').addClass('has-error');
            validation = 0;
        }
        if (validation == 1) {
            var saveType = (data.currentSelectedButton == 'save_changes') ? 'save' : 'saveBack';
            var mapData = { 'longitude': data.longitude, 'latitude': data.latitude, 'location': data.location, 'boxId': boxId, 'elementId': elementId, 'sortOrder': sortOrder, 'pageId': pageId, 'mapHeight': data.mapHeight, 'mapDisplay': data.mapDisplay, 'saveType': saveType, 'mapMarker': data.mapMarker, 'mapZoomValue': data.mapZoomValue };
            FgDirtyFields.removeAllDirtyInstances();
            FgXmlHttp.post(saveMapElement, mapData, false, CmsMapElement.saveElementCallback);
            $('#save_changes,#save_bac').attr('disabled', 'disabled');
        }
    };
    FgCmsMapElement.prototype.handleSpinner = function () {
        $('#mapZoomDiv').spinner({ step: 1, min: 0, max: 20 });
        $('.spinDiv').find('.btn').on("click", function () {
            $(this).parent().parent().find('input').change();
        });
    };
    return FgCmsMapElement;
}());
var FgCmsMapElementLog = (function () {
    function FgCmsMapElementLog() {
    }
    FgCmsMapElementLog.prototype.init = function () {
        this.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    };
    FgCmsMapElementLog.prototype.dataTableOpt = function () {
        var columnDefs = [];
        var currentDateFormat = FgLocaleSettingsData.momentDateTimeFormat;
        var i = 0;
        columnDefs.push({ "name": "date", "type": "moment-" + currentDateFormat, "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['date'];
                row.displayData = '&nbsp;&nbsp;' + row['date'];
                return row;
            }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' } });
        columnDefs.push({ "name": "option", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">' + statusTranslations[row['status']] + '</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">' + statusTranslations[row['status']] + '</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">' + statusTranslations[row['status']] + '</span>' : '-'));
                var type = (row['type'] === 'element') ? statusTranslations.element : statusTranslations.page_assignment;
                row.sortData = row['type'];
                row.displayData = type + flag;
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' } });
        columnDefs.push({ "name": "value_before", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueBefore'];
                row.displayData = row['valueBefore'];
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' } });
        columnDefs.push({ "name": "value_after", width: '20%', "targets": i++, data: function (row, type, val, meta) {
                row.sortData = row['valueAfter'];
                row.displayData = row['valueAfter'];
                return row;
            }, render: { "_": 'sortData', width: '20%', "display": 'displayData', 'filter': 'sortData' } });
        columnDefs.push({ "name": "edited_by", "targets": i++, data: function (row, type, val, meta) {
                var profileLink = profilePath.replace("dummy", row['activeContactId']);
                row.sortData = row['contact'];
                row.displayData = (row['activeContactId'] && row['isStealth'] == false) ? '<div class="fg-contact-wrap"><a class="fg-dev-contactname" href="' + profileLink + '">' + row['contact'] + '</a></div>' : '<span class="fg-table-reply">' + row['contact'] + '</span>';
                return row;
            }, render: { "_": 'sortData', "display": 'displayData', 'filter': 'sortData' } });
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
    };
    return FgCmsMapElementLog;
}());
//# sourceMappingURL=Fg_cms_map_element.js.map