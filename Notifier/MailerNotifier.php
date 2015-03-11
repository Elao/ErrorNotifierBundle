<?php

namespace Elao\ErrorNotifierBundle\Notifier;

use \Swift_Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\Templating\EngineInterface;

class MailerNotifier implements NotifierInterface
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string
     */
    protected $mailTo;

    /**
     * @var string
     */
    protected $mailFrom;

    /**
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $templating
     * @param $mailTo
     * @param $mailFrom
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, $mailTo, $mailFrom)
    {
        $this->mailer           = $mailer;
        $this->templating       = $templating;
        $this->mailTo           = $mailTo;
        $this->mailFrom         = $mailFrom;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(
        FlattenException $exception,
        Request $request = null,
        $context = null,
        Command $command = null,
        InputInterface $commandInput = null
    ) {
        $body = $this->templating->render('ElaoErrorNotifierBundle:Mailer:mail.html.twig', array(
            'exception'       => $exception,
            'request'         => $request,
            'status_code'     => $exception->getCode(),
            'context'         => $context,
            'command'         => $command,
            'command_input'   => $commandInput
        ));

        $subject = $this->getEmailSubjectLine($exception, $request, $command);

        $mail = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->mailFrom)
            ->setTo($this->mailTo)
            ->setContentType('text/html')
            ->setBody($body);

        $this->mailer->send($mail);
    }

    /**
     * Generate email subject line from exception and request or command
     *
     * @param FlattenException $exception
     * @param Request $request
     * @param Command $command
     * @return string
     */
    protected function getEmailSubjectLine(
        FlattenException $exception,
        Request $request = null,
        Command $command = null
    ) {
        if ($request || $command) {
            $subject = sprintf(
                '[%s] Error %s: %s',
                $request ? $request->headers->get('host') : $command->getName(),
                $exception->getStatusCode(),
                $exception->getMessage()
            );
        } else {
            $subject = sprintf(
                'Error %s: %s',
                $exception->getStatusCode(),
                $exception->getMessage()
            );
        }

        return function_exists('mb_substr') ? mb_substr($subject, 0, 255) : substr($subject, 0, 255);
    }
}
