<?php

namespace Common\UtilityBundle\Controller;

/**
 * MetronicController
 *
 * This MetronicController was created for some default functions
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class MetronicController extends FgController
{
    /**
     * Unauthorized user page
     *
     */
    public function unauthorizedAction()
    {
        return $this->render('CommonUtilityBundle:partials:unauthorizeduser.html.twig', array('pagination' => $allClubAttributes));
    }
}
