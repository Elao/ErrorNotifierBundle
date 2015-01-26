<?php

namespace Elao\ErrorNotifierBundle\Tests\DependencyInjection;

use Elao\ErrorNotifierBundle\DependencyInjection\Configuration;
use Elao\ErrorNotifierBundle\DependencyInjection\ElaoErrorNotifierExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtension()
    {
        return new ElaoErrorNotifierExtension();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration();
    }

    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function it_converts_string_to_array_for_to_parameter()
    {
        $this->assertProcessedConfigurationEquals(
            array_merge(
                $this->getDefaultConfiguration(),
                array(
                    'to'    => array(
                        'test@example.com',
                    ),
                    'from'  => 'test@example.com'
                )
            ),
            array(
                __DIR__ . '/../Fixtures/config_default.yml'
            )
        );
    }

    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function it_converts_string_to_integers_for_handle_http_codes_parameter()
    {
        $this->assertProcessedConfigurationEquals(
            array_merge(
                $this->getDefaultConfiguration(),
                array(
                    'to'                => array(
                        'test@example.com',
                    ),
                    'from'              => 'test@example.com',
                    'handleHTTPCodes'   => array(
                        400,
                    )
                )
            ),
            array(
                __DIR__ . '/../Fixtures/config_handle_http_codes.yml'
            )
        );
    }

    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\Configuration::getConfigTreeBuilder
     */
    public function it_converts_php_error_constant_names_to_values()
    {
        $this->assertProcessedConfigurationEquals(
            array_merge(
                $this->getDefaultConfiguration(),
                array(
                    'to'                => array(
                        'test@example.com',
                    ),
                    'from'              => 'test@example.com',
                    'ignoredPhpErrors'  => array(
                        E_NOTICE,
                        E_USER_NOTICE,
                    )
                )
            ),
            array(
                __DIR__ . '/../Fixtures/config_ignored_php_errors.yml'
            )
        );
    }

    /**
     * @return array
     */
    private function getDefaultConfiguration()
    {
        return array(
            'to'                    => array(),
            'from'                  => null,
            'mailer'                => 'mailer',
            'repeatTimeout'         => false,
            'handle404'             => false,
            'handleHTTPCodes'       => array(),
            'handlePHPWarnings'     => false,
            'handlePHPErrors'       => false,
            'handleSilentErrors'    => false,
            'ignoredClasses'        => array(),
            'ignoredPhpErrors'      => array(),
            'ignored404Paths'       => array(),
            'ignoredIPs'            => array(),
            'ignoredAgentPatterns'  => array(),
            'ignoredUrlPatterns'    => array(),
            'enabledNotifiers'      => array(),
            'notifiers'             => array(),
        );
    }
}
