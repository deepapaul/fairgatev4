var FgMoreMenu = function() {

    //var menuSelector, contentSelector;
    var handleMoreMenu = function(menuSelector) {
        $(menuSelector + ' li').removeClass("hidden").addClass("show");
        $(menuSelector + ' > li.active').removeClass(" fg-truncate").css("max-width",""); 
        $(menuSelector + ' > li.active').css("max-width","");      
        var w = $(menuSelector + ' > li.active').outerWidth(true);
        var mw = $(menuSelector).width() - $(menuSelector + ' > li.datahideshow').outerWidth(true) - 21;
        $(menuSelector + ' > li.datahideshow').addClass("hidden").removeClass("show");    
        if (mw < w) {

            //fg-truncate is using to make one tab always visible even if it has more width than total more tab width

            w = mw;

            $(menuSelector + ' > li.active').removeClass("hidden").addClass("show fg-truncate").css("max-width",w);           
            $(menuSelector + ' .datahideshow ul > li.active').addClass("hidden").removeClass("show"); 
            // $(menuSelector + ' > li.active').addClass("hidden").removeClass("show");            
            // $(menuSelector + ' .datahideshow ul > li.active').removeClass("hidden").addClass("show"); 
            var spanWidth= $(menuSelector + ' > li.active.fg-truncate span.badge').outerWidth(true);
            $(menuSelector + ' li.active span.fg-dev-tab-text').css({'max-width':w-spanWidth-45});
            $(menuSelector + ' .datahideshow ul').addClass("fg-more-menu-only");            
            
            // check trucated content have badges
//            if( $(menuSelector + ' > li.active.fg-truncate span').hasClass('fg-badge-new')  ){
//                $(menuSelector + ' > li.active.fg-truncate').addClass('has-badge-new');
//            }else{
//                $(menuSelector + ' > li.active.fg-truncate').removeClass('has-badge-new');
//            }
        } else {
            $(menuSelector + ' > li.active').removeClass("hidden fg-truncate").addClass("show").css("max-width","");         
            $(menuSelector + ' .datahideshow ul > li.active').addClass("hidden").removeClass("show");   
            $(menuSelector + ' .datahideshow ul').removeClass("fg-more-menu-only");              
        }
        var i = -1;
        var menuhtml = '';
        $(menuSelector + ' > li').each(function(index) {
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
        $(menuSelector).css('visibility','visible');
         
    }

    var handleMenuError = function(menuSelector,contentSelector) {
        $(contentSelector + ' .has-error').each(function() {
            var attrId = $(this).attr('data-attrId');
            var parentContainer = $(contentSelector + ' div[data-attrid=' + attrId + ']').closest('.tab-pane').attr('data-catid');
            $(menuSelector + '  li#data_li_' + parentContainer).addClass('has-error');
            var tabId=$(contentSelector + ' div[data-attrid=' + attrId + ']').closest('.tab-pane').attr('data-panel-tab');
            if(typeof tabId !== typeof undefined){
                $(menuSelector + '  li#data_li_' + tabId).addClass('has-error');
            }
        });
        $(menuSelector + ' .datahideshow').removeClass('more-has-error');
    }

    var handleMoreMenuError = function(menuSelector) {
        $(menuSelector + ' li.datahideshow ul li.has-error').each(function() {
            if ($(menuSelector + ' li.datahideshow ul li.has-error').hasClass('show')) {
                $(menuSelector + ' li.datahideshow ul li.has-error').closest('.datahideshow').addClass('more-has-error');
            }
        });
    }
    var handleMoreMenuClick = function(menuSelector,contentSelector) {
//        $(menuSelector + ' li a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
//            var clickId = $(this).parent().attr('id');
//            $(menuSelector + ' li').removeClass("active");
//            $(menuSelector + ' #' + clickId).addClass("active");
        $(menuSelector + ' li a[data-toggle="tab"]').on('shown.bs.tab', function() {
            var clickId = $(this).parent().attr('id');
            var parentMoreTab = $(this).closest('.data-more-tab').attr('id');
            $('#'+parentMoreTab + ' li').removeClass("active");
            $('#'+parentMoreTab + ' #'+clickId).addClass("active");     
            handleMenuError(menuSelector,contentSelector);
            handleMoreMenu(menuSelector);
            handleMoreMenuError(menuSelector);
            var type = $(this).parent().data('type');
            FgStickySaveBarInternal.init(type);
        });
    }
    return {
        //main function to initiate the theme
        initServerSide: function(menuId) {
            var menuSelector = '#' + menuId;
            $(function() {
                handleMoreMenu(menuSelector);
                $(window).resize(function() {
                    handleMoreMenu(menuSelector);
                });
                $(window).load(function() {
                    handleMoreMenu(menuSelector);
                });
            });
        },
        initClientSide: function(menuId, containerId) {
            var menuSelector = '#' + menuId;
            var contentSelector = '#' + containerId;
            $(function() {
                handleMenuError(menuSelector,contentSelector);
                handleMoreMenu(menuSelector);
                handleMoreMenuError(menuSelector);
                $(window).resize(function() {
                    handleMenuError(menuSelector,contentSelector);
                    handleMoreMenu(menuSelector);
                    handleMoreMenuError(menuSelector);
                });
                $(window).load(function() {
                    handleMenuError(menuSelector,contentSelector);
                    handleMoreMenu(menuSelector);
                    handleMoreMenuError(menuSelector);
                });
                handleMoreMenuClick(menuSelector,contentSelector);
            });
        },
        initClientSideWithNoError: function(menuId, containerId) {
            var menuSelector = '#' + menuId;
            var contentSelector = '#' + containerId;
            $(function() {
                handleMoreMenu(menuSelector);
                $(window).resize(function() {
                    handleMoreMenu(menuSelector);
                });
                $(window).load(function() {
                    handleMoreMenu(menuSelector);
                });
                handleMoreMenuClick(menuSelector,contentSelector);
            });
        }
    };
}();

