var documentId = '';
var documentLogTable = '';
var transArr = [];
var columnDefs = [
        { "targets" : 0, "data" : "dateOriginal", render : function ( data, type, row, meta ) {
            if (typeof row['transArr'] !== 'undefined') {
                transArr = row['transArr'];
            }
            var dateOrg = (row['date'] == '' || row['date'] == null) ? '' : row['date'];
            if (dateOrg == '') {
                dateOrg = (type === 'display') ? '-' : dateOrg;
            } else {
                dateOrg = (type === 'sort') ? data : dateOrg;
            }
            
            return dateOrg;
        }},
        { "targets": 1, "data" : "field", render : function ( data, type, row, meta ) {
            var option = (typeof transArr[data] !== 'undefined') ? transArr[data] : data;
            var field = '';
            if (option != '') {
                field = option + '<span class="label label-sm fg-color-'+row['status']+'">'+transArr[row['status']]+'</span>';
            } else {
                field = (type === 'display') ? '-' : option;
            }

            return field;
        }},
        { "targets": 2, "data" : "value_before", render : function ( data, type, row, meta ) {
            var valueBefore = (data == '' || data == null) ? '' : data;  
            valueBefore = ((row['kind'] == 'included') || (row['kind'] == 'excluded')) ? row['value_before_id'] : valueBefore;
            if (valueBefore != '') {
                valueBefore = FgDocumentLog.handleValueDisplay('value_before', row);
                if (row['kind'] == 'filter') {
                    valueBefore = (type == 'sort') ? '' : valueBefore;
                }
            } else {
                valueBefore = (type === 'display') ? '-' : valueBefore; 
            }
            return valueBefore;
        }},
        { "targets": 3, "data" : "value_after", render : function ( data, type, row, meta ) {
            var valueAfter = (data == '' || data == null) ? '' : data;  
            valueAfter = ((row['kind'] == 'included') || (row['kind'] == 'excluded')) ? row['value_after_id'] : valueAfter;
            if (valueAfter != '') {
                valueAfter = FgDocumentLog.handleValueDisplay('value_after', row);    
                if (row['kind'] == 'filter') {
                    valueAfter = (type == 'sort') ? '' : valueAfter
                }
            } else {
                valueAfter = (type === 'display') ? '-' : valueAfter; 
            }

            return valueAfter;
        }},
        { "targets": 4, "data" : "editedBy",  render : function ( data, type, row, meta ) {

            var editedBy = (data == '' || data == null) ? '' : data;
            if (editedBy == '') {
                editedBy = (type === 'display') ? '-' : editedBy;
            }
            
            return editedBy;
        }}
    ]; 
FgDocumentLog = {
    setVariables : function(docId) {
        documentId = docId;
    },
    /*getDatatableOptions : function() {
        var logTable = $('#datatable-documentlog');
        var opt = FgCommon.setinitialOpt();
        opt.dom = "<t><i><p>";  
        opt.paging = true;
        opt.ajax = logTable.attr('data-ajax-path');
        opt.serverSide = false;
        opt.processing = false;
        opt.deferRender = true;
        opt.order = [[0, "DESC"]];
        opt.scrollX = logTable.attr('xWidth') + "%";
        opt.sScrollXInner = logTable.attr('xWidth') + "%";
        opt.scrollY = FgCommonIntern.getWindowHeight(275) + "px";
        opt.searching = true;//imp for filtering
        opt.columnDefs = columnDefs;
        opt.retrieve = true;
        opt.iDisplayLength = 10;
        opt.fnDrawCallback = function() {
            FgPopOver.customPophover(".fg-dev-Popovers");   
        };
        
        return opt;
    },*/
    handleValueDisplay : function(field, row) {
        var columnValue = (field == 'value_before') ? row['value_before'] : row['value_after'];
        var value = '';
        switch (row['kind']) {
            case 'deposited_with':
                    var depositedWithTexts = (field == 'value_before') ? row['depositedWithBeforeIds'] : row['depositedWithAfterIds'];
                    value = (columnValue == 'NONE') ? transArr['none'] : '-';
                    if (columnValue != 'NONE') {
                        if (row['documentType'] == 'CLUB') {
                            if ((columnValue == 'ALL') || (columnValue == 'SELECTED')) {
                                var textContent = (columnValue == 'ALL') ? transArr['all_clubs'] : transArr['selection_of_clubs'];
                                if (depositedWithTexts != null) { 
                                    var popOverContent = '';
                                    var splitValues = depositedWithTexts.split("#$$$#", 10);
                                    var totalList = _.size(depositedWithTexts.split("#$$$#"));
                                    if (totalList >= 1) {
                                        textContent = (columnValue == 'ALL') ? transArr['all_clubs_except'] + ' '  : transArr['selection_of_clubs'] + ': ';
                                    }
                                    $.each(splitValues, function(index, value) {
                                        if (totalList == 1) {
                                            textContent += value;
                                        } else {
                                            popOverContent += value + "<br/>";
                                        }
                                    })
                                    if (totalList > 10) {
                                        popOverContent += ' ' + (totalList - 10) + ' ' + jstranslations['More'];
                                    }
                                    value = (totalList > 1) ? textContent + ' <i class="fg-dev-Popovers fg-dotted-br"  data-content="' + popOverContent + '" >' + totalList+ ' ' + transArr['clubs'] + '</i>' : textContent;
                                } else {
                                    value = textContent;
                                }
                            }
                        } else {
                            value = (columnValue == 'ALL') ? ((row['documentType'] == 'TEAM') ? transArr['all_teams'] : transArr['all_workgroups']) : '-'; 
                            if (columnValue == 'SELECTED') {
                                var textContent = '';
                                if (depositedWithTexts != null) { 
                                    var popOverContent = '';
                                    var splitValues = depositedWithTexts.split("#$$$#", 10);
                                    var totalList = _.size(depositedWithTexts.split("#$$$#"));
                                    $.each(splitValues, function(index, value) {
                                        if (totalList == 1) {
                                            textContent += value;
                                        } else {
                                            popOverContent += value + "<br/>";
                                        }
                                    })
                                    if (totalList > 10) {
                                        popOverContent += ' ' + (totalList - 10) + ' ' + jstranslations['More'];
                                    }
                                    value = (totalList > 1) ? ' <i class="fg-dev-Popovers fg-dotted-br" data-content="' + popOverContent + '" >' + totalList+ ' ' + ((row['documentType'] == 'TEAM') ? transArr['teams'] : transArr['workgroups']) + '</i>' : textContent;
                                }
                            }
                        }
                    }
                    break;
            case 'visible_for': 
                    value = (columnValue == '') ? '-' : '';
                    if (columnValue != '') {
                        var visibleForTexts = (field == 'value_before') ? row['selectedFunctionsBefore'] : row['selectedFunctionsAfter'];
                        if (columnValue == 'team_functions') {
                            if (visibleForTexts != null) { 
                                    var popOverContent = '';
                                    var splitValues = visibleForTexts.split("#$$$#", 10);
                                    var totalList = _.size(visibleForTexts.split("#$$$#"));
                                    textContent = (totalList >= 1) ? transArr['team_functions'] + ': ' : '';
                                    $.each(splitValues, function(index, value) {
                                        if (totalList == 1) {
                                            textContent += value;
                                        } else {
                                            popOverContent += value + "<br/>";
                                        }
                                    })
                                    if (totalList > 10) {
                                        popOverContent += ' ' + (totalList - 10) + ' ' + jstranslations['More'];
                                    }
                                    value = (totalList > 1) ? textContent + ' <i class="fg-dev-Popovers fg-dotted-br"  data-content="' + popOverContent + '" >' + totalList+ ' ' + transArr['functions'] + '</i>' : textContent;
                            } else {
                                value = transArr['team_functions'];
                            }
                        } else {
                           value =  (typeof transArr[columnValue] !== 'undefined') ? transArr[columnValue] : columnValue;
                        }
                    }
                    break; 
            case 'visible_for_contact': 
                    value = ((columnValue == '') || (columnValue == null)) ? '-' : ((columnValue == 1) ? transArr['on'] : transArr['off']);
                    break;
            case 'filter':
                    value = '-';
                    break;
            case 'included':
            case 'excluded':
                    var contactTexts = (field == 'value_before') ? row['selectedContactsBefore'] : row['selectedContactsAfter'];
                    value = ((contactTexts == '') || (contactTexts == null)) ? '-' : contactTexts;
                    if ((contactTexts != '') && (contactTexts != null)) {
                        var popOverContent = '';
                        var splitValues = contactTexts.split("#$$$#", 10);
                        var totalList = _.size(contactTexts.split("#$$$#"));
                        textContent = (totalList == 1) ? value : '';
                        $.each(splitValues, function(index, value) {
                            textContent = (totalList == 1) ? value : '';
                            if (totalList > 1) {
                                popOverContent += value + "<br/>";
                            }
                        })
                        if (totalList > 10) {
                            popOverContent += ' ' + (totalList - 10) + ' ' + jstranslations['More'];
                        }
                        value = (totalList > 1) ? ' <i class="fg-dev-Popovers fg-dotted-br" data-content="' + popOverContent + '" >' + totalList + ' ' + transArr['contacts'] + '</i>' : textContent;
                    }
                    break;
            default: 
                    value = ((columnValue == '') || (columnValue == null)) ? '-' : columnValue;
                    if (value.length > 50) {
                        value = '<i class="fg-dev-Popovers fg-dotted-br" data-content="' + value + '">'+ value.substring(0,50) + '&hellip;</i>';
                    }
                    break;
        }
        
        return value;
    }
}

$(document).ready(function() {
    //$('div.date input:enabled').parent().datepicker({ todayHighlight: true, autoclose: true, language: jstranslations.localeName, format: FgCommonIntern.dateFormat, weekStart: 1, clearBtn: true });
    //var options = FgDocumentLog.getDatatableOptions();
    var logTable = $('#datatable-documentlog');
    var options = {
                columnDefFlag: true,
                ajaxPath: logTable.attr('data-ajax-path'),
                ajaxparameterflag:true,
                fixedcolumn:false,
                columnDefValues: columnDefs,
                popupFlag: true,
                displaylength: 10,
                serverSideprocess:false,
                countDisplayFlag:true,
                opt: {
                  language: {      
                   zeroRecords:jstranslations['no_record'],
                  }
              }
            };
    
    FgDatatable.listdataTableInit('datatable-documentlog', options);
   // documentLogTable = $('#datatable-documentlog').DataTable(options);
    //return;
    $.fn.dataTable.ext.afnFiltering.push( function( oSettings, aData, iDataIndex ) { 
        if (oSettings.nTable.id != 'datatable-documentlog') {
            return true;
        }
        var date = aData[0]; //date value of current record
        var startdate = $("#filter_start_date_" + documentId).val() != '' ? $("#filter_start_date_" + documentId).val() : '';
        var enddate = $("#filter_end_date_" + documentId).val() != '' ? $("#filter_end_date_" + documentId).val() : '';
        
        if ((startdate != '') || (enddate != '')) {
            var error = false;
            if ((startdate != '') || (enddate != '')) {
                var div = 'log_date_error_' + documentId;
                error = FgCommonIntern.validateDate($("#filter_start_date_" + documentId).val(), $("#filter_end_date_" + documentId).val(), div);
            }
            if (error) {
                return false;
            } else {
                $('#log_date_error_' + documentId).css('display', 'none');
                return FgUtility.dateFilter(date, startdate, enddate);
            }
        }
        
        return true;
    });
    FgFormTools.handleDatepicker({todayHighlight: true, format: FgLocaleSettingsData.jqueryDateFormat, clearBtn: true});
    $('.datepicker').change(function() {
        listTable.draw();
    });
});
FgCommonIntern = {
    getWindowHeight: function (reduceWidth) {
        var height = $(window).height() - reduceWidth;
        if (height <= 300) {
            height = 300;
        }

        return height;
    },
    isFutureDate: function(idate) {
         //The parameter,idate passed should be timestamp with seconds
        var today = Date.now();
        return (today < parseInt(idate))? true : false;
    },
    validateDate: function(startdate, enddate, divid) {
       //to check whether start date is greater than end date
        if(startdate != '')
            var startdateTimestamp = moment(startdate,FgLocaleSettingsData.momentDateFormat).format('x');
        else
            var startdateTimestamp = 0;
        
        if(enddate != '')
            var enddateTimestamp = moment(enddate,FgLocaleSettingsData.momentDateFormat).format('x');
        else
            var enddateTimestamp = 0;
        //ends
        
        //to check whether the dates are less than future date
        var isStartDateFuture = FgCommonIntern.isFutureDate(startdateTimestamp);
        var isEndDateFuture = FgCommonIntern.isFutureDate(enddateTimestamp);
        //ends
        var error_flag = false;
        if ((enddateTimestamp) && (startdateTimestamp > enddateTimestamp)) {
            error_flag = true;
            $('#' + divid).css('display', 'block');
            $('#' + divid).html(errorMsgTranslations['Log_date_filter_err_msg1'] + '.');
        }
        if (isStartDateFuture && isEndDateFuture) {
            error_flag = true;
            $('#' + divid).css('display', 'block');
            $('#' + divid).html(errorMsgTranslations['Log_date_filter_err_msg2'] + '.');
        } else if (isStartDateFuture || isEndDateFuture) {
            if (isStartDateFuture) {
                error_flag = true;
                $('#' + divid).css('display', 'block');
                $('#' + divid).html(errorMsgTranslations['Log_date_filter_err_msg3'] + '.');
            }
            if (isEndDateFuture) {
                error_flag = true;
                $('#' + divid).css('display', 'block');
                $('#' + divid).html(errorMsgTranslations['Log_date_filter_err_msg4'] + '.');
            }
        }

        return error_flag;

    }
};
