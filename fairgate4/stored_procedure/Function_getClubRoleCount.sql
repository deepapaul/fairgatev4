-- Get assigned contacts count for a role in a club
DROP FUNCTION `getClubRoleCount`//
CREATE FUNCTION `getClubRoleCount`(`roleid` INT, `clubid` INT) RETURNS int(11)
BEGIN
	DECLARE roleCount TEXT DEFAULT '';
	DECLARE clubType TEXT DEFAULT '';
	DECLARE currclubType TEXT DEFAULT '';
	SELECT (club_type) INTO clubType FROM fg_rm_role r JOIN fg_club c 
	on c.id=r.club_id WHERE r.`id` = roleid ;
	SELECT (club_type) INTO currclubType FROM fg_club 
	where id=clubid ;
	 
    IF clubType='federation' THEN  
		SELECT count(distinct(rc.contact_id)) INTO roleCount 
		FROM  fg_cm_contact c inner join  `fg_rm_role_contact` rc  on rc.contact_id = c.fed_contact_id 
		LEFT JOIN `fg_rm_category_role_function` crf ON crf.id = rc.fg_rm_crf_id 
		WHERE crf.`role_id` = roleid and c.club_id=clubid and (c.is_fed_membership_confirmed='0' or(c.old_fed_membership_id is not null and c.is_fed_membership_confirmed='1' ) );
		 IF currclubType='sub_federation_club' OR currclubType='federation_club' THEN
			SELECT count(distinct(rc.contact_id)) INTO roleCount 
			FROM  fg_cm_contact c inner join  `fg_rm_role_contact` rc  on rc.contact_id = c.fed_contact_id 
			LEFT JOIN `fg_rm_category_role_function` crf ON crf.id = rc.fg_rm_crf_id 
			WHERE crf.`role_id` = roleid and c.club_id=clubid ;
		END IF;
	ELSEIF clubType='sub_federation'
		THEN SELECT count(distinct(rc.contact_id)) INTO roleCount 
		FROM  fg_cm_contact c inner join  `fg_rm_role_contact` rc  on rc.contact_id = c.subfed_contact_id 
		LEFT JOIN `fg_rm_category_role_function` crf ON crf.id = rc.fg_rm_crf_id 
		WHERE crf.`role_id` = roleid and c.club_id=clubid and (c.is_fed_membership_confirmed='0' or(c.old_fed_membership_id is not null and c.is_fed_membership_confirmed='1' ) );
		 IF currclubType='sub_federation_club' OR currclubType='federation_club' THEN
			SELECT count(distinct(rc.contact_id)) INTO roleCount 
			FROM  fg_cm_contact c inner join  `fg_rm_role_contact` rc  on rc.contact_id = c.subfed_contact_id 
			LEFT JOIN `fg_rm_category_role_function` crf ON crf.id = rc.fg_rm_crf_id 
			WHERE crf.`role_id` = roleid and c.club_id=clubid ;
		END IF;
	ELSE 
		 SELECT count(distinct(rc.contact_id)) INTO roleCount 
		FROM  fg_cm_contact c inner join  `fg_rm_role_contact` rc  on rc.contact_id = c.id 
		LEFT JOIN `fg_rm_category_role_function` crf ON crf.id = rc.fg_rm_crf_id 
		WHERE crf.`role_id` = roleid and c.club_id=clubid;
	END IF;
  	RETURN roleCount;

END
