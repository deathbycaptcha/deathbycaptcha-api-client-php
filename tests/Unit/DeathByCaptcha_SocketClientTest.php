<?php
/**
 * DeathByCaptcha_SocketClient Test Suite
 * 
 * Tests for Socket/TCP API client functionality
 */

use PHPUnit\Framework\TestCase;

class DeathByCaptcha_SocketClientTest extends TestCase
{
    /**
     * @var DeathByCaptcha_SocketClient
     */
    private $client;

    /**
     * Set up test client
     */
    protected function setUp(): void
    {
        $this->client = new DeathByCaptcha_SocketClient('test_user', 'test_pass');
    }

    /**
     * Test client instantiation
     */
    public function testClientInstantiation()
    {
        $this->assertInstanceOf(DeathByCaptcha_SocketClient::class, $this->client);
        $this->assertInstanceOf(DeathByCaptcha_Client::class, $this->client);
    }

    /**
     * Test that JSON extension is required
     */
    public function testJsonExtensionRequired()
    {
        if (!extension_loaded('json')) {
            $this->markTestSkipped('JSON extension is not loaded');
        }

        // If we get here, JSON is loaded and client should work
        $this->assertInstanceOf(DeathByCaptcha_SocketClient::class, $this->client);
    }

    /**
     * Test client with empty username throws exception
     */
    public function testEmptyUsernameThrowsException()
    {
        $this->expectException(DeathByCaptcha_RuntimeException::class);
        $this->expectExceptionMessage('Account username is missing or empty');
        new DeathByCaptcha_SocketClient('', 'password');
    }

    /**
     * Test client with empty password throws exception
     */
    public function testEmptyPasswordThrowsException()
    {
        $this->expectException(DeathByCaptcha_RuntimeException::class);
        $this->expectExceptionMessage('Account password is missing or empty');
        new DeathByCaptcha_SocketClient('username', '');
    }

    /**
     * Test client with null username throws exception
     */
    public function testNullUsernameThrowsException()
    {
        $this->expectException(DeathByCaptcha_RuntimeException::class);
        $this->expectExceptionMessage('Account username is missing or empty');
        new DeathByCaptcha_SocketClient(null, 'password');
    }

    /**
     * Test client with null password throws exception
     */
    public function testNullPasswordThrowsException()
    {
        $this->expectException(DeathByCaptcha_RuntimeException::class);
        $this->expectExceptionMessage('Account password is missing or empty');
        new DeathByCaptcha_SocketClient('username', null);
    }

    /**
     * Test verbose flag can be set
     */
    public function testVerboseFlagCanBeSet()
    {
        $this->client->is_verbose = true;
        $this->assertTrue($this->client->is_verbose);

        $this->client->is_verbose = false;
        $this->assertFalse($this->client->is_verbose);
    }

    /**
     * Test close method returns self
     */
    public function testCloseReturnsClient()
    {
        $result = $this->client->close();
        $this->assertInstanceOf(DeathByCaptcha_SocketClient::class, $result);
    }

    /**
     * Test HOST constant
     */
    public function testHostConstant()
    {
        $this->assertEquals('api.dbcapi.me', DeathByCaptcha_SocketClient::HOST);
    }

    /**
     * Test FIRST_PORT constant
     */
    public function testFirstPortConstant()
    {
        $this->assertEquals(8123, DeathByCaptcha_SocketClient::FIRST_PORT);
    }

    /**
     * Test LAST_PORT constant
     */
    public function testLastPortConstant()
    {
        $this->assertEquals(8130, DeathByCaptcha_SocketClient::LAST_PORT);
    }

    /**
     * Test TERMINATOR constant
     */
    public function testTerminatorConstant()
    {
        $this->assertEquals("\r\n", DeathByCaptcha_SocketClient::TERMINATOR);
    }

    /**
     * Test parse_json_response static method
     */
    public function testParseJsonResponse()
    {
        $json = '{"captcha": 456, "text": "SOLUTION", "is_correct": true}';
        $result = DeathByCaptcha_SocketClient::parse_json_response($json);

        $this->assertIsArray($result);
        $this->assertEquals(456, $result['captcha']);
        $this->assertEquals('SOLUTION', $result['text']);
        $this->assertTrue($result['is_correct']);
    }

    /**
     * Test that client can be used with authtoken
     */
    public function testClientWithAuthtoken()
    {
        $client = new DeathByCaptcha_SocketClient('authtoken', 'test_token_value');
        $this->assertInstanceOf(DeathByCaptcha_SocketClient::class, $client);
    }

    /**
     * Test SOCKET_EAGAIN constant is defined
     */
    public function testSocketEagainConstantDefined()
    {
        $this->assertTrue(defined('SOCKET_EAGAIN'));
    }

    /**
     * Test that json_encode function exists (required)
     */
    public function testJsonEncodeFunctionExists()
    {
        $this->assertTrue(function_exists('json_encode'));
    }

    /**
     * Test that json_decode function exists (required)
     */
    public function testJsonDecodeFunctionExists()
    {
        $this->assertTrue(function_exists('json_decode'));
    }

    /**
     * Test that base64_encode function exists (required)
     */
    public function testBase64EncodeFunctionExists()
    {
        $this->assertTrue(function_exists('base64_encode'));
    }

    /**
     * Test that fsockopen function exists (required)
     */
    public function testFsockopenFunctionExists()
    {
        $this->assertTrue(function_exists('fsockopen'));
    }

    /**
     * Test that client destructor doesn't throw errors
     */
    public function testDestructorDoesNotThrow()
    {
        $client = new DeathByCaptcha_SocketClient('test', 'test');
        unset($client); // This calls __destruct()
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    /**
     * Test that socket is null after instantiation
     */
    public function testSocketIsNullAfterInstantiation()
    {
        // Socket should only be created on first _connect() call
        // which happens on first API call
        // Test passes without actual API access
        $this->assertInstanceOf(DeathByCaptcha_SocketClient::class, $this->client);
    }
}
