
// The jQuery class is used to handle all the user rights 
// related functionalities such as add new admin, delete admin

FgUserRights = {
    // Function to init all the basic functionalities when page load
    init: function (options) {

        // Parameters as an array for auto complete functionality for selecting contacts in all admins such as contact, sponsor etc
        typeHeadOtherAdminsOptions = {newExcludeAdmins: options.newExcludeAdmins, includeAdminsId: '#include-contacts-admins-',
            excludeAdminId: "#exclude-contacts-admins", dataKey: 'new_all.administrator.admin.contact.id.', name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide ', uniqueId: '#new-include-contacts-admins-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        // Parameters as an array for auto complete functionality for selecting contacts in readonly admins
        typeHeadReadonlyAdminsOptions = {
            newExcludeAdmins: options.newExcludeReadonlyAdmins, includeAdminsId: '#include-contacts-readonly-admins-',
            excludeAdminId: "#exclude-contacts-readonly-admins", dataKey: 'new_all.readonly.admin.contact.id.', name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-contacts-readonly-admins-', clubAdminFlag: 0, contactNameUrl: options.contactNameUrl
        };
        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        typeHeadClubAdminOptions = {newExcludeAdmins: options.newExclude, includeAdminsId: '#include-contacts-',
            excludeAdminId: "#exclude-contacts", dataKey: 'new.clubAdmin.contact.', name: 'new_group_',
            class: ' fg-dev-new-elmt-contact hide', uniqueId: '#new-include-contacts-', clubAdminFlag: 1,
            contactNameUrl: options.contactNameUrl
        };

        // Parameters as an array for auto complete functionality for selecting contacts in fed admins
        typeHeadFedAdminOptions = {newExcludeAdmins: options.newExcludeFedAdmin, includeAdminsId: '#include-contacts-fed-',
            excludeAdminId: "#exclude-contacts-fed", dataKey: 'new.fedAdmin.contact.', name: 'new_group_',
            class: ' fg-dev-new-elmt-contact hide', uniqueId: '#new-include-contacts-fed-', clubAdminFlag: 1,
            contactNameUrl: options.contactNameFedUrl
        };

        FgUserRights.addNewClubAdmin(typeHeadClubAdminOptions); // Club administrator
        FgUserRights.addNewFedAdmin(typeHeadFedAdminOptions); // Fed administrator
        FgUserRights.addNewOtherAdmin(options, typeHeadOtherAdminsOptions); //Other admins includes contact,sponsor, document and communication admins
        FgUserRights.addNewReadonlyAdmin(options, typeHeadReadonlyAdminsOptions); //New readonly admin
        FgUserRights.expandCollaspe(); // For expand or collaspe functionality
        FgUserRights.removeAdminRow(options); // Delete user rights row function
        FgUserRights.resetUserRightsCallback(); // Reset changes function
        FgUserRights.saveUserRights(options, 'backend'); // Save all user rights in cluding validations
    },
    // Function to add new club admin from User rights setting page.
    addNewClubAdmin: function (typeHeadClubAdminOptions) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-club-admin', function () {

            var template = $('#fg-dev-add-new-contact').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var new_contact_html = _.template(template, {randomNumber: randomNumber}); // Rendering template

            $('.fg-dev-user-rights-elements').append(new_contact_html); // Displaying the rendered template under the club administration section
            var groupId = 2;
            FgUserRights.handleTypeaheadAdmins('#include-contacts-' + randomNumber, randomNumber, groupId, typeHeadClubAdminOptions, typeFlag = 'clubAdmin'); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields(new_contact_html);
        });
    },
    // Function to add new fed admin from User rights setting page.
    addNewFedAdmin: function (typeHeadFedAdminOptions) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-fed-admin', function () {

            var template = $('#fg-dev-add-new-contact-fed').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var new_contact_html = _.template(template, {randomNumber: randomNumber}); // Rendering template

            $('.fg-dev-user-rights-fed-elements').append(new_contact_html); // Displaying the rendered template under the fed administration section
            var groupId = 17;
            FgUserRights.handleTypeaheadAdmins('#include-contacts-fed-' + randomNumber, randomNumber, groupId, typeHeadFedAdminOptions, typeFlag = 'fedAdmin'); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields(new_contact_html);
        });
    },
    // Function to add new contact, sponosr, document or communication admins from User rights setting page.
    addNewOtherAdmin: function (options, typeHeadOtherAdminsOptions) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-other-admins', function () {
            var template = $('#fg-dev-add-new-contact-admins').html(); // Taking underscore template to display new other admins section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var result_data = _.template(template, {randomNumber: randomNumber, allGroups: options.allGroups, bookedModuleDetails: options.bookedModuleDetails, transAdministration: options.transAdministration}); // Rendering template

            $('#fg-dev-user-rights-div').find('.fg-dev-user-rights-admin-elements').append(result_data); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('#fg-dev-user-rights-div').show();
            FgUserRights.handleTypeaheadAdmins('#include-contacts-admins-' + randomNumber, randomNumber, options.allGroups, typeHeadOtherAdminsOptions, typeFlag = 'administrator'); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields(result_data);
        });
    },
    // Function to add new readonly admin from User rights setting page.
    addNewReadonlyAdmin: function (options, typeHeadReadonlyAdminsOptions) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-readonly-admins', function () {
            var template = $('#fg-dev-add-new-contact-readonly-admins').html(); // Taking underscore template to display new readonly admins section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1);  // Rabdom number to generate identical input values
            var result_data = _.template(template, {randomNumber: randomNumber, allGroups: options.allGroups, bookedModuleDetails: options.bookedModuleDetails, transAdministration: options.transAdministration}); // Rendering template

            $('#fg-dev-user-rights-div').find('.fg-dev-user-rights-readonly-admin-elements').append(result_data); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('#fg-dev-user-rights-div').show();
            FgUserRights.handleTypeaheadAdmins('#include-contacts-readonly-admins-' + randomNumber, randomNumber, options.allGroups, typeHeadReadonlyAdminsOptions, typeFlag = 'readonly'); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields(result_data);
        });
    },
    //backend  settings page - groups tab
    initFrontend: function (options) {
        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        typeHeadTeamAdminOptions = {newExcludeAdmins: options.newExclude, includeAdminsId: '#include-team-admin-',
            excludeAdminId: "#exclude-team-admin", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-team-admin-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        typeHeadWGAdminOptions = {newExcludeAdmins: options.newWGExclude, includeAdminsId: '#include-wg-admin-',
            excludeAdminId: "#exclude-wg-admin", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-wg-admin-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        typeHeadTeamSectionAdminOptions = {newExcludeAdmins: options.newExcludeTeamSectionAdmins, includeAdminsId: '#include-team-admin-section-',
            excludeAdminId: "#exclude-team-admin-section", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-team-admin-section-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        typeHeadWGSectionAdminOptions = {newExcludeAdmins: options.newExcludeWGSectionAdmins, includeAdminsId: '#include-wg-admin-section-',
            excludeAdminId: "#exclude-wg-admin-section", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-wg-admin-section-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };

        FgUserRights.addNewTeamSectionAdmin(typeHeadTeamSectionAdminOptions, typeHeadWGSectionAdminOptions);
        FgUserRights.addNewTeam(options);
        FgUserRights.addNewRoleAdmin(typeHeadTeamAdminOptions, typeHeadWGAdminOptions, options); // Team administrator
        FgUserRights.resetUserRightsCallback(); // Reset changes function
        FgUserRights.removeInternalRow(options);
        FgUserRights.removeInternalTeamRow();
        FgUserRights.expandCollaspe();
        FgUserRights.saveUserRights(options, 'backend');

    },
    // Function to add new page admin from User rights setting page.
    addNewPageAdmin: function (typeHeadWGAdminOptions, options) {
         $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-team-admin,.fg-dev-add-wg-admin', function () {
           
            var template = $('#fg-dev-add-new-team_admin').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var groupId = 21;
            typeHeadWGAdminOptions.dataKey = 'cms.pgAdmin.#.contact.';
            var new_contact_html = _.template(template, {randomNumber: randomNumber, pageList: options.pageList, roleType: 'pgAdmin'}); // Rendering template
            $(this).parent().siblings('.fg-dev-user-rights-elements-team').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');
            FgUserRights.handleTypeaheadAdmins('#include-wg-admin-' + randomNumber, randomNumber, groupId, typeHeadWGAdminOptions, typeFlag = 'roleAdmin'); // Function to implement auto-complete select of contacts
            FgDirtyFields.addFields(new_contact_html);
        });
    },
    // Function to add new team admin from User rights setting page.
    addNewRoleAdmin: function (typeHeadTeamAdminOptions, typeHeadWGAdminOptions, options) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-team-admin,.fg-dev-add-wg-admin', function () {
            var template = $('#fg-dev-add-new-team_admin').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values

            if ($(this).attr('class') == 'fg-dev-add-wg-admin') {
                var groupId = 9;
                typeHeadWGAdminOptions.dataKey = 'teams.wgAdmin.#.contact.';
                var new_contact_html = _.template(template, {randomNumber: randomNumber, teamList: options.workgroupList, roleType: 'wgAdmin'}); // Rendering template
            } else {
                var groupId = 9;
                typeHeadTeamAdminOptions.dataKey = 'teams.teamAdmin.#.contact.';
                var new_contact_html = _.template(template, {randomNumber: randomNumber, teamList: options.teamList, roleType: 'teamAdmin'}); // Rendering template
            }
            $(this).parent().siblings('.fg-dev-user-rights-elements').append(new_contact_html); // Displaying the rendered template under the club administration section

            FgFormTools.handleUniform();

            $('select.selectpicker').selectpicker('render');
            if ($(this).attr('class') == 'fg-dev-add-wg-admin') {
                FgUserRights.handleTypeaheadAdmins('#include-wg-admin-' + randomNumber, randomNumber, groupId, typeHeadWGAdminOptions, typeFlag = 'roleAdmin'); // Function to implement auto-complete select of contacts
            } else {
                FgUserRights.handleTypeaheadAdmins('#include-team-admin-' + randomNumber, randomNumber, groupId, typeHeadTeamAdminOptions, typeFlag = 'roleAdmin'); // Function to implement auto-complete select of contacts
            }
            FgDirtyFields.addFields(new_contact_html);
        });
    },
    // Function to add new team section admin from User rights setting page.
    addNewTeamSectionAdmin: function (typeHeadTeamSectionAdminOptions, typeHeadWGSectionAdminOptions) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-team-section,.fg-dev-add-wg-section', function () {
            var template = $('#fg-dev-add-new-team_admin_section').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            if ($(this).attr('class') == 'fg-dev-add-team-section') {
                var new_contact_html = _.template(template, {randomNumber: randomNumber, roleType: 'teamSection'}); // Rendering template
            } else {
                var new_contact_html = _.template(template, {randomNumber: randomNumber, roleType: 'wgSection'}); // Rendering template
            }
            $(this).parent().siblings('.fg-dev-user-rights-elements-section').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');
            var groupId = 'dummy';
            if ($(this).attr('class') == 'fg-dev-add-team-section') {
                typeHeadTeamSectionAdminOptions.dataKey = 'teams.teamSection.new.#.contact.';
                FgUserRights.handleTypeaheadAdmins('#include-team-admin-section-' + randomNumber, randomNumber, groupId, typeHeadTeamSectionAdminOptions, typeFlag = 'teamSectionAdmin'); // Function to implement auto-complete select of contacts while selecting new club admin.
            } else {
                typeHeadWGSectionAdminOptions.dataKey = 'teams.wgSection.new.#.contact.';
                FgUserRights.handleTypeaheadAdmins('#include-wg-admin-section-' + randomNumber, randomNumber, groupId, typeHeadWGSectionAdminOptions, typeFlag = 'wgSectionAdmin'); // Function to implement auto-complete select of contacts while selecting new club admin.

            }
            FgDirtyFields.addFields(new_contact_html);
        });
    },
    // Function to add new team from User rights setting page.
    addNewTeam: function (options) {
        $('#fg-dev-user-rights-div').on('click', '.fg-internal-add,#fg-internal-add-existing,#fg-internal-add-wgsection,#fg-internal-add-existing-wgsection', function () {
            var contactId = $(this).parent().parent().siblings('input[type="text"]').val();
            var rand1 = $(this).parent().parent().parent().find('.random').attr('data-id');
            var template = $('#fg-dev-add-new-team').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values

            //inorder to avoid duplicate team selection for same contact
            var excludeRoles = [];
            $(this).siblings().children().find('.fg-internal-blk-one select option:selected').map(function (i, el) {
                excludeRoles[i] = $(el).val();
            });
            $(this).siblings().children().find('.fg-internal-blk-one select option').map(function (i, el) {
                if (_.contains(excludeRoles, $(el).val())) {
                    $(el).prop('disabled');
                }

            });

            //end

            if ($(this).attr('id') == "fg-internal-add-existing") {
                if ((_.size(excludeRoles) + 1) == _.size(options.teamList)) {
                    $(this).css("display", "none");
                }
                var new_contact_html = _.template(template, {randomNumber: randomNumber, admins: options.admins, teamList: options.teamList, rand1: rand1, contact: contactId, roleType: 'teamSection', 'excludeRoles': excludeRoles}); // Rendering template
            } else if ($(this).attr('id') == 'fg-internal-add-existing-wgsection') {
                if ((_.size(excludeRoles) + 1) == _.size(options.workgroupList)) {
                    $(this).css("display", "none");
                }
                var new_contact_html = _.template(template, {randomNumber: randomNumber, admins: options.admins, teamList: options.workgroupList, rand1: rand1, contact: contactId, roleType: 'wgSection', 'excludeRoles': excludeRoles}); // Rendering template
            } else if ($(this).attr('id') == 'fg-internal-add-wgsection') {
                if ((_.size(excludeRoles) + 1) == _.size(options.workgroupList)) {
                    $(this).css("display", "none");
                }
                var new_contact_html = _.template(template, {randomNumber: randomNumber, admins: options.admins, teamList: options.workgroupList, rand1: rand1, roleType: 'wgSection', contact: contactId, 'excludeRoles': excludeRoles}); // Rendering template
            } else {
                if ((_.size(excludeRoles) + 1) == _.size(options.teamList)) {
                    $(this).css("display", "none");
                }
                var new_contact_html = _.template(template, {randomNumber: randomNumber, admins: options.admins, teamList: options.teamList, rand1: rand1, roleType: 'teamSection', contact: contactId, 'excludeRoles': excludeRoles}); // Rendering template
            }
            $(this).siblings('.fg-internal-team-block').append(new_contact_html);
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');
            FgDirtyFields.addFields(new_contact_html);
        });
    },
    // backend  settings page  - backend tab sections
    addNewTeamModuleAdmin: function (teamList, workgroupList, allTeamGroups, contactId) {
        $('#fg-dev-group-details-div').on('click', '.fg-dev-add-team-module-admin,.fg-dev-add-wg-module-admin', function () {
            var template = $('#fg-dev-add-new-team-module-admin').html(); // Taking underscore template to display new other admins section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var excludeRoles = [];
            $(this).parents('.fg-internal-add').siblings().children().find('.fg-internal-blk-one select option:selected').map(function (i, el) {
                excludeRoles[i] = $(el).val();
            });
            $(this).parents('.fg-internal-add').siblings().children().find('.fg-internal-blk-one select option').map(function (i, el) {
                if (_.contains(excludeRoles, $(el).val())) {
                    $(el).prop('disabled');
                }

            });
            if ($(this).attr('class') == "fg-dev-add-wg-module-admin") {
                if ((_.size(excludeRoles) + 1) == _.size(workgroupList)) {
                    $(this).css("display", "none");
                }
                var result_data = _.template(template, {randomNumber: randomNumber, teamsArray: workgroupList, allTeamGroups: allTeamGroups, contactId: contactId, type: 'wgSection', 'excludeRoles': excludeRoles}); // Rendering template
            } else {
                if ((_.size(excludeRoles) + 1) == _.size(teamList)) {
                    $(this).css("display", "none");
                }
                var result_data = _.template(template, {randomNumber: randomNumber, teamsArray: teamList, allTeamGroups: allTeamGroups, contactId: contactId, type: 'teamSection', 'excludeRoles': excludeRoles}); // Rendering template
            }
            $(this).parent().siblings('.teamModuleRow').append(result_data); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');
            FgDirtyFields.addFields(result_data);
        });
    },
    //************ INTERNAL -  Userrights************************

    // Function to add new user as grp admin in internal team/wg userrights page
    addNewGrpAdminToRole: function (typeHeadRoleAdminOptions, options) {

        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-grp-admin-internal', function () {
            var template = $('#fg-dev-new-grpadmin').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var groupId = 9;
            if (options.type == 'T') {
                var roleType = "teamAdminInt";
                typeHeadRoleAdminOptions.dataKey = 'teams.teamAdminInt.#.contact.';
            } else {
                var roleType = "wgAdminInt";
                typeHeadRoleAdminOptions.dataKey = 'teams.wgAdminInt.#.contact.';
            }
            var new_contact_html = _.template(template, {randomNumber: randomNumber, roleType: roleType, roleId: options.roleId}); // Rendering template
            $(this).parent().siblings('.fg-dev-user-rights-elements').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');
            FgUserRights.handleTypeaheadAdmins('#include-role-admin-' + randomNumber, randomNumber, groupId, typeHeadRoleAdminOptions, typeFlag = 'roleAdmin', options.roleId); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields(new_contact_html);

        });
    },
    // Function to add new role section admin from team/wg page
    addNewSectionAdminToRole: function (typeHeadRoleAdminOptions, options) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-grp-section-internal', function () {
            var template = $('#fg-dev-add-new-role-section').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            if (options.type == 'T') {
                var roleType = 'teamSection';
                typeHeadRoleAdminOptions.dataKey = 'teams.teamSection.new.#.contact.';
            } else {
                var roleType = 'wgSection';
                typeHeadRoleAdminOptions.dataKey = 'teams.wgSection.new.#.contact.';
            }
            var new_contact_html = _.template(template, {randomNumber: randomNumber, roleType: roleType, 'admins': options.admins, roleId: options.roleId}); // Rendering template
            $(this).parent().siblings('.fg-dev-user-rights-elements-section').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');

            var groupId = 'dummy';
            FgUserRights.handleTypeaheadAdmins('#include-role-admin-section-' + randomNumber, randomNumber, groupId, typeHeadRoleAdminOptions, typeFlag = 'wgSectionAdmin', options.roleId); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields('#include-role-admin-section-' + randomNumber);
        });
    },
    initCmsUserright: function (options) {
       
        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        typeHeadCmsAdminOptions = {newExcludeAdmins: options.newExclude, includeAdminsId: '#include-contacts-',
            excludeAdminId: "#exclude-contacts", dataKey: 'new.cmsAdmin.contact.', name: 'new_group_',
            class: ' fg-dev-new-elmt-contact hide', uniqueId: '#new-include-contacts-', clubAdminFlag: 1,
            contactNameUrl: options.contactNameUrl
        };
        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        typePageAdminOptions = {newExcludeAdmins: options.newExcludePageAdmins, includeAdminsId: '#include-wg-admin-',
            excludeAdminId: "#exclude-wg-admin", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-wg-admin-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
         
        FgUserRights.addNewCmsAdmin(typeHeadCmsAdminOptions); // Club administrator
        FgUserRights.addNewPageAdmin(typePageAdminOptions,options);
        FgUserRights.expandCollaspe(); // Reset changes function
        FgUserRights.removeCmsAdminRow(options);
        FgUserRights.removePageAdminRow(options);
        FgUserRights.resetUserRightsCallback();
        FgUserRights.saveUserRights(options, options.from);

    },
    // Function to add new club admin from User rights setting page.
    addNewCmsAdmin: function (typeHeadClubAdminOptions) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-cms-admin', function () {
            var groupId = 18;
            var template = $('#fg-dev-add-new-contact').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var new_contact_html = _.template(template, {randomNumber: randomNumber}); // Rendering template

            $('.fg-dev-user-rights-elements').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgUserRights.handleTypeaheadAdmins('#include-contacts-' + randomNumber, randomNumber, groupId, typeHeadClubAdminOptions, typeFlag = 'cmsAdmin'); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields(new_contact_html);
        });
    },
    initInternalRole: function (options) {
        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        var typeHeadRoleAdminOptions = {newExcludeAdmins: options.newExclude, includeAdminsId: '#include-role-admin-',
            excludeAdminId: "#exclude-role-admin", name: 'new_admin_group_',
            class: ' fairgatedirty fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-role-admin-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };

        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        var typeHeadRoleSectionAdminOptions = {newExcludeAdmins: options.newExcludeRoleSectionAdmins, includeAdminsId: '#include-role-admin-section-',
            excludeAdminId: "#exclude-role-admin-section", name: 'new_admin_group_',
            class: 'fairgatedirty fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-role-admin-section-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        FgUserRights.addNewGrpAdminToRole(typeHeadRoleAdminOptions, options);
        FgUserRights.addNewSectionAdminToRole(typeHeadRoleSectionAdminOptions, options);
        FgUserRights.resetUserRightsCallback(); // Reset changes function
        FgUserRights.removeInternalRow(options);
        FgUserRights.expandCollaspe();
        FgUserRights.saveUserRights(options, 'internal');

    },
    //********************END INTERNAL area Userrights *************


    //*******************************TEAM CATEGORY SETTINGS *****

    addNewCatTeamGrpAdmin: function (typeHeadRoleAdminOptions, options) {
        $('#fg-dev-user-rights-div-' + options.roleId).on('click', '.fg-dev-add-grp-admin-internal', function () {

            var template = $('#fg-dev-new-grpadmin').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var groupId = 9;
            if (options.type == 'T') {
                var roleType = "teamAdminInt";
                typeHeadRoleAdminOptions.dataKey = 'teams.teamAdminInt.#.contact.';
            } else {
                var roleType = "wgAdminInt";
                typeHeadRoleAdminOptions.dataKey = 'teams.wgAdminInt.#.contact.';
            }

            var new_contact_html = _.template(template, {randomNumber: randomNumber, roleType: roleType, roleId: options.roleId}); // Rendering template
            $(this).parent().siblings('.fg-dev-user-rights-elements').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');
            FgUserRights.handleTypeaheadAdmins('#include-role-admin-' + randomNumber, randomNumber, groupId, typeHeadRoleAdminOptions, typeFlag = 'roleAdmin', options.roleId, '#fg-dev-user-rights-div-' + options.roleId); // Function to implement auto-complete 
            FgUtility.resetSortOrder($('#fg-dev-user-rights-div-' + options.roleId));
            FgDirtyFields.updateFormState();
        });
    },
    // Function to add new role section admin from team/wg page
    addNewCatTeamSectionAdminToRole: function (typeHeadRoleAdminOptions, options) {
        $('#fg-dev-user-rights-div-' + options.roleId).on('click', '.fg-dev-add-grp-section-internal', function () {
            var template = $('#fg-dev-add-new-role-section').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            if (options.type == 'T') {
                var roleType = 'teamSection';
                typeHeadRoleAdminOptions.dataKey = 'teams.teamSection.new.#.contact.';
            } else {
                var roleType = 'wgSection';
                typeHeadRoleAdminOptions.dataKey = 'teams.wgSection.new.#.contact.';
            }
            var new_contact_html = _.template(template, {randomNumber: randomNumber, roleType: roleType, 'admins': options.admins, roleId: options.roleId}); // Rendering template
            $(this).parent().siblings('.fg-dev-user-rights-elements-section').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');

            var groupId = 'dummy';
            FgUserRights.handleTypeaheadAdmins('#include-role-admin-section-' + randomNumber, randomNumber, groupId, typeHeadRoleAdminOptions, typeFlag = 'wgSectionAdmin', options.roleId, '#fg-dev-user-rights-div-' + options.roleId); // Function to implement auto-complete 
            FgUtility.resetSortOrder($('#fg-dev-user-rights-div-' + options.roleId));
            FgDirtyFields.updateFormState();
            //FgDirtyFields.addFields(new_contact_html);
        });
    },
    initRoleCatUserrights: function (options) {
        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        var typeHeadRoleAdminOptions = {newExcludeAdmins: options.newExclude, includeAdminsId: '#include-role-admin-',
            excludeAdminId: "#exclude-role-admin", name: 'new_admin_group_',
            class: '  fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-role-admin-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };

        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        var typeHeadRoleSectionAdminOptions = {newExcludeAdmins: options.newExcludeRoleSectionAdmins, includeAdminsId: '#include-role-admin-section-',
            excludeAdminId: "#exclude-role-admin-section", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-role-admin-section-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        FgUserRights.addNewCatTeamGrpAdmin(typeHeadRoleAdminOptions, options);
        FgUserRights.addNewCatTeamSectionAdminToRole(typeHeadRoleSectionAdminOptions, options);
        FgUserRights.removeInternalTeamCatRow(options);
    },
    //*********************************END TEAM CATEGORY SETTINGS *********************


    //**************CALENDAR SETTINGS ************
    initCalendarAdmin: function (options) {
        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        var typeHeadRoleAdminOptions = {newExcludeAdmins: options.newExclude, includeAdminsId: '#include-role-admin-',
            excludeAdminId: "#exclude-role-admin", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-role-admin-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        FgUserRights.addNewCalendarAdmin(typeHeadRoleAdminOptions, options);
        FgUserRights.resetUserRightsCallback(); // Reset changes function
        FgUserRights.removeInternalRow(options);
        FgUserRights.saveUserRights(options, 'internal');

    },
    addNewCalendarAdmin: function (typeHeadRoleAdminOptions, options) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-calendar-admin', function () {
            var template = $('#fg-dev-add-new-calenderadmin').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var groupId = 14;
            typeHeadRoleAdminOptions.dataKey = 'calendar.admin.#.contact.';
            var new_contact_html = _.template(template, {randomNumber: randomNumber}); // Rendering template
            $(this).parent().siblings('.fg-dev-user-rights-elements').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');
            FgUserRights.handleTypeaheadAdmins('#include-role-admin-' + randomNumber, randomNumber, groupId, typeHeadRoleAdminOptions, typeFlag = 'roleAdmin', options.roleId); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields(new_contact_html);

        });
    },
    
    //**************ARTICLE USERRIGHT SETTINGS ************
    initArticleAdmin: function(options) {
        // Parameters as an array for auto complete functionality for selecting contacts in club admins
        var typeHeadRoleAdminOptions = {newExcludeAdmins: options.newExclude, includeAdminsId: '#include-role-admin-',
            excludeAdminId: "#exclude-role-admin", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-role-admin-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        var typeHeadTeamAdminOptions = {newExcludeAdmins: options.newTeamExclude, includeAdminsId: '#include-team-admin-',
            excludeAdminId: "#exclude-team-admin", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-team-admin-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        var typeHeadWGAdminOptions = {newExcludeAdmins: options.newWGExclude, includeAdminsId: '#include-wg-admin-',
            excludeAdminId: "#exclude-wg-admin", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-wg-admin-', clubAdminFlag: 0,
            contactNameUrl: options.contactNameUrl
        };
        FgUserRights.addNewArticleAdmin(typeHeadRoleAdminOptions, options);
        FgUserRights.resetUserRightsCallback(); // Reset changes function
        FgUserRights.removeInternalRow(options);
        FgUserRights.saveUserRights(options, 'internal');
        FgUserRights.addNewArticleRoleAdmin(typeHeadTeamAdminOptions, typeHeadWGAdminOptions, options); 
    },
    addNewArticleAdmin: function(typeHeadRoleAdminOptions, options) {
       $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-article-admin', function() {
            var template = $('#fg-dev-add-new-articleadmin').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            var groupId = 14;
            typeHeadRoleAdminOptions.dataKey = 'article.admin.#.contact.';
            var new_contact_html = _.template(template, {randomNumber: randomNumber}); // Rendering template
            $(this).parent().siblings('.fg-dev-user-rights-elements').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');
            FgUserRights.handleTypeaheadAdmins('#include-role-admin-' + randomNumber, randomNumber, groupId, typeHeadRoleAdminOptions, typeFlag = 'roleAdmin', options.roleId); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields(new_contact_html);

        });
    },
    
    // Function to add new team admin from article User rights setting page.
    addNewArticleRoleAdmin: function(typeHeadTeamAdminOptions, typeHeadWGAdminOptions, options) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-team-admin,.fg-dev-add-wg-admin', function() {
            var template = $('#fg-dev-add-new-team_admin').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values

            if ($(this).attr('class') == 'fg-dev-add-wg-admin') {
                var groupId = 19;
                typeHeadWGAdminOptions.dataKey = 'teams.wgAdmin.#.contact.';
                var new_contact_html = _.template(template, {randomNumber: randomNumber, teamList: options.workgroupList, roleType: 'wgAdmin'}); // Rendering template
                $(this).parent().siblings('.fg-dev-user-rights-elements-workgroup').append(new_contact_html); // Displaying the rendered template under the workgroup administration section            
            } else {
                var groupId = 19;
                typeHeadTeamAdminOptions.dataKey = 'teams.teamAdmin.#.contact.';
                var new_contact_html = _.template(template, {randomNumber: randomNumber, teamList: options.teamList, roleType: 'teamAdmin'}); // Rendering template
                $(this).parent().siblings('.fg-dev-user-rights-elements-team').append(new_contact_html); // Displaying the rendered template under the team administration section            
            }
            
            FgFormTools.handleUniform();
           
            $('select.selectpicker').selectpicker('render');
            if ($(this).attr('class') == 'fg-dev-add-wg-admin') {
                FgUserRights.handleTypeaheadAdmins('#include-wg-admin-' + randomNumber, randomNumber, groupId, typeHeadWGAdminOptions, typeFlag = 'roleAdmin'); // Function to implement auto-complete select of contacts
            } else {
                FgUserRights.handleTypeaheadAdmins('#include-team-admin-' + randomNumber, randomNumber, groupId, typeHeadTeamAdminOptions, typeFlag = 'roleAdmin'); // Function to implement auto-complete select of contacts
            }
             FgDirtyFields.addFields(new_contact_html);
        });
    },
    // Function to add new team section admin from User rights setting page.
    addNewTeamSectionAdmin: function(typeHeadTeamSectionAdminOptions, typeHeadWGSectionAdminOptions) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-team-section,.fg-dev-add-wg-section', function() {
            var template = $('#fg-dev-add-new-team_admin_section').html(); // Taking underscore template to display new club admin section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1); // Rabdom number to generate identical input values
            if ($(this).attr('class') == 'fg-dev-add-team-section') {
                var new_contact_html = _.template(template, {randomNumber: randomNumber, roleType: 'teamSection'}); // Rendering template
            } else {
                var new_contact_html = _.template(template, {randomNumber: randomNumber, roleType: 'wgSection'}); // Rendering template
            }
            $(this).parent().siblings('.fg-dev-user-rights-elements-section').append(new_contact_html); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('select.selectpicker').selectpicker('render');
            var groupId = 'dummy';
            if ($(this).attr('class') == 'fg-dev-add-team-section') {
                typeHeadTeamSectionAdminOptions.dataKey = 'teams.teamSection.new.#.contact.';
                FgUserRights.handleTypeaheadAdmins('#include-team-admin-section-' + randomNumber, randomNumber, groupId, typeHeadTeamSectionAdminOptions, typeFlag = 'teamSectionAdmin'); // Function to implement auto-complete select of contacts while selecting new club admin.
            } else {
                typeHeadWGSectionAdminOptions.dataKey = 'teams.wgSection.new.#.contact.';
                FgUserRights.handleTypeaheadAdmins('#include-wg-admin-section-' + randomNumber, randomNumber, groupId, typeHeadWGSectionAdminOptions, typeFlag = 'wgSectionAdmin'); // Function to implement auto-complete select of contacts while selecting new club admin.

            }
            FgDirtyFields.addFields(new_contact_html);
        });
    },

    //backend settings internal area tab
    initAdminstrationAdmin: function (options) {
        // Parameters as an array for auto complete functionality for selecting contacts in readonly admins
        typeHeadInternalAdminsOptions = {
            newExcludeAdmins: options.newExclude, includeAdminsId: '#include-contacts-admins-',
            excludeAdminId: "#exclude-contacts-admins", name: 'new_admin_group_',
            class: ' fg-dev-new-elmt-contact-admins hide', uniqueId: '#new-include-contacts-admins-', clubAdminFlag: 0, contactNameUrl: options.contactNameUrl
        };
        FgUserRights.addNewAdministrationAdmin(options, typeHeadInternalAdminsOptions);
        FgUserRights.removeAdministrationRow(options);
        FgUserRights.expandCollaspe();
        FgUserRights.saveUserRights(options, 'backend');
        FgUserRights.resetUserRightsCallback(); // Reset changes function
    },
    addNewAdministrationAdmin: function (options, typeHeadCalendarAdminsOptions) {
        $('#fg-dev-user-rights-div').on('click', '.fg-dev-add-calendar-admin', function () {
            var template = $('#fg-dev-add-new-contact-calendar-admins').html(); // Taking underscore template to display new readonly admins section where to select user
            var randomNumber = Math.floor((Math.random() * 1000000) + 1);  // Rabdom number to generate identical input values
            var result_data = _.template(template, {randomNumber: randomNumber, internalAdminList: options.internalAdminList}); // Rendering template
            typeHeadCalendarAdminsOptions.dataKey = 'admin.#.contact.';

            $('#fg-dev-user-rights-div').find('.fg-dev-user-rights-elements').append(result_data); // Displaying the rendered template under the club administration section
            FgFormTools.handleUniform();
            $('#fg-dev-user-rights-div').show();
            FgUserRights.handleTypeaheadAdmins('#include-contacts-admins-' + randomNumber, randomNumber, 14, typeHeadCalendarAdminsOptions, typeFlag = 'calendar'); // Function to implement auto-complete select of contacts while selecting new club admin.
            FgDirtyFields.addFields(result_data);
        });
    },
    expandCollaspe: function () { // Function for expand or collaspe functionality 
        $('body').off('click', '.fg-adminstration-area');
        $('body').on('click', '.fg-adminstration-area', function () {
            $(this).toggleClass('clicked');
            $(this).parent().parent().siblings(".fg-adminstration-area-open").slideToggle(350);
        });
    },
    removeAdministrationRow: function (options) {
        $('#fg-dev-user-rights-div').on('click', '.new_contact_right_admins_rmv', function () {
            FgDirtyFields.removeFields($(this).parent());
            deleteRow($(this), '.fg-dev-new-elmt-contact-admins', options.newExclude); // Calling common delete function to remove new row
        });
    },
    removeAdminRow: function (options) { // Used to remove newly created user rights row
        $('#fg-dev-user-rights-div').on('click', '.new_contact_right_rmv', function (event) {
            FgDirtyFields.removeFields($(this).parent());
            deleteRow($(this), '.fg-dev-new-elmt-contact', options.newExclude); // Calling common delete function to remove new row
        });
        $('#fg-dev-user-rights-div').on('click', '.new_contact_right_admins_rmv', function (event) {
            FgDirtyFields.removeFields($(this).parent());
            deleteRow($(this), '.fg-dev-new-elmt-contact-admins', options.newExcludeAdmins); // Calling common delete function to remove new row
        });
        $('#fg-dev-user-rights-div').on('click', '.new_contact_right_readonly_admins_rmv', function (event) {
            FgDirtyFields.removeFields($(this).parent());
            deleteRow($(this), '.fg-dev-new-elmt-contact-readonly-admins', options.newExcludeReadonlyAdmins); // Calling common delete function to remove new row
        });
        $('#fg-dev-user-rights-div').on('click', '.new_contact_right_fedadmin_admins_rmv', function (event) {
            FgDirtyFields.removeFields($(this).parent());
            deleteRow($(this), '.fg-dev-new-elmt-contact-readonly-admins', options.newExcludeFedAdmin); // Calling common delete function to remove new row
        });
    },
    removeCmsAdminRow: function (options) { // Used to remove newly created user rights row

        $('#fg-dev-user-rights-div').on('click', '.new_contact_right_rmv', function (event) {
            FgDirtyFields.removeFields($(this).parent());
            deleteRow($(this), '.fg-dev-new-elmt-contact', options.newExclude); // Calling common delete function to remove new row
        });

    },
    removePageAdminRow: function (options) { // Used to remove newly created user rights row

         $('#fg-dev-user-rights-div').on('click', '.new_contact_team_right_rmv', function (event) {
            deleteRow($(this), '.fg-dev-new-elmt-contact-admins', options.newExcludePageAdmins);
        });

    },
    removeTeamModulesRow: function (options) { // Used to remove newly created user rights row
        $('#fg-dev-group-details-div').on('click', '.new_contact_right_team_modules_rmv', function (event) {
            $(this).parents('.fg-internal-blk-div').remove();
        });
    },
    //remove row -internal
    removeInternalRow: function (options) {
        $('#fg-dev-user-rights-div').on('click', '.new_contact_team_right_rmv', function (event) {
            FgDirtyFields.removeFields($(this).parent());
            deleteRow($(this), '.fg-dev-new-elmt-contact', options.newExclude); // Calling common delete function to remove new row
        });
    },
    //remove row -internal
    removeInternalTeamCatRow: function (options) {
        $('#fg-dev-user-rights-div-' + options.roleId).on('click', '.new_contact_team_right_rmv', function (event) {
            FgDirtyFields.removeFields($(this).parent());
            deleteRow($(this), '.fg-dev-new-elmt-contact', options.newExclude); // Calling common delete function to remove new row

        });
    },
    //INTERNAL -userrights discard - BIND TEMPLATE ONES MORE 
    resetUserRightsCallback: function () { // Function to reset all changes made
        $('#userRightsForm').on('click', '#reset_changes', function (event) {
            initPageFunctions('discard');
        });
    },
    //backend internal and internal page save
    saveUserRights: function (options, from) { // Save all user rights row including all types
        $('#save_changes').click(function () {
            $('#failcallbackServerSide').hide();
            $('form').find('.has-error').removeClass('has-error');
            var validation = 0;
            
            if (options.saveFlag == 1) {
                return false;
            }
            
            $('.fg-dev-auto-complete-val').each(function () {
                if ($(this).val() == '') { // Setting validation flag if there is any errors
                    validation = 1;
                    $(this).siblings().first().addClass("has-error");

                }
            });
            if($('form').find('.has-error').length != 0){
                validation = 1;
            }
            if (validation == 0) { // Checking validation. If any, should display error message
                options.saveFlag = 1;
                var objectGraph = {};
                FgDirtyFields.updateFormState();
                //parse the all form field value as json array and assign that value to the array
                if (from == 'internal') {
                    objectGraph = FgInternalParseFormField.fieldParse();
                } else {
                    objectGraph = FgParseFormField.fieldParse();
                }
                var userRightArr = JSON.stringify(objectGraph);
              
                FgXmlHttp.post(options.saveUrl, {'postArr': userRightArr}, false, initPageFunctions);
            } else {
                $('#failcallbackServerSide').show(); // Displaying errors
                //scroll to top common form error alert on failing validation
                FgXmlHttp.scrollToErrorDiv();
            }
        });
    },
    //remove row -internal settings - backend
    removeInternalTeamRow: function () {
        $('#fg-dev-user-rights-div').on('click', '.new_team_right_rmv', function (event) {
            $(this).parent('.fg-internal-blk-div').remove();
        });
    },
    // This function is used to handle the auto complete functionlity
    // The function is common for all userrights
    handleTypeaheadAdmins: function (item, randomNumber, groupDetails, typeHeadOptions, typeFlag, roleId, containerId) {

        var container = containerId ? containerId : '#fg-dev-user-rights-div';
        //handle the data for autocomplete
        if (roleId) {
            var data = {'isCompany': 2, 'roleId': roleId};
        } else {
            var data = {'isCompany': 2};
        }
        var engine = new Bloodhound({
            remote: {url: typeHeadOptions.contactNameUrl,
                ajax: {data: data, method: 'post'},
                filter: function (contacts) {
                    dataset = [];
                    $.map(contacts, function (contact) {
                        var exists = false;
                        for (i = 0; i < typeHeadOptions.newExcludeAdmins.length; i++) {
                            if (contact.id == typeHeadOptions.newExcludeAdmins[i][0].id) {
                                var exists = true;
                            }
                        }
                        if (!exists) {
                            dataset.push({'id': contact.id, 'value': contact.contactname});
                        }
                    });
                    return dataset;
                }
            },
            datumTokenizer: function (d) {
                return Bloodhound.tokenizers.whitespace(d.name);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            limit: 2000
        });
        engine.initialize();
        $(item).tokenfield({
            typeahead: [
                null,
                {
                    source: engine.ttAdapter(),
                    displayKey: 'value'
                }
            ]
        }).on('tokenfield:createtoken', function (e) {
            if(typeof e.attrs.id == "undefined"){
                e.preventDefault();
            }else{
                if (item == typeHeadOptions.includeAdminsId + randomNumber) {
                    var val = $(typeHeadOptions.includeAdminsId + randomNumber).val();
                    var id = typeHeadOptions.includeAdminsId + randomNumber;
                } else {
                    var val = $(typeHeadOptions.excludeAdminId).val();
                    var id = typeHeadOptions.excludeAdminId;

                }
                if (val == '') {
                    var newval = e.attrs.id;
                } else {
                    var newval = val + ',' + e.attrs.id;
                }
                $(id).val(newval);
            }

        }).on('tokenfield:createdtoken', function (e) { // This function is called when a new token is created in the auto complete area
            typeHeadOptions.newExcludeAdmins.push($(item).tokenfield('getTokens'));
            $(typeHeadOptions.includeAdminsId + randomNumber + '-tokenfield').hide();
            $('.tt-hint').hide();

            var type = typeHeadOptions.dataKey.indexOf('#');

            if (type == -1) {
                var dataKey = typeHeadOptions.dataKey;
            } else {
                var sub1 = typeHeadOptions.dataKey.substring(0, type);
                var sub = typeHeadOptions.dataKey.substring(type + 1);
                var dataKey = sub1 + randomNumber + sub;
            }

            // Data-key and name of auto complete value is set in here for dirty class
            $(container).find(typeHeadOptions.uniqueId + randomNumber).val(e.attrs.id);
            if (typeHeadOptions.clubAdminFlag == 0) { // Checking whether the area is a club admin or not
                $(container).find(typeHeadOptions.uniqueId + randomNumber).attr('data-key', dataKey + e.attrs.id);
                $(container).find(typeHeadOptions.uniqueId + randomNumber).attr('name', typeHeadOptions.name + randomNumber + '_contact_' + e.attrs.id);
            } else {
                $(container).find(typeHeadOptions.uniqueId + randomNumber).attr('data-key', dataKey + e.attrs.id + '.group.' + groupDetails);
                $(container).find(typeHeadOptions.uniqueId + randomNumber).attr('name', typeHeadOptions.name + groupDetails + '_contact_' + e.attrs.id);
            }

            $(container).find(typeHeadOptions.uniqueId + randomNumber).attr('class', typeHeadOptions.class);

            if (typeHeadOptions.clubAdminFlag == 0) { // Checking whether the area is a club admin or not
                _.each(groupDetails, function (allAdminsVal, allAdminsKey) {
                    $(container).find('#new_admin_group_' + allAdminsVal.group_id + '_' + randomNumber).attr('data-key', 'new_all.' + typeFlag + '.admin.contact.' + e.attrs.id + '.group.' + allAdminsVal.group_id);
                });
            }

        }).on('tokenfield:removetoken', function (e) { // Function is called when removing a contact from auto complete area 
            var deletedId = e.attrs.id;
            // Data-key and name of auto complete value is set in here for dirty class
            if(typeof e.attrs.id == "undefined"){
                $(container).find(typeHeadOptions.uniqueId + randomNumber).parent().removeClass('has-error');
            }
            for (i = 0; i < typeHeadOptions.newExcludeAdmins.length; i++) {
                if (deletedId == typeHeadOptions.newExcludeAdmins[i][0].id) {
                    typeHeadOptions.newExcludeAdmins.splice(i, 1);
                }
            }

            // Need to undet fairgatedirty values when removing contacts
            $(container).find(typeHeadOptions.uniqueId + randomNumber).val('');
            $(container).find(typeHeadOptions.uniqueId + randomNumber).attr('data-key', '');
            $(container).find(typeHeadOptions.uniqueId + randomNumber).attr('name', '');
            $(container).find(typeHeadOptions.uniqueId + randomNumber).removeAttr('class').addClass('fg-dev-auto-complete-val hide');

            $(typeHeadOptions.includeAdminsId + randomNumber + '-tokenfield').show();
            $('.tt-hint').show();
        });
    },
};

// Common funtion to delete newly created user rights row
function deleteRow(thisVal, item, excludeArray) {
    var deletedContactId = thisVal.siblings(item).val();
    for (i = 0; i < excludeArray.length; i++) {
        if (deletedContactId == excludeArray[i][0].id) {
            excludeArray.splice(i, 1);
        }
    }
    thisVal.parents('.fg-dev-new-contact-div').remove();

}
