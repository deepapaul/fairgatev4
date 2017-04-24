if (typeof pathcontactSearch !== 'undefined') {
    var engine;
    engine = new Bloodhound({
        datumTokenizer: function (engine) {
            return Bloodhound.tokenizers.whitespace(engine.country.country_name);
        },
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: pathcontactSearch + "%QUERY?" + $.now(),
            ajax: {data: {'isCompany': 2}},
            filter: function (response) {
                $(response).each(function (index, value) {
                    response[index].value = response[index].contactname;
                })
                return response;
            }
        }
    });
    engine.initialize();
}

$.fn.extend({
    rowList: function (options) {

        var defaults = {
            template: 'body',
            jsondataUrl: "#",
            postValues: {}, //values to post to jsondataUrl
            fieldSort: false,
            searchfilterData: {},
            submit: ['.submit-btn', 'formId'],
            reset: '.reset-btn',
            validate: false,
            addData: ['#addrow', {}],
            loadTemplate: [],
            deleteBtn: '.closeico',
            fn: '[data-fn]',
            resultData: null,
            startSortableCallback: function (event, ui) {
            },
            stopSortableCallback: function (event, ui) {
            },
            onSuccessCallback: function (response) {
            },
            useCKEditor: false,
            useDirtyFields: false,
            dirtyFieldsConfig: null,
            validateFilterCriteria: false
        };

        options = $.extend(defaults, options);
        var _this = $(this),
                _data = _template = null,
                _dynamicFilter = [],
                _dynamicFilterPassed = [];




        var events = function () {
            $('body').off('click', '[data-toggle="collapse"]')
            $('body').on('click', '[data-toggle="collapse"]', function (e) {
                var _this = $(this),
                        targetId = '#' + $(this).data('id'),
                        closestTarget = $(this).closest(targetId);

                _this.parents('.fg-dev-rowactions').find('i.fa-minus-square-o').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');                

                if (_this.hasClass('opened')) {                    
                    if (_this.hasClass('in')) {
                        var dataTarget = _this.data('target');                        
                        $(dataTarget).collapse('hide');
                        _this.toggleClass('in');
                        e.stopPropagation();
                        _this.find('i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
                    } else {
                        //closestTarget.find('.collapse.in, .opened.in').removeClass('in');   
                        closestTarget.find('.opened.in').removeClass('in');
                        closestTarget.find('.collapse.in').collapse('hide');
                        _this.toggleClass('in');
                        _this.find('i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
                    }
                } else {
                    //closestTarget.find('.collapse.in, .opened.in').removeClass('in');
                        closestTarget.find('.opened.in').removeClass('in');
                        closestTarget.find('.collapse.in').collapse('hide');
                    _this.find('i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
                }

            });

            $('body').on('click', options.fn, function (e) {
                var _this = $(this);
                customFunctions.bindEvent(_this);
                e.preventDefault();
            });

            $(options.loadTemplate).each(function () {
                var that = this;
                $('body').off('click', that.btn);
                $('body').on('click', that.btn, function () {
                    customFunctions.addRow(that.template, that.target);
                    if (!options.useDirtyFields) {
                        FgDirtyForm.checkForm(options.submit[1]);
                    }

                })
            })

            $('body').on('click', '.new-row ' + options.deleteBtn, function () {
                customFunctions.removeRow($(this));
            });
            $('body').off('click', options.reset);
            $('body').on('click', options.reset, function (e) {
                customFunctions.buildTemplate();
                if (options.validateFilterCriteria) {
                    recipientList.validateFilterAndEmailSelection();
                }                
            });
            $('body').on('click', options.submit[0], function () {
                if (options.validateFilterCriteria) {
                   recipientList.validateFilterAndEmailSelection();
                }
                if (customFunctions.checkFilter()) {
                    //console.log(options.submit[0],options.submit[1])
                    FormValidation.init(options.submit[1], '_dynamicFunctionSuccess', '_dynamicFunctionError');
                } else {
                    return false;
                }
            });

        }

        customFunctions = {
            getData: function () {
                $.post( options.jsondataUrl, options.postValues, function (data) {                    
                    _template = $(options.template).html();
                    options.resultData = _.template(_template, {
                        'data': data
                    });                    
                    _data = data; 
                    customFunctions.buildTemplate();
                    if (options.triggerFn) {
                        $.each(options.triggerFn, function (i, item) {
                            customFunctions.customTrigger(item);
                        });
                    }
                    if (options.initCallback) {
                        options.initCallback();
                    }
                });

            },
            buildTemplate: function () {
                _dynamicFilter = [];
                _dynamicFilterPassed = [];
                el = _this;
                el.html('');
                el.append(options.resultData);

                if (options.fieldSort) {
                    el.sortable({
                        items: options.fieldSort,
                        handle: '.handle',
                        placeholder: 'placeholder',
                        start: function (event, ui) {
                            $('.placeholder').css('height', ui.item.css('height'));
                            var _this = options.startSortableCallback.call(this, event, ui);
                            if (options.useCKEditor) {
                                if (CKEDITOR.instances[_this.id_textarea]) {
                                    CKEDITOR.instances[_this.id_textarea].destroy();
                                    CKEDITOR.remove(_this.id_textarea);// Remove it
                                }
                            }
                        },
                        stop: function (event, ui) {
                            customFunctions.sortChange(el);
                            var _this = options.stopSortableCallback.call(this, event, ui);
                            if (options.useCKEditor) {
                                CKEDITOR.replace(_this.id_textarea);
                            }
                        }
                    });
                    if (options.useDirtyFields) {   
                        FgDirtyFields.init(options.submit[1], options.dirtyFieldsConfig);
                    } else {                        
                        FgDirtyForm.init();
                        FgDirtyForm.disableButtons();
                    }
                }
                if (options.triggerFunction) {
                    window[options.triggerFunction]();
                }

            },
            addRow: function (template, target) {
                if (template) {
                    _template = $(template).html();
                }
                options.addData[1].id = $.now();
                var result_data = _.template(_template, {
                    'data': [options.addData[1]]
                });
                var targetElement = (target != undefined) ? $(target) : _this;
                targetElement.append($(result_data).addClass('new-row'));
                if (options.addData[2]) {
                    customFunctions.customOpenedItems(options.addData[1].id);
                }
                if (options.rowCallback) {
                    options.rowCallback(targetElement);
                }
                if (options.useDirtyFields) {
                    FgDirtyFields.addFields(result_data);
                }

            },
            customOpenedItems: function (id) {
                $('#' + id).find('[data-fn="' + options.addData[2] + '"]').trigger('click');

            },
            customTrigger: function (el) {
                $('body').find('[data-fn="' + el + '"]').trigger('click');
            },
            bindEvent: function (element, autoFn) {
                if (!element.hasClass('revealed')) {
                    element.addClass('revealed opened in');

                    var thisData = (!autoFn) ? element.data('fn') : element.data('fn-init'),
                            thisDataTarget = element.data('target'),
                            thisDataVal = element.data('val'),
                            thisDataId = element.data('id'),
                            thisDataURL = element.data('url'),
                            thisDataRedirectURL = element.data('redirect-url'),
                            getDataObj = thisData.split('.'),
                            dynamicfunctions = (getDataObj.length > 1) ? _dynamicFunction[getDataObj[0]][getDataObj[1]] : _dynamicFunction[getDataObj[0]];

                    dynamicfunctions({
                        target: thisDataTarget,
                        id: thisDataId,
                        val: thisDataVal,
                        url: thisDataURL,
                        redirectUrl: thisDataRedirectURL,
                        thisObj: element
                    });
                    if (!options.useDirtyFields) {
                        FgDirtyForm.checkForm(options.submit[1]);
                    }
                }
                customFunctions.functionAutoInit(thisDataTarget);
            },
            functionAutoInit: function (element) {
                var dataElement = $(element).find('[data-fn-init]');
                if (dataElement.length > 0) {
                    $.each(dataElement, function (i, item) {
                        customFunctions.bindEvent($(item), true);
                    })
                }
            },
            sortChange: function (el) {

                el.find(options.fieldSort).each(function (index) {
                    var _thisEl = $(this);
                    _thisEl.find('.sort-val').val(_thisEl.index() + 1);
					_thisEl.find('.sort-val').trigger("change");
                })
                if (!options.useDirtyFields) {
                    FgDirtyForm.checkForm(options.submit[1]);
                }

            },
            removeRow: function (el) {
				$(".sft-wrapper script").each(function(index){ //This code is to fix an issue of search filter template getting deleted
					$("body").append($(this).clone().wrap('<div></div>').parent().html());
				});
			   if (options.useDirtyFields) {
					 FgDirtyFields.removeFields(el.closest('.new-row'));
					_dynamicFilter = _.without(_dynamicFilter, el.closest('.new-row').attr("id"));
					el.closest('.new-row').remove();
					
                } else {
                    FgDirtyForm.checkForm(options.submit[1]);
                    el.closest('.new-row').remove();
                }
            },
            filter: function (obj) {
                currRlId = obj.id;
                var storageName = "x-data-filter";
                var filterName = "contact_filter";
                var filterType = $('a[data-fn=filter][data-id='+currRlId+']').attr('data-filtertype');
                if (filterType == 'sponsor') {
                    filterName = "sponsor_filter";
                }
                localStorage.removeItem('x-data-filter');
                var rebuildVal = (obj.val) ? JSON.stringify(obj.val) : false,
                        filterInit = $('#filter-' + currRlId).searchFilter({
                    jsonGlobalVar: options.searchfilterData,
                    criteria: '<div class="col-md-1"><span class="fg-criterion">' + translationTerms.criteria + ':</span></div>',
                    customSelect: true,
                    addBtn: '.add-' + currRlId,
                    dateFormat: FgApp.dateFormat,
                    storageName: storageName,
                    filterName: filterName,
                    rebuild: rebuildVal,
                    conditions: filterCondition,
                    selectTitle: translationTerms.selectTitle,
                    save: '#save_' + currRlId,
                    removeRowCallback: function () {
                        if (options.useDirtyFields) {
                            customFunctions.checkFilter(true);
                            //FgDirtyFields.updateFormState();
                        }
                    },
                    changeCallback: function () { 
                        if (options.useDirtyFields) {
                            customFunctions.checkFilter(true);
							//$.fn.dirtyFields.updateFormState($object);
                            //FgDirtyFields.updateFormState();
                        }
                    },
                    savedCallback: function (data) { 
                        //console.log(currRlId);console.log(data);
                        if (filterType == 'sponsor') {
                            data = data.replace('{\"'+currRlId+ '\":{\"', '{\"sponsor_filter\":{\"');
                        }
                        $('#filter_data_' + currRlId).val(data);
						$('#filter_data_' + currRlId).trigger("change");
                        _dynamicFilterPassed.push(currRlId.toString());
                    },
                    errorCallack: function () {
                        $('#filter_data_' + currRlId).val("");
						$('#filter_data_' + currRlId).trigger("change");
                        _dynamicFilterPassed = [];
                    },
                    addRowCallback: function () {
                        $('#filter_data_' + currRlId).val("");
						$('#filter_data_' + currRlId).trigger("change");
                    }
                });
                _dynamicFilter.push(currRlId.toString()); 
                $('#filter-' + currRlId).prepend('<input type="button" class="btn hidden-submit hidden filter-submit" id="save_' + currRlId + '" data-id="' + currRlId + '">');

            },
            checkFilter: function (noErrorDisplay) {
                _dynamicFilterPassed = [];
                if ($('body').find('.filter-submit').length > 0) { 
                    var result = false;

                    $('body').find('.filter-submit').each(function () {
                        currRlId = $(this).attr('data-id');
                        $(this).trigger('click');
                    });
                    //$('body').find('.filter-submit').trigger('click');
                    if (_.uniq(_dynamicFilter).length == _.uniq(_dynamicFilterPassed).length) {
                        result = true;
                    } else {
                        result = false;
                    }
                    if (!result) {
                        if (!noErrorDisplay) {
                            $('.alert-danger').show();
//                       	scroll to top common form error alert on failing validation
                            FgXmlHttp.scrollToErrorDiv();
                        }
                    }
                    return result;
                } else {
                    return true;
                }

            },
            exceptionToken: function (obj) {

                setTimeout(function () {
                    $('.token-' + obj.id).tokenfield({
                        typeahead: [null, {
                                displayKey: 'contactname',
                                source: engine.ttAdapter()
                            }]
                    }).on('tokenfield:createtoken', function (e) {
                        valueInject(e);
                    }).on('tokenfield:edittoken', function (e) {
                        valueInject(e);
                    }).on('tokenfield:removetoken', function (e) {
                        valueInject(e);
                    });

                    var tempArray = [],
                            valueInject = function (e) {

                                if (e.attrs.id != undefined) {
                                    tempArray.push(e.attrs.id);

                                    /*********************************/
                                    var rlId = obj.id;
                                    if ($(e.currentTarget).hasClass('fg-dev-newfield')) {
                                        rlId = 'new_' + rlId;
                                    }
                                    var inputType = $(e.currentTarget).attr('data-input-type');
                                    if (e.type == 'tokenfield:removetoken') {
                                        exceptionsData[rlId][inputType].pop(e.attrs.id);
                                    } else {
                                        if (jQuery.inArray(e.attrs.id, exceptionsData[rlId][inputType]) == -1) {
                                            exceptionsData[rlId][inputType].push(e.attrs.id);
                                        }
                                    }
                                    var fieldVal = exceptionsData[rlId][inputType].join(',');
                                    $('#' + rlId + '_' + inputType).val(fieldVal);
                                    /*********************************/
                                }

                            }
                    //console.log(jsonTokenData);
                    if ((jsonTokenData[obj.id] != 'undefined') && (jsonTokenData[obj.id] != undefined)) {
                        $('#includedContacts' + obj.id).tokenfield('setTokens', jsonTokenData[obj.id]['included']);
                        $('#excludedContacts' + obj.id).tokenfield('setTokens', jsonTokenData[obj.id]['excluded']);
                    }
                }, 1);

            },
            emailerField: function (obj) {
                if (typeof datatabletranslations.nothingSelectedText !== 'undefined') {
                    $('.select-' + obj.id).selectpicker({noneSelectedText: datatabletranslations.nothingSelectedText});
                    $('.select-two-' + obj.id).selectpicker({noneSelectedText: datatabletranslations.nothingSelectedText});
                } else {
                    $('.select-' + obj.id).selectpicker();
                    $('.select-two-' + obj.id).selectpicker();
                }
            },
            success: function () {

                var objectGraph = FgInternalParseFormField.fieldParse();
                console.log(objectGraph);
                stringifyData = JSON.stringify(objectGraph);
                if (options.useDirtyFields) {
                    FgDirtyFields.removeAllDirtyInstances();
                }                
                FgXmlHttp.post(options.postURL, {
                    saveData: stringifyData
                }, false, function (response) {
                    
                    customFunctions.getData();
                    //_dynamicFilter = [];

                    options.onSuccessCallback(response);
                });

            },
            error: function (data) {
                //alert('error message');
            }

        }

        customFunctions.getData();
        events();

        window._dynamicFunction = customFunctions;
        window._dynamicFunctionSuccess = customFunctions.success;
        window._dynamicFunctionError = customFunctions.error;

        if (options.load) {
            options.load();
        }

    }

});