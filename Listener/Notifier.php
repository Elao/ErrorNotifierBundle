<?php

namespace Elao\ErrorNotifierBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

use \Swift_Mailer;

class Notifier
{
	private $mailer;

	private $templating;

	private $from;

	private $to;

	public function __construct($mailer, $templating, $from, $to)
	{
		$this->mailer = $mailer;

		$this->templating = $templating;

		$this->from = $from;

		$this->to = $to;
	}

	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		$body = $this-->templating->render('ElaoErrorNotifierBundle::mail.html.twig', array(
			'exception' => $event->getException(),
			'request' => $event->getRequest(),
			'response' => $event->getResponse(),
			'status_code' => 500,
			'status_text' => ''
		));

		$mail = \Swift_Message::newInstance()
                ->setSubject('['.$event->getRequest()->headers->get('host').'] Error')
                ->setFrom($this->from)
                ->setTo($this->to)
                ->setContentType('text/html')
                ->setBody($$body);

		$this->mailer->send($mail);
	}
}