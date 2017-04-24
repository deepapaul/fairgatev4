-- Function to get total amount for a booking in a time period
DROP FUNCTION IF EXISTS `getTotalServiceAmount`//
CREATE FUNCTION `getTotalServiceAmount`(bookingId INT(11), startDate DATE, endDate DATE) RETURNS DECIMAL(10,2)
BEGIN
    DECLARE totalAmount DECIMAL(10,2);
    CASE 
        WHEN startDate IS NULL AND endDate IS NULL THEN
                BEGIN      
                        SELECT SUM(getPaymentAmount(smp.amount, smp.discount_type, smp.discount)) INTO totalAmount FROM fg_sm_paymentplans AS smp WHERE smp.booking_id = bookingId GROUP BY smp.booking_id;
                END;
        WHEN endDate IS NOT NULL THEN
                BEGIN      
                        SELECT SUM(getPaymentAmount(smp.amount, smp.discount_type, smp.discount)) INTO totalAmount FROM fg_sm_paymentplans AS smp WHERE smp.booking_id = bookingId AND (smp.date >=startDate AND smp.date <=endDate) GROUP BY smp.booking_id;
                END;			
        ELSE
                BEGIN
                        SELECT SUM(getPaymentAmount(smp.amount, smp.discount_type, smp.discount)) INTO totalAmount FROM fg_sm_paymentplans AS smp WHERE smp.booking_id = bookingId AND smp.date >=startDate GROUP BY smp.booking_id;
                END;	
    END CASE;
    RETURN totalAmount;
END