<?php

namespace Elao\ErrorNotifierBundle;

use Elao\ErrorNotifierBundle\DependencyInjection\Compiler\DeciderCompilerPass;
use Elao\ErrorNotifierBundle\DependencyInjection\Compiler\NotifierCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * ElaoErrorNotifier Bundle
 */
class ElaoErrorNotifierBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DeciderCompilerPass());
        $container->addCompilerPass(new NotifierCompilerPass());
    }
}
