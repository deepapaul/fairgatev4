/// <reference path="../directives/jquery.d.ts" />
class Fgplusminus {
    
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
                       let fieldName :any= $(this).attr('data-field');
                        let type:any      = $(this).attr('data-type');
                        let input:any = $("input[name='"+fieldName+"']");
                       let currentVal:any = parseInt(input.val());
                        if (!isNaN(currentVal)) {
                            if(type == 'minus') {

                                if(currentVal > input.attr('min')) {
                                    input.val(currentVal - 1).change();
                                } 
                                if(parseInt(input.val()) == input.attr('min')) {
                                    $(this).attr('disabled', true);
                                }

                            } else if(type == 'plus') {

                                if(currentVal < input.attr('max')) {
                                    input.val(currentVal + 1).change();
                                }
                                if(parseInt(input.val()) == input.attr('max')) {
                                    $(this).attr('disabled', true);
                                }

                            }
                        } else {
                            input.val(1);
                        }
            });
            
            

            $('body').on('change','input.input-number',function() {

               let  minValue =  parseInt($(this).attr('min'));
                let maxValue =  parseInt($(this).attr('max'));
                let valueCurrent = parseInt($(this).val());
                name = $(this).attr('name');
                if(valueCurrent >= minValue) {
                    $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
                } else {
                    //alert('Sorry, the minimum value was reached');
                    //$(this).val($(this).data('oldCount'));
                }
                if(valueCurrent <= maxValue) {
                    $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
                } else {
                    //alert('Sorry, the maximum value was reached');
                    //$(this).val($(this).data('oldCount'));
                }


            });
            
               
    }
    
    
}
    
     


