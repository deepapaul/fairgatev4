<?php

namespace Common\UtilityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that used for configuring dbal caching
 */
class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('translator.default');
        $definition->setClass('Common\UtilityBundle\Translation\Translator');
    }
}
