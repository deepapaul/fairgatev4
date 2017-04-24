-- Function to get assigned contacts count in aclub for a category
DROP FUNCTION `getFedMemberCount`//
CREATE DEFINER=`admin`@`localhost` FUNCTION `getFedMemberCount`(clubId INT,fedId INT) RETURNS int(11)
BEGIN
DECLARE FedMemberCount INTEGER DEFAULT 0;
   SELECT COUNT(fg_cm_contact.id) INTO FedMemberCount FROM fg_cm_contact WHERE fg_cm_contact.club_id=clubId  AND  ((fg_cm_contact.main_club_id=fg_cm_contact.club_id AND fg_cm_contact.fed_membership_cat_id IS NOT NULL) OR (fg_cm_contact.fed_membership_cat_id IS NOT NULL AND (fg_cm_contact.old_fed_membership_id IS NOT NULL OR fg_cm_contact.is_fed_membership_confirmed='0')) );
   RETURN FedMemberCount;
END
