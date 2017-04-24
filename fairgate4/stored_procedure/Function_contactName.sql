DROP FUNCTION IF EXISTS `contactName`//
CREATE FUNCTION `contactName`(contactId INT) RETURNS text
BEGIN
DECLARE contactName TEXT DEFAULT '';
SELECT 
    (CASE WHEN (c.is_company = 1) 
    THEN 
        IF ((c.has_main_contact = 1), 
            CONCAT(m.`9`, ' (', m.`23`, IF (m.`2` IS NULL OR m.`2`='' OR m.`23` IS NULL OR m.`23`='',' ',', '), m.`2`, ')'), m.`9`)            
    ELSE     
        CONCAT(m.`23`, IF (m.`2` IS NULL OR m.`2`='' OR m.`23` IS NULL OR m.`23`='',' ',', '), m.`2`)
    END)  INTO contactName  
FROM `master_system` m 
LEFT JOIN fg_cm_contact `c` ON c.fed_contact_id = m.fed_contact_id 
WHERE c.id = contactId LIMIT 1;
RETURN contactName;
END