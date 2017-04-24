var filterRoleIds = [];
var filterRoleId = '';
var filterCount = saveCount = 0;
$(document).ready(function() {
    FgDirtyForm.init();
    //FgDragAndDrop.sortWithOrderUpdation('#sortrole', false);
    /* to display filter roles */
    $.getJSON(getRoleDataPath, null, function(data) {
        var role_result = FgUtility.groupByMulti(data, ['fr_sortOrder', 'fr_id', 'fr_lang']);
        var jsonData = {catId: catId, clubLanguages: clubLanguages, isNew: false};
        _.each(role_result, function(role_data, fr_sort_order) {
            _.each(role_data, function(role_detail, roleId) {
                if ((roleId != null) && (roleId != 'null')) {
                    jsonData['roleId'] = roleId;
                    jsonData['rolecontent_data'] = role_detail;

                    renderNewRow('template-filter-role', 'sortrole', jsonData);
                }

            });
        });
        delete jsonData["rolecontent_data"];
        initPageFunctions();
    });
    /*ends */
});
$('form').on('keyup', 'input[data-property=change_title]', function() {
    $('span[data-role-id=' + $(this).attr('data-roleid') + ']').html($(this).val());
});
$('form').on('click', 'button[data-elem-function=switch_lang]', function() {
    selectedLang = $(this).attr('data-selected-lang');
    FgUtility.showTranslation(selectedLang);
});
$('form').on('click', '#addrow', function() {
    var parentDivId = $(this).attr('data-parentdiv-id');
    var randomVar = $.now().toString();
    var addType = $(this).attr('data-add-type');
    var jsonDataRole = {catId: catId, clubLanguages: clubLanguages, isNew: true};
    $('#sortrole').removeClass('hide');
    var roleId = randomVar;
    jsonDataRole['roleId'] = roleId;
    jsonDataRole['filterroleid'] = roleId;
    renderNewRow('template-filter-role', parentDivId, jsonDataRole);
    filterClick = true;
    $("div.sortables:last").find('.fg-dev-openfilter').click();
});
$('form').on('click', 'input[data-deletable=checknew]', function() {
    var elementId = $(this).attr('id');
    if (elementId.indexOf('_new_') != -1) {

        var parentId = $(this).attr('data-parentid');
        var parentDivId = $($('#' + parentId).parent()).attr('id');
        var roleId = parentId.split('new_');

        $('#' + parentId).remove();
        $('div[data-rl-id=' + parentId + ']').remove();
        filterClick = false;

        FgUtility.resetSortOrder($('#' + parentDivId));
        hideDivHavingNoRows();
        return false;
    }
});
$('form').off('click', '.filterRoleCount');
$('form').on('click', '.filterRoleCount', function() {
    var filterId = $(this).attr('filter_id');
    var roleId = $(this).attr('role_id');
    var isBroken = $(this).attr('data-broken');
    var filterData = $("#" + roleId + "_jsonData").attr('data-val');
    if (filterData != '1') {
        if (isBroken == '0' || isBroken == "" && (filterData != 1)) {
            var replacediv = '.replaceFilterClass' + $(this).attr('filter_id');
            var url = $(this).attr('url');
            FgXmlHttp.post(url, {filter_id: filterId, role_id: roleId, type: 'role', 'from': 'filterrole'}, false, initPageFunctions);
            return false;
        }
    }
});
$('.contact-count-link').live('click', function() {
    var filterId = $(this).attr('filter_id');
    var roleId = $(this).attr('role_id');
    var isBroken = $(this).attr('data-broken');
    if (isBroken == '0' || isBroken == "") {
        FgContact.handlecontactclick('filterrole', catId, roleId, clubId2, contactId, clubUrlIdentifier, 'contact', 'filterrole');
    }

});

function errorHandler() {
    FgUtility.showTranslation(selectedLang);
    $('div.alert-danger span').html(formerror);
}
function initiateDragAndDrop() {
    FgDragAndDrop.sortWithOrderUpdation('.dragndrop', false);
}
function getResetSection(parentDivId) {
    var initialOrderArray = FgUtility.getOrderOfChildElements('#' + parentDivId);
    var resetSection = {'parentElement': '#' + parentDivId, 'initialOrder': initialOrderArray, 'addClass': true, 'className': 'blkareadiv'};

    return resetSection;
}
function hideDivHavingNoRows() {
    if ($('#sortrole').find('.row').length == 0) {
        $('#sortrole').addClass('hide');
    } else {
        $('#sortrole').removeClass('hide');
    }
}
$(".fg-dev-openfilter, .fg-dev-openexceptions, .fg-dev-openlog").live('click', function() {
    if ($(this).hasClass('collapsed')) {
        $(this).find('i').addClass('fa-plus-square-o');
        $(this).find('i').removeClass('fa-minus-square-o');
    } else {
        $(this).find('i').removeClass('fa-plus-square-o');
        $(this).find('i').addClass('fa-minus-square-o');
    }
});
$('form').off('click', '.fg-dev-openfilter');
$('form').on('click', '.fg-dev-openfilter', function() {
    filterRoleId = $(this).attr('role_id');
    filterRoleIds.push(filterRoleId);
    var filterId = $(this).attr('filter_id');
    $(".openfilterClass" + filterRoleId).removeClass('collapsed');
    if (!($(this).hasClass('collapsed'))) {
        $("#save_changes").attr("disabled", false);
        $("#reset_changes").attr("disabled", false);

        $(".openexceptionsClass" + filterRoleId).addClass('collapsed');
        $(".openexceptionsClass" + filterRoleId).find('i').addClass('fa-plus-square-o');
        $(".openexceptionsClass" + filterRoleId).find('i').removeClass('fa-minus-square-o');
        $("#open-exceptions-" + filterRoleId).removeClass('in');

        $(".openlogClass" + filterRoleId).addClass('collapsed');
        $(".openlogClass" + filterRoleId).find('i').addClass('fa-plus-square-o');
        $(".openlogClass" + filterRoleId).find('i').removeClass('fa-minus-square-o');
        $("#open-log-" + filterRoleId).removeClass('in');

        if (!($(this).hasClass('FilterDataExist'))) {

            if (!$(this).hasClass('FilterDataExist')) {
                var firstTime = true;
            }
            $(this).addClass('FilterDataExist');
            var target_id = '#open' + filterRoleId;
            $('#editedFilter').val(parseInt($('#editedFilter').val()) + 1);
            var storageName_jsn;
            /* The Filter data is fetch from DB on request */
            $.getJSON(contDataSinglePath + "?id=" + filterId + '&type=role', function(data_storage) {
                /* Replace the "contact_filter" with filter id to work  in the settings page */
                if (data_storage.singleSavedFilter != "") {
                    storageName_jsn = data_storage.singleSavedFilter['0'].filterData;
                    storageName_jsn = storageName_jsn.replace("contact_filter", filterId);
                    localStorage.setItem(filterId, storageName_jsn);
                } else {
                    localStorage.removeItem(filterId);
                }

                $(target_id).html('');
                var addbtnId = '#accCriteria' + filterRoleId;
                filterCount++;
                $(target_id).searchFilter({
                    jsonUrl: filterContDataPath,
                    jsonParams: {'getFilterRole': false},
                    save: '#save_' + filterRoleId,
                    storageName: filterId,
                    filterName: filterId,
                    addBtn: addbtnId,
                    customSelect: true,
                    dateFormat: FgApp.dateFormat,
                    conditions: filterCondition,
                    selectTitle: selectTitle,
                    criteria: '<div class="col-md-1"><span class="fg-criterion">' + cm_criteria + ':</span></div>',
                    onComplete: function(data) {//console.log(data);
                        if (data != 0) {
                            /* The Success call back add the stringfied data to a hidden input,
                             * this is for work the dirty form.
                             *   */
                            var stringifyed_data = JSON.stringify(data);
                            stringifyed_data = stringifyed_data.replace('{\"' + filterId + '\":{\"', '{\"contact_filter\":{\"');
                            $('#' + filterRoleId + '_jsonData').val(stringifyed_data);
                            $('#' + filterRoleId + '_jsonData').attr('data-val', stringifyed_data);
                            $('#' + filterRoleId + '_jsonData').addClass('fairgatedirty');
                            $('#' + filterRoleId + '_doNotSumbmit').removeClass('doNotSumbmit');
                            $('#' + filterRoleId + '_is_broken').val('0');

                            //FgDirtyForm.rescan('form');
                            firstTime = false;
                        } else {
                            /* The error call back will not allow you to sumbmit the form by aaading a class 'doNotSumbmit'
                             * This class is used to check on every sumbmit.
                             *   */
                            $('.alert').removeClass('display-hide');
                            $('#' + filterRoleId + '_doNotSumbmit').addClass('doNotSumbmit');
                            var url = updateBrokenPath;
                            if (firstTime) {
                                FgXmlHttp.post(url, {'id': filterId, broken: 1}, 'replcediv', false);
                            } else {
                                firstTime = false;
                            }
                        }
                        $('[class*=mask]').each(function() {
                            $(this).val($(this).val());
                        });
                    },
                    savedCallback: function(data) {
                        saveCount++;
                    },
                    errorCallack: function() {
                        saveCount--;
                    }
                });
                var saveBtn = "save_" + filterRoleId;
                $(target_id).append('<input type="button" class="btn hidden-submit hidden" value="save filter" id="' + saveBtn + '">');
            });

        } else {
            $(".fg-dev-openfilter").removeClass('collapsed');
            $(".openexceptionsClass" + filterRoleId).addClass('collapsed');
            $(".openexceptionsClass" + filterRoleId).find('i').addClass('fa-plus-square-o');
            $(".openexceptionsClass" + filterRoleId).find('i').removeClass('fa-minus-square-o');
            $("#open-exceptions-" + filterRoleId).removeClass('in');

            $(".openlogClass" + filterRoleId).addClass('collapsed');
            $(".openlogClass" + filterRoleId).find('i').addClass('fa-plus-square-o');
            $(".openlogClass" + filterRoleId).find('i').removeClass('fa-minus-square-o');
            $("#open-log-" + filterRoleId).removeClass('in');
        }

    }
});
$('form').off('click', '.fg-dev-openexceptions');
$('form').on('click', '.fg-dev-openexceptions', function() {
    var roleId = $(this).attr('role_id');
    var modes = $(this).attr('data-mode');
    if (modes.indexOf('new') > -1) {
        mode = 'new';
    } else {
        mode = 'old';
    }
    $(".openexceptionsClass" + roleId).removeClass('collapsed');
    if (!($(this).hasClass('collapsed'))) {

        $("#save_changes").attr("disabled", false);
        $("#reset_changes").attr("disabled", false);

        $(".openfilterClass" + roleId).addClass('collapsed');
        $(".openfilterClass" + roleId).find('i').addClass('fa-plus-square-o');
        $(".openfilterClass" + roleId).find('i').removeClass('fa-minus-square-o');
        $("#open-filter-" + roleId).removeClass('in');

        $(".openlogClass" + roleId).addClass('collapsed');
        $(".openlogClass" + roleId).find('i').addClass('fa-plus-square-o');
        $(".openlogClass" + roleId).find('i').removeClass('fa-minus-square-o');
        $("#open-log-" + roleId).removeClass('in');

        exceptionsDisplay(roleId, mode, this);
    }
});
$('form').off('click', '.fg-dev-openlog');
$('body').on('click', '#save_changes', function() {
    $(filterRoleIds).each(function(fky, roleId) {
        filterRoleId = roleId;
        $('#save_' + filterRoleId).trigger('click');
    });
    setTimeout(function() {
        if (filterCount == saveCount) {
            saveCount = 0;
        } else {
            return false;
        }
    }, 50)
})
$('form').on('click', '.fg-dev-openlog', function() {
    var roleId = $(this).attr('role_id');
    var id = $(this).attr('data-id');
    $(".openlogClass" + roleId).removeClass('collapsed');
    if (!($(this).hasClass('collapsed'))) {

        $(".openexceptionsClass" + roleId).addClass('collapsed');
        $(".openexceptionsClass" + roleId).find('i').addClass('fa-plus-square-o');
        $(".openexceptionsClass" + roleId).find('i').removeClass('fa-minus-square-o');
        $("#open-exceptions-" + roleId).removeClass('in');

        $(".openfilterClass" + roleId).addClass('collapsed');
        $(".openfilterClass" + roleId).find('i').addClass('fa-plus-square-o');
        $(".openfilterClass" + roleId).find('i').removeClass('fa-minus-square-o');
        $("#open-filter-" + roleId).removeClass('in');

        logdisplay(id, this);
    }
});
function handleTypeahead(item, obj, roleId) {
//    handleTypeahead = function(item, obj, roleId){
    newExclude = new Array();
    var engine = new Bloodhound({
        remote: {url: '/' + clubUrlIdentifier + '/backend/contact/contactnames/%QUERY?' + $.now(),
            ajax: {data: {'isCompany': 2}, method: 'post'},
            filter: function(contacts) {
                dataset = [];
                var tagged_user = $(item).tokenfield('getTokens');
                $.map(contacts, function(contact) {
                    var exists = false;
                    for (i = 0; i < tagged_user.length; i++) {
                        if (contact.id == tagged_user[i].id) {
                            var exists = true;
                        }
                    }
                    if (!exists) {
                        dataset.push({'id': contact.id, 'value': contact.contactname});
                    }
                });
                return dataset;
            }
        },
        datumTokenizer: function(d) {
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
    }).on('tokenfield:createtoken', function(e) {
        if (item == '#include-contact' + roleId) {
            var val = $("#include-" + roleId).val();
            var id = "#include-" + roleId;
        } else {
            var val = $("#exclude-" + roleId).val();
            var id = "#exclude-" + roleId;

        }
        if (val == '') {
            var newval = e.attrs.id;
        } else {
            var newval = val + ',' + e.attrs.id;
        }
        $(id).val(newval);

    }).on('tokenfield:removetoken', function(e) {

        var deletedId = e.attrs.id;
        var toremoveVal = e.attrs.value;
        var existing = new Array();

        if (item == '#include-contact' + roleId) {
            var id = "#include-" + roleId;
            existing = $("#include-existing" + roleId).val();
        } else {
            var id = "#exclude-" + roleId;
            existing = $("#exclude-existing" + roleId).val();
        }
        if (existing) {
            object = $.parseJSON(existing);
            $.each(object, function(key, val) {
                if (val == toremoveVal) {
                    deletedId = key;
                }
            });

        }
        if (deletedId) {
            var vals = $(id).val();
            var idarray = vals.split(',');
            for (i = 0; i < idarray.length; i++) {
                if (idarray[i] === deletedId) {
                    idarray.splice(i, 1);
                    var newval = idarray.join(',');
                    $(id).val(newval);
                }
            }
        }
    });
}
function exceptionsDisplay(roleId, mode, event) {

    var param = '?role_id=' + roleId;
    var jsonex;
    var id = '';
    var already_exists = false;
    var includeData = '';
    var excludeData = '';
    if (mode != 'new') {
        if (!$(event).hasClass('exceptionDataExist')) {
            $.getJSON(exceptionContsPath + param, null, function(data) {
                $(event).addClass('exceptionDataExist');
                include = [];
                exclude = [];
                var jsonex = {catId: catId, roleId: roleId, isNew: false};
                _.each(data, function(datas) {
                    _.each(datas, function(type_data, type) {
                        if (type == 'included') {
                            jsonex['includecontactId'] = type_data.contactId;
                            jsonex['includecontactName'] = type_data.contactName;
                            includeData = JSON.stringify(type_data.main);
                        } else {
                            jsonex['excludecontactId'] = type_data.contactId;
                            jsonex['excludecontactName'] = type_data.contactName;
                            excludeData = JSON.stringify(type_data.main);
                        }
                    });

                    var htmlFinal = FGTemplate.bind('template-filter-role-exceptions', jsonex);
                    $('#open-exceptions-' + roleId).append(htmlFinal);
                    handleTypeahead('#include-contact' + roleId, event, roleId);
                    handleTypeahead('#exclude-contact' + roleId, event, roleId);
                    $("#include-existing" + roleId).val(includeData);
                    $("#exclude-existing" + roleId).val(excludeData);
                    setTimeout(function() {
                        $('#include-' + roleId).addClass('fairgatedirty');
                        $('#exclude-' + roleId).addClass('fairgatedirty');
                        FgDirtyForm.rescan('form');
                    }, 200);
                });
            });
        } else {
            $("#exce-filter-" + roleId).show();
            setTimeout(function() {
                handleTypeahead('#include-contact' + roleId, event, roleId);
                handleTypeahead('#exclude-contact' + roleId, event, roleId);
                $('#include-' + roleId).addClass('fairgatedirty');
                $('#exclude-' + roleId).addClass('fairgatedirty');
                FgDirtyForm.rescan('form');
            }, 200);
        }

    } else {
        if ($('#open-exceptions-' + roleId).html().trim() == '') {
            var jsonex = {catId: catId, roleId: roleId, isNew: true};
            var htmlFinal = FGTemplate.bind('template-filter-role-exceptions', jsonex);
            $('#open-exceptions-' + roleId).append(htmlFinal);
        }
        handleTypeahead('#include-contact' + roleId, event, roleId);
        handleTypeahead('#exclude-contact' + roleId, event, roleId);
    }
}
function renderNewRow(templateScriptId, parentDivId, jsonData) {
    var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
    $('#' + parentDivId).append(htmlFinal);
    FgUtility.resetSortOrder($('#' + parentDivId));
    FgUtility.showTranslation(selectedLang);
    initiateDragAndDrop();
}
function initPageFunctions() {
    initiateDragAndDrop();
    FgUtility.showTranslation(selectedLang);
    FgApp.init();
    FgUtility.displayDetailsOnClick();
    hideDivHavingNoRows();
    // For resetting the changes (add row, sorting) done in the page on 'discard_changes'
    var resetSections = {};
    resetSections[0] = getResetSection('sortrole');
    FgResetChanges.init(resetSections);
    //ends
    FormValidation.init('filterrolecategorysettings', 'callSaveFunction', 'errorHandler');
    FgInputTextValidation.init();
    ComponentsDropdowns.init();
    checkForBrokenFilterCriteria();
    //handleTypeahead();

}
function callSaveFunction() {
    firstTime = false;
    var catArr;
    var objectGraph1 = {};
    $("form :input").each(function() {
        if (($(this).hasClass("fairgatedirty")) && ($(this).attr('data-filtertype') == 'filter')) {
            var inputVal = ''
            if ($(this).attr('type') == 'checkbox') {
                inputVal = $(this).attr('checked') ? 1 : 0;
            } else {
                inputVal = $(this).attr('data-val');
            }
            if ((inputVal !== '') && $(this).attr('data-key')) {
                converttojson(objectGraph1, $(this).attr('data-key').split('.'), inputVal);
            }
        }
    });

    $('div.addednew :input').addClass('fairgatedirty');
    $("form :input").each(function() {
        if (($(this).hasClass("fairgatedirty")) && ($(this).attr('data-filtertype') == 'role')) {
            var inputVal2 = ''
            if ($(this).attr('type') == 'checkbox') {
                inputVal2 = $(this).attr('checked') ? 1 : 0;
            } else {
                inputVal2 = $(this).val();
            }
            if (inputVal2 !== '' && ($(this).attr('data-key')) != 'undefined') {
                converttojson(objectGraph1, $(this).attr('data-key').split('.'), inputVal2);
            }

            if (($(this).attr('class') == 'include fairgatedirty' || $(this).attr('class') == 'exclude fairgatedirty') && (inputVal2 == '')) {
                converttojson(objectGraph1, $(this).attr('data-key').split('.'), 0);
            }
        }
    });

    //console.log(objectGraph1);return false;
    var catArr = JSON.stringify(objectGraph1);
    FgXmlHttp.post(savePath, {'catArr': catArr}, false);
}
function logdisplay(id, event) {
    var params = id.split('_');
    var param = '?type=' + params[3] + '&CatId=' + params[0] + '&roleId=' + params[2];
    var jsonlog = {type: 'role', typeId: params[2]};
    var typeId = params[2];
    if (!$(event).hasClass('LogDataExist')) {
        FgUtility.startPageLoading();
        $(event).addClass('LogDataExist');
        $.getJSON(logDataPath + param, null, function(data) {
          
            var logdisplay = FgUtility.groupByMulti(data.logdisplay, ['tabGroups']);
            var hierarchyClubIdArr = data.hierarchyClubIdArr;
            jsonlog['details'] = logdisplay;
            jsonlog['hierarchyClubIdArr'] = hierarchyClubIdArr;
            jsonlog['logTabs'] = data.logTabs;
            jsonlog['activeTab'] = '1';
            var html = FGTemplate.bind('log-listing', jsonlog);

            $('#open-log-' + params[2]).append(html);
            FgUtility.stopPageLoading();
            $('div.date input:enabled').parent().datepicker(FgApp.dateFormat);
            var logTabsLength = 2;
            for (var i = 1; i <= logTabsLength; i++) {
                FgUtility.displaylogsettings(typeId + '_' + i);
                logDateFilterSubmit('date_filter_' + typeId + '_' + i);
            }
            FgMoreMenu.initClientSideWithNoError('data-tabs_' + jsonlog['typeId'], 'data-tabs-content_' + jsonlog['typeId']);
        });
    } else {
        FgUtility.startPageLoading();

        $('#log-table_' + params[2]).show();
        FgUtility.stopPageLoading();
    }
}
$('form').on('click', '.fgContactLogFilter', function() {
    var typeId = $(this).attr('data-typeId');
    $('div[data-log-area="log-area_' + typeId + '"]').toggleClass('show');
    var tableGroup = "log_display_" + typeId;
    $('table.table[data-table-group="' + tableGroup + '"]').toggleClass('fg-common-top');
    $('#fg-log-filter_' + typeId).toggleClass('fg-active-btn');
});
$('form').on('shown.bs.tab', '.data-more-tab li a[data-toggle="tab"]', function() {
    var curDataTableId = $(this).attr('data-datatableid');
    $('#' + curDataTableId).dataTable().api().draw();
});
function checkForBrokenFilterCriteria() {
    $.getJSON(filterContDataPath, { 'getFilterRole': false }, function(masterJsonData) {
        $('.jsonDatahidden').each(function() {
            var filterJsonData = $.parseJSON($(this).attr('data-val'));
            var idStringArr = $(this).attr('id').split('_');
            var id = idStringArr[0];
            var isBroken = false;
            isBroken = FgValidateFilter.init(masterJsonData, filterJsonData);
            if (!isBroken) {
                $('#openfilter'+id).after('<i class="fa fa-warning fg-warning fg-broken-filter"  data-toggle="tooltip"></i>');
            }
        });
    });
}
