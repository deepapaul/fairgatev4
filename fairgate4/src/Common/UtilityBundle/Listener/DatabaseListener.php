<?php

namespace Common\UtilityBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * DatabaseListener
 *
 * @author PITSolutions <pit@pitsolutions.com>
 */
class DatabaseListener
{
    /**
     * Get core controller
     *
     * @param Object $event Event
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST == $event->getRequestType()) {
            $_controller = $event->getController();
            if (isset($_controller[0])) {
                $controller = $_controller[0];
                if (method_exists($controller, 'preExecute')) {
                    $controller->preExecute();
                }
            }
        }
    }


}
