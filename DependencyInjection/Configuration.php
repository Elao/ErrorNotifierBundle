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
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('to')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($value) {
                            return array($value);
                        })
                    ->end()
                    ->treatNullLike(false)
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('from')
                    ->treatNullLike(false)
                ->end()
                ->booleanNode('handle404')
                    ->defaultValue(false)
                ->end()
                ->arrayNode('ignored404Paths')
                    ->prototype('scalar')->end()
                    ->treatNullLike(array())
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
                    ->prototype('scalar')->end()
                    ->treatNullLike(array())
                ->end()

                ->arrayNode('enabled_notifiers')
                    ->prototype('scalar')->end()
                    ->treatNullLike(array('default_mailer'))
                    ->performNoDeepMerging()
                ->end()

                ->arrayNode('notifiers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('mailer')
                            ->children()
                                ->arrayNode('to')
                                    ->beforeNormalization()
                                    ->ifString()
                                        ->then(function ($value) {
                                            return array($value);
                                        })
                                    ->end()
                                    ->treatNullLike(array())
                                    ->defaultValue(array())
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('from')
                                    ->treatNullLike(false)
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end();

        return $treeBuilder;
    }
}
