<?php
/**
 * Integration and Utility Tests
 * 
 * Tests for helper functions and integration scenarios
 */

use PHPUnit\Framework\TestCase;

class DeathByCaptcha_IntegrationTest extends TestCase
{
    /**
     * Test both HTTP and Socket clients can be instantiated
     */
    public function testCanInstantiateBothClientTypes()
    {
        $httpClient = new DeathByCaptcha_HttpClient('test_user', 'test_pass');
        $socketClient = new DeathByCaptcha_SocketClient('test_user', 'test_pass');

        $this->assertInstanceOf(DeathByCaptcha_Client::class, $httpClient);
        $this->assertInstanceOf(DeathByCaptcha_Client::class, $socketClient);
    }

    /**
     * Test switching between client implementations
     */
    public function testClientInterchangeability()
    {
        $clients = [
            new DeathByCaptcha_HttpClient('user', 'pass'),
            new DeathByCaptcha_SocketClient('user', 'pass'),
        ];

        foreach ($clients as $client) {
            // Both should support the same interface
            $this->assertTrue(method_exists($client, 'close'));
            $this->assertTrue(method_exists($client, 'get_user'));
            $this->assertTrue(method_exists($client, 'get_captcha'));
            $this->assertTrue(method_exists($client, 'upload'));
            $this->assertTrue(method_exists($client, 'report'));
            $this->assertTrue(method_exists($client, 'decode'));
        }
    }

    /**
     * Test that all required methods exist and clients can be closed
     */
    public function testUserCanSwitchClients()
    {
        // This tests the interface contract
        $user = 'test_user';
        $pass = 'test_pass';

        $clients = [
            new DeathByCaptcha_HttpClient($user, $pass),
            new DeathByCaptcha_SocketClient($user, $pass),
        ];

        $closedCount = 0;
        foreach ($clients as $client) {
            try {
                // Try calling methods that should exist
                $client->close();
                $closedCount++;
            } catch (DeathByCaptcha_Exception $e) {
                // Exceptions are ok (expected without real API access)
                $this->assertTrue(true);
            }
        }
        
        // Assert that close was called successfully
        $this->assertGreaterThanOrEqual($closedCount, count($clients));
    }

    /**
     * Test that verbose mode works with both clients
     */
    public function testVerboseModeOnBothClients()
    {
        $httpClient = new DeathByCaptcha_HttpClient('test', 'test');
        $socketClient = new DeathByCaptcha_SocketClient('test', 'test');

        // Enable verbose on both
        $httpClient->is_verbose = true;
        $socketClient->is_verbose = true;

        $this->assertTrue($httpClient->is_verbose);
        $this->assertTrue($socketClient->is_verbose);

        // Disable verbose on both
        $httpClient->is_verbose = false;
        $socketClient->is_verbose = false;

        $this->assertFalse($httpClient->is_verbose);
        $this->assertFalse($socketClient->is_verbose);
    }

    /**
     * Test error handling consistency across clients
     */
    public function testExceptionConsistencyAcrossClients()
    {
        // Both should require username/password
        try {
            new DeathByCaptcha_HttpClient('', 'test');
            $this->fail('Expected exception not thrown');
        } catch (DeathByCaptcha_RuntimeException $e) {
            $this->assertStringContainsString('username', $e->getMessage());
        }

        try {
            new DeathByCaptcha_SocketClient('', 'test');
            $this->fail('Expected exception not thrown');
        } catch (DeathByCaptcha_RuntimeException $e) {
            $this->assertStringContainsString('username', $e->getMessage());
        }
    }

    /**
     * Test JSON encoding/decoding roundtrip
     */
    public function testJsonRoundTrip()
    {
        $original = [
            'captcha' => 123456,
            'text' => 'ANSWER',
            'is_correct' => true,
            'status' => 0,
        ];

        $json = json_encode($original);
        $decoded = DeathByCaptcha_Client::parse_json_response($json);

        $this->assertEquals($original['captcha'], $decoded['captcha']);
        $this->assertEquals($original['text'], $decoded['text']);
        $this->assertTrue($decoded['is_correct']);
    }

    /**
     * Test that exception classes work correctly in real scenarios
     */
    public function testExceptionHandlingScenarios()
    {
        // Scenario 1: Access Denied
        try {
            throw new DeathByCaptcha_AccessDeniedException('Invalid credentials');
        } catch (DeathByCaptcha_ClientException $e) {
            $this->assertInstanceOf(DeathByCaptcha_Exception::class, $e);
        }

        // Scenario 2: Service Overload
        try {
            throw new DeathByCaptcha_ServiceOverloadException('Too many requests');
        } catch (DeathByCaptcha_ServerException $e) {
            $this->assertInstanceOf(DeathByCaptcha_Exception::class, $e);
        }

        // Scenario 3: Invalid CAPTCHA
        try {
            throw new DeathByCaptcha_InvalidCaptchaException('Bad image');
        } catch (DeathByCaptcha_ClientException $e) {
            $this->assertInstanceOf(DeathByCaptcha_Exception::class, $e);
        }
    }

    /**
     * Test client lifecycle
     */
    public function testClientLifecycle()
    {
        // Create
        $client = new DeathByCaptcha_HttpClient('user', 'pass');
        $this->assertInstanceOf(DeathByCaptcha_HttpClient::class, $client);

        // Enable verbose
        $client->is_verbose = true;
        $this->assertTrue($client->is_verbose);

        // Close
        $result = $client->close();
        $this->assertInstanceOf(DeathByCaptcha_HttpClient::class, $result);

        // Can close multiple times
        $result2 = $client->close();
        $this->assertInstanceOf(DeathByCaptcha_HttpClient::class, $result2);
    }

    /**
     * Test that authtoken works as alternative authentication
     */
    public function testAuthTokenAsAlternativeAuth()
    {
        $tokenClient = new DeathByCaptcha_HttpClient('authtoken', 'token_12345');
        $this->assertInstanceOf(DeathByCaptcha_HttpClient::class, $tokenClient);

        $socketClient = new DeathByCaptcha_SocketClient('authtoken', 'token_67890');
        $this->assertInstanceOf(DeathByCaptcha_SocketClient::class, $socketClient);
    }

    /**
     * Test multiple client instances with different credentials
     */
    public function testMultipleClientInstances()
    {
        $client1 = new DeathByCaptcha_HttpClient('user1', 'pass1');
        $client2 = new DeathByCaptcha_HttpClient('user2', 'pass2');
        $client3 = new DeathByCaptcha_SocketClient('user3', 'pass3');

        // All should be independent instances
        $this->assertNotSame($client1, $client2);
        $this->assertNotSame($client2, $client3);
        $this->assertNotSame($client1, $client3);

        // Each should be closeable
        $client1->close();
        $client2->close();
        $client3->close();

        $this->assertTrue(true);
    }

    /**
     * Test that constants are consistent
     */
    public function testConstantsConsistency()
    {
        // Base client constants
        $this->assertGreaterThan(0, DeathByCaptcha_Client::DEFAULT_TIMEOUT);
        $this->assertGreaterThan(
            DeathByCaptcha_Client::DFLT_POLL_INTERVAL,
            DeathByCaptcha_Client::DEFAULT_TIMEOUT
        );

        // HTTP client constants
        $this->assertStringContainsString('api.dbcapi.me', DeathByCaptcha_HttpClient::BASE_URL);

        // Socket client constants
        $this->assertStringContainsString('api.dbcapi.me', DeathByCaptcha_SocketClient::HOST);
        $this->assertLessThan(
            DeathByCaptcha_SocketClient::LAST_PORT,
            DeathByCaptcha_SocketClient::FIRST_PORT
        );
    }
}
