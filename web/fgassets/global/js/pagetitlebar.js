/*
 ================================================================================================ 
 * Custom Plugin for show / hidden items in Page title bar and dynamic action menu  
 * Function - FgPageTitlebar - to configure items to display or not 
 * Function - FgProcessActionMenu - to make dynamic action menu
 * Author : Sebin
 ================================================================================================ 
 */

(function ( $ ) {
    /*
     ================================================================================================ 
     *
     *   FgPageTitlebar Used to configure the items in page title bar
     *         
     ================================================================================================ 
     */

    $.fn.FgPageTitlebar = function(options) {        
        /*
         ================================================================================================ 
         *   Default settings for action menu bar    
         ================================================================================================ 
         */
        var settings = $.extend({
            actionMenu      : false,
            title           : false,
            editTitle       : false,            
            editTitleInline : false,
            search          : false,
            searchBox       : true,
            counter         : false,
            filter          : false,
            tab             : false,
            tabType         : '', //   client = Client side tab, server = server side tab, clientNoError = clientside with no error
            colSetting      : true,
            row1            : true,
            row2            : false,
            link            : false,
            calendarSwitch  : false,
            articleSwitch   : false,
            pagetitleSwitch : false,
            searchFilter    : false,
            languageSwitch  : false,
            export          : false,
            thumbView       : false,            
            galleryScope    : false,
            upload          : false,
            galleryMode     : false,
            selectAll       : false,
            delete          : false,
            themePreview    : false,
            isCalSetmoreTab : true, // function for calling setmore tab on shown tab
            editForm        : false
        }, options );

        /*
         ================================================================================================ 
         *  Function to set property to items    
         ================================================================================================ 
         */
        
        
        var doAction = function (prop, target) {
            if (prop) {
                $('.' + target).removeClass('fg-dis-none').addClass('fg-active-IB');

                if (target === 'fg-page-title-block-2') {
                    //FgProcessActionMenu();
                    if (settings.row1) {
                        $('.fg-page-title-block-2').addClass('has-parent');
                    } else {
                        $('.fg-page-title-block-2').removeClass('has-parent').addClass('child-only');
                    }
                }
                if (target === 'fg-page-title-block-1') {
                    //FgProcessActionMenu();
                    if (settings.row2) {
                        $('.fg-page-title-block-2').addClass('has-parent');
                    }
                }

            } else {
                $('.' + target).removeClass('fg-active-IB').addClass('fg-dis-none');
            }
        };



        /*
         ================================================================================================ 
         *  check each items property and set display setting
         ================================================================================================ 
         */

        doAction(settings.title, 'fg-action-title');
        doAction(settings.editTitle, 'fg-action-editTitle');       
        doAction(settings.editTitleInline, 'fg-action-title-inline-edit');        
        doAction(settings.search, 'fg-action-search');      
        doAction(settings.searchBox, 'fg-action-search-box');
        doAction(settings.actionMenu, 'fg-action-menu');
        doAction(settings.tab, 'fg-action-tab');
        doAction(settings.filter, 'fg-action-filter');
        doAction(settings.counter, 'fg-action-counter');
        doAction(settings.colSetting, 'fg-col-settings');
        doAction(settings.calendarSwitch, 'fg-action-calendar-switch');
        doAction(settings.languageSwitch, 'fg-action-language-switch');
        doAction(settings.galleryScope, 'fg-action-gallery-scope');
        doAction(settings.galleryMode, 'fg-action-gallery-mode');
        doAction(settings.selectAll, 'fg-action-select-all');
        doAction(settings.export, 'fg-action-export');
        doAction(settings.thumbView, 'fg-action-thumb');
        doAction(settings.upload, 'fg-action-upload');
        doAction(settings.link, 'fg-page-title-link');
        doAction(settings.row1, 'fg-page-title-block-1');
        doAction(settings.row2, 'fg-page-title-block-2');
        doAction(settings.searchFilter, 'fg-action-search-filter');
        doAction(settings.articleSwitch, 'fg-action-article-switch'); 
        doAction(settings.pagetitleSwitch, 'fg-action-pagetitle-switch');  
        doAction(settings.delete, 'fg-action-delete');   
        doAction(settings.preview, 'fg-action-preview');        
        doAction(settings.editForm, 'fg-action-editForm');
        

        initilise;

        setMoreTab();         // *  Function call -  customizing More tab menu 
        setOtheritems();
        
        
    /*
     ================================================================================================ 
     *  Function to set other items positions
     ================================================================================================ 
     */
    
        function setOtheritems() {
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
            if(settings.editForm == true && settings.searchBox == false){
               $('.fg-action-search').addClass('col-settings-only');
            }else{
                $('.fg-action-search').removeClass('col-settings-only');
            }


        };

        /*
         ================================================================================================ 
         *  Function to generate dynamic more tab
         ================================================================================================ 
         */

        function setMoreTab() {
            if (settings.tab) {
                $('.fg-action-menu-wrapper').addClass('fg-has-tab'); //border for page title bar// active only in tab section 
                var totalWidth = $('.fg-action-menu-wrapper').width() - 20;
                var actMenuWidth = $('.fg-action-menu').outerWidth();
                var titleWidth = $('.fg-action-title').outerWidth();
                var editTitleInline = $('.fg-action-title-inline-edit').outerWidth();
                var searchWidth = $('.fg-action-search').outerWidth();
                var langSwitch = $('.fg-action-language-switch').outerWidth();
                var editTitle = $('.fg-action-editTitle').outerWidth();
                var articleSwitch = $('.fg-action-article-switch').outerWidth();      
                var deleteSwitch = $('.fg-action-delete').outerWidth(); 
                var previewSwitch = $('.fg-action-preview').outerWidth(); 
                var themePreview  = $('.fg-action-theme-preview').outerWidth();
                var pageTitlePreview  = $('.fg-action-pagetitle-switch').outerWidth(); 
                var editTitleInline = (settings.editTitleInline)?editTitleInline:0;
                var editTitleWidth = (settings.editTitle)?editTitle:0;
                var articleSwitchWidth = (settings.articleSwitch)?articleSwitch:0;                
                var langSwitchWidth = (settings.languageSwitch)?langSwitch:0;
                var deleteSwitch = (settings.delete)?deleteSwitch:0;
                var previewSwitchWidth = (settings.preview)?previewSwitch:0;
                var rightSideItemsWidth = searchWidth + editTitleWidth + articleSwitchWidth + langSwitchWidth + deleteSwitch + previewSwitchWidth + themePreview + pageTitlePreview;
                titleWidth = titleWidth + editTitleInline;
                var remaingSpaceCase1 = totalWidth - (actMenuWidth + titleWidth + rightSideItemsWidth);
                var remaingSpaceCase2 = totalWidth - (actMenuWidth + titleWidth);
                var remaingSpaceCase3 = totalWidth - rightSideItemsWidth;
                //  condition for checking availalble space between blocks
                if ($(window).width() > 480) {
                    if (remaingSpaceCase1 > 250) {

                        $('head').append('<style>.fg-tab-block:before{width:' + (remaingSpaceCase2 - 150) + 'px !important;}</style>');
                        $('.fg-action-tab,.fg-search-last-block').removeClass('fg-PR').attr('style', '');
                        $('.fg-action-tab').removeClass('FR  fg-tab-only').width(remaingSpaceCase1 - 70);
                        $('.fg-action-menu-wrapper').removeClass('fg-eq-height');
                       // console.log('aaa');

                    } else if (remaingSpaceCase2 > 250) {

                        $('head').append('<style>.fg-tab-block:before{width:0px !important;}</style>');
                        $('.fg-action-tab,.fg-search-last-block').removeClass('fg-PR').attr('style', '');
                        $('.fg-action-tab').addClass('FR').removeClass('fg-tab-only').width(remaingSpaceCase2 - 45);
                        $('.fg-action-menu-wrapper').addClass('fg-eq-height');
                        //console.log('bbb');

                    } else if (remaingSpaceCase3 > 250) {

                        $('.fg-action-tab ,.fg-search-last-block').css({'margin': '10px 0 0 0'});
                        $('head').append('<style>.fg-tab-block:before{width:0px !important;}</style>');
                        $('.fg-action-tab').removeClass('FR fg-PR fg-tab-only').width(remaingSpaceCase3 - 50);
                        $('.fg-action-menu-wrapper').addClass('fg-eq-height');
                         //console.log('cccc');

                    } else {

                        $('head').append('<style>.fg-tab-block:before{width:0px !important;}</style>');
                        $('.fg-action-tab').addClass('fg-PR fg-tab-only');
                        $('.fg-action-tab ,.fg-search-last-block').css({'margin': '10px 0 0 0'});
                        $('.fg-action-tab').removeClass('FR').width('100%');
                        $('.fg-action-menu-wrapper').addClass('fg-eq-height');

                       // console.log('ddd');
                    }

                } else {

                    // console.log('ffff');
                    $('head').append('<style>.fg-tab-block:before{width:0px !important;}</style>');
                    $('.fg-action-tab').css({'margin': '10px 0'});
                    $('.fg-action-tab').addClass('fg-PR fg-tab-only')
                    $('.fg-action-tab').width('100%');

                }

                // condition to check what type of more tab   
                if (settings.tabType === 'client') {
                    FgMoreMenu.initClientSide('paneltab');
                } else if (settings.tabType === 'server') {
                    FgMoreMenu.initServerSide('paneltab');
                    $('#paneltab li a[data-toggle="tab"]').removeAttr("data-toggle");
                } else if (settings.tabType === 'clientNoError') {
                    FgMoreMenu.initClientSideWithNoError('paneltab');

                }

            }

        }
        
        /*
         ================================================================================================ 
         *  Function to set Dynamic Action Menu
         *  Currently not in use - do not delete
         ================================================================================================ 
         */

        function FgProcessActionMenu() {
            var menuContent = [];

        }
        ;

        /*
         ================================================================================================ 
         *  resize function to change functionality on resize screen
         ================================================================================================ 
         */
        
        
        $(window).resize(function () {
            setMoreTab();         // *  Function call -  customizing More tab menu 
        });



        $(window).load(function () {
            // *  Function call -  reset width of  More tab menu underline
            setTimeout(function () {
                setMoreTab();
            }, 1000);
            if(settings.isCalSetmoreTab){
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) { // *  Function call -  reset width of  More tab menu after click moretab libk
                    setTimeout(function () {
                        setMoreTab();
                    }, 1000);
                });               
            }

            /*
             ================================================================================================ 
             *  Function for fixing height issue in sidebar bottom option lists
             ================================================================================================ 
             */

            $('body').on('click', 'li.sidebar_options_li .settingsright .btn', function () {
                if ($('.page-container .page-sidebar-wrapper .page-sidebar').hasClass('openSettings')) {
                    $('.page-container .page-sidebar-wrapper .page-sidebar').removeClass('openSettings');
                    $('#chkitems').addClass('noContent');
                } else {
                    $('.page-container .page-sidebar-wrapper .page-sidebar').addClass('openSettings');
                    $('#chkitems').removeClass('noContent');

                }

            });



        });

        return {
            setMoreTab: function () {
                setMoreTab();
            }
        };
    };
    var initilise = {
        return: {
            init: function () {
        }
        }

    };

}(jQuery));

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