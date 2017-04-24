var fc = {};
var FgWebsiteDatatable = (function () {
    function FgWebsiteDatatable() {
        this.listTable = {};
        this.settings = {};
        this.defaultSettings = {
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
            tableDrawCallback: function () { },
            ajaxparameters: {},
            nextPreviousOptions: {},
            searchTextBox: "fg_dev_member_search",
            rowHighlight: true,
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
                processing: true,
                serverSide: true,
                destroy: true
            }
        };
        this.handleMouseEvents();
        this.handleSorting();
    }
    FgWebsiteDatatable.prototype.getAjaxParameters = function () {
        return this.settings.ajaxparameters;
    };
    FgWebsiteDatatable.prototype.setAjaxParameters = function (params) {
        this.settings.ajaxparameters = params;
    };
    FgWebsiteDatatable.prototype.initSettings = function (options) {
        this.settings = $.extend(true, {}, this.defaultSettings, options);
    };
    FgWebsiteDatatable.prototype.initdatatable = function (tableId, options) {
        var _this = this;
        if (!jQuery().dataTable) {
            return;
        }
        this.initSettings(options);
        this.settings.tableId = tableId;
        instData = $('#' + tableId);
        if (this.settings.scrollYflag) {
            var yheight = FgInternal.getWindowHeight(this.settings.scrollYadjustvalue);
            var xwidth = this.settings.scrollxValue;
            this.settings.opt.scrollY = yheight + "px";
            this.settings.opt.scrollX = xwidth + "%";
            this.settings.opt.scrollXInner = xwidth + "%";
        }
        if (this.settings.columnDefFlag) {
            this.settings.opt.columnDefs = this.settings.columnDefValues;
        }
        if (this.settings.ajaxHeader) {
            this.settings.opt.columns = $.parseJSON(localStorage.getItem(this.settings.tableColumnTitleStorageName));
        }
        if (this.settings.displaylengthflag) {
            this.settings.opt.iDisplayLength = this.settings.displaylength;
        }
        if (this.settings.fixedcolumn) {
            this.generateFixedColumn();
        }
        if (this.settings.widthResize) {
            var totalColumn = (_.size($.parseJSON(localStorage.getItem(this.settings.tableColumnTitleStorageName)))) - 1;
            this.settings.opt.columnDefs = [{ "width": "100%", "targets": totalColumn }];
        }
        if (this.settings.initialSortingFlag) {
            this.settings.opt.order = [[this.settings.initialsortingColumn, this.settings.initialSortingorder]];
        }
        else {
            this.settings.opt.aaSorting = [];
        }
        if (this.settings.serverSideprocess) {
            this.settings.opt.serverSide = true;
            this.settings.opt.processing = true;
        }
        else {
            this.settings.opt.serverSide = false;
            this.settings.opt.processing = true;
        }
        if (this.settings.ajaxPath != '') {
            this.settings.opt.ajax = {
                "url": this.settings.ajaxPath,
                "data": function (parameter) {
                    if (_this.settings.ajaxparameterflag) {
                        _this.parameterSetting(parameter, _this.getAjaxParameters());
                    }
                    if (_this.settings.showFilter) {
                        var filterData = $.parseJSON(localStorage.getItem(_this.settings.tableFilterStorageName));
                        if (filterData !== null) {
                            _this.parameterSetting(parameter, filterData);
                        }
                    }
                },
                "type": "POST",
                "dataSrc": function (json) {
                    if (_this.settings.manipulationFlag) {
                        var fn = window[_this.settings.manipulationFunction];
                        fn(json);
                    }
                    return json.aaData;
                }
            };
        }
        if (this.settings.dataFlag) {
            this.settings.opt.data = this.settings.data;
        }
        if (!this.settings.stateSaveFlag) {
            this.settings.opt.stateSave = false;
        }
        this.settings.opt.rowCallback = function (nRow, aData, iDataIndex) {
            if (aData.removedFlag == 1) {
                $(nRow).addClass('fg-deleted-row');
            }
            $('td', nRow).each(function (index, value) {
                if (index == 0 && _this.settings.isCheckbox == true) {
                    $(this).addClass('fg-checkbox-td');
                }
                if ((aData.newlyaddedFlag == 1) || (aData.Function_original == '' && aData.Function_ADDED != '')) {
                    $(this).addClass('fg-new-member');
                }
                else if ((aData.removedFlag == 0) && (aData.Function_intersect == '')) {
                    $(this).addClass('fg-remove-member');
                }
                if ($(this).html() == '' || $(this).html() == null) {
                    $(this).html("-");
                }
                if (_this.settings.opt.responsive && index == 0 && _this.settings.opt.responsiveToggle) {
                    $(this).html("");
                }
            });
        };
        this.settings.opt.headerCallback = function (nHead, aData, iStart, iEnd, aiDisplay) {
            $('.dataTable_checkall').uniform();
        };
        this.settings.opt.drawCallback = function (tablesettings) {
            $('.dataTable_checkall').prop('checked', false);
            $('.dataClass').prop('checked', false);
            $('.fg-dev-checkedtr').removeClass('fg-dev-checkedtr');
            $.uniform.update(".dataTable_checkall");
            $.uniform.update(".dataClass");
            if (_this.settings.popupFlag) {
                var FgPopOverObj = new FgPopOver();
                FgPopOverObj.init(".fgPopovers", true, false);
                FgPopOverObj.init(".fg-dev-Popovers", true);
                _this.toolTipInit();
            }
            if (_this.settings.hidePaginationOnSinglePageCount) {
                var totalPages = tablesettings._iDisplayLength === -1 ? 0 : Math.ceil(parseInt(tablesettings.fnRecordsTotal()) / tablesettings._iDisplayLength);
                if (parseInt(totalPages) <= 1) {
                    $('#' + tableId + '_paginate').hide();
                }
                else {
                    $('#' + tableId + '_paginate').show();
                }
            }
            if (_this.settings.hidePagination && tablesettings.fnRecordsTotal() > 0) {
                var len = parseInt(tablesettings.fnRecordsTotal());                
                if (len < 11) {
                    $('#' + tableId + '_paginate').hide();
                }
            }
            setTimeout(function () {
            }, 200);
            setTimeout(function () { }, 200);
            if (!$.isEmptyObject(fc)) {
                if ($(window).width() >= 768) {
                }
                fc.fnRedrawLayout();
            }
            $(".chk_cnt").html('');
            if (_this.settings.editFlag) {
                _this.settings.inlineEditCallback();
            }
            if (!$.isEmptyObject(this.listTable)) {
                setTimeout(function () {
                }, 100);
            }
            if (_this.settings.countDisplayFlag) {
                $("#tcount").html(tablethis.settings.fnRecordsTotal());
            }
            if (_this.settings.draggableFlag) {
                var insideMain = false;
                $(".dataTables_scrollBody").droppable({
                    over: function () {
                        insideMain = true;
                    },
                    out: function () {
                        insideMain = false;
                    }
                });
                $(".dataTable tr .fg-sort").draggable({
                    cursorAt: { top: 8, left: -20 },
                    helper: function (event) {
                        var count;
                        if ($("input.dataClass:checked").length > 0) {
                            count = parseInt($("i.chk_cnt").html());
                        }
                        else {
                            count = 1;
                        }
                        return $("<div class='ui-widget-header'><span class='fg-drag-count'>" + count + "</span></div>");
                    },
                    containment: "body"
                });
                if (!((_this.settings.module == 'CMS' || _this.settings.module == 'ARTICLE'))) {
                    FgSidebar.droppableEventIconHandling();
                }
            }
            ;
        };
        this.settings.opt.stateLoadCallback = function (tablesetting) {
            var stringified = localStorage.getItem('DataTables_' + _this.settings.tableId + window.location.pathname + window.location.search);
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
            if (_this.settings.rowlengthshow) {
                $('#' + _this.settings.rowlengthWrapperdivid).empty();
                $('#' + _this.settings.rowlengthWrapperdivid).show();
                tableid = _this.settings.tableId;
                $("#" + tableid + "_length").detach().prependTo("#" + _this.settings.rowlengthWrapperdivid);
                $("#" + tableid + "_length").find('select').addClass('form-control cl-bs-select');
                $("#" + tableid + "_length").find('select').select2();
            }
            if (!$.isEmptyObject(this.listTable)) {
                setTimeout(function () {
                }, 200);
            }
            if (_this.settings.fixedcolumn) {
                if ($(window).width() >= 768) {
                    this.listTable.columns.adjust();
                }
            }
            _this.settings.tableDrawCallback();
        };
        instData.on('length.dt', function (e, settings, len) {
        });
        this.listTable = $('#' + tableId).DataTable(this.settings.opt);
        $(window).resize(function () {
            $('#' + tableId).DataTable().columns.adjust().draw();
        });
        this.handleTableHover();
        return this.listTable;
    };
    FgWebsiteDatatable.prototype.parameterSetting = function (parameter, settingValue) {
        $.each(settingValue, function (key, value) {
            parameter[key] = value;
        });
        return parameter;
    };
    FgWebsiteDatatable.prototype.generateFixedColumn = function () {
        if ($(window).width() >= 768) {
            this.settings.opt.fixedColumns = {
                leftColumns: this.settings.fixedcolumnCount
            };
        }
    };
    FgWebsiteDatatable.prototype.handleNextPreviousOption = function () {
        var nextPreviousOptions = this.settings.nextPreviousOptions;
        if (_.size(nextPreviousOptions) == 0)
            return;
        var column = nextPreviousOptions.column;
        var path = nextPreviousOptions.path;
        var key = nextPreviousOptions.key;
        if (typeof this.listTable != 'undefined') {
            var currentDataObj = this.listTable.rows({ order: 'current', page: 'all', search: 'applied' }).data();
            var currentData = _.pluck(currentDataObj, column).join();
            if (nextPreviousOptions.currentData != currentData) {
                nextPreviousOptions.currentData = currentData;
                $.ajax({
                    type: "POST",
                    url: path,
                    data: { 'key': key, 'id': currentData }
                });
            }
        }
    };
    FgWebsiteDatatable.prototype.listdataTableInit = function (tableId, options) {
        var listTableObj = this.initdatatable(tableId, options);
        setTimeout(function () {
        }, 1000);
        return listTableObj;
    };
    FgWebsiteDatatable.prototype.datatableSearch = function () {
        var _this = this;
        $('#' + this.settings.searchTextBox).off("keyup");
        $('#' + this.settings.searchTextBox).on("keyup", function () {
            var searchVal = this.value;
            _this.listTable.search(searchVal).draw();
        });
    };
    FgWebsiteDatatable.prototype.getSettings = function () {
        return this.settings;
    };
    FgWebsiteDatatable.prototype.setNewValues = function (newsettings) {
        this.settings = newsettings;
    };
    FgWebsiteDatatable.prototype.nl2br = function (str, is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    };
    FgWebsiteDatatable.prototype.handleMouseEvents = function () {
        var trIndex;
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
                    });
                });
                $(".hover-edit").each(function (index) {
                    $(this).find("tr:eq(" + trIndex + ")").each(function (index) {
                        $(this).find("td").removeClass("fghover");
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
                $("body").addClass('fg-dev-drag-active');
            },
        }, ".dataTables_wrapper tbody tr");
    };
    FgWebsiteDatatable.prototype.handleSorting = function () {
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
    };
    FgWebsiteDatatable.prototype.handleTableHover = function () {
        if (this.settings['rowHighlight']) {
            $('#' + this.settings.tableId + ' tbody').on('mouseover', 'tr', function () {
                $(this).addClass('fg-hover');
            });
            $('#' + this.settings.tableId + ' tbody').on('mouseout', 'tr', function () {
                $(this).removeClass('fg-hover');
            });
        }
    };
    FgWebsiteDatatable.prototype.toolTipInit = function () {
        var this_ = this;
        $('body').off('mouseover click', '.fg-custom-popovers');
        $('body').on('mouseover click', '.fg-custom-popovers', function (e) {
            var _this = $(this), thisContent = _this.data('content'), posLeft = _this.offset().left - 10, posTop = _this.offset().top + 50;
            this_.showTooltip({ element: e, content: thisContent, position: [posLeft, posTop] });
            $('.popover .popover-content').width($('.popover').width() - 27);
        });
        $('body').off('mouseout', '.fg-custom-popovers');
        $('body').on('mouseout', '.fg-custom-popovers', function () {
            $('body').find('.custom-popup').hide();
            $('.popover .popover-content').width('');
        });
    };
    FgWebsiteDatatable.prototype.showTooltip = function (obj) {
        var targetElement = $('body').find('.custom-popup'), let = elementContent = targetElement.find('.popover-content');
        elementContent.html(obj.content);
        targetElement.css({ 'left': obj.position[0], 'top': obj.position[1] });
        targetElement.show();
    };
    return FgWebsiteDatatable;
}());
;
