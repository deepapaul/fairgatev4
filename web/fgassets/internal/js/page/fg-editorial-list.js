

//column heading 
FgArticleListColumnHeading = {
    getColumnNames: function (tableSettingValue, listingType) {
        
        var colDefs = [];
        var i = 0;

        colDefs.push({"title": "<div class='fg-th-wrap'><i class='chk_cnt' ></i>&nbsp;&nbsp;<input type='checkbox' name='check_all' id='check_all' class='dataTable_checkall fg-dev-avoidicon-behaviour'></div>&nbsp;", "targets": i++, orderable: false, data: function (row, type, val, meta) {
                var dragArrow = '<i class="fa fg-sort ui-draggable"></i>';
                row.displayData = dragArrow + ' <input class="dataClass fg-dev-avoidicon-behaviour" type="checkbox" id=' + row['articleId'] + ' name="check"  value="0"  >';
                return row.displayData;
            }});

        if (listingType == 'ARCHIVE') {
            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('ARCHIVED_AT'), "targets": i++, data: function (row, type, val, meta) {

                    if (row['F_ARCHIVING_DATE'] != '' && row['F_ARCHIVING_DATE'] != null) {
                        row.displayData = row['F_ARCHIVING_DATE'] ? FgLocaleSettings.formatDate(row['F_ARCHIVING_DATE'], 'datetime', 'YYYY-MM-DD hh:mm:ss') : '';
                        row.sortData = FgArticleListColumnHeading.toTimestamp(row['F_ARCHIVING_DATE']);
        }

                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('ARCHIVED_BY'), "targets": i++, data: function (row, type, val, meta) {
                    if (row['ARCHIVED_BY'] != '' && row['ARCHIVED_BY'] != null) {
                        if (row['ARCHIVED_BY_ID'] != 1 && row['ARCHIVED_BY_ACTIVE'] > 0) {
                            var pathForComProf = communityProfilePath;
                            var createdPath = pathForComProf.replace("contactIdReplace", row['ARCHIVED_BY_ID']);
                            row.displayData = "<a href='" + createdPath + "'>" + row['ARCHIVED_BY'] + "</a>";
                        } else {
                            row.displayData = row['ARCHIVED_BY'];
                        }
                        row.sortData = row['ARCHIVED_BY'];
                    }
                    return row;
                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
        }

        colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('TITLE'), "targets": i++, data: function (row, type, val, meta) {
                var pathForEdit = articleEditPath;
                var editPath = pathForEdit.replace("articleIdReplace", row['articleId']);
                var pathForDetail = articleDetailsPath;
                var detailsPath = pathForDetail.replace("articleIdToReplace", row['articleId']);
                var badge = (row['STATUS'] == 'planned') ? '<span class="fg-badge fg-cms-stat-badge fg-badge-green">'+ colValTrans.valTrans(row['STATUS']).toUpperCase() +'</span>&nbsp;' : ((row['STATUS'] == 'draft') ? '<span class="fg-badge fg-cms-stat-badge fg-badge-dark-grey">'+ colValTrans.valTrans(row['STATUS']).toUpperCase() +'</span>&nbsp;' : '' )
                row.displayData = badge+ ' <a href="' + detailsPath + '" class="fg-dev-article-title">' + _.escape(row['title']) + '<a>' + '&nbsp;<a href="' + editPath + '" class="fg-tableimg-hide"><i class="fa fa-pencil-square-o fg-pencil-square-o"></i></a>';
                row.sortData = row['title'];
                return row;
            }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});


        $.each(tableSettingValue, function (keys, values) {
            if (listingType == 'EDITORIAL' || values['id'] != 'ARCHIVING_DATE') {
                if ((commentSettings == 1 && values['id'] == 'COMMENTS') || (values['id'] != 'COMMENTS')) {
                    switch (values['id']) {
                        case "PUBLICATION_DATE"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('PUBLICATION_DATE'), "targets": i++, data: function (row, type, val, meta) {
                                    if (typeof row['PUBLICATION_DATE'] != 'undefined' && row['PUBLICATION_DATE'] != '') {
                                        row.displayData = row['PUBLICATION_DATE'] ? FgLocaleSettings.formatDate(row['PUBLICATION_DATE'], 'datetime', 'YYYY-MM-DD hh:mm:ss') : '';
                                        row.sortData = FgArticleListColumnHeading.toTimestamp(row['PUBLICATION_DATE']);
            }
                                    return row;
                                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
                            break;
                        case "EDITED_AT"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('EDITED_AT'), "targets": i++, data: function (row, type, val, meta) {
                                    if (typeof row['EDITED_AT'] != 'undefined' && row['EDITED_AT'] != '') {
                                        row.displayData = row['EDITED_AT'] ? FgLocaleSettings.formatDate(row['EDITED_AT'], 'datetime', 'YYYY-MM-DD hh:mm:ss') : '';
                                        row.sortData = FgArticleListColumnHeading.toTimestamp(row['EDITED_AT']);
                                    }
                                    return row;
                                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
                            break;
                        case "CREATED_AT"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('CREATED_AT'), "targets": i++, data: function (row, type, val, meta) {
                                    if (typeof row['CREATED_AT'] != 'undefined' && row['CREATED_AT'] != '') {
                                        row.displayData = row['CREATED_AT'] ? FgLocaleSettings.formatDate(row['CREATED_AT'], 'datetime', 'YYYY-MM-DD hh:mm:ss') : '';
                                        row.sortData = FgArticleListColumnHeading.toTimestamp(row['CREATED_AT']);
                                    }
                                    return row;
                                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
                            break;
                        case "ARCHIVING_DATE"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('ARCHIVING_DATE'), "targets": i++, data: function (row, type, val, meta) {
                                    if (typeof row['ARCHIVING_DATE'] != 'undefined' && row['ARCHIVING_DATE'] != '') {
                                        row.displayData = row['ARCHIVING_DATE'] ? FgLocaleSettings.formatDate(row['ARCHIVING_DATE'], 'datetime', 'YYYY-MM-DD hh:mm:ss') : '';
                                        row.sortData = FgArticleListColumnHeading.toTimestamp(row['ARCHIVING_DATE']);
                                    }
                                    return row;
                                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
                            break;
                        case "EDITED_BY"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('EDITED_BY'), "targets": i++, data: function (row, type, val, meta) {
                                    if (row['EDITED_BY'] != '' && row['EDITED_BY'] != null) {
                                        if (row['EDITED_BY_ID'] != 1 && row['EDITED_BY_ACTIVE'] > 0) {
                                            var pathForComProf = communityProfilePath;
                                            var createdPath = pathForComProf.replace("contactIdReplace", row['EDITED_BY_ID']);
                                            row.displayData = "<a href='" + createdPath + "'>" + row['EDITED_BY'] + "</a>";
                                        } else {
                                            row.displayData = row['EDITED_BY'];
                                        }
                                        row.sortData = row['EDITED_BY'];
                                    }
                                    return row;
                                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
                            break;
                        case "CREATED_BY"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('CREATED_BY'), "targets": i++, data: function (row, type, val, meta) {
                                    if (row['CREATED_BY'] != '' && row['CREATED_BY'] != null) {
                                        if (row['CREATED_BY_ID'] != 1 && row['CREATED_BY_ACTIVE'] > 0) {
                                            var pathForComProf = communityProfilePath;
                                            var createdPath = pathForComProf.replace("contactIdReplace", row['CREATED_BY_ID']);
                                            row.displayData = "<a href='" + createdPath + "'>" + row['CREATED_BY'] + "</a>";
                                        } else {
                                            row.displayData = row['CREATED_BY'];
                                        }
                                        row.sortData = row['CREATED_BY'];
                                    }
                                    return row;
                                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
                            break;
                        case "AREAS"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('AREAS'), "targets": i++, data: function (row, type, val, meta) {
                                    var isClub = (row['isClub'] == 1) ? clubTerminology : '';
                                    var areasStr = (row['AREAS']) ? row['AREAS'] : '';
                                    var areas = (!isClub || !areasStr) ? (!isClub) ? areasStr : (!areasStr) ? isClub : '' : isClub + '*##*' + areasStr;
                                    row.displayData = areas ? FgInternal.createPopover(areas, columnSettings.titleTranslate('AREAS')) : '';
                                    row.sortData = areas;
                                    return row;
                                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
                            break;
                        case "CATEGORIES"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('CATEGORIES'), "targets": i++, data: function (row, type, val, meta) {
                                    var categories = row['CATEGORIES'];
                                    row.displayData = categories ? FgInternal.createPopover(categories, columnSettings.titleTranslate('CATEGORIES')) : '';
                                    row.sortData = categories;
                                    return row;
                                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'displayData'}});
                            break;
                        case "STATUS"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('STATUS'), "targets": i++, data: function (row, type, val, meta) {
                                    row.displayData = row['STATUS'] ? colValTrans.valTrans(row['STATUS']) : '';
                                    return row;
                                }, render: {"_": 'displayData', "display": 'displayData', 'filter': 'displayData'}});
                            break;
                        case "STATUS"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('STATUS'), "targets": i++, data: function (row, type, val, meta) {
                                    row.displayData = row['STATUS'] ? colValTrans.valTrans(row['STATUS']) : '';
                                    return row;
                                }, render: {"_": 'displayData', "display": 'displayData', 'filter': 'displayData'}});
                            break;
                        case "SCOPE"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate('SCOPE'), "targets": i++, data: function (row, type, val, meta) {
                                    if (row['SCOPE']) {
                                        var lockIcn = (row['SCOPE'] == 'PUBLIC') ? 'fa-unlock' : 'fa-lock';
                                        row.displayData = '<i class="fg-custom-popovers fa ' + lockIcn + '"  data-trigger="hover" data-placement="bottom" data-content="' + colValTrans.valTrans(row['SCOPE']) + '"></i>';
                                        row.sortData = row['SCOPE'];
                                        return row;
                                    }
                                }, render: {"_": 'sortData', "display": 'displayData', 'filter': 'sortData'}});
                            break;
                        case "IMAGE_VIDEOS"  :
                        case "COMMENTS"  :
                        case "LANGUAGES"  :
                            colDefs.push({"title": '&nbsp;&nbsp;' + columnSettings.titleTranslate(values['id']), "targets": i++, data: function (row, type, val, meta) {
                                    row.displayData = row[values['id']]
                                    return row;
                                }, render: {"_": 'displayData', "display": 'displayData', 'filter': 'displayData'}});
                            break;
                    }

                }
            }
        });
        return colDefs;
    },
    toTimestamp: function (date) {
        var timestamp = 0;
        if (typeof date != 'undefined' || date != '') {
            var momentObj = moment(date, "YYYY-MM-DD HH:mm:ss");
            timestamp = momentObj.format('x');
    }
        return timestamp;
}
}
FgEditorialList = {
    init: function () {
        tableFilterStorageName = 'ARTICLE_INTERNAL_FILTER_' + memberType + clubId + '-' + contactId;
        tableFilterVisible = 'ARTICLE_INTERNAL_FILTER_VISIBLE' + memberType + clubId + '-' + contactId;
        //sidebarActive = localStorage.getItem('activeSubMenuVar-' + clubId + '-' + contactId);
        sidebarActive = FgSidebar.getActiveMenu();
        (sidebarActive == 'li_ARCHIVE_ARCHIVE_ART') ? FgEditorialList.dataTableOpt('ARCHIVE') : FgEditorialList.dataTableOpt('EDITORIAL');
        preActive = sidebarActive;
        $('#articleFilterWrap select').val('ALL');
        if (localStorage.getItem(tableFilterStorageName) !== null) {
            FgArticleFilter.loadFilter();
        }
    },
    dataTableOpt: function (listingType) {
        tableColumnTitleStorage = 'InternaltableColumnValue_' + memberType + clubId + '-' + contactId;
        tableSettingValueStorage = 'articleInternaltableSettingValue_' + memberType + clubId + '-' + contactId;
        
        if (localStorage.getItem(tableSettingValueStorage) !== null) {
            tableSettingValue = (localStorage.getItem(tableSettingValueStorage));
        }
        columnDefs = FgArticleListColumnHeading.getColumnNames($.parseJSON(tableSettingValue), listingType);
        localStorage.setItem(tableColumnTitleStorage, JSON.stringify(columnDefs));

        var fixedColCnt = (listingType == 'ARCHIVE') ? 4 : 2;
        datatableOptionsArticle = {
            ajaxHeader: true,
            fixedcolumn: true,
            fixedcolumnCount: fixedColCnt,
            ajaxPath: ajaxUrlEditorialListing,
            ajaxparameterflag: true,
            isCheckbox: false,
            module:'ARTICLE',
            ajaxparameters: {
                columns: tableSettingValue,
                listingType: listingType,
            },
            manipulationFlag: true,
            manipulationFunction: 'manipulateArticleColumnFields',
            popupFlag: true,
            displaylength: 50,
            serverSideprocess: false,
            columnDefFlag: true,
            columnDefValues: columnDefs,
            initialSortingFlag: true,
            initialsortingColumn: 1,
            initialSortingorder: 'asc',
            draggableFlag: true,
            rowlengthshow: true,
            rowlengthWrapperdivid: 'fg_dev_memberlist_row_length',
            widthResize: false,
            showFilter: true,
            tableFilterStorageName: tableFilterStorageName,
            nextPreviousOptions: {column: 'articleId', path: saveNextPreviousArticlePath, key: saveNextPreviousArticleKey}
        };
         datatableOptionsArchive = {
            ajaxHeader: true,
            fixedcolumn: true,
            fixedcolumnCount: fixedColCnt,
            ajaxPath: ajaxUrlEditorialListing,
            ajaxparameterflag: true,
            isCheckbox: false,
            module:'ARTICLE',
            ajaxparameters: {
                columns: tableSettingValue,
                listingType: listingType
            },
            manipulationFlag: true,
            manipulationFunction: 'manipulateArticleColumnFields',
            popupFlag: true,
            displaylength: 50,
            serverSideprocess: false,
            columnDefFlag: true,
            columnDefValues: columnDefs,
            initialSortingFlag: true,
            initialsortingColumn: 1,
            initialSortingorder: 'asc',
            draggableFlag: true,
            rowlengthshow: true,
            rowlengthWrapperdivid: 'fg_dev_memberlist_row_length',
            widthResize: false,
            showFilter: true,
            tableFilterStorageName: tableFilterStorageName,
            nextPreviousOptions: {column: 'articleId', path: saveNextPreviousArticlePath, key: saveNextPreviousArticleKey}
        };
    },
    getArticleTitles: function (checkedIds) {
        var articleIds = checkedIds.split(",");
        var articleArray = [];
        if (articleIds.length > 1) {
            _.each(articleIds, function (id) {
               
                articleData = _.findWhere(editorialListJson.aaData, {articleId: id.toString()});
                
                articleArray.push({'id': id, 'title': articleData.title})
            });
        } else {
            articleArray.push({'id': checkedIds, 'title': ''})
        }
        return articleArray;
    },
}
//datatable fields manipulation for editorial listing
function manipulateArticleColumnFields(json)  {
    editorialListJson = json;
    if (json.actionMenu.adminFlag == 0) {
        $('.fg-action-menu').removeClass('fg-active-IB').addClass('fg-dis-none');
        listTable.column(0).visible(false);
    } else {
        $('.fg-action-menu').addClass('fg-active-IB').removeClass('fg-dis-none');
        listTable.column(0).visible(true);
    }

    window.actionMenuTextDraft = {'active': {'none': json.actionMenu.none, 'single': json.actionMenu.single, 'multiple': json.actionMenu.multiple}};
    scope.$apply(function () {
        scope.menuContent = window.actionMenuTextDraft;
    });

}

