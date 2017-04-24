<?php
/**
 * FgCmsPageArticleCategoriesRepository
 *
 * This repository is used for handling CMS article special page categories
 *
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author      pitsolutions.ch
 * @version     Fairgate V4
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsPageArticleCategoriesRepository
 *
 * This class is used for handling  article special page categories
 */
class FgCmsPageArticleCategoriesRepository extends EntityRepository
{

    /**
     * Function to save  article special page categories
     *
     * @param int   $pageId       page id
     * @param array $categories   categories array
     *
     * @return
     */
    public function savePageCategories($pageId, $categories)
    {
        $pageObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPage', $pageId);
        foreach ($categories as $catId) {
            $catObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageArticleCategories')->findOneBy(array('page' => $pageId, 'category' => $catId));
            if (empty($catObj)) {
                $catObj = new \Common\UtilityBundle\Entity\FgCmsPageArticleCategories();
            }
            $catIdObj = $this->_em->getReference('CommonUtilityBundle:FgCmsArticleCategory', $catId);
            $catObj->setPage($pageObj);
            $catObj->setCategory($catIdObj);
            $this->_em->persist($catObj);
        }
        $this->_em->flush();

        return;
    }

    /**
     * Function to delete existing categories for special page
     *
     * @param int $pageId  page id
     */
    public function deleteExistingSpecialPageCategory($pageId)
    {
        $catObjs = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageArticleCategories')->findBy(array('page' => $pageId));
        foreach ($catObjs as $catObj) {
            $this->_em->remove($catObj);
        }
        $this->_em->flush();
    }
}
