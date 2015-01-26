<?php

namespace Elao\ErrorNotifierBundle\Tests\Listener;

use Elao\ErrorNotifierBundle\Handler\ExceptionHandlerInterface;
use Elao\ErrorNotifierBundle\Listener\ExceptionListener;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ExceptionHandlerInterface
     */
    private $exceptionHandler;

    /**
     * @var ExceptionListener
     */
    private $exceptionListener;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->exceptionHandler = $this
            ->getMockBuilder('Elao\ErrorNotifierBundle\Handler\ExceptionHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $this->exceptionListener = new ExceptionListener($this->exceptionHandler);
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Listener\ExceptionListener::__construct
     * @covers \Elao\ErrorNotifierBundle\Listener\ExceptionListener::onKernelException
     */
    public function it_does_not_notify_exceptions_when_not_using_the_master_request()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|GetResponseForExceptionEvent $event */
        $event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event
            ->expects($this->once())
            ->method('getRequestType')
            ->willReturn(HttpKernelInterface::SUB_REQUEST)
        ;

        $this->exceptionHandler->expects($this->never())->method('handleException');

        $this->exceptionListener->onKernelException($event);
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Listener\ExceptionListener::__construct
     * @covers \Elao\ErrorNotifierBundle\Listener\ExceptionListener::onKernelException
     */
    public function it_notifies_exceptions_on_kernel_request()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|GetResponseForExceptionEvent $event */
        $event = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event
            ->expects($this->once())
            ->method('getRequestType')
            ->willReturn(HttpKernelInterface::MASTER_REQUEST)
        ;
        $event
            ->expects($this->once())
            ->method('getException')
            ->willReturn(new \Exception())
        ;

        $this->exceptionHandler->expects($this->once())->method('handleException');

        $this->exceptionListener->onKernelException($event);
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Listener\ExceptionListener::__construct
     * @covers \Elao\ErrorNotifierBundle\Listener\ExceptionListener::onConsoleException
     */
    public function it_notifies_exceptions_on_console_command()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ConsoleExceptionEvent $event */
        $event = $this
            ->getMockBuilder('Symfony\Component\Console\Event\ConsoleExceptionEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event
            ->expects($this->once())
            ->method('getException')
            ->willReturn(new \Exception())
        ;

        $this->exceptionHandler->expects($this->once())->method('handleException');

        $this->exceptionListener->onConsoleException($event);
    }
}