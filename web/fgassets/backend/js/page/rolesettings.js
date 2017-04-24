$(document).ready(function() {
    /* function to disable 'required' checkbox on switching 'allowed' radio button to 'no' */
    $('input[data-elem-function=check_allowed]:checked').each(function(){
        rolePageSettings.checkRequiredAssignment(this);
    });
    /* to display roles and functions */
    $.getJSON(pageVars.roleDataPath, function(data) {
        var role_result = FgUtility.groupByMulti(data, ['rl_sortOrder', 'rl_id', 'rl_lang']);
        var jsonData = {catId: pageVars.catId, clubLanguages: pageVars.clubLanguages, isNew: false};
        _.each(role_result, function(role_data, rl_sort_order) {
            _.each(role_data, function(role_detail, roleId) {
                if ((roleId != null) && (roleId != 'null')) {
                    jsonData['roleId'] = roleId;
                    jsonData['rolecontent_data'] = role_detail;
                    jsonData['roleType'] = 'role';
                    $('#sortrole').removeClass('hide');
                    renderNewRow('template-role-add', 'sortrole', jsonData, 'role', false);
                    if (pageVars.function_assign == 'individual') {
                        jsonData['isRoleNew'] = false;
                        renderNewRow('template-function-section-add', 'child_sortrole', jsonData, 'functionsection', false);
                    }
                }
            });
        });
        delete jsonData["rolecontent_data"];
        var function_result = {};
        if (pageVars.function_assign == 'same') {
            jsonData['roleId'] = 0;
            jsonData['isRoleNew'] = false;
            renderNewRow('template-function-section-add', 'child_sortrole', jsonData, 'functionsection', false);
            function_result = FgUtility.groupByMulti(data, ['rl_id', 'fn_sortOrder', 'fn_id', 'fn_lang']);
            var firstIndex = FgUtility.getFirstKeyOfArray(function_result);
            function_result = {'0': function_result[firstIndex]};
        } else {
            function_result = FgUtility.groupByMulti(data, ['rl_id', 'fn_sortOrder', 'functionid', 'fn_lang']);
        }
        _.each(function_result, function(function_data, roleId) {
            _.each(function_data, function(function_details, fn_sort_order) {
                _.each(function_details, function(function_detail, functionId) {
                    if ((functionId != null) && (functionId != 'null')) {
                        jsonData['roleId'] = roleId;
                        jsonData['function_assign'] = pageVars.function_assign;
                        jsonData['functionId'] = functionId;
                        jsonData['function_data'] = function_detail;
                        var functionParent = (pageVars.function_assign == 'same') ? 'categoryfunctions' : 'functions_' + roleId;
                        renderNewRow('template-function-add', functionParent, jsonData, 'function', false);
                    }
                });
            });
        });
        FgUtility.stopPageLoading();
        initPageFunctions();
        rolePageSettings.initPageEvents();
        pageVars.initialHtml = $('form#categorysettings').html();
    });
});

var rolePageSettings = {
    initPageEvents: function() {
        rolePageSettings.changeTitle();
        rolePageSettings.switchAssignmentClick();
        rolePageSettings.checkAllowed();
        rowFunctions.showLogFilter();
        rolePageSettings.resetChanges();
        FgPageTitlebar.checkMissingTranslation(defaultLang);
    },
    /* Function to change role title in individual function section on changing role title */
    changeTitle: function() {
        $('form').on('keyup', 'input[data-property=change_title]', function() {
            $('span[data-role-id=' + $(this).attr('data-roleid') + ']').text($(this).val());
        });
    },
    /* Function to show role titles on individual function section */
    showRoleTitles: function() {
        $('span[id=change_title]').each(function(){
            var roleId = $(this).attr('data-role-id');
            var roleTitle = $('input[data-roleid='+roleId+']:visible').val();
            $(this).text(roleTitle);
        });
    },
    /* Function to switch function assignment sction on switching 'function assignment' radio button */
    switchAssignmentClick: function() {
        $('input[data-elem-function=switch_assignment]').click(function() {
            var functionAssign = $(this).val();
            rolePageSettings.switchAssignment(functionAssign);
        });
    },
    // Assignment switch.
    switchAssignment: function(functionAssign) {
        if (functionAssign != pageVars.function_assign) {
            pageVars.function_assign = functionAssign
            $('#child_sortrole').html('');
            if (pageVars.function_assign != 'none') {
                var jsonData = {catId: pageVars.catId, clubLanguages: pageVars.clubLanguages, function_assign: pageVars.function_assign, isRoleNew: false};
                if (pageVars.function_assign == 'same') {
                    jsonData['roleId'] = 0;
                    renderNewRow('template-function-section-add', 'child_sortrole', jsonData, 'functionsection', true);
                    jsonData['isNew'] = true;
                    jsonData['functionId'] = $.now();
                    renderNewRow('template-function-add', 'categoryfunctions', jsonData, 'function', true);
                } else {
                    $('input[data-roleid]:visible').each(function(){
                        var dataRoleId = $(this).attr('data-roleid');
                        var roleId = ($(this).attr('id').indexOf('_new_') != -1) ? ('newrole' + dataRoleId) : dataRoleId;
                        jsonData['roleId'] = roleId;
                        renderNewRow('template-function-section-add', 'child_sortrole', jsonData, 'functionsection', true);
                        jsonData['isNew'] = true;
                        jsonData['functionId'] = $.now();
                        renderNewRow('template-function-add', 'functions_' + dataRoleId, jsonData, 'function', true);
                    });
                    rolePageSettings.showRoleTitles();
                }
            }
        }
    },
    /* Function to disable 'required' checkbox on switching 'allowed' radio button to 'no' */
    checkAllowed: function() {
        $('form').on('click', 'input[data-elem-function=check_allowed]', function() {
            rolePageSettings.checkRequiredAssignment(this);
        });
    },
    /* Function to check required assignment */
    checkRequiredAssignment: function(elem) {
        var elementName = $(elem).attr('name');
        var checkElement = (elementName == pageVars.catId + '_is_allowed_fedmember_subfed') ? $('#' + pageVars.catId + '_is_required_fedmember_subfed') : $('#' + pageVars.catId + '_is_required_fedmember_club');
        var checkElmntLblId = (elementName == pageVars.catId + '_is_allowed_fedmember_subfed') ? (pageVars.catId + '_is_required_fedmember_subfed_lbl') : (pageVars.catId + '_is_required_fedmember_club_lbl');
        if (pageVars.contCount > 0) {
            if ($(elem).val() == '0') {
                $(checkElement).prop('checked', false);
                $(checkElement).attr('disabled', 'disabled');
                $.uniform.update($(checkElement));
            }else{
                $(checkElement).removeAttr('disabled');
                $.uniform.update($(checkElement));
            }
            $('label[data-id='+checkElmntLblId+']').addClass('fg-label-inactive');
        } else {
            if ($(elem).val() == '0') {
                $(checkElement).attr("checked", false).attr('disabled', 'disabled').addClass('fairgatedirty');
                $('label[data-id='+checkElmntLblId+']').addClass('fg-label-inactive');
                $.uniform.update($(checkElement));
            } else {
                $(checkElement).removeAttr('disabled').addClass('fairgatedirty');
                $('label[data-id='+checkElmntLblId+']').removeClass('fg-label-inactive');
                $.uniform.update($(checkElement));
            }
        }
    },
    resetChanges: function() {
        $('form').on('click', '#reset_changes', function() {
            $('form#categorysettings').html(pageVars.initialHtml);
            FgDirtyFields.init('categorysettings', { enableDiscardChanges: false, setNewFieldsClean:true });
            initiateDragAndDrop();
        });
    }
};
/* to initiate drag n drop */
function initiateDragAndDrop() {
    if (pageVars.function_assign == 'same') {
        FgDragAndDrop.sortWithOrderUpdation('.dragndropwithchild', false);
    } else {
        FgDragAndDrop.sortWithOrderUpdation('.dragndropwithchild', true);
    }
    FgDragAndDrop.sortWithOrderUpdation('.dragndrop', false);
}
function doSortOrderUpdation(parentElement) {
    FgUtility.resetSortOrder(parentElement);
    FgDirtyFields.updateFormState();
}
