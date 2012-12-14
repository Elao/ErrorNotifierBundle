<?php

namespace Elao\ErrorNotifierBundle\Listener;

use \Swift_Mailer;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

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
     *
     * @return void
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
     *
     * @return void
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
        }
    }
    
    public function onKernelRequest(GetResponseEvent $event)
    {
        
        if($this->reportErrors || $this->reportWarnings)
        {
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
     * 
     * @param integer $level
     * @param string $message
     * @param string $file
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
    
    public function getErrorString($errorNo)
    {
        $errorStrings = array(
            E_ERROR => 'E_ERROR',
            E_PARSE => 'E_PARSE', 
            E_CORE_ERROR => 'E_CORE_ERROR', 
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING', 
            E_COMPILE_WARNING => 'E_COMPILE_WARNING', 
            E_STRICT => 'E_STRICT',
            E_NOTICE => 'E_STRICT', 
            E_USER_WARNING => 'E_STRICT', 
            E_USER_NOTICE => 'E_STRICT', 
            E_STRICT => 'E_STRICT', 
            E_DEPRECATED => 'E_STRICT', 
            E_USER_DEPRECATED => 'E_STRICT'
        );
        
        return array_key_exists($errorNo, $errorStrings) ? $errorStrings[$errorNo] : 'UNKNOWN';
        
    }
    
    /**
     * 
     * @param ErrorException $exception
     * @param Request $request
     * @param array $context
     */
    public function createMailAndSend($exception, $request, $context = null)
    {
        
        $body = $this->templating->render('ElaoErrorNotifierBundle::mail.html.twig', array(
            'exception'       => $exception,
            'exception_class' => get_class($exception),
            'request'         => $request,
            'status_code'     => $exception->getCode(),
            // This is probably too dangerous as it could contain recursive objects
            //'context'         => $context
        ));

        $mail = \Swift_Message::newInstance()
            ->setSubject('[' . $request->headers->get('host') . '] Error')
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
