<?php

namespace Elao\ErrorNotifierBundle\Tests\Handler;

use Elao\ErrorNotifierBundle\DecisionManagement\Manager\NotificationDecisionManagerInterface;
use Elao\ErrorNotifierBundle\Handler\ExceptionHandler;
use Elao\ErrorNotifierBundle\Handler\ExceptionHandlerInterface;
use Elao\ErrorNotifierBundle\Handler\NotificationHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Request;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|NotificationDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|NotificationHandlerInterface
     */
    private $notificationHandler;

    /**
     * @var ExceptionHandlerInterface
     */
    private $exceptionHandler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        include_once __DIR__ . '/../Fixtures/MockNativeMethods.php';

        set_native_error_get_last(null);

        $this->decisionManager = $this
            ->getMockBuilder('Elao\ErrorNotifierBundle\DecisionManagement\Manager\NotificationDecisionManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->notificationHandler = $this
            ->getMockBuilder('Elao\ErrorNotifierBundle\Handler\NotificationHandlerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->exceptionHandler = new ExceptionHandler($this->decisionManager, $this->notificationHandler);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        include_once __DIR__ . '/../Fixtures/MockNativeMethods.php';

        set_native_error_get_last(null);
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::__construct
     * @covers \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::setRequest
     */
    public function it_has_a_request()
    {
        $reflectionProperty = new \ReflectionProperty(
            'Elao\ErrorNotifierBundle\Handler\ExceptionHandler',
            'request'
        );
        $reflectionProperty->setAccessible(true);

        $request = new Request();

        $this->exceptionHandler->setRequest($request);
        $this->assertEquals($request, $reflectionProperty->getValue($this->exceptionHandler));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::__construct
     * @covers \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::setCommand
     */
    public function it_has_a_command()
    {
        $reflectionProperty = new \ReflectionProperty(
            'Elao\ErrorNotifierBundle\Handler\ExceptionHandler',
            'command'
        );
        $reflectionProperty->setAccessible(true);

        $command = new Command('command');

        $this->exceptionHandler->setCommand($command);
        $this->assertEquals($command, $reflectionProperty->getValue($this->exceptionHandler));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::__construct
     * @covers \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::setCommandInput
     */
    public function it_has_a_command_input()
    {
        $reflectionProperty = new \ReflectionProperty(
            'Elao\ErrorNotifierBundle\Handler\ExceptionHandler',
            'commandInput'
        );
        $reflectionProperty->setAccessible(true);

        $input = new ArrayInput(array());

        $this->exceptionHandler->setCommandInput($input);
        $this->assertEquals($input, $reflectionProperty->getValue($this->exceptionHandler));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::__construct
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::handleException
     * @covers \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::handlePhpError
     */
    public function it_handles_php_errors()
    {
        $this->decisionManager->expects($this->once())->method('notifyError')->willReturn(true);
        $this->notificationHandler->expects($this->once())->method('notify');

        $this->exceptionHandler->handlePhpError(E_NOTICE, 'message', 'file', 1, array());
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::__construct
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::handleException
     * @uses \Elao\ErrorNotifierBundle\Util\MemoryManagement
     * @covers \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::handlePhpFatalErrorAndWarnings
     */
    public function it_handles_php_fatal_errors_and_warnings()
    {
        set_native_error_get_last(array(
            'file'      => 'file',
            'line'      => 1,
            'message'   => 'message',
            'type'      => E_NOTICE,
        ));

        $this->decisionManager->expects($this->once())->method('notifyError')->willReturn(true);
        $this->notificationHandler->expects($this->once())->method('notify');

        $this->exceptionHandler->handlePhpFatalErrorAndWarnings();
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::__construct
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::handleException
     * @uses \Elao\ErrorNotifierBundle\Util\MemoryManagement
     * @covers \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::handlePhpFatalErrorAndWarnings
     */
    public function it_only_handles_php_fatal_errors_and_warnings_when_an_error_is_found()
    {
        $this->decisionManager->expects($this->never())->method('notifyError');

        $this->exceptionHandler->handlePhpFatalErrorAndWarnings();
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::__construct
     * @covers \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::handleException
     */
    public function it_handles_exceptions()
    {
        $this->decisionManager->expects($this->once())->method('notifyError')->willReturn(true);
        $this->notificationHandler->expects($this->once())->method('notify');

        $this->exceptionHandler->handleException(new \Exception());
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::__construct
     * @covers \Elao\ErrorNotifierBundle\Handler\ExceptionHandler::handleException
     */
    public function it_does_not_handle_exceptions_when_decision_manager_says_not_to_notify_error()
    {
        $this->decisionManager->expects($this->once())->method('notifyError')->willReturn(false);
        $this->notificationHandler->expects($this->never())->method('notify');

        $this->exceptionHandler->handleException(new \Exception());
    }
}