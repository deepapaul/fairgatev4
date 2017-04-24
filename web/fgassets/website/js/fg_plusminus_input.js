var Fgplusminus = (function () {
    function Fgplusminus(options) {
        this.options = options;
        this.settings = '';
        this.defaultSettings = {
            'selector': ".selectButton"
        };
    }
    Fgplusminus.prototype.initSettings = function () {
        this.settings = $.extend(true, {}, this.defaultSettings, this.options);
    };
    Fgplusminus.prototype.init = function () {
        this.initSettings();
        $('body').on('click', this.settings.selector, function (e) {
            e.stopImmediatePropagation();
            var fieldName = $(this).attr('data-field');
            var type = $(this).attr('data-type');
            var input = $("input[name='" + fieldName + "']");
            var currentVal = parseInt(input.val());
            if (!isNaN(currentVal)) {
                if (type == 'minus') {
                    if (currentVal > input.attr('min')) {
                        input.val(currentVal - 1).change();
                    }
                    if (parseInt(input.val()) == input.attr('min')) {
                        $(this).attr('disabled', true);
                    }
                }
                else if (type == 'plus') {
                    if (currentVal < input.attr('max')) {
                        input.val(currentVal + 1).change();
                    }
                    if (parseInt(input.val()) == input.attr('max')) {
                        $(this).attr('disabled', true);
                    }
                }
            }
            else {
                input.val(1);
            }
        });
        $('body').on('change', 'input.input-number', function () {
            var minValue = parseInt($(this).attr('min'));
            var maxValue = parseInt($(this).attr('max'));
            var valueCurrent = parseInt($(this).val());
            name = $(this).attr('name');
            if (valueCurrent >= minValue) {
                $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled');
            }
            else {
            }
            if (valueCurrent <= maxValue) {
                $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled');
            }
            else {
            }
        });
    };
    return Fgplusminus;
}());
