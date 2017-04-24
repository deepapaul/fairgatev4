// The file for adding any global js functions
FgLocaleSettings = {
    formatDate: function(value, formatType, currentFormat) {
       try {
            //Format using moment.js
            var momentObj = moment(value, currentFormat);
            if(momentObj.isValid()){
                 switch(formatType){
                    case 'date':
                        requiredFormat = FgLocaleSettingsData.momentDateFormat;
                        break;
                    case 'datetime':
                        requiredFormat = FgLocaleSettingsData.momentDateTimeFormat;
                        break;
                    case 'time':
                        requiredFormat = FgLocaleSettingsData.momentTimeFormat;
                        break;
                    default:
                        requiredFormat = 'DD.MM.YYYY';
                        break;
                }

                return momentObj.format(requiredFormat);
            } else {
                console.log('Error: Invalid Format');
                return value;
            }
        }
        catch(err) {
            console.log('Exception: Invalid Format');
            console.log(err);
            return value;
        }
    },
};
// Class for handling club settings like currency and its manipulating functions
FgClubSettings = {
    currency : FgLocaleSettingsData.currency,
    currencyPosition : FgLocaleSettingsData.currencyPosition,
    // Function to get amount with currency #}
    getAmountWithCurrency : function(amount,noParse){
        if(!noParse){
            amount = parseFloat(amount);
            amount = amount.toFixed(2);
        }
        decimalMark=$("<p/>").html(FgLocaleSettingsData.decimalMark).text();
        thousendSeperator=$("<p/>").html(FgLocaleSettingsData.thousendSeperator).text();
        amount=accounting.formatMoney(amount, "", 2, thousendSeperator, decimalMark);
        var currencyAmount = '';
        if (this.currencyPosition == 'right'){
            currencyAmount = amount + ' ' + this.currency;
        } else {
            currencyAmount = this.currency + ' ' + amount ;
        }
        return  currencyAmount;
    },
    /**
     * Function to format number with decimal marker and thousend seperator
     * @param int/float/string numb
     * @param bool noParse
     * @param int decimalPoints decimal digit number
     * @returns string
     */
    formatNumber:function(numb,noParse,decimalPoints){
        if(typeof(decimalPoints)=='undefined'){
            decimalPoints = 2;
        }
        decimalMark=$("<p/>").html(FgLocaleSettingsData.decimalMark).text();
        thousendSeperator=$("<p/>").html(FgLocaleSettingsData.thousendSeperator).text();
        if(numb=='-' || numb==''){
        return numb;
        }
        if(!noParse){
            numb = parseFloat(numb);
            numb = numb.toFixed(decimalPoints);
        }
        if(numb % 1 == 0){
            return accounting.formatMoney(numb, "", 0, thousendSeperator, '');
        } else {
            return numb=accounting.formatMoney(numb, "", decimalPoints, thousendSeperator, decimalMark);
        }
    },
    /**
     * Function to format number with decimal marker
     * @param int/float/string numb
     * @param bool noParse
     * @returns string
     */
    formatDecimalMark:function(numb,noParse){
        decimalMark=$("<p/>").html(FgLocaleSettingsData.decimalMark).text();
        if(!noParse){
            numb = parseFloat(numb);
            numb = numb.toFixed(2);
        }
        if(numb % 1 != 0){
            numb=accounting.formatMoney(numb, "", 2, '', decimalMark);
        }
        return numb;
    },
    /**
     * Function to unformat number with decimal point
     * @param {type} numb
     * @returns {unresolved}
     */
    unFormatNumber:function(numb){
        decimalMark=$("<p/>").html(FgLocaleSettingsData.decimalMark).text();
        numb=numb.replace(decimalMark,'.');

        return numb;
    }
}
//Handling Google maps in  both calendar and cms 
FgMapSettings = {
    mapShow : function(lat, lng, mapDisplay, mapZoomValue,showMarker, mapId, loc){
        // map is drawn based on the parameters passed
        var myCenter=new google.maps.LatLng(lat,lng);
        var mapProp = {
            center:myCenter,
            zoom:mapZoomValue,
            mapTypeId:google.maps.MapTypeId[mapDisplay]
        };
        map=new google.maps.Map(document.getElementById(mapId),mapProp);
        var marker=new google.maps.Marker({
            position:myCenter,
        });
        var infoString = '<div class="fg-map-info">'+loc+'</div>';
        var infowindow = new google.maps.InfoWindow({
            content:infoString
        });
      
        if(showMarker == 1 || showMarker == 'true' )
        {
          marker.setMap(map);
        }
        //location name is shown in the marker incase of calendar if it is not empty
        if(loc!='')
        {
         infowindow.open(map,marker);  
        }

        google.maps.event.addListenerOnce(map, 'idle', function(){
            FgMapSettings.mapInfoWindowResize ()// do something only the first time the map is loaded
        });
    },
    mapResize : function(lat, lng, loc){
        var myCenter=new google.maps.LatLng(lat,lng);
        var mapProp = {
            center:myCenter,
            zoom:15,
            mapTypeId:google.maps.MapTypeId.ROADMAP
        };
        var center = map.getCenter();
            google.maps.event.trigger(map, "resize");
            map.setCenter(center);
    },
    mapInfoWindowResize:function(){
        if($(window).width() < 500){
            $('.fg-map-info').parents().eq(3).addClass('fg-map-info-parent');
        }else{
            $('.fg-map-info').parents().eq(3).removeClass('fg-map-info-parent');

        }

    },
    mapAutoComplete : function(){
        var input =(document.getElementById('locAutoComp'));
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            $('#mapLat').val(place.geometry.location.lat());
            $('#mapLng').val(place.geometry.location.lng());
            if (!place.geometry) {
                $('#locAutoComp').val();
            }
            //save buttons disabled after changing location #System+testing-54
            $('input#locAutoComp').change();
        })
        }
  }

FgGlobalSettings = {
    // Handles date-time picker
    handleDateTimepicker: function(extraSettings) {
        var defaultSettings = {
            language: jstranslations.localeName,
            format: FgLocaleSettingsData.jqueryDateTimeFormat,
            autoclose: true,
            weekStart: 1
        };
        var dateSettings = $.extend(true, {}, defaultSettings, extraSettings);
        $('.datetimepicker').datetimepicker(dateSettings);
        $('body').on('click', '.fg-datetimepicker-icon', function() {
            $(this).siblings('.datetimepicker').datetimepicker('show');
        });
    },

    //Lang Switch
    handleLangSwitch: function(){
        $(document).off('click', 'button[data-elem-function=switch_lang]');
            /* function to show data in different languages on switching language */
        $(document).on('click', 'button[data-elem-function=switch_lang]', function () {
            selectedLang = $(this).attr('data-selected-lang');
            $('.btlang').removeClass('active');
            $(this).addClass('active');
            FgUtility.showTranslation(selectedLang);
        });
    },
    characterCount : function(obj,maxLength,targetcounter){

            var textLength1 =  obj.val().length;
            var remain = parseInt(maxLength - textLength1 );
            targetcounter.html(remain+' ' +jstranslations.chars);
        $(document).on('keydown keyup paste  propertychange DOMAttrModified ', obj, function (e) {
         obj.attr('maxlength',maxLength);
            var textLength =  obj.val().length;
        if (textLength >= maxLength+1) {
               obj.val(obj.val().substring(0, maxLength));
           }
        var textLength1 =  obj.val().length;
        var remain = parseInt(maxLength - textLength1 );
        targetcounter.html(remain+' ' +jstranslations.chars);

      });

    },
    // function to appended https:// in the url field ( use class "fg-urlmask")
    handleInputmask: function () {
        $(".numbermask").inputmask("decimal", {
            rightAlign: false,
            placeholder: "",
            digits: 2,
            radixPoint: FgLocaleSettingsData.decimalMark,
            autoGroup: true,
            allowPlus: false,
            allowMinus: false,
            clearMaskOnLostFocus: true,
            removeMaskOnSubmit: true,
            onUnMask: function (maskedValue, unmaskedValue) {
                var x = unmaskedValue.split(',');
                if (x.length != 2)
                    return "0.00";
                return x[0].replace(/\./g, '') + '.' + x[1];
            }
        });
        $(document).on('blur', ".fg-urlmask", function () {
            appendHttp(this);
        });
        $(document).on('keypress', ".fg-urlmask", function (e) {
            if (e.which == 13) {
                appendHttp(this);
            }
        });
        appendHttp = function (_this) {
            inputVal = $(_this).val();
            if (inputVal != "") {
                var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
                if (!regexp.test(inputVal)) {
                    var indx = inputVal.indexOf("://");
                    if (indx < 0) {
                        returnUrl = "http://" + inputVal.substring(indx);
                    }
                    else {
                        returnUrl = "http" + inputVal.substring(indx);
                    }
                    $(_this).val(returnUrl);
                }
            }
        };
    },

}
//Handling Iframe in cms(website and page-content edit page) 
//To make facebook like-box to adaptive width #FAIR-2700
FgIframeSettings = {
    loadIframe :function(el) {
        if($(el).attr('data-load') !== '1'){
            var width = $(el).parent('.fg-iframe-parent').outerWidth();
            var url = $(el).attr('data-url');
            url = url.replace('width=', '');
            var newUrl = url + (url.indexOf('?') !== -1 ? "&width="+width : "?width="+width);
            $(el).attr('src', newUrl);
            $(el).attr('data-load', '1');
        }
    }
};
