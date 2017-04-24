$(function() {
    var Cdata;
    $(document).ready(function() {
        $.getJSON(templatePath, function(data) {
            Cdata = data;
            renderNewRow('newsletterDesign', 'data-list-wrap-design', Cdata);
        });
    });

    $('body').on('keyup', '#EmailSelection', function() {
        var vals = $('#EmailSelection').val();
        if (!vals) {
            $('#fg-dev-sendmail').addClass('disabled');
        } else {
            $('#fg-dev-sendmail').removeClass('disabled');
        }
    });
    
    function enableDisable(templateId) {
        var vals = $('#hiddenContacts').val();
        var val1 = $('#EmailSelection').val();
        if ((templateId != 0) || (Cdata.pageType == "simplemail")) {
            $('#step5Preview').removeClass('hide');
            if (val1 != '') {
                $('#fg-dev-sendmail').removeClass('disabled');
            }
            if (vals != '') {
                $('#fg-dev-sendmails').removeClass('disabled');
            }
        } else {
            $('#step5Preview').addClass('hide');
            $('#fg-dev-sendmail').addClass('disabled');
            $('#fg-dev-sendmails').addClass('disabled');
        }
    }
    
    function renderNewRow(templateScriptId, parentDivId, Cdata) {
        var template = $('#' + templateScriptId).html();
        var result_data = _.template(template, {Cdata: Cdata});
        $('#' + parentDivId).append(result_data);
        $('#form_wizard_1').find('.button-save').hide();
        autocomplete(Cdata.defaultContact, Cdata.defaultContactId);
        var templateId = (Cdata.pageType == "newsletter") ? Cdata.selectedTemplate : 0;
        enableDisable(templateId);
        document.getElementById('previewFrame').setAttribute('src', previewPath);
        $('#previewFrame').load(function() {
            autoResize('previewFrame');
        });
    }
    
    function autoResize(id) {
        var newheight;
        if (document.getElementById) {
            newheight = document.getElementById(id).contentWindow.document.body.scrollHeight;
        }
        $("#previewFrame").height(newheight);
    }

    $('body').on('click', '#fg-dev-sendmails', function() {
        var ids = $('#hiddenContacts').val();
        var newsletterId = Cdata.newsletterId;
        if (Cdata.pageType == "newsletter") {
            var templateId = Cdata.selectedTemplate;
            if ((ids != '') && (templateId != null) && (newsletterId != null)) {
                FgXmlHttp.post(sendMailPath, {'newsletter': newsletterId, 'template': templateId, 'ids': ids, 'passed': 'ids', 'type': Cdata.pageType}, false, false, failCallbackFunctions, '0');
            }
        } else {
            if ((ids) && (newsletterId)) {
                FgXmlHttp.post(sendMailPath, {'newsletter': newsletterId, 'template': templateId, 'ids': ids, 'passed': 'ids', 'type': Cdata.pageType}, false, false, failCallbackFunctions, '0');
            }
        }

    });
    
    $('body').on('click', '#fg-dev-sendmail', function() {
        var emails = $('#EmailSelection').val();
        var newsletterId = Cdata.newsletterId;
        if (Cdata.pageType == "newsletter") {
            var templateId = Cdata.selectedTemplate;
            if (emails != '' && templateId != null && newsletterId != null) {
                FgXmlHttp.post(sendMailPath, {'newsletter': newsletterId, 'template': templateId, 'emails': emails, 'passed': 'emails', 'type': Cdata.pageType}, false, false, failCallbackFunctions, '0');
            }
        } else {
            if (emails != '' && newsletterId != null) {
                FgXmlHttp.post(sendMailPath, {'newsletter': newsletterId, 'template': templateId, 'emails': emails, 'passed': 'emails', 'type': Cdata.pageType}, false, false, failCallbackFunctions, '0');
            }
        }
    });
    
    $('body').on('click', '#save_nd_continue,#save', function() {
        window.location.href = sendingPath;
    });

    function failCallbackFunctions(data) {
        $('#failcallbackServerSide').hide();
        if (data.status == "ERROR") {
            $('#failcallbackServerSide span').text(data.errorMsg);
            $('#failcallbackServerSide').show();
        }
    }
    
    function autocomplete(contact, id) {
        var oldValue = $('#hiddenContacts').val(id);
        $('#contactSelection').fbautocomplete({
            url: contactNamePath, // which url will provide json!
            maxItems: 5, // only one item can be selected
            params: {'isCompany': 2} , 
            // do not use caching, always calls server even for something you have already typed. 
            // Probably you want to leave this on true
            useCache: false,
            selected: [{id: id, title: contact}],
            onItemSelected: function($obj, itemId, selected) {
                oldValue = $('#hiddenContacts').val();
                if (oldValue != '') {
                    $('#hiddenContacts').val(oldValue + "," + itemId);
                } else {
                    $('#hiddenContacts').val(itemId);
                }
                $('#fg-dev-sendmails').removeClass('disabled');
            },
            onItemRemoved: function($obj, itemId) {
                var vals = $('#hiddenContacts').val();
                var idarray = vals.split(',');
                for (i = 0; i < idarray.length; i++) {
                    if (idarray[i] === itemId) {
                        idarray.splice(i, 1);
                        var newval = idarray.join(',');
                        $('#hiddenContacts').val(newval);
                    }
                }
                if (idarray.length < 1) {
                    $('#fg-dev-sendmails').addClass('disabled');
                }
            },
            onAlreadySelected: function($obj) {
            }
        });
    }
});


