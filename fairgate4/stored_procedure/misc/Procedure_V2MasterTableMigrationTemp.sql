-- --------------------------------------------------------------------------------
-- Routine DDL
-- Note: comments before and after the routine body will not be stored by the server
-- --------------------------------------------------------------------------------
DELIMITER $$

CREATE DEFINER=`admin`@`%` PROCEDURE `V2MasterTableMigrationTemp`()
BEGIN
    DECLARE clubId, clubType, clubTitle, urlIdentifier VARCHAR(255) DEFAULT "";
    DECLARE contactIdName VARCHAR(255) DEFAULT "contact_id";
    DECLARE tableClubId, parentClubId, federationId INTEGER DEFAULT 0;
    DECLARE record_not_found INTEGER DEFAULT 0;
    DECLARE clubidCursor CURSOR FOR 
    SELECT `id`, `parent_club_id`, `federation_id`, `club_type` FROM `fg_club` WHERE id <> 1 ORDER BY `id` ASC;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
	START TRANSACTION;
	OPEN clubidCursor;
            contactIdLoop: LOOP
                FETCH clubidCursor INTO tableClubId, parentClubId, federationId, clubType;
                IF record_not_found THEN
                    SET record_not_found = 0;
                    LEAVE contactIdLoop;
                END IF;	
                -- update club specific fields to fg_cm_contact
                IF(clubType ='federation' OR clubType ='sub_federation') THEN
                    SET @alterquery = CONCAT("UPDATE fg_cm_contact C INNER JOIN `master_federation_",tableClubId,"` MC ON MC.contact_id=C.old_contact_id SET C.system_language=MC.system_language,C.intranet_access=MC.intranet_access,C.is_stealth_mode=MC.is_stealth_mode,C.is_sponsor=MC.is_sponsor,C.is_subscriber=MC.is_subscriber,C.is_household_head=MC.is_household_head,C.is_seperate_invoice=MC.is_seperate_invoice,C.is_postal_address=MC.is_postal_address WHERE C.club_id=",tableClubId);
                ELSE
                    SET @alterquery = CONCAT("UPDATE fg_cm_contact C INNER JOIN `master_club_",tableClubId,"` MC ON MC.contact_id=C.old_contact_id SET C.system_language=MC.system_language,C.intranet_access=MC.intranet_access,C.is_stealth_mode=MC.is_stealth_mode,C.is_sponsor=MC.is_sponsor,C.is_subscriber=MC.is_subscriber,C.is_household_head=MC.is_household_head,C.is_seperate_invoice=MC.is_seperate_invoice,C.is_postal_address=MC.is_postal_address WHERE C.club_id=",tableClubId);
                END IF;	
                SET SQL_SAFE_UPDATES = 0;
                 PREPARE stmt FROM @alterquery;
                 EXECUTE stmt;
                 DEALLOCATE PREPARE stmt;
                -- update contact of federation and sub federation
                IF(clubType ='sub_federation') THEN
                    SET @alterquery = CONCAT("UPDATE `master_federation_",tableClubId,"` MC INNER JOIN fg_cm_contact C ON MC.contact_id=C.old_contact_id AND C.club_type='sub_federation' SET MC.contact_id=C.id");
                    PREPARE stmt FROM @alterquery;
                    EXECUTE stmt;
                    DEALLOCATE PREPARE stmt;
                END IF;
                IF(clubType ='federation') THEN
                    SET @alterquery = CONCAT("UPDATE `master_federation_",tableClubId,"` MC INNER JOIN fg_cm_contact C ON MC.contact_id=C.old_contact_id SET MC.contact_id=C.fed_contact_id");
                    PREPARE stmt FROM @alterquery;
                    EXECUTE stmt;
                    DEALLOCATE PREPARE stmt;
                END IF;
		-- Change contact_id field name for federation
                IF(clubType ='federation') THEN
                        SET @alterquery = CONCAT("ALTER TABLE `master_federation_",tableClubId,"` CHANGE `contact_id` `fed_contact_id` INT(11) NOT NULL DEFAULT '0'");
                        PREPARE stmt FROM @alterquery;
                        EXECUTE stmt;
                        DEALLOCATE PREPARE stmt;
                END IF;
                -- drop club fields
                IF(clubType ='sub_federation' OR clubType ='federation') THEN
                    SET @alterquery = CONCAT("ALTER TABLE `master_federation_",tableClubId,"` DROP `is_fed_member`,DROP `quickwindow_visibilty`,DROP `system_language`,DROP `intranet_access`,DROP `is_stealth_mode`,DROP `is_sponsor`,DROP `is_subscriber`,DROP `is_household_head`,DROP `is_seperate_invoice`,DROP `is_postal_address`");
                ELSE
                    SET @alterquery = CONCAT("ALTER TABLE `master_club_",tableClubId,"` DROP `quickwindow_visibilty`,DROP `system_language`,DROP `intranet_access`,DROP `is_stealth_mode`,DROP `is_sponsor`,DROP `is_subscriber`,DROP `is_household_head`,DROP `is_seperate_invoice`,DROP `is_postal_address`");
                END IF;	

                PREPARE stmt FROM @alterquery;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;

                UPDATE `fg_club` SET is_sbo=0 WHERE id = tableClubId;
                
		END LOOP contactIdLoop;	
        CLOSE clubidCursor;
	COMMIT;
END