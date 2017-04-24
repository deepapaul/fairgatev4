-- Used for import - to filter CSV data
DROP FUNCTION IF EXISTS `filterText`//
CREATE DEFINER=`admin`@`localhost` FUNCTION `filterText`(`inputText` TEXT CHARSET utf8) RETURNS text CHARSET latin1
return TRIM(BOTH '\"' FROM (TRIM(BOTH '\r' FROM (TRIM(BOTH '\n' FROM TRIM(inputText))))))
