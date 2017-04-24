<?php

/**
 * FgWebSettingsI18nRepository
 * 
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
namespace Common\UtilityBundle\Repository\Cms;

use Common\UtilityBundle\Entity\FgWebSettingsI18n;
use Doctrine\ORM\EntityRepository;

/**
 * FgWebSettingsI18nRepository to manage fg_web_settings description fields
 */
class FgWebSettingsI18nRepository extends EntityRepository
{

    /**
     * Method to insert/update description of  websettings
     * 
     * @param int    $webSettingsId       webSettings Id 
     * @param array  $dataArray           data to insert
     * 
     */
    public function updateDescription($webSettingsId, $dataArray)
    {
        foreach ($dataArray as $lang => $desc) {
            $webSettingsLangObj = $this->findOneBy(array('lang' => $lang, 'settings' => $webSettingsId));
            if (!empty($webSettingsLangObj)) {
                //if null description, remove it else update only description
                ($desc) ? $webSettingsLangObj->setDescriptionLang($desc) : $this->_em->remove($webSettingsLangObj);
                $this->_em->flush();
            } else {
                //insert data
                $this->insertSettingsDesc($webSettingsId, $lang, $desc);
            }
        }
    }

    /**
     * Method to insert description to table
     * 
     * @param int    $webSettingsId webSettings Id
     * @param string $lang          language (short)
     * @param string $desc          description to insert
     */
    private function insertSettingsDesc($webSettingsId, $lang, $desc)
    {
        $webSettingsObj = $this->_em->getRepository('CommonUtilityBundle:FgWebSettings')->find($webSettingsId);
        $webSettingsLangObj = new FgWebSettingsI18n();
        $webSettingsLangObj->setDescriptionLang($desc);
        $webSettingsLangObj->setLang($lang);
        $webSettingsLangObj->setSettings($webSettingsObj);
        $this->_em->persist($webSettingsLangObj);
        $this->_em->flush();
    }
}
