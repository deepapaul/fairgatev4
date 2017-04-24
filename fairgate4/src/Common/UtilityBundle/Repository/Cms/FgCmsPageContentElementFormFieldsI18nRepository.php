<?php
/**
 * FgCmsPageContentElementFormFieldsI18nRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgCmsPageContentElementFormFieldsI18nRepository
 *
 * This class is used for handling CMS form fields.
 */
class FgCmsPageContentElementFormFieldsI18nRepository extends EntityRepository
{

    /**
     * Function to save the form field I18n to the database
     * 
     * @param Int   $elementId      Form Field Id
     * @param Array $dataArray      Stage3 options dataarray
     * @param Array $clubLanguages  Club Language array
     * 
     * @return void
     */
    public function saveElementI18n($elementId, $dataArray, $clubLanguages, $conn)
    {
        $query = "INSERT INTO fg_cms_page_content_element_form_fields_i18n (id,lang,fieldname_lang,predefined_value_lang,placeholder_value_lang,tooltip_value_lang) VALUES ";
        foreach ($clubLanguages as $language) {
            $labelValue = ($dataArray['label'][$language] != '') ? trim(FgUtility::getSecuredDataString($dataArray['label'][$language], $conn)) : '';
            $predefinedValue = ($dataArray['predefined'][$language] != '') ? trim(FgUtility::getSecuredDataString($dataArray['predefined'][$language], $conn)) : '';
            $placeholderValue = ($dataArray['placeholder'][$language] != '') ? trim(FgUtility::getSecuredDataString($dataArray['placeholder'][$language], $conn)) : '';
            $tooltipValue = ($dataArray['tooltip'][$language] != '') ? trim(FgUtility::getSecuredDataString($dataArray['tooltip'][$language], $conn)) : '';

            $query .= " ($elementId,'$language','$labelValue','$predefinedValue','$placeholderValue','$tooltipValue'),";
        }
        $query = rtrim($query, ',');
        $query .=" ON DUPLICATE KEY UPDATE fieldname_lang = VALUES(fieldname_lang), predefined_value_lang = VALUES(predefined_value_lang), placeholder_value_lang = VALUES(placeholder_value_lang), tooltip_value_lang = VALUES(tooltip_value_lang)";
        $conn = $this->getEntityManager()->getConnection();
        $conn->executeQuery($query);

        return;
    }
}
