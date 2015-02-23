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

        $enabledNotifiers = $config['enabled_notifiers'];

        if (empty($enabledNotifiers)) {
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

        $this->addRequestMatcherConfiguration($config, $container);

        if ($this->isNotifierEnabled('default_mailer', $enabledNotifiers)) {
            $this->addMailerConfiguration($config, $container);
        }

        }

        $container
            ->getDefinition('elao.error_notifier.notifier_collection')
            ->replaceArgument(0, $enabledNotifiers)
        ;
    }

    /**
     * Validate given emails
     *
     * @param array $emails
     * @param string $field
     * @throws InvalidConfigurationException
     */
    private function validateEmails($emails, $field)
    {
        if (!is_array($emails)) {
            $emails = array($emails);
        }

        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidConfigurationException(sprintf(
                    'Invalid configuration for path "elao_error_notifier.%s": This must '.
                    'be a valid email address if "default_mailer" is in the enabled_notifiers',
                    $field
                ), 500);
            }
        }
    }

    /**
     * @param $notifier
     * @param array $enabledNotifiers
     * @return bool
     */
    private function isNotifierEnabled($notifier, array $enabledNotifiers)
    {
        return in_array($notifier, $enabledNotifiers);
    }

    /**
     * Add request matcher configuration
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function addRequestMatcherConfiguration(array $config, ContainerBuilder $container)
    {
        $decisionManager = $container->getDefinition('elao.error_notifier.decision_manager.request_match');

        if (!empty($config['ignored404Paths'])) {
            foreach ($config['ignored404Paths'] as $path) {
                $decisionManager->addMethodCall(
                    'addRequestMatcher',
                    array(new Definition(new Parameter('elao.error_notifier.matcher.class'), array($path)))
                );
            }
        }
    }

    /**
     * Add default mailer configuration
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function addMailerConfiguration(array $config, ContainerBuilder $container)
    {
        $to = !empty($config['to']) ? $config['to'] : $config['notifiers']['mailer']['to'];
        $from = !empty($config['from']) ? $config['from'] :$config['notifiers']['mailer']['from'];

        $this->validateEmails($to, 'to');
        $this->validateEmails($from, 'from');

        $container
            ->getDefinition('elao.error_notifier.notifier.default_mailer')
            ->replaceArgument(2, $to)
            ->replaceArgument(3, $from)
        ;

        if ($config['mailer'] != 'mailer') {
            $container
                ->getDefinition('elao.error_notifier.notifier.default_mailer')
                ->replaceArgument(0, new Reference($config['mailer']))
            ;
        }

    }
}
