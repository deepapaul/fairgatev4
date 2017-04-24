/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgCalendarDetailView {    
    constructor() {

    }
    
    public initSettings() {
        this.handleGoogleMap();
        this.handleAttachmentClicks();
    }

    public handleGoogleMap() {
        
        $(document).ready(function () {
            
            let mapZoomValue = 15;
            let mapId = "googleMap";
            let mapDisplay = "ROADMAP";
            let showMarker = (event_location != '') ? 1 : 0;
            if (event_latitude != '' && event_longitude != '' && event_location != '' && event_location_flag == 1) {
                $(".fg-caledar-event-dtls-map-wrapper").removeClass('hide');
                FgMapSettings.mapShow(event_latitude, event_longitude, mapDisplay, mapZoomValue, showMarker, mapId, event_location);
                $(window).resize(function () {
                    FgMapSettings.mapResize(event_latitude, event_longitude, event_location);
                    FgMapSettings.mapInfoWindowResize();
                });
            }
        });
    }
    
    public handleAttachmentClicks() {
        $(document).on('click', '.fg-calender-attachment', function (e) {
            e.preventDefault();
            let filename = $(this).attr('data-filename');
            let encryptedname = $(this).attr('data-encrypted');
            let eventclubId = $(this).attr('data-clubid');
            
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
    }

}


