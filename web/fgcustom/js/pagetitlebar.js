/*
 ================================================================================================ 
 * Custom Plugin for show / hidden items in Page title bar and dynamic action menu  
 * Function - FgPageTitlebar - to configure items to display or not 
 * Function - FgProcessActionMenu - to make dynamic action menu
 * Author : Sebin
 ================================================================================================ 
 */

/*
 ================================================================================================ 
 *
 *   FgPageTitlebar Used to configure the items in page title bar
 *         
 ================================================================================================ 
 */
FgPageTitlebar = function () {
    var settings;
    var $object;
    var defaultSettings = {
        actionMenu: false,
        title: false,
        search: false,
        counter: false,
        filter: false,
        tab: false,
        tabType: '', //   client = Client side tab, server = server side tab, clientNoError = clientside with no error
        tabMinwidth: '280', // minimum width reserved for more tab
        tabMenuId: 'paneltab', // tab menu ul id - may it will be different in different page paneltab/data-tabs
        colSetting: true,
        link: false,
        searchFilter: false,
        languageSettings: false,
        languageSwitch :false,
        initCompleteCallback: function ($object) { },
        moreCompleteCallback: function ($object) { },
        tabCompleteCallback: function ($object) { }
    };
    // extends the initial configuration on method init		
    var initSettings = function (options) {
        settings = $.extend(true, {}, defaultSettings, options);
        doAction(settings.title, 'fg-action-title');
        doAction(settings.search, 'fg-action-search');
        doAction(settings.actionMenu, 'fg-action-menu');
        doAction(settings.tab, 'fg-action-tab');
        doAction(settings.filter, 'fg-action-filter');
        doAction(settings.counter, 'fg-action-counter');
        doAction(settings.colSetting, 'fg-col-settings');
        doAction(settings.link, 'fg-page-title-link');
        doAction(settings.searchFilter, 'fg-action-search-filter');
        doAction(settings.languageSettings, 'fg-action-language-set');
        doAction(settings.languageSwitch, 'fg-action-language-switch');
        
        
        settings.initCompleteCallback.call();
      //  setMoretab();        
        setOtheritems();
    }


    /*
     ================================================================================================ 
     *  Callback function for shown moretab  
     ================================================================================================ 
     */


    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) { // *  Function call -  reset width of  More tab menu after click moretab libk
        setTimeout(function () {
            settings.tabCompleteCallback.call();
            setMoretab();
        }, 1000);
    });



    /*
     ================================================================================================ 
     *  Function to set property to items    
     ================================================================================================ 
     */

    var doAction = function (prop, target) {
        if (prop) {
            $('.' + target).removeClass('fg-dis-none').addClass('fg-active-IB');

            // Check current menu is action menu 
            if (target === 'fg-action-menu') {

                //FgProcessActionMenu();
            }

        } else {
            $('.' + target).removeClass('fg-active-IB').addClass('fg-dis-none');
        }
    };


    /*
     ================================================================================================ 
     *  Function to generate dynamic more tab
     ================================================================================================ 
     */

    var setMoretab = function () {
        if (settings != undefined && settings.tab) {
            // var totDivs = $('.fg-action-menu-wrapper > div > .fg-active-IB').length;
            $('.fg-action-menu-wrapper').addClass('fg-has-tab'); //border for page title bar// active only in tab section 
            var totalWidth = $('.fg-action-menu-wrapper').width() - 20;   //20 px is reserved space for page scroll            
            var actMenuWidth = $('.fg-action-menu').width();
            var titleWidth = $('.fg-action-title').width();
            var searchWidth = $('.fg-action-search').width();
            var langSwitchWidth = $('.fg-action-language-switch').width();
            searchWidth= (settings.languageSwitch)?langSwitchWidth:searchWidth;
            //  condition for checking availalble space between blocks
            if ($(window).width() > 550) {  //if screen width larger than portrait
                
                var remaingSpaceCase1 = totalWidth - (actMenuWidth + titleWidth + searchWidth );
                var remaingSpaceCase2 = totalWidth - (actMenuWidth + titleWidth);
                var remaingSpaceCase3 = totalWidth - searchWidth;
                
               // console.log(remaingSpaceCase2 +"::remaingSpaceCase2   "+settings.tabMinwidth);
               
                if (remaingSpaceCase1 > settings.tabMinwidth) { // make everything in same line
                    
                   // console.log('aaa'+totalWidth+":::"+actMenuWidth+":::"+titleWidth+"::::"+searchWidth);
                    
                    $('.fg-action-tab').removeClass('fg-PR FR fg-clear').attr('style', '').width(remaingSpaceCase1 - 70);
                    $('.fg-nav-tab-border').addClass('col-md-height');
                    $('.fg-action-tab .fg-moretab-bottom-line').width(remaingSpaceCase1 - 70);      
                    $('.fg-action-title,.fg-action-menu,.fg-search-last-block,.fg-action-title .page-title').attr('style', '');
                    $('.fg-search-last-block').removeClass('fg-clear').addClass('case1');

                } else if (remaingSpaceCase2 > settings.tabMinwidth) { //make title and more tab in same line  
                    //console.log('bbb');                 
                    
                    $('.fg-action-tab').removeClass('fg-clear').addClass('fg-PR FR').attr('style', '').width(remaingSpaceCase2 - 60);
                    $('.fg-nav-tab-border').removeClass('col-md-height');
                  //  $('.fg-action-tab .fg-moretab-bottom-line').width(remaingSpaceCase2 - 50 - 150);   
                    $('.fg-action-tab .fg-moretab-bottom-line').width(0);   
                    $('.fg-action-title,.fg-action-menu,.fg-search-last-block,.fg-action-title .page-title').attr('style', '');
                    $('.fg-search-last-block').addClass('fg-clear').removeClass('case1');

                } else if (remaingSpaceCase3 > settings.tabMinwidth) { //make searchbox and more tab in same line
                    //console.log('ccc');
                    
                    $('.fg-action-tab').removeClass('fg-PR FR fg-clear').attr('style', '').css({'width':remaingSpaceCase3 - 20,'margin-left':0});
                    $('.fg-action-tab .fg-moretab-bottom-line').width(remaingSpaceCase3 + searchWidth + 30 - 150);       
                    $('.fg-nav-tab-border').addClass('col-md-height');   
                    
                    $('.fg-action-title .page-title').css({'margin-bottom':'10px'});
                    $('.fg-search-last-block').css({'margin-bottom':'2px'});
                    $('.fg-search-last-block').removeClass('case1');

                }else{
                    // console.log('ddd');
                    $('.fg-action-tab').addClass('fg-PR FR fg-clear').attr('style', '').width('100%');
                    $('.fg-action-tab .fg-moretab-bottom-line').width(0);      
                    $('.fg-nav-tab-border').removeClass('col-md-height');      
                    $('.fg-search-last-block').removeClass('fg-clear').css({'margin-top':'10px'});           
                    $('.fg-action-title .page-title').css({'margin-bottom':'10px'});
                    $('.fg-search-last-block').attr('style', '');
                    $('.fg-search-last-block').removeClass('case1');
                    
                }
                
            } else {
                //console.log('eee');
                $('.fg-action-tab').addClass('fg-PR FR fg-clear').attr('style', '').width('100%');
                $('.fg-action-tab .fg-moretab-bottom-line').width(0);      
                $('.fg-nav-tab-border').removeClass('col-md-height');       
                $('.fg-search-last-block').removeClass('case1').addClass('fg-clear').css({'margin-top':'10px'});                    
                $('.fg-action-title .page-title').css({'margin-bottom':'10px'});
                $('.fg-search-last-block').attr('style', '');
            }
                                   
            // condition to check what type of more tab   
            if (settings.tabType === 'client') {
                FgMoreMenuV2.initClientSide(settings.tabMenuId);
            } else if (settings.tabType === 'server') {
                FgMoreMenuV2.initServerSide(settings.tabMenuId);
                $('#'+settings.tabMenuId+' li a[data-toggle="tab"]').removeAttr("data-toggle");

            } else if (settings.tabType === 'clientNoError') {
                FgMoreMenuV2.initClientSideWithNoError(settings.tabMenuId);

            }
            settings.moreCompleteCallback.call();
        }
    };



    /*
     ================================================================================================ 
     *  Function to set other items positions
     ================================================================================================ 
     */
    
    var setOtheritems = function () {
        
        if (settings.actionMenu) {
            $('.fg-action-title').addClass('fg-has-menu');
        } else {
            $('.fg-action-title').removeClass('fg-has-menu');
        }

        if (settings.searchFilter) {
            $('.fg-action-search').addClass('fg-has-filter');
        } else {
            $('.fg-action-search').removeClass('fg-has-filter');
        }

        if (settings.colSetting) {
            $('.fg-action-search').removeClass('no-col-settings');
        } else {
            $('.fg-action-search').addClass('no-col-settings');
        }
        
        
        
    };
    
    
    /*
     ================================================================================================ 
     *  Function to correct the moretab bottom line alignment in sponsor analysis page
     ================================================================================================ 
     */
    
    var makeTabCorrect  = function (target){
        var totWidth = $(target).width();
        var titlewidth = $(target).find('.page-title').width();
        $(target).find('.fg_sm_analysis_nav_tab').css({'margin-left':titlewidth + 42});
    };
    
    
    /*
     ================================================================================================ 
     *  Public functions that can access from anywhere from the project 
     ================================================================================================ 
     */
    return {
        // initialize the page titlebar plugin
        init: function (options) {
            initSettings(options);
        },
        setMoreTab: function () {
            setMoretab();
        },
        makeTabCorrect : function(target){
            makeTabCorrect(target);
        },
        /**
         * Function to show missing traslations error in translation tab 
         * 
         * @param {string} systemLang default correspondence language
         */
        checkMissingTranslation: function (systemLang) {
            var dataKey = '';
            var missingTrans = new Array();
            $('.btlang').removeClass('error');
            $("form input[data-lang]").each(function () {
                var requiredAttr = $(this).attr('required');
                var langAttr = $(this).attr('data-lang');
                var currentDataKey = $(this).attr('data-key');
                
                if (typeof langAttr !== typeof undefined && langAttr !== false) {  
                    if(typeof requiredAttr !== typeof undefined && requiredAttr !== false && langAttr == systemLang) {
                        //data key of required field in system language
                        dataKey =  $(this).attr('data-key');
                    }
                    if(dataKey != '') {
                        //get data-key of required fields in other languages
                        requiredDataKey = dataKey.replace(systemLang, langAttr);
                        if(currentDataKey == requiredDataKey) {
                            var dataVal = $(this).val();
                            if(!dataVal) {
                                //If required fields are empty in other languages, push that language to missingTrans
                                missingTrans.push(langAttr);
                            }
                        } else {
                            dataKey = '';
                        }
                    }           
                }
            });
            missingTrans = _.uniq(missingTrans);
            
            //show error on missing tranlstions
            $.each( missingTrans, function( key, value ) {
                $('#'+value+'.btlang').addClass('error');
            });
        }
    };
    



}();
    



/*
 ================================================================================================ 
 *   FG more menu Version 2
 *   moremenu for page titlebar
 *   following scripts are using to avoid unwanted calls from fg_more-Menu.js
 *   this more menu functionality only available when more menu are within the page titlebar area
 *         
 ================================================================================================ */

var FgMoreMenuV2 = function () {
    //var menuSelector, contentSelector;
    var handleMoreMenu = function (menuSelector) {


        $(menuSelector + ' > li.active').css("max-width", "");
        $(menuSelector + ' li').removeClass("hidden").addClass("show");
        $(menuSelector + ' > li.datahideshow').addClass("hidden").removeClass("show");
        var w = $(menuSelector + ' > li.active').outerWidth(true);
        var mw = $(menuSelector).width()
        if($(menuSelector + ' > li.datahideshow').hasClass('show')){
            var mw = $(menuSelector).width() - $(menuSelector + ' > li.datahideshow').outerWidth(true) - 21;            
        }

        var i = -1;
        var menuhtml = '';
        $(menuSelector + ' > li').each(function (index) {
            //i++;
            var hidePosition = index + 1;
            if (!($(this).hasClass('active') || $(this).hasClass('datahideshow'))) {
                w += $(this).outerWidth(true);
                if (mw < w) {
                    $(this).addClass("hidden").removeClass("show");
                    $(menuSelector + ' .datahideshow').removeClass("hidden").addClass("show");
                    $(menuSelector + ' .datahideshow li:nth-child(' + hidePosition + ')').removeClass("hidden").addClass("show");
                } else {
                    $(this).removeClass("hidden");
                    $(this).addClass("show");
                    $(menuSelector + ' .datahideshow li:nth-child(' + hidePosition + ')').addClass("hidden").removeClass("show");
                }
            }
        });
        if (mw + $(menuSelector + ' > li.datahideshow').outerWidth(true) > w) {
            $(menuSelector + ' > li').removeClass("hidden").addClass("show");
            $(menuSelector + ' > li.datahideshow').addClass("hidden").removeClass("show");
        }
        if (mw < w) {
            //console.log(mw+":::"+w);
            //fg-truncate is using to make one tab always visible even if it has more width than total more tab width

            w = mw;
            $(menuSelector + ' > li.active').removeClass("hidden").addClass("show fg-truncate").css({"max-width": w});
            $(menuSelector + ' .datahideshow ul > li.active').addClass("hidden").removeClass("show");

            var spanWidth = $(menuSelector + ' > li.active.fg-truncate span.badge').outerWidth(true);
            if (spanWidth == null) {
                var spanWidth = $(menuSelector + ' > li.active.fg-truncate span.fg-more-icons-nav').outerWidth(true);
            }
            $(menuSelector + ' li.active span.fg-dev-tab-text').css({'max-width': w - spanWidth - 45});
        } else {
           // console.log('b');
            $(menuSelector + ' li.active span.fg-dev-tab-text').css({'max-width': ''});
            $(menuSelector + ' > li.active').removeClass("hidden fg-truncate").addClass("show").css("max-width", "");
            $(menuSelector + ' .datahideshow ul > li.active').addClass("hidden").removeClass("show");
        }
        $(menuSelector).css('visibility', 'visible');

    }

    var handleMenuError = function (menuSelector, contentSelector) {
        $(contentSelector + ' .has-error').each(function () {
            var attrId = $(this).attr('data-attrId');
            var parentContainer = $(contentSelector + ' div[data-attrid=' + attrId + ']').closest('.tab-pane').attr('data-catid');
            $(menuSelector + '  li#data_li_' + parentContainer).addClass('has-error');
            var tabId = $(contentSelector + ' div[data-attrid=' + attrId + ']').closest('.tab-pane').attr('data-panel-tab');
            if (typeof tabId !== typeof undefined) {
                $(menuSelector + '  li#data_li_' + tabId).addClass('has-error');
            }
        });
        $(menuSelector + ' .datahideshow').removeClass('more-has-error');
    }

    var handleMoreMenuError = function (menuSelector) {
        $(menuSelector + ' li.datahideshow ul li.has-error').each(function () {
            if ($(menuSelector + ' li.datahideshow ul li.has-error').hasClass('show')) {
                $(menuSelector + ' li.datahideshow ul li.has-error').closest('.datahideshow').addClass('more-has-error');
            }
        });
    }
    var handleMoreMenuClick = function (menuSelector, contentSelector) {
        $(menuSelector + ' li a[data-toggle="tab"]').on('shown.bs.tab', function () {
            var clickId = $(this).parent().attr('id');
            var parentMoreTab = $(this).closest('.data-more-tab').attr('id');
            $('#' + parentMoreTab + ' li').removeClass("active");
            $('#' + parentMoreTab + ' #' + clickId).addClass("active");
            handleMenuError(menuSelector, contentSelector);
            handleMoreMenu(menuSelector);
            handleMoreMenuError(menuSelector);
            var type = $(this).parent().data('type');
            FgStickySaveBar.init(type);
        });
    }
    return {
        //main function to initiate the theme
        initServerSide: function (menuId) {
            var menuSelector = '#' + menuId;
                handleMoreMenu(menuSelector);
        },
        initClientSide: function (menuId, containerId) {
            var menuSelector = '#' + menuId;
            var contentSelector = '#' + containerId;
                handleMenuError(menuSelector, contentSelector);
                handleMoreMenu(menuSelector);
                handleMoreMenuError(menuSelector);
                handleMoreMenuClick(menuSelector, contentSelector);
        },
        initClientSideWithNoError: function (menuId, containerId) {
            var menuSelector = '#' + menuId;
            var contentSelector = '#' + containerId;
                handleMoreMenu(menuSelector);
                handleMoreMenuClick(menuSelector, contentSelector);

        }
    };
}();




/*
 ================================================================================================ 
 *  Window resize  function  
 ================================================================================================ 
 */

$(window).resize(function(){
    //setTimeout(function(){
    FgPageTitlebar.setMoreTab();         // *  Function call -  customizing More tab menu 
        
    //},500)
    
    
}); 
$(window).load(function(){
    
    FgPageTitlebar.setMoreTab();         // *  Function call -  customizing More tab menu 
    
}); 

/*
==========================================================================================================================================================================
 *
 *  
 *         
==========================================================================================================================================================================
*/


(function ( $ ) {

}( jQuery ));
/*
 ================================================================================================ 
 *  Language switch missing translations
 ================================================================================================ 
 */
var FgLanguageSwitch = function(){
    return {
        /**
            * Function to show missing traslations error in translation tab 
            * 
            * @param {string} systemLang default correspondence language
            */
           checkMissingTranslation: function (systemLang, formId) {
               var dataKey = '';
               var missingTrans = new Array();
               $('.btlang').removeClass('error');

               if(formId != '' && formId != null){
                var formObj = $('#'+formId);
               } else {
                var formObj = $("form");
               }

               formObj.find('textarea[data-lang],input[data-lang]').each(function () {
                   var requiredAttr = $(this).attr('required');
                   var langAttr = $(this).attr('data-lang');
                   var currentDataKey = $(this).attr('data-key');

                   if (typeof langAttr !== typeof undefined && langAttr !== false) {  
                       if(typeof requiredAttr !== typeof undefined && requiredAttr !== false && langAttr == systemLang) {
                           //data key of required field in system language
                           dataKey =  $(this).attr('data-key');
                       }
                       if(dataKey != '') {
                           //get data-key of required fields in other languages
                           requiredDataKey = dataKey.replace(systemLang, langAttr);
                           if(currentDataKey == requiredDataKey) {
                               var dataVal = $(this).val();
                               if(!dataVal) {
                                   //If required fields are empty in other languages, push that language to missingTrans
                                   missingTrans.push(langAttr);
                               }
                           } else {
                               dataKey = '';
                           }
                       }           
                   }
               });
               missingTrans = _.uniq(missingTrans);

               //show error on missing tranlstions
               $.each( missingTrans, function( key, value ) {
                   $('#'+value+'.btlang').addClass('error');
               });
           }
    }
}();
