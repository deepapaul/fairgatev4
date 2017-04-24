$(function(){  
    PrivacySettings.privacySettingsPopulate();
    PrivacySettings.handleSave();
    PrivacySettings.handlePageTitleTabs();
    PrivacySettings.handleTabs();
    PrivacySettings.handleActivetabs();
});  

//Call back function after saving the privacy settings
function initPageFunctions() {
    FgDirtyFields.init('fg-privacySettingsForm', {'discardChangesCallback' : PrivacySettings.discardChangesFunctions, 'setInitialHtml' : false});    
    FgFormTools.handleUniform();
    FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
} 

    
var PrivacySettings = {
    //populate privacy setttings tab
    privacySettingsPopulate: function() {
        var result_template = FGTemplate.bind('fg-privacy-settings-underscore', {fieldDetails: fieldDetails});
        $('.privacyContents').html(result_template); // Appending the listing template using underscore.js
        FgDirtyFields.init('fg-privacySettingsForm', {'discardChangesCallback' : PrivacySettings.discardChangesFunctions, 'setInitialHtml' : false});// Initing fairgate dirty class
        FgFormTools.handleUniform();
    },
    
    //populate language setttings tab
    languageSettingsPopulate: function() {
        var result_template = FGTemplate.bind('fg-language-settings-underscore', {data: is_subscriber});
        $('.languageSettingContents').html(result_template); // Appending the listing template using underscore.js        
    },
    
    //populate newsletter setttings tab
    newsletterSettingsPopulate: function() {
        var result_template = FGTemplate.bind('fg-newsletter-settings-underscore', {data: defaultLanguages, data: contactSystemLang });
        $('.newsletterSubscriptionContents').html(result_template); // Appending the listing template using underscore.js        
    },
    
    //save functionality for 3 tabs(privacy/ language/ newsletter)
    handleSave: function() {
        $('#fg-privacySettingsForm').on('click', '#save_changes', function() {
            PrivacySettings.saveData(initPageFunctions, 'fg-privacySettingsForm');
        });
        $('#fg-languageSettingsForm').on('click', '#applysettings', function() {
            PrivacySettings.saveData(PrivacySettings.saveCallBack, 'fg-languageSettingsForm');
        });
        $('#fg-newsletterSettingsForm').on('click', '#apply_newslettersettings', function() {
            PrivacySettings.saveData(PrivacySettings.newsletterSaveCallBack, 'fg-newsletterSettingsForm');            
        });
        
    },
    
    //save data and call callback function for 3 tabs(privacy/ language/ newsletter)
    saveData: function(callBackFn, formId) {
        var objectGraph = {};
        //parse the all form field value as json array and assign that value to the array
        objectGraph=  FgInternalParseFormField.formFieldParse(formId);
        var privacyArr = JSON.stringify(objectGraph);
        FgXmlHttp.post(pathSavePrivacySettings, { 'postArr': privacyArr} , false, callBackFn);
    },
    
//    Method to show tabs near page title
    handlePageTitleTabs : function() {
        $( ".fg-action-menu-wrapper" ).FgPageTitlebar({
            title       : true,
            tab       : true,
            search     :false,
            actionMenu  : false,
            tabType  :'server'               
        }); 
    },
    
//    Method to handle subtabs
    handleTabs: function() {
        $(document).off('click', '#data-tabs li a[data-toggle=tab]');
        $(document).on('click', '#data-tabs li a[data-toggle=tab]', function (event) {
            var page = $(this).closest('li').attr('data-type');
            $(".tab-pane").hide();
            $("#fg_category_"+page).removeClass("hide");
            $("#fg_category_"+page).show();
            if(page == "system_language") {   
                PrivacySettings.languageSettingsPopulate();
                PrivacySettings.initLanguageFormDirtyField();
                PrivacySettings.discardChanges();                
            } else if(page == "privacy") {         
                // Initing fairgate dirty class     
                PrivacySettings.privacySettingsPopulate();                           
            } else if(page == "newsletter") {
                PrivacySettings.newsletterSettingsPopulate();
                PrivacySettings.initNewsletterFormDirtyField();
                PrivacySettings.newsletterDiscardChanges(); 
            }
        });
    },
    
    handleActivetabs: function() {
        $("#paneltab li").removeClass("active");
        $("#paneltab li[data-target=3]").addClass("active");
    },
    
    //init dirty field for language form
    initLanguageFormDirtyField : function() {
        FgDirtyFields.init('fg-languageSettingsForm', {'setInitialHtml' : false,
                                                    enableDiscardChanges: false,
                                                    discardChangeSelector : "#cancelsettings", 
                                                    saveChangeSelector : "#applysettings",  }); 
    },
    
    //init dirty field for newsletter form
    initNewsletterFormDirtyField : function() {
        FgDirtyFields.init('fg-newsletterSettingsForm', {'setInitialHtml' : false,
                                                    enableDiscardChanges: false,
                                                    discardChangeSelector : "#cancel_newslettersettings", 
                                                    saveChangeSelector : "#apply_newslettersettings",  }); 
    },
    
    //discard change call back for privacy settings page
    discardChangesFunctions: function () {
        $.uniform.update(this);       
    },
        
    
    //save call back for language page
    saveCallBack: function() {
        $('a[href=#fg_category_system_language]').trigger('click');
        FgDirtyFields.removeAllDirtyInstances();
        FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
        //adjust save bar in tab pages
        FgStickySaveBarInternal.init(1);
        
    },
    
    //save call back for newsletter page
    newsletterSaveCallBack: function() {
        $('a[href=#fg_category_newsletter]').trigger('click');
        FgDirtyFields.removeAllDirtyInstances();
        FgInternal.breadcrumb(FgInternal.extraBreadcrumbTitle);
        //adjust save bar in tab pages
        FgStickySaveBarInternal.init(1);
    },        
    
    // discard change rollback method for language settings
    discardChanges: function () {
        $("#languageSettings").select2('destroy');
        formObj = $('#fg-languageSettingsForm');  
        var initialHtml = $('#fg-languageSettingsForm').html(); 
        formObj.off('click', '#cancelsettings');
        formObj.on('click', '#cancelsettings', function () {
            $.fn.dirtyFields.rollbackForm(formObj);
            FgDirtyFields.disableSaveDiscardButtons();
            formObj.html(initialHtml);     
            FgFormTools.handleSelect2();
            PrivacySettings.initLanguageFormDirtyField();     
            //adjust save bar in tab pages
            FgStickySaveBarInternal.init(1);
        });
        FgFormTools.handleSelect2();
    },
    
    // discard change rollback method for newsletter settings
    newsletterDiscardChanges: function () {
        formObj = $('#fg-newsletterSettingsForm');  
        var initialHtml = $('#fg-newsletterSettingsForm').html(); 
        formObj.off('click', '#cancel_newslettersettings');
        formObj.on('click', '#cancel_newslettersettings', function () {
            $.fn.dirtyFields.rollbackForm(formObj);
            FgDirtyFields.disableSaveDiscardButtons();
            formObj.html(initialHtml);
            PrivacySettings.initNewsletterFormDirtyField();    
            //adjust save bar in tab pages
            FgStickySaveBarInternal.init(1);
        });
    }
}