$(function() {
    
    $.getJSON(getSettingsPath, function(fields) {
        /* display contact fields */
        var cf1 =  _.without(fields.CF.entry, _.findWhere(fields.CF.entry, {
                        id: 'joining_date'
                    }));
        var cf2 =  _.without(cf1, _.findWhere(cf1, {
                        id: 'leaving_date'
                    }));

         contactFieldData = {clubId: clubId, contactFields: cf2};
    
        FgContactColumnSettings.renderColumns('template-display-contactfields', 'displayContactFields', contactFieldData);
         assignmentArray = [];
         var k =1;
         teamCat = [];
        $.each(fields.TEAM.entry, function(i, item) {
            vr1 = item.input;
            
             $.each(vr1, function(q, p) {
                 if(p.id!='any'){
                    
                   if(p.category==null){
                        catId = parseInt(p.categoryId);
                         console.log(catId);
                        rolecategoryArr  = _.findWhere(teamCat,{categoryId:catId});
                        if(typeof rolecategoryArr!== 'undefined'){
                           p.category = rolecategoryArr['category'];
                        }
                      
                   }else{
                       tcatId = parseInt(p.categoryId);
                       troleId = parseInt(p.category);
                      teamCat.push({'category':troleId,'categoryId':tcatId}); 
                   }
                   
                  if(p.category!=null){ 
                         assignmentArray.push({'catClubId':clubId,'categoryId':p.category,'functionAssign':item.functionAssign,'isFedCat':0,'roleId':p.id,'roleTitle':p.title,'sort':k,'teamCatId':item.id});   
                        k++; 
                    }
                 }
             });
        });
        var k =1;
        $.each(fields.WORKGROUP.entry, function(i, item) {
            var vr2 = item.input;
             $.each(vr2, function(q, p) {
              if(p.id!='any'){
                   if(item.category!=null){   
                        assignmentArray.push({'catClubId':clubId,'categoryId':item.category,'functionAssign':item.functionAssign,'isFedCat':0,'roleId':p.id,'roleTitle':p.title,'sort':k,'teamCatId':item.id});   
                        k++;
                    }
                }
             });
        });
         var k =1;
         keyvar = 'ROLES-'+clubId;
       if(typeof (fields[keyvar])!='undefined') 
        { $.each(fields[keyvar]['entry'], function(i, item) {
                var vr2 = item.input;
                 $.each(vr2, function(q, p) {
                  if(p.id!='any'){
                         assignmentArray.push({'catClubId':clubId,'categoryId':item.id,categoryTitle:item.title,'functionAssign':item.functionAssign,'isFedCat':0,'roleId':p.id,'roleTitle':p.title,'sort':k,'teamCatId':""});   
                        k++;
                    }
                 });
            });
        
        }
       
         /* display role categories */
        
        datasss = $.parseJSON(clubData);
         if((currClubType=='federation')||(currClubType=='standard_club')){
             datasss=[clubId];
         }else if(currClubType=="sub_federation"){
             datasss.push(clubId);
         }
   
        $.each(datasss, function(i, itemC) {
            keyvar = 'FROLES-'+itemC;
             var k =1;
             
            if(typeof (fields[keyvar])!='undefined') 
            {
                $.each(fields[keyvar]['entry'], function(i, item) {
                   var vr2 = item.input;
                    $.each(vr2, function(q, p) {
                       if(p.id!='any'){
                          assignmentArray.push({'catClubId':itemC,'categoryId':p.categoryId,categoryTitle:item.title,'functionAssign':item.functionAssign,'isFedCat':"1",'roleId':p.id,'roleTitle':p.title,'sort':k,'teamCatId':""});   
                          k++;
                       }
                    });
               });
            }
        });
        
         
        
         var assignmentData = {assignmentFields: assignmentArray};
       
        FgContactColumnSettings.renderColumns('template-display-assignmentfields', 'displayAssignmentFields', assignmentData);
        $('#displayStaticFields').removeClass('hide');
        //show current table settings
        var selectedSettings = localStorage.getItem('tableSettingValue_' +contactType+ clubId + '-' + contactId) ? localStorage.getItem('tableSettingValue_' + contactType+ clubId + '-' + contactId) : defaultSettingsArray;
        if (!((selectedSettingId == '') || (selectedSettingId == '0'))) {
            selectedSettings = selectedSettingsArray;
        }
        if (selectedSettings.substring(0, 1) == '"') {
            selectedSettings = selectedSettings.substring(1, (selectedSettings.length - 1))
        }
        var allFixedFields = [];
        
        //To show with this order we have to kept it seprate assignment
        if((currClubType=='federation')||(currClubType=='sub_federation')){
              allFixedFields =  [{'value':fields.FM.entry ,'category':'FM','title':fields.FM.title } ,{'value':fields.SI.entry ,'category':'SI','title':fields.SI.title},{'value':fields.FI.entry ,'category':'FI','title':fields.FI.title},{'value':fields.CO.entry ,'category':'CO','title':fields.CO.title}
                                ,{'value':fields.AF.entry ,'category':'AF','title':fields.AF.title},{'value':fields.CC.entry ,'category':'CC','title':fields.CC.title}];
        }else if((currClubType=='sub_federation_club')||(currClubType=='federation_club')){
            if(clubMembershipAvailable=="1"){
                allFixedFields.push({'value':fields.CM.entry ,'category':'CM','title':fields.CM.title});
            }
              allFixedFields .push({'value':fields.FM.entry ,'category':'FM','title':fields.FM.title } ,{'value':fields.SI.entry ,'category':'SI','title':fields.SI.title},{'value':fields.CO.entry ,'category':'CO','title':fields.CO.title}
                                ,{'value':fields.AF.entry ,'category':'AF','title':fields.AF.title},{'value':fields.CC.entry ,'category':'CC','title':fields.CC.title});
            
        }else{
              allFixedFields =  [{'value':fields.CM.entry ,'category':'CM','title':fields.CM.title},{'value':fields.SI.entry ,'category':'SI','title':fields.SI.title},{'value':fields.CO.entry ,'category':'CO','title':fields.CO.title}
                                ,{'value':fields.AF.entry ,'category':'AF','title':fields.AF.title},{'value':fields.CC.entry ,'category':'CC','title':fields.CC.title}];
        
        }
        
       var systemData = {systemFields: allFixedFields};
        FgContactColumnSettings.renderColumns('template-display-staticfields', 'displayStaticFields', systemData);
        $('#displayStaticFields').removeClass('hide');  
        selectedSettings = $.parseJSON(selectedSettings);
        $.each(selectedSettings, function(sortOrder, selectedSetting) {
            var orgType = (selectedSetting.type == 'RF') ? 'R' : selectedSetting.type;
            var settingClubId = ((selectedSetting.type == 'R') || (selectedSetting.type == 'RF')) ? selectedSetting.club_id : clubId;
             var teamRleCatIds = (selectedSetting.team_rolecat_ids == '') ? '' : JSON.stringify(selectedSetting.team_rolecat_ids);
            FgContactColumnSettings.displaySelectedColumns(selectedSetting.id, selectedSetting.type, settingClubId, orgType, selectedSetting.sub_ids, teamRleCatIds, selectedSetting.is_fed_cat);
        });
        FgContactColumnSettings.initPageFunctions();
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
          
            FgContactColumnSettings.displaySelectedColumns(this.id, selType, $(this).attr('club_id'), $(this).attr('type'), subIds, teamRoleCatIds, $(this).attr('data-is-fed-cat'));
            FgDragAndDrop.init('#displaySelectedColumns');
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
        FgContactColumnSettings.saveSettings(this);
        return false;
    });
    //save settings
    $('form').on('click', '#savesettings', function() {
        FgContactColumnSettings.saveSettings(this);
        return false;
    });
    //delete settings
    $('form').on('click', '#deletesettings', function() {
        settingsId = $('select#saved_settings').val();
        if (settingsId == '') {
            $('#selectsetting_error').removeClass('hide');
            return false;
        }
        FgXmlHttp.post(deleteUrl, {'settings_id': settingsId, contacttype: contactType} , false, FgContactColumnSettings.initPageFunctions);
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
    /* load selected table settings */
    $('form').on('change', 'select#saved_settings', function() {
        var url = $(this).find('option:selected').attr('data-href');
        FgXmlHttp.replaceContentFromUrl(url);
    });
    //cancel settings
    $(document).on('click', '#cancelsettings', function() {
        location.href = clickPath;
    });
    
});

FgContactColumnSettings = {
    initPageFunctions: function() {
        FgApp.init();
        Metronic.init();
        FgColumnSettings.handleSelectPicker();
        FgDragAndDrop.init('#displaySelectedColumns');
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
                    labelName += '<span class="fg-left-exportblk"><i class="fa fa-home"></i></span>';
                }
                if ($.inArray(id, invAddrFieldIds) != -1) {
                    labelName += '<span class="fg-left-exportblk"><i class="fa fa-money"></i></span>';
                }
            }
            
            if (type == 'FM' && (labelName =='First joining date' || labelName =='Latest joining date' || labelName =='Leaving date')) {
               
                labelName =  labelName+' ('+federationTerminology+')';
             }
            var jsonData = {id: id, type: type, club_id: clubId, sub_ids: subIds, team_rolecat_ids: teamRoleCatIds, labelName: labelName, is_fed_cat: isFedCat};
           
            FgContactColumnSettings.renderColumns('template-display-selectedcolumns', 'displaySelectedColumns', jsonData, true);
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
        var settingsId = '';
        if (saveType == 'UPDATE') {
            settingsId = $('select#saved_settings').val();
        } 
        var settingsName = (saveType == 'SAVE') ? $('input[data-function=settings_name]').val() : '';
        //parse the all form field value as json array and assign that value to the array
        var objectGraph = {};
        objectGraph=  FgParseFormField.fieldParse();
        $.each(objectGraph, function(key, val) {
            if (objectGraph[key]['team_rolecat_ids'] != undefined) {
                objectGraph[key]['team_rolecat_ids'] = $.parseJSON(objectGraph[key]['team_rolecat_ids']);
            }
        });
         settingsData = JSON.stringify(objectGraph);
        if (saveType == 'APPLY') {
        
            localStorage.setItem('tableSettingValue_' +contactType + clubId + '-' + contactId, settingsData);
            localStorage.setItem('tableSettingId_' +contactType + clubId + '-' + contactId, '');
            $('form').trigger("reset"); //to avoid dirty form leave page alert
            //redirect to contact listing page
           location.href = clickPath;
        } else {
            var tablesettingsData = {save_type: saveType, settings_id: settingsId, contacttype: contactType ,settings_type: settingsType, settings_name: settingsName, settings_data: settingsData};
            FgXmlHttp.post(updatePath, {'tablesettingsData': tablesettingsData} , false, FgContactColumnSettings.initPageFunctions);
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
