<?php
namespace Common\UtilityBundle\Extensions;

use \DateTime;

/**
 * DateEx
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 * @author     pitsolutions.ch
 * @version    Release:1
 */
class DateEx extends DateTime
{
    /**
     * This function is used to convert to string
     *
     * @return String
     */
    public function __toString()
    {
        return $this->format('Y-m-d');

    }
}
