<?php
/**
 * FgCmsPageAreasRepository
 *
 * This repository is used for handling CMS article and calendar special pages areas
 *
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author      pitsolutions.ch
 * @version     Fairgate V4
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsPageAreasRepository
 *
 * This class is used for handling  article and calendar special pages areas
 */
class FgCmsPageAreasRepository extends EntityRepository
{

    /**
     * Function to save calendar and article special page areas
     *
     * @param int   $pageId  page id
     * @param array $areas   areas array
     *
     * @return
     */
    public function savePageAreas($pageId, $areas)
    {

        $pageObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPage', $pageId);
        foreach ($areas as $roleId) {
            $areaObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageAreas')->findOneBy(array('page' => $pageId, 'role' => $roleId));
            if (empty($areaObj)) {
                $areaObj = new \Common\UtilityBundle\Entity\FgCmsPageAreas();
            }
            $roleObj = $this->_em->getReference('CommonUtilityBundle:FgRmRole', $roleId);
            $areaObj->setPage($pageObj);
            $areaObj->setRole($roleObj);
            $this->_em->persist($areaObj);
        }
        $this->_em->flush();

        return;
    }

    /**
     * Function to delete existing special page areas
     *
     * @param int $pageId  page id
     *
     * @return
     */
    public function deleteExistingSpecialPageArea($pageId)
    {
        $areaObjs = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageAreas')->findBy(array('page' => $pageId));
        if (!empty($areaObjs)) {
            foreach ($areaObjs as $areaObj) {
                $this->_em->remove($areaObj);
            }
            $this->_em->flush();
        }

        return;
    }
}
