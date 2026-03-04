<?php
/**
 * Exception Classes Test Suite
 * 
 * Tests for all DeathByCaptcha API exception classes
 */

use PHPUnit\Framework\TestCase;

class DeathByCaptcha_ExceptionTest extends TestCase
{
    /**
     * Test that base exception can be thrown and caught
     */
    public function testBaseExceptionThrown()
    {
        $this->expectException(DeathByCaptcha_Exception::class);
        throw new DeathByCaptcha_RuntimeException('Test error');
    }

    /**
     * Test RuntimeException
     */
    public function testRuntimeException()
    {
        $exception = new DeathByCaptcha_RuntimeException('Runtime error message');
        $this->assertInstanceOf(DeathByCaptcha_Exception::class, $exception);
        $this->assertEquals('Runtime error message', $exception->getMessage());
    }

    /**
     * Test IOException
     */
    public function testIOException()
    {
        $exception = new DeathByCaptcha_IOException('IO error message');
        $this->assertInstanceOf(DeathByCaptcha_Exception::class, $exception);
        $this->assertEquals('IO error message', $exception->getMessage());
    }

    /**
     * Test ClientException
     */
    public function testClientException()
    {
        $exception = new DeathByCaptcha_ClientException('Client error message');
        $this->assertInstanceOf(DeathByCaptcha_Exception::class, $exception);
        $this->assertEquals('Client error message', $exception->getMessage());
    }

    /**
     * Test AccessDeniedException
     */
    public function testAccessDeniedException()
    {
        $exception = new DeathByCaptcha_AccessDeniedException('Access denied message');
        $this->assertInstanceOf(DeathByCaptcha_ClientException::class, $exception);
        $this->assertInstanceOf(DeathByCaptcha_Exception::class, $exception);
        $this->assertEquals('Access denied message', $exception->getMessage());
    }

    /**
     * Test InvalidCaptchaException
     */
    public function testInvalidCaptchaException()
    {
        $exception = new DeathByCaptcha_InvalidCaptchaException('Invalid captcha message');
        $this->assertInstanceOf(DeathByCaptcha_ClientException::class, $exception);
        $this->assertInstanceOf(DeathByCaptcha_Exception::class, $exception);
        $this->assertEquals('Invalid captcha message', $exception->getMessage());
    }

    /**
     * Test ServerException
     */
    public function testServerException()
    {
        $exception = new DeathByCaptcha_ServerException('Server error message');
        $this->assertInstanceOf(DeathByCaptcha_Exception::class, $exception);
        $this->assertEquals('Server error message', $exception->getMessage());
    }

    /**
     * Test ServiceOverloadException
     */
    public function testServiceOverloadException()
    {
        $exception = new DeathByCaptcha_ServiceOverloadException('Service overload message');
        $this->assertInstanceOf(DeathByCaptcha_ServerException::class, $exception);
        $this->assertInstanceOf(DeathByCaptcha_Exception::class, $exception);
        $this->assertEquals('Service overload message', $exception->getMessage());
    }

    /**
     * Test exception inheritance chain
     */
    public function testExceptionHierarchy()
    {
        // AccessDeniedException should be caught as ClientException
        try {
            throw new DeathByCaptcha_AccessDeniedException('Access denied');
        } catch (DeathByCaptcha_ClientException $e) {
            $this->assertTrue(true);
        }

        // ServiceOverloadException should be caught as ServerException
        try {
            throw new DeathByCaptcha_ServiceOverloadException('Service overload');
        } catch (DeathByCaptcha_ServerException $e) {
            $this->assertTrue(true);
        }

        // All exceptions should be caught as DeathByCaptcha_Exception
        $exceptions = [
            new DeathByCaptcha_RuntimeException('test'),
            new DeathByCaptcha_IOException('test'),
            new DeathByCaptcha_ClientException('test'),
            new DeathByCaptcha_AccessDeniedException('test'),
            new DeathByCaptcha_InvalidCaptchaException('test'),
            new DeathByCaptcha_ServerException('test'),
            new DeathByCaptcha_ServiceOverloadException('test'),
        ];

        foreach ($exceptions as $exception) {
            $this->assertInstanceOf(DeathByCaptcha_Exception::class, $exception);
        }
    }
}
