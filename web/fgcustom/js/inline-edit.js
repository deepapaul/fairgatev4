var inlineEdit = {
        init: function(obj){
            this.event(obj);
        },
        event: function(obj){
           $('body').on('click',obj.element,function(){
              var _this = $(this);
              var funIds =[]; 
              if(_this.data('type')==undefined){
                    var rowId = _this.data('edit-row'),
                        colId = _this.data('edit-col'),
                        colVal = _this.attr('data-edit-val'),//jQuery's data() does not update the attributes, 
                        //it sets an internal data object, but uses the data attribute as the default value only 
                        //so changed to attr as we may update the editable value (joiningdate = firstjoiningdate)
                        contactId = _this.data('edit-contactid'),
                        emailIds = _this.data('edit-emailids'),
                        placement = 'bottom',
                        inputClass = 'fg-inline-override form-control',
                        minimumResultsForSearch = -1;
                    var selectMultiple = false;
                    var jsonField=[];
                    if (_this.attr('data-edit-type') == 'select23') {
                        jsonField['data-edit-type']='select-multiple';
                    } else if ($.inArray(colId, ["CMfirst_joining_date", "CMjoining_date", "CMleaving_date"]) !== -1) {
                        jsonField = _.findWhere(obj.data.CM, {id: ""+colId});
                        jsonField['data-edit-type'] = 'date';
                    } else if ($.inArray(colId, ["FMfirst_joining_date", "FMjoining_date", "FMleaving_date"]) !== -1) {
                        jsonField = _.findWhere(obj.data.FM, {id: ""+colId});
                        jsonField['data-edit-type'] = 'date';
                    } else if (obj.data.CF != null && typeof obj.data.CF == 'object') {
                        jsonField = _.findWhere(obj.data.CF, {id: ""+colId});
                    } else {
                        jsonField = _.findWhere(obj.data, {id: ""+colId});
                    }
                    if (obj.placement !== undefined) {
                        placement = obj.placement;
                    }
                    _this.editable('destroy');
                    _this.attr('data-type',jsonField['data-edit-type']);
                    //      _this.editable('destroy');
                    var b =[];
                    var editType;
                    if (jsonField['data-type'] == 'url') {
                        inputClass+=' fg-urlmask';
                    }
                    if (jsonField['data-edit-type'] == 'number') {
                        editType = 'text';
                        inputClass+=' numbermask';                    
                    }else if (jsonField['data-edit-type'] == 'date') {
                        editType = 'text';
                        inputClass+=' datepicker datemask';
                    }else if ((jsonField['data-edit-type'] == 'select2') || (jsonField['data-edit-type'] == 'select-multiple') || (jsonField['data-edit-type'] == 'select-search')) {
                        editType = 'select2';
                        inputClass+=' select2';
                        if (jsonField['data-edit-type'] == 'select-search') {
                            editType = 'select2';
                            minimumResultsForSearch = 20;
                        }        
                        if (jsonField['data-edit-type'] == 'select-multiple') {
                            editType = 'select2';
                            selectMultiple = true;
                            b.push({'id':'','text': datatabletranslations['Select'], disabled:true})
                        } else {
                            b.push({'id':'','text': datatabletranslations['Select']})
                        }
                        if (_this.attr('data-edit-type') == 'select23') {
                            var activeId= localStorage.getItem('activeContactSubMenu'+clubId+'-1');
                            var selectedTypeRole=$('li#'+activeId+' a[data-type]').attr('data-type');
                            var catRole=$('li#'+activeId+' a[data-type]').attr('data-categoryid');
                            var roleId=$('li#'+activeId+' a[data-type]').attr('data-id');
                            var selectedF = ', '+colVal+', ';
                            var selectedValue=colVal;

                            _.each(jsonData[selectedTypeRole]['entry'], function(valuer, key) {
                                if(valuer['id']==catRole){
                                    _.each(valuer['input'], function(valuef, key) {
                                        if(valuef['id']==roleId){
                                            _.each(valuef['input'], function(value, key) {
                                                if(selectedF.indexOf(', '+value['title']+',')>-1){
                                                    funIds.push(value['id']);
                                                }
                                                b.push({'id':value['id'],'text':value['title']});
                                             });
                                        }
                                     });
                                     colVal=funIds.join(',');
                                }
                             });
                            editType='select2';
                            emailIds = activeId+'<=>'+funIds.join(',');
                        }
                    } else {
                        editType = jsonField['data-edit-type'];
                        
                    }
                    if (_this.attr('data-edit-type') != 'select23') {
                        _.each(jsonField['input'], function(value, key) {
                            b.push({'id':value['id'],'text':value['title']})
                        });
                    } 
                    var dummyParam;
                    _this.editable({
                        type: editType,
                        value:colVal,
                        emptytext: '-',
                        step:'2',
                        source:b,
                        pk:rowId,
                        url:obj.postUrl,
                        params: function(params) {
                            params.prevVal = colVal;
                            params.rowId = rowId;
                            params.colId = colId;
                            params.fieldType = jsonField['data-edit-type'];
                            params.contactId = contactId;
                            params.emailIds = emailIds;
                            dummyParam = params;
                            return params;
                        },
                        ajaxOptions: {
                            dataType: 'json'
                        },
                        display: function (value)
                        { 
                            if (typeof jsonField['input'] != 'undefined' || typeof selectedTypeRole != 'undefined') {
                                if ((jsonField['data-edit-type'] == 'select2')|| (jsonField['data-edit-type'] == 'select-search')){
                                    if (!value) {
                                        $(this).empty();
                                        return;
                                    }

                                    var match = jQuery.grep(jsonField['input'], function( o ){ 
                                        return ( o.id == value ); });
                                    if(!match[0]){
                                        return;
                                    }
                                    var html = match[0].title;
                                    $(this).html(html);
                                } else if (jsonField['data-edit-type'] == 'select-multiple'){
                                    var html = '';
                                    if($.isArray(value)){
                                        if(!value.length){
                                            $(this).empty();
                                            return;
                                        }
                                        $(value).each(function(i,e){
                                            var match = jQuery.grep(b, function( o ){ return ( o.id == e ); });
                                            if(match[0]){
                                                html += match[0].text;
                                                if(i+1 < value.length) html+=", ";
                                            }
                                        });
                                    }
                                    $(this).html(html);
                                }
                            } else {
                                $(this).html(value);
                            }
                        },
                        success: function(response, newValue) {
                            if(response.valid=='false'){
                                return response.msg;
                            }else{
                                if(dummyParam.value==""){
                                    setTimeout(function(){
              
                                    },50)
                                }
                                if(typeof activeId != typeof undefined){
                                    $('li#'+activeId+' a[data-type]').click();
                                    if(newValue.length==0){
                                        FgCountUpdate.update('remove', 'contact', selectedTypeRole, [{'categoryId' : catRole, 'subCatId' : roleId, 'dataType' : selectedTypeRole, 'sidebarCount' : 1, 'action' : 'remove'}], 1);
                                    }
                                }
                                if(obj.onComplete) {
                                    if (obj.returnResponse) {
                                        obj.onComplete(rowId, response);
                                    } else {
                                        obj.onComplete(rowId);
                                    }
                                }
                                $(".dataTable .inline-editable").each(function() {
                                    var tabValue = $(this).attr('data-tabindex');
                                    $(this).attr('tabindex', tabValue) ;
                                })
                                if ($.inArray(colId, ["CMfirst_joining_date", "CMjoining_date", "FMfirst_joining_date", "FMjoining_date"]) !== -1) {
                                    var updateCol = '';
                                    if ($.inArray(colId, ["CMfirst_joining_date", "CMjoining_date"]) !== -1) {
                                        updateCol = (colId == "CMfirst_joining_date") ? "CMjoining_date" :"CMfirst_joining_date";
                                    }
                                    if ($.inArray(colId, ["FMfirst_joining_date", "FMjoining_date"]) !== -1) {
                                        updateCol = (colId == "FMfirst_joining_date") ? "FMjoining_date" :"FMfirst_joining_date";
                                    }
                                    var updateFlag = ($('span.inline-editable[data-edit-row="' + rowId + '"][data-edit-col="' + colId + '"]').text() === $('span.inline-editable[data-edit-row="' + rowId + '"][data-edit-col="' + updateCol + '"]').text()) ? 1 : 0;
                                    if (updateFlag === 1) {
                                        $('span.inline-editable[data-edit-row="' + rowId + '"][data-edit-col="' + updateCol + '"]').attr('data-edit-val', newValue);
                                        $('span.inline-editable[data-edit-row="' + rowId + '"][data-edit-col="' + updateCol + '"]').editable('destroy');
                                        $('span.inline-editable[data-edit-row="' + rowId + '"][data-edit-col="' + updateCol + '"]').editable('setValue', newValue, true);
                                    }
                                }
                                if (obj.stopExecutionOnComplete) {
                                    return false;
                                } else {
                                    FgUtility.showToastr(response.msg);
                                   _this.focus();
                                    return;
                                }
                            } 
                            
                        },
                        viewformat: FgLocaleSettingsData.jqueryDateFormat,
                        format: FgLocaleSettingsData.jqueryDateFormat,
                        emptyclass: '',
                        placement: placement,
                        inputclass:inputClass,
                        autotext:'never',
                        highlight:'#FFF',
                        select2:{
                            multiple: selectMultiple,
                            allowClear: true,
                            viewseparator: ',',
                            width: '200',
                            autotext : 'always',
                            minimumResultsForSearch: minimumResultsForSearch,
                            dropdownCssClass: 'fg-select2-search-enable',
                        }
                    });
                    if(_this.data('required')){
                        _this.editable('option','validate',function(v){
                            if(!v) return datatabletranslations['VALIDATION_THIS_FIELD_REQUIRED'];
                        })
                    } else {
                        if(obj.callback){
                            obj.callback(_this);
                        }
                    }
                    _this.trigger('click')
                }
            });
            $(obj.element).on('shown', function(e, editable) {
              
                var type = $(this).data('type');
                
                switch (type) {
                    case 'select2':
                        FgFormTools.handleSelect2();
                        break;
                    case 'date':
                        setTimeout(function(){
                            //$(".datemask").inputmask(FgLocaleSettingsData.jqueryDateFormat);
                            $('.datepicker').datepicker({language:datatabletranslations.localeName, format: FgLocaleSettingsData.jqueryDateFormat,weekStart: 1,clearBtn: true,autoclose: true,todayHighlight: true});
                            $('.datepicker').blur().focus();
                        },1)
                        break;
                    case 'number':
                        FgFormTools.handleInputmask();
                        break;
                    default:
                        break;
                }
            });
        }

    };