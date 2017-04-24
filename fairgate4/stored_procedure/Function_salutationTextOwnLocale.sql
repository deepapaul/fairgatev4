DROP FUNCTION IF EXISTS `salutationTextOwnLocale`//
CREATE FUNCTION `salutationTextOwnLocale`(contactId INT, clubId INT, clubSystemLang CHAR(2), clubCorresLang CHAR(2), contactOwnSystemLang VARCHAR(8)) RETURNS text
BEGIN
	
	DECLARE salutation, firstName, lastName, gender, contactCorresLang, companyName, contactTitle TEXT DEFAULT ''; 
        DECLARE contactLangCorrespondance, contactLangSystem TEXT DEFAULT NULL;
	DECLARE contact_salutation_type, salutationValue TEXT DEFAULT '';
	DECLARE isCompany, hasMainContact  INTEGER DEFAULT 0;
	DECLARE isDefaultLangChecked BOOLEAN DEFAULT false;
	
	SET contact_salutation_type = 'FORMAL'; 

	SELECT cmc.is_company, cmc.has_main_contact, ms.`1`, ms.`2`, ms.`23`, ms.`72`, ms.`515`, ms.`9`, ms.`70`, cbl.`correspondance_lang`, cbl.`system_lang` 
        INTO isCompany, hasMainContact, salutation, firstName, lastName, gender, contactCorresLang, companyName, contactTitle, contactLangCorrespondance, contactLangSystem 
	FROM fg_cm_contact AS cmc 
        INNER JOIN master_system AS ms ON ms.fed_contact_id = cmc.fed_contact_id 
	LEFT JOIN fg_club cb ON cb.id = clubId 
	LEFT JOIN fg_club_language cbl ON ( ((cbl.club_id = cb.id AND cb.federation_id <=1) OR (cbl.club_id = cb.federation_id AND cb.federation_id >= 1) )AND cbl.`correspondance_lang`=ms.`515`) 
	LEFT JOIN fg_club_language_settings cbls ON cbls.club_id = cb.id AND cbls.club_language_id =  cbl.id AND cbls.is_active=1 
        WHERE cmc.id = contactId LIMIT 1;

        CASE
            WHEN (contactOwnSystemLang = 'default' AND contactLangCorrespondance IS NOT NULL) THEN
                SET contactOwnSystemLang = contactLangSystem;
            WHEN (contactOwnSystemLang= '' OR (contactOwnSystemLang = 'default' AND contactLangCorrespondance IS NULL)) THEN
                SET contactOwnSystemLang = clubSystemLang;   
            ELSE 
                BEGIN END;  
        END CASE;
        IF (contactLangCorrespondance IS NULL) THEN
            SET contactCorresLang = clubCorresLang;
        END IF;

        SET gender = UPPER(gender);	
	
	IF( salutation <> '') THEN
		SET contact_salutation_type = UPPER(salutation);		
	END IF;
        
	IF( isCompany = 1 AND hasMainContact = 0) THEN
		SET contact_salutation_type = 'NO_MAIN_CONTACT';
	END IF;

	CASE  contact_salutation_type		
		WHEN 'NO_MAIN_CONTACT' THEN
		BEGIN
			SELECT s1.company_no_maincontact_lang INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
			WHERE (s1.lang = contactCorresLang AND s2.club_id = clubId);

			IF(salutationValue IS NULL OR salutationValue = '') THEN
				SELECT s1.company_no_maincontact_lang INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id WHERE s1.lang = contactOwnSystemLang AND s2.club_id = 1;
			END IF;
		END;
		
		WHEN 'FORMAL' THEN
		BEGIN
			SELECT (CASE WHEN gender = "MALE" THEN s1.male_formal_lang ELSE s1.female_formal_lang END) INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
			WHERE (s1.lang = contactCorresLang AND s2.club_id = clubId);

			IF(salutationValue IS NULL OR salutationValue = '') THEN
				SELECT (CASE WHEN gender = "MALE" THEN (CASE WHEN (s1.male_formal_lang IS NOT NULL AND s1.male_formal_lang <>'') THEN s1.male_formal_lang ELSE s2.male_formal END) ELSE (CASE WHEN (s1.female_formal_lang IS NOT NULL AND s1.female_formal_lang <>'') THEN s1.female_formal_lang ELSE s2.female_formal END) END) INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
				WHERE s1.lang = contactOwnSystemLang AND s2.club_id = 1;			
			END IF;

			IF(contactTitle IS NOT NULL AND contactTitle <>'') THEN
                            SET salutationValue  = CONCAT(salutationValue, ' ', contactTitle);
			END IF;
			SET salutationValue  = CONCAT(salutationValue, ' ', lastName);
			
		END;
		
		WHEN 'INFORMAL' THEN
		BEGIN
                        SELECT (CASE WHEN gender = "MALE" THEN s1.male_informal_lang ELSE s1.female_informal_lang END) INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
			WHERE (s1.lang = contactCorresLang AND s2.club_id = clubId);
            
			IF(salutationValue IS NULL OR salutationValue = '') THEN
				SELECT (CASE WHEN gender = "MALE" THEN (CASE WHEN (s1.male_informal_lang IS NOT NULL AND s1.male_informal_lang <>'') THEN s1.male_informal_lang ELSE s2.male_informal END) ELSE (CASE WHEN (s1.female_informal_lang IS NOT NULL AND s1.female_informal_lang <>'') THEN s1.female_informal_lang ELSE s2.female_informal END) END) INTO salutationValue FROM fg_club_salutation_settings_i18n AS s1 LEFT JOIN fg_club_salutation_settings AS s2 ON s1.id = s2.id 
				WHERE s1.lang = contactOwnSystemLang AND s2.club_id = 1;			
			END IF;                    
                        SET salutationValue  = CONCAT(salutationValue, ' ', firstName);
                        
		END;		
	END CASE;
	RETURN salutationValue;
END