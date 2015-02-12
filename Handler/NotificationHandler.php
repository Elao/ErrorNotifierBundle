<?php

namespace Elao\ErrorNotifierBundle\Handler;

use Elao\ErrorNotifierBundle\Configuration\Configuration;
use Elao\ErrorNotifierBundle\Notifier\NotifierCollection;
use Elao\ErrorNotifierBundle\Notifier\NotifierInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;

class NotificationHandler implements NotificationHandlerInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var NotifierCollection
     */
    protected $notifierCollection;

    /**
     * @param Configuration $configuration
     * @param NotifierCollection $notifierCollection
     */
    public function __construct(Configuration $configuration, NotifierCollection $notifierCollection)
    {
        $this->configuration = $configuration;
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
        if ($this->isWithinRepeatTimeout($exception)) {
            return null;
        }

        /** @var NotifierInterface $notifier */
        foreach ($this->notifierCollection->getAllEnabled() as $notifier) {
            $notifier->notify($exception, $request, $context, $command, $commandInput);
        }
    }

    /**
     * Check last send time
     *
     * @param  FlattenException $exception
     * @return bool
     */
    private function isWithinRepeatTimeout(FlattenException $exception)
    {
        if (!$this->configuration->getRepeatTimeout()) {
            return false;
        }

        $key = md5($exception->getMessage().':'.$exception->getLine().':'.$exception->getFile());
        $file = $this->configuration->getErrorsDirectory().'/'.$key;
        $time = is_file($file) ? file_get_contents($file) : 0;
        if ($time < time()) {
            file_put_contents($file, time() + $this->configuration->getRepeatTimeout());

            return false;
        }

        return true;
    }
}
