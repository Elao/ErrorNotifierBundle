<?php

namespace Elao\ErrorNotifierBundle\Listener;

use \Swift_Mailer;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

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

    /**
     * __construct
     *
     * @param Swift_Mailer    $mailer     mailer
     * @param EngineInterface $templating templating
     * @param LoggerInterface $logger
     * @param string          $from       send mail from
     * @param string          $to         send mail to
     * @param boolean         $handle404  handle 404 error ?
     *
     * @return void
     */
    public function __construct(Swift_Mailer $mailer, EngineInterface $templating, $from, $to, $handle404 = false, DebugLoggerInterface $logger = null) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->logger = $logger;
        
        $this->from = $from;
        $this->to = $to;
        $this->handle404 = $handle404;
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
                $logger = $this->logger instanceof DebugLoggerInterface ? $this->logger : null;

                $body = $this->templating->render('ElaoErrorNotifierBundle::mail.html.twig', array(
                    'exception'       => $exception,
                    'exception_class' => get_class($exception),
                    'request'         => $event->getRequest(),
                    'logger'          => $logger,
                    'status_code'     => $exception->getStatusCode()
                ));

                $mail = \Swift_Message::newInstance()
                    ->setSubject('[' . $event->getRequest()->headers->get('host') . '] Error')
                    ->setFrom($this->from)
                    ->setTo($this->to)
                    ->setContentType('text/html')
                    ->setBody($body);

                $this->mailer->send($mail);
            }
        }
    }
}