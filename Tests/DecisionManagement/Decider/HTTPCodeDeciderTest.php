<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\HTTPCodeDecider;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HTTPCodeDeciderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\HTTPCodeDecider::__construct
     */
    public function it_requires_an_array_of_integers()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

        new HTTPCodeDecider(array('fail'));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\HTTPCodeDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\HTTPCodeDecider::ignoreError
     */
    public function it_does_not_ignore_none_http_exceptions()
    {
        $decider = new HTTPCodeDecider(array(400));

        $this->assertFalse($decider->ignoreError(new \Exception()));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\HTTPCodeDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\HTTPCodeDecider::ignoreError
     */
    public function it_does_not_ignore_exceptions_when_the_status_code_is_in_the_notifiable_codes_list()
    {
        $decider = new HTTPCodeDecider(array(400));

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\HTTPCodeDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\HTTPCodeDecider::ignoreError
     */
    public function it_ignores_exceptions_when_the_status_code_is_not_in_the_notifiable_codes_list()
    {
        $decider = new HTTPCodeDecider(array(400));

        $this->assertTrue($decider->ignoreError(new HttpException(401)));
    }
}
