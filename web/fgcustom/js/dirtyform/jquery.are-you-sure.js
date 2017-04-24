/*!
 * jQuery Plugin: Are-You-Sure (Dirty Form Detection)
 * https://github.com/codedance/jquery.AreYouSure/
 *
 * Copyright (c) 2012-2014, Chris Dance and PaperCut Software http://www.papercut.com/
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * Author:  chris.dance@papercut.com
 * Version: 1.6.0
 * Date:    7th Feb 2014
 */
(function($) {
  
  $.fn.areYouSure = function(options) {
      
    var settings = $.extend(
      {
        'message' : 'You have unsaved changes!',
        'dirtyClass' : 'dirty',
        'change' : null,
        'silent' : false,
        'addRemoveFieldsMarksDirty' : false,
        'fieldSelector': "select,textarea,input[type='text'],input[type='password'],input[type='checkbox'],input[type='radio'],input[type='hidden'],input[type='color'],input[type='date'],input[type='datetime'],input[type='datetime-local'],input[type='email'],input[type='month'],input[type='number'],input[type='range'],input[type='search'],input[type='tel'],input[type='time'],input[type='url'],input[type='week']"
      }, options);

    var getValue = function($field) {
      if ($field.hasClass('ays-ignore')
          || $field.hasClass('aysIgnore')
          || $field.attr('data-ays-ignore')
          || $field.attr('name') === undefined) {
        return null;
      }

      if ($field.is(':disabled')) {
        return 'ays-disabled';
      }

      var val;
      var type = $field.attr('type');
      if ($field.is('select')) {
        type = 'select';
      }

      switch (type) {
        case 'checkbox':
        case 'radio':
          val = $field.is(':checked');
          break;
        case 'select':
          val = '';
          $field.find('option').each(function(o) {
            var $option = $(this);
            if ($option.is(':selected')) {
              val += $option.val();
            }
          });
          break;
        default:
          val = $field.val();
      }

      return val;
    };

    var storeOrigValue = function($field) {
      $field.data('ays-orig', getValue($field));
    };

    var checkForm = function(evt) {
          //code done by Shyam Gopal C S
//            var hiddenid = $('.sorthidden').map(function() {
//                return "#" + $(this).attr('id');
//            }).get();
          //End of code
      var isFieldDirty = function($field) {
         
          
 
   
          
          
          
        var origValue = $field.data('ays-orig');
        if (undefined === origValue) {
          return false;
        }
        if(getValue($field) != origValue){
           
//            $('#' + $field.attr('id')).attr('style', 'border: 2px solid red;'); // commented for required multiselct 
			$('#' + $field.attr('id')).addClass('fairgatedirty');
             //code done by Shyam Gopal C S
//             var dirtyclassid='#' + $field.attr('id');
//             var curridpos = hiddenid.indexOf(dirtyclassid)+1;
//             
//             if(hiddenid.indexOf(dirtyclassid) >=0)
//                 {
//             for(var i=curridpos;i<=hiddenid.length;i++)
//                 {
//                  $(hiddenid[i]).attr('style', 'border: 2px solid red;');
//			$(hiddenid[i]).addClass('fairgatedirty');   
//                 }
//             
//                 }
             //End of code
        } else {
            //$('#' + $field.attr('id')).attr('style', '');
			$('#' + $field.attr('id')).removeClass('fairgatedirty');
        }
        return (getValue($field) != origValue);
      };

      var $form = ($(this).is('form')) 
                    ? $(this)
                    : $(this).parents('form');

      // Test on the target first as it's the most likely to be dirty
      if (isFieldDirty($(evt.target))) {
        setDirtyStatus($form, true);
        return;
      }

      $fields = $form.find(settings.fieldSelector);

      // Brute force - check each field
      var isDirty = false;
      $fields.each(function() {
        $field = $(this);
        if (isFieldDirty($field)) {
          isDirty = true;
          //return false; // break  //commented since dirty class is added for only one element
        }
      });

      if (settings.addRemoveFieldsMarksDirty) {              
        // Check if field count has changed
        var origCount = $form.data("ays-orig-field-count");
        if (origCount != $fields.length) {
          setDirtyStatus($form, true);
          return;
        }
      }
      
      setDirtyStatus($form, isDirty);
    };

    var initForm = function($form) {
      var fields = $form.find(settings.fieldSelector);
      $(fields).each(function() { storeOrigValue($(this)); });
      $(fields).unbind('change keyup', checkForm);
      $(fields).bind('change keyup', checkForm);
      $form.data("ays-orig-field-count", $(fields).length);
      setDirtyStatus($form, false);
    };

    var setDirtyStatus = function($form, isDirty) {
      var changed = isDirty != $form.hasClass(settings.dirtyClass);
      $form.toggleClass(settings.dirtyClass, isDirty);
        
      // Fire change event if required
      if (changed) {
        if (settings.change) settings.change.call($form, $form);

        if (isDirty) $form.trigger('dirty.areYouSure', [$form]);
        if (!isDirty) $form.trigger('clean.areYouSure', [$form]);
        $form.trigger('change.areYouSure', [$form]);
      }
    };

    var rescan = function() {
  
      var $form = $(this);
      var fields = $form.find(settings.fieldSelector);
      $(fields).each(function() {
        var $field = $(this);
        if (!$field.data('ays-orig')) {
          storeOrigValue($field);
          $field.bind('change keyup', checkForm);
        }
      });
      // Check for changes while we're here
      $form.trigger('checkform.areYouSure');
    };

    var reinitialize = function() {
      initForm($(this));
    }

    if (!settings.silent) {
      $(window).bind('beforeunload', function() {
        $dirtyForms = $("form").filter('.' + settings.dirtyClass);
        if ($dirtyForms.length > 0) {
          // $dirtyForms.removeClass(settings.dirtyClass); // Prevent multiple calls?
          return settings.message;
        }
      });
    }

    return this.each(function(elem) {
    
      if (!$(this).is('form')) {
        return;
      }
      var $form = $(this);
        
      $form.submit(function() {
        $form.removeClass(settings.dirtyClass);
      });
      $form.bind('reset', function() { setDirtyStatus($form, false);});
      // Add a custom events
	  $form.bind('rescan.areYouSure', rescan);
      $form.bind('reinitialize.areYouSure', reinitialize);
      $form.bind('checkform.areYouSure', checkForm);
	  initForm($form);
    });
  };
})(jQuery);