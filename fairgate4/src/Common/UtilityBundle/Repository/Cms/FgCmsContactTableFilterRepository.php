<?php

/**
 * FgCmsContactTableFilterRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgCmsContactTableFilterRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgCmsContactTableFilterRepository extends EntityRepository
{

    /**
     * This method is used to fetch table filter data
     *  
     * @param   int     $tableId    contact table id.
     * @param   string  $lang       Current default language.
     * 
     * @return  array   $result
     */
    public function getTablefilterData($tableId, $lang)
    {
        $result = $this->createQueryBuilder('cf')
            ->select("cf.filterType, CASE WHEN cfi18n.titleLang = '' OR cfi18n.titleLang is null  then cf.title else cfi18n.titleLang end as title, "
                . "IDENTITY(cf.attribute) as attribute, cf.sortOrder, cf.filterSubtypeIds as filterSubtype")
            ->leftJoin('CommonUtilityBundle:FgCmsContactTableFilterI18n', 'cfi18n', 'WITH', 'cfi18n.id = cf.id AND cfi18n.lang =:lang')
            ->where('cf.table = :tableId')
            ->andWhere('cf.isDeleted = :isDeleted')
            ->setParameters(array('tableId' => $tableId, 'lang' => $lang, 'isDeleted' => 0))
            ->getQuery();

        return $result->getArrayResult();
    }

    /**
     * Function to save the contact table filter data
     * 
     * @param array  $dataArray The array of form field that needed to be saved
     * @param int    $tableId   The id of the contact tabe to which the filter is added
     * @param string $container The container object
     * @param int    $stage     wizard stage
     * 
     * @return int $formFieldId Newly inserted form field id
     */
    public function saveContactFilterData($dataArray, $tableId, $container, $stage)
    {
        $clubObj = $container->get('club');
        $clubDefaultLang = $clubObj->get('club_default_lang');
        $conn = $this->_em->getConnection();
        $tableObj = $this->_em->getReference('CommonUtilityBundle:FgCmsContactTable', $tableId);

        foreach ($dataArray as $filterType => $filterData) {
            foreach ($filterData as $filterId => $filter) {
                $filterColumnObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsContactTableFilter')->find($filterId);
                if ($filterColumnObj == '') {
                    $filterColumnObj = new \Common\UtilityBundle\Entity\FgCmsContactTableFilter();
                    $filterColumnObj->setTable($this->_em->getReference('CommonUtilityBundle:FgCmsContactTable', $tableId));
                    $filterColumnObj->setFilterType(strtolower($filterType));

                    if ($filterType == 'CONTACT_FIELD') {
                        $filterColumnObj->setAttribute($this->_em->getReference('CommonUtilityBundle:FgCmAttribute', $filter['attributeId']));
                    } else {
                        $filterColumnObj->setFilterSubtypeIds($filter['attributeId']);
                    }
                }
                (isset($filter['is_deleted'])) ? $filterColumnObj->setIsDeleted($filter['is_deleted']) : '';
                (isset($filter['sortOrder'])) ? $filterColumnObj->setSortOrder($filter['sortOrder']) : '';
                $filterTitle = (isset($filter['title'][$clubDefaultLang])) ? FgUtility::getSecuredDataString($filter['title'][$clubDefaultLang], $conn) : '';
                $filterColumnObj->setTitle($filterTitle);
                $this->_em->persist($filterColumnObj);
                $this->_em->flush();
                $filterColumnId = $filterColumnObj->getId();

                $this->_em->getRepository('CommonUtilityBundle:FgCmsContactTableFilterI18n')->saveContactTableFiltersI18n($filterColumnId, $filter['title']);
            }
        }

        $this->_em->getRepository('CommonUtilityBundle:FgCmsContactTable')->setContactTableElementStage($tableObj, $stage);

        return true;
    }

    /**
     * This method to get the filter data array for edit
     *  
     * @param   int     $tableId    contact table id.
     * 
     * @return  array   $result
     */
    public function getTableFilterDataArray($tableId)
    {
        $filterDataArray = array();
        $result = $this->createQueryBuilder('F')
                ->select("F.id AS filterId, F.filterType, F.title,IDENTITY(F.attribute) AS attrId, F.filterSubtypeIds, F.sortOrder, IDENTITY(A.attributeset) AS catId, FI18n.lang, FI18n.titleLang")
                ->leftJoin('CommonUtilityBundle:FgCmsContactTableFilterI18n', 'FI18n', 'WITH', 'FI18n.id = F.id')
                ->leftJoin('CommonUtilityBundle:FgCmAttribute', 'A', 'WITH', 'F.attribute = A.id')
                ->where('F.table = :tableId')
                ->andWhere('F.isDeleted = 0')
                ->orderBy('F.sortOrder', 'ASC')
                ->setParameters(array('tableId' => $tableId))
                ->getQuery()->getArrayResult();

        foreach ($result as $filter) {
            if (!is_array($filterDataArray['F-' . $filter['filterId']])) {
                $filterDataArray['F-' . $filter['filterId']] = array_slice($filter, 0, -2);
            }
            $filterDataArray['F-' . $filter['filterId']]['titleLang'][$filter['lang']] = $filter['titleLang'];
        }
        return $filterDataArray;
    }
}