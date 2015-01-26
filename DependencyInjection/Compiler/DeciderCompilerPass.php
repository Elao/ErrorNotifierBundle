<?php

namespace Elao\ErrorNotifierBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class DeciderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('elao.error_notifier.decision_manager')) {
            return;
        }

        $definition = $container->findDefinition('elao.error_notifier.decision_manager');

        $taggedServices = $container->findTaggedServiceIds(
            'elao.error_notifier.decider'
        );

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addDecider',
                    array(new Reference($id))
                );
            }
        }
    }
}
