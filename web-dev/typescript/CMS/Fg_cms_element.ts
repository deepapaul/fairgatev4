/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgCmsElement {
    
    constructor(public options: any) {
        
    }

    public renderContent() {
        $('#cmsAddElementHeadingEdit').removeClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').addClass('fg-dis-none');
        $('#paneltab li').removeClass('active');
        $('#fg_tab_elementContent').addClass('active');
        $('.fg-lang-tab').removeClass('invisible');
    }

    public renderLog(){
        var CmsElementLog = new FgCmsElementLog();
    	CmsElementLog.init();
        $('#cmsAddElementHeadingEdit').addClass('fg-dis-none');
        $('#cmsAddElementHeadingLog').removeClass('fg-dis-none');
        $('.fg-lang-tab').addClass('invisible');
    }
    
    public saveElementCallback(d){
        var CmsElement = new FgCmsElement();
        CmsElement.updatePlaceholder(d);
        FgDirtyFields.init('addHeaderElement', { 
            saveChangeSelector : "#save_changes,#save_bac",
            enableDiscardChanges : true,
            setInitialHtml:false,
            discardChangesCallback : function(){$('.selectpicker').selectpicker('refresh');}
        });          
        if (d.saveType === 'saveAndBack') {
            window.location.href = contentEditPagePath;
        }
        elementId = d.elementId;
        $('#hiddenElementId').val(elementId);
    }
    
    public validateHeading(){
        $('.form-group').removeClass('has-error');
        $('.form-group').find('span.required').remove();
        var err = 0;
        if ($('#headingTitle-' + defaultlanguage).val() === '') {
            $('.headingTitles').parent('div').append('<span class="required">' + jstranslations.VALIDATION_THIS_FIELD_REQUIRED + '</span>');
            $('.headingTitles').closest('div.form-group').addClass('has-error');
            $('.btlang ').removeClass('active');
            $('#' + defaultlanguage).addClass('active');
            FgUtility.showTranslation(defaultlanguage);
            err =1;
        }
        if($('select#headingSize').selectpicker('val') === ''){
            $('#headingSize').parent('div').append('<span class="required">' + jstranslations.VALIDATION_THIS_FIELD_REQUIRED + '</span>');
            $('#headingSize').closest('div.form-group').addClass('has-error');
            err =1;
        }
        if(err == 1){
            return false;
        }
        return true;
    }
    
    public discardChangesCallback(){
        $('.bootstrap-select').remove();
        $('select.selectpicker').selectpicker();        
    }
    
    public updatePlaceholder(data){
        $.each($('input.headingTitles'), function (i, obj) { 
                $(this).attr('placeholder', data['elementDetails']['mainTitle']);
        });
    }
	
}

class FgCmsElementLog{
    
    
    public init(){
        this.dataTableOpt();
        FgDatatable.listdataTableInit('datatable-element-log-list', datatableOptions);
    }
    
    public reload(){
        listTable.ajax.reload();
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
                var flag = (row['status'] === 'added') ? '&nbsp;<span class="label label-sm fg-color-added">'+elementTrans.added+'</span>' : ((row['status'] === 'changed') ? '&nbsp;<span class="label label-sm fg-color-changed">'+elementTrans.changed+'</span>' : ((row['status'] === 'deleted') ? '&nbsp;<span class="label label-sm fg-color-removed">'+elementTrans.removed+'</span>' : ''));
                var type = (row['type'] === 'element') ? elementTrans.element : elementTrans.page_assignment;
                row.sortData = row['type'];
                row.displayData = type+ flag;
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
                var profileLink = profilePath.replace("**placeholder**", row['activeContactId']);
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

class FgCmsIframe{
    
    constructor(public options: any) {
    }
    
    public renderContent() {
        $('#cmsAddElementIframeEdit').removeClass('fg-dis-none');
        $('#cmsAddElementIframeLog').addClass('fg-dis-none');
        $('#paneltab li').removeClass('active');
        $('#fg_tab_cmsIframeElementContent').addClass('active');
    }
    
    public renderLog(){
        var CmsElementLog = new FgCmsElementLog();
    	CmsElementLog.init();
        $('#cmsAddElementIframeEdit').addClass('fg-dis-none');
        $('#cmsAddElementIframeLog').removeClass('fg-dis-none');
    }
        
    public validIframe (){
        var iframeCode = $('#cmsIframeCodeText').val();
        try {
            if( typeof $(iframeCode).attr('src')!=='undefined' && $(iframeCode).attr('src')!=='' && $(iframeCode).prop("tagName") === 'IFRAME' && (/^((https?|s?ftp):\/\/){0,1}(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test($(iframeCode).attr('src'))) ){
                 return true;
            }
        }
        catch(err) {
            console.log('Invalid iframe code');
        }
        return false;
    }
    public importIframeCode (){
        this.popupFormError(false);
        if(!this.validIframe()){
             this.popupFormError(true);
        }else{
            var iFrameCode = $('#cmsIframeCodeText').val();
            
            var iFrameDOMObject = $(iFrameCode);
            var iFrameSrc = iFrameDOMObject.attr('src');
            var iFrameHeight = iFrameDOMObject.attr('height');           
            var iFrameSrcWithHttp = this.appendHttp(iFrameSrc);
            $('#cmsIframeElementUrl').val(iFrameSrcWithHttp);
            if(iFrameHeight) {
                $('#cmsIframeElementHeight').val(iFrameHeight);
            }
            $('#modalIframeElement').modal('hide');
        }  
        this.reCheckDirty();      
    }
    public popupFormError(flag){
        if(flag){
            $('#cmsIframeCodeText').closest('.form-group').addClass('has-error');
             $('#modalIframeElement .alert-danger').removeClass('hide');
        }else{
            $('#cmsIframeCodeText').closest('.form-group').removeClass('has-error');
            $('#modalIframeElement .alert-danger').addClass('hide');
        }
    }
     
    public  appendHttp(urlVal) {
        if ((urlVal != '') && (!urlVal.match(/^[a-zA-Z]+:\/\//))) {
            urlVal = 'http://' + urlVal;
        }
         return urlVal;
    }
    
    public isValidUrl(Url){
        if((/^((https?|s?ftp):\/\/){0,1}(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( Url ))){
            return true;
        }
        return false;
    }
    
    public saveElementCallback(d){
        FgDirtyFields.init('addIframeElementForm', {
            saveChangeSelector : "#save_changes,#save_bac",
            enableDiscardChanges : true,
            setInitialHtml:false
        });
        if (d.saveType === 'saveAndBack') {
            window.location.href = contentEditPagePath;
        }
        elementId = d.elementId;
        $('#hiddenElementId').val(d.elementId);
    }
    
    public validateIframeForm(){
        $('.form-group').removeClass('has-error');
        $('.form-group').find('span.required').remove();
        var err = 0;
        if ($('#cmsIframeElementUrl').val() === '') {
            $('#cmsIframeElementUrl').parent('div').append('<span class="required">' + jstranslations.VALIDATION_THIS_FIELD_REQUIRED + '</span>');
            $('#cmsIframeElementUrl').closest('div.form-group').addClass('has-error');
            err =1;
        }
        if ($('#cmsIframeElementUrl').val() !== '' && ! this.isValidUrl($('#cmsIframeElementUrl').val()) ) {
            $('#cmsIframeElementUrl').parent('div').append('<span class="required">' + elementTrans.error_valid_iframe_url + '</span>');
            $('#cmsIframeElementUrl').closest('div.form-group').addClass('has-error');
            err =1;
        }
        if ($('#cmsIframeElementHeight').val() === '') {
            $('#cmsIframeElementHeight').parent('div').append('<span class="required text-pre">' + jstranslations.VALIDATION_THIS_FIELD_REQUIRED + '</span>');
            $('#cmsIframeElementHeight').closest('div.form-group').addClass('has-error');
            err =1;
        }
        if ($('#cmsIframeElementHeight').val() < 0 || isNaN($('#cmsIframeElementHeight').val())) {
            $('#cmsIframeElementHeight').parent('div').append('<span class="required text-pre">' + jstranslations.validateMin + '</span>');
            $('#cmsIframeElementHeight').closest('div.form-group').addClass('has-error');
            err =1;
        }
        if(err == 1){
            return false;
        }
        return true;
    }
    
    public reCheckDirty(){
        $('#cmsIframeElementUrl, #cmsIframeElementHeight').trigger('change');
    }
    
    
    
}

