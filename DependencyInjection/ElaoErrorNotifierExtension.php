<?php

/*
 * This file is part of the Elao ErrorNotifier Bundle
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\ErrorNotifierBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * ElaoErrorNotifier Extension
 */
class ElaoErrorNotifierExtension extends Extension
{
    /**
     * load configuration
     *
     * @param array            $configs   configs
     * @param ContainerBuilder $container container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        if (count($configs[0])) {
            $config = $this->processConfiguration($configuration, $configs);

            $container->setParameter('elao.error_notifier.config', $config);

            $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__ . '/../Resources/config/')));
            $loader->load('services.xml');

            $definition = false;

            if ($config['mailer'] != 'mailer') {
                $definition = $container->getDefinition('elao.error_notifier.listener');
                $definition->replaceArgument(0, new Reference($config['mailer']));
            }

            if ($config['lazyLoad']) {
                $definition = $definition ?: $container->getDefinition('elao.error_notifier.listener');
                $definition->setLazy($config['lazyLoad']);
            }
        }
    }
}
