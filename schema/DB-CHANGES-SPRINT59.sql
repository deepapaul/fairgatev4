ALTER TABLE `fg_api_accesslog` ADD `response_code` VARCHAR(20) NOT NULL AFTER `response_detail`;

INSERT INTO `fg_cm_contact` (`id`, `club_id`, `subfed_contact_id`, `fed_contact_id`, `main_club_id`, `is_company`, `comp_def_contact`, `comp_def_contact_fun`, `is_draft`, `is_deleted`, `is_permanent_delete`, `is_seperate_invoice`, `is_fairgate`, `last_updated`, `is_new`, `member_id`, `dispatch_type_invoice`, `dispatch_type_dun`, `has_main_contact`, `has_main_contact_address`, `is_postal_address`, `created_at`, `joining_date`, `leaving_date`, `first_joining_date`, `archived_on`, `resigned_on`, `same_invoice_address`, `login_count`, `is_former_fed_member`, `last_login`, `map`, `import_table`, `import_id`, `fed_membership_cat_id`, `club_membership_cat_id`, `is_sponsor`, `is_stealth_mode`, `intranet_access`, `is_subscriber`, `system_language`, `is_household_head`, `old_fed_membership_id`, `is_fed_membership_confirmed`, `is_club_assignment_confirmed`, `fed_membership_assigned_club_id`, `created_club_id`, `allow_merging`, `merge_to_contact_id`, `quickwindow_visibilty`, `is_fed_admin`, `old_contact_id`, `club_type`) VALUES
(396030, 1, NULL, NULL, 1, 0, NULL, NULL, 0, 0, 0, 0, 1, '0000-00-00 00:00:00', 0, 0, 'POST', 'POST', 1, 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, 1, 0, 0, NULL, 1, NULL, NULL, NULL, NULL, 0, 0, 1, 0, 'default', 0, NULL, '0', NULL, NULL, 1, 1, NULL, 1, 0, NULL, NULL);


INSERT INTO `sf_guard_user` (`id`, `first_name`, `last_name`, `username`, `username_canonical`, `email`, `email_canonical`, `algorithm`, `salt`, `password`, `is_active`, `is_super_admin`, `last_login`, `created_at`, `updated_at`, `contact_id`, `club_id`, `is_security_admin`, `is_readonly_admin`, `is_team_admin`, `is_team_section_admin`, `last_reminder`, `enabled`, `plain_password`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`, `has_full_permission`, `auth_code`) VALUES
(103172, 'gotcourts', 'gotcourts', 'gotcourts', 'gotcourts', 'gotcourts@yopmail.com', 'gotcourts@yopmail.com', 'sha1', '', 'Kaz/8MTGYDPwLDxx1529Rij8ICql5YLXmRscOzvZuIIIdIkOUypiTUU5eslrDqnl1sZWRQoM/sbkmim0zHMcZw==', 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 396030, NULL, 0, 0, 0, 0, NULL, 1, '', 0, 0, NULL, NULL, NULL, 'a:1:{i:0;s:10:"ROLE_SUPER";}', 0, NULL, 1, 'Z3lTaGtJSE9MN1lSZDJjbDRuZlY5bzl0NnE4QmxxU0JDRFRKZWVLQmM5RnMwRCtwejdJNHBMalA0RDZMOXdxdFVmcEI4UEdpVHFKUS9xOWtTSFNVTkE9PQ==');


INSERT INTO `sf_guard_user_group` (`user_id`, `group_id`, `created_at`, `updated_at`) VALUES
(103172, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');


/website/cms/resavejson/allpage
