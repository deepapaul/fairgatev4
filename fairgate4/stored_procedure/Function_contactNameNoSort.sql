DROP FUNCTION IF EXISTS `contactNameNoSort`//

CREATE FUNCTION `contactNameNoSort`(contactId INT, onlyCompanyName INT) RETURNS TEXT 
BEGIN
	DECLARE `contactNameNoSort` TEXT DEFAULT '';

        SELECT (CASE WHEN (c.is_company = 1) THEN (IF ((c.has_main_contact = 1 AND onlyCompanyName = 0), (SELECT CONCAT(m.`9`, ' (', m.`2`, ' ', m.`23`, ')') FROM `master_system` m WHERE m.`fed_contact_id` = (SELECT fed_contact_id FROM fg_cm_contact WHERE id = contactId)), (SELECT m.`9` FROM `master_system` m WHERE m.`fed_contact_id` = (SELECT fed_contact_id FROM fg_cm_contact WHERE id = contactId)))) ELSE (SELECT CONCAT(m.`2`, ' ', m.`23`) FROM `master_system` m WHERE m.`fed_contact_id` = (SELECT fed_contact_id FROM fg_cm_contact WHERE id = contactId)) END) INTO `contactNameNoSort` FROM `fg_cm_contact` c WHERE c.`id` = contactId LIMIT 1;

        RETURN `contactNameNoSort`;
END