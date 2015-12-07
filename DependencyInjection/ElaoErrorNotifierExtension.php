<?php

namespace Elao\ErrorNotifierBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
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

        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config/')));
        // Load Twig filters by default
        $loader->load('twig.xml');

        $enabledNotifiers = $config['enabledNotifiers'];

        if (empty($enabledNotifiers)) {
            return;
        }

        $loader->load('services.xml');

        $this->addDeciderConfiguration($loader, $config, $container);

        // add tag to RegisterErrorHandlersSubscriber
        if ($config['handlePHPErrors'] || $config['handlePHPWarnings']) {
            $container
                ->getDefinition('elao.error_notifier.register_error_handlers_listener')
                ->addTag('kernel.event_listener', array(
                    'event'     => 'kernel.request',
                    'method'    => 'onKernelRequest',
                    'priority'  => '0'
                ))
                ->addTag('kernel.event_listener', array(
                    'event'     => 'console.command',
                    'method'    => 'onConsoleCommand',
                    'priority'  => '0'
                ))
            ;
        }

        if ($this->isNotifierEnabled('default_mailer', $enabledNotifiers)) {
            $this->addMailerConfiguration($loader, $config, $container);
        }

        if ($this->isNotifierEnabled('default_slack', $enabledNotifiers)) {
            $this->addSlackConfiguration($loader, $config['notifiers']['slack'], $container);
        }

        $container
            ->getDefinition('elao.error_notifier.notifier_collection')
            ->replaceArgument(0, $enabledNotifiers)
        ;
    }

    /**
     * @param XmlFileLoader $loader
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function addDeciderConfiguration(XmlFileLoader $loader, array $config, ContainerBuilder $container)
    {
        $loader->load('decision_management.xml');

        $container
            ->getDefinition('elao.error_notifier.decider.client_ip')
            ->replaceArgument(1, $config['ignoredIPs'])
        ;

        $container
            ->getDefinition('elao.error_notifier.decider.command')
            ->replaceArgument(0, $config['ignoredCommands'])
        ;

        $container
            ->getDefinition('elao.error_notifier.decider.exception_class')
            ->replaceArgument(0, $config['ignoredClasses'])
        ;

        if ($config['handle404'] && !in_array(404, $config['handleHTTPCodes'])) {
            $config['handleHTTPCodes'][] = 404;
        }

        if (!in_array(500, $config['handleHTTPCodes'])) {
            $config['handleHTTPCodes'][] = 500;
        }

        $container
            ->getDefinition('elao.error_notifier.decider.http_code')
            ->replaceArgument(0, $config['handleHTTPCodes'])
        ;

        $container
            ->getDefinition('elao.error_notifier.decider.php_error')
            ->replaceArgument(0, $config['ignoredPhpErrors'])
            ->replaceArgument(1, $config['handlePHPErrors'])
            ->replaceArgument(2, $config['handlePHPWarnings'])
            ->replaceArgument(3, $config['handleSilentErrors'])
        ;

        $container
            ->getDefinition('elao.error_notifier.decider.timeout')
            ->replaceArgument(0, $config['repeatTimeout'])
        ;

        $uriRequestMatchers = array();

        foreach ($config['ignoredUrlPatterns'] as $path) {
            $uriRequestMatchers[] = new Definition(
                new Parameter('elao.error_notifier.matcher.class'),
                array($path)
            );
        }

        $container
            ->getDefinition('elao.error_notifier.decider.uri')
            ->replaceArgument(1, $uriRequestMatchers)
        ;

        $container
            ->getDefinition('elao.error_notifier.decider.user_agent')
            ->replaceArgument(1, $config['ignoredAgentPatterns'])
        ;
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
     * Add default mailer configuration
     *
     * @param XmlFileLoader $loader
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function addMailerConfiguration(XmlFileLoader $loader, array $config, ContainerBuilder $container)
    {
        $loader->load('mailer.xml');

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

    /**
     * Add slack notifier configuration
     *
     * @param XmlFileLoader $loader
     * @param array $config
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function addSlackConfiguration(XmlFileLoader $loader, array $config, ContainerBuilder $container)
    {
        $loader->load('slack.xml');

        foreach (array('api_token', 'channel') as $field) {
            if (!$config[$field]) {
                throw new InvalidConfigurationException(sprintf(
                    'elao_error_notifier.notifiers.slack.%s must be set if default_slack is enabled',
                    $field
                ));
            }
        }

        $container
            ->getDefinition('elao.error_notifier.notifier.default_slack')
            ->replaceArgument(3, $config['channel'])
        ;

        if (!class_exists('CL\Slack\Transport\ApiClient')) {
            throw new \Exception(
                'Default Slack notifier requires the "CL\Slack\Transport\ApiClient" class, part of the cleentfaar/slack package'
            );
        }

        $container
            ->getDefinition('elao.error_notifier.client.slack')
            ->setClass('CL\Slack\Transport\ApiClient')
            ->replaceArgument(0, $config['api_token'])
        ;
    }
}
