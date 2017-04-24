        function handleAssignmentError(){
            var randomAssignNum=$('#randomAssignNum').val();
            var assignErrFlag=$('#assignErrFlag').val();
            var errorType=$('#errorType').val();
            var errorArray=$('#errorArray').val();

            if(assignErrFlag==1 || errorArray!='' || errorType !='' || errorType !=0) {
                var assignmentHtmlFrmSession=sessionStorage.getItem("assignmentHtml"+randomAssignNum);
                $('#fullAssignmentSection').html('');
                $('#fullAssignmentSection').html(assignmentHtmlFrmSession);
            }
            if(assignErrFlag==1) {
                $('.new-assignment-panel select').each(function(){
                    assignErrorClass($(this));
                });
                $('#new-fedmember-assignment-panel select').each(function(){
                    assignErrorClass($(this));
                });
                $('#failcallbackServerSide span').text(transVal);
                $('#failcallbackServerSide').show();

            } else if(assignErrFlag==0) {
                $('#failcallbackServerSide span').text("");
                $('#failcallbackServerSide').hide();
            }
            if(errorArray!='') {
                errorArray=JSON.parse(errorArray);
                _.each(errorArray, function(value,key) {
                    $('option[id^='+key+']').each(function(){
                        $(this).parent().parent().parent().addClass("has-error");
                    });
                });
                $('#failcallbackServerSide span').text(errorType);
                $('#failcallbackServerSide').show();
            } else if(errorType !='' && errorType !=0) {
                $('#failcallbackServerSide span').text(errorType);
                $('#failcallbackServerSide').show();
            }
        }
        function assignErrorClass(_this) 
        {
            _this.parent().removeClass("has-error");
            var selectedVal=_this.val();
            if(selectedVal==='' || selectedVal===' ') {
                _this.parent().addClass("has-error");
            }
        }
        function customFieldParse() {
            var objectArray = {};
            $("#fullAssignmentSection :input").each(function() {
                if ($(this).hasClass("fg-dev-finalKeyVal")) {
                    var inputVal = ''
                    inputVal = $(this).val();
                    if (inputVal != "" && inputVal != '' && inputVal != "on" && inputVal!= "NormalRoles" && inputVal!= "Federation" && inputVal!= "Subfederation" && inputVal!= "Team" && inputVal!= "Workgroup") {
                        converttojson(objectArray, $(this).attr('data-key').split('.'), inputVal);
                    }
                }
            });
            return objectArray;
        }
        
        // Section for  handling Federation membership categories
        
        function listFederationCategories() {
            var new_fedMemberCategory_template=$('#newFedMemberCategory').html();
            var result_template = _.template(new_fedMemberCategory_template, {filterArray: finalMembershipCat,normalArray:arrayMembershipCat,elementtype: 'MembershipCat',finalFederation:finalFederation,finalSubfederation:finalSubfederation});
            $('#new-fedmember-assignment-panel').append(result_template);
            FgFormTools.handleBootstrapSelect();
        }
        
        
        function validateAssignments(){
            var errorFlag=0;
            $('.new-assignment-panel select').each(function(){
                var _this= $(this);
                _this.parent().removeClass("has-error");
                var selectedVal=_this.val();
                if(selectedVal==='' || selectedVal===' ') {
                    errorFlag=1;
                }
            });
            $('#new-fedmember-assignment-panel select').each(function(){
                var _this= $(this);
                _this.parent().removeClass("has-error");
                var selectedVal=_this.val();
                if(selectedVal==='' || selectedVal===' ') {
                    errorFlag=1;
                }
            });
            if(errorFlag==1) {
                return 1;
            } else {
                return 0;
            }
        }
        
        function initAreyouSure() {
            $('#form1').trigger('checkform.areYouSure');
        }
        $(document).off('click','.create_new_assignment');
        $(document).on('click', '.create_new_assignment',function(event){ 
            
            var thisVar=$(this);
            var elementTitle=thisVar.attr('element-title');
            var elementType=thisVar.attr('element-type');
            var new_assignment_initial_template=$('#newAssignmentRowInitial').html();
            var initial_template = _.template(new_assignment_initial_template,{clubType: clubType,federation:arrayFederation,subfederation:arraySubfederation,normalRoles:arrayNormalRoles,team:arrayTeam,workgroup:arrayWorkgroup});
            thisVar.parent().parent().siblings('#fullAssignmentSection').children('.new-assignment-panel').append(initial_template); 
            FgFormTools.handleBootstrapSelect();
            initAreyouSure();
            thisVar.hide();

        });

        $('body').on('change', '.roleType', function() {
            var thisVar=$(this);
            var elementType = $('option:selected', this).attr('element-type');
            var insertFlag = $(this).attr('insert-flag');
            var optionTextCat = $('option:selected', this).attr('option-text-category');
            $(this).find('option').removeAttr('selected');
            //$(this).val(elementType);
            $(this).find('option[value='+elementType+']').attr('selected',true);
            initialDropdown(thisVar,elementType,insertFlag,optionTextCat);
        });

        function initialDropdown(thisVar,elementType,insertFlag,optionTextCat) {
            var dynamicFinalArrayName='final'+elementType;
            var dynamicArrayName='array'+elementType;
            var filteredElementArray=window[dynamicFinalArrayName];
            var elementArray=window[dynamicArrayName];
            if(optionTextCat!=1) {
                if(elementType=='Workgroup') {
                    var new_assignment_roles_template=$('#newAssignmentRolesContact').html();
                    var result_template = _.template(new_assignment_roles_template, {filterArray: filteredElementArray,element:elementType,normalArray:elementArray,loggedClubId:clubId});
                } else {
                    var new_assignment_template=$('#newAssignmentCategory').html();
                    var result_template = _.template(new_assignment_template, {filterArray: filteredElementArray,normalArray:elementArray,elementtype: elementType});
                } 
                if(insertFlag == 0) {
                    thisVar.parent().parent().append(result_template);
                    thisVar.attr("insert-flag","1");
                } else {
                    thisVar.parent().siblings('.categoryDp').remove();
                    thisVar.parent().siblings('.roleDp').remove();
                    thisVar.parent().siblings('.functionDp').remove();
                    thisVar.parent().parent().append(result_template);
                    thisVar.attr("insert-flag","1");
                }
                $('.create_new_assignment').hide();
            } else {
                thisVar.parent().siblings('.categoryDp').remove();
                thisVar.parent().siblings('.roleDp').remove();
                thisVar.parent().siblings('.functionDp').remove();
                thisVar.attr("insert-flag","0");
                $('.create_new_assignment').hide();
            }
            initAreyouSure();
            ComponentsDropdowns.init();
        }
        
        
        $('body').on('change', '.catDropDown', function() {
            var selectedCatId=$(this).val();
            var elementType = $('option:selected', this).attr('element-type');
            var insertFlag = $(this).attr('insert-flag');
            var optionTextCat = $('option:selected', this).attr('option-text-category');
            $(this).find('option').removeAttr('selected');
            $(this).find('option[value='+selectedCatId+']').attr('selected',true);
            if(optionTextCat!=1) {
                var dynamicFinalArrayName='final'+elementType;
                var dynamicArrayName='array'+elementType;
                var filteredElementArray=window[dynamicFinalArrayName];
                var elementArray=window[dynamicArrayName];
                var rolesArray=filteredElementArray[selectedCatId];
                var new_assignment_roles_template=$('#newAssignmentRolesContact').html();
                var result_roles_template = _.template(new_assignment_roles_template, {filterArray: rolesArray,element:elementType,category:selectedCatId,normalArray:elementArray});

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
                $('.create_new_assignment').hide();
            }
            initAreyouSure();
            ComponentsDropdowns.init();
        });

        $(document).on('change', '.roleDropDown', function() {
            var selectedRoleId=$(this).val();
            var elementType = $('option:selected', this).attr('element-type');
            var selectedCatId = $('option:selected', this).attr('category');
            var insertFlag = $(this).attr('insert-flag');
            var optionTextRole = $('option:selected', this).attr('option-text-role');
            $(this).find('option').removeAttr('selected');
            $(this).find('option[value='+selectedRoleId+']').attr('selected',true);
            
            if(optionTextRole!=1) {
                var dynamicFinalArrayName='final'+elementType;
                var dynamicArrayName='array'+elementType;
                var filteredElementArray=window[dynamicFinalArrayName];
                var elementArray=window[dynamicArrayName];

                if(elementType=='Workgroup') {
                    var executiveBoardValue = $('option:selected', this).attr('executive-board');
                    if(executiveBoardValue==1) {
                        var dummyWorkgroupFunctionArray = _(elementArray).filter(function (x) { return x['is_executive_board']==executiveBoardValue;});                        
                        functionArray=FgUtility.groupByMulti(dummyWorkgroupFunctionArray, ['functionId']);                        
                    } else {
                        var functionArray=filteredElementArray[selectedRoleId];
                    }
                } else {
                    var functionArray=filteredElementArray[selectedCatId][selectedRoleId];
                }

                if(functionArray['null']== undefined) {

                    var new_assignment_function_template=$('#newAssignmentFunctionsContact').html();
                    var result_function_template = _.template(new_assignment_function_template, {filterArray: functionArray,element:elementType,category:selectedCatId,role:selectedRoleId,normalArray:elementArray,clubType:clubType});
                    if(insertFlag ==0) {
                        $(this).parent().parent().append(result_function_template);
                        $(this).attr("insert-flag","1");
                    } else {
                        $(this).parent().siblings('.functionDp').remove();
                        $(this).parent().parent().append(result_function_template);
                        $(this).attr("insert-flag","1");
                    }
                } else {
                    $('.create_new_assignment').show();
                    $(this).parent().siblings('.functionDp').remove();
                }
            } else {
                $(this).parent().siblings('.functionDp').remove();
                $('.create_new_assignment').hide();
            }
            initAreyouSure();
            ComponentsDropdowns.init();

        });  
        
        $(document).on('change', '.functionDropDown', function() {
            var selectedFunctionId=$(this).val();
            $(this).find('option').removeAttr('selected');
            $(this).find('option[value='+selectedFunctionId+']').attr('selected',true);
            var optionTextRole = $('option:selected', this).attr('option-text-role');
            if(optionTextRole!=1) {
                $('.create_new_assignment').show();
            } else {
                $('.create_new_assignment').hide();
            }
            initAreyouSure();
            ComponentsDropdowns.init();
        });

        $(document).on('click', '.new_assig_rmv',function(event){
            $(this).parent().parents('.roleTypeDpdn').remove();
            $('.create_new_assignment').show();
            initAreyouSure();
            return false;
        });
function assignmentSuccess(){}