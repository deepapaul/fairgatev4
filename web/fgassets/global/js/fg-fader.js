/*
 ================================================================================================ 
 * Custom Plugin for fader 
 * Function - FgFader - to configure plugin
 * resize slider call function  [[[[  $('.fg-fader').FgFader().resizeSlider();  ]]]
 ================================================================================================ 
 */
(function ( $ ) {
    /*
     ================================================================================================ 
     *
     *   FgFader Used to configure the items in slider
     *         
     ================================================================================================ 
     */
    var sliderIntervals = {};

    $.fn.FgFader = function(options) {    
        /*
         ================================================================================================ 
         *   Default settings for Fadder plugin
         ================================================================================================ 
         */
        var settings = $.extend({
            type            : 'fade', //fader / slider
            duration        : '3000', //slider interval
            breakpoints        : ['1200','992','800','768','480','360','320'] //slider interval
        }, options );
        var _this = $(this);
        var durationInterval = [];  
        initSlider(_this);
        
        
        function initSlider(slider){
                
                var sliderDuration = settings.duration; 
                /*
                 ================================================================================================ 
                 *  Iterating each calls/sliders 
                 ================================================================================================ 
                 */
                slider.each(function(i){
                    var imgHeight = 10;
                    var currentImgHeight = 0;
                    var currentImgId = $(this).attr('id');
                    var currentDuration = $(this).attr('interval');
                    sliderDuration = (currentDuration > 0) ? currentDuration*1000 : sliderDuration; // check the interval duration already set in dom
                    var sliderContainer = $(this).children('.slider');
                    $('#'+currentImgId+' > div').show();                   
                    
                    if(sliderIntervals[currentImgId] == undefined){
                        sliderIntervals[currentImgId] = [];
                    }else{
                        $.each(sliderIntervals[currentImgId],function(i,val){
                            window.clearInterval(val);
                         // sliderIntervals[currentImgId].shift();
                            sliderIntervals[currentImgId].splice(i, 1);
                        });
                    }
                        
                    
                    
                    sliderContainer.each(function(i){
                        currentImgHeight = $(this).find('.slide').outerHeight();
                        imgHeight = ( currentImgHeight > imgHeight) ? currentImgHeight : imgHeight;
                        $(this).height(currentImgHeight);
                    }).promise().done(function () { 
                        $('#'+currentImgId).removeClass('fg-visible-hidden').css({'height' :imgHeight});
                        $('#'+currentImgId+' > div:not(:first-child)').hide();
                        $('#'+currentImgId+' > div:first-child').show();
                        
                    });
                    
                    /*$(this).removeClass('fg-visible-hidden').animate({'height' :imgHeight},300);
                    //console.log($('#'+currentImgId+' > div:not(:first-child)'));
                    $('#'+currentImgId+' > div:not(:first-child)').hide();*/
                    
                    /*
                     ================================================================================================ 
                     *   start slider    
                     ================================================================================================ 
                     */
                    durationInterval[i] = setInterval(function() { 
                        $('#'+currentImgId+' > div:first')
                            .fadeOut(sliderDuration/4)
                            .next()
                            .fadeIn(sliderDuration/2)
                            .end()
                            .appendTo('#'+currentImgId);
                    },  sliderDuration);
                    //setSliderRange(currentImgId);
                    //var newIntervalId = durationInterval[i];
                    $(this).attr('data-intervalId',durationInterval[i]);  
                    sliderIntervals[currentImgId].push(durationInterval[i]);  // pushing interval id to array for clear all interval before starting new
                });
                
                
        }
        /*
        ================================================================================================ 
        *   Add class to slider for identify currently running viewport width. 
        *   If goes to next break point slider re inits its height calculation
        ================================================================================================ 
        */
        function setSliderRange(sliderId){  
            var winWidth = $(window).width();
           // var breakPoints = str.split(",");
            $.each(settings.breakpoints,function(i,val){
                //val = parseInt(val);
                if(winWidth <= val){
                    $('#'+sliderId).attr('data-slider-range',val);
                   // return false;
                }else{
                    
                }
            });
            
            
        };
        /*
        ================================================================================================ 
        *   Function for change height of slider
        ================================================================================================ 
        */
        function resizeSlider(){  
            initSlider(_this);            
        };
        
        /*
        ================================================================================================ 
        *   Function for re calculate slider height on resize
        ================================================================================================ 
        */
        
        var doit;
        $(window).resize(function(){
            clearTimeout(doit);
            doit = setTimeout(resizeSlider, 500);
        });
        return {
            resizeSlider: function () {
                resizeSlider();
            },
            destroy : function(){
                destroy();
            }
            
        };
        
        
    };

}(jQuery));