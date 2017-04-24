<?php
/**
 * FgTmThemeHeadersRepository
 */
namespace Common\UtilityBundle\Repository\Cms;

use Common\UtilityBundle\Entity\FgTmThemeHeaders;
use Doctrine\ORM\EntityRepository;

/**
 * FgTmThemeHeadersRepository - Repository class for theme headers
 *
 * FgTmThemeHeadersRepository - Repository class for fairgate theme headers configuration functionalities
 *
 * @package         CommonUtility
 * @subpackage      Repository
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgTmThemeHeadersRepository extends EntityRepository
{

    /** Function to save  create & edit headers for a specific theme configuration
     * @param integer $configId - theme configuration id
     * @param type $data -  data needs to be saved
     * @param type $edit - flag for edit
     * 
     * @return boolean
     */
    public function saveHeaderConfigurations($configId, $data, $edit = 0)
    {
        $fgTmThemeConfigurationObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        if (count($data) > 0) {

            foreach ($data as $datas) {

                if ($datas['id'] != '') {

                    $fgTmThemeHeaderObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeHeaders')->find($datas['id']);
                } else {

                    $fgTmThemeHeaderObj = new FgTmThemeHeaders();
                }
                $fgTmThemeHeaderObj
                    ->setHeaderLabel($datas['label'])
                    ->setFileName($datas['fileName'])
                    ->setConfiguration($fgTmThemeConfigurationObj);
                $this->_em->persist($fgTmThemeHeaderObj);
            }
            // echo"<br>";
            $this->_em->flush();
        }
        return true;
    }

    /**
     * Function to delete headers
     * @param array $deletedFiles deletedFiles id
     */
    public function deleteHeaders($deletedFiles)
    {
        if (count($deletedFiles) > 0) {
            foreach ($deletedFiles as $headers) {
                $qb = $this->getEntityManager()->createQueryBuilder();
                $qb->delete('CommonUtilityBundle:FgTmThemeHeaders', 'C');
                $qb->where('C.id = :headerId');
                $qb->setParameter('headerId', $headers['id']);
                $query = $qb->getQuery();
                $query->execute();
            }
        }
    }

    /**
     * Function to get header details
     * @param type $configId
     * @return type
     */
    public function getHeaderDetails($configId)
    {
        $headerObj = $this->createQueryBuilder('h')
            ->select("h.id AS id, h.fileName AS fileName")
            ->where('h.configuration = :configId')
            ->setParameter('configId', $configId);

        return $headerObj->getQuery()->getArrayResult();
    }
}
