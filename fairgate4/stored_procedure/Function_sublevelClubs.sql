-- Get all sub levels clubs and sub federation under a federation or sub federation
DROP FUNCTION `sublevelClubs`//
CREATE DEFINER=`admin`@`localhost` FUNCTION `sublevelClubs`(value INT) RETURNS int(11)
    READS SQL DATA
BEGIN
        DECLARE _id INT;
        DECLARE _parent INT;
        DECLARE _next INT;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET @id = NULL;

        SET _parent = @id;
        SET _id = -1;

        IF @id IS NULL THEN
                RETURN NULL;
        END IF;

        LOOP
                SELECT  MIN(id)
                INTO    @id
                FROM    fg_club
                WHERE   parent_club_id = _parent
                        AND id > _id;
                IF @id IS NOT NULL OR _parent = @start_with THEN
                        SET @level = @level + 1;
                        RETURN @id;
                END IF;
                SET @level := @level - 1;
                SELECT  id, parent_club_id
                INTO    _id, _parent
                FROM    fg_club
                WHERE   id = _parent;
        END LOOP;       
END
