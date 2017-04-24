

FgOverview = {
    
    hideIfemptyChkBox: function(thisVAr) {
        if(thisVAr.prop('checked') === true){
            thisVAr.parent().siblings('div').find('input').removeAttr('disabled');
        } else {
            thisVAr.parent().siblings('div').find('input').attr('checked',false);
            thisVAr.parent().siblings('div').find('input').attr('disabled','disabled');
        }
        $.uniform.update($('form input:checkbox'));
    }
}