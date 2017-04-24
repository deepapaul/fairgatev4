<?php

/**
 *
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;

/**
 * This repository manages the external appication functionality
 *
 * @author pitsolutions.ch
 */
class FgExternalApplicationFormRepository extends EntityRepository
{

	/**
	 * Function to save the external application form
	 *
	 * @param int   $clubId    current club id
	 * @param array $formData  form data to be saved
	 *
	 * @return int $extId external application form table id
	 */
	public function saveExternalApplicationForm($clubId, $formData)
	{
		$clubObj = $this->_em->getReference('CommonUtilityBundle:Fgclub', $clubId);
		$membershipObj = $this->_em->getReference('CommonUtilityBundle:FgCmMembership', $formData['FedMembership']);
		$externalFormobj = new \Common\UtilityBundle\Entity\FgExternalApplicationForm();        
		$externalFormobj->setFirstName($formData['FirstName']);
		$externalFormobj->setLastName($formData['LastName']);
		$externalFormobj->setGender($formData['Gender']);
		$date = new \DateTime();
		$dob = $date->createFromFormat(FgSettings::getPhpDateFormat(), $formData['Dob']);
		$externalFormobj->setDob($dob);
		$externalFormobj->setEmail($formData['Email']);
		if ($formData['street']) {
			$externalFormobj->setStreet($formData['street']);
		}
		if ($formData['zipcode']) {
			$externalFormobj->setZipcode($formData['zipcode']);
		}
		if ($formData['location']) {
			$externalFormobj->setLocation($formData['location']);
		}
		if ($formData['telM']) {
			$externalFormobj->setTelM($formData['telM']);
		}
		if ($formData['telG']) {
			$externalFormobj->setTelG($formData['telG']);
		}
		if ($formData['relatives']) {
			$externalFormobj->setRelatives($formData['relatives']);
		}
		if ($formData['comment']) {
			$externalFormobj->setComment($formData['comment']);
		}
		if ($formData['employer-radio'] == 'other') {
			$externalFormobj->setEmployer($formData['employer-other-text']);
		} else {
			$externalFormobj->setEmployer($formData['employer-radio']);
            $externalFormobj->setPersonalNumber($formData['employer-number-text']);            
		}
		$selectedClubs = implode(",", $formData['clubs']);
		$externalFormobj->setClubSelected($selectedClubs);
		$externalFormobj->setStatus('pending');
		$externalFormobj->setFedMembership($membershipObj);
		$externalFormobj->setCreatedDate(new \DateTime('now'));
		$externalFormobj->setClub($clubObj);

		$this->_em->persist($externalFormobj);
		$this->_em->flush();		
		 
		$extId = $externalFormobj->getId();
		 
		 return $extId;
	}

	/**
	 * Function to check whether a email id already exists or not for external application
	 *
	 * @param string $email   email to be checked
	 * @param int    $clubId  current club id
	 *
	 * @return int $returnData return data
	 */
	public function checkEmailExistsForExternalApplication($email, $clubId)
	{
		$emailCountQuery = "SELECT SUM(C.emailCount) AS mailCount FROM
					        ( SELECT count(*) AS emailCount FROM fg_external_application_form ex WHERE ex.club_id =:club AND ex.status ='pending' AND ex.email =:email
				              UNION ALL
                              SELECT count(*) AS emailCount FROM master_system ms
							  INNER JOIN fg_cm_contact cnt  ON ms.fed_contact_id = cnt.fed_contact_id AND cnt.club_id =:club
 							  WHERE ms.3 =:email

			                ) AS C ";

		$emailCount = $this->_em->getConnection()->prepare($emailCountQuery);
		$emailCount->bindValue('club', $clubId);
		$emailCount->bindValue('email', $email);
		$emailCount->execute();

		$result = $emailCount->fetchAll();
		$returnData = (empty($result) ? 0 : $result[0]['mailCount']);

		return $returnData;
	}

	/**
	 * Function to get the external application form listing data
	 *
	 * @param int    $clubId  current club id
	 * @param string $type    listing tab type
	 *
	 * @return array $result array result
	 */
	public function getApplicationConfirmationListData($clubId, $type)
	{
		$doctrineConfig = $this->getEntityManager()->getConfiguration();
		$doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
		$doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
		$doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');

		$contactName = " CONCAT(ex.lastName,', ',ex.firstName)";
		$andWhere = ($type == "list") ? "ex.status ='pending'" : "ex.status != 'pending'";
		$dateTimeFormatMysql = FgSettings::getMysqlDateTimeFormat();
		$contactNameSql = ", (CASE WHEN ((ex.status = 'pending' OR ex.status = 'discarded')) THEN $contactName  ELSE contactName(ex.fedContact) END ) AS contactName";
		$exApplQuery = $this->createQueryBuilder('ex')
			->select("ex.id as extId, ex.status, IDENTITY(ex.fedContact) AS contactId, IDENTITY(ex.decidedBy) AS decidedById, cnt.isCompany, checkActiveContact(ex.fedContact, $clubId) as isActiveContact, checkActiveContact(ex.decidedBy, $clubId) as isActiveDecidedContact,  (DATE_FORMAT(ex.createdDate, '$dateTimeFormatMysql')) as createdDate, fm.title AS membershipTitle, (DATE_FORMAT(ex.decisionDate, '$dateTimeFormatMysql')) as decisionDate, ex.employer AS Employer,  ex.relatives AS Relatives, ex.gender, contactName(ex.decidedBy) as decidedBy $contactNameSql")
			->addSelect("(SELECT GROUP_CONCAT(fgc.title ORDER BY fgc.title ASC SEPARATOR ', ') FROM CommonUtilityBundle:FgClub fgc WHERE FIND_IN_SET(fgc.id, ex.clubSelected) != 0) as existingClubs")
			->leftJoin('ex.fedMembership', 'fm')
			->leftJoin('ex.fedContact', 'cnt')
			->where('ex.club=:clubId')
			->andWhere($andWhere)
			->setParameter('clubId', $clubId);


		$result = $exApplQuery->getQuery()->getArrayResult();

		 return $result;
	}

	/**
	 * Function to get the external application form data for showing it in popup
	 *
	 * @param int $extId  external form table id
	 *
	 * @return array $returnData array result
	 */
	public function getExternalApplicationDataforPopup($extId)
	{
		$doctrineConfig = $this->getEntityManager()->getConfiguration();
		$doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
		$doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
		$doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
		$dateFormatMysql = FgSettings::getMysqlDateFormat();
		$contactName = " CONCAT(ex.lastName,' ',ex.firstName) AS contactName";
		$exApplQuery = $this->createQueryBuilder('ex')
			->select("ex.firstName, ex.lastName , ex.gender, ex.email, ex.street, ex.zipcode, ex.location, ex.telM as mobile,  ex.telG as telg,(DATE_FORMAT(ex.dob, '$dateFormatMysql')) as dob, fm.title AS membershipTitle, ex.employer , ex.personalNumber as personalNumber,  ex.relatives , $contactName, ex.comment")
			->addSelect("(SELECT GROUP_CONCAT(fgc.title ORDER BY fgc.title ASC SEPARATOR ', ') FROM CommonUtilityBundle:FgClub fgc WHERE FIND_IN_SET(fgc.id, ex.clubSelected) != 0) as selectedClubs")
			->leftJoin('ex.fedMembership', 'fm')
			->where('ex.id=:extId')
			->setParameter('extId', $extId);


		$result = $exApplQuery->getQuery()->getArrayResult();
		$returnData = (empty($result) ? $result : $result[0]);

	 	 return $returnData;
	}

	/**
	 * Function to get the external application confirmation count
	 *
	 * @param int $clubId  current club id
	 *
	 * @return int $count count
	 */
	public function getExternalApplicationConfirmationCount($clubId)
	{
		$exApplQuery = $this->createQueryBuilder('ex')
			->select("COUNT(ex.id) AS applCount")
			->where("ex.status ='pending'")
			->andWhere('ex.club=:clubId')
			->setParameter('clubId', $clubId);


		$result = $exApplQuery->getQuery()->getArrayResult();

		$count = (empty($result) ? 0 : $result[0]['applCount']);

		 return $count;
	}
    
    /**
     * Function to get Details from External Application Table
     * @param int  $clubId clubid
     * @param string $status status (pending,confirmed ,discard)
     * 
     * @return array
     */
    public function getExternalApplication($clubId,$status="pending"){
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT  id,first_name,last_name,if(gender ='male','Male','Female') as gender ,email,street,DATE_FORMAT(dob,'%d.%m.%Y') as dob,zipcode,location,tel_m,tel_g,relatives,employer,fed_membership,comment,club_selected,status,club_id FROM  fg_external_application_form  WHERE club_id=$clubId and status='$status' order by id asc limit 0,1";
        $result = $conn->fetchAll($sql);

        return $result;
        
    }
    
    /**
     * Function to update Application status of Application Id
     * @param int $applicationId external applicationId
     * @param string $status status (pending,confirmed ,discard
     * @param int $decidedBy  decidedBy
     * @param date $decidedDate decidedDate
     * @param int $fedContactId  createdContactId
     * @return int
     */
     
    public function updateApplicationStatus($applicationId,$status,$decidedBy,$fedContactId){
        $externalapObj = $this->_em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')->find($applicationId);
        $externalapObj->setStatus($status);
        $externalapObj->setDecisionDate(new \DateTime('now'));
        $externalapObj->setDecidedBy($this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($decidedBy));
        if($status=="confirmed"){
            
            $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->checkFederationMember($fedContactId);
            $fedMembership= $contactObj['isFedCategory'] ;
            if(isset($fedMembership)){
                $membershipObj = $this->_em->getReference('CommonUtilityBundle:FgCmMembership', $fedMembership);
                $externalapObj->setFedMembership($membershipObj);
            }
        }
        if(isset($fedContactId))
        $externalapObj->setFedContact($this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($fedContactId));
        $this->_em->persist($externalapObj);
        $this->_em->flush();

        return $externalapObj->getId();

   }
   /**
    * Check user for Merging and user exists in the federation
    * @param int  $fedId federation id
    * @param array $user user details arrau
    * @return array
    */
  
    public function  checkUserForMerge($fedId,$user,$container)
   {
      
        $conn = $this->getEntityManager()->getConnection();
        $firstname = $container->get('system_field_firstname');
        $lastname = $container->get('system_field_lastname');
        $dob = $container->get('system_field_dob');
        $land = $container->get('system_field_corres_ort');
        $primaryEmail = $container->get('system_field_primaryemail');
        $firstnameVal = FgUtility::getSecuredData($user['firstname'],$conn);
        $emailVal =  FgUtility::getSecuredData($user['email'],$conn);
        $lastnameVal =FgUtility::getSecuredData($user['lastname'],$conn);
        $dobVal =  $user['dob'];
        $landVal = FgUtility::getSecuredData($user['location'],$conn);
        $dobNull = " MS.`$dob`!='' AND MS.`$dob` IS NOT NULL AND MS.`$dob`!='0000-00-00' "; 
        $sql= "SELECT distinct C.club_id, fcm.title as membership,C.fed_membership_cat_id ,C.fed_contact_id,MS.$primaryEmail as email ,fcm.title as fedTitle, C.*,MS.*,contactName(C.fed_contact_id) AS contactName,(if (C.main_club_id = $fedId,1, 0)) fed_contact,(if (MS.$primaryEmail = '$emailVal',1, 0)) emailMatch , (SELECT GROUP_CONCAT(IF(CL.title !='', if(CL.id = CS.main_club_id,CONCAT(CL.title,'#mainclub#'),CL.title),'') SEPARATOR ', ') FROM fg_cm_contact CS INNER JOIN fg_club CL ON CL.id=CS.club_id WHERE CS.fed_contact_id=MS.fed_contact_id AND CL.club_type<>'federation' ) as clubs FROM master_system MS INNER JOIN `fg_cm_contact` C "
            . "  ON C.fed_contact_id=MS.fed_contact_id AND C.is_fed_membership_confirmed='0' "
            . " INNER JOIN fg_cm_membership fcm ON fcm.id=C.fed_membership_cat_id where  C.is_company='0' AND  lower(MS.$primaryEmail)=lower('".$emailVal."') "
             . " AND C.club_id=$fedId ";
        $mergeableEContacts = $conn->fetchAll($sql);
         if ( count($mergeableEContacts) < 1) {
             
              $sql_dup = "SELECT distinct C.club_id, fcm.title as membership,C.fed_membership_cat_id ,C.fed_contact_id,MS.$primaryEmail as email ,fcm.title as fedTitle, C.*,MS.*,contactName(C.fed_contact_id) AS contactName,(if (C.main_club_id = $fedId,1, 0)) fed_contact,(if (MS.$primaryEmail = '$emailVal',1, 0)) emailMatch ,(SELECT GROUP_CONCAT(IF(CL.title !='', if(CL.id = CS.main_club_id,CONCAT(CL.title,'#mainclub#'),CL.title),'') SEPARATOR ', ') FROM fg_cm_contact CS INNER JOIN fg_club CL ON CL.id=CS.club_id WHERE CS.fed_contact_id=MS.fed_contact_id  AND CL.club_type<>'federation' ) as clubs FROM master_system MS INNER JOIN `fg_cm_contact` C "
            . " ON C.fed_contact_id=MS.fed_contact_id AND C.is_fed_membership_confirmed='0'  "
            . " INNER JOIN fg_cm_membership fcm ON fcm.id=C.fed_membership_cat_id where  C.is_company='0' AND "
             ."  ((MS.`$firstname`='$firstnameVal' AND MS.`$lastname`='$lastnameVal' AND MS.`$dob`='$dobVal' AND $dobNull ) OR "
                    ."(MS.`$firstname`='$firstnameVal' AND MS.`$lastname`='$lastnameVal' AND MS.`$land`='$landVal' AND MS.`$land`!='') OR "
                    ."(MS.`$firstname`='$firstnameVal' AND MS.`$dob`='$dobVal' AND MS.`$land`='$landVal' AND MS.`$land`!='' AND $dobNull ) OR "
                    ."(MS.`$lastname`='$lastnameVal' AND MS.`$dob`='$dobVal' AND MS.`$land`='$landVal' AND MS.`$land`!='' AND $dobNull)) AND "
            . " C.club_id=$fedId ";
             
             $mergeableDuplContacts  = $conn->fetchAll($sql_dup);
         }
        

         return array('duplicates' => $mergeableDuplContacts, 'mergeEmail' => $mergeableEContacts);
   }
   
   
   
   /**
    * Check user Details
    * @param int  $fedId federation id
    * @param array $user user details arrau
    * @return array
    */
   public function  getExternalUsersDetails($clubId,$userIds)
   {
       
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT  concat (last_name, ', ', first_name)  as contactname, email,id,first_name as firstname,last_name as lastname,if(gender ='male','Male','Female') as gender,street,DATE(dob)as dob,zipcode,location,tel_m,tel_g as telg,relatives,employer,personal_number,fed_membership,comment,club_selected,status,club_id FROM  fg_external_application_form  WHERE club_id=$clubId AND status='pending' AND id IN (".implode(',',$userIds).") group by email";
        $result = $conn->fetchAll($sql);

        return $result;
   }
   
   
   /**
    * Check user Details
    * @param int  $fedId federation id
    * @param array $user user details arrau
    * @return array
    */
   public function  getContactNameExternalUsers($clubId,$userIds)
   {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT   concat (last_name, ', ', first_name)  as contactname,id FROM  fg_external_application_form  WHERE club_id=$clubId AND status='pending' AND id IN (".implode(',',$userIds).") group by email";
        $result = $conn->fetchAll($sql);
        $contactNames = array();
        foreach ($result as $resultData) {
            $contactNames[$resultData['id']] = $resultData['contactname'];
        }

        return $contactNames;

   }
 
}
