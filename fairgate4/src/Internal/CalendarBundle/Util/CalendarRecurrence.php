<?php

/**
 * CalendarRecurrence
 *
 * This class is used for handling recurring events in calendar section
 *
 * @package    InternalCalendarBundle
 * @subpackage Util
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
namespace Internal\CalendarBundle\Util;

use \Recurr\Rule;
use \Recurr\Transformer\ArrayTransformer;

/**
 * This class is used for handling recurring events in calendar section.
 *
 */
class CalendarRecurrence
{

    /**
     * Event start date
     * @var string 
     */
    public static $startDate;

    /**
     * Event end date
     * @var string 
     */
    public static $endDate;

    /**
     * Event until date
     * @var string 
     */
    public static $untilDate;

    /**
     * Recurrence rule string
     * @var string 
     */
    public static $recurrenceRule;

    /**
     * Method to set parameters
     * 
     * @param string $recurrenceRule Rule
     * @param string $startDate      Event start date
     * @param string $endDate        Event end date
     * @param string $untilDate      Until date
     */
    public function __construct($recurrenceRule = null, $startDate = null, $endDate = null, $untilDate = null)
    {
        $this->recurrenceRule = $recurrenceRule;
        $this->startDate = new \DateTime($startDate);
        if ($endDate) {
            $this->endDate = new \DateTime($endDate);
        }
        if ($untilDate) {
            $this->untilDate = new \DateTime($untilDate);
        }
    }

    /**
     * Method to find recurrence dates between 2 dates
     * 
     * @param string $afterDate
     * @param string $beforeDate
     * @param string $untilDate  untildate in master table
     * 
     * @return array with keys (recurrenceStartDate, recurrenceEndDate)
     */
    public function getRecurrenceBetween($afterDate, $beforeDate, $untilDate)
    {
        $rule = new Rule($this->recurrenceRule, $this->startDate, $this->endDate);
        if ($this->untilDate) {
            $rule->setUntil($this->untilDate);
        }
        $transformer = new ArrayTransformer();
        if ($untilDate) {
            /* until-date in fg_em_calendar (field-name: repeat_untill_date) is saved in the format '2012-12-01 00:00:00'
             * But actually it should be considered as '2012-12-01 23:59:59' . So here add 1 day to $untilDate */
            $untilDateObj = new \DateTime($untilDate);
            //$untilDateObj->modify('+1 day'); // now until date is saved as '2012-12-01 23:59:59'           
            $recurrences = $transformer->transform($rule)->endsAfter(new \DateTime($afterDate), true)->startsBefore(new \DateTime($beforeDate), true)->endsBefore($untilDateObj, true);
        } else {
            $recurrences = $transformer->transform($rule)->endsAfter(new \DateTime($afterDate), true)->startsBefore(new \DateTime($beforeDate), true);
        }
        $result = array();
        foreach ($recurrences as $recurrence) {
            $result[] = array("recurrenceStartDate" => $recurrence->getStart()->format('Y-m-d H:i:s'), "recurrenceEndDate" => $recurrence->getEnd()->format('Y-m-d H:i:s'));
        }

        return $result;
    }

    /**
     * Method to find recurrence dates after a aprticular date
     * 
     * @param string $afterDate
     * @param string $untilDate  untildate in master table
     * 
     * @return array with keys (recurrenceStartDate, recurrenceEndDate)
     */
    public function getRecurrenceAfter($afterDate, $untilDate)
    {
        $rule = new Rule($this->recurrenceRule, $this->startDate, $this->endDate);
        if ($this->untilDate) {
            $rule->setUntil($this->untilDate);
        }
        $transformer = new ArrayTransformer();
        if ($untilDate) {
            /* until-date in fg_em_calendar (field-name: repeat_untill_date) is saved in the format '2012-12-01 00:00:00'
             * But actually it should be considered as '2012-12-01 23:59:59' . So here add 1 day to $untilDate */
            $untilDateObj = new \DateTime($untilDate);
            //$untilDateObj->modify('+1 day'); // now until date is saved as '2012-12-01 23:59:59'
            $recurrences = $transformer->transform($rule)->endsAfter(new \DateTime($afterDate), true)->endsBefore($untilDateObj, true);
        } else {
            $recurrences = $transformer->transform($rule)->endsAfter(new \DateTime($afterDate), true);
        }
        $result = array();
        foreach ($recurrences as $recurrence) {
            $result[] = array("recurrenceStartDate" => $recurrence->getStart()->format('Y-m-d H:i:s'), "recurrenceEndDate" => $recurrence->getEnd()->format('Y-m-d H:i:s'));
        }

        return $result;
    }

    /**
     * Method to find next recurrence date after a particular date (get only one recurrence)( not including after date)
     * 
     * @param string $afterDate
     * 
     * @return array with keys (recurrenceStartDate, recurrenceEndDate)
     */
    public function getNextRecurrence($afterDate)
    {
        $rule = new Rule($this->recurrenceRule, $this->startDate, $this->endDate);
        if ($this->untilDate) {
            $rule->setUntil($this->untilDate);
        }
        $rule->setCount(1);
        $transformer = new ArrayTransformer();
        $constraint = new \Recurr\Transformer\Constraint\AfterConstraint(new \DateTime($afterDate));
        $recurrences = $transformer->transform($rule, null, $constraint);
        foreach ($recurrences as $recurrence) {
            $recurrenceExist = true;  //is recurrence exist according to untildate
            if ($this->untilDate) { //until date in detail table is considered
                if ($this->untilDate < $recurrence->getStart()) { // recurrence only if startdate is < untildate in detail table
                    $recurrenceExist = false;
                }
            }
            $result = array("recurrenceStartDate" => $recurrence->getStart()->format('Y-m-d H:i:s'), "recurrenceEndDate" => $recurrence->getEnd()->format('Y-m-d H:i:s'), 'recurrenceExist' => $recurrenceExist);
        }

        return $result;
    }

    /**
     * Method to set startdate
     * 
     * @param string $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = new \DateTime($startDate);
    }

    /**
     * Method to set endDate
     * 
     * @param string $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = new \DateTime($endDate);
    }

    /**
     * Method to set untilDate
     * 
     * @param string $untilDate
     */
    public function setUntilDate($untilDate)
    {
        $this->untilDate = new \DateTime($untilDate);
    }

    /**
     * Method to find recurrences after start date (including start date)
     * 
     * @return array with keys (recurrenceStartDate, recurrenceEndDate)
     */
    public function getNextRecurrences()
    {
        $rule = new Rule($this->recurrenceRule, $this->startDate, $this->endDate);
        $transformer = new ArrayTransformer();
        $recurrences = $transformer->transform($rule)->startsAfter($this->startDate, true);
        $result = array();
        foreach ($recurrences as $recurrence) {
            $result[] = array("recurrenceStartDate" => $recurrence->getStart()->format('Y-m-d H:i:s'), "recurrenceEndDate" => $recurrence->getEnd()->format('Y-m-d H:i:s'));
        }

        return $result;
    }

    /**
     * Method to find one recurrence date starts after a particular date ( not ends after)
     * 
     * @param string $afterDate  date
     * @param string $untilDate  untildate in master table
     * 
     * @return array with keys (recurrenceStartDate, recurrenceEndDate)
     */
    public function getRecurrenceStartsAfter($afterDate, $untilDate)
    {
        $rule = new Rule($this->recurrenceRule, $this->startDate, $this->endDate);
        if ($this->untilDate) {
            //When setting until date, more than one recurrences may be return. To overcome this issue only one iteration is done
            $rule->setUntil($this->untilDate);
        } else {
            // when setting count, untill becomes null. So setCount should be used only when untgill date is null.
            $rule->setCount(1);
        }
        $transformer = new ArrayTransformer();
        $constraint = new \Recurr\Transformer\Constraint\AfterConstraint(new \DateTime($afterDate), true);
        if ($untilDate) {
            /* until-date in fg_em_calendar (field-name: repeat_untill_date) is saved in the format '2012-12-01 00:00:00'
             * But actually it should be considered as '2012-12-01 23:59:59' . So here add 1 day to $untilDate */
            $untilDateObj = new \DateTime($untilDate);
            //$untilDateObj->modify('+1 day'); // now until date is saved as '2012-12-01 23:59:59'
            $recurrences = $transformer->transform($rule, null, $constraint)->endsBefore($untilDateObj, true);
        } else {
            $recurrences = $transformer->transform($rule, null, $constraint);
        }
        $result = array();
        foreach ($recurrences as $recurrence) {
            $result = array("recurrenceStartDate" => $recurrence->getStart()->format('Y-m-d H:i:s'), "recurrenceEndDate" => $recurrence->getEnd()->format('Y-m-d H:i:s'));
            break; // for getting onle one recurrence
        }

        return $result;
    }
}
