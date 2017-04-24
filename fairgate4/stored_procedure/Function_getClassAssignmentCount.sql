-- Used for import - to filter CSV data
DROP FUNCTION IF EXISTS `getClassAssignmentCount`//
CREATE DEFINER=`admin`@`localhost` FUNCTION `getClassAssignmentCount`(classId INT, clubId INT) RETURNS int(11)
BEGIN
	DECLARE assignmentCount  INTEGER DEFAULT 0;

	select count(cca.id) INTO assignmentCount from fg_club_class_assignment AS cca WHERE cca.class_id =classId AND (cca.club_id IN(SELECT c.id FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := clubId,@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id ));
	RETURN assignmentCount;
END
