var subscriberTable = '';

var attr = $('table.dataTable-subscriber').attr('changeDefaultRow');
if (typeof attr !== 'undefined' && attr !== false) {
    var rowCount = 50;
    var disOrder = [[1, "asc"]];
} else {
    var rowCount = 10;
    var disOrder = [[1, "desc"]];
}
$(document).ready(function() {

    //disable inline edit on page refresh
    if ($("#inlineEditSubscriber").is(':checked')) {
        $("#inlineEditSubscriber").attr("checked", false);
        jQuery.uniform.update('#inlineEditSubscriber');
        inlineEditFlag = (($("#inlineEditSubscriber").length > 0 && $('#inlineEditSubscriber').is(':checked'))) ? 1 : 0;
    }

    var datatableOptions = {
                    fixedcolumn: false,
                    columnDefFlag: true,
                    columnDefValues: columnDefs,               
                    rowlengthshow: false,
                    tableId: datatableId,                    
                    popupFlag: false,
                    widthResize: false,
                    ajaxHeader: false,
                    displaylength: 20,
                    serverSideprocess: datatableServerSideprocess,
                    ajaxPath: ajaxPath,                    
                    ajaxparameterflag: true,
                    hasTooltip : false,
                    isCheckbox:false,
                    initialSortingFlag: true,
                    initialsortingColumn: 1,
                    initialSortingorder: 'asc',
                    reloadOnWindowResize: false,
                    isCheckbox: true
                };
                
    $("div").remove("#subscriber-list_length"); 
   
    subscriberTable = FgBackendDatatable.listdataTableInit(datatableId, datatableOptions);
    
    subscriberTable.on( 'length.dt', function ( e, settings, len ) {
        FgUtility.startPageLoading(); 
    } );
    $("#subscriber-list_length").detach().prependTo("#fgrowchange");
    //add our own classes to the selectbox
    $("#subscriber-list_length").find('select').addClass('form-control cl-bs-select');
    $("#subscriber-list_length").find('select').select2();
    
    //For change the search box field
    $("#searchbox").off('keyup');
    $("#searchbox").on("keyup", function() {
        var searchVal = $(this).val();
        setDelay(function(){
        subscriberTable.search(searchVal).draw(); 
        },500);
    });
    var unwantedSections = $('table.dataTable-subscriber').attr('fgunwantedsection');
    var viewflag = true;
    if (typeof unwantedSections !== 'undefined' && unwantedSections !== false) {
        viewflag = false;
    }

    if (viewflag) {

        $("body").on('click', "ul#data-tabs li a[data-url]", function() {
            document.location = $(this).attr('data-url');
        });
        setTimeout(function () {
                FgCheckBoxClick.init('dataTable-subscriber');
            }, 200);
    }

});
//inline edit click 
$('body').on('click', '#inlineEditSubscriber', function() {
    inlineEditFlag = (($("#inlineEditSubscriber").length > 0 && $('#inlineEditSubscriber').is(':checked'))) ? 1 : 0;
     FgUtility.startPageLoading();
     subscriberTable.draw();  
    if (inlineEditFlag) {
        var postUrl = $('#inlineEditSubscriber').attr('inlineedit-post-url');
        var data = JSON.parse(dataInlineEdit);

        $('.inline-editable').editable({
            emptytext: '-',
            autotext: 'never',
        });
        inlineEdit.init({
            element: '.inline-editable',
            postUrl: postUrl,
            data: data,
            callback: function(data) {
                var _this = data,
                        _thistD = $(_this).parent('td'),
                        index = _thistD.index(),
                        parentWidth = $('.dataTables_scrollHeadInner th').eq(index).innerWidth();
                _thistD.css({'width': parentWidth});
                _this.css({'width': parentWidth - 10});
            }
        })
        $('body').off('keyup', 'span.inline-editable');
        $('body').on('keyup', 'span.inline-editable', function(e) {
            if (e.which == 13) {
                $(this).trigger('click');
            }
        });
    }
});


//handle action menu starts
var actionMenuText = {'active' : {'none': actionMenuNoneSelectedText, 'single': actionMenuSingleSelectedText, 'multiple': actionMenuMultipleSelectedText}};
FgSidebar.dynamicMenus.push({actionmenu: actionMenuText});


