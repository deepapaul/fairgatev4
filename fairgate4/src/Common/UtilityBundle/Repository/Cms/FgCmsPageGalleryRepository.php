<?php
/**
 * FgCmsPageGalleryRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsPageGalleryRepository
 *
 * This class is used for handling CMS page details insert, update, delete functionalities.
 */
class FgCmsPageGalleryRepository extends EntityRepository
{

    /**
     * This unction is used to save cms gallery special page data
     * @param type $pageId
     * @param type $data
     * @return boolean
     */
    public function saveData($pageId, $data)
    {
        $this->_em->getRepository('CommonUtilityBundle:FgCmsPageGallery')->deleteExistingRoles($pageId);
        $cmsPageObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPage', $pageId);
        if ($data['isAllGalleries'] !== 1) {
            foreach ($data['galleryRoleArray'] as $roleId) {
                $pageGalleryObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageGallery')->findOneBy(array('page' => $pageId, 'galleryRole' => $roleId));
                if (empty($pageGalleryObj)) {
                    $pageGalleryObj = new \Common\UtilityBundle\Entity\FgCmsPageGallery();
                }
                if ($roleId == 'CG') {
                    $pageGalleryObj->setGalleryType('CLUB');
                } else {
                    $roleObj = $this->_em->getReference('CommonUtilityBundle:FgRmRole', $roleId);
                    $pageGalleryObj->setGalleryRole($roleObj);
                    $pageGalleryObj->setGalleryType('ROLE');
                }
                $pageGalleryObj->setPage($cmsPageObj);
                $this->_em->persist($pageGalleryObj);
            }
        }
        $this->_em->flush();

        return true;
    }

    /**
     * This unction is used to get gallery roles
     * @param type $pageId
     * @return array $result
     */
    public function getGalleryRoles($pageId)
    {
        $pageObj = $this->createQueryBuilder('g')
            ->select("CASE WHEN IDENTITY(g.galleryRole) IS NOT NULL THEN IDENTITY(g.galleryRole) ELSE 'CG' END AS role")
            ->leftJoin('CommonUtilityBundle:FgRmRole', 'r', 'WITH', 'r.id = g.galleryRole')
            ->where('g.page=:pageId')
            ->setParameters(array('pageId' => $pageId));
        $roles = $pageObj->getQuery()->getArrayResult();
        $result = array();
        foreach ($roles as $val) {
            $result[] = $val['role'];
        }

        return $result;
    }

    /**
     * This unction is used to delete existing gallery roles
     * @param type $pageId
     * @return boolean
     */
    public function deleteExistingRoles($pageId)
    {
        $galRoleObjs = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageGallery')->findBy(array('page' => $pageId));
        foreach ($galRoleObjs as $galRoleObj) {
            $this->_em->remove($galRoleObj);
        }
        $this->_em->flush();

        return;
    }

    /**
     * This unction is used to get gallery roles
     * @param type $pageId
     * @return array $result
     */
    public function getRolesForPreview($pageId)
    {

        $footerDet = $this->createQueryBuilder('g')
            ->select("CASE WHEN IDENTITY(g.galleryRole)IS NOT NULL THEN IDENTITY(g.galleryRole)  ELSE g.galleryType END ")
            ->where('g.page=:pageId')
            ->setParameters(array('pageId' => $pageId));

        return $footerDet->getQuery()->getArrayResult();
    }
}
