<?php

namespace Elao\ErrorNotifierBundle\Tests\Notifier;

use Elao\ErrorNotifierBundle\Notifier\NotifierInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;

class TestNotifier implements NotifierInterface
{
    /**
     * @var boolean
     */
    private $notified = false;

    /**
     * {@inheritdoc}
     */
    public function notify(
        FlattenException $exception,
        Request $request = null,
        $context = null,
        Command $command = null,
        InputInterface $commandInput = null
    ) {
        $this->notified = true;
    }

    /**
     * @return bool
     */
    public function hasBeenNotified()
    {
        return $this->notified;
    }
}
