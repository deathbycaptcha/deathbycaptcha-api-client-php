<?php

use PHPUnit\Framework\TestCase;

class DeathByCaptcha_LiveIntegrationTest extends TestCase
{
    private static $client;
    private static $decodedCaptcha;

    public static function setUpBeforeClass(): void
    {
        $username = getenv('DBC_USERNAME') ?: '';
        $password = getenv('DBC_PASSWORD') ?: '';

        if ($username === '' || $password === '') {
            self::markTestSkipped('Missing DBC_USERNAME/DBC_PASSWORD. Configure .env for live integration tests.');
        }

        self::$client = new DeathByCaptcha_HttpClient($username, $password);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$client instanceof DeathByCaptcha_HttpClient) {
            self::$client->close();
        }
    }

    public function testServerSocketStatusReachable()
    {
        $reachable = false;

        for ($port = DeathByCaptcha_SocketClient::FIRST_PORT; $port <= DeathByCaptcha_SocketClient::LAST_PORT; $port++) {
            $errno = 0;
            $errstr = '';
            $socket = @fsockopen(DeathByCaptcha_SocketClient::HOST, $port, $errno, $errstr, 3);
            if (is_resource($socket)) {
                fclose($socket);
                $reachable = true;
                break;
            }
        }

        $this->assertTrue($reachable, 'DBC server status check failed: no reachable socket endpoint in configured port range.');
    }

    public function testUserStatusAndBalance()
    {
        $user = self::$client->get_user();

        $this->assertIsArray($user, 'Unable to fetch user status from API.');
        $this->assertArrayHasKey('user', $user);
        $this->assertArrayHasKey('balance', $user);
        $this->assertGreaterThan(0, (int)$user['user']);
        $this->assertGreaterThanOrEqual(0, (float)$user['balance']);
    }

    public function testUploadTypeZeroAndWaitForResponse()
    {
        $imagePath = getenv('DBC_TEST_IMAGE') ?: BASE_PATH . '/images/normal.jpg';
        $timeout = (int)(getenv('DBC_INTEGRATION_TIMEOUT') ?: 120);

        if (!is_readable($imagePath)) {
            $this->fail('Integration image is not readable: ' . $imagePath);
        }

        $captcha = self::$client->decode($imagePath, ['type' => 0], $timeout);
        self::$decodedCaptcha = $captcha;

        $this->assertIsArray($captcha, 'CAPTCHA decode returned null.');
        $this->assertArrayHasKey('captcha', $captcha);
        $this->assertArrayHasKey('text', $captcha);
        $this->assertGreaterThan(0, (int)$captcha['captcha']);
        $this->assertNotEmpty($captcha['text'], 'CAPTCHA solved response text is empty.');
    }

    public function testGetCaptchaById()
    {
        if (!is_array(self::$decodedCaptcha) || empty(self::$decodedCaptcha['captcha'])) {
            $this->markTestSkipped('No solved captcha available from previous step.');
        }

        $captchaId = (int)self::$decodedCaptcha['captcha'];
        $captcha = self::$client->get_captcha($captchaId);

        $this->assertIsArray($captcha);
        $this->assertSame($captchaId, (int)$captcha['captcha']);
    }

    public function testReportReturnsBoolean()
    {
        if (!is_array(self::$decodedCaptcha) || empty(self::$decodedCaptcha['captcha'])) {
            $this->markTestSkipped('No solved captcha available for report step.');
        }

        $reported = self::$client->report((int)self::$decodedCaptcha['captcha']);
        $this->assertIsBool($reported);
    }
}
