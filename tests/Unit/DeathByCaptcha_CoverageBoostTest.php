<?php

use PHPUnit\Framework\TestCase;

class TestableBaseClient extends DeathByCaptcha_Client
{
    public $uploaded = [];
    public $captchas = [];
    public $userData = null;

    public function close()
    {
        return $this;
    }

    public function get_user()
    {
        return $this->userData;
    }

    public function get_captcha($cid)
    {
        if (array_key_exists($cid, $this->captchas)) {
            return $this->captchas[$cid];
        }
        if (!empty($this->captchas)) {
            return array_shift($this->captchas);
        }
        return null;
    }

    public function report($cid)
    {
        return true;
    }

    public function upload($captcha=null, $extra=[])
    {
        $this->uploaded[] = ['captcha' => $captcha, 'extra' => $extra];
        return array_shift($this->captchas);
    }

    public function exposeLoadCaptcha($captcha)
    {
        return $this->_load_captcha($captcha);
    }

    public function exposeIsValidCaptcha($img)
    {
        return $this->_is_valid_captcha($img);
    }

    public function exposeGetPollInterval($idx)
    {
        return $this->_get_poll_interval($idx);
    }
}

class FakeHttpClient extends DeathByCaptcha_HttpClient
{
    public $calls = [];
    private $responses = [];

    public function __construct()
    {
        $this->_userpwd = ['user', 'pass'];
    }

    public function queueResponse($response)
    {
        $this->responses[] = $response;
    }

    protected function _call($cmd, $payload=null)
    {
        $this->calls[] = ['cmd' => $cmd, 'payload' => $payload];
        return array_shift($this->responses);
    }

    public function close()
    {
        return $this;
    }
}

class FakeSocketClient extends DeathByCaptcha_SocketClient
{
    public $responsesByCmd = [];
    public $closeCount = 0;

    public function queueCmdResponse($cmd, $response)
    {
        if (!isset($this->responsesByCmd[$cmd])) {
            $this->responsesByCmd[$cmd] = [];
        }
        $this->responsesByCmd[$cmd][] = $response;
    }

    protected function _connect()
    {
        if (null === $this->_sock) {
            $this->_sock = fopen('php://temp', 'r+');
        }
        return $this;
    }

    protected function _sendrecv($buf)
    {
        $payload = json_decode($buf, true);
        $cmd = $payload['cmd'];

        $response = null;
        if (isset($this->responsesByCmd[$cmd]) && count($this->responsesByCmd[$cmd]) > 0) {
            $response = array_shift($this->responsesByCmd[$cmd]);
        } else {
            $response = [];
        }

        if ($response instanceof Exception) {
            throw $response;
        }

        if (is_string($response)) {
            return $response;
        }

        return json_encode($response);
    }

    public function close()
    {
        $this->closeCount++;
        if (is_resource($this->_sock)) {
            fclose($this->_sock);
        }
        $this->_sock = null;
        return $this;
    }
}

class DeathByCaptcha_CoverageBoostTest extends TestCase
{
    public function testLoadCaptchaFromArray()
    {
        $client = new TestableBaseClient('u', 'p');
        $this->assertSame('ABC', $client->exposeLoadCaptcha([65, 66, 67]));
    }

    public function testLoadCaptchaFromBase64String()
    {
        $client = new TestableBaseClient('u', 'p');
        $this->assertSame('hello', $client->exposeLoadCaptcha('base64:aGVsbG8='));
    }

    public function testLoadCaptchaFromResource()
    {
        $client = new TestableBaseClient('u', 'p');
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'resource-body');

        $this->assertSame('resource-body', $client->exposeLoadCaptcha($resource));
        fclose($resource);
    }

    public function testLoadCaptchaFromFilePath()
    {
        $client = new TestableBaseClient('u', 'p');
        $tmp = tempnam(sys_get_temp_dir(), 'dbc');
        file_put_contents($tmp, 'file-body');

        $this->assertSame('file-body', $client->exposeLoadCaptcha($tmp));
        @unlink($tmp);
    }

    public function testInvalidCaptchaThrowsException()
    {
        $client = new TestableBaseClient('u', 'p');
        $this->expectException(DeathByCaptcha_InvalidCaptchaException::class);
        $client->exposeIsValidCaptcha('');
    }

    public function testValidCaptchaReturnsTrue()
    {
        $client = new TestableBaseClient('u', 'p');
        $this->assertTrue($client->exposeIsValidCaptcha('x'));
    }

    public function testGetPollIntervalUsesConfiguredValues()
    {
        $client = new TestableBaseClient('u', 'p');
        [$interval, $nextIdx] = $client->exposeGetPollInterval(0);

        $this->assertSame(1, $interval);
        $this->assertSame(1, $nextIdx);
    }

    public function testGetPollIntervalFallsBackToDefault()
    {
        $client = new TestableBaseClient('u', 'p');
        [$interval, $nextIdx] = $client->exposeGetPollInterval(100);

        $this->assertSame(DeathByCaptcha_Client::DFLT_POLL_INTERVAL, $interval);
        $this->assertSame(101, $nextIdx);
    }

    public function testDecodeReturnsSolvedCaptchaWithoutPolling()
    {
        $client = new TestableBaseClient('u', 'p');
        $client->captchas = [
            ['captcha' => 10, 'text' => 'SOLVED', 'is_correct' => true],
        ];

        $result = $client->decode('base64:aQ==', ['proxy' => 'x'], 1);

        $this->assertSame('SOLVED', $result['text']);
        $this->assertCount(1, $client->uploaded);
    }

    public function testDecodeReturnsNullWhenCaptchaIncorrect()
    {
        $client = new TestableBaseClient('u', 'p');
        $client->captchas = [
            ['captcha' => 10, 'text' => 'WRONG', 'is_correct' => false],
        ];

        $result = $client->decode('base64:aQ==', [], 1);
        $this->assertNull($result);
    }

    public function testDecodeReturnsNullWhenUploadFails()
    {
        $client = new TestableBaseClient('u', 'p');
        $client->captchas = [null];

        $result = $client->decode('base64:aQ==', [], 1);
        $this->assertNull($result);
    }

    public function testDecodePollsAndReturnsSolvedCaptcha()
    {
        $client = new TestableBaseClient('u', 'p');
        $client->captchas = [
            ['captcha' => 42, 'text' => null, 'is_correct' => false],
            42 => ['captcha' => 42, 'text' => 'LATE', 'is_correct' => true],
        ];

        $result = $client->decode('base64:aQ==', [], 2);

        $this->assertSame('LATE', $result['text']);
        $this->assertSame(42, $result['captcha']);
    }

    public function testMagicGettersForUserAndBalance()
    {
        $client = new TestableBaseClient('u', 'p');
        $client->userData = ['user' => 123, 'balance' => 4.5, 'is_banned' => false];

        $this->assertSame($client->userData, $client->user);
        $this->assertSame(4.5, $client->balance);
        $this->assertNull($client->non_existing_property);
    }

    public function testHttpUploadReturnsNormalizedCaptcha()
    {
        $client = new FakeHttpClient();
        $client->queueResponse(['captcha' => 777, 'text' => 'OK', 'is_correct' => 1]);

        $result = $client->upload('base64:aGVsbG8=', ['banner' => null]);

        $this->assertSame(777, $result['captcha']);
        $this->assertSame('OK', $result['text']);
        $this->assertTrue($result['is_correct']);
        $this->assertSame('captcha', $client->calls[0]['cmd']);
    }

    public function testHttpUploadWithBannerAndCaptchaUsesPayload()
    {
        $client = new FakeHttpClient();
        $client->queueResponse(['captcha' => 778, 'text' => null, 'is_correct' => 0]);

        $result = $client->upload('base64:YWJj', ['banner' => 'base64:ZGVm']);

        $this->assertSame(778, $result['captcha']);
        $this->assertArrayHasKey('captchafile', $client->calls[0]['payload']);
        $this->assertArrayHasKey('banner', $client->calls[0]['payload']);
    }

    public function testHttpUploadWithoutCaptchaUsesExtraPayload()
    {
        $client = new FakeHttpClient();
        $client->queueResponse(['captcha' => 901, 'text' => '', 'is_correct' => 1]);

        $result = $client->upload(null, ['type' => 2, 'token_params' => 'x']);

        $this->assertSame(901, $result['captcha']);
        $this->assertNull($result['text']);
        $this->assertSame('captcha', $client->calls[0]['cmd']);
        $this->assertSame(2, $client->calls[0]['payload']['type']);
    }

    public function testHttpGetUserGetCaptchaAndReport()
    {
        $client = new FakeHttpClient();
        $client->queueResponse(['user' => 321, 'balance' => 7.75, 'is_banned' => 0]);
        $client->queueResponse(['captcha' => 654, 'text' => 'ABC', 'is_correct' => 1]);
        $client->queueResponse(['is_correct' => 0]);

        $user = $client->get_user();
        $captcha = $client->get_captcha(654);
        $reported = $client->report(654);

        $this->assertSame(321, $user['user']);
        $this->assertSame(7.75, $user['balance']);
        $this->assertSame('ABC', $captcha['text']);
        $this->assertTrue($reported);
    }

    public function testHttpReportReturnsFalseWhenCaptchaStillCorrect()
    {
        $client = new FakeHttpClient();
        $client->queueResponse(['is_correct' => 1]);

        $this->assertFalse($client->report(7));
    }

    public function testHttpUploadReturnsNullOnMissingCaptchaId()
    {
        $client = new FakeHttpClient();
        $client->queueResponse(['status' => 'ok']);

        $this->assertNull($client->upload(null, ['type' => 1]));
    }

    public function testHttpGetMethodsReturnNullForInvalidPayloads()
    {
        $client = new FakeHttpClient();
        $client->queueResponse(['user' => 0]);
        $client->queueResponse(['captcha' => 0]);
        $client->queueResponse(['captcha' => 0]);

        $this->assertNull($client->get_user());
        $this->assertNull($client->get_captcha(1));
        $this->assertNull($client->upload(null, []));
    }

    public function testSocketClientHandlesSuccessfulFlowIncludingLogin()
    {
        $client = new FakeSocketClient('user', 'pass');
        $client->queueCmdResponse('login', ['status' => 0]);
        $client->queueCmdResponse('user', ['user' => 8, 'balance' => 2.5, 'is_banned' => 0]);

        $user = $client->get_user();

        $this->assertSame(8, $user['user']);
        $this->assertSame(2.5, $user['balance']);
    }

    public function testSocketClientHandlesAuthtokenLoginFlow()
    {
        $client = new FakeSocketClient('authtoken', 'secret-token');
        $client->queueCmdResponse('login', ['status' => 0]);
        $client->queueCmdResponse('user', ['user' => 9, 'balance' => 3.5, 'is_banned' => 0]);

        $user = $client->get_user();

        $this->assertSame(9, $user['user']);
        $this->assertSame(3.5, $user['balance']);
    }

    /**
     * @dataProvider socketErrorProvider
     */
    public function testSocketClientMapsApiErrorsToExceptions($apiError, $expectedException)
    {
        $client = new FakeSocketClient('user', 'pass');
        $client->queueCmdResponse('login', ['status' => 0]);
        $client->queueCmdResponse('user', ['error' => $apiError]);

        $this->expectException($expectedException);
        $client->get_user();
    }

    public static function socketErrorProvider()
    {
        return [
            ['not-logged-in', DeathByCaptcha_AccessDeniedException::class],
            ['banned', DeathByCaptcha_AccessDeniedException::class],
            ['insufficient-funds', DeathByCaptcha_AccessDeniedException::class],
            ['invalid-captcha', DeathByCaptcha_InvalidCaptchaException::class],
            ['service-overload', DeathByCaptcha_ServiceOverloadException::class],
            ['unexpected-error', DeathByCaptcha_ServerException::class],
        ];
    }

    public function testSocketClientThrowsServerExceptionForInvalidJson()
    {
        $client = new FakeSocketClient('user', 'pass');
        $client->queueCmdResponse('login', ['status' => 0]);
        $client->queueCmdResponse('user', 'not-json');

        $this->expectException(DeathByCaptcha_ServerException::class);
        $this->expectExceptionMessage('Invalid API response');
        $client->get_user();
    }

    public function testSocketClientRetriesAfterIOExceptionAndSucceeds()
    {
        $client = new FakeSocketClient('user', 'pass');
        $client->queueCmdResponse('login', ['status' => 0]);
        $client->queueCmdResponse('user', new DeathByCaptcha_IOException('temporary'));
        $client->queueCmdResponse('login', ['status' => 0]);
        $client->queueCmdResponse('user', ['user' => 3, 'balance' => 1.2, 'is_banned' => 0]);

        $user = $client->get_user();

        $this->assertSame(3, $user['user']);
        $this->assertGreaterThanOrEqual(1, $client->closeCount);
    }

    public function testSocketUploadGetCaptchaAndReportNormalization()
    {
        $client = new FakeSocketClient('user', 'pass');
        $client->queueCmdResponse('login', ['status' => 0]);
        $client->queueCmdResponse('upload', ['captcha' => 111, 'text' => '', 'is_correct' => 1]);
        $client->queueCmdResponse('captcha', ['captcha' => 111, 'text' => 'TOKEN', 'is_correct' => 1]);
        $client->queueCmdResponse('report', ['is_correct' => 0]);

        $uploaded = $client->upload('base64:YWJj', ['banner' => 'base64:ZGVm']);
        $captcha = $client->get_captcha(111);
        $reported = $client->report(111);

        $this->assertSame(111, $uploaded['captcha']);
        $this->assertNull($uploaded['text']);
        $this->assertSame('TOKEN', $captcha['text']);
        $this->assertTrue($reported);
    }

    public function testSocketUploadWithoutCaptchaAndInvalidGetCaptcha()
    {
        $client = new FakeSocketClient('user', 'pass');
        $client->queueCmdResponse('login', ['status' => 0]);
        $client->queueCmdResponse('upload', ['captcha' => 22, 'text' => null, 'is_correct' => 1]);
        $client->queueCmdResponse('captcha', ['captcha' => 0, 'text' => '', 'is_correct' => 0]);
        $client->queueCmdResponse('report', ['is_correct' => 1]);

        $uploaded = $client->upload(null, ['type' => 2]);
        $captcha = $client->get_captcha(22);
        $reported = $client->report(22);

        $this->assertSame(22, $uploaded['captcha']);
        $this->assertNull($captcha);
        $this->assertFalse($reported);
    }
}
