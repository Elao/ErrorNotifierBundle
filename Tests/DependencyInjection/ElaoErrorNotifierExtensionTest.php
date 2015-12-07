<?php

namespace Elao\ErrorNotifierBundle\Tests\DependencyInjection;

use Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;

class ElaoErrorNotifierExtensionTest extends AbstractExtensionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return array(
            new ElaoErrorNotifierExtension()
        );
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DependencyInjection\Configuration
     * @uses \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::processConfiguration
     * @uses \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::addDeciderConfiguration
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::isNotifierEnabled
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::load
     */
    public function it_only_loads_twig_extensions_with_default_configuration()
    {
        $this->load(array(
            'to'    => array('ignore@example.com'),
            'from'  => 'ignore@example.com',
        ));

        // twig.xml
        $this->assertContainerBuilderHasService('elao.error_notifier.dumpy_extension');
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DependencyInjection\Configuration
     * @uses \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::processConfiguration
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::addDeciderConfiguration
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::isNotifierEnabled
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::load
     */
    public function it_loads_all_base_services_when_a_notifier_is_enabled()
    {
        $this->load(array(
            'to'                    => array('ignore@example.com'),
            'from'                  => 'ignore@example.com',
            'ignoredIPs'            => array(
                '33.33.33.1',
                '33.33.33.2',
            ),
            'ignoredUrlPatterns'    => array(
                '/a-path',
                '/another-path',
            ),
            'enabledNotifiers'      => array(
                'fake_notifier',
            )
        ));

        // twig.xml
        $this->assertContainerBuilderHasService('elao.error_notifier.dumpy_extension');
        
        // services.xml
        $this->assertContainerBuilderHasService('elao.error_notifier.register_error_handlers_listener');
        $this->assertContainerBuilderHasService('elao.error_notifier.exception_handler');
        $this->assertContainerBuilderHasService('elao.error_notifier.exception_listener');
        $this->assertContainerBuilderHasService('elao.error_notifier.notification_handler');
        $this->assertContainerBuilderHasService('elao.error_notifier.notifier_collection');
        $this->assertContainerBuilderHasService('elao.error_notifier.decision_manager.request_match');

        // decision_management.xml
        $this->assertContainerBuilderHasService('elao.error_notifier.decision_manager');

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'elao.error_notifier.decider.client_ip',
            'elao.error_notifier.decider'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'elao.error_notifier.decider.client_ip',
            1,
            array('33.33.33.1', '33.33.33.2')
        );

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'elao.error_notifier.decider.exception_class',
            'elao.error_notifier.decider'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'elao.error_notifier.decider.http_code',
            'elao.error_notifier.decider'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'elao.error_notifier.decider.php_error',
            'elao.error_notifier.decider'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'elao.error_notifier.decider.timeout',
            'elao.error_notifier.decider'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'elao.error_notifier.decider.uri',
            'elao.error_notifier.decider'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'elao.error_notifier.decider.uri',
            1,
            array(
                new Definition(
                    new Parameter('elao.error_notifier.matcher.class'),
                    array('/a-path')
                ),
                new Definition(
                    new Parameter('elao.error_notifier.matcher.class'),
                    array('/another-path')
                ),
            )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'elao.error_notifier.decider.user_agent',
            'elao.error_notifier.decider'
        );
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DependencyInjection\Configuration
     * @uses \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::processConfiguration
     * @uses \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::addDeciderConfiguration
     * @uses \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::isNotifierEnabled
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension::load
     */
    public function it_add_tags_to_the_register_error_handlers_listener_when_handling_PHP_errors_or_warnings()
    {
        $this->load(array(
            'to'                => array('ignore@example.com'),
            'from'              => 'ignore@example.com',
            'handlePHPErrors'   => true,
            'handlePHPWarnings' => true,
            'enabledNotifiers'  => array(
                'fake_notifier',
            )
        ));

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'elao.error_notifier.register_error_handlers_listener',
            'kernel.event_listener',
            array(
                'event'     => 'kernel.request',
                'method'    => 'onKernelRequest',
                'priority'  => 0
            )
        );

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'elao.error_notifier.register_error_handlers_listener',
            'kernel.event_listener',
            array(
                'event'     => 'console.command',
                'method'    => 'onConsoleCommand',
                'priority'  => 0
            )
        );
    }
}