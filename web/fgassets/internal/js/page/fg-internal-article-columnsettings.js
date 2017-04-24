$(document).ready(function() {
    FgArticleColumnSettings.templateBind();
    FgArticleColumnSettings.initPageFunctions();
    FgArticleColumnSettings.selectItem();
    FgArticleColumnSettings.removeItem();

    $(document).off('click', '#applysettings');
    //apply settings
    $(document).on('click', '#applysettings', function() {
        FgArticleColumnSettings.applySettings(this);
        return false;
    });
    //cancel settings
    $(document).on('click', '#cancelsettings', function() {
        location.href = redirectPage;
    });
});
FgArticleColumnSettings = {
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
    templateBind: function() {
        //display 1st column -settingsData
        var settingsOptions = (typeof(filterData.SETTINGS) == "undefined")? null:filterData.SETTINGS;
        var settingsData = {'settingsOptions': settingsOptions };
        FgArticleColumnSettings.renderColumns('template-display-settings-options', 'displaySettingsOptions', settingsData);
        //display 2nd column - editingData
        var editingOptions = (typeof(filterData.EDITING) == "undefined")? null: filterData.EDITING;
        var editingData = { 'editingOptions':editingOptions };
        FgArticleColumnSettings.renderColumns('template-display-editing-options', 'displayEditingOptions', editingData);
        //display 3rd column - contentdata
        var contentOptions = (typeof(filterData.CONTENT) == "undefined")? null:filterData.CONTENT;
        var contentdata = {'contentOptions': contentOptions };
        FgArticleColumnSettings.renderColumns('template-display-content-options', 'displayContentOptions', contentdata);

        var selectedSettings = localStorage.getItem('articleInternaltableSettingValue_' + type + clubId +'-' +contactId) ? localStorage.getItem('articleInternaltableSettingValue_' + type + clubId +'-' +contactId) : defaultSettingsArray;
        selectedSettings = $.parseJSON(selectedSettings);
        $.each(selectedSettings, function(sortOrder, selectedSetting) {
            var orgType = selectedSetting.type;
            var settingClubId =  clubId;
            FgArticleColumnSettings.displaySelectedColumns(selectedSetting.id, selectedSetting.type, settingClubId, orgType);
        });
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
            var jsonData = {id: id, type: typee, club_id: clubId, labelName: labelName,  articleType:type};
            FgArticleColumnSettings.renderColumns('template-display-selectedcolumns', 'displaySelectedColumns', jsonData, true);
        }
    },
    //function to select item on click
    selectItem: function() {
        $('form').on('click', 'i.selectitem', function() {
            if (!($(this).hasClass('disabled') || $(this).parent().hasClass('disabled'))) {
                var selType = $(this).attr('type');
                FgArticleColumnSettings.displaySelectedColumns(this.id, selType, $(this).attr('club_id'), $(this).attr('type'));
                FgInternalDragAndDrop.init('#displaySelectedColumns');
                $('.popovers').popover();
            }
        });
    },
        
    //function to remove column on click
    removeItem: function() {
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
                     if ($(this).attr('data-type') == 'alias') {
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
            localStorage.setItem('articleInternaltableSettingValue_' + type + clubId +'-' +contactId, settingsData);
            $('form').trigger("reset"); 
            //redirect to club listing page
            location.href = redirectPage;
        } 
    }
        
}
