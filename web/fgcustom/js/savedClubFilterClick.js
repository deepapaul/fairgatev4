

$('body').on('click', '.openfilterClass', function() {
        if ($(this).hasClass('fg-dev-arrow-fold')) {
            $(this).find('i').addClass('fa-plus-square-o');
            $(this).find('i').removeClass('fa-minus-square-o');
            $(this).removeClass('fg-dev-arrow-fold');
            var filter_id = $(this).attr('filter_id');
            $('#open'+ filter_id).addClass('hide');         
        } else {
            var filter_id = $(this).attr('filter_id');       
            $(this).find('i').removeClass('fa-plus-square-o');
            $(this).find('i').addClass('fa-minus-square-o');
            $(this).addClass('fg-dev-arrow-fold');
            //setTimeout(function(){  
                  $('#open'+ filter_id).removeClass('hide');
            //},500)
        }
        /* The if condition will help to rectrict the same request for mutiple times */
        if (!($(this).hasClass('collapsed') || $(this).hasClass('FilterDataExist'))) {
            if (!$(this).hasClass('FilterDataExist')) {
                var firstTime = true;
            }
            $('#editedFilter').val(parseInt($('#editedFilter').val()) + 1);
            $(this).addClass('FilterDataExist');
            var target_id = '#open' + $(this).attr('filter_id');
            filterId = $(this).attr('filter_id');
            filterIds.push(filterId);
            var storageName_jsn;
            
            var commonFilterName;
            var commonFilterValue;
            var filterString;
            if(filterType=='contact') {
                commonStorageName=filterId;
                commonFilterName=filterId;
                filterString='contact_filter';
            } else if (filterType == 'sponsor') {
                commonStorageName = filterId;
                commonFilterName = filterId;
                filterString = 'sponsor_filter';
            } else {
                commonStorageName='clubFilter_'+filterId+'_'+clubId+'_'+contactId;
                commonFilterName='club_filter';
                filterString='club_filter';
            }
            
            /* The Filter data is fetch from DB on request */

            var filUrl=clubDataSingleUrl+'?id=' + filterId;
            //alert(filUrl);
            $.getJSON(filUrl).done(function(data_storage) {
                /* Replace the "contact_filter" with filter id to work  in the settings page */

                storageName_jsn = data_storage.singleSavedFilter['0'].filterData;
                if((filterType=='contact') || (filterType=='sponsor')) {
                    storageName_jsn = storageName_jsn.replace(filterString, filterId);
                }
                localStorage.setItem(commonStorageName, storageName_jsn);
                var filterArr = '';
                $(target_id).html('');
                var addbtnId = '#accCriteria' + filterId;
                filterCount++;
                
                $(target_id).searchFilter({
                    jsonUrl: filterClubDataUrl,
                    save: '#save_' + filterId,
                    storageName: commonStorageName,
                    filterName: commonFilterName,
                    addBtn: addbtnId,
                    customSelect: true,
                    dateFormat: FgApp.dateFormat,
                    conditions: filterCondition,
                    selectTitle: SELECTTYPE,
                    criteria: '<div class="col-md-1"><span class="fg-criterion">'+CRITERIA+':</span></div>',
                    onComplete: function(data) { 
                        if (data != 0) {
                            /* The Success call back add the stringfied data to a hidden input, 
                             * this is for work the dirty form.
                             *   */
                            var stringifyed_data = JSON.stringify(data);
                            stringifyed_data = stringifyed_data.replace('{\"'+commonStorageName+ '\":{\"', '{\"'+filterString+'\":{\"');
                            $('#' + filterId + '_jsonData').val(stringifyed_data);
                            $('#' + filterId + '_jsonData').addClass('fairgatedirty');
                            $('#' + filterId + '_doNotSumbmit').removeClass('doNotSumbmit');
                            $('#' + filterId + '_is_broken').val('0');
                            //FgDirtyForm.rescan('formFilter');
                            $('#formFilter').trigger('checkform.areYouSure');
                            
                            firstTime = false;
                        } else {
                            /* The error call back will not allow you to sumbmit the form by aaading a class 'doNotSumbmit'
                             * This class is used to check on every sumbmit.
                             *   */
                            $('.alert').removeClass('display-hide');
                            $('#' + filterId + '_doNotSumbmit').addClass('doNotSumbmit');
                            var url = brokenUrl;
                            if (firstTime) {
                                FgXmlHttp.post(url, {'id': filterId, broken: 1}, 'replcediv', false);
                            } else {
                                firstTime = false;
                            }
                        }
                        $('[class*=mask]').each(function() {
                            $(this).val($(this).val());
                        });
                    },
                    savedCallback: function() {
                        saveCount++;
//                        
//                        setTimeout(function(){
//                            $('#editedFilter').val(parseInt($('#editedFilter').val()) - 1);
//                            if ($('#editedFilter').val() == 0) {
//                                $('#editedFilter').val('0');
//                                $('#editedFilter').removeClass("fairgatedirty");
//                                callSaveFunction();
//                            }
//                        },1000)
                    },
                    errorCallack: function() {
                        saveCount--;
                    } 
                });
                var saveBtn = "save_" + filterId;
                $(target_id).append('<input type="button" class="btn hidden-submit hidden" value="save filter" id="' + saveBtn + '">');
            
            })
        }
    });