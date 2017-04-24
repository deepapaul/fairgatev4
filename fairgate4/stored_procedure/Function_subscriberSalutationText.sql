DROP FUNCTION `subscriberSalutationText`//
CREATE DEFINER=`admin`@`localhost` FUNCTION `subscriberSalutationText`(subscriberId INT, clubId INT, clubSystemLang CHAR(2), clubCorresLang CHAR(2)) RETURNS text
BEGIN
	
	DECLARE salutationVal, firstName, lastName, genderVal, subscriberCorresLang, subscriberSystemLang, companyName, contactTitle TEXT DEFAULT ''; 
	DECLARE salutationValue TEXT DEFAULT '';
        DECLARE subscriber_Salutation_type TEXT DEFAULT 'NONE';
	
        IF(subscriberId <> 0) THEN
                SELECT s.first_name, s.last_name, UPPER(s.gender), UPPER(s.salutation), s.correspondance_lang, cbl.system_lang INTO firstName, lastName, genderVal, salutationVal, subscriberCorresLang, subscriberSystemLang  
                FROM fg_cn_subscriber s 
                LEFT JOIN fg_club cb ON cb.id = s.club_Id 
                LEFT JOIN fg_club_language cbl ON (((cbl.club_id = cb.id AND cb.federation_id <= 1) OR (cbl.club_id = cb.federation_id AND cb.federation_id >= 1)) AND cbl.`correspondance_lang`= s.`correspondance_lang`) 
                LEFT JOIN fg_club_language_settings cbls ON cbls.club_id = cb.id AND cbls.club_language_id =  cbl.id AND cbls.is_active=1 
                WHERE s.id = subscriberId LIMIT 1;

                SET subscriber_Salutation_type = salutationVal; 
                IF((salutationVal = '') OR  (genderVal = '') OR ((salutationVal <> '' AND  genderVal <> '') AND ((salutationVal = 'FORMAL' AND lastName = '') OR (salutationVal = 'INFORMAL' AND firstName = '')))) THEN
                        SET subscriber_Salutation_type = 'NONE';
                END IF;       
        END IF;

        IF(subscriberCorresLang = '' OR subscriberCorresLang IS NULL) THEN
		SET subscriberCorresLang = clubCorresLang;
                SET subscriberSystemLang = clubSystemLang;
	END IF;

	CASE subscriber_Salutation_type			
		WHEN 'FORMAL' THEN
                        BEGIN

                                SELECT (CASE WHEN genderVal = "MALE" THEN s1.male_formal_lang ELSE s1.female_formal_lang END) INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
                                WHERE (s1.lang = subscriberCorresLang AND s2.club_id = clubId);

                                IF(salutationValue IS NULL OR salutationValue = '') THEN
                                        SELECT (CASE WHEN genderVal = "MALE" THEN (CASE WHEN (s1.male_formal_lang IS NOT NULL AND s1.male_formal_lang <>'') THEN s1.male_formal_lang ELSE s2.male_formal END) ELSE (CASE WHEN (s1.female_formal_lang IS NOT NULL AND s1.female_formal_lang <>'') THEN s1.female_formal_lang ELSE s2.female_formal END) END) INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
                                        WHERE s1.lang = subscriberSystemLang AND s2.club_id = 1;			
                                END IF;		

                                SET salutationValue  = CONCAT(salutationValue, ' ', lastName);

                        END;
		
		WHEN 'INFORMAL' THEN
                        BEGIN
                                SELECT (CASE WHEN genderVal = "MALE" THEN s1.male_informal_lang ELSE s1.female_informal_lang END) INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
                                WHERE (s1.lang = subscriberCorresLang AND s2.club_id = clubId);

                                IF(salutationValue IS NULL OR salutationValue = '') THEN
                                        SELECT (CASE WHEN genderVal = "MALE" THEN (CASE WHEN (s1.male_informal_lang IS NOT NULL AND s1.male_informal_lang <>'') THEN s1.male_informal_lang ELSE s2.male_informal END) ELSE (CASE WHEN (s1.female_informal_lang IS NOT NULL AND s1.female_informal_lang <>'') THEN s1.female_informal_lang ELSE s2.female_informal END) END) INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
                                        WHERE s1.lang = subscriberSystemLang AND s2.club_id = 1;			
                                END IF;                 
                                SET salutationValue  = CONCAT(salutationValue, ' ', firstName);

                        END;

                WHEN 'NONE' THEN
                        BEGIN
                                SELECT s1.subscriber_lang INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
                                    WHERE (s1.lang = subscriberCorresLang AND s2.club_id = clubId);

                                IF(salutationValue IS NULL OR salutationValue = '') THEN
                                        SELECT s1.subscriber_lang INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
                                        WHERE s1.lang = subscriberSystemLang AND s2.club_id = 1;			
                                END IF; 
                        END;	    
	END CASE;

	RETURN salutationValue;
END