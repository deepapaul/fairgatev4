DROP FUNCTION IF EXISTS `contactNameYOB`//

CREATE FUNCTION `contactNameYOB`(contactId INT) RETURNS TEXT
BEGIN
	DECLARE `contactNameYOB` TEXT DEFAULT '' ;
SELECT 
(

    CASE WHEN (c.is_company=0) 
    THEN ( 
        SELECT CONCAT(m.`23`,' ',`2`,
                      IF(DATE_FORMAT(m.`4`,'%Y') = '0000' OR m.`4` is NULL OR m.`4` ='','',CONCAT(' (',DATE_FORMAT(m.`4`,'%Y'),')')
                        )
                     ) FROM `master_system` m WHERE m.`fed_contact_id` = (SELECT fed_contact_id FROM fg_cm_contact WHERE id = contactId)
        ) 
    ELSE (
         IF (
             (c.has_main_contact = 1), 
             (SELECT CONCAT(m.`9`, ' (', m.`23`, ' ', m.`2`, ')') FROM `master_system` m WHERE m.`fed_contact_id` = (SELECT fed_contact_id FROM fg_cm_contact WHERE id = contactId)),
             (SELECT m.`9` FROM `master_system` m WHERE m.`fed_contact_id` = (SELECT fed_contact_id FROM fg_cm_contact WHERE id = contactId))
            )
    ) END )
    INTO contactNameYOB FROM `fg_cm_contact` c left join master_system m on c.id=m.fed_contact_id WHERE c.`id` = contactId LIMIT 1;
     RETURN contactNameYOB;
END