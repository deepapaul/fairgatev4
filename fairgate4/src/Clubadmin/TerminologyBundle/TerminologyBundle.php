<?php

namespace Clubadmin\TerminologyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use \Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * For handle terminology
 */
class TerminologyBundle extends Bundle
{
     private static $containerInstance = null;
/**
 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
 */
   public function setContainer(ContainerInterface $container = null)
   {
        parent::setContainer($container);
        self::$containerInstance = $container;
   }
/**
 * @return type
 */
   public static function getContainer()
   {
        return self::$containerInstance;
   }
}
