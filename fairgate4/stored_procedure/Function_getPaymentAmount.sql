-- Function to get amount after calculating discount - used in Sponsor managemnt
DROP FUNCTION IF EXISTS `getPaymentAmount`//
CREATE FUNCTION `getPaymentAmount`(amount DECIMAL(10,2), discountType CHAR(1), discount DECIMAL(10,2)) RETURNS DECIMAL(10,2)
BEGIN
	DECLARE totalAmount DECIMAL(10,2);
	CASE  discountType
            WHEN 'P' THEN
                BEGIN      
                        SET totalAmount = amount - ((discount*amount) / 100);
                END;
            WHEN 'A' THEN
                BEGIN
                        SET totalAmount = amount - discount;
                END;			
            ELSE
                BEGIN
                        SET totalAmount = amount;
                END;	
	END CASE;
	RETURN totalAmount;
END