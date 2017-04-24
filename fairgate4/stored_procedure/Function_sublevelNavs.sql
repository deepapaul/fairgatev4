-- Get all sub levels navigations and sub navigations under a club
DROP FUNCTION `sublevelNavs`//
CREATE DEFINER=`admin`@`localhost` FUNCTION `sublevelNavs`(value INT, clubId INT) RETURNS int(11)
    READS SQL DATA
BEGIN
        DECLARE _id INT;
        DECLARE _parent INT;
        DECLARE _sort_order INT;
        DECLARE _club_id INT;
        DECLARE _additional_nav INT;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET @id = NULL;

        SET _parent = @id;
        SET _id = -1;
        SET _sort_order = -1;
        SET _club_id = clubId;
        SET _additional_nav = 0;
        
        IF @id IS NULL THEN
                RETURN NULL;
        END IF;

        LOOP
                SELECT  id
                INTO    @id
                FROM    fg_cms_navigation
                WHERE   parent_id = _parent AND sort_order > _sort_order AND club_id = _club_id AND is_additional = _additional_nav
                        ORDER BY sort_order ASC limit 1;

                IF @id IS NOT NULL OR _parent = @start_with THEN
                        SET @level = @level + 1;
                        RETURN @id;
                END IF;

                SET @level := @level - 1;

                SELECT  id, parent_id, sort_order, club_id, is_additional
                INTO    _id, _parent, _sort_order, _club_id, _additional_nav
                FROM    fg_cms_navigation
                WHERE   id = _parent AND club_id = _club_id AND is_additional = _additional_nav ORDER BY sort_order ASC ;
        END LOOP;
END