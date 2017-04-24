drop trigger if exists lastLoginTrigger;
delimiter //
CREATE TRIGGER lastLoginTrigger AFTER UPDATE ON sf_guard_user
    FOR EACH ROW
    BEGIN
        IF NEW.last_login IS NOT NULL AND NEW.`last_login`!= OLD.`last_login` THEN
            UPDATE `fg_cm_contact` SET `last_login` = NEW.`last_login` WHERE `id` = NEW.`contact_id`;
        END IF;
    END;//