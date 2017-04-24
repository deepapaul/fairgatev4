FgClubAssignment = {
    arrayClassification : [],
    finalClassification : [],
    globalRequest: '0',
    init:function(){
        /* Valid classifications and classes*/
        $.getJSON(assignmentDropDn, function(dropdownData) {
            FgClubAssignment.arrayClassification = dropdownData.resultArray;
            FgClubAssignment.finalClassification = FgUtility.groupByMulti(FgClubAssignment.arrayClassification, ['clsfnId','clsId']);
            FgClubAssignment.assignmentSuccess();
            
        });
    },
    assignmentSuccess:function(){
        /* List the existing assignments : clubid is set globally*/
        $.getJSON(listingAssignment, function(data) {
            var template = $('#listAllClubAssigments').html();
            var Data = {content: data,
                        overviewClubId: clubid,
                        arrayClassification:FgClubAssignment.arrayClassification
                        };
            var result_data = _.template(template, Data);
            $('#assignmentListingDiv').html(result_data);
            $('#assignmentListingDiv').show();
            FgClubAssignment.initPageFunctions();
            FgUtility.changeColorOnDelete();
        });
    },
    initPageFunctions:function () {
        FgApp.init();
        FormValidation.init('clubassignmentForm', 'saveChanges', 'errorHandler');
        FgUtility.moreTab();
    },
    initialDropdown:function (thisVar,elementTitle) {
        var Template = $('#newAssignmentClassification').html();
        var Data = {normalArray:FgClubAssignment.arrayClassification};
        var result_template = _.template(Template, Data);
        if($('.insert_new_assignment_panel').children().length === 0) {
            /* initial case where there is no assignments : need to include wrapper classes*/
           $('.panel').append('<div class="insert_new_assignment_panel newAssgmtPanl"></div>');
        }
        $('.insert_new_assignment_panel').append(result_template);
        FgClubAssignment.initAreyouSure();
        ComponentsDropdowns.init();
             
    },
    initAreyouSure: function () {
        $('#clubassignmentForm').trigger('checkform.areYouSure');
    }
    
};
    /* Create a new assignment */
    $(document).off('click','.create_new_assignment');
    $(document).on('click', '.create_new_assignment',function(){ 
        var thisVar = $(this);
        var elementTitle = thisVar.attr('element-title');

        if(FgClubAssignment.globalRequest==0) {
            globalRequest=1;
            FgClubAssignment.initialDropdown(thisVar,elementTitle);
        } else if(FgClubAssignment.globalRequest==1) {
             FgClubAssignment.initialDropdown(thisVar,elementTitle);
        }
    });
    
    /* Classification drop down : On change event*/
    $('body').on('change', '.classificationDropDown', function() {
        var selectedClassificationId ='cfn'+$(this).val();
        var insertFlag = $(this).attr('insert-flag');
        var optionTextClassification = $('option:selected', this).attr('option-text-classification');
        if(optionTextClassification != 1) {
            var ClassificationArray = FgClubAssignment.finalClassification[selectedClassificationId];
            var new_assignment_class_template= $('#newAssignmentClass').html();
            var Data = {clubId:clubid,filterArray: ClassificationArray,normalArray:FgClubAssignment.arrayClassification};
            var result_class_template = _.template(new_assignment_class_template, Data );

            if(insertFlag == 0) {
                $(this).parent().parent().append(result_class_template);
                $(this).attr("insert-flag","1");
            } else {
                $(this).parent().siblings('.classDp').remove();
                $(this).parent().parent().append(result_class_template);
                $(this).attr("insert-flag","1");
            }
        } else {
            $(this).parent().siblings('.classDp').remove();
            $(this).attr("insert-flag","0");
        }
        FgClubAssignment.initAreyouSure();
        ComponentsDropdowns.init();
    });
    
    /* Reset changes */
    $(document).on('click', '#reset_changes',function(){ 
            $('.new_asgn_blk').remove();
            $('.newAssgmtPanl').remove();
            $('.inactiveblock').removeClass('inactiveblock');
            FgClubAssignment.initAreyouSure();
        });
    
    /* Remove new assignment (before save)*/
    $(document).on('click', '.new_assig_rmv',function(){
        if($(this).parent().siblings('div').length <= 0) {
            $(this).parent().parents('.newAssgmtPanl').remove();
        } else {
            $(this).parents('.new_asgn_blk').remove();
        }
        FgClubAssignment.initAreyouSure();
        return false;
    });
        
    /* Save function */
    function saveChanges() {
        var emptySelectFlag = 0;
        $('#failcallback').hide();
        $('#failcallbackServerSide').hide();
        $('select').each(function(){
            var _this= $(this);
            _this.parent().removeClass("has-error");
            var selectedVal = _this.val();
            if(selectedVal === '' || selectedVal === ' ') {
                emptySelectFlag = 1;
                 _this.parent().addClass("has-error");
                $('#failcallback').show();
//                scroll to top common form error alert on failing validation
                FgXmlHttp.scrollToErrorDiv();
            }
        });
        if(emptySelectFlag===0) {
        $('.new_asgn_blk').each(function() {
            var selectedOption = $(this).find('select:last option:selected');
            var dataKey = selectedOption.attr('data-key');
            var dataName = selectedOption.attr('name');
            var dataValue = selectedOption.val();
            var keyElement = $(this).find('input[type=hidden][data-type=key_element]');
            $(keyElement).attr({'name': dataName + '_is_new', 'data-key': dataKey + '.is_new', 'value': dataValue, 'class': 'fairgatedirty'});
        });
        var objectGraph = {};
        //parse the all form field value as json array and assign that value to the array
        objectGraph=  FgParseFormField.fieldParse();
        var classificationArr = JSON.stringify(objectGraph);
        FgXmlHttp.post(updateAssignment, {'classificationArr': classificationArr} , false, successCallbackFunctions, failCallbackFunctions, '0');
       }      
    }

    /* Success Callback */
    function successCallbackFunctions() {
        FgPageTitlebar.setMoreTab();
    }

    /* FailCallback function */
    function failCallbackFunctions(errorData) {
        $('#failcallback').hide();
        $('#failcallbackServerSide').hide();
        var errorMsg = errorData.flash;
        var errorArray = errorData.errorArray;
        _.each(errorArray, function(value,key) {
            var idPattern=key+'_class_'+value;
            $('option[id^='+idPattern+']').each(function(){
                $(this).parent().parent().parent().addClass("has-error");
            });
        });
        $('#failcallbackServerSide span').text(errorMsg);
        $('#failcallbackServerSide').show();
//        scroll to top common form error alert on failing validation
        FgXmlHttp.scrollToErrorDiv();
    }



