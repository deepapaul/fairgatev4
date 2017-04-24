$(document).ready(function() {
    var funData = jQuery.parseJSON(pageVars.funtionData);
    var functionData = FgUtility.groupByMulti(funData, ['fn_sortOrder', 'fn_id', 'fn_lang']);
    //console.log(funData);
    var jsonData = {clubLanguages: pageVars.clubLanguages, isNew: false, catId: pageVars.catId, roleId: pageVars.roleId, change_activation: false};
    _.each(functionData, function(function_details, fn_sort_order) {
        _.each(function_details, function(function_detail, functionId) {
            if ((functionId != null) && (functionId != 'null')) {
                jsonData['fromPage'] = 'execboardfunction';
                jsonData['functionId'] = functionId;
                jsonData['function_data'] = function_detail;
                execBoardSettings.renderNewRow('template-function-add', 'execboardfunctions_sort', jsonData, false);
            }
        });
    });
    execBoardSettings.initPageFunctions();
    execBoardSettings.initPageEvents();
    pageVars.initialHtml = $('form#execboardfunctionsettings').html();
});

var execBoardSettings = {
    renderNewRow: function(templateScriptId, parentDivId, jsonData, isNew) {
        var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
        $('#' + parentDivId).append(htmlFinal);
        $('#' + parentDivId).find('.addednew').slideDown('250','easeInQuart');
        FgDragAndDrop.sortWithOrderUpdation('#execboardfunctions_sort', false);
        FgUtility.showTranslation(pageVars.selectedLang);
        FgUtility.resetSortOrder($('#execboardfunctions_sort'));
        execBoardSettings.checkFunctionsDeletable();
        if (isNew) {
            FgDirtyFields.addFields(htmlFinal);
           // FgDirtyFields.enableSaveDiscardButtons();
           FgFormTools.handleUniform();
        }
          setTimeout(function () {
                FgTooltip.init()
            },100);
    },
    checkFunctionsDeletable: function() {
        if (($('input[data-deletable=checknew]:not(:checked)').length == 1) && ($('.closeico .fa.fa-lock.fa-2x.ash').length == 0)) {
            $($('input[data-deletable=checknew]:not(:checked)').parent()).addClass('hide');
            if ($($('input[data-deletable=checknew]:not(:checked)').parent().parent().find('div#disableddelete')).length == 0) {
                $($('input[data-deletable=checknew]:not(:checked)').parent().parent()).append('<div class="col-md-2 deletediv work-grp-lock" id="disableddelete"> <i class="fa fa-lock fa-2x ash"></i> </div>');
            }
        } else {
            $($('input[data-deletable=checknew]:not(:checked)').parent()).removeClass('hide');
            $($('input[data-deletable=checknew]:not(:checked)').parent().parent().find('div#disableddelete')).remove();
        }
    },
    initPageFunctions: function() {
        execBoardSettings.checkFunctionsDeletable();
        FgApp.init();
        FgDragAndDrop.sortWithOrderUpdation('#execboardfunctions_sort', false);
        FgUtility.displayDetailsOnClick();
        FgFormTools.handleUniform();
        // For resetting the sorting changes done in the page on 'discard_changes'
        var initialOrderArray = FgUtility.getOrderOfChildElements('#execboardfunctions_sort');
        var resetSections = {
            '0': {
                'parentElement': '#execboardfunctions_sort',
                'initialOrder': initialOrderArray,
                'addClass': true,
                'className': 'blkareadiv'
            }
        };
        FgResetChanges.init(resetSections);
        FormValidation.init('execboardfunctionsettings', 'saveChanges', 'errorHandler');
        FgDirtyFields.init('execboardfunctionsettings', { enableDiscardChanges: false, setNewFieldsClean:true });
        FgPageTitlebar.checkMissingTranslation(defaultLang);
    },
    initPageEvents: function() {
        execBoardSettings.switchLanguage();
        execBoardSettings.addNewRow();
        execBoardSettings.deleteNewRow();
        execBoardSettings.displayLogs();
        rowFunctions.showLogFilter();
        execBoardSettings.resetChanges();
    },
    switchLanguage: function() {
        $('form').on('click', 'button[data-elem-function=switch_lang]', function() {
            selectedLang = $(this).attr('data-selected-lang');
            FgUtility.showTranslation(selectedLang);
            pageVars.selectedLang = selectedLang;
            setTimeout(function () {
                FgTooltip.init()
            },100);
        });
    },
    addNewRow: function() {
        $('form').on('click', '#addrow', function() {
            var randomVar = $.now().toString();
            var jsonData = {clubLanguages: pageVars.clubLanguages, catId: pageVars.catId, roleId: pageVars.roleId, isNew: true, fromPage: 'execboardfunction', functionId: randomVar, change_activation: false};
            execBoardSettings.renderNewRow('template-function-add', 'execboardfunctions_sort', jsonData, true);
        });
    },
    deleteNewRow: function() {
        $('form').on('click', 'input[data-deletable=checknew]', function() {
            var elementId = $(this).attr('id');
            execBoardSettings.checkFunctionsDeletable();
            if (elementId.indexOf('_new_') != -1) {
                var parentId = $(this).attr('data-parentid');
                 FgDirtyFields.removeFields($('#' + parentId));
                $('#' + parentId).remove();
                FgUtility.resetSortOrder($('#execboardfunctions_sort'));
                if ($('div.addednew').length < 1) {
                    FgDirtyFields.updateFormState();
                }
                return false;
            }
        });
    },
    displayLogs: function() {
        $('form').on('click', '.log_role i', function() {
            var id = $(this).parent('div').attr('id');
            rowFunctions.logdisplay(id, 'log_role');
        });

        $('form').on('click', '.log_fun i', function() {
            var id = $(this).parent('div').attr('id');
            rowFunctions.logdisplay(id, 'log_fun');
        });
    },
    resetChanges: function() {
        $('form').on('click', '#reset_changes', function() {
            $('form#execboardfunctionsettings').html(pageVars.initialHtml);
            FgDirtyFields.init('execboardfunctionsettings', { enableDiscardChanges: false, setNewFieldsClean:true });
            FgDragAndDrop.sortWithOrderUpdation('#execboardfunctions_sort', false);
        });
    }
};

function saveChanges() {
    $('div.addednew input').addClass('fairgatedirty');
    var objectGraph = {};
    //parse the all form field value as json array and assign that value to the array
    objectGraph=  FgParseFormField.fieldParse();
    var catArr = JSON.stringify(objectGraph);

    FgXmlHttp.post(pageVars.savePath, { 'catArr': catArr, 'type' :'executiveboard'} , false, saveCallBack);
}

function saveCallBack(response){
    FgClearInvalidLocalStorageDataOnDelete.clear(response);
    execBoardSettings.initPageFunctions();
}

function errorHandler() {
    FgUtility.showTranslation(defaultLang);
}
function doSortOrderUpdation(parentElement) {
    FgUtility.resetSortOrder(parentElement);
    FgDirtyFields.updateFormState();
}
