var listTable, fc, jsonData;
var FgDatatable = function () {
    var settings;
    var instData;
    var defaultSettings = {
        fixedcolumn: true,
        fixedcolumnCount: 2,
        initialSort: false,
        initialSortColumn: '',
        scrollYflag: true,
        scrollYadjustvalue: '50',
        rowlengthshow: true,
        rowlengthWrapperdivid: '',
        ajaxHeader: false,
        columnDefFlag: false,
        columnDefValues: '',
        scrollxinnerValue: '',
        scrollxValue: '100',
        serverSideprocess: true,
        ajaxPath: '',
        ajaxparameterflag: false,
        tableId: '',
        tableColumnTitleStorageName: '',
        tableSettingValueStorageName: '',
        datatableobjectName: 'listTable',
        manipulationFlag: false,
        manipulationFunction: '',
        editFlag: false,
        inlineEditCallback: '',
        displaylengthflag: true,
        displaylength: 10,
        popupFlag: false,
        widthResize: false,
        initialSortingFlag: false,
        initialsortingColumn: '',
        initialSortingorder: '',
        countDisplayFlag: false,
        draggableFlag: false,
        isCheckbox: true,
        showFilter:false,
        tableFilterStorageName:'',
        dataFlag :false,
        data :'',
        stateSaveFlag:true,
        module: '',
        hasActionMenu : true,
        ajaxparameters: {},
        nextPreviousOptions: {},
        opt: {
            language: {
                search: "<span>" + jstranslations['data_Search'] + ":</span> ",
                info: jstranslations['data_showing'] + " <span>_START_</span> " + jstranslations['data_to'] + " <span>_END_</span> " + jstranslations['data_of'] + " <span>_TOTAL_</span> " + jstranslations['data_entries'],
                zeroRecords: jstranslations['no_matching_records'],
                infoEmpty: jstranslations['data_showing'] + " <span>0</span> " + jstranslations['data_to'] + " <span>0</span> " + jstranslations['data_of'] + " <span>0</span> " + jstranslations['data_entries'],
                emptyTable: jstranslations['no_record'],
                infoFiltered: "(" + jstranslations['filtered_from'] + " <span>_MAX_</span> " + jstranslations['total_entries'] + ")",
                lengthMenu: '<select>' +
                        '<option value="10">10 ' + jstranslations['row'] + '</option>' +
                        '<option value="20">20 ' + jstranslations['row'] + '</option>' +
                        '<option value="50">50 ' + jstranslations['row'] + '</option>' +
                        '<option value="100">100 ' + jstranslations['row'] + '</option>' +
                        '<option value="200">200 ' + jstranslations['row'] + '</option>' +
                        '</select> ',
                paginate: {
                    "first": '<i class="fa fa-angle-double-left"></i>',
                    "last": '<i class="fa fa-angle-double-right"></i>',
                    "next": '<i class="fa fa-angle-right"></i>',
                    "previous": '<i class="fa fa-angle-left"></i>'
                },
                thousands: FgLocaleSettingsData.thousendSeperator,
            },
            paging: true,
            scrollCollapse: true,
            dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4 fg-datatable-pagination 'i><'col-md-8'p>",
            autoWidth: false,
            stateSave: true,
            stateDuration: 60 * 60 * 24,
            responsive: true,
            deferRender: true,
            lengthChange: true,
            pagination: true,
            rowLength: true,
            scrollX: true,
            pagingType: 'full_numbers',
            processing: true,
            serverSide: true,
            destroy:true
        }

    };
    //function to merge the new option value with default option value
    var initSettings = function (options) {
        settings = $.extend(true, {}, defaultSettings, options);
    }
    var initdatatable = function (tableId, options) {
        if (!jQuery().dataTable) {
            return;
        }
        //options merging function
        initSettings(options);
        //set the id of table
        settings.tableId = tableId;
        instData = $('#' + tableId);
        //set the y-axis height 
        if (settings.scrollYflag) {
            var yheight = FgInternal.getWindowHeight(settings.scrollYadjustvalue);
            var xwidth = settings.scrollxValue;
            settings.opt.scrollY = yheight + "px";
            settings.opt.scrollX = xwidth + "%";
            settings.opt.scrollXInner = xwidth + "%";
        }
        //For set the columnDefs
        if (settings.columnDefFlag) {
            settings.opt.columnDefs = settings.columnDefValues;
        }
        //set the column from localstroage
        if (settings.ajaxHeader) {
            settings.opt.columns = $.parseJSON(localStorage.getItem(settings.tableColumnTitleStorageName));
        }
        //set default page length
        if (settings.displaylengthflag) {
            settings.opt.iDisplayLength = settings.displaylength;
        }
        //set the fixed column
        if (settings.fixedcolumn) {
            generateFixedColumn();
        }
        //rearrange the width of last column
        if (settings.widthResize) {
          var totalColumn = (_.size($.parseJSON(localStorage.getItem(settings.tableColumnTitleStorageName)))) - 1;
              settings.opt.columnDefs = [{"width": "100%", "targets": totalColumn}];
        }
        //initial sorting check
        if (settings.initialSortingFlag) {
            settings.opt.order = [[settings.initialsortingColumn, settings.initialSortingorder]];
        } else {
            settings.opt.aaSorting = [];
        }
        //server side process
        if (settings.serverSideprocess) {
            settings.opt.serverSide = true;
            settings.opt.processing = true;

        } else {
            settings.opt.serverSide = false;
            settings.opt.processing = true;
        }
        if (settings.ajaxPath != '') {
            settings.opt.ajax = {
                "url": settings.ajaxPath,
                "data": function (parameter) {
                    //for setting the document listing parameters
                    if (settings.ajaxparameterflag) {
                        parameterSetting(parameter, settings.ajaxparameters);
                    }
                    if(settings.showFilter){
                        var filterData = $.parseJSON(localStorage.getItem(settings.tableFilterStorageName));
                        if(filterData !== null){
                            parameterSetting(parameter, filterData);
                        }
                    }
                },
                "type": "POST",
                "dataSrc": function (json) {
                    if (settings.manipulationFlag) {
                        var fn = window[settings.manipulationFunction];
                        //call function
                        fn(json);
                    }

                    return json.aaData;
                }
            };
        }
        
        if(settings.dataFlag) {
           settings.opt.data=settings.data; 
        }
        if(!settings.stateSaveFlag) {
           settings.opt.stateSave=false; 
        }
        

        //row call back
        settings.opt.rowCallback = function (nRow, aData, iDataIndex) {
               if (aData.removedFlag == 1) {
                    $(nRow).addClass('fg-deleted-row');
                }
            //give - value to the null
            $('td', nRow).each(function (index, value) {
        
                if (index == 0 && settings.isCheckbox == true) {
                  $(this).addClass('fg-checkbox-td');
                }
                if ((aData.newlyaddedFlag == 1) || (aData.Function_original == '' && aData.Function_ADDED != '')) {
                    $(this).addClass('fg-new-member');
                } else if ((aData.removedFlag == 0) && (aData.Function_intersect == '')) {
                    $(this).addClass('fg-remove-member');
                }                
                
                if ($(this).html() == '' || $(this).html() == null) {
                    $(this).html("-");
                }
            });


        };
        //header callback
        settings.opt.headerCallback = function (nHead, aData, iStart, iEnd, aiDisplay) {
            $('.dataTable_checkall').uniform();
            FgInternal.toolTipInit();

        };

        settings.opt.drawCallback = function (tablesettings) {
            if (settings.hasActionMenu) {
                //stop the pageloading process
                $('.dataTable_checkall').prop('checked', false);
                //to remove check from datatable rows checkbox
                $('.dataClass').prop('checked', false);
                $('.fg-dev-checkedtr').removeClass('fg-dev-checkedtr');

                $.uniform.update(".dataTable_checkall");
                $.uniform.update(".dataClass");

                FgActionmenuhandler.init();
                setTimeout(function () {
                    FgCheckBoxClick.init()
                }, 200);
                $(".chk_cnt").html('');
            }

            //pop up functionality initializing area
            if (settings.popupFlag) {
                FgPopOver.init(".fgPopovers", true, false);
                FgPopOver.init(".fg-dev-Popovers", true);
            }
            
            setTimeout(function () {Metronic.stopPageLoading();}, 200);
            //checkbox convert to uniform in fixed column datatable           
            if (!$.isEmptyObject(fc)) {
                if ($(window).width() >= 768) {
                }

                fc.fnRedrawLayout();
            }

            //editdatatable init area
            if (settings.editFlag) {
                //call datatable init area 
                settings.inlineEditCallback();

            }
            //rearrange the column width
            if (!$.isEmptyObject(listTable)) {
                setTimeout(function () {
                    listTable.columns.adjust().fixedColumns().relayout();
                }, 100)
            }

            //To show data count
            if (settings.countDisplayFlag) {
                $("#tcount").html(tablesettings.fnRecordsTotal());
            }

            /*Drag/Drop Starts*/
            if (settings.draggableFlag) {
                var insideMain = false;
                $(".dataTables_scrollBody").droppable({
                    over: function (  ) {
                        insideMain = true;
                    },
                    out: function () {
                        insideMain = false;
                    }
                });

                $(".dataTable tr .fg-sort").draggable({
                    cursorAt: {top: 8, left: -20},
                    helper: function (event) {
                        var count;
                        if ($("input.dataClass:checked").length > 0) {
                            count = parseInt($("i.chk_cnt").html());
                        } else {
                            count = 1;
                        }
                        return $("<div class='ui-widget-header'><span class='fg-drag-count'>" + count + "</span></div>");
                    },
                    containment: "body"
                });
//                The below condition can be removed after integrating fg_sidebar in all internal areas
            
                
               
               if(!((settings.module== 'CMS'|| settings.module== 'ARTICLE'))) {
                   FgSidebar.droppableEventIconHandling();
                }
            }
            ;
            /*Drag/Drop ends*/
            
            //To initialize tooltip in datatable
            if(typeof FgTooltip !== 'undefined'){
                FgTooltip.init();
            }
        };
        settings.opt.stateLoadCallback = function (tablesetting) {
            var stringified = localStorage.getItem('DataTables_' + settings.tableId + window.location.pathname + window.location.search)
            var oData = JSON.parse(stringified || null);
            if (oData && tablesetting.fnRecordsTotal() < oData.start) {
                oData.start = 0;
            }
            if (oData) {
                $("#fg_dev_member_search").val(oData.search.search);

            }
            return oData;
        };
        settings.opt.stateSaveCallback = function (tblsettings, data) {
            localStorage.setItem('DataTables_' + settings.tableId + window.location.pathname + window.location.search, JSON.stringify(data));
            handleNextPreviousOption();
        };

        settings.opt.initComplete = function () {

            //stop page loading area
            Metronic.stopPageLoading();
            // For change the position of  no.of records per page selection drop down box
            if (settings.rowlengthshow) {
                $('#' + settings.rowlengthWrapperdivid).empty();
                $('#' + settings.rowlengthWrapperdivid).show();
                tableid = settings.tableId;
                //for change the position
                $("#" + tableid + "_length").detach().prependTo("#" + settings.rowlengthWrapperdivid);
                //add our own classes to the selectbox
                $("#" + tableid + "_length").find('select').addClass('form-control cl-bs-select');
                $("#" + tableid + "_length").find('select').select2();
                FgFormTools.handleSelect2();
            }
            if (!$.isEmptyObject(listTable)) {
                setTimeout(function () {
                    listTable.columns.adjust().fixedColumns().relayout();
                }, 200)
            }
            if (settings.fixedcolumn) {
                if ($(window).width() >= 768) {
                  listTable.columns.adjust();
                }
            }

        }
        instData.on( 'length.dt', function ( e, settings, len ) {
            Metronic.startPageLoading();
        } );
        //datatable initializing area  
        listTable = $('#' + tableId).DataTable(settings.opt);
       //refresh the datatable while resize a page 
        $(window).resize(function () {
           listTable.ajax.reload();
        });
       
        return listTable;

    }
    //function to set the parameter of ajax post
    var parameterSetting = function (parameter, settingValue) {
       
        $.each(settingValue, function (key, value) {
            parameter[key] = value;
            
        })
        return parameter;
    }

    var generateFixedColumn = function () {

        if ($(window).width() >= 768) {
            settings.opt.fixedColumns = {
                leftColumns: settings.fixedcolumnCount
            }

        }
    }

    var handleNextPreviousOption = function(){
        var nextPreviousOptions = settings.nextPreviousOptions;
        if(_.size(nextPreviousOptions) == 0)
            return;
        
        var column = nextPreviousOptions.column;
        var path = nextPreviousOptions.path;
        var key = nextPreviousOptions.key;
        if(typeof listTable != 'undefined'){
            var currentDataObj = listTable.rows( {order:'current',page:'all',search: 'applied'} ).data();
            var currentData = _.pluck(currentDataObj, column).join();
            if(nextPreviousOptions.currentData != currentData){
                nextPreviousOptions.currentData = currentData;
                $.ajax({
                     type: "POST",
                     url: path,
                     data: {'key':key,'id':currentData}
                   });
            }
        }
    }
    
    return {
        //Function to use the datatable init
        listdataTableInit: function (tableId, options) {
            var listTableObj = initdatatable(tableId, options);
            setTimeout(function () {
                //$("div.DTFC_LeftBodyLiner").scrollLeft(300);                
            }, 1000);
            return listTableObj;
        },
        datatableSearch: function () {
            $("#fg_dev_member_search").on("keyup", function () {
                var searchVal = this.value;
                setDelay(function () {
                    listTable.search(searchVal).draw();
                    setTimeout(function () {
                        listTable.columns.adjust().fixedColumns().relayout();
                    }, 100);
                    // To display table count
                    var tblinfo = listTable.page.info();
                    $("#slash").removeClass('hide');
                    $("#fcount").removeClass('hide').html(tblinfo.recordsDisplay);
                    if (searchVal == "") {
                        $("#slash").addClass('hide');
                        $("#fcount").addClass('hide');
                    }
                }, 500);
            })
        },
        getSettings: function () {
           return settings; 
        },
        setNewValues: function (newsettings) {
            settings = newsettings;
        }
        
    };

}();

function manipulatememberColumnFields(json) {

    window.actionMenuTextDraft = {'active': {'none': json.actionMenu.none, 'single': json.actionMenu.single, 'multiple': json.actionMenu.multiple}};
    scope.$apply(function () {
        scope.menuContent = window.actionMenuTextDraft;
    });
   
    //Code to remove the data from the local storage while changing the visibility
    var columnValues = $.parseJSON(localStorage.getItem(tableSettingValueStorage)); //Json with all current columns for the datatable
    var columnValueCount = (_.size($.parseJSON(localStorage.getItem(tableColumnTitleStorage)))) - 1;
    var permissionDisableFields = json.aaDataHide; //Json array with all columns that need to be hidden of the columns that are been currently shown
    var i = 3;  //adjust for first 3 fixed (edit,contact name, function)
    $.each(columnValues, function (key, values) {
        //Show the column initially
        listTable.column(i).visible(true); 
        if ($.inArray(values.id, permissionDisableFields) >= 0) {
            //Hide the column
            listTable.column(i).visible(false);
        } 
        i++;
    })

//check the privacy settings and change flag
    $.each(json.aaData, function (key, values) {

        $.each(values, function (value) {
            //split the value for identify the actual value
            var splitValues = value.split('_');
            if (_.size(splitValues) == 2 && $.inArray('CF', splitValues) > -1) {
                json.aaData[key][value + "_ADMINCHANGE"] = 0;
                if (json.aaData[key][value + "_visibility"] == 'private') {
                    json.aaData[key][value] = "<i class='fa fa-eye-slash fg-dev-Popovers' data-trigger='hover' data-placement='bottom' data-content='" + jstranslations.PrivateText + "'> </i>";
                    json.aaData[key][value + "_ADMINCHANGE"] = 1;
                } else if (json.adminflag == 1 && json.aaData[key][value + "_Flag"] == 'NONE') {
                    var dummyValue = jstranslations.waitingForConfirmation;
                    if (json.aaData[key][value] != '') {
                        dummyValue = dummyValue + ' ' + jstranslations.currentActiveValue.replace('%a%', json.aaData[key][value]);
                    }
                    json.aaData[key][value] = json.aaData[key][value + "_CHANGED"] + "&nbsp;<i class='fg-dev-Popovers fa fa-clock-o' data-trigger='hover' data-placement='bottom' data-content='" + dummyValue + "' > </i>";
                    json.aaData[key][value + "_ADMINCHANGE"] = 1;
                }
            } else if ($.inArray('Function', splitValues) > -1 && ($.inArray('Flag', splitValues) > -1)) {


                var addedValue = json.aaData[key]['Function_ADDED'];
                var removedValue = json.aaData[key]['Function_REMOVED'];
                var splitAddarray = addedValue.split(',');
                var splitRemovedarray = removedValue.split(',');
                var functionValue = json.aaData[key]['Function'];
                var funcArray = functionValue.split(',');

                //add two function(function and new added function) then remove the  removed function
                var newFunctionArray = _.difference(_.union(splitAddarray, funcArray), splitRemovedarray);
                var functionText = '';

                $.each(newFunctionArray, function (key, values) {
                    title = values.split('#');
                    if (title[0] != '') {
                        functionText += ", " + title[0];
                    }

                })
                functionText = functionText.slice(1);
                //area to create function column string
                var ftext = '';
                $.each(funcArray, function (fkey, fvalues) {
                    ftitle = fvalues.split('#');
                    if (ftitle[0] != '') {
                        ftext += ", " + ftitle[0];
                    }

                })
                ftext = ftext.slice(1);
                json.aaData[key]['Function'] = ftext;
                json.aaData[key]['Function_intersect'] = functionText;
                json.aaData[key]['Function_original'] = functionValue;
//             else if (json.aaData[key]["Function_Flag"] > 0 && values['newlyaddedFlag'] !=1) {
//                    json.aaData[key]['Function'] = (ftext!='')? functionText + "&nbsp;<i class='fg-dev-Popovers fa fa-clock-o' data-trigger='hover' data-placement='bottom' data-content='" + ftext + "' > </i>":functionText;
//                 } 
                if (json.adminflag == 1 && json.aaData[key]["Function_Flag"] > 0) {
                    var dummyValue = jstranslations.waitingForConfirmation;
                    if (ftext != '') {
                        dummyValue = dummyValue + ' ' + jstranslations.currentActiveValue.replace('%a%', ftext);
                    }
                    json.aaData[key]['Function'] = (ftext != '') ? functionText + "&nbsp;<i class='fg-dev-Popovers fa fa-clock-o' data-trigger='hover' data-placement='bottom' data-content='" + dummyValue + "' > </i>" : functionText;
                } else if (values['newlyaddedFlag'] == 1) {
                    json.aaData[key]['Function'] = functionText;
                } else {
                    json.aaData[key]['Function'] = ftext;
                }
            } else if (value == 'Gfedmembership_category') {
                    var approveIcon = (json.aaData[key]['fedmembershipApprove'] > 0) ? "&nbsp;<i class='fg-dev-Popovers fa fa-clock-o' data-trigger='hover' data-placement='bottom' data-content='" + jstranslations.fedmemConfirmTootipMsg + "' > </i>" : '';
                    json.aaData[key][value] = json.aaData[key][value] + approveIcon;
            } 

        })

    })

    for (var i = 0, ien = json.aaData.length; i < ien; i++) {
        for (var j = 0, jen = json.aaDataType.length; j < jen; j++) {
            var title = json.aaDataType[j]['title'];

            switch (json.aaDataType[j]['type']) {
                case "email":
                    if (json.aaData[i][title + "_ADMINCHANGE"] != 1) {
                        json.aaData[i][title] = json.aaData[i][title] ? '<a  href="mailto:' + json.aaData[i][title] + '" target="_self">' + json.aaData[i][title] + '</a>' : '-';
                    }
                    break;
                case "imageupload":
                    var uploadPath = json.aaDataType[j]['uploadPath']+'/';
                    if (json.aaData[i][title + "_ADMINCHANGE"] != 1) {
                        json.aaData[i][title] = json.aaData[i][title] ? '<a  href="'+ uploadPath + json.aaData[i][title] + '" target="_blank"><i class="fa fg-file-photo fg-datatable-icon"></i></a>' : '-';
                    }
                    break;
                case "fileupload":
                    var uploadPath = json.aaDataType[j]['uploadPath']+'/';
                    if (json.aaData[i][title + "_ADMINCHANGE"] != 1) {
                        json.aaData[i][title] = json.aaData[i][title] ? '<a  href="' + uploadPath +json.aaData[i][title] + '" target="_blank"><i class="fa fg-file-pdf fg-datatable-icon"></i></a>' : '-';
                    }
                    break;
                case "url":
                    if (json.aaData[i][title + "_ADMINCHANGE"] != 1) {
                        json.aaData[i][title] = json.aaData[i][title] ? '<a href="' + json.aaData[i][title] + '" target="_blank">' + json.aaData[i][title] + '</a>' : '-';
                    }
                    break;


                case "multiline":
                case "singleline":
                    if (json.aaData[i][title].length > 50) {
                        var lineBreak = json.aaData[i][title].match(/.{1,50}/g);
                        if (_.size(lineBreak) > 1 && json.aaData[i][title + "_ADMINCHANGE"] != 1) {
                            var dummyValue = jstranslations.waitingForConfirmation;
                            var dataContent = nl2br(json.aaData[i][title]);
                            if (dataContent != '') {
                                dummyValue = dummyValue + ' ' + jstranslations.currentActiveValue.replace('%a%', dataContent);
                            }
                            json.aaData[i][title] = '<i class="fg-dev-Popovers fg-dotted-br" data-trigger="hover" data-placement="bottom" data-content="' + dummyValue + '" data-original-title="">' + lineBreak[0] + ' &hellip;</i>';
                        }
                    }

                    break;

                case "contactname"  :
                    var icons = '';
                    var editIcons = '' ;
                    var editable = ' class-no-edit';
                    //its only needed in team/workgroup member list
                    var profilepicArea ='';
                    
                    if ((json['adminflag'] == 1 && (json.aaData[i]['confirm_status'] == 'CONFIRMED') )|| ((parseInt(json.aaData[i]['removedFlag']) == 0) && (json.aaData[i]['Function_intersect'] == '')) || ( parseInt(json.aaData[i]['newlyaddedFlag'])==0 && parseInt(json.aaData[i]['removedFlag'])!=0 )|| ( parseInt(json.aaData[i]['newlyaddedFlag'])==1 && parseInt(json.aaData[i]['removedFlag'])==0 ) ){
                        editIcons = '&nbsp;<a href="' + json.aaData[i]['edit_url'] + '" class="fg-tableimg-hide fg-edit-contact-ico "><i class="fa fa-pencil-square-o fg-pencil-square-o"></i></a>';
                        editable  = '';
                    }
                    //area for handle profile image according to the type
                    if(json.aaData[i]['Profilepic']!='' && json.aaData[i]['ProfilepicExists'] ) {
                        if(json.aaData[i]["Iscompany"]==1) {
                            profilepicArea = "<div class='fg-profile-img-blk-CH35 ' style='width:"+json.aaData[i]['imageWidth']+"px;' ><img src='"+json.aaData[i]['Profilepic']+"' alt=''></div>";
                        } else {
                            profilepicArea = '<div class="fg-profile-img-blk35 fg-round-img " style="background-image:url(\''+json.aaData[i]['Profilepic']+'\')" ></div>'; 
                        }
                    } else {
                        if(json.aaData[i]["Iscompany"]==1) {
                            profilepicArea = '<div class="fg-profile-img-blk35 fg-round-img fg-placeholder-icon" style="background-image:url(\'\')"><i class="fa fa-building-o"></i></div>';
                        } else if(json.aaData[i]["Gender"]=="Male" || json.aaData[i]["Gender"]=="male") {
                            profilepicArea = '<div class="fg-profile-img-blk35 fg-round-img fg-placeholder-icon" style="background-image:url(\'\')"><i class="fa fa-male"></i></div>';
                        } else if(json.aaData[i]["Gender"]=="Female" ||json.aaData[i]["Gender"]=="female") {
                            profilepicArea = '<div class="fg-profile-img-blk35 fg-round-img fg-placeholder-icon" style="background-image:url(\'\')"><i class="fa fa-female"></i></div>';
                        }
                    }                    

                    if ((json.aaData[i]['stealthFlag'] == "1") || (json.aaData[i]['newlyaddedFlag'] == "1")) {
                        json.aaData[i][title] = "<div class='fg-contact-wrap'> " +profilepicArea+ json.aaData[i][title] + editIcons + "</div>";
                    } else {
                        json.aaData[i][title] = "<div class='fg-contact-wrap'> " +profilepicArea+"<a class='fg-dev-contactname' href='" + json.aaData[i]['click_url'] + "'>" + json.aaData[i][title] + "</a>" + editIcons + "</div>";
                    }
                    break;
                case "edit"  :
                    json.aaData[i][title] = '<input class="dataClass '+editable+'" type="checkbox" id=' + json.aaData[i]['id'] + ' name="check" data-iscompany =' + json.aaData[i]['Iscompany'] + ' data-contactclub =' + json.aaData[i]['contactclubid'] + '  data-edit-attr='+ editable +' value="0">';

                    break;


            }

        }
    }
}

function manipulateDocumentColumnFields(json) {
    jsonData = json;
    if (json.actionMenu.adminFlag == 0) {
        $('.fg-action-menu').removeClass('fg-active-IB').addClass('fg-dis-none');
        listTable.column(0).visible(false);
    } else {
        $('.fg-action-menu').addClass('fg-active-IB').removeClass('fg-dis-none');
        listTable.column(0).visible(true);
    }

    window.actionMenuTextDraft = {'active': {'none': json.actionMenu.none, 'single': json.actionMenu.single, 'multiple': json.actionMenu.multiple}};
    scope.$apply(function () {
        scope.menuContent = window.actionMenuTextDraft;
    });

    for (var i = 0, ien = json.aaData.length; i < ien; i++) {
        for (var j = 0, jen = json.aaDataType.length; j < jen; j++) {
            var title = json.aaDataType[j]['title'];
            switch (json.aaDataType[j]['type']) {
                case "edit"  :
                    var dragArrow = '<i class="fa fg-sort ui-draggable"></i>';
                    json.aaData[i][title] = dragArrow + ' <input class="dataClass fg-dev-avoidicon-behaviour" type="checkbox" id=' + json.aaData[i]['documentId'] + ' data-subcategoryId=' + json.aaData[i]['subCategoryId'] + ' name="check"  value="0"  >';
                    break;
                case "T_DO_LAST_UPDATED"  :
                    json.aaData[i][title] = json.aaData[i]['T_DO_LAST_UPDATED'] ? json.aaData[i]['T_DO_LAST_UPDATED'] : '';
                    break;
                case "WG_DO_LAST_UPDATED"  :
                    json.aaData[i][title] = json.aaData[i]['WG_DO_LAST_UPDATED'] ? json.aaData[i]['WG_DO_LAST_UPDATED'] : '';
                    break;
                case "T_FO_DEPOSITED_WITH"  :
                    var depositedWith = json.aaData[i]['T_FO_DEPOSITED_WITH'];
                    json.aaData[i][title] = depositedWith ? FgInternal.createPopover(depositedWith, roleType) : '';
                    break;
                case "WG_FO_DEPOSITED_WITH"  :
                    var depositedWith = json.aaData[i]['WG_FO_DEPOSITED_WITH'];
                    json.aaData[i][title] = depositedWith ? FgInternal.createPopover(depositedWith, roleType) : '';
                    break;
                case "T_FO_SIZE"  :
                    var fileSize = json.aaData[i]['T_FO_SIZE'];
                    json.aaData[i][title] = fileSize ? FgInternal.convertByteToMb(fileSize, mb) : '';
                    break;
                case "WG_FO_SIZE"  :
                    var fileSize = json.aaData[i]['WG_FO_SIZE'];
                    json.aaData[i][title] = fileSize ? FgInternal.convertByteToMb(fileSize, mb) : '';
                    break;
                case "documentName"  :
                    var classname = (json.aaData[i]['isUnread'] === '1') ? 'fg-strong' : '';
                    var docId = json.aaData[i]['documentId'];
                    var versionId = json.aaData[i]['versionId'];
                    var url = path.replace("|documentId|", docId);
                    url = url.replace("|versionId|", versionId);
                    var editUrl = docEditPath.replace("|documentId|", docId);
                    var fileIcon = (typeof json.aaData[i]['fileName'] != 'undefined') ? fileUploader.getFileIcon(json.aaData[i]['fileName']) : '';
                    var editIcon = (json.actionMenu.adminFlag == 1) ? '&nbsp;<a href="' + editUrl + '" class="fg-tableimg-hide"><i class="fa fa-pencil-square-o fg-pencil-square-o"></i></a>' : '';
                    json.aaData[i][title] = fileIcon + '<a href="' + url + '" target="_blank" class="fg-dev-read fg-dev-docname ' + classname + '" data-id="' + docId + '" data_url="' + url + '" >' + json.aaData[i]['documentName'] + '</a>' + editIcon;
                    break;
                case "T_FO_VISIBLE_TO"  :
                    if (json.aaData[i]['T_FO_VISIBLE_TO'] === 'team_functions') {
                        json.aaData[i][title] = json.aaData[i]['visibleToFunctions'] ? FgInternal.createPopover(json.aaData[i]['visibleToFunctions'], visibleTo.team_functions, true) : '';
                    } else if (json.aaData[i]['T_FO_VISIBLE_TO'] === 'team') {
                        json.aaData[i][title] = visibleTo.team;
                    } else if (json.aaData[i]['T_FO_VISIBLE_TO'] === 'team_admin') {
                        json.aaData[i][title] = visibleTo.team_admin;
                    }
                    break;
                case "WG_FO_VISIBLE_TO"  :
                    if (json.aaData[i]['WG_FO_VISIBLE_TO'] === 'workgroup') {
                        json.aaData[i][title] = visibleTo.workgroup;
                    } else if (json.aaData[i]['WG_FO_VISIBLE_TO'] === 'workgroup_admin') {
                        json.aaData[i][title] = visibleTo.workgroup_admin;
                    }
                    break;
                 case "WG_FO_ISPUBLIC"  :
                    if (json.aaData[i]['WG_FO_ISPUBLIC'] === '1') {
                        json.aaData[i][title] = '<div class="fg-static-on">'+transON+'</div>';
                    } else {
                        json.aaData[i][title] = '<div class="fg-static-off">'+transOFF+'</div>';
                    }
                  break;
                case "T_FO_ISPUBLIC"  :
                  if (json.aaData[i]['T_FO_ISPUBLIC'] === '1') {
                        json.aaData[i][title] = '<div class="fg-static-on">On</div>';
                    } else {
                        json.aaData[i][title] = '<div class="fg-static-off">Off</div>';
                    }
                    break;

            }
        }
    }
}

function manipulateforumdata(json) {
    $(".fg-start-new-topic").attr('href', json.createtopicUrl);
    setTimeout(function () {
        $('.sorting_asc').removeClass('sorting_asc').addClass('sorting_disabled');
    }, 100);
    // If admin(super, club, group, forum)
    if (json.isAdmin) {
       $('#lock-forum').show(); 
    } else {
       $('#lock-forum').hide(); 
    }
    
    if (json.isActivatedForum == 1) { // Deactivated forum
        $('#lock-forum').html('<i class="fa fa-eye fa-2x"></i> ' + actForum);
        $('#follow-forum').hide();
    } else {
        $('#lock-forum').html('<i class="fa fa-eye-slash fa-2x"></i> ' + dectForum);
        $('#follow-forum').show();
    }

    if (json.isFollowTopic == 1) {
        $('#follow-forum').html('<i class="fa fa-bell-slash-o fa-2x"></i> ' + unfollowForum);
    } else {
        $('#follow-forum').html('<i class="fa fa-bell-o fa-2x"></i> ' + followForum);
    }
  
    $('#fg-forum-search').keyup(function (e) {
        if (e.which == 13) {//Enter key pressed
          // search event
            var search = $.trim($(this).val());
            if (search != null && search != '') {
                window.location = json.searchUrl + "?term=" + search;
            }
      }
  });
  
}

function manipulateCMSPageList(json){
    cmsData = json;
    for (var i = 0, ien = json.aaData.length; i < ien; i++) {
        var navIds = json.aaData[i]['navIds'];
        json.aaData[i]['navTitle'] = navIds ? FgCmsPageList.createNavigation(navIds) : '';
    }
}

function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
//for create the mouse over effect on the both fixed column and normal table
$(document).on({
    mouseenter: function () {
        trIndex = $(this).index() + 1;
        $(".DTFC_ScrollWrapper .dataTable").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                $(this).find("td").addClass("fghover");
                $(this).find("td").addClass('fg-dev-td-hover');
            });
        });
        $(".hover-edit").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                $(this).find("td").addClass("fghover");
                $(this).find("td").addClass('fg-dev-td-hover');
            });
        });
    },
    mouseleave: function () {
        trIndex = $(this).index() + 1;
        $(".DTFC_ScrollWrapper .dataTable").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                $(this).find("td").removeClass("fghover");
                // $(this).find("td").css("background", "#fff");
            });
        });
        $(".hover-edit").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                $(this).find("td").removeClass("fghover");
                // $(this).find("td").css("background", "#fff");
            });
        });
        $("table.dataTable").find('.fg-dev-td-hover').removeClass('fg-dev-td-hover');
    },
    drop: function () {
        trIndex = $(this).index() + 1;
        var selCount = $("input.dataClass:checked").length;
        $("table.dataTable").each(function (index) {
            $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                if (selCount <= 1) {
                    $(this).find("td").addClass("fg-droppable-color");
                }
            });
        });
        $("table.dataTable").removeClass('fg-dev-drag-active');
        $("table.dataTable").find('.fg-dev-td-hover').removeClass('fg-dev-td-hover');

    },
    drag: function (e) {
        //$("table.dataTable").not('.fg-dev-drag-active').addClass('fg-dev-drag-active');
        $("body").addClass('fg-dev-drag-active');
    },
}, ".dataTables_wrapper tbody tr");

jQuery.extend(jQuery.fn.dataTableExt.oSort, {
    "null-last-asc": function (a, b) {
        if (a === '' || a === null) {
            return 1;
        }
        if (b === '' || b === null) {
            return -1;
        }
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
    "null-last-desc": function (a, b) {
        if (a === '' || a === null) {
            return 1;
        }
        if (b === '' || b === null) {
            return -1;
        }
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    },
    "null-numeric-last-asc": function (a, b) {
        if (a === '' || a === null) {
            return 1;
        }
        if (b === '' || b === null) {
            return -1;
        }
        a = parseFloat(a);
        b = parseFloat(b);
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
    "null-numeric-last-desc": function (a, b) {
        if (a === '' || a === null) {
            return 1;
        }
        if (b === '' || b === null) {
            return -1;
        }
        a = parseFloat(a);
        b = parseFloat(b);
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    },
    "hyphen-last-asc": function (a, b) {
        if (a === '-') {
            return 1;
        }
        if (b === '-') {
            return -1;
        }
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
    "hyphen-last-desc": function (a, b) {
        if (a === '-') {
            return 1;
        }
        if (b === '-') {
            return -1;
        }
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    },
//    Used to sort values like '<0.1 MB'
    "less-symbol-asc": function (a, b) {
        var indx_a = a.indexOf("<");
        if (indx_a === 0) {
            a = a.slice(1);
        }
        var indx_b = b.indexOf("<");
        if (indx_b === 0) {
            b = b.slice(1);
        }
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
//    Used to sort values like '<0.1 MB'
    "less-symbol-desc": function (a, b) {
        var indx_a = a.indexOf("<");
        if (indx_a === 0) {
            a = a.slice(1);
        }
        var indx_b = b.indexOf("<");
        if (indx_b === 0) {
            b = b.slice(1);
        }
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
});





