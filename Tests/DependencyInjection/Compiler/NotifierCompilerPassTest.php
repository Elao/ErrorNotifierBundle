<?php

namespace Elao\ErrorNotifierBundle\Tests\DependencyInjection\Compiler;

use Elao\ErrorNotifierBundle\DependencyInjection\Compiler\NotifierCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class NotifierCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new NotifierCompilerPass());
    }

    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DependencyInjection\Compiler\NotifierCompilerPass::process
     */
    public function it_registers_any_tagged_notifiers()
    {
        $manager = new Definition();
        $manager->setClass('Elao\ErrorNotifierBundle\DecisionManagement\Manager\NotificationDecisionManager');
        $this->setDefinition('elao.error_notifier.notifier_collection', $manager);

        $notifier = new Definition();
        $notifier->addTag('elao.error_notifier.notifier', array('alias' => 'notifier_alias'));
        $this->setDefinition('tagged_notifier', $notifier);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'elao.error_notifier.notifier_collection',
            'addNotifier',
            array(
                new Reference('tagged_notifier'),
                'notifier_alias'
            )
        );
    }
}
