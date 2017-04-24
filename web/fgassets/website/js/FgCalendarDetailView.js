var FgCalendarDetailView = (function () {
    function FgCalendarDetailView() {
    }
    FgCalendarDetailView.prototype.initSettings = function () {
        this.handleGoogleMap();
        this.handleAttachmentClicks();
    };
    FgCalendarDetailView.prototype.handleGoogleMap = function () {
        $(document).ready(function () {
            var mapZoomValue = 15;
            var mapId = "googleMap";
            var mapDisplay = "ROADMAP";
            var showMarker = (event_location != '') ? 1 : 0;
            if (event_latitude != '' && event_longitude != '' && event_location != '' && event_location_flag == 1) {
                $(".fg-caledar-event-dtls-map-wrapper").removeClass('hide');
                FgMapSettings.mapShow(event_latitude, event_longitude, mapDisplay, mapZoomValue, showMarker, mapId, event_location);
                $(window).resize(function () {
                    FgMapSettings.mapResize(event_latitude, event_longitude, event_location);
                    FgMapSettings.mapInfoWindowResize();
                });
            }
        });
    };
    FgCalendarDetailView.prototype.handleAttachmentClicks = function () {
        $(document).on('click', '.fg-calender-attachment', function (e) {
            e.preventDefault();
            var filename = $(this).attr('data-filename');
            var encryptedname = $(this).attr('data-encrypted');
            var eventclubId = $(this).attr('data-clubid');
            $('#calendarAttachmentForm').remove();
            $form = $("<form id='calendarAttachmentForm' method='post' action=" + downloadPath + "></form>");
            $form.append('<input type="hidden" id="filename" name="filename">');
            $form.append('<input type="hidden" id="encrypted" name="encrypted">');
            $form.append('<input type="hidden" id="eventclubId" name="eventclubId">');
            $('body').append($form);
            $('#filename').val(filename);
            $('#encrypted').val(encryptedname);
            $('#eventclubId').val(eventclubId);
            $form.submit();
        });
    };
    return FgCalendarDetailView;
}());
//# sourceMappingURL=FgCalendarDetailView.js.map