<?php

namespace Common\UtilityBundle\Repository\Calendar;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgEmCalendarCategoryRepository
 *
 * @author pitsolutions
 */
class FgEmCalendarCategoryRepository extends EntityRepository {

    /**
     * Function to get all calendar Categories of a club
     *
     * @param int    $clubid    Club id
     * @param string    $defLang Default Language
     *
     * @return array $resultArray
     */
    public function getCalendarCategories($clubId, $defLang) {
        $result = $this->createQueryBuilder("cc")
                ->select("cc.id as id, (CASE WHEN (ccl.titleLang IS NULL OR ccl.titleLang = '') THEN cc.title ELSE ccl.titleLang END) as title")
                ->leftJoin('CommonUtilityBundle:FgEmCalendarCategoryI18n', 'ccl', 'WITH', '(cc.id = ccl.id AND ccl.lang = :defLang)')
                ->where('cc.club=:clubId')
                ->orderBy("cc.sortOrder")
                ->setParameter('clubId', $clubId)
                ->setParameter('defLang', $defLang)
                ->getQuery()
                ->getResult();
        
        return $result;
    }
    
    /**
     * Function to get all calendar Categories of a club having events
     *
     * @param int     $clubid      Club id
     * @param string  $defLang     Default Language
     * @param boolean $isHierarchy is $clubId is curren club id. (true for higher levels)
     *
     * @return array $result
     */
    public function getCalendarCategoriesWithEvents($clubId, $defLang, $isHierarchy = false) {        
        $result = $this->createQueryBuilder("cc")
                ->select("DISTINCT cc.id as id, (CASE WHEN (ccl.titleLang IS NULL OR ccl.titleLang = '') THEN cc.title ELSE ccl.titleLang END) as title")
                ->leftJoin('CommonUtilityBundle:FgEmCalendarCategoryI18n', 'ccl', 'WITH', '(cc.id = ccl.id AND ccl.lang = :defLang)')
                ->innerJoin('CommonUtilityBundle:FgEmCalendarSelectedCategories', 'SC', 'WITH', '( SC.category = cc.id )')
                ->innerJoin('CommonUtilityBundle:FgEmCalendarDetails', 'CD', 'WITH', '( SC.calendarDetails = CD.id AND CD.status !=2 )');
        if($isHierarchy) {
            $result = $result->innerJoin('CommonUtilityBundle:FgEmCalendar', 'C', 'WITH', '( CD.calendar = C.id AND C.shareWithLower = 1 )');
        }       
        $result = $result->where('cc.club=:clubId')
                ->orderBy("cc.sortOrder")
                ->setParameter('clubId', $clubId)
                ->setParameter('defLang', $defLang)
                ->getQuery()                
                ->getResult();
       
        return $result;
    }

    /**
     * Function to save calendar categories 
     *
     * @param int     $clubId        clubId
     * @param string  $defaultlang   default language
     *
     * @return array
     */
    public function categorySave($clubId, $defaultlang, $catArr, $clubLanguages, $container = NULL)
    {
        $conn = $container->get('database_connection');
        $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        foreach ($catArr as $id => $data) {
            $categoryobj = $this->find($id);
            if ($data['is_deleted'] == 1) {
                $this->deleteCategory($categoryobj);
                continue;
            }
            if (empty($categoryobj)) {
                $categoryobj = new \Common\UtilityBundle\Entity\FgEmCalendarCategory();
            }
            if (isset($data['title'][$defaultlang])) {
                $categoryobj->setTitle(FgUtility::getSecuredData($data['title'][$defaultlang], $conn));
            }
            if (isset($data['sort_order'])) {
                $categoryobj->setSortOrder($data['sort_order']);
            }
            $categoryobj->setClub($clubobj);
            $this->_em->persist($categoryobj);
            $this->_em->flush();

            $catId = $categoryobj->getId();
            $catTitle = $categoryobj->getTitle();
            $catIdobj = $this->find($catId);
            foreach ($clubLanguages as $lang) {
                $langdetails = $this->_em->getRepository('CommonUtilityBundle:FgEmCalendarCategoryI18n')->getupdateDetails($lang, $catId);
                if (!empty($langdetails)) {
                    if (array_key_exists($lang, ($data['title']))) {
                        $this->_em->getRepository('CommonUtilityBundle:FgEmCalendarCategoryI18n')->updateSingleLang($catId, $lang, $data['title'][$lang]);
                    }
                } else {
                    $langobj = new \Common\UtilityBundle\Entity\FgEmCalendarCategoryI18n();
                    $langobj->setId($catIdobj);
                    $langobj->setLang($lang);
                    $title = $data['title'][$lang];
                    $langobj->setTitleLang(FgUtility::getSecuredData($title, $conn));
                    $this->_em->persist($langobj);
                    $this->_em->flush();
                }
            }            
        }
        $this->updateMainTable($defaultlang, $clubId);
        $result = array('catId'=>$catId,'catTitle'=>$catTitle);
        
        return $result;
    }

    /**
     * Function to get Calendar Category List
     *
     * @param int     $clubId        clubId
     *
     * @return array
     */
    public function getCategoryList($clubId) {
        $exec = true;
        $category = $this->createQueryBuilder('c')
                ->select("c.id,c.title,c.sortOrder,ci18.titleLang, ci18.lang")
                ->addSelect("(SELECT COUNT(distinct ca.id) FROM CommonUtilityBundle:FgEmCalendar ca LEFT JOIN CommonUtilityBundle:FgEmCalendarDetails cd WITH (cd.calendar = ca.id) LEFT JOIN CommonUtilityBundle:FgEmCalendarSelectedCategories sc WITH (sc.calendarDetails = cd.id) WHERE sc.category = c.id) appointmentCount")
                ->leftJoin('CommonUtilityBundle:FgEmCalendarCategoryI18n', 'ci18', 'WITH', 'ci18.id = c.id')
                ->where('c.club=:clubId')
                ->setParameters(array('clubId' => $clubId));
        $dataResult = $category->getQuery()->getArrayResult();

        if ($exec) {
            $result = array();
            $id = '';
            foreach ($dataResult as $key => $arr) {
                if (count($arr) > 0) {
                    if ($arr['id'] == $id) {
                        $result[$id]['titleLang'][$arr['lang']] = $arr['titleLang'];
                    } else {
                        $id = $arr['id'];
                        $result[$id] = array('id' => $arr['id'], 'title' => $arr['title'], 'sortOrder' => $arr['sortOrder'], 'appointmentCount' => $arr['appointmentCount']);

                        $result[$id]['titleLang'][$arr['lang']] = $arr['titleLang'];
                    }
                }
            }
            return $result;
        } else {
            return $dataResult;
        }
    }

    /**
     * Function to delete a category
     *
     * @param object  $catobj   cat obj
     *
     * @return boolean
     */
    public function deleteCategory($catobj) {
        $this->_em->remove($catobj);
        $this->_em->flush();
    }

    /**
     * Function to get sort order of calendar categories
     *
     * @param object  $clubId   club Id
     *
     * @return int   sort order
     */
    public function getCategoriesSortOrder($clubId) {
        $sortorder = $this->createQueryBuilder('c')
                ->select('MAX(c.sortOrder)')
                ->where('c.club=:clubId')
                ->setParameters(array('clubId' => $clubId));
        $result = $sortorder->getQuery()->getSingleScalarResult();
        
        return $result;
    }

    /**
     * Function to update maintable entry with clubdefault language entry
     *
     * @param int    $clubId  club id 
     * @param string $clubDefaultLang  Club default language
     * 
     * @return boolean
     */
    private function updateMainTable($clubDefaultLang, $clubId)
    {
        $mainFileds = array('title');
        $i18Fields = array('title_lang');
        $fieldsList = array('mainTable' => 'fg_em_calendar_category',
            'i18nTable' => 'fg_em_calendar_category_i18n',
            'mainField' => $mainFileds,
            'i18nFields' => $i18Fields
        );
        $where = 'A.club_id = ' . $clubId;
        $updateMainTable = FgUtility::updateDefaultTable($clubDefaultLang, $fieldsList, $where);
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $conn->executeQuery($updateMainTable);

        return true;
    }

    /**
     * Function to get all calendar Categories of federation and sunfederation
     *
     * @param string  $clubId    fed/subfed Club ids
     *
     * @return array $sharedCatArr shared categories
     */
    public function getCalendarCategoryIds($clubId)
    {
        $catString = array();
        $result = $this->createQueryBuilder("cc")
            ->select(" cc.id as id")
            ->where('cc.club IN (' . $clubId . ')')
            ->orderBy("cc.sortOrder")
            ->getQuery()
            ->getResult();

        if (count($result) > 0) {
            foreach ($result as $value) {
                $catArr[] = $value['id'];
            }
            $catString = implode(",", $catArr);
        }
        return $catString;
    }
}
