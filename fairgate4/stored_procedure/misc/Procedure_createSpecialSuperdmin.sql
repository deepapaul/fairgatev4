-- call `createSuperdmin`('Deepa', 'Paul', 'deepa@yopmail.com', 'deepa.paul', '', 0) - for single person
-- call `createSuperdmin`('', '', 'deepa.pits@yopmail.com', 'deepa.pits', 'Pits', 1) - for company
DROP PROCEDURE IF EXISTS `createSuperdmin`//
CREATE PROCEDURE `createSuperdmin`(admin_first_name VARCHAR(50), admin_last_name VARCHAR(50), admin_email VARCHAR(255), admin_username VARCHAR(255), admin_password VARCHAR(255))
BEGIN
	DECLARE new_contact_id, new_user_id INTEGER DEFAULT 0;
        DECLARE salt_text, encoded_password TEXT DEFAULT '';
	
	START TRANSACTION;

            INSERT INTO `fg_cm_contact` (`club_id`, main_club_id, `is_fairgate`, created_club_id) 
            VALUES (1, 1, 1, 1);

            SELECT LAST_INSERT_ID() INTO new_contact_id;

            INSERT INTO `master_system` (`fed_contact_id`, `2`, `3`, `23`) 
            VALUES (new_contact_id, admin_first_name, admin_email, admin_last_name);
            SELECT SHA1(admin_password) INTO encoded_password;
            INSERT INTO `sf_guard_user` (`first_name`, `last_name`, `username`, `username_canonical`, `email`, `email_canonical`, `algorithm`, `salt`, `password`, `is_active`, `is_super_admin`, `last_login`, `created_at`, `updated_at`, `contact_id`, `is_security_admin`, `enabled`, `plain_password`, `locked`, `expired`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `has_full_permission`) VALUES
            (admin_first_name, admin_last_name, admin_username, admin_username, admin_email, admin_email, 'sha1', salt_text, encoded_password, 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', new_contact_id, 0, 1, '', 0, 0, NULL, NULL, 'a:1:{i:0;s:10:"ROLE_SUPER";}', 0, 1);
            
            SELECT LAST_INSERT_ID() INTO new_user_id;

            INSERT INTO `sf_guard_user_group` (`user_id`, `group_id`, `created_at`, `updated_at`) VALUES (new_user_id, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00');
    COMMIT;
END