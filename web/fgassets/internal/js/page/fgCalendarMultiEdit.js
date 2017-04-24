/**
 * 
 * Multiple edit appointment caendar
 * 
 */

var FgMultiEditApp = {
            init: function(){
                FgMultiEditApp.clickDiscard();
                FgMultiEditApp.clickSave();
                FgMultiEditApp.displayEventNames();
                FgMultiEditApp.clickToggle();
                FgMultiEditApp.toggleAreaScopeGroup();
                FgMultiEditApp.disableGroup();
            },
            /**
             * discard chages
             * 
             */
            clickDiscard: function(){
                $('#reset_changes').on('click',function(){
                    $('#multiEditForm').html(initialHtml);
                    FgFormTools.selectpickerViaAjax(categoryPath,1);
                    FgMultiEditApp.init();
                    $('select.selectpicker').selectpicker('render');
                });
                
            },
            /**
             * on click save event 
             *
             */
            clickSave: function(){
                $('#save_changes').on('click',function(){
                    var validation = 0;
                    var idSelect = ($('#group').is(':checked'))? "fg-event-areas-div-group":"fg-event-areas-div";
                    var classSelect = ($('#group').is(':checked'))? "fg-event-areas-group":"fg-event-areas";
                    if($('#'+idSelect+' .'+classSelect).val()== null || $('#'+idSelect+' .'+classSelect).val()== '' ){
                        validation = 1;
                        $('#'+idSelect).addClass("has-error");
                        $('#'+idSelect).children().find('.help-block').text(required);
                    }else{
                        $('#'+idSelect).removeClass("has-error");
                        $('#'+idSelect).children().find('.help-block').text('');
                    }
                    if($('.fg-event-categories').val() == null ||  $('.fg-event-categories').val()== ''){
                        validation = 1;
                        $('#fg-event-categories-div').addClass("has-error");
                        $('.fg-event-categories').parent().find('.help-block').text(required);
                    }else{
                        $('#fg-event-categories-div').removeClass("has-error");
                        $('.fg-event-categories').parent().find('.help-block').text('');
                    }
                    // Checking validation. If any, should display error message
                    if (validation == 0) { 
                        $('#failcallbackServerSide').hide();
                        var objectGraph = {};
                        FgDirtyFields.updateFormState();
                        //parse the all form field value as json array and assign that value to the array
                        
                        objectGraph = FgInternalParseFormField.fieldParse();
                        objectGraph['calendar_details'] = finalArray;
                        
                        if(typeof objectGraph['scope'] == 'undefined'){
                            objectGraph['scope'] = 'PUBLIC';
                            if(typeof objectGraph['areas-group'] != 'undefined'){
                                delete objectGraph['areas-group'];
                            }
                        }
                         if( objectGraph['scope'] == 'GROUP'){
                                objectGraph['areas'] = [];
                                objectGraph['areas'][0] = objectGraph['areas-group'];
                                delete objectGraph['areas-group'];
                        }
                        var multiEditArr = JSON.stringify(objectGraph);
                        // if repeated events present shoow popup
                        if(repeat == 1){
                            $.post(editPopUpPath, { 'count':_.size(jsonRowIds), 'editArr':multiEditArr}, function(data) {
                                FgModelbox.showPopup(data);
                            });
                        }else{
                            FgDirtyFields.removeAllDirtyInstances();
                            FgXmlHttp.post(editSavePath, {'saveData': multiEditArr}, false, false);
                        }
                        
                   } else {
                        $('#failcallbackServerSide').show(); // Displaying errors
                        //scroll to top common form error alert on failing validation
                        FgXmlHttp.scrollToErrorDiv();
                    }
                });
            },
            /**
             * save changes for repeated event on popup 
             * @param array multiEditArr
             * 
             */
            saveChanges: function(multiEditArr){
                FgDirtyFields.removeAllDirtyInstances();
                FgXmlHttp.post(editSavePath, {'saveData': multiEditArr}, false, false);
                
            },
            /**
             * initialize dirty field
             *
             */
            fgDirtyField: function(){
                FgDirtyFields.init('multiEditForm', {
                    dirtyFieldSettings :{
                    }, 
                        setNewFieldsClean:false ,   
                        enableDragDrop : false, 
                        enableUpdateSortOrder : false, 
                        enableDiscardChanges : false,
                       // denoteDirtyOptions:false

                });
            },
            /**
             * display event names
             *
             */
              displayEventNames: function () {
                        var appHtml = '';
                        var appLinks = {};
                        var i = 0;
                        $.each(finalArray, function(key, result) {
                            i++;
                            if (i == 11) {
                                appHtml += '<li>&hellip;</li>';
                                return false;
                            } else {
                                appLinks[result.id] = result.title;
                                detailPath1 = detailPath.replace('dummyId',result.id);
                                appHtml += '<li><a href="'+detailPath1+'" target="_blank" data-club-id="'+result.id+'"></a></li>';
                            }
                        });
                        $('#eventNames').html('<ul>' + appHtml + '</ul>');
                        FgMultiEditApp.displayAppNames(appLinks);
                        

                }
            ,
            /**
             * display appointment names
             * @param string appLinks
             * 
             */
            displayAppNames : function(appLinks) {
               $.each(appLinks, function(selAppId, selAppName) {
                   $('a[data-club-id='+selAppId+']').text(selAppName);
               });
            },
            /**
             * click create category
             * 
             */
            clickCreateCategory: function(){
                $('body').on('click', '.fg-dev-cat', function () {
                    var rand = $.now();
                    $.post(categoryCreatePath, {'catId':rand, 'defaultLang': defLang,'noParentLoad':true }, function(data) {             
                        FgModelbox.showPopup(data);         
                    });      
                });
            },
            /**
             * click toggle to show event names
             * 
             */
            clickToggle: function(){
                $('.fg-action-toggle').on('click',function(){
                    $(this).toggleClass('open');
                    $('#eventNames').toggleClass('hide');
                });
            },
            /**
             * on change toggle switch
             *
             */
            toggleAreaScopeGroup: function(){
//                $('.switch-toggle').on('change',function(){
//                    if($('#group').is(':checked')){
//                        if($('#fg-event-areas').find('option:eq(0)').val() == 'Club'){
//                            $('#fg-event-areas').find('option:eq(0)').prop('checked',false);
//                            $('#fg-event-areas').find('option:eq(0)').prop('disabled',true);
//                            
//                        }
//                        $('#fg-event-areas').prop('multiple',false)
//                    }else{
//                        $('#fg-event-areas').prop('multiple',true)
//                        $('#fg-event-areas').find('option:eq(0)').prop('disabled',false)
//                    }
//                    $('.selectpicker').selectpicker('refresh')
//
//                });
                $('.switch-toggle').on('change',function(){
                    if($('#group').is(':checked')){
                        $('#fg-event-areas-div').addClass('hide')
                        $('#fg-event-areas-div-group').removeClass('hide')
                    }else{
                        $('#fg-event-areas-div').removeClass('hide')
                        $('#fg-event-areas-div-group').addClass('hide')
                    }
                });
            },
            /**
             * disable group switch
             * 
             */
            disableGroup: function(){
                 if(_.size(assignedTeams)+_.size(assignedWorkgroups) == 0) {
                     $('#group').prop('disabled',true)
                 }
            }
            
        }
       