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
                ->scalarNode('to')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('from')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('handle404')
                    ->defaultValue(false)
                ->end()
                ->arrayNode('handleHTTPcodes')
                    ->prototype('scalar')
                    ->treatNullLike(array())
                    ->end()
                ->end()
                ->scalarNode('mailer')
                    ->defaultValue('mailer')
                ->end()
                ->scalarNode('repeatTimeout')
                    ->defaultValue(false)
                ->end()
                ->booleanNode('handlePHPWarnings')
                    ->defaultValue(false)
                ->end()
                ->booleanNode('handlePHPErrors')
                    ->defaultValue(false)
                ->end()
                ->booleanNode('handleSilentErrors')
                    ->defaultValue(false)
                ->end()
                ->arrayNode('ignoredClasses')
                    ->prototype('scalar')
                    ->treatNullLike(array())
                ->end()
            ->end();

        return $treeBuilder;
    }
}
