/*
 * Using Fg-Check-All javascript in all areas
 * 
 * Hanlde Check all functionality in all areas.
 * Hanle Single select check box functionality
 * Handle count against the checkall
 */
$(function() {
    FgCheckBoxClick = {
        init: function(defaultClass) {
           // $(".dataClass").uniform();
            FgCheckBoxClick.handleCheckAll();
            FgCheckBoxClick.handleSingleCheckBox(defaultClass);
        },
        /**
         * Handle Check all functionality in all areas.
         * 
         */
        handleCheckAll: function() {
            //for select the all check box at one click
            $("body").on('click', ".dataTable_checkall", function() {
                var dataTableType = $(this).attr('data-type');
                //CHECK ALL FUNCTIONALITY HANDLING AREA
                if (this.checked) {
                    if (typeof dataTableType !== typeof undefined && dataTableType !== false) {
                        $('#fg_dev_' + dataTableType).find(".table span").addClass('checked');
                        $('#fg_dev_' + dataTableType).find("tr").each(function(index) {
                            $(this).find("td").addClass("fg-dev-checkedtr");
                        });

                    } else {
                        if ($(".DTFC_LeftHeadWrapper:visible").length === 1) {
                            $(".DTFC_LeftHeadWrapper span").addClass('checked');
                        } else {
                            $(".table span").addClass('checked');
                        }

                        $("table.dataTable:visible").each(function(index) {
                            $(this).find("tr").each(function(index) {
                                $(this).find("td").addClass("fg-dev-checkedtr");
                            });
                        });
                    }
                } else {
                    if (typeof dataTableType !== typeof undefined && dataTableType !== false) {
                        $('#fg_dev_' + dataTableType).find(".table span").removeClass('checked');
                        $('#fg_dev_' + dataTableType).find("tr").each(function(index) {
                            $(this).find("td").removeClass("fg-dev-checkedtr");
                        });
                    } else {
                        if ($(".DTFC_LeftHeadWrapper:visible").length === 1) {
                            $(".DTFC_LeftHeadWrapper span").removeClass('checked');
                        } else {
                            $(".table span").removeClass('checked');
                        }

                        $("table.dataTable:visible").each(function(index) {
                            $(this).find("tr").each(function(index) {
                                $(this).find("td").removeClass("fg-dev-checkedtr");
                            });
                        });
                    }

                }
                //CHECK ALL FUNCTIONALITY HANDLING AREA

                //BASED ON CHECKING ADD THE CHECKED UNCHECKED BEHAVOIUS FOR CHECKBOXES
                if (typeof dataTableType !== typeof undefined && dataTableType !== false) {
                    $('#fg_dev_' + dataTableType).find(".table .dataClass").attr('checked', this.checked);
                } else {
                    if ($(".DTFC_LeftHeadWrapper:visible").length === 1) {
                        $('.DTFC_LeftBodyWrapper .dataClass').attr('checked', this.checked);
                    } else {
                        $(".table .dataClass").attr('checked', this.checked);
                    }
                }
                $.uniform.update('.dataClass');
                //BASED ON CHECKING ADD THE CHECKED UNCHECKED BEHAVOIUS FOR CHECKBOXES
                checkedRowCount = calculateCheckBoxCount.init($(this));

                //ICON BEHAVIOUR HANDLING
                FgCheckBoxClick.iconBehaviourhandling($(this));
            });
        },
        //ICON BEHAVIOUR HANDLING
        iconBehaviourhandling: function(selCheckbox) {
            //if fg-dev-avoidicon-behaviour class is exist , icon behaviour of action menu is same
            if (!selCheckbox.hasClass('fg-dev-avoidicon-behaviour')) {
                if (checkedRowCount > 1) {
                    $(".fgContactdrop .fa").removeClass('fa-bars').addClass('fa-users');
                    $('#fgdropmenu').html($("#fgmultiSelectMenu").html());

                } else if (checkedRowCount == 1) {

                    $(".fgContactdrop .fa").removeClass('fa-bars').addClass('fa-user');
                    $(".fgContactdrop .fa").removeClass('fa-users').addClass('fa-user');
                    $('#fgdropmenu').html($("#fgsingleSelectMenu").html());
                }
                else {
                    $(".fgContactdrop .fa").removeClass('fa-users').addClass('fa-bars');
                    $('#fgdropmenu').html($("#fgdefaultMenu").html());
                }
            } else {
                (checkedRowCount > 1)?  $('#fgdropmenu').html($("#fgmultiSelectMenu").html()) :
                (checkedRowCount == 1)? $('#fgdropmenu').html($("#fgsingleSelectMenu").html()):
                                        $('#fgdropmenu').html($("#fgdefaultMenu").html());
            }
        },
        /**
         * Handle Single select check box functionality
         * @param string defaultClass
         * 
         */
        handleSingleCheckBox: function(defaultClass) {

            $("body").on('click', "." + defaultClass + " .dataClass", function(e) {
                 trIndex = $(this).closest('tr').index()+1; 
                if (this.checked) {
                    $("table.dataTable:visible").each(function(index) {
                        $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                            $(this).find("td").addClass("fg-dev-checkedtr");
                            $(this).find("td .checker span").addClass('checked'); 
                        });
                    });
                    $(this).closest('tr').each(function(index) {
                        $(this).find("td").addClass("fg-dev-checkedtr");
                    });
                } else {
                    $("table.dataTable:visible").each(function(index) {
                        $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                            $(this).find("td").removeClass("fg-dev-checkedtr");
                             $(this).find("td .checker span").removeClass('checked');

                        });
                    });
                    $(this).closest('tr').each(function(index) {
                        $(this).find("td").removeClass("fg-dev-checkedtr");

                    });
                }
                
                checkAll = FgCheckBoxClick.getCheckAll($(this));
                checkedRowCount = calculateCheckBoxCount.init(checkAll);
                //ICON BEHAVIOUR HANDLING
                FgCheckBoxClick.iconBehaviourhandling($(this));
            });
        },
        //find check all obj
        getCheckAll: function(_this){
            //find the check all obj 
                var tableParent = $(_this).closest('.dataTables_scroll');
                var checkAll = tableParent.find('.dataTables_scrollHead table thead .dataTable_checkall');
                if (checkAll.length <= 0) { /* for fixed datatable */
                    var checkAll = $(_this).parents().find('.dataTables_wrapper').find('.dataTable_checkall');
                }
                
                return checkAll;
        }
    };



    //count handling
    FgCheckBoxClickCounthandling = {
        init: function(e) {
            checkedRowCount = (isNaN(checkedRowCount = parseInt($(".chk_cnt").html(), 10)) ? 0 : checkedRowCount);
            if (e.checked == true) {
                var total = checkedRowCount + 1;
            } else {
                var total = (checkedRowCount - 1) == 0 ? '' : (checkedRowCount - 1);
            }
            $(".chk_cnt").html(total);

            if (total === '') {
                $(".chk_cnt").parent().find('#uniform-check_all > span').removeClass('checked');
            }
            return total;
        }
    };
    //clear check all
    FgClearCheckAll = {
        init: function() {
            setTimeout(function() {
                $(".dataClass").prop("checked", false);
                $(".dataTable_checkall").prop("checked", true);
                $(".dataTable_checkall").trigger('click');
                $(".chk_cnt").html('');
            }, 100)
        }
    };

    //CALCULATING SELECTED CHECKBOX COUNT AND SHOW THE COUNT
    calculateCheckBoxCount = {
        init: function(thisSelector) {
            var totalRows = 0;
            var dataTableType = $(thisSelector).attr('data-type');
            if (typeof dataTableType !== typeof undefined && dataTableType !== false) {
                checkedRowCount = ($('#fg_dev_' + dataTableType + ' input.dataClass:visible:checked').length);
                totalRows = $('#fg_dev_' + dataTableType + ' input.dataClass:visible').length;
            } else {
                if ($(".DTFC_LeftHeadWrapper:visible").length === 1) {
                    checkedRowCount = ($(".DTFC_LeftBodyWrapper input.dataClass:visible:checked").length);
                    totalRows = $(".DTFC_LeftBodyWrapper input.dataClass:visible").length;
                } else {
                    checkedRowCount = ($("input.dataClass:visible:checked").length);
                    totalRows = $("input.dataClass:visible").length;
                }
            }

            if (checkedRowCount <= 0) {
                var theadParent = thisSelector.closest('th');
                theadParent.find('.chk_cnt').html('');
                thisSelector.parent().removeClass('checked');
            } else {
                var theadParent = thisSelector.closest('th');
                theadParent.find('.chk_cnt').html(checkedRowCount);
                if (totalRows != checkedRowCount) {
                    thisSelector.parent().removeClass('checked');
                } else {
                    thisSelector.parent().addClass('checked');
                }
            }

            return checkedRowCount;
        }
    };
});