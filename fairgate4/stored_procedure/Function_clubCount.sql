DROP FUNCTION IF EXISTS `clubCount`//
CREATE FUNCTION `clubCount`(`clubId` INT) RETURNS int(11)
BEGIN

        DECLARE clubTotalCount int DEFAULT 0;
        SELECT count(c.id) INTO  clubTotalCount FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := clubId,@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id WHERE c.is_deleted=0;
        RETURN clubTotalCount;
          
END
