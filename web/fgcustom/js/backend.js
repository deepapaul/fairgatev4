/* * GLOBAL JAVASCRIPT FILE FOR FAIRGATE PORTAL SOLUTION 3.0 BACKEND APPLICATION * */
$(function () {
    FgApp.init();
    FgXmlHttp.init();
    FgUtility.toolTipInit();
    FgAjaxError.init();
    FgApp.clearAllStorage();
    FgApp.handleSwipeLeftRight(); //Handle swipe functionality between pages using breadcrumb next prev 
});
var confirm = false;
/*
 * Global application specific functions
 *
 */
FgApp = {
    extraBreadcrumbTitle: [],
    dateFormat: {todayHighlight: true, autoclose: true, language: datatabletranslations.localeName, format: FgLocaleSettingsData.jqueryDateFormat, weekStart: 1, clearBtn: true},
    dateTimeFormat: {todayHighlight: true, autoclose: true, language: datatabletranslations.localeName, format: FgLocaleSettingsData.jqueryDateTimeFormat, weekStart: 1, clearBtn: true},
    init: function () {
        //FgModelBox.init();
        $.fn.select2.defaults = $.extend($.fn.select2.defaults, {
            minimumResultsForSearch: -1 // Removes search when there are 15 or fewer options
        });
        
        $.extend( true, $.fn.dataTable.defaults, {
            oLanguage: {
                sThousands: FgLocaleSettingsData.thousendSeperator,
            },
            language: {
                thousands: FgLocaleSettingsData.thousendSeperator,
            }
        });
        
        FgDirtyForm.init();
        FgDirtyForm.disableButtons();
        FgInputTextValidation.init();
        Breadcrumb.load(extraBreadcrumbTitle);
        FgFormTools.handleInputmask();
        FgFormTools.handleDatepicker();
        FgFormTools.handleBootstrapSelect();
        // Function to set the minimum value to show the search box in select2 dropdown
        FgFormTools.handleSelect2();
        //Common functions to be loaded for all pages

        FgTopNavigation.init();
        //For autocomplete search in top navigation menu (contact, club, document, sponsors)
        FgTopNavigation.search(dataTopNavigationSearch);
        FgActionDropDownClick.init();
    },
    setStorage: function ($storage) {
        if ($storage.type) {
            switch ($storage.type) {
                case 'LOCAL':
                    break;
                case 'SESSION':
                    break;
                case 'COOKIE':
                    break;
            }
        }
    },
    getStorage: function ($storage) {
        if ($storage.type) {
            switch ($storage.type) {
                case 'LOCAL':
                    break;
                case 'SESSION':
                    break;
                case 'COOKIE':
                    break;
            }
        }
    },
    clearAllStorage: function () {
       var FgCurrentSprint = Sprint.currentSprint;
       var currentSprint = localStorage.getItem('fgcurrentSprint');
       //first time entry
        if (typeof currentSprint === 'undefined' || currentSprint === null || currentSprint == '') {
            localStorage.clear();
            localStorage.setItem('fgcurrentSprint', FgCurrentSprint);
        } else if (FgCurrentSprint != currentSprint) {
            localStorage.clear();
            localStorage.setItem('fgcurrentSprint', FgCurrentSprint);
        }

    },
  
/*
 * Handle Contact swipe function for 
 * handling swipe functionality in contact overview tabs
 * loading next/previous contacts when do swipe in touchscreen
 */  
    handleSwipeLeftRight :function(){
        var swipe,swipeLeft,swipeRight;
        swipe = swipeLeft = swipeRight = false;
        var isTouchDevice = 'ontouchstart' in document.documentElement;
        $('body').addClass('touch-enabled');
        if(isTouchDevice)
            $('body').addClass('touch-enabled');
        else{ //exit if it is not a touchscreen device
            $('body').removeClass('touch-enabled');
            return;
        }
        
        $('body.touch-enabled').on('touchstart touchend touchup','table', function(event) {
            event.stopPropagation();
        });
//       Following conditions for preventing unwaned swipe trigger in similar layout
        if($('.fg-swipe-next-right a.fg-next').length>0){
            swipeLeft = swipe = true;
        } 
        if ($('.fg-swipe-next-right a.fg-prev').length>0){
            swipeRight = swipe = true; 
        }
        if (swipe) {
            $('body').hammer().on("swipeleft swiperight", function (event) {
                
                var redirectURL = '';
                if (event.type === 'swipeleft' && swipeLeft) {
                    FgUtility.startPageLoading();
                    setTimeout(function () {
                        location.href = $('a.fg-next').attr('href');
                    }, 100);
                }
                if (event.type === 'swiperight' && swipeRight) {
                    FgUtility.startPageLoading();
                    setTimeout(function () {
                        location.href = $('a.fg-prev').attr('href');
                    }, 100);
                }

            });
        }
    }
};

/*
 * Modelbox wrapper class
 * Dependency: FgApp[init]
 * This function is used for to create a colorbox pop up. This is not a property of metronic theme. Metronic theme has its on pop up box property
 */
FgModelBox = {
    init: function () {
        $('div[openBox=modelbox]').colorbox({
            scrolling: false,
            close: 'Close',
            onOpen: function () {

            },
            onComplete: function () {
                FgModelBox.init();
                FgModelBox.resize();

            }
        });
        //Display depreicated warning
        $('div[openBox=colorbox]').colorbox({
            scrolling: false,
            onComplete: function () {
                //resize the content
                FgModelBox.init();
                FgModelBox.resize();
            }
        });
        //TODO DEVENV
        if ($('div[openBox=colorbox]').length) {
            //enter the actions here
        }

    },
    open: function (url) {
    },
    close: function () {
        $.fn.colorbox.close()
    },
    resize: function () {
        //FgModelBox.init();
        //FgModelBox.resize();
        $.fn.colorbox.resize();
    },
    resizeWithHeight: function () {
    },
    openWithHtml: function (content) {

        $.colorbox({html: content, rel: 'nofollow', overlayClose: false, preloading: false, loop: false, fixed: false});
        FgModelBox.init();
        FgModelBox.resize();
        // $('div[openBox=colorbox]').colorbox({html:content,rel:'nofollow',overlayClose:false,preloading:false,loop:false,fixed:false});
    },
    updatecontent: function (innerhtmlcontent) {
        $('#cboxLoadedContent').html(innerhtmlcontent);
    }

};
/**
 * This function is for create a dirty form in a form
 *
 */
FgDirtyForm = {
    init: function () {
        $('form:not(form[skipDirtyCheck])').areYouSure({
            message: datatabletranslations.dirtyformAlert,
            addRemoveFieldsMarksDirty: true,
            change: function () {
                // Enable save button only if the form is dirty. i.e. something to save.
                if ($(this).hasClass('dirty')) {
                    $(this).find('input[type="submit"]').removeAttr('disabled');
                    $(this).find('input[type="reset"]').removeAttr('disabled');
                } else {
                    $(this).find('input[type="submit"]').attr('disabled', 'disabled');
                    $(this).find('input[type="reset"]').attr('disabled', 'disabled');
                }
            }
        });
    },
    rescan: function (formId) {
        //After create a dynamic field we must call this function for getting the dirty form in all fields
        $('#' + formId).trigger('rescan.areYouSure');

    },
    checkForm: function (formId) {
        // For making fields dirty if values of fields are set by jquery.
        $('#' + formId).trigger('checkform.areYouSure');
    },
    disableButtons: function () {
        // For disable the save button
        $('form:not(form[skipDirtyCheck])').find('input[type="submit"]').attr('disabled', 'disabled');
        $('form:not(form[skipDirtyCheck])').find('input[type="reset"]').attr('disabled', 'disabled');
    }


};

/**
 * For create the clone of a form part with new index( Add more functionality)
 *
 */
FgAddmoreForm = {
    init: function (buttonId, Index, wrapperId) {
        var form_index = Index;

        $("#" + buttonId).click(function () {

            form_index++;
            $(this).parent().before($("#" + wrapperId).clone().attr("id", wrapperId + "_" + form_index));

            $("#" + wrapperId + "_" + form_index).css("display", "block");


            $("#" + wrapperId + "_" + form_index + " :input").each(function () {

                $(this).attr("name", $(this).attr("name") + form_index);
                $(this).attr("id", $(this).attr("id") + form_index);

            });

            $("#removediv" + form_index).click(function () {

                $(this).closest("div").remove();
            });

        });





    }

}

/**
 *  Drag 'n' Drop with in a div
 *
 */

var FgDragAndDrop = {
    init: function (sortDiv) {
        handleDragAndDrop(sortDiv, false, false, false, false);
    },
    initWithChild: function (sortDiv) {
        handleDragAndDrop(sortDiv, true, false, false, false);
    },
    orderSort: function (sortDiv, inputId) {
        handleDragAndDrop(sortDiv, false, true, inputId, false);
    },
    orderSortWithChild: function (sortDiv, inputId) {
        handleDragAndDrop(sortDiv, true, true, inputId, false);
    },
    sortWithOrderUpdation: function (sortDiv, doChildSort) {
        handleDragAndDrop(sortDiv, doChildSort, false, false, true);
    },
    categorySort: function (sortDiv, doAction) {
        multiDragAndDrop(sortDiv, doAction);
    }
};


var multiDragAndDrop = function (sortDiv, doAction) {
    $(sortDiv).sortable({
        droppable: true,
        connectWith: '.sortable',
        items: '.sortable',
        opacity: 0.8,
        forcePlaceholderSize: true,
        tolerance: "fit",
        handle: '.catHandle',
        start: function (event, ui) {
            ui.item.startPos = ui.item.index();
        },
        stop: function (event, ui) {
            ui.item.stopPos = ui.item.index();
            if (doAction) {
                stopSortAction(this);
            }
        }
    });
}
var handleDragAndDrop = function (sortDiv, doChildSort, sortCheck, inputId, updateSortOrder) {
    $(sortDiv).each(function () {
        $(this).sortable({
            droppable: true,
            connectWith: $(this).children('.sortables'),
            items: $(this).children('.sortables'),
            opacity: 0.8,
            forcePlaceholderSize: true,
            tolerance: "fit",
            handle: '.handle',
            placeholder: 'placeholder',
            start: function (event, ui) {
                ui.item.addClass("fg-drag-line-border");
                ui.item.startPos = ui.item.index();
            },
            stop: function (event, ui) {
                ui.item.removeClass("fg-drag-line-border");
                ui.item.stopPos = ui.item.index();
                if (doChildSort) { //if any child div have to be sorted along with parent div
                    doAfterSort(ui.item, this);
                }
                if (sortCheck) {
                    var changeval = '';
                    $(sortDiv).children().each(function (index, value) {
                        changeval = changeval + this.id + ',';
                    })
                    if (changeval != '') {
                        changeval = changeval.substring(0, changeval.length - 1);
                    }
                    $('#' + inputId).val(changeval);
                    $('form').trigger('checkform.areYouSure');
                }
                if (updateSortOrder) {
                    doSortOrderUpdation(this);
                }
            }
        });
    });
}

/**
 *
 *  To do something after sorting
 */
function doAfterSort(item, parentElement) {
    var childDivId = 'child_';
    var dragPosition = item.startPos;
    var dropPosition = item.stopPos;
    if (dragPosition != dropPosition) {
        var parentDivId = $(parentElement).attr('id');
        var parentDiv = $('#' + childDivId + parentDivId);
        var dragDiv = parentDiv.children()[dragPosition];
        var dropDiv = parentDiv.children()[dropPosition];
        if (dragPosition < dropPosition) {
            $(dragDiv).insertAfter($(dropDiv));
        } else {
            $(dragDiv).insertBefore($(dropDiv));
        }
    }
}

/**
 *
 * To update sort order of elements after sorting
 */
function doSortOrderUpdation(parentElement) {
    FgUtility.resetSortOrder(parentElement);
}

/**
 *
 *Array convertion to JSON
 */
function converttojson(objectGraph, name, value) {
    if (name.length == 1) {
        //if the array is now one element long, we're done
        objectGraph[name[0]] = value;
    } else {
        //else we've still got more than a single element of depth
        if (objectGraph[name[0]] == null) {
            //create the node if it doesn't yet exist
            objectGraph[name[0]] = {};
        }
        //recurse, chopping off the first array element
        converttojson(objectGraph[name[0]], name.slice(1), value);
    }
}
;

/**
 *
 * Load an html content in a placeholder div on clicking an element (Using underscore.js)
 */
var FgLoadHtmlContent = {
    init: function (selector, placeholder) {
        loadHtmlContent(selector, placeholder);
    }
}

var loadHtmlContent = function (selector, placeholder) {
    $(selector).click(function () {
        var elementId = $(this).attr('id');
        var replaceDivId = 'child_' + elementId;
        var replaceDiv = $('#' + replaceDivId);
        var actionName = $(this).attr('data-url');
        var templateName = $(this).attr('data-template-script-id');
        var replaceHtml = $(this).attr('data-template');

        if (actionName != '') {
            if ($(replaceDiv).css('display') == 'none') { //Show data
                $(this).parent().parent().find(placeholder).hide();

                if ($(replaceDiv).html() == '') { //Load data only if not loaded currently
                    $(replaceDiv).load(replaceHtml);

                    $.getJSON(actionName, function (data) {
                        var template = $('#' + templateName).html();
                        var result_data = _.template(template, {content: data});
                        $(replaceDiv).html(result_data);
                    });
                }
                $(replaceDiv).show();

            } else { //Hide data on second click
                $(replaceDiv).hide();
            }
        }
        return false;
    });
}
FgMultipleMergePopup = {
    handleMergerablePopup : function (response) {
        var htmlFinal = _.template($('#merge-multiple-popup-template').html(),{'mergableContacts': response.mergableContacts});
        $('#popup_contents').html(htmlFinal);
        FgMultipleMergePopup.disableDuplicateMerging();
        FgFormTools.handleUniform();
        pageType = (response.pageTpe) ? response.pageTpe : '';
        response.alreadyActivatedCnt = (response.alreadyActivatedCnt)?response.alreadyActivatedCnt:0;
        response.totalCnt = (response.totalCnt)?response.totalCnt:0;
        $('#popup').addClass('fg-membership-merge-modal');
        FgMultipleMergePopup.mergePopupSubmitHandling(response);
        $('#popup').modal('show');
      
    },
    mergePopupSubmitHandling:function(responses){
        
        $('#cancel_merging').one('click', function() {
            FgUtility.stopPageLoading();
            if (contactType == "draft") {
                $('#popup').removeClass('fg-membership-merge-modal');
                $('#popup').modal('hide');
                FgUtility.showToastr(failureFlash, 'warning');
            }else if(contactType == "external"){ 
                $('#popup').removeClass('fg-membership-merge-modal');
                $('#popup').modal('hide');
                FgMultipleMergePopup.cancelMerging(responses);
            }else {
                FgMultipleMergePopup.cancelMerging(responses);
            }
        });
         $('#save_merging').one('click', function() {
           FgUtility.startPageLoading();
            var mergerValue = FgParseFormField.fieldParse();
            var creationArray = (responses.page == 'creations') ? {'contactDetails':responses.contactDetails,'nonMergeableContacts':responses.nonMergeableContacts,'selectedMembership':responses.selectedMembership,'selectedIds':responses.selectedIds,'totCount':responses.totCount} : '';
            extraData={'merging':'save','mergeTo':mergerValue,'mergeType':'multiple','contactData':responses.mergableContacts,'creationArray':creationArray,'totalCnt':responses.totalCnt,'alreadyActivated':responses.alreadyActivatedCnt,'selectedMembership':responses.selectedMembership,'totCount':responses.totCount};
            if (contactType == "draft") {
                $('#popup').modal('hide');
                FgXmlHttp.post(reactivateSavePath, extraData, '', function (response) {
                    confirmationCallback.confirmOrDiscardCallback(response.totalCount, 'creations');
                });
            }
             else {
                 FgXmlHttp.post(reactivateSavePath, extraData, '',function (response) {
                    
                    $('#popup').modal('hide');
                    $('#popup').removeClass('fg-membership-merge-modal');
                    if (contactType == "archivedsponsor") {
                        sponsorTable.api().draw();
                    } else {
                        if(contactType == "external"){ 
                           $('#popup').modal('hide');
                           $('#popup').removeClass('fg-membership-merge-modal');
                           document.location = document.location.href;
                          }else{
                            oTable.api().draw();
                        }
                        
                    }
                    if (response.status == 'FAILURE') {
                        FgUtility.stopPageLoading();
                        FgUtility.showToastr(response.flash, 'warning');
                    } else {
                        FgUtility.stopPageLoading();
                        FgCountUpdate.updateTopNav('add', 'contact', 'active',  parseInt(response.totalCount));
                        if (contactType == "archivedsponsor") {
                            FgCountUpdate.updateTopNav('remove', 'sponsor', 'archived', parseInt(response.totalCount));
                            FgCountUpdate.updateTopNav('add', 'sponsor', 'active', parseInt(response.totalCount));
                        } else {
                            FgCountUpdate.updateTopNav('remove', 'contact', 'archive', parseInt(response.totalCount)+responses.alreadyActivatedCnt);
                        }
                    }
                });
            }
        });
    },
    cancelMerging:function(responses){
        $('#popup').removeClass('fg-membership-merge-modal');
        var mergerValue = FgParseFormField.fieldParse();
        var creationArray = (responses.page == 'creations') ? {'contactDetails':responses.contactDetails,'nonMergeableContacts':responses.nonMergeableContacts,'selectedMembership':responses.selectedMembership,'selectedIds':responses.selectedIds,'totCount':responses.totCount} : '';
        extraData={'merging':'cancel','mergeTo':mergerValue,'mergeType':'multiple','contactData':responses.mergableContacts,'creationArray':creationArray,'totalCnt':responses.totalCount,'alreadyActivated':responses.alreadyActivatedCnt};
      
      if (contactType == "external") {
          //totalCnt
           responses.totalCount = (responses.totalCount)?responses.totalCount:responses.totalCnt;
          extraData={'merging':'cancel','mergeTo':mergerValue,'mergeType':'multiple','contactData':responses.mergableContacts,'creationArray':creationArray,'totalCnt':responses.totalCount,'alreadyActivated':responses.alreadyActivatedCnt};
          document.location = document.location.href;
      }
        if (contactType == "draft") {
            FgXmlHttp.post(reactivateSavePath, extraData, '', function (response) {console.log(response);
                confirmationCallback.confirmOrDiscardCallback(response.totalCount, 'creations');
            });
        }
        else {
                 FgXmlHttp.post(reactivateSavePath, extraData, '',function (response) {
                    $('#popup').modal('hide');
                    $('#popup').removeClass('fg-membership-merge-modal');
                    if (contactType == "archivedsponsor") {
                        sponsorTable.api().draw();
                    } 
                    else {
                        if(contactType == "external"){
                          document.location = document.location.href;
                        }else
                        oTable.api().draw();
                    }
                    if (response.status == 'FAILURE') {
                        FgUtility.stopPageLoading();
                        FgUtility.showToastr(response.flash, 'warning');
                    } else {
                        FgUtility.stopPageLoading();
                        FgCountUpdate.updateTopNav('add', 'contact', 'active',  parseInt(response.totalCount));
                        if (contactType == "archivedsponsor") {
                            FgCountUpdate.updateTopNav('remove', 'sponsor', 'archived',  parseInt(response.totalCount));
                            FgCountUpdate.updateTopNav('add', 'sponsor', 'active',  parseInt(response.totalCount));
                        } else {
                            FgCountUpdate.updateTopNav('remove', 'contact', 'archive',  parseInt(response.totalCount)+responses.alreadyActivatedCnt);
                        }
                    }
                });
            }
    },
    disableDuplicateMerging:function(){
        $('div[data-merge-wrapper] input[type=radio]:checked').each(function(){
            if($(this).val() !== 'fed_mem'){
                $('div[data-merge-wrapper] input[type=radio][value='+$(this).val()+']:not(:checked)').prop("disabled", true);
            }
        });
    }
};
FgMergePopup = {
    handleMergerablePopup : function (response) {
        fedMem={};
        
        var duplicates = (response['mergeEmail'].length>0) ? response.mergeEmail:response.duplicates;
        var typeMer= (response['mergeEmail'].length>0) ? 'email':'fields';
        var countMergeable = (response['mergeEmail'].length>0) ? 1:duplicates.length;
        var currentContactData = response['currentContactData'];
        var creationArray = (response['page'] == 'creations') ? {'contactDetails':response['contactDetails'],'selectedMembership':response['selectedMembership'],'selectedIds':response['selectedIds']} : '';
        if (contactType == "external") {

           var creationArray = {'contactDetails':response['contactDetails'],'selectedMembership':response['selectedMembership'],'selectedIds':response['selectedIds'],'alreadyActivatedCnt':response['alreadyActivatedCnt'],'totalCnt':response['totalCnt']} ;
         }
        pageType = (response.pageTpe) ? response.pageTpe : '';
        yours={'firstname':currentContactData['2']};
        yours['lastname']=currentContactData['23'];
        yours['gender']=currentContactData['Gender'];
        yours['dob']=currentContactData['4'];
        yours['location']=currentContactData['77'];
        yours['email']=currentContactData['3'];
        yours['isCompany']=currentContactData['Iscompany'];
        yours['contactName']=currentContactData['contactName'];

        fedMem[response['currentContactData']['fedMembershipId']]=response['currentContactData']['fedMembershipTitle'];

        var htmlFinal = _.template($('#merge-popup-template').html(),{'duplicates': duplicates,'fedMem':fedMem,'typeMer':typeMer,'countMergeable':countMergeable,'yours':yours});

        $('#popup_contents').html(htmlFinal);
        $('#popup').addClass('fg-membership-merge-modal');
        FgFormTools.handleUniform();
        $('#popup').modal('show');
        FgMergePopup.mergePopupHandling(typeMer, currentContactData, creationArray);
    },
    mergePopupHandling:function(typeMer, currentContactData, creationArray){
        $('#cancel_merging').one('click', function() {
            if (contactType == "draft") {
                $('#popup').removeClass('fg-membership-merge-modal');
                $('#popup').modal('hide');
                FgUtility.showToastr(failureFlash, 'warning');
            } else if(contactType == "external"){
                 $('#popup').removeClass('fg-membership-merge-modal');
                $('#popup').modal('hide');
                FgUtility.startPageLoading();
               FgMergePopup.cancelMerging(typeMer, currentContactData, creationArray);
            }
            else {
                FgUtility.startPageLoading();
                FgMergePopup.cancelMerging(typeMer, currentContactData, creationArray);
            }
        });
         $('#save_merging').one('click', function() {
             FgUtility.startPageLoading();
            var mergerValue=$('.merge-value-radio:checked').val();
            extraData={'merging':'save','mergeTo':mergerValue,'typeMer':typeMer, 'contactData' : currentContactData, 'creationArray' : creationArray};
             if (contactType == "external") {
                 
                    extraData={'merging':'save','mergeTo':mergerValue,'typeMer':typeMer, 'contactData' : currentContactData, 'creationArray' : creationArray,'selectedMembership':creationArray['selectedMembership'],'contactDetails':creationArray['contactDetails'],'selectedIds':creationArray['selectedIds'],'totCount':creationArray['totCount']};
             }
            if (contactType == "draft") {
                $('#popup').modal('hide');
                FgXmlHttp.post(reactivateSavePath, extraData, '', function (response) {
                    confirmationCallback.confirmOrDiscardCallback(response.totalCount, 'creations');
                });
            } else {
                $.get(reactivateSavePath, extraData, function (response) {
                    $('#popup').modal('hide');
                    $('#popup').removeClass('fg-membership-merge-modal');
                    if(pageType == 'overview') {
                        $('#fg-dev-reactivate').hide();
                        FgUtility.showToastr(response.flash, 'success');   
                        document.location = document.location.href;
                    } else {
                        if (contactType == "archivedsponsor") {
                            sponsorTable.api().draw();
                        } else {
                            
                            if(contactType == "external"){
                                    $('#popup').modal('hide');
                                    $('#popup').removeClass('fg-membership-merge-modal');
                                    //console.log(648);
                                   // FgUtility.showToastr(response.flash, 'success');   
                                   document.location = document.location.href;
                            }else{
                                oTable.api().draw();
                            }
                            
                        }
                        if (response.status == 'FAILURE') {
                            FgUtility.stopPageLoading();
                            FgUtility.showToastr(response.flash, 'warning');
                        } else {
                            FgUtility.stopPageLoading();
                            FgUtility.showToastr(response.flash, 'success');
                            FgCountUpdate.updateTopNav('add', 'contact', 'active', response.totalCount);
                            if (contactType == "archivedsponsor") {
                                FgCountUpdate.updateTopNav('remove', 'sponsor', 'archived', response.totalCount);
                                FgCountUpdate.updateTopNav('add', 'sponsor', 'active', response.totalCount);
                            } else {
                                FgCountUpdate.updateTopNav('remove', 'contact', 'archive', response.totalCount);
                            }
                        }
                    }
                });
            }
        });
    },
    cancelMerging:function(typeMer, currentContactData, creationArray){
       
        var mergerValue=$('.merge-value-radio:checked').val();
        extraData={'merging':'cancel','mergeTo':mergerValue,'typeMer':typeMer, 'contactData' : currentContactData, 'creationArray' : creationArray};
        if (contactType == "draft") {
            $('#popup').modal('hide');
            FgXmlHttp.post(reactivateSavePath, extraData, '', function (response) {
                confirmationCallback.confirmOrDiscardCallback(response.totalCount, 'creations');
            });
        } 
        
        else {
           
            $.get(reactivateSavePath, extraData, function (response) {
                $('#popup').modal('hide');
                $('#popup').removeClass('fg-membership-merge-modal');
                if(pageType == 'overview') {
                    $('#fg-dev-reactivate').hide();
                    FgUtility.stopPageLoading();
                   // FgUtility.showToastr(response.flash, 'success');   
                } else {
                    if (contactType == "archivedsponsor") {
                        sponsorTable.api().draw();
                    } else {
                       if(contactType == "external"){
                           $('#popup').modal('hide');
                            $('#popup').removeClass('fg-membership-merge-modal');
                         FgUtility.stopPageLoading();
                          document.location = document.location.href;
                        }
                        else
                        oTable.api().draw();
                    }
                    if (response.status == 'FAILURE') {
                        FgUtility.stopPageLoading();
                        FgUtility.showToastr(response.flash, 'warning');
                    } else {
                        FgUtility.stopPageLoading();
                        FgUtility.showToastr(response.flash, 'success');
                        FgCountUpdate.updateTopNav('add', 'contact', 'active', response.totalCount);
                        if (contactType == "archivedsponsor") {
                            FgCountUpdate.updateTopNav('remove', 'sponsor', 'archived', response.totalCount);
                            FgCountUpdate.updateTopNav('add', 'sponsor', 'active', response.totalCount);
                        } else {
                            FgCountUpdate.updateTopNav('remove', 'contact', 'archive', response.totalCount);
                        }
                    } 
                }
            }); 
        }
    }
}

confirmationCallback = {
    confirmOrDiscardCallback:function(updatedCount, page) {
        FgMoreMenu.initServerSide('paneltab');
        var actionMenuText = {'active' : {'none': actionMenuNoneSelectedText, 'single': actionMenuSingleSelectedText, 'multiple': actionMenuMultipleSelectedText}};
        FgSidebar.dynamicMenus.push({actionmenu: actionMenuText});
        Breadcrumb.load([]);
        if(page == 'creationsappform'){
            var navigationBadgeId = '#fg-dev-confirm'+page+'-count';
            var navBadgeCount = $(navigationBadgeId).html();
            navBadgeCount = ((navBadgeCount - updatedCount) < 0) ? 0 : (navBadgeCount - updatedCount);
            $(navigationBadgeId).html(navBadgeCount);
        }else{
            var tabCount =  $('#fg-'+page+'-count').html();
            tabCount = ((tabCount - updatedCount) < 0) ? 0 : (tabCount - updatedCount);
            $('#fg-'+page+'-mutations-count').html(tabCount);
            var navigationBadgeId = '#fg-dev-confirm'+page+'-count';
            var navBadgeCount = $(navigationBadgeId).html();
            navBadgeCount = ((navBadgeCount - updatedCount) < 0) ? 0 : (navBadgeCount - updatedCount);
            $(navigationBadgeId).html(navBadgeCount);
        }
        var totalConfirmationCount = (parseInt($('#fg-dev-confirmchanges-count').html()) + parseInt($('#fg-dev-confirmmutations-count').html()) + parseInt($('#fg-dev-confirmcreations-count').html())+ parseInt($('#fg-dev-confirmcreationsappform-count').html()));
        if(totalConfirmationCount == 0){
           $('.fg-confirmations-warning').hide();
        }
        FgPageTitlebar.setMoreTab();
        FgFormTools.handleUniform();
    }
}

/*
 * XmlHttp wrapper class
 * Dependency: FgApp[init]
 *
 */
FgXmlHttp = {
    //wrapper function $.post()
    post: function (url, data, replacediv, successCallback, failCallback, isReplaceContent) {
        if (!isReplaceContent)
            isReplaceContent = 1;
        var rand = Math.random();
        FgUtility.startPageLoading();
        $.post(url + "?rand=" + rand, data, function (result) {
            if (result.status) {
                if (result.redirect) {
                    if (result.sync) {
                        FgUtility.stopPageLoading();
                        document.location = result.redirect;
                        if (result.flash)
                            FgUtility.showToastr(result.flash);
                    } else {
                        FgXmlHttp.replaceContentFromUrl(result.redirect, result.flash, successCallback, result);
                    }
                } else {
                    if (result.noparentload) {
                        FgUtility.stopPageLoading();
                        if (result.flash) {
                            FgUtility.showToastr(result.flash);
                        }
                        if (successCallback && !result.errorArray) {
                            successCallback.call({}, result);
                        }
                        if (failCallback) {
                            failCallback.call({}, result);
                        }
                    } else {
                        FgXmlHttp.replaceContentFromUrl(document.location.href, result.flash, successCallback, result);
                    }
                }

            } else {
                if (isReplaceContent == 1) {
                    if (replacediv)
                        $(replacediv).html(result);
                    else {
                        $('#fg-wrapper').html(result);
                    }
                }
                if (successCallback && !result.errorArray) {
                    successCallback.call({}, result);
                }
                if (failCallback) {
                    failCallback.call({}, result);
                }
//                scroll to top common form error alert on failing validation
                FgXmlHttp.scrollToErrorDiv();
                FgUtility.stopPageLoading();
            }
        });
        // return false;
    },
    //wrapper function $.post() with file upload
    iframepost: function (url, form, extradata, replacediv, sucessCallback, failCallback) {
        FgUtility.startPageLoading();
        if (extradata)
            extradata.layout = false;
        else
            extradata = {
                layout: false
            };
        var options = {
            success: function (responseText) {
                FgUtility.stopPageLoading();
                flag = false;
                try {
                    if (typeof (responseText) == 'object') {
                        obj = responseText;
                        flag = true;
                    } else {
                        obj = JSON.decode(responseText);
                        flag = true;
                    }

                }
                catch (e) {
                    //add exception here
                }
                if (flag) {
                    if (obj.redirect) {
                        if (obj.sync) {
                            FgUtility.stopPageLoading();
                            document.location = obj.redirect;
                            if (obj.flash)
                                FgUtility.showToastr(obj.flash);
                        } else {
                            FgXmlHttp.replaceContentFromUrl(obj.redirect, obj.flash, sucessCallback, false, obj);
                        }

                    }  else if (obj.noparentload) {
                        FgUtility.stopPageLoading();
                        if (obj.flash) {
                            FgUtility.showToastr(obj.flash);
                        }
                        if (sucessCallback) {
                            sucessCallback.call({}, responseText);
                        }
                    } else {
                        FgXmlHttp.replaceContentFromUrl(document.location.href, obj.flash, sucessCallback, false, obj);
                    }
                } else {
                    if (replacediv)
                        $(replacediv).html(responseText);
                    else
                        $('#fg-wrapper').html(responseText);
                    if (sucessCallback)
                        sucessCallback.call({}, responseText);
                    if (failCallback)
                        failCallback.call({}, responseText);
                    if (form.attr('data-scrollToFirstError')) {
//                      scroll to first form error on failing validation (currently implemented only for create/edit contact by passing form attribute)
                        FgXmlHttp.scrollToErrorDiv('.has-error:eq(0):visible');
                    } else {
//                      scroll to top common form error alert on failing validation
                        FgXmlHttp.scrollToErrorDiv();
                    }
                }
            },
            url: url,
            data: extradata,
            type: 'post'
        };
        form.ajaxSubmit(options);
        return false;
    },
    //post form with files
    formPost: function (paramObj) {
        if (paramObj.form && paramObj.url) {
            FgUtility.startPageLoading();
            if (paramObj.extradata) {
                paramObj.extradata.layout = false;
            } else {
                paramObj.extradata = {'layout': false};
            }
            paramObj.form.ajaxSubmit({
                url: paramObj.url,
                data: paramObj.extradata,
                type: 'post',
                success: function (responseText) {
                    FgUtility.stopPageLoading();
                    flag = false;
                    try {
                        if (typeof (responseText) == 'object') {
                            obj = responseText;
                            flag = true;
                        } else {
                            obj = JSON.decode(responseText);
                            flag = true;
                        }

                    }
                    catch (e) {
                        //add exception here
                    }
                    if (flag == true) {
                        if (obj.redirect) {
                            if (obj.sync) {
                                document.location = obj.redirect;
                                if (obj.flash)
                                    FgUtility.showToastr(obj.flash);
                            } else {
                                FgXmlHttp.replaceContentFromUrl(obj.redirect, obj.flash, paramObj.sucessCallback, false, obj);
                            }

                        }
                        else if (obj.noreload) {
                            if (obj.flash)
                                FgUtility.showToastr(obj.flash);
                        }
                        else if (obj.status !== 'ERROR') {
                            FgXmlHttp.replaceContentFromUrl(document.location.href, obj.flash, paramObj.sucessCallback, false, obj);
                        }
                    } else {
                        if (paramObj.replacediv) {
                            $(paramObj.replacediv).html(responseText);
                        } else {
                            $('#fg-wrapper').html(responseText);
                        }
//                        scroll to top common form error alert on failing validation
                        FgXmlHttp.scrollToErrorDiv();
                    }
                    if (paramObj.successCallback) {
                        if (paramObj.successParam) {
                            paramObj.successParam.responseText = responseText;
                        }
                        else {
                            paramObj.successParam = {'responseText': responseText};
                        }
                        paramObj.successCallback.call(this, paramObj.successParam);
                    }
                    if (paramObj.failCallback) {
                        paramObj.failCallback.call({}, paramObj.responseText);
                    }
                    FgUtility.stopPageLoading();
                },
                error: function (data) {
                    if (paramObj.failCallback) {
                        paramObj.failCallback.call({}, responseText);
                    }
                }
            });
        }
    },
    replaceContentFromUrl: function (url, flashmsg, callback, callbackdata) {
        $.ajax({
            url: url,
            data: {
                silent: 1
            }, /* FiX - to avoid reloading flash message from url*/
            success: function (data) {
                FgUtility.stopPageLoading();
                $('#fg-wrapper').html(data);
                //FgApp.init();
                if (flashmsg)
                    FgUtility.showToastr(flashmsg);
                if (callback)
                    callback.call({}, callbackdata);
            }
        });
    },
    init: function () {
        $.ajaxSetup({cache: false});
    },
    //function added to focus to error element if exists or to common form error on failing validation
    scrollToErrorDiv: function (element) {
        var focusPos = 100;
        if (typeof element !== 'undefined') {
            if ($(element).length > 0) {
                focusPos = $(element).offset().top;
            }
        } else {
            if ($('.alert-danger').length > 0) {
                focusPos = $('.alert-danger').offset().top;
            }
        }
//      padding & margin is to be subtracted for better view 
        focusPos = ((focusPos - 60) > 0) ? (focusPos - 60) : focusPos;
        $('html, body').animate({
            scrollTop: focusPos}, 'fast'
                );
    }
};
/*
 * Small utilities wrapper
 *
 */
FgUtility = {
    showToastr: function (msg, type, title) {
        var toastrType = 'success';
        var toastrTitle = '';
        if (type)
            toastrType = type;
        if (title)
            toastrTitle = title;
        toastr.options = {
            positionClass: 'toast-top-center'
        };
        toastr[toastrType](msg, title);
        FgStickySaveBar.init(0);
    },
    dateFilter: function(rowDate, startdate, enddate){
        if (startdate != '')
            var startdateTimestamp = moment(startdate, FgLocaleSettingsData.momentDateFormat).format('x')
        else
            var startdateTimestamp = 0;

        if (enddate != '')
            var enddateTimestamp = moment(enddate, FgLocaleSettingsData.momentDateFormat).format('x')
        else
            var enddateTimestamp = 0;

        var currentRowTimestamp = moment(rowDate, FgLocaleSettingsData.momentDateFormat).format('x');
        var show = false;
        if (startdateTimestamp > 0 && enddateTimestamp > 0) {
            if (currentRowTimestamp >= startdateTimestamp && currentRowTimestamp <= enddateTimestamp)
                show = true;
        }
        else if (startdateTimestamp > 0) {
            if (currentRowTimestamp >= startdateTimestamp)
                show = true;
        }
        else if (enddateTimestamp > 0) {
            if (currentRowTimestamp <= enddateTimestamp)
                show = true;
        }
        if (show){
            return true;
        } else {
            return false;
        }
    },
    // custom tooltip popup
    toolTipInit: function () {

        $('body').on('mouseover click', '.fg-custom-popovers', function (e) {
            var _this = $(this);
            if(_this.attr('data-popover-content')) {
                thisContent = _this.find('.popover-content').html();                 
            } else {
                thisContent =  _this.data('content');
            }
            posLeft = function () {
                var left = ($('.fg-round-img').hasClass('fg-img-popover')) ? _this.offset().left - 18 : _this.offset().left;
                return left;
            };
            posTop = _this.offset().top;

            FgUtility.showTooltip({element: e, content: thisContent, position: [posLeft, posTop]});
        });
        $('body').on('mouseout', '.fg-custom-popovers', function () {
            $('body').find('.custom-popup').hide();
            $('.popover .popover-content').width('');
        });
    },
    showTooltip: function (obj) {
        var targetElement = $('body').find('.custom-popup'),
                elementContent = targetElement.find('.popover-content');
        elementContent.html(obj.content);
        targetElement.css({'left': obj.position[0], 'top': obj.position[1]})
        targetElement.show();
    },
    startPageLoading: function (message) {
        var globalImgPath = '/../assets/global/img/';
        $('.page-loading').remove();
        $('body').append('<div class="page-loading-bg"></div>');
        $('body').append('<div class="page-loading"><img src="' + globalImgPath + 'loading-spinner-grey.gif"/>&nbsp;&nbsp;<span>' + (message ? message : datatabletranslations.loadingVar) + '</span></div>');
    },
    stopPageLoading: function () {
        $('.page-loading,.page-loading-bg').remove();
    },
    /**
     * function to get the date format in listing
     * @param {object} dateval the date should be '2014-07-17 15:11:19'
     * @returns date in the format dd.MM.yy HH:mm
     */
    dateFormat: function (dateval) {

        var reggie = /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/;
        var dateArray = reggie.exec(dateval);
        var dateFormat = dateArray[3] + "." + dateArray[2] + "." + dateArray[1] + " " + dateArray[4] + ":" + dateArray[5];

        return dateFormat;



    },
    /**
     * Function to get ids of child elements of a parent div in order
     
     * @param {object} parentDiv        Parent div from where the sort order of child elements have to be taken
     * @returns {String|Number|getOrderOfChildElements.sortOrderArray}             */

    getOrderOfChildElements: function (parentDiv) {
        var sortOrderArray = {};
        var i = 0;
        $($(parentDiv).children()).each(function () {
            i++;
            var childId = $(this).attr('id');
            sortOrderArray[i] = childId;
        });
        sortOrderArray = JSON.stringify(sortOrderArray);

        return sortOrderArray;
    },
    /*
     * Function to get difference of two associate arrays
     
     * @param {array} array1        Array in strigified format
     * @param {array} array2        Array in strigified format
     * @returns {Array|getArrayDifference.differenceArray}             */

    getArrayDifference: function (array1, array2) {
        array1 = JSON.parse(array1);
        array2 = JSON.parse(array2);
        var differenceArray = {};
        jQuery.each(array2, function (key, val) {
            if (!array2.hasOwnProperty(key) || array2[key] !== array1[key]) {
                differenceArray[key] = val;
            }
        });
        differenceArray = JSON.stringify(differenceArray);

        return differenceArray;
    },
    //Function to get first key of an array
    getFirstKeyOfArray: function (array) {
        for (var data in array) {
            return data;
        }
    },
    //Function to show data of a given language and hide data of other languages
    showTranslation: function (lang) {
        $("[data-lang]").addClass('hide');
        $('[data-lang=' + lang + ']').removeClass('hide');
        $('button[data-elem-function=switch_lang]').removeClass('adminbtn-ash').addClass('fg-lang-switch-btn');
        $('button[data-elem-function=switch_lang][data-selected-lang=' + lang + ']').removeClass('fg-lang-switch-btn').addClass('adminbtn-ash');
    },
    //Function to change color of a div on deleting
    changeColorOnDelete: function () {
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function () {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('div#' + parentId);
            $(parentDiv).toggleClass('inactiveblock');
            var parentDivId = $($('#' + parentId).parent()).attr('id');
            FgUtility.resetSortOrder($('#' + parentDivId));
        });
    },
    //Function to change color of a div on deleting and reset required fields
    changeColorAndHandleRequiredOnDelete: function () {
        $('form').off('click', 'input[data-inactiveblock=changecolor]');
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function () {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('div#' + parentId);
            $(parentDiv).toggleClass('inactiveblock');
            if ($(this).attr("checked") == "checked") {
                $(parentDiv).find("[required]").attr("required-field", "1").removeAttr("required");
                $(parentDiv).find("[data-required]").attr("data-required-field", "1").removeAttr("data-required");
            } else {
                $(parentDiv).find("[required-field]").attr("required", "1").removeAttr("required-field");
                $(parentDiv).find("[data-required-field]").attr("data-required", "1").removeAttr("data-required-field");
            }
            var parentDivId = $($('#' + parentId).parent()).attr('id');
            FgUtility.resetSortOrder($('#' + parentDivId));
        });
    },
    //Function to change color of a div on deleting and reset required fields
    handleDelete: function () {
        $('form').on('click', 'input[data-inactiveblock=changecolor]', function () {
            var parentId = $(this).attr('data-parentid');
            var parentDiv = $('div#' + parentId);
            $(parentDiv).toggleClass('inactiveblock');
            if ($(this).is(':checked')) {
                var inpurReq = $(parentDiv).find('input[required]');
                $(inpurReq).attr('data-required', true);
                $(inpurReq).removeAttr('required');

            } else {
                var inpurReq = $(parentDiv).find('input[data-required]');
                $(inpurReq).attr('required', true);
                $(inpurReq).removeAttr('data-required');
            }

        });
    },
    //Function to reset sort order of elements
    resetSortOrder: function (parentElement) {
        var parentElementId = $(parentElement).attr('id');
        if (!$(parentElement).hasClass('excludejs')) {
            $('input[data-sort-parent=' + parentElementId + ']').parent().parent().addClass('blkareadiv'); //for styling
        }
        var i = 0;
        $('input[data-sort-parent=' + parentElementId + ']').each(function () {
            var sortParentElemId = $(this).attr('id').replace('_sort_order', '');
            if (!($($(this).parent()).hasClass('inactiveblock') || $($(this).parent().parent()).hasClass('inactiveblock') || $('#' + sortParentElemId).hasClass('inactiveblock') || $('#' + sortParentElemId).hasClass('fg-dev-rowdeleted'))) {
                i++;
                $(this).val(i);
                $(this).trigger('change');
                if (!$(parentElement).hasClass('excludejs')) {
                    if (i == 1) {
                        $(this).parent().parent().removeClass('blkareadiv').addClass('blkareadiv-top'); //for styling
                    }
                }
            }
        });
        $('form').trigger('checkform.areYouSure');
    },
    groupByMulti: function (obj, values, context) {
        if (!values.length)
            return obj;
        var byFirst = _.groupBy(obj, values[0], context),
                rest = values.slice(1);
        for (var prop in byFirst) {
            byFirst[prop] = FgUtility.groupByMulti(byFirst[prop], rest, context);
        }
        return byFirst;
    },
    back: function (url) {
        //url = document.referrer;
        $(document).on('click', '.bckid', function () {
            //window.history.back();
            var data_url = $(this).attr('data-url');
            data_url = data_url.trim();
            document.location = ((data_url == '#') || (data_url == '')) ? url : data_url;
        });
    },
    //function to display details on a div on clicking an element (eg: show log)
    displayDetailsOnClick: function () {
        $(document).off('click', 'i[data-showlog=true],i[data-showfunction=true],i[data-showadmin=true],i[data-showsponsor=true],i[data-showstats=true]');
        $(document).on('click', 'i[data-showlog=true],i[data-showfunction=true],i[data-showadmin=true],i[data-showsponsor=true],i[data-showstats=true]', function () {
            var parentElementId = $(this).attr('data-parent-div');
            var placeHolderDivId = $(this).attr('data-placeholder');
            var dataid = $(this).attr('data-id');
            var elemFunction = ($(this).hasClass('fa-plus-square-o')) ? 'show' : 'hide';
            if ($($('#' + parentElementId).find('i.fa-minus-square-o')).length) {

                $($('#' + parentElementId).find('i.fa-minus-square-o')).removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
            }

            /*Commented for transistion effect*/
            //$('#displaydetails_' + parentElementId + ' div[showdetail=true]').addClass('hide');


            if (elemFunction == 'show') {
                $('#displaydetails_' + parentElementId + ' div[showdetail=true]').addClass('hide');
                $('#displaydetails_' + parentElementId).removeClass('hide');
                $(this).removeClass('fa-plus-square-o').addClass('fa-minus-square-o');

                $('#' + placeHolderDivId).hide(); //Added code for transistion effect
                $('#' + placeHolderDivId).removeClass('hide');
                $('#' + placeHolderDivId).slideDown(600); //Added code for transistion effect
                $('#' + placeHolderDivId+ ' + .row div[name="fg-dev-add-function"]').slideDown(); //add transition for add function link

                if ($(this)[0].hasAttribute('data-showfunction')) {

                    $('#displaydetails_' + parentElementId + ' div[data-showaddfunc=true]').removeClass('hide');
                    if ($('#' + placeHolderDivId).find('.row').length == 0) {
                        $('#' + placeHolderDivId).addClass('hide');
                    }
                } else {

                    if ($(this)[0].hasAttribute('data-showadmin')) {
                        $('#sponsor_' + dataid).addClass('hide');
                        $('#log' + dataid).addClass('hide');
                    } else if ($(this)[0].hasAttribute('data-showsponsor')) {
                        $('#admin' + dataid).addClass('hide');
                        $('#log' + dataid).addClass('hide');
                    } else if ($(this)[0].hasAttribute('data-showlog')) {
                        var parentdivOfLog = $('#log_' + dataid).parent().parent().attr('id');
                        //$('#log_' + dataid).addClass('fg-control-aranew');
//                        $('html,body').animate({
//                            scrollTop: $('#' + parentdivOfLog).offset().top - 70},
//                        800);
                    }
                    if ($('#displaydetails_' + parentElementId + ' div[data-showaddfunc=true]').length) {
                        $('#displaydetails_' + parentElementId + ' div[data-showaddfunc=true]').addClass('hide');
                    }
                }
            } else {
                /*Commented for transistion effect*/
                //$('#displaydetails_' + parentElementId).addClass('hide');
                $(this).removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
                /*Commented for transistion effect*/
                //$('#' + placeHolderDivId).addClass('hide');
                //Added code for transistion effect
                
                $('#' + placeHolderDivId).slideUp("600", function () {
                    
                    $('#displaydetails_' + parentElementId).addClass('hide');

                });
                $('#' + placeHolderDivId+ ' + .row div[name="fg-dev-add-function"]').slideUp(); //add transition for add function link

            }

        });
    },
    //function for log settings with respect to datatable
    displaylogsettings: function (typeId) {
        if (!$.isEmptyObject($('#log_display_' + typeId).dataTable())) {
            $('#log_display_' + typeId).dataTable().fnDestroy();
            FgTable.initid('log_display_' + typeId, true);
        } else {
            FgTable.initid('log_display_' + typeId, true);
        }
        //for select dropdown
//        $('select[data-event='+typeId+'].selectpicker').selectpicker();
//
//        $('.multiple').on('click', function() {
//            var totald = $(this).parents('ul').find('li').size();
//            var selectedSize = $(this).parents('ul').find('li.selected').size();
//            if (totald - 2 == selectedSize && !($(this).parents('li').hasClass('selected'))) {
//                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectall').prop("selected", true);
//                $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
//                var selObj = $($(this).parents('.bootstrap-select').parent());
//                setTimeout(function() {
//                    FgUtility.checkhtml(selObj)
//                }, 20);
//            } else {
//                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectall').prop("selected", false);
//                $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
//            }
//        });
//
//        $('.selectall').on('click', function() {
//            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectAll').prop("selected", true);
//            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .multiple').prop("selected", true);
//            $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
//            var selObj = $($(this).parents('.bootstrap-select').parent());
//            setTimeout(function() {
//                FgUtility.checkhtml(selObj);
//            }, 20);
//            var totSelected = $(this).parents('ul').find('li.selected').size();
//            if (totSelected == 3) {
//                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectAll').prop("selected", false);
//                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .multiple').prop("selected", false);
//                $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
//                var selObj = $($(this).parents('.bootstrap-select').parent());
//                setTimeout(function() {
//                    FgUtility.checkhtml(selObj, 'none');
//                }, 20);
//            }
//        });
        //ends
    },
    checkhtml: function (elem, flag) {
        if (flag == 'none') {
            elem.find('.filter-option').html("None");
        } else {
            elem.find('.filter-option').html("All");
        }
    },
    isFutureDate: function (idate) {
        //The parameter,idate passed should be timestamp with seconds
        var today = Date.now();
        return (today < parseInt(idate)) ? true : false;
    },
    isGreaterDate: function (idate1, idate2) {
        //The parameter,idate passed should be the date from the datepicker
        // return -1 when idate1 > idate2
        // return 0 when idate1 = idate2
        // return 1 when idate1 < idate2
        if (idate1 != '')
            var idate1Timestamp = moment(idate1, FgLocaleSettingsData.momentDateTimeFormat).format('x');
        else
            var idate1Timestamp = 0;

        if (idate2 != '')
            var idate2Timestamp = moment(idate2, FgLocaleSettingsData.momentDateTimeFormat).format('x');
        else
            var idate2Timestamp = 0;

        if (idate1Timestamp == idate2Timestamp)
            return 0;
        else if (parseInt(idate1Timestamp) > parseInt(idate2Timestamp))
            return 1;
        else if (parseInt(idate1Timestamp) < parseInt(idate2Timestamp))
            return -1;

    },
    validateDate: function (startdate, enddate, divid) {
        //to check whether start date is greater than end date
        if (startdate != '')
            var startdateTimestamp = moment(startdate, FgLocaleSettingsData.momentDateTimeFormat).format('x');
        else
            var startdateTimestamp = 0;

        if (enddate != '')
            var enddateTimestamp = moment(enddate, FgLocaleSettingsData.momentDateTimeFormat).format('x');
        else
            var enddateTimestamp = 0;
        //ends
        //to check whether the dates are less than future date
        var isStartDateFuture = FgUtility.isFutureDate(startdateTimestamp);
        var isEndDateFuture = FgUtility.isFutureDate(enddateTimestamp);
        //ends
        var error_flag = false;
        if ((enddateTimestamp > 0) && (parseInt(startdateTimestamp) > parseInt(enddateTimestamp))) {
            error_flag = true;
            $('#' + divid).css('display', 'block');
            $('#' + divid).html(datatabletranslations['Log_date_filter_err_msg1'] + '.');
        }
        if (isStartDateFuture && isEndDateFuture) {
            error_flag = true;
            $('#' + divid).css('display', 'block');
            $('#' + divid).html(datatabletranslations['Log_date_filter_err_msg2'] + '.');
        }
        else if (isStartDateFuture || isEndDateFuture) {
            if (isStartDateFuture) {
                error_flag = true;
                $('#' + divid).css('display', 'block');
                $('#' + divid).html(datatabletranslations['Log_date_filter_err_msg3'] + '.');
            }
            if (isEndDateFuture) {
                error_flag = true;
                $('#' + divid).css('display', 'block');
                $('#' + divid).html(datatabletranslations['Log_date_filter_err_msg4'] + '.');
            }
        }

        return error_flag;

    },
    validateLeavingDate: function (startdate, enddate, divid) {
        //to check whether start date is greater than end date
        if (startdate != '')
            var startdateTimestamp = moment(startdate, FgLocaleSettingsData.momentDateFormat).format('X');
        else
            var startdateTimestamp = 0;

        if (enddate != '')
            var enddateTimestamp = moment(enddate, FgLocaleSettingsData.momentDateFormat).format('X');
        else
            var enddateTimestamp = 0;

        //to check whether the dates are less than future date
        var isStartDateFuture = FgUtility.isFutureDate(startdateTimestamp);
        var isEndDateFuture = FgUtility.isFutureDate(enddateTimestamp);
        //ends

        var error_flag = false;
        if ((enddateTimestamp == 0) || (parseInt(startdateTimestamp) > parseInt(enddateTimestamp))) {
            error_flag = true;
            $('#' + divid).css('display', 'block');
            $('#' + divid).html(datatabletranslations['Archive_contact_old_leavingdate'] + '.');
        }

        if (isEndDateFuture) {
            error_flag = true;
            $('#' + divid).css('display', 'block');
            $('#' + divid).html(datatabletranslations['Archive_contact_future_leavingdate'] + '.');
        }

        return error_flag;

    },
    moreTab: function () {
        FgUtility.alignMenu();
        $(window).on('resize', function () {
            FgUtility.alignMenu();
        });

        $(".more-tab li.hideshow").click(function () {
            $(this).children("ul").toggle();
        });
        $('.hideshow li').on('click', function () {
            var thisTarget = $(this).data('target');
            $(".more-tab > .active").removeClass('active').hide();
            $(".more-tab").find("[data-target='" + thisTarget + "']").show().addClass('active');
            $(this).hide();
        });
    },
    alignMenu: function () {
        var hrzActive = $(".more-tab > li.active"),
                w = hrzActive.outerWidth(true),
                mw = $(".more-tab").width() - $(".more-tab > li.hideshow").outerWidth(true),
                hideActive = $('.hideshow ul > li.active'),
                hrzHideShow = $('.more-tab > li.hideshow');

        hrzHideShow.hide();
        if (mw < w) {
            hrzActive.hide();
            hideActive.show();
        } else {
            hrzActive.show();
            hideActive.hide();
        }
        $(".more-tab > li").each(function (index) {
            if (!($(this).hasClass('active') || $(this).hasClass('hideshow'))) {
                w += $(this).outerWidth(true);
                if (mw < w) {
                    $(this).hide();
                    $('.hideshow').show();
                    $('.hideshow li').eq(index).show();
                } else {
                    $(this).show();
                    $('.hideshow li').eq(index).hide();
                }
            }
        });
        if (mw + $(".more-tab > li.hideshow").outerWidth(true) > w) {
            $(".more-tab > li").show();
            hrzHideShow.hide();
        }
    },
    initAutoCompleteMultiple: function (url, urlParams, item, addDataField, existingDataField, formId) {
        var engine = new Bloodhound({
            remote: {url: url,
                ajax: {data: urlParams, method: 'post'},
                filter: function (contacts) {
                    dataset = [];
                     var val = $(addDataField).val();
                     var existingArr = val.split(',');
                    $.map(contacts, function (contact) {
                        var exists = false;
                        for (i = 0; i < existingArr.length; i++) {
                            if (contact.id == existingArr[i] ) {
                                exists = true;
                            }
                        }
                        if (!exists) {
                            dataset.push({'id': contact.id, 'value': contact.contactname});
                        }
                    });
                    return dataset;
                }
            },
            datumTokenizer: function (d) {
                return Bloodhound.tokenizers.whitespace(d.name);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace
        });

        engine.initialize();

        $(item).tokenfield({
            typeahead: [
                null,
                {
                    source: engine.ttAdapter(),
                    displayKey: 'value'
                }
            ]
        }).on('tokenfield:createtoken', function (e) {
            var val = $(addDataField).val();
            if(typeof e.attrs.id == "undefined"){
                e.preventDefault();
            }else{
                var newval = '';
                if (val == '') {
                    newval = e.attrs.id;
                } else {
                    newval = val + ',' + e.attrs.id;
                }
                $(addDataField).val(newval);
                $('#' + formId).trigger('checkform.areYouSure');
            }

        }).on('tokenfield:removetoken', function (e) {
            var deletedId = e.attrs.id;console.log(deletedId);
            var toremoveVal = e.attrs.value;
            var existing = $(existingDataField).val();
            if (existing) {
                object = $.parseJSON(existing);
                $.each(object, function (key, val) {
                    if (val == toremoveVal) {
                        deletedId = key;
                    }
                });
            }
            if (deletedId) {
                var vals = $(addDataField).val();
                var idarray = vals.split(',');
                for (i = 0; i < idarray.length; i++) {
                    if (idarray[i] === deletedId) {
                        idarray.splice(i, 1);
                        var newval = idarray.join(',');
                        $(addDataField).val(newval);
                    }
                }
            }
            $('#' + formId).trigger('checkform.areYouSure');
        });
    },
    getAmountWithDiscount: function (amount, discountType, discount) {
        var totalAmount = 0;
        switch (discountType) {
            case 'P':
                totalAmount = amount - ((discount / 100) * amount);
                break;
            case 'A':
                totalAmount = amount - discount;
                break;
            default:
                totalAmount = amount;
                break;
        }
        return totalAmount;
    }

};

/**
 * For removing the newly added rows and for resetting the sorting changes
 */
var FgResetChanges = {
    init: function (resetSections) {
        var _thisObj = this;
        $('#reset_changes').click(function () {

            $('div.addednew').remove(); //To remove newly added rows
            $('.inactiveblock').removeClass('inactiveblock');

            //For resetting the sorting changes
            jQuery.each(resetSections, function (key, resetSection) {
                var parentElement = resetSection.parentElement; //parent div containing sorting elements
                var initialOrder = resetSection.initialOrder; //initial order of sorted elements
                var addClass = resetSection.addClass; //if any class have to be added to sorting elements (for styling)
                var className = resetSection.className; //classname which have to be added to sorting elements (for styling)

                initialOrder = JSON.parse(initialOrder);
                var i = 0;
                jQuery.each(initialOrder, function (key, val) {
                    i++;
                    var appendElement = $(parentElement).find('#' + val);
                    if (addClass) {
                        if (i == 1) {
                            $(appendElement).removeClass(className);
                        } else {
                            $(appendElement).addClass(className);
                        }
                    }
                    $(parentElement).append(appendElement);
                });
            });
            //For check and removing all the fairgatedirty class from the form
            $("form input").removeClass("fairgatedirty");
            //$.uniform.update($('form input:checkbox, form input:radio')); //For resetting the checked state of radio button, checkbox etc
            _thisObj.checkboxReset();
            setTimeout(function () {
                FgTooltip.init()
            },100);
        });
    },
    checkboxReset: function () {
        var timeoutObj = setTimeout(function () {
            $.uniform.update($('form input:checkbox, form input:radio'));
        }, 10);
    }
};

FgInputTag = {
    // Handles custom checkboxes & radios using jQuery Uniform plugin
    handleUniform: function () {
        if (!jQuery().uniform) {
            return;
        }
        var test = $("input[type=checkbox]:not(.toggle, .make-switch), input[type=radio]:not(.toggle, .star, .make-switch)");
        if (test.size() > 0) {
            test.each(function () {
                if ($(this).parents(".checker").size() == 0) {
                    //$(this).show();
                    $(this).uniform();
                }
            });
        }
    }
}
/**
 * For parse the all form field values that has changed
 */
FgParseFormField = {
    fieldParse: function () {
        $('.sortables').parent().each(function () {
            FgUtility.resetSortOrder($(this));
        });
        $('.fg-dev-newfield').addClass('fairgatedirty');
        var objectGraph = {};
        $("form :input").each(function () {
            var attr = $(this).attr('data-key');
            if ($(this).hasClass("fairgatedirty") && typeof attr !== typeof undefined && attr !== false) {
                var inputVal = '';
                var inputType = $(this).attr('type');
                if (inputType == 'checkbox') {
                    inputVal = $(this).attr('checked') ? 1 : 0;
                } else if (inputType == 'radio') {
                    if ($(this).is(':checked')) {
                        inputVal = $(this).val();
                    }
                } else {
                    inputVal = $(this).val();
                }
                if (inputVal !== '' || $(this).is("textarea") || $(this).is("select")) {
                    converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                } else if (inputType == 'hidden' || $(this).hasClass("hide")) {
                    converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                } else if ((inputVal === '') && ($(this).attr('data-notrequired') == 'true')) {
                    converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
            }
        });
        return objectGraph;
    }

}
/**
 * This function is for create a dirty form in a form
 *
 */
FgTextAreaAuto = {
    init: function (height) {
        if (typeof height !== 'undefined') {
            height = height + "px";
        } else {
            height = "28px";
        }
        $('.fg-input-div .auto-textarea').css("height", height);
        $('.fg-input-div .auto-textarea').css("line-height", "15px");
        $('.auto-textarea').autosize();
        /*$("form :input[type=textarea]").each(function(){
         if($(this).find(".auto-textarea")) {
         $('.auto-textarea').autosize();
         }
         });*/
    }
};
/**
 * This function is used for setting a max length for all input boxes
 *
 */
FgInputTextValidation = {
    init: function () {
        if (!$('form :input.form-control[type="text"]').hasClass('fg-dev-autocomplete')) {
            $('form :input.form-control[type="text"]').attr('maxlength', '160');
        }
    }
};

/* Function to load breadcrumb */
Breadcrumb = {
    load: function (obj) {
        if (typeof index_url != 'undefined') {
            $('ul.page-breadcrumb.breadcrumb .fg-dynamic-links').remove();
            var html = '<li class="fg-dynamic-links" data-auto="breadcrumb_level1"><a href="' + index_url + '"><i class="fa fa-home"  data-toggle="tooltip"></i></a></li>';
            var count = 2;
            $('.navbar-nav .fg-dev-header-nav-active').each(function (index) {
                var linkItem = $(this).find('> a');
                if (linkItem.length == 0) {
                    var linkItem = $(this).find('> h3');
                }
                var disableBreadcrumbLink = (linkItem.attr('data-disabled-breadcrumb') === undefined) ? false : linkItem.attr('data-disabled-breadcrumb');
                linkText = linkItem.find('.title').text();
                linkUrl = (linkItem.attr('href') === undefined) ? '#' : ((disableBreadcrumbLink) ? '#' : linkItem.attr('href'));
                var appendId = (linkItem.attr('id') === undefined) ? '' : 'id = "' + linkItem.attr('id') + '"';
                var appendType = (linkItem.attr('data-type') === undefined) ? '' : 'data-type = "' + linkItem.attr('data-type') + '"';
                var appendModule = (linkItem.attr('data-module') === undefined) ? '' : 'data-module = "' + linkItem.attr('data-module') + '"';

                var dynamicClass = (linkUrl === "#" || linkUrl === window.location.pathname) ? 'class="fg-dynamic-links fg-page-inactive"' : 'class="fg-dynamic-links fg-page-active"';
                html += '<li ' + dynamicClass + ' data-auto="breadcrumb_level' + count + '"><i class="fa fa-angle-right"></i><a href="' + linkUrl + '" ' + appendId + ' ' + appendType + ' ' + appendModule + '>' + linkText + '</a></li>';
                count = count + 1;
            });
            if (obj && typeof obj.text != typeof undefined) {
                var link = (obj.link) ? obj.link : "#";
                html += '<li class="fg-dynamic-links fg-page-active" data-auto="breadcrumb_levellast"><i class="fa fa-angle-right"></i><a href="' + link + '">' + obj.text + '</a></li>'
            }
            $('ul.page-breadcrumb.breadcrumb').append(html);
        }
        FgTooltip.init();
    }
};

FgFormTools = {
    // Handles input tag styles
    handleTagsInput: function () {
        if (!jQuery().tagsInput) {
            return;
        }
        $('#fg-wrapper .tags').tagsInput({
            width: 'auto',
            'onChange': function () {
                FgDirtyForm.rescan('form1');
                $(this).addClass('fairgatedirty');

            },
            'onAddTag': function () {
                //alert(1);
            }
        });
    },
    // Handles custom checkboxes & radios using jQuery Uniform plugin
    handleUniform: function () {
        if (!jQuery().uniform) {
            return;
        }
        var test = $("input[type=checkbox]:not(.toggle, .make-switch), input[type=radio]:not(.toggle, .star, .make-switch)");
        if (test.size() > 0) {
            test.each(function () {
                if ($(this).parents(".checker").size() == 0) {
                    //$(this).show();
                    $(this).uniform();
                }
            });
        }
    },
    // Handles Jquery input mask plugin
    handleInputmask: function () {
        //$(".datemask").inputmask(FgLocaleSettingsData.jqueryDateFormat);
        // $(".numbermask").inputmask("numeric", {rightAlign: false, 'digits': "2"});
        $(".numbermask").inputmask("decimal", {
            rightAlign: false,
            placeholder: "",
            digits: 2,
            radixPoint: FgLocaleSettingsData.decimalMark,
            autoGroup: true,
            allowPlus: false,
            allowMinus: false,
            clearMaskOnLostFocus: true,
            removeMaskOnSubmit: true,
            onUnMask: function (maskedValue, unmaskedValue) {
                var x = unmaskedValue.split('.');
                if (x.length != 2)
                    return unmaskedValue;
                return x[0].replace(/\./g, '') + '.' + x[1];
            }
        });
        //$(".numbermask").numeric({ decimal : ".",  negative : false, scale: 3 });
        // append http:// to the content if it is not in correct format        
        $(document).on('blur', ".fg-urlmask", function () {
            appendHttp(this);
        });
        $(document).on('keypress', ".fg-urlmask", function (e) {
            if (e.which == 13) {
                appendHttp(this);
            }
        });

        //append http:// to url field if it is not there
        appendHttp = function (_this) {
            inputVal = $(_this).val();
            if (inputVal != "") {
                var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
                if (!regexp.test(inputVal)) {
                    var indx = inputVal.indexOf("://");
                    if (indx < 0) {
                        returnUrl = "http://" + inputVal.substring(indx);
                    } else {
                        returnUrl = "http" + inputVal.substring(indx);
                    }
                    $(_this).val(returnUrl);
                    //console.log(returnUrl);
                }
            }
        }
    },
    // Handles Jquery input mask plugin
    handleDatepicker: function () {
        $('.datepicker').datepicker({language: datatabletranslations.localeName, format: FgApp.dateFormat.format, autoclose: true, weekStart: '1'});
    },
    handleBootstrapSelect: function () {
        $('.bs-select').selectpicker({
            noneSelectedText: datatabletranslations.noneSelectedText,
            countSelectedText: datatabletranslations.countSelectedText,
        });
    },
    handleSelect2: function () {
        $("select.select2").select2();
        // Hide focusser and search when not needed so virtual keyboard is not shown FAIR - 1014
        $('select.cl-bs-select, select.select2').not('.select-with-search').live("select2-focus", function () {
            if (!($(this).find('.select2-drop').hasClass('.select2-with-searchbox'))) {
                $(this).find('.select2-focusser').hide();
                $(this).find('.select2-drop').not('.select2-with-searchbox').find('.select2-search').remove();
            }
        })
    },
    handleformSelect2: function () {
        $('select.form-select').select2({
            minimumResultsForSearch: -1
        });
    },
    updateUniform: function (selector) {
        $.uniform.update(selector);
    },
    //Function to load country list select boxes to boost performance through ajax load
    select2ViaAjax: function (path, searchCount) {
        $.ajax({
            type: "POST",
            url: path,
            dataType: 'json',
            success: function (data) {
                $("select.fg-select-with-search").each(function () {
                    var value = $(this).val();
                    if (value) {
                        $(this).find("option[value=" + value + "]").remove();
                    }
                });
                $.each(data, function (index, element) {
                    $('.fg-select-with-search').append($('<option/>', {
                        value: index,
                        text: element,
                    }));
                });
                $("select.fg-select-with-search").each(function () {
                    var originalVal = $(this).attr("data-originalVal");
                    $(this).select2({minimumResultsForSearch: searchCount}).select2('val', originalVal);
                });
            }
        });
    }


};
FgFilter = {
    // Handles filter
    init: function (targetDiv, jsonUrl, submitButton, filterName, storageName) {
        $(targetDiv).searchFilter({
            jsonUrl: jsonUrl,
            submit: submitButton,
            filterName: filterName,
            storageName: storageName,
            callback: function (data) {
                alert("callback");
                //oTable.aoData.push({name: "filterdata", value: 'manesh'});
                filterdata = data;
                oTable.fnDraw();
            }
        });
    }
}
FgPopOver = {
    // Handles filter
    init: function (selector, htmlflag, staybahaviour) {

        htmlflag = typeof htmlflag !== 'undefined' ? htmlflag : false;
        staybahaviour = typeof staybahaviour !== 'undefined' ? staybahaviour : true;
        if (staybahaviour) {
            $("body").on('mouseenter touchstart', selector, function () {
                $('.popover').remove();
                $(this).popover({
                    html: htmlflag,
                    trigger: 'manual',
                    container: 'body',
                    placement: function () {
                        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))
                        {
                            return 'auto';
                        }
                        else
                        {
                            var width = $(window).width() - 300;
                            var xPos = this.getPosition().left;
                            var placement = width < xPos ? 'left' : 'auto';
                            return placement;
                        }
                    },
                }).on("mouseenter", function () {
                    var _this = this;
                    $(this).popover("show");
                    $(this).siblings(".popover").on("mouseleave", function () {
                        $(_this).popover('hide');
                    });

                }).on("mouseleave", function () {
                    var _this = this;
                    setTimeout(function () {
                        $(_this).popover("hide");
                    }, 50);
                    $('.popover .popover-content').width('');
                }).popover('show');

                //$('.popover .popover-content').width($('.popover').width()-27);     
            });
        } else {
            $('body').popover({
                selector: selector,
                trigger: 'hover',
                html: htmlflag,        
                delay: {show: 100, hide: 800}
            });
        }


    },
    customPophover: function (userClass) {
        $(userClass).popover({
            trigger: 'hover',
            html: true,
            placement: 'auto'

        });
    }

}

FgEnterPress = {
    // Handles submission of login page while pressing ENTER Key
    init: function () {

        $("input").keypress(function (event) {
            if (event.which == 13) {
                event.preventDefault();
                $("form").submit();
            }
        });
    }
}

FgActionDropDownClick = {
    init: function () {

        $("body").on('click', "a.fg-dev-menu-click", function (event) {
            event.preventDefault();
            var contactids = '';
            var seperator = '';
            var redirectPath = '';
            var actionType = '';
            var searchvalue = '';
            var count = $("#fcount").html();
            var itemids = '';
            var assignmentJsonArr = '';

            redirectPath = $(this).attr('data-url');
            actionType = $(this).attr('data-action-type');
            var dataType = $(this).attr('data-type');
            var datatableListtype = $(this).attr('data-list-type');
            if (dataType == 'selected') {
                if (datatableListtype !== 'active') {
                    if (datatableListtype == 'activeassignments' || datatableListtype == 'recentlydelete' || datatableListtype == 'futureassignments' || datatableListtype == 'formerassignments') {
                        $('#fg_dev_assignmentTable input.dataClass:checked').each(function () {
                            contactids += seperator + $(this).attr('id');
                            seperator = ',';
                        });
                    } else {
                        $('#fg_dev_' + datatableListtype + ' input.dataClass:checked').each(function () {
                            contactids += seperator + $(this).attr('id');
                            seperator = ',';
                        });
                    }
                } else {
                    if ($(".dataTables_wrapper div").hasClass('DTFC_LeftBodyWrapper')) {
                        $('.dataTables_wrapper .DTFC_LeftBodyWrapper tbody input.dataClass:checked').each(function () {
                            contactids += seperator + $(this).attr('id');
                            seperator = ',';
                        });
                    } else {
                        $('input.dataClass:checked').each(function () {
                            contactids += seperator + $(this).attr('id');
                            seperator = ',';
                        });
                    }
                }
            }
            //alert(actionType);return;
            switch (actionType) {
                case 'assign':
                case 'move':
                case 'remove':
                    var filterData, column = '';
                    if ($(".searchbox:visible").val().length > 0) {
                        searchvalue = $(".searchbox:visible").val();
                    }
                    if (typeof filterStorage != 'undefined') {
                        filterData = localStorage.getItem(filterStorage);
                    }
                    if (typeof tableSettingValueStorage != 'undefined') {
                        column = localStorage.getItem(tableSettingValueStorage);
                    }

                    //Drag menu details
                    var activeMenuId = (FgSidebar.activeSubMenuVar) ? localStorage.getItem(FgSidebar.activeSubMenuVar) : localStorage.getItem("submenu");
                    var activeMenuCategory_id = $('#' + activeMenuId).find('.sidebabar-link').attr('data-categoryid');
                    var activeMenuRole_id = $('#' + activeMenuId).find('.sidebabar-link').attr('data-id');
                    var activeMenuTitle = $('#' + activeMenuId).find('.sidebabar-link > span.title').html();
                    var activeMenusidebarType = ($('#' + activeMenuId).find('.sidebabar-link').attr('data-type') == 'MISSING_MEMBERSHIP') ? 'FROLES' : $('#' + activeMenuId).find('.sidebabar-link').attr('data-type');
                    //Drag menu details
                    if (dataType == 'all') {
                        itemids = 'all';
                        var url = redirectPath;
                        var sidebarItem = $.getJSON(url, {'selItemIds': itemids, 'searchVal': searchvalue, 'filterData': filterData, 'columns': column}, function (data) {
                            var contactIdsArr = data['itemIds'];
                            //alert(url);return;
                            $("#selcontacthidden").val(JSON.stringify(contactIdsArr));
                            assignmentJsonArr = {'dragMenuId': activeMenuRole_id, 'dragMenuTitle': activeMenuTitle, 'dragCategoryId': activeMenuCategory_id, 'dragCatType': activeMenusidebarType, 'selItemIds': itemids, 'filterData': filterData, 'searchVal': searchvalue};
                            showPopup('assignment', {assignmentData: assignmentJsonArr, 'actionType': actionType, 'selActionType': dataType});
                        });                        
                    } else {
                        itemids = contactids;
                        assignmentJsonArr = {'dragMenuId': activeMenuRole_id, 'dragMenuTitle': activeMenuTitle, 'dragCategoryId': activeMenuCategory_id, 'dragCatType': activeMenusidebarType, 'selItemIds': itemids, 'filterData': filterData, 'searchVal': searchvalue};
                        showPopup('assignment', {assignmentData: assignmentJsonArr, 'actionType': actionType, 'selActionType': dataType});
                    }                    
                    break;
                case 'archive':
                    if ($(".searchbox:visible").val().length > 0) {
                        searchvalue = $(".searchbox:visible").val();
                    }
                    var filterData = localStorage.getItem(filterStorage);
                    if (dataType == 'all') {
                        contactids = 'all';
                        var url = redirectPath;
                        var sidebarItem = $.getJSON(url, {'selcontactIds': contactids, 'searchVal': searchvalue, 'filterData': filterData}, function (data) {
                            var contactIdsArr = data['contactIds'];
                            $("#selcontacthidden").val(JSON.stringify(contactIdsArr));
                        });
                    }
                    showPopup('archive', {'actionType': actionType, 'selActionType': dataType, 'selContacts': contactids});
                    break;
                case 'editcontact':
                case 'edittemplate':
                case 'createtemplate':
                    window.location = redirectPath;
                    break;
                case 'templateduplicate':
                    showPopup(actionType, {'actionType': actionType, 'selActionType': dataType});
                    break;
                case 'documentdelete':
                    if (contactids == '') {
                        break;
                    } else if (contactids != '') {
                        var selectedDocCount = $(".chk_cnt").html();
                        showPopup('documentdelete', {'actionType': actionType, 'selActionType': dataType, 'contactCount': selectedDocCount, 'selectIds': contactids});
                    }
                    break;
                case 'documentOldVersionDelete':
                    var documentId = $('ul[data-document-id]').attr('data-document-id');
                    showPopup('documentOldVersionDelete', {'actionType': actionType, 'documentId': documentId});
                    break;
                case 'documentVersionDelete':
                    var docids = '';
                    var seperator = '';
                    if (dataType == 'selected') {
                        $("input.dataClass:checked").each(function () {
                            docids += seperator + $(this).attr('id');
                            seperator = ',';
                        });
                    }
                    if (docids == '') {
                        break;
                    } else {
                        showPopup('documentVersionDelete', {'actionType': actionType, 'selActionType': dataType});
                        break;
                    }

                case 'documentDownload':
                    var versionId = parseInt($('input.dataClass:checked').attr('id'));
                    redirectPath = redirectPath.replace('versionId', versionId);
                    if (redirectPath != '') {
                        window.open(redirectPath, '_blank');
                    }
                    break;
                case 'templatedelete':
                case 'subscriberdelete':
                case 'subscriberexport':
                    showPopup(actionType, {'actionType': actionType, 'selActionType': dataType});
                    break;
                case 'confirmchanges':
                case 'discardchanges':
                    showPopup(actionType, {'actionType': actionType, 'selActionType': dataType, 'urlpath': redirectPath});
                    break;
                case 'confirmConfirmations':
                case 'discardConfirmations':
                    showPopup(actionType, {'actionType': actionType, 'selActionType': dataType, 'urlpath': redirectPath});
                    break;
                case 'skipAssignment':
                    if (contactids == '') {
                        var dataObj = overviewTable.rows({
                            order: 'applied', // 'current', 'applied', 'index',  'original'
                            search: 'applied', // 'none',    'applied', 'removed'
                            page: 'all'      // 'all',     'current'
                        }).data();
                        booking_ids = _.pluck(dataObj, '@MSB_BOOKING_ID := MSB.id');
                        contactids = JSON.stringify(booking_ids).replace(/^\[|]$/g, '');
                    } else {
                        contactids = JSON.stringify(contactids).replace(/^\[|]$/g, '');
                    }
                    var selectedId = contactids;
                    servicesCount = contactids.split(",").length;
                    if (typeof CurrentContactId == 'undefined') {
                        CurrentContactId = 0;
                    }
                    FgUtility.startPageLoading();
                    pageType = 'recently_ended';
                    var passingData = {'selectedId': selectedId, 'actionType': actionType, 'pageType': pageType, "CurrentContactId": CurrentContactId};
                    $.post(pathSponsorServiceSkip + "?rand=" + Math.random(), passingData, function (result) {
                        FgUtility.stopPageLoading();
                        if (result.flash) {
                            FgUtility.showToastr(result.flash);
                        }
                        FgConfirmStop.callBackFn(result.activeServicesCount, actionType, pageType, selectedId, servicesCount, 0);
                    });
                    break;
                case 'stopserviceofsponsor':
                case 'deleteserviceofsponsor':
                case 'stopservice':
                case 'deleteservice':
                case 'stopassignmentOverview':
                    if (contactids == '') {
                        break;
                    } else if (contactids != '') {
                        if (actionType == 'stopserviceofsponsor' || actionType == 'deleteserviceofsponsor') {
                            pageType = 'servicelist';
                        } else if (actionType == 'stopservice' || actionType == 'deleteservice') {
                            if (datatableListtype == 'activeassignments') {
                                pageType = 'active_assignments';
                            } else if (datatableListtype == 'futureassignments') {
                                pageType = 'future_assignments';
                            } else if (datatableListtype == 'formerassignments') {
                                pageType = 'former_assignments';
                            } else if (datatableListtype == 'recentlydelete') {
                                pageType = 'recently_ended';
                            } else {
                                pageType = 'sponsorlist';
                            }
                        } else if (actionType == 'skipAssignment') {
                            pageType = 'recently_ended';
                        } else if (actionType == 'stopassignmentOverview') {
                            if (datatableListtype == 'activeassignments') {
                                pageType = 'active_assignments';
                            }
                        }
                        showPopup('stopservice', {'actionType': actionType, 'selActionType': dataType, 'bookedIds': contactids, "pageType": pageType});
                    }
                    break;
                case 'serviceexportcsv':
                case 'assignmentexportcsv':
                    var allserviceCount = '';
                    var pageinfo = '';
                    var searchCount = '';
                    if (datatableListtype == "activeservice") {
                        pageinfo = activeserviceTable.page.info();

                    } else if (datatableListtype == "futureservice") {
                        pageinfo = futureserviceTable.page.info();
                    } else if (datatableListtype == "futureassignments" || datatableListtype == "activeassignments" || datatableListtype == "recentlydelete" || datatableListtype == "formerassignments") {
                        allserviceCount = $("#fcount").html();
                        pageinfo = overviewTable.page.info();
                    } else {
                        pageinfo = formerserviceTable.page.info();
                    }
                    searchCount = pageinfo.recordsDisplay;
                    if (contactids == '') {
                        showPopup('serviceexportcsv', {'actionType': actionType, 'selActionType': dataType, 'tabType': datatableListtype, 'bookedIds': '', 'allserviceCount': allserviceCount, 'searchCount': searchCount});
                    } else if (contactids != '') {
                        showPopup('serviceexportcsv', {'actionType': actionType, 'selActionType': dataType, 'tabType': datatableListtype, 'bookedIds': contactids, 'allserviceCount': allserviceCount, 'searchCount': searchCount});
                    }
                    break;
                case 'reactivate':
                    //$('#reactivateContactId').val(contactids);
                    // $('#reacivateRedirectPath').val(redirectPath);

                    var htmlgetPath = $(this).attr('data-html-path');

                    $.get(htmlgetPath, {'selcontactIds': contactids}, function (data) {
                        $('#popup_contents').html(data);
                        $('#popup').modal('show');
                        
                        $("#reactivate").on('click', function () {
                            var fedMembershipVal = 0;
                            if($('#fedmembership').length > 0) {
                                fedMembershipVal = $('#fedmembership').val();
                            }
                            $('form#archivecontacts .help-block').remove();
                            $('form#archivecontacts .has-error').removeClass('has-error');
                            if ($('#fedmembership').val() == '' && $('#fedmembership').length > 0) {
                                $('form#archivecontacts select#fedmembership').parent().addClass('has-error');
                                $('<span class="help-block fg-marg-top-5">required</span>').insertAfter($('form#archivecontacts select#fedmembership + .btn-group.bootstrap-select'));
                                return false;
                            }
                            contactid = contactids;
                            assignmentJsonAr = {'selcontactIds': contactid, 'contactType': contactType, 'fedMembershipVal': fedMembershipVal};
                            $('#popup').modal('hide');
                            $.get(redirectPath, {'archivedData': assignmentJsonAr}, function (response) {
                                if (response.status != 'MERGE') {
                                    if (contactType == "archivedsponsor") {
                                        sponsorTable.api().draw();
                                    } else {
                                        oTable.api().draw();
                                    }
                                    if (response.status == 'FAILURE') {
                                        FgUtility.showToastr(response.flash, 'warning');
                                    } else {
                                        FgUtility.showToastr(response.flash, 'success');
                                        FgCountUpdate.updateTopNav('add', 'contact', 'active', response.totalCount);
                                        if (contactType == "archivedsponsor") {
                                            FgCountUpdate.updateTopNav('remove', 'sponsor', 'archived', response.totalCount);
                                            FgCountUpdate.updateTopNav('add', 'sponsor', 'active', response.totalCount);
                                        } else {
                                            FgCountUpdate.updateTopNav('remove', 'contact', 'archive', response.totalCount);
                                        }
                                    }
                                } else {
                                    if(response.mergeable){
                                        if(contactids.split(",").length > 1)
                                           FgMultipleMergePopup.handleMergerablePopup(response);
                                        else
                                            FgMergePopup.handleMergerablePopup(response);
                                    }
                                }
                            });
                        });
                    });


                    break;
                case 'delete':
                case 'removearchivesponsor':
                    if ($(".searchbox:visible").length) {
                        if ($(".searchbox:visible").val().length > 0) {
                            searchvalue = $(".searchbox:visible").val();
                        }
                    }
                    if (dataType == 'all') {
                        contactids = 'all';
                        var url = redirectPath;
                        var sidebarItem = $.getJSON(url, {'selcontactIds': contactids, 'searchVal': searchvalue, 'filterData': filterData}, function (data) {
                            var contactIdsArr = data['contactIds'];
                            $("#selcontacthidden").val(JSON.stringify(contactIdsArr));
                        });
                    }
                    showPopup('delete', {'actionType': actionType, 'selActionType': dataType});
                    break;

                case 'deletecontactdocument':
                case 'deleteclubdocument':
                    requestPath = redirectPath;
                    $('#popup_contents').html('');
                    if (requestPath != '') {
                        $.post(requestPath, {"assignmentId": contactids}, function (data) {
                            $('#popup_contents').html(data);
                            $('#popup').modal('show');
                        });
                    }
                    break;
                case 'deleteallcontactdocument':
                    requestPath = redirectPath;
                    $('#popup_contents').html('');
                    if (requestPath != '') {
                        $.post(requestPath, {"removecontact": "all"}, function (data) {
                            $('#popup_contents').html(data);
                            $('#popup').modal('show');
                        });
                    }
                    break;
                case 'deleteallclubdocument':
                    requestPath = redirectPath;
                    $('#popup_contents').html('');
                    if (requestPath != '') {
                        $.post(requestPath, {"removeclub": "all"}, function (data) {
                            $('#popup_contents').html(data);
                            $('#popup').modal('show');
                        });
                    }
                    break;
                case 'editassigneddocument':
                    var editUrl;
                    $('input.dataClass:checked').each(function () {
                        editUrl = $(this).attr('edit-url');
                    });
                    window.location = editUrl;
                    break;
                case 'editdocument':
                    var indexvalue = parseInt($('input.dataClass:checked').attr('data_index'));
                    redirectPath = redirectPath.replace('Id', contactids);
                    redirectPath = redirectPath.replace('index', indexvalue);
                    window.location = redirectPath;
                    break;
                case 'documentlog':
                    var indexvalue = parseInt($('input.dataClass:checked').attr('data_index'));
                    redirectPath = redirectPath.replace('Id', contactids);
                    redirectPath = redirectPath.replace('index', indexvalue);
                    window.location = redirectPath;
                    break;
                case 'formerfedmember-delete':

                    var htmlgetPath = $(this).attr('data-html-path');
                    var redirectPaths = redirectPath;
                    $.get(htmlgetPath, {'selcontactIds': contactids, 'dataType': dataType}, function (data) {
                        $('#popup_contents').html(data);
                        $('#popup').modal('show');
                        $("#reactivate").on('click', function () {
                            var contactid = contactids;
                            var assignmentJsonAr = {'selcontactIds': contactid, 'dataType': dataType};
                            $('#popup').modal('hide');
                            showPopup('formerfedmember-delete', {archivedData: assignmentJsonAr, 'urlpath': redirectPaths});

                        });

                    });
                    break;
                case 'createExist':
                    $('#popup_contents').html('');
                    params = {"type": ""};
                    $.post(redirectPath, params, function (data) {
                        $('#popup_contents').html(data);
                        $('#popup').modal('show');
                    });
                    break;
                case 'removeProspect':
                    if (dataType === 'all') {
                        itemids = 'all';
                        var url = $(this).attr('data-intermediate-redirect');
                        var searchvalue = '';
                        if ($(".searchbox:visible").val().length > 0) {
                            searchvalue = $(".searchbox:visible").val();
                        }
                        var filterData = localStorage.getItem(filterStorage);
                        var column = localStorage.getItem(tableSettingValueStorage);
                        $.getJSON(url, {'searchVal': searchvalue, 'filterData': filterData, 'columns': column}, function (data) {
                            contactids = data['contactIds'];

                            params = {"contactids": contactids, "actionType": dataType};
                            $('#popup_contents').html('');
                            $.post(redirectPath, params, function (data) {
                                $('#popup_contents').html(data);
                                $('#popup').modal('show');
                            });
                        });
                    } else {
                        params = {"contactids": contactids, "actionType": dataType};
                        $('#popup_contents').html('');
                        $.post(redirectPath, params, function (data) {
                            $('#popup_contents').html(data);
                            $('#popup').modal('show');
                        });
                    }
                    break;
                case 'editSponsor':
                    redirectPath = redirectPath.replace('CONTACT', contactids);
                    if (redirectPath != '') {
                        window.location = redirectPath;
                    }
                    break;
                case 'assignService':

                    if (dataType === 'all') {
                        itemids = 'all';
                        var url = $(this).attr('data-intermediate-redirect');
                        // alert(url);return;
                        var searchvalue = '';
                        if ($(".searchbox:visible").val().length > 0) {
                            searchvalue = $(".searchbox:visible").val();
                        }
                        var filterData = localStorage.getItem(filterStorage);
                        var column = localStorage.getItem(tableSettingValueStorage);

                        $.getJSON(url, {'searchVal': searchvalue, 'filterData': filterData, 'columns': column}, function (data) {
                            contactids = data['contactIds'];
                            // console.log(data);
                            var form = $('<form action="' + redirectPath + '" method="post">' +
                                    '<input type="hidden" name="contactids" value="' + contactids + '" />' +
                                    '<input type="hidden" name="actionType" value="' + dataType + '" />' +
                                    '</form>');
                            $('body').append(form);
                            $(form).submit();
                        });
                    } else {
                        var form = $('<form action="' + redirectPath + '" method="post">' +
                                '<input type="hidden" name="contactids" value="' + contactids + '" />' +
                                '<input type="hidden" name="actionType" value="' + dataType + '" />' +
                                '</form>');
                        $('body').append(form);
                        $(form).submit();
                    }
                    break;
                case 'serviceAssign':
                    var form = $('<form action="' + redirectPath + '" method="post">' +
                            '<input type="hidden" name="contactids" value="' + CurrentContactId + '" />' +
                            '<input type="hidden" name="backTo" value="' + CurrentContactId + '|' + CurrentOffset + '" />' +
                            '</form>');
                    $('body').append(form);
                    $('form').submit();
                    break;
                case 'editService':
                    var buttonType = $(this).parents('.fg-dev-dataTable-hide-wrapper[data-table-type]').attr('data-table-type');
                    var bookinId = $('.fg-dev-dataTable-hide-wrapper[data-table-type=' + buttonType + '] input.dataClass:checked').attr('id');
                    redirectPath = redirectPath.replace('BOOKINGID', bookinId);
                    if (redirectPath != '' && typeof CurrentContactId !== typeof undefined) {
                        var form = $('<form action="' + redirectPath + '" method="post">' +
                                '<input type="hidden" name="backTo" value="' + CurrentContactId + '|' + CurrentOffset + '" />' +
                                '</form>');
                        $('body').append(form);
                        $('form').submit();
                    } else if (redirectPath != '') {
                        window.location = redirectPath;
                    }
                    break;
                case 'editAssignment':
                    var bookinId = $('.tab-content .active  input.dataClass:checked').attr('id');
                    redirectPath = redirectPath.replace('BOOKINGID', bookinId);
                    if (redirectPath != '') {
                        window.location = redirectPath;
                    }
                    break;
                case 'exportpdf':
                    var sponsorlistDet = localStorage.getItem(fgLocalStorageNames.sponsor.active.listDetails);
                    var sponsorListJson = JSON.parse(sponsorlistDet);
                    var exportData;
                    var serviceName = $(".page-title-sub").html();
                    if (datatableListtype == "activeservice") {
                        exportData = activeserviceTable.rows({order: 'applied', search: 'applied', page: 'all'}).data();
                    } else if (datatableListtype == "futureservice") {
                        exportData = futureserviceTable.rows({order: 'applied', search: 'applied', page: 'all'}).data();
                    }
                    exportData.context = '';
                    exportData.length = '';
                    exportData.selector = '';
                    exportData.ajax = '';
                    if (contactids != '') {
                        var selectedIds = contactids.split(',');
                        exportData = _(exportData).filter(function (x) {
                            return _.contains(selectedIds, x['SA_bookingId'])
                        });
                    }
                    exportData = JSON.stringify(exportData);
                    if (redirectPath != '') {
                        var form = $("<form id='exportpdf' action='" + redirectPath + "' method='post'>" +
                                "<input type='hidden' name='contactids' id='contactids' value='" + contactids + "' />" +
                                "<input type='hidden' name='datatableListtype' id='datatableListtype' value='" + datatableListtype + "' />" +
                                "<input type='hidden' name='exportData' id='exportData' value='" + exportData + "' />" +
                                "<input type='hidden' name='serviceName' id='serviceName' value='" + serviceName + "' />" +
                                "<input type='hidden' name='serviceId' id='serviceId' value='" + sponsorListJson.id + "' />" +
                                "</form>");
                        $("#exportpdf").remove();
                        $('body').append(form);
                        $("#exportpdf").submit();
                        break;
                    }

                case 'sa_export_pdf':
                    var pdfStr = $('#data-tabss li.active a').attr('data-startdate');
                    var pdfEnd = $('#data-tabss li.active a').attr('data-enddate');
                    var pdfLabel = $('#data-tabss li.active a').html();
                    var type = $('#data-tabs li.active a').attr('type_id');
                    var exportData = smTable.rows({order: 'applied', search: 'applied', page: 'all'}).data();
                    exportData = _(exportData).filter(function (data) {
                        return data
                    });
                    exportData = JSON.stringify(exportData);
                    exportData = exportData.replace(/'/g, "&apos;")
                    var form = $("<form id='sa_export_pdf' action='" + redirectPath + "' method='post'>" +
                            "<input type='hidden' name='startDate' id='startDate' value='" + pdfStr + "' />" +
                            "<input type='hidden' name='endDate' id='endDate' value='" + pdfEnd + "' />" +
                            "<input type='hidden' name='yearLabel' id='yearLabel' value='" + pdfLabel + "' />" +
                            "<input type='hidden' name='tabtype' id='tabtype' value='" + type + "' />" +
                            "<input type='hidden' name='exportData' id='exportData' value='" + exportData + "' />" +
                            "</form>");
                    $("#sa_export_pdf").remove();
                    $('body').append(form);
                    $("#sa_export_pdf").submit();

                    break;
                case 'sa_export_csv':
                    var startDate = $('#data-tabss li.active a').attr('data-startdate');
                    var endDate = $('#data-tabss li.active a').attr('data-enddate');
                    var yearLabel = $('#data-tabss li.active a').html();
                    var tabType = $('#data-tabs li.active a').attr('type_id');
                    showPopup('sa_export_csv', {'actionType': actionType, 'startDate': startDate, 'tabType': tabType, 'endDate': endDate, 'yearLabel': yearLabel});
                    break;
                case 'movedocument':
                    //for moving document to another subcategory from document listing
                    var assignmentData = {dragCategoryId: "", dragMenuId: "", dropCategoryId: "", dropCategoryTitle: "", dropMenuId: "", moveText: ""};
                    showDocumentPopup({assignmentData: assignmentData, 'selActionType': '', 'subcategoryName': ''});
                    break;
                case 'duplicateassignment':
                    var form = $('<form action="' + redirectPath + '" method="post">' +
                            '<input type="hidden" name="bookedId" id="bookedId" value="' + contactids + '" />' +
                            '</form>');
                    $('body').append(form);
                    $(form).submit();
                    break;
                case 'assignment_exportpdf':
                    var exportData;
                    if (datatableListtype == "futureassignments" || datatableListtype == "activeassignments") {
                        exportData = overviewTable.rows({order: 'applied', search: 'applied', page: 'all'}).data();
                        exportData.context = '';
                        exportData.length = '';
                        exportData.selector = '';
                        exportData.ajax = '';
                    }
                    if (contactids != '') {
                        var selectedIds = contactids.split(',');
                        exportData = _(exportData).filter(function (x) {
                            return _.contains(selectedIds, x['SA_bookingId'])
                        });
                    }
                    _.map(exportData, function (num) {
                        return num.displayData = ''
                    })
                    exportData = JSON.stringify(exportData);

                    var form = $("<form class ='expo' id='assignment_exportpdf' action='" + redirectPath + "' method='post'>" +
                            "<input type='hidden' name='contactids' id='contactids' value='" + contactids + "' />" +
                            "<input type='hidden' name='datatableListtype' id='datatableListtype' value='" + datatableListtype + "' />" +
                            "<input type='hidden' name='exportData' id='exportData' value='" + exportData + "' />" +
                            "</form>");
                    $("#assignment_exportpdf").remove();
                    $('body').append(form);
                    $("#assignment_exportpdf").submit();
                    break;
                case 'addexistingfedmember':
                    showPopup('addexistingfedmember', {path:redirectPath });
                    break;
                case 'assign_membership':
                    showPopup(actionType, {path:redirectPath,assignmentData:{actionType:'assign',dropCatType:'membership'}});
                    break;
                case 'assign_fedmembership':
                    showPopup(actionType, {path:redirectPath,assignmentData:{actionType:'assign',dropCatType:'fed_membership'}});
                    break;
                case 'quit_membership':
                    showPopup(actionType, {path:redirectPath,actionType:actionType});
                    break;
                case 'quit_fed_membership':
                    showPopup(actionType, {path:redirectPath,actionType:actionType});
                    break;
                default:
                    if ($(".searchbox:visible").length) {
                        if ($(".searchbox:visible").val().length > 0) {
                            searchvalue = $(".searchbox:visible").val();
                        }
                    }
                    $("#selcontacthidden").val(contactids);
                    if (dataType == 'all') {
                        $("#selcontacthidden").val('');
                        $("#counthidden").val(count);
                    }
                    $("#searchhidden").val(searchvalue);
                    $("#hiddenform").attr("action", redirectPath);
                    $("#hiddenform").submit();
                    break;
            }
        });
    }
};

FgColumnSettings = {
    /* function to handle multi-select dropdown */
    handleSelectPicker: function () {
        $('.single').on('click', function () {
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('deselectAll');
        });
        $('.multiple').on('click', function () {
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .single').prop("selected", false);
            var totalElements = $(this).parents('ul').find('li a.multiple').size();
            var totSelected = $(this).parents('ul').find('li.selected').size();
            var singleElemCount = $($(this).parent().parent().find('li.selected a.single')).length;
            var selectedMultiElmCnt = totSelected - singleElemCount;
            if (((totalElements - 1) == selectedMultiElmCnt) && !($(this).parents('li').hasClass('selected'))) {
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectall').prop("selected", true);
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
                var parentElem = $($(this).parents('.bootstrap-select').parent());
                FgColumnSettings.showSelectAllTitle(parentElem, 'all');
            } else {
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectall').prop("selected", false);
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
            }
        });
        $('.selectall').on('click', function () {
            var totalElements = $(this).closest('ul').find('li a.multiple').size() + 1;
            var totSelected = $(this).closest('ul').find('li.selected').size();
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .single').prop("selected", false);
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .multiple').prop("selected", true);
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectAll').prop("selected", true);
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
            var parentElem = $($(this).parents('.bootstrap-select').parent());
            FgColumnSettings.showSelectAllTitle(parentElem, 'all');
            //for de-selecting
            if (totSelected == totalElements) {
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .selectAll').prop("selected", false);
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker .multiple').prop("selected", false);
                $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('render');
                FgColumnSettings.showSelectAllTitle(parentElem, 'none');
            }
        });
    },
    /* function to display 'All' title */
    showSelectAllTitle: function (parentElem, type) {
        var html = (type == 'all') ? all : none;
        setTimeout(function () {
            parentElem.find('.filter-option').html(html);
        }, 20);
    }
};

FgTopNavigation = {
    init: function () {
        //for select the menu "settings" if any of its submenu is selected
        var topNavSettings = $('.fg-dev-top-settings-selected').children().find('.fg-header-nav-active');
        $.each(topNavSettings, function (key, valueData) {
            var parentData = $(valueData).closest("ul").attr('id');
            $('#' + parentData).parent('li').addClass('fg-header-nav-active');
        });


        if ($('body').find('.fg-sticky-block').length > 0) {
            $('body').addClass('fg-sticky-save-area');
        } else {
            $('body').removeClass('fg-sticky-save-area');
        }
    },
    //For autocomplete search in top navigation menu (contact, club, document, sponsors)
    //@searchPath  array serach path for contact, club, document, sponsors
    //@overviewPath array of overview path of club and documents
    search: function (pathDetails) {
        searchPath = pathDetails.searchPath;
        overviewPath = pathDetails.overviewPath;
        $.each(searchPath, function (module, autocompleteSearchPath) {
            if ($('.fg-autocomplete-' + module).length > 0) {
                $('.fg-autocomplete-' + module).fbautocomplete({
                    url: autocompleteSearchPath, // which url will provide json!
                    maxItems: 1, // only one item can be selected                
                    useCache: false,
                    onOpen: function (event) {
                        $('.navbar-nav').find('.fg-header-nav-' + module).addClass("fg-nav-bar-open");
                    },
                    onClose: function (event) {
                        $('.navbar-nav').find('.fg-header-nav-' + module).removeClass("fg-nav-bar-open");
                    },
                    onItemSelected: function ($obj, itemId, selected) {
                        if (module === 'sponsor' || module === 'contact') {
                            overViewPath = selected[0]['path'];
                        }
                        if (module === 'document') {
                            $.each(overviewPath.document, function (documentType, documentOverviewPath) {
                                if (documentType.toUpperCase() === selected[0]['type']) {
                                    docEditPath = documentOverviewPath.replace("OFFSET", 0);
                                    overViewPath = docEditPath.replace("DOCID", selected[0]['id']);
                                }
                            });
                        }
                        if (module === 'club') {
                            clubOverViewPath = overviewPath.club.replace("OFFSET", 0);
                            overViewPath = clubOverViewPath.replace("CLUB", itemId);
                        }
                        window.location.href = overViewPath;
                    }
                });
            }
        });
    }
};

/**
 * Function for confirmation popup on buttons
 */
FgConfirmation = {
    /**
     * Function for confirmation popup on buttons
     * @param {string} confirmNote1    Confirmation note
     * @param {type} cancelLabel       Cancel label
     * @param {type} continueLabel     Continue label
     * @param {type} element           Button element on which popup appears
     * @param {type} onCancel          Confirmation status on cancel (hide/destroy)
     * @param {type} fncallback        Call back function
     * @param {type} params            Params for call back function
     * @param string cancelCallback    Function to be executed if there is anything to do on clicking cancel
     * @param json   cancelParams      Cancel callback parameters
     */
    confirm: function (confirmNote1, cancelLabel, continueLabel, element, fncallback, params, trigger, cancelCallback, cancelParams) {
        trigger = (trigger) ? trigger : 'manual';
        $("[data-toggle='confirmation']").confirmation({
            title: confirmNote1,
            placement: "top",
            trigger: trigger,
            btnCancelLabel: cancelLabel,
            btnOkLabel: continueLabel,
            popout: true,
            onConfirm: function () {
                fncallback.call({}, params);
            },
            onCancel: function () {
                if (cancelCallback) {
                    cancelCallback.call({}, cancelParams);
                } else {
                    element.confirmation("destroy");
                }
            }
        });
        element.confirmation('show');
    }
};

$("body").on('click', ".fgContactdrop", function () {
    var dataType = $(this).attr('data-type');
    var actionMenuCount = $(this).attr('data-menu-type');
    FgSidebar.processDynamicMenuDisplay($(this), dataType, actionMenuCount);
});

// FIX FOR FAIR-692
$("body").on('click', "#fg-dev-topnav-contact-active, #fgdev-topnav-club, #fg-dev-topnav-document-club, #fg-dev-topnav-document-team, #fg-dev-topnav-document-workgroup, #fg-dev-topnav-document-contact, #fg-dev-topnav-sponsor-active, #fg-dev-topnav-sponsor-archived", function () {
    var type = $(this).attr('data-type');
    var module = $(this).attr('data-module');
    localStorage.setItem(fgLocalStorageNames.contact.active.functionshowVar, '');
    if (type !== '' && type !== 'undefined') {
        var filterStorageName = fgLocalStorageNames[module][type]['filterStorage'];
        var sidebarActiveMenuName = fgLocalStorageNames[module][type]['sidebarActiveMenu'];
        var sidebarActiveSubMenuName = fgLocalStorageNames[module][type]['sidebarActiveSubMenu'];
        /* New - For pages simplilar to sponsor list - different types of list */
        var activeListDetails = fgLocalStorageNames[module][type]['listDetails'];
    }

    if (typeof sidebarActiveMenuName !== 'undefined') {
        localStorage.removeItem(sidebarActiveMenuName);
    }
    if (typeof sidebarActiveSubMenuName !== 'undefined') {
        localStorage.removeItem(sidebarActiveSubMenuName);
    }
    if (typeof filterStorageName !== 'undefined') {
        localStorage.removeItem(filterStorageName);
    }
    if (typeof ActiveMenuDetVar !== 'undefined') {
        localStorage.removeItem(ActiveMenuDetVar);
    }
    /* New - For pages simplilar to sponsor list - different types of list */
    if (typeof activeListDetails !== 'undefined') {
        localStorage.removeItem(activeListDetails);
    }
});
//FIX FOR FAIR-692
/* Handle sponsor assignments count click from different areas. On clicking redirect to service assignments listing with correspoding menu selected */
FgSponsor = {
    handlesidebarclick: function (_this, returnUrl, contactid, clubid, source)
    {
        localStorage.removeItem(fgLocalStorageNames.sponsor.active.filterStorage);
        localStorage.removeItem("filterdisplayflag_sponsor" + clubid + '-' + contactid);
        var subCatId;
        var catId = '';
        var parentli;
        var subparentli;
        var submenuli;
        var type = _this.type;
        var servicetype = $(_this).attr('service_type');
        var listDetails = {type: 'sponsor'};
        if (type == 'service') {
            subCatId = $(_this).attr('service_id');
            catId = $(_this).attr('catid');
            if (source == 'bookmark') {
                parentli = 'bookmark_li';
                submenuli = 'bookmark_li_service_li_' + catId + '_' + subCatId;
            } else {
                parentli = 'services_li_' + catId;
                subparentli = 'services_li';
                submenuli = 'services_li_' + catId + '_' + subCatId;
            }
            listDetails = {type: 'service', id: subCatId, serviceType: servicetype};
            localStorage.setItem(fgLocalStorageNames.sponsor.active.serviceTab, 'activeservice');
        } else if (type == 'allActive') {
            if (source == 'bookmark') {
                parentli = 'bookmark_li';
                submenuli = type;
            }
        } else if (type == "overview") {
            if (source == 'bookmark') {
                parentli = 'bookmark_li';
                submenuli = 'li_' + $(_this).attr('bookmarkType');
            }
            listDetails = {type: 'overview', id: $(_this).attr('bookmarkType'), overviewType: 'overview'};

        } else {
            subCatId = type;
            catId = 'type';
            var sponsorFilterName = 'sponsor_filter';
            var exportData = {};
            exportData[sponsorFilterName] = {};
            exportData[sponsorFilterName][0] = {};
            exportData[sponsorFilterName][0]['disabled'] = true;
            exportData[sponsorFilterName][0]['connector'] = null;
            exportData[sponsorFilterName][0]['type'] = 'CO';
            exportData[sponsorFilterName][0]['data_type'] = 'select';
            if (type == 'single_person' || type == 'company') {
                exportData[sponsorFilterName][0]['entry'] = 'contact_type';
            } else {
                exportData[sponsorFilterName][0]['entry'] = 'sponsor';
            }
            exportData[sponsorFilterName][0]['condition'] = "is";
            exportData[sponsorFilterName][0]['input1'] = subCatId;
            if (source == 'bookmark') {
                parentli = 'bookmark_li';
                submenuli = 'bookmark_li_' + subCatId + '_li_' + subCatId;
            } else {
                parentli = 'contact_li_' + catId;
                subparentli = 'contact_li';
                submenuli = 'contact_li_' + catId + '_' + subCatId;
            }

            filterExportData = JSON.stringify(exportData);
            localStorage.setItem(fgLocalStorageNames.sponsor.active.filterStorage, filterExportData);
            localStorage.setItem("filterdisplayflag_sponsor" + clubid + '-' + contactid, 0);
        }
        localStorage.setItem(fgLocalStorageNames.sponsor.active.listDetails, JSON.stringify(listDetails));

        localStorage.removeItem(fgLocalStorageNames.sponsor.active.sidebarActiveMenu);
        localStorage.removeItem(fgLocalStorageNames.sponsor.active.sidebarActiveSubMenu);
        if (source == 'bookmark') {
            localStorage.setItem(fgLocalStorageNames.sponsor.active.sidebarActiveMenu, parentli);
        }
        else {
            localStorage.setItem(fgLocalStorageNames.sponsor.active.sidebarActiveMenu, parentli + ',' + subparentli);
        }
        localStorage.setItem(fgLocalStorageNames.sponsor.active.sidebarActiveSubMenu, submenuli);

        window.location = returnUrl;
    }

}
/* Functions which can be used in dataTables */
FgDataTableUtil = {
    /* Get total of a column*/
    getColumnTotal: function (data, column) {
        var columnValues = _.pluck(data, column);
        var total = 0;
        _.each(columnValues, function (value) {
            value = parseFloat(value);
            total += (!isNaN(value) ? value : 0);
        });
        return total;
    },
    /* Get date time for sorting*/
    getDateTime: function (dateValue) {
        var dateTimestamp = moment(dateValue, FgLocaleSettingsData.momentDateFormat).format('x');
        var date = new Date(parseInt(dateTimestamp));
        return date.getTime();
    },
}

/* For loading header menu in layout */
FgHeader = {
    init: function (path, data) {
        var module = data.module;
        $.ajax({
            type: "POST",
            url: path,
            data: data,
            async: true,
            success: function (data) {
                //console.log(data);
                var template2 = data.template2;
                var template1 = $("<div/>").html(data.template1).text();
                template2 = $("<div/>").html(template2).text();
                $(template1).insertBefore($('div.page-container'));
                $('div.page-container').prepend(template2);
                setTimeout(function () {
                    Breadcrumb.load(extraBreadcrumbTitle);
                    FgTopNavigation.init();
                    FgTopNavigation.search(dataTopNavigationSearch);
                    $('.page-header .dropdown-toggle').dropdownHover();
                    FgHeader.handlebootstrapDropdownOnHover();
                    if (!$('body').hasClass('page-sidebar-fixed')) {
                        FgHeader.handleResponsiveTopnavClick();
                    }
                    window.sidebarTopnavLoaded = window.sidebarTopnavLoaded + 1;
                    if (window.sidebarTopnavLoaded == 2) {
                        if ((module == 'club') || (module == 'document')) {
                            FgCountUpdate.updateSidebarAllactive('add', parseInt($('ul.dropdown-menu li.fg-dev-header-nav-active a span.badge').text(), 10));
                        }
                    }

                }, 10);
            }
        });
    },
    // Handle bootsrap dropdown hover in frontend top nav switching
    handlebootstrapDropdownOnHover: function () {
        $("#fg-top-nav-app-switch").bootstrapDropdownOnHover({
            mouseOutDelay: 50
        });
        $(".hover-enabled").bootstrapDropdownOnHover({
            responsiveThreshold: 768
        });
    },
    //* END:CORE HANDLERS *//

    handleResponsiveTopnavClick: function () {
        var viewport = Metronic.getViewPort();

        $('.page-sidebar').off('click', 'li > a'); // prevent parent event bind
        jQuery('.page-sidebar').on('click', 'li > a:not(".filterCount")', function (e) {
            if ($(this).next().hasClass('sub-menu') == false) {
                if ($('.btn-navbar').hasClass('collapsed') == false) {
                    $('.btn-navbar').click();
                }
                return;
            }

            if ($(this).next().hasClass('sub-menu always-open')) {
                return;
            }

            var parent = $(this).parent().parent();
            var the = $(this);
            var menu = $('.page-sidebar-menu');
            var menu2 = $('.side_main_menu');
            var sub = jQuery(this).next();

            var autoScroll = menu.data("auto-scroll") ? menu.data("auto-scroll") : true;
            var slideSpeed = menu.data("slide-speed") ? parseInt(menu.data("slide-speed")) : 200;

            var slideOffeset = -200;

            if (sub.is(":visible")) {

                sub.slideUp(slideSpeed, function () {
                    jQuery(this).parent().find('.arrow').first().removeClass("open");
                    if (autoScroll == true && $('body').hasClass('page-sidebar-closed') == false) {
                        if ($('body').hasClass('page-sidebar-fixed')) {
                            //menu.slimScroll({'scrollTo': (the.position()).top});
                            if (viewport.width >= 992) {
                                menu2.slimScroll();
                            }

                        } else {
                            Metronic.scrollTo(the, slideOffeset);
                        }
                    }
                    if ($(this).parent())
                        $(this).parent().removeClass("open");
                    Layout.fixContentHeight();
                });
            } else {

                sub.slideDown(slideSpeed, function () {
                    jQuery(this).parent().find('.arrow').first().addClass("open");
                    if (autoScroll == true && $('body').hasClass('page-sidebar-closed') == false) {
                        if ($('body').hasClass('page-sidebar-fixed')) {
                            //menu.slimScroll({'scrollTo': (the.position()).top});
                            if (viewport.width >= 992) {
                                menu2.slimScroll();
                            }
                        } else {
                            Metronic.scrollTo(the, slideOffeset);
                        }
                    }
                    if ($(this).parent())
                        $(this).parent().addClass("open");
                    Layout.fixContentHeight();
                });
            }
            e.preventDefault();
        });
    }
}
FgStickySaveBar = {
    init: function (tabPage) {
        if ($("body").find(".backend-sticy-area").length > 0) {
            if (tabPage == 0)
            {
                $(document).ajaxComplete(function () {
                    var page_content_style = $(".page-content").attr('style');
                    $(".page-content").css({'min-height': 0});
                    var contentHeight = $(".page-content").height() + 100;
                    var windowHeight = $(window).height();
                    if (contentHeight > windowHeight)
                    {
                        $('.backend-sticy-area').addClass('fg-sticky-block');
                        $('body').addClass('fg-sticky-save-area');
                    }
                    else
                    {
                        $('.backend-sticy-area').removeClass('fg-sticky-block');
                        $('body').removeClass('fg-sticky-save-area');
                    }
                    $(".page-content").attr('style', page_content_style);
                });
            }
            else
            {
                var contentHeight = (tabPage == 2) ? $('#fg_field_category_137').height() + $('#fg_field_category_' + tabPage).height() : $('#fg_field_category_' + tabPage).height();
                contentHeight = contentHeight + 300;
                var windowHeight = $(window).height();
                if (contentHeight > windowHeight)
                {
                    $('.backend-sticy-area').addClass('fg-sticky-block');
                    $('body').addClass('fg-sticky-save-area');
                }
                else
                {
                    $('.backend-sticy-area').removeClass('fg-sticky-block');
                    $('body').removeClass('fg-sticky-save-area');
                }
            }
        }
    }
};
// prevent error/warning dialogue pop up
$.fn.dataTable.ext.errMode = 'none';
/* Redirect to login page if session is expired */
FgAjaxError = {
    init: function() {
        $(document).ajaxError(function (event, jqXHR) {
            if (jqXHR.status === 403) {
                window.location.reload();
            }
        });
    }
};
// This function is used to handle reactivate a contact from overview pages. 
// It also handles the merging functionalities
$("body").on('click', "#fg-dev-reactivate", function () {
    $.get(reactivateOverviewPopup, {'selcontactIds': reactivateOerviewContactId }, function (data) {
        $('#popup_contents').html(data);
        $('#popup').modal('show');
        $("#reactivate").on('click', function () {
            $('form#archivecontacts .help-block').remove();
            $('form#archivecontacts .has-error').removeClass('has-error');
            if($('#fedmembership').length > 0) {
                fedMembershipVal = $('#fedmembership').val();
                if(fedMembershipVal == ''){
                    $('form#archivecontacts select#fedmembership').parent().addClass('has-error');
                    $('<span class="help-block fg-marg-top-5">required</span>').insertAfter($('form#archivecontacts select#fedmembership + .btn-group.bootstrap-select'));
                    return false;
                }
            } else {
                fedMembershipVal = false;
            }
            contactid = reactivateOerviewContactId;
            assignmentJsonAr = {'selcontactIds': contactid, 'contactType': 'overview', 'fedMembershipVal': fedMembershipVal};
            $('#popup').modal('hide');
            $.get(reactivateOverviewPath, {'archivedData': assignmentJsonAr}, function (response) {
                if (response.status != 'MERGE') {
                    $('#fg-dev-reactivate').hide();
                    FgUtility.showToastr(response.flash, 'success');   
                    document.location = document.location.href;
                } else {
                     if(response.mergeable){
                        response.pageTpe ='overview';
                        FgMergePopup.handleMergerablePopup(response);
                    }
                }
            });
        });
    });
});
//handle modal pop up over lay disappear issue on click ( outer area or close button )


$(document).on('hide.bs.modal','.fg-membership-merge-modal', function () {
  $('.modal-backdrop').remove();
});



/*
 * This class will be used to clear the localstorages on delete
 * FgClearInvalidLocalStorageDataOnDelete.clear() should be called in the callback of the deletion function
 * The main function is clear(response)
 *      response will have the following properties
 *          type (mandatory) => The type will be created in the serverside code, it helps to identify what type of data is deleted   
 *          catid (mandatory) => The category if the category/category settings that is been updated
 *          deleledIds (optional) => The ids that were deleted
 *              role (optional)     => The roles that was deleted
 *              function (optional) => The functions that was deleted
 *              category (optional) => Thecategory that was deleted
 *              
 * */

FgClearInvalidLocalStorageDataOnDelete = {
    clear: function (response){
        filterStorage = JSON.parse(localStorage.getItem(fgLocalStorageNames.contact.active.filterStorage));
        if(filterStorage == null)
            return;

        var type = response.result.type;
        var catId = response.result.catid;   
        var deletedRoleIds = (response.result.deleledIds != null)?response.result.deleledIds.role:'';
        var deletedFunctionIds = (response.result.deleledIds != null)?response.result.deleledIds.function:'';
        var deletedCategoryIds = (response.result.deleledIds != null)?response.result.deleledIds.category:'';
        var clubId = fgLocalStorageNames.club.id;

        switch(type){
            case 'executiveboard':
                if(typeof deletedFunctionIds == 'object'){
                    _.each(deletedFunctionIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForSettings(filterStorage.contact_filter, ['FI'], 'ceb_function',id, '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('FI', id, 'FI_li', 'FI_li_'+id);
                        }
                    });
                }
                break;
            case 'team':
                if(typeof deletedRoleIds == 'object'){
                    _.each(deletedRoleIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForSettings(filterStorage.contact_filter, ['TEAM'], catId, id, '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('TEAM', id, 'TEAM_li_'+catId, 'TEAM_li_'+catId+'_'+id);
                        }
                    });
                }
                break;
            case 'workgroup':
                if(typeof deletedRoleIds == 'object'){
                    _.each(deletedRoleIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForSettings(filterStorage.contact_filter, ['WORKGROUP'], catId, id, '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('WORKGROUP', id, 'WORKGROUP_li_'+catId, 'WORKGROUP_li_'+catId+'_'+id);
                        }
                    });
                }
                break;
            case 'role':
                if(typeof deletedRoleIds == 'object'){
                    _.each(deletedRoleIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForSettings(filterStorage.contact_filter, ['ROLES-'+clubId, 'FROLES-'+clubId], catId, id, '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('ROLES-'+clubId, id, 'ROLES-'+clubId+'_li_'+catId+','+'ROLES-'+clubId+'_li', 'ROLES-'+clubId+'_li_'+catId+'_'+id);

                            //since we dont know whether federation role is deleted, so we delete the federation too in both cases
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('FROLES-'+clubId, id, 'FROLES-'+clubId+'_li_'+catId+','+'FROLES-'+clubId+'_li', 'FROLES-'+clubId+'_li_'+catId+'_'+id);
                        }
                    });
                }

                if(typeof deletedFunctionIds == 'object'){
                    _.each(deletedFunctionIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForSettings(filterStorage.contact_filter, ['ROLES-'+clubId, 'FROLES-'+clubId], catId, '', id);
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('ROLES-'+clubId, id, 'ROLES-'+clubId+'_li_'+catId+','+'ROLES-'+clubId+'_li', 'ROLES-'+clubId+'_li_'+catId+'_'+id);

                            //since we dont know whether federation role is deleted, so we delete the federation too in both cases
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('FROLES-'+clubId, id, 'FROLES-'+clubId+'_li_'+catId+','+'FROLES-'+clubId+'_li', 'FROLES-'+clubId+'_li_'+catId+'_'+id);
                        }
                    });
                }
                break;   
            case 'membership':
                if(typeof deletedCategoryIds == 'object'){
                    _.each(deletedCategoryIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForCategory(filterStorage.contact_filter, ['FM'], 'fed_membership', id, '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('ROLES', id, 'CONTACT_li_fed_membership,CONTACT_li', 'CONTACT_li_fed_membership_'+id);
                        }
                        
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForCategory(filterStorage.contact_filter, ['CM'], 'membership', id, '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('ROLES', id, 'CONTACT_li_membership,CONTACT_li', 'CONTACT_li_membership_'+id);
                        }
                    });
                }
                break;  
            case 'category-club':
                if(typeof deletedCategoryIds == 'object'){
                    _.each(deletedCategoryIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForCategory(filterStorage.contact_filter, ['ROLES-'+clubId], id, '', '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('ROLES-'+clubId, id, 'ROLES-'+clubId+'_li_'+id+',ROLES-'+clubId+'_li', '');
                        }
                    });
                }
                break;
            case 'category-fed_cat':
                if(typeof deletedCategoryIds == 'object'){
                    _.each(deletedCategoryIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForCategory(filterStorage.contact_filter, ['FROLES-'+clubId], id, '', '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('FROLES-'+clubId, id, 'FROLES-'+clubId+'_li_'+id+',FROLES-'+clubId+'_li', '');
                        }
                    });
                }
                break; 
            case 'filterrole':
                if(typeof deletedRoleIds == 'object'){
                    _.each(deletedRoleIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForSettings(filterStorage.contact_filter, ['FILTERROLES-'+clubId], catId, id, '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('FILTERROLES-'+clubId, id, 'FILTERROLES-'+clubId+'_li_'+catId+',FILTERROLES-'+clubId+'_li', 'FILTERROLES-'+clubId+'_li_'+catId+'_'+id+'');
                        }
                    });
                }
                break;
             case 'category-filter_role':
                if(typeof deletedCategoryIds == 'object'){
                    _.each(deletedCategoryIds, function(id){ 
                        var result = FgClearInvalidLocalStorageDataOnDelete.deleteKeyForCategory(filterStorage.contact_filter, ['FILTERROLES-'+clubId], id, '', '');
                        if(result.found == true){
                            FgClearInvalidLocalStorageDataOnDelete.clearActiveMenu('FILTERROLES-'+clubId, id, 'FILTERROLES-'+clubId+'_li_'+id+',FILTERROLES-'+clubId+'_li','');
                        }
                    });
                }
                break;    
        }
    },

    /*
     * This function is used to clear the contact filter localstorage
     * This function is similar to deleteKeyForCategory(), with the only change that this function will check the 'input1' field too
     * The selection between these two fucntion is entirely depending on the structure of local storage
     */
    deleteKeyForSettings: function(contactFilterData, indexList, entry, input1, input2){
        var returnArray = {};
        returnArray.found = false;
        _.find(contactFilterData, function(obj, key){
            if( ($.inArray(obj.type, indexList) > -1) && obj.entry == entry){
                if( (input1 != '' && obj.input1 == input1) || (input2 != '' && obj.input2 == input2) ) {
                    returnArray.found = true;
                    
                    //remove the contact filter local storage
                    localStorage.removeItem(fgLocalStorageNames.contact.active.filterStorage)
                }
            }
            return;
        });
        returnArray.contactFilterData = contactFilterData;
        return returnArray;
    },

     /*
     * This function is used to clear the contact filter localstorage
     * This function is similar to deleteKeyForSettings(), with the only change that the former will check the 'input1' field too
     * The selection between these two fucntion is entirely depending on the structure of local storage
     */
    deleteKeyForCategory: function(contactFilterData, indexList, entry, input1, input2){
        var returnArray = {};
        returnArray.found = false;
        _.find(contactFilterData, function(obj, key){
            if( ($.inArray(obj.type, indexList) > -1) && obj.entry == entry){
                returnArray.found = true;
                returnArray.input1 = obj.input1;
                
                //remove the contact filter local storage
                localStorage.removeItem(fgLocalStorageNames.contact.active.filterStorage)
            }
            return;
        });
        return returnArray;
    },

    /*
     * This function will clear the active menu details from the localstorage for both category and settings
     * The parameters is the value in the localstorages of   (sidebarActiveMenu,sidebarActiveSubMenu,ActiveMenuDetVar)
     */
    clearActiveMenu: function(type, id, activeMenuId, activeSubmenuId){
        sidebarActiveMenu = localStorage.getItem(fgLocalStorageNames.contact.active.sidebarActiveMenu);
        sidebarActiveSubMenu = localStorage.getItem(fgLocalStorageNames.contact.active.sidebarActiveSubMenu);
        ActiveMenuDetVar = JSON.parse(localStorage.getItem(fgLocalStorageNames.contact.active.ActiveMenuDetVar));

        if(ActiveMenuDetVar.type == type && ActiveMenuDetVar.id == id){
            ActiveMenuDetVar = {"type":"allActive"};
        }

        if(sidebarActiveMenu == activeMenuId || sidebarActiveSubMenu == activeSubmenuId){
            sidebarActiveMenu = 'bookmark_li';
            sidebarActiveSubMenu = 'allActive';
            ActiveMenuDetVar = {"type":"allActive"};
        } 

        localStorage.setItem(fgLocalStorageNames.contact.active.sidebarActiveMenu, sidebarActiveMenu);
        localStorage.setItem(fgLocalStorageNames.contact.active.sidebarActiveSubMenu, sidebarActiveSubMenu); 
        localStorage.setItem(fgLocalStorageNames.contact.active.ActiveMenuDetVar, JSON.stringify(ActiveMenuDetVar));
    },

    /*
     * This function will clear the active menu details from the localstorage for saved filters
     * The parameters id is the id of the saved filter
     * This function is directly called from the page
     */
    clearActiveMenuForSavedFilters: function(id){
        sidebarActiveMenu = localStorage.getItem(fgLocalStorageNames.contact.active.sidebarActiveMenu);
        sidebarActiveSubMenu = localStorage.getItem(fgLocalStorageNames.contact.active.sidebarActiveSubMenu);
        ActiveMenuDetVar = JSON.parse(localStorage.getItem(fgLocalStorageNames.contact.active.ActiveMenuDetVar));

        if(sidebarActiveSubMenu == 'filter_li_'+id){
            sidebarActiveMenu = 'bookmark_li';
            sidebarActiveSubMenu = 'allActive';
            ActiveMenuDetVar = {"type":"allActive"};
            
            //remove the contact filter local storage
            localStorage.removeItem(fgLocalStorageNames.contact.active.filterStorage)
        }

        localStorage.setItem(fgLocalStorageNames.contact.active.sidebarActiveMenu, sidebarActiveMenu);
        localStorage.setItem(fgLocalStorageNames.contact.active.sidebarActiveSubMenu, sidebarActiveSubMenu); 
        localStorage.setItem(fgLocalStorageNames.contact.active.ActiveMenuDetVar, JSON.stringify(ActiveMenuDetVar));
    }
};

 function confirmOrDiscardCallbackApplication(updatedCount) {
        FgPageTitlebar.setMoreTab();
        var actionMenuText = {'active' : {'none': actionMenuNoneSelectedText, 'single': actionMenuSingleSelectedText, 'multiple': actionMenuMultipleSelectedText}};
        FgSidebar.dynamicMenus.push({actionmenu: actionMenuText});
        Breadcrumb.load([]);
        $('#fg_tab_0 a span.badge').html(updatedCount);
        var navigationBadgeId = '#fg-dev-topnav-confirmfedmemberships-count';
        var navBadgeCount = $(navigationBadgeId).html();
        navBadgeCount = ((navBadgeCount - updatedCount) < 0) ? 0 : (navBadgeCount - updatedCount);
        $(navigationBadgeId).html(navBadgeCount); 
        var fedmembershipTopNavCount = $('#fg-dev-topnav-confirmclubassignment-count').html();
        var totalConfirmCount = (parseInt(navBadgeCount) + parseInt(fedmembershipTopNavCount));
        if(totalConfirmCount == 0){
            $('.fg-dev-application-warning').hide();
        }

    }
    /**
     * To set delay for a function.
     * Cretad for table search optimaization #FAIR-2199
     */
    var setDelay = (function(){
        var timer = 0;
        return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
        };
      })();