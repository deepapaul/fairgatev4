<?php
/**
 * FgCmsPageContentElementFormFieldOptionsRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Common\UtilityBundle\Entity\FgCmsPageContentElementFormFieldOptions;
use Doctrine\ORM\EntityRepository;

/**
 * FgCmsPageContentElementFormFieldOptionsRepository
 *
 * This class is used for handling CMS form fields option.
 */
class FgCmsPageContentElementFormFieldOptionsRepository extends EntityRepository
{
    /*
     * Function to save the form elements to the database
     * @return int   $formFieldId     The id of the form fields for which the optins is been added
     * @param array  $optionsArray    The array of options data
     * @param string $clubDefaultLang The club default language
     * @param array $clubLanguages    The array of club languages
     */

    public function saveFieldOptions($formFieldId, $optionsArray, $clubDefaultLang, $clubLanguages)
    {
        $formFieldObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFields')->find($formFieldId);
        if ($formFieldObj != '') {
            foreach ($optionsArray as $optionId => $option) {
                $optionObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptions')->find($optionId);

                if ($optionObj == '') {
                    // New option create
                    $optionObj = new FgCmsPageContentElementFormFieldOptions();
                    $optionObj->setField($formFieldObj);
                }
                $optionObj->setIsActive($option['isActive']);
                $optionObj->setSelectionValueName($option['value'][$clubDefaultLang]);
                $optionObj->setSortOrder($option['sortOrder']);
                $optionObj->setIsDeleted($option['isDeleted']);

                $this->_em->persist($optionObj);
                $this->_em->flush();

                $optionId = $optionObj->getId();

                //insert into optionsi18n
                $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptionsI18n')
                    ->saveOptionI18n($optionId, $option['value'], $clubLanguages);
            }
        }

        return;
    }
}
