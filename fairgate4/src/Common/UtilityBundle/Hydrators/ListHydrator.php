<?php

namespace Common\UtilityBundle\Hydrators;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

/**
 * This class is used to convert query result to an associative structure
 *
 * @author Rajasree P <rajasree.p@pitsolutions.com>
 */
class ListHydrator extends AbstractHydrator
{

    /**
     * This function is used to iterate through the result and convert to nested array
     * Few more customization needs to be done for using in this solution
     *
     * @author Rajasree P <rajasree.p@pitsolutions.com>
     *
     * @return Array
     */
    protected function hydrateAllData()
    {
        $result = array();
        $l = 0;
        $id = '';
        foreach ($this->_stmt->fetchAll(PDO::FETCH_ASSOC) as $key => $arr) {
            if (count($arr) > 0) {
                if ($arr['id0'] == $id) {
                    $result[$id][$arr['lang5']] = array('title_lag' => $arr['title_lang4']);
                } else {
                    $id = $arr['id0'];
                    $result[$id] = array('title' => $arr['title1'], 'sort_order' => $arr['sort_order2'], 'is_active' => $arr['is_active3']);
                    $result[$id][$arr['lang5']] = array('title_lag' => $arr['title_lang4']);
                }
            }
        }

        return $result;

    }


}
