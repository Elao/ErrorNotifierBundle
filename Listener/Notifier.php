<?php

namespace Elao\ErrorNotifierBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use \Swift_Mailer;

use Symfony\Component\Templating\EngineInterface;

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
     * @param string          $from       send mail from
     * @param string          $to         send mail to
     * @param boolean         $handle404  handle 404 error ?
     * 
     * @return void
     */
    public function __construct(Swift_Mailer $mailer, EngineInterface $templating, $from, $to, $handle404 = false)
    {
        $this->mailer = $mailer;

        $this->templating = $templating;

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
        $exception = $event->getException();

        // Http Error
        if ($exception instanceof HttpException) {

            if ($exception->getStatusCode() == 404) {
                // we handle 404 Error ?
                if (false === $this->handle404) {
                    return;
                }
            } else if ($exception->getStatusCode() != 500) {
                // always catch 500
                return;
            }
        }

        $body = $this->templating->render('ElaoErrorNotifierBundle::mail.html.twig', array(
            'exception' => $e,
            'exception_class' => getclass($exception),
            'request' => $event->getRequest(),
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