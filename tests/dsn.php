<?php

use OSW3\CloudManager\Services\DsnService;

require __DIR__ . "/../vendor/autoload.php";

if (file_exists(__DIR__."/config.php")) include __DIR__."/config.php";


if (!isset($dropbox_app_key)) $dropbox_app_key = "";
if (!isset($dropbox_app_secret)) $dropbox_app_secret = "";
if (!isset($dropbox_access_token)) $dropbox_access_token = "";


if (!isset($ftp_host)) $ftp_host = "";
if (!isset($ftp_user)) $ftp_user = "";
if (!isset($ftp_pass)) $ftp_pass = "";





print_r("\n\n");
print_r("-- OSW3 CLOUD MANAGER -- DSN SERVICE---------------------------------- \n\n");


$dsn = "dropbox:basic://{$dropbox_app_key}:{$dropbox_app_secret}";
$dsn = "dropbox:token://{$dropbox_access_token}";
// $dsn = "ftp://{$ftp_user}:{$ftp_pass}@{$ftp_host}:21";
// $dsn = "ftp://{$ftp_user}:{$ftp_pass}@{$ftp_host}";



$dsnService = new DsnService($dsn);

print_r( "DSN String : {$dsn} \n\n" );
print_r( "Driver : \t{$dsnService->getDriver()} \n" );
print_r( "Auth Method : \t{$dsnService->getAuth()} \n" );
print_r( "User : \t\t{$dsnService->getUser()} \n" );
print_r( "Pass : \t\t{$dsnService->getPass()} \n" );
print_r( "Token : \t{$dsnService->getToken()} \n" );
print_r( "Host : \t\t{$dsnService->getHost()} \n" );
print_r( "Port : \t\t{$dsnService->getPort()} \n" );

print_r( "\nDSN String : {$dsnService->get()} \n\n" );

