<?php
/**
 * Death by Captcha PHP API geetest usage example
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

// Set the proxy and geetest token data
$data = array(
    'proxy' => 'http://user:password@127.0.0.1:1234',
    'proxytype' => 'HTTP',
    'captcha_id' => 'fcd636b4514bf7ac4143922550b3008b',
    'pageurl' => 'https://www.geetest.com/en/adaptive-captcha-demo'
);
//Create a json string
$json = json_encode($data);

//Put the type and the json payload
$extra = [
    'type' => 9,
    'geetest_params' => $json,
];

// Put null the first parameter and add the extra payload
if ($captcha = $client->decode(null, $extra)) {
    echo "CAPTCHA {$captcha['captcha']} uploaded\n";

    sleep(DeathByCaptcha_Client::DEFAULT_TIMEOUT);

    // Poll for CAPTCHA indexes:
    if ($text = $client->get_text($captcha['captcha'])) {
        echo "CAPTCHA {$captcha['captcha']} solved: ".json_encode($text)."\n";

        // // To access the response by item
        // echo "captcha_id: {$text['captcha_id']}\n";
        // echo "lot_number: {$text['lot_number']}\n";
        // echo "pass_token: {$text['pass_token']}\n";
        // echo "gen_time: {$text['gen_time']}\n";
        // echo "captcha_output: {$text['captcha_output']}\n";

        // Report an incorrectly solved CAPTCHA.
        // Make sure the CAPTCHA was in fact incorrectly solved!
        //$client->report($captcha['captcha']);
    }
}
