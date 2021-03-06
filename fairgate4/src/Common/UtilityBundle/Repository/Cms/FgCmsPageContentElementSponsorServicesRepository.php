<?php 
/**
 * FgCmsPageContentElementSponsorServicesRepository.
 *
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsPageContentElementSponsorServicesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgCmsPageContentElementSponsorServicesRepository extends EntityRepository
{

    /**
     * Function to save sponsor ad element services
     *
     * @param array   $elementId  element id
     * @param array $sponsorServices services to be saved
     *
     * @return
     */
    public function saveSponsorServices($elementId, $sponsorServices)
    {
        $elementObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPageContentElement', $elementId);
        $qb = $this->createQueryBuilder();
        $qb->delete('CommonUtilityBundle:FgCmsPageContentElementSponsorServices', 's');
        $qb->where('s.element =:elementId');
        $qb->setParameters(array('elementId' => $elementId));
        $qb->getQuery()->execute();

        foreach ($sponsorServices as $serviceId) {
            $serviceObj = new \Common\UtilityBundle\Entity\FgCmsPageContentElementSponsorServices();
            $srvObj = $this->_em->getReference('CommonUtilityBundle:FgSmServices', $serviceId);
            $serviceObj->setElement($elementObj);
            $serviceObj->setService($srvObj);
            $this->_em->persist($serviceObj);
        }
        $this->_em->flush();

        return true;
    }
}
