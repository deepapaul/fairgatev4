<?php
namespace Common\UtilityBundle\Repository;
use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgClubTableSettings;

/**
 * FgClubTableSettingsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgClubTableSettingsRepository extends EntityRepository
{

    /**
     * Function to get all table settings of a club
     *
     * @param Integer $clubId    Club id
     * @param Integer $contactId Contact id
     *
     * @return Array  $result    Result array of Club Table Settings
     *
     */

    public function getAllClubTableSettings($clubId, $contactId)
    {
        $tsObj = $this->createQueryBuilder('ts')
                ->select('ts.id, ts.title, ts.isTemp, ts.attributes')
                ->where("ts.club=:clubId")
                ->andWhere("ts.contact=:contactId")
                ->orderBy('ts.title', 'ASC')
                ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId));

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
     *
     * @return Boolean true
     */
    public function addNewTableSettings($title, $attributes, $contactobj, $isTemp, $clubobj)
    {
        $tableSettingsObj = new FgClubTableSettings();
        $tableSettingsObj->setTitle($title)
                ->setAttributes($attributes)
                ->setContact($contactobj)
                ->setClub($clubobj)
                ->setIsTemp($isTemp);
        $this->getEntityManager()->persist($tableSettingsObj);
        $this->getEntityManager()->flush();

        return true;
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

