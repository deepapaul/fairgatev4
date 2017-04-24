var filterCondition = FgFilterSettings.FgSponsorCondition;
$(function() {

    /* Get json data for populating Filter */
    $.getJSON(pathFilterData, {
        'hasSponsorCriteria' : true
    }, function(jsonFilterData) {
        filterData = jsonFilterData;

        $('div[data-list-wrap]').rowList({
            template: '#recipientsList',
            jsondataUrl: pathRecipients,
            fieldSort: '.sortables',
            submit: ['#save_changes', 'receiverslist'],
            reset: '#reset_changes',
            searchfilterData: filterData,
                useDirtyFields: true,
                dirtyFieldsConfig: { enableDiscardChanges : false, enableDragDrop: false, enableUpdateSortOrder: false },
            addData: ['#addrow', {
                isAllActive: false,
                isNew: true
            }, 'filter'],
            loadTemplate:[{
                btn:'#addrow',
                template:'#addRecipientRow'
            }],
            validate: true,
            postURL: saveAction,
            validateFilterCriteria: true,
            success: function() {
                alert('Posting Data');
            },
            load: function() {
                //console.log(_dynamicFunction);
            },
            initCallback: function() {
                recipientList.validateFilterAndEmailSelection();
            }
        });
    });

});

/* Functions that should be executed after loading page content */
var initPageFunctions = function() {  
    _dynamicFunction.updateNow = function(elem) {
        var id = elem.id;
        var updatePath = pathUpdateNow.replace('recipientId', id);
        FgXmlHttp.post(updatePath, {}, false, function() {
            customFunctions.getData();
            //location.reload(true);
        });
    }
    _dynamicFunction.openExceptions = function(elem) {
        $('input[data-autocomplete-id='+elem.id+']').each(function() {
            var dataType = $(this).attr('data-type');
            var hiddenFieldId = $(this).attr('data-hidden-field-id');
            var selectedData = ((jsonTokenData[elem.id] != 'undefined') && (jsonTokenData[elem.id] != undefined)) ? jsonTokenData[elem.id][dataType] : [];

            $(this).fbautocomplete({
                url: autoCompletePath,
                params: {'isCompany': 2},
                removeButtonTitle: removestring,
                selected: selectedData,
                formName: elem.id,
                maxItems: 50,
                useCache: false,
                onItemSelected: function($obj, itemId, selected) { 
                    valueUpdate(elem.id, 'insert', dataType, itemId, hiddenFieldId);
                },
                onItemRemoved: function($obj, itemId) {
                    valueUpdate(elem.id, 'remove', dataType, itemId, hiddenFieldId);
                },
                onAlreadySelected: function($obj) {
                }
            });
        });                    
    }
    valueUpdate = function(elemId, action, dataType, itemId, hiddenFieldId) {
        if (action == 'insert') {
            exceptionsData[elemId][dataType].push(itemId);
        } else if (action == 'remove') { 
            exceptionsData[elemId][dataType] = _.without(exceptionsData[elemId][dataType], itemId);
        }
        $('#' + hiddenFieldId).val(exceptionsData[elemId][dataType].join(','));
		FgDirtyFields.updateFormState();
      //  FgDirtyForm.checkForm('receiverslist');
    };

    if (exceptionContactIds != "") {
        $.getJSON(pathExceptionConts, {'contactIds': exceptionContactIds}, function(contactNames){
            $.each(exceptionsData, function(rlId, exceptionData) {
                jsonTokenData[rlId] = {};
                if (exceptionData['included_contacts'].length) {
                    var tokenData = [];
                    $.each(exceptionData['included_contacts'], function(key, includedCont) {
                        var contName = contactNames[includedCont];
                        tokenData.push({'id': includedCont, 'value': contName, 'label': contName, 'title': contName});
                    });
                    jsonTokenData[rlId]['included_contacts'] = tokenData;
                }
                if (exceptionData['excluded_contacts'].length) {
                    var tokenData = [];
                    $.each(exceptionData['excluded_contacts'], function(key, excludedCont) {
                        var contName = contactNames[excludedCont];
                        tokenData.push({'id': excludedCont, 'value': contName, 'label': contName, 'title': contName});
                    });
                    jsonTokenData[rlId]['excluded_contacts'] = tokenData;
                }
            });
			FgDirtyFields.updateFormState();
           // FgDirtyForm.rescan('receiverslist');
        });
    }  
};
var recipientList = {
    //function to validate filetr criteria and email selection in recipient list
    validateFilterAndEmailSelection: function() {
        $('.recipient-list-rows').each(function() {
            var id = $(this).attr('id');
            var filterdata = $(this).find('.recipient-list-filters').val();
            recipientList.showAlertIconOnInvalidFilter(id, filterdata);

            var mainEmailIds = $(this).find('.recipient-list-email-fields').val();
            if ((mainEmailIds == null) || (mainEmailIds == '')) {
                $('a[data-fn=emailerField][data-id='+id+']').parent().find('.fg-broken-filter').removeClass('hide');
            } else {
                $('a[data-fn=emailerField][data-id='+id+']').parent().find('.fg-broken-filter').addClass('hide');
            }
        });
    },
    //function to show alert icon if filter criteria is invalid in recipient list
    showAlertIconOnInvalidFilter: function(id, data) {
        var filterJsonData = $.parseJSON(data);
        var isValid = false;
        if (filterJsonData !== null) {
            //validate filter criteria
            isValid = FgValidateFilter.init(filterData, filterJsonData, 'contact');
        }
        if (!isValid) {
            $('a[data-fn=filter][data-id='+id+']').parent().find('.fg-broken-filter').removeClass('hide');
        } else {
            $('a[data-fn=filter][data-id='+id+']').parent().find('.fg-broken-filter').addClass('hide');
        }
    }
}