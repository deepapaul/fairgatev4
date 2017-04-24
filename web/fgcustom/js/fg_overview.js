var FgOverview = function () {
    var settings;
    var clubSettings;
    var showBox = false;
    var defaultSettings = {
        renderSelector: '#overviewDiv', // selector to which the overview content to be rendered
        boxTemplateId: 'overviewBox', // selector to which the overview content to be rendered
        hideEmptySelector: '.fg-dev-ov-settings-onOff', // selector to which the overview content to be rendered
        contactOverviewSettings: '', // saved overview settings data
        contactOverviewData: '', // Json containing informations of contact to be listed
        contactClubId: '', // Club id of contact to be listed
        mainClubId: '', // Club id of current club
        languages: '', // list of all languages
        countryList: '', // list of all countries
        contactType: '', // type of contact (single person/company)
        notes: {
            title: 'NOTES', // title for overview notes box
            data: '', // notes data 
            templateId: 'overviewNotes', // ID of template to render notes data
            path: '' // path of notes page
        },
        fedmembership: {
            title: 'FEDMEMEBERSHIP', // title for overview federation membership box
            data: '', // memebership history data
            templateId: 'overviewFedMembership', // ID of template to render memebership history
            editLinkTemplateId: 'overviewFedMembershipEditLink', // ID of template to render memebership edit link
            logListingPath: '' // path of membership log page
        },
        clubmembership: {
            title: 'CLUBMEMEBERSHIP', // title for overview club membership box
            data: '', // memebership history data
            templateId: 'overviewClubMembership', // ID of template to render memebership history
            editLinkTemplateId: 'overviewClubMembershipEditLink', // ID of template to render memebership edit link
            logListingPath: '' // path of membership log page
        },
        addressBlock: {
            dataCorrespondence: '', // correspondance address of contact
            dataInvoice: '', // invoice address of contact
            templateId: 'overviewAddressBlock'	// ID of template to render contact address
        },
        connections: {
            mainContact: '', // details of main contact
            otherConnections: '', // deatils of other connections
            contactPath: '', // dummy contact overview path to be replaced with the contact id
            templateId: 'overviewConnections'	// ID of template to render contact connections
        },
        roleCategory: {
            categoryData: '', // details of all role categories in the club
            wrapperTemplateId: 'overviewRoleCategoryWrap', // ID of wrapper template to render assignment details
            templateId: 'overviewRoleCategory' // ID of template to render assignments in each role category
        },
        contactCategory: {
            wrapperTemplateId: 'overviewContactCategoryWrap', // ID of wrapper template to render contact field categroy details
            templateId: 'overviewContactCategory' // ID of template to render each contact field information
        },
        federationInfo: {
            data: '', // federation info details 
            clubAssignments: '',
            templateId: 'overviewFederationInfo' // ID of template to render federation information
        },
        overviewSettings: {
            templateId: 'overviewSettingsBoxContent' // ID of template to render overview settings box content
        },
        serviceAssignment: {
            title: '', // title for overview service assignment box
            data: '', // assignment data 
            wrapperTemplateId: 'overviewServiceAssignmentWrap', // ID of wrapper template to render assignment details
            templateId: 'overviewServiceAssignment', // ID of template to render assignment data
        },
        sponsored: {
            title: '', // title for overview sponsored by box
            data: '', // sponsor data 
            templateId: 'overviewSponsoredBy', // ID of template to render sponsored by details
            path: '' // url of contact overview page
        },
        profileBlock: {
            title: '', // title for overview sponsored by box
            data: '', // sponsor data 
            templateId: 'overviewProfile', // ID of template to render sponsored by details
            path: '' // url of contact overview page
        },
    };

    var defaultClubSettings = {
        renderSelector: '#overviewDiv', // selector to which the club overview content to be rendered
        boxTemplateId: 'overviewBox', // selector to which the club overview content to be rendered

        notes: {
            allNotes: '', // notes data 
            templateId: 'clubOverviewNotes', // ID of template to render notes data
            path: '' // path of notes page
        },
        clubInfos: {
            overviewContents: '', // clubinfo data
            templateId: 'clubInfos', // ID of template to render club infos
            terminologyTerms: '', // Terminology terms array
        },
        contacts: {
            templateId: 'clubOverviewContacts', // ID of template to render contacts
        },
        systemInfos: {
            templateId: 'clubOverviewSystemInfos', // ID of template to render system infos
        },
        classification: {
            templateId: 'clubOverviewClassification', // ID of template to render classification
        },
        executiveBoard: {
            templateId: 'clubOverviewExecutiveBoard', // ID of template to render executive board
        },
        addressBlock: {
            templateId: 'clubOverviewAddressBlock', // ID of template to render address block
        }
    };
    var initSettings = function (options) {
        settings = $.extend(true, {}, defaultSettings, options);
    }
    // method to render contact notes
    var renderNotes = function () {
        if (settings.notes.data.length) {
            showBox = true;
            return FGTemplate.bind(settings.notes.templateId, {settings: settings});
        }
    }
    // Method to initialize club settings
    var initClubSettings = function (options) {
        clubSettings = $.extend(true, {}, defaultClubSettings, options);
    }
    // method to render club contacts
    var renderClubContacts = function () {
        showBox = true;
        return FGTemplate.bind(clubSettings.contacts.templateId, {settings: clubSettings});
    }
    // method to render club system infos
    var renderClubSystemInfos = function () {
        showBox = true;
        return FGTemplate.bind(clubSettings.systemInfos.templateId, {settings: clubSettings});
    }
    // method to render club notes
    var renderClubNotes = function () {
        if (clubSettings.allNotes.length) {

            showBox = true;
            return FGTemplate.bind(clubSettings.notes.templateId, {settings: clubSettings});
        }
    }
    // method to render club infos
    var renderClubInfos = function () {
        if (clubSettings.overviewContents.length) {
            showBox = true;
            return FGTemplate.bind(clubSettings.clubInfos.templateId, {settings: clubSettings});
        }
    }
    // method to render club classificatios
    var renderClubClassification = function () {
        showBox = true;
        return FGTemplate.bind(clubSettings.classification.templateId, {settings: clubSettings});
    }
    // method to render club executive board
    var renderClubExecutive = function () {
        showBox = true;
        return FGTemplate.bind(clubSettings.executiveBoard.templateId, {settings: clubSettings});
    }
    // method to render club address block
    var renderClubAddressBlock = function () {
        showBox = true;
        return FGTemplate.bind(clubSettings.addressBlock.templateId, {settings: clubSettings});
    }
    // method to render federation membership history
    var renderFedMembershipLog = function () {
        if (settings.fedmembership.data.length) {
            showBox = true;
            return FGTemplate.bind(settings.fedmembership.templateId, {settings: settings});
        }
    }
    // method to render club membership history
    var renderClubMembershipLog = function () {
        if (settings.clubmembership.data.length) {
            showBox = true;
            return FGTemplate.bind(settings.clubmembership.templateId, {settings: settings});
        }
    }
    // method to render membership edit link
    var renderFedMembershipEditLink = function () {
        return FGTemplate.bind(settings.fedmembership.editLinkTemplateId, {settings: settings});
    }
    // method to render membership edit link
    var renderClubMembershipEditLink = function () {
        return FGTemplate.bind(settings.clubmembership.editLinkTemplateId, {settings: settings});
    }
    // method to render invoice and communication address
    var renderAddressBlock = function (overviewSettings) {
        showBox = true;
        return FGTemplate.bind(settings.addressBlock.templateId, {settings: settings, overviewSettings: overviewSettings});
    }
    // method to render each role category with assignment
    var renderEachRoleCategory = function (roleCategorySettings, settingsType) {
        var roleCategoryIndex = settingsType + "_" + roleCategorySettings.fieldType + "_" + roleCategorySettings.fieldId;
        var overviewRoleData = settings.contactOverviewData;
        var isRoleCategoryData = (_.isEmpty(overviewRoleData[roleCategoryIndex]) || _.isUndefined(overviewRoleData[roleCategoryIndex])) ? false : true;
        if (!isRoleCategoryData && roleCategorySettings.emptyFlag) {
            showBox = false;
        } else {
            showBox = true;
        }
        if (showBox) {
            return FGTemplate.bind(settings.roleCategory.templateId, {settings: settings, roleCategoryIndex: roleCategoryIndex, roleCategorySettings: roleCategorySettings});
        } else {
            return "";
        }
    }
    // method to render role categories
    var renderRoleCategory = function (overviewSettings) {
        var content = "";

        _.each(overviewSettings.fields, function (fieldValue, fieldKey) {
            content = content + renderEachRoleCategory(fieldValue, overviewSettings.settingsType);
        });
        if (content) {
            showBox = true;
            return FGTemplate.bind(settings.roleCategory.wrapperTemplateId, {content: content});
        } else {
            showBox = false;
            return "";
        }
    }
    // method to render sponsor categories
    var renderSponsorCategory = function (overviewSettings) {
        return FGTemplate.bind(settings.serviceAssignment.templateId, {settings: settings, overviewSettings: overviewSettings});
    }
    // method to render sponosr category wrapper
    var renderSponsorCategoryWrapper = function (overviewSettings) {
        showBox = false;
        // sets whether the service assignment box should display or not
        var displayFlag = 0;
        var servicesCount = parseInt($('li[name="fg-dev-services-tab"]:first').find('span.badge').text()) || 0;
        if (overviewSettings.fields) {
            var totalServiceCategory = _.size(overviewSettings.fields);
            var emptyCount = 0;
            _.each(overviewSettings.fields, function (value, key) {
                if (value.emptyFlag == 1) {
                    emptyCount++;
                }
            });
            displayFlag = ((totalServiceCategory == emptyCount) && (servicesCount == 0)) ? 0 : 1;
        }
        if ((displayFlag == 1) && (overviewSettings.displayFlag == 1)) {
            var content = renderSponsorCategory(overviewSettings);
            content = content.trim();
            if (content != '') {
                showBox = true;
                return FGTemplate.bind(settings.serviceAssignment.wrapperTemplateId, {content: content});
            }
        }
        
        return "";
    }
    // method to render sponsored by details
    var renderSponsoredBy = function (overviewSettings) {
        if (settings.sponsored.data.length) {
            showBox = true;
            return FGTemplate.bind(settings.sponsored.templateId, {settings: settings, overviewSettings: overviewSettings});
        }
    }
    // method to render federation information
    var renderFederationInfo = function (overviewSettings) {
        showBox = true;
        //check whether the contact is former fedmember or current fedmember
        if(settings.federationInfo.data.fedMembershipId > 0 || _.size(settings.federationInfo.clubAssignments)>0){
          showBox = true;  
        } else {
            showBox = false;
        }
        return FGTemplate.bind(settings.federationInfo.templateId, {settings: settings, overviewSettings: overviewSettings,activeClubAssignmentCount:settings.federationInfo.activeAssignmentcount});
    }
    // method to render each contact fields in a contact field category
    var renderEachContactCategory = function (contactCategorySettings, settingsType) {
        var contactCategoryIndex = settingsType + "_" + contactCategorySettings.fieldType + "_" + contactCategorySettings.fieldId;
        var overviewContactData = settings.contactOverviewData;
        var isContactCategoryData = (_.isEmpty(overviewContactData[contactCategoryIndex]) || _.isUndefined(overviewContactData[contactCategoryIndex]) || overviewContactData[contactCategoryIndex] == '0000-00-00' || overviewContactData[contactCategoryIndex] == '-') ? false : true;
        if (!isContactCategoryData && contactCategorySettings.emptyFlag) {
            showBox = false;
        } else {
            showBox = true;
        }
        if (showBox) {
            return FGTemplate.bind(settings.contactCategory.templateId, {settingsType: settingsType, settings: settings, contactCategoryIndex: contactCategoryIndex, contactCategorySettings: contactCategorySettings, isContactCategoryData: isContactCategoryData});
        } else {
            return "";
        }
    }
    // method to render each contact field category
    var renderContactCategory = function (overviewSettings) {
        var content = "";
        var dataFields = _.sortBy(overviewSettings.fields, 'itemSortOrder');
        _.each(dataFields, function (fieldValue, fieldKey) {
            content = content + renderEachContactCategory(fieldValue, overviewSettings.settingsType);
        });
        if (content) {
            showBox = true;
            return FGTemplate.bind(settings.contactCategory.wrapperTemplateId, {content: content});
        } else {
            showBox = false;
            return "";
        }
    }
    // method to render contact connection html
    var renderConnections = function (overviewSettings) {
       // var connectionCount= $(".fg-dev-connection-tab").text();
        showBox = false;
        if(settings.connections.connectionVisibility > 0) {
           showBox = true;   
        }     
        return FGTemplate.bind(settings.connections.templateId, {settings: settings, overviewSettings: overviewSettings});
    }
    
    var renderprofileBlock = function (selector) {        
         $.ajax({
            type: 'POST',
            url: settings.profileBlock.dataUrl,
            success: function( data ) {
                $(selector + " .profile").html(FGTemplate.bind(settings.profileBlock.templateId, {settings: data, clubAssignments: settings.federationInfo.clubAssignments,activeClubAssignmentCount:settings.federationInfo.activeAssignmentcount}));
                handlePopUpProfileBox();
            }
        });
    } 
    // method to render contact settings box html
    var renderSettingsBoxContent = function (overviewSettings) {
        var fedIconFlag = 0;
        var fieldContent = ""
        if (overviewSettings.clubId != 1 && overviewSettings.clubId != '' && overviewSettings.clubId != settings.mainClubId && overviewSettings.settingsType == 'categoryset') {
            fedIconFlag = overviewSettings.clubId;
        }
        if (overviewSettings.hasOwnProperty('fields')) {
            _.each(overviewSettings.fields, function (fieldVal, fieldKey) { 
                fieldContent = fieldContent + FGTemplate.bind(settings.overviewSettings.templateId, {settings: settings, fieldSettingsKey: fieldKey, fedIconFlag: fedIconFlag, overviewSettings: overviewSettings});
            });
        }
        return fieldContent;
    }
    // method to enclose each rendered data in a overview box and append
    var getOverviewHtml = function (overviewData) {
        var overviewContent = "";
        _.each(overviewData, function (val, key) {
            var boxObject = {};
            boxObject.content = "";
            boxObject.titleRight = "";
            boxObject.title = "";
            if (val[0].displayFlag == 1) {
                showBox = false;
                var settingsType = val[0].settingsType;
                switch (settingsType) {
                    case 'notes':
                        boxObject.content = renderNotes();
                        boxObject.title = settings.notes.title;
                        break;
                    case 'fedmembership': 
                        boxObject.content = renderFedMembershipLog();
                        boxObject.titleRight = renderFedMembershipEditLink();
                        boxObject.title = val[0].title;
                        break;
                    case 'clubmembership':
                        boxObject.content = renderClubMembershipLog();
                        boxObject.titleRight = renderClubMembershipEditLink();
                        boxObject.title = settings.clubmembership.title;
                        break;
                    case 'addressBlock':
                        boxObject.content = renderAddressBlock(val[0]);
                        boxObject.title = val[0].title;
                        break;
                    case 'roleCategory':
                        boxObject.content = renderRoleCategory(val[0]);
                        boxObject.title = val[0].title;
                        break;
                    case 'federationInfo':
                        boxObject.content = renderFederationInfo(val[0]);
                        boxObject.title = val[0].title;
                        if(!boxObject.content.trim()) {
                           showBox=false;
                        }
                        break;
                    case 'connections':
                        boxObject.content = renderConnections(val[0]);
                        boxObject.title = val[0].title;
                        break;
                    case 'serviceAssignment':
                        boxObject.content = renderSponsorCategoryWrapper(val[0]);
                        boxObject.title = settings.serviceAssignment.title;
                        break;
                    case 'sponsored':
                        boxObject.content = renderSponsoredBy(val[0]);
                        boxObject.title = settings.sponsored.title;
                        break;
                    default:
                        if(val[0].hasOwnProperty('fields') ){
                           firstArray = _.first(_.toArray(val[0]['fields']));
                            if(_.has(firstArray, 'itemSortOrder') ) {  
                                beforeSort =_.sortBy(val[0]['fields'], 'itemSortOrder'); 
                                val[0]['fields'] = beforeSort;
                            }
                        }                      
                        boxObject.content = renderContactCategory(val[0]);
                        boxObject.title = val[0].title;
                        break;
                }
                if (showBox) {
                    overviewContent = overviewContent + FGTemplate.bind(settings.boxTemplateId, boxObject);
                }
            }
        });
        return overviewContent;
    }

    // method to enclose each rendered data in a overview box and append
    var getClubOverviewHtml = function (overviewData, clubOverviewBlocks) {

        var overviewContent = "";
        _.each(clubOverviewBlocks, function (val, key) {
            var boxObject = {};
            boxObject.content = "";
            boxObject.titleRight = "";
            boxObject.title = "";
            showBox = false;

            switch (val) {
                case 'clubInfos':
                    boxObject.content = renderClubInfos();
                    boxObject.title = clubSettings.clubInfoTitle;
                    break;
                case 'contacts':
                    boxObject.content = renderClubContacts();
                    boxObject.title = clubSettings.contactTitle;
                    break;
                case 'systemInfos':
                    boxObject.content = renderClubSystemInfos();
                    boxObject.title = clubSettings.systemInfoTitle;
                    break;
                case 'classification':
                    boxObject.content = renderClubClassification();
                    boxObject.title = clubSettings.classificationTitle;
                    break;
                case 'executiveBoard':
                    boxObject.content = renderClubExecutive();
                    boxObject.title = clubSettings.terminologyTerms.executive_board;
                    break;
                case 'addressBlock':
                    boxObject.content = renderClubAddressBlock();
                    boxObject.title = clubSettings.addressBlockTitle;
                    break;
                case 'notes':
                    boxObject.content = renderClubNotes();
                    boxObject.title = clubSettings.notesTitle;
                    break;
                default:

            }
            if (showBox) {
                overviewContent = overviewContent + FGTemplate.bind(clubSettings.boxTemplateId, boxObject);
            }
        });
        return overviewContent;



    }
    // method to handle pop up click in contact overview page
    var handlePopUpProfileBox = function(){
         $("body").off('click', "a.fa-access-edit") ;
         $("body").on('click', "a.fa-access-edit", function(event) {
            event.preventDefault();
            var contactId = settings.profileBlock.contactId;
            var fedMembershipId = settings.profileBlock.fedMembershipId;
            var clubMembershipId = settings.profileBlock.clubMembershipId;
            var active = '';
            var contactTitle = $(".page-title-sub").text().trim();
            var path = $(this).attr("data-url"); 
            var type = $(this).attr("data-type");
            var module = $(this).attr("data-module");
            if(type != "fedmembership" || type != "clubmembership"){
               active = $(this).attr("data-active");
            }
            showPopup('contact_overview',{'path': path, 'active':active, 'type':type, 'contactTitle':contactTitle, 'contactId':contactId, 'fedMembershipId':fedMembershipId, 'clubMembershipId':clubMembershipId, 'module': module});
        });
        FgPageTitlebar.setMoreTab();
    }
    // method to enclose each rendered settings box content in a overview box and append
    var getOverviewSettingsHtml = function (overviewData) {
        var overviewContent = "";
        var leftLoopValue = 1;
        var rightLoopValue = 1;
        var boxObject = {};
        _.each(overviewData, function (dataVal) {
           //for correct the sort order of contact fields
           //BLOCK START
            if(dataVal[0].hasOwnProperty('fields') ){
                firstArray = _.first(_.toArray(dataVal[0]['fields']));
                 if(_.has(firstArray, 'itemSortOrder') ) {
                     beforeSort =_.sortBy(dataVal[0]['fields'], 'itemSortOrder');                     
                  dataVal[0]['fields'] = beforeSort;
                 }
            }
           //BLOCK END
            _.each(dataVal, function (val, key) {
                if (val.displayArea == 'left') {
                    boxObject.loopValue = leftLoopValue;
                    boxObject.loopAttr = 'data-left-catSortOrder';
                    leftLoopValue++;
                } else if (val.displayArea == 'right') {
                    boxObject.loopValue = rightLoopValue;
                    boxObject.loopAttr = 'data-right-catsortorder';
                    rightLoopValue++;
                }
                boxObject.data = val;
                boxObject.content = renderSettingsBoxContent(val);
                overviewContent = overviewContent + FGTemplate.bind(settings.boxTemplateId, boxObject);
            });
        });
        return overviewContent;
    }

    var hideIfemptyChkBox = function (selector) {
        $(selector).on('click', settings.hideEmptySelector, function () {
            if ($(this).prop('checked') === true) {
                $(this).parent().siblings('div').find('input').removeAttr('disabled');
            } else {
                $(this).parent().siblings('div').find('input').attr('checked', false);
                $(this).parent().siblings('div').find('input').attr('disabled', 'disabled');
            }
            $.uniform.update($('form input:checkbox'));
        });
    }
    var emptySortDiv = function (selector, block) {
        if ($(selector + " " + block).children().length < 1) {
            $(selector + " " + block).append('<div id="emptySortDiv" class="portlet box sortable" style="height:' + $(selector).outerHeight() + 'px;"></div>');
        } else {
            $(selector + " #emptySortDiv").remove();
        }
    }
    return {
        contactPage: function (options) {
            initSettings(options);
            var overview_array = FgUtility.groupByMulti(settings.contactOverviewSettings, ['displayArea', 'sortorder']);
            var left_overview_array = overview_array.left;
            var right_overview_array = overview_array.right;
            var selector = settings.renderSelector;
            renderprofileBlock(selector) ;
            $(selector + " .left").html(getOverviewHtml(left_overview_array));
            $(selector + " .right").html(getOverviewHtml(right_overview_array));

        },
        contactSettingsPage: function (options) {
            initSettings(options);
            var overview_array = FgUtility.groupByMulti(settings.contactOverviewSettings, ['displayArea', 'sortorder']);
            var left_overview_array = overview_array.left;
            var right_overview_array = overview_array.right;
            var selector = settings.renderSelector;
            $(selector + " .fg-leftDisplay").html(getOverviewSettingsHtml(left_overview_array));
            $(selector + " .fg-rightDisplay").html(getOverviewSettingsHtml(right_overview_array));
            hideIfemptyChkBox(selector);
            emptySortDiv(selector, ".fg-leftDisplay");
            emptySortDiv(selector, ".fg-rightDisplay");
        },
        emptySortDivCheck: function () {
            var selector = settings.renderSelector;
            emptySortDiv(selector, ".fg-leftDisplay");
            emptySortDiv(selector, ".fg-rightDisplay");
        },
        clubOverview: function (options) {
            var clubOverviewLeftBlocks = ['clubInfos', 'contacts', 'systemInfos', 'classification'];
            var clubOverviewRightBlocks = ['executiveBoard', 'addressBlock', 'notes'];
            initClubSettings(options);
            var selector = clubSettings.renderSelector;
            $(selector + " .left").html(getClubOverviewHtml(options, clubOverviewLeftBlocks));
            $(selector + " .right").html(getClubOverviewHtml(options, clubOverviewRightBlocks));
        },
        inlineEditClubAssignment : function(inlineEditData) {

            //$('div.editable-input input.datepicker').datepicker(FgApp.dateFormat);
            var data1 = JSON.parse(inlineEditData);
            $('.inline-editable').editable({
                emptytext: '-',
                autotext: 'never',
            });
            inlineEdit.init({
                element: '.inline-editable',
                postUrl: inlineEditClubAssignmentPath,
                data : data1
            })
        
        }
    };
}();

							