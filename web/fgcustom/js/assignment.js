        
// Initializing variables and arrays for different types of roles
var arrayFederation=[]; // For federation
var finalFederation; // For federation
var arraySubfederation=[]; // For subfederation
var finalSubfederation; // For subfederation
var arrayNormalRoles=[]; // For normal roles
var finalNormalRoles; // For normal roles
var arrayTeam=[]; // For team roles
var finalTeam; // For team roles
var arrayWorkgroup=[]; // For workgroup roles including executive board
var finalWorkgroup; // For workgroup roles including executive board
var globalRequest=0; // for getting global request count
var clubId; // Club id
var clubType; // current club type
var dropdownValues;
var allDropdownValues;
var arrayMembershipCat; // Membership category array

// jQuery class to generate different array for each categories like federation, subfederation and team
FgAssignment = {
    
    init: function(assignmentDpDn) {
        
        // Request for getting all values of dropdowns in all categories
        $.getJSON(assignmentDpDn, function(dropdownData) {
        
            clubId=dropdownData.clubId;
            clubType=dropdownData.clubType;
            allDropdownValues=dropdownData.resultArray; // All mixed dropdown values
            var is_fed_categoryGroup = FgUtility.groupByMulti(dropdownData.resultArray, ['is_fed_category']); // Seperating federation categories
            var federationGroup=is_fed_categoryGroup[1]; // Seperating federation categories
            var federationClubGroup = FgUtility.groupByMulti(federationGroup, ['club_id']); // Seperating federation categories

            // Seperating federation categories into federation and subfederations
            _.each(federationClubGroup, function(valueArray,key) {
                if(key==dropdownData.federationId) {
                    arrayFederation=valueArray; // Federation type
                } else if(key==dropdownData.subFederationId) {
                    arraySubfederation=valueArray; // Subfederation type
                } else if(key==dropdownData.clubId && clubType=='federation') {
                    arrayFederation=valueArray; // Federation type
                } else if(key==dropdownData.clubId && clubType=='sub_federation') {
                    arraySubfederation=valueArray; // Subfederation type
                }
            });

            // Seperating federation and subfederation membership categories which have set as mandatory assignments
            if(clubType =='federation_club') { // Seperating from all dropdown values if club is a federation club
                arrayMembershipCat = _(allDropdownValues).filter(function (x) { return (x['is_allowed_fedmember_club']==1 && x['is_required_fedmember_club']==1);});
            } else if(clubType=='sub_federation') { // Seperating from all dropdown values if club is a sub-federation
                arrayMembershipCat = _(allDropdownValues).filter(function (x) { return (x['is_allowed_fedmember_subfed']==1 && x['is_required_fedmember_subfed']==1);});
            } else if(clubType=='sub_federation_club') { // Seperating from all dropdown values if club is a sub-federation club
                arrayMembershipCat = _(allDropdownValues).filter(function (x) { return (x['is_allowed_fedmember_club']==1 && x['is_required_fedmember_club']==1);});
            }
            
            var nonfederationGroup=is_fed_categoryGroup[0]; // Seperating other categories

            arrayTeam = _(nonfederationGroup).filter(function (x) { return x['is_team']==1;}); // Seperating all team categories
            arrayWorkgroup = _(nonfederationGroup).filter(function (x) { return (x['is_workgroup']==1 && x['functionId']!=null);}); // Seperating all workgroup categories
            arrayNormalRoles = _(nonfederationGroup).filter(function (x) { return (x['is_team'] ==0 && x['is_workgroup']==0) ;}); // Seperating all other categories other than team and workgroup

            finalFederation=FgUtility.groupByMulti(arrayFederation, ['catId','roleId','functionId']); // Grouping federation categories for easy handlying
            finalSubfederation=FgUtility.groupByMulti(arraySubfederation, ['catId','roleId','functionId']); // Grouping sub federation categories for easy handlying
            finalNormalRoles=FgUtility.groupByMulti(arrayNormalRoles, ['catId','roleId','functionId']); // Grouping normal categories for easy handlying
            finalTeam=FgUtility.groupByMulti(arrayTeam, ['teamCatId','roleId','functionId']); // Grouping team categories for easy handlying
            finalWorkgroup=FgUtility.groupByMulti(arrayWorkgroup, ['roleId','functionId']); // Grouping workgroup categories for easy handlying
            finalMembershipCat=FgUtility.groupByMulti(arrayMembershipCat, ['catId','roleId','functionId']); // Grouping mandatory membership categories for easy handlying

            assignmentSuccess(); // Call back function after listing
            
        });
    },
    
    // Function to replace the given charactor from the overview link 
    // with the desired contact id to generate url at run time
    pathReplace: function(path,id){
        var finalPath = path.replace('#dummy#', id); 
        window.location = finalPath;
    },
    
    // Function to fing pending assignments which are set as mandatory assignment required
    findPendingAssgn: function(missingReqAssgnments){
        var missingAssignments = {};
        var missingFedAssignments = {};
        var missingSubFedAssignments = {};
        // Getting all membership categories and grouping it
        finalMembershipCat=FgUtility.groupByMulti(arrayMembershipCat, ['catId','roleId','functionId']);
        _.each(finalMembershipCat, function(catVal,catKey) {
            if ($.inArray(parseInt(catKey), missingReqAssgnments) != -1) {
                missingAssignments[catKey]=finalMembershipCat[catKey];
            }
        });
        
        // Searching in federation categories for missing mandatory assignment categories
        _.each(finalFederation, function(finalFedVal,finalFedKey) {
            if(missingAssignments[finalFedKey] != undefined) {
                missingFedAssignments[finalFedKey]=missingAssignments[finalFedKey];
            }
        });

        // Searching in sub-federation categories for missing mandatory assignment categories
        _.each(finalSubfederation, function(finalSubFedVal,finalSubFedKey) {
            if(missingAssignments[finalSubFedKey] != undefined) {
                missingSubFedAssignments[finalSubFedKey]=missingAssignments[finalSubFedKey];
            }
        });

        return {'fedPendingAssg':missingFedAssignments,'missingSubFedAssignments':missingSubFedAssignments};
    }
};

// jQuery class to manage assigning new values and drop down functionalities
FgMainAssignment = {
    
    // Initing mandatory functions up-front
    init: function() {
        FgMainAssignment.createNewAssignmentClick();
        FgMainAssignment.mainAssignFirstDpDownChange();
        FgMainAssignment.mainAssignSecondDpDownChange();
        FgMainAssignment.mainAssignResetAssignment();
        FgMainAssignment.mainAssignRemoveAssignment();
    },
    
    // This function is used for validation error display if there is any
    failCallbackFunctions: function(errorData){
        $('#failcallback').hide();
            $('#failcallbackServerSide').hide();
            var errorMsg=errorData.flash;
            var errorArray=errorData.errorArray;
            
            // Looping each error block to make it in red color
            _.each(errorArray, function(value,key) {
                var idPattern=key+'_role_'+value;
                $('option[id^='+idPattern+']').each(function(){
                    $(this).parent().parent().parent().addClass("has-error");
                });
            });
            $('#failcallbackServerSide span').text(errorMsg);
            $('#failcallbackServerSide').show(); // Displaying error message
            FgXmlHttp.scrollToErrorDiv();
    },
    
    // Function to save all changes made in the 
    // assignment page including new assignments, delete assignments etc
    assignmentSaveChanges: function(assignmentSavePath, contactId){
        var emptySelectFlag=0;
        $('#failcallback').hide();
        $('#failcallbackServerSide').hide();
        
        // Ckecking whether there is any error in the assignments
        // Only first level (client side) validations are done here.
        $('select').each(function(){
            var _this= $(this);
            _this.parent().removeClass("has-error");
            var selectedVal=_this.val();
            if(selectedVal==='' || selectedVal===' ') {
                emptySelectFlag=1;
                 _this.parent().addClass("has-error");
                $('#failcallback').show();
                FgXmlHttp.scrollToErrorDiv();
            }

        });
        
        // Below code is for generating the JSON array format of all changes made. 
        // This only works if the assignments have no errors
        if(emptySelectFlag===0) {
            newBlockArray=Array('.new_asgn_blk','.new_asgn_blk_fed','.new_asgn_blk_subfed');
            _.each(newBlockArray, function(val,key) {
                $(val).each(function() {
                    var selectedOption = $(this).find('select:last option:selected');
                    var dataKey = selectedOption.attr('data-key');
                    var dataName = selectedOption.attr('name');
                    var dataValue = selectedOption.val();
                    var appendValName = (selectedOption.attr('element-type') == 'Team') ? contactId + '_team' : contactId + '_';
                    var appendValKey = (selectedOption.attr('element-type') == 'Team') ? contactId + '.team' : contactId + '.';
                    var keyElement = $(this).find('input[type=hidden][data-type=key_element]');
                    $(keyElement).attr({'name': appendValName + dataName + '_is_new', 'data-key': appendValKey + dataKey + '.is_new', 'value': dataValue, 'class': 'fairgatedirty'});
                });
            });
            var objectGraph = {};
            //parse the all form field value as json array and assign that value to the array
            objectGraph=  FgParseFormField.fieldParse();
            var catArr = JSON.stringify(objectGraph); // Generating JSON array

            // AJAX call to save all updates.
            // There are also some server side validations. 
            // If some error occurs in there, failCallbackFunctions function will be called and error will displayed
            FgXmlHttp.post(assignmentSavePath, {'catArr': catArr, 'contact_id': contactId} , false, false, FgMainAssignment.failCallbackFunctions, '0');
        }
    },
    
    // Function to list all assignments including the dropdown values of all categoris in a specific format
    mainAssignmentSuccess: function(assignmentListingPath, isReadOnlyContact){        
        
        // Ajax to get all current assignments
        $.getJSON(assignmentListingPath,{ 'contactId': contactId}, function(data) {
            listingAllAssignmentData=data;
            // For missing assignment handlying
            finalMembershipCat=FgUtility.groupByMulti(arrayMembershipCat, ['catId','roleId','functionId']);
            pendingAssignments=FgAssignment.findPendingAssgn(missingReqAssgnments); // Function to get missing assignments of mandatory assignment categories

            // Calling underscore template for listing all assignments
            var template = $('#listAllAssigments').html();
            var result_data = _.template(template, {content: data,arrayTeam:arrayTeam,arrayWorkgroup:arrayWorkgroup,arrayNormalRoles:arrayNormalRoles,arrayFederation:arrayFederation,arraySubfederation:arraySubfederation,clubId:clubId,contactId:contactId,loggedContactId:loggedContactId});
            $('#assignmentListingDiv').html(result_data); 
            $('#assignmentListingDiv').show();

            //If the logged in contact is read only contact missing assignments should not be shown
            if(isReadOnlyContact == 0) {
                 // Calling underscore template for listing missing assignments under federation roles
                var pending_assignment_template=$('#newAssignmentFedRow').html();
                var result_template = _.template(pending_assignment_template, {filterArray: pendingAssignments.fedPendingAssg,normalArray:arrayMembershipCat,elementtype: 'MembershipCat'});
                if($('#fg-dev-federationrole').children('.panel').children('.insert_new_assignment_panel').children('div').length < 1) {
                    $('#fg-dev-federationrole').children('.panel').append('<div class="insert_new_assignment_panel newAssgmtPanlFed"></div>');
                } else {
                    $('#fg-dev-federationrole').children('.panel').children('.insert_new_assignment_panel').addClass('newAssgmtPanlFed');
                }
                $('#fg-dev-federationrole').children('.panel').children('.insert_new_assignment_panel').append(result_template);

                // Calling underscore template for listing missing assignments under sub-federation roles
                var pending_assignment_template=$('#newAssignmentSubfedRow').html();
                var result_template = _.template(pending_assignment_template, {filterArray: pendingAssignments.missingSubFedAssignments,normalArray:arrayMembershipCat,elementtype: 'MembershipCat'});
                if($('#fg-dev-subfederationrole').children('.panel').children('.insert_new_assignment_panel').children('div').length < 1) {
                    $('#fg-dev-subfederationrole').children('.panel').append('<div class="insert_new_assignment_panel newAssgmtPanlSubfed"></div>');
                } else {
                    $('#fg-dev-subfederationrole').children('.panel').children('.insert_new_assignment_panel').addClass('newAssgmtPanlSubfed');
                }
                $('#fg-dev-subfederationrole').children('.panel').children('.insert_new_assignment_panel').append(result_template);
                FgMainAssignment.mainAssignInitAreYouSure();
                ComponentsDropdowns.init();
            }
           

            FgMainAssignment.initPageFunctions();
            FgUtility.changeColorOnDelete(); // Function to make color change on delete row
        });
             
    },
    
    // Function is called when clicking on the Add new assignment link in all types
    createNewAssignmentClick: function(){
        // Executes while clicking on the create new assignment link
        $(document).off('click','.create_new_assignment');
        $(document).on('click', '.create_new_assignment',function(event){ 
            var thisVar=$(this);
            var elementTitle=thisVar.attr('element-title');
            var elementType=thisVar.attr('element-type');
            
            globalRequest=(globalRequest==0) ? 1 : globalRequest; // Checking global request count
            FgMainAssignment.mainAssignInitialDropdown(thisVar,elementTitle,elementType); // Getting first drop down containing categories
        });
    },
    
    // Function to display first dropdown of category or Roles( in case of workgroup)
    // The argument elementType is the clicked type(like roles,federation roles,subfederation roles etc)
    mainAssignInitialDropdown: function(thisVar,elementTitle,elementType){
        var dynamicFinalArrayName='final'+elementType; // Generating the correct array name of grouped elements from the selected type
        var dynamicArrayName='array'+elementType; // Generating the correct array name from the selected type
        var filteredElementArray=window[dynamicFinalArrayName]; // Getting the array from name
        var elementArray=window[dynamicArrayName]; // Getting the array from name

        //Handlying workgroup seperately
        // Incase of workgroup, it don't have the category dropdown. 
        // First dropdown is the roles itself
        if(elementType=='Workgroup') {
            
            var new_assignment_roles_template=$('#newAssignmentRolesRow').html(); // Getting the dropdown template
            var result_roles_template = _.template(new_assignment_roles_template, {filterArray: filteredElementArray,element:elementType,normalArray:elementArray,loggedClubId:clubId}); // Loading template using underscore.js

            if(thisVar.parent().siblings('div').children('div').length <= 1) {
                thisVar.parent().siblings('div').append('<div class="insert_new_assignment_panel newAssgmtPanl"></div>');
            }
            thisVar.parent().siblings('div').children('.insert_new_assignment_panel').append(result_roles_template); 
            FgMainAssignment.mainAssignInitAreYouSure();
            ComponentsDropdowns.init();
        } else {

            var new_assignment_template=$('#newAssignmentRow').html(); // Getting the dropdown template
            var result_template = _.template(new_assignment_template, {filterArray: filteredElementArray,normalArray:elementArray,elementtype: elementType});
            
            // If the length is less than one, that means there is no other row under the selected type. 
            // So need to isert the wrapper div also
            if(thisVar.parent().siblings('div').children('div').length <= 1) {
                thisVar.parent().siblings('div').append('<div class="insert_new_assignment_panel newAssgmtPanl"></div>');
            }
            thisVar.parent().siblings('div').children('.insert_new_assignment_panel').append(result_template);
            FgMainAssignment.mainAssignInitAreYouSure();
            ComponentsDropdowns.init();
        } 
    },
    
    // On change functionality of the category dropdown
    mainAssignFirstDpDownChange: function(){
        //On change of first dropdown in all cases
        $('body').on('change', '.catDropDown', function() {
            var selectedCatId=$(this).val(); // Selected category id
            var elementType = $('option:selected', this).attr('element-type'); // Element type
            var insertFlag = $(this).attr('insert-flag'); // Flag to check the change in the dropdown value
            var optionTextCat = $('option:selected', this).attr('option-text-category'); // For checking default selection
            
            // Checking whether the change is due to the default option selection
            if(optionTextCat!=1) {
                var dynamicFinalArrayName='final'+elementType; // Generating the correct array name of grouped elements from the selected type
                var dynamicArrayName='array'+elementType; // Generating the correct array name from the selected type
                var filteredElementArray=window[dynamicFinalArrayName]; // Getting the array from name
                var elementArray=window[dynamicArrayName]; // Getting the array from name

                var rolesArray=filteredElementArray[selectedCatId];
                var new_assignment_roles_template=$('#newAssignmentRolesRow').html(); // Getting the role drop down template
                var result_roles_template = _.template(new_assignment_roles_template, {filterArray: rolesArray,element:elementType,category:selectedCatId,normalArray:elementArray}); // Loading role dropdown template using underscore.js

                // If ythis flag is 0, just append the new html with the current. 
                // Otherwise, remove the already populated roles,functions and append from begining 
                if(insertFlag == 0) { 
                    $(this).parent().parent().append(result_roles_template);
                    $(this).attr("insert-flag","1");
                } else {
                    $(this).parent().siblings('.roleDp').remove();
                    $(this).parent().siblings('.functionDp').remove();
                    $(this).parent().parent().append(result_roles_template);
                    $(this).attr("insert-flag","1");
                }
            } else {
                $(this).parent().siblings('.roleDp').remove();
                $(this).parent().siblings('.functionDp').remove();
                $(this).attr("insert-flag","0");
            }
            FgMainAssignment.mainAssignInitAreYouSure();
            ComponentsDropdowns.init();
        });
    },
    
    // Function called when Onchange of the role dropdown 
    mainAssignSecondDpDownChange: function(){
        //On change of second dropdown in all cases
        $(document).on('change', '.roleDropDown', function() {
            var selectedRoleId=$(this).val(); // Selected role id
            var elementType = $('option:selected', this).attr('element-type'); // Element type
            var selectedCatId = $('option:selected', this).attr('category'); // Selected category id
            var insertFlag = $(this).attr('insert-flag'); // Flag to check the change in the dropdown value
            var optionTextRole = $('option:selected', this).attr('option-text-role'); // For checking default selection
            
            // Checking whether the change is due to the default option selection
            if(optionTextRole!=1) {
                var dynamicFinalArrayName='final'+elementType; // Generating the correct array name of grouped elements from the selected type
                var dynamicArrayName='array'+elementType; // Generating the correct array name from the selected type
                var filteredElementArray=window[dynamicFinalArrayName]; // Getting the array from name
                var elementArray=window[dynamicArrayName]; // Getting the array from name

                // Checking whether type is workgroup.
                // If then need to join the executive board of fderation and the club to once.
                // workgroup type don't have the category dropdown
                if(elementType=='Workgroup') {
                    var executiveBoardValue = $('option:selected', this).attr('executive-board');
                    if(executiveBoardValue==1) {
                        var dummyWorkgroupFunctionArray = _(elementArray).filter(function (x) { return x['is_executive_board']==executiveBoardValue;});                        
                        var clubExecutiveBoardFunctions = _(dummyWorkgroupFunctionArray).filter(function (x) { return x['functionIsFederation']==1;}); 
                        var normalExecutiveBoardFunctions = _(dummyWorkgroupFunctionArray).filter(function (x) { return x['functionIsFederation']==0;}); 
                        finalDummyWorkgroupFunctionArray=$.merge(clubExecutiveBoardFunctions,normalExecutiveBoardFunctions);
                        functionArray=FgUtility.groupByMulti(finalDummyWorkgroupFunctionArray, ['functionId']);                        
                    } else {
                        var functionArray=filteredElementArray[selectedRoleId];
                    }
                } else {
                    var functionArray=filteredElementArray[selectedCatId][selectedRoleId];
                }

                // Checking whether the function array according to the category id and the role id is not null.
                // If null, add a dummy input to make the dirty class enabled
                if(functionArray['null']== undefined) {

                    var new_assignment_function_template=$('#newAssignmentFunctionsRow').html(); // Getting function dropdown template
                    var result_function_template = _.template(new_assignment_function_template, {filterArray: functionArray,element:elementType,category:selectedCatId,role:selectedRoleId,normalArray:elementArray,listingAllAssignmentData:listingAllAssignmentData,clubType:clubType}); // Loading function dropdown using underscore.js

                    // If ythis flag is 0, just append the new html with the current. 
                    // Otherwise, remove the already populated roles,functions and append from begining 
                    if(insertFlag ==0) {
                        $(this).parent().parent().append(result_function_template);
                        $(this).attr("insert-flag","1");
                    } else {
                        $(this).parent().siblings('.functionDp').remove();
                        $(this).parent().parent().append(result_function_template);
                        $(this).attr("insert-flag","1");
                    }
                    FgMainAssignment.mainAssignInitAreYouSure();
                    ComponentsDropdowns.init();
                } else {
                    $('#assignmentForm').append('<input type="hidden" name=""/>'); // Adding dummy input tag
                    $(this).parent().siblings('.functionDp').remove();
                    FgMainAssignment.mainAssignInitAreYouSure();
                    ComponentsDropdowns.init();
                }
            } else {
                $(this).parent().siblings('.functionDp').remove();
                FgMainAssignment.mainAssignInitAreYouSure();
                ComponentsDropdowns.init();
            }

        });
    },
    
    // Function call to remove the newly added row when clicking on the corresponding close icon
    mainAssignRemoveAssignment: function(){
        //Close button of each assignment
        $(document).on('click', '.new_assig_rmv',function(event){
            if($(this).parent().parent().siblings('div').length <= 0) {
                $(this).parent().parent().parents('.newAssgmtPanl').remove();
            } else {
                $(this).parents('.new_asgn_blk').remove();
            }
            FgMainAssignment.mainAssignInitAreYouSure();
            return false;
        });
    },
    
    // Function called to reset all the changes made in the assignment page
    mainAssignResetAssignment: function(){
        //Handlying reset functionalities
        $(document).on('click', '#reset_changes',function(event){ 
            $('.new_asgn_blk').remove();
            $('.functionDp').remove();
            $('.newAssgmtPanl').remove(); // Removing new assignments
            $('select[class=roleDropDown]').val('');
            $('.inactiveblock').removeClass('inactiveblock');
            FgMainAssignment.mainAssignInitAreYouSure();
            setTimeout(function() {
                $('.new_asgn_blk_fed .bs-select').selectpicker('render');
                $('.new_asgn_blk_subfed .bs-select').selectpicker('render');
            }, 5);
        });
    },

    // Function to trigger Are you sure.
    mainAssignInitAreYouSure: function(){
        $('#assignmentForm').trigger('checkform.areYouSure');
    },
    // Function to init default functions
    initPageFunctions: function() {
        FgApp.init();
        FormValidation.init('assignmentForm', 'saveChanges', 'errorHandler');
        FgMoreMenu.initServerSide('paneltab');
        FgPopOver.customPophover(".fg-dev-contact-detail-assignments-popover", true);
    }
};