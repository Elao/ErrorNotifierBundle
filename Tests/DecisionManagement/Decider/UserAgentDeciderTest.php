<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserAgentDeciderTest extends \PHPUnit_Framework_TestCase
{
    const USER_AGENT    = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    private $requestStack;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HeaderBag
     */
    private $headerBag;

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
        $this->headerBag = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\HeaderBag')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::ignoreError
     */
    public function it_requires_an_array_of_strings()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

        new UserAgentDecider($this->requestStack, array(0));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::ignoreError
     */
    public function it_does_not_ignore_none_http_exceptions()
    {
        $decider = new UserAgentDecider($this->requestStack, array());

        $this->assertFalse($decider->ignoreError(new \Exception()));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::ignoreError
     */
    public function it_does_not_ignore_exceptions_when_there_is_no_master_request()
    {
        $this->requestStack->expects($this->once())->method('getMasterRequest')->willReturn(null);

        $decider = new UserAgentDecider($this->requestStack, array());

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::ignoreError
     */
    public function it_does_not_ignore_exceptions_when_the_user_agent_is_not_in_the_ignored_user_agents_list()
    {
        $request = new \StdClass();
        $request->headers = $this->headerBag;
        $this->requestStack->expects($this->once())->method('getMasterRequest')->willReturn($request);
        $this->headerBag->expects($this->once())->method('get')->with('User-Agent')->willReturn(self::USER_AGENT);

        $decider = new UserAgentDecider($this->requestStack, array('(None Matching Pattern)'));

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\UserAgentDecider::ignoreError
     */
    public function it_ignores_exceptions_when_the_user_agent_is_in_the_ignored_user_agents_list()
    {
        $request = new \StdClass();
        $request->headers = $this->headerBag;
        $this->requestStack->expects($this->once())->method('getMasterRequest')->willReturn($request);
        $this->headerBag->expects($this->once())->method('get')->with('User-Agent')->willReturn(self::USER_AGENT);

        $decider = new UserAgentDecider($this->requestStack, array('(Googlebot|bingbot)'));

        $this->assertTrue($decider->ignoreError(new HttpException(400)));
    }
}
