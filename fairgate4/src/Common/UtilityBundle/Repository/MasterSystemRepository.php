<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MasterSystemRepository extends EntityRepository
{

    public function testMasterSystem($contactId)
    {
//        select qry
        $result = $this->createQueryBuilder('ms')
                ->select("ms.firstName, ms.lastName")
                ->where("ms.fedContact=:contactId")
                ->setParameters(array('contactId' => $contactId))
                ->getQuery()
                ->getArrayResult();

        echo '<pre>';print_r($result);
        echo '----------****************---------';


//        update qry 1
        $obj = $this->_em->getRepository('CommonUtilityBundle:MasterSystem')->findOneBy(array('fedContact' => $contactId));
        $obj->setFirstName('Jennifer');
        $obj->setLastName('Lopez');
        $this->_em->persist($obj);
        $this->_em->flush();

//        update qry 2
        $q = $this->createQueryBuilder()
                ->update('CommonUtilityBundle:MasterSystem', 'ms')
                ->set('ms.firstName', ':firstName')
                ->set('ms.lastName', ':lastName')
                ->where("ms.fedContact=:contactId")
                ->setParameters(array('contactId' => $contactId, ':firstName' => 'Jennifer1', ':lastName' => 'Lopez1'))
                ->getQuery();
        $res = $q->execute();
        
        
        
        echo '----------****************----------';
        exit;
    }

    /**
     * This function is used to check whether federation members with same email exist in that club/federation
     * 
     * @param int    $clubId       Federation/Club id
     * @param int    $fedContactId Fed contact id
     * @param string $email        Email
     * 
     * @return int $fedMemberExist Count of fed members with same email
     */
    public function checkForFedMembersWithSameEmail($clubId, $fedContactId, $email)
    {
        $qb = $this->createQueryBuilder('MS')
                   ->select('COUNT(C.id) AS fedCount')
                   ->innerJoin("CommonUtilityBundle:FgCmContact", "C", "WITH", "MS.fedContact = C.id AND C.club = :clubId")
                   ->where('lower(MS.primaryEmail) = lower(:email)')
                   ->andWhere('MS.fedContact != :fedContactId')
                   ->andWhere('C.fedMembershipCat IS NOT NULL')
                   ->andWhere('C.isFedMembershipConfirmed = :isConfirmed')
                   ->setParameters(array('clubId' => $clubId, 'fedContactId' => $fedContactId, 'email' => $email, 'isConfirmed' => '0'));
        $fedMemberExist = $qb->getQuery()->getSingleScalarResult();

        return $fedMemberExist;
    }
    
    /**
     * This function is used to get the primary email of a contact
     * 
     * @param int $contactId Fed contact id
     * 
     * @return string $email Email field
     */
    public function getPrimaryEmail($contactId)
    {
        $email = $this->createQueryBuilder('ms')
            ->select("ms.primaryEmail")
            ->where("ms.fedContact=:contactId")
            ->setParameters(array('contactId' => $contactId))
            ->getQuery()
            ->getSingleScalarResult();
        
        return $email;
    }
    /**
  * 
  * @param int $contactId
  * @param String $profileImagefield  db field name of profile picture
  * @param String $companyLogofield   db field name of company logo
  * @return type
  */
    public function getAvatarImage($contactId,$profileImagefield,$companyLogofield) {
        if ($contactId != '') {
            $conn = $this->getEntityManager()->getConnection();
            $sql = 'SELECT  IF (c.is_company = 1, `' . $companyLogofield . '`, `' . $profileImagefield . '`) AS avatar, IF (c.is_company = 1, "1","0") AS isCompany FROM master_system ms INNER JOIN fg_cm_contact c ON  c.id= ms.fed_contact_id WHERE c.id  = :contactId';
             $result = $conn->fetchAll($sql, array(':contactId' => $contactId));

            if (count($result)) {
            return $result[0];
        } else {
            return array();
        }
        }

        return; 
    }
    
    /**
     * This function is used to get the profile picture details of a contact
     * 
     * @param int $fedContactId Fed contact id
     * 
     * @return array $result Result array
     */
    public function getProfilePicDetails($fedContactId)
    {
        $result = $this->createQueryBuilder('MS')
                ->select("C.isCompany, (CASE WHEN C.isCompany = 1 THEN MS.companyLogo ELSE MS.clubProfilePicture END) AS fileName")
                ->innerJoin("CommonUtilityBundle:FgCmContact", "C", "WITH", "MS.fedContact = C.id")
                ->where("MS.fedContact=:contactId")
                ->setParameters(array('contactId' => $fedContactId))
                ->getQuery()
                ->getSingleResult();

        return $result;
    }
}
