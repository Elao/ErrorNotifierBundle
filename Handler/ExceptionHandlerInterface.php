<?php

namespace Elao\ErrorNotifierBundle\Handler;

use Elao\ErrorNotifierBundle\Exception\ErrorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;

interface ExceptionHandlerInterface
{
    /**
     * Set request object
     *
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request);

    /**
     * Set command object
     *
     * @param Command $command
     * @return $this
     */
    public function setCommand(Command $command);

    /**
     * Set command input object
     *
     * @param InputInterface $input
     * @return $this
     */
    public function setCommandInput(InputInterface $input);

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
     * @return null
     */
    public function handleException(\Exception $exception);
}