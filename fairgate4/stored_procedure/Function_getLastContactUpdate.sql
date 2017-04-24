-- Get last updated time of contact in a club
DROP FUNCTION `getLastContactUpdate`//
CREATE DEFINER=`admin`@`localhost` FUNCTION `getLastContactUpdate`(`clubId` INT, `fedId` INT) RETURNS text CHARSET utf8
    NO SQL
BEGIN 
	DECLARE LAST_CONTACT_UPDATE TEXT DEFAULT ''; 
	SELECT fg_cm_contact.last_updated INTO LAST_CONTACT_UPDATE FROM fg_club INNER JOIN fg_cm_contact ON fg_club.id=fg_cm_contact.club_id WHERE (fg_cm_contact.club_id=clubId OR fg_cm_contact.club_id IN ((SELECT hi.id as ids FROM (SELECT sublevelClubs(id) AS id, @level AS level FROM (SELECT @start_with := clubId,@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club hi ON hi.id = ho.id))) ORDER BY fg_cm_contact.last_updated desc LIMIT 0,1 ; 
	RETURN LAST_CONTACT_UPDATE; 
END
