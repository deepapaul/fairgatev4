<?php

namespace Common\FileServeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('common_file_serve');

        $rootNode
            ->children()
                ->scalarNode('factory')->defaultValue('php')->end()
                ->scalarNode('base_dir')->defaultValue('%kernel.root_dir%')->end()
                ->booleanNode('skip_file_exists')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
