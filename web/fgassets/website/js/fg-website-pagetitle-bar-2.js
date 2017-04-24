var FgWebsitePageTitleBar = (function () {
    function FgWebsitePageTitleBar(containerId, options) {
        this.containerId = containerId;
        this.containerObj = $('#' + containerId);
        this.navContainerObj = $('#nextprev-' + containerId);
        this.options = $.extend({}, this.defaultOptions(), options);
        this.renderPageTitleBar();
        this.renderPageCallback();
    }
    FgWebsitePageTitleBar.prototype.defaultOptions = function () {
        var defaultOptions = {
            pagetitleBarClass: 'fg-web-page-title-bar row',
            containerTemplate: 'template_pagetitlebar_container',
            navContainerTemplate: 'template_nextprev_container',
            containerClass: 'container',
            titleBar: false,
            title: '',
            breadcrumb: false,
            breadcrumbData: [],
            nextPrevious: false,
            hideNextPreviousOnSmallDevices: false,
            nextPreviousLinks: [],
            nextPreviousLabel: [],
            nextPreviousSubLabel: [],
            backButton: false,
            backButtonTop: false,
            hideBackOnSmallDevices: false,
            backButtonData: '',
            backButtonLabel: '',
            backButtonSubLabel: '',
            searchBox: false,
            searchBoxType: '',
            timeperiod: false,
            timeperiodData: {},
            calendarViewSwitch: false
        };
        return defaultOptions;
    };
    FgWebsitePageTitleBar.prototype.renderPageTitleBar = function () {
        var options = this.options;
        this.containerObj.addClass(options.pagetitleBarClass);
        if (options.backButtonTop) {
            this.containerObj.addClass('has-close-btn');
        }
        if (options.breadcrumb) {
            this.containerObj.addClass('has-breadcrumbs');
        }
        this.containerObj.html(FGTemplate.bind(options.containerTemplate, options));
        this.navContainerObj.html(FGTemplate.bind(options.navContainerTemplate, options));
    };
    FgWebsitePageTitleBar.prototype.renderPageCallback = function () {
        $('#fg-page-timeperiod-input:not(.hide)').selectpicker({
            showIcon: false,
            showTick: false,
            tickIcon: ''
        });
        $('#fg-page-monthswitch-input:not(.hide)').selectpicker({
            showIcon: false,
            showTick: false,
            tickIcon: ''
        });
    };
    return FgWebsitePageTitleBar;
}());
