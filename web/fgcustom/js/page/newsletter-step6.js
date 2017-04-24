$(function(){
    $(document).ready(function(data){
        $('div.date input').parent().datetimepicker(FgApp.dateTimeFormat); 
        var currdatetime = moment().format(FgLocaleSettingsData.momentDateTimeFormat); 
        $('.date').datetimepicker('setStartDate', currdatetime);
        if(sendDate != '' && moment(sendDate).isValid()){
            //$("#optionsRadios26").trigger('click');
        } else {
            $('.date').datetimepicker('setDate', new Date());  
        }
        $(".date").datetimepicker("remove");
        if((sendStatus == "sending")||((recipientList == null) && (manualContactIsSet==1))){
             $('.button-submit').hide();
    }
    });
   
   
   $("#optionsRadios26").click(function () {
       $('#fg-dev-calender').removeClass('disabled');
       $('#fg-dev-calender').parent().removeClass('fg-disabled-icon');
        $('#fg-dev-input6').removeClass('fg-disabled-icon');
        $('#fg-dev-input6').addClass('fg-normal-icon');
        $('#fg-dev-input6').prop('disabled', false);
        $('#fg-dev-input6').prop('readonly', false);
        $('div.date input').parent().datetimepicker(FgApp.dateTimeFormat); 
        
        var currdatetime = moment().format(FgLocaleSettingsData.momentDateTimeFormat); 
        $('.date').datetimepicker('setStartDate', currdatetime);
        if(sendDate != '' && moment(sendDate).isValid()){
            var formattedDate = moment(sendDate, 'YYYY-MM-DD HH:mm');
            var sendDateObj = new Date(formattedDate);
            $('.date').datetimepicker('setDate', sendDateObj);
        }
    });
     $("#optionsRadios25").click(function () {
         $('#fg-dev-calender').addClass('disabled');
         $('#fg-dev-calender').parent().addClass('fg-disabled-icon');
         $('#fg-dev-input6').addClass('fg-disabled-icon');
         $('#fg-dev-input6').removeClass('fg-normal-icon');
         $('#fg-dev-input6').prop('disabled', true);
         $(".date").datetimepicker("remove");
    });
   
   
    $('#send').click(function(){
         var buttonId = this.id;
         var display;
        if (buttonId == 'send') {
           var sendingType= $('input[name=optionsRadios]:checked').val();
           if(flag==0){
               display=null;
           }else{
            display= ($('input[name=service]:checked').val())?1:0;
            }
            sendingFun(sendingType,display);
        }
    });
    
    $(document).off('click', 'a#updatenow');
    $(document).on('click', 'a#updatenow', function() {
        var updateId = $(this).attr('data-id');
        var updatePath = $(this).attr('data-url').replace('recipientId', updateId);
        FgXmlHttp.post(updatePath, {newsletterId: newsletterId}, false, function() {
            FgFormTools.handleUniform();
        });
    });
    
    function sendingFun(sendingType,display){
        
        if(sendingType == 'option2'){
           sendingTime = $('div.date input').val(); 
        }else{
            var sendingTime="now";
        }
        if(sendingTime!=""){
            timeSelectError(0);
            FgXmlHttp.post(updateSendingPath, {'type':pageType,'sendingTime':sendingTime,'sendingType':sendingType,'id':newsletterId,'display':display} ,false, callback, failCallbackFunctions, '0'); 
        }else{
            //var data = {'errorMsg':"REQUIRED",'status':"ERROR"};
            timeSelectError(1);
        }
    }
    function timeSelectError(data){
        if(data){
            $('#templateError').show();
            $('#failcallbackServerSide').show();
        }else{
            $('#templateError').hide();
            $('#failcallbackServerSide').hide();
        }
    }
    function callback(){
        $('document').removeClass('fairgatedirty');
        window.location.href = sendPath;
    }
    function failCallbackFunctions(data){
        $('#failcallbackServerSide').hide();
            if(data.status =="ERROR"){
            $('#failcallbackServerSide span').text(data.errorMsg);
            $('#failcallbackServerSide').show();
            }
    }
});

