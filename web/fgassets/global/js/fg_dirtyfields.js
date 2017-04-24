var FgDirtyFields = function() {
	var settings;
	var dirtyFieldsSettings = {};
	var $object;
	var defaultSettings = {
                dirtyFieldSettings              : {  //configurations of dirtyfield plugins can be added here
                                                        denoteDirtyForm : true,
                                                        dirtyFormClass : "dirty",
                                                        textboxContext : "self",
                                                        selectContext : "self",
                                                        checkboxRadioContext : "self",
                                                        dirtyFieldClass : "fairgatedirty",
                                                        exclusionClass	: "dFExclude",
                                                        denoteDirtyOptions : true
                                                  },
                setNewFieldsClean		: false, // Newly added fields can be added as dirty or clean
                enableUnsaveFormAlert		: true,	// enable unsaved data alert
                enableDirtyOnKeyUp		: true, // enable dirty check on keyup also
                enableDiscardChanges		: true, // enable discard change rollback from this plugin
                enableDragDrop			: true, // enable drag drop from this plugin
                enableUpdateSortOrder		: true, // enable sort order update from this plugin
                setInitialHtml                  : true, // set initial html after discard changes
                sortOrderSelector		: ".sorthidden", //selector to identify sortorder data field
                dragDropContainerSelector	: "#role_category_sort", // drag drop container selector
                discardChangeSelector		: "#reset_changes", // selector of the discard changes button
                saveChangeSelector		: "#save_changes", // selector of the save changes button
                unsavedAlertMessage		: jstranslations.dirtyformAlert, // unsaved data alert text
                initCompleteCallback            : function($object){ },
                formChangeCallback		: function(result,dirtyFieldsArray) {}, //form change callback, uses dirty field form change callback
                fieldChangeCallback		: function(originalValue,isDirty) { }, //field change callback, uses dirty field field change callback
                discardChangesCallback          : function($object){ },
                removeFieldsCallback		: function($removedObject) { } // call back for removing a newly created field, works when "removeFields" method is called
        }; 
	// extends the initial configuration on method init		
	var initSettings = function(options) { 
		settings = $.extend(true, {}, defaultSettings, options);
                $('body').addClass('dirty_field_used');
	}
	// extends the initial configuration of dirtyfields plugin	
	var initDirtyFileds = function()  {

		dirtyFieldsSettings = {
                                            fieldChangeCallback: function(originalValue,isDirty) { 
                                                    settings.fieldChangeCallback.call($(this), originalValue, isDirty); 
                                            },
                                            formChangeCallback: function(result,dirtyFieldsArray) {
                                                    if (result) {
                                                            $object.find(settings.saveChangeSelector).removeAttr('disabled');
                                                            $object.find(settings.discardChangeSelector).removeAttr('disabled');
                                                    }else{
                                                            $object.find(settings.saveChangeSelector).attr('disabled', 'disabled');
                                                            $object.find(settings.discardChangeSelector).attr('disabled', 'disabled');
                                                    }
                                                    settings.formChangeCallback.call($(this), result,dirtyFieldsArray);
                                            }
                                      };
		
		dirtyFieldsSettings = $.extend(true, dirtyFieldsSettings, settings.dirtyFieldSettings); 
		$object.dirtyFields(dirtyFieldsSettings); // initialize dirtyfields plugin
		$object.find(settings.saveChangeSelector).attr('disabled', 'disabled'); //disable submit button on initial load
		$object.find(settings.discardChangeSelector).attr('disabled', 'disabled'); //disable reset button on initial load
		
		if(settings.enableUnsaveFormAlert){ unsavedAlert(); } // unsaved data alert method call
		if(settings.enableDirtyOnKeyUp){ checkDirtyOnKeyUp(); } // dirty check on keyup method call
		if(settings.enableDiscardChanges){ discardChanges(); } // discard change rollback method call
		if(settings.enableDragDrop){ handleDragAndDrop(); } // drag drop method call
		if(settings.enableUpdateSortOrder){ updateSortOrder(); } // update sort order method call
		$.fn.dirtyFields.formSaved($object);
                settings.initCompleteCallback.call($object);
	}
	// unsaved data alert method
	var unsavedAlert = function()  {
		$(window).bind('beforeunload', function() { 
			if ($object.hasClass(dirtyFieldsSettings.dirtyFormClass)) {
			  return settings.unsavedAlertMessage;
			}
		});
	}
	// bind newly added form fields
	var bindFields = function($container){ 
			$("input[type='text'],input[type='file'],input[type='password'],textarea",$container).not("." + $object.data("dF").exclusionClass).each(function(i) {
				var addedFieldSelector = "[name='"+$(this).attr('name')+"']";
				$.fn.dirtyFields.configureField($(addedFieldSelector),$object,"text");
				if(settings.setNewFieldsClean){
					$.fn.dirtyFields.setStartingTextValue($(addedFieldSelector),$object);
				}
			});
			
			$("select",$container).not("." + $object.data("dF").exclusionClass).each(function(j) {
				var addedFieldSelector = "[name='"+$(this).attr('name')+"']";
				$.fn.dirtyFields.configureField($(addedFieldSelector),$object,"select");
				if(settings.setNewFieldsClean){
					$.fn.dirtyFields.setStartingSelectValue($(addedFieldSelector),$object);
				}
			});	
			
			$(":checkbox,:radio",$container).not("." + $object.data("dF").exclusionClass).each(function(k) {
				var addedFieldSelector = "[name='"+$(this).attr('name')+"']";
				$.fn.dirtyFields.configureField($(addedFieldSelector),$object,"checkRadio");	
				if(settings.setNewFieldsClean){
					$.fn.dirtyFields.setStartingCheckboxRadioValue($(addedFieldSelector),$object);
				}
			});		
	}
	//function to set values as clean in a specific part of form ($fieldsObject can be an object or html)
	var setFieldsClean = function($fieldsObject){
			$("input[type='text'],input[type='file'],input[type='password'],textarea",$fieldsObject).each(function(i) {
				var removedFieldSelector = "[name='"+$(this).attr('name')+"']"; 
				$.fn.dirtyFields.setStartingTextValue($(removedFieldSelector),$object);
			});
			
			$("select",$fieldsObject).each(function(j) {
				var removedFieldSelector = "[name='"+$(this).attr('name')+"']";
				$.fn.dirtyFields.setStartingSelectValue($(removedFieldSelector),$object);
			});	
			
			$(":checkbox,:radio",$fieldsObject).each(function(k) {
				var removedFieldSelector = "[name='"+$(this).attr('name')+"']"; 
				$.fn.dirtyFields.setStartingCheckboxRadioValue($(removedFieldSelector),$object);
			});	
	}
	// dirty check on keyup method
	var checkDirtyOnKeyUp = function(){
		$object.on("keyup","input,textarea",function( event ) { 
			$(this).trigger('change');
		});
	}
	// discard change rollback method
	var discardChanges = function(){ 
		var initialHtml = $object.html();
                $object.off('click',settings.discardChangeSelector);
		$object.on('click',settings.discardChangeSelector,function() {
			$.fn.dirtyFields.rollbackForm($object);
			$object.find(settings.saveChangeSelector).attr('disabled', 'disabled');
			$object.find(settings.discardChangeSelector).attr('disabled', 'disabled');
                        if(settings.setInitialHtml){ $object.html(initialHtml); } // update sort order method call			
                        settings.discardChangesCallback.call($object);
			initDirtyFileds();
			if(settings.enableUpdateSortOrder){ updateSortOrder(); } // update sort order method call
		});
	}
	// drag drop method
	var handleDragAndDrop = function() {
		$(settings.dragDropContainerSelector).each(function() {
			$(this).sortable({
				droppable: true,
				connectWith: $(this).children('.sortables'),
				items: $(this).children('.sortables'),
				opacity: 0.8,
				forcePlaceholderSize: true,
				tolerance: "fit",
				handle: '.handle',
				placeholder: 'placeholder',
				start: function(event, ui) {
					ui.item.addClass("fg-drag-line-border");
					ui.item.startPos = ui.item.index();
				},
				stop: function(event, ui) {
					ui.item.removeClass("fg-drag-line-border");
					ui.item.stopPos = ui.item.index();
					updateSortOrder(settings.sortOrderSelector);
					$.fn.dirtyFields.updateFormState($object);
				}
			});
		});
	}
	// update sort order method
	var updateSortOrder = function() {
		$object.find(settings.sortOrderSelector).each(function(i) {
			$(this).val(i+1);
		})
	}
	return {
		// initialize the fg_dirtyfields plugin ( formID is the id of the form, options is the initial configuration array)
		init: function(formID, options) { 
				$object = $("#"+formID);
				initSettings(options); 
				initDirtyFileds();
		},
		// method to call when new fields are added dynamically ( $container can be the added html or added html object
		addFields:function($container){ 
				bindFields($container);
				if(settings.enableDragDrop){ handleDragAndDrop(); } // drag drop method call
				if(settings.enableUpdateSortOrder){ updateSortOrder(); } // update sort order method call
				$.fn.dirtyFields.updateFormState($object);
		},
		// method to call when some fields are removed dynamically ( $removedObject can be the added html or added html object
		removeFields:function($removedObject){
				setFieldsClean($removedObject); 
				settings.removeFieldsCallback.call($object, $removedObject);
				if(settings.enableDragDrop){ handleDragAndDrop(); } // drag drop method call
				if(settings.enableUpdateSortOrder){ updateSortOrder(); } // update sort order method call
				$.fn.dirtyFields.updateFormState($object);
		},
		// this method can be used to check the dirty field checking anytime
		updateFormState:function(){
			$.fn.dirtyFields.updateFormState($object);
			if ($object.hasClass(dirtyFieldsSettings.dirtyFormClass)) {
				$object.find(settings.saveChangeSelector).removeAttr('disabled');
				$object.find(settings.discardChangeSelector).removeAttr('disabled');
			}else{
				$object.find(settings.saveChangeSelector).attr('disabled', 'disabled');
				$object.find(settings.discardChangeSelector).attr('disabled', 'disabled');
			}
		},
                // method to remove all instances of the dirty field, dirty option, and dirty form CSS classes from the specified container object
                removeAllDirtyInstances:function(){
                        $.fn.dirtyFields.markContainerFieldsClean($object);
                },
                
                // method to disable save and discard buttons directly
                disableSaveDiscardButtons: function(){
                        $object.find(settings.saveChangeSelector).attr('disabled', 'disabled');
                        $object.find(settings.discardChangeSelector).attr('disabled', 'disabled');
                },
                // method to enable save and discard buttons directly
                enableSaveDiscardButtons: function(){
                        $object.find(settings.saveChangeSelector).removeAttr('disabled');
                        $object.find(settings.discardChangeSelector).removeAttr('disabled');
                }
	};
}();
													
							