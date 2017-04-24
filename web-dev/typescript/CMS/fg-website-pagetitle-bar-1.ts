class FgWebsitePageTitleBar {

	containerId: string;
	containerObj: Object;
	options: Object;

	constructor(containerId, options) {
        this.containerId = containerId;
        this.containerObj = $('#'+containerId);
        this.options = $.extend({}, this.defaultOptions(), options);
        this.renderPageTitleBar();
        this.renderPageCallback();
    }

    private defaultOptions(){
    	let defaultOptions = {
    		pagetitleBarClass: 'fg-web-page-title-bar row',
    		containerTemplate: 'template_pagetitlebar_container',
    		containerClass: 'container',
    		titleBar: false,
            title: '',
            breadcrumb: false,
            breadcrumbData: [],
            nextPrevious: false,
            hideNextPreviousOnSmallDevices: false,
            nextPreviousLinks: [],
            backButton: false,
            hideBackOnSmallDevices: false,
            backButtonData: '',
            searchBox: false,
            searchBoxType: '',
            timeperiod: false,
            timeperiodData: {},
            calendarViewSwitch: false
    	};

    	return defaultOptions;
    }

    private renderPageTitleBar(){
    	let options = this.options;
        this.containerObj.addClass(options.pagetitleBarClass);
        if(options.backButton) {
            this.containerObj.addClass('has-close-btn');
        }
        if (options.breadcrumb) {
           this.containerObj.addClass('has-breadcrumbs');
        }
        var containerHtml = FGTemplate.bind(options.containerTemplate, options);
        this.containerObj.html(containerHtml);
    }

    private renderPageCallback(){
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
    }
}