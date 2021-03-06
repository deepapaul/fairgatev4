<?php 
/**
 * FgCmsPageContentTextElementHistoryRepository.
 *
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgCmsPageContentTextElementHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgCmsPageContentTextElementHistoryRepository extends EntityRepository
{

    /**
     * Function to create the text entry and text18n for article
     * This function will also update the text-version of the article.
     *
     * @param array  $textArray               The array with the details to be saved
     * @param int    $contactId               Current user id
     * @param int    $textElementId           The id of the textelement
     * @param string $clubDefaultLanguage     the default language of the club
     *
     * @return int Id of the article version
     */
    public function insertTextElementHistory($textArray, $contactId, $textElementId, $clubDefaultLanguage)
    {
        if (isset($textArray[$clubDefaultLanguage]['text'])) {
            $textHObj = new \Common\UtilityBundle\Entity\FgCmsPageContentTextElementHistory();
            $textHObj->setTextElement($this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->find($textElementId));
            $textHObj->setLastEditedBy($this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId));
            $textHObj->setLastEditedDate(new \DateTime('now'));

            (isset($textArray[$clubDefaultLanguage]['text'])) ? $textHObj->setText(str_replace('<script', '<scri&nbsp;pt', $textArray[$clubDefaultLanguage]['text'])) : '';

            $this->_em->persist($textHObj);
            $this->_em->flush();

            return $textHObj->getId();
        } else {
            $textObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentTextElement')->find($textElementId);
            return $textObj->getVersion()->getId();
        }
    }
}
