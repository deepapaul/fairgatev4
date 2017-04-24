$(function() {

    // Initiate common functions on loading page for the first time.
    langButtonInit();
    FgUtility.changeColorOnDelete();

    /* Get json data for listing membership */
    $('div[data-list-wrap]').rowList({
        template: '#membershipList',
        jsondataUrl: membershipPageVars.pathMembershipData,
        fieldSort: '.sortables',
        submit: ['#save_changes', 'membershiplist'],
        reset: '#reset_changes',
        searchfilterData: {},
        useDirtyFields: true,
        dirtyFieldsConfig: { enableDiscardChanges : false, enableDragDrop: false, enableUpdateSortOrder: false },
        addData: ['#addrow', {
            isAllActive: false,
            isNew: true
        }, 'filter'],
        loadTemplate:[{
            btn:'#addrow',
            template:'#template_membership_add'
        }],
        validate: true,
        postURL: membershipPageVars.saveAction,
        validateFilterCriteria: false,
        success: function() {
        },
        load: function() {
             FgTooltip.init();
        },
        initCallback: function() {
             FgTooltip.init();
             FgPageTitlebar.checkMissingTranslation(membershipPageVars.defaultLang);
        },
        rowCallback: function() {
            membershipList.addRowCallback();
             FgTooltip.init();
        }
    });

    rowFunctions.showLogFilter();
    FormValidation.init('membershiplist', 'saveMembershipChange', 'errorHandler');
    membershipList.resetChangesCallback();
    FgTooltip.init();

});

/* Save Membership */
function saveMembershipChange() {
    if($(".closeico").find('input[type=checkbox]:checked').length > 0) {  // if any field is deleted confirm before save
        $('#save_changes').attr("data-toggle","confirmation");
        $('#save_changes').parent().removeClass("fg-confirm-btn").addClass("fg-confirm-btn");
        FgConfirmation.confirm(membershipPageVars.confirmNote, membershipPageVars.cancelLabel, membershipPageVars.saveLabel, $('#save_changes'), membershipList.saveField, {});
    } else {
        membershipList.saveField();
    }
}
/* Error handler function */
function errorHandler() {
    langButton(membershipPageVars.defaultLang);
}

var membershipList = {
    // Functions to do after loading membership list.
    initPageFunctions: function() {
        setTimeout(function(){
            langButton(membershipPageVars.defaultLang);
            FgInputTag.handleUniform();
        }, 1);

        _dynamicFunction.openLog = function(elem) {
            rowFunctions.logdisplay(elem.id + '_membership', 'membership');
        };
    },
    // Functions to do after adding new row.
    addRowCallback: function() {
        FgUtility.showTranslation(membershipPageVars.selectedLang);
        FgInputTag.handleUniform();
    },
    // Functions to save memberships.
    saveField: function() {
        //parse the all form field value as json array and assign that value to the array
        var objectGraph = FgParseFormField.fieldParse();
        var catArr = JSON.stringify(objectGraph);
        FgXmlHttp.post(membershipPageVars.saveAction, {'catArr': catArr}, false, membershipList.saveCallback);
    },
    // Functions to do after saving membership.
    saveCallback: function(response) {
        FgClearInvalidLocalStorageDataOnDelete.clear(response);
        $.getJSON(membershipPageVars.membershipCatCountPath, function (clubMemCatCnt) {
            membershipPageVars.clubMemCatCnt = clubMemCatCnt;
            customFunctions.getData();
        });
    },
    // Functions to do after resetting dirty elements.
    resetChangesCallback: function() {
        $('form').on('click', '#reset_changes', function() {
            setTimeout(function(){
                langButton(membershipPageVars.defaultLang);
                FgTooltip.init();
            }, 1);
        });
    }
};
