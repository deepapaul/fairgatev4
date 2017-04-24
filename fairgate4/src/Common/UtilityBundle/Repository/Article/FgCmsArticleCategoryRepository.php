<?php

/**
 * FgCmsArticleCategoryRepository to handle functionalities related to fg_cms_article and other article tables.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsArticleCategoryRepository to handle fg_cms_article and related tables.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleCategoryRepository extends EntityRepository
{

    /**
     * Function to get article categories.
     *
     * @param int   $clubId        current club id
     * @param array $editableArray editable articles array
     *
     * @return array category list array
     */
    public function getCategoryList($clubId, $editableArray)
    {
        if (count($editableArray) == 0) {
            $editableArray = array(0);
        }

        $editableIds = implode(',', $editableArray);

        $category = $this->createQueryBuilder('c')
            ->select('c.id,c.title,c.sortOrder,ci18.titleLang, ci18.lang')
            ->addSelect("(SELECT COUNT(distinct ca.id) FROM CommonUtilityBundle:FgCmsArticle ca LEFT JOIN CommonUtilityBundle:FgCmsArticleSelectedcategories sc WITH (sc.article = ca.id) WHERE sc.category = c.id AND sc.article IN ($editableIds) ) articleCount")
            ->leftJoin('CommonUtilityBundle:FgCmsArticleCategoryI18n', 'ci18', 'WITH', 'ci18.id = c.id')
            ->where('c.club=:clubId')
            ->setParameters(array('clubId' => $clubId));
        $dataResult = $category->getQuery()->getArrayResult();

        $result = array();
        $id = '';
        foreach ($dataResult as $arr) {
            if (count($arr) > 0) {
                if ($arr['id'] == $id) {
                    $result[$id]['titleLang'][$arr['lang']] = $arr['titleLang'];
                } else {
                    $id = $arr['id'];
                    $result[$id] = array('id' => $arr['id'], 'title' => $arr['title'], 'sortOrder' => $arr['sortOrder'], 'articleCount' => $arr['articleCount']);

                    $result[$id]['titleLang'][$arr['lang']] = $arr['titleLang'];
                }
            }
        }

        return $result;
    }

    /**
     * Function to get article category count.
     *
     * @param int $clubId current club id
     *
     * @return int category count
     */
    public function getCategoryCount($clubId)
    {
        $category = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.club=:clubId')
            ->setParameters(array('clubId' => $clubId));
        $dataResult = $category->getQuery()->getArrayResult();

        return $dataResult[0][1];
    }

    /**
     * Function to save article categories.
     *
     * @param int    $clubId        current club id
     * @param string $defaultLang   club default language
     * @param array  $catArr        category array
     * @param array  $clubLanguages club languages
     *
     * @return array category title and id list data
     */
    public function saveCategory($clubId, $defaultLang, $catArr, $clubLanguages)
    {
        $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        foreach ($catArr as $id => $data) {
            $categoryObj = $this->find($id);
            if ($data['is_deleted'] == 1) {
                $this->deleteCategory($categoryObj);
                continue;
            }
            if (empty($categoryObj)) {
                $categoryObj = new \Common\UtilityBundle\Entity\FgCmsArticleCategory();
            }
            if (isset($data['title'][$defaultLang])) {
                $categoryObj->setTitle($data['title'][$defaultLang]);
            }
            if (isset($data['sort_order'])) {
                $categoryObj->setSortOrder($data['sort_order']);
            }
            $categoryObj->setClub($clubObj);
            $this->_em->persist($categoryObj);
            $this->_em->flush();

            $catId = $categoryObj->getId();
            $catTitle = $categoryObj->getTitle();
            $catIdObj = $this->find($catId);
            foreach ($clubLanguages as $lang) {
                $langDetails = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleCategoryI18n')->getUpdateDetails($lang, $catId);
                if (!empty($langDetails)) {
                    if (array_key_exists($lang, ($data['title']))) {
                        $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleCategoryI18n')->updateSingleLang($catId, $lang, $data['title'][$lang]);
                    }
                } else {
                    $langObj = new \Common\UtilityBundle\Entity\FgCmsArticleCategoryI18n();
                    $langObj->setId($catIdObj);
                    $langObj->setLang($lang);
                    $title = $data['title'][$lang];
                    $langObj->setTitleLang($title);
                    $this->_em->persist($langObj);
                    $this->_em->flush();
                }
            }
        }
        $this->updateCategoryTitle($clubId, $defaultLang);
        $this->reorderAllCategories($clubId);

        return array('catId' => $catId, 'catTitle' => $catTitle);
    }

    /**
     * Function to delete a category.
     *
     * @param object $categoryObj category object
     *
     * @return void
     */
    public function deleteCategory($categoryObj)
    {
        $this->_em->remove($categoryObj);
        $this->_em->flush();

        return;
    }

    /**
     * Function to get all article Categories of a club.
     *
     * @param int    $clubId  Club id
     * @param string $defLang Default Language
     *
     * @return array article category list
     */
    public function getArticleCategories($clubId, $defLang)
    {
        return $this->createQueryBuilder('C')
                ->select("C.id as id, (CASE WHEN (CL.titleLang IS NULL OR CL.titleLang = '') THEN C.title ELSE CL.titleLang END) as title")
                ->leftJoin('CommonUtilityBundle:FgCmsArticleCategoryI18n', 'CL', 'WITH', '(C.id = CL.id AND CL.lang = :defLang)')
                ->where('C.club=:clubId')
                ->orderBy('C.sortOrder')
                ->setParameter('clubId', $clubId)
                ->setParameter('defLang', $defLang)
                ->getQuery()
                ->getResult();
    }

    /**
     * Method to update category title with default language.
     *
     * @param Int    $clubId      club id
     * @param string $defaultLang club default language
     *
     * @return void
     */
    private function updateCategoryTitle($clubId, $defaultLang)
    {
        $query = 'UPDATE fg_cms_article_category C '
            . 'INNER JOIN fg_cms_article_category_i18n C18'
            . ' ON C.id = C18.id'
            . ' SET C.title = C18.title_lang '
            . " WHERE C.club_id = $clubId AND C18.lang = '$defaultLang' AND C18.title_lang > ''  AND C18.lang != ''";
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeQuery($query);

        return;
    }

    /**
     * Method reorder all categories.
     *
     * @param Int $clubId club id
     *
     * @return void
     */
    private function reorderAllCategories($clubId)
    {
        $query = 'UPDATE fg_cms_article_category CI '
            . ' INNER JOIN (SELECT id, @row := @row + 1 as row '
            . ' FROM fg_cms_article_category,(SELECT @row := 0) r '
            . " WHERE club_id = $clubId "
            . ' ORDER BY sort_order ASC '
            . ') as CI2 on CI.id = CI2.id '
            . ' SET CI.sort_order = CI2.row ';
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeQuery($query);

        return;
    }
}
