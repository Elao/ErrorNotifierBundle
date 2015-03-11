<?php

namespace Elao\ErrorNotifierBundle\Handler;

use Elao\ErrorNotifierBundle\Exception\ErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;

interface ExceptionHandlerInterface
{
    /**
     * @param Request $request
     * @param Command $command
     * @param InputInterface $commandInput
     */
    public function initializeHandler(
        Request $request = null,
        Command $command = null,
        InputInterface $commandInput = null
    );

    /**
     * @see http://php.net/set_error_handler
     *
     * @param integer   $level
     * @param string    $message
     * @param string    $file
     * @param integer   $line
     * @param array     $errorContext
     *
     * @return boolean
     *
     * @throws ErrorException
     */
    public function handlePhpError($level, $message, $file, $line, $errorContext);

    /**
     * @see http://php.net/register_shutdown_function
     * Use this shutdown function to see if there were any errors
     */
    public function handlePhpFatalErrorAndWarnings();

    /**
     * @param \Exception $exception
     * @param Request $request
     */
    public function handleException(\Exception $exception, Request $request = null);
}