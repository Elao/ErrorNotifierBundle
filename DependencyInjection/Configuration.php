<?php

/*
 * This file is part of the Elao ErrorNotifier Bundle
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

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
                ->arrayNode('to')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($value) {
                            return array($value);
                        })
                    ->end()
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('from')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function ($value) {
                            return array($value);
                        })
                    ->end()
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->prototype('scalar')
                    ->end()
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
                    ->end()
                    ->treatNullLike(array())
                ->end()
                ->arrayNode('ignoredPhpErrors')
                    ->prototype('scalar')
                    ->end()
                    ->treatNullLike(array())
                ->end()
                ->arrayNode('ignoredIPs')
                    ->prototype('scalar')
                    ->end()
                    ->treatNullLike(array())
                ->end()
                ->scalarNode('ignoredAgentsPattern')
                    ->defaultValue('')
                ->end()
                ->scalarNode('ignoredUrlsPattern')
                    ->defaultValue('')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
