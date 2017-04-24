var recipientTable, fc, jsonRecipientData; 
var FgBackendDatatable = function () { 
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
        dataFlag:false,
        dataValue:'',
        hasTooltip: true,
        reloadOnWindowResize: true,
        ajaxparameters: {},
        nextPreviousOptions: {},
        opt: {
            language: {
                search: "<span>" + datatabletranslations['data_Search'] + ":</span> ",
                info: datatabletranslations['data_showing'] + " <span>_START_</span> " + datatabletranslations['data_to'] + " <span>_END_</span> " + datatabletranslations['data_of'] + " <span>_TOTAL_</span> " + datatabletranslations['data_entries'],
                zeroRecords: datatabletranslations['no_matching_records'],
                infoEmpty: datatabletranslations['data_showing'] + " <span>0</span> " + datatabletranslations['data_to'] + " <span>0</span> " + datatabletranslations['data_of'] + " <span>0</span> " + datatabletranslations['data_entries'],
                emptyTable: datatabletranslations['no_record'],
                infoFiltered: "(" + datatabletranslations['filtered_from'] + " <span>_MAX_</span> " + datatabletranslations['total_entries'] + ")",
                lengthMenu: '<select>' +
                        '<option value="10">10 ' + datatabletranslations['row'] + '</option>' +
                        '<option value="20">20 ' + datatabletranslations['row'] + '</option>' +
                        '<option value="50">50 ' + datatabletranslations['row'] + '</option>' +
                        '<option value="100">100 ' + datatabletranslations['row'] + '</option>' +
                        '<option value="200">200 ' + datatabletranslations['row'] + '</option>' +
                        '</select> ',
                paginate: {
                    "first": '<i class="fa fa-angle-double-left"></i>',
                    "last": '<i class="fa fa-angle-double-right"></i>',
                    "next": '<i class="fa fa-angle-right"></i>',
                    "previous": '<i class="fa fa-angle-left"></i>'
                },
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
            serverSide: true
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
            var yheight = FgCommon.getWindowHeight(settings.scrollYadjustvalue);
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
        if(settings.dataFlag){
          settings.opt.data=  settings.dataValue;
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
            if (settings.isCheckbox) {
                $('.dataTable_checkall').uniform();
            }
            if (settings.hasTooltip) {
                FgInternal.toolTipInit();
            }
        };

        settings.opt.drawCallback = function (tablesettings) {
            FgUtility.startPageLoading();
            //stop the pageloading process
            $('.dataTable_checkall').prop('checked', false);
            //to remove check from datatable rows checkbox
            $('.dataClass').prop('checked', false);
            $('.fg-dev-checkedtr').removeClass('fg-dev-checkedtr');
                         
            $(".dataTable_checkall").uniform();
            $(".dataClass").uniform();
//            
            //pop up functionality initializing area
            if (settings.popupFlag) {
                FgPopOver.init(".fgPopovers", true, false);
                FgPopOver.init(".fg-dev-Popovers", true);
            }
            if (settings.isCheckbox) {     
                //reset action menu
                $(".fgContactdrop .fa").removeClass('fa-users').removeClass('fa-user').addClass('fa-bars');
                setTimeout(function () {
                    FgCheckBoxClick.init()
                }, 200);
            }
            setTimeout(function () {FgUtility.stopPageLoading();}, 200);
            //checkbox convert to uniform in fixed column datatable           
            if (!$.isEmptyObject(fc)) {
                if ($(window).width() >= 768) {
                }

                fc.fnRedrawLayout();
            }

            $(".chk_cnt").html('');
            //editdatatable init area
            if (settings.editFlag) {
                //call datatable init area 
                settings.inlineEditCallback();

            }
            //rearrange the column width
            if (!$.isEmptyObject(recipientTable) && settings.fixedcolumn) {
                setTimeout(function () {
                    recipientTable.columns.adjust().fixedColumns().relayout();
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
                    cursorAt: {top: 5, left: -20},
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

                FgSidebar.droppableEventIconHandling();
            }
            ;
            /*Drag/Drop ends*/

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
            FgUtility.stopPageLoading();
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
            if (!$.isEmptyObject(recipientTable) && settings.fixedcolumn) {
                setTimeout(function () {
                    recipientTable.columns.adjust().fixedColumns().relayout();
                }, 200)
            }
            if (settings.fixedcolumn) {
                if ($(window).width() >= 768) {
                  recipientTable.columns.adjust();
                }
            }

        }
        instData.on( 'length.dt', function ( e, settings, len ) {
            FgUtility.startPageLoading();
        } );
        //datatable initializing area          
        recipientTable = $('#' + tableId).DataTable(settings.opt);
       //refresh the datatable while resize a page 
        if (settings.reloadOnWindowResize) {
            $(window).resize(function () {
               recipientTable.ajax.reload();
            });
        }
       
        return recipientTable;

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
        if(nextPreviousOptions.length == 0)
            return;
        
        var column = nextPreviousOptions.column;
        var path = nextPreviousOptions.path;
        var key = nextPreviousOptions.key;
        if(typeof recipientTable != 'undefined'){
            var currentDataObj = recipientTable.rows( {order:'current',page:'all',search: 'applied'} ).data();
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
            var recipientTableObj = initdatatable(tableId, options);
            setTimeout(function () {
                $("div.DTFC_LeftBodyLiner").scrollLeft(300);                
            }, 1000);
            return recipientTableObj;
        },
        datatableSearch: function () {
            $("#fg_dev_member_search").on("keyup", function () {
                recipientTable.search(this.value).draw();
                setTimeout(function () {
                    recipientTable.columns.adjust().fixedColumns().relayout();
                }, 100);
                // To display table count
                var tblinfo = recipientTable.page.info();
                $("#slash").removeClass('hide');
                $("#fcount").removeClass('hide').html(tblinfo.recordsDisplay);
                if (this.value == "") {
                    $("#slash").addClass('hide');
                    $("#fcount").addClass('hide');
                }
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






