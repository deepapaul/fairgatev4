

jQuery.extend(jQuery.fn.dataTableExt.oSort, {
    "null-last-asc": function(a, b) {
        if (a === '' || a === null) {
            return 1;
        }
        if (b === '' || b === null) {
            return -1;
        }
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
    "null-last-desc": function(a, b) {
        if (a === '' || a === null) {
            return 1;
        }
        if (b === '' || b === null) {
            return -1;
        }
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    },
    "null-numeric-last-asc": function(a, b) {
        if (a === '' || a === null) {
            return 1;
        }
        if (b === '' || b === null) {
            return -1;
        }
        a=parseFloat(a);
        b=parseFloat(b);
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
    "null-numeric-last-desc": function(a, b) {
        if (a === '' || a === null) {
            return 1;
        }
        if (b === '' || b === null) {
            return -1;
        }
        a=parseFloat(a);
        b=parseFloat(b);
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    },
    "hyphen-last-asc": function(a, b) {
        if (a === '-') {
            return 1;
        }
        if (b === '-') {
            return -1;
        }
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
    "hyphen-last-desc": function(a, b) {
        if (a === '-') {
            return 1;
        }
        if (b === '-') {
            return -1;
        }
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    },
//    Used to sort values like '<0.1 MB'
    "less-symbol-asc": function(a, b) {        
        var indx_a = a.indexOf("<"); 
        if (indx_a === 0) {
            a = a.slice(1);
        }
        var indx_b = b.indexOf("<"); 
        if (indx_b === 0) {
            b = b.slice(1);
        }
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
//    Used to sort values like '<0.1 MB'
    "less-symbol-desc": function(a, b) {
        var indx_a = a.indexOf("<"); 
        if (indx_a === 0) {
            a = a.slice(1);
        }
        var indx_b = b.indexOf("<"); 
        if (indx_b === 0) {
            b = b.slice(1);
        }
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
});

(function($) {

    $.fn.dataTable.moment = function(format, locale) {
        var types = $.fn.dataTable.ext.type;

        // Add type detection
        types.detect.unshift(function(d) {
            // Null and empty values are acceptable
            if (d === '' || d === null) {
                return 'moment-' + format;
            }

            return moment(d, format, locale, true).isValid() ?
                    'moment-' + format :
                    null;
        });

        types.order[ 'moment-' + format + '-asc' ] = function(a, b) {

            a = (a === '' || a === '-' || a === null) ? Infinity : parseInt(moment(a, format, locale, true).format('x'), 10);
            b = (b === '' || b === '-' || b === null) ? Infinity : parseInt(moment(b, format, locale, true).format('x'), 10);
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        };
        types.order[ 'moment-' + format + '-desc' ] = function(a, b) {
            a = (a === '' || a === '-' || a === null) ? -Infinity : parseInt(moment(a, format, locale, true).format('x'), 10);
            b = (b === '' || b === '-' || b === null) ? -Infinity : parseInt(moment(b, format, locale, true).format('x'), 10);
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        };
    };
    if(typeof datetimeFormat === 'undefined') {
         var datetimeFormat =  FgLocaleSettingsData.momentDateTimeFormat;
    } 
     $.fn.dataTable.moment(datetimeFormat);
}(jQuery));