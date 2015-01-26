<?php

namespace Elao\ErrorNotifierBundle\Handler;

use Elao\ErrorNotifierBundle\Notifier\NotifierCollection;
use Elao\ErrorNotifierBundle\Notifier\NotifierInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;

class NotificationHandler implements NotificationHandlerInterface
{
    /**
     * @var NotifierCollection
     */
    private $notifierCollection;

    /**
     * Constructor.
     *
     * @param NotifierCollection $notifierCollection
     */
    public function __construct(NotifierCollection $notifierCollection)
    {
        $this->notifierCollection = $notifierCollection;
    }

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
        /** @var NotifierInterface $notifier */
        foreach ($this->notifierCollection->getAllEnabled() as $notifier) {
            $notifier->notify($exception, $request, $context, $command, $commandInput);
        }
    }
}
