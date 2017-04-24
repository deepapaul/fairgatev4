<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;

/**
 * This repository is used for document sub categories
 *
 * @author pitsolutions.ch
 */
class FgDmDocumentSubcategoryRepository extends EntityRepository
{
    /**
     * Function to save a  document subcategory
     *
     * @param int     $clubId            clubId
     * @param string  $defaultlang       default languages
     * @param array   $catArr            result array
     * @param array   $clublanguages     clublanguages
     * @param int     $catId             category Id
     * @param array   $getId             Whether to return id
     *
     * @return boolean
     */
    public function subcategorySave($clubId, $defaultlang, $catArr, $clublanguages, $catId, $getId = false)
    {
        $clubobj = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
        $catObj = $this->_em->getReference('CommonUtilityBundle:FgDmDocumentCategory', $catId);
        foreach ($catArr as $id => $data) {

            $subcategoryobj = $this->find($id);
            if ($data['is_deleted'] == 1) {
                $this->deleteCategory($subcategoryobj);
                continue;
            }
            if (empty($subcategoryobj)) {
                $subcategoryobj = new \Common\UtilityBundle\Entity\FgDmDocumentSubcategory();
            }
            if (isset($data['title'][$defaultlang])) {
                $subcategoryobj->setTitle($data['title'][$defaultlang]);
            }
            if (isset($data['catType'])) {
                $subcategoryobj->setDocumentType(strtoupper($data['catType']));
            }

            if (isset($data['sortOrder'])) {
                $subcategoryobj->setSortOrder($data['sortOrder']);
            }

            $subcategoryobj->setClub($clubobj);
            $subcategoryobj->setCategory($catObj);

            $this->_em->persist($subcategoryobj);
            $this->_em->flush();
            $subcatId = $subcategoryobj->getId();

            foreach ($clublanguages as $lang) {
                $langobj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategoryI18n')->findOneBy(array('id' => $subcatId, 'lang' => $lang));
                if ($langobj) {
                    $title = (array_key_exists($lang, ($data['title']))) ? $data['title'][$lang] : $langobj->getTitleLang();
                } else {
                    $title = $data['title'][$lang];
                    $langobj = new \Common\UtilityBundle\Entity\FgDmDocumentSubcategoryI18n();
                }
                $langobj->setId($subcatId);
                $langobj->setLang($lang);
                $langobj->setTitleLang($title);
                $this->_em->persist($langobj);
            }
            $this->_em->flush();
        }
        $this->updateMainTable($defaultlang, $clubId);
        
        return ($getId) ? $subcatId : true;
    }

    /**
     * Function to get subcategory List
     *
     * @param int    $clubId document club Id
     * @param string $type   Document type CONTACT/CLUB/WORKGROUP/TEAM
     * @param int    $catId  Document category id
     *
     *
     * @return int/array
     */
    public function getsubCategoryList($clubId, $type, $catId)
    {
        $exec = true;
        $category = $this->createQueryBuilder('c')
                ->select("c.id,c.title,c.sortOrder, c.documentType,ci18.titleLang, ci18.lang,cat.id as catId")
                ->addSelect("(SELECT count(d.id) FROM CommonUtilityBundle:FgDmDocuments d WHERE d.club= $clubId AND d.subcategory = c.id AND d.documentType=c.documentType) documentCount")
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategoryI18n', 'ci18', 'WITH', 'ci18.id = c.id')
                ->leftJoin('CommonUtilityBundle:FgDmDocumentCategory', 'cat', 'WITH', 'cat.id = c.category')
                ->where('c.club=:clubId')
                ->andWhere('c.documentType=:type')
                ->andWhere('c.category=:catId')
                ->setParameters(array('clubId' => $clubId, 'type' => $type, 'catId' => $catId));
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
                        $result[$id] = array('id' => $arr['id'], 'title' => $arr['title'], 'sortOrder' => $arr['sortOrder'],
                            'documentType' => $arr['documentType'], 'documentCount' => $arr['documentCount'],'catId'=>$arr['catId']);
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
     * Function to delete a subcategory
     *
     * @param object $subcatobj subcategory  object
     *
     * @return boolean
     */
    public function deleteCategory($subcatobj)
    {
        $this->_em->remove($subcatobj);
        $this->_em->flush();
        
        return true;
    }

    /**
     * Function to get category id from sub category id
     *
     * @param int $subcatId Subcategory  id
     *
     * @return int
     */
    public function getCategoryIdfromSubcatid($subcatId)
    {
        $category = $this->createQueryBuilder('sc')
                ->select("c.id as catId")
                ->leftJoin('CommonUtilityBundle:FgDmDocumentCategory', 'c', 'WITH', 'c.id = sc.category')
                ->where('sc.id=:subcat')
                ->setParameters(array('subcat' => $subcatId));
        $dataResult = $category->getQuery()->getArrayResult();
        
        return $dataResult[0]['catId'];
    }

    /**
     * Function to get maximum sort order of document sub-categories.
     *
     * @param int $categoryId Category Id
     *
     * @return int $maxSortOrder Maximum Sort Order
     */
    public function getMaxSortOrderofSubcategories($categoryId)
    {
        $maxSortData = $this->createQueryBuilder('sc')
                ->select('MAX(sc.sortOrder) AS sortOrder')
                ->where('sc.category = :categoryId')
                ->setParameter('categoryId', $categoryId)
                ->getQuery()
                ->getArrayResult();

        $maxSortOrder = $maxSortData[0]['sortOrder'];
        if ($maxSortOrder == '') {
            $maxSortOrder = '0';
        }

        return $maxSortOrder;
    }

    /**
     * Function to update main table entry with clubdefault language entry
     *
     * @param string $clubDefaultLang Club default language
     * @param int    $clubId          Document sclub id 
     * 
     * @return boolean
     */
    private function updateMainTable($clubDefaultLang, $clubId)
    {
        $mainFileds = array('title');
        $i18Fields = array('title_lang');
        $fieldsList = array('mainTable' => 'fg_dm_document_subcategory',
            'i18nTable' => 'fg_dm_document_subcategory_i18n',
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
}

?>
