<?php

namespace Elao\ErrorNotifierBundle\Handler;

use Elao\ErrorNotifierBundle\Configuration\Configuration;
use Elao\ErrorNotifierBundle\Exception\ErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var NotificationHandlerInterface
     */
    private $notificationHandler;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var InputInterface
     */
    private $commandInput;

    private static $tmpBuffer = null;

    /**
     * @param Configuration $configuration
     * @param NotificationHandlerInterface $notificationHandler
     */
    public function __construct(Configuration $configuration, NotificationHandlerInterface $notificationHandler)
    {
        $this->configuration = $configuration;
        $this->notificationHandler = $notificationHandler;

        if (!is_dir($configuration->getErrorsDirectory())) {
            mkdir($configuration->getErrorsDirectory());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initializeHandler(
        Request $request = null,
        Command $command = null,
        InputInterface $commandInput = null
    ) {
        $this->request = $request;
        $this->command = $command;
        $this->commandInput = $commandInput;

        if (!$this->configuration->handlePHPErrors() && !$this->configuration->handlePHPWarnings()) {
            return;
        }

        self::_reserveMemory();

        $this->setErrorHandlers();
    }

    /**
     * {@inheritdoc}
     */
    public function handlePhpError($level, $message, $file, $line, $errorContext)
    {
        // don't catch error with error_reporting is 0
        if (0 === error_reporting() && !$this->configuration->handleSilentErrors()) {
            return false;
        }

        if (!$this->configuration->handleWarning($level)) {
            return false;
        }

        $exception = new ErrorException($level, $message, $file, $line);

        $this->handleException($exception, $this->request, $errorContext, $this->command, $this->commandInput);

        // in order not to bypass the standard PHP error handler
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function handlePhpFatalErrorAndWarnings()
    {
        self::_freeMemory();

        if (null === $lastError = error_get_last()) {
            return;
        }

        if ($this->configuration->handleWarning($lastError['type'])) {
            $exception = new ErrorException(
                @$lastError['type'],
                @$lastError['message'],
                @$lastError['file'],
                @$lastError['line'],
                @$lastError['type']
            );

            $this->handleException($exception, $this->request);
        }
    }

    /**
     * @param \Exception $exception
     * @param Request $request
     */
    public function handleException(\Exception $exception, Request $request = null)
    {
        $flattened = $exception instanceof FlattenException ? $exception : FlattenException::create($exception);

        if ($exception instanceof HttpException && !$this->configuration->handleError($flattened->getStatusCode())) {
            return;
        }

        if ($this->configuration->ignoreExceptionClass($flattened)) {
            return;
        }

        $this->notificationHandler->notify($flattened, $request, null, $this->command, $this->commandInput);
    }

    /**
     * @return null
     */
    protected function setErrorHandlers()
    {
        // set_error_handler and register_shutdown_function can be triggered on
        // both warnings and errors
        set_error_handler(array($this, 'handlePhpError'), E_ALL);

        // From PHP Documentation: the following error types cannot be handled with
        // a user defined function using set_error_handler: *E_ERROR*, *E_PARSE*, *E_CORE_ERROR*, *E_CORE_WARNING*,
        // *E_COMPILE_ERROR*, *E_COMPILE_WARNING*
        // That is we need to use also register_shutdown_function()
        register_shutdown_function(array($this, 'handlePhpFatalErrorAndWarnings'));
    }

    /**
     * This allows to catch memory limit fatal errors.
     */
    protected static function _reserveMemory()
    {
        self::$tmpBuffer = str_repeat('x', 1024 * 500);
    }

    protected static function _freeMemory()
    {
        self::$tmpBuffer = '';
    }
}
