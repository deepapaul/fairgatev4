<?php
/**
 * FgCmsPageCategoriesRepository - short description 
 *
 * @package         package
 * @subpackage      subpackahe
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

class FgCmsPageCategoriesRepository extends EntityRepository
{

    /**
     * Function save Page Categories
     *
     * @param int $pageId  page id
     * @param int $categories  $category ids
     * 
     * @return boolean
     */
    public function savePageCategories($pageId, $categories)
    {
        $pageObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPage', $pageId);
        foreach ($categories as $catId) {
            $catObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageCategories')->findOneBy(array('page' => $pageId, 'categoryId' => $catId));
            if (empty($catObj)) {
                $catObj = new \Common\UtilityBundle\Entity\FgCmsPageCategories();
            }

            $catObj->setPage($pageObj);
            $catObj->setCategoryId($catId);
            $this->_em->persist($catObj);
        }
        $this->_em->flush();

        return true;
    }

    /**
     * Function to delete existing categories for special page
     *
     * @param int   $pageId  page id
     */
    public function deleteExistingSpecialPageCategory($pageId)
    {
        $catObjs = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageCategories')->findBy(array('page' => $pageId));
        if (!empty($catObjs)) {
            foreach ($catObjs as $catObj) {
                $this->_em->remove($catObj);
            }
            $this->_em->flush();
        }
    }
}
