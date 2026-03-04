<?php
/**
 * Death by Captcha PHP API Amazon Waf usage example
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

// Set the proxy and Amazon Waf data
$data = array(
    'proxy' => 'http://user:password@127.0.0.1:1234',
    'proxytype' => 'HTTP',
    'sitekey' => 'AQIDAHjcYu/GjX+QlghicBgQ/7bFaQZ+m5FKCMDnO+vTbNg96AHDh0IR5vgzHNceHYqZR+GOAAAAfjB8BgkqhkiG9w0BBwagbzBtAgEAMGgGCSqGSIb3DQEHATAeBglghkgBZQMEAS4wEQQMsYNQbVOLOfd/1ofjAgEQgDuhVKc2V/0XTEPc+9X/xAodxDqgyNNNyYJN1rM2gs4yBMeDXXc3z2ZxmD9jsQ8eNMGHqeii56iL2Guh4A==',
    'pageurl' => 'https://efw47fpad9.execute-api.us-east-1.amazonaws.com/latest',
    'iv' => 'CgAFRjIw2vAAABSM',
    'context' => 'zPT0jOl1rQlUNaldX6LUpn4D6Tl9bJ8VUQ/NrWFxPiiFujn5bFHzpOlKYQG0Di/UrO/p0xItkf7oGrknHqnj+UjvWv+i0BFbm3vGKceNaGtjrg4wvydL2Li5XjwRUOMW4o+NgO3JPJhkgwRKSyK62cIIzrThlOBD+gmtvKW0JNtH8efKR8Y5mBf0gi8JokjUxq/XbyB6h83tfaiWrp3dkOJsEXHLkT/wwQlFZysA919LCA+XVqgJ9lurUZqHWar+9JHqWnc0ghckKCnUzubvSQzJl+eSIAIoYZrpuZQszOwWzo4='
);
//Create a json string
$json = json_encode($data);

//Put the type and the json payload
$extra = [
    'type' => 16,
    'waf_params' => $json,
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
	
