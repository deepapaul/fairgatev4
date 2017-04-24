/**
 * Author:  Neethu George
 * Created: 27 Mar, 2017
 */
------------------------------------------------Sample ------------------------------------------------------
-- call `createSolutionAdmin`('Neethu', 'George', 'neethumg@gmail.com', 'neethumg', '', 0) - for single person
-- call `createSolutionAdmin`('', '', 'neethumg@gmail.com', 'neethumg', 'Pits', 1) - for company
---------------------------------------------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `createSolutionAdmin`//
CREATE PROCEDURE `createSolutionAdmin`(admin_first_name VARCHAR(50), admin_last_name VARCHAR(50), admin_email VARCHAR(255), admin_username VARCHAR(255), admin_company_name VARCHAR(255), isCompany INT(1))
BEGIN
    DECLARE new_contact_id, new_user_id INTEGER DEFAULT 0;
    DECLARE new_contact_name VARCHAR(255) DEFAULT '';
    
    START TRANSACTION;
        SELECT m.`fed_contact_id` FROM `master_system` m INNER JOIN `fg_cm_contact` c ON c.fed_contact_id = m.fed_contact_id  WHERE m.`3` = admin_email  AND c.`club_id` = 1 INTO new_contact_id;
        IF (new_contact_id = '') THEN
            INSERT INTO `fg_cm_contact` (`club_id`, main_club_id, `is_fairgate`, created_club_id) VALUES (1, 1, 1, 1);
            SELECT LAST_INSERT_ID() INTO new_contact_id;
            UPDATE `fg_cm_contact` SET `fed_contact_id` = new_contact_id WHERE `id` = new_contact_id;
            INSERT INTO `master_system` (`fed_contact_id`, `2`, `3`, `23`)  VALUES (new_contact_id, admin_first_name, admin_email, admin_last_name) ON DUPLICATE KEY UPDATE `2` =  admin_first_name, `23` = admin_last_name;
            SELECT contactName(new_contact_id) INTO new_contact_name;
            INSERT INTO fairgate_admin_ui.`fg_cm_contact` (`club_id`, main_club_id, `name`, `is_active`,`firstname`,`lastname`,`email`) VALUES (1, 1, new_contact_name, 1,admin_first_name,admin_last_name,admin_email);
        ELSE
            INSERT INTO `master_system` (`fed_contact_id`, `2`, `3`, `23`)  VALUES (new_contact_id, admin_first_name, admin_email, admin_last_name) ON DUPLICATE KEY UPDATE `2` =  admin_first_name, `23` = admin_last_name;
        END IF;

        SELECT `id` FROM `sf_guard_user`  WHERE `contact_id` = new_contact_id  INTO new_user_id;
        IF (new_user_id = '') THEN     
            INSERT INTO `sf_guard_user` (`first_name`, `last_name`, `username`, `username_canonical`, `email`, `email_canonical`, `algorithm`, `salt`, `password`, `is_active`, `is_super_admin`, `last_login`, `created_at`, `updated_at`, `contact_id`, `is_security_admin`, `enabled`, `plain_password`, `locked`, `expired`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `has_full_permission`,`club_id`) VALUES
            (admin_first_name, admin_last_name, admin_username, admin_username, admin_email, admin_email, 'sha1', '', '', 1, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', new_contact_id, 0, 1, '', 0, 0, NULL, NULL, 'a:1:{i:0;s:19:"ROLE_SOLUTION_ADMIN";}', 0, 1,1);

            SELECT LAST_INSERT_ID() INTO new_user_id;
        END IF;
        INSERT INTO `sf_guard_user_group` (`user_id`, `group_id`, `created_at`, `updated_at`) VALUES (new_user_id, 22, '0000-00-00 00:00:00', '0000-00-00 00:00:00');
    COMMIT;
END