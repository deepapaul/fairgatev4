/**
 * FgAssignMembership
 * 
 * Assign memberships to contacts
 *  
 * 
 */
FgAssignMembership  = {
    init: function(){
        
        FgAssignMembership.displayDropdownOptions();
        FgAssignMembership.displayPopupHeading();
        FgAssignMembership.selectCriterias();
        FgAssignMembership.clickRadioButton();
        FgAssignMembership.changeMembership();
        FgAssignMembership.validate();
        FgAssignMembership.toggle();
        
    },
    calendarInit:function(id) {
        $('#'+id+' input').parent().datepicker(FgApp.dateFormat);
        var currdatetime = moment().format(FgLocaleSettingsData.momentDateTimeFormat);
        $('#'+id).datepicker("setDate", currdatetime);
        $('#'+id).datepicker('setEndDate', currdatetime);
        var pathFirstJoining = firstJoiningDatePath.replace('%23%23', toAssignContacts);
        $.getJSON(pathFirstJoining,function(data){
            var firstJoining = data.firstjoiningdate;
            $('#'+id).datepicker('setStartDate', firstJoining);
        });
        

    },
    clickRadioButton:function(){
        $('.radio-inline').click(function() {
            FgAssignMembership.selectCriterias();
        });
    },
    selectCriterias:function() {
        if ($('input[name=radios]:checked').val() == 1) {
            FgAssignMembership.calendarInit('fg-date-inline-transfer');
            $('.calendar2').addClass('fg-disabled-icon');
            $('.calendar1').removeClass('fg-disabled-icon');
            $('#fg-date-inline-transfer input').prop('readonly', false);
            $('#fg-date-inline-joining1 input').prop('readonly', true);
            $('#fg-date-inline-joining1 input').val('');
            $('.calendar1').prop('disabled', false);
            $('.calendar2').prop('disabled', true);
            $('#fg-date-inline-transfer input').removeClass('fg-disabled-icon');
            $('#fg-date-inline-joining1 input').addClass('fg-disabled-icon');
            $("#fg-date-inline-joining1").datepicker("remove");
        } else if ($('input[name=radios]:checked').val() == 3) {
            FgAssignMembership.calendarInit('fg-date-inline-joining1');
            $('.calendar1').addClass('fg-disabled-icon');
            $('.calendar2').removeClass('fg-disabled-icon');
            $('#fg-date-inline-joining1 input').prop('readonly', false);
            $('#fg-date-inline-transfer input').prop('readonly', true);
            $('#fg-date-inline-transfer input').val('');
            $('.calendar2').prop('disabled', false);
            $('.calendar1').prop('disabled', true);
            $('#fg-date-inline-joining1 input').removeClass('fg-disabled-icon');
            $('#fg-date-inline-transfer input').addClass('fg-disabled-icon');
            $("#fg-date-inline-transfer").datepicker("remove");
        } else {
            $('.calendar2').addClass('fg-disabled-icon');
            $('.calendar1').addClass('fg-disabled-icon');
            $('#fg-date-inline-transfer input').prop('readonly', true);
            $('#fg-date-inline-joining1 input').prop('readonly', true);
            $('.calendar1').prop('disabled', true);
            $('.calendar2').prop('disabled', true);
            $('#fg-date-inline-transfer input').addClass('fg-disabled-icon');
            $('#fg-date-inline-joining1 input').addClass('fg-disabled-icon');
            $('#fg-date-inline-joining1 input').val('');
            $('#fg-date-inline-transfer input').val('');
            $("#fg-date-inline-joining1").datepicker("remove");
            $("#fg-date-inline-transfer").datepicker("remove");
        }
    },
    displayDropdownOptions:function() {
        FgAssignMembership.renderTemplateContent('display_dropdown', {'options': catArray, 'selectedId': selectedCat}, 'category_dropdown');
        $('#popup_contents select').select2(); //for styling
    },
    renderTemplateContent:function(templateScriptId, jsonData, parentDivId) {
        var htmlFinal = FGTemplate.bind(templateScriptId, jsonData);
        $('#' + parentDivId).html(htmlFinal);
    },
    toggle:function(){
        $(document).off('click', '.modal-title .fg-dev-contact-names');
        $(document).on('click', '.modal-title .fg-dev-contact-names', function(e) {
            $(this).parent().toggleClass('fg-arrowicon');
        });
    },
    changeMembership:function(){
        $('form').off('change', 'select#category_dropdown');
        $('form').on('change', 'select#category_dropdown', function() {
            $('#error_assign').addClass('hide');
            selectedCat = $(this).val();
            FgAssignMembership.displayDropdownOptions();
        });
    },
    validate:function(){
        //pop-up buttons click
    $(document).off('click', '#assignmembership');
    $(document).on('click', '#assignmembership', function() {
                $('form#assigncontacts .help-block').remove();
                $('form#assigncontacts .has-error').removeClass('has-error');


                /* validation (req. field) starts */
                var hasError = false;
                if (selectedCat == '') {
                    hasError = true;
                    $('form#assigncontacts select#category_dropdown').parent().addClass('has-error');
                    $('<span class="help-block">'+required+'.</span>').insertAfter($('form#assigncontacts select#category_dropdown'));
                    return false;
                } else {
                    toAssignContacts = toAssignContacts1;
                    _.each(contactMembership, function(val, key) {
                        if ((val.membership == selectedCat) || (selCatType == 'fed_membership' && val.approve == 1)) {
                            toAssignContacts = _.without(toAssignContacts, val.id);
                        }
                    });
                    
                    if (toAssignContacts.length < 1) {
                        $('#error_assign').removeClass('hide');
                        var errorBlockId = $('form#assigncontacts .has-error:first').parents('.form-group').parent().attr('id');
                        FgAssignMembership.displayBlockData();
                        return false;
                    }
                }
                if (selCatType != 'fed_membership') {
                    $("#fg-date-inline-joining-error").addClass('hide');
                    $("#fg-date-inline-transfer-error").addClass('hide');
                    if (nomembership > 0) {
                        if ($('#fg-date-inline-joining input').val() == '') {
                            hasError = true;
                            $('#fg-date-inline-joining-error').removeClass('hide').text(required);
                        } else {
                            var contcts = toAssignContacts.join();
                            var path1 = validateJoiningWithoutMembershipPath.replace('%23%23', contcts);

                             $.ajax({
                                type: 'GET',
                                url: path1,
                                success: function(data) {
                                    var leavingTimestamp11 = parseInt(moment(data['leaving1'], FgLocaleSettingsData.momentDateFormat).format('x'));
                                    var joiningTimestamp11 = parseInt(moment($('#fg-date-inline-joining input').val(), FgLocaleSettingsData.momentDateFormat).format('x'));
                                    if (leavingTimestamp11 > joiningTimestamp11) {
                                        hasError = true;
                                        $('#fg-date-inline-joining input').addClass('has-error');
                                        $('#fg-date-inline-joining-error').removeClass('hide').text(joiningDateRange + ": " + data['leaving1']);
                                    }
                                },
                                async: false
                            });
                        }
                    }
                    if (membership > 0) {
                        if ($('input[name=radios]:checked').val() == 1) {
                            if ($('#fg-date-inline-transfer input').val() == '') {
                                hasError = true;
                                $('#fg-date-inline-transfer-error').removeClass('hide').text(required);
                            } else {
                                var conTcts = toAssignContacts.join();
                                var path = validateTransferMembershipPath.replace('%23%23', conTcts);

                                 $.ajax({
                                    type: 'GET',
                                    url: path,
                                    success: function(data) {
                                        joiningTimestamp1 = parseInt(moment(data['joining1'], FgLocaleSettingsData.momentDateFormat).format('x'));
                                        leavingTimestamp1 = parseInt(moment($('#fg-date-inline-transfer input').val(), FgLocaleSettingsData.momentDateFormat).format('x'));
                                        if (joiningTimestamp1 > leavingTimestamp1) {
                                            $('#fg-date-inline-transfer input').addClass('has-error');
                                            $('#fg-date-inline-transfer-error').removeClass('hide').text(transferDateRange + ": " + data['joining1']);
                                            hasError = true;
                                        }
                                    },
                                    async: false
                                });
                                
                            }

                        } else if ($('input[name=radios]:checked').val() == 3 ) {
                             if ($('#fg-date-inline-joining1 input').val() == '') {
                                $('#fg-date-inline-joining1-error').removeClass('hide').text(required);
                                hasError = true;
                            }else{
                                var contCts = toAssignContacts.join();
                                var path = validateJoiningWithMembershipPath.replace('%23%23', contCts);

                                 $.ajax({
                                    type: 'GET',
                                    url: path,
                                    success: function(data) {
                                        leavingTimestamp12 = parseInt(moment(data['leaving1'], FgLocaleSettingsData.momentDateFormat).format('x'));
                                        joiningTimestamp12 = parseInt(moment($('#fg-date-inline-joining1 input').val(), FgLocaleSettingsData.momentDateFormat).format('x'));
                                        if (joiningTimestamp12 < leavingTimestamp12) {
                                            $('#fg-date-inline-joining1 input').addClass('has-error');
                                            $('#fg-date-inline-joining1-error').removeClass('hide').text(joiningDateRange + ": " + data['leaving1']);
                                            hasError = true;
                                        }
                                    },
                                    async: false
                                });
                            }
                        }
                    }

                }
               

                /* validation (req. field) ends */
                if (hasError) {
                    var errorBlockId = $('form#assigncontacts .has-error:first').parents('.form-group').parent().attr('id');
                    FgAssignMembership.displayBlockData();
                    return false;
                } else {

                    /* save assignment starts */
                   FgAssignMembership.saveAssignments();
                }
            
    });

    },
    displayPopupHeading:function() {
        
        if (selContIds.length == 1) {
            popupHeadText = singleAssignTxt.replace('%contname%', selContNames[0].name);
            $('h4.modal-title').html('<div class="fg-popup-text" id="popup_head_text"></div>');
            $('div#popup_head_text').text(popupHeadText + '...');
        } else {
            popupHeadText = multipleAssignTxt.replace('%contcount%', selContIds.length);
            if (selActionType == 'all') {
                $('h4.modal-title').html('<div class="fg-popup-text" id="popup_head_text"></div>');
                $('div#popup_head_text').text(popupHeadText + '...');
            } else {
                var contNamesHtml = '';
                var contactNameLinks = {};
                var i = 0;
                $.each(selContNames, function(ckey, selContName) {
                    i++;
                    if (i == 11) {
                        contNamesHtml += '<li>&hellip;</li>';
                        return false;
                    } else {
                        contactNameLinks[selContName.id] = selContName.name;
                        contNamesHtml += '<li><a href="overviewcontact/0/' + selContName.id + '" target="_blank" data-cont-id="' + selContName.id + '"></a></li>';
                    }
                });
                $('h4.modal-title').html('<span class="fg-dev-contact-names"><a href="#" class="fg-plus-icon"></a><a href="#" class="fg-minus-icon"></a></span><div class="fg-popup-text" id="popup_head_text"></div>\n\
                    <div class="fg-arrow-sh"><ul>' + contNamesHtml + '</ul></div>');
                $('div#popup_head_text').text(popupHeadText + '...');
                FgAssignMembership.displayContactNames(contactNameLinks);
            }
        }
    },

     displayBlockData:function() {
        //Pop-up Heading
        var dropGroupName = '...';
        $('div#popup_head_text').text(popupHeadText + dropGroupName);

        /** SAVE & CANCEL Buttons **/
//        $('button[data-function=cancel]').attr({'id': 'cancel', 'data-dismiss': 'modal'}).html('{{'CANCEL'|trans}}');
//        $('button[data-function=save]').attr('id', 'save').html('{{'SAVE'|trans}}');

    },

     saveAssignments:function() {
        if (selCatType == 'fed_membership') {
            if(toAssignContacts.length == 1){
                var pathEmail = emailValidationPath.replace('%23%23', toAssignContacts).replace('%2B%2B',selectedCat);
                $.ajax({
                    type: 'GET',
                    url: pathEmail,
                    success: function(data) {
                        if(data.mergeable){
                            FgMergeAssignmentPopup.handleMergerablePopup(data);
                            //$('#popup').modal('hide');
                        }else{
                            if(data.status == 'FAILURE'){
                                $('#error_assign').removeClass('hide');
                                $('form#assigncontacts .has-error:first').parents('.form-group').parent().attr('id');
                                FgAssignMembership.displayBlockData();
                                return false;
                            }else{
                                toAssignContacts = data.contacts;
                                var passingData = {'contact_id': toAssignContacts, 'selCount': 1,
                                'dragCat': dragCat, 'dragCatType': dragCatType,
                                'totalCount': selContIds.length, 'membership': selectedCat, 'type': selCatType,
                                'fromPage': 'contactlist', 'actionType': actionType};

                                FgXmlHttp.post(saveFedMembershipPath, passingData, false, FgAssignMembership.callBackFedmembership);
                                $('#popup').modal('hide');
                            }
                        }
                    },
                    async: false
                });
            }else{
                var pathEmail = emailValidationPath.replace('%23%23', toAssignContacts).replace('%2B%2B',selectedCat);
                $.ajax({
                    type: 'GET',
                    url: pathEmail,
                    success: function(data) {
                        toAssignContacts = data.contacts;
                        if(toAssignContacts.length < 1){
                            $('#error_assign').removeClass('hide');
                            $('form#assigncontacts .has-error:first').parents('.form-group').parent().attr('id');
                            FgAssignMembership.displayBlockData();
                            return false;
                        }else{
                            if(data.mergeable){
                                FgMultipleMergePopup.handleMergerablePopup(data,toAssignContacts);
                            } else {
                                var passingData = {'contact_id': toAssignContacts.join(), 'selCount': toAssignContacts.length,
                                'dragCat': dragCat, 'dragCatType': dragCatType,
                                'totalCount': selContIds.length, 'membership': selectedCat, 'type': selCatType,
                                'fromPage': 'contactlist', 'actionType': actionType};

                                FgXmlHttp.post(saveFedMembershipPath, passingData, false, FgAssignMembership.callBackFedmembership);
                                $('#popup').modal('hide');
                            }
                        }
                    },
                    async: false
                });
            }
        } else {
                if (nomembership > 0) {
                    joiningDate = $('#fg-date-inline-joining input').val();
                } else {
                    joiningDate = '';
                }
                if (membership > 0) {
                    criteria = $('input[name=radios]:checked').val();
                    if ($('input[name=radios]:checked').val() == 1) {
                        transferDate = $('#fg-date-inline-transfer input').val();
                    } else if ($('input[name=radios]:checked').val() == 3) {
                        transferDate = $('#fg-date-inline-joining1 input').val();
                    } else {
                        transferDate = '';
                    }
                }
            var passingData = {'contactids': toAssignContacts.join(), 'selCount': toAssignContacts.length,
                'dragCat': dragCat, 'dragCatType': dragCatType, 'transferDate': transferDate, 'joiningDate': joiningDate, 'criteria': criteria,
                'totalCount': selContIds.length, 'membership': selectedCat, 'type': selCatType,
                'fromPage': 'contactlist', 'actionType': actionType};

            FgXmlHttp.post(clubMembershipSavePath, passingData, false, FgAssignMembership.callBack);
            $('#popup').modal('hide');
        }
        nomembership = membership = 0;
        
    },

    /**
     * callback for clubmembership
     * @param {type} result
     * @returns {undefined}     */
     callBack:function(result) {
        var updateArr = {};
        var i = 0;
        for (key in result.membrshipArray) {
            updateArr[i] = {'categoryId': '', "subCatId": key, 'dataType': 'membership', 'sidebarCount': result['membrshipArray'][key], "action": "remove"};
            i++;
        }

        updateArr[i] = {'categoryId': '', "subCatId": result.membership, 'dataType': 'membership', 'sidebarCount': result.selcount, "action": "add"};

        FgCountUpdate.update('assignment', 'contact', 'active', updateArr, result.selcount);
        oTable.api().draw();
    },

    /**
     * call back for fed memberhsip
     * @param {type} result
     * @returns {undefined}     */
     callBackFedmembership:function(result) {
        var updateArr = {};
        if ((result.dragCatType == 'membership' && result.type == 'membership') || (result.dragCatType == 'fed_membership' && result.type == 'fed_membership')) {
            var updateArr = {"0": {'categoryId': '', "subCatId": result.dragCat, 'dataType': result.type, 'sidebarCount': result.selcount, "action": "remove"},
                "1": {'categoryId': '', "subCatId": result.membership, 'dataType': result.type, 'sidebarCount': result.selcount, "action": "add"}};
        } else {
            var i = 0;
            for (i = 0; i < result.membrshipArray.length; i++) {
                updateArr[i] = {'categoryId': '', "subCatId": result['membrshipArray'][i]['id'], 'dataType': result.type, 'sidebarCount': result['membrshipArray'][i]['count'], "action": "remove"};
            }
            updateArr[i] = {'categoryId': '', "subCatId": result.membership, 'dataType': result.type, 'sidebarCount': result.selcount, "action": "add"};
        }
        FgCountUpdate.update('assignment', 'contact', 'active', updateArr, result.selcount);
        oTable.api().draw();
    },

    displayContactNames:function(contactNameLinks) {
        $.each(contactNameLinks, function(selContId, selContName) {
            $('a[data-cont-id=' + selContId + ']').text(selContName);
        });
    }
};

FgMultipleMergePopup = {
    handleMergerablePopup : function (response) {
        var htmlFinal = _.template($('#merge-multiple-popup-template').html(),{'mergableContacts': response.mergableContacts});
        $('#popup_contents').html(htmlFinal);
        FgMultipleMergePopup.disableDuplicateMerging();
        FgFormTools.handleUniform();
        $('#popup').addClass('fg-membership-merge-modal');
        $('#popup').modal('show');
        FgMultipleMergePopup.mergePopupSubmitHandling();
    },
    mergePopupSubmitHandling:function(){
        $('#cancel_merging').on('click', function() {
            FgUtility.stopPageLoading();
            FgMultipleMergePopup.cancelMerging();
        });
         $('#save_merging').on('click', function() {
            FgUtility.startPageLoading();
            var mergeArray = FgParseFormField.fieldParse();
            var passingData = {'contact_id': toAssignContacts.join(), 'selCount': toAssignContacts.length,
                                'dragCat': dragCat, 'dragCatType': dragCatType,
                                'totalCount': selContIds.length, 'membership': selectedCat, 'type': selCatType,
                                'fromPage': 'contactlist', 'actionType': actionType,'merge':'multiple','mergeData':mergeArray};

            FgXmlHttp.post(saveFedMembershipPath, passingData, false, FgMultipleMergePopup.callBackFedmembershipMerge);
            $('#popup').modal('hide');
        });
    },
    cancelMerging:function(){
        $('#popup').removeClass('fg-membership-merge-modal');
        $('#popup').modal('hide');
    },
    /**
     * call back for fed memberhsip
     * @param {type} result
     * @returns {undefined}     */
     callBackFedmembershipMerge:function(result) {
        var updateArr = {};
        for (i = 0; i < result.membrshipArray.length; i++) {
            updateArr[i] = {'categoryId': '', "subCatId": result['membrshipArray'][i]['id'], 'dataType': result.type, 'sidebarCount': result['membrshipArray'][i]['count'], "action": "remove"};
        }
        var i = 0;
        $.each(result['membrshipAdd'], function(key, memCount) {
            updateArr[i] = {'categoryId': '', "subCatId": key, 'dataType': result.type, 'sidebarCount': memCount, "action": "add"};
            i = i + 1;
        });
        FgCountUpdate.update('assignment', 'contact', 'active', updateArr, result.selcount);
        oTable.api().draw();
    },
    disableDuplicateMerging:function(){
        $('div[data-merge-wrapper] input[type=radio]:checked').each(function(){
            if($(this).val() !== 'fed_mem'){
                $('div[data-merge-wrapper] input[type=radio][value='+$(this).val()+']:not(:checked)').prop("disabled", true);
            }
        });
    }
};

FgMergeAssignmentPopup = {
    handleMergerablePopup : function (response) {
        fedMem={};
        var duplicates = (response['mergeEmail'].length>0) ? response.mergeEmail:response.duplicates;
        var typeMer= (response['mergeEmail'].length>0) ? 'email':'fields';
        var countMergeable = (response['mergeEmail'].length>0) ? 1:duplicates.length;
        var currentContactData = response['currentContactData'];
        
        yours={'firstname':currentContactData['2']};
        yours['lastname']=currentContactData['23'];
        yours['gender']=currentContactData['Gender'];
        yours['dob']=currentContactData['4'];
        yours['location']=currentContactData['77'];
        yours['email']=currentContactData['3'];
        yours['isCompany']=currentContactData['Iscompany'];
        yours['contactName']=currentContactData['contactName'];

        fedMem[response['currentContactData']['fedMembershipId']]=response['currentContactData']['fedMembershipTitle'];

        var htmlFinal = _.template($('#merge-popup-template').html(),{'duplicates': duplicates,'fedMem':fedMem,'typeMer':typeMer,'countMergeable':countMergeable,'yours':yours});
        
        $('#popup_contents').html(htmlFinal);
        FgFormTools.handleUniform();
        $('#popup').addClass('fg-membership-merge-modal');
        $('#popup').modal('show');
        FgMergeAssignmentPopup.mergePopupHandling(typeMer, currentContactData);
    },
    mergePopupHandling:function(typeMer, currentContactData){
        $('#cancel_merging').on('click', function() {
            FgUtility.stopPageLoading();
            FgMergeAssignmentPopup.cancelMerging(typeMer, currentContactData);
        });
         $('#save_merging').on('click', function() {
             FgUtility.startPageLoading();
            var mergerValue=$('.merge-value-radio:checked').val();
            extraData={'merging':'save','mergeTo':mergerValue,'typeMer':typeMer, 'contactData' : currentContactData};
            $.post(mergeSavePath, extraData, function (response) {
                $('#popup').removeClass('fg-membership-merge-modal');
                $('#popup').modal('hide');
                oTable.api().draw();
                if (response.status == 'FAILURE') {
                    FgUtility.showToastr(response.flash, 'warning');
                } else {
                    var updateArr = {"0": {'categoryId': '', "subCatId": response.oldFedId, 'dataType': 'fed_membership', 'sidebarCount': 1, "action": "remove"},
                   "1": {'categoryId': '', "subCatId": response.newFedId, 'dataType': 'fed_membership', 'sidebarCount': 1, "action": "add"}};
                    FgCountUpdate.update('assignment', 'contact', 'active', updateArr, 0);
                    FgUtility.showToastr(response.flash, 'success');
                }
        });
        });
    },
    cancelMerging:function(typeMer, currentContactData){
        $('#popup').removeClass('fg-membership-merge-modal');
        $('#popup').modal('hide');
    }
};


$(document).ready(function() {
    
    if (selActionType == 'single-select') {
        var allContactsData = $.parseJSON($('#selcontacthidden').val());
        $.each(allContactsData, function(cdKey, contactData) {
            var contactId = contactData.id;
            if ($.inArray(contactId, selContIds) == -1) {
                selContIds.push(contactId);
                if ((contactData.contactClub == clubId) || (type == 'federation_club' || type == 'sub_federation_club')) {
                    contactMembership.push({'id': contactId, 'membership': (selCatType == 'fed_membership') ? contactData.fedMembershipId : contactData.clubMembershipId, 'approve': (selCatType == 'fed_membership') ? contactData.fedMembershipApprove : '0'});
                    toAssignContacts.push(contactId);
                    if (selCatType != 'fed_membership') {
                        if (contactData.clubMembershipId == '')
                            nomembership++
                        else
                            membership++;
                    }
                }
                selContNames.push({'id': contactId, 'name': contactData.contactname});
            }
        });
    } else {
        var selectedIdsElement = ($(".dataTables_wrapper div").hasClass('DTFC_LeftBodyWrapper')) ? ".DTFC_LeftBodyWrapper input.dataClass:checked" : "input.dataClass:checked";
        $(selectedIdsElement).each(function() {
            var contactId = $(this).attr('id');
            if ($.inArray(contactId, selContIds) == -1) {
                selContIds.push(contactId);
               
                if (($(this).attr('data-contactclub') == clubId)|| (type == 'federation_club' || type == 'sub_federation_club')) {
                    contactMembership.push({'id': contactId, 'membership': (selCatType == 'fed_membership') ? $(this).attr('data-fed-membership-id') : $(this).attr('data-club-membership_id'), 'approve': (selCatType == 'fed_membership') ? $(this).attr('data-fedmember-approve') : '0'});
                    toAssignContacts.push(contactId);
                    if (selCatType != 'fed_membership') {
                        if ($(this).attr('data-club-membership_id') == '')
                            nomembership++
                        else
                            membership++;

                    }
                }
                selContNames.push({'id': contactId, 'name': $(this).parents('tr').find('.fg-dev-contactname').text()});
            }
        });
    }
    toAssignContacts1 = toAssignContacts;
    selContNames = selContNames.sort(function(a, b) {
        return a.name.localeCompare(b.name);
    });
    /* get details of selected contacts ends */

    var cat_i = 0;
    if (type == 'federation' || type == 'standard_club' || type == 'sub_federation') {
        var contactData = (typeof jsonData['CN'] !== "undefined" && typeof jsonData['CN']['entry'] !== "undefined" && typeof jsonData['CN']['entry']['0'] !== "undefined" && typeof jsonData['CN']['entry']['0']['input'] !== "undefined") ? jsonData['CN']['entry']['0']['input'] : {};
    } else {
        if (clubMembershipAvailable == 1) {
            if (selCatType == 'fed_membership') {
                var contactData = (typeof jsonData['CN'] !== "undefined" && typeof jsonData['CN']['entry'] !== "undefined" && typeof jsonData['CN']['entry']['1'] !== "undefined" && typeof jsonData['CN']['entry']['1']['input'] !== "undefined") ? jsonData['CN']['entry']['1']['input'] : {};
            } else {
                var contactData = (typeof jsonData['CN'] !== "undefined" && typeof jsonData['CN']['entry'] !== "undefined" && typeof jsonData['CN']['entry']['0'] !== "undefined" && typeof jsonData['CN']['entry']['0']['input'] !== "undefined") ? jsonData['CN']['entry']['0']['input'] : {};
            }
        } else {
            var contactData = (typeof jsonData['CN'] !== "undefined" && typeof jsonData['CN']['entry'] !== "undefined" && typeof jsonData['CN']['entry']['0'] !== "undefined" && typeof jsonData['CN']['entry']['0']['input'] !== "undefined") ? jsonData['CN']['entry']['0']['input'] : {};
        }
    }
    _.each(contactData, function(datas) {       
            var catId = datas.id;
            var title = datas.title;
            catArray[cat_i] = {'id': catId, 'title': title};
            cat_i++;
    });
    if (selCatType != 'fed_membership') {
        $('.modal-dialog').addClass('fg-contact-assign-membership-modal-2');
        if (nomembership > 0) {
            if (nomembership == 1)
                $('#fg-no-current-membership .form-group p').text(nomembershiponetrans.replace('%contactcnt%', 1));
            else
                $('#fg-no-current-membership .form-group p').text(nomembershiptrans.replace('%contactcnt%', nomembership));
            $('#fg-no-current-membership').removeClass('hide');
            $('#fg-date-inline-joining input').parent().datepicker(FgApp.dateFormat);
            var currdatetime = moment().format(FgLocaleSettingsData.momentDateTimeFormat);
            $('#fg-date-inline-joining').datepicker('setDate', currdatetime);
            $('#fg-date-inline-joining').datepicker('setEndDate', currdatetime);

        }
        if (membership > 0) {
            if (membership == 1)
                $('#fg-has-membership .form-group p').text(hasmembershiponetrans.replace('%contactcnt%', 1));
            else
                $('#fg-has-membership .form-group p').text(hasmembershiptrans.replace('%contactcnt%', membership));
            $('#fg-has-membership').removeClass('hide');
            FgAssignMembership.selectCriterias();
        }
    }else{
        $('.modal-dialog').removeClass('fg-contact-assign-membership-modal-2');
    }
   
    
    FgAssignMembership.init();
    $('#popup_contents input').uniform(); //for styling
    
});