/*
 ================================================================================================ 
 * Tooltip wrapper function 
 * Function - FgTooltip - is wrapper function for improved version of bootstrap tooltip
 * FgTooltipTextArray - array that contains global tooltip of specific classes
 ================================================================================================ 

*/
//Tooltip array format 
/*
 * 
FgTooltipTextArray = {
    'btn-secondary' : "Button secondary",
    'fg-delete1' : "delete 1",
    'fg-delete-2' :"delete 2"
};
 * 
 * 
 */

 


FgTooltip = function () {
    var settings;
    var $object;
    var defaultSettings = {
        target : '', //default target is element that have data-toggle="tooltip"
        initCompleteCallback: function ($object) { }
    };
    var initSettings = function (options) {
        settings = $.extend(true, {}, defaultSettings, options);
        
        var selector = (settings.target != '')?settings.target:'[data-toggle="tooltip"], [data-rel="tooltip"]';
        var newPosition = 'top';
        var popOptions ={
            container: 'body',
            delay: { show: 500},
            template:'<div class="tooltip fg-custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            placement :  function (context, source) {
                            var winWidth = $(window).width();
                            var winHeight = $(window).height();
                            var position = $(source).offset();
                            var orginalPos = $(source).attr('data-placement');
                            if(orginalPos == 'left'){
                                newPosition = 'left';
                                if(position.left< 100){
                                    newPosition = 'right';                                
                                }
                                    
                            }
                            else if(orginalPos == 'right'){
                                newPosition = 'right';
                                if(winWidth - position.left - $(source).width() < 100){
                                    newPosition = 'left';                                    
                                }
                            }
                            else if(orginalPos == 'top'){
                                newPosition = 'top';
                                if(position.top < 100){
                                    newPosition = 'bottom';                                    
                                }
                            }
                            else if(orginalPos == 'bottom'){
                                newPosition = 'bottom';
                                if(winHeight - position.top < 100){
                                    newPosition = 'top';                                    
                                }
                            }
                            return newPosition;
                        },
            title : function () {
                        var clas = $(this).attr('class');
                        clas = $.trim(clas).split(' ')
                        var newTitle = '';
                        if (typeof FgTooltipTextArray !== "undefined") {
                            for ( var i = 0, l = clas.length; i < l; i++ ) {
                                var current = clas[i];
                               if(FgTooltipTextArray.hasOwnProperty(current) ){
                                   newTitle = FgTooltipTextArray[current];
                                   break;
                               }                           
                            }
                        }
                        if($(this).attr('data-tooltip') != undefined && $(this).attr('data-tooltip') != 'undefined' &&  $(this).attr('data-tooltip') != ''){
                            newTitle = $(this).attr('data-tooltip');
                        }
                        
                        return newTitle;
                    }
        }
        $(selector).tooltip(popOptions);
        
        $(selector).on('show.bs.tooltip', function () {
            //remove the tooltips that have been added already to the dom
            $('.tooltip').remove();
            $('body').css('overflow-x','hidden');
        })
        $(selector).on('shown.bs.tooltip', function () {
            //correcting tooltip position when tooltip on the rightmost side and have long text
            var windowWidth =  $('body').outerWidth(true);
            var tooltipwidth =  $('.fg-custom-tooltip').outerWidth(true);
            var tooltipLeft = parseInt($('.fg-custom-tooltip').css('left'));           
            var remainingLeft = windowWidth - tooltipwidth - tooltipLeft;  
            //adjusting rightmost tooltips from touching the border
            if(remainingLeft <= 0) {
                correctedLeft = tooltipLeft + remainingLeft - 5;                    
                $('.fg-custom-tooltip').css('left',correctedLeft+'px');
                var tooltipArrowLeft = parseInt($('.fg-custom-tooltip .tooltip-arrow').css('left'));
                var tooltipArrowCorrectLeft = tooltipArrowLeft - remainingLeft + 5;
                $('.fg-custom-tooltip .tooltip-arrow').css('left',tooltipArrowCorrectLeft+'px');                
            }  
            
            //adjusting leftmost tooltips from touching the border  
            if(tooltipLeft <= 0) {
                $('.fg-custom-tooltip').css('left','5px'); 
                var tooltipArrowLeft = parseInt($('.fg-custom-tooltip .tooltip-arrow').css('left'));
                var tooltipArrowCorrectLeft = tooltipArrowLeft - 5;
                $('.fg-custom-tooltip .tooltip-arrow').css('left',tooltipArrowCorrectLeft+'px');   
            }  
            $('body').css('overflow-x',''); 
        })
    };

    return {
        init: function (options) {
            initSettings(options);
        }
    };
}();