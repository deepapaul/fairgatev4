/**
 * Wrapper function for calender list
 * 
 */
fgCalendar = {
};


///Document ready
$(document).ready(function () {


    $(".fg-action-menu-wrapper").FgPageTitlebar({
        actionMenu: true,
        title: true,
        calendarSwitch: true
    });
    
    
    var dataSet={
        0:{
            label:"jan",
            data:[
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
            ]
        },
        1:{
            label:"feb",
            data:[
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
            ]
        },
        2:{
            label:"mar",
            data:[
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" },
                { eventId:1, eventDetId:2, title:"test 1", startDate:"20 10 2015" }
            ]
        }
        
        
        
    }



    $('input[type=radio].make-switch').change(function () {
        if (this.id == 'list') {
            var content = FGTemplate.bind('calendarListTemplate', {'dataSet': dataSet});
            $('#calendarList').html(content);
        } else {
            $('#calendarList').html('');
        }
        $('.fg-first-col input').uniform();

    });

    


});