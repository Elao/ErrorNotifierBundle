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

    /**
     * __construct
     * 
     * @param Swift_Mailer    $mailer     mailer
     * @param EngineInterface $templating templating
     * @param type            $from       from
     * @param type            $to         to
     * 
     * @return void
     */
    public function __construct(Swift_Mailer $mailer, EngineInterface $templating, $from, $to)
    {
        $this->mailer = $mailer;

        $this->templating = $templating;

        $this->from = $from;

        $this->to = $to;
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
        $e = FlattenException::create($event->getException(), 500, $event->getRequest()->headers->all());

        $body = $this->templating->render('ElaoErrorNotifierBundle::mail.html.twig', array(
            'exception' => $e,
            'exception_class' => $e->getClass(),
            'request' => $event->getRequest(),
            'response' => $event->getResponse(),
            'status_code' => 500,
            'status_text' => ''
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