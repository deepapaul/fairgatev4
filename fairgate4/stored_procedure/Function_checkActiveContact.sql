DROP FUNCTION IF EXISTS `checkActiveContact`//
CREATE FUNCTION `checkActiveContact`(`contactId` INT, `clubId` INT) RETURNS int(11)
BEGIN
    DECLARE result INT DEFAULT NULL;
   
    SELECT c1.id INTO result FROM fg_cm_contact c1 INNER JOIN fg_cm_contact c2 ON c2.id = contactId AND c2.fed_contact_id = c1.fed_contact_id WHERE  c1.club_id = clubId AND  (c1.is_fed_membership_confirmed = '0' OR (c1.is_fed_membership_confirmed = '1' AND c1.old_fed_membership_id IS NOT NULL) OR c1.created_club_id = clubId ) AND c1.is_deleted = 0 AND c1.is_permanent_delete = 0 AND c1.is_draft = 0 AND ( c1.fed_membership_cat_id IS NOT NULL OR c1.created_club_id = clubId );

    RETURN result;
          
END


