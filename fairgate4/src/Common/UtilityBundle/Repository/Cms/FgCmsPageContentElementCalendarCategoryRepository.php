<?php 
/**
 * FgCmsPageContentElementCalendarCategoryRepository 
 *
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author      pitsolutions.ch
 * @version     Fairgate V4
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsPageContentElementCalendarCategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgCmsPageContentElementCalendarCategoryRepository extends EntityRepository
{

    /**
     * Function to save article element categories
     *
     * @param int   $elementId  element id
     * @param array $categories categories to be saved
     *
     * @return
     */
    public function saveCalendarElementCategory($elementId, $categories)
    {
        $elementObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPageContentElement', $elementId);
        foreach ($categories as $categoryId) {
            $calendarCatObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementCalendarCategory')->findOneBy(array('element' => $elementId, 'category' => $categoryId));
            if (empty($calendarCatObj)) {
                $calendarCatObj = new \Common\UtilityBundle\Entity\FgCmsPageContentElementCalendarCategory();
            }
            $catObj = $this->_em->getReference('CommonUtilityBundle:FgEmCalendarCategory', $categoryId);
            $calendarCatObj->setElement($elementObj);
            $calendarCatObj->setCategory($catObj);
            $this->_em->persist($calendarCatObj);
        }
        $this->_em->flush();

        return true;
    }

    /**
     * Function to delete existing categories for calendar element
     *
     * @param int   $elementId  element id
     */
    public function deleteExistingElementCategory($elementId)
    {
        $calendarCatObjs = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementCalendarCategory')->findBy(array('element' => $elementId));
        foreach ($calendarCatObjs as $calendarCatObj) {
            $this->_em->remove($calendarCatObj);
        }
        $this->_em->flush();
    }
}
