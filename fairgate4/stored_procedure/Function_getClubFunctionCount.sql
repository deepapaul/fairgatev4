-- To get assigned contacts count for a role function in a club
DROP FUNCTION `getClubFunctionCount`//
CREATE DEFINER=`admin`@`localhost` FUNCTION `getClubFunctionCount`(`categoryid` INT,`roleid` INT, `functionid` INT, `clubid` INT) RETURNS int(11)
BEGIN
	DECLARE functionCount TEXT DEFAULT '';

	SELECT count(distinct(rc.contact_id)) INTO functionCount 
	FROM `fg_rm_role_contact` rc 
	LEFT JOIN `fg_rm_category_role_function` crf ON crf.id = rc.fg_rm_crf_id 
	LEFT JOIN `fg_cm_contact` cc ON rc.contact_id = cc.id
	WHERE (IF ((categoryid), (crf.`category_id` = categoryid), (1))) and 
		  (IF ((roleid), (crf.`role_id` = roleid), (1))) and
		  (IF ((functionid), (crf.`function_id` = functionid), (1))) and
		  ((cc.`club_id` IN (SELECT c.id FROM 
			(SELECT  sublevelClubs(id) AS id, @level AS level FROM 
				(SELECT  @start_with := clubid,@id := @start_with,@level := 0) vars, 
			fg_club WHERE @id IS NOT NULL) 
		ho JOIN fg_club c ON c.id = ho.id)) OR (cc.`club_id` = clubid));
  	RETURN functionCount;
END
