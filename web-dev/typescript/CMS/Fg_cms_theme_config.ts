/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
var FgXmlHttp = FgXmlHttp;
var FgModelbox = FgModelbox;
var FGTemplate = FGTemplate;
var _this;
class FgCmsThemeConfig {
    public themeConfigDuplicatePath:string;
    public themeConfigDeletePopupPath:string;
    public themeConfigDeletePath:string;
    public duplicateTemplate:string;
    public themeConfigActivatePath:string;
    public StatusElementTemplate:string;
    public deleteElementTemplate:string;
    public themeConfigEditPath:string;
    constructor() {
        this.handleDuplication();
        this.handleDelete();
        this.handleActivate();
    }
 
    public handleDuplication(){
        let _this = this;
        $(document).off('click','.fg_theme_copy_config');
        $(document).on('click','.fg_theme_copy_config',function(){
            let configId = $(this).attr('data-configid');
            FgXmlHttp.post(_this.themeConfigDuplicatePath, {'configId' : configId}, false, _this.duplicateSuccessCallback);
        });
    }
    
    public duplicateSuccessCallback(data){
       var themeEditConfigUrl =  _this.themeConfigEditPath.replace('**placeholder**',data.details.id);
       var duplicateHtml = FGTemplate.bind(_this.duplicateTemplate, {data:data.details, configEditUrl :themeEditConfigUrl});
       $('ul.fg-theme-list-wrapper').append(duplicateHtml);
    }
    
    public deleteSuccessCallback(data){
        $('li#Fg_theme_config_li_'+data.configId).remove();
        FgModelbox.hidePopup();
    }
    
    public activateSuccessCallback(data){
        var configLi = $('ul.fg-theme-list-wrapper li.fg_theme_list_li');
        $.each(configLi, function(){
            //selected li
            if($(this).attr('data-configid') === data.configId){
                var statusHtml = FGTemplate.bind(_this.StatusElementTemplate, {data: { configId : $(this).attr('data-configid'), isActive : '1' }});
                var deleteElementHtml = FGTemplate.bind(_this.deleteElementTemplate, {data: { configId : $(this).attr('data-configid'), isActive : '1' }});
            }else{
                var statusHtml = FGTemplate.bind(_this.StatusElementTemplate, {data: { configId : $(this).attr('data-configid'), isActive : '0' }});
                var deleteElementHtml = FGTemplate.bind(_this.deleteElementTemplate, {data: { configId : $(this).attr('data-configid'), isActive : '0' }});
            }
            $(this).find('.fg-activate-wrapper').html(statusHtml);
            $(this).find('.fg_config_last_icon').html(deleteElementHtml);
        });
    }
    
    public handleDelete(){
         _this = this;
        $(document).off('click','.fg_theme_config_delete_wrapper');
        $(document).on('click','.fg_theme_config_delete_wrapper',function(){
            let configId = $(this).find('input.fg_theme_config_delete').val();
            $.post(_this.themeConfigDeletePopupPath, {'configId' : configId}, function (data) {
                FgModelbox.showPopup(data);
            });
        });
        $(document).off('click', '#savePopup');
        $(document).on('click', '#savePopup', function () {
            var configId = $('#theme_config_id_hidden').val();
            FgXmlHttp.post(_this.themeConfigDeletePath, {'configId' : configId}, false, _this.deleteSuccessCallback);
        });
    }
    public handleActivate(){
        $(document).on('click', '.fg_theme_config_activate', function () {
           var configId = $(this).attr('data-configid');
            FgXmlHttp.post(_this.themeConfigActivatePath, {'configId' : configId}, false, _this.activateSuccessCallback);
        });
    }
}

