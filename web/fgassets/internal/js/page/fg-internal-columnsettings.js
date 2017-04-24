$(function() {
    
    $.getJSON(getSettingsPath, function(fields) {
       
        /* display contact fields */
        var contactFieldData = {clubId: clubId, contactFields: fields.contactFields};
        FgInternalColumnSettings.renderColumns('template-display-contactfields', 'displayContactFields', contactFieldData);

        /* display role categories */
        var assignmentData = {assignmentFields: fields.assignmentFields};
        FgInternalColumnSettings.renderColumns('template-display-assignmentfields', 'displayAssignmentFields', assignmentData);
        FgFormTools.handleBootstrapSelect();
        $('.fg-dev-int-col-func-chkbx').uniform();
        $('#displayStaticFields').removeClass('hide');
        //show current table settings
        var selectedSettings = localStorage.getItem(tablesettingValue) ? localStorage.getItem(tablesettingValue) : defaultSettingsArray;
        if (selectedSettings.substring(0, 1) === '"') {
            selectedSettings = selectedSettings.substring(1, (selectedSettings.length - 1))
        }
        selectedSettings = $.parseJSON(selectedSettings);
        $.each(selectedSettings, function(sortOrder, selectedSetting) {
            var orgType = (selectedSetting.type == 'RF') ? 'R' : selectedSetting.type;
            var settingClubId = ((selectedSetting.type == 'R') || (selectedSetting.type == 'RF')) ? selectedSetting.club_id : clubId;
            var teamRleCatIds = (selectedSetting.team_rolecat_ids == '') ? '' : JSON.stringify(selectedSetting.team_rolecat_ids);
            FgInternalColumnSettings.displaySelectedColumns(selectedSetting.id, selectedSetting.type, settingClubId, orgType, selectedSetting.sub_ids, teamRleCatIds, selectedSetting.is_fed_cat);
        });
        FgInternalColumnSettings.initPageFunctions();
    });
    //function to select item on click
    $('form').on('click', 'i.selectitem', function() {
        if (!($(this).hasClass('disabled') || $(this).parent().hasClass('disabled'))) {
            var subIds = '';
            var selType = $(this).attr('type');
            if (($(this).attr('type') == 'R') || ($(this).attr('type') == 'RF')) {
                if ($('input[type=checkbox][data-catid=' + this.id + ']').is(':checked')) {
                    selType = 'RF';
                }
                var selectData = $('select[data-catid=' + this.id + ']').val();
                if (!$.inArray('selectall', selectData)) { //if 'select all' is selected
                    selectData.splice($.inArray('selectall', selectData), 1);
                    //reset roles dropdown
                    $('select[data-catid=' + this.id + ']').parent().find('a.selectall').trigger('click'); //de-select all
                } else {
                    //reset roles dropdown
                    $('select[data-catid=' + this.id + ']').parent().find('a.selectall').trigger('click'); //select all
                    $('select[data-catid=' + this.id + ']').parent().find('a.selectall').trigger('click'); //de-select all
                }
                subIds = selectData.toString();
            }
            //for forming team cat-roles array
            var teamCats = {};
            $('select[data-catid=' + this.id + '] option.multiple').each(function() {
                if ($(this).attr('data-teamcatid') != '') {
                    if ($.inArray($(this).val(), selectData) != -1) {
                        if (teamCats[$(this).attr('data-teamcatid')] != undefined) {
                            teamCats[$(this).attr('data-teamcatid')] = teamCats[$(this).attr('data-teamcatid')] + ',' + $(this).val();
                        } else {
                            teamCats[$(this).attr('data-teamcatid')] = $(this).val();
                        }
                    }
                }
            });          
            var teamRoleCatIds = '';
            if (Object.keys(teamCats).length) {
                teamRoleCatIds = JSON.stringify(teamCats);
            }
            FgInternalColumnSettings.displaySelectedColumns(this.id, selType, $(this).attr('club_id'), $(this).attr('type'), subIds, teamRoleCatIds, $(this).attr('data-is-fed-cat'));
            FgInternalDragAndDrop.init('#displaySelectedColumns');
            $('.popovers').popover();
        }
    });
    //function to remove item on click
    $('form').on('click', 'i.removeitem,i.fa-minus-circle.fg-removeperitem', function() {
        var id = $(this).attr('id');
        var type = $(this).attr('type');
        if ((type != 'R') && (type != 'RF')) {
            $('i[id='+id+'][type='+type+']').parent().removeClass('disabled fg-itemdisabled');
            $('i[id='+id+'][type='+type+']').removeClass('fa-minus-circle');
            $('i[id='+id+'][type='+type+']').addClass('fg-plus-circle');
        }
        var elem = $(this).hasClass('removeitem') ? $(this) : $('i.removeitem[id='+id+'][type='+type+']');
        elem.parents('.selectedrow').remove();
    });
    $('form').on('click', 'a#clearselected', function() {
        $('i.removeitem').trigger('click');
    });
    $(document).off('click', '#applysettings');
    //apply settings
    $(document).on('click', '#applysettings', function() {
    
        FgInternalColumnSettings.saveSettings(this);
        return false;
    });
    /* activate add button and function checkbox on selecting role */
    $('form').on('change', 'select[data-catid]', function() {
        if ($(this).val()) {
            $(this).parent().parent().find('i.fg-plus-circle').removeClass('disabled');
            $(this).parent().parent().find('input:checkbox').attr('disabled', false);
            $.uniform.update($($(this).parent().parent().find('input:checkbox')));
        } else {
            $(this).parent().parent().find('i.fg-plus-circle').addClass('disabled');
            $(this).parent().parent().find('input:checkbox').attr({'checked': false, 'disabled': true});
            $.uniform.update($($(this).parent().parent().find('input:checkbox')));
        }
    });
    //cancel settings
    $(document).on('click', '#cancelsettings', function() {
        location.href = clickPath;
    });
    
});

FgInternalColumnSettings = {
    initPageFunctions: function() {
    FgColumnSettings.handleSelectPicker();
    FgInternalDragAndDrop.init('#displaySelectedColumns');
    },
    displaySelectedColumns: function(id, type, clubId, orgType, subIds, teamRoleCatIds, isFedCat) {
      
        var selectedCount = 0;
        var selectedHtml = '';
        var labelName = $('span[data-label-id=' + orgType + id + ']').html();
        var displayInfoIcon = true;
        if ((orgType == 'R') || (orgType == 'RF')) {
            //disable add icon & function checkbox
            $('i[data-catid=' + id + ']').addClass('disabled');
            $('input[data-catid=' + id + ']').attr({'checked': false, 'disabled': true});
            $.uniform.update($('input[data-catid=' + id + ']'));
            var totalCnt = $('select[data-catid=' + id + '] option.multiple').length;
            if (subIds == 'all') {
                selectedCount = totalCnt;
                //selectedHtml = $('select[data-catid=' + id + '] option.single').html();
                $('select[data-catid=' + id + '] option.multiple').each(function() {
                    selectedHtml += (selectedHtml == '') ? $(this).html() : ('&lt;br&gt;' + $(this).html());
                });
            } else {
                var subIdsArray = subIds.split(/,/g);
                selectedCount = subIdsArray.length;
                var selectValues = [];
                var selectHtml = {};
                $('select[data-catid=' + id + '] option.multiple').each(function() {
                    selectValues.push($(this).val());
                    selectHtml[$(this).val()] = $(this).html();
                });
                $.each(subIdsArray, function(keyVal, subId) {
                    if ($.inArray(subId, selectValues) != -1) {
                        selectedHtml += (selectedHtml == '') ? selectHtml[subId] : ('&lt;br&gt;' + selectHtml[subId]);
                    } else {
                        selectedCount = selectedCount - 1;
                    }
                });
            }
            if (selectedCount == 1) {
             
                var currSelectedHtml = selectedHtml;
                selectedHtml = labelName.replace(/"/g, '&quot;');
                labelName = currSelectedHtml;
                displayInfoIcon = false;
            }
        } else {
            $('i.selectitem[type=' + orgType + '][id=' + id + ']').parent().addClass('disabled fg-itemdisabled');
            $('i.selectitem[type=' + orgType + '][id=' + id + ']').removeClass('fg-plus-circle');
            $('i.selectitem[type=' + orgType + '][id=' + id + ']').addClass('fa-minus-circle fg-removeperitem');
        }
        selectedHtml = selectedHtml.replace(/"/g, '');
        if ((labelName != '') && (typeof labelName != 'undefined')) {
            if (type == 'RF') {
                labelName += " (+ " + transFunctions + ")";
            }
            if ((orgType == 'R') || (orgType == 'RF')) {
                if (selectedCount != 1) {
                    labelName += ' [<span class="popovers fg-dotted-br" data-trigger="hover" data-placement="auto" data-content="' + selectedHtml + '" data-html="true">' + selectedCount + '/' + totalCnt + '</span>]';
                }
                if (selectedHtml != '') {
                    if (displayInfoIcon) {
//                        labelName += ' <i class="fa fa-info-circle fg-popover-cion popovers" data-trigger="hover" data-placement="bottom" data-content="' + selectedHtml + '" data-html="true"></i>';
                    } else {
                        labelName = '<span class="popovers fg-dotted-br" data-trigger="hover" data-placement="auto" data-content="' + selectedHtml + '" data-html="true">' + labelName + '</span>';
                    }
                }
            }
            if (type == 'CF') {
                if ($.inArray(id, corrAddrFieldIds) != -1) {
                    labelName ;
                }
//                if ($.inArray(id, invAddrFieldIds) != -1) {
//                    labelName += '<span class="fg-left-exportblk"><i class="fa fa-money"></i></span>';
//                }
            }
            var jsonData = {id: id, type: type, club_id: clubId, sub_ids: subIds, team_rolecat_ids: teamRoleCatIds, labelName: labelName, is_fed_cat: isFedCat};
            FgInternalColumnSettings.renderColumns('template-display-selectedcolumns', 'displaySelectedColumns', jsonData, true);
            $('.popovers').popover();
        }
    },
    saveSettings: function(obj) {
      
        var saveType = $(obj).attr('data-save-type');
        if (saveType == 'SAVE') {
            if ($('input[data-function=settings_name]').val().trim() == '') {
                $('#savesetting_error').removeClass('hide');
                return false;
            }
        } else if (saveType == 'UPDATE') {
            if ($('select#saved_settings').val() == '') {
                $('#selectsetting_error').removeClass('hide');
                return false;
            }
        }
        $('div.selectedrow input').addClass('fairgatedirty');
        /** adding sort order **/
        var sortOrder = 0;
        $('div.selectedrow').each(function() {
            sortOrder++;
            $(this).find('input').each(function() {
                if ($(this).attr('data-sortorder-added') == '0') {
                    $(this).attr({'id': sortOrder + '_' + this.id, 'name': sortOrder + '_' + this.name, 'data-key': sortOrder + '.' + $(this).attr('data-key'), 'value': $(this).attr('data-value'), 'data-sortorder-added': '1'});
                    if (($(this).attr('data-type') == 'alias') && (($(this).attr('data-field-type') == 'R') || ($(this).attr('data-field-type') == 'RF'))) {
                        $(this).val($(this).val() + '_' + sortOrder);
                    }
                }
            });
        });
        /************************/        
        //parse the all form field value as json array and assign that value to the array
        var objectGraph = {};
        objectGraph=  FgInternalParseFormField.fieldParse();
        $.each(objectGraph, function(key, val) {
            if (objectGraph[key]['team_rolecat_ids'] != undefined) {
                objectGraph[key]['team_rolecat_ids'] = $.parseJSON(objectGraph[key]['team_rolecat_ids']);
            }
        });
        var settingsData = JSON.stringify(objectGraph);
        if (saveType == 'APPLY') {
          
            localStorage.setItem(tablesettingValue, settingsData);
            $('form').trigger("reset"); //to avoid dirty form leave page alert
            //redirect to contact listing page
            location.href = clickPath;
        } 
    },
    renderColumns: function(templateScriptId, parentDivId, jsonData, append) {
        var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
        if (append) {
            $('#' + parentDivId).append(htmlFinal);
        } else {
            $('#' + parentDivId).html(htmlFinal);
        }
    }
    
};

FgColumnSettings = {
    /* function to handle multi-select dropdown */
    handleSelectPicker: function() {
       
        $('.single').on('click', function() {
            $(this).parents('.bootstrap-select').parent().find('select.selectpicker').selectpicker('deselectAll');
        });
        $('.multiple').on('click', function() {
              
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
        $('.selectall').on('click', function() {
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
    showSelectAllTitle: function(parentElem, type) {
        var html = (type == 'all') ? all : none;
        setTimeout(function() {
            parentElem.find('.filter-option').html(html);
        }, 20);
    }
};
