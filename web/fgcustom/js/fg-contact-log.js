/**
 * Fg contact Log Javascript file
 * 
 * contact log functionality handling 
 *  
 * @author Fairgate.ch
 * 
 */

var data;
var opt;
var tabledf = '';
var tableId = '';

FgContactLogOpt = {
    // data tab column setting
    dataColumnDef: function() {
        var columnDefs = [];
        columnDefs = [{"name": "date", "width": "20%", "targets": 0, data: function(row, type, val, meta) {
                    return  row['dateOriginal'] == '' || row['dateOriginal'] == null ? '-' : row;
                }, render: {"_": 'dateOriginal', "display": 'date', 'filter': 'date'}},
            {"name": "field", "width": "20%", "targets": 1, data: function(row, type, val, meta) {
                    var colorFlag = (row['status'] != "none") ? row['status'] : '';
                    var colorLabel = (colorFlag != '') ? transKindFields[colorFlag] : '-';
                    return  row['field'] == '' || row['field'] == null ? '-' : row['field'] + ' <span class="label label-sm fg-color-' + colorFlag + '">' + colorLabel + '</span>';
                }},
            {"name": "value_before", "width": "20%", "targets": 2, data: function(row, type, val, meta) {
                    if (row['value_before'] == '' || row['value_before'] == null) {
                        return '-';
                    } else {
                        
                        // check if translation is available. else return to row
                        if (row['input_type'] == 'number') {                            
                            var beforeVal = (!isNaN(row['value_before'])) ? FgClubSettings.formatNumber(row['value_before'], false) : row['value_before'];
                            return (beforeVal) ? beforeVal : row['value_before'];
                        }
                        if (_.contains(countryAttrIds, parseInt(row['attribute_id']))) {

                            if (row['value_before'].length && row['value_before'] != '-') {
                                return countryList[row['value_before']];
                            } else {
                                return row['value_before'];
                            }
                        } else if (_.contains(languageAttrIds, parseInt(row['attribute_id']))) {

                            if (row['value_before'].length && row['value_before'] != '-') {
                                return languageList[row['value_before']];
                            } else {
                                return row['value_before'];
                            }
                        } else if (_.contains(sysAttrTransIds, parseInt(row['attribute_id']))) {

                            if (row['value_before'].length && row['value_before'] != '-') {
                                return transKindFields[row['value_before']];
                            } else {
                                return row['value_before'];
                            }
                        }
                        else {
                            //show in hover if length > 50
                            if (row['value_before'].length > 50) {
                                return '<span data-original-title="" data-content="' +
                                        row['value_before'] + '"data-container="body"  data-trigger="hover" class="popovers fg-dotted-br">' + row['value_before'].substring(0, 50) + '&hellip;</i> '

                            } else {
                                return (row['value_before'] == ' ' || row['value_before'] == null) ?'-':row['value_before'];
                            }
                        }
                    }
                }},
            {"name": "value_after", "width": "20%", "targets": 3, data: function(row, type, val, meta) {
                    if (row['value_after'] == '' || row['value_after'] == null) {
                        return '-';
                    } else {
                        if (row['input_type'] == 'number') {
                            var afterVal = (!isNaN(row['value_after'])) ? FgClubSettings.formatNumber(row['value_after'], false) : row['value_after'];
                            return (afterVal) ? afterVal : row['value_after'];
                        }
                        if (_.contains(countryAttrIds, parseInt(row['attribute_id']))) {

                            if (row['value_after'].length && row['value_after'] != '-') {
                                return countryList[row['value_after']];
                            } else {
                                return row['value_after'];
                            }
                        } else if (_.contains(languageAttrIds, parseInt(row['attribute_id']))) {

                            if (row['value_after'].length && row['value_after'] != '-') {
                                return languageList[row['value_after']];
                            } else {
                                return row['value_after'];
                            }
                        } else if (_.contains(sysAttrTransIds, parseInt(row['attribute_id']))) {

                            if (row['value_after'].length && row['value_after'] != '-') {
                                return transKindFields[row['value_after']];
                            } else {
                                return row['value_after'];
                            }
                        }
                        else {
                            //show in hover if length > 50
                            if (row['value_after'].length > 50) {
                                return '<span data-original-title="" data-content="' +
                                        row['value_after'] + '" data-container="body" data-trigger="hover" class="popovers fg-dotted-br">' + row['value_after'].substring(0, 50) + '&hellip;  </i> '

                            } else {
                                return row['value_after'];
                            }
                        }
                    }
                }},
            {"name": "editedBy", "width": "20%", "targets": 4, data: function(row, type, val, meta) {
                    if(row['activeContact']!= null){
                        var oPath = overViewPath.replace('**dummy**', row['activeContact']);
                        row['editedBy'] ='<a href="'+oPath+'" target="_blank" data-cont-id="'+ row['activeContact']+'">'+row['editedBy']+'</a>';
                    }
                    return  row['editedBy'] == '' || row['editedBy'] == null ? '-' : row['editedBy'];
                }}
        ];
        return columnDefs;
    },
    //assignment tab column definition
    assignmentColDef: function() {
        var columnDefs = [];
        columnDefs = [{"name": "date", "width": "25%", "targets": 0, data: function(row, type, val, meta) {
                    return  row['dateOriginal'] == '' || row['dateOriginal'] == null ? '-' : row;
                }, render: {"_": 'dateOriginal', "display": 'date', 'filter': 'date'}},
            {"name": "columnVal2", "width": "25%", "targets": 1, data: function(row, type, val, meta) {
                    return  row['columnVal2'] == '' || row['columnVal2'] == null ? '-' : row['columnVal2'];
                }},
            {"name": "columnVal3", "width": "25%", "targets": 2, data: function(row, type, val, meta) {

                    var colorFlag = (row['status'] != "none") ? row['status'] : '';
                    var colorLabel = (colorFlag != '') ? transKindFields[colorFlag] : '-';

                    return  row['columnVal3'] == '' || row['columnVal3'] == null ? '-' : row['columnVal3'] + ' <span class="label label-sm fg-color-' + colorFlag + '">' + colorLabel + '</span>';
                }},
            {"name": "editedBy", "width": "25%", "targets": 3, data: function(row, type, val, meta) {
                    if(row['activeContact']!= null){
                        var oPath = overViewPath.replace('**dummy**', row['activeContact']);
                        row['editedBy'] ='<a href="'+oPath+'" target="_blank" data-cont-id="'+ row['activeContact']+'">'+row['editedBy']+'</a>';
                    }
                    return  row['editedBy'] == '' || row['editedBy'] == null ? '-' :  row['editedBy'];
                }}

        ];
        return columnDefs;
    },
    //connection tab column definition
    connectionColDef: function() {
        var columnDefs = [];
        columnDefs = [{"name": "date", "width": "25%", "targets": 0, data: function(row, type, val, meta) {
                    return  row['dateOriginal'] == '' || row['dateOriginal'] == null ? '-' : row;
                }, render: {"_": 'dateOriginal', "display": 'date', 'filter': 'date'}},
            {"name": "columnVal2", "width": "25%", "targets": 1, data: function(row, type, val, meta) {
                    return (transArr[row['connectionType']] != undefined ? transArr[row['connectionType']] : row['connectionType']) + " " + row['columnVal2'];
                }},
            {"name": "columnVal3", "width": "25%", "targets": 2, data: function(row, type, val, meta) {

                    var colorFlag = (row['status'] != "none") ? row['status'] : '';
                    var colorLabel = (colorFlag != '') ? transKindFields[colorFlag] : '-';

                    return  row['columnVal3'] == '' || row['columnVal3'] == null ? '-' : row['columnVal3'] + ' <span class="label label-sm fg-color-' + colorFlag + '">' + colorLabel + '</span>';
                }},
            {"name": "editedBy", "width": "25%", "targets": 3, data: function(row, type, val, meta) {
                    if(row['activeContact']!= null){
                        var oPath = overViewPath.replace('**dummy**', row['activeContact']);
                        row['editedBy'] ='<a href="'+oPath+'" target="_blank" data-cont-id="'+ row['activeContact']+'">'+row['editedBy']+'</a>';
                    }
                    return  row['editedBy'] == '' || row['editedBy'] == null ? '-' : row['editedBy'];
                }}

        ];
        return columnDefs;
    },
    //system tab column definition
    systemColDef: function() {
        var columnDefs = [];
        columnDefs = [{"name": "date", "width": "25%", "targets": 0, data: function(row, type, val, meta) {
                    return  row['dateOriginal'] == '' || row['dateOriginal'] == null ? '-' : row;
                }, render: {"_": 'dateOriginal', "display": 'date', 'filter': 'date'}},
            {"name": "columnVal2", "width": "25%", "targets": 1, data: function(row, type, val, meta) {
                    if (row['kind'] == "contact type" && (row['value_after'] == 'Sponsor' || row['value_before'] == 'Sponsor')) {
                        return  transKindFields['contact_type'];
                    } else if (row['kind'] == "user rights" || row['kind'] == "contact type") {
                        return  transKindFields[row['kind']];
                    } else if ((row['kind'] == 'system')) {
                        return transKindFields[row['field']];
                    } else {
                        var col = ('gn_' + row['columnVal2']).toUpperCase();
                        return transKindFields[col];
                    }
                }},
            {"name": "columnVal3", "width": "25%", "targets": 2, data: function(row, type, val, meta) {
                    var colorFlag = (row['status'] != "none") ? row['status'] : '';
                    var colorLabel = (colorFlag != '') ? transKindFields[colorFlag] : '-';
                    switch (row['kind']) {

                        case "user rights" :
                            {
                                var type = row['columnVal3'].indexOf('-');
                                var toTrans = row['columnVal3'].indexOf("(");
                                if (type == -1) {
                                    var colTemp = ('log_' + row['columnVal3']).toUpperCase();
                                    var col = transArr[colTemp];
                                } else {
                                    var sub1 = row['columnVal3'].substring(0, type);
                                    var sub = row['columnVal3'].substring(type + 1, toTrans);
                                    var colTemp1 = ('log_' + sub1.trim()).toUpperCase();
                                    var colTemp = ('log_' + sub.trim()).toUpperCase();
                                    if(sub1=='P')
                                        var col = transArr[colTemp1] + " "+ row['columnVal3'].substring(toTrans);  
                                    else
                                        var col = transArr[colTemp1] + " " + transArr[colTemp] + " " + row['columnVal3'].substring(toTrans);
                                }
                                return col + ' <span class="label label-sm fg-color-' + colorFlag + '">' + colorLabel + '</span>';
                                break;
                            }
                        case "contact type":
                            {
                                
                                if (row['value_after'] != 'Sponsor' && row['value_before'] != 'Sponsor') {
                                    colorFlag = (row['status'] == "added") ? '' : 'changed';
                                    colorLabel = (colorFlag != '') ? transKindFields[colorFlag] : '-';
                                }
                                var col = ('log_' + row['columnVal3']).toUpperCase();
                                var transStatus =  (typeof transArr[col] != 'undefined') ? transArr[col] : row['columnVal3'];
                                
                                return  transStatus + ' <span class="label label-sm fg-color-' + colorFlag + '">' + colorLabel + '</span>';
                                break;
                            }
                        case "system":
                            {
                                var col = (row['columnVal3']).toUpperCase();
                                colorFlag = (row['status'] == 'changed') ? 'changed' : '';
                                colorLabel = (colorFlag != '') ? transKindFields[colorFlag] : '-';
                                var transStatus =  (typeof transArr[col] != 'undefined') ? transArr[col] : row['columnVal3'];
                                
                                return  transStatus + ' <span class="label label-sm fg-color-' + colorFlag + '">' + colorLabel + '</span>';
                                break;
                            }
                        default:
                            {
                                var col = ('log_' + row['columnVal3']).toUpperCase();
                                var transStatus =  (typeof transArr[col] != 'undefined') ? transArr[col] : row['columnVal3'];
                                
                                return (row['kind'] == "login") ? row['columnVal3'] : transStatus;
                                break;
                            }
                    }
                }},
            {"name": "editedBy", "width": "25%", "targets": 3, data: function(row, type, val, meta) {
                    if(row['activeContact']!= null){
                        var oPath = overViewPath.replace('**dummy**', row['activeContact']);
                        row['editedBy'] ='<a href="'+oPath+'" target="_blank" data-cont-id="'+row['activeContact']+'">'+row['editedBy']+'</a>';
                    }
                    return  row['editedBy'] == '' || row['editedBy'] == null ? '-' : row['editedBy'];
                }}

        ];
        return columnDefs;
    },
    //notes tab  column definition
    notesColDef: function() {
        var columnDefs = [];
        columnDefs = [{"name": "date", "width": "20%", "targets": 0, data: function(row, type, val, meta) {
                    return  row['dateOriginal'] == '' || row['dateOriginal'] == null ? '-' : row;
                }, render: {"_": 'dateOriginal', "display": 'date', 'filter': 'date'}},
            {"name": "status", "width": "20%", "targets": 1, data: function(row, type, val, meta) {
                    var colorFlag = (row['status'] != "none") ? row['status'] : '';
                    var colorLabel = (colorFlag != '') ? transKindFields[colorFlag] : '-';
                    return  ' <span class="label label-sm fg-color-' + colorFlag + '">' + colorLabel + '</span>';

                }},
            {"name": "valueBefore", "width": "20%", "targets": 2, data: function(row, type, val, meta) {
                    //show in popup if length > 400
                    var data = row['value_before'].replace(/#~~#/g, '&lt;').replace(/#~#/g, '&quot;');
                    if (row['valueBefore'].length > 400) {
                        var notesFn = 'javascript:FgContactLog.popUpNotes(&quot;' + row['value_before'] + '&quot;,&quot;' + row['date'] + '&quot;);';
                        var valueBefore = '<a href=\"' + notesFn + '\" class="popovers" data-container="body" data-trigger="hover"  data-content=\"' + data.substring(0, 400) + '&hellip; " data-original-title=""><i class="fa fa-file-text-o fa-2x"></i></a>';
                        return row['valueBefore'] == '' || row['valueBefore'] == null ? '-' : valueBefore;
                    } else {

                        return row['valueBefore'] == '' || row['valueBefore'] == null ? '-' : '<a href="#" class="popovers" data-container="body" data-trigger="hover" data-content="' + data + '" data-original-title=""><i class="fa fa-file-text-o fa-2x"></i></a>';
                    }
                }},
            {"name": "valueAfter", "width": "20%", "targets": 3, data: function(row, type, val, meta) {
                    //show in popup if length > 400
                    var data = row['value_after'].replace(/#~#/g, '&quot;').replace(/#~~#/g, '&lt;');
                    if (row['valueAfter'].length > 400) {
                        var notesFn = 'javascript:FgContactLog.popUpNotes(&quot;' + row['value_after'] + '&quot;,&quot;' + row['date'] + '&quot;);';
                        var valueAfter = '<a href=\"' + notesFn + '\" class="popovers" data-container="body" data-trigger="hover"  data-content="' + data.substring(0, 400) + '&hellip; " data-original-title=""><i class="fa fa-file-text-o fa-2x"></i></a>';
                        return row['valueAfter'] == '' || row['valueAfter'] == null ? '-' : valueAfter;
                    } else {
                        return row['valueAfter'] == '' || row['valueAfter'] == null ? '-' : '<a href="#" class="popovers" data-container="body" data-trigger="hover" data-content="' + data + '" data-original-title=""><i class="fa fa-file-text-o fa-2x"></i></a>';
                    }
                }},
            {"name": "editedBy", "width": "20%", "targets": 4, data: function(row, type, val, meta) {
                    if(row['activeContact']!= null){
                        var oPath = overViewPath.replace('**dummy**', row['activeContact']);
                        row['editedBy'] ='<a href="'+oPath+'" target="_blank" data-cont-id="'+ row['activeContact']+'">'+row['editedBy']+'</a>';
                    }
                    return  row['editedBy'] == '' || row['editedBy'] == null ? '-' : row['editedBy'];
                }}

        ];
        return columnDefs;
    },
    //communication tab column settings
    communicationColDef: function() {
        var columnDefs = [];
        columnDefs = [{"name": "date", "width": "25%", "targets": 0, data: function(row, type, val, meta) {
                    return  row['dateOriginal'] == '' || row['dateOriginal'] == null ? '-' : row;
                }, render: {"_": 'dateOriginal', "display": 'date', 'filter': 'date'}},
            {"name": "type", "width": "25%", "targets": 1, data: function(row, type, val, meta) {

                    return row['type'] == 'GENERAL' ? transArr['LOG_NEWSLETTER'] : transArr['LOG_SIMPLEMAIL'];

                }},
            {"name": "sending", "width": "25%", "targets": 2, data: function(row, type, val, meta) {
                    nlPreviewPath1 = nlPreviewPath.replace('%23nl%23', row['newsletterId']);
                    smPreviewPath1 = smPreviewPath.replace('%23sm%23', row['newsletterId']);
                    var previewUrl = (row['type'] == "GENERAL") ? nlPreviewPath1 : smPreviewPath1;
                    return '<a href="' + previewUrl + '">' + row['sending'] + '</a>';

                }},
            {"name": "editedBy", "width": "25%", "targets": 3, data: function(row, type, val, meta) {
                    if(row['activeContact']!= null){
                        var oPath = overViewPath.replace('**dummy**', row['activeContact']);
                        row['editedBy'] ='<a href="'+oPath+'" target="_blank" data-cont-id="'+ row['activeContact']+'">'+row['editedBy']+'</a>';
                    }
                    return  row['editedBy'] == '' || row['editedBy'] == null ? '-' : row['editedBy'];
                }}

        ];
        return columnDefs;
    },
    //membership tab column settings
    membershipColDef: function(from) {
        var columnDefs = [];
        var flagM = (((from == 'membership') && (isMembershipEditable == 1)) || ((from == 'fed_membership') && (fedmembershipEdit == 1)) )?1:0;
        columnDefs = [
            {"name": "joining_date", "width": "20%", "targets": 0, data: function(row, type, val, meta) {
                    var editableClick = flagM ? 'inline-editable editable-click' : '';
                    //var IconSort = flagM ? 'lastcolumn_sort' : '';
                    row['display'] = '<span class="' + editableClick + '" data-edit-row="' + row['id'] + '" data-edit-col="joining_date" data-edit-val="' + row['MembershipFrom'] + '">' + row['MembershipFrom'] + '</span> ';
                    return  row['dateFromOriginal'] == '' || row['dateFromOriginal'] == null ? '-' : row;
                }, render: {"_": 'dateFromOriginal', "display": 'display', 'filter': 'dateFromOriginal'}},
            {"name": "leaving_date", "width": "20%", "targets": 1, data: function(row, type, val, meta) {
                    var editableClick = flagM ? 'inline-editable editable-click' : '';
                    var IconSort = flagM ? 'lastcolumn_sort' : '';
                    row['display'] = row['MembershipTo'] == '' || row['MembershipTo'] == null ? '' : '<span class="' + ((row['isActiveMembership'] == 0) ? editableClick : '') + ' " data-edit-row="' + row['id'] + '" data-edit-col="leaving_date" data-edit-val="' + row['MembershipTo'] + '">' + row['MembershipTo'] + '</span>';
                    return row;
                }, render: {"_": 'dateToOriginal', "display": 'display', 'filter': 'dateToOriginal'
                }},
            {"name": "membership", "width": "20%", "targets": 2, data: function(row, type, val, meta) {
                    var editableClick = flagM ? 'inline-editable editable-click ' : '';
                    //var IconSort = flagM ? 'lastcolumn_sort' : '';
                    return  '<span class="' + ((row['isActiveMembership'] == 0) ? editableClick : '') + '" data-edit-row="' + row['id'] + '" data-edit-col="membership" data-edit-val="' + row['membershipId'] + '">' + row['Membership'] + '</span>';
                }},
            {"name": "editedBy", "width": "20%", "targets": 3, data: function(row, type, val, meta) {
                    if(row['activeContact']!= null){
                        var oPath = overViewPath.replace('**dummy**', row['activeContact']);
                        row['editedBy'] ='<a href="'+oPath+'" target="_blank" data-cont-id="'+ row['activeContact']+'">'+row['editedBy']+'</a>';
                    }
                    return  row['editedBy'] == '' || row['editedBy'] == null ? '-' : row['editedBy'];
                }}

        ];
        if (flagM) {
            columnDefs.push({"name": "membershipDelete", "width": "20%", "targets": 4, data: function(row, type, val, meta) {
                    if (((from == 'membership') && (membershipNotDelId != row['id']) ) || ((from == 'fed_membership') && (notDeletableFedMembershipLogId != row['id']))){
                        return '<a href="#" class="membership_delete" data-params="from/' + row['dateFromOriginal'] + '/to/' + row['dateToOriginal'] + '/membershipid/' + row['membershipId'] + '/contactId/' + contactId + '/currentmembershipLogId/' + membershipNotDelId + '/todelLogId/' + row['id'] + '/fromtab/'+from+'"><i class="fa fa-times-circle fa-2x"></i></a>';
                    } else {
                        return '<i class="fa fa-lock fa-2x ash"></i>';
                    }
                }});
        }
        return columnDefs;
    },
    //initial settings
    setinitialOpt: function() {
        fgDataTableInit();
        opt = {
            deferRender: true,
            order: [[0, "desc"]],
            dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4'i><'col-md-8'p>",
            scrollCollapse: true,
            paging: true,
            autoWidth: true,
            sScrollX: $('table.dataTable-log').attr('xWidth') + "%",
            sScrollXInner: $('table.dataTable-log').attr('xWidth') + "%",
            scrollY: FgCommon.getWindowHeight(275) + "px",
            stateSave: true,
            deferRender: true,
                    stateDuration: 60 * 60 * 24,
            lengthChange: true,
            serverSide: false,
            processing: false,
            pagingType: "full_numbers",
            retrieve: true,
            scrollX: true,
            language: {
                sInfo: datatabletranslations['data_showing'] + " <span>_START_</span> " + datatabletranslations['data_to'] + " <span>_END_</span> " + datatabletranslations['data_of'] + " <span>_TOTAL_</span> " + datatabletranslations['data_entries'],
                sZeroRecords: datatabletranslations['no_matching_records'],
                sInfoEmpty: datatabletranslations['data_showing'] + " <span>0</span> " + datatabletranslations['data_to'] + " <span>0</span> " + datatabletranslations['data_of'] + " <span>0</span> " + datatabletranslations['data_entries'],
                sEmptyTable: datatabletranslations['no_record'],
                sInfoFiltered: "(" + datatabletranslations['filtered_from'] + " <span>_MAX_</span> " + datatabletranslations['total_entries'] + ")",
                lengthMenu: '<select>' +
                        '<option value="10">10 ' + datatabletranslations['row'] + '</option>' +
                        '<option value="20">20 ' + datatabletranslations['row'] + '</option>' +
                        '<option value="50">50 ' + datatabletranslations['row'] + '</option>' +
                        '<option value="100">100 ' + datatabletranslations['row'] + '</option>' +
                        '<option value="200">200 ' + datatabletranslations['row'] + '</option>' +
                        '</select> ',
                oPaginate: {
                    "sFirst": '<i class="fa fa-angle-double-left"></i>',
                    "sLast": '<i class="fa fa-angle-double-right"></i>',
                    "sNext": '<i class="fa fa-angle-right"></i>',
                    "sPrevious": '<i class="fa fa-angle-left"></i>'
                }

            },
            fnDrawCallback: function() {
                setTimeout(function(){FgUtility.stopPageLoading();}, 1000);
                FgPopOver.customPophover(".popovers");
            }
        };
        return opt;
    },
    
};
FgContactLog = {
    // if notes content to be displayed in popup if length > 400
    popUpNotes: function(data, date) {
        var data = data.replace(/#~#/g, '&quot;');
        var data = data.replace(/#~~#/g, '&lt;');
        noteModalTitle1 = noteModalTitle.replace('%date%', date);
        $('#note-modal-title').text(noteModalTitle1);
        $('#notes_content').html(data);
        $('#popup_contents').html($('#notes-popup').html());
        $('#popup').modal('show');
    },
    onTabClick: function(){
        $('.commonLogClass').on('click', function() {
            // var thisElement = $(this);
            var id = $(this).attr('href');
            var tab = id.split("_");
            var tab = $('#log_display_' + tab['1'] + '_' + tab['2']).attr('data-tab');
            activeTab = tab;
            setTimeout(function() {
                switch (tab) {
                    case 'data':
                        {
                            logDateFilterSubmit("date_filter_" + tab['1'] + "_" + tab['2']);
                            logTable1.columns.adjust().draw();
                            break;
                        }

                    case 'assignments':
                        {
                            logDateFilterSubmit("date_filter_" + tab['1'] + "_" + tab['2']);
                            logTable2.columns.adjust().draw();
                            break;
                        }

                    case 'connections':
                        {
                            logTable3.columns.adjust().draw();
                            logDateFilterSubmit("date_filter_" + tab['1'] + "_" + tab['2']);
                            break;
                        }

                    case 'notes':
                        {
                            logTable5.columns.adjust().draw();
                            logDateFilterSubmit("date_filter_" + tab['1'] + "_" + tab['2']);
                            break;
                        }

                    case 'system':
                        {
                            logTable7.columns.adjust().draw();
                            logDateFilterSubmit("date_filter_" + tab['1'] + "_" + tab['2']);
                            break;
                        }

                    case 'communication':
                        {
                            logTable6.columns.adjust().draw();
                            logDateFilterSubmit("date_filter_" + tab['1'] + "_" + tab['2']);
                            break;
                        }

                    case 'membership':
                        {
                            tabledf.columns.adjust().draw();
                            break;
                        }
                    case 'fed_membership':
                        {
                            tablefed.columns.adjust().draw();
                            break;
                        }
                }
            }, 1);
        });

    },
    filterFlagOnChange : function(){
        $('#fg_contact_log_date_filter_flag').on('change', function() {
            if ($(this).is(':checked')) {
                $('#fg_contact_log_date_filter_flag').attr('checked', true);
                //update the property of the checkbox of jquery uniform plugin
                $.uniform.update('#fg_contact_log_date_filter_flag');
            } else {
                $('#fg_contact_log_date_filter_flag').attr('checked', false);
                //update the property of the checkbox of jquery uniform plugin
                $.uniform.update('#fg_contact_log_date_filter_flag');
            }
            $('.log-area').toggleClass('show');
            $('table.table').toggleClass('fg-common-top');
        });

    },
    onClickCancel: function(){
        // on click cancel button
        $(document).on('click', 'button[data-function=cancel]', function() {
             var table = (activeTab == 'membership')?tabledf:tablefed;
            table.$('tr.selected').removeClass('selected');
        });
    },
    onClickMembershipDelete : function(){
        //on click membership delete button
        $(document).on('click', '.membership_delete', function() {
            $(this).closest("tr").addClass('selected');
            var params = $(this).attr('data-params');
            var tabId = $(this).closest('table').data('id');
            $("#membershipDeleteId").val(params+'/tabId/'+tabId);
            $("#popup_contents").html($("#membership-delete-popup").html());
            $('#popup').modal('show');
        });
    },
    onClickShowHideFilter : function(){
        $(document).on('click', '#data-tabs li', function(event) {
            if (($(this).attr('data-tab') == "membership") || ($(this).attr('data-tab') == "fed_membership")) {
                $("#fg_contact_log_date_filter_flag").hide();
            } else {
                $("#fg_contact_log_date_filter_flag").show();
            }
        });
    },
    addMembership: function(){
        //add membership - 
        $("a[data-target=#membership-add-popup]").click(function(ev) {
            var currdate = moment().format(FgLocaleSettingsData.momentDateFormat); 
            $('input#joining_date,input#leaving_date').datepicker(FgApp.dateFormat)
            $('input#joining_date,input#leaving_date').datepicker('setDate', currdate);
            $('.error_joining').addClass('hide');
            $('.error_leaving').addClass('hide');
            $('.error_membership').addClass('hide');
            $('.error_invalid').addClass('hide');
        });
    },
    deleteMembership : function(){
        //delete membership - on click
        $(document).on('click', 'button[data-function=delete]', function() {
            var param = $("#membershipDeleteId").val();

            var params = param.split('/');
            var param = 'from=' + params[1] + '&to=' + params[3] + '&membershipid=' + params[5] + '&contactId=' + params[7] + '&currentmembershipLogId=' + params[9] + '&todelLogId=' + params[11];
            $.getJSON(deletepath + '?' + param, null, function(data) {
                if (data.status = 'SUCCESS') {
                    if(activeTab == 'membership'){
                        if (!$.isEmptyObject(tabledf)) {
                            tabledf.row('.selected').remove().draw(true);
                            FgUtility.showToastr(data.msg);
                        } else {
                            $('#log_display_' + contactId + '_'+params[13]).dataTable().fnDeleteRow(".selected");
                            FgUtility.showToastr(data.msg);
                        }
                    }else{
                        if (!$.isEmptyObject(tablefed)) {
                            tablefed.row('.selected').remove().draw(true);
                            FgUtility.showToastr(data.msg);
                        } else {
                            $('#log_display_' + contactId + '_'+params[13]).dataTable().fnDeleteRow(".selected");
                            FgUtility.showToastr(data.msg);
                        }
                    }
                }
            });
            $('#popup').modal('hide');
        });
    },
    saveMembership : function(){
        //save membership -popup save
        $(document).on('click', 'button[data-function=membership_save]', function() {
            var joiningDate = $("#joining_date").val(),
                leavingDate = $("#leaving_date").val(),
                membershipId = (activeTab == 'membership')? $("#membership").val():$("#log-fedmembership").val();

            var param = '?from=' + joiningDate + '&to=' + leavingDate + '&membershipid=' + membershipId + '&contactId=' + contactId + '&contactname=' + contactName+'&type='+activeTab;
            if (joiningDate != "" && leavingDate != "" && membershipId != "") {
                $.getJSON(membershipLogAddPath + param, null, function(data1) {
                    if (data1.status == "SUCCESS") {
                        var curentMemId = (activeTab == 'membership')?membershipNotDelId:notDeletableFedMembershipLogId;
                        var notDelId = '';
                        if (curentMemId) {
                            notDelId = curentMemId;
                        }
                        var table = (activeTab == 'membership')?tabledf:tablefed;
                        if (!$.isEmptyObject(table)) {
                            table.rows.add([{'Membership': data1.membership, 'MembershipFrom': joiningDate, 'MembershipTo': leavingDate,
                                    'dateFromOriginal': data1.dateFromOriginal, 'dateToOriginal': data1.dateToOriginal, 'editedBy': data1.editedBy,
                                    'id': data1.membershipHistoryId, 'membershipId': data1.membershipId, 'membershipNotDelId': notDelId, 'isActiveMembership': 0}]);
                            table.columns.adjust().draw();

                            FgUtility.showToastr(data1.success_msg);
                        }
                        FgContactLog.inlineEditMembership(activeTab);
                        $('#membership-add-popup').modal('hide');
                    } else {
                        $('.error_invalid').removeClass('hide');
                    }
                });
            } else {
                if (joiningDate == "") {
                    $('.error_joining').removeClass('hide');
                } else {
                    if (!($('.error_joining').hasClass('hide'))) {
                        $('.error_joining').addClass('hide');
                    }
                }
                if (leavingDate == "") {
                    $('.error_leaving').removeClass('hide');
                } else {
                    if (!($('.error_leaving').hasClass('hide'))) {
                        $('.error_leaving').addClass('hide');
                    }
                }
                if (membershipId == "") {
                    $('.error_membership').removeClass('hide');
                } else {
                    if (!($('.error_membership').hasClass('hide'))) {
                        $('.error_membership').addClass('hide');
                    }
                }
            }
        });
    },
     //inline edit 
    inlineEditMembership: function(from) {
        if (((isMembershipEditable == 1) && (from == 'membership')) || ((from == 'fed_membership') && (fedmembershipEdit == 1))) {
            $('div.editable-input input.datepicker').datepicker(FgApp.dateFormat);
            var data1 = (from == 'fed_membership')?JSON.parse(fedmembershipEditArr):JSON.parse(membershipEditArr);
            $('.inline-editable').editable({
                emptytext: '-',
                autotext: 'never',
            });
            inlineEdit.init({
                element: '.inline-editable',
                postUrl: inlineEditMembershipPath,
                data: data1
            })
        }
    }
};

$(document).ready(function() {

    FgMoreMenu.initClientSideWithNoError('data-tabs', 'data-tabs-content');
    FgMoreMenu.initServerSide('paneltab');
    var communicationTabFlag = 0;
    $('.filter-log-input').val('');
    $('div.date input:enabled').parent().datepicker(FgApp.dateFormat);

    $.getJSON($('table.dataTable-log').attr('data-ajax-path'), function(result) {
        data = result;

        //initial datatable initialisng
        $('table.dataTable-log').each(function() {
            var id = $(this).attr('data-id');
            tableId = $(this).attr('id');
            var tab = $(this).attr('data-tab');
            switch (tab) {
                case 'data' :
                    {
                        var opt = FgContactLogOpt.setinitialOpt();
                        opt.data = result['aaData']['data'];
                        opt.columnDefs = FgContactLogOpt.dataColumnDef();
                        logTable1 = $(this).DataTable(opt);
                        logDateFilterSubmit("date_filter_" + result['aaData']['contact'] + "_" + id);
                        $("#" + tableId + "_length").detach().prependTo("#fg_contact_log_row_change");
                        //add our own classes to the selectbox
                        $("#" + tableId + "_length").find('select').addClass('form-control cl-bs-select');
                        $("#" + tableId + "_length").find('select').select2();
                        break;
                    }
                case 'assignments':
                    {
                        $("#" + tableId + "_length").detach();
                        var opt = FgContactLogOpt.setinitialOpt();
                        opt.data = result['aaData']['assignments'];
                        opt.columnDefs = FgContactLogOpt.assignmentColDef();
                        logTable2 = $(this).DataTable(opt);
                        logDateFilterSubmit("date_filter_" + result['aaData']['contact'] + "_" + id);
                        break;
                    }
                case 'connections':
                    {
                        $("#" + tableId + "_length").detach();
                        var opt = FgContactLogOpt.setinitialOpt();
                        opt.data = result['aaData']['connections'];
                        opt.columnDefs = FgContactLogOpt.connectionColDef();
                        logTable3 = $(this).DataTable(opt);
                        logDateFilterSubmit("date_filter_" + result['aaData']['contact'] + "_" + id);
                        break;
                    }
                case 'membership':
                    {
                        $("#" + tableId + "_length").detach();
                        var opt = FgContactLogOpt.setinitialOpt();
                        opt.data = result['aaData']['membership'];
                        opt.columnDefs = FgContactLogOpt.membershipColDef('membership');
                        tabledf = $(this).DataTable(opt);
                        FgContactLog.inlineEditMembership('membership');
                        break;
                    }
                case 'fed_membership':
                    {
                        $("#" + tableId + "_length").detach();
                        var opt = FgContactLogOpt.setinitialOpt();
                        opt.data = result['aaData']['fed_membership'];
                        opt.columnDefs = FgContactLogOpt.membershipColDef('fed_membership');
                        tablefed = $(this).DataTable(opt);
                        FgContactLog.inlineEditMembership('fed_membership');
                        break;
                    }
                case  'notes':
                    {
                        $("#" + tableId + "_length").detach();
                        var opt = FgContactLogOpt.setinitialOpt();
                        opt.data = result['aaData']['notes'];
                        opt.columnDefs = FgContactLogOpt.notesColDef();
                        logTable5 = $(this).DataTable(opt);
                        logDateFilterSubmit("date_filter_" + result['aaData']['contact'] + "_" + id);
                        break;
                    }
                case 'communication' :
                    {
                        $("#" + tableId + "_length").detach();
                        communicationTabFlag = 1;
                        var opt = FgContactLogOpt.setinitialOpt();
                        opt.data = result['aaData']['communication'];
                        opt.columnDefs = FgContactLogOpt.communicationColDef();
                        logTable6 = $(this).DataTable(opt);
                        logDateFilterSubmit("date_filter_" + result['aaData']['contact'] + "_" + id);
                        break;
                    }
                case 'system':
                    {
                        $("#" + tableId + "_length").detach();
                        var opt = FgContactLogOpt.setinitialOpt();
                        opt.data = result['aaData']['system'];
                        opt.columnDefs = FgContactLogOpt.systemColDef();
                        logTable7 = $(this).DataTable(opt);
                        logDateFilterSubmit("date_filter_" + result['aaData']['contact'] + "_" + id);
                        break;
                    }
            }
        });
    });
    
    FgContactLog.onTabClick();
    FgContactLog.filterFlagOnChange();
    FgContactLog.onClickCancel();        
    FgContactLog.onClickMembershipDelete();
    FgContactLog.onClickShowHideFilter();
    FgContactLog.addMembership();
    FgContactLog.deleteMembership();
    FgContactLog.saveMembership();
    

    $('#log_display_' + contactId + '_1').on('length.dt', function(e, settings, len) {
        FgUtility.startPageLoading()
        logTable2.page.len(len).draw();
        logTable3.page.len(len).draw();
        if( clubType != 'standard_club')
        tablefed.page.len(len).draw();
        if(clubType == 'subfed_club' || clubType == 'fed_club')
        tabledf.page.len(len).draw();
        logTable5.page.len(len).draw();
        if (communicationTabFlag == 1) {
            logTable6.page.len(len).draw();
        }
        logTable7.page.len(len).draw();
    });

});




