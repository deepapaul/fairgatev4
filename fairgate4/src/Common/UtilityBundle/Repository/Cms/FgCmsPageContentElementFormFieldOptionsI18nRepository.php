<?php
/**
 * FgCmsPageContentElementFormFieldOptionsI18nRepository.
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
 * FgCmsPageContentElementFormFieldOptionsI18nRepository
 *
 * This class is used for handling CMS form fields options.
 */
class FgCmsPageContentElementFormFieldOptionsI18nRepository extends EntityRepository
{

    /**
     * Function to save the form field options I18n to the database
     * 
     * @param Int   $optionId      Form Option Id
     * @param Array $dataArray     Feild options dataarray
     * @param Array $clubLanguages Club Language array
     * 
     * @return void
     */
    public function saveOptionI18n($optionId, $dataArray, $clubLanguages)
    {
        foreach ($clubLanguages as $language) {

            $optionValue = ($dataArray[$language] != '') ? $dataArray[$language] : '';

            $query = "INSERT INTO fg_cms_page_content_element_form_field_options_i18n (id,lang,selection_value_name_lang) "
                . " VALUES ($optionId,'$language','$optionValue') ON DUPLICATE KEY UPDATE selection_value_name_lang = VALUES(selection_value_name_lang)";
            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery($query);
        }

        return;
    }
}
