FgLocaleSettings = {
    formatDate: function (value, formatType, currentFormat) {
        try {
            var momentObj = moment(value, currentFormat);
            if (momentObj.isValid()) {
                switch (formatType) {
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
            }
            else {
                console.log('Error: Invalid Format');
                return value;
            }
        }
        catch (err) {
            console.log('Exception: Invalid Format');
            console.log(err);
            return value;
        }
    },
};
FgClubSettings = {
    currency: FgLocaleSettingsData.currency,
    currencyPosition: FgLocaleSettingsData.currencyPosition,
    getAmountWithCurrency: function (amount, noParse) {
        if (!noParse) {
            amount = parseFloat(amount);
            amount = amount.toFixed(2);
        }
        decimalMark = $("<p/>").html(FgLocaleSettingsData.decimalMark).text();
        thousendSeperator = $("<p/>").html(FgLocaleSettingsData.thousendSeperator).text();
        amount = accounting.formatMoney(amount, "", 2, thousendSeperator, decimalMark);
        var currencyAmount = '';
        if (this.currencyPosition == 'right') {
            currencyAmount = amount + ' ' + this.currency;
        }
        else {
            currencyAmount = this.currency + ' ' + amount;
        }
        return currencyAmount;
    },
    formatNumber: function (numb, noParse) {
        decimalMark = $("<p/>").html(FgLocaleSettingsData.decimalMark).text();
        thousendSeperator = $("<p/>").html(FgLocaleSettingsData.thousendSeperator).text();
        if (numb == '-' || numb == '') {
            return numb;
        }
        if (!noParse) {
            numb = parseFloat(numb);
            numb = numb.toFixed(2);
        }
        if (numb % 1 == 0) {
            return accounting.formatMoney(numb, "", 0, thousendSeperator, '');
        }
        else {
            return numb = accounting.formatMoney(numb, "", 2, thousendSeperator, decimalMark);
        }
    },
    formatDecimalMark: function (numb, noParse) {
        decimalMark = $("<p/>").html(FgLocaleSettingsData.decimalMark).text();
        if (!noParse) {
            numb = parseFloat(numb);
            numb = numb.toFixed(2);
        }
        if (numb % 1 != 0) {
            numb = accounting.formatMoney(numb, "", 2, '', decimalMark);
        }
        return numb;
    },
    unFormatNumber: function (numb) {
        decimalMark = $("<p/>").html(FgLocaleSettingsData.decimalMark).text();
        numb = numb.replace(decimalMark, '.');
        return numb;
    }
};
FgMapSettings = {
    mapShow: function (lat, lng, loc) {
        var myCenter = new google.maps.LatLng(lat, lng);
        var mapProp = {
            center: myCenter,
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("googleMap"), mapProp);
        var marker = new google.maps.Marker({
            position: myCenter,
        });
        var infoString = '<div class="fg-map-info">' + loc + '</div>';
        var infowindow = new google.maps.InfoWindow({
            content: infoString
        });
        if (loc != '') {
            marker.setMap(map);
            infowindow.open(map, marker);
        }
        google.maps.event.addListenerOnce(map, 'idle', function () {
            FgMapSettings.mapInfoWindowResize();
        });
    },
    mapResize: function (lat, lng, loc) {
        var myCenter = new google.maps.LatLng(lat, lng);
        var mapProp = {
            center: myCenter,
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var center = map.getCenter();
        google.maps.event.trigger(map, "resize");
        map.setCenter(center);
    },
    mapInfoWindowResize: function () {
        if ($(window).width() < 500) {
            $('.fg-map-info').parents().eq(3).addClass('fg-map-info-parent');
        }
        else {
            $('.fg-map-info').parents().eq(3).removeClass('fg-map-info-parent');
        }
    },
    mapAutoComplete: function () {
        var input = (document.getElementById('locAutoComp'));
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            $('#mapLat').val(place.geometry.location.lat());
            $('#mapLng').val(place.geometry.location.lng());
            if (!place.geometry) {
                $('#locAutoComp').val();
            }
        });
    }
};
FgGlobalSettings = {
    handleDateTimepicker: function (extraSettings) {
        var defaultSettings = {
            language: jstranslations.localeName,
            format: FgLocaleSettingsData.jqueryDateTimeFormat,
            autoclose: true,
            weekStart: 1
        };
        var dateSettings = $.extend(true, {}, defaultSettings, extraSettings);
        $('.datetimepicker').datetimepicker(dateSettings);
        $('body').on('click', '.fg-datetimepicker-icon', function () {
            $(this).siblings('.datetimepicker').datetimepicker('show');
        });
    },
    handleLangSwitch: function () {
        $(document).off('click', 'button[data-elem-function=switch_lang]');
        $(document).on('click', 'button[data-elem-function=switch_lang]', function () {
            selectedLang = $(this).attr('data-selected-lang');
            $('.btlang').removeClass('active');
            $(this).addClass('active');
            FgUtility.showTranslation(selectedLang);
        });
    },
};
//# sourceMappingURL=fg_global.js.map