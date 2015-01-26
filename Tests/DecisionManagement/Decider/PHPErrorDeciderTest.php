<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider;
use Elao\ErrorNotifierBundle\Exception\ErrorException;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;

class PHPErrorDeciderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        include_once __DIR__ . '/../../Fixtures/MockNativeMethods.php';

        set_native_error_reporting(E_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        include_once __DIR__ . '/../../Fixtures/MockNativeMethods.php';

        set_native_error_reporting(null);
    }

    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::__construct
     */
    public function it_requires_an_array_of_php_error_codes()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

        new PHPErrorDecider(array('fail'), true, true, true);
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::__construct
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::ignoreError
     */
    public function it_does_not_ignore_none_error_exceptions()
    {
        $decider = new PHPErrorDecider(array(), true, true, true);

        $this->assertFalse($decider->ignoreError(new \Exception()));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::__construct
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::ignoreError
     */
    public function it_ignores_errors_when_error_reporting_is_0_and_handle_silent_errors_is_false()
    {
        $decider = new PHPErrorDecider(array(), true, true, false);

        set_native_error_reporting(0);

        $this->assertTrue($decider->ignoreError(new ErrorException()));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::__construct
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::ignoreError
     */
    public function it_ignores_errors_when_the_code_is_in_the_ignored_php_errors_list()
    {
        $decider = new PHPErrorDecider(array(E_NOTICE), true, true, true);

        $this->assertTrue($decider->ignoreError(new ErrorException(E_NOTICE)));
        $this->assertFalse($decider->ignoreError(new ErrorException(E_ERROR)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::__construct
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::ignoreError
     */
    public function it_ignores_errors_when_the_code_is_a_warning_and_handle_php_warnings_is_false()
    {
        $decider = new PHPErrorDecider(array(), true, false, true);

        $this->assertTrue($decider->ignoreError(new ErrorException(E_NOTICE)));
        $this->assertFalse($decider->ignoreError(new ErrorException(E_ERROR)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::__construct
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::ignoreError
     */
    public function it_does_not_ignore_errors_when_the_code_is_a_warning_and_handle_php_warnings_is_true()
    {
        $decider = new PHPErrorDecider(array(), true, true, true);

        $this->assertFalse($decider->ignoreError(new ErrorException(E_NOTICE)));
        $this->assertFalse($decider->ignoreError(new ErrorException(E_ERROR)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::__construct
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::ignoreError
     */
    public function it_ignores_errors_when_the_code_is_an_error_and_handle_php_errors_is_false()
    {
        $decider = new PHPErrorDecider(array(), false, true, true);

        $this->assertFalse($decider->ignoreError(new ErrorException(E_NOTICE)));
        $this->assertTrue($decider->ignoreError(new ErrorException(E_ERROR)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::__construct
     * @uses \Elao\ErrorNotifierBundle\Exception\ErrorException
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\PHPErrorDecider::ignoreError
     */
    public function it_does_not_ignore_errors_when_the_code_is_an_error_and_handle_php_errors_is_true()
    {
        $decider = new PHPErrorDecider(array(), true, true, true);

        $this->assertFalse($decider->ignoreError(new ErrorException(E_NOTICE)));
        $this->assertFalse($decider->ignoreError(new ErrorException(E_ERROR)));
    }
}
