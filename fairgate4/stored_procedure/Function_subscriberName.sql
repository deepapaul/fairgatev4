DROP FUNCTION IF EXISTS `subscriberName`//
CREATE FUNCTION `subscriberName`(subscriberId INT, emailFlag INT) RETURNS text
BEGIN
	DECLARE subscriberName, subscriberNameWithoutEmail TEXT;
	SELECT (CASE 
                    WHEN (s.first_name IS NOT NULL AND s.first_name!='' AND s.last_name IS NOT NULL AND s.last_name!='') THEN CONCAT(s.`last_name`,' ',s.`first_name`,' (',s.`email`,')')
		    WHEN (s.first_name IS NOT NULL AND s.first_name!='' AND (s.last_name IS NULL OR s.last_name='')) THEN CONCAT(s.`first_name`,' (',s.`email`,')')
		    WHEN (s.last_name IS NOT NULL AND s.last_name!='' AND (s.first_name IS NULL OR s.first_name='')) THEN CONCAT(s.`last_name`,' (',s.`email`,')')
		    ELSE s.`email` 
                END), 
                (CASE 
                    WHEN (s.first_name IS NOT NULL AND s.first_name!='' AND s.last_name IS NOT NULL AND s.last_name!='') THEN CONCAT(s.`last_name`,' ',s.`first_name`)
		    WHEN (s.first_name IS NOT NULL AND s.first_name!='' AND (s.last_name IS NULL OR s.last_name='')) THEN s.`first_name` 
		    WHEN (s.last_name IS NOT NULL AND s.last_name!='' AND (s.first_name IS NULL OR s.first_name='')) THEN s.`last_name` 
		    ELSE '' 
                END) INTO subscriberName, subscriberNameWithoutEmail 
        FROM `fg_cn_subscriber` s WHERE s.`id`=subscriberId;
        IF (emailFlag != 1) THEN
            SET subscriberName = subscriberNameWithoutEmail;   
        END IF;    

	RETURN subscriberName;
END
