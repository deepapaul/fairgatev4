DROP FUNCTION IF EXISTS `FIND_SET_IN_SET`;
DELIMITER $$
CREATE FUNCTION `FIND_SET_IN_SET`(needle VARCHAR(500), haystack VARCHAR(500)) RETURNS int(11) 
BEGIN
	DECLARE currentOccurence INT DEFAULT 0;
	DECLARE nextOccurence INT DEFAULT 0;
	DECLARE foundLength INT DEFAULT 0;
	DECLARE inSet INT DEFAULT 0;
	DECLARE foundItem VARCHAR(500);

	IF (needle IS NOT NULL AND haystack IS NOT NULL) THEN
		check_in_set:
		LOOP
			SET currentOccurence = nextOccurence+1;
			SET nextOccurence = LOCATE(',', needle, (currentOccurence+1));

			IF nextOccurence = 0 THEN
				SET foundLength =  (LENGTH(needle) - currentOccurence) + 1;
			ELSE 
				SET foundLength = nextOccurence - currentOccurence;
			END IF;

			SET foundItem = MID(needle, currentOccurence, foundLength);
			SET inSet = FIND_IN_SET(foundItem, haystack);

			IF (nextOccurence = 0 || inSet = 0) THEN
				LEAVE check_in_set;
		    END IF;
		END LOOP check_in_set;
	END IF;

	RETURN inSet;
END