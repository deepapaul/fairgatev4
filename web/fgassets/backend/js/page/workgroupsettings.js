$(document).ready(function() {
    /* to display roles and functions */
    var ids ='';
    $.getJSON(pageVars.workgroupDataPath, function(data) {
        var rolefunctiondata = data.rolefunctiondata;
        var execboardfunctions = data.execboardfunctions;
        var jsonData = {catId: pageVars.catId, clubLanguages: pageVars.clubLanguages, isNew: false};
        var wrkgrp_result = FgUtility.groupByMulti(rolefunctiondata, ['rl_sortOrder', 'rl_id', 'rl_lang']);
        _.each(wrkgrp_result, function(wrkgrp_data, wg_sort_order) {
            _.each(wrkgrp_data, function(wrkgrp_detail, wgId) {
                ids += wgId+',';
                jsonData['roleId'] = wgId;
                jsonData['rolecontent_data'] = wrkgrp_detail;
                jsonData['roleType'] = 'workgroup';
                jsonData['disable_row'] = (wgId == pageVars.executiveBoardId) ? true : false;
                renderNewRow('template-role-add', 'sortrole', jsonData, 'role', false);
            });
        });
        var param = '?roleId=' + ids.substring(0, ids.length - 1);
        $.getJSON(pageVars.userrightsCountPath + param, function(countData){
             _.each(countData['count'], function(val,key) {
                 $('#count_'+pageVars.catId+'_role_'+val['id']).text(val['count']);
             });
        });
        delete jsonData["rolecontent_data"];
        var execboardData = FgUtility.groupByMulti(execboardfunctions, ['fn_sortOrder', 'fn_id', 'fn_lang']);

        var function_result = FgUtility.groupByMulti(rolefunctiondata, ['rl_id', 'fn_sortOrder', 'functionid', 'fn_lang']);
        _.each(function_result, function(function_data, roleId) {
            if (roleId == pageVars.executiveBoardId) {
                _.each(execboardData, function(function_details, fn_sort_order) {
                    _.each(function_details, function(function_detail, functionId) {
                        if ((functionId != null) && (functionId != 'null')) {
                            jsonData['roleId'] = roleId;
                            jsonData['function_assign'] = pageVars.function_assign;
                            jsonData['functionId'] = functionId;
                            jsonData['function_data'] = function_detail;
                            jsonData['disable_row'] = true;
                            renderNewRow('template-function-add', 'functions_' + roleId, jsonData, 'function', false);
                        }
                    });
                });
            }
            delete jsonData["disable_row"];
            _.each(function_data, function(function_details, fn_sort_order) {
                _.each(function_details, function(function_detail, functionId) {
                    if ((functionId != null) && (functionId != 'null')) {
                        var isFedFunction = false;
                        if ('de' in function_detail) {
                            isFedFunction = function_detail['de'][0]['isFedFunction'];
                        } else {
                            var firstLang = FgUtility.getFirstKeyOfArray(function_detail);
                            isFedFunction = function_detail[firstLang][0]['isFedFunction'];
                        }
                        if (!((pageVars.clubType == 'federation') && isFedFunction)) {
                            jsonData['roleId'] = roleId;
                            jsonData['function_assign'] = pageVars.function_assign;
                            jsonData['functionId'] = functionId;
                            jsonData['function_data'] = function_detail;
                            renderNewRow('template-function-add', 'functions_' + roleId, jsonData, 'function', false);
                        }
                    }
                });
            });
            rolePageSettings.addFunctionCreationLink(roleId);
        });
        FgUtility.stopPageLoading();
        initPageFunctions();
        setLockForRole();
        workgroupSettings.initPageEvents();
        pageVars.initialHtml = $('form#categorysettings').html();
        enableExecutiveBoardFuncBlock();
    });
});

var workgroupSettings = {
    // User rights display.
    userrightsDisplay: function(id) {
        var params = id.split('_');
        var param = '?type=' + params[1] + '&role=' + params[2];
        $.getJSON(pageVars.userrightsPath + param, null, function(data) {
            var contactPath = pageVars.contactOverviewPath; // Overview link
            var result_data = FGTemplate.bind('fg-dev-internal-team-userrights',
            {contactPath:contactPath,roleId:data.roleId,type:data.type,groupAdmin:JSON.parse(data.groupAdmin),loggedContactId: data.loggedContactId,admins:JSON.parse(data.admins)});
            $('#fg-dev-user-rights-div-'+params[2]).html(result_data); // Displaying user rights from underscore
            $('select.selectpicker').selectpicker('render');
            //$('#fg-dev-user-rights-div-'+params[2]).show();
            $('#fg-dev-user-rights-div-'+params[2]).slideDown(600);
            FgDirtyFields.addFields(result_data);
            FgUtility.stopPageLoading();
            var newExcludeRoleAdmins = new Array();
            var newExcludeRoleSectionAdmins = new Array();
            if(data.type == 'W'){
                var exclude = JSON.parse(data.exclude);
                if(exclude.existingWGUserDetails != null && exclude.existingWGUserDetails.length != 0) {
                    newExcludeRoleAdmins = exclude.existingWGUserDetails; // Assigning current details to auto complete array to exclude the same contacts from auto complete
                }
                if(exclude.existingWGSectionUser != null && exclude.existingWGSectionUser.length != 0) {
                    newExcludeRoleSectionAdmins = exclude.existingWGSectionUser; // Assigning current details to auto complete array to exclude the same contacts from auto complete
                }
            }
            var options = {
                newExclude                  : newExcludeRoleAdmins,
                newExcludeRoleSectionAdmins : newExcludeRoleSectionAdmins,
                contactNameUrl              : pageVars.contactNameUrl,
                saveUrl                     : pageVars.saveUserrightsPath,
                saveFlag                    : 0,
                admins                      : JSON.parse(data.admins),
                type                        : data.type,
                roleId                      : data.roleId,
                formId                      : "categorysettings"
            };
            FgUserRights.initRoleCatUserrights(options); // Initing all the underlying functionalities of user rights including create and delete
        });
    },
    // Display workgroup details.
    optiondisplay: function(id) {
        var params = id.split('_');
        $(this).find('#option_' + id).removeClass('hide');
        if ($("#" + id).children().attr('data-loaded') == 'true') {
               $(this).find('#displaydetails_' + id).removeClass('hide');
               $("#option_" + params[2]).removeClass('hide');
        } else {
            $("#" + id).children().attr('data-loaded', 'true');
        }
    },
    // Function to initiate events after loading content.
    initPageEvents: function() {
        FgPopOver.customPophover(".fg-dev-team-popover", true);
        workgroupSettings.displayDetailsOnClick();
        rowFunctions.showLogFilter();
        workgroupSettings.resetChanges();
    },
    // Display details on clicking.
    displayDetailsOnClick: function() {
        $('form').on('click', '.fg-admin', function() {
            var id = $(this).parent('div').attr('id');
            if (!$("#displaydetails_" + id).attr('data-loaded')) {
                FgUtility.startPageLoading();
                workgroupSettings.userrightsDisplay(id);
                workgroupSettings.optiondisplay(id);
            }
        });
    },
    resetChanges: function() {
        $('form').on('click', '#reset_changes', function() {
            $('form#categorysettings').html(pageVars.initialHtml);
            FgDirtyFields.init('categorysettings', { enableDiscardChanges: false, setNewFieldsClean:true });
            initiateDragAndDrop();
            setTimeout(function () {
                FgTooltip.init()
            },100);
        });
    }
};
var rolePageSettings = {
    /* Function to add 'Add Function' link */
    addFunctionCreationLink: function(roleId) {
        var addFunctionHtml = '<div class="row" data-showaddfunc="true"><div class="col-md-2 fg-common-top-btm" name="fg-dev-add-function">\n\
                <a id="addrow" href="#basic" data-toggle="modal" data-parentdiv-id="functions_'+roleId+'" data-add-type="function" data-parent="'+roleId+'">\n\
                <i class="fa fg-plus-circle fg-add-link fa-2x pull-left"></i> <span class="fg-add-text">'+pageVars.addFunctionText+'</span> </a></div></div>';
        $('#functions_' + roleId).after(addFunctionHtml);
    }
};
/* to initiate drag n drop */
function initiateDragAndDrop() {
    FgDragAndDrop.sortWithOrderUpdation('.dragndrop', true);
}
function doAfterSort() {
    insertBorderLine();
}
function doSortOrderUpdation(parentElement) {
    FgUtility.resetSortOrder(parentElement);
    FgDirtyFields.updateFormState();
}
function enableExecutiveBoardFuncBlock() {
    
    if(wgId != 1 && execbrdId !=1){
       $('#functions_'+execbrdId).show();
        if ($('#functions_' + execbrdId).hasClass('hide')) {
            $('#functions_' + execbrdId).removeClass('hide');
        }
       $('#displaydetails_'+wgId+'_role_'+execbrdId).removeClass('hide');
       $('i[data-parent-div='+wgId+'_role_'+execbrdId+'][data-showfunction="true"]').removeClass('fa-plus-square-o');
       $('i[data-parent-div='+wgId+'_role_'+execbrdId+'][data-showfunction="true"]').addClass('fa-minus-square-o');
    }
}
