FgInternalColumnSettings = {
    initPageFunctions: function() {
        FgInternalDragAndDrop.init('#displaySelectedColumns');
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
    displaySelectedColumns: function(id, typee, clubId, orgType) {
        var selectedHtml = '';
        var labelName = $('span[data-label-id=' + orgType +"_"+ id + ']').html();
        $('i.selectitem[type=' + orgType + '][id=' + id + ']').parent().addClass('disabled fg-itemdisabled');
        $('i.selectitem[type=' + orgType + '][id=' + id + ']').removeClass('fg-plus-circle');
        $('i.selectitem[type=' + orgType + '][id=' + id + ']').addClass('fa-minus-circle fg-removeperitem');
        selectedHtml = selectedHtml.replace(/"/g, '');
        if ((labelName != '') && (typeof labelName != 'undefined')) {
            var jsonData = {id: id, type: typee, club_id: clubId, labelName: labelName,docType:type};
             
            FgInternalColumnSettings.renderColumns('template-display-selectedcolumns', 'displaySelectedColumns', jsonData, true);
        }
    },
    //save or update table settings
    applySettings: function(obj) {
        var saveType = $(obj).attr('data-save-type');
        
        $('div.selectedrow input').addClass('fairgatedirty');
        /** adding sort order **/
        var sortOrder = 0;
        $('div.selectedrow').each(function() {
            sortOrder++;
            $(this).find('input').each(function() {
                if ($(this).attr('data-sortorder-added') == '0') {
                    $(this).attr({'id': sortOrder + '_' + this.id, 'name': sortOrder + '_' + this.name, 'data-key': sortOrder + '.' + $(this).attr('data-key'), 'value': $(this).attr('data-value'), 'data-sortorder-added': '1'});
                     if (($(this).attr('data-type') == 'alias') && ($(this).attr('data-field-type') == 'CL') ) {
                        $(this).val($(this).val() + '_' + sortOrder);
                    }
                }
            });
        });
       
        //parse all form field values as json array and assign that value to the array
        var objectGraph = {};
        objectGraph = FgInternalParseFormField.fieldParse();
        var settingsData = JSON.stringify(objectGraph);
        if (saveType == 'APPLY') {
            localStorage.setItem('documentInternaltableSettingValue_' + type + clubId +'-' +contactId, settingsData);
            $('form').trigger("reset"); 
            //redirect to club listing page
            location.href = redirectPage;

        } 
    }
};
    /* on document ready*/
    $(document).ready(function() {
        
        $.getJSON(filterDocData, function(data) {
            jsonData = data;
            //display 1st column -fileData
            var fileOptions = (typeof(jsonData.FILE) == "undefined")? null:jsonData.FILE;
                var fileData = {'fileOptions': fileOptions };
               
                FgInternalColumnSettings.renderColumns('template-display-file-options', 'displayFileOptions', fileData);
            //display 2nd column - dateData
            var DateOptions = (typeof(jsonData.DATE) == "undefined")? null: jsonData.DATE;
                var dateData = { 'DateOptions':DateOptions };               
                FgInternalColumnSettings.renderColumns('template-display-date-options', 'displayDateOptions', dateData);
            //display 3rd column - user data 
            var UserOptions = (typeof(jsonData.USER) == "undefined")? null:jsonData.USER;
                var userData = {'UserOptions': UserOptions };  
                FgInternalColumnSettings.renderColumns('template-display-user-options', 'displayUserOptions', userData);

            var selectedSettings = localStorage.getItem('documentInternaltableSettingValue_' + type + clubId +'-' +contactId) ? localStorage.getItem('documentInternaltableSettingValue_' + type + clubId +'-' +contactId) : defaultSettingsArray;
            selectedSettings = $.parseJSON(selectedSettings);
            
            $.each(selectedSettings, function(sortOrder, selectedSetting) {
                var orgType = selectedSetting.type;
                var settingClubId =  clubId;
                FgInternalColumnSettings.displaySelectedColumns(selectedSetting.id, selectedSetting.type, settingClubId, orgType);
            });
            
        FgInternalColumnSettings.initPageFunctions();   
        });
    
       //function to select item on click
        $('form').on('click', 'i.selectitem', function() {
            if (!($(this).hasClass('disabled') || $(this).parent().hasClass('disabled'))) {
                var selType = $(this).attr('type');
                FgInternalColumnSettings.displaySelectedColumns(this.id, selType, $(this).attr('club_id'), $(this).attr('type'));
                FgInternalDragAndDrop.init('#displaySelectedColumns');
                $('.popovers').popover();
            }
        });
        
        //function to remove column on click
        $('form').on('click', 'i.removeitem,i.fa-minus-circle.fg-removeperitem', function() {
            var id = $(this).attr('id');
            var type = $(this).attr('type');
            $('i[id='+id+'][type='+type+']').parent().removeClass('disabled fg-itemdisabled');
            $('i[id='+id+'][type='+type+']').removeClass('fa-minus-circle');
            $('i[id='+id+'][type='+type+']').addClass('fg-plus-circle');
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
            FgInternalColumnSettings.applySettings(this);
            return false;
        });
        
        //cancel settings
        $(document).on('click', '#cancelsettings', function() {
            location.href = redirectPage;
        });
    });


