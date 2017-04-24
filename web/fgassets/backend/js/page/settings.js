FgSettings = {
    initPage: function(){
        FgMoreMenu.initServerSide('paneltab');
    }
}
FgLanguageSettings = {
    initLanguage:function(){
        FgLanguageSettings.getLanguages();
        FgLanguageSettings.handleDefaultLangTitle();
        FgLanguageSettings.handleSave();
        FgLanguageSettings.handleDeleteClick();
    },
    getLanguages:function(){
        $('div[data-list-wrap]').rowList({
        template: '#settingsLanguagesListWrap',
        jsondataUrl: pathGetContent,
        fieldSort: '.sortables',
        submit: ['#save_change', 'clubSettingsTab'],
        deleteBtn:'.closeico',
        reset: '#reset_changes',
        useDirtyFields:true,
        postURL:saveURL,
        dirtyFieldsConfig:{
            enableDragDrop : false, 
            enableDiscardChanges: false, 
            initCompleteCallback: function($object){
                    if($('#personalLanguage option[selected]').length>0){
                        $('#personalLanguage').val($('#personalLanguage option[selected]').attr('value'));
                    } else {
                        $('#personalLanguage').val('default');
                    }
                    $('.bss-select').select2('destroy');
                    $('.bss-select').select2();
                    FgFormTools.handleBootstrapSelect();
                    FgFormTools.handleSelect2();
                },
            enableUpdateSortOrder : false
        },
        rowCallback : function(data){
            FgFormTools.handleBootstrapSelect();
            FgFormTools.handleSelect2();
            $('div[data-list-wrap]').find('.new-row:last input.sort-val').attr('value',parseInt($('div[data-list-wrap] .row:nth-last-child(2)').find('.sort-val').val())+1);
        },
        addData: ['.addField', {
            isAllActive: false,
            isNew: true,
        }],
        loadTemplate:[{
            btn:'.addField',
            template:'#settingsLanguagesListWrap'
        }],
        validate: true,
        // postURL: saveAction,
        success: function() {
            alert('Posting Data');
        },
        load: function(data) {    
           FgUtility.changeColorOnDelete();
        }
    });
    },
    handleDefaultLangTitle:function(){
        $('body').on('change','.fg-lang-selecter',function(){
            valSelected=$(this).val();
            $(this).parents('.row.new-row').attr('data-corr-lang', valSelected);
            var t=$(this).parents('.row.new-row').find('.fg-default-select option[data-thou]');
            var d=$(this).parents('.row.new-row').find('.fg-default-select option[data-decim]');
            t.html(t.attr('data-thou')+' '+$(this).find('option[value='+valSelected+']').html()+" ("+$(this).find('option[value='+valSelected+']').attr('data-thounand')+")");
            d.html(d.attr('data-decim')+' '+$(this).find('option[value='+valSelected+']').html()+" ("+$(this).find('option[value='+valSelected+']').attr('data-decimal')+")");
            $(this).parents('.row.new-row').find('.fg-default-select').selectpicker("render");
        });
    },
    handlePostData:function(){
        if($('#save_changes').hasClass('disabled'))
        return;
        $('#save_changes').addClass('disabled');    
        var objectGraph = FgParseFormField.fieldParse(),
        stringifyData = JSON.stringify(objectGraph); 
        FgXmlHttp.post(saveURL, {
            saveData: stringifyData
        }, false, function () {
//            var valS=$('select.bss-select').val();
//            $('select.bss-select option').removeAttr('selected');
//            $('select.bss-select option[value='+valS+']').attr('selected','selected');
//            customFunctions.getData();
            
            FgDirtyFields.removeAllDirtyInstances();
            window.location.reload();
            FgPageTitlebar.setMoreTab();
        });
    },
    handleSave:function(){
        $('#save_changes').click(function() {
            if(FgLanguageSettings.validateLangUnique()){
               return false;
            }
            if(!$('#save_changes').is(':disabled')){
                if($('input[data-deletebtm]:checked').length>0){
                    $('#save_changes').attr("data-toggle","confirmation");
                    $('#save_changes').parent().removeClass("fg-confirm-btn").addClass("fg-confirm-btn");
                    FgConfirmation.confirm(confirmMsg,cancelLabel,saveLabel,$('#save_changes'), FgLanguageSettings.handleSavePopUp);
                } else {
                    FgLanguageSettings.handleSavePopUp();
                }
            }
        });
        $('#save[data-function=save]').click(function() {
            $('#popup').modal('hide');
            FgLanguageSettings.handlePostData();
        });
    },
    handleSavePopUp:function(){
        if($('input[data-active-lang].fairgatedirty').length){
           $.getJSON(saveValidateURl, function (data) {
               var langCount=0;
                $('input[data-active-lang].fairgatedirty').each(function(index,e){
                    var lang=$(e).attr('data-active-lang');
                    if(typeof data[lang] != typeof undefined && ($(e).is(':not(:checked)') ||$(e).parent().find('[data-deletebtm]').is(':checked')) ){
                        langCount++;
                    }
                });
                if(langCount){
                    $('#popup').find('[data-display]').addClass('hide');
                    var langToHide =(langCount==1) ? 'single':'multi';
                    $('#popup').find('[data-display='+langToHide+']').removeClass('hide');
                    $('#popup').modal('show');
                } else {
                    FgLanguageSettings.handlePostData();
                }
            }); 
        } else {
            FgLanguageSettings.handlePostData();
        }
        
    },
    handleDeleteClick:function(){
        $('body').on('click','input[data-deletebtm]',function(){
            if($('input[data-deletebtm]:checked').length==$('input[data-deletebtm]').length-1){
                //$(this).prop('checked', false);
                $('input[data-deletebtm]:not(:checked)').parents('.deletediv').find('div[data-lock]').removeClass('hide');
                $('input[data-deletebtm]:not(:checked)').parents('.deletediv').find('div[data-unlock]').addClass('hide');
                FgFormTools.handleUniform();
            } else {
                $('input[data-deletebtm]').parents('.deletediv').find('div[data-lock]').addClass('hide');
                $('input[data-deletebtm]').parents('.deletediv').find('div[data-unlock]').removeClass('hide');
            }
        });
    },
    getSelectedLanguages:function(){
        var langArray = [];
        $('#clubLanguagesWrap .row.fg-border-line:not(.inactiveblock)').each(function(){
            if($(this).attr('data-corr-lang') !== ''){
                langArray.push($(this).attr('data-corr-lang'));
            } else {
                langArray.push($('select#language'+$(this).attr('id')).val());
            }
        });
        return langArray;
    },
    validateLangUnique: function () {
        var selectedLangs = FgLanguageSettings.getSelectedLanguages();
        var langOccurence = _.countBy(selectedLangs, _.identity);
        $('.has-error').removeClass('has-error');
        $('#failcallbackServerSide').addClass('hide');
        var flag = false;
        $('#clubLanguagesWrap .row.new-row').each(function () {
            var selectElement = $('select#language' + $(this).attr('id'));
            var lang = selectElement.val();
            if (langOccurence[lang] > 1) {
                flag = true;
                if (!selectElement.parent().hasClass('has-error')) {
                    selectElement.parent().addClass('has-error');
                }
                $('#failcallbackServerSide').removeClass('hide');
            }
        });
        
        return flag;
    }
    
};

FgAgeLimitsSettings = {
    initAgeLimitsSettings: function(){
        $('#majorityAgeLimit').spinner({step: 1, min: 0, max: 99});
        $('#minorityAgeLimit').spinner({step: 1, min: 0, max: 99});
        FgDirtyFields.init('contactAgeLimits', { enableDiscardChanges: false });

        $('.spinDiv').find('.btn').on("click", function() {
            $(this).parent().parent().find('input').change();
        });

        $('form').on('click', '#reset_changes', function () {
            FgDirtyFields.removeAllDirtyInstances();
            FgDirtyFields.disableSaveDiscardButtons();
            $('#majorityAgeLimit').spinner("value", $('#majorityAgeOriginal').attr("value"));
            $('#minorityAgeLimit').spinner("value", $('#minorityAgeOriginal').attr("value"));
            return false;
        });

        $('form').on('click', '#save_changes', function () {
            FgXmlHttp.post(
                    ageLimitSaveUrl, 
                    $('#contactAgeLimits').serialize(), 
                    '', 
                    FgAgeLimitsSettings.successCallback());
            return false;
        });
    },
    successCallback: function(){
        $('#majorityAgeOriginal').attr('value', $('#majorityAgeLimit').spinner("value"));
        $('#minorityAgeOriginal').attr('value', $('#minorityAgeLimit').spinner("value"));
        FgDirtyFields.init('contactAgeLimits', { enableDiscardChanges: false });
        FgPageTitlebar.setMoreTab();
    }
}

FgTerminologySettings = {
    initTerminologySettings: function(){
        FormValidation.init('form', "saveTerminologyDetails");
    },
    saveChanges: function() {
        var objectGraph = {};
        $("form :input").each(function () {
            if ($(this).hasClass("fairgatedirty")) {
                var inputVal = ''
                inputVal = $(this).val();
                if (typeof $(this).attr('data-key') !== 'undefined') {
                    converttojson(objectGraph, $(this).attr('data-key').split('.'), inputVal);
                }
            }
        });
        var attributes = JSON.stringify(objectGraph);
        FgXmlHttp.post(terminologyPathSave, {'attributes': attributes}, false,FgTerminologySettings.terminologycallback);
    },
     terminologycallback: function(){
        FgPageTitlebar.setMoreTab();
    }
}
function saveTerminologyDetails(){
    FgTerminologySettings.saveChanges();
}


FgSalutationSettings = {
    initSalutationSettings: function(){
        $('.btn-group button.btlang').click(function() {
            var lang = $(this).attr('data-selected-lang');
            FgUtility.showTranslation(lang);
        });
        FormValidation.init('form1', 'saveSalutationDetails');
    },
    saveChanges: function() {
        FgXmlHttp.post(salutationSaveUrl, $('#form1').serialize(),'',FgSalutationSettings.Salutationcallback);
    },
    Salutationcallback: function() {
         FgPageTitlebar.setMoreTab();
    }
}
function saveSalutationDetails(){
    FgSalutationSettings.saveChanges();
}