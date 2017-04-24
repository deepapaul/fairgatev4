-- Get federation member count in a club for a fedeartion
DROP FUNCTION IF EXISTS `getFedMemberCount`//
CREATE FUNCTION `getFedMemberCount`(clubId INT,fedId INT) RETURNS INTEGER
BEGIN
    DECLARE FedMemberCount INTEGER DEFAULT 0;
    -- get count of contact ids from fg cm contact when (clubid is the overview id or fgcmcontact clubids in sublevel of overview id) and membership cat id is fedmember
    SELECT COUNT(fg_cm_contact.id) INTO FedMemberCount FROM fg_cm_contact WHERE  (fg_cm_contact.club_id=clubId OR fg_cm_contact.club_id IN ((SELECT hi.id as ids FROM (SELECT sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := clubId,@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club hi ON hi.id = ho.id))) AND fg_cm_contact.membership_cat_id IN( SELECT id FROM fg_cm_membership WHERE fg_cm_membership.club_id=fedId AND fg_cm_membership.is_fed_category=1);
    RETURN FedMemberCount;
END
