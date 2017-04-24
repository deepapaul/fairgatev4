/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var exportFlag = false;
function filterCallback() {

    if (!$.isEmptyObject(oTable)) {
        oTable.api().clear();
        oTable.api().draw();
    } else {
        FgTable.init();
    }
    $('.alert').removeClass('display-hide');
    if (FgSidebar.isFirstTime) {
        callSidebar();
        FgSidebar.isFirstTime = false;
    }

}


function  updateFilterFlag(filterflag, filtername) {

    if (filterflag != 1) {
        localStorage.setItem(filtername, 0);
    }
    if (filterflag == 0) {
        $('.filter-alert').hide();
        //enable the filter checkbox
        $("#filterFlag").attr("checked", false);
        jQuery.uniform.update('#filterFlag');
    }
}



function filterSave(id, url, clubId, translation) {

    $(id).editable({
        type: 'text',
        url: url,
        pk: clubId,
        emptytext: '',
        display: false,
        placement: 'bottom',
        inputclass: 'form-control input-sm',
        validate: function(value) {
            if ($.trim(value) == '') {
                return datatabletranslations['VALIDATION_THIS_FIELD_REQUIRED'];
            }
        },
        params: function(params) {
            var stringifyed_data = localStorage.getItem(filterStorage);
            stringifyed_data = stringifyed_data.replace(/,"disabled":true/g, '');
            params.jString = stringifyed_data; //JSON.stringify(data);
            return params;
        },
        success: function(data) {

            if (data.operation == 'INSERT') {
                data.parentMenuId = filterMenuId;
                var menuHtml = FgSidebar.generateHtml(FgSidebar.templateLevel2, {'subMenu': data});
                var parentMenu = $('#' + filterMenuId + ' ul.sub-menu');
                $('#' + filterMenuId).addClass('open');
                $(menuHtml).appendTo(parentMenu);
                FgSidebar.handleArrows(parentMenu, '');
            }
            FgUtility.showToastr(translation);
        }

    });
}
function getTableColumns(tableSettingValue, jsonData, clubterminology, exportFlag) {
    var tableColumnTitle = [];
    tableColumnTitle.push({"sTitle": "<div class='fg-th-wrap'><i class='chk_cnt' ></i>&nbsp;<input type='checkbox' name='check_all' id='check_all' class='dataTable_checkall fg-dev-avoidicon-behaviour'></div>", "mData": "edit", "bSortable": false, "width": "10%"});
    if (exportFlag == true) {
        tableColumnTitle.push({"sTitle": clubterminology + " " + datatabletranslations['club_name'], "mData": "clubname", "bSortable": true});
    } else {
        tableColumnTitle.push({"sTitle": "<span class='fg-clname-wrap'>" + clubterminology + " " + datatabletranslations['club_name'] + "</span>&nbsp;", "mData": "clubname", "bSortable": true});
    }

    $.each(tableSettingValue, function(keys, values) {

        $.each(values, function(key, value) {
            if (key == 'type' && values[key] == 'SI') {
                $.each(jsonData['SI']['entry'], function(jsonKey, jsonValue) {
                    if (jsonValue['id'] == values['id']) {
                        tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
                    }
                });
                // tableColumnTitle = jsonKeyTitleFind(jsonData, 'SI', tableColumnTitle,values);

            } else if (key == 'type' && values[key] == 'CF') {
                $.each(jsonData['CD']['entry'], function(jsonKey, jsonValue) {
                    if (jsonValue['id'] == values['id']) {
                        var title = '';
                        if (exportFlag == false) {
                            if (_.has(jsonValue, "grp")) {
                                if (jsonValue['grp'] == 'correspondence') {
                                    shortImg = '<span class="fg-left-exportblk"><i class="fa fa-home"></i></span>';
                                } else if (jsonValue['grp'] == 'invoice') {
                                    shortImg = '<span class="fg-left-exportblk"><i class="fa fa-money"></i></span>';
                                } else {
                                    shortImg = '';
                                }
                                title = jsonValue['title'] + shortImg;
                            } else {
                                title = jsonValue['title'];
                            }
                        } else {

                            if (jsonValue['grp'] == 'correspondence') {
                                shortName = "&nbsp;(Korr.)";
                            } else if (jsonValue['grp'] == 'invoice') {
                                shortName = "&nbsp;(Rg.)";
                            } else {
                                shortName = '';
                            }
                            //console.log(jsonValue['title']);
                            title = jsonValue['title'] + shortName;
                        }

                        tableColumnTitle.push({"sTitle": title, "mData": values['name']});
                    }
                });
                // tableColumnTitle = jsonKeyTitleFind(jsonData, 'CF', tableColumnTitle,values);

            } else if (key == 'type' && values[key] == 'AF') {

                $.each(jsonData['AF']['entry'], function(jsonKey, jsonValue) {
                    if (jsonValue['id'] == values['id']) {
                        tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
                    }

                });
                //tableColumnTitle = jsonKeyTitleFind(jsonData, 'AF', tableColumnTitle,values);
            } else if (key == 'type' && typeof jsonData['CO'] !== 'undefined' && values[key] == 'CO') {

                $.each(jsonData['CO']['entry'], function(jsonKey, jsonValue) {

                    if (jsonValue['id'] == values['id']) {
                        tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
                    }

                });
                //tableColumnTitle = jsonKeyTitleFind(jsonData, 'CO', tableColumnTitle,values);

            } else if (key == 'type' && values[key] == 'CL') {
                var jsonRoleKey = 'class';
                var fcount = '';
                var title = _.chain(jsonData['class']['entry']).where({"id": values['id']}).pluck("title").value();
                var popovervalues = '';
                var bracket;
                if (tableSettingValue[keys]['sub_ids'] == 'all') {

                    if (_.size(_.where(jsonData['class']['entry'], {"id": values['id']})) > 0) {
                        var title = _.chain(jsonData['class']['entry']).where({"id": values['id']}).pluck("title").value();
                        inputObj = _.chain(jsonData['class']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped;
                        //for collecting the title for tootl tip
                        var popovercontent = '';
                        var count = 0;

                        $.each(inputObj, function(objkey, objvalues) {
                            popovercontent += objvalues['title'] + "<br>";
                            count++;
                        });

                        if (count == 1) {
                            popovercontent = popovercontent.replace(/<br>/g, "");
                            dummyTitle = title;
                            title = (exportFlag == false) ? '<span class="fg-custom-popovers fg-dotted-br" data-content="' + dummyTitle + '">' + popovercontent + '</span>' : popovercontent;
                            popovercontent = dummyTitle;
                            bracket = '';
                            popovervalues = '';
                        } else {
                            bracket = "[" + count + "/" + count + "]";
                            popovervalues = '[<span class="fg-custom-popovers fg-dotted-br" data-content="' + popovercontent + '">' + count + "/" + count + '</span>]';
                        }

                        fcount = title + "&nbsp;";

                    } else {

                        var title = _.chain(jsonData['class']['entry']).where({"id": values['id']}).pluck("title").value();
                        fcount = title + "&nbsp;" + "[0/0]";
                        popovervalues = '';
                        bracket = '';
                    }

                } else {
                    functionArray = values['sub_ids'].split(",");
                    var popovercontent = '';

                    //for collecting the title for tootl tip
                    var subIdCount = 0;
                    $.each(functionArray, function(key, value) {
                        var objId = _.chain(jsonData["class"]["entry"]).where({id: values['id']}).pluck("input").flatten().where({id: value}).value();
                        popovercontent += objId[0].title + '<br />';
                        subIdCount++;
                    });
                    inputObj = _.chain(jsonData['class']['entry']).where({"id": values['id']}).pluck('input').flatten().value();
                    var popovervalues = '';
                    if (functionArray.length > 1) {
                        fcount = title + "&nbsp;";
                        bracket = '[' + functionArray.length + '/' + _.size(inputObj) + ']';
                        popovervalues = '[<span class="fg-custom-popovers fg-dotted-br" data-content="' + popovercontent + '">' + functionArray.length + '/' + _.size(inputObj) + '</span>]';
                    } else {
                        fcount = '&nbsp;';
                        bracket = '';
                        popovervalues = '<span class="fg-custom-popovers fg-dotted-br" data-content="' + title + '">' + popovercontent + '</span>';
                    }

                }

                if (exportFlag == true) {

                    tableColumnTitle.push({"sTitle": fcount + bracket, "mData": values['name']});
                } else {
                    tableColumnTitle.push({"sTitle": fcount + popovervalues, "mData": values['name']});
                }

            }
        });
    }); //end of main iteration
    FgPopOver.init(".fgPopovers", true);
    return tableColumnTitle;
}
function removeDeletedFields(tableSettingValue, jsonData) {
    var classObj;

    $.each(tableSettingValue, function(keys, values) {
        $.each(values, function(key, value) {
            if (_.has(jsonData, 'class') && values['type'] == 'CL' && key == 'type') {
                if (_.size(_.where(jsonData['class']['entry'], {"id": values['id']})) > 0) {
                    inputLength = _.chain(jsonData['class']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length;
                    if (inputLength > 0) {
                        var subIds = tableSettingValue[keys]['sub_ids'].split(",");
                        var actualSubIds = tableSettingValue[keys]['sub_ids'].split(",");
                        $.each(subIds, function(subKeys, subValues) {
                            if (subValues == '') {
                                actualSubIds.splice(0, 1);
                            } else if (subValues != 'all') {
                                var objId = _.chain(jsonData["class"]["entry"]).where({id: values['id']}).pluck("input").flatten().where({id: subValues}).value()
                                if (objId.length == 0) {
                                    index = $.inArray(subValues, actualSubIds);
                                    actualSubIds.splice(index, 1);
                                }
                            }
                        });
                        if (actualSubIds.length > 0) {
                            tableSettingValue[keys]['sub_ids'] = actualSubIds.join();
                            if (tableSettingValue[keys]['sub_ids'].charAt(tableSettingValue[keys]['sub_ids'].length - 1) == ',') {
                                tableSettingValue[keys]['sub_ids'] = tableSettingValue[keys]['sub_ids'].substring(0, tableSettingValue[keys]['sub_ids'].length - 1);
                            }
                            if (tableSettingValue[keys]['sub_ids'] == '' || tableSettingValue[keys]['sub_ids'] == ',') {
                                tableSettingValue[keys]['sub_ids'] = 'all'
                            }
                        } else {
                            delete tableSettingValue[keys];
                        }
                    } else {
                        tableSettingValue[keys]['sub_ids'] = 'all'
                    }
                }
                else {
                    delete tableSettingValue[keys];
                }
            }
        });

    });
    return tableSettingValue;
}



function callFilterFlag(filtername) {
    $("#filterFlag").on("click", function() {

        if ($(this).is(':checked')) {
            $('.filter-alert').show();
            localStorage.setItem(filtername, 1);
        } else {
            $('.filter-alert').hide();
            localStorage.setItem(filtername, 0);
        }
    })
    if (localStorage.getItem(filtername) == 1) {
        $('#filterFlag').attr('checked', true);
        //update the property of the checkbox of jquery uniform plugin
        $.uniform.update('#filterFlag');
    }
}
function  updateFilterFlag(filterflag, filtername) {

    if (filterflag != 1) {
        localStorage.setItem(filtername, 0);
    }
    if (filterflag == 0) {
        $('.filter-alert').hide();
        //enable the filter checkbox
        $("#filterFlag").attr("checked", false);
        jQuery.uniform.update('#filterFlag');
    }
}
function jsonKeyTitleFind(jsonData, type, tableColumnTitle, values) {

    $.each(jsonData[type]['entry'], function(jsonKey, jsonValue) {
        if (jsonValue['id'] == values['id']) {
            tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
        }

    });

    return tableColumnTitle;
}



$(function() {
    //bind animation event to filter button
    FgSidebar.filterAnimationInit("#search");

//    //Function to disable the export link if no clubs is listed
//    $('#fgContactdrop').on('click', function() {
//        var cntcount = $("#fcount").text();
//        if (cntcount == '0') {
//            $(".dropdown-menu").find(".fg-dev-menu-click").addClass("inactive fg-no-hand");
//            $(".dropdown-menu").find(".fg-dev-menu-click").removeClass("fg-dev-menu-click");
//        } else {
//            $(".dropdown-menu").find(".inactive").addClass("fg-dev-menu-click");
//            $(".dropdown-menu").find(".fg-dev-menu-click").removeClass("inactive fg-no-hand");
//        }
//    });
    if (typeof filterDisplayFlagStorage !== 'undefined') {
        var filterflag = localStorage.getItem(filterDisplayFlagStorage);

        updateFilterFlag(filterflag, filterDisplayFlagStorage);
    }

    //bind the click event to the tableColumn select box

    $('#tableColumns').on('click', function() {
        tableSettingValues = $.parseJSON($("#tableColumns option:selected").attr("data-attributes"));
        localStorage.setItem("ClubcolumnSettingId_" + clubId + "-" + contactId, $("#tableColumns option:selected").val());
        tableSettingValue = removeDeletedFields(tableSettingValues, jsonData);
        settingValue = tableSettingValues;
        localStorage.setItem("ClubtableSettingValue_" + clubId + "_" + contactId, JSON.stringify(tableSettingValues));
        tableColumnTitles = getTableColumns(settingValue, jsonData, clubTerminology);
        localStorage.setItem("Clubtablecolumn_" + clubId + "_" + contactId, JSON.stringify(tableColumnTitles));
        setTimeout(function() {
            window.location = ClubhomepPath;
        }, 100)


    });

    //bind the click event to filter close button
    $('.fg_filter_hide').on('click', function() {

        $('.filter-alert').hide();
        if ($('#filterFlag').length > 0 && $('#filterFlag').is(':checked')) {

            $('#filterFlag').attr('checked', false);
            //update the property of the checkbox of jquery uniform plugin
            localStorage.setItem(filterDisplayFlagStorage, 0);
            $.uniform.update('#filterFlag');
        } else {

            localStorage.setItem("filterdisplayflag", 1);
        }
    });
    //filter image handling
    $(".fg_dev_filter_show").on('click', function() {
        $('.filter-alert').toggle('fast', function() {
            if ($(this).is(":hidden")) {
                localStorage.setItem(filterDisplayFlagStorage, 0);
                $("#filterFlag").attr('checked', false);
            } else {
                localStorage.setItem(filterDisplayFlagStorage, 1);
                $("#filterFlag").attr('checked', true);
            }
        })
        setTimeout(function() {
            $.uniform.update('#filterFlag');
        }, 500)
    })


})

//filterSave('#filternameText',"{{url('update_club_filter_data')}}",filterStorage,filterMenuId,"{%trans%}CM_CONTACT_FILTER_SAVE_SUCCESS{%endtrans%}");