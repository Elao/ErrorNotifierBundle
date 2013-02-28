<?php

namespace Elao\ErrorNotifierBundle\Listener;

use \Swift_Mailer;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
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

    private $from;

    private $to;

    private $handle404;

    private $reportWarnings = false;
    private $reportErrors = false;

    private $request;

    private static $tmpBuffer = null;

    /**
     * __construct
     *
     * @param Swift_Mailer    $mailer     mailer
     * @param EngineInterface $templating templating
     * @param string          $from       send mail from
     * @param string          $to         send mail to
     * @param boolean         $handle404  handle 404 error ?
     */
    public function __construct(Swift_Mailer $mailer, EngineInterface $templating, $from, $to, $handle404 = false, $handlePHPErrors = false, $handlePHPWarnings = false)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;

        $this->from = $from;
        $this->to = $to;
        $this->handle404 = $handle404;

        $this->reportErrors = $handlePHPErrors;
        $this->reportWarnings = $handlePHPWarnings;
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
            $this->createMailAndSend($exception, $event->getRequest());
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

            // set_error_handler and register_shutdown_function can be triggered on
            // both warnings and errors
            set_error_handler(array($this, 'handlePhpError'), E_ALL);

            // From PHP Documentation: the following error types cannot be handled with
            // a user defined function using set_error_handler: *E_ERROR*, *E_PARSE*, *E_CORE_ERROR*, *E_CORE_WARNING*,
            // *E_COMPILE_ERROR*, *E_COMPILE_WARNING*
            // That is we need to use also register_shutdown_function()
            register_shutdown_function(array($this, 'handlePhpFatalErrorAndWarnings'));
        }

    }

    /**
     *
     * @see http://php.net/set_error_handler
     * @param integer $level
     * @param string  $message
     * @param string  $file
     * @param integer $line
     *
     * @throws ErrorException
     */
    public function handlePhpError($level, $message, $file, $line, $errcontext)
    {

        // there would be more warning codes but they are not caught by set_error_handler
        // but by register_shutdown_function
        $warningsCodes = array(E_NOTICE, E_USER_WARNING, E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED);

        if (!$this->reportWarnings && in_array($level, $warningsCodes)) {
            return false;
        }

        $exception = new \ErrorException(sprintf('%s: %s in %s line %d', $this->getErrorString($level), $message, $file, $line), 0, $level, $file, $line);

        $this->createMailAndSend($exception, $this->request, $errcontext);

        return false; // in order not to bypass the standard PHP error handler
    }

    /**
     * @see http://php.net/register_shutdown_function
     * Use this shutdown function to see if there were any errors
     */
    public function handlePhpFatalErrorAndWarnings()
    {
        self::_freeMemory();

        $lastError = error_get_last();
        if (is_null($lastError))
            return;

        $errors = array();

        if ($this->reportErrors) {
            $errors = array_merge($errors, array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR));
        }

        if ($this->reportWarnings) {
            $errors = array_merge($errors, array(E_CORE_WARNING, E_COMPILE_WARNING, E_STRICT));
        }

        if (in_array($lastError['type'], $errors)) {
            $exception = new \ErrorException(sprintf('%s: %s in %s line %d', @$this->getErrorString(@$lastError['type']), @$lastError['message'], @$lastError['file'], @$lastError['line']),  @$lastError['type'], @$lastError['type'], @$lastError['file'], @$lastError['line']);
            $this->createMailAndSend($exception, $this->request);
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

            E_ERROR => 'Error',
            E_PARSE => 'Parse Error',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        );

        return array_key_exists($errorNo, $errorStrings) ? $errorStrings[$errorNo] : 'UNKNOWN';

    }

    /**
     *
     * @param ErrorException $exception
     * @param Request        $request
     * @param array          $context
     */
    public function createMailAndSend($exception, $request, $context = null)
    {

        if (!$exception instanceof FlattenException) {
            $exception = FlattenException::create($exception);
        }
        
        $body = $this->templating->render('ElaoErrorNotifierBundle::mail.html.twig', array(
            'exception'       => $exception,
            'request'         => $request,
            'status_code'     => $exception->getCode(),
            'context'         => $context
        ));
        
        $subject = '[' . $request->headers->get('host') . '] Error ' . $exception->getCode() . ' ' . $exception->getMessage();

        $mail = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->from)
            ->setTo($this->to)
            ->setContentType('text/html')
            ->setBody($body);

        $this->mailer->send($mail);

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
