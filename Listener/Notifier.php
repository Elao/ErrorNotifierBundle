<?php

namespace Elao\ErrorNotifierBundle\Listener;

use \Swift_Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\HttpKernel\Exception\FlattenException;

/**
 * Notifier
 */
class Notifier
{

    /**
     * @var Swift_Mailer $mailer
     */
    private $mailer;

    /**
     * @var EngineInterface $templating
     */
    private $templating;

    /**
     * @var string
     */
    private $errorsDir;

    private $from;
    private $to;
    private $request;
    private $handle404;
    private $ignoredClasses;
    private $reportWarnings = false;
    private $reportErrors   = false;
    private $reportSilent   = false;
    private $repeatTimeout  = false;
    private $command;
    private $commandInput;

    private static $tmpBuffer = null;

    /**
     * The constructor
     *
     * @param Swift_Mailer    $mailer     mailer
     * @param EngineInterface $templating templating
     * @param string          $cacheDir   cacheDir
     * @param array           $config     configure array
     */
    public function __construct(Swift_Mailer $mailer, EngineInterface $templating, $cacheDir, $config)
    {
        $this->mailer         = $mailer;
        $this->templating     = $templating;
        $this->from           = $config['from'];
        $this->to             = $config['to'];
        $this->handle404      = $config['handle404'];
        $this->reportErrors   = $config['handlePHPErrors'];
        $this->reportWarnings = $config['handlePHPWarnings'];
        $this->reportSilent   = $config['handleSilentErrors'];
        $this->ignoredClasses = $config['ignoredClasses'];
        $this->repeatTimeout  = $config['repeatTimeout'];
        $this->errorsDir      = $cacheDir.'/errors';

        if (!is_dir($this->errorsDir)) {
            mkdir($this->errorsDir);
        }
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

        $exception = $event->getException();

        if ($exception instanceof HttpException) {
            if (500 === $exception->getStatusCode() || (404 === $exception->getStatusCode() && true === $this->handle404)) {
                $this->createMailAndSend($exception, $event->getRequest());
            }
        } else {
            $sendMail = !in_array(get_class($exception), $this->ignoredClasses);

            if ($sendMail === true) {
                $this->createMailAndSend($exception, $event->getRequest());
            }
        }
    }

    /**
     * Handle the event
     *
     * @param ConsoleExceptionEvent $event event
     */
    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        $exception = $event->getException();

        $sendMail = !in_array(get_class($exception), $this->ignoredClasses);

        if ($sendMail === true) {
            $this->createMailAndSend($exception, null, null, $this->command, $this->commandInput);
        }
    }

    /**
     * Once we have the request we can use it to show debug details in the email
     *
     * Ideally the handlers would be registered earlier on in the boot process
     * so that compilation errors (like missing config files) could be caught
     * but that would mean that the DI Container wouldn't be completed so we'd
     * have to mess around with instantiating the mailer and twig etc
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->reportErrors || $this->reportWarnings) {
            self::_reserveMemory();

            $this->request = $event->getRequest();

            $this->setErrorHandlers();
        }
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $this->request = null;

        $this->command = $event->getCommand();
        $this->commandInput = $event->getInput();

        if ($this->reportErrors || $this->reportWarnings) {
            self::_reserveMemory();

            $this->setErrorHandlers();
        }
    }

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
     * @see http://php.net/set_error_handler
     *
     * @param integer $level
     * @param string  $message
     * @param string  $file
     * @param integer $line
     *
     * @throws ErrorException
     */
    public function handlePhpError($level, $message, $file, $line, $errcontext)
    {
        // don't catch error with error_repoting is 0
        if (0 === error_reporting() && false === $this->reportSilent) {
            return false;
        }

        // there would be more warning codes but they are not caught by set_error_handler
        // but by register_shutdown_function
        $warningsCodes = array(E_NOTICE, E_USER_WARNING, E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED);

        if (!$this->reportWarnings && in_array($level, $warningsCodes)) {
            return false;
        }

        $exception = new \ErrorException(sprintf('%s: %s in %s line %d', $this->getErrorString($level), $message, $file, $line), 0, $level, $file, $line);

        $this->createMailAndSend($exception, $this->request, $errcontext, $this->command, $this->commandInput);

        // in order not to bypass the standard PHP error handler
        return false;
    }

    /**
     * @see http://php.net/register_shutdown_function
     * Use this shutdown function to see if there were any errors
     */
    public function handlePhpFatalErrorAndWarnings()
    {
        self::_freeMemory();

        $lastError = error_get_last();

        if (is_null($lastError)) {
            return;
        }

        $errors = array();

        if ($this->reportErrors) {
            $errors = array_merge($errors, array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR));
        }

        if ($this->reportWarnings) {
            $errors = array_merge($errors, array(E_CORE_WARNING, E_COMPILE_WARNING, E_STRICT));
        }

        if (in_array($lastError['type'], $errors)) {
            $exception = new \ErrorException(sprintf('%s: %s in %s line %d', @$this->getErrorString(@$lastError['type']), @$lastError['message'], @$lastError['file'], @$lastError['line']),  @$lastError['type'], @$lastError['type'], @$lastError['file'], @$lastError['line']);
            $this->createMailAndSend($exception, $this->request, null, $this->command, $this->commandInput);
        }
    }

    /**
     * Convert the error code to a readable format
     *
     * @param  integer $errorNo
     * @return string
     */
    public function getErrorString($errorNo)
    {
        // may be exhaustive, but not sure
        $errorStrings = array(
            E_WARNING           => 'Warning',
            E_NOTICE            => 'Notice',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Runtime Notice (E_STRICT)',
            E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
            E_ERROR             => 'Error',
            E_PARSE             => 'Parse Error',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        );

        return array_key_exists($errorNo, $errorStrings) ? $errorStrings[$errorNo] : 'UNKNOWN';
    }

    /**
     * @param ErrorException $exception
     * @param Request        $request
     * @param array          $context
     * @param Command        $command
     * @param InputInterface $commandInput
     */
    public function createMailAndSend($exception, Request $request = null, $context = null, Command $command = null, InputInterface $commandInput = null)
    {
        if (!$exception instanceof FlattenException) {
            $exception = FlattenException::create($exception);
        }
        if ($this->repeatTimeout && $this->checkRepeat($exception)) {
            return;
        }
        if(is_null($context)) {
            $context = get_defined_vars();
        }

        $body = $this->templating->render('ElaoErrorNotifierBundle::mail.html.twig', array(
            'exception'       => $exception,
            'request'         => $request,
            'status_code'     => $exception->getCode(),
            'context'         => $context,
            'command'         => $command,
            'command_input'   => $commandInput
        ));

        if($this->request) {
            $subject = '[' . $request->headers->get('host') . '] Error ' . $exception->getStatusCode() . ': ' . $exception->getMessage();
        } elseif($this->command) {
            $subject = '[' . $this->command->getName() . '] Error ' . $exception->getStatusCode() . ': ' . $exception->getMessage();
        } else {
            $subject = 'Error ' . $exception->getStatusCode() . ': ' . $exception->getMessage();
        }

        if (function_exists('mb_substr')) {
            $subject = mb_substr($subject, 0, 255);
        } else {
            $subject = substr($subject, 0, 255);
        }

        $mail = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->from)
            ->setTo($this->to)
            ->setContentType('text/html')
            ->setBody($body);

        $this->mailer->send($mail);
    }

    /**
     * Check last send time
     *
     * @param  FlattenException $exception
     * @return bool
     */
    private function checkRepeat(FlattenException $exception)
    {
        $key = md5($exception->getMessage().':'.$exception->getLine().':'.$exception->getFile());
        $file = $this->errorsDir.'/'.$key;
        $time = is_file($file) ? file_get_contents($file) : 0;
        if ($time < time()) {
            file_put_contents($file, time() + $this->repeatTimeout);

            return false;
        }

        return true;
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
