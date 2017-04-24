/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var exportflag = true;
FgMemberList = {
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

        $.each(tableSettingValue, function (keys, values) {

            $.each(values, function (key, value) {

                if (key == 'type' && values[key] == 'CF') {

                    if (!FgMemberList.checkIfExist(values)) {
                        delete tableSettingValue[keys];
                    }
                } else if (key == 'type' && (values[key] == 'RF' || values[key] == 'R') && values['sub_ids'] != 'all') {

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

                    }
                }
                //check if it is a contact field .if yes check if this field is delete or note
                //if(key)

            });
        });

        return tableSettingValue;

    },
    
    getAllMemberIdsInList: function(){
       var dataObj = listTable.rows({
                        order: 'applied', // 'current', 'applied', 'index',  'original'
                        search: 'applied', // 'none',    'applied', 'removed'
                        page: 'all'      // 'all',     'current'
                    }).data(); 
                    var memberIds = _.pluck(dataObj, 'messageId');
        var checkedIds = JSON.stringify(memberIds).replace(/^\[|]$/g, '');          
        return checkedIds;
    },
    
    removeMemberConfirmationPopup: function (checkedIds, selected, type, roleId){
      var titletext = $("#paneltab .active").find('a').html();
        $.post(pathRemoveMemberConfirmationPopup, {'memberIds' : checkedIds ,'selected': selected, 'titleText':titletext, 'type': type, 'roleId':roleId}, function(data) {             
            FgModelbox.showPopup(data);     
        });     
    },
    /*function for delete member*/
    
    deleteMember: function(memberIds,type,roleId) {
        var params = {'memberIds' : memberIds, 'type': type,'roleId': roleId};
        FgModelbox.hidePopup();
        FgXmlHttp.post(pathDeleteMember, params,false, FgMemberList.redrawList, false);
    },
     /*Call back function*/
     
    redrawList: function(data) {
        listTable.search('').draw();
    } 
};

FgMemberColumnHeading = {
    getColumnNames: function (tableSettingValue, teamId, workgroupId, general_table_title_array, exportflag) {
        tableColumnTitle = [];
        tableColumnTitle.push({"sTitle": "<input type='checkbox' name='check_all' id='check_all' class='dataTable_checkall'><i class='chk_cnt'></i>", "data": "edit", "bSortable": false,"sClass":'fg-checkbox-th'});

        if (exportflag == false) {
            tableColumnTitle.push({"sTitle":  jstranslations['contact_name'] , "data": "contactname", "bSortable": true,"sClass":'fg-pad-left-7'});
        }

        tableColumnTitle.push({'sTitle': jstranslations['Function'], 'data': 'Function'});
        $.each(tableSettingValue, function (keys, values) {

            $.each(values, function (key, value) {

                if (key == 'type' && values[key] == 'CF') {

                    $.each(jsonData['CF']['entry'], function (jsonKey, jsonValue) {

                        if (jsonValue['id'] == values['id']) {
                            if (exportflag == false) {
                                var atrShortName = jsonValue['shortName'];
                                if ($.inArray(jsonValue['id'], corrAddrFieldIds) != -1) {
                                    if (atrShortName.indexOf("(Korr.)") >= 0) {
                                        atrShortName = atrShortName.replace('(Korr.)', '');
                                    } else {
                                        atrShortName = atrShortName;
                                    }
                                }
//                                if ($.inArray(jsonValue['id'], invAddrFieldIds) != -1) {
//                                    if (atrShortName.indexOf("(Rg.)") >= 0) {
//                                        atrShortName = atrShortName.replace('(Rg.)', '');
//                                    } else {
//                                        atrShortName = atrShortName;
//                                    }
//                                }
                                tableColumnTitle.push({"sTitle": atrShortName, "data": values['name']});
                            } else {
                                var atrShortNamexport = $.trim(jsonValue['shortName']);

                                if ($.inArray(jsonValue['id'], corrAddrFieldIds) != -1) {
                                    if (atrShortNamexport.indexOf("(Korr.)") >= 0) {
                                        atrShortNamexport = atrShortNamexport.replace('(Korr.)', '');
                                    } else {
                                        atrShortNamexport = atrShortNamexport;
                                    }
                                }
//                                if ($.inArray(jsonValue['id'], invAddrFieldIds) != -1) {
//                                    if (atrShortNamexport.indexOf("(Rg.)") >= 0) {
//                                        atrShortNamexport = atrShortNamexport;
//                                    } else {
//                                        atrShortNamexport = atrShortNamexport ;
//                                    }
//                                }
                                tableColumnTitle.push({"sTitle": atrShortNamexport, "data": values['name']});
                            }
                        }

                    });
                } else if (key == 'type' && values[key] == 'G') {

                    if (general_table_title_array.hasOwnProperty(values['id'])) {
                        tableColumnTitle.push({"sTitle": general_table_title_array[values['id']], "data": values['name']});

                    }

                } else if (key == 'type' && (values[key] == 'RF' || values[key] == 'R')) {

                    if (tableSettingValue[keys]['sub_ids'] == 'all') {
                        if (values['id'] == teamId) {
                            var teamList=membersList;
                            var fcount = _.size(teamList);
                            var popovercontent = '';
                         //popovercontents
                         $.each(teamList, function(jsonKey, jsonValue){
                            popovercontent += jsonValue + "<br>"; 
                         })
                           
                            if (typeof jsonData['TEAM'] !== 'undefined' && _.size(jsonData['TEAM']) > 0) {
                               
                                var title = jsonData['TEAM']['title'];

                                var squarebracket = '';
                                if (fcount == 1) {
                                    squarebracket = '';
                                    popovercontent = popovercontent.replace(/<br>/g, "");
                                    dummyTitle = title;
                                    title = popovercontent;
                                    popovercontent = dummyTitle;
                                    if (values[key] == 'RF') {
                                        title = title + "&nbsp;(+ " + jstranslations['Function'] + ")";
                                    }
                                    title = (exportflag == false) ? "<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + title + "</span>" : title;

                                } else {
                                    if (values[key] == 'RF') {
                                        squarebracket = (exportflag == false) ? '&nbsp;(+ ' + jstranslations['Function'] + ')&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + fcount + '/' + fcount + '</span>]' : '&nbsp;(+ ' + jstranslations['Function'] + ')&nbsp;[' + fcount + '/' + fcount + ']';
                                    } else {
                                        squarebracket = (exportflag == false) ? '&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + fcount + '/' + fcount + '</span>]' : '&nbsp;[' + fcount + '/' + fcount + ']';
                                    }

                                }

                                var actualTitle = title;
                                popovervalues = squarebracket
                                if (exportflag == false) {
                                    tableColumnTitle.push({"sTitle": actualTitle + popovervalues, "data": values['name']});
                                } else {
                                    tableColumnTitle.push({"sTitle": actualTitle + popovervalues, "data": values['name']});
                                }
                                //tableColumnTitle.push({"sTitle":actualTitle, "data":values['name']});
                            }
                        } else if (values['id'] == workgroupId) {
                            var workgroupList=membersList;
                            var fcount = _.size(workgroupList);
                            var popovercontent = '';
                            //popovercontents
                            $.each(workgroupList, function(jsonKey, jsonValue){
                               popovercontent += jsonValue + "<br>"; 
                            })
                            if (typeof jsonData['WORKGROUP'] !== 'undefined' && _.size(jsonData['WORKGROUP']) > 0) {
                                var popovercontent = '';
                                var title = jsonData['WORKGROUP']['title'];
                                var squarebracket = '';
                                
                                if (_.chain(jsonData["WORKGROUP"]['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length == 1) {
                                    squarebracket = '';
                                    popovercontent = popovercontent.replace(/<br>/g, "");
                                    dummyTitle = title;
                                    title = popovercontent;
                                    popovercontent = dummyTitle;
                                    if (values[key] == 'RF') {
                                        title = title + "&nbsp;(+ " + jstranslations['Function'] + ")";
                                    }
                                    title = (exportflag == false) ? "<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + title + "</span>" : title;

                                } else {
                                    if (values[key] == 'RF') {
                                        squarebracket = (exportflag == false) ? '&nbsp;(+ ' + jstranslations['Function'] + ')&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + fcount + '/' + fcount+ '</span>]' : '&nbsp;(+ ' + jstranslations['Function'] + ')&nbsp;[' + fcount + '/' + fcount + ']';
                                    } else {
                                        squarebracket = (exportflag == false) ? '&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + fcount+ '/' + fcount+ '</span>]' : '&nbsp;[' + fcount + '/' + fcount + ']';
                                    }

                                }
                                var fcount = title;

                                popovervalues = squarebracket;
                                if (exportflag == false) {
                                    tableColumnTitle.push({"sTitle": fcount + popovervalues, "data": values['name']});
                                } else {
                                    tableColumnTitle.push({"sTitle": fcount + popovervalues, "data": values['name']});
                                }
                            }

                        }

                    } else {

                        if (values['id'] == teamId) {
                            var tcount = 0;
                            var fcount = 0;
                            var popovercontent = '';
                            var titleContent = '';
                            var teamList=membersList;
                            tcount=_.size(teamList);
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
                                var title = '';
                                if (fcount > 1) {
                                    title = jsonData['TEAM']['title'];
                                } else {
                                    title = titleContent;
                                    popovercontent = jsonData['TEAM']['title'];
                                }
                                var bracket = (fcount > 1) ? "&nbsp;[" + fcount + "/" + tcount + "]" : '';

                                var actualTitle = (values[key] == 'RF') ? title + "&nbsp;(+ " + jstranslations['Function'] + ")" : title + '';
                                if (fcount > 1) {
                                    popovervalues = actualTitle + '&nbsp;[<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + fcount + "/" + tcount + '</span>]';
                                } else {
                                    popovervalues = '<span class="fg-custom-popovers fg-dotted-br"  data-content="' + popovercontent + '">' + actualTitle + '' + '</span>';
                                }

                                if (exportflag == false) {
                                    tableColumnTitle.push({"sTitle": popovervalues, "data": values['name']});
                                } else {
                                    tableColumnTitle.push({"sTitle": actualTitle + bracket, "data": values['name']});
                                }

                                //tableColumnTitle.push({"sTitle":actualTitle, "data":values['name']});
                            }

                        } else if (values['id'] == workgroupId) {
                            var workgroupList=membersList;
                            var tcount=_.size(workgroupList);
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
                                        bracket = "&nbsp;[" + functionArray.length + "/" + tcount + "]";
                                    } else {
                                        bracket = '';
                                    }
                                    if (values[key] == 'RF') {
                                        fcount = title + "&nbsp;(+ " + jstranslations['Function'] + ")";
                                    } else {
                                        fcount = title;
                                    }

                                    var popovervalues = '';
                                    if (functionArray.length > 1) {
                                        popovervalues = fcount + "&nbsp;[<span class='fg-custom-popovers fg-dotted-br'  data-content='" + popovercontent + "'>" + functionArray.length + "/" + tcount + "</span>]";
                                    } else {
                                        popovervalues = "&nbsp;<span class='fg-custom-popovers fg-dotted-br'  data-content='" + popovercontent + "'>" + fcount + '' + "</span>";
                                    }

                                    if (exportflag == false) {
                                        tableColumnTitle.push({"sTitle": popovervalues, "data": values['name']});
                                    } else {
                                        tableColumnTitle.push({"sTitle": fcount + bracket, "data": values['name']});
                                    }

                                    // tableColumnTitle.push({"sTitle":fcount, "data":values['name']});
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
