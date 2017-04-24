-- To create master table for all clubs and system table for migration v3
DROP PROCEDURE IF EXISTS `createMasterClubAll`//
CREATE PROCEDURE `createMasterClubAll`()
BEGIN
	DECLARE clubId, record_not_found, isFairgate, isFederation, isSubFederation INTEGER DEFAULT 0; 
	DECLARE clubType TEXT DEFAULT "";
	DECLARE clubcursor CURSOR FOR SELECT id, is_fairgate, is_federation, is_sub_federation FROM fg_club WHERE is_deleted <> 1 AND is_fairgate <>1;	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;

	START TRANSACTION;
		call createMasterSystemTable();
		OPEN clubcursor;
			loop_club: LOOP
				FETCH clubcursor INTO clubId, isFairgate, isFederation, isSubFederation;
					IF record_not_found THEN
						SET record_not_found = 0;
						LEAVE loop_club;
					END IF;
					SET clubType = 'club';					
					IF(isFederation = 1) THEN
						SET clubType = 'federation';
					END IF;
					IF(isSubFederation = 1) THEN
						SET clubType = 'sub_federation';
					END IF;			
   				 call `createMasterClubTable`( clubId, clubType);			
			END LOOP loop_club;	
		CLOSE clubcursor;
	COMMIT;
END
