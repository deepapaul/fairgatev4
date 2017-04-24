DROP PROCEDURE IF EXISTS `handleAttributes`//
CREATE PROCEDURE `handleAttributes`(IN randomval TEXT, IN clubId INTEGER, IN clubType VARCHAR(15), IN corresAddress VARCHAR(15), IN invoiceAddress VARCHAR(15), IN clubDefaultLang CHAR(2))
BEGIN
	DECLARE catId, catType, catTitle, CatSort, catIsDeleted, catIsActive, catClubId, fieldRandom VARCHAR(255) DEFAULT "";
	DECLARE catLang, catTitleLang, currentAddressType, currentAddressId VARCHAR(255) DEFAULT "";
	DECLARE addressInsertId, insertCatId, fieldId, fieldCatId, fieldNewOld, fieldSetId,field_Name,fieldnameShort,inputType,isCompany,isPersonal,isSingleEdit,availabilitySubfed, isRequiredFedmemberSubfed, availabilityClub, isRequiredFedmemberClub, addresType, availabilityContact, isSetPrivacyItself, privacyContact, availabilityGroupadmin, isConfirmContact, isRequiredType, isConfirmTeamadmin, fieldSort, fieldIsDeleted, fieldIsActive, fieldClubId VARCHAR(255) DEFAULT "";
	DECLARE fieldLang, field_NameLang, field_NameShortLang VARCHAR(255) DEFAULT "";
	DECLARE cur_send_date DATETIME;
	DECLARE test, insertColumnQuery, valueColumnQuery, duplicateColumnQuery, insertAttributeQuery, deleteFieldIds, createFieldIds, updatedFieldIds, predefinedValue TEXT DEFAULT "";
	
	DECLARE newAddressType VARCHAR(255) DEFAULT "";
	DECLARE newCatId, addressId1, newFieldId, loopCount, requiredCount, fieldUid, isSystemField INTEGER DEFAULT 0;
	
	DECLARE isNewField, insertNewAddressRow BOOLEAN DEFAULT FALSE;	
	DECLARE record_not_found INTEGER DEFAULT 0;
-- 	Assign name for Mysql exception "General error: 1615 Prepared statement needs to be re-prepared"
	DECLARE mysql_prepare_exception CONDITION FOR 1615;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;
	
		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			ROLLBACK;
               	DELETE FROM fg_temp_attributeset WHERE `random` = randomval;
            	DELETE FROM fg_temp_attributeset_i18n WHERE `random` = randomval;
            	DELETE FROM fg_temp_attribute WHERE `random` = randomval;
            	DELETE FROM fg_temp_attribute_i18n WHERE `random` = randomval;
               	DELETE FROM fg_temp_attribute_required WHERE `random` = randomval;
		END;

	SET autocommit=0;
	SET SESSION tmp_table_size = 67108864;
	SET SESSION max_heap_table_size = 67108864;
	START TRANSACTION;
	
    
	SET SQL_SAFE_UPDATES = 0;
-- 	WARNING :::: MAKE SURE THAT ID OF ALL SYTEM FIELDS ARE CORRECT WHEN MOVING TO ANOTHER DATABASE
-- 	CURRENTLY WE HAVE SET FOUR CRON INSTANCES
	BLOCK_CATEGORY: BEGIN	
		DECLARE categoryCursor CURSOR FOR  SELECT id, `type`,title,sort_order, isDeleted FROM `fg_temp_attributeset` WHERE `random` = randomval AND club_id = clubId ;		
        OPEN categoryCursor;
			categoryCursorLoop: LOOP
				
				FETCH categoryCursor INTO catId, catType, catTitle, CatSort, catIsDeleted;
				IF record_not_found THEN
					SET record_not_found = 0;
					LEAVE categoryCursorLoop;
				END IF;	
				-- IF CATEGORY IS DELETED, DELETE IT FROM TABLE
				IF (catIsDeleted = 1) THEN
					IF(catType = 'old') THEN
						DELETE FROM fg_cm_attributeset WHERE id = catId AND club_id = clubId;
					END IF;
				ELSE
					-- IF CATEGORY IS NOT DELETED, SAVE CATEGORY DATA IF ANY
					SET @newCatId = catId;
					IF(catTitle IS NOT NULL OR CatSort IS NOT NULL) THEN
						INSERT INTO fg_cm_attributeset SET id = IF(catType ='old', catId, ''), club_id = IF ( catType ='new', clubId, NULL), title = IF ( (catType ='old' OR (catType ='new' AND catTitle IS NOT NULL) ), catTitle, title), sort_order = IF ( (catType ='old' OR (catType ='new' AND CatSort IS NOT NULL) ), CatSort, sort_order) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID( id ), title = IF(VALUES(title) IS NOT NULL,VALUES(title),title) , sort_order = IF(VALUES(sort_order) IS NOT NULL, VALUES(sort_order),sort_order), club_id = IF(VALUES(club_id) IS NOT NULL, VALUES(club_id),club_id) ;						
						SELECT LAST_INSERT_ID() INTO @newCatId;
					END IF;
					-- save category i18n values --
					INSERT INTO fg_cm_attributeset_i18n (id, lang, title_lang ) SELECT @newCatId, tas.lang, IF(tas.title_lang IS NOT NULL, tas.title_lang, cas.title_lang) FROM `fg_temp_attributeset_i18n` as tas LEFT JOIN fg_cm_attributeset_i18n as cas ON tas.id= cas.id AND tas.lang = cas.lang WHERE tas.id = catId ON DUPLICATE KEY UPDATE title_lang = VALUES(title_lang);

					-- save category field values --
					-- LOOP FOR FIELDS IN CATEGORY FROM THE TEMPAORARY TABLE FOR FIELDS
					BLOCK_FIELD: BEGIN
						DECLARE fieldCursor CURSOR FOR  SELECT * FROM `fg_temp_attribute` WHERE `random` = randomval AND cat_id = catId AND club_id = clubId;
                        OPEN fieldCursor;
							fieldCursorLoop: LOOP
								FETCH fieldCursor INTO fieldUid,fieldId, fieldCatId, fieldNewOld, fieldSetId,field_Name,fieldnameShort,inputType,isCompany,isPersonal,predefinedValue,isSingleEdit, isRequiredFedmemberSubfed, isRequiredFedmemberClub, addresType, availabilityContact, isSetPrivacyItself, privacyContact, availabilityGroupadmin, isConfirmContact, isRequiredType, isConfirmTeamadmin, fieldSort, fieldIsDeleted, fieldIsActive, fieldClubId, availabilitySubfed,availabilityClub,fieldRandom;
								IF record_not_found THEN
									-- SET test = CONCAT(test, 'record not found fieldId ',fieldId,' --');
									SET record_not_found = 0;
									LEAVE fieldCursorLoop;
								END IF;
								SET loopCount = 1;
								-- IF FIELD IS DELETED, DELETE IT FROM TABLE AND CORRESPODING COLUMN IN  MASTER CLUB/FEDERATION TABLES
								IF(fieldIsDeleted = 1) THEN
									SET deleteFieldIds = CONCAT(deleteFieldIds, IF(deleteFieldIds ='', '', ','), fieldId);
									-- IF ADDRESS FIELD, DELETE INVOICE FIELD ALSO, IF ANY
									IF(@newCatId = corresAddress) THEN
										SET fieldId  = (SELECT id FROM fg_cm_attribute WHERE address_id = fieldId AND club_id = clubId LIMIT 0,1);
										IF(fieldId IS NOT NULL) THEN
											SET deleteFieldIds = CONCAT(deleteFieldIds, IF(deleteFieldIds ='', '', ','), fieldId);
										END IF;
									END IF;
								ELSE
									IF(fieldNewOld = 'new') THEN
										SET insertCatId = @newCatId;
									ELSE
                                                                                SET isSystemField  = (SELECT isSystemField FROM fg_cm_attribute WHERE id = fieldId);                                                                                
										-- IF CATEGORY IS CHNAGED, UPDATE FIELD WITH THAT CATEGORY
										IF(fieldSetId IS NOT NUll) THEN
											SET insertCatId = fieldSetId;
										ELSE
											SET insertCatId = @newCatId;
										END IF;										
									END IF;
									-- IF THE FIELD IS IN ADDRESS CATEGORY 
									-- -- SPECIAL HANDLING FOR ADDRESS ACTEGORY FIELDS
									-- -- -- IF THE FIELD IS USED FOR BOTH 
									-- -- -- -- BY DEFAULT THE FIELD WILL BE USED FOR 'BOTH'/(INVOICE + CORRESPONDANCE) 
									-- -- -- -- WE HAVE TO KEEP TWO FIELDS IN THIS CASE 
									-- -- -- -- -- ONE FOR INVOICE (addres_type= 'invoice' and address_id = {corresponding field in correspondance category})
									-- -- -- -- -- OTHER ONE IS FOR CORRESPONDANCE(addres_type= 'both' and address_id = NULL)
									-- -- -- -- -- IF EXISTING ADDRESS FIELD AND USEED FOR CHANGE FROM 'INVOICE' ->'BOTH', KEEP THAT FIELD FOR 'INVOICE'(addres_type= 'invoice' and address_id = {corresponding field in correspondance category}) 
									-- -- -- -- -- -- AND CREATE ANOTHER ONE FOR CORRESPONDANCE(addres_type= 'BOTH' and address_id = NULL)
									-- -- -- -- -- IF EXISTING ADDRESS FIELD AND USEED FOR CHANGE FROM 'CORRESPONDANCE' ->'BOTH', KEEP THAT FIELD FOR 'CORRESPONDANCE'(addres_type= 'both' and address_id = null) 
									-- -- -- -- -- -- AND CREATE ANOTHER ONE FOR INVOICE(addres_type= 'BOTH' and address_id = {corresponding field in correspondance category})
									
									-- -- -- IF USED FOR INVOICE
									-- -- -- -- ONLY ONE FIELD (addres_type= 'invoice' and address_id = NULL)
									-- -- -- -- IF EXISTING ADDRESS FIELD AND USEED FOR CHANGE FROM 'BOTH' ->INVOICE, KEEP 'INVOICE ' FIELD AND DELETE THE OTHER ONE
									-- -- -- -- IF EXISTING ADDRESS FIELD AND USEED FOR CHANGE FROM 'CORRESPONDANCE' ->INVOICE, JUST CHNAGE THE ADDRESS TYPE TO 'INVOICE'
									-- -- -- IF USED FOR CORRESPONDANCE
									-- -- -- -- ONLY ONE FIELD (addres_type= 'correspondance' and address_id = NULL)								
									
									IF(	insertCatId = corresAddress) THEN
										-- SET test = CONCAT(test, 'Address Category--');
										-- IF THE FIELD IS NEW OR AN EXISTING FIELD IN ANOTHER CATEGORY CHANGED TO ADDRESS CATEGORY
										IF((fieldNewOld = 'new') OR (fieldSetId IS NOT NUll AND fieldNewOld = 'old' AND fieldSetId = corresAddress AND @newCatId<>fieldSetId)) THEN
											-- IF THE ADDRESS TYPE IS NULL, SET IT TO BOTH(INVOICE+CORRESPONDANCE)
											IF(addresType IS NULL) THEN
												-- SET test = CONCAT(test, 'New field address type null --');
												SET addresType='both';
												SET currentAddressType = 'correspondance';
											ELSE
												-- SET test = CONCAT(test, 'New field address type not null --');
												IF(addresType='both') THEN
													SET currentAddressType = 'correspondance';
												ELSE
													SET currentAddressType = addresType;
												END IF;										
											END IF;
											-- SET test = CONCAT(test, 'currentAddressType =',currentAddressType,' --');
										ELSE
											SELECT addres_type INTO currentAddressType FROM fg_cm_attribute WHERE id = fieldId;
											-- SET test = CONCAT(test, 'Old field address type null --', 'currentAddressType =',currentAddressType,' --');
										END IF;
										
										IF(addresType IS NOT NULL) THEN
											BEGIN												
												-- SET test = CONCAT(test, 'Address type is not null -- addresType =',addresType,'--');												
												CASE addresType
													WHEN 'correspondance' THEN
													BEGIN														
														IF(fieldNewOld = 'old' AND currentAddressType = 'both') THEN
															-- SET test = CONCAT(test, 'Address type is correspondance --');
															SET currentAddressId  = (SELECT id FROM fg_cm_attribute WHERE address_id = fieldId AND club_id = clubId LIMIT 0,1);
															IF(currentAddressId IS NOT NULL) THEN
																SET deleteFieldIds = CONCAT(deleteFieldIds, IF(deleteFieldIds ='', '', ','), currentAddressId);
															END IF;
														END IF;
														SET insertCatId = corresAddress;
													END;
													WHEN 'invoice' THEN
													BEGIN
														IF(fieldNewOld = 'old' AND currentAddressType = 'both') THEN
															-- SET test = CONCAT(test, 'Address type is invoice --');
															SET deleteFieldIds = CONCAT(deleteFieldIds, IF(deleteFieldIds ='', '', ','), fieldId);
															SET currentAddressId  = (SELECT id FROM fg_cm_attribute WHERE address_id = fieldId AND club_id = clubId LIMIT 0,1);
															IF(currentAddressId IS NOT NULL) THEN
																SET fieldId = currentAddressId;	
															END IF;
														END IF;
														SET insertCatId = invoiceAddress;
													END;
													WHEN 'both' THEN
													BEGIN
														IF(currentAddressType ='invoice') THEN
															-- SET test = CONCAT(test, 'Address type is both  currentAddressType is invoice--');
															SET insertCatId = invoiceAddress;
															SET addresType = 'invoice';
															SET newAddressType = 'both';
															SET loopCount = 2;
														ELSE 
															IF(currentAddressType ='correspondance') THEN
																-- SET test = CONCAT(test, 'Address type is both  currentAddressType is correspondance--');
																SET newAddressType = 'invoice';
																SET loopCount = 2;
																SET insertCatId = corresAddress;
															END IF;
														END IF;
													END;
												END CASE;
												
											END;
										ELSE
											IF (fieldNewOld = 'old') THEN
												CASE currentAddressType
													WHEN 'both' THEN
													BEGIN
														SET loopCount = 2;
														SELECT id INTO currentAddressId FROM fg_cm_attribute WHERE address_id = fieldId LIMIT 0,1;
													END;
													WHEN 'invoice' THEN
													BEGIN
														SET insertCatId = invoiceAddress;
													END;
													ELSE
														BEGIN
															-- EMPTY --
														END;
												END CASE;
											END IF;
										END IF;
									ELSE
										IF(fieldNewOld = 'old') THEN
											SET addresType = 'both';
											SET currentAddressId  = (SELECT id FROM fg_cm_attribute WHERE address_id = fieldId LIMIT 0,1);
											IF(currentAddressId IS NOT NULL) THEN
												SET deleteFieldIds = CONCAT(deleteFieldIds, IF(deleteFieldIds ='', '', ','), currentAddressId);
											END IF;
										END IF;
									END IF;
									SET addressInsertId = fieldId;
									-- SET test = CONCAT(test, 'fieldId = ',fieldId,'--');
									-- IF ADDRESS TYPE IS 'BOTH' loopCount WILL BE 2, TO HANDLE TWO LINKED FIELD IN ADDRESS CATEGORY
									WHILE loopCount > 0 DO
										BEGIN
											-- SET test = CONCAT(test, 'addressInsertId = ',addressInsertId,'--');
											-- SET test = CONCAT(test, 'insertCatId = ',insertCatId,'--');
											-- SET test = CONCAT(test, 'clubId = ',clubId,'--');
                                                                                        
                                                                                        SET insertColumnQuery =  'id, attributeset_id';
                                                                                        SET valueColumnQuery = CONCAT(IF(fieldNewOld ='old', addressInsertId, "''"), ',', insertCatId);
                                                                                        SET duplicateColumnQuery = CONCAT('id = LAST_INSERT_ID( id ), attributeset_id = VALUES(attributeset_id)');
                                                                                        
                                                                                        IF (field_Name IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', fieldname');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ", '", field_Name, "'");
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', fieldname = VALUES(fieldname)');
                                                                                        END IF;
                                                                                        IF (fieldnameShort IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', fieldname_short');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ", '", fieldnameShort, "'");
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', fieldname_short = VALUES(fieldname_short)');
                                                                                        END IF;
                                                                                        IF (inputType IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', input_type');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ", '", inputType, "'");
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', input_type = VALUES(input_type)');
                                                                                        END IF;
                                                                                        IF (isCompany IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', is_company');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ', ', isCompany);
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', is_company = VALUES(is_company)');
                                                                                        END IF;
                                                                                        IF (isPersonal IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', is_personal');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ', ', isPersonal);
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', is_personal = VALUES(is_personal)');
                                                                                        END IF;
                                                                                        IF (predefinedValue IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', predefined_value');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ", '", predefinedValue, "'");
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', predefined_value = VALUES(predefined_value)');
                                                                                        END IF;
                                                                                        IF (isSingleEdit IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', is_single_edit');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ', ', isSingleEdit);
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', is_single_edit = VALUES(is_single_edit)');
                                                                                        END IF;
                                                                                        IF (availabilitySubfed IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', availability_sub_fed');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ", '", availabilitySubfed, "'");
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', availability_sub_fed = VALUES(availability_sub_fed)');
                                                                                        END IF;
                                                                                        IF (availabilityClub IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', availability_club');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ", '", availabilityClub, "'");
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', availability_club = VALUES(availability_club)');
                                                                                        END IF;
                                                                                        IF (addresType IS NOT NULL) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', addres_type');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ", '", addresType, "'");
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', addres_type = VALUES(addres_type)');
                                                                                        END IF;

                                                                                        IF ((fieldNewOld ='new') OR (addressInsertId = '') ) THEN
                                                                                            SET insertColumnQuery = CONCAT(insertColumnQuery, ', club_id');
                                                                                            SET valueColumnQuery = CONCAT(valueColumnQuery, ', ', clubId);
                                                                                            SET duplicateColumnQuery = CONCAT(duplicateColumnQuery, ', club_id = VALUES(club_id)');
                                                                                        END IF;
                                                                                        SET @insertAttributeQuery = CONCAT('INSERT INTO fg_cm_attribute(',insertColumnQuery,') VALUES(', valueColumnQuery, ') ON DUPLICATE KEY UPDATE ', duplicateColumnQuery);  
                                                                                        PREPARE stmt FROM @insertAttributeQuery;
                                                                                        EXECUTE stmt;
                                                                                        DEALLOCATE PREPARE stmt;

											SELECT LAST_INSERT_ID() INTO @newFieldId;
											/* update fed profile status*/											
											IF(availabilitySubfed IS NOT NULL OR availabilityClub) THEN
												UPDATE fg_cm_attribute SET `fed_profile_status` = IF( (`availability_sub_fed` != 'not_available' OR `availability_club` != 'not_available'), 1,0 ) WHERE id = @newFieldId AND club_id = clubId;
											END IF;
											
											IF(fieldNewOld = 'new') THEN
												SET isNewField = true;
												SET createFieldIds = CONCAT(createFieldIds, IF(createFieldIds ='', '', ','), @newFieldId);
											END IF;
											IF(fieldNewOld = 'old' AND inputType IS NOT NULL AND loopCount = 1 AND insertNewAddressRow = FALSE) THEN
												SET updatedFieldIds = CONCAT(updatedFieldIds, IF(updatedFieldIds ='', '', ','), @newFieldId);
											END IF;
											-- save field i18n values --
											IF (insertNewAddressRow = FALSE ) THEN
												-- SET test = CONCAT(test, 'insertNewAddressRow = FALSE--');
												-- SET test = CONCAT(test, 'newFieldId = ',@newFieldId,'--');
												INSERT INTO fg_cm_attribute_i18n (id, lang, fieldname_lang, fieldname_short_lang ) SELECT @newFieldId, ta.lang, IF(ta.fieldname_lang IS NOT NULL, ta.fieldname_lang, ca.fieldname_lang),IF(ta.fieldname_short_lang IS NOT NULL, ta.fieldname_short_lang, ca.fieldname_short_lang) FROM `fg_temp_attribute_i18n` as ta LEFT JOIN fg_cm_attribute_i18n as ca ON ta.id= ca.id AND ta.lang = ca.lang WHERE ta.id = fieldId ON DUPLICATE KEY UPDATE fieldname_lang = VALUES(fieldname_lang),fieldname_short_lang = VALUES(fieldname_short_lang); 										
												-- save required fields --
												SET requiredCount= 0;
												IF(fieldNewOld ='old') THEN
													SELECT count(membership_id) INTO requiredCount FROM fg_temp_attribute_required WHERE attribute_id = fieldId AND club_id = clubId;
													IF(requiredCount IS NOT NULL AND requiredCount>0) THEN
														DELETE FROM fg_cm_attribute_required WHERE attribute_id = @newFieldId AND club_id = clubId;
													END IF;
												END IF;
												INSERT INTO fg_cm_attribute_required(attribute_id, club_id, membership_id) SELECT @newFieldId, clubId, tr.membership_id FROM fg_temp_attribute_required AS tr WHERE tr.attribute_id = fieldId AND tr.club_id = clubId ON DUPLICATE KEY UPDATE membership_id = VALUES(membership_id);
											ELSE
												-- SET test = CONCAT(test, 'insertNewAddressRow = TRUE--');
												SET createFieldIds = CONCAT(createFieldIds, IF(createFieldIds ='', '', ','), @newFieldId);
												INSERT INTO fg_cm_attribute_i18n (id, lang, fieldname_lang, fieldname_short_lang ) SELECT @newFieldId, ta.lang, ta.fieldname_lang, ta.fieldname_short_lang FROM `fg_cm_attribute_i18n` AS ta WHERE ta.id = @addressId1;								
												-- save required fields --
												INSERT INTO fg_cm_attribute_required(attribute_id, club_id, membership_id) SELECT @newFieldId, tr.club_id, tr.membership_id FROM fg_cm_attribute_required AS tr WHERE tr.attribute_id = @addressId1 AND tr.club_id = clubId;
											END IF;
											
		-- availabilityContact, isSetPrivacyItself, privacyContact, availabilityGroupadmin, isConfirmContact, isRequiredType, isConfirmTeamadmin, fieldSort, fieldIsDeleted, fieldIsActive, fieldClubId									
											-- save contact profile - club attributes --
                                                                                        -- is_active : If federation set a system field required for subfederation or club, make sure the visbility of that field is on in subfed /club 
											INSERT INTO fg_cm_club_attribute 
											SET attribute_id = @newFieldId,
											club_id = clubId,
											availability_contact = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND availabilityContact IS NOT NULL) ), availabilityContact, availability_contact) , 
											is_set_privacy_itself = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND isSetPrivacyItself IS NOT NULL) ), isSetPrivacyItself, is_set_privacy_itself), 
											privacy_contact = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND privacyContact IS NOT NULL) ), privacyContact, privacy_contact), 
											availability_groupadmin = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND availabilityGroupadmin IS NOT NULL) ), availabilityGroupadmin, availability_groupadmin), 
											is_confirm_contact = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND isConfirmContact IS NOT NULL) ), isConfirmContact, is_confirm_contact),
											is_required_type = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND isRequiredType IS NOT NULL) ), isRequiredType, is_required_type),
											is_confirm_teamadmin = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND isConfirmTeamadmin IS NOT NULL) ), isConfirmTeamadmin, is_confirm_teamadmin), 
											sort_order = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND fieldSort IS NOT NULL) ), fieldSort, sort_order),
                                                                                        is_required_fedmember_subfed = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND isRequiredFedmemberSubfed IS NOT NULL) ), isRequiredFedmemberSubfed, is_required_fedmember_subfed),    
                                                                                        is_required_fedmember_club = IF ( (fieldNewOld ='old' OR (fieldNewOld ='new' AND isRequiredFedmemberClub IS NOT NULL) ), isRequiredFedmemberClub, is_required_fedmember_club), 
                                                                                        is_active = IF (  ((fieldNewOld ='old' AND (isSystemField =0 OR clubType ='standard_club' OR  (isSystemField =1 AND clubType ='federation') OR (isSystemField =1 AND clubType ='sub_federation' AND is_required_fedmember_subfed=0)  OR (isSystemField =1 AND (clubType !='federation' AND clubType !='sub_federation') AND is_required_fedmember_club=0) ) ) OR (fieldNewOld ='new' AND fieldIsActive IS NOT NULL)), fieldIsActive, is_active)     
											ON DUPLICATE KEY UPDATE 
											availability_contact = IF(VALUES(availability_contact)IS NOT NULL, VALUES(availability_contact), availability_contact),
											is_set_privacy_itself = IF(VALUES(is_set_privacy_itself)IS NOT NULL, VALUES(is_set_privacy_itself), is_set_privacy_itself),
											privacy_contact =IF(VALUES(privacy_contact)IS NOT NULL, VALUES(privacy_contact), privacy_contact),
											availability_groupadmin = IF(VALUES(availability_groupadmin)IS NOT NULL, VALUES(availability_groupadmin), availability_groupadmin),
											is_confirm_contact = IF(VALUES(is_confirm_contact)IS NOT NULL, VALUES(is_confirm_contact), is_confirm_contact),
											is_required_type = IF(VALUES(is_required_type)IS NOT NULL, VALUES(is_required_type), is_required_type),
											is_confirm_teamadmin = IF(VALUES(is_confirm_teamadmin)IS NOT NULL, VALUES(is_confirm_teamadmin), is_confirm_teamadmin),
											sort_order = IF(VALUES(sort_order)IS NOT NULL, VALUES(sort_order), sort_order),
                                                                                        is_required_fedmember_subfed = IF(VALUES(is_required_fedmember_subfed)IS NOT NULL, VALUES(is_required_fedmember_subfed), is_required_fedmember_subfed),
                                                                                        is_required_fedmember_club = IF(VALUES(is_required_fedmember_club)IS NOT NULL, VALUES(is_required_fedmember_club), is_required_fedmember_club),
                                                                                        is_active = IF(VALUES(is_active)IS NOT NULL, VALUES(is_active), is_active);	
											/* update profile status*/
                                                                                        /*
											IF(isSetPrivacyItself IS NOT NULL OR privacyContact IS NOT NULL OR availabilityContact IS NOT NULL OR  availabilityGroupadmin IS NOT NULL OR isChangableContact IS NOT NULL OR isChangableTeamadmin IS NOT NULL) THEN
												UPDATE fg_cm_club_attribute SET `profile_status` = IF( (`is_set_privacy_itself` =1 AND `privacy_contact` ='community' AND `is_visible_contact` = 1 AND `is_visible_teamadmin` = 1 AND `is_changable_contact` =1 AND `is_changable_teamadmin` = 1), 1,0 ) WHERE attribute_id = @newFieldId AND club_id = clubId;
											END IF;
                                                                                        */
											-- IF A NEW FIELD IS CREATED for afederation/sub federation, UPDATE entries in fg_cm_club_attrribute for sub level clubs
											IF(isNewField = TRUE AND (clubType ='federation' OR clubType ='sub_federation')) THEN
                                                                                            INSERT INTO fg_cm_club_attribute(attribute_id, sort_order, club_id, is_required_fedmember_subfed, is_required_fedmember_club, is_active) SELECT @newFieldId, 999, hi.id, IF ( isRequiredFedmemberSubfed IS NOT NULL, isRequiredFedmemberSubfed, 0),IF ( isRequiredFedmemberClub IS NOT NULL, isRequiredFedmemberClub, 0),IF ( fieldIsActive IS NOT NULL, fieldIsActive, 1) FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := clubId,@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club hi ON hi.id = ho.id;
											END IF;
                                                                                        -- FAIR-683, FAIR-1055 --
											IF(clubType ='federation') THEN
                                                                                            UPDATE fg_cm_club_attribute AS cca, fg_cm_club_attribute AS ccab, fg_cm_attribute AS ca, fg_club AS c 
                                                                                            SET cca.is_required_fedmember_subfed = ccab.is_required_fedmember_subfed, 
                                                                                            cca.is_required_fedmember_club = ccab.is_required_fedmember_club, 
                                                                                            cca.is_active = (CASE WHEN ca.is_system_field =1 AND c.club_type ='sub_federation' AND ccab.is_required_fedmember_subfed = 1 THEN 1 WHEN ca.is_system_field =1 AND c.club_type !='sub_federation' AND ccab.is_required_fedmember_club = 1 THEN 1 WHEN ca.is_system_field =1 THEN cca.is_active ELSE ccab.is_active END) 
                                                                                            WHERE ccab.attribute_id=@newFieldId AND cca.attribute_id = ccab.attribute_id AND cca.club_id = c.id AND ca.id=ccab.attribute_id AND ccab.club_id = clubId AND cca.club_id IN(SELECT hi.id as ids FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := clubId,@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club hi ON hi.id = ho.id);
											END IF;
											IF(clubType ='sub_federation') THEN
                                                                                            UPDATE fg_cm_club_attribute AS cca, fg_cm_club_attribute AS ccab, fg_cm_attribute AS ca SET cca.is_required_fedmember_subfed = ccab.is_required_fedmember_subfed, cca.is_required_fedmember_club = ccab.is_required_fedmember_club, cca.is_active = ccab.is_active 
                                                                                            WHERE ccab.attribute_id=@newFieldId AND cca.attribute_id = ccab.attribute_id AND ccab.club_id = clubId AND ca.id= ccab.attribute_id AND ca.club_id = clubId AND cca.club_id IN(SELECT hi.id as ids FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := clubId,@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club hi ON hi.id = ho.id);
											END IF;

											-- SET test = CONCAT(test, 'loopCount= ',loopCount,'--');
											-- IF ADDRESS CATEGORY FIELD, HANLE TWO FIELDS IF ANY
											IF(insertCatId = corresAddress OR insertCatId = invoiceAddress) THEN
												IF(addresType IS NOT NULL) THEN
													IF(loopCount = 2) THEN
														IF(fieldNewOld = 'new') THEN
															SET createFieldIds = CONCAT(createFieldIds, IF(createFieldIds ='', '', ','), @newFieldId);	
														END IF;
														SET insertNewAddressRow = TRUE;
														-- SET test = CONCAT(test, 'insertNewAddressRow= ',insertNewAddressRow,'--');
														-- SET test = CONCAT(test, 'New Address type= ',newAddressType,'--');
														SET @addressId1 = @newFieldId;
														SET addresType =  newAddressType;
														IF( addresType ='both' OR addresType ='correspondance' ) THEN
															SET insertCatId = corresAddress;
														ELSE
															SET insertCatId = invoiceAddress;
														END IF;													
														SET addressInsertId = '';
														-- SET test = CONCAT(test, 'insertCatId= ',insertCatId,'--');
														-- SET test = CONCAT(test, 'addresType= ',addresType,'--');
														IF(fieldNewOld ='old') THEN
															SELECT at.fieldname, at.fieldname_short, at.input_type, at.is_company, at.is_personal, at.predefined_value, at.is_single_edit, at.availability_sub_fed, at.is_required_fedmember_subfed, at.availability_club, at.is_required_fedmember_club, at.is_active,
															atc.is_visible_contact, atc.is_set_privacy_itself, atc.privacy_contact, atc.is_visible_teamadmin, atc.is_changable_contact, atc.is_changable_teamadmin, atc.is_confirm_contact, atc.is_required_type, atc.is_confirm_teamadmin, atc.sort_order  
															INTO field_Name, fieldnameShort, inputType, isCompany, isPersonal, predefinedValue, isSingleEdit, availabilitySubfed, isRequiredFedmemberSubfed, availabilityClub, isRequiredFedmemberClub, fieldIsActive, availabilityContact, isSetPrivacyItself, privacyContact, availabilityGroupadmin, isConfirmContact, isRequiredType, isConfirmTeamadmin, fieldSort FROM fg_cm_attribute AS at INNER JOIN fg_cm_club_attribute AS atc ON at.id = atc.attribute_id WHERE at.id=@newFieldId AND atc.club_id =clubId;
														END IF;
													ELSE
														-- IF A NEW FIELD IS INSERTED IN ADDRESS CATEGORY, THEN UPDATE address_id column in fg_cm_attribute
														IF(addressInsertId ='') THEN
															CASE newAddressType
																WHEN 'both' THEN
																BEGIN
																	UPDATE fg_cm_attribute SET address_id =@newFieldId WHERE id = @addressId1;
																END;
																WHEN 'invoice' THEN
																BEGIN
																	UPDATE fg_cm_attribute SET address_id =@addressId1 WHERE id = @newFieldId;
																END;
																ELSE
																BEGIN
																	-- EMPTY --
																END;
															END CASE;
															SET insertNewAddressRow = FALSE;
														END IF;
													END IF;		
												ELSE
													IF (fieldNewOld = 'old' AND currentAddressType = 'both') THEN
														IF(loopCount = 2) THEN
															IF(inputType IS NOT NULL) THEN
																SET updatedFieldIds = CONCAT(updatedFieldIds, IF(updatedFieldIds ='', '', ','), @newFieldId);
															END IF;
															SET addressInsertId = currentAddressId;
															SET insertCatId = invoiceAddress;
														END IF;
													END IF;
													
												END IF;
											END IF;
											SET loopCount = loopCount - 1;
											-- SET test = CONCAT(test, '@newFieldId= ',@newFieldId,'-- while loop ends');
										END;
									END WHILE;
								END IF;
								SET addresType = NULL;
								SET fieldSetId = NULL;
								SET currentAddressType = NULL;
								SET @addressId1 = NULL;
								SET currentAddressId = NULL;
								SET isNewField = FALSE;
                                                                SET isSystemField = 0;                                                                
							END LOOP fieldCursorLoop;
						CLOSE fieldCursor;
					END BLOCK_FIELD;
				END IF;
			END LOOP categoryCursorLoop;
		CLOSE categoryCursor;
	END BLOCK_CATEGORY;
	-- DELETE FIELD FROM fg_cm_attribute + master club/fderation
	IF(deleteFieldIds <> '')THEN
		-- SET test = CONCAT(test, 'deleteFieldIds= ',deleteFieldIds,'--');
		call alterMasterClubTableFields(deleteFieldIds, clubId, clubType, 'delete');
	END IF;
	-- CREATE FIELD IN master club/fderation
	IF(createFieldIds <> '')THEN
		-- SET test = CONCAT(test, 'createFieldIds= ',createFieldIds,'--');
		call alterMasterClubTableFields(createFieldIds, clubId, clubType, 'create');
		SET test = CONCAT(test, "call alterMasterClubTableFields(",createFieldIds,',',clubId,',',clubType,',', "'create');");
	END IF;	
	-- UPDATE FIELD IN master club/fderation IF INPUT_TYPE CHANGED
	IF(updatedFieldIds <> '')THEN
		-- SET test = CONCAT(test, 'updatedFieldIds= ',updatedFieldIds,'--');
		-- SET test = CONCAT(test, "call alterMasterClubTableFields(",updatedFieldIds,',',clubId,',',clubType,',', "'update');");
		call alterMasterClubTableFields(updatedFieldIds, clubId, clubType, 'update');
	END IF;	
        
        DELETE FROM fg_temp_attributeset WHERE `random` = randomval;
        DELETE FROM fg_temp_attributeset_i18n WHERE `random` = randomval;
        DELETE FROM fg_temp_attribute WHERE `random` = randomval;
        DELETE FROM fg_temp_attribute_i18n WHERE `random` = randomval;
        DELETE FROM fg_temp_attribute_required WHERE `random` = randomval;	
	UPDATE fg_cm_attribute A INNER JOIN fg_cm_attribute_i18n AI ON A.id=AI.id AND AI.lang=clubDefaultLang SET A.fieldname=AI.fieldname_lang,A.fieldname_short=AI.fieldname_lang WHERE A.club_id=clubId AND AI.fieldname_lang IS NOT NULL AND AI.fieldname_lang!='';
        UPDATE fg_cm_attributeset A INNER JOIN fg_cm_attributeset_i18n ASI ON A.id=ASI.id AND ASI.lang=clubDefaultLang SET A.title=ASI.title_lang WHERE A.club_id=clubId AND ASI.title_lang IS NOT NULL AND ASI.title_lang!='';
    COMMIT;
    -- SELECT test;
END
