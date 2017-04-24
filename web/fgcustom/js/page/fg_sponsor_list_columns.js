var exportflag = true;
FgSponsorList = {
    checkIfExist: function(checkValue) {
        var flag = false;
        $.each(checkValue, function(keys, values) {
            if (keys === 'id' && _.chain(jsonData["CF"]['entry']).where({"id": values}).flatten().pluck('id').value() !== '') {
                flag = true;
            }
        })
        return flag;
    },
    deletecheck: function(tableSettingValue, jsonData) {
        $.each(tableSettingValue, function(keys, values) {
            $.each(values, function(key, value) {
                if (key === 'type' && values[key] === 'CF') {
                    if (!FgSponsorList.checkIfExist(values)) {
                        delete tableSettingValue[keys];
                    }
                } else if (_.has(jsonData, 'SS') && values['type'] == 'SS' && key == 'type') {
                    if (_.size(_.where(jsonData['SS']['entry'], {"id": values['id']})) > 0) {
                        inputLength = _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length;
                        var deleteCount = 0;
                        if (inputLength > 0) {
                            var subIds = tableSettingValue[keys]['sub_ids'].split(",");
                            $.each(subIds, function(subKeys, subValues) {
                                if(subValues=='') {
                                    subIds.splice(0, 1);
                                } else if(subValues !='all') {
                                        var objId = _.chain(jsonData["SS"]["entry"]).where({id: values['id']}).pluck("input").flatten().where({id: subValues}).value()
                                        if (objId.length == 0) {
                                            deleteCount++;
                                            delete subIds[subKeys];
                                        }
                                  }
                            });
                            if (deleteCount != inputLength) {
                                tableSettingValue[keys]['sub_ids'] = subIds.join();
                                if (tableSettingValue[keys]['sub_ids'].charAt(tableSettingValue[keys]['sub_ids'].length - 1) == ',') {
                                    tableSettingValue[keys]['sub_ids'] = tableSettingValue[keys]['sub_ids'].substring(0, tableSettingValue[keys]['sub_ids'].length - 1);
                                }
                                if (tableSettingValue[keys]['sub_ids'] == '' || tableSettingValue[keys]['sub_ids'] == ',') {
                                    tableSettingValue[keys]['sub_ids'] = 'all';
                                }
                            } else {
                                delete tableSettingValue[keys];
                            }
                        } else {
                            delete tableSettingValue[keys];
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

};
FgSponsorColumnHeading = {
    getColumnNames: function(tableSettingValue, general_table_title_array, exportflag) {
        tableColumnTitle = [];
        tableColumnTitle.push({"sTitle": "<div class='fg-th-wrap'><i class='chk_cnt' ></i>&nbsp;<input type='checkbox' name='check_all' id='check_all' class='dataTable_checkall'></div>&nbsp;", "mData": "edit", "bSortable": false});
        if (exportflag == false) {
            tableColumnTitle.push({"sTitle": "<span class='fg-contact-wrap'>" + datatabletranslations['contact_name'] + "</span>&nbsp;", "mData": "contactname", "bSortable": true});
        }
        if (contactType == 'archivedsponsor') {
            tableColumnTitle.push({"sTitle": datatabletranslations['archived_On'], "mData": "archived_on", "bSortable": true});
        }
        $.each(tableSettingValue, function(keys, values) {

            $.each(values, function(key, value) {

                if (key === 'type' && values[key] === 'CF') {

                    $.each(jsonData['CF']['entry'], function(jsonKey, jsonValue) {

                        if (jsonValue['id'] === values['id']) {
                            if (exportflag === false) {
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
                                if ($.inArray(jsonValue['id'], corrAddrFieldIds) !== -1) {
                                    if (atrShortNamexport.indexOf("(Korr.)") >= 0) {
                                        atrShortNamexport = atrShortNamexport;
                                    } else {
                                        atrShortNamexport = atrShortNamexport + "&nbsp;(" + datatabletranslations['Korr'] + ")";
                                    }
                                }
                                if ($.inArray(jsonValue['id'], invAddrFieldIds) !== -1) {
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
                } else if (key === 'type' && values[key] === 'SA') {
                    $.each(jsonData['SA']['entry'], function(jsonKey, jsonValue) {
                        if (jsonValue['id'] === values['id']) {
                            tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
                        }
                    });
                } else if (key == 'type' && (values[key] == 'SS')) {
                    var popovercontent = '';
                    var title = '';
                    var fcount = 0;
                    if (tableSettingValue[keys]['sub_ids'] == 'all') {
                        if (typeof jsonData['SS'] !== 'undefined' && _.size(jsonData['SS']['entry']) > 0) {
                            if (_.size(_.findWhere(jsonData['SS']['entry'], {"id": values['id']}))) {
                                title = _.where(jsonData['SS']['entry'], {"id": values['id']})[0].title;
                                fcount = _.size(_.where(jsonData['SS']['entry'], {"id": values['id']})[0]['input'])
                                inputObj = _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped;
                                $.each(inputObj, function(jsonKey, jsonValue) {
                                    popovercontent += jsonValue['title'] + "<br>";
                                });

                            }

                            var squarebracket = '';
                            if (fcount == 1) {
                                squarebracket = '';
                                popovercontent = popovercontent.replace(/<br>/g, "");
                                dummyTitle = title;
                                title = popovercontent;
                                popovercontent = dummyTitle;
                                title = (exportflag == false) ? "<span href='#' class='fg-custom-popovers fg-dotted-br' data-content='" + popovercontent + "'>" + title + "</span>" : title;

                            } else {
                                squarebracket = '&nbsp;[' + datatabletranslations['All'] + ']';
                                console.log(squarebracket);
                            }

                            var actualTitle = title;
                            popovervalues = squarebracket
                            if (exportflag == false) {
                                tableColumnTitle.push({"sTitle": actualTitle + popovervalues, "mData": values['name']});
                            } else {
                                tableColumnTitle.push({"sTitle": actualTitle + popovervalues, "mData": values['name']});
                            }
                        }
                    } else {
                        if (_.size(_.where(jsonData['SS']['entry'], {"id": values['id']})[0]['input']) > 0) {
                            inputObj = _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped;
                            functionArray = values['sub_ids'].split(",");
                            $.each(functionArray, function(iterationkey, iterationvalue) {
                                titleContent = _.chain(jsonData["SS"]['entry']).where({"id": values['id']}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value();
                                popovercontent += _.chain(jsonData["SS"]['entry']).where({"id": values['id']}).pluck("input").flatten().where({id: iterationvalue}).pluck("title").value() + "<br>";
                            })
                            if (functionArray.length > 1) {
                                var title = _.where(jsonData['SS']['entry'], {"id": values['id']})[0].title;
                            } else {
                                var title = titleContent;
                                popovercontent = _.where(jsonData['SS']['entry'], {"id": values['id']})[0].title;
                            }
                            var bracket = (functionArray.length > 1) ? "&nbsp;[" + functionArray.length + "/" + _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length + "]" : '';

                            fcount = title;
                            var popovervalues = '';
                            if (functionArray.length > 1) {
                                popovervalues = fcount + "&nbsp;[<span class='fg-custom-popovers fg-dotted-br'  data-content='" + popovercontent + "'>" + functionArray.length + "/" + _.chain(jsonData['SS']['entry']).where({"id": values['id']}).pluck('input').flatten()._wrapped.length + "</span>]";
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
            });
        }); //end of main iteration

        return tableColumnTitle;
    }

};