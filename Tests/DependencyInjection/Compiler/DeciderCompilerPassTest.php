<?php

namespace Elao\ErrorNotifierBundle\Tests\DependencyInjection\Compiler;

use Elao\ErrorNotifierBundle\DependencyInjection\Compiler\DeciderCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DeciderCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DeciderCompilerPass());
    }

    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\Compiler\DeciderCompilerPass::process
     */
    public function it_registers_any_tagged_deciders()
    {
        $collection = new Definition();
        $collection->setClass('Elao\ErrorNotifierBundle\Notifier\NotifierCollection');
        $this->setDefinition('elao.error_notifier.decision_manager', $collection);

        $decider = new Definition();
        $decider->addTag('elao.error_notifier.decider');
        $this->setDefinition('tagged_decider', $decider);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'elao.error_notifier.decision_manager',
            'addDecider',
            array(
                new Reference('tagged_decider')
            )
        );
    }
}
