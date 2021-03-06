<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;

/**
 * UserLoginRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SfGuardUserRepository extends EntityRepository
{

    /**
     * Function to get properties.
     *
     * @param Int $userId User id
     *
     * @return array
     */
    public function getContactDetails($userId)
    {
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $doctrineConfig->addCustomStringFunction('contactNameNoSort', 'Common\UtilityBundle\Extensions\FetchContactNameNoSort');
        $qb = $this->createQueryBuilder('sf')
            ->select('c.id,contactName(c.id) as contactname', 'c.id,contactNameNoSort(c.id 0) as contactnamenosort', 'sf.email', 'c.isCompany', 'fedC.id as fedContactId', 'subfedC.id as subfedContactId', "CASE WHEN (c.isFedMembershipConfirmed = '0' AND fedMemCat.id IS NOT NULL ) THEN 1 ELSE 0 END as isFedCategory")
            ->leftJoin('sf.contact', 'c')
            ->leftJoin('c.fedContact', 'fedC')
            ->leftJoin('c.subfedContact', 'subfedC')
            ->leftJoin('c.fedMembershipCat', 'fedMemCat')
            ->where('sf.id=:userId')
            ->setParameter('userId', $userId);
        $result = $qb->getQuery()->getResult();

        return $result[0];
    }

    /**
     * Function to get contact name.
     *
     * @param Int $userId User id
     *
     * @return array
     */
    public function getContactname($userId)
    {
        $qb = $this->createQueryBuilder('sf')
            ->select('sf.firstName', 'sf.lastName')
            ->leftJoin('sf.contact', 'c')
            ->where('sf.contact=:userId')
            ->setParameter('userId', $userId);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Function to update password of all users with same fed contact id.
     *
     * @param String $conn   Connection
     * @param Int    $userId User id
     *
     * @return array
     */
    public function alterPasswordByContactId($conn, $userId)
    {
        $qb = $this->createQueryBuilder('sf')
            ->select('GROUP_CONCAT(C2.id) AS contactIds', 'sf.password')
            ->innerJoin('sf.contact', 'C1')
            ->leftJoin('CommonUtilityBundle:FgCmContact', 'C2', 'WITH', 'C2.fedContact = C1.fedContact')
            ->where('sf.id=:userId')
            ->setParameter('userId', $userId);

        $result = $qb->getQuery()->getResult();

        $contactId = $result[0]['contactIds'];
        $password = $result[0]['password'];
        $updateQry = "UPDATE sf_guard_user s SET s.password='" . FgUtility::getSecuredData($password, $conn) . "',s.enabled='1' WHERE s.contact_id IN (" . FgUtility::getSecuredData($contactId, $conn) . ' )';
        $conn->executeQuery($updateQry);

        return;
    }

    /**
     * Function to update password of all users with same contact id.
     *
     * @param String $conn   Connection
     * @param Int    $userId User id
     *
     * @return array
     */
    public function checkNullPassword($conn, $userId)
    {
        $sql = 'SELECT s.password,s.contact_id
               FROM sf_guard_user s
               WHERE s.id= :userId';

        $result = $conn->fetchAll($sql, array('userId' => $userId));

        return $result;
    }

    /**
     * Function for custom logout.
     *
     * @param object $container   Container object
     * @param object $session     Session object
     * @param String $logout      Logout url
     * @param object $request     Request object
     * @param object $currentUser User object
     *
     * @throws AccessDeniedException
     */
    public function customLogoutTrigger($container, $session, $logout, $request, $currentUser = '')
    {
        $triggerLogout = true;
        $club = $container->get('club');
        if ($currentUser) {
            $superAdminFlag = $currentUser->getIsSuperAdmin();
            $hasFullPermission = $currentUser->getHasFullPermission();
            $sfGuardId = $currentUser->getId();
            $contactId = $this->getEntityManager()->getRepository('CommonUtilityBundle:SfGuardUser')->getContactDetails($sfGuardId);
            $accessibleClubDetails = $container->get('contact')->get('accessibleClubs');
            $accessibleClubs = array_keys($accessibleClubDetails);
            $ContactPdo = new ContactPdo($container);
            $fedAdminFlag = $ContactPdo->checkFedAdminContact($currentUser->getId(), $club->get('id'), $club->get('federation_id'));
        }
        $secure = strstr($request->getUri(), ':', true);
        if (isset($superAdminFlag) && $superAdminFlag != 1) {
            $loggedClubId = $currentUser->getClub()->getId();
            if ($currentUser) {
                if ($loggedClubId != $club->get('id') && $fedAdminFlag != 1) {

                    //lastLogin date of user in new club ($accessibleClubDetails[$club->get('id')]['contactId'] -> contact-id of user in new club )
                    $lastLoginInNewClub = ($accessibleClubDetails[$club->get('id')]['contactId']) ? $this->getLastLoginDate($accessibleClubDetails[$club->get('id')]['contactId']) : null;

                    //if new club id is in array of accessible clubs && last login of contact in new club is not null
                    if (in_array($club->get('id'), $accessibleClubs) && ($lastLoginInNewClub != null)) {
                        $applicationArea = $club->get('applicationArea');
                        $hasIntranetAccess = ($applicationArea == 'internal') ? $currentUser->getContact()->getIntranetAccess() : 1;
                        if ($hasIntranetAccess == 1) {
                            $triggerLogout = false;
                            $this->triggerLogin($container, $accessibleClubDetails[$club->get('id')]['contactId'], $club->get('id'));
                        }
                    }
                    if ($triggerLogout) {
                        $currentUri = $secure . '://' . $request->server->get('HTTP_HOST') . $logout;
                        header('Location:' . $currentUri);
                        exit;
                    }
                }
            } else {
                if ($loggedClubId != $club->get('id')) {
                    $currentUri = $secure . '://' . $request->server->get('HTTP_HOST') . $logout;
                    header('Location:' . $currentUri);
                    exit;
                }
            }
        } elseif (isset($superAdminFlag) && $superAdminFlag == 1 && $hasFullPermission != 1) {
            $specialClubArray = $this->getEntityManager()->getRepository('CommonUtilityBundle:SfGuardUserClub')->getClubDetails($sfGuardId, $club->get('id'));

            if (!in_array($club->get('id'), $specialClubArray, true)) {
                throw new AccessDeniedException('This user does not have access to this section.');
            }
        }

        return;
    }

    /**
     * Method to get lastLogin date of user in new club (when switchin club).
     *
     * @param int $newContactId contactid of user in new club
     *
     * @return string $lastLoginInNewClub (date string or null )
     */
    private function getLastLoginDate($newContactId)
    {
        $newContactObj = $this->getEntityManager()->getRepository('CommonUtilityBundle:FgCmContact')->find($newContactId);
        $lastLoginObj = $newContactObj->getLastLogin();
        $lastLoginInNewClub = ($lastLoginObj instanceof \DateTime) ? $lastLoginObj->format('Y-m-d H:i:s') : null;

        return $lastLoginInNewClub;
    }

    /**
     * Function to get existing contact id.
     *
     * @param Int $conn      Connection
     * @param Int $contactId ContactId
     *
     * @return array
     */
    public function getPasswordEmptyUsers($conn, $contactId)
    {
        $passQry = 'SELECT password FROM sf_guard_user WHERE contact_id=' . FgUtility::getSecuredData($contactId, $conn) . " AND (password !='' OR password != 'NULL') AND enabled=1 LIMIT 1 ";
        $existingPassword = $conn->executeQuery($passQry)->fetchAll();

        return $existingPassword;
    }

    /**
     * Function to get existing contact id.
     *
     * @param Int $conn     conn
     * @param Int $valueQry ValueQry
     *
     * @return array
     */
    public function insertNewSfUser($conn, $valueQry)
    {
        $sfGuardUserInsertQuery = 'INSERT INTO sf_guard_user (`first_name`,`last_name`,`username`,`username_canonical`,`email`,`email_canonical`,`password`,`created_at`,`updated_at`,`contact_id`,`club_id`,`enabled`,`roles`,`is_security_admin`,`is_readonly_admin`) VALUES ' . implode(',', $valueQry) . ';';
        $conn->executeQuery($sfGuardUserInsertQuery);

        return;
    }

    /**
     * Function to get salutation.
     *
     * @param Object $userObj User Obj
     * @param Object $club    Clubservice
     *
     * @return string
     */
    public function getSalutation($userObj, $club)
    {
        $contactObj = $userObj->getContact();
        $contactId = $contactObj->getId();
        $clubSystemLang = $club->get('default_system_lang');
        $clubDefaultLang = $club->get('default_lang');
        $clubId = $club->get('id');
        $conn = $this->getEntityManager()->getConnection();
        $storedprocedure = $conn->prepare("SELECT salutationText( $contactId, $clubId,'$clubSystemLang','$clubDefaultLang' )");
        $storedprocedure->execute();
        $results = $storedprocedure->fetchAll();
        foreach ($results[0] as $key => $val) {
            $salutation = $val;
        }

        return $salutation;
    }

    /**
     * Function to trigger login for a contact in a specific club.
     *
     * @param object $container Container object
     * @param int    $contactId ContactId
     * @param int    $clubId    ClubId
     *
     * @return RedirectResponse $redirectUrl RedirectUrl
     */
    private function triggerLogin($container = '', $contactId = '', $clubId = '')
    {
        if ($container != '' && $contactId != '' && $clubId != '') {
            $userObj = $this->getEntityManager()->getRepository('CommonUtilityBundle:SfGuardUser')->findOneBy(array('contact' => $contactId, 'club' => $clubId));
            if ($userObj) {
                //create token instance
                //2nd argument is password, but empty string is accepted
                //3rd argument is "firewall" name(be careful, not a "provider" name!!! though UsernamePasswordToken.php names it as "providerKey")
                $token = new UsernamePasswordToken($userObj, '', 'main', $userObj->getRoles());
                //set token instance to security context
                $container->get('security.token_storage')->setToken($token);

                //fire a login event
                $request = new Request();
                $event = new InteractiveLoginEvent($request, $token);
                $container->get('event_dispatcher')->dispatch('security.interactive_login', $event);
                //reset contact parameters, roles and uerrights after triggerring login
                $container->get('contact')->setContactParameters($userObj);
            }
        }
    }

    /**
     * Function to update last reminder.
     *
     * @param int $contactId ContactId
     * @param int $clubId    ClubId
     */
    public function updateLastReminder($contactId, $clubId)
    {
        $userObj = $this->getEntityManager()->getRepository('CommonUtilityBundle:SfGuardUser')->findOneBy(array('contact' => $contactId, 'club' => $clubId));
        if ($userObj) {
            $userObj->setLastReminder(new \DateTime('now'));
            $this->getEntityManager()->persist($userObj);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Function to get sf_guard_user details from contact id.
     *
     * @param Int $contactId Contact id
     *
     * @return array
     */
    public function getSfGuardUserDetails($contactId)
    {
        $qb = $this->createQueryBuilder('sf')
            ->select('sf.id as sfGuardUserId')
            ->where('sf.contact=:contactId')
            ->setParameter('contactId', $contactId);

        $result = $qb->getQuery()->getResult();

        if (count($result) > 0) {
            return $result[0];
        } else {
            return;
        }
    }

    /**
     * This function is used to insert sf guard entry for a contact in particular clubs.
     *
     * @param object $container Container object
     * @param int    $clubId    Club id
     * @param int    $newClubId New Club id
     * @param int    $contactId Contact id
     * @param int    $contactId NewContact id
     */
    public function insertSfGuardEntry($container, $clubId, $newClubId, $contactId, $newContactId)
    {
        $contactPdo = new Pdo\ContactPdo($container);
        $contactPdo->insertToUserTable($clubId, $newClubId, $contactId, $newContactId);
    }

    /**
     * Function to get the details of superadmins for notofocation mailing
     * 
     * @param onject $conn
     * @param string $clubDefaultLanguage
     * @param string $contactDefaultLanguage
     * @param int $emailField
     * 
     * @return array $superAdminList
     */
    public function getSuperAdminForNotification($conn, $clubDefaultLanguage, $contactDefaultLanguage, $emailField)
    {
        $query = "SELECT CONCAT(first_name, ' ' ,last_name) AS name, salutationText( contact_id, club_id, '$clubDefaultLanguage','$contactDefaultLanguage') AS salutationText, `$emailField` AS email 
                    FROM sf_guard_user 
                    INNER JOIN master_system ON fed_contact_id = contact_id 
                    WHERE is_super_admin = 1 ";
        $superAdminList = $conn->executeQuery($query)->fetchAll();

        return $superAdminList;
    }

    /**
     * Function is used to get the first 20 contacts of a federation club
     *
     * @param int $fId   Federation club id
     * @param int $count Count of results
     *
     * @return array
     */
    public function findSfGuardUserWithAuthCode($authCode)
    {
        $qb = $this->createQueryBuilder('sf');
        $qb->select('sf.id')
            ->where('sf.authCode=:authCode')
            ->setParameter('authCode', $authCode);

        $result = $qb->getQuery()->getResult();

        if (!empty($result)) {
            return $result[0];
        } else {
            return false;
        }
    }

    /**
     * Method to unset password of main contacts, when making club invalid
     * 
     * @param array $nonConfirmedClubs ids of nonConfirmedClubs after 7 days
     * 
     * @return boolean
     */
    public function unsetPasswordOfMainContacts($nonConfirmedClubs)
    {
        $mainContactIds = array_column($nonConfirmedClubs, 'mainContactId');
        $qb = $this->createQueryBuilder('USER');
        $que = $qb->update('CommonUtilityBundle:SfGuardUser', 'USER')
            ->set('USER.salt', ':null')
            ->set('USER.password', ':null')
            ->where('USER.contact IN (:mainContactIds)')
            ->setParameter('mainContactIds', $mainContactIds)
            ->setParameter('null', null)
            ->getQuery();
        $que->execute();

        return true;
    }

    /**
     * Method to set password for user registered
     * 
     * @param int    $contactId contact Id
     * @param string $password  password
     */
    public function setUserPassword($contactId, $password)
    {
        $user = $this->_em->getRepository('CommonUtilityBundle:SfGuardUser')->findOneBy(array('contact' => $contactId));
        $user->setPlainPassword($password);
        $user->setEnabled(1);
        $this->_em->flush();
    }
}
