<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Manager;

use Elao\ErrorNotifierBundle\DecisionManagement\Manager\NotificationDecisionManager;
use Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider\PresetResultDecider;

class NotificationDecisionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Manager\NotificationDecisionManager::addDecider
     */
    public function it_adds_unique_deciders()
    {
        $manager = new NotificationDecisionManager();

        $reflectionDeciders = new \ReflectionProperty(
            'Elao\ErrorNotifierBundle\DecisionManagement\Manager\NotificationDecisionManager',
            'deciders'
        );
        $reflectionDeciders->setAccessible(true);

        $this->assertCount(0, $reflectionDeciders->getValue($manager));

        $manager->addDecider(new PresetResultDecider(true));

        $this->assertCount(1, $reflectionDeciders->getValue($manager));

        $manager->addDecider(new PresetResultDecider(true));

        $this->assertCount(1, $reflectionDeciders->getValue($manager));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Manager\NotificationDecisionManager::addDecider
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Manager\NotificationDecisionManager::notifyError
     */
    public function it_decides_whether_to_notify_an_error_or_not()
    {
        $manager = new NotificationDecisionManager();
        $decider = new PresetResultDecider(false);
        $exception = new \Exception();

        /** notify error true by default */
        $this->assertTrue($manager->notifyError($exception));

        $manager->addDecider($decider);

        /** notify error as no deciders returned to ignore */
        $this->assertTrue($manager->notifyError($exception));

        $decider->setResult(true);

        /** do not notify error as deciders returned to ignore */
        $this->assertFalse($manager->notifyError($exception));
    }
}
