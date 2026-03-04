<?php
/**
 * Death by Captcha PHP API newrecaptcha_image_group usage example
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

$captcha_filename = "../images/test2.jpg";  // your image here
$banner_filename = "../images/banner.jpg"; // your banner image here
$extra = [
    'type' => 3,  // captcha_type
    'banner' => $banner_filename,  // banner img
    'banner_text' => "select all pizza:",  // banner text
    // 'grid' => "3x3"  // optional parameter for specifying what grid
    // images are aligned to.
    // if omitted, dbc would try to autodetect the grid.
];

if ($captcha = $client->decode($captcha_filename, $extra)) {
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
