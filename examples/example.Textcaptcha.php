<?php
/**
 * Death by Captcha PHP API TextCaptcha usage example
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
$client = new DeathByCaptcha_SocketClient($username, $password);
$client->is_verbose = true;

// To use token the first parameter must be authtoken.
// $client = new DeathByCaptcha_HttpClient("authtoken", $token_from_panel);

echo "Your balance is {$client->balance} US cents\n";

//Put the type and the json payload
$extra = [
    'type' => 11,
    'textcaptcha' => 'How many days have the week',
];

// Put null the first parameter and add the extra payload
if ($captcha = $client->decode(null, $extra)) {
    echo "CAPTCHA {$captcha['captcha']} uploaded\n";

    sleep(DeathByCaptcha_Client::DEFAULT_TIMEOUT);

    // Poll for CAPTCHA indexes:
    if ($text = $client->get_text($captcha['captcha'])) {
        echo "CAPTCHA {$captcha['captcha']} solved: {$text}\n";

        // Report an incorrectly solved CAPTCHA.
        // Make sure the CAPTCHA was in fact incorrectly solved!
        //$client->report($captcha['captcha']);
    }
}
	
