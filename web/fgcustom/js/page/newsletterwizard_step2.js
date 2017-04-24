/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function fedmembersubscriptionClick() {
    $('body').off('click', ".fedmember_content");
    $('body').on('click', ".fedmember_content", function() {
        $(".fedmember_content").removeClass('fg_fed_hide').addClass('fg_fed_hide');
        $.ajax({url: formerfedmemberUrl,
            success: function(data) {
                $('#fedmember_table').find('.replaceDiv').html(data);
                $('body').off('click', ".fedmember_content");
            }
        })


    })
}
$(function() {
    //FgUtility.moreTab();
    $(".preview").on('click', function() {
        FgUtility.startPageLoading();
        $.ajax({url: tabshowUrl,
            type: 'POST',
            data: $('#form-tab2').serializeArray(),
            success: function(data) {
                FgUtility.stopPageLoading();
                $('#tabcontentarea').html(data);
                $('.hidden-settings').show();
                fedmembersubscriptionClick();


            }

        })
    })
    $("#preview").on('click', function() {
        if ($('#firstentry').val() > 0 && (($('#selected-email-fields').val() == '' || $('#selected-email-fields').val() == null) && ($(".fbautocomplete-main-div").find('.ids-fbautocomplete').length > 0))) {
            $("#mandatoryEmailError").show();

        } else if (($('#selected-email-fields').val() == '' || $('#selected-email-fields').val() == null) && ($(".fbautocomplete-main-div").find('.ids-fbautocomplete').length > 0)) {
            $("#mandatoryEmailError").show();
        } else {
            $('#firstentry').val('1');
            $("#mandatoryEmailError").hide();
            FgUtility.startPageLoading();
            $.ajax({url: activerecipientUrl,
                type: 'POST',
                data: $('#form-tab2').serializeArray(),
                success: function(data) {
                    FgUtility.stopPageLoading();
                    $('#tabcontentarea').html(data);
                    $('.hidden-settings').show();

                }

            })
        }
    })

    $('body').on('click', ".additionalsubscriber_content", function() {
        $.ajax({url: subscriberUrl,
            success: function(data) {
                $('#additionalsubscriber_table').find('.replaceDiv').html(data);

            }
        })


    })


    $('body').on('click', ".activerecipient_content", function() {
        $("#searchbox").hide();
        $("#searchbox2").hide();
        $("#searchbox1").show();
        $("#fgrowchange").show();
        $("#nl_recipient_table_length").show();
        $("div").remove("#subscriber-list_length");
        $("#federation-list_length").hide();
        $("#nl_recipient_remove_table_length").detach().prependTo("#fgremoverowchange");
        $("#nl_recipient_remove_table_length").show();
    })
    $('body').on('click', ".fg_fed_hide", function() {
        $("#searchbox").hide();
        $("#searchbox2").show();
        $("#searchbox1").hide();
        $("#fgrowchange").show();
        $("#nl_recipient_table_length").hide();
        $("div").remove("#subscriber-list_length");
        $("#federation-list_length").show();
        $("#nl_recipient_remove_table_length").hide();


    })

})
var FgNewsletterWizardGroupresults = function() {
    return {
        reciepientList: function(data, clubData) {
            var distinctData = _.groupBy(data, function(x) {
                return x.email + ",," + x.salutation;
            });
            var groupedData = _.map(distinctData, function(x) {
                var filteredData = _.uniq(x, false, function(p) {
                    return JSON.stringify(p)
                });
                var emailfield = _.pluck(filteredData, "emailfield").join('<br>');
                var name = _.pluck(filteredData, "name").join();
                var clubs = (typeof filteredData[0].contactClub !== "undefined" && filteredData[0].contactClub !== null) ? filteredData[0].contactClub.split(",") : [];
                var contactClubs = _.map(clubs, function(club){
                    var clubTitle = clubData[club.replace('#mainclub#', '')];
                    return ((club !== null) ? (clubTitle + ((clubs.length === 1) ? '' : ((club.indexOf('#mainclub#') !== -1) ? ' <i class="fa fa-star text-yellow"></i>' : ''))) : '-');
                });
                var subFederations = (typeof filteredData[0].contactSubFederation !== "undefined" && filteredData[0].contactSubFederation !== null) ? filteredData[0].contactSubFederation.split(",") : [];

                var contactSubFederations = _.map(subFederations, function(subFederation){
                    return ((subFederation !== null && subFederation !== "0") ? clubData[subFederation] : "-");
                });
                return {"email": filteredData[0].email, "salutation": filteredData[0].salutation, "emailfield": emailfield, "name": name, 'emailId': filteredData[0].emailId, "contactClub" : (contactClubs.join('<br>')), "contactSubFederation" : (contactSubFederations.join('<br>'))};
            });
            
            return groupedData;
        },
        reciepientListforAll: function(data, id) {
            var distinctData = _.groupBy(data, function(x) {
                return x.email + ",," + x.salutation;
            });
            var groupedData = _.map(distinctData, function(x) {
                var filteredData = _.uniq(x, false, function(p) {
                    return JSON.stringify(p)
                });
                var emailfield = _.pluck(filteredData, "emailField").join('<br>');
                var contactClub = [];
                _.filter(filteredData, function(data){ 
                   contactClub.push(((data.contactClub !== null) ? data.contactClub : "-")); 
                });
                contactClub = contactClub.join('<br>');
                var contactSubFederation = [];
                _.filter(filteredData, function(data){ 
                   contactSubFederation.push(((data.contactSubFederation !== null) ? data.contactSubFederation : "-")); 
                });
                contactSubFederation = contactSubFederation.join('<br>'); 
                var contact = [];
                _.filter(filteredData, function(data){
                    if (hasContactModuleRights && data.contactType == 'contact') {
                        var contactUrl = contactOverviewPath.replace('|contactId|', data.contactId);
                        contact.push('<a href="' + contactUrl + '" target="_blank" >' + data.contact + '</a>'); 
                    } else {
                        contact.push(data.contact); 
                    }
                });
                contact = contact.join('<br>');
                var isEmailChanged = (filteredData[0].isEmailChanged) ? filteredData[0].isEmailChanged : 0;
                return {"contactId": filteredData[0].id, "isBounce": filteredData[0].isBounce, "email": filteredData[0].email, "emailfield": emailfield, "isEmailChanged": isEmailChanged, "contacts": contact, "salutation": filteredData[0].salutation, "opened": filteredData[0].opened, "logId": filteredData[0].logId, "contactClub" : contactClub, "contactSubFederation" : contactSubFederation};
            });
            return groupedData;
        },
        partitionedData: function(reciepientData, excludeData) {
            var keyedData = _.map(excludeData, function(x) {
                return x.emailId + ",," + x.salutation;
            });

            return _.partition(reciepientData, function(x) {
                return _.contains(keyedData, x.emailId + ",," + x.salutation)
            });
        }
    };
}();
function getRemovedRecipientString(removedContactData) {
    var datastring = '';
    $.each(removedContactData, function(index, expertData) {
        datastring += ',' + expertData.emailId + '#' + expertData.salutation;
    })
    datastring = (datastring != '') ? datastring.substr(1) : datastring;

    return  datastring;
}

