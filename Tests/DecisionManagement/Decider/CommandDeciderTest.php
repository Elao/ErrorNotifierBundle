<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CommandDeciderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Command
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConsoleCommandEvent
     */
    private $event;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->command = $this
            ->getMockBuilder('Symfony\Component\Console\Command\Command')
            ->disableOriginalConstructor()
            ->getMock();
        $this->event = $this
            ->getMockBuilder('Symfony\Component\Console\Event\ConsoleCommandEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Assertion\Assert
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider::__construct
     */
    public function it_requires_an_array_of_strings()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

        new CommandDecider(array(0));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider::ignoreError
     */
    public function it_does_not_ignore_exceptions_when_not_created_by_a_command()
    {
        $decider = new CommandDecider(array());

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider::ignoreError
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider::setCommandName
     */
    public function it_does_not_ignore_exceptions_when_the_command_name_is_not_in_the_ignored_commands_list()
    {
        $this->event->expects($this->once())->method('getCommand')->willReturn($this->command);
        $this->command->expects($this->once())->method('getName')->willReturn('ignored:command');

        $decider = new CommandDecider(array('not-ignored:command'));
        $decider->setCommandName($this->event);

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider::ignoreError
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\CommandDecider::setCommandName
     */
    public function it_ignores_exceptions_when_the_ccommand_name_is_in_the_ignored_commands_list()
    {
        $this->event->expects($this->once())->method('getCommand')->willReturn($this->command);
        $this->command->expects($this->once())->method('getName')->willReturn('ignored:command');

        $decider = new CommandDecider(array('ignored:command'));
        $decider->setCommandName($this->event);

        $this->assertTrue($decider->ignoreError(new HttpException(400)));
    }

}
