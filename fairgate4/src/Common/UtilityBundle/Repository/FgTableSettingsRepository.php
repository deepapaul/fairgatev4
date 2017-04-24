<?php
/**
 * FgTableSettingsRepository
 *
 * This class is used for table column settings in contact administration.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgTableSettings;
/**
 * FgTableSettingsRepository
 *
 * This class is used for displaying and saving table column settings in contact administration.
 */
class FgTableSettingsRepository extends EntityRepository
{

    /**
     * Function to get table settings of a contact
     * @param int $clubId    Club id
     * @param int $contactId Contact id
     *
     * @return array $result Result array of table setting data.
     */
    public function getTableSettings($clubId, $contactId)
    {
        $qb = $this->createQueryBuilder('ts')
                ->select('ts.attributes', 'ts.rows')
                ->leftJoin('ts.contact', 'fc')
                ->leftJoin('ts.club', 'cl')
                ->where("fc.id=:contactId")
                ->andWhere("cl.id=:clubId")
                ->setParameters(array('contactId' => $contactId, 'clubId' => $clubId));

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Function to get current table settings
     * @param int    $clubId    Club id
     * @param int    $contactId Contact id
     * @param string $type      Settings type
     *
     * @return array $tableSettings Array of Table Settings Data.
     */
    public function getCurrentTableSettings($clubId, $contactId, $type = 'DATA')
    {
        $tsObj = $this->createQueryBuilder('ts')
                ->select('ts.id, ts.attributes')
                ->where("ts.club=:clubId")
                ->andWhere("ts.contact=:contactId")
                ->andWhere("ts.type=:type")
                ->andWhere("ts.isTemp=1")
                ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId, 'type' => $type));

        $result = $tsObj->getQuery()->getResult();

        if (count($result)) {
            $tableSettings = $result[0];
        } else {
            global $kernel;
            $container = $kernel->getContainer();
            $tableSettings['id'] = '';
            $tableSettings['attributes'] = $container->getParameter('default_table_settings');
        }

        return $tableSettings;
    }

    /**
     * Function to get all table settings of a club
     * @param int    $clubId    Club id
     * @param int    $contactId Contact id
     * @param string $type      Settings type
     *
     * @return array $result Result array of Table Settings.
     */
    public function getAllTableSettings($clubId, $contactId, $type = 'DATA')
    {
        $tsObj = $this->createQueryBuilder('ts')
                ->select('ts.id, ts.title, ts.isTemp, ts.attributes')
                ->where("ts.club=:clubId")
                ->andWhere("ts.contact=:contactId")
                ->andWhere("ts.type=:type")
                ->orderBy('ts.title', 'ASC')
                ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId, 'type' => $type));

        $result = $tsObj->getQuery()->getResult();

        return $result;
    }
    
     /**
     * Function to add new table settings
     *
     * @param String  $title      Title of table settings
     * @param Array   $attributes Array of attributes
     * @param Object  $contactobj Object of contact
     * @param Integer $isTemp     Istemp 0/1
     * @param Object  $clubobj    Object of club
     * @param enum $type type of tablesetting
     * @return Boolean true
     */
    public function addNewTableSettings($title, $attributes, $contactobj, $isTemp, $clubobj, $type = 'DATA') {
        $tableSettingsObj = new FgTableSettings();
        $tableSettingsObj->setTitle($title)
                ->setAttributes($attributes)
                ->setContact($contactobj)
                ->setClub($clubobj)
                ->setIsTemp($isTemp)
                ->setType($type);
        $this->getEntityManager()->persist($tableSettingsObj);
        $this->getEntityManager()->flush();

        return $tableSettingsObj->getId();
    }

    /**
     * Function to update table settings
     *
     * @param Object $tableObj   Object of Tablesettings
     * @param Array  $attributes Array of attributes
     *
     * @return Boolean true
     */
    public function updateTableSettings($tableObj, $attributes)
    {
        $tableObj->setAttributes($attributes);
        $this->getEntityManager()->persist($tableObj);
        $this->getEntityManager()->flush();

        return true;
    }
    /**
     * Function to delete table settings
     *
     * @param Object $tableObj Object of Tablesettings
     *
     * @return Boolean true
     */
    public function deleteTableSettings($tableObj)
    {
        $this->getEntityManager()->remove($tableObj);
        $this->getEntityManager()->flush();

        return true;
    }

}
