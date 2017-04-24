$(function() {

    FgUtility.startPageLoading();
    /* Get json data for populating Filter */
    $.getJSON(filterClubDataUrl, {}, function(jsonFilterData) {
        filterData = jsonFilterData;

        $('div[data-list-wrap]').rowList({
            template: '#sponsorSavedFilter',
            jsondataUrl: pageVars.pathFilterData,
            fieldSort: '.sortables',
            submit: ['#save_changes', 'formSponsorFilter'],
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
                template:'#template-add-filter-role'
            }],
            validate: true,
            postURL: pageVars.saveAction,
            success: function() {
                alert('Posting Data');
            },
            
            load: function() {
               
            },
           
        });
    });
    sponsorSavedFilter.calculateContactCount();
});

/* Functions that should be executed after loading page content */
var initPageFunctions = function() {
    FgUtility.stopPageLoading();
    checkForBrokenFilterCriteria();
    
    /* Save Data */
    _dynamicFunctionSuccess = function() {
        var objectGraph = FgParseFormField.fieldParse(),
            stringifyData = JSON.stringify(objectGraph);

        FgXmlHttp.post(pageVars.saveAction, {filterArr: stringifyData, contactType: 'contact'}, false, function(){
            _dynamicFunction.getData();
        });
    }
};
/* Check whether any filter criteria is broken */
var checkForBrokenFilterCriteria = function() {
    $.getJSON(filterClubDataUrl, function(masterJsonData) {
        $('.jsonDatahidden').each(function() {
            var filterJsonData = $.parseJSON($(this).attr('value'));
            var idStringArr = $(this).attr('id').split('_');
            var id = idStringArr[0];
            var isBroken = false;
            isBroken = FgValidateFilter.init(masterJsonData, filterJsonData, 'contact');
            if (!isBroken) {
                $('a[data-target="#data-'+ id +'"]').after('<i class="fa fa-warning fg-warning fg-broken-filter"></i>');
            }
        });
    });
};
var sponsorSavedFilter = {
    /* Function to calculate no. of contacts in a filter */
    calculateContactCount: function() {
        $('#formSponsorFilter').off('click', '.filterCount');
        $('#formSponsorFilter').on('click', '.filterCount', function() {
            var status = $(this).attr('status')
            var filter_id = $(this).attr('filter_id');
            if (status == 'calculate') {
                var replacediv = '.replaceFilterClass' + $(this).attr('filter_id');
                var url = $(this).attr('url');
                $(this).attr('status','contactfilter');
                $.post(url, {filterId: filter_id}, function(data) { 
                    if (data == '-1') {
                        $(replacediv).html('<i class="fa fa-warning fg-warning"></i>'); 
                    } else if (data == 1) {
                        $(replacediv).html(data + ' ' + pageVars.contact);
                    } else {
                        $(replacediv).html(data + ' ' + pageVars.contacts);  
                    }
                });
                return false;
            } else {
                handleCountOrSidebarClick.updateFilter('FILTER', 'filterdisplayflag_contact'+pageVars.clubId+'-'+pageVars.contactId, '', pageVars.clubId, pageVars.contactId, '', '', pageVars.clubUrlIdentifier, 'count', '', '', '', '', 'contact', '', $('#'+filter_id+'_jsonData').val(), filter_id, '');
            }
        });
    }
};
