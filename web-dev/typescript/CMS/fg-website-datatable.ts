/// <reference path="../directives/jquery.d.ts" />
/// <reference path="../directives/underscore.d.ts" />
/// <reference path="../directives/jqueryui.d.ts" />
 //  var listTable ={};
   var fc ={};
class FgWebsiteDatatable {
    listTable: Object = {};
    settings: Object = {};
    defaultSettings: any = {
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
        hidePagination: false,
        hidePaginationOnSinglePageCount: false,
        displaylength: 10,
        popupFlag: false,
        widthResize: false,
        initialSortingFlag: false,
        initialsortingColumn: '',
        initialSortingorder: '',
        countDisplayFlag: false,
        draggableFlag: false,
        isCheckbox: false,
        showFilter: false,
        tableFilterStorageName: '',
        dataFlag: false,
        data: '',
        stateSaveFlag: true,
        module: '',
        tableDrawCallback: function(){},
        ajaxparameters: {},
        nextPreviousOptions: {},
        searchTextBox : "fg_dev_member_search",
        rowHighlight : true,
        opt: {
            language: {
                search: "<span>" + jstranslations['data_Search'] + ":</span> ",
                info: jstranslations['data_showing'] + " <span>_START_</span> " + jstranslations['data_to'] + " <span>_END_</span> " + jstranslations['data_of'] + " <span>_TOTAL_</span> " + jstranslations['data_entries'],
                zeroRecords: jstranslations['loadingVar'],
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
                    "next": '<i class="fa fa-angle-right"></i>',
                    "previous": '<i class="fa fa-angle-left"></i>'
                },
                thousands: FgLocaleSettingsData.thousendSeperator,
            },
            paging: true,
            scrollCollapse: true,
            dom: "<'col-md-12't><'col-md-12'p>",
            autoWidth: false,
            stateSave: true,
            stateDuration: 60 * 60 * 24,
            responsive: false,
            responsiveToggle: true,
            deferRender: true,
            lengthChange: true,
            pagination: true,
            rowLength: true,
            scrollX: true,
            //pagingType: 'full_numbers',
            processing: true,
            serverSide: true,
            destroy: true
        }
    }
    
    constructor() {
        this.handleMouseEvents();
        this.handleSorting();
    }
    
    public getAjaxParameters(){
        return this.settings.ajaxparameters;
    }
    
    public setAjaxParameters(params) {
        this.settings.ajaxparameters = params;
    }
    //function to merge the new option value with default option value
    public initSettings(options) {
        
        this.settings = $.extend(true, {}, this.defaultSettings, options);
        
    }
    
    public initdatatable(tableId, options) {
        let _this = this;
        if (!jQuery().dataTable) {
            return;
        }
        //options merging function
        this.initSettings(options);
        //set the id of table
        this.settings.tableId = tableId;
        instData = $('#' + tableId);
        
        //set the y-axis height 
        if (this.settings.scrollYflag) {
            var yheight = FgInternal.getWindowHeight(this.settings.scrollYadjustvalue);
            var xwidth = this.settings.scrollxValue;
            this.settings.opt.scrollY = yheight + "px";
            this.settings.opt.scrollX = xwidth + "%";
            this.settings.opt.scrollXInner = xwidth + "%";
        }
        //For set the columnDefs
        if (this.settings.columnDefFlag) {
            this.settings.opt.columnDefs = this.settings.columnDefValues;
        }
        //set the column from localstroage
        if (this.settings.ajaxHeader) {
            this.settings.opt.columns = $.parseJSON(localStorage.getItem(this.settings.tableColumnTitleStorageName));
        }
        //set default page length
        if (this.settings.displaylengthflag) {
            this.settings.opt.iDisplayLength = this.settings.displaylength;
        }
        
        //set the fixed column
        if (this.settings.fixedcolumn) {
            this.generateFixedColumn();
        }
        //rearrange the width of last column
        if (this.settings.widthResize) {
          var totalColumn = (_.size($.parseJSON(localStorage.getItem(this.settings.tableColumnTitleStorageName)))) - 1;
              this.settings.opt.columnDefs = [{"width": "100%", "targets": totalColumn}];
        }
        //initial sorting check
        if (this.settings.initialSortingFlag) {
            this.settings.opt.order = [[this.settings.initialsortingColumn, this.settings.initialSortingorder]];
        } else {
            this.settings.opt.aaSorting = [];
        }
        //server side process
        if (this.settings.serverSideprocess) {
            this.settings.opt.serverSide = true;
            this.settings.opt.processing = true;

        } else {
            this.settings.opt.serverSide = false;
            this.settings.opt.processing = true;
        }
        
        if (this.settings.ajaxPath != '') {
            this.settings.opt.ajax = {
                "url": this.settings.ajaxPath,
                "data": function (parameter) {
                    //for setting the document listing parameters
                    if (_this.settings.ajaxparameterflag) {
                        _this.parameterSetting(parameter, _this.getAjaxParameters());
                    }
                    if(_this.settings.showFilter){
                        var filterData = $.parseJSON(localStorage.getItem(_this.settings.tableFilterStorageName));
                        if(filterData !== null){
                            _this.parameterSetting(parameter, filterData);
                        }
                    }
                },
                "type": "POST",
                "dataSrc": function (json) {
                    if (_this.settings.manipulationFlag) {
                        var fn = window[_this.settings.manipulationFunction];
                        //call function
                        fn(json);
                    }

                    return json.aaData;
                }
            };
        }
        
        if(this.settings.dataFlag) {
           this.settings.opt.data=this.settings.data; 
        }
        if(!this.settings.stateSaveFlag) {
           this.settings.opt.stateSave=false; 
        }
        

        //row call back
        this.settings.opt.rowCallback = function (nRow, aData, iDataIndex) {
               if (aData.removedFlag == 1) {
                    $(nRow).addClass('fg-deleted-row');
                }
            //give - value to the null
            $('td', nRow).each(function (index, value) {
        
                if (index == 0 && _this.settings.isCheckbox == true) {
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
                if(_this.settings.opt.responsive && index == 0 && _this.settings.opt.responsiveToggle){
                    $(this).html("");
                }
            });


        };
        //header callback
        this.settings.opt.headerCallback = function (nHead, aData, iStart, iEnd, aiDisplay) {
            $('.dataTable_checkall').uniform();
           // FgInternal.toolTipInit();
        };

        this.settings.opt.drawCallback = function (tablesettings) {
            //stop the pageloading process
            $('.dataTable_checkall').prop('checked', false);
            //to remove check from datatable rows checkbox
            $('.dataClass').prop('checked', false);
            $('.fg-dev-checkedtr').removeClass('fg-dev-checkedtr');
             
            $.uniform.update(".dataTable_checkall");
            $.uniform.update(".dataClass");
            //pop up functionality initializing area
            if (_this.settings.popupFlag) {
                let FgPopOverObj = new FgPopOver();
                FgPopOverObj.init(".fgPopovers", true, false);
                FgPopOverObj.init(".fg-dev-Popovers", true);
                _this.toolTipInit();
            }
            if (_this.settings.hidePaginationOnSinglePageCount) {
                var totalPages = tablesettings._iDisplayLength === -1 ? 0 : Math.ceil( parseInt(tablesettings.fnRecordsTotal()) / tablesettings._iDisplayLength );
                if (parseInt(totalPages) <= 1) {
                    $('#' + tableId + '_paginate').hide();
                } else {
                    $('#' + tableId + '_paginate').show();
                }
            }
            if (_this.settings.hidePagination && tablesettings.fnRecordsTotal() > 0) {
                var len = parseInt($('#' + tableId + ' tr').length);
                if (len < 11) {
                    $('#' + tableId + '_paginate').hide();
                }
            }
            
            //FgActionmenuhandler.init();
            setTimeout(function () {
                //FgCheckBoxClick.init()
            }, 200);

            setTimeout(function () { /*Metronic.stopPageLoading();*/ }, 200);
            //checkbox convert to uniform in fixed column datatable           
            if (!$.isEmptyObject(fc)) {
                if ($(window).width() >= 768) {
                }

                fc.fnRedrawLayout();
            }

            $(".chk_cnt").html('');
            //editdatatable init area
            if (_this.settings.editFlag) {
                //call datatable init area 
                _this.settings.inlineEditCallback();

            }
            //rearrange the column width
            if (!$.isEmptyObject(this.listTable)) {
                setTimeout(function () {
                    //listTable.columns.adjust().fixedColumns().relayout();
                }, 100)
            }

            //To show data count
            if (_this.settings.countDisplayFlag) {
                $("#tcount").html(tablethis.settings.fnRecordsTotal());
            }

            /*Drag/Drop Starts*/
            if (_this.settings.draggableFlag) {
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
            
                
               
               if(!((_this.settings.module== 'CMS'|| _this.settings.module== 'ARTICLE'))) {
                   FgSidebar.droppableEventIconHandling();
                }
            }
            ;
            /*Drag/Drop ends*/

        };
        this.settings.opt.stateLoadCallback = function (tablesetting) {
            var stringified = localStorage.getItem('DataTables_' + _this.settings.tableId + window.location.pathname + window.location.search)
            var oData = JSON.parse(stringified || null);
            if (oData && tablesetting.fnRecordsTotal() < oData.start) {
                oData.start = 0;
            }
            if (oData) {
                $("#fg_dev_member_search").val(oData.search.search);

            }
            return oData;
        };
        this.settings.opt.stateSaveCallback = function (tblsettings, data) {
            localStorage.setItem('DataTables_' + _this.settings.tableId + window.location.pathname + window.location.search, JSON.stringify(data));
            _this.handleNextPreviousOption();
        };

        this.settings.opt.initComplete = function () {

            //stop page loading area
            /*Metronic.stopPageLoading();*/
            // For change the position of  no.of records per page selection drop down box
            if (_this.settings.rowlengthshow) {
                $('#' + _this.settings.rowlengthWrapperdivid).empty();
                $('#' + _this.settings.rowlengthWrapperdivid).show();
                tableid = _this.settings.tableId;
                //for change the position
                $("#" + tableid + "_length").detach().prependTo("#" + _this.settings.rowlengthWrapperdivid);
                //add our own classes to the selectbox
                $("#" + tableid + "_length").find('select').addClass('form-control cl-bs-select');
                $("#" + tableid + "_length").find('select').select2();
                //FgFormTools.handleSelect2();
            }
            if (!$.isEmptyObject(this.listTable)) {
                setTimeout(function () {
                   // this.listTable.columns.adjust().fixedColumns().relayout();
                }, 200)
            }
            if (_this.settings.fixedcolumn) {
                if ($(window).width() >= 768) {
                  this.listTable.columns.adjust();
                }
            }
            _this.settings.tableDrawCallback();

        }
        instData.on( 'length.dt', function ( e, settings, len ) {
           /* Metronic.startPageLoading();*/
        } );
        
        //datatable initializing area  
        this.listTable = $('#' + tableId).DataTable(this.settings.opt);
       //refresh the datatable while resize a page 
        
        $(window).resize(function () { 
            $('#' + tableId).DataTable().columns.adjust().draw(); 
        });
       this.handleTableHover();
       
        return this.listTable;

    
    }
    
    //function to set the parameter of ajax post
    public parameterSetting(parameter, settingValue) {
        
        $.each(settingValue, function (key, value) {
            parameter[key] = value;
            
        })
        return parameter;
    }
    
    public generateFixedColumn() {

        if ($(window).width() >= 768) {
            this.settings.opt.fixedColumns = {
                leftColumns: this.settings.fixedcolumnCount
            }

        }
    }
    
    public handleNextPreviousOption() {
        var nextPreviousOptions = this.settings.nextPreviousOptions;
        if(_.size(nextPreviousOptions) == 0)
            return;
        
        var column = nextPreviousOptions.column;
        var path = nextPreviousOptions.path;
        var key = nextPreviousOptions.key;
        if(typeof this.listTable != 'undefined'){
            var currentDataObj = this.listTable.rows( {order:'current',page:'all',search: 'applied'} ).data();
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
    
    //Function to use the datatable init
    public listdataTableInit(tableId, options) {
        var listTableObj = this.initdatatable(tableId, options);
        setTimeout(function () {
            //$("div.DTFC_LeftBodyLiner").scrollLeft(300);                
        }, 1000);
        return listTableObj;
    }
    
    public datatableSearch() {
        let _this = this;
        $('#' + this.settings.searchTextBox).off("keyup")
        $('#' + this.settings.searchTextBox).on("keyup", function() {
            var searchVal = this.value;
            _this.listTable.search(searchVal).draw();
        })
    }
    
    public getSettings() {
       return this.settings; 
    }    
    
    public setNewValues(newsettings) {
        this.settings = newsettings;
    }
    
    public nl2br(str, is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    }
    
    //for create the mouse over effect on the both fixed column and normal table
    public handleMouseEvents() {
        let trIndex;
        $(document).on({
            mouseenter: function() {
                trIndex = $(this).index() + 1;
                $(".DTFC_ScrollWrapper .dataTable").each(function(index) {
                    $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                        $(this).find("td").addClass("fghover");
                        $(this).find("td").addClass('fg-dev-td-hover');
                    });
                });
                $(".hover-edit").each(function(index) {
                    $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                        $(this).find("td").addClass("fghover");
                        $(this).find("td").addClass('fg-dev-td-hover');
                    });
                });
            },
            mouseleave: function() {
                trIndex = $(this).index() + 1;
                $(".DTFC_ScrollWrapper .dataTable").each(function(index) {
                    $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                        $(this).find("td").removeClass("fghover");
                        // $(this).find("td").css("background", "#fff");
                    });
                });
                $(".hover-edit").each(function(index) {
                    $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                        $(this).find("td").removeClass("fghover");
                        // $(this).find("td").css("background", "#fff");
                    });
                });
                $("table.dataTable").find('.fg-dev-td-hover').removeClass('fg-dev-td-hover');
            },
            drop: function() {
                trIndex = $(this).index() + 1;
                var selCount = $("input.dataClass:checked").length;
                $("table.dataTable").each(function(index) {
                    $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                        if (selCount <= 1) {
                            $(this).find("td").addClass("fg-droppable-color");
                        }
                    });
                });
                $("table.dataTable").removeClass('fg-dev-drag-active');
                $("table.dataTable").find('.fg-dev-td-hover').removeClass('fg-dev-td-hover');

            },
            drag: function(e) {
                //$("table.dataTable").not('.fg-dev-drag-active').addClass('fg-dev-drag-active');
                $("body").addClass('fg-dev-drag-active');
            },
        }, ".dataTables_wrapper tbody tr");
    }
    
    public handleSorting() {
        jQuery.extend(jQuery.fn.dataTableExt.oSort, {
            "null-last-asc": function(a, b) {
                if (a === '' || a === null) {
                    return 1;
                }
                if (b === '' || b === null) {
                    return -1;
                }
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },
            "null-last-desc": function(a, b) {
                if (a === '' || a === null) {
                    return 1;
                }
                if (b === '' || b === null) {
                    return -1;
                }
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            },
            "null-numeric-last-asc": function(a, b) {
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
            "null-numeric-last-desc": function(a, b) {
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
            "hyphen-last-asc": function(a, b) {
                if (a === '-') {
                    return 1;
                }
                if (b === '-') {
                    return -1;
                }
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },
            "hyphen-last-desc": function(a, b) {
                if (a === '-') {
                    return 1;
                }
                if (b === '-') {
                    return -1;
                }
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            },
            //    Used to sort values like '<0.1 MB'
            "less-symbol-asc": function(a, b) {
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
            "less-symbol-desc": function(a, b) {
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
    }
    
    public handleTableHover(){
        if(this.settings['rowHighlight']) {
            $('#'+this.settings.tableId+' tbody').on( 'mouseover', 'tr', function () {
                $(this).addClass('fg-hover'); 
            } );

            $('#'+this.settings.tableId+' tbody').on( 'mouseout', 'tr', function () {
                $(this).removeClass('fg-hover');
            } );
        }
    }
    
      public toolTipInit () {
          let this_=this;
          $('body').off('mouseover click', '.fg-custom-popovers');
        $('body').on('mouseover click', '.fg-custom-popovers', function(e) {
            let _this = $(this),            
                    thisContent = _this.data('content'),
                    posLeft = _this.offset().left-10,
                    posTop = _this.offset().top + 50;
            this_.showTooltip({element: e, content: thisContent, position: [posLeft, posTop]});
            $('.popover .popover-content').width($('.popover').width()-27); 
        });
         $('body').off('mouseout', '.fg-custom-popovers');
        $('body').on('mouseout', '.fg-custom-popovers', function() {
            $('body').find('.custom-popup').hide();            
            $('.popover .popover-content').width('');
        });
    }
    
    public showTooltip (obj:Object) {
        let targetElement = $('body').find('.custom-popup'),
               let elementContent = targetElement.find('.popover-content');
        elementContent.html(obj.content);
        targetElement.css({'left': obj.position[0], 'top': obj.position[1]})
        targetElement.show();
    }
    
};



