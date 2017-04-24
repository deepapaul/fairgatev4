var FgPopOver = (function () {
    function FgPopOver() {
    }
    FgPopOver.prototype.init = function (selector, htmlflag, staybahaviour) {
        htmlflag = typeof htmlflag !== 'undefined' ? htmlflag : false;
        staybahaviour = typeof staybahaviour !== 'undefined' ? staybahaviour : true;
        if (staybahaviour) {
            $("body").on('mouseenter touchstart', selector, function () {
                _this = $(this);
                $(this).popover({
                    html: htmlflag,
                    trigger: 'manual',
                    container: 'body',
                    content: _this.find('.popover-content').html(),
                    placement: function () {
                        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                            return 'auto';
                        }
                        else {
                            var width = $(window).width() - 300;
                            var xPos = this.getPosition().left;
                            var placement = width < xPos ? 'left' : 'auto';
                            return placement;
                        }
                    },
                }).on("mouseenter click", function () {
                    var _this = this;
                    $(this).popover("show");
                    $(this).siblings(".popover").on("mouseleave", function () {
                        $(_this).popover('hide');
                    });
                }).on("mouseleave", function () {
                    var _this = this;
                    setTimeout(function () {
                        $(_this).popover("hide");
                    }, 50);
                    $('.popover .popover-content').width('');
                }).popover('show');
            });
        }
        else {
            $('body').popover({
                selector: selector,
                trigger: 'hover',
                html: htmlflag,
                delay: { show: 100, hide: 800 }
            });
        }
    };
    FgPopOver.prototype.customPophover = function (userClass) {
        $(userClass).popover({
            trigger: 'hover',
            html: true,
            placement: 'auto'
        });
    };
    return FgPopOver;
}());
