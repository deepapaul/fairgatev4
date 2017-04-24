<?php

namespace Clubadmin\ContactBundle\Util;

use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContext;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * For handling the validation of membership log data edit via inline edit.
 */
class FgClubAssignmentValidator
{
    /**
     * $em.
     *
     * @var object {entitymanager object}
     */
    private $em;

    /**
     * $contactId.
     *
     * @var int ContactId
     */
    private $contactId;

    /**
     * $container.
     *
     * @var object {container object}
     */
    private $container;

    /**
     * $clubAssignmentId.
     *
     * @var int clubAssignmentId
     */
    private $clubAssignmentId;

    /**
     * $value.
     *
     * @var string New value
     */
    private $value;

    /**
     * $value.
     *
     * @var string New value
     */
    private $fieldName;

    /**
     * $clubAssignmentArray.
     *
     * @var array clubAssignment history details Array
     */
    private $clubAssignmentArray = array();

    /**
     * $clubArr.
     *
     * @var array Club details Array
     */
    private $clubArr = array();
    private $conn;

    /**
     * Constructor for initial setting.
     *
     * @param object $container
     * @param int    $membershipHistoryId
     * @param string $fieldName
     * @param string $value
     */
    public function __construct($container, $contactId, $clubAssignmentId, $fieldName, $value, $clubArray)
    {
        $this->container = $container;
        $this->contactId = $contactId;
        $this->clubAssignmentId = $clubAssignmentId;
        $this->fieldName = $fieldName;
        $this->conn = $this->container->get('database_connection');
        $this->value = FgUtility::getSecuredData($value, $this->conn);
        $this->clubArr = $clubArray;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->clubAssignmentArray = $this->em->getRepository('CommonUtilityBundle:FgClubAssignment')->getClubAssignmentObject($this->clubAssignmentId);
    }

    /**
     * Function to validate date.
     *
     * @param type                                          $date
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isValidDate($date, ExecutionContext $context)
    {
        $mimeTypesMessage = $this->container->get('translator')->trans('NOT_VALID_TYPE');
        if ($date == '') {
            $context->addViolation($mimeTypesMessage);
        } else {
            $format = FgSettings::getPhpDateFormat();
            $newDate = \DateTime::createFromFormat($format, $date);
            if (!$newDate) {
                $context->addViolation($mimeTypesMessage);
            }
        }
    }

    /**
     * Function to validate from date.
     *
     * @param type             $fromDate
     * @param ExecutionContext $context
     */
    public function isValidFromDate($fromDate, ExecutionContext $context)
    {
        $error = 0;
        //Check if the current joining date is greater than today
        if ($fromDate == '') {
            $fromDateMessage = $this->container->get('translator')->trans('NOT_VALID_TYPE');
            $context->addViolation($fromDateMessage);
            $error = 1;
        } else {
            $phpDateFormat = FgSettings::getPhpDateFormat();
            $dateObj = new \DateTime();
            $currentDateTimestamp = strtotime('+1 day'); //Actually its yesterdays,
            $currentDate = date($phpDateFormat);

            $fromDateTimestamp = $dateObj->createFromFormat($phpDateFormat, $fromDate)->format('U');
            $fromDateSql = $dateObj->createFromFormat($phpDateFormat, $fromDate)->format('Y-m-d');

            //Check if the current joining date is greater than today
            if ($fromDateTimestamp >= $currentDateTimestamp) {
                $fromDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $currentDate));
                $context->addViolation($fromDateMessage);
                $error = 1;
            }

            //Check if current joining date is greater than current leaving date
            if ($error == 0) {
                $clubAssignmentArray = $this->clubAssignmentArray[0];
                if ($clubAssignmentArray['toDate'] != '') {
                    $currentToDateTimestamp = $clubAssignmentArray['toDate']->format('U');
                    if ($fromDateTimestamp >= $currentToDateTimestamp) {
                        $fromDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $clubAssignmentArray['toDate']->format($phpDateFormat)));
                        $context->addViolation($fromDateMessage);
                        $error = 1;
                    }
                }
            }

            //Check if the current from date is between any other date (except the one being edited)
            if ($error == 0) {
                $clubHierarchy = $this->container->get('club')->get('clubHeirarchy');
                array_push($clubHierarchy, $this->container->get('club')->get('id'));
                $clubObj = new ClubPdo($this->container);
                $overlapDates = $clubObj->validateAssignmentDate($this->contactId, $fromDateSql, $clubHierarchy, $this->clubAssignmentId);

                if (count($overlapDates)) {
                    $overlapDates = $overlapDates[0];
                    $fromDate = $dateObj->createFromFormat($phpDateFormat, $overlapDates['fromDate'])->format($phpDateFormat);

                    if ($overlapDates['toDate'] != '') {
                        $toDate = $dateObj->createFromFormat($phpDateFormat, $overlapDates['toDate'])->format($phpDateFormat);
                    }

                    $fromDateMessage = $this->container->get('translator')->trans('CONTACT_MEMBERSHIP_DATE_OVERLAP', array('%joiningdate%' => $fromDate, '%leavingdate%' => $toDate));
                    $context->addViolation($fromDateMessage);
                    $error = 1;
                }
            }
        }
    }

    /**
     * Function to validate toDate.
     *
     * @param type             $toDate
     * @param ExecutionContext $context
     */
    public function isValidToDate($toDate, ExecutionContext $context)
    {
        $phpDateFormat = FgSettings::getPhpDateFormat();
        $dateObj = new \DateTime();
        $currentDateTimestamp = strtotime('+1 day'); //Actually its yesterdays,
        $currentDate = date($phpDateFormat);
//check to date is valid null or not
        if ($toDate == '') {
            $fromDateMessage = $this->container->get('translator')->trans('NOT_VALID_TYPE');
            $context->addViolation($fromDateMessage);
            $error = 1;
        } else {
            $toDateTimestamp = $dateObj->createFromFormat($phpDateFormat, $toDate)->format('U');
            $toDateSql = $dateObj->createFromFormat($phpDateFormat, $toDate)->format('Y-m-d');

            $error = 0;
            //Check if the current joining date is greater than today
            if ($toDateTimestamp >= $currentDateTimestamp) {
                $toDateMessage = $this->container->get('translator')->trans('LEAVING_DATE_VALIDATION_MSG', array('%a%' => $currentDate));
                $context->addViolation($toDateMessage);
                $error = 1;
            }

            //Check if current to date is less than current joining date
            if ($error == 0) {
                $clubAssignmentArray = $this->clubAssignmentArray[0];
                if ($clubAssignmentArray['toDate'] != '') {
                    $currentFromDateTimestamp = $clubAssignmentArray['fromDate']->format('U');
                    if ($currentFromDateTimestamp >= $toDateTimestamp) {
                        $toDateMessage = $this->container->get('translator')->trans('LEAVING_DATE_VALIDATION_MSG', array('%a%' => $clubAssignmentArray['fromDate']->format($phpDateFormat)));
                        $context->addViolation($toDateMessage);
                        $error = 1;
                    }
                }
            }

            //Check if the current leaving date is between any other date (except the one being edited)
            if ($error == 0) {
                $clubHierarchy = $this->container->get('club')->get('clubHeirarchy');
                array_push($clubHierarchy, $this->container->get('club')->get('id'));
                $clubObj = new ClubPdo($this->container);
                $overlapDates = $clubObj->validateAssignmentDate($this->contactId, $toDateSql, $clubHierarchy, $this->clubAssignmentId);

                if (count($overlapDates)) {
                    $overlapDates = $overlapDates[0];
                    $fromDate = $dateObj->createFromFormat($phpDateFormat, $overlapDates['fromDate'])->format($phpDateFormat);

                    if ($overlapDates['toDate'] != '') {
                        $toDate = $dateObj->createFromFormat($phpDateFormat, $overlapDates['toDate'])->format($phpDateFormat);
                    }

                    $fromDateMessage = $this->container->get('translator')->trans('CONTACT_MEMBERSHIP_DATE_OVERLAP', array('%joiningdate%' => $fromDate, '%leavingdate%' => $toDate));
                    $context->addViolation($fromDateMessage);
                    $error = 1;
                }
            }
        }
    }

    /**
     * Function to validate joining and leaving date.
     */
    public function validateClubAssignmentData()
    {
        $requiredMessage = $this->container->get('translator')->trans('REQUIRED');
        $updateSuccessMessage = 'MEMBERSHIP_LOG_UPDATE_SUCCESS';
        $valueConstraints = array();
        switch ($this->fieldName) {
            case 'fromDate':
                $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidDate')));                       
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidFromDate')));                            
                break;
            case 'toDate':
                $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidDate')));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidToDate')));
                break;
            default:
                break;
        }
        $collectionConstraint = new Collection(array(
            'contactId' => array(
                new NotBlank(array('message' => $requiredMessage)),
            ),
            'clubAssignmentId' => array(
                new NotBlank(array('message' => $requiredMessage)),
            ),
            'value' => $valueConstraints,
            'fieldName' => array(
                new NotBlank(array('message' => $requiredMessage)),
            ),
        ));
        $clubAssignmentData = array('contactId' => $this->contactId, 'clubAssignmentId' => $this->clubAssignmentId, 'value' => $this->value, 'fieldName' => $this->fieldName);
        $errors = $this->container->get('validator')->validate($clubAssignmentData, $collectionConstraint);
        if (count($errors) !== 0) {
            $output = array('valid' => 'false', 'msg' => $errors[0]->getMessage());
        } else {
            $output = array('valid' => 'true', 'msg' => $this->container->get('translator')->trans($updateSuccessMessage));
        }

        return $output;
    }
}
