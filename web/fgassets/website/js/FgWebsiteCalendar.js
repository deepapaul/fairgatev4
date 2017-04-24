var FgWebsiteCalendar = (function () {
    function FgWebsiteCalendar(pageId) {
        this.renderType = calendarView;
        this.calendarElementId = '#fg-calendar-fullcalendar';
        this.listElementId = '#fg-calendar-list';
        this.locale = jstranslations.localeName;
        this.pageId = pageId;
        this.checkForMobileView();
        this.setSwitchEvents();
        this.initListPopover();
        this.windowResize();
        moment.locale(this.locale);
        $('#fg-page-monthswitch-input').val(this.renderType);
        $('#fg-page-monthswitch-input').selectpicker('render');
    }
    FgWebsiteCalendar.prototype.setSwitchEvents = function () {
        var _this = this;
        $('#fg-dev-pagetitle-container').on('change', '#fg-page-monthswitch-input', function () {
            _this.renderType = $(this).val();
            _this.render(true);
        });
        $('#fg-dev-pagetitle-container').on('change', '#fg-page-timeperiod-input', function () {
            _this.render(true);
        });
        $('#fg-dev-pagetitle-container').on('keyup', '#fg-page-search', function () {
            clearTimeout(_this.currentTimeout);
            _this.currentTimeout = setTimeout(function () {
                _this.render(true);
            }, 500);
        });
    };
    FgWebsiteCalendar.prototype.getFilterData = function () {
        var dataArray = {};
        dataArray.pageId = this.pageId;
        dataArray.filterData = {};
        dataArray.search = $('#fg-page-search').val();
        return dataArray;
    };
    FgWebsiteCalendar.prototype.render = function (refetch) {
        this.setTimeperiodElement();
        if (this.renderType == 'agendaWeek' || this.renderType == 'month') {
            this.renderCalendar(refetch);
        }
        else {
            this.renderList(refetch);
        }
    };
    FgWebsiteCalendar.prototype.downloadAttachment = function (filename, encryptedId, clubId) {
        $('#attachmentDownloadForm').remove();
        var form = $("<form id='attachmentDownloadForm' method='post' action=" + downloadPath + "></form>");
        form.append('<input type="hidden" name="filename" value="' + filename + '">');
        form.append('<input type="hidden" name="encrypted" value="' + encryptedId + '">');
        form.append('<input type="hidden" name="eventclubId" value="' + clubId + '">');
        $('body').append(form);
        form.submit();
        form.remove();
    };
    FgWebsiteCalendar.prototype.setTimeperiodElement = function () {
        if (this.renderType == 'list') {
            $('.fg-page-timeperiod').removeClass('hide');
        }
        else {
            $("#fg-page-timeperiod-input").val($("#fg-page-timeperiod-input option:first").val());
            $('#fg-page-timeperiod-input').selectpicker('render');
            $('.fg-page-timeperiod').addClass('hide');
        }
        $(".fg-page-timeperiod").addClass('hidden-xs');
        $(".fg-page-monthswitch").addClass('hidden-xs');
    };
    FgWebsiteCalendar.prototype.renderList = function (refetch) {
        var _this = this;
        $('.popover').popover('destroy');
        $(_this.calendarElementId).addClass('hide');
        $(_this.listElementId).removeClass('hide');
        _this.destroyCalendar();
        var dataArray = _this.getFilterData();
        var dateFilterValue = $('#fg-page-timeperiod-input').val().split('__');
        dataArray.startDate = dateFilterValue[0];
        dataArray.endDate = dateFilterValue[1];
        z = 0;
        $.ajax({
            type: "POST",
            url: eventDataPath,
            data: dataArray,
            success: function (response) {
                _this.eventData = response.calendarData;
                var eventsByMonth = _this.getFormattedData(response.calendarData);
                if (eventsByMonth.length > 0)
                    var content = FGTemplate.bind('calendarListTemplate', { 'dataSet': eventsByMonth });
                else
                    var content = FGTemplate.bind('noEventTemplate', {});
                $(_this.listElementId).html(content);
            },
            dataType: 'json'
        });
    };
    FgWebsiteCalendar.prototype.renderCalendar = function (refetch) {
        $(this.listElementId).addClass('hide');
        $(this.calendarElementId).removeClass('hide');
        $('.popover').popover('destroy');
        if (typeof this.fullCalendarObj == 'undefined' || this.fullCalendarObj == null) {
            var eventObj = this.getEventObject();
            var settingsObj = this.getDefaultSettings();
            var options = jQuery.extend(eventObj, settingsObj);
            this.fullCalendarObj = $(this.calendarElementId).fullCalendar(options);
            $(this.calendarElementId).fullCalendar('option', 'locale', this.locale);
        }
        else {
            this.fullCalendarObj.fullCalendar('changeView', this.renderType);
            this.fullCalendarObj.fullCalendar('refetchEvents');
        }
    };
    FgWebsiteCalendar.prototype.destroyCalendar = function () {
        if (typeof this.fullCalendarObj != 'undefined' && this.fullCalendarObj != null) {
            $(this.calendarElementId).fullCalendar('destroy');
            this.fullCalendarObj = null;
        }
    };
    FgWebsiteCalendar.prototype.getEventObject = function () {
        var _this = this;
        var eventObj = {
            events: function (start, end, timezone, callback) {
                if (_this.eventFetchRequest && _this.eventFetchRequest.readystate != 4) {
                    _this.eventFetchRequest.abort();
                }
                var dataArray = _this.getFilterData();
                dataArray.startDate = start.format('YYYY-MM-DD');
                dataArray.endDate = end.format('YYYY-MM-DD');
                _this.eventFetchRequest = $.ajax({
                    method: "POST",
                    url: eventDataPath,
                    dataType: 'json',
                    data: dataArray,
                    success: function (response) {
                        $('.popover').popover('destroy');
                        _this.eventData = response.calendarData;
                        var dataSet = _this.formatEventsForCalendar(response.calendarData);
                        callback(dataSet);
                    }
                });
            }
        };
        return eventObj;
    };
    FgWebsiteCalendar.prototype.formatEventsForCalendar = function (data) {
        var formattedEventData = new Array;
        _.each(data, function (e, key) {
            var temp = {};
            temp.id = key;
            temp.title = e.title;
            if (e.isAllday == 1) {
                temp.allDay = true;
                temp.start = moment(e.startDate, "YYYY-MM-DD");
                nextDay = moment(e.endDate, "YYYY-MM-DD HH:mm:ss").add(1, 'day');
                temp.end = moment(nextDay.format('YYYY-MM-DD') + ' 00:00:00', "YYYY-MM-DD");
            }
            else {
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
            if (e.eventRoleAreas != null && e.eventRoleAreas.length > 0) {
                var roleArray = e.eventRoleAreas.split('|&&&|');
                var teamColors = [];
                _.each(roleArray, function (role, key) {
                    var roleDetailArray = role.split('|@@@|');
                    if (temp.backgroundColor == '' || typeof temp.backgroundColor == 'undefined') {
                        temp.backgroundColor = roleDetailArray[2];
                    }
                    else {
                        if (typeof teamColors[roleDetailArray[2]] == 'undefined') {
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
    ;
    FgWebsiteCalendar.prototype.getDefaultSettings = function () {
        var _this = this;
        var _defaultSettings = {
            header: {
                left: 'prev,today,next'
            },
            buttonText: {
                today: todayText,
                month: 'Month',
                week: 'Week'
            },
            viewRender: function (view, element) {
                $(_this.calendarElementId).find('.fc-toolbar .fc-right').remove();
                if ($('#fg-calendar-title').length == 0) {
                    var defaultToday = $('button.fc-today-button').clone(true);
                    $('button.fc-today-button').replaceWith('<button class="fc-state-default" type="button" id="fg-calendar-title"></button>');
                    $('.fc-next-button').after(defaultToday);
                }
                if (view.name == 'agendaWeek') {
                    $('#fg-calendar-title').html(_this.getTitleForAgendaWeek(view));
                }
                else {
                    $('#fg-calendar-title').html(view.intervalStart.format('MMMM YYYY'));
                }
            },
            eventRender: function (event, element) {
                if (typeof clubTitles[event.clubId] != 'undefined') {
                    if (clubTitles[event.clubId]['clubType'] == 'federation' || clubTitles[event.clubId]['clubType'] == 'sub_federation') {
                        currentClubLogo = clubLogoUrl.replace("#dummy#", event.clubId, 'g');
                        element.find('.fc-content').append('<img class="fg-calendar-club-logo" src="' + currentClubLogo + '">');
                    }
                }
                teamColors = _.uniq(_.keys(event.teamColors));
                _.each(teamColors, function (color, key) {
                    if (color != event.backgroundColor && key < 2) {
                        if (key == 0) {
                            element.addClass('fg-event-hasteam');
                        }
                        else if (key == 1) {
                            element.addClass('fg-event-hasteam-2');
                        }
                        element.find('.fc-content').append('<span class="fg-event-teamcolor fg-event-teamcolor-' + key + '" style="background-color:' + color + ' !important;"></span>');
                    }
                });
                $('.fc-scroller').scroll(function () {
                    $('.popover').popover('destroy');
                });
            },
            eventClick: function (event, jsEvent, view) {
                var calEvent = event;
                var element = $(jsEvent.currentTarget);
                calEvent.startTimestamp = (parseInt(moment(event.start, 'YYYY-MM-DD HH:mm:ss').format('X'))) + (moment().utcOffset() * 60);
                calEvent.endTimestamp = (parseInt(moment(event.end, 'YYYY-MM-DD HH:mm:ss').format('X'))) + (moment().utcOffset() * 60);
                calEvent.eventDetailId = event.eventdetailid;
                calEvent.title = event.title;
                if (event.end == null) {
                    calEvent.end = event.start;
                }
                if (event.allDay == true) {
                    calEvent.end = event.end.subtract(1, 'seconds');
                }
                parameters = {};
                parameters['startDate'] = event.start.format('YYYY-MM-DD');
                parameters['endDate'] = event.end.format('YYYY-MM-DD');
                parameters['startTime'] = event.start.format('HH:mm:ss');
                parameters['endTime'] = event.end.format('HH:mm:ss');
                //To make timestamp from server side. FAIR-2689
                //Note: timestamp will be created from parameters(startDateTime & endDateTime) in server side insteadof timestamp from detailJsonUrl.
                parameters['serverTimestamp'] = '1';
                parameters['startDateTime'] = calEvent.start.format('YYYY-MM-DD HH:mm:ss');
                parameters['endDateTime'] = calEvent.end.format('YYYY-MM-DD HH:mm:ss');
                calEvent.v = _this.renderType;
                if (view.name == 'agendaWeek' && event.allDay == false) {
                    _this.showEventPopOver(element, calEvent, parameters, jsEvent.pageY + 10);
                }
                else {
                    _this.showEventPopOver(element, calEvent, parameters, '');
                }
            },
            changeEventContent: function (element, event) {
            },
            titleFormat: 'MMM D YYYY',
            defaultView: this.renderType,
            eventDurationEditable: false,
            editable: true,
            eventLimit: true,
            fixedWeekCount: false,
            timezone: false,
            nextDayThreshold: '00:00',
            timeFormat: 'HH:mm (A)'
        };
        _defaultSettings.eventMouseover = _defaultSettings.eventClick;
        return _defaultSettings;
    };
    FgWebsiteCalendar.prototype.getTitleForAgendaWeek = function (view) {
        var currentDateFormat = FgLocaleSettingsData.momentDateFormat;
        var startDate = view.intervalStart;
        var endDate = view.intervalEnd.subtract(1, 'days');
        var title;
        switch (currentDateFormat) {
            case 'DD.MM.YYYY':
                title = startDate.format('DD.MM.') + ' - ' + endDate.format('DD.MM.YYYY');
                break;
            case 'D.M.YYYY':
                title = startDate.format('D.M.') + ' - ' + endDate.format('D.M.YYYY');
                break;
            case 'YYYY.MM.DD':
                title = startDate.format('YYYY.MM.DD') + ' - ' + endDate.format('MM.DD');
                break;
            case 'DD/MM/YYYY':
                title = startDate.format('DD/MM') + ' - ' + endDate.format('DD/MM/YYYY');
                break;
            case 'D/M/YYYY':
                title = startDate.format('D/M') + ' - ' + endDate.format('D/M/YYYY');
                break;
            case 'YYYY/MM/DD':
                title = startDate.format('YYYY/MM/DD') + ' - ' + endDate.format('MM/DD');
                break;
            case 'YYYY/M/D':
                title = startDate.format('YYYY/M/D') + ' - ' + endDate.format('M/D');
                break;
            case 'M/D/YYYY':
                title = startDate.format('M/D') + ' - ' + endDate.format('M/D/YYYY');
                break;
            case 'DD-MM-YYYY':
                title = startDate.format('DD-MM') + ' - ' + endDate.format('DD-MM-YYYY');
                break;
            case 'D-M-YYYY':
                title = startDate.format('D-M') + ' - ' + endDate.format('D-M-YYYY');
                break;
            case 'DD-MM-YY':
                title = startDate.format('DD-MM-YY') + ' - ' + endDate.format('DD-MM-YY');
                break;
            case 'YYYY-MM-DD':
                title = startDate.format('YYYY-MM-DD') + ' - ' + endDate.format('MM-DD');
                break;
            default:
                title = view.title;
                break;
        }
        return title;
    };
    FgWebsiteCalendar.prototype.getFormattedData = function (events) {
        var result = [];
        var _this = this;
        var filterDateArray = $('#fg-page-timeperiod-input').val().split("#");
        var listStartDate = moment(filterDateArray[0], "YYYY-MM-DD");
        var listEndDate = moment(filterDateArray[1], "YYYY-MM-DD");
        var listStartDateFormatted = listStartDate.format('YYYYMMDD');
        var listEndDateFormatted = listEndDate.format('YYYYMMDD');
        $.each(events, function (i, o) {
            var fromDate = moment(o.startDate, "YYYY-MM-DD h:mm:ss");
            var toDate = moment(o.endDate, "YYYY-MM-DD h:mm:ss");
            if (fromDate.isValid() && toDate.isValid()) {
                var evDates = _this.getDaysBetweenDates(fromDate, toDate);
                $.each(evDates, function (j, p) {
                    var P_moment = moment(p);
                    var P_moment_YMD = P_moment.format('YYYYMMDD');
                    var P_moment_YM = P_moment.format('YYYYMM');
                    if (P_moment_YMD >= listStartDateFormatted && P_moment_YMD <= listEndDateFormatted) {
                        if (typeof result[P_moment_YM] == 'undefined')
                            result[P_moment_YM] = [];
                        result[P_moment_YM].push({
                            evId: o.eventId,
                            evDetId: o.eventDetailId,
                            evClubId: o.clubId,
                            clubColorCode: o.clubColorCode,
                            evDate: p,
                            evTimestamp: P_moment.format('x'),
                            evStartDate: fromDate,
                            eventStartTimeStamp: o.startDateTimestamp,
                            eventEndTimeStamp: o.endDateTimestamp,
                            evEndDate: toDate,
                            evTitle: o.title,
                            evScope: o.scope,
                            isAllDay: o.isAllday,
                            evCategory: o.eventCategories,
                            evRole: o.eventRoleAreas,
                            isMasterRepeat: o.isMasterRepeat,
                            isClubAreaSelected: o.isClubAreaSelected,
                            rowId: i,
                            evClubs: { title: clubTitles[o.clubId], logo: clubLogoUrl.replace("#dummy#", o.clubId) }
                        });
                    }
                });
            }
        });
        return result;
    };
    FgWebsiteCalendar.prototype.getDaysBetweenDates = function (startDate, endDate) {
        var dates = [];
        if (startDate.format('YYYYMMDD') == endDate.format('YYYYMMDD')) {
            dates.push(startDate.clone().toDate());
        }
        else {
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
    };
    FgWebsiteCalendar.prototype.showEventPopOver = function (target, dataArray, parameters, top) {
        clearTimeout(_this.currentTimeout);
        $('.popover').popover('destroy');
        _this.currentTimeout = setTimeout(function () {
            var _this = this;
            target.popover({
                html: true,
                trigger: 'manual',
                container: '#fg-calendar-container',
                delay: { "show": 10, "hide": 5 },
                content: function () {
                    var content = FGTemplate.bind('eventPopOverTemplate', { 'e': dataArray });
                    content = content.replace("XXX", dataArray.detailId, 'g');
                    return content;
                },
                placement: function () {
                    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))
                        return 'right';
                    else
                        return 'bottom';
                },
            });
            target.popover('show');
            if (top != '')
                $('.popover').css('top', top);
            target.on('shown.bs.popover', function () {
                var detailUrl = detailJsonUrlPath.replace("dummyId", dataArray.eventDetailId, 'g');
                detailUrl = detailUrl.replace("**startTime**", dataArray.startTimestamp, 'g');
                detailUrl = detailUrl.replace("**endTime**", dataArray.endTimestamp, 'g');
                if (_this.popOverFetchRequest && _this.popOverFetchRequest.readystate != 4) {
                    _this.popOverFetchRequest.abort();
                }
                _this.popOverFetchRequest = $.ajax({
                    method: "GET",
                    url: detailUrl,
                    dataType: 'json',
                    data: parameters,
                    success: function (data) {
                        var popoverContent = FGTemplate.bind('eventPopOverSubTemplate', { 'd': data, calEvent: dataArray });
                        $("#calendar-popover-body").html(popoverContent);
                    }
                });
            });
        }, 500);
    };
    FgWebsiteCalendar.prototype.initListPopover = function () {
        var _this = this;
        $(_this.listElementId).on('mouseover', 'a.fg-event-link', function (e) {
            var element = $(this);
            var hrefLinkParams = element.attr('href').split('/');
            var index = element.attr('data-index');
            var calandarDetail = _this.eventData[index];
            var calEvent = {
                'end': moment(new Date(calandarDetail.endDate.replace(' ', 'T'))),
                'start': moment(new Date(calandarDetail.startDate.replace(' ', 'T'))),
                'id': index,
                'endTimestamp': hrefLinkParams[(hrefLinkParams.length - 1)],
                'startTimestamp': hrefLinkParams[(hrefLinkParams.length - 2)]
            };
            $.extend(calEvent, calandarDetail);
            var parameters = {};
            parameters['startDate'] = calEvent.start.format('YYYY-MM-DD');
            parameters['endDate'] = calEvent.end.format('YYYY-MM-DD');
            parameters['startTime'] = calEvent.start.format('HH:mm:ss');
            parameters['endTime'] = calEvent.end.format('HH:mm:ss');
            _this.showEventPopOver(element, calEvent, parameters, '');
        });
    };
    FgWebsiteCalendar.prototype.windowResize = function () {
        var _this = this;
        $(window).resize(function () {
            if ($(window).width() < 768 && _this.renderType != 'list') {
                _this.renderType = 'list';
                $("#fg-page-timeperiod-input").val($("#fg-page-timeperiod-input option:first").val());
                $("#fg-page-monthswitch-input").val($("#fg-page-monthswitch-input option:last").val());
                $('#fg-page-timeperiod-input').selectpicker('render');
                $('#fg-page-monthswitch-input').selectpicker('render');
                _this.render(false);
            }
        });
    };
    FgWebsiteCalendar.prototype.checkForMobileView = function () {
        var _this = this;
        if ($(window).width() < 768 && _this.renderType != 'list') {
            _this.renderType = 'list';
            $("#fg-page-timeperiod-input").val($("#fg-page-timeperiod-input option:first").val());
            $("#fg-page-monthswitch-input").val($("#fg-page-monthswitch-input option:last").val());
            $('#fg-page-timeperiod-input').selectpicker('render');
            $('#fg-page-monthswitch-input').selectpicker('render');
        }
    };
    FgWebsiteCalendar.prototype.findPopOverPosition = function () {
        var maxHeight = 300;
    };
    return FgWebsiteCalendar;
}());
