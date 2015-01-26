<?php

namespace Elao\ErrorNotifierBundle\Handler;

use Elao\ErrorNotifierBundle\DecisionManagement\Manager\NotificationDecisionManagerInterface;
use Elao\ErrorNotifierBundle\Exception\ErrorException;
use Elao\ErrorNotifierBundle\Util\MemoryManagement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var NotificationDecisionManagerInterface
     */
    private $decisionManager;

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

    /**
     * Constructor.
     *
     * @param NotificationDecisionManagerInterface $decisionManager
     * @param NotificationHandlerInterface $notificationHandler
     */
    public function __construct(
        NotificationDecisionManagerInterface $decisionManager,
        NotificationHandlerInterface $notificationHandler
    ) {
        $this->decisionManager = $decisionManager;
        $this->notificationHandler = $notificationHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommandInput(InputInterface $input)
    {
        $this->commandInput = $input;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handlePhpError($level, $message, $file, $line, $errorContext)
    {
        $exception = new ErrorException($level, $message, $file, $line);

        $this->handleException($exception);

        // in order not to bypass the standard PHP error handler
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function handlePhpFatalErrorAndWarnings()
    {
        MemoryManagement::freeMemory();

        if (null === $lastError = error_get_last()) {
            return;
        }

        $exception = new ErrorException(
            @$lastError['type'],
            @$lastError['message'],
            @$lastError['file'],
            @$lastError['line'],
            @$lastError['type']
        );

        $this->handleException($exception);
    }

    /**
     * {@inheritdoc}
     */
    public function handleException(\Exception $exception)
    {
        if (false === $this->decisionManager->notifyError($exception)) {
            return;
        }

        $flattened = $exception instanceof FlattenException ? $exception : FlattenException::create($exception);

        $this->notificationHandler->notify($flattened, $this->request, null, $this->command, $this->commandInput);
    }
}
