/**
  * Handle calendar appointment edit
  */
 var repeatOption = '';
$(function () {
    $(".fg-action-menu-wrapper").FgPageTitlebar({
        title: true,
        tab: false,
        row2: true,
        languageSwitch: true
    });
    
    CalendarTemplate.init();
    CalendarEdit.init();
    if(duplicate === ''){
        CalendarDirty.init();
    }
    $('select.selectpicker').addClass('fg-event-select');
    CalendarSelect.init();
    CalendarSaveCancel.save();
    CalendarSaveCancel.discard(result_data);
    CalendarEdit.handleAreas();
    CalendarEdit.handleRepeatSelect();
    CalendarEdit.handleIsAllDay();
    CalendarEdit.handleRepeat();
    CalendarEdit.handleLangSwitch();
    CalendarCategorySavePopup.categorysave();
    FgLanguageSwitch.checkMissingTranslation(defaultlanguage);
});
var CalendarEdit = {
    init: function(){
        FgFormTools.handleUniform();
        var extraSettings = {
            orientation: "bottom auto"
        };
        FgFormTools.handleDatepicker(extraSettings);
        if(isAllday == 1){
            var fromDate = $('#from-date').val();
            var toDate = $('#to-date').val();
            $('#from-date').val(moment(fromDate, FgLocaleSettingsData.momentDateTimeFormat).format(FgLocaleSettingsData.momentDateFormat));
            $('#to-date').val(moment(toDate, FgLocaleSettingsData.momentDateTimeFormat).format(FgLocaleSettingsData.momentDateFormat));
            CalendarEdit.handleDateTimepicker({
                    minView: 2,
                    format: FgLocaleSettingsData.jqueryDateFormat
                });
        }else{
             CalendarEdit.handleDateTimepicker();
        }
        
        $('#until-date-icon').on('click', function(){
            var isDisabled = $('.fg-event-until').is(':disabled');
            if (!isDisabled) {
                $('#until-date').datepicker('show');
            }
        });
        $('select.selectpicker').addClass('fg-event-select');
        //$('.timepicker').timepicker();
        FgMapSettings.mapAutoComplete();
        
        CalendarDescription.initDescEditor(clubLanguages);
        CalendarDescription.initDescEditorToggler(clubLanguages);
        Pagetitle.switchActive();
        FgInternal.toolTipInit();
        CalendarEdit.handleScope();
        CalendarEdit.loadValues();
        
        //Set end date when start date is changed
        var minutes = calendarDateInterval.init();
        $(".eventStartDate").datetimepicker().on("change", function() {   
            CalendarEdit.handleDates(moment($(this).val(), FgLocaleSettingsData.momentDateFormat));
            if(minutes >= 0){
                var newDate = moment($(this).val(), FgLocaleSettingsData.momentDateTimeFormat).add(minutes,'minutes');
                $('.eventEndDate').datetimepicker('setDate', newDate.toDate()).trigger('change');
            }
            else {
                $('.eventEndDate').datetimepicker('setDate', $(this).val()).trigger('change');
            }
        });
        $('.fg-url').on('blur', function(){
            var urlVal = $('.fg-url').val();
            if ((urlVal != '') && (!urlVal.match(/^[a-zA-Z]+:\/\//))) {
                urlVal = 'http://' + urlVal;
                $(".fg-url").val(urlVal);
            }
        });
        $('.fg-upload-area-div').removeClass('hide');
        CalendarEdit.uploadInit();
        CalendarEdit.delete();
        CalendarEdit.deleteServerContent();
    },
    handleDateTimepicker: function(extraSettings) {
        var defaultSettings = {
            language: jstranslations.localeName,
            format: FgLocaleSettingsData.jqueryDateTimeFormat,
            autoclose: true,
            weekStart: 1
        };
        var dateSettings = $.extend(true, {}, defaultSettings, extraSettings);
        $('.datetimepicker').datetimepicker('remove');
        $('.datetimepicker').datetimepicker(dateSettings);
        $('body').on('click', '.fg-datetimepicker-icon', function() {
            $(this).siblings('.datetimepicker').datetimepicker('show');
        });
    },
    uploadInit: function(){
        CalendarEdit.initUploader(calendarUploaderOptions);
        $('.fg-cal-file-upload').on('click', function(){
            $('#file-uploader').trigger('click');
        });
        $('.fg-cal-browse-server').on('click', function(){
            window.toSend = $(this);
            //localStorage.removeItem(localStorage.calenderBrowseServer);
            window.upload = 'calendarUpload';
            localStorage.setItem('calenderBrowseServer', 'calendarUpload');
            window.open(browseServerPath, "", "width=1000, height=1000");
        });
    },
    onFileSelect: function(){
        var serverFile = JSON.parse(localStorage.getItem('calenderBrowseServer'));
        var fileSize = FgFileUpload.formatFileSize(parseInt(serverFile.size));
        //append to the interface
        var appendContent = $('<li class="fg-calendar-upload-item fg-clear filecontent" id="'+serverFile.id+'">' +
        '<div class="col-sm-12 fg-calendar-item-name">' + 
        '<div id="fg-uploadcalendar-name" class="row fg-uploadcalendar-name"> <div class="col-md-9"><a target="_blank" href="'+serverFile.url+'">'+serverFile.name+'</a></div><div class="col-md-3"> <span class="fg-file-size"> '+fileSize+' </span></div></div></div>' +
        '<input id="fg-uploadcalendar-name" name="fileName" type="hidden" placeholder="" value="'+serverFile.name+'" class="form-control fg-uploadcalendar-name" data-key="fileupload.name.'+serverFile.id+'">' +
        '<input class="fg-uploadcalendar-randName" name="randFileName" type="hidden" value="'+serverFile.id+'" data-key="fileupload.randName.'+serverFile.id+'">' +
        '<input class="fg-uploadcalendar-size" name="fileSize" type="hidden" value="'+serverFile.size+'" data-key="fileupload.size.'+serverFile.id+'">' +
        '<input class="fg-uploadcalendar-newold" name="newold" type="hidden" value="server" data-key="fileupload.newold.'+serverFile.id+'">'+
        '<a href="javascript:void(0)" class="fg-delete" parentid="'+serverFile.id+'"><i class="fa fa-times-circle fa-2x"></i></a>'+
        '</li>');
        $(window.toSend).parents('.fg-calendar-upload-wrapper ').find('.fg-calendar-upload-items').append(appendContent);
        $('.fileCount').val(parseInt($('.fileCount').val())+parseInt(1));
    },
    setErrorMessage: function(uploadObj, data) {
        var template = $('#'+calendarUploaderOptions.validationErrorTemplateId).html();
        var result = _.template(template, {error : data.result.error,name:data.result.name });
        $('#'+data.fileid).find('.fg-replacewith-errormsg').html(result);
        $('#'+data.fileid).addClass('has-error');
        $('#'+data.fileid+" input:hidden").remove();
    },
    delete: function(){
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function() {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('li#' + parentId);
            $(parentDiv).toggleClass('inactiveblock');
        });
    },
    deleteServerContent: function(){
        $('body').on('click','a.fg-delete', function(){
            var attr = $(this).attr('parentid');
            if(attr !== ''){
                $('#'+attr).remove();
                $('.fileCount').val(parseInt($('.fileCount').val())-parseInt(1));
            }
        });
    },
    initElements: function (uploadedObj,data){
        var rowId = data.fileid;
        if(rowId)
        {
            $('#'+rowId).find('.fg-delete').click(function(){
                $(this).parents('.filecontent').remove();
                CalendarEdit.handleActionButtonContainer();
            });
            
            CalendarEdit.handleActionButtonContainer();
        }
    },
    handleActionButtonContainer: function(){
        //If any rows exists show else hide
        if($('.filecontent').length > 0){
            $('.fileCount').val($('.filecontent').length);
            $('.fg-upload-area-div').removeClass('hide');
        } else {
            $('.fileCount').val('');
            $('.fg-upload-area-div').addClass('hide');
            $('#filemanager-upload-error-container').html('');
        }
    },
    initUploader: function(settings){
        uploaderObj = FgFileUpload.init($('#file-uploader'), settings);
    },
    //Load all values
    loadValues: function(){
        $('.fg-check-share-lower').attr('disabled',true);
        jQuery.uniform.update('.fg-check-share-lower');

        $('.fg-repeat-types').val(freq);
        CalendarSelect.init();
        //Load all day
        if(isAllday == 1){
            $('.is_allday').attr('checked', true);
            jQuery.uniform.update('.is_allday');
        }
        //Load Repeat Radio button
        if(freq === 'NEVER' || freq === '' || freq === 'DAILY' || freq === 'WEEKLY'){
            $('#optionsMonthly[value=option1]').attr('checked',true);
            jQuery.uniform.update('#optionsMonthly');
            $('#optionsAnnually[value=option1]').attr('checked',true);
            jQuery.uniform.update('#optionsAnnually');

            CalendarEdit.selectAnnualy();
            CalendarEdit.selectMonthly();
        }
        if(freq === 'MONTHLY'){
            $('#optionsAnnually[value=option1]').attr('checked',true);
            jQuery.uniform.update('#optionsAnnually');
            
            CalendarEdit.selectAnnualy();
        }
        if(freq === 'YEARLY'){
            $('#optionsMonthly[value=option1]').attr('checked',true);
            jQuery.uniform.update('#optionsMonthly');
            
            CalendarEdit.selectMonthly();
        }
        //Load repeat cases
        if(freq === 'DAILY') {
            $('.fg-day').val(interval);
        }
        else if(freq === 'WEEKLY') {
            $('.fg-week').val(interval);
            $.each(byDay.split(","), function(i,e){
                $(".fg-weekly-byday option[value='" + e + "']").attr("selected", true);
            });
            $('select.selectpicker').selectpicker('refresh');
        }
        else if(freq === 'MONTHLY') {
            $('.fg-month').val(interval);            
            if(byMonthDay !=''){
                repeatOption = 'monthly_bymonthday';
                $.each(byMonthDay.split(","), function(i,e){
                    $(".fg-monthly-bymonthday option[value='" + e + "']").attr("selected", true);
                });
                $(".fg-monthly-byday").attr('disabled', true);
                $(".fg-monthly-byday-interval").attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');
                $('#optionsMonthly[value=option2]').attr('checked', false);
                jQuery.uniform.update('#optionsMonthly[value=option2]');
                $('#optionsMonthly[value=option1]').attr('checked', true);
                jQuery.uniform.update('#optionsMonthly[value=option1]');
            }
            else {
                repeatOption = 'monthly_byday';
                var dayRule = byDay.split('').reverse().slice(0,2).reverse().join('');
                var byDayInterval = byDay.replace(dayRule,'');
                $(".fg-monthly-byday option[value='" + dayRule + "']").attr("selected", true);
                $(".fg-monthly-byday-interval option[value='" + byDayInterval + "']").attr("selected", true);
                $(".fg-monthly-bymonthday").attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');

                $('#optionsMonthly[value=option1]').attr('checked', false);
                jQuery.uniform.update('#optionsMonthly[value=option1]');
                $('#optionsMonthly[value=option2]').attr('checked', true);
                jQuery.uniform.update('#optionsMonthly[value=option2]');
            }
        }
        else if(freq === 'YEARLY') {
            $('.fg-year').val(interval);
            $.each(byMonth.split(","), function(i,e){
                $(".fg-annually-bymonth option[value='" + e + "']").attr("selected", true);
            });
            $('select.selectpicker').selectpicker('refresh');
            if(byMonthDay !=''){
                repeatOption = 'yearly_bymonthday';
                $.each(byMonthDay.split(","), function(i,e){
                    $(".fg-annualy-bymonthday option[value='" + e + "']").attr("selected", true);
                });
                $(".fg-annualy-byday").attr('disabled', true);
                $(".fg-annualy-byday-interval").attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');
                $('#optionsAnnually[value=option2]').attr('checked', false);
                jQuery.uniform.update('#optionsAnnually[value=option2]');
                $('#optionsAnnually[value=option1]').attr('checked', true);
                jQuery.uniform.update('#optionsAnnually[value=option1]');
            }
            else {
                repeatOption = 'yearly_byday';
                var dayRule = byDay.split('').reverse().slice(0,2).reverse().join('');
                var byDayInterval = byDay.replace(dayRule,'');
                $(".fg-annualy-byday option[value='" + dayRule + "']").attr("selected", true);
                $(".fg-annualy-byday-interval option[value='" + byDayInterval + "']").attr("selected", true);
                $(".fg-annualy-bymonthday").attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');

                $('#optionsAnnually[value=option1]').attr('checked', false);
                jQuery.uniform.update('#optionsAnnually[value=option1]');
                $('#optionsAnnually[value=option2]').attr('checked', true);
                jQuery.uniform.update('#optionsAnnually[value=option2]');
            }
        }
        CalendarRepeat.repeatFreq(freq);
        //Load Scope
        $(":radio[value='" + scope + "']").attr('checked', true);
        jQuery.uniform.update('#optionsAnnually[value=option1]');
        //Load areas
        if(scope === 'GROUP') {
            $('.fg-event-areas-div').hide();
            $(".fg-event-areas-global option[value='" + eventAreas + "']").attr("selected", true);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-event-share-with-lower').hide();
        }
        else {
            $('.fg-event-areas-global-div').hide();
            $.each(eventAreas.split("|&&&|"), function(i,e){
                $(".fg-event-areas option[value='" + e + "']").attr("selected", true);
            });
            $(".fg-event-areas option[value='" + clubSelected + "']").attr("selected", true);
            if(clubSelected != '') {
                $('.fg-check-share-lower').attr('disabled',false);
                if(shareWithLower == 1) {
                    $('.fg-check-share-lower').attr('checked',true);
                }
                jQuery.uniform.update('.fg-check-share-lower');
            }
            $('select.selectpicker').selectpicker('refresh');
        }
        //Load catefories
        $.each(categories.split("|&&&|"), function(i,e){
            $(".fg-event-categories option[value='" + e + "']").attr("selected", true);
        });
        $('select.selectpicker').selectpicker('refresh');
        //load location, latitude, longtitude, show on goole maps and URL
        $('#locAutoComp').val(loc);
        $('#mapLat').val(lat);
        $('#mapLng').val(lng);
        if(showMaps == 1){
            $('.fg-show-in-map').attr('checked', true);
            jQuery.uniform.update('.fg-show-in-map');
        }
        $('.fg-url').val(url);
    },
    //ALL DAY CHECKING
    handleIsAllDay: function(){
        $('body').on('click', '.is_allday', function(){
            var fromDate = $('#from-date').val();
            var toDate = $('#to-date').val();
            if($(this).is(':checked')){
                $('#from-date').val(moment(fromDate, FgLocaleSettingsData.momentDateTimeFormat).format(FgLocaleSettingsData.momentDateFormat));
                $('#to-date').val(moment(toDate, FgLocaleSettingsData.momentDateTimeFormat).format(FgLocaleSettingsData.momentDateFormat));
                CalendarEdit.handleDateTimepicker({
                    minView: 2,
                    format: FgLocaleSettingsData.jqueryDateFormat
                });
            }
            else {
                $('#from-date').val(moment(fromDate, FgLocaleSettingsData.momentDateFormat).format(FgLocaleSettingsData.momentDateTimeFormat));
                $('#to-date').val(moment(toDate, FgLocaleSettingsData.momentDateFormat).format(FgLocaleSettingsData.momentDateTimeFormat));
                CalendarEdit.handleDateTimepicker({})
            }
        });
    },
    //SCOPE CHECKING
    handleScope: function(){
        $("input:radio[name=scope]").click(function() {
            var value = $(this).attr('id');
            if(value === 'group'){
                $('.fg-event-areas-global-div').show();
                $('.fg-event-areas-div').hide();
                $('.fg-event-share-with-lower').hide();
            }
            else {
                $('.fg-event-areas-global-div').hide();
                $('.fg-event-areas-div').show();
                $('.fg-event-share-with-lower').show();
            }
        });
    },
    //REPEAT CASES
    handleRepeat: function(){
        $('body').on('change', '.fg-repeat-types', function(){
            $(".fg-rule-label").removeClass('text-red');
            $('span.rule.required').remove();
            //Preselect repeat datas
            CalendarEdit.handleDates(moment($('.eventStartDate').val(), FgLocaleSettingsData.momentDateFormat));
            var value = $(this).val();
            if(value === 'DAILY') {
                $('.fg-repeat-cases').show();
                $('.fg-rule-daily').show();
                $('.fg-rule-weekly').hide();
                $('.fg-rule-monthly').hide();
                $('.fg-rule-annualy').hide();
                $('.fg-event-until').attr('disabled', false);
                $('.fg-event-until-div').removeClass('fg-disabled');
            }
            else if(value === 'WEEKLY') {
                $('.fg-repeat-cases').show();
                $('.fg-rule-weekly').show();
                $('.fg-rule-daily').hide();
                $('.fg-rule-monthly').hide();
                $('.fg-rule-annualy').hide();
                $('.fg-event-until').attr('disabled', false);
                $('.fg-event-until-div').removeClass('fg-disabled');
            }
            else if(value === 'MONTHLY') {
                $('.fg-repeat-cases').show();
                $('.fg-rule-monthly').show();
                $('.fg-rule-daily').hide();
                $('.fg-rule-weekly').hide();
                $('.fg-rule-annualy').hide();
                $('.fg-event-until').attr('disabled', false);
                $('.fg-event-until-div').removeClass('fg-disabled');
            }
            else if(value === 'YEARLY') {
                $('.fg-repeat-cases').show();
                $('.fg-rule-annualy').show();
                $('.fg-rule-daily').hide();
                $('.fg-rule-monthly').hide();
                $('.fg-rule-weekly').hide();
                $('.fg-event-until').attr('disabled', false);
                $('.fg-event-until-div').removeClass('fg-disabled');
            }
            else if(value === 'NEVER') {
                CalendarRepeat.neverRepeat();
            }
        });
    },
    //Lang Switch
    handleLangSwitch: function(){
        $(document).off('click', 'button[data-elem-function=switch_lang]');
            /* function to show data in different languages on switching language */
        $(document).on('click', 'button[data-elem-function=switch_lang]', function () {
            selectedLang = $(this).attr('data-selected-lang');
            FgUtility.showTranslation(selectedLang);
        });
    },
    handleAreas: function(){
        $(document).on('change', '.fg-event-areas', function () {
            if($.inArray('Club', $(this).val())===0){
                $('.fg-check-share-lower').attr('disabled',false);
                jQuery.uniform.update('.fg-check-share-lower');
            }
            else{
                $('.fg-check-share-lower').attr('disabled',true);
                jQuery.uniform.update('.fg-check-share-lower');
            }
        });
    },
    handleRepeatSelect: function(){
        $(document).on('click', '#optionsAnnually', function () {
            if($('input[id=optionsAnnually]:checked').val()==='option1'){
                $('.fg-annualy-byday-interval').attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');
                $('.fg-annualy-byday').attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');
                $('.fg-annualy-bymonthday').attr('disabled', false);
                $('select.selectpicker').selectpicker('refresh');
            }
            else if($('input[id=optionsAnnually]:checked').val()==='option2'){
                $('.fg-annualy-bymonthday').attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');
                $('.fg-annualy-byday-interval').attr('disabled', false);
                $('select.selectpicker').selectpicker('refresh');
                $('.fg-annualy-byday').attr('disabled', false);
                $('select.selectpicker').selectpicker('refresh');
            }
        });
        $(document).on('click', '#optionsMonthly', function () {
            if($('input[id=optionsMonthly]:checked').val()==='option1'){
                $('.fg-monthly-byday-interval').attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');
                $('.fg-monthly-byday').attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');
                $('.fg-monthly-bymonthday').attr('disabled', false);
                $('select.selectpicker').selectpicker('refresh');
            }
            else if($('input[id=optionsMonthly]:checked').val()==='option2'){
                $('.fg-monthly-bymonthday').attr('disabled', true);
                $('select.selectpicker').selectpicker('refresh');
                $('.fg-monthly-byday-interval').attr('disabled', false);
                $('select.selectpicker').selectpicker('refresh');
                $('.fg-monthly-byday').attr('disabled', false);
                $('select.selectpicker').selectpicker('refresh');
            }
        });
    },
    selectAnnualy: function(){
        if($('input[id=optionsAnnually]:checked').val()==='option1'){
            $('.fg-annualy-byday-interval').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-annualy-byday').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-annualy-bymonthday').attr('disabled', false);
            $('select.selectpicker').selectpicker('refresh');
        }
        else if($('input[id=optionsAnnually]:checked').val()==='option2'){
            $('.fg-annualy-bymonthday').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-annualy-byday-interval').attr('disabled', false);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-annualy-byday').attr('disabled', false);
            $('select.selectpicker').selectpicker('refresh');
        }
    },
    selectMonthly: function(){
        if($('input[id=optionsMonthly]:checked').val()==='option1'){
            $('.fg-monthly-byday-interval').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-monthly-byday').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-monthly-bymonthday').attr('disabled', false);
            $('select.selectpicker').selectpicker('refresh');
        }
        else if($('input[id=optionsMonthly]:checked').val()==='option2'){
            $('.fg-monthly-bymonthday').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-monthly-byday-interval').attr('disabled', false);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-monthly-byday').attr('disabled', false);
            $('select.selectpicker').selectpicker('refresh');
        }
    },
    handleDates: function(stDate){
        //day of week (Th,Mo)
        var dayWeek = stDate.format('dd').toUpperCase();
        //day of month (1,..31)
        var dayMn = stDate.format('D');
        //Month Number (1,..12)
        var mnNum = stDate.format('M');
        //Week Number of month (1,2,3,4,-1)
        var weekNm = Math.ceil(stDate.date() / 7);
        var wkNm = (weekNm==5 || weekNm==6) ? '-1' : weekNm;
        if(freq != 'DAILY'){
            $('.fg-day').val('1');
        }
        if(freq != 'WEEKLY'){
            $('.fg-week').val('1');
            $(".fg-weekly-byday").val('');
            $(".fg-weekly-byday option[value='" + dayWeek + "']").attr("selected", true);
            $('select.selectpicker').selectpicker('refresh');
        }
        if(freq != 'MONTHLY'){
            $('.fg-month').val('1');
            $(".fg-monthly-byday option[value='" + dayWeek + "']").attr("selected", true);
            $(".fg-monthly-bymonthday").val('');
            $(".fg-monthly-bymonthday option[value='" + dayMn + "']").attr("selected", true);
            $(".fg-monthly-byday-interval option[value='" + wkNm + "']").attr("selected", true);
            $('select.selectpicker').selectpicker('refresh');
        }
        if(freq != 'YEARLY'){
            $('.fg-year').val('1');
            $(".fg-annualy-byday option[value='" + dayWeek + "']").attr("selected", true);
            $(".fg-annualy-bymonthday").val('');
            $(".fg-annualy-bymonthday option[value='" + dayMn + "']").attr("selected", true);
            $(".fg-annually-bymonth").val('');
            $(".fg-annually-bymonth option[value='" + mnNum + "']").attr("selected", true);
            $(".fg-annualy-byday-interval option[value='" + wkNm + "']").attr("selected", true);
            $('select.selectpicker').selectpicker('refresh');
        }
    },
}
var CalendarSaveCancel = {
    save: function(){
        $(document).off('click', '#edit_app_save');
        $(document).on('click', '#edit_app_save', function () {
            $('div.has-error').removeClass('has-error');
            $('span.required').remove();
            $('#failcallbackClientSide').hide();
            
            $('form :input').parent('div').removeClass('has-error');
            $('#failcallbackClientSide').hide();
            var error = CalendarValidation.init();
            if(error==1){
                $('.btlang').removeClass('active');
                $('#'+defaultlanguage).addClass('active');
                FgUtility.showTranslation(defaultlanguage);
                return false;
            }
            else{
                var otherDet = {'calendar_detail_id':eventId, 'edit_start_date':StartDateTime, 'edit_end_date':endDateTime};
                if(duplicate == 1){
                    $("form :input").addClass('fg-dev-newfield');
                    $("form :input[type=text]:hidden").removeClass('fg-dev-newfield');
                    $("form :input[disabled]").removeClass('fg-dev-newfield');
                    $('div.fg-event-select:hidden').parent().find('select.fg-event-select').removeClass('fg-dev-newfield');
                    $('div.fg-event-areas:visible').parent().find('select.fg-event-areas').addClass('fg-dev-newfield');
                    $('div.fg-event-areas-global:visible').parent().find('select.fg-event-areas-global').addClass('fg-dev-newfield');
                    $("form :input[data-lang]").addClass('fg-dev-newfield');
                    $("#mapLat").addClass('fg-dev-newfield');
                    $("#mapLng").addClass('fg-dev-newfield');
                    var otherDet = {'edit_start_date':StartDateTime, 'edit_end_date':endDateTime};
                }
                else{
                    $('form :input[data-repeat]').removeClass('fg-dev-newfield');                    
                    if($('.fg-repeat-types').val() != freq 
                            || (repeatOption == 'yearly_byday' && $('input[id=optionsAnnually]:checked').val()==='option1') 
                            || (repeatOption == 'yearly_bymonthday' && $('input[id=optionsAnnually]:checked').val()==='option2') 
                            || (repeatOption == 'monthly_byday' && $('input[id=optionsMonthly]:checked').val()==='option1') 
                            || (repeatOption == 'monthly_bymonthday' && $('input[id=optionsMonthly]:checked').val()==='option2') 
                            ){                        
                        if($('.fg-repeat-types').val() === 'DAILY'){
                            $('form :input[data-repeat=day]').addClass('fg-dev-newfield');
                        }
                        if($('.fg-repeat-types').val() === 'WEEKLY'){
                            $('form :input[data-repeat=week]').addClass('fg-dev-newfield');
                        }
                        if($('.fg-repeat-types').val() === 'MONTHLY'){
                            $('form :input[data-repeat=month]').addClass('fg-dev-newfield');
                            if($('input[id=optionsMonthly]:checked').val()==='option1'){
                                $('form :input[data-repeat=month-op1]').addClass('fg-dev-newfield');
                            }
                            else if($('input[id=optionsMonthly]:checked').val()==='option2'){
                                $('form :input[data-repeat=month-op2]').addClass('fg-dev-newfield');
                            }
                        }
                        if($('.fg-repeat-types').val() === 'YEARLY'){
                            $('form :input[data-repeat=year]').addClass('fg-dev-newfield');
                            if($('input[id=optionsAnnually]:checked').val()==='option1'){
                                $('form :input[data-repeat=year-op1]').addClass('fg-dev-newfield');
                            }
                            else if($('input[id=optionsAnnually]:checked').val()==='option2'){
                                $('form :input[data-repeat=year-op2]').addClass('fg-dev-newfield');
                            }
                        }
                    }
                }
                if($('form :input[data-date=start]').hasClass('fairgatedirty')){
                    $('form :input[data-date=start]').addClass('fg-dev-newfield');
                }
                if($('form :input[data-date=end]').hasClass('fairgatedirty')){
                    $('form :input[data-date=end]').addClass('fg-dev-newfield');
                }
                if($('form :input[data-byday=month]').hasClass('fairgatedirty')){
                    $('form :input[data-byday=month]').addClass('fg-dev-newfield');
                }
                if($('form :input[data-byday=year]').hasClass('fairgatedirty')){
                    $('form :input[data-byday=year]').addClass('fg-dev-newfield');
                }
                if($('.locauto').hasClass('fairgatedirty')){
                    if($('.locauto').val() === ''){
                        $("#mapLat").val('');
                        $("#mapLng").val('');
                    }
                    $("#mapLat").addClass('fg-dev-newfield');
                    $("#mapLng").addClass('fg-dev-newfield');
                }
                if($('input[name=scope]:checked').val()!='GROUP'){
                    $('.fg-event-areas-global').removeClass('fg-dev-newfield');
                }
                
                $('.fg-uploadcalendar-newold[value!="old"]').siblings('.fg-uploadcalendar-name').addClass('fg-dev-newfield');
                $('.fg-uploadcalendar-newold[value!="old"]').siblings('.fg-uploadcalendar-randName').addClass('fg-dev-newfield');
                $('.fg-uploadcalendar-newold[value!="old"]').siblings('.fg-uploadcalendar-size').addClass('fg-dev-newfield');
                $('.fg-uploadcalendar-newold[value!="old"]').addClass('fg-dev-newfield');
                $('.fileCount').addClass('fg-dev-newfield');
                if(duplicate =='' && isRepeat === 1) {
                    var dateModified=0;
                    if($('form :input[data-key="start_date.date"]').hasClass('fairgatedirty') || $('form :input[data-key="end_date.date"]').hasClass('fairgatedirty')){
                        dateModified=1;
                    }
                    if ($('body').hasClass('dirty_field_used')) {
                        $('body').removeClass('dirty_field_used');                        
                    }
                    var objectCalendarData = FgInternalParseFormField.fieldParse();
                    var mergedData = $.extend(objectCalendarData, otherDet);
                    stringifyData = JSON.stringify(mergedData);
                    $.post(editPopupPath, {'count':'1','editArr':stringifyData}, function(data){
                        FgModelbox.showPopup(data);
                        if(dateModified==1){
                            $('input[type=radio][value=all][name=publishType]').parents('label').remove();
                        }
                    });
                }
                else{
                    if ($('body').hasClass('dirty_field_used')) {
                        $('body').removeClass('dirty_field_used');                        
                    }
                    var objectCalendarData = FgInternalParseFormField.fieldParse();
                    var mergedData = $.extend(objectCalendarData, otherDet);
                    if(typeof mergedData['is_allday'] === 'undefined') {
                        mergedData['is_allday'] = isAllday ? '1' : '0';
                    }
                    stringifyData = JSON.stringify(mergedData);
                    CalendarSaveCancel.popupSave(stringifyData);
                }
            }            
        });
    },
    popupSave: function(data){
        if(duplicate === ''){
            FgDirtyFields.removeAllDirtyInstances();
        }
        //Set the start date to local storage for showing next time
        var fromDateObj = moment($('#from-date').val(), FgLocaleSettingsData.momentDateFormat)
        localStorage.setItem(FgLocalStorageNames.calendar.selectedDate,fromDateObj.format('YYYY-MM-DD'));
        
        FgXmlHttp.post(calendarAppointmentSave, {saveData: data}, false, false);
    },
    discard: function(){
        //Set the start date to local storage for showing next time
        var fromDateObj = moment($('#from-date').val(), FgLocaleSettingsData.momentDateFormat)
        localStorage.setItem(FgLocalStorageNames.calendar.selectedDate,fromDateObj.format('YYYY-MM-DD'));
        
        $(document).off('click', '#edit_app_reset');
        $(document).on('click', '#edit_app_reset', function () {
            window.location = calendarView;
        });
    }
}
var CalendarValidation = {
    init: function(){
        var err = 0;
        var days = calendarDateInterval.dateTimeCheck()
        if(days === false){
            $('.fg-datetime-wrapper').parent('div').addClass('has-error');
            $('.fg-datetime-left').append('<span class=required>'+startEndDateNotValid+'</span>');
            $('#failcallbackClientSide').show();
            $("html, body").animate({ scrollTop: 0 }, "slow");
            var err = 1;
            return err;
        }
        
        if($('li.has-error').length > 0){
           $('#failcallbackClientSide').show();
           $("html, body").animate({ scrollTop: 0 }, "slow");
           return 1;
        }
        
        if($('#event_name_'+defaultlanguage).val()===''){
            var err = RepeatValidate.notValidGroup('fg-event-name');
            $('.fg-event-name').parent('div').append('<span class=required>'+required+'</span>');
        }
        if($('.eventStartDate').val()===''){
            var err = RepeatValidate.notValidGroup('eventStartDate');
            $('.fg-datetime-left').append('<span class=required>'+required+'</span>');
        }
        if($('.eventEndDate').val()===''){
            var err = RepeatValidate.notValidGroup('eventEndDate');
            $('.fg-datetime-right').append('<span class=required>'+required+'</span>');
        }
        if((($('.fg-event-areas').val()==='')||($('.fg-event-areas').val()===null))&&($('.fg-event-areas').is(':visible'))){
            var err = RepeatValidate.notValidGroup('fg-event-areas');
            $('.fg-event-areas').parent('div').append('<span class=required>'+required+'</span>');
        }
        if(($('.fg-event-areas-global').val()==='')&&($('.fg-event-areas-global').is(':visible'))){
            var err = RepeatValidate.notValidGroup('fg-event-areas-global');
            $('.fg-event-areas-global').parent('div').append('<span class=required>'+required+'</span>');
        }
        if(($('.fg-event-categories').val()==='')||($('.fg-event-categories').val()===null)){
            var err = RepeatValidate.notValidGroup('fg-event-categories');
            $('.fg-event-categories').parent('div').append('<span class=required>'+required+'</span>');
        }
        if($('.fg-repeat-types').val()==='DAILY'){
            if(($('.fg-day').val()==='') || isNaN($('.fg-day').val())){
                var err = RepeatValidate.notValid('fg-day');
                $('.fg-rule-daily').append("<span class='rule required'>"+required+"</span>");
                $(".fg-rule-label").addClass('text-red');
            }
        }
        else if($('.fg-repeat-types').val()==='WEEKLY'){
            if(($('.fg-week').val()==='') || isNaN($('.fg-week').val())){
                var err = RepeatValidate.notValid('fg-week');
                $('.fg-week').parent('div').append("<span class='rule required show'>"+required+"</span>");
                $(".fg-rule-label").addClass('text-red');
            }
            if(($('.fg-weekly-byday').val()==='')||($('.fg-weekly-byday').val()===null)){
                var err = RepeatValidate.notValid('fg-weekly-byday');
                $('.fg-weekly-byday').parent('div').append("<span class='rule required'>"+required+"</span>");
                $(".fg-rule-label").addClass('text-red');
            }
        }
        else if($('.fg-repeat-types').val()==='MONTHLY'){
            if(($('.fg-month').val()==='') || isNaN($('.fg-month').val())){
                var err = RepeatValidate.notValid('fg-month');
                $('.fg-month').parent('div').append("<span class='rule required show'>"+required+"</span>");
                $(".fg-rule-label").addClass('text-red');
            }
            if($('input[id=optionsMonthly]:checked').val()==='option1'){
                if(($('.fg-monthly-bymonthday').val()==='')||($('.fg-monthly-bymonthday').val()===null)){
                    var err = RepeatValidate.notValid('fg-monthly-bymonthday');
                    $(".fg-rule-label").addClass('text-red');
                    //$('.fg-monthly-bymonthday').append("<span class='rule required show'>"+required+"</span>");
                    $(".fg-rule-label").addClass('text-red');
                }
            }
            if($('input[id=optionsMonthly]:checked').val()==='option2'){
                if($('.fg-monthly-byday-interval').val()===''){
                    var err = RepeatValidate.notValid('fg-monthly-byday-interval');
                    $(".fg-rule-label").addClass('text-red');
                }
                if($('.fg-monthly-byday').val()===''){
                    var err = RepeatValidate.notValid('fg-monthly-byday');
                    $(".fg-rule-label").addClass('text-red');
                }
            }
        }
        else if($('.fg-repeat-types').val()==='YEARLY'){
            if(($('.fg-year').val()==='') || isNaN($('.fg-year').val())){
                var err = RepeatValidate.notValid('fg-year');
                $('.fg-year').parent('div').append("<span class='rule required show'>"+required+"</span>");
                $(".fg-rule-label").addClass('text-red');
            }
            if(($('.fg-annually-bymonth').val()==='')||($('.fg-annually-bymonth').val()===null)){
                var err = RepeatValidate.notValid('fg-annually-bymonth');
                $('.fg-annually-bymonth').parent('div').append("<span class='rule required'>"+required+"</span>");
                $(".fg-rule-label").addClass('text-red');
            }
            if($('input[id=optionsAnnually]:checked').val()==='option1'){
                if(($('.fg-annualy-bymonthday').val()==='')||($('.fg-annualy-bymonthday').val()===null)){
                    var err = RepeatValidate.notValid('fg-annualy-bymonthday');
                    //$('.fg-annualy-bymonthday').append("<span class='rule required show'>"+required+"</span>");
                    $(".fg-rule-label").addClass('text-red');
                }
            }
            if($('input[id=optionsAnnually]:checked').val()==='option2'){
                if($('.fg-annualy-byday-interval').val()===''){
                    var err = RepeatValidate.notValid('fg-annualy-byday-interval');
                    $(".fg-rule-label").addClass('text-red');
                }
                if($('.fg-annualy-byday').val()===''){
                   var err =  RepeatValidate.notValid('fg-annualy-byday');
                   $(".fg-rule-label").addClass('text-red');
                }
            }
        }
        if($('.fg-show-in-map').is(':checked')){
            if($('#locAutoComp').val()==''){
                var err = RepeatValidate.notValidGroup('locauto');
                $('.locauto').parent('div').append('<span class=required>'+required+'</span>');
            }
        }
        var urlVal = $(".fg-url").val();
        if(urlVal!=''){
            if(!(/^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(urlVal))){
                var err = RepeatValidate.notValidGroup('fg-url');
                $('.fg-url').parent('div').append('<span class=required>'+invalidURL+'</span>');
            }
        }
        return err;
    }
}
var RepeatValidate = {
    notValid: function(repeatClass){
        $('.'+repeatClass).parent('div').addClass('has-error');
        $('#failcallbackClientSide').show();
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return 1;
    },
    notValidGroup: function(repeatClass){
        $('.'+repeatClass).parents('div .form-group').addClass('has-error');
        $('#failcallbackClientSide').show();
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return 1;
    }
}
var CalendarTemplate = {
    init: function(){
        $('#fg-calendar-appointment').html(result_data);
        $('#fg-calendar-appointment').show();
    }
}
var CalendarSelect = {
    init: function(){
        $('select.selectpicker').selectpicker({noneSelectedText: selectTrans}); 
        $('select.selectpicker').selectpicker('render');
    }
}
var CalendarDirty = {
    init: function(){
        FgDirtyFields.init('fg-calendar-edit-form', {
            dirtyFieldSettings :{
                denoteDirtyForm  : false
            }, 
                enableDiscardChanges : false
        });
    }
}
var CalendarRepeat = {
    neverRepeat: function(){
        $('.fg-rule-annualy').hide();
        $('.fg-rule-daily').hide();
        $('.fg-rule-monthly').hide();
        $('.fg-rule-weekly').hide();
        $('.fg-repeat-cases').hide();
        $('.fg-event-until').attr('disabled', true);
        $('.fg-event-until-div').addClass('fg-disabled');
    },
    repeatFreq: function(freq){
        if(freq === 'DAILY') {
            $('.fg-repeat-cases').show();
            $('.fg-rule-daily').show();
            $('.fg-rule-weekly').hide();
            $('.fg-rule-monthly').hide();
            $('.fg-rule-annualy').hide();
            $('.fg-event-until').attr('disabled', false);
            $('.fg-event-until-div').removeClass('fg-disabled');
        }
        else if(freq === 'WEEKLY') {
            $('.fg-repeat-cases').show();
            $('.fg-rule-weekly').show();
            $('.fg-rule-daily').hide();
            $('.fg-rule-monthly').hide();
            $('.fg-rule-annualy').hide();
            $('.fg-event-until').attr('disabled', false);
            $('.fg-event-until-div').removeClass('fg-disabled');
        }
        else if(freq === 'MONTHLY') {
            $('.fg-repeat-cases').show();
            $('.fg-rule-monthly').show();
            $('.fg-rule-daily').hide();
            $('.fg-rule-weekly').hide();
            $('.fg-rule-annualy').hide();
            $('.fg-event-until').attr('disabled', false);
            $('.fg-event-until-div').removeClass('fg-disabled');
        }
        else if(freq === 'YEARLY') {
            $('.fg-repeat-cases').show();
            $('.fg-rule-annualy').show();
            $('.fg-rule-daily').hide();
            $('.fg-rule-monthly').hide();
            $('.fg-rule-weekly').hide();
            $('.fg-event-until').attr('disabled', false);
            $('.fg-event-until-div').removeClass('fg-disabled');
        }
        else {
            CalendarRepeat.neverRepeat();
        }
    }
}
var CalendarCategorySavePopup = {
    categorysave: function(){
        $('body').on('click', '.fg-dev-cat', function () {
             var rand = $.now();
             $.post(calendarCategorySave, {'catId':rand,'defaultLang':defaultlanguage,'noParentLoad':true }, function(data) {             
             FgModelbox.showPopup(data);         
            });      
            });
    }
    
}
var CalendarDescription = {
    initDescEditor: function(clubLanguages, settings){
        var configArray = {};
        if(settings == 'advanced')
            configArray.toolbar = ckEditorConfig.mailAdvanced;
        else
            configArray.toolbar = ckEditorConfig.mailSimple;
        configArray.language = jstranslations.localeName;
        configArray.disallowedContent = 'script; *[on*]';

        _.each(clubLanguages,function(val,key){
            if(CKEDITOR.instances['calDesc_'+val]) {
                try {
                    //It will cause error when the CKEditor html is replaced manually by FGDirty field, but the distance is not removed
                    CKEDITOR.instances['calDesc_'+val].destroy();
                } catch (error){}
                delete CKEDITOR.instances['calDesc_'+val];
            }
                var editorObj = CKEDITOR.replace( 'calDesc_'+val,configArray); 

                editorObj.on( 'change', function() {
                    $('#calDesc_'+val).attr('value', editorObj.getData()).change();
                });
        });
    },
    initDescEditorToggler: function(clubLanguages){
        $( ".fg-advanced-editor" ).click(function() {
            $( ".fg-advanced-editor" ).hide();
            $( ".fg-simple-editor" ).show();
            CalendarDescription.initDescEditor(clubLanguages, 'advanced');
        });
        $( ".fg-simple-editor" ).click(function() {     
            $( ".fg-advanced-editor" ).show();
            $( ".fg-simple-editor" ).hide();
            CalendarDescription.initDescEditor(clubLanguages, 'simple');
        });
    }
}
var Pagetitle = {
    switchActive : function (){
        $('body').on('click', '.btlang', function () {
            var attr = $(this).attr('data-selected-lang');
            $('.btlang').removeClass('active');
            $(this).addClass('active');

        });
    }
}
calendarDateInterval = {
    init: function(){
        var start = $(".eventStartDate").val();
        var end = $(".eventEndDate").val();
        var startObj = moment(start, FgLocaleSettingsData.momentDateTimeFormat);
        var endObj = moment(end, FgLocaleSettingsData.momentDateTimeFormat);
        var days = endObj.diff(startObj, 'days');
        var min = endObj.diff(startObj, 'minutes');
        
        return min;
    },
    dateTimeCheck: function(){
        var startDate = $(".eventStartDate").val();
        var endDate = $(".eventEndDate").val();
        var start = startDate;
        var end = endDate;
        var startObj = moment(start, FgLocaleSettingsData.momentDateTimeFormat);
        var endObj = moment(end, FgLocaleSettingsData.momentDateTimeFormat);
        if(startObj.format('X') > endObj.format('X')){
            return false;
        } else {
            return true;
        }
    }
}