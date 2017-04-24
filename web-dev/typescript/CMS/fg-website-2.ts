/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
/**
 * FgWebsiteTheme class
 * 
 */

var FgWebsiteObj;
var _thisFgWebsiteTheme;
class FgWebsiteTheme {   
    constructor() {
        //object new FgWebsite() declared in layout.html
        FgWebsiteObj = fgwebsite;
        _thisFgWebsiteTheme = this; 
    } 
    /**
     * build header template
     */
    public headerNav(path, parentDivId) {
         //handle class for header image - display
        if(_.size(publicConfig.header_options.header_label)){
            var label = publicConfig.header_options.header_label; 
            if(typeof label.TM_DEFAULT_LOGO != 'undefined' && typeof label.TM_MOBILE_SCREEN_LOGO != 'undefined') {                
                var classD = 'fg-web-logo hidden-xs';
                var classM = 'fg-mob-logo visible-xs';
                var classL = '';
            }else if(typeof label.TM_DEFAULT_LOGO != 'undefined' && typeof label.TM_MOBILE_SCREEN_LOGO == 'undefined'){
                var classD = 'fg-web-logo';
                var classM = 'visible-xs';
                var classL = '';
            }else if(typeof label.TM_DEFAULT_LOGO == 'undefined' && typeof label.TM_MOBILE_SCREEN_LOGO != 'undefined'){
                var classD = '';
                var classM = 'fg-mob-logo visible-xs';
                var classL = 'visible-xs'
            } else {
                var classD = '';
                var classM = '';
                var classL = 'hide'
            }
        }
        
        $.ajax({
            type: "GET",
            url: path,
            async: false,
            success: function(jsonData) {
                jsonData.classD = classD;
                jsonData.classM = classM;
                jsonData.classL = classL;
                FgWebsiteObj.activeHeaderMenu = jsonData.menu;
                FgWebsiteObj.navigationMenuPath = jsonData.navPath;
                FgWebsiteObj.contactLang = jsonData.lang;
                FgWebsiteObj.clubLang = jsonData.clubLang;
                var htmlheader = FgWebsiteObj.buildHeaderNavigation(jsonData.data);
                var htmlFinal = FGTemplate.bind(FgWebsiteObj.templateHeaderNav, jsonData);
                $('#' + parentDivId).prepend(htmlFinal);
                $('#fg-web-header-menus').html(htmlheader);
                //more menu integration
                _thisFgWebsiteTheme.setMoreTabMenu(htmlheader);
                _thisFgWebsiteTheme.navigationActions();
                FgWebsiteObj.navigationActivation();
                //to make footer sticky
                //to handle header loading
                setTimeout(function(){
                    _thisFgWebsiteTheme.makeFooterSticky();
                    $(".fg-web-main-content").removeClass('fg-visible-hidden');
                },1000);
                            
                _thisFgWebsiteTheme.makeHeaderSticky();                
            }
        });
    }
    /**
     * Set more tab
     */
    public setMoreTabMenu(htmlMenus) {
        var htmlMoreTab = FGTemplate.bind('template_header_navigation_moremenu');
        $('#fg-web-header-menus').append(htmlMoreTab);
        $('.fg-dev-more-menu').append(htmlMenus);  
        FgMoreMenu.initServerSide('fg-web-header-menus');
    }
    
    public makeHeaderSticky() {
        
        let $navBarContainer : Object = $('.fg-navbar-container');
        let $body : Object = $('body');
        if($(window).width()>991){
            let headerHeight  : number = $('.fg-web-page-header').outerHeight()-20;
            let winHeight     : number = $(window).height();
            
            $( window ).scroll(function() {
                let scrollPosition : number = $(window).scrollTop();
                if(scrollPosition > headerHeight){
                    $body.addClass('fg-header-sticky');
                }else{                    
                    $body.removeClass('fg-header-sticky');
                }
            }
        }else{
            $body.removeClass('fg-header-sticky');
        }
    }

    public onScrollEvent() {
        $(window).scroll(function() {
            if ($(this).scrollTop() > 200) { 
                $('.fg-web-go-top').addClass('show');
                $('.fg-web-go-top').removeClass('hide');
            } else {
                $('.fg-web-go-top').addClass('hide');
                $('.fg-web-go-top').removeClass('show');
            }            
        });
    }
    
    /**
     * make footer sticky
     */
    public makeFooterSticky() {
        let hasAdmin = $('body').hasClass('fg-has-admin-nav'); // checking it page has admin nav
        let hasFixedheader = $('body').hasClass('fg-header-sticky'); // checking page has fixed header
        let headerHeight = $('.fg-web-page-header').outerHeight();
        let contentHeight = $('.fg-web-main-content').outerHeight();
        let footerHeight = $('.fg-web-page-footer').outerHeight();

        let winHeight = $(window).height();
        let winWidth = $(window).width();
        if (winWidth > 767) {
            let totHeight = headerHeight + contentHeight + footerHeight;
            //if (winHeight > totHeight) {
            let remaingHeight = winHeight - headerHeight - footerHeight;
            if (hasAdmin) {
                remaingHeight = remaingHeight - 46;
            }
            remaingHeight = (remaingHeight < 300) ? 300 : remaingHeight;
            $('.fg-web-main-content').css('min-height', remaingHeight);
            // }
        }        
        $('body').removeClass('no-scroll');
    }
    
    /*
     ================================================================================================ 
     *  @set fullscreen height for responsive navbar
     ================================================================================================ 
     */
    public setNavHeight (){
        let $headerHeight  : number = $('.fg-web-page-header').outerHeight();
        let $winHeight     : number = $(window).height();
        let $winWidth      : number = $(window).width();
        let $remaingHeight : number = $winHeight - $headerHeight;
        if($winWidth < 992){

            $('.fg-web-main-nav').css({
                'height': $remaingHeight
            })
        } else{

            $('.fg-web-main-nav').css({
                'height': ''
            })
        }
    }
    
    /**
     * build additional navigation
     */
     public callAdditionalNav(templateId){
         var htmlNav =FGTemplate.bind('template_top_lang_navigation','');
           $('#' + templateId).prepend(htmlNav);
         
          $.ajax({
            type: "GET",
            url: additionalmenuPath,
            async: false,
            success: function(additionalmenu) {
               
                 if((additionalmenu.additionalNav.length == 0)&&(typeof  additionalmenu.langNav==="undefined") ){
                      $('.fg-web-top-nav-wrapper').hide();
                 }
               if(additionalmenu){
                   if (additionalmenu) {
                    var additionalNavJson = { additionalNav: additionalmenu.additionalNav, activeMenu: additionalmenu.currentmenu };
                    var htmlFinal = FGTemplate.bind(FgWebsiteObj.templateAdditionNav, additionalNavJson);
                    $('#fg-div-additional').prepend(htmlFinal);
                    var htmlFinalMobile = FGTemplate.bind(FgWebsiteObj.templateAdditionNavMobile, additionalNavJson);
                    $('.fg-dev-div-additional').append(htmlFinalMobile);
                       
                    if(additionalmenu.defLang!= $('html').attr('lang'))
                        $('html').attr('lang',additionalmenu.defLang);
                   }
                    if(additionalmenu.langNav){
                     var htmlFinal = FGTemplate.bind('template_lang_navigation', {langNav:additionalmenu.langNav,deflang:additionalmenu.defLang} );
                     $('.fg-web-top-nav-languages').append(htmlFinal);
                    }
                    if((additionalmenu.additionalNav.length == 0)){
                        $('.fg-icon-additional-nav').addClass('fg-hide'); 
                    }
               }
                
            }
        });
         
         
     }
     
      
     /*
     ================================================================================================ 
     * Navigation actions - Handle header nav icon click (on mobile view)
     * has Custom events
     * selector .fg-web-nav-icon
     * shown.fg.nav trigger after nav displayed
     * hidden.fg.nav trigger after nav hidden 
     ================================================================================================ 
     */
     public headerNavIconClick() {        
        $('.fg-web-nav-icon').on('click',function(){
            var $this = $(this)
            var $navIcon =  $this;
            var $navContainer = $navIcon.siblings('.fg-web-main-nav');
            $navIcon.toggleClass('active');
            $navContainer.toggleClass('active');
            _thisFgWebsiteTheme.setNavHeight();
            if($navContainer.hasClass('active')){

                    $this.trigger( "shown.fg.nav" );

            }else{

                    $this.trigger( "hidden.fg.nav" );
            }
            
        });
     } 
     
     /**
     * header navigation actions
     */
    public navigationActions() {
        $(window).load(function() {
            FgWebsiteThemeObj.headerNavIconClick();

            $('.dropdown-submenu .fg-dropdown-toggle').on("click", function(e) {
                if ($(window).width() <= 768) {
                    $(this).parent('li').toggleClass('open');
                    $(this).toggleClass('open');
                    e.stopPropagation();
                    e.preventDefault();
                }
            });

            $(".fg-web-main-nav .dropdown,.fg-web-main-nav .dropdown-submenu").on('mouseenter mouseleave tap', function(e) {

                if ($(this).children('ul').length) {
                    var elm = $(this).children('ul');
                    var off = elm.offset();
                    var l = off.left;
                    var w = elm.width();
                    var docW = $(window).width();
                    var isEntirelyVisible = (l + w <= docW);
                    if (!isEntirelyVisible) {

                        elm.removeClass('fg-web-open-right').addClass('fg-web-open-left');
                    } else {                           // $(this).removeClass('edge');
                    }
                }
            });
        });
    }   
     
};
