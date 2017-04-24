/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
class FgGotcourtsApiService {
    public gcBookingUrl: string = '';
    public generateTokenUrl: string = '';
    public cancelGcServiceUrl: string = '';
    public clubApiToken: string = '';
    public clubApiId: string = '';
    public step: any = 1;
    public clipboardObj: any = {};

    constructor(opt) {
        this.extendOptions(opt);
        this.loadTemplate();
    }

    private extendOptions(opt) {
        this.step = opt.step;
        this.gcBookingUrl = opt.gcBookingUrl;
        this.generateTokenUrl = opt.generateTokenUrl;
        this.cancelGcServiceUrl = opt.cancelGcServiceUrl;
        this.clubApiToken = opt.clubApiToken;
        this.clubApiId = opt.clubApiId;
    }

    private setStep(step) {
        this.step = step;
    }

    private setClubApiToken(clubApiToken) {
        this.clubApiToken = clubApiToken;
    }

    private setClubApiId(clubApiId) {
        this.clubApiId = clubApiId;
    }

    private handleGotCourtbooking() {
        let self = this;
        FgXmlHttp.post(this.gcBookingUrl, {

        }, false, function(d) {
            self.setGcApiData(d.data);
            self.loadTemplate();
        });
    }

    private generateToken(type) {
        let tType = typeof type == 'undefined' ? '' : type;
        //To generate token
        let self = this;
        self.disableElement('fg-dev-generate-new-token-btn');
        self.disableElement('fg-dev-regenerate-token-btn');
        FgXmlHttp.post(this.generateTokenUrl, {
            type: tType,
            apiId: $('#fg-dev-club-api-id').val(),
        }, false, function(d) {
            self.setGcApiData(d.data);
            self.loadTemplate();
        });
    }
    
    private cancelGotCourtsService() {
        let self = this;
        self.disableElement('fg-dev-cancel-api-service-btn');
        FgXmlHttp.post(this.cancelGcServiceUrl, {
            apiId: $('#fg-dev-club-api-id').val()
        }, false, function(d) {
            self.setGcApiData(d.data);
            self.loadTemplate();
        });
    }
    
    private setGcApiData(data){
        this.setStep(data['step']);
        this.setClubApiToken(data['tokenWithClub']);
        this.setClubApiId(data['gcApiId']);
    }

    private loadTemplate() {
        var templateCompiled = _.template($('#templateContactApplicationFormField').html()); 
        var gcApiHtml = templateCompiled({ step: this.step, token: this.clubApiToken, clubApiId: this.clubApiId });
        $('#gc-api-connection-wrapper').html(gcApiHtml);
        this.loadTemplateCallback();
    }
    
    private loadTemplateCallback(){
        this.handleCheckboxClickEvent();
        this.handleButtonClickEvents();
        $('#fg-dev-book-gcapi-checkbox').uniform();
        FgTooltip.init();
    }

    private handleCopyToClipboard() {
       this.clipboardObj = new Clipboard('#fg-dev-copy-token-clipboard-btn');
    }
    
    private disableElement(el){
        $('#'+el).attr('disabled',true);
    }
    
    public handleButtonClickEvents() {
        let self = this;
        //Click event to book GotCourts api service
        $('#fg-book-gcapi-connection-btn').off();
        $('#fg-book-gcapi-connection-btn').click(function() {
            self.handleGotCourtbooking();
        });
        //Click event to generate club token
        $('#fg-dev-generate-new-token-btn').off();
        $('#fg-dev-generate-new-token-btn').click(function() {
            self.generateToken('new');
        });
        
        //Click event to re-generate token
        $('#fg-dev-regenerate-token-btn').off();
        $('#fg-dev-regenerate-token-btn').click(function() {
            self.generateToken('');
        });
        //Click event to cancel GotCourts api service
        $('#fg-dev-cancel-api-service-btn').off();
        $('#fg-dev-cancel-api-service-btn').click(function() {
            self.cancelGotCourtsService();
        });
        
        //Click event to copy token to clipboard
        this.handleCopyToClipboard();
    }

    public handleCheckboxClickEvent() {
        $('#fg-dev-book-gcapi-checkbox').off();
        $('#fg-dev-book-gcapi-checkbox').click(function() {
            if ($(this).prop('checked')) {
                $('#fg-book-gcapi-connection-btn').attr('disabled', false).removeClass('fg-disabled-link')
            } else {
                $('#fg-book-gcapi-connection-btn').attr('disabled', true).addClass('fg-disabled-link');
            }
        });
    }
    
}





