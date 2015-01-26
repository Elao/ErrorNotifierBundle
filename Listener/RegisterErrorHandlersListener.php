<?php

namespace Elao\ErrorNotifierBundle\Listener;

use Elao\ErrorNotifierBundle\Handler\ExceptionHandlerInterface;
use Elao\ErrorNotifierBundle\Util\MemoryManagement;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RegisterErrorHandlersListener
{
    /**
     * @var ExceptionHandlerInterface
     */
    private $exceptionHandler;

    /**
     * Constructor.
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
        $this->exceptionHandler->setRequest($event->getRequest());
        $this->registerErrorHandlers();
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $this->exceptionHandler
            ->setCommand($event->getCommand())
            ->setCommandInput($event->getInput())
        ;
        $this->registerErrorHandlers();
    }

    /**
     * Register PHP error handlers
     *
     * @return null
     */
    private function registerErrorHandlers()
    {
        MemoryManagement::reserveMemory();

        // set_error_handler and register_shutdown_function can be triggered on
        // both warnings and errors
        set_error_handler(array($this->exceptionHandler, 'handlePhpError'), E_ALL);

        // From PHP Documentation: the following error types cannot be handled with
        // a user defined function using set_error_handler: *E_ERROR*, *E_PARSE*, *E_CORE_ERROR*, *E_CORE_WARNING*,
        // *E_COMPILE_ERROR*, *E_COMPILE_WARNING*
        // That is we need to use also register_shutdown_function()
        register_shutdown_function(array($this->exceptionHandler, 'handlePhpFatalErrorAndWarnings'));
    }
}