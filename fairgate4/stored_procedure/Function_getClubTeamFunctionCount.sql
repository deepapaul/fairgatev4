DROP FUNCTION `getClubTeamFunctionCount`//
CREATE DEFINER=`admin`@`localhost` FUNCTION `getClubTeamFunctionCount`(`teamcategoryid` INT,`roleid` INT, `functionid` INT, `clubid` INT) RETURNS int(11)
BEGIN
	DECLARE functionCount TEXT DEFAULT '';

	SELECT count(distinct(rc.contact_id)) INTO functionCount 
	FROM `fg_rm_role_contact` rc 
	LEFT JOIN `fg_rm_category_role_function` crf ON crf.id = rc.fg_rm_crf_id 
	LEFT JOIN `fg_rm_role` r ON r.id = crf.role_id AND r.team_category_id = teamcategoryid AND is_active = 1
	LEFT JOIN `fg_cm_contact` cc ON rc.contact_id = cc.id
	WHERE (IF ((roleid), (crf.`role_id` = roleid), (crf.`role_id` IN (r.id)))) and
		  (IF ((functionid), (crf.`function_id` = functionid), (1))) and
		  (crf.`club_id` = clubid);
  	RETURN functionCount;
END
