var FgGotcourtsApiService = (function () {
    function FgGotcourtsApiService(opt) {
        this.gcBookingUrl = '';
        this.generateTokenUrl = '';
        this.cancelGcServiceUrl = '';
        this.clubApiToken = '';
        this.clubApiId = '';
        this.step = 1;
        this.clipboardObj = {};
        this.extendOptions(opt);
        this.loadTemplate();
    }
    FgGotcourtsApiService.prototype.extendOptions = function (opt) {
        this.step = opt.step;
        this.gcBookingUrl = opt.gcBookingUrl;
        this.generateTokenUrl = opt.generateTokenUrl;
        this.cancelGcServiceUrl = opt.cancelGcServiceUrl;
        this.clubApiToken = opt.clubApiToken;
        this.clubApiId = opt.clubApiId;
    };
    FgGotcourtsApiService.prototype.setStep = function (step) {
        this.step = step;
    };
    FgGotcourtsApiService.prototype.setClubApiToken = function (clubApiToken) {
        this.clubApiToken = clubApiToken;
    };
    FgGotcourtsApiService.prototype.setClubApiId = function (clubApiId) {
        this.clubApiId = clubApiId;
    };
    FgGotcourtsApiService.prototype.handleGotCourtbooking = function () {
        var self = this;
        FgXmlHttp.post(this.gcBookingUrl, {}, false, function (d) {
            self.setGcApiData(d.data);
            self.loadTemplate();
        });
    };
    FgGotcourtsApiService.prototype.generateToken = function (type) {
        var tType = typeof type == 'undefined' ? '' : type;
        var self = this;
        self.disableElement('fg-dev-generate-new-token-btn');
        self.disableElement('fg-dev-regenerate-token-btn');
        FgXmlHttp.post(this.generateTokenUrl, {
            type: tType,
            apiId: $('#fg-dev-club-api-id').val(),
        }, false, function (d) {
            self.setGcApiData(d.data);
            self.loadTemplate();
        });
    };
    FgGotcourtsApiService.prototype.cancelGotCourtsService = function () {
        var self = this;
        self.disableElement('fg-dev-cancel-api-service-btn');
        FgXmlHttp.post(this.cancelGcServiceUrl, {
            apiId: $('#fg-dev-club-api-id').val()
        }, false, function (d) {
            self.setGcApiData(d.data);
            self.loadTemplate();
        });
    };
    FgGotcourtsApiService.prototype.setGcApiData = function (data) {
        this.setStep(data['step']);
        this.setClubApiToken(data['tokenWithClub']);
        this.setClubApiId(data['gcApiId']);
    };
    FgGotcourtsApiService.prototype.loadTemplate = function () {
        var templateCompiled = _.template($('#templateContactApplicationFormField').html());
        var gcApiHtml = templateCompiled({ step: this.step, token: this.clubApiToken, clubApiId: this.clubApiId });
        $('#gc-api-connection-wrapper').html(gcApiHtml);
        this.loadTemplateCallback();
    };
    FgGotcourtsApiService.prototype.loadTemplateCallback = function () {
        this.handleCheckboxClickEvent();
        this.handleButtonClickEvents();
        $('#fg-dev-book-gcapi-checkbox').uniform();
        FgTooltip.init();
    };
    FgGotcourtsApiService.prototype.handleCopyToClipboard = function () {
        this.clipboardObj = new Clipboard('#fg-dev-copy-token-clipboard-btn');
    };
    FgGotcourtsApiService.prototype.disableElement = function (el) {
        $('#' + el).attr('disabled', true);
    };
    FgGotcourtsApiService.prototype.handleButtonClickEvents = function () {
        var self = this;
        $('#fg-book-gcapi-connection-btn').off();
        $('#fg-book-gcapi-connection-btn').click(function () {
            self.handleGotCourtbooking();
        });
        $('#fg-dev-generate-new-token-btn').off();
        $('#fg-dev-generate-new-token-btn').click(function () {
            self.generateToken('new');
        });
        $('#fg-dev-regenerate-token-btn').off();
        $('#fg-dev-regenerate-token-btn').click(function () {
            self.generateToken('');
        });
        $('#fg-dev-cancel-api-service-btn').off();
        $('#fg-dev-cancel-api-service-btn').click(function () {
            self.cancelGotCourtsService();
        });
        this.handleCopyToClipboard();
    };
    FgGotcourtsApiService.prototype.handleCheckboxClickEvent = function () {
        $('#fg-dev-book-gcapi-checkbox').off();
        $('#fg-dev-book-gcapi-checkbox').click(function () {
            if ($(this).prop('checked')) {
                $('#fg-book-gcapi-connection-btn').attr('disabled', false).removeClass('fg-disabled-link');
            }
            else {
                $('#fg-book-gcapi-connection-btn').attr('disabled', true).addClass('fg-disabled-link');
            }
        });
    };
    return FgGotcourtsApiService;
}());
