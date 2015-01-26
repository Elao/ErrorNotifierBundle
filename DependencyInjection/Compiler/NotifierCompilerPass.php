<?php

namespace Elao\ErrorNotifierBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class NotifierCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('elao.error_notifier.notifier_collection')) {
            return;
        }

        $definition = $container->findDefinition('elao.error_notifier.notifier_collection');

        $taggedServices = $container->findTaggedServiceIds(
            'elao.error_notifier.notifier'
        );

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addNotifier',
                    array(new Reference($id), $attributes['alias'])
                );
            }
        }
    }
}
