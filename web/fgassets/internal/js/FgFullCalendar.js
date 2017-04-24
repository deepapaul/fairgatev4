// The file for adding any global js functions
// @todo Loading
var calandarData;
FgFullCalendar = function () {
    var $object;
    var $calendar;
    var _settings = {};
    var _parameters = {};
    var _drapDropRevertFunction;
    var _searchTerm;
    var _refetch;
            
    //The function used to initialize fullcalendar; will ne initiated on sidebar call back
    var _initialize = function (settings,parameters) {
        $object = this;
        _parameters = parameters;
        _settings = jQuery.extend(settings, _defaultSettings());
        _viewSwitch();
        
        //If the local storage is set, render that view
        var previousView = localStorage.getItem(calendarviewStoragename);
        if(previousView == 'month'){
            $('#fg-time-filter').addClass('fg-time-filter-hide');
            $(_settings.monthButton).prop('checked', 'checked') ;  //Set the month option as checked on page loads
            _settings.defaultView = 'month';
            _render();
        } else if(previousView == 'list') {
            $('#fg-time-filter').removeClass('fg-time-filter-hide');
            $(_settings.listButton).prop('checked', 'checked') ;  //Set the month option as checked on page loads
            _renderList();
        } else {
            $('#fg-time-filter').addClass('fg-time-filter-hide');
            $(_settings.weekButton).prop('checked', 'checked') ;  //Set the week option as checked on page loads
            _settings.defaultView = 'agendaWeek';
            _render();
        }
        
    };

    //The default settings needed for the fgFullCalendar
    var _defaultSettings = function () {
        var _defaultSettings =
                {
                    header: {
                        left: 'prev,today,next',
                        right: 'agendaWeek,month,listViewButton'
                    },
                    buttonText: {
                        today: todayText,
                        month: 'Month',
                        week: 'Week'
                    },
                    viewRender: function (view, element) {
                        //Triggered when a new date-range is rendered, or when the view type switches.
                        
                        var calendarElement = $(_settings.calendarElement)
                        //Change the 'Today' to the right and put the 'Header' in the place of the today//
                        //only needed for the initial rendering //
                        if ($('#fg-calendar-title').length == 0) {
                            var defaultToday = $('button.fc-today-button').clone(true);
                            $('button.fc-today-button').replaceWith('<button class="fc-state-default" type="button" id="fg-calendar-title"></button>');
                            $('.fc-next-button').after(defaultToday);
                        }
                        
                        if(view.name == 'agendaWeek'){
                            $('#fg-calendar-title').html(_getTitleForAgendaWeek(view)); //Need to reset on every month/week change
                        } else {
                            $('#fg-calendar-title').html(view.intervalStart.format('MMMM YYYY')); //Need to reset on every month/week change
                        }

                        
                        ///////////////////////////////////////////////////////////////////////////////////


                        //Need to hide the Week/Month/List bar and attach events to the new elements//
                        var defaultCalendarNav = calendarElement.find('.fc-toolbar .fc-right .fc-button-group');
                        defaultCalendarNav.hide();
                        /////////////////////////////////////////////////////////////////////////////////////////////////


                        /// Set the start date and end date for future usage ///
                        _parameters.startDate = view.start.format('YYYY-MM-DD');
                        _parameters.endDate = view.end.format('YYYY-MM-DD');
                        _parameters.range = view.intervalUnit;
                        /////////////////////////////////////////////////////////////////////////////////////////////////    
                        
                        $('.popover').popover('destroy'); //Hide the tootip on week/month view
                        $('.fc-scroller').on( "scroll", function() {
                            $('.popover').popover('destroy');
                        });
                },
                eventRender: function(event, element) { 
                    //Triggered while an event is being rendered.
                        _defaultSettings.changeEventContent(element, event); 
                    //Set the logo of the event if federation/sub-federation
                    if(typeof clubTitles[event.clubId] != 'undefined'){
                        if(clubTitles[event.clubId]['clubType'] == 'federation' || clubTitles[event.clubId]['clubType'] == 'sub_federation'){
                            currentClubLogo = clubLogoUrl.replace("#dummy#", event.clubId,'g');
                             element.find('.fc-content').append('<img class="fg-calendar-club-logo" src="'+currentClubLogo+'">');
                        }
                    }
                
                    //Set the team colors in the eventbox
                    teamColors = _.uniq(_.keys(event.teamColors));
                    _.each(teamColors, function(color, key){
                        //only two colors 
                        if(color != event.backgroundColor && key < 2){
                            if(key == 0){
                                element.addClass('fg-event-hasteam');
                            } else if(key == 1) {
                                 element.addClass('fg-event-hasteam-2');
                             }     
                            element.find('.fc-content').append('<span class="fg-event-teamcolor fg-event-teamcolor-'+key+'" style="background-color:'+color+' !important;"></span>');
                        }
                    });
                },
                changeEventContent:function(element, event){
                        var vType =  (localStorage.getItem(calendarviewStoragename) === null) ? _settings.defaultView : localStorage.getItem(calendarviewStoragename); 
                        var fcContent = element.find('.fc-content');
                        var fcTime = fcContent.find('.fc-time');
                        var fcTitle = fcContent.find('.fc-title');
                        var fcStartTime = event.start;
                        var fcEndTime = event.end;
                        if(fcStartTime!==null && fcEndTime!==null){
                            var mFcStartTime = fcStartTime;
                            var mFcEndTime = fcEndTime;
                            var timeDiff = mFcEndTime.diff(mFcStartTime, 'minutes');
                            if(Math.abs(timeDiff) < 60 && vType=='agendaWeek' && event.allDay==false){
                                fcTitle.html('<span style="font-size:.85em">'+fcTime.attr('data-start')+'</span> '+fcTitle.html()).css('padding-top','6px');
                                fcTime.remove();
                            }
                        }
                },
                titleFormat: 'MMM D YYYY',
                defaultDate: _getDefaultDate(),
                defaultView: _getDefaultView(),
                eventDurationEditable: false,
                editable: true,
                eventLimit: true, // allow "more" link when too many events
                fixedWeekCount: false,
                timezone: false,
                nextDayThreshold: '00:00',
                timeFormat: 'HH:mm (A)',
                locale: jstranslations.localeName
            };

        return _defaultSettings;
    };
    
    var _getDefaultDate = function(){
        //if localstorage is set take that, and then clear else set now
        calenderCurrentDate = localStorage.getItem(FgLocalStorageNames.calendar.selectedDate);
        if(calenderCurrentDate != ''){
            //date should be in the YYYY-MM-DD format
            localStorage.setItem(FgLocalStorageNames.calendar.selectedDate,'');
            var calenderCurrentDateObj = moment(calenderCurrentDate, 'YYYY-MM-DD');
            if(calenderCurrentDateObj.isValid())
                return calenderCurrentDateObj;
            else
                return moment(); 
        } else {
           return moment(); 
        }
    };
    
    var _getDefaultView = function(){
        //if localstorage is set take that else agendaWeek
        calenderView = localStorage.getItem(calendarviewStoragename);
        return (calenderView != '')?calenderView:'agendaWeek';
    };
    
    //The function that will mock the calendar view type switch events; ie. agendaWeek, month, list
    var _viewSwitch = function () {
        $(_settings.weekButton).click(function () {
            _settings.viewType = 'agendaWeek';
            localStorage.setItem(calendarviewStoragename, _settings.viewType);
            
            $('#fg-time-filter').addClass('fg-time-filter-hide');
            // to hide export button
            $('.fg-action-export').addClass('fg-dis-none').removeClass('fg-active-IB');
            FgCalenderSidebar.getFilterdata();
            FgCalendarList.redrawActionmenu();
            $(_settings.calendarElement).show();
            $(_settings.calendarListElement).hide();
            _viewSwitchEvent('agendaWeek');
            scope.$apply(function () { scope.menuType = 0; });
        });
        $(_settings.monthButton).click(function () {  
            _settings.viewType = 'month';
            localStorage.setItem(calendarviewStoragename, _settings.viewType);  
            
            $('#fg-time-filter').addClass('fg-time-filter-hide');
            // to hide export button
            $('.fg-action-export').addClass('fg-dis-none').removeClass('fg-active-IB');
            FgCalenderSidebar.getFilterdata();
            FgCalendarList.redrawActionmenu();
            $(_settings.calendarElement).show();
            $(_settings.calendarListElement).hide();
            _viewSwitchEvent('month');
            scope.$apply(function () { scope.menuType = 0; });
        });
        $(_settings.listButton).click(function () {
            _settings.viewType = 'list';
            localStorage.setItem(calendarviewStoragename, _settings.viewType);
        
            //Call the list class
            $('#fg-time-filter').removeClass('fg-time-filter-hide');
            // to show export button
            $('.fg-action-export').removeClass('fg-dis-none').addClass('fg-active-IB');
            FgCalenderSidebar.getFilterdata();
            $(_settings.calendarElement).hide();
            $(_settings.calendarListElement).show();
            _renderList();
            
        });
    };

    //This function will be called on month/week view
    var _viewSwitchEvent = function(view){
        // $calendar will be undefined when the 'list' is loaded first as per the localstorage value
       $('#fg-time-filter').addClass('fg-time-filter-hide');
       
        if(typeof $calendar == 'undefined'){
            _settings.defaultView = view;
            _render();
        } else if(_parameters.search != _searchTerm || _refetch == true){  //search or filter term is changed since last db fetch
            _redraw();
        } else{
            $calendar.fullCalendar('changeView', view);
        }
    };
    
    //All functionalities of the event box is rendered here
    var _event = function () {
        var currentEventRequest = null;
        var eventObj = {
                        //This will be the event source
                        events: function(start, end, timezone, callback) {
                            FgInternal.pageLoaderOverlayStart('.fc-view-container');
                            _searchTerm = _parameters.search;
                            if(currentEventRequest && currentEventRequest.readystate != 4) {
                                currentEventRequest.abort();
                            }
                            currentEventRequest = $.ajax({
                               method: "POST",
                               url: _settings.dataurl,
                               dataType: 'json',
                               data: _parameters,
                               success: function(data) {
                                    //Format data as per needed 
                                    _refetch = false;
                                    calandarData = data;
                                    dataSet = _formatEventsForCalendar(data);   //Format the dta from ajax as per the fullcalendar requirements
                                    callback(dataSet);  //Default callback function of fullcalendar
                                    FgInternal.pageLoaderOverlayStop('.fc-view-container');
                               }
                            });
                        },
                        //Event to be run on event click
                        eventClick: function(calEvent, jsEvent, view) {
                            if( typeof calEvent.id == 'undefined'){
                                return;
                            }
                            var eventCell = $(jsEvent.currentTarget);
                            $('.popover').popover('destroy');
                            eventCell.popover({
                                html : true, 
                                container: '#fg-calendar',
                                delay: { "show": 10, "hide": 5 },
                                content: function() {
                                    var content = FGTemplate.bind('eventPopOverTemplate', {'e': calEvent});
                                    content = content.replace("XXX", calEvent.eventdetailid,'g');
                                    return content;
                                },
                                placement: function () {
                                    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) 
                                        return 'right';
                                    else
                                        return 'bottom';
                                },
                            }).popover('show');
                            $('.popover').css({'top': (jsEvent.pageY -90),'z-index':100001} );
                            eventCell.on('shown.bs.popover', function () {
                             
                               var startTimestamp = (parseInt(moment(calEvent.start,'YYYY-MM-DD HH:mm:ss').format('X'))) + (moment().utcOffset()*60);
                               var endTimestamp = (parseInt(moment(calEvent.end,'YYYY-MM-DD HH:mm:ss').format('X'))) + (moment().utcOffset()*60);
                               var detailJsonUrl = _settings.detailJsonUrl.replace("dummyId", calEvent.eventdetailid,'g');
                          
                               detailJsonUrl = detailJsonUrl.replace("**startTime**", startTimestamp,'g');
                               detailJsonUrl = detailJsonUrl.replace("**endTime**", endTimestamp,'g');
                                //https://code.google.com/p/fullcalendar/issues/detail?id=1014
                                if(calEvent.end == null){
                                    calEvent.end = calEvent.start;
                                }
                                
                                if(calEvent.allDay == true){
                                    calEvent.end = calEvent.end.subtract(1, 'seconds');
                                }
                                
                                parameters = {};
                                parameters['startDate'] = calEvent.start.format('YYYY-MM-DD');
                                parameters['endDate'] = calEvent.end.format('YYYY-MM-DD');
                                parameters['startTime'] = calEvent.start.format('HH:mm:ss');
                                parameters['endTime'] = calEvent.end.format('HH:mm:ss');
                                //To make timestamp from server side. FAIR-2689
                                //Note: timestamp will be created from parameters(startDateTime & endDateTime) in server side insteadof timestamp from detailJsonUrl.
                                parameters['serverTimestamp'] = '1';
                                parameters['startDateTime'] = calEvent.start.format('YYYY-MM-DD HH:mm:ss');
                                parameters['endDateTime'] = calEvent.end.format('YYYY-MM-DD HH:mm:ss');

                                $.getJSON( detailJsonUrl, parameters)
                                    .done(function(data){                               
                                        var popoverContent = FGTemplate.bind('eventPopOverSubTemplate', {'d': data, calEvent:calEvent});
                                        $("#calendar-popover-body").html(popoverContent);
                                    });
                            });         
                                    
                            if($(jsEvent.currentTarget).hasClass('fc-day-grid-event')){
                                
                            }
                        },
                        //Event for handling the event drop, ie event edit via month-week interface
                        eventDrop: function(event, delta, revertFunc, jsEvent, ui, view) {
                            _drapDropRevertFunction = revertFunc;
                            
                            //https://code.google.com/p/fullcalendar/issues/detail?id=1014
                            if(event.end == null){
                                event.end = event.start.add(1, 'day').subtract(1, 'seconds');
                            }
                
                            //It is the moment immediately after the event has ended. (From docs)
                            //For example, if the last full day of an event is Thursday, the exclusive end of the event will be 00:00:00 on Friday!
                            if(event.end.format('HH:mm:ss') == '00:00:00'){
                                event.end = event.end.subtract(1, 'seconds');
                            }
                            
                            var oldEventDetails = $calendar.fullCalendar( 'clientEvents', event.id );
                            //https://code.google.com/p/fullcalendar/issues/detail?id=1014 
                            if(oldEventDetails[0]._end == null){
                                //for one day events
                                oldStartDate = oldEventDetails[0]._start._i;
                                oldEndDate = moment(oldStartDate, "YYYY-MM-DD HH:mm:ss").subtract(1, 'seconds').format("YYYY-MM-DD HH:mm:ss");
                            } else if(event.allDay == true){
                                // all day, multiple day spanning events
                                oldEndDate = oldEventDetails[0]._end._i;
                                oldEndDate = moment(oldEndDate, "YYYY-MM-DD HH:mm:ss").subtract(1, 'seconds').format("YYYY-MM-DD HH:mm:ss");
                            } else {
                                oldEndDate = oldEventDetails[0]._end._i;
                            }
                            
                            var e = {
                                    'eventdetailid': event.eventdetailid,
                                    
                                    'eventStartDate': event.start.format(FgLocaleSettingsData.momentDateFormat),
                                    'eventEndDate': event.end.format(FgLocaleSettingsData.momentDateFormat),
                                    'eventStartTime': event.start.format(FgLocaleSettingsData.momentTimeFormat),
                                    'eventEndTime': event.end.format(FgLocaleSettingsData.momentTimeFormat),
                                    
                                    'oldEventStart': oldEventDetails[0]._start._i,
                                    'oldEventEnd': oldEndDate,
                                    
                                    'isMasterRepeat': event.isMasterRepeat,
                                }
                            var html = FGTemplate.bind('eventConfirmationModalTemplate', {'e': e});
                            FgModelbox.showPopup(html);
                            FgFormTools.handleUniform();     
                        }
        };

        eventObj.eventMouseover = eventObj.eventClick;
        return eventObj;
    };
    //Event that will handle the date select and the redirect to the create page
    var _dateSelectEvent = function () {
        if(_settings.adminFlag == false)
            return;
        
        var selectableObj = {
            selectable: true,
            selectHelper: true,
            select: function (start, end) {
                var parameters = {};
               
                //https://code.google.com/p/fullcalendar/issues/detail?id=1014
                if(end == null){
                   end = start.add(1, 'day').subtract(1, 'seconds');
                }
        
                //It is the moment immediately after the event has ended. 
                //For example, if the last full day of an event is Thursday, the exclusive end of the event will be 00:00:00 on Friday!
                if(end.format('HH:mm:ss') == '00:00:00'){
                    end = end.subtract(1, 'seconds');
                }
                parameters['startDate'] = start.format('YYYY-MM-DD');
                parameters['endDate'] = end.format('YYYY-MM-DD');
                parameters['startTime'] = start.format('HH:mm:ss');
                parameters['endTime'] = end.format('HH:mm:ss');
               if (typeof _settings.viewType == 'undefined') {
                   _settings.viewType = _settings.defaultView;
                }
                if((_settings.viewType =='agendaWeek' && parameters['startTime'] == '00:00:00' && parameters['endTime'] == '23:59:59') || (_settings.viewType =='month' )){
                    parameters['allday'] = 'allday';
                }
                $('#tempform').remove();
                $form = $("<form id='tempform' method='post' action="+_settings.createurl+"></form>");
                _.each(parameters, function(value, name){
                    $form.append('<input type="hidden" name="'+name+'" value="'+value+'">');
                })
                $('body').append($form);
                $form.submit();
            }
        };
        return selectableObj;
    };
    
    //The formatter function that will format the data from ajax as per the fullcalendar needs
    var _formatEventsForCalendar = function (data) {
        var formattedEventData = new Array;
        _.each(data, function (e, key) {
            var temp = {};
            temp.id = key;
            temp.title = e.title;
            
            if(e.isAllday == 1){
                temp.allDay = true;
                temp.start = moment(e.startDate, "YYYY-MM-DD");
                nextDay  = moment(e.endDate, "YYYY-MM-DD HH:mm:ss").add(1, 'day'); //For enddate of the all day event should be the next day start
                temp.end =  moment(nextDay.format('YYYY-MM-DD')+' 00:00:00', "YYYY-MM-DD");
            } else {
                temp.allDay = false;
                temp.start = moment(e.startDate, "YYYY-MM-DD HH:mm:ss");
                temp.end = moment(e.endDate, "YYYY-MM-DD HH:mm:ss");
            }
   
            temp.editRight = e.hasEditRights;
            temp.deletRight = e.hasEditRights;
            temp.eventid = e.eventId;
            temp.eventdetailid = e.eventDetailId;
            temp.isMasterRepeat = e.isMasterRepeat;
            temp.clubId = e.clubId;
            
            if (e.isClubAreaSelected == 1) {
                temp.backgroundColor = e.clubColorCode;
            }
            
            //Create the background color and team colors for the event
            if (e.eventRoleAreas != null && e.eventRoleAreas.length > 0) {
                var roleArray = e.eventRoleAreas.split('|&&&|');
                var teamColors = [];
                _.each(roleArray, function (role, key) {
                    var roleDetailArray = role.split('|@@@|');
                    if (temp.backgroundColor == '' || typeof temp.backgroundColor == 'undefined') {   //if club color is null the set the first color
                        temp.backgroundColor = roleDetailArray[2];
                    } else {
                        //else is used because the background color shouldn't be in the team colors
                        if(typeof teamColors[roleDetailArray[2]] == 'undefined'){
                            teamColors[roleDetailArray[2]] = [];
                        }
                        teamColors[roleDetailArray[2]][key] = roleDetailArray[1];
                    }  
                });
                temp.teamColors = teamColors;
            }
            
            formattedEventData.push(temp);
        });
        return formattedEventData;
    };

    //The actual function that will initialize the full calendar
    var _render = function () {
        $('.popover').popover('destroy'); //Hide the tootip on week/month view
        var options = jQuery.extend(_event(), _dateSelectEvent(), _settings);
        $calendar = $(_settings.calendarElement).fullCalendar(options);
    };

    //The function that will call the function for rendering the list
    var _renderList = function () {
        $('.popover').popover('destroy'); //Hide the tootip on week/month view
        FgCalendarList.renderList(_settings, _parameters);
    };

    //This function will recall the ajax with all the set parametes
    var _redraw = function () {
        $('.popover').popover('destroy'); //Hide the tootip on week/month view
        if(typeof $calendar == 'undefined'){
            _render();
        } else {  
            $calendar.fullCalendar('refetchEvents');
        }
        
    };

    //This function will save the event details on drag-drop
    var _saveEvent = function (saveData) {
        $.ajax({
            method: "POST",
            url: _settings.editurl,
            dataType: 'json',
            data:{'saveData':JSON.stringify(saveData)},
            success: function(data) {
                _redraw();
            }
        });
        return false;
    };

    //The function that will be used to navigate to the edit event page from the tooltip
     var _viewEvent = function (detailId, index, el) {    
        var calendarView = localStorage.getItem(calendarviewStoragename);
        if( calendarView == 'list') { //in list view
            var eventDetailObj = listEventDetailObj;
        } else { // in month and week view
            var eventDetailObj = $calendar.fullCalendar('clientEvents', index);
        }
        
        //https://code.google.com/p/fullcalendar/issues/detail?id=1014
        if(eventDetailObj[0].end == null){
            eventDetailObj[0].end = eventDetailObj[0].start;
        }
        
        if(eventDetailObj[0].allDay == true){
            eventDetailObj[0].end = moment(eventDetailObj[0].end, "YYYY-MM-DD HH:mm:ss").subtract(1, 'seconds');
        }
        
        parameters = {};
        parameters['startDate'] = eventDetailObj[0].start.format('YYYY-MM-DD');
        parameters['endDate'] = eventDetailObj[0].end.format('YYYY-MM-DD');
        parameters['startTime'] = eventDetailObj[0].start.format('HH:mm:ss');
        parameters['endTime'] = eventDetailObj[0].end.format('HH:mm:ss');

        var startTimestamp = $(el).attr('data-startTimestamp');
        var endTimestamp = $(el).attr('data-endTimestamp');
        detailspageurl = _settings.detailpageurl.replace("dummyId", detailId,'g');
        detailspageurl =detailspageurl.replace("**startTime**", startTimestamp,'g');
        detailspageurl = detailspageurl.replace("**endTime**", endTimestamp,'g');
        
        window.location.href = detailspageurl;            
    };
    //The function that will be used to navigate to the edit event page from the tooltip
     var _editEvent = function (detailId, index) {   
         var calendarView = localStorage.getItem(calendarviewStoragename);
         if(calendarView == 'list') { //in list view
             var eventDetailObj = listEventDetailObj;
         } else { // in month and week view
             var eventDetailObj = $calendar.fullCalendar('clientEvents', index);
         }
        
        //https://code.google.com/p/fullcalendar/issues/detail?id=1014
        if(eventDetailObj[0].end == null){
            eventDetailObj[0].end = eventDetailObj[0].start;
        }
        
        if(eventDetailObj[0].allDay == true){
            eventDetailObj[0].end = moment(eventDetailObj[0].end, "YYYY-MM-DD HH:mm:ss").subtract(1, 'seconds');
        }
                            
        parameters = {};
        parameters['startDate'] = eventDetailObj[0].start.format('YYYY-MM-DD');
        parameters['endDate'] = eventDetailObj[0].end.format('YYYY-MM-DD');
        parameters['startTime'] = eventDetailObj[0].start.format('HH:mm:ss');
        parameters['endTime'] = eventDetailObj[0].end.format('HH:mm:ss');
        
        editpageurl = _settings.editpageurl.replace("dummyId", detailId,'g');
        $('#tempform').remove();
        $form = $("<form id='tempform' method='post' action="+editpageurl+"></form>");
        _.each(parameters, function(value, name){
            $form.append('<input type="hidden" name="'+name+'" value="'+value+'">');
        })
        $('body').append($form);
        $form.submit();
                
    };
    
    //This function will add the parameters to the calendar objects
    //The parameters that are been added will be sent to the server on next redraw()
    // When reset is true, it will remove the 'key' from the calendar object and set the new value
    //When the reset is false, it will append the value to the key
    var _addParameter = function (key, value, reset) {
        if (reset == true) {
            _parameters[key] = value;
        } else {
            //append the value to the key
            if (typeof _parameters[key] == 'undefined') {
                _parameters[key] = value;
            } else {
                if (typeof _parameters[key] == 'object') {
                    var tempValueArray = _parameters[key];
                } else if (typeof _parameters[key] == 'number') {
                    var tempValueArray = [_parameters[key]];
                } else if (typeof _parameters[key] == 'string') {
                    var tempValueArray = [_parameters[key]];
                }
                tempValueArray = _.flatten(_.union(tempValueArray, value));
                _parameters[key] = tempValueArray;
            }
        }
    };

    var _getParameters = function () {
        return _parameters;
    };
   
    var _getTitleForAgendaWeek = function (view) {
        var currentDateFormat = FgLocaleSettingsData.momentDateFormat;
        var startDate = view.intervalStart;
        var endDate = view.intervalEnd.subtract(1, 'days');
        switch(currentDateFormat){
            //23.08.2005 => 26.08. – 01.09.2015
            case 'DD.MM.YYYY': 
                title = startDate.format('DD.MM.') + ' - ' + endDate.format('DD.MM.YYYY');
                break;
            //23.8.2005 => 26.8. – 1.9.2015
            case 'D.M.YYYY': 
                title = startDate.format('D.M.') + ' - ' + endDate.format('D.M.YYYY');
                break;
            //2015.08.23 => 2015.08.26 – 09.01   
            case 'YYYY.MM.DD': 
                title = startDate.format('YYYY.MM.DD') + ' - ' + endDate.format('MM.DD');
                break;
            //23/08/2015 => 26/08 – 01/09/2015   
            case 'DD/MM/YYYY': 
                title = startDate.format('DD/MM') + ' - ' + endDate.format('DD/MM/YYYY');
                break;
            //23/8/2015 => 26/8 – 1/9/2015  
            case 'D/M/YYYY': 
                title = startDate.format('D/M') + ' - ' + endDate.format('D/M/YYYY');
                break;
            //2015/08/23 => 2015/08/26 – 09/01  
            case 'YYYY/MM/DD': 
                title = startDate.format('YYYY/MM/DD') + ' - ' + endDate.format('MM/DD');
                break;
            //2015/8/23 => 2015/8/26 – 9/1  
            case 'YYYY/M/D': 
                title = startDate.format('YYYY/M/D') + ' - ' + endDate.format('M/D');
                break;
            //8/23/2015 => 8/26 – 9/1/2015 
            case 'M/D/YYYY': 
                title = startDate.format('M/D') + ' - ' + endDate.format('M/D/YYYY');
                break;
            //23-08-2015 => "26-08 – 01-09-2015" 
            case 'DD-MM-YYYY': 
                title = startDate.format('DD-MM') + ' - ' + endDate.format('DD-MM-YYYY');
                break;
            //23-8-2015 => "26.08 – 1-9-2015" @todo need to confirm
            case 'D-M-YYYY': 
                title = startDate.format('D-M') + ' - ' + endDate.format('D-M-YYYY');
                break;
            //23-08-15: "26-08-15 – 01-09-15" 
            case 'DD-MM-YY': 
                title = startDate.format('DD-MM-YY') + ' - ' + endDate.format('DD-MM-YY');
                break;
            //2015-08-23: "2015-08-26 – 09-01"
            case 'YYYY-MM-DD': 
                title = startDate.format('YYYY-MM-DD') + ' - ' + endDate.format('MM-DD');
                break;
            default:
                title = view.title;
                break;
        }

        return title;
    };

    return {
        // initialize the page titlebar plugin
        initialize: function (settings, data) {
            _initialize(settings, data);
        },
        //The public function to redraw the calendar
        redraw: function () {
            _redraw();
        },
        //The public function to redraw the list
        renderList:function(){
            _renderList();
        },
        //The public function to add parameter
        addParameter: function (key, value, reset) {
            _addParameter(key, value, reset);
        },
        //The function to get the parameters that have been added to the request
        getParameters: function () {
            return _getParameters();
        },
        drapDropRevertFunction: function () {
            if(typeof _drapDropRevertFunction == 'function')    //The drop function will be called on closing all modal boxes
                _drapDropRevertFunction();
        },
        //The function that is used save event on drag-drop
        saveEvent: function () {
            var formData = FgInternalParseFormField.fieldParse();
            _saveEvent(formData);
        },
        //The event which is triggered in clicking the delete button in tooltip
        deleteEvent: function (detailId, index) {            
            var deletedEvents = {0:{id:detailId,index:index}};
            FgCalendarDelete.clickDelete(deletedEvents);
        },
        //The event which is triggered in clicking the edit button in tooltip
        editEvent: function (detailId, index) {
            _editEvent(detailId, index);
        },
        //The event which is triggered in clicking the view button in tooltip
        viewEvent: function (detailId, index, el) {
            _viewEvent(detailId, index, el);
        },
        //The function that can give some details on the calendar
        getCalendarDetails: function(){
            var settings = _settings;
            var parameters = _parameters;
            var details = {};
            details['view'] = _settings.viewType;
            details['startDate'] = parameters.startDate;
            details['endDate'] = parameters.endDate;
            return details;
        },
        //The function that set the status for forcefully refetching data from server on view change
        forceRefetch: function (status) {
            _refetch = status;
        },
    };

}();

//Wrapper function for calendar list view.
FgCalendarList = {
    //To format event data & render list view
    renderList: function (settings, parameters) {
        FgInternal.pageLoaderOverlayStart('page-container');
        
        var defaultStart = new Date().getFullYear()+'-01-01';
        var defaultEnd = new Date().getFullYear()+'-12-31';
        parameters.startDate = defaultStart;
        parameters.endDate = defaultEnd;
        $fgyear =$('[name=fg-year-value]:checked').attr('data-value');
        FgFullCalendar.addParameter('filter',localStorage.getItem(filterStoragename),true);
        if(typeof $fgyear !=='undefined'){
            $fgsplit = $fgyear.split("#");
            parameters.startDate = $fgsplit[0];
            parameters.endDate = $fgsplit[1];
        }
        $.ajax({
            method: "POST",
            url: settings.dataurl,
            dataType: 'json',
            data: parameters,
            success: function (data) {
                calandarData = data;
                var eventsByMonth = FgCalendarDataFormatter.getFormattedData(data);
                var dataCount = _.size(eventsByMonth);    
                var cnt =0;
                $(settings.calendarListElement).html('');
                if (dataCount > 0) {
                    _.each(eventsByMonth, function (v, i) {
                        var content = FGTemplate.bind('calendarListTemplate', {'d': v, 'i': i, dataCount: dataCount});
                        $(settings.calendarListElement).append(content);
                        $('.calendarList-'+i+' .dataClass').uniform();
                        FgInternal.pageLoaderOverlayStop('page-container');
                    });
                } else {
                    var content = FGTemplate.bind('calendarListTemplate', {'d': '', 'i': '', dataCount: dataCount});
                    $(settings.calendarListElement).append(content);
                    FgInternal.pageLoaderOverlayStop('page-container');
                } 
                FgCalendarList.redrawActionmenu('list');
                FgCalendarList.exportBtnHandler(_.size(eventsByMonth));
            }
        });        
    },
    //To search events
    searchEvent:function(){
        var searchVal = $('#fg_dev_member_search').val();
        $pageTitle =$('.page-title .page-title-text');
        if(searchVal===''){
           $pageTitle.html(pageTitle);
        }else{
           $pageTitle.html(searchTitle.replace('%searchval%',searchVal));
        }
        FgFullCalendar.addParameter('search',searchVal,true);
        
        var calendarView = localStorage.getItem(calendarviewStoragename);
        if(calendarView == 'list'){
            FgFullCalendar.renderList();
        } else {
            FgFullCalendar.redraw();
        }
    },
    //Redraw action menu for different view
    redrawActionmenu:function(viewType){
        
        if(viewType=='list'){
            scope.$apply(function () {
                scope.menuContent = actionMenuForList;
            });
            setTimeout(function () {
                    FgCheckBoxClick.init();
            }, 200);
        }else{ //month,week
            scope.$apply(function () {
                scope.menuContent = actionMenuForOthers;
            });
            //scope.$apply(function () { scope.menuType = 0; });
        }
    },
    postEventDatails:function(index,action){
        
        mStartDateTime = moment(calandarData[index].startDate, "YYYY-MM-DD h:mm:ss");
        mEndDateTime = moment(calandarData[index].endDate, "YYYY-MM-DD h:mm:ss");
        window.location.href = action;       
    },
    // To enable/disable export button
    exportBtnHandler:function(evLength){          
            if(evLength > 0){
               $('.fg-action-export').removeClass('fg-calendar-export-disable');
            } else{
               $('.fg-action-export').addClass('fg-calendar-export-disable');  
            }
    }
};
//Wrapper fuction to format event details
FgCalendarDataFormatter = {
    //Formatting event data: Events are grouped by year and month.
    getFormattedData: function (events) {
        var result = {};
        var $filterDates = $('[name=fg-year-value]:checked').attr('data-value');
        var $filterDateArray = $filterDates.split("#");
        var listStartDate = moment($filterDateArray[0], "YYYY-MM-DD").format('YYYYMMDD');
        var listEndDate = moment($filterDateArray[1], "YYYY-MM-DD").format('YYYYMMDD');

        _.each(events, function (o, i) {
            var fromDate = moment(o.startDate, "YYYY-MM-DD h:mm:ss");
            var toDate = moment(o.endDate, "YYYY-MM-DD h:mm:ss");
            if (fromDate.isValid() && toDate.isValid()) {
                var evDates = FgCalendarDataFormatter.getDaysBetweenDates(fromDate, toDate);
                _.each(evDates, function (p, j) {
                    //If event within filterDate 
                    var P_moment = moment(p);
                    var P_moment_YMD = P_moment.format('YYYYMMDD');
                    var P_moment_YM = P_moment.format('YYYYMM');
                    if (P_moment_YMD >= listStartDate && P_moment_YMD <= listEndDate) {
                        //If array exists    
                        result[P_moment_YM] = (typeof result[P_moment_YM] != 'undefined' && result[P_moment_YM] instanceof Array) ? result[P_moment_YM] : [];
                        result[P_moment_YM].push({
                            evId: o.eventId,
                            evDetId: o.eventDetailId,
                            evClubId: o.clubId,
                            clubColorCode: o.clubColorCode,
                            evDate: p,
                            evTimestamp: P_moment.format('x'),
                            evStartDate: fromDate,
                            evEndDate: toDate,
                            evStartTimestamp: o.startTimestamp,
                            evEndTimestamp: o.endTimestamp,
                            evTitle: o.title,
                            evScope: o.scope,
                            isAllDay: o.isAllday,
                            evCategory: o.eventCategories,
                            evRole: o.eventRoleAreas,
                            hasEditRights: o.hasEditRights,
                            isMasterRepeat: o.isMasterRepeat,
                            isClubAreaSelected: o.isClubAreaSelected,
                            rowId: i,
                            evClubs: {title: clubTitles[o.clubId], logo: clubLogoUrl.replace("#dummy#", o.clubId)}
                        });
                    }
                });
            }
        });
        
        return result;
    },
    //Function return date between two dates
    getDaysBetweenDates: function (startDate, endDate) {
        var dates = [];
        //If Start date and enddate are equal.
        if(startDate.format('YYYYMMDD')==endDate.format('YYYYMMDD')){ 
            dates.push(startDate.clone().toDate());
        }else{
            var currDate = startDate.clone().startOf('day');
            var lastDate = endDate.clone().startOf('day');

            dates.push(startDate.clone().toDate());
            while (currDate.add('days', 1).diff(lastDate) < 0) {
                dates.push(currDate.clone().toDate());
            }
            if (startDate < endDate) {
                dates.push(lastDate.clone().toDate());
            }
            
        }
        return dates;
    },
    // Convert datetime to timestamp (date-time format should be YYYY-MM-DD HH:mm:ss)
    makeTimeStamp:function(dateTime){
        return (parseInt(moment(dateTime,'YYYY-MM-DD HH:mm:ss').format('X'))) + (moment().utcOffset()*60);
    }
};
FgCalendarDelete = {
    clickDelete: function(deletedEvents){
        $.post(deleteAppPath, { 'jsonRowId':JSON.stringify(deletedEvents) }, function(data) {
            $('.popover').popover('destroy'); //Hide the tootip on week/month view
            FgModelbox.showPopup(data);
        });
    },
    deleteSave: function(deleteArray,choice,from){
        var Arr = JSON.stringify(deleteArray);
        var passingData = {'deleteArray': Arr,'choice':choice};
        
        if(from == 'listing')
            FgXmlHttp.post(deleteSavePath, passingData, false, FgCalendarDelete.calBack);
        else
            FgXmlHttp.post(deleteSavePath, passingData, false, false);
        FgModelbox.hidePopup();
    },calBack: function(){
        var calendarView = localStorage.getItem(calendarviewStoragename);
        FgFullCalendar.forceRefetch(true);
        if(calendarView == 'list'){
           var callback = FgFullCalendar.renderList();
        } else {
           $('.popover').popover('destroy');
           var callback = FgFullCalendar.redraw();
        }
    },
    displayPopupHeading :  function() {
        if (_.size(jsonRowIds) == 1 ) {
            if (type == 0) {
                popupHeadTitle = translation['popupTitleSingleNon'];
                popupHeadText = translation['popupTextSingleNon'];
            } else {
                popupHeadTitle = translation['popupTitleSingleRep'];
                popupHeadText = translation['popupTextSingleRep'];
            }
            $('h4.modal-title').html('<div class="fg-popup-text" id="popup_head_text"></div>');
            $('div#popup_head_text').text(popupHeadTitle);
            $('label.pop-uptext').text(popupHeadText);
        } else {
             if (type == 0) {
                popupHeadTitle = translation['popupTitleMultiNon'].replace('%count%', _.size(jsonRowIds));
                popupHeadText = translation['popupTextMultiNon'];
                $('label.pop-uptext').text(popupHeadText);
            } else {
                popupHeadTitle = translation['popupTitleMultiRep'].replace('%count%', _.size(jsonRowIds));
                popupHeadText = translation['popupTextMultiRep'];
                 $('label.pop-uptext').text(popupHeadText);
            }
        
                var appHtml = '';
                var appLinks = {};
                var i = 0;
                $.each(finalArray, function(key, result) {
                    i++;
                    if (i == 11) {
                        appHtml += '<li>&hellip;</li>';
                        return false;
                    } else {
                        appLinks[result.eventDetailId] = result.title;
                        detailPath1 = detailPath.replace('dummyId',result.eventId);
                        appHtml += '<li><a href="'+detailPath1+'" target="_blank" data-club-id="'+result.eventDetailId+'"></a></li>';
                    }
                });
                $('h4.modal-title').html('<span class="fg-dev-names"><a href="#" class="fg-plus-icon"></a><a href="#" class="fg-minus-icon"></a></span><div class="fg-popup-text" id="popup_head_text"></div>\n\
                    <div class="fg-arrow-sh"><ul>' + appHtml + '</ul></div>');
                $('div#popup_head_text').text(popupHeadTitle );
                FgCalendarDelete.displayAppNames(appLinks);
                
        }
    },
    displayAppNames : function(appLinks) {
        $.each(appLinks, function(selAppId, selAppName) {
            $('a[data-club-id='+selAppId+']').text(selAppName);
        });
    }
};

FgCalendarDuplicate = {
    clickEdit: function(editEvents){
        var editDuplicate = editSinglePath;
        var editDuplicate = editDuplicate.replace('dummyId', editEvents);
        window.location = editDuplicate;
    }
}

$('#fg-popup').on('hidden.bs.modal', function (e) {
    FgFullCalendar.drapDropRevertFunction();
});

$(document).on('keypress','#fg_dev_member_search',function(event){
    if (event.which == 13) {
        localStorage.setItem(searchLocalStorage,$(this).val())
        FgCalendarList.searchEvent();   
    }
    
});
$(document).on('click','.fg-first-col input[type=checkbox]',function(event){
  if($(this).prop('checked')){
      
      $(this).closest('.fg-calendar-list-item').addClass('checked');
  }else{
      if($(this).closest('.fg-calendar-list-item').hasClass('checked')){
          $(this).closest('.fg-calendar-list-item').removeClass('checked');
      }
      
  }
    
});
//Click event to view event details. 
$(document).on('click','.fg-calendar-view-content .event-details',function(e){
    e.preventDefault();
    FgCalendarList.postEventDatails($(this).data('index'),$(this).data('href'));
});

// Click function to handle the export functionaity of calendar events
$(document).off('click', '.fg-action-export');
$(document).on('click','.fg-action-export',function(e){
    e.preventDefault();
    if($(this).hasClass('fg-calendar-export-disable')) return;
    var viewType =  localStorage.getItem(calendarviewStoragename);  
   
                    var eventData = {};
                    var selectedEventFinal = {};  
                    var selectedEventDetailsFinal ={};
                    var eventCount = '';
                    var search = $("#fg_dev_member_search").val();
                    var filter = localStorage.getItem(filterStoragename);
                    var filterFinal = JSON.parse(filter);
              
                    if (viewType === "list") {
                        var selectedEvents = {};
                        var values = [];
                        var i = 0;
                        $('#calendarList input.fg-export-input').each(function(d){
                             var data = parseInt($(this).val());
                             if(!_.contains(values,data)){
                                selectedEvents[parseInt(i)]= {'index':data};
                                i++;
                             }
                              values.push(data);
                        }); 

                        var eventArray = {};
                        for(var count = 0; count < _.size(selectedEvents); count++){
                           var  data = calandarData[selectedEvents[count]['index']];
                           eventArray[count] = data;
                        } 
             
                        var events = _.groupBy(eventArray, 'eventId'); 
                         _.each(events, function(eventData, event) {
                             selectedEventFinal[event]= eventData[0]['title'];

                        });
                        var eventDetails = _.groupBy(eventArray, 'eventDetailId'); 
                       _.each(eventDetails, function(eventDetailsData, eventDetailId) {
                            selectedEventDetailsFinal[eventDetailId] = { 
                                                                        'eventTitle' : eventDetailsData[0]['title'],
                                                                        'startTimeStamp' : FgCalendarDataFormatter.makeTimeStamp(eventDetailsData[0]['startDate']),
                                                                        'endTimeStamp' : FgCalendarDataFormatter.makeTimeStamp(eventDetailsData[0]['endDate'])
                                                                    };
                        });                    
                        eventCount = Object.keys(eventDetails).length;
                    }
                        eventData = {'count':eventCount,'events':selectedEventFinal,'search':search,'filter':filterFinal,'viewType':viewType,'eventDetails':selectedEventDetailsFinal};;
                        FgCalendarExport.exportMenuClick(eventData);
});

FgCalendarExport = {
    exportMenuClick: function(eventData){
        $.post(exportPopupPath, { 'eventData':JSON.stringify(eventData) }, function(data) {
            FgModelbox.showPopup(data);
        });
    },
    exportSave: function(eventData){
        $('#exportform').remove();
        $form = $("<form id='exportform' method='post' action="+exportSavePath+"></form>");
        $form.append('<input type="hidden" id="eventExportData" name="eventData">');
        $('body').append($form);
        $('#eventExportData').val(JSON.stringify(eventData));
        $form.submit();
        FgModelbox.hidePopup();
        FgFullCalendar.renderList();
    }
 }
 
 //Click event to view event details in list view. 
 var listEventDetailObj;
$(document).on('mouseover','.list-cal-events',function(e){  
    hrefLink = $(this).attr('data-href');
    hrefLinkParams = hrefLink.split('/');
    var index = $(this).attr('data-index');    
    var calandarDetail = calandarData[index];
    calEvent = {
        'end': moment(new Date(calandarDetail.endDate.replace(' ','T'))),
        'start': moment(new Date(calandarDetail.startDate.replace(' ','T'))),
        'id': index, 
        'endTime': hrefLinkParams[(hrefLinkParams.length - 1)],
        'startTime': hrefLinkParams[(hrefLinkParams.length - 2)],
        'editRight' : calandarDetail.hasEditRights,
        'deletRight' : calandarDetail.hasEditRights
    }    
    $.extend( calEvent, calandarDetail );
    listEventDetailObj = {0 : calEvent }; //used in edit and delete functions
    $('.popover').popover('destroy');  
    $(this).popover({
        html : true,
        container: '#fg-calendar-list',
        delay: { "show": 10, "hide": 5 },
        placement: function (e) {
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                return 'right';
            } else {                
                placement = 'bottom';
                return placement;
            }
        },
        content: function(e) {
            var content = FGTemplate.bind('eventPopOverTemplate', {'e': calEvent});
            return content;
        },
    }).popover('show');
    $('.popover').css({'z-index':100001} );
    $(this).on('shown.bs.popover', function (e) {  
        var detailJsonUrl = detailJsonUrlPath.replace("dummyId", calEvent.eventDetailId,'g');
        detailJsonUrl = detailJsonUrl.replace("**startTime**", calEvent.startTime,'g');
        detailJsonUrl = detailJsonUrl.replace("**endTime**", calEvent.endTime,'g');
        if(calEvent.end == null){
            calEvent.end = calEvent.start;
        }
//        if(calandarDetail.isAllday == 1){           
//            calEvent.end = calEvent.end.subtract(1, 'seconds');
//        }

        parameters = {};
        parameters['startDate'] = calEvent.start.format('YYYY-MM-DD');
        parameters['endDate'] = calEvent.end.format('YYYY-MM-DD');
        parameters['startTime'] = calEvent.start.format('HH:mm:ss');
        parameters['endTime'] = calEvent.end.format('HH:mm:ss');

        $.getJSON( detailJsonUrl, parameters)
            .done(function(data){    
                var popoverContent = FGTemplate.bind('eventPopOverSubTemplate', {'d': data, calEvent:calEvent});
                $("#calendar-popover-body").html(popoverContent);
            });
    });                              
});

