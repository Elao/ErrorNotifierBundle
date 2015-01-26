<?php

namespace Elao\ErrorNotifierBundle\Tests\DecisionManagement\Decider;

use Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClientIPDeciderTest extends \PHPUnit_Framework_TestCase
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
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Assertion\Assert
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider::__construct
     */
    public function it_requires_an_array_of_ip_addresses()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

        new ClientIPDecider($this->requestStack, array(0));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider::ignoreError
     */
    public function it_does_not_ignore_none_http_exceptions()
    {
        $decider = new ClientIPDecider($this->requestStack, array());

        $this->assertFalse($decider->ignoreError(new \Exception()));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider::ignoreError
     */
    public function it_does_not_ignore_exceptions_when_there_is_no_master_request()
    {
        $this->requestStack->expects($this->once())->method('getMasterRequest')->willReturn(null);

        $decider = new ClientIPDecider($this->requestStack, array());

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Assertion\Assert
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider::ignoreError
     */
    public function it_does_not_ignore_exceptions_when_the_client_ip_is_not_in_the_ignored_ips_list()
    {
        $this->requestStack->expects($this->once())->method('getMasterRequest')->willReturn($this->request);
        $this->request->expects($this->once())->method('getClientIp')->willReturn('33.33.33.1');

        $decider = new ClientIPDecider($this->requestStack, array());

        $this->assertFalse($decider->ignoreError(new HttpException(400)));
    }

    /**
     * @test
     * @uses \Elao\ErrorNotifierBundle\Assertion\Assert
     * @uses \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider::__construct
     * @covers \Elao\ErrorNotifierBundle\DecisionManagement\Decider\ClientIPDecider::ignoreError
     */
    public function it_ignores_exceptions_when_the_client_ip_is_in_the_ignored_ips_list()
    {
        $this->requestStack->expects($this->once())->method('getMasterRequest')->willReturn($this->request);
        $this->request->expects($this->once())->method('getClientIp')->willReturn('33.33.33.1');

        $decider = new ClientIPDecider($this->requestStack, array('33.33.33.1'));

        $this->assertTrue($decider->ignoreError(new HttpException(400)));
    }
}
