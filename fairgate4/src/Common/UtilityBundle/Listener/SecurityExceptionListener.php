<?php

namespace Common\UtilityBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

/**
 * ExceptionListener catches authentication exception and converts them to
 * Response instances. 
 * Overrided class
 * 
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SecurityExceptionListener extends BaseExceptionListener
{
    /**
     * @param Request $request
     */
    protected function setTargetPath(Request $request)
    {
        if ($request->isXmlHttpRequest() || strpos($request->getUri(), ".js")) {
            return;
        }
       
        parent::setTargetPath($request);
    }
}