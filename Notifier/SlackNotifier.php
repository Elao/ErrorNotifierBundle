<?php

namespace Elao\ErrorNotifierBundle\Notifier;

use CL\Slack\Payload\ChatPostMessagePayload;
use CL\Slack\Transport\ApiClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

class SlackNotifier implements NotifierInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param EngineInterface $templating
     * @param $channel
     * @param ApiClient $apiClient
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EngineInterface $templating,
        $channel,
        ApiClient $apiClient = null
    ) {
        $this->tokenStorage     = $tokenStorage;
        $this->apiClient        = $apiClient;
        $this->templating       = $templating;

        if (strpos($channel, '#') !== 0) {
            $channel = sprintf('#%s', $channel);
        }

        $this->channel          = $channel;
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
        $this->assertClSlackInstalled();

        $text = $this->templating->render('ElaoErrorNotifierBundle:Slack:text.txt.twig', array(
            'exception'       => $exception,
            'request'         => $request,
            'status_code'     => $exception->getCode(),
            'context'         => $context,
            'command'         => $command,
            'command_input'   => $commandInput
        ));

        $payload = new ChatPostMessagePayload();
        $payload->setChannel($this->channel);
        $payload->setText($text);
        $payload->setUsername(sprintf('%s (bot)', $this->getUsername($exception)));
        $payload->setIconEmoji('skull');

        $this->apiClient->send($payload);
    }

    /**
     * Asserts CL Slack Bundle is installed and enabled to use this notifier
     *
     * @return null
     * @throws \Exception
     */
    private function assertClSlackInstalled()
    {
        if (null === $this->apiClient) {
            throw new \Exception(
                'Please make sure that you have installed and enabled the CLSlackBundle (cleentfaar/slack-bundle)'
            );
        }
    }

    /**
     * @param FlattenException $exception
     * @return string
     */
    private function getUsername(FlattenException $exception)
    {
        if (null !== $token = $this->tokenStorage->getToken()) {
            return $token->getUsername();
        }

        return $exception->getStatusCode();
    }
}
