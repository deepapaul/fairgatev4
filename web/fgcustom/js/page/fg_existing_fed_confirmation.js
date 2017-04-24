var x,y;
FgConfirmations = {
    confirmationsListTable : '',
    confirmationsLogTable : '',
    activeTabName : '',
    getInitialOpt :function(type){
        var opt = FgCommon.setinitialOpt();
        opt.ajax = $('table#confirmations-'+type+'-table').attr('data-ajax-path');
        opt.deferRender = true;
        opt.order = [[1, "desc"]];
        opt.dom = "<'col-md-12't>";
        opt.scrollX = $('table#confirmations-'+type+'-table').attr('xWidth') + "%";
        opt.sScrollXInner = $('table#confirmations-'+type+'-table').attr('xWidth') + "%";
        opt.scrollY = FgCommon.getWindowHeight(275) + "px";
        opt.stateSave = true;
        opt.stateDuration = 60 * 60 * 24;
        opt.lengthChange = false;
        opt.iDisplayLength = 10;
        opt.serverSide = false;
        opt.processing = false;
        opt.columnDefs = (type == 'list') ? columnDefs1 : columnDefs2;
        opt.retrieve = true;

        opt.fnDrawCallback = function() {
            $(".dataClass").uniform();
            $("#check_all").attr('checked', false);
            $("table").find(".chk_cnt").html('');
        };
        if(type == 'list'){
            opt.fnInitComplete = function() {
                setTimeout(function () {
                    FgCheckBoxClick.init('confirmationsListDatatable');
                }, 200);
            }
        }
        return opt;
    },
    initDatatable : function() {
        var options = FgConfirmations.getInitialOpt('list');
        FgConfirmations.confirmationsListTable = $('table#confirmations-list-table').DataTable(options);
        var options = FgConfirmations.getInitialOpt('log');
        FgConfirmations.confirmationsLogTable = $('table#confirmations-log-table').DataTable(options);
    }
}
$(document).ready(function() {
    
    FgConfirmations.initDatatable();
    FgMoreMenu.initClientSideWithNoError('data-tabs', 'data-tabs-content');
    
    $('#paneltab li ').on('click', function() {
        if ($(this).attr('name') == 'confirmations-log-table') {
            setTimeout(function () {
                    FgConfirmations.confirmationsLogTable.columns.adjust().draw();
                    
                }, 100);
            $('button.fgContactdrop').attr('disabled', 'disabled');
        } else {
            setTimeout(function () {
                    FgConfirmations.confirmationsListTable.columns.adjust().draw();
                }, 100);
            $('button.fgContactdrop').removeAttr('disabled');
        }
    });
    
    //handle action menu starts
    if (FgConfirmations.activeTabName == 'list') {
        var actionMenuText = {'active' : {'none': actionMenuNoneSelectedText, 'single': actionMenuSingleSelectedText, 'multiple': actionMenuMultipleSelectedText}};
        FgSidebar.dynamicMenus.push({actionmenu: actionMenuText});
    }
    
    //contact detail popup for creations
    $('body').on('click', '.fg-creations-new-contact', function(e) {
        var dataUrl = $(this).attr('data-url');
        showPopup('newcontactdetail', { 'urlpath' : dataUrl });
    });
});
