<?php

namespace Elao\ErrorNotifierBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

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
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        if (count($configs[0])) {
            $config = $this->processConfiguration($configuration, $configs);

            $container->setParameter('elao.error_notifier.from', $config['from']);
            $container->setParameter('elao.error_notifier.to', $config['to']);
            $container->setParameter('elao.error_notifier.handle404', $config['handle404']);

            $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config/')));
            $loader->load('services.xml');
        }
    }
}
