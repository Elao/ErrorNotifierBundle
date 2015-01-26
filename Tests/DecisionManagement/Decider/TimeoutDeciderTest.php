<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;

class TimeoutDeciderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->cacheDirectory = __DIR__ . '/../../CacheDirectory';

        include_once __DIR__ . '/../../Fixtures/MockNativeMethods.php';
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        include_once __DIR__ . '/../../Fixtures/MockNativeMethods.php';

        set_native_is_dir_result(null);
        set_native_is_file_result(null);
        set_native_file_get_contents_result(null);
        set_override_native_file_put_contents(false);
        set_native_time(null);
    }

    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::__construct
     */
    public function it_requires_false_or_a_positive_integer()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

        new TimeoutDecider('fail', $this->cacheDirectory);
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::ignoreError
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::isWithinRepeatTimeout
     */
    public function it_does_not_ignore_exceptions_when_no_repeat_timeout_is_set()
    {
        $decider = new TimeoutDecider(false, $this->cacheDirectory);

        $this->assertFalse($decider->ignoreError(new \Exception()));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::ignoreError
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::isWithinRepeatTimeout
     */
    public function if_does_not_ignore_exceptions_when_there_is_no_previous_timeout_set()
    {
        set_native_is_dir_result(true);
        set_native_is_file_result(false);
        set_override_native_file_put_contents(true);

        $decider = new TimeoutDecider(100, $this->cacheDirectory);

        $this->assertFalse($decider->ignoreError(new \Exception()));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::ignoreError
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::isWithinRepeatTimeout
     */
    public function if_does_not_ignore_exceptions_when_outside_of_the_repeat_timeout()
    {
        set_native_is_dir_result(true);
        set_native_is_file_result(true);
        // 2015-12-01 00:00:00
        set_native_file_get_contents_result(1448946000);
        set_override_native_file_put_contents(true);

        $decider = new TimeoutDecider(100, $this->cacheDirectory);

        // 2015-12-01 00:00:00 + 300 seconds
        set_native_time(1448946300);
        $this->assertFalse($decider->ignoreError(new \Exception()));
    }

    /**
     * @te-st
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::ignoreError
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\TimeoutDecider::isWithinRepeatTimeout
     */
    public function if_ignores_exceptions_when_within_the_repeat_timeout()
    {
        set_native_is_dir_result(true);
        set_native_is_file_result(true);
        // 2015-12-01 00:00:00
        set_native_file_get_contents_result(1448946000);
        set_override_native_file_put_contents(true);

        $decider = new TimeoutDecider(100, $this->cacheDirectory);

        // 2015-12-01 00:00:00 + 50 seconds
        set_native_time(1448946050);
        $this->assertTrue($decider->ignoreError(new \Exception()));
    }
}
