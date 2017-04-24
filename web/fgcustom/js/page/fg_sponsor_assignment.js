FgSponsorAssignment = {
    /**
     * Function to payment method switching 
     */
    handlePaymentSwitch:function(){
        $('input[name=privacyContact]').on('change',function(){
            $('dl[data-payment]').hide();
            $('dl[data-payment='+$(this).val()+']').show();
            if($('input[name=privacyContact]:checked').val()==='custom' && $('[data-custom=payment]>div').length===0 ){
                $('a[data-add]').click();
            }
        });
    },
    /**
     * Function to subcategory selection on category switching
     */
    handleCategoryChange:function(){
        $('select[data-category]').on('change',function(){
            var template=$('#serviceDropdownTemplate').html();
            $('[data-deposited-row]').remove();
            var results = _.template(template,{'catId': $(this).val(),'subCat':'' });
            $('div[data-service-area]').html(results); 
            setTimeout(function(){
                $('div[data-service-area] select[data-services]').selectpicker();
                FgDirtyForm.checkForm('service_assignment');
            },100);
            
        });
    },
    /**
     * Function to handle 'deposited with' on service switching
     */
    handleServiceTypeSwitching:function(){
        $('body').on('change','select[data-services]',function(){
            FgSponsorAssignment.populateDeposited();
            FgSponsorAssignment.handleServiceDefault();
            $(this).addClass('fg-dev-newfield');
            $('form input[type="submit"]').removeAttr('disabled');
            $('form input[type="reset"]').removeAttr('disabled');
        });
    },
    populateDeposited:function(){
        $('[data-deposited-row]').remove();
        var serviceType= $('select[data-services]').find('option[value='+$('select[data-services]').val()+']').attr('data-servicetype');
        //display 'diposited with'
        if(serviceType=='team' || serviceType=='contact'){
            var teamTempate=$('#serviceDepositedWithTemplate').html();
            var depositedHtml = _.template(teamTempate,{'serviceType': serviceType });
            $(depositedHtml).insertAfter('dd[data-row=end-date]');
            if(serviceType=='contact'){
                FgSponsorAssignment.handleContactsAuto();
            } else{
                $('select[data-deposited]').selectpicker({ noneSelectedText: datatabletranslations.noneSelectedText}); 
                FgColumnSettings.handleSelectPicker();
            }
        } 
        $('select[data-services]').selectpicker('render');
    },
    /**
     * Contact autocomplete handler for include and exclude in contact doc
     */
    handleContactsAuto:function(){
        selectedContact =(typeof selectedContacts===typeof undifined) ? '':selectedContacts;
        $('input[data-contactlist]').fbautocomplete({
            url: contactUrl, // which url will provide json!
            removeButtonTitle: removestring,
            params: {'isCompany': 2} ,        
            selected: selectedContact,
            maxItems: 50,
            useCache: true,
            onItemSelected: function($obj, itemId, selected) {
                var ids= $('#'+$obj.context.id+'Selection').val()==='' ? []: JSON.parse($('#'+$obj.context.id+'Selection').val());
                ids.push(itemId);
                $('#'+$obj.context.id+'Selection').val(JSON.stringify(ids));
                FgDirtyForm.checkForm('service_assignment');
                $('#'+$obj.context.id+'Selection').addClass('fg-dev-newfield');
            },
            onItemRemoved: function($obj, itemId) {
                ids= JSON.parse($('#'+$obj.context.id+'Selection').val());
                if(typeof (ids) == 'object') {
                    var newArray = jQuery.grep(ids, function (item,index) { return item !== itemId;  });
                    $('#'+$obj.context.id+'Selection').val(JSON.stringify(newArray));
                    FgDirtyForm.checkForm('service_assignment');
                }
                $('#'+$obj.context.id+'Selection').addClass('fg-dev-newfield');
            },
            onAlreadySelected: function($obj) {

            }
        });
    },
    /**
     * Function to handle fairgate dirty class on save
     */
    handleDirtyOnSave:function(){
        var paymentplan=$('form input[data-key=payment_plan]:checked').val();
        if($('form input[data-key=payment_plan].fairgatedirty').hasClass('fairgatedirty')){
            $('[data-payment='+paymentplan+'] input[type=text][data-key],[data-payment='+paymentplan+'] select[data-key]').addClass('fairgatedirty');
        }
        $('.newCustomRow input[type=text][data-key],.newCustomRow select[data-key]').addClass('fairgatedirty');
        if(paymentplan=='custom' || paymentplan=='none' ) {
            $('[data-payment=regular]').find(':input.fairgatedirty').removeClass('fairgatedirty');
        }
        if(paymentplan=='regular' || paymentplan=='none' ) {
            $('[data-payment=custom]').find(':input.fairgatedirty').removeClass('fairgatedirty');
        }
    },
    /**
     * Function to handle save assignment call back
     */
    saveAssignment:function(){
        $('#save_changes').on('click',function(){
            $($('input[data-key=first_payment_date]').attr('data-error-container')).addClass('hide');
            $($('input[data-key=last_payment_date]').attr('data-error-container')).addClass('hide');
            $('input[data-key=first_payment_date]').parent().removeClass('has-error');
            $('input[data-key=last_payment_date]').parent().removeClass('has-error');
            $('[data-payment] :input').addClass('ignore');
            $('[data-payment='+$('form input[data-key=payment_plan]:checked').val()+'] :input').removeClass('ignore');
            var deteValid=FgSponsorAssignment.validatePaymentDate();
            if($('#service_assignment').valid() && deteValid){
                if(!bookingId){ 
                    $('form :input[data-key]').addClass('fairgatedirty');
                } else {
                    $('form input[type=hidden]').addClass('fairgatedirty');
                }
                var objectArray={};
                FgSponsorAssignment.handleDirtyOnSave();
                objectArray=FgParseFormField.fieldParse();
                FgXmlHttp.post(pathUpdate, {'data': objectArray,'backTo':backTo}, false);
                FgDirtyForm.init();
            }
            setTimeout(function() {
                FgSponsorAssignment.formatError();
           }, 500);
        });
        
    },
    /**
     * Function to add new custom payment
     */
    addRow: function() {
        $('[data-add]').on('click',function(){
            var serviceValue=$('select[data-services]').val();
            var sAmount= (serviceValue !=='') ? $('select[data-services] option[value='+serviceValue+']').attr('data-price'):'';
            _template = $('#servicePaymentsTemplate').html();
            var result_data = _.template(_template, { 'data': 'new_'+$.now(),'amount':sAmount });
            $('div[data-custom=payment]').append($(result_data).addClass('new-row'));
            $('div[data-custom=payment] .new-row:last select').selectpicker();
            FgFormTools.handleInputmask();
            $('div[data-custom=payment] .new-row:last').find('.date').datepicker(FgApp.dateFormat);
            FormValidation.init('service_assignment','');
            if($('[data-total]').html()===''){
                $('[data-total]').html(FgClubSettings.getAmountWithCurrency('0.00'));
            }
            if($('[data-fiscal-total]').html()===''){
                $('[data-fiscal-total]').html(FgClubSettings.getAmountWithCurrency('0.00'));
            }
            FgDirtyForm.checkForm('service_assignment');
        });
    },
    /**
     * Function to delete custom payment
     */
    deleteRow:function(){ 
        $('body').on('click', '.new-row .closeico', function(e){
            if($('[data-custom=payment]>div').length!==1 ){
                $(this).parents('.new-row').remove();
                FgSponsorAssignment.updateTotalAmount();
            }
        });
    },
    /**
     * Function to handle date range
     * @param {type} fromId
     * @param {type} toId
     */
    handleDateRange:function(fromId,toId){
        $('#'+fromId).datepicker(FgApp.dateFormat).on('changeDate', function(e){
            $('#'+toId).datepicker('setStartDate', e.date);
            var endDate = $('#'+toId).datepicker('getDate');
            if(endDate=='Invalid Date'){ 
                $('#'+toId).datepicker('setDate','');
            }
        });
        $('#'+toId).datepicker($.extend({},FgApp.dateFormat,{startDate:$('#'+fromId+' input').val()})).on('changeDate', function(e){ 
            $('#'+fromId).datepicker('setEndDate', e.date);
            var startDate = $('#'+fromId).datepicker('getDate');
            if(startDate=='Invalid Date'){
                $('#'+fromId).datepicker('setDate','');
            }
        });  
    },
    /**
     * Function to set default service values
     */
    handleServiceDefault:function(){
        var serviceValue=$('select[data-services]').val();
        if(serviceValue !==''){
            var selectedOpt=$('select[data-services] option[value='+serviceValue+']');
            pPlan=selectedOpt.attr('data-pPlan');
            $('input[data-key=payment_plan][value='+pPlan+']').click();
            if(pPlan==='regular'){
                $('[data-payment=regular] input[data-key=repetition_months]').val(selectedOpt.attr('data-repMonth'));
                $('[data-payment=regular] input[data-key=amount]').val(selectedOpt.attr('data-price'));
            } else if(pPlan==='custom'){
                $('div[data-custom=payment]').html('');
                $('a[data-add]').click();
                $('[data-payment=custom] .new-row input[data-amount]').val(selectedOpt.attr('data-price'));
            }
            
        }
    },
    /**
     * Function to handle Discount validation
     */
    handleDiscountValidation:function(){
        $("body").on('change', '[data-discount-validate]', function() { 
            var me=$(this).attr('data-discount-validate');
            $('[data-discount-validate='+me+']').addClass('fg-dev-newfield');
            if($('select[data-discount-validate='+me+']').val()=='P'){
                $('input[data-discount][data-discount-validate='+me+']').attr('max','100');
            } else if($('select[data-discount-validate='+me+']').val()=='A'){
                $('input[data-discount][data-discount-validate='+me+']').attr('max', FgClubSettings.unFormatNumber($('input[data-amount][data-discount-validate='+me+']').val()));
            }
            if(me != 'regular') {
                FgSponsorAssignment.updateTotalAmount();
            }
        });
        $("body").on('change', '[data-paymentdate]', function() {
            FgSponsorAssignment.updateTotalAmount();
        });
        $("body").on('change', '[data-discount-shown]', function() {
            atrVal=$(this).attr('data-discount-shown');
            $('[data-discount='+atrVal+']').val(FgClubSettings.unFormatNumber($(this).val()));
            $('[data-discount='+atrVal+']').change();
        });
    },
    
    /**
     * Function to update total amount of custom payment
     */
    updateTotalAmount:function(){
        //if($('div[data-custom="payment"] :input.fg-validate-inp').valid())
        {
            var totals=FgSponsorAssignment.getTotalPayment(fiscalStart,fiscalEnd);
            $('[data-total]').html(FgClubSettings.getAmountWithCurrency(totals.totalPayment));
            $('[data-fiscal-total]').html(FgClubSettings.getAmountWithCurrency(totals.totalFiscalYearPayment));
        }
        setTimeout(function() {
             FgSponsorAssignment.formatError();
        }, 500);
        if($('[data-total]').html()==''){
            $('[data-total]').html(FgClubSettings.getAmountWithCurrency('0.00'));
        }
        if($('[data-fiscal-total]').html()==''){
            $('[data-fiscal-total]').html(FgClubSettings.getAmountWithCurrency('0.00'));
        }
    },
    /**
     * Function to reset changes
     */
    resetChanges:function(){
        $('body').off('click','#cancel_button');
        $('#cancel_button').click(function(){
            var data_url = backTo;
            if(!$(this).is(':disabled') && data_url !== '') {
                data_url = data_url.trim();
                FgDirtyForm.init();
                document.location = data_url;
            }
        });
    },
    /**
     * Page initialization function
     */
    Init:function(){
        var that=FgSponsorAssignment;
        FormValidation.init('service_assignment','');
        that.addRow();
        that.deleteRow();
        that.handlePaymentSwitch();
        that.handleCategoryChange();
        that.handleServiceTypeSwitching();
        that.handleDateRange('fromDate','toDate');
        that.handleDateRange('firstOnDate','lastOnDate');
        that.handleDiscountValidation();
        that.resetChanges();
        if($('select[data-category]').val() !==''){
            var template=$('#serviceDropdownTemplate').html();
            var results = _.template(template,{'catId': $('select[data-category]').val(),'subCat': $('[data-service-area]').attr('data-service-area')});
            $('div[data-service-area]').html(results); 
            $('div[data-service-area] select[data-services]').selectpicker();
            if($('div[data-service-area] select[data-services]') !==''){
                FgSponsorAssignment.populateDeposited();
            }
        }
        if(!bookingId){
            FgSponsorAssignment.handleServiceDefault();
            $('form input[type="submit"]').removeAttr('disabled');
            $('form input[type="reset"]').removeAttr('disabled');
        } else{
            if($('input[data-key=payment_plan]:checked').val()==='custom') {
                FgSponsorAssignment.updateTotalAmount();
            }
            FgSponsorAssignment.updateDirty();
        }
        
        that.saveAssignment();
    },
    /* Find total of payments of custom payment plan*/
    getTotalPayment: function(startDate, endDate) {
        var totalPayment = 0;
        var totalFiscalYearPayment = 0;
        var paymentAmount = 0;
        var data = {};
        $('div[data-custom="payment"] >.row').each(function() {
            pdate =$(this).find('input[data-paymentdate]').val();
            var dateValue = pdate.split('.');
            var paymentDate = dateValue[2]+'-'+dateValue[1]+'-'+dateValue[0];            
            amount = parseFloat(FgClubSettings.unFormatNumber($(this).find('input[data-amount]').val()));
            discount = parseFloat(FgClubSettings.unFormatNumber($(this).find('input[data-discount]').val()));
            discount = isNaN(discount) ? 0 : discount;
            discountType = $(this).find('select[data-discount-validate]').val();
            paymentAmount = FgUtility.getAmountWithDiscount(amount, discountType, discount);
            totalPayment += paymentAmount;
            
            var startDatePayTimestamp = moment(startDate, FgLocaleSettingsData.momentDateFormat).format('X');
            var endDateTimestamp = moment(endDate, FgLocaleSettingsData.momentDateFormat).format('X');
            var paymentDateTimestamp = moment(pdate, FgLocaleSettingsData.momentDateFormat).format('X');
            
            if(startDatePayTimestamp <= paymentDateTimestamp && paymentDateTimestamp <= endDateTimestamp){
                totalFiscalYearPayment += paymentAmount;
            }
        });
        data = {totalPayment :totalPayment.toFixed(2), totalFiscalYearPayment :totalFiscalYearPayment.toFixed(2)};
        return data;
    },
    /**
     * Function to check whether payment dates are less than service end date.
     * @returns {Boolean}
     */
    validatePaymentDate:function(){
        var payplan = $('input[data-key=payment_plan]:checked').val();
        var serviceEndDate = $('#endDate').val(); 
        var returnValue= true;
        if(payplan=='regular' && serviceEndDate !==''){
            var firstPay=$('input[data-key=first_payment_date]').val();
            var endDateValueTimestamp = moment(serviceEndDate, FgLocaleSettingsData.momentDateFormat).format('X');

            if(firstPay !==''){
                var firstPayTimestamp = moment(firstPay, FgLocaleSettingsData.momentDateFormat).format('X');
                if(endDateValueTimestamp < firstPayTimestamp){
                    $($('input[data-key=first_payment_date]').attr('data-error-container')).removeClass('hide');
                    $('input[data-key=first_payment_date]').parent().addClass('has-error');
                    returnValue= false;
                }
            }
            var lastPay=$('input[data-key=last_payment_date]').val();
            if(lastPay !==''){
                var lastPayTimestamp = moment(lastPay, FgLocaleSettingsData.momentDateFormat).format('X');
                if(endDateValueTimestamp < lastPayTimestamp){
                    $($('input[data-key=last_payment_date]').attr('data-error-container')).removeClass('hide');
                    $('input[data-key=last_payment_date]').parent().addClass('has-error');
                    returnValue= false;
                }
            }
        }
        return returnValue;
    },
    formatError:function(){
        $('.has-error .help-block:visible').each(function(){
            var elemId=$(this).parents('.has-error').attr('id');
            var maxVal=$('[data-error-container=#'+elemId+']').attr('max');
            var magEr=$(this).text().replace(maxVal,FgClubSettings.formatNumber(maxVal));
            $(this).html(magEr);
        });
    },
    /**
     * Function to update dirty on input change
     */
    updateDirty:function(){
        $('body').on('change','select[data-key=depositedWith]',function(){
            FgDirtyForm.checkForm('service_assignment');
        });
    }
};
