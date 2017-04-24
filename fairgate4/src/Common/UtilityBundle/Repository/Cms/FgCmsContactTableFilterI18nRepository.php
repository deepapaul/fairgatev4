<?php
/**
 * FgCmsContactTableFilterI18nRepository.
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
 * FgCmsContactTableFilterI18nRepository
 *
 * This class is used for handling CMS contact table filter columns.
 */
class FgCmsContactTableFilterI18nRepository extends EntityRepository
{

    /**
     * Function to save the form field I18n to the database
     * 
     * @param Int   $filterColumnId      Form Field Id
     * @param Array $filterDataArray     Stage3 options dataarray
     * 
     * @return void
     */
    public function saveContactTableFiltersI18n($filterColumnId, $filterDataTitleArray)
    {
        if (count($filterDataTitleArray) > 0) {
            $conn = $this->getEntityManager()->getConnection();
            $query = "INSERT INTO fg_cms_contact_table_filter_i18n (id,lang,title_lang) VALUES ";
            foreach ($filterDataTitleArray as $language => $title) {
                $labelValue = ($title != '') ? trim(FgUtility::getSecuredDataString($title, $conn)) : '';
                $query .= " ($filterColumnId,'$language','$labelValue'),";
            }
            $query = rtrim($query, ',');
            $query .=" ON DUPLICATE KEY UPDATE title_lang = VALUES(title_lang)";
            $conn->executeQuery($query);
        }
        return;
    }
}
