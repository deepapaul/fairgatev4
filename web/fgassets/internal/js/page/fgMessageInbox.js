var draftClickedFlag = 0;
var datatableOptionsDrafts;
var InboxListTableObj;
var DraftListTableObj;

$(function () {        
        //apply action menu
        fgMessageInbox.handleActionMenu(actionMenuTextInbox);
        FgPopOver.init('.fg-dev-Popovers');
        /* menu bar ---- */
        $( ".fg-action-menu-wrapper" ).FgPageTitlebar({
            actionMenu: true,
            title     : true,
            tab       : true,
            counter   : true,            
            tabType     : 'client'
        });
        /* Toggle between drafts and inbox*/         
            $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
                var clickedId = $(this).attr('data_id');
                if(clickedId=='drafts') {
                 fgMessageInbox.showDrafts();         
                } else {
                  fgMessageInbox.showInbox();  
                }
            })
        
        //For inbox
        var columnDefs = [
            { type: "checkbox", width:"1%",orderable: false, sortable: false, targets: 0, data: function(row, type, val, meta){
                    return "<input type='checkbox'  id='"+row['messageId']+"' name='check' class='dataClass fg-dev-avoidicon-behaviour' >";
                } },
                { "name": "subject",  width:"19%",orderable: false, sortable: false, "targets": 1 , data: function(row, type, val, meta){
                    var boldClass = (row['unreadCount'] > "0"||(row['readAt'] == null&& row['created_by']!=currentContact)) ?  'fg-strong':'';
                    var subjectLine = "<a class='msg-a-"+row['messageId']+" "+boldClass+"' href='"+conversationPath.replace("MESSAGEID", row['messageId'])+"'>"+row['subject']+"</a><br />"+row['createdAt'];                        
                    return  subjectLine;
                } },
                { "name": "replies", width:"19%",orderable: false, sortable: false, "targets": 2, data: function(row, type, val, meta){
                    unreadCount = (row['unreadCount'] <= "0") ? '' : row['unreadCount']+" "+textNew;
                    unreadLine = "<br /><span id='msg-unread-span-"+row['messageId']+"' >"+unreadCount+"</span>"; 
                    return row['repliesCount']+unreadLine;
                } },
                { "name": "notification", width:"19%",orderable: false, sortable: false, "targets": 3, data: function(row, type, val, meta){                    
                    var notificationLine =  ( row['notification'] == 1 ) ? '<div class="fg-static-on" id="notification_'+row['messageId']+'" >'+textOn+'</div>':'<div class="fg-static-off" id="notification_'+row['messageId']+'">'+textOff+'</div>';                    
                    return  notificationLine; 
                } },
                { "name": "updated", width:"19%",orderable: false, sortable: false, "targets":4, data: function(row, type, val, meta){
                    var isCompanyImageFlag =(row['isCompanyUpdated'] == 0 ) ? 'fg-round-img' : '';
                    if( row['isCompanyUpdated'] == 0 || row['updatedImage'] == '') { 
                        var updatedImage = (row['updatedImage']) ? row['updatedImage'] : '';
                        var imgBlock = "<div class='fg-profile-img-blk35 "+isCompanyImageFlag+" ' style=background-image:url('"+ updatedImage  +"') ></div>";                                               
                    } else {
                        var imgBlock = "<div class='fg-profile-img-blk-CH35 ' ><img src='"+row['updatedImage']+"' alt=''></div>";                        
                    } 
                    updatedStr = imgBlock+"<span class='fg-table-reply'>"+row['updatedAt']+"<br />"+textBy+" "+row['updatedBy']+"</span>";                                       

                    return (row['updatedBy'] != '') ? updatedStr : '-';
                } },
                { "name": "sender", width:"19%",orderable: false, sortable: false, "targets": 5, data: function(row, type, val, meta){
                    var isCompanyImageFlag =(row['isCompanySender'] == 0 ) ? 'fg-round-img' : '';
                    if( row['isCompanySender'] == 0 || row['senderImage'] == '') { 
                        var senderImage = (row['senderImage']) ? row['senderImage'] : '';
                        var imgBlock = "<div class='fg-profile-img-blk35 "+isCompanyImageFlag+" ' style=background-image:url('"+ senderImage  +"') ></div>";                                               
                    } else {
                        var imgBlock = "<div class='fg-profile-img-blk-CH35 ' ><img src='"+row['senderImage']+"' alt=''></div>";                        
                    }  
                    var contactname = ( (row['is_stealth_mode'] == 1) && (currentContact != row['created_by'])) ? row['senderName'] : "<a href='"+pathCommunityProfile.replace("CONTACTID", row['created_by'])+"'>"+row['senderName']+"</a>";
                    return imgBlock+"<span class='fg-table-reply'>"+contactname+"</span>";   
                    
                } }
            ] ;
    var datatableOptions = {
            fixedcolumn:false,
            columnDefFlag:true,            
            ajaxPath: pathInboxListing,
            columnDefValues: columnDefs,
            displaylengthflag: true,
            displaylength: limit,
            opt: {language : {
                    lengthMenu: ''
            } }
        }; 

    InboxListTableObj = FgDatatable.listdataTableInit('datatable-messages', datatableOptions );  
    //After listing inbox , make coloums adjust + disable markRead action menu disabled if no unread messages are there
    setTimeout(function(){ 
        InboxListTableObj.columns.adjust(); 
        fgMessageInbox.handleMarkReadActionMenu();        
    }, 1500);
   
    //For inbox end
    
    //For draft
        var columnDefsDrafts = [{ type: "checkbox", width:"1%",orderable: false, sortable: false, targets: 0, data: function(row, type, val, meta){
                    return "<input type='checkbox'  id='"+row['messageId']+"' name='draft-check' class='dataClass fg-dev-avoidicon-behaviour' >";
                } },
                { "name": "subject", orderable: false, sortable: false,  "targets": 1 , data: function(row, type, val, meta){
                    var subject = (row['subject'] == "") ? "-" : row['subject'];
                    var editPath = (row['step'] == "1") ? pathMessageEditStep1.replace("MESSAGEID", row['messageId']) : pathMessageEditStep2.replace("MESSAGEID", row['messageId']);
                    var subjectLine = "<a href='"+editPath+"'>"+subject+"</a>";                    
                        
                    return  subjectLine;
                } },
                { "name": "receivers", orderable: false, sortable: false, "targets": 2, data: function(row, type, val, meta){ 
                    var popupLine = '';
                    if(row['otherReceiversCount']) {                        
                        popupLine = ' '+textAnd+' '+'<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="'+row['otherReceiverNames']+'" data-original-title="">'+row['otherReceiversCount']+' '+textMore+'</i>';                        
                    }
                    return row['receiverNames']+popupLine;
                } },
                { "name": "created_on", orderable: false, sortable: false, "targets": 3, data: function(row, type, val, meta){                    
                    return row['createdAt']; 
                } },                
            ] ;
    datatableOptionsDrafts = {
            fixedcolumn:false,
            columnDefFlag:true,            
            ajaxPath: pathInboxDrafts,
            columnDefValues: columnDefsDrafts,    
            displaylengthflag: true,
            displaylength: limit,
            opt: {language : {
                    lengthMenu: ''
            } }
        };      
    //For draft end
});

var fgMessageInbox = {
    /*show coinfirmation popup*/
    showConfirmationPopup: function(checkedIds, selected, isDraft) {        
        $.post(pathConfirmationPopup, {'messageIds' : checkedIds, 'selected': selected, 'isDraft': isDraft}, function(data) {
            FgModelbox.showPopup(data);            
        });               
    },
    /*function for delete message*/
    deleteMessage: function(messageIds, isDraft) {
        var params = {'messageIds' : messageIds, 'isDraft': isDraft};
        FgXmlHttp.post(pathDeleteMessage, params, '', fgMessageInbox.redrawList, false, false);
    },   
    /*Call back function*/
    redrawList: function(data) {          
        FgModelbox.hidePopup(); 
        //listTable.draw();
        if(data.isDraft == "1") {
            DraftListTableObj.draw();
            if(data.deletedCount) {
                fgMessageInbox.updateCount(countDrafts, data.deletedCount, true); 
            }
        } else {
            InboxListTableObj.draw();    
            if(data.deletedCount) {
                fgMessageInbox.updateCount(countInbox, data.deletedCount, false); 
            }
            setTimeout(function(){         
                fgMessageInbox.handleMarkReadActionMenu();        
            }, 1500);
        }
    }, 
    /*update counter*/
    updateCount: function(countText, deletedCount, isDraft) {
        var totalCount =  (parseInt(countText));
        countText = totalCount - parseInt(deletedCount);    
        if(isDraft) {
            countDrafts = countText;
            $('#fg-messages-count-drafts').html(countText);
        } else {
            countInbox = countText;
            $('#fg-messages-count-inbox').html(countText);
        }
        
         countDraft = $('#fg-messages-count-drafts').text()
         countInbox = $('#fg-messages-count-inbox').text()
         var TotalCount = parseInt(countDraft)+parseInt(countInbox)
        $('.fg-action-counter small').html(TotalCount);
    },
    /*function for make messages unread*/
    markAsUnread: function(messageIds) {
        var params = {'messageIds' : messageIds};
        FgXmlHttp.post(pathUnreadMessage, params, '', fgMessageInbox.unreadCallback, false, false);
    },
    
    /* Call back function after making unread */
    unreadCallback: function(data) {
        if(data.resultArray) {
            $.each(data.resultArray, function(messageid, unreadcount) {
                if(unreadcount.messagesCount > 0 ||( unreadcount.createdBy !=currentContact)) {
                    if(!$(".msg-a-"+messageid).hasClass("fg-strong")) {
                        $(".msg-a-"+messageid).addClass("fg-strong");
                    }
                }
                if(unreadcount.messagesCount > 0 ){
                    $( "#msg-unread-span-"+messageid).html( unreadcount.messagesCount+" "+textNew ); 
                }
            });            
        }        
        FgCheckBoxClick.FgClearCheckAll();
        setTimeout(function(){         
            fgMessageInbox.handleMarkReadActionMenu();        
        }, 1500);
    },
    /* Call back function after making read */
    readCallback: function(data) {        
        if(data.resultArray) {
            $.each(data.resultArray, function(key, messageid) {                  
                if($(".msg-a-"+messageid).hasClass("fg-strong")) {
                    $(".msg-a-"+messageid).removeClass("fg-strong");
                }
                $( "#msg-unread-span-"+messageid).html("");                          
            }); 
        }
        FgCheckBoxClick.FgClearCheckAll();
        setTimeout(function(){         
            fgMessageInbox.handleMarkReadActionMenu();        
        }, 1500);
    },
    /*function for make messages read*/
    markAsRead: function(messageIds) {
        var params = {'messageIds' : messageIds};
        FgXmlHttp.post(pathReadMessage, params, '', fgMessageInbox.readCallback, false, false);
    },
    
    /*function for setNotification*/
    setNotification: function(messageIds, status, fromActionMenu) { 
       var params = {'messageIds' : messageIds, 'status': status};
       FgXmlHttp.post(pathSetNotification, params, '', fgMessageInbox.setNotificationCallback, false, false);      
    },
    /* set Notification Callback */
    setNotificationCallback: function(data) {         
        if(data.resultArray) {            
            $.each(data.resultArray, function(key, messageid) {  
                if(data.notificationStatus == "1") {
                    $("#notification_"+messageid).removeClass("fg-static-off").addClass("fg-static-on").html(textOn);  
                } else {
                    $("#notification_"+messageid).removeClass("fg-static-on").addClass("fg-static-off").html(textOff);  
                }                   
            }); 
        }  
        FgCheckBoxClick.FgClearCheckAll();  
    },    
    /*set radio buttons inside datatable*/
    handleRadiobutton: function() {
        $('input[type="radio"]').each(function( index ) {
            if(this.hasAttribute("checked")) {
                this.checked = true;
            }
        });
    },
    /*Show drafts*/
    showDrafts: function () {       
        $('#datatable-row-draft').removeClass("hide");
        $('#datatable-row-inbox').addClass("hide");
      //  $(".fg-action-counter small").html(countDrafts);
        if(draftClickedFlag == 0) {
            DraftListTableObj = FgDatatable.listdataTableInit('datatable-messages-draft', datatableOptionsDrafts );  // set draft initialise
            setTimeout(function(){ DraftListTableObj.columns.adjust(); }, 200);
        }
        draftClickedFlag++;
        fgMessageInbox.handleActionMenu(actionMenuTextDraft);    
        FgCheckBoxClick.handleEmptyDatatableMenu();
    },
    /*Show Inbox*/
    showInbox: function () {
        $('#datatable-row-draft').addClass("hide");
        $('#datatable-row-inbox').removeClass("hide");
       // $(".fg-action-counter small").html(countInbox);
        fgMessageInbox.handleActionMenu(actionMenuTextInbox);  
        FgCheckBoxClick.handleEmptyDatatableMenu();
        setTimeout(function(){ InboxListTableObj.columns.adjust(); }, 200);
    },
    
    //apply action menu
    handleActionMenu: function(actionMenuText) {
        scope = angular.element($("#BaseController")).scope();  
        scope.$apply(function(){
            scope.menuContent = actionMenuText;
        });
    },
    //To get all message ids in data list
    getAllMessageIdsInList: function (type) {
        if(type == "inbox") {
            var dataObj = InboxListTableObj.rows({
                        order: 'applied', // 'current', 'applied', 'index',  'original'
                        search: 'applied', // 'none',    'applied', 'removed'
                        page: 'all'      // 'all',     'current'
                    }).data();    
        } else {
            var dataObj = DraftListTableObj.rows({
                        order: 'applied', // 'current', 'applied', 'index',  'original'
                        search: 'applied', // 'none',    'applied', 'removed'
                        page: 'all'      // 'all',     'current'
                    }).data();    
        }                     
        var messageIds = _.pluck(dataObj, 'messageId');
        var checkedIds = JSON.stringify(messageIds).replace(/^\[|]$/g, '');          
        return checkedIds;
    },
    
    //disable markRead action menu in inbox if no unread messages are there
    //enable markRead action menu if unread messages are there
    handleMarkReadActionMenu: function() {
        unreadElements = $('div#datatable-row-inbox').find(".fg-strong");
        if(unreadElements.length == 0) {
            actionMenuTextInbox.active.none.messageMarkAsRead.isActive = 'false';
            actionMenuTextInbox.active.single.messageMarkAsRead.isActive = 'false';
            actionMenuTextInbox.active.multiple.messageMarkAsRead.isActive = 'false'; 
            fgMessageInbox.handleActionMenu(actionMenuTextInbox); 
        } else {
            actionMenuTextInbox.active.none.messageMarkAsRead.isActive = 'true';
            actionMenuTextInbox.active.single.messageMarkAsRead.isActive = 'true';
            actionMenuTextInbox.active.multiple.messageMarkAsRead.isActive = 'true'; 
            fgMessageInbox.handleActionMenu(actionMenuTextInbox); 
        }
    }
    
    
};