<?php

/**
 * FgCmsContactTableColumnsRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmsContactTableColumns;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgAvatar;

/**
 * FgCmsContactTableColumnsRepository
 *
 */
class FgCmsContactTableColumnsRepository extends EntityRepository
{

    /**
     * save/update contact table element columns
     *
     * @param array     $formatArray    formatArray
     * @param int       $table          table id
     * @param object    $container      container
     *
     * @return int
     */
    public function saveContactTableColumns($formatArray, $table, $container)
    {
        $defaultLang = $container->get('club')->get('club_default_lang');
        $tableObj = $this->_em->getReference('CommonUtilityBundle:FgCmsContactTable', $table);
        foreach ($formatArray as $type => $rand) {
            foreach ($rand as $id => $properties) {
                $mode = "old";
                $contactTbColObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsContactTableColumns')->find($id);
                if (empty($contactTbColObj)) {
                    $contactTbColObj = new FgCmsContactTableColumns();
                    $mode = 'new';
                }

                $contactTbColObj = $this->mandatoryFields($contactTbColObj, $tableObj, $properties, $defaultLang, $type);
                $contactTbColObj = $this->typeManipulation($type, $contactTbColObj, $properties);
                $this->_em->persist($contactTbColObj);
                //need to flush inside loop as column table id is required for i18table insertion/updateion
                $this->_em->flush();
                $conn = $this->getEntityManager()->getConnection();
                if ($mode == "new") {
                    $lastInserted = $conn->executeQuery("SELECT LAST_INSERT_ID() AS newId")->fetch();
                    $columnId = $lastInserted['newId'];
                } else {
                    $columnId = $id;
                }
                $this->i18InsertionAndUpdation($conn, $properties, $columnId);
            }
        }
        $list = $this->getColumnData($table);
        $this->_em->getRepository('CommonUtilityBundle:FgCmsContactTable')->setContactTableElementStage($tableObj, 2);
        $this->updateTableJson($tableObj, $list, $container);

        return 1;
    }

    /**
     * mandatory fields for table columns
     *
     * @param object $contactTbColObj   contact Table Column Object
     * @param object $tableObj          table Object
     * @param array  $properties        properties
     * @param string $defaultLang       default Language
     * @param string $type              type
     *
     * @return object return contact table column object
     */
    private function mandatoryFields($contactTbColObj, $tableObj, $properties, $defaultLang, $type)
    {
        $contactTbColObj->setTable($tableObj);
        if ($properties['title'][$defaultLang] != '') {
            $contactTbColObj->setTitle($properties['title'][$defaultLang]);
        }
        if ($properties['is_deleted'] != '') {
            $contactTbColObj->setIsDeleted($properties['is_deleted']);
        }
        if ($properties['sortOrder'] != '') {
            $contactTbColObj->setSortOrder($properties['sortOrder']);
        }
        $contactTbColObj->setColumnType(strtolower($type));

        return $contactTbColObj;
    }

    /**
     * i18 Insertion And Updation
     *
     * @param object $conn          conn
     * @param array  $properties    properties
     * @param int    $columnId      last Inserted column id/column id in case of updation
     */
    private function i18InsertionAndUpdation($conn, $properties, $columnId)
    {
        foreach ($properties['title'] as $lang => $langTitle) {
            $langTit = \Common\UtilityBundle\Util\FgUtility::getSecuredDataString($langTitle, $conn);
            $query = "INSERT INTO fg_cms_contact_table_columns_i18n (id,lang,title_lang) "
                . " VALUES ('$columnId','$lang','$langTit') "
                . "ON DUPLICATE KEY UPDATE title_lang = VALUES(title_lang)";
            $conn->executeQuery($query);
        }
    }

    /**
     * contact table column save manipulate object based on type
     *
     * @param string     $type              type of column
     * @param object     $contactTbColObj   contact Table Column Object
     * @param array      $properties        properties to be updated
     *
     * @return type
     */
    private function typeManipulation($type, $contactTbColObj, $properties)
    {
        switch ($type) {
            case 'CONTACT_NAME':
                if (isset($properties['linkUrl'])) {
                    $attrObj = $this->_em->getRepository('CommonUtilityBundle:FgCmAttribute')->find($properties['linkUrl']);
                    $contactTbColObj->setAttribute($attrObj);
                }
                if (isset($properties['showPictue'])) {
                    $contactTbColObj->setShowProfilePicture($properties['showPictue']);
                }
                break;

            case 'CONTACT_FIELD':
                if (isset($properties['attributeId'])) {
                    $attrObj = $this->_em->getRepository('CommonUtilityBundle:FgCmAttribute')->find($properties['attributeId']);
                    $contactTbColObj->setAttribute($attrObj);
                }
                break;

            case 'ANALYSIS_FIELD':
            case 'MEMBERSHIP_INFO':
            case 'FED_MEMBERSHIP_INFO':
            case 'FEDERATION_INFO':
                if (isset($properties['attributeId'])) {
                    $contactTbColObj->setColumnSubtype($properties['attributeId']);
                }
                if (isset($properties['teamFunction'])) {
                    $contactTbColObj->setFunctionIds(implode(',', $properties['teamFunction']));
                }
                break;

            case 'WORKGROUP_ASSIGNMENTS':
            case 'TEAM_FUNCTIONS':
            case 'FILTER_ROLE_ASSIGNMENTS':
                break;

            case 'TEAM_ASSIGNMENTS':
                if (isset($properties['teamFunction'])) {
                    $contactTbColObj->setFunctionIds(implode(',', $properties['teamFunction']));
                }
                break;

            case 'ROLE_CATEGORY_ASSIGNMENTS':
            case 'COMMON_ROLE_FUNCTIONS':
            case 'COMMON_FED_ROLE_FUNCTIONS':
            case 'COMMON_SUB_FED_ROLE_FUNCTIONS':
            case 'FED_ROLE_CATEGORY_ASSIGNMENTS':
            case 'SUB_FED_ROLE_CATEGORY_ASSIGNMENTS':
                if (isset($properties['attributeId'])) {
                    $roleCatObj = $this->_em->getRepository('CommonUtilityBundle:FgRmCategory')->find($properties['attributeId']);
                    $contactTbColObj->setRoleCategory($roleCatObj);
                }
                break;

            case 'INDIVIDUAL_ROLE_FUNCTIONS':
            case 'INDIVIDUAL_FED_ROLE_FUNCTIONS':
            case 'INDIVIDUAL_SUB_FED_ROLE_FUNCTIONS':
            case 'WORKGROUP_FUNCTIONS':
                if (isset($properties['attributeId'])) {
                    $roleObj = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->find($properties['attributeId']);
                    $contactTbColObj->setRole($roleObj)->setRoleCategory($roleObj->getCategory());
                }
                break;
        }
        return $contactTbColObj;
    }

    /**
     * get table column list
     *
     * @param int $table table id
     *
     * @return array
     */
    public function getTableColumnList($table)
    {
        $qs = $this->createQueryBuilder('tc')
            ->select('tc.columnType as type, '
                . ' CASE WHEN tc.attribute IS NOT NULL THEN IDENTITY(tc.attribute)  '
                . 'WHEN tc.role IS NOT NULL THEN  IDENTITY(tc.role) '
                . 'WHEN tc.roleCategory IS NOT NULL THEN IDENTITY(tc.roleCategory) '
                . ' ELSE tc.columnSubtype '
                . 'END as attr  ')
            ->where('tc.table = :table')
            ->andWhere('tc.isDeleted = 0')
            ->setParameters(array('table' => $table))
            ->getQuery();

        return $qs->getArrayResult();
    }

    /**
     * update the json data for column setting purpose
     *
     * @param object    $tableObj   table Object
     * @param array     $list       list of columns
     * @param object    $container  container
     *
     */
    private function updateTableJson($tableObj, $list, $container)
    {
        $clubId = $container->get('club')->get('id');
        $fedId = $container->get('club')->get('federation_id');
        $arr = array();
        foreach ($list as $key => $value) {
            switch ($value['type']) {
                case 'contact_name':
                    $arr[$key] = array('id' => 'contactname', 'type' => 'contactname', 'linkUrl' => $value['attr'], 'club_id' => $clubId, 'name' => $value['id'], 'showProfilePicture' => $value['showProfilePicture'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'contact_field':
                    $arr[$key] = array('id' => $value['attr'], 'type' => 'CF', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'membership_info':
                    if ($value['col'] == 'member_years') {
                        $value['col'] = 'club_' . $value['col'];
                    }
                    $arr[$key] = array('id' => $value['col'], 'type' => 'CM', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'fed_membership_info':
                    $arr[$key] = array('id' => 'fed_' . $value['col'], 'type' => 'FM', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title'] , );
                    break;
                case 'federation_info':
                    $arr[$key] = array('id' => $value['col'], 'type' => 'FI', 'club_id' => $fedId, 'name' => $value['id'], 'sub_ids' => $value['functionIds'],'title' => $value['titleLang'], 'defaultTitle' => $value['title'] , 'sub_ids' => $value['functionIds']);
                    break;
                case 'analysis_field':
                    $arr[$key] = array('id' => $value['col'], 'type' => 'AF', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'workgroup_assignments':
                    $arr[$key] = array('id' => $value['attr'], 'type' => 'WA', 'club_id' => $clubId, 'name' => $value['id'], 'sub_ids' => 'all', 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'team_assignments':
                    $arr[$key] = array('id' => $value['functionIds'], 'type' => 'TA', 'club_id' => $clubId, 'name' => $value['id'], 'sub_ids' => $value['functionIds'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'role_category_assignments':
                    $arr[$key] = array('id' => $value['cat'], 'type' => 'RCA', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'fed_role_category_assignments':
                    $arr[$key] = array('id' => $value['cat'], 'type' => 'FRCA', 'club_id' => $fedId, 'name' => $value['id'], 'is_fed_cat' => 1, 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'sub_fed_role_category_assignments':
                    $arr[$key] = array('id' => $value['cat'], 'type' => 'SFRCA', 'club_id' => $fedId, 'name' => $value['id'], 'title' => $value['titleLang'], 'is_fed_cat' => 1, 'defaultTitle' => $value['title']);
                    break;
                case 'common_role_functions':
                    $arr[$key] = array('id' => $value['cat'], 'type' => 'CRF', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'filter_role_assignments':
                    $arr[$key] = array('id' => $value['attr'], 'type' => 'FRA', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'team_functions':
                    $arr[$key] = array('id' => $value['attr'], 'type' => 'TF', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'workgroup_functions':
                    $arr[$key] = array('id' => $value['role'], 'type' => 'WF', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'common_fed_role_functions':
                    $arr[$key] = array('id' => $value['cat'], 'type' => 'CFRF', 'club_id' => $fedId, 'name' => $value['id'], 'title' => $value['titleLang'], 'is_fed_cat' => 1, 'defaultTitle' => $value['title']);
                    break;
                case 'individual_role_functions':
                    $arr[$key] = array('id' => $value['role'], 'type' => 'IRF', 'club_id' => $clubId, 'name' => $value['id'], 'title' => $value['titleLang'], 'defaultTitle' => $value['title']);
                    break;
                case 'individual_fed_role_functions':
                    $arr[$key] = array('id' => $value['role'], 'type' => 'IFRF', 'club_id' => $fedId, 'name' => $value['id'], 'title' => $value['titleLang'], 'is_fed_cat' => 1, 'defaultTitle' => $value['title']);
                    break;
                case 'individual_sub_fed_role_functions':
                    $arr[$key] = array('id' => $value['role'], 'type' => 'ISFRF', 'club_id' => $fedId, 'name' => $value['id'], 'title' => $value['titleLang'], 'is_fed_cat' => 1, 'defaultTitle' => $value['title']);
                    break;
                case 'common_sub_fed_role_functions':
                    $arr[$key] = array('id' => $value['cat'], 'type' => 'CSFRF', 'club_id' => $fedId, 'name' => $value['id'], 'title' => $value['titleLang'], 'is_fed_cat' => 1, 'defaultTitle' => $value['title']);
                    break;
            }
        }

        $tableObj->setColumnData(json_encode($arr));
        $this->_em->persist($tableObj);
        $this->_em->flush();
    }

    /**
     * get the columns of existing table
     *
     * @param int $table  table id
     *
     * @return array
     */
    public function getColumnData($table)
    {
        $qs = $this->createQueryBuilder('tc')
            ->select('tc.sortOrder, tc.id as id ,tci18n.lang, tci18n.titleLang ,tc.columnType as type, IDENTITY(tc.attribute) as attr, tc.functionIds, '
                . 'IDENTITY(tc.role) as role,IDENTITY(tc.roleCategory) as cat,tc.columnSubtype as col,IDENTITY(a.attributeset) as attrset,'
                . 'tc.showProfilePicture, tc.title')
            ->leftJoin('CommonUtilityBundle:FgCmsContactTableColumnsI18n', 'tci18n', 'WITH', 'tci18n.id=tc.id')
            ->leftJoin('CommonUtilityBundle:FgRmRole', 'r', 'WITH', 'r.id = tc.role')
            ->leftJoin('CommonUtilityBundle:FgRmCategory', 'c', 'WITH', 'c.id = tc.roleCategory')
            ->leftJoin('CommonUtilityBundle:FgCmAttribute', 'a', 'WITH', 'a.id = tc.attribute')
            ->where('tc.table = :table')
            ->andWhere('tc.isDeleted = 0')
            ->orderBy('tc.sortOrder', 'ASC')
            ->setParameters(array('table' => $table))
            ->getQuery()
            ->getArrayResult();

        $result = array();
        foreach ($qs as $arr) {
            $id = $arr['sortOrder'];
            if (isset($result[$id])) {
                $result[$id]['titleLang'][$arr['lang']] = $arr['titleLang'];
            } else {
                $lang = $arr['lang'];
                $titleLang = $arr['titleLang'];
                unset($arr['titleLang']);
                unset($arr['lang']);
                $arr['titleLang'][$lang] = $titleLang;
                $result[$id] = $arr;
            }
        }
        return $result;
    }

    /**
     * This function is used to get the maximum sort order within a portrait container column
     * 
     * @param int $columnId The portrait container column id
     * 
     * @return int $sortOrder The maximum sort order value
     */
    public function getMaxSortOrderInAPortaitColumn($columnId)
    {
        $query = $this->createQueryBuilder('TC')
            ->select('MAX(TC.sortOrder) AS maxSortOrder')
            ->where('TC.column = :columnId')
            ->andWhere('TC.isDeleted = 0')
            ->setParameter('columnId', $columnId)
            ->getQuery();
        $sortOrder = $query->getSingleScalarResult();

        return ($sortOrder) ? $sortOrder : 0;
    }

    /**
     * The function is used to create a portrait container column
     * 
     * @param int    $tableId                   The table id
     * @param string $columnType                The column type
     * @param array  $tableColumnData           The table column data
     * @param int    $portraitContainerColumnId The portrait container column id
     * @param int    $clubId                    The club id
     * @param string $clubDefaultLang           The club default language
     * 
     * @return int $tableColumnId The table column id
     */
    public function createPortraitTableColumn($tableId, $columnType, $tableColumnData, $portraitContainerColumnId, $clubId, $clubDefaultLang)
    {
        $tableObj = $this->_em->getReference('CommonUtilityBundle:FgCmsContactTable', $tableId);
        $portraitContainerColumnObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPortraitContainerColumn', $portraitContainerColumnId);
        $tableColumnObj = new FgCmsContactTableColumns();
        $tableColumnObj->setTable($tableObj);
        $tableColumnObj->setColumnType($columnType);
        $tableColumnObj->setColumn($portraitContainerColumnObj);
        $tableColumnObj->setIsDeleted(0);
        switch ($columnType) {
            case 'ANALYSIS_FIELD':
            case 'FEDERATION_INFO':
            case 'FED_MEMBERSHIP_INFO':
            case 'MEMBERSHIP_INFO':
                $tableColumnObj->setColumnSubtype($tableColumnData['attributeId']);
                break;
            default:
                break;
        }
        $tableColumnId = $this->updatePortraitTableColumn($columnType, $tableColumnObj, $tableColumnData, $clubId, $clubDefaultLang);

        return $tableColumnId;
    }

    /**
     * This function is used to update the portrait table column entry
     * 
     * @param string $columnType        The column type enum value
     * @param object $curTableColumnObj The table column object
     * @param array  $tableColumnData   The table column data
     * @param int    $clubId            The club id
     * @param string $clubDefaultLang   The club default language
     * 
     * @return int Table column object id
     */
    public function updatePortraitTableColumn($columnType, $curTableColumnObj, $tableColumnData, $clubId, $clubDefaultLang)
    {
        $tableColumnObj = $this->updatePortraitTableColumnTitleAndSortOrder($curTableColumnObj, $tableColumnData, $clubDefaultLang);
        //empty value display and line break before not for profile pic/company logo
        if ($columnType !== 'PROFILE_PIC') {
            if (isset($tableColumnData['line_break_before'])) {
                $tableColumnObj->setLineBreakBefore($tableColumnData['line_break_before']);
            }
            if (isset($tableColumnData['empty_value_display'])) {
                $tableColumnObj->setEmptyValueDisplay($tableColumnData['empty_value_display']);
            }
        }
        switch ($columnType) {
            case 'PROFILE_PIC':
                if (isset($tableColumnData['linkUrl']) && $tableColumnData['linkUrl'] != '') {
                    $attributeObj = $this->_em->getReference('CommonUtilityBundle:FgCmAttribute', $tableColumnData['linkUrl']);
                    $tableColumnObj->setAttribute($attributeObj);
                }
                if (isset($tableColumnData['file_is_deleted'])) {
                    $filePath = FgUtility::getUploadDir() . '/' . $clubId . '/admin/website_portrait/' . $tableColumnObj->getProfileImage();
                    $filepath580 = FgUtility::getUploadDir() . '/' . $clubId . '/admin/website_portrait_580/' . $tableColumnObj->getProfileImage();
                    $filepath320 = FgUtility::getUploadDir() . '/' . $clubId . '/admin/website_portrait_320/' . $tableColumnObj->getProfileImage();
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    file_exists($filepath580) ? unlink($filepath580) :'';
                    file_exists($filepath320) ? unlink($filepath320) :'';
                    $tableColumnObj->setProfileImage('');
                }
                if (isset($tableColumnData['tempfilename'])) {
                    $placeholderImg = $this->savePortraitPlaceholderImage($clubId, $tableColumnData);
                    $tableColumnObj->setProfileImage($placeholderImg);
                }
                break;

            case 'CONTACT_NAME':
                if (isset($tableColumnData['linkUrl']) && $tableColumnData['linkUrl'] != '') {
                    $attributeObj = $this->_em->getReference('CommonUtilityBundle:FgCmAttribute', $tableColumnData['linkUrl']);
                    $tableColumnObj->setAttribute($attributeObj);
                }
                break;

            case 'CONTACT_FIELD':
                if (isset($tableColumnData['attributeId'])) {
                    $attributeObj = $this->_em->getReference('CommonUtilityBundle:FgCmAttribute', $tableColumnData['attributeId']);
                    $tableColumnObj->setAttribute($attributeObj);
                }
                if (isset($tableColumnData['field_display_type'])) {
                    $tableColumnObj->setFieldDisplayType($tableColumnData['field_display_type']);
                }
                break;

            case 'TEAM_ASSIGNMENTS':
                if (isset($tableColumnData['functions'])) {
                    $tableColumnObj->setFunctionIds(implode(',', $tableColumnData['functions']));
                }
                if (isset($tableColumnData['multiassignment'])) {
                    $tableColumnObj->setSeparateListing($tableColumnData['multiassignment']);
                }
                break;
              case 'FEDERATION_INFO':
                if (isset($tableColumnData['functions'])) {
                    $tableColumnObj->setFunctionIds(implode(',', $tableColumnData['functions']));
                }
                if (isset($tableColumnData['multiassignment'])) {
                    $tableColumnObj->setSeparateListing($tableColumnData['multiassignment']);
                }
                break;
            case 'COMMON_ROLE_FUNCTIONS':
            case 'COMMON_FED_ROLE_FUNCTIONS':
            case 'COMMON_SUB_FED_ROLE_FUNCTIONS':
            case 'ROLE_CATEGORY_ASSIGNMENTS':
            case 'FED_ROLE_CATEGORY_ASSIGNMENTS':
            case 'SUB_FED_ROLE_CATEGORY_ASSIGNMENTS':
                if (isset($tableColumnData['attributeId'])) {
                    $roleCatObj = $this->_em->getReference('CommonUtilityBundle:FgRmCategory', $tableColumnData['attributeId']);
                    $tableColumnObj->setRoleCategory($roleCatObj);
                }
                if (isset($tableColumnData['multiassignment'])) {
                    $tableColumnObj->setSeparateListing($tableColumnData['multiassignment']);
                }
                break;

            case 'WORKGROUP_FUNCTIONS':
            case 'INDIVIDUAL_ROLE_FUNCTIONS':
            case 'INDIVIDUAL_FED_ROLE_FUNCTIONS':
            case 'INDIVIDUAL_SUB_FED_ROLE_FUNCTIONS':
                if (isset($tableColumnData['attributeId'])) {
                    $roleObj = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->find($tableColumnData['attributeId']);
                    $tableColumnObj->setRole($roleObj)->setRoleCategory($roleObj->getCategory());
                }
                if (isset($tableColumnData['multiassignment'])) {
                    $tableColumnObj->setSeparateListing($tableColumnData['multiassignment']);
                }
                break;
                
            case 'FILTER_ROLE_ASSIGNMENTS':
            case 'TEAM_FUNCTIONS':
            case 'WORKGROUP_ASSIGNMENTS':
                if (isset($tableColumnData['multiassignment'])) {
                    $tableColumnObj->setSeparateListing($tableColumnData['multiassignment']);
                }
                break;

            //'FED_MEMBERSHIP_INFO', 'FEDERATION_INFO', 'ANALYSIS_FIELD', 'MEMBERSHIP_INFO'
            default:
                break;
        }
        //set column id of the particular data
        if (isset($tableColumnData['columnId'])) {
            $columnObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPortraitContainerColumn', $tableColumnData['columnId']);
            $tableColumnObj->setColumn($columnObj);
        }
        $this->_em->persist($tableColumnObj);
        $this->_em->flush();
        $this->_em->clear();//multiple insert was working only after clearing

        return $tableColumnObj->getId();
    }
    
    /**
     * This function is used to save the portrait placeholder image
     * 
     * @param int   $clubId The club id
     * @param array $data   The file details array
     * 
     * @return string $fileName The new filename
     */
    public function savePortraitPlaceholderImage($clubId, $data)
    {
        $fileName = '';
        if ($data['tempfilename'] != '' && $data['filename'] != '') {
            $uploadDir = FgUtility::getUploadDir();
            $uploadPath = $uploadDir . '/' . $clubId . '/admin/website_portrait';
            $uploadPath580 = $uploadDir . '/' . $clubId . '/admin/website_portrait_580';
            $uploadPath300 = $uploadDir . '/' . $clubId . '/admin/website_portrait_300';
            FgAvatar::createUploadDirectories($uploadPath);
            FgAvatar::createUploadDirectories($uploadPath580);
            FgAvatar::createUploadDirectories($uploadPath300);
            $fileName = FgUtility::getFilename($uploadPath, $data['filename']);
            $filePath = $uploadDir . '/temp/' . $data['tempfilename'];
            if (file_exists($filePath)) {
                copy($filePath, $uploadPath . '/' . $fileName);
                FgUtility::resizeImage($filePath, $uploadPath580 . '/' . $data['filename'], '580');
                FgUtility::resizeImage($filePath, $uploadPath300 . '/' . $data['filename'], '300');
                unlink($filePath);
            }
        }
        
        return $fileName;
    }
    
    /**
     * This function is used to update the portrait table column title and sort order
     * 
     * @param object $tableColumnObj  The table column object
     * @param array  $tableColumnData The table column data
     * @param string $clubDefaultLang The club default language
     * 
     * @return object $tableColumnObj The table column object
     */
    private function updatePortraitTableColumnTitleAndSortOrder($tableColumnObj, $tableColumnData, $clubDefaultLang)
    {
        $titleArr = json_decode($tableColumnData['label'], true);
        if (isset($titleArr[$clubDefaultLang])) {

            $tableColumnObj->setTitle($titleArr[$clubDefaultLang]);
        }
        if (isset($tableColumnData['sortOrder'])) {
            $tableColumnObj->setSortOrder($tableColumnData['sortOrder']);
        }

        return $tableColumnObj;
    }

    /**
     * This function is used to delete the portrait table column entry
     * 
     * @param int $tableColumnId The table column id
     */
    public function deletePortraitTableColumn($tableColumnId)
    {
        //TO DO - unlink placeholder images if any
        $tableColumnObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsContactTableColumns')->find($tableColumnId);
        if (!empty($tableColumnObj)) {
            $tableColumnObj->setIsDeleted(1);
            $tableColumnObj->setColumn(null);
            $this->_em->persist($tableColumnObj);
            $this->_em->flush();
        }
    }
}
