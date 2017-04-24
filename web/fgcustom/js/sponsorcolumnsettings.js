FgSponsorColumnSettings = {
    initPageFunctions: function() {
        FgApp.init();
        Metronic.init();
        FgColumnSettings.handleSelectPicker();
        FgDragAndDrop.init('#displaySelectedColumns');
    },
    /* function to display columns */
    renderColumns: function(templateScriptId, parentDivId, jsonData, append) {
        var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
        if (append) {
            $('#' + parentDivId).append(htmlFinal);
        } else {
            $('#' + parentDivId).html(htmlFinal);
        }
    },
    /* function to display selected columns */
    displaySelectedColumns: function(id, type, clubId, orgType, subIds) {
        var selectedCount = 0;
        var selectedHtml = '';
        var labelName = $('span[data-label-id=' + orgType + id + ']').html();
        var displayInfoIcon = true;
        if (orgType == 'SS') {
            //disable add icon 
            $('i[data-clasftnid=' + id + ']').addClass('disabled');
            var totalCnt = $('select[data-clasftnid=' + id + '] option.multiple').length;
            if (subIds == 'all') {
                selectedCount = totalCnt;
                //selectedHtml = $('select[data-clasftnid=' + id + '] option.single').html();
                $('select[data-clasftnid=' + id + '] option.multiple').each(function() {
                    selectedHtml += (selectedHtml == '') ? $(this).html() : ('&lt;br&gt;' + $(this).html());
                });
            }
            else {
                var subIdsArray = subIds.split(/,/g);
                selectedCount = subIdsArray.length;
                var selectValues = [];
                var selectHtml = {};
                $('select[data-clasftnid=' + id + '] option.multiple').each(function() {
                    selectValues.push($(this).val());
                    selectHtml[$(this).val()] = $(this).html();
                });
                $.each(subIdsArray, function(keyVal, subId) {
                    if ($.inArray(subId, selectValues) != -1) {
                        selectedHtml += (selectedHtml == '') ? selectHtml[subId] : ('&lt;br&gt;' + selectHtml[subId]);
                    }
                    else {
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
            if(selectedCount == 0){
               subIds = 'all';
               selectedCount = $('select[data-clasftnid=' + id + '] option.multiple').length;
               $('select[data-clasftnid=' + id + '] option.multiple').each(function() {
                selectedHtml += (selectedHtml == '') ? $(this).html() : ('&lt;br&gt;' + $(this).html());
               });
            }
        }
        else {
            $('i.selectitem[type=' + orgType + '][id=' + id + ']').parent().addClass('disabled fg-itemdisabled');
            $('i.selectitem[type=' + orgType + '][id=' + id + ']').removeClass('fg-plus-circle');
            $('i.selectitem[type=' + orgType + '][id=' + id + ']').addClass('fa-minus-circle fg-removeperitem');
        }
        selectedHtml = selectedHtml.replace(/"/g, '');
        if ((labelName != '') && (typeof labelName != 'undefined')) {
            if (orgType == 'SS') {
                if (selectedCount != 1) {
                    if(subIds != 'all' && selectedCount != 0){
                        labelName += ' [<span class="popovers fg-dotted-br" data-trigger="hover" data-placement="auto" data-content="' + selectedHtml + '" data-html="true">' + selectedCount + '/' + totalCnt + '</span>]';
                    }else{
                        labelName += ' [<span class="popovers fg-dotted-br" data-trigger="hover" data-placement="auto" data-content="' + selectedHtml + '" data-html="true">'+datatabletranslations['All']+ '</span>]';
                    }
                }
                if (selectedHtml != '') {
                    if(displayInfoIcon){
//                        labelName += ' <i class="fa fa-info-circle fg-popover-cion popovers" data-trigger="hover" data-placement="bottom" data-content="' + selectedHtml + '" data-html="true"></i>';
                    } else {
                        labelName = '<span class="popovers fg-dotted-br" data-trigger="hover" data-placement="auto" data-content="' + selectedHtml + '" data-html="true">' + labelName + '</span>';
                    }
                }
            }
            
            if (type == 'CF') {
                if ($.inArray(id, corrAddrField) != -1) {
                    labelName += '<span class="fg-left-exportblk"><i class="fa fa-home"></i></span>';
                }
                if ($.inArray(id, invAddrField) != -1) {
                    labelName += '<span class="fg-left-exportblk"><i class="fa fa-money"></i></span>';
                }
            }
            var jsonData = {id: id, type: type, club_id: clubId, sub_ids: subIds, labelName: labelName};

            FgSponsorColumnSettings.renderColumns('template-display-selectedcolumns', 'displaySelectedColumns', jsonData, true);
        }
    },
    //save or update table settings
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
                     if (($(this).attr('data-type') == 'alias') && ($(this).attr('data-field-type') == 'SS') ) {
                        $(this).val($(this).val() + '_' + sortOrder);
                    }
                }
            });
        });
        /************************/
        var settingsId = '';
        if (saveType == 'UPDATE') {
            settingsId = $('select#saved_settings').val();
        }
        var settingsName = (saveType == 'SAVE') ? $('input[data-function=settings_name]').val() : '';
        //parse all form field values as json array and assign that value to the array
        var objectGraph = {};
        objectGraph = FgParseFormField.fieldParse();
        var settingsData = JSON.stringify(objectGraph);
        if (saveType == 'APPLY') {
            localStorage.setItem('tableSettingValue_'+contactType+clubId + '-' + contactId, settingsData);
            localStorage.setItem('SponsorSettingId_'+contactType+clubId + '-' + contactId, '');
            $('form').trigger("reset"); //to avoid dirty form leave page alert
            //redirect to club listing page
            location.href = sponsorHomePage;

        } else {
            var tablesettingsData = {save_type: saveType, settings_id: settingsId, settings_name: settingsName, settings_data: settingsData,contact_type:contactType};
            FgXmlHttp.post(updateSponsorColumnSettings, {'tablesettingsData': tablesettingsData}, false, FgSponsorColumnSettings.initPageFunctions);
        }
    }
};
    /* on document ready*/
    $(document).ready(function() {
        $.getJSON(filterDataPath, function(data) {
            jsonData = data;
            //display 1st column - club data
            var contactFieldData = {'contactFields':jsonData.CF.entry, 'profPicFields':jsonData.G.entry}; 
            FgSponsorColumnSettings.renderColumns('template-display-contactfields', 'displayContactFields', contactFieldData);
            //contact option
//            if(jsonData['CO']){
//            var contactOption = {'contactOptions':jsonData['CO'] };  
//            FgSponsorColumnSettings.renderColumns('template-display-contactoptions', 'displayContactoption', contactOption);
//            }
            //display 2nd column - 
            if(jsonData['SS']){
            var sponsorServicesData = {'assignmentFields':jsonData['SS'].entry,'sponserAnalysis':jsonData['SA'],'showFilter':jsonData['SS'].show_filter };  
            FgSponsorColumnSettings.renderColumns('template-display-servicefields', 'displayServiceFields', sponsorServicesData);
            }
             var selectedSettings = localStorage.getItem('tableSettingValue_'+contactType+clubId + '-' + contactId) ? localStorage.getItem('tableSettingValue_'+contactType+ clubId + '-' + contactId) : defaultSettingsArray;
            if (!((selectedSettingId == '') || (selectedSettingId == '0'))) {
                selectedSettings = selectedSettingsArray;
            }
            if (selectedSettings.substring(0,1) == '"') {
                selectedSettings = selectedSettings.substring(1, (selectedSettings.length-1))
            }
            selectedSettings = $.parseJSON(selectedSettings);
            $.each(selectedSettings, function(sortOrder, selectedSetting) {
                var orgType = selectedSetting.type;
                var settingClubId = (selectedSetting.type == 'SS') ? selectedSetting.club_id : clubId;
                
                FgSponsorColumnSettings.displaySelectedColumns(selectedSetting.id, selectedSetting.type, settingClubId, orgType, selectedSetting.sub_ids);
            });
        FgSponsorColumnSettings.initPageFunctions();   
        });
    
       //function to select item on click
        $('form').on('click', 'i.selectitem', function() {
            if (!($(this).hasClass('disabled') || $(this).parent().hasClass('disabled'))) {
                var subIds = '';
                var selType = $(this).attr('type');
                if ($(this).attr('type') == 'SS'){
                    var selectData = $('select[data-clasftnid='+this.id+']').val();
                    if (!$.inArray('selectall', selectData)) {
                        //if 'select all' is selected
                        selectData.splice($.inArray('selectall', selectData),1);
                        //reset classes dropdown
                        $('select[data-clasftnid='+this.id+']').parent().find('a.selectall').trigger('click'); //de-select all
                    } else {
                        //reset classes dropdown
                        $('select[data-clasftnid='+this.id+']').parent().find('a.selectall').trigger('click'); //select all
                        $('select[data-clasftnid='+this.id+']').parent().find('a.selectall').trigger('click'); //de-select all
                    }
                    subIds = selectData.toString();
                }
                
            FgSponsorColumnSettings.displaySelectedColumns(this.id, selType, $(this).attr('club_id'), $(this).attr('type'), subIds);
            FgDragAndDrop.init('#displaySelectedColumns');
            $('.popovers').popover();
            }
        });
        
        //function to remove column on click
        $('form').on('click', 'i.removeitem,i.fa-minus-circle.fg-removeperitem', function() {
            var id = $(this).attr('id');
            var type = $(this).attr('type');
            if (type != 'SS') {
                $('i[id='+id+'][type='+type+']').parent().removeClass('disabled fg-itemdisabled');
                $('i[id='+id+'][type='+type+']').removeClass('fa-minus-circle');
                $('i[id='+id+'][type='+type+']').addClass('fg-plus-circle');
            }
            var elem = $(this).hasClass('removeitem') ? $(this) : $('i.removeitem[id='+id+'][type='+type+']');
            elem.parents('.selectedrow').remove();
        });
        //trigger the function to remove column on click 
        $('form').on('click', 'a#clearselected', function() {
            $('i.removeitem').trigger('click');
        });
        $(document).off('click', '#applysettings');
        //apply settings
        $(document).on('click', '#applysettings', function() {
            FgSponsorColumnSettings.saveSettings(this);
            return false;
        });
        //save settings
        $('form').on('click', '#savesettings', function() {
            FgSponsorColumnSettings.saveSettings(this);
            return false;
        });
        //delete settings
        $('form').on('click', '#deletesettings', function() {
            settingsId = $('select#saved_settings').val();
            if (settingsId == '') {
                $('#selectsetting_error').removeClass('hide');
                return false;
            }
            FgXmlHttp.post(deleteSponsorColumnSettings, {'settings_id': settingsId,'contacttype':contactType} , false, FgSponsorColumnSettings.initPageFunctions);
        });
        
        /* activate plus button  on selecting class */
        $('form').on('change', 'select[data-clasftnid]', function() {
            if ($(this).val()) {
                $(this).parent().parent().find('i.fg-plus-circle').removeClass('disabled');
            } else {
                $(this).parent().parent().find('i.fg-plus-circle').addClass('disabled');
            }
        });
        
        /* load selected table settings */
        $('form').on('change', 'select#saved_settings', function() {
            var url = $(this).find('option:selected').attr('data-href');
            FgXmlHttp.replaceContentFromUrl(url);
        });
        
        //cancel settings
        $(document).on('click', '#cancelsettings', function() {
            location.href = sponsorHomePage;
        });
    });
