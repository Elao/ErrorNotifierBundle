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
                    ->treatNullLike(array())
                    ->treatFalseLike(array())
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->scalarNode('from')
                    ->defaultValue(false)
                    ->treatNullLike(false)
                ->end()
                ->scalarNode('mailer')
                    ->defaultValue('mailer')
                ->end()

                ->scalarNode('repeatTimeout')
                    ->defaultValue(false)
                ->end()
                ->booleanNode('handle404')
                    ->defaultValue(false)
                ->end()
                ->arrayNode('handleHTTPCodes')
                    ->beforeNormalization()
                    ->ifArray()
                        ->then(function ($array) {
                            foreach ($array as $key => $value) {
                                $array[$key] = (int) $value;
                            }

                            return $array;
                        })
                    ->end()
                    ->defaultValue(array())
                    ->treatNullLike(array())
                    ->prototype('integer')
                    ->end()
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

                ->arrayNode('ignoredCommands')
                    ->defaultValue(array())
                    ->treatNullLike(array())
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('ignoredClasses')
                    ->defaultValue(array())
                    ->treatNullLike(array())
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('ignoredPhpErrors')
                    ->beforeNormalization()
                    ->ifArray()
                        ->then(function ($array) {
                            foreach ($array as $key => $value) {
                                $array[$key] = (int) (defined($value) ? constant($value) : $value);
                            }

                            return $array;
                        })
                    ->end()
                    ->defaultValue(array())
                    ->treatNullLike(array())
                    ->prototype('integer')
                    ->end()
                ->end()
                ->arrayNode('ignored404Paths')
                    ->defaultValue(array())
                    ->treatNullLike(array())
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('ignoredIPs')
                    ->defaultValue(array())
                    ->treatNullLike(array())
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('ignoredAgentPatterns')
                    ->defaultValue(array())
                    ->treatNullLike(array())
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('ignoredUrlPatterns')
                    ->defaultValue(array())
                    ->treatNullLike(array())
                    ->prototype('scalar')
                    ->end()
                ->end()

                ->arrayNode('enabledNotifiers')
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
                        ->arrayNode('slack')
                            ->children()
                                ->scalarNode('api_token')
                                    ->treatNullLike(false)
                                    ->defaultValue(false)
                                ->end()
                                ->scalarNode('channel')
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
