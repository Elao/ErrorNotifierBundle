<?php

namespace Elao\ErrorNotifierBundle\Listener;

use Elao\ErrorNotifierBundle\Handler\ExceptionHandlerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

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
     * Once we have the request we can use it to show debug details in the email
     *
     * Ideally the handlers would be registered earlier on in the boot process
     * so that compilation errors (like missing config files) could be caught
     * but that would mean that the DI Container wouldn't be completed so we'd
     * have to mess around with instantiating the mailer and twig etc
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->exceptionHandler->initializeHandler($event->getRequest());
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $this->exceptionHandler->initializeHandler(null, $event->getCommand(), $event->getInput());
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
