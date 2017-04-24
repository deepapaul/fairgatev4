<?php

namespace Clubadmin\ContactBundle\Util;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmMembershipHistory;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContext;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * For handling the validation of membership log data edit via inline edit
 */
class FgMembershipValidator
{
    /**
     * $em
     * @var object {entitymanager object}
     */
    private $em;

    /**
     * $contactId
     * @var int ContactId
     */
    private $contactId;

    /**
     * $container
     * @var object {container object}
     */
    private $container;

    /**
     * $membershipHistoryId
     * @var int MembershipHistoryId
     */
    private $membershipHistoryId;

    /**
     * $value
     * @var string New value
     */
    private $value;

    /**
     * $value
     * @var string New value
     */
    private $fieldName;

    /**
     * $membershipHistoryArr
     * @var array Membership history details Array
     */
    private $membershipHistoryArr = array();

    /**
     * $clubArr
     * @var array Club details Array
     */
    private $clubArr = array();
    private $conn;
    /**
     * Constructor for initial setting
     *
     * @param object $container
     * @param int    $membershipHistoryId
     * @param string $fieldName
     * @param string $value
     */
    public function __construct($container, $contactId, $membershipHistoryId, $fieldName, $value, $clubArray)
    {
        $this->container = $container;
        $this->contactId = $contactId;
        $this->membershipHistoryId = $membershipHistoryId;
        $this->fieldName = $fieldName;
        $this->conn = $this->container->get('database_connection');
        $this->value = FgUtility::getSecuredData($value, $this->conn);
        $this->clubArr = $clubArray;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->membershipHistoryArr = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getMembershipHistoryObject($this->membershipHistoryId);
    }

   /**
    * Function to validate date
    * @param type $date
    * @param \Symfony\Component\Validator\ExecutionContext $context
    */
    public function isValidDate($date, ExecutionContext $context)
    {
        $mimeTypesMessage = $this->container->get('translator')->trans('NOT_VALID_TYPE');
        $format = FgSettings::getPhpDateFormat();
        $newDate = \DateTime::createFromFormat($format, $date);
        if (!$newDate) {
            $context->addViolation($mimeTypesMessage);
        }
    }

    /**
     * Function to validate joining date
     * @param Date $joiningDate joining date
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isValidJoiningDate($joiningDate, ExecutionContext $context)
    {
        $phpDateFormat = FgSettings::getPhpDateFormat();
        
        $dateObj = new \DateTime();
        $currentDateTimestamp = strtotime('+1 day');//Actually its yesterdays,
        $currentDate = date($phpDateFormat);
        
        $joiningDateTimestamp = $dateObj->createFromFormat($phpDateFormat, $joiningDate)->format('U');
        $joiningDateSql = $dateObj->createFromFormat($phpDateFormat, $joiningDate)->format('Y-m-d');
        
        $error = 0;
        //Check if the current joining date is greater than today
        if ($joiningDateTimestamp >= $currentDateTimestamp) {
            $joiningDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $currentDate));
            $context->addViolation($joiningDateMessage);
            $error = 1;
        }
        
        //Check if current joining date is greater than current leaving date
        if($error == 0){
            $membershipHistoryArr = $this->membershipHistoryArr[0];
            if($membershipHistoryArr['leavingDate'] != ''){
                $currentLeavingDateTimestamp = $membershipHistoryArr['leavingDate']->format('U');
                if ($joiningDateTimestamp >= $currentLeavingDateTimestamp) {
                    $joiningDateMessage = $this->container->get('translator')->trans('JOINING_DATE_VALIDATION_MSG', array('%a%' => $membershipHistoryArr['leavingDate']->format($phpDateFormat)));
                    $context->addViolation($joiningDateMessage);
                    $error = 1;
                }
            }
        }
          
        //Check if the current joining date is between any other date (except the one being edited)
        if($error == 0){
            $clubHierarchy = $this->container->get('club')->get('clubHeirarchy');
            array_push($clubHierarchy, $this->container->get('club')->get('id'));
            $alreadyAddedMemershipOverlapDates = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')
                                        ->validateMembershipDate($this->contactId, $joiningDateSql, $clubHierarchy,$this->membershipHistoryId );
            
            if(count($alreadyAddedMemershipOverlapDates)){
                $overlapDates = $alreadyAddedMemershipOverlapDates[0];
                $joiningdate = $dateObj->createFromFormat($phpDateFormat, $overlapDates['joiningDate'])->format($phpDateFormat);
                
                if($overlapDates['leavingDate'] != '')
                    $leavingdate = $dateObj->createFromFormat($phpDateFormat, $overlapDates['leavingDate'])->format($phpDateFormat);
                
                $joiningDateMessage = $this->container->get('translator')->trans('CONTACT_MEMBERSHIP_DATE_OVERLAP', array('%joiningdate%' => $joiningdate,'%leavingdate%' => $leavingdate));
                $context->addViolation($joiningDateMessage);
                $error = 1;
            }
        }
       
    }

    /**
     * Function to validate leaving date
     * @param Date $leavingDate leaving date
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isValidLeavingDate($leavingDate, ExecutionContext $context)
    {
        $phpDateFormat = FgSettings::getPhpDateFormat();
        
        $dateObj = new \DateTime();
        $currentDateTimestamp = strtotime('+1 day');//Actually its yesterdays,
        $currentDate = date($phpDateFormat);
        
        $leavingDateTimestamp = $dateObj->createFromFormat($phpDateFormat, $leavingDate)->format('U');
        $leavingDateSql = $dateObj->createFromFormat($phpDateFormat, $leavingDate)->format('Y-m-d');
        
        $error = 0;
        //Check if the current joining date is greater than today
        if ($leavingDateTimestamp >= $currentDateTimestamp) {
            $leavingDateMessage = $this->container->get('translator')->trans('LEAVING_DATE_VALIDATION_MSG', array('%a%' => $currentDate));
            $context->addViolation($leavingDateMessage);
            $error = 1;
        }
        
        //Check if current leaving date is less than current joining date
        if($error == 0){
            $membershipHistoryArr = $this->membershipHistoryArr[0];
            if($membershipHistoryArr['joiningDate'] != ''){
                $currentJoiningDateTimestamp = $membershipHistoryArr['joiningDate']->format('U');
                if ($currentJoiningDateTimestamp >= $leavingDateTimestamp) {
                    $leavingDateMessage = $this->container->get('translator')->trans('LEAVING_DATE_VALIDATION_MSG', array('%a%' => $membershipHistoryArr['joiningDate']->format($phpDateFormat)));
                    $context->addViolation($leavingDateMessage);
                    $error = 1;
                }
            }
        }
        
        //Check if the current leaving date is between any other date (except the one being edited)
        if($error == 0){
            $clubHierarchy = $this->container->get('club')->get('clubHeirarchy');
            array_push($clubHierarchy, $this->container->get('club')->get('id'));
            $alreadyAddedMemershipOverlapDates = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')
                                        ->validateMembershipDate($this->contactId, $leavingDateSql, $clubHierarchy,$this->membershipHistoryId );
            
            
            if(count($alreadyAddedMemershipOverlapDates)){
                $overlapDates = $alreadyAddedMemershipOverlapDates[0];
                $joiningdate = $dateObj->createFromFormat($phpDateFormat, $overlapDates['joiningDate'])->format($phpDateFormat);
                
                if($overlapDates['leavingDate'] != '')
                    $leavingdate = $dateObj->createFromFormat($phpDateFormat, $overlapDates['leavingDate'])->format($phpDateFormat);
                
                $joiningDateMessage = $this->container->get('translator')->trans('CONTACT_MEMBERSHIP_DATE_OVERLAP', array('%joiningdate%' => $joiningdate,'%leavingdate%' => $leavingdate));
                $context->addViolation($joiningDateMessage);
                $error = 1;
            }
        }
        
    }

    /**
     * Function to validate joining and leaving date
     */
    public function validateMembershipLogData()
    {
        $requiredMessage = $this->container->get('translator')->trans('REQUIRED');
        $updateSuccessMessage = 'MEMBERSHIP_LOG_UPDATE_SUCCESS';
        $valueConstraints = array();
        switch ($this->fieldName) {
            case 'joining_date':
                $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidDate')));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidJoiningDate')));
                break;
            case 'leaving_date':
                $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidDate')));
                $valueConstraints[] = new Callback(array('callback' => array($this, 'isValidLeavingDate')));
                break;
            case 'membership':
                $valueConstraints[] = new NotBlank(array('message' => $requiredMessage));
                $objMembershipPdo = new membershipPdo($this->container);
                $membershipArr = $objMembershipPdo->getMemberships($this->clubArr['clubType'], $this->clubArr['clubId'], $this->clubArr['subFederationId'], $this->clubArr['federationId']);
                $membershipIds = array();
                foreach ($membershipArr as $key => $val) {
                    $membershipIds[] = $key;
                }
                $valueConstraints[] = new Choice(array('choices' => $membershipIds, 'message' => $requiredMessage));
                break;
            default:
                break;
        }
        $collectionConstraint = new Collection(array(
            'contactId' => array(
                new NotBlank(array('message' => $requiredMessage)),
            ),
            'membershipHistoryId' => array(
                new NotBlank(array('message' => $requiredMessage)),
            ),
            'value' => $valueConstraints,
            'fieldName' => array(
                new NotBlank(array('message' => $requiredMessage)),
            ),
        ));
        $fgMembershipLogData = array('contactId' => $this->contactId, 'membershipHistoryId' => $this->membershipHistoryId, 'value' => $this->value, 'fieldName' => $this->fieldName);
        $errors = $this->container->get('validator')->validate($fgMembershipLogData, $collectionConstraint);
        if (count($errors) !== 0) {
            $output = array('valid' => 'false', 'msg' => $errors[0]->getMessage());
        } else {
            $output = array('valid' => 'true', 'msg' => $this->container->get('translator')->trans($updateSuccessMessage));
        }

        return $output;
    }
}
