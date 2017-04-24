

function pageLoad() {
    deleteButton();
    langButtonInit();
}

function deleteButton() {

    $(document).on('click', '.delete_now', function () {
        $($(this).data("target")).remove();
    });
}

function langButtonInit() {

    $(document).on('click', '.btlang', function () {
        var selectedLang = $(this).attr('lang');
        langButton(selectedLang);
    });
}

function showSelectedLangButton(lang) {
    $('button[data-elem-function=switch_lang]').removeClass('adminbtn-ash').addClass('fg-lang-switch-btn');
    $('button[data-elem-function=switch_lang][data-selected-lang=' + lang + ']').removeClass('fg-lang-switch-btn').addClass('adminbtn-ash');
}

function initDirtyForm() {
    FgDirtyForm.init();
    FgDirtyForm.disableButtons();
}
function initDragAndDropSort(className) {
    FgDragAndDrop.sortWithOrderUpdation('#' + className, false);
}
/**
 *
 *assign the language button switcher functionality to the language buttons
 */
function langButton(language) {
    showSelectedLangButton(language);
    FgUtility.showTranslation(language);
}
/**
 * generate a new area while click on the add more button
 */
function renderNewAddmore(templateId, containerId, language, defaultData) {
    var htmlFinal = FGTemplate.bind(templateId, {data: defaultData});
    $('#' + containerId).append(htmlFinal);
    FgUtility.resetSortOrder($('#' + containerId));
    FgUtility.showTranslation(language);
}

FgCommon = {
    getWindowHeight: function (reduceWidth) {
        var height = $(window).height() - reduceWidth;
        if (height <= 300) {
            height = 300;
        }

        return height;
    },
    splitContactname: function (contactname) {
        
        var seperator = (hasSeperator) ? '>>>>' : ',' ;
        var datastring = '';
        var contactArray = contactname.split(seperator);

        datastring = (_.size(contactArray) > 1) ? contactArray.join('<br>') : contactArray[0];

        return  datastring;
    },
//    for manipulating contact details json string
//    input contactname json string having path and contact-name of many contacts
//    output each contact name in anchor tag, with 'path', separated with 'line-break'    
    pluckContactDetails: function (contactname) {
        var datastring = '';
        var contactDetails = _(contactname).toArray();
        if (contactDetails.length > 0) {
            for (i = 0; i < contactDetails.length; i++) {
                if (contactDetails[i]['path']) {
                    datastring += '<a href="' + contactDetails[i]['path'] + '" target="_blank" >' + contactDetails[i]['contact-name'] + '</a><br />';
                } else {
                    datastring += contactDetails[i]['contact-name'] + '<br />';
                }
            }
        }

        return  datastring;
    },
    
    /**
     * To get contactname anchored with overview path
     * @param {string} contactname comma separated contactnames
     * @param {string} contactIds  comma separated contact-ids
     * @param {string} overviewPath Path contact overview
     * @returns {String}
     */
    getContactNameWithPath: function(contactname, contactIds, overviewPath) {
        var datastring = '';
        var contactNames = contactname.split(",");
        var contactIds = contactIds.split(",");
        if (contactNames.length > 0) {
            for (i = 0; i < contactNames.length; i++) {
                datastring += '<a href="' + overviewPath.replace('CONTACT',contactIds[i]) + '" target="_blank" >' + contactNames[i] + '</a><br />';
            }
        }

        return  datastring;
    },
    
    setinitialOpt: function () {
        var opt = {
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
            paging: false,
            scrollCollapse: true,
            dom: "<'row_select_datatow col-md-12'l><'col-md-12't><'col-md-4 fg-datatable-pagination 'i><'col-md-8'p>",
            autoWidth: true,
            stateSave: true,
            stateDuration: 60 * 60 * 24,
            pagingType: "full_numbers"
        };
        return opt;
    },
    //function to generate fixed column (only for window width >= 768px)
    generateFixedColumn: function (table, leftColumnCount) {
        if ($(window).width() >= 768) {
            fc = new $.fn.dataTable.FixedColumns(table, {
                "leftColumns": leftColumnCount,
                "drawCallback": function () {
                    $(".DTFC_LeftWrapper .dataTable_checkall").uniform();
                    $(".DTFC_LeftWrapper .dataClass").uniform();
                }
            });
            fc.fnRedrawLayout();
        } else {
            $(".dataClass").uniform();
            $(".dataTable_checkall").uniform();

        }
    },
    checkboxpluginInit: function () {
        if ($(window).width() >= 768) {
            $(".DTFC_LeftWrapper .dataTable_checkall").uniform();
            $(".DTFC_LeftWrapper .dataClass").uniform();
        } else {
            $(".dataClass").uniform();
            $(".dataTable_checkall").uniform();
        }
    },
    setDataTableCountDisplay: function (_this) {
        if ($('.fa-filter:visible').length > 0) {
            $("#fcount").html(_this.fnRecordsTotal());
        } else if ($.isNumeric(totalCount) && totalCount >= 0 && $("#filterCount").length > 0) {
            $("#tcount").html(totalCount);
            $("#fcount").html(_this.fnRecordsTotal());
            if ($(".filter-alert:visible").length > 0) {
                $("#fg-slash").show();
                $("#tcount").show();
            }

        } else if (!$.isNumeric(totalCount) && totalCount == '' && $("#filterCount").length > 0) {
            $("#fcount").html(_this.fnRecordsTotal());
            if ($(".filter-alert:visible").length > 0 && $("#tcount").html() != '') {
                $("#fg-slash").show();
                $("#tcount").show();
            }
        } else if ($.isNumeric(totalCount) && totalCount == 0 && $("#filterCount").length == 0) {
            $("#tcount").html(0)
            $("#fcount").html(0)
        } else if ($.isNumeric(totalCount) && totalCount >= 0 && $("#filterCount").length == 0) {
            $("#tcount").html(totalCount)
            $("#fcount").html(0)
        } else if (typeof totalCount == 'string' && totalCount == '' && $("#filterCount").length > 0) {

        } else if ($.isNumeric(totalCount) && totalCount == 0) {
            $("#tcount").html(0)
            $("#fcount").html(0)
        } else if (!$.isNumeric(totalCount) && totalCount == '' && $("#filterCount").length == 0) {
            //temporary setting
            $("#fcount").html(_this.fnRecordsTotal())
            $("#tcount").html(_this.fnRecordsTotal())
        } else {
            $("#fcount").html(_this.fnRecordsTotal())
        }
    },
    splitWithLineBreak: function (dataText, dataSeperator) {
        
        var seperator = (typeof dataSeperator !== 'undefined') ? dataSeperator : ',';
        var datastring = '';
        var dataArray = dataText.split(seperator);
        datastring = (_.size(dataArray) > 1) ? dataArray.join('<br>') : dataArray[0];

        return  datastring;
    },
}
 