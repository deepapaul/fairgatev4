/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


FgDocumentTableColumnHeading = {
    getColumnNames: function(tableSettingValue) {
        tableColumnTitle = [];
        tableColumnTitle.push({"sTitle": "<div class='fg-th-wrap'><i class='chk_cnt' ></i>&nbsp;<input type='checkbox' name='check_all' id='check_all' class='dataTable_checkall fg-dev-avoidicon-behaviour'></div>&nbsp;", "mData": "edit", "bSortable": false});
        tableColumnTitle.push({"sTitle": datatabletranslations['documentname'], "mData": 'docname'});

    $.each(tableSettingValue, function(keys, values) {

            $.each(values, function(key, value) {

                if (key == 'type' && values[key] == 'FO') {

                    $.each(jsonData['FILE']['entry'], function(jsonKey, jsonValue) {

                        if (jsonValue['id'] == values['id']) {
                            //console.log('errrfile');
                         tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
                        }

                    });
                } else if (key == 'type' && values[key] == 'UO') {

                   $.each(jsonData['USER']['entry'], function(jsonKey, jsonValue) {

                        if (jsonValue['id'] == values['id']) {
                            // console.log('errrUSER');  
                         tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
                        }

                    });
                } else if (key == 'type' && values[key] == 'DO') {
                    
                   $.each(jsonData['DATE']['entry'], function(jsonKey, jsonValue) {

                        if (jsonValue['id'] == values['id']) {
                          //console.log('errrDO');  
                         tableColumnTitle.push({"sTitle": jsonValue['title'], "mData": values['name']});
                        }

                    });
                } 

            });
        });
        
        return tableColumnTitle;
   

    }


};
