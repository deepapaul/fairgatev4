DROP procedure IF EXISTS `V4createClub`//
CREATE PROCEDURE `V4createClub`(IN parentClubId INTEGER, IN federationId INTEGER, IN urlIdentifier TEXT, IN clubTitle TEXT, IN clubType TEXT, IN website TEXT, IN hasSubfederation INTEGER, IN clubMembershipAvailable tinyint,IN fedMembershipMandatory tinyint,IN assignFedmembershipWithApplication tinyint,IN addExistingFedMemberClub tinyint,IN fedAdminAccess tinyint)
BEGIN
	DECLARE clubId VARCHAR(255) DEFAULT "";
	DECLARE record_not_found INTEGER DEFAULT 0;
	DECLARE signature VARCHAR(255) DEFAULT "";
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;
	
	SET autocommit=0;
	START TRANSACTION;
	
	INSERT INTO `fg_club_address` (`company`, `street`, `pobox`, `city`, `zipcode`, `state`, `country`, `language`, `created_at`, `updated_at`, `co`) VALUES
(clubTitle, '', '', '', '', '', '', 'de', '', '', NULL);

	SELECT LAST_INSERT_ID() INTO @corresAddressId;

		

		INSERT INTO fg_club 
			SET id = '', 
			parent_club_id = parentClubId,
                        federation_id = federationId,

                        sub_federation_id = IF(clubType = 'sub_federation_club', parentClubId, 0),
			is_federation = IF(clubType ='federation', 1, 0),
			is_sub_federation = IF(clubType ='sub_federation', 1, 0),
			club_type = clubType,
			subfed_level = IF(clubType ='sub_federation', 1, 0),
			title = clubTitle,
			url_identifier = urlIdentifier,
                        website = website,
			`year` = '',
			correspondence_id = @corresAddressId,
			billing_id = @corresAddressId,
			`is_active` =1,
			responsible_contact_id =1,
			first_contact_type_id =1,
			assignment_country =1,
			assignment_state=2,
			assignment_activity=4,
			assignment_subactivity=8,
			backend_lang = 'de',
                        has_subfederation = hasSubfederation,
			created_at =NOW();

		SELECT LAST_INSERT_ID() INTO @clubId;

                INSERT INTO `fg_club_i18n` (`id`, `title_lang`, `lang`, `is_active`) VALUES (@clubId, clubTitle, 'de', 1);
				
		IF(clubType ='federation') THEN
						UPDATE fg_club SET `club_membership_available` = clubMembershipAvailable,`fed_membership_mandatory` = fedMembershipMandatory,`assign_fedmembership_with_application` = assignFedmembershipWithApplication,`add_existing_fed_member_club` = addExistingFedMemberClub,`fed_admin_access` = fedAdminAccess WHERE id = @clubId;
		ELSEIF(clubType ='standard_club') THEN
						UPDATE fg_club SET `club_membership_available` = 1 WHERE id = @clubId;
		ELSE
						UPDATE fg_club AS c
			LEFT JOIN fg_club fed on c.federation_id = fed.id
			SET
				`c`.`club_membership_available` = fed.club_membership_available,
				`c`.`fed_membership_mandatory` = fed.fed_membership_mandatory,
				`c`.`assign_fedmembership_with_application` = fed.assign_fedmembership_with_application,
				`c`.`add_existing_fed_member_club` = fed.add_existing_fed_member_club,
				`c`.`fed_admin_access` = fed.fed_admin_access
			WHERE c.id = @clubId;   
		END IF;
		

		                IF(clubType ='federation' OR clubType ='standard_club') THEN
                    INSERT INTO `fg_club_language` (`club_id`, `correspondance_lang`, `system_lang`, `visible_for_club`, `date_format`, `time_format`, `thousand_separator`, `decimal_marker`) VALUES
                    (@clubId, 'de', 'de', 1, 'dd.mm.YY', 'H:i', 'default', 'default');
                    SELECT LAST_INSERT_ID() INTO @languageId;
                    INSERT INTO `fg_club_language_settings` (`club_language_id`, `club_id`, `sort_order`, `is_active`) VALUES
                    (@languageId, @clubId, 1, 1);
                ELSE
                    INSERT INTO `fg_club_language_settings` (`club_language_id`, `club_id`, `sort_order`, `is_active`) SELECT `club_language_id`, @clubId, `sort_order`, `is_active` FROM fg_club_language_settings WHERE club_id = federationId;
                END IF;

				INSERT INTO `fg_cm_club_attribute` (`attribute_id`, `club_id`, privacy_contact, is_confirm_contact, sort_order, is_required_type, profile_status) SELECT `attribute_id`, @clubId, privacy_contact, is_confirm_contact, sort_order, is_required_type, profile_status  FROM fg_cm_club_attribute WHERE club_id = 1;
                IF (clubType ='sub_federation' OR clubType ='federation_club' OR clubType ='sub_federation_club') THEN
                    INSERT INTO `fg_cm_club_attribute` (`attribute_id`, `club_id`) SELECT id, @clubId FROM fg_cm_attribute WHERE club_id = federationId;
                END IF;
                IF (clubType ='sub_federation_club') THEN
                    INSERT INTO `fg_cm_club_attribute` (`attribute_id`, `club_id`) SELECT id, @clubId FROM fg_cm_attribute WHERE club_id = parentClubId;
                END IF;

				INSERT INTO `fg_rm_category` (`club_id`, `title`, `contact_assign`, `role_assign`, `function_assign`, `is_active`, `is_team`, `is_workgroup`, `sort_order`, `is_allowed_fedmember_subfed`, `is_allowed_fedmember_club`, `is_required_fedmember_subfed`, `is_required_fedmember_club`, `is_fed_category`) SELECT @clubId, `title`, `contact_assign`, `role_assign`, `function_assign`, `is_active`, `is_team`, `is_workgroup`, `sort_order`, `is_allowed_fedmember_subfed`, `is_allowed_fedmember_club`, `is_required_fedmember_subfed`, `is_required_fedmember_club`, `is_fed_category` FROM `fg_rm_category` WHERE club_id = 1;
				                INSERT INTO `fg_rm_category_i18n` (`id`,`title_lang`,`lang`,`is_active`) SELECT rc.`id`, rc.`title`, cl.`correspondance_lang`, 1 FROM `fg_club_language_settings` AS cls INNER JOIN fg_club_language cl ON cl.id = cls.club_language_id LEFT JOIN fg_rm_category AS rc ON cls.club_id = rc.club_id WHERE cls.club_id =  @clubId ORDER BY cls.sort_order;
				INSERT INTO `fg_rm_role` (`category_id`, `title`, `is_executive_board`, `sort_order`, `club_id`, `description`, `type`) SELECT id, 'Executive Board', 1, 1, @clubId, 'Executive Board', 'W' FROM `fg_rm_category` WHERE club_id = @clubId AND is_workgroup=1;
		SELECT LAST_INSERT_ID() INTO @executiveBrdId;
						INSERT INTO `fg_rm_role_i18n` (`id`,`title_lang`, `description_lang`, `lang`,`is_active`) SELECT rmr.`id`, rmr.`title`, rmr.`description`, cl.`correspondance_lang`, 1 FROM `fg_club_language_settings` AS cls INNER JOIN fg_club_language cl ON cl.id = cls.club_language_id LEFT JOIN fg_rm_role AS rmr ON cls.club_id = rmr.club_id WHERE cls.club_id =  @clubId ORDER BY cls.sort_order;
                IF(clubType ='federation' OR clubType ='standard_club') THEN
						
			INSERT INTO `fg_rm_function` (`category_id`, `title`, `sort_order`, is_federation) SELECT id, 'Präsident', 1, 0 FROM `fg_rm_category` WHERE club_id = @clubId AND is_workgroup=1;
			SELECT LAST_INSERT_ID() INTO @executiveBrdFunId;
						INSERT INTO fg_rm_role_function (role_id, function_id) VALUES(@executiveBrdId, @executiveBrdFunId);
			
			IF(clubType ='federation') THEN
								INSERT INTO `fg_rm_function` (`category_id`, `title`, `sort_order`, is_federation) SELECT id, 'Präsident', 1, 1 FROM `fg_rm_category` WHERE club_id = @clubId AND is_workgroup=1;
				SELECT LAST_INSERT_ID() INTO @executiveBrdFunId;
								INSERT INTO fg_rm_role_function (role_id, function_id) VALUES(@executiveBrdId, @executiveBrdFunId);			
			END IF;
						                        INSERT INTO `fg_rm_function_i18n` (`id`,`title_lang`,`lang`) SELECT rf.`id`, rf.`title`, cl.`correspondance_lang` FROM `fg_club_language_settings` AS cls INNER JOIN fg_club_language cl ON cl.id = cls.club_language_id LEFT JOIN fg_rm_category AS rc ON cls.club_id = rc.club_id INNER JOIN fg_rm_function AS rf ON rc.id = rf.category_id WHERE cls.club_id =  @clubId ORDER BY cls.sort_order;
			
		END IF;

                                INSERT INTO `fg_cn_recepients` (`name`, `club_id`, `updated_at`, `filter_data`, `is_all_active`, `sort_order`) VALUES ('All Contacts', @clubId, NOW(), '', '1', '1');
                INSERT INTO `fg_cn_recepients_email`(recepient_list_id, email_type, email_field_id) SELECT LAST_INSERT_ID(), 'contact_field', '3';
		
        IF(clubType ='federation' OR clubType ='standard_club') THEN        
						INSERT INTO `fg_cm_membership` (`club_id`, `title`, `sort_order`) VALUES (@clubId, 'Aktivmitglied', 1);
							        INSERT INTO `fg_cm_membership_i18n` (`id`,`title_lang`, `lang`) SELECT cm.`id`, cm.`title`, cl.`correspondance_lang` FROM `fg_club_language_settings` AS cls INNER JOIN fg_club_language cl ON cl.id = cls.club_language_id LEFT JOIN fg_cm_membership AS cm ON cls.club_id = cm.club_id WHERE cls.club_id =  @clubId ORDER BY cls.sort_order;			
						SELECT LAST_INSERT_ID() INTO @membershipId;
			INSERT INTO fg_cm_membership_log (club_id, membership_id, date,kind,field,value_after,changed_by) VALUES (@clubId,@membershipId,now(),'data','Name','Aktivmitglied', 1);

		ELSEIF(clubType ='sub_federation_club' OR clubType ='federation_club') THEN
						SELECT club_membership_available INTO @federationClubMembershipAvailable FROM fg_club WHERE id = federationId LIMIT 1;
			IF(@federationClubMembershipAvailable = 1) THEN
				INSERT INTO `fg_cm_membership` (`club_id`, `title`, `sort_order`) VALUES (@clubId, 'Aktivmitglied', 1);
												INSERT INTO `fg_cm_membership_i18n` (`id`,`title_lang`, `lang`) SELECT cm.`id`, cm.`title`, cl.`correspondance_lang` FROM `fg_club_language_settings` AS cls INNER JOIN fg_club_language cl ON cl.id = cls.club_language_id LEFT JOIN fg_cm_membership AS cm ON cls.club_id = cm.club_id WHERE cls.club_id =  @clubId ORDER BY cls.sort_order;
			
			SELECT LAST_INSERT_ID() INTO @membershipId;
			INSERT INTO fg_cm_membership_log (club_id, membership_id, date,kind,field,value_after,changed_by) VALUES (@clubId,@membershipId,now(),'data','Name','Aktivmitglied', 1);
			END IF;	
		END IF;	
		SET signature = concat('Freundliche Grüsse','
' ,clubTitle );
		INSERT INTO `fg_club_settings` (`club_id`, `fiscal_year`, `currency`,`signature`) VALUES(@clubId, '2015-01-01', 'CHF',signature);
		SELECT LAST_INSERT_ID() INTO @settingid;
				IF(clubType ='federation') THEN
			SET @query1 = CONCAT('CREATE TABLE `master_federation_',@clubId, "`(`club_id` int(11), `fed_contact_id` int(11),PRIMARY KEY (`fed_contact_id`)) ENGINE = InnoDB DEFAULT  CHARACTER SET utf8 COLLATE utf8_general_ci");
			SET @alterquery = CONCAT('ALTER TABLE `master_federation_',@clubId,'` ADD FOREIGN KEY (`fed_contact_id`) REFERENCES `fg_cm_contact`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION, ADD FOREIGN KEY (`club_id`) REFERENCES `fg_club`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
		ELSEIF(clubType ='sub_federation') THEN
			SET @query1 = CONCAT('CREATE TABLE `master_federation_',@clubId, "`(`club_id` int(11), `contact_id` int(11),PRIMARY KEY (`contact_id`)) ENGINE = InnoDB DEFAULT  CHARACTER SET utf8 COLLATE utf8_general_ci");
			SET @alterquery = CONCAT('ALTER TABLE `master_federation_',@clubId,'` ADD FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION, ADD FOREIGN KEY (`club_id`) REFERENCES `fg_club`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION');

		ELSE
			SET @query1 = CONCAT('CREATE TABLE `master_club_',@clubId, "`(`contact_id` int(11), PRIMARY KEY (`contact_id`)) ENGINE = InnoDB DEFAULT  CHARACTER SET utf8 COLLATE utf8_general_ci ");
			SET @alterquery =CONCAT('ALTER TABLE `master_club_',@clubId,'` ADD FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION');
		END IF;	
                
				INSERT INTO fg_cms_article_category (title,sort_order,club_id) VALUES ('Kategorie',1,@clubId);
		INSERT INTO fg_cms_article_category_i18n (id,lang,title_lang) SELECT AC.id,'de',AC.title FROM fg_cms_article_category AC WHERE AC.club_id = @clubId LIMIT 1;

                                INSERT INTO `fg_cms_article_clubsetting` (club_id, comment_active, show_multilanguage_version, timeperiod_start_day, timeperiod_start_month) VALUES (@clubId, '0', '0', '1', '1');
                
                                INSERT INTO `fg_tm_theme_configuration` (`club_id`, `theme_id`, `title`, `header_scrolling`, `created_at`, `updated_at`, `is_active`, `is_default`, `custom_css`, `created_by`, `updated_by`, `bg_image_selection`, `bg_slider_time`, `color_scheme_id`) VALUES
                (@clubId, 1, 'Die Standard-Konfiguration', 0, NULL, NULL, 1, 1, NULL, 1, NULL, 'random', NULL, 1);
				INSERT INTO `fg_tm_theme_configuration` (`club_id`, `theme_id`, `title`, `header_scrolling`, `created_at`, `updated_at`, `is_active`, `is_default`, `custom_css`, `created_by`, `updated_by`, `bg_image_selection`, `bg_slider_time`, `color_scheme_id`) VALUES
				(@clubId, 2, 'Die Standard-Konfiguration 2', 0, NULL, NULL, 0, 1, NULL, 1, NULL, 'random', NULL, 5);
				SELECT @settingid;
				 INSERT INTO `fg_club_settings_i18n` (id, signature_lang,logo_lang,lang) VALUES (@settingid, signature, NULL, 'de');

                
		PREPARE stmt FROM @query1;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;

		PREPARE stmt FROM @alterquery;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
		 
	COMMIT;
END