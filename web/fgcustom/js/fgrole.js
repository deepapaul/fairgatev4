/* * GLOBAL JAVASCRIPT FILE FOR FAIRGATE PORTAL SOLUTION 4.0 ROLE LISTING PAGES * */
$(function () {
    /* function to show data in different languages on switching language */
    $('form').on('click', 'button[data-elem-function=switch_lang]', function () {
        selectedLang = $(this).attr('data-selected-lang');
        pageVars.selectedLang = selectedLang;
        FgUtility.showTranslation(selectedLang);
        if (pageVars.role_section == 'role') {
            if (pageVars.function_assign == 'individual') {
                rolePageSettings.showRoleTitles();
            }
        }
    });
    /* function to remove newly added row on clicking delete button */
    $('form').on('click', 'input[data-deletable=checknew]', function () {
        var elementId = $(this).attr('id');
        if (elementId.indexOf('_new_') != -1) {
            var parentId = $(this).attr('data-parentid');
            FgDirtyFields.removeFields($('#' + parentId));
            var parentDivId = $($('#' + parentId).parent()).attr('id');
            $('#' + parentId).remove();
            $('div[data-rl-id=' + parentId + ']').remove();
            FgUtility.resetSortOrder($('#' + parentDivId));
            hideDivHavingNoRows();
            if (pageVars.role_section == 'workgroup') {
                insertBorderLine();
            }
            if ($('form[skipDirtyCheck]').length > 0) {
                if ($('div.addednew').length < 1) {
                    FgDirtyFields.updateFormState();
                }
            }
            return false;
        }
    });
    /* function to add new row */
    $('form').on('click', '#addrow', function () {
        var parentDivId = $(this).attr('data-parentdiv-id');
        var randomVar = $.now().toString();
        var addType = $(this).attr('data-add-type');
        var jsonData = {catId: pageVars.catId, clubLanguages: pageVars.clubLanguages, isNew: true, function_assign: pageVars.function_assign};
        if (addType == 'role') {
            var roleId = randomVar;
            jsonData['roleId'] = roleId;
            jsonData['functionId'] = randomVar;
            jsonData['roleType'] = pageVars.role_section;
            renderNewRow('template-role-add', parentDivId, jsonData, addType, true);
            if (pageVars.role_section == 'role') {
                if (pageVars.function_assign == 'individual') {
                    jsonData['isRoleNew'] = true;
                    renderNewRow('template-function-section-add', 'child_sortrole', jsonData, 'functionsection', true);
                    jsonData['roleId'] = 'newrole' + roleId;
                    renderNewRow('template-function-add', 'functions_' + roleId, jsonData, 'function', true);
                }
            } else {
                jsonData['roleId'] = 'newrole' + roleId;
                renderNewRow('template-function-add', 'functions_' + roleId, jsonData, 'function', true);
                rolePageSettings.addFunctionCreationLink(roleId);
                $($('#functions_' + roleId).parent().parent().find('i[data-showfunction=true]')).trigger('click');
            }
        } else if (addType == 'class') {
            var roleId = randomVar;
            jsonData['roleId'] = roleId;
            jsonData['functionId'] = randomVar;
            jsonData['roleType'] = pageVars.role_section;
            renderNewRow('template-role-add', parentDivId, jsonData, addType, true);
        } else {
            if (pageVars.function_assign == 'individual') {
                var roleId = $(this).attr('data-parent');
                if (roleId.indexOf('newrole') != -1) {
                    jsonData['roleId'] = roleId;
                } else {
                    if ($('input[data-roleid=' + roleId + ']').length) {
                        var roleParentId = $('input[data-roleid=' + roleId + ']').attr('id');
                        jsonData['roleId'] = (roleParentId.indexOf('_new_') != -1) ? ('newrole' + roleId) : roleId;
                    } else {
                        jsonData['roleId'] = roleId;
                    }
                }
            } else {
                jsonData['roleId'] = 0;
            }
            jsonData['functionId'] = randomVar;
            renderNewRow('template-function-add', parentDivId, jsonData, addType, true);
        }
        hideDivHavingNoRows();
    });
    $('form').on('click', '.log_role i', function () {
        var id = $(this).parent('div').attr('id');
        if ($('form[skipDirtyCheck]').length > 0) {
            rowFunctions.logdisplay(id, 'log_role');
        } else {
            logdisplay(id, 'log_role');
        }
    });

    $('form').on('click', '.log_fun i', function () {
        var id = $(this).parent('div').attr('id');
        if ($('form[skipDirtyCheck]').length > 0) {
            rowFunctions.logdisplay(id, 'log_fun');
        } else {
            logdisplay(id, 'log_fun');
        }
    });
});
/* function to load initial data */
function initPageFunctions() {
    if (pageVars.role_section == 'workgroup') {
        insertBorderLine();
    }
    initiateDragAndDrop();
    FgUtility.showTranslation(pageVars.selectedLang);
    if (pageVars.role_section == 'role') {
        $('form input:radio').uniform();
        // $('form input:checkbox').uniform();
        if (pageVars.function_assign == 'individual') {
            rolePageSettings.showRoleTitles(); //to show role titles in each function section
        }
    } else if (pageVars.role_section == 'class') {
        $('form input:radio').uniform();
    }
    FgApp.init();
    hideDivHavingNoRows();
    FgUtility.displayDetailsOnClick();
    // For resetting the changes (add row, sorting) done in the page on 'discard_changes'
    var resetSections = {};
    resetSections[0] = getResetSection('sortrole');
    if (pageVars.function_assign == 'same') {
        resetSections[1] = getResetSection('categoryfunctions');
    } else {
        var i = 0;
        $('input[data-roleid]:visible').each(function () {
            i++;
            resetSections[i] = getResetSection('functions_' + $(this).attr('data-roleid'));
        });
    }
    FgResetChanges.init(resetSections);
    FormValidation.init('categorysettings', 'saveChanges', 'errorHandler');
    FgInputTextValidation.init();
    $('.popovers').popover();
    if ($('form[skipDirtyCheck]').length > 0) {
        FgDirtyFields.init('categorysettings', {enableDiscardChanges: false, setNewFieldsClean: true});
    }
    FgPageTitlebar.checkMissingTranslation(defaultLang);
}
/* function to insert bottom border line */
function insertBorderLine() {
    $('div.row').removeClass('connan-br-btm');
    $($('div[id^=functions_]').find('.row:last')).addClass('connan-br-btm');
    if (pageVars.hasOwnProperty('executiveBoardId')) {
        $('#functions_' + pageVars.executiveBoardId + ' .row:last').removeClass('connan-br-btm');
    }
    $($('div[id^=functions_]:last').find('.row:last')).removeClass('connan-br-btm');
}
/* function to hide placeholder div if no rows present */
function hideDivHavingNoRows() {
    if ($('#sortrole').find('.row').length == 0) {
        $('#sortrole').addClass('hide');
    } else {
        $('#sortrole').removeClass('hide');
    }
    if ($('#categoryfunctions').find('.row').length == 0) {
        $('#categoryfunctions').addClass('hide');
    } else {
        $('#categoryfunctions').removeClass('hide');
    }
    $('div[id^=functions_]').each(function () {
        if ($(this).find('.row').length == 0) {
            $(this).addClass('hide');
        }
    });
}
/* check whether functions are added */
function roleFunctionValidation() {
    var noFunctionError = false;
    if (pageVars.function_assign == 'individual') {
        var roleCnt = 0;
        var noFuncSectionCnt = 0;
        $('#sortrole').children('.row').each(function () {
            if (!$(this).children('.closeico').find('input[data-deletable=checknew]').is(':checked')) {
                roleCnt++;
                var idArray = this.id.split('_role_');
                var roleId = idArray[1];
                roleId = roleId.replace('new_', '');
                if (($('#functions_' + roleId + ' .closeico input[data-deletable=checknew]:not(:checked)').length <= 0) && ($('#functions_' + roleId + ' .closeico i.fa.fa-lock').length <= 0)) {
                    noFuncSectionCnt++;
                    noFunctionError = true;
                    if (pageVars.role_section == 'workgroup') {
                        if ($(this).find('i[data-showfunction=true]').hasClass('fa-plus-square-o')) {
                            $(this).find('i[data-showfunction=true]').trigger('click');
                        }
                    }
                }
            }
        });
        if ((pageVars.role_section == 'role') && (roleCnt == noFuncSectionCnt)) { //for role category, if all functions are deleted, the category should be saved as no-function category
            noFunctionError = false;
        }
    }
    return noFunctionError;
}

function userRightdValidation() {
    validation = 0;
    $('.fg-dev-auto-complete-val').each(function () {
        if ($(this).val() == '') { // Setting validation flag if there is any errors
            validation = 1;
            $(this).siblings().first().addClass("has-error");

        }
    });
    return validation;
}
/* save function */
function saveChanges() {
    $('div.alert-danger').hide();
    $('form').find('has-error').removeClass('has-error');
    var noFunctionError = roleFunctionValidation();
    var userrightsError = userRightdValidation();
    if (noFunctionError) {
        $('div.alert-danger span').html(pageVars.nofunctionerror);
        $('div.alert-danger').show();
        Metronic.scrollTo($('div.alert-danger'), -200);
    } else if (userrightsError) {
        $('div.alert-danger span').html(pageVars.formerror);
        $('div.alert-danger').show();
        Metronic.scrollTo($('div.alert-danger'), -200);
        return false;
    } else {
        $('div.addednew input').addClass('fairgatedirty');
        $('input[data-elem-function=switch_assignment]:checked').addClass('fairgatedirty');
        var objectGraph = {};
        //parse the all form field value as json array and assign that value to the array
        objectGraph = FgParseFormField.fieldParse();
        FgDirtyForm.disableButtons();
        var catArr = JSON.stringify(objectGraph);

        if (pageVars.hasOwnProperty('saveAction')) {
            if (pageVars.role_section == 'workgroup') {
                var element = $('input[type="submit"]#save_changes');
                element.attr('data-toggle', "confirmation");
                element.parent('div').addClass('fg-confirm-btn');
                var catArr = JSON.stringify(objectGraph[pageVars.catId]);
                var userrightsArr = JSON.stringify(objectGraph['teams']);
                element.confirmation('destroy');
                if ($('input[type="checkbox"].fgroledeletebutton').closest('div.inactiveblock').length > 0) {
                    FgConfirmation.confirm(confirmNote, cancelTrans, confirmTrans, element, function () {
                        FgXmlHttp.post(pageVars.saveAction, {'catid': pageVars.catId, 'catArr': catArr, 'type': pageVars.role_section, 'userrightsArr': userrightsArr}, false, callInitAfterSave);
                    }, false, 'manual');
                } else {
                    FgXmlHttp.post(pageVars.saveAction, {'catid': pageVars.catId, 'catArr': catArr, 'type': pageVars.role_section, 'userrightsArr': userrightsArr}, false, callInitAfterSave);
                }
            } else {

                FgXmlHttp.post(pageVars.saveAction, {'catid': pageVars.catId, 'catArr': catArr, 'type': pageVars.role_section, 'function_assign': pageVars.function_assign}, false, callInitAfterSave);


            }
        }
    }
}
function callInitAfterSave(response) {
    FgClearInvalidLocalStorageDataOnDelete.clear(response);
    initPageFunctions();
    $('form input:checkbox').uniform();

}
/* error handler function */
function errorHandler() {
    FgUtility.showTranslation(defaultLang);
    if (pageVars.hasOwnProperty('formerror')) {
        $('div.alert-danger span').html(pageVars.formerror);
    }
}
/* function to form an object containing initial sortorder of elements in a div */
function getResetSection(parentDivId) {
    var initialOrderArray = FgUtility.getOrderOfChildElements('#' + parentDivId);
    var resetSection = {'parentElement': '#' + parentDivId, 'initialOrder': initialOrderArray, 'addClass': true, 'className': 'blkareadiv'};

    return resetSection;
}
/* function to display new row */
function renderNewRow(templateScriptId, parentDivId, jsonData, addType, isNew) {
    var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
    $('#' + parentDivId).append(htmlFinal);
    setTimeout(function () {   // adjust slide timeing effects on newly creatd row [ fixes for jumping issue
        $('#' + parentDivId).find('.addednew').slideDown(250);
    }, 500)


    if (pageVars.role_section == 'workgroup') {
        insertBorderLine();
    }
    initiateDragAndDrop();
    if (addType != 'functionsection') {
        FgUtility.resetSortOrder($('#' + parentDivId));
        if (addType == 'role') {
            FgUtility.resetSortOrder($('#functions_' + jsonData.roleId));
        }
    }
    FgUtility.showTranslation(pageVars.selectedLang);
    if ($('form[skipDirtyCheck]').length > 0) {
        if (isNew) {
            FgDirtyFields.addFields(htmlFinal);
            //FgDirtyFields.enableSaveDiscardButtons();
        }
    }
    FgTooltip.init();
}
/* function to bring lock icon for role */
function setLockForRole()
{
    roles = $('#sortrole').find(".fg-dev-lockhandler");
    _.each(roles, function (roleDetails, index) {
        var rowId = $(roleDetails).attr('id');
        var forumObj = $(roleDetails).find('#forumCount_' + rowId);
        var forumCount = $(forumObj).val();
//        if (forumCount > 0) {
//            $('#'+rowId).find('.fg-role-lock').html('<i class="fa fa-lock fa-2x ash workgroup-lock-icon" data-toggle="tooltip"></i>');
//        } else {
        var fnBlockId = '#displaydetails_' + rowId;
        fnBlockDetails = $('#sortrole').find(fnBlockId + ' .fg-settings-block .fg-dev-function');
        _.each(fnBlockDetails, function (functionDetails, key) {
            var functionBlockId = $(functionDetails).attr('id');
            //console.log(functionBlockId);
            var delIcon = $('#' + functionBlockId).find('.fg-lock-wokgroup-del');
            if (delIcon.length == 1) {
                $('#' + rowId).find('.fg-role-lock').html('<i class="fa fa-lock fa-2x ash workgroup-lock-icon" data-toggle="tooltip"></i>');
                return false;
            }
        });
        //}
    });
    FgTooltip.init();
}