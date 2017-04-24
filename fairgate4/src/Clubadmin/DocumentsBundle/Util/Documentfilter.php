<?php
namespace Clubadmin\DocumentsBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

class Documentfilter {

    //put your code here
    private $where;
    private $contact;
    private $filterDatas;
    private $joinarray = array();
    private $mysqlDateFormat;
    private $club;
    private $container;
    private $conn;
    private $typeArray;
    private $numbertypeArray;
    private $docType;

    /**
     * class for create filter field
     * @param type $contact     contactid
     * @param type $filterArray array
     * @param type $club        clubobject
     */
    public function __construct($container, $filterArray, $docType) {

        $this->filterDatas = $filterArray;
        $this->where = '';
        $this->iCount = 0;
        $this->mysqlDateFormat = FgSettings::getMysqlDateFormat();
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->conn = $this->container->get('database_connection');
        $this->docType = $docType;
    }

    /**
     * get the filter
     *
     * @return type
     */
    public function generateDocumentFilter() {
        foreach ($this->filterDatas as $filters) {
            $this->iCount++;
            foreach ($filters as $key => $filter) {
                list($filterType) = explode('-', $filter);

                //check the type of filter data
                switch ($filterType) {

                    case "FILE":

                        $this->fileInfofilter($filters);
                        if ($this->iCount > 1) {
                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "DOCS":
                    case "FDOCS":
                        $this->docsInfofilter($filters);
                        if ($this->iCount > 1) {
                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "DATE":

                        $this->dateInfofilter($filters);
                        if ($this->iCount > 1) {
                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "USER":
                        $this->userInfofilter($filters);
                        if ($this->iCount > 1) {
                            $this->where = "( " . $this->where . " )";
                        }

                        break;
                }
            }
        }

        return $this->where;
    }

    /**
     * file details filter
     *
     * @param array $filters
     */
    public function fileInfofilter($filters) {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
            $this->where.=" (";
        } else {
            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['entry'] != '') {
            switch ($filters['entry']) {
                case "VISIBLE_TO":
                    if ($this->docType == 'CONTACT' || $this->docType == 'CLUB') {
                        $filters['entry'] = 'fdd.is_visible_to_contact';
                    } else {
                        $filters['entry'] = 'fdd.visible_for';
                    }
                    if ($filters['condition'] == 'is not') {
                        if (isset($filters['input2']) && $filters['input2'] != 'any') {
                            $this->where.= "fdd.id NOT IN (SELECT document_id FROM fg_dm_team_functions WHERE function_id=" . $filters['input2'] . "))";
                        } elseif (isset($filters['input2']) && $filters['input2'] == 'any') {
                            $this->where.= "fdd.visible_for !='team_functions')";
                        } elseif ($filters['input1'] != '') {
                            $this->where.=$filters['entry'] . "!='" . $filters['input1'] . "')";
                        }
                    } else {
                        if (isset($filters['input2']) && $filters['input2'] != 'any') {
                            $this->where.= "fdd.id IN (SELECT document_id FROM fg_dm_team_functions WHERE function_id=" . $filters['input2'] . "))";
                        } elseif (isset($filters['input2']) && $filters['input2'] == 'any') {
                            $this->where.= "fdd.visible_for ='team_functions')";
                        } elseif ($filters['input1'] != '') {
                            $this->where.=$filters['entry'] . "='" . $filters['input1'] . "')";
                        }
                    }

                    break;
                case "DEPOSITED_WITH":
                    if ($this->docType == 'CONTACT' || $this->docType == 'CLUB') {
                        $this->depositedWith($filters);
                    } else {
                        if ($filters['condition'] == 'is not' && $filters['input1'] != 'any') {
                            $this->where.= "fdd.id NOT IN (SELECT document_id FROM fg_dm_assigment WHERE role_id=" . $filters['input1'] . "))";
                        } elseif ($filters['condition'] == 'is' && $filters['input1'] != 'any') {
                            $this->where.= "fdd.deposited_with='ALL' OR fdd.id IN (SELECT document_id FROM fg_dm_assigment WHERE role_id=" . $filters['input1'] . "))";
                        } elseif ($filters['condition'] == 'is not' && $filters['input1'] == 'any') {
                            $this->where.= "fdd.deposited_with='NONE' OR fdd.id IN (SELECT DD.id FROM fg_dm_documents DD LEFT JOIN fg_dm_assigment DDA ON DDA.document_id=DD.id  WHERE DD.deposited_with='SELECTED' AND DDA.document_type='{$this->docType}' AND DDA.id IS NULL ))";
                        } else {
                            $this->where.= "fdd.deposited_with='ALL' OR fdd.id IN(SELECT DD.id FROM fg_dm_documents DD INNER JOIN fg_dm_assigment DDA ON DDA.document_id=DD.id  WHERE DD.deposited_with='SELECTED' AND DDA.document_type='{$this->docType}'))";
                        }
                    }
                    break;
                case "DESCRIPTION":
                    $filters['entry'] = 'fdd.description';
                    if ($filters['input1'] != '' && $filters['condition'] == 'is not') {
                        $this->where.=$filters['entry'] . "!='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                    } elseif ($filters['input1'] != '' && $filters['condition'] == 'is') {
                        $this->where.=$filters['entry'] . "='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                    } elseif ($filters['input1'] != '' && $filters['condition'] == 'begins with') {
                        $this->where.=" " . $filters['entry'] . " LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
                    } elseif ($filters['input1'] != '' && $filters['condition'] == 'begins not with') {
                        $this->where.=" " . $filters['entry'] . " NOT LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
                    } elseif ($filters['input1'] != '' && $filters['condition'] == 'end with') {
                        $this->where.=" " . $filters['entry'] . "  LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
                    } elseif ($filters['input1'] != '' && $filters['condition'] == 'end not with') {
                        $this->where.=" " . $filters['entry'] . " NOT LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
                    } elseif ($filters['input1'] == '' && ( $filters['condition'] == 'end with' || $filters['condition'] == 'begins with' )) {
                        $this->where.=" " . $filters['entry'] . " ='' OR " . $filters['entry'] . " IS  NULL  )";
                    } elseif ($filters['input1'] == '' && ( $filters['condition'] == 'end not with' || $filters['condition'] == 'begins not with' )) {
                        $this->where.=" " . $filters['entry'] . " !='' AND " . $filters['entry'] . " IS NOT NULL  )";
                    } elseif ($filters['input1'] != '' && $filters['condition'] == 'contains not') {
                        $this->where.=" " . $filters['entry'] . " NOT LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
                    } elseif ($filters['input1'] == '' && $filters['condition'] == 'contains not') {
                        $this->where.=" " . $filters['entry'] . " !='' AND " . $filters['entry'] . " IS NOT NULL  )";
                    } else {
                        if ($filters['input1'] != '') {
                            $this->where.=" " . $filters['entry'] . " LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
                        } else {
                            $this->where.=" " . $filters['entry'] . " ='' OR " . $filters['entry'] . " IS  NULL  )";
                        }
                    }

                    break;
                case "ISPUBLIC":
                    $filters['entry'] = 'fdd.is_publish_link';
                    if ($filters['condition'] == 'is not') {

                        $this->where.=$filters['entry'] . "!='" . $filters['input1'] . "')";
                    } else {

                        $this->where.=$filters['entry'] . "='" . $filters['input1'] . "')";
                    }
                    break;    
                case "SIZE":
                    $filters['entry'] = 'fg_dm_version.size';
                    if ($filters['input1'] != '') {
                        $filters['input1'] = FgUtility::mbtobyteConversion($filters['input1']);
                    }
                    if (isset($filters['input2']) && $filters['input2'] != '') {
                        $filters['input2'] = FgUtility::mbtobyteConversion($filters['input2']);
                    }


                    if ($filters['condition'] == 'is between') {
                        if ($filters['input1'] == '' && (isset($filters['input2']) && $filters['input2'] == '')) {
                            $this->where.=$filters['entry'] . " !='' OR " . $filters['entry'] . " IS NOT NULL )";
                        } elseif ($filters['input1'] != '' && (isset($filters['input2']) && $filters['input2'] == '')) {
                            $this->where.=$filters['entry'] . " >= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                        } elseif ($filters['input1'] == '' && (isset($filters['input2']) && $filters['input2'] != '')) {
                            $this->where.=$filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                        } else {
                            $this->where .= $filters['entry'] . " BETWEEN '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' AND '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                        }
                    } elseif ($filters['condition'] == 'is not between') {
                        if ($filters['input1'] == '' && (isset($filters['input2']) && $filters['input2'] == '')) {
                            $this->where.=$filters['entry'] . " ='' OR " . $filters['entry'] . " IS NULL )";
                        } elseif ($filters['input1'] != '' && (isset($filters['input2']) && $filters['input2'] == '')) {
                            $this->where.=$filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
                        } elseif ($filters['input1'] == '' && (isset($filters['input2']) && $filters['input2'] != '')) {
                            $this->where.=$filters['entry'] . " > '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                        } else {
                            $this->where .= $filters['entry'] . " NOT BETWEEN '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' AND '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                        }

                        //$this->where.=" BETWEEN " . FgUtility::getSecuredData($filters['input1']) . " AND " . FgUtility::getSecuredData($filters['input2']) . ")";
                    } elseif ($filters['condition'] == 'is not') {
                        if ($filters['input1'] == '') {
                            $this->where.=$filters['entry'] . " !=''  AND " . $filters['entry'] . " IS NOT NULL )";
                        } elseif ($filters['input1'] != '') {
                            $this->where.=" " . $filters['entry'] . " != '" . $filters['input1'] . "')";
                        }
                    } elseif ($filters['condition'] == 'is') {
                        if ($filters['input1'] != '') {
                            $this->where.=" " . $filters['entry'] . " = '" . $filters['input1'] . "')";
                        } else {
                            $this->where.=" " . $filters['entry'] . " ='' OR " . $filters['entry'] . " IS  NULL  )";
                        }
                    }

                    break;
            }
        }
    }

    /**
     * document filter
     * @param array $filters
     */
    public function docsInfofilter($filters) {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
            $this->where.=" (";
        } else {
            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['input1'] == 'any') {
            $filters['input1'] = $filters['entry'];
            $filters['entry'] = "(SELECT FDS.category_id FROM fg_dm_documents DD JOIN fg_dm_document_subcategory FDS ON DD.subcategory_id=FDS.id WHERE fdd.id=DD.id)";
        } else {
            $filters['entry'] = "fdd.subcategory_id";
        }
        if ($filters['condition'] == 'is not') {
            $this->where.=$filters['entry'] . "!='" . $filters['input1'] . "')";
        } else {
            $this->where.=$filters['entry'] . "='" . $filters['input1'] . "')";
        }
    }

    /**
     * date filter
     *
     * @param array $filters
     */
    public function dateInfofilter($filters) {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
            $this->where.=" (";
        } else {
            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['entry'] != '') {
            switch ($filters['entry']) {
                case "UPLOADED":
                    if ($filters['condition'] != '') {
                        $filters['entry'] = "date_format(fg_dm_version.created_at,'%Y-%m-%d')";
                    }
                    $betweenDate = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "','".$this->mysqlDateFormat."') AND STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "','".$this->mysqlDateFormat."')";
                    break;
                case "LAST_UPDATED":
                    if ($filters['condition'] != '') {
                        $filters['entry'] = "date_format(fg_dm_version.updated_at,'%Y-%m-%d')";
                    }
                    $betweenDate = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "','".$this->mysqlDateFormat."') AND STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "','".$this->mysqlDateFormat."')";
                    break;
            }
        }

        if ($filters['condition'] != '') {
            if ($filters['condition'] == 'is between') {
                if ($filters['input1'] == '' && (isset($filters['input2']) && $filters['input2'] == '')) {
                    $this->where.=$filters['entry'] . " !='' OR " . $filters['entry'] . " IS NOT NULL AND " . $filters['entry'] . "!='00.00.0000' AND " . $filters['entry'] . "!='0000-00-00 00:00:00')";
                } elseif ($filters['input1'] != '' && (isset($filters['input2']) && $filters['input2'] == '')) {
                    $this->where.=$filters['entry'] . " >= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                } elseif ($filters['input1'] == '' && (isset($filters['input2']) && $filters['input2'] != '')) {
                    $this->where.=$filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                } else {
                    $this->where .= isset($betweenDate) ? $filters['entry'] . " BETWEEN $betweenDate)" : $filters['entry'] . " BETWEEN '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' AND '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                }
            } elseif ($filters['condition'] == 'is not between') {
                if ($filters['input1'] == '' && (isset($filters['input2']) && $filters['input2'] == '')) {
                    $this->where.=$filters['entry'] . " ='' OR " . $filters['entry'] . " IS NULL )";
                } elseif ($filters['input1'] != '' && (isset($filters['input2']) && $filters['input2'] == '')) {
                    $this->where.=$filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
                } elseif ($filters['input1'] == '' && (isset($filters['input2']) && $filters['input2'] != '')) {
                    $this->where.=$filters['entry'] . " > '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                } else {
                    $this->where .= isset($betweenDate) ? $filters['entry'] . " NOT BETWEEN $betweenDate)" : $filters['entry'] . " BETWEEN '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' AND '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                }

                //$this->where.=" BETWEEN " . FgUtility::getSecuredData($filters['input1']) . " AND " . FgUtility::getSecuredData($filters['input2']) . ")";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is' && ($filters['entry'] != 'fg_dm_version.created_at' || $filters['entry'] != 'fg_dm_version.updated_at')) {
                $this->where.=$filters['entry'] . "='00.00.0000')";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is not' && ($filters['entry'] != 'fg_dm_version.created_at' || $filters['entry'] != 'fg_dm_version.updated_at')) {
                $this->where.=$filters['entry'] . "!='00.00.0000')";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is' && ($filters['entry'] == 'fg_dm_version.created_at' || $filters['entry'] == 'fg_dm_version.updated_at')) {
                $this->where.=$filters['entry'] . "<0 || " . $filters['entry'] . " IS NULL )";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is not' && ($filters['entry'] == 'fg_dm_version.created_at' || $filters['entry'] == 'fg_dm_version.updated_at')) {
                $this->where.=$filters['entry'] . ">=0)";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'is not') {
                $input1 = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "','".$this->mysqlDateFormat."')";
                $this->where.=$filters['entry'] . "!=" . $input1 . ")";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'is') {
                $input1 = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "','".$this->mysqlDateFormat."')";
                $this->where.=$filters['entry'] . "=" . $input1 . ")";
            } else {
                if ($filters['input1'] != '') {
                    $this->where.=" " . $filters['entry'] . " LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
                } else {
                    $this->where.=" " . $filters['entry'] . " ='' OR " . $filters['entry'] . " IS  NULL  )";
                }
            }
        }
    }

    /**
     * user filter
     *
     * @param array $filters
     */
    public function userInfofilter($filters) {
        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
            $this->where.=" (";
        } else {
            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['entry'] != '') {
            switch ($filters['entry']) {
                case "UPLOADED_BY":
                    $filters['entry'] = "(SELECT contactName(dfv.created_by) FROM fg_dm_documents fd LEFT JOIN fg_dm_version dfv ON fd.current_revision=dfv.id  WHERE  fdd.id=fd.id AND fd.club_id={$this->club->get("id")})";
                    break;
                case "UPDATED_BY":
                    $filters['entry'] = "(SELECT contactName(dfv.updated_by) FROM fg_dm_documents fd LEFT JOIN fg_dm_version dfv ON fd.current_revision=dfv.id  WHERE  fdd.id=fd.id AND fd.club_id={$this->club->get("id")})";
                    break;
                case "AUTHOR":
                    $filters['entry'] = "fdd.author";
                    break;
            }

            $dateStringValue = "STR_TO_DATE('".FgUtility::getSecuredData($filters['input1'], $this->conn)."')";
            if ($filters['input1'] != '' && $filters['condition'] == 'is not') {
                $this->where.=$filters['entry'] . "!='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'is') {
                $this->where.=$filters['entry'] . "='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'begins with') {
                $this->where.=" " . $filters['entry'] . " LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'begins not with') {
                $this->where.=" " . $filters['entry'] . " NOT LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'end with') {
                $this->where.=" " . $filters['entry'] . "  LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'end not with') {
                $this->where.=" " . $filters['entry'] . " NOT LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
            } elseif ($filters['input1'] == '' && ( $filters['condition'] == 'end with' || $filters['condition'] == 'begins with' || $filters['condition'] == 'contains' )) {
                $this->where.=" " . $filters['entry'] . " ='' OR " . $filters['entry'] . " IS  NULL  )";
            } elseif ($filters['input1'] == '' && ( $filters['condition'] == 'end not with' || $filters['condition'] == 'begins not with' || $filters['condition'] == 'contains not' )) {
                $this->where.=" " . $filters['entry'] . " !='' AND " . $filters['entry'] . " IS NOT NULL  )";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'contains not') {
                $this->where.=" " . $filters['entry'] . " NOT LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'contains') {
                $this->where.=" " . $filters['entry'] . " LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
            } else {
                $this->where.=" " . $filters['entry'] . " ='' OR " . $filters['entry'] . " IS  NULL  )";
            }
        }
    }

    /**
     * For set the deposited filter for club and contact
     * @param array $filters
     */
    private function depositedWith($filters) {
        $lastname = $this->container->getParameter('system_field_lastname');
        $firstname = $this->container->getParameter('system_field_firstname');
        $likeCondition = '';
        if ($filters['input1'] != '' && $filters['condition'] == 'begins with') {
            $likeCondition = " LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%'";
        } elseif ($filters['input1'] != '' && $filters['condition'] == 'begins not with') {
            $likeCondition = " LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%'";
        } elseif ($filters['input1'] != '' && $filters['condition'] == 'end with') {
            $likeCondition = "  LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "'";
        } elseif ($filters['input1'] != '' && $filters['condition'] == 'end not with') {
            $likeCondition = "  LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "'";
        } elseif ($filters['input1'] != '' && $filters['condition'] == 'contains not') {
            $likeCondition = "  LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' ";
        } elseif ($filters['input1'] != '' && $filters['condition'] == 'contains') {
            $likeCondition = " LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' ";
        }

//for handle the club and contact filter section
        if ($this->docType == 'CONTACT') {
            if ($filters['input1'] != '') {
                $depositedwith = "SELECT  dda.document_id FROM fg_dm_assigment dda JOIN fg_cm_contact as fc ON fc.id=dda.contact_id  LEFT JOIN master_system ms ON ms.fed_contact_id=fc.id WHERE dda.document_id=fdd.id  AND  (`" . $lastname . "` " . $likeCondition . " OR `" . $firstname . "` " . $likeCondition . " OR `9` " . $likeCondition . ") GROUP BY dda.club_id  ";
                if ($filters['condition'] == 'end not with' || $filters['condition'] == 'begins not with' || $filters['condition'] == 'contains not') {
                    $this->where.=" fdd.id  NOT IN (" . $depositedwith . "))";
                } else {
                    $this->where.=" fdd.id  IN (" . $depositedwith . "))";
                }
            } else {
                $depositedwith = "(SELECT  DC.id  FROM fg_dm_documents as DC LEFT JOIN fg_dm_assigment dda ON DC.id=dda.document_id  WHERE DC.id=fdd.id AND dda.id IS NULL )  ";
                if ($filters['condition'] == 'end not with' || $filters['condition'] == 'begins not with' || $filters['condition'] == 'contains not') {
                    $this->where.=" fdd.id  NOT IN (" . $depositedwith . "))";
                } else {
                    $this->where.=" fdd.id  IN (" . $depositedwith . "))";
                }
            }
        } else {
            if ($filters['input1'] != '') {
                if ($filters['condition'] == 'end not with' || $filters['condition'] == 'begins not with' || $filters['condition'] == 'contains not') {
                    $depositedwith = "(SELECT distinct(dda.document_id)  FROM fg_dm_documents as CD LEFT JOIN fg_dm_assigment dda ON CD.id=dda.document_id LEFT JOIN fg_club as fc ON fc.id=dda.club_id WHERE CD.id=fdd.id AND fc.title " . $likeCondition . ") ";
                    $this->where.=" fdd.id NOT IN (" . $depositedwith . ") AND fdd.deposited_with !='all' )";
                } else {
                    $depositedwith = "(SELECT distinct(dda.document_id)  FROM fg_dm_documents as CD LEFT JOIN fg_dm_assigment dda ON CD.id=dda.document_id LEFT JOIN fg_club as fc ON fc.id=dda.club_id WHERE dda.document_id=fdd.id AND fc.title " . $likeCondition . ") ";
                    $this->where.=" fdd.id IN (" . $depositedwith . ") OR fdd.deposited_with='all' )";
                }
            } else {
                if ($filters['condition'] == 'end not with' || $filters['condition'] == 'begins not with' || $filters['condition'] == 'contains not') {
                    $depositedwith = "(SELECT  DC.id  FROM fg_dm_documents as DC LEFT JOIN fg_dm_assigment dda ON DC.id=dda.document_id  WHERE DC.id=fdd.id  AND dda.id IS NULL )";
                    $this->where.=" fdd.id NOT IN (" . $depositedwith . ") OR (fdd.deposited_with='all' AND  fdd.deposited_with !='NONE'))";
                } else {
                    $depositedwith = "(SELECT  DC.id  FROM fg_dm_documents as DC LEFT JOIN fg_dm_assigment dda ON DC.id=dda.document_id  WHERE DC.id=fdd.id AND DC.deposited_with !='all' AND dda.id IS NULL) ";
                    $this->where.=" fdd.id IN (" . $depositedwith . "))";
                }
            }
        }
    }
}
