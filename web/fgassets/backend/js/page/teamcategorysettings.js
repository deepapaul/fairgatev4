$(document).ready(function () {
    var result_data = pageVars.result_data;
    //team category language switching starts here
    var lang_result = result_data[pageVars.catid];
    var jsonDataLang = {catId: pageVars.catid, clubLanguages: pageVars.clubLanguages};
    jsonDataLang['title'] = lang_result['title'];
    jsonDataLang['titleLang'] = lang_result['titleLang'];
    //teamSettings.renderNewRow('lang_switch', 'team_lang', jsonDataLang, false);
    //team category language switching ends here
    //team and function listing starts here
    var param = '?cat_id=' + pageVars.catid + '&teamCatId=' + pageVars.teamCatId;
    var ids = '';
    $.getJSON(pageVars.teamFunctionPath + param, null, function (data) {
        var team_result = FgUtility.groupByMulti(data, ['rl_sortOrder', 'rl_id', 'rl_lang']);
        var fn_test = FgUtility.groupByMulti(data, ['rl_sortOrder', 'rl_id', 'f_sortOrder']);
        var jsonDataTeam = {catId: pageVars.catid, clubLanguages: pageVars.clubLanguages, isNew: false, categories: pageVars.categories, frontend_booked: pageVars.frontendBooked};
        teamId = 0;
        _.each(team_result, function (team_data, rl_sort_order) {
            _.each(team_data, function (team_detail, teamId) {
                if ((teamId != null) && (teamId != 'null')) {
                    ids += teamId + ',';
                    jsonDataTeam['teamId'] = teamId;
                    jsonDataTeam['teamcontent_data'] = team_detail;
                    jsonDataTeam['team_function'] = {};

                    _.each(fn_test, function (fn_result, sortorder) {
                        _.each(fn_result, function (fn_s, fnteamId) {
                            if (teamId == fnteamId) {
                                jsonDataTeam['team_function'][teamId] = fn_s;
                            }
                        });
                    });
                    teamSettings.renderNewRow('template-team-add', 'sortrole', jsonDataTeam, false);
                }
            });
        });
        FgUtility.resetSortOrder($('#sortrole'));
        var param = '?roleId=' + ids.substring(0, ids.length - 1);
        $.getJSON(pageVars.userrightsCountPath + param, function (countData) {
            _.each(countData['count'], function (val, key) {
                $('#count_' + pageVars.catid + '_team_' + val['id']).text(val['count']);
            });
        });

        var function_result = {};
        function_result = FgUtility.groupByMulti(data, ['f_sortOrder', 'f_id', 'f_lang']);
        _.each(function_result, function (function_data, f_sort_order) {
            _.each(function_data, function (function_details, functionId) {
                if ((functionId != null) && (functionId != 'null')) {
                    jsonDataTeam['functionId'] = functionId;
                    jsonDataTeam['function_data'] = function_details;
                    teamSettings.renderNewRow('template-teamfunction-add', 'child_sortrole', jsonDataTeam, false);
                }
            });
        });
        FgUtility.resetSortOrder($('#child_sortrole'));
        //ends
        teamSettings.initPageFunctions();
        teamSettings.initPageEvents();
        pageVars.initialHtml = $('form#teamcategorysettings').html();
    });
    //team and function listing ends here
});

var teamSettings = {
    // Functions to be executed after loading page.
    initPageFunctions: function () {
        teamSettings.initiateDragAndDrop();
        FgUtility.showTranslation(pageVars.selectedLang);
        FgApp.init();
        FgDirtyFields.init('teamcategorysettings', {enableDiscardChanges: false, setNewFieldsClean: true});
        FgUtility.displayDetailsOnClick();
        teamSettings.hideDivHavingNoRows();
        FormValidation.init('teamcategorysettings', 'saveChanges', 'errorHandler');
        teamSettings.initiatedefaultdescription();
        FgInputTextValidation.init();
        FgPopOver.customPophover(".fg-dev-team-popover", true);
        FgPageTitlebar.checkMissingTranslation(defaultLang);

    },
    // Initiate drag and drop.
    initiateDragAndDrop: function () {
        FgDragAndDrop.sortWithOrderUpdation('.dragndrop', false);
    },
    // For hiding divs having no rows.
    hideDivHavingNoRows: function () {
        if ($('#sortrole').find('.row').length == 0) {
            $('#sortrole').addClass('hide');
        } else {
            $('#sortrole').removeClass('hide');
        }
        if ($('#child_sortrole').find('.row').length == 0) {
            $('#child_sortrole').addClass('hide');
        } else {
            $('#child_sortrole').removeClass('hide');
        }
    },
    // Setting default description.
    initiatedefaultdescription: function () {
        $('textarea[data-placeholder=exists]').click(function () {
            $(this).attr('placeholder', '');
        });
        $('textarea[data-placeholder=exists]').focusout(function () {
            if ($(this).val() == '') {
                $(this).attr('placeholder', pageVars.catSettingDefaultDesc);
            }
        });
    },
    // Events to be bind after loading page.
    initPageEvents: function () {
        teamSettings.displayLogs();
        teamSettings.sponsorDisplay();
        teamSettings.administratorsDisplay();
        teamSettings.addTeam();
        teamSettings.deleteNewRow();
        teamSettings.switchLanguage();
        teamSettings.changeTitle();
        rowFunctions.showLogFilter();
        teamSettings.resetChanges();
        teamSettings.setRowDeleted();
    },
    // Display logs.
    displayLogs: function () {
        // Log team block click handling.
        $('form').on('click', '.log_team i', function () {
            var id = $(this).parent('div').attr('id');
            rowFunctions.logdisplay(id, 'team');
        });
        // Log function block click handling.
        $('form').on('click', '.log_fun i', function () {
            var id = $(this).parent('div').attr('id');
            rowFunctions.logdisplay(id, 'team');
        });
    },
    // Sponsor block click handling.
    sponsorDisplay: function () {
        $('form').on('click', '.sponsor_team i', function () {
            var id = $(this).parent('div').attr('id');
            $("div[id^='log_'].fg-control-aranew.fg-pad-0").hide();
            teamSettings.sponsorBydisplay(id);
        });
    },
    sponsorBydisplay: function (id) {
        var params = id.split('_');
        var param = '?type=' + params[1] + '&roleId=' + params[2];
        var jsonlog = {type: params[1], typeId: params[2]};
        if ($("#" + id).children().attr('data-loaded') == 'true') {
            $(this).find('#displaydetails_' + id).removeClass('hide');
            $("#log_" + params[2]).removeClass('hide');
        } else {
            $('#sponsor_' + jsonlog['typeId']).addClass('fg-hide');
            $("#" + id).children().attr('data-loaded', 'true');
            $.getJSON(pageVars.teamSponsorListPath + param, null, function (data) {
                jsonlog['details'] = data.sponsordata;
                jsonlog['contactpath'] = data.contactpath;
                var html = FGTemplate.bind('teamSponsoredBy', jsonlog);
                $('#sponsor_' + jsonlog['typeId']).hide().removeClass('fg-hide').children('.fg-pad-20').append(html);
                $('#sponsor_' + jsonlog['typeId']).slideDown(600);
            });
        }
    },
    // Admin userights click handling.
    administratorsDisplay: function () {
        $('form').on('click', '.fg-admin ', function () {
            var id = $(this).parent('div').attr('id');
            if (!$("#displaydetails_" + id).attr('data-loaded')) {
                FgUtility.startPageLoading();
                teamSettings.userrightsDisplay(id);
                teamSettings.optiondisplay(id);
            }
        });
    },
    optiondisplay: function (id) {
        var params = id.split('_');
        $(this).find('#option_' + id).removeClass('hide');
        if ($("#" + id).children().attr('data-loaded') == 'true') {
            $(this).find('#displaydetails_' + id).removeClass('hide');
            $("#option_" + params[2]).removeClass('hide');

        } else {
            $("#" + id).children().attr('data-loaded', 'true');
        }
    },
    // User rights display.
    userrightsDisplay: function (id) {
        var params = id.split('_');
        var param = '?type=' + params[1] + '&role=' + params[2];
        $.getJSON(pageVars.userrightsPath + param, null, function (data) {
            var contactPath = pageVars.contactOverviewPath; // Overview link
            var result_data = FGTemplate.bind('fg-dev-internal-team-userrights',
                    {contactPath: contactPath, roleId: data.roleId, type: data.type, groupAdmin: JSON.parse(data.groupAdmin), loggedContactId: data.loggedContactId, admins: JSON.parse(data.admins)});
            $('#fg-dev-user-rights-div-' + params[2]).html(result_data); // Displaying user rights from underscore
            $('select.selectpicker').selectpicker('render');
            $('#fg-dev-user-rights-div-' + params[2]).show();
            FgDirtyFields.addFields(result_data);
            FgUtility.stopPageLoading();
            var newExcludeRoleAdmins = new Array();
            var newExcludeRoleSectionAdmins = new Array();
            if (data.type == 'T') {
                var exclude = JSON.parse(data.exclude);
                if (exclude.existingUserDetails != null && exclude.existingUserDetails.length != 0) {
                    newExcludeRoleAdmins = exclude.existingUserDetails; // Assigning current details to auto complete array to exclude the same contacts from auto complete
                }
                if (exclude.existingSectionUser != null && exclude.existingSectionUser.length != 0) {
                    newExcludeRoleSectionAdmins = exclude.existingSectionUser; // Assigning current details to auto complete array to exclude the same contacts from auto complete
                }
            }
            var options = {
                newExclude: newExcludeRoleAdmins,
                newExcludeRoleSectionAdmins: newExcludeRoleSectionAdmins,
                contactNameUrl: pageVars.contactNameUrl,
                saveUrl: pageVars.saveUserrightsPath,
                saveFlag: 0,
                admins: JSON.parse(data.admins),
                type: data.type,
                roleId: data.roleId,
                formId: "teamcategorysettings"
            };
            FgUserRights.initRoleCatUserrights(options); // Initing all the underlying functionalities of user rights including create and delete
        });
    },
    // Add team.
    addTeam: function () {
        $('form').on('click', '#addteam', function () {
            var parentDivId = $(this).attr('data-parentdiv-id');
            var randomVar = $.now().toString();
            var addType = $(this).attr('data-add-type');
            var functions = FgUtility.groupByMulti(pageVars.new_functions, ['f_sortOrder']);
            var jsonDataTeam = {catId: pageVars.catid, clubLanguages: pageVars.clubLanguages, isNew: true, categories: pageVars.categories, frontend_booked: pageVars.frontendBooked, new_functions: functions};
            if (addType == 'team') {
                $('#sortrole').removeClass('hide');
                var teamId = randomVar;
                jsonDataTeam['teamId'] = 'newteam' + teamId;
                teamSettings.renderNewRow('template-team-add', parentDivId, jsonDataTeam, true);
                FgTooltip.init();
            } else {
                $('#child_sortrole').removeClass('hide');
                var functionId = randomVar;
                jsonDataTeam['functionId'] = 'newfunction' + functionId;
                teamSettings.renderNewRow('template-teamfunction-add', parentDivId, jsonDataTeam, true);
                FgTooltip.init();
            }
        });
    },
    // Adding new row.
    renderNewRow: function (templateScriptId, parentDivId, jsonData, isNew) {
        var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
        $('#' + parentDivId).append(htmlFinal);
        $('#' + parentDivId).find('.addednew').slideDown('250', 'easeInQuart');
        FgUtility.showTranslation(pageVars.selectedLang);
        teamSettings.initiateDragAndDrop();
        teamSettings.initiatedefaultdescription();
        FgTextAreaAuto.init(height = 42);
        if (isNew) {
            FgDirtyFields.addFields(htmlFinal);
        }
    },
    // Delete newly added row.
    deleteNewRow: function () {
        $('form').on('click', 'input[data-deletable=checknew]', function () {
            var elementId = $(this).attr('id');
            var parentId = $(this).attr('data-parentid');
            var parentDivId = $($('#' + parentId).parent()).attr('id');
            if (elementId.indexOf('_new_') != -1) {
                FgDirtyFields.removeFields($('#' + parentId));
                $('#' + parentId).remove();
                $('div[data-rl-id=' + parentId + ']').remove();
                FgUtility.resetSortOrder($('#' + parentDivId));
                teamSettings.hideDivHavingNoRows();
                if ($('div.addednew').length < 1) {
                    FgDirtyFields.updateFormState();
                }
                return false;
            }
            FgUtility.resetSortOrder($('#' + parentDivId));
        });
    },
    // Switching language.
    switchLanguage: function () {
        $('form').on('click', 'button[data-elem-function=switch_lang]', function () {
            var selectedLang = $(this).attr('data-selected-lang');
            pageVars.selectedLang = selectedLang;
            FgUtility.showTranslation(selectedLang);
        });
    },
    // Function to do on changing title.
    changeTitle: function () {
        $('form').on('keyup', 'input[data-property=change_title]', function () {
            $('span[data-role-id=' + $(this).attr('data-roleid') + ']').html($(this).val());
        });
    },
    resetChanges: function () {
        $('form').on('click', '#reset_changes', function () {
            $('form#teamcategorysettings').html(pageVars.initialHtml);
            FgDirtyFields.init('teamcategorysettings', {enableDiscardChanges: false, setNewFieldsClean: true});
            teamSettings.initiateDragAndDrop();
            FgTooltip.init();
        });
    },
    setRowDeleted: function () {
        $('form').on('change', 'select[name*=team_category_id]', function () {
            if ($(this).val() != pageVars.catid) {
                $($(this).parents('.sortables')).addClass('fg-dev-rowdeleted');
            } else {
                $($(this).parents('.sortables')).removeClass('fg-dev-rowdeleted');
            }
        });
    }
};
function saveChanges() {
    $('div.alert-danger').hide();
    $('form').find('has-error').removeClass('has-error');
    //check whether functions are added
    if (($('#child_sortrole .closeico input[data-deletable=checknew]:not(:checked)').length <= 0) && ($('#child_sortrole i.fa.fa-lock').length <= 0)) {
        $('div.alert-danger span').html(pageVars.nofunctionerror);
        $('div.alert-danger').show();
        Metronic.scrollTo($('div.alert-danger'), -200);
    } else {
        $('div.addednew :input').addClass('fairgatedirty');
        $('input[data-elem-function=switch_assignment]:checked').addClass('fairgatedirty');
        var objectGraph = {};
        //parse the all form field value as json array and assign that value to the array
        objectGraph = FgParseFormField.fieldParse();
        var catArr = JSON.stringify(objectGraph[pageVars.catid]);
        var userrightsArr = JSON.stringify(objectGraph['teams']);
        var element = $('input[type="submit"]#save_changes');
        element.attr('data-toggle', "confirmation");
        element.parent('div').addClass('fg-confirm-btn');
        element.confirmation('destroy');
        validation = 0;
        $('.fg-dev-auto-complete-val').each(function () {
            if ($(this).val() == '') { // Setting validation flag if there is any errors
                validation = 1;
                $(this).siblings().first().addClass("has-error");

            }
        });
        if (validation) {
            $('div.alert-danger span').html(pageVars.formerror);
            $('div.alert-danger').show();
            Metronic.scrollTo($('div.alert-danger'), -200);
            return false;
        }
        if ($('input[type="checkbox"].fgroledeletebutton').closest('div.inactiveblock').length > 0) {
            FgConfirmation.confirm(confirmNote, cancelTrans, confirmTrans, element, function () {
                FgXmlHttp.post(pageVars.updatePath, {'catid': pageVars.catid, 'catArr': catArr, 'type': 'team', 'userrightsArr': userrightsArr}, false, saveTeamCategoryCallback);
            }, false, 'manual');
        } else {
            FgXmlHttp.post(pageVars.updatePath, {'catid': pageVars.catid, 'catArr': catArr, 'type': 'team', 'userrightsArr': userrightsArr}, false, saveTeamCategoryCallback);
        }
    }
}

function saveTeamCategoryCallback(response) {
    FgClearInvalidLocalStorageDataOnDelete.clear(response);
    teamSettings.initPageFunctions();
}
function errorHandler() {
    FgUtility.showTranslation(defaultLang);
    $('div.alert-danger span').html(pageVars.formerror);
}
function doSortOrderUpdation(parentElement) {
    FgUtility.resetSortOrder(parentElement);
    FgDirtyFields.updateFormState();
}
