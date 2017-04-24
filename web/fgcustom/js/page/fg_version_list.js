FgVersionList = {
    versionTable : '',
    getInitialOpt :function(){
        var opt = FgCommon.setinitialOpt();
        opt.ajax = $('table.dataTable-version-list').attr('data-ajax-path');
        opt.deferRender = true;
        opt.order = [[1, "desc"]];
        opt.dom = "<'col-md-12't>";
        opt.scrollX = $('table.dataTable-version-list').attr('xWidth') + "%";
        opt.sScrollXInner = $('table.dataTable-version-list').attr('xWidth') + "%";
        opt.scrollY = FgCommon.getWindowHeight(275) + "px";
        opt.stateSave = true;
        opt.stateDuration = 60 * 60 * 24;
        opt.lengthChange = false;
        opt.iDisplayLength = 10;
        opt.serverSide = false;
        opt.processing = false;
        opt.columnDefs = columnDefs;
        opt.retrieve = true;

        opt.fnDrawCallback = function() {
            $(".dataClass").uniform();
            $("#check_all").attr('checked', false);
//            $("#check_all").uniform();
            $("table").find(".chk_cnt").html('');
            $(".fgContactdrop .fa").removeClass('fa-users').removeClass('fa-user').addClass('fa-bars');
        };
        
        return opt;
    },
    initDatatable : function() {
        var options = FgVersionList.getInitialOpt();
        FgVersionList.versionTable = $('table.dataTable-version-list').DataTable(options);
    }
}
$(document).ready(function() {
    $("body").off('click', ".dataTable_checkall");
    $("body").on('click', ".dataTable_checkall", function() {
        if (this.checked) {
            $(this).parents("table").find("span").addClass('checked');
            $(this).parents("table").each(function(index) {
                $(this).find("tr").each(function(index) {
                    $(this).find("td").addClass("fg-dev-checkedtr");
                });
            });
        } else {
            $(this).parents("table").find("span").removeClass('checked');
            $(this).parents("table").each(function(index) {
                $(this).find("tr").each(function(index) {
                    $(this).find("td").removeClass("fg-dev-checkedtr");
                });
            });
        }
        $('.dataClass').attr('checked', this.checked);
        $.uniform.update('.dataClass');
        updateCheckedCount(this);
    });
    $("body").off('click', ".dataClass");
    $("body").on('click', ".dataTable-version-list .dataClass", function() {
        trIndex = $(this).parents('tr');
        if (this.checked) {
            trIndex.find("td").addClass("fg-dev-checkedtr");
        } else {
            trIndex.find("td").removeClass("fg-dev-checkedtr");
        }
        updateCheckedCount(this);
    });
});

function updateCheckedCount(e) {
    var n = ($("input.dataClass:checked").length);
    if (n <= 0) {
        $("table").find(".chk_cnt").html('');
    } else {
        $("table").find(".chk_cnt").html(n);
    }
    var classnameIndex = e.getAttribute("class").search("fg-dev-avoidicon-behaviour");
    //if fg-dev-avoidicon-behaviour class is exist , icon behaviour of action menu is same
    if (classnameIndex != -1) {
        if (n > 1) {
            $('#fgdropmenu').html($("#fgmultiSelectMenu").html());
        } else if (n == 1) {
            $('#fgdropmenu').html($("#fgsingleSelectMenu").html());
        } else {
            $('#fgdropmenu').html($("#fgdefaultMenu").html());
        }
    }
}

//handle action menu starts
var actionMenuText = {'active' : {'none': actionMenuNoneSelectedText, 'single': actionMenuSingleSelectedText, 'multiple': actionMenuMultipleSelectedText}};
FgSidebar.dynamicMenus.push({actionmenu: actionMenuText});


