<?php

namespace Elao\ErrorNotifierBundle\Tests\Notifier;

use Elao\ErrorNotifierBundle\Notifier\NotifierCollection;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;

class NotifierCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::__construct
     */
    public function it_requires_an_array_of_strings()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

        new NotifierCollection(array(1));
    }
    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::__construct
     * @covers \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::addNotifier
     * @covers \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::isEnabled
     */
    public function it_adds_to_a_collection_of_notifiers()
    {
        $reflectionNotifiers = new \ReflectionProperty(
            'Elao\ErrorNotifierBundle\Notifier\NotifierCollection',
            'notifiers'
        );
        $reflectionNotifiers->setAccessible(true);

        $notifierCollection = new NotifierCollection(array());

        // no notifiers by default
        $this->assertCount(0, array_merge(
            $reflectionNotifiers->getValue($notifierCollection)['enabled'],
            $reflectionNotifiers->getValue($notifierCollection)['disabled']
        ));

        $notifierCollection->addNotifier(new TestNotifier(), 'alias_1');

        // 1 notifier with alias "alias_1"
        $this->assertCount(1, array_merge(
            $reflectionNotifiers->getValue($notifierCollection)['enabled'],
            $reflectionNotifiers->getValue($notifierCollection)['disabled']
        ));

        $notifierCollection->addNotifier(new TestNotifier(), 'alias_2');

        // 2 notifiers
        $this->assertCount(2, array_merge(
            $reflectionNotifiers->getValue($notifierCollection)['enabled'],
            $reflectionNotifiers->getValue($notifierCollection)['disabled']
        ));

        $notifierCollection->addNotifier(new TestNotifier(), 'alias_2');

        // 2 notifiers as second "alias_2" overwrote original
        $this->assertCount(2, array_merge(
            $reflectionNotifiers->getValue($notifierCollection)['enabled'],
            $reflectionNotifiers->getValue($notifierCollection)['disabled']
        ));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::__construct
     * @uses \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::addNotifier
     * @uses \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::isEnabled
     * @covers \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::getAllEnabled
     */
    public function it_returns_all_enabled_notifiers()
    {
        $notifierCollection = new NotifierCollection(array('alias_enabled'));

        $enabledNotifier = new TestNotifier();
        $notifierCollection->addNotifier($enabledNotifier, 'alias_enabled');

        $disabledNotifier = new TestNotifier();
        $notifierCollection->addNotifier($disabledNotifier, 'alias_disabled');

        $this->assertEquals(array($enabledNotifier), $notifierCollection->getAllEnabled());
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::__construct
     * @uses \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::addNotifier
     * @uses \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::isEnabled
     * @covers \Elao\ErrorNotifierBundle\Notifier\NotifierCollection::getAllDisabled
     */
    public function it_returns_all_disabled_notifiers()
    {
        $notifierCollection = new NotifierCollection(array('alias_enabled'));

        $enabledNotifier = new TestNotifier();
        $notifierCollection->addNotifier($enabledNotifier, 'alias_enabled');

        $disabledNotifier = new TestNotifier();
        $notifierCollection->addNotifier($disabledNotifier, 'alias_disabled');

        $this->assertEquals(array($disabledNotifier), $notifierCollection->getAllDisabled());
    }
}