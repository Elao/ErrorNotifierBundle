<?php

namespace Elao\ErrorNotifierBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for ElaoErrorNotifierBundle
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Get config tree
     * 
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $root = $treeBuilder->root('elao_error_notifier');

        $root
            ->children()
                ->scalarNode('to')->cannotBeEmpty()->end()
                ->scalarNode('from')->cannotBeEmpty()->end()
                ->booleanNode('handle404')->cannotBeEmpty()->defaultValue(false)->end()
            ->end();

        return $treeBuilder;
    }

}