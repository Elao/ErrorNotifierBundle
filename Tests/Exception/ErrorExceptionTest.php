<?php

namespace Elao\ErrorNotifierBundle\Tests\Exception;

use Elao\ErrorNotifierBundle\Exception\ErrorException;

class ErrorExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \Elao\ErrorNotifierBundle\Exception\ErrorException::__construct
     * @covers \Elao\ErrorNotifierBundle\Exception\ErrorException::getErrorString
     */
    public function it_creates_a_exception()
    {
        $exception = new ErrorException(E_NOTICE, 'message', 'filename', 5, 400);

        $this->assertEquals('Notice: message in filename line 5', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals(E_NOTICE, $exception->getSeverity());
        $this->assertEquals('filename', $exception->getFile());
        $this->assertEquals(5, $exception->getLine());
    }
}
