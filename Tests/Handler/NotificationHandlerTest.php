<?php

namespace Elao\ErrorNotifierBundle\Tests\Handler;

use Elao\ErrorNotifierBundle\Handler\NotificationHandler;
use Elao\ErrorNotifierBundle\Notifier\NotifierCollection;
use Symfony\Component\Debug\Exception\FlattenException;

class NotificationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Handler\NotificationHandler::__construct
     * @covers \Elao\ErrorNotifierBundle\Handler\NotificationHandler::notify
     */
    public function it_notifies_all_enabled_notifiers()
    {
        $notifier1 = $this
            ->getMockBuilder('Elao\ErrorNotifierBundle\Notifier\NotifierInterface')
            ->getMock()
        ;
        $notifier1->expects($this->once())->method('notify');

        $notifier2 = $this
            ->getMockBuilder('Elao\ErrorNotifierBundle\Notifier\NotifierInterface')
            ->getMock()
        ;
        $notifier2->expects($this->once())->method('notify');

        /** @var \PHPUnit_Framework_MockObject_MockObject|NotifierCollection $notifierCollection */
        $notifierCollection = $this
            ->getMockBuilder('Elao\ErrorNotifierBundle\Notifier\NotifierCollection')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $notifierCollection->expects($this->once())->method('getAllEnabled')->willReturn(array($notifier1, $notifier2));

        $notificationHandler = new NotificationHandler($notifierCollection);
        $notificationHandler->notify(new FlattenException(new \Exception()));
    }
}