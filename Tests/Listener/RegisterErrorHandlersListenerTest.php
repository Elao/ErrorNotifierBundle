<?php

namespace Elao\ErrorNotifierBundle\Tests\Listener;

use Elao\ErrorNotifierBundle\Handler\ExceptionHandlerInterface;
use Elao\ErrorNotifierBundle\Listener\RegisterErrorHandlersListener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RegisterErrorHandlersListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ExceptionHandlerInterface
     */
    private $exceptionHandler;

    /**
     * @var RegisterErrorHandlersListener
     */
    private $registerErrorHandlersListener;

    /**
     * @var boolean
     */
    private $setErrorHandlerCalled;

    /**
     * @var boolean
     */
    private $registerShutdownFunctionCalled;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->exceptionHandler = $this
            ->getMockBuilder('Elao\ErrorNotifierBundle\Handler\ExceptionHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registerErrorHandlersListener = new RegisterErrorHandlersListener($this->exceptionHandler);

        include_once __DIR__ . '/../Fixtures/MockNativeMethods.php';

        set_set_error_handler_method(array($this, 'setErrorHandlerCallback'));
        set_register_shutdown_function_method(array($this, 'registerShutdownFunctionCallback'));
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        include_once __DIR__ . '/../Fixtures/MockNativeMethods.php';

        set_set_error_handler_method(null);
        set_register_shutdown_function_method(null);
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Listener\RegisterErrorHandlersListener::__construct
     * @uses \Elao\ErrorNotifierBundle\Util\MemoryManagement
     * @covers \Elao\ErrorNotifierBundle\Listener\RegisterErrorHandlersListener::onKernelRequest
     * @covers \Elao\ErrorNotifierBundle\Listener\RegisterErrorHandlersListener::registerErrorHandlers
     */
    public function it_registers_the_error_handlers_on_kernel_request()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|GetResponseEvent $event */
        $event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn(new Request())
        ;

        $this->exceptionHandler->expects($this->once())->method('setRequest');

        $this->registerErrorHandlersListener->onKernelRequest($event);

        $this->assertTrue($this->setErrorHandlerCalled);
        $this->assertTrue($this->registerShutdownFunctionCalled);
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Listener\RegisterErrorHandlersListener::__construct
     * @uses \Elao\ErrorNotifierBundle\Util\MemoryManagement
     * @covers \Elao\ErrorNotifierBundle\Listener\RegisterErrorHandlersListener::onConsoleCommand
     * @covers \Elao\ErrorNotifierBundle\Listener\RegisterErrorHandlersListener::registerErrorHandlers
     */
    public function it_registers_the_error_handlers_on_console_command()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ConsoleCommandEvent $event */
        $event = $this
            ->getMockBuilder('Symfony\Component\Console\Event\ConsoleCommandEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event
            ->expects($this->once())
            ->method('getCommand')
            ->willReturn(new Command('command'))
        ;
        $event
            ->expects($this->once())
            ->method('getInput')
            ->willReturn(new ArrayInput(array()))
        ;

        $this->exceptionHandler->expects($this->once())->method('setCommand')->willReturn($this->exceptionHandler);
        $this->exceptionHandler->expects($this->once())->method('setCommandInput');

        $this->registerErrorHandlersListener->onConsoleCommand($event);

        $this->assertTrue($this->setErrorHandlerCalled);
        $this->assertTrue($this->registerShutdownFunctionCalled);
    }

    /**
     * Callable for testing "set_error_handler" has been called
     */
    public function setErrorHandlerCallback()
    {
        $this->setErrorHandlerCalled = true;
    }

    /**
     * Callable for testing "register_shutdown_function" has been called
     */
    public function registerShutdownFunctionCallback()
    {
        $this->registerShutdownFunctionCalled = true;
    }
}