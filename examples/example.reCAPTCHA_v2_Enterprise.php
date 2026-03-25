<?php
/**
 * Death by Captcha PHP API reCAPTCHA v2 Enterprise usage example
 *
 * reCAPTCHA v2 Enterprise is an advanced version of reCAPTCHA v2 providing
 * more detailed risk analysis. It uses type=25 and token_enterprise_params
 * instead of the token_params used for standard reCAPTCHA v2 (type=4).
 *
 * Note: proxy is mandatory for reCAPTCHA v2 Enterprise.
 *
 * @package DBCAPI
 * @subpackage PHP
 */

/**
 * DBC API clients
 */
require_once '../deathbycaptcha.php';

$username = "username";  // DBC account username
$password = "password";  // DBC account password
$token_from_panel = "your-token-from-panel";  // DBC account authtoken

// Use DeathByCaptcha_SocketClient() class if you want to use SOCKET API.
$client = new DeathByCaptcha_HttpClient($username, $password);
$client->is_verbose = true;

// To use token the first parameter must be authtoken.
// $client = new DeathByCaptcha_HttpClient("authtoken", $token_from_panel);

echo "Your balance is {$client->balance} US cents\n";

// Set the proxy and reCAPTCHA v2 Enterprise data.
// Note: proxy is mandatory for reCAPTCHA v2 Enterprise.
$data = array(
    'proxy'     => 'http://user:password@127.0.0.1:1234',
    'proxytype' => 'HTTP',
    'googlekey' => '6Le-wvkSAAAAAPBMRTvw0Q4Muexq9bi0DJwx_mJ-',
    'pageurl'   => 'https://www.google.com/recaptcha/api2/demo',
);

// Create a json string
$json = json_encode($data);

// Put the type and the json payload.
// Use type=25 and token_enterprise_params (not token_params).
$extra = [
    'type'                    => 25,
    'token_enterprise_params' => $json,
];

// Put null the first parameter and add the extra payload
if ($captcha = $client->decode(null, $extra)) {
    echo "CAPTCHA {$captcha['captcha']} uploaded\n";

    sleep(DeathByCaptcha_Client::DEFAULT_TIMEOUT);

    // Poll for CAPTCHA result:
    if ($text = $client->get_text($captcha['captcha'])) {
        echo "CAPTCHA {$captcha['captcha']} solved: {$text}\n";

        // Report an incorrectly solved CAPTCHA.
        // Make sure the CAPTCHA was in fact incorrectly solved!
        //$client->report($captcha['captcha']);
    }
}
