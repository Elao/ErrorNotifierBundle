<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

class URIDeciderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    private $requestStack;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RequestMatcher
     */
    private $requestMatcher;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->requestStack = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->requestMatcher = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\RequestMatcher')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider::__construct
     */
    public function it_requires_an_array_of_request_matchers()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

         new URIDecider($this->requestStack, array(new \StdClass()));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider::ignoreError
     */
    public function it_does_not_ignore_none_http_exceptions()
    {
        $decider = new URIDecider($this->requestStack, array());

        $this->assertFalse($decider->ignoreError(new \Exception()));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider::ignoreError
     */
    public function it_does_not_ignore_exceptions_when_there_is_no_master_request()
    {
        $this->requestStack->expects($this->once())->method('getMasterRequest')->willReturn(null);

        $decider = new URIDecider($this->requestStack, array());

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider::ignoreError
     */
    public function it_does_not_ignore_exceptions_when_the_uri_does_not_match_a_request_matcher()
    {
        $this->requestStack->expects($this->once())->method('getMasterRequest')->willReturn($this->request);
        $this->requestMatcher->expects($this->once())->method('matches')->with($this->request)->willReturn(false);

        $decider = new URIDecider(
            $this->requestStack,
            array(
                $this->requestMatcher,
            )
        );

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\URIDecider::ignoreError
     */
    public function it_ignores_exceptions_when_the_uri_matches_a_request_matcher()
    {
        $this->requestStack->expects($this->once())->method('getMasterRequest')->willReturn($this->request);
        $this->requestMatcher->expects($this->once())->method('matches')->with($this->request)->willReturn(true);

        $decider = new URIDecider(
            $this->requestStack,
            array(
                $this->requestMatcher,
            )
        );

        $this->assertTrue($decider->ignoreError(new HttpException(400)));
    }
}
