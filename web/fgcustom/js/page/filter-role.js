$(function() {

    FgUtility.startPageLoading();
    /* Get json data for populating Filter */
    $.getJSON(pathFilterData, {
        'getFilterRole': false
    }, function(jsonFilterData) {
        filterData = jsonFilterData;

        $('div[data-list-wrap]').rowList({
            template: '#filterRoles',
            jsondataUrl: pathFilterRoles,
            fieldSort: '.sortables',
            submit: ['#save_changes', 'filterrolecategorysettings'],
            reset: '#reset_changes',
            searchfilterData: filterData,
            addData: ['#addrow', {
                isAllActive: false,
                isNew: true
            }, 'filter'],
            loadTemplate:[{
                btn:'#addrow',
                template:'#template-add-filter-role'
            }],
            validate: true,
            postURL: saveAction,
            success: function() {
                alert('Posting Data');
            },
            load: function() {
                //console.log(_dynamicFunction);
            },
            triggerFunction: ['displayTranslation'],
            rowCallback: function(elem) {
                addRowCallback(elem);
                FgTooltip.init();
            },
            initCallback: function (response) {
                FgPageTitlebar.checkMissingTranslation(defaultLang);
            },
            onSuccessCallback:function (response) {
                FgPageTitlebar.checkMissingTranslation(defaultLang);
                FgUtility.showTranslation(defaultLang);
            }
        });
    });
    $('form').off('click', 'button[data-elem-function=switch_lang]');
    $('form').on('click', 'button[data-elem-function=switch_lang]', function() {
        selectedLang = $(this).attr('data-selected-lang');
        FgUtility.showTranslation(selectedLang);
    });
    $('form').off('click', '.contact-count-link');
    $('form').on('click', '.contact-count-link', function() {
        var roleId = $(this).attr('role_id');
        var isBroken = $(this).attr('data-broken');
        if (isBroken == '0' || isBroken == "") {
            handleCountOrSidebarClick.updateFilter('filterrole', 'filterdisplayflag_contact'+clubId2+'-'+contactId, '', clubId2, contactId, '', '', clubUrlIdentifier, 'count', '', '', '', '', 'contact', 'filterrole', catId, roleId, '');
        }
    });
    $('form').off('shown.bs.tab', '.data-more-tab li a[data-toggle="tab"]');
    $('form').on('shown.bs.tab', '.data-more-tab li a[data-toggle="tab"]', function() {
        var curDataTableId = $(this).attr('data-datatableid');
        $('#' + curDataTableId).dataTable().api().draw();
    });
});

/* Functions that should be executed after loading page content */
var initPageFunctions = function() {
    FgUtility.stopPageLoading();
    _dynamicFunctionError = function() {
        FgUtility.showTranslation(defaultLang);
    }
    _dynamicFunction.updateNow = function(elem) {
        var updateElem = $('a#update' + elem.id);
        var filterId = updateElem.attr('data-filter_id');
        var roleId = updateElem.attr('data-role_id');
        var isBroken = updateElem.attr('data-isbroken');
        var filterData = $("#filter_data_" + roleId).val();
        if (filterData != '1') {
            if (isBroken == '0' || isBroken == "" && (filterData != 1)) {
                var url = updateElem.attr('data-url');
                FgXmlHttp.post(url, {filter_id: filterId, role_id: roleId, type: 'role', 'from': 'filterrole'}, false, function() {
                    customFunctions.getData();
                    //location.reload(true);
                });
            }
        }
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
    _dynamicFunction.openLog = function(elem) {
        var roleId = elem.id;
        var id = catId + '_role_' + roleId + '_role';
        var params = id.split('_');
        var param = '?type=' + params[3] + '&CatId=' + params[0] + '&roleId=' + params[2];
        var jsonlog = {type: 'role', typeId: params[2]};
        var typeId = params[2];
        if (!$(this).hasClass('LogDataExist')) {
            $('#open-log-' + params[2]).children('.fg-pad-20').css({'display':'none'})
            FgUtility.startPageLoading();
            $(this).addClass('LogDataExist');
            $.getJSON(logDataPath + param, null, function(data) {

                var logdisplay = FgUtility.groupByMulti(data.logdisplay, ['tabGroups']);
                var hierarchyClubIdArr = data.hierarchyClubIdArr;
                jsonlog['details'] = logdisplay;
                jsonlog['hierarchyClubIdArr'] = hierarchyClubIdArr;
                jsonlog['logTabs'] = data.logTabs;
                jsonlog['activeTab'] = '1';
                var html = FGTemplate.bind('log-listing', jsonlog);
                $('#open-log-' + params[2]).css('display','').children('.fg-pad-20').append(html);
                FgUtility.stopPageLoading();
                $('div.date input:enabled').parent().datepicker(FgApp.dateFormat);
                var logTabsLength = 2;
                for (var i = 1; i <= logTabsLength; i++) {
                    FgUtility.displaylogsettings(typeId + '_' + i);
                    logDateFilterSubmit('date_filter_' + typeId + '_' + i);
                }
                FgMoreMenu.initClientSideWithNoError('data-tabs_' + jsonlog['typeId'], 'data-tabs-content_' + jsonlog['typeId']);
                $('#open-log-' + params[2]).children('.fg-pad-20').slideDown(600);
                setTimeout(function(){
                    var datamoreTab = $('#open-log-' + params[2]).find('.data-more-tab').attr('id');
                    FgMoreMenu.initClientSide(datamoreTab);
                    
                },100);
            });
        } else {
            FgUtility.startPageLoading();
            $('#log-table_' + params[2]).show();
            FgUtility.stopPageLoading();
        }
    }
    addRowCallback = function(elem) {
        var titleElement = $(elem).children().last().find('input[data-title-id]');
        var rolId = $(titleElement).attr('data-title-id');
        $.each(clubLanguages, function(key, lang){
            if (lang != selectedLang) {
                var elemId = catId + '_role_new_' + rolId + '_i18n_' + lang + '_title';
                var elemKey = catId + '.role.new.' + rolId + '.i18n.' + lang + '.title';
                var required = (lang == defaultLang) ? ' required' : 'data-notrequired="true"';
                $(titleElement).parent().append('<div dataerror-group="" data-lang="'+lang+'"><input type="text" data-lang="'+lang+'" data-title-id="'+rolId+'" id="'+elemId+'" name="'+elemId+'" data-key="'+elemKey+'" class="form-control input-sm fg-dev-newfield" placeholder="'+titlePlaceholder+'" value="" '+required+' /></div>');
            }
        });
        FgUtility.showTranslation(selectedLang);
    }
    valueUpdate = function(elemId, action, dataType, itemId, hiddenFieldId) {
        if (action == 'insert') {
            exceptionsData[elemId][dataType].push(itemId);
        } else if (action == 'remove') {
            exceptionsData[elemId][dataType] = _.without(exceptionsData[elemId][dataType], itemId);
        }
        $('#' + hiddenFieldId).val(exceptionsData[elemId][dataType].join(','));
        FgDirtyForm.checkForm('filterrolecategorysettings');
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
            FgDirtyForm.rescan('filterrolecategorysettings');
        });
    }
    checkForBrokenFilterCriteria();
      setTimeout(function () {
                FgTooltip.init();
            },100);
};
var displayTranslation = function() {
    $.each(langTitleArray, function(lang, titleArray){
        $.each(titleArray, function(rolId, title){
            var elemId = catId + '_role_' + rolId + '_i18n_' + lang + '_title';
            var elemKey = catId + '.role.' + rolId + '.i18n.' + lang + '.title';
            var required = (lang == defaultLang) ? ' required' : 'data-notrequired="true"';
            var placeh = roleTitleLangs[rolId].title;
            if(lang == defaultLang){
                $('input[data-title-id='+rolId+']').parent().parent().prepend('<div dataerror-group="" data-lang="'+lang+'"><input type="text" data-lang="'+lang+'" data-title-id="'+rolId+'" id="'+elemId+'" name="'+elemId+'" data-key="'+elemKey+'" class="form-control input-sm" placeholder="'+placeh+'" value="'+title+'" '+required+'/></div>');
            } else {
                $('input[data-title-id='+rolId+']').parent().parent().append('<div dataerror-group="" data-lang="'+lang+'"><input type="text" data-lang="'+lang+'" data-title-id="'+rolId+'" id="'+elemId+'" name="'+elemId+'" data-key="'+elemKey+'" class="form-control input-sm" placeholder="'+placeh+'" value="'+title+'" '+required+'/></div>');
            }
        });
    });
    $.each(roleTitleLangs, function(rolId, titleLangs){
        var rTitle = titleLangs.title;
        $.each(clubLanguages, function(key, lang){
            if (jQuery.inArray(lang, titleLangs.languages) == -1) {
                var elemId = catId + '_role_' + rolId + '_i18n_' + lang + '_title';
                var elemKey = catId + '.role.' + rolId + '.i18n.' + lang + '.title';
                var required = (lang == defaultLang) ? ' required' : 'data-notrequired="true"';
                if(lang == defaultLang){
                    $('input[data-title-id='+rolId+']').parent().parent().prepend('<div dataerror-group="" data-lang="'+lang+'"><input type="text" data-lang="'+lang+'" data-title-id="'+rolId+'" id="'+elemId+'" name="'+elemId+'" data-key="'+elemKey+'" class="form-control input-sm" placeholder="'+rTitle+'" value="" '+required+'/></div>'); 
                } else {
                    $('input[data-title-id='+rolId+']').parent().parent().append('<div dataerror-group="" data-lang="'+lang+'"><input type="text" data-lang="'+lang+'" data-title-id="'+rolId+'" id="'+elemId+'" name="'+elemId+'" data-key="'+elemKey+'" class="form-control input-sm" placeholder="'+rTitle+'" value="" '+required+'/></div>'); 
                }
            }    
        });
    });
    FgUtility.showTranslation(defaultLang);
    FgDirtyForm.init();
    FgDirtyForm.disableButtons();
    FgTooltip.init()
};
var checkForBrokenFilterCriteria = function() {
    $.getJSON(filterContDataPath, { 'getFilterRole': false }, function(masterJsonData) {
        $('.jsonDatahidden').each(function() {
            var filterJsonData = $.parseJSON($(this).val());
            var id = $(this).attr('role-id');
            var isBroken = false;
            isBroken = FgValidateFilter.init(masterJsonData, filterJsonData, 'contact');
            if (!isBroken) {
                $('a[data-fn=filter][data-id='+id+']').after('<i class="fa fa-warning fg-warning fg-broken-filter"  data-toggle="tooltip"></i>');
                FgTooltip.init();
            }
        });
    });
};