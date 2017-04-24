drop trigger if exists assignMemberId//
CREATE TRIGGER `assignMemberId` BEFORE INSERT ON `fg_cm_contact`
    FOR EACH ROW 
    BEGIN
        DECLARE maxMemberId INT;
        IF NEW.member_id ='0' THEN
            SELECT memberIdCount(NEW.club_id) INTO  maxMemberId;
            SET NEW.member_id = maxMemberId + 1;
        END IF;
    END;