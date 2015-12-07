<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\ExceptionClassDecider;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionClassDeciderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ExceptionClassDecider::__construct
     */
    public function it_requires_an_array_of_strings()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

        new ExceptionClassDecider(array(0));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ExceptionClassDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ExceptionClassDecider::ignoreError
     */
    public function it_does_not_ignore_exceptions_when_the_class_is_not_in_the_ignored_classes_list()
    {
        $decider = new ExceptionClassDecider(array());

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ExceptionClassDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ExceptionClassDecider::ignoreError
     */
    public function it_ignores_exceptions_when_the_class_is_in_the_ignored_classes_list()
    {
        $decider = new ExceptionClassDecider(array(
            'Symfony\Component\HttpKernel\Exception\HttpException',
        ));

        $this->assertTrue($decider->ignoreError(new HttpException(400)));
    }

}
