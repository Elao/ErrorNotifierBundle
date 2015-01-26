<?php

namespace Elao\ErrorNotifierBundle\Tests\Assertion;

use Elao\ErrorNotifierBundle\Assertion\Assert;
use Elao\ErrorNotifierBundle\Tests\Exception\AssertException;

class AssertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\Assertion\Assert::ip
     */
    public function it_throws_an_exception_when_given_an_invalid_ip_address()
    {
        $this->setExpectedException(AssertException::CLASS_NAME);

        Assert::ip('1');
    }
}
