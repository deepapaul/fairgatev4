DROP PROCEDURE IF EXISTS `insertNewsletterContactsToSpoolv4`//
CREATE PROCEDURE `insertNewsletterContactsToSpoolv4`(IN newsletterId INT, IN manualContactsQry MEDIUMTEXT, IN nonMandatoryQry MEDIUMTEXT, IN clubDefaultLang VARCHAR(2), IN clubDefaultSystemLang VARCHAR(2), IN masterTable TEXT)
BEGIN
	DECLARE curSubscriberSelection VARCHAR(2);
	DECLARE curSendDate DATETIME;
        DECLARE curSalutation TEXT CHARSET utf8 DEFAULT "";
        DECLARE subscriberLangQry TEXT DEFAULT "1";
	DECLARE curClubType, curSalutationType, curNewsletterType, curPublishType, curLanguageSelection, langQry, languagesStr, systemLangQry, activeLangsStr, corresLangQry, contactIdQry TEXT DEFAULT "";
	DECLARE qry1, qry2 MEDIUMTEXT DEFAULT "";
	DECLARE curClubId, curLastSpoolContactId, curIncludeFormerMembers, curRecepientList INTEGER DEFAULT 0;
	DECLARE recordNotFound, pos, rowsInsertedInSpool, islangCheck INTEGER DEFAULT 0;
-- 	Assign name for Mysql exception "General error: 1615 Prepared statement needs to be re-prepared"
	DECLARE mysqlPrepareException CONDITION FOR 1615;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET recordNotFound = 1;
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		ROLLBACK;
	END;
	SET autocommit = 0;
	SET SESSION tmp_table_size = 67108864;
	SET SESSION max_heap_table_size = 67108864;
	START TRANSACTION;
	BLOCK1: BEGIN
		DECLARE EXIT HANDLER FOR mysqlPrepareException
		BEGIN
			ROLLBACK;
			UPDATE fg_cn_newsletter SET is_cron=0 WHERE id=newsletterId;
		END;
		SELECT `club_id`,`send_date`,`salutation_type`,`salutation`,`newsletter_type`,`publish_type`,`language_selection`,`last_spool_contact_id`,`is_subscriber_selection`,`include_former_members`,`recepient_list`,`club_type`  
		INTO curClubId,curSendDate,curSalutationType,curSalutation,curNewsletterType,curPublishType,curLanguageSelection,curLastSpoolContactId,curSubscriberSelection,curIncludeFormerMembers,curRecepientList,curClubType FROM `fg_cn_newsletter` 
                LEFT JOIN `fg_club` ON `fg_club`.`id` = `fg_cn_newsletter`.`club_id` WHERE `fg_cn_newsletter`.`id` = newsletterId;

--              QUERY FOR CORRESPONDANCE LANGUAGE CHECKING
		IF (curLanguageSelection = 'SELECTED') THEN
			SELECT CONCAT("'",GROUP_CONCAT(DISTINCT `language_code` SEPARATOR "','"),"'") INTO languagesStr FROM `fg_cn_newsletter_publish_lang` WHERE `newsletter_id`=newsletterId;
		END IF;

		IF (languagesStr IS NOT NULL AND languagesStr != '') THEN 
			SET langQry = CONCAT("WHERE (t.`lang` IN (",languagesStr,"))");
                        SET subscriberLangQry = CONCAT(" (s.`correspondance_lang` IN (",languagesStr,"))");
			SET islangCheck = 1;
		END IF;

--              ASSIGN THE ACTIVE LANGUAGES AND SYSTEM LANGUAGES IN THIS CLUB TO VARIABLES
                SELECT GROUP_CONCAT(DISTINCT CL.`correspondance_lang`) INTO activeLangsStr FROM `fg_club_language_settings` AS CLS INNER JOIN `fg_club_language` AS CL ON (CL.`id` = CLS.`club_language_id`) WHERE CLS.`club_id` = curClubId 
                AND CLS.`is_active` = 1 ORDER BY CLS.`sort_order` ASC;

                DROP TABLE IF EXISTS `club_languages_columncursor`;
                CREATE TEMPORARY TABLE `club_languages_columncursor` AS SELECT DISTINCT CL.`correspondance_lang`, CL.`system_lang`, CLS.sort_order FROM `fg_club_language_settings` CLS INNER JOIN `fg_club_language` CL ON (CL.`id` = CLS.`club_language_id`) 
                WHERE CLS.`club_id` = curClubId AND CLS.`is_active`=1;

-- 		BEFORE INSERTING TO fg_mail_message(spool) TABLE DELETE ENTRIES FROM fg_cn_newsletter_receiver_log TABLE FOR THIS newsletter_id
		IF (curLastSpoolContactId = 0) THEN
			DELETE FROM fg_cn_newsletter_receiver_log WHERE newsletter_id=newsletterId;
		END IF;

		IF (curPublishType = 'SUBSCRIPTION') THEN 
--                      INSERT TO fg_cn_newsletter_receiver_log TABLE WITH NON MANDATORY FILTER CRITERIA AND FORMER FEDERATION MEMBER IF SELECTED
                        IF (nonMandatoryQry != '' AND nonMandatoryQry IS NOT NULL) THEN
                                SET systemLangQry = CONCAT("(CASE WHEN (t.`systemLanguage` = 'default' AND ((FIND_IN_SET(t.`lang`, '", activeLangsStr, "') ) > 0) ) THEN (SELECT CLT.`system_lang` FROM `club_languages_columncursor` CLT WHERE CLT.`correspondance_lang` = t.`lang`) WHEN ((t.`systemLanguage` = 'default') OR (t.`systemLanguage` IS NULL OR t.`systemLanguage` = '')) THEN ('", clubDefaultSystemLang, "') ELSE t.`systemLanguage` END )"); 
                                SET corresLangQry = CONCAT("(CASE WHEN (FIND_IN_SET(t.`lang` ,'", activeLangsStr, "') > 0) THEN t.`lang` ELSE '", clubDefaultLang, "' END) AS corresLang");
                                SET @qry1 = CONCAT("INSERT INTO `fg_cn_newsletter_receiver_log` (`contact_id`, `corres_lang`, `club_id`, `newsletter_id`, `email`, `salutation`,`email_field_ids`,`linked_contact_ids`,`system_language`,`send_date`, `resent_email`, `bounce_count`) SELECT t.`contactId`,", corresLangQry, ", t.`clubid`,t.`newsletterId`,t.`email`,t.`salutation`,t.`emailFieldId`,CASE WHEN t.`linkedContactid` IS NULL THEN '' ELSE t.`linkedContactid` END, ", systemLangQry, ",'", curSendDate, "', '', 0 FROM (", nonMandatoryQry, ") AS t ",langQry," ON DUPLICATE KEY UPDATE `fg_cn_newsletter_receiver_log`.`contact_id` = CONCAT(`fg_cn_newsletter_receiver_log`.`contact_id`, IF(`fg_cn_newsletter_receiver_log`.`contact_id` != '', ',', ''), VALUES(contact_id)), `email_field_ids` = CONCAT(`email_field_ids`, IF(`email_field_ids` != '', ',', ''), VALUES(email_field_ids)), `linked_contact_ids` = CONCAT(`linked_contact_ids`, IF(`linked_contact_ids` != '', ',', ''), VALUES(linked_contact_ids))");
                                PREPARE stmt1 FROM @qry1;
                                EXECUTE stmt1 ;
                                DEALLOCATE PREPARE stmt1; 
                        END IF;
--                      INSERT TO fg_cn_newsletter_receiver_log TABLE WITH ACTIVE SUBSCRIBERS
                        IF (curSubscriberSelection = '1') THEN
                                SET systemLangQry = CONCAT("(CASE WHEN (FIND_IN_SET(s.`correspondance_lang`, '", activeLangsStr, "') > 0) THEN (SELECT CLT.`system_lang` FROM `club_languages_columncursor` CLT WHERE CLT.`correspondance_lang` = s.`correspondance_lang`) ELSE ('", clubDefaultSystemLang, "') END )"); 
                                SET corresLangQry = CONCAT("(CASE WHEN (FIND_IN_SET(s.`correspondance_lang` ,'", activeLangsStr, "') > 0) THEN s.`correspondance_lang` ELSE '", clubDefaultLang, "' END) AS corresLang");
                                SET @qry4 = CONCAT("INSERT INTO `fg_cn_newsletter_receiver_log` (`subscriber_id`, `corres_lang`, `club_id`, `newsletter_id`, `email`, `salutation`,`email_field_ids`,`system_language`,`send_date`, `resent_email`, `bounce_count`) SELECT s.`id`,", corresLangQry, ", s.`club_id`,", newsletterId , ", s.`email`, (CASE WHEN ('", curSalutationType, "' = 'INDIVIDUAL') THEN subscriberSalutationText(s.`id`, ", curClubId, ",'", clubDefaultSystemLang,"','", clubDefaultLang,"') ELSE '",curSalutation,"' END), 'E-Mail',", systemLangQry, ",'", curSendDate, "', '', 0 FROM `fg_cn_subscriber` s WHERE (s.`club_id`=", curClubId, " AND ",subscriberLangQry,") ON DUPLICATE KEY UPDATE `fg_cn_newsletter_receiver_log`.`email` = VALUES(email)");
                                PREPARE stmt4 FROM @qry4;
                                EXECUTE stmt4 ;
                                DEALLOCATE PREPARE stmt4; 
                        END IF;
		ELSE
--          		INSERT TO fg_cn_newsletter_receiver_log TABLE WITH UPDATED MANDATORY RECEIVER LIST
                        IF (curRecepientList != '' AND curRecepientList IS NOT NULL) THEN 
                                IF (curClubType = 'federation') THEN
                                    SET contactIdQry = "t.fed_contact_id";
                                ELSE
                                    SET contactIdQry = "t.contact_id";
                                END IF;
                                SET systemLangQry = CONCAT("(CASE WHEN (m.`system_lang` = 'default' AND ((FIND_IN_SET(m.`corres_lang`, '", activeLangsStr, "') ) > 0) ) THEN (SELECT CLT.`system_lang` FROM `club_languages_columncursor` CLT WHERE CLT.`correspondance_lang` = m.`corres_lang` ORDER BY sort_order ASC LIMIT 0,1 ) WHEN ((m.`system_lang` = 'default') OR (m.`system_lang` IS NULL OR m.`system_lang` = '')) THEN ('", clubDefaultSystemLang, "') ELSE m.`system_lang` END )"); 
                                SET corresLangQry = CONCAT("(CASE WHEN (FIND_IN_SET(m.`corres_lang` , '", activeLangsStr, "') > 0) THEN m.`corres_lang` ELSE '", clubDefaultLang, "' END) AS corresLang");
                                SET @qry2 = CONCAT("INSERT INTO `fg_cn_newsletter_receiver_log` (`contact_id`, `corres_lang`, `club_id`, `newsletter_id`, `email`, `salutation`,`email_field_ids`,`linked_contact_ids`,`system_language`,`send_date`, `resent_email`, `bounce_count`) SELECT m.`contact_id`, ", corresLangQry, ", ", curClubId, ", ", newsletterId, ", m.`email`, IF(('", curSalutationType, "'= 'INDIVIDUAL'), m.`salutation`, '", curSalutation, "'), m.`email_field_id`, CASE WHEN m.`linked_contact_id` IS NULL THEN '' ELSE m.`linked_contact_id` END,", systemLangQry,",'", curSendDate,"', '', 0 FROM `fg_cn_recepients_mandatory` AS m LEFT JOIN ", masterTable, " AS t ON (", contactIdQry, " = m.contact_id) WHERE (m.`recepient_list_id` =", curRecepientList, "  AND IF((", islangCheck, " = 1), FIND_IN_SET(m.`corres_lang`,(SELECT CONCAT(GROUP_CONCAT(`language_code` SEPARATOR ',')) FROM `fg_cn_newsletter_publish_lang` WHERE `newsletter_id`= ", newsletterId, ")),1)) ON DUPLICATE KEY UPDATE `fg_cn_newsletter_receiver_log`.`contact_id` = CONCAT(`fg_cn_newsletter_receiver_log`.`contact_id`, IF(`fg_cn_newsletter_receiver_log`.`contact_id` != '', ',', ''), VALUES(contact_id)), `email_field_ids` = CONCAT(`email_field_ids`, IF(`email_field_ids` != '', ',', ''), VALUES(email_field_ids)), `linked_contact_ids` = CONCAT(`linked_contact_ids`, IF(`linked_contact_ids` != '', ',', ''), VALUES(linked_contact_ids))");
                                PREPARE stmt2 FROM @qry2;
                                EXECUTE stmt2 ;
                                DEALLOCATE PREPARE stmt2; 
                        END IF;
--                      INSERT TO fg_cn_newsletter_receiver_log TABLE WITH ADDITIONAL MANUAL CONTACTS main/substitute emails
                        IF (manualContactsQry != '' AND manualContactsQry IS NOT NULL) THEN 
                                SET systemLangQry = CONCAT("(CASE WHEN (t.`systemLanguage` = 'default' AND ((FIND_IN_SET(t.`lang`, '", activeLangsStr, "') ) > 0) ) THEN (SELECT CLT.`system_lang` FROM `club_languages_columncursor` CLT WHERE CLT.`correspondance_lang` = t.`lang`) WHEN ((t.`systemLanguage` = 'default') OR (t.`systemLanguage` IS NULL OR t.`systemLanguage` = '')) THEN ('", clubDefaultSystemLang, "') ELSE t.`systemLanguage` END )"); 
                                SET corresLangQry = CONCAT("(CASE WHEN (FIND_IN_SET(t.`lang` , '", activeLangsStr, "') > 0) THEN t.`lang` ELSE '", clubDefaultLang, "' END) AS corresLang");
                                SET @qry3 = CONCAT("INSERT INTO `fg_cn_newsletter_receiver_log` (`contact_id`, `corres_lang`, `club_id`, `newsletter_id`, `email`, `salutation`,`email_field_ids`,`linked_contact_ids`,`system_language`,`send_date`, `resent_email`, `bounce_count`) SELECT t.`contactId`,", corresLangQry, ",t.`clubid`,t.`newsletterId`,t.`email`,t.`salutation`,t.`emailFieldId`,CASE WHEN t.`linkedContactid` IS NULL THEN '' ELSE t.`linkedContactid` END,", systemLangQry,",'", curSendDate,"', '', 0 FROM (", manualContactsQry, ") AS t ",langQry," ON DUPLICATE KEY UPDATE `fg_cn_newsletter_receiver_log`.`contact_id` = CONCAT(`fg_cn_newsletter_receiver_log`.`contact_id`, IF(`fg_cn_newsletter_receiver_log`.`contact_id` != '', ',', ''), VALUES(contact_id)), `email_field_ids` = CONCAT(`email_field_ids`, IF(`email_field_ids` != '', ',', ''), VALUES(email_field_ids)), `linked_contact_ids` = CONCAT(`linked_contact_ids`, IF(`linked_contact_ids` != '', ',', ''), VALUES(linked_contact_ids))");
                                PREPARE stmt3 FROM @qry3;
                                EXECUTE stmt3 ;
                                DEALLOCATE PREPARE stmt3; 
                        END IF;
		END IF;

-- 		IF ANY EXCLUDED EMAILS ARE THERE FOR THIS NEWSLETTER DELETE THEM FROM fg_cn_newsletter_receiver_log TABLE
                DELETE a FROM `fg_cn_newsletter_receiver_log` a JOIN `fg_cn_newsletter_exclude_contacts` b ON a.`newsletter_id` = b.`newsletter_id` where a.newsletter_id=newsletterId AND a.email=b.email AND a.salutation=b.salutation;

--              INSERT TO fg_mail_message WITH UNIQUE VALUES
                INSERT INTO `fg_mail_message` (`newsletter_id`, `email`, `cron_instance`, `receiver_log_id`, `salutation`) 
                (SELECT newsletterId, `email`, IF(((`fg_cn_newsletter_receiver_log`.`id` MOD 4) = 0),4,(`fg_cn_newsletter_receiver_log`.`id` MOD 4)),`fg_cn_newsletter_receiver_log`.`id`, `fg_cn_newsletter_receiver_log`.`salutation` FROM `fg_cn_newsletter_receiver_log` 
                WHERE `newsletter_id`=newsletterId AND `is_sent` = 0);

-- 		TO PRIORITIZE NEWSLETTER
                SELECT COUNT(*) INTO rowsInsertedInSpool FROM fg_mail_message WHERE newsletter_id=newsletterId;
                CASE
--                      IF NO RECEIVER LOG ENTRY INSERTED TO SPOOL THEN SET NEWSLETTER STATUS TO DRAFT
                        WHEN (rowsInsertedInSpool = 0) THEN
                                UPDATE fg_cn_newsletter SET `status` = 'draft' WHERE id =newsletterId;
                        WHEN (rowsInsertedInSpool < 1000) THEN
                                UPDATE fg_mail_message SET priority=1 WHERE newsletter_id=newsletterId;
                        WHEN (rowsInsertedInSpool < 2000) THEN
                                UPDATE fg_mail_message SET priority=2 WHERE newsletter_id=newsletterId;
                        WHEN (rowsInsertedInSpool < 5000) THEN
                                UPDATE fg_mail_message SET priority=3 WHERE newsletter_id=newsletterId;
                        WHEN (rowsInsertedInSpool < 10000) THEN
                                UPDATE fg_mail_message SET priority=4 WHERE newsletter_id=newsletterId;
                        ELSE
                                UPDATE fg_mail_message SET priority=5 WHERE newsletter_id=newsletterId;
                END CASE;

-- 		AFTER ALL THE ENTRIES HAVE BEEN INSERTED IN SPOOL SET last_spool_contact_id=-1 AND is_cron=0 FOR THIS NEWSLETTER
                UPDATE fg_cn_newsletter SET last_spool_contact_id = -1, is_cron = 0 WHERE id=newsletterId;
	END BLOCK1;
    COMMIT; 
END