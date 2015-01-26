<?php

namespace Elao\ErrorNotifierBundle\Notifier;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;

interface NotifierInterface
{
    /**
     * @param FlattenException $exception
     * @param Request $request
     * @param null $context
     * @param Command $command
     * @param InputInterface $commandInput
     * @return null
     */
    public function notify(
        FlattenException $exception,
        Request $request = null,
        $context = null,
        Command $command = null,
        InputInterface $commandInput = null
    );
}
