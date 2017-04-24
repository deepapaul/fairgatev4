DROP PROCEDURE IF EXISTS  `updateMemberId`//
CREATE PROCEDURE `updateMemberId`(IN `p_club_id` INT)
BEGIN
    DECLARE dynamicSQL TEXT;
    DECLARE exec_qry TEXT;
	SELECT MAX(member_id)+1 INTO @memberId FROM fg_cm_contact WHERE club_id = p_club_id;
        IF (@memberId IS NULL) THEN
            SET @memberId = 1;
        END IF;    
	SET @dynamicSQL=CONCAT("UPDATE fg_cm_contact C
	INNER JOIN (
		 SELECT c.id, @ROW := @ROW + 1 AS memberid 
		 FROM fg_cm_contact c 
		 JOIN (SELECT @ROW := ",@memberId,") next 
		 WHERE club_id = ",p_club_id," AND member_id = 0
		 ) AS M ON C.id = M.id
	SET C.member_id = M.memberid
	WHERE club_id = ",p_club_id," AND member_id = 0  ");
	PREPARE exec_qry FROM @dynamicSQL;
	EXECUTE exec_qry ;
	DEALLOCATE PREPARE exec_qry;
END