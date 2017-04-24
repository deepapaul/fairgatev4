/** Javascript to handle area colors functionality **/
$(function(){
   // addHeight();
    /** Area to init mini colors colorpicker **/
    initMiniColors('.mini-color');
    
    /** Area to init dirty fields **/
    FgDirtyFields.init('area-colors', {discardChangesCallback: function () {
            initMiniColors('.mini-color');
    }});
   
    /** Area to handle page title bar **/
    $(".fg-action-menu-wrapper").FgPageTitlebar({
        title: true
    });
  
    /** Area to handle nestable expand and close button **/
    $('.fg-nestable').nestable({
        expandBtnHTML: '<button data-action="expand" type="button" class="fg-collapse-icon">Expand</button>',
        collapseBtnHTML: '<button data-action="collapse" type="button" class="fg-collapse-icon">Collapse</button>'
    });
    
    /** Function to handle page content height in each nestable **/
    $(document).on('click', '.fg-collapse-icon', function () {
        Layout.fixContentHeight();
    });
    
    /** Function to handle remove color link click **/
    $(document).off('click', '.fg-remove-ind-color');
    $(document).on('click', '.fg-remove-ind-color', function () {
        var id = $(this).attr('data-id');
        var parentId = $(this).parent().parent().attr('data-type-id');
        $('#' + 'remove_' + parentId).addClass('hide');
        $('#' + 'select_' + id).removeClass('hide');
        $('#color_' + id).attr('value', '');
        FgDirtyFields.updateFormState();
    });
  
   
   
    /** Function to save the color code values **/
    $(document).off('click', '#save_changes');
    $(document).on('click', '#save_changes', function () {
        var objectGraph = {};
        //parse the all form field value as json array and assign that value to the array
        objectGraph = FgInternalParseFormField.fieldParse();
        var colorArr = JSON.stringify(objectGraph);
        FgXmlHttp.post(savePath, {'colorArr': colorArr}, false, '');
        return false;
    });

   
    
    /** Function to handle choose color link click **/
    $(document).off('click', '.fg-choose-ind-color');
    $(document).on('click', '.fg-choose-ind-color', function () {
        var id = $(this).attr('data-id');
        var parentId = $(this).parent().parent().attr('data-type-id');
        $('#' + 'select_' + parentId).addClass('hide');
        $('#' + 'remove_' + id).removeClass('hide');
        $('#color_' + id).attr('value', '');
        $('#color_' + id).attr('value', $('#color_' + id).attr('data-value'));
        $('#color_' + id).minicolors('destroy');
        initMiniColors($('#color_' + id));
        FgDirtyFields.updateFormState();
    });
 

    /** Function to init mini colors color picker **/
    function initMiniColors(selector) {
        $(selector).minicolors({
            position: $(this).attr('data-position') || 'bottom left',
            control: $(this).attr('data-control') || 'wheel',
            change: function (value, opacity) {
                if (!value)
                    return;
                if (opacity)
                    value += ', ' + opacity;
            },
            theme: 'bootstrap'
        });
    }
//  function addHeight(){
//     var screenHeight = $(document).outerHeight(true);
//     
//      $( ".page-content" ).css( "min-height",screenHeight-400);
//  }
   
});


