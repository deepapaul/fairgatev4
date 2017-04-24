$(function() {
    $('.timeline-page').bootpag({
        total: pages,
        maxVisible: 5
    }).on("page", function(event, num) {
        var ntval = true;
        var dirtyfmconfirm = "";
        if ($(".ulcls").find("textarea").hasClass('fairgatedirty')) {
            dirtyfmconfirm = confirm(dirtyformconfirm);
            ntval = false;
        }
        var rand = Math.random();
        var url = noteUrl+"?rand=" + rand;
        if ((dirtyfmconfirm == true) || (ntval == true)) {
            $.ajax({
                type: "POST",
                url: url,
                data: {'page': num},
                success: function(data)
                {
                    var total = $('.totalrecords').val();
                    var pages = $('.pages').val();
                    var limit = $('.limit').val();
                    var offset = (num - 1) * limit;
                    var startValue = offset + 1;
                    var endValue = num * limit;
                    if (pages == num) {
                        var endValue = total;
                    }
                    $(".pagshow").remove();
                    $("#pagdiv").html(data);
                    var paginationMg = paginationMsg.replace("#groupA#", startValue).replace("#groupB#",endValue).replace("#groupC#",total);
                    $('.ajaxpag_show').prepend('<div class="timeline-show-text pull-left pagshow">' + paginationMg + '</div>');
                    FgTextAreaAuto.init();
                    FgApp.init();
                }
            });
        } else {
            $(".bootpag").find("li").removeClass("disabled");
        }
    });
    callbackfn();
    FgUtility.moreTab();
    FgTextAreaAuto.init();
    $("#reset_changes").on('click', function() {
        $(".newRow").remove();
    });
    $('.'+clickClass).on('click', '.addField', function() {
        addFieldCount = 'new_' + $.now();
        var curdate = moment().format(FgLocaleSettingsData.momentDateFormat);
        var curtime = moment().format(FgLocaleSettingsData.momentTimeFormat)
        var result_data = FGTemplate.bind('newContactnote', {content: {'addCount': addFieldCount, 'curdate': curdate, 'curtime': curtime}});
        $('.ulcls').prepend(result_data);
        FgTextAreaAuto.init();
        $("#form1").trigger('checkform.areYouSure');
        
    });
    $(document).on('click', '.newRowDelete', function() {
        $(this).parents('.newRow').slideUp(function() {
            $(this).remove();
        })
    });
});
function saveChanges() {
    $(".newRow").find("textarea").addClass('fairgatedirty');
    var objectGraph = {};
    $("form :input").each(function() {
        if ($(this).hasClass("fairgatedirty")) {
            var inputVal = ''
            inputVal = $(this).val();
            if (inputVal !== '') {
                if (typeof $(this).attr('data-key') !== 'undefined') {
                    converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
            }
        }
    });
    var attributes = JSON.stringify(objectGraph);
    FgXmlHttp.post(updateUrl, {'attributes': attributes}, false, callbackfn);
}
function callbackfn() {
    FgApp.init();
    FormValidation.init('form1', 'saveChanges');
    FgInputTag.handleUniform();
    FgUtility.moreTab();
    FgPageTitlebar.setMoreTab();
    
}
