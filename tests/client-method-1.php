<?php

use OSW3\CloudManager\Client;

require __DIR__ . "/../vendor/autoload.php";


print_r("\n\n");
print_r("CLOUD MANAGER - METHOD 1 ------------------------------------------------------ ");
print_r("\n\n");

$ftp_server = "host";
$ftp_user = "user";
$ftp_pass = "pass";


// Method 1 - Client with DSN
// ----------------------------------

// Instance
// --

$dsn = "ftp://{$ftp_user}:{$ftp_pass}@{$ftp_server}";
$auto_connect = true;
$auto_auth = true;
// $client = new Client($dsn, $auto_connect, $auto_auth);
$client = new Client($dsn);



// DSN
// --

$hasDsn = $client->hasDsn();
print_r("Has DSN : ". ($hasDsn ? "yes" : "no") ."\n\n");


// Show DSN Parts
if ($hasDsn)
{
    print_r("DSN Driver : {$client->dsn()->getDriver()} \n\n");
    print_r("DSN Host : {$client->dsn()->getHost()} \n\n");
    print_r("DSN User : {$client->dsn()->getUser()} \n\n");
    print_r("DSN Pass : {$client->dsn()->getPass()} \n\n");
    print_r("DSN Port : {$client->dsn()->getPort()} \n\n");
    print_r("DSN String : {$client->dsn()->get()} \n\n");
}



// Connected & Authenticated
// --

$isConnected = $client->isConnected();
print_r("Is Connected : ". ($isConnected ? "yes" : "no") ."\n\n");

$hasCredential = $client->hasCredential();
print_r("Has credential : ". ($hasCredential ? "yes" : "no") ."\n\n");
