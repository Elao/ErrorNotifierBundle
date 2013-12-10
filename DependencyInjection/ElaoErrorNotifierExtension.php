<?php

namespace Elao\ErrorNotifierBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
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
            $container->setParameter('elao.error_notifier.handlePHPErrors', $config['handlePHPErrors']);
            $container->setParameter('elao.error_notifier.handlePHPWarnings', $config['handlePHPWarnings']);
            $container->setParameter('elao.error_notifier.ignored_classes', $config['ignoredClasses']);
            $container->setParameter('elao.error_notifier.repeatTimeout', $config['repeatTimeout']);

            $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config/')));
            $loader->load('services.xml');

            if ($config['mailer'] != 'mailer') {
                $container->getDefinition('elao.error_notifier.listener')->replaceArgument(0, new Reference($config['mailer']));
            }
        }
    }
}
