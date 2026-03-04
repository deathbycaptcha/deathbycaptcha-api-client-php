<?php

require_once 'deathbycaptcha.php';


$username = $argv[1];
$password = $argv[2];
$clienttype = $argv[3];

if ($clienttype == "HTTP"){
    echo "using http client\n";
    $client = new DeathByCaptcha_HttpClient($username, $password);    
} else {
    echo "using socket client\n";
    $client = new DeathByCaptcha_SocketClient($username, $password); 
}

$client->is_verbose = true;

echo "Your balance is {$client->balance} US cents\n";
