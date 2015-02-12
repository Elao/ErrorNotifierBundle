<?php

namespace Elao\ErrorNotifierBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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
        $config = $this->processConfiguration($configuration, $configs);

        if (!$config['enabled']) {
            return;
        }

        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config/')));
        $loader->load('services.xml');

        $container
            ->getDefinition('elao.error_notifier.configuration')
            ->replaceArgument(1, $config['handle404'])
            ->replaceArgument(2, $config['handlePHPErrors'])
            ->replaceArgument(3, $config['handlePHPWarnings'])
            ->replaceArgument(4, $config['handleSilentErrors'])
            ->replaceArgument(5, $config['repeatTimeout'])
            ->replaceArgument(6, $config['ignoredClasses'])
        ;

        $enabledNotifiers = $config['enabledNotifiers'];

        if (in_array('default_mailer', $enabledNotifiers)) {
            foreach (array('to', 'from') as $field) {
                if (!filter_var($config[$field], FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidConfigurationException(sprintf(
                        'Invalid configuration for path "elao_error_notifier.%s": This must be a valid email address if "default_mailer" is in the enabled_notifiers',
                        $field
                    ), 500);
                }
            }

            $container
                ->getDefinition('elao.error_notifier.notifier.default_mailer')
                ->addTag('elao.error_notifier', array('alias' => 'elao.default_mailer'))
                ->replaceArgument(2, $config['to'])
                ->replaceArgument(3, $config['from'])
            ;

            if ($config['mailer'] != 'mailer') {
                $container
                    ->getDefinition('elao.error_notifier.notifier.default_mailer')
                    ->replaceArgument(0, new Reference($config['mailer']))
                ;
            }
        }

        $container
            ->getDefinition('elao.error_notifier.notifier_collection')
            ->replaceArgument(0, $enabledNotifiers)
        ;
    }
}
