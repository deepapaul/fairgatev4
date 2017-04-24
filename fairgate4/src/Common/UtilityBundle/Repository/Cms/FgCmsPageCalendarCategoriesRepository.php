<?php
/**
 * FgCmsPageCalendarCategoriesRepository
 *
 * This repository is used for handling CMS calendar special page categories
 *
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author      pitsolutions.ch
 * @version     Fairgate V4
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsPageCalendarCategoriesRepository
 *
 * This class is used for handling  calendar special page categories
 */
class FgCmsPageCalendarCategoriesRepository extends EntityRepository
{

    /**
     * Function to save calendar special page categories
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
            $catObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageCalendarCategories')->findOneBy(array('page' => $pageId, 'category' => $catId));
            if (empty($catObj)) {
                $catObj = new \Common\UtilityBundle\Entity\FgCmsPageCalendarCategories();
            }
            $catIdObj = $this->_em->getReference('CommonUtilityBundle:FgEmCalendarCategory', $catId);
            $catObj->setPage($pageObj);
            $catObj->setCategory($catIdObj);
            $this->_em->persist($catObj);
        }
        $this->_em->flush();

        return true;
    }

    /**
     * Function to delete existing categories for special page
     *
     * @param int $pageId  page id
     */
    public function deleteExistingSpecialPageCategory($pageId)
    {
        $catObjs = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageCalendarCategories')->findBy(array('page' => $pageId));
        foreach ($catObjs as $catObj) {
            $this->_em->remove($catObj);
        }
        $this->_em->flush();
    }
}
