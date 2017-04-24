DROP PROCEDURE  IF EXISTS `updateRecipientContacts`//
CREATE PROCEDURE `updateRecipientContacts`(recipientListId INT, selectQuery TEXT, fromQuery TEXT, whereQuery TEXT, parentEmailJoinQuery TEXT, mainEmailIds VARCHAR(255), substituteEmail VARCHAR(255), primaryEmailFieldId INT)

BEGIN

DECLARE record_not_found 	 INTEGER DEFAULT 0; 
DECLARE emailFieldId     	 VARCHAR(255);
DECLARE strLen           	 INT DEFAULT 0;
DECLARE SubStrLen 	  	 INT DEFAULT 0;
DECLARE insertQuery      	 TEXT;
DECLARE saveSubstituteEmailConts INT DEFAULT 1;
DECLARE execQuery, exec_qry	 TEXT;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;
DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
	ROLLBACK;
END;

START TRANSACTION;

-- Delete current recipient contacts --
DELETE FROM `fg_cn_recepients_mandatory` WHERE `recepient_list_id`=recipientListId;

-- Save filtered contacts having the selected main emails - starts --
loop_mainEmailIds: LOOP

	SET strLen = LENGTH(mainEmailIds);
	SET emailFieldId = SUBSTRING_INDEX(mainEmailIds, ',', 1);

	IF (emailFieldId = 'parent_email') THEN
	    SET insertQuery = CONCAT(selectQuery, ", '", primaryEmailFieldId, "' AS emailField, 'parentemail' AS emailType, (SELECT `", primaryEmailFieldId, "` FROM master_system INNER JOIN fg_cm_contact cc ON cc.fed_contact_id = master_system.fed_contact_id WHERE cc.id=lc.contact_id) AS email, lc.contact_id AS linkedContId", " ", fromQuery, parentEmailJoinQuery, " ", whereQuery, " AND (((SELECT `", primaryEmailFieldId, "` FROM master_system INNER JOIN fg_cm_contact cc ON cc.fed_contact_id = master_system.fed_contact_id WHERE cc.id=lc.contact_id) IS NOT NULL) AND ((SELECT `", primaryEmailFieldId, "` FROM master_system INNER JOIN fg_cm_contact cc ON cc.fed_contact_id = master_system.fed_contact_id WHERE cc.id=lc.contact_id) != ''))");
	ELSE
	    SET insertQuery = CONCAT(selectQuery, ", '", emailFieldId, "' AS emailField, 'emailfield' AS emailType, `", emailFieldId, "` AS email, NULL AS linkedContId", " ", fromQuery, " ", whereQuery, " AND ((`", emailFieldId, "` IS NOT NULL) AND (`", emailFieldId, "` != ''))");
	END IF;

	SET @execQuery = CONCAT("INSERT INTO `fg_cn_recepients_mandatory` (`contact_id`, `recepient_list_id`, `salutation`, `corres_lang`, `system_lang`, `email_field_id`, `email_type`, `email`, `linked_contact_id`) ", insertQuery);

	PREPARE exec_qry FROM @execQuery;
        EXECUTE exec_qry;
        DEALLOCATE PREPARE exec_qry;

	IF (emailFieldId = substituteEmail) THEN
	    SET saveSubstituteEmailConts = 0;
	END IF;

	SET SubStrLen = LENGTH(SUBSTRING_INDEX(mainEmailIds, ',', 1));
	SET mainEmailIds = MID(mainEmailIds, SubStrLen+2, strLen);
    
    	IF ((mainEmailIds = NULL) || (mainEmailIds = '')) THEN
		LEAVE loop_mainEmailIds;
    	END IF;

END LOOP loop_mainEmailIds;


IF ((substituteEmail != '') AND (saveSubstituteEmailConts = 1)) THEN

	IF (substituteEmail = 'parent_email') THEN
	    SET insertQuery = CONCAT(selectQuery, ", '", primaryEmailFieldId, "' AS emailField, 'parentemail' AS emailType, (SELECT `", primaryEmailFieldId, "` FROM master_system INNER JOIN fg_cm_contact cc ON cc.fed_contact_id = master_system.fed_contact_id WHERE cc.id=lc.contact_id) AS email, lc.contact_id AS linkedContId", " ", fromQuery, parentEmailJoinQuery, " ", whereQuery, " AND (fg_cm_contact.id NOT IN (SELECT `contact_id` FROM `fg_cn_recepients_mandatory` WHERE `recepient_list_id`=",recipientListId,")) AND (((SELECT `", primaryEmailFieldId, "` FROM master_system INNER JOIN fg_cm_contact cc ON cc.fed_contact_id = master_system.fed_contact_id WHERE cc.id=lc.contact_id) IS NOT NULL) AND ((SELECT `", primaryEmailFieldId, "` FROM master_system INNER JOIN fg_cm_contact cc ON cc.fed_contact_id = master_system.fed_contact_id WHERE cc.id=lc.contact_id) != ''))");
	ELSE
	    SET insertQuery = CONCAT(selectQuery, ", '", substituteEmail, "' AS emailField, 'emailfield' AS emailType, `", substituteEmail, "` AS email, NULL AS linkedContId", " ", fromQuery, " ", whereQuery, " AND (fg_cm_contact.id NOT IN (SELECT `contact_id` FROM `fg_cn_recepients_mandatory` WHERE `recepient_list_id`=",recipientListId,")) AND ((`", substituteEmail, "` IS NOT NULL) AND (`", substituteEmail, "` != ''))");
	END IF;

	SET @execQuery = CONCAT("INSERT INTO `fg_cn_recepients_mandatory` (`contact_id`, `recepient_list_id`, `salutation`, `corres_lang`, `system_lang`, `email_field_id`, `email_type`, `email`, `linked_contact_id`) ", insertQuery);

	PREPARE exec_qry FROM @execQuery;
        EXECUTE exec_qry;
        DEALLOCATE PREPARE exec_qry;

END IF;



		SET @execQuery = CONCAT("UPDATE `fg_cn_recepients` SET updated_at = NOW(), `contact_count` = (SELECT COUNT(DISTINCT fg_cm_contact.id) ", fromQuery, " ", whereQuery, ") WHERE `id` = ", recipientListId);
	PREPARE exec_qry FROM @execQuery;
        EXECUTE exec_qry;
        DEALLOCATE PREPARE exec_qry;

		UPDATE `fg_cn_recepients` SET `mandatory_count` = (SELECT COUNT(DISTINCT CONCAT(`email`, '#', `salutation`)) FROM `fg_cn_recepients_mandatory` WHERE `recepient_list_id`=recipientListId) WHERE `id` = recipientListId;

		SET @execQuery = CONCAT("UPDATE `fg_cn_recepients` SET `subscriber_count` = (SELECT COUNT(DISTINCT fg_cm_contact.id) ", fromQuery, " ", whereQuery, " AND ((`", primaryEmailFieldId, "` IS NOT NULL) AND (`", primaryEmailFieldId, "` != '')) AND (fg_cm_contact.is_subscriber = 1)) WHERE `id` = ", recipientListId);
	PREPARE exec_qry FROM @execQuery;
        EXECUTE exec_qry;
        DEALLOCATE PREPARE exec_qry;


COMMIT;

END

