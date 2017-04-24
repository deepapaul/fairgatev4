/* ------------------------------------------------------------------------
 Class: searchFilter
 Use: Search filter using jquery and underscore
 Author: Prathap pr (http://www.designhills.com)
 Version: 0.1
 ------------------------------------------------------------------------- */

;
(function($, window, document, undefined) {

    var pluginName = "searchFilter",
            defaults = {
                jsonUrl: "json/import.json",
                jsonParams: {},
                jsonGlobalVar: false,
                conditions: {
                    defaults: [{
                            title: 'and',
                            id: 'and'
                        }, {
                            title: 'or',
                            id: 'or'
                        }],
                    date: [{
                            title: 'is',
                            id: 'is'
                        }, {
                            title: 'is not',
                            id: 'is not'
                        }, {
                            title: 'is between',
                            id: 'is between',
                            multiple: 1
                        }, {
                            title: 'is not between',
                            id: 'is not between',
                            multiple: 1
                        }],
                    number: [{
                            title: 'is',
                            id: 'is'
                        }, {
                            title: 'is not',
                            id: 'is not'
                        }, {
                            title: 'is between',
                            id: 'is between',
                            multiple: 1
                        }, {
                            title: 'is not between',
                            id: 'is not between',
                            multiple: 1
                        }],
                    select: [{
                            title: 'is',
                            id: 'is'
                        }, {
                            title: 'is not',
                            id: 'is not'
                        }],
                    text: [{
                            title: 'contains',
                            id: 'contains'
                        }, {
                            title: 'contains not',
                            id: 'contains not'
                        }],
                    assignments: [{
                            title: 'has active assignments',
                            id: 'has active assignments'
                        }, {
                            title: 'has no active assignment',
                            id: 'has no active assignment'
                        }, {
                            title: 'has past assignment',
                            id: 'has past assignment'
                        }, {
                            title: 'has no past assignment',
                            id: 'has no past assignment'
                        }]

                },
                dateFormat: {},
                customSelect: false,
                deleteBtn: '<i class="fa fa-minus-circle fa-2x pull-right"></i>',
                addBtn: '#pencils',
                matches: ['entry', 'input'],
                conditionNode: 3,
                optionsNode: 'fixed_options',
                templates: {
                    select: '<div><select class="bs-select form-control input-sm dFExclude"><% _.each( obj.listItems, function( listItem ){ %><option value="<%- listItem.value %>" data-content="<%- listItem.title %>" data-tree="<%- listItem.tree %>" <% if (typeof(listItem.separator) !== "undefined") {%> class="fg-dev-separator" <% } %>><%- listItem.title %></option> <% }); %></select></div>',
                    assignments: '<div><select class="bs-select form-control input-sm dFExclude"><% _.each( obj.listItems, function( listItem ){ %><option value="<%- listItem.value %>" data-content="<%- listItem.title %>" data-tree="<%- listItem.tree %>" <% if (typeof(listItem.separator) !== "undefined") {%> class="fg-dev-separator" <% } %>><%- listItem.title %></option> <% }); %></select></div>',
                    select_group: '<div><select class="bs-select form-control input-sm dFExclude"><% _.each( obj.listItems, function( listItem, index ){ %>   <% if(index==0){ %><option value="" data-tree="default"><%- listItem.title %></option><% }else{ %><optgroup label="<%- listItem.title %>"><% _.each( listItem.value, function( item ){ %><option value="<%- item.value %>" data-tree="<%- item.tree %>"><%- item.title %></option><% }); %></optgroup><% } %><% }); %></select></div>',
                    text: '<div><input type ="text" class="form-control dFExclude" /></div>',
                    date: '<div class="input-group date"><input type ="text" class="date datemask form-control dFExclude" /><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span></div>',
                    number: '<div><input type ="text" class="number numbermask form-control dFExclude" /></div>',
                    text_Range: '<div><input type ="text" class="form-control dFExclude" /><input type ="text" class="form-control dFExclude" /></div>',
                    number_Range: '<div class="input-group"><input type="text" class="number numbermask form-control dFExclude" name="start"><span class="input-group-addon">' + datatabletranslations.dateRangeAnd + '</span><input type="text" class="number numbermask form-control dFExclude" name="end"></div>',
                    date_Range: '<div class="input-daterange input-group"><label class="input-group"><input type="text" class="date datemask form-control dFExclude" name="start"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span></label><span class="input-group-addon">' + datatabletranslations.dateRangeAnd + '</span><label class="input-group"><input type="text" class="date datemask form-control dFExclude" name="end"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span></label></div>'
                },
                submit: '#search',
                clearBtn: '#clear',
                clearAddDefault: true,
                callback: function() {
                },
				addRowCallback: function() {
                },
                removeRowCallback: function() {
                },
                changeCallback: function() {
                },
                savedCallback: function() {
                },
                errorCallack: function() {
                },
                onComplete: function() {
                },
                errorClass: 'error',
                filterName: '999999',
                storageName: 'data-filter',
                selectTitle: 'Select Type',
                rebuild: false,
                save: '#save',
                criteria: '<div class="col-md-1"><span class="fg-criterion">Criterion:</span></div>',
                wrapper: 'wrapper-filter',
                exportTemplate: ['connector', 'type', 'entry', 'condition', 'input1', 'input2', 'data_type', 'disabled'],
                markUpClass: {
                    level1: "col-md-1 1x",
                    level2: "col-md-2 2x",
                    level3: "col-md-2 3x",
                    level4: "col-md-2 4x",
                    level5: "col-md-4 5x",
                    level6: "col-md-2 6x"

                },
                breakPoint: {
                    index: 6,
                    markUpClass: ["col-md-2"]
                }
            };

    // The actual plugin constructor
    function Plugin(element, options) {
        this.element = element;
        this.settings = $.extend(true, defaults, options);
        this.templates = this.settings.templates;
        this._defaults = defaults;
        this._name = pluginName;
        this._vars = {
            _jsonMap: false,
            _validField: false,
            _htmlMarkup: "",
            _rowid: 0,
            _tagIndex: 0,
            _condition: 0,
            _checkIndex: 0,
            _scopeLength: 0,
            _addCustomSelect: false,
            _fieldIndex: 1,
            _breakPointAdded: false
        }
        this.init();
        this.redraw = function() {
            this.remove();
            this._vars._rowid = 0;
            FgUtility.startPageLoading();
            this.renderScript();
            this.rebuild();
            this._vars._addCustomSelect = true;
            this.customSelect("#" + this.element.id);
        }
        this.reCache = function(data) {
            this.reCacheVar(data);
        }

    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        init: function() {
            // _.templateSettings.variable = "sfts";
            this.remove();
            // get global variable if present,otherwise check for url to import json object
            if (this.settings.jsonGlobalVar) {
                this.cacheVar();
            } else {
                this.import();
            }
           // FgUtility.startPageLoading();
        },
        import: function() {
            var _that = this;
            // some logic for importing json as object
             FgUtility.startPageLoading();
            var jsonCache = $.ajax({
                url: this.settings.jsonUrl,
                data: this.settings.jsonParams,
                dataType: "json",
                success: function(jsonData) {
                    // _that._vars._jsonMap = _.where(jsonData,{'show_filter':1});
                    // _that._vars._jsonMap = _.without(jsonData, _.findWhere(jsonData, {'show_filter':0}));
                    // console.log(jsonData);
                    _that._vars._jsonMap = _.filter(jsonData, function(item) {
                        return item.show_filter !== 0
                    });
                    // _that._vars._jsonMap = jsonData;
                    
                    _that.renderScript();
                    if (_that.checkStorage() || _that.settings.rebuild) {
                        _that.rebuild();
                    } else {
                        _that.addRow(true);
                        setTimeout(function() {
                            _that._vars._addCustomSelect = true;
                            // _that.customSelect();
                        }, 500)
                        _that.settings.onComplete(1);
                    }
                    _that.addEvent();
                    setTimeout(function() {
                        _that._vars._addCustomSelect = true;
                        _that.customSelect("#" + _that.element.id);
                    }, 800);
                }
            });
        },
        cacheVar: function() {
            var _that = this;
            _that._vars._addCustomSelect = false;
            // success: function(jsonData) {
            // _that._vars._jsonMap = this.settings.jsonGlobalVar;
            _that._vars._jsonMap = _.filter(_that.settings.jsonGlobalVar, function(item) {
                return item.show_filter !== 0
            });
            //console.log($(_that.element).parent().parent());
             FgUtility.startPageLoading();                   
            _that.renderScript();
            if (_that.checkStorage() || _that.settings.rebuild) {
                _that.rebuild();
            } else {
                _that.addRow(true);
                setTimeout(function() {
                    _that._vars._addCustomSelect = true;
                    // _that.customSelect();
                }, 500)
                _that.settings.onComplete(1);
            }
            _that.addEvent();
            // }
            setTimeout(function() {
                _that._vars._addCustomSelect = true;
                _that.customSelect("#" + _that.element.id);
            }, 800);
        },
        reCacheVar: function(data) {
            var _that = this;
            _that.settings.jsonGlobalVar = data;
            _that._vars._jsonMap = _.filter(_that.settings.jsonGlobalVar, function(item) {
                return item.show_filter !== 0
            });
        },
        renderScript: function() {
            
            var _that = this;
            // check for script already exists or not
            
           // console.log($(_that.element).parent().parent());
            if ($('body').find('.sft-select-template').length < 1) {

                // if not append some script tags to the div
                $.each(this.templates, function(key, value) {
                    _that._vars._htmlMarkup += '<script type="text/template" class="sft-' + key + '-template">';
                    _that._vars._htmlMarkup += value;
                    _that._vars._htmlMarkup += '</script>';
                });
                $.each(this.settings.conditions, function(key, value) {
                    _that._vars._htmlMarkup += '<script type="text/template" class="sft-condition-' + key + '-template">';
                    // console.log(_that.settings.markUpClass);
                    _that._vars._htmlMarkup += '<div><select class="bs-select form-control input-sm dFExclude"><% _.each( obj.listItems, function( listItem ){ %><option value="<%- listItem.id %>" data-multiple="<%- listItem.multiple %>" ><%- listItem.title %></option> <% }); %></select></div>';
                    _that._vars._htmlMarkup += '</script>';
                });
            }
            $(this.element).append(_that._vars._htmlMarkup).addClass('sft-wrapper');
            this.removeRow();
            //$(this.element).parent('.fg-filter-blk').hide();    
            $(this.element).parent('.fg-filter-blk').slideDown(600);
            FgUtility.stopPageLoading();
        },
        renderFieldInit: function() {
            this.render({
                template: 'select',
                node: {
                    listItems: this.getNode({
                        node: "title",
                        depth: 0,
                        entry: this.settings.matches[0],
                        key: true
                    })
                }
            });
        },
        addRow: function(render, disabled) {
            var _that = this;
            this._vars._rowid += 1;
            var disableClass = disabled ? "disabled" : "";
            $(this.element).append('<div class="row filter-pad sft-row sft-row-' + this._vars._rowid + ' ' + disableClass + ' fg-clear" data-row="' + this._vars._rowid + '"></div>');
            if (!this.isChild()) {
                var criteriaElement = $(this.settings.criteria).addClass('sft-field');
                $(this.element).find('.sft-row-' + this._vars._rowid).prepend(criteriaElement);
            }
            if (this.isChild()) {
                if (!disabled) {
                    $('#' + this.element.id).find('.sft-row-' + this._vars._rowid).append($(this.settings.deleteBtn).addClass('sft-remove'));
                }
                this.renderCondition({
                    template: "defaults",
                    hasevents: true
                });
            }
            if (render) {
                this.renderFieldInit();
            }
			
        },
        addEvent: function() {
            var _that = this,
                    currentRowId = this._vars._rowid;
            $('body').on('click', this.settings.addBtn, function(e) {
                _that.addRow(true);
                _that.customSelect('.sft-row-' + _that._vars._rowid);
                e.preventDefault();
				_that.settings.addRowCallback();
            });
            $('body').on('click', this.settings.submit, function(e) {
                _that.validate('submit');
                e.preventDefault();
            });
            $('body').on('click', this.settings.save, function(e) {
                _that.validate('save');
                e.preventDefault();
            });
            $('body').on('click', this.settings.clearBtn, function(e) {
                _that.clearAll();
                e.preventDefault();
                //hide filter icon on clearing filter
                $(".fa-filter").hide();
//                setTimeout(function() {
//                            if (!$.isEmptyObject(oTable)) {
//                    oTable.api().draw();
//
//                } else if (!$.isEmptyObject(documentTable)) {
//                    documentTable.api().draw();
//                }
//                        }, 500)



            });
        },
        render: function(obj) {
            // dont render select if no array value exists
            if ((obj.node.listItems != null && obj.node.listItems.length < 1 && (obj.template == "select" || obj.template == "assignments" )) || (!obj.node.listItems && (obj.template == "select" || obj.template == "assignments")))
                return false;

            // use group template if the items are grouped 
            if (obj.node.listItems && obj.node.listItems[0] != undefined) {
                if (_.has(obj.node.listItems[0], "group")) {
                    // var select_array = {
                    //     value: '',
                    //     tree: "default",
                    //     title: 'some title goes here'
                    // }
                    // obj.node.listItems.unshift(select_array);
                    // console.log(obj.node.listItems);
                    obj.template = "select_group";
                }
            }


            var _that = this;
            var conditionClass = (obj.isCondition) ? "condition-" : "",
                    template = _.template($('.sft-' + conditionClass + obj.template + "-template").html(), obj.node),
                    currentRow = (obj.rowId) ? obj.rowId : _that._vars._rowid,
                    objectWrapper = (obj.wrapper) ? obj.wrapper : _that.element.id,
                    rowHtml = $('#' + objectWrapper).find('.sft-row-' + currentRow);

            _that._vars._breakPointAdded = false,
                    _that._vars._fieldIndex = rowHtml.find('.sft-field').length + 1;

            var fieldEntity = _that.settings.markUpClass['level' + _that._vars._fieldIndex];

            // check for breakPoint
            if (_that.settings.breakPoint.index == _that._vars._fieldIndex) {
                _that._vars._breakPointAdded = true;
            }

            if (_that._vars._breakPointAdded) {
                fieldEntity = _that.settings.breakPoint.markUpClass[0];
            }

            if (!obj.noevents) {
                template = $(template).addClass('sft-field sft-template sft-template-' + _that._vars._tagIndex + ' ' + fieldEntity).attr('data-type', obj.template);
                _that._vars._tagIndex++;
                // console.log(_that._vars._tagIndex);
            } else {
                template = $(template).addClass('sft-field sft-condition-template sft-template-' + _that._vars._tagIndex + ' ' + fieldEntity).attr('data-template', obj.template);
            }
            rowHtml.append(template);

            // if field reaches breakpoint, adding some logic to remove previous classname and adding new classname
            if (_that._vars._breakPointAdded) {
                $(template).prev('.sft-field').removeClass(_that.settings.markUpClass['level' + (_that.settings.breakPoint.index - 1)]).addClass(fieldEntity);
            }

            // activate second option as selected
            if ((obj.template == "select" || obj.template == "assignments") && !obj.noevents) {
                // console.log(obj.node.listItems.length);
                if (obj.node.listItems.length <= 2) {
                    // rowHtml.find('.sft-template.sft-template-' + (_that._vars._tagIndex - 1) + ' select option:eq(1)').attr("selected", "selected");
                    setTimeout(function() {
                        rowHtml.find('.sft-template.sft-template-' + (_that._vars._tagIndex - 1) + ' select').trigger('change');
                    }, 50)

                } else {
                    // activate any option as default
                    _.each(obj.node.listItems, function(singleNode) {
                        if (singleNode.value == "") {
                            rowHtml.find('.sft-template.sft-template-' + (_that._vars._tagIndex - 1) + ' select option[value=""]').attr("selected", "selected");
                        }
                    });
                }
            }

            // this.customSelect(objectWrapper);
            this.listen(template);
            this.datepicker();
        },
        renderCondition: function(obj) {
            this.render({
                template: obj.template,
                node: {
                    listItems: this.settings.conditions[obj.template]
                },
                noevents: obj.hasevents,
                isCondition: true,
                rowId: obj.position,
                wrapper: obj.wrapper
            });
        },
        isChild: function() {
            return (this._vars._rowid != 1) ? true : false;
            ;
        },
        getNode: function(obj) {
            var nodes,
                    select_type,
                    _that = this;

            // for the first node
            if (obj.key) {
                nodes = _.map(this._vars._jsonMap, function(jsonMap, key) {
                    return {
                        value: jsonMap['id'],
                        // value: key,
                        tree: 'select~' + jsonMap.id + '|' + obj.entry,
                        title: jsonMap[obj.node],
                        separator:jsonMap['has_separator']
                    };
                });
                _that.addtypeField(nodes, {
                    value: "",
                    tree: "default",
                    title: _that.settings.selectTitle
                });


            } else {
                var newjsonArray = this._vars._jsonMap;
                // var newjsonArray = _.filter(this._vars._jsonMap, function(item) {
                //     return item.show_filter!==0;
                // });
                scope = obj.selector.split('|'),
                        this._vars._scopeLength = scope.length,
                        select_type_scope = (this._vars._scopeLength > 2) ? (this._vars._scopeLength / 2) - 1 : 0;
                // select_type = (newjsonArray[this._vars._scopeLength][this.settings.optionsNode]);
                select_type = _.findWhere(newjsonArray, {'id': scope[0]});
                select_type = select_type[this.settings.optionsNode];
                // (newjsonArray[this._vars._scopeLength][this.settings.optionsNode]);
                _.each(scope, function(sel) {
                    if (sel == "entry" || sel == "input") {
                        newjsonArray = newjsonArray[sel];
                    } else {
                        newjsonArray = _.findWhere(newjsonArray, {'id': sel});
                    }
                });

                // append condition node only after hireacrchy is occured
                if (scope.length > this.settings.conditionNode && scope.length < this.settings.conditionNode + 2) {
                    this.renderCondition({
                        template: obj.conditionNode,
                        hasevents: true,
                        position: obj.position,
                        wrapper: obj.wrapper
                    });
                }

                //parse only nodes having show_filter key
                // var filterjsonArray = _.where(newjsonArray,{'show_filter':1});
                // var filterjsonArray = _.without(newjsonArray, _.where(newjsonArray, {'show_filter':0}))
                // console.log(newjsonArray);
                var filterjsonArray = _.filter(newjsonArray, function(item) {
                    return item.show_filter !== 0;
                });
                // console.log(filterjsonArray);
                nodes = _.map(filterjsonArray, function(jsonMap, key) {
                    // console.log(jsonMap.id);
                    var inputType = (jsonMap["type"]) ? jsonMap["type"] : "default";
                    return {
                        value: jsonMap["id"],
                        tree: inputType + '~' + obj.selector + '|' + jsonMap.id + '|' + obj.entry,
                        title: jsonMap["title"]
                    };
                });


                // remove type field if there is no array 
                if (nodes.length < 1)
                    return false;

                // append select type text by shifting the select type to the beginning of the node
                if (select_type[select_type_scope]) {
                    $.each(select_type[select_type_scope], function(index) {
                        _that.addtypeField(nodes, select_type[select_type_scope][index]);
                    })
                }

                // add grouped objects to the existing array
                // uncompleted version

                var newGroup = this.groupFields({
                    json: filterjsonArray,
                    selector: obj.selector,
                    entry: obj.entry
                });
                if (newGroup.length > 1) {
                    // console.log('node excecute');
                    nodes = newGroup;
                    _that.addtypeField(nodes, select_type[select_type_scope][0], true);
                }
            }
            return nodes;

        },
        addtypeField: function(oldNode, newNode, group) {
            var select_array;
            if (group) {
                select_array = {
                    group: 'select',
                    value: newNode.id,
                    tree: "default",
                    title: newNode.title
                }
            } else {
                select_array = {
                    value: newNode.id,
                    tree: "default",
                    title: newNode.title
                }
            }
            return oldNode.unshift(select_array);
        },
        removeField: function(element) {
            element.remove();
        },
        removeRow: function() {
			var that = this;
            $('body').on('click', '.sft-remove', function() {
                $(this).closest('.sft-row').remove();
				that.settings.removeRowCallback();
            })
        },
        groupFields: function(obj) {
            var objIndex = 0;
            var grouping = _.chain(obj.json).groupBy('selectgroup').map(function(value, key) {
                if (key != "undefined") {
                    var newValue = [];

                    _.each(value, function(j, k) {
                        var inputType = (j["type"]) ? j["type"] : "default";
                        newValue[k] = {
                            title: j.title,
                            tree: inputType + '~' + obj.selector + '|' + j.id + '|' + obj.entry,
                            value: j.id
                        }
                        objIndex++;
                    });
                    // console.log(newValue);
                    return {
                        group: key,
                        title: key,
                        value: newValue
                    }
                }


                // _.pluck(value, 'title')
            }).value();
            return grouping;
        },
        listen: function(element) {
            var that = this;
            $('.sft-template select,.sft-condition-template select').unbind('change');
            $('.sft-template select').on('change', function(e) {
                // console.log(that._vars._jsonMap);
                var $this = $(this);
                that.removeField($this.parent('div').nextAll('div'));
                var dataVal = $this.find('option:selected').attr('data-tree'),
                        type = dataVal.split('~'),
                        closestRow = $this.closest('.sft-row'),
                        parentId = closestRow.data('row'),
                        closestWrapper = $this.closest('.sft-wrapper')[0].id;

                if (type[0] === "default") {
                    // check for breakpoint node
                    if (that._vars._breakPointAdded) {
                        that._vars._fieldIndex = closestRow.find('.sft-field').length;
                        if (that.settings.breakPoint.index - 1 == that._vars._fieldIndex) {
                            $this.closest('.sft-field').removeClass(that.settings.breakPoint.markUpClass[0]).addClass(that.settings.markUpClass['level' + that._vars._fieldIndex])
                            that._vars._breakPointAdded = false;
                        }
                        that.customSelect('.sft-row-' + parentId);
                    } else {
                        that.customSelect('.sft-row-' + parentId);
                    }
                    return false;
                }
                that.render({
                    template: type[0],
                    node: {
                        listItems: that.getNode({
                            selector: type[1],
                            entry: that.settings.matches[1],
                            conditionNode: type[0],
                            position: parentId,
                            wrapper: closestWrapper
                        })
                    },
                    wrapper: closestWrapper,
                    rowId: parentId
                });
                that.customSelect('.sft-row-' + parentId);
				//that.settings.changeCallback();
            });
            $('.sft-condition-template select').on('change', function() {
                // that.customSelect();
                var $this = $(this),
                        multipleCheck = $this.find('option:selected').data('multiple'),
                        selecTemplate = $(this).closest('.sft-condition-template').data('template'),
                        parentId = $(this).closest('.sft-row').data('row'),
                        closestWrapper = $this.closest('.sft-wrapper')[0].id,
                        targetTemplate = (multipleCheck == "1") ? '_Range' : "";

                // dont update template is its a select option
                if (selecTemplate == "select" || selecTemplate == "defaults" || selecTemplate == "assignments") {
                    that.customSelect('.sft-row-' + parentId);
                    return false;
                }

                that.removeField($(this).parent('div').nextAll('div'));
                that.render({
                    template: selecTemplate + targetTemplate,
                    node: {
                        listItems: null
                    },
                    wrapper: closestWrapper,
                    rowId: parentId
                });
                that.customSelect('.sft-row-' + parentId);
				//that.settings.changeCallback();
            });
			$('.sft-template input').on('change', function(e) {
				that.settings.changeCallback();
			});
        },
        datepicker: function(range) {
            $(".numbermask").inputmask("numeric", {
                rightAlign: false,
                radixPoint: FgLocaleSettingsData.decimalMark,
                'digits': "2"
            });
            //$(".datemask").inputmask(FgLocaleSettingsData.jqueryDateFormat);
            $('.input-daterange input').datepicker(this.settings.dateFormat);
            $('.date').datepicker(this.settings.dateFormat);
        },
        customSelect: function(obj) {
            if (!this.settings.customSelect)
                return false;

            if (this._vars._addCustomSelect) { 
                $(obj).find('select').selectpicker('refresh'); this.settings.changeCallback();
                // if(obj!=undefined){
                //     $('#' + obj).find('select').selectpicker('refresh');
                // }else{
                //     $('#' + this.element.id).find('select').selectpicker('refresh');
                // }
            }
			
        },
        errorField: function(element) {
            // this._vars._validField = false;
            element.closest('div').addClass(this.settings.errorClass);
            element.attr('required', 'required');
            var closestRow = element.closest('.sft-row');
            this.removeDisable(closestRow);
            if (this._vars._validField) {
                this._vars._validField = false;
            }
        },
        validate: function(status) {
            var _that = this;
            // $('body').on('click', this.settings.submit, function() {
            var exportData = {},
                    inputdata, $this, disabledRow;
            exportData[_that.settings.filterName] = {};
            $(_that.element).find('.' + _that.settings.errorClass).removeClass(_that.settings.errorClass);
            _that._vars._validField = true;
            $(_that.element).find(' .sft-row').each(function(rowIndex) {
                disabledRow = $(this).hasClass('disabled');
                var templateLength = $(this).find('.sft-template').length;
                var data_type = $(this).find('.sft-template:last-child()').data('type');
                // reset condition to null if its in the first iteration
                exportData[_that.settings.filterName][rowIndex] = {}
                $(this).find(':input').not(':button').each(function(index) {
                    $this = $(this);
                    inputdata = _that.stripslashes($this.val()),
                            inputType = $this.prop('tagName');
                    if (inputType == "SELECT") {
                        if (inputdata === " " || inputdata === "" || inputdata === null || inputdata === undefined) {
                            _that.errorField($this);
                        }
                    }

                    if (rowIndex == 0) {
                        exportData[_that.settings.filterName][rowIndex]['connector'] = "null"
                        index += 1;
                    }
                    exportData[_that.settings.filterName][rowIndex][_that.settings.exportTemplate[index]] = inputdata;
                });

                // save disabled row
                if (disabledRow) {
                    exportData[_that.settings.filterName][rowIndex]["disabled"] = true;
                }

                // add data_type to the existing array
                exportData[_that.settings.filterName][rowIndex]["data_type"] = data_type;

            });
            if (_that._vars._validField) {
                _that.settings.onComplete(exportData);
                _that.export(exportData, status);
                setTimeout(function() {
                    _that._vars._addCustomSelect = true;
                    // _that.customSelect();
                }, 500);
            } else {
                _that._vars._addCustomSelect = true;
                // _that.customSelect();
                _that.settings.onComplete(0);
                _that.settings.errorCallack(0);
                // console.log('Please fill out the required fields');
            }
            // })

        },
        export: function(data, status) {
            if (status == "save") {
                localStorage.setItem(this.settings.storageName, JSON.stringify(data));
                this.settings.savedCallback(JSON.stringify(data));
                // console.log('Filter Saved successfully !!!')
            } else if (status == "submit") {
                localStorage.setItem(this.settings.storageName, JSON.stringify(data));
                this.settings.callback(data);
            } else if (status == "change") {
                localStorage.setItem(this.settings.storageName, JSON.stringify(data));
            }
        },
        checkStorage: function() {
            if (localStorage.getItem(this.settings.storageName) === null) {
                return false;
            } else {
                return true;
            }
        },
        rebuild: function() {
            var _that = this;
            var inputIndex,
                    getFilter = (localStorage.getItem(this.settings.storageName)) ? localStorage.getItem(this.settings.storageName) : this.settings.rebuild,
                    // dataIndex,
                    buildTemplate,
                    savedData = JSON.parse(getFilter);

            // rebuild from init if rebuild data is empty
            if (getFilter == null) {
                _that.addRow(true);
                return false;
            }

            _.each(savedData[this.settings.filterName], function(objectList, index) {
                _.has(objectList, 'disabled');
                var checkDisabled = _.has(objectList, 'disabled') ? true : false;
                _that.addRow(true, checkDisabled);
                // console.log(objectList);

                // check whether list have more than one input
                if (_.has(objectList, 'input2')) {
                    multipleInput = true;
                }
                _.each(objectList, function(dataObj, key) {

                    buildTemplate = '#' + _that.element.id + ' .sft-template-' + (_that._vars._tagIndex - 1);
                    if (dataObj != "null") {

                        // dont go through key value of disabled and data_type. These are only using to populate third party data
                        if (key == "data_type" || key == "disabled")
                            return false;

                        // get key number to populate multiple input field
                        inputIndex = key.match(/(\d+)/g);
                        if (inputIndex) {
                            inputIndex -= 1;
                        }
                        // disable trigger if nothing exists
                        if (_that._vars._tagIndex == _that._vars.checkingVal)
                            _that._vars._tagIndex++;
                        _that._vars.checkingVal = _that._vars._tagIndex;

                        $(buildTemplate + ' :input').eq(inputIndex).not('select,:button').attr('value', dataObj);
                        $(buildTemplate + ' select option[value="' + dataObj + '"]').attr("selected", "selected");
                        $(buildTemplate + ' select').trigger('change');
                    }

                    // dataIndex++;
                })
            });
            _that.addDisable();
            _that.validate();
            // _that.settings.onComplete(1);
            // console.log(savedData);
        },
        addDisable: function() {
            $('.disabled :input').attr({
                'disabled': 'disabled',
                'readonly': 'readonly'
            });
        },
        removeDisable: function(element) {
            element.removeClass('disabled');
            element.find(':input').removeAttr('disabled readonly');
        },
        clearAll: function() {
            $('#' + this.element.id).find(".sft-row").not('.disabled').remove();
            this._vars._validField;
            if ($('#' + this.element.id).find(".sft-row").length < 1) {
                localStorage.removeItem(this.settings.storageName);
                this._vars._rowid = 0;
                //this.addRow(true);
                this.settings.onComplete(1);
                $('#'+this.element.id+' select').select2();
            } else {
                this.validate("change");
            }
            // console.log('Filter Cleared successfully !!!')
        },
        remove: function() {
            $(this.element).html('');

        },
        stripslashes: function(str) {
            var changedVal = str.replace(/[>]/g, '&rt;');
            return (changedVal)
        }
    });

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function(options) {
        this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });

        // chain jQuery functions
        return this;
    };

})(jQuery, window, document);