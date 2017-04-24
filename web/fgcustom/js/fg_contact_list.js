/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var exportflag = true;
FgContactList = {
    checkIfExist: function (checkValue) {

        var flag = false;
        $.each(checkValue, function (keys, values) {

            if (keys == 'id' && _.chain(jsonData["CF"]['entry']).where({"id": values}).flatten().pluck('id').value() != '') {

                flag = true;
            }



        })
        return flag;
    },
    deletecheck: function (tableSettingValue, jsonData, teamId, workgroupId) {
        //for remove old entry in the database
        var escapeFieldsArray = ['join_leave_dates','membership_years','membership_category'];

        $.each(tableSettingValue, function (keys, values) {

            $.each(values, function (key, value) {

                if (key == 'type' && values[key] == 'CF') {

                    if (!FgContactList.checkIfExist(values)) {
                        delete tableSettingValue[keys];
                    }
                }
                // checking for delete old entries saved in database
                if (key == 'type' && values[key] == 'G') {
                   if(jQuery.inArray(values['id'],escapeFieldsArray) > -1) {
                      delete tableSettingValue[keys]; 
                   }
                }


                else if (key == 'type' && (values[key] == 'RF' || values[key] == 'R') && values['sub_ids'] != 'all') {

                    if (values['id'] == teamId) {
                        var actualSubIds = tableSettingValue[keys]['sub_ids'].split(",");
                        var subIds = tableSettingValue[keys]['team_rolecat_ids'];
                        if (typeof jsonData['TEAM'] !== 'undefined' && _.size(jsonData['TEAM']) > 0) {

                            $.each(subIds, function (subKeys, subValues) {
                                if (subValues == '') {
                                    actualSubIds.splice(0, 1);
                                } else {
                                    if (_.chain(jsonData["TEAM"]['entry']).where({"id": subKeys}).flatten().pluck('id').value() == '') {
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
                            delete tableSettingValue[keys];
                        }


                    } else if (values['id'] == workgroupId) {
                        if (typeof jsonData['WORKGROUP'] !== 'undefined' && _.size(jsonData['WORKGROUP']) > 0) {
                            if (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).flatten().pluck('id').value() != '') {
                                var actualSubIds = tableSettingValue[keys]['sub_ids'].split(",");
                                var subIds = tableSettingValue[keys]['sub_ids'].split(",");
                                $.each(subIds, function (subKeys, subValues) {
                                    if (subValues == '') {
                                        actualSubIds.splice(0, 1);
                                    } else {
                                        if (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten().where({"id": subValues}).pluck('id').value() == '') {
                                            index = $.inArray(subValues, actualSubIds);
                                            actualSubIds.splice(index, 1);
                                        }
                                    }
                                })
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
                            }
                        }
                        else {
                            delete tableSettingValue[keys];
                        }

                    } else {
                        jsonRoleKey = 'ROLES-';
                        if (values['is_fed_cat'] == 1) {
                            jsonRoleKey = 'FROLES-';
                        }
                        //check role category is exist or not
                        if (typeof jsonData[jsonRoleKey + values['club_id']] !== 'undefined' && _.size(jsonData[jsonRoleKey + values['club_id']]) > 0) {

                            if (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).flatten().pluck("id").value() == '') {
                                delete tableSettingValue[keys];
                            } else {
                                var subIds = tableSettingValue[keys]['sub_ids'].split(",");
                                var actualSubIds = tableSettingValue[keys]['sub_ids'].split(",");
                                $.each(subIds, function (subKeys, subValues) {
                                    if (subValues == '') {
                                        actualSubIds.splice(0, 1);
                                    } else {
                                        if (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck("input").flatten().where({"id": subValues}).pluck('id').value() == '') {
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
                            }
                        } else {
                            delete tableSettingValue[keys];
                        }

                    }
                } else if (key == 'type' && (values[key] == 'RF' || values[key] == 'R') && values['sub_ids'] == 'all') {
                    if (values['id'] == teamId) {
                        if (typeof jsonData['TEAM'] !== 'undefined' && _.size(jsonData['TEAM']) > 0) {
                            if (_.chain(jsonData["TEAM"]['entry']).where({"id": values['id']}).flatten().pluck('id').value() == '') {
                                //delete  tableSettingValue[keys];
                            }
                        } else {
                            //delete  tableSettingValue[keys];
                        }

                    } else if (values['id'] == workgroupId) {
                        if (typeof jsonData['WORKGROUP'] !== 'undefined' && _.size(jsonData['WORKGROUP']) > 0) {
                            if (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).flatten().pluck('id').value() == '') {
                                delete  tableSettingValue[keys];
                            }
                        } else {
                            delete  tableSettingValue[keys];
                        }

                    } else {
                        jsonRoleKey = 'ROLES-';
                        if (values['is_fed_cat'] == 1) {
                            jsonRoleKey = 'FROLES-';
                        }
                        if (typeof jsonData[jsonRoleKey + values['club_id']] !== 'undefined' && _.size(jsonData[jsonRoleKey + values['club_id']]) > 0) {

                            if (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).flatten().pluck('id').value() == '') {
                                delete  tableSettingValue[keys];
                            }

                        } else {
                            delete  tableSettingValue[keys];
                        }


                    }
                }
                //check if it is a contact field .if yes check if this field is delete or note
                //if(key)

            });
        });

        return tableSettingValue;

    }

};

FgTableColumnHeading = {
    getColumnNames: function (tableSettingValue, teamId, workgroupId, general_table_title_array, exportflag) {
        tableColumnTitle = [];
        tableColumnTitle.push({"sTitle": "<div class='fg-th-wrap'><i class='chk_cnt' ></i>&nbsp;<input type='checkbox' name='check_all' id='check_all' class='dataTable_checkall'></div>&nbsp;", "mData": "edit", "bSortable": false});

        if (exportflag == false) {
            tableColumnTitle.push({"sTitle": "<span class='fg-contact-wrap'>" + datatabletranslations['contact_name'] + "</span>&nbsp;", "mData": "contactname", "bSortable": true});
        }
        if (contactType == 'archive') {
            tableColumnTitle.push({"sTitle": datatabletranslations['archived_On'], "mData": "archived_on", "bSortable": true});
        } else if (contactType == 'formerfederationmember') {
            tableColumnTitle.push({"sTitle": datatabletranslations['Resigned_on'], "mData": "resigned_on", "bSortable": true});
        }
        tableColumnTitle.push({'sTitle': datatabletranslations['Function'], 'mData': 'Function'});
        $.each(tableSettingValue, function (keys, values) {

            $.each(values, function (key, value) {

                if (key == 'type' && values[key] == 'CF') {

                    $.each(jsonData['CF']['entry'], function (jsonKey, jsonValue) {

                        if (jsonValue['id'] == values['id']) {
                            if (exportflag == false) {
                                var atrShortName = jsonValue['shortName'];
                                if ($.inArray(jsonValue['id'], corrAddrFieldIds) != -1) {
                                    if (atrShortName.indexOf("(Korr.)") >= 0) {
                                        atrShortName = atrShortName.replace('(Korr.)', '<span class="fg-left-exportblk"><i class="fa fa-home"></i></span>');
                                    } else {
                                        atrShortName = atrShortName + '<span class="fg-left-exportblk"><i class="fa fa-home"></i></span>';
                                    }
                                }
                                if ($.inArray(jsonValue['id'], invAddrFieldIds) != -1) {
                                    if (atrShortName.indexOf("(Rg.)") >= 0) {
                                        atrShortName = atrShortName.replace('(Rg.)', '<span class="fg-left-exportblk"><i class="fa fa-money"></i></span>');
                                    } else {
                                        atrShortName = atrShortName + '<span class="fg-left-exportblk"><i class="fa fa-money"></i></span>';
                                    }
                                }
                                tableColumnTitle.push({"sTitle": atrShortName, "mData": values['name']});
                            } else {
                                var atrShortNamexport = $.trim(jsonValue['shortName']);

                                if ($.inArray(jsonValue['id'], corrAddrFieldIds) != -1) {
                                    if (atrShortNamexport.indexOf("(Korr.)") >= 0) {
                                        atrShortNamexport = atrShortNamexport;
                                    } else {
                                        atrShortNamexport = atrShortNamexport + "&nbsp;(" + datatabletranslations['Korr'] + ")";
                                    }
                                }
                                if ($.inArray(jsonValue['id'], invAddrFieldIds) != -1) {
                                    if (atrShortNamexport.indexOf("(Rg.)") >= 0) {
                                        atrShortNamexport = atrShortNamexport;
                                    } else {
                                        atrShortNamexport = atrShortNamexport + "&nbsp;(" + datatabletranslations['Reg'] + ")";
                                    }
                                }
                                tableColumnTitle.push({"sTitle": atrShortNamexport, "mData": values['name']});
                            }
                        }

                    });
                } else if (key == 'type' && values[key] == 'G' && general_table_title_array.hasOwnProperty(values['id'])) {//General field handle       
                        tableColumnTitle.push({"sTitle": general_table_title_array[values['id']], "mData": values['name']}); 
                } else if (key == 'type' && values[key] == 'CN' && general_table_title_array.hasOwnProperty(values['id'])) {
                    tableColumnTitle.push({"sTitle": general_table_title_array[values['id']], "mData": values['name']});

                } else if (key == 'type' && values[key] == 'FI') { //federation info handle area
                    tableColumnTitle.push({"sTitle": String(_.chain(jsonData['FI']['entry']).where({"id": values['id']}).flatten().pluck('title').value()), "mData": values['name']});
                } else if (key == 'type' && values[key] == 'FM') {//For handle fed memembership 
                    var titleField= (values['id']== 'fed_membership') ? 'fed_membership':values['name'];
                    tableColumnTitle.push({"sTitle": String(_.chain(jsonData['FM']['entry']).where({"id": titleField}).flatten().pluck('shortTitle').value()), "mData": values['name']});
                } else if (key == 'type' && values[key] == 'CM') { //For handle club membership
                    var titleField= (values['id']== 'membership') ? 'membership':values['name'];

                    tableColumnTitle.push({"sTitle": String(_.chain(jsonData['CM']['entry']).where({"id": titleField}).flatten().pluck('shortTitle').value()), "mData": values['name']});
                } else if (key == 'type' && (values[key] == 'RF' || values[key] == 'R')) { // roles handle area

                    if (tableSettingValue[keys]['sub_ids'] == 'all') {
                        if (values['id'] == teamId) {
                            if (typeof jsonData['TEAM'] !== 'undefined' && _.size(jsonData['TEAM']) > 0) {

                                var fcount = 0;
                                var popovercontent = '';
                                $.each(jsonData['TEAM']['entry'], function (jsonKey, jsonValue) {
                                    fcount = fcount + _.size(jsonData['TEAM']['entry'][jsonKey]['input']) - 1;
                                    $.each(jsonData['TEAM']['entry'][jsonKey]['input'], function (iterateKey, iterateValue) {
                                        if (jsonData['TEAM']['entry'][jsonKey]['input'][iterateKey]['id'] != 'any') {
                                            popovercontent += jsonData['TEAM']['entry'][jsonKey]['input'][iterateKey]['title'] + "<br>";
                                        }
                                    })

                                });
                                var title = jsonData['TEAM']['title'];

                                var squarebracket = '';
                                if (fcount == 1) {
                                    squarebracket = '';
                                    popovercontent = popovercontent.replace(/<br>/g, "");
                                    dummyTitle = title;
                                    title = popovercontent;
                                    popovercontent = dummyTitle;
                                    if (values[key] == 'RF') {
                                        title = title + "&nbsp;(+ " + datatabletranslations['Function'] + ")";
                                    }
                                    title = (exportflag == false) ? "<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + title + "</span>" : title;

                                } else {
                                    if (values[key] == 'RF') {
                                        squarebracket = (exportflag == false) ? '&nbsp;(+ ' + datatabletranslations['Function'] + ')&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + fcount + '/' + fcount + '</span>]' : '&nbsp;(+ ' + datatabletranslations['Function'] + ')&nbsp;[' + fcount + '/' + fcount + ']';
                                    } else {
                                        squarebracket = (exportflag == false) ? '&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + fcount + '/' + fcount + '</span>]' : '&nbsp;[' + fcount + '/' + fcount + ']';
                                    }

                                }

                                var actualTitle = title;
                                popovervalues = squarebracket
                                if (exportflag == false) {
                                    tableColumnTitle.push({"sTitle": actualTitle + popovervalues, "mData": values['name']});
                                } else {
                                    tableColumnTitle.push({"sTitle": actualTitle + popovervalues, "mData": values['name']});
                                }
                                //tableColumnTitle.push({"sTitle":actualTitle, "mData":values['name']});
                            }
                        } else if (values['id'] == workgroupId) {

                            if (typeof jsonData['WORKGROUP'] !== 'undefined' && _.size(jsonData['WORKGROUP']) > 0) {
                                var popovercontent = '';
                                var title = jsonData['WORKGROUP']['title'];

                                //create popover content
                                inputObj = _.chain(jsonData['WORKGROUP']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped;
                                $.each(inputObj, function (jsonKey, jsonValue) {
                                    $.each(jsonValue, function (iterateKey, iterateValue) {
                                        if (iterateKey == 'title' && jsonValue['id'] != 'any') {
                                            popovercontent += iterateValue + "<br>";
                                        }

                                    })

                                });
                                var squarebracket = '';
                                if ((_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) == 1) {
                                    squarebracket = '';
                                    popovercontent = popovercontent.replace(/<br>/g, "");
                                    dummyTitle = title;
                                    title = popovercontent;
                                    popovercontent = dummyTitle;
                                    if (values[key] == 'RF') {
                                        title = title + "&nbsp;(+ " + datatabletranslations['Function'] + ")";
                                    }
                                    title = (exportflag == false) ? "<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + title + "</span>" : title;

                                } else {
                                    if (values[key] == 'RF') {
                                        squarebracket = (exportflag == false) ? '&nbsp;(+ ' + datatabletranslations['Function'] + ')&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + '/' + (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + '</span>]' : '&nbsp;(+ ' + datatabletranslations['Function'] + ')&nbsp;[' + (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + '/' + (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + ']';
                                    } else {
                                        squarebracket = (exportflag == false) ? '&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + '/' + (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + '</span>]' : '&nbsp;[' + (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + '/' + (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + ']';
                                    }

                                }
                                var fcount = title;

                                popovervalues = squarebracket;
                                if (exportflag == false) {
                                    tableColumnTitle.push({"sTitle": fcount + popovervalues, "mData": values['name']});
                                } else {
                                    tableColumnTitle.push({"sTitle": fcount + popovervalues, "mData": values['name']});
                                }
                                // tableColumnTitle.push({"sTitle":fcount, "mData":values['name']});
                            }

                        } else {

                            jsonRoleKey = 'ROLES-';
                            if (values['is_fed_cat'] == '1') {
                                jsonRoleKey = 'FROLES-';
                            }
                            if (typeof jsonData[jsonRoleKey + values['club_id']] !== 'undefined' && _.size(jsonData[jsonRoleKey + values['club_id']]) > 0) {

                                if (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck("id").value() != '' && _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length > 0) {

                                    var popovercontent = '';
                                    inputObj = _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped;

                                    $.each(inputObj, function (jsonKey, jsonValue) {
                                        $.each(jsonValue, function (iterateKey, iterateValue) {
                                            if (iterateKey == 'title' && jsonValue['id'] != 'any') {
                                                popovercontent += iterateValue + "<br>";
                                            }

                                        })

                                    });
                                    // popovervalues = '<i class="fa fa-info-circle fg-popover-cion fg-custom-popovers"  data-content="' + popovercontent + '"></i>';
                                    var bracketArea;
                                    var title = _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck("title").value();
                                    var squarebracket = '';
                                    if (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length == 1) {
                                        squarebracket = '';
                                        popovercontent = popovercontent.replace(/<br>/g, "");
                                        dummyTitle = title;
                                        title = popovercontent;
                                        popovercontent = dummyTitle;
                                        if (values[key] == 'RF') {
                                            title = (exportflag == false) ? "<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + title + '&nbsp;(+ ' + datatabletranslations['Function'] + ")</span>" : title + "&nbsp;(+ " + datatabletranslations['Function'] + ")";
                                        } else {
                                            title = (exportflag == false) ? "<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + title + "</span>" : title;
                                        }

                                    } else {

                                        // squarebracket= "<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>[" + _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length + "/" + _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length + "]</span>"; 
                                        if (values[key] == 'RF') {
                                            squarebracket = (exportflag == false) ? "&nbsp;(+ " + datatabletranslations['Function'] + ")&nbsp;[<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "/" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "</span>]" : "&nbsp;(+ " + datatabletranslations['Function'] + ")&nbsp;[" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "/" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "]";
                                        } else {
                                            squarebracket = (exportflag == false) ? "&nbsp;[<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "/" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "</span>]" : "&nbsp;[" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "/" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "]";
                                        }
                                    }

                                    bracketArea = squarebracket;

                                    var fcount = title;

                                    tableColumnTitle.push({"sTitle": fcount + bracketArea, "mData": values['name']});

                                }

                            }
                        }

                    } else {

                        if (values['id'] == teamId) {
                            var tcount = 0;
                            var fcount = 0;
                            var popovercontent = '';
                            var titleContent = '';
                            $.each(values['team_rolecat_ids'], function (tKey, tValue) {
                                functionArray = '';
                                functionArray = tValue.split(",");
                                fcount = fcount + _.size(functionArray);
                                $.each(functionArray, function (iterationkey, iterationvalue) {
                                    titleContent = _.chain(jsonData["TEAM"]['entry']).where({"id": tKey}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value();
                                    popovercontent += _.chain(jsonData["TEAM"]['entry']).where({"id": tKey}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value() + "<br />";

                                })

                            });
                            if (typeof jsonData['TEAM'] !== 'undefined' && _.size(jsonData['TEAM']) > 0) {
                                $.each(jsonData['TEAM']['entry'], function (jsonKey, jsonValue) {
                                    tcount = tcount + _.size(jsonData['TEAM']['entry'][jsonKey]['input']) - 1;
                                });
                                var title = '';
                                if (fcount > 1) {
                                    title = jsonData['TEAM']['title'];
                                } else {
                                    title = titleContent;
                                    popovercontent = jsonData['TEAM']['title'];
                                }
                                var bracket = (fcount > 1) ? "&nbsp;[" + fcount + "/" + tcount + "]" : '';

                                var actualTitle = (values[key] == 'RF') ? title + "&nbsp;(+ " + datatabletranslations['Function'] + ")" : title + '';
                                if (fcount > 1) {
                                    popovervalues = actualTitle + '&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + fcount + "/" + tcount + '</span>]';
                                } else {
                                    popovervalues = '<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + actualTitle + '' + '</span>';
                                }

                                if (exportflag == false) {
                                    tableColumnTitle.push({"sTitle": popovervalues, "mData": values['name']});
                                } else {
                                    tableColumnTitle.push({"sTitle": actualTitle + bracket, "mData": values['name']});
                                }

                                //tableColumnTitle.push({"sTitle":actualTitle, "mData":values['name']});
                            }

                        } else if (values['id'] == workgroupId) {

                            if (typeof jsonData['WORKGROUP'] !== 'undefined' && _.size(jsonData['WORKGROUP']) > 0) {
                                if (_.chain(jsonData['WORKGROUP']['entry']).where({"id": values['id']}).pluck("id").value() != '' && _.chain(jsonData['WORKGROUP']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length > 0) {

                                    var popovercontent = '';
                                    var titleContent = '';

                                    functionArray = values['sub_ids'].split(",");
                                    //create the popover content
                                    $.each(functionArray, function (iterationkey, iterationvalue) {
                                        titleContent = _.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value();
                                        popovercontent += _.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value() + "<br>";
                                    })
                                    if (functionArray.length > 1) {
                                        var title = jsonData['WORKGROUP']['title'];
                                    } else {
                                        var title = titleContent;
                                        popovercontent = jsonData['WORKGROUP']['title'];
                                    }
                                    var fcount = '';

                                    var bracket;
                                    if (functionArray.length > 1) {
                                        bracket = "&nbsp;[" + functionArray.length + "/" + _.chain(jsonData['WORKGROUP']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length + "]";
                                    } else {
                                        bracket = '';
                                    }
                                    if (values[key] == 'RF') {
                                        fcount = title + "&nbsp;(+ " + datatabletranslations['Function'] + ")";
                                    } else {
                                        fcount = title;
                                    }

                                    var popovervalues = '';
                                    if (functionArray.length > 1) {
                                        popovervalues = fcount + "&nbsp;[<span class='fg-custom-popovers fg-dotted-br'  data-content='" + popovercontent + "'>" + functionArray.length + "/" + (_.chain(jsonData['WORKGROUP']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "</span>]";
                                    } else {
                                        popovervalues = "&nbsp;<span class='fg-custom-popovers fg-dotted-br'  data-content='" + popovercontent + "'>" + fcount + '' + "</span>";
                                    }

                                    if (exportflag == false) {
                                        tableColumnTitle.push({"sTitle": popovervalues, "mData": values['name']});
                                    } else {
                                        tableColumnTitle.push({"sTitle": fcount + bracket, "mData": values['name']});
                                    }

                                    // tableColumnTitle.push({"sTitle":fcount, "mData":values['name']});
                                }


                            }

                        } else {

                            var popovercontent = '';
                            var titleContent = '';
                            jsonRoleKey = 'ROLES-';
                            if (values['is_fed_cat'] == '1') {
                                jsonRoleKey = 'FROLES-';
                            }
                            if (typeof jsonData[jsonRoleKey + values['club_id']] !== 'undefined' && _.size(jsonData[jsonRoleKey + values['club_id']]) > 0) {
                                if (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck("id").value() != '' && _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length > 0) {
                                    functionArray = values['sub_ids'].split(",");
                                    //create the popover content
                                    $.each(functionArray, function (iterationkey, iterationvalue) {
                                        titleContent = _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value();
                                        popovercontent += _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value() + "<br />";
                                    })

                                    if (functionArray.length > 1) {
                                        var title = _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck("title").value();
                                    } else {
                                        var title = titleContent;
                                        popovercontent = _.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck("title").value();
                                    }

                                    var bracket;
                                    if (functionArray.length > 1) {
                                        bracket = "&nbsp;[" + (functionArray.length) + "/" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "]";
                                    } else {
                                        bracket = '';
                                    }
                                    var fcount = (values[key] == 'RF') ? title + " (+ " + datatabletranslations['Function'] + ")" : title;

                                    var popovervalues = '';
                                    if (functionArray.length > 1) {
                                        popovervalues = fcount + "&nbsp;[<span class='fg-custom-popovers fg-dotted-br'  data-content='" + popovercontent + "'>" + (functionArray.length) + "/" + (_.chain(jsonData[jsonRoleKey + values['club_id']]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length - 1) + "</span>]";
                                    } else {
                                        popovervalues = "&nbsp;<span class='fg-custom-popovers fg-dotted-br'  data-content='" + popovercontent + "'>" + fcount + '' + "</span>";
                                    }

                                    if (exportflag == false) {
                                        tableColumnTitle.push({"sTitle": popovervalues, "mData": values['name']});
                                    } else {
                                        tableColumnTitle.push({"sTitle": fcount + bracket, "mData": values['name']});
                                    }
                                }


                            }
                        }


                    }

                }

            });
        }); //end of main iteration

        return tableColumnTitle;

    }


};
