 /**
  * Handle calendar appointment create
  */
$(function () {
    $(".fg-action-menu-wrapper").FgPageTitlebar({
        title: true,
        tab: false,
        row2: true,
        languageSwitch: true
    });
    
    CalendarTemplate.init();
    Calendar.init();
    //CalendarDirty.init();
    CalendarSelect.init();
    CalendarSaveCancel.save();
    CalendarSaveCancel.discard();
    Calendar.handleAreas();
    Calendar.handleRepeatSelect();
    Calendar.handleIsAllDay();
    Calendar.handleRepeat();
    Calendar.handleLangSwitch();
    CalendarCategorySavePopup.categorysave();
    Calendar.handleDates(moment($('.eventStartDate').val(), FgLocaleSettingsData.momentDateFormat));
    var days = calendarDateInterval.init();
    $(".eventStartDate").datetimepicker().on("change", function() {
        Calendar.handleDates(moment($(this).val(), FgLocaleSettingsData.momentDateFormat));
        if(days >= 0){
            var newDate = moment($(this).val(), FgLocaleSettingsData.momentDateTimeFormat).add(days, 'days').add(1, 'h');
            $('.eventEndDate').datetimepicker('setDate', newDate.toDate()).trigger('change');
        }
        else {
            $('.eventEndDate').datetimepicker('setDate', moment($(this).val(), FgLocaleSettingsData.momentDateTimeFormat).toDate()).trigger('change');
        }
    });
});

var Calendar = {
    init: function(){
        FgFormTools.handleUniform();
        FgFormTools.handleDatepicker();
        Calendar.handleDateTimepicker();
        $('#until-date-icon').on('click', function(){
            var isDisabled = $('.fg-event-until').is(':disabled');
            if (!isDisabled) {
                $('#until-date').datepicker('show');
            }
        });
        $('select.selectpicker').addClass('fg-event-select');
        FgMapSettings.mapAutoComplete();
        CalendarRepeat.neverRepeat();
        CalendarDescription.initDescEditor(clubLanguages);
        CalendarDescription.initDescEditorToggler(clubLanguages);
        Pagetitle.switchActive();
        FgInternal.toolTipInit();
        Calendar.handleScope();
        
        $('.fg-repeat-interval').val('1');
        
        $('.fg-event-areas-global-div').hide();

        $('.fg-check-share-lower').attr('disabled',true);
        jQuery.uniform.update('.fg-check-share-lower');
        if($('input[id=optionsMonthly]:checked').val()==='option1'){
            $('.fg-monthly-byday-interval').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-monthly-byday').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
        }
        else if($('input[id=optionsMonthly]:checked').val()==='option2'){
            $('.fg-monthly-bymonthday').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
        }
        if($('input[id=optionsAnnually]:checked').val()==='option1'){
            $('.fg-annualy-byday-interval').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
            $('.fg-annualy-byday').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
        }
        else if($('input[id=optionsAnnually]:checked').val()==='option2'){
            $('.fg-annualy-bymonthday').attr('disabled', true);
            $('select.selectpicker').selectpicker('refresh');
        }
        $('#optionsMonthly[value=option1]').attr('checked',true);
        jQuery.uniform.update('#optionsMonthly');
        $('#optionsAnnually[value=option1]').attr('checked',true);
        jQuery.uniform.update('#optionsAnnually');
        
        Calendar.selectAnnualy();
        Calendar.selectMonthly();
        $('.fg-url').on('blur', function(){
            var urlVal = $('.fg-url').val();
            if ((urlVal != '') && (!urlVal.match(/^[a-zA-Z]+:\/\//))) {
                urlVal = 'http://' + urlVal;
                $(".fg-url").val(urlVal);
            }
        });
        Calendar.uploadInit();
        Calendar.deleteServerContent();
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
        Calendar.initUploader(calendarUploaderOptions);
        $('.fg-cal-file-upload').on('click', function(){
            $('#file-uploader').trigger('click');
        });
        $('.fg-cal-browse-server').on('click', function(){
            window.toSend = $(this);
            window.upload = 'calendarUploadCreate';
            localStorage.setItem('calenderBrowseServer', 'calendarUploadCreate');
            window.open(browseServerPath, "", "width=1000, height=1000");
        });
    },
    initElements: function (uploadedObj,data){
        var rowId = data.fileid;
        if(rowId)
        {
            $('#'+rowId).find('.fg-delete').click(function(){
                $(this).parents('.filecontent').remove();
                Calendar.handleActionButtonContainer();
            });
            
            Calendar.handleActionButtonContainer();
        }
    },
    onFileSelect: function(){
        var serverFile = JSON.parse(localStorage.getItem('calenderBrowseServer'));
        var fileSize = FgFileUpload.formatFileSize(parseInt(serverFile.size));
        //append to the interface
        var appendContent = $('<li class="fg-calendar-upload-item fg-clear filecontent" id="'+serverFile.id+'">' +
        '<div class="col-sm-12 fg-calendar-item-name">' + 
        '<div id="fg-uploadcalendar-name" class="row fg-uploadcalendar-name"><div class="col-md-9"><a target="_blank" href="'+serverFile.url+'">'+serverFile.name+'</a> </div><div class="col-md-3"><span class="fg-file-size"> '+fileSize+' </span></div></div></div>' +
        '<input id="fg-uploadcalendar-name" name="fileName" type="hidden" placeholder="" value="'+serverFile.name+'" class="form-control fg-uploadcalendar-name" data-key="fileupload.name.'+serverFile.id+'">' +
        '<input class="fg-uploadcalendar-randName" name="randFileName" type="hidden" value="'+serverFile.id+'" data-key="fileupload.randName.'+serverFile.id+'">' +
        '<input class="fg-uploadcalendar-size" name="fileSize" type="hidden" value="'+serverFile.size+'" data-key="fileupload.size.'+serverFile.id+'">' +
        '<input class="fg-uploadcalendar-newold" name="newold" type="hidden" value="server" data-key="fileupload.newold.'+serverFile.id+'">'+
        '<a href="javascript:void(0)" class="fg-delete" parentid="'+serverFile.id+'"><i class="fa fa-times-circle fa-2x"></i></a>'+
        '</li>');
        $('.fg-upload-area-div').removeClass('hide');
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
    deleteServerContent: function(){
        $('body').on('click','a.fg-delete', function(){
            var attr = $(this).attr('parentid');
            if(attr !== ''){
                $('#'+attr).remove();
                $('.fileCount').val(parseInt($('.fileCount').val())-parseInt(1));
            }
        });
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
    handleDates: function(stDate){
        //day of week (Th,Mo)
        var dayWeek = stDate.format('dd').toUpperCase();
        $(".fg-weekly-byday").val('');
        $(".fg-weekly-byday option[value='" + dayWeek + "']").attr("selected", true);
        $(".fg-monthly-byday option[value='" + dayWeek + "']").attr("selected", true);
        $(".fg-annualy-byday option[value='" + dayWeek + "']").attr("selected", true);
        $('select.selectpicker').selectpicker('refresh');
        //day of month (1,..31)
        var dayMn = stDate.format('D');
        $(".fg-monthly-bymonthday").val('');
        $(".fg-monthly-bymonthday option[value='" + dayMn + "']").attr("selected", true);
        $(".fg-annualy-bymonthday").val('');
        $(".fg-annualy-bymonthday option[value='" + dayMn + "']").attr("selected", true);
        $('select.selectpicker').selectpicker('refresh');
        //Month Number (1,..12)
        var mnNum = stDate.format('M');
        $(".fg-annually-bymonth").val('');
        $(".fg-annually-bymonth option[value='" + mnNum + "']").attr("selected", true);
        $('select.selectpicker').selectpicker('refresh');
        //Week Number of month (1,2,3,4,-1)
        var weekNm = Math.ceil(stDate.date() / 7);
        var wkNm = (weekNm==5 || weekNm==6) ? '-1' : weekNm;
        $(".fg-monthly-byday-interval option[value='" + wkNm + "']").attr("selected", true);
        $(".fg-annualy-byday-interval option[value='" + wkNm + "']").attr("selected", true);
        $('select.selectpicker').selectpicker('refresh');
    },
    //ALL DAY CHECKING
    handleIsAllDay: function(){
        $('body').on('click', '.is_allday', function(){
            var fromDate = $('#from-date').val();
            var toDate = $('#to-date').val();

            if($(this).is(':checked')){
                $('#from-date').val(moment(fromDate, FgLocaleSettingsData.momentDateTimeFormat).format(FgLocaleSettingsData.momentDateFormat));
                $('#to-date').val(moment(toDate, FgLocaleSettingsData.momentDateTimeFormat).format(FgLocaleSettingsData.momentDateFormat));
                Calendar.handleDateTimepicker({
                    minView:2,
                    format:FgLocaleSettingsData.jqueryDateFormat
                    });
            }
            else {
                $('#from-date').val(moment(fromDate, FgLocaleSettingsData.momentDateFormat).format(FgLocaleSettingsData.momentDateTimeFormat));
                $('#to-date').val(moment(toDate, FgLocaleSettingsData.momentDateFormat).format(FgLocaleSettingsData.momentDateTimeFormat));
                Calendar.handleDateTimepicker({})
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
            Calendar.selectAnnualy();
        });
        $(document).on('click', '#optionsMonthly', function () {
            Calendar.selectMonthly();
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
    }
}

var CalendarSaveCancel = {
    save: function(){
        $(document).off('click', '#save_changes');
        $(document).on('click', '#save_changes', function () {
            $('div.has-error').removeClass('has-error');
            $('span.required').remove();
            $('#failcallbackClientSide').hide();
            
            $("form :input").addClass('fg-dev-newfield');
            $("form :input[type=text]:hidden").removeClass('fg-dev-newfield');
            $("form :input[disabled]").removeClass('fg-dev-newfield');
            $('div.fg-event-select:hidden').parent().find('select.fg-event-select').removeClass('fg-dev-newfield');
            $('div.fg-event-areas:visible').parent().find('select.fg-event-areas').addClass('fg-dev-newfield');
            $('div.fg-event-areas-global:visible').parent().find('select.fg-event-areas-global').addClass('fg-dev-newfield');
            $("form :input[data-lang]").addClass('fg-dev-newfield');
            $("#mapLat").addClass('fg-dev-newfield');
            $("#mapLng").addClass('fg-dev-newfield');
            
            var error = CalendarValidation.init();
            
            if(error==1){
                $('.btlang').removeClass('active');
                $('#'+defaultlanguage).addClass('active');
                FgUtility.showTranslation(defaultlanguage);
                return false;
            } else{
                var objectCalendarData = FgInternalParseFormField.fieldParse();
                stringifyData = JSON.stringify(objectCalendarData);
                
                //Set the start date to local storage for showing next time
                var fromDateObj = moment($('#from-date').val(), FgLocaleSettingsData.momentDateFormat)
                localStorage.setItem(FgLocalStorageNames.calendar.selectedDate,fromDateObj.format('YYYY-MM-DD'));
                FgXmlHttp.post(calendarAppointmentSave, {saveData: stringifyData}, false, false);
            }
        });
    },
    discard: function(){
        $(document).off('click', '#reset_changes');
        $(document).on('click', '#reset_changes', function () {
            window.location = calendarView;
        });
    }
}
var CalendarCategorySavePopup = {
    categorysave: function(){
        $('body').on('click', '.fg-dev-cat', function () {
                var rand = $.now();
                $.post(calendarCategorySave, {'catId':rand,'defaultLang':defaultlanguage,'noParentLoad':true  }, function(data) {             
                FgModelbox.showPopup(data);         
            });      
        });
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
var CalendarRepeat = {
    neverRepeat: function(){
        $('.fg-rule-annualy').hide();
        $('.fg-rule-daily').hide();
        $('.fg-rule-monthly').hide();
        $('.fg-rule-weekly').hide();
        $('.fg-repeat-cases').hide();
        $('.fg-event-until').attr('disabled', true);
        $('.fg-event-until-div').addClass('fg-disabled');
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
var CalendarDescription = {
    initDescEditor: function(clubLanguages, settings){
        var configArray = {};
        if(settings == 'advanced')
            configArray.toolbar = ckEditorConfig.mailAdvanced;
        else
            configArray.toolbar = ckEditorConfig.mailSimple;
        configArray.language = jstranslations.localeName;
        configArray.disallowedContent = 'script; *[on*]';
        configArray.filebrowserBrowseUrl= filemanagerDocumentBrowse;
        configArray.filebrowserImageBrowseUrl= filemanagerImageBrowse;

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
var CalendarDirty = {
    init: function(){
        FgDirtyFields.init('fg-calendar-create-form', {
            dirtyFieldSettings :{
            }, 
                enableDiscardChanges : false
        });
    }
}
calendarDateInterval = {
    init: function(){
        var start = $(".eventStartDate").val();
        var end = $(".eventEndDate").val();
        var startObj = moment(start, FgLocaleSettingsData.momentDateFormat);
        var endObj = moment(end, FgLocaleSettingsData.momentDateFormat);
        var days = endObj.diff(startObj, 'days')
        
        return days;
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