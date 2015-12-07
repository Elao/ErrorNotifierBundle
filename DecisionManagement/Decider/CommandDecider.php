<?php

namespace Elao\ErrorNotifierBundle\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\Assertion\Assert;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandDecider implements DeciderInterface, EventSubscriberInterface
{
    /**
     * @var array
     */
    private $ignoredCommands;

    /**
     * @var string
     */
    private $commandName;

    /**
     * Constructor.
     *
     * @param array $ignoredCommands
     */
    public function __construct(array $ignoredCommands)
    {
        Assert::allString($ignoredCommands);

        $this->ignoredCommands = $ignoredCommands;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvents::COMMAND  => 'setCommandName',
        );
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function setCommandName(ConsoleCommandEvent $event)
    {
        $this->commandName = $event->getCommand()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreError(\Exception $exception)
    {
        if (null === $this->commandName) {
            return false;
        }

        if (in_array($this->commandName, $this->ignoredCommands)) {
            return true;
        }

        return false;
    }
}