<?php

namespace Elao\ErrorNotifierBundle\Listener;

use Elao\ErrorNotifierBundle\Handler\ExceptionHandlerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;

/**
 * Exception Listener
 */
class ExceptionListener
{
    /**
     * @var ExceptionHandlerInterface
     */
    private $exceptionHandler;

    /**
     * The constructor
     *
     * @param ExceptionHandlerInterface $exceptionHandler
     */
    public function __construct(ExceptionHandlerInterface $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Handle the event
     *
     * @param GetResponseForExceptionEvent $event event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $this->exceptionHandler->handleException($event->getException());
    }

    /**
     * Handle the event
     *
     * @param ConsoleExceptionEvent $event event
     */
    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $this->exceptionHandler->handleException($event->getException());
    }
}
