<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;

/**
 * FgSmServicesI18nRepository
 *
 * This class is used for handling translation entries of sponsor services in Sponsors Administration.
 *
 * @author pitsolutions.ch
 */
class FgSmServicesI18nRepository extends EntityRepository
{

    /**
     * Function to add translation data of a service.
     *
     * @param array   $translationArray Translation data array
     * @param object  $serviceObj       Service Object
     * @param boolean $doSave           Whether to save data or return data object
     *
     * @return \Common\UtilityBundle\Entity\FgSmServicesI18n Returns object if '$doSave' is false.
     */
    public function addTranslation($translationArray, $serviceObj = false, $doSave = true)
    {
        if (!$serviceObj) {
            $serviceObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServices')->find($translationArray['id']);
        }
        $title = isset($translationArray['title']) ? $translationArray['title'] : '';
        $description = isset($translationArray['description']) ? $translationArray['description'] : '';

        $transObj = new \Common\UtilityBundle\Entity\FgSmServicesI18n();
        $transObj->setId($serviceObj)
                ->setLang($translationArray['lang'])
                ->setTitleLang(stripslashes($title))
                ->setDescriptionLang($description)
                ->setIsActive('1');

        if ($doSave) {
            $this->_em->persist($transObj);
            $this->_em->flush();
        } else {
            return $transObj;
        }
    }

    /**
     * Function to insert translation data of a service.
     *
     * @param int    $serviceId       Service id
     * @param array  $translationData Translation data array
     * @param object $serviceObj      Service Object
     * @param string $clubDefaultLang Club Default Language
     * @param object $container       Container Object
     */
    public function insertTranslationData($serviceId, $translationData, $serviceObj = false, $clubDefaultLang = 'de', $container = null)
    {
        $pdoClass = new SponsorPdo($container);
        $pdoClass->insertServiceTranslation($serviceId, $translationData, $clubDefaultLang);
    }

    /**
     * Function to update translation data of a service.
     *
     * @param array   $translationArray Translation data array
     * @param object  $serviceObj       Service Object
     * @param boolean $doSave           Whether to save data or return data object
     * @param object  $container        Container Object
     *
     * @return \Common\UtilityBundle\Entity\FgSmServicesI18n Returns object if '$doSave' is false.
     */
    public function updateTranslation($translationArray, $serviceObj = false, $doSave = true, $container = null)
    {
        $serviceId = $translationArray['id'];
        $lang = $translationArray['lang'];
        if (!$serviceObj) {
            $serviceObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServices')->find($serviceId);
        }
        $transObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServicesI18n')->findOneBy(array('id' => $serviceId, 'lang' => $lang));
        if ($transObj) {
            $translationArray = $this->setDefaultTitleAndDescription($translationArray, $serviceObj, $transObj);
            $pdoClass = new SponsorPdo($container);
            $pdoClass->updateServiceTranslation($translationArray);
        } else {
            $transObj = $this->addTranslation($translationArray, $serviceObj, false);
        }
        if ($doSave) {
            $this->_em->persist($transObj);
            $this->_em->flush();
        } else {
            return $transObj;
        }
    }

    /**
     * Function to set default title and description for saving translation.
     *
     * @param array  $translationArray Translation data array
     * @param object $serviceObj       Service object
     * @param object $transObj         Translation object
     *
     * @return array Translation data array
     */
    private function setDefaultTitleAndDescription($translationArray, $serviceObj, $transObj)
    {
        if (!isset($translationArray['title'])) {
            $srTitle= stripslashes($serviceObj->getTitle());
            $trTitle= stripslashes($transObj->getTitleLang());
            $translationArray['title'] = $transObj->getTitleLang() ? str_replace('"', '', $trTitle): str_replace('"', '', $srTitle);
        }
        if (!isset($translationArray['description'])) {
            $translationArray['description'] = $transObj->getDescriptionLang() ? $transObj->getDescriptionLang() : $serviceObj->getDescription();
        }

        return $translationArray;
    }

}

