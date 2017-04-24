var attr = $('table.dataTable-confirmation').attr('changeDefaultRow');
if (typeof attr !== 'undefined' && attr !== false) {
    var rowCount = 50;
    var disOrder = [[1, "asc"]];
} else {
    var rowCount = 10;
    var disOrder = [[1, "desc"]];
}
$(document).ready(function() {
    var changesCount = $('span#fg-subscriber-count').text();
    $('span#fg-dev-confirmchanges-count').each(function(){
        $(this).text(changesCount);
    });

    var opt = {
        ajax: {'url': $('table.dataTable-confirmation').attr('data-ajax-path'), 'data': {'changesCount': changesCount}},
        deferRender: true,
        order: disOrder,
        dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4'><'col-md-8'p>",
        scrollCollapse: true,
        paging: false,
        autoWidth: true,
        sScrollX: $('table.dataTable-confirmation').attr('xWidth') + "%",
        sScrollXInner: $('table.dataTable-confirmation').attr('xWidth') + "%",
        scrollY: FgCommon.getWindowHeight(275) + "px",
        stateDuration: 60 * 60 * 24,
        lengthChange: true,
        sServerMethod: "POST",
        iDisplayLength: rowCount,
        lengthMenu: [10, 20, 50, 100, 200],
        pagingType: "full_numbers",
        language: {
            sSearch: "<span>" + datatabletranslations['data_Search'] + ":</span> ",
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
        columnDefs: columnDefs,
        fnDrawCallback: function() {
            Breadcrumb.load();
            $(".dataClass").uniform();
            $("#check_all").attr('checked', false);
            $("#check_all").uniform();
            $("table").find(".chk_cnt").html('');
            $(".fgContactdrop .fa").removeClass('fa-users').removeClass('fa-user').addClass('fa-bars');
        },
        fnInitComplete : function() {
            setTimeout(function () {
                FgCheckBoxClick.init('dataTable-confirmation');
            }, 200);
        }
    };
    opt.serverSide = false;
    opt.processing = false;
   
    confirmTable = $('table.dataTable-confirmation').DataTable(opt);

    $("body").on('click', "ul#data-tabs li a[data-url]", function() {
        document.location = $(this).attr('data-url');
    });
});

//handle action menu starts
if (actionType == 'changes') {
    var actionMenuText = {'active' : {'none': actionMenuNoneSelectedText, 'single': actionMenuSingleSelectedText, 'multiple': actionMenuMultipleSelectedText}};
    FgSidebar.dynamicMenus.push({actionmenu: actionMenuText});
}
