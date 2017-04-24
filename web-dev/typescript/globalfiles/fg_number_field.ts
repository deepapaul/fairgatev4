/// <reference path="../directives/jquery.d.ts" />
class FgNumber {
    
    settings:any='';
    defaultSettings:any={
    'selector' : ".selectButton"   
    }
    constructor(public options: any) {
     
    }
    public initSettings() {
        this.settings = $.extend(true, {}, this.defaultSettings, this.options);
    }

    public init() {
        
        this.initSettings();        
        $('body').on('click',this.settings.selector,function(e){
            e.stopImmediatePropagation();
            var fieldName = $(this).attr('data-field');
            var type = $(this).attr('data-type');
            var input = $("input#" + fieldName);
            var step = parseFloat(input.attr('stepto'))+0;
            var minValue = parseFloat(input.attr('min'))+0;
            var maxValue = parseFloat(input.attr('max'))+0;
            var currentVal = parseFloat(input.attr('data-val'))+0;
            if (!isNaN(currentVal)) {
                if (type == 'minus') {
                    newVal = currentVal - step;
                    if (newVal >= minValue) {
                        input.attr('data-val', newVal);
                        newVal = (newVal %1 ===0) ? newVal : FgClubSettings.formatDecimalMark(newVal);
                        input.val(newVal).change();
                    }
                }
                else if (type == 'plus') {
                    newVal = currentVal + step;
                    if (newVal <= maxValue || (isNaN(maxValue)|| maxValue ==0)) {
                        input.attr('data-val', newVal);
                        newVal = (newVal %1 ===0) ? newVal : FgClubSettings.formatDecimalMark(newVal);
                        input.val(newVal).change();
                    }
                }
            }
            else {
                newVal = input.attr('min');
                input.attr('data-val', newVal);
                newVal = (newVal %1 ===0) ? newVal : FgClubSettings.formatDecimalMark(newVal);
                input.val(newVal).change();
            }   
        });
            
            

            $('body').on('change',this.settings.inputNum, function() {
                var minValue = parseFloat($(this).attr('min'))+0;
                var maxValue = parseFloat($(this).attr('max'))+0;
                var step = parseFloat($(this).attr('stepto'))+0;
                var valueCurrent = $(this).val();
                valueCurrent = parseFloat(valueCurrent.replace(',', '.'))+0;
                $(this).attr('data-val', valueCurrent);
                if (valueCurrent < minValue) {
                    $(this).attr('data-val', $(this).attr('min'));
                    minVal = (minValue %1 ===0) ? minValue : FgClubSettings.formatDecimalMark(minValue);
                    $(this).val(minVal);
                }
                if (valueCurrent > maxValue && isNaN(maxValue) && maxValue !=0) {
                    $(this).attr('data-val', $(this).attr('max'));
                    maxVal = (maxValue %1 ===0) ? maxValue : FgClubSettings.formatDecimalMark(maxValue);
                    $(this).val(maxVal);
                }
            });
            
               
    }
    
    
}
    
     


