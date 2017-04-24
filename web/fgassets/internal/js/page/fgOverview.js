var FgOverview = function () {
    var settings;
    var defaultSettings = {
        boxTemplateId: 'overviewBox',
        contactId: '',
        contactProfileData: '', // Json containing initial informations of logged in contact
        contactClubId: '', // Club id of logged in contact
        currentClubId: '', // Club id of current club
        contactType: '', // type of contact (single person/company)
        profile: {
            title: '', // title for overview profile box
            templateId: 'overviewProfileBox', // ID of template to render profile data
            selectorDiv: 'profileBoxDiv', // ID of div to append profile box
            additionalClass: '' //additional box content wrapper css class
        },
        groups: {
            title: 'My groups', // title for personal overview groups box
            templateId: 'overviewGroupsBox', // ID of template to render group details
            selectorDiv: 'groupsBoxDiv', // ID of div to append groups box
            additionalClass: '' //additional box content wrapper css class
        },
        messages: {
            title: 'New messages', // title for new messages box
            templateId: 'overviewMessagesBox',	// ID of template to render new messages 
            selectorDiv: 'messagesBoxDiv', // ID of div to append messages box
            dataUrl: '', //url to obtain data
            additionalClass: '' //additional box content wrapper css class
        },
        forums: {
            title: 'Last forum posts', // title for forum posts box
            templateId: 'overviewForumsBox', // ID of template to render forum posts details
            selectorDiv: 'forumsBoxDiv', // ID of div to append forums box
            dataUrl: '', //url to obtain data
            additionalClass: 'fg-dashboard-wrap' //additional box content wrapper css class
        },
        articles: {
            title: 'Latest articles', // title for article box
            templateId: 'overviewArticleBox', // ID of template to render articles details
            selectorDiv: 'articlesBoxDiv', // ID of div to append article box
            dataUrl: '', //url to obtain data
            additionalClass: 'fg-dashboard-wrap' //additional box content wrapper css class
        },
        connections: {
            title: 'My contact connections', // title for overview connections box
            templateId: 'overviewConnectionsBox', // ID of template to render sponsored by details
            selectorDiv: 'connectionsBoxDiv', // ID of div to append connections box
            dataUrl: '', //url to obtain data
            additionalClass: 'fg-dashboard-wrap' //additional box content wrapper css class
        },
        nextbirthdays: {
            title: 'Next birthdays', // title for overview next birthdays box
            templateId: 'overviewNextBirthdaysBox', // ID of template to render next birthday details
            selectorDiv: 'nextBirthdaysBoxDiv', // ID of div to append next birthdays box
            dataUrl: '', //url to obtain data
            additionalClass: 'fg-dashboard-wrap', //additional box content wrapper css class
            params : '{}' //parameters to be passed in post
        },
        documents: {
            title: 'Last documents', // title for overview last documents box
            templateId: 'overviewDocumentsBox', // ID of template to render last documents details
            selectorDiv: 'documentsBoxDiv', // ID of div to append last birthdays box
            dataUrl: '', //url to obtain data
            additionalClass: 'fg-dashboard-wrap', //additional box content wrapper css class
            params : '{}' //parameters to be passed in post
        },
        members: {
            title: 'Members', // title for overview next birthdays box
            templateId: 'overviewMembersBox', // ID of template to render next birthday details
            selectorDiv: 'membersBoxDiv', // ID of div to append next birthdays box
            dataUrl: '', //url to obtain data
            additionalClass: 'fg-dashboard-wrap text-center', //additional box content wrapper css class
            params : '{}' //parameters to be passed in post
        },
        calendar: {
            title: 'Calendar', // title for overview next birthdays box
            templateId: 'overviewCalendarBox', // ID of template to render next birthday details
            selectorDiv: 'calendarBoxDiv', // ID of div to append next birthdays box
            dataUrl: '', //url to obtain data
            additionalClass: '', //additional box content wrapper css class
            params : '{}' //parameters to be passed in post
        },
    };

    var initSettings = function (options) {
        settings = $.extend(true, {}, defaultSettings, options);
    }
    
    // method to enclose each rendered data in a overview box
    var renderBox = function (box) {
        var boxObject = {};
        var boxContent = '';
        boxObject.content = box.content;
        boxObject.title = box.boxSettings.title;
        boxObject.additionalClass = box.boxSettings.additionalClass;
        boxContent = FGTemplate.bind(settings.boxTemplateId, boxObject);
        if (box.showBox) {            
            $('#'+box.boxSettings.selectorDiv).html(boxContent);
            //remove display none class if box needs to be shown
            if ($('#'+box.boxSettings.selectorDiv).parent('div').hasClass('fg-dis-none')) {
                $('#'+box.boxSettings.selectorDiv).parent('div').removeClass('fg-dis-none');
            }
        } else if(!box.isProfileBox){ /* In team/workgroup overview, on tab changing, if a box is not retaining, it should be hide */
            /*Not applicable in case of groupbox ie: isProfileBox is true */
            $('#'+box.boxSettings.selectorDiv).parent('div').addClass('fg-dis-none');
        }
    }
    
    var renderProfileBox = function () {
        var content = FGTemplate.bind(settings.profile.templateId, {'contactData': settings.contactProfileData});
        $('#'+settings.profile.selectorDiv).html(content);
        FgInternal.triggerChangePasswordPopUp();
    }
    
    var renderGroupsBox = function () {
        var showBox = false;
        if (settings.groups.data.length > 0) {
           showBox = true; 
        }
        var content = FGTemplate.bind(settings.groups.templateId, {'teamandgroupsData': settings.groups.data});
        renderBox({'boxSettings' : settings.groups, 'content' : content, 'showBox' : showBox, 'isProfileBox' : true });
        groupLinkCallBack();
    }
    
    var groupLinkCallBack = function() {
        $('.fg-group-link').on('click', function(){
            var ajaxUrl = $(this).attr('data-url');
            var type = $(this).attr('data-type');
            var clubid = $(this).attr('data-clubid');
            var contactid = $(this).attr('data-contactid');
            var roleid = $(this).attr('data-id');
      
            (type == 1) ? localStorage.setItem('team_'+clubid+'_'+contactid, JSON.stringify({'id': roleid, 'type': 'Team'})) : localStorage.setItem('workgroup_'+clubid+'_'+contactid, JSON.stringify({'id': roleid, 'type': 'Workgroup'}));
            window.location = ajaxUrl;
        });
    }

    var birthdaySuccessCallBack = function() {
        $('.fg-plus-click').on('click', function(){
            $(this).parent().find('.fg-bithday-contact').removeClass("hide");
            $(this).parent().find('.fg-bithday-contact1').addClass("hide");
            $(this).parent().find('.fg-minus-click').removeClass("hide");
            $(this).parent().find('.fg-plus-click').addClass("hide");
        });
        $('.fg-minus-click').on('click', function(){
            $(this).parent().find('.fg-bithday-contact').addClass("hide");
            $(this).parent().find('.fg-bithday-contact1').removeClass("hide");
            $(this).parent().find('.fg-minus-click').addClass("hide");
            $(this).parent().find('.fg-plus-click').removeClass("hide");
        });
    } 
    var documentSuccessCallBack = function() {
        $('.fg-plus-click').on('click', function(){
            $(this).parent().find('.fg-doc-block-more').removeClass("hide");
            $(this).parent().find('.fg-minus-click').removeClass("hide");
            $(this).parent().find('.fg-plus-click').addClass("hide");
        });
        $('.fg-minus-click').on('click', function(){
            $(this).parent().find('.fg-doc-block-more').addClass("hide");
            $(this).parent().find('.fg-minus-click').addClass("hide");
            $(this).parent().find('.fg-plus-click').removeClass("hide");
        });
//        $(document).on('click', ".fg-dev-read", function (event) {
//        $(document).on('click', ".fg-dev-read", function () {
        $('.fg-doc-unread').on('click', function(){  
            $(this).removeClass('fg-strong');
        });
    }
    
    
      var calendarDetailLinkFunc = function() {
        $('.fg-calendarbox-details-link').on('click', function(){
            
          var dataStartDate = $(this).attr('data-start-date');
          var dataEndDate = $(this).attr('data-end-date');
          var targetUrl = $(this).attr('data-link');
          var startTimestamp = (parseInt(moment(dataStartDate,'YYYY-MM-DD HH:mm:ss').format('X'))) + (moment().utcOffset()*60);
          var endTimestamp = (parseInt(moment(dataEndDate,'YYYY-MM-DD HH:mm:ss').format('X'))) + (moment().utcOffset()*60);
          targetUrl= targetUrl.replace('startDate', startTimestamp);
          targetUrl= targetUrl.replace('endDate', endTimestamp);
      
          window.location.href= targetUrl;
         
        });
 
    } 
     
    
    
    // method to render contact connection html
    var renderMessagesBox = function () {
        var showBox = true;              
        var content = '';
        $.ajax({
            type: 'GET',
            url: settings.messages.dataUrl,
            success: function( data ) {  
                content = FGTemplate.bind(settings.messages.templateId, {settings: data});
                renderBox({'boxSettings' : settings.messages, 'content' : content, 'showBox' : showBox, 'isProfileBox' : false });
            }
        });
    }
    // method to render contact connection html
//    var renderForumsBox = function () {
//        var showBox = false;              
//        var content = '';
//        $.ajax({
//            type: 'GET',
//            url: settings.messages.dataUrl,
//            success: function( data ) {  
//                if (data.length > 0) {
//                    showBox = true;
//                    content = FGTemplate.bind(settings.forums.templateId, {settings: settings});
//                }
//                renderBox({'boxSettings' : settings.forums, 'content' : content, 'showBox' : showBox });
//            }
//        });
//    }
    // method to render contact connection html
    var renderConnectionsBox = function () {
        var showBox = false;              
        var content = '';
        $.ajax({
            type: 'GET',
            url: settings.connections.dataUrl,
            success: function( data ) {  
                if (data.length > 0) {
                    showBox = true;
                    content = FGTemplate.bind(settings.connections.templateId, { connections : data});
                }
                renderBox({'boxSettings' : settings.connections, 'content' : content, 'showBox' : showBox, 'isProfileBox' : false });
            }
        });
    }
    // method to render contact connection html
    var renderNextBirthdaysBox = function () {
        var showBox = true;              
        var content = ''; 
        $.ajax({
            type: 'POST',
            url: settings.nextbirthdays.dataUrl,
            data: settings.nextbirthdays.params,
            success: function( data ) {
                content = FGTemplate.bind(settings.nextbirthdays.templateId, {data: data});
                renderBox({'boxSettings' : settings.nextbirthdays, 'content' : content, 'showBox' : showBox, 'isProfileBox' : false });
                birthdaySuccessCallBack();
            }
        });

    }
    // method to render contact connection html
    var renderMembersBox = function () {
        var showBox = true;              
        var content = ''; 
        $.ajax({
            type: 'POST',
            url: settings.members.dataUrl,
            data: settings.members.params,
            success: function( data ) {
                content = FGTemplate.bind(settings.members.templateId, {data: data});
                renderBox({'boxSettings' : settings.members, 'content' : content, 'showBox' : showBox, 'isProfileBox' : false });
                renderPieChart('functions', data.functions);
                renderPieChart('residences', data.residences);
            }
        });

    }
    // method to render contact connection html
    var renderDocumentsBox = function () {
        var showBox = false;              
        var content = '';
        $.ajax({
            type: 'POST',
            url: settings.documents.dataUrl,
            data: settings.documents.params,
            success: function( data ) { 
                if (data.myDocuments.length > 0) {
                    showBox = true;
                    content = FGTemplate.bind(settings.documents.templateId, {settings: data, showBadge: settings.documents.showBadge});
                }
                renderBox({'boxSettings' : settings.documents, 'content' : content, 'showBox' : showBox, 'isProfileBox' : false });
                documentSuccessCallBack();
            }
        });
    }
    
    // method to render forum box
    var renderForumBox = function () {
        var showBox = false;              
        var content = '';
        $.ajax({
            type: 'POST',
            url: settings.forums.dataUrl,
            data: settings.forums.params,
            success: function( data ) { 
                if (data.forums.length > 0) {
                    showBox = true;
                    content = FGTemplate.bind(settings.forums.templateId, {settings: data});                   
                }                
                renderBox({'boxSettings' : settings.forums, 'content' : content, 'showBox' : showBox, 'isProfileBox' : false });               
            }
        });
    }  
    // method to render article box
    var renderArticleBox = function () {
        var showBox = false;
        var content = '';
        $.ajax({
            type: 'POST',
            url: settings.articles.dataUrl,
            data: settings.articles.params,
            success: function (data) {
                if (data.article.length > 0) {
                    showBox = true;
                    content = FGTemplate.bind(settings.articles.templateId, {settings: data});
                }
                renderBox({'boxSettings': settings.articles, 'content': content, 'showBox': showBox, 'isProfileBox': false});
            }
        });
    }
    
    // method to render calendar box
    var renderCalendarBox = function () {
        var showBox = false;              
        var content = '';    
        $.ajax({
            type: 'POST',
            url: settings.calendar.dataUrl,
            data: settings.calendar.params,
            success: function( data ) { 
                
                if (data.length > 0) {
                    showBox = true;
                    content = FGTemplate.bind(settings.calendar.templateId, {settings: data,roleType:settings.calendar.params.roleType,currentClubId:settings.calendar.params.currentClubId});                   
                }                
                renderBox({'boxSettings' : settings.calendar, 'content' : content, 'showBox' : showBox });               
                calendarDetailLinkFunc();
            }
        });
    } 

    var renderPieChart = function (chartName, result) {
        plotPieChart(chartName, JSON.stringify(result));
        $(window).resize(function() {
            plotPieChart(chartName, JSON.stringify(result));
        });
        $(window).load(function() {
            plotPieChart(chartName, JSON.stringify(result));
        });
    }
    
    var plotPieChart = function(chartName, chartResult) {
        var templateSelector = (chartName == 'functions') ? settings.members.functions.templateId : settings.members.residences.templateId;
        var innerRadiusEnable = (chartName == 'functions') ? settings.members.functions.innerRadiusEnable : settings.members.residences.innerRadiusEnable;
        chartResult = JSON.parse(chartResult);
        chartResultOptimized = chartResult;
        if (!chartResultOptimized.length) {
            $(templateSelector).children().remove();
            $(templateSelector).append('<div class="dashboard-nodata" style="color:#D8D8D8;">'+settings.members.emptyMsg+'</div>');
        } else {
            var width = $(templateSelector).outerWidth();
            var height = $(templateSelector).outerHeight();
            var radius = (width > height) ? height*45/100 : width*45/100; //in pixel
            var innerRadius = (innerRadiusEnable) ? radius/2 : 0;			
            plotGraph.init_pie(chartResultOptimized, templateSelector, radius, innerRadius);
            bindHoverForPieChart(templateSelector);
        }
    }

    var bindHoverForPieChart = function(templateSelector) {     
        $(templateSelector).bind("plothover", function (event, pos, item) {
            if (item) {
                var label = item.series.label;
                var x = item.datapoint[0].toFixed(0);
                $("#tooltip").html(label+": "+ item.series.data[0][1] +" ("+ x+"%"+")")
                        .css({top: pos.pageY-200, left: pos.pageX-10,"background-color":"#000", "opacity":0.5,"color":"white"})
                        .fadeIn(200);
            } else {
                $("#tooltip").hide();
            }
        });                
    }
    return {
        initPersonalOverview: function (options) {
            initSettings(options);
            renderProfileBox();
            renderGroupsBox();
            renderMessagesBox();
//            renderForumsBox();
            renderConnectionsBox();
            renderNextBirthdaysBox();
            renderDocumentsBox();
            renderForumBox();
            renderCalendarBox();
            renderArticleBox();
        },
        initRoleOverview: function (options) {
            initSettings(options);
            renderMembersBox();
//            renderForumsBox();
            renderNextBirthdaysBox();
            renderDocumentsBox();
            renderForumBox();
            renderCalendarBox();
            renderArticleBox();
        }
    };
}();

							