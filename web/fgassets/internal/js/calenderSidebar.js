var FgCalenderSidebar = function () {
    var settings;
    var defaultSettings = {
        localStorageName: '', //localstorage name of the items according to the type
        container: '.fg-dev-calender-filter',
        jsonData: '',
        selectedFilters: '',
        clubType: 'federation',
        checkedfilterValue: '',
        adminFlag: 0,
        roleadminFlag: 0,
        translations: '',
        clubHeirarchy: {'federation_club': ['federation'], 'sub_federation': ['federation'], 'sub_federation_club': ['federation', 'sub_federation']},
        initCompleteCallback: function ($object) {
        },
        tabCompleteCallback: function ($object) {
        }
    };
    var sidebarLoad = function () {
        //iterate first level of index value
        $.each(settings.jsonData, function (key, filterCategory) {
            
            //check second level of iteration is need or not(subitems checking)
            switch (key) {
                case 'general':
                    //creat first level ul
                    $('<ul />', {id: "fg-general-filter-levels", class: 'fg-filter-item '}).appendTo('#fg-general-filter');
                    if(_.size(filterCategory) >0) {                    
                    $.each(filterCategory, function (itemKey, filtervalues) {
                        if (_.size(filtervalues['subItems']) > 0) {
                            //second level iterations    
                            $('<li />', {class: 'fg-filter-item has-child', id: 'general-level1-' + filtervalues['categoryType'] + '-' + filtervalues['id']}).appendTo('#fg-general-filter-levels').append('<div class="filter-content"><label><input data-list="no" class ="fg-filter-checkbox" type="checkbox" name="' + filtervalues['categoryType'] + '"><span class="fg-filter-label"><i class="fa fa-angle-right arrow"></i>' + filtervalues['title'] + '</span></label></div>');
                            $('<ul />', {id: 'general-level2-' + filtervalues['categoryType'] + '-' + filtervalues['id'], class: 'fg-filter-items-lvl-1'}).appendTo('#general-level1-' + filtervalues['categoryType'] + '-' + filtervalues['id']);
                            $.each(filtervalues['subItems'], function (secondKey, subitem) {
                                var ownEvent = '';
                                if (("own" in subitem) &&(subitem.own===true)) {
                                      ownEvent = 'own-event="yes"';
                                }
                                //third level iteration
                                $('<li />', {class: 'fg-filter-item'}).appendTo('#general-level2-' + filtervalues['categoryType'] + '-' + filtervalues['id']).append('<div class="filter-content"><label><input data-list="no" data-type="' + filtervalues['id'] + '" class ="fg-filter-checkbox" event="all" ' + ownEvent + ' type="checkbox" data-id="' + subitem['id'] + '"><span class="fg-filter-label">' + subitem['title'] + '</span></label></div>');
                            })

                        } else {
                            $('<li />', {class: 'fg-filter-item'}).appendTo('#fg-general-filter-levels').append('<div class="filter-content"><label><input data-list="no" data-type="' + filtervalues['type'] + '" class ="fg-filter-checkbox" own-event="yes" type="checkbox" event="all"  data-id="' + filtervalues['id'] + '"><span class="fg-filter-label"><i class="fa-empty"></i>' + filtervalues['title'] + '</span></label></div>');
                        }
                    });
                }
                    //create without area (without area filter is to be shown only if atleast one event without area exists in current club)  
                    if (settings.adminFlag == 1 && settings.jsonData.eventsWithoutArea != 0 && typeof settings.jsonData.eventsWithoutArea != typeof undefined ) {
                        $('<li />', {class: 'fg-filter-item'}).appendTo('#fg-general-filter-levels').append('<div class="filter-content"><label><input data-list="no" class ="fg-filter-checkbox" event="all" type="checkbox" data-id="IS NULL" data-type="GEN_WITHOUT_AREA"><span class="fg-filter-label">' + settings.translations.WithoutArea + '<i class="fa fa-warning"></i> </span></label></div>');
                    }



                    break;


                case 'category':

                    //creat first level ul
                    $('<ul />', {id: "fg-category-filter-levels", class: 'fg-filter-item'}).appendTo('#fg-category-filter');
                    var secondFilterCat = filterCategory;
                    if (_.size(_.where(filterCategory, {type: settings.clubType})) > 0) {
                        filterCategory = _.where(filterCategory, {type: settings.clubType});
                        $.each(filterCategory, function (itemKey, filtervalues) {
                            if (_.size(filtervalues['subItems']) > 0) {
                                //second level iterations
                                $.each(filtervalues['subItems'], function (secondKey, subitem) {
                                    //third level iteration
                                    $('<li />', {event: 'all', ownEvent: 'yes', class: 'fg-filter-item'}).appendTo('#fg-category-filter-levels').append('<div class="filter-content"><label><input data-list="no" class ="fg-filter-checkbox" checked="true"  type="checkbox" data-id="' + subitem['id'] + '" data-type="CA"><span class="fg-filter-label">' + subitem['title'] + '</span></label></div>');
                                })

                            }

                        });


                    }

                    //For create heirarchy category creation
                    if (settings.clubType in settings.clubHeirarchy) {
                        $.each(settings.clubHeirarchy[settings.clubType], function (key, values) {
                            //check the heirarchy value exist in filtervalues
                            var clubtype = values.toString();
                            if (_.size(_.where(secondFilterCat, {type: clubtype})) > 0) {
                                var secondFilterCatsub = _.where(secondFilterCat, {type: clubtype});
                                $.each(secondFilterCatsub, function (itemKey, filtervalues) {
                                    if (_.size(filtervalues['subItems']) > 0) {
                                        //second level iterations
                                        var appendString = "";
                                        var catId = 'CA_' + values;
                                        var subTitle = filtervalues['title'];
                                        $.each(filtervalues['subItems'], function (secondKey, subitem) {
                                            //third level iteration
                                            appendString += subitem['id'] + ',';
                                        })
                                        appendString = appendString.slice(0, -1);

                                        $('<li />', {class: 'fg-filter-item'}).appendTo('#fg-category-filter-levels').append('<div class="filter-content"><label><input data-list="no" class ="fg-filter-checkbox"  data-value="' + appendString + '"  type="checkbox" checked="true" data-id="' + catId + '" data-type="CA_LEVELS"><span class="fg-filter-label">' + subTitle + '</span></label></div>');

                                    }
                                });
                            }
                        });
                    }
                    //create without category  
                    if (_.size(_.where(secondFilterCat, {type: 'withoutcategory'})) > 0 && (settings.adminFlag == 1 || settings.roleadminFlag == 1)) {
                        $('<li />', {class: 'fg-filter-item'}).appendTo('#fg-category-filter-levels').append('<div class="filter-content"><label><input data-list="no" class ="fg-filter-checkbox"  type="checkbox" data-id="IS NULL" data-type="CATEGORY_WITHOUT"><span class="fg-filter-label">' + settings.translations.Withoutcategory + '<i class="fa fa-warning"></i> </span></label></div>');
                    }

                    break;
                case 'years':
                    $('<ul />', {id: "fg-time-filter-levels", class: 'fg-filter-item '}).appendTo('#fg-time-filter');
                    $.each(filterCategory, function (itemKey, subitem) {
                        var label = (subitem['currentyear'] == 'no') ? subitem['label'] : subitem['currentyear'];
                        var checked = (subitem['currentyear'] == 'no') ? '' : 'checked=checked';
                        $('<li />', {class: 'fg-filter-item'}).appendTo('#fg-time-filter-levels').append('<div class="filter-content"><label><input data-value="' + subitem['start'] + '#' + subitem['end'] + '" data-type="year" class ="fg-filter-checkbox"  data-list="yes" event="all"  type="radio" data-id="' + subitem['label'] + '" name="fg-year-value" ' + checked + '><span>' + label + '</span></label></div>');

                    });
                    break;

            }


        })

    };

    var CalendarCategoryCreatePopup = function () {
        $('body').on('click', '.fg-add-category');
        $('body').on('click', '.fg-add-category', function () {
            var rand = $.now();
            $.post(calendarCategorySave, {'catId': rand, 'defaultLang': defaultlanguage, 'noParentLoad': true, 'sidebarCreate': 'true'}, function (data) {
                FgModelbox.showPopup(data);
            });
        });
    };

    var topmenuclick = function () {
        $('.fg-filtermenu').on('click', function () {
            var clickedValue = $(this).attr('data-value');
            if (clickedValue == 'all') {
                $("#fg-own-event").removeClass('menuactive');
                $("input[type='checkbox'][event^='" + clickedValue + "']").prop('checked', true);
            } else {
                $("input[type='checkbox'][event^='all']").prop('checked', false);
                $("input[type='checkbox'][own-event^='yes']").prop('checked', true);
            }
            selectedFilters();
            calenderCall();
            tristateConfig();
            $.uniform.update();
        })

    };
    var filtercheckboxClick = function () {

        $('.fg-filter-checkbox').on('change', function () {
            var currentItem = $(this).prop('checked'); //get current checkbox
            var parentLvl1 = $(this).parents('li').first(); //get current checkbox parent li
            if (currentItem) {
                parentLvl1.find('input[type="checkbox"]').prop('checked', true); // set checked all   child checkox property

            } else {
                parentLvl1.find('input[type="checkbox"]').prop('checked', false); // clear all  checked child checkox property

            }
            tristateConfig();
            var seletectedItems = selectedFilters();
            calenderCall();

            $.uniform.update();
        })

    };
    var calenderCall = function () {
        FgFullCalendar.addParameter('filter', JSON.stringify(settings.checkedfilterValue), true);
        FgFullCalendar.forceRefetch(true);
        eventlistType = localStorage.getItem(calendarviewStoragename);
        if (eventlistType != 'list') {
            FgFullCalendar.redraw();
        } else {
            FgFullCalendar.renderList();
        }

    }
    var selectedFilters = function () {
        var seletectedItem = [];

        $('.fg-calendar-filter-list input[type="checkbox"]:checked, .fg-calendar-filter-list input[type="radio"]:checked').each(function () {
            // If input is visible and checked...
            if ($(this).is(':visible')) {
                var dataId = $(this).attr('data-id');
                var type = $(this).attr('data-type');
                var dataValue = $(this).attr('data-value');
                seletectedItem.push({'id': dataId, 'type': type, 'value': dataValue});
            }

        });
        settings.checkedfilterValue = seletectedItem;
        localStorage.setItem(settings.localStorageName, JSON.stringify(seletectedItem));
        return seletectedItem;
    };
    var defaultSelection = function () {

        var defaultFilterstringValue = localStorage.getItem(settings.localStorageName);
        //set the values from localstorage
        if (defaultFilterstringValue !== 'undefined' && defaultFilterstringValue != '' && defaultFilterstringValue != null) {
            var defaultFiltervalues = $.parseJSON(defaultFilterstringValue);
            $.each(defaultFiltervalues, function (key, selectedItems) {
                $('.fg-calendar-filter-list input[data-type="' + selectedItems.type + '"][data-id="' + selectedItems.id + '"]').prop('checked', true);
            })
            $("#fg-own-event").removeClass('menuactive');
        } else {
            $("#fg-own-event").trigger('click');
            $("#fg-own-event").addClass('menuactive');
        }
        tristateConfig();
        selectedFilters();
        $.uniform.update();
    }
    var filterToggle = function () {

        $('.fg-filter-item.has-child > .filter-content:first-child .fg-filter-label').toggle(
                function () {
                    $(this).parent().parent().parent().addClass('open');
                    $(this).parent().parent().find('.arrow:first-child').removeClass('fa-angle-right').addClass('fa-angle-down');
                },
                function () {
                    $(this).parent().parent().parent().removeClass('open');
                    $(this).parent().parent().find('.arrow:first-child').addClass('fa-angle-right').removeClass('fa-angle-down');

                }
        );
    }

    var selectOwnEvent = function () {
        $("input[type='checkbox'][own-event^='yes']").prop('checked', true);
        selectedFilters();
        $.uniform.update();
    }
    var callUniform = function () {
        $(".fg-calendar-filter-list input[type=checkbox],.fg-calendar-filter-list input[type=radio]").uniform();
    }

    var tristateConfig = function () {
        /*
         ================================================================================================ 
         *  Check box tri state for View List
         ================================================================================================ 
         */

        /*============================================================================================================ 
         *  to check checked property of each li child and set property to checked/unchecked/indetermined
         *============================================================================================================ */

        var eachCheckBox = $('.fg-calendar-filter-list input[type="checkbox"]');
        var eachParent = $('fg-calendar-filter-list').children('li');

        //////////// Begin loop//////////////////

        $.each(eachCheckBox, function (key, item) {
            var currentParent = $(this).parents('li').first(); // get first li of current checkbox
            if (currentParent.hasClass('has-child')) { // check current li has child

                var totCheckBoxInParent = currentParent.children('ul').find('input[type="checkbox"]').length; // get all checkbox count in current li
                var totCheckedCheckBoxInParent = currentParent.children('ul').find('input[type="checkbox"]:checked').length; // get all checked checkbox count in current li

                if (totCheckBoxInParent == totCheckedCheckBoxInParent) { //check if  counts are same property to checked
                    currentParent.children().children().children('.checker').removeClass('indeterminate');
                    currentParent.children().children().children('.checker').find('input[type=checkbox]').prop({
                        'indeterminate': false,
                        'checked': true
                    });

                } else if (totCheckedCheckBoxInParent > 0) { //check if  counts are not same and not zero property to indetermined

                    currentParent.children().children().children('.checker').addClass('indeterminate');
                    currentParent.children().children().children('.checker').find('input[type=checkbox]').prop({
                        'indeterminate': true,
                        'checked': false
                    });

                } else if (totCheckedCheckBoxInParent == 0) {

                    currentParent.children().children().children('.checker').removeClass('indeterminate');
                    currentParent.children().children().children('.checker').find('input[type=checkbox]').prop({
                        'indeterminate': false,
                        'checked': false
                    });
                }
            }
        });
        $.uniform.update();



    }


    return {
        initialize: function (options) {
            settings = $.extend(true, {}, defaultSettings, options);
            //call sidebar creation
            $(".fg-filterblock").hide();
            sidebarLoad();

            $(".fg-filterblock").show();
            if (localStorage.getItem(calendarviewStoragename) == 'list') {
                $('#fg-time-filter').removeClass('fg-time-filter-hide');
            } else {
                $('#fg-time-filter').addClass('fg-time-filter-hide');
            }
            //call input uniform plugin
            callUniform();
            // add event to top menu 
            topmenuclick();
            //add event to input box
            //toggle initialization
            filterToggle();

            filtercheckboxClick()
            //set default option
            defaultSelection();
            CalendarCategoryCreatePopup();
            settings.initCompleteCallback.call({'filter': JSON.stringify(settings.checkedfilterValue),'search':$('#fg_dev_member_search').val()});
        },
        getFilterdata: function () {
            selectedFilters();
        },
        setTristate:function() {
           tristateConfig(); 
        }

    }

}();







