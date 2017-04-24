        
        $(document).ajaxComplete(function() {
            FgPageTitlebar.checkMissingTranslation(sysLang);
        });
        //field sort success function
        function doAfterSort(item, parentElement) {
            var i = 1;
            $(parentElement).find('.order').each(function() {
                oldSortValue=$(this).val();
                $(this).val(i);
                if(i != oldSortValue){
                    $(this).addClass('newAttrSort')
                }
                i++;
            });
            $(parentElement).trigger('checkform.areYouSure');
        }
        //category sort success function
        function stopSortAction(parentElement) { 
            var i = 1;
            $(parentElement).find('.sortable .catOrder').each(function() {
                oldSortValue=$(this).val();
                $(this).val(i);
                if(i != oldSortValue){
                    $(this).addClass('newCatSort')
                }
                i++;
            });
            $(parentElement).trigger('checkform.areYouSure');
        }
        //new field delete start                    
        $(document).on('click', '.newRowDelete', function() {
            $(this).parents('div[data-row]').slideUp(function(){ 
                $(this).remove();
            });
            $('.contact_area').trigger('checkform.areYouSure');
        });
        //new field delete end
        //new category delete start
        $(document).on('click', '.newCatDelete', function() {
            $(this).parents('.fieldArea').parent().slideUp(function(){ 
                $(this).remove();
            });
            $('.contact_area').trigger('checkform.areYouSure');
        });
        //reset changes starts
        $(document).on('click', '#reset_changes', function() {
            $('.newRow').closest('.row').remove();
            $('.inactiveblock').removeClass('inactiveblock');
            setTimeout(function(){ //update uniform
                $('.contact-field-selectpicker.fairgatedirty').selectpicker('render');
                $.uniform.update();
            },500);
            //reorder category
            $('.contact_area').find('.sortable').each(function(){
                if($(this).find('.catOrder').hasClass('newCatSort')){
                    var initialOrder=$(this).attr('data-catsortorder');
                    var destination=parseInt(initialOrder)-1;
                    $( this ).insertAfter( "div[data-catsortorder="+destination+"]" );
                    $( this ).find('.catOrder').val(initialOrder);
                }
            });
            //reorder fields
            $('.contact_area').find('div[data-catsortorder]').each(function(){
                if($(this).find('.order').hasClass('newAttrSort')){
                    var catorder=parseInt($(this).attr('data-catsortorder'));
                    var totalRows=$(this).find('.catFieldSort').children('.childrow').length;
                    for (var i = 1; i <=totalRows ; i++) { 
                        $('div[data-catsortorder='+catorder+'] div[data-fieldsortorder='+i+']').insertAfter("div[data-catsortorder="+catorder+ "] div[data-fieldsortorder]:last-child" );
                        $('div[data-catsortorder='+catorder+'] div[data-fieldsortorder='+i+']').find('.order').val(i);
                    }
                }
            });
            $('#form1').trigger('checkform.areYouSure');
        });
        //reset changes ends
        //Add new field start
        $(document).on('click', '.contact_area .addField', function() {
            addFieldCount = 'new_' + $.now();
            var catId = $(this).attr('data-catId');
            var activeLang = $('button.btlang.adminbtn-ash').attr('data-selected-lang');
            activeLang= (activeLang==='' || activeLang === undefined) ? sysLang:activeLang;
            var parentDiv = $(this).closest('.fieldArea');
            order = $(parentDiv).find('input[type=hidden].order:last').val();
            var result_data = FGTemplate.bind('newContactField', {content: {'addCount': addFieldCount, 'catId': catId, 'activelang': activeLang}});
            $(parentDiv).find('.catFieldSort').append(result_data);
            order=isNaN(parseInt(order)) ? 0:parseInt(order);
            $(parentDiv).find('input[type=hidden].order:last').val(parseInt(order) + 1);
            $(parentDiv).find('#'+addFieldCount+' .contact-field-selectpicker').selectpicker({
                noneSelectedText: datatabletranslations.noneSelectedText,
                countSelectedText: datatabletranslations.countSelectedMembership+'  <span class="fg-contact-mandatory"></span>',
            });
            FgUtility.showTranslation(activeLang);
            selectfunction();
            FgDragAndDrop.initWithChild($(parentDiv).find('.catFieldSort'));
            $(parentDiv).trigger('checkform.areYouSure');
            FgTooltip.init();
        });
        //Add new field end
        //Add new category start
        $(document).on('click', '.contact_area .addCategory', function() {
            var catid= 'newCat_' + $.now();
            addNewCategory(catid, this);
        });
        function addNewCategory(defaultData, itemObject) {
            catOrder = $('div.contact_area .sortable:last').find('input[type=hidden].catOrder:last').val();
            var htmlFinal = FGTemplate.bind('addNewCategory', {data: {'catId':defaultData}});
            $('.page-content').find('div.contact_area').append(htmlFinal);
            catorder=isNaN(parseInt(catOrder)) ? 0:parseInt(catOrder);
            $('div.contact_area:last').find('input[type=hidden].catOrder:last').val(catorder + 1);
            var activeLang = $('button.btlang.adminbtn-ash').attr('data-selected-lang');
            activeLang= (activeLang==='' || activeLang ===  undefined) ? sysLang:activeLang;
            FgUtility.showTranslation(activeLang);
            $('div.contact_area').trigger('checkform.areYouSure');
            FgTooltip.init();
        }
        //Add new category end
        $(window).on('load', function() {
            $('div[data-visible]:last .addCategory').show();
            FgUtility.changeColorOnDelete(); 
        if (clubType == 'federation' || clubType == 'sub_federation') {
            var selectedText = datatabletranslations.countSelectedFedMembership;
        } else if (clubmembershipAval) {
             var selectedText = datatabletranslations.countSelectedMembership;
        } else {
           var selectedText = datatabletranslations.countSelectedFedMembership;
        }

            $('.contact-field-selectpicker').each(function(){                
                $(this).selectpicker({
                    noneSelectedText: datatabletranslations.noneSelectedText,
                    countSelectedText: selectedText+'  <span class="fg-contact-mandatory"></span>'
                });
                if($(this).find(":selected").hasClass( "fg-option-mandatory" )) {
                    $(this).parent().find('.bootstrap-select').find('button.dropdown-toggle').addClass('fg-btn-mandatory');
                }                 
            });
            selectfunction();                
        });
        //Function to handle select for mandatory selection
        function selectfunction() {
            $('.single').on('click',function(){ //alert(2);
                $(this).parents('.bootstrap-select').parent().find('select.contact-field-selectpicker option').prop("selected", false);
                $(this).parents('.bootstrap-select').parent().find('select.contact-field-selectpicker').selectpicker('render');
                $(this).parents('.bootstrap-select').find('button.dropdown-toggle').removeClass('fg-btn-mandatory');                
            });
             $('.multiple').on('click',function(){
                $(this).parents('.bootstrap-select').parent().find('select.contact-field-selectpicker .single').prop("selected", false);
                var totSelected=$(this).parents('ul').find('li.selected').size();
                if(totSelected == '1' && $(this).parents('li').hasClass('selected'))
                { 
                    $(this).parents('.bootstrap-select').parent().find('select.contact-field-selectpicker .single:first').prop("selected", true);
                }
                $(this).parents('.bootstrap-select').parent().find('select.contact-field-selectpicker').selectpicker('render');
                $(this).parents('.bootstrap-select').find('button.dropdown-toggle').removeClass('fg-btn-mandatory');                
            });   
            $( ".fg-option-mandatory" ).click(function() {
                $(this).parents('.bootstrap-select').find('button.dropdown-toggle').addClass('fg-btn-mandatory');
            });
        }

        $(document).ready(function() {
            FgUtility.showTranslation(sysLang);
            //category sorting
            FgDragAndDrop.categorySort('.contact_area', true);
            //contact field sorting
            $('.catFieldSort').each(function() {
                FgDragAndDrop.initWithChild(this);
            });
            FgApp.init();
            $('.btn-group button.btlang').click(function() {
                var lang = $(this).attr('data-selected-lang');
                FgUtility.showTranslation(lang);
            });
            FormValidation.init('form1', 'saveChanges');
            //VALIDATION AREA
            $('.propertyClick').children('.fa-minus-square-o').hide();
        });
            $(document).on('change', '.popupDivContent .propCategory', function() {

                var attributeId = $(this).attr('attributeId');
                var categoryId = $(this).attr('categoryId');
                var categoryVal = $(this).val();
                if (categoryVal != system_category_address) {
                    $('#usedForWrapper_' + categoryId + '_' + attributeId).hide();
                } else {
                    $('#usedForWrapper_' + categoryId + '_' + attributeId).show();
                }
            });

            $(document).on('change', '.popupDivContent .propContactFieldType', function() {

                var contactFieldArray = ['checkbox', 'select', 'radio'];
                var attributeId = $(this).attr('attributeId');
                var categoryId = $(this).attr('categoryId');
                var contactFieldVal = $(this).val();
                if ($.inArray(contactFieldVal, contactFieldArray) > -1) {
                    $('#propValuesWrapper_' + categoryId + '_' + attributeId).show();
                    $('input[name=propertyValues_' + categoryId + '_' + attributeId + ']').attr("required", "true");
                } else {
                    $('#propValuesWrapper_' + categoryId + '_' + attributeId).hide();
                    $('input[name=propertyValues_' + categoryId + '_' + attributeId + ']').removeAttr("required");
                }
            });

            $(document).on('change', '.popupDivContent .propCategory', function() {
                var attributeId = $(this).attr('attributeId');
                var categoryId = $(this).attr('categoryId');
                var categoryVal = $(this).val();
                $('#propAvailableWrapper_' + categoryId + '_' + attributeId + ' input:radio').attr("disabled", false);
                if (categoryVal == 1) {
                    $('#availableFor_' + categoryId + '_' + attributeId + '_person').prop('checked', true);
                    $('#availableFor_' + categoryId + '_' + attributeId + '_company').prop('checked', false);
                    $('#availableFor_' + categoryId + '_' + attributeId + '_both').prop('checked', false);
                    $('#propAvailableWrapper_' + categoryId + '_' + attributeId + ' input:radio').attr("disabled", true);
                } else if (categoryVal == 3) {
                    $('#availableFor_' + categoryId + '_' + attributeId + '_company').prop('checked', true);
                    $('#availableFor_' + categoryId + '_' + attributeId + '_person').prop('checked', false);
                    $('#availableFor_' + categoryId + '_' + attributeId + '_both').prop('checked', false);
                    $('#propAvailableWrapper_' + categoryId + '_' + attributeId + ' input:radio').attr("disabled", true);
                }
                $.uniform.update();
            });

            $(document).on('click', '.propertyClick', function() {
                  var attributeId = $(this).attr('attributeId'); 
                  var mainWraper = '#propertiesSection' + attributeId;
                  if ($(this).find('.fa-plus-square-o').is(":visible")) {
                      $("#makespan").is(":visible") == true
                      var thisVar = $(this).find('.fa-plus-square-o');
                      var categoryId = $(this).attr('categoryId');
                      path = pathProperty+'&attributeId=' + attributeId + '&categoryId=' + categoryId;
                      var propertyLoadedStatus = $(this).attr('propertyLoadedStatus');
                      var underscoreTemplateId = '#propertiesrow';
                      var displayStatusAttr = 'propertyLoadedStatus';
                      if (propertyLoadedStatus == 0) {
                          FgUtility.startPageLoading();
                          renderUnderscoreTemplate(thisVar, attributeId, categoryId, displayStatusAttr, underscoreTemplateId, mainWraper, path, clubUrlIdentifier);
                       }
                      showOnClick( $(this).find('.fa-plus-square-o'), attributeId, displayStatusAttr, mainWraper);
                  } else {
                      hideOnClick($(this).find('.fa-minus-square-o'), mainWraper);
                  }
            });
         
        //Function to show collapse/toggle                            
        function hideOnClick(thisVar, hideBlock)
        {
            $(hideBlock).slideUp();
            thisVar.parent().find('.fa-plus-square-o').show();
            thisVar.parent().find('.fa-minus-square-o').hide();
        }
        //Function to show collapse/toggle    
        function showOnClick(thisVar, attributeId, displayStatusAttr, mainWraper)
        {
            $('#propertiesSection'+attributeId).slideUp();
            $('#federationSection' + attributeId).slideUp();
            $('#profileSection' + attributeId).slideUp();
            thisVar.parent().parents('.popupClickArea').find('.fa-minus-square-o').hide();
            thisVar.parent().parents('.popupClickArea').find('.fa-plus-square-o').show();
            var displayStatus = thisVar.parent().attr(displayStatusAttr);
            if (displayStatus == 1) {
                $(mainWraper).slideDown(600);
            }
            thisVar.parent().find('.fa-plus-square-o').hide();
            thisVar.parent().find('.fa-minus-square-o').show();
        }
        
        //Function render template using underscore
        function renderUnderscoreTemplate(thisVariable, attributeVal, categoryVal, displayStatusAttribute, underscoreTemplateId, templateWraperId, urlPath, clubUrlIdentifier) {
            $.getJSON(urlPath, function(data) {
                $('#form1').find('.fairgatedirty').addClass('dummyDirty');                                        
                var template = $(underscoreTemplateId).html();
                var result_data = _.template(template, {content: data, 'attributeId' : attributeVal});
                $(templateWraperId).hide().html(result_data);                
                $(templateWraperId+' .tags').tagsInput({defaultText:textAddTag, width: 'auto', minInputWidth: 'auto', 'onChange' : function() {$(this).addClass('fairgatedirty');}});
                $(templateWraperId+' .selectpicker').selectpicker('render');
                var activeLang = $('button.btlang.adminbtn-ash').attr('data-selected-lang');
                activeLang= (activeLang==='' || activeLang ===  undefined) ? sysLang:activeLang;
                FgUtility.showTranslation(activeLang);
                FgDirtyForm.rescan('form1');
                $('#form1').find('.dummyDirty').addClass('fairgatedirty').removeClass('dummyDirty');
                FgInputTag.handleUniform();
                FgUtility.stopPageLoading();
                $(templateWraperId).slideDown();
                
            });
            thisVariable.parent().attr(displayStatusAttribute, '1');
            thisVariable.parent().find('.fa-plus-square-o').hide();
            thisVariable.parent().find('.fa-minus-square-o').show();

            return;
        }
        //Function to do before save changes
        function saveChanges() {
            $(".newRow").find("input").addClass('fairgatedirty');
            $(".newRow").find("select").addClass('fairgatedirty');
            $('div[data-row].newRow').parents('.fieldArea').find('input.order').addClass('fairgatedirty');
            $(".newAttrSort").addClass('fairgatedirty');
            $(".newCatSort").addClass('fairgatedirty');
            var objectGraph = {};           
            $("form :input").each(function() {                                        
                if ($(this).hasClass("fairgatedirty")) {
                    var inputVal = ''
                    if ($(this).attr('type') == 'checkbox') {
                        inputVal = $(this).attr('checked') ? 1 : 0;
                    } else if ($(this).attr('type') == 'radio') {
                         if($(this).attr('checked'))inputVal = $(this).val();
                    }else{
                        inputVal = $(this).val();
                    }
                    if (inputVal !== '') {
                        if( typeof $(this).attr('data-key') !== 'undefined'){                              
                            converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                        }
                    }
                }
            });
            var attributes = JSON.stringify(objectGraph);
            var setDeleteField = 0;
            if($(".closeico").find('input[type=checkbox]:checked').length > 0) {  // if any field is deleted confirm before save
                $(".closeico").find('input[type=checkbox]:checked').each(function() {
                    if($(this).attr('data-key').indexOf("fields") > 0) {
                        setDeleteField++;
                    }
                });
            }
            if(setDeleteField > 0) {  // if any field is deleted confirm before save
                $('#save_changes').attr("data-toggle","confirmation");   
                $('#save_changes').parent().removeClass("fg-confirm-btn").addClass("fg-confirm-btn");
                FgConfirmation.confirm(confirmNote,cancelLabel,saveLabel,$('#save_changes'), saveField, {'attributes': attributes} );               
            } else {
                saveField({'attributes': attributes});
            } 
        }
        //Function to save changes
        function saveField(params) {
            FgXmlHttp.post(pathFieldUpdate, params, false, callbackfn);
        }
        //Function to call after save changes
        function callbackfn() {
            FgApp.init();
            FgPageTitlebar.init({
                title: true,
                languageSettings: true
            });
            FgUtility.showTranslation(sysLang);
            FormValidation.init('form1', 'saveChanges');
            FgDragAndDrop.categorySort('.contact_area', true);
            //contact field sorting
            $('.catFieldSort').each(function() {
                FgDragAndDrop.initWithChild(this);
            });  
            $('.btn-group button.btlang').click(function() {
                var lang = $(this).attr('data-selected-lang');
                FgUtility.showTranslation(lang);
            });
            if (clubType == 'federation' || clubType == 'sub_federation') {
               var selectedText = datatabletranslations.countSelectedFedMembership;
           } else if (clubmembershipAval) {
                var selectedText = datatabletranslations.countSelectedMembership;
           } else {
              var selectedText = datatabletranslations.countSelectedFedMembership;
           }
            $('.contact-field-selectpicker').each(function(){               
                $(this).selectpicker({
                    noneSelectedText: datatabletranslations.noneSelectedText,
                    countSelectedText: selectedText+'  <span class="fg-contact-mandatory"></span>'
                });
                if($(this).find(":selected").hasClass( "fg-option-mandatory" )) {
                    $(this).parent().find('.bootstrap-select').find('button.dropdown-toggle').addClass('fg-btn-mandatory');
                }                
            });
            selectfunction();
            FgUtility.changeColorOnDelete(); 
            jQuery('.popovers').popover();
            jQuery('.tooltips').tooltip();
            $('div[data-visible]:last .addCategory').show(); 
        }
