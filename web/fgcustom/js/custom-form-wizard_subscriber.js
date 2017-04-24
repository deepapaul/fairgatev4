var csvData = [];
var FormWizardSubscriber = function() {


    return {
        //main function to initiate the module
        init: function() {
            if (!jQuery().bootstrapWizard) {
                return;
            }

            function format(state) {
                if (!state.id)
                    return state.text; // optgroup
                return "<img class='flag' src='../../assets/global/img/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
            }

            var displayConfirm = function() {
                $('#tab4 .form-control-static').each(function() {
                    var form = 'form-tab4';
                    var input = $('[name="' + $(this).attr("data-display") + '"]', form);
                    if (input.is(":radio")) {
                        input = $('[name="' + $(this).attr("data-display") + '"]:checked', form);
                    }
                    if (input.is(":text") || input.is("textarea")) {
                        $(this).html(input.val());
                    } else if (input.is("select")) {
                        $(this).html(input.find('option:selected').text());
                    } else if (input.is(":radio") && input.is(":checked")) {
                        $(this).html(input.attr("data-title"));
                    } else if ($(this).attr("data-display") == 'payment') {
                        var payment = [];
                        $('[name="payment[]"]').each(function() {
                            payment.push($(this).attr('data-title'));
                        });
                        $(this).html(payment.join("<br>"));
                    }
                });
            }

            var handleTitle = function(tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                // set wizard title
                $('.step-title', $('#form_wizard_1')).text(importStep + (index + 1) + importOf + total);
                // set done steps
                jQuery('li', $('#form_wizard_1')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }

                if (current == 1) {
                    $('#form_wizard_1').find('.button-previous').hide();
                    $('div[data-sample]').show();
                    $('div[data-sample-assignment]').hide();
                } else if(current == 5) {
                    $('div[data-sample-assignment]').show();
                } else {
                    $('#form_wizard_1').find('.button-previous').show();
                    $('div[data-sample]').hide();
                    $('div[data-sample-assignment]').hide();
                }

                if (current >= total) {
                    $('#form_wizard_1').find('.button-next').hide();
                    $('#form_wizard_1').find('.button-submit').show();
                    displayConfirm();
                } else {
                    $('#form_wizard_1').find('.button-next').show();
                    $('#form_wizard_1').find('.button-submit').hide();
                }
                Metronic.scrollTo($('.page-title'));
            }

            // default form wizard
            $('#form_wizard_1').bootstrapWizard({
                'nextSelector': '',
                'previousSelector': '.button-previous',
                onTabClick: function(tab, navigation, index, clickedIndex) {
                    if (index > clickedIndex) {
                        handleTitle(tab, navigation, clickedIndex);
                    }
                    else {
                        return false;
                    }
                },
                onNext: function(tab, navigation, index) {
                    handleTitle(tab, navigation, index);
                },
                onPrevious: function(tab, navigation, index) {
                    handleTitle(tab, navigation, index);
                },
                onTabShow: function(tab, navigation, index) {
                    var total = navigation.find('li').length;
                    var current = index + 1;
                    var $percent = (current / total) * 100;
                    $('#form_wizard_1').find('.progress-bar').css({
                        width: $percent + '%'
                    });
                }
            });

            $('#form_wizard_1').find('.button-previous').hide();
            $('#form_wizard_1 .button-submit').click(function() {
                var index = $('#form_wizard_1').bootstrapWizard('currentIndex');
                var form = 'body #form-tab' + (index + 1);
                var paramobj = {'url': $(form).attr('data-url'), 'form': $(form)};
                FgXmlHttp.formPost(paramobj);
            }).hide();
            $('#form_wizard_1 .button-next').click(function() {
                var index = $('#form_wizard_1').bootstrapWizard('currentIndex');
                var form = 'body #form-tab' + (index + 1);
                var replaceDiv = 'body #tab' + (index + 2);
                var showNext = true;
                if (index == 0) {
                    $('p[data-required]').closest('.form-group').removeClass('has-error');
                    $('p[data-required]').addClass('display-none');
                }
                else if (index == 1) {
                    $('#tab2 td.has-error').removeClass('has-error');
                    var mandatory = $('#assign-data-fields-selection-subscriber').attr('data-mandatory');
                    var mandatoryArray = mandatory.split(":");
                    var mandatory = ':' + $('#assign-data-fields-selection-subscriber').attr('data-mandatory') + ':';
                    var manCount = 0;
                    var mapFields = ':';
                    var duplicateMapping = false;
                    var manFiledError=dataAssignments.handleMandetory();
                    var isUnmapedFields=false;
                    var unmapedFieldsList='';
                    

                    $('#tab2 select').each(function() {
                        if ($(this).closest('tr').find('input[data-inactiveblock=changecolor]').is(':unchecked')) {
                            if ((mapFields.indexOf(':' + $(this).val() + ':') > -1) && ($(this).val() !== '')) {
                                duplicateMapping = true;
                                var selectVal=$(this).val();
                                $('#tab2 select').each(function() {
                                    if($(this).val()===selectVal){
                                        $(this).closest('td').addClass('has-error');
                                    }
                                });
                            }
                            else if($(this).val() === '') {
                                isUnmapedFields=true;
                                unmapedFieldsList += (unmapedFieldsList==='') ? $(this).closest('tr').find('td:first').text().trim(): ', '+$(this).closest('tr').find('td:first').text().trim();
                            }
                            mapFields += $(this).val() + ':';
                            if (mandatory.indexOf(':' + $(this).val() + ':') > -1) {
                                manCount++;
                                manFiledError[$(this).val()]='';
                            }
                        }
                    });
                    if ((manCount < mandatoryArray.length) || (duplicateMapping === true) || (isUnmapedFields===true)) {
                        $('#tab' + (index + 1) + ' .alert-danger').show();
                        var mandatoryErrorField='';
                        var spliter=': ';
                        for (var i in manFiledError) {
                           if(manFiledError[i] != ''){
                            mandatoryErrorField += spliter+manFiledError[i].trim();
                            spliter=", ";
                           }
                        }
                        var  errorMessage='';
                        errorMessage = duplicateMapping ? errorMessage+mappingError+'<br>' : errorMessage;
                        errorMessage  = (manCount < mandatoryArray.length) ? errorMessage+mandatoryError+mandatoryErrorField+'.'+'<br>':errorMessage;
                        errorMessage = (isUnmapedFields===true) ? errorMessage+columnSkippError+unmapedFieldsList+'<br>' : errorMessage;
                        $('#tab' + (index + 1) + ' .alert-danger span[data-error]').html(errorMessage);
                        Metronic.scrollTo($('#tab' + (index + 1) + ' .alert-danger'), -200);
                        return false;
                    }
                    else {
                        $('#tab' + (index + 1) + ' .alert-danger').hide();
                    }
                } else if (index == 2) {
                    var dataCorrected = $(form).attr('data-corrected');
                    if (dataCorrected == 0) {
                        replaceDiv = 'body #tab' + (index + 1);
                        showNext = false;
                    }
                }
                var paramobj = {'url': $(form).attr('data-url'), 'form': $(form), 'replacediv': replaceDiv, 'successCallback': callbackfn, 'successParam': {'index': index, 'showNext': showNext}};
                FgXmlHttp.formPost(paramobj);

            });
            function callbackfn(responce) {
                $('#tab' + (responce.index + 1) + ' .alert-danger').hide();
                if (responce.responseText.status) {
                    if (responce.responseText.status == 'ERROR' && responce.index == 0) {
                        $('p[data-required]').closest('.form-group').addClass('has-error');
                        $('p[data-required]').removeClass('display-none');
                        $('#tab' + (responce.index + 1) + ' .alert-danger span[data-error]').html(responce.responseText.message);
                        $('#tab' + (responce.index + 1) + ' .alert-danger').show();
                        Metronic.scrollTo($('#tab' + (responce.index + 1) + ' .alert-danger'), -200);
                    }
                }
                else {
                    var index = $('#form_wizard_1').bootstrapWizard('currentIndex');
                    if (index == 0) {
                        var csvRows = $('#tab' + (index + 2)).find('#assign-data-fields-selection-subscriber').attr('data-rows');
                        csvData = JSON.parse(csvRows);
                        initialLimit = csvData[0].length;
                        limit = initialLimit;
                        var template = $('#assign-data-fields-selection-subscriber').html();
                        var result_data = _.template(template, {data: {'csvData': csvData, 'offset': 0, 'limit': initialLimit}});
                        $('#tab2 table tbody').html(result_data);
                        if (csvData[0].length <= 20) {
                            $('div[data-addMore] a').hide();
                        }
                        else if(csvData[0].length < 25){
                            $('div[data-addMore] a .fg-add-text').html(csvData[0].length-20+' more columns');
                        }
                        $('.bs-select').selectpicker();
                        FgFormTools.handleUniform();
                    }else if (index == 2){
                        FgFormTools.handleUniform();
                    }
                    
                    if (responce.showNext) {
                        $('#form_wizard_1').bootstrapWizard('next');
                    }
                }
            }
        }
    };

}();

