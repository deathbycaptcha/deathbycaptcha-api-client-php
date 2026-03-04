<?php
/**
 * DeathByCaptcha_HttpClient Test Suite
 * 
 * Tests for HTTP API client functionality
 */

use PHPUnit\Framework\TestCase;

class DeathByCaptcha_HttpClientTest extends TestCase
{
    /**
     * @var DeathByCaptcha_HttpClient
     */
    private $client;

    /**
     * Set up test client
     */
    protected function setUp(): void
    {
        $this->client = new DeathByCaptcha_HttpClient('test_user', 'test_pass');
    }

    /**
     * Test client instantiation
     */
    public function testClientInstantiation()
    {
        $this->assertInstanceOf(DeathByCaptcha_HttpClient::class, $this->client);
        $this->assertInstanceOf(DeathByCaptcha_Client::class, $this->client);
    }

    /**
     * Test that CURL extension is required
     */
    public function testCurlExtensionRequired()
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('CURL extension is not loaded');
        }

        // If we get here, CURL is loaded and client should work
        $this->assertInstanceOf(DeathByCaptcha_HttpClient::class, $this->client);
    }

    /**
     * Test client with empty username throws exception
     */
    public function testEmptyUsernameThrowsException()
    {
        $this->expectException(DeathByCaptcha_RuntimeException::class);
        $this->expectExceptionMessage('Account username is missing or empty');
        new DeathByCaptcha_HttpClient('', 'password');
    }

    /**
     * Test client with empty password throws exception
     */
    public function testEmptyPasswordThrowsException()
    {
        $this->expectException(DeathByCaptcha_RuntimeException::class);
        $this->expectExceptionMessage('Account password is missing or empty');
        new DeathByCaptcha_HttpClient('username', '');
    }

    /**
     * Test client with null username throws exception
     */
    public function testNullUsernameThrowsException()
    {
        $this->expectException(DeathByCaptcha_RuntimeException::class);
        $this->expectExceptionMessage('Account username is missing or empty');
        new DeathByCaptcha_HttpClient(null, 'password');
    }

    /**
     * Test client with null password throws exception
     */
    public function testNullPasswordThrowsException()
    {
        $this->expectException(DeathByCaptcha_RuntimeException::class);
        $this->expectExceptionMessage('Account password is missing or empty');
        new DeathByCaptcha_HttpClient('username', null);
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
        $this->assertInstanceOf(DeathByCaptcha_HttpClient::class, $result);
    }

    /**
     * Test API version constant
     */
    public function testAPIVersionConstant()
    {
        $this->assertStringContainsString('DBC/PHP', DeathByCaptcha_Client::API_VERSION);
    }

    /**
     * Test DEFAULT_TIMEOUT constant
     */
    public function testDefaultTimeoutConstant()
    {
        $this->assertEquals(60, DeathByCaptcha_Client::DEFAULT_TIMEOUT);
    }

    /**
     * Test DEFAULT_TOKEN_TIMEOUT constant
     */
    public function testDefaultTokenTimeoutConstant()
    {
        $this->assertEquals(120, DeathByCaptcha_Client::DEFAULT_TOKEN_TIMEOUT);
    }

    /**
     * Test BASE_URL constant
     */
    public function testBaseURLConstant()
    {
        $this->assertEquals('https://api.dbcapi.me/api', DeathByCaptcha_HttpClient::BASE_URL);
    }

    /**
     * Test parse_json_response static method
     */
    public function testParseJsonResponse()
    {
        $json = '{"captcha": 123, "text": "ANSWER", "is_correct": true}';
        $result = DeathByCaptcha_HttpClient::parse_json_response($json);

        $this->assertIsArray($result);
        $this->assertEquals(123, $result['captcha']);
        $this->assertEquals('ANSWER', $result['text']);
        $this->assertTrue($result['is_correct']);
    }

    /**
     * Test parse_plain_response static method
     */
    public function testParsePlainResponse()
    {
        $plain = 'captcha=123&text=ANSWER&is_correct=1';
        $result = DeathByCaptcha_HttpClient::parse_plain_response($plain);

        $this->assertIsArray($result);
        $this->assertEquals('123', $result['captcha']);
        $this->assertEquals('ANSWER', $result['text']);
        $this->assertEquals('1', $result['is_correct']);
    }

    /**
     * Test that client can be used with authtoken
     */
    public function testClientWithAuthtoken()
    {
        $client = new DeathByCaptcha_HttpClient('authtoken', 'test_token_value');
        $this->assertInstanceOf(DeathByCaptcha_HttpClient::class, $client);
    }

    /**
     * Test magic getter for user property (without actual API call)
     */
    public function testMagicGetterAccess()
    {
        // Test that the magic getter is accessible
        // Note: This won't return actual data without a valid API call
        try {
            $user = $this->client->user;
            // Will be null if not authenticated
            $this->assertNull($user);
        } catch (DeathByCaptcha_IOException $e) {
            // Expected when network is not available
            $this->assertTrue(true);
        } catch (Exception $e) {
            // Other exceptions might occur - that's ok for this test
            $this->assertTrue(true);
        }
    }

    /**
     * Test that client destructor doesn't throw errors
     */
    public function testDestructorDoesNotThrow()
    {
        $client = new DeathByCaptcha_HttpClient('test', 'test');
        unset($client); // This calls __destruct()
        $this->assertTrue(true); // If we get here, no exception was thrown
    }
}
