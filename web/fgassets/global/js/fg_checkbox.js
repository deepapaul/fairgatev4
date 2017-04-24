/* 
 * Handling check all and check box properties of datatable
 * Used for changing the behavour of action menu
 * Used for changing the behaviour of action menu icon
 */
FgCheckBoxClick = {
    init: function() {
        $(".dataClass").uniform();
        FgCheckBoxClick.handleEmptyDatatableMenu();
        FgCheckBoxClick.handleCheckAll();
        FgCheckBoxClick.handleSingleCheckBox();
    },
    
    handleCheckAll: function(){
        $("body").on('click', ".dataTable_checkall", function() {
            var dataTableType = $(this).attr('data-type');
            //CHECK ALL FUNCTIONALITY HANDLING AREA
            if (this.checked) {
                if (typeof dataTableType !== typeof undefined && dataTableType !== false) {
                        $('#fg_dev_'+dataTableType).find(".table span").addClass('checked');          
                        $('#fg_dev_'+dataTableType).find("tr").each(function(index) {
                            $(this).find("td").addClass("fg-dev-checkedtr");
                            $(this).find("td .checker span").addClass('checked');
                        });

                } else {      
                    if ($(".DTFC_LeftHeadWrapper:visible").length === 1) {
                        $(".DTFC_LeftHeadWrapper span").addClass('checked');                   
                    } else {  
                        $(".table span").addClass('checked');
                    }

                    $("table.dataTable:visible").each(function(index) {
                        $(this).find("tr").each(function(index) {
                            if(!$(this).find("td div").hasClass('disabled')) {
                                $(this).find("td").addClass("fg-dev-checkedtr");                           
                                $(this).find("td .checker span").addClass('checked');
                                $(this).find("td .checker span input").attr('checked', 'checked');
                            }
                        });
                    });
                }
                $('body').addClass('fg-no-hand-icon');
            } else {
                if (typeof dataTableType !== typeof undefined && dataTableType !== false) {
                    $('#fg_dev_'+dataTableType).find(".table span").removeClass('checked');                
                    $('#fg_dev_'+dataTableType).find("tr").each(function(index) {
                        $(this).find("td").removeClass("fg-dev-checkedtr");
                        $(this).find("td .checker span").removeClass('checked');
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
                            $(this).find("td .checker span").removeClass('checked');
                            $(this).find("td .checker span input").removeAttr('checked');
                        });
                    });
                }  
                $('body').removeClass('fg-no-hand-icon');
            }
            $.uniform.update('.dataClass');    
            var n = FgCheckBoxClick.actionMenuUpdate();
            FgCheckBoxClick.iconBehaviourhandling(n, $(this));
        });
    },
    
    handleSingleCheckBox: function(){
        $("body").on('click', ".dataTable .dataClass, .calendarList .dataClass", function(e) { //event added to '.calendarList .dataClass' for calendar list.  
            trIndex = $(this).closest('tr').index()+1;        
            if (this.checked) {                
                $("table.dataTable:visible").each(function(index) {
                    $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                        $(this).find("td").addClass("fg-dev-checkedtr");
                        $(this).find("td .checker span").addClass('checked'); 
                    });
                });
            } else {
                $("table.dataTable:visible").each(function(index) {
                    $(this).find("tr:eq(" + trIndex + ")").each(function(index) {
                        $(this).find("td").removeClass("fg-dev-checkedtr");
                         $(this).find("td .checker span").removeClass('checked');

                    });
                });
            } 
            
            FgCheckBoxClick.addNoHandIconClass();
            var n = FgCheckBoxClick.actionMenuUpdate();
            FgCheckBoxClick.updateCheckAll(n);
            FgCheckBoxClick.iconBehaviourhandling(n, $(this));
        });       
    },
    addNoHandIconClass : function(){
        //No hand icon when >1 pages selected - only for cms
        var count = parseInt($(".DTFC_LeftWrapper .chk_cnt").text(), 10); 
        if(count > 1)
            $('body').addClass('fg-no-hand-icon');
        else
            $('body').removeClass('fg-no-hand-icon');
    },
    /**
     * Method to check check all box if number of selected checkbox == total number of checkboxes
     * @param {int} n number of chckbox selected
     */
    updateCheckAll: function(n) {        
        if ($(".dataClass:visible").length == n) {
            $(".dataTable_checkall:visible").parent().addClass('checked');
        } else {
            $(".dataTable_checkall:visible").parent().removeClass('checked');
        }
    },
    
    FgCheckBoxClickCounthandling: function(){        
        n = (isNaN(n = parseInt($(".chk_cnt").html(), 10)) ? 0 : n);
        if (e.checked == true) {
            var total = n + 1;
        } else {
            var total = (n - 1) == 0 ? '' : (n - 1);
        }
        $(".chk_cnt").html(total);

        if (total === '') {
            $(".chk_cnt").parent().find('#uniform-check_all > span').removeClass('checked');
        }
        return total;
        
    },
    
    FgClearCheckAll: function(){    
        setTimeout(function(){  
            $(".dataClass").prop("checked", false);
                $(".dataTable_checkall").prop("checked", true);
                $(".dataTable_checkall").trigger('click');
                $(".chk_cnt").html('');
        },100);       
    },
    
    //CALCULATING SELECTED CHECKBOX COUNT AND SHOW THE COUNT
    actionMenuUpdate: function(){
        $(".fgContactdrop .fa").removeClass('fa-users').addClass('fa-bars');
        if (typeof dataTableType !== typeof undefined && dataTableType !== false) {
            var n = ($('#fg_dev_'+dataTableType+' input.dataClass:checked').length);
        } else {
            if ($(".DTFC_LeftHeadWrapper:visible").length === 1) {
                var n = ($(".DTFC_LeftBodyWrapper input.dataClass:checked").length);

            } else {
                var n = ($("input.dataClass:visible:checked").length);
            }
        }
        if (n <= 0) {
            $(".chk_cnt").html('');            
            scope.$apply(function(){
                scope.menuType = 0;
            }); 
        } else if (n === 1) {
             $(".chk_cnt").html(n);            
            scope.$apply(function(){
                scope.menuType = 1;
            }); 
        } else {
            $(".chk_cnt").html(n);            
            scope.$apply(function(){
                scope.menuType = 2;
            }); 
        }
        
        return n;
    },

    //ICON BEHAVIOUR HANDLING
    //if fg-dev-avoidicon-behaviour class is exist , icon behaviour of action menu is same
    iconBehaviourhandling: function(n, selCheckbox){  
        if (!selCheckbox.hasClass('fg-dev-avoidicon-behaviour')) {
            if (n > 1) {
                $(".fgContactdrop .fa").removeClass('fa-bars').addClass('fa-users');
                $('#fgdropmenu').html($("#fgmultiSelectMenu").html());

            } else if (n == 1) {

                $(".fgContactdrop .fa").removeClass('fa-bars').addClass('fa-user');
                $(".fgContactdrop .fa").removeClass('fa-users').addClass('fa-user');
                $('#fgdropmenu').html($("#fgsingleSelectMenu").html());
            }
            else {
                $(".fgContactdrop .fa").removeClass('fa-users').addClass('fa-bars');
                $('#fgdropmenu').html($("#fgdefaultMenu").html());
            }
        } else {
            if (n > 1) {
                $('#fgdropmenu').html($("#fgmultiSelectMenu").html());
            } else if (n == 1) {
                $('#fgdropmenu').html($("#fgsingleSelectMenu").html());
            }
            else {
                $('#fgdropmenu').html($("#fgdefaultMenu").html());
            }
        }
    },

    handleEmptyDatatableMenu: function() {
        FgCheckBoxClick.actionMenuUpdate();
        var datatableRowCnt = $("input.dataClass:visible").length;
        if (datatableRowCnt <= 0) {
            $('.fgContactdrop').next('.action-drop-menu').addClass('fg-dev-table-empty');
        } else {
            $('.fgContactdrop').next('.action-drop-menu').removeClass('fg-dev-table-empty');
        }
    }
};