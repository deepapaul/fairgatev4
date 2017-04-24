<?php
/*
 * This class to sync the data from the fairgate DB to FAdmin DB
 * 
 */
namespace Common\UtilityBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgMySqlSyncData;

/**
 * This class to sync the data from the fairgate DB to FAdmin DB.
 *
 * @author pitsolutions <pitsolutions.ch>
 */
class FgContactSyncDataToAdmin
{

    /**
     * @var object Container variable 
     */
    public $container;

    /**
     * @var object Connection variable for admin connection
     */
    private $adminConnection;

    /**
     * @var object Connection variable for fairgate connection
     */
    private $connection;

    /**
     * @var object Entity variable for fairgate connection
     */
    private $em;

    /**
     * @var string The query array
     */
    private $queryString = array();

    /**
     * Constructor of FgAdminConnection class.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->adminConnection = new FgMySqlSyncData($container);
        $this->em = $this->container->get('doctrine')->getManager();
        $this->adminEntityMgr = $this->container->get('doctrine.orm.admin_entity_manager');
        $this->adminCon = $this->adminEntityMgr->getConnection();
        $this->connection = $this->em->getConnection();
    }

    /**
     * The function to update the name of a contact
     * 
     * @param array/int $contactId
     * 
     * @return \Common\UtilityBundle\Util\FgContactSyncDataToAdmin
     */
    public function updateContactName($contactId)
    {
        if ($contactId != '') {
            $this->queryString[] = $this->getNameSyncQuery($contactId);
        }
        return $this;
    }

    /**
     * The function to update the last club updated date
     * 
     * @param array/int $clubId
     * 
     * @return \Common\UtilityBundle\Util\FgContactSyncDataToAdmin
     */
    public function updateLastUpdated($clubId)
    {
        if ($clubId != '') {
            $this->queryString[] = $this->getLastUpdatedSyncQuery($clubId);
        }

        return $this;
    }

    /**
     * The fucntion to add the new user to the admin DB when userright is given
     * @param type $contactId
     * 
     * @return \Common\UtilityBundle\Util\FgContactSyncDataToAdmin
     */
    public function updateUserRights($contactId)
    {
        if ($contactId != '') {
            $this->queryString[] = $this->getUserRightsQuery($contactId);
        }

        return $this;
    }

    /**
     * The function to update the last admin logged date
     * 
     * @param array/int $clubId
     * @param string    $dateToday date time
     * 
     * @return \Common\UtilityBundle\Util\FgContactSyncDataToAdmin
     */
    public function updateLastAdminLogged($clubId, $dateToday)
    {
        if ($clubId != '') {
            $this->queryString[] = $this->getLastAdminLoggedSyncQuery($clubId, $dateToday);
        }

        return $this;
    }


    /**
     * The function to update the last admin logged contactId
     * 
     * @param array/int $clubId
     * @param int    $contactId ContactId
     * 
     * @return \Common\UtilityBundle\Util\FgContactSyncDataToAdmin
     */
    public function getLastAdminLoggedInUpdate($clubId, $contactId)
    {
        if ($clubId != '') {
            $this->queryString[] = $this->updateLastAdminSyncQuery($clubId, $contactId);
        }

        return $this;
    }

    /**
     * The function to update the last admin logged contactId
     * 
     * @param array/int $clubId
     * 
     * @return \Common\UtilityBundle\Util\FgContactSyncDataToAdmin
     */
    public function getMainContactLoginCount($clubId, $contactId)
    {
        if ($clubId != '') {
            $this->queryString[] = $this->updateMainContactLoginCount($clubId);
        }

        return $this;
    }

    /**
     * The function to update the last admin logged contactId
     * 
     * @param array/int $clubId
     * 
     * @return \Common\UtilityBundle\Util\FgContactSyncDataToAdmin
     */
    public function getAllContactLoginCount($clubId, $contactId)
    {
        if ($clubId != '') {
            $this->queryString[] = $this->updateAllContactLoginCount($clubId);
        }

        return $this;
    }

    /**
     * This function will executequery in the Admin DB
     */
    public function executeQuery()
    {
        if (count($this->queryString) > 0) {
            $queryString = implode('', $this->queryString);
            $this->adminConnection->executeQuery($queryString);
        }
    }

    /**
     * 
     * @param array/int $contactId
     * @return string
     */
    private function getNameSyncQuery($contactId)
    {
        $queryString = '';
        if (!is_array($contactId)) {
            $contactId = array($contactId);
        }
        $queryStatement = $this->connection->executeQuery('SELECT c.id, contactName(c.id) AS name, s.`2` AS firstname, s.`23` AS lastname ,s.`86` AS mobile ,s.`3` AS email FROM `fg_cm_contact` c INNER JOIN master_system s ON s.fed_contact_id = c.fed_contact_id WHERE c.id IN (?) OR c.subfed_contact_id IN (?) OR c.fed_contact_id IN (?)', array($contactId, $contactId, $contactId), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
        $contactData = $queryStatement->fetchAll();
        foreach ($contactData as $contact) {
            $queryString.= "UPDATE fg_cm_contact SET name = '" . $contact['name'] . "', firstname = '" . $contact['firstname'] . "',lastname = '" . $contact['lastname'] .
                "',mobile_number = '" . $contact['mobile'] . "',email='" . $contact['email'] . "'  WHERE id = " . $contact['id'] . ";";
        }

        return $queryString;
    }
    /**
     * function to build update query for last admin logged
     * 
     * @param int $clubId
     * @param string $dateToday date
     * 
     * @return string
     */
    private function getLastAdminLoggedSyncQuery($clubId, $dateToday)
    {
        $queryString = "UPDATE fg_club SET last_admin_login = '" . $dateToday . "' WHERE id = " . $clubId . ";";

        return $queryString;
    }

    /**
     * 
     * @param int $clubId
     * @return string
     */
    private function getLastUpdatedSyncQuery($clubId)
    {
        $queryString = '';
        $queryStatement = $this->connection->executeQuery('SELECT contact.last_updated FROM fg_cm_contact contact INNER JOIN fg_club club ON club.id = contact.club_id WHERE club.id = ? OR club.federation_id = ? OR club.sub_federation_id = ? ORDER BY last_updated DESC LIMIT 1', array($clubId, $clubId, $clubId), array(\PDO::PARAM_INT, \PDO::PARAM_INT, \PDO::PARAM_INT));
        $lastUpdatedData = $queryStatement->fetchAll();
        if (is_array($lastUpdatedData)) {
            $lastUpdatedDate = $lastUpdatedData[0]['last_updated'];
            $queryString.= "UPDATE fg_club SET last_contact_updated = '" . $lastUpdatedDate . "' WHERE id = " . $clubId . " OR federation_id = " . $clubId . " OR sub_federation_id = " . $clubId . ";";
        }
        return $queryString;
    }

    /**
     * @param array/int $contactId
     * @return string
     */
    private function getUserRightsQuery($contactId)
    {
        $queryString = '';
        if (!is_array($contactId)) {
            $contactId = array($contactId);
        }
        $query = "SELECT c.id, contactName(c.id) AS name,c.club_id,c.main_club_id, s.`2` AS firstname, s.`23` AS lastname ,s.`86` AS mobile ,s.`3` AS email FROM fg_cm_contact c
                    INNER JOIN master_system s ON s.fed_contact_id = c.fed_contact_id
                    INNER JOIN sf_guard_user ON sf_guard_user.contact_id = c.fed_contact_id 
                    INNER JOIN sf_guard_user_group ON sf_guard_user_group.user_id = sf_guard_user.id AND sf_guard_user_group.group_id NOT IN (1,7,8)
                    WHERE c.id IN (?) OR c.subfed_contact_id IN (?) OR c.fed_contact_id IN (?)
                    UNION
                    SELECT c.id, contactName(c.id) AS name,c.club_id,c.main_club_id, s.`2` AS firstname, s.`23` AS lastname ,s.`86` AS mobile ,s.`3` AS email FROM fg_cm_contact c
                    INNER JOIN master_system s ON s.fed_contact_id = c.fed_contact_id
                    WHERE c.id IN (?) OR c.subfed_contact_id IN (?) OR c.fed_contact_id IN (?) AND is_fed_admin = 1";
        $paramObjType = \Doctrine\DBAL\Connection::PARAM_INT_ARRAY;
        $queryStatement = $this->connection->executeQuery($query, array($contactId, $contactId, $contactId, $contactId, $contactId, $contactId), array($paramObjType, $paramObjType, $paramObjType, $paramObjType, $paramObjType, $paramObjType));
        $contactData = $queryStatement->fetchAll();
        foreach ($contactData as $contact) {
            $queryString.= "INSERT INTO fg_cm_contact (id,club_id,main_club_id,name,firstname,lastname,mobile_number,email) VALUES (" . $contact['id'] . "," . $contact['club_id'] . "," . $contact['main_club_id'] . ",'" . $contact['name'] . "','" . $contact['firstname'] . "','" . $contact['lastname'] . "','" . $contact['mobile'] . "','" . $contact['email'] . "') " .
                "ON DUPLICATE KEY UPDATE name = '" . $contact['name'] . "', firstname = '" . $contact['firstname'] . "', lastname = '" . $contact['lastname'] . "', mobile_number = '" . $contact['mobile'] . "', email = '" . $contact['email'] . "';";
        }
        return $queryString;
    }

    /**
     * function to build update query for last admin logged
     * 
     * @param int $clubId
     * @param int  $contactId contactId
     * @param int $clubId
     * 
     * @return string
     */
    private function updateLastAdminSyncQuery($clubId, $contactId)
    {
        $queryString = "UPDATE fg_club SET  last_login_admin_contact = '" . $contactId . "' WHERE id = " . $clubId . ";";

        return $queryString;
    }

    /**
     * function to build update count query for household contact
     * 
     * @param int $clubId
     *  @param int  $contactId contactId
     * 
     * @return string
     */
    private function updateMainContactLoginCount($clubId)
    {
        $queryString = "UPDATE fg_club SET main_contact_login_count = main_contact_login_count + 1 WHERE id = " . $clubId . " ";

        return $queryString;
    }

    /**
     * function to build update  count query for all contact
     * 
     * @param int $clubId
     * 
     * @return string
     */
    private function updateAllContactLoginCount($clubId)
    {
         $queryString = "UPDATE fg_club SET contact_login_count = contact_login_count + 1 WHERE id = " . $clubId . ";";

        return $queryString;
    }
    
    /**
     * function to check solution Admin
     * 
     * @param int $contactId
     * 
     * @return array
     */
    public function checkSolutionContact($contactId)
    {
        $result = $this->adminCon->fetchAll("SELECT fairgate_solution_contact FROM fg_club  WHERE fairgate_solution_contact=$contactId");
    

        return $result;
    }
    
    


}
